# Authentication path contract

## Current-main reassessment — 2026-08-18

Fresh source and route review at SHA `1780866c61c0b7cb0e7a9652735ccdc312d671ad` confirms the contract below remains current: no public registration route; login requires `name`, directly compares the submitted and stored password, and does not check `is_active`; admin create/update directly assigns submitted passwords; change and reset paths hash. The auth feature test still submits `email`. No credential value was read, printed, submitted, or changed.

## Local reconciliation status — 2026-08-18

The operator confirms that local admin authentication works against the isolated Phase 0 environment. The effective local configuration uses `APP_ENV=local`, `APP_URL=http://127.0.0.1:8000`, MariaDB database `school_management_phase0`, and database-backed sessions. The agent verified that the local login page renders successfully through the database-backed session middleware; no credential was entered and authenticated journey capture remains for the new Phase 0 run. The code-level contract below remains valid.

| Path | Observed current behavior | Evidence / limitation |
|---|---|---|
| Admin Create User | Authenticated admin-intended controller assigns submitted password directly | `app/Http/Controllers/UserController.php`; no live user created |
| Admin Update User | Assigns submitted password directly when provided; `Hash` import is unused there | Same controller; no live password changed |
| Login | Form/request contract uses `name`, not email; looks up user by name and directly compares stored/submitted values; rate limit 5; `Auth::login`; session regeneration | `app/Http/Requests/Auth/LoginRequest.php`; operator confirms local login works; unauthenticated login UI verified |
| Active/inactive behavior | Login path contains no `is_active` check | Observed source; inference: inactive account can authenticate if credentials match; live confirmation pending |
| Change Password | Validates current password and writes `Hash::make` output | Authenticated password controller/path; not invoked |
| Forgot/Reset Password | Laravel password broker; reset writes `Hash::make` output | Auth routes/controllers; not invoked |
| Public registration | No public registration route | `routes/auth.php` and route inventory; unused view/controller artifacts exist |
| Logout | `Auth::logout`, session invalidation, CSRF-token regeneration | Authenticated session controller; not invoked |

## Contract inconsistency

Admin create/update and login use directly comparable values, while change/reset uses adaptive hashing. Existing automated login tests submit `email`, but the current request expects `name`. These are baseline facts only; no credential was tested, printed, reset, migrated, or repaired.

## Authorization boundary

Administrator-intended routes use generic `web,auth` middleware in the generated route list. Role permissions appear to control navigation visibility. This does not demonstrate server-side authorization enforcement for direct requests.
