<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * API - Forgot Password Request
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

$email = trim($_POST['email'] ?? '');
if (empty($email)) {
    Helpers::jsonResponse(false, 'Email address is required.');
}

$db = new Database();

try {
    // Check if user exists and is active
    $stmt = $db->query("SELECT id, name FROM users WHERE email = ? AND status = 'ACTIVE' LIMIT 1", [$email]);
    $user = $stmt->fetch();

    if (!$user) {
        Helpers::jsonResponse(false, 'No account found with that email address.');
    }

    // Generate secure token
    $token = bin2hex(random_bytes(32));
    $request_ip = $_SERVER['REMOTE_ADDR'];
    $expires_at = date('Y-m-d H:i:s', time() + 3600); // 1 hour from now

    // Update user with reset info
    $db->query("UPDATE users SET reset_token = ?, reset_request_ip = ?, reset_expires_at = ? WHERE id = ?", [
        $token, $request_ip, $expires_at, $user['id']
    ]);
} catch (\Exception $e) {
    error_log("Forgot password DB error: " . $e->getMessage());
    Helpers::jsonResponse(false, 'Something went wrong. Please try again later.');
}

// Send email
$mail = new \PHPMailer\PHPMailer\PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['SMTP_USER'] ?? '';
    $mail->Password   = $_ENV['SMTP_PASS'] ?? '';
    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $_ENV['SMTP_PORT'] ?? 587;

    $mail->setFrom($_ENV['SMTP_USER'] ?? 'infogrovixo@gmail.com', 'Grovixo Support');
    $mail->addReplyTo($_ENV['SMTP_USER'] ?? 'infogrovixo@gmail.com', 'Grovixo Support');
    $mail->addAddress($email, $user['name']);

    $resetLink = BASE_URL . "/reset_password?token=" . $token;

    $mail->isHTML(true);
    $mail->Subject = 'Reset Your Password - Grovixo';
    
    $content = "<h2>Password Reset Request</h2>
    <p>Hello {$user['name']},</p>
    <p>We received a request to reset your password for your Grovixo account.</p>
    <p>If you made this request, please click the button below to set a new password. This link will expire in 1 hour.</p>
    <div class='btn-container'>
        <a href='{$resetLink}' class='btn'>Reset Password</a>
    </div>
    <p>Or copy and paste this link in your browser:</p>
    <p class='small-link'>{$resetLink}</p>
    <p><strong>Note:</strong> For security reasons, you must reset your password from the same network/IP address from which you requested it.</p>
    <br>
    <p>If you did not request a password reset, you can safely ignore this email.</p>";
    
    $mail->Body = Helpers::getEmailTemplate('Reset Your Password - Grovixo', $content);
    $mail->AltBody = "Hello {$user['name']},\n\nWe received a request to reset your password for your Grovixo account.\n\nPlease copy and paste the following link into your browser to reset your password:\n\n{$resetLink}\n\nThis link will expire in 1 hour.\n\nIf you did not request a password reset, you can safely ignore this email.\n\nThank you!";
    
    $mail->send();
    
    Helpers::logActivity($db, "auth", "Password reset requested for: " . $email);
} catch (\Exception $e) {
    error_log("Password reset email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    Helpers::jsonResponse(false, 'Failed to send reset email. Please contact support.');
}

Helpers::jsonResponse(true, 'A password reset link has been sent to your email address.');
