<?php
namespace App\Controllers;

use App\Services\PasswordResetService;

class PasswordResetController {
    private $passwordResetService;

    public function __construct() {
        $this->passwordResetService = new PasswordResetService();
    }

    public function requestReset() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['action'] ?? '') !== 'request_reset') {
            return null;
        }

        $email = trim($_POST['email'] ?? '');
        return $this->passwordResetService->requestReset($email);
    }

    public function resetPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['action'] ?? '') !== 'reset_password') {
            return null;
        }

        $email = trim($_POST['email'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        return $this->passwordResetService->resetPassword($email, $code, $newPassword, $confirmPassword);
    }
}
