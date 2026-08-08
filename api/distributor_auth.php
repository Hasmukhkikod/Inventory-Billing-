<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Distributor/Partner Portal - Registration, Email OTP Verification, Login
 */
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
use App\Models\Helpers;
use App\Models\Database;
use App\Models\DistributorAuth;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helpers::jsonResponse(false, 'Invalid request method');
}
if (!Helpers::verifyCsrf()) {
    Helpers::jsonResponse(false, 'Security Validation Failed. Please refresh and try again.');
}

$db = new Database();
$action = $_POST['action'] ?? '';

function sendDistributorOtpEmail(string $toEmail, string $toName, string $otp): bool {
    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER'] ?? '';
        $mail->Password   = $_ENV['SMTP_PASS'] ?? '';
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $_ENV['SMTP_PORT'] ?? 587;

        $mail->setFrom($_ENV['SMTP_USER'] ?? 'infogrovixo@gmail.com', 'Grovixo Partners');
        $mail->addReplyTo($_ENV['SMTP_USER'] ?? 'infogrovixo@gmail.com', 'Grovixo Support');
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = 'Verify your Grovixo Partner account';
        $content = "<h2>Verify your email</h2>
        <p>Hello {$toName},</p>
        <p>Use the code below to verify your email and activate your Grovixo Partner account. This code expires in 10 minutes.</p>
        <div style='text-align:center; margin:30px 0;'>
            <span style='display:inline-block; padding:16px 32px; background:#f4f7fa; border-radius:10px; font-size:32px; font-weight:700; letter-spacing:8px; color:#12214f;'>{$otp}</span>
        </div>
        <p>If you did not request this, you can safely ignore this email.</p>";

        $mail->Body = Helpers::getEmailTemplate('Verify your Grovixo Partner account', $content);
        $mail->AltBody = "Hello {$toName},\n\nYour Grovixo Partner verification code is: {$otp}\n\nThis code expires in 10 minutes.";
        $mail->send();
        return true;
    } catch (\Exception $e) {
        error_log("Distributor OTP email could not be sent. Mailer Error: " . $e->getMessage());
        return false;
    }
}

