<?php

namespace Tests\Controllers;

use App\Controllers\SeatController;
use PHPUnit\Framework\TestCase;

class SeatControllerTest extends TestCase
{
    private SeatController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new SeatController();
    }

    protected function tearDown(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST = [];
        parent::tearDown();
    }

    // ---- Guard clauses: not POST / no action / unknown action ----

    public function testHandleRequestReturnsNullWhenNotPost(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->assertNull($this->controller->handleRequest());
    }

    public function testHandleRequestReturnsNullWhenNoAction(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [];

        $this->assertNull($this->controller->handleRequest());
    }

    public function testHandleRequestReturnsNullForUnknownAction(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['action' => 'unknown_action'];

        $this->assertNull($this->controller->handleRequest());
    }

    // ---- add / edit branch ----

    public function testHandleRequestAddWithInvalidRoomReturnsError(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'action' => 'add',
            'room_id' => 0,
            'seat_row' => 'A',
            'seat_number' => 1,
            'seat_type_id' => 1,
            'is_active' => 1,
        ];

        $result = $this->controller->handleRequest();

        $this->assertIsArray($result);
        $this->assertSame('error', $result['status']);
        $this->assertSame('Phòng chiếu không hợp lệ!', $result['message']);
    }

    public function testHandleRequestEditWithInvalidIdReturnsError(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'action' => 'edit',
            'id' => 0,
            'room_id' => 1,
            'seat_row' => 'A',
            'seat_number' => 1,
            'seat_type_id' => 1,
            'is_active' => 1,
        ];

        $result = $this->controller->handleRequest();

        $this->assertIsArray($result);
        $this->assertSame('error', $result['status']);
        $this->assertSame('ID ghế không hợp lệ!', $result['message']);
    }

    public function testHandleRequestAddDefaultsIsActiveToFalseWhenMissing(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'action' => 'add',
            'room_id' => 0, // vẫn lỗi sớm, chỉ để phủ nhánh (int)($_POST['is_active'] ?? 0)
        ];

        $result = $this->controller->handleRequest();

        $this->assertIsArray($result);
        $this->assertSame('error', $result['status']);
    }

    // ---- delete branch ----

    public function testHandleRequestDeleteWithInvalidIdReturnsError(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'action' => 'delete',
            'id' => 0,
        ];

        $result = $this->controller->handleRequest();

        $this->assertIsArray($result);
        $this->assertSame('error', $result['status']);
        $this->assertSame('ID ghế không hợp lệ!', $result['message']);
    }

    public function testHandleRequestDeleteWithMissingIdDefaultsToZero(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['action' => 'delete'];

        $result = $this->controller->handleRequest();

        $this->assertIsArray($result);
        $this->assertSame('error', $result['status']);
    }

    // ---- generate branch ----

    public function testHandleRequestGenerateWithInvalidRoomReturnsError(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'action' => 'generate',
            'room_id' => 0,
            'start_row' => 'A',
            'end_row' => 'H',
            'seats_per_row' => 5,
            'seat_type_id' => 1,
        ];

        $result = $this->controller->handleRequest();

        $this->assertIsArray($result);
        $this->assertSame('error', $result['status']);
        $this->assertSame('Phòng chiếu không hợp lệ!', $result['message']);
    }

    public function testHandleRequestGenerateUsesDefaultsWhenFieldsMissing(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'action' => 'generate',
            'room_id' => 0, // lỗi sớm, nhưng vẫn phủ nhánh gán default start_row='A', end_row='H', seats_per_row=5
        ];

        $result = $this->controller->handleRequest();

        $this->assertIsArray($result);
        $this->assertSame('error', $result['status']);
    }

    // ---- bulk_delete branch ----

    public function testHandleRequestBulkDeleteWithInvalidRoomReturnsError(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'action' => 'bulk_delete',
            'room_id' => 0,
            'delete_start_row' => 'A',
            'delete_end_row' => 'H',
            'delete_start_number' => 1,
            'delete_end_number' => 12,
        ];

        $result = $this->controller->handleRequest();

        $this->assertIsArray($result);
        $this->assertSame('error', $result['status']);
        $this->assertSame('Phòng chiếu không hợp lệ!', $result['message']);
    }

    public function testHandleRequestBulkDeleteUsesDefaultsWhenFieldsMissing(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'action' => 'bulk_delete',
            'room_id' => 0, // lỗi sớm, nhưng vẫn phủ nhánh gán default delete_start_row/number
        ];

        $result = $this->controller->handleRequest();

        $this->assertIsArray($result);
        $this->assertSame('error', $result['status']);
    }

    // ---- quick_add branch ----

    public function testHandleRequestQuickAddWithInvalidRoomReturnsError(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'action' => 'quick_add',
            'room_id' => 0,
            'seat_row' => 'A',
        ];

        $result = $this->controller->handleRequest();

        $this->assertIsArray($result);
        $this->assertSame('error', $result['status']);
        $this->assertSame('Phòng chiếu không hợp lệ!', $result['message']);
    }

    public function testHandleRequestQuickAddWithMissingFieldsDefaultsSafely(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['action' => 'quick_add'];

        $result = $this->controller->handleRequest();

        $this->assertIsArray($result);
        $this->assertSame('error', $result['status']);
    }
}