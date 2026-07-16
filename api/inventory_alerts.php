<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Inventory Alerts API Endpoint
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

try {
    $data = [];

    // 1. Low Stock (Current stock <= min stock but > 0)
    $data['low_stock'] = $db->query("
        SELECT id, product_name, current_stock, minimum_stock 
        FROM products 
        WHERE current_stock <= minimum_stock AND current_stock > 0 AND status = 'ACTIVE' AND deleted_at IS NULL
        ORDER BY current_stock ASC
        LIMIT 10
    ")->fetchAll();

    // 2. Out of Stock
    $data['out_of_stock'] = $db->query("
        SELECT id, product_name, current_stock 
        FROM products 
        WHERE current_stock <= 0 AND status = 'ACTIVE' AND deleted_at IS NULL
        ORDER BY product_name ASC
        LIMIT 10
    ")->fetchAll();

    // 3. Expiry Soon (Next 30 days)
    $data['expiry_soon'] = $db->query("
        SELECT p.id, p.product_name, pb.batch_no, pb.expiry_date, pb.quantity 
        FROM product_batches pb 
        JOIN products p ON pb.product_id = p.id 
        WHERE pb.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) 
          AND pb.expiry_date >= CURDATE() 
          AND pb.quantity > 0 
          AND p.status = 'ACTIVE' 
          AND pb.deleted_at IS NULL
        ORDER BY pb.expiry_date ASC
        LIMIT 10
    ")->fetchAll();

    // 4. Fast Moving (High sales in last 30 days)
    $data['fast_moving'] = $db->query("
        SELECT p.id, p.product_name, SUM(ii.quantity) as total_sold 
        FROM invoice_items ii 
        JOIN products p ON ii.product_id = p.id 
        JOIN invoices i ON ii.invoice_id = i.id 
        WHERE i.invoice_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
          AND p.status = 'ACTIVE' 
          AND i.deleted_at IS NULL
        GROUP BY p.id 
        ORDER BY total_sold DESC 
        LIMIT 10
    ")->fetchAll();

    // 5. Dead Stock (In stock but no sales in last 90 days)
    $data['dead_stock'] = $db->query("
        SELECT id, product_name, current_stock 
        FROM products 
        WHERE current_stock > 0 
          AND status = 'ACTIVE' 
          AND deleted_at IS NULL
          AND id NOT IN (
              SELECT DISTINCT ii.product_id 
              FROM invoice_items ii 
              JOIN invoices i ON ii.invoice_id = i.id 
              WHERE i.invoice_date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
          )
        ORDER BY current_stock DESC 
        LIMIT 10
    ")->fetchAll();

    Helpers::jsonResponse(true, "Inventory Alerts fetched successfully", $data);

} catch (Exception $e) {
    Helpers::jsonResponse(false, "Failed to load inventory alerts: " . $e->getMessage());
}
