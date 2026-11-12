<?php
/**
 * Shared helper functions and reusable application utilities.
 */

require_once __DIR__ . '/../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function dbConnection(): mysqli
{
    static $connection = null;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    $config = require __DIR__ . '/../config/database.php';

    $connection = @new mysqli(
        $config['host'],
        $config['username'],
        $config['password'],
        $config['database'],
        $config['port']
    );

    if ($connection->connect_error) {
        throw new RuntimeException('Database connection failed: ' . $connection->connect_error);
    }

    if (!$connection->set_charset($config['charset'])) {
        throw new RuntimeException('Database charset failed: ' . $connection->error);
    }

    return $connection;
}

function configValue(string $key, $default = null)
{
    static $config = null;

    if ($config === null) {
        $config = require __DIR__ . '/../config/config.php';
    }

    return $config[$key] ?? $default;
}

function jsonResponse(bool $success, $data = null, string $message = '', string $code = 'SUCCESS', int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message,
        'code' => $code,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

function currentRequestPath(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $parts = parse_url($uri);
    return $parts['path'] ?? '/';
}

function redirect(string $url): void
{
    if (!headers_sent()) {
        header('Location: ' . $url);
    }
    exit;
}

function sanitizeString($value, int $maxLength = 255): string
{
    $value = trim((string) $value);
    $value = strip_tags($value);
    $value = preg_replace('/\s+/', ' ', $value);

    if ($maxLength > 0 && mb_strlen($value) > $maxLength) {
        $value = mb_substr($value, 0, $maxLength);
    }

    return $value;
}

function sanitizeEmail(string $email): string
{
    return strtolower(trim(filter_var($email, FILTER_SANITIZE_EMAIL)));
}

function generateCsrfToken(): string
{
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }

    return $_SESSION[CSRF_TOKEN_NAME];
}

function validateCsrfToken(?string $token): bool
{
    if (empty($token) || empty($_SESSION[CSRF_TOKEN_NAME])) {
        return false;
    }

    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

function setFlash(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}

function getFlash(string $key): ?string
{
    $message = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $message;
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]+/', '', $text);
    $text = preg_replace('/\s+/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

function parsePagination(array $request, int $defaultLimit = DEFAULT_PAGE_SIZE): array
{
    $page = isset($request['page']) ? max(1, (int) $request['page']) : 1;
    $limit = isset($request['limit']) ? max(1, (int) $request['limit']) : $defaultLimit;
    $offset = ($page - 1) * $limit;

    return [
        'page' => $page,
        'limit' => min($limit, 100),
        'offset' => $offset,
    ];
}

function ensureUploadsDirectory(string $subFolder = ''): string
{
    $base = APP_UPLOAD_PATH;
    $path = $subFolder !== '' ? rtrim($base, '/') . '/' . ltrim($subFolder, '/') : $base;

    if (!is_dir($path)) {
        if (!mkdir($path, 0775, true) && !is_dir($path)) {
            throw new RuntimeException('Unable to create upload directory: ' . $path);
        }
    }

    return $path;
}

function getClientIp(): string
{
    foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            $value = $_SERVER[$key];
            if (strpos($value, ',') !== false) {
                $parts = explode(',', $value);
                $value = trim($parts[0]);
            }
            return $value;
        }
    }

    return '0.0.0.0';
}

function logActivity(?int $adminId, string $action, ?string $entityType = null, ?int $entityId = null, ?array $details = null): void
{
    try {
        $conn = dbConnection();
        $stmt = $conn->prepare(
            'INSERT INTO activity_logs (admin_id, action, entity_type, entity_id, details_json, ip_address, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())'
        );

        $ip = getClientIp();
        $payload = $details ? json_encode($details, JSON_UNESCAPED_SLASHES) : null;

        $stmt->bind_param('ississ', $adminId, $action, $entityType, $entityId, $payload, $ip);
        $stmt->execute();
        $stmt->close();
    } catch (Throwable $e) {
        error_log('Log activity failed: ' . $e->getMessage());
    }
}
