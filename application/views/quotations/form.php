<?php
/**
 * IIMS v2.0 - Create / Edit Quotation (Full-Width Layout)
 */
$isEdit = !empty($quotation);
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fa-solid fa-file-lines text-indigo me-2"></i><?php echo $isEdit ? 'Edit Quotation: ' . \App\Models\Helpers::sanitize($quotation['quotation_no']) : 'Create Quotation / Estimate'; ?></h4>
        <nav class="text-muted small">Home / Quotations / <?php echo $isEdit ? 'Edit' : 'Create'; ?> Quotation</nav>
    </div>
    <div>
        <a href="<?php echo BASE_URL; ?>/quotations/index.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-list me-1"></i>Back to List</a>
    </div>
</div>

<!-- Section 1: Customer & Quotation Info -->
<div class="panel-card mb-4">
    <div class="panel-body py-3">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Customer Name</label>
                <div class="input-group">
                    <select class="form-select searchable-select" id="qt-customer-select">
                        <option value="">-- No Customer --</option>
                    </select>
                    <button class="btn btn-outline-secondary" id="btn-quick-customer" type="button" title="Add Customer">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Quotation Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="qt-date" required value="<?php echo $isEdit ? $quotation['quotation_date'] : date('Y-m-d'); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Valid Until</label>
                <input type="date" class="form-control" id="qt-valid-until" value="<?php echo $isEdit ? ($quotation['valid_until'] ?? '') : date('Y-m-d', strtotime('+30 days')); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Notes</label>
                <input type="text" class="form-control" id="qt-notes-short" placeholder="Quick note..." value="<?php echo $isEdit ? htmlspecialchars($quotation['notes'] ?? '') : ''; ?>">
            </div>
        </div>
    </div>
</div>

