# Phase 0 open items and decision

## Current reconciled status — 2026-08-18

**Phase 0 status: BLOCKED AT STEP 0.2 — MSSQL BACKUP PROCESS/OWNER REQUIRES OPERATOR OR DBA CONFIRMATION.**

The previous `BLOCKED` conclusion was based partly on attempting to discover a remote deployment through local IIS. The authoritative prompt pack now defines that deployment as a separate, disconnected system and explicitly says this boundary is not a Phase 0 failure. Valid static, route, SQL-source, subject-contract, authentication-contract, and data-source findings remain preserved.

### Current remaining work

1. Obtain the DBA/operator MSSQL backup owner, process, reference, retention location, and last restore/verification status. SELECT-only `msdb` inspection found no full or other backup history for `Shirgaon SchoolERP`; no MSSQL backup was attempted.
2. Obtain the disconnected remote operator's deployed-folder and web-server configuration backup evidence.
3. After the Step 0.2 stop condition is resolved or explicitly governed by the operator, capture authenticated local screens using an existing test account without recording credentials or changing passwords/data.
4. Select namespace-explicit academic and `ERP-MAP-001` contexts using PII-minimized SELECT-only evidence.
5. Identify rank ties/grade boundaries and capture redacted result sheet/register/report-card outputs from the isolated local baseline without regenerating results.
6. Record and preserve the pending `exam_master_subjects` migration/import drift; do not run migrations to repair it during Phase 0.
7. Address the committed sensitive SQL artifacts through a controlled security decision; do not delete or rewrite history casually.

### Step 0.2 local work completed

- A restricted off-repository backup set now contains a verified all-refs Git bundle, exact `.env` escrow, readable Phase 0 evidence archive, and a consistent MariaDB dump with exit-status and structural validation.
- The MariaDB dump contains 54 table definitions and preserves the current canonical/historical Subject-ID transition state without normalization.
- No restore was attempted. A future restore rehearsal must use an explicitly isolated non-production target.

Remote deployed-system identity, backup, and parity evidence must be collected on that separate system by an authorized operator. It is not a local discovery task.

## Prior decision — superseded by the local-only correction

**Phase 0 status: BLOCKED** as of 2026-08-17T22:21:12+05:30.

The repository/static baseline is strong enough for code review, but Phase 0 cannot claim a restorable or live regression baseline without deployment identity, current database backups, and authenticated output evidence.

## Prior blocking items — retained as historical evidence

1. Operator must identify the actual IIS site/application URL and physical deployment root, then reconcile its manifest/hash with Git SHA `1780866c61c0b7cb0e7a9652735ccdc312d671ad`.
2. Operator/DBA must create and verify a current MariaDB backup to approved secure storage.
3. DBA must confirm the authoritative MSSQL backup/recovery point and process; Phase 0 must remain read-only to MSSQL.
4. Operator must securely escrow `.env` and IIS/site configuration outside Git and verify recovery access.
5. A restore rehearsal must be run only in an isolated non-production environment.
6. Provide an approved live/test URL and non-production test accounts or sanitized operator screenshots/exports for the journeys and visual outputs.
7. Reconcile artifact-derived counts and test contexts against the designated reference database using SELECT-only queries.
8. Treat committed dumps as a security incident candidate: restrict access, assess exposure, determine notification/rotation/removal strategy, and preserve evidence. Do not casually rewrite history.

## Prior open characterization work — retained as historical evidence

- Capture Node/npm/build versions from the actual deployment/build host.
- Capture MariaDB and MSSQL server versions from approved read-only connections.
- Identify explicit rank ties and every grade boundary in a sanitized reference copy.
- Capture representative result sheet, register, and report card renderings with PII redacted.

## Completed without behavior change

- Git and recent-commit baseline; hashes for relevant source/config/lock/dump files.
- Runtime/config facts available without secrets.
- Full 171-route inventory and exposure classification.
- Current data-source, session, authentication, authorization, subject-ID, marks, result, and reporting contracts.
- Committed-dump structural/count/edge-case characterization.
- Historical local IIS screenshot, now classified as out-of-scope for deployment comparison.

No application PHP, Blade, JavaScript, CSS, configuration, migration, schema, or data was changed. No state-changing route was invoked and no tag was created.
