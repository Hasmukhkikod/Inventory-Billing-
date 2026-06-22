<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Floating Bottom Navigation for Mobile First UI (Screens < 768px)
 */
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
$currentPage = basename($_SERVER['PHP_SELF']);
$validModules = ['products', 'customers', 'suppliers', 'expenses', 'billing', 'reports', 'users', 'settings', 'purchases', 'returns', 'quotations', 'challans'];
$currentModule = in_array($currentDir, $validModules) ? $currentDir : $currentPage;
?>
<nav class="mobile-bottom-nav d-flex d-lg-none">
    <a href="<?php echo BASE_URL; ?>/index.php" class="nav-item <?php echo $currentModule === 'index.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-chart-pie"></i>
        <span>Dashboard</span>
    </a>
    
    <a href="<?php echo BASE_URL; ?>/products/index.php" class="nav-item <?php echo $currentModule === 'products' ? 'active' : ''; ?>">
        <i class="fa-solid fa-box-open"></i>
        <span>Inventory</span>
    </a>
    
    <a href="<?php echo BASE_URL; ?>/billing/index.php" class="nav-item <?php echo $currentModule === 'billing' ? 'active' : ''; ?>">
        <i class="fa-solid fa-file-invoice-dollar"></i>
        <span>Billing POS</span>
    </a>
    
    <a href="<?php echo BASE_URL; ?>/customers/index.php" class="nav-item <?php echo $currentModule === 'customers' ? 'active' : ''; ?>">
        <i class="fa-solid fa-users"></i>
        <span>CRM</span>
    </a>
    
    <a href="javascript:void(0);" class="nav-item" id="bottom-menu-toggle">
        <i class="fa-solid fa-bars text-indigo"></i>
        <span>Menu</span>
    </a>
</nav>

<style>
.mobile-bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: 65px;
    background: var(--bg-secondary);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: space-around;
    align-items: center;
    z-index: 1040;
    box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.05);
    padding-bottom: env(safe-area-inset-bottom);
}

.mobile-bottom-nav .nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    color: var(--text-secondary);
    font-size: 0.75rem;
    font-weight: 500;
    flex: 1;
    height: 100%;
    transition: all var(--transition-fast);
    gap: 4px;
}

.mobile-bottom-nav .nav-item i {
    font-size: 1.25rem;
    transition: transform var(--transition-fast);
}

.mobile-bottom-nav .nav-item:hover {
    color: var(--text-primary);
}

.mobile-bottom-nav .nav-item.active {
    color: var(--accent-indigo);
}

.mobile-bottom-nav .nav-item.active i {
    transform: translateY(-2px);
    text-shadow: 0 0 10px rgba(99, 102, 241, 0.6);
}

/* Hide desktop sidebars and add padding when bottom navigation is visible */
@media (max-width: 991.98px) {
    .sidebar {
        left: -260px;
        z-index: 1050;
        transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: none;
    }
    .sidebar.show {
        left: 0 !important;
        box-shadow: 5px 0 25px rgba(0, 0, 0, 0.15) !important;
    }
    .main-content {
        margin-left: 0 !important;
        padding-bottom: 85px !important; /* Spacing for sticky bottom nav */
        padding-top: 1rem !important;
    }
    .top-navbar {
        margin-bottom: 1.5rem !important;
    }
}
</style>
