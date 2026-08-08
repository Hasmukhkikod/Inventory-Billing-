<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Distributors / Partners Directory (Super Admin)
 */
?>

<div class="panel-card">
    <div class="panel-header">
        <h5 class="mb-0 text-dark"><i class="fa-solid fa-user-tie me-2 text-indigo"></i>Distributors / Partners</h5>
        <a href="<?php echo BASE_URL; ?>/leads/index" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-bullseye me-1"></i> View Leads
        </a>
    </div>

    <div class="panel-body text-dark">
        <div class="table-responsive entity-desktop-table">
            <table class="table table-hover w-100" id="distributorsTable">
                <thead>
                    <tr>
                        <th>Partner</th>
                        <th>Contact</th>
                        <th>Commission</th>
                        <th>Clients</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <div class="entity-mobile-cards" id="distributorsMobileCards"></div>
    </div>
</div>

<!-- Edit Distributor Modal -->
<div class="modal fade" id="editDistributorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Partner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="edit-distributor-form">
                    <input type="hidden" name="id" id="edit-dist-id">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Commission Rate (%)</label>
                        <input type="number" step="0.01" min="0" max="100" class="form-control" name="commission_rate" id="edit-dist-rate" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Status</label>
                        <select class="form-select" name="status" id="edit-dist-status" required>
                            <option value="ACTIVE">Active</option>
                            <option value="INACTIVE">Inactive</option>
                            <option value="PENDING">Pending (not yet verified)</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btn-save-distributor">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Assign Client Modal -->
<div class="modal fade" id="assignClientModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Client to <span id="assign-dist-name"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="assign-dist-id">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Organization (unassigned only)</label>
                    <select class="form-select" id="assign-org-select" style="width:100%;"></select>
                </div>
                <hr>
                <h6 class="small fw-semibold text-secondary text-uppercase">Currently Assigned</h6>
                <ul class="list-group" id="assigned-clients-list"></ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btn-assign-client">Assign Client</button>
            </div>
        </div>
    </div>
</div>

<?php echo \App\Models\Helpers::csrfField(); ?>

