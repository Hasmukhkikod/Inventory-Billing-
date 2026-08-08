<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Distributor/Partner Portal - Logout
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
use App\Models\Database;
use App\Models\DistributorAuth;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = new Database();
$distAuth = new DistributorAuth($db);
$distAuth->logout();

header("Location: " . BASE_URL . "/partner/login");
exit;
