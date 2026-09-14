# TEAM BVA – MOVIE TICKET BOOKING
## Working Plan: Test Scope → EP/BVA → Test Case → Unit/Postman → White-box → Coverage → Evidence

> **IMPORTANT**
>
> Đây là tài liệu làm việc cho project **Movie Ticket Booking**.
>
> File Assignment được cung cấp chỉ dùng làm **mẫu phương pháp và cách trình bày bảng**, không phải specification của project và không được copy máy móc các boundary, số lượng test case, biến đầu vào hay kết luận coverage của Assignment sang project.
>
> **Source of truth phải là code, business rule, API hiện tại, unit test hiện tại, Postman collection hiện tại và kết quả chạy thực tế của project.**

---

# 1. Mục tiêu hiện tại của team

Hiện tại **không ưu tiên phát triển thêm feature**.

Team tập trung hoàn thiện phần kiểm thử và tài liệu chứng minh cho các chức năng đã có/đã được merge trong project.

Cần trả lời được một cách có hệ thống:

1. Project đang chọn **chức năng/module nào** để áp dụng BVA?
2. Chức năng đó được kiểm thử theo **Black-box**, **White-box**, hay cả hai?
3. Business rule và validation condition thực tế là gì?
4. Các **equivalence partitions** là gì?
5. `min`, `min+`, `nominal`, `max-`, `max` thực tế là gì?
6. Có giá trị `min-`, `max+` hay không?
7. Test case nào cover partition/boundary nào?
8. Test case đã thiết kế có khớp với PHPUnit không?
9. Postman/Newman có thực sự chạy đúng test case không?
10. White-box logic/decision nào được cover?
11. Coverage được đo bằng công cụ nào và công thức nào?
12. Có evidence đủ để trình bày với giảng viên không?

---

# 2. Assignment được sử dụng như thế nào?

Assignment mẫu cho chúng ta **phương pháp**, không phải dữ liệu của Movie Ticket Booking.

Assignment mẫu minh họa các nội dung:

- Equivalence Partitioning.
- Boundary Value Analysis.
- Test case design.
- Input / Expected Outcome / Tag.
- Unit test dựa trên boundary.
- Kết quả chạy test.

Ví dụ trong Assignment có bảng dạng:

| Conditions | Valid Partitions | Tag | Invalid Partitions | Tag | Valid Boundaries | Tag |
|---|---|---|---|---|---|---|

và bảng test case:

| Test Case | Input | Expected Outcome | New Tags Covered |
|---|---|---|---|

Assignment cũng mô tả Standard BVA bằng:

- `min`
- `min+`
- `nominal`
- `max-`
- `max`

và yêu cầu test case có input, expected result, boundary và tag.

**Đây là format tham khảo.**

Không được suy ra từ Assignment rằng Movie Ticket Booking phải có:

- cùng số biến;
- cùng min/max;
- cùng số test case;
- cùng cách đánh số tag;
- cùng coverage;
- cùng business rule.

---

# 3. Nguyên tắc quan trọng nhất: đọc project trước, thiết kế test sau

Mỗi thành viên khi nhận một module phải bắt đầu bằng:

```text
SOURCE CODE
   ↓
BUSINESS RULE
   ↓
VALIDATION CONDITION
   ↓
TEST SCOPE
   ↓
EP
   ↓
BVA
   ↓
TEST CASE
   ↓
UNIT TEST / POSTMAN
   ↓
WHITE-BOX
   ↓
COVERAGE
   ↓
EVIDENCE
```

**Không được làm ngược:**

```text
Chọn số đẹp
↓
Tạo boundary
↓
Ép code chạy theo test
```

---

# 4. Những gì đã biết về project – chỉ dùng làm điểm bắt đầu

Project hiện có backend PHP và các test/automation liên quan đến BVA.

Trong các phần đã triển khai trước đây, team đã làm việc với các module:

