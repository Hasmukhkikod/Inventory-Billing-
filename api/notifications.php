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

            // 1a. Payment Due Alerts
            $paymentDue = $db->query("
                SELECT id, invoice_no, due_amount, customer_id, due_date
                FROM invoices 
                WHERE due_amount > 0 AND due_date <= CURDATE() AND status != 'PAID' AND deleted_at IS NULL
                LIMIT 5
            ")->fetchAll();

            foreach ($paymentDue as $p) {
                $alerts[] = [
                    'id' => 'payment_' . $p['id'],
                    'title' => 'Payment Due',
                    'message' => 'Invoice ' . $p['invoice_no'] . ' has a due amount of ₹' . (float)$p['due_amount'],
                    'type' => 'PAYMENT',
                    'created_at' => date('Y-m-d H:i:s'),
                    'is_read' => 0
                ];
            }

            // 1b. Backup Failed Alerts
            $backupFailed = $db->query("
                SELECT id, backup_date
                FROM backup_logs 
                WHERE status = 'FAILED' AND deleted_at IS NULL
                LIMIT 2
            ")->fetchAll();

            foreach ($backupFailed as $b) {
                $alerts[] = [
                    'id' => 'backup_' . $b['id'],
                    'title' => 'Backup Failed',
                    'message' => 'System backup failed on ' . $b['backup_date'],
                    'type' => 'SYSTEM',
                    'created_at' => date('Y-m-d H:i:s'),
                    'is_read' => 0
                ];
            }

            // 1c. GST Due / Today's Reminder (Virtual/Mock Alerts)
            $dayOfMonth = (int)date('d');
            if ($dayOfMonth >= 15 && $dayOfMonth <= 20) {
                $alerts[] = [
                    'id' => 'gst_due_1',
                    'title' => 'GST Due',
                    'message' => 'GST filing is due soon. Please prepare your GSTR reports.',
                    'type' => 'TAX',
                    'created_at' => date('Y-m-d H:i:s'),
                    'is_read' => 0
                ];
            }
            
            $alerts[] = [
                'id' => 'reminder_' . date('Ymd'),
                'title' => 'Today\'s Reminder',
                'message' => 'Please review today\'s pending tasks and follow up with overdue payments.',
                'type' => 'INFO',
                'created_at' => date('Y-m-d H:i:s'),
                'is_read' => 0
            ];

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
        if (!Helpers::verifyCsrf()) {
            Helpers::jsonResponse(false, "CSRF verification failed.");
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
        if (!Helpers::verifyCsrf()) {
            Helpers::jsonResponse(false, "CSRF verification failed.");
        }
        $id = $_POST['id'] ?? '';

        // Virtual alerts (low stock, payment due, backup failed, GST due,
        // today's reminder) aren't real rows - they're recomputed fresh from
        // live data on every list() call, so there's nothing to persist as
        // "read." Just acknowledge so the UI can dismiss it for this view.
        if (empty($id) || !ctype_digit((string)$id)) {
            Helpers::jsonResponse(true, "Alert dismissed.");
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
