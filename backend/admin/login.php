<?php
require_once __DIR__ . '/../includes/auth.php';

if (isAdminLoggedIn()) {
    redirect('/backend/admin/dashboard.php');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!adminLogin($email, $password)) {
            $errors[] = 'Invalid email or password.';
        } else {
            redirect('/backend/admin/dashboard.php');
        }
    }
}

$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f7fb; margin: 0; display: grid; place-items: center; min-height: 100vh; }
        .login-box { width: 100%; max-width: 420px; background: #fff; padding: 32px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        h1 { margin-top: 0; font-size: 28px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; }
        input { width: 100%; box-sizing: border-box; padding: 12px; margin-bottom: 16px; border: 1px solid #dfe3e8; border-radius: 8px; }
        button { width: 100%; padding: 12px; border: none; background: #0d6efd; color: #fff; border-radius: 8px; font-size: 16px; cursor: pointer; }
        .error { background: #f8d7da; color: #842029; padding: 10px; border-radius: 8px; margin-bottom: 16px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>Admin Login</h1>

        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

            <label for="email">Email</label>
            <input id="email" type="email" name="email" required>

            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>

            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
