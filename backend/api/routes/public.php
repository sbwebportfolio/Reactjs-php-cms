<?php
/** Public read-only page endpoints. */
require_once __DIR__ . '/../responses/json.php';
require_once __DIR__ . '/../../includes/validation.php';

return function (string $method, array $pathParts): void {
    $resource = $pathParts[0] ?? '';
    if ($method === 'GET' && $resource === 'health') {
        apiSuccess(['app' => APP_NAME, 'status' => 'ok', 'timestamp' => date('c')], 'API is healthy.');
    }

    if ($method === 'GET' && $resource === 'pages') {
        $conn = dbConnection();
        $slug = $pathParts[1] ?? '';
        if ($slug !== '') {
            $stmt = $conn->prepare("SELECT id, title, slug, excerpt, content, banner_image, published_at, updated_at FROM pages WHERE slug = ? AND status = 'published' AND (published_at IS NULL OR published_at <= NOW()) LIMIT 1");
            $stmt->bind_param('s', $slug);
            $stmt->execute();
            $page = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$page) apiError('Page not found.', 'NOT_FOUND', 404);
            apiSuccess(['page' => $page], 'Page loaded.');
        }

        $pagination = parsePagination($_GET);
        $search = trim((string) ($_GET['search'] ?? ''));
        $where = "status = 'published' AND (published_at IS NULL OR published_at <= NOW())";
        if ($search !== '') {
            $where .= ' AND (title LIKE ? OR excerpt LIKE ?)';
            $term = '%' . $search . '%';
            $stmt = $conn->prepare("SELECT id, title, slug, excerpt, banner_image, published_at FROM pages WHERE {$where} ORDER BY published_at DESC, id DESC LIMIT ? OFFSET ?");
            $stmt->bind_param('ssii', $term, $term, $pagination['limit'], $pagination['offset']);
            $countStmt = $conn->prepare("SELECT COUNT(*) AS total FROM pages WHERE {$where}");
            $countStmt->bind_param('ss', $term, $term);
        } else {
            $stmt = $conn->prepare("SELECT id, title, slug, excerpt, banner_image, published_at FROM pages WHERE {$where} ORDER BY published_at DESC, id DESC LIMIT ? OFFSET ?");
            $stmt->bind_param('ii', $pagination['limit'], $pagination['offset']);
            $countStmt = $conn->prepare("SELECT COUNT(*) AS total FROM pages WHERE {$where}");
        }
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $countStmt->execute();
        $total = (int) $countStmt->get_result()->fetch_assoc()['total'];
        $countStmt->close();
        apiSuccess(['pages' => $items], 'Pages loaded.', 200);
    }

    apiError('Public route not found.', 'NOT_FOUND', 404, ['path' => implode('/', $pathParts)]);
};
