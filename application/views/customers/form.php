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
                    <input type="number" step="0.01" min="0" class="form-control" name="opening_balance" id="cust-opening-balance" placeholder="0.00" value="<?php echo $isEdit ? (float)$customer['opening_balance'] : '0.00'; ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Credit Limit (₹)</label>
                    <input type="number" step="0.01" min="0" class="form-control" name="credit_limit" id="cust-credit-limit" placeholder="0.00" value="<?php echo $isEdit ? (float)$customer['credit_limit'] : '0.00'; ?>">
                    <small class="text-muted">Use 0 for unlimited / no limit</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label">State</label>
                    <select class="form-select" name="state" id="cust-state">
                        <option value="">-- Select State --</option>
                        <option value="Jammu & Kashmir" <?php echo ($isEdit && ($customer['state'] ?? '') === 'Jammu & Kashmir') ? 'selected' : ''; ?>>Jammu & Kashmir</option>
                        <option value="Himachal Pradesh" <?php echo ($isEdit && ($customer['state'] ?? '') === 'Himachal Pradesh') ? 'selected' : ''; ?>>Himachal Pradesh</option>
                        <option value="Punjab" <?php echo ($isEdit && ($customer['state'] ?? '') === 'Punjab') ? 'selected' : ''; ?>>Punjab</option>
                        <option value="Chandigarh" <?php echo ($isEdit && ($customer['state'] ?? '') === 'Chandigarh') ? 'selected' : ''; ?>>Chandigarh</option>
                        <option value="Uttarakhand" <?php echo ($isEdit && ($customer['state'] ?? '') === 'Uttarakhand') ? 'selected' : ''; ?>>Uttarakhand</option>
                        <option value="Haryana" <?php echo ($isEdit && ($customer['state'] ?? '') === 'Haryana') ? 'selected' : ''; ?>>Haryana</option>
                        <option value="Delhi" <?php echo ($isEdit && ($customer['state'] ?? '') === 'Delhi') ? 'selected' : ''; ?>>Delhi</option>
                        <option value="Rajasthan" <?php echo ($isEdit && ($customer['state'] ?? '') === 'Rajasthan') ? 'selected' : ''; ?>>Rajasthan</option>
                        <option value="Uttar Pradesh" <?php echo ($isEdit && ($customer['state'] ?? '') === 'Uttar Pradesh') ? 'selected' : ''; ?>>Uttar Pradesh</option>
                        <option value="Bihar" <?php echo ($isEdit && ($customer['state'] ?? '') === 'Bihar') ? 'selected' : ''; ?>>Bihar</option>
                        <option value="Sikkim" <?php echo ($isEdit && ($customer['state'] ?? '') === 'Sikkim') ? 'selected' : ''; ?>>Sikkim</option>
                        <option value="Arunachal Pradesh" <?php echo ($isEdit && ($customer['state'] ?? '') === 'Arunachal Pradesh') ? 'selected' : ''; ?>>Arunachal Pradesh</option>
                        <option value="Nagaland" <?php echo ($isEdit && ($customer['state'] ?? '') === 'Nagaland') ? 'selected' : ''; ?>>Nagaland</option>
                        <option value="Manipur" <?php echo ($isEdit && ($customer['state'] ?? '') === 'Manipur') ? 'selected' : ''; ?>>Manipur</option>
                        <option value="Mizoram" <?php echo ($isEdit && ($customer['state'] ?? '') === 'Mizoram') ? 'selected' : ''; ?>>Mizoram</option>
                        <option value="Tripura" <?php echo ($isEdit && ($customer['state'] ?? '') === 'Tripura') ? 'selected' : ''; ?>>Tripura</option>
                        <option value="Meghalaya" <?php echo ($isEdit && ($customer['state'] ?? '') === 'Meghalaya') ? 'selected' : ''; ?>>Meghalaya</option>
                        <option value="Assam" <?php echo ($isEdit && ($customer['state'] ?? '') === 'Assam') ? 'selected' : ''; ?>>Assam</option>
                        <option value="West Bengal" <?php echo ($isEdit && ($customer['state'] ?? '') === 'West Bengal') ? 'selected' : ''; ?>>West Bengal</option>
                        <option value="Jharkhand" <?php echo ($isEdit && ($customer['state'] ?? '') === 'Jharkhand') ? 'selected' : ''; ?>>Jharkhand</option>
                        <option value="Odisha" <?php echo ($isEdit && ($customer['state'] ?? '') === 'Odisha') ? 'selected' : ''; ?>>Odisha</option>
                        <option value="Chhattisgarh" <?php echo ($isEdit && ($customer['state'] ?? '') === 'Chhattisgarh') ? 'selected' : ''; ?>>Chhattisgarh</option>
                        <option value="Madhya Pradesh" <?php echo ($isEdit && ($customer['state'] ?? '') === 'Madhya Pradesh') ? 'selected' : ''; ?>>Madhya Pradesh</option>
                        <option value="Gujarat" <?php echo ($isEdit && ($customer['state'] ?? '') === 'Gujarat') ? 'selected' : ''; ?>>Gujarat</option>
                        <option value="Maharashtra" <?php echo ($isEdit && ($customer['state'] ?? '') === 'Maharashtra') ? 'selected' : ''; ?>>Maharashtra</option>
                        <option value="Karnataka" <?php echo ($isEdit && ($customer['state'] ?? '') === 'Karnataka') ? 'selected' : ''; ?>>Karnataka</option>
                        <option value="Goa" <?php echo ($isEdit && ($customer['state'] ?? '') === 'Goa') ? 'selected' : ''; ?>>Goa</option>
                        <option value="Kerala" <?php echo ($isEdit && ($customer['state'] ?? '') === 'Kerala') ? 'selected' : ''; ?>>Kerala</option>
                        <option value="Tamil Nadu" <?php echo ($isEdit && ($customer['state'] ?? '') === 'Tamil Nadu') ? 'selected' : ''; ?>>Tamil Nadu</option>
                        <option value="Telangana" <?php echo ($isEdit && ($customer['state'] ?? '') === 'Telangana') ? 'selected' : ''; ?>>Telangana</option>
                        <option value="Andhra Pradesh" <?php echo ($isEdit && ($customer['state'] ?? '') === 'Andhra Pradesh') ? 'selected' : ''; ?>>Andhra Pradesh</option>
                    </select>
                    <small class="text-muted">For CGST/SGST vs IGST calculation</small>
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
