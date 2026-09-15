# Báo cáo kiểm thử theo form Assignment

Các báo cáo được soạn lần lượt từ source service, controller, model, schema, PHPUnit và Postman hiện có; ngày rà soát 2026-09-15.

| Module | Báo cáo | Số dòng thiết kế chính | Phân loại |
|---|---|---:|---|
| Room | [Room_BaoCao_Form_Assignment.md](room/Room_BaoCao_Form_Assignment.md) | 40 | 15 kiểm tra cận dưới + 25 EP; Standard BVA đóng N/A |
| Review | [Review_BaoCao_Form_Assignment.md](review/Review_BaoCao_Form_Assignment.md) | 25 | 10 Standard BVA + 15 EP |
| Booking | [Booking_BaoCao_Form_Assignment.md](booking/Booking_BaoCao_Form_Assignment.md) | 52 | 6 ngưỡng thời gian + 46 EP; thêm 6 fault-injection case ngoài tổng |

Mẫu cấu trúc: [Seat Assignment](seat/083205006374_LuongQuocAn_BaoCao_Seat_Form_Assignment_Chot.md).

## Cách đọc

Mỗi báo cáo có inventory method/file, lý do chọn method, input và fixture, EP theo form Assignment, phân tích biên, bảng test, mapping automation, evidence và kết luận. Mã case đánh lại theo method, nên khi trao đổi cần kèm module/method.

“Có test tương đương” nghĩa là source test phủ cùng partition; không mặc định literal input, fixture, assertion hoặc số dòng thiết kế trùng nhau. Mọi case chưa có automation được ghi rõ; kết quả lịch sử được tách khỏi lần chạy hiện tại.

## Kết quả rà soát đáng chú ý

- Room không đặt upper bound cho total_seats. Evidence cũ 19 tests/31 assertions và Newman 4/4 không chứng minh 40 case mới. Nhánh theatre_id<=0 chưa được test bằng ID 999999.
- Review validation chỉ kiểm tra rating. Bộ hiện có thiếu nominal=3 tại validateReviewInput, chưa chứng minh thêm đánh giá thành công hoặc thống kê trung bình.
- BookingServiceTest vừa chạy lại: **53 tests, 116 assertions, PASS**; dùng mock model. Các route /validate* do Postman Booking sinh chưa đi qua BookingService như tên gọi, nên không thể dùng chúng làm evidence service.

Lần soạn này không sửa source nghiệp vụ/test, không chạy lại integration DB hoặc Newman. Các việc bổ sung automation và sửa route được ghi trong từng báo cáo để thực hiện riêng. Tài liệu Room cũ được giữ nguyên làm tham chiếu lịch sử; dùng báo cáo mới khi cần thiết kế đầy đủ và các hiệu chỉnh mapping.
