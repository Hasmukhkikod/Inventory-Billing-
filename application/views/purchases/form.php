<?php
/**
 * IIMS v2.0 - Create / Edit Purchase Order (Full-Width Layout)
 */
$isEdit = !empty($purchase);
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fa-solid fa-cart-flatbed text-indigo me-2"></i><?php echo $isEdit ? 'Edit Purchase Order: ' . \App\Models\Helpers::sanitize($purchase['purchase_no']) : 'New Purchase Order'; ?></h4>
        <nav class="text-muted small">Home / Purchases / <?php echo $isEdit ? 'Edit' : 'New'; ?> Purchase Order</nav>
    </div>
    <div>
        <a href="<?php echo BASE_URL; ?>/purchases/index" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-list me-1"></i>Back to List</a>
    </div>
</div>

<!-- Section 1: Supplier & Purchase Info -->
<div class="panel-card mb-4">
    <div class="panel-body py-3">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Supplier Name <span class="text-danger">*</span></label>
                <div class="input-group">
                    <select class="form-select searchable-select" id="pur-supplier-select" required>
                        <option value="">-- Select Supplier --</option>
                    </select>
                    <button class="btn btn-outline-secondary" id="btn-quick-supplier" type="button" title="Add Supplier">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Purchase Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="pur-date" required value="<?php echo $isEdit ? date('Y-m-d', strtotime($purchase['purchase_date'])) : date('Y-m-d'); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Payment Status</label>
                <select class="form-select" id="pur-payment-status">
                    <option value="UNPAID" <?php echo ($isEdit && $purchase['payment_status'] === 'UNPAID') ? 'selected' : (!$isEdit ? 'selected' : ''); ?>>UNPAID</option>
                    <option value="PARTIAL" <?php echo ($isEdit && $purchase['payment_status'] === 'PARTIAL') ? 'selected' : ''; ?>>PARTIAL</option>
                    <option value="PAID" <?php echo ($isEdit && $purchase['payment_status'] === 'PAID') ? 'selected' : ''; ?>>PAID</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Order Status</label>
                <?php $editOrderStatus = $isEdit ? ($purchase['order_status'] ?? 'PENDING') : 'PENDING'; ?>
                <select class="form-select" id="pur-order-status">
                    <option value="PENDING" <?php echo $editOrderStatus === 'PENDING' ? 'selected' : ''; ?>>PENDING</option>
                    <option value="COMPLETED" <?php echo $editOrderStatus === 'COMPLETED' ? 'selected' : ''; ?>>COMPLETED</option>
                </select>
                <small class="text-muted">Inventory updates only when Completed</small>
            </div>
        </div>
    </div>
</div>

