# Phase 0 / Step 0.1 — Git and Runtime Baseline

## Step 0.1 re-execution freeze — 2026-08-18T11:24:47.7256866+05:30

**Step status:** `COMPLETE WITH OPEN ITEMS`. This re-run refreshes the source/runtime freeze only. It does not resume the separately blocked Step 0.2 or start Steps 0.3–0.5.

### Fresh repository identity

| Field | Classification | Frozen value |
|---|---|---|
| Repository / origin | Observed fact | `Prajnanabodhini/SchoolERP_Result_Management`; `https://github.com/Prajnanabodhini/SchoolERP_Result_Management.git` |
| Default / checked-out branch | Observed fact | `main` / `main`; upstream `origin/main` |
| Local HEAD | Observed fact | `1780866c61c0b7cb0e7a9652735ccdc312d671ad` |
| Fresh GitHub `main` | Observed fact | `1780866c61c0b7cb0e7a9652735ccdc312d671ad` |
| Local vs GitHub | Observed fact | MATCH; ahead/behind `0 / 0` |
| Commit | Observed fact | `Latest Code`; 2026-08-17 17:51:35 +0530 |
| Git tree | Observed fact | `0ce8b41c8a6e3b55974cfbe3275a9213561175fd` |
| Tracked worktree / index | Observed fact | Clean / clean |
| Untracked files | Observed fact | Current 14-file authoritative prompt pack, preserved/generated `docs/phase-0-baseline` package, and pre-existing `School ERP projects - Shortcut.lnk`; exact pre-update output was captured with `git status --porcelain=v1 --untracked-files=all` |
| Tag at HEAD | Observed fact | None; no tag created or pushed because none was explicitly authorized and the remote deployed identity is unavailable |
| Git object connectivity | Observed fact | Passed; `git fsck --full --no-reflogs` reported dangling tree objects only, with no connectivity failure |

### Fresh local runtime and data-source identity

| Item | Classification | Value |
|---|---|---|
| OS | Observed fact | Microsoft Windows NT 10.0.19045.0 |
| PHP / SAPI | Observed fact | 8.3.31 / CLI |
| Laravel / Composer | Observed fact | 13.17.0 / 2.10.2 |
| Node / npm | Observed fact | 26.7.0 / 11.19.0 from the operator runtime context |
| Local listeners | Observed fact | PHP at `127.0.0.1:8000`; Node/Vite at `[::1]:5173` |
| Relevant PHP extensions | Observed fact | `PDO`, `pdo_mysql`, `pdo_sqlsrv`, `sqlsrv`, `mbstring`, `openssl`, `fileinfo` |
| MariaDB | Observed SELECT-only | 12.3.2; `school_management_phase0`; 54 base tables |
| MSSQL | Observed SELECT-only | `Shirgaon SchoolERP`; 17.0.1125.2 Enterprise Developer Edition (64-bit); Windows Integrated Authentication |
| Application / CLI timezone | Observed fact | `Asia/Kolkata` / `UTC` |
| Environment | Observed fact | `APP_ENV=local`; `APP_DEBUG=true`; URL `http://127.0.0.1:8000` |
| Default DB / drivers | Observed fact | `mariadb` / `school_management_phase0`; cache/session/queue all `database`; MSSQL connection `sqlsrv_olderp` |
| Remote production-testing deployment | Operator-confirmed boundary | `NOT CONNECTED / NOT INSPECTABLE FROM THIS ENVIRONMENT` |
| Remote vs GitHub | Unknown by design | No comparison attempted; must be captured by the authorized remote operator |

### Fingerprint verification

All 28 required dependency, route, configuration, controller, model, helper, and SQL-dump entries in `evidence/checksums/step-0.1-sha256.txt` were recomputed with zero mismatches. The current baseline dump remains SHA-256 `f143b909fd9df56a6cf0c08461bf79b3f9023426167ac8f85bb1ddb9a6494ceb`; the historical pre-cleanup dump remains `699f6cb819c360595f64c2fc7673a9b4ed2aa00e6d7ac4673866e62342075257`. Live MariaDB identity remains supported by the previously captured 18-table count match.

