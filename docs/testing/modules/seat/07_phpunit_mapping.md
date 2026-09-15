# Mapping PHPUnit ↔ Assignment hiện hành

Nguồn: [báo cáo 75 case](083205006374_LuongQuocAn_BaoCao_Seat_Form_Assignment_Chot.md).

Test: `backend/tests/Services/SeatAssignmentTest.php::testAssignmentCase`, data provider `assignmentCases` đọc trực tiếp sáu bảng trong Markdown. Provider kiểm tra đủ từng mã liên tiếp và số lượng 10/8, 15/10, 20/12; thiếu/trùng mã làm suite báo lỗi.

Fixture mới mỗi dataset, room/type 1 tồn tại, 999999 không tồn tại. Generate thành công kiểm tra từng vị trí và dữ liệu insert; trùng toàn bộ không được insert. BulkDelete kiểm tra tham số range, giữ ghế ngoài range và phòng khác, kiểm tra tổng ghế còn lại. Validation lỗi không được ghi dữ liệu. Mọi case assert status và thông báo chính xác.

Kết quả lần chạy 2026-09-15: **75 tests, 307 assertions, 0 failures, 0 errors**. Evidence: [assignment-junit.xml](assignment-junit.xml). Đây là unit test service dùng model doubles; chưa xác nhận SQL/HTTP integration.

