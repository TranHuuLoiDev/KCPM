<?php
namespace App\Controllers;

use App\Services\TicketService;

class TicketController {
    private $service;

    public function __construct() {
        $this->service = new TicketService();
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];

            if ($action === 'add' || $action === 'edit') {
                $data = [
                    'booking_id' => (int)($_POST['booking_id'] ?? 0),
                    'showtime_id' => (int)($_POST['showtime_id'] ?? 0),
                    'seat_id' => (int)($_POST['seat_id'] ?? 0),
                    'price' => (float)($_POST['price'] ?? 0),
                    'status' => $_POST['status'] ?? 'booked',
                ];

                if ($action === 'add') {
                    return $this->service->addTicket($data);
                }

                $id = (int)($_POST['id'] ?? 0);
                return $this->service->updateTicket($id, $data);
            }

            if ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                return $this->service->deleteTicket($id);
            }
        }
        return null;
    }

    public function getAllTickets() {
        return $this->service->getAllTickets();
    }

    public function getTicketById($id) {
        return $this->service->getTicketById((int)$id);
    }

    public function getBookedSeatIdsByShowtimeId($showtimeId) {
        return $this->service->getBookedSeatIdsByShowtimeId((int)$showtimeId);
    }

    public function getTicketsByBookingId($bookingId) {
        return $this->service->getTicketsByBookingId((int)$bookingId);
    }

    public function getTotalTicketsByUser($userId) {
        return $this->service->getTotalTicketsByUser((int)$userId);
    }
}
