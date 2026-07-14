<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Supplier Controller
 */
namespace App\Controllers;

use App\Models\Auth;
use App\Models\Database;
use App\Models\Helpers;




class SupplierController {
    protected $db;
    protected $auth;

    public function __construct($db, $auth) {
        $this->db = $db;
        $this->auth = $auth;
    }

    public function index() {
        $this->auth->requirePermission('Manage Suppliers');
        
        // Load layout views
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/suppliers/list.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function form($id = null) {
        $this->auth->requirePermission('Manage Suppliers');
        
        $supplier = null;
        if ($id > 0) {
            $db = $this->db;
            $supplier = $db->query("SELECT * FROM suppliers WHERE id = ? LIMIT 1", [(int)$id])->fetch();
        }
        
        // Load layout views
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/suppliers/form.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function view($id) {
        $this->auth->requirePermission('Manage Suppliers');
        
        $db = $this->db;
        $supplier = $db->query("SELECT * FROM suppliers WHERE id = ? LIMIT 1", [(int)$id])->fetch();
        
        if (!$supplier) {
            header("Location: " . BASE_URL . "/suppliers/index");
            exit;
        }
        
        // Load layout views
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/suppliers/view.php';
        require_once __DIR__ . '/../views/footer.php';
    }
}
