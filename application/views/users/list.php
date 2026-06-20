<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Users & Security List View
 */
?>

<div class="row g-4 mb-4">
    <div class="col-md-9">
        <div class="panel-card">
            <div class="panel-header">
                <ul class="nav nav-tabs border-0" id="usersTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active text-indigo border-0 bg-transparent fw-semibold" id="users-tab" data-bs-toggle="tab" data-bs-target="#users-pane" type="button" role="tab" aria-controls="users-pane" aria-selected="true">
                            <i class="fa-solid fa-users me-2"></i>System Users
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-secondary border-0 bg-transparent fw-semibold" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity-pane" type="button" role="tab" aria-controls="activity-pane" aria-selected="false">
                            <i class="fa-solid fa-clock-rotate-left me-2"></i>Activity Logs
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-secondary border-0 bg-transparent fw-semibold" id="logins-tab" data-bs-toggle="tab" data-bs-target="#logins-pane" type="button" role="tab" aria-controls="logins-pane" aria-selected="false">
                            <i class="fa-solid fa-shield-halved me-2"></i>Login History
                        </button>
                    </li>
                </ul>
                <a href="<?php echo BASE_URL; ?>/users/form.php" class="btn btn-primary btn-sm btn-action-add" id="btn-add-user">
                    <i class="fa-solid fa-user-plus me-1"></i> Add User
                </a>
            </div>
            
            <div class="panel-body">
                <div class="tab-content" id="usersTabsContent">
                    
                    <!-- SYSTEM USERS LIST -->
                    <div class="tab-pane fade show active" id="users-pane" role="tabpanel" aria-labelledby="users-tab" tabindex="0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle w-100" id="usersTable">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Role</th>
                                        <th>Email</th>
                                        <th>Mobile</th>
                                        <th>Last Login</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- SYSTEM AUDIT ACTIVITY LOGS -->
                    <div class="tab-pane fade" id="activity-pane" role="tabpanel" aria-labelledby="activity-tab" tabindex="0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle w-100" id="activityLogsTable">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>User</th>
                                        <th>Module</th>
                                        <th>Action Description</th>
                                        <th>IP Address</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- LOGIN LOGS -->
                    <div class="tab-pane fade" id="logins-pane" role="tabpanel" aria-labelledby="logins-tab" tabindex="0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle w-100" id="loginLogsTable">
                                <thead>
                                    <tr>
                                        <th>Login Time</th>
                                        <th>Logout Time</th>
                                        <th>User</th>
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

    <!-- Right Sidebar - Predefined RBAC Matrix Summary -->
    <div class="col-md-3">
        <div class="panel-card">
            <div class="panel-header">
                <h6 class="mb-0 text-dark"><i class="fa-solid fa-shield-halved text-indigo me-2"></i>RBAC Matrix</h6>
            </div>
            <div class="panel-body p-3" id="rbac-matrix-box">
                <div class="text-center py-4 text-secondary small">Loading roles & permissions...</div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Hide add button when looking at logs tabs
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        const targetId = $(e.target).attr('id');
        if (targetId === 'users-tab') {
            $('#btn-add-user').show();
        } else {
            $('#btn-add-user').hide();
        }
    });

    // 1. DT Init - Users list
    const usersTable = $('#usersTable').DataTable({
        ajax: {
            url: BASE_URL + '/api/users.php?action=list',
            dataSrc: 'data'
        },
        columns: [
            { 
                data: 'name', 
                className: 'fw-semibold text-dark',
                render: function(data, type, row) {
                    return `<a href="${BASE_URL}/users/view.php?id=${row.id}" class="text-indigo text-decoration-none">${data}</a>`;
                }
            },
            { data: 'role_name', className: 'text-indigo fw-semibold' },
            { data: 'email' },
            { data: 'mobile' },
            { 
                data: 'last_login', 
                defaultContent: 'Never logged in',
                render: d => d ? new Date(d).toLocaleString('en-IN', {day:'2-digit', month:'short', hour:'2-digit', minute:'2-digit'}) : 'Never'
            },
            {
                data: 'status',
                render: d => d === 'ACTIVE' ? '<span class="badge bg-light-success">Active</span>' : '<span class="badge bg-light-danger">Disabled</span>'
            },
            {
                data: null,
                className: 'text-end',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group">
                            <a href="${BASE_URL}/users/view.php?id=${row.id}" class="btn btn-sm btn-outline-secondary py-1 px-2 text-indigo" title="View Profile">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="${BASE_URL}/users/form.php?id=${row.id}" class="btn btn-sm btn-outline-secondary py-1 px-2 text-emerald" title="Edit user">
                                <i class="fa-solid fa-pencil"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-secondary py-1 px-2 text-danger btn-delete" data-id="${row.id}" title="De-activate User">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ]
    });

    // 2. DT Init - Activity Logs
    const activityTable = $('#activityLogsTable').DataTable({
        ajax: {
            url: BASE_URL + '/api/users.php?action=activity_logs',
            dataSrc: 'data'
        },
        columns: [
            { 
                data: 'created_at',
                render: d => new Date(d).toLocaleString('en-IN', {day:'2-digit', month:'short', hour:'2-digit', minute:'2-digit'})
            },
            { data: 'user_name', defaultContent: 'System Admin' },
            { data: 'module', className: 'text-indigo font-monospace' },
            { data: 'action', className: 'text-dark' },
            { data: 'ip_address', defaultContent: '-' }
        ],
        order: [[0, 'desc']]
    });

    // 3. DT Init - Login logs
    const loginsTable = $('#loginLogsTable').DataTable({
        ajax: {
            url: BASE_URL + '/api/users.php?action=login_logs',
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
            { data: 'user_name' },
            { data: 'browser', defaultContent: '-' },
            { data: 'ip_address', defaultContent: '-' }
        ],
        order: [[0, 'desc']]
    });

    // Reload list triggers on tab focus
    $('#activity-tab').click(() => activityTable.ajax.reload());
    $('#logins-tab').click(() => loginsTable.ajax.reload());

    // Delete trigger
    $('#usersTable').on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'De-activate User Account?',
            text: "This disables their authentication logins immediately!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#dc2626',
            confirmButtonText: 'Yes, disable account',
            background: '#ffffff',
            color: '#0f172a'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE_URL + '/api/users.php?action=delete',
                    type: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status) {
                            usersTable.ajax.reload();
                            Swal.fire({ icon: 'success', title: 'Account Disabled', text: res.message, background: '#ffffff', color: '#0f172a' });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Failed', text: res.message, background: '#ffffff', color: '#0f172a' });
                        }
                    }
                });
            }
        });
    });

    // Load Predefined RBAC Matrix summary details
    function loadRbacMatrix() {
        $.ajax({
            url: BASE_URL + '/api/users.php?action=roles_list',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    const box = $("#rbac-matrix-box");
                    box.empty();
                    
                    res.data.forEach(role => {
                        let badges = '';
                        role.permissions.forEach(p => {
                            badges += `<span class="badge bg-light-primary m-1">${p}</span>`;
                        });
                        
                        box.append(`
                            <div class="mb-4 border-bottom pb-3">
                                <h6 class="text-dark fw-bold d-flex justify-content-between mb-2">
                                    <span>${role.role_name}</span>
                                    <small class="text-indigo small">${role.description || ''}</small>
                                </h6>
                                <div>${badges || '<span class="text-muted small">No permissions assigned</span>'}</div>
                            </div>
                        `);
                    });
                }
            }
        });
    }

    loadRbacMatrix();
});
</script>
