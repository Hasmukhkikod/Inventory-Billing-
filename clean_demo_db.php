<?php
require_once 'vendor/autoload.php';
require_once 'config/config.php';
require_once 'application/Models/Database.php';

echo "<pre>";

try {
    $db = new \App\Models\Database('demo');
    $pdo = $db->getConnection();
    
    $email = 'hasmukhkikod7874@gmail.com';
    
    $user = $pdo->query("SELECT * FROM users WHERE email = '$email'")->fetch();
    if ($user) {
        echo "Found user in DEMO database:\n";
        print_r($user);
        $pdo->exec("DELETE FROM users WHERE email = '$email'");
        echo "DELETED user from DEMO database.\n";
    } else {
        echo "User NOT found in DEMO database.\n";
    }
    
    $org = $pdo->query("SELECT * FROM organizations WHERE email = '$email'")->fetch();
    if ($org) {
        echo "Found org in DEMO database:\n";
        print_r($org);
        $pdo->exec("DELETE FROM organizations WHERE email = '$email'");
        echo "DELETED org from DEMO database.\n";
    } else {
        echo "Org NOT found in DEMO database.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "Done!</pre>";
