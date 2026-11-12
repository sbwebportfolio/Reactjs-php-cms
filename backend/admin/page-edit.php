<?php
require_once __DIR__ . '/modules/pages/page-service.php';
requireAdminAuth();
if (!userHasPermission('pages.manage')) {
    http_response_code(403);
    exit('You do not have permission to manage pages.');
}

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT) ?: null;
$page = $id ? pageModuleLoad($id) : null;
if ($id && !$page) { http_response_code(404); exit('Page not found.'); }
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? null)) { http_response_code(419); exit('Your session expired.'); }
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: null;
    $errors = pageModuleSave($_POST, $id, (int) getAdminSession()['id']);
    if (!$errors) {
        logActivity((int) getAdminSession()['id'], $id ? 'page.updated' : 'page.created', 'page', $id ?: (int) dbConnection()->insert_id);
        redirect('/backend/admin/pages.php?notice=saved');
    }
    $page = $_POST;
}
$page = $page ?? ['id' => '', 'title' => '', 'slug' => '', 'excerpt' => '', 'content' => '', 'banner_image' => '', 'status' => 'draft'];
$pageTitle = $id ? 'Edit Page' : 'Create Page';
include __DIR__ . '/includes/header.php';
?>
<div class="content-wrap"><section class="panel"><form method="post" class="cms-form">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="id" value="<?= (int) ($page['id'] ?? 0) ?>">
<?php foreach ($errors as $error): ?><p class="error-message"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endforeach; ?>
<label>Title<input required maxlength="240" name="title" value="<?= htmlspecialchars((string) ($page['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label>
<label>URL slug<input required maxlength="240" name="slug" value="<?= htmlspecialchars((string) ($page['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="about-us"></label>
<label>Excerpt<textarea name="excerpt" rows="3" maxlength="5000"><?= htmlspecialchars((string) ($page['excerpt'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea></label>
<label>Page content<textarea name="content" rows="14" maxlength="1000000"><?= htmlspecialchars((string) ($page['content'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea><small>Plain text content for this initial module. Rich-text editing will be added with the editor integration.</small></label>
<label>Banner image path<input maxlength="500" name="banner_image" value="<?= htmlspecialchars((string) ($page['banner_image'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="/backend/storage/uploads/banners/example.jpg"></label>
<label>Status<select name="status"><?php foreach (['draft','published','scheduled','archived'] as $s): ?><option value="<?= $s ?>" <?= ($page['status'] ?? 'draft') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></label>
<div class="form-actions"><a class="btn btn-ghost" href="/backend/admin/pages.php">Cancel</a><button class="btn btn-primary" type="submit">Save page</button></div>
</form></section></div>
<?php include __DIR__ . '/includes/footer.php'; ?>
