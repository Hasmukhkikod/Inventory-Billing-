<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Database Configuration
 */

require_once __DIR__ . '/config.php';

// Database Driver config
define('DB_DRIVER', $_ENV['DB_DRIVER'] ?? 'mysql'); // Options: 'mysql', 'sqlite'

// MySQL Credentials
if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'billingdemo.grovixo.com') {
    // Force Hostinger demo credentials on the demo server (ignores accidental .env uploads)
    define('DB_HOST', 'localhost');
    define('DB_PORT', '3306');
    define('DB_NAME', 'u432404563_billing_demo');
    define('DB_USER', 'u432404563_billing_demo');
    define('DB_PASS', 'Grovixo@2026');
} else {
    // Local / Default credentials
    define('DB_HOST', $_ENV['DB_HOST'] ?? '127.0.0.1');
    define('DB_PORT', $_ENV['DB_PORT'] ?? '3306');
    define('DB_NAME', $_ENV['DB_NAME'] ?? 'invoices_systeam');
    define('DB_USER', $_ENV['DB_USER'] ?? 'root');
    define('DB_PASS', $_ENV['DB_PASS'] ?? '');
}

// SQLite Credentials (fallback)
define('SQLITE_FILE', BASE_DIR . '/database/database.sqlite');
