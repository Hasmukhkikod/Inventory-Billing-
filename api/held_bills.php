<?php
/**
 * IIMS v2.0 - Held Bills API (Hold & Recall)
 */
require_once __DIR__ . '/../vendor/autoload.php';
use App\Models\Auth;
use App\Models\Helpers;
use App\Models\Database;

require_once __DIR__ . '/../config/database.php';

$db = new Database();
$auth = new Auth($db);
$auth->requirePermission('Create Invoice');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'hold':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, 'Method not allowed');
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, 'CSRF verification failed');

        $customer_id = !empty($_POST['customer_id']) ? (int)$_POST['customer_id'] : null;
        $bill_note = trim($_POST['bill_note'] ?? '');
        $cart_data = $_POST['cart_data'] ?? '[]';
        $subtotal = (float)($_POST['subtotal'] ?? 0);
        $invoice_type = trim($_POST['invoice_type'] ?? 'RETAIL');

        $cart = json_decode($cart_data, true);
        if (empty($cart)) Helpers::jsonResponse(false, 'Cart is empty');

        try {
            $db->insert("
                INSERT INTO held_bills (customer_id, bill_note, cart_data, subtotal, invoice_type, created_by)
                VALUES (?, ?, ?, ?, ?, ?)
            ", [$customer_id, $bill_note, $cart_data, $subtotal, $invoice_type, $_SESSION['user_id']]);

            Helpers::logActivity($db, 'billing', 'Held bill for later', null);
            Helpers::jsonResponse(true, 'Bill held successfully');
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Failed to hold bill: ' . $e->getMessage());
        }
        break;

    case 'list':
        try {
            $stmt = $db->query("
                SELECT hb.*, c.customer_name
                FROM held_bills hb
                LEFT JOIN customers c ON hb.customer_id = c.id
                WHERE hb.status = 'ACTIVE' AND hb.deleted_at IS NULL
                ORDER BY hb.created_at DESC
            ");
            Helpers::jsonResponse(true, 'Held bills', $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Failed: ' . $e->getMessage());
        }
        break;

    case 'recall':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, 'Method not allowed');
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, 'CSRF verification failed');

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) Helpers::jsonResponse(false, 'Invalid bill ID');

        try {
            $bill = $db->query("SELECT * FROM held_bills WHERE id = ? AND status = 'ACTIVE' LIMIT 1", [$id])->fetch();
            if (!$bill) Helpers::jsonResponse(false, 'Held bill not found');

            $db->query("UPDATE held_bills SET status = 'INACTIVE', deleted_at = CURRENT_TIMESTAMP WHERE id = ?", [$id]);
            Helpers::logActivity($db, 'billing', 'Recalled held bill #' . $id, $id);
            Helpers::jsonResponse(true, 'Bill recalled', $bill);
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Failed: ' . $e->getMessage());
        }
        break;

    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, 'Method not allowed');
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, 'CSRF verification failed');

        $id = (int)($_POST['id'] ?? 0);
        try {
            $db->query("UPDATE held_bills SET status = 'INACTIVE', deleted_at = CURRENT_TIMESTAMP WHERE id = ?", [$id]);
            Helpers::jsonResponse(true, 'Held bill deleted');
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Failed: ' . $e->getMessage());
        }
        break;

    default:
        Helpers::jsonResponse(false, 'Unknown action: ' . $action);
}
