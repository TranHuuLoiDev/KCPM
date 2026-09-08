<?php

namespace Tests\Services;

use App\Services\SeatService;
use PHPUnit\Framework\TestCase;

class SeatServiceTest extends TestCase
{
    private SeatService $service;

    // Dữ liệu có sẵn từ seed BookingTicketDatabase.sql
    private const SEED_ROOM_ID = 1;
    private const SEED_SHOWTIME_ID = 1;
    private const SEED_SEAT_IDS = [1, 2];

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SeatService();
    }

    // ---- getBookedSeats ----

    public function testGetBookedSeatsReturnsEmptyArrayForInvalidShowtime(): void
    {
        $result = $this->service->getBookedSeats(0);

        $this->assertSame([], $result);
    }

    public function testGetBookedSeatsReturnsArrayForValidShowtime(): void
    {
        $result = $this->service->getBookedSeats(self::SEED_SHOWTIME_ID);

        $this->assertIsArray($result);
    }

    // ---- getSeatsByRoomId ----

    public function testGetSeatsByRoomIdReturnsEmptyArrayForInvalidRoom(): void
    {
        $result = $this->service->getSeatsByRoomId(0);

        $this->assertSame([], $result);
    }

    public function testGetSeatsByRoomIdReturnsArrayForValidRoom(): void
    {
        $result = $this->service->getSeatsByRoomId(self::SEED_ROOM_ID);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('seat_row', $result[0]);
    }

    // ---- getSeatsByIds ----

    public function testGetSeatsByIdsReturnsEmptyArrayForEmptyInput(): void
    {
        $result = $this->service->getSeatsByIds([]);

        $this->assertSame([], $result);
    }

    public function testGetSeatsByIdsReturnsArrayForValidIds(): void
    {
        $result = $this->service->getSeatsByIds(self::SEED_SEAT_IDS);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    // ---- getNextRowLetter ----

    public function testGetNextRowLetterReturnsAForEmptyArray(): void
    {
        $this->assertSame('A', $this->service->getNextRowLetter([]));
    }

    public function testGetNextRowLetterReturnsNextLetter(): void
    {
        $this->assertSame('C', $this->service->getNextRowLetter(['A', 'B']));
    }

    public function testGetNextRowLetterReturnsNullWhenPastH(): void
    {
        $this->assertNull($this->service->getNextRowLetter(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']));
    }

    // ---- getDisplayRows ----

    public function testGetDisplayRowsReturnsSortedArray(): void
    {
        $rows = $this->service->getDisplayRows(self::SEED_ROOM_ID);

        $this->assertIsArray($rows);
        $sorted = $rows;
        sort($sorted);
        $this->assertSame($sorted, $rows);
    }

    public function testGetDisplayRowsIncludesShowRowIfNotPresent(): void
    {
        // dùng room chưa từng có ghế row 'H' để đảm bảo test độc lập với dữ liệu thật
        $rows = $this->service->getDisplayRows(self::SEED_ROOM_ID, 'H');

        $this->assertContains('H', $rows);
    }

    // ---- getAllRooms / getAllSeatTypes (đơn giản, không cần fixture) ----

    public function testGetAllRoomsReturnsArray(): void
    {
        $this->assertIsArray($this->service->getAllRooms());
    }

    public function testGetAllSeatTypesReturnsArray(): void
    {
        $this->assertIsArray($this->service->getAllSeatTypes());
    }
    // ---- BVA validateSeatInput ----

public function testValidateSeatNumberBelowMinimum(): void
{
    $result = $this->service->validateSeatInput([
        'room_id' => 1,
        'seat_row' => 'A',
        'seat_number' => 0,
        'seat_type_id' => 1,
        'is_active' => true
    ]);

    $this->assertSame('error', $result['status']);
}

public function testValidateSeatNumberAtMinimum(): void
{
    $result = $this->service->validateSeatInput([
        'room_id' => 1,
        'seat_row' => 'A',
        'seat_number' => 1,
        'seat_type_id' => 1,
        'is_active' => true
    ]);

    $this->assertSame('success', $result['status']);
}

public function testValidateSeatNumberMinPlusOne(): void
{
    $result = $this->service->validateSeatInput([
        'room_id' => 1,
        'seat_row' => 'A',
        'seat_number' => 2,
        'seat_type_id' => 1,
        'is_active' => true
    ]);

    $this->assertSame('success', $result['status']);
}

public function testValidateSeatNumberMaxMinusOne(): void
{
    $result = $this->service->validateSeatInput([
        'room_id' => 1,
        'seat_row' => 'A',
        'seat_number' => 11,
        'seat_type_id' => 1,
        'is_active' => true
    ]);

    $this->assertSame('success', $result['status']);
}

public function testValidateSeatNumberAtMaximum(): void
{
    $result = $this->service->validateSeatInput([
        'room_id' => 1,
        'seat_row' => 'A',
        'seat_number' => 12,
        'seat_type_id' => 1,
        'is_active' => true
    ]);

    $this->assertSame('success', $result['status']);
}

public function testValidateSeatNumberAboveMaximum(): void
{
    $result = $this->service->validateSeatInput([
        'room_id' => 1,
        'seat_row' => 'A',
        'seat_number' => 13,
        'seat_type_id' => 1,
        'is_active' => true
    ]);

    $this->assertSame('error', $result['status']);
}

public function testValidateSeatRejectsInvalidRow(): void
{
    $result = $this->service->validateSeatInput([
        'room_id' => 1,
        'seat_row' => 'I',
        'seat_number' => 1,
        'seat_type_id' => 1,
        'is_active' => true
    ]);

    $this->assertSame('error', $result['status']);
}

    public function testValidateSeatRejectsInvalidRoom(): void
    {
        $result = $this->service->validateSeatInput([
            'room_id' => 0,
            'seat_row' => 'A',
            'seat_number' => 1,
            'seat_type_id' => 1,
            'is_active' => true
        ]);

        $this->assertSame('error', $result['status']);
    }

    public function testValidateSeatRejectsInvalidSeatType(): void
    {
        $result = $this->service->validateSeatInput([
            'room_id' => 1,
            'seat_row' => 'A',
            'seat_number' => 1,
            'seat_type_id' => 0,
            'is_active' => true
        ]);

        $this->assertSame('error', $result['status']);
    }
}