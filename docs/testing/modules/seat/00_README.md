# Kiểm thử Seat — bản hiện hành

Báo cáo và dữ liệu test dùng chung: [Assignment 75 case](083205006374_LuongQuocAn_BaoCao_Seat_Form_Assignment_Chot.md).

| Method | BVA | EP | PHPUnit |
|---|---:|---:|---:|
| validateSeatInput | 10 | 8 | 18/18 PASS |
| generateSeats | 15 | 10 | 25/25 PASS |
| bulkDeleteSeats | 20 | 12 | 32/32 PASS |
| Tổng | 45 | 30 | 75/75 PASS |

Kết quả thực thi: **75 tests, 307 assertions**. [JUnit từng case](assignment-junit.xml) và [mapping](07_phpunit_mapping.md).

## Chạy lại

Từ thư mục backend:

```powershell
php vendor/bin/phpunit tests/Services/SeatAssignmentTest.php --no-coverage --testdox --log-junit ../docs/testing/modules/seat/assignment-junit.xml
```

Từ thư mục gốc, sinh collection chứa 18 case validation:

```powershell
node tests/bva/generate-postman.js
node tests/automation/run-bva-and-log.js "BVA - Seat"
```

Newman cần API hoạt động, room/type 1 tồn tại và ID 999999 không tồn tại. Chưa chạy lại Newman trong lần rà soát này.

PHPUnit Assignment dùng model doubles và fixture mới mỗi case, không kết nối MySQL. Test gọi service thật và kiểm tra dữ liệu insert, range delete, số lượng ghế, status, message, đồng bộ tổng ghế; không thay thế kiểm thử SQL thực tế.

Các tài liệu 01–06, 08–10 được giữ kèm nhãn phạm vi lịch sử để bảo toàn evidence và mã TC cũ. Thiết kế hiện hành và các bảng V/X/B nằm trong báo cáo Assignment. Các mã TC-SEAT-* cũ không tương đương trực tiếp với BVA-xx/EP-xx mới; tên dataset luôn có tiền tố method để tránh trùng mã.

## Rà soát các file test

- SeatServiceTest.php: 25 regression test, gồm 12 validation cũ; chưa đủ 18 input chính xác. Lần chạy lại gặp MySQL connection refused.
- SeatServiceCrudTest.php: 20 integration test; giữ kiểm thử CRUD ngoài Assignment. Chưa chạy lại khi database chưa khả dụng.
- SeatControllerTest.php: 14 test điều phối request/default/guard, không thay thế service case.
- seat_bva_test.js: UI smoke test, đã sửa tên Feature để phản ánh đúng phạm vi.
- bva-cases.js, seat-assignment-cases.js, generate-postman.js, collection chính: đồng bộ 18 dòng validation từ Markdown.
- run-bva-and-log.js: lấy field theo từng case để log đúng seat_row, room_id, seat_type_id.
- Collection EP ở gốc: 3 case số ghế bổ sung thuộc bộ cũ; collection movie-ticket-booking và các bản _nam/_toan không cung cấp bộ 75 case này.
- Test các module Booking, Room, Theatre, Movie, Review, Authentication cùng test-api.php/frontend/test_booking.php nằm ngoài ba method được giao, giữ phạm vi riêng.

Không sửa source nghiệp vụ: 75 case đều đạt trên SeatService hiện tại. Những case EP vi phạm đồng thời nhiều điều kiện được giải thích trong mục 8.4 của báo cáo.