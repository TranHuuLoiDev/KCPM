# BÁO CÁO KIỂM THỬ MODULE SEAT

**Họ và tên:** Lương Quốc An  
**MSSV:** 083205006374  
**Project:** Movie Ticket Booking  
**Module thực hiện:** Seat  
**Nội dung:** Equivalence Partitioning, Boundary Value Analysis, PHPUnit, Postman/Newman, White-box và Coverage  

---

# MỤC LỤC

1. Giới thiệu
2. Tổng quan module Seat
3. Phạm vi kiểm thử
4. Phân tích yêu cầu và Business Rule
5. Phân hoạch lớp tương đương
6. Phân tích giá trị biên
7. Thiết kế Test Case
8. Triển khai kiểm thử tự động
9. Kiểm thử hộp trắng
10. Kết quả thực nghiệm
11. Phân tích Coverage
12. Evidence và Traceability
13. Đánh giá kết quả
14. Kết luận
15. Phụ lục

---

# 1. GIỚI THIỆU

## 1.1. Mục đích báo cáo

Báo cáo trình bày quá trình kiểm thử module **Seat** trong hệ thống **Movie Ticket Booking**.  
Mục tiêu chính là kiểm tra tính đúng đắn của chức năng validation dữ liệu ghế, đặc biệt tập trung vào trường `seat_number` có miền giá trị hợp lệ rõ ràng từ `1` đến `12`.

Các kỹ thuật được sử dụng gồm:

- Equivalence Partitioning (EP).
- Standard Boundary Value Analysis.
- Robustness Boundary Value Analysis.
- PHPUnit.
- Postman/Newman.
- White-box Decision Analysis.
- Xdebug Code Coverage.

## 1.2. Mục tiêu kiểm thử

Quá trình kiểm thử hướng tới các mục tiêu:

- Xác định đúng miền dữ liệu hợp lệ và không hợp lệ.
- Kiểm tra đầy đủ các giá trị tại và xung quanh biên.
- Kiểm tra các điều kiện validation quan trọng trong source code.
- Tự động hóa test bằng PHPUnit và Newman.
- Đo coverage bằng Xdebug.
- Xây dựng evidence phục vụ báo cáo và demo.

---

# 2. TỔNG QUAN MODULE SEAT

## 2.1. Chức năng chính

Module Seat chịu trách nhiệm quản lý ghế trong phòng chiếu, bao gồm:

- Thêm ghế.
- Cập nhật ghế.
- Xóa ghế.
- Tạo ghế hàng loạt.
- Xóa ghế hàng loạt.
- Lấy danh sách ghế.
- Lấy sơ đồ ghế.
- Kiểm tra dữ liệu ghế trước khi xử lý.

Trong phạm vi báo cáo này, phần được kiểm thử tập trung là:

```text
validateSeatInput()
validateBase()
```

File production:

```text
backend/app/Services/SeatService.php
```

File test:

```text
backend/tests/Services/SeatServiceTest.php
```

API sử dụng cho black-box automation:

```text
POST /seats/validate
```

---

# 3. PHẠM VI KIỂM THỬ

## 3.1. Scope chính

Scope chính:

```text
Module   : Seat
Service  : SeatService
Function : validateSeatInput()
Internal : validateBase()
```

Input được kiểm tra:

| Input | Ý nghĩa | Điều kiện hợp lệ |
|---|---|---|
| `room_id` | ID phòng chiếu | `> 0` và phải tồn tại |
| `seat_row` | Hàng ghế | Một ký tự từ `A` đến `H` |
| `seat_number` | Số ghế | Từ `1` đến `12` |
| `seat_type_id` | ID loại ghế | `> 0` và phải tồn tại |

## 3.2. Input BVA chính

Input phù hợp nhất để áp dụng Boundary Value Analysis là:

```text
seat_number
```

Vì source code quy định miền đóng:

```text
1 <= seat_number <= 12
```

Trong khi `room_id` và `seat_type_id` không có upper bound cụ thể nên không tự đặt max giả.

---

# 4. PHÂN TÍCH YÊU CẦU VÀ BUSINESS RULE

## 4.1. Business Rule

Các rule được rút ra trực tiếp từ source:

| Rule ID | Điều kiện | Kết quả nếu vi phạm |
|---|---|---|
| BR-S01 | `room_id > 0` và phòng tồn tại | `error` |
| BR-S02 | `seat_row` phải thuộc `A..H` | `error` |
| BR-S03 | `1 <= seat_number <= 12` | `error` |
| BR-S04 | `seat_type_id > 0` và loại ghế tồn tại | `error` |
| BR-S05 | Tất cả điều kiện hợp lệ | `success` |

