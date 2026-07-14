<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Quotation & Estimate Controller
 */
namespace App\Controllers;

use App\Models\Auth;
use App\Models\Database;
use App\Models\Helpers;




class QuotationController {
    protected $db;
    protected $auth;

    public function __construct($db, $auth) {
        $this->db = $db;
        $this->auth = $auth;
    }

    public function index() {
        $this->auth->requirePermission('Manage Quotations');

        // Load layout views
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/quotations/list.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function form($id = null) {
        $this->auth->requirePermission('Manage Quotations');

        $quotation = null;
        $items = [];
        if (isset($_GET['id']) && (int)$_GET['id'] > 0) {
            $quotation = $this->db->query("SELECT * FROM quotations WHERE id = ? LIMIT 1", [(int)$_GET['id']])->fetch();
            if ($quotation) {
                $items = $this->db->query("
                    SELECT qi.*, p.product_name, p.sku
                    FROM quotation_items qi
                    JOIN products p ON qi.product_id = p.id
                    WHERE qi.quotation_id = ? AND qi.deleted_at IS NULL
                ", [(int)$_GET['id']])->fetchAll();
            }
        }

        // Load layout views
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/quotations/form.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function view($id) {
        $this->auth->requirePermission('Manage Quotations');

        $db = $this->db;
        $quotation = $db->query("
            SELECT q.*, c.customer_name, c.mobile as customer_mobile, c.gst_number as customer_gst, c.address as customer_address, u.name as created_by_name
            FROM quotations q
            LEFT JOIN customers c ON q.customer_id = c.id
            LEFT JOIN users u ON q.created_by = u.id
            WHERE q.id = ? LIMIT 1
        ", [(int)$id])->fetch();

        if (!$quotation) {
            header("Location: " . BASE_URL . "/quotations/index");
            exit;
        }

        $items = $db->query("
            SELECT qi.*, p.product_name, p.sku, un.short_name as unit_name,
                   COALESCE(qi.billing_unit_name, un.short_name, 'Pcs') as display_unit, qi.primary_qty
            FROM quotation_items qi
            JOIN products p ON qi.product_id = p.id
            LEFT JOIN units un ON p.unit_id = un.id
            WHERE qi.quotation_id = ? AND qi.deleted_at IS NULL
        ", [(int)$id])->fetchAll();

        $company = $db->query("SELECT * FROM company_settings WHERE id = 1 LIMIT 1")->fetch();

        // Load layout views
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/quotations/view.php';
        require_once __DIR__ . '/../views/footer.php';
    }
}
