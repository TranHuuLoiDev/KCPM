# 01 - Scope Definition Document: Room Module
## KAN-57
## 1. Overview & Objectives
- **Project**: Movie Ticket Booking (Team BVA)
- **Target Module**: `Room` (Phòng chiếu)
- **Target Field**: `total_seats` (Tổng số ghế trong phòng)
- **API Endpoint**: `POST /rooms/validate` (hoặc `POST /backend/api.php/rooms/validate`)

---

## 2. Function Selection Rationale

| Attribute | Specification Details |
| :--- | :--- |
| **Module** | Room |
| **Function under Test** | `App\Services\RoomService::validateRoomInput($data)` |
| **Source Code File** | [RoomService.php](file:///d:/xampp/htdocs/movie-ticket-booking/backend/app/Services/RoomService.php#L86-L133) |
| **Target Field** | `total_seats` (Data type: `integer`) |
| **Testing Scope** | Both **Black-box** (API validation via Postman/Newman) & **White-box** (PHPUnit + Decision Matrix) |
| **Selection Reason** | 1. Có điều kiện kiểm tra biên số học rõ ràng trong code (`total_seats >= 1`).<br>2. Có tích hợp sẵn trong Unit test suite PHPUnit.<br>3. Có endpoint API hỗ trợ kiểm thử tự động Black-box qua Postman/Newman.<br>4. Thỏa mãn các tiêu chí phân tích White-box decision coverage. |

---

## 3. Scope Classification

### 3.1 Black-box Scope
- Kiểm thử các phản hồi bên ngoài qua HTTP API `POST /rooms/validate`.
- **Request Body**:
  ```json
  {
    "theatre_id": 1,
    "name": "Phong Standard A",
    "total_seats": 1,
    "is_active": true
  }
  ```
- **Expected Status Codes & Response**:
  - Valid input ($total\_seats \ge 1$): `{"status": "success", "message": "Dữ liệu phòng hợp lệ!"}` (HTTP 200)
  - Invalid input ($total\_seats < 1$): `{"status": "error", "message": "Số ghế phải lớn hơn 0!"}` (HTTP 200)

### 3.2 White-box Scope
- Phân tích và kiểm thử luồng logic bên trong [RoomService.php](file:///d:/xampp/htdocs/movie-ticket-booking/backend/app/Services/RoomService.php):
  - Phương thức `validateBase($data)`: Kiểm tra tên, rạp, và `total_seats`.
  - Phương thức `validate($data, $excludeId)`: Kiểm tra tên trùng lặp trong DB.
  - Các phương thức CRUD: `addRoom`, `updateRoom`, `deleteRoom`, `getRoomById`.

---

## 4. Preconditions & Requirements
1. **Database Precondition**:
   - `theatre_id`: Phải là ID của một rạp chiếu hợp lệ đang tồn tại trong bảng `theatres` (ví dụ `theatre_id = 1`).
   - Cơ sở dữ liệu MySQL (`movie_ticket_booking`) phải đang chạy.
2. **Environment Precondition**:
   - PHP 8.2+ với extension `mysqli` và `xdebug` / `pcov`.
   - Local web server (PHP CLI Dev Server `http://127.0.0.1:8080` hoặc Apache XAMPP).
