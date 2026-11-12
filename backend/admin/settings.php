<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdminAuth();

$pageTitle = 'Settings';
include __DIR__ . '/includes/header.php';
?>
<div class="content-wrap">
    <section class="hero-strip compact">
        <div>
            <p class="eyebrow accent">System</p>
            <h2>Settings</h2>
        </div>
        <a href="#" class="btn btn-primary">Save Changes</a>
    </section>
    <section class="panel">
        <div class="panel-header">
            <h3>Site settings</h3>
        </div>
        <div class="form-grid">
            <label>
                <span>Site name</span>
                <input type="text" value="Corporate CMS" />
            </label>
            <label>
                <span>Contact email</span>
                <input type="email" value="hello@example.com" />
            </label>
        </div>
    </section>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
