<?php
/**
 * IIMS v2.0 - POS Billing & Invoice API
 * Enhanced: Split Payment, CGST/SGST/IGST, Coupons, Loyalty, Due Date
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
    case 'search_product':
        $q = trim($_GET['q'] ?? '');
        if (empty($q)) Helpers::jsonResponse(true, 'Empty query', []);
        try {
            if ($q === '*') {
                $stmt = $db->query("
                    SELECT p.id, p.product_name, p.sku, p.barcode, p.hsn_code, p.selling_price, p.gst_percentage, p.current_stock,
                           p.secondary_unit_id, p.conversion_factor,
                           u.id as unit_id, u.short_name as unit_name,
                           su.short_name as secondary_unit_name
                    FROM products p
                    LEFT JOIN units u ON p.unit_id = u.id
                    LEFT JOIN units su ON p.secondary_unit_id = su.id
                    WHERE p.status = 'ACTIVE' AND p.deleted_at IS NULL
                    ORDER BY p.product_name ASC
                    LIMIT 50
                ");
            } else {
                $stmt = $db->query("
                    SELECT p.id, p.product_name, p.sku, p.barcode, p.hsn_code, p.selling_price, p.gst_percentage, p.current_stock,
                           p.secondary_unit_id, p.conversion_factor,
                           u.id as unit_id, u.short_name as unit_name,
                           su.short_name as secondary_unit_name
                    FROM products p
                    LEFT JOIN units u ON p.unit_id = u.id
                    LEFT JOIN units su ON p.secondary_unit_id = su.id
                    WHERE p.status = 'ACTIVE' AND p.deleted_at IS NULL AND (p.barcode = ? OR p.product_name LIKE ? OR p.sku LIKE ?)
                    LIMIT 20
                ", [$q, "%$q%", "%$q%"]);
            }
            Helpers::jsonResponse(true, 'Products found', $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Search failed: ' . $e->getMessage());
        }
        break;

    case 'get_customers':
        try {
            $stmt = $db->query("SELECT id, customer_name, mobile, gst_number, state, loyalty_points FROM customers WHERE status = 'ACTIVE' AND deleted_at IS NULL ORDER BY customer_name ASC");
            Helpers::jsonResponse(true, 'Customers list', $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Failed: ' . $e->getMessage());
        }
        break;

    case 'create_invoice':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, 'Method not allowed');
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, 'CSRF verification failed');

        $customer_id = !empty($_POST['customer_id']) ? (int)$_POST['customer_id'] : null;
        $invoice_type = trim($_POST['invoice_type'] ?? 'RETAIL');
        $discount_amount = (float)($_POST['discount_amount'] ?? 0);
        $coupon_id = !empty($_POST['coupon_id']) ? (int)$_POST['coupon_id'] : null;
        $coupon_discount = (float)($_POST['coupon_discount'] ?? 0);
        $loyalty_points_redeemed = (int)($_POST['loyalty_points_redeemed'] ?? 0);
        $loyalty_discount = (float)($_POST['loyalty_discount'] ?? 0);
        $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
        $notes = trim($_POST['notes'] ?? '');
        $is_igst = (int)($_POST['is_igst'] ?? 0);

        $payments = json_decode($_POST['payments'] ?? '[]', true);
        $cart = json_decode($_POST['cart'] ?? '[]', true);

        if (empty($cart)) Helpers::jsonResponse(false, 'Cart is empty');
        if (empty($payments)) Helpers::jsonResponse(false, 'Select a payment method');
        if ($discount_amount < 0) Helpers::jsonResponse(false, 'Discount amount cannot be negative');

        // Validate payment amounts are non-negative
        foreach ($payments as $p) {
            if ((float)($p['amount'] ?? 0) < 0) {
                Helpers::jsonResponse(false, 'Payment amount cannot be negative');
            }
        }

        try {
            $result = $db->transaction(function($t) use ($customer_id, $invoice_type, $discount_amount, $coupon_id, $coupon_discount, $loyalty_points_redeemed, $loyalty_discount, $due_date, $notes, $is_igst, $payments, $cart) {
                $sub_total = 0.00;
                $total_tax = 0.00;
                $total_cgst = 0.00;
                $total_sgst = 0.00;
                $total_igst = 0.00;
                $validatedItems = [];

                foreach ($cart as $item) {
                    $pid = (int)$item['id'];
                    $qty = (float)$item['qty'];
                    $disc = (float)($item['discount'] ?? 0);
                    $is_secondary = (int)($item['is_secondary_unit'] ?? 0);
                    $billing_unit_id = (int)($item['billing_unit_id'] ?? 0);
                    $billing_unit_name = trim($item['billing_unit_name'] ?? '');
                    $conv_factor = (float)($item['conversion_factor'] ?? 0);

                    if ($qty <= 0) throw new Exception("Quantity must be greater than zero.");
                    if ($disc < 0 || $disc > 100) throw new Exception("Discount percentage must be between 0 and 100.");

                    $product = $t->query("SELECT * FROM products WHERE id = ? AND status = 'ACTIVE' AND deleted_at IS NULL LIMIT 1", [$pid])->fetch();
                    if (!$product) throw new Exception("Product ID $pid not found");

                    $stock = (float)$product['current_stock'];
                    $primary_qty = $qty;
                    if ($is_secondary && $conv_factor > 0) {
                        $primary_qty = $qty / $conv_factor;
                    }
                    if ($stock < $primary_qty) throw new Exception("Insufficient stock for: " . $product['product_name'] . ". Available: $stock");

                    $rate = (float)($item['rate'] ?? $product['selling_price']);
                    $gst_rate = (float)$product['gst_percentage'];
                    $hsn = $product['hsn_code'] ?? '';

                    $row_base = $qty * $rate;
                    $row_disc = ($disc > 0) ? ($row_base * ($disc / 100)) : 0;
                    $row_taxable = $row_base - $row_disc;
                    $row_tax = $row_taxable * ($gst_rate / 100);
                    $row_total = $row_taxable + $row_tax;

                    $row_cgst = 0; $row_sgst = 0; $row_igst = 0;
                    if ($is_igst) {
                        $row_igst = $row_tax;
                    } else {
                        $row_cgst = $row_tax / 2;
                        $row_sgst = $row_tax / 2;
                    }

                    $sub_total += $row_taxable;
                    $total_tax += $row_tax;
                    $total_cgst += $row_cgst;
                    $total_sgst += $row_sgst;
                    $total_igst += $row_igst;

                    $validatedItems[] = [
                        'product_id' => $pid, 'product_name' => $product['product_name'],
                        'hsn_code' => $hsn, 'quantity' => $qty, 'primary_qty' => $primary_qty,
                        'billing_unit_id' => $billing_unit_id, 'billing_unit_name' => $billing_unit_name,
                        'rate' => $rate,
                        'tax_rate' => $gst_rate, 'cgst' => $row_cgst > 0 ? $gst_rate / 2 : 0,
                        'sgst' => $row_sgst > 0 ? $gst_rate / 2 : 0, 'igst' => $is_igst ? $gst_rate : 0,
                        'discount_pct' => $disc, 'discount_amount' => $row_disc,
                        'total_amount' => $row_total, 'stock_before' => $stock
                    ];
                }

                $grand_total = $sub_total + $total_tax - $discount_amount - $coupon_discount - $loyalty_discount;
                $rounded_total = round($grand_total);
                $round_off = $rounded_total - $grand_total;

                $total_paid = 0;
                $primary_method = 'CASH';
                $has_credit = false;
                foreach ($payments as $p) {
                    $total_paid += (float)$p['amount'];
                    if ($p['method'] === 'CREDIT') $has_credit = true;
                }
                if (count($payments) === 1) $primary_method = $payments[0]['method'];
                else $primary_method = 'SPLIT';

                if ($has_credit) $total_paid = 0;
                $actual_paid = min($total_paid, $rounded_total);
                $balance_amount = max(0, $rounded_total - $total_paid);

                // Credit limit check
                if (($has_credit || $balance_amount > 0) && $customer_id) {
                    $cust = $t->query("
                        SELECT c.customer_name, c.credit_limit,
                          (c.opening_balance + IFNULL((SELECT SUM(grand_total) FROM invoices WHERE customer_id = c.id AND status != 'INACTIVE'), 0) - IFNULL((SELECT SUM(amount) FROM customer_payments WHERE customer_id = c.id AND status = 'ACTIVE'), 0)) as credit_balance
                        FROM customers c WHERE c.id = ? LIMIT 1
                    ", [$customer_id])->fetch();
                    if ($cust && (float)$cust['credit_limit'] > 0) {
                        $outstanding = (float)$cust['credit_balance'];
                        if ($outstanding + $balance_amount > (float)$cust['credit_limit']) {
                            throw new Exception("Credit limit exceeded for " . $cust['customer_name']);
                        }
                    }
                }

                $pay_status = 'PAID';
                if ($balance_amount > 0) $pay_status = ($actual_paid > 0) ? 'PARTIAL' : 'UNPAID';

                // Generate invoice number with range check
                $prefQ = $t->query("SELECT invoice_prefix, invoice_start, invoice_end FROM company_settings WHERE id = 1 LIMIT 1")->fetch();
                $prefix = $prefQ['invoice_prefix'] ?? 'INV-';
                $startNum = (int)($prefQ['invoice_start'] ?? 1);
                $endNum = (int)($prefQ['invoice_end'] ?? 99999);
                $year = date('Y');
                $countQ = $t->query("SELECT COUNT(*) as count FROM invoices WHERE invoice_date LIKE ?", ["$year-%"])->fetch();
                $nextNum = $startNum + (int)($countQ['count'] ?? 0);
                if ($nextNum > $endNum) {
                    throw new Exception("Invoice number limit reached ($endNum). Please update the range in Settings → Invoice & Tax.");
                }
                $seq = str_pad($nextNum, 5, '0', STR_PAD_LEFT);
                $invoice_number = $prefix . $year . '-' . $seq;

                // Loyalty points earned
                $loyaltySettings = $t->query("SELECT loyalty_enabled, loyalty_points_per_100 FROM company_settings WHERE id = 1 LIMIT 1")->fetch();
                $loyalty_earned = 0;
                if ((int)($loyaltySettings['loyalty_enabled'] ?? 0) && $customer_id) {
                    $loyalty_earned = (int)(floor($rounded_total / 100) * (int)($loyaltySettings['loyalty_points_per_100'] ?? 1));
                }

                // Save invoice
                $invoiceId = $t->insert("
                    INSERT INTO invoices (invoice_no, invoice_type, customer_id, invoice_date, due_date, subtotal, gst_amount, cgst_amount, sgst_amount, igst_amount, is_igst, discount, coupon_id, coupon_discount, loyalty_points_earned, loyalty_points_redeemed, round_off, grand_total, payment_method, paid_amount, due_amount, notes, status, created_by)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
                ", [
                    $invoice_number, $invoice_type, $customer_id, date('Y-m-d'), $due_date,
                    $sub_total, $total_tax, $total_cgst, $total_sgst, $total_igst, $is_igst,
                    $discount_amount, $coupon_id, $coupon_discount,
                    $loyalty_earned, $loyalty_points_redeemed,
                    $round_off, $rounded_total, $primary_method,
                    $actual_paid, $balance_amount, $notes, $pay_status, $_SESSION['user_id']
                ]);

                // Save items & update stock
                foreach ($validatedItems as $item) {
                    $t->insert("
                        INSERT INTO invoice_items (invoice_id, product_id, billing_unit_id, billing_unit_name, hsn_code, quantity, primary_qty, rate, gst, cgst, sgst, igst, discount, amount)
                        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
                    ", [$invoiceId, $item['product_id'], $item['billing_unit_id'] ?: null, $item['billing_unit_name'] ?: null, $item['hsn_code'], $item['quantity'], $item['primary_qty'], $item['rate'], $item['tax_rate'], $item['cgst'], $item['sgst'], $item['igst'], $item['discount_amount'], $item['total_amount']]);

                    $newStock = $item['stock_before'] - $item['primary_qty'];
                    $t->query("UPDATE products SET current_stock = ? WHERE id = ?", [$newStock, $item['product_id']]);
                    $t->insert("INSERT INTO stock_transactions (product_id, transaction_type, reference_no, quantity, stock_before, stock_after, remarks, created_by) VALUES (?, 'Sale', ?, ?, ?, ?, ?, ?)",
                        [$item['product_id'], $invoice_number, -$item['primary_qty'], $item['stock_before'], $newStock, "Invoice: $invoice_number", $_SESSION['user_id']]);
                }

                // Save split payments
                foreach ($payments as $p) {
                    $pAmount = (float)$p['amount'];
                    if ($pAmount <= 0) continue;
                    $t->insert("INSERT INTO invoice_payments (invoice_id, payment_method, amount, created_by) VALUES (?,?,?,?)",
                        [$invoiceId, $p['method'], $pAmount, $_SESSION['user_id']]);
                }

                // Customer payment ledger
                if ($customer_id && $actual_paid > 0) {
                    $t->insert("INSERT INTO customer_payments (customer_id, payment_date, amount, payment_method, reference_no, notes, created_by) VALUES (?,?,?,?,?,?,?)",
                        [$customer_id, date('Y-m-d'), $actual_paid, $primary_method, $invoice_number, "Payment for $invoice_number", $_SESSION['user_id']]);
                }

                // Global payments
                if ($actual_paid > 0) {
                    $t->insert("INSERT INTO payments (transaction_type, reference_id, payment_method, amount, transaction_date, remarks, created_by) VALUES ('Customer Payment',?,?,?,?,?,?)",
                        [$invoiceId, $primary_method, $actual_paid, date('Y-m-d'), "Invoice: $invoice_number", $_SESSION['user_id']]);
                }

                // Coupon usage
                if ($coupon_id) {
                    $t->query("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?", [$coupon_id]);
                }

                // Loyalty: earn + redeem
                if ($customer_id && ($loyalty_earned > 0 || $loyalty_points_redeemed > 0)) {
                    $custPts = $t->query("SELECT loyalty_points FROM customers WHERE id = ?", [$customer_id])->fetch();
                    $currentPts = (int)($custPts['loyalty_points'] ?? 0);

                    if ($loyalty_points_redeemed > 0) {
                        $afterRedeem = $currentPts - $loyalty_points_redeemed;
                        $t->insert("INSERT INTO loyalty_transactions (customer_id, invoice_id, points, type, balance_after, remarks, created_by) VALUES (?,?,?,?,?,?,?)",
                            [$customer_id, $invoiceId, -$loyalty_points_redeemed, 'REDEEMED', $afterRedeem, "Redeemed on $invoice_number", $_SESSION['user_id']]);
                        $currentPts = $afterRedeem;
                    }
                    if ($loyalty_earned > 0) {
                        $afterEarn = $currentPts + $loyalty_earned;
                        $t->insert("INSERT INTO loyalty_transactions (customer_id, invoice_id, points, type, balance_after, remarks, created_by) VALUES (?,?,?,?,?,?,?)",
                            [$customer_id, $invoiceId, $loyalty_earned, 'EARNED', $afterEarn, "Earned from $invoice_number", $_SESSION['user_id']]);
                        $currentPts = $afterEarn;
                    }
                    $t->query("UPDATE customers SET loyalty_points = ? WHERE id = ?", [$currentPts, $customer_id]);
                }

                $t->insert("INSERT INTO notifications (title, message, type, is_read, status) VALUES (?,?,'System',0,'PENDING')",
                    ["Invoice Generated", "Invoice $invoice_number - Total: " . Helpers::formatCurrency($rounded_total)]);

                Helpers::logActivity($t, 'billing', "Created invoice: $invoice_number (₹$rounded_total)", (int)$invoiceId);

                return ['invoice_id' => $invoiceId, 'invoice_number' => $invoice_number];
            });

            Helpers::jsonResponse(true, 'Invoice created: ' . $result['invoice_number'], $result);
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Invoice failed: ' . $e->getMessage());
        }
        break;

    case 'list_invoices':
        try {
            $stmt = $db->query("
                SELECT i.*, c.customer_name FROM invoices i
                LEFT JOIN customers c ON i.customer_id = c.id
                WHERE i.status != 'INACTIVE' AND i.deleted_at IS NULL
                ORDER BY i.created_at DESC
            ");
            Helpers::jsonResponse(true, 'Invoices list', $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Failed: ' . $e->getMessage());
        }
        break;

    case 'day_end_report':
        $date = $_GET['date'] ?? date('Y-m-d');
        try {
            $sales_by_method = $db->query("
                SELECT payment_method, COUNT(*) as count, SUM(grand_total) as total, SUM(paid_amount) as paid
                FROM invoices WHERE invoice_date = ? AND status != 'INACTIVE' GROUP BY payment_method
            ", [$date])->fetchAll();

            $totals = $db->query("
                SELECT COUNT(*) as invoice_count, IFNULL(SUM(grand_total),0) as total_sales, IFNULL(SUM(paid_amount),0) as total_received, IFNULL(SUM(due_amount),0) as total_due
                FROM invoices WHERE invoice_date = ? AND status != 'INACTIVE'
            ", [$date])->fetch();

            $returns = ['total' => 0];
            try {
                $returns = $db->query("SELECT IFNULL(SUM(total_amount),0) as total FROM sales_returns WHERE return_date = ? AND status = 'ACTIVE'", [$date])->fetch();
            } catch (Exception $e) { /* sales_returns table may not exist */ }
            $expenses = $db->query("SELECT IFNULL(SUM(amount),0) as total FROM expenses WHERE expense_date = ? AND status = 'ACTIVE'", [$date])->fetch();

            $top_products = $db->query("
                SELECT p.product_name, SUM(ii.quantity) as qty_sold, SUM(ii.amount) as revenue
                FROM invoice_items ii JOIN invoices i ON ii.invoice_id = i.id JOIN products p ON ii.product_id = p.id
                WHERE i.invoice_date = ? AND i.status != 'INACTIVE'
                GROUP BY ii.product_id ORDER BY qty_sold DESC LIMIT 5
            ", [$date])->fetchAll();

            $cashier_sales = $db->query("
                SELECT u.name as cashier, COUNT(*) as bills, SUM(i.grand_total) as total
                FROM invoices i JOIN users u ON i.created_by = u.id
                WHERE i.invoice_date = ? AND i.status != 'INACTIVE' GROUP BY i.created_by
            ", [$date])->fetchAll();

            // Split payment breakdown
            $split_breakdown = $db->query("
                SELECT ip.payment_method, SUM(ip.amount) as total
                FROM invoice_payments ip JOIN invoices i ON ip.invoice_id = i.id
                WHERE i.invoice_date = ? AND i.status != 'INACTIVE' GROUP BY ip.payment_method
            ", [$date])->fetchAll();

            Helpers::jsonResponse(true, 'Day-end report', [
                'date' => $date, 'totals' => $totals,
                'sales_by_method' => $sales_by_method,
                'split_breakdown' => $split_breakdown,
                'returns_total' => (float)($returns['total'] ?? 0),
                'expenses_total' => (float)($expenses['total'] ?? 0),
                'top_products' => $top_products,
                'cashier_sales' => $cashier_sales,
                'net_cash' => (float)($totals['total_received'] ?? 0) - (float)($returns['total'] ?? 0) - (float)($expenses['total'] ?? 0)
            ]);
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Failed: ' . $e->getMessage());
        }
        break;

    case 'get_invoice_payments':
        $id = (int)($_GET['id'] ?? 0);
        try {
            $payments = $db->query("SELECT * FROM invoice_payments WHERE invoice_id = ? AND status = 'ACTIVE'", [$id])->fetchAll();
            Helpers::jsonResponse(true, 'Payments', $payments);
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
                $db->query("UPDATE invoices SET status = 'INACTIVE', deleted_at = CURRENT_TIMESTAMP WHERE id IN ($placeholders)", $ids);
                Helpers::logActivity($db, 'billing', 'Bulk deleted ' . count($ids) . ' records');
                Helpers::jsonResponse(true, count($ids) . ' records deleted successfully');
            }
            Helpers::jsonResponse(false, 'Unknown bulk action');
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Bulk action failed: ' . $e->getMessage());
        }
        break;

    default:
        Helpers::jsonResponse(false, 'Action not found: ' . $action);
}
