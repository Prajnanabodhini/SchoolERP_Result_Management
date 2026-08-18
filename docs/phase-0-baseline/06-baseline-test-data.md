# Baseline regression contexts

No production row was created or changed. The cases below are derived from `db/school_management.sql`, which the authoritative prompt pack designates as the **current local MariaDB reconstruction baseline**. Live SELECT-only counts now match the configured imported database `school_management_phase0` across all 18 checked tables. The historical pre-cleanup dump is not used for current cases. No student or user PII is included.

| Case ID | Context / expected observation | Artifact evidence | Live status |
|---|---|---|---|
| `SUBJ-CANON-001` | Current/new writes use canonical `subjects.id` downstream | Static controller contract plus canonical baseline-source rows counted in `subject-id-contract-matrix.md` | Static/source verified; local UI pending |
| `SUBJ-LEGACY-001` | Historical mapping-ID rows coexist in subject-related columns | Baseline-source semantic counts in `subject-id-contract-matrix.md` | Existing source case; do not normalize |
| `SUBJ-REUSE-001` | Subject ID 1, `ENGLISH`, is reused by 15 Standard Wise mappings | Mapping IDs 1, 7, 13, 19, 25, 31, 38, 45, 52, 60, 68, 77, 85, 95, 101 | Pending |
| `SUBJ-CREATE-001` | Current Add Subject creates a master row and a mapping together | Static `SubjectController` evidence | Static only; do not create a subject for baseline evidence |
| `DASH-SUBJ-001` | Pending allocation/status row 47 stores canonical subject ID 2; the current dashboard relation treats 2 as a mapping ID and can display a different subject | Dump joins plus `TeacherSubjectAllocation` relation | Pending visual confirmation |
| `MARK-ABSENT` | At least one absent mark exists | 113 of 3,037 `student_marks` rows | Pending selection in isolated copy |
| `MARK-ZERO` | At least one non-absent zero-theory mark exists | 39 rows | Pending selection |
| `MARK-PASS-EXACT` | Exact theory pass boundary exists | 195 rows | Pending selection |
| `MARK-PASS-BELOW` | One below theory pass exists | 135 rows | Pending selection |
| `MARK-MAX` | Theory mark exactly at maximum exists | 212 rows | Pending selection |
| `MARK-LOCKED` | Submitted/locked mark context exists | 2,694 rows | Pending selection |
| `RESULT-PASS` | Generated passing result context exists | 202 of 319 result rows | Pending selection |
| `RESULT-FAIL` | Generated failing result context exists | 117 of 319 result rows | Pending selection |
| `RESULT-RANK` | Ranked passing context exists | 202 ranked rows | Pending tie-case identification |
| `DETAIL-ABSENT` | Generated result detail absent case exists | 34 of 1,045 rows | Pending selection |
| `DETAIL-PASS-EXACT` | Generated detail at exact pass exists | 60 rows | Pending selection |
| `DETAIL-PASS-BELOW` | Generated detail one below pass exists | 53 rows | Pending selection |
| `AUTH-CREATE-001` | Admin create/update directly assigns password input | Static controller evidence | Static only; no user/password change |
| `AUTH-LOGIN-001` | Local admin authentication using `name` identifier | Static request contract; operator confirms working local login | Agent visual recapture pending |
| `AUTH-CHANGE-PWD-001` | Change-password path hashes new password | Static controller evidence | Static only; do not invoke for baseline |
| `AUTH-RESET-PWD-001` | Forgot/reset path uses broker and hashing | Static route/controller evidence | Static only; do not invoke for baseline |
| `ROUTE-PUBLIC-001` | Public teacher-bulk exam-details/test/storage exposure candidates | `route-exposure-matrix.md` and route TSV | Repository route list verified |
| `ROUTE-STALE-001` | Two route targets reference absent controller methods | Static controller/route comparison | Do not invoke |
| `ROUTE-MUTATING-GET-001` | Two authenticated GET ERP sync routes perform writes | Static route/controller evidence | Do not invoke |
| `ROUTE-ADMIN-GUARD-001` | Administrator-intended routes have generic `auth` middleware | Generated route list | Repository route list verified |
| `ERP-MAP-001` | One anonymized local-to-MSSQL mapping path and returned student count | Mapping contract plus verified MSSQL table access | Specific context not yet selected; no PII captured |

## Missing contexts to create later as synthetic fixtures

After an isolated test environment and authorization exist, create synthetic cases for: incomplete draft, an explicit rank tie, each grade boundary, mixed theory/practical boundaries, missing mapping, duplicate historical/current subject identifiers, inactive-user login behavior, and authorization denial. Do not create these in production.

## Selection constraints

- Re-identification keys are intentionally excluded from this package.
- Operators should map a case ID to records inside the isolated local `school_management_phase0` copy.
- Expected values must be refreshed from a verified live backup or declared reference snapshot before regression execution.
