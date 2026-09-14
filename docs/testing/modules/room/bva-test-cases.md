# 03 - Boundary Value Analysis (BVA) & Test Case Design: Room Module
## KAN-57
## 1. Boundary Values Breakdown

Dựa trên điều kiện validation trong source code ($total\_seats \ge 1$):
- **Lower Boundary (MIN)**: $1$
- **Upper Boundary (MAX)**: N/A (Không có trong source code)

### Standard BVA vs. Robustness Boundary Values Table

| Boundary Position | Value | Category | BVA Tag | Expected Outcome |
| :--- | :--- | :--- | :--- | :--- |
| **MIN - 2** | `-1` | Robustness / Out-of-bound | `BVA-ROOM-MINMINUS-02` | `status: "error"`, message: `"Số ghế phải lớn hơn 0!"` |
| **MIN - 1** | `0` | Out-of-bound (Ngay dưới Min) | `BVA-ROOM-MINMINUS-01` | `status: "error"`, message: `"Số ghế phải lớn hơn 0!"` |
| **MIN** | `1` | Standard BVA (Cận dưới hợp lệ) | `BVA-ROOM-MIN-01` | `status: "success"`, message: `"Dữ liệu phòng hợp lệ!"` |
| **MIN + 1** | `2` | Standard BVA (Ngay trên Min) | `BVA-ROOM-MINPLUS-01` | `status: "success"`, message: `"Dữ liệu phòng hợp lệ!"` |
| **NOMINAL** | `40` | Standard BVA (Giá trị đại diện) | `BVA-ROOM-NOMINAL-01` | `status: "success"`, message: `"Dữ liệu phòng hợp lệ!"` |
| **MAX - 1** | N/A | N/A (Không có upper bound) | N/A | N/A |
| **MAX** | N/A | N/A (Không có upper bound) | N/A | N/A |
| **MAX + 1** | N/A | N/A (Không có upper bound) | N/A | N/A |

---

## 2. Complete Test Case Design Matrix

| TC ID | Module | Function / Endpoint | Input Data | Preconditions | Boundary | EP Tag | BVA Tag | Expected Output | Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `TC-ROOM-BVA-01` | Room | `POST /rooms/validate` | `total_seats = -1` | `theatre_id=1`, `name="Room A"` | `MIN - 2` | `EP-ROOM-X1` | `BVA-ROOM-MINMINUS-02` | `status: "error"` | **PASS** |
| `TC-ROOM-BVA-02` | Room | `POST /rooms/validate` | `total_seats = 0` | `theatre_id=1`, `name="Room B"` | `MIN - 1` | `EP-ROOM-X1` | `BVA-ROOM-MINMINUS-01` | `status: "error"` | **PASS** |
| `TC-ROOM-BVA-03` | Room | `POST /rooms/validate` | `total_seats = 1` | `theatre_id=1`, `name="Room C"` | `MIN` | `EP-ROOM-V1` | `BVA-ROOM-MIN-01` | `status: "success"` | **PASS** |
| `TC-ROOM-BVA-04` | Room | `POST /rooms/validate` | `total_seats = 2` | `theatre_id=1`, `name="Room D"` | `MIN + 1` | `EP-ROOM-V1` | `BVA-ROOM-MINPLUS-01` | `status: "success"` | **PASS** |
| `TC-ROOM-BVA-05` | Room | `POST /rooms/validate` | `total_seats = 40` | `theatre_id=1`, `name="Room E"` | `NOMINAL` | `EP-ROOM-V1` | `BVA-ROOM-NOMINAL-01` | `status: "success"` | **PASS** |

---

## 3. Test Categories Classification
1. **Standard BVA**:
   - `TC-ROOM-BVA-03` (`total_seats = 1`)
   - `TC-ROOM-BVA-04` (`total_seats = 2`)
   - `TC-ROOM-BVA-05` (`total_seats = 40`)
2. **Robustness / Out-of-bound BVA**:
   - `TC-ROOM-BVA-01` (`total_seats = -1`)
   - `TC-ROOM-BVA-02` (`total_seats = 0`)
3. **Full Functional / Happy Path**:
   - `TC-ROOM-BVA-05` (Tạo phòng thành công với giá trị ghế chuẩn 40).
