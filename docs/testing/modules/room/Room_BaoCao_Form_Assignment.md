# BÁO CÁO KIỂM THỬ MODULE ROOM

**Project:** Movie Ticket Booking  
**Module:** Room  
**Service:** `App\Services\RoomService`  
**Ngày rà soát:** 2026-09-15  
**Mẫu trình bày:** [Báo cáo Seat](../seat/083205006374_LuongQuocAn_BaoCao_Seat_Form_Assignment_Chot.md)  
**Trạng thái:** Thiết kế và mapping theo source; không mặc định các case mới đã PASS. Thông tin người thực hiện/MSSV chưa được cung cấp cho module này.

---

# 1. XÁC ĐỊNH MODULE VÀ FILE LIÊN QUAN

Room quản lý phòng chiếu, tổng ghế khai báo, rạp sở hữu và trạng thái hoạt động.

| File | Nội dung đã đối chiếu |
|---|---|
| [RoomService.php](../../../../backend/app/Services/RoomService.php) | Validation lõi, trùng tên, thêm/sửa/xóa/đọc phòng |
| [RoomModel.php](../../../../backend/app/Models/RoomModel.php) | `findByName` tìm tên toàn hệ thống, loại trừ ID khi sửa; SQL insert/update/delete |
| [TheatreModel.php](../../../../backend/app/Models/TheatreModel.php) | Tra cứu rạp tồn tại |
| [RoomController.php](../../../../backend/app/Controllers/RoomController.php) | Trim tên, ép số nguyên ID/số ghế; `is_active=isset(...)` |
| [api.php](../../../../backend/api.php) | `POST /rooms/validate` gọi `validateRoomInput`, có trim/ép kiểu |
| [manage_rooms.php](../../../../frontend/admin/manage_rooms.php) | Form quản lý gọi controller |
| [BookingTicketDatabase.sql](../../../../backend/Database/BookingTicketDatabase.sql) | `rooms.total_seats INT NOT NULL`, tên VARCHAR(50), FK rạp; cascade phòng → ghế/suất chiếu |
| [RoomServiceTest.php](../../../../backend/tests/Services/RoomServiceTest.php) | 19 test integration; tạo rạp tạm ở setUp, dọn phòng/rạp ở tearDown |
| [bva-cases.js](../../../../tests/bva/bva-cases.js) | 4 case Room: -1, 0, 1, 2 |
| [generate-postman.js](../../../../tests/bva/generate-postman.js) | Tạo payload với rạp 1, tên theo mã case |
| [Collection chính](../../../../tests/postman/BVA_MovieBooking.postman_collection.json) | Folder `BVA - Room` |
| [Runner](../../../../tests/automation/run-bva-and-log.js) | Log kết quả API, không đo coverage source |
| [scope.md](scope.md), [equivalence-partition.md](equivalence-partition.md), [bva-test-cases.md](bva-test-cases.md), [whitebox.md](whitebox.md) | Tài liệu cũ cần đọc cùng các hiệu chỉnh ở mục 8 |
| [unit-test-result.txt](unit-test-result.txt), [postman-result.txt](postman-result.txt) | Evidence lịch sử 19 tests/31 assertions và Newman 4/4 |

Phân biệt giới hạn SQL với business rule: kích thước INT/VARCHAR không tự trở thành biên BVA của service. Source không đặt `total_seats <= 96`, `<= 500` hoặc giới hạn theo số hàng ghế. `SeatService::syncRoomTotalSeats` có thể cập nhật lại tổng ghế từ số ghế thực tế; điều đó không bổ sung upper bound cho RoomService.

# 2. XÁC ĐỊNH METHOD

| STT | Method | Visibility | Chức năng |
|---:|---|---|---|
| 1 | `__construct()` | public | Tạo RoomModel, TheatreModel |
| 2 | `addRoom($data)` | public | Validate đầy đủ rồi insert |
| 3 | `updateRoom($id,$data)` | public | Guard ID, validate, kiểm tra tồn tại, update |
| 4 | `deleteRoom($id)` | public | Guard, tồn tại, xóa phòng |
| 5 | `getAllRooms()` | public | Danh sách phòng kèm rạp |
| 6 | `getRoomById($id)` | public | Đọc phòng, bổ sung tên/thành phố rạp |
| 7 | `getAllTheatres()` | public | Danh sách rạp |
| 8 | `validateRoomInput($data)` | public | Chỉ validation lõi |
| 9 | `validateBase($data)` | private | Tên, rạp, tổng ghế |
| 10 | `validate($data,$excludeId=null)` | private | Validation lõi và trùng tên |

