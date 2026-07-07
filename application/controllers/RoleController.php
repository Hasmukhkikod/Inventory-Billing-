<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Role Controller
 */
namespace App\Controllers;

use App\Models\Auth;
use App\Models\Database;

class RoleController {
    protected $db;
    protected $auth;

    public function __construct($db, $auth) {
        $this->db = $db;
        $this->auth = $auth;
    }

    public function index() {
        $this->auth->requirePermission('Manage Settings');
        
        $roles = $this->db->query("SELECT * FROM roles WHERE deleted_at IS NULL ORDER BY id ASC")->fetchAll();
        
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/roles/list.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function form() {
        $this->auth->requirePermission('Manage Settings');
        
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $role = null;
        $rolePermissions = [];

        if ($id > 0) {
            $role = $this->db->query("SELECT * FROM roles WHERE id = ?", [$id])->fetch();
            if (!$role) {
                die("Role not found.");
            }
            // Fetch mapped permissions
            $mapped = $this->db->query("SELECT permission_id FROM role_permissions WHERE role_id = ?", [$id])->fetchAll();
            $rolePermissions = array_column($mapped, 'permission_id');
        }

        // Fetch all permissions grouped by module
        $perms = $this->db->query("SELECT * FROM permissions WHERE status = 'ACTIVE' ORDER BY module ASC")->fetchAll();
        $permissions = [];
        foreach ($perms as $p) {
            $permissions[$p['module']][] = $p;
        }
        
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/roles/form.php';
        require_once __DIR__ . '/../views/footer.php';
    }
}
