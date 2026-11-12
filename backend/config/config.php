<?php
/**
 * Core application configuration. Secrets are supplied through environment variables.
 */
$env = getenv('APP_ENV') ?: 'production';
$debugValue = getenv('APP_DEBUG');
$debug = $debugValue === false ? ($env === 'development') : filter_var($debugValue, FILTER_VALIDATE_BOOLEAN);

if (!defined('APP_NAME')) define('APP_NAME', getenv('APP_NAME') ?: 'Corporate CMS');
if (!defined('APP_ENV')) define('APP_ENV', $env);
if (!defined('APP_DEBUG')) define('APP_DEBUG', $debug);
if (!defined('APP_URL')) define('APP_URL', rtrim(getenv('APP_URL') ?: 'http://localhost', '/'));
if (!defined('APP_TIMEZONE')) define('APP_TIMEZONE', getenv('APP_TIMEZONE') ?: 'UTC');
if (!defined('APP_BASE_PATH')) define('APP_BASE_PATH', dirname(__DIR__, 2));
if (!defined('APP_BACKEND_PATH')) define('APP_BACKEND_PATH', APP_BASE_PATH . '/backend');
if (!defined('APP_UPLOAD_PATH')) define('APP_UPLOAD_PATH', getenv('APP_UPLOAD_PATH') ?: APP_BACKEND_PATH . '/storage/uploads');
if (!defined('APP_LOG_PATH')) define('APP_LOG_PATH', getenv('APP_LOG_PATH') ?: APP_BACKEND_PATH . '/storage/logs');
if (!defined('APP_ADMIN_PATH')) define('APP_ADMIN_PATH', APP_BACKEND_PATH . '/admin');
if (!defined('APP_API_PATH')) define('APP_API_PATH', APP_BACKEND_PATH . '/api');
if (!defined('SESSION_NAME')) define('SESSION_NAME', 'cms_admin_session');
if (!defined('SESSION_LIFETIME')) define('SESSION_LIFETIME', 3600);
if (!defined('CSRF_TOKEN_NAME')) define('CSRF_TOKEN_NAME', 'cms_csrf_token');
if (!defined('DEFAULT_PAGE_SIZE')) define('DEFAULT_PAGE_SIZE', 15);
if (!defined('MAX_UPLOAD_SIZE')) define('MAX_UPLOAD_SIZE', 5242880);

if (!in_array(APP_ENV, ['development', 'test', 'production'], true)) {
    throw new RuntimeException('APP_ENV must be development, test, or production.');
}

date_default_timezone_set(APP_TIMEZONE);
