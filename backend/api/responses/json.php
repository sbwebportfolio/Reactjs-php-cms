<?php
/**
 * API middleware for admin authentication.
 */

require_once __DIR__ . '/../../includes/auth.php';

function enforceAdminApiAuth(): void
{
    if (!isAdminLoggedIn()) {
        jsonResponse(false, null, 'Authentication required.', 'UNAUTHORIZED', 401);
    }
}
