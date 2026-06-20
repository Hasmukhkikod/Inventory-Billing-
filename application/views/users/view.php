<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * User Profile & Session Activity View
 */
?>
<div class="row g-4">
    <!-- User Profile Details Card -->
    <div class="col-md-4">
        <div class="panel-card h-100">
            <div class="panel-header">
                <h6 class="mb-0 text-indigo"><i class="fa-solid fa-circle-info me-2"></i>User Profile</h6>
                <a href="<?php echo BASE_URL; ?>/users/form.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-secondary text-emerald py-1">
                    <i class="fa-solid fa-pencil me-1"></i> Edit
                </a>
            </div>
            
            <div class="panel-body">
                <div class="text-center mb-4">
                    <div class="bg-tertiary rounded-circle text-indigo d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2.2rem; background-color: #f1f5f9;">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                    <h5 class="text-dark fw-bold mb-1"><?php echo \App\Models\Helpers::sanitize($user['name']); ?></h5>
                    <span class="badge bg-light-primary"><?php echo \App\Models\Helpers::sanitize($user['role_name']); ?></span>
                </div>
                
                <div class="border-top border-secondary pt-3">
                    <div class="row g-2 small mb-2">
                        <div class="col-5 text-secondary">Email:</div>
                        <div class="col-7 text-dark fw-semibold text-break"><?php echo \App\Models\Helpers::sanitize($user['email']); ?></div>
                    </div>
                    <div class="row g-2 small mb-2">
                        <div class="col-5 text-secondary">Mobile:</div>
                        <div class="col-7 text-dark fw-semibold"><?php echo \App\Models\Helpers::sanitize($user['mobile']); ?></div>
                    </div>
                    <div class="row g-2 small mb-2">
                        <div class="col-5 text-secondary">Status:</div>
                        <div class="col-7">
                            <?php if ($user['status'] === 'ACTIVE'): ?>
                                <span class="badge bg-light-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-light-danger">Disabled</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row g-2 small mb-2">
                        <div class="col-5 text-secondary">Last Login:</div>
                        <div class="col-7 text-dark fw-semibold">
                            <?php echo $user['last_login'] ? date('d-M-Y H:i', strtotime($user['last_login'])) : 'Never'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Logs & History -->
    <div class="col-md-8">
        <div class="panel-card h-100">
            <div class="panel-header">
                <ul class="nav nav-tabs border-0" id="userLogsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active text-indigo border-0 bg-transparent fw-semibold" id="user-activity-tab" data-bs-toggle="tab" data-bs-target="#user-activity-pane" type="button" role="tab" aria-controls="user-activity-pane" aria-selected="true">
                            <i class="fa-solid fa-clock-rotate-left me-2"></i>Activity Logs
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-secondary border-0 bg-transparent fw-semibold" id="user-logins-tab" data-bs-toggle="tab" data-bs-target="#user-logins-pane" type="button" role="tab" aria-controls="user-logins-pane" aria-selected="false">
                            <i class="fa-solid fa-shield-halved me-2"></i>Login History
                        </button>
                    </li>
                </ul>
            </div>
            
            <div class="panel-body">
                <div class="tab-content text-dark" id="userLogsTabsContent">
                    
                    <!-- USER AUDIT ACTIVITY LOGS -->
                    <div class="tab-pane fade show active" id="user-activity-pane" role="tabpanel" aria-labelledby="user-activity-tab" tabindex="0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle w-100" id="userActivityLogsTable">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Module</th>
                                        <th>Action Description</th>
                                        <th>IP Address</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- USER LOGIN LOGS -->
                    <div class="tab-pane fade" id="user-logins-pane" role="tabpanel" aria-labelledby="user-logins-tab" tabindex="0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle w-100" id="userLoginLogsTable">
                                <thead>
                                    <tr>
                                        <th>Login Time</th>
                                        <th>Logout Time</th>
                                        <th>Browser / Device</th>
                                        <th>IP Address</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const userId = <?php echo (int)$user['id']; ?>;

    // 1. DT Init - Activity Logs
    const activityTable = $('#userActivityLogsTable').DataTable({
        ajax: {
            url: BASE_URL + '/api/users.php?action=activity_logs&user_id=' + userId,
            dataSrc: 'data'
        },
        columns: [
            { 
                data: 'created_at',
                render: d => new Date(d).toLocaleString('en-IN', {day:'2-digit', month:'short', hour:'2-digit', minute:'2-digit'})
            },
            { data: 'module', className: 'text-indigo font-monospace' },
            { data: 'action', className: 'text-dark' },
            { data: 'ip_address', defaultContent: '-' }
        ],
        order: [[0, 'desc']]
    });

    // 2. DT Init - Login logs
    const loginsTable = $('#userLoginLogsTable').DataTable({
        ajax: {
            url: BASE_URL + '/api/users.php?action=login_logs&user_id=' + userId,
            dataSrc: 'data'
        },
        columns: [
            { 
                data: 'login_time',
                render: d => new Date(d).toLocaleString('en-IN', {day:'2-digit', month:'short', hour:'2-digit', minute:'2-digit'})
            },
            { 
                data: 'logout_time',
                defaultContent: 'Active Session',
                render: d => d ? new Date(d).toLocaleString('en-IN', {day:'2-digit', month:'short', hour:'2-digit', minute:'2-digit'}) : '<span class="badge bg-light-success">Active Session</span>'
            },
            { data: 'browser', defaultContent: '-' },
            { data: 'ip_address', defaultContent: '-' }
        ],
        order: [[0, 'desc']]
    });

    // Reload triggers on tab focus
    $('#user-activity-tab').click(() => activityTable.ajax.reload());
    $('#user-logins-tab').click(() => loginsTable.ajax.reload());
});
</script>
