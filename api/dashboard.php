<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Dashboard Statistics API (Part 2 Database updates)
 */

require_once __DIR__ . '/../vendor/autoload.php';
use App\Models\Auth;
use App\Models\Helpers;
use App\Models\Database;

require_once __DIR__ . '/../config/database.php';

$db = new Database();
$auth = new Auth($db);
$auth->requirePermission('Access Dashboard');

$driver = $db->getConnection()->getAttribute(PDO::ATTR_DRIVER_NAME);

// Setup database date conditions based on active driver
$todayQuery = ($driver === 'sqlite') ? "date('now', 'localtime')" : "CURDATE()";
$sevenDaysAgo = ($driver === 'sqlite') ? "date('now', '-6 days', 'localtime')" : "DATE_SUB(CURDATE(), INTERVAL 6 DAY)";
$sixMonthsAgo = ($driver === 'sqlite') ? "date('now', '-5 months', 'start of month')" : "DATE_SUB(CURDATE(), INTERVAL 5 MONTH)";

try {
    // 1. KPI Metrics
    // Today's Sales
    $salesStmt = $db->query("SELECT SUM(grand_total) as total FROM invoices WHERE invoice_date = $todayQuery AND status != 'CANCELLED'");
    $todaySales = (float)($salesStmt->fetch()['total'] ?? 0);

    // Today's Purchases
    $purchaseStmt = $db->query("SELECT SUM(total_amount) as total FROM purchases WHERE purchase_date = $todayQuery AND status = 'ACTIVE'");
    $todayPurchases = (float)($purchaseStmt->fetch()['total'] ?? 0);

    // Today's Expenses
    $expenseStmt = $db->query("SELECT SUM(amount) as total FROM expenses WHERE expense_date = $todayQuery AND status = 'ACTIVE'");
    $todayExpenses = (float)($expenseStmt->fetch()['total'] ?? 0);

    // Today's Cost of Goods Sold (COGS)
    $cogsStmt = $db->query("
        SELECT SUM(ii.quantity * p.cost_price) as total_cogs 
        FROM invoice_items ii 
        JOIN invoices i ON ii.invoice_id = i.id 
        JOIN products p ON ii.product_id = p.id 
        WHERE i.invoice_date = $todayQuery AND i.status != 'CANCELLED'
    ");
    $todayCogs = (float)($cogsStmt->fetch()['total_cogs'] ?? 0);

    // Today's Profit: Sales Revenue - COGS - Expenses
    $todayProfit = $todaySales - $todayCogs - $todayExpenses;

    // Counts
    $totalCustomers = (int)($db->query("SELECT COUNT(*) as count FROM customers WHERE status = 'ACTIVE'")->fetch()['count'] ?? 0);
    $totalSuppliers = (int)($db->query("SELECT COUNT(*) as count FROM suppliers WHERE status = 'ACTIVE'")->fetch()['count'] ?? 0);
    $totalProducts = (int)($db->query("SELECT COUNT(*) as count FROM products WHERE status = 'ACTIVE'")->fetch()['count'] ?? 0);
    
    // Low stock count
    $lowStockCount = (int)($db->query("SELECT COUNT(*) as count FROM products WHERE status = 'ACTIVE' AND current_stock <= minimum_stock")->fetch()['count'] ?? 0);

    // v2.0: Overdue invoices
    $overdueCount = 0;
    try {
        $overdueCount = (int)($db->query("SELECT COUNT(*) as count FROM invoices WHERE status IN ('UNPAID','PARTIAL') AND due_date IS NOT NULL AND due_date < $todayQuery AND deleted_at IS NULL")->fetch()['count'] ?? 0);
    } catch (Exception $e) { /* due_date column may not exist yet */ }

    // v2.0: Held bills count
    $heldCount = 0;
    try {
        $heldCount = (int)($db->query("SELECT COUNT(*) as count FROM held_bills WHERE status = 'ACTIVE' AND deleted_at IS NULL")->fetch()['count'] ?? 0);
    } catch (Exception $e) { /* table may not exist yet */ }

    // v2.0: Outstanding receivables
    $receivableTotal = (float)($db->query("SELECT IFNULL(SUM(due_amount),0) as total FROM invoices WHERE status IN ('UNPAID','PARTIAL') AND deleted_at IS NULL")->fetch()['total'] ?? 0);

    // v2.0: Expiring stock (within 30 days)
    $expiringCount = 0;
    try {
        $thirtyDaysLater = ($driver === 'sqlite') ? "date('now', '+30 days', 'localtime')" : "DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
        $expiringCount = (int)($db->query("SELECT COUNT(*) as count FROM product_batches WHERE status = 'ACTIVE' AND expiry_date IS NOT NULL AND expiry_date <= $thirtyDaysLater AND quantity > 0")->fetch()['count'] ?? 0);
    } catch (Exception $e) { /* table may not exist yet */ }

    // v2.0: Top 5 products this month
    $monthStart = ($driver === 'sqlite') ? "date('now', 'start of month')" : "DATE_FORMAT(CURDATE(), '%Y-%m-01')";
    $topProducts = [];
    try {
        $topProductsStmt = $db->query("
            SELECT p.product_name, SUM(ii.quantity) as qty_sold, SUM(ii.amount) as revenue
            FROM invoice_items ii JOIN invoices i ON ii.invoice_id = i.id JOIN products p ON ii.product_id = p.id
            WHERE i.invoice_date >= $monthStart AND i.status != 'CANCELLED'
            GROUP BY ii.product_id ORDER BY qty_sold DESC LIMIT 5
        ");
        $topProducts = $topProductsStmt->fetchAll();
    } catch (Exception $e) {}

    // v2.0: Payment mode distribution this month
    $paymentModes = [];
    try {
        $paymentModesStmt = $db->query("
            SELECT payment_method, COUNT(*) as count, SUM(grand_total) as total
            FROM invoices WHERE invoice_date >= $monthStart AND status != 'CANCELLED'
            GROUP BY payment_method ORDER BY total DESC
        ");
        $paymentModes = $paymentModesStmt->fetchAll();
    } catch (Exception $e) {}


    // 2. Charts Data
    // Daily Sales (Last 7 days)
    if ($driver === 'sqlite') {
        $dailySalesQuery = "
            SELECT date(invoice_date) as sales_date, SUM(grand_total) as total 
            FROM invoices 
            WHERE invoice_date >= $sevenDaysAgo AND status != 'CANCELLED'
            GROUP BY date(invoice_date) 
            ORDER BY sales_date ASC
        ";
    } else {
        $dailySalesQuery = "
            SELECT DATE(invoice_date) as sales_date, SUM(grand_total) as total 
            FROM invoices 
            WHERE invoice_date >= $sevenDaysAgo AND status != 'CANCELLED'
            GROUP BY DATE(invoice_date) 
            ORDER BY sales_date ASC
        ";
    }
    $dailySalesStmt = $db->query($dailySalesQuery);
    $rawDailySales = $dailySalesStmt->fetchAll();

    // Fill missing days
    $dailySalesData = [];
    for ($i = 6; $i >= 0; $i--) {
        $dateStr = date('Y-m-d', strtotime("-$i days"));
        $val = 0;
        foreach ($rawDailySales as $row) {
            if ($row['sales_date'] === $dateStr) {
                $val = (float)$row['total'];
                break;
            }
        }
        $dailySalesData[] = [
            'label' => date('d M', strtotime($dateStr)),
            'value' => $val
        ];
    }

    // Monthly Sales (Last 6 Months)
    if ($driver === 'sqlite') {
        $monthlyQuery = "
            SELECT strftime('%Y-%m', invoice_date) as sales_month, SUM(grand_total) as total 
            FROM invoices 
            WHERE invoice_date >= $sixMonthsAgo AND status != 'CANCELLED'
            GROUP BY strftime('%Y-%m', invoice_date) 
            ORDER BY sales_month ASC
        ";
    } else {
        $monthlyQuery = "
            SELECT DATE_FORMAT(invoice_date, '%Y-%m') as sales_month, SUM(grand_total) as total 
            FROM invoices 
            WHERE invoice_date >= $sixMonthsAgo AND status != 'CANCELLED'
            GROUP BY DATE_FORMAT(invoice_date, '%Y-%m') 
            ORDER BY sales_month ASC
        ";
    }
    $monthlyStmt = $db->query($monthlyQuery);
    $rawMonthly = $monthlyStmt->fetchAll();

    $monthlySalesData = [];
    for ($i = 5; $i >= 0; $i--) {
        $monthStr = date('Y-m', strtotime("-$i months"));
        $val = 0;
        foreach ($rawMonthly as $row) {
            if ($row['sales_month'] === $monthStr) {
                $val = (float)$row['total'];
                break;
            }
        }
        $monthlySalesData[] = [
            'label' => date('M Y', strtotime($monthStr . "-01")),
            'value' => $val
        ];
    }

    // Expenses category distribution (Last 30 days)
    $thirtyDaysAgo = ($driver === 'sqlite') ? "date('now', '-29 days', 'localtime')" : "DATE_SUB(CURDATE(), INTERVAL 29 DAY)";
    $expenseCatStmt = $db->query("
        SELECT ec.category_name as category, SUM(e.amount) as total 
        FROM expenses e 
        JOIN expense_categories ec ON e.category_id = ec.id 
        WHERE e.expense_date >= $thirtyDaysAgo AND e.status = 'ACTIVE'
        GROUP BY ec.id
        ORDER BY total DESC
    ");
    $expenseCatData = $expenseCatStmt->fetchAll();


    // 3. Recent Activities Lists
    // Recent 5 Invoices
    $recentInvoicesStmt = $db->query("
        SELECT i.invoice_no, i.invoice_date, i.grand_total, i.status, c.customer_name 
        FROM invoices i 
        LEFT JOIN customers c ON i.customer_id = c.id 
        ORDER BY i.created_at DESC LIMIT 5
    ");
    $recentInvoices = $recentInvoicesStmt->fetchAll();

    // Recent 5 Payments (From unified payments log table!)
    $recentPaymentsStmt = $db->query("
        SELECT p.transaction_type, p.transaction_date, p.payment_method, p.amount, p.remarks,
               COALESCE(c.customer_name, s.supplier_name) as party_name
        FROM payments p
        LEFT JOIN customer_payments cp ON p.reference_id = cp.id AND p.transaction_type = 'Customer Payment'
        LEFT JOIN customers c ON cp.customer_id = c.id
        LEFT JOIN supplier_payments sp ON p.reference_id = sp.id AND p.transaction_type = 'Supplier Payment'
        LEFT JOIN suppliers s ON sp.supplier_id = s.id
        WHERE p.status = 'ACTIVE'
        ORDER BY p.transaction_date DESC, p.id DESC LIMIT 5
    ");
    $recentPayments = $recentPaymentsStmt->fetchAll();

    Helpers::jsonResponse(true, "Dashboard stats loaded", [
        'kpis' => [
            'today_sales' => $todaySales,
            'today_purchases' => $todayPurchases,
            'today_expenses' => $todayExpenses,
            'today_profit' => $todayProfit,
            'total_customers' => $totalCustomers,
            'total_suppliers' => $totalSuppliers,
            'total_products' => $totalProducts,
            'low_stock_count' => $lowStockCount,
            'overdue_count' => $overdueCount,
            'held_count' => $heldCount,
            'receivable_total' => $receivableTotal,
            'expiring_count' => $expiringCount
        ],
        'charts' => [
            'daily_sales' => $dailySalesData,
            'monthly_sales' => $monthlySalesData,
            'expenses_by_category' => $expenseCatData,
            'top_products' => $topProducts,
            'payment_modes' => $paymentModes
        ],
        'recent' => [
            'invoices' => $recentInvoices,
            'expenses' => [], // optional or fetched if needed
            'payments' => $recentPayments
        ]
    ]);

} catch (Exception $e) {
    Helpers::jsonResponse(false, "Dashboard loading error: " . $e->getMessage());
}
