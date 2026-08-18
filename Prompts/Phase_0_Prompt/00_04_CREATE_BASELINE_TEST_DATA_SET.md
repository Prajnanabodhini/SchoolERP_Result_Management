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


# Step 0.4 Prompt — Establish a Representative Baseline Regression Data Set

## Goal

Identify a small, representative, repeatable set of existing records that can be used to compare the current system against future refactored versions.

The baseline must be sufficient to validate business logic without copying the whole production environment.

## Important

This step primarily **selects and documents** test contexts. It does not mean production data should be exported broadly or placed in Git.

Student personal information must be minimized.

Where production data is used for reference:
- store IDs rather than unnecessary PII,
- redact names/contact data from documentation,
- use secure evidence storage,
- later create synthetic/anonymized equivalents for automated tests.

## Selection Strategy

Choose at least the following representative contexts where available.

### Academic contexts
Select:
- at least 2 academic years if historical data permits,
- at least 2 sections if relevant,
- multiple standards,
- at least 2 divisions.

### Subject scenarios
Include:
- a Subject Master subject used by multiple standards,
- multiple Standard Wise Subject mappings,
- theory-only subject,
- theory + oral if used,
- theory + practical if used,
- theory + oral + practical if used,
- skill/co-scholastic/optional subject if part of active current workflow.

### Teacher allocation scenarios
Include:
- one teacher with one class/subject,
- one teacher with multiple subject allocations,
- one class with multiple teachers/subjects.

### Marks scenarios
Find or safely construct in an isolated test environment:
- zero marks
- exact pass mark
- one below pass
- high/maximum marks
- absent
- incomplete/draft
- submitted/locked
- multiple components

### Result scenarios
Include:
- all-pass student
- one-subject-fail student
- multiple-subject-fail student
- absent case
- tied percentage/rank case if available
- boundary grade percentage case
- exact passing boundary

## Data Dictionary

For every selected record, document identifiers with clear namespaces.

Example:

```text
ERP Academic Year ID
ERP Section ID
ERP Standard ID
ERP Division ID
ERP Student ID

Local Subject ID
Local Standard Wise Subject ID
Local Exam Master ID
Local Teacher Allocation ID
Local Student Mark ID
Local Student Result ID
```

Never use a label such as `subject_id` in the baseline document without clarifying which entity it refers to.

## Queries

Use SELECT-only queries.

For each baseline case:
- record query,
- record timestamp,
- record row count,
- record key result values.

Do not run updates to “make a test case” on production.

If a required edge case is not present in production:
- mark it `NOT PRESENT IN BASELINE`,
- define a synthetic fixture for future test environment,
- do not manufacture it in production.

## Anonymization

For documentation, prefer:

```text
ERP Student ID: 48192
Student Alias: BASELINE-STUDENT-A
```

rather than storing real names.

For family/contact/fee details:
- do not capture unless directly necessary for a tested report,
- redact sensitive values.

## Required Baseline Case Catalog

Create a stable case ID scheme such as:

```text
CTX-001
SUBJ-001
ALLOC-001
MARK-001
MARK-002
RESULT-001
RESULT-002
REPORT-001
AUTH-001
```

For each case:

```text
Case ID
Purpose
Preconditions
Source IDs
Local IDs
Expected inputs
Expected outputs
Current status
Evidence query/file
PII classification
Can automate later? YES/NO
```

## Deliverable

Create `06-baseline-test-data.md`.

Also create a machine-readable companion if useful:

```text
baseline-cases.json
```

or:

```text
baseline-cases.yaml
```

Do not include secrets or unnecessary PII.

## Acceptance Criteria

- [ ] Representative academic contexts selected.
- [ ] Subject Master and Standard Wise Subject examples included.
- [ ] Teacher allocation examples included.
- [ ] Marks boundaries included or explicitly marked for synthetic future fixtures.
- [ ] Result pass/fail/rank/grade scenarios included.
- [ ] IDs are namespace-explicit.
- [ ] Production data was not modified.
- [ ] MSSQL was only read.
- [ ] Sensitive student data was minimized/redacted.
- [ ] Baseline cases are stable enough to reuse during refactoring.

## Stop Conditions

Stop if:
- obtaining a test case requires changing production marks/results,
- sensitive personal data would need to be copied into Git,
- ID semantics cannot be confidently determined,
- an edge case cannot be proven from existing data.

Record the gap instead.

## Current-Main Step 0.4 Addendum

Add these baseline cases where they already exist:

### `SUBJ-CANON-001`
Current/new canonical Subject ID flow.

### `SUBJ-LEGACY-001`
Existing historical mapping-ID row, if any.
Do not create one.

### `SUBJ-REUSE-001`
One Subject Master mapped to multiple Standards, if currently present.
If absent, record `NOT PRESENT IN CURRENT BASELINE`.

### `SUBJ-CREATE-001`
Current Add Subject behavior (master + mapping together).
Use existing/static evidence; do not create a production subject for the test.

### `DASH-SUBJ-001`
A pending teacher entry with:
- TSA subject ID
- Subject Master ID/name
- Standard Wise mapping ID
- dashboard-displayed subject

### Authentication cases
Prefer static evidence.
Only in an isolated test environment define:
- `AUTH-CREATE-001`
- `AUTH-LOGIN-001`
- `AUTH-CHANGE-PWD-001`
- `AUTH-RESET-PWD-001`

### Route cases
- `ROUTE-PUBLIC-001`
- `ROUTE-STALE-001`
- `ROUTE-MUTATING-GET-001`
- `ROUTE-ADMIN-GUARD-001`

Do not invoke state-changing GET routes.

### `ERP-MAP-001`
One anonymized local-to-MSSQL mapping path with IDs and returned student count.

## LOCAL-ONLY EXECUTION CORRECTION

The production-testing deployment is on a separate system that is not connected to this environment.

Therefore Phase 0 must NOT attempt to discover the deployed application folder, hash deployed files, compare local files directly with that server, start/stop the deployed service, or run commands on it.

For UI inspection, start the repository locally from VS Code using `LOCAL_VSCODE_RUN_GUIDE.md`.

`http://127.0.0.1:8000` is the local `php artisan serve` development server, not the remote deployment.

Use `db/school_management.sql` as the current MariaDB baseline. Keep `db/school_management_before_final_subject_cleanup.sql` only as a historical/pre-cleanup reference.

If MSSQL is not reachable from this machine, mark MSSQL-dependent flows as blocked/not locally validated rather than inventing behavior.
