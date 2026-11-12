<?php
/**
 * MySQLi database configuration.
 */

require_once __DIR__ . '/config.php';

return [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'username' => getenv('DB_USERNAME') ?: 'root',
    'password' => getenv('DB_PASSWORD') ?: '',
    'database' => getenv('DB_DATABASE') ?: 'corporate_cms',
    'port' => getenv('DB_PORT') ?: 3306,
    'charset' => 'utf8mb4',
];
