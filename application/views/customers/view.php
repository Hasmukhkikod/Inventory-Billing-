<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Customer Detailed Profile & Ledger Statement View
 */
?>
<div class="row g-4">
    <!-- Customer Profile Details Card -->
    <div class="col-md-4">
        <div class="panel-card h-100">
            <div class="panel-header">
                <h6 class="mb-0 text-indigo"><i class="fa-solid fa-circle-info me-2"></i>Customer Profile</h6>
                <a href="<?php echo BASE_URL; ?>/customers/form.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-outline-secondary text-emerald py-1">
                    <i class="fa-solid fa-pencil me-1"></i> Edit
                </a>
            </div>
            
            <div class="panel-body">
                <div class="text-center mb-4">
                    <div class="bg-tertiary rounded-circle text-indigo d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2.2rem; background-color: #f1f5f9;">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <h5 class="text-dark fw-bold mb-1"><?php echo \App\Models\Helpers::sanitize($customer['customer_name']); ?></h5>
                    <span class="badge bg-light-primary"><?php echo \App\Models\Helpers::sanitize($customer['mobile']); ?></span>
                </div>
                
                <div class="border-top border-secondary pt-3">
                    <div class="row g-2 small mb-2">
                        <div class="col-5 text-secondary">Email:</div>
                        <div class="col-7 text-dark fw-semibold text-break"><?php echo \App\Models\Helpers::sanitize($customer['email'] ?: '-'); ?></div>
                    </div>
                    <div class="row g-2 small mb-2">
                        <div class="col-5 text-secondary">GSTIN:</div>
                        <div class="col-7 text-dark fw-semibold text-break"><?php echo \App\Models\Helpers::sanitize($customer['gst_number'] ?: '-'); ?></div>
                    </div>
                    <div class="row g-2 small mb-2">
                        <div class="col-5 text-secondary">Credit Limit:</div>
                        <div class="col-7 text-dark fw-semibold">
                            <?php echo $customer['credit_limit'] > 0 ? '₹ ' . number_format($customer['credit_limit'], 2) : '<span class="text-success">No Limit</span>'; ?>
                        </div>
                    </div>
                    <div class="row g-2 small mb-2">
                        <div class="col-5 text-secondary">Address:</div>
                        <div class="col-7 text-dark fw-semibold text-break"><?php echo nl2br(\App\Models\Helpers::sanitize($customer['address'] ?: '-')); ?></div>
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
                    <span class="text-secondary small me-2">Outstanding credit:</span>
                    <strong class="text-rose fs-6" id="view-outstanding-bal">Loading...</strong>
                </div>
            </div>

            <div class="panel-body">
                <!-- Stat Overview Row -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6 text-center border-md-end border-secondary">
                        <span class="text-secondary small d-block mb-1">Opening Balance</span>
                        <strong class="fs-5 text-dark">₹ <?php echo number_format($customer['opening_balance'], 2); ?></strong>
                    </div>
                    <div class="col-md-6 text-center">
                        <span class="text-secondary small d-block mb-1">Net Outstanding Credit</span>
                        <strong class="fs-5 text-rose fw-bold" id="view-outstanding-bal-stat">₹ 0.00</strong>
                    </div>
                </div>
                
                <h6 class="text-secondary border-bottom pb-2 mb-3">Recent Transactions Ledger</h6>
                
                <div class="table-responsive" style="max-height: 400px;">
                    <table class="table table-hover align-middle mb-0" id="customerLedgerTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Doc Type</th>
                                <th>Doc No</th>
                                <th>Remarks</th>
                                <th class="text-end">Debit (+)</th>
                                <th class="text-end">Credit (-)</th>
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
    const customerId = <?php echo (int)$customer['id']; ?>;
    
    // Load Statement Ledger via AJAX
    $.ajax({
        url: BASE_URL + '/api/customers.php?action=ledger&customer_id=' + customerId,
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            if (res.status) {
                const c = res.data.customer;
                const items = res.data.ledger;
                
                const formattedCredit = '₹' + parseFloat(c.credit_balance).toFixed(2);
                $("#view-outstanding-bal").text(formattedCredit);
                $("#view-outstanding-bal-stat").text(formattedCredit);
                
                if (parseFloat(c.credit_balance) > 0) {
                    $("#view-outstanding-bal").addClass("text-rose").removeClass("text-success");
                    $("#view-outstanding-bal-stat").addClass("text-rose").removeClass("text-success");
                } else {
                    $("#view-outstanding-bal").addClass("text-success").removeClass("text-rose");
                    $("#view-outstanding-bal-stat").addClass("text-success").removeClass("text-rose");
                }

                const body = $("#customerLedgerTable tbody");
                body.empty();

                if (items.length === 0) {
                    body.append('<tr><td colspan="7" class="text-center py-4 text-secondary">No ledger entries for this customer.</td></tr>');
                } else {
                    items.forEach(function(t) {
                        const isInv = t.type === 'INVOICE';
                        const badge = isInv 
                            ? '<span class="badge bg-light-primary">Invoice</span>' 
                            : (t.type === 'OPENING' ? '<span class="badge bg-light-secondary">Opening Balance</span>' : '<span class="badge bg-light-success">Payment</span>');

                        body.append(`
                            <tr>
                                <td>${formatDate(t.date)}</td>
                                <td>${badge}</td>
                                <td class="fw-semibold text-dark">${t.doc_no}</td>
                                <td>${t.notes || '-'}</td>
                                <td class="text-end text-rose fw-bold">${t.debit > 0 ? ('₹' + t.debit.toFixed(2)) : '-'}</td>
                                <td class="text-end text-emerald fw-bold">${t.credit > 0 ? ('₹' + t.credit.toFixed(2)) : '-'}</td>
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
