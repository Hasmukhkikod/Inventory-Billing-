<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Product Details & Ledger View
 */
?>
<div class="row g-4">
    <!-- Product Profile Card -->
    <div class="col-md-4">
        <div class="panel-card h-100">
            <div class="panel-header">
                <h6 class="mb-0 text-indigo"><i class="fa-solid fa-circle-info me-2"></i>Product Card</h6>
                <a href="<?php echo BASE_URL; ?>/products/form.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-secondary text-emerald py-1">
                    <i class="fa-solid fa-pencil me-1"></i> Edit
                </a>
            </div>
            
            <div class="panel-body text-center">
                <div class="mb-3">
                    <?php if (!empty($product['image'])): ?>
                        <img src="<?php echo BASE_URL . '/' . \App\Models\Helpers::sanitize($product['image']); ?>" class="rounded border" style="width: 140px; height: 140px; object-fit: cover;">
                    <?php else: ?>
                        <div class="bg-tertiary rounded border text-secondary d-inline-flex align-items-center justify-content-center" style="width: 140px; height: 140px; font-size: 3rem;">
                            <i class="fa-solid fa-image"></i>
                        </div>
                    <?php endif; ?>
                </div>
                
                <h5 class="text-dark fw-bold mb-1"><?php echo \App\Models\Helpers::sanitize($product['product_name']); ?></h5>
                <span class="badge bg-light-primary mb-3">SKU: <?php echo \App\Models\Helpers::sanitize($product['sku']); ?></span>
                
                <div class="border-top border-secondary pt-3 text-start">
                    <div class="row g-2 small mb-2">
                        <div class="col-6 text-secondary">Barcode:</div>
                        <div class="col-6 text-dark fw-semibold"><?php echo \App\Models\Helpers::sanitize($product['barcode'] ?: '-'); ?></div>
                    </div>
                    <div class="row g-2 small mb-2">
                        <div class="col-6 text-secondary">HSN Code:</div>
                        <div class="col-6 text-dark fw-semibold"><?php echo \App\Models\Helpers::sanitize($product['hsn_code'] ?? '-'); ?></div>
                    </div>
                    <div class="row g-2 small mb-2">
                        <div class="col-6 text-secondary">Category:</div>
                        <div class="col-6 text-dark fw-semibold"><?php echo \App\Models\Helpers::sanitize($product['category_name'] ?: 'Uncategorized'); ?></div>
                    </div>
                    <div class="row g-2 small mb-2">
                        <div class="col-6 text-secondary">Brand:</div>
                        <div class="col-6 text-dark fw-semibold"><?php echo \App\Models\Helpers::sanitize($product['brand_name'] ?: 'Generic'); ?></div>
                    </div>
                    <div class="row g-2 small mb-2">
                        <div class="col-6 text-secondary">Unit Code:</div>
                        <div class="col-6 text-dark fw-semibold"><?php echo \App\Models\Helpers::sanitize($product['unit_name'] ?: 'Pcs'); ?></div>
                    </div>
                    <div class="row g-2 small mb-2">
                        <div class="col-6 text-secondary">GST slab:</div>
                        <div class="col-6 text-dark fw-semibold"><?php echo (float)$product['gst_percentage']; ?>%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Ledger / Stock stats -->
    <div class="col-md-8">
        <div class="panel-card h-100">
            <div class="panel-header">
                <h6 class="mb-0 text-indigo"><i class="fa-solid fa-boxes-packing me-2"></i>Stock Valuation & Ledger</h6>
                <div>
                    <span class="text-secondary small me-2">Current Stock:</span>
                    <?php
                        $stock = (float)$product['current_stock'];
                        $min = (float)$product['minimum_stock'];
                        if ($stock <= $min) {
                            echo '<span class="badge bg-light-danger fw-bold fs-6">' . $stock . '</span>';
                        } else {
                            echo '<span class="badge bg-light-success fw-bold fs-6">' . $stock . '</span>';
                        }
                    ?>
                </div>
            </div>
            
            <div class="panel-body">
                <!-- Pricing details row -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4 text-center border-end border-secondary">
                        <span class="text-secondary small d-block mb-1">Cost Price</span>
                        <strong class="fs-5 text-dark">₹ <?php echo number_format($product['cost_price'], 2); ?></strong>
                    </div>
                    <div class="col-md-4 text-center border-end border-secondary">
                        <span class="text-secondary small d-block mb-1">Selling Price</span>
                        <strong class="fs-5 text-indigo">₹ <?php echo number_format($product['selling_price'], 2); ?></strong>
                    </div>
                    <div class="col-md-4 text-center">
                        <span class="text-secondary small d-block mb-1">Inventory Value (Cost)</span>
                        <strong class="fs-5 text-dark">₹ <?php echo number_format($product['cost_price'] * $product['current_stock'], 2); ?></strong>
                    </div>
                </div>
                
                <h6 class="text-secondary border-bottom pb-2 mb-3">Recent Stock Transactions</h6>
                
                <div class="table-responsive" style="max-height: 250px;">
                    <table class="table table-hover align-middle mb-0" id="stockTxTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Ref Invoice</th>
                                <th class="text-end">Qty Change</th>
                                <th class="text-end">Balance</th>
                                <th>User</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($transactions)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-secondary">No stock transactions logged yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($transactions as $t): ?>
                                    <?php
                                        $isPositive = (float)$t['quantity'] > 0;
                                        $badgeClass = $isPositive ? 'bg-light-success' : 'bg-light-danger';
                                    ?>
                                    <tr>
                                        <td><?php echo date('d-M-Y H:i', strtotime($t['created_at'])); ?></td>
                                        <td><span class="badge <?php echo $badgeClass; ?>"><?php echo \App\Models\Helpers::sanitize($t['transaction_type']); ?></span></td>
                                        <td><?php echo \App\Models\Helpers::sanitize($t['reference_no'] ?: '-'); ?></td>
                                        <td class="text-end fw-bold <?php echo $isPositive ? 'text-emerald' : 'text-rose'; ?>">
                                            <?php echo ($isPositive ? '+' : '') . (float)$t['quantity']; ?>
                                        </td>
                                        <td class="text-end fw-bold text-dark"><?php echo (float)$t['stock_after']; ?></td>
                                        <td class="text-muted small"><?php echo \App\Models\Helpers::sanitize($t['creator_name'] ?: 'System'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
