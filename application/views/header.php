<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * MVC Common Header View
 */

use App\Models\Auth;
use App\Models\Helpers;
use App\Models\Database;

$auth = $this->auth;
if (!$auth->check()) {
    header("Location: " . BASE_URL . "/login.php");
    exit;
}
$currentUser = $auth->user();

$currentDir = basename(dirname($_SERVER['PHP_SELF']));
$currentPage = basename($_SERVER['PHP_SELF']);
$validModules = ['products', 'customers', 'suppliers', 'expenses', 'billing', 'reports', 'users', 'settings', 'purchases', 'returns', 'quotations', 'challans'];
$currentModule = in_array($currentDir, $validModules) ? $currentDir : $currentPage;

// Fetch Company Settings
$db = $this->db;
$compSettings = $db->query("SELECT * FROM company_settings WHERE id = 1 LIMIT 1")->fetch();

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo Helpers::sanitize($compSettings['company_name'] ?? COMPANY_NAME); ?> - IIMS</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/images/favicon.png" error="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📦</text></svg>'">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- DataTables Bootstrap 5 CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- SweetAlert 2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Select2 Searchable Dropdowns -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <!-- Custom stylesheet -->
    <link href="<?php echo BASE_URL; ?>/assets/css/style.css" rel="stylesheet">
    <!-- JS BASE_URL Declaration -->
    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
    </script>
</head>
<body>
<div class="sidebar-backdrop" id="sidebar-backdrop"></div>

