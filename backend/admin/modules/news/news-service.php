<?php
/** News content validation and persistence using MySQLi prepared statements. */
require_once __DIR__ . '/../../../includes/functions.php';

function newsSlug(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    return trim($value, '-');
}

function newsLoad(int $id): ?array
{
    $stmt = dbConnection()->prepare('SELECT id, category_id, title, slug, excerpt, content, featured_image, is_featured, status, published_at FROM news WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
    return $row;
}

function newsSave(array $input, ?int $id, int $adminId): array
{
    $title = sanitizeString($input['title'] ?? '', 240);
    $slug = newsSlug((string) ($input['slug'] ?? $title));
    $excerpt = trim((string) ($input['excerpt'] ?? ''));
    $content = trim((string) ($input['content'] ?? ''));
    $image = trim((string) ($input['featured_image'] ?? ''));
    $status = (string) ($input['status'] ?? 'draft');
    $featured = isset($input['is_featured']) ? 1 : 0;
    $categoryId = filter_var($input['category_id'] ?? null, FILTER_VALIDATE_INT);
    $categoryId = $categoryId && $categoryId > 0 ? $categoryId : null;
    $publishedInput = trim((string) ($input['published_at'] ?? ''));
    $publishedAt = null;
    $errors = [];

    if ($title === '') $errors['title'] = 'Title is required.';
    if ($slug === '') $errors['slug'] = 'A valid URL slug is required.';
    if ($content === '') $errors['content'] = 'Article content is required.';
    if (!in_array($status, ['draft','published','scheduled','archived'], true)) $errors['status'] = 'Choose a valid status.';
    if (mb_strlen($excerpt) > 5000) $errors['excerpt'] = 'Excerpt must be 5,000 characters or fewer.';
    if (mb_strlen($content) > 1000000) $errors['content'] = 'Article content is too long.';
    if (mb_strlen($image) > 500) $errors['featured_image'] = 'Image reference is too long.';
    if ($publishedInput !== '') {
        $date = DateTime::createFromFormat('Y-m-d\TH:i', $publishedInput);
        if (!$date) $errors['published_at'] = 'Publication date is invalid.';
        else $publishedAt = $date->format('Y-m-d H:i:s');
    } elseif ($status === 'published') {
        $publishedAt = date('Y-m-d H:i:s');
    } elseif ($status === 'scheduled') {
        $errors['published_at'] = 'A publication date is required for scheduled news.';
    }
    if ($errors) return $errors;

    $conn = dbConnection();
    if ($id === null) {
        $stmt = $conn->prepare('INSERT INTO news (category_id,title,slug,excerpt,content,featured_image,is_featured,status,published_at,created_by) VALUES (?,?,?,?,?,?,?,?,?,?)');
        $stmt->bind_param('isssssissi', $categoryId, $title, $slug, $excerpt, $content, $image, $featured, $status, $publishedAt, $adminId);
    } else {
        $stmt = $conn->prepare('UPDATE news SET category_id=?,title=?,slug=?,excerpt=?,content=?,featured_image=?,is_featured=?,status=?,published_at=?,updated_by=? WHERE id=?');
        $stmt->bind_param('isssssissii', $categoryId, $title, $slug, $excerpt, $content, $image, $featured, $status, $publishedAt, $adminId, $id);
    }
    try {
        $stmt->execute();
    } catch (mysqli_sql_exception $e) {
        $stmt->close();
        if ((int) $e->getCode() === 1062) return ['slug' => 'This news URL slug is already in use.'];
        throw $e;
    }
    $stmt->close();
    return [];
}

function newsCategories(): array
{
    return dbConnection()->query('SELECT id,name FROM news_categories ORDER BY name')->fetch_all(MYSQLI_ASSOC);
}
