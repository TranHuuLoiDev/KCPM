> **Phạm vi lịch sử (trước bản Assignment 75 case):** Nội dung và số liệu bên dưới mô tả bộ Seat cũ. Bản hiện hành gồm 18 validation + 25 generate + 32 bulkDelete, đã chạy PHPUnit 75/75, 307 assertions. Postman hiện có 18 validation case, chưa chạy lại; Xdebug bên dưới là evidence cũ. Xem [báo cáo hiện hành](083205006374_LuongQuocAn_BaoCao_Seat_Form_Assignment_Chot.md) và [mapping hiện hành](07_phpunit_mapping.md).

# 03 – EQUIVALENCE PARTITIONING: SEAT

## 1. Nguyên tắc

Equivalence Partitioning được tạo từ validation condition thật trong `SeatService`, không tự đặt range hoặc business rule.

Tag dùng thống nhất:

```text
SEAT-V* → valid partition
SEAT-X* → invalid partition
```

## 2. Phân hoạch chi tiết

| Module | Condition | Partition | Range / Giá trị | Tag | Expected |
|---|---|---|---|---|---|
| Seat | `seat_number` | Valid | `1..12` | `SEAT-V1` | success |
| Seat | `seat_number` | Invalid below | `<1` | `SEAT-X1` | error |
| Seat | `seat_number` | Invalid above | `>12` | `SEAT-X2` | error |
| Seat | `seat_row` | Valid | Một ký tự `A..H` sau normalize | `SEAT-V2` | tiếp tục validation |
| Seat | `seat_row` | Invalid | Không khớp `^[A-H]$` | `SEAT-X3` | error |
| Seat | `room_id` | Valid | `>0` và tồn tại | `SEAT-V3` | tiếp tục validation |
| Seat | `room_id` | Invalid non-positive | `<=0` | `SEAT-X4A` | error |
| Seat | `room_id` | Invalid not found | `>0` nhưng không tồn tại | `SEAT-X4B` | error |
| Seat | `seat_type_id` | Valid | `>0` và tồn tại | `SEAT-V4` | tiếp tục validation |
| Seat | `seat_type_id` | Invalid non-positive | `<=0` | `SEAT-X5A` | error |
| Seat | `seat_type_id` | Invalid not found | `>0` nhưng không tồn tại | `SEAT-X5B` | error |

## 3. Bảng theo format Assignment

| Conditions | Valid Partitions | Tag | Invalid Partitions | Tag | Valid Boundaries | Tag |
|---|---|---|---|---|---|---|
| `seat_number` | `1..12` | `SEAT-V1` | `<1` | `SEAT-X1` | `1` | `SEAT-B1` |
| `seat_number` | `1..12` | `SEAT-V1` | `>12` | `SEAT-X2` | `12` | `SEAT-B5` |
| `seat_row` | `A..H` | `SEAT-V2` | ngoài `A..H` | `SEAT-X3` | N/A | N/A |
| `room_id` | `>0` và tồn tại | `SEAT-V3` | `<=0` | `SEAT-X4A` | N/A | N/A |
| `room_id` | `>0` và tồn tại | `SEAT-V3` | `>0` nhưng không tồn tại | `SEAT-X4B` | N/A | N/A |
| `seat_type_id` | `>0` và tồn tại | `SEAT-V4` | `<=0` | `SEAT-X5A` | N/A | N/A |
| `seat_type_id` | `>0` và tồn tại | `SEAT-V4` | `>0` nhưng không tồn tại | `SEAT-X5B` | N/A | N/A |

## 4. Representative values

| Tag | Representative | Lý do |
|---|---|---|
| `SEAT-V1` | `6` | Giá trị hợp lệ đại diện trong `1..12` |
| `SEAT-X1` | `0` | Ngay dưới lower boundary |
| `SEAT-X2` | `13` | Ngay trên upper boundary |
| `SEAT-V2` | `A` | Hàng hợp lệ |
| `SEAT-X3` | `I` | Ngoài miền A..H |
| `SEAT-V3` | `1` | ID hợp lệ nếu record tồn tại |
| `SEAT-X4A` | `0` | Non-positive ID |
| `SEAT-X4B` | `999999` | Positive ID dùng kiểm tra không tồn tại |
| `SEAT-V4` | `1` | ID hợp lệ nếu record tồn tại |
| `SEAT-X5A` | `0` | Non-positive ID |
| `SEAT-X5B` | `999999` | Positive ID dùng kiểm tra không tồn tại |

## 5. Quan hệ EP và BVA

```text
SEAT-X1 → 0  → min-
SEAT-V1 → 1,2,6,11,12
SEAT-X2 → 13 → max+
```

## 6. Không tạo partition giả

- Không tự tạo upper bound khác cho `seat_number`.
- Không tự đặt max cho `room_id` hoặc `seat_type_id` vì source không quy định upper bound.

## 7. Kết luận EP

```text
seat_number:
V1 = 1..12
X1 = <1
X2 = >12
```
