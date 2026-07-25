<?php
require_once 'vendor/autoload.php';
require_once 'config/config.php';
require_once 'application/Models/Database.php';

$db = new \App\Models\Database();
$pdo = $db->getConnection();

$tables = [
    'activity_logs', 'backup_logs', 'brands', 'categories', 'challan_items', 
    'challans', 'company_settings', 'coupons', 'customer_payments', 'customers', 
    'expense_categories', 'expenses', 'held_bills', 'invoice_items', 'invoice_payments', 
    'invoices', 'login_logs', 'loyalty_transactions', 'notifications', 'payments', 
    'product_batches', 'product_images', 'products', 'purchase_items', 'purchase_return_items', 
    'purchase_returns', 'purchases', 'quotation_items', 'quotations', 'report_logs', 
    'role_permissions', 'roles', 'sales_return_items', 'sales_returns', 'stock_transactions', 
    'supplier_payments', 'suppliers', 'unit_conversions', 'units', 'users', 'printers'
];

echo "Starting migration...\n";

// Create plans table
$pdo->exec("
CREATE TABLE IF NOT EXISTS `plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `duration_days` int(11) NOT NULL DEFAULT 30,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
echo "Created plans table.\n";

// Create organizations table
$pdo->exec("
CREATE TABLE IF NOT EXISTS `organizations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `plan_id` int(11) DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
echo "Created organizations table.\n";

// Insert default plan and organization
$pdo->exec("INSERT IGNORE INTO `plans` (id, plan_name, price, duration_days) VALUES (1, 'Default Plan', 0, 365)");
$pdo->exec("INSERT IGNORE INTO `organizations` (id, name, plan_id, valid_until) VALUES (1, 'Default Organization', 1, '2099-12-31')");

foreach ($tables as $table) {
    try {
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `org_id` int(11) DEFAULT 1;");
        $pdo->exec("ALTER TABLE `$table` ADD INDEX `idx_org_id` (`org_id`);");
        echo "Added org_id to $table\n";
    } catch (PDOException $e) {
        // Ignore duplicate column errors
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "Column org_id already exists in $table\n";
        } else {
            echo "Error on $table: " . $e->getMessage() . "\n";
        }
    }
}

// Ensure Super Admin (id=1) has org_id = 0 (or NULL) so they can see everything.
// Wait, if org_id is 0, we can use that in queries: org_id = ? OR 0 = ?
$pdo->exec("UPDATE `users` SET `org_id` = 0 WHERE `id` = 1");

echo "Migration completed.\n";
