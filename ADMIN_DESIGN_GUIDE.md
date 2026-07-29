# Admin UI/UX Design Guide

Tài liệu này dùng làm chuẩn thiết kế lại phần admin cho project `movie-ticket-booking`, tham khảo trực tiếp từ phần admin của `cinema-booking-system` nhưng điều chỉnh để phù hợp cấu trúc hiện tại của project này.

## 1. Mục Tiêu Thiết Kế

Phần admin cần có cảm giác:

- Điện ảnh, tối, tập trung vào dữ liệu.
- Rõ ràng cho thao tác quản trị: xem nhanh, lọc nhanh, sửa nhanh.
- Đồng nhất giữa dashboard, quản lý phim, thể loại, người dùng và đặt vé.
- Không làm lại logic PHP/database nếu chưa cần; ưu tiên chuẩn hóa UI, CSS, component và trải nghiệm thao tác.

## 2. Phong Cách Tổng Thể

Admin dùng phong cách `Dark Cinema Admin`.

Đặc điểm chính:

- Nền chính đen/xám than.
- Màu nhấn đỏ điện ảnh giống Netflix.
- Card, table, form đều dùng surface tối có border nhẹ.
- Icon đi kèm hầu hết menu và hành động.
- Ưu tiên bố cục quản trị thực dụng, không dùng layout kiểu landing page.

## 3. Bảng Màu

### Core Colors

| Token | Màu | Dùng cho |
| --- | --- | --- |
| `--admin-bg` | `#0a0a0a` | Nền toàn trang admin |
| `--admin-surface` | `#151515` | Card, modal, vùng form |
| `--admin-surface-2` | `#202020` | Input, table hover, nested surface |
| `--admin-sidebar` | `#000000` | Sidebar |
| `--admin-border` | `#333333` | Border, divider |
| `--admin-text` | `#f5f5f5` | Text chính |
| `--admin-muted` | `#a7a7a7` | Text phụ |
| `--admin-danger` | `#e50914` | Màu nhấn chính, CTA, active menu |
| `--admin-danger-hover` | `#b20710` | Hover CTA đỏ |

### Semantic Colors

| Token | Màu | Dùng cho |
| --- | --- | --- |
| `--admin-success` | `#22c55e` | Thành công, vé đã xác nhận |
| `--admin-warning` | `#f59e0b` | Cảnh báo, sắp chiếu |
| `--admin-info` | `#38bdf8` | Sửa, thông tin phụ |
| `--admin-secondary` | `#64748b` | Nút phụ, trạng thái trung tính |

## 4. Typography

Project hiện tại đang dùng `Josefin Sans`. Có thể giữ để đồng bộ nhận diện, nhưng trong admin nên ưu tiên độ đọc:

- Heading/menu: `Josefin Sans`, weight `600-700`.
- Body/table/form: `Josefin Sans` hoặc fallback `"Segoe UI", Arial, sans-serif`.
- Không dùng chữ quá lớn trong dashboard/table.
- Không viết hoa toàn bộ quá nhiều, chỉ dùng cho nhãn nhỏ hoặc stat label.

Khuyến nghị:

```css
body {
    font-family: "Josefin Sans", "Segoe UI", Arial, sans-serif;
}
```

## 5. Layout Admin

### Sidebar

Sidebar là trục điều hướng chính:

- Fixed bên trái.
- Rộng `260px`.
- Cao `100vh`.
- Nền `#000000` hoặc gradient đen rất nhẹ.
- Border phải màu đỏ hoặc xám than.
- Brand nằm trên cùng, màu đỏ, có icon film.
- Menu item cao khoảng `44-48px`, có icon trước label.
- Active item cần nổi bật rõ bằng nền đỏ hoặc nền đỏ mờ + border trái đỏ.
- Logout nằm cuối sidebar.

Áp dụng cho:

- `admin/admin_sidebar.php`
- `css/admin.css`

### Content Area

Vùng nội dung:

- `margin-left: 260px`.
- Padding desktop: `28px-32px`.
- Nền toàn trang `#0a0a0a`.
- Mỗi trang nên có page header gồm title, mô tả ngắn nếu cần, và CTA chính ở bên phải.

Ví dụ page header:

```html
<div class="admin-page-header">
    <div>
        <h1>Quản lý phim</h1>
        <p>Theo dõi, thêm mới và chỉnh sửa thông tin phim.</p>
    </div>
    <button class="btn btn-netflix-red">
        <i class="bi bi-plus-lg"></i> Thêm phim
    </button>
</div>
```

