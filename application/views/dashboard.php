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

<script src="<?php echo BASE_URL; ?>/assets/js/dashboard.js?v=<?php echo \App\Models\Helpers::assetVersion('/assets/js/dashboard.js'); ?>" defer></script>
