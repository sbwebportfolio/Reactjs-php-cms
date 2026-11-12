<?php
/**
 * Admin API endpoints.
 */

require_once __DIR__ . '/../responses/json.php';
require_once __DIR__ . '/../../includes/auth.php';

return function ($method, $pathParts): void {
    enforceAdminApiAuth();

    if ($method === 'GET' && ($pathParts[0] ?? '') === 'dashboard') {
        requirePermission('dashboard.view');

        $stats = [
            'pages' => 0,
            'projects' => 0,
            'news' => 0,
            'blog' => 0,
            'inquiries' => 0,
        ];

        apiSuccess([
            'admin' => getAdminSession(),
            'stats' => $stats,
        ], 'Dashboard summary loaded successfully.');
    }

    if ($method === 'GET' && ($pathParts[0] ?? '') === 'permissions') {
        requirePermission('users.manage');

        $permissions = [
            'dashboard.view',
            'pages.manage',
            'projects.manage',
            'news.manage',
            'blog.manage',
            'media.manage',
            'settings.manage',
            'users.manage',
        ];

        apiSuccess(['permissions' => $permissions], 'Permissions loaded successfully.');
    }

    apiError('Admin route not found.', 'NOT_FOUND', 404, ['path' => implode('/', $pathParts)]);
};
