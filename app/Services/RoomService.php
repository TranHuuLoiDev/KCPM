<?php
namespace App\Services;

use App\Models\RoomModel;
use App\Models\TheatreModel;

class RoomService {
    private $model;
    private $theatreModel;

    public function __construct() {
        $this->model = new RoomModel();
        $this->theatreModel = new TheatreModel();
    }

    public function addRoom($data) {
        $validation = $this->validate($data);
        if ($validation) {
            return $validation;
        }

        if ($this->model->insert($data)) {
            return ['status' => 'success', 'message' => 'Thêm phòng chiếu thành công!'];
        }
        return ['status' => 'error', 'message' => 'Lỗi khi thêm phòng: ' . $this->model->getError()];
    }

    public function updateRoom($id, $data) {
        if ($id <= 0) {
            return ['status' => 'error', 'message' => 'ID phòng không hợp lệ!'];
        }

        $validation = $this->validate($data, $id);
        if ($validation) {
            return $validation;
        }

        if (!$this->model->findById($id)) {
            return ['status' => 'error', 'message' => 'Phòng chiếu không tồn tại!'];
        }

        if ($this->model->update($id, $data)) {
            return ['status' => 'success', 'message' => 'Cập nhật phòng chiếu thành công!'];
        }
        return ['status' => 'error', 'message' => 'Lỗi khi cập nhật phòng: ' . $this->model->getError()];
    }

    public function deleteRoom($id) {
        if ($id <= 0) {
            return ['status' => 'error', 'message' => 'ID phòng không hợp lệ!'];
        }

        if (!$this->model->findById($id)) {
            return ['status' => 'error', 'message' => 'Phòng chiếu không tồn tại!'];
        }

        if ($this->model->delete($id)) {
            return ['status' => 'success', 'message' => 'Xóa phòng chiếu thành công! Ghế và suất chiếu liên quan cũng đã bị xóa.'];
        }
        return ['status' => 'error', 'message' => 'Lỗi khi xóa phòng: ' . $this->model->getError()];
    }

    public function getAllRooms() {
        return $this->model->getAllWithTheatre();
    }

    public function getAllTheatres() {
        return $this->theatreModel->getAll();
    }

    private function validate($data, $excludeId = null) {
        if (empty($data['name'])) {
            return ['status' => 'error', 'message' => 'Tên phòng không được để trống!'];
        }
        if ($data['theatre_id'] <= 0 || !$this->theatreModel->findById($data['theatre_id'])) {
            return ['status' => 'error', 'message' => 'Rạp chiếu không hợp lệ!'];
        }
        if ($data['total_seats'] < 1) {
            return ['status' => 'error', 'message' => 'Số ghế phải lớn hơn 0!'];
        }
        if ($this->model->findByName($data['name'], $excludeId)) {
            return ['status' => 'error', 'message' => "Tên phòng '{$data['name']}' đã tồn tại trong hệ thống!"];
        }
        return null;
    }
}
