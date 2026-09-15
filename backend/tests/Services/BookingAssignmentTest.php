<?php
namespace Tests\Services;

use App\Models\BookingModel;
use App\Models\ShowtimeModel;
use App\Models\SeatModel;
use App\Models\TicketModel;
use App\Services\BookingService;
use Tests\Support\AssignmentCases;
use Tests\Support\IsolatedServiceTestCase;

class BookingAssignmentTest extends IsolatedServiceTestCase
{
    public static function cases(): array
    {
        return AssignmentCases::read('Booking', [
            'processBooking'=>['TB'=>3,'EP'=>17],
            'cancelBooking'=>['TB'=>3,'EP'=>9],
            'updateAdminBookingStatus'=>['EP'=>10],
            'normalizeAdminFilters'=>['EP'=>10],
        ]);
    }

    public static function faults(): array
    {
        $report = file_get_contents(dirname(__DIR__,3) . '/docs/testing/modules/booking/Booking_BaoCao_Form_Assignment.md');
        preg_match_all('/^\| (WB-\d{2})\s*\|/m', $report, $ids);
        if ($ids[1] !== ['WB-01','WB-02','WB-03','WB-04','WB-05','WB-06']) throw new \RuntimeException('Unexpected WB cases');
        $rows = [];
        foreach ($ids[1] as $id) $rows[$id] = [$id];
        return $rows;
    }

    /** @dataProvider faults */
    public function testFault(string $id): void
    {
        $method = $id <= 'WB-03' ? 'processBooking' : ($id === 'WB-04' ? 'cancelBooking' : 'updateAdminBookingStatus');
        $row = ['userId'=>'10','showtimeId'=>'1','seatIds'=>'[5,6]','paymentMethod'=>'momo','bookingId'=>'100','status mới'=>'paid'];
        $this->execute($method, $id, $row, $id);
    }

    /** @dataProvider cases */
    public function testAssignment(string $method, string $id, array $row): void
    {
        $this->execute($method, $id, $row);
    }

