<?php
/** Admin auth and role-based permission helpers. */
require_once __DIR__ . '/functions.php';

function requireAdminAuth(): void
{
    if (!isAdminLoggedIn()) redirect('/backend/admin/login.php');
}

function isAdminLoggedIn(): bool
{
    return isset($_SESSION['admin']['id'], $_SESSION['admin']['email'])
        && (int) $_SESSION['admin']['id'] > 0;
}

function getAdminSession(): ?array
{
    return $_SESSION['admin'] ?? null;
}

function adminLogin(string $email, string $password): bool
{
    $email = sanitizeEmail($email);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') return false;

    $conn = dbConnection();
    $stmt = $conn->prepare('SELECT id, name, email, password_hash, status, failed_login_count, locked_until FROM admins WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$admin || $admin['status'] !== 'active') {
        password_verify($password, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.');
        return false;
    }
    if (!empty($admin['locked_until']) && strtotime($admin['locked_until']) > time()) return false;

    if (!password_verify($password, $admin['password_hash'])) {
        $attempts = (int) $admin['failed_login_count'] + 1;
        $lockedUntil = $attempts >= 5 ? date('Y-m-d H:i:s', time() + 900) : null;
        $update = $conn->prepare('UPDATE admins SET failed_login_count = ?, locked_until = ? WHERE id = ?');
        $id = (int) $admin['id'];
        $update->bind_param('isi', $attempts, $lockedUntil, $id);
        $update->execute();
        $update->close();
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['admin'] = [
        'id' => (int) $admin['id'],
        'name' => $admin['name'],
        'email' => $admin['email'],
        'login_time' => time(),
    ];

    $id = (int) $admin['id'];
    $update = $conn->prepare('UPDATE admins SET failed_login_count = 0, locked_until = NULL, last_login_at = NOW() WHERE id = ?');
    $update->bind_param('i', $id);
    $update->execute();
    $update->close();
    logActivity($id, 'admin.login', 'admin', $id);
    return true;
}

function adminLogout(): void
{
    if (isAdminLoggedIn()) {
        logActivity((int) $_SESSION['admin']['id'], 'admin.logout', 'admin', (int) $_SESSION['admin']['id']);
    }
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
    session_destroy();
}

function userHasPermission(string $permissionKey): bool
{
    if (!isAdminLoggedIn()) return false;
    $conn = dbConnection();
    $adminId = (int) $_SESSION['admin']['id'];
    $stmt = $conn->prepare('SELECT 1 FROM admin_roles ar JOIN role_permissions rp ON rp.role_id = ar.role_id JOIN permissions p ON p.id = rp.permission_id WHERE ar.admin_id = ? AND p.permission_key = ? LIMIT 1');
    $stmt->bind_param('is', $adminId, $permissionKey);
    $stmt->execute();
    $found = (bool) $stmt->get_result()->fetch_row();
    $stmt->close();
    return $found;
}

function requirePermission(string $permissionKey): void
{
    if (!userHasPermission($permissionKey)) {
        jsonResponse(false, null, 'Forbidden.', 'FORBIDDEN', 403);
    }
}
