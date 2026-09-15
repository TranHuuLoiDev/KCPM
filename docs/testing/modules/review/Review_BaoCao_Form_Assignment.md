# BÁO CÁO KIỂM THỬ MODULE REVIEW

**Project:** Movie Ticket Booking  
**Module:** Review  
**Service:** `App\Services\ReviewService`  
**Ngày rà soát:** 2026-09-15  
**Mẫu:** [Báo cáo Seat](../seat/083205006374_LuongQuocAn_BaoCao_Seat_Form_Assignment_Chot.md)  
**Trạng thái:** Thiết kế theo source và đối chiếu automation; chưa gán PASS cho các case mới. Chưa có thông tin người thực hiện/MSSV riêng của module.

---

# 1. XÁC ĐỊNH MODULE VÀ FILE LIÊN QUAN

Review tiếp nhận đánh giá phim, lưu số sao/nội dung, đọc danh sách và thống kê điểm trung bình.

| File | Nội dung đối chiếu |
|---|---|
| [ReviewService.php](../../../../backend/app/Services/ReviewService.php) | Guard user/phim, rating 1..5, trim comment, gọi model |
| [ReviewModel.php](../../../../backend/app/Models/ReviewModel.php) | INSERT review; danh sách join users; `ROUND(AVG(rating),1)` và COUNT |
| [MovieModel.php](../../../../backend/app/Models/MovieModel.php) | `getMovieByIdWithGenres` xác định phim tồn tại |
| [ReviewController.php](../../../../backend/app/Controllers/ReviewController.php) | Action `add_review`; user lấy từ session; chuyển dữ liệu sang service |
| [api.php](../../../../backend/api.php) | `POST /reviews/validate` chỉ gọi validateReviewInput |
| [movie_details.php](../../../../frontend/movie_details.php) | Trang chi tiết phim và thao tác đánh giá |
| [BookingTicketDatabase.sql](../../../../backend/Database/BookingTicketDatabase.sql) | Rating TINYINT CHECK 1..5; comment TEXT; FK user/movie |
| [ReviewServiceTest.php](../../../../backend/tests/Services/ReviewServiceTest.php) | 8 test; setUp kết nối DB và lấy phim đầu tiên |
| [bva-cases.js](../../../../tests/bva/bva-cases.js) | 6 case rating: 0,1,2,4,5,6 |
| [generate-postman.js](../../../../tests/bva/generate-postman.js), [collection chính](../../../../tests/postman/BVA_MovieBooking.postman_collection.json) | Folder `BVA - Review`, request validation |
| [Runner Newman](../../../../tests/automation/run-bva-and-log.js) | Kết quả HTTP; không chứng minh insert hay AVG/COUNT |

Không thấy file `ReviewModelTest.php` hoặc `ReviewControllerTest.php` trong cây test hiện tại. Việc có tên Review trong collection tổng hợp không chứng minh đủ những method còn lại.

# 2. XÁC ĐỊNH METHOD TRONG SERVICE

| STT | Method | Visibility | Chức năng |
|---:|---|---|---|
| 1 | `__construct()` | public | Tạo ReviewModel, MovieModel |
| 2 | `addReview($userId,$movieId,$rating,$comment)` | public | Validate đầy đủ, lưu đánh giá |
| 3 | `validateReviewInput($data)` | public | Chỉ kiểm tra rating |
| 4 | `validateRating($rating)` | private | Rule 1..5 |
| 5 | `getReviewsByMovieId($movieId)` | public | ID<=0 trả []; còn lại gọi model |
| 6 | `getRatingSummary($movieId)` | public | ID<=0 trả zero summary; còn lại gọi model |

# 3. CHỌN METHOD, INPUT VÀ QUY ƯỚC

Chọn `validateReviewInput`, `addReview`, `getRatingSummary`: lần lượt kiểm tra lõi rating, ghi đánh giá và tổng hợp kết quả. Method đọc danh sách được rà soát thêm ở mục 8.

