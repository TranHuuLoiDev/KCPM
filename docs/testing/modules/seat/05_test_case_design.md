> **Phạm vi lịch sử (trước bản Assignment 75 case):** Nội dung và số liệu bên dưới mô tả bộ Seat cũ. Bản hiện hành gồm 18 validation + 25 generate + 32 bulkDelete, đã chạy PHPUnit 75/75, 307 assertions. Postman hiện có 18 validation case, chưa chạy lại; Xdebug bên dưới là evidence cũ. Xem [báo cáo hiện hành](083205006374_LuongQuocAn_BaoCao_Seat_Form_Assignment_Chot.md) và [mapping hiện hành](07_phpunit_mapping.md).

# 05 – TEST CASE DESIGN: SEAT

## 1. Nguyên tắc

```text
Business Rule
↓
Equivalence Partition
↓
Boundary Value
↓
Expected Output
```

Mỗi test chỉ thay đổi input mục tiêu; các input còn lại giữ hợp lệ.

## 2. Preconditions chung

Đối với test `seat_number`:

```text
P1: Database khả dụng.
P2: room_id=1 tồn tại.
P3: seat_type_id=1 tồn tại.
P4: seat_row=A.
P5: is_active=true.
```

## 3. Standard BVA test cases

| Test Case | Input | Expected Outcome | New Tags Covered |
|---|---|---|---|
| `TC-SEAT-BVA-02` | `seat_number=1` | `success` | `SEAT-V1`, `SEAT-B1` |
| `TC-SEAT-BVA-03` | `seat_number=2` | `success` | `SEAT-B2` |
| `TC-SEAT-BVA-07` | `seat_number=6` | `success` | `SEAT-B3`, `FUNC-HAPPY` |
| `TC-SEAT-BVA-04` | `seat_number=11` | `success` | `SEAT-B4` |
| `TC-SEAT-BVA-05` | `seat_number=12` | `success` | `SEAT-B5` |

## 4. Robustness test cases

| Test Case | Input | Expected Outcome | New Tags Covered |
|---|---|---|---|
| `TC-SEAT-BVA-01` | `seat_number=0` | `error` | `SEAT-X1`, `SEAT-R1` |
| `TC-SEAT-BVA-06` | `seat_number=13` | `error` | `SEAT-X2`, `SEAT-R2` |

## 5. EP / supporting validation cases

| Test Case | Input thay đổi | Expected Outcome | Tags Covered |
|---|---|---|---|
| `TC-SEAT-EP-01` | `seat_row=I`, `seat_number=6` | `error` | `SEAT-X3` |
| `TC-SEAT-EP-02` | `room_id=0`, `seat_number=6` | `error` | `SEAT-X4A` |
| `TC-SEAT-EP-03` | `seat_type_id=0`, `seat_number=6` | `error` | `SEAT-X5A` |
| `TC-SEAT-WB-01` | `room_id=999999`, `seat_number=6` | `error` | `SEAT-X4B` |
| `TC-SEAT-WB-02` | `seat_type_id=999999`, `seat_number=6` | `error` | `SEAT-X5B` |

## 6. Chi tiết test case

### TC-SEAT-BVA-01

| Field | Nội dung |
|---|---|
| TC ID | `TC-SEAT-BVA-01` |
| Module | Seat |
| Function | `validateSeatInput()` |
| Test Type | Black-box + White-box |
| Input | `seat_number=0` |
| Precondition | room `1` tồn tại, type `1` tồn tại, row `A` |
| Boundary | `min-` |
| Partition | `SEAT-X1` |
| Expected Output | `status=error` |
| Expected Reason | `seat_number < 1` |
| Tags Covered | `SEAT-X1`, `SEAT-R1` |
| Actual Output | TBD – execution phase |
| Status | TBD |
| Evidence | TBD |

### TC-SEAT-BVA-02

| Field | Nội dung |
|---|---|
| TC ID | `TC-SEAT-BVA-02` |
| Module | Seat |
| Function | `validateSeatInput()` |
| Test Type | Black-box + White-box |
| Input | `seat_number=1` |
| Precondition | room `1` tồn tại, type `1` tồn tại, row `A` |
| Boundary | `min` |
| Partition | `SEAT-V1` |
| Expected Output | `status=success` |
| Expected Reason | Lower valid boundary |
| Tags Covered | `SEAT-V1`, `SEAT-B1` |
| Actual Output | TBD – execution phase |
| Status | TBD |
| Evidence | TBD |

### TC-SEAT-BVA-03

