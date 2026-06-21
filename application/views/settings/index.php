<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Application Settings & DB backups View
 */
?>

<div class="row g-4">
    <!-- Left forms panel -->
    <div class="col-md-8">
        <div class="panel-card">
            <div class="panel-header">
                <ul class="nav nav-tabs border-0" id="settingsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active border-0 bg-transparent fw-semibold settings-tab" id="company-tab" data-bs-toggle="tab" data-bs-target="#company-pane" type="button" role="tab" aria-controls="company-pane" aria-selected="true">
                            <i class="fa-solid fa-building me-2"></i>Company Details
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link border-0 bg-transparent fw-semibold settings-tab" id="billing-tab" data-bs-toggle="tab" data-bs-target="#billing-pane" type="button" role="tab" aria-controls="billing-pane" aria-selected="false">
                            <i class="fa-solid fa-receipt me-2"></i>Invoice & Tax
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link border-0 bg-transparent fw-semibold settings-tab" id="loyalty-tab" data-bs-toggle="tab" data-bs-target="#loyalty-pane" type="button" role="tab" aria-controls="loyalty-pane" aria-selected="false">
                            <i class="fa-solid fa-gift me-2"></i>Loyalty & Templates
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link border-0 bg-transparent fw-semibold settings-tab" id="bank-tab" data-bs-toggle="tab" data-bs-target="#bank-pane" type="button" role="tab" aria-controls="bank-pane" aria-selected="false">
                            <i class="fa-solid fa-university me-2"></i>Bank Details
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link border-0 bg-transparent fw-semibold settings-tab" id="coupons-tab" data-bs-toggle="tab" data-bs-target="#coupons-pane" type="button" role="tab" aria-controls="coupons-pane" aria-selected="false">
                            <i class="fa-solid fa-tags me-2"></i>Coupons
                        </button>
                    </li>
                </ul>
            </div>
            
            <div class="panel-body">
                <form id="settingsForm">
                    <?php echo \App\Models\Helpers::csrfField(); ?>
                    
                    <div class="tab-content text-dark" id="settingsTabsContent">
                        
                        <!-- COMPANY DETAILS PANE -->
                        <div class="tab-pane fade show active" id="company-pane" role="tabpanel" aria-labelledby="company-tab" tabindex="0">
                            <div class="row g-3">
                                <!-- Logo Upload -->
                                <div class="col-md-12">
                                    <label class="form-label">Company Logo</label>
                                    <div class="d-flex align-items-center gap-3">
                                        <div id="logo-preview" style="width:64px; height:64px; border-radius:10px; border:2px dashed #cbd5e1; display:flex; align-items:center; justify-content:center; overflow:hidden; background:#f8fafc;">
                                            <i class="fa-solid fa-image text-muted fs-4" id="logo-placeholder"></i>
                                            <img id="logo-img" src="" alt="Logo" style="width:100%; height:100%; object-fit:contain; display:none;">
                                        </div>
                                        <div>
                                            <input type="file" class="form-control form-control-sm" id="set-logo-file" accept="image/png,image/jpeg,image/svg+xml,image/webp" style="max-width:280px;">
                                            <small class="text-muted">PNG, JPG, SVG, or WebP. Max 2MB. Used in sidebar & invoices.</small>
                                            <div class="mt-1">
                                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 d-none" id="btn-remove-logo"><i class="fa-solid fa-trash-can me-1"></i>Remove Logo</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Business Name *</label>
                                    <input type="text" class="form-control" name="company_name" id="set-name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">GSTIN Identification Number</label>
                                    <input type="text" class="form-control" name="company_gst" id="set-gst">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Primary Email Address</label>
                                    <input type="email" class="form-control" name="company_email" id="set-email">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Contact Phone Number</label>
                                    <input type="text" class="form-control" name="company_phone" id="set-phone">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Billing Office Address</label>
                                    <textarea class="form-control" name="company_address" id="set-address" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- BILLING & TAX DETAILS PANE -->
                        <div class="tab-pane fade" id="billing-pane" role="tabpanel" aria-labelledby="billing-tab" tabindex="0">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Invoice Number Prefix</label>
                                    <input type="text" class="form-control" name="invoice_prefix" id="set-prefix" placeholder="e.g. INV-">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">GST Tax Slabs (%)</label>
                                    <input type="hidden" name="gst_slabs" id="set-slabs" value="">
                                    <div class="gst-slabs-container border rounded p-2 bg-white" style="min-height: 44px;">
                                        <div class="d-flex flex-wrap gap-2 mb-2" id="gst-tags-box"></div>
                                        <div class="d-flex gap-2">
                                            <div class="input-group input-group-sm" style="max-width: 200px;">
                                                <input type="number" step="0.01" min="0" max="100" class="form-control" id="gst-new-value" placeholder="e.g. 5">
                                                <span class="input-group-text">%</span>
                                                <button class="btn btn-primary" type="button" id="btn-add-gst-slab"><i class="fa-solid fa-plus"></i></button>
                                            </div>
                                            <select class="form-select form-select-sm" id="gst-preset-dropdown" style="max-width: 180px;">
                                                <option value="">Quick Add...</option>
                                                <option value="0">0% (Exempt)</option>
                                                <option value="0.25">0.25%</option>
                                                <option value="3">3%</option>
                                                <option value="5">5%</option>
                                                <option value="12">12%</option>
                                                <option value="18">18%</option>
                                                <option value="28">28%</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">State Code (for CGST/SGST)</label>
                                    <input type="text" class="form-control" name="state_code" id="set-state-code" maxlength="5" placeholder="e.g. 27">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Invoice Footer Remarks</label>
                                    <input type="text" class="form-control" name="invoice_footer" id="set-footer" placeholder="Thank you message">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Terms and Conditions</label>
                                    <textarea class="form-control" name="invoice_terms" id="set-terms" rows="4" placeholder="Business policies..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- LOYALTY & TEMPLATES PANE -->
                        <div class="tab-pane fade" id="loyalty-pane" role="tabpanel" aria-labelledby="loyalty-tab" tabindex="0">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <h6 class="text-indigo mb-0"><i class="fa-solid fa-gift me-2"></i>Loyalty Settings</h6>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Enable Loyalty Points</label>
                                    <div class="form-check form-switch mt-1">
                                        <input class="form-check-input" type="checkbox" name="loyalty_enabled" id="set-loyalty-enabled" value="1">
                                        <label class="form-check-label" for="set-loyalty-enabled">Active</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Points per &#8377;100 spent</label>
                                    <input type="number" class="form-control" name="loyalty_points_per_100" id="set-loyalty-points" min="0" placeholder="e.g. 10">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">&#8377; value per point</label>
                                    <input type="number" step="0.01" class="form-control" name="loyalty_redeem_value" id="set-loyalty-redeem" min="0" placeholder="e.g. 0.50">
                                </div>

                                <div class="col-md-12 mt-4">
                                    <h6 class="text-indigo mb-0"><i class="fa-solid fa-file-invoice me-2"></i>Invoice Template</h6>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Invoice Template</label>
                                    <select class="form-select" name="invoice_template" id="set-invoice-template">
                                        <option value="standard">Standard</option>
                                        <option value="thermal">Thermal</option>
                                        <option value="professional">Professional</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Thermal Width</label>
                                    <select class="form-select" name="thermal_width" id="set-thermal-width">
                                        <option value="80mm">80mm</option>
                                        <option value="58mm">58mm</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- BANK DETAILS PANE -->
                        <div class="tab-pane fade" id="bank-pane" role="tabpanel" aria-labelledby="bank-tab" tabindex="0">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <h6 class="text-indigo mb-0"><i class="fa-solid fa-university me-2"></i>Bank Account Details</h6>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Bank Name</label>
                                    <input type="text" class="form-control" name="bank_name" id="set-bank-name" placeholder="e.g. State Bank of India">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Account Number</label>
                                    <input type="text" class="form-control" name="bank_account_no" id="set-bank-account" placeholder="Account number">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">IFSC Code</label>
                                    <input type="text" class="form-control" name="bank_ifsc" id="set-bank-ifsc" placeholder="e.g. SBIN0001234">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Branch Name</label>
                                    <input type="text" class="form-control" name="bank_branch" id="set-bank-branch" placeholder="Branch name">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">UPI ID</label>
                                    <input type="text" class="form-control" name="upi_id" id="set-upi-id" placeholder="e.g. business@upi">
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="mt-4 pt-3 border-top border-secondary text-end" id="settings-save-row">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-circle-check me-2"></i>Commit Changes</button>
                    </div>
                </form>

                <!-- COUPONS PANE (outside the settings form since it has its own CRUD) -->
                <div class="tab-content">
                    <div class="tab-pane fade" id="coupons-pane" role="tabpanel" aria-labelledby="coupons-tab" tabindex="0">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="text-indigo mb-0"><i class="fa-solid fa-tags me-2"></i>Discount Coupons & Promo Codes</h6>
                            <button class="btn btn-primary btn-sm" id="btn-add-coupon"><i class="fa-solid fa-plus me-1"></i>Create Coupon</button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="couponsTable">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Value</th>
                                        <th>Min Order</th>
                                        <th>Valid Until</th>
                                        <th>Used</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="coupons-tbody">
                                    <tr><td colspan="9" class="text-center py-4 text-secondary">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Coupon Create/Edit Modal -->
    <div class="modal fade" id="couponModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="couponModalTitle"><i class="fa-solid fa-tags text-indigo me-2"></i>Create Coupon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="couponForm">
                    <?php echo \App\Models\Helpers::csrfField(); ?>
                    <input type="hidden" name="id" id="coupon-id" value="0">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label">Coupon Code *</label>
                                <input type="text" class="form-control text-uppercase" name="coupon_code" id="coupon-code" required placeholder="e.g. SAVE20">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Coupon Name *</label>
                                <input type="text" class="form-control" name="coupon_name" id="coupon-name" required placeholder="e.g. Diwali Sale 20%">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Discount Type *</label>
                                <select class="form-select" name="discount_type" id="coupon-type">
                                    <option value="PERCENTAGE">Percentage (%)</option>
                                    <option value="FLAT">Flat Amount (₹)</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Discount Value *</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="discount_value" id="coupon-value" required placeholder="e.g. 10">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Min Order Amount (₹)</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="min_order_amount" id="coupon-min" value="0" placeholder="0 = no minimum">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Max Discount (₹)</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="max_discount" id="coupon-max" placeholder="For % type only">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Valid From</label>
                                <input type="date" class="form-control" name="valid_from" id="coupon-from">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Valid Until</label>
                                <input type="date" class="form-control" name="valid_until" id="coupon-until">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Usage Limit</label>
                                <input type="number" min="0" class="form-control" name="usage_limit" id="coupon-limit" value="0" placeholder="0 = unlimited">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-circle-check me-1"></i>Save Coupon</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right backups panel -->
    <div class="col-md-4">
        <div class="panel-card">
            <div class="panel-header">
                <h6 class="mb-0 text-dark"><i class="fa-solid fa-database me-2 text-indigo"></i>Database Backups</h6>
                <?php if ($auth->hasPermission('Run Backups')): ?>
                <button class="btn btn-sm btn-outline-secondary py-1 px-2 text-indigo" id="btn-run-backup" title="Create Backup">
                    <i class="fa-solid fa-plus"></i> Backup
                </button>
                <?php endif; ?>
            </div>
            
            <div class="panel-body p-0 text-dark">
                <div class="table-responsive" style="max-height: 420px;">
                    <table class="table table-hover align-middle mb-0" id="backupsTable">
                        <thead>
                            <tr>
                                <th>File Details</th>
                                <th>Size</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // 1. Fetch current settings details
    $.ajax({
        url: BASE_URL + '/api/settings.php?action=list',
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            if (res.status) {
                const s = res.data;
                $("#set-name").val(s.company_name || '');
                $("#set-gst").val(s.gst_number || '');
                $("#set-email").val(s.email || '');
                $("#set-phone").val(s.phone || '');
                $("#set-address").val(s.address || '');
                $("#set-prefix").val(s.invoice_prefix || '');
                loadGstSlabs(s.gst_slabs || '0,5,12,18,28');
                $("#set-state-code").val(s.state_code || '');
                $("#set-footer").val(s.invoice_footer || '');
                $("#set-terms").val(s.invoice_terms || '');
                // Loyalty & Templates
                $("#set-loyalty-enabled").prop('checked', s.loyalty_enabled == 1);
                $("#set-loyalty-points").val(s.loyalty_points_per_100 || '');
                $("#set-loyalty-redeem").val(s.loyalty_redeem_value || '');
                $("#set-invoice-template").val(s.invoice_template || 'standard');
                $("#set-thermal-width").val(s.thermal_width || '80mm');
                // Bank Details
                $("#set-bank-name").val(s.bank_name || '');
                $("#set-bank-account").val(s.bank_account_no || '');
                $("#set-bank-ifsc").val(s.bank_ifsc || '');
                $("#set-bank-branch").val(s.bank_branch || '');
                $("#set-upi-id").val(s.upi_id || '');
                // Company Logo
                if (s.company_logo) {
                    $('#logo-img').attr('src', BASE_URL + '/uploads/' + s.company_logo).show();
                    $('#logo-placeholder').hide();
                    $('#btn-remove-logo').removeClass('d-none');
                }
            }
        }
    });

    // Logo file preview
    $('#set-logo-file').on('change', function() {
        const file = this.files[0];
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) {
            Swal.fire({ icon: 'warning', title: 'File Too Large', text: 'Logo must be under 2MB', background: '#ffffff', color: '#0f172a' });
            $(this).val('');
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#logo-img').attr('src', e.target.result).show();
            $('#logo-placeholder').hide();
            $('#btn-remove-logo').removeClass('d-none');
        };
        reader.readAsDataURL(file);
    });

    // Remove logo
    $('#btn-remove-logo').click(function() {
        $('#logo-img').attr('src', '').hide();
        $('#logo-placeholder').show();
        $('#set-logo-file').val('');
        $(this).addClass('d-none');
        // Mark for removal on save
        if (!$('#remove-logo-flag').length) {
            $('#settingsForm').append('<input type="hidden" name="remove_logo" id="remove-logo-flag" value="1">');
        }
    });

    // Save Settings (with FormData for file upload)
    $("#settingsForm").submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        // Ensure loyalty_enabled is sent even when unchecked
        if (!$("#set-loyalty-enabled").is(':checked')) {
            formData.set('loyalty_enabled', '0');
        }
        // Add logo file if selected
        var logoFile = $('#set-logo-file')[0].files[0];
        if (logoFile) {
            formData.append('company_logo_file', logoFile);
        }
        $.ajax({
            url: BASE_URL + '/api/settings.php?action=save',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    Swal.fire({ icon: 'success', title: 'Settings Saved', text: res.message, background: '#ffffff', color: '#0f172a' }).then(function() {
                        location.reload();
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Update Failed', text: res.message, background: '#ffffff', color: '#0f172a' });
                }
            }
        });
    });

    // 2. Load Backups
    loadBackupsList();

    $("#btn-run-backup").click(function() {
        Swal.fire({
            title: 'Trigger Backup?',
            text: "Creating a snapshot database file... this might take a moment.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Create Backup',
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#dc2626',
            showLoaderOnConfirm: true,
            background: '#ffffff',
            color: '#0f172a',
            preConfirm: () => {
                return $.ajax({
                    url: BASE_URL + '/api/settings.php?action=backup',
                    type: 'POST',
                    dataType: 'json'
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.value && result.value.status) {
                loadBackupsList();
                Swal.fire({ icon: 'success', title: 'Backup Successful', text: result.value.message, background: '#ffffff', color: '#0f172a' });
            } else if (result.value) {
                Swal.fire({ icon: 'error', title: 'Backup Failed', text: result.value.message, background: '#ffffff', color: '#0f172a' });
            }
        });
    });

    function loadBackupsList() {
        $.ajax({
            url: BASE_URL + '/api/settings.php?action=backup_list',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                const body = $("#backupsTable tbody");
                body.empty();
                
                if (res.status && res.data.length > 0) {
                    res.data.forEach(function(b) {
                        const date = new Date(b.created_at).toLocaleString('en-IN', {day:'2-digit', month:'short', hour:'2-digit', minute:'2-digit'});
                        
                        let actionsCell = `<span class="text-rose small fw-semibold">Failed</span>`;
                        if (b.status === 'SUCCESS') {
                            actionsCell = `<a href="${BASE_URL}/api/settings.php?action=download_backup&file=${encodeURIComponent(b.backup_file)}" class="btn btn-sm btn-outline-secondary py-0.5 px-2 text-indigo fw-semibold" title="Download DB file"><i class="fa-solid fa-download"></i> Get</a>`;
                        }
                        
                        body.append(`
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark small">${b.backup_file}</div>
                                    <div class="text-secondary" style="font-size: 0.75rem;">${date} | By: ${b.creator_name || 'System'}</div>
                                </td>
                                <td><span class="small text-secondary">${b.backup_size}</span></td>
                                <td>${actionsCell}</td>
                            </tr>
                        `);
                    });
                } else {
                    body.append('<tr><td colspan="3" class="text-center py-4 text-secondary">No backups recorded yet</td></tr>');
                }
            }
        });
    }

    // ==================== GST SLAB TAG MANAGER ====================
    let gstSlabs = [];

    function loadGstSlabs(csvString) {
        gstSlabs = csvString.split(',').map(s => parseFloat(s.trim())).filter(n => !isNaN(n));
        gstSlabs = [...new Set(gstSlabs)].sort((a, b) => a - b);
        renderGstTags();
    }

    function renderGstTags() {
        const box = $('#gst-tags-box').empty();
        gstSlabs.forEach(function(val) {
            box.append(
                '<span class="badge bg-light-primary d-inline-flex align-items-center gap-1 px-2 py-1" style="font-size:0.85rem;">' +
                val + '%' +
                '<button type="button" class="btn-close btn-close-sm ms-1 btn-remove-gst" data-val="' + val + '" style="font-size:0.6rem;filter:none;opacity:0.6;" aria-label="Remove"></button>' +
                '</span>'
            );
        });
        if (gstSlabs.length === 0) {
            box.append('<span class="text-muted small">No GST slabs added</span>');
        }
        $('#set-slabs').val(gstSlabs.join(','));
    }

    function addGstSlab(val) {
        val = parseFloat(val);
        if (isNaN(val) || val < 0 || val > 100) {
            Swal.fire({ icon: 'warning', title: 'Invalid', text: 'Enter a valid percentage (0-100)', background: '#ffffff', color: '#0f172a' });
            return;
        }
        if (gstSlabs.includes(val)) {
            Swal.fire({ icon: 'info', title: 'Already Exists', text: val + '% is already added', timer: 1500, showConfirmButton: false, background: '#ffffff', color: '#0f172a' });
            return;
        }
        gstSlabs.push(val);
        gstSlabs.sort((a, b) => a - b);
        renderGstTags();
    }

    $('#btn-add-gst-slab').click(function() {
        const val = $('#gst-new-value').val();
        if (val === '') return;
        addGstSlab(val);
        $('#gst-new-value').val('').focus();
    });

    $('#gst-new-value').on('keypress', function(e) {
        if (e.which === 13) { e.preventDefault(); $('#btn-add-gst-slab').click(); }
    });

    $('#gst-preset-dropdown').on('change', function() {
        const val = $(this).val();
        if (val !== '') {
            addGstSlab(val);
            $(this).val('');
        }
    });

    $(document).on('click', '.btn-remove-gst', function() {
        const val = parseFloat($(this).data('val'));
        gstSlabs = gstSlabs.filter(s => s !== val);
        renderGstTags();
    });

    // Hide Commit Changes button when Coupons tab is active
    $('button[data-bs-target="#coupons-pane"]').on('shown.bs.tab', function() { $('#settings-save-row').hide(); });
    $('#settingsTabs button:not([data-bs-target="#coupons-pane"])').on('shown.bs.tab', function() { $('#settings-save-row').show(); });

    // ==================== COUPON MANAGEMENT ====================
    const couponCsrf = $('input[name="csrf_token"]').val();

    // Load coupons when tab is shown
    $('button[data-bs-target="#coupons-pane"]').on('shown.bs.tab', function() { loadCoupons(); });

    function loadCoupons() {
        $.getJSON(BASE_URL + '/api/coupons.php?action=list', function(res) {
            const tbody = $('#coupons-tbody').empty();
            if (!res.status || res.data.length === 0) {
                tbody.html('<tr><td colspan="9" class="text-center py-4 text-secondary">No coupons created yet. Click "Create Coupon" to add one.</td></tr>');
                return;
            }
            res.data.forEach(function(c) {
                const typeLabel = c.discount_type === 'PERCENTAGE' ? c.discount_value + '%' : '₹' + parseFloat(c.discount_value).toFixed(2);
                const validUntil = c.valid_until ? new Date(c.valid_until).toLocaleDateString('en-IN', {day:'2-digit',month:'short',year:'numeric'}) : 'No expiry';
                const isExpired = c.valid_until && new Date(c.valid_until) < new Date();
                const limitText = c.usage_limit > 0 ? c.used_count + '/' + c.usage_limit : c.used_count + '/∞';
                const statusBadge = isExpired ? '<span class="badge bg-light-danger">Expired</span>' : '<span class="badge bg-light-success">Active</span>';

                tbody.append(`
                    <tr>
                        <td><span class="fw-bold text-indigo">${c.coupon_code}</span></td>
                        <td>${c.coupon_name}</td>
                        <td><span class="badge bg-light-primary">${c.discount_type}</span></td>
                        <td class="fw-semibold">${typeLabel}</td>
                        <td>₹${parseFloat(c.min_order_amount).toFixed(0)}</td>
                        <td class="${isExpired ? 'text-rose' : ''}">${validUntil}</td>
                        <td>${limitText}</td>
                        <td>${statusBadge}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-secondary py-0 px-2 btn-edit-coupon" data-id="${c.id}" title="Edit"><i class="fa-solid fa-pen"></i></button>
                            <button class="btn btn-sm btn-outline-danger py-0 px-2 btn-delete-coupon" data-id="${c.id}" title="Delete"><i class="fa-solid fa-trash-can"></i></button>
                        </td>
                    </tr>
                `);
            });
        });
    }

    // Create coupon button
    $('#btn-add-coupon').click(function() {
        $('#couponModalTitle').html('<i class="fa-solid fa-tags text-indigo me-2"></i>Create Coupon');
        $('#coupon-id').val(0);
        $('#couponForm')[0].reset();
        $('#couponModal').modal('show');
    });

    // Edit coupon
    $(document).on('click', '.btn-edit-coupon', function() {
        const id = $(this).data('id');
        $.getJSON(BASE_URL + '/api/coupons.php?action=get&id=' + id, function(res) {
            if (!res.status) return;
            const c = res.data;
            $('#couponModalTitle').html('<i class="fa-solid fa-pen text-indigo me-2"></i>Edit Coupon');
            $('#coupon-id').val(c.id);
            $('#coupon-code').val(c.coupon_code);
            $('#coupon-name').val(c.coupon_name);
            $('#coupon-type').val(c.discount_type);
            $('#coupon-value').val(c.discount_value);
            $('#coupon-min').val(c.min_order_amount);
            $('#coupon-max').val(c.max_discount || '');
            $('#coupon-from').val(c.valid_from || '');
            $('#coupon-until').val(c.valid_until || '');
            $('#coupon-limit').val(c.usage_limit);
            $('#couponModal').modal('show');
        });
    });

    // Save coupon
    $('#couponForm').submit(function(e) {
        e.preventDefault();
        $.post(BASE_URL + '/api/coupons.php?action=save', $(this).serialize(), function(res) {
            if (res.status) {
                $('#couponModal').modal('hide');
                loadCoupons();
                Swal.fire({ icon: 'success', title: 'Coupon Saved', text: res.message, timer: 1500, showConfirmButton: false, background: '#ffffff', color: '#0f172a' });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#ffffff', color: '#0f172a' });
            }
        }, 'json');
    });

    // Delete coupon
    $(document).on('click', '.btn-delete-coupon', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete Coupon?', text: 'This coupon will be deactivated.',
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Delete', confirmButtonColor: '#dc2626',
            background: '#ffffff', color: '#0f172a'
        }).then(function(r) {
            if (r.isConfirmed) {
                $.post(BASE_URL + '/api/coupons.php?action=delete', { csrf_token: couponCsrf, id: id }, function(res) {
                    if (res.status) { loadCoupons(); }
                }, 'json');
            }
        });
    });
});
</script>
