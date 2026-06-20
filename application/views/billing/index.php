<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Invoices Directory View
 */
?>

<div class="panel-card">
    <div class="panel-header">
        <h5 class="mb-0 text-dark"><i class="fa-solid fa-file-invoice me-2 text-indigo"></i>Invoices Directory</h5>
        <div class="d-flex gap-2">
            <a href="<?php echo BASE_URL; ?>/billing/day_end.php" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-chart-column me-1"></i> Day-End Report
            </a>
            <a href="<?php echo BASE_URL; ?>/billing/form.php" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-cash-register me-1"></i> Open POS Terminal
            </a>
        </div>
    </div>
    
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-hover w-100" id="invoicesTable">
                <thead>
                    <tr>
                        <th>Invoice No</th>
                        <th>Customer</th>
                        <th>Invoice Date</th>
                        <th>Invoice Type</th>
                        <th>Payment Method</th>
                        <th>Total Amount</th>
                        <th>Paid Amount</th>
                        <th>Due Amount</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const invoicesTable = $('#invoicesTable').DataTable({
        ajax: {
            url: BASE_URL + '/api/billing.php?action=list_invoices',
            dataSrc: 'data'
        },
        columns: [
            { 
                data: 'invoice_no', 
                className: 'fw-semibold text-dark',
                render: function(data, type, row) {
                    return `<a href="${BASE_URL}/billing/view.php?id=${row.id}" class="text-indigo text-decoration-none">${data}</a>`;
                }
            },
            { 
                data: 'customer_name', 
                defaultContent: '<span class="text-muted">Walk-in Customer</span>' 
            },
            { 
                data: 'invoice_date',
                render: function(data) {
                    if(!data) return '-';
                    const d = new Date(data);
                    return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
                }
            },
            { data: 'invoice_type' },
            { 
                data: 'payment_method',
                render: function(data) {
                    return `<span class="badge bg-light-primary">${data}</span>`;
                }
            },
            { 
                data: 'grand_total',
                render: function(data) { return '₹' + parseFloat(data).toFixed(2); },
                className: 'fw-semibold text-dark'
            },
            { 
                data: 'paid_amount',
                render: function(data) { return '₹' + parseFloat(data).toFixed(2); },
                className: 'text-emerald'
            },
            { 
                data: 'due_amount',
                render: function(data) { 
                    const val = parseFloat(data);
                    if (val > 0) {
                        return `<span class="text-rose fw-semibold">₹${val.toFixed(2)}</span>`;
                    }
                    return `<span class="text-success">Paid</span>`;
                }
            },
            {
                data: null,
                className: 'text-end',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group">
                            <a href="${BASE_URL}/billing/view.php?id=${row.id}" class="btn btn-sm btn-outline-secondary py-1 px-2 text-indigo" title="View details">
                                <i class="fa-solid fa-eye"></i> View
                            </a>
                            <a href="${BASE_URL}/invoice_print.php?id=${row.id}" target="_blank" class="btn btn-sm btn-outline-secondary py-1 px-2 text-dark" title="Print Invoice">
                                <i class="fa-solid fa-print"></i> Print
                            </a>
                        </div>
                    `;
                }
            }
        ],
        order: [[0, 'desc']]
    });
});
</script>
