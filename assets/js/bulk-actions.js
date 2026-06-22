/**
 * IIMS v2.0 - Bulk Actions Handler
 * Usage: Add data-bulk-table="tableId" and data-bulk-api="apiUrl" to the panel-card
 */
$(document).ready(function () {

    // Initialize bulk actions for each table that has the toolbar
    $('.bulk-actions-toolbar').each(function () {
        const toolbar = $(this);
        const tableId = toolbar.data('table');
        const apiUrl = toolbar.data('api');
        const csrfToken = $('input[name="csrf_token"]').val() || '';

        // Select All checkbox
        toolbar.find('.bulk-select-all').on('change', function () {
            const checked = $(this).is(':checked');
            $('#' + tableId + ' tbody .bulk-check').prop('checked', checked);
            updateBulkCount(toolbar, tableId);
        });

        // Individual checkbox
        $(document).on('change', '#' + tableId + ' .bulk-check', function () {
            updateBulkCount(toolbar, tableId);
            const total = $('#' + tableId + ' tbody .bulk-check').length;
            const selected = $('#' + tableId + ' tbody .bulk-check:checked').length;
            toolbar.find('.bulk-select-all').prop('checked', total === selected && total > 0);
        });

        // Apply bulk action
        toolbar.find('.btn-bulk-apply').on('click', function () {
            const action = toolbar.find('.bulk-action-select').val();
            if (!action) {
                Swal.fire({ icon: 'warning', title: 'Select Action', text: 'Please choose a bulk action first.', background: '#ffffff', color: '#1e293b' });
                return;
            }

            const ids = [];
            $('#' + tableId + ' tbody .bulk-check:checked').each(function () {
                ids.push($(this).val());
            });

            if (ids.length === 0) {
                Swal.fire({ icon: 'warning', title: 'No Selection', text: 'Please select at least one record.', background: '#ffffff', color: '#1e293b' });
                return;
            }

            const actionLabels = {
                'delete': 'Delete',
                'activate': 'Activate',
                'deactivate': 'Deactivate',
                'export_csv': 'Export CSV'
            };

            // Export CSV doesn't need confirmation
            if (action === 'export_csv') {
                exportSelectedCSV(tableId, ids);
                return;
            }

            const actionLabel = actionLabels[action] || action;
            const confirmColor = action === 'delete' ? '#dc2626' : '#2563eb';
            const confirmIcon = action === 'delete' ? 'warning' : 'question';

            Swal.fire({
                icon: confirmIcon,
                title: 'Confirm Bulk ' + actionLabel + '?',
                html: 'This will <strong>' + actionLabel.toLowerCase() + '</strong> <span class="text-indigo fw-bold">' + ids.length + ' selected record(s)</span>.<br><br>This action cannot be undone.',
                showCancelButton: true,
                confirmButtonText: 'Yes, ' + actionLabel,
                cancelButtonText: 'Cancel',
                confirmButtonColor: confirmColor,
                background: '#ffffff',
                color: '#1e293b'
            }).then(function (result) {
                if (result.isConfirmed) {
                    executeBulkAction(apiUrl, action, ids, csrfToken, tableId, toolbar);
                }
            });
        });
    });

    function updateBulkCount(toolbar, tableId) {
        const count = $('#' + tableId + ' tbody .bulk-check:checked').length;
        const badge = toolbar.find('.bulk-count');
        if (count > 0) {
            badge.text(count + ' selected').removeClass('d-none');
            toolbar.find('.btn-bulk-apply').prop('disabled', false);
        } else {
            badge.addClass('d-none');
            toolbar.find('.btn-bulk-apply').prop('disabled', true);
        }
    }

    function executeBulkAction(apiUrl, action, ids, csrfToken, tableId, toolbar) {
        $.ajax({
            url: apiUrl + '?action=bulk',
            type: 'POST',
            data: {
                csrf_token: csrfToken,
                bulk_action: action,
                ids: JSON.stringify(ids)
            },
            dataType: 'json',
            success: function (res) {
                if (res.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Done!',
                        text: res.message || ids.length + ' records updated.',
                        timer: 1500,
                        showConfirmButton: false,
                        background: '#ffffff',
                        color: '#1e293b'
                    });
                    // Reload DataTable
                    if ($.fn.DataTable.isDataTable('#' + tableId)) {
                        $('#' + tableId).DataTable().ajax.reload(null, false);
                    }
                    toolbar.find('.bulk-select-all').prop('checked', false);
                    updateBulkCount(toolbar, tableId);
                } else {
                    Swal.fire({ icon: 'error', title: 'Failed', text: res.message, background: '#ffffff', color: '#1e293b' });
                }
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Server error. Please try again.', background: '#ffffff', color: '#1e293b' });
            }
        });
    }

    function exportSelectedCSV(tableId, ids) {
        const table = $('#' + tableId);
        const headers = [];
        table.find('thead th').each(function (i) {
            const text = $(this).text().trim();
            if (text && i > 0 && text !== 'Actions') headers.push(text); // skip checkbox col and actions
        });

        const rows = [headers.join(',')];
        table.find('tbody tr').each(function () {
            const rowId = $(this).find('.bulk-check').val();
            if (ids.includes(rowId)) {
                const cells = [];
                $(this).find('td').each(function (i) {
                    if (i > 0 && i < $(this).closest('tr').find('td').length - 1) { // skip checkbox and actions
                        let text = $(this).text().trim().replace(/"/g, '""').replace(/\n/g, ' ');
                        cells.push('"' + text + '"');
                    }
                });
                rows.push(cells.join(','));
            }
        });

        const csv = rows.join('\n');
        const blob = new Blob([csv], { type: 'text/csv' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = tableId + '_export_' + new Date().toISOString().slice(0, 10) + '.csv';
        link.click();

        Swal.fire({ icon: 'success', title: 'Exported!', text: ids.length + ' records exported to CSV.', timer: 1500, showConfirmButton: false, background: '#ffffff', color: '#1e293b' });
    }

    // Re-apply checkboxes after DataTable redraws
    $(document).on('draw.dt', function () {
        $('.bulk-actions-toolbar').each(function () {
            const toolbar = $(this);
            const tableId = toolbar.data('table');
            toolbar.find('.bulk-select-all').prop('checked', false);
            updateBulkCount(toolbar, tableId);
        });
    });
});