<div class="app-wrapper">
    <!-- Sidebar -->
    <aside class="sidebar d-flex" id="app-sidebar">
        <div class="sidebar-header d-flex justify-content-between align-items-center">
            <a href="index.php" class="sidebar-brand">
                <?php if (!empty($compSettings['company_logo']) && file_exists(BASE_DIR . '/uploads/' . $compSettings['company_logo'])): ?>
                    <img src="<?php echo BASE_URL . '/uploads/' . $compSettings['company_logo']; ?>" alt="Logo" style="height: 32px; width: 32px; object-fit: contain; border-radius: 6px;">
                <?php else: ?>
                    <i class="fa-solid fa-boxes-stacked"></i>
                <?php endif; ?>
                <span><?php echo \App\Models\Helpers::sanitize($compSettings['company_name'] ?? 'Grovixo'); ?></span>
            </a>
            <button class="btn-close d-lg-none" id="sidebar-close-btn" type="button" aria-label="Close" style="filter: none;"></button>
        </div>
        
        <ul class="sidebar-menu">
            <?php if ($auth->hasPermission('Access Dashboard')): ?>
            <li class="sidebar-item <?php echo $currentModule === 'index.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/index.php" class="sidebar-link">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($auth->hasPermission('Manage Inventory')): ?>
            <li class="sidebar-item <?php echo $currentModule === 'products' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/products/index.php" class="sidebar-link">
                    <i class="fa-solid fa-box-open"></i>
                    <span>Inventory</span>
                </a>
            </li>
            <li class="sidebar-item <?php echo $currentModule === 'purchases' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/purchases/index.php" class="sidebar-link">
                    <i class="fa-solid fa-cart-flatbed"></i>
                    <span>Purchases</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($auth->hasPermission('Create Invoice')): ?>
            <li class="sidebar-item <?php echo $currentModule === 'billing' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/billing/index.php" class="sidebar-link">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span>POS & Billing</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($auth->hasPermission('Manage Inventory')): ?>
            <li class="sidebar-item <?php echo $currentModule === 'returns' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/returns/index.php" class="sidebar-link">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span>Returns Log</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($auth->hasPermission('Manage Quotations')): ?>
            <li class="sidebar-item <?php echo $currentModule === 'quotations' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/quotations/index.php" class="sidebar-link">
                    <i class="fa-solid fa-file-signature"></i>
                    <span>Quotations</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($auth->hasPermission('Manage Challans')): ?>
            <li class="sidebar-item <?php echo $currentModule === 'challans' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/challans/index.php" class="sidebar-link">
                    <i class="fa-solid fa-truck-fast"></i>
                    <span>Delivery Challans</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($auth->hasPermission('Manage Customers')): ?>
            <li class="sidebar-item <?php echo $currentModule === 'customers' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/customers/index.php" class="sidebar-link">
                    <i class="fa-solid fa-users"></i>
                    <span>Customers</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($auth->hasPermission('Manage Suppliers')): ?>
            <li class="sidebar-item <?php echo $currentModule === 'suppliers' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/suppliers/index.php" class="sidebar-link">
                    <i class="fa-solid fa-truck-field"></i>
                    <span>Suppliers</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($auth->hasPermission('Manage Expenses')): ?>
            <li class="sidebar-item <?php echo $currentModule === 'expenses' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/expenses/index.php" class="sidebar-link">
                    <i class="fa-solid fa-wallet"></i>
                    <span>Expenses</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($auth->hasPermission('View Reports')): ?>
            <li class="sidebar-item <?php echo $currentModule === 'reports' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/reports/index.php" class="sidebar-link">
                    <i class="fa-solid fa-file-waveform"></i>
                    <span>Reports</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($auth->hasPermission('Manage Users')): ?>
            <li class="sidebar-item <?php echo $currentModule === 'users' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/users/index.php" class="sidebar-link">
                    <i class="fa-solid fa-users-gear"></i>
                    <span>Users</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($auth->hasPermission('Manage Settings')): ?>
            <li class="sidebar-item <?php echo $currentModule === 'settings' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/settings/index.php" class="sidebar-link">
                    <i class="fa-solid fa-gears"></i>
                    <span>Settings</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-avatar">
                    <?php echo strtoupper(substr($currentUser['name'], 0, 1)); ?>
                </div>
                <div class="sidebar-user-details">
                    <span class="sidebar-username"><?php echo Helpers::sanitize($currentUser['name']); ?></span>
                    <span class="sidebar-role"><?php echo Helpers::sanitize($currentUser['role_name']); ?></span>
                </div>
            </div>
            <a href="<?php echo BASE_URL; ?>/logout.php" class="text-danger btn-logout-icon" title="Logout">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </aside>

    <!-- Main Content Panel -->
    <main class="main-content">
        <!-- Top Navbar -->
        <header class="top-navbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary d-lg-none py-1.5 px-2.5" id="sidebar-toggle-btn" type="button" aria-label="Toggle Menu" style="margin-bottom: 0;">
                    <i class="fa-solid fa-bars fs-5"></i>
                </button>
                <h4 class="mb-0 text-capitalize d-none d-lg-block">
                    <?php
                        $titleMap = [
                            'index.php' => 'Business Performance Dashboard',
                            'products' => 'Inventory Management',
                            'billing' => 'POS Terminal & Invoicing',
                            'customers' => 'Customer CRM & Ledgers',
                            'suppliers' => 'Supplier Directory & Payables',
                            'expenses' => 'Expense Book Keeping',
                            'reports' => 'Analytics & Reports Hub',
                            'users' => 'User Directory & Permissions',
                            'settings' => 'System Configurations',
                            'purchases' => 'Supplier Purchases Ledger',
                            'returns' => 'Sales & Purchase Returns',
                            'quotations' => 'Quotations & Estimates',
                            'challans' => 'Delivery Challans'
                        ];
                        echo $titleMap[$currentModule] ?? 'Grovixo';
                    ?>
                </h4>
            </div>

            <!-- Global Search box centered -->
            <div class="global-search-container mx-2 flex-grow-1 flex-sm-grow-0" style="max-width: 320px;">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass text-indigo"></i></span>
                    <input type="text" class="form-control" id="global-search-input" placeholder="Search..." autocomplete="off">
                </div>
                <div class="global-search-results d-none" id="global-search-results-box"></div>
            </div>

            <!-- Header actions (Clock, Notification, Profile dropdown) -->
            <div class="d-flex align-items-center gap-3">
                <div class="text-secondary small d-none d-xl-block">
                    <i class="fa-regular fa-clock me-1"></i> <?php echo date('d-M-Y H:i'); ?>
                </div>
                
                <!-- Notification Bell Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-outline-secondary py-1.5 px-2.5 notification-bell-btn d-flex align-items-center" type="button" data-bs-toggle="dropdown" id="notificationDropdownBtn" aria-expanded="false">
                        <i class="fa-regular fa-bell text-indigo fs-5"></i>
                        <span class="notification-badge d-none" id="notification-badge-count">0</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border notification-dropdown p-2" aria-labelledby="notificationDropdownBtn" id="notification-dropdown-box">
                        <li class="dropdown-header text-uppercase text-secondary fw-semibold border-bottom pb-2 mb-2" style="font-size: 0.75rem;">System Alerts</li>
                        <div id="notification-list-content" style="max-height: 250px; overflow-y: auto;">
                            <li class="text-center py-3 text-secondary small">No pending alerts</li>
                        </div>
                    </ul>
                </div>

                <div class="border-start border-secondary h-25 mx-1 d-none d-md-block"></div>
                
                <!-- User Profile Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-circle-user text-indigo"></i>
                        <span class="d-none d-sm-inline"><?php echo Helpers::sanitize($currentUser['email']); ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border">
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/settings/index.php"><i class="fa-solid fa-cog me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/logout.php"><i class="fa-solid fa-sign-out me-2"></i>Sign Out</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Global UI/UX Event handlers scripts -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
        $(document).ready(function() {
            // 1. Live Global Search autocomplete
            let searchTimeout = null;
            $("#global-search-input").on('input', function() {
                clearTimeout(searchTimeout);
                const query = $(this).val().trim();
                const box = $("#global-search-results-box");
                
                if (query.length < 2) {
                    box.addClass('d-none');
                    return;
                }
                
                searchTimeout = setTimeout(function() {
                    $.ajax({
                        url: BASE_URL + '/api/search.php?q=' + encodeURIComponent(query),
                        type: 'GET',
                        dataType: 'json',
                        success: function(res) {
                            box.empty();
                            if (res.status && (res.data.products.length > 0 || res.data.customers.length > 0 || res.data.suppliers.length > 0 || res.data.invoices.length > 0)) {
                                const d = res.data;
                                
                                // Products list
                                if (d.products.length > 0) {
                                    box.append('<div class="global-search-group-header">Inventory Products</div>');
                                    d.products.forEach(p => {
                                        box.append(`
                                            <div class="global-search-item" onclick="window.location='${BASE_URL}/products/index.php'">
                                                <span><strong>${p.title}</strong> <small class="text-muted">(${p.subtitle})</small></span>
                                                <span class="badge bg-light-primary">₹${parseFloat(p.price).toFixed(2)} | Stock: ${parseFloat(p.stock)}</span>
                                            </div>
                                        `);
                                    });
                                }
                                
                                // Customers list
                                if (d.customers.length > 0) {
                                    box.append('<div class="global-search-group-header">CRM Customers</div>');
                                    d.customers.forEach(c => {
                                        box.append(`
                                            <div class="global-search-item" onclick="window.location='${BASE_URL}/customers/index.php'">
                                                <span><strong>${c.title}</strong></span>
                                                <span class="text-muted small">${c.subtitle}</span>
                                            </div>
                                        `);
                                    });
                                }

                                // Suppliers list
                                if (d.suppliers.length > 0) {
                                    box.append('<div class="global-search-group-header">Suppliers Directory</div>');
                                    d.suppliers.forEach(s => {
                                        box.append(`
                                            <div class="global-search-item" onclick="window.location='${BASE_URL}/suppliers/index.php'">
                                                <span><strong>${s.title}</strong></span>
                                                <span class="text-muted small">${s.subtitle}</span>
                                            </div>
                                        `);
                                    });
                                }

                                // Invoices list
                                if (d.invoices.length > 0) {
                                    box.append('<div class="global-search-group-header">Invoices Log</div>');
                                    d.invoices.forEach(i => {
                                        box.append(`
                                            <div class="global-search-item" onclick="window.open('${BASE_URL}/invoice_print.php?id=${i.id}', '_blank')">
                                                <span><i class="fa-solid fa-receipt me-1 text-indigo"></i><strong>${i.title}</strong> <small class="text-muted">(${i.subtitle})</small></span>
                                                <span class="fw-bold text-rose">₹${parseFloat(i.price).toFixed(2)}</span>
                                            </div>
                                        `);
                                    });
                                }
                                box.removeClass('d-none');
                            } else {
                                box.html('<div class="p-3 text-center text-muted small">No global matches found</div>').removeClass('d-none');
                            }
                        }
                    });
                }, 300);
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('#global-search-input, #global-search-results-box').length) {
                    $("#global-search-results-box").addClass('d-none');
                }
            });

            // 2. Fetch Notifications Bell Updates
            function loadNotifications() {
                $.ajax({
                    url: BASE_URL + '/api/notifications.php?action=list',
                    type: 'GET',
                    dataType: 'json',
                    success: function(res) {
                        if (res.status) {
                            const badge = $("#notification-badge-count");
                            const count = res.data.count;
                            
                            if (count > 0) {
                                badge.text(count).removeClass('d-none');
                            } else {
                                badge.addClass('d-none');
                            }

                            const container = $("#notification-list-content");
                            container.empty();
                            
                            if (res.data.alerts.length === 0) {
                                container.append('<li class="text-center py-3 text-secondary small">No pending alerts</li>');
                            } else {
                                res.data.alerts.forEach(function(item) {
                                    let icon = '<i class="fa-solid fa-circle-info text-primary me-2"></i>';
                                    if (item.type === 'STOCK') {
                                        icon = '<i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>';
                                    }
                                    container.append(`
                                        <li class="px-3 py-2 border-bottom notification-item" data-id="${item.id}" style="cursor: pointer; list-style: none;">
                                            <div class="d-flex align-items-start">
                                                ${icon}
                                                <div>
                                                    <div class="fw-semibold text-white small" style="font-size:0.8rem;">${item.title}</div>
                                                    <div class="text-secondary" style="font-size:0.75rem;">${item.message}</div>
                                                </div>
                                            </div>
                                        </li>
                                    `);
                                });
                            }
                        }
                    }
                });
            }

            loadNotifications();
            setInterval(loadNotifications, 30000); // Polling count alerts every 30s

            // Mark notifications read
            $("#notification-list-content").on('click', '.notification-item', function() {
                const id = $(this).data('id');
                const self = $(this);
                $.ajax({
                    url: BASE_URL + '/api/notifications.php?action=mark_read',
                    type: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status) {
                            self.slideUp(function() { 
                                self.remove();
                                loadNotifications();
                            });
                        }
                    }
                });
            });
        });
        </script>
        
        <!-- Flash Alert system -->
        <?php if (isset($_GET['error']) && $_GET['error'] === 'unauthorized'): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 bg-light-danger shadow-sm mb-4" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                <strong>Access Denied:</strong> You do not possess the required user roles/permissions to view that module.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
