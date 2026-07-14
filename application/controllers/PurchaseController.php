<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Purchase Inventory Controller
 */
namespace App\Controllers;

use App\Models\Auth;
use App\Models\Database;
use App\Models\Helpers;




class PurchaseController {
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
        require_once __DIR__ . '/../views/purchases/list.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function form($id = null) {
        $this->auth->requirePermission('Manage Inventory');
        
        $purchase = null;
        if ($id > 0) {
            $db = $this->db;
            $purchase = $db->query("SELECT * FROM purchases WHERE id = ? LIMIT 1", [(int)$id])->fetch();
        }
        
        // Load layout views
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/purchases/form.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function view($id) {
        $this->auth->requirePermission('Manage Inventory');
        
        $db = $this->db;
        $purchase = $db->query("
            SELECT p.*, s.supplier_name, s.mobile as supplier_mobile, s.gst_number as supplier_gst, s.address as supplier_address, u.name as creator_name
            FROM purchases p
            LEFT JOIN suppliers s ON p.supplier_id = s.id
            LEFT JOIN users u ON p.created_by = u.id
            WHERE p.id = ? LIMIT 1
        ", [(int)$id])->fetch();
        
        if (!$purchase) {
            header("Location: " . BASE_URL . "/purchases/index");
            exit;
        }

        $items = $db->query("
            SELECT pi.*, p.product_name, p.sku, un.short_name as unit_name,
                   COALESCE(pi.billing_unit_name, un.short_name, 'Pcs') as display_unit, pi.primary_qty
            FROM purchase_items pi
            JOIN products p ON pi.product_id = p.id
            LEFT JOIN units un ON p.unit_id = un.id
            WHERE pi.purchase_id = ?
        ", [(int)$id])->fetchAll();

        // Load layout views
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/purchases/view.php';
        require_once __DIR__ . '/../views/footer.php';
    }
}
