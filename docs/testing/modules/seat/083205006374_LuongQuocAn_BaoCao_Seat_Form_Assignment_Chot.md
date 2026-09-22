# BÁO CÁO KIỂM THỬ MODULE SEAT

**Họ và tên:** Lương Quốc An  
**MSSV:** 083205006374  
**Project:** Movie Ticket Booking  
**Module thực hiện:** Seat  
**Service:** `App\Services\SeatService`  
**File source:** `backend/app/Services/SeatService.php`  

---

# 1. XÁC ĐỊNH MODULE VÀ PHẠM VI THỰC HIỆN

Module được chọn để thực hiện kiểm thử là:

```text
Seat
```

Module Seat chịu trách nhiệm quản lý dữ liệu ghế trong hệ thống đặt vé xem phim, bao gồm:

- Thêm ghế.
- Cập nhật ghế.
- Xóa ghế.
- Tạo ghế hàng loạt.
- Xóa ghế hàng loạt.
- Lấy danh sách ghế.
- Lấy ghế theo phòng.
- Kiểm tra dữ liệu ghế trước khi xử lý.

Service chính:

```text
backend/app/Services/SeatService.php
```

Class:

```php
App\Services\SeatService
```

Trong báo cáo này, quy trình trình bày được thống nhất theo đúng hướng của file Assignment:

```text
Xác định module
→ Xác định các method trong service
→ Chọn method phù hợp để kiểm thử
→ Xác định input và điều kiện hợp lệ
→ Phân hoạch lớp tương đương
→ Phân tích giá trị biên
→ Thiết kế bảng biên và phân biệt Standard BVA với biên quan hệ
→ Thiết kế một bảng EP V/X gộp toàn bộ input
→ Mapping với automation và kết quả thực tế nếu đã có
```

---

# 2. XÁC ĐỊNH CÁC METHOD TRONG SEATSERVICE

Dựa trên source hiện tại, `SeatService` có các method sau:

| STT | Method | Visibility | Chức năng |
|---:|---|---|---|
| 1 | `__construct()` | public | Khởi tạo model |
| 2 | `addSeat($data)` | public | Thêm một ghế |
| 3 | `validateUpdateSeat($id, &$data)` | private | Validate dữ liệu trước update |
| 4 | `updateSeat($id, $data)` | public | Cập nhật ghế |
| 5 | `deleteSeat($id)` | public | Xóa ghế |
| 6 | `bulkDeleteSeats(...)` | public | Xóa nhiều ghế theo khoảng |
| 7 | `generateSeats(...)` | public | Tạo ghế hàng loạt |
| 8 | `getAllSeats($roomId = null)` | public | Lấy danh sách ghế |
| 9 | `getAllRooms()` | public | Lấy danh sách phòng |
| 10 | `getAllSeatTypes()` | public | Lấy danh sách loại ghế |
| 11 | `quickAddSeat($roomId, $seatRow)` | public | Thêm nhanh ghế |
| 12 | `getDisplayRows($roomId, $showRow = null)` | public | Lấy danh sách hàng ghế hiển thị |
| 13 | `getNextRowLetter(array $displayRows)` | public | Lấy hàng ghế tiếp theo |
| 14 | `validateSeatInput($data)` | public | Validate dữ liệu Seat |
| 15 | `validateBase($data)` | private | Validation lõi |
| 16 | `validate($data, $excludeId = null)` | private | Validation + kiểm tra trùng vị trí |
| 17 | `syncRoomTotalSeats($roomId)` | private | Đồng bộ tổng số ghế của phòng |
| 18 | `getBookedSeats($showtime_id)` | public | Lấy ghế đã đặt |
| 19 | `getSeatsByRoomId($roomId)` | public | Lấy ghế theo phòng |
| 20 | `getSeatsByIds($seatIds)` | public | Lấy ghế theo danh sách ID |

---

# 3. CHỌN METHOD ĐỂ THỰC HIỆN KIỂM THỬ

Ba method được chọn để phân tích theo Assignment:

```text
1. validateSeatInput($data)
2. generateSeats($roomId, $startRow, $endRow, $seatsPerRow, $seatTypeId)
3. bulkDeleteSeats($roomId, $startRow, $endRow, $startNumber, $endNumber)
```

Lý do chọn:

| Method | Lý do |
|---|---|
| `validateSeatInput()` | Có nhiều điều kiện validation rõ ràng; `seat_number` và `seat_row` có miền đóng phù hợp với EP/BVA |
| `generateSeats()` | Có `seatsPerRow` trong `1..12`, `startRow` và `endRow` trong `A..H` |
| `bulkDeleteSeats()` | Có 4 biến có miền đóng: `startRow`, `endRow`, `startNumber`, `endNumber` |

Các input kiểu ID như `roomId`, `room_id`, `seatTypeId`, `seat_type_id` chỉ có điều kiện `>0` và/hoặc phải tồn tại trong database, không có upper bound được source quy định. Vì vậy các input này được kiểm thử bằng **Equivalence Partitioning**, không tự tạo `max` để ép thành Standard BVA.

> **Quy ước trình bày:** Tag `V`, `X`, `B` được đánh lại từ đầu trong từng method để bảng ngắn gọn và giống form Assignment.

---

# 4. METHOD 1 — `validateSeatInput($data)`

---

## 4.1. Mô tả chức năng

Method:

```php
validateSeatInput($data)
```

