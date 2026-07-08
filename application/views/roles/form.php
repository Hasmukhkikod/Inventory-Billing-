<div class="row g-4 mb-4">
    <div class="col-md-12">
        <div class="panel-card">
            <div class="panel-header">
                <h5 class="mb-0 text-indigo">
                    <i class="fa-solid fa-user-shield me-2"></i><?php echo $id > 0 ? 'Edit Role' : 'Create New Role'; ?>
                </h5>
                <a href="<?php echo BASE_URL; ?>/roles/index.php" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Roles
                </a>
            </div>
            
            <div class="panel-body">
                <form id="roleForm" onsubmit="saveRole(event)">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <input type="hidden" name="action" value="save">

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="role_name" class="form-label fw-semibold">Role Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="role_name" name="role_name" 
                                   value="<?php echo $id > 0 ? \App\Models\Helpers::sanitize($role['role_name']) : ''; ?>" 
                                   required <?php echo $id == 1 ? 'readonly' : ''; ?>>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="status" class="form-label fw-semibold">Status</label>
                            <select class="form-select" id="status" name="status" <?php echo $id == 1 ? 'disabled' : ''; ?>>
                                <option value="ACTIVE" <?php echo ($id > 0 && $role['status'] == 'ACTIVE') ? 'selected' : ''; ?>>Active</option>
                                <option value="INACTIVE" <?php echo ($id > 0 && $role['status'] == 'INACTIVE') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        
                        <div class="col-md-12">
                            <label for="description" class="form-label fw-semibold">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2" <?php echo $id == 1 ? 'readonly' : ''; ?>><?php echo $id > 0 ? \App\Models\Helpers::sanitize($role['description']) : ''; ?></textarea>
                        </div>
                    </div>

                    <hr class="text-muted opacity-25">
                    
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 row-gap-2">
                        <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-key me-2 text-indigo"></i>Role Permissions</h5>
                        <?php if ($id != 1): ?>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAllPermissions()">Select / Deselect All</button>
                        <?php endif; ?>
                    </div>
                    <p class="text-secondary small mb-4">Select the modules and actions this role is allowed to access. <br><i>Note: Super Admin always has full system access regardless of checkboxes.</i></p>

                    <div class="row g-4">
                        <?php foreach ($permissions as $module => $perms): ?>
                            <div class="col-md-4">
                                <div class="card h-100 border border-light-subtle shadow-none bg-light-subtle rounded-3">
                                    <div class="card-header bg-white fw-bold text-indigo border-bottom py-3">
                                        <i class="fa-solid fa-layer-group me-2 opacity-50"></i><?php echo htmlspecialchars($module); ?>
                                    </div>
                                    <div class="card-body">
                                        <?php foreach ($perms as $p): ?>
                                            <div class="form-check custom-checkbox mb-2">
                                                <input class="form-check-input perm-checkbox" type="checkbox" 
                                                       name="permissions[]" 
                                                       value="<?php echo $p['id']; ?>" 
                                                       id="perm_<?php echo $p['id']; ?>"
                                                       <?php echo in_array($p['id'], $rolePermissions) || $id == 1 ? 'checked' : ''; ?>
                                                       <?php echo $id == 1 ? 'disabled' : ''; ?>>
                                                <label class="form-check-label" for="perm_<?php echo $p['id']; ?>">
                                                    <?php echo htmlspecialchars($p['permission_name']); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-4 text-end border-top pt-3">
                        <a href="<?php echo BASE_URL; ?>/roles/index.php" class="btn btn-light border me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4" <?php echo $id == 1 ? 'disabled' : ''; ?>>
                            <i class="fa-solid fa-floppy-disk me-2"></i>Save Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.custom-checkbox .form-check-input {
    width: 1.2rem;
    height: 1.2rem;
    margin-top: 0.15rem;
    cursor: pointer;
}
.custom-checkbox .form-check-input:checked {
    background-color: #6366f1;
    border-color: #6366f1;
}
.custom-checkbox .form-check-label {
    margin-left: 0.3rem;
    cursor: pointer;
    color: #4b5563;
}
</style>

<script>
function toggleAllPermissions() {
    const checkboxes = document.querySelectorAll('.perm-checkbox');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    checkboxes.forEach(cb => {
        if (!cb.disabled) {
            cb.checked = !allChecked;
        }
    });
}

function saveRole(e) {
    e.preventDefault();
    const form = document.getElementById('roleForm');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Prevent double submission
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...';
    
    const formData = new FormData(form);
    
    fetch('<?php echo BASE_URL; ?>/api/roles.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status) {
            window.location.href = '<?php echo BASE_URL; ?>/roles/index.php';
        } else {
            Swal.fire({ icon: 'error', title: 'Failed', text: data.message, background: '#ffffff', color: '#0f172a' });
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-floppy-disk me-2"></i>Save Role';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred. Please try again.', background: '#ffffff', color: '#0f172a' });
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa-solid fa-floppy-disk me-2"></i>Save Role';
    });
}
</script>
