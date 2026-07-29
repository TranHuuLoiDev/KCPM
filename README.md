# Online Movie Ticket Booking System

## Project Overview

Online Movie Ticket Booking System là website hỗ trợ khách hàng tra cứu phim, xem lịch chiếu, chọn ghế và đặt vé xem phim trực tuyến.

Hệ thống được phát triển theo kiến trúc PHP MVC kết hợp MySQL, bao gồm hai phân hệ chính:

* User Site (Khách hàng)
* Admin Site (Quản trị hệ thống)

---

# Technology Stack

## Backend

* PHP 8.x
* MVC Architecture
* Session-based Authentication

## Database

* MySQL 8.x

## Frontend

* HTML5
* CSS3
* JavaScript

## Development Tools

* Laragon
* VS Code
* Git
* GitHub
* HeidiSQL

---

# Environment Setup

## 1. Tải Code (Clone)
1. Mở Terminal (hoặc Git Bash, CMD) tại thư mục `htdocs` của XAMPP (thường là `C:\xampp\htdocs`):
2. Chạy lệnh tải dự án:
   ```bash
   git clone https://github.com/huutienSE/movie-ticket-booking.git
   ```
3. Di chuyển vào thư mục dự án:
   ```bash
   cd movie-ticket-booking
   ```

## 2. Khởi tạo Cơ Sở Dữ Liệu (Database)

Dự án sử dụng cơ sở dữ liệu chung tên là: **`movie_ticket_booking`**. (User mặc định là `root`, pass rỗng `""`).

1. Mở bảng điều khiển **XAMPP Control Panel** và bấm **Start** 2 dịch vụ: `Apache` và `MySQL`.
2. Truy cập phpMyAdmin: 👉 [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
3. Tạo Database tên là `movie_ticket_booking` (Collation: `utf8mb4_general_ci`).
4. Chọn tab **Import (Nhập)**, chọn file `Database/BookingTicketDatabase.sql` có trong thư mục dự án.
5. Bấm **Go (Thực hiện)**.

## 3. Chạy Dự Án
- 🏠 **Trang khách hàng:** [http://localhost/movie-ticket-booking/](http://localhost/movie-ticket-booking/)
- 🔑 **Trang Đăng nhập:** [http://localhost/movie-ticket-booking/login.php](http://localhost/movie-ticket-booking/login.php)
> **Tài khoản test (Admin):** `admin@example.com` / `password`

---

# Project Structure (Hybrid MVC)

Từ Phase 2, tất cả tuân theo kiến trúc **Phân tầng (Hybrid MVC)**.

```text
movie-ticket-booking/
├── app/                        # BỘ NÃO CỦA DỰ ÁN (Chứa toàn bộ PHP Logic)
│   ├── Config/                 # Cấu hình chung
│   │   └── Database.php        # Lớp kết nối CSDL duy nhất
│   │
│   ├── Models/                 # TẦNG 1: Tương tác Database
│   │   ├── UserModel.php       # Chứa các câu SELECT, INSERT, UPDATE...
│   │
│   ├── Services/               # TẦNG 2: Xử lý nghiệp vụ (Business Logic)
│   │   ├── AuthService.php     # Kiểm tra pass, check ghế trống, tính tiền...
│   │
│   ├── Controllers/            # TẦNG 3: Nhận Request & Điều hướng
│   │   ├── AuthController.php  # Nhận $_POST từ form, gọi Service tương ứng
│   │
│   └── init.php                # File Autoloader (tự động nạp class)
│
├── admin/                      # GIAO DIỆN ADMIN (HTML + PHP lấy data)
│   ├── manage_movies.php       # Gọi Controller để lấy mảng data và foreach HTML
│   └── ...
│
└── (Thư mục gốc)               # GIAO DIỆN KHÁCH HÀNG (Nằm ngay ngoài cùng)
    ├── index.php               
    └── login.php               
```

---

# Git Branch Strategy

## Main Branch

```txt
main
```

* Stable version
* Demo version
* Final release

Không được commit trực tiếp vào main.

---

## Development Branch

```txt
develop
```

* Integration branch
* Testing branch

Không được commit trực tiếp vào develop.

---

## Feature Branches

Ví dụ:

```txt
feature/authentication
feature/movie-module
feature/showtime-module
feature/booking-module
feature/admin-module
feature/frontend-user
feature/frontend-booking
```

Mỗi thành viên phát triển trên feature branch riêng.

---

# Development Workflow

## Bước 1

Luôn cập nhật develop mới nhất:

```bash
git checkout develop
git pull origin develop
```

## Bước 2

Tạo feature branch:

```bash
git checkout -b feature/module-name
```

Ví dụ:

```bash
git checkout -b feature/movie-crud
```

## Bước 3

Thực hiện code và commit:

```bash
git add .
git commit -m "feat: implement movie CRUD"
```

## Bước 4

Push branch:

```bash
git push origin feature/movie-crud
```

## Bước 5

Tạo Pull Request:

```txt
feature/movie-crud
        ↓
      develop
```

## Bước 6

Chờ review.

Sau khi được approve mới được merge.

---

# Pull Request Rules

## Main Branch

Điều kiện merge:

* Pull Request bắt buộc
* Tối thiểu 2 approvals
* Resolve toàn bộ comments
* Không force push
* Không delete branch

## Develop Branch

Điều kiện merge:

* Pull Request bắt buộc
* Tối thiểu 1 approval
* Resolve toàn bộ comments
* Không force push
* Không delete branch

---

# Team Rules

## Không được

* Commit trực tiếp lên main
* Commit trực tiếp lên develop
* Force push
* Merge PR của chính mình
* Push code chưa test

## Bắt buộc

* Pull latest develop trước khi code
* Commit rõ ràng
* Tạo Pull Request
* Chờ review trước khi merge

---

# Commit Message Convention

## Feature

```txt
feat: add login functionality
```

## Fix

```txt
fix: resolve booking validation bug
```

## Refactor

```txt
refactor: improve booking service
```

## Documentation

```txt
docs: update README
```

## Chore

```txt
chore: configure project structure
```

---

# Contributors

| Member              | Responsibility                                                       |
| ------------------- | -------------------------------------------------------------------- |
| Huỳnh Phạm Hữu Tiền | Team Leader, Architecture, Database, Authentication, Booking Backend |
| Nhân                | Movie Module, Genre Module                                           |
| Tiến                | Room, Seat, Showtime Module                                          |
| Quỳnh Anh           | User Frontend                                                        |
| Thịnh               | Booking Frontend                                                     |
| TBD                 | Testing, Documentation, Admin UI                                     |

---

# Current Status

* [x] Project Initialization
* [x] Git Repository Setup
* [x] GitHub Branch Protection
* [x] Development Environment Setup
* [ ] Database Design
* [ ] Authentication Module
* [ ] Movie Module
* [ ] Showtime Module
* [ ] Booking Module
* [ ] Admin Module
* [ ] Testing & Deployment

```
```