| Method | Dòng Markdown | Dataset PHPUnit | Kết quả |
|---|---|---|---|
| validateSeatInput | BVA-01 | validateSeatInput/BVA-01 | PASS |
| validateSeatInput | BVA-02 | validateSeatInput/BVA-02 | PASS |
| validateSeatInput | BVA-03 | validateSeatInput/BVA-03 | PASS |
| validateSeatInput | BVA-04 | validateSeatInput/BVA-04 | PASS |
| validateSeatInput | BVA-05 | validateSeatInput/BVA-05 | PASS |
| validateSeatInput | BVA-06 | validateSeatInput/BVA-06 | PASS |
| validateSeatInput | BVA-07 | validateSeatInput/BVA-07 | PASS |
| validateSeatInput | BVA-08 | validateSeatInput/BVA-08 | PASS |
| validateSeatInput | BVA-09 | validateSeatInput/BVA-09 | PASS |
| validateSeatInput | BVA-10 | validateSeatInput/BVA-10 | PASS |
| validateSeatInput | EP-01 | validateSeatInput/EP-01 | PASS |
| validateSeatInput | EP-02 | validateSeatInput/EP-02 | PASS |
| validateSeatInput | EP-03 | validateSeatInput/EP-03 | PASS |
| validateSeatInput | EP-04 | validateSeatInput/EP-04 | PASS |
| validateSeatInput | EP-05 | validateSeatInput/EP-05 | PASS |
| validateSeatInput | EP-06 | validateSeatInput/EP-06 | PASS |
| validateSeatInput | EP-07 | validateSeatInput/EP-07 | PASS |
| validateSeatInput | EP-08 | validateSeatInput/EP-08 | PASS |
| generateSeats | BVA-01 | generateSeats/BVA-01 | PASS |
| generateSeats | BVA-02 | generateSeats/BVA-02 | PASS |
| generateSeats | BVA-03 | generateSeats/BVA-03 | PASS |
| generateSeats | BVA-04 | generateSeats/BVA-04 | PASS |
| generateSeats | BVA-05 | generateSeats/BVA-05 | PASS |
| generateSeats | BVA-06 | generateSeats/BVA-06 | PASS |
| generateSeats | BVA-07 | generateSeats/BVA-07 | PASS |
| generateSeats | BVA-08 | generateSeats/BVA-08 | PASS |
| generateSeats | BVA-09 | generateSeats/BVA-09 | PASS |
| generateSeats | BVA-10 | generateSeats/BVA-10 | PASS |
| generateSeats | BVA-11 | generateSeats/BVA-11 | PASS |
| generateSeats | BVA-12 | generateSeats/BVA-12 | PASS |
| generateSeats | BVA-13 | generateSeats/BVA-13 | PASS |
| generateSeats | BVA-14 | generateSeats/BVA-14 | PASS |
| generateSeats | BVA-15 | generateSeats/BVA-15 | PASS |
| generateSeats | EP-01 | generateSeats/EP-01 | PASS |
| generateSeats | EP-02 | generateSeats/EP-02 | PASS |
| generateSeats | EP-03 | generateSeats/EP-03 | PASS |
| generateSeats | EP-04 | generateSeats/EP-04 | PASS |
| generateSeats | EP-05 | generateSeats/EP-05 | PASS |
| generateSeats | EP-06 | generateSeats/EP-06 | PASS |
| generateSeats | EP-07 | generateSeats/EP-07 | PASS |
| generateSeats | EP-08 | generateSeats/EP-08 | PASS |
| generateSeats | EP-09 | generateSeats/EP-09 | PASS |
| generateSeats | EP-10 | generateSeats/EP-10 | PASS |
| bulkDeleteSeats | BVA-01 | bulkDeleteSeats/BVA-01 | PASS |
| bulkDeleteSeats | BVA-02 | bulkDeleteSeats/BVA-02 | PASS |
| bulkDeleteSeats | BVA-03 | bulkDeleteSeats/BVA-03 | PASS |
| bulkDeleteSeats | BVA-04 | bulkDeleteSeats/BVA-04 | PASS |
| bulkDeleteSeats | BVA-05 | bulkDeleteSeats/BVA-05 | PASS |
| bulkDeleteSeats | BVA-06 | bulkDeleteSeats/BVA-06 | PASS |
| bulkDeleteSeats | BVA-07 | bulkDeleteSeats/BVA-07 | PASS |
| bulkDeleteSeats | BVA-08 | bulkDeleteSeats/BVA-08 | PASS |
| bulkDeleteSeats | BVA-09 | bulkDeleteSeats/BVA-09 | PASS |
| bulkDeleteSeats | BVA-10 | bulkDeleteSeats/BVA-10 | PASS |
| bulkDeleteSeats | BVA-11 | bulkDeleteSeats/BVA-11 | PASS |
| bulkDeleteSeats | BVA-12 | bulkDeleteSeats/BVA-12 | PASS |
| bulkDeleteSeats | BVA-13 | bulkDeleteSeats/BVA-13 | PASS |
| bulkDeleteSeats | BVA-14 | bulkDeleteSeats/BVA-14 | PASS |
| bulkDeleteSeats | BVA-15 | bulkDeleteSeats/BVA-15 | PASS |
| bulkDeleteSeats | BVA-16 | bulkDeleteSeats/BVA-16 | PASS |
| bulkDeleteSeats | BVA-17 | bulkDeleteSeats/BVA-17 | PASS |
| bulkDeleteSeats | BVA-18 | bulkDeleteSeats/BVA-18 | PASS |
| bulkDeleteSeats | BVA-19 | bulkDeleteSeats/BVA-19 | PASS |
| bulkDeleteSeats | BVA-20 | bulkDeleteSeats/BVA-20 | PASS |
| bulkDeleteSeats | EP-01 | bulkDeleteSeats/EP-01 | PASS |
| bulkDeleteSeats | EP-02 | bulkDeleteSeats/EP-02 | PASS |
| bulkDeleteSeats | EP-03 | bulkDeleteSeats/EP-03 | PASS |
| bulkDeleteSeats | EP-04 | bulkDeleteSeats/EP-04 | PASS |
| bulkDeleteSeats | EP-05 | bulkDeleteSeats/EP-05 | PASS |
| bulkDeleteSeats | EP-06 | bulkDeleteSeats/EP-06 | PASS |
| bulkDeleteSeats | EP-07 | bulkDeleteSeats/EP-07 | PASS |
| bulkDeleteSeats | EP-08 | bulkDeleteSeats/EP-08 | PASS |
| bulkDeleteSeats | EP-09 | bulkDeleteSeats/EP-09 | PASS |
| bulkDeleteSeats | EP-10 | bulkDeleteSeats/EP-10 | PASS |
| bulkDeleteSeats | EP-11 | bulkDeleteSeats/EP-11 | PASS |
| bulkDeleteSeats | EP-12 | bulkDeleteSeats/EP-12 | PASS |

Postman dùng SEAT-VALIDATE-BVA-xx / SEAT-VALIDATE-EP-xx cho 18 case validation. Không có HTTP mapping cho generate/bulkDelete trong collection hiện tại.
