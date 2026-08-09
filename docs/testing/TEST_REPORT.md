### KAN-27 — Authentication: Login & Authentication Validation

#### 1. Unit Test

| Test Suite              | Result                           | Evidence                                                                          |
| ----------------------- | -------------------------------- | --------------------------------------------------------------------------------- |
| AuthService Unit Test   | PASS — 13 tests / 43 assertions  | `docs/evidence/huutien/authentication/KAN-27/after/01-auth-service-unit-test.png` |
| Full PHPUnit Test Suite | PASS — 74 tests / 155 assertions | `docs/evidence/huutien/authentication/KAN-27/after/02-full-unit-test-suite.png`   |

#### Unit Test Result

* `AuthServicesTest.php`: 13 tests passed, 43 assertions.
* Full PHPUnit test suite: 74 tests passed, 155 assertions.
* No test failure or error occurred.
* PHPUnit reported `No code coverage driver available`; this is an environment warning and does not affect test execution.

---

#### 2. API Test — Postman

Authentication API was tested using Postman through:

* `POST /api.php/login`
* `POST /api.php/register`

The API test suite covers:

* Successful login.
* Invalid login credentials.
* Login input validation.
* Admin login.
* Successful registration.
* Registration input validation.
* Duplicate email and phone validation.

#### Postman Execution Result

| Metric               |            Result |
| -------------------- | ----------------: |
| Total API Test Cases |                14 |
| Passed               |                14 |
| Failed               |                 0 |
| Result               |              PASS |
| Tool                 |           Postman |
| Execution Method     | Collection Runner |

#### Postman Evidence

| Evidence                              | File                                                                                 |
| ------------------------------------- | ------------------------------------------------------------------------------------ |
| Login API test execution              | `docs/evidence/huutien/authentication/KAN-27/after/03-postman-login-test.png`        |
| Full Authentication Collection Runner | `docs/evidence/huutien/authentication/KAN-27/after/04-postman-collection-runner.png` |

---

#### 3. Authentication Test Case Result

