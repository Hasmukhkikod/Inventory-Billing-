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
    $stmt = $db->query("SELECT id, password, email, reset_request_ip FROM users WHERE reset_token = ? AND reset_expires_at > NOW() LIMIT 1", [$token]);
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

    // Update password and clear reset fields
    $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
    $db->query("UPDATE users SET password = ?, reset_token = NULL, reset_request_ip = NULL, reset_expires_at = NULL WHERE id = ?", [
        $hashedPassword, $user['id']
    ]);

    Helpers::logActivity($db, "auth", "Password reset successfully for user ID: " . $user['id']);
} catch (\Exception $e) {
    error_log("Reset password DB error: " . $e->getMessage());
    Helpers::jsonResponse(false, 'Something went wrong. Please try again later.');
}

Helpers::jsonResponse(true, 'Your password has been successfully reset.');
