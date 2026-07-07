<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Application Settings & DB backups View
 */
?>

<div class="row g-4">
    <!-- Top forms panel (Full Width) -->
    <div class="col-md-12">
        <div class="panel-card" style="overflow: visible;">
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
                    <li class="nav-item" role="presentation">
                        <button class="nav-link border-0 bg-transparent fw-semibold settings-tab" id="theme-tab" data-bs-toggle="tab" data-bs-target="#theme-pane" type="button" role="tab" aria-controls="theme-pane" aria-selected="false">
                            <i class="fa-solid fa-paint-roller me-2"></i>Theme & Display
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link border-0 bg-transparent fw-semibold settings-tab" id="data-tab" data-bs-toggle="tab" data-bs-target="#data-pane" type="button" role="tab" aria-controls="data-pane" aria-selected="false">
                            <i class="fa-solid fa-database me-2"></i>Data & Backups
                        </button>
                    </li>
                </ul>
            </div>
            
            <div class="panel-body">
                <form id="settingsForm" novalidate>
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
                                <div class="col-md-12 mb-2">
                                    <h6 class="text-indigo mb-0"><i class="fa-solid fa-hashtag me-2"></i>Document Numbering</h6>
                                    <small class="text-muted">Set prefix, start number, and end number for each document. Alert shows when 100 numbers are left.</small>
                                </div>

                                <!-- Invoice -->
                                <div class="col-md-12">
                                    <div class="border rounded p-3 mb-2">
                                        <div class="row g-2 align-items-end">
                                            <div class="col-md-3">
                                                <label class="form-label small fw-semibold">Invoice Prefix</label>
                                                <input type="text" class="form-control" name="invoice_prefix" id="set-prefix" placeholder="INV-">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-semibold">Start Number</label>
                                                <input type="number" min="1" class="form-control" name="invoice_start" id="set-inv-start" placeholder="e.g. 1">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-semibold">End Number (Limit)</label>
                                                <input type="number" min="1" class="form-control" name="invoice_end" id="set-inv-end" placeholder="e.g. 10000">
                                            </div>
                                            <div class="col-md-3">
                                                <span class="badge bg-light-primary d-block py-2 text-center" id="inv-range-status">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quotation -->
                                <div class="col-md-12">
                                    <div class="border rounded p-3 mb-2">
                                        <div class="row g-2 align-items-end">
                                            <div class="col-md-3">
                                                <label class="form-label small fw-semibold">Quotation Prefix</label>
                                                <input type="text" class="form-control" name="quotation_prefix" id="set-qt-prefix" placeholder="QT-">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-semibold">Start Number</label>
                                                <input type="number" min="1" class="form-control" name="quotation_start" id="set-qt-start" placeholder="e.g. 1">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-semibold">End Number (Limit)</label>
                                                <input type="number" min="1" class="form-control" name="quotation_end" id="set-qt-end" placeholder="e.g. 10000">
                                            </div>
                                            <div class="col-md-3">
                                                <span class="badge bg-light-primary d-block py-2 text-center" id="qt-range-status">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Purchase -->
                                <div class="col-md-12">
                                    <div class="border rounded p-3 mb-2">
                                        <div class="row g-2 align-items-end">
                                            <div class="col-md-3">
                                                <label class="form-label small fw-semibold">Purchase Prefix</label>
                                                <input type="text" class="form-control" name="purchase_prefix" id="set-po-prefix" placeholder="PO-">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-semibold">Start Number</label>
                                                <input type="number" min="1" class="form-control" name="purchase_start" id="set-po-start" placeholder="e.g. 1">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-semibold">End Number (Limit)</label>
                                                <input type="number" min="1" class="form-control" name="purchase_end" id="set-po-end" placeholder="e.g. 10000">
                                            </div>
                                            <div class="col-md-3">
                                                <span class="badge bg-light-primary d-block py-2 text-center" id="po-range-status">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Challan -->
                                <div class="col-md-12">
                                    <div class="border rounded p-3 mb-2">
                                        <div class="row g-2 align-items-end">
                                            <div class="col-md-3">
                                                <label class="form-label small fw-semibold">Challan Prefix</label>
                                                <input type="text" class="form-control" name="challan_prefix" id="set-dc-prefix" placeholder="DC-">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-semibold">Start Number</label>
                                                <input type="number" min="1" class="form-control" name="challan_start" id="set-dc-start" placeholder="e.g. 1">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-semibold">End Number (Limit)</label>
                                                <input type="number" min="1" class="form-control" name="challan_end" id="set-dc-end" placeholder="e.g. 10000">
                                            </div>
                                            <div class="col-md-3">
                                                <span class="badge bg-light-primary d-block py-2 text-center" id="dc-range-status">-</span>
                                            </div>
                                        </div>
                                    </div>
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
                                    <label class="form-label">State (for CGST/SGST)</label>
                                    <select class="form-select searchable-select" name="state_code" id="set-state-code">
                                        <option value="">-- Select State --</option>
                                        <option value="01">01 - Jammu & Kashmir</option>
                                        <option value="02">02 - Himachal Pradesh</option>
                                        <option value="03">03 - Punjab</option>
                                        <option value="04">04 - Chandigarh</option>
                                        <option value="05">05 - Uttarakhand</option>
                                        <option value="06">06 - Haryana</option>
                                        <option value="07">07 - Delhi</option>
                                        <option value="08">08 - Rajasthan</option>
                                        <option value="09">09 - Uttar Pradesh</option>
                                        <option value="10">10 - Bihar</option>
                                        <option value="11">11 - Sikkim</option>
                                        <option value="12">12 - Arunachal Pradesh</option>
                                        <option value="13">13 - Nagaland</option>
                                        <option value="14">14 - Manipur</option>
                                        <option value="15">15 - Mizoram</option>
                                        <option value="16">16 - Tripura</option>
                                        <option value="17">17 - Meghalaya</option>
                                        <option value="18">18 - Assam</option>
                                        <option value="19">19 - West Bengal</option>
                                        <option value="20">20 - Jharkhand</option>
                                        <option value="21">21 - Odisha</option>
                                        <option value="22">22 - Chhattisgarh</option>
                                        <option value="23">23 - Madhya Pradesh</option>
                                        <option value="24">24 - Gujarat</option>
                                        <option value="25">25 - Daman & Diu</option>
                                        <option value="26">26 - Dadra & Nagar Haveli</option>
                                        <option value="27">27 - Maharashtra</option>
                                        <option value="29">29 - Karnataka</option>
                                        <option value="30">30 - Goa</option>
                                        <option value="31">31 - Lakshadweep</option>
                                        <option value="32">32 - Kerala</option>
                                        <option value="33">33 - Tamil Nadu</option>
                                        <option value="34">34 - Puducherry</option>
                                        <option value="35">35 - Andaman & Nicobar</option>
                                        <option value="36">36 - Telangana</option>
                                        <option value="37">37 - Andhra Pradesh</option>
                                        <option value="38">38 - Ladakh</option>
                                    </select>
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

                        <!-- LOYALTY PANE -->
                        <div class="tab-pane fade" id="loyalty-pane" role="tabpanel" aria-labelledby="loyalty-tab" tabindex="0">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <h6 class="text-indigo mb-3"><i class="fa-solid fa-gift me-2"></i>Customer Loyalty Program</h6>
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
                            </div>
                        </div>

                        <!-- THEME & DISPLAY PANE -->
                        <div class="tab-pane fade" id="theme-pane" role="tabpanel" aria-labelledby="theme-tab" tabindex="0">
                            <div class="row g-4">
                                <div class="col-md-12" id="invoice-template-section">
                                    <h6 class="text-indigo mb-3"><i class="fa-solid fa-file-pdf me-2"></i>Invoice Document Template</h6>
                                    <input type="hidden" name="invoice_template" id="set-invoice-template" value="standard">
                                    <style>
                                        .tab-pane:focus { outline: none; }
                                        .template-preview-wrapper { position: relative; z-index: 1; }
                                        .template-preview-wrapper:hover { z-index: 50; }
                                        .template-preview { border: 2px solid transparent; cursor: pointer; transition: 0.2s; border-radius: 8px; overflow: hidden; }
                                        .template-preview.selected { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.2); }
                                        .preview-content { height: 120px; background: #f8f9fa; display: flex; flex-direction: column; }
                                        .preview-header { height: 25px; width: 100%; }
                                        .preview-body { flex: 1; padding: 10px; }
                                        .preview-line { height: 4px; background: #e9ecef; margin-bottom: 4px; border-radius: 2px; }
                                        
                                        /* Large Hover Popover */
                                        .hover-large-preview {
                                            position: absolute;
                                            bottom: 100%;
                                            left: 50%;
                                            transform: translateX(-50%) translateY(10px) scale(0.9);
                                            opacity: 0;
                                            visibility: hidden;
                                            width: 320px;
                                            background: #fff;
                                            border-radius: 12px;
                                            box-shadow: 0 20px 50px rgba(0,0,0,0.25);
                                            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
                                            pointer-events: none;
                                            margin-bottom: 15px;
                                            border: 1px solid #e5e7eb;
                                            overflow: hidden;
                                        }
                                        .template-preview-wrapper:hover .hover-large-preview {
                                            opacity: 1;
                                            visibility: visible;
                                            transform: translateX(-50%) translateY(0) scale(1);
                                        }
                                        .hover-large-preview::after {
                                            content: '';
                                            position: absolute;
                                            top: 100%;
                                            left: 50%;
                                            margin-left: -10px;
                                            border-width: 10px;
                                            border-style: solid;
                                            border-color: #fff transparent transparent transparent;
                                        }
                                        /* Detailed Mockup Elements */
                                        .mockup { padding: 15px; font-family: sans-serif; }
                                        .mockup-table { width: 100%; margin-top: 10px; border-collapse: collapse; }
                                        .mockup-table th { font-size: 8px; text-transform: uppercase; padding: 4px; text-align: left; }
                                        .mockup-table td { font-size: 8px; padding: 4px; border-bottom: 1px solid #eee; }
                                        
                                        /* Alignment Helpers */
                                        .hover-large-preview.preview-align-left { left: 0; transform: translateX(0) translateY(10px) scale(0.9); transform-origin: left bottom; }
                                        .template-preview-wrapper:hover .hover-large-preview.preview-align-left { transform: translateX(0) translateY(0) scale(1); }
                                        .hover-large-preview.preview-align-left::after { left: 15%; margin-left: 0; }
                                        
                                        .hover-large-preview.preview-align-right { left: auto; right: 0; transform: translateX(0) translateY(10px) scale(0.9); transform-origin: right bottom; }
                                        .template-preview-wrapper:hover .hover-large-preview.preview-align-right { transform: translateX(0) translateY(0) scale(1); }
                                        .hover-large-preview.preview-align-right::after { left: auto; right: 15%; margin-left: 0; }
                                    </style>
                                    <div class="row g-3 justify-content-center">
                                        <!-- Standard -->
                                        <div class="col-md-3 template-preview-wrapper">
                                            <div class="hover-large-preview preview-align-left">
                                                <div style="background: linear-gradient(135deg, #2563eb, #6366f1); padding: 10px; border-radius: 12px 12px 0 0; color: #fff;">
                                                    <div style="font-size:12px; font-weight:bold;">COMPANY NAME</div>
                                                    <div style="font-size:8px;">INVOICE #INV-123</div>
                                                </div>
                                                <div class="mockup">
                                                    <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                                                        <div><div style="font-size:9px; font-weight:bold;">Billed To:</div><div style="font-size:8px; color:#666;">Customer Name<br>Address Line</div></div>
                                                        <div style="text-align:right;"><div style="font-size:9px; font-weight:bold;">Details:</div><div style="font-size:8px; color:#666;">Date: 2026-07-07</div></div>
                                                    </div>
                                                    <table class="mockup-table">
                                                        <tr style="background:#f8f9fa;"><th colspan="2">Item</th><th>Qty</th><th>Total</th></tr>
                                                        <tr><td colspan="2"><strong>Product A</strong></td><td>1</td><td>$50</td></tr>
                                                        <tr><td colspan="2"><strong>Product B</strong></td><td>2</td><td>$100</td></tr>
                                                    </table>
                                                    <div style="text-align:right; margin-top:10px; font-size:10px; font-weight:bold;">Total: $150</div>
                                                </div>
                                            </div>
                                            <div class="template-preview" data-theme="standard" onclick="selectTemplate('standard')">
                                                <div class="preview-content">
                                                    <div class="preview-header" style="background: linear-gradient(135deg, #2563eb, #6366f1);"></div>
                                                    <div class="preview-body"><div class="preview-line w-50"></div><div class="preview-line w-75"></div></div>
                                                </div>
                                                <div class="p-2 text-center fw-bold small bg-white border-top">Standard</div>
                                            </div>
                                        </div>
                                        <!-- Professional -->
                                        <div class="col-md-3 template-preview-wrapper">
                                            <div class="hover-large-preview">
                                                <div style="border: 2px solid #374151;">
                                                    <div style="background: #1f2937; padding: 10px; color: #fff;">
                                                        <div style="font-size:12px; font-weight:bold; font-family:Georgia;">COMPANY NAME</div>
                                                        <div style="font-size:8px; font-family:Georgia;">INVOICE #INV-123</div>
                                                    </div>
                                                    <div class="mockup" style="font-family:Georgia;">
                                                        <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                                                            <div><div style="font-size:9px; font-weight:bold;">Billed To:</div><div style="font-size:8px; color:#666;">Customer Name</div></div>
                                                        </div>
                                                        <table class="mockup-table">
                                                            <tr style="background:#374151; color:#fff;"><th colspan="2">Item</th><th>Qty</th><th>Total</th></tr>
                                                            <tr><td colspan="2"><strong>Product A</strong></td><td>1</td><td>$50</td></tr>
                                                            <tr><td colspan="2"><strong>Product B</strong></td><td>2</td><td>$100</td></tr>
                                                        </table>
                                                        <div style="text-align:right; margin-top:10px; font-size:10px; font-weight:bold;">Total: $150</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="template-preview" data-theme="professional" onclick="selectTemplate('professional')">
                                                <div class="preview-content" style="border: 2px solid #374151;">
                                                    <div class="preview-header" style="background: #1f2937;"></div>
                                                    <div class="preview-body"><div class="preview-line w-50" style="background:#9ca3af"></div><div class="preview-line w-75"></div></div>
                                                </div>
                                                <div class="p-2 text-center fw-bold small bg-white border-top">Professional</div>
                                            </div>
                                        </div>
                                        <!-- Modern -->
                                        <div class="col-md-3 template-preview-wrapper">
                                            <div class="hover-large-preview">
                                                <div style="border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); padding: 15px;">
                                                    <div style="border-bottom: 2px solid #ecfdf5; padding-bottom: 10px; margin-bottom: 10px;">
                                                        <div style="font-size:12px; font-weight:800; color:#10b981;">COMPANY NAME</div>
                                                        <div style="font-size:8px;">INVOICE #INV-123</div>
                                                    </div>
                                                    <div class="mockup" style="padding:0;">
                                                        <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                                                            <div><div style="font-size:9px; font-weight:bold; color:#10b981;">Billed To:</div><div style="font-size:8px; color:#666;">Customer Name</div></div>
                                                        </div>
                                                        <table class="mockup-table">
                                                            <tr style="background:#ecfdf5; color:#065f46;"><th colspan="2">Item</th><th>Qty</th><th>Total</th></tr>
                                                            <tr><td colspan="2"><strong>Product A</strong></td><td>1</td><td>$50</td></tr>
                                                            <tr><td colspan="2"><strong>Product B</strong></td><td>2</td><td>$100</td></tr>
                                                        </table>
                                                        <div style="text-align:right; margin-top:10px; font-size:10px; font-weight:bold; color:#10b981;">Total: $150</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="template-preview" data-theme="modern" onclick="selectTemplate('modern')">
                                                <div class="preview-content" style="border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); margin: 5px; height: 110px;">
                                                    <div class="preview-header" style="background: transparent; border-bottom: 2px solid #ecfdf5; height: 20px;"></div>
                                                    <div class="preview-body"><div class="preview-line w-75" style="background:#10b981"></div></div>
                                                </div>
                                                <div class="p-2 text-center fw-bold small bg-white border-top">Modern</div>
                                            </div>
                                        </div>
                                        <!-- Classic -->
                                        <div class="col-md-3 template-preview-wrapper">
                                            <div class="hover-large-preview preview-align-right">
                                                <div style="border: 3px solid #000; padding: 10px; font-family: 'Times New Roman', serif;">
                                                    <div style="border-bottom: 3px solid #000; padding-bottom: 5px; margin-bottom: 10px;">
                                                        <div style="font-size:14px; font-weight:bold; text-align:center;">COMPANY NAME</div>
                                                        <div style="font-size:10px; text-align:center;">INVOICE #INV-123</div>
                                                    </div>
                                                    <div class="mockup" style="padding:0; font-family: 'Times New Roman', serif;">
                                                        <table class="mockup-table" style="border: 1px solid #000;">
                                                            <tr style="background:#eee;"><th colspan="2" style="border:1px solid #000;">Item</th><th style="border:1px solid #000;">Qty</th><th style="border:1px solid #000;">Total</th></tr>
                                                            <tr><td colspan="2" style="border:1px solid #000;"><strong>Product A</strong></td><td style="border:1px solid #000;">1</td><td style="border:1px solid #000;">$50</td></tr>
                                                            <tr><td colspan="2" style="border:1px solid #000;"><strong>Product B</strong></td><td style="border:1px solid #000;">2</td><td style="border:1px solid #000;">$100</td></tr>
                                                        </table>
                                                        <div style="text-align:right; margin-top:10px; font-size:10px; font-weight:bold;">Total: $150</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="template-preview" data-theme="classic" onclick="selectTemplate('classic')">
                                                <div class="preview-content" style="border: 3px solid #000; margin: 2px; height: 116px; background: #fff;">
                                                    <div class="preview-header" style="background: #fff; border-bottom: 3px solid #000; height: 20px;"></div>
                                                    <div class="preview-body"><div class="preview-line w-100" style="background:#000; height:2px;"></div></div>
                                                </div>
                                                <div class="p-2 text-center fw-bold small bg-white border-top">Classic</div>
                                            </div>
                                        </div>
                                    </div>
                                    <script>
                                        function selectTemplate(theme) {
                                            $('#set-invoice-template').val(theme);
                                            $('.template-preview').removeClass('selected');
                                            $('.template-preview[data-theme="' + theme + '"]').addClass('selected');
                                        }
                                        setTimeout(() => {
                                            selectTemplate($('#set-invoice-template').val() || 'standard');
                                        }, 1000);
                                    </script>
                                </div>
                                <div class="col-md-12" id="pos-template-section" style="display: none;">
                                    <h6 class="text-indigo mb-3"><i class="fa-solid fa-receipt me-2"></i>POS Receipt Template</h6>
                                    <input type="hidden" name="pos_template" id="set-pos-template" value="pos_standard">
                                    <div class="row g-3 justify-content-center">
                                        <!-- POS Standard -->
                                        <div class="col-md-3 template-preview-wrapper">
                                            <div class="hover-large-preview preview-align-left">
                                                <div style="background: #fff; padding: 10px; color: #000; border-bottom: 1px dashed #000; text-align: center;">
                                                    <div style="font-size:12px; font-weight:bold;">COMPANY NAME</div>
                                                    <div style="font-size:8px;">RECEIPT #INV-123</div>
                                                </div>
                                                <div class="mockup text-center" style="color: #000;">
                                                    <table class="mockup-table">
                                                        <tr style="border-bottom: 1px solid #000;"><th colspan="2">Item</th><th>Qty</th><th>Total</th></tr>
                                                        <tr><td colspan="2"><strong>Product A</strong></td><td>1</td><td>$50</td></tr>
                                                        <tr><td colspan="2"><strong>Product B</strong></td><td>2</td><td>$100</td></tr>
                                                    </table>
                                                    <div style="text-align:right; margin-top:10px; font-size:12px; font-weight:bold;">Total: $150</div>
                                                    <div style="font-size:8px; margin-top:5px; text-align:center;">Thank You!</div>
                                                </div>
                                            </div>
                                            <div class="template-preview pos" data-theme="pos_standard" onclick="selectPosTemplate('pos_standard')">
                                                <div class="preview-content" style="background:#fff; align-items:center; border: 1px dashed #ccc; padding: 5px;">
                                                    <div class="preview-line w-50" style="background:#000; height:2px; margin-bottom:8px;"></div>
                                                    <div class="preview-line w-75" style="background:#000; height:1px;"></div>
                                                    <div class="preview-line w-75" style="background:#000; height:1px;"></div>
                                                    <div class="preview-line w-50" style="background:#000; height:1px; margin-top:5px;"></div>
                                                </div>
                                                <div class="p-2 text-center fw-bold small bg-white border-top">Thermal Standard</div>
                                            </div>
                                        </div>
                                        <!-- POS Minimal -->
                                        <div class="col-md-3 template-preview-wrapper">
                                            <div class="hover-large-preview preview-align-left">
                                                <div style="background: #fff; padding: 5px; color: #000; text-align: left;">
                                                    <div style="font-size:14px; font-weight:bold; letter-spacing: 1px;">COMPANY NAME</div>
                                                    <div style="font-size:8px;">INV-123 | 2026-07-07</div>
                                                </div>
                                                <div class="mockup" style="color: #000; padding: 5px;">
                                                    <table class="mockup-table" style="border:none;">
                                                        <tr><td colspan="2"><strong>Product A</strong> x1</td><td class="text-end">$50</td></tr>
                                                        <tr><td colspan="2"><strong>Product B</strong> x2</td><td class="text-end">$100</td></tr>
                                                    </table>
                                                    <div style="border-top: 1px solid #000; padding-top:5px; text-align:right; font-size:12px; font-weight:bold;">Total: $150</div>
                                                </div>
                                            </div>
                                            <div class="template-preview pos" data-theme="pos_minimal" onclick="selectPosTemplate('pos_minimal')">
                                                <div class="preview-content" style="background:#fff; border: 1px solid #eee; padding: 5px; align-items:flex-start;">
                                                    <div class="preview-line w-25" style="background:#000; height:3px; margin-bottom:10px;"></div>
                                                    <div class="preview-line w-100" style="background:#eee; height:1px;"></div>
                                                    <div class="preview-line w-100" style="background:#eee; height:1px;"></div>
                                                    <div class="preview-line w-50" style="background:#000; height:2px; align-self:flex-end;"></div>
                                                </div>
                                                <div class="p-2 text-center fw-bold small bg-white border-top">Thermal Minimal</div>
                                            </div>
                                        </div>
                                        <!-- POS Bold -->
                                        <div class="col-md-3 template-preview-wrapper">
                                            <div class="hover-large-preview preview-align-right">
                                                <div style="background: #000; padding: 15px; color: #fff; text-align: center;">
                                                    <div style="font-size:14px; font-weight:900;">COMPANY NAME</div>
                                                    <div style="font-size:10px;">RECEIPT</div>
                                                </div>
                                                <div class="mockup text-center" style="color: #000;">
                                                    <div style="font-size:14px; font-weight:bold; margin-bottom:5px;">INV-123</div>
                                                    <table class="mockup-table">
                                                        <tr style="background:#000; color:#fff;"><th colspan="2">Item</th><th>Qty</th><th>Total</th></tr>
                                                        <tr><td colspan="2"><strong>Product A</strong></td><td>1</td><td>$50</td></tr>
                                                    </table>
                                                    <div style="background:#000; color:#fff; text-align:center; padding:10px; margin-top:10px; font-size:16px; font-weight:bold;">TOTAL: $150</div>
                                                </div>
                                            </div>
                                            <div class="template-preview pos" data-theme="pos_bold" onclick="selectPosTemplate('pos_bold')">
                                                <div class="preview-content" style="background:#fff; border: 1px solid #ccc;">
                                                    <div style="background:#000; height:30px; width:100%;"></div>
                                                    <div style="padding:5px;">
                                                        <div class="preview-line w-100" style="background:#ccc; height:10px; margin-bottom:2px;"></div>
                                                        <div style="background:#000; height:20px; width:100%; margin-top:10px;"></div>
                                                    </div>
                                                </div>
                                                <div class="p-2 text-center fw-bold small bg-white border-top">Thermal Bold</div>
                                            </div>
                                        </div>
                                    </div>
                                    <script>
                                        function selectPosTemplate(theme) {
                                            $('#set-pos-template').val(theme);
                                            $('.template-preview.pos').removeClass('selected');
                                            $('.template-preview.pos[data-theme="' + theme + '"]').addClass('selected');
                                        }
                                        setTimeout(() => {
                                            selectPosTemplate($('#set-pos-template').val() || 'pos_standard');
                                        }, 1000);
                                    </script>
                                </div>
                                <div class="col-md-12">
                                    <hr class="my-3">
                                </div>
                                <div class="col-md-4" id="thermal-width-section">
                                    <label class="form-label">Thermal Width</label>
                                    <select class="form-select" name="thermal_width" id="set-thermal-width">
                                        <option value="80mm">80mm</option>
                                        <option value="58mm">58mm</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label"><i class="fa-solid fa-language text-indigo me-1"></i>System Language</label>
                                    <select class="form-select" name="system_language" id="set-system-language">
                                        <option value="en">English (Default)</option>
                                        <option value="hi">Hindi (हिंदी)</option>
                                        <option value="gu">Gujarati (ગુજરાતી)</option>
                                        <option value="mr">Marathi (मराठी)</option>
                                        <option value="bn">Bengali (বাংলা)</option>
                                        <option value="ta">Tamil (தமிழ்)</option>
                                        <option value="te">Telugu (తెలుగు)</option>
                                        <option value="kn">Kannada (ಕನ್ನಡ)</option>
                                        <option value="ml">Malayalam (മലയാളം)</option>
                                        <option value="pa">Punjabi (ਪੰਜਾਬੀ)</option>
                                        <option value="ur">Urdu (اردو)</option>
                                        <option value="or">Odia (ଓଡ଼ିଆ)</option>
                                        <option value="as">Assamese (অসমীয়া)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label mb-0"><i class="fa-solid fa-barcode text-indigo me-1"></i>POS & Barcode Mode</label>
                                        <button type="button" class="btn btn-sm btn-outline-info py-0 px-2" style="font-size: 0.75rem; display: none;" data-bs-toggle="modal" data-bs-target="#posGuideModal" id="btn-pos-guide">
                                            <i class="fa-solid fa-circle-question me-1"></i>Guide
                                        </button>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="set-pos-mode" value="1">
                                        <input type="hidden" name="pos_mode" id="pos-mode-hidden" value="0">
                                        <label class="form-check-label" for="set-pos-mode">Enable Scanner</label>
                                    </div>
                                    <script>
                                        function togglePosSettings(isPosEnabled) {
                                            document.getElementById('pos-mode-hidden').value = isPosEnabled ? 1 : 0;
                                            document.getElementById('btn-pos-guide').style.display = isPosEnabled ? 'inline-block' : 'none';
                                            
                                            // Toggle visibility of related settings
                                            const invoiceTemplateSec = document.getElementById('invoice-template-section');
                                            const posTemplateSec = document.getElementById('pos-template-section');
                                            const thermalWidthSec = document.getElementById('thermal-width-section');
                                            
                                            if (isPosEnabled) {
                                                if(invoiceTemplateSec) invoiceTemplateSec.style.display = 'none';
                                                if(posTemplateSec) posTemplateSec.style.display = 'block';
                                                if(thermalWidthSec) thermalWidthSec.style.display = 'block';
                                            } else {
                                                if(invoiceTemplateSec) invoiceTemplateSec.style.display = 'block';
                                                if(posTemplateSec) posTemplateSec.style.display = 'none';
                                                if(thermalWidthSec) thermalWidthSec.style.display = 'none';
                                            }
                                        }

                                        document.getElementById('set-pos-mode').addEventListener('change', function() {
                                            togglePosSettings(this.checked);
                                            if (this.checked) {
                                                var posModal = new bootstrap.Modal(document.getElementById('posGuideModal'));
                                                posModal.show();
                                            }
                                        });
                                        
                                        // Update on load
                                        setTimeout(() => {
                                            const isChecked = document.getElementById('set-pos-mode').checked;
                                            togglePosSettings(isChecked);
                                        }, 1000);
                                    </script>
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

                    <!-- DATA & BACKUPS PANE -->
                    <div class="tab-pane fade" id="data-pane" role="tabpanel" aria-labelledby="data-tab" tabindex="0">
                        <div class="row g-4">
                            <!-- Backups -->
                            <div class="col-md-8">
                                <div class="panel-card h-100" style="border: 1px solid #e2e8f0; box-shadow: none;">
                                    <div class="panel-header bg-light">
                                        <h6 class="mb-0 text-dark"><i class="fa-solid fa-database me-2 text-indigo"></i>Database Backups</h6>
                                        <?php if ($auth->hasPermission('Run Backups')): ?>
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 text-indigo" id="btn-run-backup" title="Create Backup">
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
                            <!-- Danger Zone -->
                            <div class="col-md-4">
                                <?php if (($_SESSION['role_id'] ?? 0) == 1): ?>
                                <div class="panel-card h-100" style="border: 1px solid #fecdd3; box-shadow: none;">
                                    <div class="panel-header border-bottom border-danger" style="background: #fff1f2;">
                                        <h6 class="mb-0 text-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i>Danger Zone</h6>
                                    </div>
                                    <div class="panel-body d-flex flex-column justify-content-between">
                                        <p class="text-muted mb-3">Permanently delete all records from the system. Only admin user accounts will be preserved. This action cannot be undone.</p>
                                        <button type="button" class="btn btn-danger w-100 mt-auto" id="btn-purge-all">
                                            <i class="fa-solid fa-trash-can me-2"></i>Delete All Records
                                        </button>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
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
                $("#set-prefix").val(s.invoice_prefix || 'INV-');
                $("#set-inv-start").val(s.invoice_start || 1);
                $("#set-inv-end").val(s.invoice_end || 99999);
                $("#set-qt-prefix").val(s.quotation_prefix || 'QT-');
                $("#set-qt-start").val(s.quotation_start || 1);
                $("#set-qt-end").val(s.quotation_end || 99999);
                $("#set-po-prefix").val(s.purchase_prefix || 'PO-');
                $("#set-po-start").val(s.purchase_start || 1);
                $("#set-po-end").val(s.purchase_end || 99999);
                $("#set-dc-prefix").val(s.challan_prefix || 'DC-');
                $("#set-dc-start").val(s.challan_start || 1);
                $("#set-dc-end").val(s.challan_end || 99999);

                // Show range status with usage from API
                if (s.doc_usage) updateRangeStatuses(s.doc_usage);

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
                $("#set-system-language").val(s.system_language || 'en');
                $("#set-pos-mode").prop('checked', s.pos_mode == 1);
                $("#pos-mode-hidden").val(s.pos_mode || 0);
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
        
        if (!$("#set-name").val().trim()) {
            Swal.fire({ icon: 'warning', title: 'Missing Info', text: 'Business Name is required.', background: '#ffffff', color: '#0f172a' });
            return;
        }

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
                        
                        let downloadBtn = '';
                        if (b.status === 'SUCCESS') {
                            downloadBtn = `<a href="${BASE_URL}/api/settings.php?action=download_backup&file=${encodeURIComponent(b.backup_file)}" class="btn btn-sm btn-outline-secondary py-0 px-2 text-indigo fw-semibold" title="Download"><i class="fa-solid fa-download"></i></a>`;
                        } else {
                            downloadBtn = `<span class="text-rose small fw-semibold">Failed</span>`;
                        }

                        body.append(`
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark small">${b.backup_file}</div>
                                    <div class="text-secondary" style="font-size: 0.75rem;">${date} | By: ${b.creator_name || 'System'}</div>
                                </td>
                                <td><span class="small text-secondary">${b.backup_size}</span></td>
                                <td>
                                    <div class="d-flex gap-1 justify-content-end">
                                        ${downloadBtn}
                                        <button class="btn btn-sm btn-outline-danger py-0 px-2 btn-delete-backup" data-id="${b.id}" title="Delete"><i class="fa-solid fa-trash-can"></i></button>
                                    </div>
                                </td>
                            </tr>
                        `);
                    });
                } else {
                    body.append('<tr><td colspan="3" class="text-center py-4 text-secondary">No backups recorded yet</td></tr>');
                }
            }
        });
    }

    // Delete backup
    $(document).on('click', '.btn-delete-backup', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete Backup?',
            text: 'This backup file will be permanently removed.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            background: '#ffffff',
            color: '#0f172a'
        }).then(function(r) {
            if (r.isConfirmed) {
                $.post(BASE_URL + '/api/settings.php?action=delete_backup', {
                    csrf_token: $('input[name="csrf_token"]').val(),
                    id: id
                }, function(res) {
                    if (res.status) {
                        loadBackupsList();
                        Swal.fire({ icon: 'success', title: 'Deleted', text: res.message, timer: 1500, showConfirmButton: false, background: '#ffffff', color: '#0f172a' });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#ffffff', color: '#0f172a' });
                    }
                }, 'json');
            }
        });
    });

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

    // Range status display
    function updateRangeStatuses(usage) {
        const docs = [
            { key: 'invoice', el: '#inv-range-status', startEl: '#set-inv-start', endEl: '#set-inv-end' },
            { key: 'quotation', el: '#qt-range-status', startEl: '#set-qt-start', endEl: '#set-qt-end' },
            { key: 'purchase', el: '#po-range-status', startEl: '#set-po-start', endEl: '#set-po-end' },
            { key: 'challan', el: '#dc-range-status', startEl: '#set-dc-start', endEl: '#set-dc-end' }
        ];
        docs.forEach(function(d) {
            const used = usage[d.key] || 0;
            const start = parseInt($(d.startEl).val()) || 1;
            const end = parseInt($(d.endEl).val()) || 99999;
            const total = end - start + 1;
            const remaining = total - used;
            const badge = $(d.el);

            if (remaining <= 0) {
                badge.removeClass('bg-light-primary bg-light-warning').addClass('bg-light-danger').html('<i class="fa-solid fa-circle-exclamation me-1"></i>LIMIT REACHED');
            } else if (remaining <= 100) {
                badge.removeClass('bg-light-primary bg-light-danger').addClass('bg-light-warning').html('<i class="fa-solid fa-triangle-exclamation me-1"></i>' + remaining + ' left!');
            } else {
                badge.removeClass('bg-light-warning bg-light-danger').addClass('bg-light-primary').text('Used: ' + used + ' / ' + total);
            }
        });
    }

    // Hide Commit Changes button when Coupons tab is active
    $('button[data-bs-target="#coupons-pane"]').on('shown.bs.tab', function() { $('#settings-save-row').hide(); });
    $('#settingsTabs button:not([data-bs-target="#coupons-pane"])').on('shown.bs.tab', function() { $('#settings-save-row').show(); });

    // ==================== PURGE ALL RECORDS ====================
    $('#btn-purge-all').click(function() {
        Swal.fire({
            title: 'Delete All Records?',
            html: '<p class="mb-2">This will <strong>permanently delete</strong> all data including:</p>' +
                  '<p class="text-start small text-muted mb-0">Invoices, Quotations, Purchases, Challans, Products, Customers, Suppliers, Expenses, Payments, Returns, Logs, and more.</p>' +
                  '<p class="mt-2 fw-semibold text-danger">Only admin accounts will be kept. This cannot be undone!</p>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Continue',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            background: '#ffffff',
            color: '#0f172a'
        }).then((firstResult) => {
            if (!firstResult.isConfirmed) return;

            Swal.fire({
                title: 'Enter Your Password',
                text: 'Confirm your admin password to proceed with deletion.',
                input: 'password',
                inputPlaceholder: 'Enter your password',
                inputAttributes: { autocomplete: 'current-password' },
                icon: 'lock',
                showCancelButton: true,
                confirmButtonText: 'Delete Everything',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                background: '#ffffff',
                color: '#0f172a',
                inputValidator: (value) => {
                    if (!value) return 'Password is required';
                }
            }).then((passResult) => {
                if (!passResult.isConfirmed) return;

                Swal.fire({
                    title: 'Deleting all records...',
                    text: 'Please wait while all data is being removed.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); },
                    background: '#ffffff',
                    color: '#0f172a'
                });
                $.post(BASE_URL + '/api/settings.php?action=purge_all', {
                    csrf_token: $('input[name="csrf_token"]').val(),
                    password: passResult.value
                }, function(res) {
                    if (res.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'All Records Deleted',
                            text: res.message,
                            background: '#ffffff',
                            color: '#0f172a'
                        }).then(function() {
                            location.reload();
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Failed', text: res.message, background: '#ffffff', color: '#0f172a' });
                    }
                }, 'json');
            });
        });
    });

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
