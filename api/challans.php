<?php
/**
 * IIMS v2.0 - Delivery Challan API
 */
require_once __DIR__ . '/../vendor/autoload.php';
use App\Models\Auth;
use App\Models\Helpers;
use App\Models\Database;

require_once __DIR__ . '/../config/database.php';

$db = new Database();
$auth = new Auth($db);
$auth->requirePermission('Manage Challans');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        try {
            $stmt = $db->query("
                SELECT ch.*, ch.status as challan_status, c.customer_name
                FROM challans ch
                LEFT JOIN customers c ON ch.customer_id = c.id
                WHERE ch.status != 'INACTIVE' AND ch.deleted_at IS NULL
                ORDER BY ch.created_at DESC
            ");
            Helpers::jsonResponse(true, 'Challans list', $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Failed: ' . $e->getMessage());
        }
        break;

    case 'get':
        $id = (int)($_GET['id'] ?? 0);
        try {
            $challan = $db->query("
                SELECT ch.*, c.customer_name, c.mobile as customer_mobile, c.address as customer_address, u.name as created_by_name
                FROM challans ch LEFT JOIN customers c ON ch.customer_id = c.id LEFT JOIN users u ON ch.created_by = u.id
                WHERE ch.id = ? LIMIT 1
            ", [$id])->fetch();
            if (!$challan) Helpers::jsonResponse(false, 'Challan not found');

            $items = $db->query("
                SELECT ci.*, p.product_name, p.sku, un.short_name as unit_name
                FROM challan_items ci JOIN products p ON ci.product_id = p.id LEFT JOIN units un ON p.unit_id = un.id
                WHERE ci.challan_id = ?
            ", [$id])->fetchAll();

            $challan['items'] = $items;
            Helpers::jsonResponse(true, 'Challan details', $challan);
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Failed: ' . $e->getMessage());
        }
        break;

    case 'save':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, 'Method not allowed');
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, 'CSRF verification failed');

        $customer_id = !empty($_POST['customer_id']) ? (int)$_POST['customer_id'] : null;
        $challan_date = $_POST['challan_date'] ?? date('Y-m-d');
        $transport_name = trim($_POST['transport_name'] ?? '');
        $vehicle_no = trim($_POST['vehicle_no'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $invoice_id = !empty($_POST['invoice_id']) ? (int)$_POST['invoice_id'] : null;
        $cart = json_decode($_POST['cart'] ?? '[]', true);

        if (empty($cart)) Helpers::jsonResponse(false, 'Add items to the challan');

        try {
            $db->transaction(function($t) use ($customer_id, $challan_date, $transport_name, $vehicle_no, $notes, $invoice_id, $cart) {
                $prefQ = $t->query("SELECT challan_prefix, challan_start, challan_end FROM company_settings WHERE id = 1 LIMIT 1")->fetch();
                $prefix = $prefQ['challan_prefix'] ?? 'DC-';
                $startNum = (int)($prefQ['challan_start'] ?? 1);
                $endNum = (int)($prefQ['challan_end'] ?? 99999);
                $year = date('Y');
                $count = $t->query("SELECT COUNT(*) as c FROM challans WHERE challan_date LIKE ?", ["$year-%"])->fetch();
                $nextNum = $startNum + (int)($count['c'] ?? 0);
                if ($nextNum > $endNum) {
                    throw new Exception("Challan number limit reached ($endNum). Update range in Settings.");
                }
                $seq = str_pad($nextNum, 5, '0', STR_PAD_LEFT);
                $challan_no = $prefix . $year . '-' . $seq;

                $challanId = $t->insert("
                    INSERT INTO challans (challan_no, customer_id, invoice_id, challan_date, transport_name, vehicle_no, notes, created_by)
                    VALUES (?,?,?,?,?,?,?,?)
                ", [$challan_no, $customer_id, $invoice_id, $challan_date, $transport_name, $vehicle_no, $notes, $_SESSION['user_id']]);

                foreach ($cart as $item) {
                    $t->insert("INSERT INTO challan_items (challan_id, product_id, quantity, created_by) VALUES (?,?,?,?)",
                        [$challanId, (int)$item['id'], (float)$item['qty'], $_SESSION['user_id']]);
                }

                Helpers::logActivity($t, 'challans', "Created challan: $challan_no", (int)$challanId);
                Helpers::jsonResponse(true, 'Delivery challan created: ' . $challan_no, ['id' => $challanId, 'challan_no' => $challan_no]);
            });
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Failed: ' . $e->getMessage());
        }
        break;

    case 'update_status':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, 'Method not allowed');
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, 'CSRF verification failed');

        $id = (int)($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        $valid = ['ACTIVE', 'DELIVERED', 'CANCELLED'];
        if (!in_array($status, $valid)) Helpers::jsonResponse(false, 'Invalid status');

        try {
            $db->query("UPDATE challans SET status = ? WHERE id = ?", [$status, $id]);
            Helpers::logActivity($db, 'challans', "Updated challan #$id status to $status", $id);
            Helpers::jsonResponse(true, 'Status updated');
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Failed: ' . $e->getMessage());
        }
        break;

    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, 'Method not allowed');
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, 'CSRF verification failed');

        $id = (int)($_POST['id'] ?? 0);
        try {
            $db->query("UPDATE challans SET status='INACTIVE', deleted_at=CURRENT_TIMESTAMP WHERE id=?", [$id]);
            Helpers::jsonResponse(true, 'Challan deleted');
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Failed: ' . $e->getMessage());
        }
        break;

    default:
        Helpers::jsonResponse(false, 'Unknown action: ' . $action);
}
