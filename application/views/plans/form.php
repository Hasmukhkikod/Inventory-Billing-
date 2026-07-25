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
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary" id="saveBtn">
                        <i class="fa-solid fa-save me-2"></i>Save Plan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
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
