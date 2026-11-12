<?php
/** Versioned API front controller. Supports /api/v1/... URLs. */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/middleware/request.php';

header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

try {
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $segments = array_values(array_filter(explode('/', trim($uriPath, '/')), 'strlen'));
    $apiIndex = array_search('api', $segments, true);
    if ($apiIndex === false) jsonResponse(false, null, 'API path not found.', 'NOT_FOUND', 404);
    $route = array_slice($segments, $apiIndex + 1);
    if (($route[0] ?? '') === 'v1') array_shift($route);
    if (!$route) jsonResponse(false, null, 'API route not found.', 'NOT_FOUND', 404);

    if (($route[0] ?? '') === 'admin') {
        array_shift($route);
        $handler = require __DIR__ . '/routes/admin.php';
    } elseif (($route[0] ?? '') === 'news') {
        $handler = require __DIR__ . '/routes/news.php';
    } else {
        $handler = require __DIR__ . '/routes/public.php';
    }
    $handler($method, $route);
} catch (Throwable $e) {
    error_log('API error: ' . $e->getMessage());
    if (APP_DEBUG) jsonResponse(false, ['exception' => get_class($e)], 'API request failed: ' . $e->getMessage(), 'SERVER_ERROR', 500);
    jsonResponse(false, null, 'An internal server error occurred.', 'SERVER_ERROR', 500);
}