### Re-execution acceptance review

- [x] Exact local and fresh GitHub baseline SHA recorded and matched.
- [x] Dirty/untracked state recorded; tracked worktree and index are clean.
- [x] Remote deployed identity explicitly recorded as unavailable by environment boundary.
- [x] All 28 required fingerprints recomputed with zero mismatch.
- [x] Runtime and both database identities refreshed without secrets.
- [x] No application code, configuration, database, credential, Git ref/history, tag, service, or deployment was changed.
- [x] No reset, checkout, rebase, force operation, pull, migration, seeder, state-changing route, or remote inspection occurred.
- [x] Step 0.2 remains separately blocked at its MSSQL backup-process stop condition; Steps 0.3–0.5 remain not started.

## New Step 0.1 execution freeze — 2026-08-18T10:51:49.4603703+05:30

**Step status:** COMPLETE WITH OPEN ITEMS — stop for human review before Step 0.2.

### Current repository freeze

| Field | Classification | Frozen value |
|---|---|---|
| Repository / origin | Observed | `Prajnanabodhini/SchoolERP_Result_Management`; `https://github.com/Prajnanabodhini/SchoolERP_Result_Management.git` |
| Default / checked-out branch | Observed | `main` / `main`; upstream `origin/main` |
| Local HEAD | Observed | `1780866c61c0b7cb0e7a9652735ccdc312d671ad` |
| Fresh GitHub `main` | Observed | `1780866c61c0b7cb0e7a9652735ccdc312d671ad` |
| Local vs GitHub | Observed | MATCH; ahead/behind `0 / 0` |
| Commit | Observed | `Latest Code`; 2026-08-17 17:51:35 +0530 |
| Git tree | Observed | `0ce8b41c8a6e3b55974cfbe3275a9213561175fd` |
| Tracked / staged changes | Observed | None |
| Untracked scope | Observed | Prompt pack, preserved/generated Phase 0 documentation/evidence, and pre-existing `School ERP projects - Shortcut.lnk` |
| Tag at HEAD | Observed | None; no tag created because none was authorized and remote deployment identity is unavailable here |
| Git object connectivity | Observed | Passed; dangling tree objects reported by `git fsck` are unreachable artifacts, not connectivity failures |

### Local-only deployment boundary

| Identity | Classification | Value |
|---|---|---|
| Local development source | Observed | This Git worktree at the frozen HEAD above |
| Local Phase 0 URL | Observed | `http://127.0.0.1:8000`; Laravel login HTTP 200 |
| Remote production-testing deployment | Operator-confirmed boundary | `NOT CONNECTED / NOT INSPECTABLE FROM THIS ENVIRONMENT` |
| Remote deployed SHA/manifest | Unavailable by design | Not queried; local IIS discovery is prohibited by the authoritative correction |
| Remote vs GitHub comparison | Not available | Must be collected on the remote system by an authorized operator; not a local Step 0.1 failure |

### Current runtime fingerprint

| Item | Classification | Value |
|---|---|---|
| OS | Observed | Microsoft Windows NT 10.0.19045.0 |
| PHP / SAPI | Observed | 8.3.31 / CLI; loaded `D:\Softwares\php\php.ini` |
| Laravel | Observed | 13.17.0 |
| Composer | Observed | 2.10.2 |
| Node / npm | Observed | 26.7.0 / 11.19.0 from the operator's VS Code runtime installation |
| Local web runtime | Observed | Laravel development server at `127.0.0.1:8000`; Vite client HTTP 200 at `localhost:5173` |
| Relevant PHP extensions | Observed | `PDO`, `pdo_mysql`, `pdo_sqlsrv`, `sqlsrv`, `mbstring`, `openssl`, `fileinfo` |
| SQL Server ODBC drivers | Observed | SQL Server plus ODBC Driver 17 and 18, 32-bit and 64-bit installations |
| MariaDB | Observed | Server/client 12.3.2; `school_management_phase0`; 54 base tables |
| MSSQL | Observed SELECT-only | `Shirgaon SchoolERP`; 17.0.1125.2 Enterprise Developer Edition (64-bit); Windows Integrated Authentication |
| Application / CLI timezone | Observed | `Asia/Kolkata` / `UTC` |
| `APP_ENV` / `APP_DEBUG` | Observed | `local` / `true` |
| Default DB / connection | Observed | `mariadb` / `school_management_phase0` |
| Cache / session / queue | Observed | `database` / `database` / `database` |
| MSSQL connection name / driver | Observed | `sqlsrv_olderp` / `sqlsrv` |

