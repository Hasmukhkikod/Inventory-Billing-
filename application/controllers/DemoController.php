<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Demo Controller (Super Admin only)
 */
namespace App\Controllers;

use App\Models\Auth;
use App\Models\Database;

class DemoController {
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
        
        $demoDb = new Database('demo');
        
        $orgs = $demoDb->query("
            SELECT o.*, p.plan_name 
            FROM organizations o 
            LEFT JOIN plans p ON o.plan_id = p.id 
            ORDER BY o.id ASC
        ")->fetchAll();
        
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/demos/list.php';
        require_once __DIR__ . '/../views/footer.php';
    }
}
