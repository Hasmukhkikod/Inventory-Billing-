<?php
require_once 'vendor/autoload.php';
require_once 'config/config.php';
require_once 'application/Models/Database.php';

$db = new \App\Models\Database();
$pdo = $db->getConnection();

try {
    $pdo->exec("DROP VIEW IF EXISTS products;");
    $pdo->exec("RENAME TABLE t_products TO products;");
    echo "Fixed.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
