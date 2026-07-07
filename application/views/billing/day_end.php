<?php
/**
 * IIMS v2.0 - Day-End Cash Register Report
 */
?>
<div class="panel-card mb-4">
    <div class="panel-header">
        <h5 class="mb-0 text-dark"><i class="fa-solid fa-cash-register me-2 text-indigo"></i>Day-End Cash Register Report</h5>
        <div class="d-flex gap-2 align-items-center">
            <input type="date" class="form-control form-control-sm" id="day-end-date" value="<?php echo date('Y-m-d'); ?>" style="width:160px;">
            <button class="btn btn-primary btn-sm" id="btn-load-day-end"><i class="fa-solid fa-sync me-1"></i>Load</button>
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print();"><i class="fa-solid fa-print me-1"></i>Print</button>
            <button class="btn btn-danger btn-sm" onclick="downloadDayEndPDF();"><i class="fa-solid fa-file-pdf me-1"></i>PDF</button>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="stat-card"><div class="stat-card-icon blue"><i class="fa-solid fa-file-invoice"></i></div><div class="stat-card-val text-indigo" id="de-invoice-count">0</div><div class="stat-card-label">Total Invoices</div></div></div>
    <div class="col-md-3"><div class="stat-card"><div class="stat-card-icon green"><i class="fa-solid fa-wallet"></i></div><div class="stat-card-val text-emerald" id="de-total-sales">₹0</div><div class="stat-card-label">Total Sales</div></div></div>
    <div class="col-md-3"><div class="stat-card"><div class="stat-card-icon amber"><i class="fa-solid fa-hand-holding-dollar"></i></div><div class="stat-card-val text-warning" id="de-total-received">₹0</div><div class="stat-card-label">Amount Received</div></div></div>
    <div class="col-md-3"><div class="stat-card"><div class="stat-card-icon red"><i class="fa-solid fa-coins"></i></div><div class="stat-card-val text-emerald" id="de-net-cash">₹0</div><div class="stat-card-label">Net Cash Position</div></div></div>
</div>

<div class="row g-4 mb-4">
    <!-- Payment Mode Breakdown -->
    <div class="col-md-6">
        <div class="panel-card h-100">
            <div class="panel-header"><h6 class="mb-0 text-dark"><i class="fa-solid fa-credit-card me-2 text-indigo"></i>Payment Mode Breakdown</h6></div>
            <div class="panel-body p-0">
                <table class="table table-hover mb-0"><thead><tr><th>Method</th><th>Bills</th><th class="text-end">Total (₹)</th><th class="text-end">Received (₹)</th></tr></thead>
                <tbody id="de-methods-table"><tr><td colspan="4" class="text-center py-3 text-secondary">Loading...</td></tr></tbody></table>
            </div>
        </div>
    </div>
    <!-- Split Payment Details -->
    <div class="col-md-6">
        <div class="panel-card h-100">
            <div class="panel-header"><h6 class="mb-0 text-dark"><i class="fa-solid fa-arrows-split-up-and-left me-2 text-emerald"></i>Split Payment Summary</h6></div>
            <div class="panel-body p-0">
                <table class="table table-hover mb-0"><thead><tr><th>Method</th><th class="text-end">Amount (₹)</th></tr></thead>
                <tbody id="de-split-table"><tr><td colspan="2" class="text-center py-3 text-secondary">Loading...</td></tr></tbody></table>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Top Products -->
    <div class="col-md-6">
        <div class="panel-card h-100">
            <div class="panel-header"><h6 class="mb-0 text-dark"><i class="fa-solid fa-fire me-2 text-warning"></i>Top 5 Products Sold</h6></div>
            <div class="panel-body p-0">
                <table class="table table-hover mb-0"><thead><tr><th>Product</th><th class="text-center">Qty</th><th class="text-end">Revenue (₹)</th></tr></thead>
                <tbody id="de-top-products"><tr><td colspan="3" class="text-center py-3 text-secondary">Loading...</td></tr></tbody></table>
            </div>
        </div>
    </div>
    <!-- Cashier-wise -->
    <div class="col-md-6">
        <div class="panel-card h-100">
            <div class="panel-header"><h6 class="mb-0 text-dark"><i class="fa-solid fa-users me-2 text-indigo"></i>Cashier-wise Sales</h6></div>
            <div class="panel-body p-0">
                <table class="table table-hover mb-0"><thead><tr><th>Cashier</th><th class="text-center">Bills</th><th class="text-end">Total (₹)</th></tr></thead>
                <tbody id="de-cashier-table"><tr><td colspan="3" class="text-center py-3 text-secondary">Loading...</td></tr></tbody></table>
            </div>
        </div>
    </div>