| Field | Nội dung |
|---|---|
| TC ID | `TC-SEAT-BVA-03` |
| Module | Seat |
| Function | `validateSeatInput()` |
| Test Type | Black-box + White-box |
| Input | `seat_number=2` |
| Precondition | room `1` tồn tại, type `1` tồn tại, row `A` |
| Boundary | `min+` |
| Partition | `SEAT-V1` |
| Expected Output | `status=success` |
| Expected Reason | Ngay trên lower boundary |
| Tags Covered | `SEAT-B2` |
| Actual Output | TBD – execution phase |
| Status | TBD |
| Evidence | TBD |

### TC-SEAT-BVA-07

| Field | Nội dung |
|---|---|
| TC ID | `TC-SEAT-BVA-07` |
| Module | Seat |
| Function | `validateSeatInput()` |
| Test Type | Black-box + White-box |
| Input | `seat_number=6` |
| Precondition | room `1` tồn tại, type `1` tồn tại, row `A` |
| Boundary | `nominal` |
| Partition | `SEAT-V1` |
| Expected Output | `status=success` |
| Expected Reason | Representative valid value |
| Tags Covered | `SEAT-B3`, `FUNC-HAPPY` |
| Actual Output | TBD – execution phase |
| Status | TBD |
| Evidence | TBD |

### TC-SEAT-BVA-04

| Field | Nội dung |
|---|---|
| TC ID | `TC-SEAT-BVA-04` |
| Module | Seat |
| Function | `validateSeatInput()` |
| Test Type | Black-box + White-box |
| Input | `seat_number=11` |
| Precondition | room `1` tồn tại, type `1` tồn tại, row `A` |
| Boundary | `max-` |
| Partition | `SEAT-V1` |
| Expected Output | `status=success` |
| Expected Reason | Ngay dưới upper boundary |
| Tags Covered | `SEAT-B4` |
| Actual Output | TBD – execution phase |
| Status | TBD |
| Evidence | TBD |

### TC-SEAT-BVA-05

| Field | Nội dung |
|---|---|
| TC ID | `TC-SEAT-BVA-05` |
| Module | Seat |
| Function | `validateSeatInput()` |
| Test Type | Black-box + White-box |
| Input | `seat_number=12` |
| Precondition | room `1` tồn tại, type `1` tồn tại, row `A` |
| Boundary | `max` |
| Partition | `SEAT-V1` |
| Expected Output | `status=success` |
| Expected Reason | Upper valid boundary |
| Tags Covered | `SEAT-B5` |
| Actual Output | TBD – execution phase |
| Status | TBD |
| Evidence | TBD |

### TC-SEAT-BVA-06

| Field | Nội dung |
|---|---|
| TC ID | `TC-SEAT-BVA-06` |
| Module | Seat |
| Function | `validateSeatInput()` |
| Test Type | Black-box + White-box |
| Input | `seat_number=13` |
| Precondition | room `1` tồn tại, type `1` tồn tại, row `A` |
| Boundary | `max+` |
| Partition | `SEAT-X2` |
| Expected Output | `status=error` |
| Expected Reason | `seat_number > 12` |
| Tags Covered | `SEAT-X2`, `SEAT-R2` |
| Actual Output | TBD – execution phase |
| Status | TBD |
| Evidence | TBD |

### TC-SEAT-EP-01

| Field | Nội dung |
|---|---|
| TC ID | `TC-SEAT-EP-01` |
| Module | Seat |
| Function | `validateSeatInput()` |
| Test Type | White-box / EP supporting case |
| Input | `seat_row=I`, `seat_number=6` |
| Precondition | room `1` tồn tại, type `1` tồn tại |
| Boundary | N/A |
| Partition | `SEAT-X3` |
| Expected Output | `status=error` |
| Expected Reason | `seat_row` không khớp A..H |
| Tags Covered | `SEAT-X3` |
| Actual Output | TBD – execution phase |
| Status | TBD |
| Evidence | TBD |

### TC-SEAT-EP-02

| Field | Nội dung |
|---|---|
| TC ID | `TC-SEAT-EP-02` |
| Module | Seat |
| Function | `validateSeatInput()` |
| Test Type | White-box / EP supporting case |
| Input | `room_id=0`, `seat_number=6` |
| Precondition | type `1` tồn tại, row `A` |
| Boundary | N/A |
| Partition | `SEAT-X4A` |
| Expected Output | `status=error` |
| Expected Reason | `room_id <= 0` |
| Tags Covered | `SEAT-X4A` |
| Actual Output | TBD – execution phase |
| Status | TBD |
| Evidence | TBD |

### TC-SEAT-EP-03

