# Review Coverage

## 1. Coverage categories that must stay separate

The workplan explicitly says not to confuse different metrics.

The following metrics are relevant to Review:

1. Test pass rate
2. BVA tag coverage
3. White-box decision coverage
4. Tool-based code coverage

## 2. Test pass rate

This metric is not available yet because PHPUnit for `ReviewService` is missing from the current repository and the review route is not in the current `backend/api.php` REST enforcement.

If tests are added and run, the pass rate is:

```text
Passed / Total × 100%
```

## 3. BVA tag coverage

For the review rating rule, the designed BVA tags are:

- `EP-RATING-V1`
- `EP-RATING-X1`
- `EP-RATING-X2`
- `BVA-RATING-MIN`
- `BVA-RATING-MINPLUS`
- `BVA-RATING-NOMINAL`
- `BVA-RATING-MAXMINUS`
- `BVA-RATING-MAX`
- `ROBUST-RATING-LOW`
- `ROBUST-RATING-HIGH`

When the full postman collection is executed, the tag footprint may be computed by the test engineer from the request metadata. The repository currently includes the collection style in the test data as evidence.

## 4. Manual decision coverage

From `ReviewService::addReview()` there are 5 decisions:

- D1: `userId <= 0`
- D2: `movieId <= 0 || !$movieModel->getMovieByIdWithGenres($movieId)`
- D3: `rating < 1 || $rating > 5`
- D4: `comment === ''`
- D5: `create([...])`

Each decision has a TRUE outcome and a FALSE outcome.

Manual decision coverage formula:

```text
Covered Outcomes / Total Outcomes × 100%
```

Based on the matrix in `whitebox.md`, the known mapping yields:

```text
10 / 10 × 100% = 100%
```

This is a manual/white-box decision table result, not Sonar or PHPUnit coverage.

## 5. Tool-based coverage

Tool-based coverage requires:

- PHPUnit with Xdebug or PCOV
- or SonarCloud optionally reporting a coverage measure

The workspace currently does not provide evidence that the backend review service has a PHPUnit file and a running Xdebug/PCOV path. Therefore tool-based code coverage must remain `unavailable` in the evidence report until an executable review PHPUnit file and coverage driver are present.
