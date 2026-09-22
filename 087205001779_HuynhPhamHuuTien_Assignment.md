# Assignment: Kiểm thử chức năng đăng ký học phần

**Thời lượng:** 90 phút  
**Chủ đề:** Phân hoạch lớp tương đương, phân tích giá trị biên, thiết kế test case và kiểm thử tự động  
**Mức độ:** Cơ bản đến trung bình  
**Hình thức:** Cá nhân  
**Tổng điểm:** 10 điểm

---

## 1. Mục tiêu bài tập

1. Xác định được **điều kiện kiểm thử** từ một đặc tả yêu cầu đơn giản.
2. Áp dụng được kỹ thuật **phân hoạch lớp tương đương** để chia miền dữ liệu đầu vào thành các lớp hợp lệ và không hợp lệ.
3. Áp dụng được kỹ thuật **phân tích giá trị biên** để chọn các dữ liệu kiểm thử nằm gần ranh giới giữa vùng hợp lệ và không hợp lệ.
4. Thiết kế được bảng **test case** có đầy đủ input, expected result và tag bao phủ.
5. Viết được hàm kiểm tra logic và một số **unit test** cho các trường hợp biên.

---

## 2. Nội dung tham khảo

Bài tập này bám sát các nội dung chính trong phần kỹ thuật kiểm thử hộp đen:

- **Equivalence Partitioning**: phân chia miền dữ liệu đầu vào thành các lớp tương đương.
- **Boundary Value Analysis**: chọn giá trị tại biên và gần biên để kiểm thử.
- **Test case design**: thiết kế test case có input, expected outcome và tag bao phủ.
- **Test script / Unit test**: triển khai kiểm thử tự động bằng code.

Trong bài này, sinh viên cần đặc biệt chú ý đến cách trình bày theo mẫu:

| Conditions | Valid Partitions | Tag | Invalid Partitions | Tag | Valid Boundaries | Tag |
|---|---|---|---|---|---|---|

và bảng test case theo mẫu:

| Test Case | Input | Expected Outcome | New Tags Covered |
|---|---|---|---|

---

## 3. Mô tả bài toán

Hệ thống đăng ký học phần của Trường Đại học UTH cho phép sinh viên gửi yêu cầu đăng ký học phần.

Một yêu cầu đăng ký được xem là **hợp lệ** khi tất cả các điều kiện sau đồng thời thỏa mãn:

| Biến đầu vào | Ý nghĩa | Kiểu dữ liệu | Miền giá trị hợp lệ |
|---|---|---|---|
| `tinChi` | Số tín chỉ sinh viên muốn đăng ký | Số nguyên | Từ 10 đến 25 |
| `gpa` | Điểm trung bình tích lũy hiện tại | Số thực | Từ 2.0 đến 4.0 |
| `monNo` | Số môn sinh viên đang nợ | Số nguyên | Từ 0 đến 3 |
| `hocKy` | Học kỳ hiện tại của sinh viên | Số nguyên | Từ 1 đến 10 |

Hệ thống trả về:

- `True` hoặc thông báo **Hợp lệ** nếu tất cả điều kiện đều đúng.
- `False` hoặc thông báo **Không hợp lệ** nếu có ít nhất một điều kiện sai.

---

## 4. Giả định của bài toán

Để tránh hiểu nhầm, bài tập sử dụng các giả định sau:

1. Chỉ xét dữ liệu đầu vào là dữ liệu số.
2. Không xét dữ liệu `null`, rỗng, chuỗi ký tự hoặc định dạng sai.
3. `tinChi`, `monNo`, `hocKy` là số nguyên.
4. `gpa` là số thực, có thể có phần thập phân.
5. Một yêu cầu đăng ký hợp lệ khi và chỉ khi **tất cả** biến đầu vào nằm trong miền hợp lệ.

Công thức logic tổng quát:

$$
Valid =
(10 \leq tinChi \leq 25)
\land
(2.0 \leq gpa \leq 4.0)
\land
(0 \leq monNo \leq 3)
\land
(1 \leq hocKy \leq 10)
$$

---

# PHẦN A. ĐỀ BÀI GIAO CHO SINH VIÊN

---

## Câu 1. Xác định lớp tương đương

**Điểm:** 2 điểm

Hãy xác định các lớp tương đương hợp lệ và không hợp lệ cho từng biến đầu vào.

Sinh viên cần điền vào bảng sau:

