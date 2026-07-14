<div class="row g-4 mb-4">
    <div class="col-md-12">
        <div class="panel-card">
            <div class="panel-header">
                <ul class="nav nav-tabs border-0" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active text-indigo border-0 bg-transparent fw-semibold">
                            <i class="fa-solid fa-user-shield me-2"></i>Roles & Permissions
                        </button>
                    </li>
                </ul>
                <div class="d-flex gap-2">
                    <a href="<?php echo BASE_URL; ?>/users/index" class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back to Users
                    </a>
                    <a href="<?php echo BASE_URL; ?>/roles/form" class="btn btn-primary btn-sm btn-action-add">
                        <i class="fa-solid fa-plus me-1"></i> Create Role
                    </a>
                </div>
            </div>
            
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle w-100" id="rolesTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Role Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($roles)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-shield-halved mb-2 fs-4"></i><br>
                                        No roles found
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($roles as $role): ?>
                                    <tr>
                                        <td class="fw-medium">#<?php echo $role['id']; ?></td>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo \App\Models\Helpers::sanitize($role['role_name']); ?></div>
                                            <?php if ($role['id'] == 1): ?>
                                                <span class="badge bg-danger-subtle text-danger rounded-pill mt-1" style="font-size: 0.7rem;">Super Admin</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-secondary"><?php echo \App\Models\Helpers::sanitize($role['description'] ?: '-'); ?></td>
                                        <td>
                                            <?php if ($role['status'] === 'ACTIVE'): ?>
                                                <span class="badge bg-light-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-light-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <?php if ($role['id'] != 1): ?>
                                                <a href="<?php echo BASE_URL; ?>/roles/form?id=<?php echo $role['id']; ?>" class="btn btn-sm btn-light text-primary border-0 me-1" title="Edit Role">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-light text-danger border-0 btn-delete" data-id="<?php echo $role['id']; ?>" title="Delete Role">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted small fst-italic">System Core</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            Swal.fire({
                title: 'Delete Role?',
                text: "Are you sure you want to delete this role?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#dc2626',
                confirmButtonText: 'Yes, delete',
                background: '#ffffff',
                color: '#0f172a'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('id', id);
                    
                    fetch('<?php echo BASE_URL; ?>/api/roles.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status) {
                            window.location.reload();
                        } else {
                            Swal.fire({ icon: 'error', title: 'Failed', text: data.message, background: '#ffffff', color: '#0f172a' });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred. Please try again.', background: '#ffffff', color: '#0f172a' });
                    });
                }
            });
        });
    });
});
</script>
