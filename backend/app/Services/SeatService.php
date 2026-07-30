<?php
namespace App\Services;

use App\Models\SeatModel;
use App\Models\SeatTypeModel;
use App\Models\RoomModel;

class SeatService {
    private $model;
    private $seatTypeModel;
    private $roomModel;

    public function __construct() {
        $this->model = new SeatModel();
        $this->seatTypeModel = new SeatTypeModel();
        $this->roomModel = new RoomModel();
    }

    public function addSeat($data) {
        $data['seat_row'] = strtoupper(trim($data['seat_row'] ?? ''));
        $validation = $this->validate($data);
        if ($validation) {
            return $validation;
        }

        if ($this->model->insert($data)) {
            $this->syncRoomTotalSeats($data['room_id']);
            return ['status' => 'success', 'message' => 'Thêm ghế thành công!'];
        }
        return ['status' => 'error', 'message' => 'Lỗi khi thêm ghế: ' . $this->model->getError()];
    }

    public function updateSeat($id, $data) {
        if ($id <= 0) {
            return ['status' => 'error', 'message' => 'ID ghế không hợp lệ!'];
        }

        $existing = $this->model->findById($id);
        if (!$existing) {
            return ['status' => 'error', 'message' => 'Ghế không tồn tại!'];
        }

        $data['seat_row'] = strtoupper(trim($data['seat_row'] ?? ''));
        $validation = $this->validate($data, $id);
        if ($validation) {
            return $validation;
        }

        if ($this->model->update($id, $data)) {
            $this->syncRoomTotalSeats($data['room_id']);
            if ((int)$existing['room_id'] !== (int)$data['room_id']) {
                $this->syncRoomTotalSeats($existing['room_id']);
            }
            return ['status' => 'success', 'message' => 'Cập nhật ghế thành công!'];
        }
        return ['status' => 'error', 'message' => 'Lỗi khi cập nhật ghế: ' . $this->model->getError()];
    }

    public function deleteSeat($id) {
        if ($id <= 0) {
            return ['status' => 'error', 'message' => 'ID ghế không hợp lệ!'];
        }

        $existing = $this->model->findById($id);
        if (!$existing) {
            return ['status' => 'error', 'message' => 'Ghế không tồn tại!'];
        }

        if ($this->model->delete($id)) {
            $this->syncRoomTotalSeats($existing['room_id']);
            return ['status' => 'success', 'message' => 'Xóa ghế thành công!'];
        }
        return ['status' => 'error', 'message' => 'Lỗi khi xóa ghế: ' . $this->model->getError()];
    }

    public function bulkDeleteSeats($roomId, $startRow, $endRow, $startNumber, $endNumber) {
        if ($roomId <= 0 || !$this->roomModel->findById($roomId)) {
            return ['status' => 'error', 'message' => 'Phòng chiếu không hợp lệ!'];
        }

        $startRow = strtoupper(trim($startRow));
        $endRow = strtoupper(trim($endRow));
        $start = ord($startRow);
        $end = ord($endRow);

        if ($start < ord('A') || $start > ord('H') || $end < ord('A') || $end > ord('H') || $start > $end) {
            return ['status' => 'error', 'message' => 'Khoảng hàng ghế phải từ A đến H và hợp lệ!'];
        }

        if ($startNumber < 1 || $startNumber > 12 || $endNumber < 1 || $endNumber > 12 || $startNumber > $endNumber) {
            return ['status' => 'error', 'message' => 'Khoảng số ghế phải từ 1 đến 12 và hợp lệ!'];
        }

        $total = $this->model->countByRange($roomId, $startRow, $endRow, $startNumber, $endNumber);
        if ($total === 0) {
            return ['status' => 'error', 'message' => 'Không có ghế nào trong khoảng đã chọn.'];
        }

        $deleted = $this->model->deleteByRange($roomId, $startRow, $endRow, $startNumber, $endNumber);
        if ($deleted === false) {
            return ['status' => 'error', 'message' => 'Lỗi khi xóa ghế hàng loạt: ' . $this->model->getError()];
        }

        $this->syncRoomTotalSeats($roomId);
        return ['status' => 'success', 'message' => "Xóa thành công $deleted ghế."];
    }

