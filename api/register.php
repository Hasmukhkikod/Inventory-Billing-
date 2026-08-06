<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * API - Registration
 */
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../application/Models/Database.php';
require_once __DIR__ . '/../application/Models/Auth.php';
require_once __DIR__ . '/../application/Models/Helpers.php';

use App\Models\Database;
use App\Models\Helpers;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['demo']) && $_GET['demo'] == 1) {
    $_SESSION['is_demo'] = true;
}

$db = new Database();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $password = bin2hex(random_bytes(8)); // Generate a temporary random password

    if (empty($firstName) || empty($lastName) || empty($email) || empty($mobile)) {
        Helpers::jsonResponse(false, "All fields are required.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        Helpers::jsonResponse(false, "Invalid email address.");
    }

    // Check if email or mobile already exists
    $existingUser = $db->query("SELECT id FROM users WHERE email = ? OR mobile = ? LIMIT 1", [$email, $mobile])->fetch();
    $existingOrg = $db->query("SELECT id FROM organizations WHERE email = ? OR phone = ? LIMIT 1", [$email, $mobile])->fetch();
    
    // If org exists but user doesn't, it's an orphaned record from a previous crash. Clean it up!
    if ($existingOrg && !$existingUser) {
        $db->query("DELETE FROM organizations WHERE id = ?", [$existingOrg['id']]);
        $existingOrg = false;
    }

    if ($existingUser || $existingOrg) {
        Helpers::jsonResponse(false, "This email or mobile is already registered.");
    }

    $orgName = $firstName . " " . $lastName . "'s Business";
    $plan_id = 1; // Assign to Default Plan so they have access to features
    $start_date = date('Y-m-d');
    $valid_until = date('Y-m-d', strtotime('+15 days'));
    
    // Generate verification token
    $verification_token = bin2hex(random_bytes(32));

    try {
        $db->getConnection()->beginTransaction();

        // Create Organization
        $newOrgId = $db->insert(
            "INSERT INTO organizations (name, email, phone, plan_id, start_date, valid_until, status, is_verified, verification_token, is_approved) VALUES (?, ?, '', ?, ?, ?, 'ACTIVE', 0, ?, 1)", 
            [$orgName, $email, $plan_id, $start_date, $valid_until, $verification_token]
        );

        // Create Admin Role for new organization
        $newRoleId = $db->insert(
            "INSERT INTO roles (role_name, description, status, created_by, org_id) VALUES (?, ?, ?, ?, ?)",
            ['Admin', 'Default Admin Role', 'ACTIVE', 1, $newOrgId]
        );
        
        // Copy default Admin permissions (assuming role_id = 2 is the template)
        $perms = $db->query("SELECT permission_id FROM role_permissions WHERE role_id = 2")->fetchAll();
        foreach ($perms as $p) {
            $db->insert(
                "INSERT INTO role_permissions (role_id, permission_id, org_id) VALUES (?, ?, ?)", 
                [$newRoleId, $p['permission_id'], $newOrgId]
            );
        }

        // Create Admin User
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $db->insert(
            "INSERT INTO users (name, email, mobile, password, role_id, org_id, status) VALUES (?, ?, ?, ?, ?, ?, 'ACTIVE')",
            [$firstName . " " . $lastName, $email, $mobile, $hashedPassword, $newRoleId, $newOrgId]
        );

        $db->getConnection()->commit();

        // Send verification email
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
            $mail->addAddress($email, $firstName . " " . $lastName);

            $demoParam = (isset($_GET['demo']) && $_GET['demo'] == 1) ? '&demo=1' : '';
            $verifyLink = BASE_URL . "/verify?token=" . $verification_token . $demoParam;

            $mail->isHTML(true);
            $mail->Subject = 'Verify your Grovixo Registration & Set Password';
            
            $content = "<h2>Welcome to Grovixo!</h2>
            <p>Hello {$firstName},</p>
            <p>Your 15-day free trial has been created successfully.</p>
            <p>Please click the button below to verify your email address and set your password before logging in:</p>
            <div class='btn-container'>
                <a href='{$verifyLink}' class='btn'>Verify Email & Set Password</a>
            </div>
            <p>Or copy and paste this link in your browser:</p>
            <p class='small-link'>{$verifyLink}</p>";
            
            $mail->Body = Helpers::getEmailTemplate('Verify your Grovixo Registration & Set Password', $content);
            $mail->AltBody = "Hello {$firstName},\n\nWelcome to Grovixo! Your 15-day free trial has been created.\n\nPlease copy and paste the link below in your browser to verify your email address and set your password before logging in:\n\n{$verifyLink}\n\nThank you!";
            
            $mail->send();
        } catch (\Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            // Delete the created user and org since email failed and they can't verify
            $db->query("DELETE FROM users WHERE org_id = ?", [$newOrgId]);
            $db->query("DELETE FROM organizations WHERE id = ?", [$newOrgId]);
            Helpers::jsonResponse(false, "Failed to send verification email. Please try again later or contact support.");
        }

        Helpers::jsonResponse(true, "Your 15-day trial is ready. Please check your email to verify your account!");

    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            Helpers::jsonResponse(false, "This email is already registered.");
        }
        Helpers::jsonResponse(false, "Error: " . $e->getMessage());
    }
} else {
    Helpers::jsonResponse(false, "Invalid request method.");
}
