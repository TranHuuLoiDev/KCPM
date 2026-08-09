## Database

### TC-DB-001 — Execute movie seed script successfully

- **Jira:** KAN-15
- **Type:** Functional
- **Level:** Database
- **Objective:** Verify that the movie seed SQL script executes successfully after refactoring.

#### Preconditions
- MySQL database is available.
- Movie seed SQL script is available.

#### Steps
1. Execute the movie seed SQL script.
2. Observe the execution result.

#### Expected Result
- SQL script executes successfully.
- No SQL syntax or runtime error occurs.

#### Actual Result
- Movie seed SQL script executed successfully.
- No SQL syntax or runtime error occurred.
- Database records were inserted/updated successfully.

#### Status
`PASS`

---

### TC-DB-002 — Verify movie status after seed

- **Jira:** KAN-15
- **Type:** Data Verification
- **Level:** Database
- **Objective:** Verify that the refactoring does not change the `now_showing` movie status.

#### Preconditions
- Movie seed SQL script has been executed successfully.

#### Steps
1. Query movies with status `now_showing`.
2. Compare the result with the expected movie data.

#### Expected Result
- Movies previously using `now_showing` still have:
  `status = 'now_showing'`.
- No unrelated movie data is changed.

#### Actual Result

- Movie seed SQL script executed successfully.
- Movies with `status = 'now_showing'` were returned as expected.
- Movie status data remained consistent with the expected result after refactoring.
- No unrelated movie status was changed.

#### Status

`PASS`

---

### TC-DB-003 — Verify duplicated literal has been removed

- **Jira:** KAN-15
- **Type:** Static Analysis / Code Verification
- **Level:** Database
- **Objective:** Verify that the duplicated `now_showing` literal has been replaced by the shared variable.

#### Preconditions
- KAN-15 implementation has been completed.

#### Steps
1. Search the SQL seed script for `'now_showing'`.
2. Verify that `@STATUS_NOW_SHOWING` is defined.
3. Verify that the movie INSERT statements use `@STATUS_NOW_SHOWING`.
4. Run SonarCloud analysis.

#### Expected Result
- `@STATUS_NOW_SHOWING` is defined once.
- The 8 duplicated usages are replaced.
- SonarCloud no longer reports the duplicated literal issue.

#### Actual Result

- `@STATUS_NOW_SHOWING` is defined once in the SQL seed script.
- The duplicated `now_showing` literals in the movie INSERT statements were replaced with `@STATUS_NOW_SHOWING`.
- SonarCloud analysis completed successfully.
- SonarCloud Quality Gate passed with 0 new issues.
- The duplicated literal issue reported by KAN-15 is no longer reported.

#### Status

`PASS`


## Authentication