# 3. CHỌN METHOD VÀ QUY ƯỚC

Chọn `validateRoomInput`, `addRoom`, `updateRoom`: cùng input nhưng khác kiểm tra trùng tên, ID và ghi dữ liệu. Những method đọc/xóa vẫn được rà soát ở mapping, không bị tính vào số case thiết kế ba method.

Quy trình: input → điều kiện → EP → biên → bảng test → mapping → evidence. Tag V/X/B đánh lại trong từng method; định danh đầy đủ là `Room/method/EP-xx` hoặc `Room/method/LB-xx`.

**Standard BVA miền đóng: N/A cho cả ba method.** `total_seats` chỉ có cận dưới 1; bảng LB là kiểm tra cận dưới một phía, không phải bộ năm giá trị min/min+/nominal/max-/max. ID dùng EP, không tự đặt max. Số nguyên là giả định của bảng; service không tự kiểm tra kiểu nguyên, controller/API mới ép kiểu.

Fixture F: rạp 1 tồn tại; rạp 999999 không tồn tại; phòng 10 tồn tại, tên `Room Old`; phòng 11 tên `Room Used`; ID phòng 999999 không tồn tại. Tên `Room New` chưa tồn tại. Mỗi case dùng fixture mới; `is_active=true`. F phải được tạo trong DB test hoặc bằng model doubles, không giả định đúng với DB người dùng.

---

# 4. METHOD 1 — `validateRoomInput($data)`

## 4.1. Input và điều kiện

| Input | Điều kiện thực tế |
|---|---|
| `name` | `empty(name)` phải false; không trim tại service |
| `theatre_id` | >0 và `findById` có kết quả |
| `total_seats` | >=1; thiếu trường được coi là 0 |
| `is_active` | Giữ true, không có validation trong method |

Không kiểm tra trùng tên và không insert. Tên `'0'` bị `empty` coi là rỗng; tên ba dấu cách vượt qua service trực tiếp, nhưng API trim thành rỗng.

## 4.2. Bước 1 — EP theo form Assignment

| Conditions | Valid Partitions | Tag | Invalid Partitions | Tag | Valid Boundaries | Tag |
|---|---|---|---|---|---|---|
| name | `empty=false` | V1 | `empty=true` | X1 | N/A | N/A |
| theatre_id | >0, tồn tại | V2 | <=0; >0 không tồn tại | X2, X3 | N/A | N/A |
| total_seats | >=1 trong scope số nguyên | V3 | <1 | X4 | 1, cận dưới | B1 |

## 4.3. Bước 2 — Phân tích biên

| Vị trí | Giá trị | Phân loại |
|---|---:|---|
| min-2 | -1 | Ngoài miền, bổ sung |
| min-1 | 0 | Ngoài miền sát cận dưới |
| min | 1 | B1 |
| min+1 | 2 | B2 |
| nominal | 40 | B3, đại diện; không phải biên |
| max-/max | N/A | Source không quy định |

## 4.4. Bước 3 — Thiết kế Test Case

### Phần A — Test case cho Standard BVA

Nguyên tắc: mỗi lần chỉ thay đổi **một biến đang kiểm thử**, các input còn lại giữ ở giá trị hợp lệ.

Giá trị nominal / hợp lệ dùng để giữ cố định:

```java
name         = Room New
theatre_id   = 1 (tồn tại)
is_active    = true
total_seats nominal = 40
Tên phòng có thể trùng khi chỉ gọi validation.
```

**Standard BVA miền đóng: N/A** — `total_seats` chỉ có cận dưới, không có max được source quy định.

#### Bảng bổ sung — Kiểm tra cận dưới một phía (LB)

