# 07 - PHPUnit Mapping & Execution Evidence: Seat Module

## 1. Mục tiêu

Sau khi hoàn thành Scope → Business Rules → Equivalence Partition → BVA → Test Case Design → White-box, bước này kiểm tra rằng mỗi test case đã thiết kế có PHPUnit method tương ứng và lưu execution evidence.

```text
Module    : Seat
Function  : validateSeatInput()
Test file : backend/tests/Services/SeatServiceTest.php
PHPUnit   : 9.6.35
```

## 2. PHPUnit Mapping Table

| TC ID | PHPUnit File | Test Method | Input | Expected | Tag | Match |
|---|---|---|---|---|---|---|
| `TC-SEAT-BVA-01` | `SeatServiceTest.php` | `testValidateSeatNumberBelowMinimum()` | `seat_number=0` | `error` | `SEAT-X1`, `SEAT-R1` | **YES** |
| `TC-SEAT-BVA-02` | `SeatServiceTest.php` | `testValidateSeatNumberAtMinimum()` | `seat_number=1` | `success` | `SEAT-V1`, `SEAT-B1` | **YES** |
| `TC-SEAT-BVA-03` | `SeatServiceTest.php` | `testValidateSeatNumberMinPlusOne()` | `seat_number=2` | `success` | `SEAT-B2` | **YES** |
| `TC-SEAT-BVA-07` | `SeatServiceTest.php` | `testValidateSeatNumberAtNominal()` | `seat_number=6` | `success` | `SEAT-B3`, `FUNC-HAPPY` | **YES** |
| `TC-SEAT-BVA-04` | `SeatServiceTest.php` | `testValidateSeatNumberMaxMinusOne()` | `seat_number=11` | `success` | `SEAT-B4` | **YES** |
| `TC-SEAT-BVA-05` | `SeatServiceTest.php` | `testValidateSeatNumberAtMaximum()` | `seat_number=12` | `success` | `SEAT-B5` | **YES** |
| `TC-SEAT-BVA-06` | `SeatServiceTest.php` | `testValidateSeatNumberAboveMaximum()` | `seat_number=13` | `error` | `SEAT-X2`, `SEAT-R2` | **YES** |
| `TC-SEAT-EP-01` | `SeatServiceTest.php` | `testValidateSeatRejectsInvalidRow()` | `seat_row=I` | `error` | `SEAT-X3` | **YES** |
| `TC-SEAT-EP-02` | `SeatServiceTest.php` | `testValidateSeatRejectsInvalidRoom()` | `room_id=0` | `error` | `SEAT-X4A` | **YES** |
| `TC-SEAT-EP-03` | `SeatServiceTest.php` | `testValidateSeatRejectsInvalidSeatType()` | `seat_type_id=0` | `error` | `SEAT-X5A` | **YES** |
| `TC-SEAT-WB-01` | `SeatServiceTest.php` | `testValidateSeatRejectsNonExistingRoom()` | `room_id=999999` | `error` | `SEAT-X4B` | **YES** |
| `TC-SEAT-WB-02` | `SeatServiceTest.php` | `testValidateSeatRejectsNonExistingSeatType()` | `seat_type_id=999999` | `error` | `SEAT-X5B` | **YES** |

```text
Mapping completeness = 12 / 12 = 100%
```

> Đây là mapping completeness, không phải code coverage.

## 3. BVA Mapping

### Standard BVA

| Boundary | Value | TC ID | PHPUnit Method | Expected |
|---|---:|---|---|---|
| MIN | 1 | `TC-SEAT-BVA-02` | `testValidateSeatNumberAtMinimum()` | success |
| MIN + 1 | 2 | `TC-SEAT-BVA-03` | `testValidateSeatNumberMinPlusOne()` | success |
| NOMINAL | 6 | `TC-SEAT-BVA-07` | `testValidateSeatNumberAtNominal()` | success |
| MAX - 1 | 11 | `TC-SEAT-BVA-04` | `testValidateSeatNumberMaxMinusOne()` | success |
| MAX | 12 | `TC-SEAT-BVA-05` | `testValidateSeatNumberAtMaximum()` | success |

### Robustness

| Boundary | Value | TC ID | PHPUnit Method | Expected |
|---|---:|---|---|---|
| MIN - 1 | 0 | `TC-SEAT-BVA-01` | `testValidateSeatNumberBelowMinimum()` | error |
| MAX + 1 | 13 | `TC-SEAT-BVA-06` | `testValidateSeatNumberAboveMaximum()` | error |