## 4.2. Logic tổng quát

```text
Valid =
(room_id > 0 AND room tồn tại)
AND
(seat_row thuộc A..H)
AND
(1 <= seat_number <= 12)
AND
(seat_type_id > 0 AND seat type tồn tại)
```

Kết quả thành công:

```text
status = success
message = Dữ liệu ghế hợp lệ!
```

---

# 5. PHÂN HOẠCH LỚP TƯƠNG ĐƯƠNG

## 5.1. Equivalence Partitioning

| Input | Valid Partition | Tag | Invalid Partition | Tag |
|---|---|---|---|---|
| `seat_number` | `1..12` | `SEAT-V1` | `<1` | `SEAT-X1` |
| `seat_number` | `1..12` | `SEAT-V1` | `>12` | `SEAT-X2` |
| `seat_row` | `A..H` | `SEAT-V2` | Ngoài `A..H` | `SEAT-X3` |
| `room_id` | `>0` và tồn tại | `SEAT-V3` | `<=0` | `SEAT-X4A` |
| `room_id` | `>0` và tồn tại | `SEAT-V3` | `>0` nhưng không tồn tại | `SEAT-X4B` |
| `seat_type_id` | `>0` và tồn tại | `SEAT-V4` | `<=0` | `SEAT-X5A` |
| `seat_type_id` | `>0` và tồn tại | `SEAT-V4` | `>0` nhưng không tồn tại | `SEAT-X5B` |

## 5.2. Giá trị đại diện

| Tag | Representative Value |
|---|---|
| `SEAT-V1` | `6` |
| `SEAT-X1` | `0` |
| `SEAT-X2` | `13` |
| `SEAT-V2` | `A` |
| `SEAT-X3` | `I` |
| `SEAT-V3` | `1` |
| `SEAT-X4A` | `0` |
| `SEAT-X4B` | `999999` |
| `SEAT-V4` | `1` |
| `SEAT-X5A` | `0` |
| `SEAT-X5B` | `999999` |

---

# 6. PHÂN TÍCH GIÁ TRỊ BIÊN

## 6.1. Standard BVA

Với:

```text
MIN = 1
MAX = 12
```

Các giá trị Standard BVA:

| Boundary | Value | Tag | Expected |
|---|---:|---|---|
| `min` | 1 | `SEAT-B1` | success |
| `min+` | 2 | `SEAT-B2` | success |
| `nominal` | 6 | `SEAT-B3` | success |
| `max-` | 11 | `SEAT-B4` | success |
| `max` | 12 | `SEAT-B5` | success |

Tập Standard BVA:

```text
{1, 2, 6, 11, 12}
```

## 6.2. Robustness BVA

Các giá trị ngoài biên:

| Boundary | Value | Tag | Partition | Expected |
|---|---:|---|---|---|
| `min-` | 0 | `SEAT-R1` | `SEAT-X1` | error |
| `max+` | 13 | `SEAT-R2` | `SEAT-X2` | error |

Tập Robustness:

```text
{0, 13}
```

## 6.3. Nguyên tắc giữ biến còn lại ở nominal

Khi test `seat_number`, giữ:

```text
room_id      = 1
seat_row     = A
seat_type_id = 1
is_active    = true
```

---

# 7. THIẾT KẾ TEST CASE

## 7.1. Test case BVA

| TC ID | Input | Boundary | Expected | Tag |
|---|---:|---|---|---|
| `TC-SEAT-BVA-01` | `seat_number=0` | min- | error | `SEAT-X1`, `SEAT-R1` |
| `TC-SEAT-BVA-02` | `seat_number=1` | min | success | `SEAT-V1`, `SEAT-B1` |
| `TC-SEAT-BVA-03` | `seat_number=2` | min+ | success | `SEAT-B2` |
| `TC-SEAT-BVA-07` | `seat_number=6` | nominal | success | `SEAT-B3` |
| `TC-SEAT-BVA-04` | `seat_number=11` | max- | success | `SEAT-B4` |
| `TC-SEAT-BVA-05` | `seat_number=12` | max | success | `SEAT-B5` |
| `TC-SEAT-BVA-06` | `seat_number=13` | max+ | error | `SEAT-X2`, `SEAT-R2` |

