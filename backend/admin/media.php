<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdminAuth();

$pageTitle = 'Media';
include __DIR__ . '/includes/header.php';
?>
<div class="content-wrap">
    <section class="hero-strip compact">
        <div>
            <p class="eyebrow accent">Assets</p>
            <h2>Media Library</h2>
        </div>
        <a href="#" class="btn btn-primary">Upload Files</a>
    </section>
    <section class="panel table-panel">
        <div class="panel-header">
            <h3>Recently uploaded</h3>
            <a href="#">Browse</a>
        </div>
        <div class="media-grid">
            <div class="media-card">
                <div class="media-thumb blue">IMG</div>
                <strong>banner-1.jpg</strong>
                <small>1.2 MB</small>
            </div>
            <div class="media-card">
                <div class="media-thumb green">PDF</div>
                <strong>company-profile.pdf</strong>
                <small>420 KB</small>
            </div>
        </div>
    </section>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