## 4. Supporting Validation Mapping

| Validation | Input | PHPUnit Method | White-box Decision |
|---|---|---|---|
| Invalid row | `seat_row=I` | `testValidateSeatRejectsInvalidRow()` | D2 |
| Invalid room | `room_id=0` | `testValidateSeatRejectsInvalidRoom()` | D1a |
| Room không tồn tại | `room_id=999999` | `testValidateSeatRejectsNonExistingRoom()` | D1b |
| Invalid seat type | `seat_type_id=0` | `testValidateSeatRejectsInvalidSeatType()` | D4a |
| Seat type không tồn tại | `seat_type_id=999999` | `testValidateSeatRejectsNonExistingSeatType()` | D4b |

## 5. Lệnh chạy

```powershell
cd C:\xampp\htdocs\movie-ticket-booking\backend

C:\xampp\php\php.exe vendor\bin\phpunit tests\Services\SeatServiceTest.php --filter ValidateSeat --testdox
```

## 6. Actual Execution Evidence

Kết quả đã chạy thực tế:

```text
PHPUnit 9.6.35 by Sebastian Bergmann and contributors.

Warning: No code coverage driver available

Seat Service (Tests\Services\SeatService)
 ✔ Validate seat number below minimum
 ✔ Validate seat number at minimum
 ✔ Validate seat number min plus one
 ✔ Validate seat number at nominal
 ✔ Validate seat number max minus one
 ✔ Validate seat number at maximum
 ✔ Validate seat number above maximum
 ✔ Validate seat rejects invalid row
 ✔ Validate seat rejects invalid room
 ✔ Validate seat rejects invalid seat type
 ✔ Validate seat rejects non existing room
 ✔ Validate seat rejects non existing seat type

Time: 00:00.023, Memory: 6.00 MB

OK (12 tests, 12 assertions)
```

## 7. Execution Summary

| Metric | Result |
|---|---:|
| PHPUnit Version | `9.6.35` |
| Tests Executed | `12` |
| Assertions | `12` |
| Passed | `12` |
| Failed | `0` |
| Errors | `0` |
| Test Pass Rate | `100%` |
| PHPUnit Mapping | `12/12` |
| Mapping Completeness | `100%` |

```text
Pass Rate = 12 / 12 × 100% = 100%
```

## 8. Coverage Warning

Output có:

```text
Warning: No code coverage driver available
```

Điều này chỉ có nghĩa môi trường chưa bật Xdebug/PCOV để đo tool-based coverage.

Không được suy ra:

```text
12/12 PASS = 100% code coverage
```

Vì:

```text
Test Pass Rate ≠ Code Coverage
```

## 9. Traceability

| TC ID | EP | BVA | PHPUnit | Execution | Result |
|---|---|---|---|---|---|
| TC-SEAT-BVA-01 | YES | Robustness | YES | YES | PASS |
| TC-SEAT-BVA-02 | YES | Standard | YES | YES | PASS |
| TC-SEAT-BVA-03 | YES | Standard | YES | YES | PASS |
| TC-SEAT-BVA-07 | YES | Standard | YES | YES | PASS |
| TC-SEAT-BVA-04 | YES | Standard | YES | YES | PASS |
| TC-SEAT-BVA-05 | YES | Standard | YES | YES | PASS |
| TC-SEAT-BVA-06 | YES | Robustness | YES | YES | PASS |
| TC-SEAT-EP-01 | YES | N/A | YES | YES | PASS |
| TC-SEAT-EP-02 | YES | N/A | YES | YES | PASS |
| TC-SEAT-EP-03 | YES | N/A | YES | YES | PASS |
| TC-SEAT-WB-01 | YES | N/A | YES | YES | PASS |
| TC-SEAT-WB-02 | YES | N/A | YES | YES | PASS |

## 10. Kết luận

```text
12 test case thiết kế
12 PHPUnit methods tương ứng
12 tests executed
12 tests PASS
12 assertions
0 failures
0 errors
```

Kết luận:

```text
PHPUnit Mapping Completeness = 100%
Validation Test Pass Rate    = 100%
```

Bước tiếp theo:

```text
08 - Postman / Newman Mapping & Execution
```
