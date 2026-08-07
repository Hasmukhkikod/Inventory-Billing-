<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Floating Bottom Navigation for Mobile First UI (Screens < 768px)
 */
$requestPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$uriSegments = $requestPath === '' ? [] : explode('/', $requestPath);
$currentDir = $uriSegments[0] ?? 'index';
$currentPage = end($uriSegments) ?: 'index';
$isSuperAdmin = ($_SESSION['org_id'] ?? null) === 0;

if ($isSuperAdmin) {
    $validModules = ['organizations', 'demos', 'plans', 'announcements'];
    $currentModule = in_array($currentDir, $validModules) ? $currentDir : $currentPage;
} else {
    $validModules = ['products', 'customers', 'suppliers', 'expenses', 'billing', 'reports', 'users', 'settings', 'purchases', 'returns', 'quotations', 'challans'];
    $currentModule = in_array($currentDir, $validModules) ? $currentDir : $currentPage;
}
?>
<nav class="mobile-bottom-nav d-flex d-lg-none">
    <a href="<?php echo BASE_URL; ?>/index" class="nav-item <?php echo $currentModule === 'index' ? 'active' : ''; ?>">
        <i class="fa-solid fa-chart-pie"></i>
        <span>Dashboard</span>
    </a>

    <?php if ($isSuperAdmin): ?>
    <a href="<?php echo BASE_URL; ?>/organizations/index" class="nav-item <?php echo $currentModule === 'organizations' ? 'active' : ''; ?>">
        <i class="fa-solid fa-building"></i>
        <span>Orgs</span>
    </a>

    <a href="<?php echo BASE_URL; ?>/demos/index" class="nav-item <?php echo $currentModule === 'demos' ? 'active' : ''; ?>">
        <i class="fa-solid fa-stopwatch"></i>
        <span>Demos</span>
    </a>

    <a href="<?php echo BASE_URL; ?>/plans/index" class="nav-item <?php echo $currentModule === 'plans' ? 'active' : ''; ?>">
        <i class="fa-solid fa-list-check"></i>
        <span>Plans</span>
    </a>
    <?php else: ?>
    <a href="<?php echo BASE_URL; ?>/products/index" class="nav-item <?php echo $currentModule === 'products' ? 'active' : ''; ?>">
        <i class="fa-solid fa-box-open"></i>
        <span>Inventory</span>
    </a>

    <a href="<?php echo BASE_URL; ?>/billing/index" class="nav-item <?php echo $currentModule === 'billing' ? 'active' : ''; ?>">
        <i class="fa-solid fa-file-invoice-dollar"></i>
        <span>Billing POS</span>
    </a>

    <a href="<?php echo BASE_URL; ?>/customers/index" class="nav-item <?php echo $currentModule === 'customers' ? 'active' : ''; ?>">
        <i class="fa-solid fa-users"></i>
        <span>CRM</span>
    </a>
    <?php endif; ?>

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

/* Sidebar open/close, and the .main-content/.top-navbar layout knock-on
   effects of the bottom nav bar, are already fully handled by style.css's
   mobile-first section (.sidebar / .sidebar.show / .main-content). This
   used to duplicate those rules with a second, conflicting mechanism (a
   left-position-based sidebar toggle competing with style.css's
   transform-based one, plus a stale hardcoded padding-bottom value) - both
   removed in favor of the single source of truth in style.css. */
</style>
