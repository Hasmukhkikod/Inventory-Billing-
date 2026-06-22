<?php
/**
 * Invoice & Inventory Management System (IIMS) v2.0
 * POS Billing Terminal View - Enhanced with Hold/Recall, Split Payment, Coupons, Loyalty
 */
$compSettings = $this->db->query("SELECT * FROM company_settings WHERE id = 1 LIMIT 1")->fetch();
$loyaltyEnabled = (int)($compSettings['loyalty_enabled'] ?? 0);
$loyaltyPer100 = (int)($compSettings['loyalty_points_per_100'] ?? 1);
$loyaltyRedeemValue = (float)($compSettings['loyalty_redeem_value'] ?? 1.00);
$companyState = trim($compSettings['state'] ?? '');
?>

<div class="pos-container">
    <!-- Billing Table Area -->
    <div class="pos-billing-area">
        <!-- Top Search Bar -->
        <div class="pos-search-bar">
            <div class="d-flex align-items-center gap-2" style="width: 250px;">
                <select class="form-select searchable-select" id="pos-customer-select">
                    <option value="">-- Walk-in Customer --</option>
                </select>
                <button class="btn btn-outline-secondary py-2" id="btn-quick-customer" type="button" title="Add Customer">
                    <i class="fa-solid fa-user-plus text-indigo"></i>
                </button>
            </div>

            <div class="border-start border-secondary h-50"></div>

            <div style="width: 150px;">
                <select class="form-select" id="pos-invoice-type">
                    <option value="RETAIL" selected>Retail Invoice</option>
                    <option value="GST">GST Invoice</option>
                    <option value="TAX">Tax Invoice</option>
                    <option value="PROFORMA">Proforma Inv</option>
                </select>
            </div>

            <div class="border-start border-secondary h-50"></div>

            <div class="flex-grow-1 position-relative">
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-barcode text-indigo"></i></span>
                    <input type="text" class="form-control" id="pos-product-search" placeholder="Type name, SKU, or scan barcode..." autocomplete="off">
                </div>
                <div class="pos-product-search-results d-none" id="search-results-box"></div>
            </div>

            <!-- Held Bills Badge -->
            <button class="btn btn-warning py-2 position-relative" id="btn-toggle-held" title="Held Bills (F5)">
                <i class="fa-solid fa-pause-circle"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" id="held-bills-count">0</span>
            </button>
        </div>

        <!-- Cart Table List -->
        <div class="pos-items-table">
            <table class="table table-hover mb-0 align-middle" id="pos-cart-table">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Product Details</th>
                        <th style="width: 90px;">HSN</th>
                        <th style="width: 100px;">Qty</th>
                        <th style="width: 110px;">Rate (₹)</th>
                        <th style="width: 80px;">GST %</th>
                        <th style="width: 90px;">Disc %</th>
                        <th style="width: 130px;" class="text-end">Total (₹)</th>
                        <th style="width: 40px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="cart-empty-row">
                        <td colspan="9" class="text-center py-5 text-secondary">
                            <i class="fa-solid fa-basket-shopping fs-2 mb-3 d-block text-muted"></i>
                            Scan barcode or search products to begin checkout
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Keyboard shortcuts legend -->
        <div class="pos-shortcuts-legend py-2 px-3 border-top border-secondary text-secondary small bg-card">
            <span class="me-3"><kbd>F2</kbd> Search</span>
            <span class="me-3"><kbd>F3</kbd> Hold Bill</span>
            <span class="me-3"><kbd>F4</kbd> Generate</span>
            <span class="me-3"><kbd>F5</kbd> Recall</span>
            <span class="me-3"><kbd>F6</kbd> Print Last</span>
            <span><kbd>ESC</kbd> Cancel</span>
        </div>
    </div>

    <!-- Summary / Checkout Sidebar -->
    <div class="pos-checkout-sidebar">
        <div class="pos-checkout-scrollable">
            <h5 class="text-indigo border-bottom border-secondary pb-3 mb-4">
                <i class="fa-solid fa-cash-register me-2 text-indigo"></i>Checkout Details
            </h5>

            <!-- Customer Loyalty Points Display -->
            <?php if ($loyaltyEnabled): ?>
            <div class="loyalty-display d-none mb-3 p-2 rounded border border-secondary" id="loyalty-panel">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small text-secondary"><i class="fa-solid fa-star text-warning me-1"></i>Loyalty Points</span>
                    <span class="fw-bold text-warning" id="customer-loyalty-points">0</span>
                </div>
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" id="redeem-loyalty-toggle">
                    <label class="form-check-label small text-secondary" for="redeem-loyalty-toggle">Redeem points (₹<span id="loyalty-redeem-value"><?php echo $loyaltyRedeemValue; ?></span>/pt)</label>
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

            <div class="checkout-row">
                <span class="text-secondary">Sub Total (Taxable)</span>
                <span class="text-dark fw-bold" id="bill-subtotal">₹0.00</span>
            </div>
            <div class="checkout-row">
                <span class="text-secondary">CGST Amount</span>
                <span class="text-dark" id="bill-cgst">₹0.00</span>
            </div>
            <div class="checkout-row">
                <span class="text-secondary">SGST Amount</span>
                <span class="text-dark" id="bill-sgst">₹0.00</span>
            </div>
            <div class="checkout-row d-none" id="igst-row">
                <span class="text-secondary">IGST Amount</span>
                <span class="text-dark" id="bill-igst">₹0.00</span>
            </div>
            <div class="checkout-row">
                <span class="text-secondary">Total Tax</span>
                <span class="text-dark" id="bill-tax">₹0.00</span>
            </div>

            <!-- Coupon Code -->
            <div class="checkout-row align-items-center mt-3">
                <span class="text-secondary">Coupon Code</span>
                <div class="input-group" style="width: 180px;">
                    <input type="text" class="form-control form-control-sm text-uppercase" id="coupon-code-input" placeholder="Enter code">
                    <button class="btn btn-sm btn-outline-secondary" id="btn-apply-coupon" type="button">Apply</button>
                </div>
            </div>
            <div class="checkout-row d-none" id="coupon-discount-row">
                <span class="text-emerald small"><i class="fa-solid fa-tag me-1"></i><span id="coupon-label">Coupon</span></span>
                <span class="text-emerald fw-bold" id="bill-coupon-discount">-₹0.00</span>
                <button class="btn btn-sm text-danger p-0 ms-1" id="btn-remove-coupon" title="Remove"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="checkout-row align-items-center mt-2">
                <span class="text-secondary">Flat Discount (₹)</span>
                <input type="number" step="0.01" min="0" class="form-control text-end py-1" style="width: 120px;" id="bill-discount-input" value="0.00">
            </div>

            <?php if ($loyaltyEnabled): ?>
            <div class="checkout-row d-none" id="loyalty-discount-row">
                <span class="text-warning small"><i class="fa-solid fa-star me-1"></i>Loyalty Discount</span>
                <span class="text-warning fw-bold" id="bill-loyalty-discount">-₹0.00</span>
            </div>
            <?php endif; ?>

            <div class="checkout-row">
                <span class="text-secondary">Round Off</span>
                <span class="text-secondary" id="bill-roundoff">₹0.00</span>
            </div>

            <div class="checkout-row total">
                <span>Grand Total</span>
                <span id="bill-grand-total">₹0.00</span>
            </div>

            <div class="border-top border-secondary my-3"></div>

            <!-- Due Date for credit sales -->
            <div class="mb-3 d-none" id="due-date-row">
                <label class="form-label small">Payment Due Date</label>
                <input type="date" class="form-control form-control-sm" id="bill-due-date">
            </div>

            <!-- Notes -->
            <div class="mb-3">
                <label class="form-label small">Invoice Notes</label>
                <textarea class="form-control form-control-sm" id="bill-notes" rows="2" placeholder="Optional notes..."></textarea>
            </div>

            <!-- Split Payment Section -->
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0 fw-semibold">Payment Mode</label>
                    <button class="btn btn-sm btn-outline-secondary py-0 px-2" id="btn-add-split" type="button" title="Split Payment">
                        <i class="fa-solid fa-plus me-1"></i>Split
                    </button>
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
                <!-- Payment summary -->
                <div class="d-flex justify-content-between small mt-2">
                    <span class="text-secondary">Total Received:</span>
                    <span class="fw-bold" id="total-paid-display">₹0.00</span>
                </div>
                <div class="d-flex justify-content-between small">
                    <span class="text-secondary">Change / Balance:</span>
                    <span class="fw-bold" id="change-balance-display">₹0.00</span>
                </div>
            </div>
        </div>

        <div class="pos-checkout-actions">
            <div class="d-flex gap-2">
                <button class="btn btn-warning flex-shrink-0 py-3" id="btn-hold-bill" title="Hold Bill (F3)">
                    <i class="fa-solid fa-pause"></i>
                </button>
                <button class="btn btn-success flex-grow-1 py-3 fs-5" id="btn-save-invoice">
                    <i class="fa-solid fa-receipt me-2"></i>Generate Invoice
                </button>
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
                <h5 class="modal-title">Add Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="quickCustomerForm">
                <?php echo \App\Models\Helpers::csrfField(); ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Customer Name *</label>
                            <input type="text" class="form-control" name="customer_name" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Mobile *</label>
                            <input type="text" class="form-control" name="mobile" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">GST Number</label>
                            <input type="text" class="form-control" name="gst_number">
                        </div>
                        <div class="col-6">
                            <label class="form-label">State</label>
                            <input type="text" class="form-control" name="state" placeholder="e.g. Maharashtra">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2"></textarea>
                        </div>
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

<!-- Hold Bill Note Modal -->
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
                <button type="button" class="btn btn-warning" id="btn-confirm-hold">
                    <i class="fa-solid fa-pause me-1"></i>Hold Bill
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden config for JS -->
<input type="hidden" id="config-loyalty-enabled" value="<?php echo $loyaltyEnabled; ?>">
<input type="hidden" id="config-loyalty-per-100" value="<?php echo $loyaltyPer100; ?>">
<input type="hidden" id="config-loyalty-redeem-value" value="<?php echo $loyaltyRedeemValue; ?>">
<input type="hidden" id="config-company-state" value="<?php echo htmlspecialchars($companyState); ?>">

<script src="<?php echo BASE_URL; ?>/assets/js/billing.js" defer></script>