| ID          | Jira   | Test Case                                 | Type          | Level       | Input JSON                                                                                                                                                                           | Expected Result                    | Actual Result                    | Status |
| ----------- | ------ | ----------------------------------------- | ------------- | ----------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ---------------------------------- | -------------------------------- | ------ |
| TC-AUTH-001 | KAN-27 | Login với thông tin hợp lệ                | Positive      | API         | `{"email":"testauth01@gmail.com","password":"123456"}`                                                                                                                               | Login thành công                   | Login thành công                 | PASS   |
| TC-AUTH-002 | KAN-27 | Login với password sai                    | Negative      | API         | `{"email":"testauth01@gmail.com","password":"hhahhaha"}`                                                                                                                             | Login thất bại                     | Login bị từ chối                 | PASS   |
| TC-AUTH-003 | KAN-27 | Login với email không tồn tại             | Negative      | API         | `{"email":"hellllooo01@gmail.com","password":"123456"}`                                                                                                                              | Login thất bại                     | Login bị từ chối                 | PASS   |
| TC-AUTH-004 | KAN-27 | Login thiếu email                         | Validation    | API         | `{"email":"","password":"123456"}`                                                                                                                                                   | Login bị từ chối                   | Request bị từ chối               | PASS   |
| TC-AUTH-005 | KAN-27 | Login thiếu password                      | Validation    | API         | `{"email":"testauth01@gmail.com","password":""}`                                                                                                                                     | Login bị từ chối                   | Request bị từ chối               | PASS   |
| TC-AUTH-006 | KAN-27 | Login với email không hợp lệ              | Validation    | API         | `{"email":"testauth01gmail","password":"123456"}`                                                                                                                                    | Login bị từ chối                   | Request bị từ chối               | PASS   |
| TC-AUTH-007 | KAN-27 | Login bằng tài khoản admin                | Authorization | API         | `{"email":"admin@example.com","password":"password"}`                                                                                                                                | Login thành công, role = admin     | Login thành công với role admin  | PASS   |
| TC-AUTH-008 | KAN-27 | Logout sau khi login                      | Functional    | Application | `Session: authenticated user`                                                                                                                                                        | Session user bị xóa                | Được kiểm tra ở application flow | PASS*  |
| TC-AUTH-009 | KAN-27 | Register với thông tin hợp lệ             | Positive      | API         | `{"first_name":"Test","last_name":"User","email":"postman.test.00001@example.com","phone":"0997654329","birth_date":"2000-01-01","password":"123456","confirm_password":"123456"}`   | Tạo tài khoản thành công           | Tài khoản được tạo thành công    | PASS   |
| TC-AUTH-010 | KAN-27 | Register thiếu thông tin bắt buộc         | Validation    | API         | `{"first_name":"","last_name":"User","email":"postman.test.002@example.com","phone":"0987654322","birth_date":"2000-01-01","password":"123456","confirm_password":"123456"}`         | Registration bị từ chối            | Request bị từ chối               | PASS   |
| TC-AUTH-011 | KAN-27 | Register email không hợp lệ               | Validation    | API         | `{"first_name":"Test","last_name":"User","email":"invalid-email","phone":"0987654323","birth_date":"2000-01-01","password":"123456","confirm_password":"123456"}`                    | Registration bị từ chối            | Request bị từ chối               | PASS   |
| TC-AUTH-012 | KAN-27 | Register password confirmation không khớp | Validation    | API         | `{"first_name":"Test","last_name":"User","email":"postman.test.003@example.com","phone":"0987654324","birth_date":"2000-01-01","password":"123456","confirm_password":"654321"}`     | Registration bị từ chối            | Request bị từ chối               | PASS   |
| TC-AUTH-013 | KAN-27 | Register password dưới 6 ký tự            | Boundary      | API         | `{"first_name":"Test","last_name":"User","email":"postman.test.004@example.com","phone":"0987654325","birth_date":"2000-01-01","password":"12345","confirm_password":"12345"}`       | Registration bị từ chối            | Request bị từ chối               | PASS   |
| TC-AUTH-014 | KAN-27 | Register email đã tồn tại                 | Business Rule | API         | `{"first_name":"Test","last_name":"Existing","email":"postman.test.001@example.com","phone":"0987654326","birth_date":"2000-01-01","password":"123456","confirm_password":"123456"}` | Registration bị từ chối            | Request bị từ chối               | PASS   |
| TC-AUTH-015 | KAN-27 | Register phone đã tồn tại                 | Business Rule | API         | `{"first_name":"Test","last_name":"Existing","email":"user@test1111.com","phone":"0987654321","birth_date":"2000-01-01","password":"123456","confirm_password":"123456"}`            | Registration bị từ chối            | Request bị từ chối               | PASS   |
| TC-AUTH-016 | KAN-27 | Password được hash khi register           | Security      | Unit        | `password = "123456"`                                                                                                                                                                | Password được hash trước khi lưu   | Được kiểm tra bằng PHPUnit       | PASS   |
| TC-AUTH-017 | KAN-27 | Password verification khi login           | Security      | Unit        | `correct / incorrect password`                                                                                                                                                       | `password_verify()` hoạt động đúng | Được kiểm tra bằng PHPUnit       | PASS   |
| TC-AUTH-018 | KAN-27 | Đổi password với password hiện tại sai    | Negative      | Unit        | `wrong current password`                                                                                                                                                             | Không đổi password                 | Được kiểm tra bằng PHPUnit       | PASS   |
| TC-AUTH-019 | KAN-27 | Đổi password với confirmation không khớp  | Validation    | Unit        | `new password ≠ confirmation`                                                                                                                                                        | Không đổi password                 | Được kiểm tra bằng PHPUnit       | PASS   |
| TC-AUTH-020 | KAN-27 | Đổi password thành công                   | Positive      | Unit        | `valid current + new password`                                                                                                                                                       | Password được cập nhật             | Được kiểm tra bằng PHPUnit       | PASS   |

> `*` TC-AUTH-008 không phải API test trong Postman vì API hiện tại không expose endpoint logout riêng. Case được kiểm tra ở application/service flow.

---

#### 4. Overall Test Progress

| Test Level                       |   Test Cases | Passed | Failed | Result   |
| -------------------------------- | -----------: | -----: | -----: | -------- |
| Unit Test                        |     13 tests |     13 |      0 | PASS     |
| API Test — Postman               |           14 |     14 |      0 | PASS     |
| Full PHPUnit Suite               |     74 tests |     74 |      0 | PASS     |
| **Authentication Test Coverage** | **20 cases** | **20** |  **0** | **PASS** |

#### Overall Result

* KAN-27 Authentication testing completed successfully.
* 14 API test cases were executed using Postman Collection Runner.
* All 14 API test cases passed.
* `AuthServicesTest.php` passed with 13 tests and 43 assertions.
* Full PHPUnit test suite passed with 74 tests and 155 assertions.
* No test failure or error was detected.
* Authentication validation covers login, registration, credential validation, duplicate account data, password security, logout flow and password change validation.
* Test evidence has been stored under:
  `docs/evidence/huutien/authentication/KAN-27/after/`
