<?php
namespace App\Controllers;

use App\Services\BookingService;

class BookingController {
    private $service;

    public function __construct() {
        $this->service = new BookingService();
    }

    // handle request
    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return null;
        }

        $action = $_POST['action'] ?? '';

        if ($action === 'book_ticket') {
            $userId = (int)($_SESSION['user']['id'] ?? 0);
            $showtimeId = (int)($_POST['showtime_id'] ?? 0);
            $seatIds = $_POST['seats'] ?? [];
            $paymentMethod = $_POST['payment_method'] ?? 'cash';

            return $this->service->processBooking(
                $userId,
                $showtimeId,
                $seatIds,
                $paymentMethod
            );
        }

        if ($action === 'cancel_booking') {
            $userId = (int)($_SESSION['user']['id'] ?? 0);
            $bookingId = (int)($_POST['booking_id'] ?? 0);

            return $this->service->cancelBooking($userId, $bookingId);
        }

        return null;
    }

    public function handleAdminRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return null;
        }

        $action = $_POST['action'] ?? '';

        if ($action === 'update_status') {
            $bookingId = (int)($_POST['booking_id'] ?? 0);
            $status = $_POST['status'] ?? '';

            return $this->service->updateAdminBookingStatus($bookingId, $status);
        }

        if ($action === 'delete') {
            $bookingId = (int)($_POST['booking_id'] ?? 0);

            return $this->service->deleteAdminBooking($bookingId);
        }

        return null;
    }

    public function getUserBookings($userId) {
        return $this->service->getUserBookings((int)$userId);
    }

    public function cancelBooking($userId, $bookingId) {
        return $this->service->cancelBooking((int)$userId, (int)$bookingId);
    }

    public function getTotalSpentByUser($userId) {
        return $this->service->getTotalSpentByUser((int)$userId);
    }

    public function getAdminBookingStats() {
        return $this->service->getAdminBookingStats();
    }

    public function getAdminBookings($filters) {
        return $this->service->getAdminBookings($filters);
    }

    public function getAdminBookingDetail($bookingId) {
        return $this->service->getAdminBookingDetail((int)$bookingId);
    }
}
