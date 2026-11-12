<?php
/** Admin contact inbox: filtering, status updates, inquiry details, and CSV export. */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdminAuth();
if (!userHasPermission('inquiries.manage')) { http_response_code(403); exit('You do not have permission to manage inquiries.'); }

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $result = dbConnection()->query('SELECT id,name,email,phone,subject,message,status,created_at FROM contact_inquiries ORDER BY created_at DESC LIMIT 10000');
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="contact-inquiries.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID','Name','Email','Phone','Subject','Message','Status','Submitted']);
    while ($row = $result->fetch_assoc()) {
        foreach ($row as &$value) {
            $value = (string) $value;
            // Avoid spreadsheet formula injection in exported text fields.
            if (isset($value[0]) && in_array($value[0], ['=', '+', '-', '@'], true)) $value = "'" . $value;
        }
        unset($value);
        fputcsv($out, $row);
    }
    fclose($out);
    exit;
}

$errors=[];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!validateCsrfToken($_POST['csrf_token']??null)) { http_response_code(419); exit('Your session expired. Reload and try again.'); }
    $id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);
    $status=(string)($_POST['status']??'');
    if (!$id || !in_array($status,['unread','read','replied','archived'],true)) $errors[]='Invalid inquiry update.';
    else {
        $stmt=dbConnection()->prepare('UPDATE contact_inquiries SET status=? WHERE id=?');
        $stmt->bind_param('si',$status,$id);$stmt->execute();$changed=$stmt->affected_rows;$stmt->close();
        if (!$changed) $errors[]='Inquiry not found or unchanged.';
        else { logActivity((int)getAdminSession()['id'],'inquiry.status.updated','contact_inquiry',$id,['status'=>$status]); redirect('/backend/admin/inquiries.php?notice=updated'); }
    }
}

$selectedId=filter_var($_GET['id']??null,FILTER_VALIDATE_INT);
$selected=null;
if($selectedId){$stmt=dbConnection()->prepare('SELECT id,name,email,phone,subject,message,status,created_at FROM contact_inquiries WHERE id=? LIMIT 1');$stmt->bind_param('i',$selectedId);$stmt->execute();$selected=$stmt->get_result()->fetch_assoc()?:null;$stmt->close();}
$filter=(string)($_GET['status']??'');$allowed=['unread','read','replied','archived'];
$sql='SELECT id,name,email,subject,status,created_at FROM contact_inquiries';$params=[];$types='';
if(in_array($filter,$allowed,true)){$sql.=' WHERE status=?';$params[]=$filter;$types='s';}
$sql.=' ORDER BY created_at DESC LIMIT 100';$stmt=dbConnection()->prepare($sql);if($params)$stmt->bind_param($types,...$params);$stmt->execute();$items=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);$stmt->close();
$pageTitle='Contact Inquiries';include __DIR__.'/includes/header.php';
?>
<div class="content-wrap">
<section class="hero-strip compact"><div><p class="eyebrow accent">Inbox</p><h2>Contact inquiries</h2></div><a class="btn btn-primary" href="/backend/admin/inquiries.php?export=csv">Export CSV</a></section>
<?php if(isset($_GET['notice'])): ?><p class="panel notice">Inquiry <?= htmlspecialchars((string)$_GET['notice'],ENT_QUOTES,'UTF-8') ?>.</p><?php endif; ?>
<?php foreach($errors as $error): ?><p class="panel error-message"><?= htmlspecialchars($error,ENT_QUOTES,'UTF-8') ?></p><?php endforeach; ?>
<?php if($selected): ?><section class="panel"><div class="panel-header"><h3><?= htmlspecialchars($selected['subject'],ENT_QUOTES,'UTF-8') ?></h3><a href="/backend/admin/inquiries.php">Close</a></div><p><strong><?= htmlspecialchars($selected['name'],ENT_QUOTES,'UTF-8') ?></strong> · <a href="mailto:<?= htmlspecialchars($selected['email'],ENT_QUOTES,'UTF-8') ?>"><?= htmlspecialchars($selected['email'],ENT_QUOTES,'UTF-8') ?></a></p><p><?= nl2br(htmlspecialchars($selected['message'],ENT_QUOTES,'UTF-8')) ?></p><small><?= htmlspecialchars($selected['created_at'],ENT_QUOTES,'UTF-8') ?> · <?= htmlspecialchars($selected['phone']??'No phone',ENT_QUOTES,'UTF-8') ?></small><form method="post" class="toolbar"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken(),ENT_QUOTES,'UTF-8') ?>"><input type="hidden" name="id" value="<?= (int)$selected['id'] ?>"><select name="status"><?php foreach($allowed as $s): ?><option value="<?= $s ?>" <?= $selected['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select><button class="btn btn-primary">Update status</button></form></section><?php endif; ?>
<section class="panel table-panel"><form class="toolbar" method="get"><select name="status"><option value="">All statuses</option><?php foreach($allowed as $s): ?><option value="<?= $s ?>" <?= $filter===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select><button class="btn btn-ghost">Filter</button></form><div class="table-scroll"><table class="data-table"><thead><tr><th>Name</th><th>Email</th><th>Subject</th><th>Status</th><th>Received</th><th></th></tr></thead><tbody><?php if(!$items): ?><tr><td colspan="6">No inquiries found.</td></tr><?php endif; ?><?php foreach($items as $item): ?><tr><td><?= htmlspecialchars($item['name'],ENT_QUOTES,'UTF-8') ?></td><td><?= htmlspecialchars($item['email'],ENT_QUOTES,'UTF-8') ?></td><td><?= htmlspecialchars($item['subject'],ENT_QUOTES,'UTF-8') ?></td><td><?= htmlspecialchars(ucfirst($item['status']),ENT_QUOTES,'UTF-8') ?></td><td><?= htmlspecialchars($item['created_at'],ENT_QUOTES,'UTF-8') ?></td><td><a href="/backend/admin/inquiries.php?id=<?= (int)$item['id'] ?>">View</a></td></tr><?php endforeach; ?></tbody></table></div></section></div>
<?php include __DIR__.'/includes/footer.php'; ?>
