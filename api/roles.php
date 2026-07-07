<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * API: Roles & Permissions
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
use App\Models\Auth;
use App\Models\Database;
use App\Models\Helpers;

$db = new Database();
$auth = new Auth($db);

if (!$auth->check()) {
    Helpers::jsonResponse(false, "Authentication required.");
}

$auth->requirePermission('Manage Settings');
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'save':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, "Method not allowed.");
        
        $id = (int)($_POST['id'] ?? 0);
        $role_name = trim($_POST['role_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = trim($_POST['status'] ?? 'ACTIVE');
        $permissions = isset($_POST['permissions']) && is_array($_POST['permissions']) ? $_POST['permissions'] : [];
        $created_by = $auth->user()['id'];

        if (empty($role_name)) {
            Helpers::jsonResponse(false, "Role Name is required.");
        }

        $nameQuery = ($id > 0) 
            ? "SELECT id FROM roles WHERE role_name = ? AND id != ? AND deleted_at IS NULL LIMIT 1"
            : "SELECT id FROM roles WHERE role_name = ? AND deleted_at IS NULL LIMIT 1";
        $nameParams = ($id > 0) ? [$role_name, $id] : [$role_name];
        
        if ($db->query($nameQuery, $nameParams)->fetch()) {
            Helpers::jsonResponse(false, "A role with this name already exists.");
        }

        try {
            $db->pdo->beginTransaction();
            
            if ($id > 0) {
                if ($id == 1) {
                    $db->pdo->rollBack();
                    Helpers::jsonResponse(false, "Super Admin role cannot be modified.");
                }
                $db->query("UPDATE roles SET role_name = ?, description = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?", [$role_name, $description, $status, $id]);
                $db->query("DELETE FROM role_permissions WHERE role_id = ?", [$id]);
                $roleId = $id;
            } else {
                $roleId = $db->insert("INSERT INTO roles (role_name, description, status, created_by) VALUES (?, ?, ?, ?)", [$role_name, $description, $status, $created_by]);
            }
            
            if (!empty($permissions)) {
                $stmt = $db->pdo->prepare("INSERT INTO role_permissions (role_id, permission_id, created_by) VALUES (?, ?, ?)");
                foreach ($permissions as $perm_id) {
                    $stmt->execute([$roleId, (int)$perm_id, $created_by]);
                }
            }

            $db->pdo->commit();
            Helpers::logActivity($db, "roles", ($id > 0 ? "Updated role" : "Created role") . ": $role_name", $roleId);
            Helpers::jsonResponse(true, "Role saved successfully.");
        } catch (\Exception $e) {
            if ($db->pdo->inTransaction()) {
                $db->pdo->rollBack();
            }
            Helpers::jsonResponse(false, "Error saving role: " . $e->getMessage());
        }
        break;

    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, "Method not allowed.");
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) Helpers::jsonResponse(false, "Invalid Role ID.");
        if ($id == 1) Helpers::jsonResponse(false, "Super Admin role cannot be deleted.");
        
        $usersWithRole = $db->query("SELECT COUNT(id) as cnt FROM users WHERE role_id = ?", [$id])->fetch();
        if ($usersWithRole && $usersWithRole['cnt'] > 0) {
            Helpers::jsonResponse(false, "Cannot delete role because it is currently assigned to {$usersWithRole['cnt']} user(s).");
        }

        try {
            $db->query("UPDATE roles SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?", [$id]);
            Helpers::logActivity($db, "roles", "Deleted role ID: $id", $id);
            Helpers::jsonResponse(true, "Role deleted successfully.");
        } catch (\Exception $e) {
            Helpers::jsonResponse(false, "Error deleting role: " . $e->getMessage());
        }
        break;

    default:
        Helpers::jsonResponse(false, "Invalid action requested.");
}
