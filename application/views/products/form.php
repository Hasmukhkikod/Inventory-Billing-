<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Product Form Page (Add / Edit)
 */
$isEdit = !empty($product);
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
                
                <div class="col-md-4">
                    <label class="form-label">Barcode (EAN/UPC)</label>
                    <input type="text" class="form-control" name="barcode" id="prod-barcode" placeholder="Scan or enter barcode" value="<?php echo $isEdit ? \App\Models\Helpers::sanitize($product['barcode']) : ''; ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">HSN Code</label>
                    <input type="text" class="form-control" name="hsn_code" id="prod-hsn" placeholder="HSN/SAC code" value="<?php echo $isEdit ? \App\Models\Helpers::sanitize($product['hsn_code'] ?? '') : ''; ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Category</label>
                    <select class="form-select" name="category_id" id="prod-category">
                        <option value="">-- Choose Category --</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Brand</label>
                    <select class="form-select" name="brand_id" id="prod-brand">
                        <option value="">-- Choose Brand --</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Cost Price *</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="0.01" class="form-control" name="cost_price" id="prod-cost" required value="<?php echo $isEdit ? (float)$product['cost_price'] : '0.00'; ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Selling Price *</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="0.01" class="form-control" name="selling_price" id="prod-selling" required value="<?php echo $isEdit ? (float)$product['selling_price'] : '0.00'; ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">GST Tax Percentage (%)</label>
                    <select class="form-select" name="gst_percentage" id="prod-gst">
                        <option value="0" <?php echo ($isEdit && $product['gst_percentage'] == 0) ? 'selected' : ''; ?>>0% (Exempt)</option>
                        <option value="5" <?php echo ($isEdit && $product['gst_percentage'] == 5) ? 'selected' : ''; ?>>5% GST</option>
                        <option value="12" <?php echo ($isEdit && $product['gst_percentage'] == 12) ? 'selected' : ''; ?>>12% GST</option>
                        <option value="18" <?php echo (!$isEdit || $product['gst_percentage'] == 18) ? 'selected' : ''; ?>>18% GST</option>
                        <option value="28" <?php echo ($isEdit && $product['gst_percentage'] == 28) ? 'selected' : ''; ?>>28% GST</option>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Measurement Unit</label>
                    <select class="form-select" name="unit_id" id="prod-unit">
                        <option value="">-- Choose Unit --</option>
                    </select>
                </div>
                
                <?php if (!$isEdit): ?>
                <div class="col-md-4" id="initial-stock-col">
                    <label class="form-label">Initial Stock Qty</label>
                    <input type="number" step="0.01" class="form-control" name="opening_stock" id="prod-stock" value="0">
                </div>
                <?php endif; ?>
                
                <div class="col-md-4">
                    <label class="form-label">Minimum Stock Alert</label>
                    <input type="number" step="0.01" class="form-control" name="minimum_stock" id="prod-min-stock" value="<?php echo $isEdit ? (float)$product['minimum_stock'] : '5'; ?>">
                </div>
                
                <div class="col-md-12">
                    <label class="form-label">Product Image</label>
                    <input type="file" class="form-control" name="product_image" id="prod-image">
                    <div class="mt-2 text-muted small">JPG, PNG, WEBP (Max: 2MB)</div>
                    <?php if ($isEdit && !empty($product['image'])): ?>
                    <div class="mt-2" id="prod-image-preview-wrapper">
                        <span class="d-block small text-secondary mb-1">Current Image:</span>
                        <img src="<?php echo BASE_URL . '/' . \App\Models\Helpers::sanitize($product['image']); ?>" id="prod-image-preview" class="rounded border border-secondary" style="height: 80px;">
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="mt-4 pt-3 border-top border-secondary text-end">
                <a href="<?php echo BASE_URL; ?>/products/index.php" class="btn btn-outline-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-circle-check me-1"></i><?php echo $isEdit ? 'Update Product Details' : 'Save Product'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    const selectedCategory = '<?php echo $isEdit ? (int)$product['category_id'] : ''; ?>';
    const selectedBrand = '<?php echo $isEdit ? (int)$product['brand_id'] : ''; ?>';
    const selectedUnit = '<?php echo $isEdit ? (int)$product['unit_id'] : ''; ?>';

    loadMetaOptions();

    $("#productForm").submit(function(e) {
        e.preventDefault();
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
                    Swal.fire({ 
                        icon: 'success', 
                        title: 'Saved', 
                        text: res.message, 
                        background: '#ffffff', 
                        color: '#0f172a' 
                    }).then(() => {
                        window.location.href = BASE_URL + '/products/index.php';
                    });
                } else {
                    Swal.fire({ 
                        icon: 'error', 
                        title: 'Error', 
                        text: res.message, 
                        background: '#ffffff', 
                        color: '#0f172a' 
                    });
                }
            }
        });
    });

    function loadMetaOptions() {
        $.ajax({
            url: BASE_URL + '/api/products.php?action=categories_list',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                const sel = $("#prod-category");
                if (res.status) {
                    res.data.forEach(c => {
                        const selAttr = c.id == selectedCategory ? 'selected' : '';
                        sel.append(`<option value="${c.id}" ${selAttr}>${c.category_name}</option>`);
                    });
                }
            }
        });

        $.ajax({
            url: BASE_URL + '/api/products.php?action=brands_list',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                const sel = $("#prod-brand");
                if (res.status) {
                    res.data.forEach(b => {
                        const selAttr = b.id == selectedBrand ? 'selected' : '';
                        sel.append(`<option value="${b.id}" ${selAttr}>${b.brand_name}</option>`);
                    });
                }
            }
        });

        $.ajax({
            url: BASE_URL + '/api/products.php?action=units_list',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                const sel = $("#prod-unit");
                if (res.status) {
                    res.data.forEach(u => {
                        const selAttr = u.id == selectedUnit ? 'selected' : '';
                        sel.append(`<option value="${u.id}" ${selAttr}>${u.unit_name} (${u.short_name})</option>`);
                    });
                }
            }
        });
    }
});
</script>