| Biến đầu vào | Lớp hợp lệ | Tag | Lớp không hợp lệ | Tag    |
| ------------ | ---------- | --- | ---------------- | ------ |
| Số tín chỉ   | 10–25      | V1  | < 10; > 25       | X1; X2 |
| GPA          | 2.0–4.0    | V2  | < 2.0; > 4.0     | X3; X4 |
| Số môn nợ    | 0–3        | V3  | < 0; > 3         | X5; X6 |
| Học kỳ       | 1–10       | V4  | < 1; > 10        | X7; X8 |

### Yêu cầu

- Mỗi biến cần có ít nhất 1 lớp hợp lệ.
- Mỗi biến cần có ít nhất 2 lớp không hợp lệ:
  - Nhỏ hơn giá trị nhỏ nhất.
  - Lớn hơn giá trị lớn nhất.
- Mỗi lớp cần được đặt tag để phục vụ theo dõi độ bao phủ.
- Có thể đặt tag theo mẫu:
  - `V1`, `V2`, `V3`, ... cho lớp hợp lệ.
  - `X1`, `X2`, `X3`, ... cho lớp không hợp lệ.

---

## Câu 2. Phân tích giá trị biên

**Điểm:** 2 điểm

Áp dụng kỹ thuật **Standard Boundary Value Analysis** để xác định các giá trị cần kiểm thử cho từng biến.

Với mỗi biến có miền giá trị:

$$
[min, max]
$$

cần xác định 5 giá trị:

| Ký hiệu | Ý nghĩa |
|---|---|
| `min` | Giá trị nhỏ nhất hợp lệ |
| `min+` | Giá trị ngay trên giá trị nhỏ nhất |
| `nominal` | Giá trị đại diện nằm giữa miền hợp lệ |
| `max-` | Giá trị ngay dưới giá trị lớn nhất |
| `max` | Giá trị lớn nhất hợp lệ |



Sinh viên cần điền vào bảng sau:

| Biến đầu vào | min | min+ | nominal | max- | max | Tag biên                |
| ------------ | --: | ---: | ------: | ---: | --: | ----------------------- |
| Số tín chỉ   |  10 |   11 |      18 |   24 |  25 | B1, B2, B3, B4, B5      |
| GPA          | 2.0 |  2.1 |     3.0 |  3.9 | 4.0 | B6, B7, B8, B9, B10     |
| Số môn nợ    |   0 |    1 |       2 |    2 |   3 | B11, B12, B13, B14, B15 |
| Học kỳ       |   1 |    2 |       5 |    9 |  10 | B16, B17, B18, B19, B20 |

### Giả định

GPA được giả định lấy **1 chữ số thập phân**, do đó:

* `min+ = 2.1`
* `max- = 3.9`


### Gợi ý chọn nominal

| Biến | Miền hợp lệ | Có thể chọn nominal |
|---|---:|---:|
| Số tín chỉ | 10 đến 25 | 18 |
| GPA | 2.0 đến 4.0 | 3.0 |
| Số môn nợ | 0 đến 3 | 2 |
| Học kỳ | 1 đến 10 | 5 |

### Lưu ý

Với biến GPA là số thực, sinh viên có thể chọn `min+` và `max-` theo độ chính xác giả định.

Ví dụ nếu giả định GPA lấy 1 chữ số thập phân:

| Biên | Giá trị |
|---|---:|
| min | 2.0 |
| min+ | 2.1 |
| nominal | 3.0 |
| max- | 3.9 |
| max | 4.0 |

Nếu muốn kiểm thử mạnh hơn, có thể bổ sung các giá trị ngoài biên như `1.9` và `4.1`, nhưng phần này thuộc hướng **Robustness BVA**.

---

## Câu 3. Thiết kế test case

**Điểm:** 3 điểm

Dựa trên kết quả Câu 1 và Câu 2, hãy thiết kế bảng test case để kiểm thử chức năng đăng ký học phần.

### Yêu cầu

- Thiết kế tối thiểu 8 test case.
- Phải có cả test case hợp lệ và không hợp lệ.
- Phải có test case kiểm tra tại giá trị biên.
- Mỗi test case cần ghi rõ tag được bao phủ.
- Kết quả mong đợi phải ghi rõ:
  - **Hợp lệ**, hoặc
  - **Không hợp lệ**, kèm lý do.



### Phần A — Test case cho Standard BVA

