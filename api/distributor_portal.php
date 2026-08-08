<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Distributor/Partner Portal - Dashboard, Clients, Commission, Leads
 */
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
use App\Models\Helpers;
use App\Models\Database;
use App\Models\DistributorAuth;

header('Content-Type: application/json');

$db = new Database();
$distAuth = new DistributorAuth($db);

if (!$distAuth->check()) {
    Helpers::jsonResponse(false, 'Your session has expired. Please log in again.');
}

$distributorId = (int)$_SESSION['distributor_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'dashboard':
        try {
            $distributor = $db->query("SELECT id, name, business_name, email, mobile, commission_rate, status, created_at FROM distributors WHERE id = ? LIMIT 1", [$distributorId])->fetch();

            $clients = $db->query("
                SELECT o.id, o.name AS org_name, o.email, o.phone, o.status, o.valid_until, o.start_date,
                       p.plan_name, p.price AS plan_price,
                       dc.assigned_at
                FROM distributor_clients dc
                JOIN organizations o ON dc.organization_id = o.id
                LEFT JOIN plans p ON o.plan_id = p.id
                WHERE dc.distributor_id = ?
                ORDER BY o.valid_until ASC
            ", [$distributorId])->fetchAll();

            $rate = (float)$distributor['commission_rate'];
            $totalCommission = 0;
            $activeCount = 0;
            foreach ($clients as &$c) {
                $price = (float)($c['plan_price'] ?? 0);
                $c['commission'] = round($price * $rate / 100, 2);
                if ($c['status'] === 'ACTIVE') {
                    $totalCommission += $c['commission'];
                    $activeCount++;
                }
            }
            unset($c);

            Helpers::jsonResponse(true, 'Dashboard loaded', [
                'distributor' => $distributor,
                'clients' => $clients,
                'summary' => [
                    'total_clients' => count($clients),
                    'active_clients' => $activeCount,
                    'total_commission' => round($totalCommission, 2),
                ],
            ]);
        } catch (\Exception $e) {
            error_log("Distributor dashboard error: " . $e->getMessage());
            Helpers::jsonResponse(false, 'Could not load dashboard data.');
        }
        break;

    case 'leads_list':
        try {
            $leads = $db->query("SELECT * FROM distributor_leads WHERE distributor_id = ? ORDER BY created_at DESC", [$distributorId])->fetchAll();
            Helpers::jsonResponse(true, 'Leads loaded', $leads);
        } catch (\Exception $e) {
            error_log("Distributor leads list error: " . $e->getMessage());
            Helpers::jsonResponse(false, 'Could not load leads.');
        }
        break;

    case 'lead_add':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, 'Invalid method');
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, 'Security Validation Failed. Please refresh and try again.');

        $leadName = trim($_POST['lead_name'] ?? '');
        $businessName = trim($_POST['business_name'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if (empty($leadName) || empty($mobile)) {
            Helpers::jsonResponse(false, 'Lead name and mobile number are required.');
        }

        try {
            $leadId = $db->insert("
                INSERT INTO distributor_leads (distributor_id, lead_name, business_name, mobile, email, notes, status)
                VALUES (?, ?, ?, ?, ?, ?, 'NEW')
            ", [$distributorId, $leadName, $businessName ?: null, $mobile, $email ?: null, $notes ?: null]);

            Helpers::logActivity($db, "partners", "Partner submitted a new lead: $leadName", $leadId);
            Helpers::jsonResponse(true, 'Lead submitted successfully. Our team will follow up soon.');
        } catch (\Exception $e) {
            error_log("Distributor lead add error: " . $e->getMessage());
            Helpers::jsonResponse(false, 'Could not submit the lead. Please try again.');
        }
        break;

    default:
        Helpers::jsonResponse(false, 'Action not found: ' . $action);
}
