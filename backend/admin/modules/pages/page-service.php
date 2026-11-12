<?php
/** Page admin module: input validation and prepared-statement persistence. */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/validation.php';

function pageModuleEscape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function pageModuleSlug(string $value): string
{
    $slug = strtolower(trim($value));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
    return trim($slug, '-');
}

function pageModuleLoad(int $id): ?array
{
    $stmt = dbConnection()->prepare('SELECT id, title, slug, excerpt, content, banner_image, status, published_at FROM pages WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
    return $row;
}

function pageModuleSave(array $input, ?int $id, int $adminId): array
{
    $title = sanitizeString($input['title'] ?? '', 240);
    $slug = pageModuleSlug((string) ($input['slug'] ?? $title));
    $excerpt = trim((string) ($input['excerpt'] ?? ''));
    $content = trim((string) ($input['content'] ?? ''));
    $banner = trim((string) ($input['banner_image'] ?? ''));
    $status = (string) ($input['status'] ?? 'draft');
    $allowedStatuses = ['draft', 'published', 'scheduled', 'archived'];
    $errors = [];

    if ($title === '') $errors['title'] = 'Title is required.';
    if ($slug === '') $errors['slug'] = 'A valid URL slug is required.';
    if (!in_array($status, $allowedStatuses, true)) $errors['status'] = 'Select a valid status.';
    if (mb_strlen($excerpt) > 5000) $errors['excerpt'] = 'Excerpt is too long.';
    if (mb_strlen($content) > 1000000) $errors['content'] = 'Page content is too long.';
    if (mb_strlen($banner) > 500) $errors['banner_image'] = 'Banner image path is too long.';

    if ($errors) return $errors;

    $publishedAt = $status === 'published' ? date('Y-m-d H:i:s') : null;
    $conn = dbConnection();
    if ($id === null) {
        $stmt = $conn->prepare('INSERT INTO pages (title, slug, excerpt, content, banner_image, status, published_at, created_by, updated_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('sssssssii', $title, $slug, $excerpt, $content, $banner, $status, $publishedAt, $adminId, $adminId);
    } else {
        $stmt = $conn->prepare('UPDATE pages SET title = ?, slug = ?, excerpt = ?, content = ?, banner_image = ?, status = ?, published_at = IF(? = \'published\', COALESCE(published_at, NOW()), NULL), updated_by = ? WHERE id = ?');
        $stmt->bind_param('sssssssii', $title, $slug, $excerpt, $content, $banner, $status, $status, $adminId, $id);
    }

    try {
        $stmt->execute();
    } catch (mysqli_sql_exception $e) {
        $stmt->close();
        if ((int) $e->getCode() === 1062) return ['slug' => 'This slug is already in use.'];
        throw $e;
    }
    $stmt->close();
    return [];
}
