# SchoolERP — Phase 0 Progress Log

Use this file as the working memory for Copilot/Codex/developers executing Phase 0.

## Rules

- Append progress; do not rewrite history silently.
- Record evidence paths.
- Record blockers.
- Record all deviations.
- Never mark a step complete without acceptance-criteria review.
- Do not record secrets.

---

## Phase 0 Status

**Overall:** BLOCKED AT STEP 0.2 — AUTHORITATIVE MSSQL BACKUP PROCESS/OWNER REQUIRES OPERATOR OR DBA CONFIRMATION

| Step | Status | Started | Completed | Operator/Agent | Evidence | Open Items |
|---|---|---|---|---|---|---|
| 0.1 Freeze working state | COMPLETE WITH OPEN ITEMS / REVALIDATED | 2026-08-18T10:51:49+05:30 | 2026-08-18T11:27:18+05:30 | Codex | `01-git-baseline.md`; `00-recent-commit-delta.md`; Step 0.1 checksum evidence | Remote deployed SHA intentionally unavailable here; no tag authorized; Step 0.2 remains separately blocked |
| 0.2 Backups | BLOCKED AT STOP CONDITION | 2026-08-18T11:13:00+05:30 | | Codex | `docs/phase-0-baseline/03-backup-manifest.md`; secured off-repository backup set | Local source/`.env`/evidence/MariaDB verified; remote deployment unavailable; MSSQL has no visible backup history and owner/process is unknown |
| 0.3 Current happy paths | NOT STARTED — static evidence available | | | | data-source, happy-path, authentication, route, and DB evidence | New run must capture authenticated local flows |
| 0.4 Baseline test data | NOT STARTED — source cases available | | | | `docs/phase-0-baseline/06-baseline-test-data.md` | New run must select namespace-explicit live contexts |
| 0.5 Baseline outputs | NOT STARTED — numerical evidence available | | | | output catalog, MariaDB/MSSQL counts, route list | New run must capture local screenshots/reports |

---

## Log Entries

### 2026-08-18 — Prior-run reconciliation against prompt pack 3.0-local-vscode

**Timestamp:** 2026-08-18T10:03:55.2830396+05:30  
**Step:** Pre-continuation reconciliation across Steps 0.1–0.5  
**Actor:** Codex  
**Action:** Inventoried all 22 existing `docs/phase-0-baseline` artifacts, read all new Markdown instructions, verified prompt hashes, ran sanitized local configuration checks, attempted local service/UI probes, and performed SELECT-only MSSQL verification through Windows Integrated Authentication. Existing documents were updated in place; no historical file was deleted.  
**Observed result:** Prompt pack hashes matched before the authorized progress-log/checklist updates. GitHub `main` and local HEAD still match at `1780866c61c0b7cb0e7a9652735ccdc312d671ad`. Local config targets `mariadb` database `school_management_phase0`, database-backed cache/session/queue, and `http://127.0.0.1:8000`. MSSQL `Shirgaon SchoolERP` and actual reads from `SubStudentMst`, `FeeMstStudent`, `StandardMst`, and `DivisionMst` are verified. The MariaDB service and Laravel/Vite listeners were not running at capture time; the approved MariaDB service-start attempt failed for service permissions.  
**Evidence:** `docs/phase-0-baseline/evidence/prior-run-artifact-reconciliation.md`; `evidence/runtime/local-environment-verification.md`; `evidence/counts/mssql-read-verification.md`  
**Files created:** Three distinct reconciliation/runtime evidence files. Existing Phase 0 documents were corrected in place.  
**Production impact:** READ-ONLY except an unsuccessful attempt to start the local MariaDB service; no service was actually started or stopped. Documentation writes only.  
**Issues/blockers:** Local MariaDB must be started by the operator; Node/npm are unavailable in the current Codex shell; local Laravel/Vite/UI evidence remains pending; backup/restore evidence remains partial.  
**Next action:** Continue via `LOCAL_VSCODE_RUN_GUIDE.md` after the local MariaDB service is running.

#### Old → new corrections recorded