Nguyên tắc: mỗi lần kiểm tra một biến, 3 biến còn lại giữ nominal. Theo Chương 4, với `n = 4` biến, bộ Standard BVA lý thuyết có `4n + 1 = 17` test case: một test nominal chung và bốn giá trị `min`, `min+`, `max-`, `max` cho mỗi biến. Do miền nguyên `monNo = [0,3]` quá hẹp nên `nominal = 2` trùng `max- = 2`; hai mục tiêu này được gắn chung trên một test. Vì vậy bảng dưới có **16 input thực thi khác nhau**, nhưng vẫn phủ đủ toàn bộ tag biên.

Sinh viên điền vào bảng sau:
|    STT | Test case                | Số tín chỉ | GPA | Số môn nợ | Học kỳ | Kết quả mong đợi | Tag      |
| -----: | ------------------------ | ---------: | --: | --------: | -----: | ---------------- | -------- |
| BVA-01 | Số tín chỉ – min         |         10 | 3.0 |         2 |      5 | **Hợp lệ**       | B1       |
| BVA-02 | Số tín chỉ – min+        |         11 | 3.0 |         2 |      5 | **Hợp lệ**       | B2       |
| BVA-03 | Nominal chung            |         18 | 3.0 |         2 |      5 | **Hợp lệ**       | B3, B8, B13, B14, B18 |
| BVA-04 | Số tín chỉ – max-        |         24 | 3.0 |         2 |      5 | **Hợp lệ**       | B4       |
| BVA-05 | Số tín chỉ – max         |         25 | 3.0 |         2 |      5 | **Hợp lệ**       | B5       |
| BVA-06 | GPA – min                |         18 | 2.0 |         2 |      5 | **Hợp lệ**       | B6       |
| BVA-07 | GPA – min+               |         18 | 2.1 |         2 |      5 | **Hợp lệ**       | B7       |
| BVA-09 | GPA – max-               |         18 | 3.9 |         2 |      5 | **Hợp lệ**       | B9       |
| BVA-10 | GPA – max                |         18 | 4.0 |         2 |      5 | **Hợp lệ**       | B10      |
| BVA-11 | Số môn nợ – min          |         18 | 3.0 |         0 |      5 | **Hợp lệ**       | B11      |
| BVA-12 | Số môn nợ – min+         |         18 | 3.0 |         1 |      5 | **Hợp lệ**       | B12      |
| BVA-14 | Số môn nợ – max          |         18 | 3.0 |         3 |      5 | **Hợp lệ**       | B15      |
| BVA-15 | Học kỳ – min             |         18 | 3.0 |         2 |      1 | **Hợp lệ**       | B16      |
| BVA-16 | Học kỳ – min+            |         18 | 3.0 |         2 |      2 | **Hợp lệ**       | B17      |
| BVA-18 | Học kỳ – max-            |         18 | 3.0 |         2 |      9 | **Hợp lệ**       | B19      |
| BVA-19 | Học kỳ – max             |         18 | 3.0 |         2 |     10 | **Hợp lệ**       | B20      |

---

### Phần B — Test case phủ Equivalence Partitioning V/X

Ở đây mỗi test sẽ cố tình làm một lớp không hợp lệ sai, các biến còn lại giữ giá trị hợp lệ. Cách này chứng minh rõ từng lớp X hoạt động.

|   STT | Test case                   | Số tín chỉ | GPA | Số môn nợ | Học kỳ | Kết quả mong đợi                         | Tag            |
| ----: | --------------------------- | ---------: | --: | --------: | -----: | ---------------------------------------- | -------------- |
| EP-01 | Tất cả giá trị hợp lệ       |         18 | 3.0 |         2 |      5 | **Hợp lệ**                               | V1, V2, V3, V4 |
| EP-02 | Số tín chỉ dưới miền hợp lệ |          9 | 3.0 |         2 |      5 | **Không hợp lệ** – Số tín chỉ nhỏ hơn 10 | X1             |
| EP-03 | Số tín chỉ trên miền hợp lệ |         26 | 3.0 |         2 |      5 | **Không hợp lệ** – Số tín chỉ lớn hơn 25 | X2             |
| EP-04 | GPA dưới miền hợp lệ        |         18 | 1.9 |         2 |      5 | **Không hợp lệ** – GPA nhỏ hơn 2.0       | X3             |
| EP-05 | GPA trên miền hợp lệ        |         18 | 4.1 |         2 |      5 | **Không hợp lệ** – GPA lớn hơn 4.0       | X4             |
| EP-06 | Số môn nợ dưới miền hợp lệ  |         18 | 3.0 |        -1 |      5 | **Không hợp lệ** – Số môn nợ nhỏ hơn 0   | X5             |
| EP-07 | Số môn nợ trên miền hợp lệ  |         18 | 3.0 |         4 |      5 | **Không hợp lệ** – Số môn nợ lớn hơn 3   | X6             |
| EP-08 | Học kỳ dưới miền hợp lệ     |         18 | 3.0 |         2 |      0 | **Không hợp lệ** – Học kỳ nhỏ hơn 1      | X7             |
| EP-09 | Học kỳ trên miền hợp lệ     |         18 | 3.0 |         2 |     11 | **Không hợp lệ** – Học kỳ lớn hơn 10     | X8             |




