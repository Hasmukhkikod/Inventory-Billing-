<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Supplier Profile & Account Statement View
 */
?>
<div class="row g-4">
    <!-- Supplier Profile Details Card -->
    <div class="col-md-4">
        <div class="panel-card h-100">
            <div class="panel-header">
                <h6 class="mb-0 text-indigo"><i class="fa-solid fa-circle-info me-2"></i>Supplier Profile</h6>
                <a href="<?php echo BASE_URL; ?>/suppliers/form.php?id=<?php echo $supplier['id']; ?>" class="btn btn-sm btn-outline-secondary text-emerald py-1">
                    <i class="fa-solid fa-pencil me-1"></i> Edit
                </a>
            </div>
            
            <div class="panel-body">
                <div class="text-center mb-4">
                    <div class="bg-tertiary rounded-circle text-indigo d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2.2rem; background-color: #f1f5f9;">
                        <i class="fa-solid fa-truck"></i>
                    </div>
                    <h5 class="text-dark fw-bold mb-1"><?php echo \App\Models\Helpers::sanitize($supplier['supplier_name']); ?></h5>
                    <span class="badge bg-light-primary"><?php echo \App\Models\Helpers::sanitize($supplier['mobile']); ?></span>
                </div>
                
                <div class="border-top border-secondary pt-3">
                    <div class="row g-2 small mb-2">
                        <div class="col-5 text-secondary">Contact:</div>
                        <div class="col-7 text-dark fw-semibold"><?php echo \App\Models\Helpers::sanitize($supplier['contact_person'] ?: '-'); ?></div>
                    </div>
                    <div class="row g-2 small mb-2">
                        <div class="col-5 text-secondary">Email:</div>
                        <div class="col-7 text-dark fw-semibold text-break"><?php echo \App\Models\Helpers::sanitize($supplier['email'] ?: '-'); ?></div>
                    </div>
                    <div class="row g-2 small mb-2">
                        <div class="col-5 text-secondary">GSTIN:</div>
                        <div class="col-7 text-dark fw-semibold text-break"><?php echo \App\Models\Helpers::sanitize($supplier['gst_number'] ?: '-'); ?></div>
                    </div>
                    <div class="row g-2 small mb-2">
                        <div class="col-5 text-secondary">Address:</div>
                        <div class="col-7 text-dark fw-semibold text-break"><?php echo nl2br(\App\Models\Helpers::sanitize($supplier['address'] ?: '-')); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Ledger Statement & Financials -->
    <div class="col-md-8">
        <div class="panel-card h-100">
            <div class="panel-header d-flex flex-wrap justify-content-between align-items-center row-gap-2">
                <h6 class="mb-0 text-indigo"><i class="fa-solid fa-file-invoice-dollar me-2"></i>Account Ledger & Statement</h6>
                <div>
                    <span class="text-secondary small me-2">Outstanding Payable:</span>
                    <strong class="text-rose fs-6" id="view-payable-bal">Loading...</strong>
                </div>
            </div>

            <div class="panel-body">
                <!-- Stat Overview Row -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6 text-center border-md-end border-secondary">
                        <span class="text-secondary small d-block mb-1">Opening Balance</span>
                        <strong class="fs-5 text-dark">₹ <?php echo number_format($supplier['opening_balance'], 2); ?></strong>
                    </div>
                    <div class="col-md-6 text-center">
                        <span class="text-secondary small d-block mb-1">Net Outstanding Payable</span>
                        <strong class="fs-5 text-rose fw-bold" id="view-payable-bal-stat">₹ 0.00</strong>
                    </div>
                </div>
                
                <h6 class="text-secondary border-bottom pb-2 mb-3">Recent Transactions Ledger</h6>
                
                <div class="table-responsive" style="max-height: 400px;">
                    <table class="table table-hover align-middle mb-0" id="supplierLedgerTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Doc Type</th>
                                <th>Doc No</th>
                                <th>Remarks</th>
                                <th class="text-end">Debit (-) Paid</th>
                                <th class="text-end">Credit (+) Billed</th>
                                <th class="text-end">Balance (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const supplierId = <?php echo (int)$supplier['id']; ?>;
    
    // Load Statement Ledger via AJAX
    $.ajax({
        url: BASE_URL + '/api/suppliers.php?action=ledger&supplier_id=' + supplierId,
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            if (res.status) {
                const s = res.data.supplier;
                const items = res.data.ledger;
                
                const formattedPayable = '₹' + parseFloat(s.outstanding_balance).toFixed(2);
                $("#view-payable-bal").text(formattedPayable);
                $("#view-payable-bal-stat").text(formattedPayable);
                
                if (parseFloat(s.outstanding_balance) > 0) {
                    $("#view-payable-bal").addClass("text-rose").removeClass("text-success");
                    $("#view-payable-bal-stat").addClass("text-rose").removeClass("text-success");
                } else {
                    $("#view-payable-bal").addClass("text-success").removeClass("text-rose");
                    $("#view-payable-bal-stat").addClass("text-success").removeClass("text-rose");
                }

                const body = $("#supplierLedgerTable tbody");
                body.empty();

                if (items.length === 0) {
                    body.append('<tr><td colspan="7" class="text-center py-4 text-secondary">No ledger entries for this supplier.</td></tr>');
                } else {
                    items.forEach(function(t) {
                        const isPurchase = t.type === 'PURCHASE';
                        const badge = isPurchase 
                            ? '<span class="badge bg-light-primary">Purchase Entry</span>' 
                            : (t.type === 'OPENING' ? '<span class="badge bg-light-secondary">Opening Balance</span>' : '<span class="badge bg-light-success">Payment Paid</span>');

                        body.append(`
                            <tr>
                                <td>${formatDate(t.date)}</td>
                                <td>${badge}</td>
                                <td class="fw-semibold text-dark">${t.doc_no}</td>
                                <td>${t.notes || '-'}</td>
                                <td class="text-end text-emerald fw-bold">${t.debit > 0 ? ('₹' + t.debit.toFixed(2)) : '-'}</td>
                                <td class="text-end text-rose fw-bold">${t.credit > 0 ? ('₹' + t.credit.toFixed(2)) : '-'}</td>
                                <td class="text-end fw-bold text-dark">₹${t.balance.toFixed(2)}</td>
                            </tr>
                        `);
                    });
                }
            }
        }
    });

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
    }
});
</script>
