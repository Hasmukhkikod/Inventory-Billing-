<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Returns Directory List View
 */
?>

<div class="panel-card text-dark">
    <div class="panel-header d-flex justify-content-between align-items-center">
        <ul class="nav nav-tabs border-0" id="returnsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active text-indigo border-0 bg-transparent fw-semibold" id="sales-ret-tab" data-bs-toggle="tab" data-bs-target="#sales-ret-pane" type="button" role="tab" aria-controls="sales-ret-pane" aria-selected="true">
                    <i class="fa-solid fa-arrow-turn-down me-2"></i>Sales Returns (Credit Notes)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link text-secondary border-0 bg-transparent fw-semibold" id="purchase-ret-tab" data-bs-toggle="tab" data-bs-target="#purchase-ret-pane" type="button" role="tab" aria-controls="purchase-ret-pane" aria-selected="false">
                    <i class="fa-solid fa-arrow-turn-up me-2"></i>Purchase Returns (Debit Notes)
                </button>
            </li>
        </ul>
        <div class="d-flex gap-2">
            <a href="<?php echo BASE_URL; ?>/returns/form?type=SALES" class="btn btn-outline-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i> New Sales Return
            </a>
            <a href="<?php echo BASE_URL; ?>/returns/form?type=PURCHASE" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-plus me-1"></i> New Purchase Return
            </a>
        </div>
    </div>
    
    <div class="panel-body text-dark">
        <div class="tab-content" id="returnsTabsContent">
            
            <!-- SALES RETURNS TAB -->
            <div class="tab-pane fade show active" id="sales-ret-pane" role="tabpanel" aria-labelledby="sales-ret-tab" tabindex="0">
                <div class="table-responsive">
                    <table class="table table-hover w-100" id="salesReturnsTable">
                        <thead>
                            <tr>
                                <th>Return No</th>
                                <th>Original Invoice</th>
                                <th>Customer</th>
                                <th>Return Date</th>
                                <th>Total Refund (₹)</th>
                                <th>Remarks</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- PURCHASE RETURNS TAB -->
            <div class="tab-pane fade" id="purchase-ret-pane" role="tabpanel" aria-labelledby="purchase-ret-tab" tabindex="0">
                <div class="table-responsive">
                    <table class="table table-hover w-100" id="purchaseReturnsTable">
                        <thead>
                            <tr>
                                <th>Return No</th>
                                <th>Original PO No</th>
                                <th>Supplier</th>
                                <th>Return Date</th>
                                <th>Total Value (₹)</th>
                                <th>Remarks</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Sales returns datatable
    const salesTable = $('#salesReturnsTable').DataTable({
        ajax: {
            url: BASE_URL + '/api/returns.php?action=list_sales',
            dataSrc: 'data'
        },
        columns: [
            { 
                data: 'return_no', 
                className: 'fw-semibold text-dark',
                render: function(data, type, row) {
                    return `<a href="${BASE_URL}/returns/view?id=${row.id}&type=SALES" class="text-indigo text-decoration-none">${data}</a>`;
                }
            },
            { data: 'invoice_no', defaultContent: '-' },
            { data: 'customer_name', defaultContent: '<span class="text-muted">Walk-in Customer</span>' },
            { 
                data: 'return_date',
                render: function(data) {
                    if(!data) return '-';
                    const d = new Date(data);
                    return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
                }
            },
            { data: 'total_amount', render: d => '₹' + parseFloat(d).toFixed(2), className: 'text-end fw-bold text-dark font-monospace' },
            { data: 'remarks', defaultContent: '-' },
            {
                data: null,
                className: 'text-end',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <a href="${BASE_URL}/returns/view?id=${row.id}&type=SALES" class="btn btn-sm btn-outline-secondary py-1 px-2 text-indigo">
                            <i class="fa-solid fa-eye"></i> View
                        </a>
                    `;
                }
            }
        ],
        order: [[0, 'desc']]
    });

    // Purchase returns datatable
    const purchaseTable = $('#purchaseReturnsTable').DataTable({
        ajax: {
            url: BASE_URL + '/api/returns.php?action=list_purchase',
            dataSrc: 'data'
        },
        columns: [
            { 
                data: 'return_no', 
                className: 'fw-semibold text-dark',
                render: function(data, type, row) {
                    return `<a href="${BASE_URL}/returns/view?id=${row.id}&type=PURCHASE" class="text-indigo text-decoration-none">${data}</a>`;
                }
            },
            { data: 'purchase_no', defaultContent: '-' },
            { data: 'supplier_name' },
            { 
                data: 'return_date',
                render: function(data) {
                    if(!data) return '-';
                    const d = new Date(data);
                    return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
                }
            },
            { data: 'total_amount', render: d => '₹' + parseFloat(d).toFixed(2), className: 'text-end fw-bold text-dark font-monospace' },
            { data: 'remarks', defaultContent: '-' },
            {
                data: null,
                className: 'text-end',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <a href="${BASE_URL}/returns/view?id=${row.id}&type=PURCHASE" class="btn btn-sm btn-outline-secondary py-1 px-2 text-indigo">
                            <i class="fa-solid fa-eye"></i> View
                        </a>
                    `;
                }
            }
        ],
        order: [[0, 'desc']]
    });

    // Reload tab tables on click
    $('#purchase-ret-tab').click(() => purchaseTable.ajax.reload());
    $('#sales-ret-tab').click(() => salesTable.ajax.reload());
});
</script>
