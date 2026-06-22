<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Dashboard Home View Page
 */
?>
<!-- Dashboard KPI Cards -->
<div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-4 mb-4">
    <!-- Today's Sales -->
    <div class="col">
        <div class="stat-card">
            <div class="stat-card-icon blue">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
            <div class="stat-card-val" id="kpi-sales">₹0.00</div>
            <div class="stat-card-label">Today's Sales</div>
        </div>
    </div>
    
    <!-- Today's Purchases -->
    <div class="col">
        <div class="stat-card">
            <div class="stat-card-icon green">
                <i class="fa-solid fa-basket-shopping"></i>
            </div>
            <div class="stat-card-val" id="kpi-purchases">₹0.00</div>
            <div class="stat-card-label">Today's Purchases</div>
        </div>
    </div>
    
    <!-- Today's Expenses -->
    <div class="col">
        <div class="stat-card">
            <div class="stat-card-icon red">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div class="stat-card-val" id="kpi-expenses">₹0.00</div>
            <div class="stat-card-label">Today's Expenses</div>
        </div>
    </div>
    
    <!-- Today's Profit -->
    <div class="col">
        <div class="stat-card">
            <div class="stat-card-icon amber">
                <i class="fa-solid fa-coins"></i>
            </div>
            <div class="stat-card-val" id="kpi-profit">₹0.00</div>
            <div class="stat-card-label">Today's Profit</div>
        </div>
    </div>
</div>

<!-- Document Range Warnings -->
<div id="doc-range-warnings"></div>

<!-- Quick Actions -->
<div class="d-flex gap-2 mb-4 flex-wrap">
    <a href="<?php echo BASE_URL; ?>/billing/form.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-cash-register me-1"></i>New Invoice</a>
    <a href="<?php echo BASE_URL; ?>/quotations/form.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-file-signature me-1"></i>New Quotation</a>
    <a href="<?php echo BASE_URL; ?>/billing/day_end.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-chart-column me-1"></i>Day-End Report</a>
    <a href="<?php echo BASE_URL; ?>/products/form.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-box me-1"></i>Add Product</a>
</div>

<!-- Secondary indicators -->
<div class="row row-cols-2 row-cols-md-4 g-3 mb-4">
    <div class="col">
        <div class="stat-card p-3 rounded-3 d-flex align-items-center justify-content-between">
            <div>
                <span class="text-secondary small d-block">Customers</span>
                <strong class="fs-5 text-dark" id="count-customers">0</strong>
            </div>
            <i class="fa-solid fa-user-group text-indigo fs-4"></i>
        </div>
    </div>
    <div class="col">
        <div class="stat-card p-3 rounded-3 d-flex align-items-center justify-content-between">
            <div>
                <span class="text-secondary small d-block">Suppliers</span>
                <strong class="fs-5 text-dark" id="count-suppliers">0</strong>
            </div>
            <i class="fa-solid fa-truck-ramp-box text-emerald fs-4"></i>
        </div>
    </div>
    <div class="col">
        <div class="stat-card p-3 rounded-3 d-flex align-items-center justify-content-between">
            <div>
                <span class="text-secondary small d-block">Products</span>
                <strong class="fs-5 text-dark" id="count-products">0</strong>
            </div>
            <i class="fa-solid fa-box text-primary fs-4"></i>
        </div>
    </div>
    <div class="col">
        <div class="bg-secondary border border-secondary p-3 rounded-3 d-flex align-items-center justify-content-between bg-light-danger border-danger">
            <div>
                <span class="text-rose small d-block">Low Stock Items</span>
                <strong class="fs-5 text-rose" id="count-lowstock">0</strong>
            </div>
            <i class="fa-solid fa-circle-exclamation text-rose fs-4"></i>
        </div>
    </div>
</div>

