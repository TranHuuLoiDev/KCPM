<?php

namespace Tests\Services;

use App\Services\RoomService;
use App\Services\TheatreService;
use PHPUnit\Framework\TestCase;

class RoomServiceTest extends TestCase
{
    private RoomService $service;
    private TheatreService $theatreService;
    private int $theatreId;
    private array $insertedRoomIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RoomService();
        $this->theatreService = new TheatreService();

        $theatreName = 'Rap Cho Room Test ' . uniqid();
        $this->theatreService->addTheatre([
            'name' => $theatreName,
            'address' => '1 Duong Test',
            'city' => 'Ho Chi Minh',
            'phone' => '0911111111',
            'total_screens' => 3,
        ]);

        $theatres = $this->theatreService->getAllTheatres();
        $found = array_filter($theatres, fn($t) => $t['name'] === $theatreName);
        $this->theatreId = (int)array_values($found)[0]['id'];
    }

    protected function tearDown(): void
    {
        foreach ($this->insertedRoomIds as $id) {
            $this->service->deleteRoom($id);
        }
        $this->theatreService->deleteTheatre($this->theatreId);
        parent::tearDown();
    }

    private function sampleData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Phong Test ' . uniqid(),
            'theatre_id' => $this->theatreId,
            'total_seats' => 40,
            'is_active' => 1,
        ], $overrides);
    }

    private function insertAndTrack(array $data): int
    {
        $this->service->addRoom($data);
        $rooms = $this->service->getAllRooms();
        $found = array_filter($rooms, fn($r) => $r['name'] === $data['name']);
        $room = array_values($found)[0];
        $this->insertedRoomIds[] = (int)$room['id'];
        return (int)$room['id'];
    }

    // ---- Validate ----

    public function testAddRoomFailsWhenNameEmpty(): void
    {
        $result = $this->service->addRoom($this->sampleData(['name' => '']));

        $this->assertSame('error', $result['status']);
        $this->assertSame('Tên phòng không được để trống!', $result['message']);
    }

    public function testAddRoomFailsWhenTheatreInvalid(): void
    {
        $result = $this->service->addRoom($this->sampleData(['theatre_id' => 999999]));

        $this->assertSame('error', $result['status']);
        $this->assertSame('Rạp chiếu không hợp lệ!', $result['message']);
    }

    public function testAddRoomFailsWhenTotalSeatsLessThanOne(): void
    {
        $result = $this->service->addRoom($this->sampleData(['total_seats' => 0]));

        $this->assertSame('error', $result['status']);
        $this->assertSame('Số ghế phải lớn hơn 0!', $result['message']);
    }

    public function testAddRoomFailsWhenNameAlreadyExists(): void
    {
        $data = $this->sampleData(['name' => 'Phong Trung Ten']);
        $this->insertAndTrack($data);

        $result = $this->service->addRoom($this->sampleData(['name' => 'Phong Trung Ten']));

        $this->assertSame('error', $result['status']);
        $this->assertSame("Tên phòng 'Phong Trung Ten' đã tồn tại trong hệ thống!", $result['message']);
    }

    // ---- Add ----

    public function testAddRoomSucceedsWithValidData(): void
    {
        $data = $this->sampleData();
        $result = $this->service->addRoom($data);

        $this->assertSame('success', $result['status']);

        $rooms = $this->service->getAllRooms();
        $found = array_filter($rooms, fn($r) => $r['name'] === $data['name']);
        $this->insertedRoomIds[] = (int)array_values($found)[0]['id'];
    }

    // ---- Update ----

    public function testUpdateRoomFailsWithInvalidId(): void
    {
        $result = $this->service->updateRoom(0, $this->sampleData());

        $this->assertSame('error', $result['status']);
        $this->assertSame('ID phòng không hợp lệ!', $result['message']);
    }

    public function testUpdateRoomFailsWhenRoomDoesNotExist(): void
    {
        $result = $this->service->updateRoom(999999, $this->sampleData());

        $this->assertSame('error', $result['status']);
        $this->assertSame('Phòng chiếu không tồn tại!', $result['message']);
    }

    public function testUpdateRoomSucceedsAndPersistsChanges(): void
    {
        $id = $this->insertAndTrack($this->sampleData(['name' => 'Ten Phong Cu']));

        $updated = $this->sampleData(['name' => 'Ten Phong Moi']);
        $result = $this->service->updateRoom($id, $updated);

        $this->assertSame('success', $result['status']);

        $room = $this->service->getRoomById($id);
        $this->assertSame('Ten Phong Moi', $room['name']);
    }

    // ---- Delete ----

    public function testDeleteRoomFailsWithInvalidId(): void
    {
        $result = $this->service->deleteRoom(0);

        $this->assertSame('error', $result['status']);
        $this->assertSame('ID phòng không hợp lệ!', $result['message']);
    }

    public function testDeleteRoomFailsWhenRoomDoesNotExist(): void
    {
        $result = $this->service->deleteRoom(999999);

        $this->assertSame('error', $result['status']);
        $this->assertSame('Phòng chiếu không tồn tại!', $result['message']);
    }

    public function testDeleteRoomSucceeds(): void
    {
        $id = $this->insertAndTrack($this->sampleData());

        $result = $this->service->deleteRoom($id);

        $this->assertSame('success', $result['status']);
        $this->assertNull($this->service->getRoomById($id));

        $this->insertedRoomIds = array_diff($this->insertedRoomIds, [$id]);
    }

    // ---- Get ----

    public function testGetRoomByIdReturnsNullForInvalidId(): void
    {
        $this->assertNull($this->service->getRoomById(0));
    }

    public function testGetRoomByIdReturnsRoomWithTheatreInfo(): void
    {
        $id = $this->insertAndTrack($this->sampleData(['name' => 'Phong Kiem Tra']));

        $room = $this->service->getRoomById($id);

        $this->assertNotNull($room);
        $this->assertSame('Phong Kiem Tra', $room['name']);
        $this->assertArrayHasKey('theatre_name', $room);
    }

    public function testGetAllRoomsReturnsArray(): void
    {
        $rooms = $this->service->getAllRooms();

        $this->assertIsArray($rooms);
    }

    public function testGetAllTheatresReturnsArray(): void
    {
        $theatres = $this->service->getAllTheatres();

        $this->assertIsArray($theatres);
    }
}