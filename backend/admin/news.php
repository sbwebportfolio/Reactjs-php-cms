<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdminAuth();

$pageTitle = 'News';
include __DIR__ . '/includes/header.php';
?>
<div class="content-wrap">
    <section class="hero-strip compact">
        <div>
            <p class="eyebrow accent">Publishing</p>
            <h2>News</h2>
        </div>
        <a href="#" class="btn btn-primary">New Article</a>
    </section>
    <section class="panel table-panel">
        <div class="panel-header">
            <h3>Latest articles</h3>
            <a href="#">Manage</a>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Published</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Company growth update</td>
                    <td>Insights</td>
                    <td><span class="status-badge">Published</span></td>
                    <td>Today</td>
                </tr>
            </tbody>
        </table>
    </section>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
