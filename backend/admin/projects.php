<?php
require_once __DIR__ . '/modules/projects/project-service.php';
requireAdminAuth();
if (!function_exists('userHasPermission') || !userHasPermission('projects.manage')) {
    http_response_code(403);
    exit('You do not have permission to manage projects.');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? null)) { http_response_code(419); exit('Your session expired. Reload and try again.'); }
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: null;
    $action = (string) ($_POST['action'] ?? 'save');
    $adminId = (int) getAdminSession()['id'];
    if ($action === 'archive' && $id) {
        $stmt = dbConnection()->prepare("UPDATE projects SET status='archived',updated_by=? WHERE id=?");
        $stmt->bind_param('ii', $adminId, $id); $stmt->execute(); $stmt->close();
        logActivity($adminId, 'project.archived', 'project', $id);
        redirect('/backend/admin/projects.php?notice=archived');
    }
    $errors = projectSave($_POST, $id, $adminId);
    if (!$errors) {
        $savedId = $id ?? (int) dbConnection()->insert_id;
        logActivity($adminId, $id ? 'project.updated' : 'project.created', 'project', $savedId);
        redirect('/backend/admin/projects.php?notice=saved');
    }
    $_SESSION['project_form_errors'] = $errors;
    $_SESSION['project_form_values'] = $_POST;
    redirect('/backend/admin/project-edit.php' . ($id ? '?id=' . $id : ''));
}

$search = trim((string) ($_GET['search'] ?? ''));
$status = (string) ($_GET['status'] ?? '');
$sql = 'SELECT p.id,p.title,p.slug,p.status,p.is_featured,p.updated_at,c.name AS category_name FROM projects p LEFT JOIN project_categories c ON c.id=p.category_id WHERE 1=1';
$params = []; $types = '';
if ($search !== '') { $sql .= ' AND (p.title LIKE ? OR p.slug LIKE ? OR p.location LIKE ?)'; $term = '%' . $search . '%'; array_push($params, $term, $term, $term); $types .= 'sss'; }
if (in_array($status, ['draft','published','archived'], true)) { $sql .= ' AND p.status=?'; $params[] = $status; $types .= 's'; }
$sql .= ' ORDER BY p.updated_at DESC LIMIT 100';
$stmt = dbConnection()->prepare($sql); if ($params) $stmt->bind_param($types, ...$params); $stmt->execute(); $projects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
$pageTitle = 'Projects'; include __DIR__ . '/includes/header.php';
?>
<div class="content-wrap">
<section class="hero-strip compact"><div><p class="eyebrow accent">Portfolio</p><h2>Manage projects</h2></div><a class="btn btn-primary" href="/backend/admin/project-edit.php">Add project</a></section>
<?php if (isset($_GET['notice'])): ?><p class="panel notice">Project <?= htmlspecialchars((string) $_GET['notice'], ENT_QUOTES, 'UTF-8') ?>.</p><?php endif; ?>
<section class="panel table-panel"><form class="toolbar" method="get"><input name="search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Search title, slug or location"><select name="status"><option value="">All statuses</option><?php foreach(['draft','published','archived'] as $s): ?><option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select><button class="btn btn-ghost">Filter</button></form>
<div class="table-scroll"><table class="data-table"><thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Featured</th><th>Updated</th><th>Actions</th></tr></thead><tbody>
<?php if (!$projects): ?><tr><td colspan="6">No projects found.</td></tr><?php endif; ?>
<?php foreach($projects as $p): ?><tr><td><?= htmlspecialchars($p['title'],ENT_QUOTES,'UTF-8') ?></td><td><?= htmlspecialchars($p['category_name'] ?? '—',ENT_QUOTES,'UTF-8') ?></td><td><?= htmlspecialchars(ucfirst($p['status']),ENT_QUOTES,'UTF-8') ?></td><td><?= (int)$p['is_featured'] ? 'Yes' : 'No' ?></td><td><?= htmlspecialchars($p['updated_at'],ENT_QUOTES,'UTF-8') ?></td><td class="actions"><a href="/backend/admin/project-edit.php?id=<?= (int)$p['id'] ?>">Edit</a><?php if($p['status']!=='archived'): ?><form method="post"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken(),ENT_QUOTES,'UTF-8') ?>"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><input type="hidden" name="action" value="archive"><button class="link-button" onclick="return confirm('Archive this project?')">Archive</button></form><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div></section></div>
<?php include __DIR__ . '/includes/footer.php'; ?>
