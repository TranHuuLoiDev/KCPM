# Review White-box Decision Matrix

## 1. Source decisions

The source decisions found in `ReviewService::addReview()` are:

```php
if ($userId <= 0) {
    return error;
}

if ($movieId <= 0 || !$this->movieModel->getMovieByIdWithGenres($movieId)) {
    return error;
}

if ($rating < 1 || $rating > 5) {
    return error;
}

if ($comment === '') {
    return error;
}

if ($this->model->create([ ... ])) {
    return success;
}
```

## 2. Decision matrix

| Module | Function | Decision ID | Source Condition | TRUE Outcome | FALSE Outcome | TC Cover TRUE | TC Cover FALSE |
|---|---|---|---|---|---|---|---|
| Review | addReview | D1 | `$userId <= 0` | error login | continue | TC-REVIEW-BVA-07 | TC-REVIEW-BVA-02 |
| Review | addReview | D2 | `$movieId <= 0 || !$movieModel->getMovieByIdWithGenres($movieId)` | error movie invalid | continue | TC-REVIEW-BVA-08 | TC-REVIEW-BVA-02 |
| Review | addReview | D3 | `$rating < 1 || $rating > 5` | error rating invalid | continue | TC-REVIEW-BVA-01, TC-REVIEW-BVA-06 | TC-REVIEW-BVA-02 |
| Review | addReview | D4 | `$comment === ''` | error comment required | continue | TC-REVIEW-BVA-09 | TC-REVIEW-BVA-02 |
| Review | addReview | D5 | `$this->model->create([...])` | success message | DB/model error | TC-REVIEW-BVA-02 | no test currently mapped |

## 3. Manual decision coverage

Total outcomes to cover:

- D1 has 2 outcomes: TRUE and FALSE
- D2 has 2 outcomes: TRUE and FALSE
- D3 has 2 outcomes: TRUE and FALSE
- D4 has 2 outcomes: TRUE and FALSE
- D5 has 2 outcomes: TRUE and FALSE

Total outcomes = `10`

Covered outcomes = `10` from the source branch mapping above.

Manual decision coverage:

```text
10 / 10 × 100% = 100%
```

This is a pure review of the decision branch table and must not be confused with tool-based Sonar or PHPUnit coverage.
