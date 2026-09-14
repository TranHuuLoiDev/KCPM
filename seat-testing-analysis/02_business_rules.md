# 02 – BUSINESS RULES & VALIDATION CONDITIONS: SEAT

## 1. Luồng validation

`validateSeatInput($data)` chuẩn hóa `seat_row`, gọi `validateBase($data)`, nếu có lỗi thì trả lỗi; nếu không có lỗi thì trả success.

```text
Input
↓
Normalize seat_row
↓
validateBase()
↓
Có validation error?
├── Yes → return error
└── No  → return success
```

## 2. Business rules từ source

| ID | Input | Type | Validation rule | Expected khi vi phạm |
|---|---|---|---|---|
| BR-S01 | `room_id` | integer | Phải `> 0` và room phải tồn tại | `error` |
| BR-S02 | `seat_row` | string | Sau trim + uppercase phải khớp `^[A-H]$` | `error` |
| BR-S03 | `seat_number` | integer | Phải nằm trong `1..12` | `error` |
| BR-S04 | `seat_type_id` | integer | Phải `> 0` và seat type phải tồn tại | `error` |
| BR-S05 | toàn bộ dữ liệu | array | Không có validation error | `success` |

## 3. Validation message

| Trường hợp | Status | Message |
|---|---|---|
| Room không hợp lệ | `error` | `Phòng chiếu không hợp lệ!` |
| Hàng ghế không hợp lệ | `error` | `Hàng ghế phải từ A đến H!` |
| Số ghế ngoài `1..12` | `error` | `Số ghế phải từ 1 đến 12!` |
| Loại ghế không hợp lệ | `error` | `Loại ghế không hợp lệ!` |
| Tất cả hợp lệ | `success` | `Dữ liệu ghế hợp lệ!` |

## 4. Rule dùng cho BVA

```text
1 <= seat_number <= 12
```

Suy ra:

```text
MIN = 1
MAX = 12
```

## 5. Preconditions khi test seat_number

Khi thay đổi `seat_number`, các input khác phải giữ hợp lệ:

```text
room_id      = 1      → phải tồn tại trong DB
seat_row     = A      → hợp lệ
seat_type_id = 1      → phải tồn tại trong DB
is_active    = true
```

Baseline request:

```json
{
  "room_id": 1,
  "seat_row": "A",
  "seat_number": "<GIÁ TRỊ ĐANG TEST>",
  "seat_type_id": 1,
  "is_active": true
}
```

## 6. Database dependency

Hai rule có dependency DB:

```text
room_id > 0 AND room tồn tại
seat_type_id > 0 AND seat type tồn tại
```

Phải phân biệt:

```text
ID <= 0
```

và:

```text
ID > 0 nhưng record không tồn tại
```

## 7. Validation conditions white-box

### Room

```text
room_id <= 0 OR room không tồn tại
```

### Seat row

```text
seat_row không phải đúng 1 ký tự A..H
```

### Seat number

```text
seat_number < 1 OR seat_number > 12
```

### Seat type

```text
seat_type_id <= 0 OR seat type không tồn tại
```

## 8. Kết luận

```text
seat_number
Valid   : 1..12
Invalid : <1 hoặc >12
```