| STT   | Test case               | name     | theatre_id | total_seats | is_active | Kết quả mong đợi                          | Tag |
| ----- | ----------------------- | -------- | ---------- | ----------- | --------- | ----------------------------------------- | --- |
| LB-01 | `total_seats` – min-2   | Room New | 1          | -1          | true      | **Không hợp lệ** – Số ghế phải lớn hơn 0! | X4  |
| LB-02 | `total_seats` – min-1   | Room New | 1          | 0           | true      | **Không hợp lệ** – Số ghế phải lớn hơn 0! | X4  |
| LB-03 | `total_seats` – min     | Room New | 1          | 1           | true      | **Hợp lệ**                                | B1  |
| LB-04 | `total_seats` – min+    | Room New | 1          | 2           | true      | **Hợp lệ**                                | B2  |
| LB-05 | `total_seats` – nominal | Room New | 1          | 40          | true      | **Hợp lệ**                                | B3  |

---

### Phần B — Test case phủ Equivalence Partitioning V/X

Ở mỗi test, chỉ cố tình làm sai một lớp không hợp lệ; các input khác giữ hợp lệ.

| STT   | Test case                  | name      | theatre_id | total_seats | is_active | Kết quả mong đợi                                  | Tag        |
| ----- | -------------------------- | --------- | ---------- | ----------- | --------- | ------------------------------------------------- | ---------- |
| EP-01 | Tất cả giá trị hợp lệ      | Room New  | 1          | 40          | true      | **Hợp lệ**                                        | V1, V2, V3 |
| EP-02 | `name` rỗng                | `''`      | 1          | 40          | true      | **Không hợp lệ** – Tên phòng không được để trống! | X1         |
| EP-03 | `name` bằng chuỗi 0        | `'0'`     | 1          | 40          | true      | **Không hợp lệ** – Tên phòng không được để trống! | X1         |
| EP-04 | `theatre_id <= 0`          | Room New  | 0          | 40          | true      | **Không hợp lệ** – Rạp chiếu không hợp lệ!        | X2         |
| EP-05 | `theatre_id` không tồn tại | Room New  | 999999     | 40          | true      | **Không hợp lệ** – Rạp chiếu không hợp lệ!        | X3         |
| EP-06 | `total_seats` dưới miền    | Room New  | 1          | 0           | true      | **Không hợp lệ** – Số ghế phải lớn hơn 0!         | X4         |
| EP-07 | `name` chỉ có khoảng trắng | `'   '`   | 1          | 40          | true      | **Hợp lệ**                                        | V1         |
| EP-08 | Tên phòng đã tồn tại       | Room Used | 1          | 40          | true      | **Hợp lệ**                                        | V1, V2, V3 |

#### Ghi chú kết quả mong đợi

- **LB-03, LB-04, LB-05**: Dữ liệu phòng hợp lệ!.
- **EP-07**: tại service trực tiếp.
- **EP-08**: dù tên trùng phòng 11.

### Tổng hợp method `validateRoomInput`

| Nhóm                         | Số TC     | Phạm vi phủ                              |
| ---------------------------- | --------- | ---------------------------------------- |
| **Standard BVA**             | 0 (N/A)   | Không có miền đóng phù hợp               |
| **Cận dưới một phía (LB)**   | 5         | **B1–B3** và partition dưới miền         |
| **Equivalence Partitioning** | 8         | **V1–V3, X1–X4**                         |
| **Tổng theo hai bảng**       | **13 TC** | **Phủ toàn bộ tag thiết kế trong scope** |

Success nghĩa là dữ liệu vượt validation, không phải đã tạo phòng. Tổng: 5 LB + 8 EP = **13 case**.

## 4.5. Mapping automation

LB-01/02/03/04 lần lượt tương ứng `testValidateRoomTotalSeatsBelowMinimum`, `testValidateRoomTotalSeatsZero`, `testValidateRoomTotalSeatsAtMinimum`, `testValidateRoomTotalSeatsMinPlusOne`. PHPUnit dùng rạp tạm/tên ngẫu nhiên thay F, tương đương partition nhưng không trùng literal fixture. Bốn request Postman `TC-ROOM-BVA-01..04` tương ứng cùng giá trị.

LB-05 và EP-01..08 chưa có test riêng gọi method với đầy đủ input ở bảng. EP-06 trùng ý nghĩa LB-02, không tính thành một test độc lập đã có. Không dùng test `addRoom` để khẳng định đã chạy `validateRoomInput` cho từng case.

# 5. METHOD 2 — `addRoom($data)`

## 5.1. Input và chức năng

