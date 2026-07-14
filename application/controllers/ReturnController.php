<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Return Orders Controller
 */
namespace App\Controllers;

use App\Models\Auth;
use App\Models\Database;
use App\Models\Helpers;




class ReturnController {
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
        require_once __DIR__ . '/../views/returns/list.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function form($id = null) {
        $this->auth->requirePermission('Manage Inventory');
        
        $return = null;
        $type = $_GET['type'] ?? 'SALES'; // SALES or PURCHASE
        
        // Load layout views
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/returns/form.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function view($id) {
        $this->auth->requirePermission('Manage Inventory');
        
        $type = $_GET['type'] ?? 'SALES'; // SALES or PURCHASE
        $db = $this->db;
        $return = null;
        $items = [];
        
        if ($type === 'SALES') {
            $return = $db->query("
                SELECT sr.*, i.invoice_no, c.customer_name, c.mobile as customer_mobile, u.name as creator_name
                FROM sales_returns sr
                LEFT JOIN invoices i ON sr.invoice_id = i.id
                LEFT JOIN customers c ON sr.customer_id = c.id
                LEFT JOIN users u ON sr.created_by = u.id
                WHERE sr.id = ? LIMIT 1
            ", [(int)$id])->fetch();
            
            if ($return) {
                $items = $db->query("
                    SELECT sri.*, p.product_name, p.sku, un.short_name as unit_name
                    FROM sales_return_items sri
                    JOIN products p ON sri.product_id = p.id
                    LEFT JOIN units un ON p.unit_id = un.id
                    WHERE sri.sales_return_id = ?
                ", [(int)$id])->fetchAll();
            }
        } else {
            $return = $db->query("
                SELECT pr.*, pur.purchase_no, s.supplier_name, s.mobile as supplier_mobile, u.name as creator_name
                FROM purchase_returns pr
                LEFT JOIN purchases pur ON pr.purchase_id = pur.id
                LEFT JOIN suppliers s ON pr.supplier_id = s.id
                LEFT JOIN users u ON pr.created_by = u.id
                WHERE pr.id = ? LIMIT 1
            ", [(int)$id])->fetch();
            
            if ($return) {
                $items = $db->query("
                    SELECT pri.*, p.product_name, p.sku, un.short_name as unit_name
                    FROM purchase_return_items pri
                    JOIN products p ON pri.product_id = p.id
                    LEFT JOIN units un ON p.unit_id = un.id
                    WHERE pri.purchase_return_id = ?
                ", [(int)$id])->fetchAll();
            }
        }
        
        if (!$return) {
            header("Location: " . BASE_URL . "/returns/index");
            exit;
        }

        // Load layout views
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/returns/view.php';
        require_once __DIR__ . '/../views/footer.php';
    }
}
