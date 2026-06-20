<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Delivery Challan Controller
 */
namespace App\Controllers;

use App\Models\Auth;
use App\Models\Database;
use App\Models\Helpers;




class ChallanController {
    protected $db;
    protected $auth;

    public function __construct($db, $auth) {
        $this->db = $db;
        $this->auth = $auth;
    }

    public function index() {
        $this->auth->requirePermission('Manage Challans');

        // Load layout views
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/challans/list.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function form($id = null) {
        $this->auth->requirePermission('Manage Challans');

        $challan = null;
        $items = [];
        $editId = isset($_GET['id']) ? (int)$_GET['id'] : ($id ? (int)$id : 0);
        if ($editId > 0) {
            $db = $this->db;
            $challan = $db->query("SELECT * FROM challans WHERE id = ? LIMIT 1", [$editId])->fetch();
            if ($challan) {
                $items = $db->query("SELECT ci.*, p.product_name, p.sku FROM challan_items ci JOIN products p ON ci.product_id = p.id WHERE ci.challan_id = ?", [$editId])->fetchAll();
            }
        }

        // Load layout views
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/challans/form.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function view($id) {
        $this->auth->requirePermission('Manage Challans');

        $db = $this->db;
        $challan = $db->query("
            SELECT ch.*, c.customer_name, c.mobile as customer_mobile, c.gst_number as customer_gst, c.address as customer_address, u.name as creator_name
            FROM challans ch
            LEFT JOIN customers c ON ch.customer_id = c.id
            LEFT JOIN users u ON ch.created_by = u.id
            WHERE ch.id = ? LIMIT 1
        ", [(int)$id])->fetch();

        if (!$challan) {
            header("Location: " . BASE_URL . "/challans/index.php");
            exit;
        }

        $items = $db->query("
            SELECT ci.*, p.product_name, p.sku, un.short_name as unit_name
            FROM challan_items ci
            JOIN products p ON ci.product_id = p.id
            LEFT JOIN units un ON p.unit_id = un.id
            WHERE ci.challan_id = ?
        ", [(int)$id])->fetchAll();

        // Fetch company settings for view
        $compSettings = $db->query("SELECT * FROM company_settings WHERE id = 1 LIMIT 1")->fetch();

        // Load layout views
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/challans/view.php';
        require_once __DIR__ . '/../views/footer.php';
    }
}
