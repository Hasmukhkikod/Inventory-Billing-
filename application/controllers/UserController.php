<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * User Management Controller
 */
namespace App\Controllers;

use App\Models\Auth;
use App\Models\Database;
use App\Models\Helpers;




class UserController {
    protected $db;
    protected $auth;

    public function __construct($db, $auth) {
        $this->db = $db;
        $this->auth = $auth;
    }

    public function index() {
        $this->auth->requirePermission('Manage Users');
        
        // Load layout views
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/users/list.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function form($id = null) {
        $this->auth->requirePermission('Manage Users');
        
        $user = null;
        if ($id > 0) {
            $db = $this->db;
            $user = $db->query("SELECT * FROM users WHERE id = ? LIMIT 1", [(int)$id])->fetch();
        }
        
        // Load layout views
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/users/form.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function view($id) {
        $this->auth->requirePermission('Manage Users');
        
        $db = $this->db;
        $user = $db->query("
            SELECT u.*, r.role_name 
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.id = ? LIMIT 1
        ", [(int)$id])->fetch();
        
        if (!$user) {
            header("Location: " . BASE_URL . "/users/index.php");
            exit;
        }
        
        // Load layout views
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/users/view.php';
        require_once __DIR__ . '/../views/footer.php';
    }
}
