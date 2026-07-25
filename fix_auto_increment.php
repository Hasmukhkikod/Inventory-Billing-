<?php
require_once 'vendor/autoload.php';
require_once 'config/config.php';
require_once 'application/Models/Database.php';

echo "<pre>";
echo "<h3>Database Auto-Increment Fixer</h3>";

try {
    // Fix Demo Database
    echo "<b>Fixing DEMO database...</b>\n";
    $dbDemo = new \App\Models\Database('demo');
    $pdoDemo = $dbDemo->getConnection();
    
    $pdoDemo->exec("ALTER TABLE `organizations` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");
    echo "✅ Fixed AUTO_INCREMENT for demo organizations table.\n";
    
    $pdoDemo->exec("ALTER TABLE `users` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");
    echo "✅ Fixed AUTO_INCREMENT for demo users table.\n";

} catch (Exception $e) {
    echo "⚠️ Notice (Demo DB): " . $e->getMessage() . "\n";
}

try {
    // Fix Main Database
    echo "\n<b>Fixing MAIN database...</b>\n";
    $dbMain = new \App\Models\Database();
    $pdoMain = $dbMain->getConnection();
    
    $pdoMain->exec("ALTER TABLE `organizations` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");
    echo "✅ Fixed AUTO_INCREMENT for main organizations table.\n";
    
    $pdoMain->exec("ALTER TABLE `users` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");
    echo "✅ Fixed AUTO_INCREMENT for main users table.\n";

} catch (Exception $e) {
    echo "⚠️ Notice (Main DB): " . $e->getMessage() . "\n";
}

echo "\n<b style='color:green;'>All tables have been successfully repaired!</b>\n";
echo "You can now go back and register your Free Trial.</pre>";
