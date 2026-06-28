<?php
/**
 * IIMS v2.0 - Create Invoice (Full-Width Layout)
 */
$compSettings = $this->db->query("SELECT * FROM company_settings WHERE id = 1 LIMIT 1")->fetch();
$loyaltyEnabled = (int)($compSettings['loyalty_enabled'] ?? 0);
$loyaltyPer100 = (int)($compSettings['loyalty_points_per_100'] ?? 1);
$loyaltyRedeemValue = (float)($compSettings['loyalty_redeem_value'] ?? 1.00);
$companyState = trim($compSettings['state'] ?? '');
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fa-solid fa-file-invoice text-indigo me-2"></i>Create Invoice</h4>
        <nav class="text-muted small">Home / Invoices / Create Invoice</nav>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-warning btn-sm position-relative" id="btn-toggle-held" title="Held Bills (F5)">
            <i class="fa-solid fa-pause-circle me-1"></i>Held Bills
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" id="held-bills-count">0</span>
        </button>
        <a href="<?php echo BASE_URL; ?>/billing/index.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-list me-1"></i>All Invoices</a>
    </div>
</div>

<!-- Section 1: Customer & Invoice Info -->
<div class="panel-card mb-4">
    <div class="panel-body py-3">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Customer Name</label>
                <div class="input-group">
                    <select class="form-select searchable-select" id="pos-customer-select">
                        <option value="">Walk-in Customer</option>
                    </select>
                    <button class="btn btn-outline-secondary" id="btn-quick-customer" type="button" title="Add Customer">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Invoice Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="pos-invoice-date" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Due Date</label>
                <input type="date" class="form-control" id="bill-due-date">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Invoice Type</label>
                <select class="form-select" id="pos-invoice-type">
                    <option value="RETAIL">Retail</option>
                    <option value="GST" selected>GST Invoice</option>
                    <option value="TAX">Tax Invoice</option>
                    <option value="PROFORMA">Proforma</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Mobile No</label>
                <input type="text" class="form-control" id="pos-customer-mobile" placeholder="Auto-filled" readonly>
            </div>
        </div>
    </div>
</div>

<!-- Section 2: Product Cart -->
<div class="panel-card mb-4">
    <div class="panel-body">
        <!-- Cart Table -->
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0" id="pos-cart-table">
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Product Name</th>
                        <th style="width:100px;">HSN / SAC</th>
                        <th style="width:80px;">Qty</th>
                        <th style="width:100px;">Unit</th>
                        <th style="width:120px;">Price (₹)</th>
                        <th style="width:100px;">Discount</th>
                        <th style="width:80px;">GST %</th>
                        <th style="width:120px;" class="text-end">Total (₹)</th>
                        <th style="width:50px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="cart-add-row">
                        <td colspan="10" class="py-2">
                            <select class="form-select" id="cart-product-select" style="width:100%;">
                                <option value="">Add Product</option>
                            </select>
                        </td>
                    </tr>
                    <tr class="cart-empty-row">
                        <td colspan="10" class="text-center py-4 text-secondary">
                            <i class="fa-solid fa-basket-shopping fs-3 mb-2 d-block text-muted"></i>
                            Search and select products above to add them
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Add Row / Clear -->
        <div class="d-flex gap-2 mt-3">
            <button class="btn btn-sm btn-outline-primary" id="btn-focus-search"><i class="fa-solid fa-plus me-1"></i>Add Product</button>
            <button class="btn btn-sm btn-outline-danger" id="btn-clear-cart">Clear All</button>
        </div>
    </div>
</div>

