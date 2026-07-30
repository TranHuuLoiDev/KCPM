<?php
namespace App\Services;

use App\Models\TheatreModel;

class TheatreService {
    private $model;

    public function __construct() {
        $this->model = new TheatreModel();
    }

    public function addTheatre($data) {
        $validation = $this->validate($data);
        if ($validation) {
            return $validation;
        }

        if ($this->model->insert($data)) {
            return ['status' => 'success', 'message' => 'Thêm rạp thành công!'];
        }
        return ['status' => 'error', 'message' => 'Lỗi khi thêm rạp: ' . $this->model->getError()];
    }

    public function updateTheatre($id, $data) {
        if ($id <= 0) {
            return ['status' => 'error', 'message' => 'ID rạp không hợp lệ!'];
        }

        $validation = $this->validate($data);
        if ($validation) {
            return $validation;
        }

        if (!$this->model->findById($id)) {
            return ['status' => 'error', 'message' => 'Rạp không tồn tại!'];
        }

        if ($this->model->update($id, $data)) {
            return ['status' => 'success', 'message' => 'Cập nhật rạp thành công!'];
        }
        return ['status' => 'error', 'message' => 'Lỗi khi cập nhật rạp: ' . $this->model->getError()];
    }

    public function deleteTheatre($id) {
        if ($id <= 0) {
            return ['status' => 'error', 'message' => 'ID rạp không hợp lệ!'];
        }

        if (!$this->model->findById($id)) {
            return ['status' => 'error', 'message' => 'Rạp không tồn tại!'];
        }

        if ($this->model->delete($id)) {
            return ['status' => 'success', 'message' => 'Xóa rạp thành công! Các phòng, ghế và suất chiếu liên quan cũng đã bị xóa.'];
        }
        return ['status' => 'error', 'message' => 'Lỗi khi xóa rạp: ' . $this->model->getError()];
    }

    public function getAllTheatres() {
        return $this->model->getAll();
    }

    private function validate($data) {
        if (empty($data['name'])) {
            return ['status' => 'error', 'message' => 'Tên rạp không được để trống!'];
        }
        if ($data['total_screens'] < 1) {
            return ['status' => 'error', 'message' => 'Số phòng chiếu phải lớn hơn 0!'];
        }
        return null;
    }
}
