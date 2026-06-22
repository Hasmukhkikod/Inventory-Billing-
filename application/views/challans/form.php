<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Delivery Challan Creation / Edit Form View
 */
$isEdit = !empty($challan);
?>

<div class="row g-4 text-dark">
    <!-- Items Entry Panel -->
    <div class="col-lg-8">
        <div class="panel-card">
            <div class="panel-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-dark"><i class="fa-solid fa-truck me-2 text-indigo"></i><?php echo $isEdit ? 'Edit Delivery Challan' : 'New Delivery Challan'; ?></h5>
                <a href="<?php echo BASE_URL; ?>/challans/index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to List
                </a>
            </div>

            <div class="panel-body">
                <!-- Search row -->
                <div class="row g-3 mb-4">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Select Customer *</label>
                        <div class="input-group">
                            <select class="form-select searchable-select" id="dc-customer-select" required>
                                <option value="">-- Select Customer --</option>
                            </select>
                            <button class="btn btn-outline-secondary" type="button" id="btn-quick-customer" title="Add New Customer"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="col-md-7 position-relative">
                        <label class="form-label fw-semibold">Search Product to Add *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-barcode text-indigo"></i></span>
                            <input type="text" class="form-control" id="dc-product-search" placeholder="Search by name, SKU, or barcode..." autocomplete="off">
                        </div>
                        <div class="pos-product-search-results d-none w-100" id="dc-search-results-box" style="position: absolute; left: 0; right: 0; z-index: 1000; background: white; border: 1px solid #ddd; max-height: 250px; overflow-y: auto;">
                            <!-- Results loaded via JS -->
                        </div>
                    </div>
                </div>

                <!-- Cart Table (Qty only, no pricing) -->
                <div class="table-responsive" style="min-height: 250px;">
                    <table class="table table-hover align-middle mb-0" id="dc-cart-table">
                        <thead>
                            <tr class="bg-light text-dark">
                                <th>#</th>
                                <th>Product Details</th>
                                <th>Available Stock</th>
                                <th style="width: 120px;">Dispatch Qty</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="cart-empty-row">
                                <td colspan="5" class="text-center py-5 text-secondary">
                                    <i class="fa-solid fa-boxes-stacked fs-2 mb-3 d-block text-muted"></i>
                                    Search and select products above to add them to the delivery challan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Dispatch Details & Save Panel -->
    <div class="col-lg-4">
        <div class="panel-card" style="position: sticky; top: 1rem;">
            <div class="panel-header">
                <h6 class="mb-0 text-dark"><i class="fa-solid fa-truck-fast me-2 text-indigo"></i>Dispatch Details</h6>
            </div>

            <div class="panel-body">
                <div>
                    <?php echo \App\Models\Helpers::csrfField(); ?>
                    <?php if ($isEdit): ?>
                        <input type="hidden" id="dc-edit-id" value="<?php echo (int)$challan['id']; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Challan Date *</label>
                        <input type="date" class="form-control" id="dc-date" required value="<?php echo $isEdit ? date('Y-m-d', strtotime($challan['challan_date'])) : date('Y-m-d'); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Transport Name</label>
                        <input type="text" class="form-control" id="dc-transport" placeholder="e.g. Blue Dart, DTDC..." value="<?php echo $isEdit ? htmlspecialchars($challan['transport_name'] ?? '') : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Vehicle Number</label>
                        <input type="text" class="form-control" id="dc-vehicle" placeholder="e.g. GJ-01-AB-1234" value="<?php echo $isEdit ? htmlspecialchars($challan['vehicle_no'] ?? '') : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes / Remarks</label>
                        <textarea class="form-control" id="dc-notes" rows="3" placeholder="Special instructions..."><?php echo $isEdit ? htmlspecialchars($challan['notes'] ?? '') : ''; ?></textarea>
                    </div>

                    <div class="border-top border-secondary-subtle my-3"></div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Total Items</span>
                        <strong id="dc-total-items">0</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Total Quantity</span>
                        <strong id="dc-total-qty">0</strong>
                    </div>
                </div>

                <div>
                    <button class="btn btn-success w-100 py-3 fs-5" id="btn-save-challan">
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
                <h5 class="modal-title"><i class="fa-solid fa-user-plus me-2 text-indigo"></i>Add Customer</h5>
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

