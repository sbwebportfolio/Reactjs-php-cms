<?php
/** First-admin installer. Run once from CLI after importing the CMS schema. */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/../includes/functions.php';

if ($argc !== 4) {
    fwrite(STDERR, "Usage: php backend/bin/create-admin.php \"Full Name\" email@example.com 'StrongPassword'\n");
    exit(2);
}
[$script, $name, $email, $password] = $argv;
$name = trim($name);
$email = strtolower(trim($email));
if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
    fwrite(STDERR, "Name and valid email are required; password must be at least 12 characters.\n");
    exit(2);
}
$conn = dbConnection();
$count = (int) $conn->query('SELECT COUNT(*) AS total FROM admins')->fetch_assoc()['total'];
if ($count > 0) {
    fwrite(STDERR, "An admin already exists. Use the dashboard's user management instead.\n");
    exit(1);
}
$roleResult = $conn->query("SELECT id FROM roles WHERE name = 'Super Admin' LIMIT 1");
$role = $roleResult ? $roleResult->fetch_assoc() : null;
if (!$role) {
    fwrite(STDERR, "Super Admin role is missing; import cms_database.sql first.\n");
    exit(1);
}
$conn->begin_transaction();
try {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO admins (name, email, password_hash, status) VALUES (?, ?, ?, 'active')");
    $stmt->bind_param('sss', $name, $email, $hash);
    $stmt->execute();
    $adminId = $conn->insert_id;
    $stmt->close();
    $roleId = (int) $role['id'];
    $stmt = $conn->prepare('INSERT INTO admin_roles (admin_id, role_id) VALUES (?, ?)');
    $stmt->bind_param('ii', $adminId, $roleId);
    $stmt->execute();
    $stmt->close();
    $conn->commit();
    fwrite(STDOUT, "Super Admin account created successfully.\n");
} catch (Throwable $e) {
    $conn->rollback();
    fwrite(STDERR, "Admin creation failed; transaction rolled back. Check database logs.\n");
    error_log($e->getMessage());
    exit(1);
}
