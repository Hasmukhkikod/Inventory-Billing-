<?php
require_once 'vendor/autoload.php';
require_once 'config/config.php';
require_once 'application/Models/Database.php';

echo "<pre>";

function fixUsersTable($dbName) {
    echo "Fixing users table for $dbName...\n";
    try {
        $db = new \App\Models\Database($dbName);
        $pdo = $db->getConnection();
        
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `org_id` int(11) DEFAULT 1;");
        $pdo->exec("ALTER TABLE `users` ADD INDEX `idx_org_id` (`org_id`);");
        echo "Successfully added org_id to users in $dbName\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "Column org_id already exists in users in $dbName\n";
        } else {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
}

fixUsersTable('main');
fixUsersTable('demo');

echo "Done!</pre>";