<script>
$(document).ready(function() {
    let cart = [];
    const csrfToken = $('input[name="csrf_token"]').val();
    const editId = $('#dc-edit-id').val() || 0;

    // Load Customers
    function loadCustomers(selectId) {
        $.ajax({
            url: BASE_URL + '/api/billing.php?action=get_customers',
            type: 'GET',
            dataType: 'json',
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
                Swal.fire({ icon: 'success', title: 'Customer Added', timer: 1500, showConfirmButton: false, background: '#ffffff', color: '#0f172a' });
            } else {
                Swal.fire({ icon: 'error', title: 'Failed', text: res.message, background: '#ffffff', color: '#0f172a' });
            }
        }, 'json');
    });

    // If editing, load existing cart items
    <?php if ($isEdit): ?>
    $.ajax({
        url: BASE_URL + '/api/challans.php?action=get&id=<?php echo (int)$challan['id']; ?>',
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            if (res.status && res.data.items) {
                res.data.items.forEach(function(item) {
                    cart.push({
                        product_id: parseInt(item.product_id),
                        product_name: item.product_name,
                        sku: item.sku,
                        current_stock: parseFloat(item.current_stock || 0),
                        quantity: parseFloat(item.quantity)
                    });
                });
                renderCart();
            }
        }
    });
    <?php endif; ?>

    // Product search autocompletion
    $("#dc-product-search").on('input', function() {
        const query = $(this).val().trim();
        if (query.length < 2) {
            $("#dc-search-results-box").addClass('d-none');
            return;
        }

        $.ajax({
            url: BASE_URL + '/api/billing.php?action=search_product&q=' + encodeURIComponent(query),
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                const box = $("#dc-search-results-box");
                box.empty();
                if (res.status && res.data.length > 0) {
                    res.data.forEach(item => {
                        box.append(`
                            <div class="search-result-item p-2 border-bottom" style="cursor: pointer;" data-id="${item.id}">
                                <strong>${item.product_name}</strong> - <span class="text-indigo small">${item.sku}</span>
                                <div class="text-secondary small">Stock: ${item.current_stock} ${item.unit_name || 'Pcs'}</div>
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

    // Handle product selection from search
    $("#dc-search-results-box").on('click', '.search-result-item', function() {
        const id = $(this).data('id');
        $.ajax({
            url: BASE_URL + '/api/products.php?action=get&id=' + id,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    addToCart(res.data);
                    $("#dc-product-search").val('');
                    $("#dc-search-results-box").addClass('d-none');
                }
            }
        });
    });

    // Close search results on outside click
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#dc-product-search, #dc-search-results-box').length) {
            $("#dc-search-results-box").addClass('d-none');
        }
    });

    function addToCart(p) {
        const existing = cart.find(item => item.product_id === p.id);
        if (existing) {
            existing.quantity += 1;
        } else {
            cart.push({
                product_id: p.id,
                product_name: p.product_name,
                sku: p.sku,
                current_stock: parseFloat(p.current_stock || 0),
                quantity: 1
            });
        }
        renderCart();
    }

    function renderCart() {
        const body = $("#dc-cart-table tbody");
        body.find('tr:not(.cart-empty-row)').remove();

        if (cart.length === 0) {
            $(".cart-empty-row").show();
            updateSummary();
            return;
        }

        $(".cart-empty-row").hide();

        cart.forEach((item, index) => {
            body.append(`
                <tr>
                    <td>${index + 1}</td>
                    <td>
                        <strong>${item.product_name}</strong>
                        <span class="text-muted small d-block">SKU: ${item.sku}</span>
                    </td>
                    <td class="text-secondary">${item.current_stock}</td>
                    <td>
                        <input type="number" step="1" class="form-control form-control-sm item-qty-input" data-index="${index}" value="${item.quantity}" style="width: 100px;" min="1">
                    </td>
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

    $("#dc-cart-table").on('input', '.item-qty-input', function() {
        const idx = $(this).data('index');
        const val = parseInt($(this).val());
        if (val > 0) {
            cart[idx].quantity = val;
            updateSummary();
        }
    });

    $("#dc-cart-table").on('click', '.btn-remove-item', function() {
        const idx = $(this).data('index');
        cart.splice(idx, 1);
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
            Swal.fire({ icon: 'warning', title: 'Customer Missing', text: 'Please select a customer.', background: '#ffffff', color: '#0f172a' });
            return;
        }
        if (!challanDate) {
            Swal.fire({ icon: 'warning', title: 'Date Missing', text: 'Please select a challan date.', background: '#ffffff', color: '#0f172a' });
            return;
        }
        if (cart.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Cart Empty', text: 'Please add products to dispatch.', background: '#ffffff', color: '#0f172a' });
            return;
        }

        // Prepare cart data (product_id + quantity only)
        const cartData = cart.map(item => ({
            product_id: item.product_id,
            quantity: item.quantity
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
                        icon: 'success',
                        title: 'Challan Saved',
                        text: res.message,
                        background: '#ffffff',
                        color: '#0f172a'
                    }).then(() => {
                        window.location.href = BASE_URL + '/challans/view.php?id=' + (res.data.challan_id || editId);
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error Saving Challan',
                        text: res.message,
                        background: '#ffffff',
                        color: '#0f172a'
                    });
                }
            }
        });
    });
});
</script>