Tag đánh lại trong mỗi method; ID đầy đủ `Review/method/BVA-xx` hoặc `Review/method/EP-xx`. Bảng input giả định giá trị rating nguyên sau ép kiểu. `rating` có Standard BVA 1,2,3,4,5; ID chỉ EP; comment chỉ có điều kiện không rỗng sau trim, không tự đặt độ dài max ở service.

Fixture F: user 1 có thật khi integration; movie 1 tồn tại; movie 999999 không tồn tại; comment nominal `Phim hay`; mỗi case có dữ liệu riêng, model create thành công trừ khi ghi rõ. Không coi seed đang có là fixture ổn định.

---

# 4. METHOD 1 — `validateReviewInput($data)`

## 4.1. Chức năng và điều kiện

Method chỉ lấy `(int)($data['rating'] ?? 0)`. Các trường user_id, movie_id, comment được API truyền vào nhưng **không được method kiểm tra**. Input rating hợp lệ không đồng nghĩa đánh giá đã được lưu hoặc người dùng đã đăng nhập.

## 4.2. Bước 1 — EP theo form Assignment

| Conditions | Valid Partitions | Tag | Invalid Partitions | Tag | Valid Boundaries | Tag |
|---|---|---|---|---|---|---|
| rating sau ép int | 1..5 | V1 | <1 | X1 | 1,5 | B1,B5 |
| rating sau ép int | 1..5 | V1 | >5 | X2 | 1,5 | B1,B5 |

## 4.3. Bước 2 — Biên

| Vị trí | Giá trị | Tag |
|---|---:|---|
| min | 1 | B1 |
| min+ | 2 | B2 |
| nominal | 3 | B3 |
| max- | 4 | B4 |
| max | 5 | B5 |

0 và 6 thuộc EP ngoài miền, không đưa vào Standard BVA hợp lệ.

## 4.4. Bước 3 — Thiết kế Test Case

### Phần A — Test case cho Standard BVA

Nguyên tắc: mỗi lần chỉ thay đổi **một biến đang kiểm thử**, các input còn lại giữ ở giá trị hợp lệ.

Giá trị nominal / hợp lệ dùng để giữ cố định:

```java
rating nominal = 3
Method chỉ kiểm tra rating; không có input khác cần giữ cố định.
```

| STT    | Test case          | rating | Kết quả mong đợi | Tag |
| ------ | ------------------ | ------ | ---------------- | --- |
| BVA-01 | `rating` – min     | 1      | **Hợp lệ**       | B1  |
| BVA-02 | `rating` – min+    | 2      | **Hợp lệ**       | B2  |
| BVA-03 | `rating` – nominal | 3      | **Hợp lệ**       | B3  |
| BVA-04 | `rating` – max-    | 4      | **Hợp lệ**       | B4  |
| BVA-05 | `rating` – max     | 5      | **Hợp lệ**       | B5  |

---

### Phần B — Test case phủ Equivalence Partitioning V/X

Ở mỗi test, chỉ cố tình làm sai một lớp không hợp lệ; các input khác giữ hợp lệ.

| STT   | Test case             | rating | Kết quả mong đợi                                    | Tag |
| ----- | --------------------- | ------ | --------------------------------------------------- | --- |
| EP-01 | Tất cả giá trị hợp lệ | 3      | **Hợp lệ**                                          | V1  |
| EP-02 | `rating` dưới miền    | 0      | **Không hợp lệ** – Vui lòng chọn số sao từ 1 đến 5! | X1  |
| EP-03 | `rating` trên miền    | 6      | **Không hợp lệ** – Vui lòng chọn số sao từ 1 đến 5! | X2  |

#### Ghi chú kết quả mong đợi

- **BVA-01, BVA-02, BVA-03, BVA-04, BVA-05, EP-01**: Dữ liệu đánh giá hợp lệ!.

### Tổng hợp method `validateReviewInput`

