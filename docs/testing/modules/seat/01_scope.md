# 01 – TEST SCOPE: MODULE SEAT

## 1. Mục tiêu

Xác định chính xác chức năng của module Seat được chọn để áp dụng Equivalence Partitioning, Boundary Value Analysis, Black-box, White-box và thiết kế test case có thể trace về source.

## 2. Chức năng được chọn

| Module | Function | Input chính | Business Rule chính | Black-box | White-box | Lý do chọn |
|---|---|---|---|---|---|---|
| Seat | `validateSeatInput($data)` | `seat_number` | `1 <= seat_number <= 12` | Yes | Yes | Có input số, min/max rõ, valid/invalid domain, PHPUnit, API validation endpoint và decision trong source |

## 3. Source liên quan

### Production source

`backend/app/Services/SeatService.php`

```php
public function validateSeatInput($data)
private function validateBase($data)
```

### API

```text
POST /seats/validate
```

Local endpoint:

```text
http://localhost/movie-ticket-booking/backend/api.php/seats/validate
```

### Unit test

`backend/tests/Services/SeatServiceTest.php`

### BVA automation

`tests/bva/bva-cases.js`

### Postman collection

`tests/postman/BVA_MovieBooking.postman_collection.json`

## 4. Input thuộc phạm vi

| Input | Vai trò | BVA | EP |
|---|---|---:|---:|
| `seat_number` | Input chính | Yes | Yes |
| `room_id` | Precondition + validation | No | Yes |
| `seat_row` | Precondition + validation | No | Yes |
| `seat_type_id` | Precondition + validation | No | Yes |
| `is_active` | Có trong request nhưng `validateBase()` không có rule validation riêng | No | No |

## 5. Black-box scope

Black-box kiểm tra từ API bên ngoài:

- Request body.
- `seat_number`.
- Response.
- `status = success/error`.

Ví dụ request hợp lệ:

```json
{
  "room_id": 1,
  "seat_row": "A",
  "seat_number": 6,
  "seat_type_id": 1,
  "is_active": true
}
```

Expected:

```json
{
  "status": "success"
}
```

## 6. White-box scope

Luồng chính:

```text
validateSeatInput()
    ↓
validateBase()
```

Decision cần phân tích:

- `room_id` invalid?
- `seat_row` invalid?
- `seat_number < 1 || seat_number > 12`?
- `seat_type_id` invalid?
- `$validation` có lỗi?

## 7. Ngoài phạm vi hiện tại

Không lấy các function sau làm trọng tâm EP/BVA:

- `addSeat()`
- `updateSeat()`
- `deleteSeat()`
- `bulkDeleteSeats()`
- `generateSeats()`
- `quickAddSeat()`
- `getBookedSeats()`
- `getSeatsByRoomId()`
- `getSeatsByIds()`

## 8. Kết luận phạm vi

```text
Module    : Seat
Function  : validateSeatInput()
BVA Input : seat_number
Range     : 1..12
API       : POST /seats/validate
Black-box : Yes
White-box : Yes
```
