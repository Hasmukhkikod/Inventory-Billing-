<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Delivery Challans List View
 */
?>

<div class="panel-card">
    <div class="panel-header">
        <h5 class="mb-0 text-dark"><i class="fa-solid fa-truck me-2 text-indigo"></i>Delivery Challans</h5>
        <a href="<?php echo BASE_URL; ?>/challans/form" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i> New Challan
        </a>
    </div>

    <div class="panel-body text-dark">
        <div class="bulk-actions-toolbar d-flex align-items-center gap-2 mb-3" data-table="challansTable" data-api="<?php echo BASE_URL; ?>/api/challans.php">
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
            <table class="table table-hover w-100" id="challansTable">
                <thead>
                    <tr>
                        <th style="width: 30px;"></th>
                        <th>Challan No</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Transport</th>
                        <th>Vehicle No</th>
                        <th>Status</th>
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
    const csrfToken = '<?php echo \App\Models\Helpers::getCsrfToken(); ?>';

    const table = $('#challansTable').DataTable({
        ajax: {
            url: BASE_URL + '/api/challans.php?action=list',
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
                data: 'challan_no',
                className: 'fw-semibold text-dark',
                render: function(data, type, row) {
                    return `<a href="${BASE_URL}/challans/view?id=${row.id}" class="text-indigo text-decoration-none">${data}</a>`;
                }
            },
            { data: 'customer_name', render: d => d || '<span class="text-muted">Walk-in</span>' },
            {
                data: 'challan_date',
                render: function(data) {
                    if(!data) return '-';
                    const d = new Date(data);
                    return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
                }
            },
            { data: 'transport_name', render: d => d || '-' },
            { data: 'vehicle_no', render: d => d || '-' },
            {
                data: 'challan_status',
                render: function(data) {
                    const statusMap = {
                        'ACTIVE': 'bg-light-primary text-indigo',
                        'DISPATCHED': 'bg-light-warning text-warning',
                        'DELIVERED': 'bg-light-success text-success',
                        'CANCELLED': 'bg-light-danger text-rose'
                    };
                    const cls = statusMap[data] || 'bg-light-secondary';
                    return `<span class="badge ${cls}">${data}</span>`;
                }
            },
            {
                data: null,
                className: 'text-end',
                orderable: false,
                render: function(data, type, row) {
                    let actions = `
                        <div class="btn-group">
                            <a href="${BASE_URL}/challans/view?id=${row.id}" class="btn btn-sm btn-outline-secondary py-1 px-2 text-indigo" title="View details">
                                <i class="fa-solid fa-eye"></i>
                            </a>`;

                    if (row.challan_status !== 'CANCELLED' && row.challan_status !== 'DELIVERED') {
                        actions += `
                            <a href="${BASE_URL}/challans/form?id=${row.id}" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Edit">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-success py-1 px-2 btn-mark-delivered" data-id="${row.id}" title="Mark Delivered">
                                <i class="fa-solid fa-check"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger py-1 px-2 btn-cancel-challan" data-id="${row.id}" title="Cancel">
                                <i class="fa-solid fa-ban"></i>
                            </button>`;
                    }

                    actions += `
                            <button class="btn btn-sm btn-outline-danger py-1 px-2 btn-delete-challan" data-id="${row.id}" title="Delete">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>`;
                    return actions;
                }
            }
        ],
        order: [[1, 'desc']]
    });

    // Mark as Delivered
    $('#challansTable').on('click', '.btn-mark-delivered', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Mark as Delivered?',
            text: 'This challan will be marked as delivered.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Delivered',
            background: '#151e30',
            color: '#f3f4f6'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(BASE_URL + '/api/challans.php?action=update_status', {
                    csrf_token: csrfToken,
                    id: id,
                    status: 'DELIVERED'
                }, function(res) {
                    if (res.status) {
                        Swal.fire({ icon: 'success', title: 'Updated', text: res.message, background: '#151e30', color: '#f3f4f6' });
                        table.ajax.reload(null, false);
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#151e30', color: '#f3f4f6' });
                    }
                }, 'json');
            }
        });
    });

    // Cancel Challan
    $('#challansTable').on('click', '.btn-cancel-challan', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Cancel Challan?',
            text: 'This challan will be marked as cancelled.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Cancel It',
            background: '#151e30',
            color: '#f3f4f6'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(BASE_URL + '/api/challans.php?action=update_status', {
                    csrf_token: csrfToken,
                    id: id,
                    status: 'CANCELLED'
                }, function(res) {
                    if (res.status) {
                        Swal.fire({ icon: 'success', title: 'Cancelled', text: res.message, background: '#151e30', color: '#f3f4f6' });
                        table.ajax.reload(null, false);
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#151e30', color: '#f3f4f6' });
                    }
                }, 'json');
            }
        });
    });

    // Delete Challan
    $('#challansTable').on('click', '.btn-delete-challan', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete Challan?',
            text: 'This challan will be permanently removed.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Delete',
            confirmButtonColor: '#dc3545',
            background: '#151e30',
            color: '#f3f4f6'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(BASE_URL + '/api/challans.php?action=delete', {
                    csrf_token: csrfToken,
                    id: id
                }, function(res) {
                    if (res.status) {
                        Swal.fire({ icon: 'success', title: 'Deleted', text: res.message, background: '#151e30', color: '#f3f4f6' });
                        table.ajax.reload(null, false);
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#151e30', color: '#f3f4f6' });
                    }
                }, 'json');
            }
        });
    });
});
</script>
