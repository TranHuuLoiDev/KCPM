> **Phạm vi lịch sử (trước bản Assignment 75 case):** Nội dung và số liệu bên dưới mô tả bộ Seat cũ. Bản hiện hành gồm 18 validation + 25 generate + 32 bulkDelete, đã chạy PHPUnit 75/75, 307 assertions. Postman hiện có 18 validation case, chưa chạy lại; Xdebug bên dưới là evidence cũ. Xem [báo cáo hiện hành](083205006374_LuongQuocAn_BaoCao_Seat_Form_Assignment_Chot.md) và [mapping hiện hành](07_phpunit_mapping.md).

# 10 - Evidence & Final Cross-check: Seat Module

## 1. Evidence Summary

Module:

```text
Seat
```

Production scope:

```text
backend/app/Services/SeatService.php
validateSeatInput()
validateBase()
```

Main BVA rule:

```text
1 <= seat_number <= 12
```

---

## 2. PHPUnit Functional Evidence

Command:

```powershell
cd C:\xampp\htdocs\movie-ticket-booking\backend

C:\xampp\php\php.exe vendor\bin\phpunit tests\Services\SeatServiceTest.php --filter ValidateSeat --testdox
```

Validation execution:

```text
OK (12 tests, 12 assertions)
```

Result:

```text
12/12 PASS
Validation Test Pass Rate = 100%
```

---

## 3. Newman BVA Evidence

Command:

```powershell
cd C:\xampp\htdocs\movie-ticket-booking

$env:BASE_URL="http://localhost/movie-ticket-booking/backend/api.php"

node tests\automation\run-bva-and-log.js "BVA - Seat"
```

Actual output:

```text
[PASS] TC-SEAT-BVA-01 | seat_number=0  | Expected=error   | Actual=error
[PASS] TC-SEAT-BVA-02 | seat_number=1  | Expected=success | Actual=success
[PASS] TC-SEAT-BVA-03 | seat_number=2  | Expected=success | Actual=success
[PASS] TC-SEAT-BVA-07 | seat_number=6  | Expected=success | Actual=success
[PASS] TC-SEAT-BVA-04 | seat_number=11 | Expected=success | Actual=success
[PASS] TC-SEAT-BVA-05 | seat_number=12 | Expected=success | Actual=success
[PASS] TC-SEAT-BVA-06 | seat_number=13 | Expected=error   | Actual=error

Total    : 7
Passed   : 7
Failed   : 0
Pass Rate: 100%
```

Reports:

```text
tests/reports/bva-result-2026-09-15T09-02-33-692Z.json
tests/reports/bva-result-2026-09-15T09-02-33-692Z.csv
tests/reports/latest-bva-result.json
tests/reports/latest-bva-result.csv
```

---

## 4. Xdebug Installation Evidence

PHP CLI:

```text
PHP 8.2.12 (cli)
Zend Engine v4.2.12
with Xdebug v3.5.3
```

Extension check:

```text
xdebug
Xdebug
```

Environment:

```text
PHP      : 8.2.12
TYPE     : TS
ARCH     : x64
Compiler : Visual C++ 2019
Xdebug   : 3.5.3
```

Status:

```text
Xdebug installation = DONE
Coverage driver      = WORKING
```

---

## 5. PHPUnit + Xdebug Coverage Evidence

Command:

```powershell
cd C:\xampp\htdocs\movie-ticket-booking\backend

$env:XDEBUG_MODE="coverage"

C:\xampp\php\php.exe vendor\bin\phpunit `
  tests\Services\SeatServiceTest.php `
  --coverage-text `
  --coverage-clover coverage-seat.xml
```

Actual execution:

```text
25 / 25 (100%)

OK (25 tests, 29 assertions)

Generating code coverage report in Clover XML format ... done
```

---

## 6. Tool-based Coverage Result

Overall report:

```text
Classes:  2.44% (1/41)
Methods:  5.48% (19/347)
Lines:    5.44% (125/2299)
```

SeatService:

```text
App\Services\SeatService
Methods: 45.00% (9/20)
Lines:   25.90% (43/166)
```

Therefore:

```text
SeatService Method Coverage = 45.00%
SeatService Line Coverage   = 25.90%
```

Clover report:

```text
backend/coverage-seat.xml
```

---

## 7. Supporting Coverage

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
```

---

## 8. White-box Evidence

Manual decision matrix:

```text
D1a: room_id <= 0
D1b: room khÃ´ng tá»“n táº¡i
D2 : seat_row invalid
D3a: seat_number < 1
D3b: seat_number > 12
D4a: seat_type_id <= 0
D4b: seat type khÃ´ng tá»“n táº¡i
D5 : validation cÃ³ error?
```

Coverage:

```text
8 decisions
16 TRUE/FALSE outcomes
16/16 covered
Manual Decision Coverage = 100%
```

