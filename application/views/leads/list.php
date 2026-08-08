<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Partner Leads Directory (Super Admin)
 */
?>

<div class="panel-card">
    <div class="panel-header">
        <h5 class="mb-0 text-dark"><i class="fa-solid fa-bullseye me-2 text-indigo"></i>Partner Leads</h5>
        <a href="<?php echo BASE_URL; ?>/distributors/index" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-user-tie me-1"></i> View Partners
        </a>
    </div>

    <div class="panel-body text-dark">
        <div class="table-responsive entity-desktop-table">
            <table class="table table-hover w-100" id="leadsTable">
                <thead>
                    <tr>
                        <th>Lead</th>
                        <th>Contact</th>
                        <th>Submitted By</th>
                        <th>Notes</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <div class="entity-mobile-cards" id="leadsMobileCards"></div>
    </div>
</div>

<?php echo \App\Models\Helpers::csrfField(); ?>

<script>
$(document).ready(function() {
    const csrfToken = $('input[name="csrf_token"]').val();

    const leadsTable = $('#leadsTable').DataTable({
        ajax: {
            url: BASE_URL + '/api/leads.php?action=list',
            dataSrc: 'data'
        },
        columns: [
            {
                data: 'lead_name',
                render: function(data, type, row) {
                    return `<strong class="text-dark">${data}</strong>` + (row.business_name ? `<br><span class="text-muted small">${row.business_name}</span>` : '');
                }
            },
            {
                data: 'mobile',
                render: function(data, type, row) {
                    return `<span class="d-block small">${data || '-'}</span><span class="d-block small text-muted">${row.email || ''}</span>`;
                }
            },
            {
                data: 'distributor_name',
                render: function(data, type, row) {
                    return `<span class="d-block small">${data}</span><span class="d-block small text-muted">${row.business_name || row.distributor_email}</span>`;
                }
            },
            { data: 'notes', render: d => d ? `<span class="small text-muted">${d}</span>` : '<span class="text-muted small">-</span>' },
            {
                data: 'created_at',
                render: function(data) {
                    const d = new Date(data);
                    return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
                }
            },
            {
                data: 'status',
                render: function(data, type, row) {
                    const map = {
                        NEW: 'bg-light-primary text-indigo',
                        CONTACTED: 'bg-light-warning text-warning',
                        CONVERTED: 'bg-light-success text-success',
                        REJECTED: 'bg-light-danger text-rose'
                    };
                    return `<span class="badge ${map[data] || 'bg-light-secondary'}">${data}</span>`;
                }
            },
            {
                data: null,
                className: 'text-end',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary py-1 px-2" type="button" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item btn-lead-status" href="#" data-id="${row.id}" data-status="CONTACTED"><i class="fa-solid fa-phone me-2 text-warning"></i>Mark Contacted</a></li>
                                <li><a class="dropdown-item btn-lead-status" href="#" data-id="${row.id}" data-status="CONVERTED"><i class="fa-solid fa-check me-2 text-success"></i>Mark Converted</a></li>
                                <li><a class="dropdown-item btn-lead-status" href="#" data-id="${row.id}" data-status="REJECTED"><i class="fa-solid fa-xmark me-2 text-danger"></i>Mark Rejected</a></li>
                            </ul>
                        </div>
                    `;
                }
            }
        ],
        order: [[4, 'desc']],
        drawCallback: function() {
            renderEntityMobileCards({
                tableId: 'leadsTable',
                containerId: 'leadsMobileCards',
                titleCol: 0,
                metaCols: [
                    { col: 1, icon: 'fa-solid fa-phone' },
                    { col: 2, icon: 'fa-solid fa-user-tie' }
                ],
                fieldCols: [
                    { col: 4, label: 'Submitted' },
                    { col: 5, label: 'Status' },
                    { col: 3, label: 'Notes', wide: true }
                ],
                actionsCol: 6,
                initials: function($tds) { return entityInitials($tds.eq(0).text()); },
                emptyText: 'No leads submitted by partners yet.'
            });
        }
    });

    $('#leadsTable, #leadsMobileCards').on('click', '.btn-lead-status', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const status = $(this).data('status');
        $.post(BASE_URL + '/api/leads.php?action=update_status', { id: id, status: status, csrf_token: csrfToken }, function(res) {
            if (res.status) {
                leadsTable.ajax.reload(null, false);
                Swal.fire({ icon: 'success', title: 'Updated', text: res.message, background: '#ffffff', color: '#0f172a' });
            } else {
                Swal.fire({ icon: 'error', title: 'Failed', text: res.message, background: '#ffffff', color: '#0f172a' });
            }
        }, 'json');
    });
});
</script>
