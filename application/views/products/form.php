<?php
/**
 * IIMS v2.0 - Product Form (Add / Edit)
 * Fixed: Dynamic GST slabs, Add New buttons, negative price prevention
 */
$isEdit = !empty($product);
$compSettings = $this->db->query("SELECT gst_slabs FROM company_settings WHERE id = 1 LIMIT 1")->fetch();
$gstSlabs = explode(',', $compSettings['gst_slabs'] ?? '0,5,12,18,28');
?>
<div class="panel-card">
    <div class="panel-header">
        <h5 class="mb-0 text-indigo">
            <i class="fa-solid fa-box-open me-2"></i><?php echo $isEdit ? 'Edit Product Details' : 'Add New Product'; ?>
        </h5>
        <a href="<?php echo BASE_URL; ?>/products/index.php" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Inventory
        </a>
    </div>

    <div class="panel-body">
        <form id="productForm" enctype="multipart/form-data">
            <?php echo \App\Models\Helpers::csrfField(); ?>
            <input type="hidden" name="id" id="prod-id" value="<?php echo $isEdit ? (int)$product['id'] : '0'; ?>">
            <input type="hidden" name="existing_image" id="prod-existing-image" value="<?php echo $isEdit ? \App\Models\Helpers::sanitize($product['image']) : ''; ?>">

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Product Name *</label>
                    <input type="text" class="form-control" name="product_name" id="prod-name" required placeholder="Product title" value="<?php echo $isEdit ? \App\Models\Helpers::sanitize($product['product_name']) : ''; ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">SKU *</label>
                    <input type="text" class="form-control" name="sku" id="prod-sku" required placeholder="Unique SKU" value="<?php echo $isEdit ? \App\Models\Helpers::sanitize($product['sku']) : ''; ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Barcode (EAN/UPC)</label>
                    <input type="text" class="form-control" name="barcode" id="prod-barcode" placeholder="Scan or enter barcode" value="<?php echo $isEdit ? \App\Models\Helpers::sanitize($product['barcode']) : ''; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">HSN Code</label>
                    <input type="text" class="form-control" name="hsn_code" id="prod-hsn" placeholder="HSN/SAC code" value="<?php echo $isEdit ? \App\Models\Helpers::sanitize($product['hsn_code'] ?? '') : ''; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <div class="input-group">
                        <select class="form-select searchable-select" name="category_id" id="prod-category">
                            <option value="">-- Choose --</option>
                        </select>
                        <button class="btn btn-outline-secondary" type="button" id="btn-add-category" title="Add New Category"><i class="fa-solid fa-plus"></i></button>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Brand</label>
                    <div class="input-group">
                        <select class="form-select searchable-select" name="brand_id" id="prod-brand">
                            <option value="">-- Choose --</option>
                        </select>
                        <button class="btn btn-outline-secondary" type="button" id="btn-add-brand" title="Add New Brand"><i class="fa-solid fa-plus"></i></button>
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Cost Price *</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="0.01" min="0" class="form-control" name="cost_price" id="prod-cost" required value="<?php echo $isEdit ? (float)$product['cost_price'] : '0.00'; ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Selling Price *</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="0.01" min="0" class="form-control" name="selling_price" id="prod-selling" required value="<?php echo $isEdit ? (float)$product['selling_price'] : '0.00'; ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">GST Tax (%)</label>
                    <select class="form-select" name="gst_percentage" id="prod-gst">
                        <?php foreach ($gstSlabs as $slab): $slab = trim($slab); ?>
                            <option value="<?php echo $slab; ?>" <?php echo ($isEdit && (float)$product['gst_percentage'] == (float)$slab) ? 'selected' : (!$isEdit && $slab == '18' ? 'selected' : ''); ?>><?php echo $slab; ?>% GST</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Measurement Unit</label>
                    <div class="input-group">
                        <select class="form-select searchable-select" name="unit_id" id="prod-unit">
                            <option value="">-- Choose --</option>
                        </select>
                        <button class="btn btn-outline-secondary" type="button" id="btn-add-unit" title="Add New Unit"><i class="fa-solid fa-plus"></i></button>
                    </div>
                </div>

                <?php if (!$isEdit): ?>
                <div class="col-md-4">
                    <label class="form-label">Initial Stock Qty</label>
                    <input type="number" step="0.01" min="0" class="form-control" name="opening_stock" id="prod-stock" value="0">
                </div>
                <?php endif; ?>

                <div class="col-md-4">
                    <label class="form-label">Minimum Stock Alert</label>
                    <input type="number" step="0.01" min="0" class="form-control" name="minimum_stock" id="prod-min-stock" value="<?php echo $isEdit ? (float)$product['minimum_stock'] : '5'; ?>">
                </div>

                <div class="col-md-12">
                    <label class="form-label">Product Image</label>
                    <input type="file" class="form-control" name="product_image" id="prod-image" accept="image/jpeg,image/png,image/webp">
                    <div class="mt-1 text-muted small">JPG, PNG, WEBP (Max: 2MB)</div>
                    <?php if ($isEdit && !empty($product['image'])): ?>
                    <div class="mt-2">
                        <span class="d-block small text-secondary mb-1">Current Image:</span>
                        <img src="<?php echo BASE_URL . '/' . \App\Models\Helpers::sanitize($product['image']); ?>" class="rounded border" style="height: 80px;">
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top text-end">
                <a href="<?php echo BASE_URL; ?>/products/index.php" class="btn btn-outline-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-circle-check me-1"></i><?php echo $isEdit ? 'Update Product' : 'Save Product'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Quick Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header"><h6 class="modal-title">Add Category</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="text" class="form-control" id="new-category-name" placeholder="Category name" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" id="btn-save-category"><i class="fa-solid fa-check me-1"></i>Add</button>
            </div>
        </div>
    </div>
