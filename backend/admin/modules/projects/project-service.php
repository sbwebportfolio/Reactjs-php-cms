<?php
/** Project admin module service. All persistence uses prepared MySQLi statements. */
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../includes/auth.php';

function projectSlug(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    return trim($value, '-');
}

function projectLoad(int $id): ?array
{
    $stmt = dbConnection()->prepare('SELECT id, category_id, title, slug, excerpt, content, location, client_name, start_date, completion_date, project_status, featured_image, is_featured, status FROM projects WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
    return $row;
}

function projectSave(array $input, ?int $id, int $adminId): array
{
    $title = sanitizeString($input['title'] ?? '', 240);
    $slug = projectSlug((string) ($input['slug'] ?? $title));
    $excerpt = trim((string) ($input['excerpt'] ?? ''));
    $content = trim((string) ($input['content'] ?? ''));
    $location = sanitizeString($input['location'] ?? '', 240);
    $client = sanitizeString($input['client_name'] ?? '', 200);
    $projectStatus = sanitizeString($input['project_status'] ?? '', 80);
    $image = trim((string) ($input['featured_image'] ?? ''));
    $status = (string) ($input['status'] ?? 'draft');
    $featured = isset($input['is_featured']) ? 1 : 0;
    $categoryId = filter_var($input['category_id'] ?? null, FILTER_VALIDATE_INT);
    $categoryId = $categoryId && $categoryId > 0 ? $categoryId : null;
    $startDate = trim((string) ($input['start_date'] ?? '')) ?: null;
    $completionDate = trim((string) ($input['completion_date'] ?? '')) ?: null;
    $errors = [];

    if ($title === '') $errors['title'] = 'Project title is required.';
    if ($slug === '') $errors['slug'] = 'A valid URL slug is required.';
    if (!in_array($status, ['draft', 'published', 'archived'], true)) $errors['status'] = 'Invalid publication status.';
    if (mb_strlen($excerpt) > 5000) $errors['excerpt'] = 'Excerpt exceeds 5,000 characters.';
    if (mb_strlen($content) > 1000000) $errors['content'] = 'Project content is too long.';
    if (mb_strlen($image) > 500) $errors['featured_image'] = 'Image reference is too long.';
    foreach (['start_date' => $startDate, 'completion_date' => $completionDate] as $field => $date) {
        if ($date !== null && !DateTime::createFromFormat('Y-m-d', $date)) $errors[$field] = 'Date must use YYYY-MM-DD format.';
    }
    if ($startDate && $completionDate && $completionDate < $startDate) $errors['completion_date'] = 'Completion date must not precede the start date.';
    if ($errors) return $errors;

    $publishedAt = $status === 'published' ? date('Y-m-d H:i:s') : null;
    $conn = dbConnection();
    if ($id === null) {
        $stmt = $conn->prepare('INSERT INTO projects (category_id,title,slug,excerpt,content,location,client_name,start_date,completion_date,project_status,featured_image,is_featured,status,published_at,created_by,updated_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
        $stmt->bind_param('isssssssssisissi', $categoryId, $title, $slug, $excerpt, $content, $location, $client, $startDate, $completionDate, $projectStatus, $image, $featured, $status, $publishedAt, $adminId, $adminId);
    } else {
        $stmt = $conn->prepare('UPDATE projects SET category_id=?,title=?,slug=?,excerpt=?,content=?,location=?,client_name=?,start_date=?,completion_date=?,project_status=?,featured_image=?,is_featured=?,status=?,published_at=IF(?=\'published\',COALESCE(published_at,NOW()),NULL),updated_by=? WHERE id=?');
        $stmt->bind_param('isssssssssisissii', $categoryId, $title, $slug, $excerpt, $content, $location, $client, $startDate, $completionDate, $projectStatus, $image, $featured, $status, $status, $adminId, $id);
    }
    try {
        $stmt->execute();
    } catch (mysqli_sql_exception $e) {
        $stmt->close();
        if ((int) $e->getCode() === 1062) return ['slug' => 'This project URL slug is already in use.'];
        throw $e;
    }
    $stmt->close();
    return [];
}
