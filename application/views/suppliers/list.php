<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Supplier CRM List View
 */
?>

<div class="panel-card">
    <div class="panel-header">
        <h5 class="mb-0 text-dark"><i class="fa-solid fa-truck-field me-2 text-indigo"></i>Supplier Directory</h5>
        <a href="<?php echo BASE_URL; ?>/suppliers/form.php" class="btn btn-primary btn-sm" id="btn-add-supplier">
            <i class="fa-solid fa-plus me-1"></i> Add Supplier
        </a>
    </div>
    
    <div class="panel-body">
        <div class="bulk-actions-toolbar d-flex align-items-center gap-2 mb-3" data-table="suppliersTable" data-api="<?php echo BASE_URL; ?>/api/suppliers.php">
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
            <table class="table table-hover w-100" id="suppliersTable">
                <thead>
                    <tr>
                        <th style="width: 30px;"></th>
                        <th>Supplier Name</th>
                        <th>Contact Person</th>
                        <th>Mobile</th>
                        <th>Email</th>
                        <th>GST Number</th>
                        <th>Outstanding Payable</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- ================= MODALS ================= -->

<!-- PAY SUPPLIER MODAL -->
<div class="modal fade" id="makePaymentModal" tabindex="-1" aria-labelledby="makePaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="makePaymentModalLabel">Record Outgoing Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="makePaymentForm">
                <div class="modal-body">
                    <input type="hidden" name="supplier_id" id="pay-supp-id" value="0">
                    
                    <div class="mb-3">
                        <label class="form-label">Supplier Name</label>
                        <input type="text" class="form-control" id="pay-supp-name" readonly>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Outstanding Balance</label>
                            <input type="text" class="form-control text-rose fw-bold" id="pay-outstanding" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payment Date</label>
                            <input type="date" class="form-control" name="payment_date" id="pay-date" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Amount Paid (₹) *</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" name="amount" id="pay-amount" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" name="payment_method" id="pay-method" required>
                                <option value="CASH">CASH</option>
                                <option value="UPI">UPI / QR SCAN</option>
                                <option value="CARD">BANK CARD</option>
                                <option value="NET_BANKING">NET BANKING</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Transaction Reference #</label>
                        <input type="text" class="form-control" name="reference_no" placeholder="UTR/Cheque/Transaction ID">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" name="remarks" rows="2" placeholder="Payment notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // DT init
    const suppliersTable = $('#suppliersTable').DataTable({
        ajax: {
            url: BASE_URL + '/api/suppliers.php?action=list',
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
                data: 'supplier_name',
                className: 'fw-semibold text-dark',
                render: function(data, type, row) {
                    return `<a href="${BASE_URL}/suppliers/view.php?id=${row.id}" class="text-indigo text-decoration-none">${data}</a>`;
                }
            },
            { data: 'contact_person', defaultContent: '-' },
            { data: 'mobile' },
            { data: 'email', defaultContent: '-' },
            { data: 'gst_number', defaultContent: '-' },
            {
                data: 'outstanding_balance',
                render: function(data) {
                    const val = parseFloat(data);
                    if (val > 0) {
                        return `<span class="badge bg-light-danger fw-bold"><i class="fa-solid fa-arrow-trend-up me-1"></i>₹${val.toFixed(2)}</span>`;
                    }
                    return `<span class="badge bg-light-success fw-bold">₹0.00</span>`;
                }
            },
            {
                data: null,
                className: 'text-end',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group">
                            <a href="${BASE_URL}/suppliers/view.php?id=${row.id}" class="btn btn-sm btn-outline-secondary py-1 px-2 text-indigo" title="Statement Ledger">
                                <i class="fa-solid fa-list-ul"></i> Statement
                            </a>
                            <button class="btn btn-sm btn-outline-secondary py-1 px-2 text-success btn-pay" data-id="${row.id}" data-name="${row.supplier_name}" data-bal="${row.outstanding_balance}" title="Record Payment Paid" ${parseFloat(row.outstanding_balance) <= 0 ? 'disabled' : ''}>
                                <i class="fa-solid fa-hand-holding-dollar"></i> Pay
                            </button>
                            <a href="${BASE_URL}/suppliers/form.php?id=${row.id}" class="btn btn-sm btn-outline-secondary py-1 px-2 text-emerald" title="Edit supplier">
                                <i class="fa-solid fa-pencil"></i>
                            </a>
                        </div>
                    `;
                }
            }
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search supplier list..."
        }
    });

    // Payment Trigger
    $('#suppliersTable').on('click', '.btn-pay', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const bal = parseFloat($(this).data('bal'));

        $("#pay-supp-id").val(id);
        $("#pay-supp-name").val(name);
        $("#pay-outstanding").val('₹' + bal.toFixed(2));
        $("#pay-amount").val('').attr('max', bal);
        
        $("#makePaymentModal").modal('show');
    });

    $("#makePaymentForm").submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: BASE_URL + '/api/suppliers.php?action=make_payment',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    $("#makePaymentModal").modal('hide');
                    suppliersTable.ajax.reload();
                    Swal.fire({ icon: 'success', title: 'Payment Logged', text: res.message, background: '#ffffff', color: '#0f172a' });
                } else {
                    Swal.fire({ icon: 'error', title: 'Failed', text: res.message, background: '#ffffff', color: '#0f172a' });
                }
            }
        });
    });
});
</script>
