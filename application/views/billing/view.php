<?php
/**
 * IIMS v2.0 - Invoice Detail View
 * Shows: GST breakdown (CGST/SGST/IGST), Split payments, Coupons, Loyalty
 */
$payments = $this->db->query("SELECT * FROM invoice_payments WHERE invoice_id = ? AND status = 'ACTIVE'", [(int)$invoice['id']])->fetchAll();
$isIGST = (int)($invoice['is_igst'] ?? 0);
?>
<div class="row g-4 justify-content-center">
    <div class="col-md-9 col-lg-8">
        <div class="panel-card mb-4 no-print">
            <div class="panel-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-indigo"><i class="fa-solid fa-file-invoice me-2"></i>Invoice Details</h5>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="<?php echo BASE_URL; ?>/invoice_print.php?id=<?php echo $invoice['id']; ?>" target="_blank" class="btn btn-primary btn-sm"><i class="fa-solid fa-print me-1"></i>Print A4</a>
                    <a href="<?php echo BASE_URL; ?>/invoice_thermal.php?id=<?php echo $invoice['id']; ?>" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-receipt me-1"></i>Thermal</a>
                    <?php
                    $waMsg = 'Invoice ' . $invoice['invoice_no'] . ' - Total: ' . \App\Models\Helpers::formatCurrency($invoice['grand_total']) . '. Thank you!';
                    ?>
                    <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($waMsg); ?>" target="_blank" class="btn btn-success btn-sm"><i class="fa-brands fa-whatsapp me-1"></i>WhatsApp</a>
                    <a href="<?php echo BASE_URL; ?>/billing/index.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>Back</a>
                </div>
            </div>
        </div>

        <div class="card border p-4 bg-white text-dark shadow-sm">
            <!-- Header -->
            <div class="row align-items-center mb-4">
                <div class="col-8">
                    <h3 class="fw-bold mb-1 text-uppercase text-dark"><?php echo \App\Models\Helpers::sanitize($company['company_name'] ?? 'Grovixo'); ?></h3>
                    <p class="mb-0 text-muted small" style="white-space:pre-line;"><?php echo \App\Models\Helpers::sanitize($company['address'] ?? ''); ?></p>
                    <p class="mb-0 small mt-1"><strong>Phone:</strong> <?php echo \App\Models\Helpers::sanitize($company['phone'] ?? ''); ?> | <strong>Email:</strong> <?php echo \App\Models\Helpers::sanitize($company['email'] ?? ''); ?></p>
                    <?php if (!empty($company['gst_number'])): ?><p class="mb-0 small text-muted"><strong>GSTIN:</strong> <?php echo \App\Models\Helpers::sanitize($company['gst_number']); ?></p><?php endif; ?>
                </div>
                <div class="col-4 text-end">
                    <h4 class="fw-bold text-secondary mb-1 text-uppercase"><?php echo \App\Models\Helpers::sanitize($invoice['invoice_type']); ?></h4>
                    <h5 class="text-indigo mb-1 fw-bold"><?php echo $invoice['invoice_no']; ?></h5>
                    <p class="text-secondary small mb-0">Date: <?php echo date('d-M-Y', strtotime($invoice['invoice_date'])); ?></p>
                    <?php if (!empty($invoice['due_date'])): ?>
                        <p class="text-secondary small mb-0">Due: <?php echo date('d-M-Y', strtotime($invoice['due_date'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <hr class="my-3">

            <!-- Customer & Meta -->
            <div class="row mb-4">
                <div class="col-6">
                    <h6 class="fw-bold text-dark mb-2">BILLED TO:</h6>
                    <?php if (!empty($invoice['customer_name'])): ?>
                        <strong class="text-black"><?php echo \App\Models\Helpers::sanitize($invoice['customer_name']); ?></strong><br>
                        <span class="text-secondary small">Mobile: <?php echo \App\Models\Helpers::sanitize($invoice['customer_mobile']); ?></span>
                        <?php if (!empty($invoice['customer_gst'])): ?><br><span class="text-secondary small">GSTIN: <?php echo \App\Models\Helpers::sanitize($invoice['customer_gst']); ?></span><?php endif; ?>
                        <?php if (!empty($invoice['customer_address'])): ?><br><span class="text-secondary small"><?php echo \App\Models\Helpers::sanitize($invoice['customer_address']); ?></span><?php endif; ?>
                    <?php else: ?>
                        <span class="text-muted">Walk-in Customer</span>
                    <?php endif; ?>
                </div>
                <div class="col-6 text-end">
                    <p class="mb-1 text-secondary small"><strong>Payment:</strong> <?php echo \App\Models\Helpers::sanitize($invoice['payment_method']); ?></p>
                    <p class="mb-1 text-secondary small"><strong>Status:</strong>
                        <span class="badge <?php echo $invoice['status'] === 'PAID' ? 'bg-success' : ($invoice['status'] === 'PARTIAL' ? 'bg-warning text-dark' : 'bg-danger'); ?>"><?php echo $invoice['status']; ?></span>
                    </p>
                    <p class="mb-1 text-secondary small"><strong>Cashier:</strong> <?php echo \App\Models\Helpers::sanitize($invoice['cashier_name']); ?></p>
                </div>
            </div>

            <!-- Items Table -->
            <table class="table table-bordered mb-4 text-dark border-secondary-subtle">
                <thead>
                    <tr class="align-middle bg-light text-dark">
                        <th style="width:35px;">#</th>
                        <th>Item</th>
                        <th style="width:70px;">HSN</th>
                        <th class="text-center" style="width:70px;">Qty</th>
                        <th class="text-end" style="width:90px;">Rate</th>
                        <th class="text-center" style="width:70px;">GST%</th>
                        <th class="text-end" style="width:80px;">Disc</th>
                        <th class="text-end" style="width:100px;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $idx => $item): ?>
                        <tr>
                            <td class="text-center"><?php echo $idx + 1; ?></td>
                            <td><strong><?php echo \App\Models\Helpers::sanitize($item['product_name']); ?></strong><br><span class="text-muted small">SKU: <?php echo \App\Models\Helpers::sanitize($item['sku']); ?></span></td>
                            <td class="small"><?php echo \App\Models\Helpers::sanitize($item['hsn_code'] ?? '-'); ?></td>
                            <td class="text-center"><?php echo (float)$item['quantity'] . ' ' . ($item['unit_name'] ?: 'Pcs'); ?></td>
                            <td class="text-end">₹<?php echo number_format($item['rate'], 2); ?></td>
                            <td class="text-center"><?php echo (float)$item['gst']; ?>%</td>
                            <td class="text-end"><?php echo (float)$item['discount'] > 0 ? '₹' . number_format($item['discount'], 2) : '-'; ?></td>
                            <td class="text-end fw-bold">₹<?php echo number_format($item['amount'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Totals -->
            <div class="row">
                <div class="col-7">
                    <?php if (!empty($company['invoice_terms'])): ?>
                        <div class="border rounded p-3 bg-light small"><strong>Terms:</strong><br><span class="text-muted" style="white-space:pre-line;"><?php echo \App\Models\Helpers::sanitize($company['invoice_terms']); ?></span></div>
                    <?php endif; ?>
                    <?php if (!empty($invoice['notes'])): ?>
                        <div class="border rounded p-3 bg-light small mt-2"><strong>Notes:</strong><br><span class="text-muted"><?php echo nl2br(\App\Models\Helpers::sanitize($invoice['notes'])); ?></span></div>
                    <?php endif; ?>
                </div>
                <div class="col-5">
                    <div class="d-flex justify-content-between mb-1"><span class="text-secondary">Subtotal:</span><span>₹<?php echo number_format($invoice['subtotal'], 2); ?></span></div>
                    <?php if (!$isIGST): ?>
                        <div class="d-flex justify-content-between mb-1"><span class="text-secondary">CGST:</span><span>₹<?php echo number_format($invoice['cgst_amount'], 2); ?></span></div>
                        <div class="d-flex justify-content-between mb-1"><span class="text-secondary">SGST:</span><span>₹<?php echo number_format($invoice['sgst_amount'], 2); ?></span></div>
                    <?php else: ?>
                        <div class="d-flex justify-content-between mb-1"><span class="text-secondary">IGST:</span><span>₹<?php echo number_format($invoice['igst_amount'], 2); ?></span></div>
                    <?php endif; ?>
                    <?php if ((float)$invoice['discount'] > 0): ?>
                        <div class="d-flex justify-content-between mb-1"><span class="text-secondary">Discount:</span><span class="text-success">-₹<?php echo number_format($invoice['discount'], 2); ?></span></div>
                    <?php endif; ?>
                    <?php if ((float)($invoice['coupon_discount'] ?? 0) > 0): ?>
                        <div class="d-flex justify-content-between mb-1"><span class="text-secondary">Coupon Discount:</span><span class="text-success">-₹<?php echo number_format($invoice['coupon_discount'], 2); ?></span></div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between mb-1"><span class="text-secondary">Round Off:</span><span><?php echo ($invoice['round_off'] >= 0 ? '+' : '') . '₹' . number_format($invoice['round_off'], 2); ?></span></div>
                    <hr class="my-2 border-dark">
                    <div class="d-flex justify-content-between fw-bold text-dark fs-5 mb-3"><span>Total:</span><span>₹<?php echo number_format($invoice['grand_total'], 2); ?></span></div>

                    <?php if (count($payments) > 1): ?>
                        <div class="small fw-bold mb-1 text-dark">Split Payments:</div>
                        <?php foreach ($payments as $p): ?>
                            <div class="d-flex justify-content-between mb-1 small"><span class="text-secondary"><?php echo $p['payment_method']; ?>:</span><span>₹<?php echo number_format($p['amount'], 2); ?></span></div>
                        <?php endforeach; ?>
                        <hr class="my-1">
                    <?php endif; ?>

                    <div class="d-flex justify-content-between mb-1"><span class="text-secondary">Paid:</span><span class="text-success">₹<?php echo number_format($invoice['paid_amount'], 2); ?></span></div>
                    <?php if ((float)$invoice['due_amount'] > 0): ?>
                        <div class="d-flex justify-content-between mb-1 fw-semibold text-danger"><span>Balance Due:</span><span>₹<?php echo number_format($invoice['due_amount'], 2); ?></span></div>
                    <?php endif; ?>

                    <?php if ((int)($invoice['loyalty_points_earned'] ?? 0) > 0): ?>
                        <div class="d-flex justify-content-between mb-1 small text-warning"><span>Loyalty Earned:</span><span>+<?php echo $invoice['loyalty_points_earned']; ?> pts</span></div>
                    <?php endif; ?>
                    <?php if ((int)($invoice['loyalty_points_redeemed'] ?? 0) > 0): ?>
                        <div class="d-flex justify-content-between mb-1 small text-warning"><span>Points Redeemed:</span><span>-<?php echo $invoice['loyalty_points_redeemed']; ?> pts</span></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="text-center mt-5 text-muted small border-top pt-3">
                <?php echo \App\Models\Helpers::sanitize($company['invoice_footer'] ?? 'Thank you!'); ?>
            </div>
        </div>
    </div>
</div>
