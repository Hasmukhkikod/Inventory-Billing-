<?php
/**
 * IIMS v2.0 - Quotation Detail View
 */
?>
<div class="row g-4 justify-content-center">
    <div class="col-md-9 col-lg-8">
        <div class="panel-card mb-4">
            <div class="panel-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-indigo"><i class="fa-solid fa-file-signature me-2"></i>Quotation Details</h5>
                <div class="d-flex gap-2">
                    <a href="<?php echo BASE_URL; ?>/quotation_print?id=<?php echo $quotation['id']; ?>" target="_blank" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-print me-1"></i>Print
                    </a>
                    <?php if ($quotation['status'] === 'ACCEPTED'): ?>
                        <button class="btn btn-success btn-sm" id="btn-convert-invoice" data-id="<?php echo $quotation['id']; ?>">
                            <i class="fa-solid fa-file-invoice me-1"></i>Convert to Invoice
                        </button>
                    <?php endif; ?>
                    <?php
                    $waMsg = 'Quotation ' . $quotation['quotation_no'] . ' - Total: ' . \App\Models\Helpers::formatCurrency($quotation['grand_total']);
                    ?>
                    <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($waMsg); ?>" target="_blank" class="btn btn-success btn-sm">
                        <i class="fa-brands fa-whatsapp me-1"></i>WhatsApp
                    </a>
                    <a href="<?php echo BASE_URL; ?>/quotations/index" class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-arrow-left me-1"></i>Back
                    </a>
                </div>
            </div>
        </div>

        <div class="card border p-4 bg-white text-dark shadow-sm">
            <div class="row align-items-center mb-4">
                <div class="col-12 col-sm-8">
                    <h3 class="fw-bold mb-1 text-uppercase text-dark"><?php echo \App\Models\Helpers::sanitize($company['company_name'] ?? 'Grovixo'); ?></h3>
                    <p class="mb-0 text-muted small" style="white-space:pre-line;"><?php echo \App\Models\Helpers::sanitize($company['address'] ?? ''); ?></p>
                    <p class="mb-0 small mt-1"><strong>Phone:</strong> <?php echo \App\Models\Helpers::sanitize($company['phone'] ?? ''); ?> | <strong>Email:</strong> <?php echo \App\Models\Helpers::sanitize($company['email'] ?? ''); ?></p>
                    <?php if (!empty($company['gst_number'])): ?>
                        <p class="mb-0 small text-muted"><strong>GSTIN:</strong> <?php echo \App\Models\Helpers::sanitize($company['gst_number']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-12 col-sm-4 text-sm-end mt-3 mt-sm-0">
                    <h4 class="fw-bold text-secondary mb-1">QUOTATION</h4>
                    <h5 class="text-indigo mb-1 fw-bold"><?php echo $quotation['quotation_no']; ?></h5>
                    <p class="text-secondary small mb-0">Date: <?php echo date('d-M-Y', strtotime($quotation['quotation_date'])); ?></p>
                    <?php if (!empty($quotation['valid_until'])): ?>
                        <p class="text-secondary small mb-0">Valid Until: <?php echo date('d-M-Y', strtotime($quotation['valid_until'])); ?></p>
                    <?php endif; ?>
                    <span class="badge mt-1 <?php
                        echo match($quotation['status']) {
                            'DRAFT' => 'bg-light-primary', 'SENT' => 'bg-light-warning',
                            'ACCEPTED' => 'bg-light-success', 'REJECTED' => 'bg-light-danger',
                            'CONVERTED' => 'bg-primary', default => 'bg-secondary'
                        };
                    ?>"><?php echo $quotation['status']; ?></span>
                </div>
            </div>

            <hr class="my-3">

            <div class="row mb-4">
                <div class="col-12 col-sm-6">
                    <h6 class="fw-bold text-dark mb-2">QUOTATION FOR:</h6>
                    <?php if (!empty($quotation['customer_name'])): ?>
                        <strong class="text-black"><?php echo \App\Models\Helpers::sanitize($quotation['customer_name']); ?></strong><br>
                        <span class="text-secondary small">Mobile: <?php echo \App\Models\Helpers::sanitize($quotation['customer_mobile'] ?? ''); ?></span>
                        <?php if (!empty($quotation['customer_address'])): ?>
                            <br><span class="text-secondary small"><?php echo \App\Models\Helpers::sanitize($quotation['customer_address']); ?></span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="text-muted">No customer selected</span>
                    <?php endif; ?>
                </div>
                <div class="col-12 col-sm-6 text-sm-end mt-3 mt-sm-0">
                    <p class="mb-1 small text-secondary"><strong>Created By:</strong> <?php echo \App\Models\Helpers::sanitize($quotation['created_by_name'] ?? 'System'); ?></p>
                </div>
            </div>

            <div class="table-responsive">
            <table class="table table-bordered mb-4 text-dark border-secondary-subtle">
                <thead>
                    <tr class="align-middle bg-light text-dark">
                        <th style="width:40px;">#</th>
                        <th>Product</th>
                        <th class="text-center" style="width:80px;">Qty</th>
                        <th class="text-end" style="width:100px;">Rate</th>
                        <th class="text-center" style="width:70px;">GST%</th>
                        <th class="text-end" style="width:90px;">Disc</th>
                        <th class="text-end" style="width:120px;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $idx => $item): ?>
                        <tr>
                            <td class="text-center"><?php echo $idx + 1; ?></td>
                            <td><strong><?php echo \App\Models\Helpers::sanitize($item['product_name']); ?></strong><br><span class="text-muted small">SKU: <?php echo \App\Models\Helpers::sanitize($item['sku']); ?></span></td>
                            <td class="text-center"><?php echo (float)$item['quantity'] . ' ' . $item['display_unit']; ?><?php if (!empty($item['primary_qty']) && (float)$item['primary_qty'] != (float)$item['quantity']): ?><br><span class="text-muted small">(<?php echo (float)$item['primary_qty'] . ' ' . ($item['unit_name'] ?: 'Pcs'); ?>)</span><?php endif; ?></td>
                            <td class="text-end">₹<?php echo number_format($item['rate'], 2); ?></td>
                            <td class="text-center"><?php echo (float)$item['gst']; ?>%</td>
                            <td class="text-end"><?php echo (float)$item['discount'] > 0 ? $item['discount'] . '%' : '-'; ?></td>
                            <td class="text-end fw-bold">₹<?php echo number_format($item['amount'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>

            <div class="row">
                <div class="col-12 col-sm-7">
                    <?php if (!empty($quotation['notes'])): ?>
                        <div class="border rounded p-3 bg-light small">
                            <strong>Notes:</strong><br>
                            <span class="text-muted"><?php echo nl2br(\App\Models\Helpers::sanitize($quotation['notes'])); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-12 col-sm-5 mt-3 mt-sm-0">
                    <div class="d-flex justify-content-between mb-1"><span class="text-secondary">Subtotal:</span><span>₹<?php echo number_format($quotation['subtotal'], 2); ?></span></div>
                    <?php if ((float)$quotation['discount'] > 0): ?>
                        <div class="d-flex justify-content-between mb-1"><span class="text-secondary">Discount:</span><span class="text-success">-₹<?php echo number_format($quotation['discount'], 2); ?></span></div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between mb-1"><span class="text-secondary">GST:</span><span>₹<?php echo number_format($quotation['gst_amount'], 2); ?></span></div>
                    <hr class="my-2 border-dark">
                    <div class="d-flex justify-content-between fw-bold text-dark fs-5"><span>Total:</span><span>₹<?php echo number_format($quotation['grand_total'], 2); ?></span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($quotation['status'] === 'ACCEPTED'): ?>
<script>
$('#btn-convert-invoice').click(function() {
    const id = $(this).data('id');
    Swal.fire({
        title: 'Convert to Invoice?', text: 'This will open the POS terminal pre-filled with this quotation.',
        icon: 'question', showCancelButton: true, confirmButtonText: 'Convert', confirmButtonColor: '#10b981',
        background: '#151e30', color: '#f3f4f6'
    }).then(function(r) {
        if (r.isConfirmed) window.location.href = BASE_URL + '/billing/form?from_quotation=' + id;
    });
});
</script>
<?php endif; ?>
