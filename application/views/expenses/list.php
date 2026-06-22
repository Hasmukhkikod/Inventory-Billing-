<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Expense Record List View
 */
?>

<div class="row g-4 mb-4">
    <div class="col-md-9">
        <div class="panel-card">
            <div class="panel-header">
                <h5 class="mb-0 text-dark"><i class="fa-solid fa-receipt me-2 text-indigo"></i>Expenses Record</h5>
                <a href="<?php echo BASE_URL; ?>/expenses/form.php" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus me-1"></i> Log Expense
                </a>
            </div>
            
            <div class="panel-body">
                <div class="bulk-actions-toolbar d-flex align-items-center gap-2 mb-3" data-table="expensesTable" data-api="<?php echo BASE_URL; ?>/api/expenses.php">
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
                    <table class="table table-hover w-100" id="expensesTable">
                        <thead>
                            <tr>
                                <th style="width: 30px;"></th>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Payment Method</th>
                                <th>Bill Document</th>
                                <th>Amount (₹)</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <!-- Expense Categories Manager -->
        <div class="panel-card">
            <div class="panel-header">
                <h6 class="mb-0 text-dark"><i class="fa-solid fa-tags me-2 text-emerald"></i>Categories</h6>
            </div>
            <div class="panel-body">
                <form id="expenseCategoryForm" class="mb-4">
                    <div class="mb-2">
                        <label class="form-label small">New Category Name</label>
                        <div class="input-group">
                            <input type="text" class="form-control form-control-sm" name="category_name" id="new-cat-name" required placeholder="e.g. Salaries">
                            <button type="submit" class="btn btn-success btn-sm"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>
                </form>
                <div style="max-height: 250px; overflow-y: auto;">
                    <ul class="list-group list-group-flush" id="expense-categories-list">
                        <!-- Loaded dynamically -->
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Load categories
    loadExpenseCategories();

    // DT Init
    const expensesTable = $('#expensesTable').DataTable({
        ajax: {
            url: BASE_URL + '/api/expenses.php?action=list',
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
                data: 'expense_date',
                render: function(data, type, row) {
                    return `<a href="${BASE_URL}/expenses/view.php?id=${row.id}" class="text-indigo text-decoration-none">${formatDate(data)}</a>`;
                }
            },
            { data: 'category_name', className: 'text-dark fw-semibold' },
            { data: 'description', defaultContent: '-' },
            { data: 'payment_method', defaultContent: 'CASH' },
            { 
                data: 'bill_attachment',
                render: function(data) {
                    if (data) {
                        return `<a href="${BASE_URL}/${data}" target="_blank" class="badge bg-light-primary"><i class="fa-solid fa-paperclip me-1"></i>View Bill</a>`;
                    }
                    return `<span class="text-muted small">No receipt</span>`;
                }
            },
            { 
                data: 'amount',
                render: function(data) { return '₹' + parseFloat(data).toFixed(2); },
                className: 'fw-bold text-rose'
            },
            {
                data: null,
                className: 'text-end',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group">
                            <a href="${BASE_URL}/expenses/view.php?id=${row.id}" class="btn btn-sm btn-outline-secondary py-1 px-2 text-indigo" title="View details">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="${BASE_URL}/expenses/form.php?id=${row.id}" class="btn btn-sm btn-outline-secondary py-1 px-2 text-emerald" title="Edit expense">
                                <i class="fa-solid fa-pencil"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-secondary py-1 px-2 text-danger btn-delete" data-id="${row.id}" title="Delete expense">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[1, 'desc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search expense log..."
        }
    });

    // Delete Trigger
    $("#expensesTable").on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete expense?',
            text: "This action will permanently delete this ledger entry!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#dc2626',
            confirmButtonText: 'Yes, delete!',
            background: '#ffffff',
            color: '#0f172a'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE_URL + '/api/expenses.php?action=delete',
                    type: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status) {
                            expensesTable.ajax.reload();
                            Swal.fire({ icon: 'success', title: 'Deleted', text: res.message, background: '#ffffff', color: '#0f172a' });
                        }
                    }
                });
            }
        });
    });

    // Category Creation
    $("#expenseCategoryForm").submit(function(e) {
        e.preventDefault();
        const category_name = $("#new-cat-name").val().trim();
        
        $.ajax({
            url: BASE_URL + '/api/expenses.php?action=category_save',
            type: 'POST',
            data: { category_name: category_name },
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    $("#new-cat-name").val('');
                    loadExpenseCategories();
                    Swal.fire({ icon: 'success', title: 'Added', text: res.message, background: '#ffffff', color: '#0f172a' });
                } else {
                    Swal.fire({ icon: 'error', title: 'Failed', text: res.message, background: '#ffffff', color: '#0f172a' });
                }
            }
        });
    });

    function loadExpenseCategories() {
        $.ajax({
            url: BASE_URL + '/api/expenses.php?action=categories_list',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    const list = $("#expense-categories-list");
                    list.empty();
                    
                    res.data.forEach(c => {
                        list.append(`<li class="list-group-item bg-transparent text-secondary border-secondary px-0 small d-flex justify-content-between">
                            <span><i class="fa-solid fa-tag text-indigo me-2"></i>${c.category_name}</span>
                        </li>`);
                    });
                }
            }
        });
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
    }
});
</script>