| Module | Chức năng/đối tượng đã xuất hiện trong BVA |
|---|---|
| Seat | `seat_number` validation |
| Room | `total_seats` validation |
| Theatre | `total_screens` validation |
| Review | `rating` validation |
| Auth | password validation |

Project cũng đã có các thành phần từng được sử dụng:

- PHP backend.
- PHPUnit.
- Postman.
- Newman.
- Node.js BVA automation/result logging.
- SonarCloud.
- Git/GitHub PR.

**Nhưng danh sách trên không phải scope cuối cùng.**

Trước khi chốt report, phải checkout code hiện tại và xác nhận:

```powershell
git checkout main
git pull origin main
git status
```

Sau đó kiểm tra source/test/collection thực tế.

---

# 5. Cách chọn chức năng để trình bày

Không cần test toàn bộ project.

Mỗi nhóm cần chọn một tập chức năng **có lý do rõ ràng**.

Ví dụ:

```text
Module: Seat
Function: Validate seat number
Reason:
- Có input số.
- Có validation boundary.
- Có valid/invalid domain.
- Có unit test.
- Có API endpoint.
- Có thể phân tích decision trong source.
```

Sau đó xác định:

```text
Function
Input
Business rule
Valid domain
Invalid domain
Boundary
Expected output
```

## Scope table

| Module | Function | Input | Business Rule | Black-box | White-box | Reason |
|---|---|---|---|---|---|---|
| Seat | ... | ... | ... | Yes | Yes | ... |
| Room | ... | ... | ... | Yes | Yes | ... |
| Theatre | ... | ... | ... | Yes | Yes | ... |

**Chỉ đưa chức năng vào bảng khi đã xác nhận từ code/project.**

---

# 6. Black-box và White-box phải được tách rõ

## 6.1 Black-box

Kiểm tra hệ thống từ bên ngoài.

Quan tâm:

- API endpoint.
- HTTP method.
- Request body.
- Input.
- HTTP status.
- Response.
- Expected result.

Không cần biết code bên trong.

Ví dụ:

```text
POST /theatres/validate

Input:
total_screens = 1

Expected:
status = success
```

Nguồn evidence:

- Postman.
- Newman.
- API automation.
- Response log.

---

## 6.2 White-box

Kiểm tra dựa trên logic bên trong source code.

Quan tâm:

- `if`.
- `else`.
- `&&`.
- `||`.
- comparison.
- validation condition.
- return path.
- exception/error path.
- decision outcome.

Ví dụ source:

```php
if ($totalScreens < 1) {
    return error;
}

return success;
```

Decision:

```text
D1: totalScreens < 1
```

Outcomes:

```text
TRUE  → error
FALSE → success
```

Test mapping:

| TC | Input | D1 | Outcome |
|---|---:|---|---|
| TC-01 | 0 | TRUE | error |
| TC-02 | 1 | FALSE | success |

Như vậy cả hai decision outcomes đã được exercise.

---

# 7. Equivalence Partitioning – cách làm đúng cho project

Với mỗi input:

### Bước 1
Tìm điều kiện validation thật trong code.

Ví dụ:

```text
x >= 1
```

### Bước 2
Tạo partition:

```text
Valid:
x >= 1

Invalid:
x < 1
```

Không tự tạo upper-bound nếu source không có upper-bound.

---

## Nếu có range `[min, max]`

Ví dụ source thật sự là:

```text
1 <= x <= 12
```

thì có thể có:

```text
V1 = 1..12
X1 = x < 1
X2 = x > 12
```

## EP table

| Module | Condition | Partition | Range | Tag | Expected |
|---|---|---|---|---|---|
| ... | ... | Valid | ... | V1 | success |
| ... | ... | Invalid below | ... | X1 | error |
| ... | ... | Invalid above | ... | X2 | error |

**Tag phải dùng thống nhất toàn project.**

---

# 8. Boundary Value Analysis

