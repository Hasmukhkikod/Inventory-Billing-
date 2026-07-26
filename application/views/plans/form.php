<div class="content-body">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0"><?php echo $plan ? 'Edit Plan' : 'New Plan'; ?></h2>
        <a href="<?php echo BASE_URL; ?>/plans/index" class="btn btn-outline-secondary shadow-sm">
            <i class="fa-solid fa-arrow-left me-2"></i>Back
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form id="planForm" onsubmit="savePlan(event)">
                <input type="hidden" name="id" value="<?php echo $plan['id'] ?? ''; ?>">
                <?php echo \App\Models\Helpers::csrfField(); ?>
                
                <?php
                $planFeatures = [];
                if (!empty($plan['features'])) {
                    $planFeatures = json_decode($plan['features'], true) ?: [];
                }
                ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Plan Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="plan_name" value="<?php echo \App\Models\Helpers::sanitize($plan['plan_name'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Price <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" name="price" value="<?php echo $plan['price'] ?? '0.00'; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Duration (Days) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="duration_days" value="<?php echo $plan['duration_days'] ?? '30'; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="ACTIVE" <?php echo (($plan['status'] ?? '') === 'ACTIVE') ? 'selected' : ''; ?>>Active</option>
                            <option value="INACTIVE" <?php echo (($plan['status'] ?? '') === 'INACTIVE') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-12 mt-4 pt-3 border-top">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="fa-solid fa-list-check me-2 text-indigo"></i>Plan Features</h5>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary me-1" id="btn-select-all">Select All</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-deselect-all">Deselect All</button>
                        </div>
                    </div>
                    <div class="row g-3">
                        <?php
                        $availableFeatures = [
                            'Inventory' => [
                                'inventory' => 'Products List',
                                'categories' => 'Categories',
                                'brands' => 'Brands',
                                'units' => 'Units',
                                'conversions' => 'Unit Conversions',
                            ],
                            'Purchases & Suppliers' => [
                                'purchases' => 'Purchases',
                                'suppliers' => 'Suppliers',
                            ],
                            'Billing & Sales' => [
                                'billing' => 'Billing & Invoicing',
                                'returns' => 'Returns Log',
                                'quotations' => 'Quotations',
                                'challans' => 'Delivery Challans',
                            ],
                            'CRM & Customers' => [
                                'customers' => 'CRM (Customers)',
                            ],
                            'Finance & Reports' => [
                                'expenses' => 'Expenses',
                                'reports' => 'Reporting',
                            ],
                            'Users & Access' => [
                                'users' => 'Users',
                                'roles' => 'Roles & Permissions',
                            ],
                            'Settings & Add-ons' => [
                                'coupons' => 'Discount Coupons',
                                'theme' => 'Theme & Display',
                                'backups' => 'Data & Backups',
                                'printer' => 'Printer Settings',
                                'feedback' => 'Feedback System',
                            ]
                        ];
                        foreach ($availableFeatures as $groupName => $features):
                        ?>
                        <div class="col-12 mt-4">
                            <h6 class="text-secondary border-bottom pb-2 mb-3"><?php echo $groupName; ?></h6>
                            <div class="row g-3">
                                <?php foreach ($features as $key => $label): 
                                    $isChecked = in_array($key, $planFeatures) ? 'checked' : '';
                                ?>
                                <div class="col-md-3 col-sm-4 col-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="<?php echo $key; ?>" id="feat_<?php echo $key; ?>" <?php echo $isChecked; ?>>
                                        <label class="form-check-label" for="feat_<?php echo $key; ?>"><?php echo $label; ?></label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-primary" id="saveBtn">
                        <i class="fa-solid fa-save me-2"></i>Save Plan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btnSelectAll = document.getElementById('btn-select-all');
    const btnDeselectAll = document.getElementById('btn-deselect-all');
    
    if (btnSelectAll) {
        btnSelectAll.addEventListener('click', () => {
            document.querySelectorAll('input[name="features[]"]').forEach(cb => cb.checked = true);
        });
    }
    
    if (btnDeselectAll) {
        btnDeselectAll.addEventListener('click', () => {
            document.querySelectorAll('input[name="features[]"]').forEach(cb => cb.checked = false);
        });
    }
});

async function savePlan(e) {
    e.preventDefault();
    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Saving...';

    try {
        const formData = new FormData(e.target);
        const res = await fetch(BASE_URL + '/api/plans.php?action=save', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.status) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.href = BASE_URL + '/plans/index';
            });
        } else {
            Swal.fire('Error', data.message || 'Failed to save', 'error');
        }
    } catch (err) {
        Swal.fire('Error', 'An unexpected error occurred.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-save me-2"></i>Save Plan';
    }
}
</script>
