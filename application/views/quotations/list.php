<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Quotations & Estimates List View
 */
?>

<div class="panel-card">
    <div class="panel-header">
        <h5 class="mb-0 text-dark"><i class="fa-solid fa-file-lines me-2 text-indigo"></i>Quotations & Estimates</h5>
        <a href="<?php echo BASE_URL; ?>/quotations/form" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i> Create Quotation
        </a>
    </div>

    <div class="panel-body text-dark">
        <div class="bulk-actions-toolbar d-flex align-items-center gap-2 mb-3" data-table="quotationsTable" data-api="<?php echo BASE_URL; ?>/api/quotations.php">
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
            <table class="table table-hover w-100" id="quotationsTable">
                <thead>
                    <tr>
                        <th style="width: 30px;"></th>
                        <th>Quotation No</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Valid Until</th>
                        <th>Grand Total</th>
                        <th>Status</th>
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

    const quotationsTable = $('#quotationsTable').DataTable({
        ajax: {
            url: BASE_URL + '/api/quotations.php?action=list',
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
                data: 'quotation_no',
                className: 'fw-semibold text-dark',
                render: function(data, type, row) {
                    return `<a href="${BASE_URL}/quotations/view?id=${row.id}" class="text-indigo text-decoration-none">${data}</a>`;
                }
            },
            {
                data: 'customer_name',
                defaultContent: '<span class="text-muted">No Customer</span>'
            },
            {
                data: 'quotation_date',
                render: function(data) {
                    if (!data) return '-';
                    const d = new Date(data);
                    return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
                }
            },
            {
                data: 'valid_until',
                render: function(data) {
                    if (!data) return '-';
                    const d = new Date(data);
                    return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
                }
            },
            {
                data: 'grand_total',
                render: function(data) { return '<span class="font-monospace">₹' + parseFloat(data).toFixed(2) + '</span>'; },
                className: 'text-end fw-bold text-dark'
            },
            {
                data: 'status',
                render: function(data) {
                    const statusMap = {
                        'DRAFT': 'bg-light-primary text-primary',
                        'SENT': 'bg-light-warning text-warning',
                        'ACCEPTED': 'bg-light-success text-success',
                        'REJECTED': 'bg-light-danger text-rose',
                        'CONVERTED': 'bg-light-primary text-indigo'
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
                            <a href="${BASE_URL}/quotations/view?id=${row.id}" class="btn btn-sm btn-outline-secondary py-1 px-2 text-indigo" title="View details">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                    `;

                    if (row.status !== 'CONVERTED') {
                        actions += `
                            <a href="${BASE_URL}/quotations/form?id=${row.id}" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Edit">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                        `;
                    }

                    if (row.status === 'ACCEPTED') {
                        actions += `
                            <button class="btn btn-sm btn-outline-success py-1 px-2 btn-convert-quotation" data-id="${row.id}" data-no="${row.quotation_no}" title="Convert to Invoice">
                                <i class="fa-solid fa-file-invoice-dollar"></i> Convert
                            </button>
                        `;
                    }

                    if (row.status !== 'CONVERTED') {
                        actions += `
                            <button class="btn btn-sm btn-outline-danger py-1 px-2 btn-delete-quotation" data-id="${row.id}" data-no="${row.quotation_no}" title="Delete">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        `;
                    }

                    actions += '</div>';
                    return actions;
                }
            }
        ],
        order: [[1, 'desc']]
    });

    // Delete quotation
    $('#quotationsTable').on('click', '.btn-delete-quotation', function() {
        const id = $(this).data('id');
        const no = $(this).data('no');

        Swal.fire({
            title: 'Delete Quotation?',
            text: 'Are you sure you want to delete ' + no + '?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, Delete',
            background: '#151e30',
            color: '#f3f4f6'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE_URL + '/api/quotations.php?action=delete',
                    type: 'POST',
                    data: { csrf_token: csrfToken, id: id },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status) {
                            Swal.fire({ icon: 'success', title: 'Deleted', text: res.message, background: '#151e30', color: '#f3f4f6' });
                            quotationsTable.ajax.reload();
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#151e30', color: '#f3f4f6' });
                        }
                    }
                });
            }
        });
    });

    // Convert to invoice
    $('#quotationsTable').on('click', '.btn-convert-quotation', function() {
        const id = $(this).data('id');
        const no = $(this).data('no');

        Swal.fire({
            title: 'Convert to Invoice?',
            text: 'This will mark ' + no + ' as CONVERTED and load the items into POS for invoicing.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, Convert',
            background: '#151e30',
            color: '#f3f4f6'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE_URL + '/api/quotations.php?action=convert_to_invoice',
                    type: 'POST',
                    data: { csrf_token: csrfToken, id: id },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status) {
                            // Store quotation data in sessionStorage for POS to pick up
                            sessionStorage.setItem('quotation_cart', JSON.stringify(res.data));
                            Swal.fire({
                                icon: 'success',
                                title: 'Quotation Converted',
                                text: 'Redirecting to POS terminal to create invoice...',
                                background: '#151e30',
                                color: '#f3f4f6',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = BASE_URL + '/billing/form';
                            });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#151e30', color: '#f3f4f6' });
                        }
                    }
                });
            }
        });
    });
});
</script>