Giữ bốn input của method 1. Luồng: `validateBase` → kiểm tra tên trùng toàn hệ thống → insert. F độc lập mỗi lần; muốn success thì model insert phải thành công.

## 5.2. Bước 1 — EP

| Conditions | Valid Partitions | Tag | Invalid Partitions | Tag | Valid Boundaries | Tag |
|---|---|---|---|---|---|---|
| name | Không empty | V1 | Empty | X1 | N/A | N/A |
| theatre_id | >0, tồn tại | V2 | <=0; không tồn tại | X2,X3 | N/A | N/A |
| total_seats | >=1 | V3 | <1 | X4 | 1 | B1 |
| tên trong DB | Chưa tồn tại | V4 | Trùng tên phòng khác, kể cả khác rạp | X5 | N/A | N/A |

## 5.3. Bước 2 — Biên

`total_seats`: -1,0,1,2,40; không có max. ID, tên và trạng thái không có Standard BVA đóng tại service. B1=1, B2=2, B3=40.

## 5.4. Bước 3 — Thiết kế Test Case

### Phần A — Test case cho Standard BVA

Nguyên tắc: mỗi lần chỉ thay đổi **một biến đang kiểm thử**, các input còn lại giữ ở giá trị hợp lệ.

Giá trị nominal / hợp lệ dùng để giữ cố định:

```java
name         = Room New (chưa tồn tại)
theatre_id   = 1 (tồn tại)
is_active    = true
total_seats nominal = 40
Model insert thành công; fixture mới cho mỗi test.
```

**Standard BVA miền đóng: N/A** — `total_seats` chỉ có cận dưới, không có max được source quy định.

#### Bảng bổ sung — Kiểm tra cận dưới một phía (LB)

| STT   | Test case               | name     | theatre_id | total_seats | is_active | Kết quả mong đợi                                        | Tag |
| ----- | ----------------------- | -------- | ---------- | ----------- | --------- | ------------------------------------------------------- | --- |
| LB-01 | `total_seats` – min-2   | Room New | 1          | -1          | true      | **Không hợp lệ** – Số ghế phải lớn hơn 0!; không insert | X4  |
| LB-02 | `total_seats` – min-1   | Room New | 1          | 0           | true      | **Không hợp lệ** – Số ghế phải lớn hơn 0!; không insert | X4  |
| LB-03 | `total_seats` – min     | Room New | 1          | 1           | true      | **Hợp lệ**                                              | B1  |
| LB-04 | `total_seats` – min+    | Room New | 1          | 2           | true      | **Hợp lệ**                                              | B2  |
| LB-05 | `total_seats` – nominal | Room New | 1          | 40          | true      | **Hợp lệ**                                              | B3  |

---

### Phần B — Test case phủ Equivalence Partitioning V/X

Ở mỗi test, chỉ cố tình làm sai một lớp không hợp lệ; các input khác giữ hợp lệ.

| STT   | Test case                  | name      | theatre_id | total_seats | is_active | Kết quả mong đợi                                                    | Tag    |
| ----- | -------------------------- | --------- | ---------- | ----------- | --------- | ------------------------------------------------------------------- | ------ |
| EP-01 | Tất cả giá trị hợp lệ      | Room New  | 1          | 40          | true      | **Hợp lệ**                                                          | V1–V4  |
| EP-02 | `name` rỗng                | `''`      | 1          | 40          | true      | **Không hợp lệ** – Tên phòng không được để trống!                   | X1     |
| EP-03 | `theatre_id <= 0`          | Room New  | 0          | 40          | true      | **Không hợp lệ** – Rạp chiếu không hợp lệ!                          | X2     |
| EP-04 | `theatre_id` không tồn tại | Room New  | 999999     | 40          | true      | **Không hợp lệ** – Rạp chiếu không hợp lệ!                          | X3     |
| EP-05 | `total_seats` dưới miền    | Room New  | 1          | 0           | true      | **Không hợp lệ** – Số ghế phải lớn hơn 0!                           | X4     |
| EP-06 | Tên trùng phòng khác       | Room Used | 1          | 40          | true      | **Không hợp lệ** – Tên phòng 'Room Used' đã tồn tại trong hệ thống! | X5     |
| EP-07 | `name` chỉ có khoảng trắng | `'   '`   | 1          | 40          | true      | **Hợp lệ**                                                          | V1, V4 |

