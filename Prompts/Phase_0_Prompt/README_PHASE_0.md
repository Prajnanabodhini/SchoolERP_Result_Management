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


# Phase 0 Prompt Pack Index

This pack is designed to be executed **step-by-step** by GitHub Copilot, Codex, another coding agent, or a developer/operator.

## Execution order

1. `00_PHASE_0_MASTER.md`
2. `00_01_FREEZE_CURRENT_WORKING_STATE.md`
3. `00_02_BACKUP_APPLICATION_AND_DATABASES.md`
4. `00_03_DOCUMENT_CURRENT_HAPPY_PATHS.md`
5. `00_04_CREATE_BASELINE_TEST_DATA_SET.md`
6. `00_05_CAPTURE_BASELINE_OUTPUTS.md`
7. Update `PHASE_0_PROGRESS_LOG.md` after every step.
8. Complete `PHASE_0_ACCEPTANCE_CHECKLIST.md` before Phase 0 is declared complete.

## Important

Do **not** start Phase 1 merely because the documentation files exist. Phase 0 is complete only when the evidence and acceptance criteria have been verified.

If live server/database access is unavailable to the coding agent, the agent must:
- complete all repository-side work it can safely perform,
- generate an operator checklist for the unavailable live-environment actions,
- mark those actions as pending,
- never invent the missing evidence.

## 2026-08-17 Current-Main Reassessment

Read `CURRENT_MAIN_REASSESSMENT.md` before executing Phase 0.

Observed `main` during reassessment:
`1780866c61c0b7cb0e7a9652735ccdc312d671ad`

Do not assume this remains HEAD.

### Revised execution order

1. `CURRENT_MAIN_REASSESSMENT.md`
2. `00_PHASE_0_MASTER.md`
3. `START_HERE_PHASE_0_STEP_0_1.md`
4. `00_01_FREEZE_CURRENT_WORKING_STATE.md`
5. Review Step 0.1 evidence.
6. `00_02_BACKUP_APPLICATION_AND_DATABASES.md`
7. `00_03_DOCUMENT_CURRENT_HAPPY_PATHS.md`
8. `00_04_CREATE_BASELINE_TEST_DATA_SET.md`
9. `00_05_CAPTURE_BASELINE_OUTPUTS.md`
10. Complete the progress log and acceptance checklist.

## LOCAL-ONLY EXECUTION CORRECTION

The production-testing deployment is on a separate system that is not connected to this environment.

Therefore Phase 0 must NOT attempt to discover the deployed application folder, hash deployed files, compare local files directly with that server, start/stop the deployed service, or run commands on it.

For UI inspection, start the repository locally from VS Code using `LOCAL_VSCODE_RUN_GUIDE.md`.

`http://127.0.0.1:8000` is the local `php artisan serve` development server, not the remote deployment.

Use `db/school_management.sql` as the current MariaDB baseline. Keep `db/school_management_before_final_subject_cleanup.sql` only as a historical/pre-cleanup reference.

If MSSQL is not reachable from this machine, mark MSSQL-dependent flows as blocked/not locally validated rather than inventing behavior.