Mục đích:

```text
Kiểm tra dữ liệu ghế trước khi thực hiện các xử lý tiếp theo.
```

Các input:

| Input | Ý nghĩa | Điều kiện hợp lệ |
|---|---|---|
| `room_id` | ID phòng chiếu | `>0` và phòng phải tồn tại |
| `seat_row` | Hàng ghế | Một ký tự từ `A` đến `H` |
| `seat_number` | Số ghế | Từ `1` đến `12` |
| `seat_type_id` | ID loại ghế | `>0` và loại ghế phải tồn tại |
| `is_active` | Trạng thái | Giữ `true` trong scope kiểm thử |

Kết quả:

```text
status = success  → dữ liệu ghế hợp lệ
status = error    → có ít nhất một validation không hợp lệ
```

---

## 4.2. Bước 1 — Xác định lớp tương đương

| Biến đầu vào | Lớp hợp lệ | Tag | Lớp không hợp lệ | Tag |
|---|---|---|---|---|
| `seat_number` | `1..12` | V1 | `<1` | X1 |
| `seat_number` | `1..12` | V1 | `>12` | X2 |
| `seat_row` | `A..H` | V2 | Ngoài `A..H` | X3 |
| `room_id` | `>0` và tồn tại | V3 | `<=0` | X4 |
| `room_id` | `>0` và tồn tại | V3 | `>0` nhưng không tồn tại | X5 |
| `seat_type_id` | `>0` và tồn tại | V4 | `<=0` | X6 |
| `seat_type_id` | `>0` và tồn tại | V4 | `>0` nhưng không tồn tại | X7 |

### Bảng theo form Assignment

| Conditions | Valid Partitions | Tag | Invalid Partitions | Tag | Valid Boundaries | Tag |
|---|---|---|---|---|---|---|
| `seat_number` | `1..12` | V1 | `<1` | X1 | `1`, `12` | B1, B5 |
| `seat_row` | `A..H` | V2 | Ngoài `A..H` | X3 | `A`, `H` | B6, B10 |
| `room_id` | `>0` và tồn tại | V3 | `<=0` | X4 | N/A | N/A |
| `room_id` | `>0` và tồn tại | V3 | `>0` nhưng không tồn tại | X5 | N/A | N/A |
| `seat_type_id` | `>0` và tồn tại | V4 | `<=0` | X6 | N/A | N/A |
| `seat_type_id` | `>0` và tồn tại | V4 | `>0` nhưng không tồn tại | X7 | N/A | N/A |

---

## 4.3. Bước 2 — Phân tích giá trị biên

Các input có miền đóng và có thứ tự:

```text
seat_number ∈ [1,12]
seat_row    ∈ [A,H]
```

### Giá trị biên của `seat_number`

| Ký hiệu | Giá trị | Tag |
|---|---:|---|
| min | 1 | B1 |
| min+ | 2 | B2 |
| nominal | 6 | B3 |
| max- | 11 | B4 |
| max | 12 | B5 |

### Giá trị biên của `seat_row`

Do `seat_row` là miền rời rạc có thứ tự từ `A` đến `H`, chọn:

| Ký hiệu | Giá trị | Tag |
|---|:---:|---|
| min | A | B6 |
| min+ | B | B7 |
| nominal | D | B8 |
| max- | G | B9 |
| max | H | B10 |

---

## 4.4. Bước 3 — Thiết kế Test Case

### Phần A — Test case cho Standard BVA

Nguyên tắc: mỗi lần chỉ thay đổi **một biến đang kiểm thử**, các input còn lại giữ ở giá trị hợp lệ.

Giá trị nominal / hợp lệ dùng để giữ cố định:

```text
room_id      = 1
seat_type_id = 1
is_active    = true

seat_number nominal = 6
seat_row nominal    = D
```

| STT | Test case | room_id | seat_row | seat_number | seat_type_id | is_active | Kết quả mong đợi | Tag |
|---:|---|---:|:---:|---:|---:|:---:|---|---|
| BVA-01 | `seat_number` – min | 1 | D | 1 | 1 | true | **Hợp lệ** | B1 |
| BVA-02 | `seat_number` – min+ | 1 | D | 2 | 1 | true | **Hợp lệ** | B2 |
| BVA-03 | `seat_number` – nominal | 1 | D | 6 | 1 | true | **Hợp lệ** | B3 |
| BVA-04 | `seat_number` – max- | 1 | D | 11 | 1 | true | **Hợp lệ** | B4 |
| BVA-05 | `seat_number` – max | 1 | D | 12 | 1 | true | **Hợp lệ** | B5 |
| BVA-06 | `seat_row` – min | 1 | A | 6 | 1 | true | **Hợp lệ** | B6 |
| BVA-07 | `seat_row` – min+ | 1 | B | 6 | 1 | true | **Hợp lệ** | B7 |
| BVA-08 | `seat_row` – nominal | 1 | D | 6 | 1 | true | **Hợp lệ** | B8 |
| BVA-09 | `seat_row` – max- | 1 | G | 6 | 1 | true | **Hợp lệ** | B9 |
| BVA-10 | `seat_row` – max | 1 | H | 6 | 1 | true | **Hợp lệ** | B10 |

