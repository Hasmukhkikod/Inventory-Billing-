<?php
function planNameInitials($name) {
    $words = preg_split('/\s+/', trim($name));
    $initials = '';
    foreach (array_slice($words, 0, 2) as $word) {
        $initials .= strtoupper(substr($word, 0, 1));
    }
    return $initials ?: 'PL';
}
?>
<div class="content-body plans-list-page">
    <div class="plan-mobile-toolbar d-md-none">
        <div class="plan-mobile-title-row">
            <button class="plan-mobile-menu" id="sidebar-toggle-btn" type="button" aria-label="Toggle Menu">
                <i class="fa-solid fa-bars"></i>
            </button>
            <h1>Subscription Plans</h1>
            <a href="<?php echo BASE_URL; ?>/plans/form" class="btn btn-primary plan-mobile-create">
                <i class="fa-solid fa-plus"></i><span>New Plan</span>
            </a>
        </div>
        <div class="plan-mobile-search-row">
            <label class="plan-mobile-search" for="plan-mobile-search-input">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input id="plan-mobile-search-input" type="search" placeholder="Search plans…" autocomplete="off">
            </label>
            <button class="plan-mobile-filter" type="button" aria-label="Filter plans">
                <i class="fa-solid fa-arrow-down-wide-short"></i>
            </button>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4 plan-desktop-heading">
        <h2 class="h4 mb-0">Subscription Plans</h2>
        <a href="<?php echo BASE_URL; ?>/plans/form" class="btn btn-primary shadow-sm">
            <i class="fa-solid fa-plus me-2"></i>New Plan
        </a>
    </div>

    <div class="plan-mobile-cards d-md-none" id="plan-mobile-cards">
        <?php foreach($plans as $p): ?>
            <?php
                $isActive = $p['status'] === 'ACTIVE';
                $name = \App\Models\Helpers::sanitize($p['plan_name']);
            ?>
            <article class="plan-mobile-card" data-plan-search="<?php echo htmlspecialchars(strtolower($p['plan_name'] . ' ' . $p['status']), ENT_QUOTES, 'UTF-8'); ?>">
                <div class="plan-mobile-card-head">
                    <div class="plan-mobile-avatar"><?php echo planNameInitials($p['plan_name']); ?></div>
                    <div class="plan-mobile-identity">
                        <h2><?php echo $name; ?></h2>
                    </div>
                    <span class="plan-mobile-id">#<?php echo $p['id']; ?></span>
                </div>

                <div class="plan-mobile-details">
                    <div><span>Price</span><strong><?php echo \App\Models\Helpers::formatCurrency($p['price']); ?></strong></div>
                    <div><span>Duration (Days)</span><strong><?php echo $p['duration_days']; ?></strong></div>
                    <div><span>Status</span><strong class="plan-status-badge <?php echo $isActive ? 'active' : 'disabled'; ?>"><?php echo $isActive ? 'Active' : 'Inactive'; ?></strong></div>
                </div>

                <div class="plan-mobile-actions">
                    <a href="<?php echo BASE_URL; ?>/plans/form?id=<?php echo $p['id']; ?>" class="btn btn-outline-primary"><i class="fa-solid fa-pen"></i>Edit</a>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if(empty($plans)): ?>
            <div class="plan-mobile-empty">No plans found.</div>
        <?php endif; ?>
    </div>

    <div class="card shadow-sm border-0 plan-desktop-table">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="plansTable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Plan Name</th>
                            <th>Price</th>
                            <th>Duration (Days)</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($plans as $p): ?>
                        <tr>
                            <td><?php echo $p['id']; ?></td>
                            <td><strong class="text-primary"><?php echo \App\Models\Helpers::sanitize($p['plan_name']); ?></strong></td>
                            <td><?php echo \App\Models\Helpers::formatCurrency($p['price']); ?></td>
                            <td><?php echo $p['duration_days']; ?></td>
                            <td>
                                <?php if($p['status'] === 'ACTIVE'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?php echo BASE_URL; ?>/plans/form?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($plans)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No plans found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('plan-mobile-search-input');
    if (!input) return;
    input.addEventListener('input', function () {
        var query = input.value.trim().toLowerCase();
        document.querySelectorAll('.plan-mobile-card').forEach(function (card) {
            card.hidden = query && !card.dataset.planSearch.includes(query);
        });
    });
});
</script>
