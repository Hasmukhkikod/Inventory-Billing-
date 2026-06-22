/**
 * Invoice & Inventory Management System (IIMS)
 * Dashboard Client Controller
 */

$(document).ready(function() {
    fetchDashboardStats();
    
    function fetchDashboardStats() {
        $.ajax({
            url: 'api/dashboard.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    populateKPIs(response.data.kpis);
                    renderCharts(response.data.charts);
                    populateTables(response.data.recent);
                    applyMobileResponsiveLabels();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Data Load Error',
                        text: response.message,
                        background: '#151e30',
                        color: '#f3f4f6'
                    });
                }
            },
            error: function() {
                console.error("Failed to load dashboard data via API.");
            }
        });
    }
    
    function formatCurrency(amount) {
        return '₹' + parseFloat(amount).toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
    
    function populateKPIs(kpis) {
        $("#kpi-sales").text(formatCurrency(kpis.today_sales));
        $("#kpi-purchases").text(formatCurrency(kpis.today_purchases));
        $("#kpi-expenses").text(formatCurrency(kpis.today_expenses));
        
        const profitVal = parseFloat(kpis.today_profit);
        const profitEl = $("#kpi-profit");
        profitEl.text(formatCurrency(profitVal));
        if (profitVal < 0) {
            profitEl.removeClass('text-white').addClass('text-rose');
        } else {
            profitEl.removeClass('text-rose').addClass('text-white');
        }
        
        $("#count-customers").text(kpis.total_customers);
        $("#count-suppliers").text(kpis.total_suppliers);
        $("#count-products").text(kpis.total_products);
        $("#count-lowstock").text(kpis.low_stock_count);

        // v2.0 KPIs
        $("#count-overdue").text(kpis.overdue_count || 0);

        // Document range warnings
        if (kpis.doc_warnings && kpis.doc_warnings.length > 0) {
            let warningHtml = '';
            kpis.doc_warnings.forEach(function(w) {
                const color = w.remaining <= 0 ? 'danger' : 'warning';
                const icon = w.remaining <= 0 ? 'circle-exclamation' : 'triangle-exclamation';
                const msg = w.remaining <= 0
                    ? w.name + ' number limit REACHED! Update range in Settings.'
                    : 'Only ' + w.remaining + ' ' + w.name + ' numbers left (limit: ' + w.limit + ')';
                warningHtml += '<div class="alert alert-' + color + ' alert-dismissible fade show border-0 py-2 small mb-2" role="alert">' +
                    '<i class="fa-solid fa-' + icon + ' me-2"></i><strong>' + w.name + ':</strong> ' + msg +
                    '<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>';
            });
            $('#doc-range-warnings').html(warningHtml);
        }
        $("#count-held").text(kpis.held_count || 0);
        $("#count-receivable").text(formatCurrency(kpis.receivable_total || 0));
        $("#count-expiring").text(kpis.expiring_count || 0);
    }
    
    function populateTables(recent) {
        // Invoices
        const invoicesBody = $("#table-recent-invoices tbody");
        invoicesBody.empty();
        
        if (recent.invoices.length === 0) {
            invoicesBody.append('<tr><td colspan="5" class="text-center py-4 text-secondary">No invoices issued yet</td></tr>');
        } else {
            recent.invoices.forEach(function(inv) {
                let badgeClass = 'bg-light-success';
                if (inv.status === 'PARTIAL') badgeClass = 'bg-light-warning';
                if (inv.status === 'UNPAID') badgeClass = 'bg-light-danger';
                if (inv.status === 'CANCELLED') badgeClass = 'text-decoration-line-through text-muted';
                
                invoicesBody.append(`
                    <tr>
                        <td class="fw-semibold text-white" data-label="Invoice No">${inv.invoice_no}</td>
                        <td data-label="Customer">${inv.customer_name || 'Walk-in Customer'}</td>
                        <td data-label="Date">${formatDate(inv.invoice_date)}</td>
                        <td class="fw-bold" data-label="Amount">${formatCurrency(inv.grand_total)}</td>
                        <td data-label="Status"><span class="badge ${badgeClass}">${inv.status}</span></td>
                    </tr>
                `);
            });
        }
        
        // Payments
        const paymentsBody = $("#table-recent-payments tbody");
        paymentsBody.empty();
        
        if (recent.payments.length === 0) {
            paymentsBody.append('<tr><td colspan="5" class="text-center py-4 text-secondary">No transaction logs available</td></tr>');
        } else {
            recent.payments.forEach(function(pay) {
                const isReceivable = pay.transaction_type === 'Customer Payment' || pay.transaction_type === 'RECEIVABLE';
                const typeBadge = isReceivable 
                    ? '<span class="badge bg-light-success"><i class="fa-solid fa-arrow-down me-1"></i>In</span>' 
                    : '<span class="badge bg-light-danger"><i class="fa-solid fa-arrow-up me-1"></i>Out</span>';
                    
                paymentsBody.append(`
                    <tr>
                        <td data-label="Party Name">
                            <div class="fw-semibold text-white">${pay.party_name || 'System Transaction'}</div>
                        </td>
                        <td data-label="Type">${typeBadge}</td>
                        <td data-label="Date">${formatDate(pay.transaction_date)}</td>
                        <td data-label="Method">${pay.payment_method}</td>
                        <td class="fw-bold ${isReceivable ? 'text-emerald' : 'text-rose'}" data-label="Amount">
                            ${isReceivable ? '+' : '-'}${formatCurrency(pay.amount)}
                        </td>
                    </tr>
                `);
            });
        }
    }
    
    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
    }
    
    function renderCharts(charts) {
        const gridColor = 'rgba(255, 255, 255, 0.05)';
        const textMuted = '#9ca3af';
        
        // 1. Daily Sales Chart (Line)
        const ctxDaily = document.getElementById('chart-daily-sales').getContext('2d');
        const gradientDaily = ctxDaily.createLinearGradient(0, 0, 0, 300);
        gradientDaily.addColorStop(0, 'rgba(99, 102, 241, 0.4)');
        gradientDaily.addColorStop(1, 'rgba(99, 102, 241, 0.0)');
        
        new Chart(ctxDaily, {
            type: 'line',
            data: {
                labels: charts.daily_sales.map(item => item.label),
                datasets: [{
                    label: 'Daily Sales (₹)',
                    data: charts.daily_sales.map(item => item.value),
                    borderColor: '#6366f1',
                    borderWidth: 3,
                    pointBackgroundColor: '#6366f1',
                    pointHoverRadius: 6,
                    tension: 0.4,
                    fill: true,
                    backgroundColor: gradientDaily
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: textMuted }
                    },
                    y: {
                        grid: { color: gridColor },
                        ticks: {
                            color: textMuted,
                            callback: function(value) { return '₹' + value; }
                        }
                    }
                }
            }
        });
        
        // 2. Monthly Revenue Chart (Bar)
        const ctxMonthly = document.getElementById('chart-monthly-sales').getContext('2d');
        new Chart(ctxMonthly, {
            type: 'bar',
            data: {
                labels: charts.monthly_sales.map(item => item.label),
                datasets: [{
                    label: 'Monthly Revenue (₹)',
                    data: charts.monthly_sales.map(item => item.value),
                    backgroundColor: 'rgba(99, 102, 241, 0.85)',
                    hoverBackgroundColor: '#6366f1',
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: textMuted }
                    },
                    y: {
                        grid: { color: gridColor },
                        ticks: {
                            color: textMuted,
                            callback: function(value) { return '₹' + value; }
                        }
                    }
                }
            }
        });
        
        // 3. Expenses by Category Chart (Doughnut)
        const ctxExpenses = document.getElementById('chart-expenses-cat').getContext('2d');
        if (charts.expenses_by_category.length === 0) {
            new Chart(ctxExpenses, {
                type: 'doughnut',
                data: {
                    labels: ['No Expenses logged'],
                    datasets: [{
                        data: [1],
                        backgroundColor: ['#1f2937'],
                        borderWidth: 1,
                        borderColor: '#2e3b52'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { color: textMuted } }
                    }
                }
            });
        } else {
            new Chart(ctxExpenses, {
                type: 'doughnut',
                data: {
                    labels: charts.expenses_by_category.map(item => item.category),
                    datasets: [{
                        data: charts.expenses_by_category.map(item => item.total),
                        backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#ec4899', '#3b82f6', '#8b5cf6', '#f43f5e'],
                        borderWidth: 2,
                        borderColor: '#111827'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { color: textMuted } }
                    }
                }
            });
        }

        // v2.0: Top Products Bar Chart
        if (charts.top_products && charts.top_products.length > 0) {
            const ctxTop = document.getElementById('chart-top-products').getContext('2d');
            new Chart(ctxTop, {
                type: 'bar',
                data: {
                    labels: charts.top_products.map(i => i.product_name),
                    datasets: [{
                        label: 'Qty Sold',
                        data: charts.top_products.map(i => parseFloat(i.qty_sold)),
                        backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#ec4899', '#3b82f6'],
                        borderRadius: 6
                    }]
                },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { grid: { color: gridColor }, ticks: { color: textMuted } }, y: { grid: { display: false }, ticks: { color: textMuted } } }
                }
            });
        }

        // v2.0: Payment Mode Doughnut
        if (charts.payment_modes && charts.payment_modes.length > 0) {
            const ctxPay = document.getElementById('chart-payment-modes').getContext('2d');
            new Chart(ctxPay, {
                type: 'doughnut',
                data: {
                    labels: charts.payment_modes.map(i => i.payment_method),
                    datasets: [{
                        data: charts.payment_modes.map(i => parseFloat(i.total)),
                        backgroundColor: ['#10b981', '#6366f1', '#f59e0b', '#3b82f6', '#ec4899', '#8b5cf6'],
                        borderWidth: 2, borderColor: '#ffffff'
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { color: textMuted } } } }
            });
        }
    }

    function applyMobileResponsiveLabels() {
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