    private function execute(string $method, string $id, array $row, ?string $fault = null): void
    {
        $booking=$this->createMock(BookingModel::class);
        $showtime=$this->createMock(ShowtimeModel::class);
        $seat=$this->createMock(SeatModel::class);
        $ticket=$this->createMock(TicketModel::class);
        $now=new \DateTimeImmutable('2026-09-15 12:00:00');
        $service=$this->service(BookingService::class, ['bookingModel'=>$booking,'showtimeModel'=>$showtime,
            'seatModel'=>$seat,'ticketModel'=>$ticket,'clock'=>static fn()=>$now]);
        if ($method === 'normalizeAdminFilters') {
            $input=$id === 'EP-09' ? [] : array_combine(['status','from_date','to_date','search'], AssignmentCases::inputs($row,['status','from_date','to_date','search']));
            $expected=['status'=>'paid','from_date'=>'2026-08-01','to_date'=>'2026-08-31','search'=>'ABC123'];
            if ($id==='EP-02') $expected['status']='';
            if ($id==='EP-03') $expected['from_date']='';
            if ($id==='EP-04') $expected['to_date']='';
            if ($id==='EP-05') $expected['status']='pending';
            if ($id==='EP-06') $expected['status']='canceled';
            if ($id==='EP-07') { $expected['from_date']='2024-02-29'; $expected['to_date']='2024-03-01'; }
            if ($id==='EP-08') { $expected['from_date']='2026-08-31'; $expected['to_date']='2026-08-01'; }
            if ($id==='EP-09') $expected=array_fill_keys(array_keys($expected),'');
            $booking->expects($this->never())->method('beginTransaction');
            $this->assertSame($expected,$service->normalizeAdminFilters($input));
            return;
        }
        $status=$fault ? 'error' : AssignmentCases::status($row);
        $events=[];
        $transaction=$status==='success' || ($fault && $fault!=='WB-03');
        foreach (['beginTransaction'=>'begin','commit'=>'commit','rollback'=>'rollback'] as $name=>$event) {
            $times=$name==='beginTransaction' ? (int)$transaction : ($name==='commit' ? (int)($status==='success') : (int)($transaction && $status==='error'));
            $booking->expects($this->exactly($times))->method($name)->willReturnCallback(static function () use (&$events,$event) { $events[]=$event; });
        }
        $booking->method('getError')->willReturn('fixture error');
        // Any unrelated mutation is forbidden, including on guard failures.
        if($method!=='processBooking') { $booking->expects($this->never())->method('createBooking'); $ticket->expects($this->never())->method('createMany'); }
        if($method!=='cancelBooking') $booking->expects($this->never())->method('cancelBooking');
        if($method!=='updateAdminBookingStatus') {
            $booking->expects($this->never())->method('updateBookingStatus');
            $booking->expects($this->never())->method('updateTicketsStatusByBooking');
        }
        $booking->expects($this->never())->method('deleteBooking');
        $when=$now->modify('+1 day');
        if(str_starts_with($id,'TB')) $when=$now->modify(['TB-01'=>'-1 second','TB-02'=>'+0 seconds','TB-03'=>'+1 second'][$id]);
        if(($method==='processBooking' && $id==='EP-08') || ($method==='cancelBooking' && $id==='EP-07')) $when=$now->modify('-1 day');
        $schedule=['show_date'=>$when->format('Y-m-d'),'start_time'=>$when->format('H:i:s'),'status'=>'active','room_id'=>1,'base_price'=>100000];
        if($method==='processBooking') {
            $args=AssignmentCases::inputs($row,['userId','showtimeId','seatIds','paymentMethod']);
            if($id==='EP-07') $schedule['status']='inactive';
            $showtime->method('getDetailById')->willReturnCallback(static fn($id)=>$id===1 ? $schedule : null);
            $selected=[['id'=>5,'room_id'=>$id==='EP-10'?2:1,'is_active'=>$id==='EP-11'?0:1,'seat_type_price'=>20000],
                ['id'=>6,'room_id'=>1,'is_active'=>1,'seat_type_price'=>30000]];
            $normalized=is_array($args[2]) ? array_values(array_unique(array_map('intval',$args[2]))) : [];
            $selected=array_values(array_filter($selected,static fn($s)=>in_array($s['id'],$normalized,true)));
            if($fault==='WB-03') $selected=[['id'=>7],['id'=>8]];
            $seat->method('getByIds')->with($normalized)->willReturn($selected);
            $ticket->method('isSeatBooked')->willReturn($id==='EP-12');
            $prices=[];
            foreach($normalized as $sid) $prices[]=['seat_id'=>$sid,'price'=>$sid===5?120000.0:130000.0];
            $payment=$id==='EP-13'?'cash':$args[3];
            $booking->expects($this->exactly((int)$transaction))->method('createBooking')->with($args[0],array_sum(array_column($prices,'price')),$payment)
                ->willReturnCallback(static function () use (&$events,$fault) { $events[]='createBooking'; return $fault==='WB-01'?false:1001; });
            $ticket->expects($this->exactly((int)($transaction && $fault!=='WB-01')))->method('createMany')->with(1001,$args[1],$prices)
                ->willReturnCallback(static function () use (&$events,$fault) { $events[]='createMany'; return $fault!=='WB-02'; });
            $result=$service->processBooking(...$args);
            $message=$status==='success'?'Đặt vé thành công!':($fault ? ($fault==='WB-03'?'Ghế không hợp lệ.':'Có lỗi xảy ra khi đặt vé.') : AssignmentCases::error($row));
            if(!$fault && $status==='error' && str_starts_with($id,'TB')) $message='Suất chiếu này đã bắt đầu hoặc đã kết thúc.';
            if($id==='EP-05') $message='Vui lòng chọn ít nhất 1 ghế.';
            $expected=['status'=>$status,'message'=>$message];
            if($status==='success') $expected['booking_id']=1001;
            if($id==='EP-02') $expected['page']='login.php';
            $sequence=$transaction ? ['begin','createBooking'] : [];
            if($transaction && $fault!=='WB-01') $sequence[]='createMany';
        } elseif($method==='cancelBooking') {
            $args=AssignmentCases::inputs($row,['userId','bookingId']);
            $booking->method('getByIdAndUser')->willReturnCallback(static fn($bid,$uid)=>$bid===100 && $uid===10 ? ['id'=>100,'status'=>$id==='EP-06'?'canceled':'paid'] : null);
            if($id==='EP-09') $schedule=['show_date'=>'invalid','start_time'=>'invalid'];
            $booking->method('getPrimaryShowtimeByBookingId')->with(100)->willReturn($id==='EP-08'?null:$schedule);
            $booking->expects($this->exactly((int)$transaction))->method('cancelBooking')->with($args[1],$args[0])
                ->willReturnCallback(static function () use (&$events,$fault) { $events[]='cancel'; return $fault!=='WB-04'; });
            $result=$service->cancelBooking(...$args);
            $message=$status==='success'?'Huy ve thanh cong.':($fault?'Loi khi huy booking: fixture error':AssignmentCases::error($row));
            if(!$fault && $status==='error' && str_starts_with($id,'TB')) $message='Khong the huy ve khi suat chieu da bat dau.';
            if($id==='EP-05') $message='Khong tim thay booking can huy.';
            $expected=['status'=>$status,'message'=>$message];
            $sequence=$transaction?['begin','cancel']:[];
        } else {
            $args=AssignmentCases::inputs($row,['bookingId','status mới']);
            $old=in_array($id,['EP-07','EP-08','EP-09'],true)?'canceled':(in_array($id,['EP-01','EP-10'],true)?'pending':'paid');
            $booking->method('getAdminBookingById')->willReturnCallback(static fn($bid)=>$bid===100 ? ['id'=>100,'status'=>$old] : null);
            $booking->expects($this->exactly(in_array($id,['EP-07','EP-08'],true)?1:0))->method('hasSeatConflictWhenRestoring')->with(100)->willReturn($id==='EP-07');
            $new=trim($args[1]);
            $booking->expects($this->exactly((int)$transaction))->method('updateBookingStatus')->with($args[0],$new)
                ->willReturnCallback(static function () use (&$events,$fault) { $events[]='updateBooking'; return $fault!=='WB-05'; });
            $booking->expects($this->exactly((int)($transaction && $fault!=='WB-05')))->method('updateTicketsStatusByBooking')->with($args[0],$new==='canceled'?'canceled':'booked')
                ->willReturnCallback(static function () use (&$events,$fault) { $events[]='updateTickets'; return $fault!=='WB-06'; });
            $result=$service->updateAdminBookingStatus(...$args);
            $message=$status==='success'?'Cập nhật trạng thái booking thành công.':($fault ? ($fault==='WB-05'?'Lỗi khi cập nhật trạng thái booking: fixture error':'Lỗi khi cập nhật trạng thái vé: fixture error') : AssignmentCases::error($row));
            $expected=['status'=>$status,'message'=>$message];
            $sequence=$transaction?['begin','updateBooking']:[];
            if($transaction && $fault!=='WB-05') $sequence[]='updateTickets';
        }
        if($transaction) $sequence[]=$status==='success'?'commit':'rollback';
        $this->assertSame($expected,$result);
        $this->assertSame($sequence,$events,'Transaction calls must occur in order');
    }
}
