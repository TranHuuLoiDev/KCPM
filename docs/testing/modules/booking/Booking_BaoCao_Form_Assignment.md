> **Cập nhật thực thi 2026-09-15:** 52 case thiết kế chính đã đồng bộ với `BookingAssignmentTest` và PASS. Có thêm 6/6 WB fault-injection PASS. Toàn suite: 398 tests, 1.112 assertions, không fail/skip. Evidence hiện hành: [JUnit](../../../../outputs/final-testing-report/evidence/phpunit.xml), [Xdebug](../../../../outputs/final-testing-report/evidence/coverage-summary.json), [Newman](../../../../outputs/final-testing-report/evidence/newman.json). Các nhận xét automation cũ bên dưới chỉ là lịch sử; dùng mapping hiện hành tại mục N.5.

# BÁO CÁO KIỂM THỬ MODULE BOOKING

**Project:** Movie Ticket Booking  
**Module:** Booking  
**Service:** `App\Services\BookingService`  
**Ngày rà soát:** 2026-09-15  
**Mẫu:** [Báo cáo Seat](../seat/083205006374_LuongQuocAn_BaoCao_Seat_Form_Assignment_Chot.md)  
**Trạng thái:** Đã đồng bộ bảng thiết kế với test và xác minh bằng lần chạy chung. Người phụ trách kiểm thử chưa được xác nhận.

---

# 1. XÁC ĐỊNH MODULE VÀ FILE LIÊN QUAN

Booking xử lý đặt vé, tính tiền theo ghế, hủy booking, quản trị trạng thái và truy vấn dữ liệu. Các quyết định chủ yếu phụ thuộc ID, tập trạng thái, thời gian và dữ liệu liên quan; không có miền số lượng ghế đóng như Seat.

| File | Vai trò đã rà soát |
|---|---|
| [BookingService.php](../../../../backend/app/Services/BookingService.php) | Business rule, thời gian, trạng thái, transaction, chuẩn hóa filter |
| [BookingModel.php](../../../../backend/app/Models/BookingModel.php) | Tạo booking, kiểm tra ownership, hủy booking/vé, khôi phục, truy vấn và transaction |
| [ShowtimeModel.php](../../../../backend/app/Models/ShowtimeModel.php) | `getDetailById` cung cấp trạng thái, phòng, thời gian, base_price |
| [SeatModel.php](../../../../backend/app/Models/SeatModel.php) | `getByIds` lấy vị trí, phòng, trạng thái ghế và phụ phí loại ghế |
| [TicketModel.php](../../../../backend/app/Models/TicketModel.php) | Kiểm tra ghế đã đặt và `createMany` |
| [BookingController.php](../../../../backend/app/Controllers/BookingController.php) | Action book_ticket/cancel_booking; admin update_status/delete; lấy user từ session |
| [booking.php](../../../../frontend/booking.php), [booking_history.php](../../../../frontend/booking_history.php), [manage_booking.php](../../../../frontend/admin/manage_booking.php) | Luồng giao diện gọi controller |
| [api.php](../../../../backend/api.php) | Các route Booking đang dùng model trực tiếp hoặc nhánh mô phỏng BVA; xem mục 8 |
| [BookingTicketDatabase.sql](../../../../backend/Database/BookingTicketDatabase.sql) | FK user/booking/showtime/seat, trạng thái, cascade; không có giới hạn 10 ghế trong service |
| [BookingServiceTest.php](../../../../backend/tests/Services/BookingServiceTest.php) | 53 test dùng Reflection/model mocks, không cần DB |
| [BookingModelTest.php](../../../../backend/tests/Models/BookingModelTest.php) | 18 test integration, tạo/xóa booking thật, user seed 2 |
| [bva-cases.js](../../../../tests/bva/bva-cases.js), [generate-postman.js](../../../../tests/bva/generate-postman.js) | 39 case Booking, đặt tên BVA cho các ID cận dưới |
| [Collection chính](../../../../tests/postman/BVA_MovieBooking.postman_collection.json), [runner](../../../../tests/automation/run-bva-and-log.js) | Cần đối chiếu route thật trước khi sử dụng kết quả |
| [BOOKING.md](../BOOKING.md), [booking.md](../../../../booking.md), [Assignment tham khảo](../../../../087205001779_HuynhPhamHuuTien_Assignment.md) | Tài liệu tham khảo; không dùng miền tín chỉ/GPA hoặc giả định bài mẫu làm rule Booking |

# 2. XÁC ĐỊNH METHOD

| STT | Method | Visibility | Chức năng |
|---:|---|---|---|
| 1 | `__construct(?callable $clock = null)` | public | Tạo bốn model |
| 2 | `processBooking($userId,$showtimeId,$seatIds,$paymentMethod)` | public | Guard, tính tiền, tạo booking và vé trong transaction |
| 3 | `getUserBookings($userId)` | public | ID<=0 trả []; còn lại đọc model |
| 4 | `cancelBooking($userId,$bookingId)` | public | Ownership, trạng thái, thời gian, transaction hủy |
| 5 | `getAdminBookingStats()` | public | Chuyển tiếp thống kê |
| 6 | `getAdminBookings($input)` | public | Chuẩn hóa filter rồi đọc model |
| 7 | `getAdminBookingDetail($bookingId)` | public | Guard, đọc booking và tickets |
| 8 | `updateAdminBookingStatus($bookingId,$status)` | public | Kiểm tra trạng thái, xung đột ghế, đồng bộ vé |
| 9 | `deleteAdminBooking($bookingId)` | public | Guard, tồn tại, transaction xóa |
| 10 | `normalizeAdminFilters($input)` | public | Status/date hợp lệ giữ lại, search trim |
| 11 | `getTotalSpentByUser($userId)` | public | ID<=0 trả 0, còn lại gọi model |
| 12 | `isValidDate($date)` | private | Parse Y-m-d và so sánh round-trip |