<!-- Section 3: Totals + Payment (Full Width Bottom) -->
<div class="row g-4 mb-4">
    <!-- Left: Notes & Coupon -->
    <div class="col-lg-5">
        <div class="panel-card h-100">
            <div class="panel-body">
                <h6 class="fw-semibold mb-3"><i class="fa-solid fa-receipt text-indigo me-2"></i>Additional Details</h6>

                <!-- Loyalty -->
                <?php if ($loyaltyEnabled): ?>
                <div class="d-none mb-3 p-2 rounded border" id="loyalty-panel">
                    <div class="d-flex justify-content-between small">
                        <span><i class="fa-solid fa-star text-warning me-1"></i>Loyalty Points</span>
                        <strong class="text-warning" id="customer-loyalty-points">0</strong>
                    </div>
                    <div class="form-check form-switch mt-1">
                        <input class="form-check-input" type="checkbox" id="redeem-loyalty-toggle">
                        <label class="form-check-label small" for="redeem-loyalty-toggle">Redeem (₹<span id="loyalty-redeem-value"><?php echo $loyaltyRedeemValue; ?></span>/pt)</label>
                    </div>
                    <div class="d-none mt-2" id="redeem-points-row">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">Points</span>
                            <input type="number" class="form-control" id="redeem-points-input" min="0" value="0">
                            <span class="input-group-text text-emerald" id="redeem-discount-display">= ₹0</span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Payment Mode -->
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0 fw-semibold small">Payment Mode</label>
                        <button class="btn btn-sm btn-outline-secondary py-0 px-2" id="btn-add-split" type="button"><i class="fa-solid fa-plus me-1"></i>Split</button>
                    </div>
                    <div id="payment-methods-container">
                        <div class="payment-row d-flex gap-2 mb-2" data-index="0">
                            <select class="form-select form-select-sm pay-method" style="width: 55%;">
                                <option value="CASH">CASH</option>
                                <option value="UPI">UPI / QR SCAN</option>
                                <option value="CARD">CARD</option>
                                <option value="NET_BANKING">NET BANKING</option>
                                <option value="CREDIT">CREDIT</option>
                            </select>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm pay-amount" placeholder="Amount" value="0.00">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between small mt-1">
                        <span class="text-secondary">Received:</span>
                        <strong id="total-paid-display">₹0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span class="text-secondary">Change / Balance:</span>
                        <strong id="change-balance-display">₹0.00</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Totals Summary -->
    <div class="col-lg-7">
        <div class="panel-card h-100">
            <div class="panel-body">
                <h6 class="fw-semibold mb-3"><i class="fa-solid fa-calculator text-indigo me-2"></i>Invoice Summary</h6>

                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td class="text-secondary">Sub Total (Taxable)</td><td class="text-end fw-bold" id="bill-subtotal">₹0.00</td></tr>
                            <tr><td class="text-secondary">CGST Amount</td><td class="text-end" id="bill-cgst">₹0.00</td></tr>
                            <tr><td class="text-secondary">SGST Amount</td><td class="text-end" id="bill-sgst">₹0.00</td></tr>
                            <tr class="d-none" id="igst-row"><td class="text-secondary">IGST Amount</td><td class="text-end" id="bill-igst">₹0.00</td></tr>
                            <tr><td class="text-secondary">Total Tax</td><td class="text-end" id="bill-tax">₹0.00</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-secondary">Flat Discount (₹)</td>
                                <td class="text-end"><input type="number" step="0.01" min="0" class="form-control form-control-sm text-end d-inline-block" style="width:100px;" id="bill-discount-input" value="0.00"></td>
                            </tr>
                            <?php if ($loyaltyEnabled): ?>
                            <tr class="d-none" id="loyalty-discount-row">
                                <td class="text-warning small"><i class="fa-solid fa-star me-1"></i>Loyalty</td>
                                <td class="text-end text-warning fw-bold" id="bill-loyalty-discount">-₹0.00</td>
                            </tr>
                            <?php endif; ?>
                            <tr><td class="text-secondary">Round Off</td><td class="text-end" id="bill-roundoff">₹0.00</td></tr>
                        </table>
                    </div>
                </div>

                <!-- Grand Total -->
                <div class="border-top mt-3 pt-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 text-indigo fw-bold">Grand Total</h4>
                        <h3 class="mb-0 text-indigo fw-bold" id="bill-grand-total">₹0.00</h3>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2 mt-4">
                    <button class="btn btn-warning px-3 py-2" id="btn-hold-bill" title="Hold Bill (F3)">
                        <i class="fa-solid fa-pause me-1"></i>Hold
                    </button>
                    <button class="btn btn-success flex-grow-1 py-2 fs-5" id="btn-save-invoice">
                        <i class="fa-solid fa-receipt me-2"></i>Generate Invoice
                    </button>
                </div>

                <!-- Shortcuts -->
                <div class="text-center mt-3 text-secondary small">
                    <kbd>F2</kbd> Search &nbsp; <kbd>F3</kbd> Hold &nbsp; <kbd>F4</kbd> Generate &nbsp; <kbd>F5</kbd> Recall &nbsp; <kbd>F6</kbd> Print &nbsp; <kbd>ESC</kbd> Cancel
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Held Bills Slide Panel -->
<div class="held-bills-panel" id="held-bills-panel">
    <div class="held-bills-header">
        <h6 class="mb-0"><i class="fa-solid fa-pause-circle me-2 text-warning"></i>Held Bills</h6>
        <button class="btn btn-sm btn-outline-secondary" id="btn-close-held"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="held-bills-body" id="held-bills-list">
        <div class="text-center text-secondary py-4">No held bills</div>
    </div>
</div>

<!-- Quick Customer Modal -->
<div class="modal fade" id="quickCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-user-plus text-indigo me-2"></i>Add Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="quickCustomerForm">
                <?php echo \App\Models\Helpers::csrfField(); ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6"><label class="form-label">Customer Name *</label><input type="text" class="form-control" name="customer_name" required></div>
                        <div class="col-6"><label class="form-label">Mobile *</label><input type="text" class="form-control" name="mobile" required></div>
                        <div class="col-6"><label class="form-label">GST Number</label><input type="text" class="form-control" name="gst_number"></div>
                        <div class="col-6"><label class="form-label">State</label><input type="text" class="form-control" name="state" placeholder="e.g. Maharashtra"></div>
                        <div class="col-12"><label class="form-label">Address</label><textarea class="form-control" name="address" rows="2"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add & Select</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hold Bill Modal -->
<div class="modal fade" id="holdBillModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-pause-circle text-warning me-2"></i>Hold Bill</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Bill Note (optional)</label>
                <input type="text" class="form-control" id="hold-bill-note" placeholder="e.g. Table 3, Customer waiting...">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" id="btn-confirm-hold"><i class="fa-solid fa-pause me-1"></i>Hold Bill</button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden config -->
<input type="hidden" id="config-loyalty-enabled" value="<?php echo $loyaltyEnabled; ?>">
<input type="hidden" id="config-loyalty-per-100" value="<?php echo $loyaltyPer100; ?>">
<input type="hidden" id="config-loyalty-redeem-value" value="<?php echo $loyaltyRedeemValue; ?>">
<input type="hidden" id="config-company-state" value="<?php echo htmlspecialchars($companyState); ?>">

<script src="<?php echo BASE_URL; ?>/assets/js/billing.js" defer></script>
