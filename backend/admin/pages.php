<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdminAuth();

$pageTitle = 'Pages';
include __DIR__ . '/includes/header.php';
?>
<div class="content-wrap">
    <section class="hero-strip compact">
        <div>
            <p class="eyebrow accent">Content</p>
            <h2>Pages</h2>
        </div>
        <a href="#" class="btn btn-primary">Create Page</a>
    </section>

    <section class="panel table-panel">
        <div class="panel-header">
            <h3>Published page list</h3>
            <a href="#">Manage</a>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <th>Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Home</td>
                    <td>/</td>
                    <td><span class="status-badge">Published</span></td>
                    <td>Today</td>
                    <td><a href="#">Edit</a></td>
                </tr>
                <tr>
                    <td>About Us</td>
                    <td>/about-us</td>
                    <td><span class="status-badge">Published</span></td>
                    <td>Yesterday</td>
                    <td><a href="#">Edit</a></td>
                </tr>
            </tbody>
        </table>
    </section>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