# 3. CHỌN METHOD VÀ QUY ƯỚC

Chọn **bốn** method: processBooking, cancelBooking, updateAdminBookingStatus và normalizeAdminFilters. Ba method đầu là luồng thay đổi booking; method cuối được phân tích riêng để bao gồm rule ngày và các test filter hiện có. Không ép số method hoặc số case phải bằng Seat.

Quy trình từng method: input/fixture → EP → phân tích biên → bảng case → mapping thực thi. Tag V/X/B được reset theo method; định danh đầy đủ `Booking/method/EP-xx` hoặc `Booking/method/TB-xx`. N/A không tính là test case.

**Standard BVA miền đóng: N/A.** ID chỉ có điều kiện dương; số ghế chỉ yêu cầu mảng không rỗng, không có max trong BookingService. Payment/status là tập liệt kê, không có thứ tự min/max. Thời gian được xét theo ngưỡng “bây giờ”; TB là kiểm tra ngưỡng động, không phải Standard BVA năm giá trị cho miền đóng.

Fixture F dùng cho thiết kế:

- U=10 là user có thật cho integration; U2=11 là user khác.
- S=1 active, room_id=1, thời gian bắt đầu tương lai một ngày, base_price=100000.
- Ghế 5 và 6 thuộc phòng 1, active, chưa đặt tại S; phụ phí 20000 và 30000. Giá từng vé 120000 và 130000, tổng 250000.
- K=100 thuộc U, status=paid, suất chiếu chính tương lai, có tickets. K=999999 không tồn tại. Các ID chỉ là fixture test, không giả định seed đang có đúng như vậy.
- Model trả thành công, booking mới=1001, trừ khi case ghi khác. Mỗi case có fixture độc lập.
- Case thời gian chính xác phải kiểm soát clock/timezone; dùng cùng timezone của PHP và fixture. Không dựa vào việc lệnh chạy “đủ nhanh”.

---

# 4. METHOD 1 — `processBooking(...)`

## 4.1. Input và thứ tự xử lý

| Input/điều kiện | Rule thực tế |
|---|---|
| userId | >0; service không lookup user tồn tại |
| showtimeId | >0, tồn tại, status đúng chuỗi `active` |
| seatIds | Mảng không rỗng; sau đó intval từng phần tử, loại trùng, reindex |
| paymentMethod | cash/momo/vnpay/bank_transfer; giá trị khác tự đổi cash |
| thời gian suất | Parse được và <=now thì lỗi; nếu parse trả false thì bỏ qua guard thời gian |
| dữ liệu ghế | Đủ số lượng ID sau chuẩn hóa, đúng phòng, active, chưa booked |
| giá | base_price + seat_type_price từng ghế, cộng tổng |
| ghi dữ liệu | begin → tạo booking → tạo vé → commit; Exception trong try → rollback |

Guard user → showtimeId → mảng ghế → normalize payment → tra cứu suất → thời gian → chuẩn hóa ID ghế → đủ ghế → từng ghế → transaction. Kiểm tra đã đặt nằm trước transaction; kết quả mock không chứng minh chống đặt trùng khi có hai request đồng thời.

## 4.2. Bước 1 — EP theo form Assignment

| Conditions | Valid Partitions | Tag | Invalid Partitions | Tag | Valid Boundaries | Tag |
|---|---|---|---|---|---|---|
| userId | >0 | V1 | <=0 | X1 | N/A | N/A |
| showtimeId | >0 | V2 | <=0 | X2 | N/A | N/A |
| seatIds | Mảng không rỗng | V3 | Mảng rỗng hoặc không phải mảng | X3 | N/A | N/A |
| suất chiếu | Tồn tại, active | V4 | Không tồn tại; không active | X4,X5 | N/A | N/A |
| thời gian parse được | >now | V5 | <=now | X6 | Ngưỡng động | TB |
| ID ghế | Lookup đủ ghế sau loại trùng | V6 | Thiếu ghế | X7 | N/A | N/A |
| phòng của ghế | Đúng phòng suất | V7 | Khác phòng | X8 | N/A | N/A |
| ghế active | int(is_active)=1 | V8 | Khác 1 | X9 | N/A | N/A |
| vé tồn tại | Chưa booked | V9 | Có vé status khác canceled | X10 | N/A | N/A |
| paymentMethod | Một trong 4 giá trị cho phép | V10 | Giá trị ngoài tập, được chuẩn hóa cash | X11 | N/A | N/A |

X11 là input ngoài danh sách nhưng kết quả hợp lệ sau chuẩn hóa; không kỳ vọng error cho mọi tag X.

## 4.3. Bước 2 — Phân tích biên

ID -1,0,1,2 trong bộ cũ là kiểm tra cận dưới/EP. Chỉ vượt guard ID không có nghĩa booking thành công. Với seatIds, []/[5]/[5,6] kiểm tra rỗng/một/nhiều ghế; không có max=10 hoặc max=12 ở method. Giới hạn seat_number=12 của Seat không phải giới hạn số vé mỗi booking.

Với clock cố định T, xét thời điểm suất T-1 giây, T, T+1 giây; các input khác thuộc F. Source có clock injection qua constructor; test cố định T để kiểm tra chính xác T-1/T/T+1 giây.

## 4.4. Bước 3 — Thiết kế Test Case

### Phần A — Test case cho Standard BVA

Nguyên tắc: mỗi lần chỉ thay đổi **một biến đang kiểm thử**, các input còn lại giữ ở giá trị hợp lệ.

Giá trị nominal / hợp lệ dùng để giữ cố định:

