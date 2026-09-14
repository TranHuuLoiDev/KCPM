# SEAT TEST DESIGN PACKAGE – MOVIE TICKET BOOKING

## Mục đích

Bộ tài liệu này triển khai đúng chuỗi phương pháp trong `TEAM_BVA_MOVIE_TICKET_BOOKING_WORKPLAN.md` cho **module Seat**, từ bước xác định phạm vi đến bước thiết kế test case.

```text
SOURCE CODE
↓
BUSINESS RULE
↓
VALIDATION CONDITION
↓
TEST SCOPE
↓
EQUIVALENCE PARTITIONING
↓
BOUNDARY VALUE ANALYSIS
↓
TEST CASE DESIGN
```

## Source of truth

Không lấy boundary hay business rule từ Assignment để áp đặt vào project. Các kết luận trong bộ tài liệu này dựa trên source hiện tại của project:

- `backend/app/Services/SeatService.php`
- `backend/api.php`
- `backend/tests/Services/SeatServiceTest.php`
- `tests/bva/bva-cases.js`
- `tests/postman/BVA_MovieBooking.postman_collection.json`

## Phạm vi

- Module: `Seat`
- Function chính: `SeatService::validateSeatInput($data)`
- Logic hỗ trợ: `SeatService::validateBase($data)`
- Input BVA chính: `seat_number`
- Input hỗ trợ EP/white-box: `room_id`, `seat_row`, `seat_type_id`

## Các file

| File | Nội dung |
|---|---|
| `01_scope.md` | Xác định phạm vi kiểm thử Seat |
| `02_business_rules.md` | Business rule, validation condition, precondition |
| `03_equivalence_partition.md` | Phân hoạch lớp tương đương |
| `04_boundary_value_analysis.md` | Standard BVA và Robustness |
| `05_test_case_design.md` | Thiết kế test case từ EP/BVA |

## Nguyên tắc

- Standard BVA của `seat_number`: `1, 2, 6, 11, 12`.
- `0` và `13` là Robustness / Out-of-bound.
- Khi test `seat_number`, các input còn lại giữ hợp lệ.
- `room_id=1` và `seat_type_id=1` chỉ được dùng nếu các record thực sự tồn tại trong DB.
- Actual Output / Status / Evidence ở giai đoạn thiết kế để `TBD`; sẽ điền ở bước execution/evidence.
