# Booking Flow

Input page:

```txt
booking.php?showtime_id={showtime_id}
```

## Render Booking Page

```txt
ShowtimeController::getShowtimeDetail($showtimeId)
-> ShowtimeService::getShowtimeDetail($showtimeId)
-> ShowtimeModel::getDetailById($showtimeId)
-> lấy đầy đủ thông tin suất chiếu gồm:
   showtime_id, movie_id, room_id, show_date, start_time, end_time, base_price, status,
   movie_title, movie_poster, movie_duration, movie_age_restriction,
   room_name, theatre_id, theatre_name, theatre_address, theatre_city
```

```txt
SeatController::getSeatsByRoomId($roomId)
-> SeatService::getSeatsByRoomId($roomId)
-> SeatModel::getByRoomIdWithType($roomId)
-> lấy danh sách ghế của phòng gồm:
   seat_id, room_id, seat_row, seat_number, seat_type_id, is_active,
   seat_type_name, seat_type_price
```

```txt
TicketController::getBookedSeatIdsByShowtimeId($showtimeId)
-> TicketService::getBookedSeatIdsByShowtimeId($showtimeId)
-> TicketModel::getBookedSeatIdsByShowtimeId($showtimeId)
-> lấy danh sách seat_id đã được đặt trong suất chiếu
-> dùng để disable ghế trên booking.php
```

## Submit Booking

```txt
booking.php POST action=book_ticket
-> BookingController::handleRequest()
-> BookingService::processBooking($userId, $showtimeId, $seatIds, $paymentMethod)
```

```txt
BookingService::processBooking(...)
-> ShowtimeModel::getDetailById($showtimeId)
-> kiểm tra suất chiếu tồn tại và active
```

```txt
BookingService::processBooking(...)
-> SeatModel::getByRoomIdWithType($roomId)
-> kiểm tra các ghế user chọn thuộc đúng phòng chiếu
-> tính giá từng ghế = base_price + seat_type_price
```

```txt
BookingService::processBooking(...)
-> TicketModel::getBookedSeatIdsByShowtimeId($showtimeId)
-> kiểm tra ghế user chọn chưa bị đặt
```

```txt
BookingService::processBooking(...)
-> BookingModel::createBooking($data)
-> tạo 1 booking tổng gồm:
   user_id, total_price, payment_method, status
```

```txt
BookingService::processBooking(...)
-> TicketModel::createMany($bookingId, $showtimeId, $seatPrices)
-> tạo nhiều ticket cho booking vừa tạo
-> mỗi ticket ứng với 1 ghế
```

## Relationship

```txt
bookings 1 -> n tickets

1 booking = 1 lần đặt vé của user
n tickets = nhiều ghế trong lần đặt đó
```
