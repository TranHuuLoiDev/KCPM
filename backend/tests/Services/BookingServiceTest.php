<?php

namespace Tests\Services;

use App\Models\BookingModel;
use App\Models\SeatModel;
use App\Models\ShowtimeModel;
use App\Models\TicketModel;
use App\Services\BookingService;
use PHPUnit\Framework\TestCase;

class BookingServiceTest extends TestCase
{
    private BookingService $service;

    private $bookingModel;
    private $showtimeModel;
    private $seatModel;
    private $ticketModel;

    protected function setUp(): void
    {
        parent::setUp();

        /*
         * BookingService hiện tại tự new các Model trong constructor.
         * Vì vậy dùng Reflection để inject mock Model,
         * giúp test business logic mà không phụ thuộc database thật.
         */
        $this->bookingModel = $this->createMock(BookingModel::class);
        $this->showtimeModel = $this->createMock(ShowtimeModel::class);
        $this->seatModel = $this->createMock(SeatModel::class);
        $this->ticketModel = $this->createMock(TicketModel::class);

        $reflection = new \ReflectionClass(BookingService::class);
        $this->service = $reflection->newInstanceWithoutConstructor();

        $this->setPrivateProperty(
            $this->service,
            'bookingModel',
            $this->bookingModel
        );

        $this->setPrivateProperty(
            $this->service,
            'showtimeModel',
            $this->showtimeModel
        );

        $this->setPrivateProperty(
            $this->service,
            'seatModel',
            $this->seatModel
        );

        $this->setPrivateProperty(
            $this->service,
            'ticketModel',
            $this->ticketModel
        );
    }

    private function setPrivateProperty(
        object $object,
        string $property,
        mixed $value
    ): void {
        $reflection = new \ReflectionClass($object);
        $propertyReflection = $reflection->getProperty($property);
        $propertyReflection->setAccessible(true);
        $propertyReflection->setValue($object, $value);
    }

    // =========================================================
    // processBooking()
    // =========================================================

