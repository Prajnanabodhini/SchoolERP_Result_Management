# SchoolERP — Phase 0 Acceptance Checklist

Phase 0 must not be marked complete until this checklist is reviewed.

**Preflight note (2026-08-18):** checked items below identify reusable evidence candidates from the prior run and reconciliation. They do not mean the new Phase 0 has started or that final acceptance is granted. Re-review them during the new run; the final decision remains blank.

## A. Source Baseline

- [x] Repository URL recorded.
- [x] Branch recorded.
- [x] HEAD SHA recorded.
- [x] Dirty/untracked state recorded.
- [x] Remote deployed source identity explicitly recorded as unavailable from this environment.
- [x] Repo vs deployed comparison documented as not inspectable from this environment.
- [x] Dependency lockfile hashes captured.
- [x] Optional baseline tag handled with explicit authorization (none created).

## B. Runtime Baseline

- [x] PHP version.
- [x] Laravel version.
- [x] Composer version.
- [x] Node/npm versions captured for the local VS Code runtime installation (26.7.0 / 11.19.0).
- [x] OS/local web-server method documented.
- [x] PHP MSSQL driver/extensions.
- [x] MariaDB version.
- [x] MSSQL version and read access verified.
- [x] APP_ENV documented without exposing secrets.
- [x] APP_DEBUG status documented.
- [x] Timezone documented.

## C. Backups

- [x] Source backup/recovery method verified.
- [ ] Deployed application backup verified.
- [x] `.env` securely backed up outside Git.
- [x] MariaDB backup structurally validated.
- [ ] MSSQL backup or DBA backup process confirmed.
- [ ] Web server configuration captured.
- [x] Checksums recorded.
- [x] Restore prerequisites/process documented.
- [x] No production restore was attempted.

## D. Current Architecture Characterization

- [x] Current MSSQL reads documented and verified.
- [x] Current MariaDB reads/writes documented.
- [x] Current local mirrored/imported academic/student tables documented.
- [x] Subject Master purpose documented correctly.
- [x] Standard Wise Subject Master purpose documented correctly.
- [x] Current ID semantics documented.
- [x] Current session conventions documented.
- [x] Current authorization behavior documented.
- [x] Current logging/audit behavior documented.

## E. Functional Flows

- [x] Login (static contract and operator-confirmed local function; visual recapture pending).
- [x] User management (static contract).
- [x] Roles/permissions (static contract).
- [x] Academic context selection (static contract; MSSQL tables reachable).
- [x] Subject management (static contract).
- [x] Exam configuration (static contract).
- [x] Teacher allocation (static contract).
- [x] Marks draft/save (static contract).
- [x] Marks submit/lock (static contract).
- [x] Admin marks correction (static contract).
- [x] Completion status (static/source evidence).
- [x] Result generation (static contract; not invoked).
- [x] Result sheet (static contract).
- [x] Result register (static contract).
- [x] Report card (static contract).
- [x] Analytics (static contract).
- [x] Logout/session (static contract).

## F. Baseline Test Cases

- [ ] Academic contexts.
- [x] Standard Wise Subject examples (baseline source).
- [x] Teacher allocation examples (baseline source).
- [x] Exact pass boundary (baseline-source count; case selection pending).
- [x] One-below-pass boundary (baseline-source count; case selection pending).
- [x] Zero marks (baseline-source count; case selection pending).
- [x] High/max marks (baseline-source count; case selection pending).
- [x] Absent (baseline-source count; case selection pending).
- [ ] Draft/incomplete.
- [x] Locked/submitted (baseline-source count; case selection pending).
- [x] All-pass result (baseline-source count; case selection pending).
- [x] Failure result (baseline-source count; case selection pending).
- [ ] Rank/tie case if available.
- [ ] Grade boundary case if available.
- [x] Missing baseline edge cases defined as future synthetic fixtures.

## G. Baseline Evidence

- [x] Existing screenshot catalogued and superseded; valid local screenshots pending.
- [x] Numerical evidence catalogued.
- [x] MariaDB source/live counts and live MSSQL counts captured.
- [x] Marks boundary/absent counts captured; row-level local cases pending.
- [x] Result status/detail counts captured; rendered outputs pending.
- [ ] Report outputs captured.
- [x] Checksums created.
- [x] PII minimized/redacted.
- [x] No credentials/secrets captured.

