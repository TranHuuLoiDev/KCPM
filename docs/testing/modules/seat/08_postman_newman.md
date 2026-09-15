# 08 - Postman / Newman Mapping & Execution Evidence: Seat Module

## 1. Mục tiêu

Dùng Newman để chạy Black-box automation cho 7 test case BVA của `seat_number` và lưu bằng chứng execution.

```text
Module     : Seat
Function   : validateSeatInput()
Endpoint   : POST /seats/validate
Collection : tests/postman/BVA_MovieBooking.postman_collection.json
Folder     : BVA - Seat
```

---

## 2. BVA Mapping

| TC ID | Boundary | Input | Expected |
|---|---|---:|---|
| `TC-SEAT-BVA-01` | MIN - 1 | 0 | error |
| `TC-SEAT-BVA-02` | MIN | 1 | success |
| `TC-SEAT-BVA-03` | MIN + 1 | 2 | success |
| `TC-SEAT-BVA-07` | NOMINAL | 6 | success |
| `TC-SEAT-BVA-04` | MAX - 1 | 11 | success |
| `TC-SEAT-BVA-05` | MAX | 12 | success |
| `TC-SEAT-BVA-06` | MAX + 1 | 13 | error |

Standard BVA:

```text
1, 2, 6, 11, 12
```

Robustness:

```text
0, 13
```

---

## 3. Lệnh chạy thực tế

```powershell
cd C:\xampp\htdocs\movie-ticket-booking

$env:BASE_URL="http://localhost/movie-ticket-booking/backend/api.php"

node tests\automation\run-bva-and-log.js "BVA - Seat"
```

---

## 4. Actual Execution Output

```text
BVA AUTOMATION - RESULT LOGGING
Folder : BVA - Seat

[PASS] TC-SEAT-BVA-01 | seat_number=0  | Expected=error   | Actual=error
[PASS] TC-SEAT-BVA-02 | seat_number=1  | Expected=success | Actual=success
[PASS] TC-SEAT-BVA-03 | seat_number=2  | Expected=success | Actual=success
[PASS] TC-SEAT-BVA-07 | seat_number=6  | Expected=success | Actual=success
[PASS] TC-SEAT-BVA-04 | seat_number=11 | Expected=success | Actual=success
[PASS] TC-SEAT-BVA-05 | seat_number=12 | Expected=success | Actual=success
[PASS] TC-SEAT-BVA-06 | seat_number=13 | Expected=error   | Actual=error

BVA TEST RESULT SUMMARY

Total    : 7
Passed   : 7
Failed   : 0
Pass Rate: 100%
```

---

## 5. Execution Result Table

| TC ID | Input | Expected | Actual | Result |
|---|---:|---|---|---|
| `TC-SEAT-BVA-01` | 0 | error | error | **PASS** |
| `TC-SEAT-BVA-02` | 1 | success | success | **PASS** |
| `TC-SEAT-BVA-03` | 2 | success | success | **PASS** |
| `TC-SEAT-BVA-07` | 6 | success | success | **PASS** |
| `TC-SEAT-BVA-04` | 11 | success | success | **PASS** |
| `TC-SEAT-BVA-05` | 12 | success | success | **PASS** |
| `TC-SEAT-BVA-06` | 13 | error | error | **PASS** |

HTTP status và response time không xuất hiện trong console output đã lưu ở đây; nếu cần báo cáo chi tiết thì lấy từ JSON/CSV report.

---

## 6. Newman / Automation Summary

| Metric | Result |
|---|---:|
| Total | 7 |
| Passed | 7 |
| Failed | 0 |
| Pass Rate | **100%** |

Công thức:

```text
Pass Rate
= Passed / Total × 100%
= 7 / 7 × 100%
= 100%
```

Đây là **Test Pass Rate**, không phải Code Coverage.

---

## 7. Report Evidence

Automation đã tạo:

```text
tests/reports/bva-result-2026-09-15T09-02-33-692Z.json
tests/reports/bva-result-2026-09-15T09-02-33-692Z.csv
```

Ngoài ra script còn cập nhật:

```text
tests/reports/latest-bva-result.json
tests/reports/latest-bva-result.csv
```

Các report này chứa các field:

```text
TestCaseID
Module
Field
Input
Boundary
Expected
Actual
HTTPStatus
ResponseTimeMs
Result
Error
```

---

## 8. Warning trong execution

Console có:

```text
[DEP0176] DeprecationWarning: fs.F_OK is deprecated
```

Đây là warning của Node/dependency, không phải failure của BVA.

Bằng chứng là automation vẫn chạy đủ 7 test và trả:

```text
Passed = 7
Failed = 0
Pass Rate = 100%
```

---

## 9. Postman Mapping Coverage

Nếu chỉ xét phạm vi BVA `seat_number`:

```text
Postman BVA Mapping = 7 / 7 = 100%
```

Nếu xét toàn bộ 12 validation test case đã thiết kế:

```text
PHPUnit Mapping = 12 / 12
Postman BVA Mapping = 7 / 12
```

5 case supporting validation/white-box sau chưa có request riêng trong BVA collection:

```text
TC-SEAT-EP-01
TC-SEAT-EP-02
TC-SEAT-EP-03
TC-SEAT-WB-01
TC-SEAT-WB-02
```

Vì vậy không được ghi Postman cover 12/12.

---

## 10. Quan hệ với White-box

Postman/Newman không tự tính white-box coverage.

Ví dụ:

```text
TC-SEAT-BVA-01
seat_number=0
```

Black-box evidence:

```text
Expected=error
Actual=error
PASS
```

White-box reasoning:

```text
D3a: seat_number < 1
TRUE
```

White-box coverage phải dựa trên source + decision matrix + test mapping; Newman chỉ cung cấp execution evidence.

---

## 11. Kết luận

Kết quả Black-box BVA Seat:

```text
7 test cases executed
7 PASS
0 FAIL
Pass Rate = 100%
```

Tất cả Standard BVA và Robustness values của `seat_number` đã chạy thành công qua API automation.
