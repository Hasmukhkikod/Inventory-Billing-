<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Return Invoices & Orders API
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
    case 'list_sales':
        try {
            $stmt = $db->query("
                SELECT sr.*, i.invoice_no, c.customer_name 
                FROM sales_returns sr
                LEFT JOIN invoices i ON sr.invoice_id = i.id
                LEFT JOIN customers c ON sr.customer_id = c.id
                WHERE sr.status = 'ACTIVE' AND sr.deleted_at IS NULL
                ORDER BY sr.created_at DESC
            ");
            Helpers::jsonResponse(true, "Sales returns list", $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed to load: " . $e->getMessage());
        }
        break;

    case 'list_purchase':
        try {
            $stmt = $db->query("
                SELECT pr.*, pur.purchase_no, s.supplier_name 
                FROM purchase_returns pr
                LEFT JOIN purchases pur ON pr.purchase_id = pur.id
                LEFT JOIN suppliers s ON pr.supplier_id = s.id
                WHERE pr.status = 'ACTIVE' AND pr.deleted_at IS NULL
                ORDER BY pr.created_at DESC
            ");
            Helpers::jsonResponse(true, "Purchase returns list", $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed to load: " . $e->getMessage());
        }
        break;

    case 'get_invoice_items':
        $invoice_id = (int)($_GET['invoice_id'] ?? 0);
        try {
            $items = $db->query("
                SELECT ii.*, p.product_name, p.sku, p.current_stock, u.short_name as unit_name
                FROM invoice_items ii
                JOIN products p ON ii.product_id = p.id
                LEFT JOIN units u ON p.unit_id = u.id
                WHERE ii.invoice_id = ?
            ", [$invoice_id])->fetchAll();
            Helpers::jsonResponse(true, "Invoice items loaded", $items);
        } catch (Exception $e) {
            Helpers::jsonResponse(false, $e->getMessage());
        }
        break;

    case 'get_purchase_items':
        $purchase_id = (int)($_GET['purchase_id'] ?? 0);
        try {
            $items = $db->query("
                SELECT pi.*, p.product_name, p.sku, p.current_stock, u.short_name as unit_name
                FROM purchase_items pi
                JOIN products p ON pi.product_id = p.id
                LEFT JOIN units u ON p.unit_id = u.id
                WHERE pi.purchase_id = ?
            ", [$purchase_id])->fetchAll();
            Helpers::jsonResponse(true, "Purchase items loaded", $items);
        } catch (Exception $e) {
            Helpers::jsonResponse(false, $e->getMessage());
        }
        break;

    case 'save_sales':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, "Invalid method");
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, "CSRF verification failed.");

        $invoice_id = (int)($_POST['invoice_id'] ?? 0);
        $return_date = trim($_POST['return_date'] ?? date('Y-m-d'));
        $remarks = trim($_POST['remarks'] ?? '');
        $cart = json_decode($_POST['cart'] ?? '[]', true);

        if ($invoice_id <= 0) Helpers::jsonResponse(false, "Please select a valid invoice.");
        if (empty($cart)) Helpers::jsonResponse(false, "No items selected to return.");

        try {
            $result = $db->transaction(function($t) use ($invoice_id, $return_date, $remarks, $cart) {
                // Fetch Invoice metadata
                $invoice = $t->query("SELECT * FROM invoices WHERE id = ? LIMIT 1", [$invoice_id])->fetch();
                if (!$invoice) throw new Exception("Invoice not found.");
                
                $customer_id = $invoice['customer_id'];
                
                // Calculate values
                $total_amount = 0.00;
                $validatedItems = [];

                foreach ($cart as $item) {
                    $pid = (int)$item['product_id'];
                    $qty = (float)$item['qty'];
                    $rate = (float)$item['rate'];
                    
                    // Verify that the quantity returned is not more than quantity purchased
                    $purchasedItem = $t->query("SELECT quantity FROM invoice_items WHERE invoice_id = ? AND product_id = ? LIMIT 1", [$invoice_id, $pid])->fetch();
                    if (!$purchasedItem) {
                        throw new Exception("Product ID $pid was not purchased under this invoice.");
                    }
                    if ($qty > (float)$purchasedItem['quantity']) {
                        throw new Exception("Cannot return more than purchased. Purchased: " . (float)$purchasedItem['quantity'] . ", Return Request: $qty");
                    }

                    $amount = $qty * $rate;
                    $total_amount += $amount;

                    $product = $t->query("SELECT current_stock, product_name FROM products WHERE id = ? LIMIT 1", [$pid])->fetch();

                    $validatedItems[] = [
                        'product_id' => $pid,
                        'product_name' => $product['product_name'],
                        'quantity' => $qty,
                        'rate' => $rate,
                        'amount' => $amount,
                        'stock_before' => (float)$product['current_stock']
                    ];
                }

                // Generate Sales Return ID SR-YYYY-00001
                $year = date('Y');
                $countQuery = $t->query("SELECT COUNT(*) as count FROM sales_returns WHERE return_date LIKE ?", ["$year-%"])->fetch();
                $seq = str_pad((int)($countQuery['count'] ?? 0) + 1, 5, '0', STR_PAD_LEFT);
                $return_no = 'SR-' . $year . '-' . $seq;

                // Insert sales_returns
                $returnId = $t->insert("
                    INSERT INTO sales_returns (invoice_id, customer_id, return_no, return_date, total_amount, remarks, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ", [$invoice_id, $customer_id, $return_no, $return_date, $total_amount, $remarks, $_SESSION['user_id']]);

                // Insert sales_return_items and increase inventory stock
                foreach ($validatedItems as $item) {
                    $t->insert("
                        INSERT INTO sales_return_items (sales_return_id, product_id, quantity, rate, amount, created_by)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ", [$returnId, $item['product_id'], $item['quantity'], $item['rate'], $item['amount'], $_SESSION['user_id']]);

                    $newStock = $item['stock_before'] + $item['quantity'];
                    $t->query("UPDATE products SET current_stock = ? WHERE id = ?", [$newStock, $item['product_id']]);

                    // Log stock transaction
                    $t->insert("
                        INSERT INTO stock_transactions (product_id, transaction_type, reference_no, quantity, stock_before, stock_after, remarks, created_by)
                        VALUES (?, 'Sales Return', ?, ?, ?, ?, ?, ?)
                    ", [$item['product_id'], $return_no, $item['quantity'], $item['stock_before'], $newStock, "Returned from customer under SR: $return_no", $_SESSION['user_id']]);
                }

                Helpers::logActivity($t, "returns", "Logged sales return: $return_no (Total Credit Note: $total_amount)", $returnId);
                return ['return_id' => $returnId, 'return_no' => $return_no];
            });
            Helpers::jsonResponse(true, "Sales Return processed successfully.", $result);
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Transaction failed: " . $e->getMessage());
        }
        break;

    case 'save_purchase':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, "Invalid method");
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, "CSRF verification failed.");

        $purchase_id = (int)($_POST['purchase_id'] ?? 0);
        $return_date = trim($_POST['return_date'] ?? date('Y-m-d'));
        $remarks = trim($_POST['remarks'] ?? '');
        $cart = json_decode($_POST['cart'] ?? '[]', true);

        if ($purchase_id <= 0) Helpers::jsonResponse(false, "Please select a valid purchase order.");
        if (empty($cart)) Helpers::jsonResponse(false, "No items selected to return.");

        try {
            $result = $db->transaction(function($t) use ($purchase_id, $return_date, $remarks, $cart) {
                // Fetch Purchase order metadata
                $purchase = $t->query("SELECT * FROM purchases WHERE id = ? LIMIT 1", [$purchase_id])->fetch();
                if (!$purchase) throw new Exception("Purchase Order not found.");
                
                $supplier_id = $purchase['supplier_id'];
                
                // Calculate values
                $total_amount = 0.00;
                $validatedItems = [];

                foreach ($cart as $item) {
                    $pid = (int)$item['product_id'];
                    $qty = (float)$item['qty'];
                    $cost = (float)$item['cost_price'];
                    
                    // Verify that the quantity returned is not more than quantity purchased
                    $purchasedItem = $t->query("SELECT quantity FROM purchase_items WHERE purchase_id = ? AND product_id = ? LIMIT 1", [$purchase_id, $pid])->fetch();
                    if (!$purchasedItem) {
                        throw new Exception("Product ID $pid was not purchased under this purchase order.");
                    }
                    if ($qty > (float)$purchasedItem['quantity']) {
                        throw new Exception("Cannot return more than purchased. Purchased: " . (float)$purchasedItem['quantity'] . ", Return Request: $qty");
                    }

                    // Enforce Zero-Negative Stock checks: cannot return more than currently in stock!
                    $product = $t->query("SELECT current_stock, product_name FROM products WHERE id = ? LIMIT 1", [$pid])->fetch();
                    if ((float)$product['current_stock'] < $qty) {
                        throw new Exception("Cannot return " . $product['product_name'] . ". Available stock in inventory (" . (float)$product['current_stock'] . ") is less than return quantity ($qty).");
                    }

                    $amount = $qty * $cost;
                    $total_amount += $amount;

                    $validatedItems[] = [
                        'product_id' => $pid,
                        'product_name' => $product['product_name'],
                        'quantity' => $qty,
                        'cost_price' => $cost,
                        'amount' => $amount,
                        'stock_before' => (float)$product['current_stock']
                    ];
                }

                // Generate Purchase Return ID PR-YYYY-00001
                $year = date('Y');
                $countQuery = $t->query("SELECT COUNT(*) as count FROM purchase_returns WHERE return_date LIKE ?", ["$year-%"])->fetch();
                $seq = str_pad((int)($countQuery['count'] ?? 0) + 1, 5, '0', STR_PAD_LEFT);
                $return_no = 'PR-' . $year . '-' . $seq;

                // Insert purchase_returns
                $returnId = $t->insert("
                    INSERT INTO purchase_returns (purchase_id, supplier_id, return_no, return_date, total_amount, remarks, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ", [$purchase_id, $supplier_id, $return_no, $return_date, $total_amount, $remarks, $_SESSION['user_id']]);

                // Insert purchase_return_items and decrease inventory stock
                foreach ($validatedItems as $item) {
                    $t->insert("
                        INSERT INTO purchase_return_items (purchase_return_id, product_id, quantity, cost_price, amount, created_by)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ", [$returnId, $item['product_id'], $item['quantity'], $item['cost_price'], $item['amount'], $_SESSION['user_id']]);

                    $newStock = $item['stock_before'] - $item['quantity'];
                    $t->query("UPDATE products SET current_stock = ? WHERE id = ?", [$newStock, $item['product_id']]);

                    // Log stock transaction
                    $t->insert("
                        INSERT INTO stock_transactions (product_id, transaction_type, reference_no, quantity, stock_before, stock_after, remarks, created_by)
                        VALUES (?, 'Purchase Return', ?, ?, ?, ?, ?, ?)
                    ", [$item['product_id'], $return_no, -$item['quantity'], $item['stock_before'], $newStock, "Returned to supplier under PR: $return_no", $_SESSION['user_id']]);
                }

                Helpers::logActivity($t, "returns", "Logged purchase return: $return_no (Total Debit Note: $total_amount)", $returnId);
                return ['return_id' => $returnId, 'return_no' => $return_no];
            });
            Helpers::jsonResponse(true, "Purchase Return processed successfully.", $result);
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Transaction failed: " . $e->getMessage());
        }
        break;

    default:
        Helpers::jsonResponse(false, "Action not found: " . $action);
}
