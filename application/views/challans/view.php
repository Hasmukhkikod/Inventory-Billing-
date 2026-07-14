<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Delivery Challan Detailed View
 */
?>
<div class="row g-4 justify-content-center text-dark">
    <div class="col-md-9 col-lg-8">
        <div class="panel-card mb-4 no-print">
            <div class="panel-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-dark"><i class="fa-solid fa-truck me-2"></i>Delivery Challan</h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                        <i class="fa-solid fa-print me-1"></i> Print
                    </button>
                    <a href="<?php echo BASE_URL; ?>/challans/index" class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- White Challan Receipt Card -->
        <div class="card border p-4 bg-white shadow-sm text-dark">
            <!-- Company Header Block -->
            <div class="row align-items-center mb-4">
                <div class="col-12 col-sm-8">
                    <h4 class="fw-bold mb-1 text-uppercase text-dark"><?php echo \App\Models\Helpers::sanitize($compSettings['company_name'] ?? COMPANY_NAME); ?></h4>
                    <p class="mb-0 text-muted small" style="white-space: pre-line;"><?php echo \App\Models\Helpers::sanitize($compSettings['address'] ?? ''); ?></p>
                    <?php if (!empty($compSettings['phone'])): ?>
                        <p class="mb-0 small"><strong>Phone:</strong> <?php echo \App\Models\Helpers::sanitize($compSettings['phone']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($compSettings['gst_number'])): ?>
                        <p class="mb-0 small"><strong>GSTIN:</strong> <?php echo \App\Models\Helpers::sanitize($compSettings['gst_number']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-12 col-sm-4 text-sm-end mt-3 mt-sm-0">
                    <h4 class="fw-bold text-secondary mb-1">DELIVERY CHALLAN</h4>
                    <h5 class="text-indigo mb-1 fw-bold"><?php echo \App\Models\Helpers::sanitize($challan['challan_no']); ?></h5>
                    <p class="text-secondary small mb-0">Date: <?php echo date('d-M-Y', strtotime($challan['challan_date'])); ?></p>
                    <?php
                    $statusBadge = 'bg-light-primary text-indigo';
                    if ($challan['challan_status'] === 'DELIVERED') $statusBadge = 'bg-light-success text-success';
                    elseif ($challan['challan_status'] === 'CANCELLED') $statusBadge = 'bg-light-danger text-rose';
                    elseif ($challan['challan_status'] === 'PENDING') $statusBadge = 'bg-light-warning text-warning';
                    ?>
                    <span class="badge <?php echo $statusBadge; ?> mt-1"><?php echo \App\Models\Helpers::sanitize($challan['challan_status']); ?></span>
                </div>
            </div>

            <hr class="my-4">

            <!-- Customer & Transport Details -->
            <div class="row mb-4">
                <div class="col-12 col-sm-6">
                    <h6 class="fw-bold text-dark mb-2">DISPATCH TO:</h6>
                    <strong class="text-black d-block"><?php echo \App\Models\Helpers::sanitize($challan['customer_name']); ?></strong>
                    <?php if (!empty($challan['customer_address'])): ?>
                        <span class="d-block text-secondary small" style="white-space: pre-line;"><?php echo \App\Models\Helpers::sanitize($challan['customer_address']); ?></span>
                    <?php endif; ?>
                    <span class="d-block text-secondary">Mobile: <?php echo \App\Models\Helpers::sanitize($challan['customer_mobile'] ?? '-'); ?></span>
                    <?php if (!empty($challan['customer_gst'])): ?>
                        <span class="d-block text-secondary small">GSTIN: <?php echo \App\Models\Helpers::sanitize($challan['customer_gst']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="col-12 col-sm-6 text-sm-end mt-3 mt-sm-0">
                    <h6 class="fw-bold text-dark mb-2">TRANSPORT INFO:</h6>
                    <p class="mb-1 text-secondary"><strong>Transport:</strong> <?php echo \App\Models\Helpers::sanitize($challan['transport_name'] ?: 'N/A'); ?></p>
                    <p class="mb-1 text-secondary"><strong>Vehicle No:</strong> <?php echo \App\Models\Helpers::sanitize($challan['vehicle_no'] ?: 'N/A'); ?></p>
                    <p class="mb-1 text-secondary"><strong>Created By:</strong> <?php echo \App\Models\Helpers::sanitize($challan['creator_name'] ?: 'System'); ?></p>
                </div>
            </div>

            <!-- Items Table -->
            <div class="table-responsive">
            <table class="table table-bordered mb-4 text-dark border-secondary-subtle">
                <thead>
                    <tr class="align-middle bg-light text-dark">
                        <th style="width: 40px;">#</th>
                        <th>Item Details</th>
                        <th class="text-center" style="width: 120px;">Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $totalQty = 0; ?>
                    <?php foreach ($items as $idx => $item): ?>
                        <?php $totalQty += (float)$item['quantity']; ?>
                        <tr>
                            <td class="text-center"><?php echo $idx + 1; ?></td>
                            <td>
                                <strong><?php echo \App\Models\Helpers::sanitize($item['product_name']); ?></strong>
                                <span class="text-muted small d-block">SKU: <?php echo \App\Models\Helpers::sanitize($item['sku']); ?></span>
                            </td>
                            <td class="text-center fw-bold"><?php echo (float)$item['quantity'] . ' ' . $item['display_unit']; ?><?php if (!empty($item['primary_qty']) && (float)$item['primary_qty'] != (float)$item['quantity']): ?><br><span class="text-muted small fw-normal">(<?php echo (float)$item['primary_qty'] . ' ' . ($item['unit_name'] ?: 'Pcs'); ?>)</span><?php endif; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-light">
                        <td colspan="2" class="text-end fw-bold">Total Quantity:</td>
                        <td class="text-center fw-bold"><?php echo $totalQty; ?></td>
                    </tr>
                </tfoot>
            </table>
            </div>

            <!-- Notes Section -->
            <?php if (!empty($challan['notes'])): ?>
                <div class="mb-4 p-3 bg-light rounded">
                    <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-note-sticky me-1 text-indigo"></i> Notes / Remarks</h6>
                    <p class="mb-0 text-secondary" style="white-space: pre-line;"><?php echo \App\Models\Helpers::sanitize($challan['notes']); ?></p>
                </div>
            <?php endif; ?>

            <!-- Signatures -->
            <div class="row mt-5 pt-4 border-top border-secondary-subtle">
                <div class="col-4 col-sm-4 text-center">
                    <div class="border-top border-dark pt-2 mt-5 mx-3">
                        <small class="text-secondary">Prepared By</small>
                    </div>
                </div>
                <div class="col-4 col-sm-4 text-center">
                    <div class="border-top border-dark pt-2 mt-5 mx-3">
                        <small class="text-secondary">Checked By</small>
                    </div>
                </div>
                <div class="col-4 col-sm-4 text-center">
                    <div class="border-top border-dark pt-2 mt-5 mx-3">
                        <small class="text-secondary">Received By</small>
                    </div>
                </div>
            </div>

            <!-- Disclaimer -->
            <div class="text-center mt-4">
                <small class="text-muted">This is a delivery challan and not a tax invoice. Goods dispatched as per the above details.</small>
            </div>
        </div>
    </div>
</div>
