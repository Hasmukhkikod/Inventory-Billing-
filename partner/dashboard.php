<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Distributor/Partner Portal - Dashboard (Clients, Renewals, Commission)
 */
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
use App\Models\Helpers;
use App\Models\Database;
use App\Models\DistributorAuth;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = new Database();
$distAuth = new DistributorAuth($db);
$distAuth->requireLogin();
$distributor = $distAuth->user();

$clients = $db->query("
    SELECT o.id, o.name AS org_name, o.email, o.phone, o.status, o.valid_until, o.start_date,
           p.plan_name, p.price AS plan_price,
           dc.assigned_at
    FROM distributor_clients dc
    JOIN organizations o ON dc.organization_id = o.id
    LEFT JOIN plans p ON o.plan_id = p.id
    WHERE dc.distributor_id = ?
    ORDER BY o.valid_until ASC
", [$distributor['id']])->fetchAll();

$rate = (float)$distributor['commission_rate'];
$totalCommission = 0;
$activeCount = 0;
$expiringSoonCount = 0;
$today = strtotime(date('Y-m-d'));
foreach ($clients as &$c) {
    $price = (float)($c['plan_price'] ?? 0);
    $c['commission'] = round($price * $rate / 100, 2);
    if ($c['status'] === 'ACTIVE') {
        $totalCommission += $c['commission'];
        $activeCount++;
    }
    if (!empty($c['valid_until'])) {
        $daysLeft = round((strtotime($c['valid_until']) - $today) / 86400);
        $c['days_left'] = $daysLeft;
        if ($daysLeft >= 0 && $daysLeft <= 7) {
            $expiringSoonCount++;
        }
    } else {
        $c['days_left'] = null;
    }
}
unset($c);

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
require __DIR__ . '/../application/views/partner_layout_header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-0" style="color:#12214f;">Welcome, <?php echo Helpers::sanitize($distributor['name']); ?> 👋</h4>
        <p class="text-secondary mb-0 small"><?php echo Helpers::sanitize($distributor['business_name'] ?: 'Grovixo Partner'); ?> · Commission rate: <strong><?php echo number_format($rate, 2); ?>%</strong></p>
    </div>
    <a href="<?php echo BASE_URL; ?>/partner/leads" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i> Add a Lead</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="label">Total Clients</div>
            <div class="value"><?php echo count($clients); ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="label">Active Clients</div>
            <div class="value text-success"><?php echo $activeCount; ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="label">Renewing in 7 Days</div>
            <div class="value <?php echo $expiringSoonCount > 0 ? 'text-warning' : ''; ?>"><?php echo $expiringSoonCount; ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="label">Est. Commission</div>
            <div class="value" style="color:#3b5bff;">₹<?php echo number_format($totalCommission, 2); ?></div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-hd">
        <h5><i class="fa-solid fa-people-group me-2 text-primary"></i>Your Clients</h5>
        <span class="text-secondary small">Commission is estimated from each client's current plan price and your commission rate.</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th class="ps-3">Business</th>
                    <th>Plan</th>
                    <th>Status</th>
                    <th>Renewal Date</th>
                    <th class="text-end pe-3">Your Commission</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($clients)): ?>
                    <tr><td colspan="5" class="text-center text-secondary py-5">You don't have any clients assigned yet. Submit a lead and our team will follow up!</td></tr>
                <?php else: ?>
                    <?php foreach ($clients as $c): ?>
                        <tr>
                            <td class="ps-3">
                                <strong><?php echo Helpers::sanitize($c['org_name']); ?></strong>
                                <div class="text-secondary small"><?php echo Helpers::sanitize($c['email'] ?: '-'); ?></div>
                            </td>
                            <td><?php echo Helpers::sanitize($c['plan_name'] ?: '-'); ?></td>
                            <td>
                                <?php
                                    $statusClass = $c['status'] === 'ACTIVE' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary';
                                ?>
                                <span class="badge badge-status <?php echo $statusClass; ?>"><?php echo Helpers::sanitize($c['status']); ?></span>
                            </td>
                            <td>
                                <?php if (!empty($c['valid_until'])): ?>
                                    <?php echo date('d M Y', strtotime($c['valid_until'])); ?>
                                    <?php if ($c['days_left'] !== null && $c['days_left'] <= 7): ?>
                                        <div class="small <?php echo $c['days_left'] < 0 ? 'text-danger' : 'text-warning'; ?> fw-semibold">
                                            <?php echo $c['days_left'] < 0 ? 'Expired' : $c['days_left'] . ' day(s) left'; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-3 fw-bold">₹<?php echo number_format($c['commission'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../application/views/partner_layout_footer.php'; ?>
