<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdminAuth();

$admin = getAdminSession();

$stats = [
    ['label' => 'Total Pages', 'value' => '0', 'trend' => '+0%'],
    ['label' => 'Total Projects', 'value' => '0', 'trend' => '+0%'],
    ['label' => 'Total News', 'value' => '0', 'trend' => '+0%'],
    ['label' => 'Total Press Releases', 'value' => '0', 'trend' => '+0%'],
    ['label' => 'Total Blog Posts', 'value' => '0', 'trend' => '+0%'],
    ['label' => 'Total Testimonials', 'value' => '0', 'trend' => '+0%'],
    ['label' => 'Total Contact Inquiries', 'value' => '0', 'trend' => '+0%'],
];

$recentActivity = [
    ['title' => 'Homepage updated', 'meta' => '2 hours ago by ' . ($admin['name'] ?? 'Admin')],
    ['title' => 'Project featured section refreshed', 'meta' => '5 hours ago by ' . ($admin['name'] ?? 'Admin')],
    ['title' => 'New testimonial submitted', 'meta' => 'Yesterday'],
    ['title' => 'News article scheduled', 'meta' => 'Yesterday'],
];

$recentInquiries = [
    ['name' => 'Alicia Stone', 'subject' => 'Website redesign inquiry', 'status' => 'Unread'],
    ['name' => 'Jonathan Marshall', 'subject' => 'Project consultation', 'status' => 'Read'],
    ['name' => 'Priya Nair', 'subject' => 'Partnership request', 'status' => 'Replied'],
    ['name' => 'Mason Reed', 'subject' => 'Press release request', 'status' => 'Unread'],
];

$pageTitle = 'Dashboard';
include __DIR__ . '/includes/header.php';
?>
<div class="content-wrap">
    <section class="hero-strip">
        <div>
            <p class="eyebrow accent">Overview</p>
            <h2>Welcome back, <?= htmlspecialchars($admin['name'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></h2>
        </div>
        <a href="#" class="btn btn-primary">Quick Action</a>
    </section>

    <section class="stats-grid" aria-label="Dashboard statistics">
        <?php foreach ($stats as $card): ?>
            <article class="stat-card">
                <div class="stat-label"><?= htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8') ?></div>
                <div class="stat-value"><?= htmlspecialchars($card['value'], ENT_QUOTES, 'UTF-8') ?></div>
                <div class="stat-trend"><?= htmlspecialchars($card['trend'], ENT_QUOTES, 'UTF-8') ?></div>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="two-column-grid">
        <article class="panel">
            <div class="panel-header">
                <h3>Recent Activity</h3>
                <a href="#">View all</a>
            </div>
            <ul class="activity-list">
                <?php foreach ($recentActivity as $item): ?>
                    <li>
                        <span class="activity-dot"></span>
                        <div>
                            <strong><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <small><?= htmlspecialchars($item['meta'], ENT_QUOTES, 'UTF-8') ?></small>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </article>

        <article class="panel">
            <div class="panel-header">
                <h3>Recent Contact Submissions</h3>
                <a href="#">View all</a>
            </div>
            <div class="inquiry-list">
                <?php foreach ($recentInquiries as $inquiry): ?>
                    <div class="inquiry-item">
                        <div>
                            <strong><?= htmlspecialchars($inquiry['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <small><?= htmlspecialchars($inquiry['subject'], ENT_QUOTES, 'UTF-8') ?></small>
                        </div>
                        <span class="status-badge"><?= htmlspecialchars($inquiry['status'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>
    </section>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
