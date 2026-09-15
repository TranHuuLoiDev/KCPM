# Review Test Cases

## 1. Objective

Design test cases for `ReviewService::addReview()` covering validation decisions and success/failure outcomes.

## 2. Base test case template

| TC ID | Module | Function | Type | Input | Preconditions | Expected Output | Boundary | Partition | Tags |
|---|---|---|---|---|---|---|---|---|---|
| TC-REVIEW-BVA-01 | Review | addReview | Black-box + White-box | userId=1, movieId=1, rating=0, comment='BVA test' | valid logged user, valid existing movie | error | min- | invalid below | EP-RATING-X1, ROBUST-RATING-LOW |
| TC-REVIEW-BVA-02 | Review | addReview | Black-box + White-box | userId=1, movieId=1, rating=1, comment='BVA test' | valid logged user, valid existing movie | success | min | valid | EP-RATING-V1, BVA-RATING-MIN |
| TC-REVIEW-BVA-03 | Review | addReview | Black-box + White-box | userId=1, movieId=1, rating=2, comment='BVA test' | valid logged user, valid existing movie | success | min+ | valid | EP-RATING-V1, BVA-RATING-MINPLUS |
| TC-REVIEW-BVA-04 | Review | addReview | Black-box + White-box | userId=1, movieId=1, rating=4, comment='BVA test' | valid logged user, valid existing movie | success | max- | valid | EP-RATING-V1, BVA-RATING-MAXMINUS |
| TC-REVIEW-BVA-05 | Review | addReview | Black-box + White-box | userId=1, movieId=1, rating=5, comment='BVA test' | valid logged user, valid existing movie | success | max | valid | EP-RATING-V1, BVA-RATING-MAX |
| TC-REVIEW-BVA-06 | Review | addReview | Black-box + White-box | userId=1, movieId=1, rating=6, comment='BVA test' | valid logged user, valid existing movie | error | max+ | invalid above | EP-RATING-X2, ROBUST-RATING-HIGH |
| TC-REVIEW-BVA-07 | Review | addReview | Black-box + White-box | userId=0, movieId=1, rating=3, comment='BVA test' | invalid/no session user | error | precondition | user invalid | PRECOND-USER |
| TC-REVIEW-BVA-08 | Review | addReview | Black-box + White-box | userId=1, movieId=0, rating=3, comment='BVA test' | valid logged user, movie id invalid | error | precondition | movie invalid | PRECOND-MOVIE |
| TC-REVIEW-BVA-09 | Review | addReview | Black-box + White-box | userId=1, movieId=1, rating=3, comment='' | valid logged user, valid existing movie | error | comment empty | invalid comment | EP-COMMENT-X1 |

## 3. Mapping to real collection request style

The existing Postman collection in the workspace provides six request samples in the review folder:

- `TC-REVIEW-BVA-01` with rating `0`
- `TC-REVIEW-BVA-02` with rating `1`
- `TC-REVIEW-BVA-03` with rating `2`
- `TC-REVIEW-BVA-04` with rating `4`
- `TC-REVIEW-BVA-05` with rating `5`
- `TC-REVIEW-BVA-06` with rating `6`

This aligns with the review service range `[1,5]` and proves the repository already has an intended trace for the numeric boundary.

## 4. Important mismatch to report, not hide

The service currently has no dedicated PHPUnit file for `ReviewService`. The tests folder contains `AuthServicesTest`, `BookingServiceTest`, `RoomServiceTest`, `SeatServiceTest`, `TheatreServiceTest`, but no `ReviewServiceTest`. Therefore, the `Review` test case set should be tracked as a design artifact and mapping should be recorded as not yet available in PHPUnit.
