<?php
require_once 'vendor/autoload.php';
require_once 'config/config.php';
require_once 'application/Models/Database.php';

session_start();
$_SESSION['is_demo'] = true;

$db = new \App\Models\Database();
$pdo = $db->getConnection();

echo "<pre>";
$email = 'joshi' . rand(1000, 9999) . '@gmail.com';
$firstName = 'Joshi';
$lastName = 'Test';
$orgName = $firstName . " " . $lastName . "'s Business";
$plan_id = 0;
$start_date = date('Y-m-d');
$valid_until = date('Y-m-d', strtotime('+15 days'));
$verification_token = bin2hex(random_bytes(32));
$password = 'password123';

try {
    echo "Inserting org...\n";
    $newOrgId = $db->insert(
        "INSERT INTO organizations (name, email, phone, plan_id, start_date, valid_until, status, is_verified, verification_token, is_approved) VALUES (?, ?, '', ?, ?, ?, 'ACTIVE', 0, ?, 1)", 
        [$orgName, $email, $plan_id, $start_date, $valid_until, $verification_token]
    );
    echo "Org ID: $newOrgId\n";

    echo "Inserting user...\n";
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $db->insert(
        "INSERT INTO users (name, email, mobile, password, role_id, org_id, status) VALUES (?, ?, '', ?, 2, ?, 'ACTIVE')",
        [$firstName . " " . $lastName, $email, $hashedPassword, $newOrgId]
    );
    echo "User created successfully.\n";
} catch (Exception $e) {
    echo "\nEXCEPTION CAUGHT:\n";
    echo $e->getMessage() . "\n";
}
echo "</pre>";