Nếu xác định được range `[min,max]`, lập:

| Boundary | Ý nghĩa |
|---|---|
| `min-` | Ngay dưới min |
| `min` | Min hợp lệ |
| `min+` | Ngay trên min |
| `nominal` | Giá trị đại diện trong miền |
| `max-` | Ngay dưới max |
| `max` | Max hợp lệ |
| `max+` | Ngay trên max |

Assignment mẫu đặc biệt yêu cầu Standard BVA:

```text
min
min+
nominal
max-
max
```

và phần ngoài biên có thể trình bày riêng:

```text
min-
max+
```

---

# 9. Hai phần BVA phải phân biệt

## A. Standard BVA

Tập trung boundary thuộc miền hợp lệ:

```text
min
min+
nominal
max-
max
```

Ví dụ:

```text
MIN = 1
MAX = 12
```

có thể test:

```text
1
2
nominal
11
12
```

## B. Robustness / Out-of-bound

Kiểm tra bên ngoài boundary:

```text
0
13
```

Mục tiêu:

- Chứng minh hệ thống reject giá trị ngoài miền.
- Phân biệt rõ với Standard BVA.

**Không được gọi tất cả 7 giá trị trên là Standard BVA.**

---

# 10. Nominal value

Nominal phải là giá trị hợp lệ đại diện cho miền.

Không nhất thiết phải là trung bình toán học.

Ví dụ:

```text
1..12
```

có thể chọn một giá trị ở giữa như:

```text
6
```

hoặc một giá trị representative hợp lý.

Nominal phải:

- nằm trong valid domain;
- không phải boundary;
- không gây thêm precondition bất thường.

---

# 11. Với biến không có max

Nếu source chỉ quy định:

```text
x >= 1
```

thì:

```text
min = 1
min+ = 2
min- = 0
```

nhưng **không được tự đặt max**.

Khi đó bảng phải ghi:

```text
MAX = N/A
MAX- = N/A
MAX+ = N/A
```

và giải thích:

> Source/business rule không quy định upper bound.

Đây là điểm rất quan trọng để tránh bịa boundary cho project.

---

# 12. Test case design

Mỗi test case cần đủ thông tin để một người khác có thể chạy lại.

## Test case template

| Field | Nội dung |
|---|---|
| TC ID | TC-XXX-BVA-01 |
| Module | ... |
| Function | ... |
| Test Type | Black-box / White-box |
| Input | ... |
| Precondition | ... |
| Boundary | min/min+/nominal/max-/max/min-/max+ |
| Partition | V1/X1/X2 |
| Expected Output | ... |
| Expected Reason | ... |
| Tags Covered | ... |
| Actual Output | ... |
| Status | PASS/FAIL |
| Evidence | ... |

---

# 13. Các test case phải bao phủ nhiều loại

Không chỉ có BVA.

Mỗi scope nên xem xét:

### 1. Standard BVA

```text
min
min+
nominal
max-
max
```

### 2. Robustness

```text
min-
max+
```

nếu có boundary tương ứng.

### 3. Equivalence Partition

```text
valid
invalid below
invalid above
```

nếu business rule có đủ các lớp.

### 4. Full functional / happy path

Một test nghiệp vụ hợp lệ bình thường.

Ví dụ:

```text
Tạo room hợp lệ hoàn chỉnh.
```

Test này không nhất thiết là BVA.

Tag ví dụ:

```text
FUNC-HAPPY
```

---

# 14. Nguyên tắc thay đổi một biến

Nếu function có nhiều input:

```text
A
B
C
D
```

khi test BVA của `A`:

```text
A = boundary value
B = nominal
C = nominal
D = nominal
```

Đây là cách thể hiện rõ tác động của boundary đang được test.

Tuy nhiên với API có precondition/database dependency, phải chọn nominal values **thực sự hợp lệ trong database**, không chỉ nominal về mặt số học.

---