## H. Safety

- [x] No application code modified.
- [x] No production schema changed.
- [x] No production data changed for testing.
- [x] MSSQL remained read-only.
- [x] No public-user disruption caused.
- [x] No user passwords reset.
- [x] No result regenerated solely for baseline evidence.
- [x] No obsolete files deleted.

## Final Decision

**Phase 0 status:**  
- [ ] COMPLETE
- [ ] COMPLETE WITH OPEN ITEMS
- [x] BLOCKED

**Approved by:**  
**Date:**  
**Open items:** Step 0.2 stop condition is active: the authoritative MSSQL backup process/owner/reference is unknown. The disconnected remote operator must also supply deployed-folder and web-server backup evidence. Steps 0.3–0.5 have not started. See `docs/phase-0-baseline/03-backup-manifest.md` and `09-phase-0-open-items.md`.

## I. Current-Main Reassessment Additions

### Commit/deployment
- [x] Current `main` HEAD re-queried.
- [x] Recent commit delta captured.
- [ ] Deployed source identity established.
- [x] Remote deployed comparison marked `NOT INSPECTABLE FROM THIS ENVIRONMENT`.
- [x] No tag created against an unverified deployment state.

### Subject contract
- [x] Subject Master purpose recorded.
- [x] Standard Wise Subject purpose recorded.
- [x] Current Add Subject behavior recorded.
- [x] Current canonical `subjects.id` writes recorded.
- [x] Historical mapping-ID compatibility recorded.
- [x] Subject-ID contract matrix completed.
- [x] Existing canonical/historical row counts captured safely from both the baseline source and live isolated MariaDB.
- [x] Dashboard subject display characterized statically; visual confirmation pending.

### Authentication
- [x] Admin create/update password path documented.
- [x] Login path documented.
- [x] Change Password path documented.
- [x] Forgot/Reset Password path documented.
- [x] Public registration route state verified.
- [x] No real password changed for baseline evidence.

### Routes
- [x] Public route inventory captured.
- [x] public teacher-bulk exam-details classified.
- [x] public test page classified.
- [x] GET ERP sync routes classified but not invoked.
- [x] Administrator-intended route middleware recorded.
- [x] stale route candidates recorded.
- [x] alternate marks-edit path recorded without production exploitation.

### MSSQL/current mapping
- [x] Year/Section source documented.
- [x] Standard source documented.
- [x] Division source documented.
- [x] Student source documented.
- [x] local-to-MSSQL mapping chain documented.

### Database evidence
- [x] Both committed SQL dumps hashed.
- [x] Current baseline SQL, historical SQL, and live-database authority distinguished.

## LOCAL-ONLY EXECUTION CORRECTION

The production-testing deployment is on a separate system that is not connected to this environment.

Therefore Phase 0 must NOT attempt to discover the deployed application folder, hash deployed files, compare local files directly with that server, start/stop the deployed service, or run commands on it.

For UI inspection, start the repository locally from VS Code using `LOCAL_VSCODE_RUN_GUIDE.md`.

`http://127.0.0.1:8000` is the local `php artisan serve` development server, not the remote deployment.

Use `db/school_management.sql` as the current MariaDB baseline. Keep `db/school_management_before_final_subject_cleanup.sql` only as a historical/pre-cleanup reference.

If MSSQL is not reachable from this machine, mark MSSQL-dependent flows as blocked/not locally validated rather than inventing behavior.

## J. Local VS Code Execution Boundary

- [x] Remote deployed testing system recorded as inaccessible.
- [x] No remote server inspection attempted after the new local-only correction.
- [x] `db/school_management.sql` selected as current local MariaDB baseline.
- [x] pre-cleanup SQL retained only as historical reference.
- [x] local `.env` points to isolated MariaDB database `school_management_phase0`.
- [x] `composer setup` was NOT used.
- [x] migrations were NOT used to reconstruct the baseline.
- [x] Laravel development server started from VS Code and HTTP 200 verified.
- [x] Vite started from VS Code and client HTTP 200 verified.
- [x] local UI provenance requirements recorded; new screenshots pending.
- [x] MSSQL availability recorded and verified.
- [x] MSSQL-dependent-screen rule recorded; MSSQL is currently available.
