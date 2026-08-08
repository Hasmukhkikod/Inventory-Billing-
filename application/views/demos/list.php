<?php
function demoAccountInitials($name) {
    $words = preg_split('/\s+/', trim($name));
    $initials = '';
    foreach (array_slice($words, 0, 2) as $word) {
        $initials .= strtoupper(substr($word, 0, 1));
    }
    return $initials ?: 'DA';
}
?>
<div class="content-body demos-list-page">
    <div class="demo-mobile-toolbar d-md-none">
        <div class="demo-mobile-title-row">
            <button class="demo-mobile-menu" id="sidebar-toggle-btn" type="button" aria-label="Toggle Menu">
                <i class="fa-solid fa-bars"></i>
            </button>
            <h1>Demo Accounts</h1>
            <a href="<?php echo BASE_URL; ?>/organizations/form?demo=1" class="demo-mobile-create" title="New Demo Account">
                <i class="fa-solid fa-plus"></i><span>New</span>
            </a>
        </div>
        <div class="demo-mobile-search-row">
            <label class="demo-mobile-search" for="demo-mobile-search-input">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input id="demo-mobile-search-input" type="search" placeholder="Search demo accounts…" autocomplete="off">
            </label>
            <button class="demo-mobile-filter" type="button" aria-label="Filter demo accounts">
                <i class="fa-solid fa-arrow-down-wide-short"></i>
            </button>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4 demo-desktop-heading">
        <div>
            <h2 class="h4 mb-1">Demo Accounts</h2>
            <span class="text-muted small">View all active and expired free trials</span>
        </div>
        <a href="<?php echo BASE_URL; ?>/organizations/form?demo=1" class="btn btn-primary shadow-sm">
            <i class="fa-solid fa-plus me-2"></i>New Demo Account
        </a>
    </div>

    <div class="demo-mobile-cards d-md-none" id="demo-mobile-cards">
        <?php foreach($orgs as $o): ?>
            <?php
                $isExpired = $o['valid_until'] && strtotime($o['valid_until']) < strtotime(date('Y-m-d'));
                $isApproved = (int)($o['is_approved'] ?? 0) === 1;
                $isActive = $o['status'] === 'ACTIVE';
                $name = \App\Models\Helpers::sanitize($o['name']);
                $email = \App\Models\Helpers::sanitize($o['email'] ?? 'N/A');
                $phone = \App\Models\Helpers::sanitize($o['phone'] ?? 'N/A');
                if (!$isApproved) {
                    $note = 'Password set. Needs admin approval.';
                    $noteIcon = 'fa-user-clock';
                } elseif ($isExpired) {
                    $note = 'Trial period has expired.';
                    $noteIcon = 'fa-triangle-exclamation';
                } elseif ($isActive) {
                    $note = 'Trial is live and active.';
                    $noteIcon = 'fa-check-double';
                } else {
                    $note = 'Trial account is disabled.';
                    $noteIcon = 'fa-ban';
                }
            ?>
            <article class="demo-mobile-card" data-demo-search="<?php echo htmlspecialchars(strtolower($o['name'] . ' ' . ($o['email'] ?? '') . ' ' . ($o['phone'] ?? '') . ' ' . ($o['plan_name'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>">
                <div class="demo-mobile-card-head">
                    <div class="demo-mobile-avatar"><?php echo demoAccountInitials($o['name']); ?></div>
                    <div class="demo-mobile-identity">
                        <h2><?php echo $name; ?></h2>
                        <div class="demo-mobile-contact">
                            <span><i class="fa-regular fa-envelope"></i><?php echo $email; ?></span>
                            <?php if (!empty($o['phone'])): ?><span><i class="fa-solid fa-phone"></i><?php echo $phone; ?></span><?php endif; ?>
                        </div>
                    </div>
                    <span class="demo-mobile-id">#<?php echo $o['id']; ?></span>
                </div>

                <div class="demo-mobile-details">
                    <div><span>Plan</span><strong class="demo-plan-badge"><?php echo \App\Models\Helpers::sanitize($o['plan_name'] ?? 'No Plan'); ?></strong></div>
                    <div><span>Start Date</span><strong><?php echo $o['start_date'] ? date('d M Y', strtotime($o['start_date'])) : '-'; ?></strong></div>
                    <div><span>Expiry Date</span><strong class="<?php echo $isExpired ? 'text-danger' : 'text-success'; ?>"><?php echo $o['valid_until'] ? date('d M Y', strtotime($o['valid_until'])) : 'Lifetime'; ?></strong></div>
                    <div>
                        <span>Status</span>
                        <div class="demo-status-group">
                            <strong class="demo-status-badge <?php echo $isApproved ? 'approved' : 'pending'; ?>"><i class="fa-solid <?php echo $isApproved ? 'fa-check' : 'fa-clock'; ?>"></i><?php echo $isApproved ? 'Approved' : 'Pending'; ?></strong>
                            <strong class="demo-status-badge <?php echo $isActive ? 'active' : 'disabled'; ?>"><?php echo $isActive ? 'Active' : 'Inactive'; ?></strong>
                        </div>
                    </div>
                </div>

                <div class="demo-status-note"><i class="fa-solid <?php echo $noteIcon; ?>"></i><span><?php echo $note; ?></span></div>

                <div class="demo-mobile-actions">
                    <a href="<?php echo BASE_URL; ?>/organizations/form?id=<?php echo $o['id']; ?>&demo=1" class="btn btn-outline-primary"><i class="fa-solid fa-pen"></i>Edit</a>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if(empty($orgs)): ?>
            <div class="demo-mobile-empty">No organizations found.</div>
        <?php endif; ?>
    </div>

    <div class="card shadow-sm border-0 demo-desktop-table">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="orgsTable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Plan</th>
                            <th>Start Date</th>
                            <th>Expiry Date</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($orgs as $o): ?>
                        <tr>
                            <td><?php echo $o['id']; ?></td>
                            <td><strong class="text-primary"><?php echo \App\Models\Helpers::sanitize($o['name']); ?></strong></td>
                            <td>
                                <div><i class="fa-solid fa-envelope me-1 text-muted"></i> <?php echo \App\Models\Helpers::sanitize($o['email'] ?? 'N/A'); ?></div>
                                <div class="small"><i class="fa-solid fa-phone me-1 text-muted"></i> <?php echo \App\Models\Helpers::sanitize($o['phone'] ?? 'N/A'); ?></div>
                            </td>
                            <td><span class="badge bg-info text-dark"><?php echo \App\Models\Helpers::sanitize($o['plan_name'] ?? 'No Plan'); ?></span></td>
                            <td>
                                <?php echo $o['start_date'] ? date('d M Y', strtotime($o['start_date'])) : '<span class="text-muted">-</span>'; ?>
                            </td>
                            <td>
                                <?php
                                if($o['valid_until']) {
                                    $isExpired = strtotime($o['valid_until']) < strtotime(date('Y-m-d'));
                                    echo $isExpired ? '<span class="text-danger fw-bold"><i class="fa-solid fa-triangle-exclamation me-1"></i>'.date('d M Y', strtotime($o['valid_until'])).'</span>' : '<span class="text-success">'.date('d M Y', strtotime($o['valid_until'])).'</span>';
                                } else {
                                    echo '<span class="text-muted">Lifetime</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php if($o['is_approved'] == 1): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success me-1"><i class="fa-solid fa-check me-1"></i>Approved</span>
                                <?php else: ?>
                                    <span class="badge bg-warning bg-opacity-10 text-warning me-1"><i class="fa-solid fa-clock me-1"></i>Pending</span>
                                <?php endif; ?>

                                <?php if($o['status'] === 'ACTIVE'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?php echo BASE_URL; ?>/organizations/form?id=<?php echo $o['id']; ?>&demo=1" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($orgs)): ?>
                            <tr><td colspan="8" class="text-center text-muted py-4">No organizations found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('demo-mobile-search-input');
    if (!input) return;
    input.addEventListener('input', function () {
        var query = input.value.trim().toLowerCase();
        document.querySelectorAll('.demo-mobile-card').forEach(function (card) {
            card.hidden = query && !card.dataset.demoSearch.includes(query);
        });
    });
});
</script>
