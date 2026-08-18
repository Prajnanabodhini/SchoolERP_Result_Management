# SchoolERP Result Management System
## Phase 0 — Baseline, Protection, and Current-State Characterization

**Repository:** `Prajnanabodhini/SchoolERP_Result_Management`  
**Application:** Laravel 13 Result Management System  
**Status:** Current code is running and deployed locally for production/user testing.  
**Phase type:** Read-mostly / baseline / protection.  
**Primary rule:** **Do not refactor, clean up, rename, delete, or redesign application code in Phase 0.**

---

## Frozen Target Architecture Context

The following target architecture has already been agreed and is **context only for Phase 0**. Phase 0 must not implement it yet.

1. The working MSSQL database is the authoritative School ERP data source for:
   - Academic Year
   - Section
   - Standard
   - Division
   - Student
   - GR and related student information required by the Result System
2. These master records must eventually be fetched live from MSSQL using their source IDs and must not be replicated as authoritative master data in the Result Management MariaDB database.
3. MariaDB is the authoritative database for Result Management data such as:
   - users, roles, permissions
   - subject master
   - standard-wise subject mapping
   - exam configuration
   - teacher allocations
   - marks and marks status
   - generated results and result details
   - application/security/audit logs
4. Subject Master contains the overall subject list.
5. Standard Wise Subject Master assigns Subject Master records to specific standards/classes.
6. The future architecture is modular, object-oriented, loosely coupled, repository/service/DTO driven, secure by default, fully logged, and extensively tested.
7. None of the above architectural changes are to be implemented during Phase 0.

---

## Existing Repository Facts That Must Be Preserved During Phase 0

The current application is a working Laravel application with:
- Laravel 13 / PHP 8.3+
- Blade / Tailwind CSS / Alpine.js / Vite
- MariaDB/MySQL for Result System data
- MSSQL connection currently named/configured as `sqlsrv_olderp` in parts of the code
- live student/academic data access through MSSQL
- teacher allocation, marks entry, marks submission/locking, result generation, result sheets/register/report cards
- current users actively testing the deployed application

Known architectural/security issues already discovered must **not** be “fixed incidentally” during Phase 0. They are inputs to later phases.

Examples include:
- plaintext/comparable password handling
- weak server-side authorization boundaries
- unsafe alternate marks-edit path
- subject ID semantic inconsistencies
- duplicate/evolutionary code paths
- migration/schema drift
- local copies of data that should eventually come from MSSQL
- stale routes/relationships
- old development artifacts

Phase 0 is for producing a trustworthy baseline, not repairing these issues.

---

## Universal Phase 0 Safety Rules

These rules apply to every Phase 0 step.

### Absolutely prohibited
- Do not alter application PHP, Blade, JS, CSS, configuration, migrations, SQL schema, or business logic.
- Do not rename classes, methods, routes, tables, columns, files, folders, or database connections.
- Do not delete “unused” files.
- Do not clean up zero-byte/root artifacts.
- Do not run migrations against production.
- Do not run seeders against production.
- Do not truncate or delete production data.
- Do not write to the authoritative MSSQL database.
- Do not reset passwords.
- Do not regenerate results merely for testing unless explicitly authorized and performed in a controlled test context.
- Do not fabricate environment values, credentials, database rows, user accounts, screenshots, or outputs.
- Do not interpret a local copied table as authoritative if its source is actually MSSQL.
- Do not silently change the current working behavior.

### Allowed in Phase 0
- Read repository files.
- Inspect Git metadata.
- Read application/database configuration.
- Read database schemas and safe SELECT-only data where access is available.
- Capture hashes, counts, inventories, route lists, environment metadata, and screenshots.
- Create documentation and baseline evidence outside runtime code.
- Create backups using non-destructive tools.
- Create a Git tag/branch only when explicitly authorized by the operator.
- Execute non-mutating diagnostic commands.
- Run existing tests only against development/test environments unless explicitly proven safe.
- Record unavailable information as `NOT AVAILABLE` rather than guessing.

### Production safety
Before any command that could potentially mutate Git remotes, databases, files, or deployed state:
1. State what the command changes.
2. Confirm it targets the intended environment.
3. Prefer a dry run or read-only equivalent where possible.
4. If environment identity is uncertain, stop and document the uncertainty.

---

## Output Quality Standard

Every deliverable must distinguish:
- **Observed fact** — directly confirmed from repo/database/server.
- **Inference** — conclusion based on evidence.
- **Unknown** — not available or not safely verifiable.
- **Future target** — agreed architecture, not current behavior.

Do not merge these categories.

All evidence should include source references such as:
- repository path
- command used
- table/query
- timestamp
- environment name
- screenshot filename
- hash/checksum where appropriate


# Step 0.5 Prompt — Capture Numerical, Functional, and Visual Baseline Outputs

## Goal

Capture what the current application produces for the baseline cases selected in Step 0.4 so that future refactoring can be compared against real current behavior.

Screenshots alone are insufficient. Capture both:
- visual output
- numerical/data output

## Inputs

Use:
- `06-baseline-test-data.md`
- current deployed application
- current repository baseline
- safe read-only database queries

## Output Categories

### A. Authentication/UI
Capture:
- login screen
- dashboard by representative role
- menu visibility by role
- user-management screen
- password-related current UI

Do not include actual passwords.

### B. Academic selection
Capture:
- academic year
- section
- standard
- division
- student lists where safe

Redact unnecessary PII.

