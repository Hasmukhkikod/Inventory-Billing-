<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Authentication Page (Email-based)
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';
use App\Models\Auth;
use App\Models\Helpers;
use App\Models\Database;

$db = new Database();
$auth = new Auth($db);

// If already logged in, redirect to Dashboard
if ($auth->check()) {
    header("Location: index.php");
    exit;
}

$errorMessage = "";
$loginSuccess = false;

// Load company settings for branding
$compSettings = $db->query("SELECT company_name, company_logo FROM company_settings WHERE id = 1 LIMIT 1")->fetch();
$brandName = $compSettings['company_name'] ?? 'Grovixo';
$brandLogo = $compSettings['company_logo'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Helpers::verifyCsrf()) {
        $errorMessage = "Security Validation Failed. Please refresh and try again.";
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        
        // Rate limiting check
        $attempts = $_SESSION['login_attempts'] ?? 0;
        $lockoutTime = $_SESSION['login_lockout'] ?? 0;

        if ($lockoutTime > time()) {
            $wait = ceil(($lockoutTime - time()) / 60);
            $errorMessage = "Too many failed attempts. Please try again in {$wait} minutes.";
        } else {
            if (empty($email) || empty($password)) {
                $errorMessage = "Please enter both email and password.";
            } else {
                if ($auth->login($email, $password)) {
                    // Reset attempts on success
                    unset($_SESSION['login_attempts']);
                    unset($_SESSION['login_lockout']);
                    $loginSuccess = true;
                } else {
                    // Increment failed attempts
                    $_SESSION['login_attempts'] = $attempts + 1;
                    if ($_SESSION['login_attempts'] >= 5) {
                        $_SESSION['login_lockout'] = time() + (5 * 60); // 5 minutes lockout
                        $errorMessage = "Account locked due to 5 failed attempts. Try again in 5 minutes.";
                    } else {
                        $errorMessage = "Invalid email or password, or account is disabled. Attempt {$_SESSION['login_attempts']} of 5.";
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grovixo - System Authentication</title>
    <?php if ($loginSuccess): ?>
    <meta http-equiv="refresh" content="1.5;url=index.php">
    <?php endif; ?>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom stylesheet -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="auth-split-wrapper">

<?php if ($loginSuccess): ?>
<!-- Success Transition Preloader -->
<div class="auth-success-screen">
    <div class="auth-success-scene">
        <i class="fa-solid fa-box auth-float-box auth-float-box-1"></i>
        <i class="fa-solid fa-cube auth-float-box auth-float-box-2"></i>
        <i class="fa-solid fa-box-open auth-float-box auth-float-box-3"></i>

        <div class="auth-invoice-paper">
            <div class="auth-invoice-line auth-invoice-line-title"></div>
            <div class="auth-invoice-line" style="animation-delay: .38s;"></div>
            <div class="auth-invoice-line" style="animation-delay: .48s;"></div>
            <div class="auth-invoice-line auth-invoice-line-short" style="animation-delay: .58s;"></div>
            <div class="auth-invoice-stamp"><i class="fa-solid fa-check"></i></div>
        </div>
    </div>
    <div class="text-white mt-4 fw-bold auth-success-text" style="letter-spacing: 1px;">LOGIN SUCCESSFUL</div>
    <div class="text-white-50 mt-1 small auth-success-text" style="animation-delay: .15s;">Preparing your dashboard...</div>
</div>
<?php else: ?>

<!-- Initial Preloader -->
<div id="login-preloader" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #6366f1; z-index: 9999; display: flex; justify-content: center; align-items: center; flex-direction: column;">
    <div class="spinner-border text-white" role="status" style="width: 3rem; height: 3rem;">
        <span class="visually-hidden">Loading...</span>
    </div>
    <div class="text-white mt-3 fw-bold" style="letter-spacing: 1px;">INITIALIZING SECURE LOGIN...</div>
</div>

<!-- Brand / Feature Panel -->
<div class="auth-brand-panel d-none d-lg-flex">
    <div class="auth-brand-logo-row">
        <span class="auth-logo-badge">
            <?php if (!empty($brandLogo) && file_exists(UPLOAD_DIR . '/' . $brandLogo)): ?>
                <img src="<?php echo BASE_URL . '/uploads/' . $brandLogo; ?>" alt="Logo">
            <?php else: ?>
                <i class="fa-solid fa-boxes-stacked"></i>
            <?php endif; ?>
        </span>
        <span><?php echo Helpers::sanitize($brandName); ?></span>
    </div>

    <span class="auth-eyebrow"><i class="fa-solid fa-shield-halved"></i> Invoice &amp; Inventory Platform</span>
    <h1>Invoice &amp; Inventory,<br>Simplified.</h1>
    <p class="auth-lead">Everything you need to manage sales, stock, and finances &mdash; all in one workspace.</p>

    <ul class="auth-feature-list">
        <li><span class="icon-badge"><i class="fa-solid fa-file-invoice"></i></span> GST-ready invoicing &amp; billing</li>
        <li><span class="icon-badge"><i class="fa-solid fa-boxes-stacked"></i></span> Real-time stock &amp; inventory tracking</li>
        <li><span class="icon-badge"><i class="fa-solid fa-cart-shopping"></i></span> Purchases, sales &amp; returns</li>
        <li><span class="icon-badge"><i class="fa-solid fa-chart-line"></i></span> Insightful reports &amp; analytics</li>
    </ul>

    <div class="auth-mock-card" style="align-self: flex-end;">
        <div class="auth-mock-card-header">
            <span class="auth-mock-dot"></span>
            <span>INV-2026-0007</span>
            <span class="auth-mock-badge">Paid</span>
        </div>
        <div class="auth-mock-row"><span>Subtotal</span><span>&#8377;58,200</span></div>
        <div class="auth-mock-row"><span>GST (18%)</span><span>&#8377;10,476</span></div>
        <div class="auth-mock-row auth-mock-total"><span>Total</span><span>&#8377;68,676</span></div>
    </div>
</div>

<!-- Form Panel -->
<div class="auth-form-panel" id="login-content" style="opacity: 0; transform: translateY(14px); transition: opacity 0.5s ease, transform 0.5s ease;">
    <div class="auth-form-inner">
        <div class="mb-4">
            <div class="d-lg-none text-center mb-3">
                <?php if (!empty($brandLogo) && file_exists(UPLOAD_DIR . '/' . $brandLogo)): ?>
                    <img src="<?php echo BASE_URL . '/uploads/' . $brandLogo; ?>" alt="Logo" style="height: 44px; margin-bottom: 8px;">
                <?php else: ?>
                    <i class="fa-solid fa-boxes-stacked text-indigo" style="font-size: 2.25rem;"></i>
                <?php endif; ?>
                <h4 class="mb-1"><?php echo Helpers::sanitize($brandName); ?></h4>
            </div>
            <h3 class="mb-1 text-dark">Welcome back</h3>
            <p class="text-secondary small mb-0">Sign in to your <?php echo Helpers::sanitize($brandName); ?> account to continue.</p>
        </div>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger border-0 bg-light-danger small" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i> <?php echo Helpers::sanitize($errorMessage); ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" autocomplete="off">
            <?php echo Helpers::csrfField(); ?>

            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-regular fa-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email address" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2.5">
                <i class="fa-solid fa-right-to-bracket me-2"></i>Login
            </button>
        </form>
    </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Remove preloader once fully loaded
    window.addEventListener('load', function() {
        const preloader = document.getElementById('login-preloader');
        const content = document.getElementById('login-content');
        if (preloader) {
            preloader.style.opacity = '0';
            preloader.style.transition = 'opacity 0.4s ease';
            setTimeout(() => {
                preloader.style.display = 'none';
                content.style.opacity = '1';
                content.style.transform = 'translateY(0)';
            }, 400);
        }
    });
</script>
<?php endif; ?>
</body>
</html>
