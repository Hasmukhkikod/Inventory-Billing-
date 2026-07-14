<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Reports & Analytics Hub View (Part 3)
 */
?>

<!-- Date Filter Ribbon -->
<div class="panel-card mb-4">
    <div class="panel-body py-3">
        <form id="report-filter-form" class="row g-3 align-items-center justify-content-between">
            <div class="col-md-5 d-flex flex-wrap align-items-center gap-2 row-gap-2">
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-nowrap">From Date</label>
                    <input type="date" class="form-control form-control-sm" name="start_date" id="rep-start-date" value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>">
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-nowrap">To Date</label>
                    <input type="date" class="form-control form-control-sm" name="end_date" id="rep-end-date" value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>
            
            <div class="col-md-4 text-end">
                <button type="submit" class="btn btn-primary btn-sm px-4">
                    <i class="fa-solid fa-filter me-1"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Dynamic Profit & Loss Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-icon blue"><i class="fa-solid fa-wallet"></i></div>
            <div class="stat-card-val text-indigo" id="pl-sales">₹0.00</div>
            <div class="stat-card-label">Total Revenue (Sales)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-icon amber"><i class="fa-solid fa-cart-shopping"></i></div>
            <div class="stat-card-val text-warning" id="pl-cogs">₹0.00</div>
            <div class="stat-card-label">Cost of Goods Sold (COGS)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-icon red"><i class="fa-solid fa-file-invoice-dollar"></i></div>
            <div class="stat-card-val text-rose" id="pl-expenses">₹0.00</div>
            <div class="stat-card-label">Total Expenses</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-icon green"><i class="fa-solid fa-money-bill-trend-up"></i></div>
            <div class="stat-card-val text-success" id="pl-netprofit">₹0.00</div>
            <div class="stat-card-label">Net Profit / Margin</div>
        </div>
    </div>
</div>