| Old conclusion/assumption | New authoritative correction |
|---|---|
| Discover/hash a local IIS deployment | Remote deployed testing system is separate, disconnected, and out of scope for local inspection |
| Local IIS 404 as deployment evidence | Historical out-of-scope evidence only; preserve but do not use for comparison |
| Remote identity gap blocks local Phase 0 | It is an environment boundary, not itself a Phase 0 failure |
| IIS runtime framing | Local UI runtime is VS Code + `php artisan serve` + Vite |
| MySQL/file cache assumptions | Effective config is MariaDB `school_management_phase0`, database cache/session/queue |
| Main SQL treated only as historical artifact | `db/school_management.sql` is the current local reconstruction baseline; pre-cleanup SQL is historical |
| MSSQL inaccessible | Windows Integrated Authentication and actual Laravel table reads are verified |
| All live DB evidence unavailable | MSSQL live read counts are captured; local MariaDB recheck remains pending because its service was stopped |

---

### 2026-08-18 — Live local-stack preflight completion

**Timestamp:** 2026-08-18 (after operator startup)  
**Step:** Pre-Phase-0 readiness check; no Phase 0 step started  
**Actor:** Codex  
**Action:** Rechecked the operator-started Laravel and Vite servers, queried the isolated MariaDB baseline with SELECT-only commands, captured Node/npm/MariaDB versions, ran observation-only migration status, and re-audited old/new artifact assumptions.  
**Observed result:** MariaDB is running at version 12.3.2. All 18 checked live table counts match `db/school_management.sql`. Laravel `/` and `/login` return HTTP 200 and the login page renders. Vite `/@vite/client` returns HTTP 200. Node/npm are 26.7.0/11.19.0. MSSQL Integrated Authentication/read evidence remains valid. One imported-schema drift item was confirmed: `exam_master_subjects` exists with 63 rows while its create migration is pending.  
**Evidence:** `docs/phase-0-baseline/evidence/counts/mariadb-live-verification.md`; updated runtime and reconciliation evidence  
**Files created:** One distinct MariaDB live-verification evidence file; existing documents updated in place.  
**Production impact:** READ-ONLY; no form submitted, migration run, database write, credential change, result generation, marks action, ERP sync, or remote-system access.  
**Issues/blockers:** None for starting the new local Phase 0. Backup acceptance, authenticated captures, selected regression contexts, and report evidence are Phase 0 work—not preflight blockers.  
**Next action:** Await operator instruction to start the new Phase 0 at Step 0.1.

---

### 2026-08-18 — New Phase 0 Step 0.1 execution

**Timestamp:** 2026-08-18T10:55:09.8450319+05:30  
**Step:** 0.1 — Freeze current working state  
**Actor:** Codex  
**Action:** Re-read the authoritative Step 0.1 chain; re-queried local Git and live GitHub `main`; captured the 20-commit request (repository contains four commits), ten-commit name/status request, working-tree state, dependency/application hashes, runtime versions, database versions, local SQL import identity, and local development endpoints.  
**Observed result:** Local HEAD, `origin/main`, and live GitHub `main` match at `1780866c61c0b7cb0e7a9652735ccdc312d671ad`; tree `0ce8b41c8a6e3b55974cfbe3275a9213561175fd`; ahead/behind 0/0; no tracked/staged changes; no tag at HEAD. All 29 Step 0.1 file hashes reverified without mismatch. Local MariaDB and MSSQL versions/reads are verified. Remote deployment remains intentionally uninspectable from this environment.  
**Evidence:** `docs/phase-0-baseline/01-git-baseline.md`; `docs/phase-0-baseline/00-recent-commit-delta.md`; `docs/phase-0-baseline/evidence/checksums/step-0.1-sha256.txt`; live MariaDB/MSSQL/runtime evidence  
**Files created:** None required; existing Step 0.1 artifacts updated in place.  
**Production impact:** READ-ONLY; documentation writes only.  
**Issues/blockers:** Human-readable prior-run references contained two incorrect SQL dump hashes; corrected to the values already present in the machine checksum manifest and recorded here. Remote deployed SHA/manifest is unavailable by the authoritative environment boundary.  
**Next action:** Human review of Step 0.1. Do not start Step 0.2 until the operator instructs it.

#### Step 0.1 acceptance review

