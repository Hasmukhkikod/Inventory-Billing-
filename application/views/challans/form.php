<?php
/**
 * IIMS v2.0 - Delivery Challan Create / Edit (Full-Width Layout)
 */
$isEdit = !empty($challan);
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fa-solid fa-truck text-indigo me-2"></i><?php echo $isEdit ? 'Edit Delivery Challan' : 'New Delivery Challan'; ?></h4>
        <nav class="text-muted small">Home / Delivery Challans / <?php echo $isEdit ? 'Edit' : 'Create'; ?></nav>
    </div>
    <div>
        <a href="<?php echo BASE_URL; ?>/challans/index.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-list me-1"></i>Back to List</a>
    </div>
</div>

<!-- Section 1: Customer & Challan Info -->
<div class="panel-card mb-4">
    <div class="panel-body py-3">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Customer Name <span class="text-danger">*</span></label>
                <div class="input-group">
                    <select class="form-select searchable-select" id="dc-customer-select" required>
                        <option value="">-- Select Customer --</option>
                    </select>
                    <button class="btn btn-outline-secondary" id="btn-quick-customer" type="button" title="Add Customer">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Challan Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="dc-date" required value="<?php echo $isEdit ? date('Y-m-d', strtotime($challan['challan_date'])) : date('Y-m-d'); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Transport Name</label>
                <input type="text" class="form-control" id="dc-transport" placeholder="e.g. Blue Dart, DTDC..." value="<?php echo $isEdit ? htmlspecialchars($challan['transport_name'] ?? '') : ''; ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Vehicle No</label>
                <input type="text" class="form-control" id="dc-vehicle" placeholder="e.g. GJ-01-AB-1234" value="<?php echo $isEdit ? htmlspecialchars($challan['vehicle_no'] ?? '') : ''; ?>">
            </div>
        </div>
    </div>
</div>

