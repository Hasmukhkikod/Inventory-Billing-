<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * API - Distributors / Partners (Super Admin only)
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
            $distributors = $db->query("
                SELECT d.*, COUNT(dc.id) AS client_count
                FROM distributors d
                LEFT JOIN distributor_clients dc ON dc.distributor_id = d.id
                WHERE d.deleted_at IS NULL
                GROUP BY d.id
                ORDER BY d.created_at DESC
            ")->fetchAll();
            Helpers::jsonResponse(true, "Distributors list", $distributors);
        } catch (\Exception $e) {
            Helpers::jsonResponse(false, "Failed to load: " . $e->getMessage());
        }
        break;

    case 'update':
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, "CSRF verification failed");
        $id = (int)($_POST['id'] ?? 0);
        $commissionRate = (float)($_POST['commission_rate'] ?? 0);
        $status = trim($_POST['status'] ?? '');

        if ($id <= 0) Helpers::jsonResponse(false, "Invalid distributor.");
        if (!in_array($status, ['ACTIVE', 'INACTIVE', 'PENDING'])) Helpers::jsonResponse(false, "Invalid status.");
        if ($commissionRate < 0 || $commissionRate > 100) Helpers::jsonResponse(false, "Commission rate must be between 0 and 100.");

        try {
            $db->query("UPDATE distributors SET commission_rate = ?, status = ? WHERE id = ?", [$commissionRate, $status, $id]);
            Helpers::logActivity($db, "partners", "Updated distributor #$id (rate: $commissionRate%, status: $status)", $id);
            Helpers::jsonResponse(true, "Distributor updated.");
        } catch (\Exception $e) {
            Helpers::jsonResponse(false, "Failed to update: " . $e->getMessage());
        }
        break;

    case 'delete':
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, "CSRF verification failed");
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) Helpers::jsonResponse(false, "Invalid distributor.");

        try {
            $db->query("UPDATE distributors SET deleted_at = CURRENT_TIMESTAMP, status = 'INACTIVE' WHERE id = ?", [$id]);
            Helpers::logActivity($db, "partners", "Deleted distributor #$id", $id);
            Helpers::jsonResponse(true, "Distributor removed.");
        } catch (\Exception $e) {
            Helpers::jsonResponse(false, "Failed to remove: " . $e->getMessage());
        }
        break;

    case 'unassigned_orgs':
        try {
            $orgs = $db->query("
                SELECT o.id, o.name, o.email, p.plan_name
                FROM organizations o
                LEFT JOIN plans p ON o.plan_id = p.id
                WHERE o.id NOT IN (SELECT organization_id FROM distributor_clients)
                ORDER BY o.name ASC
            ")->fetchAll();
            Helpers::jsonResponse(true, "Unassigned organizations", $orgs);
        } catch (\Exception $e) {
            Helpers::jsonResponse(false, "Failed to load: " . $e->getMessage());
        }
        break;

    case 'clients_for':
        $distributorId = (int)($_GET['distributor_id'] ?? 0);
        if ($distributorId <= 0) Helpers::jsonResponse(false, "Invalid distributor.");
        try {
            $clients = $db->query("
                SELECT dc.id AS link_id, o.id AS org_id, o.name AS org_name, o.status, o.valid_until, p.plan_name
                FROM distributor_clients dc
                JOIN organizations o ON dc.organization_id = o.id
                LEFT JOIN plans p ON o.plan_id = p.id
                WHERE dc.distributor_id = ?
                ORDER BY o.name ASC
            ", [$distributorId])->fetchAll();
            Helpers::jsonResponse(true, "Clients", $clients);
        } catch (\Exception $e) {
            Helpers::jsonResponse(false, "Failed to load: " . $e->getMessage());
        }
        break;

    case 'assign_client':
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, "CSRF verification failed");
        $distributorId = (int)($_POST['distributor_id'] ?? 0);
        $organizationId = (int)($_POST['organization_id'] ?? 0);

        if ($distributorId <= 0 || $organizationId <= 0) Helpers::jsonResponse(false, "Please choose both a distributor and an organization.");

        try {
            $existing = $db->query("SELECT id FROM distributor_clients WHERE organization_id = ?", [$organizationId])->fetch();
            if ($existing) {
                Helpers::jsonResponse(false, "This organization is already assigned to a partner.");
            }
            $db->insert("INSERT INTO distributor_clients (distributor_id, organization_id) VALUES (?, ?)", [$distributorId, $organizationId]);
            Helpers::logActivity($db, "partners", "Assigned organization #$organizationId to distributor #$distributorId", $distributorId);
            Helpers::jsonResponse(true, "Client assigned successfully.");
        } catch (\Exception $e) {
            Helpers::jsonResponse(false, "Failed to assign: " . $e->getMessage());
        }
        break;

    case 'unassign_client':
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, "CSRF verification failed");
        $linkId = (int)($_POST['link_id'] ?? 0);
        if ($linkId <= 0) Helpers::jsonResponse(false, "Invalid record.");

        try {
            $db->query("DELETE FROM distributor_clients WHERE id = ?", [$linkId]);
            Helpers::logActivity($db, "partners", "Unassigned client link #$linkId");
            Helpers::jsonResponse(true, "Client unassigned.");
        } catch (\Exception $e) {
            Helpers::jsonResponse(false, "Failed to unassign: " . $e->getMessage());
        }
        break;

    default:
        Helpers::jsonResponse(false, "Action not found: " . $action);
}