Theo công thức Chương 4, `n = 2` nên Standard BVA có `4n + 1 = 9` input khác nhau. `BVA-03` và `BVA-08` cùng là vector nominal `(seat_row=D, seat_number=6)`; hai dòng được giữ để truy vết hai tag `B3` và `B8`, nhưng chỉ được tính là **một** test case Standard BVA duy nhất.

---

### Phần B — Test case phủ Equivalence Partitioning V/X

Ở mỗi test, chỉ cố tình làm sai một lớp không hợp lệ; các input khác giữ hợp lệ.

| STT | Test case | room_id | seat_row | seat_number | seat_type_id | is_active | Kết quả mong đợi | Tag |
|---:|---|---:|:---:|---:|---:|:---:|---|---|
| EP-01 | Tất cả giá trị hợp lệ | 1 | D | 6 | 1 | true | **Hợp lệ** | V1, V2, V3, V4 |
| EP-02 | `seat_number` dưới miền | 1 | D | 0 | 1 | true | **Không hợp lệ** – Số ghế nhỏ hơn 1 | X1 |
| EP-03 | `seat_number` trên miền | 1 | D | 13 | 1 | true | **Không hợp lệ** – Số ghế lớn hơn 12 | X2 |
| EP-04 | `seat_row` ngoài miền | 1 | I | 6 | 1 | true | **Không hợp lệ** – Hàng ghế phải từ A đến H | X3 |
| EP-05 | `room_id <= 0` | 0 | D | 6 | 1 | true | **Không hợp lệ** – Phòng chiếu không hợp lệ | X4 |
| EP-06 | `room_id` không tồn tại | 999999 | D | 6 | 1 | true | **Không hợp lệ** – Phòng chiếu không hợp lệ | X5 |
| EP-07 | `seat_type_id <= 0` | 1 | D | 6 | 0 | true | **Không hợp lệ** – Loại ghế không hợp lệ | X6 |
| EP-08 | `seat_type_id` không tồn tại | 1 | D | 6 | 999999 | true | **Không hợp lệ** – Loại ghế không hợp lệ | X7 |

### Tổng hợp method `validateSeatInput`

| Nhóm | Số TC | Phạm vi phủ |
|---|---:|---|
| **Standard BVA** | 9 input duy nhất | **B1–B10; BVA-03/BVA-08 trùng vector nominal** |
| **Dòng truy vết dư** | 1 | Giữ riêng để mapping tag/automation |
| **Equivalence Partitioning** | 8 | **V1–V4, X1–X7** |
| **Tổng theo hai bảng** | **18 TC** | **Phủ toàn bộ V, X và B trong scope** |

---

## 4.5. Trạng thái automation thực tế

Đã chạy PHPUnit: **18/18 (10 dataset biên, tương ứng 9 input Standard BVA duy nhất, + 8 EP) PASS** trong "SeatAssignmentTest.php".

Mỗi dòng bảng được nạp trực tiếp thành một dataset có tên method/BVA-xx hoặc method/EP-xx. Fixture model trong bộ nhớ được tạo mới cho từng case; room/type 1 tồn tại, 999999 không tồn tại. Test kiểm tra status, thông báo chính xác và các lời gọi tạo/xóa/đồng bộ tổng ghế. Đây là unit test service, chưa chứng minh SQL hoặc HTTP integration.

Evidence: [assignment-junit.xml](assignment-junit.xml). Chi tiết: [07_phpunit_mapping.md](07_phpunit_mapping.md).

---

# 5. METHOD 2 — `generateSeats(...)`

---

## 5.1. Mô tả chức năng

Method:

```php
generateSeats(
    $roomId,
    $startRow,
    $endRow,
    $seatsPerRow,
    $seatTypeId
)
```

Mục đích:

```text
Tạo hàng loạt ghế từ startRow đến endRow,
mỗi hàng có seatsPerRow ghế.
```

Các input:

| Input | Điều kiện hợp lệ |
|---|---|
| `roomId` | `>0` và phòng tồn tại |
| `startRow` | `A..H` |
| `endRow` | `A..H` |
| Quan hệ row | `startRow <= endRow` |
| `seatsPerRow` | `1..12` |
| `seatTypeId` | Loại ghế tồn tại |
| Trạng thái vị trí | Còn ít nhất một vị trí chưa tồn tại nếu muốn tạo thành công |

---

## 5.2. Bước 1 — Xác định lớp tương đương

| Biến / điều kiện | Lớp hợp lệ | Tag | Lớp không hợp lệ | Tag |
|---|---|---|---|---|
| `roomId` | `>0` và tồn tại | V1 | `<=0` | X1 |
| `roomId` | `>0` và tồn tại | V1 | `>0` nhưng không tồn tại | X2 |
| `seatTypeId` | tồn tại | V2 | không tồn tại | X3 |
| `seatsPerRow` | `1..12` | V3 | `<1` | X4 |
| `seatsPerRow` | `1..12` | V3 | `>12` | X5 |
| `startRow` | `A..H` | V4 | ngoài `A..H` | X6 |
| `endRow` | `A..H` | V5 | ngoài `A..H` | X7 |
| Quan hệ row | `startRow <= endRow` | V6 | `startRow > endRow` | X8 |
| Trạng thái vị trí | còn ít nhất 1 vị trí mới | V7 | tất cả vị trí đã tồn tại | X9 |

### Bảng theo form Assignment

