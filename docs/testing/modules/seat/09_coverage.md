# 09 - Coverage Summary: Seat Module

## 1. Mục tiêu

Tổng hợp các loại coverage/metric theo đúng workplan và không trộn lẫn các khái niệm:

```text
1. Test Pass Rate
2. BVA Tag Coverage
3. Manual White-box Decision Coverage
4. Tool-based Code Coverage
```

---

## 2. PHPUnit Test Pass Rate

Lần chạy coverage thực tế:

```text
PHPUnit 9.6.35
25 / 25 tests (100%)
OK (25 tests, 29 assertions)
```

Suy ra:

```text
Passed = 25
Total  = 25

PHPUnit SeatServiceTest Pass Rate
= 25 / 25 × 100%
= 100%
```

> Lần chạy này là toàn bộ `SeatServiceTest.php`, nên số test là 25, không còn chỉ là 12 validation tests như lần chạy `--filter ValidateSeat`.

---

## 3. Newman BVA Pass Rate

Execution thực tế:

```text
Total    : 7
Passed   : 7
Failed   : 0
Pass Rate: 100%
```

Suy ra:

```text
Newman BVA Pass Rate
= 7 / 7 × 100%
= 100%
```

---

## 4. BVA Tag Coverage

Các BVA/Robustness tags:

```text
SEAT-B1 → MIN
SEAT-B2 → MIN + 1
SEAT-B3 → NOMINAL
SEAT-B4 → MAX - 1
SEAT-B5 → MAX
SEAT-R1 → MIN - 1
SEAT-R2 → MAX + 1
```

Coverage:

```text
Standard BVA = 5 / 5 = 100%
Robustness   = 2 / 2 = 100%
Total        = 7 / 7 = 100%
```

---

## 5. Manual White-box Decision Coverage

Decision matrix:

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

Có:

```text
8 decisions
16 TRUE/FALSE outcomes
```

Manual mapping:

```text
Covered Decision Outcomes = 16
Total Decision Outcomes   = 16

Manual Decision Coverage
= 16 / 16 × 100%
= 100%
```

> Đây là manual decision coverage, không phải Xdebug branch coverage.

---

## 6. Tool-based Code Coverage bằng Xdebug

Môi trường đã cài thành công:

```text
PHP 8.2.12
Xdebug 3.5.3
TS
x64
Visual C++ 2019
```

Xác nhận:

```text
with Xdebug v3.5.3
xdebug
Xdebug
```

Command:

```powershell
cd C:\xampp\htdocs\movie-ticket-booking\backend

$env:XDEBUG_MODE="coverage"

C:\xampp\php\php.exe vendor\bin\phpunit `
  tests\Services\SeatServiceTest.php `
  --coverage-text `
  --coverage-clover coverage-seat.xml
```

Execution:

```text
25 / 25 (100%)

OK (25 tests, 29 assertions)

Generating code coverage report in Clover XML format ... done
```

---

## 7. Overall Project Coverage Report

Do PHPUnit config đang include toàn bộ source `app`, report tổng thể có mẫu số là toàn project:

```text
Classes :  2.44% (1/41)
Methods :  5.48% (19/347)
Lines   :  5.44% (125/2299)
```

Các số này là **overall project coverage của lần chạy riêng SeatServiceTest.php**, không phải Seat-only coverage.

Do chỉ chạy test của Seat nên các controller/service/module khác phần lớn là 0%.

---

## 8. SeatService Tool-based Coverage

Phần quan trọng nhất cho module Seat:

```text
App\Services\SeatService

Methods: 45.00% (9/20)
Lines:   25.90% (43/166)
```

Đây là số tool-based coverage hợp lệ do PHPUnit + Xdebug sinh ra.

Kết luận:

```text
SeatService Method Coverage = 45.00%
SeatService Line Coverage   = 25.90%
```

---

## 9. Supporting Classes Coverage

Do `SeatServiceTest.php` gọi các model liên quan, coverage report còn ghi:

```text
App\Models\RoomModel
Methods: 27.27% (3/11)
Lines:   17.72% (14/79)

App\Models\SeatModel
Methods: 23.53% (4/17)
Lines:   28.57% (44/154)

App\Models\SeatTypeModel
Methods: 100.00% (3/3)
Lines:   100.00% (13/13)

App\Config\Database
Methods: 0.00% (0/1)
Lines:   91.67% (11/12)
```

Các class này là dependency/supporting coverage, không phải metric chính để kết luận cho SeatService.

---

## 10. Vì sao SeatService chỉ 25.90% line coverage?

`SeatService.php` có nhiều function ngoài phạm vi validation, ví dụ các chức năng CRUD/generate/get/display khác.

Trong khi phần phân tích BVA/EP chính của bài tập tập trung vào:

```text
validateSeatInput()
validateBase()
```

Do đó:

```text
Manual Decision Coverage của validation = 100%
```

nhưng:

```text
Line Coverage toàn SeatService class = 25.90%
```

Hai số này không mâu thuẫn vì mẫu số khác nhau.

---

## 11. Coverage Summary Table

| Metric | Value | Evidence |
|---|---:|---|
| PHPUnit SeatServiceTest Pass Rate | **100%** | `25/25 PASS` |
| PHPUnit Assertions | `29` | Execution output |
| Newman BVA Pass Rate | **100%** | `7/7 PASS` |
| Standard BVA Tag Coverage | **100%** | `5/5` |
| Robustness Tag Coverage | **100%** | `2/2` |
| Total BVA/Robustness Tag Coverage | **100%** | `7/7` |
| Manual Decision Coverage | **100%** | `16/16 outcomes` |
| PHPUnit Mapping Completeness | **100%** | `12/12` |
| Postman BVA Mapping | **100%** | `7/7 BVA cases` |
| SeatService Method Coverage | **45.00%** | Xdebug `9/20` |
| SeatService Line Coverage | **25.90%** | Xdebug `43/166` |
| Overall Project Method Coverage | **5.48%** | Xdebug `19/347` |
| Overall Project Line Coverage | **5.44%** | Xdebug `125/2299` |

---

## 12. Clover Report

Report đã được sinh:

```text
backend/coverage-seat.xml
```

Đây là evidence tool-based có thể dùng cho:

```text
PHPUnit/Xdebug coverage evidence
SonarCloud import nếu cấu hình scanner hỗ trợ Clover
CI coverage artifact
```

---

## 13. Kết luận

Các số liệu phải ghi đúng tên:

```text
PHPUnit Pass Rate             = 100%
Newman BVA Pass Rate          = 100%
BVA Tag Coverage              = 100%
Manual Decision Coverage      = 100%
SeatService Method Coverage   = 45.00%
SeatService Line Coverage     = 25.90%
Overall Project Line Coverage = 5.44%
```

Không được thay thế lẫn nhau.

Đối với báo cáo module Seat, nên ưu tiên nêu:

```text
SeatService Line Coverage   = 25.90%
SeatService Method Coverage = 45.00%
```

và giải thích rằng report tổng thể thấp vì PHPUnit đang tính mẫu số trên toàn bộ project trong khi chỉ chạy `SeatServiceTest.php`.
