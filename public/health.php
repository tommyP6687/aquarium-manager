<?php
require_once __DIR__ . '/../config/database.php';

try {
    get_db()->query('SELECT 1');
    http_response_code(200);
    echo 'OK';
} catch (\Throwable $e) {
    error_log('Health check failed: ' . $e->getMessage());
    http_response_code(500);
    echo 'DB unavailable';
}
