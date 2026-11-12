<?php
/** Public read-only page and project routes. */
require_once __DIR__ . '/../responses/json.php';
require_once __DIR__ . '/../../includes/validation.php';

return function (string $method, array $pathParts): void {
    $resource = $pathParts[0] ?? '';
    if ($resource === 'projects') {
        $handler = require __DIR__ . '/projects.php';
        $handler($method, $pathParts);
    }
    if ($method === 'GET' && $resource === 'health') {
        apiSuccess(['app'=>APP_NAME,'status'=>'ok','timestamp'=>date('c')], 'API is healthy.');
    }
    if ($method === 'GET' && $resource === 'pages') {
        $conn=dbConnection(); $slug=$pathParts[1]??'';
        if ($slug!=='') {
            $stmt=$conn->prepare("SELECT id,title,slug,excerpt,content,banner_image,published_at,updated_at FROM pages WHERE slug=? AND status='published' AND (published_at IS NULL OR published_at<=NOW()) LIMIT 1");
            $stmt->bind_param('s',$slug); $stmt->execute(); $page=$stmt->get_result()->fetch_assoc(); $stmt->close();
            if(!$page) apiError('Page not found.','NOT_FOUND',404);
            apiSuccess(['page'=>$page],'Page loaded.');
        }
        $page=max(1,(int)($_GET['page']??1)); $limit=min(100,max(1,(int)($_GET['per_page']??15))); $offset=($page-1)*$limit;
        $stmt=$conn->prepare("SELECT id,title,slug,excerpt,banner_image,published_at FROM pages WHERE status='published' AND (published_at IS NULL OR published_at<=NOW()) ORDER BY published_at DESC,id DESC LIMIT ? OFFSET ?");
        $stmt->bind_param('ii',$limit,$offset);$stmt->execute();$items=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);$stmt->close();
        apiSuccess(['pages'=>$items,'pagination'=>['page'=>$page,'per_page'=>$limit]],'Pages loaded.');
    }
    apiError('Public route not found.','NOT_FOUND',404,['path'=>implode('/',$pathParts)]);
};
