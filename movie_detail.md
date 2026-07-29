# Movie Detail Data Flow

File page: `movie_details.php`

Input chính:

```php
movie_details.php?id={movie_id}
```

Page này cần lấy dữ liệu từ 3 nhóm chính:

1. Thông tin phim
2. Lịch chiếu của phim
3. Review của phim

---

## 1. Lấy Thông Tin Phim

Mục đích: render thông tin cơ bản của phim trên trang chi tiết.

Flow:

```txt
movie_details.php
    -> MovieController::getMovieById($movieId)
        -> MovieService::getMovieById($movieId)
            -> MovieModel::getMovieByIdWithGenres($movieId)
```

Hàm model:

```php
MovieModel::getMovieByIdWithGenres($id)
```

Dữ liệu lấy được:

```txt
movies.id
movies.title
movies.description
movies.director
movies.cast
movies.age_restriction
movies.country
movies.duration
movies.screening_date
movies.poster
movies.trailer_url
movies.status
movies.is_active
genre_ids
genre_names
```

Ý nghĩa:

```txt
genre_ids:
- dùng cho form admin edit movie để checked checkbox thể loại.

genre_names:
- dùng cho movie_details.php để hiển thị tên thể loại.
```

Ví dụ render:

```php
$movie['title']
$movie['poster']
$movie['genre_names']
$movie['description']
$movie['director']
$movie['cast']
$movie['duration']
$movie['age_restriction']
$movie['country']
$movie['trailer_url']
```

---

## 2. Lấy Lịch Chiếu Theo Phim

Mục đích: render danh sách lịch chiếu để người dùng chọn suất chiếu.

Flow:

```txt
movie_details.php
    -> ShowtimeController::getShowtimesByMovieId($movieId)
        -> ShowtimeService::getShowtimesByMovieId($movieId)
            -> ShowtimeModel::getByMovieId($movieId)
```

Hàm model:

```php
ShowtimeModel::getByMovieId($movieId)
```

Dữ liệu lấy được:

```txt
showtime_id
movie_id
room_id
show_date
start_time
end_time
base_price
status
room_name
theatre_id
theatre_name
theatre_address
theatre_city
```

Điều kiện lọc:

```txt
showtimes.movie_id = movie_id
showtimes.status = 'active'
showtimes.show_date >= CURDATE()
```

Ý nghĩa:

```txt
showtime_id:
- dùng để tạo link sang booking.php.

room_id:
- chưa dùng để lấy ghế ở movie_details.php.
- sẽ dùng ở booking.php sau khi người dùng chọn showtime.

theatre_name, theatre_address, theatre_city:
- dùng để hiển thị rạp chiếu.

show_date, start_time, end_time:
- dùng để hiển thị ngày giờ chiếu.

base_price:
- dùng để hiển thị giá vé cơ bản.
```

Ví dụ link sang booking:

```php
booking.php?showtime_id=<?= $showtime['showtime_id'] ?>
```

Lưu ý:

```txt
movie_details.php chỉ hiển thị lịch chiếu.
Không lấy seats ở page này.
Danh sách ghế sẽ được lấy ở booking.php dựa trên showtime_id.
```

---

## 3. Lấy Review Theo Phim

Mục đích: render danh sách đánh giá và điểm trung bình của phim.

Database có bảng:

```txt
reviews
```

Project đã có:

```txt
ReviewModel
ReviewService
ReviewController
```

Flow:

```txt
movie_details.php
    -> ReviewController::getReviewsByMovieId($movieId)
        -> ReviewService::getReviewsByMovieId($movieId)
            -> ReviewModel::getByMovieId($movieId)
```

Hàm model:

```php
ReviewModel::getByMovieId($movieId)
```

Dữ liệu cần lấy:

```txt
reviews.id
reviews.user_id
reviews.movie_id
reviews.rating
reviews.comment
reviews.created_at
users.first_name
users.last_name
```

Query sẽ join:

```txt
reviews
    -> users
```

Mục đích render:

```php
$review['rating']
$review['comment']
$review['first_name'] . ' ' . $review['last_name']
$review['created_at']
```

---

## 4. Lấy Thống Kê Rating

Mục đích: hiển thị điểm trung bình và tổng số review.

Flow:

```txt
movie_details.php
    -> ReviewController::getRatingSummary($movieId)
        -> ReviewService::getRatingSummary($movieId)
            -> ReviewModel::getRatingSummary($movieId)
```

Hàm model:

```php
ReviewModel::getRatingSummary($movieId)
```

Dữ liệu cần trả về:

```txt
average_rating
total_reviews
```

Ví dụ:

```php
[
    'average_rating' => 4.2,
    'total_reviews' => 15
]
```

---

## 5. Tổng Hợp Dữ Liệu Cho movie_details.php

Trong `movie_details.php`, dữ liệu nên được lấy như sau:

```php
$movieId = (int)($_GET['id'] ?? 0);

$movieController = new MovieController();
$showtimeController = new ShowtimeController();

$movie = $movieController->getMovieById($movieId);
$showtimes = $showtimeController->getShowtimesByMovieId($movieId);
```

```php
$reviewController = new ReviewController();

$reviews = $reviewController->getReviewsByMovieId($movieId);
$ratingSummary = $reviewController->getRatingSummary($movieId);
```

Kết quả page cần render:

```txt
$movie:
- thông tin phim
- poster
- trailer
- thể loại

$showtimes:
- danh sách lịch chiếu
- rạp
- phòng
- ngày giờ
- giá vé cơ bản
- link sang booking.php?showtime_id=...

$reviews:
- danh sách review của người dùng

$ratingSummary:
- điểm trung bình
- tổng số review
```

---

## 6. Contract Sang Page Booking

Khi người dùng chọn lịch chiếu:

```txt
movie_details.php
    -> booking.php?showtime_id={showtime_id}
```

`movie_details.php` không truyền:

```txt
movie_id
room_id
seat_id
```

Lý do:

```txt
booking.php chỉ cần showtime_id.
Từ showtime_id, booking.php có thể lấy lại movie, room, theatre và seats một cách chính xác.
```
