<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Analytics & Reports API Endpoint (Part 3)
 */

require_once __DIR__ . '/../vendor/autoload.php';
use App\Models\Auth;
use App\Models\Helpers;
use App\Models\Database;

require_once __DIR__ . '/../config/database.php';

$db = new Database();
$auth = new Auth($db);
$auth->requirePermission('View Reports');

$action = $_GET['action'] ?? $_POST['action'] ?? 'summary';

// Extract dates if provided
$start_date = $_GET['start_date'] ?? $_POST['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end_date'] ?? $_POST['end_date'] ?? date('Y-m-d');

switch ($action) {
    case 'summary':
        // Profit & Loss Report Summary Card Calculations
        try {
            $driver = $db->getConnection()->getAttribute(PDO::ATTR_DRIVER_NAME);
            
            // 1. Total Sales
            $salesStmt = $db->query("
                SELECT IFNULL(SUM(grand_total), 0.00) as total 
                FROM invoices 
                WHERE status != 'INACTIVE' AND deleted_at IS NULL AND invoice_date BETWEEN ? AND ?
            ", [$start_date, $end_date]);
            $totalSales = (float)$salesStmt->fetch()['total'];

            // 2. Total Purchases
            $purchStmt = $db->query("
                SELECT IFNULL(SUM(total_amount), 0.00) as total 
                FROM purchases 
                WHERE status != 'INACTIVE' AND deleted_at IS NULL AND purchase_date BETWEEN ? AND ?
            ", [$start_date, $end_date]);
            $totalPurchases = (float)$purchStmt->fetch()['total'];

            // 3. Total Expenses
            $expStmt = $db->query("
                SELECT IFNULL(SUM(amount), 0.00) as total 
                FROM expenses 
                WHERE status = 'ACTIVE' AND deleted_at IS NULL AND expense_date BETWEEN ? AND ?
            ", [$start_date, $end_date]);
            $totalExpenses = (float)$expStmt->fetch()['total'];

            // 4. Cost of Goods Sold (COGS)
            $cogsStmt = $db->query("
                SELECT IFNULL(SUM(ii.quantity * p.cost_price), 0.00) as total_cogs
                FROM invoice_items ii
                JOIN invoices i ON ii.invoice_id = i.id
                JOIN products p ON ii.product_id = p.id
                WHERE i.status != 'INACTIVE' AND i.deleted_at IS NULL AND i.invoice_date BETWEEN ? AND ?
            ", [$start_date, $end_date]);
            $cogs = (float)$cogsStmt->fetch()['total_cogs'];

            // Gross Profit = Sales - COGS
            $grossProfit = $totalSales - $cogs;
            // Net Profit = Gross Profit - Expenses
            $netProfit = $grossProfit - $totalExpenses;

            Helpers::jsonResponse(true, "Report summary loaded", [
                'total_sales' => $totalSales,
                'total_purchases' => $totalPurchases,
                'total_expenses' => $totalExpenses,
                'cogs' => $cogs,
                'gross_profit' => $grossProfit,
                'net_profit' => $netProfit,
                'start_date' => $start_date,
                'end_date' => $end_date
            ]);
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed to compute P&L metrics: " . $e->getMessage());
        }
        break;

    case 'sales':
        try {
            $stmt = $db->query("
                SELECT i.*, c.customer_name 
                FROM invoices i
                LEFT JOIN customers c ON i.customer_id = c.id
                WHERE i.status != 'INACTIVE' AND i.deleted_at IS NULL AND i.invoice_date BETWEEN ? AND ?
                ORDER BY i.invoice_date DESC
            ", [$start_date, $end_date]);
            Helpers::jsonResponse(true, "Sales reports loaded", $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, $e->getMessage());
        }
        break;

    case 'purchases':
        try {
            $stmt = $db->query("
                SELECT p.*, s.supplier_name 
                FROM purchases p
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                WHERE p.status != 'INACTIVE' AND p.deleted_at IS NULL AND p.purchase_date BETWEEN ? AND ?
                ORDER BY p.purchase_date DESC
            ", [$start_date, $end_date]);
            Helpers::jsonResponse(true, "Purchases reports loaded", $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, $e->getMessage());
        }
        break;

    case 'stock':
        try {
            // Stock valuation at cost and selling price
            $stmt = $db->query("
                SELECT id, product_name, sku, cost_price, selling_price, opening_stock, current_stock, minimum_stock,
                  (current_stock * cost_price) as valuation_cost,
                  (current_stock * selling_price) as valuation_selling
                FROM products
                WHERE status = 'ACTIVE' AND deleted_at IS NULL
                ORDER BY current_stock ASC
            ");
            Helpers::jsonResponse(true, "Stock level reports loaded", $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, $e->getMessage());
        }
        break;

    case 'expenses':
        try {
            $stmt = $db->query("
                SELECT e.*, ec.category_name 
                FROM expenses e
                JOIN expense_categories ec ON e.category_id = ec.id
                WHERE e.status = 'ACTIVE' AND e.deleted_at IS NULL AND e.expense_date BETWEEN ? AND ?
                ORDER BY e.expense_date DESC
            ", [$start_date, $end_date]);
            Helpers::jsonResponse(true, "Expenses reports loaded", $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, $e->getMessage());
        }
        break;

    case 'customers':
        try {
            $stmt = $db->query("
                SELECT c.*,
                  (c.opening_balance + 
                   (SELECT IFNULL(SUM(grand_total), 0) FROM invoices WHERE customer_id = c.id AND status != 'INACTIVE') - 
                   (SELECT IFNULL(SUM(amount), 0) FROM customer_payments WHERE customer_id = c.id AND status = 'ACTIVE')) as credit_balance
                FROM customers c
                WHERE c.status = 'ACTIVE' AND c.deleted_at IS NULL
                ORDER BY credit_balance DESC
            ");
            Helpers::jsonResponse(true, "Customer receivables loaded", $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, $e->getMessage());
        }
        break;

    case 'suppliers':
        try {
            $stmt = $db->query("
                SELECT s.*,
                  (s.opening_balance + 
                   (SELECT IFNULL(SUM(total_amount), 0) FROM purchases WHERE supplier_id = s.id AND status != 'INACTIVE') - 
                   (SELECT IFNULL(SUM(amount), 0) FROM supplier_payments WHERE supplier_id = s.id AND status = 'ACTIVE')) as outstanding_balance
                FROM suppliers s
                WHERE s.status = 'ACTIVE' AND s.deleted_at IS NULL
                ORDER BY outstanding_balance DESC
            ");
            Helpers::jsonResponse(true, "Supplier payables loaded", $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, $e->getMessage());
        }
        break;

    case 'gst':
        try {
            $stmt = $db->query("
                SELECT i.id, i.invoice_no, i.invoice_date, i.subtotal, i.gst_amount, i.cgst_amount, i.sgst_amount, i.igst_amount, i.is_igst, c.customer_name
                FROM invoices i LEFT JOIN customers c ON i.customer_id = c.id
                WHERE i.status != 'INACTIVE' AND i.deleted_at IS NULL AND i.invoice_date BETWEEN ? AND ? AND i.gst_amount > 0
                ORDER BY i.invoice_date DESC
            ", [$start_date, $end_date]);
            Helpers::jsonResponse(true, 'GST report', $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, $e->getMessage());
        }
        break;

    case 'overdue':
        try {
            $driver = $db->getConnection()->getAttribute(PDO::ATTR_DRIVER_NAME);
            $todayQ = ($driver === 'sqlite') ? "date('now','localtime')" : "CURDATE()";
            $daysCalc = ($driver === 'sqlite')
                ? "CAST(julianday('now','localtime') - julianday(i.due_date) AS INTEGER)"
                : "DATEDIFF(CURDATE(), i.due_date)";

            $stmt = $db->query("
                SELECT i.id, i.invoice_no, i.invoice_date, i.due_date, i.grand_total, i.due_amount, c.customer_name,
                $daysCalc as days_overdue
                FROM invoices i LEFT JOIN customers c ON i.customer_id = c.id
                WHERE i.status IN ('UNPAID','PARTIAL') AND i.deleted_at IS NULL AND i.due_date IS NOT NULL AND i.due_date < $todayQ
                ORDER BY i.due_date ASC
            ");
            Helpers::jsonResponse(true, 'Overdue invoices', $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, $e->getMessage());
        }
        break;

    default:
        Helpers::jsonResponse(false, "Unknown action: " . $action);
}
