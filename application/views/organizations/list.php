<?php
function organizationInitials($name) {
    $words = preg_split('/\s+/', trim($name));
    $initials = '';
    foreach (array_slice($words, 0, 2) as $word) {
        $initials .= strtoupper(substr($word, 0, 1));
    }
    return $initials ?: 'OR';
}
?>
<div class="content-body organizations-list-page">
    <div class="organization-mobile-toolbar d-md-none">
        <div class="organization-mobile-title-row">
            <button class="organization-mobile-menu" id="sidebar-toggle-btn" type="button" aria-label="Toggle Menu">
                <i class="fa-solid fa-bars"></i>
            </button>
            <h1><?php echo \App\Models\Helpers::translate('Organizations'); ?></h1>
            <a href="<?php echo BASE_URL; ?>/organizations/form" class="organization-mobile-create" title="<?php echo \App\Models\Helpers::translate('New Organization'); ?>">
                <i class="fa-solid fa-plus"></i><span>New</span>
            </a>
        </div>
        <div class="organization-mobile-search-row">
            <label class="organization-mobile-search" for="organization-mobile-search-input">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input id="organization-mobile-search-input" type="search" placeholder="Search organizations…" autocomplete="off">
            </label>
            <button class="organization-mobile-filter" type="button" aria-label="Filter organizations">
                <i class="fa-solid fa-arrow-down-wide-short"></i>
            </button>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4 organization-desktop-heading">
        <h2 class="h4 mb-0"><?php echo \App\Models\Helpers::translate('Organizations (Clients)'); ?></h2>
        <a href="<?php echo BASE_URL; ?>/organizations/form" class="btn btn-primary shadow-sm">
            <i class="fa-solid fa-plus me-2"></i><?php echo \App\Models\Helpers::translate('New Organization'); ?>
        </a>
    </div>

    <div class="organization-mobile-cards d-md-none" id="organization-mobile-cards">
        <?php foreach($orgs as $o): ?>
            <?php
                $isVerified = isset($o['is_verified']) ? (int)$o['is_verified'] : 0;
                $isApproved = isset($o['is_approved']) ? (int)$o['is_approved'] : 0;
                $isExpired = $o['valid_until'] && strtotime($o['valid_until']) < strtotime(date('Y-m-d'));
                $name = \App\Models\Helpers::sanitize($o['name']);
                $email = \App\Models\Helpers::sanitize($o['email'] ?? 'N/A');
                $phone = \App\Models\Helpers::sanitize($o['phone'] ?? 'N/A');
                $statusClass = $isVerified === 0 ? 'awaiting-verification' : ($isApproved === 0 ? 'awaiting-approval' : ($o['status'] === 'ACTIVE' ? 'active' : 'disabled'));
                $statusLabel = $isVerified === 0 ? 'Awaiting Verification' : ($isApproved === 0 ? 'Awaiting Approval' : ($o['status'] === 'ACTIVE' ? 'Verified & Active' : 'Account Disabled'));
                $statusIcon = $isVerified === 0 ? 'fa-envelope-circle-check' : ($isApproved === 0 ? 'fa-user-clock' : ($o['status'] === 'ACTIVE' ? 'fa-check-double' : 'fa-ban'));
                $statusNote = $isVerified === 0 ? 'Link sent. Waiting for password set.' : ($isApproved === 0 ? 'Password set. Needs admin approval.' : 'Full system access');
            ?>
            <article class="organization-mobile-card" data-organization-search="<?php echo htmlspecialchars(strtolower($o['name'] . ' ' . ($o['email'] ?? '') . ' ' . ($o['phone'] ?? '') . ' ' . ($o['plan_name'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>">
                <div class="organization-mobile-card-head">
                    <div class="organization-mobile-avatar"><?php echo organizationInitials($o['name']); ?></div>
                    <div class="organization-mobile-identity">
                        <div class="organization-mobile-name-line">
                            <h2><?php echo $name; ?></h2>
                            <?php if (stripos($o['name'], 'default') !== false): ?><span class="organization-default-badge">✦ Default</span><?php endif; ?>
                        </div>
                        <div class="organization-mobile-contact">
                            <span><i class="fa-regular fa-envelope"></i><?php echo $email; ?></span>
                            <?php if (!empty($o['phone'])): ?><span><i class="fa-solid fa-phone"></i><?php echo $phone; ?></span><?php endif; ?>
                        </div>
                    </div>
                    <span class="organization-mobile-id">#<?php echo $o['id']; ?></span>
                    <button class="organization-mobile-more" type="button" aria-label="More options"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                </div>

                <div class="organization-mobile-details">
                    <div><span>Plan</span><strong class="organization-plan-badge"><?php echo \App\Models\Helpers::sanitize($o['plan_name'] ?? 'No Plan'); ?></strong></div>
                    <div><span>Expiry Date</span><strong class="<?php echo $isExpired ? 'text-danger' : 'text-success'; ?>"><?php echo $o['valid_until'] ? date('d M Y', strtotime($o['valid_until'])) : 'Lifetime'; ?></strong></div>
                    <div><span>Start Date</span><strong><?php echo $o['start_date'] ? date('d M Y', strtotime($o['start_date'])) : '-'; ?></strong></div>
                    <div><span>Status</span><strong class="organization-status-badge <?php echo $statusClass; ?>"><i class="fa-solid <?php echo $statusIcon; ?>"></i><?php echo \App\Models\Helpers::translate($statusLabel); ?></strong></div>
                </div>

                <div class="organization-status-note"><i class="fa-regular fa-envelope"></i><span><?php echo \App\Models\Helpers::translate($statusNote); ?></span></div>
                <div class="organization-mobile-actions">
                    <a href="<?php echo BASE_URL; ?>/organizations/form?id=<?php echo $o['id']; ?>" class="btn btn-outline-secondary"><i class="fa-regular fa-eye"></i>View Details</a>
                    <a href="<?php echo BASE_URL; ?>/organizations/form?id=<?php echo $o['id']; ?>" class="btn btn-outline-primary"><i class="fa-solid fa-pen"></i>Edit</a>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if(empty($orgs)): ?>
            <div class="organization-mobile-empty">No organizations found.</div>
        <?php endif; ?>
    </div>

    <div class="card shadow-sm border-0 organization-desktop-table">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="orgsTable">
                    <thead class="table-light">
                        <tr>
                            <th><?php echo \App\Models\Helpers::translate('ID'); ?></th>
                            <th><?php echo \App\Models\Helpers::translate('Name'); ?></th>
                            <th><?php echo \App\Models\Helpers::translate('Contact'); ?></th>
                            <th><?php echo \App\Models\Helpers::translate('Plan'); ?></th>
                            <th><?php echo \App\Models\Helpers::translate('Start Date'); ?></th>
                            <th><?php echo \App\Models\Helpers::translate('Expiry Date'); ?></th>
                            <th><?php echo \App\Models\Helpers::translate('Status'); ?></th>
                            <th class="text-end"><?php echo \App\Models\Helpers::translate('Actions'); ?></th>
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
                                <?php
                                $isVerified = isset($o['is_verified']) ? (int)$o['is_verified'] : 0;
                                $isApproved = isset($o['is_approved']) ? (int)$o['is_approved'] : 0;
                                
                                if ($isVerified === 0) {
                                    echo '<span class="badge bg-warning text-dark border"><i class="fa-solid fa-envelope-circle-check me-1"></i>' . \App\Models\Helpers::translate('Awaiting Verification') . '</span>';
                                    echo '<div class="text-muted mt-1" style="font-size:11px;">' . \App\Models\Helpers::translate('Link sent. Waiting for password set.') . '</div>';
                                } elseif ($isVerified === 1 && $isApproved === 0) {
                                    echo '<span class="badge bg-info text-dark border"><i class="fa-solid fa-user-clock me-1"></i>Awaiting Approval</span>';
                                    echo '<div class="text-muted mt-1" style="font-size:11px;">Password set. Needs admin approval.</div>';
                                } else {
                                    if ($o['status'] === 'ACTIVE') {
                                        echo '<span class="badge bg-success border"><i class="fa-solid fa-check-double me-1"></i>Verified & Active</span>';
                                    } else {
                                        echo '<span class="badge bg-danger border"><i class="fa-solid fa-ban me-1"></i>Account Disabled</span>';
                                    }
                                    echo '<div class="text-muted mt-1" style="font-size:11px;">Full system access</div>';
                                }
                                ?>
                            </td>
                            <td class="text-end">
                                <a href="<?php echo BASE_URL; ?>/organizations/form?id=<?php echo $o['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
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
    var input = document.getElementById('organization-mobile-search-input');
    if (!input) return;
    input.addEventListener('input', function () {
        var query = input.value.trim().toLowerCase();
        document.querySelectorAll('.organization-mobile-card').forEach(function (card) {
            card.hidden = query && !card.dataset.organizationSearch.includes(query);
        });
    });
});
</script>
