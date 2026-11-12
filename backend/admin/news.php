<?php
require_once __DIR__ . '/modules/news/news-service.php';
requireAdminAuth();
if (!userHasPermission('news.manage')) { http_response_code(403); exit('You do not have permission to manage news.'); }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? null)) { http_response_code(419); exit('Your session expired. Reload and try again.'); }
    $adminId = (int) getAdminSession()['id'];
    $action = (string) ($_POST['action'] ?? 'save');
    try {
        if ($action === 'add_category') {
            $name = sanitizeString($_POST['category_name'] ?? '', 160);
            $slug = newsSlug((string) ($_POST['category_slug'] ?? $name));
            if ($name === '' || $slug === '') $errors['category'] = 'Enter a category name and valid slug.';
            else {
                $stmt = dbConnection()->prepare('INSERT INTO news_categories (name,slug) VALUES (?,?)');
                $stmt->bind_param('ss', $name, $slug);
                $stmt->execute(); $stmt->close();
                logActivity($adminId, 'news.category.created', 'news_category', (int) dbConnection()->insert_id);
                redirect('/backend/admin/news.php?notice=category-added');
            }
        } elseif ($action === 'archive') {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) { http_response_code(400); exit('Invalid article.'); }
            $stmt = dbConnection()->prepare("UPDATE news SET status='archived',updated_by=? WHERE id=?");
            $stmt->bind_param('ii', $adminId, $id); $stmt->execute(); $stmt->close();
            logActivity($adminId, 'news.archived', 'news', $id);
            redirect('/backend/admin/news.php?notice=archived');
        } else {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: null;
            $errors = newsSave($_POST, $id, $adminId);
            if (!$errors) {
                $savedId = $id ?? (int) dbConnection()->insert_id;
                logActivity($adminId, $id ? 'news.updated' : 'news.created', 'news', $savedId);
                redirect('/backend/admin/news.php?notice=saved');
            }
            $_SESSION['news_form_errors'] = $errors;
            $_SESSION['news_form_values'] = $_POST;
            redirect('/backend/admin/news-edit.php' . ($id ? '?id=' . $id : ''));
        }
    } catch (Throwable $e) {
        error_log('News operation failed: ' . $e->getMessage());
        $errors['general'] = APP_DEBUG ? $e->getMessage() : 'The news operation could not be completed.';
    }
}

$search = trim((string) ($_GET['search'] ?? ''));
$status = (string) ($_GET['status'] ?? '');
$sql = 'SELECT n.id,n.title,n.slug,n.status,n.is_featured,n.published_at,n.updated_at,c.name AS category_name FROM news n LEFT JOIN news_categories c ON c.id=n.category_id WHERE 1=1';
$params=[]; $types='';
if ($search !== '') { $sql .= ' AND (n.title LIKE ? OR n.slug LIKE ?)'; $term='%'.$search.'%'; $params[]=$term; $params[]=$term; $types.='ss'; }
if (in_array($status,['draft','published','scheduled','archived'],true)) { $sql .= ' AND n.status=?'; $params[]=$status; $types.='s'; }
$sql .= ' ORDER BY n.updated_at DESC LIMIT 100';
$stmt=dbConnection()->prepare($sql); if($params)$stmt->bind_param($types,...$params); $stmt->execute(); $items=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
$pageTitle='News'; include __DIR__ . '/includes/header.php';
?>
<div class="content-wrap">
<section class="hero-strip compact"><div><p class="eyebrow accent">Publishing</p><h2>News articles</h2></div><a class="btn btn-primary" href="/backend/admin/news-edit.php">New article</a></section>
<?php if(isset($_GET['notice'])): ?><p class="panel notice">News <?= htmlspecialchars((string)$_GET['notice'],ENT_QUOTES,'UTF-8') ?>.</p><?php endif; ?>
<?php foreach($errors as $error): ?><p class="panel error-message"><?= htmlspecialchars($error,ENT_QUOTES,'UTF-8') ?></p><?php endforeach; ?>
<section class="panel table-panel"><form class="toolbar" method="get"><input name="search" value="<?= htmlspecialchars($search,ENT_QUOTES,'UTF-8') ?>" placeholder="Search news"><select name="status"><option value="">All statuses</option><?php foreach(['draft','published','scheduled','archived'] as $s): ?><option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select><button class="btn btn-ghost">Filter</button></form>
<div class="table-scroll"><table class="data-table"><thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Featured</th><th>Publication date</th><th>Actions</th></tr></thead><tbody>
<?php if(!$items): ?><tr><td colspan="6">No news articles found.</td></tr><?php endif; ?>
<?php foreach($items as $item): ?><tr><td><?= htmlspecialchars($item['title'],ENT_QUOTES,'UTF-8') ?></td><td><?= htmlspecialchars($item['category_name']??'—',ENT_QUOTES,'UTF-8') ?></td><td><?= htmlspecialchars(ucfirst($item['status']),ENT_QUOTES,'UTF-8') ?></td><td><?= (int)$item['is_featured']?'Yes':'No' ?></td><td><?= htmlspecialchars($item['published_at']??'—',ENT_QUOTES,'UTF-8') ?></td><td class="actions"><a href="/backend/admin/news-edit.php?id=<?= (int)$item['id'] ?>">Edit</a><?php if($item['status']!=='archived'): ?><form method="post"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken(),ENT_QUOTES,'UTF-8') ?>"><input type="hidden" name="action" value="archive"><input type="hidden" name="id" value="<?= (int)$item['id'] ?>"><button type="submit" class="link-button" onclick="return confirm('Archive this article?')">Archive</button></form><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div></section>
<section class="panel"><h3>Add news category</h3><form class="toolbar" method="post"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken(),ENT_QUOTES,'UTF-8') ?>"><input type="hidden" name="action" value="add_category"><input name="category_name" maxlength="160" placeholder="Category name" required><input name="category_slug" maxlength="180" placeholder="category-slug"><button class="btn btn-ghost">Add category</button></form></section>
</div><?php include __DIR__ . '/includes/footer.php'; ?>
