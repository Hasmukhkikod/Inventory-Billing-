<?php
require_once 'vendor/autoload.php';
require_once 'config/config.php';
require_once 'application/Models/Database.php';

session_start();
$_SESSION['is_demo'] = true;

$db = new \App\Models\Database();
$pdo = $db->getConnection();

echo "<pre>";
echo "<h3>Database Diagnostic Tool</h3>";

$email = $_GET['email'] ?? 'test_' . rand(1000, 9999) . '@gmail.com';
echo "<b>Testing with Email:</b> $email\n\n";

// 1. Check if it exists
$user = $db->query("SELECT id FROM users WHERE email = ? LIMIT 1", [$email])->fetch();
$org = $db->query("SELECT id FROM organizations WHERE email = ? LIMIT 1", [$email])->fetch();

echo "<b>1. Does this email already exist in the database?</b>\n";
if ($user) {
    echo "❌ YES - Found in 'users' table (ID: " . $user['id'] . ")\n";
} else {
    echo "✅ NO - Not in 'users' table.\n";
}
if ($org) {
    echo "❌ YES - Found in 'organizations' table (ID: " . $org['id'] . ")\n";
} else {
    echo "✅ NO - Not in 'organizations' table.\n";
}

if ($user || $org) {
    echo "\n<b style='color:red;'>STOP: The registration is blocking because this email IS actually in the database!</b>\n";
    echo "\nAttempting to delete it to fix the issue...\n";
    $db->query("DELETE FROM users WHERE email = ?", [$email]);
    $db->query("DELETE FROM organizations WHERE email = ?", [$email]);
    echo "✅ Deleted successfully! Try refreshing this page to run the insert test.\n";
    exit;
}

// 2. Try to insert
echo "\n<b>2. Attempting to create a new registration (like the form does)...</b>\n";
$orgName = "Test Business";
$plan_id = 0;
$start_date = date('Y-m-d');
$valid_until = date('Y-m-d', strtotime('+15 days'));
$verification_token = bin2hex(random_bytes(32));

try {
    $newOrgId = $db->insert(
        "INSERT INTO organizations (name, email, phone, plan_id, start_date, valid_until, status, is_verified, verification_token, is_approved) VALUES (?, ?, '', ?, ?, ?, 'ACTIVE', 0, ?, 1)", 
        [$orgName, $email, $plan_id, $start_date, $valid_until, $verification_token]
    );
    echo "✅ Organization inserted successfully! (ID: $newOrgId)\n";

    $password = 'password123';
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $db->insert(
        "INSERT INTO users (name, email, mobile, password, role_id, org_id, status) VALUES (?, ?, '', ?, 2, ?, 'ACTIVE')",
        ["Test User", $email, $hashedPassword, $newOrgId]
    );
    echo "✅ User inserted successfully!\n";
    
    // Clean up
    $db->query("DELETE FROM users WHERE email = ?", [$email]);
    $db->query("DELETE FROM organizations WHERE email = ?", [$email]);
    echo "\n<b style='color:green;'>SUCCESS! The database is working perfectly. No errors.</b>\n";

} catch (Exception $e) {
    echo "\n<b style='color:red;'>❌ EXCEPTION CRASH CAUGHT!</b>\n";
    echo "This is the REAL error that is causing the problem:\n";
    echo "<b>" . $e->getMessage() . "</b>\n";
    
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        echo "\n\n<i>Notice: Because the error contains 'Duplicate entry', the old register.php file assumes the email is already registered and shows you the 'This email is already registered' message!</i>\n";
    }
}
echo "</pre>";
