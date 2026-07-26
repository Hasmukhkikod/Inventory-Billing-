<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * API - Announcements
 */
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../application/Models/Database.php';
require_once __DIR__ . '/../application/Models/Auth.php';
require_once __DIR__ . '/../application/Models/Helpers.php';

use App\Models\Database;
use App\Models\Helpers;
use App\Models\Auth;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = new Database();
$auth = new Auth($db);

if (!$auth->check() || $_SESSION['user_id'] != 1) {
    Helpers::jsonResponse(false, 'Unauthorized. Super Admin access required.', null, 403);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Helpers::verifyCsrf();
    
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create' || $action === 'edit') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $title = trim($_POST['title'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $display_type = $_POST['display_type'] ?? 'banner';
        $location = $_POST['location'] ?? 'all_pages';
        $start_time = !empty($_POST['start_time']) ? $_POST['start_time'] : null;
        $end_time = !empty($_POST['end_time']) ? $_POST['end_time'] : null;
        $duration_seconds = isset($_POST['duration_seconds']) ? (int)$_POST['duration_seconds'] : 0;
        
        $modal_size = $_POST['modal_size'] ?? 'md';
        $show_frequency = $_POST['show_frequency'] ?? 'always';
        $frequency_minutes = isset($_POST['frequency_minutes']) ? (int)$_POST['frequency_minutes'] : 0;
        
        if (empty($title) || empty($message)) {
            Helpers::jsonResponse(false, 'Title and Message are required.');
        }
        
        try {
            if ($action === 'create') {
                $db->insert(
                    "INSERT INTO system_announcements (title, message, display_type, location, start_time, end_time, duration_seconds, modal_size, show_frequency, frequency_minutes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [$title, $message, $display_type, $location, $start_time, $end_time, $duration_seconds, $modal_size, $show_frequency, $frequency_minutes]
                );
                Helpers::jsonResponse(true, 'Announcement created successfully!');
            } else {
                $db->query(
                    "UPDATE system_announcements SET title = ?, message = ?, display_type = ?, location = ?, start_time = ?, end_time = ?, duration_seconds = ?, modal_size = ?, show_frequency = ?, frequency_minutes = ? WHERE id = ?",
                    [$title, $message, $display_type, $location, $start_time, $end_time, $duration_seconds, $modal_size, $show_frequency, $frequency_minutes, $id]
                );
                Helpers::jsonResponse(true, 'Announcement updated successfully!');
            }
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Error: " . $e->getMessage());
        }
    }
    
    if ($action === 'delete') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        try {
            $db->query("DELETE FROM system_announcements WHERE id = ?", [$id]);
            Helpers::jsonResponse(true, 'Announcement deleted successfully!');
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Error: " . $e->getMessage());
        }
    }
    
    if ($action === 'toggle_status') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $status = $_POST['status'] === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
        try {
            $db->query("UPDATE system_announcements SET status = ? WHERE id = ?", [$status, $id]);
            Helpers::jsonResponse(true, 'Status updated!', ['new_status' => $status]);
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Error: " . $e->getMessage());
        }
    }
}

Helpers::jsonResponse(false, 'Invalid action.');