# 15. Precondition phải được ghi rõ

Đặc biệt với API.

Ví dụ Review:

```text
rating = 1
```

nhưng API còn yêu cầu:

```text
movie_id tồn tại
user_id tồn tại
```

Nếu movie không tồn tại, response:

```text
Phim không hợp lệ!
```

thì không thể kết luận:

```text
rating=1 → invalid
```

Phải phân biệt:

```text
BVA input failure
```

với:

```text
precondition/data failure
```

## Test case phải có

```text
Precondition:
movie_id = valid existing movie
user_id = valid existing user
```

---

# 16. Mapping Excel ↔ PHPUnit

Sau khi thiết kế test case, kiểm tra unit test hiện tại.

Ví dụ:

```text
TC-REVIEW-BVA-02
```

phải tìm được test method tương ứng.

## Mapping table

| TC ID | PHPUnit File | Test Method | Input | Expected | Tag | Match |
|---|---|---|---|---|---|---|
| TC-... | ...Test.php | ...() | ... | ... | ... | YES/NO |

Không được có tình trạng:

```text
Excel nói test rating=1 success
```

nhưng:

```text
PHPUnit không test rating=1
```

---

# 17. PHPUnit – chạy test

Backend hiện dùng PHPUnit.

Từ root:

```powershell
cd backend
php vendor/bin/phpunit
```

Chạy riêng module:

```powershell
php vendor/bin/phpunit tests/Services/<Module>ServiceTest.php
```

Evidence cần giữ:

```text
PHPUnit version
Tests
Assertions
PASS/FAIL
```

Ví dụ:

```text
OK (8 tests, 12 assertions)
```

Nếu xuất hiện:

```text
Warning: No code coverage driver available
```

thì đây là vấn đề **coverage instrumentation**, không đồng nghĩa PHPUnit test thất bại.

---

# 18. Coverage của PHPUnit

Nếu muốn đo coverage bằng PHPUnit, môi trường PHP phải có coverage driver như:

- Xdebug.
- PCOV.

Khi driver đã hoạt động:

```powershell
php vendor/bin/phpunit --coverage-text
```

hoặc:

```powershell
php vendor/bin/phpunit --coverage-html coverage
```

Nếu môi trường không có driver, không được tự ghi một con số coverage.

---

# 19. Postman – kiểm tra Black-box

Postman phải mapping được với test case.

## Postman mapping

| TC ID | Collection | Folder | Request | Endpoint | Method | Expected | Actual | Status |
|---|---|---|---|---|---|---|---|---|
| TC-... | BVA | Seat | ... | ... | POST | ... | ... | PASS |

Test script nên có TC ID.

Ví dụ:

```javascript
const tcId = "TC-ROOM-BVA-03";
const expected = "success";

const json = pm.response.json();

pm.test(`${tcId} expected status`, () => {
    pm.expect(json.status).to.eql(expected);
});
```

---

# 20. Postman có thể hỗ trợ White-box như thế nào?

Postman **không tự nhìn vào PHP source để tính white-box coverage**.

Postman thực hiện API request.

White-box được chứng minh bằng:

```text
Source code
+
Decision/condition mapping
+
Test case
+
API result
```

Ví dụ:

```text
Source:
if ($x < 1)

TC01:
x = 0
→ TRUE branch

TC02:
x = 1
→ FALSE branch
```

Postman response cung cấp evidence rằng request đã thực sự chạy.

**White-box reasoning vẫn phải do team lập matrix.**

---

# 21. Newman

Nếu project có collection và Newman:

```powershell
npx newman run tests/postman/<collection>.json
```

Nếu project đã có npm script:

```powershell
npm run <script>
```

phải kiểm tra `package.json` trước khi dùng.

Không tự ghi command nếu script không tồn tại.

Evidence:

```text
iterations
requests
tests
assertions
failures
```

---

# 22. BVA automation/result logging

Project đã từng có script:

