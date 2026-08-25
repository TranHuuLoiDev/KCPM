# KAN-35 — BOOKING TEST PLAN & TEST CASES

## 1. Test Scope

Phạm vi kiểm thử KAN-35 tập trung vào Booking flow:

- Tạo booking.
- Kiểm tra showtime.
- Kiểm tra seat availability.
- Kiểm tra seat thuộc đúng room của showtime.
- Kiểm tra seat đã được đặt.
- Tính tổng tiền booking.
- Tạo ticket.
- Kiểm tra booking status.
- Lấy booking của user.
- Lấy booking detail.
- Hủy booking.
- Kiểm tra quyền sở hữu booking khi cancel.
- Xử lý các trường hợp dữ liệu không hợp lệ.
- Kiểm tra transaction và rollback ở các trường hợp lỗi phù hợp.

---

# 2. TEST CASE MATRIX

| ID | Jira | Test Case | Type | Level | Preconditions | Test Data | Expected Result | Actual Result | Status |
|---|---|---|---|---|---|---|---|---|---|
| TC-BOOK-001 | KAN-35 | Tạo booking với thông tin hợp lệ | Positive | API | User đăng nhập, showtime tồn tại, ghế còn trống | Valid user + showtime + available seats | Booking thành công, tạo ticket tương ứng | - | NOT RUN |
| TC-BOOK-002 | KAN-35 | Tạo booking với showtime không tồn tại | Negative | API | User đăng nhập | Invalid showtime ID | Booking bị từ chối | - | NOT RUN |
| TC-BOOK-003 | KAN-35 | Tạo booking với user không hợp lệ | Negative | API/Unit | User không hợp lệ | Invalid user ID | Booking bị từ chối | - | NOT RUN |
| TC-BOOK-004 | KAN-35 | Tạo booking không có ghế | Validation | API/Unit | User và showtime hợp lệ | Empty seat list | Booking bị từ chối | - | NOT RUN |
| TC-BOOK-005 | KAN-35 | Tạo booking với seat ID không tồn tại | Validation | API/Unit | User và showtime hợp lệ | Invalid seat ID | Booking bị từ chối | - | NOT RUN |
| TC-BOOK-006 | KAN-35 | Tạo booking với ghế đã được đặt | Business Rule | API/Unit | Ghế đã có ticket hợp lệ | Already booked seat | Không cho phép đặt trùng ghế | - | NOT RUN |
| TC-BOOK-007 | KAN-35 | Tạo booking với nhiều ghế còn trống | Positive | API | User và showtime hợp lệ | Multiple available seats | Booking thành công, tạo ticket cho từng ghế | - | NOT RUN |
| TC-BOOK-008 | KAN-35 | Tạo booking với payment method hợp lệ | Positive | API | Booking data hợp lệ | Supported payment method | Booking được xử lý với payment method hợp lệ | - | NOT RUN |
| TC-BOOK-009 | KAN-35 | Tạo booking với payment method không hợp lệ | Validation | API/Unit | User và showtime hợp lệ | Unsupported payment method | Payment method được xử lý theo business rule hiện tại | - | NOT RUN |
| TC-BOOK-010 | KAN-35 | Kiểm tra tổng tiền booking | Business Rule | Unit/API | Booking có seat hợp lệ | Base price + seat type price | Tổng tiền được tính chính xác | - | NOT RUN |
| TC-BOOK-011 | KAN-35 | Kiểm tra ticket được tạo sau booking | Positive | API/DB | Booking thành công | Valid booking | Ticket được tạo tương ứng với booking và seat | - | NOT RUN |
| TC-BOOK-012 | KAN-35 | Kiểm tra trạng thái booking sau khi tạo | Business Rule | API/DB | Booking thành công | Valid booking | Booking có status đúng theo implementation | - | NOT RUN |
| TC-BOOK-013 | KAN-35 | Lấy danh sách booking của user | Positive | API | User có booking | Valid user ID | Trả về các booking thuộc user | - | NOT RUN |
| TC-BOOK-014 | KAN-35 | Lấy booking của user không có dữ liệu | Boundary | API | User tồn tại nhưng chưa booking | Valid user ID | Trả về danh sách rỗng | - | NOT RUN |
| TC-BOOK-015 | KAN-35 | Lấy booking detail theo booking ID | Positive | API | Booking tồn tại | Valid booking ID | Trả về đúng thông tin booking và ticket | - | NOT RUN |
| TC-BOOK-016 | KAN-35 | Lấy booking với ID không tồn tại | Negative | API | - | Invalid booking ID | Không trả về booking hoặc trả lỗi phù hợp | - | NOT RUN |
| TC-BOOK-017 | KAN-35 | User hủy booking của chính mình | Positive | API | User sở hữu booking | Valid booking ID | Booking được hủy thành công | - | NOT RUN |
| TC-BOOK-018 | KAN-35 | User hủy booking không thuộc quyền sở hữu | Authorization | API/Unit | Booking thuộc user khác | Other user's booking ID | Không cho phép hủy booking | - | NOT RUN |
| TC-BOOK-019 | KAN-35 | Hủy booking không tồn tại | Negative | API | - | Invalid booking ID | Thao tác hủy thất bại | - | NOT RUN |
| TC-BOOK-020 | KAN-35 | Hủy booking ở trạng thái không thể hủy | Business Rule | API/Unit | Booking ở trạng thái không cho phép cancel | Existing booking | Không cho phép hủy và trả lỗi phù hợp | - | NOT RUN |

