<?php
/** Session-authenticated API guard; state-changing calls require CSRF validation. */
require_once __DIR__ . '/../../includes/auth.php';

function enforceAdminApiAuth(): void
{
    if (!isAdminLoggedIn()) {
        jsonResponse(false, null, 'Authentication required.', 'UNAUTHORIZED', 401);
    }

    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
        $token = requestHeader('X-CSRF-Token');
        if (!validateCsrfToken($token)) {
            jsonResponse(false, null, 'A valid CSRF token is required.', 'CSRF_INVALID', 419);
        }
    }
}
