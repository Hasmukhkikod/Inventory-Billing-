<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Customer Controller
 */
namespace App\Controllers;

use App\Models\Auth;
use App\Models\Database;
use App\Models\Helpers;




class CustomerController {
    protected $db;
    protected $auth;

    public function __construct($db, $auth) {
        $this->db = $db;
        $this->auth = $auth;
    }

    public function index() {
        $this->auth->requirePermission('Manage Customers');
        
        // Load layout views
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/customers/list.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function form($id = null) {
        $this->auth->requirePermission('Manage Customers');
        
        $customer = null;
        if ($id > 0) {
            $db = $this->db;
            $customer = $db->query("SELECT * FROM customers WHERE id = ? LIMIT 1", [(int)$id])->fetch();
        }
        
        // Load layout views
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/customers/form.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function view($id) {
        $this->auth->requirePermission('Manage Customers');
        
        $db = $this->db;
        $customer = $db->query("SELECT * FROM customers WHERE id = ? LIMIT 1", [(int)$id])->fetch();
        
        if (!$customer) {
            header("Location: " . BASE_URL . "/customers/index.php");
            exit;
        }
        
        // Load layout views
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/customers/view.php';
        require_once __DIR__ . '/../views/footer.php';
    }
}
