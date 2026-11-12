<?php
/**
 * API response helpers.
 */

require_once __DIR__ . '/../includes/functions.php';

function apiSuccess($data = null, string $message = 'Request completed successfully.', int $statusCode = 200): void
{
    jsonResponse(true, $data, $message, 'SUCCESS', $statusCode);
}

function apiError(string $message, string $code = 'ERROR', int $statusCode = 400, $details = null): void
{
    jsonResponse(false, $details, $message, $code, $statusCode);
}
