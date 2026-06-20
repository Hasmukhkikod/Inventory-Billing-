<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Database Configuration
 */

require_once __DIR__ . '/config.php';

// Database Driver config
define('DB_DRIVER', $_ENV['DB_DRIVER'] ?? 'mysql'); // Options: 'mysql', 'sqlite'

// MySQL Credentials
define('DB_HOST', $_ENV['DB_HOST'] ?? '127.0.0.1');
define('DB_PORT', $_ENV['DB_PORT'] ?? '3306');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'invoices_systeam');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

// SQLite Credentials (fallback)
define('SQLITE_FILE', BASE_DIR . '/database/database.sqlite');
