<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Logout Controller
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';
use App\Models\Auth;
use App\Models\Helpers;
use App\Models\Database;

$db = new Database();
$auth = new Auth($db);
$auth->logout();

header("Location: login");
exit;
