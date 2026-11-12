<?php
/** Secure media upload and metadata utilities. */
require_once __DIR__ . '/../../../../includes/functions.php';

function mediaAllowedMimeTypes(): array
{
    return [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'application/pdf' => 'pdf',
    ];
}

function mediaUpload(array $file, string $altText, int $adminId): array
{
    if (!isset($file['error']) || is_array($file['error'])) return ['file' => 'Invalid upload request.'];
    if ($file['error'] !== UPLOAD_ERR_OK) return ['file' => 'The upload did not complete successfully.'];
    if (!is_uploaded_file($file['tmp_name'] ?? '')) return ['file' => 'Invalid uploaded file.'];
    if ((int) $file['size'] < 1 || (int) $file['size'] > MAX_UPLOAD_SIZE) return ['file' => 'File size must be between 1 byte and 5 MB.'];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $types = mediaAllowedMimeTypes();
    if (!isset($types[$mime])) return ['file' => 'Only JPG, PNG, GIF, WebP, and PDF files are allowed.'];

    $original = basename((string) ($file['name'] ?? 'upload'));
    $original = preg_replace('/[^A-Za-z0-9._ -]/', '_', $original) ?: 'upload';
    $extension = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    if ($extension !== $types[$mime]) return ['file' => 'The file extension does not match the detected file type.'];

    $directory = ensureUploadsDirectory('media');
    $stored = bin2hex(random_bytes(20)) . '.' . $extension;
    $absolute = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $stored;
    if (!move_uploaded_file($file['tmp_name'], $absolute)) return ['file' => 'Could not store the uploaded file.'];
    chmod($absolute, 0640);

    $relative = 'media/' . $stored;
    $altText = sanitizeString($altText, 255);
    try {
        $stmt = dbConnection()->prepare('INSERT INTO media_library (uploaded_by, original_name, stored_name, relative_path, mime_type, file_size, alt_text) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $size = (int) $file['size'];
        $stmt->bind_param('issssis', $adminId, $original, $stored, $relative, $mime, $size, $altText);
        $stmt->execute();
        $id = (int) dbConnection()->insert_id;
        $stmt->close();
        return ['id' => $id];
    } catch (Throwable $e) {
        @unlink($absolute);
        throw $e;
    }
}
