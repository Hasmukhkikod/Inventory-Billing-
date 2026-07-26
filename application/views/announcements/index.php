<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Announcements View Page
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1">Global Announcements & Ads</h2>
        <p class="text-muted mb-0">Broadcast messages, banners, and modals to all organizations on the platform.</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#announcementModal" onclick="resetForm()">
        <i class="fa-solid fa-bullhorn me-2"></i>New Broadcast
    </button>
</div>

<div class="panel-card">
    <div class="panel-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="announcementsTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Title</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th>Schedule</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($announcements)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-comment-slash fs-1 mb-3"></i>
                                <p>No announcements found.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($announcements as $row): ?>
                            <tr>
                                <td class="ps-4" style="cursor: pointer;" onclick="showLivePreview(<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?>)">
                                    <div class="fw-bold text-primary text-decoration-underline" style="text-underline-offset: 3px;" title="Click to view Live Preview"><?php echo htmlspecialchars($row['title']); ?></div>
                                    <div class="small text-muted text-truncate mt-1" style="max-width: 250px;"><?php echo strip_tags($row['message']); ?></div>
                                </td>
                                <td>
                                    <?php if($row['display_type'] == 'banner'): ?>
                                        <span class="badge bg-info text-dark"><i class="fa-solid fa-panorama me-1"></i> Top Banner</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary"><i class="fa-solid fa-window-restore me-1"></i> Modal Popup</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                        $loc = $row['location'];
                                        if($loc == 'dashboard') echo 'Dashboard Only';
                                        elseif($loc == 'billing') echo 'Billing/POS Only';
                                        else echo 'All Pages';
                                    ?>
                                </td>
                                <td>
                                    <div class="small">
                                        <div><strong class="text-success">Start:</strong> <?php echo $row['start_time'] ? date('d M Y h:i A', strtotime($row['start_time'])) : 'Immediately'; ?></div>
                                        <div><strong class="text-danger">End:</strong> <?php echo $row['end_time'] ? date('d M Y h:i A', strtotime($row['end_time'])) : 'Never'; ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input status-toggle" type="checkbox" role="switch" data-id="<?php echo $row['id']; ?>" <?php echo $row['status'] === 'ACTIVE' ? 'checked' : ''; ?>>
                                        <label class="form-check-label ms-1" style="font-size: 0.85rem;"><?php echo $row['status']; ?></label>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-primary" onclick="editAnnouncement(<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?>)" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger ms-1" onclick="deleteAnnouncement(<?php echo $row['id']; ?>)" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for Create/Edit -->
