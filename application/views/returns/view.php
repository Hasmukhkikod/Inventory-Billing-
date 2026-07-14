<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Returns Voucher detailed view
 */
$type = $_GET['type'] ?? 'SALES'; // SALES or PURCHASE
?>
<div class="row g-4 justify-content-center text-dark">
    <div class="col-md-9 col-lg-8">
        <div class="panel-card mb-4 no-print">
            <div class="panel-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-dark"><i class="fa-solid fa-file-invoice me-2"></i>Return Transaction Details</h5>
                <a href="<?php echo BASE_URL; ?>/returns/index" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>
        
        <!-- White Return Receipt Voucher Card -->
        <div class="card border p-4 bg-white shadow-sm text-dark">
            <!-- Header Block -->
            <div class="row align-items-center mb-4">
                <div class="col-12 col-sm-8">
                    <?php if ($type === 'SALES'): ?>
                        <h4 class="fw-bold mb-1 text-uppercase text-dark"><?php echo \App\Models\Helpers::sanitize($return['customer_name'] ?: 'Walk-in Customer'); ?></h4>
                        <p class="mb-0 text-muted small">
                            Mobile: <?php echo \App\Models\Helpers::sanitize($return['customer_mobile'] ?? ''); ?>
                        </p>
                    <?php else: ?>
                        <h4 class="fw-bold mb-1 text-uppercase text-dark"><?php echo \App\Models\Helpers::sanitize($return['supplier_name']); ?></h4>
                        <p class="mb-0 text-muted small">
                            Mobile: <?php echo \App\Models\Helpers::sanitize($return['supplier_mobile'] ?? ''); ?>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="col-12 col-sm-4 text-sm-end mt-3 mt-sm-0">
                    <h4 class="fw-bold text-secondary mb-1"><?php echo $type === 'SALES' ? 'CREDIT NOTE' : 'DEBIT NOTE'; ?></h4>
                    <h5 class="text-indigo mb-1 fw-bold"><?php echo $return['return_no']; ?></h5>
                    <p class="text-secondary small mb-0">Date: <?php echo date('d-M-Y', strtotime($return['return_date'])); ?></p>
                </div>
            </div>

            <hr class="my-4">

            <!-- Metadata Section -->
            <div class="row mb-4">
                <div class="col-12 col-sm-6">
                    <h6 class="fw-bold text-dark mb-2"><?php echo $type === 'SALES' ? 'RETURNED BY:' : 'RETURNED TO:'; ?></h6>
                    <strong class="text-black d-block"><?php echo \App\Models\Helpers::sanitize($type === 'SALES' ? ($return['customer_name'] ?: 'Walk-in') : $return['supplier_name']); ?></strong>
                </div>
                <div class="col-12 col-sm-6 text-sm-end mt-3 mt-sm-0">
                    <h6 class="fw-bold text-dark mb-2">VOUCHER META:</h6>
                    <p class="mb-1 text-secondary"><strong>Original Doc:</strong> <?php echo \App\Models\Helpers::sanitize($type === 'SALES' ? $return['invoice_no'] : $return['purchase_no']); ?></p>
                    <p class="mb-1 text-secondary"><strong>Logged By:</strong> <?php echo \App\Models\Helpers::sanitize($return['creator_name'] ?: 'System'); ?></p>
                </div>
            </div>

            <!-- Items Table -->
            <div class="table-responsive">
            <table class="table table-bordered mb-4 text-dark border-secondary-subtle">
                <thead>
                    <tr class="align-middle bg-light text-dark">
                        <th style="width: 40px;">#</th>
                        <th>Item Details</th>
                        <th class="text-center" style="width: 120px;">Returned Qty</th>
                        <th class="text-end" style="width: 120px;">Unit Rate</th>
                        <th class="text-end" style="width: 120px;">Total Value</th>
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
                            <td class="text-center"><?php echo (float)$item['quantity'] . ' ' . ($item['unit_name'] ?: 'Pcs'); ?></td>
                            <td class="text-end">₹<?php echo number_format($type === 'SALES' ? $item['rate'] : $item['cost_price'], 2); ?></td>
                            <td class="text-end fw-bold">₹<?php echo number_format($item['amount'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>

            <!-- Calculation summaries -->
            <div class="row">
                <div class="col-12 col-sm-7">
                    <div class="border rounded p-3 bg-light" style="font-size: 11px;">
                        <strong class="text-dark d-block mb-1">Return Remarks / Reason:</strong>
                        <span class="text-muted" style="white-space: pre-line;"><?php echo \App\Models\Helpers::sanitize($return['remarks'] ?: 'No remarks provided.'); ?></span>
                    </div>
                </div>
                <div class="col-12 col-sm-5 text-sm-end align-self-end mt-3 mt-sm-0">
                    <div class="d-flex justify-content-between fw-bold text-dark fs-5">
                        <span>Total Returned Value:</span>
                        <span>₹<?php echo number_format($return['total_amount'], 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
