<?php
$navItems = [
    ['label' => 'Dashboard', 'icon' => '⌂', 'href' => '/backend/admin/dashboard.php'],
    ['label' => 'Pages', 'icon' => '▣', 'href' => '/backend/admin/pages.php'],
    ['label' => 'Projects', 'icon' => '▤', 'href' => '/backend/admin/projects.php'],
    ['label' => 'News', 'icon' => '◌', 'href' => '/backend/admin/news.php'],
    ['label' => 'Media', 'icon' => '◫', 'href' => '/backend/admin/media.php'],
    ['label' => 'Settings', 'icon' => '⚙', 'href' => '/backend/admin/settings.php'],
];
?>
<aside class="sidebar"><div class="brand-block"><div class="brand-mark">C</div><div><strong>Corporate CMS</strong><small>Admin Panel</small></div></div><nav class="nav-menu" aria-label="Sidebar navigation">
<?php foreach($navItems as $item): $current=parse_url($_SERVER['REQUEST_URI']??'',PHP_URL_PATH); ?><a class="nav-item <?= $current===$item['href']?'active':'' ?>" href="<?= htmlspecialchars($item['href'],ENT_QUOTES,'UTF-8') ?>"><span class="nav-icon"><?= htmlspecialchars($item['icon'],ENT_QUOTES,'UTF-8') ?></span><span><?= htmlspecialchars($item['label'],ENT_QUOTES,'UTF-8') ?></span></a><?php endforeach; ?>
</nav></aside>
