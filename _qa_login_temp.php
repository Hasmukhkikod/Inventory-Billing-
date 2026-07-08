<?php
// TEMPORARY QA helper - to be deleted after mobile responsive verification.
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';
use App\Models\Auth;
use App\Models\Database;

$db = new Database();
$auth = new Auth($db);
$auth->login('mobile-qa-temp@example.com', 'TempVerify123!');
$target = $_GET['to'] ?? 'index.php';
header('Location: ' . $target);
exit;