- [x] Exact Git baseline SHA recorded and freshly matched to GitHub `main`.
- [x] Dirty/untracked state recorded; tracked and staged changes are absent.
- [x] Remote deployed identity explicitly recorded as unavailable by environment boundary.
- [x] Required dependency and current-main file fingerprints captured and reverified.
- [x] Runtime versions/configuration captured without secrets.
- [x] Correct current and historical SQL source identities/hashes recorded.
- [x] No application code, Git history, database row, schema, credential, result, marks, ERP data, service, or remote deployment changed.
- [x] No reset, checkout, rebase, force operation, migration, seeder, state-changing route, or tag operation performed.
- [x] Tag intentionally not created because no authorization was given and remote deployed identity is not inspectable here.
- [x] Step 0.2 remains not started.

---

### 2026-08-18 — Current `main` reassessment execution

**Timestamp:** 2026-08-18T11:07:22+05:30  
**Step:** Current-main reassessment after Step 0.1; Step 0.2 not started  
**Actor:** Codex  
**Action:** Re-read `CURRENT_MAIN_REASSESSMENT.md`; freshly compared local HEAD with GitHub `main`; rechecked the recent commit scope, canonical and historical Subject-ID source contracts, dashboard relationship, authentication paths, alternate marks edit, route exposure, stale routes, migration/test drift, and local-to-MSSQL mapping. Ran aggregate SELECT-only live MariaDB classification for all five transitional Subject-ID tables and verified the saved route inventory against a newly generated in-memory route list. Existing artifacts were updated in place instead of creating conflicting versions.  
**Observed result:** Local and GitHub `main` remain `1780866c61c0b7cb0e7a9652735ccdc312d671ad`; tree `0ce8b41c8a6e3b55974cfbe3275a9213561175fd`; ahead/behind 0/0; tracked worktree and index clean; no tag at HEAD. The 171-route inventory remains exact (159 authenticated, 12 without `auth`). Live Subject-ID classification exactly matches the designated SQL source: 2,457 canonical-only and 1,887 legacy-only rows across the five transitional tables, with zero both-valid and zero neither-valid rows. All 95 mapping rows resolve to canonical Subject Master rows. The prompt’s remaining source findings are confirmed.  
**Evidence:** `docs/phase-0-baseline/00-recent-commit-delta.md`; `subject-id-contract-matrix.md`; `route-exposure-matrix.md`; `authentication-path-contract.md`; `current-local-to-mssql-mapping.md`; `evidence/counts/mariadb-live-verification.md`  
**Files created:** No duplicate functional artifact. Existing evidence was safely superseded in place; one reassessment checksum snapshot will cover the revised package.  
**Production impact:** READ-ONLY; documentation writes only. No remote deployment access, route invocation, credential use, migration, normalization, marks/result action, or database write.  
**Issues/blockers:** Remote deployed source identity remains unavailable by the authoritative environment boundary. Dashboard pending-subject visual evidence remains a later Phase 0 capture item. These do not alter the reassessment conclusions.  
**Next action:** Human review of the reassessment and Step 0.1. Step 0.2 remains `NOT STARTED` until explicitly instructed.

#### Current-main reassessment acceptance review

- [x] GitHub `main` re-queried immediately before reassessment freeze.
- [x] Current Subject Master writes and historical compatibility revalidated in source.
- [x] Canonical-versus-legacy rows classified against live MariaDB with aggregate SELECT-only queries.
- [x] Dashboard relationship divergence revalidated without invoking the UI flow.
- [x] Authentication, route exposure, alternate marks edit, stale routes, and migration/test drift revalidated.
- [x] Local-to-MSSQL translation evidence retained; verified connection status not overstated as full mapping coverage.
- [x] Remote deployment explicitly retained as `NOT CONNECTED / NOT INSPECTABLE FROM THIS ENVIRONMENT`.
- [x] No application, database, credential, deployment, Git history, or tag change performed.
- [x] Step 0.2 remains not started.

---

### 2026-08-18 — Phase 0 Step 0.2 backup execution and stop condition