#### Ghi chú kết quả mong đợi

- **LB-03, LB-04, LB-05**: insert đúng 1 phòng.
- **EP-01**: Thêm phòng chiếu thành công!.
- **EP-07**: tại service nếu tên này chưa tồn tại và insert thành công.

### Tổng hợp method `addRoom`

| Nhóm                         | Số TC     | Phạm vi phủ                              |
| ---------------------------- | --------- | ---------------------------------------- |
| **Standard BVA**             | 0 (N/A)   | Không có miền đóng phù hợp               |
| **Cận dưới một phía (LB)**   | 5         | **B1–B3** và partition dưới miền         |
| **Equivalence Partitioning** | 7         | **V1–V4, X1–X5**                         |
| **Tổng theo hai bảng**       | **12 TC** | **Phủ toàn bộ tag thiết kế trong scope** |

Tổng: 5 LB + 7 EP = **12 case**. Với lỗi validation phải kiểm tra không gọi insert. Success cần đọc lại name/theatre_id/total_seats/is_active; status đơn thuần chưa chứng minh đủ persistence.

## 5.5. Mapping automation

| Case mới | Test hiện có | Mức tương ứng |
|---|---|---|
| EP-01, LB-05 | `testAddRoomSucceedsWithValidData` | Cùng tổng ghế 40; assert status, tìm phòng để cleanup; chưa assert đủ field |
| EP-02 | `testAddRoomFailsWhenNameEmpty` | Cùng partition, assert message |
| EP-04 | `testAddRoomFailsWhenTheatreInvalid` | ID 999999; không phủ theatre_id<=0 |
| EP-05, LB-02 | `testAddRoomFailsWhenTotalSeatsLessThanOne` | total_seats=0 |
| EP-06 | `testAddRoomFailsWhenNameAlreadyExists` | Tên fixture khác, cùng trùng tên |
| Còn lại | Chưa có riêng | Chưa gán PASS |

# 6. METHOD 3 — `updateRoom($id,$data)`

## 6.1. Input và thứ tự

Thêm `id` vào bốn input. Thứ tự thực tế: id<=0 → base validation → trùng tên loại trừ id → kiểm tra phòng tồn tại → update. Không đặt kiểm tra tồn tại trước validation trong expected result.

## 6.2. Bước 1 — EP

| Conditions | Valid Partitions | Tag | Invalid Partitions | Tag | Valid Boundaries | Tag |
|---|---|---|---|---|---|---|
| id | >0, tồn tại | V1 | <=0; không tồn tại | X1,X2 | N/A | N/A |
| name | Không empty | V2 | Empty | X3 | N/A | N/A |
| theatre_id | >0, tồn tại | V3 | <=0; không tồn tại | X4,X5 | N/A | N/A |
| total_seats | >=1 | V4 | <1 | X6 | 1 | B1 |
| trùng tên | Không trùng phòng khác, kể cả giữ tên chính mình | V5 | Trùng phòng khác | X7 | N/A | N/A |

## 6.3. Bước 2 — Biên

Giống method 2; giữ id=10. B1=1, B2=2, B3=40. Standard BVA đóng N/A.

## 6.4. Bước 3 — Thiết kế Test Case

### Phần A — Test case cho Standard BVA

Nguyên tắc: mỗi lần chỉ thay đổi **một biến đang kiểm thử**, các input còn lại giữ ở giá trị hợp lệ.

Giá trị nominal / hợp lệ dùng để giữ cố định:

```java
id           = 10 (phòng tồn tại)
name         = Room New (không trùng phòng khác)
theatre_id   = 1 (tồn tại)
is_active    = true
total_seats nominal = 40
Model update thành công; fixture mới cho mỗi test.
```

**Standard BVA miền đóng: N/A** — `total_seats` chỉ có cận dưới, không có max được source quy định.

#### Bảng bổ sung — Kiểm tra cận dưới một phía (LB)

