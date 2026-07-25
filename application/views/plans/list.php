<div class="content-body">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Subscription Plans</h2>
        <a href="<?php echo BASE_URL; ?>/plans/form" class="btn btn-primary shadow-sm">
            <i class="fa-solid fa-plus me-2"></i>New Plan
        </a>
    </div>

    <div class="card shadow-sm border-0">
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
                            <td><?php echo number_format($p['price'], 2); ?></td>
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
