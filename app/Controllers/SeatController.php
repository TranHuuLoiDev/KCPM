<?php
namespace App\Controllers;

use App\Services\SeatService;

class SeatController {
    private $service;

    public function __construct() {
        $this->service = new SeatService();
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];

            if ($action === 'add' || $action === 'edit') {
                $data = [
                    'room_id' => (int)($_POST['room_id'] ?? 0),
                    'seat_row' => trim($_POST['seat_row'] ?? ''),
                    'seat_number' => (int)($_POST['seat_number'] ?? 0),
                    'seat_type_id' => (int)($_POST['seat_type_id'] ?? 0),
                    'is_active' => (int) ($_POST['is_active'] ?? 0) === 1,
                ];

                if ($action === 'add') {
                    return $this->service->addSeat($data);
                }

                $id = (int)($_POST['id'] ?? 0);
                return $this->service->updateSeat($id, $data);
            }

            if ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                return $this->service->deleteSeat($id);
            }

            if ($action === 'generate') {
                $roomId = (int)($_POST['room_id'] ?? 0);
                $startRow = trim($_POST['start_row'] ?? 'A');
                $endRow = trim($_POST['end_row'] ?? 'H');
                $seatsPerRow = (int)($_POST['seats_per_row'] ?? 5);
                $seatTypeId = (int)($_POST['seat_type_id'] ?? 0);
                return $this->service->generateSeats($roomId, $startRow, $endRow, $seatsPerRow, $seatTypeId);
            }

            if ($action === 'bulk_delete') {
                $roomId = (int)($_POST['room_id'] ?? 0);
                $startRow = trim($_POST['delete_start_row'] ?? 'A');
                $endRow = trim($_POST['delete_end_row'] ?? 'H');
                $startNumber = (int)($_POST['delete_start_number'] ?? 1);
                $endNumber = (int)($_POST['delete_end_number'] ?? 12);
                return $this->service->bulkDeleteSeats($roomId, $startRow, $endRow, $startNumber, $endNumber);
            }

            if ($action === 'quick_add') {
                $roomId = (int) ($_POST['room_id'] ?? 0);
                $seatRow = trim($_POST['seat_row'] ?? '');
                return $this->service->quickAddSeat($roomId, $seatRow);
            }
        }
        return null;
    }

    public function getDisplayRows($roomId, $showRow = null) {
        return $this->service->getDisplayRows($roomId, $showRow);
    }

    public function getNextRowLetter(array $displayRows) {
        return $this->service->getNextRowLetter($displayRows);
    }

    public function getAllSeats($roomId = null) {
        return $this->service->getAllSeats($roomId);
    }

    public function getSeatsByRoomId($roomId) {
        return $this->service->getSeatsByRoomId($roomId);
    }

    public function getAllRooms() {
        return $this->service->getAllRooms();
    }

    public function getAllSeatTypes() {
        return $this->service->getAllSeatTypes();
    }

}
