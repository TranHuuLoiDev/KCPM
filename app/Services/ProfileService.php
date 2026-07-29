<?php
namespace App\Services;

use App\Models\BookingModel;
use App\Models\TicketModel;
use App\Models\UserModel;

class ProfileService {
    private $userModel;
    private $ticketModel;
    private $bookingModel;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->ticketModel = new TicketModel();
        $this->bookingModel = new BookingModel();
    }

    public function getProfile($userId) {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return null;
        }
        return $this->userModel->getById($userId);
    }

    public function getProfileOverview($userId) {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return [
                'user' => null,
                'total_tickets' => 0,
                'total_spent' => 0
            ];
        }

        return [
            'user' => $this->userModel->getById($userId),
            'total_tickets' => $this->ticketModel->getTotalTicketsByUser($userId),
            'total_spent' => $this->bookingModel->getTotalSpentByUser($userId)
        ];
    }

    public function updateProfile($userId, $data) {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return ['status' => 'error', 'message' => 'Vui lòng đăng nhập để cập nhật hồ sơ!'];
        }

        $data = [
            'first_name' => trim($data['first_name'] ?? ''),
            'last_name' => trim($data['last_name'] ?? ''),
            'email' => trim($data['email'] ?? ''),
            'phone' => trim($data['phone'] ?? ''),
            'birth_date' => trim($data['birth_date'] ?? '')
        ];

        if ($data['first_name'] === '' || $data['last_name'] === '' || $data['email'] === '' || $data['phone'] === '') {
            return ['status' => 'error', 'message' => 'Vui lòng nhập đầy đủ họ, tên, email và số điện thoại!'];
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['status' => 'error', 'message' => 'Email không hợp lệ!'];
        }

        if ($this->userModel->findByEmail($data['email'], $userId)) {
            return ['status' => 'error', 'message' => 'Email này đã được sử dụng bởi tài khoản khác!'];
        }

        if ($this->userModel->findByPhone($data['phone'], $userId)) {
            return ['status' => 'error', 'message' => 'Số điện thoại này đã được sử dụng bởi tài khoản khác!'];
        }

        if ($data['birth_date'] === '') {
            $data['birth_date'] = null;
        } elseif (!$this->isValidBirthDate($data['birth_date'])) {
            return ['status' => 'error', 'message' => 'Ngày sinh không hợp lệ!'];
        }

        if (!$this->userModel->updateProfile($userId, $data)) {
            return ['status' => 'error', 'message' => 'Lỗi khi cập nhật hồ sơ: ' . $this->userModel->getError()];
        }

        $updatedUser = $this->userModel->getById($userId);
        if ($updatedUser) {
            $this->syncSessionUser($updatedUser);
        }

        return ['status' => 'success', 'message' => 'Cập nhật hồ sơ thành công!'];
    }

    public function updatePassword($userId, $currentPassword, $newPassword, $confirmPassword) {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return ['status' => 'error', 'message' => 'Vui lòng đăng nhập để đổi mật khẩu!'];
        }

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            return ['status' => 'error', 'message' => 'Vui lòng nhập đầy đủ thông tin mật khẩu!'];
        }

        if ($newPassword !== $confirmPassword) {
            return ['status' => 'error', 'message' => 'Mật khẩu xác nhận không khớp!'];
        }

        if (strlen($newPassword) < 6) {
            return ['status' => 'error', 'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự!'];
        }

        $user = $this->userModel->getById($userId);
        if (!$user) {
            return ['status' => 'error', 'message' => 'Tài khoản không tồn tại!'];
        }

        if (!password_verify($currentPassword, $user['password'])) {
            return ['status' => 'error', 'message' => 'Mật khẩu hiện tại không chính xác!'];
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        if (!$this->userModel->updatePassword($userId, $hashedPassword)) {
            return ['status' => 'error', 'message' => 'Lỗi khi đổi mật khẩu: ' . $this->userModel->getError()];
        }

        return ['status' => 'success', 'message' => 'Đổi mật khẩu thành công!'];
    }

    private function isValidBirthDate($birthDate) {
        $date = \DateTime::createFromFormat('Y-m-d', $birthDate);
        return $date && $date->format('Y-m-d') === $birthDate;
    }

    private function syncSessionUser($user) {
        if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
            return;
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'role' => $user['role']
        ];
    }
}
