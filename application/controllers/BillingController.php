<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Billing Controller
 */
namespace App\Controllers;

use App\Models\Auth;
use App\Models\Database;
use App\Models\Helpers;




class BillingController {
    protected $db;
    protected $auth;

    public function __construct($db, $auth) {
        $this->db = $db;
        $this->auth = $auth;
    }

    public function index() {
        $this->auth->requirePermission('Create Invoice');
        
        // Load layout views
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/billing/index.php'; // lists invoices or goes to POS
        require_once __DIR__ . '/../views/footer.php';
    }

    public function form($id = null) {
        $this->auth->requirePermission('Create Invoice');
        
        // POS Terminal form is the form.php
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/billing/form.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function dayEnd() {
        $this->auth->requirePermission('Create Invoice');
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/billing/day_end.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function view($id) {
        $this->auth->requirePermission('Create Invoice');
        
        $db = $this->db;
        $invoice = $db->query("
            SELECT i.*, c.customer_name, c.mobile as customer_mobile, c.gst_number as customer_gst, c.address as customer_address, u.name as cashier_name
            FROM invoices i
            LEFT JOIN customers c ON i.customer_id = c.id
            LEFT JOIN users u ON i.created_by = u.id
            WHERE i.id = ? LIMIT 1
        ", [(int)$id])->fetch();
        
        if (!$invoice) {
            header("Location: " . BASE_URL . "/billing/index.php");
            exit;
        }

        $items = $db->query("
            SELECT ii.*, p.product_name, p.sku, p.hsn_code, un.short_name as unit_name
            FROM invoice_items ii
            JOIN products p ON ii.product_id = p.id
            LEFT JOIN units un ON p.unit_id = un.id
            WHERE ii.invoice_id = ?
        ", [(int)$id])->fetchAll();
        
        $company = $db->query("SELECT * FROM company_settings WHERE id = 1 LIMIT 1")->fetch();

        // Load layout views
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/billing/view.php';
        require_once __DIR__ . '/../views/footer.php';
    }
}