switch ($action) {
    case 'register':
        $name = trim($_POST['name'] ?? '');
        $businessName = trim($_POST['business_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');

        if (empty($name) || empty($email) || empty($mobile) || empty($password)) {
            Helpers::jsonResponse(false, 'Please fill in all required fields.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Helpers::jsonResponse(false, 'Please enter a valid email address.');
        }
        if ($password !== $confirmPassword) {
            Helpers::jsonResponse(false, 'Passwords do not match.');
        }
        if (strlen($password) < 6) {
            Helpers::jsonResponse(false, 'Password must be at least 6 characters.');
        }

        try {
            $existing = $db->query("SELECT id FROM distributors WHERE email = ? OR mobile = ? LIMIT 1", [$email, $mobile])->fetch();
            if ($existing) {
                Helpers::jsonResponse(false, 'An account with this email or mobile number already exists. Please log in instead.');
            }

            $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $otpExpiresAt = date('Y-m-d H:i:s', time() + 600); // 10 minutes
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $distributorId = $db->insert("
                INSERT INTO distributors (name, business_name, email, mobile, password, status, is_verified, otp_code, otp_expires_at, otp_attempts)
                VALUES (?, ?, ?, ?, ?, 'PENDING', 0, ?, ?, 0)
            ", [$name, $businessName ?: null, $email, $mobile, $hashedPassword, $otp, $otpExpiresAt]);

            if (!sendDistributorOtpEmail($email, $name, $otp)) {
                $db->query("DELETE FROM distributors WHERE id = ?", [$distributorId]);
                Helpers::jsonResponse(false, 'Could not send the verification email. Please try again later.');
            }

            $_SESSION['pending_distributor_id'] = $distributorId;
            Helpers::logActivity($db, "partners", "New partner registration: $email", $distributorId);
            Helpers::jsonResponse(true, 'A 6-digit verification code has been sent to your email.', ['otp_required' => true]);
        } catch (\Exception $e) {
            error_log("Distributor registration error: " . $e->getMessage());
            Helpers::jsonResponse(false, 'Something went wrong. Please try again later.');
        }
        break;

    case 'verify_otp':
        $otp = trim($_POST['otp'] ?? '');
        $pendingId = $_SESSION['pending_distributor_id'] ?? 0;

        if (!$pendingId) {
            Helpers::jsonResponse(false, 'Your verification session has expired. Please register again.');
        }
        if (empty($otp)) {
            Helpers::jsonResponse(false, 'Please enter the verification code.');
        }

        try {
            $distributor = $db->query("SELECT id, name, email, otp_code, otp_expires_at, otp_attempts, is_verified FROM distributors WHERE id = ? LIMIT 1", [$pendingId])->fetch();
            if (!$distributor) {
                Helpers::jsonResponse(false, 'Account not found. Please register again.');
            }
            if ($distributor['is_verified']) {
                Helpers::jsonResponse(true, 'Already verified. You can log in now.');
            }
            if ((int)$distributor['otp_attempts'] >= 5) {
                Helpers::jsonResponse(false, 'Too many incorrect attempts. Please request a new code.');
            }
            if (empty($distributor['otp_expires_at']) || strtotime($distributor['otp_expires_at']) < time()) {
                Helpers::jsonResponse(false, 'This verification code has expired. Please request a new one.');
            }
            if (!hash_equals((string)$distributor['otp_code'], $otp)) {
                $db->query("UPDATE distributors SET otp_attempts = otp_attempts + 1 WHERE id = ?", [$distributor['id']]);
                Helpers::jsonResponse(false, 'Incorrect verification code. Please try again.');
            }

            $db->query("UPDATE distributors SET is_verified = 1, status = 'ACTIVE', otp_code = NULL, otp_expires_at = NULL, otp_attempts = 0 WHERE id = ?", [$distributor['id']]);
            Helpers::logActivity($db, "partners", "Partner verified email: " . $distributor['email'], $distributor['id']);

            // Verification completes the sign-up flow, so log them straight in.
            unset($_SESSION['pending_distributor_id']);
            $_SESSION['distributor_id'] = $distributor['id'];
            $_SESSION['distributor_name'] = $distributor['name'];
            $_SESSION['distributor_email'] = $distributor['email'];
            $_SESSION['distributor_last_activity'] = time();

            Helpers::jsonResponse(true, 'Your account is verified! Taking you to your dashboard...');
        } catch (\Exception $e) {
            error_log("Distributor OTP verify error: " . $e->getMessage());
            Helpers::jsonResponse(false, 'Something went wrong. Please try again later.');
        }
        break;

    case 'resend_otp':
        $pendingId = $_SESSION['pending_distributor_id'] ?? 0;
        if (!$pendingId) {
            Helpers::jsonResponse(false, 'Your verification session has expired. Please register again.');
        }

        try {
            $distributor = $db->query("SELECT id, name, email, is_verified FROM distributors WHERE id = ? LIMIT 1", [$pendingId])->fetch();
            if (!$distributor || $distributor['is_verified']) {
                Helpers::jsonResponse(false, 'This account is already verified or no longer exists.');
            }

            $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $otpExpiresAt = date('Y-m-d H:i:s', time() + 600);
            $db->query("UPDATE distributors SET otp_code = ?, otp_expires_at = ?, otp_attempts = 0 WHERE id = ?", [$otp, $otpExpiresAt, $distributor['id']]);

            if (!sendDistributorOtpEmail($distributor['email'], $distributor['name'], $otp)) {
                Helpers::jsonResponse(false, 'Could not send the verification email. Please try again later.');
            }

            Helpers::jsonResponse(true, 'A new verification code has been sent to your email.');
        } catch (\Exception $e) {
            error_log("Distributor OTP resend error: " . $e->getMessage());
            Helpers::jsonResponse(false, 'Something went wrong. Please try again later.');
        }
        break;

    case 'login':
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            Helpers::jsonResponse(false, 'Please enter both email and password.');
        }

        try {
            $distributor = $db->query("SELECT * FROM distributors WHERE (email = ? OR mobile = ?) AND deleted_at IS NULL LIMIT 1", [$email, $email])->fetch();

            if (!$distributor || !password_verify($password, $distributor['password'])) {
                Helpers::jsonResponse(false, 'Invalid email or password.');
            }
            if (!$distributor['is_verified']) {
                Helpers::jsonResponse(false, 'Please verify your email before logging in.', ['needs_verification' => true]);
            }
            if ($distributor['status'] !== 'ACTIVE') {
                Helpers::jsonResponse(false, 'Your partner account is not active. Please contact support.');
            }

            $_SESSION['distributor_id'] = $distributor['id'];
            $_SESSION['distributor_name'] = $distributor['name'];
            $_SESSION['distributor_email'] = $distributor['email'];
            $_SESSION['distributor_last_activity'] = time();

            Helpers::logActivity($db, "partners", "Partner login: " . $distributor['email'], $distributor['id']);
            Helpers::jsonResponse(true, 'Login successful.');
        } catch (\Exception $e) {
            error_log("Distributor login error: " . $e->getMessage());
            Helpers::jsonResponse(false, 'Something went wrong. Please try again later.');
        }
        break;

    case 'logout':
        $distAuth = new DistributorAuth($db);
        $distAuth->logout();
        Helpers::jsonResponse(true, 'Logged out.');
        break;

    default:
        Helpers::jsonResponse(false, 'Action not found: ' . $action);
}
