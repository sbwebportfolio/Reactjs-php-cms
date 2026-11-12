<?php
/**
 * Admin API endpoints. Includes server-backed statistics and session metadata.
 */
require_once __DIR__ . '/../responses/json.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/request.php';
require_once __DIR__ . '/../../includes/auth.php';

return function (string $method, array $pathParts): void {
    enforceAdminApiAuth();
    $route = $pathParts[0] ?? '';

    if ($method === 'GET' && $route === 'csrf') {
        apiSuccess(['token' => generateCsrfToken()], 'CSRF token issued.');
    }

    if ($method === 'GET' && $route === 'dashboard') {
        requirePermission('dashboard.view');
        $conn = dbConnection();
        $tables = [
            'pages' => 'pages', 'projects' => 'projects', 'news' => 'news',
            'press_releases' => 'press_releases', 'blog_posts' => 'blog_posts',
            'testimonials' => 'testimonials', 'contact_inquiries' => 'contact_inquiries',
        ];
        $stats = [];
        foreach ($tables as $key => $table) {
            // Table names are fixed in this allowlisted map, never user input.
            $result = $conn->query('SELECT COUNT(*) AS total FROM `' . $table . '`');
            $stats[$key] = (int) $result->fetch_assoc()['total'];
        }
        $recent = [];
        $result = $conn->query('SELECT id, name, email, subject, status, created_at FROM contact_inquiries ORDER BY created_at DESC LIMIT 8');
        while ($row = $result->fetch_assoc()) $recent[] = $row;
        apiSuccess([
            'admin' => getAdminSession(),
            'stats' => $stats,
            'recent_inquiries' => $recent,
            'csrf_token' => generateCsrfToken(),
        ], 'Dashboard summary loaded.');
    }

    if ($method === 'GET' && $route === 'permissions') {
        requirePermission('users.manage');
        $result = dbConnection()->query('SELECT permission_key, description FROM permissions ORDER BY permission_key');
        $permissions = [];
        while ($row = $result->fetch_assoc()) $permissions[] = $row;
        apiSuccess(['permissions' => $permissions]);
    }

    apiError('Admin route not found.', 'NOT_FOUND', 404, ['path' => implode('/', $pathParts)]);
};
