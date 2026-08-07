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
            <a href="<?php echo BASE_URL; ?>/billing/day_end" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-chart-column me-1"></i> Day-End Report
            </a>
            <a href="<?php echo BASE_URL; ?>/billing/form" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-cash-register me-1"></i> Create Bill 
            </a>
        </div>
    </div>
    
    <div class="panel-body">
        <div class="bulk-actions-toolbar d-flex align-items-center gap-2 mb-3" data-table="invoicesTable" data-api="<?php echo BASE_URL; ?>/api/billing.php">
            <div class="form-check">
                <input class="form-check-input bulk-select-all" type="checkbox" title="Select All">
            </div>
            <select class="form-select form-select-sm bulk-action-select" style="width: 180px;">
                <option value="">-- Bulk Action --</option>
                <option value="delete">Delete Selected</option>
                <option value="export_csv">Export Selected CSV</option>
            </select>
            <button class="btn btn-sm btn-outline-secondary btn-bulk-apply" disabled>
                <i class="fa-solid fa-check-double me-1"></i>Apply
            </button>
            <span class="badge bg-light-primary small d-none bulk-count">0 selected</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover w-100" id="invoicesTable">
                <thead>
                    <tr>
                        <th style="width: 30px;"></th>
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
        serverSide: true,
        processing: true,
        ajax: {
            url: BASE_URL + '/api/billing.php?action=list_invoices',
            type: 'POST',
            dataSrc: 'data'
        },
        responsive: false, // hand-styled mobile card layout below instead of DataTables' generic collapse
        columns: [
            {
                data: 'id',
                orderable: false,
                className: 'text-center',
                render: function(data) {
                    return '<input type="checkbox" class="form-check-input bulk-check" value="' + data + '">';
                }
            },
            {
                data: 'invoice_no',
                className: 'fw-semibold text-dark',
                render: function(data, type, row) {
                    return `<i class="fa-solid fa-file-invoice mobile-row-icon"></i><a href="${BASE_URL}/billing/view?id=${row.id}" class="text-indigo text-decoration-none">${data}</a>`;
                }
            },
            {
                data: 'customer_name',
                defaultContent: '<i class="fa-solid fa-user mobile-row-icon"></i><span class="text-muted">Walk-in Customer</span>',
                render: function(data) {
                    return '<i class="fa-solid fa-user mobile-row-icon"></i>' + (data || '');
                }
            },
            {
                data: 'invoice_date',
                render: function(data) {
                    if(!data) return '-';
                    const d = new Date(data);
                    return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
                }
            },
            {
                data: 'invoice_type',
                render: function(data) { return '<i class="fa-solid fa-tag mobile-row-icon"></i>' + data; }
            },
            {
                data: 'payment_method',
                render: function(data) {
                    return '<i class="fa-solid fa-credit-card mobile-row-icon"></i><span class="badge bg-light-primary">' + data + '</span>';
                }
            },
            {
                data: 'grand_total',
                render: function(data) { return '<i class="fa-solid fa-indian-rupee-sign mobile-row-icon"></i>₹' + parseFloat(data).toFixed(2); },
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
                    const icon = '<i class="fa-solid fa-circle-check mobile-row-icon"></i>';
                    if (val > 0) {
                        return icon + `<span class="text-rose fw-semibold">₹${val.toFixed(2)} due</span>`;
                    }
                    return icon + `<span class="text-success">Paid</span>`;
                }
            },
            {
                data: null,
                className: 'text-end',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group">
                            <a href="${BASE_URL}/billing/view?id=${row.id}" class="btn btn-sm btn-outline-secondary py-1 px-2 text-indigo" title="View details">
                                <i class="fa-solid fa-eye"></i> View
                            </a>
                            <a href="${BASE_URL}/invoice_print?id=${row.id}" target="_blank" class="btn btn-sm btn-outline-secondary py-1 px-2 text-dark" title="Print Invoice">
                                <i class="fa-solid fa-print"></i> Print
                            </a>
                        </div>
                    `;
                }
            }
        ],
        order: [[1, 'desc']]
    });
});
</script>