| STT   | Test case               | id  | name     | theatre_id | total_seats | is_active | Kết quả mong đợi                          | Tag |
| ----- | ----------------------- | --- | -------- | ---------- | ----------- | --------- | ----------------------------------------- | --- |
| LB-01 | `total_seats` – min-2   | 10  | Room New | 1          | -1          | true      | **Không hợp lệ** – Số ghế phải lớn hơn 0! | X6  |
| LB-02 | `total_seats` – min-1   | 10  | Room New | 1          | 0           | true      | **Không hợp lệ** – Số ghế phải lớn hơn 0! | X6  |
| LB-03 | `total_seats` – min     | 10  | Room New | 1          | 1           | true      | **Hợp lệ**                                | B1  |
| LB-04 | `total_seats` – min+    | 10  | Room New | 1          | 2           | true      | **Hợp lệ**                                | B2  |
| LB-05 | `total_seats` – nominal | 10  | Room New | 1          | 40          | true      | **Hợp lệ**                                | B3  |

---

### Phần B — Test case phủ Equivalence Partitioning V/X

Ở mỗi test, chỉ cố tình làm sai một lớp không hợp lệ; các input khác giữ hợp lệ.

| STT   | Test case                        | id     | name      | theatre_id | total_seats | is_active | Kết quả mong đợi                                                    | Tag    |
| ----- | -------------------------------- | ------ | --------- | ---------- | ----------- | --------- | ------------------------------------------------------------------- | ------ |
| EP-01 | Tất cả giá trị hợp lệ            | 10     | Room New  | 1          | 40          | true      | **Hợp lệ**                                                          | V1–V5  |
| EP-02 | `id <= 0`                        | 0      | Room New  | 1          | 40          | true      | **Không hợp lệ** – ID phòng không hợp lệ!                           | X1     |
| EP-03 | `id` không tồn tại               | 999999 | Room New  | 1          | 40          | true      | **Không hợp lệ** – Phòng chiếu không tồn tại!                       | X2     |
| EP-04 | `name` rỗng                      | 10     | `''`      | 1          | 40          | true      | **Không hợp lệ** – Tên phòng không được để trống!                   | X3     |
| EP-05 | `theatre_id <= 0`                | 10     | Room New  | 0          | 40          | true      | **Không hợp lệ** – Rạp chiếu không hợp lệ!                          | X4     |
| EP-06 | `theatre_id` không tồn tại       | 10     | Room New  | 999999     | 40          | true      | **Không hợp lệ** – Rạp chiếu không hợp lệ!                          | X5     |
| EP-07 | `total_seats` dưới miền          | 10     | Room New  | 1          | 0           | true      | **Không hợp lệ** – Số ghế phải lớn hơn 0!                           | X6     |
| EP-08 | Tên trùng phòng khác             | 10     | Room Used | 1          | 40          | true      | **Không hợp lệ** – Tên phòng 'Room Used' đã tồn tại trong hệ thống! | X7     |
| EP-09 | Giữ tên của chính phòng đang sửa | 10     | Room Old  | 1          | 40          | true      | **Hợp lệ**                                                          | V5     |
| EP-10 | `name` chỉ có khoảng trắng       | 10     | `'   '`   | 1          | 40          | true      | **Hợp lệ**                                                          | V2, V5 |

#### Ghi chú kết quả mong đợi

- **LB-03, LB-04, LB-05**: cập nhật phòng 10.
- **EP-01**: Cập nhật phòng chiếu thành công!.
- **EP-09**: tên của chính phòng 10 được giữ lại.
- **EP-10**: tại service nếu tên chưa trùng và update thành công.

### Tổng hợp method `updateRoom`

| Nhóm                         | Số TC     | Phạm vi phủ                              |
| ---------------------------- | --------- | ---------------------------------------- |
| **Standard BVA**             | 0 (N/A)   | Không có miền đóng phù hợp               |
| **Cận dưới một phía (LB)**   | 5         | **B1–B3** và partition dưới miền         |
| **Equivalence Partitioning** | 10        | **V1–V5, X1–X7**                         |
| **Tổng theo hai bảng**       | **15 TC** | **Phủ toàn bộ tag thiết kế trong scope** |

Tổng: 5 LB + 10 EP = **15 case**.

## 6.5. Mapping automation

`testUpdateRoomFailsWithInvalidId` ↔ EP-02; `testUpdateRoomFailsWhenRoomDoesNotExist` ↔ EP-03; `testUpdateRoomSucceedsAndPersistsChanges` ↔ EP-01/LB-05 theo partition (assert tên mới). Các case khác chưa có test riêng. Postman Room chỉ gọi validation, không chứng minh update hoặc excludeId.

