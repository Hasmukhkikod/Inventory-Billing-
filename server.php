<?php
/**
 * Router script for PHP built-in server
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve static files as-is
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Route all other requests to index.php
require_once __DIR__ . '/index.php';