### C. Subject configuration
Capture:
- Subject Master list
- Standard Wise Subject allocation for selected standards
- IDs from DB/query in accompanying evidence

### D. Teacher allocation
Capture:
- selected class allocation
- selected subject allocation
- allocation row counts and IDs

### E. Marks
For each baseline marks case capture:
- displayed students
- subject
- exam
- component maxima
- existing marks
- absent state
- lock/submission state

Record numerical DB evidence for the same case.

### F. Completion
Capture:
- teacher marks status
- completion state
- expected versus completed allocations where applicable

### G. Result generation
For selected cases record:
- component totals
- subject total
- subject pass/fail
- aggregate total
- percentage
- grade
- overall result
- rank
- result/result-detail row IDs/counts

Do not regenerate real results merely to capture evidence if a generated result already exists.

### H. Result sheet
Capture visual and numerical output.

### I. Result register
Capture visual and numerical output.

### J. Report card
Capture visual and numerical output.

### K. Analytics
Capture a small set of current analytics numbers and explicitly note any known rule difference from official result generation.

## Screenshot Naming Convention

Use stable names such as:

```text
AUTH-001-login.png
CTX-001-academic-selection.png
SUBJ-001-standard-wise-subjects.png
ALLOC-001-teacher-allocation.png
MARK-001-entry.png
MARK-001-db-evidence.txt
RESULT-001-sheet.png
RESULT-001-values.json
REPORT-001-report-card.png
```

## Numerical Evidence Format

For each case create structured evidence such as:

```json
{
  "case_id": "RESULT-001",
  "captured_at": "ISO-8601 timestamp",
  "erp_student_id": 48192,
  "exam_master_id": 5,
  "expected": {
    "total": 412,
    "percentage": 82.4,
    "grade": "A",
    "result": "PASS",
    "rank": 3
  }
}
```

Use aliases/redaction where PII is unnecessary.

## Checksums

Calculate checksums for captured evidence files where practical.

This protects the baseline from accidental later replacement.

## Baseline Comparison Principle

Future phases must be able to say:

```text
Case RESULT-001

Before refactor:
Total       412
Percentage  82.40
Grade       A
Result      PASS
Rank        3

After refactor:
Total       412
Percentage  82.40
Grade       A
Result      PASS
Rank        3
```

If future behavior intentionally changes due to correction of a known bug, that change must be documented as an approved behavioral change rather than silently treated as regression success.

## Deliverable

Create:
- `07-baseline-output-catalog.md`
- `evidence/screenshots/`
- `evidence/counts/`
- `evidence/reports/`
- `evidence/checksums/`

The catalog should map every baseline case to its evidence files.

## Acceptance Criteria

- [ ] Critical user journeys have visual evidence.
- [ ] Critical business outputs have numerical evidence.
- [ ] Subject/Standard Wise Subject behavior is represented.
- [ ] Marks boundaries are represented where safely available.
- [ ] Generated result totals/percentages/grades/ranks are captured.
- [ ] Report outputs are captured.
- [ ] Evidence is mapped to stable case IDs.
- [ ] Sensitive PII is redacted/minimized.
- [ ] No passwords/tokens/secrets captured.
- [ ] Production data was not changed merely to create evidence.
- [ ] Evidence checksums/catalog exist.

## Stop Conditions

Do not proceed with a capture action if it would:
- alter real marks,
- unlock/lock a real assessment,
- regenerate a published result,
- expose unnecessary student/family PII,
- require a password/token to be captured,
- mutate MSSQL.

## Current-Main Step 0.5 Addendum

Capture SELECT-only evidence linking, where applicable:

```text
subjects.id
standard_wise_subjects.id
standard_wise_subjects.subject_id
exam_master_subjects.subject_id
teacher_subject_allocations.subject_id
teacher_marks_status.subject_id
student_marks.subject_id
student_result_details.subject_id
```

Also capture:
- dashboard subject display for one pending teacher entry,
- Subject Master / Standard Wise Mapping UI,
- Add Subject informational behavior,
- `php artisan route:list` output,
- login/change/reset route inventory,
- no-public-registration evidence.

Do **not** invoke simply for evidence:
- result generation,
- marks update/submit,
- admin reopen/correction,
- ERP sync,
- password change/reset,
- Subject create/update/delete.

Every evidence artifact must identify:
- deployed baseline SHA/manifest,
- database snapshot timestamp,
- GitHub main SHA.

## LOCAL-ONLY EXECUTION CORRECTION

The production-testing deployment is on a separate system that is not connected to this environment.

Therefore Phase 0 must NOT attempt to discover the deployed application folder, hash deployed files, compare local files directly with that server, start/stop the deployed service, or run commands on it.

For UI inspection, start the repository locally from VS Code using `LOCAL_VSCODE_RUN_GUIDE.md`.

`http://127.0.0.1:8000` is the local `php artisan serve` development server, not the remote deployment.

Use `db/school_management.sql` as the current MariaDB baseline. Keep `db/school_management_before_final_subject_cleanup.sql` only as a historical/pre-cleanup reference.

If MSSQL is not reachable from this machine, mark MSSQL-dependent flows as blocked/not locally validated rather than inventing behavior.

## Local Output Provenance

Every screenshot/output captured here must record:

```text
Source: Local VS Code Phase 0 instance
Git SHA:
MariaDB source dump: db/school_management.sql
MSSQL reachable: YES / NO
Laravel URL: http://127.0.0.1:8000
```

Do not label local screenshots as production/deployed screenshots.