```java
userId        = 10
showtimeId    = 1
seatIds       = [5, 6]
paymentMethod = momo
Fixture F: suất active, bắt đầu tương lai; ghế đúng phòng, active, chưa đặt.
Kiểm tra TB: cố định clock T, chỉ đổi thời điểm bắt đầu suất chiếu.
Model ghi dữ liệu thành công.
```

**Standard BVA miền đóng: N/A** — Thời gian được kiểm tra theo ngưỡng động T, không có miền đóng min/max.

#### Bảng bổ sung — Kiểm tra ngưỡng thời gian (TB)

| STT   | Test case                           | userId | showtimeId | seatIds | paymentMethod | Thời điểm S | Kết quả mong đợi                                               | Tag |
| ----- | ----------------------------------- | ------ | ---------- | ------- | ------------- | ----------- | -------------------------------------------------------------- | --- |
| TB-01 | Thời điểm suất chiếu – trước ngưỡng | 10     | 1          | [5,6]   | momo          | T-1 giây    | **Không hợp lệ** – Suất chiếu này đã bắt đầu hoặc đã kết thúc. | X6  |
| TB-02 | Thời điểm suất chiếu – tại ngưỡng   | 10     | 1          | [5,6]   | momo          | T           | **Không hợp lệ** – Cùng lỗi; so sánh dùng <=                   | X6  |
| TB-03 | Thời điểm suất chiếu – sau ngưỡng   | 10     | 1          | [5,6]   | momo          | T+1 giây    | **Hợp lệ**                                                     | V5  |

---

### Phần B — Test case phủ Equivalence Partitioning V/X

Ở mỗi test, chỉ cố tình làm sai một lớp không hợp lệ; các input khác giữ hợp lệ.

| STT   | Test case                             | userId | showtimeId | seatIds       | paymentMethod  | Thay đổi F            | Kết quả mong đợi                                                 | Tag    |
| ----- | ------------------------------------- | ------ | ---------- | ------------- | -------------- | --------------------- | ---------------------------------------------------------------- | ------ |
| EP-01 | Tất cả giá trị hợp lệ                 | 10     | 1          | [5,6]         | momo           | Không                 | **Hợp lệ**                                                       | V1–V10 |
| EP-02 | `userId <= 0`                         | 0      | 1          | [5,6]         | momo           | Không                 | **Không hợp lệ** – Vui lòng đăng nhập để đặt vé.; page=login.php | X1     |
| EP-03 | `showtimeId <= 0`                     | 10     | 0          | [5,6]         | momo           | Không                 | **Không hợp lệ** – Suất chiếu không hợp lệ.                      | X2     |
| EP-04 | Danh sách ghế rỗng                    | 10     | 1          | []            | momo           | Không                 | **Không hợp lệ** – Vui lòng chọn ít nhất 1 ghế.                  | X3     |
| EP-05 | `seatIds` không phải mảng             | 10     | 1          | `'5'` (chuỗi) | momo           | Không                 | **Không hợp lệ** – Cùng lỗi chọn ghế                             | X3     |
| EP-06 | Suất chiếu không tồn tại              | 10     | 999999     | [5,6]         | momo           | Lookup suất=null      | **Không hợp lệ** – Suất chiếu không khả dụng.                    | X4     |
| EP-07 | Suất chiếu không active               | 10     | 1          | [5,6]         | momo           | S.status=inactive     | **Không hợp lệ** – Suất chiếu không khả dụng.                    | X5     |
| EP-08 | Suất chiếu đã bắt đầu                 | 10     | 1          | [5,6]         | momo           | S bắt đầu hôm qua     | **Không hợp lệ** – Suất chiếu này đã bắt đầu hoặc đã kết thúc.   | X6     |
| EP-09 | ID ghế không tồn tại                  | 10     | 1          | [999999]      | momo           | Không tìm được ghế    | **Không hợp lệ** – Danh sách ghế không hợp lệ.                   | X7     |
| EP-10 | Ghế thuộc phòng khác                  | 10     | 1          | [5]           | momo           | Ghế 5.room_id=2       | **Không hợp lệ** – Ghế không thuộc phòng chiếu này.              | X8     |
| EP-11 | Ghế không active                      | 10     | 1          | [5]           | momo           | Ghế 5.is_active=0     | **Không hợp lệ** – Có ghế không khả dụng.                        | X9     |
| EP-12 | Ghế đã được đặt                       | 10     | 1          | [5]           | momo           | Ghế 5 đã booked tại S | **Không hợp lệ** – Có ghế vừa được đặt. Vui lòng chọn ghế khác.  | X10    |
| EP-13 | Payment ngoài tập được đổi thành cash | 10     | 1          | [5]           | invalid_method | Không                 | **Hợp lệ**                                                       | X11    |
| EP-14 | Danh sách có ID ghế trùng             | 10     | 1          | [5,5]         | momo           | Không                 | **Hợp lệ**                                                       | V3, V6 |
| EP-15 | Thanh toán bằng cash                  | 10     | 1          | [5]           | cash           | Không                 | **Hợp lệ**                                                       | V10    |
| EP-16 | Thanh toán bằng vnpay                 | 10     | 1          | [5]           | vnpay          | Không                 | **Hợp lệ**                                                       | V10    |
| EP-17 | Thanh toán bằng bank_transfer         | 10     | 1          | [5]           | bank_transfer  | Không                 | **Hợp lệ**                                                       | V10    |

#### Ghi chú kết quả mong đợi

- **TB-03**: nếu clock được giữ T, tạo 2 vé.
- **EP-01**: booking_id=1001; 2 vé; tổng 250000.
- **EP-13**: createBooking nhận cash, tổng 120000.
- **EP-14**: lookup [5], tạo 1 vé, tổng 120000.
- **EP-15**: giữ cash.
- **EP-16**: giữ vnpay.
- **EP-17**: giữ bank_transfer.

