<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Application Settings & Backup API (Part 2 Database updates)
 */

require_once __DIR__ . '/../vendor/autoload.php';
use App\Models\Auth;
use App\Models\Helpers;
use App\Models\Database;

require_once __DIR__ . '/../config/database.php';

$db = new Database();
$auth = new Auth($db);
$auth->requirePermission('Manage Settings');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        // Retrieve company settings from company_settings table (row ID 1)
        try {
            $settings = $db->query("SELECT * FROM company_settings WHERE id = 1 LIMIT 1")->fetch();
            if (!$settings) {
                // Return default array structure if not seeded somehow
                $settings = [
                    'company_name' => '',
                    'gst_number' => '',
                    'email' => '',
                    'phone' => '',
                    'address' => '',
                    'invoice_prefix' => 'INV-',
                    'gst_slabs' => '0,5,12,18,28',
                    'invoice_footer' => '',
                    'invoice_terms' => '',
                    'state_code' => '',
                    'loyalty_enabled' => 0,
                    'loyalty_points_per_100' => 0,
                    'loyalty_redeem_value' => 0,
                    'invoice_template' => 'standard',
                    'pos_template' => 'pos_standard',
                    'thermal_width' => '80mm',
                    'pos_mode' => 0,
                    'pos_show_logo' => 1,
                    'pos_show_cashier' => 1,
                    'pos_show_customer_mobile' => 1,
                    'pos_show_hsn' => 0,
                    'pos_show_gst_breakdown' => 1,
                    'pos_header_text' => '',
                    'pos_footer_text' => '',
                    'system_language' => 'en',
                    'bank_name' => '',
                    'bank_account_no' => '',
                    'bank_ifsc' => '',
                    'bank_branch' => '',
                    'upi_id' => ''
                ];
            }
            // Get document usage counts for range status
            $year = date('Y');
            try {
                $invCount = (int)($db->query("SELECT COUNT(*) as c FROM invoices WHERE invoice_date LIKE ?", ["$year-%"])->fetch()['c'] ?? 0);
                $qtCount = (int)($db->query("SELECT COUNT(*) as c FROM quotations WHERE quotation_date LIKE ?", ["$year-%"])->fetch()['c'] ?? 0);
                $poCount = (int)($db->query("SELECT COUNT(*) as c FROM purchases WHERE purchase_date LIKE ?", ["$year-%"])->fetch()['c'] ?? 0);
                $dcCount = (int)($db->query("SELECT COUNT(*) as c FROM challans WHERE challan_date LIKE ?", ["$year-%"])->fetch()['c'] ?? 0);
            } catch (Exception $e) { $invCount = $qtCount = $poCount = $dcCount = 0; }

            $settings['doc_usage'] = [
                'invoice' => $invCount,
                'quotation' => $qtCount,
                'purchase' => $poCount,
                'challan' => $dcCount
            ];

            Helpers::jsonResponse(true, "Settings loaded", $settings);
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed to load settings: " . $e->getMessage());
        }
        break;

    case 'save':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, "Invalid method");
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, "CSRF verification failed.");

        $company_name = trim($_POST['company_name'] ?? '');
        $gst_number = trim($_POST['company_gst'] ?? $_POST['gst_number'] ?? '');
        $email = trim($_POST['company_email'] ?? $_POST['email'] ?? '');
        $phone = trim($_POST['company_phone'] ?? $_POST['phone'] ?? '');
        $address = trim($_POST['company_address'] ?? $_POST['address'] ?? '');
        $invoice_prefix = trim($_POST['invoice_prefix'] ?? 'INV-');
        $quotation_prefix = trim($_POST['quotation_prefix'] ?? 'QT-');
        $purchase_prefix = trim($_POST['purchase_prefix'] ?? 'PO-');
        $challan_prefix = trim($_POST['challan_prefix'] ?? 'DC-');
        $invoice_start = (int)($_POST['invoice_start'] ?? 1);
        $invoice_end = (int)($_POST['invoice_end'] ?? 99999);
        $quotation_start = (int)($_POST['quotation_start'] ?? 1);
        $quotation_end = (int)($_POST['quotation_end'] ?? 99999);
        $purchase_start = (int)($_POST['purchase_start'] ?? 1);
        $purchase_end = (int)($_POST['purchase_end'] ?? 99999);
        $challan_start = (int)($_POST['challan_start'] ?? 1);
        $challan_end = (int)($_POST['challan_end'] ?? 99999);
        $gst_slabs = trim($_POST['gst_slabs'] ?? '0,5,12,18,28');
        $state_code = trim($_POST['state_code'] ?? '');
        $invoice_footer = trim($_POST['invoice_footer'] ?? '');
        $invoice_terms = trim($_POST['invoice_terms'] ?? '');
        $loyalty_enabled = (int)($_POST['loyalty_enabled'] ?? 0);
        $loyalty_points_per_100 = (int)($_POST['loyalty_points_per_100'] ?? 0);
        $loyalty_redeem_value = (float)($_POST['loyalty_redeem_value'] ?? 0);
        $invoice_template = trim($_POST['invoice_template'] ?? 'standard');
        $pos_template = trim($_POST['pos_template'] ?? 'pos_standard');
        $thermal_width = trim($_POST['thermal_width'] ?? '80mm');
        $pos_mode = (int)($_POST['pos_mode'] ?? 0);
        $pos_show_logo = (int)($_POST['pos_show_logo'] ?? 0);
        $pos_show_cashier = (int)($_POST['pos_show_cashier'] ?? 0);
        $pos_show_customer_mobile = (int)($_POST['pos_show_customer_mobile'] ?? 0);
        $pos_show_hsn = (int)($_POST['pos_show_hsn'] ?? 0);
        $pos_show_gst_breakdown = (int)($_POST['pos_show_gst_breakdown'] ?? 0);
        $pos_header_text = trim($_POST['pos_header_text'] ?? '');
        $pos_footer_text = trim($_POST['pos_footer_text'] ?? '');
        $system_language = trim($_POST['system_language'] ?? 'en');
        $bank_name = trim($_POST['bank_name'] ?? '');
        $bank_account_no = trim($_POST['bank_account_no'] ?? '');
        $bank_ifsc = trim($_POST['bank_ifsc'] ?? '');
        $bank_branch = trim($_POST['bank_branch'] ?? '');
        $upi_id = trim($_POST['upi_id'] ?? '');

        if (empty($company_name)) {
            Helpers::jsonResponse(false, "Business name is required.");
        }

        // Handle logo upload
        $logo_filename = null;
        $update_logo = false;

        if (!empty($_FILES['company_logo_file']['name']) && $_FILES['company_logo_file']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/png', 'image/jpeg', 'image/svg+xml', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['company_logo_file']['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime, $allowed)) {
                Helpers::jsonResponse(false, "Invalid logo format. Use PNG, JPG, SVG, or WebP.");
            }
            if ($_FILES['company_logo_file']['size'] > 2 * 1024 * 1024) {
                Helpers::jsonResponse(false, "Logo file must be under 2MB.");
            }

            $ext = pathinfo($_FILES['company_logo_file']['name'], PATHINFO_EXTENSION);
            $logo_filename = 'company_logo_' . time() . '.' . $ext;
            $dest = UPLOAD_DIR . '/' . $logo_filename;

            if (!move_uploaded_file($_FILES['company_logo_file']['tmp_name'], $dest)) {
                Helpers::jsonResponse(false, "Failed to upload logo file.");
            }

            // Delete old logo
            $old = $db->query("SELECT company_logo FROM company_settings WHERE id = 1 LIMIT 1")->fetch();
            if (!empty($old['company_logo']) && file_exists(UPLOAD_DIR . '/' . $old['company_logo'])) {
                @unlink(UPLOAD_DIR . '/' . $old['company_logo']);
            }
            $update_logo = true;
        }

        // Handle logo removal
        if (!empty($_POST['remove_logo'])) {
            $old = $db->query("SELECT company_logo FROM company_settings WHERE id = 1 LIMIT 1")->fetch();
            if (!empty($old['company_logo']) && file_exists(UPLOAD_DIR . '/' . $old['company_logo'])) {
                @unlink(UPLOAD_DIR . '/' . $old['company_logo']);
            }
            $logo_filename = '';
            $update_logo = true;
        }

        try {
            $sql = "UPDATE company_settings
                SET company_name = ?, gst_number = ?, email = ?, phone = ?, address = ?,
                    invoice_prefix = ?, quotation_prefix = ?, purchase_prefix = ?, challan_prefix = ?,
                    invoice_start = ?, invoice_end = ?, quotation_start = ?, quotation_end = ?,
                    purchase_start = ?, purchase_end = ?, challan_start = ?, challan_end = ?,
                    gst_slabs = ?, state_code = ?, invoice_footer = ?, invoice_terms = ?,
                    loyalty_enabled = ?, loyalty_points_per_100 = ?, loyalty_redeem_value = ?,
                    invoice_template = ?, pos_template = ?, thermal_width = ?, pos_mode = ?, system_language = ?,
                    pos_show_logo = ?, pos_show_cashier = ?, pos_show_customer_mobile = ?, pos_show_hsn = ?,
                    pos_show_gst_breakdown = ?, pos_header_text = ?, pos_footer_text = ?,
                    bank_name = ?, bank_account_no = ?, bank_ifsc = ?, bank_branch = ?, upi_id = ?";
            $params = [
                $company_name, $gst_number, $email, $phone, $address,
                $invoice_prefix, $quotation_prefix, $purchase_prefix, $challan_prefix,
                $invoice_start, $invoice_end, $quotation_start, $quotation_end,
                $purchase_start, $purchase_end, $challan_start, $challan_end,
                $gst_slabs, $state_code, $invoice_footer, $invoice_terms,
                $loyalty_enabled, $loyalty_points_per_100, $loyalty_redeem_value,
                $invoice_template, $pos_template, $thermal_width, $pos_mode, $system_language,
                $pos_show_logo, $pos_show_cashier, $pos_show_customer_mobile, $pos_show_hsn,
                $pos_show_gst_breakdown, $pos_header_text, $pos_footer_text,
                $bank_name, $bank_account_no, $bank_ifsc, $bank_branch, $upi_id
            ];

            if ($update_logo) {
                $sql .= ", company_logo = ?";
                $params[] = $logo_filename;
            }

            $sql .= ", updated_at = CURRENT_TIMESTAMP WHERE id = 1";
            $db->query($sql, $params);

            Helpers::logActivity($db, "settings", "Updated system and company configurations.");
            Helpers::jsonResponse(true, "Settings updated successfully.");
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed to save settings: " . $e->getMessage());
        }
        break;

    case 'backup':
        // Backup implementation
        $auth->requirePermission('Run Backups');
        $driver = $db->getConnection()->getAttribute(PDO::ATTR_DRIVER_NAME);
        
        $timestamp = date('Y-m-d_H-i-s');
        
        try {
            if ($driver === 'sqlite') {
                $backupFileName = "backup_{$timestamp}.sqlite";
                $backupPath = BACKUP_DIR . '/' . $backupFileName;
                
                // SQLite backup is as simple as copying the db file
                if (!file_exists(SQLITE_FILE)) {
                    throw new Exception("SQLite database file not found.");
                }
                
                if (copy(SQLITE_FILE, $backupPath)) {
                    $size = formatSize(filesize($backupPath));
                    
                    // Log to DB
                    $db->insert("INSERT INTO backup_logs (backup_file, backup_size, backup_date, status, created_by) VALUES (?, ?, ?, 'SUCCESS', ?)", [
                        $backupFileName, $size, date('Y-m-d'), $_SESSION['user_id']
                    ]);
                    
                    Helpers::logActivity($db, "backups", "SQLite database backed up: $backupFileName");
                    Helpers::jsonResponse(true, "Backup created successfully.", ['file' => $backupFileName]);
                } else {
                    throw new Exception("File copy failed.");
                }
            } else {
                // MySQL Backup
                $backupFileName = "backup_{$timestamp}.sql";
                $backupPath = BACKUP_DIR . '/' . $backupFileName;
                
                // Generic PHP MySQL Dumper to avoid shell_exec / mysqldump commands
                $sqlDump = dumpMySQLDatabase($db->getConnection());
                
                if (file_put_contents($backupPath, $sqlDump) !== false) {
                    $size = formatSize(filesize($backupPath));
                    
                    $db->insert("INSERT INTO backup_logs (backup_file, backup_size, backup_date, status, created_by) VALUES (?, ?, ?, 'SUCCESS', ?)", [
                        $backupFileName, $size, date('Y-m-d'), $_SESSION['user_id']
                    ]);
                    
                    Helpers::logActivity($db, "backups", "MySQL database backed up: $backupFileName");
                    Helpers::jsonResponse(true, "MySQL Backup created successfully.", ['file' => $backupFileName]);
                } else {
                    throw new Exception("Failed to write SQL file.");
                }
            }
        } catch (Exception $e) {
            // Log failed attempt
            $db->insert("INSERT INTO backup_logs (backup_file, backup_size, backup_date, status, created_by) VALUES (?, '0 KB', ?, 'FAILED', ?)", [
                "failed_{$timestamp}.sql", date('Y-m-d'), $_SESSION['user_id']
            ]);
            Helpers::jsonResponse(false, "Backup failed: " . $e->getMessage());
        }
        break;

    case 'backup_list':
        $auth->requirePermission('Run Backups');
        try {
            $stmt = $db->query("
                SELECT bl.*, u.name as creator_name 
                FROM backup_logs bl 
                LEFT JOIN users u ON bl.created_by = u.id 
                ORDER BY bl.created_at DESC
            ");
            Helpers::jsonResponse(true, "Backup logs list", $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed to load backup logs: " . $e->getMessage());
        }
        break;

    case 'download_backup':
        $auth->requirePermission('Run Backups');
        $fileName = basename($_GET['file'] ?? '');
        if (empty($fileName)) {
            die("Filename required.");
        }
        $filePath = BACKUP_DIR . '/' . $fileName;
        if (!file_exists($filePath) || !is_file($filePath)) {
            die("File not found.");
        }
        
        if (ob_get_level()) ob_end_clean();
        
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;

    case 'delete_backup':
        $auth->requirePermission('Run Backups');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, "Invalid method");
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, "CSRF verification failed.");

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) Helpers::jsonResponse(false, "Invalid backup ID.");

        try {
            $backup = $db->query("SELECT * FROM backup_logs WHERE id = ? LIMIT 1", [$id])->fetch();
            if (!$backup) Helpers::jsonResponse(false, "Backup record not found.");

            $filePath = BACKUP_DIR . '/' . $backup['backup_file'];
            if (file_exists($filePath) && is_file($filePath)) {
                @unlink($filePath);
            }

            $db->query("DELETE FROM backup_logs WHERE id = ?", [$id]);
            Helpers::logActivity($db, "backups", "Deleted backup: " . $backup['backup_file']);
            Helpers::jsonResponse(true, "Backup deleted successfully.");
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed to delete backup: " . $e->getMessage());
        }
        break;

    case 'purge_all':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, "Invalid method");
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, "CSRF verification failed.");

        $roleId = $_SESSION['role_id'] ?? null;
        if ($roleId != 1) {
            Helpers::jsonResponse(false, "Only Super Admin can perform this action.");
        }

        $password = $_POST['password'] ?? '';
        if (empty($password)) {
            Helpers::jsonResponse(false, "Password is required to confirm this action.");
        }

        $userId = $_SESSION['user_id'] ?? null;
        $user = $db->query("SELECT password FROM users WHERE id = ? LIMIT 1", [$userId])->fetch();
        if (!$user || !password_verify($password, $user['password'])) {
            Helpers::jsonResponse(false, "Incorrect password. Action cancelled.");
        }

        try {
            $db->transaction(function($db) {
                $tables = [
                    'loyalty_transactions',
                    'coupons',
                    'challan_items',
                    'challans',
                    'quotation_items',
                    'quotations',
                    'invoice_payments',
                    'held_bills',
                    'purchase_return_items',
                    'purchase_returns',
                    'sales_return_items',
                    'sales_returns',
                    'backup_logs',
                    'login_logs',
                    'activity_logs',
                    'notifications',
                    'report_logs',
                    'payments',
                    'expenses',
                    'expense_categories',
                    'invoice_items',
                    'invoices',
                    'customer_payments',
                    'customers',
                    'purchase_items',
                    'purchases',
                    'supplier_payments',
                    'suppliers',
                    'stock_transactions',
                    'product_images',
                    'product_batches',
                    'products',
                    'unit_conversions',
                    'units',
                    'brands',
                    'categories',
                ];

                foreach ($tables as $table) {
                    try {
                        $db->query("DELETE FROM $table");
                    } catch (Exception $e) {
                        // Skip if table doesn't exist yet
                    }
                }

                $db->query("DELETE FROM users WHERE role_id != 1");
            });

            Helpers::jsonResponse(true, "All records have been deleted successfully. Admin accounts preserved.");
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed to purge records: " . $e->getMessage());
        }
        break;

    default:
        Helpers::jsonResponse(false, "Action not found: " . $action);
}

