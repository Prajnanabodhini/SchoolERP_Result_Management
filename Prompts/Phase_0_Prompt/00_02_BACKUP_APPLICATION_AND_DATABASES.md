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


# Step 0.2 Prompt — Back Up Application, Databases, and Deployment Configuration

## Goal

Establish a restorable safety net before any later architectural/security work begins.

A backup is not considered trustworthy merely because a file exists. Capture evidence that it is readable and structurally valid.

## Scope

Back up, as permitted:

1. Source/repository baseline.
2. Deployed application folder.
3. Runtime environment configuration.
4. MariaDB Result Management database.
5. Authoritative MSSQL School ERP database — **or confirm/record the school's existing backup process if the Result project is not authorized to create MSSQL backups**.
6. IIS/Apache/Nginx configuration relevant to this application.
7. Scheduled tasks/services relevant to this application, if any.
8. Current `.env` securely.

## Critical Safety Rules

### MSSQL
The Result application itself is conceptually read-only against MSSQL. Do not use application-level access to alter MSSQL.

If database-level backup privileges are unavailable:
- do not try to escalate privileges,
- document the existing DBA backup mechanism,
- request a verified backup reference from the DBA/operator.

### MariaDB
Use backup tooling that performs a consistent dump.

Do not:
- drop tables,
- restore over production,
- run migrations,
- modify data.

### Secrets
Do not commit `.env` or database backups into Git.

Store backups in an operator-approved secure location.

## Tasks

### A. Create a backup manifest

Assign a unique backup set identifier, for example:

```text
SchoolERP-Phase0-2026-08-17
```

For each artifact capture:
- source
- timestamp
- machine
- path/location
- size
- checksum
- tool/command
- success/failure
- restore-validation status
- retention owner

### B. Source/repository backup

Options may include:
- Git remote plus immutable baseline SHA/tag,
- `git bundle`,
- read-only archive of the baseline commit.

Example non-destructive archive concept:

```bash
git bundle create SchoolERP-baseline.bundle --all
```

Only do this if appropriate to the operator environment.

### C. Deployed application backup

Archive the actual deployed folder, preserving:
- application code
- public assets
- relevant writable content required for restore

Avoid copying transient logs/caches if they are huge unless needed for incident evidence.

Record deployment-path permissions/ACLs separately where practical.

### D. `.env` and secret configuration backup

Back up securely **outside Git**.

Document only:
- that it was backed up,
- storage location identifier,
- checksum,
- owner.

Do not put secret contents in the markdown report.

### E. MariaDB backup

Use the site's approved database backup tooling.

Capture:
- server/database name (redacted if needed)
- server version
- dump format
- row/table count summary where possible
- checksum

Validation should include at least:
- backup file exists,
- non-zero size,
- archive/dump can be opened/read,
- expected table definitions are present.

Best practice if a safe isolated restore target exists:
- restore to a temporary/test database,
- confirm schema/table count and representative row counts,
- then remove only the temporary test restore after authorization.

Never test restore by overwriting production.

### F. MSSQL backup

Preferred approach:
- obtain/confirm a DBA-managed full backup or equivalent verified snapshot.

Capture:
- backup ID/path
- date/time
- database name
- backup method
- last successful verification/restore test if available

If only SELECT access is available:
- do **not** attempt to grant yourself backup permissions,
- document `BACKUP NOT PERFORMED BY RESULT APP — DBA BACKUP REQUIRED/CONFIRMED`.

### G. Web server configuration

Capture read-only exports/copies of relevant:
- IIS site/app-pool bindings and settings
- Apache/Nginx vhost if applicable
- PHP handler/version
- rewrite rules
- TLS binding/certificate metadata (not private key)
- application physical path

### H. Scheduled/runtime services

If used, document:
- Windows Task Scheduler jobs
- queue workers
- Laravel scheduler
- services
- cron
- startup scripts

Do not alter them.

## Deliverable

Create `03-backup-manifest.md`.

Recommended table:

| Artifact | Timestamp | Location | Size | SHA-256 | Tool | Validation | Owner |
|---|---|---|---:|---|---|---|---|

Also record:

### Restore readiness
- Source: VERIFIED / PARTIAL / PENDING
- Deployed app: VERIFIED / PARTIAL / PENDING
- MariaDB: VERIFIED / PARTIAL / PENDING
- MSSQL: VERIFIED / DBA-CONFIRMED / PENDING
- Environment config: VERIFIED / PARTIAL / PENDING
- Web server config: VERIFIED / PARTIAL / PENDING

## Acceptance Criteria

- [ ] Baseline source is recoverable.
- [ ] Deployed application copy is recoverable.
- [ ] `.env`/secret config is securely backed up outside Git.
- [ ] MariaDB backup exists and was at least structurally validated.
- [ ] MSSQL backup responsibility/status is known and documented.
- [ ] Web server deployment configuration is captured.
- [ ] Checksums are recorded.
- [ ] No backup containing secrets or student data was committed to Git.
- [ ] No restore was performed over production.
- [ ] No production data was changed.
- [ ] Backup owner and retention location are known.

## Stop Conditions

Stop and escalate if:
- no recoverable MariaDB backup can be created,
- no authoritative MSSQL backup process exists,
- backups are being written inside a web-accessible directory,
- backup destination lacks adequate storage/security,
- the only possible validation would overwrite production.

## Current-Main Step 0.2 Addendum

Treat MSSQL in business documentation as the **authoritative School ERP MSSQL database**. The current code connection name `sqlsrv_olderp` is only an implementation detail.

The MariaDB backup must preserve all transition-state data exactly as it exists, including:
- canonical Subject IDs,
- Standard Wise Subject mappings,
- historical subject-ID formats,
- local academic mapping tables,
- marks/status/results/audits.

Do not normalize anything before backup.

Pair each backup with:
- source SHA/manifest,
- deployed source identity,
- database snapshot timestamp.

## LOCAL-ONLY EXECUTION CORRECTION

The production-testing deployment is on a separate system that is not connected to this environment.

Therefore Phase 0 must NOT attempt to discover the deployed application folder, hash deployed files, compare local files directly with that server, start/stop the deployed service, or run commands on it.

For UI inspection, start the repository locally from VS Code using `LOCAL_VSCODE_RUN_GUIDE.md`.

`http://127.0.0.1:8000` is the local `php artisan serve` development server, not the remote deployment.

Use `db/school_management.sql` as the current MariaDB baseline. Keep `db/school_management_before_final_subject_cleanup.sql` only as a historical/pre-cleanup reference.

If MSSQL is not reachable from this machine, mark MSSQL-dependent flows as blocked/not locally validated rather than inventing behavior.

## SQL Snapshot Selection

Use `db/school_management.sql` for local reconstruction. It is the later post-cleanup dump and was updated again by the later Subject mapping commit.

Keep `db/school_management_before_final_subject_cleanup.sql` only as historical comparison/recovery evidence.

The inaccessible remote testing system cannot be backed up from this environment; record that limitation instead of attempting remote access.
