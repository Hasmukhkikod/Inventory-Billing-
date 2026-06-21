<?php
/**
 * IIMS v2.0 - Coupon Management API
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
    case 'list':
        $auth->requirePermission('Manage Coupons');
        try {
            $stmt = $db->query("SELECT * FROM coupons WHERE status != 'INACTIVE' AND deleted_at IS NULL ORDER BY created_at DESC");
            Helpers::jsonResponse(true, 'Coupons list', $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Failed: ' . $e->getMessage());
        }
        break;

    case 'get':
        $auth->requirePermission('Manage Coupons');
        $id = (int)($_GET['id'] ?? 0);
        try {
            $coupon = $db->query("SELECT * FROM coupons WHERE id = ? LIMIT 1", [$id])->fetch();
            if (!$coupon) Helpers::jsonResponse(false, 'Coupon not found');
            Helpers::jsonResponse(true, 'Coupon details', $coupon);
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Failed: ' . $e->getMessage());
        }
        break;

    case 'save':
        $auth->requirePermission('Manage Coupons');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, 'Method not allowed');
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, 'CSRF verification failed');

        $id = (int)($_POST['id'] ?? 0);
        $code = strtoupper(trim($_POST['coupon_code'] ?? ''));
        $name = trim($_POST['coupon_name'] ?? '');
        $type = trim($_POST['discount_type'] ?? 'FLAT');
        $value = (float)($_POST['discount_value'] ?? 0);
        $min_amount = (float)($_POST['min_order_amount'] ?? 0);
        $max_discount = !empty($_POST['max_discount']) ? (float)$_POST['max_discount'] : null;
        $valid_from = $_POST['valid_from'] ?? null;
        $valid_until = $_POST['valid_until'] ?? null;
        $usage_limit = (int)($_POST['usage_limit'] ?? 0);

        if (empty($code) || empty($name)) Helpers::jsonResponse(false, 'Code and name are required');
        if ($value <= 0) Helpers::jsonResponse(false, 'Discount value must be positive');

        try {
            $exists = $db->query("SELECT id FROM coupons WHERE coupon_code = ? AND id != ? AND deleted_at IS NULL LIMIT 1", [$code, $id])->fetch();
            if ($exists) Helpers::jsonResponse(false, 'Coupon code already exists');

            if ($id > 0) {
                $db->query("UPDATE coupons SET coupon_code=?, coupon_name=?, discount_type=?, discount_value=?, min_order_amount=?, max_discount=?, valid_from=?, valid_until=?, usage_limit=? WHERE id=?",
                    [$code, $name, $type, $value, $min_amount, $max_discount, $valid_from ?: null, $valid_until ?: null, $usage_limit, $id]);
                Helpers::logActivity($db, 'coupons', "Updated coupon: $code", $id);
                Helpers::jsonResponse(true, 'Coupon updated', ['id' => $id]);
            } else {
                $newId = $db->insert("INSERT INTO coupons (coupon_code, coupon_name, discount_type, discount_value, min_order_amount, max_discount, valid_from, valid_until, usage_limit, created_by) VALUES (?,?,?,?,?,?,?,?,?,?)",
                    [$code, $name, $type, $value, $min_amount, $max_discount, $valid_from ?: null, $valid_until ?: null, $usage_limit, $_SESSION['user_id']]);
                Helpers::logActivity($db, 'coupons', "Created coupon: $code", (int)$newId);
                Helpers::jsonResponse(true, 'Coupon created', ['id' => $newId]);
            }
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Failed: ' . $e->getMessage());
        }
        break;

    case 'delete':
        $auth->requirePermission('Manage Coupons');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, 'Method not allowed');
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, 'CSRF verification failed');

        $id = (int)($_POST['id'] ?? 0);
        try {
            $db->query("UPDATE coupons SET status='INACTIVE', deleted_at=CURRENT_TIMESTAMP WHERE id=?", [$id]);
            Helpers::jsonResponse(true, 'Coupon deleted');
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Failed: ' . $e->getMessage());
        }
        break;

    case 'validate':
        $auth->requirePermission('Create Invoice');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, 'Method not allowed');
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, 'CSRF verification failed');

        $code = strtoupper(trim($_POST['coupon_code'] ?? ''));
        $orderAmount = (float)($_POST['order_amount'] ?? 0);

        if (empty($code)) Helpers::jsonResponse(false, 'Enter a coupon code');

        try {
            $coupon = $db->query("SELECT * FROM coupons WHERE coupon_code = ? AND status = 'ACTIVE' AND deleted_at IS NULL LIMIT 1", [$code])->fetch();
            if (!$coupon) Helpers::jsonResponse(false, 'Invalid coupon code');

            if (!empty($coupon['valid_from']) && date('Y-m-d') < $coupon['valid_from']) Helpers::jsonResponse(false, 'Coupon not yet active');
            if (!empty($coupon['valid_until']) && date('Y-m-d') > $coupon['valid_until']) Helpers::jsonResponse(false, 'Coupon has expired');
            if ($coupon['usage_limit'] > 0 && $coupon['used_count'] >= $coupon['usage_limit']) Helpers::jsonResponse(false, 'Coupon usage limit reached');
            if ($orderAmount < (float)$coupon['min_order_amount']) Helpers::jsonResponse(false, 'Minimum order amount is ₹' . number_format($coupon['min_order_amount'], 2));

            $discountAmount = 0;
            if ($coupon['discount_type'] === 'FLAT') {
                $discountAmount = (float)$coupon['discount_value'];
            } else {
                $discountAmount = $orderAmount * ((float)$coupon['discount_value'] / 100);
                if ($coupon['max_discount'] && $discountAmount > (float)$coupon['max_discount']) {
                    $discountAmount = (float)$coupon['max_discount'];
                }
            }

            Helpers::jsonResponse(true, 'Coupon applied', [
                'id' => $coupon['id'],
                'coupon_code' => $coupon['coupon_code'],
                'coupon_name' => $coupon['coupon_name'],
                'discount_type' => $coupon['discount_type'],
                'discount_amount' => $discountAmount
            ]);
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Failed: ' . $e->getMessage());
        }
        break;

    default:
        Helpers::jsonResponse(false, 'Unknown action: ' . $action);
}
