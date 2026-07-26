<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Announcements Controller
 */
namespace App\Controllers;

class AnnouncementsController {
    protected $db;
    protected $auth;

    public function __construct($db, $auth) {
        $this->db = $db;
        $this->auth = $auth;
    }

    public function index() {
        // Only Super Admin can access this
        if ($_SESSION['user_id'] != 1) {
            header("Location: " . BASE_URL . "/index");
            exit;
        }
        
        $announcements = $this->db->query("SELECT * FROM system_announcements ORDER BY id DESC")->fetchAll();
        
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/announcements/index.php';
        require_once __DIR__ . '/../views/footer.php';
    }
}
