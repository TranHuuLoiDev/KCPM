<?php
require_once "config.php";
use App\Services\BookingService;
use App\Models\BookingModel;
$service = new BookingService();
// Create a fake booking for user 1, showtime 1, seats 1 and 2
$result = $service->processBooking(1, 1, [1, 2], "cash");
var_dump($result);
if ($result["status"] === "error") {
    $model = new BookingModel();
    echo "DB Error: " . $model->getError();
}

