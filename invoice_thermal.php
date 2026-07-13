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

// Default/fallback receipt paper width in dots @ 203dpi, set from Settings >
// Printer Settings. Older rows may still hold a legacy '80mm'/'58mm' string
// from before that control stored an exact dot width - normalize both.
$raw_thermal_width = trim((string)($company['thermal_width'] ?? '576'));
if ($raw_thermal_width === '58mm') {
    $default_width_dots = 384;
} elseif ($raw_thermal_width === '80mm' || $raw_thermal_width === '') {
    $default_width_dots = 576;
} else {
    $default_width_dots = max(128, min(1200, (int)$raw_thermal_width));
}
$thermal_width_mm = round($default_width_dots / (203 / 25.4), 1) . 'mm';
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
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table th { text-align: left; font-size: 10px; border-bottom: 1px solid #000; padding: 2px 3px; }
        table td { padding: 2px 3px; font-size: 11px; word-break: break-word; }
        table th:first-child, table td:first-child { padding-left: 0; }
        table th:last-child, table td:last-child { padding-right: 0; }
        table .amt { text-align: right; font-size: 10px; }
        /* Item name gets the most room; Qty/Amount are fixed-width numeric
           columns so long product names can never push amounts off/together.
           Rate moves to the detail line below the item instead of its own
           column - a 4-column layout doesn't reliably fit full currency values
           on narrow (58mm) paper. */
        table th:nth-child(1), table td:nth-child(1) { width: 38%; }
        table th:nth-child(2), table td:nth-child(2) { width: 24%; }
        table th:nth-child(3), table td:nth-child(3) { width: 38%; }
        .item-detail { font-size: 10px; color: #555; padding: 0 3px 4px 0; }
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
    <?php echo Helpers::csrfField(); ?>

    <!-- Print confirmation card: shown once the default printer (Settings -> Printer
         Settings) is known. The receipt below this card is the print preview - what
         you see is exactly what gets sent. -->
    <div id="print-confirm-card" style="display:none; max-width:360px; margin:0 auto 16px; padding:18px 20px; border:1px solid #e2e8f0; border-radius:10px; background:#f8fafc; text-align:center;">
        <div style="font-size:13px; color:#64748b; font-weight:600; letter-spacing:0.4px; text-transform:uppercase;">Print Receipt</div>
        <div id="confirm-printer-line" style="font-size:15px; font-weight:700; color:#0f172a; margin:4px 0 14px;"></div>
        <button id="btn-confirm-print" style="width:100%; padding:11px; font-size:15px; font-weight:600; cursor:pointer; background-color:#4f46e5; color:white; border:none; border-radius:6px;">🖨️ Print Now</button>
        <div style="margin-top:10px; font-size:12px;">
            <label style="cursor:pointer; color:#555;">
                <input type="checkbox" id="chk-auto-print" style="vertical-align:middle;">
                Always print automatically on load
            </label>
        </div>
        <div style="margin-top:8px;"><a href="#" id="link-more-options" style="font-size:12px; color:#4f46e5; text-decoration:none;">Use a different printer ▾</a></div>
    </div>

    <div id="no-printer-card" style="display:none; max-width:360px; margin:0 auto 16px; padding:16px 20px; border:1px solid #fde68a; border-radius:10px; background:#fffbeb; text-align:center; font-size:13px; color:#92400e;">
        No printer configured yet.
        <a href="<?php echo BASE_URL; ?>/settings/index.php" target="_blank" style="color:#4f46e5;">Add one in Printer Settings</a>,
        or print this once using the options below.
    </div>

    <div id="manual-print-options" style="display:none;">
        <button onclick="window.print();" style="padding:8px 16px;font-size:14px;cursor:pointer;">Print Receipt</button>
        <button id="btn-usb-print" style="padding:8px 16px;font-size:14px;cursor:pointer;background-color:#4f46e5;color:white;border:none;border-radius:3px;">🖨️ USB</button>
        <button id="btn-bt-print" style="padding:8px 16px;font-size:14px;cursor:pointer;background-color:#2563eb;color:white;border:none;border-radius:3px;">🔵 Bluetooth</button>
        <button id="btn-lan-print" style="padding:8px 16px;font-size:14px;cursor:pointer;background-color:#0891b2;color:white;border:none;border-radius:3px;">📶 WiFi/LAN</button>

        <div style="margin-top:10px; font-size:12px; color:#555; display:flex; align-items:center; justify-content:center; gap:8px; flex-wrap:wrap;">
            <label for="sel-printer-width">Printer width:</label>
            <select id="sel-printer-width" style="padding:3px 6px; font-size:12px;">
                <option value="384">384 dots (58mm)</option>
                <option value="576">576 dots (80mm - most common)</option>
                <option value="512">512 dots (80mm - some models)</option>
                <option value="832">832 dots (80mm - high-res)</option>
                <option value="custom">Custom…</option>
            </select>
            <input type="number" id="inp-width-custom" min="128" max="1200" step="8" placeholder="dots" style="display:none; width:70px; padding:3px 6px; font-size:12px;">
        </div>

        <div style="margin-top:8px; font-size:12px; color:#555; display:flex; align-items:center; justify-content:center; gap:6px; flex-wrap:wrap;">
            <label for="inp-lan-ip">WiFi/LAN printer:</label>
            <input type="text" id="inp-lan-ip" placeholder="192.168.1.50" style="width:110px; padding:3px 6px; font-size:12px;">
            <span>:</span>
            <input type="number" id="inp-lan-port" placeholder="9100" style="width:60px; padding:3px 6px; font-size:12px;">
        </div>
        <div style="color:#888; margin-top:6px; max-width:420px; margin-left:auto; margin-right:auto; font-size:11px;">
            Click USB/Bluetooth once first to select your printer - after that it's remembered on this device/browser.
        </div>
    </div>

    <div id="print-status" style="margin:8px 0; font-size:12px; min-height:16px;"></div>

    <div style="margin-top:8px;">
        <button onclick="downloadPDF();" style="padding:8px 16px;font-size:14px;cursor:pointer;background-color:#dc3545;color:white;border:none;border-radius:3px;">Download PDF</button>
        <button onclick="window.close();" style="padding:8px 16px;font-size:14px;cursor:pointer;">Close</button>
    </div>
</div>
<div class="receipt <?php echo htmlspecialchars($pos_template); ?>" style="width: <?php echo htmlspecialchars($thermal_width_mm); ?>;">
    
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
        <thead><tr><th>Item</th><th class="amt">Qty</th><th class="amt">Amount</th></tr></thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo Helpers::sanitize($item['product_name']); ?><?php if ($pos_show_hsn && !empty($item['hsn_code'])): ?><br><span class="small">HSN: <?php echo Helpers::sanitize($item['hsn_code']); ?></span><?php endif; ?></td>
                    <td class="amt"><?php echo (float)$item['quantity'] . ' ' . $item['display_unit']; ?><?php if (!empty($item['primary_qty']) && (float)$item['primary_qty'] != (float)$item['quantity']): ?><br><span class="small">(<?php echo (float)$item['primary_qty'] . ' ' . ($item['unit_name'] ?: 'Pcs'); ?>)</span><?php endif; ?></td>
                    <td class="amt"><?php echo number_format($item['amount'], 2); ?></td>
                </tr>
                <tr><td colspan="3" class="item-detail">
                    @ <?php echo Helpers::formatCurrency($item['rate']); ?>
                    <?php if ((float)$item['discount'] > 0) echo ' &middot; Disc: ' . Helpers::formatCurrency($item['discount']); ?>
                    <?php if ((float)$item['gst'] > 0) echo ' &middot; GST: ' . (float)$item['gst'] . '%'; ?>
                </td></tr>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<!-- html2pdf's bundle doesn't expose html2canvas as a standalone global, but the
     USB print path needs it directly (to rasterize the receipt without generating
     a PDF first), so it's loaded separately here. -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/thermal-printer.js?v=<?php echo \App\Models\Helpers::assetVersion('/assets/js/thermal-printer.js'); ?>"></script>
<script>
    // Server-computed fallback (from Settings' saved thermal width), used until
    // the user picks/confirms an exact width for their specific printer.
    const DEFAULT_PRINTER_WIDTH_DOTS = <?php echo (int)$default_width_dots; ?>;
    const DOTS_PER_MM = 203 / 25.4; // 203dpi is the near-universal ESC/POS thermal printer resolution
    const CSRF_TOKEN = document.querySelector('input[name="csrf_token"]').value;

    function savePrinterPref(patch) {
        GrovixoThermalPrinter.setPreference(patch);
    }

    function applyPreviewWidth(widthDots) {
        // Reflect the selected printer width in the on-screen receipt too, so the
        // preview honestly matches what will come out of that specific machine.
        const mm = (widthDots / DOTS_PER_MM).toFixed(1);
        document.querySelector('.receipt').style.width = mm + 'mm';
    }

    function getSelectedWidthDots() {
        const sel = document.getElementById('sel-printer-width');
        if (sel.value === 'custom') {
            const custom = parseInt(document.getElementById('inp-width-custom').value, 10);
            return (custom && custom > 0) ? custom : DEFAULT_PRINTER_WIDTH_DOTS;
        }
        return parseInt(sel.value, 10);
    }

    (function initWidthControl() {
        const sel = document.getElementById('sel-printer-width');
        const customInput = document.getElementById('inp-width-custom');
        const saved = GrovixoThermalPrinter.getPreference();
        const initialWidth = (saved && saved.widthDots) ? saved.widthDots : DEFAULT_PRINTER_WIDTH_DOTS;

        const presetValues = ['384', '576', '512', '832'];
        if (presetValues.includes(String(initialWidth))) {
            sel.value = String(initialWidth);
        } else {
            sel.value = 'custom';
            customInput.style.display = 'inline-block';
            customInput.value = initialWidth;
        }
        applyPreviewWidth(initialWidth);

        function onWidthChange() {
            customInput.style.display = (sel.value === 'custom') ? 'inline-block' : 'none';
            const widthDots = getSelectedWidthDots();
            savePrinterPref({ widthDots: widthDots });
            applyPreviewWidth(widthDots);
        }

        sel.addEventListener('change', onWidthChange);
        customInput.addEventListener('input', onWidthChange);
    })();

    function downloadPDF() {
        const element = document.querySelector('.receipt');
        // Match the PDF page width to whichever printer width is currently
        // selected/previewed, instead of always assuming 80mm.
        const widthMm = getSelectedWidthDots() / DOTS_PER_MM;
        const heightMm = Math.max(200, (element.offsetHeight / element.offsetWidth) * widthMm + 20);
        const opt = {
            margin: 0,
            filename: 'Receipt_<?php echo $invoice['invoice_no']; ?>.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'mm', format: [widthMm, heightMm], orientation: 'portrait' }
        };
        html2pdf().set(opt).from(element).save();
    }

    function setStatus(text, color) {
        const el = document.getElementById('print-status');
        el.textContent = text;
        el.style.color = color || '#555';
    }

    async function doPrintUsb(widthDots) {
        setStatus('Connecting to USB printer…');
        try {
            await GrovixoThermalPrinter.printViaUsb(document.querySelector('.receipt'), { widthDots: widthDots });
            setStatus('Sent to USB printer.', '#059669');
            return true;
        } catch (err) {
            console.error(err);
            setStatus('USB print failed: ' + err.message, '#dc2626');
            return false;
        }
    }

    async function doPrintBluetooth(widthDots) {
        setStatus('Connecting over Bluetooth…');
        try {
            await GrovixoThermalPrinter.printViaBluetooth(document.querySelector('.receipt'), { widthDots: widthDots });
            setStatus('Sent to Bluetooth printer.', '#059669');
            return true;
        } catch (err) {
            console.error(err);
            setStatus('Bluetooth print failed: ' + err.message, '#dc2626');
            return false;
        }
    }

    async function doPrintLan(ip, port, widthDots) {
        if (!ip) {
            setStatus('Enter the printer\'s IP address first.', '#dc2626');
            return false;
        }
        setStatus('Sending to ' + ip + ':' + port + '…');
        try {
            await GrovixoThermalPrinter.printViaLan(document.querySelector('.receipt'), {
                widthDots: widthDots, ip: ip, port: port,
                baseUrl: '<?php echo BASE_URL; ?>', csrfToken: CSRF_TOKEN
            });
            setStatus('Sent to network printer.', '#059669');
            return true;
        } catch (err) {
            console.error(err);
            setStatus('WiFi/LAN print failed: ' + err.message, '#dc2626');
            return false;
        }
    }

    // Manual/fallback buttons - print via a specific transport using the width
    // and (for LAN) IP entered in the "Use a different printer" section.
    document.getElementById('btn-usb-print').addEventListener('click', () => doPrintUsb(getSelectedWidthDots()));
    document.getElementById('btn-bt-print').addEventListener('click', () => doPrintBluetooth(getSelectedWidthDots()));
    document.getElementById('btn-lan-print').addEventListener('click', () => {
        const ip = document.getElementById('inp-lan-ip').value.trim();
        const port = parseInt(document.getElementById('inp-lan-port').value, 10) || 9100;
        savePrinterPref({ lanIp: ip, lanPort: port });
        doPrintLan(ip, port, getSelectedWidthDots());
    });

    if (!GrovixoThermalPrinter.isUsbSupported()) {
        document.getElementById('btn-usb-print').disabled = true;
        document.getElementById('btn-usb-print').title = 'Direct USB printing needs Chrome or Edge.';
    }
    if (!GrovixoThermalPrinter.isBluetoothSupported()) {
        document.getElementById('btn-bt-print').disabled = true;
        document.getElementById('btn-bt-print').title = 'Bluetooth printing needs Chrome or Edge, with Bluetooth turned on.';
    }

    // Remembered fallback-section values - opt-in, per browser/device.
    const localPref = GrovixoThermalPrinter.getPreference();
    if (localPref && localPref.lanIp) document.getElementById('inp-lan-ip').value = localPref.lanIp;
    if (localPref && localPref.lanPort) document.getElementById('inp-lan-port').value = localPref.lanPort;

    const autoChk = document.getElementById('chk-auto-print');
    autoChk.checked = !!(localPref && localPref.autoPrint);
    autoChk.addEventListener('change', function () {
        savePrinterPref({ autoPrint: this.checked });
    });

    document.getElementById('link-more-options').addEventListener('click', function (e) {
        e.preventDefault();
        document.getElementById('manual-print-options').style.display = 'block';
        this.style.display = 'none';
    });

    // ---- Default printer (Settings -> Printer Settings) drives the main
    // confirmation card: "Print on [Name]" + the receipt below it as the preview.
    let defaultPrinter = null;

    async function printViaDefaultPrinter() {
        if (!defaultPrinter) return false;
        const widthDots = defaultPrinter.paper_width_dots || DEFAULT_PRINTER_WIDTH_DOTS;
        if (defaultPrinter.connection_type === 'USB') return doPrintUsb(widthDots);
        if (defaultPrinter.connection_type === 'BLUETOOTH') return doPrintBluetooth(widthDots);
        if (defaultPrinter.connection_type === 'LAN') return doPrintLan(defaultPrinter.ip_address, defaultPrinter.port, widthDots);
        return false;
    }
    document.getElementById('btn-confirm-print').addEventListener('click', printViaDefaultPrinter);

    fetch('<?php echo BASE_URL; ?>/api/printers.php?action=get_default')
        .then(r => r.json())
        .then(function (res) {
            defaultPrinter = (res.status && res.data.printer) ? res.data.printer : null;

            if (defaultPrinter) {
                const typeLabel = { USB: 'USB', BLUETOOTH: 'Bluetooth', LAN: 'WiFi/LAN' }[defaultPrinter.connection_type];
                document.getElementById('confirm-printer-line').textContent = defaultPrinter.name + ' (' + typeLabel + ')';
                document.getElementById('print-confirm-card').style.display = 'block';
                applyPreviewWidth(defaultPrinter.paper_width_dots || DEFAULT_PRINTER_WIDTH_DOTS);

                if (autoChk.checked) {
                    printViaDefaultPrinter().then(function (ok) { if (!ok) window.print(); });
                }
            } else {
                document.getElementById('no-printer-card').style.display = 'block';
                document.getElementById('manual-print-options').style.display = 'block';
                window.print();
            }
        })
        .catch(function () {
            document.getElementById('manual-print-options').style.display = 'block';
            window.print();
        });
</script>
</body>
</html>