---

# 3. TEST LEVEL

## 3.1 Unit Test

Tập trung kiểm tra business logic của BookingService:

- Validation.
- User validation.
- Showtime validation.
- Seat validation.
- Seat availability.
- Seat-room relationship.
- Duplicate seat handling.
- Total amount calculation.
- Booking creation.
- Ticket creation.
- Cancel booking.
- Authorization khi cancel.
- Business rules.
- Error handling.
- Transaction/rollback ở các nhánh có thể kiểm tra được.

## 3.2 Postman API Test

Kiểm tra flow thực tế thông qua API:

Request
→ Controller
→ BookingService
→ Model/Database
→ Response

Postman không chỉ dùng để gửi request thủ công.

Sử dụng:

- Collection.
- Environment variables.
- Pre-request Script khi cần.
- Tests Script.
- Assertions.
- Collection Runner.
- Result Log.

Các API test phải kiểm tra response bằng script thay vì chỉ nhìn response bằng mắt.

## 3.3 Database Verification

Kiểm tra database đối với các test case quan trọng:

- Booking được tạo.
- Ticket được tạo.
- Seat được ghi nhận đã đặt.
- Booking status được cập nhật.
- Booking bị cancel.
- Ticket status được cập nhật phù hợp.

---

# 4. POSTMAN AUTOMATION FLOW

Collection
→ Environment
→ Pre-request Script
→ Send Request
→ Tests Script
→ Assertions
→ Collection Runner
→ Result Log
→ API Log
→ Database Verification
→ Evidence

Mục tiêu là có thể chạy nhiều test case liên tiếp bằng Collection Runner thay vì gửi từng request và kiểm tra thủ công.

---

# 5. TEST RESULT

Sau khi chạy test, cập nhật:

- Actual Result.
- Status.
- Evidence.

Status sử dụng:

- PASS
- FAIL
- BLOCKED
- NOT RUN

Nếu test FAIL:

Test Case
→ Collect Evidence
→ Create Jira Bug
→ Fix
→ Retest
→ PASS

---

# 6. EVIDENCE

Evidence KAN-35 tập trung vào kết quả kiểm thử:

1. Unit Test execution result.
2. Postman Collection Runner result.
3. Postman API response/test result.
4. Database verification cho các test case quan trọng.
5. Bug evidence nếu phát hiện lỗi.

Không cần chụp toàn bộ source code để làm evidence.

---

# 7. KAN-35 WORKFLOW

## Step 1 — Jira Task

Xác định phạm vi KAN-35 và acceptance criteria.

## Step 2 — Create Test Branch

Tạo branch riêng cho task test KAN-35.

Ví dụ:

test/KAN-35-booking-testing

## Step 3 — Review Implementation

Đọc và phân tích:

- BookingController.
- BookingService.
- BookingModel.
- TicketModel.
- ShowtimeModel.
- SeatModel.
- Các API liên quan đến Booking.

Xác định business rules và các nhánh cần kiểm thử.

## Step 4 — Design Test Cases

Hoàn thiện Test Case Matrix.

