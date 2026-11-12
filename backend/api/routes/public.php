<?php
/** Public read-only content and contact submission routes. */
require_once __DIR__ . '/../responses/json.php';
require_once __DIR__ . '/../middleware/request.php';
require_once __DIR__ . '/../../includes/validation.php';
require_once __DIR__ . '/../controllers/contact.php';

return function (string $method, array $pathParts): void {
    $resource = $pathParts[0] ?? '';

    if ($resource === 'projects') {
        $handler = require __DIR__ . '/projects.php';
        $handler($method, $pathParts);
        return;
    }
    if ($resource === 'news') {
        $handler = require __DIR__ . '/news.php';
        $handler($method, $pathParts);
        return;
    }
    if ($method === 'GET' && $resource === 'health') {
        apiSuccess(['app'=>APP_NAME,'status'=>'ok','timestamp'=>date('c')], 'API is healthy.');
    }
    if ($method === 'GET' && $resource === 'pages') {
        $conn = dbConnection();
        $slug = $pathParts[1] ?? '';
        if ($slug !== '') {
            $stmt = $conn->prepare("SELECT id,title,slug,excerpt,content,banner_image,published_at,updated_at FROM pages WHERE slug=? AND status='published' AND (published_at IS NULL OR published_at<=NOW()) LIMIT 1");
            $stmt->bind_param('s', $slug); $stmt->execute(); $page = $stmt->get_result()->fetch_assoc(); $stmt->close();
            if (!$page) apiError('Page not found.','NOT_FOUND',404);
            apiSuccess(['page'=>$page],'Page loaded.');
        }
        $page=max(1,(int)($_GET['page']??1)); $limit=min(100,max(1,(int)($_GET['per_page']??15))); $offset=($page-1)*$limit;
        $stmt=$conn->prepare("SELECT id,title,slug,excerpt,banner_image,published_at FROM pages WHERE status='published' AND (published_at IS NULL OR published_at<=NOW()) ORDER BY published_at DESC,id DESC LIMIT ? OFFSET ?");
        $stmt->bind_param('ii',$limit,$offset);$stmt->execute();$items=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);$stmt->close();
        apiSuccess(['pages'=>$items,'pagination'=>['page'=>$page,'per_page'=>$limit]],'Pages loaded.');
    }
    if ($resource === 'contact' && $method === 'POST') {
        $result = contactSubmit(requestData());
        if (!empty($result['errors'])) apiError('Validation failed.','VALIDATION_ERROR',422,$result['errors']);
        if (!empty($result['rate_limited'])) apiError('Too many requests. Please try again later.','RATE_LIMITED',429);
        apiSuccess(['accepted'=>true], 'Your message has been received.', 201);
    }
    if ($resource === 'contact' && $method !== 'POST') {
        header('Allow: POST');
        apiError('Method not allowed.','METHOD_NOT_ALLOWED',405);
    }
    apiError('Public route not found.','NOT_FOUND',404,['path'=>implode('/',$pathParts)]);
};
