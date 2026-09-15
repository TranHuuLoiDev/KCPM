<?php

namespace Tests\Services;

use App\Models\RoomModel;
use App\Models\SeatModel;
use App\Models\SeatTypeModel;
use App\Services\SeatService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

/** Executes every BVA/EP table row in the versioned assignment, without a database. */
class SeatAssignmentTest extends TestCase
{
    private const REPORT = '/docs/testing/modules/seat/083205006374_LuongQuocAn_BaoCao_Seat_Form_Assignment_Chot.md';

    public static function assignmentCases(): array
    {
        $lines = file(dirname(__DIR__, 3) . self::REPORT, FILE_IGNORE_NEW_LINES);
        $method = null;
        $cases = [];
        $counts = [];
        foreach ($lines as $line) {
            if (preg_match('/^# [456]\. METHOD [123].*`(\w+)\(/u', $line, $match)) {
                $method = $match[1];
            }
            if (!preg_match('/^\| (BVA|EP)-\d{2} \|/', $line)) {
                continue;
            }
            $cells = array_map('trim', explode('|', trim($line, '|')));
            $id = $cells[0];
            $key = $method . '/' . $id;
            if (isset($cases[$key]) || !in_array($method, ['validateSeatInput', 'generateSeats', 'bulkDeleteSeats'], true)) {
                throw new RuntimeException('Invalid or duplicate assignment case: ' . $key);
            }
            $inputs = array_slice($cells, 2, 5);
            foreach ($inputs as &$value) {
                $value = ctype_digit($value) ? (int) $value : ($value === 'true' ? true : $value);
            }
            unset($value);
            $expected = $cells[count($cells) - 2];
            if (strpos($expected, '**Không hợp lệ**') === 0) {
                $status = 'error';
            } elseif (strpos($expected, '**Hợp lệ**') === 0) {
                $status = 'success';
            } else {
                throw new RuntimeException('Unknown expected result: ' . $key);
            }
            $cases[$key] = [$method, $id, $inputs, $status];
            $group = $method . '/' . substr($id, 0, strpos($id, '-'));
            $counts[$group] = ($counts[$group] ?? 0) + 1;
        }
        $required = ['validateSeatInput/BVA' => 10, 'validateSeatInput/EP' => 8,
            'generateSeats/BVA' => 15, 'generateSeats/EP' => 10,
            'bulkDeleteSeats/BVA' => 20, 'bulkDeleteSeats/EP' => 12];
        if ($counts !== $required) {
            throw new RuntimeException('Assignment must contain exactly 45 BVA and 30 EP rows.');
        }
        foreach ($required as $group => $count) {
            for ($i = 1; $i <= $count; $i++) {
                if (!isset($cases[$group . '-' . sprintf('%02d', $i)])) {
                    throw new RuntimeException('Missing assignment case in ' . $group);
                }
            }
        }
        return $cases;
    }

