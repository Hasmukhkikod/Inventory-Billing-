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
                SELECT p.*, COALESCE(p.order_status, 'PENDING') as order_status, s.supplier_name
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
                    SELECT pi.*, prod.product_name, prod.sku, prod.hsn_code, un.short_name as unit_name
                    FROM purchase_items pi
                    JOIN products prod ON pi.product_id = prod.id
                    LEFT JOIN units un ON prod.unit_id = un.id
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

        $purchase_id = (int)($_POST['id'] ?? 0);
        $supplier_id = (int)($_POST['supplier_id'] ?? 0);
        $purchase_date = trim($_POST['purchase_date'] ?? date('Y-m-d'));
        $payment_status = trim($_POST['payment_status'] ?? 'UNPAID');
        $order_status = trim($_POST['order_status'] ?? 'PENDING');
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
        if (!in_array($order_status, ['PENDING', 'COMPLETED'])) {
            Helpers::jsonResponse(false, "Invalid order status.");
        }

        try {
            $result = $db->transaction(function($t) use ($db, $purchase_id, $supplier_id, $purchase_date, $payment_status, $order_status, $discount, $cart) {
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

                if ($purchase_id > 0) {
                    // UPDATE existing purchase
                    $existing = $t->query("SELECT * FROM purchases WHERE id = ? AND status = 'ACTIVE' LIMIT 1", [$purchase_id])->fetch();
                    if (!$existing) {
                        throw new Exception("Purchase order not found for update.");
                    }
                    $purchase_no = $existing['purchase_no'];
                    $old_order_status = $existing['order_status'] ?? 'PENDING';

                    // If old status was COMPLETED, reverse old stock entries
                    if ($old_order_status === 'COMPLETED') {
                        $oldItems = $t->query("SELECT pi.*, p.current_stock FROM purchase_items pi JOIN products p ON pi.product_id = p.id WHERE pi.purchase_id = ? AND pi.deleted_at IS NULL", [$purchase_id])->fetchAll();
                        foreach ($oldItems as $oi) {
                            $stockNow = (float)$oi['current_stock'];
                            $reversed = $stockNow - (float)$oi['quantity'];
                            if ($reversed < 0) $reversed = 0;
                            $t->query("UPDATE products SET current_stock = ? WHERE id = ?", [$reversed, $oi['product_id']]);
                            $t->insert("INSERT INTO stock_transactions (product_id, transaction_type, reference_no, quantity, stock_before, stock_after, remarks, created_by) VALUES (?, 'Purchase Reversal', ?, ?, ?, ?, ?, ?)",
                                [$oi['product_id'], $purchase_no, -$oi['quantity'], $stockNow, $reversed, "PO edit reversal: $purchase_no", $_SESSION['user_id']]);
                        }
                    }

                    // Soft-delete old items
                    $t->query("UPDATE purchase_items SET deleted_at = CURRENT_TIMESTAMP WHERE purchase_id = ?", [$purchase_id]);

                    // Update purchase header
                    $t->query("UPDATE purchases SET supplier_id = ?, purchase_date = ?, subtotal = ?, discount = ?, gst_amount = ?, total_amount = ?, payment_status = ?, order_status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                        [$supplier_id, $purchase_date, $subtotal, $discount, $gst_amount, $total_amount, $payment_status, $order_status, $purchase_id]);

                    // Re-read current stock for new items (stock may have changed after reversal)
                    foreach ($validatedItems as &$item) {
                        $freshProduct = $t->query("SELECT current_stock FROM products WHERE id = ?", [$item['product_id']])->fetch();
                        $item['stock_before'] = (float)$freshProduct['current_stock'];
                    }
                    unset($item);

                    // Insert new items
                    foreach ($validatedItems as $item) {
                        $t->insert("INSERT INTO purchase_items (purchase_id, product_id, quantity, cost_price, gst, amount, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)",
                            [$purchase_id, $item['product_id'], $item['quantity'], $item['cost_price'], $item['gst'], $item['amount'], $_SESSION['user_id']]);

                        if ($order_status === 'COMPLETED') {
                            $newStock = $item['stock_before'] + $item['quantity'];
                            $t->query("UPDATE products SET current_stock = ?, cost_price = ? WHERE id = ?", [$newStock, $item['cost_price'], $item['product_id']]);
                            $t->insert("INSERT INTO stock_transactions (product_id, transaction_type, reference_no, quantity, stock_before, stock_after, remarks, created_by) VALUES (?, 'Purchase', ?, ?, ?, ?, ?, ?)",
                                [$item['product_id'], $purchase_no, $item['quantity'], $item['stock_before'], $newStock, "PO updated: $purchase_no", $_SESSION['user_id']]);
                        }
                    }

                    Helpers::logActivity($db, "purchases", "Updated purchase: $purchase_no (Total: $total_amount, Status: $order_status)", $purchase_id);
                    return ['purchase_id' => $purchase_id, 'purchase_no' => $purchase_no, 'action' => 'updated'];

                } else {
                    // CREATE new purchase
                    $prefQ = $t->query("SELECT purchase_prefix, purchase_start, purchase_end FROM company_settings WHERE id = 1 LIMIT 1")->fetch();
                    $prefix = $prefQ['purchase_prefix'] ?? 'PO-';
                    $startNum = (int)($prefQ['purchase_start'] ?? 1);
                    $endNum = (int)($prefQ['purchase_end'] ?? 99999);
                    $year = date('Y');
                    $countQuery = $t->query("SELECT COUNT(*) as count FROM purchases WHERE purchase_date LIKE ?", ["$year-%"])->fetch();
                    $nextNum = $startNum + (int)($countQuery['count'] ?? 0);
                    if ($nextNum > $endNum) {
                        throw new Exception("Purchase number limit reached ($endNum). Update range in Settings.");
                    }
                    $seq = str_pad($nextNum, 5, '0', STR_PAD_LEFT);
                    $purchase_no = $prefix . $year . '-' . $seq;

                    $newId = $t->insert("
                        INSERT INTO purchases (purchase_no, supplier_id, purchase_date, subtotal, discount, gst_amount, total_amount, payment_status, order_status, created_by)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ", [$purchase_no, $supplier_id, $purchase_date, $subtotal, $discount, $gst_amount, $total_amount, $payment_status, $order_status, $_SESSION['user_id']]);

                    foreach ($validatedItems as $item) {
                        $t->insert("INSERT INTO purchase_items (purchase_id, product_id, quantity, cost_price, gst, amount, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)",
                            [$newId, $item['product_id'], $item['quantity'], $item['cost_price'], $item['gst'], $item['amount'], $_SESSION['user_id']]);

                        if ($order_status === 'COMPLETED') {
                            $newStock = $item['stock_before'] + $item['quantity'];
                            $t->query("UPDATE products SET current_stock = ?, cost_price = ? WHERE id = ?", [$newStock, $item['cost_price'], $item['product_id']]);
                            $t->insert("INSERT INTO stock_transactions (product_id, transaction_type, reference_no, quantity, stock_before, stock_after, remarks, created_by) VALUES (?, 'Purchase', ?, ?, ?, ?, ?, ?)",
                                [$item['product_id'], $purchase_no, $item['quantity'], $item['stock_before'], $newStock, "Purchased under PO: $purchase_no", $_SESSION['user_id']]);
                        }
                    }

                    Helpers::logActivity($db, "purchases", "Created purchase entry: $purchase_no (Total: $total_amount, Status: $order_status)", $newId);
                    return ['purchase_id' => $newId, 'purchase_no' => $purchase_no, 'action' => 'created'];
                }
            });

            $msg = ($result['action'] ?? '') === 'updated' ? "Purchase order updated successfully." : "Purchase entry saved successfully.";
            Helpers::jsonResponse(true, $msg, $result);
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Purchase commit failed: " . $e->getMessage());
        }
        break;

    case 'update_status':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, 'Method not allowed');
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, 'CSRF verification failed');

        $id = (int)($_POST['id'] ?? 0);
        $new_status = trim($_POST['order_status'] ?? '');

        if ($id <= 0) Helpers::jsonResponse(false, 'Invalid purchase ID');
        if (!in_array($new_status, ['PENDING', 'COMPLETED'])) Helpers::jsonResponse(false, 'Invalid order status');

        try {
            $purchase = $db->query("SELECT * FROM purchases WHERE id = ? AND status = 'ACTIVE' AND deleted_at IS NULL LIMIT 1", [$id])->fetch();
            if (!$purchase) Helpers::jsonResponse(false, 'Purchase not found');

            $old_status = $purchase['order_status'] ?? 'PENDING';
            if ($old_status === $new_status) Helpers::jsonResponse(true, 'Status is already ' . $new_status);

            $db->transaction(function($t) use ($db, $id, $new_status, $old_status, $purchase) {
                $t->query("UPDATE purchases SET order_status = ? WHERE id = ?", [$new_status, $id]);

                $items = $t->query("SELECT pi.*, p.current_stock FROM purchase_items pi JOIN products p ON pi.product_id = p.id WHERE pi.purchase_id = ? AND pi.deleted_at IS NULL", [$id])->fetchAll();

                if ($new_status === 'COMPLETED' && $old_status === 'PENDING') {
                    foreach ($items as $item) {
                        $stockBefore = (float)$item['current_stock'];
                        $newStock = $stockBefore + (float)$item['quantity'];
                        $t->query("UPDATE products SET current_stock = ?, cost_price = ? WHERE id = ?", [$newStock, $item['cost_price'], $item['product_id']]);
                        $t->insert("
                            INSERT INTO stock_transactions (product_id, transaction_type, reference_no, quantity, stock_before, stock_after, remarks, created_by)
                            VALUES (?, 'Purchase', ?, ?, ?, ?, ?, ?)
                        ", [$item['product_id'], $purchase['purchase_no'], $item['quantity'], $stockBefore, $newStock, "PO completed: " . $purchase['purchase_no'], $_SESSION['user_id']]);
                    }
                } elseif ($new_status === 'PENDING' && $old_status === 'COMPLETED') {
                    foreach ($items as $item) {
                        $stockBefore = (float)$item['current_stock'];
                        $newStock = $stockBefore - (float)$item['quantity'];
                        if ($newStock < 0) $newStock = 0;
                        $t->query("UPDATE products SET current_stock = ? WHERE id = ?", [$newStock, $item['product_id']]);
                        $t->insert("
                            INSERT INTO stock_transactions (product_id, transaction_type, reference_no, quantity, stock_before, stock_after, remarks, created_by)
                            VALUES (?, 'Purchase Reversal', ?, ?, ?, ?, ?, ?)
                        ", [$item['product_id'], $purchase['purchase_no'], -$item['quantity'], $stockBefore, $newStock, "PO reverted to pending: " . $purchase['purchase_no'], $_SESSION['user_id']]);
                    }
                }

                Helpers::logActivity($db, "purchases", "Updated PO status: " . $purchase['purchase_no'] . " from $old_status to $new_status", $id);
            });

            Helpers::jsonResponse(true, "Order status updated to $new_status successfully.");
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Status update failed: " . $e->getMessage());
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
                $db->query("UPDATE purchases SET status = 'INACTIVE', deleted_at = CURRENT_TIMESTAMP WHERE id IN ($placeholders)", $ids);
                Helpers::logActivity($db, 'purchases', 'Bulk deleted ' . count($ids) . ' records');
                Helpers::jsonResponse(true, count($ids) . ' records deleted successfully');
            }
            Helpers::jsonResponse(false, 'Unknown bulk action');
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Bulk action failed: ' . $e->getMessage());
        }
        break;

    default:
        Helpers::jsonResponse(false, "Action not found: " . $action);
}
