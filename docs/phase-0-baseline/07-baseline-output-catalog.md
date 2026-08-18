# Baseline output catalog

## Numerical evidence from designated local baseline source

The following values were parsed from `db/school_management.sql` (SHA-256 `f143b909fd9df56a6cf0c08461bf79b3f9023426167ac8f85bb1ddb9a6494ceb`), the authoritative current local reconstruction source. Live count comparison is recorded separately.

| Output | Value |
|---|---:|
| Subjects | 76 |
| Standard Wise mappings | 95 |
| Exam-subject rows | 63 |
| Teacher-subject allocations | 99 |
| Teacher marks-status rows | 100 |
| Student marks | 3,037 |
| Locked student marks | 2,694 |
| Absent student marks | 113 |
| Generated results | 319 |
| Passing / failing results | 202 / 117 |
| Ranked results | 202 |
| Result details | 1,045 |
| Absent result details | 34 |

Subject-ID semantic counts are catalogued separately in `subject-id-contract-matrix.md`.

## Verified MSSQL numerical evidence

SELECT-only reads through Windows Integrated Authentication and Laravel's `sqlsrv_olderp` connection produced: `SubStudentMst` 8,134 rows, `FeeMstStudent` 1,871, `StandardMst` 163, and `DivisionMst` 238. See `evidence/counts/mssql-read-verification.md`. These are local access checks against `Shirgaon SchoolERP`; they contain no row-level PII and are not remote-deployment output.

## Visual and report evidence

| Artifact | What it proves | Limitation |
|---|---|---|
| `evidence/screenshots/DEPLOY-UNKNOWN-localhost-login-404.png` | Historical prior-run request reached default local IIS | **Superseded/out of scope**; proves nothing about the remote deployment or local Laravel server |
| Application screens | `PENDING LOCAL RECAPTURE` | Use `http://127.0.0.1:8000` and label evidence `Source: Local VS Code Phase 0 instance` |
| Result sheet/register/report card rendered outputs | `NOT AVAILABLE` | Invoking generation or using guessed production context was unsafe; no operator-provided existing exports were found |

Representative formulas and result behavior are recorded in `05-current-happy-paths.md`. Exact per-student values are deliberately excluded to minimize PII.

## Comparison readiness

Repository and baseline-source structure can be compared objectively using hashes, routes, counts, semantic-ID counts, and static contracts. MSSQL reads and local MariaDB source-to-live table counts are verified. The local login UI renders; authenticated journey/report capture remains for the new Phase 0 run. Remote parity is outside this environment's scope.