    /** @dataProvider assignmentCases */
    public function testAssignmentCase(string $method, string $id, array $input, string $status): void
    {
        // Only room/type 1 exists. Every dataset receives fresh, isolated model doubles.
        $room = $this->createMock(RoomModel::class);
        $type = $this->createMock(SeatTypeModel::class);
        $seats = $this->createMock(SeatModel::class);
        $room->method('findById')->willReturnCallback(static fn($id) => $id === 1 ? ['id' => 1] : null);
        $type->method('findById')->willReturnCallback(static fn($id) => $id === 1 ? ['id' => 1] : null);
        $reflection = new ReflectionClass(SeatService::class);
        $service = $reflection->newInstanceWithoutConstructor();
        foreach (['model' => $seats, 'roomModel' => $room, 'seatTypeModel' => $type] as $name => $model) {
            $property = $reflection->getProperty($name);
            $property->setAccessible(true);
            $property->setValue($service, $model);
        }

        $expectedMessage = self::errorMessage($method, $id);
        if ($method === 'validateSeatInput') {
            $seats->expects($this->never())->method('insert');
            $seats->expects($this->never())->method('deleteByRange');
            $room->expects($this->never())->method('updateTotalSeats');
            $result = $service->validateSeatInput(array_combine(
                ['room_id', 'seat_row', 'seat_number', 'seat_type_id', 'is_active'], $input
            ));
            $expectedMessage = $expectedMessage ?? 'Dữ liệu ghế hợp lệ!';
        } elseif ($method === 'generateSeats') {
            [$roomId, $start, $end, $number, $typeId] = $input;
            $duplicate = $id === 'EP-10';
            $expectedSeats = [];
            if ($status === 'success' || $duplicate) {
                foreach (range($start, $end) as $row) {
                    foreach (range(1, $number) as $n) {
                        $expectedSeats[] = ['room_id' => $roomId, 'seat_row' => $row,
                            'seat_number' => $n, 'seat_type_id' => $typeId, 'is_active' => true];
                    }
                }
            }
            $inserted = [];
            $lookups = [];
            $seats->expects($this->exactly(count($expectedSeats)))->method('findByPosition')
                ->willReturnCallback(static function ($r, $row, $n) use (&$lookups, $duplicate) {
                    $lookups[] = [$r, $row, $n];
                    return $duplicate ? ['id' => $n] : null;
                });
            $seats->expects($this->exactly($status === 'success' ? count($expectedSeats) : 0))->method('insert')
                ->willReturnCallback(static function ($data) use (&$inserted) {
                    $inserted[] = $data;
                    return true;
                });
            $seats->expects($this->never())->method('deleteByRange');
            $sync = $status === 'success' || $duplicate;
            $seats->expects($this->exactly($sync ? 1 : 0))->method('countByRoomId')->with(1)->willReturn(count($expectedSeats));
            $room->expects($this->exactly($sync ? 1 : 0))->method('updateTotalSeats')->with(1, count($expectedSeats))->willReturn(true);
            $result = $service->generateSeats(...$input);
            $this->assertSame($status === 'success' ? $expectedSeats : [], $inserted);
            $this->assertSame(array_map(static fn($s) => [$s['room_id'], $s['seat_row'], $s['seat_number']], $expectedSeats), $lookups);
            $expectedMessage = $expectedMessage ?? 'Tạo thành công ' . count($expectedSeats) . ' ghế.';
        } else {
            [$roomId, $start, $end, $first, $last] = $input;
            // Full room plus a seat in a different room: assert exact range and preservation.
            $fixture = [];
            foreach (range('A', 'H') as $row) {
                foreach (range(1, 12) as $n) {
                    $fixture[] = [1, $row, $n];
                }
            }
            $inRange = static fn($s) => $s[0] === $roomId && $s[1] >= $start && $s[1] <= $end && $s[2] >= $first && $s[2] <= $last;
            if ($id === 'EP-12') {
                $fixture = array_values(array_filter($fixture, static fn($s) => !$inRange($s)));
            }
            $fixture[] = [2, 'D', 3];
            $before = $fixture;
            $matches = array_values(array_filter($before, $inRange));
            $remaining = array_values(array_filter($before, static fn($s) => !$inRange($s)));
            $seats->expects($this->exactly($status === 'success' || $id === 'EP-12' ? 1 : 0))
                ->method('countByRange')->with(...$input)->willReturn(count($matches));
            $seats->expects($this->exactly($status === 'success' ? 1 : 0))->method('deleteByRange')->with(...$input)
                ->willReturnCallback(static function () use (&$fixture, $remaining, $matches) {
                    $fixture = $remaining;
                    return count($matches);
                });
            $seats->expects($this->never())->method('insert');
            $total = count(array_filter($remaining, static fn($s) => $s[0] === 1));
            $seats->expects($this->exactly($status === 'success' ? 1 : 0))->method('countByRoomId')->with(1)->willReturn($total);
            $room->expects($this->exactly($status === 'success' ? 1 : 0))->method('updateTotalSeats')->with(1, $total)->willReturn(true);
            $result = $service->bulkDeleteSeats(...$input);
            $this->assertSame($status === 'success' ? $remaining : $before, $fixture);
            $expectedMessage = $expectedMessage ?? 'Xóa thành công ' . count($matches) . ' ghế.';
        }
        $this->assertSame(['status' => $status, 'message' => $expectedMessage], $result);
    }

    private static function errorMessage(string $method, string $id): ?string
    {
        $room = 'Phòng chiếu không hợp lệ!';
        $type = 'Loại ghế không hợp lệ!';
        $row = 'Khoảng hàng ghế phải từ A đến H và hợp lệ!';
        $number = 'Khoảng số ghế phải từ 1 đến 12 và hợp lệ!';
        $messages = [
            'validateSeatInput' => ['EP-02' => 'Số ghế phải từ 1 đến 12!', 'EP-03' => 'Số ghế phải từ 1 đến 12!',
                'EP-04' => 'Hàng ghế phải từ A đến H!', 'EP-05' => $room, 'EP-06' => $room, 'EP-07' => $type, 'EP-08' => $type],
            'generateSeats' => ['EP-02' => $room, 'EP-03' => $room, 'EP-04' => $type,
                'EP-05' => 'Số ghế mỗi hàng phải từ 1 đến 12!', 'EP-06' => 'Số ghế mỗi hàng phải từ 1 đến 12!',
                'EP-07' => 'Hàng ghế phải từ A đến H và hợp lệ!', 'EP-08' => 'Hàng ghế phải từ A đến H và hợp lệ!',
                'EP-09' => 'Hàng ghế phải từ A đến H và hợp lệ!', 'EP-10' => 'Không tạo ghế mới. Tất cả vị trí đã tồn tại.'],
            'bulkDeleteSeats' => ['EP-02' => $room, 'EP-03' => $room, 'EP-04' => $row, 'EP-05' => $row, 'EP-06' => $row,
                'EP-07' => $number, 'EP-08' => $number, 'EP-09' => $number, 'EP-10' => $number, 'EP-11' => $number,
                'EP-12' => 'Không có ghế nào trong khoảng đã chọn.'],
        ];
        return $messages[$method][$id] ?? null;
    }
}