| Nhóm                         |     Số TC | Phạm vi phủ               |
| ---------------------------- | --------: | ------------------------- |
| **Standard BVA**             |        16 | **B1–B20; các điểm trùng dùng chung một TC** |
| **Equivalence Partitioning** |         9 | **V1–V4, X1–X8**          |
| **Tổng**                     | **25 TC** | **Phủ toàn bộ V, X và B** |


## Câu 4. Triển khai kiểm thử tự động

**Điểm:** 3 điểm

Hãy viết chương trình kiểm tra logic của hàm:

```python
ValidateDangKy(tinChi, gpa, monNo, hocKy)
```

Hàm trả về:

- `True` nếu tất cả đầu vào hợp lệ.
- `False` nếu có ít nhất một đầu vào không hợp lệ.

Sinh viên có thể chọn một trong các ngôn ngữ sau:

| Ngôn ngữ | Framework gợi ý |
|---|---|
| Python | `unittest` hoặc `pytest` |
| Java | `JUnit` |
| C# | `NUnit` hoặc `xUnit` |

### Yêu cầu bắt buộc

1. Viết hàm `ValidateDangKy`.
2. Viết ít nhất 2 unit test cho trường hợp biên.
3. Các unit test phải dựa trên giá trị biên đã xác định ở Câu 2.
4. Có ít nhất:
   - 1 test case hợp lệ tại biên.
   - 1 test case không hợp lệ ngoài biên.

---


## Triển khai kiểm thử tự động

Sử dụng Java và framework **JUnit 5** để kiểm tra logic của hàm `ValidateDangKy(tinChi, gpa, monNo, hocKy)`.

### 1. Hàm ValidateDangKy

```java
public class DangKyValidator {

    public static boolean ValidateDangKy(
            int tinChi,
            double gpa,
            int monNo,
            int hocKy) {

        return tinChi >= 10 && tinChi <= 25
                && gpa >= 2.0 && gpa <= 4.0
                && monNo >= 0 && monNo <= 3
                && hocKy >= 1 && hocKy <= 10;
    }
}
```

### 2. Unit test bằng JUnit 5

Các test case được xây dựng dựa trên các giá trị biên đã xác định ở Câu 2.

```java
import static org.junit.jupiter.api.Assertions.assertTrue;
import static org.junit.jupiter.api.Assertions.assertFalse;

import org.junit.jupiter.api.Test;

class DangKyValidatorTest {

    // B1, B6, B11, B16
    @Test
    void testAllMinimumBoundaryValuesValid() {
        assertTrue(
                DangKyValidator.ValidateDangKy(
                        10, 2.0, 0, 1
                )
        );
    }

    // B5, B10, B15, B20
    @Test
    void testAllMaximumBoundaryValuesValid() {
        assertTrue(
                DangKyValidator.ValidateDangKy(
                        25, 4.0, 3, 10
                )
        );
    }

    // X1 - ngoài biên số tín chỉ
    @Test
    void testTinChiBelowMinimumInvalid() {
        assertFalse(
                DangKyValidator.ValidateDangKy(
                        9, 3.0, 2, 5
                )
        );
    }

    // X2 - ngoài biên số tín chỉ
    @Test
    void testTinChiAboveMaximumInvalid() {
        assertFalse(
                DangKyValidator.ValidateDangKy(
                        26, 3.0, 2, 5
                )
        );
    }

    // X3 - ngoài biên GPA
    @Test
    void testGpaBelowMinimumInvalid() {
        assertFalse(
                DangKyValidator.ValidateDangKy(
                        18, 1.9, 2, 5
                )
        );
    }

    // X4 - ngoài biên GPA
    @Test
    void testGpaAboveMaximumInvalid() {
        assertFalse(
                DangKyValidator.ValidateDangKy(
                        18, 4.1, 2, 5
                )
        );
    }

    // X5 - ngoài biên số môn nợ
    @Test
    void testMonNoBelowMinimumInvalid() {
        assertFalse(
                DangKyValidator.ValidateDangKy(
                        18, 3.0, -1, 5
                )
        );
    }

    // X7 - ngoài biên học kỳ
    @Test
    void testHocKyBelowMinimumInvalid() {
        assertFalse(
                DangKyValidator.ValidateDangKy(
                        18, 3.0, 2, 0
                )
        );
    }
}
```