Phân loại:

- Positive.
- Negative.
- Validation.
- Boundary.
- Business Rule.
- Authorization.
- Error Handling.

## Step 5 — Implement Unit Test

Tạo/hoàn thiện test class cho Booking.

Ví dụ:

tests/Services/BookingServiceTest.php

Viết test theo các business rule đã xác định.

## Step 6 — Run Unit Test

Chạy test riêng của Booking trước.

Sau đó chạy toàn bộ PHPUnit suite.

Ghi nhận:

- Number of tests.
- Assertions.
- PASS/FAIL.
- Errors/Warnings.

## Step 7 — Create Postman Collection

Tạo collection:

KAN-35 — Booking Testing

Thiết lập Environment Variables cho:

- Base URL.
- User ID.
- Showtime ID.
- Seat IDs.
- Booking ID.
- Các dữ liệu cần thiết khác.

## Step 8 — Write Postman Test Scripts

Mỗi request quan trọng phải có Tests Script để tự động kiểm tra:

- HTTP status.
- Response structure.
- Success/error status.
- Message.
- Required fields.
- Booking ID.
- Ticket data.
- Business rules.

## Step 9 — Run Collection Runner

Chạy toàn bộ collection bằng Collection Runner.

Không chạy từng request thủ công nếu các test có thể được tự động hóa.

Ghi nhận:

- Passed.
- Failed.
- Response.
- Test assertions.
- Execution log.

## Step 10 — Database Verification

Đối chiếu database với kết quả API.

Đặc biệt kiểm tra:

- Booking.
- Booking details.
- Tickets.
- Status.
- Seat availability.

## Step 11 — Evidence

Lưu evidence theo cấu trúc của project.

Ví dụ:

docs/evidence/huutien/booking/KAN-35/

after/

- unit-test-result.png
- postman-runner-result.png
- postman-test-result.png
- database-verification.png

Tên file thực tế sẽ chốt theo evidence phát sinh trong quá trình test.

## Step 12 — Update Test Report

Cập nhật:

- Test Case Result.
- Actual Result.
- PASS/FAIL.
- Evidence path.
- Unit Test result.
- Postman result.
- Database verification.
- Bugs nếu có.

## Step 13 — Commit & Push

Commit toàn bộ thay đổi của task KAN-35.

Push lên branch test/KAN-35-booking-testing.

## Step 14 — Pull Request

Tạo PR để review.

PR phải mô tả:

- Summary.
- Tests performed.
- Test results.
- Evidence.
- Bugs/fixes nếu có.

## Step 15 — CI / Quality Check

Kiểm tra:

- PHPUnit.
- GitHub Actions.
- SonarCloud nếu workflow project yêu cầu.

## Step 16 — Merge & Jira

Sau khi PR được approve và checks PASS:

- Merge PR.
- Update Jira KAN-35.
- Đính kèm evidence cần thiết.
- Chuyển task sang Done khi toàn bộ acceptance criteria hoàn thành.

---

# 8. DEFINITION OF DONE

KAN-35 hoàn thành khi:

- [ ] Test cases được chốt.
- [ ] Unit tests được implement.
- [ ] Unit tests PASS.
- [ ] Postman Collection được tạo.
- [ ] Environment được cấu hình.
- [ ] Postman Tests Script được viết.
- [ ] Collection Runner chạy thành công.
- [ ] API result được ghi nhận.
- [ ] Database verification hoàn thành cho các case cần thiết.
- [ ] Evidence được lưu.
- [ ] TEST_REPORT.md được cập nhật.
- [ ] Nếu có bug: tạo Jira Bug, fix và retest.
- [ ] Commit.
- [ ] Push.
- [ ] Pull Request.
- [ ] CI/quality checks PASS.
- [ ] Review hoàn tất.
- [ ] Merge.
- [ ] Jira KAN-35 được cập nhật Done.

---

# 9. TRẠNG THÁI HIỆN TẠI

KAN-35 đã hoàn thành bước:

- Chuyển sang branch KAN-35.
- Đọc và phân tích Booking implementation.
- Xác định phạm vi test.
- Chốt Test Case Matrix.
- Xác định chiến lược Unit Test + Postman Automation + Database Verification.

BƯỚC TIẾP THEO:

→ Implement Unit Test cho BookingService.

