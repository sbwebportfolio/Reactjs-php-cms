<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdminAuth();

$admin = getAdminSession();
$pageTitle = $pageTitle ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> | Corporate CMS</title>
    <link rel="stylesheet" href="/backend/admin/assets/css/admin.css">
</head>
<body>
    <div class="admin-shell">
        <?php include __DIR__ . '/sidebar.php'; ?>
        <main class="main-panel">
            <header class="topbar">
                <div><p class="eyebrow">Administration</p><h1><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1></div>
                <div class="topbar-actions"><span class="badge">Logged in as <?= htmlspecialchars($admin['name'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></span>
                    <form method="POST" action="/backend/admin/logout.php"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8') ?>"><button type="submit" class="btn btn-ghost">Logout</button></form>
                </div>
            </header>
