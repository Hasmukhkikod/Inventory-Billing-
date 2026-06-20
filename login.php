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
                    header("Location: index.php");
                    exit;
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
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom stylesheet -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="auth-wrapper">

<div class="auth-card">
    <div class="text-center mb-4">
        <h3 class="mb-1 text-dark"><i class="fa-solid fa-boxes-stacked text-indigo me-2"></i>Grovixo</h3>
        <p class="text-secondary small">Invoice & Inventory Management System</p>
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
            <i class="fa-solid fa-right-to-bracket me-2"></i>Authenticate Securely
        </button>
    </form>
    
    <div class="text-center mt-4">
        <span class="text-muted small">Powered by Grovixo IIMS v2.0</span>
    </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
