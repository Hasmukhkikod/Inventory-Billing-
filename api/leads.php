<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * API - Partner Leads (Super Admin only)
 */
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
use App\Models\Database;
use App\Models\Auth;
use App\Models\Helpers;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = new Database();
$auth = new Auth($db);

if (!$auth->check() || $_SESSION['user_id'] != 1) {
    Helpers::jsonResponse(false, "Unauthorized access.");
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        try {
            $leads = $db->query("
                SELECT dl.*, d.name AS distributor_name, d.business_name, d.email AS distributor_email
                FROM distributor_leads dl
                JOIN distributors d ON dl.distributor_id = d.id
                ORDER BY dl.created_at DESC
            ")->fetchAll();
            Helpers::jsonResponse(true, "Leads list", $leads);
        } catch (\Exception $e) {
            Helpers::jsonResponse(false, "Failed to load: " . $e->getMessage());
        }
        break;

    case 'update_status':
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, "CSRF verification failed");
        $id = (int)($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? '');

        if ($id <= 0) Helpers::jsonResponse(false, "Invalid lead.");
        if (!in_array($status, ['NEW', 'CONTACTED', 'CONVERTED', 'REJECTED'])) Helpers::jsonResponse(false, "Invalid status.");

        try {
            $db->query("UPDATE distributor_leads SET status = ? WHERE id = ?", [$status, $id]);
            Helpers::logActivity($db, "partners", "Updated lead #$id status to $status", $id);
            Helpers::jsonResponse(true, "Lead status updated.");
        } catch (\Exception $e) {
            Helpers::jsonResponse(false, "Failed to update: " . $e->getMessage());
        }
        break;

    default:
        Helpers::jsonResponse(false, "Action not found: " . $action);
}
