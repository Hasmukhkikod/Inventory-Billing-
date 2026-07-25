<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Plan Controller (Super Admin only)
 */
namespace App\Controllers;

use App\Models\Auth;
use App\Models\Database;

class PlanController {
    protected $db;
    protected $auth;

    public function __construct($db, $auth) {
        $this->db = $db;
        $this->auth = $auth;
    }

    public function index() {
        // Only Super Admin can access (id = 1, org_id = 0)
        if ($_SESSION['user_id'] != 1) {
            header("Location: " . BASE_URL . "/index?error=unauthorized");
            exit;
        }
        
        $plans = $this->db->query("SELECT * FROM plans ORDER BY id ASC")->fetchAll();
        
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/plans/list.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function form() {
        if ($_SESSION['user_id'] != 1) {
            header("Location: " . BASE_URL . "/index?error=unauthorized");
            exit;
        }
        
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $plan = null;

        if ($id > 0) {
            $plan = $this->db->query("SELECT * FROM plans WHERE id = ?", [$id])->fetch();
            if (!$plan) {
                die("Plan not found.");
            }
        }

        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/plans/form.php';
        require_once __DIR__ . '/../views/footer.php';
    }
}
