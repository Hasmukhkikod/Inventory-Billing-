<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'vendor/autoload.php';
require_once 'config/config.php';
require_once 'application/Models/Database.php';
require_once 'application/Models/Auth.php';

try {
    $db = new \App\Models\Database('main');
    $auth = new \App\Models\Auth($db);
    
    $email = "grovixo@gmail.com";
    $password = "Password@123";
    
    echo "Attempting to login with $email and $password...\n";
    $result = $auth->login($email, $password);
    
    if ($result) {
        echo "LOGIN SUCCESSFUL!\n";
        print_r($_SESSION);
    } else {
        echo "LOGIN FAILED!\n";
    }
} catch (Exception $e) {
    echo "CRASH! " . $e->getMessage() . "\n";
}
