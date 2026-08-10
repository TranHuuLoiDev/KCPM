<?php

namespace Tests\Services;

use App\Services\SeatService;
use App\Services\RoomService;
use App\Services\TheatreService;
use PHPUnit\Framework\TestCase;

class SeatServiceCrudTest extends TestCase
{
    private SeatService $service;
    private RoomService $roomService;
    private TheatreService $theatreService;
    private int $theatreId;
    private int $roomId;
    private array $insertedSeatIds = [];

    // seat_types có sẵn từ seed: 1 = REGULAR, 2 = VIP
    private const SEAT_TYPE_ID = 1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SeatService();
        $this->roomService = new RoomService();
        $this->theatreService = new TheatreService();

        $theatreName = 'Rap Cho Seat Test ' . uniqid();
        $this->theatreService->addTheatre([
            'name' => $theatreName,
            'address' => '1 Duong Test',
            'city' => 'Ho Chi Minh',
            'phone' => '0922222222',
            'total_screens' => 1,
        ]);
        $theatres = $this->theatreService->getAllTheatres();
        $foundTheatre = array_values(array_filter($theatres, fn($t) => $t['name'] === $theatreName))[0];
        $this->theatreId = (int)$foundTheatre['id'];

        $roomName = 'Phong Cho Seat Test ' . uniqid();
        $this->roomService->addRoom([
            'name' => $roomName,
            'theatre_id' => $this->theatreId,
            'total_seats' => 0,
            'is_active' => 1,
        ]);
        $rooms = $this->roomService->getAllRooms();
        $foundRoom = array_values(array_filter($rooms, fn($r) => $r['name'] === $roomName))[0];
        $this->roomId = (int)$foundRoom['id'];
    }

    protected function tearDown(): void
    {
        foreach ($this->insertedSeatIds as $id) {
            $this->service->deleteSeat($id);
        }
        $this->roomService->deleteRoom($this->roomId);
        $this->theatreService->deleteTheatre($this->theatreId);
        parent::tearDown();
    }

    private function sampleData(array $overrides = []): array
    {
        return array_merge([
            'room_id' => $this->roomId,
            'seat_row' => 'A',
            'seat_number' => 1,
            'seat_type_id' => self::SEAT_TYPE_ID,
            'is_active' => true,
        ], $overrides);
    }

    private function insertAndTrack(array $data): int
    {
        $this->service->addSeat($data);
        $seats = $this->service->getAllSeats($this->roomId);
        $found = array_values(array_filter(
            $seats,
            fn($s) => $s['seat_row'] === $data['seat_row'] && (int)$s['seat_number'] === (int)$data['seat_number']
        ));
        $id = (int)$found[0]['id'];
        $this->insertedSeatIds[] = $id;
        return $id;
    }

    // ---- addSeat ----

    public function testAddSeatFailsWhenRoomInvalid(): void
    {
        $result = $this->service->addSeat($this->sampleData(['room_id' => 999999]));

        $this->assertSame('error', $result['status']);
        $this->assertSame('Phòng chiếu không hợp lệ!', $result['message']);
    }

    public function testAddSeatFailsWhenSeatRowInvalid(): void
    {
        $result = $this->service->addSeat($this->sampleData(['seat_row' => 'Z']));

        $this->assertSame('error', $result['status']);
        $this->assertSame('Hàng ghế phải từ A đến H!', $result['message']);
    }

    public function testAddSeatFailsWhenSeatNumberOutOfRange(): void
    {
        $result = $this->service->addSeat($this->sampleData(['seat_number' => 13]));

        $this->assertSame('error', $result['status']);
        $this->assertSame('Số ghế phải từ 1 đến 12!', $result['message']);
    }

    public function testAddSeatFailsWhenSeatTypeInvalid(): void
    {
        $result = $this->service->addSeat($this->sampleData(['seat_type_id' => 999999]));

        $this->assertSame('error', $result['status']);
        $this->assertSame('Loại ghế không hợp lệ!', $result['message']);
    }

    public function testAddSeatSucceeds(): void
    {
        $data = $this->sampleData();
        $result = $this->service->addSeat($data);

        $this->assertSame('success', $result['status']);

        $id = $this->insertAndTrack($data);
        $this->assertGreaterThan(0, $id);
    }

    public function testAddSeatFailsWhenPositionAlreadyExists(): void
    {
        $data = $this->sampleData(['seat_row' => 'B', 'seat_number' => 5]);
        $this->insertAndTrack($data);

        $result = $this->service->addSeat($data);

        $this->assertSame('error', $result['status']);
        $this->assertStringContainsString('đã tồn tại trong phòng này', $result['message']);
    }

    // ---- updateSeat ----

    public function testUpdateSeatFailsWithInvalidId(): void
    {
        $result = $this->service->updateSeat(0, $this->sampleData());

        $this->assertSame('error', $result['status']);
        $this->assertSame('ID ghế không hợp lệ!', $result['message']);
    }

    public function testUpdateSeatFailsWhenSeatDoesNotExist(): void
    {
        $result = $this->service->updateSeat(999999, $this->sampleData());

        $this->assertSame('error', $result['status']);
        $this->assertSame('Ghế không tồn tại!', $result['message']);
    }

    public function testUpdateSeatSucceeds(): void
    {
        $id = $this->insertAndTrack($this->sampleData(['seat_row' => 'C', 'seat_number' => 1]));

        $updated = $this->sampleData(['seat_row' => 'C', 'seat_number' => 2]);
        $result = $this->service->updateSeat($id, $updated);

        $this->assertSame('success', $result['status']);
    }

    // ---- deleteSeat ----

    public function testDeleteSeatFailsWithInvalidId(): void
    {
        $result = $this->service->deleteSeat(0);

        $this->assertSame('error', $result['status']);
        $this->assertSame('ID ghế không hợp lệ!', $result['message']);
    }

    public function testDeleteSeatFailsWhenSeatDoesNotExist(): void
    {
        $result = $this->service->deleteSeat(999999);

        $this->assertSame('error', $result['status']);
        $this->assertSame('Ghế không tồn tại!', $result['message']);
    }

    public function testDeleteSeatSucceeds(): void
    {
        $id = $this->insertAndTrack($this->sampleData(['seat_row' => 'D', 'seat_number' => 1]));

        $result = $this->service->deleteSeat($id);

        $this->assertSame('success', $result['status']);

        $this->insertedSeatIds = array_diff($this->insertedSeatIds, [$id]);
    }

    // ---- quickAddSeat ----

    public function testQuickAddSeatFailsWhenRoomInvalid(): void
    {
        $result = $this->service->quickAddSeat(999999, 'A');

        $this->assertSame('error', $result['status']);
        $this->assertSame('Phòng chiếu không hợp lệ!', $result['message']);
    }

    public function testQuickAddSeatFailsWhenRowInvalid(): void
    {
        $result = $this->service->quickAddSeat($this->roomId, 'Z');

        $this->assertSame('error', $result['status']);
        $this->assertSame('Hàng ghế không hợp lệ!', $result['message']);
    }

    public function testQuickAddSeatSucceeds(): void
    {
        $result = $this->service->quickAddSeat($this->roomId, 'E');

        $this->assertSame('success', $result['status']);

        $seats = $this->service->getAllSeats($this->roomId);
        $found = array_values(array_filter($seats, fn($s) => $s['seat_row'] === 'E'));
        $this->insertedSeatIds[] = (int)$found[0]['id'];
    }

    // ---- generateSeats ----

    public function testGenerateSeatsFailsWhenRoomInvalid(): void
    {
        $result = $this->service->generateSeats(999999, 'A', 'B', 5, self::SEAT_TYPE_ID);

        $this->assertSame('error', $result['status']);
        $this->assertSame('Phòng chiếu không hợp lệ!', $result['message']);
    }

    public function testGenerateSeatsSucceeds(): void
    {
        $result = $this->service->generateSeats($this->roomId, 'F', 'F', 3, self::SEAT_TYPE_ID);

        $this->assertSame('success', $result['status']);

        $seats = $this->service->getAllSeats($this->roomId);
        foreach ($seats as $seat) {
            if ($seat['seat_row'] === 'F') {
                $this->insertedSeatIds[] = (int)$seat['id'];
            }
        }
    }

    // ---- bulkDeleteSeats ----

    public function testBulkDeleteSeatsFailsWhenRoomInvalid(): void
    {
        $result = $this->service->bulkDeleteSeats(999999, 'A', 'B', 1, 5);

        $this->assertSame('error', $result['status']);
        $this->assertSame('Phòng chiếu không hợp lệ!', $result['message']);
    }

    public function testBulkDeleteSeatsSucceeds(): void
    {
        $this->service->generateSeats($this->roomId, 'G', 'G', 3, self::SEAT_TYPE_ID);

        $result = $this->service->bulkDeleteSeats($this->roomId, 'G', 'G', 1, 3);

        $this->assertSame('success', $result['status']);
    }

    // ---- getDisplayRows: nhánh thêm showRow khi chưa tồn tại ----

    public function testGetDisplayRowsIncludesShowRowWhenNotPresent(): void
    {
        // roomId là phòng tạm hoàn toàn trống, chưa có ghế hàng H nào
        $rows = $this->service->getDisplayRows($this->roomId, 'H');

        $this->assertContains('H', $rows);
    }
}