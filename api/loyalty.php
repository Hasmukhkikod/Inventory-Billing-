<?php
/**
 * IIMS v2.0 - Customer Loyalty Points API
 */
require_once __DIR__ . '/../vendor/autoload.php';
use App\Models\Auth;
use App\Models\Helpers;
use App\Models\Database;

require_once __DIR__ . '/../config/database.php';

$db = new Database();
$auth = new Auth($db);

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'balance':
        $auth->requirePermission('Create Invoice');
        $customer_id = (int)($_GET['customer_id'] ?? 0);
        if ($customer_id <= 0) Helpers::jsonResponse(false, 'Invalid customer');

        try {
            $customer = $db->query("SELECT loyalty_points FROM customers WHERE id = ? LIMIT 1", [$customer_id])->fetch();
            Helpers::jsonResponse(true, 'Balance', ['points' => (int)($customer['loyalty_points'] ?? 0)]);
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Failed: ' . $e->getMessage());
        }
        break;

    case 'history':
        $auth->requirePermission('Manage Customers');
        $customer_id = (int)($_GET['customer_id'] ?? 0);
        if ($customer_id <= 0) Helpers::jsonResponse(false, 'Invalid customer');

        try {
            $stmt = $db->query("
                SELECT lt.*, i.invoice_no
                FROM loyalty_transactions lt
                LEFT JOIN invoices i ON lt.invoice_id = i.id
                WHERE lt.customer_id = ? AND lt.status = 'ACTIVE'
                ORDER BY lt.created_at DESC
                LIMIT 100
            ", [$customer_id]);
            Helpers::jsonResponse(true, 'History', $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Failed: ' . $e->getMessage());
        }
        break;

    case 'adjust':
        $auth->requirePermission('Manage Customers');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, 'Method not allowed');
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, 'CSRF verification failed');

        $customer_id = (int)($_POST['customer_id'] ?? 0);
        $points = (int)($_POST['points'] ?? 0);
        $remarks = trim($_POST['remarks'] ?? '');

        if ($customer_id <= 0) Helpers::jsonResponse(false, 'Invalid customer');
        if ($points === 0) Helpers::jsonResponse(false, 'Points cannot be zero');

        try {
            $customer = $db->query("SELECT loyalty_points FROM customers WHERE id = ? LIMIT 1", [$customer_id])->fetch();
            if (!$customer) Helpers::jsonResponse(false, 'Customer not found');

            $currentPoints = (int)$customer['loyalty_points'];
            $newBalance = $currentPoints + $points;
            if ($newBalance < 0) Helpers::jsonResponse(false, 'Insufficient points for deduction');

            $db->query("UPDATE customers SET loyalty_points = ? WHERE id = ?", [$newBalance, $customer_id]);
            $db->insert("INSERT INTO loyalty_transactions (customer_id, points, type, balance_after, remarks, created_by) VALUES (?,?,?,?,?,?)",
                [$customer_id, $points, 'ADJUSTED', $newBalance, $remarks, $_SESSION['user_id']]);

            Helpers::logActivity($db, 'loyalty', "Adjusted $points points for customer #$customer_id", $customer_id);
            Helpers::jsonResponse(true, 'Points adjusted', ['new_balance' => $newBalance]);
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Failed: ' . $e->getMessage());
        }
        break;

    default:
        Helpers::jsonResponse(false, 'Unknown action: ' . $action);
}
