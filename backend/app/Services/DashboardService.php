<?php
namespace App\Services;

use App\Models\DashboardModel;
use App\Models\MovieModel;
use App\Models\UserModel;
use App\Models\BookingModel;

class DashboardService {
    private $dashboardModel;
    private $movieModel;
    private $userModel;
    private $bookingModel;

    public function __construct() {
        $this->dashboardModel = new DashboardModel();
        $this->movieModel = new MovieModel();
        $this->userModel = new UserModel();
        $this->bookingModel = new BookingModel();
    }

    public function getDashboardData() {
        $data = [
            'total_movies' => $this->movieModel->getTotalMovies(),
            'total_users' => $this->userModel->getStats()['total_users'],
            'total_bookings' => $this->bookingModel->getTotalBookings(),
            'total_revenue' => $this->bookingModel->getTotalRevenue(),
            'today_bookings_count' => $this->bookingModel->getTodayBookingsCount(),
            'today_bookings_list' => $this->dashboardModel->getTodayBookings(),
            'popular_movies' => $this->dashboardModel->getPopularMovies(),
            'loyal_customers' => $this->dashboardModel->getLoyalCustomers()
        ];

        return $data;
    }
}
