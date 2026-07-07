<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * User Form Page (Add / Edit)
 */
$isEdit = !empty($user);
?>
<div class="panel-card">
    <div class="panel-header">
        <h5 class="mb-0 text-indigo">
            <i class="fa-solid fa-user-shield me-2"></i><?php echo $isEdit ? 'Edit User Credentials' : 'Create User Profile'; ?>
        </h5>
        <a href="<?php echo BASE_URL; ?>/users/index.php" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Directory
        </a>
    </div>
    
    <div class="panel-body">
        <form id="userForm">
            <?php echo \App\Models\Helpers::csrfField(); ?>
            <input type="hidden" name="id" id="usr-id" value="<?php echo $isEdit ? (int)$user['id'] : '0'; ?>">
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name *</label>
                    <input type="text" class="form-control" name="name" id="usr-name" required placeholder="e.g. Rahul Patil" value="<?php echo $isEdit ? \App\Models\Helpers::sanitize($user['name']) : ''; ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Address *</label>
                    <input type="email" class="form-control" name="email" id="usr-email" required placeholder="name@company.com" value="<?php echo $isEdit ? \App\Models\Helpers::sanitize($user['email']) : ''; ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Mobile Number *</label>
                    <input type="text" class="form-control" name="mobile" id="usr-mobile" required placeholder="10 digit number" value="<?php echo $isEdit ? \App\Models\Helpers::sanitize($user['mobile']) : ''; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">System Access Role *</label>
                    <select class="form-select" name="role_id" id="usr-role" required>
                        <?php foreach($roles as $r): ?>
                            <option value="<?php echo $r['id']; ?>" <?php echo ($isEdit && $user['role_id'] == $r['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($r['role_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Account Status</label>
                    <select class="form-select" name="status" id="usr-status">
                        <option value="ACTIVE" <?php echo ($isEdit && $user['status'] === 'ACTIVE') ? 'selected' : ''; ?>>ACTIVE</option>
                        <option value="INACTIVE" <?php echo ($isEdit && $user['status'] === 'INACTIVE') ? 'selected' : ''; ?>>INACTIVE</option>
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Password <?php echo $isEdit ? '' : '*'; ?></label>
                    <input type="password" class="form-control" name="password" id="usr-password" <?php echo $isEdit ? '' : 'required'; ?> placeholder="<?php echo $isEdit ? 'Leave blank to keep current password' : 'Enter login password'; ?>">
                    <?php if ($isEdit): ?>
                        <div class="small text-muted mt-1">Leave blank to keep existing password.</div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="mt-4 pt-3 border-top border-secondary text-end">
                <a href="<?php echo BASE_URL; ?>/users/index.php" class="btn btn-outline-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-circle-check me-1"></i><?php echo $isEdit ? 'Update User' : 'Save User'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    $("#userForm").submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: BASE_URL + '/api/users.php?action=save',
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
                        window.location.href = BASE_URL + '/users/index.php';
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
