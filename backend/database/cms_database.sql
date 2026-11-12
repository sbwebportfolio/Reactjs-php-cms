<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdminAuth();

$admin = getAdminSession();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f4f7fb; }
        .topbar { background: #111827; color: white; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; }
        .nav { display: flex; gap: 16px; align-items: center; }
        .wrap { padding: 32px; }
        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; }
        .card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.05); }
        .card h3 { margin-top: 0; }
        .logout { color: white; text-decoration: none; }
    </style>
</head>
<body>
    <div class="topbar">
        <div><strong>Corporate CMS</strong></div>
        <div class="nav">
            <span>Welcome, <?php echo htmlspecialchars($admin['name']); ?></span>
            <a class="logout" href="/backend/admin/logout.php">Logout</a>
        </div>
    </div>

    <div class="wrap">
        <h1>Dashboard</h1>
        <div class="cards">
            <div class="card">
                <h3>Total Pages</h3>
                <div>0</div>
            </div>
            <div class="card">
                <h3>Total Projects</h3>
                <div>0</div>
            </div>
            <div class="card">
                <h3>Total News</h3>
                <div>0</div>
            </div>
            <div class="card">
                <h3>Total Inquiries</h3>
                <div>0</div>
            </div>
        </div>
    </div>
</body>
</html>
