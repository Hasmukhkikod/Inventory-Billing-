<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Products & Inventory Directory view
 */
?>
<div class="panel-card mb-4">
    <div class="panel-header">
        <ul class="nav nav-tabs border-0" id="inventoryTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active text-indigo border-0 bg-transparent fw-semibold" id="products-tab" data-bs-toggle="tab" data-bs-target="#products-pane" type="button" role="tab" aria-controls="products-pane" aria-selected="true">
                    <i class="fa-solid fa-boxes-stacked me-2"></i>Products List
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link text-secondary border-0 bg-transparent fw-semibold" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories-pane" type="button" role="tab" aria-controls="categories-pane" aria-selected="false">
                    <i class="fa-solid fa-tags me-2"></i>Categories
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link text-secondary border-0 bg-transparent fw-semibold" id="brands-tab" data-bs-toggle="tab" data-bs-target="#brands-pane" type="button" role="tab" aria-controls="brands-pane" aria-selected="false">
                    <i class="fa-solid fa-bookmark me-2"></i>Brands
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link text-secondary border-0 bg-transparent fw-semibold" id="units-tab" data-bs-toggle="tab" data-bs-target="#units-pane" type="button" role="tab" aria-controls="units-pane" aria-selected="false">
                    <i class="fa-solid fa-scale-balanced me-2"></i>Units
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link text-secondary border-0 bg-transparent fw-semibold" id="conversions-tab" data-bs-toggle="tab" data-bs-target="#conversions-pane" type="button" role="tab" aria-controls="conversions-pane" aria-selected="false">
                    <i class="fa-solid fa-right-left me-2"></i>Unit Conversions
                </button>
            </li>
        </ul>
        <div>
            <a href="<?php echo BASE_URL; ?>/products/form.php" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i> Add Product
            </a>
        </div>
    </div>
    
    <div class="panel-body">
        <div class="tab-content" id="inventoryTabsContent">
            
            <!-- PRODUCTS TAB PANE -->
            <div class="tab-pane fade show active" id="products-pane" role="tabpanel" aria-labelledby="products-tab" tabindex="0">
                <div class="bulk-actions-toolbar d-flex align-items-center gap-2 mb-3" data-table="productsTable" data-api="<?php echo BASE_URL; ?>/api/products.php">
                    <div class="form-check">
                        <input class="form-check-input bulk-select-all" type="checkbox" title="Select All">
                    </div>
                    <select class="form-select form-select-sm bulk-action-select" style="width: 180px;">
                        <option value="">-- Bulk Action --</option>
                        <option value="delete">Delete Selected</option>
                        <option value="export_csv">Export Selected CSV</option>
                    </select>
                    <button class="btn btn-sm btn-outline-secondary btn-bulk-apply" disabled>
                        <i class="fa-solid fa-check-double me-1"></i>Apply
                    </button>
                    <span class="badge bg-light-primary small d-none bulk-count">0 selected</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover w-100" id="productsTable">
                        <thead>
                            <tr>
                                <th style="width: 30px;"></th>
                                <th>Image</th>
                                <th>Product Name</th>
                                <th>SKU</th>
                                <th>HSN</th>
                                <th>Category</th>
                                <th>Selling Price</th>
                                <th>Stock</th>
                                <th>GST</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            
            <!-- CATEGORIES TAB PANE -->
            <div class="tab-pane fade" id="categories-pane" role="tabpanel" aria-labelledby="categories-tab" tabindex="0">
                <div class="row">
                    <div class="col-md-4 mb-4 mb-md-0">
                        <div class="card bg-secondary border border-secondary p-3">
                            <h6 class="text-indigo mb-3" id="cat-form-title">Add New Category</h6>
                            <form id="categoryForm">
                                <input type="hidden" name="id" id="cat-id" value="0">
                                <div class="mb-3">
                                    <label class="form-label">Category Name</label>
                                    <input type="text" class="form-control" name="category_name" id="cat-name" required placeholder="e.g. Mobile Phones">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" id="cat-desc" rows="3" placeholder="Category brief..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm w-100">Save Category</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm w-100 mt-2 d-none" id="btn-cancel-cat">Cancel Edit</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="table-responsive">
                            <table class="table table-hover w-100" id="categoriesTable">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BRANDS TAB PANE -->
            <div class="tab-pane fade" id="brands-pane" role="tabpanel" aria-labelledby="brands-tab" tabindex="0">
                <div class="row">
                    <div class="col-md-4 mb-4 mb-md-0">
                        <div class="card bg-secondary border border-secondary p-3">
                            <h6 class="text-indigo mb-3" id="brand-form-title">Add New Brand</h6>
                            <form id="brandForm">
                                <input type="hidden" name="id" id="brand-id" value="0">
                                <div class="mb-3">
                                    <label class="form-label">Brand Name</label>
                                    <input type="text" class="form-control" name="brand_name" id="brand-name" required placeholder="e.g. Apple">
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm w-100">Save Brand</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm w-100 mt-2 d-none" id="btn-cancel-brand">Cancel Edit</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="table-responsive">
                            <table class="table table-hover w-100" id="brandsTable">
                                <thead>
                                    <tr>
                                        <th>Brand ID</th>
                                        <th>Brand Name</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- UNITS TAB PANE -->
            <div class="tab-pane fade" id="units-pane" role="tabpanel" aria-labelledby="units-tab" tabindex="0">
                <div class="row">
                    <div class="col-md-4 mb-4 mb-md-0">
                        <div class="card bg-secondary border border-secondary p-3">
                            <h6 class="text-indigo mb-3" id="unit-form-title">Add Measurement Unit</h6>
                            <form id="unitForm">
                                <input type="hidden" name="id" id="unit-id" value="0">
                                <div class="mb-3">
                                    <label class="form-label">Unit Name</label>
                                    <input type="text" class="form-control" name="unit_name" id="unit-name" required placeholder="e.g. Pieces">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Short Code</label>
                                    <input type="text" class="form-control" name="short_name" id="unit-short" required placeholder="e.g. Pcs">
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm w-100">Save Unit</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm w-100 mt-2 d-none" id="btn-cancel-unit">Cancel Edit</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="table-responsive">
                            <table class="table table-hover w-100" id="unitsTable">
                                <thead>
                                    <tr>
                                        <th>Unit Name</th>
                                        <th>Short Name</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- UNIT CONVERSIONS TAB PANE -->
            <div class="tab-pane fade" id="conversions-pane" role="tabpanel" aria-labelledby="conversions-tab" tabindex="0">
                <div class="row">
                    <div class="col-md-4 mb-4 mb-md-0">
                        <div class="card bg-secondary border border-secondary p-3">
                            <h6 class="text-indigo mb-3" id="conv-form-title">Add Unit Conversion</h6>
                            <form id="conversionForm">
                                <input type="hidden" name="id" id="conv-id" value="0">
                                <div class="mb-3">
                                    <label class="form-label">Primary Unit</label>
                                    <select class="form-select" name="primary_unit_id" id="conv-primary" required>
                                        <option value="">-- Select --</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Secondary Unit</label>
                                    <select class="form-select" name="secondary_unit_id" id="conv-secondary" required>
                                        <option value="">-- Select --</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Conversion Factor</label>
                                    <div class="input-group">
                                        <span class="input-group-text" id="conv-factor-label">1 Unit =</span>
                                        <input type="number" step="0.0001" min="0.0001" class="form-control" name="conversion_factor" id="conv-factor" required placeholder="e.g. 12">
                                        <span class="input-group-text" id="conv-factor-unit"></span>
                                    </div>
                                    <small class="text-muted" id="conv-preview"></small>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm w-100">Save Conversion</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm w-100 mt-2 d-none" id="btn-cancel-conv">Cancel Edit</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="table-responsive">
                            <table class="table table-hover w-100" id="conversionsTable">
                                <thead>
                                    <tr>
                                        <th>Primary Unit</th>
                                        <th>Secondary Unit</th>
                                        <th>Factor</th>
                                        <th class="text-end">Actions</th>
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

