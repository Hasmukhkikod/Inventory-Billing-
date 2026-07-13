<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Helpers & Global Utilities
 */
namespace App\Models;

require_once __DIR__ . '/../../config/database.php';

class Helpers {
    /**
     * Sanitize inputs to prevent XSS
     */
    public static function sanitize($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sanitize($value);
            }
        } else {
            $data = trim($data);
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }
        return $data;
    }

    /**
     * Generate HTML CSRF field
     */
    public static function csrfField(): string {
        return '<input type="hidden" name="csrf_token" value="' . self::getCsrfToken() . '">';
    }

    /**
     * Get or generate CSRF token
     */
    public static function getCsrfToken(): string {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCsrf(): bool {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Output standard JSON structure and exit
     */
    public static function jsonResponse(bool $status, string $message, array $data = []): void {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }

    /**
     * Format currency as Indian Rupee (INR)
     */
    public static function formatCurrency($amount): string {
        return '₹' . number_format((float)$amount, 2, '.', ',');
    }

    /**
     * Format date
     */
    public static function formatDate(string $date, string $format = 'd-M-Y'): string {
        return date($format, strtotime($date));
    }

    /**
     * Cache-busting version string for a local asset (e.g. '/assets/js/billing.js')
     * so browsers fetch the latest file immediately after a deploy instead of
     * serving a stale cached copy indefinitely.
     */
    public static function assetVersion(string $relativePath): int {
        $path = BASE_DIR . $relativePath;
        return file_exists($path) ? filemtime($path) : time();
    }

    /**
     * Audit logger matching Part 2 columns
     */
    public static function logActivity(Database $db, string $module, string $action, ?int $recordId = null): bool {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $device = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            
            $db->insert("
                INSERT INTO activity_logs (user_id, module, action, record_id, ip_address, device, status, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, 'ACTIVE', ?)
            ", [
                $userId, $module, $action, $recordId, $ip, substr($device, 0, 150), $userId
            ]);
            return true;
        } catch (Exception $e) {
            // Write to error log folder
            error_log("Failed to log activity: " . $e->getMessage());
            return false;
        }
    }
}
