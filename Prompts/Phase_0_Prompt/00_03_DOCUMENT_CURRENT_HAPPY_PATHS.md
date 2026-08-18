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


# Step 0.3 Prompt — Document the Current End-to-End Working Flows

## Goal

Create a factual characterization of how the deployed/current application behaves **today**, before redesign.

Do not describe the future architecture as if it already exists.

## Method

For each workflow:
1. Identify entry route/page.
2. Identify user role required in actual current behavior.
3. Record page/controller/routes involved.
4. Record important database reads/writes.
5. Record current session/context keys.
6. Record validation/authorization observed.
7. Record output/result.
8. Record known issue without fixing it.
9. Capture screenshot/reference if live UI is available.

Where current behavior differs from intended behavior, document both:

```text
CURRENT OBSERVED BEHAVIOR:
...

INTENDED/FROZEN FUTURE BEHAVIOR:
...
```

Do not silently reconcile them.

## Workflows to Characterize

### 1. Authentication
- login
- login identifier
- password handling behavior
- inactive user behavior
- logout
- session regeneration if observed
- public registration route presence

### 2. User administration
- create user
- edit user
- activate/deactivate
- role assignment
- permission assignment

### 3. Academic context
- academic year selection
- section selection
- standard/division/student sourcing
- identify MSSQL versus local table source for each current screen

### 4. Subject management
Document separately:
- Subject Master — global subject list
- Standard Wise Subject Master — allocation of a Subject Master subject to a specific standard/class

Identify current DB tables and IDs used.

### 5. Exam configuration
- exam master
- subject/component configuration
- theory/oral/practical maxima/pass marks
- any exam-pattern path still active

### 6. Teacher allocation
- class allocation
- subject allocation
- bulk allocation
- status initialization

### 7. Marks entry
- assignment selection
- student retrieval
- existing mark retrieval
- component display
- absent handling
- draft/save

### 8. Marks submission
- validation
- lock behavior
- completion status
- transaction behavior

### 9. Alternate/administrative marks editing
Document both:
- normal teacher path
- administrator correction path
- any alternate edit route/path

Do not exploit unsafe behavior. Characterize it from code and safe tests only.

### 10. Result generation
- prerequisites
- data source
- subject interpretation
- totals
- pass/fail
- percentage
- grade
- rank
- generated tables

### 11. Result sheet
- filters/context
- data source
- live MSSQL lookups
- result source

### 12. Result register
Same characterization.

### 13. Report card
Same characterization.

### 14. Analytics
- source table
- thresholds/rules
- current differences from official result generation

### 15. Logging/audit
- mark audit
- application logs
- auth/user logs
- what is absent

## Required Current-State Architecture Diagram

Create a Mermaid or text diagram of the **current actual** flow, including:

```text
Browser
→ Laravel routes
→ controllers/helpers/models
→ MariaDB
→ MSSQL
```

Show important direct controller-to-DB dependencies currently present.

Also create a separate **frozen target boundary diagram**, clearly labelled as future state.

## Required Data Source Matrix

Create a table such as:

| Business Data | Current Source(s) | Current Code | Current Local Copy? | Future Authoritative Source |
|---|---|---|---|---|
| Academic Year | ... | ... | ... | MSSQL |
| Section | ... | ... | ... | MSSQL |
| Standard | ... | ... | ... | MSSQL |
| Division | ... | ... | ... | MSSQL |
| Student | ... | ... | ... | MSSQL |
| Subject | MariaDB | ... | N/A | MariaDB |
| Standard Wise Subject | MariaDB | ... | N/A | MariaDB |

Do not guess.

## Deliverable

Create `05-current-happy-paths.md` and `04-current-data-source-map.md`.

For each workflow include:

```text
Purpose
Actor
Preconditions
Routes
Controllers
Services/Helpers
Database Reads
Database Writes
Session State
Validation
Authorization
Expected Success Result
Current Known Weaknesses
Evidence
```

## Acceptance Criteria

- [ ] All major current workflows documented.
- [ ] Current data sources explicitly identified.
- [ ] Subject Master vs Standard Wise Subject semantics documented correctly.
- [ ] MSSQL is described as an active authoritative School ERP source, not merely “legacy,” in the future-state section.
- [ ] Current duplicated/mirrored tables are documented factually without deleting them.
- [ ] No workflow was modified.
- [ ] Unsafe paths were not exploited against production.
- [ ] Current behavior and future target are clearly separated.
- [ ] Unknowns are labelled unknown.

## Stop Conditions

Stop and document rather than experimenting if:
- a flow would require submitting/changing real production marks,
- a workflow would regenerate real published results,
- a login/security test could lock out users,
- testing requires modifying MSSQL,
- current user permissions are unclear.

## Current-Main Step 0.3 Addendum

### Mandatory new characterization

1. **Subject Master vs Standard Wise Subject**
   - frozen target meaning,
   - current controller/UI behavior,
   - current IDs written downstream.

2. **Subject-ID transition by workflow**
   Cover:
   - Exam Master
   - Teacher Bulk Allocation
   - Dashboard
   - Marks Entry
   - Marks Save
   - Marks Submit
   - Alternate Marks Edit
   - Admin Marks
   - Result Generation
   - Result Sheet

3. **Dashboard subject relationship**
   Compare current `TeacherSubjectAllocation.subject_id` write semantics with the dashboard's `standardWiseSubject` relationship.

4. **Authentication**
   Characterize:
   - Admin user create/update
   - login
   - change password
   - forgot/reset password
   - public registration
   without changing real credentials.

5. **Route exposure matrix**
   Include:
   - `/teacher-bulk-allocation/exam-details`
   - `/test-page`
   - `/erp-student-sync/{year}`
   - `/erp-sync/students/{year}`
   - `/marks-entry/edit`
   - `/marks-entry/update`
   - Administrator routes
   - stale route targets

6. **Current MSSQL translation path**
   Document local IDs/mapping tables used before querying MSSQL.

7. **Selection**
   Document MSSQL-backed year/section selection plus hard-coded Section name mapping.

8. **Result Generation**
   Record current canonical contract:
   - `student_marks.subject_id = subjects.id`
   - `student_result_details.subject_id = subjects.id`
   with separate Standard Wise Subject validation.

## LOCAL-ONLY EXECUTION CORRECTION

The production-testing deployment is on a separate system that is not connected to this environment.

Therefore Phase 0 must NOT attempt to discover the deployed application folder, hash deployed files, compare local files directly with that server, start/stop the deployed service, or run commands on it.

For UI inspection, start the repository locally from VS Code using `LOCAL_VSCODE_RUN_GUIDE.md`.

`http://127.0.0.1:8000` is the local `php artisan serve` development server, not the remote deployment.

Use `db/school_management.sql` as the current MariaDB baseline. Keep `db/school_management_before_final_subject_cleanup.sql` only as a historical/pre-cleanup reference.

If MSSQL is not reachable from this machine, mark MSSQL-dependent flows as blocked/not locally validated rather than inventing behavior.

## Local UI Observation Method

1. Follow `LOCAL_VSCODE_RUN_GUIDE.md`.
2. Import `db/school_management.sql` into an isolated local MariaDB database.
3. Start Laravel with `php artisan serve --host=127.0.0.1 --port=8000`.
4. Start Vite with `npm run dev`.
5. Open `http://127.0.0.1:8000`.
6. Observe only the local instance.
7. If MSSQL is unavailable, mark dependent flows `BLOCKED - MSSQL unavailable`.