    public function generateSeats($roomId, $startRow, $endRow, $seatsPerRow, $seatTypeId) {
        if ($roomId <= 0 || !$this->roomModel->findById($roomId)) {
            return ['status' => 'error', 'message' => 'Phòng chiếu không hợp lệ!'];
        }
        if (!$this->seatTypeModel->findById($seatTypeId)) {
            return ['status' => 'error', 'message' => 'Loại ghế không hợp lệ!'];
        }
        if ($seatsPerRow < 1 || $seatsPerRow > 12) {
            return ['status' => 'error', 'message' => 'Số ghế mỗi hàng phải từ 1 đến 12!'];
        }

        $start = ord(strtoupper($startRow));
        $end = ord(strtoupper($endRow));
        if ($start < ord('A') || $start > ord('H') || $end < ord('A') || $end > ord('H') || $start > $end) {
            return ['status' => 'error', 'message' => 'Hàng ghế phải từ A đến H và hợp lệ!'];
        }

        $created = 0;
        $skipped = 0;

        for ($rowCode = $start; $rowCode <= $end; $rowCode++) {
            $seatRow = chr($rowCode);
            for ($seatNumber = 1; $seatNumber <= $seatsPerRow; $seatNumber++) {
                if ($this->model->findByPosition($roomId, $seatRow, $seatNumber)) {
                    $skipped++;
                    continue;
                }

                $data = [
                    'room_id' => $roomId,
                    'seat_row' => $seatRow,
                    'seat_number' => $seatNumber,
                    'seat_type_id' => $seatTypeId,
                    'is_active' => true,
                ];

                if ($this->model->insert($data)) {
                    $created++;
                }
            }
        }

        $this->syncRoomTotalSeats($roomId);

        if ($created === 0 && $skipped > 0) {
            return ['status' => 'error', 'message' => 'Không tạo ghế mới. Tất cả vị trí đã tồn tại.'];
        }

        return [
            'status' => 'success',
            'message' => "Tạo thành công $created ghế" . ($skipped > 0 ? ", bỏ qua $skipped ghế đã tồn tại." : '.'),
        ];
    }

    public function getAllSeats($roomId = null) {
        return $this->model->getAllWithDetails($roomId);
    }


    public function getAllRooms() {
        return $this->roomModel->getAllWithTheatre();
    }

    public function getAllSeatTypes() {
        return $this->seatTypeModel->getAll();
    }

    public function quickAddSeat($roomId, $seatRow) {
        if ($roomId <= 0 || !$this->roomModel->findById($roomId)) {
            return ['status' => 'error', 'message' => 'Phòng chiếu không hợp lệ!'];
        }

        $seatRow = strtoupper(trim($seatRow));
        if (!preg_match('/^[A-H]$/', $seatRow)) {
            return ['status' => 'error', 'message' => 'Hàng ghế không hợp lệ!'];
        }

        $seatNumber = $this->model->getNextSeatNumber($roomId, $seatRow);
        if ($seatNumber > 12) {
            return ['status' => 'error', 'message' => 'Hàng này đã đạt tối đa 12 ghế!'];
        }

        $types = $this->seatTypeModel->getAll();
        if (empty($types)) {
            return ['status' => 'error', 'message' => 'Chưa có loại ghế trong hệ thống!'];
        }

        return $this->addSeat([
            'room_id' => $roomId,
            'seat_row' => $seatRow,
            'seat_number' => $seatNumber,
            'seat_type_id' => (int) $types[0]['id'],
            'is_active' => true,
        ]);
    }

    public function getDisplayRows($roomId, $showRow = null) {
        $rows = $this->model->getRowLettersByRoom($roomId);
        $showRow = strtoupper(trim((string) $showRow));
        if ($showRow !== '' && preg_match('/^[A-H]$/', $showRow) && !in_array($showRow, $rows, true)) {
            $rows[] = $showRow;
        }
        sort($rows);
        return $rows;
    }

    public function getNextRowLetter(array $displayRows) {
        if (empty($displayRows)) {
            return 'A';
        }
        $last = end($displayRows);
        $nextCode = ord($last) + 1;
        if ($nextCode > ord('H')) {
            return null;
        }
        return chr($nextCode);
    }

    private function validate($data, $excludeId = null) {
        if ($data['room_id'] <= 0 || !$this->roomModel->findById($data['room_id'])) {
            return ['status' => 'error', 'message' => 'Phòng chiếu không hợp lệ!'];
        }

        if (!preg_match('/^[A-H]$/', $data['seat_row'])) {
            return ['status' => 'error', 'message' => 'Hàng ghế phải từ A đến H!'];
        }

        $seatNumber = (int)($data['seat_number'] ?? 0);
        if ($seatNumber < 1 || $seatNumber > 12) {
            return ['status' => 'error', 'message' => 'Số ghế phải từ 1 đến 12!'];
        }

        if ($data['seat_type_id'] <= 0 || !$this->seatTypeModel->findById($data['seat_type_id'])) {
            return ['status' => 'error', 'message' => 'Loại ghế không hợp lệ!'];
        }

        if ($this->model->findByPosition($data['room_id'], $data['seat_row'], $seatNumber, $excludeId)) {
            return ['status' => 'error', 'message' => "Ghế {$data['seat_row']}$seatNumber đã tồn tại trong phòng này!"];
        }

        return null;
    }

    private function syncRoomTotalSeats($roomId) {
        $total = $this->model->countByRoomId($roomId);
        $this->roomModel->updateTotalSeats($roomId, $total);
    }


    /**
     * Lấy ghế đã đặt theo suất chiếu
     */
    public function getBookedSeats($showtime_id) {
        if ($showtime_id <= 0) return [];
        return $this->model->getBookedSeats($showtime_id);
    }

    /**
     * Lấy sơ đồ ghế (có trạng thái booked/available)
     */
     public function getSeatsByRoomId($roomId) {
        $roomId = (int)$roomId;
        if ($roomId <= 0) {
            return [];
        }
        return $this->model->getByRoomIdWithType($roomId);
    }


    /**
     * Lấy thông tin ghế theo danh sách ID
     */
    public function getSeatsByIds($seatIds) {
        if (empty($seatIds)) return [];
        return $this->seatModel->getByIds($seatIds);
    }
}
