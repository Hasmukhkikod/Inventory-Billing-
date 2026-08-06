<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';
use App\Models\Database;
session_start();
$_SESSION['is_demo'] = true;
$db = new Database();

$features = ['inventory', 'categories', 'brands', 'units', 'conversions', 'purchases', 'suppliers', 'billing', 'returns', 'quotations', 'challans', 'customers', 'expenses', 'reports', 'users', 'roles', 'coupons', 'theme', 'backups', 'printer', 'feedback'];
$featuresJson = json_encode($features);

// Update Default Plan features
$db->getConnection()->exec("UPDATE plans SET features = '" . $featuresJson . "' WHERE id = 1");

// Fix any organizations that were on plan 0 to plan 1
$db->getConnection()->exec("UPDATE organizations SET plan_id = 1 WHERE plan_id = 0");

echo "Plans and organizations updated successfully.\n";
