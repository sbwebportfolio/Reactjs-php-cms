<?php
require_once __DIR__ . '/modules/blog/blog-service.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminAuth();
if(!userHasPermission('blog.manage')){http_response_code(403);exit('You do not have permission to manage blog posts.');}
$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!validateCsrfToken($_POST['csrf_token']??null)){http_response_code(419);exit('Your session expired. Reload and try again.');}
    $adminId=(int)getAdminSession()['id'];$action=(string)($_POST['action']??'save');
    if($action==='archive'){
        $id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);if(!$id){http_response_code(400);exit('Invalid post.');}
        $stmt=dbConnection()->prepare("UPDATE blog_posts SET status='archived' WHERE id=?");$stmt->bind_param('i',$id);$stmt->execute();$stmt->close();logActivity($adminId,'blog.archived','blog_post',$id);redirect('/backend/admin/blog.php?notice=archived');
    }
    $id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT)?:null;
    try{$errors=blogSave($_POST,$id,$adminId);if(!$errors){$saved=$id??(int)dbConnection()->insert_id;logActivity($adminId,$id?'blog.updated':'blog.created','blog_post',$saved);redirect('/backend/admin/blog.php?notice=saved');}}
    catch(Throwable $e){error_log('Blog save failed: '.$e->getMessage());$errors['general']=APP_DEBUG?$e->getMessage():'Could not save blog post.';}
    $_SESSION['blog_form_errors']=$errors;$_SESSION['blog_form_values']=$_POST;redirect('/backend/admin/blog-edit.php'.($id?'?id='.$id:''));
}
$search=trim((string)($_GET['search']??''));$status=(string)($_GET['status']??'');
$sql='SELECT b.id,b.title,b.slug,b.status,b.published_at,b.updated_at,c.name category_name FROM blog_posts b LEFT JOIN blog_categories c ON c.id=b.category_id WHERE 1=1';$params=[];$types='';
if($search!==''){$sql.=' AND (b.title LIKE ? OR b.slug LIKE ?)';$term='%'.$search.'%';$params[]=$term;$params[]=$term;$types.='ss';}
if(in_array($status,['draft','published','scheduled','archived'],true)){$sql.=' AND b.status=?';$params[]=$status;$types.='s';}
$sql.=' ORDER BY b.updated_at DESC LIMIT 100';$stmt=dbConnection()->prepare($sql);if($params)$stmt->bind_param($types,...$params);$stmt->execute();$posts=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);$stmt->close();
$pageTitle='Blog';include __DIR__.'/includes/header.php';
?>
<div class="content-wrap"><section class="hero-strip compact"><div><p class="eyebrow accent">Publishing</p><h2>Blog posts</h2></div><a class="btn btn-primary" href="/backend/admin/blog-edit.php">New post</a></section>
<?php if(isset($_GET['notice'])): ?><p class="panel notice">Post <?= htmlspecialchars((string)$_GET['notice'],ENT_QUOTES,'UTF-8') ?>.</p><?php endif; ?>
<section class="panel table-panel"><form class="toolbar" method="get"><input name="search" value="<?= htmlspecialchars($search,ENT_QUOTES,'UTF-8') ?>" placeholder="Search blog posts"><select name="status"><option value="">All statuses</option><?php foreach(['draft','published','scheduled','archived'] as $s): ?><option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select><button class="btn btn-ghost">Filter</button></form><div class="table-scroll"><table class="data-table"><thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Publication date</th><th>Actions</th></tr></thead><tbody>
<?php if(!$posts): ?><tr><td colspan="5">No blog posts found.</td></tr><?php endif; ?><?php foreach($posts as $post): ?><tr><td><?= htmlspecialchars($post['title'],ENT_QUOTES,'UTF-8') ?></td><td><?= htmlspecialchars($post['category_name']??'—',ENT_QUOTES,'UTF-8') ?></td><td><?= htmlspecialchars(ucfirst($post['status']),ENT_QUOTES,'UTF-8') ?></td><td><?= htmlspecialchars($post['published_at']??'—',ENT_QUOTES,'UTF-8') ?></td><td class="actions"><a href="/backend/admin/blog-edit.php?id=<?= (int)$post['id'] ?>">Edit</a><?php if($post['status']!=='archived'): ?><form method="post"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken(),ENT_QUOTES,'UTF-8') ?>"><input type="hidden" name="action" value="archive"><input type="hidden" name="id" value="<?= (int)$post['id'] ?>"><button class="link-button" onclick="return confirm('Archive this post?')">Archive</button></form><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div></section></div><?php include __DIR__.'/includes/footer.php'; ?>
