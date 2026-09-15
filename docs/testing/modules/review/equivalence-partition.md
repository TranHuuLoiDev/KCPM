# Review Equivalence Partition

## 1. Selected variable

`rating`

## 2. Source business rule

The range in `ReviewService::addReview()` is:

```php
if ($rating < 1 || $rating > 5) {
    return error;
}
```

Thus the source enforces:

```text
1 <= rating <= 5
```

## 3. Equivalence partitions

| Module | Condition | Partition | Range | Tag | Expected |
|---|---|---|---|---|---|
| Review | Rating input | Valid | `1..5` | EP-RATING-V1 | success |
| Review | Rating input | Invalid below | `<1` | EP-RATING-X1 | error |
| Review | Rating input | Invalid above | `>5` | EP-RATING-X2 | error |

## 4. Concrete examples

- Valid examples: `1`, `2`, `3`, `4`, `5`
- Invalid below: `0`, `-1`
- Invalid above: `6`, `7`

## 5. Other conditions

For the `movieId` field, the service requires:

- positive id, and
- movie must exist in `MovieModel::getMovieByIdWithGenres()`.

This results in:

| Module | Condition | Partition | Range | Tag | Expected |
|---|---|---|---|---|---|
| Review | movieId | Valid | positive existing movie id | EP-MOVIE-V1 | continue |
| Review | movieId | Invalid | `<=0` or missing movie | EP-MOVIE-X1 | movie invalid |

For the `comment` field:

| Module | Condition | Partition | Range | Tag | Expected |
|---|---|---|---|---|---|
| Review | comment | Valid | non-empty after trim | EP-COMMENT-V1 | continue |
| Review | comment | Invalid | empty after trim | EP-COMMENT-X1 | comment required |