| Nhóm                         | Số TC    | Phạm vi phủ                              |
| ---------------------------- | -------- | ---------------------------------------- |
| **Standard BVA**             | 5        | **B1–B5**                                |
| **Equivalence Partitioning** | 3        | **V1, X1–X2**                            |
| **Tổng theo hai bảng**       | **8 TC** | **Phủ toàn bộ tag thiết kế trong scope** |

Tổng **5 BVA + 3 EP = 8 case**. Nominal xuất hiện hai lần để thể hiện hai mục đích thiết kế, không phải hai input khác nhau.

## 4.5. Mapping automation

| Case | Test PHPUnit hiện có | Postman hiện có |
|---|---|---|
| BVA-01 | `testRatingAtMinimum` | TC-REVIEW-BVA-02 |
| BVA-02 | `testRatingMinPlusOne` | TC-REVIEW-BVA-03 |
| BVA-03, EP-01 | Chưa có riêng tại validateReviewInput | Chưa có rating=3 |
| BVA-04 | `testRatingMaxMinusOne` | TC-REVIEW-BVA-04 |
| BVA-05 | `testRatingAtMaximum` | TC-REVIEW-BVA-05 |
| EP-02 | `testRatingBelowMinimum` | TC-REVIEW-BVA-01 |
| EP-03 | `testRatingAboveMaximum` | TC-REVIEW-BVA-06 |

`testValidRatingContinuesToCommentValidation` dùng rating=3 nhưng gọi addReview và nhận lỗi comment. Không thay cho test success nominal của validateReviewInput.

# 5. METHOD 2 — `addReview($userId,$movieId,$rating,$comment)`

## 5.1. Input, thứ tự và kết quả

| Input | Điều kiện thực tế |
|---|---|
| userId | Ép int, phải >0; service không tra cứu user tồn tại |
| movieId | Ép int, >0 và tra cứu được phim |
| rating | Ép int, 1..5 |
| comment | Trim, khác chuỗi rỗng |

Thứ tự trả lỗi: user → movie → rating → comment → create. Với integration, FK users yêu cầu user tồn tại; đây không phải một nhánh validation user riêng trong service. Method không kiểm tra người dùng đã xem phim hay đã đánh giá phim trước đó.

## 5.2. Bước 1 — EP

| Conditions | Valid Partitions | Tag | Invalid Partitions | Tag | Valid Boundaries | Tag |
|---|---|---|---|---|---|---|
| userId | >0 | V1 | <=0 | X1 | N/A | N/A |
| movieId | >0, tồn tại | V2 | <=0; >0 không tồn tại | X2,X3 | N/A | N/A |
| rating | 1..5 | V3 | <1; >5 | X4,X5 | 1,5 | B1,B5 |
| comment | Khác rỗng sau trim | V4 | Rỗng sau trim | X6 | N/A | N/A |

## 5.3. Bước 2 — Biên

Chỉ rating có miền đóng: 1,2,3,4,5 tương ứng B1..B5. User/movie và độ dài comment không có upper bound nghiệp vụ tại service. Giữ user=1, movie=1, comment=`Phim hay` và model create thành công khi xét BVA.

## 5.4. Bước 3 — Thiết kế Test Case

### Phần A — Test case cho Standard BVA

Nguyên tắc: mỗi lần chỉ thay đổi **một biến đang kiểm thử**, các input còn lại giữ ở giá trị hợp lệ.

Giá trị nominal / hợp lệ dùng để giữ cố định:

```java
userId       = 1 (user tồn tại khi integration)
movieId      = 1 (phim tồn tại)
comment      = Phim hay
rating nominal = 3
Model create thành công; fixture mới cho mỗi test.
```

