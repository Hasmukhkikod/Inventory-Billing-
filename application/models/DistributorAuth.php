<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Distributor/Partner Portal Authentication
 *
 * Deliberately separate from Auth.php: distributors are not org staff and
 * are not tied to any single org_id, so they don't fit the users/roles/
 * permissions model the main app uses. This is a lightweight, parallel
 * session (`$_SESSION['distributor_id']`), the same way `$_SESSION['is_demo']`
 * exists alongside the main org session shape without touching it.
 */
namespace App\Models;

class DistributorAuth {
    private Database $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function login(string $email, string $password): bool {
        $stmt = $this->db->query("SELECT * FROM distributors WHERE (email = ? OR mobile = ?) AND deleted_at IS NULL LIMIT 1", [$email, $email]);
        $distributor = $stmt->fetch();

        if (!$distributor || !password_verify($password, $distributor['password'])) {
            return false;
        }

        if (!$distributor['is_verified']) {
            return false;
        }

        if ($distributor['status'] !== 'ACTIVE') {
            return false;
        }

        $_SESSION['distributor_id'] = $distributor['id'];
        $_SESSION['distributor_name'] = $distributor['name'];
        $_SESSION['distributor_email'] = $distributor['email'];
        $_SESSION['distributor_last_activity'] = time();

        return true;
    }

    public function check(): bool {
        if (!isset($_SESSION['distributor_id'])) {
            return false;
        }
        if (isset($_SESSION['distributor_last_activity']) && (time() - $_SESSION['distributor_last_activity'] > 1800)) {
            $this->logout();
            return false;
        }
        $_SESSION['distributor_last_activity'] = time();
        return true;
    }

    public function user(): ?array {
        if (!$this->check()) {
            return null;
        }
        $stmt = $this->db->query("SELECT id, name, business_name, email, mobile, commission_rate, status FROM distributors WHERE id = ? LIMIT 1", [$_SESSION['distributor_id']]);
        return $stmt->fetch() ?: null;
    }

    public function logout(): void {
        unset($_SESSION['distributor_id'], $_SESSION['distributor_name'], $_SESSION['distributor_email'], $_SESSION['distributor_last_activity']);
    }

    public function requireLogin(): void {
        if (!$this->check()) {
            header("Location: " . BASE_URL . "/partner/login");
            exit;
        }
    }
}
