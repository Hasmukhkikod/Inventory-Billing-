<?php
require_once 'vendor/autoload.php';
require_once 'config/config.php';
require_once 'application/Models/Database.php';

try {
    $db = new \App\Models\Database('main');
    $pdo = $db->getConnection();
    
    $stmt = $pdo->query("SELECT * FROM activity_logs WHERE module = 'auth' ORDER BY id DESC LIMIT 5");
    $logs = $stmt->fetchAll();
    
    echo "Recent Auth Logs:\n";
    print_r($logs);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
