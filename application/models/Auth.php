<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Authentication & RBAC Model
 */
namespace App\Models;

class Auth {
    private Database $db;
    private ?array $currentUser = null;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    /**
     * Authenticate user by email and password
     */
    public function login(string $email, string $password): bool {
        $stmt = $this->db->query("
            SELECT u.*, r.role_name 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            WHERE u.email = ? LIMIT 1
        ", [$email]);
        $user = $stmt->fetch();

        if ($user && $user['status'] === 'ACTIVE' && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['name'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['role_name'] = $user['role_name'];
            $_SESSION['last_activity'] = time();
            
            $this->currentUser = $user;
            
            // Log login event
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $browserRaw = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            
            $friendlyBrowser = 'Unknown Browser';
            if (preg_match('/chrome|crios/i', $browserRaw)) {
                $friendlyBrowser = 'Google Chrome';
            } elseif (preg_match('/firefox|iceweasel|fxios/i', $browserRaw)) {
                $friendlyBrowser = 'Mozilla Firefox';
            } elseif (preg_match('/safari/i', $browserRaw) && !preg_match('/chrome|crios/i', $browserRaw)) {
                $friendlyBrowser = 'Apple Safari';
            } elseif (preg_match('/msie|trident/i', $browserRaw)) {
                $friendlyBrowser = 'Internet Explorer';
            } elseif (preg_match('/edge|edg/i', $browserRaw)) {
                $friendlyBrowser = 'Microsoft Edge';
            }
            
            $friendlyDevice = 'Desktop';
            if (preg_match('/mobile|phone|ipod|iphone|android|blackberry|iemobile/i', $browserRaw)) {
                $friendlyDevice = 'Mobile';
            } elseif (preg_match('/ipad|tablet|playbook|silk/i', $browserRaw)) {
                $friendlyDevice = 'Tablet';
            }
            $browser = $friendlyBrowser . ' (' . $friendlyDevice . ')';
            
            $this->db->insert("
                INSERT INTO login_logs (user_id, login_time, ip_address, browser, status) 
                VALUES (?, CURRENT_TIMESTAMP, ?, ?, 'SUCCESS')
            ", [$user['id'], $ip, substr($browser, 0, 100)]);

            Helpers::logActivity($this->db, "auth", "User login successful", $user['id']);
            return true;
        }

        Helpers::logActivity($this->db, "auth", "Failed login attempt for email: " . $email);
        return false;
    }

    /**
     * Terminate user session
     */
    public function logout(): void {
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            
            // Update last logout log (subquery for SQLite compatibility)
            $this->db->query("
                UPDATE login_logs
                SET logout_time = CURRENT_TIMESTAMP
                WHERE id = (SELECT id FROM login_logs WHERE user_id = ? AND logout_time IS NULL ORDER BY login_time DESC LIMIT 1)
            ", [$userId]);

            Helpers::logActivity($this->db, "auth", "User logout", $userId);
        }
        
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    /**
     * Check if user session is active (with 30-min timeout)
     */
    public function check(): bool {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
            $this->logout();
            return false;
        }
        $_SESSION['last_activity'] = time();
        return true;
    }

    /**
     * Get current user details
     */
    public function user(): ?array {
        if (!$this->check()) {
            return null;
        }
        if ($this->currentUser === null) {
            $stmt = $this->db->query("
                SELECT u.*, r.role_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                WHERE u.id = ? LIMIT 1
            ", [$_SESSION['user_id']]);
            $this->currentUser = $stmt->fetch() ?: null;
        }
        return $this->currentUser;
    }

    /**
     * Verify role permission
     */
    public function hasPermission(string $permissionName): bool {
        if (!$this->check()) {
            return false;
        }
        
        $roleId = $_SESSION['role_id'] ?? null;
        if (!$roleId) {
            return false;
        }

        // Super Admin (role_id = 1) always has all permissions
        if ($roleId == 1) {
            return true;
        }

        $stmt = $this->db->query("
            SELECT COUNT(*) as allowed 
            FROM role_permissions rp
            JOIN permissions p ON rp.permission_id = p.id
            WHERE rp.role_id = ? AND p.permission_name = ? AND rp.status = 'ACTIVE' AND p.status = 'ACTIVE'
        ", [$roleId, $permissionName]);
        
        $res = $stmt->fetch();
        return isset($res['allowed']) && $res['allowed'] > 0;
    }

    /**
     * Restrict page to verified permission
     */
    public function requirePermission(string $permissionName): void {
        if (!$this->check()) {
            if ($this->isAjaxRequest()) {
                Helpers::jsonResponse(false, "Session expired. Please log in again.");
            } else {
                header("Location: login.php");
                exit;
            }
        }

        if (!$this->hasPermission($permissionName)) {
            if ($this->isAjaxRequest()) {
                header('HTTP/1.1 403 Forbidden');
                Helpers::jsonResponse(false, "Unauthorized: You do not have permissions.");
            } else {
                header("Location: index.php?error=unauthorized");
                exit;
            }
        }
    }

    private function isAjaxRequest(): bool {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') 
               || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);
    }
}