| Conditions | Valid Partitions | Tag | Invalid Partitions | Tag | Valid Boundaries | Tag |
|---|---|---|---|---|---|---|
| `seatsPerRow` | `1..12` | V3 | `<1`, `>12` | X4, X5 | `1`, `12` | B1, B5 |
| `startRow` | `A..H` | V4 | ngoài `A..H` | X6 | `A`, `H` | B6, B10 |
| `endRow` | `A..H` | V5 | ngoài `A..H` | X7 | `A`, `H` | B11, B15 |
| `startRow <= endRow` | đúng | V6 | sai | X8 | N/A | N/A |
| `roomId` | `>0` và tồn tại | V1 | không hợp lệ / không tồn tại | X1, X2 | N/A | N/A |
| `seatTypeId` | tồn tại | V2 | không tồn tại | X3 | N/A | N/A |

---

## 5.3. Bước 2 — Phân tích giá trị biên

Phân loại kỹ thuật theo Chương 4:

```text
seatsPerRow ∈ [1,12]  → Standard BVA một biến
startRow, endRow      → biên quan hệ vì còn ràng buộc startRow <= endRow
```

### `seatsPerRow`

| Ký hiệu | Giá trị | Tag |
|---|---:|---|
| min | 1 | B1 |
| min+ | 2 | B2 |
| nominal | 6 | B3 |
| max- | 11 | B4 |
| max | 12 | B5 |

### `startRow`

| Ký hiệu | Giá trị | Tag |
|---|:---:|---|
| min | A | B6 |
| min+ | B | B7 |
| nominal | D | B8 |
| max- | G | B9 |
| max | H | B10 |

### `endRow`

| Ký hiệu | Giá trị | Tag |
|---|:---:|---|
| min | A | B11 |
| min+ | B | B12 |
| nominal | D | B13 |
| max- | G | B14 |
| max | H | B15 |

---

## 5.4. Bước 3 — Thiết kế Test Case

### Phần A — Standard BVA cho `seatsPerRow` và bộ biên quan hệ cho hàng

Nguyên tắc: mỗi lần chỉ thay đổi **một biến đang kiểm thử**, các input khác giữ hợp lệ.

Precondition:

```text
roomId = 1 tồn tại
seatTypeId = 1 tồn tại
seatsPerRow nominal = 6

Khi test seatsPerRow:
startRow = D
endRow   = D

Khi test startRow:
endRow = H
để luôn giữ startRow <= endRow

Khi test endRow:
startRow = A
để luôn giữ startRow <= endRow

Mỗi test sử dụng fixture độc lập hoặc reset dữ liệu,
bảo đảm còn vị trí ghế chưa tồn tại trong range đang test.
```

| STT | Test case | roomId | startRow | endRow | seatsPerRow | seatTypeId | Kết quả mong đợi | Tag |
|---:|---|---:|:---:|:---:|---:|---:|---|---|
| BVA-01 | `seatsPerRow` – min | 1 | D | D | 1 | 1 | **Hợp lệ** – tiếp tục tạo ghế | B1 |
| BVA-02 | `seatsPerRow` – min+ | 1 | D | D | 2 | 1 | **Hợp lệ** – tiếp tục tạo ghế | B2 |
| BVA-03 | `seatsPerRow` – nominal | 1 | D | D | 6 | 1 | **Hợp lệ** – tiếp tục tạo ghế | B3 |
| BVA-04 | `seatsPerRow` – max- | 1 | D | D | 11 | 1 | **Hợp lệ** – tiếp tục tạo ghế | B4 |
| BVA-05 | `seatsPerRow` – max | 1 | D | D | 12 | 1 | **Hợp lệ** – tiếp tục tạo ghế | B5 |
| BVA-06 | `startRow` – min | 1 | A | H | 6 | 1 | **Hợp lệ** – tiếp tục tạo ghế | B6 |
| BVA-07 | `startRow` – min+ | 1 | B | H | 6 | 1 | **Hợp lệ** – tiếp tục tạo ghế | B7 |
| BVA-08 | `startRow` – nominal | 1 | D | H | 6 | 1 | **Hợp lệ** – tiếp tục tạo ghế | B8 |
| BVA-09 | `startRow` – max- | 1 | G | H | 6 | 1 | **Hợp lệ** – tiếp tục tạo ghế | B9 |
| BVA-10 | `startRow` – max | 1 | H | H | 6 | 1 | **Hợp lệ** – tiếp tục tạo ghế | B10 |
| BVA-11 | `endRow` – min | 1 | A | A | 6 | 1 | **Hợp lệ** – tiếp tục tạo ghế | B11 |
| BVA-12 | `endRow` – min+ | 1 | A | B | 6 | 1 | **Hợp lệ** – tiếp tục tạo ghế | B12 |
| BVA-13 | `endRow` – nominal | 1 | A | D | 6 | 1 | **Hợp lệ** – tiếp tục tạo ghế | B13 |
| BVA-14 | `endRow` – max- | 1 | A | G | 6 | 1 | **Hợp lệ** – tiếp tục tạo ghế | B14 |
| BVA-15 | `endRow` – max | 1 | A | H | 6 | 1 | **Hợp lệ** – tiếp tục tạo ghế | B15 |

Chỉ `BVA-01..05` là bộ Standard BVA độc lập cho `seatsPerRow`. `BVA-06..15` là **relational boundary tests** cho cặp `startRow/endRow`; không dùng chúng để áp công thức `4n + 1`, vì thay đổi một đầu mút buộc đầu mút còn lại phải đổi để giữ `startRow <= endRow`.