<!-- STOCK ADJUSTMENT MODAL -->
<div class="modal fade" id="adjustStockModal" tabindex="-1" aria-labelledby="adjustStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adjustStockModalLabel">Stock Adjustment Ledger</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="adjustStockForm">
                <div class="modal-body">
                    <input type="hidden" name="product_id" id="adjust-prod-id" value="0">
                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="adjust-prod-name" readonly>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Adjustment Type</label>
                            <select class="form-select" name="adjustment_type" id="adjust-type" required>
                                <option value="Increase">Stock Increase (+)</option>
                                <option value="Decrease">Stock Decrease (-)</option>
                                <option value="Damage">Damage (-)</option>
                                <option value="Lost">Lost (-)</option>
                                <option value="Expired">Expired (-)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" step="0.01" class="form-control" name="quantity" id="adjust-qty" required min="0.01" placeholder="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Audit Remarks</label>
                        <textarea class="form-control" name="remarks" id="adjust-remarks" rows="3" required placeholder="e.g. Audit correction"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Stock Correction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Products DataTable Setup
    const productsTable = $('#productsTable').DataTable({
        ajax: {
            url: BASE_URL + '/api/products.php?action=list',
            dataSrc: 'data'
        },
        columns: [
            {
                data: 'id',
                orderable: false,
                className: 'text-center',
                render: function(data) {
                    return '<input type="checkbox" class="form-check-input bulk-check" value="' + data + '">';
                }
            },
            {
                data: 'image',
                render: function(data) {
                    if (data) {
                        return `<img src="${BASE_URL}/${data}" class="rounded border border-secondary" style="width: 38px; height: 38px; object-fit: cover;">`;
                    }
                    return `<div class="bg-tertiary rounded border border-secondary text-secondary d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;"><i class="fa-solid fa-image"></i></div>`;
                }
            },
            { 
                data: 'product_name', 
                className: 'fw-semibold',
                render: function(data, type, row) {
                    return `<a href="${BASE_URL}/products/view.php?id=${row.id}" class="text-indigo text-decoration-none">${data}</a>`;
                }
            },
            { data: 'sku' },
            { data: 'hsn_code', defaultContent: '<span class="text-muted">-</span>' },
            { data: 'category_name', defaultContent: '<span class="text-muted">Uncategorized</span>' },
            { 
                data: 'selling_price',
                render: function(data) {
                    return '₹ ' + parseFloat(data).toLocaleString('en-IN', {minimumFractionDigits: 2});
                }
            },
            {
                data: 'current_stock',
                render: function(data, type, row) {
                    const stock = parseFloat(data);
                    const min = parseFloat(row.minimum_stock);
                    const unit = row.unit_name || 'Pcs';
                    let display = stock + ' ' + unit;
                    if (row.secondary_unit_name && row.conversion_factor) {
                        const secQty = parseFloat((stock * parseFloat(row.conversion_factor)).toFixed(2));
                        display += ' <span class="text-muted small">(' + secQty + ' ' + row.secondary_unit_name + ')</span>';
                    }
                    if (stock <= min) {
                        return `<span class="badge bg-light-danger fw-bold"><i class="fa-solid fa-circle-exclamation me-1"></i>${display}</span>`;
                    }
                    return `<span class="badge bg-light-success fw-bold">${display}</span>`;
                }
            },
            { 
                data: 'gst_percentage',
                render: function(data) { return parseFloat(data) + '%'; }
            },
            { 
                data: 'status',
                render: function(data) {
                    return data === 'ACTIVE' 
                        ? '<span class="badge bg-light-success">Active</span>' 
                        : '<span class="badge bg-light-danger">Inactive</span>';
                }
            },
            {
                data: null,
                className: 'text-end',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-secondary py-1 px-2 text-indigo btn-adjust" data-id="${row.id}" data-name="${row.product_name}" title="Adjust Stock">
                                <i class="fa-solid fa-sliders"></i>
                            </button>
                            <a href="${BASE_URL}/products/form.php?id=${row.id}" class="btn btn-sm btn-outline-secondary py-1 px-2 text-emerald" title="Edit Product">
                                <i class="fa-solid fa-pencil"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-secondary py-1 px-2 text-danger btn-delete" data-id="${row.id}" title="Delete Product">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[2, 'asc']],
        drawCallback: function() {
            applyMobileLabels();
        },
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search inventory..."
        }
    });

    $('#productsTable').on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete product?',
            text: "This will set status to Inactive or delete the record.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#dc2626',
            confirmButtonText: 'Yes, remove!',
            background: '#ffffff',
            color: '#0f172a'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE_URL + '/api/products.php?action=delete',
                    type: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status) {
                            productsTable.ajax.reload();
                            Swal.fire({ icon: 'success', title: 'Removed', text: res.message, background: '#ffffff', color: '#0f172a' });
                        }
                    }
                });
            }
        });
    });

    // Stock adjustments modal
    $('#productsTable').on('click', '.btn-adjust', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        
        $("#adjust-prod-id").val(id);
        $("#adjust-prod-name").val(name);
        $("#adjust-qty").val('');
        $("#adjust-remarks").val('');
        
        $("#adjustStockModal").modal('show');
    });

    $("#adjustStockForm").submit(function(e) {
        e.preventDefault();
        const data = $(this).serialize() + '&action=adjust_stock';
        
        $.ajax({
            url: BASE_URL + '/api/products.php',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    $("#adjustStockModal").modal('hide');
                    productsTable.ajax.reload();
                    Swal.fire({ icon: 'success', title: 'Adjusted', text: res.message, background: '#ffffff', color: '#0f172a' });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#ffffff', color: '#0f172a' });
                }
            }
        });
    });

    // Categories Table
    const categoriesTable = $('#categoriesTable').DataTable({
        ajax: { url: BASE_URL + '/api/products.php?action=categories_list', dataSrc: 'data' },
        columns: [
            { data: 'category_name', className: 'fw-semibold text-dark' },
            { data: 'description', defaultContent: '-' },
            {
                data: null, className: 'text-end', orderable: false,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-sm btn-outline-secondary py-1 px-2 text-emerald btn-edit-cat" data-id="${row.id}" data-name="${row.category_name}" data-desc="${row.description}">
                            <i class="fa-solid fa-pencil"></i>
                        </button>
                    `;
                }
            }
        ],
        drawCallback: function() { applyMobileLabels(); }
    });

    $("#categoryForm").submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: BASE_URL + '/api/products.php?action=category_save',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    categoriesTable.ajax.reload();
                    $("#categoryForm")[0].reset();
                    $("#cat-id").val('0');
                    $("#cat-form-title").text("Add New Category");
                    $("#btn-cancel-cat").addClass('d-none');
                    Swal.fire({ icon: 'success', title: 'Saved', text: res.message, background: '#ffffff', color: '#0f172a' });
                }
            }
        });
    });

    $('#categoriesTable').on('click', '.btn-edit-cat', function() {
        $("#cat-id").val($(this).data('id'));
        $("#cat-name").val($(this).data('name'));
        $("#cat-desc").val($(this).data('desc'));
        $("#cat-form-title").text("Edit Category");
        $("#btn-cancel-cat").removeClass('d-none');
    });

    $("#btn-cancel-cat").click(function() {
        $("#categoryForm")[0].reset();
        $("#cat-id").val('0');
        $("#cat-form-title").text("Add New Category");
        $(this).addClass('d-none');
    });

    // Brands Table
    const brandsTable = $('#brandsTable').DataTable({
        ajax: { url: BASE_URL + '/api/products.php?action=brands_list', dataSrc: 'data' },
        columns: [
            { data: 'id' },
            { data: 'brand_name', className: 'fw-semibold text-dark' },
            {
                data: null, className: 'text-end', orderable: false,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-sm btn-outline-secondary py-1 px-2 text-emerald btn-edit-brand" data-id="${row.id}" data-name="${row.brand_name}">
                            <i class="fa-solid fa-pencil"></i>
                        </button>
                    `;
                }
            }
        ],
        drawCallback: function() { applyMobileLabels(); }
    });

    $("#brandForm").submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: BASE_URL + '/api/products.php?action=brand_save',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    brandsTable.ajax.reload();
                    $("#brandForm")[0].reset();
                    $("#brand-id").val('0');
                    $("#brand-form-title").text("Add New Brand");
                    $("#btn-cancel-brand").addClass('d-none');
                    Swal.fire({ icon: 'success', title: 'Saved', text: res.message, background: '#ffffff', color: '#0f172a' });
                }
            }
        });
    });

    $('#brandsTable').on('click', '.btn-edit-brand', function() {
        $("#brand-id").val($(this).data('id'));
        $("#brand-name").val($(this).data('name'));
        $("#brand-form-title").text("Edit Brand");
        $("#btn-cancel-brand").removeClass('d-none');
    });

    $("#btn-cancel-brand").click(function() {
        $("#brandForm")[0].reset();
        $("#brand-id").val('0');
        $("#brand-form-title").text("Add New Brand");
        $(this).addClass('d-none');
    });

    // Units Table
    const unitsTable = $('#unitsTable').DataTable({
        ajax: { url: BASE_URL + '/api/products.php?action=units_list', dataSrc: 'data' },
        columns: [
            { data: 'unit_name', className: 'fw-semibold text-dark' },
            { data: 'short_name' },
            {
                data: null, className: 'text-end', orderable: false,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-sm btn-outline-secondary py-1 px-2 text-emerald btn-edit-unit" data-id="${row.id}" data-name="${row.unit_name}" data-short="${row.short_name}">
                            <i class="fa-solid fa-pencil"></i>
                        </button>
                    `;
                }
            }
        ],
        drawCallback: function() { applyMobileLabels(); }
    });

    $("#unitForm").submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: BASE_URL + '/api/products.php?action=unit_save',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    unitsTable.ajax.reload();
                    $("#unitForm")[0].reset();
                    $("#unit-id").val('0');
                    $("#unit-form-title").text("Add Measurement Unit");
                    $("#btn-cancel-unit").addClass('d-none');
                    Swal.fire({ icon: 'success', title: 'Saved', text: res.message, background: '#ffffff', color: '#0f172a' });
                }
            }
        });
    });

    $('#unitsTable').on('click', '.btn-edit-unit', function() {
        $("#unit-id").val($(this).data('id'));
        $("#unit-name").val($(this).data('name'));
        $("#unit-short").val($(this).data('short'));
        $("#unit-form-title").text("Edit Unit");
        $("#btn-cancel-unit").removeClass('d-none');
    });

    $("#btn-cancel-unit").click(function() {
        $("#unitForm")[0].reset();
        $("#unit-id").val('0');
        $("#unit-form-title").text("Add Measurement Unit");
        $(this).addClass('d-none');
    });

    // Unit Conversions Table
    let convUnits = [];
    function loadConvUnits() {
        $.getJSON(BASE_URL + '/api/products.php?action=units_list', function(res) {
            if (res.status) {
                convUnits = res.data;
                const pSel = $('#conv-primary'), sSel = $('#conv-secondary');
                pSel.find('option:not(:first)').remove();
                sSel.find('option:not(:first)').remove();
                res.data.forEach(function(u) {
                    pSel.append('<option value="' + u.id + '">' + u.unit_name + ' (' + u.short_name + ')</option>');
                    sSel.append('<option value="' + u.id + '">' + u.unit_name + ' (' + u.short_name + ')</option>');
                });
            }
        });
    }
    loadConvUnits();

    const conversionsTable = $('#conversionsTable').DataTable({
        ajax: { url: BASE_URL + '/api/products.php?action=unit_conversions_list', dataSrc: 'data' },
        columns: [
            { data: null, className: 'fw-semibold text-dark', render: function(d,t,r) { return r.primary_unit_name + ' (' + r.primary_short_name + ')'; } },
            { data: null, render: function(d,t,r) { return r.secondary_unit_name + ' (' + r.secondary_short_name + ')'; } },
            { data: null, render: function(d,t,r) { return '1 ' + r.primary_short_name + ' = ' + parseFloat(r.conversion_factor) + ' ' + r.secondary_short_name; } },
            {
                data: null, className: 'text-end', orderable: false,
                render: function(d,t,r) {
                    return '<button class="btn btn-sm btn-outline-secondary py-1 px-2 text-emerald btn-edit-conv" data-id="' + r.id + '" data-primary="' + r.primary_unit_id + '" data-secondary="' + r.secondary_unit_id + '" data-factor="' + r.conversion_factor + '"><i class="fa-solid fa-pencil"></i></button>' +
                           ' <button class="btn btn-sm btn-outline-secondary py-1 px-2 text-danger btn-delete-conv" data-id="' + r.id + '"><i class="fa-solid fa-trash-can"></i></button>';
                }
            }
        ],
        drawCallback: function() { applyMobileLabels(); }
    });

    $('#conv-primary, #conv-secondary').on('change', function() {
        const pId = $('#conv-primary').val(), sId = $('#conv-secondary').val();
        const pUnit = convUnits.find(u => u.id == pId), sUnit = convUnits.find(u => u.id == sId);
        $('#conv-factor-label').text('1 ' + (pUnit ? pUnit.short_name : 'Unit') + ' =');
        $('#conv-factor-unit').text(sUnit ? sUnit.short_name : '');
        const f = parseFloat($('#conv-factor').val()) || 0;
        if (pUnit && sUnit && f > 0) {
            $('#conv-preview').text('1 ' + pUnit.short_name + ' = ' + f + ' ' + sUnit.short_name);
        } else {
            $('#conv-preview').text('');
        }
    });
    $('#conv-factor').on('input', function() { $('#conv-primary').trigger('change'); });

    $('#conversionForm').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: BASE_URL + '/api/products.php?action=unit_conversion_save',
            type: 'POST',
            data: $(this).serialize() + '&csrf_token=' + $('meta[name="csrf-token"]').attr('content'),
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    conversionsTable.ajax.reload();
                    $('#conversionForm')[0].reset();
                    $('#conv-id').val('0');
                    $('#conv-form-title').text('Add Unit Conversion');
                    $('#btn-cancel-conv').addClass('d-none');
                    $('#conv-preview').text('');
                    Swal.fire({ icon: 'success', title: 'Saved', text: res.message, background: '#ffffff', color: '#0f172a' });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#ffffff', color: '#0f172a' });
                }
            }
        });
    });

    $('#conversionsTable').on('click', '.btn-edit-conv', function() {
        $('#conv-id').val($(this).data('id'));
        $('#conv-primary').val($(this).data('primary'));
        $('#conv-secondary').val($(this).data('secondary'));
        $('#conv-factor').val($(this).data('factor'));
        $('#conv-form-title').text('Edit Conversion');
        $('#btn-cancel-conv').removeClass('d-none');
        $('#conv-primary').trigger('change');
    });

    $('#conversionsTable').on('click', '.btn-delete-conv', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete conversion?', icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#2563eb', cancelButtonColor: '#dc2626', confirmButtonText: 'Yes, delete!',
            background: '#ffffff', color: '#0f172a'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(BASE_URL + '/api/products.php?action=unit_conversion_delete', { id: id }, function(res) {
                    if (res.status) { conversionsTable.ajax.reload(); Swal.fire({ icon: 'success', title: 'Deleted', background: '#ffffff', color: '#0f172a' }); }
                }, 'json');
            }
        });
    });

    $('#btn-cancel-conv').click(function() {
        $('#conversionForm')[0].reset();
        $('#conv-id').val('0');
        $('#conv-form-title').text('Add Unit Conversion');
        $('#conv-preview').text('');
        $(this).addClass('d-none');
    });

    // Tab loads
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        const target = $(e.target).attr("id");
        if (target === "categories-tab") categoriesTable.ajax.reload();
        if (target === "brands-tab") brandsTable.ajax.reload();
        if (target === "units-tab") unitsTable.ajax.reload();
        if (target === "conversions-tab") { conversionsTable.ajax.reload(); loadConvUnits(); }
        if (target === "products-tab") productsTable.ajax.reload();
    });

    function applyMobileLabels() {
        $('table').each(function() {
            const headers = [];
            $(this).find('thead th').each(function() {
                headers.push($(this).text().trim());
            });
            $(this).find('tbody tr').each(function() {
                $(this).find('td').each(function(index) {
                    if (headers[index]) {
                        $(this).attr('data-label', headers[index]);
                    }
                });
            });
        });
    }
});
</script>
