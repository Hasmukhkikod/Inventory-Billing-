<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Settings Controller
 */
namespace App\Controllers;

use App\Models\Auth;
use App\Models\Database;
use App\Models\Helpers;




class SettingsController {
    protected $db;
    protected $auth;

    public function __construct($db, $auth) {
        $this->db = $db;
        $this->auth = $auth;
    }

    public function index() {
        $this->auth->requirePermission('Manage Settings');
        
        // Load layout views
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/settings/index.php';
        require_once __DIR__ . '/../views/footer.php';
    }
}
