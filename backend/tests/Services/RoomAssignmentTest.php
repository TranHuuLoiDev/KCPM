<?php
namespace Tests\Services;

use App\Models\RoomModel;
use App\Models\TheatreModel;
use App\Services\RoomService;
use Tests\Support\AssignmentCases;
use Tests\Support\IsolatedServiceTestCase;

class RoomAssignmentTest extends IsolatedServiceTestCase
{
    public static function cases(): array
    {
        return AssignmentCases::read('Room', [
            'validateRoomInput' => ['LB' => 5, 'EP' => 8],
            'addRoom' => ['LB' => 5, 'EP' => 7],
            'updateRoom' => ['LB' => 5, 'EP' => 10],
        ]);
    }

    /** @dataProvider cases */
    public function testAssignment(string $method, string $id, array $row): void
    {
        $room = $this->createMock(RoomModel::class);
        $theatre = $this->createMock(TheatreModel::class);
        $service = $this->service(RoomService::class, ['model' => $room, 'theatreModel' => $theatre]);
        $data = array_combine(['name','theatre_id','total_seats','is_active'], AssignmentCases::inputs($row, ['name','theatre_id','total_seats','is_active']));
        $roomId = isset($row['id']) ? AssignmentCases::value($row['id']) : null;
        $status = AssignmentCases::status($row);
        $fixture = [10 => ['name'=>'Room Old','theatre_id'=>1,'total_seats'=>40,'is_active'=>true],
            11 => ['name'=>'Room Used','theatre_id'=>1,'total_seats'=>40,'is_active'=>true]];
        $before = $fixture;
        $theatre->method('findById')->willReturnCallback(static fn($id) => $id === 1 ? ['id'=>1] : null);
        $room->method('findById')->willReturnCallback(static fn($id) => $fixture[$id] ?? null);
        $room->expects($method === 'validateRoomInput' ? $this->never() : $this->any())->method('findByName')
            ->willReturnCallback(function ($name, $exclude = null) use ($fixture, $method, $roomId) {
                $this->assertSame($method === 'updateRoom' ? $roomId : null, $exclude);
                foreach ($fixture as $id => $record) {
                    if ($record['name'] === $name && $id !== $exclude) return ['id'=>$id];
                }
                return null;
            });
        $room->expects($this->exactly($method === 'addRoom' && $status === 'success' ? 1 : 0))->method('insert')->with($data)
            ->willReturnCallback(static function ($data) use (&$fixture) { $fixture[12]=$data; return 12; });
        $room->expects($this->exactly($method === 'updateRoom' && $status === 'success' ? 1 : 0))->method('update')->with($roomId, $data)
            ->willReturnCallback(static function ($id, $data) use (&$fixture) { $fixture[$id]=$data; return true; });
        $room->expects($this->never())->method('delete');
        $result = $method === 'updateRoom' ? $service->$method($roomId, $data) : $service->$method($data);
        $messages = ['validateRoomInput'=>'Dữ liệu phòng hợp lệ!', 'addRoom'=>'Thêm phòng chiếu thành công!', 'updateRoom'=>'Cập nhật phòng chiếu thành công!'];
        $this->assertSame(['status'=>$status,'message'=>$status === 'success' ? $messages[$method] : AssignmentCases::error($row)], $result);
        if ($status === 'success' && $method !== 'validateRoomInput') $before[$method === 'addRoom' ? 12 : $roomId] = $data;
        $this->assertSame($before, $fixture, 'Only the selected room may change');
    }
}