    public function testProcessBookingFailsWhenUserIsNotLoggedIn(): void
    {
        $result = $this->service->processBooking(
            0,
            1,
            [1],
            'cash'
        );

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Vui lòng đăng nhập để đặt vé.',
            $result['message']
        );
    }

    public function testProcessBookingFailsWhenShowtimeIdIsInvalid(): void
    {
        $result = $this->service->processBooking(
            1,
            0,
            [1],
            'cash'
        );

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Suất chiếu không hợp lệ.',
            $result['message']
        );
    }

    public function testProcessBookingFailsWhenSeatListIsEmpty(): void
    {
        $result = $this->service->processBooking(
            1,
            1,
            [],
            'cash'
        );

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Vui lòng chọn ít nhất 1 ghế.',
            $result['message']
        );
    }

    public function testProcessBookingFailsWhenSeatListIsNotArray(): void
    {
        $result = $this->service->processBooking(
            1,
            1,
            '1',
            'cash'
        );

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Vui lòng chọn ít nhất 1 ghế.',
            $result['message']
        );
    }

    public function testProcessBookingFailsWhenShowtimeDoesNotExist(): void
    {
        $this->showtimeModel
            ->expects($this->once())
            ->method('getDetailById')
            ->with(999)
            ->willReturn(null);

        $result = $this->service->processBooking(
            1,
            999,
            [1],
            'cash'
        );

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Suất chiếu không khả dụng.',
            $result['message']
        );
    }

    public function testProcessBookingFailsWhenShowtimeIsNotActive(): void
    {
        $this->showtimeModel
            ->expects($this->once())
            ->method('getDetailById')
            ->with(1)
            ->willReturn([
                'status' => 'inactive',
                'room_id' => 1,
                'show_date' => date('Y-m-d', strtotime('+1 day')),
                'start_time' => '20:00:00',
                'base_price' => 100000
            ]);

        $result = $this->service->processBooking(
            1,
            1,
            [1],
            'cash'
        );

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Suất chiếu không khả dụng.',
            $result['message']
        );
    }

    public function testProcessBookingFailsWhenShowtimeAlreadyStarted(): void
    {
        $this->showtimeModel
            ->method('getDetailById')
            ->willReturn([
                'status' => 'active',
                'room_id' => 1,
                'show_date' => date('Y-m-d', strtotime('-1 day')),
                'start_time' => '20:00:00',
                'base_price' => 100000
            ]);

        $result = $this->service->processBooking(
            1,
            1,
            [1],
            'cash'
        );

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Suất chiếu này đã bắt đầu hoặc đã kết thúc.',
            $result['message']
        );
    }

    public function testProcessBookingFailsWhenSeatDoesNotExist(): void
    {
        $this->showtimeModel
            ->method('getDetailById')
            ->willReturn([
                'status' => 'active',
                'room_id' => 1,
                'show_date' => date('Y-m-d', strtotime('+1 day')),
                'start_time' => '20:00:00',
                'base_price' => 100000
            ]);

        $this->seatModel
            ->method('getByIds')
            ->with([999])
            ->willReturn([]);

        $result = $this->service->processBooking(
            1,
            1,
            [999],
            'cash'
        );

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Danh sách ghế không hợp lệ.',
            $result['message']
        );
    }

    public function testProcessBookingFailsWhenSeatBelongsToAnotherRoom(): void
    {
        $this->showtimeModel
            ->method('getDetailById')
            ->willReturn([
                'status' => 'active',
                'room_id' => 1,
                'show_date' => date('Y-m-d', strtotime('+1 day')),
                'start_time' => '20:00:00',
                'base_price' => 100000
            ]);

        $this->seatModel
            ->method('getByIds')
            ->with([5])
            ->willReturn([
                [
                    'id' => 5,
                    'room_id' => 2,
                    'is_active' => 1,
                    'seat_type_price' => 20000
                ]
            ]);

        $result = $this->service->processBooking(
            1,
            1,
            [5],
            'cash'
        );

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Ghế không thuộc phòng chiếu này.',
            $result['message']
        );
    }

    public function testProcessBookingFailsWhenSeatIsInactive(): void
    {
        $this->showtimeModel
            ->method('getDetailById')
            ->willReturn([
                'status' => 'active',
                'room_id' => 1,
                'show_date' => date('Y-m-d', strtotime('+1 day')),
                'start_time' => '20:00:00',
                'base_price' => 100000
            ]);

        $this->seatModel
            ->method('getByIds')
            ->willReturn([
                [
                    'id' => 5,
                    'room_id' => 1,
                    'is_active' => 0,
                    'seat_type_price' => 20000
                ]
            ]);

        $result = $this->service->processBooking(
            1,
            1,
            [5],
            'cash'
        );

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Có ghế không khả dụng.',
            $result['message']
        );
    }

    public function testProcessBookingFailsWhenSeatIsAlreadyBooked(): void
    {
        $this->showtimeModel
            ->method('getDetailById')
            ->willReturn([
                'status' => 'active',
                'room_id' => 1,
                'show_date' => date('Y-m-d', strtotime('+1 day')),
                'start_time' => '20:00:00',
                'base_price' => 100000
            ]);

        $this->seatModel
            ->method('getByIds')
            ->willReturn([
                [
                    'id' => 5,
                    'room_id' => 1,
                    'is_active' => 1,
                    'seat_type_price' => 20000
                ]
            ]);

        $this->ticketModel
            ->expects($this->once())
            ->method('isSeatBooked')
            ->with(1, 5)
            ->willReturn(true);

        $result = $this->service->processBooking(
            1,
            1,
            [5],
            'cash'
        );

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Có ghế vừa được đặt. Vui lòng chọn ghế khác.',
            $result['message']
        );
    }

    public function testProcessBookingSucceedsWithValidData(): void
    {
        $this->showtimeModel
            ->method('getDetailById')
            ->willReturn([
                'status' => 'active',
                'room_id' => 1,
                'show_date' => date('Y-m-d', strtotime('+1 day')),
                'start_time' => '20:00:00',
                'base_price' => 100000
            ]);

        $this->seatModel
            ->method('getByIds')
            ->with([5, 6])
            ->willReturn([
                [
                    'id' => 5,
                    'room_id' => 1,
                    'is_active' => 1,
                    'seat_type_price' => 20000
                ],
                [
                    'id' => 6,
                    'room_id' => 1,
                    'is_active' => 1,
                    'seat_type_price' => 30000
                ]
            ]);

        $this->ticketModel
            ->expects($this->exactly(2))
            ->method('isSeatBooked')
            ->willReturn(false);

        $this->bookingModel
            ->expects($this->once())
            ->method('beginTransaction');

        $this->bookingModel
            ->expects($this->once())
            ->method('createBooking')
            ->with(10, 250000, 'momo')
            ->willReturn(1001);

        $this->ticketModel
            ->expects($this->once())
            ->method('createMany')
            ->with(
                1001,
                1,
                [
                    [
                        'seat_id' => 5,
                        'price' => 120000
                    ],
                    [
                        'seat_id' => 6,
                        'price' => 130000
                    ]
                ]
            )
            ->willReturn(true);

        $this->bookingModel
            ->expects($this->once())
            ->method('commit');

        $result = $this->service->processBooking(
            10,
            1,
            [5, 6],
            'momo'
        );

        $this->assertSame('success', $result['status']);
        $this->assertSame(
            'Đặt vé thành công!',
            $result['message']
        );
        $this->assertSame(1001, $result['booking_id']);
    }

    public function testProcessBookingUsesCashWhenPaymentMethodIsInvalid(): void
    {
        $this->showtimeModel
            ->method('getDetailById')
            ->willReturn([
                'status' => 'active',
                'room_id' => 1,
                'show_date' => date('Y-m-d', strtotime('+1 day')),
                'start_time' => '20:00:00',
                'base_price' => 100000
            ]);

        $this->seatModel
            ->method('getByIds')
            ->willReturn([
                [
                    'id' => 5,
                    'room_id' => 1,
                    'is_active' => 1,
                    'seat_type_price' => 20000
                ]
            ]);

        $this->ticketModel
            ->method('isSeatBooked')
            ->willReturn(false);

        $this->bookingModel
            ->method('createBooking')
            ->with(10, 120000, 'cash')
            ->willReturn(1002);

        $this->ticketModel
            ->method('createMany')
            ->willReturn(true);

        $result = $this->service->processBooking(
            10,
            1,
            [5],
            'invalid_method'
        );

        $this->assertSame('success', $result['status']);
        $this->assertSame(1002, $result['booking_id']);
    }

    // =========================================================
    // getUserBookings()
    // =========================================================

    public function testGetUserBookingsReturnsEmptyArrayForInvalidUser(): void
    {
        $result = $this->service->getUserBookings(0);

        $this->assertSame([], $result);
    }

    public function testGetUserBookingsReturnsBookingsForValidUser(): void
    {
        $bookings = [
            ['id' => 1, 'status' => 'paid'],
            ['id' => 2, 'status' => 'pending']
        ];

        $this->bookingModel
            ->expects($this->once())
            ->method('getBookingsByUser')
            ->with(10)
            ->willReturn($bookings);

        $result = $this->service->getUserBookings(10);

        $this->assertSame($bookings, $result);
    }

    // =========================================================
    // cancelBooking()
    // =========================================================

    public function testCancelBookingFailsWhenUserIsInvalid(): void
    {
        $result = $this->service->cancelBooking(0, 10);

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Vui long dang nhap de huy ve.',
            $result['message']
        );
    }

    public function testCancelBookingFailsWhenBookingIdIsInvalid(): void
    {
        $result = $this->service->cancelBooking(10, 0);

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Booking khong hop le.',
            $result['message']
        );
    }

    public function testCancelBookingFailsWhenBookingDoesNotBelongToUser(): void
    {
        $this->bookingModel
            ->expects($this->once())
            ->method('getByIdAndUser')
            ->with(100, 10)
            ->willReturn(null);

        $result = $this->service->cancelBooking(10, 100);

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Khong tim thay booking can huy.',
            $result['message']
        );
    }

    public function testCancelBookingFailsWhenAlreadyCanceled(): void
    {
        $this->bookingModel
            ->method('getByIdAndUser')
            ->willReturn([
                'id' => 100,
                'status' => 'canceled'
            ]);

        $result = $this->service->cancelBooking(10, 100);

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Booking nay da duoc huy truoc do.',
            $result['message']
        );
    }

    // =========================================================
    // Admin / helper validation
    // =========================================================

    public function testGetAdminBookingDetailReturnsNullForInvalidId(): void
    {
        $result = $this->service->getAdminBookingDetail(0);

        $this->assertNull($result);
    }

    public function testGetTotalSpentByUserReturnsZeroForInvalidUser(): void
    {
        $result = $this->service->getTotalSpentByUser(0);

        $this->assertSame(0, $result);
    }

    public function testUpdateAdminBookingStatusFailsForInvalidId(): void
    {
        $result = $this->service->updateAdminBookingStatus(
            0,
            'paid'
        );

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Booking không hợp lệ.',
            $result['message']
        );
    }

    public function testUpdateAdminBookingStatusFailsForInvalidStatus(): void
    {
        $result = $this->service->updateAdminBookingStatus(
            10,
            'invalid'
        );

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Trạng thái booking không hợp lệ.',
            $result['message']
        );
    }

    public function testDeleteAdminBookingFailsForInvalidId(): void
    {
        $result = $this->service->deleteAdminBooking(0);

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Booking không hợp lệ.',
            $result['message']
        );
    }

    // =========================================================
    // normalizeAdminFilters()
    // =========================================================

    public function testNormalizeAdminFiltersKeepsValidFilters(): void
    {
        $result = $this->service->normalizeAdminFilters([
            'status' => 'paid',
            'from_date' => '2026-08-01',
            'to_date' => '2026-08-31',
            'search' => 'ABC123'
        ]);

        $this->assertSame([
            'status' => 'paid',
            'from_date' => '2026-08-01',
            'to_date' => '2026-08-31',
            'search' => 'ABC123'
        ], $result);
    }

    public function testNormalizeAdminFiltersRemovesInvalidStatusAndDates(): void
    {
        $result = $this->service->normalizeAdminFilters([
            'status' => 'invalid',
            'from_date' => '2026-99-99',
            'to_date' => 'abc',
            'search' => '  test  '
        ]);

        $this->assertSame('', $result['status']);
        $this->assertSame('', $result['from_date']);
        $this->assertSame('', $result['to_date']);
        $this->assertSame('test', $result['search']);
    }
}