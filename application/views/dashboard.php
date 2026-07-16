<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Dashboard Home View Page
 */
?>
<section class="dashboard-shell">
    <div class="dashboard-hero mb-4">
        <div class="dashboard-hero-content">
            <div>
                <span class="dashboard-eyebrow">Live business command center</span>
                <h1>Today at a glance</h1>
                <p>Sales, cash flow, inventory pressure, and recent movement in one focused workspace.</p>
            </div>
            <div class="dashboard-hero-actions">
                <a href="<?php echo BASE_URL; ?>/billing/form" class="btn btn-light">
                    <i class="fa-solid fa-cash-register me-2"></i>New Invoice
                </a>
                <a href="<?php echo BASE_URL; ?>/billing/day_end" class="btn btn-outline-light">
                    <i class="fa-solid fa-chart-column me-2"></i>Day-End
                </a>
                <button type="button" class="btn btn-outline-light" onclick="openInventoryAlerts()">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>Inventory Alerts
                </button>
            </div>
        </div>

        <div class="dashboard-hero-metrics">
            <div>
                <span>Sales</span>
                <strong id="kpi-sales">₹0.00</strong>
            </div>
            <div>
                <span>Profit</span>
                <strong id="kpi-profit">₹0.00</strong>
            </div>
            <div>
                <span>Receivable</span>
                <strong id="count-receivable">₹0.00</strong>
            </div>
        </div>
    </div>

    <div id="doc-range-warnings" class="dashboard-alerts"></div>

    <div class="dashboard-kpi-grid mb-4">
        <div class="dashboard-kpi-card accent-indigo">
            <div class="dashboard-kpi-icon"><i class="fa-solid fa-cart-shopping"></i></div>
            <div>
                <span>Today's Sales</span>
                <strong id="kpi-sales-card">₹0.00</strong>
                <small>Revenue booked today</small>
            </div>
        </div>
        <div class="dashboard-kpi-card accent-emerald">
            <div class="dashboard-kpi-icon"><i class="fa-solid fa-basket-shopping"></i></div>
            <div>
                <span>Purchases</span>
                <strong id="kpi-purchases">₹0.00</strong>
                <small>Inventory cost today</small>
            </div>
        </div>
        <div class="dashboard-kpi-card accent-rose">
            <div class="dashboard-kpi-icon"><i class="fa-solid fa-receipt"></i></div>
            <div>
                <span>Expenses</span>
                <strong id="kpi-expenses">₹0.00</strong>
                <small>Operational spend</small>
            </div>
        </div>
        <div class="dashboard-kpi-card accent-amber">
            <div class="dashboard-kpi-icon"><i class="fa-solid fa-coins"></i></div>
            <div>
                <span>Net Profit</span>
                <strong id="kpi-profit-card">₹0.00</strong>
                <small>Sales minus costs</small>
            </div>
        </div>
    </div>

    <div class="dashboard-actions mb-4">
        <a href="<?php echo BASE_URL; ?>/billing/form" class="dashboard-action-card">
            <i class="fa-solid fa-file-invoice-dollar"></i>
            <span>Invoice</span>
        </a>
        <a href="<?php echo BASE_URL; ?>/quotations/form" class="dashboard-action-card">
            <i class="fa-solid fa-file-signature"></i>
            <span>Quotation</span>
        </a>
        <a href="<?php echo BASE_URL; ?>/products/form" class="dashboard-action-card">
            <i class="fa-solid fa-box"></i>
            <span>Product</span>
        </a>
        <a href="<?php echo BASE_URL; ?>/customers/index" class="dashboard-action-card">
            <i class="fa-solid fa-user-group"></i>
            <span>Customers</span>
        </a>
        <a href="<?php echo BASE_URL; ?>/reports/index" class="dashboard-action-card">
            <i class="fa-solid fa-file-waveform"></i>
            <span>Reports</span>
        </a>
    </div>

    <div class="dashboard-status-grid mb-4">
        <div class="dashboard-status-card">
            <span>Customers</span>
            <strong id="count-customers">0</strong>
            <i class="fa-solid fa-user-group text-indigo"></i>
        </div>
        <div class="dashboard-status-card">
            <span>Suppliers</span>
            <strong id="count-suppliers">0</strong>
            <i class="fa-solid fa-truck-ramp-box text-emerald"></i>
        </div>
        <div class="dashboard-status-card">
            <span>Products</span>
            <strong id="count-products">0</strong>
            <i class="fa-solid fa-box text-primary"></i>
        </div>
        <div class="dashboard-status-card danger">
            <span>Low Stock</span>
            <strong id="count-lowstock">0</strong>
            <i class="fa-solid fa-circle-exclamation text-rose"></i>
        </div>
        <div class="dashboard-status-card warning">
            <span>Overdue</span>
            <strong id="count-overdue">0</strong>
            <i class="fa-solid fa-clock text-warning"></i>
        </div>
        <div class="dashboard-status-card">
            <span>Held Bills</span>
            <strong id="count-held">0</strong>
            <i class="fa-solid fa-pause-circle text-indigo"></i>
        </div>
        <div class="dashboard-status-card warning">
            <span>Expiring Stock</span>
            <strong id="count-expiring">0</strong>
            <i class="fa-solid fa-triangle-exclamation text-warning"></i>
        </div>
        <div class="dashboard-status-card success">
            <span>Payment Watch</span>
            <strong id="count-receivable-card">₹0.00</strong>
            <i class="fa-solid fa-hand-holding-dollar text-emerald"></i>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-8">
            <div class="panel-card dashboard-panel h-100">
                <div class="panel-header">
                    <div>
                        <span class="panel-kicker">Last 7 days</span>
                        <h5 class="mb-0 text-dark"><i class="fa-solid fa-chart-line me-2 text-indigo"></i>Daily Sales</h5>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="dashboard-chart chart-tall">
                        <canvas id="chart-daily-sales"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="panel-card dashboard-panel h-100">
                <div class="panel-header">
                    <div>
                        <span class="panel-kicker">Last 30 days</span>
                        <h5 class="mb-0 text-dark"><i class="fa-solid fa-chart-pie me-2 text-emerald"></i>Expense Mix</h5>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="dashboard-chart chart-tall">
                        <canvas id="chart-expenses-cat"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="panel-card dashboard-panel">
                <div class="panel-header">
                    <div>
                        <span class="panel-kicker">Last 6 months</span>
                        <h5 class="mb-0 text-dark"><i class="fa-solid fa-chart-bar me-2 text-amber"></i>Monthly Revenue Trends</h5>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="dashboard-chart chart-wide">
                        <canvas id="chart-monthly-sales"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-6">
            <div class="panel-card dashboard-panel h-100">
                <div class="panel-header">
                    <div>
                        <span class="panel-kicker">This month</span>
                        <h5 class="mb-0 text-dark"><i class="fa-solid fa-fire me-2 text-rose"></i>Top Selling Products</h5>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="dashboard-chart chart-mid">
                        <canvas id="chart-top-products"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="panel-card dashboard-panel h-100">
                <div class="panel-header">
                    <div>
                        <span class="panel-kicker">This month</span>
                        <h5 class="mb-0 text-dark"><i class="fa-solid fa-credit-card me-2 text-indigo"></i>Payment Mode Distribution</h5>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="dashboard-chart chart-mid">
                        <canvas id="chart-payment-modes"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-6">
            <div class="panel-card dashboard-panel h-100">
                <div class="panel-header">
                    <div>
                        <span class="panel-kicker">Recent activity</span>
                        <h5 class="mb-0 text-dark"><i class="fa-solid fa-file-invoice me-2 text-indigo"></i>Recent Invoices</h5>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/billing/form" class="btn btn-sm btn-outline-secondary">New Invoice</a>
                </div>
                <div class="panel-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 dashboard-table" id="table-recent-invoices">
                            <thead>
                                <tr>
                                    <th>Invoice No</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-secondary">Loading details...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="panel-card dashboard-panel h-100">
                <div class="panel-header">
                    <div>
                        <span class="panel-kicker">Cash movement</span>
                        <h5 class="mb-0 text-dark"><i class="fa-solid fa-cash-register me-2 text-emerald"></i>Recent Payments Flow</h5>
                    </div>
                </div>
                <div class="panel-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 dashboard-table" id="table-recent-payments">
                            <thead>
                                <tr>
                                    <th>Party Name</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Method</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-secondary">Loading details...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Inventory Alerts Modal -->
