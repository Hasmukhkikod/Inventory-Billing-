<?php
/**
 * IIMS v2.0 - Purchase Order Print Template
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
if ($id <= 0) die("Invalid Purchase Order ID");

$purchase = $db->query("
    SELECT p.*, s.supplier_name, s.mobile as supplier_mobile, s.gst_number as supplier_gst, s.address as supplier_address, u.name as created_by_name
    FROM purchases p LEFT JOIN suppliers s ON p.supplier_id = s.id LEFT JOIN users u ON p.created_by = u.id
    WHERE p.id = ? LIMIT 1
", [$id])->fetch();
if (!$purchase) die("Purchase Order not found");

$items = $db->query("
    SELECT pi.*, pr.product_name, pr.sku, pr.hsn_code, un.short_name as unit_name,
           COALESCE(pi.billing_unit_name, un.short_name, 'Pcs') as display_unit, pi.primary_qty
    FROM purchase_items pi JOIN products pr ON pi.product_id = pr.id LEFT JOIN units un ON pr.unit_id = un.id
    WHERE pi.purchase_id = ?
", [$id])->fetchAll();

$company = $db->query("SELECT * FROM company_settings WHERE id = 1 LIMIT 1")->fetch();
if (!$company) $company = ['company_name' => 'Grovixo', 'phone' => '', 'email' => '', 'address' => '', 'gst_number' => ''];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order - <?php echo $purchase['purchase_no']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/templates.css">
    <style>
        body { background: #fff; color: #212529; font-size: 13px; font-family: 'Segoe UI', Arial, sans-serif; }
        .box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; }
        .brand-bar { background: linear-gradient(135deg, #059669, #10b981); padding: 12px 20px; color: #fff; border-radius: 8px 8px 0 0; }
        .table th { background-color: #f8f9fa !important; font-size: 11px; text-transform: uppercase; }
        @media print { .box { border: none; padding: 0; } .no-print { display: none; } body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
    </style>
</head>
<body>
<div class="container my-4 no-print text-end" style="max-width:800px;">
    <button class="btn btn-primary btn-sm" onclick="window.print();"><i class="fa-solid fa-print me-1"></i>Print</button>
    <button class="btn btn-danger btn-sm" onclick="downloadPDF();"><i class="fa-solid fa-file-pdf me-1"></i>PDF</button>
    <button class="btn btn-outline-secondary btn-sm" onclick="window.close();">Close</button>
</div>
<?php $theme = $company['invoice_template'] ?? 'standard'; ?>
<div class="box theme-<?php echo htmlspecialchars($theme); ?>">
    <div class="brand-bar d-flex justify-content-between align-items-center mb-0">
        <div><h4 class="fw-bold mb-0"><?php echo Helpers::sanitize($company['company_name']); ?></h4><small class="opacity-75"><?php echo Helpers::sanitize($company['address'] ?? ''); ?></small></div>
        <div class="text-end"><h5 class="mb-0">PURCHASE ORDER</h5><small class="opacity-75"><?php echo $purchase['purchase_no']; ?></small></div>
    </div>
    <div class="p-3 bg-light border-bottom small">
        <strong>Phone:</strong> <?php echo Helpers::sanitize($company['phone'] ?? ''); ?> | <strong>Email:</strong> <?php echo Helpers::sanitize($company['email'] ?? ''); ?>
        <?php if (!empty($company['gst_number'])): ?> | <strong>GSTIN:</strong> <?php echo Helpers::sanitize($company['gst_number']); ?><?php endif; ?>
    </div>
    <div class="row p-3 mb-3">
        <div class="col-6">
            <h6 class="text-muted mb-1 border-bottom pb-1">Supplier Details</h6>
            <strong><?php echo Helpers::sanitize($purchase['supplier_name']); ?></strong><br>
            <?php echo nl2br(Helpers::sanitize($purchase['supplier_address'])); ?><br>
            <?php if (!empty($purchase['supplier_mobile'])): ?>Ph: <?php echo $purchase['supplier_mobile']; ?><br><?php endif; ?>
            <?php if (!empty($purchase['supplier_gst'])): ?>GSTIN: <?php echo $purchase['supplier_gst']; ?><br><?php endif; ?>
        </div>
        <div class="col-6 text-end">
            <h6 class="text-muted mb-1 border-bottom pb-1 text-end">Order Details</h6>
            Date: <strong><?php echo Helpers::formatDate($purchase['purchase_date']); ?></strong><br>
            Expected Delivery: <strong><?php echo !empty($purchase['expected_delivery_date']) ? Helpers::formatDate($purchase['expected_delivery_date']) : '-'; ?></strong><br>
            Created By: <?php echo Helpers::sanitize($purchase['created_by_name']); ?>
        </div>
    </div>

    <table class="table table-sm mb-4">
        <thead>
            <tr>
                <th>#</th>
                <th>Item</th>
                <th>HSN</th>
                <th class="text-center">Qty</th>
                <th class="text-end">Price</th>
                <th class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $sn = 1; foreach ($items as $item): ?>
                <tr>
                    <td><?php echo $sn++; ?></td>
                    <td>
                        <strong><?php echo Helpers::sanitize($item['product_name']); ?></strong><br>
                        <small class="text-muted"><?php echo $item['sku']; ?></small>
                    </td>
                    <td><?php echo $item['hsn_code']; ?></td>
                    <td class="text-center"><?php echo $item['quantity'] . ' ' . $item['display_unit']; ?></td>
                    <td class="text-end"><?php echo Helpers::formatCurrency($item['cost_price']); ?></td>
                    <td class="text-end"><?php echo Helpers::formatCurrency($item['total_price']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="row">
        <div class="col-6 offset-6">
            <div class="d-flex justify-content-between mb-1"><span class="text-secondary">Subtotal:</span><span><?php echo Helpers::formatCurrency($purchase['subtotal']); ?></span></div>
            <div class="d-flex justify-content-between mb-1"><span class="text-secondary">GST:</span><span><?php echo Helpers::formatCurrency($purchase['gst_amount']); ?></span></div>
            <hr class="my-2">
            <div class="d-flex justify-content-between fw-bold fs-5"><span>Total:</span><span><?php echo Helpers::formatCurrency($purchase['grand_total']); ?></span></div>
        </div>
    </div>

    <div class="row mt-5 pt-4 text-center">
        <div class="col-6 offset-6">
            <div class="border-top border-dark mx-auto w-75 pt-2 small">
                <strong>For <?php echo Helpers::sanitize($company['company_name']); ?></strong>
                <span class="d-block text-secondary mt-1">Authorized Signatory</span>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function downloadPDF() {
        const element = document.querySelector('.box');
        const opt = {
            margin: 0.2,
            filename: 'PO_<?php echo $purchase['purchase_no']; ?>.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
        };
        html2pdf().set(opt).from(element).save();
    }
</script>
</body>
</html>
