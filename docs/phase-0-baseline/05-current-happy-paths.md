# Current functional-path characterization

Status key: **Static** means characterized from routes/controllers/models without invoking a state-changing workflow. **Operator-confirmed** is current environment information supplied by the operator. **Locally verified read** means a SELECT-only MSSQL check succeeded. No state-changing route was invoked.

## Reconciled local validation status — 2026-08-18

- Remote deployment: `NOT CONNECTED / NOT INSPECTABLE`; no remote comparison is attempted.
- Local Laravel application and admin authentication: operator-confirmed working.
- Local configuration: `http://127.0.0.1:8000`, MariaDB `school_management_phase0`, database-backed session/cache/queue.
- MSSQL: Windows Integrated Authentication and actual reads verified for the four application tables.
- Follow-up access verification: MariaDB, Laravel, and Vite are reachable; the login page renders successfully. No login was submitted during preflight.

| Journey | Current observed contract | Status |
|---|---|---|
| Admin login | Public `/login`; submits `name` and password; rate limited; direct stored-value comparison; successful login regenerates session | Static; operator-confirmed locally working |
| User management | Authenticated admin-intended screens create/update users; password is assigned directly on those paths | Static; authorization concern recorded |
| Roles/permissions | Authenticated CRUD; `PermissionHelper` controls menu visibility but route inventory shows no role middleware on admin routes | Static |
| Year/section selection | Reads distinct values from MSSQL and writes four session context keys | Static; underlying MSSQL reads locally verified |
| Subject management | Current Add Subject creates a Subject Master row and one Standard Wise mapping together | Static |
| Exam configuration | New writes use canonical `subjects.id`; compatibility reads accept historical mapping IDs | Static |
| Teacher/class allocation | Authenticated MariaDB allocation path | Static |
| Teacher/subject allocation | Validates subject and mapping/exam, writes canonical subject ID, creates pending marks status | Static |
| Marks draft/save | Primary save path checks allocation/ownership or admin, lock state and bounds, and uses a transaction | Static |
| Marks submit/lock | Checks ownership/admin, completeness and bounds; locks marks and records completed status in a transaction | Static |
| Alternate marks edit | Authenticated path loads broad assignments; update lacks equivalent ownership, lock, range validation, and transaction controls | Static; deliberately not exploited |
| Administrator correction | Compatibility-aware admin edit path and audit records exist | Static; live pending |
| Completion status | `teacher_marks_status` uses `PENDING`/`COMPLETED`; new writes use canonical subject IDs | Static plus committed-dump evidence |
| Result generation | Requires locked marks, validates mapped subject, deduplicates latest marks, replaces generated result rows in a transaction | Static only; not invoked |
| Result sheet | Uses Result Management data plus direct MSSQL student lookup in current paths | Static; live output pending |
| Result register | Current implementation includes local mirrored student-table joins | Static; live output pending |
| Report card | Current implementation includes local student/fee-table joins in parts of the path | Static; live output pending |
| Analytics | Reads `student_marks`; its pass threshold logic differs from official result generation | Static |
| Logout/session | Logs out, invalidates session, regenerates CSRF token | Static |

## Current result rules observed in source

- An absent mark contributes zero and counts as a failed subject.
- Result totals aggregate subject maximum and obtained marks; percentage is rounded to two decimals.
- Official overall grade bands are A1 91+, A2 81+, B1 71+, B2 61+, C1 51+, C2 41+, D 33+, otherwise FAIL, subject-pass requirements still applying.
- Only passing results are ranked by percentage descending then obtained marks descending. Identical percentage and obtained marks share a rank, leaving gaps.
- Analytics applies a separate 35% pass interpretation, so it may diverge from official generated results.

These rules are source-code observations. Remote production-testing behavior cannot be inspected here; local visual/numerical confirmation must use the VS Code instance and must be labelled as local evidence.