### SQL source/import identity

| Source | Classification | SHA-256 / verification |
|---|---|---|
| `db/school_management.sql` | Current local reconstruction baseline | `f143b909fd9df56a6cf0c08461bf79b3f9023426167ac8f85bb1ddb9a6494ceb`; live counts match across all 18 checked tables |
| `db/school_management_before_final_subject_cleanup.sql` | Historical/pre-cleanup only | `699f6cb819c360595f64c2fc7673a9b4ed2aa00e6d7ac4673866e62342075257` |

All required dependency/controller/model/helper hashes in `evidence/checksums/step-0.1-sha256.txt` were reverified with zero mismatches. No secret value was captured.

## Prior-run reconciliation context — preserved

This section supersedes the earlier deployment-discovery assumptions retained below as historical evidence.

| Field | Current classification | Reconciled value |
|---|---|---|
| Local source HEAD | Observed | `1780866c61c0b7cb0e7a9652735ccdc312d671ad` |
| GitHub `main` re-query | Observed 2026-08-18 | Matches local HEAD: `1780866c61c0b7cb0e7a9652735ccdc312d671ad` |
| Remote production-testing system | Operator-confirmed boundary | `NOT CONNECTED / NOT INSPECTABLE FROM THIS ENVIRONMENT` |
| Direct remote deployment comparison | Not applicable here | Must not be attempted from this environment |
| Local UI runtime | Authoritative method | VS Code terminals; `php artisan serve` at `http://127.0.0.1:8000`; Vite separately |
| Local MariaDB baseline source | Authoritative prompt-pack selection | `db/school_management.sql` |
| Historical SQL source | Historical only | `db/school_management_before_final_subject_cleanup.sql` |
| Local configured database | Observed effective config | `mariadb` / `school_management_phase0` |
| Local MSSQL | Verified SELECT-only | `Shirgaon SchoolERP`, Windows Integrated Authentication, actual application-table reads succeeded |

The prior `http://localhost/login` IIS probe was outside the corrected scope. Its screenshot is retained, but it is neither remote-deployment evidence nor local Laravel UI evidence. Remote inaccessibility is an environment boundary, not a Step 0.1 failure. See `evidence/prior-run-artifact-reconciliation.md`.

**Capture time:** 2026-08-17T22:01:59.7907393+05:30 (2026-08-17T16:31:59.7912570Z)  
**Capture scope:** read-only repository, runtime, and deployment-identity checks  
**Step status:** COMPLETE WITH OPEN ITEMS — awaiting human review before Step 0.2

## Classification key

- **Observed fact:** directly confirmed by the command or file named in this report.
- **Inference:** a conclusion from observed evidence, not independently proven.
- **Unknown:** unavailable or not safely verifiable.
- **Future target:** agreed architecture; not current behavior and not implemented here.

## Repository baseline

