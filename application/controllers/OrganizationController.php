<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Organization Controller (Super Admin only)
 */
namespace App\Controllers;

use App\Models\Auth;
use App\Models\Database;

class OrganizationController {
    protected $db;
    protected $auth;

    public function __construct($db, $auth) {
        $this->db = $db;
        $this->auth = $auth;
    }

    public function index() {
        if ($_SESSION['user_id'] != 1) {
            header("Location: " . BASE_URL . "/index?error=unauthorized");
            exit;
        }
        
        $orgs = $this->db->query("
            SELECT o.*, p.plan_name 
            FROM organizations o 
            LEFT JOIN plans p ON o.plan_id = p.id 
            ORDER BY o.id ASC
        ")->fetchAll();
        
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/organizations/list.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function form() {
        if ($_SESSION['user_id'] != 1) {
            header("Location: " . BASE_URL . "/index?error=unauthorized");
            exit;
        }
        
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $isDemo = isset($_GET['demo']) && $_GET['demo'] == '1';
        $db = $isDemo ? new Database('demo') : $this->db;
        $org = null;

        if ($id > 0) {
            $org = $db->query("SELECT * FROM organizations WHERE id = ?", [$id])->fetch();
            if (!$org) {
                die("Organization not found.");
            }
        }
        
        $plans = $db->query("SELECT * FROM plans")->fetchAll();

        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/organizations/form.php';
        require_once __DIR__ . '/../views/footer.php';
    }
}
