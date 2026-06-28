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
                SELECT ci.*, p.product_name, p.sku,
                       p.secondary_unit_id, p.conversion_factor,
                       un.id as unit_id, un.short_name as unit_name,
                       su.short_name as secondary_unit_name,
                       ci.billing_unit_id, ci.billing_unit_name, ci.primary_qty
                FROM challan_items ci
                JOIN products p ON ci.product_id = p.id
                LEFT JOIN units un ON p.unit_id = un.id
                LEFT JOIN units su ON p.secondary_unit_id = su.id
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

        $challan_id = (int)($_POST['id'] ?? 0);
        $customer_id = !empty($_POST['customer_id']) ? (int)$_POST['customer_id'] : null;
        $challan_date = $_POST['challan_date'] ?? date('Y-m-d');
        $transport_name = trim($_POST['transport_name'] ?? '');
        $vehicle_no = trim($_POST['vehicle_no'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $invoice_id = !empty($_POST['invoice_id']) ? (int)$_POST['invoice_id'] : null;
        $cart = json_decode($_POST['cart'] ?? '[]', true);

        if (empty($cart)) Helpers::jsonResponse(false, 'Add items to the challan');

        try {
            $result = $db->transaction(function($t) use ($db, $challan_id, $customer_id, $challan_date, $transport_name, $vehicle_no, $notes, $invoice_id, $cart) {

                if ($challan_id > 0) {
                    // UPDATE existing challan
                    $existing = $t->query("SELECT * FROM challans WHERE id = ? LIMIT 1", [$challan_id])->fetch();
                    if (!$existing) throw new Exception("Challan not found for update.");
                    if (in_array($existing['status'], ['DELIVERED', 'CANCELLED'])) {
                        throw new Exception("Cannot edit a " . strtolower($existing['status']) . " challan.");
                    }

                    $t->query("UPDATE challans SET customer_id = ?, challan_date = ?, transport_name = ?, vehicle_no = ?, notes = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                        [$customer_id, $challan_date, $transport_name, $vehicle_no, $notes, $challan_id]);

                    // Remove old items and insert new
                    $t->query("DELETE FROM challan_items WHERE challan_id = ?", [$challan_id]);
                    foreach ($cart as $item) {
                        $pid = (int)($item['product_id'] ?? $item['id'] ?? 0);
                        $qty = (float)($item['quantity'] ?? $item['qty'] ?? 0);
                        $b_unit_id = (int)($item['billing_unit_id'] ?? 0);
                        $b_unit_name = trim($item['billing_unit_name'] ?? '');
                        $is_sec = (int)($item['is_secondary_unit'] ?? 0);
                        $cf = (float)($item['conversion_factor'] ?? 0);
                        $p_qty = ($is_sec && $cf > 0) ? $qty / $cf : $qty;
                        $t->insert("INSERT INTO challan_items (challan_id, product_id, billing_unit_id, billing_unit_name, quantity, primary_qty, created_by) VALUES (?,?,?,?,?,?,?)",
                            [$challan_id, $pid, $b_unit_id ?: null, $b_unit_name ?: null, $qty, $p_qty, $_SESSION['user_id']]);
                    }

                    Helpers::logActivity($db, 'challans', "Updated challan: " . $existing['challan_no'], $challan_id);
                    return ['challan_id' => $challan_id, 'challan_no' => $existing['challan_no'], 'action' => 'updated'];

                } else {
                    // CREATE new challan
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

                    $newId = $t->insert("
                        INSERT INTO challans (challan_no, customer_id, invoice_id, challan_date, transport_name, vehicle_no, notes, created_by)
                        VALUES (?,?,?,?,?,?,?,?)
                    ", [$challan_no, $customer_id, $invoice_id, $challan_date, $transport_name, $vehicle_no, $notes, $_SESSION['user_id']]);

                    foreach ($cart as $item) {
                        $pid = (int)($item['product_id'] ?? $item['id'] ?? 0);
                        $qty = (float)($item['quantity'] ?? $item['qty'] ?? 0);
                        $t->insert("INSERT INTO challan_items (challan_id, product_id, quantity, created_by) VALUES (?,?,?,?)",
                            [$newId, $pid, $qty, $_SESSION['user_id']]);
                    }

                    Helpers::logActivity($db, 'challans', "Created challan: $challan_no", (int)$newId);
                    return ['challan_id' => $newId, 'challan_no' => $challan_no, 'action' => 'created'];
                }
            });

            $msg = ($result['action'] ?? '') === 'updated' ? 'Challan updated successfully.' : 'Delivery challan created: ' . $result['challan_no'];
            Helpers::jsonResponse(true, $msg, $result);
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

    case 'bulk':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, 'Method not allowed');
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, 'CSRF verification failed');

        $bulk_action = trim($_POST['bulk_action'] ?? '');
        $ids = json_decode($_POST['ids'] ?? '[]', true);

        if (empty($ids)) Helpers::jsonResponse(false, 'No records selected');

        try {
            if ($bulk_action === 'delete') {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $db->query("UPDATE challans SET status = 'INACTIVE', deleted_at = CURRENT_TIMESTAMP WHERE id IN ($placeholders)", $ids);
                Helpers::logActivity($db, 'challans', 'Bulk deleted ' . count($ids) . ' records');
                Helpers::jsonResponse(true, count($ids) . ' records deleted successfully');
            }
            Helpers::jsonResponse(false, 'Unknown bulk action');
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Bulk action failed: ' . $e->getMessage());
        }
        break;

    default:
        Helpers::jsonResponse(false, 'Unknown action: ' . $action);
}
