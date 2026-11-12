<?php
/** Admin login page with CSRF validation and generic authentication errors. */
require_once __DIR__ . '/../includes/auth.php';

if (isAdminLoggedIn()) redirect('/backend/admin/dashboard.php');
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Reload the page and try again.';
    } else {
        try {
            if (adminLogin((string) ($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''))) {
                redirect('/backend/admin/dashboard.php');
            }
            $errors[] = 'Invalid email or password, or this account is temporarily locked.';
        } catch (Throwable $e) {
            error_log('Admin login error: ' . $e->getMessage());
            $errors[] = APP_DEBUG ? $e->getMessage() : 'Login is temporarily unavailable.';
        }
    }
}
$csrfToken = generateCsrfToken();
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Admin Login</title>
<style>body{font-family:system-ui,sans-serif;background:#f3f5f9;margin:0;display:grid;place-items:center;min-height:100vh}.box{width:min(420px,calc(100% - 40px));background:white;padding:32px;border-radius:14px;box-shadow:0 12px 40px #17203318}label{display:block;font-weight:600;margin:14px 0 6px}input{box-sizing:border-box;width:100%;padding:12px;border:1px solid #ccd2dc;border-radius:8px}button{width:100%;margin-top:20px;padding:12px;border:0;border-radius:8px;background:#1d4ed8;color:#fff;font-weight:700}.error{padding:12px;background:#fee2e2;color:#991b1b;border-radius:8px}</style></head>
<body><main class="box"><h1>Admin sign in</h1><?php foreach($errors as $error): ?><p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endforeach; ?><form method="post"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>"><label for="email">Email</label><input id="email" name="email" type="email" autocomplete="username" required maxlength="190"><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password" required><button type="submit">Sign in</button></form></main></body></html>
