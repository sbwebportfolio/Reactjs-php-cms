<?php
/**
 * Admin authentication helpers.
 */

require_once __DIR__ . '/functions.php';

function requireAdminAuth(): void
{
    if (!isAdminLoggedIn()) {
        redirect('/backend/admin/login.php');
    }
}

function isAdminLoggedIn(): bool
{
    return !empty($_SESSION['admin']['id']) && !empty($_SESSION['admin']['email']);
}

function getAdminSession(): ?array
{
    return $_SESSION['admin'] ?? null;
}

function adminLogin(string $email, string $password): bool
{
    $email = sanitizeEmail($email);
    if ($email === '' || $password === '') {
        return false;
    }

    try {
        $conn = dbConnection();
        $stmt = $conn->prepare('SELECT id, name, email, password_hash, status FROM admins WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        $stmt->close();

        if (!$admin) {
            return false;
        }

        if ($admin['status'] !== 'active') {
            return false;
        }

        if (!password_verify($password, $admin['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['admin'] = [
            'id' => (int) $admin['id'],
            'name' => $admin['name'],
            'email' => $admin['email'],
            'login_time' => time(),
        ];

        $stmt = $conn->prepare('UPDATE admins SET last_login_at = NOW() WHERE id = ?');
        $stmt->bind_param('i', $admin['id']);
        $stmt->execute();
        $stmt->close();

        return true;
    } catch (Throwable $e) {
        error_log('Admin login failed: ' . $e->getMessage());
        return false;
    }
}

function adminLogout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
}

function userHasPermission(string $permissionKey): bool
{
    if (!isAdminLoggedIn()) {
        return false;
    }

    $adminId = (int) $_SESSION['admin']['id'];

    try {
        $conn = dbConnection();
        $stmt = $conn->prepare(
            'SELECT COUNT(*) AS total
             FROM role_permissions rp
             INNER JOIN admin_roles ar ON ar.role_id = rp.role_id
             INNER JOIN permissions p ON p.id = rp.permission_id
             WHERE ar.admin_id = ? AND p.permission_key = ?'
        );
        $stmt->bind_param('is', $adminId, $permissionKey);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return !empty($row['total']) && (int) $row['total'] > 0;
    } catch (Throwable $e) {
        error_log('Permission check failed: ' . $e->getMessage());
        return false;
    }
}

function requirePermission(string $permissionKey): void
{
    if (!userHasPermission($permissionKey)) {
        http_response_code(403);
        jsonResponse(false, null, 'Forbidden. You do not have permission to access this resource.', 'FORBIDDEN', 403);
    }
}
