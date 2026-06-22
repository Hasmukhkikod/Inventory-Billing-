<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Customer CRM & Receivable Management API (Part 2 Database updates)
 */

require_once __DIR__ . '/../vendor/autoload.php';
use App\Models\Auth;
use App\Models\Helpers;
use App\Models\Database;

require_once __DIR__ . '/../config/database.php';

$db = new Database();
$auth = new Auth($db);
$auth->requirePermission('Manage Customers');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        try {
            $stmt = $db->query("
                SELECT c.*,
                  (c.opening_balance + 
                   (SELECT IFNULL(SUM(grand_total), 0) FROM invoices WHERE customer_id = c.id AND status != 'INACTIVE') - 
                   (SELECT IFNULL(SUM(amount), 0) FROM customer_payments WHERE customer_id = c.id AND status = 'ACTIVE')) as credit_balance
                FROM customers c
                WHERE c.status = 'ACTIVE' AND c.deleted_at IS NULL
                ORDER BY c.customer_name ASC
            ");
            Helpers::jsonResponse(true, "Customers list", $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed to load customers: " . $e->getMessage());
        }
        break;

    case 'get':
        $id = (int)($_GET['id'] ?? 0);
        try {
            $customer = $db->query("
                SELECT c.*,
                  (c.opening_balance + 
                   (SELECT IFNULL(SUM(grand_total), 0) FROM invoices WHERE customer_id = c.id AND status != 'INACTIVE') - 
                   (SELECT IFNULL(SUM(amount), 0) FROM customer_payments WHERE customer_id = c.id AND status = 'ACTIVE')) as credit_balance
                FROM customers c
                WHERE c.id = ? LIMIT 1
            ", [$id])->fetch();
            if ($customer) {
                Helpers::jsonResponse(true, "Customer found", $customer);
            } else {
                Helpers::jsonResponse(false, "Customer not found");
            }
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Error: " . $e->getMessage());
        }
        break;

    case 'save':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, "Invalid method");
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, "CSRF verification failed");

        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['customer_name'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $gst = trim($_POST['gst_number'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $opening_balance = (float)($_POST['opening_balance'] ?? 0);
        $credit_limit = (float)($_POST['credit_limit'] ?? 0);

        if (empty($name) || empty($mobile)) {
            Helpers::jsonResponse(false, "Name and Mobile number are required.");
        }

        // Validate Mobile uniqueness
        $phoneCheckQuery = ($id > 0) 
            ? "SELECT id FROM customers WHERE mobile = ? AND id != ? LIMIT 1"
            : "SELECT id FROM customers WHERE mobile = ? LIMIT 1";
        $phoneParams = ($id > 0) ? [$mobile, $id] : [$mobile];
        
        if ($db->query($phoneCheckQuery, $phoneParams)->fetch()) {
            Helpers::jsonResponse(false, "A customer with this mobile number already exists.");
        }

        try {
            if ($id > 0) {
                $db->query("
                    UPDATE customers 
                    SET customer_name = ?, mobile = ?, email = ?, gst_number = ?, address = ?, opening_balance = ?, credit_limit = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ", [$name, $mobile, $email, $gst, $address, $opening_balance, $credit_limit, $id]);
                
                Helpers::logActivity($db, "customers", "Updated customer ID: $id ($name)", $id);
                Helpers::jsonResponse(true, "Customer details updated successfully.", ['id' => $id]);
            } else {
                $customerId = $db->insert("
                    INSERT INTO customers (customer_name, mobile, email, gst_number, address, opening_balance, credit_limit, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ", [$name, $mobile, $email, $gst, $address, $opening_balance, $credit_limit, $_SESSION['user_id']]);

                Helpers::logActivity($db, "customers", "Created customer ID: $customerId ($name)", $customerId);
                Helpers::jsonResponse(true, "Customer created successfully.", ['id' => $customerId]);
            }
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed to save customer: " . $e->getMessage());
        }
        break;

    case 'receive_payment':
        // Record customer making a payment to reduce credit balance
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, "Invalid method");

        $customerId = (int)($_POST['customer_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $method = trim($_POST['payment_method'] ?? 'CASH');
        $ref = trim($_POST['reference_no'] ?? $_POST['reference_number'] ?? '');
        $remarks = trim($_POST['remarks'] ?? $_POST['notes'] ?? '');
        $date = trim($_POST['payment_date'] ?? date('Y-m-d'));

        if ($customerId <= 0 || $amount <= 0) {
            Helpers::jsonResponse(false, "Valid Customer ID and positive payment amount are required.");
        }

        try {
            $customer = $db->query("SELECT customer_name FROM customers WHERE id = ? LIMIT 1", [$customerId])->fetch();
            if (!$customer) Helpers::jsonResponse(false, "Customer not found.");

            $db->transaction(function($t) use ($customerId, $amount, $method, $ref, $remarks, $date) {
                // 1. Record payment in customer_payments table
                $paymentId = $t->insert("
                    INSERT INTO customer_payments (customer_id, payment_date, amount, payment_method, reference_no, notes, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ", [$customerId, $date, $amount, $method, $ref, $remarks, $_SESSION['user_id']]);

                // 2. Record payment in global payments table
                $t->insert("
                    INSERT INTO payments (transaction_type, reference_id, payment_method, amount, transaction_date, remarks, created_by)
                    VALUES ('Customer Payment', ?, ?, ?, ?, ?, ?)
                ", [$paymentId, $method, $amount, $date, "Customer Payment Received: $remarks", $_SESSION['user_id']]);
            });

            Helpers::logActivity($db, "customers", "Received payment of " . Helpers::formatCurrency($amount) . " from " . $customer['customer_name'], $customerId);
            Helpers::jsonResponse(true, "Payment logged successfully. Customer ledger updated.");
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed to process payment: " . $e->getMessage());
        }
        break;

    case 'ledger':
        // Fetch Unified customer statement (debits vs credits)
        $customerId = (int)($_GET['customer_id'] ?? 0);
        if ($customerId <= 0) Helpers::jsonResponse(false, "Invalid customer ID.");

        try {
            $customer = $db->query("
                SELECT c.*,
                  (c.opening_balance + 
                   (SELECT IFNULL(SUM(grand_total), 0) FROM invoices WHERE customer_id = c.id AND status != 'INACTIVE') - 
                   (SELECT IFNULL(SUM(amount), 0) FROM customer_payments WHERE customer_id = c.id AND status = 'ACTIVE')) as credit_balance
                FROM customers c
                WHERE c.id = ? LIMIT 1
            ", [$customerId])->fetch();
            
            if (!$customer) Helpers::jsonResponse(false, "Customer not found.");

            // Merge invoices (debits) and payments (credits)
            $ledgerStmt = $db->query("
                SELECT 'INVOICE' as doc_type, id as doc_id, invoice_no as doc_no, invoice_date as doc_date, grand_total as debit, 0.00 as credit, status as notes
                FROM invoices 
                WHERE customer_id = ? AND status != 'INACTIVE'
                
                UNION ALL
                
                SELECT 'PAYMENT' as doc_type, id as doc_id, reference_no as doc_no, payment_date as doc_date, 0.00 as debit, amount as credit, notes as notes
                FROM customer_payments 
                WHERE customer_id = ? AND status = 'ACTIVE'
                
                ORDER BY doc_date ASC, doc_type DESC
            ", [$customerId, $customerId]);
            
            $transactions = $ledgerStmt->fetchAll();

            // Calculate running balances
            $runningBalance = (float)$customer['opening_balance'];
            $ledger = [];
            
            if ($runningBalance != 0) {
                $ledger[] = [
                    'date' => $customer['created_at'],
                    'type' => 'OPENING',
                    'doc_no' => '-',
                    'notes' => 'Opening Balance',
                    'debit' => $runningBalance > 0 ? $runningBalance : 0.00,
                    'credit' => $runningBalance < 0 ? abs($runningBalance) : 0.00,
                    'balance' => $runningBalance
                ];
            }

            foreach ($transactions as $t) {
                $debit = (float)$t['debit'];
                $credit = (float)$t['credit'];
                
                $runningBalance += ($debit - $credit);
                
                $ledger[] = [
                    'date' => $t['doc_date'],
                    'type' => $t['doc_type'],
                    'doc_no' => $t['doc_no'] ?: ('PAY-' . $t['doc_id']),
                    'notes' => $t['notes'],
                    'debit' => $debit,
                    'credit' => $credit,
                    'balance' => $runningBalance
                ];
            }

            Helpers::jsonResponse(true, "Customer statement loaded", [
                'customer' => $customer,
                'ledger' => $ledger
            ]);
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Ledger generation failed: " . $e->getMessage());
        }
        break;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) Helpers::jsonResponse(false, "Invalid customer ID.");
        try {
            $db->query("UPDATE customers SET status = 'INACTIVE', deleted_at = CURRENT_TIMESTAMP WHERE id = ?", [$id]);
            Helpers::logActivity($db, "customers", "Soft deleted customer ID: $id", $id);
            Helpers::jsonResponse(true, "Customer deleted successfully.");
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed: " . $e->getMessage());
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
                $db->query("UPDATE customers SET status = 'INACTIVE', deleted_at = CURRENT_TIMESTAMP WHERE id IN ($placeholders)", $ids);
                Helpers::logActivity($db, 'customers', 'Bulk deleted ' . count($ids) . ' records');
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
