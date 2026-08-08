## Database

### TC-DB-001 — Execute movie seed script successfully

- **Jira:** KAN-15
- **Type:** Functional
- **Level:** Database
- **Objective:** Verify that the movie seed SQL script executes successfully after refactoring.

#### Preconditions
- MySQL database is available.
- Movie seed SQL script is available.

#### Steps
1. Execute the movie seed SQL script.
2. Observe the execution result.

#### Expected Result
- SQL script executes successfully.
- No SQL syntax or runtime error occurs.

#### Actual Result
- Movie seed SQL script executed successfully.
- No SQL syntax or runtime error occurred.
- Database records were inserted/updated successfully.

#### Status
`PASS`

---

### TC-DB-002 — Verify movie status after seed

- **Jira:** KAN-15
- **Type:** Data Verification
- **Level:** Database
- **Objective:** Verify that the refactoring does not change the `now_showing` movie status.

#### Preconditions
- Movie seed SQL script has been executed successfully.

#### Steps
1. Query movies with status `now_showing`.
2. Compare the result with the expected movie data.

#### Expected Result
- Movies previously using `now_showing` still have:
  `status = 'now_showing'`.
- No unrelated movie data is changed.

#### Actual Result

- Movie seed SQL script executed successfully.
- Movies with `status = 'now_showing'` were returned as expected.
- Movie status data remained consistent with the expected result after refactoring.
- No unrelated movie status was changed.

#### Status

`PASS`

---

### TC-DB-003 — Verify duplicated literal has been removed

- **Jira:** KAN-15
- **Type:** Static Analysis / Code Verification
- **Level:** Database
- **Objective:** Verify that the duplicated `now_showing` literal has been replaced by the shared variable.

#### Preconditions
- KAN-15 implementation has been completed.

#### Steps
1. Search the SQL seed script for `'now_showing'`.
2. Verify that `@STATUS_NOW_SHOWING` is defined.
3. Verify that the movie INSERT statements use `@STATUS_NOW_SHOWING`.
4. Run SonarCloud analysis.

#### Expected Result
- `@STATUS_NOW_SHOWING` is defined once.
- The 8 duplicated usages are replaced.
- SonarCloud no longer reports the duplicated literal issue.

#### Actual Result
- Not executed yet.

#### Status
`NOT RUN`