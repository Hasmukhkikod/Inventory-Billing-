<?php
require_once 'vendor/autoload.php';
require_once 'config/config.php';
require_once 'application/Models/Database.php';

$db = new \App\Models\Database();
$pdo = $db->getConnection();

try {
    $pdo->exec("ALTER TABLE company_settings ADD COLUMN demo_popup_days_before INT DEFAULT 2 AFTER company_logo");
    $pdo->exec("ALTER TABLE company_settings ADD COLUMN demo_popup_timer_minutes INT DEFAULT 30 AFTER demo_popup_days_before");
    echo "Settings columns added successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