| Field | Classification | Value | Evidence |
|---|---|---|---|
| Repository | Observed fact | `Prajnanabodhini/SchoolERP_Result_Management` | `origin` URL and prompt-pack repository identity |
| Remote origin | Observed fact | `https://github.com/Prajnanabodhini/SchoolERP_Result_Management.git` | `git remote -v` |
| Default/tracking branch | Observed fact | `main`; `origin/HEAD -> origin/main`; upstream `origin/main` | `git symbolic-ref refs/remotes/origin/HEAD`; `git rev-parse --abbrev-ref --symbolic-full-name @{u}` |
| Checked-out branch | Observed fact | `main` | `git branch --show-current` |
| Local HEAD | Observed fact | `1780866c61c0b7cb0e7a9652735ccdc312d671ad` | `git rev-parse HEAD` |
| GitHub `main` HEAD | Observed fact | `1780866c61c0b7cb0e7a9652735ccdc312d671ad` | live `git ls-remote origin refs/heads/main` query |
| Local vs GitHub | Observed fact | `MATCH` | the two independently queried SHA values are identical |
| HEAD commit | Observed fact | `Latest Code` | `git show -s --format='%H%n%ci%n%s' HEAD` |
| HEAD commit time | Observed fact | `2026-08-17 17:51:35 +0530` | same command |
| Git tree object | Observed fact | `0ce8b41c8a6e3b55974cfbe3275a9213561175fd` | `git rev-parse HEAD^{tree}` |
| Ahead/behind upstream | Observed fact | `0 / 0` against the locally stored `origin/main` ref | `git rev-list --left-right --count @{u}...HEAD` |
| Tracked modifications | Observed fact | None before Phase 0 documentation was created | `git status --porcelain=v1 --untracked-files=all` |
| Untracked files before capture | Observed fact | The 13 files under `Prompts/Phase_0_Prompt/` and `School ERP projects - Shortcut.lnk` | same command; exact list below |
| Baseline tag | Observed fact | Not created; no tag points at HEAD | `git tag --points-at HEAD`; no operator authorization and deployment identity is unresolved |

### Initial untracked-file inventory

```text
Prompts/Phase_0_Prompt/00_01_FREEZE_CURRENT_WORKING_STATE.md
Prompts/Phase_0_Prompt/00_02_BACKUP_APPLICATION_AND_DATABASES.md
Prompts/Phase_0_Prompt/00_03_DOCUMENT_CURRENT_HAPPY_PATHS.md
Prompts/Phase_0_Prompt/00_04_CREATE_BASELINE_TEST_DATA_SET.md
Prompts/Phase_0_Prompt/00_05_CAPTURE_BASELINE_OUTPUTS.md
Prompts/Phase_0_Prompt/00_PHASE_0_MASTER.md
Prompts/Phase_0_Prompt/CURRENT_MAIN_REASSESSMENT.md
Prompts/Phase_0_Prompt/PACK_METADATA.json
Prompts/Phase_0_Prompt/PHASE_0_ACCEPTANCE_CHECKLIST.md
Prompts/Phase_0_Prompt/PHASE_0_PROGRESS_LOG.md
Prompts/Phase_0_Prompt/PROMPT_PACK_SHA256.txt
Prompts/Phase_0_Prompt/README_PHASE_0.md
Prompts/Phase_0_Prompt/START_HERE_PHASE_0_STEP_0_1.md
School ERP projects - Shortcut.lnk
```

The `docs/phase-0-baseline/` files and progress-log edit are Phase 0 documentation produced after this initial status capture. They are not runtime code.

## Historical deployment-vs-Git gate — superseded by local-only correction

| Identity | Classification | Value |
|---|---|---|
| GitHub `main` HEAD (`SOURCE-HEAD`) | Observed fact | `1780866c61c0b7cb0e7a9652735ccdc312d671ad` |
| Inspected local working-tree root | Observed fact | `D:\School ERP projects\SchoolERP Result Management` |
| Inspected local working-tree HEAD | Observed fact | `1780866c61c0b7cb0e7a9652735ccdc312d671ad` |
| Deployed production-testing root | **Unknown** | IIS site configuration could not be read due to insufficient permission |
| Deployed production-testing HEAD/manifest (`DEPLOYED-BASELINE`) | **Unknown** | The deployed root could not be established, so no deployed hash was fabricated |
| Deployed vs GitHub | **Unknown** | Cannot compare until the IIS physical path or another authoritative deployment record is supplied |

Observed deployment indicators in the inspected working tree: `.env`, `vendor/`, `node_modules/`, and `public/build/` exist; the Windows `W3SVC` service is running. The shortcut in the root points only to `D:\School ERP projects`. These facts make the inspected folder a plausible runtime/deployment candidate, but they do not prove the IIS physical path. That conclusion is therefore an **inference**, not a deployed identity.

