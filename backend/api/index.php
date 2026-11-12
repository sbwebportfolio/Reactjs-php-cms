<?php
/**
 * Central API router.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = parse_url($requestUri, PHP_URL_PATH);

if ($uri === null) {
    jsonResponse(false, null, 'Invalid request URI.', 'BAD_REQUEST', 400);
}

$cleanUri = trim($uri, '/');
$segments = $cleanUri === '' ? [] : explode('/', $cleanUri);

if (count($segments) < 2 || $segments[0] !== 'api') {
    jsonResponse(false, null, 'API base path not found.', 'NOT_FOUND', 404);
}

$routePath = array_slice($segments, 1);
$publicRoutes = require __DIR__ . '/routes/public.php';
$adminRoutes = require __DIR__ . '/routes/admin.php';

try {
    if (count($routePath) > 0 && $routePath[0] === 'admin') {
        $adminRoutes($method, array_slice($routePath, 1));
    } else {
        $publicRoutes($method, $routePath);
    }
} catch (Throwable $e) {
    if (APP_DEBUG) {
        jsonResponse(false, ['trace' => $e->getTraceAsString()], 'Server error: ' . $e->getMessage(), 'SERVER_ERROR', 500);
    }

    jsonResponse(false, null, 'An internal server error occurred.', 'SERVER_ERROR', 500);
}
