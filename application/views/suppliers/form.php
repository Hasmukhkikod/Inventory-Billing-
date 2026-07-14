<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Supplier Form Page (Add / Edit)
 */
$isEdit = !empty($supplier);
?>
<div class="panel-card">
    <div class="panel-header">
        <h5 class="mb-0 text-indigo">
            <i class="fa-solid fa-truck-field me-2"></i><?php echo $isEdit ? 'Edit Supplier Details' : 'Add New Supplier'; ?>
        </h5>
        <a href="<?php echo BASE_URL; ?>/suppliers/index" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to CRM
        </a>
    </div>
    
    <div class="panel-body">
        <form id="supplierForm">
            <?php echo \App\Models\Helpers::csrfField(); ?>
            <input type="hidden" name="id" id="supp-id" value="<?php echo $isEdit ? (int)$supplier['id'] : '0'; ?>">
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Supplier Business Name *</label>
                    <input type="text" class="form-control" name="supplier_name" id="supp-name" required placeholder="e.g. Acme Electronics Distributor" value="<?php echo $isEdit ? \App\Models\Helpers::sanitize($supplier['supplier_name']) : ''; ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact Person Name</label>
                    <input type="text" class="form-control" name="contact_person" id="supp-contact" placeholder="Representative name" value="<?php echo $isEdit ? \App\Models\Helpers::sanitize($supplier['contact_person']) : ''; ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Mobile Number *</label>
                    <input type="text" class="form-control" name="mobile" id="supp-mobile" required placeholder="10 digit number" value="<?php echo $isEdit ? \App\Models\Helpers::sanitize($supplier['mobile']) : ''; ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" id="supp-email" placeholder="sales@distributor.com" value="<?php echo $isEdit ? \App\Models\Helpers::sanitize($supplier['email']) : ''; ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">GSTIN Identification</label>
                    <input type="text" class="form-control" name="gst_number" id="supp-gst" placeholder="e.g. 27AAAAA0000A1Z5" value="<?php echo $isEdit ? \App\Models\Helpers::sanitize($supplier['gst_number']) : ''; ?>">
                </div>
                <?php if (!$isEdit): ?>
                <div class="col-md-6">
                    <label class="form-label">Opening Balance (₹)</label>
                    <input type="number" step="0.01" min="0" class="form-control" name="opening_balance" id="supp-opening" placeholder="0.00" value="0.00">
                </div>
                <?php endif; ?>
                
                <div class="col-md-12">
                    <label class="form-label">Business Address</label>
                    <textarea class="form-control" name="address" id="supp-address" rows="3" placeholder="Full address..."><?php echo $isEdit ? \App\Models\Helpers::sanitize($supplier['address']) : ''; ?></textarea>
                </div>
            </div>
            
            <div class="mt-4 pt-3 border-top border-secondary text-end">
                <a href="<?php echo BASE_URL; ?>/suppliers/index" class="btn btn-outline-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-circle-check me-1"></i><?php echo $isEdit ? 'Update Details' : 'Save Supplier'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    $("#supplierForm").submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: BASE_URL + '/api/suppliers.php?action=save',
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
                        window.location.href = BASE_URL + '/suppliers/index';
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
