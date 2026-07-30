<?php
namespace App\Controllers;

use App\Services\DashboardService;

class DashboardController {
    private $dashboardService;

    public function __construct() {
        $this->dashboardService = new DashboardService();
    }

    public function index() {
        return $this->dashboardService->getDashboardData();
    }
}
