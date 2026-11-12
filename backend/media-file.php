<?php
/** Serve public media files by database id, never by user-supplied path. */
require_once __DIR__ . '/../../includes/functions.php';
$id=filter_var($_GET['id']??null,FILTER_VALIDATE_INT);
if(!$id||$id<1){http_response_code(404);exit;}
$stmt=dbConnection()->prepare('SELECT relative_path,mime_type,file_size FROM media_library WHERE id=? AND is_public=1 AND deleted_at IS NULL LIMIT 1');
$stmt->bind_param('i',$id);$stmt->execute();$media=$stmt->get_result()->fetch_assoc();$stmt->close();
$allowed=['image/jpeg','image/png','image/gif','image/webp','application/pdf'];
if(!$media||!in_array($media['mime_type'],$allowed,true)){http_response_code(404);exit;}
$root=realpath(APP_UPLOAD_PATH.'/media');$file=realpath(APP_UPLOAD_PATH.'/'.$media['relative_path']);
if(!$root||!$file||!str_starts_with($file,$root.DIRECTORY_SEPARATOR)||!is_file($file)){http_response_code(404);exit;}
header('Content-Type: '.$media['mime_type']);header('Content-Length: '.(int)$media['file_size']);header('X-Content-Type-Options: nosniff');header('Content-Disposition: inline; filename="'.basename($file).'"');header('Cache-Control: public, max-age=86400');readfile($file);