| STT    | Test case          | userId | movieId | rating | comment  | Kết quả mong đợi | Tag |
| ------ | ------------------ | ------ | ------- | ------ | -------- | ---------------- | --- |
| BVA-01 | `rating` – min     | 1      | 1       | 1      | Phim hay | **Hợp lệ**       | B1  |
| BVA-02 | `rating` – min+    | 1      | 1       | 2      | Phim hay | **Hợp lệ**       | B2  |
| BVA-03 | `rating` – nominal | 1      | 1       | 3      | Phim hay | **Hợp lệ**       | B3  |
| BVA-04 | `rating` – max-    | 1      | 1       | 4      | Phim hay | **Hợp lệ**       | B4  |
| BVA-05 | `rating` – max     | 1      | 1       | 5      | Phim hay | **Hợp lệ**       | B5  |

---

### Phần B — Test case phủ Equivalence Partitioning V/X

Ở mỗi test, chỉ cố tình làm sai một lớp không hợp lệ; các input khác giữ hợp lệ.

| STT   | Test case               | userId | movieId | rating | comment  | Kết quả mong đợi                                        | Tag   |
| ----- | ----------------------- | ------ | ------- | ------ | -------- | ------------------------------------------------------- | ----- |
| EP-01 | Tất cả giá trị hợp lệ   | 1      | 1       | 3      | Phim hay | **Hợp lệ**                                              | V1–V4 |
| EP-02 | `userId <= 0`           | 0      | 1       | 3      | Phim hay | **Không hợp lệ** – Vui lòng đăng nhập để đánh giá phim! | X1    |
| EP-03 | `movieId <= 0`          | 1      | 0       | 3      | Phim hay | **Không hợp lệ** – Phim không hợp lệ!                   | X2    |
| EP-04 | `movieId` không tồn tại | 1      | 999999  | 3      | Phim hay | **Không hợp lệ** – Phim không hợp lệ!                   | X3    |
| EP-05 | `rating` dưới miền      | 1      | 1       | 0      | Phim hay | **Không hợp lệ** – Vui lòng chọn số sao từ 1 đến 5!     | X4    |
| EP-06 | `rating` trên miền      | 1      | 1       | 6      | Phim hay | **Không hợp lệ** – Vui lòng chọn số sao từ 1 đến 5!     | X5    |
| EP-07 | `comment` rỗng          | 1      | 1       | 3      | `''`     | **Không hợp lệ** – Vui lòng nhập nội dung đánh giá!     | X6    |
| EP-08 | `comment` rỗng sau trim | 1      | 1       | 3      | `'   '`  | **Không hợp lệ** – Vui lòng nhập nội dung đánh giá!     | X6    |

#### Ghi chú kết quả mong đợi

- **BVA-01**: lưu rating 1.
- **BVA-02**: lưu rating 2.
- **BVA-03**: lưu rating 3.
- **BVA-04**: lưu rating 4.
- **BVA-05**: lưu rating 5.

### Tổng hợp method `addReview`

| Nhóm                         | Số TC     | Phạm vi phủ                              |
| ---------------------------- | --------- | ---------------------------------------- |
| **Standard BVA**             | 5         | **B1–B5**                                |
| **Equivalence Partitioning** | 8         | **V1–V4, X1–X6**                         |
| **Tổng theo hai bảng**       | **13 TC** | **Phủ toàn bộ tag thiết kế trong scope** |

Success message chính xác: `Gửi đánh giá thành công!`. Phải assert payload create và số bản ghi mới, không chỉ status.

Tổng **5 BVA + 8 EP = 13 case**. Lỗi validation phải dừng trước create; case user lỗi không được cần tra cứu phim.

## 5.5. Mapping automation

- EP-05 ↔ `testAddReviewUsesSameRatingValidation`: cùng lỗi rating=0; movie lấy động, comment thực tế `BVA Review Test`, tương đương nhưng khác literal fixture.
- EP-07 ↔ `testValidRatingContinuesToCommentValidation`: cùng rating=3, comment rỗng; movie lấy động.
- BVA-01..05 và những EP còn lại chưa có test riêng trong ReviewServiceTest.
- Endpoint `/reviews/validate` không gọi addReview, nên không chứng minh user/movie/comment validation hay persistence của method này.

# 6. METHOD 3 — `getRatingSummary($movieId)`

