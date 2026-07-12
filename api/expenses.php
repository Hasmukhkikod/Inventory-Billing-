<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Expense Book Keeping API (Part 2 Database updates)
 */

require_once __DIR__ . '/../vendor/autoload.php';
use App\Models\Auth;
use App\Models\Helpers;
use App\Models\Database;

require_once __DIR__ . '/../config/database.php';

$db = new Database();
$auth = new Auth($db);
$auth->requirePermission('Manage Expenses');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        try {
            $stmt = $db->query("
                SELECT e.*, ec.category_name 
                FROM expenses e 
                JOIN expense_categories ec ON e.category_id = ec.id 
                WHERE e.deleted_at IS NULL
                ORDER BY e.expense_date DESC
            ");
            Helpers::jsonResponse(true, "Expenses list", $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed to load expenses: " . $e->getMessage());
        }
        break;

    case 'save':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, "Invalid method");
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, "CSRF verification failed");

        $id = (int)($_POST['id'] ?? 0);
        $category_id = (int)($_POST['category_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $date = trim($_POST['expense_date'] ?? date('Y-m-d'));
        $desc = trim($_POST['description'] ?? '');
        $payment_method = trim($_POST['payment_method'] ?? 'CASH');

        if ($category_id <= 0 || $amount <= 0) {
            Helpers::jsonResponse(false, "Expense Category and positive amount are required.");
        }

        // Handle Bill copy upload
        $billPath = $_POST['existing_bill'] ?? '';
        if (isset($_FILES['bill_copy']) && $_FILES['bill_copy']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['bill_copy']['tmp_name'];
            $fileName = $_FILES['bill_copy']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
            if (in_array($fileExtension, $allowedExtensions)) {
                $newFileName = 'bill_' . md5(time() . $amount) . '.' . $fileExtension;
                $destPath = UPLOAD_DIR . '/' . $newFileName;
                
                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $billPath = 'uploads/' . $newFileName;
                }
            } else {
                Helpers::jsonResponse(false, "Invalid document format. Allowed: PDF, JPG, JPEG, PNG");
            }
        }

        try {
            if ($id > 0) {
                $db->query("
                    UPDATE expenses 
                    SET category_id = ?, amount = ?, expense_date = ?, description = ?, bill_attachment = ?, payment_method = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ", [$category_id, $amount, $date, $desc, $billPath, $payment_method, $id]);
                
                Helpers::logActivity($db, "expenses", "Updated expense ID: $id (Amount: $amount)", $id);
                Helpers::jsonResponse(true, "Expense record updated successfully.");
            } else {
                $db->transaction(function($t) use ($category_id, $amount, $date, $desc, $billPath, $payment_method) {
                    $expenseId = $t->insert("
                        INSERT INTO expenses (category_id, amount, expense_date, description, bill_attachment, payment_method, status, created_by) 
                        VALUES (?, ?, ?, ?, ?, ?, 'ACTIVE', ?)
                    ", [$category_id, $amount, $date, $desc, $billPath, $payment_method, $_SESSION['user_id']]);

                    // Also record payment in global payments table
                    $t->insert("
                        INSERT INTO payments (transaction_type, reference_id, payment_method, amount, transaction_date, remarks, created_by)
                        VALUES ('Expense', ?, ?, ?, ?, ?, ?)
                    ", [$expenseId, $payment_method, $amount, $date, "Expense: $desc", $_SESSION['user_id']]);

                    Helpers::logActivity($t, "expenses", "Created expense ID: $expenseId (Amount: $amount)", $expenseId);
                });

                Helpers::jsonResponse(true, "Expense logged successfully.");
            }
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed to save expense: " . $e->getMessage());
        }
        break;

    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, "Invalid method");
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, "CSRF verification failed");
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) Helpers::jsonResponse(false, "Invalid expense ID.");
        
        try {
            // Soft delete
            $db->query("UPDATE expenses SET deleted_at = CURRENT_TIMESTAMP, status = 'INACTIVE' WHERE id = ?", [$id]);
            Helpers::logActivity($db, "expenses", "Deleted expense ID: $id", $id);
            Helpers::jsonResponse(true, "Expense record deleted successfully.");
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed to delete expense: " . $e->getMessage());
        }
        break;

    case 'categories_list':
        try {
            $stmt = $db->query("SELECT * FROM expense_categories WHERE deleted_at IS NULL ORDER BY category_name ASC");
            Helpers::jsonResponse(true, "Expense categories", $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, $e->getMessage());
        }
        break;

    case 'category_save':
        $name = trim($_POST['category_name'] ?? $_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        if (empty($name)) Helpers::jsonResponse(false, "Category name is required");
        
        try {
            // Check existence
            $check = $db->query("SELECT id FROM expense_categories WHERE category_name = ? AND deleted_at IS NULL LIMIT 1", [$name])->fetch();
            if ($check) {
                Helpers::jsonResponse(false, "This category already exists.");
            }
            
            $catId = $db->insert("INSERT INTO expense_categories (category_name, description) VALUES (?, ?)", [$name, $desc]);
            Helpers::logActivity($db, "expenses", "Created Expense Category: $name", $catId);
            Helpers::jsonResponse(true, "Expense category created successfully.", ['id' => $catId, 'name' => $name]);
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed to create category: " . $e->getMessage());
        }
        break;

    case 'bulk':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, 'Method not allowed');
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, 'CSRF verification failed');

        $bulk_action = trim($_POST['bulk_action'] ?? '');
        $ids = json_decode($_POST['ids'] ?? '[]', true);

        if (empty($ids)) Helpers::jsonResponse(false, 'No records selected');

        try {
            if ($bulk_action === 'delete') {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $db->query("UPDATE expenses SET status = 'INACTIVE', deleted_at = CURRENT_TIMESTAMP WHERE id IN ($placeholders)", $ids);
                Helpers::logActivity($db, 'expenses', 'Bulk deleted ' . count($ids) . ' records');
                Helpers::jsonResponse(true, count($ids) . ' records deleted successfully');
            }
            Helpers::jsonResponse(false, 'Unknown bulk action');
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Bulk action failed: ' . $e->getMessage());
        }
        break;

    default:
        Helpers::jsonResponse(false, "Action not found: " . $action);
}
