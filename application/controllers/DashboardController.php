<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Dashboard Controller
 */
namespace App\Controllers;

use App\Models\Auth;

class DashboardController {
    protected $db;
    protected $auth;

    public function __construct($db, $auth) {
        $this->db = $db;
        $this->auth = $auth;
    }

    public function index() {
        $this->auth->requirePermission('Access Dashboard');
        
        // Load layout and main view
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/dashboard.php';
        require_once __DIR__ . '/../views/footer.php';
    }
}