## 6.1. Input và kết quả

Ép movieId thành int; ID<=0 trả `['average_rating'=>0,'total_reviews'=>0]`. ID>0 gọi model trực tiếp, không xác minh phim tồn tại. Model tính trung bình làm tròn một chữ số thập phân và tổng số review; khi không có review trả zero summary.

## 6.2. Bước 1 — EP

| Conditions | Valid Partitions / nhánh xử lý | Tag | Invalid Partitions | Tag | Valid Boundaries | Tag |
|---|---|---|---|---|---|---|
| movieId | >0, chuyển cho model | V1 | <=0, trả zero summary | X1 | N/A | N/A |
| dữ liệu review | Không có review; có review | V2,V3 | N/A | N/A | N/A | N/A |
| phim không tồn tại | ID dương không có review vẫn trả zero | V2 | Không có nhánh error riêng | N/A | N/A | N/A |

X1 là ID không hợp lệ, nhưng output là dữ liệu mặc định, không phải `status=error`.

## 6.3. Bước 2 — Biên

Standard BVA **N/A**: chỉ có ID và dữ liệu truy vấn. Không lấy miền output average_rating 1..5 làm miền input movieId, không tự đặt max số review.

## 6.4. Bước 3 — Thiết kế Test Case

### Phần A — Test case cho Standard BVA

Nguyên tắc: mỗi lần chỉ thay đổi **một biến đang kiểm thử**, các input còn lại giữ ở giá trị hợp lệ.

Giá trị nominal / hợp lệ dùng để giữ cố định:

```java
movieId = 1 (phim tồn tại)
Fixture nominal = ba review rating 1, 5, 5 của phim 1
Không có review khác của phim 1 trong fixture.
```

**Standard BVA miền đóng: N/A** — Các input không có miền biên đóng được source quy định.

---

### Phần B — Test case phủ Equivalence Partitioning V/X

Ở mỗi test, chỉ cố tình làm sai một lớp không hợp lệ; các input khác giữ hợp lệ.

ID không hợp lệ được trả về zero summary theo source; không đổi output này thành `status=error`.

| STT   | Test case                            | movieId | Fixture review                                          | Kết quả mong đợi                                                        | Tag    |
| ----- | ------------------------------------ | ------- | ------------------------------------------------------- | ----------------------------------------------------------------------- | ------ |
| EP-01 | `movieId <= 0`                       | 0       | Bất kỳ                                                  | **Trả về dữ liệu** – average_rating=0, total_reviews=0; không gọi model | X1     |
| EP-02 | Phim chưa có review                  | 1       | Không có review                                         | **Trả về dữ liệu** – average_rating=0, total_reviews=0                  | V1, V2 |
| EP-03 | Phim có ba review để tính trung bình | 1       | Ba review rating 1,5,5; không có review khác của phim 1 | **Trả về dữ liệu** – average_rating=3.7, total_reviews=3                | V1, V3 |
| EP-04 | `movieId` không tồn tại              | 999999  | Phim không tồn tại, không có review                     | **Trả về dữ liệu** – average_rating=0, total_reviews=0                  | V1, V2 |

### Tổng hợp method `getRatingSummary`

| Nhóm                         | Số TC    | Phạm vi phủ                              |
| ---------------------------- | -------- | ---------------------------------------- |
| **Standard BVA**             | 0 (N/A)  | Không có miền đóng phù hợp               |
| **Equivalence Partitioning** | 4        | **V1–V3, X1**                            |
| **Tổng theo bảng EP**        | **4 TC** | **Phủ toàn bộ tag thiết kế trong scope** |

Tổng **4 EP**. Unit test service có mock chỉ chứng minh guard/chuyển tiếp kết quả; EP-03 cần model integration để xác nhận SQL AVG/ROUND/COUNT thật.

## 6.5. Mapping automation

Chưa có test nào trong ReviewServiceTest gọi getRatingSummary. Không tính các test rating validation thành coverage thống kê.

