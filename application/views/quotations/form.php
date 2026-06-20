<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Quotation Create/Edit Form View
 */
$isEdit = !empty($quotation);
?>

<div class="row g-4 text-dark">
    <!-- Items Entry Panel -->
    <div class="col-lg-8">
        <div class="panel-card">
            <div class="panel-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-dark">
                    <i class="fa-solid fa-file-lines me-2 text-indigo"></i>
                    <?php echo $isEdit ? 'Edit Quotation: ' . \App\Models\Helpers::sanitize($quotation['quotation_no']) : 'New Quotation / Estimate'; ?>
                </h5>
                <a href="<?php echo BASE_URL; ?>/quotations/index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to List
                </a>
            </div>

            <div class="panel-body">
                <!-- Search row -->
                <div class="row g-3 mb-4">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Select Customer</label>
                        <select class="form-select" id="qt-customer-select">
                            <option value="">-- No Customer --</option>
                        </select>
                    </div>
                    <div class="col-md-7 position-relative">
                        <label class="form-label fw-semibold">Search Product to Add *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-barcode text-indigo"></i></span>
                            <input type="text" class="form-control" id="qt-product-search" placeholder="Search by name, SKU, or barcode..." autocomplete="off">
                        </div>
                        <div class="pos-product-search-results d-none w-100" id="qt-search-results-box" style="position: absolute; left: 0; right: 0; z-index: 1000; background: white; border: 1px solid #ddd; max-height: 250px; overflow-y: auto;">
                            <!-- Results loaded via JS -->
                        </div>
                    </div>
                </div>

                <!-- Cart Table -->
                <div class="table-responsive" style="min-height: 250px;">
                    <table class="table table-hover align-middle mb-0" id="qt-cart-table">
                        <thead>
                            <tr class="bg-light text-dark">
                                <th>#</th>
                                <th>Product Details</th>
                                <th style="width: 90px;">Qty</th>
                                <th style="width: 130px;">Rate (₹)</th>
                                <th style="width: 90px;">GST %</th>
                                <th style="width: 90px;">Disc %</th>
                                <th class="text-end" style="width: 130px;">Total (₹)</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="cart-empty-row">
                                <td colspan="8" class="text-center py-5 text-secondary">
                                    <i class="fa-solid fa-basket-shopping fs-2 mb-3 d-block text-muted"></i>
                                    Search and select products above to add them to the quotation.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary / Save Panel -->
    <div class="col-lg-4">
        <div class="panel-card h-100">
            <div class="panel-header">
                <h6 class="mb-0 text-dark"><i class="fa-solid fa-file-invoice-dollar me-2 text-indigo"></i>Quotation Summary</h6>
            </div>

            <div class="panel-body d-flex flex-column justify-content-between h-100">
                <div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Quotation Date *</label>
                        <input type="date" class="form-control" id="qt-date" required value="<?php echo $isEdit ? $quotation['quotation_date'] : date('Y-m-d'); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Valid Until</label>
                        <input type="date" class="form-control" id="qt-valid-until" value="<?php echo $isEdit ? ($quotation['valid_until'] ?? '') : date('Y-m-d', strtotime('+30 days')); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes / Remarks</label>
                        <textarea class="form-control" id="qt-notes" rows="3" placeholder="Additional notes for the customer..."><?php echo $isEdit ? \App\Models\Helpers::sanitize($quotation['notes'] ?? '') : ''; ?></textarea>
                    </div>

                    <div class="border-top border-secondary-subtle my-3"></div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Subtotal (Taxable)</span>
                        <strong id="qt-subtotal">₹0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">GST Tax Amount</span>
                        <strong id="qt-tax">₹0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-secondary">Flat Discount (₹)</span>
                        <input type="number" step="0.01" class="form-control text-end py-1" style="width: 120px;" id="qt-discount-input" value="<?php echo $isEdit ? number_format((float)$quotation['discount'], 2, '.', '') : '0.00'; ?>">
                    </div>

                    <hr class="my-3 border-dark">

                    <div class="d-flex justify-content-between fw-bold text-dark fs-5 mb-4">
                        <span>Grand Total</span>
                        <span id="qt-grand-total">₹0.00</span>
                    </div>
                </div>

                <div>
                    <?php echo \App\Models\Helpers::csrfField(); ?>
                    <button class="btn btn-success w-100 py-3 fs-5" id="btn-save-quotation">
                        <i class="fa-solid fa-circle-check me-2"></i><?php echo $isEdit ? 'Update Quotation' : 'Save Quotation'; ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let cart = [];
    const csrfToken = $('input[name="csrf_token"]').val();
    const isEdit = <?php echo $isEdit ? 'true' : 'false'; ?>;
    const editId = <?php echo $isEdit ? (int)$quotation['id'] : 0; ?>;
    const editCustomerId = <?php echo $isEdit ? (int)($quotation['customer_id'] ?? 0) : 0; ?>;

    // Load Customers
    $.ajax({
        url: BASE_URL + '/api/customers.php?action=list',
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            const select = $("#qt-customer-select");
            if (res.status) {
                res.data.forEach(c => {
                    const selected = (editCustomerId > 0 && c.id == editCustomerId) ? ' selected' : '';
                    select.append(`<option value="${c.id}"${selected}>${c.customer_name} (${c.mobile})</option>`);
                });
            }
        }
    });

    // Pre-fill cart if editing
    <?php if ($isEdit && !empty($items)): ?>
    cart = <?php echo json_encode(array_map(function($item) {
        return [
            'id' => (int)$item['product_id'],
            'product_name' => $item['product_name'],
            'sku' => $item['sku'],
            'qty' => (float)$item['quantity'],
            'rate' => (float)$item['rate'],
            'gst_percentage' => (float)$item['gst'],
            'discount_percentage' => (float)$item['discount']
        ];
    }, $items)); ?>;
    renderCart();
    <?php endif; ?>

    // Product search autocompletion
    $("#qt-product-search").on('input', function() {
        const query = $(this).val().trim();
        if (query.length < 2) {
            $("#qt-search-results-box").addClass('d-none');
            return;
        }

        $.ajax({
            url: BASE_URL + '/api/billing.php?action=search_product&q=' + encodeURIComponent(query),
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                const box = $("#qt-search-results-box");
                box.empty();
                if (res.status && res.data.length > 0) {
                    res.data.forEach(item => {
                        box.append(`
                            <div class="search-result-item p-2 border-bottom" style="cursor: pointer;" data-id="${item.id}">
                                <strong>${item.product_name}</strong> - <span class="text-indigo small">${item.sku}</span>
                                <div class="text-secondary small">Sell Price: ₹${parseFloat(item.selling_price || 0).toFixed(2)} | Stock: ${item.current_stock}</div>
                            </div>
                        `);
                    });
                    box.removeClass('d-none');
                } else {
                    box.html('<div class="p-2 text-secondary">No products found</div>').removeClass('d-none');
                }
            }
        });
    });

    // Close search results on outside click
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#qt-product-search, #qt-search-results-box').length) {
            $("#qt-search-results-box").addClass('d-none');
        }
    });

    // Handle product selection from search
    $("#qt-search-results-box").on('click', '.search-result-item', function() {
        const id = $(this).data('id');
        $.ajax({
            url: BASE_URL + '/api/products.php?action=get&id=' + id,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    addToCart(res.data);
                    $("#qt-product-search").val('');
                    $("#qt-search-results-box").addClass('d-none');
                }
            }
        });
    });

    function addToCart(p) {
        const existing = cart.find(item => item.id === p.id);
        if (existing) {
            existing.qty += 1;
        } else {
            cart.push({
                id: p.id,
                product_name: p.product_name,
                sku: p.sku,
                qty: 1,
                rate: parseFloat(p.selling_price || 0),
                gst_percentage: parseFloat(p.gst_percentage || 0),
                discount_percentage: 0
            });
        }
        renderCart();
    }

    function renderCart() {
        const body = $("#qt-cart-table tbody");
        body.find('tr:not(.cart-empty-row)').remove();

        if (cart.length === 0) {
            $(".cart-empty-row").show();
            calculateTotals();
            return;
        }

        $(".cart-empty-row").hide();

        cart.forEach((item, index) => {
            const base = item.qty * item.rate;
            const disc_amt = base * (item.discount_percentage / 100);
            const after_disc = base - disc_amt;
            const tax_amt = after_disc * (item.gst_percentage / 100);
            const row_total = after_disc + tax_amt;

            body.append(`
                <tr>
                    <td>${index + 1}</td>
                    <td>
                        <strong>${item.product_name}</strong>
                        <span class="text-muted small d-block">SKU: ${item.sku}</span>
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control form-control-sm item-qty-input" data-index="${index}" value="${item.qty}" style="width: 75px;" min="0.01">
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control form-control-sm item-rate-input" data-index="${index}" value="${item.rate.toFixed(2)}" style="width: 110px;" min="0.00">
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control form-control-sm item-gst-input" data-index="${index}" value="${item.gst_percentage}" style="width: 75px;" min="0">
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control form-control-sm item-disc-input" data-index="${index}" value="${item.discount_percentage}" style="width: 75px;" min="0" max="100">
                    </td>
                    <td class="text-end fw-bold text-dark font-monospace">₹${row_total.toFixed(2)}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-danger btn-remove-item" data-index="${index}"><i class="fa-solid fa-trash"></i></button>
                    </td>
                </tr>
            `);
        });

        calculateTotals();
    }

    // Cart event handlers
    $("#qt-cart-table").on('input', '.item-qty-input', function() {
        const idx = $(this).data('index');
        const val = parseFloat($(this).val());
        if (val > 0) {
            cart[idx].qty = val;
            renderCart();
        }
    });

    $("#qt-cart-table").on('input', '.item-rate-input', function() {
        const idx = $(this).data('index');
        const val = parseFloat($(this).val());
        if (val >= 0) {
            cart[idx].rate = val;
            renderCart();
        }
    });

    $("#qt-cart-table").on('input', '.item-gst-input', function() {
        const idx = $(this).data('index');
        const val = parseFloat($(this).val());
        if (val >= 0) {
            cart[idx].gst_percentage = val;
            renderCart();
        }
    });

    $("#qt-cart-table").on('input', '.item-disc-input', function() {
        const idx = $(this).data('index');
        const val = parseFloat($(this).val());
        if (val >= 0 && val <= 100) {
            cart[idx].discount_percentage = val;
            renderCart();
        }
    });

    $("#qt-cart-table").on('click', '.btn-remove-item', function() {
        const idx = $(this).data('index');
        cart.splice(idx, 1);
        renderCart();
    });

    $("#qt-discount-input").on('input', function() {
        calculateTotals();
    });

    function calculateTotals() {
        let subtotal = 0;
        let tax = 0;

        cart.forEach(item => {
            const base = item.qty * item.rate;
            const disc_amt = base * (item.discount_percentage / 100);
            const after_disc = base - disc_amt;
            subtotal += after_disc;
            tax += after_disc * (item.gst_percentage / 100);
        });

        const discount = parseFloat($("#qt-discount-input").val()) || 0;
        const grand = subtotal + tax - discount;

        $("#qt-subtotal").text('₹' + subtotal.toFixed(2));
        $("#qt-tax").text('₹' + tax.toFixed(2));
        $("#qt-grand-total").text('₹' + (grand > 0 ? grand.toFixed(2) : '0.00'));
    }

    // Save quotation
    $("#btn-save-quotation").click(function() {
        const customerId = $("#qt-customer-select").val();
        const quotationDate = $("#qt-date").val();
        const validUntil = $("#qt-valid-until").val();
        const notes = $("#qt-notes").val();
        const discount = parseFloat($("#qt-discount-input").val()) || 0;

        if (cart.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Cart Empty', text: 'Please add products to the quotation.', background: '#151e30', color: '#f3f4f6' });
            return;
        }

        if (!quotationDate) {
            Swal.fire({ icon: 'warning', title: 'Date Required', text: 'Please set the quotation date.', background: '#151e30', color: '#f3f4f6' });
            return;
        }

        const postData = {
            csrf_token: csrfToken,
            customer_id: customerId || 0,
            quotation_date: quotationDate,
            valid_until: validUntil || '',
            notes: notes || '',
            discount: discount,
            cart: JSON.stringify(cart)
        };

        if (isEdit) {
            postData.quotation_id = editId;
        }

        $.ajax({
            url: BASE_URL + '/api/quotations.php?action=save',
            type: 'POST',
            data: postData,
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    Swal.fire({
                        icon: 'success',
                        title: isEdit ? 'Quotation Updated' : 'Quotation Created',
                        text: res.message,
                        background: '#151e30',
                        color: '#f3f4f6'
                    }).then(() => {
                        window.location.href = BASE_URL + '/quotations/view.php?id=' + res.data.quotation_id;
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: res.message,
                        background: '#151e30',
                        color: '#f3f4f6'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'Could not reach the server. Please try again.',
                    background: '#151e30',
                    color: '#f3f4f6'
                });
            }
        });
    });
});
</script>
