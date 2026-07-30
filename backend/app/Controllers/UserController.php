<?php
namespace App\Controllers;

use App\Services\UserService;

class UserController {
    private $service;

    public function __construct() {
        $this->service = new UserService();
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];

            if ($action === 'add' || $action === 'edit') {
                $data = [
                    'first_name' => trim($_POST['first_name'] ?? ''),
                    'last_name' => trim($_POST['last_name'] ?? ''),
                    'email' => trim($_POST['email'] ?? ''),
                    'phone' => trim($_POST['phone'] ?? ''),
                    'password' => $_POST['password'] ?? '', // password validation handles empty strings
                    'birth_date' => trim($_POST['birth_date'] ?? ''),
                    'role' => $_POST['role'] ?? 'user'
                ];

                if ($action === 'add') {
                    return $this->service->addUser($data);
                } else {
                    $id = (int)($_POST['id'] ?? 0);
                    return $this->service->updateUser($id, $data);
                }
            } 
            elseif ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                return $this->service->deleteUser($id);
            }
        }
        return null;
    }

    public function getAllUsers() {
        return $this->service->getAllUsers();
    }

    public function getUsersByFilter($keyword) {
        return $this->service->getUsersByFilter($keyword);
    }

    public function searchUsers($keyword = '') {
        return trim($keyword) === '' ? $this->getAllUsers() : $this->getUsersByFilter($keyword);
    }

    public function getUserById($id) {
        return $this->service->getUserById($id);
    }

    public function getStats() {
        return $this->service->getStats();
    }
}