---

### Phần B — Test case phủ Equivalence Partitioning V/X

| STT | Test case | roomId | startRow | endRow | seatsPerRow | seatTypeId | Trạng thái vị trí | Kết quả mong đợi | Tag |
|---:|---|---:|:---:|:---:|---:|---:|---|---|---|
| EP-01 | Tất cả giá trị hợp lệ | 1 | D | D | 6 | 1 | còn vị trí mới | **Hợp lệ** | V1, V2, V3, V4, V5, V6, V7 |
| EP-02 | `roomId <= 0` | 0 | D | D | 6 | 1 | bất kỳ | **Không hợp lệ** – Phòng chiếu không hợp lệ | X1 |
| EP-03 | `roomId` không tồn tại | 999999 | D | D | 6 | 1 | bất kỳ | **Không hợp lệ** – Phòng chiếu không hợp lệ | X2 |
| EP-04 | `seatTypeId` không tồn tại | 1 | D | D | 6 | 999999 | bất kỳ | **Không hợp lệ** – Loại ghế không hợp lệ | X3 |
| EP-05 | `seatsPerRow < 1` | 1 | D | D | 0 | 1 | bất kỳ | **Không hợp lệ** – Số ghế mỗi hàng nhỏ hơn 1 | X4 |
| EP-06 | `seatsPerRow > 12` | 1 | D | D | 13 | 1 | bất kỳ | **Không hợp lệ** – Số ghế mỗi hàng lớn hơn 12 | X5 |
| EP-07 | `startRow` ngoài A..H | 1 | I | I | 6 | 1 | bất kỳ | **Không hợp lệ** – Hàng ghế không hợp lệ | X6 |
| EP-08 | `endRow` ngoài A..H | 1 | D | I | 6 | 1 | bất kỳ | **Không hợp lệ** – Hàng ghế không hợp lệ | X7 |
| EP-09 | `startRow > endRow` | 1 | H | A | 6 | 1 | bất kỳ | **Không hợp lệ** – Khoảng hàng ghế không hợp lệ | X8 |
| EP-10 | Tất cả vị trí đã tồn tại | 1 | D | D | 6 | 1 | tất cả đã tồn tại | **Không hợp lệ** – Không tạo ghế mới | X9 |

### Tổng hợp method `generateSeats`

| Nhóm | Số TC | Phạm vi phủ |
|---|---:|---|
| **Standard BVA (`seatsPerRow`)** | 5 | **B1–B5** |
| **Biên quan hệ (`startRow/endRow`)** | 10 | **B6–B15** |
| **Equivalence Partitioning** | 10 | **V1–V7, X1–X9** |
| **Tổng theo hai bảng** | **25 TC** | **Phủ các partition và boundary chính** |

---

## 5.5. Trạng thái automation thực tế

Đã chạy PHPUnit: **25/25 (5 Standard BVA + 10 biên quan hệ + 10 EP) PASS** trong "SeatAssignmentTest.php".

Mỗi dòng bảng được nạp trực tiếp thành một dataset có tên method/BVA-xx hoặc method/EP-xx. Fixture model trong bộ nhớ được tạo mới cho từng case; room/type 1 tồn tại, 999999 không tồn tại. Test kiểm tra status, thông báo chính xác và các lời gọi tạo/xóa/đồng bộ tổng ghế. Đây là unit test service, chưa chứng minh SQL hoặc HTTP integration.

Evidence: [assignment-junit.xml](assignment-junit.xml). Chi tiết: [07_phpunit_mapping.md](07_phpunit_mapping.md).

---

# 6. METHOD 3 — `bulkDeleteSeats(...)`

---

## 6.1. Mô tả chức năng

Method:

```php
bulkDeleteSeats(
    $roomId,
    $startRow,
    $endRow,
    $startNumber,
    $endNumber
)
```

Mục đích:

```text
Xóa các ghế nằm trong một khoảng hàng và một khoảng số ghế.
```

Các điều kiện hợp lệ:

| Input / điều kiện | Điều kiện |
|---|---|
| `roomId` | `>0` và phòng tồn tại |
| `startRow` | `A..H` |
| `endRow` | `A..H` |
| Quan hệ row | `startRow <= endRow` |
| `startNumber` | `1..12` |
| `endNumber` | `1..12` |
| Quan hệ number | `startNumber <= endNumber` |
| Dữ liệu trong range | Có ít nhất một ghế |

---

## 6.2. Bước 1 — Xác định lớp tương đương

| Biến / điều kiện | Lớp hợp lệ | Tag | Lớp không hợp lệ | Tag |
|---|---|---|---|---|
| `roomId` | `>0` và tồn tại | V1 | `<=0` | X1 |
| `roomId` | `>0` và tồn tại | V1 | `>0` nhưng không tồn tại | X2 |
| `startRow` | `A..H` | V2 | ngoài `A..H` | X3 |
| `endRow` | `A..H` | V3 | ngoài `A..H` | X4 |
| Quan hệ row | `startRow <= endRow` | V4 | `startRow > endRow` | X5 |
| `startNumber` | `1..12` | V5 | `<1` | X6 |
| `startNumber` | `1..12` | V5 | `>12` | X7 |
| `endNumber` | `1..12` | V6 | `<1` | X8 |
| `endNumber` | `1..12` | V6 | `>12` | X9 |
| Quan hệ number | `startNumber <= endNumber` | V7 | `startNumber > endNumber` | X10 |
| Ghế trong range | có ít nhất một ghế | V8 | không có ghế | X11 |

