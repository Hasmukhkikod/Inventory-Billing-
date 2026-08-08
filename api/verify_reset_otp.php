<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * API - Verify Super Admin password-reset OTP
 */
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
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
$otp = trim($_POST['otp'] ?? '');

if (empty($token) || empty($otp)) {
    Helpers::jsonResponse(false, 'Missing required fields.');
}

$db = new Database();

try {
    $stmt = $db->query("SELECT id, otp_code, otp_expires_at, otp_attempts, pending_password FROM users WHERE reset_token = ? LIMIT 1", [$token]);
    $user = $stmt->fetch();

    if (!$user || empty($user['pending_password'])) {
        Helpers::jsonResponse(false, 'This verification session is invalid or has expired. Please request a new password reset.');
    }

    if ((int)$user['otp_attempts'] >= 5) {
        Helpers::jsonResponse(false, 'Too many incorrect attempts. Please request a new password reset.');
    }

    if (empty($user['otp_expires_at']) || strtotime($user['otp_expires_at']) < time()) {
        Helpers::jsonResponse(false, 'This verification code has expired. Please request a new password reset.');
    }

    if (!hash_equals((string)$user['otp_code'], $otp)) {
        $db->query("UPDATE users SET otp_attempts = otp_attempts + 1 WHERE id = ?", [$user['id']]);
        Helpers::logActivity($db, "auth", "Incorrect password-reset OTP attempt for user ID: " . $user['id']);
        Helpers::jsonResponse(false, 'Incorrect verification code. Please try again.');
    }

    // Correct code - apply the pending password and clear all reset/OTP state
    $db->query("UPDATE users SET password = ?, reset_token = NULL, reset_request_ip = NULL, reset_expires_at = NULL,
                otp_code = NULL, otp_expires_at = NULL, otp_attempts = 0, pending_password = NULL WHERE id = ?", [
        $user['pending_password'], $user['id']
    ]);

    Helpers::logActivity($db, "auth", "Password reset verified via OTP for user ID: " . $user['id']);
} catch (\Exception $e) {
    error_log("Verify reset OTP DB error: " . $e->getMessage());
    Helpers::jsonResponse(false, 'Something went wrong. Please try again later.');
}

Helpers::jsonResponse(true, 'Your password has been successfully reset.');
