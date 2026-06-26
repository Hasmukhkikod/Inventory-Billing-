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
                        <th>Order Status</th>
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

<?php echo \App\Models\Helpers::csrfField(); ?>
<script>
$(document).ready(function() {
    const csrfToken = $('input[name="csrf_token"]').val();
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
            {
                data: 'order_status',
                render: function(data, type, row) {
                    const status = data || 'PENDING';
                    if (status === 'COMPLETED') {
                        return `<span class="badge bg-light-success text-success"><i class="fa-solid fa-circle-check me-1"></i>COMPLETED</span>`;
                    }
                    return `<span class="badge bg-light-warning text-warning" style="cursor:pointer;" onclick="updateOrderStatus(${row.id}, 'COMPLETED')" title="Click to mark as Completed"><i class="fa-solid fa-clock me-1"></i>PENDING</span>`;
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
                    let actions = `<div class="btn-group">
                            <a href="${BASE_URL}/purchases/view.php?id=${row.id}" class="btn btn-sm btn-outline-secondary py-1 px-2 text-indigo" title="View details">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="${BASE_URL}/purchases/form.php?id=${row.id}" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Edit">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                        </div>`;
                    return actions;
                }
            }
        ],
        order: [[1, 'desc']]
    });

    window.updateOrderStatus = function(purchaseId, newStatus) {
        Swal.fire({
            title: 'Mark as Completed?',
            text: 'This will add all items from this PO to inventory. This action can be reversed.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Complete',
            cancelButtonText: 'Cancel',
            background: '#ffffff',
            color: '#1e293b'
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE_URL + '/api/purchases.php?action=update_status',
                    type: 'POST',
                    data: { csrf_token: csrfToken, id: purchaseId, order_status: newStatus },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status) {
                            Swal.fire({ icon: 'success', title: 'Status Updated', text: res.message, timer: 1500, showConfirmButton: false, background: '#ffffff', color: '#1e293b' });
                            $('#purchasesTable').DataTable().ajax.reload(null, false);
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#ffffff', color: '#1e293b' });
                        }
                    },
                    error: function() {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to update status. Please try again.', background: '#ffffff', color: '#1e293b' });
                    }
                });
            }
        });
    };
});
</script>