<div class="modal fade" id="inventoryAlertModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0 bg-light-warning">
                <h5 class="modal-title text-dark">
                    <i class="fa-solid fa-bell-concierge text-warning me-2"></i>Inventory Intelligence
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <ul class="nav nav-tabs nav-justified px-3 pt-3 mb-0 border-bottom-0" id="invAlertTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-semibold" id="low-stock-tab" data-bs-toggle="tab" data-bs-target="#low-stock-pane" type="button" role="tab">Low Stock</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold" id="out-stock-tab" data-bs-toggle="tab" data-bs-target="#out-stock-pane" type="button" role="tab">Out of Stock</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold" id="expiry-tab" data-bs-toggle="tab" data-bs-target="#expiry-pane" type="button" role="tab">Expiry Soon</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold" id="fast-tab" data-bs-toggle="tab" data-bs-target="#fast-pane" type="button" role="tab">Fast Moving</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold" id="dead-tab" data-bs-toggle="tab" data-bs-target="#dead-pane" type="button" role="tab">Dead Stock</button>
                    </li>
                </ul>
                <div class="tab-content p-3" id="invAlertTabContent" style="background-color: #f8fafc; min-height: 300px;">
                    
                    <!-- Low Stock Pane -->
                    <div class="tab-pane fade show active" id="low-stock-pane" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover bg-white rounded shadow-sm">
                                <thead><tr><th>Product</th><th>Current Stock</th><th>Min Stock</th></tr></thead>
                                <tbody id="low-stock-list"><tr><td colspan="3" class="text-center">Loading...</td></tr></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Out of Stock Pane -->
                    <div class="tab-pane fade" id="out-stock-pane" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover bg-white rounded shadow-sm">
                                <thead><tr><th>Product</th><th>Status</th></tr></thead>
                                <tbody id="out-stock-list"><tr><td colspan="2" class="text-center">Loading...</td></tr></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Expiry Soon Pane -->
                    <div class="tab-pane fade" id="expiry-pane" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover bg-white rounded shadow-sm">
                                <thead><tr><th>Product</th><th>Batch</th><th>Expiry Date</th><th>Qty</th></tr></thead>
                                <tbody id="expiry-list"><tr><td colspan="4" class="text-center">Loading...</td></tr></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Fast Moving Pane -->
                    <div class="tab-pane fade" id="fast-pane" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover bg-white rounded shadow-sm">
                                <thead><tr><th>Product</th><th>Total Sold (30 Days)</th></tr></thead>
                                <tbody id="fast-list"><tr><td colspan="2" class="text-center">Loading...</td></tr></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Dead Stock Pane -->
                    <div class="tab-pane fade" id="dead-pane" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover bg-white rounded shadow-sm">
                                <thead><tr><th>Product</th><th>Current Stock</th><th>Last Sold</th></tr></thead>
                                <tbody id="dead-list"><tr><td colspan="3" class="text-center">Loading...</td></tr></tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function openInventoryAlerts() {
    var myModal = new bootstrap.Modal(document.getElementById('inventoryAlertModal'));
    myModal.show();
    
    // Fetch data
    $.ajax({
        url: BASE_URL + '/api/inventory_alerts.php',
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            if (res.status) {
                const data = res.data;
                
                // Low Stock
                let lsHtml = '';
                if (data.low_stock.length > 0) {
                    data.low_stock.forEach(item => {
                        lsHtml += `<tr><td>${item.product_name}</td><td class="text-warning fw-bold">${parseFloat(item.current_stock)}</td><td>${parseFloat(item.minimum_stock)}</td></tr>`;
                    });
                } else { lsHtml = '<tr><td colspan="3" class="text-center text-muted">No low stock items</td></tr>'; }
                $('#low-stock-list').html(lsHtml);

                // Out of Stock
                let osHtml = '';
                if (data.out_of_stock.length > 0) {
                    data.out_of_stock.forEach(item => {
                        osHtml += `<tr><td>${item.product_name}</td><td class="text-danger fw-bold">Out of Stock</td></tr>`;
                    });
                } else { osHtml = '<tr><td colspan="2" class="text-center text-muted">No out of stock items</td></tr>'; }
                $('#out-stock-list').html(osHtml);

                // Expiry Soon
                let expHtml = '';
                if (data.expiry_soon.length > 0) {
                    data.expiry_soon.forEach(item => {
                        expHtml += `<tr><td>${item.product_name}</td><td>${item.batch_no}</td><td class="text-danger fw-bold">${item.expiry_date}</td><td>${parseFloat(item.quantity)}</td></tr>`;
                    });
                } else { expHtml = '<tr><td colspan="4" class="text-center text-muted">No items expiring soon</td></tr>'; }
                $('#expiry-list').html(expHtml);

                // Fast Moving
                let fmHtml = '';
                if (data.fast_moving.length > 0) {
                    data.fast_moving.forEach(item => {
                        fmHtml += `<tr><td>${item.product_name}</td><td class="text-success fw-bold">${parseFloat(item.total_sold)}</td></tr>`;
                    });
                } else { fmHtml = '<tr><td colspan="2" class="text-center text-muted">Not enough data</td></tr>'; }
                $('#fast-list').html(fmHtml);

                // Dead Stock
                let dsHtml = '';
                if (data.dead_stock.length > 0) {
                    data.dead_stock.forEach(item => {
                        dsHtml += `<tr><td>${item.product_name}</td><td>${parseFloat(item.current_stock)}</td><td class="text-muted">> 90 days ago</td></tr>`;
                    });
                } else { dsHtml = '<tr><td colspan="3" class="text-center text-muted">No dead stock found</td></tr>'; }
                $('#dead-list').html(dsHtml);
            }
        },
        error: function() {
            $('#low-stock-list, #out-stock-list, #expiry-list, #fast-list, #dead-list').html('<tr><td colspan="4" class="text-center text-danger">Failed to load data</td></tr>');
        }
    });
}
</script>

<script src="<?php echo BASE_URL; ?>/assets/js/dashboard.js?v=<?php echo \App\Models\Helpers::assetVersion('/assets/js/dashboard.js'); ?>" defer></script>