### Bảng theo form Assignment

| Conditions | Valid Partitions | Tag | Invalid Partitions | Tag | Valid Boundaries | Tag |
|---|---|---|---|---|---|---|
| `startRow` | `A..H` | V2 | ngoài `A..H` | X3 | `A`, `H` | B1, B5 |
| `endRow` | `A..H` | V3 | ngoài `A..H` | X4 | `A`, `H` | B6, B10 |
| `startNumber` | `1..12` | V5 | `<1`, `>12` | X6, X7 | `1`, `12` | B11, B15 |
| `endNumber` | `1..12` | V6 | `<1`, `>12` | X8, X9 | `1`, `12` | B16, B20 |
| `startRow <= endRow` | đúng | V4 | sai | X5 | N/A | N/A |
| `startNumber <= endNumber` | đúng | V7 | sai | X10 | N/A | N/A |
| `roomId` | `>0` và tồn tại | V1 | không hợp lệ / không tồn tại | X1, X2 | N/A | N/A |

---

## 6.3. Bước 2 — Phân tích giá trị biên

Có 4 biến có miền đóng:

```text
startRow    ∈ [A,H]
endRow      ∈ [A,H]
startNumber ∈ [1,12]
endNumber   ∈ [1,12]
```

### `startRow`

| Ký hiệu | Giá trị | Tag |
|---|:---:|---|
| min | A | B1 |
| min+ | B | B2 |
| nominal | D | B3 |
| max- | G | B4 |
| max | H | B5 |

### `endRow`

| Ký hiệu | Giá trị | Tag |
|---|:---:|---|
| min | A | B6 |
| min+ | B | B7 |
| nominal | D | B8 |
| max- | G | B9 |
| max | H | B10 |

### `startNumber`

| Ký hiệu | Giá trị | Tag |
|---|---:|---|
| min | 1 | B11 |
| min+ | 2 | B12 |
| nominal | 6 | B13 |
| max- | 11 | B14 |
| max | 12 | B15 |

### `endNumber`

| Ký hiệu | Giá trị | Tag |
|---|---:|---|
| min | 1 | B16 |
| min+ | 2 | B17 |
| nominal | 6 | B18 |
| max- | 11 | B19 |
| max | 12 | B20 |

---

## 6.4. Bước 3 — Thiết kế Test Case

### Phần A — Bộ biên quan hệ cho khoảng xóa

Nguyên tắc: mỗi lần chỉ thay đổi **một biến đang kiểm thử**, các input khác giữ ở giá trị hợp lệ.

Do method có hai điều kiện quan hệ:

```text
startRow <= endRow
startNumber <= endNumber
```

nên khi kiểm tra từng biến phải chọn các giá trị cố định sao cho điều kiện quan hệ vẫn đúng.

Precondition:

```text
roomId = 1 tồn tại

Khi test startRow:
endRow = H
startNumber = 1
endNumber = 12

Khi test endRow:
startRow = A
startNumber = 1
endNumber = 12

Khi test startNumber:
startRow = D
endRow = D
endNumber = 12

Khi test endNumber:
startRow = D
endRow = D
startNumber = 1

Mỗi test chạy trên fixture độc lập hoặc được reset dữ liệu,
và phải có ghế trong range cần xóa.
```

| STT | Test case | roomId | startRow | endRow | startNumber | endNumber | Kết quả mong đợi | Tag |
|---:|---|---:|:---:|:---:|---:|---:|---|---|
| BVA-01 | `startRow` – min | 1 | A | H | 1 | 12 | **Hợp lệ** – tiếp tục xóa ghế | B1 |
| BVA-02 | `startRow` – min+ | 1 | B | H | 1 | 12 | **Hợp lệ** – tiếp tục xóa ghế | B2 |
| BVA-03 | `startRow` – nominal | 1 | D | H | 1 | 12 | **Hợp lệ** – tiếp tục xóa ghế | B3 |
| BVA-04 | `startRow` – max- | 1 | G | H | 1 | 12 | **Hợp lệ** – tiếp tục xóa ghế | B4 |
| BVA-05 | `startRow` – max | 1 | H | H | 1 | 12 | **Hợp lệ** – tiếp tục xóa ghế | B5 |
| BVA-06 | `endRow` – min | 1 | A | A | 1 | 12 | **Hợp lệ** – tiếp tục xóa ghế | B6 |
| BVA-07 | `endRow` – min+ | 1 | A | B | 1 | 12 | **Hợp lệ** – tiếp tục xóa ghế | B7 |
| BVA-08 | `endRow` – nominal | 1 | A | D | 1 | 12 | **Hợp lệ** – tiếp tục xóa ghế | B8 |
| BVA-09 | `endRow` – max- | 1 | A | G | 1 | 12 | **Hợp lệ** – tiếp tục xóa ghế | B9 |
| BVA-10 | `endRow` – max | 1 | A | H | 1 | 12 | **Hợp lệ** – tiếp tục xóa ghế | B10 |
| BVA-11 | `startNumber` – min | 1 | D | D | 1 | 12 | **Hợp lệ** – tiếp tục xóa ghế | B11 |
| BVA-12 | `startNumber` – min+ | 1 | D | D | 2 | 12 | **Hợp lệ** – tiếp tục xóa ghế | B12 |
| BVA-13 | `startNumber` – nominal | 1 | D | D | 6 | 12 | **Hợp lệ** – tiếp tục xóa ghế | B13 |
| BVA-14 | `startNumber` – max- | 1 | D | D | 11 | 12 | **Hợp lệ** – tiếp tục xóa ghế | B14 |
| BVA-15 | `startNumber` – max | 1 | D | D | 12 | 12 | **Hợp lệ** – tiếp tục xóa ghế | B15 |
| BVA-16 | `endNumber` – min | 1 | D | D | 1 | 1 | **Hợp lệ** – tiếp tục xóa ghế | B16 |
| BVA-17 | `endNumber` – min+ | 1 | D | D | 1 | 2 | **Hợp lệ** – tiếp tục xóa ghế | B17 |
| BVA-18 | `endNumber` – nominal | 1 | D | D | 1 | 6 | **Hợp lệ** – tiếp tục xóa ghế | B18 |
| BVA-19 | `endNumber` – max- | 1 | D | D | 1 | 11 | **Hợp lệ** – tiếp tục xóa ghế | B19 |
| BVA-20 | `endNumber` – max | 1 | D | D | 1 | 12 | **Hợp lệ** – tiếp tục xóa ghế | B20 |

