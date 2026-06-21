<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Quotations API Endpoint
 */

require_once __DIR__ . '/../vendor/autoload.php';
use App\Models\Auth;
use App\Models\Helpers;
use App\Models\Database;

require_once __DIR__ . '/../config/database.php';

$db = new Database();
$auth = new Auth($db);
$auth->requirePermission('Manage Quotations');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        try {
            $stmt = $db->query("
                SELECT q.*, c.customer_name
                FROM quotations q
                LEFT JOIN customers c ON q.customer_id = c.id
                WHERE q.deleted_at IS NULL
                ORDER BY q.created_at DESC
            ");
            Helpers::jsonResponse(true, "Quotations list", $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed to load quotations: " . $e->getMessage());
        }
        break;

    case 'get':
        $id = (int)($_GET['id'] ?? 0);
        try {
            $quotation = $db->query("
                SELECT q.*, c.customer_name, c.mobile as customer_mobile, c.gst_number as customer_gst, c.address as customer_address
                FROM quotations q
                LEFT JOIN customers c ON q.customer_id = c.id
                WHERE q.id = ? LIMIT 1
            ", [$id])->fetch();

            if ($quotation) {
                $items = $db->query("
                    SELECT qi.*, p.product_name, p.sku, p.selling_price, p.gst_percentage
                    FROM quotation_items qi
                    JOIN products p ON qi.product_id = p.id
                    WHERE qi.quotation_id = ? AND qi.deleted_at IS NULL
                ", [$id])->fetchAll();
                $quotation['items'] = $items;
                Helpers::jsonResponse(true, "Quotation found", $quotation);
            } else {
                Helpers::jsonResponse(false, "Quotation not found");
            }
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Error: " . $e->getMessage());
        }
        break;

    case 'save':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, "Invalid method");
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, "CSRF verification failed.");

        $quotation_id = (int)($_POST['quotation_id'] ?? 0);
        $customer_id = (int)($_POST['customer_id'] ?? 0);
        $quotation_date = trim($_POST['quotation_date'] ?? date('Y-m-d'));
        $valid_until = trim($_POST['valid_until'] ?? '');
        $notes = Helpers::sanitize(trim($_POST['notes'] ?? ''));
        $discount = (float)($_POST['discount'] ?? 0);
        $cart = json_decode($_POST['cart'] ?? '[]', true);

        if (empty($cart)) {
            Helpers::jsonResponse(false, "Quotation cart is empty.");
        }

        try {
            $db->transaction(function($t) use ($quotation_id, $customer_id, $quotation_date, $valid_until, $notes, $discount, $cart) {
                // 1. Calculate values
                $subtotal = 0.00;
                $gst_amount = 0.00;
                $validatedItems = [];

                foreach ($cart as $item) {
                    $pid = (int)$item['id'];
                    $qty = (float)$item['qty'];
                    $rate = (float)$item['rate'];
                    $gst_rate = (float)($item['gst_percentage'] ?? 0);
                    $item_discount = (float)($item['discount_percentage'] ?? 0);

                    $product = $t->query("SELECT * FROM products WHERE id = ? AND status = 'ACTIVE' LIMIT 1", [$pid])->fetch();
                    if (!$product) {
                        throw new \Exception("Product ID " . $pid . " is invalid or inactive.");
                    }

                    $row_base = $qty * $rate;
                    $row_disc = $row_base * ($item_discount / 100);
                    $row_after_disc = $row_base - $row_disc;
                    $row_tax = $row_after_disc * ($gst_rate / 100);
                    $row_total = $row_after_disc + $row_tax;

                    $subtotal += $row_after_disc;
                    $gst_amount += $row_tax;

                    $validatedItems[] = [
                        'product_id' => $pid,
                        'quantity' => $qty,
                        'rate' => $rate,
                        'gst' => $gst_rate,
                        'discount' => $item_discount,
                        'amount' => $row_total
                    ];
                }

                $grand_total = $subtotal + $gst_amount - $discount;
                if ($grand_total < 0) $grand_total = 0;

                if ($quotation_id > 0) {
                    // UPDATE existing quotation
                    $existing = $t->query("SELECT * FROM quotations WHERE id = ? LIMIT 1", [$quotation_id])->fetch();
                    if (!$existing) {
                        throw new \Exception("Quotation not found for update.");
                    }
                    if ($existing['status'] === 'CONVERTED') {
                        throw new \Exception("Cannot edit a converted quotation.");
                    }

                    $t->query("
                        UPDATE quotations SET customer_id = ?, quotation_date = ?, valid_until = ?, subtotal = ?, discount = ?, gst_amount = ?, grand_total = ?, notes = ?, updated_at = CURRENT_TIMESTAMP
                        WHERE id = ?
                    ", [
                        $customer_id > 0 ? $customer_id : null,
                        $quotation_date,
                        !empty($valid_until) ? $valid_until : null,
                        $subtotal,
                        $discount,
                        $gst_amount,
                        $grand_total,
                        $notes,
                        $quotation_id
                    ]);

                    // Remove old items
                    $t->query("UPDATE quotation_items SET deleted_at = CURRENT_TIMESTAMP WHERE quotation_id = ?", [$quotation_id]);

                    // Insert updated items
                    foreach ($validatedItems as $vi) {
                        $t->insert("
                            INSERT INTO quotation_items (quotation_id, product_id, quantity, rate, gst, discount, amount, created_by)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                        ", [$quotation_id, $vi['product_id'], $vi['quantity'], $vi['rate'], $vi['gst'], $vi['discount'], $vi['amount'], $_SESSION['user_id']]);
                    }

                    Helpers::logActivity($db, "quotations", "Updated quotation: " . $existing['quotation_no'], $quotation_id);
                    Helpers::jsonResponse(true, "Quotation updated successfully.", ['quotation_id' => $quotation_id, 'quotation_no' => $existing['quotation_no']]);

                } else {
                    // CREATE new quotation

                    // Generate sequential number QT-YYYY-00001
                    $year = date('Y');
                    $countQuery = $t->query("SELECT COUNT(*) as count FROM quotations WHERE quotation_no LIKE ?", ["QT-$year-%"])->fetch();
                    $seq = str_pad((int)($countQuery['count'] ?? 0) + 1, 5, '0', STR_PAD_LEFT);
                    $quotation_no = 'QT-' . $year . '-' . $seq;

                    $qId = $t->insert("
                        INSERT INTO quotations (quotation_no, customer_id, quotation_date, valid_until, subtotal, discount, gst_amount, grand_total, notes, status, created_by)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'DRAFT', ?)
                    ", [
                        $quotation_no,
                        $customer_id > 0 ? $customer_id : null,
                        $quotation_date,
                        !empty($valid_until) ? $valid_until : null,
                        $subtotal,
                        $discount,
                        $gst_amount,
                        $grand_total,
                        $notes,
                        $_SESSION['user_id']
                    ]);

                    // Save items
                    foreach ($validatedItems as $vi) {
                        $t->insert("
                            INSERT INTO quotation_items (quotation_id, product_id, quantity, rate, gst, discount, amount, created_by)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                        ", [$qId, $vi['product_id'], $vi['quantity'], $vi['rate'], $vi['gst'], $vi['discount'], $vi['amount'], $_SESSION['user_id']]);
                    }

                    Helpers::logActivity($db, "quotations", "Created quotation: $quotation_no (Total: $grand_total)", $qId);
                    Helpers::jsonResponse(true, "Quotation created successfully.", ['quotation_id' => $qId, 'quotation_no' => $quotation_no]);
                }
            });
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Quotation save failed: " . $e->getMessage());
        }
        break;

    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, "Invalid method");
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, "CSRF verification failed.");

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            Helpers::jsonResponse(false, "Invalid quotation ID.");
        }

        try {
            $quotation = $db->query("SELECT * FROM quotations WHERE id = ? LIMIT 1", [$id])->fetch();
            if (!$quotation) {
                Helpers::jsonResponse(false, "Quotation not found.");
            }
            if ($quotation['status'] === 'CONVERTED') {
                Helpers::jsonResponse(false, "Cannot delete a converted quotation.");
            }

            $db->query("UPDATE quotations SET status = 'INACTIVE', deleted_at = CURRENT_TIMESTAMP WHERE id = ?", [$id]);
            Helpers::logActivity($db, "quotations", "Deleted quotation: " . $quotation['quotation_no'], $id);
            Helpers::jsonResponse(true, "Quotation deleted successfully.");
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Delete failed: " . $e->getMessage());
        }
        break;

    case 'update_status':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, "Invalid method");
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, "CSRF verification failed.");

        $id = (int)($_POST['id'] ?? 0);
        $new_status = Helpers::sanitize(trim($_POST['status'] ?? ''));

        $allowed = ['DRAFT', 'SENT', 'ACCEPTED', 'REJECTED'];
        if (!in_array($new_status, $allowed)) {
            Helpers::jsonResponse(false, "Invalid status value.");
        }
        if ($id <= 0) {
            Helpers::jsonResponse(false, "Invalid quotation ID.");
        }

        try {
            $quotation = $db->query("SELECT * FROM quotations WHERE id = ? LIMIT 1", [$id])->fetch();
            if (!$quotation) {
                Helpers::jsonResponse(false, "Quotation not found.");
            }
            if ($quotation['status'] === 'CONVERTED') {
                Helpers::jsonResponse(false, "Cannot change status of a converted quotation.");
            }

            $db->query("UPDATE quotations SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?", [$new_status, $id]);
            Helpers::logActivity($db, "quotations", "Updated quotation status to $new_status: " . $quotation['quotation_no'], $id);
            Helpers::jsonResponse(true, "Status updated to $new_status.");
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Status update failed: " . $e->getMessage());
        }
        break;

    case 'convert_to_invoice':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, "Invalid method");
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, "CSRF verification failed.");

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            Helpers::jsonResponse(false, "Invalid quotation ID.");
        }

        try {
            $quotation = $db->query("
                SELECT q.*, c.customer_name, c.mobile as customer_mobile
                FROM quotations q
                LEFT JOIN customers c ON q.customer_id = c.id
                WHERE q.id = ? LIMIT 1
            ", [$id])->fetch();

            if (!$quotation) {
                Helpers::jsonResponse(false, "Quotation not found.");
            }
            if ($quotation['status'] === 'CONVERTED') {
                Helpers::jsonResponse(false, "This quotation has already been converted.");
            }

            // Fetch quotation items with product details
            $items = $db->query("
                SELECT qi.*, p.product_name, p.sku, p.selling_price, p.gst_percentage, p.current_stock
                FROM quotation_items qi
                JOIN products p ON qi.product_id = p.id
                WHERE qi.quotation_id = ? AND qi.deleted_at IS NULL
            ", [$id])->fetchAll();

            // Mark quotation as CONVERTED
            $db->query("UPDATE quotations SET status = 'CONVERTED', updated_at = CURRENT_TIMESTAMP WHERE id = ?", [$id]);
            Helpers::logActivity($db, "quotations", "Converted quotation to invoice: " . $quotation['quotation_no'], $id);

            // Return data for POS to load
            $quotation['items'] = $items;
            Helpers::jsonResponse(true, "Quotation marked as converted. Redirecting to POS.", $quotation);
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Conversion failed: " . $e->getMessage());
        }
        break;

    default:
        Helpers::jsonResponse(false, "Action not found: " . $action);
}
