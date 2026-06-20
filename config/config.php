<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Application General Configuration
 */

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

// Set Default Timezone
date_default_timezone_set('Asia/Kolkata');

// Error Reporting Config (For Dev)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Application Directories
define('BASE_DIR', dirname(__DIR__));
define('UPLOAD_DIR', BASE_DIR . '/uploads');
define('BACKUP_DIR', BASE_DIR . '/backups');
define('LOG_DIR', BASE_DIR . '/logs');

// Part 6 Base URL & Paths
define('BASE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost:8000'));
define('APP_PATH', BASE_DIR . '/application');
define('UPLOAD_PATH', BASE_URL . '/uploads');
define('ASSET_PATH', BASE_URL . '/assets');
define('COMPANY_NAME', 'Grovixo');

// Make directories if not exists
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}
if (!is_dir(BACKUP_DIR)) {
    mkdir(BACKUP_DIR, 0755, true);
}
if (!is_dir(LOG_DIR)) {
    mkdir(LOG_DIR, 0755, true);
}

// App Secret for CSRF protection
if (!isset($_SESSION['app_csrf_secret'])) {
    $_SESSION['app_csrf_secret'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/database.php';
