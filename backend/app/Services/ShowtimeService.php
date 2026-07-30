<?php
namespace App\Services;

use App\Models\MovieModel;
use App\Models\RoomModel;
use App\Models\ShowtimeModel;

class ShowtimeService {
    private $showtimeModel;
    private $movieModel;
    private $roomModel;

    public function __construct() {
        $this->showtimeModel = new ShowtimeModel();
        $this->movieModel = new MovieModel();
        $this->roomModel = new RoomModel();
    }

    public function addShowtime($data) {
        $validation = $this->validate($data);
        if ($validation) {
            return $validation;
        }

        if ($this->showtimeModel->insert($data)) {
            return ['status' => 'success', 'message' => 'Thêm suất chiếu thành công!'];
        }
        return ['status' => 'error', 'message' => 'Lỗi khi thêm suất chiếu: ' . $this->showtimeModel->getError()];
    }

    public function updateShowtime($id, $data) {
        if ($id <= 0) {
            return ['status' => 'error', 'message' => 'ID suất chiếu không hợp lệ!'];
        }

        if (!$this->showtimeModel->findById($id)) {
            return ['status' => 'error', 'message' => 'Suất chiếu không tồn tại!'];
        }

        $validation = $this->validate($data, $id);
        if ($validation) {
            return $validation;
        }

        if ($this->showtimeModel->update($id, $data)) {
            return ['status' => 'success', 'message' => 'Cập nhật suất chiếu thành công!'];
        }
        return ['status' => 'error', 'message' => 'Lỗi khi cập nhật suất chiếu: ' . $this->showtimeModel->getError()];
    }

    public function deleteShowtime($id) {
        if ($id <= 0) {
            return ['status' => 'error', 'message' => 'ID suất chiếu không hợp lệ!'];
        }

        if (!$this->showtimeModel->findById($id)) {
            return ['status' => 'error', 'message' => 'Suất chiếu không tồn tại!'];
        }

        if ($this->showtimeModel->delete($id)) {
            return ['status' => 'success', 'message' => 'Xóa suất chiếu thành công!'];
        }
        return ['status' => 'error', 'message' => 'Lỗi khi xóa suất chiếu: ' . $this->showtimeModel->getError()];
    }

    public function getAllShowtimes() {
        return $this->showtimeModel->getAllWithDetails();
    }

    public function getShowtimeDetail($showtimeId) {
        $showtimeId = (int)$showtimeId;
        if ($showtimeId <= 0) {
            return null;
        }
        return $this->showtimeModel->getDetailById($showtimeId);
    }

    public function getShowtimesByMovieId($movieId) {
        $movieId = (int)$movieId;
        if ($movieId <= 0) {
            return [];
        }
        return $this->showtimeModel->getByMovieId($movieId);
    }

    public function getAllMovies() {
        return $this->movieModel->getAllMovies();
    }

    public function getAllRooms() {
        return $this->roomModel->getAllRooms();
    }

    private function validate(&$data, $excludeId = null) {
        if ($data['movie_id'] <= 0 || !$this->showtimeModel->movieExists($data['movie_id'])) {
            return ['status' => 'error', 'message' => 'Phim không hợp lệ!'];
        }
        if ($data['room_id'] <= 0 || !$this->showtimeModel->roomExists($data['room_id'])) {
            return ['status' => 'error', 'message' => 'Phòng chiếu không hợp lệ!'];
        }
        if (empty($data['show_date'])) {
            return ['status' => 'error', 'message' => 'Ngày chiếu không được để trống!'];
        }
        if (empty($data['start_time'])) {
            return ['status' => 'error', 'message' => 'Giờ bắt đầu không được để trống!'];
        }

        $startTime = $this->normalizeTime($data['start_time']);
        if (!$startTime) {
            return ['status' => 'error', 'message' => 'Giờ bắt đầu không hợp lệ!'];
        }
        $data['start_time'] = $startTime;

        $duration = $this->showtimeModel->getMovieDuration($data['movie_id']);
        if ($duration <= 0) {
            return ['status' => 'error', 'message' => 'Không thể tính giờ kết thúc. Vui lòng cập nhật thời lượng phim.'];
        }
        $data['end_time'] = $this->computeEndTime($startTime, $duration);

        if ($data['base_price'] <= 0) {
            return ['status' => 'error', 'message' => 'Giá vé cơ bản phải lớn hơn 0!'];
        }

        if (!in_array($data['status'], ['active', 'canceled'], true)) {
            return ['status' => 'error', 'message' => 'Trạng thái suất chiếu không hợp lệ!'];
        }

        if ($this->showtimeModel->findConflict($data['room_id'], $data['show_date'], $data['start_time'], $excludeId)) {
            return ['status' => 'error', 'message' => 'Phòng đã có suất chiếu trùng ngày và giờ bắt đầu!'];
        }

        return null;
    }

    private function normalizeTime($time) {
        $time = trim($time);
        if (preg_match('/^\d{2}:\d{2}$/', $time)) {
            return $time . ':00';
        }
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
            return $time;
        }
        return null;
    }


    public function getShowtimesByMovie($movieId) {
        if ($movieId <= 0) {
            return [];
        }

        return $this->model->getShowtimesByMovie($movieId);
    }

    public function getShowtimeDetails($showtimeId) {
        if ($showtimeId <= 0) {
            return null;
        }

        return $this->model->getShowtimeDetails($showtimeId);
    }

    private function computeEndTime($startTime, $durationMinutes) {
        $start = strtotime($startTime);
        return date('H:i:s', $start + ($durationMinutes * 60));
    }

    public function getShowtimeById($id) {
        if ($id <= 0) return null;
        return $this->model->getById($id);
    }
}
