# Runtime baseline

## Step 0.1 revalidation — 2026-08-18T11:24:47+05:30

Fresh non-mutating checks reconfirmed PHP 8.3.31, Laravel 13.17.0, Composer 2.10.2, Node 26.7.0, npm 11.19.0, MariaDB 12.3.2 with 54 base tables in `school_management_phase0`, and MSSQL 17.0.1125.2 Enterprise Developer Edition (64-bit) for `Shirgaon SchoolERP`. Effective non-secret configuration remains `APP_ENV=local`, `APP_DEBUG=true`, application timezone `Asia/Kolkata`, default `mariadb`, and database-backed cache/session/queue. PHP and Vite listeners remain present at ports 8000 and 5173. No runtime service or configuration was changed.

Captured: 2026-08-17T22:21:12+05:30. Environment label in the inspected repository: `local`.

## Authoritative reconciliation — 2026-08-18T10:03:55+05:30

| Item | Current observed/operator-confirmed value | Evidence |
|---|---|---|
| Runtime boundary | Local VS Code development instance; remote testing system not connected/inspectable | Prompt pack v3.0 and operator statement |
| Laravel URL | `http://127.0.0.1:8000` | follow-up HTTP and browser verification succeeded |
| PHP / Laravel / Composer | 8.3.31 / 13.17.0 / 2.10.2 | `php artisan about` |
| Default database | `mariadb`, database `school_management_phase0` | effective sanitized configuration |
| Cache / session / queue | `database` / `database` / `database` | `php artisan about` |
| MSSQL | `Shirgaon SchoolERP`; Windows Integrated Authentication; SQL Server 17.0.1125.2 | direct and Laravel-configured SELECT-only reads |
| MariaDB | Server/client 12.3.2; `school_management_phase0` live reads verified | SELECT-only source-to-live count comparison |
| Node/npm | 26.7.0 / 11.19.0 | VS Code runtime installation; Vite client HTTP 200 |

The correct local UI server is Laravel's development server, not IIS. Detailed point-in-time evidence is in `evidence/runtime/local-environment-verification.md`.

## Historical prior-run observations — superseded where they conflict above

| Item | Observed value | Evidence |
|---|---|---|
| PHP | 8.3.31 | `php -v` |
| Laravel | 13.17.0 | `php artisan --version` |
| Composer | 2.10.2 | `composer --version` |
| OS | Windows NT 10.0.19045 | PHP/runtime command output |
| Web service | IIS `W3SVC` running; `w3wp` present | Windows service/process queries |
| Relevant PHP extensions | `pdo_sqlsrv`, `sqlsrv`, `pdo_mysql` | `php -m` |
| Application timezone | `Asia/Kolkata` | effective Laravel configuration |
| `APP_ENV` | `local` | effective Laravel configuration; no secret copied |
| `APP_DEBUG` | `true` | effective Laravel configuration; no secret copied |
| Cache | `file` | historical capture; superseded by current effective `database` value |
| Session | `database` | effective Laravel configuration |
| Queue | `database` | effective Laravel configuration |
| Database connection names | default MySQL/MariaDB plus `sqlsrv_olderp` | effective config and `config/database.php` |
| SQL services | MariaDB and `MSSQLSERVER` services running | historical point-in-time query; at reconciliation MariaDB was stopped and MSSQLSERVER running |

The configured `.env` file is ignored by Git and was not copied into this evidence package. Secret values were not recorded.

## Historical unknowns — superseded where corrected above

- Node and npm were not available on `PATH`; build-tool versions are `NOT AVAILABLE`.
- The live MariaDB version could not be queried because authentication negotiation failed before a query ran.
- The restricted-process MSSQL attempt failed during credential/encryption setup. This is superseded by successful Windows Integrated Authentication reads outside the restricted sandbox.
- IIS discovery is outside the corrected local-only Phase 0 scope. The remote testing deployment is on another disconnected system.
- The historical `http://localhost/login` 404 is retained but is not valid deployment or local-Laravel evidence.

No database write, route with application side effects, migration, seeder, or application test was executed.
