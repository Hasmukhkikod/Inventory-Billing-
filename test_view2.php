<?php
require_once 'vendor/autoload.php';
require_once 'config/config.php';
require_once 'application/Models/Database.php';

$db = new \App\Models\Database();
$pdo = $db->getConnection();

$pdo->exec("DROP VIEW IF EXISTS products;");
$pdo->exec("DROP FUNCTION IF EXISTS current_org_id;");
$pdo->exec("CREATE FUNCTION current_org_id() RETURNS INT NO SQL RETURN @current_org_id;");

$pdo->exec("
CREATE VIEW products AS 
SELECT * FROM t_products 
WHERE org_id = current_org_id() OR current_org_id() = 0;
");

$pdo->exec("SET @current_org_id = 1;");
$stmt = $pdo->query("SELECT COUNT(*) FROM products");
echo "Count for org 1: " . $stmt->fetchColumn() . "\n";
