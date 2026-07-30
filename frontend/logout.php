<?php
require_once '../backend/config.php';

use App\Controllers\AuthController;

$authController = new AuthController();
$authController->handleLogout();

