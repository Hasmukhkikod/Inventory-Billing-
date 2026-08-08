<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Partner Leads Controller (Super Admin only)
 */
namespace App\Controllers;

use App\Models\Auth;
use App\Models\Database;

class LeadController {
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

        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/leads/list.php';
        require_once __DIR__ . '/../views/footer.php';
    }
}