<!-- Section 2: Product Search & Cart -->
<div class="panel-card mb-4">
    <div class="panel-body">
        <!-- Product search moved into the table -->

        <!-- Cart Table (No pricing columns - dispatch document) -->
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0 cart-items-table" id="dc-cart-table">
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Product Name</th>
                        <th style="width:100px;">HSN / SAC</th>
                        <th style="width:80px;">Qty</th>
                        <th style="width:100px;">Unit</th>
                        <th style="width:50px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="cart-add-row">
                        <td colspan="6" class="py-2">
                            <select class="form-select" id="cart-product-select" style="width:100%;">
                                <option value="">Add Product</option>
                            </select>
                        </td>
                    </tr>
                    <tr class="cart-empty-row">
                        <td colspan="6" class="text-center py-4 text-secondary">
                            <i class="fa-solid fa-boxes-stacked fs-3 mb-2 d-block text-muted"></i>
                            Select products above to add them to the delivery challan
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
                    <label class="form-label small">Dispatch Notes</label>
                    <textarea class="form-control form-control-sm" id="dc-notes" rows="4" placeholder="Special instructions..."><?php echo $isEdit ? htmlspecialchars($challan['notes'] ?? '') : ''; ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Dispatch Summary -->
    <div class="col-lg-7">
        <div class="panel-card h-100">
            <div class="panel-body">
                <h6 class="fw-semibold mb-3"><i class="fa-solid fa-truck-fast text-indigo me-2"></i>Dispatch Summary</h6>

                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-secondary">Total Items</td>
                        <td class="text-end fw-bold" id="dc-total-items">0</td>
                    </tr>
                    <tr>
                        <td class="text-secondary">Total Quantity</td>
                        <td class="text-end fw-bold" id="dc-total-qty">0</td>
                    </tr>
                </table>

                <!-- Action Button -->
                <div class="d-flex gap-2 mt-4">
                    <?php echo \App\Models\Helpers::csrfField(); ?>
                    <?php if ($isEdit): ?>
                        <input type="hidden" id="dc-edit-id" value="<?php echo (int)$challan['id']; ?>">
                    <?php endif; ?>
                    <button class="btn btn-success flex-grow-1 py-2 fs-5" id="btn-save-challan">
                        <i class="fa-solid fa-circle-check me-2"></i><?php echo $isEdit ? 'Update Challan' : 'Generate Challan'; ?>
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
    const editId = $('#dc-edit-id').val() || 0;

    // Focus search on Add Product click
    $('#btn-focus-search').click(function() { $('#cart-product-select').select2('open'); });

    // Clear All
    $('#btn-clear-cart').click(function() {
        if (cart.length === 0) return;
        Swal.fire({
            title: 'Clear All Items?', text: 'This will remove all products from the challan.',
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, Clear All',
            background: '#ffffff', color: '#1e293b'
        }).then(r => { if (r.isConfirmed) { cart = []; renderCart(); } });
    });

    // Load Customers
    function loadCustomers(selectId) {
        $.ajax({
            url: BASE_URL + '/api/billing.php?action=get_customers',
            type: 'GET', dataType: 'json',
            success: function(res) {
                const select = $("#dc-customer-select");
                select.find('option:not(:first)').remove();
                if (res.status) {
                    res.data.forEach(c => select.append(`<option value="${c.id}">${c.customer_name} (${c.mobile})</option>`));
                }
                if (selectId) {
                    select.val(selectId);
                }
                <?php if ($isEdit && !empty($challan['customer_id'])): ?>
                else {
                    select.val('<?php echo (int)$challan['customer_id']; ?>');
                }
                <?php endif; ?>
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

    // If editing, load existing cart items
    <?php if ($isEdit): ?>
    $.ajax({
        url: BASE_URL + '/api/challans.php?action=get&id=<?php echo (int)$challan['id']; ?>',
        type: 'GET', dataType: 'json',
        success: function(res) {
            if (res.status && res.data.items) {
                res.data.items.forEach(function(item) {
                    cart.push({
                        product_id: parseInt(item.product_id),
                        product_name: item.product_name,
                        sku: item.sku,
                        hsn_code: item.hsn_code || '',
                        unit_name: item.unit_name || 'PCS',
                        current_stock: parseFloat(item.current_stock || 0),
                        quantity: parseFloat(item.quantity),
                        unit_id: item.unit_id || null,
                        secondary_unit_name: item.secondary_unit_name || null,
                        secondary_unit_id: item.secondary_unit_id || null,
                        conversion_factor: item.conversion_factor ? parseFloat(item.conversion_factor) : null,
                        billing_unit_id: item.billing_unit_id || item.unit_id || null,
                        billing_unit_name: item.billing_unit_name || item.unit_name || 'PCS',
                        is_secondary_unit: parseInt(item.is_secondary_unit || 0)
                    });
                });
                renderCart();
            }
        }
    });
    <?php endif; ?>

    // Product search using Select2
    $('#cart-product-select').select2({
        placeholder: 'Add Product — search by name, SKU or barcode...',
        allowClear: true,
        theme: 'bootstrap-5', width: '100%',
        dropdownParent: $(document.body),
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
            const $select = $(this);
            setTimeout(function () { $select.val(null).trigger('change'); }, 0);
        }
    });

    function addToCart(p) {
        const existing = cart.find(item => item.product_id === parseInt(p.id));
        if (existing) {
            existing.quantity += 1;
        } else {
            cart.push({
                product_id: parseInt(p.id),
                product_name: p.product_name,
                sku: p.sku,
                hsn_code: p.hsn_code || '',
                unit_name: p.unit_name || 'PCS',
                current_stock: parseFloat(p.current_stock || 0),
                quantity: 1,
                unit_id: p.unit_id || null,
                secondary_unit_name: p.secondary_unit_name || null,
                secondary_unit_id: p.secondary_unit_id || null,
                conversion_factor: p.conversion_factor ? parseFloat(p.conversion_factor) : null,
                billing_unit_id: p.unit_id || null,
                billing_unit_name: p.unit_name || 'PCS',
                is_secondary_unit: 0
            });
        }
        renderCart();
    }

    function renderCart() {
        const body = $("#dc-cart-table tbody");
        body.find('tr:not(.cart-add-row):not(.cart-empty-row)').remove();

        if (cart.length === 0) {
            $(".cart-empty-row").show();
            updateSummary();
            return;
        }
        $(".cart-empty-row").hide();

        cart.forEach((item, index) => {
            let unitCell = '';
            if (item.secondary_unit_name && item.conversion_factor) {
                const currentUnit = item.is_secondary_unit ? 'secondary' : 'primary';
                let equivalentText = '';
                if (item.is_secondary_unit) {
                    equivalentText = parseFloat((item.quantity / item.conversion_factor).toFixed(4)) + ' ' + item.unit_name;
                } else {
                    equivalentText = parseFloat((item.quantity * item.conversion_factor).toFixed(2)) + ' ' + item.secondary_unit_name;
                }
                unitCell = `
                    <select class="form-select form-select-sm cart-unit-select" style="width:90px;display:inline-block;">
                        <option value="primary" ${currentUnit === 'primary' ? 'selected' : ''}>${item.unit_name}</option>
                        <option value="secondary" ${currentUnit === 'secondary' ? 'selected' : ''}>${item.secondary_unit_name}</option>
                    </select>
                    <div class="text-muted small mt-1">${equivalentText}</div>
                `;
            } else {
                unitCell = `<span class="text-muted small">${item.billing_unit_name || item.unit_name || 'PCS'}</span>`;
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
                        <input type="number" step="1" class="form-control form-control-sm item-qty-input" data-index="${index}" value="${item.quantity}" style="width:80px;" min="1">
                    </td>
                    <td>${unitCell}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-danger btn-remove-item" data-index="${index}"><i class="fa-solid fa-trash"></i></button>
                    </td>
                </tr>
            `);
        });

        updateSummary();
    }

    function updateSummary() {
        let totalQty = 0;
        cart.forEach(item => totalQty += item.quantity);
        $('#dc-total-items').text(cart.length);
        $('#dc-total-qty').text(totalQty);
    }

    // Cart event handlers
    $("#dc-cart-table").on('input', '.item-qty-input', function() {
        const idx = $(this).data('index'), val = parseInt($(this).val());
        if (val > 0) { cart[idx].quantity = val; renderCart(); }
    });
    $("#dc-cart-table").on('click', '.btn-remove-item', function() {
        cart.splice($(this).data('index'), 1); renderCart();
    });

    // Unit conversion handler
    $("#dc-cart-table").on('change', '.cart-unit-select', function() {
        const idx = $(this).closest('tr').data('index');
        const selectedValue = $(this).val();
        const item = cart[idx];
        if (selectedValue === 'secondary' && item.is_secondary_unit === 0) {
            item.is_secondary_unit = 1;
            item.billing_unit_id = item.secondary_unit_id;
            item.billing_unit_name = item.secondary_unit_name;
        } else if (selectedValue === 'primary' && item.is_secondary_unit === 1) {
            item.is_secondary_unit = 0;
            item.billing_unit_id = item.unit_id;
            item.billing_unit_name = item.unit_name;
        }
        renderCart();
    });

    // Save Challan
    $("#btn-save-challan").click(function() {
        const customerId = $("#dc-customer-select").val();
        const challanDate = $("#dc-date").val();
        const transport = $("#dc-transport").val().trim();
        const vehicle = $("#dc-vehicle").val().trim();
        const notes = $("#dc-notes").val().trim();

        if (!customerId) {
            Swal.fire({ icon: 'warning', title: 'Customer Missing', text: 'Please select a customer.', background: '#ffffff', color: '#1e293b' });
            return;
        }
        if (!challanDate) {
            Swal.fire({ icon: 'warning', title: 'Date Missing', text: 'Please select a challan date.', background: '#ffffff', color: '#1e293b' });
            return;
        }
        if (cart.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Cart Empty', text: 'Please add products to dispatch.', background: '#ffffff', color: '#1e293b' });
            return;
        }

        const cartData = cart.map(item => ({
            product_id: item.product_id,
            quantity: item.quantity,
            billing_unit_id: item.billing_unit_id || item.unit_id,
            billing_unit_name: item.billing_unit_name || item.unit_name || 'PCS',
            is_secondary_unit: item.is_secondary_unit || 0
        }));

        $.ajax({
            url: BASE_URL + '/api/challans.php?action=save',
            type: 'POST',
            data: {
                csrf_token: csrfToken,
                id: editId,
                customer_id: customerId,
                challan_date: challanDate,
                transport_name: transport,
                vehicle_no: vehicle,
                notes: notes,
                cart: JSON.stringify(cartData)
            },
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    Swal.fire({
                        icon: 'success', title: 'Challan Saved', text: res.message,
                        background: '#ffffff', color: '#1e293b'
                    }).then(() => { window.location.href = BASE_URL + '/challans/view.php?id=' + (res.data.challan_id || editId); });
                } else {
                    Swal.fire({
                        icon: 'error', title: 'Error Saving Challan', text: res.message,
                        background: '#ffffff', color: '#1e293b'
                    });
                }
            }
        });
    });
});
</script>