Read-only deployment discovery was attempted with `Get-Website`, `Get-WebApplication`, and `%SystemRoot%\System32\inetsrv\appcmd.exe list site/app/vdir`. The IIS provider was unavailable and `appcmd` returned `Cannot read configuration file due to insufficient permissions`, including after explicit elevated-command approval. No IIS configuration was changed.

### Historical gate decision — superseded

At the Step 0.1 boundary, the deployment-identity gate required human review before backup claims could be completed. The operator subsequently instructed execution of the full master prompt, so Steps 0.2–0.5 continued using read-only repository/static/artifact evidence. Their live backup and output gates remain blocked. No tag was created or pushed.

The following was the previous-run proposal and is not an action for this local environment:

1. the IIS site's authoritative physical path and application mapping, or
2. a read-only export/screenshot of the relevant IIS site/application configuration.

If that path is not a Git worktree, capture a read-only SHA-256 file manifest excluding runtime-generated logs and caches, then compare it with `SOURCE-HEAD` without overwriting either copy.

## Dependency and source fingerprints

All hashes are SHA-256 over the current files in the inspected local working tree. The machine-readable list is in `evidence/checksums/step-0.1-sha256.txt`.

| File | SHA-256 |
|---|---|
| `composer.json` | `22c007173dda5d2d7519358a20010c98c9820ea950beeb7dd606a1aaac927fb9` |
| `composer.lock` | `8a78f2b3eaff9f149b1805f291a72f0b7fd505c9985b124a9200b52ccfa26e60` |
| `package.json` | `62c401d2039ecdc0231a53858eb8cba65dfcb2b5a4d5e21db7b665216eb8d995` |
| `package-lock.json` | `e52c92c195966a5faf59790d92db9e406951980470b23aa9cde73f080a410f0a` |
| `vite.config.js` | `36115cea61096c250d9ce2f77db4d37c5d7d75a82725a35e39ac7b37c3d304bf` |
| `config/database.php` | `37ea993b378917068dd25e3e6d63ed502d1f71a18ad5b5d6fcec056c78fa9bb1` |
| `bootstrap/app.php` | `810566eefe8bb4aa869152dadcf051598557510fa8368b27af73324427190dfc` |
| `routes/web.php` | `d29207f1fa49ef05c75e4840a528b6774600a12a4cdfcf445232eab50565ac56` |
| `routes/auth.php` | `b45128e0e7aa93417513865ff67220b47c640959a3d1579faf5f9dec0e0611d1` |

The required controller/model/helper and SQL-dump hashes are also present in the machine-readable checksum evidence. In particular:

| SQL dump | SHA-256 | Authority classification |
|---|---|---|
| `db/school_management.sql` | `f143b909fd9df56a6cf0c08461bf79b3f9023426167ac8f85bb1ddb9a6494ceb` | Committed artifact; not assumed to equal the live database |
| `db/school_management_before_final_subject_cleanup.sql` | `699f6cb819c360595f64c2fc7673a9b4ed2aa00e6d7ac4673866e62342075257` | Committed artifact; not assumed to equal the live database |

## Runtime fingerprint

