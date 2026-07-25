<?php
require_once 'vendor/autoload.php';
require_once 'config/config.php';
require_once 'application/Models/Database.php';

$db = new \App\Models\Database();
$pdo = $db->getConnection();

$pdo->exec("RENAME TABLE products TO t_products;");
$pdo->exec("
CREATE VIEW products AS 
SELECT * FROM t_products 
WHERE org_id = @current_org_id OR @current_org_id = 0;
");

$pdo->exec("
CREATE TRIGGER tr_products_b_ins BEFORE INSERT ON t_products FOR EACH ROW
BEGIN
    IF NEW.org_id IS NULL OR NEW.org_id = 0 THEN
        SET NEW.org_id = @current_org_id;
    END IF;
END;
");

$pdo->exec("SET @current_org_id = 1;");
// Should work
$stmt = $pdo->query("SELECT COUNT(*) FROM products");
echo "Count for org 1: " . $stmt->fetchColumn() . "\n";

$pdo->exec("SET @current_org_id = 2;");
$stmt = $pdo->query("SELECT COUNT(*) FROM products");
echo "Count for org 2: " . $stmt->fetchColumn() . "\n";

// Insert
$pdo->exec("SET @current_org_id = 2;");
$pdo->exec("INSERT INTO products (product_name, sku, category_id, brand_id, status) VALUES ('Test Org 2', 'SKU-001', 1, 1, 'ACTIVE')");

$stmt = $pdo->query("SELECT COUNT(*) FROM products");
echo "Count for org 2 after insert: " . $stmt->fetchColumn() . "\n";

$pdo->exec("SET @current_org_id = 0;");
$stmt = $pdo->query("SELECT COUNT(*) FROM products");
echo "Count for org 0 (Super Admin): " . $stmt->fetchColumn() . "\n";

