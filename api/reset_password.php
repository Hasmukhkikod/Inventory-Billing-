<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * API - Reset Password Submit
 */
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
use App\Models\Auth;
use App\Models\Helpers;
use App\Models\Database;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helpers::jsonResponse(false, 'Invalid request method');
}

if (!Helpers::verifyCsrf()) {
    Helpers::jsonResponse(false, 'Security Validation Failed. Please refresh and try again.');
}

$token = $_POST['token'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($token) || empty($new_password)) {
    Helpers::jsonResponse(false, 'Missing required fields.');
}

if ($new_password !== $confirm_password) {
    Helpers::jsonResponse(false, 'Passwords do not match.');
}

if (strlen($new_password) < 6) {
    Helpers::jsonResponse(false, 'Password must be at least 6 characters.');
}

$db = new Database();

try {
    // Verify token and expiry
    $stmt = $db->query("SELECT id, name, password, email, reset_request_ip FROM users WHERE reset_token = ? AND reset_expires_at > NOW() LIMIT 1", [$token]);
    $user = $stmt->fetch();

    if (!$user) {
        Helpers::jsonResponse(false, 'This password reset link is invalid or has expired.');
    }

    // Security Check 1: Strict IP Match
    if ($user['reset_request_ip'] !== $_SERVER['REMOTE_ADDR']) {
        Helpers::logActivity($db, "auth", "Failed reset password due to IP mismatch for user ID: " . $user['id']);
        Helpers::jsonResponse(false, 'Security Error: Your current network/IP address does not match the one used to request this reset link.');
    }

    // Security Check 2: Check if new password is the same as old password
    if (password_verify($new_password, $user['password'])) {
        Helpers::jsonResponse(false, 'Your new password cannot be the same as your old password.');
    }

    $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

    // The Super Admin account gets an extra email-OTP step before the new
    // password takes effect - the old password keeps working in the meantime
    // so a lost/delayed OTP email can never fully lock out the sole admin.
    if ((int)$user['id'] === 1) {
        $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $otpExpiresAt = date('Y-m-d H:i:s', time() + 600); // 10 minutes

        $db->query("UPDATE users SET otp_code = ?, otp_expires_at = ?, otp_attempts = 0, pending_password = ? WHERE id = ?", [
            $otp, $otpExpiresAt, $hashedPassword, $user['id']
        ]);

        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USER'] ?? '';
            $mail->Password   = $_ENV['SMTP_PASS'] ?? '';
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $_ENV['SMTP_PORT'] ?? 587;

            $mail->setFrom($_ENV['SMTP_USER'] ?? 'infogrovixo@gmail.com', 'Grovixo Security');
            $mail->addReplyTo($_ENV['SMTP_USER'] ?? 'infogrovixo@gmail.com', 'Grovixo Support');
            $mail->addAddress($user['email'], $user['name']);

            $mail->isHTML(true);
            $mail->Subject = 'Your Grovixo Admin Verification Code';
            $content = "<h2>Verify It's You</h2>
            <p>Hello {$user['name']},</p>
            <p>Use the code below to confirm your password change for the Grovixo Super Admin account. This code expires in 10 minutes.</p>
            <div style='text-align:center; margin:30px 0;'>
                <span style='display:inline-block; padding:16px 32px; background:#f4f7fa; border-radius:10px; font-size:32px; font-weight:700; letter-spacing:8px; color:#12214f;'>{$otp}</span>
            </div>
            <p>If you did not request this password change, please ignore this email and your password will remain unchanged.</p>";

            $mail->Body = Helpers::getEmailTemplate('Your Grovixo Admin Verification Code', $content);
            $mail->AltBody = "Hello {$user['name']},\n\nYour Grovixo admin verification code is: {$otp}\n\nThis code expires in 10 minutes. If you did not request this, you can ignore this email.";
            $mail->send();
        } catch (\Exception $e) {
            error_log("OTP email could not be sent. Mailer Error: " . $e->getMessage());
            Helpers::jsonResponse(false, 'Could not send the verification code email. Please try again later.');
        }

        Helpers::logActivity($db, "auth", "Password reset OTP sent for Super Admin (user ID 1).");
        Helpers::jsonResponse(true, 'A 6-digit verification code has been sent to your email.', ['otp_required' => true]);
    }

    // Regular users: update password and clear reset fields immediately
    $db->query("UPDATE users SET password = ?, reset_token = NULL, reset_request_ip = NULL, reset_expires_at = NULL WHERE id = ?", [
        $hashedPassword, $user['id']
    ]);

    Helpers::logActivity($db, "auth", "Password reset successfully for user ID: " . $user['id']);
} catch (\Exception $e) {
    error_log("Reset password DB error: " . $e->getMessage());
    Helpers::jsonResponse(false, 'Something went wrong. Please try again later.');
}

Helpers::jsonResponse(true, 'Your password has been successfully reset.');
