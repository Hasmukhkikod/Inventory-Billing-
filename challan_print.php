<?php
/**
 * IIMS v2.0 - Delivery Challan Print Template
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
if ($id <= 0) die("Invalid Challan ID");

$challan = $db->query("
    SELECT ch.*, c.customer_name, c.mobile as customer_mobile, c.address as customer_address, u.name as created_by_name
    FROM challans ch LEFT JOIN customers c ON ch.customer_id = c.id LEFT JOIN users u ON ch.created_by = u.id
    WHERE ch.id = ? LIMIT 1
", [$id])->fetch();
if (!$challan) die("Challan not found");

$items = $db->query("
    SELECT ci.*, p.product_name, p.sku, p.hsn_code, un.short_name as unit_name,
           COALESCE(ci.billing_unit_name, un.short_name, 'Pcs') as display_unit, ci.primary_qty
    FROM challan_items ci JOIN products p ON ci.product_id = p.id LEFT JOIN units un ON p.unit_id = un.id
    WHERE ci.challan_id = ?
", [$id])->fetchAll();

$company = $db->query("SELECT * FROM company_settings WHERE id = 1 LIMIT 1")->fetch();
if (!$company) $company = ['company_name' => 'Grovixo', 'phone' => '', 'email' => '', 'address' => '', 'gst_number' => ''];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Challan - <?php echo $challan['challan_no']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/templates.css">
    <style>
        body { background: #fff; color: #212529; font-size: 13px; font-family: 'Segoe UI', Arial, sans-serif; }
        .box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; }
        .brand-bar { background: linear-gradient(135deg, #10b981, #059669); padding: 12px 20px; color: #fff; border-radius: 8px 8px 0 0; }
        .table th { background-color: #f8f9fa !important; font-size: 11px; text-transform: uppercase; }
        @media print { .box { border: none; padding: 0; } .no-print { display: none; } body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
    </style>
</head>
<body>
<div class="container my-4 no-print text-end" style="max-width:800px;">
    <button class="btn btn-primary btn-sm" onclick="window.print();">Print</button>
    <button class="btn btn-danger btn-sm" onclick="downloadPDF();"><i class="fa-solid fa-file-pdf me-1"></i>PDF</button>
    <button class="btn btn-outline-secondary btn-sm" onclick="window.close();">Close</button>
</div>
<?php $theme = $company['invoice_template'] ?? 'standard'; ?>
<div class="box theme-<?php echo htmlspecialchars($theme); ?>">
    <div class="brand-bar d-flex justify-content-between align-items-center mb-0">
        <div><h4 class="fw-bold mb-0"><?php echo Helpers::sanitize($company['company_name']); ?></h4><small class="opacity-75"><?php echo Helpers::sanitize($company['address'] ?? ''); ?></small></div>
        <div class="text-end"><h5 class="mb-0">DELIVERY CHALLAN</h5><small class="opacity-75"><?php echo $challan['challan_no']; ?></small></div>
    </div>
    <div class="p-3 bg-light border-bottom small">
        <strong>Phone:</strong> <?php echo Helpers::sanitize($company['phone'] ?? ''); ?> | <strong>Email:</strong> <?php echo Helpers::sanitize($company['email'] ?? ''); ?>
        <?php if (!empty($company['gst_number'])): ?> | <strong>GSTIN:</strong> <?php echo Helpers::sanitize($company['gst_number']); ?><?php endif; ?>
    </div>

    <div class="row p-3 mb-3">
        <div class="col-6">
            <h6 class="fw-bold">DELIVER TO:</h6>
            <?php if (!empty($challan['customer_name'])): ?>
                <strong><?php echo Helpers::sanitize($challan['customer_name']); ?></strong><br>
                <span class="text-secondary small">Mobile: <?php echo Helpers::sanitize($challan['customer_mobile']); ?></span>
                <?php if (!empty($challan['customer_address'])): ?><br><span class="small text-secondary"><?php echo Helpers::sanitize($challan['customer_address']); ?></span><?php endif; ?>
            <?php else: ?><span class="text-muted">-</span><?php endif; ?>
        </div>
        <div class="col-6 text-end">
            <p class="small mb-1"><strong>Date:</strong> <?php echo Helpers::formatDate($challan['challan_date']); ?></p>
            <p class="small mb-1"><strong>Status:</strong> <?php echo $challan['status']; ?></p>
            <?php if (!empty($challan['transport_name'])): ?><p class="small mb-1"><strong>Transport:</strong> <?php echo Helpers::sanitize($challan['transport_name']); ?></p><?php endif; ?>
            <?php if (!empty($challan['vehicle_no'])): ?><p class="small mb-1"><strong>Vehicle:</strong> <?php echo Helpers::sanitize($challan['vehicle_no']); ?></p><?php endif; ?>
        </div>
    </div>

    <table class="table table-bordered mb-4">
        <thead><tr><th>#</th><th>Item</th><th>HSN</th><th class="text-center">Quantity</th></tr></thead>
        <tbody>
            <?php foreach ($items as $idx => $item): ?>
                <tr>
                    <td><?php echo $idx + 1; ?></td>
                    <td><strong><?php echo Helpers::sanitize($item['product_name']); ?></strong><br><span class="text-muted" style="font-size:10px;">SKU: <?php echo Helpers::sanitize($item['sku']); ?></span></td>
                    <td class="small"><?php echo Helpers::sanitize($item['hsn_code'] ?: '-'); ?></td>
                    <td class="text-center fw-bold"><?php echo (float)$item['quantity'] . ' ' . $item['display_unit']; ?><?php if (!empty($item['primary_qty']) && (float)$item['primary_qty'] != (float)$item['quantity']): ?><br><span class="text-muted" style="font-size:10px;">(<?php echo (float)$item['primary_qty'] . ' ' . ($item['unit_name'] ?: 'Pcs'); ?>)</span><?php endif; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (!empty($challan['notes'])): ?>
        <div class="border rounded p-2 bg-light small mb-4"><strong>Notes:</strong><br><span class="text-muted"><?php echo nl2br(Helpers::sanitize($challan['notes'])); ?></span></div>
    <?php endif; ?>

    <div class="row mt-5 pt-4">
        <div class="col-6 text-center">
            <div class="border-top border-dark mx-auto w-75 pt-2 small"><strong>Received By</strong><span class="d-block text-secondary mt-1">Signature & Stamp</span></div>
        </div>
        <div class="col-6 text-center">
            <div class="border-top border-dark mx-auto w-75 pt-2 small"><strong>For <?php echo Helpers::sanitize($company['company_name']); ?></strong><span class="d-block text-secondary mt-1">Authorized Signatory</span></div>
        </div>
    </div>
    <div class="text-center mt-4 text-muted small border-top pt-3">This is a delivery challan for goods dispatch. Not a tax invoice.</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function downloadPDF() {
        const element = document.querySelector('.box');
        const opt = {
            margin: 0.2,
            filename: 'Challan_<?php echo $challan['challan_no']; ?>.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
        };
        html2pdf().set(opt).from(element).save();
    }
</script>
</body>
</html>
