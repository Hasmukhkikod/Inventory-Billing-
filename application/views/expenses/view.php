<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Expense Details View
 */
?>
<div class="row g-4 justify-content-center">
    <div class="col-md-8">
        <div class="panel-card">
            <div class="panel-header">
                <h5 class="mb-0 text-indigo"><i class="fa-solid fa-receipt me-2"></i>Expense Record Voucher</h5>
                <div>
                    <a href="<?php echo BASE_URL; ?>/expenses/form.php?id=<?php echo $expense['id']; ?>" class="btn btn-sm btn-outline-secondary text-emerald me-2">
                        <i class="fa-solid fa-pencil me-1"></i> Edit
                    </a>
                    <a href="<?php echo BASE_URL; ?>/expenses/index.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
            
            <div class="panel-body">
                <div class="row g-4 align-items-center mb-4">
                    <div class="col-sm-6">
                        <span class="text-secondary small d-block mb-1">Voucher Amount</span>
                        <strong class="fs-3 text-rose">₹ <?php echo number_format($expense['amount'], 2); ?></strong>
                    </div>
                    <div class="col-sm-6 text-sm-end">
                        <span class="text-secondary small d-block mb-1">Expense Date</span>
                        <strong class="fs-5 text-dark"><?php echo date('d-M-Y', strtotime($expense['expense_date'])); ?></strong>
                    </div>
                </div>
                
                <div class="border-top border-secondary pt-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <span class="text-secondary small d-block">Expense Category</span>
                            <span class="fs-6 text-dark fw-semibold"><?php echo \App\Models\Helpers::sanitize($expense['category_name']); ?></span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-secondary small d-block">Payment Method</span>
                            <span class="badge bg-light-primary"><?php echo \App\Models\Helpers::sanitize($expense['payment_method']); ?></span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-secondary small d-block">Logged By</span>
                            <span class="text-dark fw-semibold"><?php echo \App\Models\Helpers::sanitize($expense['creator_name'] ?: 'System'); ?></span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-secondary small d-block">Created Time</span>
                            <span class="text-muted small"><?php echo date('d-M-Y H:i:s', strtotime($expense['created_at'])); ?></span>
                        </div>
                        
                        <div class="col-md-12">
                            <span class="text-secondary small d-block mb-1">Description / Remarks</span>
                            <div class="p-3 border rounded text-dark bg-light" style="white-space: pre-wrap;"><?php echo \App\Models\Helpers::sanitize($expense['description'] ?: 'No description provided.'); ?></div>
                        </div>
                        
                        <div class="col-md-12">
                            <span class="text-secondary small d-block mb-2">Attached Receipt / Bill</span>
                            <?php if (!empty($expense['bill_attachment'])): ?>
                                <?php
                                $ext = strtolower(pathinfo($expense['bill_attachment'], PATHINFO_EXTENSION));
                                $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);
                                ?>
                                <div class="border p-2 rounded text-center bg-light">
                                    <?php if ($isImg): ?>
                                        <div class="mb-3">
                                            <img src="<?php echo BASE_URL . '/' . \App\Models\Helpers::sanitize($expense['bill_attachment']); ?>" class="img-fluid rounded border" style="max-height: 300px;">
                                        </div>
                                    <?php endif; ?>
                                    <a href="<?php echo BASE_URL . '/' . \App\Models\Helpers::sanitize($expense['bill_attachment']); ?>" target="_blank" class="btn btn-indigo btn-sm">
                                        <i class="fa-solid fa-up-right-from-square me-1"></i> Open Bill Document
                                    </a>
                                </div>
                            <?php else: ?>
                                <span class="text-muted small italic">No invoice or receipt attached.</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
