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


# Step 0.1 Prompt — Freeze the Current Working State

## Goal

Identify and preserve an immutable reference to the exact version of the application currently considered the working production-testing baseline.

This step is about **Git/source provenance and runtime identity**. It must not change application functionality.

## Tasks

### A. Establish repository identity

Record:

```text
Repository
Default branch
Current checked-out branch
HEAD SHA
HEAD commit date
HEAD commit message
Remote origin
Git status
Untracked files
Modified tracked files
```

Recommended safe commands include:

```bash
git remote -v
git branch --show-current
git rev-parse HEAD
git show -s --format='%H%n%ci%n%s' HEAD
git status --short
git status --porcelain=v1 --untracked-files=all
```

Do not run destructive Git commands.

### B. Determine whether deployed source equals repository HEAD

If the deployed local server is accessible:
1. Identify deployed application root.
2. Determine whether it is a Git working tree.
3. Capture its current commit if available.
4. Compare important source/config manifests with repo baseline.
5. Do not overwrite the deployed copy.

If the deployed folder is not a Git working tree:
- create a read-only file manifest,
- calculate hashes of application files where practical,
- exclude runtime-generated directories when appropriate (`storage/logs`, cache files, etc.),
- document the comparison method.

### C. Capture dependency baselines

Record checksums for:

```text
composer.json
composer.lock
package.json
package-lock.json
vite.config.*
config/database.php
bootstrap/app.php
routes/web.php
routes/auth.php
```

Use SHA-256 where available.

Example:

```bash
sha256sum composer.lock package-lock.json
```

On Windows use an equivalent such as:

```powershell
Get-FileHash .\composer.lock -Algorithm SHA256
```

### D. Capture runtime identity

Where safe, record:

```text
php -v
php artisan --version
composer --version
node --version
npm --version
php -m
```

Also identify:
- operating system
- web server
- PHP SAPI
- relevant SQL Server PHP extensions/drivers
- MariaDB/MySQL version
- MSSQL version, read-only query only
- timezone

Do not expose passwords, APP_KEY, tokens, usernames requiring secrecy, or connection strings containing credentials.

### E. Create a baseline tag only if explicitly authorized

Preferred tag name:

```text
v0-production-testing-baseline
```

or a timestamped equivalent:

```text
baseline-2026-08-17
```

Before creating/pushing a tag:
- show the SHA it will point to,
- confirm the working application corresponds to that SHA,
- explain that creating a Git tag changes repository metadata,
- obtain operator authorization if not already granted.

Do not push a tag automatically merely because the prompt mentions it.

If tag creation is not authorized, document the exact baseline SHA instead.

## Deliverable

Create `01-git-baseline.md` containing:

### Repository baseline
- repo
- branch
- SHA
- commit
- status
- tag status

### Deployed baseline
- deployed path
- commit or manifest identity
- matches repo: YES / NO / UNKNOWN
- differences

### Dependency fingerprint
A table of important files and SHA-256 hashes.

### Runtime fingerprint
Version table.

### Evidence
Commands and timestamps.

## Acceptance Criteria

- [ ] Exact Git baseline SHA recorded.
- [ ] Dirty/untracked state recorded.
- [ ] Deployed version identity recorded or explicitly unavailable.
- [ ] Dependency fingerprints captured.
- [ ] Runtime versions captured.
- [ ] No secrets written to documentation.
- [ ] No code modified.
- [ ] No Git reset/checkout/rebase/force operation performed.
- [ ] Tag either safely created with authorization or intentionally not created.
- [ ] Any repo/deployment mismatch is explicitly documented.

## Stop Conditions

Stop and report rather than changing anything if:
- deployed source does not match repository and the reason is unknown,
- Git working tree contains unexplained production-only changes,
- secrets appear to be committed,
- deployed files would need to be overwritten to compare them,
- the environment cannot be identified safely.

## Current-Main Step 0.1 Addendum

### Re-query HEAD first

Observed during reassessment:
`1780866c61c0b7cb0e7a9652735ccdc312d671ad`

Re-query at execution time.

Capture:
```bash
git log --oneline --decorate -20
git log --name-status -10
git status --porcelain=v1 --untracked-files=all
git rev-parse HEAD
```

### Deployment-vs-Git gate

Record:
```text
GitHub main HEAD:
Local development HEAD:
Deployed production-testing HEAD/manifest:
Match? YES / NO / UNKNOWN
```

If the deployed folder is not a Git worktree, create a read-only hash manifest.

Do not pull/reset/checkout the deployed application just to make it match GitHub.

If deployed code and GitHub differ, preserve both identities:
- `SOURCE-HEAD`
- `DEPLOYED-BASELINE`

Do not create a final baseline tag until the deployed working identity is known.

### Additional files to fingerprint

Hash the current versions of:
- SubjectController
- ExamMasterController
- TeacherBulkAllocationController
- MarkEntry/Save/Submit/Edit controllers
- AdminMarksController
- ResultGenerationController
- ResultSheetController
- TeacherSubjectAllocation model
- TeacherMarksStatus model
- StudentHelper
- LoginRequest
- UserController
- PasswordController
- NewPasswordController
- routes/web.php
- routes/auth.php
- config/database.php
- both committed SQL dumps

## LOCAL-ONLY EXECUTION CORRECTION

The production-testing deployment is on a separate system that is not connected to this environment.

Therefore Phase 0 must NOT attempt to discover the deployed application folder, hash deployed files, compare local files directly with that server, start/stop the deployed service, or run commands on it.

For UI inspection, start the repository locally from VS Code using `LOCAL_VSCODE_RUN_GUIDE.md`.

`http://127.0.0.1:8000` is the local `php artisan serve` development server, not the remote deployment.

Use `db/school_management.sql` as the current MariaDB baseline. Keep `db/school_management_before_final_subject_cleanup.sql` only as a historical/pre-cleanup reference.

If MSSQL is not reachable from this machine, mark MSSQL-dependent flows as blocked/not locally validated rather than inventing behavior.

## Revised Step 0.1 Source Baseline for This Environment

Freeze only what is accessible here:

```text
GitHub/current repository HEAD
local working-tree status
runtime/dependency versions
correct SQL dump identity
local Phase 0 database import identity
```

Use VS Code PowerShell:

```powershell
git rev-parse HEAD
git status --porcelain=v1 --untracked-files=all
git log --oneline --decorate -20
php -v
composer --version
node -v
npm -v
mariadb --version
php -m
```

Record the remote deployed testing system as `NOT CONNECTED / NOT INSPECTABLE FROM THIS ENVIRONMENT`. This is an environment boundary, not a Phase 0 failure.
