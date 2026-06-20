<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Notifications API Endpoint (Part 3)
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

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

switch ($action) {
    case 'list':
        try {
            $alerts = [];
            
            // 1. Fetch Dynamic Low Stock Alerts
            $lowStockProducts = $db->query("
                SELECT id, product_name, current_stock, minimum_stock 
                FROM products 
                WHERE current_stock <= minimum_stock AND status = 'ACTIVE' AND deleted_at IS NULL
                LIMIT 5
            ")->fetchAll();

            foreach ($lowStockProducts as $p) {
                $alerts[] = [
                    'id' => 'stock_' . $p['id'],
                    'title' => 'Low Stock Warning',
                    'message' => 'Product "' . $p['product_name'] . '" has only ' . (float)$p['current_stock'] . ' units remaining (Min: ' . (float)$p['minimum_stock'] . ')',
                    'type' => 'STOCK',
                    'created_at' => date('Y-m-d H:i:s'),
                    'is_read' => 0
                ];
            }

            // 2. Fetch notifications from DB table
            $dbAlerts = $db->query("
                SELECT id, title, message, type, is_read, created_at 
                FROM notifications 
                WHERE is_read = 0 AND status = 'ACTIVE' AND deleted_at IS NULL
                ORDER BY created_at DESC 
                LIMIT 10
            ")->fetchAll();

            foreach ($dbAlerts as $n) {
                $alerts[] = [
                    'id' => $n['id'],
                    'title' => $n['title'],
                    'message' => $n['message'],
                    'type' => $n['type'],
                    'created_at' => $n['created_at'],
                    'is_read' => $n['is_read']
                ];
            }

            // Unread Count
            $unreadCount = count($alerts);

            Helpers::jsonResponse(true, "Notifications list", [
                'count' => $unreadCount,
                'alerts' => $alerts
            ]);
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed to load notifications: " . $e->getMessage());
        }
        break;

    case 'mark_all_read':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helpers::jsonResponse(false, "Method not allowed.");
        }
        try {
            $db->query("UPDATE notifications SET is_read = 1 WHERE is_read = 0");
            Helpers::jsonResponse(true, "All alerts marked as read.");
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed to update: " . $e->getMessage());
        }
        break;

    case 'mark_read':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helpers::jsonResponse(false, "Method not allowed.");
        }
        $id = $_POST['id'] ?? '';
        if (empty($id) || strpos($id, 'stock_') === 0) {
            // Virtual alert, count it success immediately
            Helpers::jsonResponse(true, "Virtual alert marked read.");
        }

        try {
            $db->query("UPDATE notifications SET is_read = 1 WHERE id = ?", [(int)$id]);
            Helpers::jsonResponse(true, "Alert marked as read.");
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed: " . $e->getMessage());
        }
        break;

    default:
        Helpers::jsonResponse(false, "Action not found: " . $action);
}