### Tổng hợp method `processBooking`

| Nhóm                         | Số TC     | Phạm vi phủ                              |
| ---------------------------- | --------- | ---------------------------------------- |
| **Standard BVA**             | 0 (N/A)   | Không có miền đóng phù hợp               |
| **Ngưỡng thời gian (TB)**    | 3         | Trước / tại / sau ngưỡng T               |
| **Equivalence Partitioning** | 17        | **V1–V10, X1–X11**                       |
| **Tổng theo hai bảng**       | **20 TC** | **Phủ toàn bộ tag thiết kế trong scope** |

Success message: `Đặt vé thành công!`. Với lỗi guard/seat phải không tạo booking/vé; success assert payload từng vé, commit và không rollback. Tổng **3 TB + 17 EP = 20 dòng**.

## 4.5. Mapping automation hiện hành

`backend/tests/Services/BookingAssignmentTest.php::testAssignment` đọc trực tiếp từng dòng trong mục 4.4 bằng `AssignmentCases`. Dataset được định danh `processBooking/ID`; kiểm tra đầy đủ input, kết quả, thông báo và lời gọi model tương ứng. **20/20 PASS** trong lần chạy chung 2026-09-15.


# 5. METHOD 2 — `cancelBooking($userId,$bookingId)`

## 5.1. Input và rule

Ép int cả hai ID; user>0, booking>0; `getByIdAndUser` phải tìm thấy; status chưa canceled; nếu có suất chính và parse được thời gian thì phải >now. Không có suất chính, thiếu trường ngày/giờ hoặc parse=false thì source bỏ qua guard thời gian. Khi model cancel trả true: commit và success; false/Exception trong try: rollback, trả message lỗi.

Model cancel cập nhật booking và tickets; unit mock service không chứng minh dữ liệu ticket thực tế đã đổi. Suất chính được lấy từ model, không phải tham số trực tiếp của method.

## 5.2. Bước 1 — EP

| Conditions | Valid Partitions | Tag | Invalid Partitions | Tag | Valid Boundaries | Tag |
|---|---|---|---|---|---|---|
| userId | >0 | V1 | <=0 | X1 | N/A | N/A |
| bookingId | >0 | V2 | <=0 | X2 | N/A | N/A |
| booking/ownership | Tồn tại, thuộc user | V3 | Không tồn tại hoặc không thuộc user | X3 | N/A | N/A |
| status hiện tại | Khác canceled | V4 | canceled | X4 | N/A | N/A |
| thời gian parse được | >now | V5 | <=now | X5 | Ngưỡng động | TB |
| dữ liệu thời gian | Không có suất hoặc parse=false: bỏ qua guard | V6 | Không có error validation riêng | N/A | N/A | N/A |

V6 mô tả hành vi hiện tại, không xác nhận đây là yêu cầu nghiệp vụ mong muốn.

## 5.3. Bước 2 — Biên

Standard BVA đóng N/A. TB dùng clock T kiểm soát; giữ user=10, booking=100, status=paid và model cancel thành công.

## 5.4. Bước 3 — Thiết kế Test Case

### Phần A — Test case cho Standard BVA

Nguyên tắc: mỗi lần chỉ thay đổi **một biến đang kiểm thử**, các input còn lại giữ ở giá trị hợp lệ.

Giá trị nominal / hợp lệ dùng để giữ cố định:

```java
userId    = 10
bookingId = 100 (thuộc user 10)
status hiện tại = paid
Suất chính bắt đầu tương lai; model cancel thành công.
Kiểm tra TB: cố định clock T, chỉ đổi thời điểm suất chính.
```

**Standard BVA miền đóng: N/A** — Thời gian được kiểm tra theo ngưỡng động T, không có miền đóng min/max.

#### Bảng bổ sung — Kiểm tra ngưỡng thời gian (TB)

| STT   | Test case                           | userId | bookingId | Suất chính của K | Kết quả mong đợi                                               | Tag |
| ----- | ----------------------------------- | ------ | --------- | ---------------- | -------------------------------------------------------------- | --- |
| TB-01 | Thời điểm suất chiếu – trước ngưỡng | 10     | 100       | T-1 giây         | **Không hợp lệ** – Khong the huy ve khi suat chieu da bat dau. | X5  |
| TB-02 | Thời điểm suất chiếu – tại ngưỡng   | 10     | 100       | T                | **Không hợp lệ** – Cùng lỗi, không cancel                      | X5  |
| TB-03 | Thời điểm suất chiếu – sau ngưỡng   | 10     | 100       | T+1 giây         | **Hợp lệ**                                                     | V5  |

---

### Phần B — Test case phủ Equivalence Partitioning V/X

Ở mỗi test, chỉ cố tình làm sai một lớp không hợp lệ; các input khác giữ hợp lệ.