<!-- Tabbed Reports tables -->
<div class="panel-card text-dark">
    <div class="panel-header">
        <ul class="nav nav-tabs border-0" id="reportsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active text-indigo border-0 bg-transparent fw-semibold" id="sales-rep-tab" data-bs-toggle="tab" data-bs-target="#sales-pane" type="button" role="tab" aria-controls="sales-pane" aria-selected="true">
                    <i class="fa-solid fa-receipt me-2"></i>Sales Ledger
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link text-secondary border-0 bg-transparent fw-semibold" id="stock-rep-tab" data-bs-toggle="tab" data-bs-target="#stock-pane" type="button" role="tab" aria-controls="stock-pane" aria-selected="false">
                    <i class="fa-solid fa-boxes-stacked me-2"></i>Stock Valuation
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link text-secondary border-0 bg-transparent fw-semibold" id="exp-rep-tab" data-bs-toggle="tab" data-bs-target="#exp-pane" type="button" role="tab" aria-controls="exp-pane" aria-selected="false">
                    <i class="fa-solid fa-wallet me-2"></i>Expenses list
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link text-secondary border-0 bg-transparent fw-semibold" id="cust-rep-tab" data-bs-toggle="tab" data-bs-target="#cust-pane" type="button" role="tab" aria-controls="cust-pane" aria-selected="false">
                    <i class="fa-solid fa-users me-2"></i>Customer Receivables
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link text-secondary border-0 bg-transparent fw-semibold" id="supp-rep-tab" data-bs-toggle="tab" data-bs-target="#supp-pane" type="button" role="tab" aria-controls="supp-pane" aria-selected="false">
                    <i class="fa-solid fa-truck-field me-2"></i>Supplier Payables
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link text-secondary border-0 bg-transparent fw-semibold" id="gst-rep-tab" data-bs-toggle="tab" data-bs-target="#gst-pane" type="button" role="tab" aria-controls="gst-pane" aria-selected="false">
                    <i class="fa-solid fa-landmark me-2"></i>GST Report
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link text-secondary border-0 bg-transparent fw-semibold" id="overdue-rep-tab" data-bs-toggle="tab" data-bs-target="#overdue-pane" type="button" role="tab" aria-controls="overdue-pane" aria-selected="false">
                    <i class="fa-solid fa-clock me-2"></i>Overdue Invoices
                </button>
            </li>
        </ul>
    </div>
    
    <div class="panel-body text-dark">
        <div class="tab-content text-dark" id="reportsTabsContent">
            
            <!-- SALES REPORT PANE -->
            <div class="tab-pane fade show active text-dark" id="sales-pane" role="tabpanel" aria-labelledby="sales-rep-tab" tabindex="0">
                <div class="d-flex justify-content-between mb-3 text-dark">
                    <h6 class="text-dark mb-0">Invoice Revenue Records</h6>
                    <button class="btn btn-outline-secondary btn-sm btn-export" data-table="salesReportTable" title="Export CSV">
                        <i class="fa-solid fa-file-csv me-1 text-indigo"></i> Export CSV
                    </button>
                </div>
                <div class="table-responsive text-dark">
                    <table class="table table-hover align-middle w-100" id="salesReportTable">
                        <thead>
                            <tr>
                                <th>Invoice No</th>
                                <th>Customer</th>
                                <th>Invoice Date</th>
                                <th>Invoice Type</th>
                                <th>Total (₹)</th>
                                <th>Paid (₹)</th>
                                <th>Due (₹)</th>
                                <th>Payment Mode</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- STOCK VALUATION PANE -->
            <div class="tab-pane fade text-dark" id="stock-pane" role="tabpanel" aria-labelledby="stock-rep-tab" tabindex="0">
                <div class="d-flex justify-content-between mb-3 text-dark">
                    <h6 class="text-dark mb-0">Asset & Inventory Valuation</h6>
                    <button class="btn btn-outline-secondary btn-sm btn-export" data-table="stockReportTable" title="Export CSV">
                        <i class="fa-solid fa-file-csv me-1 text-indigo"></i> Export CSV
                    </button>
                </div>
                <div class="table-responsive text-dark">
                    <table class="table table-hover align-middle w-100" id="stockReportTable">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>SKU</th>
                                <th>Cost Price</th>
                                <th>Selling Price</th>
                                <th>Stock</th>
                                <th>Value at Cost</th>
                                <th>Value at Sell</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- EXPENSES SUMMARY PANE -->
            <div class="tab-pane fade text-dark" id="exp-pane" role="tabpanel" aria-labelledby="exp-rep-tab" tabindex="0">
                <div class="d-flex justify-content-between mb-3 text-dark">
                    <h6 class="text-dark mb-0">Business Outflows</h6>
                    <button class="btn btn-outline-secondary btn-sm btn-export" data-table="expensesReportTable" title="Export CSV">
                        <i class="fa-solid fa-file-csv me-1 text-indigo"></i> Export CSV
                    </button>
                </div>
                <div class="table-responsive text-dark">
                    <table class="table table-hover align-middle w-100" id="expensesReportTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Method</th>
                                <th>Amount (₹)</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- CUSTOMERS RECEIVABLE PANE -->
            <div class="tab-pane fade text-dark" id="cust-pane" role="tabpanel" aria-labelledby="cust-rep-tab" tabindex="0">
                <div class="d-flex justify-content-between mb-3 text-dark">
                    <h6 class="text-dark mb-0">Customer CRM Ledger Balances</h6>
                    <button class="btn btn-outline-secondary btn-sm btn-export" data-table="customerReportTable" title="Export CSV">
                        <i class="fa-solid fa-file-csv me-1 text-indigo"></i> Export CSV
                    </button>
                </div>
                <div class="table-responsive text-dark">
                    <table class="table table-hover align-middle w-100" id="customerReportTable">
                        <thead>
                            <tr>
                                <th>Customer Name</th>
                                <th>Mobile</th>
                                <th>Email</th>
                                <th>Outstanding Receivable</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- SUPPLIERS PAYABLE PANE -->
            <div class="tab-pane fade text-dark" id="supp-pane" role="tabpanel" aria-labelledby="supp-rep-tab" tabindex="0">
                <div class="d-flex justify-content-between mb-3 text-dark">
                    <h6 class="text-dark mb-0">Supplier Payables Book</h6>
                    <button class="btn btn-outline-secondary btn-sm btn-export" data-table="supplierReportTable" title="Export CSV">
                        <i class="fa-solid fa-file-csv me-1 text-indigo"></i> Export CSV
                    </button>
                </div>
                <div class="table-responsive text-dark">
                    <table class="table table-hover align-middle w-100" id="supplierReportTable">
                        <thead>
                            <tr>
                                <th>Supplier Name</th>
                                <th>Contact Person</th>
                                <th>Mobile</th>
                                <th>Outstanding Payable</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- GST REPORT PANE -->
            <div class="tab-pane fade text-dark" id="gst-pane" role="tabpanel" aria-labelledby="gst-rep-tab" tabindex="0">
                <div class="d-flex justify-content-between mb-3 text-dark">
                    <h6 class="text-dark mb-0">GST Tax Breakdown (CGST / SGST / IGST)</h6>
                    <button class="btn btn-outline-secondary btn-sm btn-export" data-table="gstReportTable" title="Export CSV">
                        <i class="fa-solid fa-file-csv me-1 text-indigo"></i> Export CSV
                    </button>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-3"><div class="stat-card p-3"><span class="text-secondary small d-block">Total CGST</span><strong class="fs-5 text-indigo" id="gst-total-cgst">₹0.00</strong></div></div>
                    <div class="col-md-3"><div class="stat-card p-3"><span class="text-secondary small d-block">Total SGST</span><strong class="fs-5 text-emerald" id="gst-total-sgst">₹0.00</strong></div></div>
                    <div class="col-md-3"><div class="stat-card p-3"><span class="text-secondary small d-block">Total IGST</span><strong class="fs-5 text-warning" id="gst-total-igst">₹0.00</strong></div></div>
                    <div class="col-md-3"><div class="stat-card p-3"><span class="text-secondary small d-block">Total Tax</span><strong class="fs-5 text-rose" id="gst-total-tax">₹0.00</strong></div></div>
                </div>
                <div class="table-responsive text-dark">
                    <table class="table table-hover align-middle w-100" id="gstReportTable">
                        <thead><tr><th>Invoice No</th><th>Date</th><th>Customer</th><th class="text-end">Taxable (₹)</th><th class="text-end">CGST (₹)</th><th class="text-end">SGST (₹)</th><th class="text-end">IGST (₹)</th><th class="text-end">Total Tax (₹)</th></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- OVERDUE INVOICES PANE -->
            <div class="tab-pane fade text-dark" id="overdue-pane" role="tabpanel" aria-labelledby="overdue-rep-tab" tabindex="0">
                <div class="d-flex justify-content-between mb-3 text-dark">
                    <h6 class="text-dark mb-0">Overdue Invoices (Past Due Date)</h6>
                    <button class="btn btn-outline-secondary btn-sm btn-export" data-table="overdueReportTable" title="Export CSV">
                        <i class="fa-solid fa-file-csv me-1 text-indigo"></i> Export CSV
                    </button>
                </div>
                <div class="table-responsive text-dark">
                    <table class="table table-hover align-middle w-100" id="overdueReportTable">
                        <thead><tr><th>Invoice No</th><th>Customer</th><th>Invoice Date</th><th>Due Date</th><th>Days Overdue</th><th class="text-end">Total (₹)</th><th class="text-end">Due (₹)</th></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let salesTable, stockTable, expensesTable, customerTable, supplierTable, gstTable, overdueTable;

    function fetchSummary() {
        const start = $("#rep-start-date").val();
        const end = $("#rep-end-date").val();

        $.ajax({
            url: BASE_URL + `/api/reports.php?action=summary&start_date=${start}&end_date=${end}`,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    const d = res.data;
                    $("#pl-sales").text('₹' + parseFloat(d.total_sales).toFixed(2));
                    $("#pl-cogs").text('₹' + parseFloat(d.cogs).toFixed(2));
                    $("#pl-expenses").text('₹' + parseFloat(d.total_expenses).toFixed(2));

                    const margin = d.net_profit;
                    const marginEl = $("#pl-netprofit");
                    marginEl.text('₹' + parseFloat(margin).toFixed(2));

                    if (margin >= 0) {
                        marginEl.removeClass('text-rose').addClass('text-success');
                    } else {
                        marginEl.removeClass('text-success').addClass('text-rose');
                    }
                }
            }
        });
    }

    // Used by the "Apply Filters" button - re-fetches the summary and reloads the
    // date-filtered tables. Not called on initial page load: each table already
    // fetches its own first page of data via its own `ajax` config at construction,
    // so reloading them again here too would race that initial request and made
    // the loading indicator flicker/vanish before the real data arrived.
    function applyFilters() {
        const start = $("#rep-start-date").val();
        const end = $("#rep-end-date").val();

        fetchSummary();

        if (salesTable) salesTable.ajax.url(BASE_URL + `/api/reports.php?action=sales&start_date=${start}&end_date=${end}`).load();
        if (expensesTable) expensesTable.ajax.url(BASE_URL + `/api/reports.php?action=expenses&start_date=${start}&end_date=${end}`).load();
        if (stockTable) stockTable.ajax.reload();
        if (customerTable) customerTable.ajax.reload();
        if (supplierTable) supplierTable.ajax.reload();
    }

    // Initialize tables
    const start = $("#rep-start-date").val();
    const end = $("#rep-end-date").val();

    salesTable = $('#salesReportTable').DataTable({
        ajax: {
            url: BASE_URL + `/api/reports.php?action=sales&start_date=${start}&end_date=${end}`,
            dataSrc: 'data'
        },
        columns: [
            { 
                data: 'invoice_no', 
                className: 'fw-semibold text-dark',
                render: function(data, type, row) {
                    return `<a href="${BASE_URL}/billing/view?id=${row.id}" class="text-indigo text-decoration-none">${data}</a>`;
                }
            },
            { data: 'customer_name', defaultContent: 'Walk-in Customer' },
            { 
                data: 'invoice_date',
                render: function(data) {
                    if(!data) return '-';
                    const d = new Date(data);
                    return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
                }
            },
            { data: 'invoice_type' },
            { data: 'grand_total', render: d => '₹' + parseFloat(d).toFixed(2), className: 'text-end text-dark font-monospace' },
            { data: 'paid_amount', render: d => '₹' + parseFloat(d).toFixed(2), className: 'text-end text-emerald font-monospace' },
            { data: 'due_amount', render: d => '₹' + parseFloat(d).toFixed(2), className: 'text-end text-rose font-monospace' },
            { data: 'payment_method' }
        ]
    });

    stockTable = $('#stockReportTable').DataTable({
        ajax: {
            url: BASE_URL + '/api/reports.php?action=stock',
            dataSrc: 'data'
        },
        columns: [
            { 
                data: 'product_name', 
                className: 'fw-semibold text-dark',
                render: function(data, type, row) {
                    return `<a href="${BASE_URL}/products/view?id=${row.id}" class="text-indigo text-decoration-none">${data}</a>`;
                }
            },
            { data: 'sku' },
            { data: 'cost_price', render: d => '₹' + parseFloat(d).toFixed(2), className: 'text-end font-monospace' },
            { data: 'selling_price', render: d => '₹' + parseFloat(d).toFixed(2), className: 'text-end font-monospace' },
            { 
                data: 'current_stock', 
                render: (d, t, r) => {
                    const min = parseFloat(r.minimum_stock);
                    const curr = parseFloat(d);
                    if (curr <= min) {
                        return `<span class="badge bg-light-danger fw-bold">${curr} (Low stock)</span>`;
                    }
                    return `<span class="badge bg-light-success fw-bold">${curr}</span>`;
                } 
            },
            { data: 'valuation_cost', render: d => '₹' + parseFloat(d).toFixed(2), className: 'text-end text-indigo fw-bold font-monospace' },
            { data: 'valuation_selling', render: d => '₹' + parseFloat(d).toFixed(2), className: 'text-end text-dark fw-bold font-monospace' }
        ]
    });

    expensesTable = $('#expensesReportTable').DataTable({
        ajax: {
            url: BASE_URL + `/api/reports.php?action=expenses&start_date=${start}&end_date=${end}`,
            dataSrc: 'data'
        },
        columns: [
            { 
                data: 'expense_date',
                render: function(data) {
                    if(!data) return '-';
                    const d = new Date(data);
                    return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
                }
            },
            { data: 'category_name', className: 'text-dark fw-semibold' },
            { data: 'description', defaultContent: '-' },
            { data: 'payment_method' },
            { data: 'amount', render: d => '₹' + parseFloat(d).toFixed(2), className: 'text-end text-rose fw-bold font-monospace' }
        ]
    });

    customerTable = $('#customerReportTable').DataTable({
        ajax: {
            url: BASE_URL + '/api/reports.php?action=customers',
            dataSrc: 'data'
        },
        columns: [
            { 
                data: 'customer_name', 
                className: 'fw-semibold text-dark',
                render: function(data, type, row) {
                    return `<a href="${BASE_URL}/customers/view?id=${row.id}" class="text-indigo text-decoration-none">${data}</a>`;
                }
            },
            { data: 'mobile' },
            { data: 'email', defaultContent: '-' },
            { data: 'credit_balance', render: d => '₹' + parseFloat(d).toFixed(2), className: 'text-end text-rose fw-bold font-monospace' }
        ]
    });

    supplierTable = $('#supplierReportTable').DataTable({
        ajax: {
            url: BASE_URL + '/api/reports.php?action=suppliers',
            dataSrc: 'data'
        },
        columns: [
            { 
                data: 'supplier_name', 
                className: 'fw-semibold text-dark',
                render: function(data, type, row) {
                    return `<a href="${BASE_URL}/suppliers/view?id=${row.id}" class="text-indigo text-decoration-none">${data}</a>`;
                }
            },
            { data: 'contact_person', defaultContent: '-' },
            { data: 'mobile' },
            { data: 'outstanding_balance', render: d => '₹' + parseFloat(d).toFixed(2), className: 'text-end text-rose fw-bold font-monospace' }
        ]
    });

    // GST Report Table
    gstTable = $('#gstReportTable').DataTable({
        ajax: { url: BASE_URL + `/api/reports.php?action=gst&start_date=${start}&end_date=${end}`, dataSrc: 'data' },
        columns: [
            { data: 'invoice_no', className: 'fw-semibold', render: (d, t, r) => `<a href="${BASE_URL}/billing/view?id=${r.id}" class="text-indigo text-decoration-none">${d}</a>` },
            { data: 'invoice_date', render: d => { if(!d) return '-'; return new Date(d).toLocaleDateString('en-IN', {day:'2-digit',month:'short',year:'numeric'}); } },
            { data: 'customer_name', defaultContent: 'Walk-in' },
            { data: 'subtotal', render: d => '₹' + parseFloat(d).toFixed(2), className: 'text-end font-monospace' },
            { data: 'cgst_amount', render: d => '₹' + parseFloat(d).toFixed(2), className: 'text-end text-indigo font-monospace' },
            { data: 'sgst_amount', render: d => '₹' + parseFloat(d).toFixed(2), className: 'text-end text-emerald font-monospace' },
            { data: 'igst_amount', render: d => '₹' + parseFloat(d).toFixed(2), className: 'text-end text-warning font-monospace' },
            { data: 'gst_amount', render: d => '₹' + parseFloat(d).toFixed(2), className: 'text-end text-rose fw-bold font-monospace' }
        ],
        footerCallback: function() {
            const api = this.api();
            let cgst = 0, sgst = 0, igst = 0, tax = 0;
            api.rows().data().each(function(r) { cgst += parseFloat(r.cgst_amount||0); sgst += parseFloat(r.sgst_amount||0); igst += parseFloat(r.igst_amount||0); tax += parseFloat(r.gst_amount||0); });
            $('#gst-total-cgst').text('₹' + cgst.toFixed(2));
            $('#gst-total-sgst').text('₹' + sgst.toFixed(2));
            $('#gst-total-igst').text('₹' + igst.toFixed(2));
            $('#gst-total-tax').text('₹' + tax.toFixed(2));
        }
    });

    // Overdue Invoices Table
    overdueTable = $('#overdueReportTable').DataTable({
        ajax: { url: BASE_URL + '/api/reports.php?action=overdue', dataSrc: 'data' },
        columns: [
            { data: 'invoice_no', className: 'fw-semibold', render: (d, t, r) => `<a href="${BASE_URL}/billing/view?id=${r.id}" class="text-indigo text-decoration-none">${d}</a>` },
            { data: 'customer_name', defaultContent: 'Walk-in' },
            { data: 'invoice_date', render: d => d ? new Date(d).toLocaleDateString('en-IN', {day:'2-digit',month:'short',year:'numeric'}) : '-' },
            { data: 'due_date', render: d => d ? new Date(d).toLocaleDateString('en-IN', {day:'2-digit',month:'short',year:'numeric'}) : '-', className: 'text-rose fw-bold' },
            { data: 'days_overdue', className: 'text-center text-rose fw-bold' },
            { data: 'grand_total', render: d => '₹' + parseFloat(d).toFixed(2), className: 'text-end font-monospace' },
            { data: 'due_amount', render: d => '₹' + parseFloat(d).toFixed(2), className: 'text-end text-rose fw-bold font-monospace' }
        ]
    });

    // Initial load: tables already fetched their first page via their own `ajax`
    // config above - only the summary cards need an explicit initial fetch.
    fetchSummary();

    // Trigger on form submit
    $("#report-filter-form").submit(function(e) {
        e.preventDefault();
        applyFilters();
    });

    // CSV Exporter Handler
    $(".btn-export").click(function() {
        const tableId = $(this).data('table');
        exportTableToCSV(tableId);
    });

    function exportTableToCSV(tableId) {
        const csv = [];
        const rows = document.querySelectorAll("#" + tableId + " tr");
        
        for (let i = 0; i < rows.length; i++) {
            const row = [], cols = rows[i].querySelectorAll("td, th");
            
            for (let j = 0; j < cols.length; j++) {
                let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, "").replace(/(\s\s)/gm, " ").trim();
                data = data.replace(/"/g, '""'); // Escaping quotes
                row.push('"' + data + '"');
            }
            csv.push(row.join(","));
        }

        const csvString = csv.join("\n");
        const filename = tableId + "_" + new Date().toISOString().slice(0,10) + ".csv";
        const link = document.createElement("a");
        link.setAttribute("href", "data:text/csv;charset=utf-8," + encodeURIComponent(csvString));
        link.setAttribute("download", filename);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
});
</script>
