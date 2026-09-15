# Review Evidence

## 1. Source evidence

- [backend/app/Services/ReviewService.php](backend/app/Services/ReviewService.php)
- [backend/app/Controllers/ReviewController.php](backend/app/Controllers/ReviewController.php)
- [backend/app/Models/ReviewModel.php](backend/app/Models/ReviewModel.php)

## 2. Collection evidence

The review BVA collection examples are visible in:

- [tests/postman/BVA_MovieBooking.postman_collection.json](tests/postman/BVA_MovieBooking.postman_collection.json)

The review folder in the collection contains individual requests:

- `TC-REVIEW-BVA-01 | rating=0 | MIN - 1`
- `TC-REVIEW-BVA-02 | rating=1 | MIN`
- `TC-REVIEW-BVA-03 | rating=2 | MIN + 1`
- `TC-REVIEW-BVA-04 | rating=4 | MAX - 1`
- `TC-REVIEW-BVA-05 | rating=5 | MAX`
- `TC-REVIEW-BVA-06 | rating=6 | MAX + 1`

## 3. Command evidence

The current environment does not have a working backend PHPUnit command due to dependency and PHP environment mismatch.

Commands and outputs verified:

```powershell
cd c:\xampp\htdocs\movie-ticket-booking\backend ; php vendor/bin/phpunit
```

Output:

```text
php : The term 'php' is not recognized as the name of a cmdlet, function,
script file, or operable program.
```

```powershell
cd c:\xampp\htdocs\movie-ticket-booking\backend ; C:\xampp\php\php.exe vendor/bin/phpunit
```

Output:

```text
Could not open input file: vendor/bin/phpunit
```

```powershell
cd c:\xampp\htdocs\movie-ticket-booking\backend ; C:\xampp\php\php.exe composer.phar install --no-interaction
```

Output:

```text
The zip extension and unzip/7z commands are both missing, skipping.
```

## 4. Conclusion

The Review module is already clearly analyzable from source and collection artifacts. The test-design evidence is valid and consistent with the business rule from source. However, the repository currently lacks a proper PHPUnit review test file and a working dependency install path, so the mapping to PHPUnit and tool-based coverage is not yet fully executable in this workspace.