</div>

<!-- Quick Add Brand Modal -->
<div class="modal fade" id="addBrandModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header"><h6 class="modal-title">Add Brand</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="text" class="form-control" id="new-brand-name" placeholder="Brand name" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" id="btn-save-brand"><i class="fa-solid fa-check me-1"></i>Add</button>
            </div>
        </div>
    </div>
</div>

<!-- Quick Add Unit Modal -->
<div class="modal fade" id="addUnitModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header"><h6 class="modal-title">Add Unit</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-2">
                    <input type="text" class="form-control" id="new-unit-name" placeholder="Unit name (e.g. Kilograms)" required>
                </div>
                <div>
                    <input type="text" class="form-control" id="new-unit-short" placeholder="Short name (e.g. Kg)" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" id="btn-save-unit"><i class="fa-solid fa-check me-1"></i>Add</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const csrfToken = $('input[name="csrf_token"]').val();
    const selectedCategory = '<?php echo $isEdit ? (int)$product['category_id'] : ''; ?>';
    const selectedBrand = '<?php echo $isEdit ? (int)$product['brand_id'] : ''; ?>';
    const selectedUnit = '<?php echo $isEdit ? (int)$product['unit_id'] : ''; ?>';

    loadCategories();
    loadBrands();
    loadUnits();

    // Prevent negative values on blur
    $('input[type="number"][min="0"]').on('blur', function() {
        if (parseFloat($(this).val()) < 0) $(this).val('0');
    });

    // Save Product
    $("#productForm").submit(function(e) {
        e.preventDefault();

        // Validate prices
        var cost = parseFloat($("#prod-cost").val());
        var sell = parseFloat($("#prod-selling").val());
        if (cost < 0 || sell < 0) {
            Swal.fire({ icon: 'error', title: 'Invalid Price', text: 'Prices cannot be negative.', background: '#ffffff', color: '#0f172a' });
            return;
        }

        const formData = new FormData(this);
        formData.append('action', 'save');

        $.ajax({
            url: BASE_URL + '/api/products.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    Swal.fire({ icon: 'success', title: 'Saved', text: res.message, background: '#ffffff', color: '#0f172a' }).then(() => {
                        window.location.href = BASE_URL + '/products/index.php';
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#ffffff', color: '#0f172a' });
                }
            }
        });
    });

    // ===== Load Dropdowns =====
    function loadCategories(selectId) {
        $.getJSON(BASE_URL + '/api/products.php?action=categories_list', function(res) {
            const sel = $("#prod-category");
            sel.find('option:not(:first)').remove();
            if (res.status) {
                res.data.forEach(function(c) {
                    const s = (selectId ? c.id == selectId : c.id == selectedCategory) ? 'selected' : '';
                    sel.append('<option value="' + c.id + '" ' + s + '>' + c.category_name + '</option>');
                });
            }
        });
    }

    function loadBrands(selectId) {
        $.getJSON(BASE_URL + '/api/products.php?action=brands_list', function(res) {
            const sel = $("#prod-brand");
            sel.find('option:not(:first)').remove();
            if (res.status) {
                res.data.forEach(function(b) {
                    const s = (selectId ? b.id == selectId : b.id == selectedBrand) ? 'selected' : '';
                    sel.append('<option value="' + b.id + '" ' + s + '>' + b.brand_name + '</option>');
                });
            }
        });
    }

    function loadUnits(selectId) {
        $.getJSON(BASE_URL + '/api/products.php?action=units_list', function(res) {
            const sel = $("#prod-unit");
            sel.find('option:not(:first)').remove();
            if (res.status) {
                res.data.forEach(function(u) {
                    const s = (selectId ? u.id == selectId : u.id == selectedUnit) ? 'selected' : '';
                    sel.append('<option value="' + u.id + '" ' + s + '>' + u.unit_name + ' (' + u.short_name + ')</option>');
                });
            }
        });
    }

    // ===== Quick Add Category =====
    $('#btn-add-category').click(function() { $('#new-category-name').val(''); $('#addCategoryModal').modal('show'); });
    $('#btn-save-category').click(function() {
        const name = $('#new-category-name').val().trim();
        if (!name) return;
        $.post(BASE_URL + '/api/products.php?action=category_save', { csrf_token: csrfToken, category_name: name }, function(res) {
            if (res.status) {
                $('#addCategoryModal').modal('hide');
                loadCategories(res.data.id);
                Swal.fire({ icon: 'success', title: 'Category Added', timer: 1500, showConfirmButton: false, background: '#ffffff', color: '#0f172a' });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#ffffff', color: '#0f172a' });
            }
        }, 'json');
    });

    // ===== Quick Add Brand =====
    $('#btn-add-brand').click(function() { $('#new-brand-name').val(''); $('#addBrandModal').modal('show'); });
    $('#btn-save-brand').click(function() {
        const name = $('#new-brand-name').val().trim();
        if (!name) return;
        $.post(BASE_URL + '/api/products.php?action=brand_save', { csrf_token: csrfToken, brand_name: name }, function(res) {
            if (res.status) {
                $('#addBrandModal').modal('hide');
                loadBrands(res.data.id);
                Swal.fire({ icon: 'success', title: 'Brand Added', timer: 1500, showConfirmButton: false, background: '#ffffff', color: '#0f172a' });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#ffffff', color: '#0f172a' });
            }
        }, 'json');
    });

    // ===== Quick Add Unit =====
    $('#btn-add-unit').click(function() { $('#new-unit-name').val(''); $('#new-unit-short').val(''); $('#addUnitModal').modal('show'); });
    $('#btn-save-unit').click(function() {
        const name = $('#new-unit-name').val().trim();
        const short = $('#new-unit-short').val().trim();
        if (!name || !short) return;
        $.post(BASE_URL + '/api/products.php?action=unit_save', { csrf_token: csrfToken, unit_name: name, short_name: short }, function(res) {
            if (res.status) {
                $('#addUnitModal').modal('hide');
                loadUnits(res.data.id);
                Swal.fire({ icon: 'success', title: 'Unit Added', timer: 1500, showConfirmButton: false, background: '#ffffff', color: '#0f172a' });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#ffffff', color: '#0f172a' });
            }
        }, 'json');
    });

    // Enter key in modals
    $('#new-category-name').on('keypress', function(e) { if (e.which === 13) { e.preventDefault(); $('#btn-save-category').click(); } });
    $('#new-brand-name').on('keypress', function(e) { if (e.which === 13) { e.preventDefault(); $('#btn-save-brand').click(); } });
    $('#new-unit-short').on('keypress', function(e) { if (e.which === 13) { e.preventDefault(); $('#btn-save-unit').click(); } });
});
</script>
