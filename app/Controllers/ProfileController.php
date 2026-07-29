<?php
namespace App\Controllers;

use App\Services\ProfileService;

class ProfileController {
    private $profileService;

    public function __construct() {
        $this->profileService = new ProfileService();
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'])) {
            return null;
        }

        $userId = (int)($_SESSION['user']['id'] ?? 0);
        $action = $_POST['action'];

        if ($action === 'update_profile') {
            $data = [
                'first_name' => trim($_POST['first_name'] ?? ''),
                'last_name' => trim($_POST['last_name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'birth_date' => trim($_POST['birth_date'] ?? '')
            ];

            return $this->profileService->updateProfile($userId, $data);
        }

        if ($action === 'update_password' || $action === 'change_password') {
            $currentPassword = $_POST['current_password'] ?? $_POST['old_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? $_POST['password_confirmation'] ?? '';

            return $this->profileService->updatePassword($userId, $currentPassword, $newPassword, $confirmPassword);
        }

        return null;
    }

    public function getProfile($userId) {
        return $this->profileService->getProfile((int)$userId);
    }

    public function getProfileOverview($userId) {
        return $this->profileService->getProfileOverview((int)$userId);
    }

    public function updateProfile($userId, $data) {
        return $this->profileService->updateProfile((int)$userId, $data);
    }

    public function updatePassword($userId, $currentPassword, $newPassword, $confirmPassword) {
        return $this->profileService->updatePassword((int)$userId, $currentPassword, $newPassword, $confirmPassword);
    }
}
