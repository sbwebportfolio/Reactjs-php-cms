<?php
/** Logout is a state-changing operation and must be POST + CSRF protected. */
require_once __DIR__ . '/../includes/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validateCsrfToken($_POST['csrf_token'] ?? null)) {
    http_response_code(405);
    header('Allow: POST');
    exit('A valid POST request is required to sign out.');
}
adminLogout();
redirect('/backend/admin/login.php');
