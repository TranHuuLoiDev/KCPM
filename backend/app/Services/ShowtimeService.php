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
        $result = null;
        $validation = $this->validate($data);

        if ($validation) {
            $result = $validation;
        } elseif ($this->showtimeModel->insert($data)) {
            $result = ['status' => 'success', 'message' => 'Thêm suất chiếu thành công!'];
        } else {
            $result = ['status' => 'error', 'message' => 'Lỗi khi thêm suất chiếu: ' . $this->showtimeModel->getError()];
        }

        return $result;
    }

    public function updateShowtime($id, $data) {
        $result = null;

        if ($id <= 0) {
            $result = ['status' => 'error', 'message' => 'ID suất chiếu không hợp lệ!'];
        } elseif (!$this->showtimeModel->findById($id)) {
            $result = ['status' => 'error', 'message' => 'Suất chiếu không tồn tại!'];
        } else {
            $validation = $this->validate($data, $id);
            if ($validation) {
                $result = $validation;
            } elseif ($this->showtimeModel->update($id, $data)) {
                $result = ['status' => 'success', 'message' => 'Cập nhật suất chiếu thành công!'];
            } else {
                $result = ['status' => 'error', 'message' => 'Lỗi khi cập nhật suất chiếu: ' . $this->showtimeModel->getError()];
            }
        }

        return $result;
    }

    public function deleteShowtime($id) {
        $result = null;

        if ($id <= 0) {
            $result = ['status' => 'error', 'message' => 'ID suất chiếu không hợp lệ!'];
        } elseif (!$this->showtimeModel->findById($id)) {
            $result = ['status' => 'error', 'message' => 'Suất chiếu không tồn tại!'];
        } elseif ($this->showtimeModel->delete($id)) {
            $result = ['status' => 'success', 'message' => 'Xóa suất chiếu thành công!'];
        } else {
            $result = ['status' => 'error', 'message' => 'Lỗi khi xóa suất chiếu: ' . $this->showtimeModel->getError()];
        }

        return $result;
    }

    public function getAllShowtimes() {
        return $this->showtimeModel->getAllWithDetails();
    }

    public function getShowtimeDetail($showtimeId) {
        $result = null;
        $showtimeId = (int)$showtimeId;
        if ($showtimeId > 0) {
            $result = $this->showtimeModel->getDetailById($showtimeId);
        }
        return $result;
    }

    public function getShowtimesByMovieId($movieId) {
        $result = [];
        $movieId = (int)$movieId;
        if ($movieId > 0) {
            $result = $this->showtimeModel->getByMovieId($movieId);
        }
        return $result;
    }

    public function getAllMovies() {
        return $this->movieModel->getAllMovies();
    }

    public function getAllRooms() {
        return $this->roomModel->getAllRooms();
    }

    private function validate(&$data, $excludeId = null) {
        $result = null;

        if ($data['movie_id'] <= 0 || !$this->showtimeModel->movieExists($data['movie_id'])) {
            $result = ['status' => 'error', 'message' => 'Phim không hợp lệ!'];
        } elseif ($data['room_id'] <= 0 || !$this->showtimeModel->roomExists($data['room_id'])) {
            $result = ['status' => 'error', 'message' => 'Phòng chiếu không hợp lệ!'];
        } elseif (empty($data['show_date'])) {
            $result = ['status' => 'error', 'message' => 'Ngày chiếu không được để trống!'];
        } elseif (empty($data['start_time'])) {
            $result = ['status' => 'error', 'message' => 'Giờ bắt đầu không được để trống!'];
        } else {
            $startTime = $this->normalizeTime($data['start_time']);
            if (!$startTime) {
                $result = ['status' => 'error', 'message' => 'Giờ bắt đầu không hợp lệ!'];
            } else {
                $data['start_time'] = $startTime;
                $duration = $this->showtimeModel->getMovieDuration($data['movie_id']);
                if ($duration <= 0) {
                    $result = ['status' => 'error', 'message' => 'Không thể tính giờ kết thúc. Vui lòng cập nhật thời lượng phim.'];
                } else {
                    $data['end_time'] = $this->computeEndTime($startTime, $duration);

                    if ($data['base_price'] <= 0) {
                        $result = ['status' => 'error', 'message' => 'Giá vé cơ bản phải lớn hơn 0!'];
                    } elseif (!in_array($data['status'], ['active', 'canceled'], true)) {
                        $result = ['status' => 'error', 'message' => 'Trạng thái suất chiếu không hợp lệ!'];
                    } elseif ($this->showtimeModel->findConflict($data['room_id'], $data['show_date'], $data['start_time'], $excludeId)) {
                        $result = ['status' => 'error', 'message' => 'Phòng đã có suất chiếu trùng ngày và giờ bắt đầu!'];
                    }
                }
            }
        }

        return $result;
    }

    private function normalizeTime($time) {
        $result = null;
        $time = trim($time);
        if (preg_match('/^\d{2}:\d{2}$/', $time)) {
            $result = $time . ':00';
        } elseif (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
            $result = $time;
        }
        return $result;
    }

    public function getShowtimesByMovie($movieId) {
        $result = [];
        if ($movieId > 0) {
            $result = $this->model->getShowtimesByMovie($movieId);
        }
        return $result;
    }

    public function getShowtimeDetails($showtimeId) {
        $result = null;
        if ($showtimeId > 0) {
            $result = $this->model->getShowtimeDetails($showtimeId);
        }
        return $result;
    }

    private function computeEndTime($startTime, $durationMinutes) {
        $start = strtotime($startTime);
        return date('H:i:s', $start + ($durationMinutes * 60));
    }

    public function getShowtimeById($id) {
        $result = null;
        if ($id > 0) {
            $result = $this->model->getById($id);
        }
        return $result;
    }
}