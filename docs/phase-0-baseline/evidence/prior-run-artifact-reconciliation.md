# Prior-run artifact reconciliation

Reconciled: 2026-08-18T10:03:55+05:30.

The 22 files below existed before the 2026-08-18 reconciliation. None was deleted. The authoritative prompt pack is version `3.0-local-vscode`; its published hashes were verified with zero mismatches.

| Existing artifact | Reconciliation disposition |
|---|---|
| `00-recent-commit-delta.md` | Preserved; remote-IIS conclusion superseded in place |
| `01-git-baseline.md` | Updated for local-only execution boundary |
| `02-runtime-baseline.md` | Updated for local VS Code configuration and verified MSSQL access |
| `03-backup-manifest.md` | Updated: current SQL source versus historical dump corrected |
| `04-current-data-source-map.md` | Preserved and supplemented with verified MSSQL reads |
| `05-current-happy-paths.md` | Preserved; validation status corrected from remote-live pending to local evidence status |
| `06-baseline-test-data.md` | Preserved; main SQL classified as designated local baseline source |
| `07-baseline-output-catalog.md` | Updated; prior IIS image classified as out-of-scope historical evidence |
| `08-known-current-issues.md` | Preserved; deployment issue corrected to remote environment boundary |
| `09-phase-0-open-items.md` | Updated; obsolete remote-IIS blocker removed |
| `authentication-path-contract.md` | Preserved; operator-confirmed local admin authentication recorded |
| `current-local-to-mssql-mapping.md` | Preserved; live MSSQL read verification added |
| `route-exposure-matrix.md` | Preserved unchanged; repository route evidence remains valid |
| `subject-id-contract-matrix.md` | Preserved unchanged; main SQL remains the designated baseline source |
| `evidence/route-list/route-list.tsv` | Preserved; 171 repository routes remain valid |
| `evidence/counts/committed-dump-counts.md` | Preserved; relabelled as current local baseline-source counts |
| `evidence/db-schema/committed-dump-schema-summary.md` | Preserved; main/historical roles clarified |
| `evidence/reports/README.md` | Updated for local-only output capture |
| `evidence/screenshots/DEPLOY-UNKNOWN-localhost-login-404.png` | Preserved as historical evidence; explicitly invalid for remote deployment comparison and local Laravel UI |
| `evidence/screenshots/README.md` | Updated with supersession notice |
| `evidence/checksums/step-0.1-sha256.txt` | Preserved as historical checksum evidence |
| `evidence/checksums/phase-0-package-sha256.txt` | Superseded by a regenerated manifest after reconciliation; file retained |

## Old → new corrections

| Previous-run conclusion | Authoritative correction |
|---|---|
| Attempt to discover a local IIS deployment | Remote production-testing system is a separate, disconnected environment. It must not be inspected from here. |
| `localhost/login` IIS 404 treated as deployment discovery | Out of scope. The image proves only that the default local IIS root answered that historical request. |
| Deployed identity was a Phase 0 blocking discovery task | Record remote deployment as `NOT CONNECTED / NOT INSPECTABLE`; this boundary is not itself a Phase 0 failure. |
| Runtime framed as IIS | Correct local UI runtime is VS Code plus `php artisan serve` at `http://127.0.0.1:8000`, with Vite when available. |
| Default/cache source reported as MySQL/file | Effective current configuration is `mariadb` / `school_management_phase0`, with database-backed cache, session, and queue. |
| Both SQL dumps treated alike as historical artifacts | `db/school_management.sql` is the designated current local MariaDB reconstruction baseline; the pre-cleanup dump is historical only. |
| MSSQL unavailable | Windows Integrated Authentication and application-configured `sqlsrv_olderp` reads are verified against `Shirgaon SchoolERP`. |
| Live DB/UI evidence universally unavailable | MSSQL count evidence is now live and local. MariaDB/UI recapture remains pending because the MariaDB service and development servers were not running during this reconciliation. |

No historical file was deleted, no application behavior was changed, and no database write was performed.

## Live-stack follow-up

After the operator started the local services, the preflight verification confirmed MariaDB 12.3.2, Node/npm 26.7.0/11.19.0, Laravel HTTP 200, Vite client HTTP 200, and a rendered login form. Live counts in `school_management_phase0` match the designated SQL source for every checked table. The earlier stopped-service/listener observations remain valid only for their original timestamp and are superseded for current readiness.
