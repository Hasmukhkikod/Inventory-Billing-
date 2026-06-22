<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Purchases List View
 */
?>

<div class="panel-card">
    <div class="panel-header">
        <h5 class="mb-0 text-dark"><i class="fa-solid fa-cart-flatbed me-2 text-indigo"></i>Purchases Directory</h5>
        <a href="<?php echo BASE_URL; ?>/purchases/form.php" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i> New Purchase Order
        </a>
    </div>
    
    <div class="panel-body text-dark">
        <div class="bulk-actions-toolbar d-flex align-items-center gap-2 mb-3" data-table="purchasesTable" data-api="<?php echo BASE_URL; ?>/api/purchases.php">
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
            <table class="table table-hover w-100" id="purchasesTable">
                <thead>
                    <tr>
                        <th style="width: 30px;"></th>
                        <th>Purchase No</th>
                        <th>Supplier</th>
                        <th>Purchase Date</th>
                        <th>Payment Status</th>
                        <th>Subtotal (₹)</th>
                        <th>GST (₹)</th>
                        <th>Discount (₹)</th>
                        <th>Total (₹)</th>
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
    $('#purchasesTable').DataTable({
        ajax: {
            url: BASE_URL + '/api/purchases.php?action=list',
            dataSrc: 'data'
        },
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
                data: 'purchase_no',
                className: 'fw-semibold text-dark',
                render: function(data, type, row) {
                    return `<a href="${BASE_URL}/purchases/view.php?id=${row.id}" class="text-indigo text-decoration-none">${data}</a>`;
                }
            },
            { data: 'supplier_name' },
            { 
                data: 'purchase_date',
                render: function(data) {
                    if(!data) return '-';
                    const d = new Date(data);
                    return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
                }
            },
            { 
                data: 'payment_status',
                render: function(data) {
                    const statusMap = {
                        'PAID': 'bg-light-success text-success',
                        'PARTIAL': 'bg-light-warning text-warning',
                        'UNPAID': 'bg-light-danger text-rose'
                    };
                    const cls = statusMap[data] || 'bg-light-secondary';
                    return `<span class="badge ${cls}">${data}</span>`;
                }
            },
            { data: 'subtotal', render: d => '₹' + parseFloat(d).toFixed(2), className: 'text-end font-monospace' },
            { data: 'gst_amount', render: d => '₹' + parseFloat(d).toFixed(2), className: 'text-end font-monospace' },
            { data: 'discount', render: d => '₹' + parseFloat(d).toFixed(2), className: 'text-end font-monospace text-success' },
            { data: 'total_amount', render: d => '₹' + parseFloat(d).toFixed(2), className: 'text-end fw-bold text-dark font-monospace' },
            {
                data: null,
                className: 'text-end',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group">
                            <a href="${BASE_URL}/purchases/view.php?id=${row.id}" class="btn btn-sm btn-outline-secondary py-1 px-2 text-indigo" title="View details">
                                <i class="fa-solid fa-eye"></i> View
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
