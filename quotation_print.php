<?php
/**
 * IIMS v2.0 - Quotation Print Template
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
if ($id <= 0) die("Invalid Quotation ID");

$quotation = $db->query("
    SELECT q.*, c.customer_name, c.mobile as customer_mobile, c.gst_number as customer_gst, c.address as customer_address, u.name as created_by_name
    FROM quotations q LEFT JOIN customers c ON q.customer_id = c.id LEFT JOIN users u ON q.created_by = u.id
    WHERE q.id = ? LIMIT 1
", [$id])->fetch();
if (!$quotation) die("Quotation not found");

$items = $db->query("
    SELECT qi.*, p.product_name, p.sku, p.hsn_code, un.short_name as unit_name,
           COALESCE(qi.billing_unit_name, un.short_name, 'Pcs') as display_unit, qi.primary_qty
    FROM quotation_items qi JOIN products p ON qi.product_id = p.id LEFT JOIN units un ON p.unit_id = un.id
    WHERE qi.quotation_id = ?
", [$id])->fetchAll();

$company = $db->query("SELECT * FROM company_settings WHERE id = 1 LIMIT 1")->fetch();
if (!$company) $company = ['company_name' => 'Grovixo', 'phone' => '', 'email' => '', 'address' => '', 'gst_number' => ''];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation - <?php echo $quotation['quotation_no']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #fff; color: #212529; font-size: 13px; font-family: 'Segoe UI', Arial, sans-serif; }
        .box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; }
        .brand-bar { background: linear-gradient(135deg, #6366f1, #8b5cf6); padding: 12px 20px; color: #fff; border-radius: 8px 8px 0 0; }
        .table th { background-color: #f8f9fa !important; font-size: 11px; text-transform: uppercase; }
        @media print { .box { border: none; padding: 0; } .no-print { display: none; } body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
    </style>
</head>
<body>
<div class="container my-4 no-print text-end" style="max-width:800px;">
    <button class="btn btn-primary btn-sm" onclick="window.print();"><i class="fa-solid fa-print me-1"></i>Print</button>
    <button class="btn btn-outline-secondary btn-sm" onclick="window.close();">Close</button>
</div>
<div class="box">
    <div class="brand-bar d-flex justify-content-between align-items-center mb-0">
        <div><h4 class="fw-bold mb-0"><?php echo Helpers::sanitize($company['company_name']); ?></h4><small class="opacity-75"><?php echo Helpers::sanitize($company['address'] ?? ''); ?></small></div>
        <div class="text-end"><h5 class="mb-0">QUOTATION</h5><small class="opacity-75"><?php echo $quotation['quotation_no']; ?></small></div>
    </div>
    <div class="p-3 bg-light border-bottom small">
        <strong>Phone:</strong> <?php echo Helpers::sanitize($company['phone'] ?? ''); ?> | <strong>Email:</strong> <?php echo Helpers::sanitize($company['email'] ?? ''); ?>
        <?php if (!empty($company['gst_number'])): ?> | <strong>GSTIN:</strong> <?php echo Helpers::sanitize($company['gst_number']); ?><?php endif; ?>
    </div>
    <div class="row p-3 mb-3">
        <div class="col-6">
            <h6 class="fw-bold">QUOTATION FOR:</h6>
            <?php if (!empty($quotation['customer_name'])): ?>
                <strong><?php echo Helpers::sanitize($quotation['customer_name']); ?></strong><br>
                <span class="text-secondary small">Mobile: <?php echo Helpers::sanitize($quotation['customer_mobile']); ?></span>
                <?php if (!empty($quotation['customer_gst'])): ?><br><span class="small">GSTIN: <?php echo Helpers::sanitize($quotation['customer_gst']); ?></span><?php endif; ?>
                <?php if (!empty($quotation['customer_address'])): ?><br><span class="small text-secondary"><?php echo Helpers::sanitize($quotation['customer_address']); ?></span><?php endif; ?>
            <?php else: ?>
                <span class="text-muted">-</span>
            <?php endif; ?>
        </div>
        <div class="col-6 text-end">
            <p class="small mb-1"><strong>Date:</strong> <?php echo Helpers::formatDate($quotation['quotation_date']); ?></p>
            <?php if (!empty($quotation['valid_until'])): ?><p class="small mb-1"><strong>Valid Until:</strong> <?php echo Helpers::formatDate($quotation['valid_until']); ?></p><?php endif; ?>
            <p class="small mb-1"><strong>Status:</strong> <?php echo $quotation['status']; ?></p>
        </div>
    </div>

    <table class="table table-bordered mb-4">
        <thead><tr><th>#</th><th>Item</th><th>HSN</th><th class="text-center">Qty</th><th class="text-end">Rate</th><th class="text-center">GST%</th><th class="text-end">Disc%</th><th class="text-end">Amount</th></tr></thead>
        <tbody>
            <?php foreach ($items as $idx => $item): ?>
                <tr>
                    <td><?php echo $idx + 1; ?></td>
                    <td><strong><?php echo Helpers::sanitize($item['product_name']); ?></strong><br><span class="text-muted" style="font-size:10px;">SKU: <?php echo Helpers::sanitize($item['sku']); ?></span></td>
                    <td class="small"><?php echo Helpers::sanitize($item['hsn_code'] ?: '-'); ?></td>
                    <td class="text-center"><?php echo (float)$item['quantity'] . ' ' . $item['display_unit']; ?><?php if (!empty($item['primary_qty']) && (float)$item['primary_qty'] != (float)$item['quantity']): ?><br><span class="text-muted" style="font-size:10px;">(<?php echo (float)$item['primary_qty'] . ' ' . ($item['unit_name'] ?: 'Pcs'); ?>)</span><?php endif; ?></td>
                    <td class="text-end"><?php echo Helpers::formatCurrency($item['rate']); ?></td>
                    <td class="text-center"><?php echo (float)$item['gst']; ?>%</td>
                    <td class="text-end"><?php echo (float)$item['discount'] > 0 ? (float)$item['discount'] . '%' : '-'; ?></td>
                    <td class="text-end fw-bold"><?php echo Helpers::formatCurrency($item['amount']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="row">
        <div class="col-7">
            <?php if (!empty($quotation['notes'])): ?>
                <div class="border rounded p-2 bg-light small"><strong>Notes:</strong><br><span class="text-muted"><?php echo nl2br(Helpers::sanitize($quotation['notes'])); ?></span></div>
            <?php endif; ?>
            <?php if (!empty($company['invoice_terms'])): ?>
                <div class="border rounded p-2 bg-light small mt-2"><strong>Terms:</strong><br><span class="text-muted" style="white-space:pre-line;"><?php echo Helpers::sanitize($company['invoice_terms']); ?></span></div>
            <?php endif; ?>
        </div>
        <div class="col-5">
            <div class="d-flex justify-content-between mb-1"><span class="text-secondary">Subtotal:</span><span><?php echo Helpers::formatCurrency($quotation['subtotal']); ?></span></div>
            <?php if ((float)$quotation['discount'] > 0): ?>
                <div class="d-flex justify-content-between mb-1"><span class="text-secondary">Discount:</span><span class="text-success">-<?php echo Helpers::formatCurrency($quotation['discount']); ?></span></div>
            <?php endif; ?>
            <div class="d-flex justify-content-between mb-1"><span class="text-secondary">GST:</span><span><?php echo Helpers::formatCurrency($quotation['gst_amount']); ?></span></div>
            <hr class="my-2">
            <div class="d-flex justify-content-between fw-bold fs-5"><span>Total:</span><span><?php echo Helpers::formatCurrency($quotation['grand_total']); ?></span></div>
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
    <div class="text-center mt-4 text-muted small border-top pt-3">This is a quotation and not a tax invoice. Prices are subject to change.</div>
</div>
</body>
</html>