<script>
$(document).ready(function() {
    const csrfToken = $('input[name="csrf_token"]').val();
    const editModal = new bootstrap.Modal(document.getElementById('editDistributorModal'));
    const assignModal = new bootstrap.Modal(document.getElementById('assignClientModal'));

    const distributorsTable = $('#distributorsTable').DataTable({
        ajax: {
            url: BASE_URL + '/api/distributors.php?action=list',
            dataSrc: 'data'
        },
        columns: [
            {
                data: 'name',
                render: function(data, type, row) {
                    return `<strong class="text-dark">${data}</strong>` + (row.business_name ? `<br><span class="text-muted small">${row.business_name}</span>` : '');
                }
            },
            {
                data: 'email',
                render: function(data, type, row) {
                    return `<span class="d-block small">${data}</span><span class="d-block small text-muted">${row.mobile}</span>`;
                }
            },
            { data: 'commission_rate', render: d => parseFloat(d).toFixed(2) + '%' },
            { data: 'client_count' },
            {
                data: 'status',
                render: function(data, type, row) {
                    if (!row.is_verified) return '<span class="badge bg-light-warning text-warning">Unverified</span>';
                    const map = { ACTIVE: 'bg-light-success text-success', INACTIVE: 'bg-light-danger text-rose', PENDING: 'bg-light-secondary text-secondary' };
                    return `<span class="badge ${map[data] || 'bg-light-secondary'}">${data}</span>`;
                }
            },
            {
                data: null,
                className: 'text-end',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-secondary py-1 px-2 btn-edit-dist" data-id="${row.id}" data-rate="${row.commission_rate}" data-status="${row.status}" title="Edit">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary py-1 px-2 text-indigo btn-assign-client" data-id="${row.id}" data-name="${row.name}" title="Assign Client">
                                <i class="fa-solid fa-user-plus"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger py-1 px-2 btn-delete-dist" data-id="${row.id}" title="Remove">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[0, 'asc']],
        drawCallback: function() {
            renderEntityMobileCards({
                tableId: 'distributorsTable',
                containerId: 'distributorsMobileCards',
                titleCol: 0,
                metaCols: [{ col: 1, icon: 'fa-solid fa-envelope' }],
                fieldCols: [
                    { col: 2, label: 'Commission' },
                    { col: 3, label: 'Clients' },
                    { col: 4, label: 'Status' }
                ],
                actionsCol: 5,
                initials: function($tds) { return entityInitials($tds.eq(0).text()); },
                emptyText: 'No partners have registered yet.'
            });
        }
    });

    // Edit distributor
    $('#distributorsTable, #distributorsMobileCards').on('click', '.btn-edit-dist', function() {
        $('#edit-dist-id').val($(this).data('id'));
        $('#edit-dist-rate').val($(this).data('rate'));
        $('#edit-dist-status').val($(this).data('status'));
        editModal.show();
    });

    $('#btn-save-distributor').on('click', function() {
        $.post(BASE_URL + '/api/distributors.php?action=update', {
            id: $('#edit-dist-id').val(),
            commission_rate: $('#edit-dist-rate').val(),
            status: $('#edit-dist-status').val(),
            csrf_token: csrfToken
        }, function(res) {
            if (res.status) {
                editModal.hide();
                distributorsTable.ajax.reload(null, false);
                Swal.fire({ icon: 'success', title: 'Saved', text: res.message, background: '#ffffff', color: '#0f172a' });
            } else {
                Swal.fire({ icon: 'error', title: 'Failed', text: res.message, background: '#ffffff', color: '#0f172a' });
            }
        }, 'json');
    });

    // Delete distributor
    $('#distributorsTable, #distributorsMobileCards').on('click', '.btn-delete-dist', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Remove this partner?',
            text: 'They will no longer be able to log in to the Partner Portal.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'Yes, remove',
            background: '#ffffff', color: '#0f172a'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(BASE_URL + '/api/distributors.php?action=delete', { id: id, csrf_token: csrfToken }, function(res) {
                    if (res.status) {
                        distributorsTable.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: 'Removed', text: res.message, background: '#ffffff', color: '#0f172a' });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Failed', text: res.message, background: '#ffffff', color: '#0f172a' });
                    }
                }, 'json');
            }
        });
    });

    // Assign client modal
    let orgSelect2Initialized = false;
    function loadUnassignedOrgs() {
        $.get(BASE_URL + '/api/distributors.php?action=unassigned_orgs', function(res) {
            const $select = $('#assign-org-select');
            $select.empty().append('<option value=""></option>');
            if (res.status) {
                res.data.forEach(o => {
                    $select.append(`<option value="${o.id}">${o.name} (${o.email || 'no email'}) - ${o.plan_name || 'No plan'}</option>`);
                });
            }
            if (!orgSelect2Initialized) {
                $select.select2({ theme: 'bootstrap-5', width: '100%', placeholder: 'Search organizations…', allowClear: true, dropdownParent: $('#assignClientModal') });
                orgSelect2Initialized = true;
            } else {
                $select.trigger('change');
            }
        }, 'json');
    }

    function loadAssignedClients(distributorId) {
        $.get(BASE_URL + '/api/distributors.php?action=clients_for&distributor_id=' + distributorId, function(res) {
            const $list = $('#assigned-clients-list');
            $list.empty();
            if (res.status && res.data.length) {
                res.data.forEach(c => {
                    $list.append(`
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>${c.org_name} <span class="text-muted small">(${c.plan_name || 'No plan'})</span></span>
                            <button class="btn btn-sm btn-outline-danger btn-unassign" data-link-id="${c.link_id}"><i class="fa-solid fa-xmark"></i></button>
                        </li>
                    `);
                });
            } else {
                $list.append('<li class="list-group-item text-muted small">No clients assigned yet.</li>');
            }
        }, 'json');
    }

    $('#distributorsTable, #distributorsMobileCards').on('click', '.btn-assign-client', function() {
        const id = $(this).data('id');
        $('#assign-dist-id').val(id);
        $('#assign-dist-name').text($(this).data('name'));
        loadUnassignedOrgs();
        loadAssignedClients(id);
        assignModal.show();
    });

    $('#btn-assign-client').on('click', function() {
        const distributorId = $('#assign-dist-id').val();
        const organizationId = $('#assign-org-select').val();
        if (!organizationId) {
            Swal.fire({ icon: 'warning', title: 'Pick an organization', background: '#ffffff', color: '#0f172a' });
            return;
        }
        $.post(BASE_URL + '/api/distributors.php?action=assign_client', {
            distributor_id: distributorId, organization_id: organizationId, csrf_token: csrfToken
        }, function(res) {
            if (res.status) {
                loadUnassignedOrgs();
                loadAssignedClients(distributorId);
                distributorsTable.ajax.reload(null, false);
                Swal.fire({ icon: 'success', title: 'Assigned', text: res.message, background: '#ffffff', color: '#0f172a' });
            } else {
                Swal.fire({ icon: 'error', title: 'Failed', text: res.message, background: '#ffffff', color: '#0f172a' });
            }
        }, 'json');
    });

    $('#assigned-clients-list').on('click', '.btn-unassign', function() {
        const linkId = $(this).data('link-id');
        const distributorId = $('#assign-dist-id').val();
        $.post(BASE_URL + '/api/distributors.php?action=unassign_client', { link_id: linkId, csrf_token: csrfToken }, function(res) {
            if (res.status) {
                loadUnassignedOrgs();
                loadAssignedClients(distributorId);
                distributorsTable.ajax.reload(null, false);
            }
        }, 'json');
    });
});
</script>