// Helpers
function formatSize($bytes): string {
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    }
    return number_format($bytes / 1024, 2) . ' KB';
}

function dumpMySQLDatabase(PDO $pdo): string {
    $tables = [];
    $result = $pdo->query("SHOW TABLES");
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    
    $return = "-- Grovixo MySQL Backup --\n\n";
    
    foreach ($tables as $table) {
        $result = $pdo->query("SELECT * FROM " . $table);
        $numFields = $result->columnCount();
        
        $return .= "DROP TABLE IF EXISTS " . $table . ";\n";
        
        $row2 = $pdo->query("SHOW CREATE TABLE " . $table)->fetch(PDO::FETCH_NUM);
        $return .= "\n\n" . $row2[1] . ";\n\n";
        
        for ($i = 0; $i < $numFields; $i++) {
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $return .= "INSERT INTO " . $table . " VALUES(";
                for ($j = 0; $j < $numFields; $j++) {
                    if (isset($row[$j])) {
                        $escaped = addslashes($row[$j]);
                        $escaped = preg_replace("/\n/i", "\\n", $escaped);
                        $return .= '"' . $escaped . '"';
                    } else {
                        $return .= 'NULL';
                    }
                    if ($j < ($numFields - 1)) {
                        $return .= ',';
                    }
                }
                $return .= ");\n";
            }
        }
        $return .= "\n\n\n";
    }
    return $return;
}
