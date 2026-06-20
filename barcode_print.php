<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Barcode Label Print Page
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';

use App\Models\Auth;
use App\Models\Helpers;
use App\Models\Database;

require_once __DIR__ . '/config/database.php';

$db = new Database();
$auth = new Auth($db);
if (!$auth->check()) die("Unauthorized");

$id = (int)($_GET['id'] ?? 0);
$qty = (int)($_GET['qty'] ?? 12);

$product = $db->query("SELECT * FROM products WHERE id = ? LIMIT 1", [$id])->fetch();
if (!$product) die("Product not found");

$company = $db->query("SELECT company_name FROM company_settings WHERE id = 1 LIMIT 1")->fetch();
$barcodeValue = !empty($product['barcode']) ? $product['barcode'] : $product['sku'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcode Labels - <?php echo Helpers::sanitize($product['product_name']); ?></title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            padding: 20px;
        }
        .controls {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px 20px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .controls label {
            font-weight: 600;
            font-size: 14px;
        }
        .controls input[type="number"] {
            width: 80px;
            padding: 6px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 14px;
        }
        .controls button {
            padding: 8px 20px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
        }
        .btn-print {
            background: #4f46e5;
            color: #ffffff;
        }
        .btn-print:hover { background: #4338ca; }
        .btn-back {
            background: #e2e8f0;
            color: #334155;
        }
        .btn-back:hover { background: #cbd5e1; }
        .barcode-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        .barcode-label {
            border: 1px dashed #94a3b8;
            border-radius: 6px;
            padding: 10px 8px;
            text-align: center;
            background: #ffffff;
            page-break-inside: avoid;
        }
        .barcode-label .product-name {
            font-size: 10px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .barcode-label svg {
            display: block;
            margin: 0 auto;
            max-width: 100%;
        }
        .barcode-label .product-price {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 4px;
        }

        @media print {
            body { padding: 0; background: #ffffff; }
            .controls { display: none; }
            .barcode-grid { gap: 2px; }
            .barcode-label {
                border: 1px dashed #999;
                border-radius: 0;
                padding: 6px 4px;
            }
            @page { margin: 5mm; }
        }
    </style>
</head>
<body>
    <div class="controls">
        <label for="labelQty">Labels:</label>
        <input type="number" id="labelQty" value="<?php echo $qty; ?>" min="1" max="500">
        <button class="btn-print" onclick="updateLabels()">Regenerate</button>
        <button class="btn-print" onclick="window.print()">Print Labels</button>
        <button class="btn-back" onclick="history.back()">Back</button>
    </div>

    <div class="barcode-grid" id="barcodeGrid">
        <!-- Labels generated via JS -->
    </div>

    <script>
        const productName = <?php echo json_encode(Helpers::sanitize($product['product_name'])); ?>;
        const barcodeValue = <?php echo json_encode($barcodeValue); ?>;
        const sellingPrice = <?php echo json_encode(number_format($product['selling_price'], 2)); ?>;

        function generateLabels(count) {
            const grid = document.getElementById('barcodeGrid');
            grid.innerHTML = '';

            for (let i = 0; i < count; i++) {
                const label = document.createElement('div');
                label.className = 'barcode-label';

                const nameEl = document.createElement('div');
                nameEl.className = 'product-name';
                nameEl.textContent = productName;
                label.appendChild(nameEl);

                const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                svg.setAttribute('class', 'barcode-svg');
                label.appendChild(svg);

                const priceEl = document.createElement('div');
                priceEl.className = 'product-price';
                priceEl.textContent = '₹ ' + sellingPrice;
                label.appendChild(priceEl);

                grid.appendChild(label);

                try {
                    JsBarcode(svg, barcodeValue, {
                        format: 'CODE128',
                        width: 1.5,
                        height: 40,
                        displayValue: true,
                        fontSize: 11,
                        margin: 2,
                        textMargin: 2
                    });
                } catch (e) {
                    svg.innerHTML = '<text x="10" y="20" fill="red" font-size="10">Invalid barcode</text>';
                }
            }
        }

        function updateLabels() {
            const qty = parseInt(document.getElementById('labelQty').value) || 12;
            generateLabels(qty);
        }

        // Generate initial labels
        generateLabels(<?php echo $qty; ?>);
    </script>
</body>
</html>
