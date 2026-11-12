<?php
require_once __DIR__ . '/modules/pages/page-service.php';
requireAdminAuth();
if (!userHasPermission('pages.manage')) {
    http_response_code(403);
    exit('You do not have permission to manage pages.');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(419);
        exit('Your session expired. Reload and try again.');
    }
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: null;
    $action = (string) ($_POST['action'] ?? 'save');
    $adminId = (int) getAdminSession()['id'];
    try {
        if ($action === 'archive' && $id !== null) {
            $stmt = dbConnection()->prepare("UPDATE pages SET status = 'archived', updated_by = ? WHERE id = ?");
            $stmt->bind_param('ii', $adminId, $id);
            $stmt->execute();
            $stmt->close();
            logActivity($adminId, 'page.archived', 'page', $id);
            redirect('/backend/admin/pages.php?notice=archived');
        }
        $errors = pageModuleSave($_POST, $id, $adminId);
        if (!$errors) {
            $savedId = $id ?? (int) dbConnection()->insert_id;
            logActivity($adminId, $id === null ? 'page.created' : 'page.updated', 'page', $savedId);
            redirect('/backend/admin/pages.php?notice=saved');
        }
    } catch (Throwable $e) {
        error_log('Page save failed: ' . $e->getMessage());
        $errors['general'] = APP_DEBUG ? $e->getMessage() : 'Could not save this page.';
    }
}

$search = trim((string) ($_GET['search'] ?? ''));
$status = (string) ($_GET['status'] ?? '');
$sql = 'SELECT id, title, slug, status, updated_at FROM pages WHERE 1=1';
$params = [];
$types = '';
if ($search !== '') {
    $sql .= ' AND (title LIKE ? OR slug LIKE ?)';
    $term = '%' . $search . '%';
    $params[] = $term; $params[] = $term; $types .= 'ss';
}
if (in_array($status, ['draft', 'published', 'scheduled', 'archived'], true)) {
    $sql .= ' AND status = ?'; $params[] = $status; $types .= 's';
}
$sql .= ' ORDER BY updated_at DESC LIMIT 100';
$stmt = dbConnection()->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$pages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = 'Pages';
include __DIR__ . '/includes/header.php';
?>
<div class="content-wrap">
    <section class="hero-strip compact"><div><p class="eyebrow accent">Content</p><h2>Manage pages</h2></div><a href="/backend/admin/page-edit.php" class="btn btn-primary">Create page</a></section>
    <?php if (isset($_GET['notice'])): ?><p class="panel notice">Page <?= htmlspecialchars((string) $_GET['notice'], ENT_QUOTES, 'UTF-8') ?>.</p><?php endif; ?>
    <?php foreach ($errors as $error): ?><p class="panel error-message"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endforeach; ?>
    <section class="panel table-panel">
        <form method="get" class="toolbar"><input name="search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Search pages"><select name="status"><option value="">All statuses</option><?php foreach (['draft','published','scheduled','archived'] as $s): ?><option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select><button class="btn btn-ghost" type="submit">Filter</button></form>
        <div class="table-scroll"><table class="data-table"><thead><tr><th>Title</th><th>Slug</th><th>Status</th><th>Updated</th><th>Actions</th></tr></thead><tbody>
        <?php if (!$pages): ?><tr><td colspan="5">No pages found.</td></tr><?php endif; ?>
        <?php foreach ($pages as $page): ?><tr><td><?= htmlspecialchars($page['title'], ENT_QUOTES, 'UTF-8') ?></td><td>/<?= htmlspecialchars($page['slug'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars(ucfirst($page['status']), ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($page['updated_at'], ENT_QUOTES, 'UTF-8') ?></td><td class="actions"><a href="/backend/admin/page-edit.php?id=<?= (int) $page['id'] ?>">Edit</a><?php if ($page['status'] !== 'archived'): ?><form method="post" action="/backend/admin/pages.php" onsubmit="return confirm('Archive this page?')"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="id" value="<?= (int) $page['id'] ?>"><input type="hidden" name="action" value="archive"><button class="link-button" type="submit">Archive</button></form><?php endif; ?></td></tr><?php endforeach; ?>
        </tbody></table></div>
    </section>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