```text
tests/automation/run-bva-and-log.js
```

và command:

```powershell
node tests\automation\run-bva-and-log.js
```

Nếu `package.json` có script tương ứng, có thể chạy:

```powershell
npm run bva:log
```

**Luôn kiểm tra `package.json` trước.**

Automation output có thể gồm:

```text
Total
Passed
Failed
Pass Rate
JSON report
CSV report
```

Pass rate chỉ là:

```text
Passed / Total × 100%
```

---

# 23. Không được nhầm các loại coverage

Có ít nhất 4 số liệu khác nhau.

## 23.1 Test Pass Rate

```text
Passed / Total Test Cases × 100%
```

Ví dụ:

```text
19 / 23 = 82.61%
```

Đây chỉ là tỷ lệ test PASS.

---

## 23.2 BVA Tag Coverage

```text
Covered BVA Tags
/
Total BVA Tags
× 100%
```

Ví dụ:

```text
B1..B10
```

nếu cover 10/10:

```text
100%
```

---

## 23.3 White-box Decision Coverage

Nếu team chọn manual decision coverage:

```text
Covered Decision Outcomes
/
Total Decision Outcomes
× 100%
```

Ví dụ một decision:

```text
TRUE
FALSE
```

và cả hai được test:

```text
2 / 2 = 100%
```

---

## 23.4 Tool-based Code Coverage

Có thể lấy từ:

- PHPUnit + Xdebug/PCOV.
- SonarCloud nếu coverage report được upload.
- CI coverage tool.

Metric này phải ghi đúng tên metric của tool.

---

# 24. White-box – cách làm chính thức cho project

Mỗi module phải có **Decision Matrix**.

## Template

| Module | Function | Decision ID | Source Condition | TRUE Outcome | FALSE Outcome | TC cover TRUE | TC cover FALSE |
|---|---|---|---|---|---|---|---|
| Seat | ... | D1 | ... | ... | ... | ... | ... |
| Room | ... | D1 | ... | ... | ... | ... | ... |
| Theatre | ... | D1 | ... | ... | ... | ... | ... |

Nếu có nhiều điều kiện:

```php
if ($a >= 1 && $b <= 10)
```

không được coi toàn bộ expression là một con số đơn giản mà không phân tích logic phù hợp.

Cần ghi:

```text
D1: $a >= 1
D2: $b <= 10
```

nếu mục tiêu là condition/decision analysis.

---

# 25. White-box tools nên dùng

## Level 1 – Source inspection

- VS Code.
- Vibe Coding/Antigravity.
- Git.
- Search code.

Mục tiêu:

```text
if/else
condition
return
exception
```

---

## Level 2 – PHPUnit coverage

- PHPUnit.
- Xdebug.
- PCOV.

Mục tiêu:

```text
line coverage
branch/condition coverage nếu tool hỗ trợ
```

---

## Level 3 – SonarCloud

Mục tiêu:

- code coverage nếu có report;
- bugs;
- vulnerabilities;
- code smells;
- quality gate;
- branch/condition metrics nếu được cấu hình và hiển thị.

**SonarCloud là tool measurement/evidence, không phải nơi định nghĩa BVA tags.**

---

# 26. Cách dùng SonarCloud đúng

Khi mở SonarCloud:

1. Xác định project.
2. Kiểm tra branch đang xem.
3. Kiểm tra thời điểm analysis.
4. Kiểm tra coverage data.
5. Kiểm tra branch/condition data nếu có.
6. Kiểm tra Quality Gate.
7. Chụp evidence.

Nếu Sonar không có coverage data:

```text
Coverage = unavailable
```

Không suy đoán.

---

# 27. Mục tiêu câu hỏi “Seat + Room + Theatre phủ bao nhiêu %?”

Đây là phần cần chuẩn bị kỹ nhất.

Team phải quyết định trước **đang trả lời coverage nào**.

Không trả lời đơn giản:

```text
BVA = 100%
```

mà phải nói:

```text
BVA Tag Coverage = ...
Manual White-box Decision Coverage = ...
Automated Test Pass Rate = ...
Tool-based Code Coverage = ...
```

nếu các metric đó thực sự có dữ liệu.

---

# 28. Bảng tổng hợp White-box Seat + Room + Theatre

## Template

| Module | Function | Decisions | Outcomes | Covered Outcomes | Manual Decision Coverage |
|---|---|---:|---:|---:|---:|
| Seat | ... | ... | ... | ... | ... |
| Room | ... | ... | ... | ... | ... |
| Theatre | ... | ... | ... | ... | ... |
| **TOTAL** | | **...** | **...** | **...** | **...%** |

Công thức:

```text
Coverage =
Covered Outcomes / Total Outcomes × 100%
```

**Chỉ điền số sau khi đọc source code thật.**

---

# 29. Excel cuối cùng

File tổng hợp nên có các sheet:

## 01 – Scope

- Module.
- Function.
- Source.
- API.
- Black-box.
- White-box.
- Reason for selection.

## 02 – Business Rules

- Function.
- Input.
- Type.
- Validation rule.
- Preconditions.
- Expected behavior.

## 03 – Equivalence Partition

- Condition.
- Valid partition.
- Invalid partition below.
- Invalid partition above.
- Tags.

## 04 – BVA

- Input.
- Min-.
- Min.
- Min+.
- Nominal.
- Max-.
- Max.
- Max+.
- Tags.

## 05 – Test Cases

- TC ID.
- Function.
- Type.
- Input.
- Preconditions.
- Expected.
- Tag.
- Actual.
- Status.

## 06 – PHPUnit Mapping

- TC ID.
- Test file.
- Test method.
- Input.
- Expected.
- Result.

## 07 – Postman/Newman

- TC ID.
- Request.
- Endpoint.
- Method.
- Body.
- Expected.
- Actual.
- Assertion.
- Result.

## 08 – White-box

- Decision ID.
- Source condition.
- TRUE.
- FALSE.
- Covering TC.
- Covered/Not covered.

## 09 – Coverage

- Pass rate.
- BVA tag coverage.
- Manual decision coverage.
- PHPUnit coverage.
- Sonar coverage.
- Source/evidence.

## 10 – Evidence

- Screenshot.
- Command.
- Output.
- Report file.
- Commit/PR.

---

# 30. Cross-check quan trọng

Trước khi kết luận một test case DONE:

```text
Excel
  ↕
PHPUnit
  ↕
Postman
  ↕
Automation
  ↕
Source code
```

Ví dụ:

```text
TC-SEAT-BVA-01
```

phải biết:

- input là gì;
- boundary gì;
- tag gì;
- Excel ghi expected gì;
- PHPUnit có test không;
- Postman có test không;
- automation có test không;
- source decision nào được cover;
- evidence ở đâu.

---

# 31. Quy trình review chéo

Sau khi member hoàn thành module:

### Member thực hiện

```text
Code inspection
→ EP
→ BVA
→ Test Case
→ Unit
→ Postman
→ White-box
→ Evidence
```

### Member khác review

Kiểm tra:

```text
1. Boundary có thật không?
2. Partition có đúng không?
3. Expected có đúng business rule không?
4. Precondition có hợp lệ không?
5. PHPUnit có khớp Excel không?
6. Postman có khớp Excel không?
7. White-box có khớp source không?
8. Coverage formula có đúng không?
9. Evidence có chạy thật không?
```

---

# 32. Definition of Done

Một module chỉ được đánh dấu DONE khi:

- [ ] Đã xác định function cụ thể.
- [ ] Đã xác định module.
- [ ] Đã xác định lý do chọn function.
- [ ] Đã xác định Black-box/White-box.
- [ ] Đã đọc source code.
- [ ] Đã xác định business rule.
- [ ] Đã xác định precondition.
- [ ] Đã lập Equivalence Partition.
- [ ] Đã xác định min/max thật.
- [ ] Đã lập Standard BVA.
- [ ] Đã xem xét min-/max+.
- [ ] Đã thiết kế test case.
- [ ] Có happy-path/full functional test nếu cần.
- [ ] Test case có tag.
- [ ] Excel ↔ PHPUnit mapping đúng.
- [ ] PHPUnit chạy được.
- [ ] Excel ↔ Postman mapping đúng.
- [ ] Newman/Postman chạy được.
- [ ] White-box decision matrix hoàn chỉnh.
- [ ] Coverage được tính đúng loại.
- [ ] Sonar được đối chiếu nếu có dữ liệu.
- [ ] Có command làm evidence.
- [ ] Có output thực tế.
- [ ] Không có kết luận dựa trên dữ liệu giả định.
- [ ] Sẵn sàng review/PR.

---

# 33. Git workflow cho task hiện tại

Mỗi member tạo branch từ `main`.

```powershell
git checkout main
git pull origin main
git checkout -b <jira-key>-<short-description>
```

Ví dụ:

```text
KAN-XX-bva-scope-analysis
KAN-XX-bva-test-design
KAN-XX-whitebox-coverage
KAN-XX-postman-review
KAN-XX-test-evidence
```

Trước khi commit:

```powershell
git status
```

Sau khi test:

```powershell
git add .
git commit -m "test: refine BVA test design and coverage"
git push -u origin <branch-name>
```

---

# 34. Prompt chuẩn cho Antigravity / Vibe Coding

Copy prompt sau và thay `<MODULE>`:

```text
Bạn đang làm phần testing analysis cho module <MODULE> của project Movie Ticket Booking.

QUAN TRỌNG:
Assignment mẫu chỉ được dùng để tham khảo phương pháp trình bày EP/BVA/test case.
Không được copy boundary hoặc business rule của Assignment sang project.

SOURCE OF TRUTH:
- Source code hiện tại.
- Business logic hiện tại.
- API hiện tại.
- PHPUnit hiện tại.
- Postman collection hiện tại.
- Database/precondition thực tế nếu test API cần.
- Kết quả chạy thực tế.

KHÔNG ĐƯỢC:
- tự bịa min/max;
- tự bịa business rule;
- đổi expected chỉ để test PASS;
- sửa production code nếu task không yêu cầu;
- gọi pass rate là coverage;
- gọi manual decision coverage là Sonar branch coverage;
- kết luận BVA sai khi API fail vì precondition/database.

NHIỆM VỤ:

1. Inspect toàn bộ source liên quan đến <MODULE>.
2. Xác định function cụ thể cần test.
3. Chỉ ra:
   - module
   - function
   - input
   - type
   - business rule
   - precondition
   - API endpoint nếu có
4. Phân loại:
   - Black-box
   - White-box
5. Tìm validation conditions trong source.
6. Xác định Equivalence Partition:
   - valid
   - invalid below
   - invalid above nếu thực sự có upper bound
7. Xác định BVA:
   - min
   - min+
   - nominal
   - max-
   - max
   - min- nếu có
   - max+ nếu có
8. Tạo test case có:
   - TC ID
   - input
   - precondition
   - expected output
   - partition
   - boundary
   - tag
9. Tách rõ:
   - Standard BVA
   - Robustness/out-of-bound
   - Full functional/happy path
10. Kiểm tra PHPUnit hiện tại.
11. Mapping từng TC với test method.
12. Chạy PHPUnit và ghi output thực tế.
13. Kiểm tra Postman collection.
14. Mapping từng TC với Postman request.
15. Chạy Newman/Postman nếu có thể.
16. Đọc source và lập White-box Decision Matrix.
17. Với mỗi decision:
   - condition
   - TRUE
   - FALSE
   - TC cover TRUE
   - TC cover FALSE
18. Tính manual decision coverage:
   Covered Outcomes / Total Outcomes × 100%.
19. Nếu có PHPUnit/Xdebug/PCOV hoặc Sonar coverage thì ghi riêng tool-based coverage.
20. Cuối cùng report:
   - Scope
   - Business rule
   - EP
   - BVA
   - Test cases
   - PHPUnit mapping/result
   - Postman mapping/result
   - White-box matrix
   - Coverage
   - Files changed
   - Commands executed
   - Evidence cần lưu

Nếu phát hiện mismatch giữa Excel/test design/code:
KHÔNG tự sửa để ép PASS.
Hãy báo:
- mismatch;
- expected;
- actual;
- source rule;
- nguyên nhân;
- đề xuất xử lý.
```

