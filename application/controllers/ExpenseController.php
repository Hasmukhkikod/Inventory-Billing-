<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Expense Controller
 */
namespace App\Controllers;

use App\Models\Auth;
use App\Models\Database;
use App\Models\Helpers;




class ExpenseController {
    protected $db;
    protected $auth;

    public function __construct($db, $auth) {
        $this->db = $db;
        $this->auth = $auth;
    }

    public function index() {
        $this->auth->requirePermission('Manage Expenses');
        
        // Load layout views
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/expenses/list.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function form($id = null) {
        $this->auth->requirePermission('Manage Expenses');
        
        $expense = null;
        if ($id > 0) {
            $db = $this->db;
            $expense = $db->query("SELECT * FROM expenses WHERE id = ? LIMIT 1", [(int)$id])->fetch();
        }
        
        // Load layout views
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/expenses/form.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function view($id) {
        $this->auth->requirePermission('Manage Expenses');
        
        $db = $this->db;
        $expense = $db->query("
            SELECT e.*, ec.category_name, u.name as creator_name
            FROM expenses e
            LEFT JOIN expense_categories ec ON e.category_id = ec.id
            LEFT JOIN users u ON e.created_by = u.id
            WHERE e.id = ? LIMIT 1
        ", [(int)$id])->fetch();
        
        if (!$expense) {
            header("Location: " . BASE_URL . "/expenses/index.php");
            exit;
        }
        
        // Load layout views
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/expenses/view.php';
        require_once __DIR__ . '/../views/footer.php';
    }
}