| STT   | Test case                             | userId | bookingId | Thay đổi F                                             | Kết quả mong đợi                                               | Tag   |
| ----- | ------------------------------------- | ------ | --------- | ------------------------------------------------------ | -------------------------------------------------------------- | ----- |
| EP-01 | Tất cả giá trị hợp lệ                 | 10     | 100       | Không                                                  | **Hợp lệ**                                                     | V1–V5 |
| EP-02 | `userId <= 0`                         | 0      | 100       | Không                                                  | **Không hợp lệ** – Vui long dang nhap de huy ve.               | X1    |
| EP-03 | `bookingId <= 0`                      | 10     | 0         | Không                                                  | **Không hợp lệ** – Booking khong hop le.                       | X2    |
| EP-04 | Booking không tồn tại                 | 10     | 999999    | Không tồn tại                                          | **Không hợp lệ** – Khong tim thay booking can huy.             | X3    |
| EP-05 | Booking không thuộc user              | 11     | 100       | K thuộc user 10                                        | **Không hợp lệ** – Cùng lỗi không tìm thấy booking             | X3    |
| EP-06 | Booking đã canceled                   | 10     | 100       | K.status=canceled                                      | **Không hợp lệ** – Booking nay da duoc huy truoc do.           | X4    |
| EP-07 | Suất chiếu đã bắt đầu                 | 10     | 100       | Suất bắt đầu hôm qua                                   | **Không hợp lệ** – Khong the huy ve khi suat chieu da bat dau. | X5    |
| EP-08 | Không có suất chính                   | 10     | 100       | getPrimaryShowtimeByBookingId trả null                 | **Hợp lệ**                                                     | V6    |
| EP-09 | Thời gian suất chính không parse được | 10     | 100       | show_date='invalid', start_time='invalid', parse=false | **Hợp lệ**                                                     | V6    |

#### Ghi chú kết quả mong đợi

- **TB-03**: nếu clock giữ T.
- **EP-01**: Huy ve thanh cong.; commit.
- **EP-08, EP-09**: theo source hiện tại.

### Tổng hợp method `cancelBooking`

| Nhóm                         | Số TC     | Phạm vi phủ                              |
| ---------------------------- | --------- | ---------------------------------------- |
| **Standard BVA**             | 0 (N/A)   | Không có miền đóng phù hợp               |
| **Ngưỡng thời gian (TB)**    | 3         | Trước / tại / sau ngưỡng T               |
| **Equivalence Partitioning** | 9         | **V1–V6, X1–X5**                         |
| **Tổng theo hai bảng**       | **12 TC** | **Phủ toàn bộ tag thiết kế trong scope** |

Tổng **3 TB + 9 EP = 12 dòng**. Giữ nguyên thông báo không dấu của source, không tự sửa message kỳ vọng sang tiếng Việt có dấu.

## 5.5. Mapping automation hiện hành

`backend/tests/Services/BookingAssignmentTest.php::testAssignment` đọc trực tiếp từng dòng trong mục 5.4 bằng `AssignmentCases`. Dataset được định danh `cancelBooking/ID`; kiểm tra đầy đủ input, kết quả, thông báo và lời gọi model tương ứng. **12/12 PASS** trong lần chạy chung 2026-09-15.


# 6. METHOD 3 — `updateAdminBookingStatus($bookingId,$status)`

## 6.1. Input và tác động

Ép bookingId thành int, trim status; ID>0, status thuộc pending/paid/canceled, booking tồn tại. Chỉ kiểm tra conflict khi status cũ=canceled và status mới khác canceled. Status vé=canceled nếu hủy, ngược lại booked. Cập nhật cả booking/vé trong một transaction.

Không có rule cấm paid→pending trong source; không áp rule quyền admin vào service khi nó không nhận user/role. Kiểm thử quyền truy cập ở tầng route/page là scope riêng.

## 6.2. Bước 1 — EP

| Conditions | Valid Partitions | Tag | Invalid Partitions | Tag | Valid Boundaries | Tag |
|---|---|---|---|---|---|---|
| bookingId | >0 và tồn tại | V1 | <=0; không tồn tại | X1,X2 | N/A | N/A |
| status mới sau trim | pending/paid/canceled | V2 | Ngoài tập | X3 | N/A | N/A |
| khôi phục booking canceled | Không conflict | V3 | Conflict với vé của booking khác | X4 | N/A | N/A |
| không khôi phục | Không cần kiểm tra conflict | V4 | N/A | N/A | N/A | N/A |

## 6.3. Bước 2 — Biên

Standard BVA N/A: ID không có max, status là tập định danh. Không lấy thứ tự pending→paid→canceled làm min/nominal/max.

## 6.4. Bước 3 — Thiết kế Test Case

### Phần A — Test case cho Standard BVA

Nguyên tắc: mỗi lần chỉ thay đổi **một biến đang kiểm thử**, các input còn lại giữ ở giá trị hợp lệ.

Giá trị nominal / hợp lệ dùng để giữ cố định:

```java
bookingId = 100 (tồn tại)
status mới = paid
status cũ = pending
Không có xung đột ghế; model cập nhật booking/vé thành công.
```

**Standard BVA miền đóng: N/A** — Các input không có miền biên đóng được source quy định.

---

### Phần B — Test case phủ Equivalence Partitioning V/X

Ở mỗi test, chỉ cố tình làm sai một lớp không hợp lệ; các input khác giữ hợp lệ.

| STT   | Test case                             | bookingId | status mới | Status cũ / fixture                   | Kết quả mong đợi                                                                       | Tag        |
| ----- | ------------------------------------- | --------- | ---------- | ------------------------------------- | -------------------------------------------------------------------------------------- | ---------- |
| EP-01 | Cập nhật pending thành paid           | 100       | paid       | pending                               | **Hợp lệ**                                                                             | V1, V2, V4 |
| EP-02 | Cập nhật paid thành pending           | 100       | pending    | paid                                  | **Hợp lệ**                                                                             | V2, V4     |
| EP-03 | Cập nhật paid thành canceled          | 100       | canceled   | paid                                  | **Hợp lệ**                                                                             | V2, V4     |
| EP-04 | `bookingId <= 0`                      | 0         | paid       | Không cần lookup                      | **Không hợp lệ** – Booking không hợp lệ.                                               | X1         |
| EP-05 | Status mới ngoài tập hợp lệ           | 100       | invalid    | paid                                  | **Không hợp lệ** – Trạng thái booking không hợp lệ.                                    | X3         |
| EP-06 | Booking không tồn tại                 | 999999    | paid       | Lookup=null                           | **Không hợp lệ** – Không tìm thấy booking cần cập nhật.                                | X2         |
| EP-07 | Khôi phục canceled có xung đột ghế    | 100       | paid       | canceled, conflict=true               | **Không hợp lệ** – Không thể khôi phục booking vì có ghế đã được đặt bởi booking khác. | X4         |
| EP-08 | Khôi phục canceled không xung đột     | 100       | paid       | canceled, conflict=false              | **Hợp lệ**                                                                             | V3         |
| EP-09 | Giữ canceled, không kiểm tra xung đột | 100       | canceled   | canceled, có ghế trùng ở booking khác | **Hợp lệ**                                                                             | V4         |
| EP-10 | Trim khoảng trắng của status mới      | 100       | `' paid '` | pending                               | **Hợp lệ**                                                                             | V2         |

