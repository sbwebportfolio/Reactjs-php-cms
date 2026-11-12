<?php
$navItems = [
    ['label' => 'Dashboard', 'icon' => '⌂', 'href' => '/backend/admin/dashboard.php'],
    ['label' => 'Pages', 'icon' => '▣', 'href' => '/backend/admin/pages.php'],
    ['label' => 'Homepage', 'icon' => '◫', 'href' => '/backend/admin/homepage.php'],
    ['label' => 'Projects', 'icon' => '▤', 'href' => '/backend/admin/projects.php'],
    ['label' => 'News', 'icon' => '◌', 'href' => '/backend/admin/news.php'],
    ['label' => 'Press Releases', 'icon' => '◍', 'href' => '/backend/admin/press.php'],
    ['label' => 'Blog', 'icon' => '✎', 'href' => '/backend/admin/blog.php'],
    ['label' => 'Testimonials', 'icon' => '★', 'href' => '/backend/admin/testimonials.php'],
    ['label' => 'Media', 'icon' => '◫', 'href' => '/backend/admin/media.php'],
    ['label' => 'Contact Inquiries', 'icon' => '✉', 'href' => '/backend/admin/inquiries.php'],
    ['label' => 'Users', 'icon' => '👤', 'href' => '/backend/admin/users.php'],
    ['label' => 'Settings', 'icon' => '⚙', 'href' => '/backend/admin/settings.php'],
];
?>
<aside class="sidebar">
    <div class="brand-block">
        <div class="brand-mark">C</div>
        <div>
            <strong>Corporate CMS</strong>
            <small>Admin Panel</small>
        </div>
    </div>

    <nav class="nav-menu" aria-label="Sidebar navigation">
        <?php foreach ($navItems as $item): ?>
            <?php $isActive = ((string) ($_SERVER['REQUEST_URI'] ?? '')) === $item['href'] || strpos((string) ($_SERVER['REQUEST_URI'] ?? ''), basename($item['href'])) !== false; ?>
            <a class="nav-item <?= $isActive ? 'active' : '' ?>" href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>">
                <span class="nav-icon"><?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?></span>
                <span><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
</aside>
