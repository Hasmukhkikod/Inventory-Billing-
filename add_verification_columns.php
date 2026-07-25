<?php
require_once 'vendor/autoload.php';
require_once 'config/config.php';
require_once 'application/Models/Database.php';

$db = new \App\Models\Database();
$pdo = $db->getConnection();

try {
    $pdo->exec("ALTER TABLE organizations ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER status");
    $pdo->exec("ALTER TABLE organizations ADD COLUMN verification_token VARCHAR(100) DEFAULT NULL AFTER is_verified");
    echo "Columns added successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
