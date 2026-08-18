# Local environment verification

Captured: 2026-08-18T10:03:55+05:30.

## Verified non-secret configuration

| Item | Value / status |
|---|---|
| Laravel | 13.17.0 |
| PHP CLI | 8.3.31 |
| Composer | 2.10.2 |
| Application environment | `local` |
| Debug | enabled |
| URL | `http://127.0.0.1:8000` |
| Timezone | `Asia/Kolkata` |
| Default database connection | `mariadb` |
| Local MariaDB database | `school_management_phase0` |
| Cache/session/queue | `database` / `database` / `database` |
| MSSQL driver | `sqlsrv`; configured credentials blank for Windows Integrated Authentication |
| MSSQL application read | VERIFIED; see `../counts/mssql-read-verification.md` |
| MariaDB client | 12.3.2 |
| Node/npm in current Codex shell | Not available on `PATH`; this does not prove absence from the operator's VS Code terminal |

## Point-in-time process status

- Operator-provided environment state: local Laravel, MariaDB, admin authentication, and MSSQL are working.
- At the agent's capture time, no listener existed on port 8000 or 5173.
- The MariaDB Windows service was `Stopped`; an approved start attempt failed because the service could not be opened with the available permissions. Therefore the configured local database could not be re-queried in this agent session.
- `MSSQLSERVER` was running and both direct and Laravel-configured SELECT-only reads succeeded under Windows Integrated Authentication.

This distinguishes configured/known working capability from the processes that happened to be running during the reconciliation. No service was stopped, no schema/data was changed, and no application route with side effects was invoked.

## Follow-up verification after operator startup

The later preflight check confirmed:

- MariaDB service `Running`; Laravel connected to `school_management_phase0` and returned SELECT-only counts.
- Laravel development server listening at `127.0.0.1:8000`; `/` and `/login` returned HTTP 200.
- The browser rendered the SchoolERP login form, including Username, Password, Remember me, Forgot Password, and Login controls.
- Vite listening on IPv6 loopback `[::1]:5173`; `/@vite/client` returned HTTP 200 JavaScript.
- Node 26.7.0 and npm 11.19.0 from the VS Code runtime installation.
- MariaDB server/client 12.3.2, PHP 8.3.31, Laravel 13.17.0, and Composer 2.10.2.

No form was submitted and no application/database state was changed.
