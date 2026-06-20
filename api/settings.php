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
                    'thermal_width' => '80mm',
                    'bank_name' => '',
                    'bank_account_no' => '',
                    'bank_ifsc' => '',
                    'bank_branch' => '',
                    'upi_id' => ''
                ];
            }
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
        $gst_slabs = trim($_POST['gst_slabs'] ?? '0,5,12,18,28');
        $state_code = trim($_POST['state_code'] ?? '');
        $invoice_footer = trim($_POST['invoice_footer'] ?? '');
        $invoice_terms = trim($_POST['invoice_terms'] ?? '');
        $loyalty_enabled = (int)($_POST['loyalty_enabled'] ?? 0);
        $loyalty_points_per_100 = (int)($_POST['loyalty_points_per_100'] ?? 0);
        $loyalty_redeem_value = (float)($_POST['loyalty_redeem_value'] ?? 0);
        $invoice_template = trim($_POST['invoice_template'] ?? 'standard');
        $thermal_width = trim($_POST['thermal_width'] ?? '80mm');
        $bank_name = trim($_POST['bank_name'] ?? '');
        $bank_account_no = trim($_POST['bank_account_no'] ?? '');
        $bank_ifsc = trim($_POST['bank_ifsc'] ?? '');
        $bank_branch = trim($_POST['bank_branch'] ?? '');
        $upi_id = trim($_POST['upi_id'] ?? '');

        if (empty($company_name)) {
            Helpers::jsonResponse(false, "Business name is required.");
        }

        try {
            $db->query("
                UPDATE company_settings
                SET company_name = ?, gst_number = ?, email = ?, phone = ?, address = ?,
                    invoice_prefix = ?, gst_slabs = ?, state_code = ?, invoice_footer = ?, invoice_terms = ?,
                    loyalty_enabled = ?, loyalty_points_per_100 = ?, loyalty_redeem_value = ?,
                    invoice_template = ?, thermal_width = ?,
                    bank_name = ?, bank_account_no = ?, bank_ifsc = ?, bank_branch = ?, upi_id = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = 1
            ", [
                $company_name, $gst_number, $email, $phone, $address,
                $invoice_prefix, $gst_slabs, $state_code, $invoice_footer, $invoice_terms,
                $loyalty_enabled, $loyalty_points_per_100, $loyalty_redeem_value,
                $invoice_template, $thermal_width,
                $bank_name, $bank_account_no, $bank_ifsc, $bank_branch, $upi_id
            ]);

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
