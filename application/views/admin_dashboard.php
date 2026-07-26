<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Super Admin Dashboard View Page
 */
?>
<section class="dashboard-shell">
    <div class="dashboard-hero mb-4" style="background: radial-gradient(circle at 90% 10%, #2b3d8c 0%, transparent 40%), var(--navy);">
        <div class="dashboard-hero-content">
            <div>
                <span class="dashboard-eyebrow" style="color: #aebdff;">System Administrator Command Center</span>
                <h1>Platform Overview</h1>
                <p>Manage organizations, monitor system health, and track data storage usage across all tenants.</p>
            </div>
            <div class="dashboard-hero-actions">
                <a href="<?php echo BASE_URL; ?>/organizations/index" class="btn btn-light">
                    <i class="fa-solid fa-building me-2"></i>Manage Organizations
                </a>
                <a href="<?php echo BASE_URL; ?>/plans/index" class="btn btn-outline-light">
                    <i class="fa-solid fa-tags me-2"></i>Manage Plans
                </a>
            </div>
        </div>

        <div class="dashboard-hero-metrics">
            <div>
                <span>Total Tenants</span>
                <strong><?php echo number_format($data['total_orgs']); ?></strong>
            </div>
            <div>
                <span>Active Trials</span>
                <strong class="text-success"><?php echo number_format($data['active_orgs']); ?></strong>
            </div>
            <div>
                <span>Est. Storage Used</span>
                <strong><?php echo $data['total_storage_mb']; ?> MB</strong>
            </div>
        </div>
    </div>

    <div class="dashboard-kpi-grid mb-4">
        <div class="dashboard-kpi-card accent-indigo">
            <div class="dashboard-kpi-icon"><i class="fa-solid fa-server"></i></div>
            <div>
                <span>Total Data Points</span>
                <strong><?php echo number_format($data['total_system_records']); ?></strong>
                <small>Records across all tenants</small>
            </div>
        </div>
        <div class="dashboard-kpi-card accent-emerald">
            <div class="dashboard-kpi-icon"><i class="fa-solid fa-building-circle-check"></i></div>
            <div>
                <span>Active Organizations</span>
                <strong><?php echo number_format($data['active_orgs']); ?></strong>
                <small>Currently active on the platform</small>
            </div>
        </div>
        <div class="dashboard-kpi-card accent-amber">
            <div class="dashboard-kpi-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
            <div>
                <span>Expired Trials</span>
                <strong><?php echo number_format($data['expired_orgs']); ?></strong>
                <small>Trials that have ended</small>
            </div>
        </div>
        <div class="dashboard-kpi-card accent-rose">
            <div class="dashboard-kpi-icon"><i class="fa-solid fa-database"></i></div>
            <div>
                <span>Est. Platform Size</span>
                <strong><?php echo $data['total_storage_mb']; ?> MB</strong>
                <small>Calculated system storage</small>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="panel-card dashboard-panel h-100">
                <div class="panel-header d-flex justify-content-between align-items-center">
                    <div>
                        <span class="panel-kicker">Tenant Management</span>
                        <h5 class="mb-0 text-dark"><i class="fa-solid fa-building me-2 text-indigo"></i>Recent Organizations</h5>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/organizations/index" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="panel-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 dashboard-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Organization Name</th>
                                    <th>Email ID</th>
                                    <th>Current Plan</th>
                                    <th>Valid Until</th>
                                    <th>Storage (Est.)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($data['recent_orgs'])): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No organizations registered yet.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($data['recent_orgs'] as $org): ?>
                                        <?php 
                                            // Storage calculation
                                            $orgRecords = $data['storage_map'][$org['id']] ?? 0;
                                            $orgStorageMb = number_format(($orgRecords * 2.5) / 1024, 2);
                                            
                                            // Status Badge
                                            $isVerified = isset($org['is_verified']) ? (int)$org['is_verified'] : 0;
                                            $isApproved = isset($org['is_approved']) ? (int)$org['is_approved'] : 0;
                                            
                                            if ($isVerified === 0) {
                                                $statusBadge = '<span class="badge bg-warning text-dark" title="Waiting for password set">Awaiting Verification</span>';
                                            } elseif ($isVerified === 1 && $isApproved === 0) {
                                                $statusBadge = '<span class="badge bg-info text-dark" title="Needs admin approval">Awaiting Approval</span>';
                                            } else {
                                                if ($org['status'] === 'ACTIVE') {
                                                    if ($org['valid_until'] && strtotime($org['valid_until']) < time()) {
                                                        $statusBadge = '<span class="badge bg-warning text-dark">Expired</span>';
                                                    } else {
                                                        $statusBadge = '<span class="badge bg-success">Active</span>';
                                                    }
                                                } else {
                                                    $statusBadge = '<span class="badge bg-danger">Inactive</span>';
                                                }
                                            }
                                        ?>
                                        <tr>
                                            <td class="text-muted">#<?php echo $org['id']; ?></td>
                                            <td class="fw-bold text-dark"><?php echo htmlspecialchars($org['name']); ?></td>
                                            <td><?php echo htmlspecialchars($org['email']); ?></td>
                                            <td><?php echo htmlspecialchars($org['plan_name'] ?: 'Trial Plan'); ?></td>
                                            <td><?php echo $org['valid_until'] ? date('d M Y', strtotime($org['valid_until'])) : 'Lifetime'; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="me-2"><?php echo $orgStorageMb; ?> MB</span>
                                                    <small class="text-muted">(<?php echo number_format($orgRecords); ?> rows)</small>
                                                </div>
                                            </td>
                                            <td><?php echo $statusBadge; ?></td>
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
