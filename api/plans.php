<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * API - Plans
 */
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../application/Models/Database.php';
require_once __DIR__ . '/../application/Models/Auth.php';
require_once __DIR__ . '/../application/Models/Helpers.php';

use App\Models\Database;
use App\Models\Auth;
use App\Models\Helpers;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = new Database();
$auth = new Auth($db);

// Only Super Admin can access
if (!$auth->check() || $_SESSION['user_id'] != 1) {
    Helpers::jsonResponse(false, "Unauthorized access.");
}

$action = $_GET['action'] ?? '';

if ($action === 'save') {
    Helpers::verifyCsrf();
    
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $plan_name = trim($_POST['plan_name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $duration_days = (int)($_POST['duration_days'] ?? 30);
    $status = $_POST['status'] ?? 'ACTIVE';

    if (empty($plan_name)) {
        Helpers::jsonResponse(false, "Plan name is required.");
    }

    try {
        if ($id > 0) {
            $db->query("UPDATE plans SET plan_name = ?, price = ?, duration_days = ?, status = ? WHERE id = ?", 
                       [$plan_name, $price, $duration_days, $status, $id]);
            Helpers::logActivity($db, "plans", "Updated plan #$id", $id);
            Helpers::jsonResponse(true, "Plan updated successfully.");
        } else {
            $newId = $db->insert("INSERT INTO plans (plan_name, price, duration_days, status) VALUES (?, ?, ?, ?)", 
                                 [$plan_name, $price, $duration_days, $status]);
            Helpers::logActivity($db, "plans", "Created new plan #$newId", $newId);
            Helpers::jsonResponse(true, "Plan created successfully.");
        }
    } catch (Exception $e) {
        Helpers::jsonResponse(false, "Error: " . $e->getMessage());
    }
}

Helpers::jsonResponse(false, "Invalid action.");