Bốn đầu mút không độc lập: `startRow <= endRow` và `startNumber <= endNumber`. Vì vậy 20 dòng này là **relational boundary tests theo từng đầu mút**, không phải một bộ Standard BVA có số lượng `4n + 1`. Các case quan hệ đảo chiều tiếp tục được kiểm tra trong bảng EP.

---

### Phần B — Test case phủ Equivalence Partitioning V/X

| STT | Test case | roomId | startRow | endRow | startNumber | endNumber | Trạng thái range | Kết quả mong đợi | Tag |
|---:|---|---:|:---:|:---:|---:|---:|---|---|---|
| EP-01 | Tất cả giá trị hợp lệ | 1 | D | D | 3 | 6 | có ghế | **Hợp lệ** | V1–V8 |
| EP-02 | `roomId <= 0` | 0 | D | D | 3 | 6 | bất kỳ | **Không hợp lệ** – Phòng chiếu không hợp lệ | X1 |
| EP-03 | `roomId` không tồn tại | 999999 | D | D | 3 | 6 | bất kỳ | **Không hợp lệ** – Phòng chiếu không hợp lệ | X2 |
| EP-04 | `startRow` ngoài A..H | 1 | I | I | 3 | 6 | bất kỳ | **Không hợp lệ** – Khoảng hàng ghế không hợp lệ | X3 |
| EP-05 | `endRow` ngoài A..H | 1 | D | I | 3 | 6 | bất kỳ | **Không hợp lệ** – Khoảng hàng ghế không hợp lệ | X4 |
| EP-06 | `startRow > endRow` | 1 | H | A | 3 | 6 | bất kỳ | **Không hợp lệ** – Khoảng hàng ghế không hợp lệ | X5 |
| EP-07 | `startNumber < 1` | 1 | D | D | 0 | 6 | bất kỳ | **Không hợp lệ** – Khoảng số ghế không hợp lệ | X6 |
| EP-08 | `startNumber > 12` | 1 | D | D | 13 | 13 | bất kỳ | **Không hợp lệ** – Khoảng số ghế không hợp lệ | X7 |
| EP-09 | `endNumber < 1` | 1 | D | D | 1 | 0 | bất kỳ | **Không hợp lệ** – Khoảng số ghế không hợp lệ | X8 |
| EP-10 | `endNumber > 12` | 1 | D | D | 1 | 13 | bất kỳ | **Không hợp lệ** – Khoảng số ghế không hợp lệ | X9 |
| EP-11 | `startNumber > endNumber` | 1 | D | D | 8 | 6 | bất kỳ | **Không hợp lệ** – Khoảng số ghế không hợp lệ | X10 |
| EP-12 | Không có ghế trong range | 1 | D | D | 3 | 6 | không có ghế | **Không hợp lệ** – Không có ghế nào trong khoảng đã chọn | X11 |

### Tổng hợp method `bulkDeleteSeats`

| Nhóm | Số TC | Phạm vi phủ |
|---|---:|---|
| **Standard BVA độc lập** | 0 (N/A) | Các biến bị ràng buộc theo cặp |
| **Biên quan hệ** | 20 | **B1–B20** |
| **Equivalence Partitioning** | 12 | **V1–V8, X1–X11** |
| **Tổng theo hai bảng** | **32 TC** | **Phủ các partition và boundary chính** |

---

## 6.5. Trạng thái automation thực tế

Đã chạy PHPUnit: **32/32 (20 biên quan hệ + 12 EP) PASS** trong "SeatAssignmentTest.php".

Mỗi dòng bảng được nạp trực tiếp thành một dataset có tên method/BVA-xx hoặc method/EP-xx. Fixture model trong bộ nhớ được tạo mới cho từng case; room/type 1 tồn tại, 999999 không tồn tại. Test kiểm tra status, thông báo chính xác và các lời gọi tạo/xóa/đồng bộ tổng ghế. Đây là unit test service, chưa chứng minh SQL hoặc HTTP integration.