Important distinction:

```text
Manual Decision Coverage = 100%
SeatService Line Coverage = 25.90%
```

Hai metric khÃ¡c nhau, máº«u sá»‘ khÃ¡c nhau.

---

## 9. Final Metric Summary

| Metric | Result |
|---|---:|
| Validation PHPUnit Pass Rate | **100% (12/12)** |
| Full SeatServiceTest Pass Rate | **100% (25/25)** |
| Assertions in coverage run | **29** |
| Newman BVA Pass Rate | **100% (7/7)** |
| Standard BVA Coverage | **100% (5/5)** |
| Robustness Coverage | **100% (2/2)** |
| Manual Decision Coverage | **100% (16/16)** |
| PHPUnit Mapping Completeness | **100% (12/12)** |
| Postman BVA Mapping | **100% (7/7)** |
| SeatService Method Coverage | **45.00% (9/20)** |
| SeatService Line Coverage | **25.90% (43/166)** |
| Overall Project Line Coverage | **5.44% (125/2299)** |

---

## 10. Screenshot Evidence Checklist

NÃªn chá»¥p:

- [ ] `SeatService.php` Ä‘oáº¡n validation.
- [ ] PHPUnit validation `12 tests, 12 assertions`.
- [ ] Newman `7 Passed, 0 Failed, 100%`.
- [ ] Xdebug `php -v`.
- [ ] Coverage terminal cÃ³ `SeatService Methods 45.00% / Lines 25.90%`.
- [ ] `coverage-seat.xml` trong thÆ° má»¥c backend.
- [ ] `latest-bva-result.json`.
- [ ] `latest-bva-result.csv`.
- [ ] Git status trÆ°á»›c commit.
- [ ] Commit / PR sau khi push.

---

## 11. Git Evidence

Sau khi copy cÃ¡c tÃ i liá»‡u má»›i vÃ o repo:

```powershell
git status
```

Chá»‰ add file thuá»™c task Seat:

```powershell
git add docs/testing/modules/seat/06_whitebox.md
git add docs/testing/modules/seat/07_phpunit_mapping.md
git add docs/testing/modules/seat/08_postman_newman.md
git add docs/testing/modules/seat/09_coverage.md
git add docs/testing/modules/seat/10_evidence.md
```

CÃ³ thá»ƒ add Clover report náº¿u team muá»‘n giá»¯ coverage artifact:

```powershell
git add backend/coverage-seat.xml
```

KhÃ´ng dÃ¹ng `git add .` náº¿u cÃ²n file ngoÃ i scope.

---

## 12. Definition of Done

| Háº¡ng má»¥c | Tráº¡ng thÃ¡i |
|---|---|
| Scope | DONE |
| Business Rules | DONE |
| EP | DONE |
| Standard BVA | DONE |
| Robustness BVA | DONE |
| Test Case Design | DONE |
| PHPUnit Mapping | DONE |
| PHPUnit Execution | DONE |
| Postman/Newman Mapping | DONE |
| Newman Execution | DONE |
| White-box Decision Matrix | DONE |
| Manual Decision Coverage | DONE |
| Xdebug Coverage Driver | DONE |
| Tool-based SeatService Coverage | DONE |
| Clover Coverage Report | DONE |
| Screenshot Package | DONE |
| Git Commit | DONE |
| Pull Request | DONE |

---

## 13. Final Conclusion

Dá»¯ liá»‡u thá»±c táº¿ hiá»‡n táº¡i:

```text
Seat validation PHPUnit:
12/12 PASS

Full SeatServiceTest:
25/25 PASS
29 assertions

Seat BVA Newman:
7/7 PASS

Manual Decision Coverage:
16/16 = 100%

SeatService Tool-based Coverage:
Methods = 45.00%
Lines   = 25.90%

Overall Project Coverage from this Seat-only run:
Lines = 5.44%
```

Pháº§n testing ká»¹ thuáº­t Ä‘Ã£ cÃ³ Ä‘á»§ execution evidence.

## Final Status

```text
Screenshot Package = DONE
Git Commit         = DONE
Push Branch        = DONE
Pull Request       = DONE

SEAT TESTING PACKAGE = COMPLETED
---

## 14. GitHub / Pull Request Evidence

CÃ¡c thay Ä‘á»•i cá»§a module Seat Ä‘Ã£ Ä‘Æ°á»£c commit, push vÃ  merge vÃ o `main`.

| PR | Ná»™i dung | Tráº¡ng thÃ¡i |
|---|---|---|
| #50 | White-box decision matrix vÃ  coverage analysis | MERGED |
| #52 | PHPUnit, Newman, coverage vÃ  evidence | MERGED |
| #53 | Move Seat testing docs vÃ o `docs/testing/modules/seat/` | MERGED |

PR #53:

```text
docs(seat): move testing analysis into docs modules
Status: MERGED