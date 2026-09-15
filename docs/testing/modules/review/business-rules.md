# Review Business Rules

## 1. Business rule from source

The source validation logic in `ReviewService::addReview()` is:

```php
$userId = (int)$userId;
$movieId = (int)$movieId;
$rating = (int)$rating;
$comment = trim($comment);

if ($userId <= 0) {
    return ['status' => 'error', 'message' => 'Vui lòng đăng nhập để đánh giá phim!'];
}

if ($movieId <= 0 || !$this->movieModel->getMovieByIdWithGenres($movieId)) {
    return ['status' => 'error', 'message' => 'Phim không hợp lệ!'];
}

if ($rating < 1 || $rating > 5) {
    return ['status' => 'error', 'message' => 'Vui lòng chọn số sao từ 1 đến 5!'];
}

if ($comment === '') {
    return ['status' => 'error', 'message' => 'Vui lòng nhập nội dung đánh giá!'];
}

if ($this->model->create([...])) {
    return ['status' => 'success', 'message' => 'Gửi đánh giá thành công!'];
}
```

## 2. Validation conditions

The conditions found are:

- `D1: userId > 0`
- `D2: movieId > 0 AND movie exists`
- `D3: rating >= 1`
- `D4: rating <= 5`
- `D5: comment after trim != ''`

## 3. Preconditions

The test case must include:

- valid session user; `user_id` from session must exist and be positive;
- valid movie; `movie_id` must exist in `movies` table;
- valid `rating` range `[1,5]`;
- valid non-empty `comment` after trimming.

## 4. Expected result

The expected service result must be:

- `['status' => 'error', 'message' => 'Vui lòng đăng nhập để đánh giá phim!']` if user is not logged in;
- `['status' => 'error', 'message' => 'Phim không hợp lệ!']` if movie id is invalid or movie does not exist;
- `['status' => 'error', 'message' => 'Vui lòng chọn số sao từ 1 đến 5!']` if rating is outside `[1,5]`;
- `['status' => 'error', 'message' => 'Vui lòng nhập nội dung đánh giá!']` if comment is empty;
- `['status' => 'success', 'message' => 'Gửi đánh giá thành công!']` if all validation decides pass and model insert returns true.

## 5. Important note

The rating variable in the source is coerced to integer. Because of that, the service will compare integer input values. It does not accept a non-integer rating as a valid input to the intended branch.
