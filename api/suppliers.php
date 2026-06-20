<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Supplier CRM & Payable Management API (Part 2 Database updates)
 */

require_once __DIR__ . '/../vendor/autoload.php';
use App\Models\Auth;
use App\Models\Helpers;
use App\Models\Database;

require_once __DIR__ . '/../config/database.php';

$db = new Database();
$auth = new Auth($db);
$auth->requirePermission('Manage Suppliers');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        try {
            $stmt = $db->query("
                SELECT s.*,
                  (s.opening_balance + 
                   (SELECT IFNULL(SUM(total_amount), 0) FROM purchases WHERE supplier_id = s.id AND status != 'INACTIVE') - 
                   (SELECT IFNULL(SUM(amount), 0) FROM supplier_payments WHERE supplier_id = s.id AND status = 'ACTIVE')) as outstanding_balance
                FROM suppliers s
                WHERE s.status = 'ACTIVE' AND s.deleted_at IS NULL
                ORDER BY s.supplier_name ASC
            ");
            Helpers::jsonResponse(true, "Suppliers list", $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed to load suppliers: " . $e->getMessage());
        }
        break;

    case 'get':
        $id = (int)($_GET['id'] ?? 0);
        try {
            $supplier = $db->query("
                SELECT s.*,
                  (s.opening_balance + 
                   (SELECT IFNULL(SUM(total_amount), 0) FROM purchases WHERE supplier_id = s.id AND status != 'INACTIVE') - 
                   (SELECT IFNULL(SUM(amount), 0) FROM supplier_payments WHERE supplier_id = s.id AND status = 'ACTIVE')) as outstanding_balance
                FROM suppliers s
                WHERE s.id = ? LIMIT 1
            ", [$id])->fetch();
            if ($supplier) {
                Helpers::jsonResponse(true, "Supplier found", $supplier);
            } else {
                Helpers::jsonResponse(false, "Supplier not found");
            }
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Error: " . $e->getMessage());
        }
        break;

    case 'save':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, "Invalid method");
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, "CSRF verification failed");

        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['supplier_name'] ?? '');
        $contact = trim($_POST['contact_person'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $gst = trim($_POST['gst_number'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $opening_balance = (float)($_POST['opening_balance'] ?? 0);

        if (empty($name) || empty($mobile)) {
            Helpers::jsonResponse(false, "Name and Mobile number are required.");
        }

        // Validate Mobile uniqueness
        $phoneCheckQuery = ($id > 0) 
            ? "SELECT id FROM suppliers WHERE mobile = ? AND id != ? LIMIT 1"
            : "SELECT id FROM suppliers WHERE mobile = ? LIMIT 1";
        $phoneParams = ($id > 0) ? [$mobile, $id] : [$mobile];
        
        if ($db->query($phoneCheckQuery, $phoneParams)->fetch()) {
            Helpers::jsonResponse(false, "A supplier with this mobile number already exists.");
        }

        try {
            if ($id > 0) {
                $db->query("
                    UPDATE suppliers 
                    SET supplier_name = ?, contact_person = ?, mobile = ?, email = ?, gst_number = ?, address = ?, opening_balance = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ", [$name, $contact, $mobile, $email, $gst, $address, $opening_balance, $id]);
                
                Helpers::logActivity($db, "suppliers", "Updated supplier ID: $id ($name)", $id);
                Helpers::jsonResponse(true, "Supplier details updated successfully.");
            } else {
                $supplierId = $db->insert("
                    INSERT INTO suppliers (supplier_name, contact_person, mobile, email, gst_number, address, opening_balance, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ", [$name, $contact, $mobile, $email, $gst, $address, $opening_balance, $_SESSION['user_id']]);

                Helpers::logActivity($db, "suppliers", "Created supplier ID: $supplierId ($name)", $supplierId);
                Helpers::jsonResponse(true, "Supplier created successfully.");
            }
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed to save supplier: " . $e->getMessage());
        }
        break;

    case 'make_payment':
        // Record us making a payment to reduce payable outstanding balance
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, "Invalid method");

        $supplierId = (int)($_POST['supplier_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $method = trim($_POST['payment_method'] ?? 'CASH');
        $ref = trim($_POST['reference_no'] ?? $_POST['reference_number'] ?? '');
        $remarks = trim($_POST['remarks'] ?? '');
        $date = trim($_POST['payment_date'] ?? date('Y-m-d'));

        if ($supplierId <= 0 || $amount <= 0) {
            Helpers::jsonResponse(false, "Valid Supplier ID and positive payment amount are required.");
        }

        try {
            $supplier = $db->query("SELECT supplier_name FROM suppliers WHERE id = ? LIMIT 1", [$supplierId])->fetch();
            if (!$supplier) Helpers::jsonResponse(false, "Supplier not found.");

            $db->transaction(function($t) use ($supplierId, $amount, $method, $ref, $remarks, $date) {
                // 1. Record payment in supplier_payments table
                $paymentId = $t->insert("
                    INSERT INTO supplier_payments (supplier_id, payment_date, amount, payment_method, reference_no, notes, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ", [$supplierId, $date, $amount, $method, $ref, $remarks, $_SESSION['user_id']]);

                // 2. Record payment in global payments table
                $t->insert("
                    INSERT INTO payments (transaction_type, reference_id, payment_method, amount, transaction_date, remarks, created_by)
                    VALUES ('Supplier Payment', ?, ?, ?, ?, ?, ?)
                ", [$paymentId, $method, $amount, $date, "Supplier Payment Sent: $remarks", $_SESSION['user_id']]);
            });

            Helpers::logActivity($db, "suppliers", "Paid " . Helpers::formatCurrency($amount) . " to " . $supplier['supplier_name'], $supplierId);
            Helpers::jsonResponse(true, "Payment logged successfully. Supplier ledger updated.");
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed to process payment: " . $e->getMessage());
        }
        break;

    case 'ledger':
        // Fetch Unified supplier statement (debits vs credits)
        $supplierId = (int)($_GET['supplier_id'] ?? 0);
        if ($supplierId <= 0) Helpers::jsonResponse(false, "Invalid supplier ID.");

        try {
            $supplier = $db->query("
                SELECT s.*,
                  (s.opening_balance + 
                   (SELECT IFNULL(SUM(total_amount), 0) FROM purchases WHERE supplier_id = s.id AND status != 'INACTIVE') - 
                   (SELECT IFNULL(SUM(amount), 0) FROM supplier_payments WHERE supplier_id = s.id AND status = 'ACTIVE')) as outstanding_balance
                FROM suppliers s
                WHERE s.id = ? LIMIT 1
            ", [$supplierId])->fetch();
            
            if (!$supplier) Helpers::jsonResponse(false, "Supplier not found.");

            // Merge purchases (credits) and payments (debits)
            $ledgerStmt = $db->query("
                SELECT 'PURCHASE' as doc_type, id as doc_id, purchase_no as doc_no, purchase_date as doc_date, 0.00 as debit, total_amount as credit, payment_status as notes
                FROM purchases 
                WHERE supplier_id = ? AND status != 'INACTIVE'
                
                UNION ALL
                
                SELECT 'PAYMENT' as doc_type, id as doc_id, reference_no as doc_no, payment_date as doc_date, amount as debit, 0.00 as credit, notes as notes
                FROM supplier_payments 
                WHERE supplier_id = ? AND status = 'ACTIVE'
                
                ORDER BY doc_date ASC, doc_type DESC
            ", [$supplierId, $supplierId]);
            
            $transactions = $ledgerStmt->fetchAll();

            // Calculate running balances
            $runningBalance = (float)$supplier['opening_balance'];
            $ledger = [];

            if ($runningBalance != 0) {
                $ledger[] = [
                    'date' => $supplier['created_at'],
                    'type' => 'OPENING',
                    'doc_no' => '-',
                    'notes' => 'Opening Balance',
                    'debit' => $runningBalance < 0 ? abs($runningBalance) : 0.00,
                    'credit' => $runningBalance > 0 ? $runningBalance : 0.00,
                    'balance' => $runningBalance
                ];
            }

            foreach ($transactions as $t) {
                $debit = (float)$t['debit'];
                $credit = (float)$t['credit'];
                
                // Credit increases what we owe; Debit reduces it
                $runningBalance += ($credit - $debit);
                
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

            Helpers::jsonResponse(true, "Supplier statement loaded", [
                'supplier' => $supplier,
                'ledger' => $ledger
            ]);
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Ledger generation failed: " . $e->getMessage());
        }
        break;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) Helpers::jsonResponse(false, "Invalid supplier ID.");
        try {
            $db->query("UPDATE suppliers SET status = 'INACTIVE', deleted_at = CURRENT_TIMESTAMP WHERE id = ?", [$id]);
            Helpers::logActivity($db, "suppliers", "Soft deleted supplier ID: $id", $id);
            Helpers::jsonResponse(true, "Supplier deleted successfully.");
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed: " . $e->getMessage());
        }
        break;

    default:
        Helpers::jsonResponse(false, "Action not found: " . $action);
}
