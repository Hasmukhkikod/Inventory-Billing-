<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Return Order Logging / Form View
 */
$type = $_GET['type'] ?? 'SALES'; // SALES or PURCHASE
?>

<div class="row g-4 text-dark">
    <!-- Items Return Form Panel -->
    <div class="col-lg-8">
        <div class="panel-card">
            <div class="panel-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-dark">
                    <i class="fa-solid fa-rotate-left me-2 text-indigo"></i>
                    Log <?php echo $type === 'SALES' ? 'Sales Return (Credit Note)' : 'Purchase Return (Debit Note)'; ?>
                </h5>
                <a href="<?php echo BASE_URL; ?>/returns/index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to List
                </a>
            </div>
            
            <div class="panel-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <?php if ($type === 'SALES'): ?>
                            <label class="form-label fw-semibold">Select Original Invoice *</label>
                            <select class="form-select searchable-select" id="ret-doc-select" required>
                                <option value="">-- Choose Invoice --</option>
                            </select>
                        <?php else: ?>
                            <label class="form-label fw-semibold">Select Original Purchase Order *</label>
                            <select class="form-select searchable-select" id="ret-doc-select" required>
                                <option value="">-- Choose Purchase Order --</option>
                            </select>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Return Date *</label>
                        <input type="date" class="form-control" id="ret-date" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>

                <!-- Document Items Table -->
                <div class="table-responsive" style="min-height: 250px;">
                    <table class="table table-hover align-middle mb-0" id="ret-items-table">
                        <thead>
                            <tr class="bg-light text-dark">
                                <th style="width: 40px;">#</th>
                                <th>Product Details</th>
                                <th class="text-center" style="width: 140px;">Purchased Qty</th>
                                <th class="text-center" style="width: 140px;">Return Qty</th>
                                <th class="text-end" style="width: 120px;">Price (₹)</th>
                                <th class="text-end" style="width: 120px;">Refund Total (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="doc-empty-row">
                                <td colspan="6" class="text-center py-5 text-secondary">
                                    <i class="fa-solid fa-file-invoice fs-2 mb-3 d-block text-muted"></i>
                                    Please select an document above to load the purchase items details.
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
        <div class="panel-card" style="position: sticky; top: 1rem;">
            <div class="panel-header">
                <h6 class="mb-0 text-dark"><i class="fa-solid fa-receipt me-2 text-indigo"></i>Return Summary</h6>
            </div>

            <div class="panel-body">
                <div>
                    <?php echo \App\Models\Helpers::csrfField(); ?>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Return Remarks / Reason *</label>
                        <textarea class="form-control" id="ret-remarks" rows="4" required placeholder="Reason for returning items..."></textarea>
                    </div>

                    <div class="border-top border-secondary-subtle my-3"></div>

                    <div class="d-flex justify-content-between fw-bold text-dark fs-5 mb-4">
                        <span>Total Refund/Credit</span>
                        <span id="ret-grand-total">₹0.00</span>
                    </div>
                </div>

                <div>
                    <button class="btn btn-success w-100 py-3 fs-5" id="btn-save-return">
                        <i class="fa-solid fa-circle-check me-2"></i>Save Return Transaction
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const returnType = '<?php echo $type; ?>';
    let docItems = [];
    const csrfToken = $('input[name="csrf_token"]').val();

    // 1. Fetch Source Documents (Invoices or Purchases)
    if (returnType === 'SALES') {
        $.ajax({
            url: BASE_URL + '/api/billing.php?action=list_invoices',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                const select = $("#ret-doc-select");
                if (res.status) {
                    res.data.forEach(i => {
                        const name = i.customer_name || 'Walk-in Customer';
                        select.append(`<option value="${i.id}">${i.invoice_no} | ${name} | ₹${parseFloat(i.grand_total).toFixed(2)}</option>`);
                    });
                }
            }
        });
    } else {
        $.ajax({
            url: BASE_URL + '/api/purchases.php?action=list',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                const select = $("#ret-doc-select");
                if (res.status) {
                    res.data.forEach(p => {
                        select.append(`<option value="${p.id}">${p.purchase_no} | ${p.supplier_name} | ₹${parseFloat(p.total_amount).toFixed(2)}</option>`);
                    });
                }
            }
        });
    }

    // 2. Load Items on Document selection
    $("#ret-doc-select").change(function() {
        const id = $(this).val();
        if (!id) {
            $(".doc-empty-row").show();
            $("#ret-items-table tbody tr:not(.doc-empty-row)").remove();
            docItems = [];
            calculateTotals();
            return;
        }

        const url = returnType === 'SALES' 
            ? BASE_URL + '/api/returns.php?action=get_invoice_items&invoice_id=' + id
            : BASE_URL + '/api/returns.php?action=get_purchase_items&purchase_id=' + id;

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    docItems = res.data;
                    renderDocItems();
                }
            }
        });
    });

    function renderDocItems() {
        const body = $("#ret-items-table tbody");
        body.find('tr:not(.doc-empty-row)').remove();
        
        if (docItems.length === 0) {
            $(".doc-empty-row").show();
            calculateTotals();
            return;
        }
        
        $(".doc-empty-row").hide();
        
        docItems.forEach((item, index) => {
            const price = returnType === 'SALES' ? parseFloat(item.rate) : parseFloat(item.cost_price);
            item.return_qty = item.return_qty || 0;
            item.item_price = price;
            
            body.append(`
                <tr>
                    <td>${index + 1}</td>
                    <td>
                        <strong>${item.product_name}</strong>
                        <span class="text-muted small d-block">SKU: ${item.sku}</span>
                    </td>
                    <td class="text-center fw-semibold text-secondary">${parseFloat(item.quantity)} ${item.unit_name || 'Pcs'}</td>
                    <td>
                        <input type="number" step="0.01" class="form-control form-control-sm item-ret-qty" data-index="${index}" value="${item.return_qty}" max="${parseFloat(item.quantity)}" min="0" style="width: 100px;">
                    </td>
                    <td class="text-end font-monospace">₹${price.toFixed(2)}</td>
                    <td class="text-end fw-bold text-dark font-monospace row-refund-total">₹${(item.return_qty * price).toFixed(2)}</td>
                </tr>
            `);
        });

        calculateTotals();
    }

    // Handle quantity changes
    $("#ret-items-table").on('input', '.item-ret-qty', function() {
        const idx = $(this).data('index');
        const val = parseFloat($(this).val()) || 0;
        const max = parseFloat($(this).attr('max'));
        
        if (val < 0) {
            $(this).val(0);
            docItems[idx].return_qty = 0;
        } else if (val > max) {
            $(this).val(max);
            docItems[idx].return_qty = max;
        } else {
            docItems[idx].return_qty = val;
        }

        const price = docItems[idx].item_price;
        $(this).closest('tr').find('.row-refund-total').text('₹' + (docItems[idx].return_qty * price).toFixed(2));
        
        calculateTotals();
    });

    function calculateTotals() {
        let total = 0;
        docItems.forEach(item => {
            if (item.return_qty > 0) {
                total += item.return_qty * item.item_price;
            }
        });
        $("#ret-grand-total").text('₹' + total.toFixed(2));
    }

    // Save Return trigger
    $("#btn-save-return").click(function() {
        const docId = $("#ret-doc-select").val();
        const date = $("#ret-date").val();
        const remarks = $("#ret-remarks").val().trim();
        
        if (!docId) {
            Swal.fire({ icon: 'warning', title: 'Document Required', text: 'Please select a document.' });
            return;
        }
        if (!remarks) {
            Swal.fire({ icon: 'warning', title: 'Remarks Required', text: 'Please write a reason for return.' });
            return;
        }

        const returnItems = docItems.filter(item => item.return_qty > 0).map(item => ({
            product_id: item.product_id,
            qty: item.return_qty,
            rate: item.item_price,
            cost_price: item.item_price // for purchases
        }));

        if (returnItems.length === 0) {
            Swal.fire({ icon: 'warning', title: 'No Items selected', text: 'Please enter return quantity > 0 for at least one item.' });
            return;
        }

        const saveUrl = returnType === 'SALES'
            ? BASE_URL + '/api/returns.php?action=save_sales'
            : BASE_URL + '/api/returns.php?action=save_purchase';

        const dataKey = returnType === 'SALES' ? 'invoice_id' : 'purchase_id';

        $.ajax({
            url: saveUrl,
            type: 'POST',
            data: {
                csrf_token: csrfToken,
                [dataKey]: docId,
                return_date: date,
                remarks: remarks,
                cart: JSON.stringify(returnItems)
            },
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    Swal.fire({ 
                        icon: 'success', 
                        title: 'Return Logged successfully', 
                        text: res.message, 
                        background: '#ffffff', 
                        color: '#0f172a' 
                    }).then(() => {
                        window.location.href = BASE_URL + '/returns/index.php';
                    });
                } else {
                    Swal.fire({ 
                        icon: 'error', 
                        title: 'Error Logging Return', 
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
