<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';
use App\Models\Database;
session_start();
$_SESSION['is_demo'] = true;
$db = new Database();
$stmt = $db->query("SELECT * FROM plans WHERE id = 1");
print_r($stmt->fetch());
