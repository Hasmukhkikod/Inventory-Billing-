<?php
require_once 'vendor/autoload.php';
require_once 'config/config.php';
require_once 'application/Models/Database.php';

try {
    $db = new \App\Models\Database('main');
    $pdo = $db->getConnection();
    
    $stmt = $pdo->query("SELECT id, name, email, password FROM users WHERE id = 1");
    $user = $stmt->fetch();
    
    echo "Hash in DB: " . $user['password'] . "\n";
    
    $testPassword = "Password@123";
    if (password_verify($testPassword, $user['password'])) {
        echo "YES! It matches Password@123 perfectly.\n";
    } else {
        echo "NO! It DOES NOT MATCH Password@123!\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