# 7. TỔNG HỢP VÀ WHITE-BOX

| Method | Standard BVA đóng | LB một phía | EP | Tổng dòng thiết kế |
|---|---:|---:|---:|---:|
| validateRoomInput | 0 (N/A) | 5 | 8 | 13 |
| addRoom | 0 (N/A) | 5 | 7 | 12 |
| updateRoom | 0 (N/A) | 5 | 10 | 15 |
| Tổng | 0 | 15 | 25 | **40** |

Một input xuất hiện ở cả EP/LB vẫn là hai dòng thiết kế; không suy ra số test automation độc lập bằng phép cộng mapping.

| Quyết định/nhánh | Case thiết kế | Bằng chứng code test hiện tại |
|---|---|---|
| Tên empty | M1 EP-02/03; M2 EP-02; M3 EP-04 | Có ở addRoom |
| theatre_id<=0 | M1 EP-04; M2 EP-03; M3 EP-05 | Chưa có riêng |
| ID rạp dương nhưng không tồn tại | M1 EP-05; M2 EP-04; M3 EP-06 | Có ở addRoom với 999999 |
| total_seats<1 | LB-01/02 | Có validate và add, chưa có update |
| Trùng tên | M2 EP-06; M3 EP-08/09 | Có add, chưa chứng minh loại trừ ID khi update |
| ID phòng/tồn tại phòng | M3 EP-02/03 | Có ở update/delete |
| Model insert/update trả false | Ngoài 40 dòng; cần fault injection | Chưa có |
| Validation lỗi và ID không tồn tại cùng lúc | Ngoài 40 dòng; expected lỗi validation trước | Chưa có |

Không tính tỷ lệ decision/branch coverage khi chưa lập đủ từng nhánh và có evidence thực thi.

# 8. KẾT QUẢ RÀ SOÁT EVIDENCE VÀ SAI LỆCH TÀI LIỆU CŨ

- File evidence cũ ghi **19 tests, 31 assertions** và Newman **4/4**. Đây là kết quả lưu từ trước; lần soạn này không chạy integration Room, vì setUp ghi database thật. Không gán 40/40 PASS.
- `bva-test-cases.md` ghi case nominal=40 đã PASS, nhưng PHPUnit validation và Postman hiện chỉ có -1,0,1,2. Chưa tìm thấy evidence riêng cho nominal tại validateRoomInput.
- Tài liệu cũ gọi request validation nominal là “tạo phòng thành công”; source chỉ validate, không insert.
- `whitebox.md` gán `testAddRoomFailsWhenTheatreInvalid` cho nhánh theatre_id<=0; input thật là 999999 nên chỉ phủ nhánh không tồn tại. Kết luận 14/14 outcomes chưa đủ căn cứ.
- `validateRoomInput` không đi qua kiểm tra trùng tên; không dùng BVA endpoint làm evidence nhánh `findByName`.
- Không coi nominal là biên; không dùng giới hạn số ghế Seat để tự đặt max cho Room.
- Tên toàn dấu cách: báo cáo ghi hành vi service trực tiếp, khác API/controller do trim. Đây là chênh lệch tầng xử lý cần giữ rõ trong test.

# 9. THỰC THI TIẾP THEO VÀ KẾT LUẬN

Từ thư mục `backend`, khi DB test/fixture đã sẵn sàng:

```powershell
php vendor/bin/phpunit tests/Services/RoomServiceTest.php --no-coverage --do-not-cache-result --testdox
```

Từ thư mục gốc, API trỏ DB test:

```powershell
node tests/automation/run-bva-and-log.js "BVA - Room"
```

Nếu đo coverage thì tạo report riêng Room và ghi rõ phạm vi suite. Các case insert/update cần cleanup cả khi assertion thất bại; không dùng phòng/rạp seed để thử xóa cascade. Schema có giới hạn lưu trữ nhưng vượt giới hạn SQL là bài integration riêng, chưa có service error chuẩn cho mọi SQL exception.

**Kết luận:** Đã thiết kế 40 dòng cho ba method và chỉ rõ phần automation hiện có. Room áp dụng EP và biên một phía; không thể chép nguyên Standard BVA miền đóng của Seat.
