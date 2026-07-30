<?php
namespace App\Services;

use App\Models\UserModel;

class UserService {
    private $model;

    public function __construct() {
        $this->model = new UserModel();
    }

    public function addUser($data) {
        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email']) || empty($data['phone']) || empty($data['password'])) {
            return ['status' => 'error', 'message' => 'Vui lòng nhập đầy đủ thông tin bắt buộc!'];
        }
        
        $emailExists = $this->model->findByEmail($data['email']);
        if ($emailExists) {
            return ['status' => 'error', 'message' => 'Email này đã được sử dụng!'];
        }

        $phoneExists = $this->model->findByPhone($data['phone']);
        if ($phoneExists) {
            return ['status' => 'error', 'message' => 'Số điện thoại này đã được sử dụng!'];
        }

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        if (empty($data['birth_date'])) {
            $data['birth_date'] = null;
        }

        if ($this->model->insert($data)) {
            return ['status' => 'success', 'message' => 'Thêm người dùng thành công!'];
        }
        return ['status' => 'error', 'message' => 'Lỗi khi thêm người dùng: ' . $this->model->getError()];
    }

    public function updateUser($id, $data) {
        if ($id <= 0 || empty($data['first_name']) || empty($data['last_name']) || empty($data['email']) || empty($data['phone'])) {
            return ['status' => 'error', 'message' => 'Dữ liệu không hợp lệ!'];
        }

        $emailExists = $this->model->findByEmail($data['email'], $id);
        if ($emailExists) {
            return ['status' => 'error', 'message' => 'Email này đã được sử dụng bởi người khác!'];
        }

        $phoneExists = $this->model->findByPhone($data['phone'], $id);
        if ($phoneExists) {
            return ['status' => 'error', 'message' => 'Số điện thoại này đã được sử dụng bởi người khác!'];
        }

        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']); // Model will handle empty password
        }

        if (empty($data['birth_date'])) {
            $data['birth_date'] = null;
        }

        if ($this->model->update($id, $data)) {
            return ['status' => 'success', 'message' => 'Cập nhật người dùng thành công!'];
        }
        return ['status' => 'error', 'message' => 'Lỗi khi cập nhật: ' . $this->model->getError()];
    }

    public function deleteUser($id) {
        if ($id <= 0) {
            return ['status' => 'error', 'message' => 'ID không hợp lệ!'];
        }
        if ($id == 1) {
            return ['status' => 'error', 'message' => 'Không thể xóa tài khoản Admin gốc!'];
        }

        if ($this->model->delete($id)) {
            return ['status' => 'success', 'message' => 'Xóa người dùng thành công!'];
        }
        return ['status' => 'error', 'message' => 'Lỗi khi xóa: ' . $this->model->getError()];
    }

    public function getAllUsers() {
        return $this->model->getAll();
    }

    public function getUsersByFilter($keyword) {
        return $this->model->getByFilter($keyword);
    }

    public function searchUsers($keyword = '') {
        return trim($keyword) === '' ? $this->getAllUsers() : $this->getUsersByFilter($keyword);
    }

    public function getUserById($id) {
        return $this->model->getById($id);
    }

    public function getStats() {
        return $this->model->getStats();
    }
}