# 7. TỔNG HỢP THIẾT KẾ VÀ WHITE-BOX

| Method | Standard BVA | EP | Tổng dòng |
|---|---:|---:|---:|
| validateReviewInput | 5 | 3 | 8 |
| addReview | 5 | 8 | 13 |
| getRatingSummary | 0 (N/A) | 4 | 4 |
| Tổng | **10** | **15** | **25** |

| Decision/nhánh | Thiết kế tương ứng | Automation hiện có |
|---|---|---|
| Rating<1 / >5 / trong miền | M1 EP-02/03, BVA; M2 EP-05/06 | Có ở validation; add chỉ rating<1 |
| userId<=0 | M2 EP-02 | Chưa có |
| movieId<=0 / phim không tồn tại | M2 EP-03/04 | Chưa có |
| comment rỗng sau trim | M2 EP-07/08 | Có chuỗi rỗng, chưa có whitespace |
| create true / false | M2 success; fault injection bổ sung | Chưa có |
| summary guard / model | M3 EP-01..04 | Chưa có |

Không công bố tỷ lệ branch/line coverage từ bảng này; cần chạy công cụ đo riêng.

# 8. CASE BỔ SUNG VÀ ĐIỂM CẦN THEO DÕI

Các mục sau nằm ngoài tổng 25 dòng; ghi để tránh suy diễn điều kiện không có trong source.

| Tình huống | Expected theo source / cách xác minh |
|---|---|
| validateReviewInput thiếu rating hoặc rating=`abc` | Ép/default thành 0 → error |
| rating=`3.9` | Ép int thành 3 → vượt validation; chưa có rule từ chối số thập phân |
| validateReviewInput rating=3, user=0, movie=0, comment rỗng | success vì chỉ xét rating; không coi là addReview success |
| addReview comment=`'  Phim hay  '` | create nhận `Phim hay` |
| userId dương nhưng không tồn tại | Service không lookup user; integration có thể lỗi FK/exception, không tự kỳ vọng thông báo đăng nhập |
| create trả false, getError=`fixture error` | error: `Lỗi khi gửi đánh giá: fixture error`; model exception không được service bắt |
| Cùng user đánh giá cùng phim lần nữa | Không có kiểm tra trùng ở service và không có UNIQUE(user_id,movie_id) trong schema được đọc |
| getReviewsByMovieId(0) | [] và không query |
| getReviewsByMovieId(1) | Danh sách theo created_at giảm dần, có thông tin user; cần fixture thứ tự khác thời điểm |

# 9. EVIDENCE, LỆNH CHẠY VÀ KẾT LUẬN

Rà soát tĩnh thấy **8 test methods**: 6 validation rating + 2 addReview. Đây là số test trong source, không phải kết quả 8/8 PASS của lần soạn này. Chưa chạy lại ReviewServiceTest/HTTP/Newman. Constructor/setUp đang phụ thuộc DB dù validateReviewInput tự nó chỉ kiểm tra rating; nếu không có phim, setUp gọi markTestSkipped, không được báo PASS.

Chạy từ `backend` khi DB test có phim:

```powershell
php vendor/bin/phpunit tests/Services/ReviewServiceTest.php --no-coverage --do-not-cache-result --testdox
```

Từ thư mục gốc khi API sẵn sàng:

```powershell
node tests/automation/run-bva-and-log.js "BVA - Review"
```

Test addReview thành công phải dùng dữ liệu riêng và cleanup/rollback. Test thống kê phải tạo chính xác tập rating, không phụ thuộc review seed. Snapshot `latest-bva-result` có thể bị module khác ghi đè; chỉ sử dụng report chứa Module=Review và đúng mã test của lần chạy.

**Kết luận:** Thiết kế 25 dòng theo ba method. Standard BVA rating đủ năm giá trị; nominal=3 còn thiếu test validation riêng. Automation hiện có không chứng minh addReview thành công hoặc summary; không chuyển evidence của Seat sang Review.