### 3. Đối chiếu với các câu trước

| Nội dung C4             | Mapping                             |
| ----------------------- | ----------------------------------- |
| Số tín chỉ              | Câu 2: B1, B5; Câu 1: X1, X2        |
| GPA                     | Câu 2: B6, B10; Câu 1: X3, X4       |
| Số môn nợ               | Câu 2: B11, B15; Câu 1: X5          |
| Học kỳ                  | Câu 2: B16, B20; Câu 1: X7          |
| Hợp lệ tại biên         | `10, 2.0, 0, 1` và `25, 4.0, 3, 10` |
| Không hợp lệ ngoài biên | `9`, `26`, `1.9`, `4.1`, `-1`, `0`  |
| Framework               | JUnit 5                             |
| Kết quả                 | `True` / `False`                    |

### 4. Kết quả chạy test

```text
Test run finished.
8 tests successful.
0 tests failed.
```


# PHẦN B. BẢNG CHẤM ĐIỂM CHI TIẾT

---

## Câu 1. Lớp tương đương: 2 điểm

| Tiêu chí | Điểm |
|---|---:|
| Xác định đúng lớp hợp lệ cho 4 biến | 0.8 |
| Xác định đúng lớp không hợp lệ nhỏ hơn min | 0.4 |
| Xác định đúng lớp không hợp lệ lớn hơn max | 0.4 |
| Có đặt tag rõ ràng cho các lớp | 0.4 |
| **Tổng** | **2.0** |

---

## Câu 2. Giá trị biên: 2 điểm

| Tiêu chí | Điểm |
|---|---:|
| Xác định đúng biên cho số tín chỉ | 0.5 |
| Xác định đúng biên cho GPA | 0.5 |
| Xác định đúng biên cho số môn nợ | 0.5 |
| Xác định đúng biên cho học kỳ | 0.5 |
| **Tổng** | **2.0** |

---

## Câu 3. Test case: 3 điểm

| Tiêu chí | Điểm |
|---|---:|
| Có tối thiểu 8 test case | 0.5 |
| Có test case hợp lệ | 0.5 |
| Có test case không hợp lệ | 0.5 |
| Có test case tại biên hoặc gần biên | 0.5 |
| Expected result rõ ràng, có lý do khi không hợp lệ | 0.5 |
| Có tag được bao phủ | 0.5 |
| **Tổng** | **3.0** |

---

## Câu 4. Unit test: 3 điểm

| Tiêu chí | Điểm |
|---|---:|
| Viết đúng hàm `ValidateDangKy` | 1.0 |
| Có sử dụng framework unit test | 0.5 |
| Có ít nhất 2 test case biên | 0.5 |
| Có ít nhất 1 case hợp lệ tại biên | 0.5 |
| Có ít nhất 1 case không hợp lệ ngoài biên | 0.5 |
| **Tổng** | **3.0** |

---

# PHẦN C. NHẬN XÉT

## 1. Vì sao cần tag?

Tag giúp theo dõi test case nào đã bao phủ lớp nào hoặc biên nào.

Ví dụ:

| Tag | Ý nghĩa |
|---|---|
| V1 | Số tín chỉ hợp lệ |
| X1 | Số tín chỉ nhỏ hơn min |
| X2 | Số tín chỉ lớn hơn max |
| B1 | Số tín chỉ tại min |
| B5 | Số tín chỉ tại max |

Khi thiết kế test case, sinh viên có thể ghi:

| Test case | Tag bao phủ |
|---|---|
| TC01 | V1, V2, V3, V4 |
| TC02 | B1, B6, B11, B16 |
| TC04 | X1 |

---