<!-- Section 2: Product Search & Cart -->
<div class="panel-card mb-4">
    <div class="panel-body">
        <!-- Product search moved into the table -->

        <!-- Cart Table -->
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0" id="qt-cart-table">
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Product Name</th>
                        <th style="width:100px;">HSN / SAC</th>
                        <th style="width:80px;">Qty</th>
                        <th style="width:100px;">Unit</th>
                        <th style="width:120px;">Rate (&#8377;)</th>
                        <th style="width:130px;">Discount</th>
                        <th style="width:80px;">GST %</th>
                        <th style="width:120px;" class="text-end">Total (&#8377;)</th>
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
                            <i class="fa-solid fa-cart-shopping fs-3 mb-2 d-block text-muted"></i>
                            Select products above to add them to the quotation
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

<!-- Section 3: Notes + Summary -->
<div class="row g-4 mb-4">
    <!-- Left: Notes / Remarks -->
    <div class="col-lg-5">
        <div class="panel-card h-100">
            <div class="panel-body">
                <h6 class="fw-semibold mb-3"><i class="fa-solid fa-sticky-note text-indigo me-2"></i>Remarks</h6>
                <div class="mb-3">
                    <label class="form-label small">Quotation Notes</label>
                    <textarea class="form-control form-control-sm" id="qt-notes" rows="4" placeholder="Additional notes for the customer..."><?php echo $isEdit ? \App\Models\Helpers::sanitize($quotation['notes'] ?? '') : ''; ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Totals Summary -->
    <div class="col-lg-7">
        <div class="panel-card h-100">
            <div class="panel-body">
                <h6 class="fw-semibold mb-3"><i class="fa-solid fa-calculator text-indigo me-2"></i>Quotation Summary</h6>

                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-secondary">Subtotal (Taxable)</td>
                        <td class="text-end fw-bold" id="qt-subtotal">&#8377;0.00</td>
                    </tr>
                    <tr>
                        <td class="text-secondary">GST Tax Amount</td>
                        <td class="text-end" id="qt-tax">&#8377;0.00</td>
                    </tr>
                    <tr>
                        <td class="text-secondary">Flat Discount (&#8377;)</td>
                        <td class="text-end">
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end d-inline-block" style="width:100px;" id="qt-discount-input" value="<?php echo $isEdit ? number_format((float)$quotation['discount'], 2, '.', '') : '0.00'; ?>">
                        </td>
                    </tr>
                </table>

                <!-- Grand Total -->
                <div class="border-top mt-3 pt-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 text-indigo fw-bold">Grand Total</h4>
                        <h3 class="mb-0 text-indigo fw-bold" id="qt-grand-total">&#8377;0.00</h3>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="d-flex gap-2 mt-4">
                    <?php echo \App\Models\Helpers::csrfField(); ?>
                    <button class="btn btn-success flex-grow-1 py-2 fs-5" id="btn-save-quotation">
                        <i class="fa-solid fa-circle-check me-2"></i><?php echo $isEdit ? 'Update Quotation' : 'Save Quotation'; ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Add Customer Modal -->
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

<script>
$(document).ready(function() {
    let cart = [];
    const csrfToken = $('input[name="csrf_token"]').val();
    const isEdit = <?php echo $isEdit ? 'true' : 'false'; ?>;
    const editId = <?php echo $isEdit ? (int)$quotation['id'] : 0; ?>;
    const editCustomerId = <?php echo $isEdit ? (int)($quotation['customer_id'] ?? 0) : 0; ?>;

    // Focus search on Add Product click
    $('#btn-focus-search').click(function() { $('#cart-product-select').select2('open'); });

    // Clear All
    $('#btn-clear-cart').click(function() {
        if (cart.length === 0) return;
        Swal.fire({
            title: 'Clear All Items?', text: 'This will remove all products from the quotation.',
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, Clear All',
            background: '#ffffff', color: '#1e293b'
        }).then(r => { if (r.isConfirmed) { cart = []; renderCart(); } });
    });

    // Load Customers
    function loadCustomers(selectId) {
        $.ajax({
            url: BASE_URL + '/api/customers.php?action=list',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                const select = $("#qt-customer-select");
                select.find('option:not(:first)').remove();
                if (res.status) {
                    res.data.forEach(c => {
                        const s = (selectId ? c.id == selectId : (editCustomerId > 0 && c.id == editCustomerId)) ? ' selected' : '';
                        select.append(`<option value="${c.id}"${s}>${c.customer_name} (${c.mobile})</option>`);
                    });
                }
            }
        });
    }
    loadCustomers();

    // Quick Add Customer
    $('#btn-quick-customer').click(function() { $('#quickCustomerForm')[0].reset(); $('#quickCustomerModal').modal('show'); });
    $('#quickCustomerForm').submit(function(e) {
        e.preventDefault();
        $.post(BASE_URL + '/api/customers.php?action=save', $(this).serialize(), function(res) {
            if (res.status) {
                $('#quickCustomerModal').modal('hide');
                loadCustomers(res.data.id);
                Swal.fire({ icon: 'success', title: 'Customer Added', timer: 1500, showConfirmButton: false, background: '#ffffff', color: '#1e293b' });
            } else {
                Swal.fire({ icon: 'error', title: 'Failed', text: res.message, background: '#ffffff', color: '#1e293b' });
            }
        }, 'json');
    });

    // Pre-fill cart if editing
    <?php if ($isEdit && !empty($items)): ?>
    cart = <?php echo json_encode(array_map(function($item) {
        return [
            'id' => (int)$item['product_id'],
            'product_name' => $item['product_name'],
            'sku' => $item['sku'],
            'hsn_code' => $item['hsn_code'] ?? '',
            'unit_name' => $item['unit_name'] ?? 'PCS',
            'unit_id' => $item['unit_id'] ?? null,
            'secondary_unit_name' => $item['secondary_unit_name'] ?? null,
            'secondary_unit_id' => $item['secondary_unit_id'] ?? null,
            'conversion_factor' => isset($item['conversion_factor']) ? (float)$item['conversion_factor'] : null,
            'billing_unit_id' => $item['billing_unit_id'] ?? ($item['unit_id'] ?? null),
            'billing_unit_name' => $item['billing_unit_name'] ?? ($item['unit_name'] ?? 'PCS'),
            'is_secondary_unit' => (int)($item['is_secondary_unit'] ?? 0),
            'original_rate' => (float)($item['original_rate'] ?? $item['rate']),
            'qty' => (float)$item['quantity'],
            'rate' => (float)$item['rate'],
            'gst_percentage' => (float)$item['gst'],
            'discount_value' => (float)$item['discount'],
            'discount_type' => $item['discount_type'] ?? 'percent'
        ];
    }, $items)); ?>;
    renderCart();
    <?php endif; ?>

    // Product search using Select2 (like Invoices)
    $('#cart-product-select').select2({
        placeholder: 'Add Product — search by name, SKU or barcode...',
        allowClear: true,
        theme: 'bootstrap-5', width: '100%',
        ajax: {
            url: BASE_URL + '/api/billing.php',
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return { action: 'search_product', q: params.term || '*' };
            },
            processResults: function (data) {
                if (!data.status) return { results: [] };
                return {
                    results: data.data.map(function (p) {
                        const stock = parseFloat(p.current_stock);
                        let stockTxt = stock + ' ' + (p.unit_name || 'PCS');
                        if (p.secondary_unit_name && p.conversion_factor) {
                            const secStock = parseFloat((stock * parseFloat(p.conversion_factor)).toFixed(2));
                            stockTxt += ' (' + secStock + ' ' + p.secondary_unit_name + ')';
                        }
                        return {
                            id: p.id,
                            text: p.product_name + ' (' + p.sku + ')',
                            product: p,
                            stock: stockTxt,
                            price: parseFloat(p.selling_price || 0).toFixed(2),
                            inStock: stock > 0
                        };
                    })
                };
            },
            cache: true
        },
        minimumInputLength: 0,
        templateResult: function (item) {
            if (item.loading) return 'Searching...';
            if (!item.product) return item.text;
            const badge = item.inStock
                ? '<span class="badge bg-light-success float-end">' + item.stock + '</span>'
                : '<span class="badge bg-light-danger float-end">Out of stock</span>';
            return $('<div>' +
                '<strong>' + item.product.product_name + '</strong> <span class="text-muted small">(' + item.product.sku + ')</span>' + badge +
                '<div class="small text-indigo">₹' + item.price + ' | GST: ' + parseFloat(item.product.gst_percentage || 0) + '%</div>' +
                '</div>');
        },
        templateSelection: function () {
            return 'Add Product';
        }
    });

    $('#cart-product-select').on('select2:select', function (e) {
        const data = e.params.data;
        if (data && data.product) {
            addToCart(data.product);
            $(this).val(null).trigger('change');
        }
    });

    function addToCart(p) {
        const existing = cart.find(item => item.id === parseInt(p.id));
        if (existing) {
            existing.qty += 1;
        } else {
            cart.push({
                id: parseInt(p.id),
                product_name: p.product_name,
                sku: p.sku,
                hsn_code: p.hsn_code || '',
                unit_name: p.unit_name || 'PCS',
                qty: 1,
                rate: parseFloat(p.selling_price || 0),
                original_rate: parseFloat(p.selling_price || 0),
                unit_id: p.unit_id || null,
                secondary_unit_name: p.secondary_unit_name || null,
                secondary_unit_id: p.secondary_unit_id || null,
                conversion_factor: p.conversion_factor ? parseFloat(p.conversion_factor) : null,
                billing_unit_id: p.unit_id || null,
                billing_unit_name: p.unit_name || 'PCS',
                is_secondary_unit: 0,
                gst_percentage: parseFloat(p.gst_percentage || 0),
                discount_value: 0,
                discount_type: 'percent'
            });
        }
        renderCart();
    }

    function renderCart() {
        const body = $("#qt-cart-table tbody");
        body.find('tr:not(.cart-add-row):not(.cart-empty-row)').remove();

        if (cart.length === 0) {
            $(".cart-empty-row").show();
            calculateTotals();
            return;
        }
        $(".cart-empty-row").hide();

        cart.forEach((item, index) => {
            const base = item.qty * item.rate;
            let disc_amt = 0;
            if (item.discount_type === 'percent') {
                disc_amt = base * (item.discount_value / 100);
            } else {
                disc_amt = parseFloat(item.discount_value) || 0;
            }
            const after_disc = base - disc_amt;
            const tax_amt = after_disc * (item.gst_percentage / 100);
            const row_total = after_disc + tax_amt;

            let unitCell;
            if (item.secondary_unit_name && item.conversion_factor) {
                unitCell = '<td><select class="form-select form-select-sm py-1 cart-unit-select" data-index="' + index + '" style="width:90px;">' +
                    '<option value="primary"' + (item.is_secondary_unit === 0 ? ' selected' : '') + '>' + (item.unit_name || 'PCS') + '</option>' +
                    '<option value="secondary"' + (item.is_secondary_unit === 1 ? ' selected' : '') + '>' + item.secondary_unit_name + '</option>' +
                    '</select>';
                if (item.is_secondary_unit === 0) {
                    const secQty = parseFloat((item.qty * item.conversion_factor).toFixed(2));
                    unitCell += '<div class="text-muted small mt-1">= ' + secQty + ' ' + item.secondary_unit_name + '</div>';
                } else {
                    const priQty = parseFloat((item.qty / item.conversion_factor).toFixed(4));
                    unitCell += '<div class="text-muted small mt-1">= ' + priQty + ' ' + (item.unit_name || 'PCS') + '</div>';
                }
                unitCell += '</td>';
            } else {
                unitCell = '<td class="text-muted small">' + (item.billing_unit_name || item.unit_name || 'PCS') + '</td>';
            }

            $('.cart-add-row').before(`
                <tr data-index="${index}">
                    <td>${index + 1}</td>
                    <td>
                        <strong>${item.product_name}</strong>
                        <span class="text-muted small d-block">SKU: ${item.sku}</span>
                    </td>
                    <td class="small">${item.hsn_code || '-'}</td>
                    <td>
                        <input type="number" step="0.01" class="form-control form-control-sm item-qty-input" data-index="${index}" value="${item.qty}" style="width:70px;" min="0.01">
                    </td>
                    ${unitCell}
                    <td>
                        <input type="number" step="0.01" class="form-control form-control-sm item-rate-input" data-index="${index}" value="${item.rate.toFixed(2)}" style="width:100px;" min="0">
                    </td>
                    <td>
                        <div class="input-group input-group-sm" style="width:150px;">
                            <input type="number" step="0.01" class="form-control item-disc-input" data-index="${index}" value="${item.discount_value}" min="0">
                            <select class="form-select item-disc-type" data-index="${index}" style="max-width:62px;">
                                <option value="percent" ${item.discount_type==='percent'?'selected':''}>%</option>
                                <option value="amount" ${item.discount_type==='amount'?'selected':''}>&#8377;</option>
                            </select>
                        </div>
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control form-control-sm item-gst-input" data-index="${index}" value="${item.gst_percentage}" style="width:70px;" min="0">
                    </td>
                    <td class="text-end fw-bold font-monospace">&#8377;${row_total.toFixed(2)}</td>
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
        const idx = $(this).data('index'), val = parseFloat($(this).val());
        if (val > 0) { cart[idx].qty = val; renderCart(); }
    });
    $("#qt-cart-table").on('input', '.item-rate-input', function() {
        const idx = $(this).data('index'), val = parseFloat($(this).val());
        if (val >= 0) { cart[idx].rate = val; renderCart(); }
    });
    $("#qt-cart-table").on('input', '.item-gst-input', function() {
        const idx = $(this).data('index'), val = parseFloat($(this).val());
        if (val >= 0) { cart[idx].gst_percentage = val; renderCart(); }
    });
    $("#qt-cart-table").on('input', '.item-disc-input', function() {
        const idx = $(this).data('index'), val = parseFloat($(this).val());
        if (val >= 0) { cart[idx].discount_value = val; renderCart(); }
    });
    $("#qt-cart-table").on('change', '.item-disc-type', function() {
        const idx = $(this).data('index');
        cart[idx].discount_type = $(this).val();
        renderCart();
    });
    $("#qt-cart-table").on('click', '.btn-remove-item', function() {
        cart.splice($(this).data('index'), 1); renderCart();
    });
    $("#qt-cart-table").on('change', '.cart-unit-select', function () {
        const idx = $(this).data('index');
        const selectedValue = $(this).val();
        const item = cart[idx];
        if (selectedValue === 'secondary' && item.is_secondary_unit === 0) {
            item.rate = parseFloat((item.rate / item.conversion_factor).toFixed(2));
            item.is_secondary_unit = 1;
            item.billing_unit_id = item.secondary_unit_id;
            item.billing_unit_name = item.secondary_unit_name;
        } else if (selectedValue === 'primary' && item.is_secondary_unit === 1) {
            item.rate = parseFloat((item.rate * item.conversion_factor).toFixed(2));
            item.is_secondary_unit = 0;
            item.billing_unit_id = item.unit_id;
            item.billing_unit_name = item.unit_name;
        }
        renderCart();
    });
    $("#qt-discount-input").on('input', function() { calculateTotals(); });

    function calculateTotals() {
        let subtotal = 0, tax = 0;
        cart.forEach(item => {
            const base = item.qty * item.rate;
            let disc_amt = item.discount_type === 'percent' ? base * (item.discount_value / 100) : item.discount_value;
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
        const notes = $("#qt-notes").val() || $("#qt-notes-short").val();
        const discount = parseFloat($("#qt-discount-input").val()) || 0;

        if (cart.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Cart Empty', text: 'Please add products to the quotation.', background: '#ffffff', color: '#1e293b' });
            return;
        }
        if (!quotationDate) {
            Swal.fire({ icon: 'warning', title: 'Date Required', text: 'Please set the quotation date.', background: '#ffffff', color: '#1e293b' });
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
        if (isEdit) { postData.quotation_id = editId; }

        $.ajax({
            url: BASE_URL + '/api/quotations.php?action=save',
            type: 'POST', data: postData, dataType: 'json',
            success: function(res) {
                if (res.status) {
                    Swal.fire({
                        icon: 'success', title: isEdit ? 'Quotation Updated' : 'Quotation Created',
                        text: res.message, background: '#ffffff', color: '#1e293b'
                    }).then(() => { window.location.href = BASE_URL + '/quotations/view.php?id=' + res.data.quotation_id; });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#ffffff', color: '#1e293b' });
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Network Error', text: 'Could not reach the server. Please try again.', background: '#ffffff', color: '#1e293b' });
            }
        });
    });
});
</script>