#### Ghi chú kết quả mong đợi

- **EP-01**: booking paid, vé booked.
- **EP-02**: booking pending, vé booked.
- **EP-03**: booking/vé canceled.
- **EP-08**: vé booked.
- **EP-09**: không gọi conflict check, vẫn canceled.
- **EP-10**: model nhận paid.

### Tổng hợp method `updateAdminBookingStatus`

| Nhóm                         | Số TC     | Phạm vi phủ                              |
| ---------------------------- | --------- | ---------------------------------------- |
| **Standard BVA**             | 0 (N/A)   | Không có miền đóng phù hợp               |
| **Equivalence Partitioning** | 10        | **V1–V4, X1–X4**                         |
| **Tổng theo bảng EP**        | **10 TC** | **Phủ toàn bộ tag thiết kế trong scope** |

Success message: `Cập nhật trạng thái booking thành công.`. Tổng **10 EP**. Lỗi trước transaction không được ghi dữ liệu; success phải assert status từng bảng và commit.

## 6.5. Mapping automation hiện hành

`backend/tests/Services/BookingAssignmentTest.php::testAssignment` đọc trực tiếp từng dòng trong mục 6.4 bằng `AssignmentCases`. Dataset được định danh `updateAdminBookingStatus/ID`; kiểm tra đầy đủ input, kết quả, thông báo và lời gọi model tương ứng. **10/10 PASS** trong lần chạy chung 2026-09-15.


# 7. METHOD 4 — `normalizeAdminFilters($input)`

## 7.1. Input và chức năng

Nhận status/from_date/to_date/search. Tất cả trim sau ép string. Status ngoài tập thành `''`; ngày thiếu/sai định dạng/sai lịch thành `''`; search được trim. Không có response status success/error: kết quả là mảng bốn filter. Không kiểm tra from_date<=to_date.

## 7.2. Bước 1 — EP

| Conditions | Valid Partitions | Tag | Invalid Partitions | Tag | Valid Boundaries | Tag |
|---|---|---|---|---|---|---|
| status | pending/paid/canceled | V1 | Ngoài tập, bỏ filter | X1 | N/A | N/A |
| from_date | Ngày thật đúng Y-m-d | V2 | Sai format/lịch, bỏ filter | X2 | N/A | N/A |
| to_date | Ngày thật đúng Y-m-d | V3 | Sai format/lịch, bỏ filter | X3 | N/A | N/A |
| search | Chuỗi sau trim, kể cả rỗng | V4 | Không có rule độ dài tại service | N/A | N/A | N/A |
| trường thiếu | Trở thành chuỗi rỗng | V5 | Không trả lỗi | N/A | N/A | N/A |

## 7.3. Bước 2 — Biên

Standard BVA N/A: không có khoảng ngày min/max cố định trong source. Ngày nhuận và cuối tháng là partition về tính hợp lệ lịch; không giả định from/to phải có thứ tự tăng.

## 7.4. Bước 3 — Thiết kế Test Case

### Phần A — Test case cho Standard BVA

Nguyên tắc: mỗi lần chỉ thay đổi **một biến đang kiểm thử**, các input còn lại giữ ở giá trị hợp lệ.

Giá trị nominal / hợp lệ dùng để giữ cố định:

```java
status    = paid
from_date = 2026-08-01
to_date   = 2026-08-31
search    = ABC123
Kết quả là mảng filter, không có status success/error.
```

**Standard BVA miền đóng: N/A** — Các input không có miền biên đóng được source quy định.

---

### Phần B — Test case phủ Equivalence Partitioning V/X

Ở mỗi test, chỉ cố tình làm sai một lớp không hợp lệ; các input khác giữ hợp lệ.

EP-09 truyền mảng rỗng; EP-10 kiểm tra trim đồng thời các trường hợp lệ. Kết quả ghi theo thứ tự `[status, from_date, to_date, search]`, không phải response success/error.