| ID          | Jira   | Test Case                                 | Type          | Level       | Preconditions     | Input JSON                                                                                                                                                                         | Expected Result                         | Actual Result                    | Status |
| ----------- | ------ | ----------------------------------------- | ------------- | ----------- | ----------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------- | -------------------------------- | ------ |
| TC-AUTH-001 | KAN-27 | Login với thông tin hợp lệ                | Positive      | API         | Tài khoản tồn tại | `{"email":"testauth01@gmail.com","password":"123456"}`                                                                                                                             | Login thành công, session user được tạo | Login thành công                 | PASS   |
| TC-AUTH-002 | KAN-27 | Login với password sai                    | Negative      | API         | Tài khoản tồn tại | `{"email":"testauth01@gmail.com","password":"hhahhaha"}`                                                                                                                           | Login thất bại, không tạo session       | Login bị từ chối                 | PASS   |
| TC-AUTH-003 | KAN-27 | Login với email không tồn tại             | Negative      | API         | -                 | `{"email":"hellllooo01@gmail.com","password":"123456"}`                                                                                                                            | Login thất bại                          | Login bị từ chối                 | PASS   |
| TC-AUTH-004 | KAN-27 | Login thiếu email                         | Validation    | API         | -                 | `{"email":"","password":"123456"}`                                                                                                                                                 | Login bị từ chối                        | Request bị từ chối               | PASS   |
| TC-AUTH-005 | KAN-27 | Login thiếu password                      | Validation    | API         | -                 | `{"email":"testauth01@gmail.com","password":""}`                                                                                                                                   | Login bị từ chối                        | Request bị từ chối               | PASS   |
| TC-AUTH-006 | KAN-27 | Login với email không hợp lệ              | Validation    | API         | -                 | `{"email":"testauth01gmail","password":"123456"}`                                                                                                                                  | Login bị từ chối                        | Request bị từ chối               | PASS   |
| TC-AUTH-007 | KAN-27 | Login bằng tài khoản admin                | Authorization | API         | Admin tồn tại     | `{"email":"admin@example.com","password":"password"}`                                                                                                                              | Login thành công, role = admin          | Login thành công với role admin  | PASS   |
| TC-AUTH-008 | KAN-27 | Logout sau khi login                      | Functional    | Application | User đã login     | `Session: authenticated user`                                                                                                                                                      | Session user bị xóa                     | Được kiểm tra ở application flow | PASS*  |
| TC-AUTH-009 | KAN-27 | Register với thông tin hợp lệ             | Positive      | API         | -                 | `{"first_name":"Test","last_name":"User","email":"postman.test.00001@example.com","phone":"0997654329","birth_date":"2000-01-01","password":"123456","confirm_password":"123456"}` | Tạo tài khoản thành công                | Tài khoản được tạo thành công    | PASS   |
| TC-AUTH-010 | KAN-27 | Register thiếu thông tin bắt buộc         | Validation    | API         | -                 | `{"first_name":"","last_name":"User","email":"postman.test.002@example.com",...}`                                                                                                  | Registration bị từ chối                 | Request bị từ chối               | PASS   |
| TC-AUTH-011 | KAN-27 | Register email không hợp lệ               | Validation    | API         | -                 | `{"first_name":"Test","email":"invalid-email",...}`                                                                                                                                | Registration bị từ chối                 | Request bị từ chối               | PASS   |
| TC-AUTH-012 | KAN-27 | Register password confirmation không khớp | Validation    | API         | -                 | `{"email":"postman.test.003@example.com","password":"123456","confirm_password":"654321",...}`                                                                                     | Registration bị từ chối                 | Request bị từ chối               | PASS   |
| TC-AUTH-013 | KAN-27 | Register password dưới 6 ký tự            | Boundary      | API         | -                 | `{"email":"postman.test.004@example.com","password":"12345","confirm_password":"12345",...}`                                                                                       | Registration bị từ chối                 | Request bị từ chối               | PASS   |
| TC-AUTH-014 | KAN-27 | Register email đã tồn tại                 | Business Rule | API         | Email đã tồn tại  | `{"email":"postman.test.001@example.com","phone":"0987654326",...}`                                                                                                                | Registration bị từ chối                 | Request bị từ chối               | PASS   |
| TC-AUTH-015 | KAN-27 | Register phone đã tồn tại                 | Business Rule | API         | Phone đã tồn tại  | `{"email":"user@test1111.com","phone":"0987654321",...}`                                                                                                                           | Registration bị từ chối                 | Request bị từ chối               | PASS   |
| TC-AUTH-016 | KAN-27 | Password được hash khi register           | Security      | Unit        | -                 | `password="123456"`                                                                                                                                                                | Password được hash trước khi lưu        | Được kiểm tra bằng PHPUnit       | PASS   |
| TC-AUTH-017 | KAN-27 | Password verification khi login           | Security      | Unit        | User tồn tại      | `correct / incorrect password`                                                                                                                                                     | `password_verify()` trả kết quả đúng    | Được kiểm tra bằng PHPUnit       | PASS   |
| TC-AUTH-018 | KAN-27 | Đổi password với password hiện tại sai    | Negative      | Unit        | User đã login     | `wrong current password`                                                                                                                                                           | Không đổi password                      | Được kiểm tra bằng PHPUnit       | PASS   |
| TC-AUTH-019 | KAN-27 | Đổi password với confirmation không khớp  | Validation    | Unit        | User đã login     | `new password ≠ confirmation`                                                                                                                                                      | Không đổi password                      | Được kiểm tra bằng PHPUnit       | PASS   |
| TC-AUTH-020 | KAN-27 | Đổi password thành công                   | Positive      | Unit        | User đã login     | `valid current + new password`                                                                                                                                                     | Password được cập nhật thành công       | Được kiểm tra bằng PHPUnit       | PASS   |

> `*` TC-AUTH-008 được kiểm tra ở application flow vì hệ thống hiện tại không expose endpoint logout riêng qua API.

### Test Execution Summary

| Test Level                    | Test Cases | Passed | Failed | Result   |
| ----------------------------- | ---------: | -----: | -----: | -------- |
| Unit Test                     |   13 tests |     13 |      0 | PASS     |
| API Test — Postman            |         14 |     14 |      0 | PASS     |
| Full PHPUnit Suite            |   74 tests |     74 |      0 | PASS     |
| **Authentication Test Cases** |     **20** | **20** |  **0** | **PASS** |
