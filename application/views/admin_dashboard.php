<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Super Admin Dashboard View Page
 */
?>
<section class="dashboard-shell">
    <div class="dashboard-hero mb-4" style="background: radial-gradient(circle at 90% 10%, #4f46e5 0%, transparent 60%), #1e1b4b; color: white; border-radius: 1rem; padding: 2rem;">
        <div class="dashboard-hero-content">
            <div>
                <span class="dashboard-eyebrow" style="color: #aebdff;">System Administrator Command Center</span>
                <h1>Platform Overview</h1>
                <p>Manage organizations, monitor registration pipelines, and track data storage usage across all tenants.</p>
            </div>
            <div class="dashboard-hero-actions">
                <a href="<?php echo BASE_URL; ?>/organizations/index" class="btn btn-light">
                    <i class="fa-solid fa-building me-2"></i>Manage Organizations
                </a>
                <a href="<?php echo BASE_URL; ?>/settings/index" class="btn btn-outline-light">
                    <i class="fa-solid fa-gears me-2"></i>System Settings
                </a>
            </div>
        </div>

        <div class="dashboard-hero-metrics">
            <div>
                <span>Total Tenants</span>
                <strong><?php echo number_format($data['total_orgs']); ?></strong>
            </div>
            <div>
                <span>Est. Storage Used</span>
                <strong><?php echo $data['total_storage_mb']; ?> MB</strong>
            </div>
            <div>
                <span>Platform Records</span>
                <strong><?php echo number_format($data['total_system_records']); ?></strong>
            </div>
        </div>
    </div>

    <!-- Registration Pipeline KPIs -->
    <div class="mb-2 fw-bold text-secondary text-uppercase" style="letter-spacing: 1px; font-size: 0.85rem;"><i class="fa-solid fa-filter me-2 text-indigo"></i>Registration Pipeline</div>
    <div class="dashboard-kpi-grid mb-4">
        <div class="dashboard-kpi-card accent-indigo">
            <div class="dashboard-kpi-icon"><i class="fa-solid fa-building"></i></div>
            <div>
                <span>Total Signups</span>
                <strong><?php echo number_format($data['total_orgs']); ?></strong>
                <small>All time organizations</small>
            </div>
        </div>
        <div class="dashboard-kpi-card accent-amber">
            <div class="dashboard-kpi-icon"><i class="fa-solid fa-envelope-open-text"></i></div>
            <div>
                <span>Awaiting Verification</span>
                <strong><?php echo number_format($data['awaiting_verification']); ?></strong>
                <small>Link sent, waiting for user</small>
            </div>
        </div>
        <div class="dashboard-kpi-card accent-rose">
            <div class="dashboard-kpi-icon"><i class="fa-solid fa-user-shield"></i></div>
            <div>
                <span>Awaiting Approval</span>
                <strong><?php echo number_format($data['awaiting_approval']); ?></strong>
                <small>Needs Admin review</small>
            </div>
        </div>
        <div class="dashboard-kpi-card accent-emerald">
            <div class="dashboard-kpi-icon"><i class="fa-solid fa-circle-check"></i></div>
            <div>
                <span>Active Tenants</span>
                <strong><?php echo number_format($data['active_orgs']); ?></strong>
                <small>Fully verified & active</small>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="panel-card dashboard-panel h-100 border-0 shadow-sm rounded-4">
                <div class="panel-header d-flex justify-content-between align-items-center bg-white border-bottom p-4 rounded-top-4">
                    <div>
                        <span class="panel-kicker text-indigo fw-bold mb-1 d-block" style="font-size: 0.75rem; letter-spacing: 1.5px;">Tenant Management & Storage</span>
                        <h5 class="mb-0 text-dark"><i class="fa-solid fa-server me-2 text-primary"></i>Recent Organizations Pipeline</h5>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/organizations/index" class="btn btn-sm btn-outline-primary px-3 rounded-pill fw-semibold">View All</a>
                </div>
                <div class="panel-body p-0 bg-white rounded-bottom-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                            <thead class="table-light text-secondary">
                                <tr>
                                    <th class="ps-4">ID</th>
                                    <th>Organization Details</th>
                                    <th>Pipeline Stage</th>
                                    <th>Current Plan</th>
                                    <th>Storage Utilization</th>
                                    <th class="text-end pe-4">Valid Until</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($data['recent_orgs'])): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fa-regular fa-folder-open mb-2" style="font-size: 2rem;"></i><br>
                                        No organizations registered yet.
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($data['recent_orgs'] as $org): ?>
                                        <?php 
                                            // Storage calculation
                                            $orgRecords = $data['storage_map'][$org['id']] ?? 0;
                                            $orgStorageMb = number_format(($orgRecords * 2.5) / 1024, 2);
                                            // Assume max storage is 100MB for progress bar demo
                                            $storagePercentage = min(100, (($orgRecords * 2.5) / 1024) / 100 * 100);
                                            
                                            // Status Badge
                                            $isVerified = isset($org['is_verified']) ? (int)$org['is_verified'] : 0;
                                            $isApproved = isset($org['is_approved']) ? (int)$org['is_approved'] : 0;
                                            
                                            $stageBadge = '';
                                            $stageColor = '';
                                            $stageIcon = '';
                                            $stageText = '';
                                            
                                            if ($isVerified === 0) {
                                                $stageColor = 'warning';
                                                $stageIcon = 'fa-envelope';
                                                $stageText = 'Verification Sent (Awaiting User)';
                                            } elseif ($isVerified === 1 && $isApproved === 0) {
                                                $stageColor = 'danger';
                                                $stageIcon = 'fa-user-shield';
                                                $stageText = 'Awaiting Admin Approval';
                                            } else {
                                                if ($org['status'] === 'ACTIVE') {
                                                    if ($org['valid_until'] && strtotime($org['valid_until']) < time()) {
                                                        $stageColor = 'secondary';
                                                        $stageIcon = 'fa-clock';
                                                        $stageText = 'Trial Expired';
                                                    } else {
                                                        $stageColor = 'success';
                                                        $stageIcon = 'fa-circle-check';
                                                        $stageText = 'Fully Active';
                                                    }
                                                } else {
                                                    $stageColor = 'dark';
                                                    $stageIcon = 'fa-ban';
                                                    $stageText = 'Inactive';
                                                }
                                            }
                                        ?>
                                        <tr>
                                            <td class="ps-4 text-muted fw-medium">#<?php echo $org['id']; ?></td>
                                            <td>
                                                <div class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($org['name']); ?></div>
                                                <div class="text-muted small"><i class="fa-regular fa-envelope me-1"></i><?php echo htmlspecialchars($org['email']); ?></div>
                                            </td>
                                            <td>
                                                <div class="badge bg-light-<?php echo $stageColor; ?> text-<?php echo $stageColor; ?> border border-<?php echo $stageColor; ?>-subtle px-2 py-1 rounded-pill">
                                                    <i class="fa-solid <?php echo $stageIcon; ?> me-1"></i> <?php echo $stageText; ?>
                                                </div>
                                            </td>
                                            <td><span class="fw-medium text-secondary"><?php echo htmlspecialchars($org['plan_name'] ?: 'Trial Plan'); ?></span></td>
                                            <td style="width: 25%;">
                                                <div class="d-flex justify-content-between align-items-center mb-1 small">
                                                    <span class="fw-semibold text-dark"><?php echo $orgStorageMb; ?> MB</span>
                                                    <span class="text-muted"><?php echo number_format($orgRecords); ?> rows</span>
                                                </div>
                                                <div class="progress" style="height: 6px; border-radius: 10px;">
                                                    <div class="progress-bar bg-indigo" role="progressbar" style="width: <?php echo $storagePercentage; ?>%;" aria-valuenow="<?php echo $storagePercentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </td>
                                            <td class="text-end pe-4 text-secondary">
                                                <?php echo $org['valid_until'] ? date('d M Y', strtotime($org['valid_until'])) : '<span class="badge bg-dark">Lifetime</span>'; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
