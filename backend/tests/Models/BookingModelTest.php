<?php

namespace Tests\Models;

use App\Models\BookingModel;
use PHPUnit\Framework\TestCase;

class BookingModelTest extends TestCase
{
    private BookingModel $model;
    private array $insertedIds = [];

    // user id 2 (John Doe) đã có sẵn trong dữ liệu seed BookingTicketDatabase.sql
    private const SEED_USER_ID = 2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new BookingModel();
    }

    protected function tearDown(): void
    {
        foreach ($this->insertedIds as $id) {
            $this->model->deleteBooking($id);
        }
        parent::tearDown();
    }

    public function testCreateBookingReturnsNewId(): void
    {
        $id = $this->model->createBooking(self::SEED_USER_ID, 150000, 'momo');
        $this->insertedIds[] = $id;

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);

        $booking = $this->model->getById($id);
        $this->assertSame('paid', $booking['status']);
        $this->assertSame('momo', $booking['payment_method_id']);
    }

    public function testGetByIdAndUserReturnsNullForWrongUser(): void
    {
        $id = $this->model->createBooking(self::SEED_USER_ID, 100000, 'vnpay');
        $this->insertedIds[] = $id;

        $result = $this->model->getByIdAndUser($id, 999999);

        $this->assertNull($result);
    }

    public function testGetByIdAndUserReturnsBookingForCorrectUser(): void
    {
        $id = $this->model->createBooking(self::SEED_USER_ID, 100000, 'vnpay');
        $this->insertedIds[] = $id;

        $result = $this->model->getByIdAndUser($id, self::SEED_USER_ID);

        $this->assertNotNull($result);
        $this->assertSame((string)$id, (string)$result['id']);
    }

    public function testCancelBookingSucceeds(): void
    {
        $id = $this->model->createBooking(self::SEED_USER_ID, 100000, 'bank_transfer');
        $this->insertedIds[] = $id;

        $result = $this->model->cancelBooking($id, self::SEED_USER_ID);

        $this->assertTrue($result);

        $booking = $this->model->getById($id);
        $this->assertSame('canceled', $booking['status']);
    }

    public function testCancelBookingFailsForWrongUser(): void
    {
        $id = $this->model->createBooking(self::SEED_USER_ID, 100000, 'momo');
        $this->insertedIds[] = $id;

        $result = $this->model->cancelBooking($id, 999999);

        $this->assertFalse($result);
    }

    public function testGetTotalBookingsReturnsNumeric(): void
    {
        $count = $this->model->getTotalBookings();

        $this->assertIsNumeric($count);
    }

    public function testGetTotalRevenueReturnsNumeric(): void
    {
        $revenue = $this->model->getTotalRevenue();

        $this->assertIsNumeric($revenue);
    }

    public function testGetTodayBookingsCountReturnsNumeric(): void
    {
        $count = $this->model->getTodayBookingsCount();

        $this->assertIsNumeric($count);
    }

    public function testGetTotalSpentByUserReturnsInt(): void
    {
        $total = $this->model->getTotalSpentByUser(self::SEED_USER_ID);

        $this->assertIsInt($total);
        $this->assertGreaterThanOrEqual(0, $total);
    }

    public function testGetAdminBookingStatsReturnsExpectedKeys(): void
    {
        $stats = $this->model->getAdminBookingStats();

        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('paid', $stats);
        $this->assertArrayHasKey('canceled', $stats);
        $this->assertArrayHasKey('today', $stats);
    }

    public function testGetErrorReturnsString(): void
    {
        $error = $this->model->getError();

        $this->assertIsString($error);
    }
}