| Field | Nội dung |
|---|---|
| TC ID | `TC-SEAT-EP-03` |
| Module | Seat |
| Function | `validateSeatInput()` |
| Test Type | White-box / EP supporting case |
| Input | `seat_type_id=0`, `seat_number=6` |
| Precondition | room `1` tồn tại, row `A` |
| Boundary | N/A |
| Partition | `SEAT-X5A` |
| Expected Output | `status=error` |
| Expected Reason | `seat_type_id <= 0` |
| Tags Covered | `SEAT-X5A` |
| Actual Output | TBD – execution phase |
| Status | TBD |
| Evidence | TBD |

### TC-SEAT-WB-01

| Field | Nội dung |
|---|---|
| TC ID | `TC-SEAT-WB-01` |
| Module | Seat |
| Function | `validateSeatInput()` |
| Test Type | White-box supporting condition |
| Input | `room_id=999999`, `seat_number=6` |
| Precondition | `999999` không tồn tại; type `1` tồn tại; row `A` |
| Boundary | N/A |
| Partition | `SEAT-X4B` |
| Expected Output | `status=error` |
| Expected Reason | `room_id > 0` nhưng lookup không tìm thấy |
| Tags Covered | `SEAT-X4B` |
| Actual Output | TBD – execution phase |
| Status | TBD |
| Evidence | TBD |

### TC-SEAT-WB-02

| Field | Nội dung |
|---|---|
| TC ID | `TC-SEAT-WB-02` |
| Module | Seat |
| Function | `validateSeatInput()` |
| Test Type | White-box supporting condition |
| Input | `seat_type_id=999999`, `seat_number=6` |
| Precondition | room `1` tồn tại; type `999999` không tồn tại; row `A` |
| Boundary | N/A |
| Partition | `SEAT-X5B` |
| Expected Output | `status=error` |
| Expected Reason | `seat_type_id > 0` nhưng lookup không tìm thấy |
| Tags Covered | `SEAT-X5B` |
| Actual Output | TBD – execution phase |
| Status | TBD |
| Evidence | TBD |

## 7. Coverage thiết kế

### Standard BVA

```text
SEAT-B1 → TC-SEAT-BVA-02
SEAT-B2 → TC-SEAT-BVA-03
SEAT-B3 → TC-SEAT-BVA-07
SEAT-B4 → TC-SEAT-BVA-04
SEAT-B5 → TC-SEAT-BVA-05
```

```text
5/5 Standard BVA tags
```

### Robustness

```text
SEAT-R1 → TC-SEAT-BVA-01
SEAT-R2 → TC-SEAT-BVA-06
```

```text
2/2 Robustness tags
```

## 8. Traceability trước execution

| TC ID | Rule | Partition | Boundary | Planned PHPUnit | Planned Postman |
|---|---|---|---|---|---|
| TC-SEAT-BVA-01 | BR-S03 | SEAT-X1 | min- | Yes | Yes |
| TC-SEAT-BVA-02 | BR-S03 | SEAT-V1 | min | Yes | Yes |
| TC-SEAT-BVA-03 | BR-S03 | SEAT-V1 | min+ | Yes | Yes |
| TC-SEAT-BVA-07 | BR-S03 | SEAT-V1 | nominal | Yes | Yes |
| TC-SEAT-BVA-04 | BR-S03 | SEAT-V1 | max- | Yes | Yes |
| TC-SEAT-BVA-05 | BR-S03 | SEAT-V1 | max | Yes | Yes |
| TC-SEAT-BVA-06 | BR-S03 | SEAT-X2 | max+ | Yes | Yes |
| TC-SEAT-EP-01 | BR-S02 | SEAT-X3 | N/A | Yes | Not yet in BVA collection |
| TC-SEAT-EP-02 | BR-S01 | SEAT-X4A | N/A | Yes | Not yet in BVA collection |
| TC-SEAT-EP-03 | BR-S04 | SEAT-X5A | N/A | Yes | Not yet in BVA collection |
| TC-SEAT-WB-01 | BR-S01 | SEAT-X4B | N/A | Yes | Not yet in BVA collection |
| TC-SEAT-WB-02 | BR-S04 | SEAT-X5B | N/A | Yes | Not yet in BVA collection |

## 9. Kết luận giai đoạn thiết kế

Đã có:

```text
1 scope chính thức
5 business rules
valid/invalid equivalence partitions
5 Standard BVA values
2 Robustness values
12 test cases thiết kế
traceability Rule → Partition → Boundary → TC
```

Bước tiếp theo mới là:

```text
PHPUnit Mapping / Execution
Postman / Newman Execution
White-box Decision Matrix
Coverage
Evidence
```