## 6. Dashboard

Dashboard nên gồm:

- Hàng stat cards ở đầu trang.
- Bảng đặt vé mới nhất.
- Danh sách phim nổi bật hoặc phim mới cập nhật.
- Có thể thêm doanh thu/thống kê nếu dữ liệu sẵn có.

### Stat Card

Stat card nên giữ cảm giác giống project mẫu:

- Nền gradient tối nhẹ: `#151515` tới `#202020`.
- Border trái màu semantic.
- Icon lớn bên phải.
- Hover nâng nhẹ `translateY(-4px)`.
- Không dùng quá nhiều màu nền Bootstrap mặc định như `bg-primary`, `bg-success`; thay bằng border/icon màu để giữ tone tối.

Spec:

- Border radius: `8px`.
- Padding: `20px-24px`.
- Title nhỏ, màu muted.
- Số liệu lớn, màu trắng.
- Icon `Bootstrap Icons`.

## 7. Tables

Table là thành phần quan trọng nhất trong admin.

Quy tắc:

- Table đặt trong card/surface riêng.
- Header nền đen hoặc xám rất đậm.
- Header text màu đỏ hoặc trắng tùy mức nhấn.
- Row border màu `#333333`.
- Hover row nền `#202020`.
- Cột hành động nằm cuối, dùng icon button.
- Với bảng dài, luôn bọc bằng `.table-responsive`.

Nên tránh:

- Table nền trắng mặc định Bootstrap.
- Header `table-light` nếu chưa override dark theme.
- Nút text dài trong cột thao tác.

Action buttons:

- Sửa: `btn-outline-info` hoặc custom icon button info.
- Xóa: `btn-outline-danger`.
- Xem chi tiết: `btn-outline-secondary`.

## 8. Forms Và Modals

Admin hiện tại dùng modal để thêm/sửa. Có thể tiếp tục pattern này.

### Form Controls

Input/select/textarea:

- Nền `#202020`.
- Text `#f5f5f5`.
- Border `#3a3a3a`.
- Focus border đỏ `#e50914`.
- Focus shadow đỏ mờ.
- Label màu `#cccccc`.

### Modal

Modal:

- Nền `#151515`.
- Header/footer border `#333333`.
- Title rõ ràng, có icon nếu phù hợp.
- Button chính màu đỏ khi thêm mới.
- Button lưu thay đổi có thể dùng info hoặc đỏ, nhưng nên thống nhất: lưu chính dùng đỏ.

### Validation/Feedback

- Success alert dùng xanh nhưng nền tối.
- Error alert dùng đỏ.
- Thông báo cần nằm gần vùng thao tác.
- Khi xóa dữ liệu, luôn có confirm.

## 9. Buttons

### Primary CTA

Dùng cho hành động chính của trang:

- Thêm phim.
- Thêm thể loại.
- Lưu form.
- Xác nhận thao tác chính.

Style:

```css
.btn-netflix-red {
    background: #e50914;
    color: #fff;
    border: 1px solid #e50914;
}

.btn-netflix-red:hover {
    background: #b20710;
    border-color: #b20710;
    color: #fff;
}
```

### Secondary Buttons

Dùng cho hủy, quay lại, xem thêm:

- Nền trong suốt hoặc xám than.
- Border `#444`.
- Text muted hoặc trắng.

### Icon Buttons

Cột thao tác trong table nên dùng icon:

- Edit: `bi-pencil-square`.
- Delete: `bi-trash`.
- View: `bi-eye`.
- Booking: `bi-ticket-perforated`.

Nên có `title` cho icon button để dễ hiểu khi hover.

## 10. Badges Và Status

Status nên dùng badge có màu semantic:

| Trạng thái | Màu |
| --- | --- |
| Đang chiếu / confirmed / active | Green |
| Sắp chiếu / pending | Yellow |
| Đã kết thúc / cancelled / inactive | Gray hoặc Red |
| Admin role | Red |
| User role | Secondary |

Badge nên có:

- Border radius `999px`.
- Padding ngang `10px-12px`.
- Font size nhỏ nhưng đọc được.

## 11. UX Rules

### Điều Hướng

- Menu active phải luôn đúng theo trang hiện tại.
- Thứ tự menu đề xuất:
  1. Dashboard
  2. Quản lý Phim
  3. Quản lý Thể loại
  4. Quản lý Đặt vé
  5. Quản lý Người dùng
  6. Xem trang chủ
  7. Đăng xuất

