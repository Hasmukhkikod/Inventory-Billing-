<?php
/**
 * IIMS v2.0 - Thermal Receipt (80mm/58mm POS Printer)
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
if ($id <= 0) die("Invalid Invoice ID");

$invoice = $db->query("
    SELECT i.*, c.customer_name, c.mobile as customer_mobile, u.name as cashier_name
    FROM invoices i LEFT JOIN customers c ON i.customer_id = c.id LEFT JOIN users u ON i.created_by = u.id
    WHERE i.id = ? LIMIT 1
", [$id])->fetch();
if (!$invoice) die("Invoice not found");

$items = $db->query("
    SELECT ii.*, p.product_name, un.short_name as unit_name,
           COALESCE(ii.billing_unit_name, un.short_name, 'Pcs') as display_unit, ii.primary_qty
    FROM invoice_items ii JOIN products p ON ii.product_id = p.id LEFT JOIN units un ON p.unit_id = un.id
    WHERE ii.invoice_id = ?
", [$id])->fetchAll();

$company = $db->query("SELECT * FROM company_settings WHERE id = 1 LIMIT 1")->fetch();
if (!$company) $company = ['company_name' => 'Grovixo', 'phone' => '', 'address' => '', 'gst_number' => '', 'invoice_footer' => 'Thank you!'];
$pos_template = $company['pos_template'] ?? 'pos_standard';
$thermal_width = $company['thermal_width'] ?? '80mm';
$pos_show_logo = (int)($company['pos_show_logo'] ?? 1);
$pos_show_cashier = (int)($company['pos_show_cashier'] ?? 1);
$pos_show_customer_mobile = (int)($company['pos_show_customer_mobile'] ?? 1);
$pos_show_hsn = (int)($company['pos_show_hsn'] ?? 0);
$pos_show_gst_breakdown = (int)($company['pos_show_gst_breakdown'] ?? 1);
$pos_header_text = trim($company['pos_header_text'] ?? '');
$pos_footer_text = trim($company['pos_footer_text'] ?? '') ?: ($company['invoice_footer'] ?? 'Thank you!');
$logoPath = !empty($company['company_logo']) && file_exists(UPLOAD_DIR . '/' . $company['company_logo']) ? BASE_URL . '/uploads/' . $company['company_logo'] : null;

$payments = $db->query("SELECT * FROM invoice_payments WHERE invoice_id = ? AND status = 'ACTIVE'", [$id])->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - <?php echo $invoice['invoice_no']; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; font-size: 12px; color: #000; background: #fff; }
        .receipt { width: 80mm; margin: 0 auto; padding: 3mm; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .small { font-size: 10px; }
        .divider { border-top: 1px dashed #000; margin: 4px 0; }
        .double-divider { border-top: 2px solid #000; margin: 4px 0; }
        .company-name { font-size: 16px; font-weight: bold; text-transform: uppercase; }
        .row { display: flex; justify-content: space-between; }
        .item-row { display: flex; justify-content: space-between; margin: 2px 0; }
        .item-name { flex: 1; }
        .item-detail { font-size: 10px; color: #555; padding-left: 8px; }
        table { width: 100%; border-collapse: collapse; }
        table th { text-align: left; font-size: 10px; border-bottom: 1px solid #000; padding: 2px 0; }
        table td { padding: 2px 0; font-size: 11px; }
        table .amt { text-align: right; }
        .total-line { font-size: 16px; font-weight: bold; }
        .no-print { text-align: center; padding: 10px; }
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
            .receipt { width: 100%; }
        }
        @media screen {
            .receipt { border: 1px solid #ccc; margin: 20px auto; padding: 10px; }
        }
        
        /* POS Themes */
        .receipt.pos_minimal { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        .receipt.pos_minimal .company-name { font-size: 18px; letter-spacing: 1px; border-bottom: 2px solid #000; padding-bottom: 5px; margin-bottom: 5px; }
        .receipt.pos_minimal .divider { border-top: 1px solid #ddd; }
        .receipt.pos_minimal .double-divider { display: none; }
        
        .receipt.pos_bold .company-name { display: none; }
        .receipt.pos_bold .header-box { background: #000; color: #fff; padding: 10px; text-align: center; font-weight: 900; font-size: 18px; text-transform: uppercase; margin-bottom: 10px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .receipt.pos_bold table th { background: #000; color: #fff; padding: 4px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .receipt.pos_bold .total-box { background: #000; color: #fff; padding: 8px; text-align: center; font-size: 16px; margin-top: 10px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    </style>
</head>
<body>
<div class="no-print">
    <button onclick="window.print();" style="padding:8px 20px;font-size:14px;cursor:pointer;">Print Receipt</button>
    <button onclick="downloadPDF();" style="padding:8px 20px;font-size:14px;cursor:pointer;background-color:#dc3545;color:white;border:none;border-radius:3px;">Download PDF</button>
    <button onclick="window.close();" style="padding:8px 20px;font-size:14px;cursor:pointer;">Close</button>
</div>
<div class="receipt <?php echo htmlspecialchars($pos_template); ?>" style="width: <?php echo htmlspecialchars($thermal_width); ?>;">
    
    <?php if ($pos_template === 'pos_bold'): ?>
        <div class="header-box">
            <?php if ($pos_show_logo && $logoPath): ?><img src="<?php echo $logoPath; ?>" alt="Logo" style="height:28px; margin-bottom:4px;"><?php endif; ?>
            <div><?php echo Helpers::sanitize($company['company_name']); ?></div>
            <div style="font-size:10px; font-weight:normal; margin-top:2px;"><?php echo Helpers::sanitize($company['address'] ?? ''); ?></div>
        </div>
    <?php endif; ?>

    <div class="text-center">
        <?php if ($pos_show_logo && $logoPath && $pos_template !== 'pos_bold'): ?>
            <img src="<?php echo $logoPath; ?>" alt="Logo" style="height:32px; margin-bottom:4px;">
        <?php endif; ?>
        <div class="company-name"><?php echo Helpers::sanitize($company['company_name']); ?></div>
        <?php if ($pos_template !== 'pos_bold'): ?>
        <div class="small"><?php echo Helpers::sanitize($company['address'] ?? ''); ?></div>
        <?php endif; ?>
        <div class="small">Ph: <?php echo Helpers::sanitize($company['phone'] ?? ''); ?></div>
        <?php if (!empty($company['gst_number'])): ?>
            <div class="small">GSTIN: <?php echo Helpers::sanitize($company['gst_number']); ?></div>
        <?php endif; ?>
        <?php if ($pos_header_text !== ''): ?>
            <div class="small bold" style="margin-top:2px;"><?php echo Helpers::sanitize($pos_header_text); ?></div>
        <?php endif; ?>
    </div>

    <div class="double-divider"></div>

    <div class="row"><span class="bold"><?php echo $invoice['invoice_type']; ?> INVOICE</span><span><?php echo $invoice['invoice_no']; ?></span></div>
    <div class="row small"><span>Date: <?php echo date('d/m/Y H:i', strtotime($invoice['created_at'])); ?></span></div>
    <?php if (!empty($invoice['customer_name'])): ?>
        <div class="row small"><span>Customer: <?php echo Helpers::sanitize($invoice['customer_name']); ?></span></div>
        <?php if ($pos_show_customer_mobile): ?>
        <div class="row small"><span>Mobile: <?php echo Helpers::sanitize($invoice['customer_mobile']); ?></span></div>
        <?php endif; ?>
    <?php endif; ?>
    <?php if ($pos_show_cashier): ?>
    <div class="row small"><span>Cashier: <?php echo Helpers::sanitize($invoice['cashier_name']); ?></span></div>
    <?php endif; ?>

    <div class="divider"></div>

    <table>
        <thead><tr><th>Item</th><th class="amt">Qty</th><th class="amt">Rate</th><th class="amt">Amt</th></tr></thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo Helpers::sanitize($item['product_name']); ?><?php if ($pos_show_hsn && !empty($item['hsn_code'])): ?><br><span class="small">HSN: <?php echo Helpers::sanitize($item['hsn_code']); ?></span><?php endif; ?></td>
                    <td class="amt"><?php echo (float)$item['quantity'] . ' ' . $item['display_unit']; ?><?php if (!empty($item['primary_qty']) && (float)$item['primary_qty'] != (float)$item['quantity']): ?><br><span class="small">(<?php echo (float)$item['primary_qty'] . ' ' . ($item['unit_name'] ?: 'Pcs'); ?>)</span><?php endif; ?></td>
                    <td class="amt"><?php echo number_format($item['rate'], 2); ?></td>
                    <td class="amt"><?php echo number_format($item['amount'], 2); ?></td>
                </tr>
                <?php if ((float)$item['gst'] > 0 || (float)$item['discount'] > 0): ?>
                    <tr><td colspan="4" class="item-detail">
                        <?php if ((float)$item['discount'] > 0) echo 'Disc: ₹' . number_format($item['discount'], 2) . ' '; ?>
                        <?php if ((float)$item['gst'] > 0) echo 'GST: ' . (float)$item['gst'] . '%'; ?>
                    </td></tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="divider"></div>

    <div class="row"><span>Subtotal:</span><span><?php echo Helpers::formatCurrency($invoice['subtotal']); ?></span></div>
    <?php $totalTax = (float)$invoice['cgst_amount'] + (float)$invoice['sgst_amount'] + (float)$invoice['igst_amount']; ?>
    <?php if ($pos_show_gst_breakdown): ?>
        <?php if ((float)$invoice['cgst_amount'] > 0): ?>
            <div class="row small"><span>CGST:</span><span><?php echo Helpers::formatCurrency($invoice['cgst_amount']); ?></span></div>
            <div class="row small"><span>SGST:</span><span><?php echo Helpers::formatCurrency($invoice['sgst_amount']); ?></span></div>
        <?php endif; ?>
        <?php if ((float)$invoice['igst_amount'] > 0): ?>
            <div class="row small"><span>IGST:</span><span><?php echo Helpers::formatCurrency($invoice['igst_amount']); ?></span></div>
        <?php endif; ?>
    <?php elseif ($totalTax > 0): ?>
        <div class="row small"><span>Tax:</span><span><?php echo Helpers::formatCurrency($totalTax); ?></span></div>
    <?php endif; ?>
    <?php if ((float)$invoice['discount'] > 0): ?>
        <div class="row"><span>Discount:</span><span>-<?php echo Helpers::formatCurrency($invoice['discount']); ?></span></div>
    <?php endif; ?>
    <?php if ((float)$invoice['coupon_discount'] > 0): ?>
        <div class="row"><span>Coupon:</span><span>-<?php echo Helpers::formatCurrency($invoice['coupon_discount']); ?></span></div>
    <?php endif; ?>
    <?php if ((float)$invoice['round_off'] != 0): ?>
        <div class="row small"><span>Round Off:</span><span><?php echo ($invoice['round_off'] >= 0 ? '+' : '') . Helpers::formatCurrency($invoice['round_off']); ?></span></div>
    <?php endif; ?>

    <div class="double-divider"></div>
    <div class="row total-line"><span>TOTAL:</span><span><?php echo Helpers::formatCurrency($invoice['grand_total']); ?></span></div>
    
    <?php if ($pos_template === 'pos_bold'): ?>
    <div class="total-box">TOTAL: <?php echo Helpers::formatCurrency($invoice['grand_total']); ?></div>
    <?php endif; ?>
    
    <div class="double-divider"></div>

    <?php if (count($payments) > 1): ?>
        <div class="small bold">Payments:</div>
        <?php foreach ($payments as $p): ?>
            <div class="row small"><span><?php echo $p['payment_method']; ?>:</span><span><?php echo Helpers::formatCurrency($p['amount']); ?></span></div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="row"><span>Paid (<?php echo $invoice['payment_method']; ?>):</span><span><?php echo Helpers::formatCurrency($invoice['paid_amount']); ?></span></div>
    <?php endif; ?>

    <?php if ((float)$invoice['due_amount'] > 0): ?>
        <div class="row bold"><span>Balance Due:</span><span><?php echo Helpers::formatCurrency($invoice['due_amount']); ?></span></div>
    <?php else: ?>
        <?php $change = (float)$invoice['paid_amount'] - (float)$invoice['grand_total']; if ($change > 0): ?>
            <div class="row"><span>Change:</span><span><?php echo Helpers::formatCurrency($change); ?></span></div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ((int)$invoice['loyalty_points_earned'] > 0): ?>
        <div class="divider"></div>
        <div class="row small"><span>Points Earned:</span><span>+<?php echo $invoice['loyalty_points_earned']; ?> pts</span></div>
    <?php endif; ?>

    <div class="divider"></div>
    <div class="text-center small" style="margin-top:6px;">
        <?php echo Helpers::sanitize($pos_footer_text); ?>
    </div>
    <div class="text-center small" style="margin-top:4px;">Powered by Grovixo IIMS</div>
</div>
<script>window.onload = function() { window.print(); };</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function downloadPDF() {
        const element = document.querySelector('.receipt');
        const opt = {
            margin: 0,
            filename: 'Receipt_<?php echo $invoice['invoice_no']; ?>.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'mm', format: [80, 200], orientation: 'portrait' }
        };
        html2pdf().set(opt).from(element).save();
    }
</script>
</body>
</html>
