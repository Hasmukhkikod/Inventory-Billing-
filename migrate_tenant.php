<?php
require_once 'vendor/autoload.php';
require_once 'config/config.php';
require_once 'application/Models/Database.php';

function migrateDb($dbName) {
    echo "Starting migration for $dbName...\n";
    try {
        $db = new \App\Models\Database($dbName);
        $pdo = $db->getConnection();
    } catch (Exception $e) {
        echo "Could not connect to $dbName: " . $e->getMessage() . "\n";
        return;
    }

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

    foreach ($tables as $table) {
        try {
            $pdo->exec("ALTER TABLE `$table` ADD COLUMN `org_id` int(11) DEFAULT 1;");
            $pdo->exec("ALTER TABLE `$table` ADD INDEX `idx_org_id` (`org_id`);");
            echo "Added org_id to $table\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                // Ignore
            } else {
                echo "Error on $table: " . $e->getMessage() . "\n";
            }
        }
    }

    // Ensure Super Admin (id=1) has org_id = 0 so they can see everything.
    try {
        $pdo->exec("UPDATE `users` SET `org_id` = 0 WHERE `id` = 1");
    } catch (Exception $e) {
        // Ignore
    }

    echo "Migration completed for $dbName.\n\n";
}

migrateDb('main');
migrateDb('demo');