<div class="modal fade" id="announcementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Create Broadcast</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="announcementForm">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="id" id="announcementId" value="">
                    <?php echo \App\Models\Helpers::csrfField(); ?>
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Broadcast Title (Internal / Header)</label>
                            <input type="text" class="form-control" name="title" id="title" required placeholder="e.g. Server Maintenance Notice">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Display Type</label>
                            <select class="form-select" name="display_type" id="display_type" onchange="toggleFormFields()">
                                <option value="banner">Top Banner</option>
                                <option value="modal">Modal Popup</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4" id="modalSizeContainer" style="display: none;">
                            <label class="form-label">Modal Size</label>
                            <select class="form-select" name="modal_size" id="modal_size" onchange="renderPreview()">
                                <option value="md">Standard</option>
                                <option value="lg">Large</option>
                                <option value="fullscreen">Full Screen</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Location to Show</label>
                            <select class="form-select" name="location" id="location">
                                <option value="all_pages">All Pages</option>
                                <option value="dashboard">Dashboard Only</option>
                                <option value="billing">Billing/POS Only</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Message / Advertisement Content (HTML allowed)</label>
                            <textarea class="form-control" name="message" id="message" rows="4" required placeholder="Enter your message here..."></textarea>
                            <div class="form-text">You can use basic HTML like &lt;b&gt;, &lt;a href="..."&gt;, etc.</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Display Frequency</label>
                            <select class="form-select" name="show_frequency" id="show_frequency" onchange="toggleFormFields()">
                                <option value="always">Always (Every Page Load)</option>
                                <option value="once_per_login">Once Per Login Session</option>
                                <option value="every_x_minutes">Every X Minutes</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4" id="frequencyMinutesContainer" style="display: none;">
                            <label class="form-label">Cooldown (Minutes)</label>
                            <input type="number" class="form-control" name="frequency_minutes" id="frequency_minutes" value="5" min="1">
                        </div>

                        <div class="col-md-4" id="durationContainer" style="display: none;">
                            <label class="form-label">Auto-hide Duration (seconds)</label>
                            <input type="number" class="form-control" name="duration_seconds" id="duration_seconds" value="0" min="0">
                            <div class="form-text">0 = User must close it manually</div>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Start Time (Optional)</label>
                            <input type="datetime-local" class="form-control" name="start_time" id="start_time">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">End Time (Optional)</label>
                            <input type="datetime-local" class="form-control" name="end_time" id="end_time">
                        </div>
                    </div>
                </form>

                <hr class="my-4">
                
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-muted">Live Preview</h6>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="renderPreview()">
                        <i class="fa-solid fa-eye me-1"></i> Update Preview
                    </button>
                </div>
                
                <!-- Preview Container -->
                <div class="mt-3 p-3 border rounded bg-light" id="previewContainer" style="min-height: 100px; position: relative; overflow: hidden;">
                    <div class="text-center text-muted mt-4">Click "Update Preview" to see how it looks.</div>
                </div>

            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveAnnouncement()">Save Broadcast</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function toggleFormFields() {
        if ($('#display_type').val() === 'modal') {
            $('#durationContainer').show();
            $('#modalSizeContainer').show();
        } else {
            $('#durationContainer').hide();
            $('#modalSizeContainer').hide();
        }
        
        if ($('#show_frequency').val() === 'every_x_minutes') {
            $('#frequencyMinutesContainer').show();
        } else {
            $('#frequencyMinutesContainer').hide();
        }
        renderPreview();
    }

    function resetForm() {
        $('#formAction').val('create');
        $('#announcementId').val('');
        $('#modalTitle').text('Create Broadcast');
        $('#announcementForm')[0].reset();
        $('#previewContainer').html('<div class="text-center text-muted mt-4">Click "Update Preview" to see how it looks.</div>');
        toggleFormFields();
    }

    function editAnnouncement(data) {
        $('#formAction').val('edit');
        $('#announcementId').val(data.id);
        $('#modalTitle').text('Edit Broadcast');
        
        $('#title').val(data.title);
        $('#display_type').val(data.display_type);
        $('#modal_size').val(data.modal_size || 'md');
        $('#location').val(data.location);
        $('#message').val(data.message);
        $('#duration_seconds').val(data.duration_seconds);
        $('#show_frequency').val(data.show_frequency || 'always');
        $('#frequency_minutes').val(data.frequency_minutes || 0);
        
        if (data.start_time) {
            $('#start_time').val(data.start_time.substring(0,16)); // Format for datetime-local
        } else {
            $('#start_time').val('');
        }
        
        if (data.end_time) {
            $('#end_time').val(data.end_time.substring(0,16));
        } else {
            $('#end_time').val('');
        }
        
        toggleFormFields();
        renderPreview();
        
        var modal = new bootstrap.Modal(document.getElementById('announcementModal'));
        modal.show();
    }

    function showLivePreview(data) {
        // Remove any existing live preview elements
        $('#livePreviewModal, #livePreviewBanner').remove();
        $('.modal-backdrop').remove(); // Clean up if any orphans
        $('body').removeClass('modal-open');
        $('body').css('padding-right', '');

        if (data.display_type === 'banner') {
            const html = `
            <div id="livePreviewBanner" class="alert alert-info border-0 rounded-0 m-0 shadow-sm d-flex justify-content-between align-items-center alert-dismissible fade show" style="background: linear-gradient(90deg, #4f46e5 0%, #3730a3 100%); color: white; position: fixed; top: 0; left: 0; width: 100%; z-index: 10000;">
                <div class="d-flex align-items-center"><i class="fa-solid fa-bullhorn fs-4 me-3"></i><div><strong>${data.title}</strong><br><span style="font-size: 0.9rem;">${data.message}</span></div></div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
            $('body').append(html);
        } else {
            let sizeClass = '';
            const mSize = data.modal_size || 'md';
            if (mSize === 'lg') sizeClass = 'modal-lg';
            if (mSize === 'fullscreen') sizeClass = 'modal-fullscreen';
            
            const html = `
            <div class="modal fade" id="livePreviewModal" tabindex="-1" style="z-index: 10000;">
                <div class="modal-dialog modal-dialog-centered ${sizeClass}">
                    <div class="modal-content shadow">
                        <div class="modal-header border-bottom-0 pb-0">
                            <h5 class="modal-title">${data.title}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body py-4 text-center">
                            ${data.message}
                        </div>
                        <div class="modal-footer border-top-0 pt-0 justify-content-center">
                            <button type="button" class="btn btn-primary px-4 rounded-pill" data-bs-dismiss="modal">Got it!</button>
                        </div>
                    </div>
                </div>
            </div>`;
            
            $('body').append(html);
            var m = new bootstrap.Modal(document.getElementById('livePreviewModal'));
            m.show();
            
            // Clean up DOM after hidden
            document.getElementById('livePreviewModal').addEventListener('hidden.bs.modal', function () {
                $('#livePreviewModal').remove();
            });
            
            if (data.duration_seconds > 0) {
                setTimeout(() => m.hide(), data.duration_seconds * 1000);
            }
        }
    }

    function renderPreview() {
        const type = $('#display_type').val();
        const title = $('#title').val() || 'Notice';
        const msg = $('#message').val() || 'Your message goes here...';
        const container = $('#previewContainer');
        
        if (type === 'banner') {
            container.html(`
                <div class="alert alert-info border-0 rounded-0 m-0 shadow-sm d-flex justify-content-between align-items-center" style="background: linear-gradient(90deg, #4f46e5 0%, #3730a3 100%); color: white;">
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-bullhorn fs-4 me-3"></i>
                        <div>
                            <strong>${title}</strong><br>
                            <span style="font-size: 0.9rem;">${msg}</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" disabled></button>
                </div>
            `);
        } else {
            let sizeClass = '';
            const mSize = $('#modal_size').val();
            if (mSize === 'lg') sizeClass = 'modal-lg';
            if (mSize === 'fullscreen') sizeClass = 'modal-fullscreen';
            
            container.html(`
                <div class="modal d-block" style="position: relative; z-index: 1;">
                    <div class="modal-dialog modal-dialog-centered ${sizeClass}">
                        <div class="modal-content shadow">
                            <div class="modal-header border-bottom-0 pb-0">
                                <h5 class="modal-title">${title}</h5>
                                <button type="button" class="btn-close" disabled></button>
                            </div>
                            <div class="modal-body py-4 text-center">
                                ${msg}
                            </div>
                            <div class="modal-footer border-top-0 pt-0 justify-content-center">
                                <button type="button" class="btn btn-primary px-4 rounded-pill" disabled>Got it!</button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        }
    }

    function saveAnnouncement() {
        if(!$('#title').val() || !$('#message').val()) {
            Swal.fire('Error', 'Title and Message are required.', 'error');
            return;
        }
        
        $.ajax({
            url: BASE_URL + '/api/announcements.php',
            type: 'POST',
            data: $('#announcementForm').serialize(),
            success: function(res) {
                if (res.status) {
                    Swal.fire('Success', res.message, 'success').then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to save announcement.', 'error');
            }
        });
    }

    function deleteAnnouncement(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This broadcast will be permanently deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(BASE_URL + '/api/announcements.php', {
                    action: 'delete',
                    id: id,
                    csrf_token: $('meta[name="csrf-token"]').attr('content')
                }, function(res) {
                    if (res.status) {
                        window.location.reload();
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                });
            }
        });
    }

    $('.status-toggle').change(function() {
        const id = $(this).data('id');
        const currentStatus = $(this).prop('checked') ? 'INACTIVE' : 'ACTIVE'; // Send current, server toggles
        
        $.post(BASE_URL + '/api/announcements.php', {
            action: 'toggle_status',
            id: id,
            status: currentStatus,
            csrf_token: $('meta[name="csrf-token"]').attr('content')
        }, function(res) {
            if (res.status) {
                window.location.reload();
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        });
    });
</script>
