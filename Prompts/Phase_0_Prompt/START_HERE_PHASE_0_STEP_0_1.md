# START HERE — Phase 0 / Step 0.1

Read first:
1. `CURRENT_MAIN_REASSESSMENT.md`
2. `00_PHASE_0_MASTER.md`
3. `00_01_FREEZE_CURRENT_WORKING_STATE.md`

## Immediate goal

Freeze the exact source identity of the application version currently being tested by users without changing code or production data.

## Execute only Step 0.1

1. Query current repository/GitHub HEAD.
2. Capture recent Git history and changed-file delta.
3. Identify the deployed application root.
4. Determine deployed SHA if it is a Git worktree.
5. Otherwise create a read-only file-hash manifest.
6. Compare deployed code with repository HEAD.
7. Capture runtime/dependency fingerprints.
8. Do not create/push the final baseline tag until the identities are reconciled.
9. Produce:
   - `01-git-baseline.md`
   - `00-recent-commit-delta.md`
10. Stop for human review before Step 0.2.

## Observed reference during reassessment

`main` was:
`1780866c61c0b7cb0e7a9652735ccdc312d671ad`

Do not assume it is still current.

## Prohibited during Step 0.1

Do not:
- pull/checkout/reset/rebase the deployed server,
- overwrite deployed files,
- run migrations/seeders,
- invoke ERP sync,
- generate results,
- submit/edit marks,
- change/reset passwords,
- create/update/delete subjects,
- modify MSSQL.

If deployed code differs from GitHub, document it. Do not fix it.

## LOCAL-ONLY EXECUTION CORRECTION

The production-testing deployment is on a separate system that is not connected to this environment.

Therefore Phase 0 must NOT attempt to discover the deployed application folder, hash deployed files, compare local files directly with that server, start/stop the deployed service, or run commands on it.

For UI inspection, start the repository locally from VS Code using `LOCAL_VSCODE_RUN_GUIDE.md`.

`http://127.0.0.1:8000` is the local `php artisan serve` development server, not the remote deployment.

Use `db/school_management.sql` as the current MariaDB baseline. Keep `db/school_management_before_final_subject_cleanup.sql` only as a historical/pre-cleanup reference.

If MSSQL is not reachable from this machine, mark MSSQL-dependent flows as blocked/not locally validated rather than inventing behavior.
