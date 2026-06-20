<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Purchase Order Creation / Checkout View
 */
?>

<div class="row g-4 text-dark">
    <!-- Items Entry Panel -->
    <div class="col-lg-8">
        <div class="panel-card">
            <div class="panel-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-dark"><i class="fa-solid fa-cart-flatbed me-2 text-indigo"></i>New Purchase Order</h5>
                <a href="<?php echo BASE_URL; ?>/purchases/index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to List
                </a>
            </div>
            
            <div class="panel-body">
                <!-- Search row -->
                <div class="row g-3 mb-4">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Select Supplier *</label>
                        <select class="form-select" id="pur-supplier-select" required>
                            <option value="">-- Select Supplier --</option>
                        </select>
                    </div>
                    <div class="col-md-7 position-relative">
                        <label class="form-label fw-semibold">Search Product to Add *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-barcode text-indigo"></i></span>
                            <input type="text" class="form-control" id="pur-product-search" placeholder="Search by name, SKU, or barcode..." autocomplete="off">
                        </div>
                        <div class="pos-product-search-results d-none w-100" id="pur-search-results-box" style="position: absolute; left: 0; right: 0; z-index: 1000; background: white; border: 1px solid #ddd; max-height: 250px; overflow-y: auto;">
                            <!-- Results loaded via JS -->
                        </div>
                    </div>
                </div>

                <!-- Cart Table -->
                <div class="table-responsive" style="min-height: 250px;">
                    <table class="table table-hover align-middle mb-0" id="pur-cart-table">
                        <thead>
                            <tr class="bg-light text-dark">
                                <th>#</th>
                                <th>Product Details</th>
                                <th style="width: 100px;">Qty</th>
                                <th style="width: 140px;">Cost Price (₹)</th>
                                <th style="width: 100px;">GST %</th>
                                <th class="text-end" style="width: 140px;">Total (₹)</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="cart-empty-row">
                                <td colspan="7" class="text-center py-5 text-secondary">
                                    <i class="fa-solid fa-basket-shopping fs-2 mb-3 d-block text-muted"></i>
                                    Search and select products above to add them to the purchase order.
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
                <h6 class="mb-0 text-dark"><i class="fa-solid fa-file-invoice-dollar me-2 text-indigo"></i>Checkout Summary</h6>
            </div>
            
            <div class="panel-body d-flex flex-column justify-content-between h-100">
                <div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Purchase Date *</label>
                        <input type="date" class="form-control" id="pur-date" required value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Status</label>
                        <select class="form-select" id="pur-payment-status">
                            <option value="UNPAID" selected>UNPAID</option>
                            <option value="PARTIAL">PARTIAL</option>
                            <option value="PAID">PAID</option>
                        </select>
                    </div>

                    <div class="border-top border-secondary-subtle my-3"></div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Subtotal (Taxable)</span>
                        <strong id="pur-subtotal">₹0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">GST Tax Amount</span>
                        <strong id="pur-tax">₹0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-secondary">Flat Discount (₹)</span>
                        <input type="number" step="0.01" class="form-control text-end py-1" style="width: 120px;" id="pur-discount-input" value="0.00">
                    </div>

                    <hr class="my-3 border-dark">

                    <div class="d-flex justify-content-between fw-bold text-dark fs-5 mb-4">
                        <span>Grand Total</span>
                        <span id="pur-grand-total">₹0.00</span>
                    </div>
                </div>

                <div>
                    <button class="btn btn-success w-100 py-3 fs-5" id="btn-save-purchase">
                        <i class="fa-solid fa-circle-check me-2"></i>Generate PO Entry
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

    // Load Suppliers
    $.ajax({
        url: BASE_URL + '/api/suppliers.php?action=list',
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            const select = $("#pur-supplier-select");
            if (res.status) {
                res.data.forEach(s => select.append(`<option value="${s.id}">${s.supplier_name} (${s.mobile})</option>`));
            }
        }
    });

    // Product search autocompletion
    $("#pur-product-search").on('input', function() {
        const query = $(this).val().trim();
        if (query.length < 2) {
            $("#pur-search-results-box").addClass('d-none');
            return;
        }

        $.ajax({
            url: BASE_URL + '/api/billing.php?action=search_product&q=' + encodeURIComponent(query),
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                const box = $("#pur-search-results-box");
                box.empty();
                if (res.status && res.data.length > 0) {
                    res.data.forEach(item => {
                        box.append(`
                            <div class="search-result-item p-2 border-bottom" style="cursor: pointer;" data-id="${item.id}">
                                <strong>${item.product_name}</strong> - <span class="text-indigo small">${item.sku}</span>
                                <div class="text-secondary small">Cost Price: ₹${parseFloat(item.cost_price || 0).toFixed(2)} | Stock: ${item.current_stock}</div>
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
    $("#pur-search-results-box").on('click', '.search-result-item', function() {
        const id = $(this).data('id');
        $.ajax({
            url: BASE_URL + '/api/products.php?action=get&id=' + id,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    addToCart(res.data);
                    $("#pur-product-search").val('');
                    $("#pur-search-results-box").addClass('d-none');
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
                cost_price: parseFloat(p.cost_price || 0),
                gst_percentage: parseFloat(p.gst_percentage || 18)
            });
        }
        renderCart();
    }

    function renderCart() {
        const body = $("#pur-cart-table tbody");
        body.find('tr:not(.cart-empty-row)').remove();
        
        if (cart.length === 0) {
            $(".cart-empty-row").show();
            calculateTotals();
            return;
        }
        
        $(".cart-empty-row").hide();
        
        cart.forEach((item, index) => {
            const row_total = item.qty * item.cost_price * (1 + item.gst_percentage / 100);
            body.append(`
                <tr>
                    <td>${index + 1}</td>
                    <td>
                        <strong>${item.product_name}</strong>
                        <span class="text-muted small d-block">SKU: ${item.sku}</span>
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control form-control-sm item-qty-input" data-index="${index}" value="${item.qty}" style="width: 80px;" min="0.01">
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control form-control-sm item-cost-input" data-index="${index}" value="${item.cost_price.toFixed(2)}" style="width: 120px;" min="0.00">
                    </td>
                    <td>${item.gst_percentage}%</td>
                    <td class="text-end fw-bold text-dark font-monospace">₹${row_total.toFixed(2)}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-danger btn-remove-item" data-index="${index}"><i class="fa-solid fa-trash"></i></button>
                    </td>
                </tr>
            `);
        });
        
        calculateTotals();
    }

    $("#pur-cart-table").on('input', '.item-qty-input', function() {
        const idx = $(this).data('index');
        const val = parseFloat($(this).val());
        if (val > 0) {
            cart[idx].qty = val;
            renderCart();
        }
    });

    $("#pur-cart-table").on('input', '.item-cost-input', function() {
        const idx = $(this).data('index');
        const val = parseFloat($(this).val());
        if (val >= 0) {
            cart[idx].cost_price = val;
            renderCart();
        }
    });

    $("#pur-cart-table").on('click', '.btn-remove-item', function() {
        const idx = $(this).data('index');
        cart.splice(idx, 1);
        renderCart();
    });

    $("#pur-discount-input").on('input', function() {
        calculateTotals();
    });

    function calculateTotals() {
        let subtotal = 0;
        let tax = 0;
        
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

    $("#btn-save-purchase").click(function() {
        const supplierId = $("#pur-supplier-select").val();
        const purchaseDate = $("#pur-date").val();
        const paymentStatus = $("#pur-payment-status").val();
        const discount = parseFloat($("#pur-discount-input").val()) || 0;
        
        if (!supplierId) {
            Swal.fire({ icon: 'warning', title: 'Supplier Missing', text: 'Please select a supplier.' });
            return;
        }
        if (cart.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Cart Empty', text: 'Please add products to checkout.' });
            return;
        }

        $.ajax({
            url: BASE_URL + '/api/purchases.php?action=save',
            type: 'POST',
            data: {
                csrf_token: csrfToken,
                supplier_id: supplierId,
                purchase_date: purchaseDate,
                payment_status: paymentStatus,
                discount: discount,
                cart: JSON.stringify(cart)
            },
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    Swal.fire({ 
                        icon: 'success', 
                        title: 'Purchase Order Logged', 
                        text: res.message, 
                        background: '#ffffff', 
                        color: '#0f172a' 
                    }).then(() => {
                        window.location.href = BASE_URL + '/purchases/index.php';
                    });
                } else {
                    Swal.fire({ 
                        icon: 'error', 
                        title: 'Error Saving PO', 
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