## 7.2. Test case EP và supporting validation

| TC ID | Input thay đổi | Expected | Tag |
|---|---|---|---|
| `TC-SEAT-EP-01` | `seat_row=I` | error | `SEAT-X3` |
| `TC-SEAT-EP-02` | `room_id=0` | error | `SEAT-X4A` |
| `TC-SEAT-EP-03` | `seat_type_id=0` | error | `SEAT-X5A` |
| `TC-SEAT-WB-01` | `room_id=999999` | error | `SEAT-X4B` |
| `TC-SEAT-WB-02` | `seat_type_id=999999` | error | `SEAT-X5B` |

## 7.3. Tổng số test case thiết kế

```text
Standard BVA      : 5
Robustness BVA    : 2
EP / Supporting   : 5
---------------------
Tổng              : 12 test case
```

---

# 8. TRIỂN KHAI KIỂM THỬ TỰ ĐỘNG

## 8.1. PHPUnit

Framework:

```text
PHPUnit 9.6.35
```

Lệnh chạy validation test:

```powershell
cd C:\xampp\htdocs\movie-ticket-booking\backend

C:\xampp\php\php.exe vendor\bin\phpunit `
  tests\Services\SeatServiceTest.php `
  --filter ValidateSeat `
  --testdox
```

Kết quả:

```text
OK (12 tests, 12 assertions)
```

Pass rate:

```text
12/12 = 100%
```

## 8.2. Newman / Postman

Endpoint:

```text
POST /seats/validate
```

Lệnh chạy:

```powershell
cd C:\xampp\htdocs\movie-ticket-booking

$env:BASE_URL="http://localhost/movie-ticket-booking/backend/api.php"

node tests\automation\run-bva-and-log.js "BVA - Seat"
```

Kết quả:

```text
Total    : 7
Passed   : 7
Failed   : 0
Pass Rate: 100%
```

Các giá trị đã chạy:

```text
0, 1, 2, 6, 11, 12, 13
```

---

# 9. KIỂM THỬ HỘP TRẮNG

## 9.1. Decision Matrix

Các decision trong scope:

```text
D1a: room_id <= 0
D1b: room không tồn tại
D2 : seat_row invalid
D3a: seat_number < 1
D3b: seat_number > 12
D4a: seat_type_id <= 0
D4b: seat type không tồn tại
D5 : validation có error?
```

Tổng số outcome:

```text
8 decisions x 2 outcomes = 16 outcome slots
```

Mapping thực tế:

```text
16/16 covered
Manual Decision Coverage = 100%
```

---

# 10. KẾT QUẢ THỰC NGHIỆM

## 10.1. PHPUnit validation

```text
12 tests
12 assertions
12 passed
0 failed
```

Kết quả:

```text
Validation Test Pass Rate = 100%
```

## 10.2. Full SeatServiceTest

Lệnh chạy full test kèm coverage:

```powershell
$env:XDEBUG_MODE="coverage"

C:\xampp\php\php.exe vendor\bin\phpunit `
  tests\Services\SeatServiceTest.php `
  --coverage-text `
  --coverage-clover coverage-seat.xml
```

Kết quả:

```text
25 / 25 (100%)
OK (25 tests, 29 assertions)
```

## 10.3. Newman

```text
7/7 PASS
0 FAIL
Pass Rate = 100%
```

---

# 11. PHÂN TÍCH COVERAGE

## 11.1. Tool-based Coverage

Công cụ:

```text
PHP 8.2.12
Xdebug 3.5.3
PHPUnit 9.6.35
```

Coverage riêng `SeatService`:

```text
Methods: 45.00% (9/20)
Lines:   25.90% (43/166)
```

Overall project trong Seat-only run:

```text
Classes:  2.44% (1/41)
Methods:  5.48% (19/347)
Lines:    5.44% (125/2299)
```

Clover report:

```text
backend/coverage-seat.xml
```

## 11.2. Phân biệt các loại Coverage

### Test Pass Rate

```text
PHPUnit Validation: 12/12 = 100%
Newman BVA:          7/7 = 100%
```

### BVA Tag Coverage

```text
Standard BVA:   5/5 = 100%
Robustness BVA: 2/2 = 100%
```

### Manual Decision Coverage

```text
16/16 = 100%
```

### Tool-based Code Coverage

```text
SeatService Method Coverage = 45.00%
SeatService Line Coverage   = 25.90%
```

Các chỉ số trên có mẫu số khác nhau nên không được đồng nhất với nhau.

