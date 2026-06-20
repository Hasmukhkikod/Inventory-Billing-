<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Reports Controller
 */
namespace App\Controllers;

use App\Models\Auth;
use App\Models\Database;
use App\Models\Helpers;




class ReportsController {
    protected $db;
    protected $auth;

    public function __construct($db, $auth) {
        $this->db = $db;
        $this->auth = $auth;
    }

    public function index() {
        $this->auth->requirePermission('View Reports');
        
        // Load layout views
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/reports/index.php';
        require_once __DIR__ . '/../views/footer.php';
    }
}
