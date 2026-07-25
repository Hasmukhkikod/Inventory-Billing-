<div class="content-body">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0"><?php echo $org ? 'Edit Organization' : 'New Organization'; ?></h2>
        <a href="<?php echo BASE_URL; ?>/organizations/index" class="btn btn-outline-secondary shadow-sm">
            <i class="fa-solid fa-arrow-left me-2"></i>Back
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form id="orgForm" onsubmit="saveOrg(event)">
                <input type="hidden" name="id" value="<?php echo $org['id'] ?? ''; ?>">
                <?php echo \App\Models\Helpers::csrfField(); ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Organization Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="<?php echo \App\Models\Helpers::sanitize($org['name'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="<?php echo \App\Models\Helpers::sanitize($org['email'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" name="phone" value="<?php echo \App\Models\Helpers::sanitize($org['phone'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Subscription Plan</label>
                        <select class="form-select" name="plan_id">
                            <option value="">-- No Plan --</option>
                            <?php foreach($plans as $p): ?>
                                <option value="<?php echo $p['id']; ?>" <?php echo (($org['plan_id'] ?? '') == $p['id']) ? 'selected' : ''; ?>>
                                    <?php echo \App\Models\Helpers::sanitize($p['plan_name']) . " (₹" . $p['price'] . ")"; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" name="start_date" value="<?php echo $org['start_date'] ?? ''; ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Valid Until (Expiry Date)</label>
                        <input type="date" class="form-control" name="valid_until" value="<?php echo $org['valid_until'] ?? ''; ?>">
                        <small class="text-muted">Leave blank for lifetime validity.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="ACTIVE" <?php echo (($org['status'] ?? '') === 'ACTIVE') ? 'selected' : ''; ?>>Active</option>
                            <option value="INACTIVE" <?php echo (($org['status'] ?? '') === 'INACTIVE') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary" id="saveBtn">
                        <i class="fa-solid fa-save me-2"></i>Save Organization
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
async function saveOrg(e) {
    e.preventDefault();
    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Saving...';

    try {
        const formData = new FormData(e.target);
        const res = await fetch(BASE_URL + '/api/organizations.php?action=save', {
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
                window.location.href = BASE_URL + '/organizations/index';
            });
        } else {
            Swal.fire('Error', data.message || 'Failed to save', 'error');
        }
    } catch (err) {
        Swal.fire('Error', 'An unexpected error occurred.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-save me-2"></i>Save Organization';
    }
}
</script>
