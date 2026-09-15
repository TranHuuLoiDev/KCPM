# Review Boundary Value Analysis

## 1. Rating boundary from source

The review service rule is:

```text
1 <= rating <= 5
```

Therefore the real boundary values are:

| Boundary | Value | Meaning |
|---|---:|---|
| min- | 0 | below lower boundary |
| min | 1 | lower valid boundary |
| min+ | 2 | immediately above min |
| nominal | 3 | valid representative value inside range |
| max- | 4 | immediately below max |
| max | 5 | upper valid boundary |
| max+ | 6 | above upper boundary |

## 2. Standard BVA point

The standard BVA set for the review rating is:

```text
1, 2, 3, 4, 5
```

This covers:

- min = 1
- min+ = 2
- nominal = 3
- max- = 4
- max = 5

## 3. Robustness/out-of-bound set

The source must be challenged with out-of-bound examples:

```text
0, 6
```

These two values prove that the service rejects values outside `[1,5]`.

## 4. Note about boundary naming

The collection file in the repository already contains a Postman folder with these exact values:

- `TC-REVIEW-BVA-01 | rating=0 | MIN - 1`
- `TC-REVIEW-BVA-02 | rating=1 | MIN`
- `TC-REVIEW-BVA-03 | rating=2 | MIN + 1`
- `TC-REVIEW-BVA-04 | rating=4 | MAX - 1`
- `TC-REVIEW-BVA-05 | rating=5 | MAX`
- `TC-REVIEW-BVA-06 | rating=6 | MAX + 1`

This matches the source rule and should be respected.