**Timestamp:** 2026-08-18T11:17:53+05:30  
**Step:** 0.2 — Back up application and databases  
**Actor:** Codex  
**Action:** Created a restricted off-repository backup directory; produced and verified an all-refs Git bundle, exact protected `.env` copy, Phase 0 evidence archive, and consistent MariaDB dump. Queried MSSQL backup history/permission metadata using SELECT-only Windows Integrated Authentication. Inspected local runtime listeners and scheduler/service evidence without changing them.  
**Observed result:** Git bundle verification passed with complete history at frozen SHA. The protected `.env` hash matches its source. The evidence ZIP is readable with 44 entries. The validated MariaDB dump completed with exit 0, contains 54 table definitions and all expected transition tables, and has a normal completion footer. MSSQL `msdb` exposes no full or other backup record for `Shirgaon SchoolERP`; the current principal reports backup permission, but server-side destination, authorization, retention owner, and restore-test evidence are unknown.  
**Evidence:** `docs/phase-0-baseline/03-backup-manifest.md`; protected backup set `SchoolERP-Phase0-2026-08-18` outside the repository  
**Files created:** Five protected backup files: verified source bundle, `.env` backup, evidence ZIP, validated MariaDB dump, and one preserved unverified initial dump attempt. Documentation updated in place.  
**Production impact:** Backup-file writes only in the protected off-repository directory; SELECT-only database inspection. No application code, Git refs/remotes, database row/schema, credential, running service, or remote deployment changed.  
**Issues/blockers:** Step prompt stop condition reached because no authoritative MSSQL backup process/owner/reference is known. Remote deployed application and web-server backups also require the disconnected system's operator.  
**Next action:** Operator/DBA must identify the MSSQL backup process/owner (or explicitly govern it as pending) before Step 0.3 starts.

#### Step 0.2 acceptance review

- [x] Local source, secret configuration, Phase 0 evidence, and MariaDB backup artifacts created outside Git with restricted ACLs.
- [x] Source bundle and archive readability verified; checksums recorded.
- [x] MariaDB dump completed consistently and passed structural validation.
- [x] No restore was attempted and neither database was changed.
- [ ] Remote deployed application/web-server backup evidence requires the disconnected system's operator.
- [ ] MSSQL backup owner/process/reference requires operator/DBA confirmation.
- [x] Step 0.3 remains not started.

---

### 2026-08-18 — Requested Step 0.1 re-execution

**Timestamp:** 2026-08-18T11:27:18+05:30  
**Step:** 0.1 — Freeze current working state, re-execution  
**Actor:** Codex  
**Action:** Re-read the authoritative Step 0.1 prompt; re-queried local Git and fresh GitHub `main`; captured current commit/tree/ahead-behind/status/log/tag state; revalidated Git object connectivity; recomputed all required file fingerprints; refreshed PHP/Laravel/Composer/Node/npm/MariaDB/MSSQL/runtime identity using non-mutating checks.  
**Observed result:** Local and GitHub `main` remain `1780866c61c0b7cb0e7a9652735ccdc312d671ad`; tree `0ce8b41c8a6e3b55974cfbe3275a9213561175fd`; ahead/behind 0/0; tracked worktree and index clean; no tag at HEAD. All 28 machine-manifest fingerprints match. MariaDB remains `school_management_phase0` with 54 base tables; MSSQL remains `Shirgaon SchoolERP` 17.0.1125.2; local PHP/Vite listeners remain present.  
**Evidence:** `docs/phase-0-baseline/01-git-baseline.md`; `02-runtime-baseline.md`; `evidence/checksums/step-0.1-sha256.txt`  
**Files created:** No new functional artifact; existing Step 0.1 documents updated safely in place.  
**Production impact:** READ-ONLY; documentation writes only.  
**Issues/blockers:** Remote deployed identity remains unavailable by the authoritative local-only boundary. Historical Step 0.1 text referred to 29 fingerprints; the machine manifest contains 28 required entries, all verified. Phase 0 remains blocked at the separate Step 0.2 MSSQL backup-process stop condition.  
**Next action:** Await operator direction for Step 0.2 governance. Do not start Steps 0.3–0.5.

#### Re-execution acceptance review

- [x] Exact Git SHA, commit, tree, branch, upstream, dirty/untracked state, and tag state captured.
- [x] Fresh GitHub `main` matches local HEAD.
- [x] Remote deployed comparison explicitly unavailable; no local IIS inference used.
- [x] Required dependency/source/SQL hashes and runtime versions revalidated.
- [x] No secret recorded and no code, configuration, database, Git metadata, service, or deployment changed.
- [x] Step 0.2 blocker and later-step boundary preserved.

---

### Entry Template

**Timestamp:**  
**Step:**  
**Actor:**  
**Action:**  
**Observed result:**  
**Evidence:**  
**Files created:**  
**Production impact:** NONE / READ-ONLY / OTHER  
**Issues/blockers:**  
**Next action:**  

---