| Item | Classification | Observed value | Evidence |
|---|---|---|---|
| PHP | Observed fact | `8.3.31`, NTS, Visual C++ 2019 x64 | `php -v` |
| PHP SAPI | Observed fact | `cli` for the inspection commands | `php -r` / `PHP_SAPI` |
| Laravel | Observed fact | `13.17.0` | `php artisan --version`; `php artisan about --only=environment` |
| Composer | Observed fact | `2.10.2` | `composer --version` |
| Node | Unknown | Not available on `PATH` | `node --version` |
| npm | Unknown | Not available on `PATH` | `npm --version` |
| OS kernel identity | Observed fact | `Microsoft Windows NT 10.0.19045.0` | `[System.Environment]::OSVersion.VersionString` |
| OS edition | Unknown | WMI query was denied | `Get-CimInstance Win32_OperatingSystem` |
| Web server service | Observed fact | IIS `W3SVC` is running | `Get-Service W3SVC` |
| Deployed web-server mapping | Unknown | IIS site configuration inaccessible | deployment discovery commands above |
| Loaded PHP configuration | Observed fact | `D:\Softwares\php\php.ini` | `php --ini` |
| MSSQL PHP support | Observed fact | `pdo_sqlsrv`, `sqlsrv` loaded | `php -m` |
| MySQL PHP support | Observed fact | `mysqlnd`, `pdo_mysql` loaded | `php -m` |
| SQL Server client tooling | Observed fact | `sqlcmd` 17.0.1000.7; connection attempt identified ODBC Driver 17 | `sqlcmd -?`; `php artisan db:show --database=sqlsrv_olderp` |
| MariaDB/MySQL server version | Unknown | SELECT-only connection failed: server requested unsupported `auth_gssapi_client` method | `php artisan db:show --database=mysql --no-interaction` |
| MSSQL server version | Unknown | SELECT-only connection failed: ODBC client reported encryption unsupported | `php artisan db:show --database=sqlsrv_olderp --no-interaction` |
| PHP CLI default timezone | Observed fact | `UTC` | `date_default_timezone_get()` before Laravel bootstrap |
| Laravel timezone | Observed fact | `Asia/Kolkata` | `php artisan config:show app.timezone` |
| `APP_ENV` | Observed fact | `local` | `php artisan config:show app.env` |
| `APP_DEBUG` | Observed fact | `true` / enabled | `php artisan config:show app.debug`; `artisan about` |
| Default DB connection | Observed fact | `mysql` | `php artisan config:show database.default` |
| Configured DB connections | Observed fact | `sqlsrv_olderp`, `mysql`, `mariadb`, `sqlsrv` among the named connections | static inspection of `config/database.php`; no credentials recorded |
| Cache driver | Observed fact | `file` | `php artisan config:show cache.default` |
| Session driver | Observed fact | `database` | `php artisan config:show session.driver` |
| Queue driver | Observed fact | `database` | `php artisan config:show queue.default` |

The two database inspections were SELECT-only metadata probes. Both failed before a server version could be retrieved; no query result or database mutation occurred. No credentials, connection strings, account names, `APP_KEY`, tokens, or passwords are included here.

## Commands used

```text
git ls-remote origin refs/heads/main
git remote -v
git branch --show-current
git rev-parse HEAD
git show -s --format='%H%n%ci%n%s' HEAD
git status --porcelain=v1 --untracked-files=all
git log --oneline --decorate -20
git log --name-status -10
git rev-parse HEAD^{tree}
git tag --points-at HEAD
Get-FileHash <required baseline files> -Algorithm SHA256
php -v
php artisan --version
php artisan about --only=environment
composer --version
node --version
npm --version
php --ini
php -m
php artisan config:show <safe non-secret key>
php artisan db:show --database=mysql --no-interaction
php artisan db:show --database=sqlsrv_olderp --no-interaction
Get-Service W3SVC
Get-Website / Get-WebApplication
appcmd.exe list site / app / vdir
```

## Safety and open items

- **Observed fact:** no application PHP, Blade, JavaScript, CSS, configuration, migration, schema, or business-logic file was edited.
- **Observed fact:** no migration, seeder, ERP synchronization, marks operation, result generation, credential operation, database write, checkout/reset/rebase, tag creation, push, or deployment overwrite was performed.
- **Observed fact:** only documentation files were created/updated by this step.
- **Environment boundary:** the remote deployed application root/SHA is intentionally not inspectable here; do not attempt local IIS discovery.
- **Observed follow-up:** MariaDB 12.3.2 live SELECT-only counts match the designated source across all checked tables. MSSQL version and real table reads are verified in `evidence/counts/mssql-read-verification.md`.
- **Observed follow-up:** Node/npm 26.7.0/11.19.0; Laravel and Vite endpoints respond locally.
- **Next action:** continue local-only Phase 0 using `LOCAL_VSCODE_RUN_GUIDE.md`; do not inspect the remote deployment.
