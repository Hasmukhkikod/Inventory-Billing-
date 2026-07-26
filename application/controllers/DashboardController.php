<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Dashboard Controller
 */
namespace App\Controllers;

use App\Models\Auth;

class DashboardController {
    protected $db;
    protected $auth;

    public function __construct($db, $auth) {
        $this->db = $db;
        $this->auth = $auth;
    }

    public function index() {
        $this->auth->requirePermission('Access Dashboard');
        
        $orgId = $_SESSION['org_id'] ?? null;
        
        if ($orgId == 0) {
            // SUPER ADMIN DASHBOARD
            $data = $this->getSuperAdminData();
            
            require_once __DIR__ . '/../views/header.php';
            require_once __DIR__ . '/../views/admin_dashboard.php';
            require_once __DIR__ . '/../views/footer.php';
        } else {
            // REGULAR TENANT DASHBOARD
            require_once __DIR__ . '/../views/header.php';
            require_once __DIR__ . '/../views/dashboard.php';
            require_once __DIR__ . '/../views/footer.php';
        }
    }

    private function getSuperAdminData() {
        $db = $this->db;
        $data = [];
        
        $data['total_orgs'] = $db->query("SELECT COUNT(*) as count FROM organizations")->fetch()['count'] ?? 0;
        $data['active_orgs'] = $db->query("SELECT COUNT(*) as count FROM organizations WHERE status = 'ACTIVE'")->fetch()['count'] ?? 0;
        $data['expired_orgs'] = $db->query("SELECT COUNT(*) as count FROM organizations WHERE valid_until < CURDATE() AND valid_until IS NOT NULL")->fetch()['count'] ?? 0;
        $data['recent_orgs'] = $db->query("SELECT o.*, p.plan_name FROM organizations o LEFT JOIN plans p ON o.plan_id = p.id ORDER BY o.id DESC LIMIT 20")->fetchAll();
        
        $storageQuery = "
            SELECT org_id, SUM(rows) as total_records FROM (
                SELECT org_id, COUNT(*) as rows FROM invoices GROUP BY org_id
                UNION ALL
                SELECT org_id, COUNT(*) as rows FROM products GROUP BY org_id
                UNION ALL
                SELECT org_id, COUNT(*) as rows FROM customers GROUP BY org_id
                UNION ALL
                SELECT org_id, COUNT(*) as rows FROM expenses GROUP BY org_id
                UNION ALL
                SELECT org_id, COUNT(*) as rows FROM purchases GROUP BY org_id
                UNION ALL
                SELECT org_id, COUNT(*) as rows FROM quotations GROUP BY org_id
            ) as t GROUP BY org_id
        ";
        
        try {
            $storageDataRaw = $db->query($storageQuery)->fetchAll();
            $storageMap = [];
            $totalRecords = 0;
            foreach ($storageDataRaw as $row) {
                if ($row['org_id']) {
                    $storageMap[$row['org_id']] = $row['total_records'];
                    $totalRecords += $row['total_records'];
                }
            }
            $data['storage_map'] = $storageMap;
            $data['total_system_records'] = $totalRecords;
            $data['total_storage_mb'] = number_format(($totalRecords * 2.5) / 1024, 2); // Assume 2.5KB average per record
        } catch (\Exception $e) {
            $data['storage_map'] = [];
            $data['total_system_records'] = 0;
            $data['total_storage_mb'] = '0.00';
        }
        
        return $data;
    }
}
