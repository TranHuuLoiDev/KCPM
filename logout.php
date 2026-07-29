<?php
require_once 'config.php';

use App\Controllers\AuthController;

$authController = new AuthController();
$authController->handleLogout();