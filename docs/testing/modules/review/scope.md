# Review Scope

## 1. Module

Review

## 2. Function under test

`ReviewService::addReview($userId, $movieId, $rating, $comment)`

This is the validation and insert function used by the movie detail review page.

## 3. Source trace

The source path is:

- `backend/app/Controllers/ReviewController.php`
- `backend/app/Services/ReviewService.php`
- `backend/app/Models/ReviewModel.php`

The controller receives the HTTP form POST and passes the data to service:

`ReviewController::handleRequest()`

The business validation occurs in the service:

`ReviewService::addReview()`

The persistence is in model:

`ReviewModel::create()`

## 4. Inputs

- `userId`: taken from `$_SESSION['user']['id']`
- `movieId`: taken from `$_POST['movie_id']`
- `rating`: taken from `$_POST['rating']`
- `comment`: taken from `$_POST['comment']`

## 5. Type

Black-box: the review form sends POST data and receives status/message.

White-box: the validation decisions are visible directly in `ReviewService::addReview()`.

## 6. Why this function was selected

Because the function has:

- a numeric validation range (`rating` from `1` to `5`);
- multiple explicit decisions (`if` conditions);
- a service-framework error/success message contract;
- clear valid/invalid partitions;
- a clear request/response flow.

## 7. API mapping

The project `backend/api.php` file does not expose a dedicated `reviews` REST endpoint inside the current file. The Review flow in source is mainly implemented through the application controller/service route and not a separate API resource in `api.php`.

Thus the selected test scope is the service-level functional path rather than a standalone REST route from the API file.

## 8. Test scope

The test scope is only the rating validation and lifecycle in `addReview()`.

The test must check:

- login/user validation
- movie existence validation
- rating lower and upper range validation
- comment non-empty validation
- successful insert decision
