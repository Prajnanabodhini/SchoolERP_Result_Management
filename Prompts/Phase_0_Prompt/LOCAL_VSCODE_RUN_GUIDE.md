# SchoolERP Result Management System
## Local VS Code Run Guide for Phase 0

**Purpose:** Run the current repository locally from VS Code for Phase 0 inspection and UI validation.

**Environment boundary:** The user-testing deployment is on a separate computer/system that is not connected to this working environment.

Therefore:
- Do not attempt to inspect, start, stop, hash, compare, or modify the remote deployed instance.
- Phase 0 UI inspection must run a local development instance from VS Code.
- `http://127.0.0.1:8000` means the temporary Laravel development server started from VS Code; it is not the production-testing deployment.

# 1. Correct MariaDB SQL file

Use `db/school_management.sql` as the current local MariaDB baseline.

Do not use `db/school_management_before_final_subject_cleanup.sql` except as a historical/pre-cleanup reference.

Evidence:
- pre-cleanup dump completed: 2026-08-16 09:11:20
- current dump completed: 2026-08-16 20:50:14
- the later Subject mapping commit modified only `db/school_management.sql`
- the current dump contains later Subject/Standard Wise Subject data absent from the pre-cleanup dump

Do not delete the older file.

# 2. Prerequisites

From a VS Code PowerShell terminal:

```powershell
php -v
composer --version
node -v
npm -v
mariadb --version
```

Required/recommended:
- PHP 8.3+
- Composer
- Node.js 20.19+ or 22.12+
- npm
- local MariaDB test instance
- VS Code

Check PHP extensions:

```powershell
php -m | findstr /I "pdo_mysql sqlsrv pdo_sqlsrv mbstring openssl fileinfo"
```

For full functionality, `sqlsrv` and `pdo_sqlsrv` are required for pages that query the authoritative School ERP MSSQL database.

# 3. Open the repository

VS Code -> File -> Open Folder -> select the repository root.

Then:

```powershell
git status
git rev-parse HEAD
```

# 4. Install PHP dependencies

```powershell
composer install
```

## Do not run

```powershell
composer setup
```

The repository's `composer setup` executes `php artisan migrate --force`. The current migration chain is not a reliable reconstruction of the database.

# 5. Install Node/Vite dependencies

```powershell
npm ci
```

If an engine error appears, check `node -v` and use Node 20.19+ or 22.12+.

# 6. Create an isolated local MariaDB database

Use a dedicated database such as:

`school_management_phase0`

Example:

```powershell
mariadb -u root -p -e "CREATE DATABASE school_management_phase0 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Never import into a production/shared DB.

# 7. Import the correct SQL dump

```powershell
cmd /c "mariadb -u root -p school_management_phase0 < db\school_management.sql"
```

If the executable is `mysql.exe`:

```powershell
cmd /c "mysql -u root -p school_management_phase0 < db\school_management.sql"
```

Verify with SELECT-only checks:

```sql
SHOW TABLES;
SELECT COUNT(*) FROM subjects;
SELECT COUNT(*) FROM standard_wise_subjects;
SELECT COUNT(*) FROM exam_masters;
SELECT COUNT(*) FROM teacher_subject_allocations;
SELECT COUNT(*) FROM student_marks;
SELECT COUNT(*) FROM student_results;
```

# 8. Create local `.env`

If `.env` does not exist:

```powershell
Copy-Item .env.example .env
```

Edit `.env`:

```dotenv
APP_NAME="SchoolERP Result Management"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=school_management_phase0
DB_USERNAME=root
DB_PASSWORD=YOUR_LOCAL_MARIADB_PASSWORD

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

MSSQL_HOST=
MSSQL_PORT=1433
MSSQL_DATABASE=
MSSQL_USERNAME=
MSSQL_PASSWORD=
```

Do not commit `.env`.

# 9. MSSQL connectivity

## Mode A - MSSQL reachable
Populate the MSSQL variables and preferably use a read-only SQL Server login for Phase 0.

## Mode B - MSSQL not reachable
The Laravel app can still start, but screens that directly query `SubStudentMst`, `FeeMstStudent`, `StandardMst`, or `DivisionMst` through `sqlsrv_olderp` may fail or be incomplete. Mark them:

`NOT VALIDATED LOCALLY - MSSQL CONNECTION UNAVAILABLE`

Do not fabricate production behavior.

# 10. Generate local key and clear caches

```powershell
php artisan key:generate
php artisan optimize:clear
```

# 11. Safe sanity checks

```powershell
php artisan about
php artisan route:list
php artisan migrate:status
```

`migrate:status` is observation only.

Do not run:

```powershell
php artisan migrate
php artisan migrate:fresh
php artisan migrate:refresh
php artisan db:seed
```

# 12. Start from VS Code

Use two VS Code terminals.

## Terminal 1 - Laravel

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

## Terminal 2 - Vite

```powershell
npm run dev
```

Open:

`http://127.0.0.1:8000`

This is a temporary local developer server only.

# 13. Optional single-command startup

The repo also defines:

```powershell
composer run dev
```

It starts Laravel, queue listener, Pail, and Vite. For Phase 0 the two-terminal method is preferred because it is easier to observe and troubleshoot.

# 14. Login

Use an existing known test account from the imported local SQL snapshot. Do not expose production credentials.

# 15. UI validation status

For each page record one of:
- PASS
- PARTIAL
- BLOCKED - MSSQL unavailable
- ERROR
- NOT TESTED

# 16. Safety

Do not use local UI inspection to normalize IDs, fix authentication, edit locked marks, generate replacement production results, run ERP sync, delete masters, run migrations, or modify the remote deployment.