---

# 12. EVIDENCE VÀ TRACEABILITY

## 12.1. Evidence chính

Các bằng chứng đã thu thập:

- Git status sạch.
- PHPUnit validation `12 tests, 12 assertions`.
- Full SeatServiceTest `25/25`, `29 assertions`.
- Xdebug 3.5.3 hoạt động.
- Newman `7 Passed, 0 Failed, 100%`.
- `latest-bva-result.csv`.
- `latest-bva-result.json`.
- SeatService coverage `45.00% Methods / 25.90% Lines`.
- GitHub PR.
- SonarCloud Quality Gate passed.

## 12.2. Screenshot package

```text
docs/evidence/seat/
```

Đề xuất tên file:

```text
01_git_status_phpunit_validation.png
02_phpunit_coverage_run.png
03_xdebug_enabled.png
04_newman_bva_7_7.png
05_latest_bva_csv.png
06_seatservice_coverage.png
07_latest_bva_json.png
08_github_sonar_passed.png
```

## 12.3. Traceability

```text
Business Rule
→ Equivalence Partition
→ Boundary
→ Test Case
→ PHPUnit / Newman
→ Actual Result
→ Evidence
```

---

# 13. ĐÁNH GIÁ KẾT QUẢ

## 13.1. Điểm đạt được

Module Seat đã đạt:

```text
Validation PHPUnit       : 100%
Full SeatServiceTest     : 100%
Newman BVA               : 100%
Standard BVA Coverage    : 100%
Robustness Coverage      : 100%
Manual Decision Coverage : 100%
```

## 13.2. Hạn chế

Tool-based line coverage của toàn bộ class SeatService chỉ đạt:

```text
25.90%
```

Nguyên nhân là phạm vi bài tập tập trung vào validation, trong khi `SeatService` còn nhiều chức năng khác:

- addSeat
- updateSeat
- deleteSeat
- bulkDeleteSeats
- generateSeats
- quickAddSeat
- các hàm truy vấn khác

Các method này chưa phải trọng tâm của scope validation hiện tại.

## 13.3. Hướng cải thiện

Có thể mở rộng trong tương lai:

- Unit test cho `addSeat()`.
- Unit test cho `updateSeat()`.
- Unit test cho `deleteSeat()`.
- Test `generateSeats()`.
- Test `bulkDeleteSeats()`.
- Tăng Line Coverage và Method Coverage.
- Mở rộng Postman collection cho các EP supporting case.

---

# 14. KẾT LUẬN

Qua quá trình kiểm thử module Seat, bài làm đã áp dụng đầy đủ:

```text
Equivalence Partitioning
Standard Boundary Value Analysis
Robustness Boundary Value Analysis
Test Case Design
PHPUnit
Postman / Newman
White-box Decision Analysis
Xdebug Code Coverage
Evidence
```

Kết quả chính:

| Metric | Result |
|---|---:|
| Validation PHPUnit Pass Rate | **100% (12/12)** |
| Full SeatServiceTest Pass Rate | **100% (25/25)** |
| Assertions | **29** |
| Newman BVA Pass Rate | **100% (7/7)** |
| Standard BVA Coverage | **100% (5/5)** |
| Robustness Coverage | **100% (2/2)** |
| Manual Decision Coverage | **100% (16/16)** |
| SeatService Method Coverage | **45.00% (9/20)** |
| SeatService Line Coverage | **25.90% (43/166)** |

Có thể kết luận phần kiểm thử validation của module Seat đã hoàn thành đúng scope đặt ra, có đầy đủ test design, automation, coverage và evidence.

---

# 15. PHỤ LỤC

## 15.1. File source chính

```text
backend/app/Services/SeatService.php
backend/tests/Services/SeatServiceTest.php
```

## 15.2. File automation

```text
tests/bva/bva-cases.js
tests/automation/run-bva-and-log.js
tests/reports/latest-bva-result.json
tests/reports/latest-bva-result.csv
```

## 15.3. File coverage

```text
backend/coverage-seat.xml
```

## 15.4. Tài liệu Seat

```text
docs/testing/modules/seat/
├── 00_README.md
├── 01_scope.md
├── 02_business_rules.md
├── 03_equivalence_partition.md
├── 04_boundary_value_analysis.md
├── 05_test_case_design.md
├── 06_whitebox.md
├── 07_phpunit_mapping.md
├── 08_postman_newman.md
├── 09_coverage.md
└── 10_evidence.md
```
