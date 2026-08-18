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


# Master Prompt — Execute Phase 0

## Role

Act as a senior Laravel architect, database engineer, QA lead, security-conscious release engineer, and production change-control reviewer.

Your job is to create a trustworthy baseline of the currently working SchoolERP Result Management System **without changing application behavior**.

## Objective

At the end of Phase 0 we must be able to answer, with evidence:

1. Exactly which Git commit represents the current working baseline?
2. What repository state was deployed for user testing?
3. What versions/configuration define the current runtime?
4. Can the current application and both database environments be restored?
5. What are the current end-to-end working user journeys?
6. Which data/context will be used for regression testing?
7. What are the current numerical and visual outputs for those journeys?
8. What known limitations are deliberately left untouched for later phases?
9. Can a future refactor be compared objectively against the current application?

## Mandatory Substeps

Execute these in order:

### Step 0.1 — Freeze Current Working State
Use `00_01_FREEZE_CURRENT_WORKING_STATE.md`.

### Step 0.2 — Back Up Application and Databases
Use `00_02_BACKUP_APPLICATION_AND_DATABASES.md`.

### Step 0.3 — Document Current Happy Paths
Use `00_03_DOCUMENT_CURRENT_HAPPY_PATHS.md`.

### Step 0.4 — Establish Baseline Test Data
Use `00_04_CREATE_BASELINE_TEST_DATA_SET.md`.

### Step 0.5 — Capture Current Outputs
Use `00_05_CAPTURE_BASELINE_OUTPUTS.md`.

## Required Phase 0 Deliverables

Create a `docs/phase-0-baseline/` documentation package **or an equivalent non-runtime documentation location approved by the operator** containing:

```text
phase-0-baseline/
├── 01-git-baseline.md
├── 02-runtime-baseline.md
├── 03-backup-manifest.md
├── 04-current-data-source-map.md
├── 05-current-happy-paths.md
├── 06-baseline-test-data.md
├── 07-baseline-output-catalog.md
├── 08-known-current-issues.md
├── 09-phase-0-open-items.md
└── evidence/
    ├── route-list/
    ├── db-schema/
    ├── counts/
    ├── screenshots/
    ├── reports/
    └── checksums/
```

If repository modification is not authorized, create the exact same structure outside the repository and provide it as an artifact.

## Baseline Evidence to Capture

At minimum:

### Git / source
- repository URL
- current branch
- HEAD commit SHA
- HEAD commit date/message
- clean/dirty status
- untracked files
- current remote(s)
- PHP dependency lock hash
- NPM dependency lock hash
- relevant config file hashes
- deployed-code hash or manifest if accessible

### Runtime
- PHP version
- Laravel version
- Composer version
- Node/npm versions used for build, if applicable
- OS
- web server (IIS/Apache/Nginx)
- enabled PHP extensions relevant to Laravel/MSSQL
- SQL Server driver information
- MariaDB/MySQL version
- MSSQL version if safely retrievable
- timezone
- `APP_ENV`
- `APP_DEBUG` state
- queue/session/cache drivers
- database connection names
- no secrets in documentation

### Current database/source ownership
Document what current code actually reads from:
- MSSQL live
- MariaDB local
- local mirrored/imported tables
- other sources

This is a **current-state map**, not the target architecture.

### User journeys
Capture at least:
- admin login
- user management
- role/permission management
- year/section selection
- teacher/class allocation
- teacher/subject allocation
- marks entry
- draft/save behavior
- submit/lock behavior
- administrator mark correction
- exam completion
- result generation
- result sheet
- result register
- report card
- logout/session behavior

### Baseline output
Capture representative values, not only screenshots:
- number of students
- subject count
- expected marks rows
- obtained marks examples
- absent examples
- totals
- pass/fail
- percentages
- grades
- ranks
- generated result row counts

## Phase 0 Completion Rule

Phase 0 is complete only when:

- all five step prompts have been executed,
- evidence exists for each claimed fact,
- backups are verified or explicitly marked pending,
- regression data is identified,
- current outputs are captured,
- no runtime code was modified,
- no production data was changed,
- open items are documented,
- the Phase 0 acceptance checklist is fully reviewed.

## Required Final Report

Return:

1. Phase 0 status: `COMPLETE`, `COMPLETE WITH OPEN ITEMS`, or `BLOCKED`.
2. Git baseline SHA/tag.
3. Backup status for:
   - source
   - deployed application
   - MariaDB
   - MSSQL
   - environment/server configuration
4. Happy paths successfully characterized.
5. Test data contexts captured.
6. Output evidence captured.
7. Any observed divergence between repo and deployed instance.
8. Any safety concern discovered.
9. Explicit confirmation that no application behavior was changed.

## Current-Main Addendum

In addition to the original Phase 0 outputs, create:

- `00-recent-commit-delta.md`
- `subject-id-contract-matrix.md`
- `route-exposure-matrix.md`
- `authentication-path-contract.md`
- `current-local-to-mssql-mapping.md`

### Subject-ID contract matrix

At minimum classify:

| Table / Model | Column | Current new-write meaning | Historical alternate | Evidence |
|---|---|---|---|---|
| `subjects` | `id` | canonical Subject Master | N/A | |
| `standard_wise_subjects` | `id` | mapping ID | N/A | |
| `standard_wise_subjects` | `subject_id` | `subjects.id` | N/A | |
| `exam_master_subjects` | `subject_id` | `subjects.id` | possible mapping ID | |
| `teacher_subject_allocations` | `subject_id` | `subjects.id` | possible mapping ID | |
| `teacher_marks_status` | `subject_id` | `subjects.id` | possible mapping ID | |
| `student_marks` | `subject_id` | `subjects.id` | investigate | |
| `student_result_details` | `subject_id` | `subjects.id` | investigate | |

Do not update/normalize any row.

### Current-vs-target Subject workflow

Document:

**Frozen target:** reusable Subject Master + Standard Wise mapping.

**Current:** Add Subject creates a new Subject Master row and one mapping together.

### Authentication contract

Characterize separately:
- Admin Create/Update User
- Login
- Change Password
- Forgot/Reset Password
- public registration route presence/absence
- active/inactive user behavior

No production password changes.

### Route inventory

Classify public/authenticated/admin-intended/state-changing/stale routes.

Do not invoke state-changing routes for baseline evidence.

### SQL dump fingerprints

Hash:
- `db/school_management.sql`
- `db/school_management_before_final_subject_cleanup.sql`

Do not assume dump == live database.

## LOCAL-ONLY EXECUTION CORRECTION

The production-testing deployment is on a separate system that is not connected to this environment.

Therefore Phase 0 must NOT attempt to discover the deployed application folder, hash deployed files, compare local files directly with that server, start/stop the deployed service, or run commands on it.

For UI inspection, start the repository locally from VS Code using `LOCAL_VSCODE_RUN_GUIDE.md`.

`http://127.0.0.1:8000` is the local `php artisan serve` development server, not the remote deployment.

Use `db/school_management.sql` as the current MariaDB baseline. Keep `db/school_management_before_final_subject_cleanup.sql` only as a historical/pre-cleanup reference.

If MSSQL is not reachable from this machine, mark MSSQL-dependent flows as blocked/not locally validated rather than inventing behavior.
