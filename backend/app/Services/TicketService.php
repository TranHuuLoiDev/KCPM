<?php
namespace App\Services;

use App\Models\TicketModel;
use App\Models\BookingModel;
use App\Models\ShowtimeModel;
use App\Models\SeatModel;

class TicketService {
    private $model;
    private $bookingModel;
    private $showtimeModel;
    private $seatModel;

    public function __construct() {
        $this->model = new TicketModel();
        $this->bookingModel = new BookingModel();
        $this->showtimeModel = new ShowtimeModel();
        $this->seatModel = new SeatModel();
    }

    public function addTicket($data) {
        $validation = $this->validate($data);
        if ($validation) {
            return $validation;
        }

        $id = $this->model->create($data);
        if ($id) {
            return ['status' => 'success', 'message' => 'Thêm vé thành công!', 'ticket_id' => $id];
        }
        return ['status' => 'error', 'message' => 'Lỗi khi thêm vé: ' . $this->model->getError()];
    }

    public function updateTicket($id, $data) {
        if ($id <= 0 || !$this->model->getById($id)) {
            return ['status' => 'error', 'message' => 'Vé không hợp lệ!'];
        }

        $validation = $this->validate($data, $id);
        if ($validation) {
            return $validation;
        }

        if ($this->model->update($id, $data)) {
            return ['status' => 'success', 'message' => 'Cập nhật vé thành công!'];
        }
        return ['status' => 'error', 'message' => 'Lỗi khi cập nhật vé: ' . $this->model->getError()];
    }

    public function deleteTicket($id) {
        if ($id <= 0 || !$this->model->getById($id)) {
            return ['status' => 'error', 'message' => 'Vé không hợp lệ!'];
        }

        if ($this->model->delete($id)) {
            return ['status' => 'success', 'message' => 'Xóa vé thành công!'];
        }
        return ['status' => 'error', 'message' => 'Lỗi khi xóa vé: ' . $this->model->getError()];
    }

    public function getAllTickets() {
        return $this->model->getAll();
    }

    public function getTicketById($id) {
        return $id > 0 ? $this->model->getById($id) : null;
    }

    public function getBookedSeatIdsByShowtimeId($showtimeId) {
        $showtimeId = (int)$showtimeId;
        if ($showtimeId <= 0) {
            return [];
        }
        return $this->model->getBookedSeatIdsByShowtimeId($showtimeId);
    }

    public function isSeatBooked($showtimeId, $seatId, $excludeTicketId = null) {
        return $this->model->isSeatBooked((int)$showtimeId, (int)$seatId, $excludeTicketId);
    }

    public function createMany($bookingId, $showtimeId, $seatPrices) {
        return $this->model->createMany((int)$bookingId, (int)$showtimeId, $seatPrices);
    }

    public function getTicketsByBookingId($bookingId) {
        $bookingId = (int)$bookingId;
        if ($bookingId <= 0) {
            return [];
        }
        return $this->model->getByBookingId($bookingId);
    }

    public function getTotalTicketsByUser($userId) {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return 0;
        }
        return $this->model->getTotalTicketsByUser($userId);
    }

    private function validate(&$data, $excludeTicketId = null) {
        $data['booking_id'] = (int)($data['booking_id'] ?? 0);
        $data['showtime_id'] = (int)($data['showtime_id'] ?? 0);
        $data['seat_id'] = (int)($data['seat_id'] ?? 0);
        $data['price'] = (float)($data['price'] ?? 0);
        $data['status'] = $data['status'] ?? 'booked';

        if ($data['booking_id'] <= 0 || !$this->bookingModel->getById($data['booking_id'])) {
            return ['status' => 'error', 'message' => 'Booking không hợp lệ!'];
        }

        $showtime = $data['showtime_id'] > 0 ? $this->showtimeModel->findById($data['showtime_id']) : null;
        if (!$showtime) {
            return ['status' => 'error', 'message' => 'Suất chiếu không hợp lệ!'];
        }

        $seat = $data['seat_id'] > 0 ? $this->seatModel->findById($data['seat_id']) : null;
        if (!$seat) {
            return ['status' => 'error', 'message' => 'Ghế không hợp lệ!'];
        }

        if ((int)$seat['room_id'] !== (int)$showtime['room_id']) {
            return ['status' => 'error', 'message' => 'Ghế không thuộc phòng của suất chiếu này!'];
        }

        if ($this->model->isSeatBooked($data['showtime_id'], $data['seat_id'], $excludeTicketId)) {
            return ['status' => 'error', 'message' => 'Ghế này đã được đặt trong suất chiếu!'];
        }

        if ($data['price'] <= 0) {
            return ['status' => 'error', 'message' => 'Giá vé phải lớn hơn 0!'];
        }

        if (!in_array($data['status'], ['booked', 'used', 'canceled'], true)) {
            return ['status' => 'error', 'message' => 'Trạng thái vé không hợp lệ!'];
        }

        return null;
    }
}