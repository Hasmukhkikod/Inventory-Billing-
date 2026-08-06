<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';
use App\Models\Database;
session_start();
$_SESSION['is_demo'] = true;
$db = new Database();
try {
    $stmt = $db->query("SHOW CREATE TABLE system_announcements");
    print_r($stmt->fetch());
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
