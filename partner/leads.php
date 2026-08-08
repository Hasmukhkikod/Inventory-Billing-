<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Distributor/Partner Portal - Submit & Track Leads
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

$leads = $db->query("SELECT * FROM distributor_leads WHERE distributor_id = ? ORDER BY created_at DESC", [$distributor['id']])->fetchAll();

$pageTitle = 'Leads';
$activeNav = 'leads';
require __DIR__ . '/../application/views/partner_layout_header.php';
?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="panel">
            <div class="panel-hd"><h5><i class="fa-solid fa-plus me-2 text-primary"></i>Add a New Lead</h5></div>
            <div class="p-3">
                <form id="lead-form" novalidate>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Contact Name *</label>
                        <input type="text" class="form-control" name="lead_name" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Business Name</label>
                        <input type="text" class="form-control" name="business_name">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Mobile Number *</label>
                        <input type="tel" class="form-control" name="mobile" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Email</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Notes</label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="Anything our team should know before reaching out"></textarea>
                    </div>
                    <p class="text-danger small" id="lead-form-error" style="display:none;"></p>
                    <button type="submit" class="btn btn-primary w-100" id="lead-submit-btn">Submit Lead →</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="panel">
            <div class="panel-hd"><h5><i class="fa-solid fa-bullseye me-2 text-primary"></i>Your Submitted Leads</h5></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="ps-3">Lead</th>
                            <th>Contact</th>
                            <th>Submitted</th>
                            <th class="pe-3">Status</th>
                        </tr>
                    </thead>
                    <tbody id="leads-tbody">
                        <?php if (empty($leads)): ?>
                            <tr id="leads-empty-row"><td colspan="4" class="text-center text-secondary py-5">You haven't submitted any leads yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($leads as $l): ?>
                                <?php
                                    $statusClass = match ($l['status']) {
                                        'CONTACTED' => 'bg-warning-subtle text-warning',
                                        'CONVERTED' => 'bg-success-subtle text-success',
                                        'REJECTED' => 'bg-danger-subtle text-danger',
                                        default => 'bg-primary-subtle text-primary',
                                    };
                                ?>
                                <tr>
                                    <td class="ps-3">
                                        <strong><?php echo Helpers::sanitize($l['lead_name']); ?></strong>
                                        <?php if (!empty($l['business_name'])): ?><div class="text-secondary small"><?php echo Helpers::sanitize($l['business_name']); ?></div><?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="small"><?php echo Helpers::sanitize($l['mobile'] ?: '-'); ?></div>
                                        <div class="small text-secondary"><?php echo Helpers::sanitize($l['email'] ?: ''); ?></div>
                                    </td>
                                    <td class="small text-secondary"><?php echo date('d M Y', strtotime($l['created_at'])); ?></td>
                                    <td class="pe-3"><span class="badge badge-status <?php echo $statusClass; ?>"><?php echo Helpers::sanitize($l['status']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('lead-form').addEventListener('submit', async function (e) {
    e.preventDefault();
    const form = this;
    const err = document.getElementById('lead-form-error');
    const btn = document.getElementById('lead-submit-btn');
    err.style.display = 'none';

    if (!form.checkValidity()) {
        err.textContent = 'Please fill in the required fields (name and mobile).';
        err.style.display = 'block';
        form.reportValidity();
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Submitting...';

    try {
        const formData = new FormData(form);
        formData.append('action', 'lead_add');
        formData.append('csrf_token', CSRF_TOKEN);
        const res = await fetch(BASE_URL + '/api/distributor_portal.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.status) {
            Swal.fire({ icon: 'success', title: 'Lead submitted!', text: data.message, confirmButtonColor: '#3b5bff' });
            form.reset();
            window.location.reload();
        } else {
            err.textContent = data.message || 'Could not submit the lead.';
            err.style.display = 'block';
        }
    } catch (error) {
        err.textContent = 'A network error occurred. Please try again.';
        err.style.display = 'block';
    } finally {
        btn.disabled = false;
        btn.textContent = 'Submit Lead →';
    }
});
</script>

<?php require __DIR__ . '/../application/views/partner_layout_footer.php'; ?>
