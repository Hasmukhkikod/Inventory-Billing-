<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Purchase Order detailed view
 */
?>
<div class="row g-4 justify-content-center text-dark">
    <div class="col-md-9 col-lg-8">
        <div class="panel-card mb-4 no-print">
            <div class="panel-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-dark"><i class="fa-solid fa-file-invoice me-2"></i>Purchase Order Summary</h5>
                <div class="d-flex gap-2">
                    <a href="<?php echo BASE_URL; ?>/purchase_print.php?id=<?php echo $purchase['id']; ?>" target="_blank" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-print me-1"></i> Print A4
                    </a>
                    <a href="<?php echo BASE_URL; ?>/purchases/index.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
        
        <!-- White Invoice Receipt Card -->
        <div class="card border p-4 bg-white shadow-sm text-dark">
            <!-- Header Block -->
            <div class="row align-items-center mb-4">
                <div class="col-8">
                    <h4 class="fw-bold mb-1 text-uppercase text-dark"><?php echo \App\Models\Helpers::sanitize($purchase['supplier_name']); ?></h4>
                    <p class="mb-0 text-muted small" style="white-space: pre-line;">
                        <?php echo \App\Models\Helpers::sanitize($purchase['supplier_address'] ?? ''); ?>
                    </p>
                    <p class="mb-0 small mt-1">
                        <strong>Phone:</strong> <?php echo \App\Models\Helpers::sanitize($purchase['supplier_mobile'] ?? ''); ?>
                    </p>
                    <?php if (!empty($purchase['supplier_gst'])): ?>
                        <p class="mb-0 small"><strong>GSTIN:</strong> <?php echo \App\Models\Helpers::sanitize($purchase['supplier_gst']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-4 text-end">
                    <h4 class="fw-bold text-secondary mb-1">PURCHASE ORDER</h4>
                    <h5 class="text-indigo mb-1 fw-bold"><?php echo $purchase['purchase_no']; ?></h5>
                    <p class="text-secondary small mb-0">Date: <?php echo date('d-M-Y', strtotime($purchase['purchase_date'])); ?></p>
                </div>
            </div>

            <hr class="my-4">

            <!-- Supplier & Staff Section -->
            <div class="row mb-4">
                <div class="col-6">
                    <h6 class="fw-bold text-dark mb-2">BILLED FROM:</h6>
                    <strong class="text-black d-block"><?php echo \App\Models\Helpers::sanitize($purchase['supplier_name']); ?></strong>
                    <span class="d-block text-secondary">Mobile: <?php echo \App\Models\Helpers::sanitize($purchase['supplier_mobile']); ?></span>
                </div>
                <div class="col-6 text-end">
                    <h6 class="fw-bold text-dark mb-2">ORDER META:</h6>
                    <?php
                        $paymentStatus = $purchase['payment_status'] ?? 'PENDING';
                        $paymentBadgeClass = match($paymentStatus) {
                            'PAID' => 'bg-light-success text-success',
                            'PARTIAL' => 'bg-light-warning text-warning',
                            'UNPAID' => 'bg-light-danger text-rose',
                            'PENDING' => '',
                            default => 'bg-light-secondary text-secondary'
                        };
                        $paymentBadgeStyle = $paymentStatus === 'PENDING' ? 'style="background-color: #d97706; color: white;"' : '';
                    ?>
                    <p class="mb-1 text-secondary"><strong>Payment Status:</strong> <span class="badge <?php echo $paymentBadgeClass; ?>" <?php echo $paymentBadgeStyle; ?>><?php echo \App\Models\Helpers::sanitize($paymentStatus); ?></span></p>
                    <?php
                        $orderStatus = $purchase['order_status'] ?? 'PENDING';
                        $orderBadgeClass = $orderStatus === 'COMPLETED' ? 'bg-light-success text-success' : '';
                        $orderBadgeStyle = $orderStatus === 'COMPLETED' ? '' : 'style="background-color: #d97706; color: white;"';
                    ?>
                    <p class="mb-1 text-secondary"><strong>Order Status:</strong> <span class="badge <?php echo $orderBadgeClass; ?>" <?php echo $orderBadgeStyle; ?>><?php echo \App\Models\Helpers::sanitize($orderStatus); ?></span></p>
                    <p class="mb-1 text-secondary"><strong>Logged By:</strong> <?php echo \App\Models\Helpers::sanitize($purchase['creator_name'] ?: 'System'); ?></p>
                </div>
            </div>

            <!-- Items Table -->
            <table class="table table-bordered mb-4 text-dark border-secondary-subtle">
                <thead>
                    <tr class="align-middle bg-light text-dark">
                        <th style="width: 40px;">#</th>
                        <th>Item Details</th>
                        <th class="text-center" style="width: 100px;">Qty</th>
                        <th class="text-end" style="width: 120px;">Cost Price</th>
                        <th class="text-center" style="width: 80px;">GST</th>
                        <th class="text-end" style="width: 120px;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $idx => $item): ?>
                        <tr>
                            <td class="text-center"><?php echo $idx + 1; ?></td>
                            <td>
                                <strong><?php echo \App\Models\Helpers::sanitize($item['product_name']); ?></strong>
                                <span class="text-muted small d-block">SKU: <?php echo \App\Models\Helpers::sanitize($item['sku']); ?></span>
                            </td>
                            <td class="text-center"><?php echo (float)$item['quantity'] . ' ' . $item['display_unit']; ?><?php if (!empty($item['primary_qty']) && (float)$item['primary_qty'] != (float)$item['quantity']): ?><br><span class="text-muted small">(<?php echo (float)$item['primary_qty'] . ' ' . ($item['unit_name'] ?: 'Pcs'); ?>)</span><?php endif; ?></td>
                            <td class="text-end">₹<?php echo number_format($item['cost_price'], 2); ?></td>
                            <td class="text-center"><?php echo (float)$item['gst'] . '%'; ?></td>
                            <td class="text-end fw-bold">₹<?php echo number_format($item['amount'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Calculation summaries -->
            <div class="row justify-content-end">
                <div class="col-5">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-secondary">Subtotal (Taxable):</span>
                        <span>₹<?php echo number_format($purchase['subtotal'], 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-secondary">GST Tax Amount:</span>
                        <span>₹<?php echo number_format($purchase['gst_amount'], 2); ?></span>
                    </div>
                    <?php if ($purchase['discount'] > 0): ?>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-secondary">Flat Discount:</span>
                            <span class="text-success">-₹<?php echo number_format($purchase['discount'], 2); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <hr class="my-2 border-dark">
                    
                    <div class="d-flex justify-content-between fw-bold text-dark fs-5">
                        <span>Total Amount:</span>
                        <span>₹<?php echo number_format($purchase['total_amount'], 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
