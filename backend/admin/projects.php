<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdminAuth();

$pageTitle = 'Projects';
include __DIR__ . '/includes/header.php';
?>
<div class="content-wrap">
    <section class="hero-strip compact">
        <div>
            <p class="eyebrow accent">Portfolio</p>
            <h2>Projects</h2>
        </div>
        <a href="#" class="btn btn-primary">Add Project</a>
    </section>

    <section class="panel table-panel">
        <div class="panel-header">
            <h3>Project management</h3>
            <a href="#">Filters</a>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Updated</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Urban Plaza Redevelopment</td>
                    <td>Commercial</td>
                    <td><span class="status-badge">Published</span></td>
                    <td>Today</td>
                </tr>
            </tbody>
        </table>
    </section>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