<!-- Additional KPIs -->
<div class="row row-cols-2 row-cols-md-4 g-3 mb-4">
    <div class="col">
        <div class="stat-card bg-light-warning border-0 p-3 rounded-3 d-flex align-items-center justify-content-between">
            <div><span class="text-warning small d-block">Overdue Invoices</span><strong class="fs-5 text-warning" id="count-overdue">0</strong></div>
            <i class="fa-solid fa-clock text-warning fs-4"></i>
        </div>
    </div>
    <div class="col">
        <div class="stat-card bg-light-primary border-0 p-3 rounded-3 d-flex align-items-center justify-content-between">
            <div><span class="text-indigo small d-block">Held Bills</span><strong class="fs-5 text-indigo" id="count-held">0</strong></div>
            <i class="fa-solid fa-pause-circle text-indigo fs-4"></i>
        </div>
    </div>
    <div class="col">
        <div class="stat-card p-3 rounded-3 d-flex align-items-center justify-content-between">
            <div><span class="text-secondary small d-block">Outstanding Receivable</span><strong class="fs-5 text-rose" id="count-receivable">₹0</strong></div>
            <i class="fa-solid fa-hand-holding-dollar text-rose fs-4"></i>
        </div>
    </div>
    <div class="col">
        <div class="stat-card p-3 rounded-3 d-flex align-items-center justify-content-between">
            <div><span class="text-secondary small d-block">Expiring Stock</span><strong class="fs-5 text-warning" id="count-expiring">0</strong></div>
            <i class="fa-solid fa-triangle-exclamation text-warning fs-4"></i>
        </div>
    </div>
</div>

<!-- Charts Panel Row -->
<div class="row g-4 mb-4">
    <!-- Daily line revenue -->
    <div class="col-12 col-lg-8">
        <div class="panel-card">
            <div class="panel-header">
                <h5 class="mb-0 text-dark"><i class="fa-solid fa-chart-line me-2 text-indigo"></i>Daily Sales (Last 7 Days)</h5>
            </div>
            <div class="panel-body">
                <div style="height: 300px; position: relative;">
                    <canvas id="chart-daily-sales"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Expense distribution -->
    <div class="col-12 col-lg-4">
        <div class="panel-card">
            <div class="panel-header">
                <h5 class="mb-0 text-dark"><i class="fa-solid fa-chart-pie me-2 text-emerald"></i>Expenses by Category (Last 30 days)</h5>
            </div>
            <div class="panel-body">
                <div style="height: 300px; position: relative;" class="d-flex align-items-center justify-content-center">
                    <canvas id="chart-expenses-cat"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Monthly Sales Bar Chart -->
    <div class="col-12">
        <div class="panel-card">
            <div class="panel-header">
                <h5 class="mb-0 text-dark"><i class="fa-solid fa-chart-bar me-2 text-amber"></i>Monthly Revenue Trends (Last 6 Months)</h5>
            </div>
            <div class="panel-body">
                <div style="height: 250px; position: relative;">
                    <canvas id="chart-monthly-sales"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Analytics Row -->
<div class="row g-4 mb-4">
    <div class="col-12 col-lg-6">
        <div class="panel-card">
            <div class="panel-header"><h5 class="mb-0 text-dark"><i class="fa-solid fa-fire me-2 text-rose"></i>Top 5 Selling Products (This Month)</h5></div>
            <div class="panel-body"><div style="height:250px;position:relative;"><canvas id="chart-top-products"></canvas></div></div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="panel-card">
            <div class="panel-header"><h5 class="mb-0 text-dark"><i class="fa-solid fa-credit-card me-2 text-indigo"></i>Payment Mode Distribution (This Month)</h5></div>
            <div class="panel-body"><div style="height:250px;position:relative;" class="d-flex align-items-center justify-content-center"><canvas id="chart-payment-modes"></canvas></div></div>
        </div>
    </div>
</div>

<!-- Transactions log grids -->
<div class="row g-4">
    <!-- Recent invoices -->
    <div class="col-12 col-xl-6">
        <div class="panel-card h-100">
            <div class="panel-header">
                <h5 class="mb-0 text-dark"><i class="fa-solid fa-file-invoice me-2 text-indigo"></i>Recent Invoices</h5>
                <a href="<?php echo BASE_URL; ?>/billing/form.php" class="btn btn-sm btn-outline-secondary py-1 px-2.5 small">New Invoice</a>
            </div>
            <div class="panel-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="table-recent-invoices">
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

    <!-- Recent Payments -->
    <div class="col-12 col-xl-6">
        <div class="panel-card h-100">
            <div class="panel-header">
                <h5 class="mb-0 text-dark"><i class="fa-solid fa-cash-register me-2 text-emerald"></i>Recent Payments Flow</h5>
            </div>
            <div class="panel-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="table-recent-payments">
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

<!-- Dashboard chart loading controller -->
<script src="assets/js/dashboard.js" defer></script>
