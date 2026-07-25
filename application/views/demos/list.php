<div class="content-body">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Demo Accounts</h2>
        <span class="text-muted small">View all active and expired free trials</span>
    </div>

    <div class="card shadow-sm border-0">
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