</div>

<!-- Deductions Row -->
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="stat-card bg-light-danger border-0"><div class="d-flex justify-content-between align-items-center"><div><span class="text-secondary small">Returns</span><div class="fs-5 fw-bold text-rose" id="de-returns">₹0</div></div><i class="fa-solid fa-rotate-left text-rose fs-3"></i></div></div></div>
    <div class="col-md-4"><div class="stat-card bg-light-warning border-0"><div class="d-flex justify-content-between align-items-center"><div><span class="text-secondary small">Expenses</span><div class="fs-5 fw-bold text-warning" id="de-expenses">₹0</div></div><i class="fa-solid fa-receipt text-warning fs-3"></i></div></div></div>
    <div class="col-md-4"><div class="stat-card bg-light-danger border-0"><div class="d-flex justify-content-between align-items-center"><div><span class="text-secondary small">Outstanding Due</span><div class="fs-5 fw-bold text-rose" id="de-total-due">₹0</div></div><i class="fa-solid fa-clock text-rose fs-3"></i></div></div></div>
</div>

<script>
$(document).ready(function() {
    function loadDayEnd() {
        const date = $('#day-end-date').val();
        $.getJSON(BASE_URL + '/api/billing.php?action=day_end_report&date=' + date, function(res) {
            if (!res.status) return;
            const d = res.data;
            $('#de-invoice-count').text(d.totals.invoice_count || 0);
            $('#de-total-sales').text('₹' + parseFloat(d.totals.total_sales || 0).toFixed(2));
            $('#de-total-received').text('₹' + parseFloat(d.totals.total_received || 0).toFixed(2));
            $('#de-total-due').text('₹' + parseFloat(d.totals.total_due || 0).toFixed(2));
            $('#de-net-cash').text('₹' + parseFloat(d.net_cash || 0).toFixed(2));
            $('#de-returns').text('₹' + parseFloat(d.returns_total || 0).toFixed(2));
            $('#de-expenses').text('₹' + parseFloat(d.expenses_total || 0).toFixed(2));

            const mt = $('#de-methods-table').empty();
            if (d.sales_by_method.length === 0) mt.append('<tr><td colspan="4" class="text-center py-3 text-secondary">No sales today</td></tr>');
            else d.sales_by_method.forEach(function(r) {
                mt.append('<tr><td><span class="badge bg-light-primary">' + r.payment_method + '</span></td><td>' + r.count + '</td><td class="text-end fw-bold">₹' + parseFloat(r.total).toFixed(2) + '</td><td class="text-end text-emerald">₹' + parseFloat(r.paid).toFixed(2) + '</td></tr>');
            });

            const st = $('#de-split-table').empty();
            if (d.split_breakdown.length === 0) st.append('<tr><td colspan="2" class="text-center py-3 text-secondary">No split payments</td></tr>');
            else d.split_breakdown.forEach(function(r) {
                st.append('<tr><td><span class="badge bg-light-success">' + r.payment_method + '</span></td><td class="text-end fw-bold">₹' + parseFloat(r.total).toFixed(2) + '</td></tr>');
            });

            const tp = $('#de-top-products').empty();
            if (d.top_products.length === 0) tp.append('<tr><td colspan="3" class="text-center py-3 text-secondary">No products sold</td></tr>');
            else d.top_products.forEach(function(r) {
                tp.append('<tr><td class="fw-semibold text-dark">' + r.product_name + '</td><td class="text-center">' + parseFloat(r.qty_sold) + '</td><td class="text-end fw-bold text-indigo">₹' + parseFloat(r.revenue).toFixed(2) + '</td></tr>');
            });

            const ct = $('#de-cashier-table').empty();
            if (d.cashier_sales.length === 0) ct.append('<tr><td colspan="3" class="text-center py-3 text-secondary">No data</td></tr>');
            else d.cashier_sales.forEach(function(r) {
                ct.append('<tr><td class="fw-semibold">' + r.cashier + '</td><td class="text-center">' + r.bills + '</td><td class="text-end fw-bold">₹' + parseFloat(r.total).toFixed(2) + '</td></tr>');
            });
        });
    }
    loadDayEnd();
    $('#btn-load-day-end').click(loadDayEnd);
});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function downloadDayEndPDF() {
        const element = document.querySelector('.main-content') || document.body;
        const opt = {
            margin: 0.3,
            filename: 'DayEndReport_' + $('#day-end-date').val() + '.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
        };
        html2pdf().set(opt).from(element).save();
    }
</script>
