<?php
require_once 'vendor/autoload.php';
require_once 'config/config.php';
require_once 'application/Models/Database.php';

try {
    $db = new \App\Models\Database('main');
    $pdo = $db->getConnection();
    
    $stmt = $pdo->query("SELECT * FROM roles WHERE id = 1");
    $role = $stmt->fetch();
    
    if ($role) {
        echo "Role 1 exists: " . $role['role_name'] . "\n";
    } else {
        echo "Role 1 DOES NOT EXIST!\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
