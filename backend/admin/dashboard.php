<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdminAuth();

$pageTitle = 'Dashboard';
include __DIR__ . '/includes/header.php';
?>
<div class="content-wrap">
    <section class="hero-strip">
        <div>
            <p class="eyebrow accent">Overview</p>
            <h2>Welcome back, <?= htmlspecialchars(getAdminSession()['name'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></h2>
        </div>
        <a href="/backend/admin/pages.php" class="btn btn-primary">Quick Action</a>
    </section>

    <section class="stats-grid" aria-label="Dashboard statistics">
        <article class="stat-card">
            <div class="stat-label">Total Pages</div>
            <div class="stat-value">0</div>
            <div class="stat-trend">+0%</div>
        </article>
        <article class="stat-card">
            <div class="stat-label">Total Projects</div>
            <div class="stat-value">0</div>
            <div class="stat-trend">+0%</div>
        </article>
        <article class="stat-card">
            <div class="stat-label">Total News</div>
            <div class="stat-value">0</div>
            <div class="stat-trend">+0%</div>
        </article>
        <article class="stat-card">
            <div class="stat-label">Total Blog Posts</div>
            <div class="stat-value">0</div>
            <div class="stat-trend">+0%</div>
        </article>
        <article class="stat-card">
            <div class="stat-label">Total Inquiries</div>
            <div class="stat-value">0</div>
            <div class="stat-trend">+0%</div>
        </article>
    </section>

    <section class="two-column-grid">
        <article class="panel">
            <div class="panel-header">
                <h3>Recent Activity</h3>
                <a href="#">View all</a>
            </div>
            <ul class="activity-list">
                <li>
                    <span class="activity-dot"></span>
                    <div>
                        <strong>Homepage updated</strong>
                        <small>2 hours ago by <?= htmlspecialchars(getAdminSession()['name'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></small>
                    </div>
                </li>
                <li>
                    <span class="activity-dot"></span>
                    <div>
                        <strong>Project section refreshed</strong>
                        <small>5 hours ago by <?= htmlspecialchars(getAdminSession()['name'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></small>
                    </div>
                </li>
                <li>
                    <span class="activity-dot"></span>
                    <div>
                        <strong>New testimonial submitted</strong>
                        <small>Yesterday</small>
                    </div>
                </li>
            </ul>
        </article>

        <article class="panel">
            <div class="panel-header">
                <h3>Recent Contact Submissions</h3>
                <a href="#">View all</a>
            </div>
            <div class="inquiry-list">
                <div class="inquiry-item">
                    <div>
                        <strong>Alicia Stone</strong>
                        <small>Website redesign inquiry</small>
                    </div>
                    <span class="status-badge">Unread</span>
                </div>
                <div class="inquiry-item">
                    <div>
                        <strong>Jonathan Marshall</strong>
                        <small>Project consultation</small>
                    </div>
                    <span class="status-badge">Read</span>
                </div>
                <div class="inquiry-item">
                    <div>
                        <strong>Priya Nair</strong>
                        <small>Partnership request</small>
                    </div>
                    <span class="status-badge">Replied</span>
                </div>
            </div>
        </article>
    </section>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
