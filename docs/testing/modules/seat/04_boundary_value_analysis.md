> **Phạm vi lịch sử (trước bản Assignment 75 case):** Nội dung và số liệu bên dưới mô tả bộ Seat cũ. Bản hiện hành gồm 18 validation + 25 generate + 32 bulkDelete, đã chạy PHPUnit 75/75, 307 assertions. Postman hiện có 18 validation case, chưa chạy lại; Xdebug bên dưới là evidence cũ. Xem [báo cáo hiện hành](083205006374_LuongQuocAn_BaoCao_Seat_Form_Assignment_Chot.md) và [mapping hiện hành](07_phpunit_mapping.md).

# 04 – BOUNDARY VALUE ANALYSIS: SEAT

## 1. Input áp dụng BVA

```text
seat_number
```

Rule:

```text
1 <= seat_number <= 12
```

```text
MIN = 1
MAX = 12
```

## 2. Standard BVA

| Boundary | Value | Tag | Partition | Expected |
|---|---:|---|---|---|
| `min` | 1 | `SEAT-B1` | `SEAT-V1` | success |
| `min+` | 2 | `SEAT-B2` | `SEAT-V1` | success |
| `nominal` | 6 | `SEAT-B3` | `SEAT-V1` | success |
| `max-` | 11 | `SEAT-B4` | `SEAT-V1` | success |
| `max` | 12 | `SEAT-B5` | `SEAT-V1` | success |

Standard BVA:

```text
{1, 2, 6, 11, 12}
```

## 3. Robustness / Out-of-bound

| Boundary | Value | Tag | Partition | Expected |
|---|---:|---|---|---|
| `min-` | 0 | `SEAT-R1` | `SEAT-X1` | error |
| `max+` | 13 | `SEAT-R2` | `SEAT-X2` | error |

Robustness:

```text
{0, 13}
```

Không gọi cả 7 giá trị là Standard BVA.

## 4. Lý do chọn nominal = 6

- Nằm trong `1..12`.
- Không phải boundary.
- Gần giữa miền.
- Không tạo precondition bất thường.

## 5. One-variable-at-a-time

Khi test `seat_number`:

```text
room_id      = 1
seat_row     = A
seat_type_id = 1
is_active    = true
```

`room_id=1` và `seat_type_id=1` phải thực sự tồn tại.

## 6. Mapping boundary → TC ID

| TC ID | Boundary | Value | Expected |
|---|---|---:|---|
| `TC-SEAT-BVA-01` | min- | 0 | error |
| `TC-SEAT-BVA-02` | min | 1 | success |
| `TC-SEAT-BVA-03` | min+ | 2 | success |
| `TC-SEAT-BVA-07` | nominal | 6 | success |
| `TC-SEAT-BVA-04` | max- | 11 | success |
| `TC-SEAT-BVA-05` | max | 12 | success |
| `TC-SEAT-BVA-06` | max+ | 13 | error |

Giữ `TC-SEAT-BVA-07` cho nominal để không renumber các case `04..06` đã tồn tại trước đó.

## 7. BVA Tag Coverage thiết kế

```text
SEAT-B1
SEAT-B2
SEAT-B3
SEAT-B4
SEAT-B5
SEAT-R1
SEAT-R2
```

Designed tags:

```text
7/7
```

Đây là design coverage của boundary tags, không phải code coverage.
