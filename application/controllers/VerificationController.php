<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Verification Controller
 */
namespace App\Controllers;

use App\Models\Auth;
use App\Models\Database;
use App\Models\Helpers;

class VerificationController {
    protected $db;
    protected $auth;

    public function __construct($db, $auth) {
        $this->db = $db;
        $this->auth = $auth;
    }

    public function verify() {
        $token = $_GET['token'] ?? ($_POST['token'] ?? '');
        $message = '';
        $error = '';
        $success = false;
        $showForm = false;

        if (empty($token)) {
            $message = "Invalid or missing verification token.";
        } else {
            $isDemo = isset($_GET['demo']) && $_GET['demo'] == '1' || isset($_POST['demo']) && $_POST['demo'] == '1';
            $db = $isDemo ? new Database('demo') : $this->db;

            // Find organization with this token
            $org = $db->query("SELECT * FROM organizations WHERE verification_token = ? LIMIT 1", [$token])->fetch();

            if ($org) {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $password = $_POST['password'] ?? '';
                    $confirm_password = $_POST['confirm_password'] ?? '';

                    if (empty($password) || strlen($password) < 8) {
                        $error = "Password must be at least 8 characters long.";
                        $showForm = true;
                    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
                        $error = "Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.";
                        $showForm = true;
                    } elseif ($password !== $confirm_password) {
                        $error = "Passwords do not match.";
                        $showForm = true;
                    } else {
                        // Update user password
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $db->query("UPDATE users SET password = ? WHERE org_id = ? AND email = ?", [$hashedPassword, $org['id'], $org['email']]);

                        // Update to verified
                        $db->query("UPDATE organizations SET is_verified = 1, verification_token = NULL WHERE id = ?", [$org['id']]);
                        $message = "Your password has been set and email verified successfully. You can now log in.";
                        $success = true;

                        // Send welcome email
                        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                        try {
                            $mail->isSMTP();
                            $mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
                            $mail->SMTPAuth   = true;
                            $mail->Username   = $_ENV['SMTP_USER'] ?? '';
                            $mail->Password   = $_ENV['SMTP_PASS'] ?? '';
                            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                            $mail->Port       = $_ENV['SMTP_PORT'] ?? 587;

                            $mail->setFrom($_ENV['SMTP_USER'] ?? 'infogrovixo@gmail.com', 'Grovixo');
                            $mail->addReplyTo($_ENV['SMTP_USER'] ?? 'infogrovixo@gmail.com', 'Grovixo Support');
                            $mail->addAddress($org['email'], $org['name']);

                            $loginLink = BASE_URL . "/login" . ($isDemo ? "?demo=1" : "");

                            $mail->isHTML(true);
                            $mail->Subject = 'Welcome to Grovixo - Registration Successful';
                            
                            $content = "<h2>Registration Successful!</h2>
                            <p>Hello {$org['name']},</p>
                            <p>Your email has been verified and your password has been set successfully.</p>
                            <p>You can now log in to your account using the following link:</p>
                            <div class='btn-container'>
                                <a href='{$loginLink}' class='btn'>Login to Grovixo</a>
                            </div>
                            <p>Or copy and paste this link in your browser:</p>
                            <p class='small-link'>{$loginLink}</p>";
                            
                            $mail->Body = Helpers::getEmailTemplate('Welcome to Grovixo - Registration Successful', $content);
                            $mail->AltBody = "Hello {$org['name']},\n\nYour email has been verified and your password has been set successfully.\n\nYou can now log in to your account using the following link:\n\n{$loginLink}\n\nThank you!";
                            
                            $mail->send();
                        } catch (\Exception $e) {
                            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
                        }
                    }
                } else {
                    $showForm = true;
                }
            } else {
                $message = "Invalid or expired verification token.";
            }
        }

        require_once __DIR__ . '/../views/auth/verify.php';
    }
}
