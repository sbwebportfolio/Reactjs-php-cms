<?php
require_once __DIR__ . '/modules/news/news-service.php';
requireAdminAuth();
if (!userHasPermission('news.manage')) { http_response_code(403); exit('You do not have permission to manage news.'); }
$id=filter_var($_GET['id']??null,FILTER_VALIDATE_INT)?:null;
$item=$id?newsLoad($id):null;
if($id&&!$item){http_response_code(404);exit('News article not found.');}
$errors=$_SESSION['news_form_errors']??[];$values=$_SESSION['news_form_values']??null;unset($_SESSION['news_form_errors'],$_SESSION['news_form_values']);
if($values)$item=array_merge($item??[],$values);
$item=$item??['id'=>0,'category_id'=>null,'title'=>'','slug'=>'','excerpt'=>'','content'=>'','featured_image'=>'','is_featured'=>0,'status'=>'draft','published_at'=>''];
$categories=newsCategories();
$pageTitle=$id?'Edit News':'Create News';include __DIR__.'/includes/header.php';
$publishedValue='';if(!empty($item['published_at']))$publishedValue=str_replace(' ','T',substr((string)$item['published_at'],0,16));
?>
<div class="content-wrap"><section class="panel"><form method="post" action="/backend/admin/news.php" class="cms-form">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken(),ENT_QUOTES,'UTF-8') ?>"><input type="hidden" name="id" value="<?= (int)($item['id']??0) ?>">
<?php foreach($errors as $error): ?><p class="error-message"><?= htmlspecialchars($error,ENT_QUOTES,'UTF-8') ?></p><?php endforeach; ?>
<label>Title<input required maxlength="240" name="title" value="<?= htmlspecialchars((string)($item['title']??''),ENT_QUOTES,'UTF-8') ?>"></label>
<label>URL slug<input required maxlength="240" name="slug" value="<?= htmlspecialchars((string)($item['slug']??''),ENT_QUOTES,'UTF-8') ?>"></label>
<label>Category<select name="category_id"><option value="">Uncategorized</option><?php foreach($categories as $category): ?><option value="<?= (int)$category['id'] ?>" <?= (string)($item['category_id']??'')===(string)$category['id']?'selected':'' ?>><?= htmlspecialchars($category['name'],ENT_QUOTES,'UTF-8') ?></option><?php endforeach; ?></select></label>
<label>Excerpt<textarea name="excerpt" rows="3" maxlength="5000"><?= htmlspecialchars((string)($item['excerpt']??''),ENT_QUOTES,'UTF-8') ?></textarea></label>
<label>Article content<textarea required name="content" rows="14" maxlength="1000000"><?= htmlspecialchars((string)($item['content']??''),ENT_QUOTES,'UTF-8') ?></textarea><small>Plain text content. Rich-text editor integration can be added later.</small></label>
<label>Featured image path<input maxlength="500" name="featured_image" value="<?= htmlspecialchars((string)($item['featured_image']??''),ENT_QUOTES,'UTF-8') ?>"></label>
<label>Publication date<input type="datetime-local" name="published_at" value="<?= htmlspecialchars($publishedValue,ENT_QUOTES,'UTF-8') ?>"></label>
<label class="checkbox-label"><input type="checkbox" name="is_featured" value="1" <?= !empty($item['is_featured'])?'checked':'' ?>> Feature this article</label>
<label>Status<select name="status"><?php foreach(['draft','published','scheduled','archived'] as $s): ?><option value="<?= $s ?>" <?= ($item['status']??'draft')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></label>
<div class="form-actions"><a class="btn btn-ghost" href="/backend/admin/news.php">Cancel</a><button class="btn btn-primary">Save news</button></div>
</form></section></div><?php include __DIR__.'/includes/footer.php'; ?>
