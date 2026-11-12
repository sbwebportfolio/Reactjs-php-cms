<?php
require_once __DIR__ . '/modules/media/media-service.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminAuth();
if (!userHasPermission('media.manage')) { http_response_code(403); exit('You do not have permission to manage media.'); }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? null)) { http_response_code(419); exit('Your session expired. Reload and try again.'); }
    $adminId = (int) getAdminSession()['id'];
    $action = (string) ($_POST['action'] ?? 'upload');
    try {
        if ($action === 'delete') {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) { http_response_code(400); exit('Invalid media item.'); }
            $stmt = dbConnection()->prepare('UPDATE media_library SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL');
            $stmt->bind_param('i', $id); $stmt->execute(); $changed = $stmt->affected_rows; $stmt->close();
            if (!$changed) { http_response_code(404); exit('Media item not found.'); }
            logActivity($adminId, 'media.deleted', 'media', $id);
            redirect('/backend/admin/media.php?notice=deleted');
        }
        $result = mediaUpload($_FILES['media_file'] ?? [], (string) ($_POST['alt_text'] ?? ''), $adminId);
        if (isset($result['file'])) $errors = $result;
        else {
            logActivity($adminId, 'media.uploaded', 'media', $result['id']);
            redirect('/backend/admin/media.php?notice=uploaded');
        }
    } catch (Throwable $e) {
        error_log('Media operation failed: ' . $e->getMessage());
        $errors['general'] = APP_DEBUG ? $e->getMessage() : 'The media operation could not be completed.';
    }
}

$search = trim((string) ($_GET['search'] ?? ''));
$sql = 'SELECT id,original_name,relative_path,mime_type,file_size,alt_text,created_at FROM media_library WHERE deleted_at IS NULL';
$params=[]; $types='';
if ($search !== '') { $sql .= ' AND (original_name LIKE ? OR alt_text LIKE ?)'; $term='%'.$search.'%'; $params=[$term,$term]; $types='ss'; }
$sql .= ' ORDER BY created_at DESC LIMIT 100';
$stmt=dbConnection()->prepare($sql); if($params)$stmt->bind_param($types,...$params); $stmt->execute(); $items=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
$pageTitle='Media Library'; include __DIR__ . '/includes/header.php';
?>
<div class="content-wrap">
<section class="hero-strip compact"><div><p class="eyebrow accent">Assets</p><h2>Media library</h2></div></section>
<?php if(isset($_GET['notice'])): ?><p class="panel notice">Media <?= htmlspecialchars((string)$_GET['notice'],ENT_QUOTES,'UTF-8') ?>.</p><?php endif; ?>
<?php foreach($errors as $error): ?><p class="panel error-message"><?= htmlspecialchars($error,ENT_QUOTES,'UTF-8') ?></p><?php endforeach; ?>
<section class="panel"><h3>Upload image or PDF (maximum 5 MB)</h3><form method="post" enctype="multipart/form-data" class="cms-form"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken(),ENT_QUOTES,'UTF-8') ?>"><input type="hidden" name="MAX_FILE_SIZE" value="5242880"><label>File<input type="file" name="media_file" accept="image/jpeg,image/png,image/gif,image/webp,application/pdf" required></label><label>Alt text (for images)<input maxlength="255" name="alt_text"></label><div class="form-actions"><button class="btn btn-primary" type="submit">Upload file</button></div></form></section>
<section class="panel table-panel"><form class="toolbar" method="get"><input name="search" value="<?= htmlspecialchars($search,ENT_QUOTES,'UTF-8') ?>" placeholder="Search file name or alt text"><button class="btn btn-ghost">Search</button></form><div class="table-scroll"><table class="data-table"><thead><tr><th>Preview</th><th>File</th><th>Type</th><th>Size</th><th>Alt text</th><th>Uploaded</th><th>Actions</th></tr></thead><tbody>
<?php if(!$items): ?><tr><td colspan="7">No media files found.</td></tr><?php endif; ?>
<?php foreach($items as $item): ?><tr><td><?php if(str_starts_with($item['mime_type'],'image/')): ?><img class="media-preview" src="/backend/media-file.php?id=<?= (int)$item['id'] ?>" alt="<?= htmlspecialchars($item['alt_text']??'',ENT_QUOTES,'UTF-8') ?>"><?php else: ?>PDF<?php endif; ?></td><td><?= htmlspecialchars($item['original_name'],ENT_QUOTES,'UTF-8') ?></td><td><?= htmlspecialchars($item['mime_type'],ENT_QUOTES,'UTF-8') ?></td><td><?= number_format(((int)$item['file_size'])/1024,1) ?> KB</td><td><?= htmlspecialchars($item['alt_text']??'',ENT_QUOTES,'UTF-8') ?></td><td><?= htmlspecialchars($item['created_at'],ENT_QUOTES,'UTF-8') ?></td><td><form method="post" onsubmit="return confirm('Remove this media item?')"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken(),ENT_QUOTES,'UTF-8') ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$item['id'] ?>"><button class="link-button" type="submit">Remove</button></form></td></tr><?php endforeach; ?>
</tbody></table></div></section></div>
<?php include __DIR__ . '/includes/footer.php'; ?>
