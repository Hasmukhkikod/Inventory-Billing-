<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Helpers & Global Utilities
 */
namespace App\Models;

require_once __DIR__ . '/../../config/database.php';

class Helpers {
    private static $translations = [];
    private static $currentLang = 'en';

    /**
     * Initialize language dictionaries
     */
    public static function initLanguage($langCode = 'en') {
        self::$currentLang = $langCode;
        $langFile = __DIR__ . '/../../application/lang/' . $langCode . '.json';
        if (file_exists($langFile)) {
            $json = file_get_contents($langFile);
            self::$translations = json_decode($json, true) ?: [];
        } else {
            self::$translations = [];
        }
    }

    public static function getCurrentLang() {
        return self::$currentLang;
    }

    /**
     * Translate a string
     */
    public static function translate($key) {
        if (isset(self::$translations[$key])) {
            return self::$translations[$key];
        }
        return $key;
    }
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

    /**
     * Generate a beautiful responsive HTML email template
     */
    public static function getEmailTemplate(string $title, string $content): string {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f4f7fa; color: #333333; margin: 0; padding: 0; }
                .wrapper { width: 100%; table-layout: fixed; background-color: #f4f7fa; padding-bottom: 60px; }
                .main { background-color: #ffffff; margin: 0 auto; width: 100%; max-width: 600px; border-spacing: 0; color: #333333; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-top: 40px; }
                .header { padding: 30px; text-align: center; background-color: #ffffff; border-bottom: 1px solid #eeeeee; }
                .header h1 { margin: 0; color: #12214f; font-size: 24px; letter-spacing: -0.5px; }
                .content { padding: 40px 40px 30px; font-size: 16px; line-height: 1.6; }
                .footer { padding: 20px; text-align: center; font-size: 13px; color: #999999; background-color: #fbfbfb; border-top: 1px solid #eeeeee; }
                .btn { display: inline-block; padding: 14px 28px; background-color: #3b5bff; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 15px; text-align: center; margin-top: 10px; margin-bottom: 20px; box-shadow: 0 4px 10px rgba(59,91,255,0.3); }
                .btn-container { text-align: center; margin: 30px 0; }
                .small-link { font-size: 12px; color: #999999; word-break: break-all; }
                h2 { color: #12214f; margin-top: 0; }
            </style>
        </head>
        <body>
            <center class="wrapper">
                <table class="main" width="100%">
                    <tr>
                        <td class="header">
                            <h1>Grovixo</h1>
                        </td>
                    </tr>
                    <tr>
                        <td class="content">
                            ' . $content . '
                        </td>
                    </tr>
                    <tr>
                        <td class="footer">
                            &copy; ' . date("Y") . ' Grovixo. All rights reserved.<br>
                            If you did not request this, please ignore this email.
                        </td>
                    </tr>
                </table>
            </center>
        </body>
        </html>';
    }
}
