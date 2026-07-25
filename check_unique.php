<?php
require_once 'vendor/autoload.php';
require_once 'config/config.php';
require_once 'application/Models/Database.php';

$db = new \App\Models\Database();
$pdo = $db->getConnection();

$stmt = $pdo->query("SHOW CREATE TABLE users");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo $row['Create Table'] . "\n\n";

$stmt2 = $pdo->query("SHOW CREATE TABLE organizations");
$row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
echo $row2['Create Table'] . "\n";
