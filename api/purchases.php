<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Purchases API Endpoint
 */

require_once __DIR__ . '/../vendor/autoload.php';
use App\Models\Auth;
use App\Models\Helpers;
use App\Models\Database;

require_once __DIR__ . '/../config/database.php';

$db = new Database();
$auth = new Auth($db);
$auth->requirePermission('Manage Inventory');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        try {
            $stmt = $db->query("
                SELECT p.*, s.supplier_name 
                FROM purchases p
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                WHERE p.status = 'ACTIVE' AND p.deleted_at IS NULL
                ORDER BY p.created_at DESC
            ");
            Helpers::jsonResponse(true, "Purchases list", $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed to load purchases: " . $e->getMessage());
        }
        break;

    case 'get':
        $id = (int)($_GET['id'] ?? 0);
        try {
            $purchase = $db->query("
                SELECT p.*, s.supplier_name 
                FROM purchases p
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                WHERE p.id = ? LIMIT 1
            ", [$id])->fetch();
            
            if ($purchase) {
                $items = $db->query("
                    SELECT pi.*, prod.product_name, prod.sku 
                    FROM purchase_items pi
                    JOIN products prod ON pi.product_id = prod.id
                    WHERE pi.purchase_id = ? AND pi.deleted_at IS NULL
                ", [$id])->fetchAll();
                $purchase['items'] = $items;
                Helpers::jsonResponse(true, "Purchase found", $purchase);
            } else {
                Helpers::jsonResponse(false, "Purchase not found");
            }
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Error: " . $e->getMessage());
        }
        break;

    case 'save':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, "Invalid method");
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, "CSRF verification failed.");

        $supplier_id = (int)($_POST['supplier_id'] ?? 0);
        $purchase_date = trim($_POST['purchase_date'] ?? date('Y-m-d'));
        $payment_status = trim($_POST['payment_status'] ?? 'UNPAID');
        $discount = (float)($_POST['discount'] ?? 0);
        $cart = json_decode($_POST['cart'] ?? '[]', true);

        if ($supplier_id <= 0) {
            Helpers::jsonResponse(false, "Please select a valid supplier.");
        }
        if (empty($cart)) {
            Helpers::jsonResponse(false, "Purchase cart is empty.");
        }
        if ($discount < 0) {
            Helpers::jsonResponse(false, "Discount cannot be negative.");
        }

        try {
            $db->transaction(function($t) use ($supplier_id, $purchase_date, $payment_status, $discount, $cart) {
                // 1. Calculate values
                $subtotal = 0.00;
                $gst_amount = 0.00;
                $validatedItems = [];

                foreach ($cart as $item) {
                    $pid = (int)$item['id'];
                    $qty = (float)$item['qty'];
                    $cost = (float)$item['cost_price'];
                    $gst_rate = (float)$item['gst_percentage'];

                    if ($qty <= 0) {
                        throw new Exception("Quantity must be greater than zero.");
                    }
                    if ($cost < 0) {
                        throw new Exception("Cost price cannot be negative.");
                    }
                    if ($gst_rate < 0) {
                        throw new Exception("GST percentage cannot be negative.");
                    }

                    $product = $t->query("SELECT * FROM products WHERE id = ? AND status = 'ACTIVE' LIMIT 1", [$pid])->fetch();
                    if (!$product) {
                        throw new Exception("Product ID " . $pid . " is invalid.");
                    }

                    $row_base = $qty * $cost;
                    $row_tax = $row_base * ($gst_rate / 100);
                    $row_total = $row_base + $row_tax;

                    $subtotal += $row_base;
                    $gst_amount += $row_tax;

                    $validatedItems[] = [
                        'product_id' => $pid,
                        'quantity' => $qty,
                        'cost_price' => $cost,
                        'gst' => $gst_rate,
                        'amount' => $row_total,
                        'stock_before' => (float)$product['current_stock']
                    ];
                }

                $total_amount = $subtotal + $gst_amount - $discount;

                // 2. Generate Purchase Number PO-YYYY-00001
                $year = date('Y');
                $countQuery = $t->query("SELECT COUNT(*) as count FROM purchases WHERE purchase_date LIKE ?", ["$year-%"])->fetch();
                $seq = str_pad((int)($countQuery['count'] ?? 0) + 1, 5, '0', STR_PAD_LEFT);
                $purchase_no = 'PO-' . $year . '-' . $seq;

                // 3. Save Purchase
                $purchaseId = $t->insert("
                    INSERT INTO purchases (purchase_no, supplier_id, purchase_date, subtotal, discount, gst_amount, total_amount, payment_status, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ", [$purchase_no, $supplier_id, $purchase_date, $subtotal, $discount, $gst_amount, $total_amount, $payment_status, $_SESSION['user_id']]);

                // 4. Save Items and Update Stock
                foreach ($validatedItems as $item) {
                    $t->insert("
                        INSERT INTO purchase_items (purchase_id, product_id, quantity, cost_price, gst, amount, created_by)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ", [$purchaseId, $item['product_id'], $item['quantity'], $item['cost_price'], $item['gst'], $item['amount'], $_SESSION['user_id']]);

                    $newStock = $item['stock_before'] + $item['quantity'];

                    // Update product stock and cost price
                    $t->query("UPDATE products SET current_stock = ?, cost_price = ? WHERE id = ?", [$newStock, $item['cost_price'], $item['product_id']]);

                    // Log stock transaction
                    $t->insert("
                        INSERT INTO stock_transactions (product_id, transaction_type, reference_no, quantity, stock_before, stock_after, remarks, created_by)
                        VALUES (?, 'Purchase', ?, ?, ?, ?, ?, ?)
                    ", [$item['product_id'], $purchase_no, $item['quantity'], $item['stock_before'], $newStock, "Purchased under PO: $purchase_no", $_SESSION['user_id']]);
                }

                // 5. Update Supplier outstanding payable if unpaid or partial
                // Supplier outstanding payable is calculated dynamically via purchases vs supplier_payments,
                // but let's log the transaction activity
                Helpers::logActivity($db, "purchases", "Created purchase entry: $purchase_no (Total: $total_amount)", $purchaseId);
                
                Helpers::jsonResponse(true, "Purchase entry saved successfully.", ['purchase_id' => $purchaseId, 'purchase_no' => $purchase_no]);
            });
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Purchase commit failed: " . $e->getMessage());
        }
        break;

    default:
        Helpers::jsonResponse(false, "Action not found: " . $action);
}
