<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Product Inventory Controller
 */
namespace App\Controllers;

use App\Models\Auth;
use App\Models\Database;
use App\Models\Helpers;




class ProductController {
    protected $db;
    protected $auth;

    public function __construct($db, $auth) {
        $this->db = $db;
        $this->auth = $auth;
    }

    public function index() {
        $this->auth->requirePermission('Manage Inventory');
        
        // Load layout views
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/products/list.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function form($id = null) {
        $this->auth->requirePermission('Manage Inventory');
        
        $product = null;
        if ($id > 0) {
            $db = $this->db;
            $product = $db->query("SELECT * FROM products WHERE id = ? LIMIT 1", [(int)$id])->fetch();
        }
        
        // Load layout views
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/products/form.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function view($id) {
        $this->auth->requirePermission('Manage Inventory');
        
        $db = $this->db;
        $product = $db->query("
            SELECT p.*, c.category_name, b.brand_name, u.unit_name 
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN units u ON p.unit_id = u.id
            WHERE p.id = ? LIMIT 1
        ", [(int)$id])->fetch();
        
        if (!$product) {
            header("Location: " . BASE_URL . "/products/index.php");
            exit;
        }

        // Fetch stock ledger transactions
        $transactions = $db->query("
            SELECT st.*, u.name as creator_name
            FROM stock_transactions st
            LEFT JOIN users u ON st.created_by = u.id
            WHERE st.product_id = ? AND st.status = 'ACTIVE' AND st.deleted_at IS NULL
            ORDER BY st.created_at DESC
        ", [(int)$id])->fetchAll();
        
        // Load layout views
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/products/view.php';
        require_once __DIR__ . '/../views/footer.php';
    }
}
