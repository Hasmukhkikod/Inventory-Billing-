<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';
use App\Models\Database;
session_start();
$_SESSION['is_demo'] = true;
$db = new Database();
$stmt = $db->query("SELECT * FROM plans");
print_r($stmt->fetchAll());
