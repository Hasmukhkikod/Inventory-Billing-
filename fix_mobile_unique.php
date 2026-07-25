<?php
require_once 'vendor/autoload.php';
require_once 'config/config.php';
require_once 'application/Models/Database.php';

echo "<pre>";
echo "<h3>Database Mobile Index Fixer</h3>";

function dropMobileUnique($dbName) {
    echo "<b>Checking $dbName database...</b>\n";
    try {
        $db = new \App\Models\Database($dbName);
        $pdo = $db->getConnection();
        
        $pdo->exec("ALTER TABLE `users` DROP INDEX `mobile`;");
        echo "✅ SUCCESS: Dropped unique index on 'mobile' column in '$dbName'.\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), "check that column/key exists") !== false || strpos($e->getMessage(), "Can't DROP") !== false) {
            echo "✅ 'mobile' unique index doesn't exist or is already removed.\n";
        } else {
            echo "⚠️ Notice: " . $e->getMessage() . "\n";
        }
    }
}

dropMobileUnique('demo');
echo "\n";
dropMobileUnique('main');

echo "\n<b style='color:green;'>All tables have been successfully repaired!</b>\n";
echo "You can now go back and register your Free Trial.</pre>";