<!-- Section 2: Product Search & Cart -->
<div class="panel-card mb-4">
    <div class="panel-body">
        <!-- Product Search (Mobile Friendly) -->
        <div class="mb-3 product-search" id="product-search">
            <div class="input-group input-group-lg shadow-sm border-indigo rounded">
                <span class="input-group-text bg-white border-end-0 text-indigo"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" class="form-control border-start-0 ps-0" id="cart-product-search" placeholder="Add Product — search by name, SKU or barcode..." autocomplete="off">
                <button class="btn btn-outline-secondary d-none" type="button" id="cart-product-search-clear" title="Clear"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="product-search-results shadow-lg" id="product-search-results"></div>
        </div>

        <!-- Cart Table -->
        <div class="table-responsive shadow-sm rounded border cart-table-wrapper">
            <table class="table table-hover align-middle mb-0 cart-items-table" id="pur-cart-table">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Product Name</th>
                        <th style="width:100px;">HSN / SAC</th>
                        <th style="width:80px;">Qty</th>
                        <th style="width:100px;">Unit</th>
                        <th style="width:130px;">Cost Price (&#8377;)</th>
                        <th style="width:80px;">GST %</th>
                        <th style="width:120px;" class="text-end">Total (&#8377;)</th>
                        <th style="width:50px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="cart-add-row d-none"></tr>
                    <tr class="cart-empty-row">
                        <td colspan="9" class="text-center py-4 text-secondary">
                            <i class="fa-solid fa-cart-shopping fs-3 mb-2 d-block text-muted"></i>
                            Select products above to add them to the purchase order
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Clear -->
        <div class="d-flex gap-2 mt-3">
            <button class="btn btn-sm btn-outline-danger" id="btn-clear-cart">Clear All</button>
        </div>
    </div>
</div>

<!-- Section 3: Notes + Summary -->
<div class="row g-4 mb-4">
    <!-- Left: Notes -->
    <div class="col-lg-5">
        <div class="panel-card h-100">
            <div class="panel-body">
                <h6 class="fw-semibold mb-3"><i class="fa-solid fa-sticky-note text-indigo me-2"></i>Notes</h6>
                <div class="mb-3">
                    <label class="form-label small">Purchase Notes</label>
                    <textarea class="form-control form-control-sm" id="pur-notes" rows="4" placeholder="Optional remarks..."></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Totals Summary -->
    <div class="col-lg-7">
        <div class="panel-card h-100">
            <div class="panel-body">
                <h6 class="fw-semibold mb-3"><i class="fa-solid fa-calculator text-indigo me-2"></i>Purchase Summary</h6>

                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-secondary">Subtotal (Taxable)</td>
                        <td class="text-end fw-bold" id="pur-subtotal">&#8377;0.00</td>
                    </tr>
                    <tr>
                        <td class="text-secondary">GST Tax Amount</td>
                        <td class="text-end" id="pur-tax">&#8377;0.00</td>
                    </tr>
                    <tr>
                        <td class="text-secondary">Flat Discount (&#8377;)</td>
                        <td class="text-end">
                            <input type="number" step="1" min="0" class="form-control form-control-sm text-end d-inline-block" style="width:100px;" id="pur-discount-input" value="<?php echo $isEdit ? number_format((float)$purchase['discount'], 2, '.', '') : '0.00'; ?>">
                        </td>
                    </tr>
                </table>

                <!-- Grand Total -->
                <div class="border-top mt-3 pt-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 text-indigo fw-bold">Grand Total</h4>
                        <h3 class="mb-0 text-indigo fw-bold" id="pur-grand-total">&#8377;0.00</h3>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="d-flex gap-2 mt-4">
                    <?php echo \App\Models\Helpers::csrfField(); ?>
                    <?php if ($isEdit): ?>
                        <input type="hidden" id="pur-edit-id" value="<?php echo (int)$purchase['id']; ?>">
                    <?php endif; ?>
                    <button class="btn btn-success flex-grow-1 py-2 fs-5" id="btn-save-purchase">
                        <i class="fa-solid fa-circle-check me-2"></i><?php echo $isEdit ? 'Update Purchase Order' : 'Generate PO Entry'; ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Add Supplier Modal -->
<div class="modal fade" id="quickSupplierModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-truck-field text-indigo me-2"></i>Add Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="quickSupplierForm">
                <?php echo \App\Models\Helpers::csrfField(); ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6"><label class="form-label">Supplier Name *</label><input type="text" class="form-control" name="supplier_name" id="qs-name" required></div>
                        <div class="col-6"><label class="form-label">Mobile *</label><input type="text" class="form-control" name="mobile" id="qs-mobile" required></div>
                        <div class="col-6"><label class="form-label">Contact Person</label><input type="text" class="form-control" name="contact_person" id="qs-contact"></div>
                        <div class="col-6"><label class="form-label">GST Number</label><input type="text" class="form-control" name="gst_number" id="qs-gst"></div>
                        <div class="col-12"><label class="form-label">Address</label><textarea class="form-control" name="address" id="qs-address" rows="2"></textarea></div>
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
    const editId = $('#pur-edit-id').val() || 0;

    // Clear All
    $('#btn-clear-cart').click(function() {
        if (cart.length === 0) return;
        Swal.fire({
            title: 'Clear All Items?', text: 'This will remove all products from the purchase order.',
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, Clear All',
            background: '#ffffff', color: '#1e293b'
        }).then(r => { if (r.isConfirmed) { cart = []; renderCart(); } });
    });

    // Load Suppliers
    function loadSuppliers(selectId) {
        $.ajax({
            url: BASE_URL + '/api/suppliers.php?action=list',
            type: 'GET', dataType: 'json',
            success: function(res) {
                const select = $("#pur-supplier-select");
                select.find('option:not(:first)').remove();
                if (res.status) {
                    res.data.forEach(s => {
                        const sel = (selectId && s.id == selectId) ? ' selected' : '';
                        select.append(`<option value="${s.id}"${sel}>${s.supplier_name} (${s.mobile})</option>`);
                    });
                }
            }
        });
    }
    <?php if ($isEdit): ?>
    loadSuppliers(<?php echo (int)$purchase['supplier_id']; ?>);
    // Load existing cart items for editing
    $.ajax({
        url: BASE_URL + '/api/purchases.php?action=get&id=<?php echo (int)$purchase['id']; ?>',
        type: 'GET', dataType: 'json',
        success: function(res) {
            if (res.status && res.data.items) {
                res.data.items.forEach(function(item) {
                    cart.push({
                        id: parseInt(item.product_id),
                        product_name: item.product_name,
                        sku: item.sku,
                        hsn_code: item.hsn_code || '',
                        unit_name: item.unit_name || 'PCS',
                        qty: parseFloat(item.quantity),
                        cost_price: parseFloat(item.cost_price),
                        gst_percentage: parseFloat(item.gst)
                    });
                });
                renderCart();
            }
        }
    });
    <?php else: ?>
    loadSuppliers();
    <?php endif; ?>

    // Quick Add Supplier
    $("#btn-quick-supplier").click(function() { $("#quickSupplierForm")[0].reset(); $("#quickSupplierModal").modal('show'); });
    $("#quickSupplierForm").submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: BASE_URL + '/api/suppliers.php?action=save',
            type: 'POST', data: $(this).serialize(), dataType: 'json',
            success: function(res) {
                if (res.status) {
                    $("#quickSupplierModal").modal('hide');
                    loadSuppliers(res.data ? res.data.id : null);
                    Swal.fire({ icon: 'success', title: 'Supplier Added', text: res.message, timer: 1500, showConfirmButton: false, background: '#ffffff', color: '#1e293b' });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#ffffff', color: '#1e293b' });
                }
            }
        });
    });

    // Product search: a small purpose-built typeahead instead of Select2 -
    // Select2 opens its dropdown synchronously on mousedown, and when this
    // row sits low on a mobile screen it flips to render *above* the
    // trigger, right under the finger that's still down - the matching
    // touchend/mouseup then lands on a result row instead of the trigger
    // and Select2 immediately closes itself again. Owning the interaction
    // here avoids that entirely and behaves identically on touch and mouse.
    let searchResults = [];
    let activeResultIndex = -1;
    let searchDebounce = null;
    let searchRequestSeq = 0;
    const $search = $('#cart-product-search');
    const $searchResults = $('#product-search-results');
    const $searchClear = $('#cart-product-search-clear');

    function escapeHtml(str) {
        return $('<div>').text(str == null ? '' : str).html();
    }

    function renderSearchResults(products) {
        searchResults = products;
        activeResultIndex = -1;
        if (!products.length) {
            $searchResults.html('<div class="product-result-empty">No products found</div>');
        } else {
            $searchResults.html(products.map(function (p, idx) {
                const stock = parseFloat(p.current_stock);
                let stockTxt = stock + ' ' + (p.unit_name || 'PCS');
                if (p.secondary_unit_name && p.conversion_factor) {
                    stockTxt += ' (' + parseFloat((stock * parseFloat(p.conversion_factor)).toFixed(2)) + ' ' + p.secondary_unit_name + ')';
                }
                const price = parseFloat(p.cost_price || p.selling_price || 0).toFixed(2);
                return '<div class="product-result-item" data-idx="' + idx + '">' +
                    '<strong>' + escapeHtml(p.product_name) + '</strong> <span class="text-muted small">(' + escapeHtml(p.sku) + ')</span>' +
                    '<span class="badge bg-light-secondary float-end">' + stockTxt + '</span>' +
                    '<div class="small text-indigo">Cost: ₹' + price + ' | GST: ' + parseFloat(p.gst_percentage || 0) + '%</div>' +
                    '</div>';
            }).join(''));
        }
        $searchResults.addClass('show');
    }

    function hideSearchResults() { $searchResults.removeClass('show'); }

    function fetchAndShowResults(term) {
        // Guard against out-of-order responses (e.g. the "refresh with all
        // products" request fired after selecting a result resolving after a
        // newer, already-typed filtered search) silently clobbering fresher
        // results. Only the most recently *sent* request is ever rendered.
        term = (term || '').trim();
        if (!term) {
            hideSearchResults();
            return;
        }
        const seq = ++searchRequestSeq;
        $.getJSON(BASE_URL + '/api/billing.php', { action: 'search_product', q: term }, function (res) {
            if (seq !== searchRequestSeq) return;
            renderSearchResults(res.status ? res.data : []);
        });
    }

    function setActiveResult(idx) {
        activeResultIndex = idx;
        $searchResults.find('.product-result-item').removeClass('active');
        if (idx >= 0) {
            const $item = $searchResults.find('.product-result-item').eq(idx).addClass('active');
            if ($item.length) $item[0].scrollIntoView({ block: 'nearest' });
        }
    }

    function selectSearchResult(product) {
        addToCart(product);
        $search.val('').focus();
        $searchClear.addClass('d-none');
        fetchAndShowResults('');
    }

    $search.on('focus', function () { fetchAndShowResults($(this).val()); });
    $search.on('input', function () {
        const term = $(this).val();
        $searchClear.toggleClass('d-none', !term);
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(function () { fetchAndShowResults(term); }, 200);
    });
    $search.on('keydown', function (e) {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (searchResults.length) setActiveResult((activeResultIndex + 1) % searchResults.length);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (searchResults.length) setActiveResult((activeResultIndex - 1 + searchResults.length) % searchResults.length);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (activeResultIndex >= 0 && searchResults[activeResultIndex]) selectSearchResult(searchResults[activeResultIndex]);
            else if (searchResults.length === 1) selectSearchResult(searchResults[0]);
        } else if (e.key === 'Escape') {
            hideSearchResults();
        }
    });
    $searchResults.on('click', '.product-result-item', function () {
        const idx = parseInt($(this).data('idx'), 10);
        if (searchResults[idx]) selectSearchResult(searchResults[idx]);
    });
    $searchClear.on('click', function () {
        $search.val('').focus();
        $(this).addClass('d-none');
        fetchAndShowResults('');
    });
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#product-search').length) hideSearchResults();
    });

    function addToCart(item) {
        const existing = cart.find(i => i.id === parseInt(item.id));
        if (existing) {
            existing.qty += 1;
        } else {
            cart.push({
                id: parseInt(item.id),
                product_name: item.product_name,
                sku: item.sku,
                hsn_code: item.hsn_code || '',
                unit_name: item.unit_name || 'PCS',
                qty: 1,
                cost_price: parseFloat(item.cost_price || item.selling_price || 0),
                gst_percentage: parseFloat(item.gst_percentage || 0),
                unit_id: item.unit_id || null,
                secondary_unit_name: item.secondary_unit_name || null,
                secondary_unit_id: item.secondary_unit_id || null,
                conversion_factor: item.conversion_factor ? parseFloat(item.conversion_factor) : null,
                billing_unit_id: item.unit_id || null,
                billing_unit_name: item.unit_name || 'PCS',
                is_secondary_unit: 0,
                original_rate: parseFloat(item.cost_price || item.selling_price || 0)
            });
        }
        renderCart();
    }

    function renderCart() {
        const body = $('#pur-cart-table tbody');
        body.find('tr:not(.cart-add-row):not(.cart-empty-row)').remove();
        if (cart.length === 0) {
            $('.cart-empty-row').show();
            calculateTotals();
            return;
        }
        $('.cart-empty-row').hide();

        cart.forEach((item, index) => {
            const row_total = item.qty * item.cost_price * (1 + item.gst_percentage / 100);

            let unitCell = '';
            if (item.secondary_unit_name && item.conversion_factor) {
                unitCell = '<td><select class="form-select form-select-sm py-1 cart-unit-select" style="width:90px;">' +
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
                        <input type="number" step="1" class="form-control form-control-sm item-qty-input" data-index="${index}" value="${item.qty}" style="width:70px;" min="0">
                    </td>
                    ${unitCell}
                    <td>
                        <input type="number" step="1" class="form-control form-control-sm item-cost-input" data-index="${index}" value="${item.cost_price.toFixed(2)}" style="width:110px;" min="0">
                    </td>
                    <td>
                        <input type="number" step="1" class="form-control form-control-sm item-gst-input" data-index="${index}" value="${item.gst_percentage}" style="width:70px;" min="0">
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
    $("#pur-cart-table").on('input', '.item-qty-input', function() {
        const idx = $(this).data('index'), val = parseFloat($(this).val());
        if (val > 0) { cart[idx].qty = val; renderCart(); }
    });
    $("#pur-cart-table").on('input', '.item-cost-input', function() {
        const idx = $(this).data('index'), val = parseFloat($(this).val());
        if (val >= 0) { cart[idx].cost_price = val; renderCart(); }
    });
    $("#pur-cart-table").on('input', '.item-gst-input', function() {
        const idx = $(this).data('index'), val = parseFloat($(this).val());
        if (val >= 0) { cart[idx].gst_percentage = val; renderCart(); }
    });
    $("#pur-cart-table").on('click', '.btn-remove-item', function() {
        cart.splice($(this).data('index'), 1); renderCart();
    });
    $("#pur-cart-table").on('change', '.cart-unit-select', function () {
        const idx = $(this).closest('tr').data('index');
        const selectedValue = $(this).val();
        const item = cart[idx];
        if (selectedValue === 'secondary' && item.is_secondary_unit === 0) {
            item.cost_price = parseFloat((item.cost_price / item.conversion_factor).toFixed(2));
            item.is_secondary_unit = 1;
            item.billing_unit_id = item.secondary_unit_id;
            item.billing_unit_name = item.secondary_unit_name;
        } else if (selectedValue === 'primary' && item.is_secondary_unit === 1) {
            item.cost_price = parseFloat((item.cost_price * item.conversion_factor).toFixed(2));
            item.is_secondary_unit = 0;
            item.billing_unit_id = item.unit_id;
            item.billing_unit_name = item.unit_name;
        }
        renderCart();
    });
    $("#pur-discount-input").on('input', function() { calculateTotals(); });

    function calculateTotals() {
        let subtotal = 0, tax = 0;
        cart.forEach(item => {
            const base = item.qty * item.cost_price;
            subtotal += base;
            tax += base * (item.gst_percentage / 100);
        });
        const discount = parseFloat($("#pur-discount-input").val()) || 0;
        const grand = subtotal + tax - discount;
        $("#pur-subtotal").text('₹' + subtotal.toFixed(2));
        $("#pur-tax").text('₹' + tax.toFixed(2));
        $("#pur-grand-total").text('₹' + (grand > 0 ? grand.toFixed(2) : '0.00'));
    }

    // Save Purchase Order
    $("#btn-save-purchase").click(function() {
        const supplierId = $("#pur-supplier-select").val();
        const purchaseDate = $("#pur-date").val();
        const paymentStatus = $("#pur-payment-status").val();
        const orderStatus = $("#pur-order-status").val();
        const discount = parseFloat($("#pur-discount-input").val()) || 0;

        if (!supplierId) {
            Swal.fire({ icon: 'warning', title: 'Supplier Missing', text: 'Please select a supplier.', background: '#ffffff', color: '#1e293b' });
            return;
        }
        if (cart.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Cart Empty', text: 'Please add products to checkout.', background: '#ffffff', color: '#1e293b' });
            return;
        }

        $.ajax({
            url: BASE_URL + '/api/purchases.php?action=save',
            type: 'POST',
            data: {
                csrf_token: csrfToken,
                id: editId,
                supplier_id: supplierId,
                purchase_date: purchaseDate,
                payment_status: paymentStatus,
                order_status: orderStatus,
                discount: discount,
                cart: JSON.stringify(cart)
            },
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    Swal.fire({
                        icon: 'success', title: editId > 0 ? 'Purchase Order Updated' : 'Purchase Order Logged', text: res.message,
                        background: '#ffffff', color: '#1e293b'
                    }).then(() => { window.location.href = BASE_URL + '/purchases/index'; });
                } else {
                    Swal.fire({
                        icon: 'error', title: 'Error Saving PO', text: res.message,
                        background: '#ffffff', color: '#1e293b'
                    });
                }
            }
        });
    });
});
</script>
