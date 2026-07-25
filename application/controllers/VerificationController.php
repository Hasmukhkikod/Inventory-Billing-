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
        $token = $_GET['token'] ?? '';
        $message = '';
        $success = false;

        if (empty($token)) {
            $message = "Invalid or missing verification token.";
        } else {
            // Find organization with this token
            $org = $this->db->query("SELECT id FROM organizations WHERE verification_token = ? LIMIT 1", [$token])->fetch();

            if ($org) {
                // Update to verified
                $this->db->query("UPDATE organizations SET is_verified = 1, verification_token = NULL WHERE id = ?", [$org['id']]);
                $message = "Your email ID has been verified successfully. You can now log in.";
                $success = true;
            } else {
                $message = "Invalid or expired verification token.";
            }
        }

        require_once __DIR__ . '/../views/auth/verify.php';
    }
}
