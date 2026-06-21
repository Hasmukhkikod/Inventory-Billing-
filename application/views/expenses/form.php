<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Expense Form Page (Log / Edit)
 */
$isEdit = !empty($expense);
?>
<div class="panel-card">
    <div class="panel-header">
        <h5 class="mb-0 text-indigo">
            <i class="fa-solid fa-receipt me-2"></i><?php echo $isEdit ? 'Edit Expense Details' : 'Log Business Expense'; ?>
        </h5>
        <a href="<?php echo BASE_URL; ?>/expenses/index.php" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Expenses
        </a>
    </div>
    
    <div class="panel-body">
        <form id="expenseForm" enctype="multipart/form-data">
            <?php echo \App\Models\Helpers::csrfField(); ?>
            <input type="hidden" name="id" id="exp-id" value="<?php echo $isEdit ? (int)$expense['id'] : '0'; ?>">
            <input type="hidden" name="existing_bill" id="exp-existing-bill" value="<?php echo $isEdit ? \App\Models\Helpers::sanitize($expense['bill_attachment']) : ''; ?>">
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Expense Category *</label>
                    <div class="input-group">
                        <select class="form-select" name="category_id" id="exp-category" required>
                            <option value="">-- Choose Category --</option>
                        </select>
                        <button class="btn btn-outline-secondary" type="button" id="btn-quick-exp-category" title="Add New Category"><i class="fa-solid fa-plus"></i></button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Date *</label>
                    <input type="date" class="form-control" name="expense_date" id="exp-date" required value="<?php echo $isEdit ? \App\Models\Helpers::sanitize($expense['expense_date']) : date('Y-m-d'); ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Amount Spent (₹) *</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="0.01" min="0.01" class="form-control" name="amount" id="exp-amount" required value="<?php echo $isEdit ? (float)$expense['amount'] : ''; ?>">
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Payment Method *</label>
                    <select class="form-select" name="payment_method" id="exp-method" required>
                        <option value="CASH" <?php echo ($isEdit && $expense['payment_method'] === 'CASH') ? 'selected' : ''; ?>>CASH</option>
                        <option value="UPI" <?php echo ($isEdit && $expense['payment_method'] === 'UPI') ? 'selected' : ''; ?>>UPI / QR SCAN</option>
                        <option value="CARD" <?php echo ($isEdit && $expense['payment_method'] === 'CARD') ? 'selected' : ''; ?>>BANK CARD</option>
                        <option value="NET_BANKING" <?php echo ($isEdit && $expense['payment_method'] === 'NET_BANKING') ? 'selected' : ''; ?>>NET BANKING</option>
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Description / Remarks</label>
                    <textarea class="form-control" name="description" id="exp-desc" rows="3" placeholder="Expense description..."><?php echo $isEdit ? \App\Models\Helpers::sanitize($expense['description']) : ''; ?></textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Attach Bill Copy (PDF/JPG/PNG)</label>
                    <input type="file" class="form-control" name="bill_copy" id="exp-bill">
                    <div class="mt-2 text-muted small">Max file size: 2MB</div>
                    <?php if ($isEdit && !empty($expense['bill_attachment'])): ?>
                    <div class="mt-2" id="exp-bill-preview-wrapper">
                        <i class="fa-solid fa-paperclip text-indigo me-2"></i>
                        <a href="<?php echo BASE_URL . '/' . \App\Models\Helpers::sanitize($expense['bill_attachment']); ?>" target="_blank" class="text-indigo small fw-semibold">View Current Attachment</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="mt-4 pt-3 border-top border-secondary text-end">
                <a href="<?php echo BASE_URL; ?>/expenses/index.php" class="btn btn-outline-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-circle-check me-1"></i><?php echo $isEdit ? 'Update Expense' : 'Save Expense'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Quick Add Expense Category Modal -->
<div class="modal fade" id="quickExpCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Add Expense Category</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Category Name *</label>
                <input type="text" class="form-control" id="new-exp-category-name" placeholder="e.g. Office Supplies" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="btn-save-exp-category">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const selectedCategory = '<?php echo $isEdit ? (int)$expense['category_id'] : ''; ?>';
    
    // Load category dropdown options
    $.ajax({
        url: BASE_URL + '/api/expenses.php?action=categories_list',
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            const select = $("#exp-category");
            if (res.status) {
                res.data.forEach(c => {
                    const selAttr = c.id == selectedCategory ? 'selected' : '';
                    select.append(`<option value="${c.id}" ${selAttr}>${c.category_name}</option>`);
                });
            }
        }
    });

    // Quick Add Expense Category
    $('#btn-quick-exp-category').click(function() {
        $('#new-exp-category-name').val('');
        $('#quickExpCategoryModal').modal('show');
    });

    $('#btn-save-exp-category').click(function() {
        const name = $('#new-exp-category-name').val().trim();
        if (!name) return;
        $.ajax({
            url: BASE_URL + '/api/expenses.php?action=category_save',
            type: 'POST',
            data: { csrf_token: $('input[name="csrf_token"]').val(), category_name: name },
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    $('#exp-category').append(`<option value="${res.data.id}" selected>${res.data.name}</option>`);
                    $('#quickExpCategoryModal').modal('hide');
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                }
            }
        });
    });

    $("#expenseForm").submit(function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'save');
        
        $.ajax({
            url: BASE_URL + '/api/expenses.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    Swal.fire({ 
                        icon: 'success', 
                        title: 'Saved', 
                        text: res.message, 
                        background: '#ffffff', 
                        color: '#0f172a' 
                    }).then(() => {
                        window.location.href = BASE_URL + '/expenses/index.php';
                    });
                } else {
                    Swal.fire({ 
                        icon: 'error', 
                        title: 'Error', 
                        text: res.message, 
                        background: '#ffffff', 
                        color: '#0f172a' 
                    });
                }
            }
        });
    });
});
</script>
