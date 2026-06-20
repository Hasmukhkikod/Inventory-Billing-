<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Global Search API Endpoint (Part 3)
 */

require_once __DIR__ . '/../vendor/autoload.php';
use App\Models\Auth;
use App\Models\Helpers;
use App\Models\Database;

require_once __DIR__ . '/../config/database.php';

$db = new Database();
$auth = new Auth($db);
if (!$auth->check()) {
    Helpers::jsonResponse(false, "Unauthorized Access.");
}

$q = trim($_GET['q'] ?? '');

if (strlen($q) < 2) {
    Helpers::jsonResponse(true, "Query too short", []);
}

try {
    $results = [
        'products' => [],
        'customers' => [],
        'suppliers' => [],
        'invoices' => []
    ];

    $likeParam = "%$q%";

    // 1. Search Products
    if ($auth->hasPermission('Manage Inventory')) {
        $prodStmt = $db->query("
            SELECT id, product_name as title, sku as subtitle, current_stock as stock, selling_price as price
            FROM products 
            WHERE status = 'ACTIVE' AND deleted_at IS NULL AND (product_name LIKE ? OR sku LIKE ? OR barcode = ?)
            LIMIT 5
        ", [$likeParam, $likeParam, $q]);
        $results['products'] = $prodStmt->fetchAll();
    }

    // 2. Search Customers
    if ($auth->hasPermission('Manage Customers')) {
        $custStmt = $db->query("
            SELECT id, customer_name as title, mobile as subtitle
            FROM customers
            WHERE status = 'ACTIVE' AND deleted_at IS NULL AND (customer_name LIKE ? OR mobile LIKE ?)
            LIMIT 5
        ", [$likeParam, $likeParam]);
        $results['customers'] = $custStmt->fetchAll();
    }

    // 3. Search Suppliers
    if ($auth->hasPermission('Manage Suppliers')) {
        $suppStmt = $db->query("
            SELECT id, supplier_name as title, mobile as subtitle
            FROM suppliers
            WHERE status = 'ACTIVE' AND deleted_at IS NULL AND (supplier_name LIKE ? OR mobile LIKE ?)
            LIMIT 5
        ", [$likeParam, $likeParam]);
        $results['suppliers'] = $suppStmt->fetchAll();
    }

    // 4. Search Invoices
    if ($auth->hasPermission('Create Invoice')) {
        $invStmt = $db->query("
            SELECT id, invoice_no as title, invoice_date as subtitle, grand_total as price
            FROM invoices
            WHERE status != 'INACTIVE' AND deleted_at IS NULL AND (invoice_no LIKE ?)
            LIMIT 5
        ", [$likeParam]);
        $results['invoices'] = $invStmt->fetchAll();
    }

    Helpers::jsonResponse(true, "Search results", $results);

} catch (Exception $e) {
    Helpers::jsonResponse(false, "Search error: " . $e->getMessage());
}
