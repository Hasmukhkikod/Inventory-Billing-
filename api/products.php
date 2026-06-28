<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Products & Inventory CRUD API (Part 2 Database updates)
 */

require_once __DIR__ . '/../vendor/autoload.php';
use App\Models\Auth;
use App\Models\Helpers;
use App\Models\Database;

require_once __DIR__ . '/../config/database.php';

$db = new Database();
$auth = new Auth($db);
$auth->requirePermission('Manage Inventory');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        try {
            $stmt = $db->query("
                SELECT p.*, c.category_name, b.brand_name, u.short_name as unit_name,
                       su.short_name as secondary_unit_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                LEFT JOIN units u ON p.unit_id = u.id
                LEFT JOIN units su ON p.secondary_unit_id = su.id
                WHERE p.status = 'ACTIVE' AND (p.deleted_at IS NULL)
                ORDER BY p.product_name ASC
            ");
            Helpers::jsonResponse(true, "Products list loaded", $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed: " . $e->getMessage());
        }
        break;

    case 'get':
        $id = (int)($_GET['id'] ?? 0);
        try {
            $product = $db->query("
                SELECT p.*, u.short_name as unit_name, su.short_name as secondary_unit_name
                FROM products p
                LEFT JOIN units u ON p.unit_id = u.id
                LEFT JOIN units su ON p.secondary_unit_id = su.id
                WHERE p.id = ? LIMIT 1
            ", [$id])->fetch();
            if ($product) {
                Helpers::jsonResponse(true, "Product loaded", $product);
            } else {
                Helpers::jsonResponse(false, "Product not found");
            }
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Error: " . $e->getMessage());
        }
        break;

    case 'save':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, "Method not allowed");
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, "CSRF verification failed.");

        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['product_name'] ?? '');
        $sku = trim($_POST['sku'] ?? '');
        $barcode = trim($_POST['barcode'] ?? '');
        $hsn_code = trim($_POST['hsn_code'] ?? '');
        $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $brand_id = !empty($_POST['brand_id']) ? (int)$_POST['brand_id'] : null;
        $unit_id = !empty($_POST['unit_id']) ? (int)$_POST['unit_id'] : null;
        $secondary_unit_id = !empty($_POST['secondary_unit_id']) ? (int)$_POST['secondary_unit_id'] : null;
        $conversion_factor = !empty($_POST['conversion_factor']) ? (float)$_POST['conversion_factor'] : null;
        if ($secondary_unit_id && $unit_id && $secondary_unit_id === $unit_id) {
            $secondary_unit_id = null;
            $conversion_factor = null;
        }
        if (!$secondary_unit_id) $conversion_factor = null;
        $cost_price = (float)($_POST['cost_price'] ?? 0);
        $selling_price = (float)($_POST['selling_price'] ?? 0);
        $gst_percentage = (float)($_POST['gst_percentage'] ?? 0);
        $minimum_stock = (float)($_POST['minimum_stock'] ?? 0);
        $initial_stock = (float)($_POST['opening_stock'] ?? 0);

        if (empty($name) || empty($sku)) {
            Helpers::jsonResponse(false, "Product Name and SKU are required.");
        }

        if ($cost_price < 0 || $selling_price < 0) {
            Helpers::jsonResponse(false, "Prices cannot be negative.");
        }

        // SKU Unique Check
        $skuCheckQuery = ($id > 0) 
            ? "SELECT id FROM products WHERE sku = ? AND id != ? LIMIT 1"
            : "SELECT id FROM products WHERE sku = ? LIMIT 1";
        $skuParams = ($id > 0) ? [$sku, $id] : [$sku];
        if ($db->query($skuCheckQuery, $skuParams)->fetch()) {
            Helpers::jsonResponse(false, "Product with this SKU already exists.");
        }

        // Barcode Unique Check
        if (!empty($barcode)) {
            $barcodeCheckQuery = ($id > 0) 
                ? "SELECT id FROM products WHERE barcode = ? AND id != ? LIMIT 1"
                : "SELECT id FROM products WHERE barcode = ? LIMIT 1";
            $barcodeParams = ($id > 0) ? [$barcode, $id] : [$barcode];
            if ($db->query($barcodeCheckQuery, $barcodeParams)->fetch()) {
                Helpers::jsonResponse(false, "Product with this Barcode already exists.");
            }
        }

        // Image upload
        $imagePath = $_POST['existing_image'] ?? '';
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['product_image']['tmp_name'];
            $fileName = $_FILES['product_image']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($fileExtension, $allowedExtensions)) {
                $newFileName = md5(time() . $sku) . '.' . $fileExtension;
                $destPath = UPLOAD_DIR . '/' . $newFileName;
                
                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $imagePath = 'uploads/' . $newFileName;
                }
            } else {
                Helpers::jsonResponse(false, "Invalid image format.");
            }
        }

        try {
            if ($id > 0) {
                // Update
                $db->query("
                    UPDATE products
                    SET category_id = ?, brand_id = ?, unit_id = ?, secondary_unit_id = ?, conversion_factor = ?,
                        sku = ?, barcode = ?, hsn_code = ?,
                        product_name = ?, cost_price = ?, selling_price = ?, gst_percentage = ?,
                        minimum_stock = ?, image = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ", [$category_id, $brand_id, $unit_id, $secondary_unit_id, $conversion_factor, $sku, $barcode, $hsn_code, $name, $cost_price, $selling_price, $gst_percentage, $minimum_stock, $imagePath, $id]);

                Helpers::logActivity($db, "inventory", "Updated product ID: $id ($name)", $id);
                Helpers::jsonResponse(true, "Product details updated.");
            } else {
                // Insert Product & Transaction
                $db->transaction(function($t) use ($category_id, $brand_id, $unit_id, $secondary_unit_id, $conversion_factor, $sku, $barcode, $hsn_code, $name, $cost_price, $selling_price, $gst_percentage, $initial_stock, $minimum_stock, $imagePath) {
                    $productId = $t->insert("
                        INSERT INTO products (category_id, brand_id, unit_id, secondary_unit_id, conversion_factor, sku, barcode, hsn_code, product_name, cost_price, selling_price, gst_percentage, opening_stock, current_stock, minimum_stock, image, status, created_by)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'ACTIVE', ?)
                    ", [$category_id, $brand_id, $unit_id, $secondary_unit_id, $conversion_factor, $sku, $barcode, $hsn_code, $name, $cost_price, $selling_price, $gst_percentage, $initial_stock, $initial_stock, $minimum_stock, $imagePath, $_SESSION['user_id']]);

                    if ($initial_stock > 0) {
                        $t->insert("
                            INSERT INTO stock_transactions (product_id, transaction_type, reference_no, quantity, stock_before, stock_after, remarks, created_by) 
                            VALUES (?, 'Adjustment', 'Initial', ?, 0.00, ?, 'Initial Stock Entry', ?)
                        ", [$productId, $initial_stock, $initial_stock, $_SESSION['user_id']]);
                    }
                    Helpers::logActivity($db, "inventory", "Created product: $name", $productId);
                });

                Helpers::jsonResponse(true, "Product created successfully.");
            }
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed to save product: " . $e->getMessage());
        }
        break;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) Helpers::jsonResponse(false, "Invalid product ID.");

        try {
            // Soft delete by setting deleted_at or deactivating
            $db->query("UPDATE products SET status = 'INACTIVE', deleted_at = CURRENT_TIMESTAMP WHERE id = ?", [$id]);
            Helpers::logActivity($db, "inventory", "Product de-activated / soft-deleted ID: $id", $id);
            Helpers::jsonResponse(true, "Product marked as Inactive.");
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed: " . $e->getMessage());
        }
        break;

    case 'adjust_stock':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, "Invalid method");
        
        $productId = (int)($_POST['product_id'] ?? 0);
        $type = trim($_POST['adjustment_type'] ?? ''); // IN, OUT
        $qty = (float)($_POST['quantity'] ?? 0);
        $remarks = trim($_POST['remarks'] ?? '');

        if ($productId <= 0 || !in_array($type, ['IN', 'OUT']) || $qty <= 0) {
            Helpers::jsonResponse(false, "Validation failed: Check inputs.");
        }

        try {
            $product = $db->query("SELECT current_stock, product_name, cost_price FROM products WHERE id = ? LIMIT 1", [$productId])->fetch();
            if (!$product) Helpers::jsonResponse(false, "Product not found.");

            $oldStock = (float)$product['current_stock'];
            $newStock = ($type === 'IN') ? ($oldStock + $qty) : ($oldStock - $qty);

            if ($newStock < 0) {
                Helpers::jsonResponse(false, "Stock cannot fall below zero. Available: $oldStock");
            }

            $db->transaction(function($t) use ($productId, $type, $qty, $oldStock, $newStock, $remarks) {
                // Log Stock transaction with before/after levels!
                $t->insert("
                    INSERT INTO stock_transactions (product_id, transaction_type, quantity, stock_before, stock_after, remarks, created_by) 
                    VALUES (?, 'Adjustment', ?, ?, ?, ?, ?)
                ", [$productId, ($type === 'IN' ? $qty : -$qty), $oldStock, $newStock, $remarks, $_SESSION['user_id']]);

                // Update product current stock
                $t->query("UPDATE products SET current_stock = ? WHERE id = ?", [$newStock, $productId]);
            });

            Helpers::logActivity($db, "inventory", "Stock adjustment for: " . $product['product_name'] . " ($type $qty)", $productId);
            Helpers::jsonResponse(true, "Stock adjusted successfully. Current: $newStock");
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Adjustment failed: " . $e->getMessage());
        }
        break;

    // CATEGORIES CRUD
    case 'categories_list':
        try {
            $stmt = $db->query("SELECT * FROM categories WHERE deleted_at IS NULL ORDER BY category_name ASC");
            Helpers::jsonResponse(true, "Categories loaded", $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, $e->getMessage());
        }
        break;

    case 'category_save':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, "Invalid method");
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, "CSRF verification failed");
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['category_name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        if (empty($name)) Helpers::jsonResponse(false, "Category Name is required.");

        try {
            if ($id > 0) {
                $db->query("UPDATE categories SET category_name = ?, description = ? WHERE id = ?", [$name, $desc, $id]);
                Helpers::jsonResponse(true, "Category updated.", ['id' => $id]);
            } else {
                $newId = $db->insert("INSERT INTO categories (category_name, description) VALUES (?, ?)", [$name, $desc]);
                Helpers::jsonResponse(true, "Category added.", ['id' => (int)$newId]);
            }
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed: " . $e->getMessage());
        }
        break;

    // BRANDS CRUD
    case 'brands_list':
        try {
            $stmt = $db->query("SELECT * FROM brands WHERE deleted_at IS NULL ORDER BY brand_name ASC");
            Helpers::jsonResponse(true, "Brands loaded", $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, $e->getMessage());
        }
        break;

    case 'brand_save':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, "Invalid method");
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, "CSRF verification failed");
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['brand_name'] ?? '');
        if (empty($name)) Helpers::jsonResponse(false, "Brand Name is required.");

        try {
            if ($id > 0) {
                $db->query("UPDATE brands SET brand_name = ? WHERE id = ?", [$name, $id]);
                Helpers::jsonResponse(true, "Brand updated.", ['id' => $id]);
            } else {
                $newId = $db->insert("INSERT INTO brands (brand_name) VALUES (?)", [$name]);
                Helpers::jsonResponse(true, "Brand added.", ['id' => (int)$newId]);
            }
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed: " . $e->getMessage());
        }
        break;

    // UNITS CRUD
    case 'units_list':
        try {
            $stmt = $db->query("SELECT * FROM units WHERE deleted_at IS NULL ORDER BY unit_name ASC");
            Helpers::jsonResponse(true, "Units loaded", $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, $e->getMessage());
        }
        break;

    case 'unit_save':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, "Invalid method");
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, "CSRF verification failed");
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['unit_name'] ?? '');
        $short = trim($_POST['short_name'] ?? '');
        if (empty($name) || empty($short)) Helpers::jsonResponse(false, "Unit details are required.");

        try {
            if ($id > 0) {
                $db->query("UPDATE units SET unit_name = ?, short_name = ? WHERE id = ?", [$name, $short, $id]);
                Helpers::jsonResponse(true, "Unit updated.", ['id' => $id]);
            } else {
                $newId = $db->insert("INSERT INTO units (unit_name, short_name) VALUES (?, ?)", [$name, $short]);
                Helpers::jsonResponse(true, "Unit added.", ['id' => (int)$newId]);
            }
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed: " . $e->getMessage());
        }
        break;

    // UNIT CONVERSIONS CRUD
    case 'unit_conversions_list':
        try {
            $stmt = $db->query("
                SELECT uc.*, pu.unit_name as primary_unit_name, pu.short_name as primary_short_name,
                       su.unit_name as secondary_unit_name, su.short_name as secondary_short_name
                FROM unit_conversions uc
                JOIN units pu ON uc.primary_unit_id = pu.id
                JOIN units su ON uc.secondary_unit_id = su.id
                WHERE uc.deleted_at IS NULL
                ORDER BY pu.unit_name ASC
            ");
            Helpers::jsonResponse(true, "Unit conversions loaded", $stmt->fetchAll());
        } catch (Exception $e) {
            Helpers::jsonResponse(false, $e->getMessage());
        }
        break;

    case 'unit_conversion_save':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, "Invalid method");
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, "CSRF verification failed");

        $id = (int)($_POST['id'] ?? 0);
        $primary_uid = (int)($_POST['primary_unit_id'] ?? 0);
        $secondary_uid = (int)($_POST['secondary_unit_id'] ?? 0);
        $factor = (float)($_POST['conversion_factor'] ?? 0);

        if ($primary_uid <= 0 || $secondary_uid <= 0 || $factor <= 0) {
            Helpers::jsonResponse(false, "All fields are required and conversion factor must be > 0.");
        }
        if ($primary_uid === $secondary_uid) {
            Helpers::jsonResponse(false, "Primary and secondary units must be different.");
        }

        try {
            if ($id > 0) {
                $db->query("UPDATE unit_conversions SET primary_unit_id = ?, secondary_unit_id = ?, conversion_factor = ? WHERE id = ?",
                    [$primary_uid, $secondary_uid, $factor, $id]);
                Helpers::jsonResponse(true, "Conversion updated.", ['id' => $id]);
            } else {
                $existing = $db->query("SELECT id FROM unit_conversions WHERE primary_unit_id = ? AND secondary_unit_id = ? AND deleted_at IS NULL LIMIT 1",
                    [$primary_uid, $secondary_uid])->fetch();
                if ($existing) {
                    Helpers::jsonResponse(false, "This unit conversion pair already exists.");
                }
                $newId = $db->insert("INSERT INTO unit_conversions (primary_unit_id, secondary_unit_id, conversion_factor, created_by) VALUES (?, ?, ?, ?)",
                    [$primary_uid, $secondary_uid, $factor, $_SESSION['user_id']]);
                Helpers::jsonResponse(true, "Conversion added.", ['id' => (int)$newId]);
            }
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed: " . $e->getMessage());
        }
        break;

    case 'unit_conversion_delete':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, "Invalid method");
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) Helpers::jsonResponse(false, "Invalid ID.");
        try {
            $db->query("UPDATE unit_conversions SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?", [$id]);
            Helpers::jsonResponse(true, "Conversion deleted.");
        } catch (Exception $e) {
            Helpers::jsonResponse(false, "Failed: " . $e->getMessage());
        }
        break;

    case 'get_conversion':
        $primary_uid = (int)($_GET['primary_unit_id'] ?? 0);
        $secondary_uid = (int)($_GET['secondary_unit_id'] ?? 0);
        try {
            $conv = $db->query("SELECT * FROM unit_conversions WHERE primary_unit_id = ? AND secondary_unit_id = ? AND deleted_at IS NULL LIMIT 1",
                [$primary_uid, $secondary_uid])->fetch();
            if ($conv) {
                Helpers::jsonResponse(true, "Conversion found", $conv);
            } else {
                Helpers::jsonResponse(false, "No conversion found");
            }
        } catch (Exception $e) {
            Helpers::jsonResponse(false, $e->getMessage());
        }
        break;

    case 'bulk':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, 'Method not allowed');
        if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, 'CSRF verification failed');

        $bulk_action = trim($_POST['bulk_action'] ?? '');
        $ids = json_decode($_POST['ids'] ?? '[]', true);

        if (empty($ids)) Helpers::jsonResponse(false, 'No records selected');

        try {
            if ($bulk_action === 'delete') {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $db->query("UPDATE products SET status = 'INACTIVE', deleted_at = CURRENT_TIMESTAMP WHERE id IN ($placeholders)", $ids);
                Helpers::logActivity($db, 'products', 'Bulk deleted ' . count($ids) . ' records');
                Helpers::jsonResponse(true, count($ids) . ' records deleted successfully');
            }
            Helpers::jsonResponse(false, 'Unknown bulk action');
        } catch (Exception $e) {
            Helpers::jsonResponse(false, 'Bulk action failed: ' . $e->getMessage());
        }
        break;

    default:
        Helpers::jsonResponse(false, "Invalid Action: " . $action);
}