Evidence: [assignment-junit.xml](assignment-junit.xml). Chi tiết: [07_phpunit_mapping.md](07_phpunit_mapping.md).

---

# 7. BẢNG TỔNG HỢP TOÀN BỘ TEST DESIGN

| Method | Standard BVA (input duy nhất) | Biên quan hệ / dòng truy vết | Equivalence Partitioning | Tổng dòng automation | Trạng thái execution |
|---|---:|---:|---:|---:|---|
| `validateSeatInput()` | 9 | 1 dòng nominal trùng | 8 | 18 | PHPUnit 18/18 PASS |
| `generateSeats()` | 5 | 10 | 10 | 25 | PHPUnit đầy đủ PASS |
| `bulkDeleteSeats()` | 0 (N/A) | 20 | 12 | 32 | PHPUnit đầy đủ PASS |
| **Tổng** | **14** | **31** | **30** | **75** | — |

Lưu ý:

```text
Một số input có thể xuất hiện ở cả bảng BVA và EP.
Điều này không sai vì hai bảng đo hai mục đích coverage khác nhau:
- BVA tập trung vào boundary.
- EP tập trung vào valid/invalid partition.
```

---

# 8. KẾT QUẢ RÀ SOÁT VÀ THỰC THI (2026-09-15)

## 8.1. Bộ Assignment

```text
OK (75 tests, 307 assertions)
```

Evidence từng case: [assignment-junit.xml](assignment-junit.xml).

Chạy từ thư mục backend:

```powershell
php vendor/bin/phpunit tests/Services/SeatAssignmentTest.php --no-coverage --testdox --log-junit ../docs/testing/modules/seat/assignment-junit.xml
```

## 8.2. Các test khác

- SeatServiceTest.php: 25 test cũ, 12 test validation chỉ trùng một phần input; giữ làm regression. Lần rà soát này dừng ở lỗi kết nối MySQL (connection refused), chưa xác nhận lại PASS.
- SeatServiceCrudTest.php: 20 test integration CRUD, trong đó generate/bulk-delete mới có lỗi room và happy path. Không dùng số lượng này thay cho 57 case Assignment của hai method.
- SeatControllerTest.php: 14 test routing/default/guard, phụ thuộc database qua constructor; không phải 14 case Assignment.
- tests/e2e/seat_bva_test.js: một smoke test mở trang, không chứng minh BVA.
- Postman chính đã có đủ 18 case validateSeatInput, sinh trực tiếp từ bảng Markdown bằng tests/bva/seat-assignment-cases.js. Chưa chạy lại HTTP/Newman trong lần này. Generate/bulkDelete được kiểm thử bằng PHPUnit, chưa có HTTP automation tương ứng.
- Các file Postman _nam, _toan không chứa bộ Seat; collection EP gốc có 3 case số ghế cũ. Các test booking, auth, room, theatre, movie, review và script thử API không thuộc ba method Seat trong báo cáo.

## 8.3. Evidence lịch sử

12 tests/12 assertions, 25 tests/29 assertions, Newman 7/7 và Xdebug Methods 45.00%, Lines 25.90% là số liệu báo cáo cũ. Không dùng làm kết quả hiện tại của bộ 75 case. Lần này không đo lại Xdebug coverage.

## 8.4. Lưu ý về thiết kế EP

Giữ nguyên input 75 dòng theo bản được cung cấp. Generate EP-07 và bulkDelete EP-04 dùng I/I nên đồng thời sai startRow/endRow; bulkDelete EP-08 dùng 13/13 nên đồng thời sai hai số ghế; EP-09 dùng 1/0 còn vi phạm thứ tự. Những dòng này kiểm tra nhóm validation tương ứng, không chứng minh cô lập duy nhất một partition. Với startRow=I hoặc startNumber=13, không thể vừa giữ đầu cuối trong miền vừa giữ thứ tự hợp lệ.

Thông báo trong bảng là mô tả nghiệp vụ. Test assert chuỗi trả về chính xác trong source, ví dụ số ghế ngoài miền trả về “Số ghế phải từ 1 đến 12!”.

---
# 9. KẾT LUẬN

Cách trình bày cuối cùng được chốt như sau:

```text
Mỗi method được chọn
→ Xác định input và business rule
→ Xác định Equivalence Partitioning
→ Xác định biến nào thực sự có miền biên
→ Phân biệt Standard BVA độc lập với biên quan hệ của các input phụ thuộc nhau
→ Gộp toàn bộ valid/invalid partition vào MỘT bảng EP V/X
→ Tổng hợp số test case
→ Đối chiếu với automation và evidence thực tế
```

Ba method được chọn:

```text
validateSeatInput()
generateSeats()
bulkDeleteSeats()
```

Các input kiểu ID không có upper bound do source quy định sẽ **không bị ép thành Standard BVA**. Chúng được kiểm thử bằng Equivalence Partitioning.

Các input rời rạc nhưng có thứ tự và miền đóng như:

```text
seat_row
startRow
endRow
```

chỉ được trình bày theo Standard BVA khi có thể thay đổi một biến và giữ các biến còn lại ở cùng một vector nominal. Khi có ràng buộc `start <= end`, các case được ghi là **biên quan hệ**. Các điểm biên vẫn dùng:

```text
min
min+
nominal
max-
max
```

tương tự các biến số có miền đóng.

Đây là format được sử dụng thống nhất cho toàn bộ báo cáo Seat.