---

# 35. Prompt riêng cho White-box

```text
Phân tích white-box cho <MODULE> trong project Movie Ticket Booking.

Không sử dụng boundary từ Assignment nếu source code project không có rule đó.

1. Đọc source code thật.
2. Xác định function validation.
3. Liệt kê các decision/condition:
   - if
   - else
   - && 
   - ||
   - ternary nếu ảnh hưởng logic
   - exception/error path
   - return path
4. Đánh ID D1, D2...
5. Xác định TRUE/FALSE outcome.
6. Map từng test case hiện có vào outcome.
7. Xác định outcome chưa được cover.
8. Tạo bảng:

Module | Function | Decision | Condition | TRUE | FALSE | TC TRUE | TC FALSE | Covered

9. Tính manual decision coverage.
10. Nếu có PHPUnit + Xdebug/PCOV hoặc Sonar data, đối chiếu riêng.
11. Không gọi manual coverage là Sonar coverage.
12. Không tạo số coverage nếu chưa có evidence.
```

---

# 36. Evidence package cuối cùng

Mỗi module nên bàn giao:

```text
/module-name/
├── scope.md
├── equivalence-partition.md
├── bva-test-cases.md
├── whitebox.md
├── unit-test-result.txt
├── postman-result.txt
└── screenshots/
```

Nếu team chỉ lưu chung trong Excel thì Excel phải có cột:

```text
Evidence ID
Evidence Type
Command
Result
Source
```

---

# 37. Những câu hỏi giảng viên có thể hỏi

Team phải chuẩn bị trả lời:

### “Tại sao chọn chức năng này?”

→ Vì function có validation/business rule rõ và phù hợp với EP/BVA/white-box.

### “Min/max lấy ở đâu?”

→ Chỉ ra source code/business rule.

### “Tại sao chọn nominal?”

→ Là giá trị hợp lệ đại diện trong miền, không phải boundary.

### “Tại sao test min-?”

→ Kiểm tra giá trị ngay ngoài lower boundary.

### “Test này là black-box hay white-box?”

→ Nếu chỉ nhìn request/response: Black-box.
→ Nếu mapping vào source condition/decision: White-box.

### “Pass rate bao nhiêu?”

→ `Passed / Total`.

### “Coverage bao nhiêu?”

→ Phải nói rõ:
- BVA tag coverage;
- manual decision coverage;
- tool-based code coverage.

### “Sonar báo bao nhiêu?”

→ Chỉ dùng đúng metric Sonar đang hiển thị.

---

# 38. Nguyên tắc cuối cùng

Không lấy Assignment làm khuôn rập.

Hãy lấy:

```text
ASSIGNMENT
   ↓
Phương pháp
   ↓
Project source code
   ↓
Business rule thật
   ↓
EP/BVA phù hợp
   ↓
Test case
   ↓
Automation
   ↓
White-box
   ↓
Coverage
   ↓
Evidence
```

**Assignment trả lời “làm phương pháp như thế nào”.**

**Project trả lời “test cái gì và boundary nào”.**

Đây là nguyên tắc bắt buộc cho toàn bộ team.
