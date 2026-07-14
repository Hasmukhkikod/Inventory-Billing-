<?php
/**
 * Router script for PHP built-in server - mirrors the .htaccess rewrite
 * rules used in production (Apache) so clean, extensionless URLs behave
 * identically in local dev.
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Cosmetic redirect: hide .php in the address bar (except /api/ endpoints,
// which the app calls directly via AJAX and must keep responding at their
// exact .php path with no redirect hop). This must run before the static/
// real-file check below, otherwise an old-style /billing/form.php link
// would just get served directly instead of redirected to the clean URL -
// exactly how Apache's mod_rewrite runs this same rule before its own
// default file-serving in the production .htaccess.
if (substr($uri, -4) === '.php' && strpos($uri, '/api/') !== 0) {
    $clean = substr($uri, 0, -4);
    $query = $_SERVER['QUERY_STRING'] ?? '';
    header('Location: ' . $clean . ($query ? '?' . $query : ''), true, 301);
    exit;
}

// Serve static files (css/js/images/uploads, and /api/*.php) as-is
if ($uri !== '/' && file_exists(__DIR__ . $uri) && !is_dir(__DIR__ . $uri)) {
    return false;
}

// Transparently resolve a clean URL back to its real .php file (covers
// standalone files like login.php and the per-module front-controller
// stub files like billing/form.php).
if ($uri !== '/' && file_exists(__DIR__ . $uri . '.php')) {
    require __DIR__ . $uri . '.php';
    return true;
}

// Route everything else to the front controller
require_once __DIR__ . '/index.php';
