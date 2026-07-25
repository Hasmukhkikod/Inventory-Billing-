<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * API - Organizations
 */
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../application/Models/Database.php';
require_once __DIR__ . '/../application/Models/Auth.php';
require_once __DIR__ . '/../application/Models/Helpers.php';

use App\Models\Database;
use App\Models\Auth;
use App\Models\Helpers;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = new Database();
$auth = new Auth($db);

// Only Super Admin can access
if (!$auth->check() || $_SESSION['user_id'] != 1) {
    Helpers::jsonResponse(false, "Unauthorized access.");
}

$action = $_GET['action'] ?? '';

if ($action === 'save') {
    Helpers::verifyCsrf();
    
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $plan_id = (int)($_POST['plan_id'] ?? 0);
    $start_date = $_POST['start_date'] ?? null;
    $valid_until = $_POST['valid_until'] ?? null;
    $status = $_POST['status'] ?? 'ACTIVE';

    if (empty($name)) {
        Helpers::jsonResponse(false, "Organization name is required.");
    }
    
    if (empty($start_date)) {
        $start_date = null;
    }
    
    if (empty($valid_until)) {
        $valid_until = null;
    }

    try {
        if ($id > 0) {
            $db->query("UPDATE organizations SET name = ?, email = ?, phone = ?, plan_id = ?, start_date = ?, valid_until = ?, status = ? WHERE id = ?", 
                       [$name, $email, $phone, $plan_id, $start_date, $valid_until, $status, $id]);
            Helpers::logActivity($db, "organizations", "Updated organization #$id", $id);
            Helpers::jsonResponse(true, "Organization updated successfully.");
        } else {
            $verification_token = bin2hex(random_bytes(32));
            
            $newId = $db->insert("INSERT INTO organizations (name, email, phone, plan_id, start_date, valid_until, status, is_verified, verification_token) VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?)", 
                                 [$name, $email, $phone, $plan_id, $start_date, $valid_until, $status, $verification_token]);
            Helpers::logActivity($db, "organizations", "Created new organization #$newId", $newId);
            
            // Auto create an admin user for this org
            $defaultPassword = password_hash('123456', PASSWORD_DEFAULT);
            $db->insert("INSERT INTO users (name, email, phone, password, role_id, org_id, status) VALUES (?, ?, ?, ?, ?, ?, ?)",
                ['Admin', $email ?: 'admin@org'.$newId.'.com', $phone, $defaultPassword, 2, $newId, 'ACTIVE']);
                
            // Send verification email
            if ($email) {
                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $_ENV['SMTP_USER'] ?? '';
                    $mail->Password   = $_ENV['SMTP_PASS'] ?? '';
                    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = $_ENV['SMTP_PORT'] ?? 587;

                    $mail->setFrom($_ENV['SMTP_USER'] ?? 'infogrovixo@gmail.com', 'IIMS System');
                    $mail->addAddress($email, $name);

                    $verifyLink = BASE_URL . "/verify?token=" . $verification_token;

                    $mail->isHTML(true);
                    $mail->Subject = 'Verify your Organization Registration';
                    $mail->Body    = "Hello {$name},<br><br>Your organization has been registered successfully. Please click the link below to verify your email address before logging in:<br><br><a href='{$verifyLink}'>{$verifyLink}</a><br><br>Thank you!";
                    $mail->AltBody = "Hello {$name},\n\Your organization has been registered successfully. Please visit the link below to verify your email address:\n\n{$verifyLink}\n\nThank you!";

                    $mail->send();
                } catch (\Exception $e) {
                    error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
                }
            }
                
            Helpers::jsonResponse(true, "Organization created. A verification email has been sent.");
        }
    } catch (Exception $e) {
        Helpers::jsonResponse(false, "Error: " . $e->getMessage());
    }
}

Helpers::jsonResponse(false, "Invalid action.");
