<?php
require_once __DIR__ . '/modules/projects/project-service.php';
requireAdminAuth();
if (!function_exists('userHasPermission') || !userHasPermission('projects.manage')) { http_response_code(403); exit('You do not have permission to manage projects.'); }
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT) ?: null;
$project = $id ? projectLoad($id) : null;
if ($id && !$project) { http_response_code(404); exit('Project not found.'); }
$errors = $_SESSION['project_form_errors'] ?? []; $values = $_SESSION['project_form_values'] ?? null; unset($_SESSION['project_form_errors'], $_SESSION['project_form_values']);
if ($values) $project = array_merge($project ?? [], $values);
$categories = dbConnection()->query('SELECT id,name FROM project_categories ORDER BY name')->fetch_all(MYSQLI_ASSOC);
$project = $project ?? ['id'=>0,'title'=>'','slug'=>'','excerpt'=>'','content'=>'','location'=>'','client_name'=>'','start_date'=>'','completion_date'=>'','project_status'=>'','featured_image'=>'','is_featured'=>0,'status'=>'draft','category_id'=>null];
$pageTitle = $id ? 'Edit Project' : 'Create Project'; include __DIR__ . '/includes/header.php';
?>
<div class="content-wrap"><section class="panel"><form method="post" action="/backend/admin/projects.php" class="cms-form">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken(),ENT_QUOTES,'UTF-8') ?>"><input type="hidden" name="id" value="<?= (int)($project['id']??0) ?>">
<?php foreach($errors as $error): ?><p class="error-message"><?= htmlspecialchars($error,ENT_QUOTES,'UTF-8') ?></p><?php endforeach; ?>
<label>Project title<input required maxlength="240" name="title" value="<?= htmlspecialchars((string)($project['title']??''),ENT_QUOTES,'UTF-8') ?>"></label>
<label>URL slug<input required maxlength="240" name="slug" value="<?= htmlspecialchars((string)($project['slug']??''),ENT_QUOTES,'UTF-8') ?>"></label>
<label>Category<select name="category_id"><option value="">Uncategorized</option><?php foreach($categories as $c): ?><option value="<?= (int)$c['id'] ?>" <?= (string)($project['category_id']??'')===(string)$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name'],ENT_QUOTES,'UTF-8') ?></option><?php endforeach; ?></select></label>
<label>Summary<textarea name="excerpt" rows="3" maxlength="5000"><?= htmlspecialchars((string)($project['excerpt']??''),ENT_QUOTES,'UTF-8') ?></textarea></label>
<label>Description<textarea name="content" rows="10" maxlength="1000000"><?= htmlspecialchars((string)($project['content']??''),ENT_QUOTES,'UTF-8') ?></textarea></label>
<div class="form-grid"><label>Location<input maxlength="240" name="location" value="<?= htmlspecialchars((string)($project['location']??''),ENT_QUOTES,'UTF-8') ?>"></label><label>Client<input maxlength="200" name="client_name" value="<?= htmlspecialchars((string)($project['client_name']??''),ENT_QUOTES,'UTF-8') ?>"></label><label>Project status<input maxlength="80" name="project_status" value="<?= htmlspecialchars((string)($project['project_status']??''),ENT_QUOTES,'UTF-8') ?>"></label><label>Featured image path<input maxlength="500" name="featured_image" value="<?= htmlspecialchars((string)($project['featured_image']??''),ENT_QUOTES,'UTF-8') ?>"></label><label>Start date<input type="date" name="start_date" value="<?= htmlspecialchars((string)($project['start_date']??''),ENT_QUOTES,'UTF-8') ?>"></label><label>Completion date<input type="date" name="completion_date" value="<?= htmlspecialchars((string)($project['completion_date']??''),ENT_QUOTES,'UTF-8') ?>"></label></div>
<label class="checkbox-label"><input type="checkbox" name="is_featured" value="1" <?= !empty($project['is_featured'])?'checked':'' ?>> Feature this project</label>
<label>Publication status<select name="status"><?php foreach(['draft','published','archived'] as $s): ?><option value="<?= $s ?>" <?= ($project['status']??'draft')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></label>
<div class="form-actions"><a class="btn btn-ghost" href="/backend/admin/projects.php">Cancel</a><button class="btn btn-primary">Save project</button></div>
</form></section></div>
<?php include __DIR__ . '/includes/footer.php'; ?>