### Page Actions

- CTA chính đặt trên cùng bên phải.
- Các action phụ nằm trong table row.
- Nút xóa không đặt quá gần nút lưu chính nếu có nguy cơ bấm nhầm.

### Empty States

Khi chưa có dữ liệu:

- Hiển thị dòng trống thân thiện.
- Có icon nhẹ.
- Có CTA thêm mới nếu phù hợp.

Ví dụ:

```html
<tr>
    <td colspan="8" class="text-center text-muted py-4">
        Chưa có phim nào. Hãy thêm phim đầu tiên.
    </td>
</tr>
```

### Responsive

Admin desktop là chính, nhưng vẫn cần không vỡ trên tablet/mobile:

- Dưới `992px`: sidebar có thể chuyển thành top/offcanvas menu.
- Table luôn có horizontal scroll.
- Page header chuyển thành column.
- Stat cards chuyển từ 4 cột sang 2 cột, rồi 1 cột.

## 12. Mapping Cho Project Hiện Tại

Các file admin hiện có:

- `admin/admin_header.php`: giữ Bootstrap 5, Bootstrap Icons, Google Font.
- `admin/admin_sidebar.php`: chuẩn hóa label tiếng Việt, thêm link xem trang chủ nếu cần.
- `admin/admin_footer.php`: giữ đóng layout và script Bootstrap.
- `css/admin.css`: nơi chính để gom toàn bộ design token và component styles.
- `admin/index.php`: dashboard, stat cards, recent data.
- `admin/manage_movies.php`: table + modal thêm/sửa phim.
- `admin/manage_genres.php`: table + modal thêm/sửa thể loại.
- `admin/manage_users.php`: table quản lý người dùng.
- `admin/manage_booking.php`: table quản lý đặt vé.

Ưu tiên refactor sau này:

1. Chuẩn hóa biến màu trong `css/admin.css`.
2. Làm lại sidebar active/hover theo tone mẫu.
3. Làm lại stat card để bỏ nền Bootstrap quá sáng.
4. Làm table dark nhất quán cho tất cả trang.
5. Làm form/modal dark nhất quán.
6. Sửa encoding tiếng Việt nếu file đang bị lỗi hiển thị.
7. Kiểm tra responsive cho dashboard và các bảng.

## 13. CSS Skeleton Đề Xuất

Khi bắt đầu code lại UI, có thể dùng skeleton sau trong `css/admin.css`:

```css
:root {
    --admin-bg: #0a0a0a;
    --admin-surface: #151515;
    --admin-surface-2: #202020;
    --admin-sidebar: #000000;
    --admin-border: #333333;
    --admin-text: #f5f5f5;
    --admin-muted: #a7a7a7;
    --admin-danger: #e50914;
    --admin-danger-hover: #b20710;
    --admin-success: #22c55e;
    --admin-warning: #f59e0b;
    --admin-info: #38bdf8;
    --admin-secondary: #64748b;
}

body {
    background: var(--admin-bg);
    color: var(--admin-text);
    font-family: "Josefin Sans", "Segoe UI", Arial, sans-serif;
}

.admin-sidebar {
    width: 260px;
    min-height: 100vh;
    position: fixed;
    inset: 0 auto 0 0;
    background: linear-gradient(180deg, #151515 0%, #000000 100%);
    border-right: 1px solid var(--admin-border);
}

.admin-content {
    margin-left: 260px;
    min-height: 100vh;
    padding: 32px;
}

.card,
.admin-surface {
    background: var(--admin-surface);
    border: 1px solid var(--admin-border);
    border-radius: 8px;
    color: var(--admin-text);
}
```

## 14. Checklist Khi Code Lại UI Admin

- [ ] Không thay đổi logic xử lý dữ liệu nếu task chỉ là UI.
- [ ] Tất cả trang admin dùng chung `admin_header.php`, `admin_sidebar.php`, `admin_footer.php`.
- [ ] Không viết CSS inline trừ trường hợp cực nhỏ.
- [ ] Tất cả màu lấy từ biến CSS.
- [ ] Sidebar active đúng trang.
- [ ] Dashboard stat cards dùng tone tối, không dùng màu Bootstrap mặc định quá sáng.
- [ ] Table, modal, form cùng dark theme.
- [ ] Button chính dùng đỏ `#e50914`.
- [ ] Button xóa có confirm.
- [ ] Có empty state cho bảng không có dữ liệu.
- [ ] Kiểm tra trên desktop và mobile/tablet.
