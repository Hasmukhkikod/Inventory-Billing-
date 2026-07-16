<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Feedback API (Floating Help widget)
 */

require_once __DIR__ . '/../vendor/autoload.php';
use App\Models\Auth;
use App\Models\Helpers;
use App\Models\Database;

require_once __DIR__ . '/../config/database.php';

$db = new Database();
$auth = new Auth($db);
if (!$auth->check()) Helpers::jsonResponse(false, 'Session expired. Please log in again.');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'save':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, "Invalid method");
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, "CSRF verification failed.");

        $message = trim($_POST['message'] ?? '');
        $pageUrl = trim($_POST['page_url'] ?? '');
        if (empty($message)) {
            Helpers::jsonResponse(false, "Please enter your feedback before sending.");
        }

        try {
            $db->insert("
                INSERT INTO feedback (user_id, message, page_url) VALUES (?, ?, ?)
            ", [$_SESSION['user_id'] ?? null, $message, $pageUrl ?: null]);
            Helpers::jsonResponse(true, "Feedback sent. Thank you!");
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed to save feedback: " . $e->getMessage());
        }
        break;

    case 'list':
        $auth->requirePermission('Manage Settings');
        try {
            $rows = $db->query("
                SELECT f.*, u.name as user_name
                FROM feedback f LEFT JOIN users u ON f.user_id = u.id
                ORDER BY f.created_at DESC
            ")->fetchAll();
            Helpers::jsonResponse(true, "Feedback list", ['feedback' => $rows]);
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed to load feedback: " . $e->getMessage());
        }
        break;

    case 'mark_read':
        $auth->requirePermission('Manage Settings');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, "Invalid method");
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, "CSRF verification failed.");
        $id = (int)($_POST['id'] ?? 0);
        try {
            $db->query("UPDATE feedback SET is_read = 1 WHERE id = ?", [$id]);
            Helpers::jsonResponse(true, "Marked as read.");
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed to update: " . $e->getMessage());
        }
        break;

    case 'delete':
        $auth->requirePermission('Manage Settings');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, "Invalid method");
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, "CSRF verification failed.");
        $id = (int)($_POST['id'] ?? 0);
        try {
            $db->query("DELETE FROM feedback WHERE id = ?", [$id]);
            Helpers::jsonResponse(true, "Feedback deleted.");
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed to delete: " . $e->getMessage());
        }
        break;

    default:
        Helpers::jsonResponse(false, "Invalid action.");
}
