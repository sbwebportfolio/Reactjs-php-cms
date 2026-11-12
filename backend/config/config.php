<?php
/**
 * Core application configuration.
 * This file stores environment-sensitive values and defaults.
 */

define('APP_NAME', 'Corporate CMS');
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('APP_DEBUG', getenv('APP_DEBUG') ?: (APP_ENV === 'development'));
define('APP_URL', getenv('APP_URL') ?: 'http://localhost');
define('APP_TIMEZONE', getenv('APP_TIMEZONE') ?: 'UTC');
define('APP_BASE_PATH', dirname(__DIR__));
define('APP_BACKEND_PATH', APP_BASE_PATH . '/backend');
define('APP_UPLOAD_PATH', APP_BACKEND_PATH . '/uploads');
define('APP_LOG_PATH', APP_BACKEND_PATH . '/logs');
define('APP_ADMIN_PATH', APP_BACKEND_PATH . '/admin');
define('APP_API_PATH', APP_BACKEND_PATH . '/api');

define('SESSION_NAME', 'cms_admin_session');
define('SESSION_LIFETIME', 3600);
define('CSRF_TOKEN_NAME', 'cms_csrf_token');
define('DEFAULT_PAGE_SIZE', 15);

define('MAX_UPLOAD_SIZE', 5242880);

define('ALLOWED_IMAGE_TYPES', 'jpg,jpeg,png,gif,webp');
define('ALLOWED_DOCUMENT_TYPES', 'pdf,doc,docx,xls,xlsx');

if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set(APP_TIMEZONE);
}