| STT   | Test case                             | status     | from_date        | to_date          | search       | Kết quả mong đợi                                                      | Tag    |
| ----- | ------------------------------------- | ---------- | ---------------- | ---------------- | ------------ | --------------------------------------------------------------------- | ------ |
| EP-01 | Tất cả filter hợp lệ                  | paid       | 2026-08-01       | 2026-08-31       | ABC123       | **Kết quả chuẩn hóa** – [paid,2026-08-01,2026-08-31,ABC123]           | V1–V4  |
| EP-02 | Status ngoài tập hợp lệ               | invalid    | 2026-08-01       | 2026-08-31       | ABC123       | **Kết quả chuẩn hóa** – ['',2026-08-01,2026-08-31,ABC123]             | X1     |
| EP-03 | Ngày bắt đầu không tồn tại trong lịch | paid       | 2026-02-30       | 2026-08-31       | ABC123       | **Kết quả chuẩn hóa** – [paid,'',2026-08-31,ABC123]                   | X2     |
| EP-04 | Ngày kết thúc sai định dạng           | paid       | 2026-08-01       | abc              | ABC123       | **Kết quả chuẩn hóa** – [paid,2026-08-01,'',ABC123]                   | X3     |
| EP-05 | Filter trạng thái pending             | pending    | 2026-08-01       | 2026-08-31       | ABC123       | **Kết quả chuẩn hóa** – [pending,2026-08-01,2026-08-31,ABC123]        | V1     |
| EP-06 | Filter trạng thái canceled            | canceled   | 2026-08-01       | 2026-08-31       | ABC123       | **Kết quả chuẩn hóa** – [canceled,2026-08-01,2026-08-31,ABC123]       | V1     |
| EP-07 | Ngày nhuận hợp lệ                     | paid       | 2024-02-29       | 2024-03-01       | ABC123       | **Kết quả chuẩn hóa** – [paid,2024-02-29,2024-03-01,ABC123]           | V2, V3 |
| EP-08 | Ngày bắt đầu sau ngày kết thúc        | paid       | 2026-08-31       | 2026-08-01       | ABC123       | **Kết quả chuẩn hóa** – Giữ nguyên cả hai ngày, không tự đảo hoặc xóa | V2, V3 |
| EP-09 | Thiếu toàn bộ trường filter           | thiếu      | thiếu            | thiếu            | thiếu        | **Kết quả chuẩn hóa** – ['','','','']                                 | V5     |
| EP-10 | Trim khoảng trắng các filter          | `' paid '` | `' 2026-08-01 '` | `' 2026-08-31 '` | `' ABC123 '` | **Kết quả chuẩn hóa** – [paid,2026-08-01,2026-08-31,ABC123]           | V1–V4  |

### Tổng hợp method `normalizeAdminFilters`

| Nhóm                         | Số TC     | Phạm vi phủ                              |
| ---------------------------- | --------- | ---------------------------------------- |
| **Standard BVA**             | 0 (N/A)   | Không có miền đóng phù hợp               |
| **Equivalence Partitioning** | 10        | **V1–V5, X1–X3**                         |
| **Tổng theo bảng EP**        | **10 TC** | **Phủ toàn bộ tag thiết kế trong scope** |

Tổng **10 EP**.

## 7.5. Mapping automation hiện hành

`backend/tests/Services/BookingAssignmentTest.php::testAssignment` đọc trực tiếp từng dòng trong mục 7.4 bằng `AssignmentCases`. Dataset được định danh `normalizeAdminFilters/ID`; kiểm tra đầy đủ input, kết quả, thông báo và lời gọi model tương ứng. **10/10 PASS** trong lần chạy chung 2026-09-15.


# 8. RÀ SOÁT AUTOMATION, ROUTE VÀ WHITE-BOX — GHI NHẬN TRƯỚC LẦN ĐỒNG BỘ

## 8.1. Bảng tổng thiết kế

| Method | Standard BVA đóng | Ngưỡng thời gian TB | EP | Tổng dòng |
|---|---:|---:|---:|---:|
| processBooking | 0 (N/A) | 3 | 17 | 20 |
| cancelBooking | 0 (N/A) | 3 | 9 | 12 |
| updateAdminBookingStatus | 0 (N/A) | 0 | 10 | 10 |
| normalizeAdminFilters | 0 (N/A) | 0 | 10 | 10 |
| Tổng | 0 | **6** | **46** | **52** |

52 là số dòng thiết kế mới. 53 là số test methods của suite hiện có bao gồm các method ngoài bốn method này. Hai số không có quan hệ PASS/total với nhau.

## 8.2. Các nhánh fault injection ngoài 52 dòng

| ID | Method / fixture lỗi | Expected cần assert | Test hiện có |
|---|---|---|---|
| WB-01 | process: createBooking=false | rollback, không commit/tạo vé; error Có lỗi xảy ra khi đặt vé. | PASS — BookingAssignmentTest::testFault |
| WB-02 | process: createMany=false | rollback booking/vé; cùng error tổng quát | PASS — BookingAssignmentTest::testFault |
| WB-03 | process: model trả đủ count nhưng không có ID đang tìm | error Ghế không hợp lệ.; không transaction | PASS — BookingAssignmentTest::testFault |
| WB-04 | cancel: cancelBooking=false, getError='fixture error' | rollback; error Loi khi huy booking: fixture error | PASS — BookingAssignmentTest::testFault |
| WB-05 | update status: updateBookingStatus=false | rollback; error Lỗi khi cập nhật trạng thái booking: fixture error | PASS — BookingAssignmentTest::testFault |
| WB-06 | update status: updateTicketsStatusByBooking=false | rollback; error Lỗi khi cập nhật trạng thái vé: fixture error | PASS — BookingAssignmentTest::testFault |

WB-03 là dữ liệu mock bất nhất để kiểm tra guard phòng thủ, không phải partition input thông thường. Source gọi beginTransaction trước try và chỉ catch Exception; không tuyên bố bắt mọi Throwable hoặc rollback mọi lỗi từ mọi vị trí.

Ngoài ra cần regression cho thời gian parse=false, ID ghế được intval, payment sai chữ hoa, booking canceled khôi phục sang pending, thiếu phụ phí mặc định 0. Chưa tính vào 52 dòng hoặc sáu WB trên. Service không đặt rule từ chối giá âm; không đưa business rule mong muốn thành expected hiện tại.

## 8.3. Sai lệch route Postman — không dùng làm evidence service

`generate-postman.js` sinh những URL dưới đây, nhưng `backend/api.php` không có handler validation Booking tương ứng gọi BookingService:

