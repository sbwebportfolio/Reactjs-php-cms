<?php
/** Read JSON or form input consistently. */
function requestData(): array
{
    $contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
    if (str_contains($contentType, 'application/json')) {
        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') return [];
        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            jsonResponse(false, null, 'Request body must be valid JSON.', 'INVALID_JSON', 400);
        }
        return $decoded;
    }
    return $_POST;
}

function requireHttpMethod(array $allowed): void
{
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    if (!in_array($method, $allowed, true)) {
        header('Allow: ' . implode(', ', $allowed));
        jsonResponse(false, null, 'HTTP method not allowed.', 'METHOD_NOT_ALLOWED', 405);
    }
}

function requestHeader(string $name): ?string
{
    $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
    return isset($_SERVER[$key]) ? trim((string) $_SERVER[$key]) : null;
}
