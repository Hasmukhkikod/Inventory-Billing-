<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Printer Settings API
 */

require_once __DIR__ . '/../vendor/autoload.php';
use App\Models\Auth;
use App\Models\Helpers;
use App\Models\Database;

require_once __DIR__ . '/../config/database.php';

$db = new Database();
$auth = new Auth($db);
if (!$auth->check()) Helpers::jsonResponse(false, 'Session expired. Please log in again.');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        try {
            $printers = $db->query("SELECT * FROM printers WHERE deleted_at IS NULL ORDER BY is_default DESC, name ASC")->fetchAll();
            Helpers::jsonResponse(true, 'Printers list', ['printers' => $printers]);
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Failed to load printers: ' . $e->getMessage());
        }
        break;

    case 'get_default':
        try {
            $printer = $db->query("SELECT * FROM printers WHERE is_default = 1 AND deleted_at IS NULL LIMIT 1")->fetch();
            Helpers::jsonResponse(true, 'Default printer', ['printer' => $printer ?: null]);
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Failed: ' . $e->getMessage());
        }
        break;

    case 'save':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, 'Invalid method');
        $auth->requirePermission('Manage Settings');
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, 'CSRF verification failed.');

        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $connection_type = trim($_POST['connection_type'] ?? '');
        $ip_address = trim($_POST['ip_address'] ?? '');
        $port = !empty($_POST['port']) ? (int)$_POST['port'] : null;
        $paper_width_dots = (int)($_POST['paper_width_dots'] ?? 576);

        if (empty($name)) Helpers::jsonResponse(false, 'Printer name is required.');
        if (!in_array($connection_type, ['USB', 'BLUETOOTH', 'LAN'])) Helpers::jsonResponse(false, 'Invalid connection type.');
        if ($connection_type === 'LAN') {
            if (empty($ip_address) || !filter_var($ip_address, FILTER_VALIDATE_IP)) {
                Helpers::jsonResponse(false, 'Enter a valid IP address for a WiFi/LAN printer.');
            }
            if (!$port) $port = 9100;
        } else {
            $ip_address = null;
            $port = null;
        }
        if ($paper_width_dots < 128 || $paper_width_dots > 1200) {
            Helpers::jsonResponse(false, 'Printer width looks invalid.');
        }

        try {
            if ($id > 0) {
                $existing = $db->query("SELECT id FROM printers WHERE id = ? AND deleted_at IS NULL LIMIT 1", [$id])->fetch();
                if (!$existing) Helpers::jsonResponse(false, 'Printer not found.');

                $db->query("
                    UPDATE printers SET name=?, connection_type=?, ip_address=?, port=?, paper_width_dots=?, updated_at=CURRENT_TIMESTAMP
                    WHERE id=?
                ", [$name, $connection_type, $ip_address, $port, $paper_width_dots, $id]);

                Helpers::logActivity($db, 'printers', "Updated printer: $name", $id);
                Helpers::jsonResponse(true, 'Printer updated.', ['id' => $id]);
            } else {
                $newId = $db->insert("
                    INSERT INTO printers (name, connection_type, ip_address, port, paper_width_dots, created_by)
                    VALUES (?,?,?,?,?,?)
                ", [$name, $connection_type, $ip_address, $port, $paper_width_dots, $_SESSION['user_id']]);

                $count = $db->query("SELECT COUNT(*) as c FROM printers WHERE deleted_at IS NULL")->fetch();
                if ((int)$count['c'] === 1) {
                    $db->query("UPDATE printers SET is_default = 1 WHERE id = ?", [$newId]);
                }

                Helpers::logActivity($db, 'printers', "Added printer: $name ($connection_type)", $newId);
                Helpers::jsonResponse(true, 'Printer added.', ['id' => $newId]);
            }
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Save failed: ' . $e->getMessage());
        }
        break;

    case 'set_default':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, 'Invalid method');
        $auth->requirePermission('Manage Settings');
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, 'CSRF verification failed.');

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) Helpers::jsonResponse(false, 'Invalid printer.');

        try {
            $printer = $db->query("SELECT name FROM printers WHERE id = ? AND deleted_at IS NULL LIMIT 1", [$id])->fetch();
            if (!$printer) Helpers::jsonResponse(false, 'Printer not found.');

            $db->query("UPDATE printers SET is_default = 0");
            $db->query("UPDATE printers SET is_default = 1 WHERE id = ?", [$id]);
            Helpers::logActivity($db, 'printers', 'Set default printer: ' . $printer['name'], $id);
            Helpers::jsonResponse(true, 'Default printer updated.');
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Failed: ' . $e->getMessage());
        }
        break;

    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, 'Invalid method');
        $auth->requirePermission('Manage Settings');
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, 'CSRF verification failed.');

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) Helpers::jsonResponse(false, 'Invalid printer.');

        try {
            $printer = $db->query("SELECT name, is_default FROM printers WHERE id = ? AND deleted_at IS NULL LIMIT 1", [$id])->fetch();
            if (!$printer) Helpers::jsonResponse(false, 'Printer not found.');

            $db->query("UPDATE printers SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?", [$id]);

            if ((int)$printer['is_default'] === 1) {
                $next = $db->query("SELECT id FROM printers WHERE deleted_at IS NULL ORDER BY created_at ASC LIMIT 1")->fetch();
                if ($next) $db->query("UPDATE printers SET is_default = 1 WHERE id = ?", [$next['id']]);
            }

            Helpers::logActivity($db, 'printers', 'Removed printer: ' . $printer['name'], $id);
            Helpers::jsonResponse(true, 'Printer removed.');
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Delete failed: ' . $e->getMessage());
        }
        break;

    default:
        Helpers::jsonResponse(false, 'Action not found: ' . $action);
}
