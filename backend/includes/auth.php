<?php
/** Role-based access helpers used by PHP admin modules. */
require_once __DIR__ . '/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params(['lifetime'=>SESSION_LIFETIME,'path'=>'/','httponly'=>true,'samesite'=>'Lax']);
    session_start();
}

function isAdminLoggedIn(): bool
{
    return !empty($_SESSION['admin']['id']) && !empty($_SESSION['admin']['email']);
}

function getAdminSession(): ?array { return $_SESSION['admin'] ?? null; }

function requireAdminAuth(): void
{
    if (!isAdminLoggedIn()) redirect('/backend/admin/login.php');
}

function userHasPermission(string $permissionKey): bool
{
    if (!isAdminLoggedIn()) return false;
    $adminId=(int)$_SESSION['admin']['id'];
    $stmt=dbConnection()->prepare('SELECT 1 FROM admin_roles ar JOIN role_permissions rp ON rp.role_id=ar.role_id JOIN permissions p ON p.id=rp.permission_id WHERE ar.admin_id=? AND p.permission_key=? LIMIT 1');
    $stmt->bind_param('is',$adminId,$permissionKey);$stmt->execute();$found=(bool)$stmt->get_result()->fetch_row();$stmt->close();
    return $found;
}
