<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Customer Form Page (Add / Edit)
 */
$isEdit = !empty($customer);
?>
<div class="panel-card">
    <div class="panel-header">
        <h5 class="mb-0 text-indigo">
            <i class="fa-solid fa-user-plus me-2"></i><?php echo $isEdit ? 'Edit Customer Details' : 'Add New Customer'; ?>
        </h5>
        <a href="<?php echo BASE_URL; ?>/customers/index.php" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to CRM
        </a>
    </div>
    
    <div class="panel-body">
        <form id="customerForm">
            <?php echo \App\Models\Helpers::csrfField(); ?>
            <input type="hidden" name="id" id="cust-id" value="<?php echo $isEdit ? (int)$customer['id'] : '0'; ?>">
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name *</label>
                    <input type="text" class="form-control" name="customer_name" id="cust-name" required placeholder="e.g. Anand Sharma" value="<?php echo $isEdit ? \App\Models\Helpers::sanitize($customer['customer_name']) : ''; ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mobile Number *</label>
                    <input type="text" class="form-control" name="mobile" id="cust-mobile" required placeholder="10 digit number" value="<?php echo $isEdit ? \App\Models\Helpers::sanitize($customer['mobile']) : ''; ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" id="cust-email" placeholder="name@domain.com" value="<?php echo $isEdit ? \App\Models\Helpers::sanitize($customer['email']) : ''; ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">GSTIN Identification</label>
                    <input type="text" class="form-control" name="gst_number" id="cust-gst" placeholder="e.g. 27AAAAA0000A1Z5" value="<?php echo $isEdit ? \App\Models\Helpers::sanitize($customer['gst_number']) : ''; ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Opening Balance (₹)</label>
                    <input type="number" step="0.01" class="form-control" name="opening_balance" id="cust-opening-balance" placeholder="0.00" value="<?php echo $isEdit ? (float)$customer['opening_balance'] : '0.00'; ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Credit Limit (₹)</label>
                    <input type="number" step="0.01" class="form-control" name="credit_limit" id="cust-credit-limit" placeholder="0.00" value="<?php echo $isEdit ? (float)$customer['credit_limit'] : '0.00'; ?>">
                    <small class="text-muted">Use 0 for unlimited / no limit</small>
                </div>
                
                <div class="col-md-12">
                    <label class="form-label">Address</label>
                    <textarea class="form-control" name="address" id="cust-address" rows="3" placeholder="Full address..."><?php echo $isEdit ? \App\Models\Helpers::sanitize($customer['address']) : ''; ?></textarea>
                </div>
            </div>
            
            <div class="mt-4 pt-3 border-top border-secondary text-end">
                <a href="<?php echo BASE_URL; ?>/customers/index.php" class="btn btn-outline-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-circle-check me-1"></i><?php echo $isEdit ? 'Update Details' : 'Save Customer'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    $("#customerForm").submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: BASE_URL + '/api/customers.php?action=save',
            type: 'POST',
            data: $(this).serialize(),
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
                        window.location.href = BASE_URL + '/customers/index.php';
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
