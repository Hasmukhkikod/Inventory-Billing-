<?php
require_once 'vendor/autoload.php';
require_once 'config/config.php';
require_once 'application/Models/Database.php';

function updateAdminPassword($dbName) {
    try {
        $db = new \App\Models\Database($dbName);
        $pdo = $db->getConnection();
        
        $newPassword = "Password@123";
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $pdo->query("UPDATE users SET password = '$hashed' WHERE id = 1");
        
        // Also ensure status is ACTIVE
        $pdo->query("UPDATE users SET status = 'ACTIVE' WHERE id = 1");
        
        // Ensure role is 1
        $pdo->query("UPDATE users SET role_id = 1 WHERE id = 1");
        
        echo "Successfully updated Admin password in '$dbName' database.\n";
    } catch (Exception $e) {
        echo "Error in '$dbName': " . $e->getMessage() . "\n";
    }
}

updateAdminPassword('main');
updateAdminPassword('demo');
