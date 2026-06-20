<?php
/**
 * IIMS v2.0 - Professional Invoice Print (A4)
 * Features: CGST/SGST/IGST, HSN, Split Payments, Loyalty, Coupons
 */
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';
use App\Models\Auth;
use App\Models\Helpers;
use App\Models\Database;

require_once __DIR__ . '/config/database.php';

$db = new Database();
$auth = new Auth($db);
if (!$auth->check()) die("Unauthorized Access");

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die("Invalid Invoice ID");

try {
    $invoice = $db->query("
        SELECT i.*, c.customer_name, c.mobile as customer_mobile, c.gst_number as customer_gst, c.address as customer_address, c.state as customer_state, u.name as cashier_name
        FROM invoices i LEFT JOIN customers c ON i.customer_id = c.id LEFT JOIN users u ON i.created_by = u.id
        WHERE i.id = ? LIMIT 1
    ", [$id])->fetch();
    if (!$invoice) die("Invoice not found");

    $items = $db->query("
        SELECT ii.*, p.product_name, p.sku, p.hsn_code, un.short_name as unit_name
        FROM invoice_items ii JOIN products p ON ii.product_id = p.id LEFT JOIN units un ON p.unit_id = un.id
        WHERE ii.invoice_id = ?
    ", [$id])->fetchAll();

    $company = $db->query("SELECT * FROM company_settings WHERE id = 1 LIMIT 1")->fetch();
    if (!$company) $company = ['company_name' => 'Grovixo', 'company_logo' => '', 'gst_number' => '', 'phone' => '', 'email' => '', 'address' => '', 'invoice_footer' => 'Thank you!', 'invoice_terms' => '', 'bank_name' => '', 'bank_account_no' => '', 'bank_ifsc' => '', 'upi_id' => ''];

    $payments = $db->query("SELECT * FROM invoice_payments WHERE invoice_id = ? AND status = 'ACTIVE'", [$id])->fetchAll();

} catch (Exception $e) { die("Error: " . $e->getMessage()); }

$isIGST = (int)($invoice['is_igst'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - <?php echo $invoice['invoice_no']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #fff; color: #212529; font-size: 13px; font-family: 'Segoe UI', Arial, sans-serif; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0,0,0,0.08); }
        .table th { background-color: #f8f9fa !important; color: #333 !important; border-bottom: 2px solid #dee2e6 !important; font-size: 11px; text-transform: uppercase; }
        .table td { font-size: 12px; }
        .brand-bar { background: linear-gradient(135deg, #2563eb, #6366f1); padding: 12px 20px; color: #fff; border-radius: 8px 8px 0 0; }
        @media print {
            .invoice-box { border: none; box-shadow: none; padding: 0; }
            .no-print { display: none; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .brand-bar { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        @media (max-width: 576px) {
            .invoice-box { padding: 15px !important; border: none !important; box-shadow: none !important; }
            .invoice-box .text-end { text-align: left !important; }
            .invoice-box .offset-6 { margin-left: 0 !important; }
        }
    </style>
</head>
<body>
<div class="container my-4 no-print text-end" style="max-width:800px;">
    <button class="btn btn-primary btn-sm" onclick="window.print();"><i class="fa-solid fa-print me-1"></i>Print</button>
    <a href="invoice_thermal.php?id=<?php echo $id; ?>" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-receipt me-1"></i>Thermal</a>
    <?php
    $waMsg = 'Invoice ' . $invoice['invoice_no'] . ' - Total: ' . Helpers::formatCurrency($invoice['grand_total']) . '. Thank you for your business!';
    ?>
    <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($waMsg); ?>" target="_blank" class="btn btn-success btn-sm"><i class="fa-brands fa-whatsapp me-1"></i>WhatsApp</a>
    <button class="btn btn-outline-secondary btn-sm" onclick="window.close();">Close</button>
</div>

<div class="invoice-box">
    <!-- Brand Header Bar -->
    <div class="brand-bar d-flex justify-content-between align-items-center mb-0">
        <div>
            <h4 class="fw-bold mb-0"><?php echo Helpers::sanitize($company['company_name']); ?></h4>
            <small class="opacity-75"><?php echo Helpers::sanitize($company['address'] ?? ''); ?></small>
        </div>
        <div class="text-end">
            <h5 class="mb-0"><?php echo $invoice['invoice_type']; ?> INVOICE</h5>
            <small class="opacity-75"><?php echo $invoice['invoice_no']; ?></small>
        </div>
    </div>

    <div class="p-3 bg-light border-bottom">
        <div class="row">
            <div class="col-6 small">
                <strong>Phone:</strong> <?php echo Helpers::sanitize($company['phone'] ?? ''); ?> |
                <strong>Email:</strong> <?php echo Helpers::sanitize($company['email'] ?? ''); ?>
                <?php if (!empty($company['gst_number'])): ?> | <strong>GSTIN:</strong> <?php echo Helpers::sanitize($company['gst_number']); ?><?php endif; ?>
            </div>
            <div class="col-6 text-end small">
                <strong>Date:</strong> <?php echo Helpers::formatDate($invoice['invoice_date']); ?>
                <?php if (!empty($invoice['due_date'])): ?> | <strong>Due:</strong> <?php echo Helpers::formatDate($invoice['due_date']); ?><?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row p-3 mb-3">
        <div class="col-6">
            <h6 class="fw-bold text-dark mb-2">BILLED TO:</h6>
            <?php if (!empty($invoice['customer_name'])): ?>
                <strong><?php echo Helpers::sanitize($invoice['customer_name']); ?></strong><br>
                <span class="text-secondary small">Mobile: <?php echo Helpers::sanitize($invoice['customer_mobile']); ?></span><br>
                <?php if (!empty($invoice['customer_gst'])): ?><span class="text-secondary small">GSTIN: <?php echo Helpers::sanitize($invoice['customer_gst']); ?></span><br><?php endif; ?>
                <?php if (!empty($invoice['customer_state'])): ?><span class="text-secondary small">State: <?php echo Helpers::sanitize($invoice['customer_state']); ?></span><br><?php endif; ?>
                <?php if (!empty($invoice['customer_address'])): ?><span class="text-secondary small"><?php echo Helpers::sanitize($invoice['customer_address']); ?></span><?php endif; ?>
            <?php else: ?>
                <span class="text-muted">Walk-in Customer</span>
            <?php endif; ?>
        </div>
        <div class="col-6 text-end">
            <p class="mb-1 small"><strong>Payment:</strong> <?php echo $invoice['payment_method']; ?></p>
            <p class="mb-1 small"><strong>Status:</strong>
                <span class="badge <?php echo $invoice['status'] === 'PAID' ? 'bg-success' : ($invoice['status'] === 'PARTIAL' ? 'bg-warning text-dark' : 'bg-danger'); ?>"><?php echo $invoice['status']; ?></span>
            </p>
            <p class="mb-1 small"><strong>Cashier:</strong> <?php echo Helpers::sanitize($invoice['cashier_name']); ?></p>
        </div>
    </div>

    <!-- Items Table with GST Breakdown -->
    <table class="table table-bordered mb-4">
        <thead>
            <tr class="align-middle">
                <th style="width:30px;">#</th>
                <th>Item</th>
                <th style="width:70px;">HSN</th>
                <th class="text-center" style="width:60px;">Qty</th>
                <th class="text-end" style="width:80px;">Rate</th>
                <th class="text-end" style="width:70px;">Disc</th>
                <th class="text-end" style="width:80px;">Taxable</th>
                <?php if ($isIGST): ?>
                    <th class="text-center" style="width:90px;">IGST</th>
                <?php else: ?>
                    <th class="text-center" style="width:80px;">CGST</th>
                    <th class="text-center" style="width:80px;">SGST</th>
                <?php endif; ?>
                <th class="text-end" style="width:90px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $idx => $item):
                $taxable = (float)$item['amount'] / (1 + (float)$item['gst'] / 100);
                $taxAmt = (float)$item['amount'] - $taxable;
            ?>
                <tr>
                    <td class="text-center"><?php echo $idx + 1; ?></td>
                    <td><strong><?php echo Helpers::sanitize($item['product_name']); ?></strong><br><span class="text-muted" style="font-size:10px;">SKU: <?php echo Helpers::sanitize($item['sku']); ?></span></td>
                    <td class="small"><?php echo Helpers::sanitize($item['hsn_code'] ?: '-'); ?></td>
                    <td class="text-center"><?php echo (float)$item['quantity'] . ' ' . ($item['unit_name'] ?: 'Pcs'); ?></td>
                    <td class="text-end"><?php echo Helpers::formatCurrency($item['rate']); ?></td>
                    <td class="text-end"><?php echo (float)$item['discount'] > 0 ? Helpers::formatCurrency($item['discount']) : '-'; ?></td>
                    <td class="text-end"><?php echo Helpers::formatCurrency($taxable); ?></td>
                    <?php if ($isIGST): ?>
                        <td class="text-center small"><?php echo (float)$item['igst']; ?>%<br><span class="text-muted"><?php echo Helpers::formatCurrency($taxAmt); ?></span></td>
                    <?php else: ?>
                        <td class="text-center small"><?php echo (float)$item['cgst']; ?>%<br><span class="text-muted"><?php echo Helpers::formatCurrency($taxAmt / 2); ?></span></td>
                        <td class="text-center small"><?php echo (float)$item['sgst']; ?>%<br><span class="text-muted"><?php echo Helpers::formatCurrency($taxAmt / 2); ?></span></td>
                    <?php endif; ?>
                    <td class="text-end fw-bold"><?php echo Helpers::formatCurrency($item['amount']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Totals -->
    <div class="row">
        <div class="col-7">
            <?php if (!empty($company['invoice_terms'])): ?>
                <div class="border rounded p-2 bg-light small mb-3">
                    <strong>Terms & Conditions:</strong><br>
                    <span class="text-muted" style="white-space:pre-line;"><?php echo Helpers::sanitize($company['invoice_terms']); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($company['bank_name'])): ?>
                <div class="border rounded p-2 bg-light small">
                    <strong>Bank Details:</strong><br>
                    Bank: <?php echo Helpers::sanitize($company['bank_name']); ?><br>
                    A/C: <?php echo Helpers::sanitize($company['bank_account_no'] ?? ''); ?><br>
                    IFSC: <?php echo Helpers::sanitize($company['bank_ifsc'] ?? ''); ?>
                    <?php if (!empty($company['upi_id'])): ?><br>UPI: <?php echo Helpers::sanitize($company['upi_id']); ?><?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-5">
            <div class="d-flex justify-content-between mb-1"><span class="text-secondary">Subtotal:</span><span><?php echo Helpers::formatCurrency($invoice['subtotal']); ?></span></div>
            <?php if (!$isIGST): ?>
                <div class="d-flex justify-content-between mb-1"><span class="text-secondary">CGST:</span><span><?php echo Helpers::formatCurrency($invoice['cgst_amount']); ?></span></div>
                <div class="d-flex justify-content-between mb-1"><span class="text-secondary">SGST:</span><span><?php echo Helpers::formatCurrency($invoice['sgst_amount']); ?></span></div>
            <?php else: ?>
                <div class="d-flex justify-content-between mb-1"><span class="text-secondary">IGST:</span><span><?php echo Helpers::formatCurrency($invoice['igst_amount']); ?></span></div>
            <?php endif; ?>
            <?php if ((float)$invoice['discount'] > 0): ?>
                <div class="d-flex justify-content-between mb-1"><span class="text-secondary">Discount:</span><span class="text-success">-<?php echo Helpers::formatCurrency($invoice['discount']); ?></span></div>
            <?php endif; ?>
            <?php if ((float)$invoice['coupon_discount'] > 0): ?>
                <div class="d-flex justify-content-between mb-1"><span class="text-secondary">Coupon Discount:</span><span class="text-success">-<?php echo Helpers::formatCurrency($invoice['coupon_discount']); ?></span></div>
            <?php endif; ?>
            <div class="d-flex justify-content-between mb-1"><span class="text-secondary">Round Off:</span><span><?php echo ($invoice['round_off'] >= 0 ? '+' : '') . Helpers::formatCurrency($invoice['round_off']); ?></span></div>

            <hr class="my-2">
            <div class="d-flex justify-content-between fw-bold fs-5 mb-3"><span>Total:</span><span><?php echo Helpers::formatCurrency($invoice['grand_total']); ?></span></div>

            <?php if (count($payments) > 1): ?>
                <div class="small fw-bold mb-1">Split Payments:</div>
                <?php foreach ($payments as $p): ?>
                    <div class="d-flex justify-content-between mb-1 small"><span class="text-secondary"><?php echo $p['payment_method']; ?>:</span><span><?php echo Helpers::formatCurrency($p['amount']); ?></span></div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="d-flex justify-content-between mb-1"><span class="text-secondary">Paid:</span><span><?php echo Helpers::formatCurrency($invoice['paid_amount']); ?></span></div>
            <?php endif; ?>

            <?php if ((float)$invoice['due_amount'] > 0): ?>
                <div class="d-flex justify-content-between mb-1 fw-semibold text-danger"><span>Balance Due:</span><span><?php echo Helpers::formatCurrency($invoice['due_amount']); ?></span></div>
            <?php endif; ?>

            <?php if ((int)$invoice['loyalty_points_earned'] > 0): ?>
                <div class="d-flex justify-content-between mb-1 small text-warning"><span>Loyalty Earned:</span><span>+<?php echo $invoice['loyalty_points_earned']; ?> pts</span></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Signature -->
    <div class="row mt-5 pt-4 text-center">
        <div class="col-6 offset-6">
            <div class="border-top border-dark mx-auto w-75 pt-2 small">
                <strong>For <?php echo Helpers::sanitize($company['company_name']); ?></strong>
                <span class="d-block text-secondary mt-1">Authorized Signatory</span>
            </div>
        </div>
    </div>

    <div class="text-center mt-4 text-muted small border-top pt-3">
        <?php echo Helpers::sanitize($company['invoice_footer'] ?? 'Thank you!'); ?>
    </div>
</div>
</body>
</html>
