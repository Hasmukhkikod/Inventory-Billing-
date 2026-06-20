<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * User & Permissions Management API (Part 3)
 */

require_once __DIR__ . '/../vendor/autoload.php';
use App\Models\Auth;
use App\Models\Helpers;
use App\Models\Database;

require_once __DIR__ . '/../config/database.php';

$db = new Database();
$auth = new Auth($db);
$auth->requirePermission('Manage Users');

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

switch ($action) {
    case 'list':
        try {
            $stmt = $db->query("
                SELECT u.id, u.role_id, u.name, u.email, u.mobile, u.status, u.last_login, r.role_name 
                FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE u.deleted_at IS NULL
                ORDER BY u.name ASC
            ");
            Helpers::jsonResponse(true, "Users list", $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed to load users: " . $e->getMessage());
        }
        break;

    case 'get':
        $id = (int)($_GET['id'] ?? 0);
        try {
            $user = $db->query("SELECT id, role_id, name, email, mobile, status FROM users WHERE id = ? LIMIT 1", [$id])->fetch();
            if ($user) {
                Helpers::jsonResponse(true, "User loaded", $user);
            } else {
                Helpers::jsonResponse(false, "User not found.");
            }
        } catch (Exception $e) {
            Helpers::jsonResponse(false, $e->getMessage());
        }
        break;

    case 'save':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, "Method not allowed.");
        
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $role_id = (int)($_POST['role_id'] ?? 0);
        $status = trim($_POST['status'] ?? 'ACTIVE');
        $password = trim($_POST['password'] ?? '');

        if (empty($name) || empty($email) || empty($mobile) || $role_id <= 0) {
            Helpers::jsonResponse(false, "Name, Email, Mobile, and Role ID are required.");
        }

        // Email uniqueness check
        $emailQuery = ($id > 0) 
            ? "SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1"
            : "SELECT id FROM users WHERE email = ? LIMIT 1";
        $emailParams = ($id > 0) ? [$email, $id] : [$email];
        if ($db->query($emailQuery, $emailParams)->fetch()) {
            Helpers::jsonResponse(false, "A user with this email address already exists.");
        }

        try {
            if ($id > 0) {
                // Update
                if (!empty($password)) {
                    $hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                    $db->query("
                        UPDATE users 
                        SET role_id = ?, name = ?, email = ?, mobile = ?, password = ?, status = ?, updated_at = CURRENT_TIMESTAMP
                        WHERE id = ?
                    ", [$role_id, $name, $email, $mobile, $hashed, $status, $id]);
                } else {
                    $db->query("
                        UPDATE users 
                        SET role_id = ?, name = ?, email = ?, mobile = ?, status = ?, updated_at = CURRENT_TIMESTAMP
                        WHERE id = ?
                    ", [$role_id, $name, $email, $mobile, $status, $id]);
                }
                Helpers::logActivity($db, "users", "Updated user ID: $id ($name)", $id);
                Helpers::jsonResponse(true, "User profile updated successfully.");
            } else {
                // Insert
                if (empty($password)) {
                    Helpers::jsonResponse(false, "Password is required for new users.");
                }
                $hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $newId = $db->insert("
                    INSERT INTO users (role_id, name, email, mobile, password, status, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ", [$role_id, $name, $email, $mobile, $hashed, $status, $_SESSION['user_id']]);

                Helpers::logActivity($db, "users", "Created user ID: $newId ($name)", $newId);
                Helpers::jsonResponse(true, "User created successfully.");
            }
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed to save user: " . $e->getMessage());
        }
        break;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) Helpers::jsonResponse(false, "Invalid user ID.");
        if ($id === 1 || $id === (int)$_SESSION['user_id']) {
            Helpers::jsonResponse(false, "Cannot delete super-admin user or currently logged-in account.");
        }

        try {
            $db->query("UPDATE users SET deleted_at = CURRENT_TIMESTAMP, status = 'INACTIVE' WHERE id = ?", [$id]);
            Helpers::logActivity($db, "users", "Soft-deleted user account ID: $id", $id);
            Helpers::jsonResponse(true, "User marked as inactive / soft-deleted.");
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed: " . $e->getMessage());
        }
        break;

    case 'roles_list':
        try {
            $roles = $db->query("SELECT * FROM roles WHERE deleted_at IS NULL ORDER BY id ASC")->fetchAll();
            $rolesWithPermissions = [];
            
            foreach ($roles as $r) {
                // Fetch associated permission names
                $permsStmt = $db->query("
                    SELECT p.permission_name 
                    FROM role_permissions rp
                    JOIN permissions p ON rp.permission_id = p.id
                    WHERE rp.role_id = ? AND rp.status = 'ACTIVE' AND rp.deleted_at IS NULL
                ", [$r['id']]);
                
                $r['permissions'] = $permsStmt->fetchAll(PDO::FETCH_COLUMN);
                $rolesWithPermissions[] = $r;
            }
            Helpers::jsonResponse(true, "Roles and permissions loaded", $rolesWithPermissions);
        } catch (Exception $e) {
            Helpers::jsonResponse(false, $e->getMessage());
        }
        break;

    case 'activity_logs':
        try {
            $userId = (int)($_GET['user_id'] ?? 0);
            $query = "
                SELECT al.*, u.name as user_name 
                FROM activity_logs al
                LEFT JOIN users u ON al.user_id = u.id
            ";
            $params = [];
            if ($userId > 0) {
                $query .= " WHERE al.user_id = ? ";
                $params[] = $userId;
            }
            $query .= " ORDER BY al.created_at DESC LIMIT 50 ";
            
            $stmt = $db->query($query, $params);
            Helpers::jsonResponse(true, "Activity logs", $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, $e->getMessage());
        }
        break;

    case 'login_logs':
        try {
            $userId = (int)($_GET['user_id'] ?? 0);
            $query = "
                SELECT ll.*, u.name as user_name 
                FROM login_logs ll
                JOIN users u ON ll.user_id = u.id
            ";
            $params = [];
            if ($userId > 0) {
                $query .= " WHERE ll.user_id = ? ";
                $params[] = $userId;
            }
            $query .= " ORDER BY ll.login_time DESC LIMIT 50 ";
            
            $stmt = $db->query($query, $params);
            Helpers::jsonResponse(true, "Login history logs", $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, $e->getMessage());
        }
        break;

    default:
        Helpers::jsonResponse(false, "Action not found: " . $action);
}
