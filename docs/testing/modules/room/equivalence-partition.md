# 02 - Equivalence Partitioning (EP) Document: Room Module
## KAN-57
## 1. Source Code Validation Logic Analysis
File: [RoomService.php](file:///d:/xampp/htdocs/movie-ticket-booking/backend/app/Services/RoomService.php#L111-L113)

```php
if (($data['total_seats'] ?? 0) < 1) {
    return ['status' => 'error', 'message' => 'Số ghế phải lớn hơn 0!'];
}
```

Theo quy tắc nghiệp vụ thực tế trong source code, điều kiện kiểm tra chỉ kiểm tra giá trị nhỏ hơn 1. Do đó:
- Miền hợp lệ: $total\_seats \ge 1$ (số nguyên dương).
- Miền bất hợp lệ bên dưới: $total\_seats < 1$ (số nguyên $\le 0$).
- Giới hạn trên (Upper Bound): **Không áp dụng (N/A)** vì source code không quy định giới hạn trên (ví dụ không có điều kiện `total_seats > 500`).

---

## 2. Equivalence Partitioning Table

| Module | Condition / Field | Partition Type | Numerical Range | EP Tag | Expected Output | Status Code |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **Room** | `total_seats` | **Valid Partition (V1)** | $total\_seats \ge 1$ ($1, 2, 3, \dots, 500, \dots$) | `EP-ROOM-V1` | `status: "success"`, message: `"Dữ liệu phòng hợp lệ!"` | HTTP 200 |
| **Room** | `total_seats` | **Invalid Below (X1)** | $total\_seats < 1$ ($\dots, -2, -1, 0$) | `EP-ROOM-X1` | `status: "error"`, message: `"Số ghế phải lớn hơn 0!"` | HTTP 200 |
| **Room** | `total_seats` | **Invalid Above (X2)** | N/A (Source code không quy định upper bound) | `EP-ROOM-X2` | N/A | N/A |

---

## 3. Partition Rationales & Rules
1. **Quy tắc không bịa đặt cận**:
   - Theo nguyên tắc làm việc của project, dữ liệu BVA/EP phải dựa trên Source of Truth (code hiện tại). Không tự ý đặt upper bound nếu source code không xử lý.
2. **Khóa Tag đại diện**:
   - `EP-ROOM-V1`: Bao phủ tất cả các test case có $total\_seats \ge 1$ (`TC-ROOM-BVA-03`, `TC-ROOM-BVA-04`, `TC-ROOM-BVA-05`).
   - `EP-ROOM-X1`: Bao phủ tất cả các test case có $total\_seats < 1$ (`TC-ROOM-BVA-01`, `TC-ROOM-BVA-02`).