| URL sinh bởi generator | Luồng thực tế đọc từ api.php |
|---|---|
| POST /bookings/validate | Bị nhánh POST bookings bắt vì chưa có segments[2]; gọi BookingModel::createBooking trực tiếp nếu đủ input |
| GET /bookings/validate-user | Bị nhánh chi tiết booking bắt, ép chuỗi validate-user thành ID 0 |
| POST /bookings/validate-cancel | Bị nhánh tạo booking bắt; không gọi cancelBooking service |
| GET /admin/bookings/validate-detail | Rơi vào danh sách admin GET; không phải detail service |
| POST /admin/bookings/validate-status | Rơi vào nhánh mô phỏng ticket_quantity cuối file |
| DELETE /admin/bookings/validate-delete | Rơi vào nhánh mô phỏng ticket_quantity cuối file |
| GET /bookings/validate-total-spent | Bị nhánh chi tiết booking bắt; không gọi getTotalSpentByUser service |

Nhánh mô phỏng `ticket_quantity=1..10` không phải rule của processBooking. Output `confirmed` trong nhánh này cũng không thuộc tập pending/paid/canceled của updateAdminBookingStatus.

Vì vậy **39 case trong folder BVA - Booking không tương đương 39 service case chạy qua HTTP**. Không chạy collection này để lấy evidence hủy/đặt vé service trước khi sửa mapping route; một số request có thể ghi booking theo luồng khác. Báo cáo này ghi nhận sai lệch, không sửa API trong yêu cầu soạn tài liệu.

Một số test PHPUnit có tên “AcceptsMinimumPositive...”/“HandlesMinimumPositive...” cố tình mock lookup=null và expected error/null; Postman lại expected success cho ID dương. Đây là khác biệt fixture/tầng xử lý, không thể đối chiếu chỉ bằng giá trị ID. Các test có tên MinPlusTwo nhưng input=2 thực chất là min+1 khi min=1.

## 8.4. Các method/test còn lại đã rà soát

- getUserBookings: guard <=0 và chuyển tiếp array; có test -1,0,1,2,10. Output không có `status`.
- getAdminBookingDetail: có test ID guard/lookup null; chưa có service happy path trả cả booking và tickets.
- getTotalSpentByUser: có guard 0 và chuyển tiếp tổng; số tiền không phải input BVA.
- deleteAdminBooking: test ID guard/lookup null; chưa có happy path hoặc rollback service. Model delete dùng cascade tickets theo schema.
- getAdminBookings: service gọi normalizeAdminFilters; model tests không thay cho test chứng minh filter được truyền đúng từ service.
- getAdminBookingStats: chưa có test service riêng; model test assert keys.
- BookingModelTest có 18 test. `testTransactionMethods` gọi begin/rollback rồi assertTrue(true), chưa chứng minh dữ liệu thật rollback. `testHasSeatConflictWhenRestoringReturnsBool` chỉ chứng minh kiểu, chưa chứng minh logic conflict.
- TicketModel::isSeatBooked coi status khác canceled là đã đặt; createMany tạo vé booked. Chưa có evidence concurrent booking/locking trong suite service mock; không tuyên bố đã bảo đảm không đặt trùng dưới tải đồng thời.

# 9. KẾT QUẢ THỰC THI VÀ KẾT LUẬN — GHI NHẬN TRƯỚC LẦN ĐỒNG BỘ

## 9.1. Kết quả chạy lại thật

Từ thư mục backend, đã chạy trong lần soạn báo cáo này:

```powershell
php vendor/bin/phpunit tests/Services/BookingServiceTest.php --no-coverage --do-not-cache-result
```

Output:

```text
PHPUnit 9.6.35
53 / 53 (100%)
OK (53 tests, 116 assertions)
```

Suite dùng mock model, không kết nối MySQL. Đây là **test pass rate của suite hiện có**, không phải 100% coverage toàn module hay 52/52 case thiết kế mới.

Không chạy BookingModelTest/HTTP/Newman hoặc đo lại Xdebug trong lần này. Chỉ sử dụng ảnh/report cũ khi xác định được đúng source, suite, fixture và thời điểm; không đưa số Seat coverage vào Booking.

## 9.2. Điều kiện triển khai các case còn thiếu

Case transaction cần model doubles có expectation call order/commit/rollback và integration DB riêng kiểm tra dữ liệu cả booking lẫn tickets. Kiểm soát clock cho sáu TB; không dùng suất seed theo ngày cố định. Các case canceled restore phải tạo booking khác giữ cùng seat/showtime để kiểm chứng xung đột SQL.

Fixture từng case phải độc lập và cleanup dù test thất bại. Service mock không kiểm tra FK user có thật; integration cần user thật. Thiết kế mong muốn bổ sung (chống concurrency, từ chối ngày sai, kiểm tra user tồn tại) phải được tách khỏi hành vi hiện có.

**Kết luận:** Hoàn tất bốn chuỗi phân tích, 52 dòng thiết kế chính và sáu fault-injection case bổ sung. Suite hiện tại chạy 53/53 PASS; các khoảng trống về thời gian, transaction, khôi phục và route HTTP được ghi riêng để triển khai tiếp mà không làm sai số liệu báo cáo.

## Cập nhật phạm vi HTTP và clock

`currentTime()` là method private thứ 13 của service. Constructor nhận clock tùy chọn; runtime mặc định dùng thời gian thực.

Collection hiện có 39 request Booking đến `/bookings/validate-assignment`: 39/39 PASS. Route chỉ mô phỏng kiểm tra input và không gọi luồng giao dịch BookingService; không dùng số này để thay cho 52 case service hoặc 6 WB. Các route validate cũ ở bảng lịch sử không dùng trong lần chạy hiện hành.
