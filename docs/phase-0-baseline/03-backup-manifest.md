# Backup and recovery manifest

Initial evidence captured: 2026-08-17T22:21:12+05:30.  
Step 0.2 backup set captured: 2026-08-18T11:17:53+05:30.

## Backup set identity and protection

**Backup set:** `SchoolERP-Phase0-2026-08-18`  
**Environment:** isolated local Phase 0 workstation  
**Source SHA:** `1780866c61c0b7cb0e7a9652735ccdc312d671ad`  
**Remote deployed source identity:** `NOT CONNECTED / NOT INSPECTABLE FROM THIS ENVIRONMENT`  
**Retention owner:** `DESKTOP-OOB65OU\admin`  
**Protected location:** `D:\School ERP projects\SchoolERP Result Management Phase0 Backups\SchoolERP-Phase0-2026-08-18`

The backup directory is outside the Git repository and is not web-accessible through the local Laravel document root. NTFS inheritance was removed; only the local operator and `SYSTEM` have full-control access. The location and ACL were created specifically for this Phase 0 backup set.

## Backup artifact manifest

| Artifact | Timestamp | Protected location / identity | Size | SHA-256 | Tool | Validation | Owner |
|---|---|---|---:|---|---|---|---|
| Repository all-refs bundle | 2026-08-18T11:14:52+05:30 | `SchoolERP-source-all-refs.bundle` | 1,630,360 | `7ce428161f2105c46d89512290a5ab12cd62ef466dc28a467b3e3cc0f9e9b486` | `git bundle create --all` | VERIFIED: `git bundle verify`; complete history; `main`/HEAD at frozen SHA | local operator |
| Local secret configuration | source mtime 2026-08-18T09:15:04+05:30 | `SchoolERP-local.env.backup` | 618 | `ff8431ad2707ff2b86d501fdfa4e0ef34e5d1274e2f06cf906cfa95af2c83f74` | protected file copy | VERIFIED: hash exactly matches local `.env`; contents never printed | local operator |
| Phase 0 evidence snapshot | 2026-08-18T11:14:54+05:30 | `SchoolERP-phase0-evidence.zip` | 167,327 | `e41f266f9edb24ed3b0f9e56bc8e93b06b10f80510fc6d60a6f64017791e50df` | PowerShell `Compress-Archive` | VERIFIED readable: 44 archive entries | local operator |
| MariaDB consistent dump | 2026-08-18T11:15:38+05:30 | `school_management_phase0-validated.sql` | 4,197,806 | `3e6c01ee23ebd1448c1778abde6da18f07fafb9db802edcf93c986d1f61eeb31` | MariaDB 12.3 `mariadb-dump --single-transaction --quick --routines --events --triggers --hex-blob` | VERIFIED STRUCTURALLY: command exit 0; 54 `CREATE TABLE`; 40 `INSERT INTO`; expected transition tables present; normal completion footer | local operator |

An earlier dump wrapper attempt produced `school_management_phase0.sql` (4,197,806 bytes; SHA-256 `6ea3d8d45f2fbd4d4f13fda9e9e11eeb0f00b2af133ffdf90970de97158bea7d`) but failed before the wrapper confirmed the subprocess exit status. It is preserved in the secured directory as **UNVERIFIED / NOT A RECOVERY CANDIDATE**; it is not the validated backup listed above.

## Restore readiness

| Asset | Status | Evidence / recovery statement |
|---|---|---|
| Repository source | VERIFIED | Restore/fetch from the complete Git bundle and check out frozen SHA `1780866c61c0b7cb0e7a9652735ccdc312d671ad`; GitHub `main` is an additional source. |
| Remote deployed application files | PENDING — NOT ACCESSIBLE | Separate disconnected system by authoritative environment definition. No remote copy or server inspection was attempted. Its operator must create and verify the deployed-folder backup. |
| Local MariaDB `school_management_phase0` | VERIFIED STRUCTURALLY | New consistent dump completed with exit 0, is non-zero/readable, has all 54 table definitions, expected transition tables, and a normal footer. No isolated restore was performed. |
| MSSQL `Shirgaon SchoolERP` | PENDING — OPERATOR/DBA CONFIRMATION REQUIRED | SELECT-only backup-history query returned no full or other backup record in `msdb`; recovery model is `SIMPLE`. The Windows principal reports `BACKUP DATABASE` permission, but no server-side destination, retention owner, authorization, or restore-test evidence is known. No backup was attempted. |
| Local `.env` | VERIFIED | Exact protected off-repository copy exists and hash matches. |
| Local VS Code runtime configuration | PARTIAL / VERIFIED FOR LOCAL METHOD | Local listeners are PHP on `127.0.0.1:8000` and Node/Vite on `[::1]:5173`; sanitized runtime configuration is documented. No app-specific Windows scheduled task/service or Laravel schedule declaration was found. External scheduler configuration remains unknown. |
| Remote web-server configuration | PENDING — NOT ACCESSIBLE | The remote deployment is disconnected. Local `php artisan serve` + Vite is not evidence of its web-server configuration. |

## Step 0.2 acceptance review

- [x] Baseline source is recoverable from a verified Git bundle.
- [ ] Remote deployed application copy is recoverable; remote operator action required.
- [x] `.env` is securely backed up outside Git with restricted ACLs.
- [x] MariaDB backup exists and passed command and structural validation.
- [ ] MSSQL backup owner/process/reference is known; operator/DBA confirmation required.
- [ ] Remote web-server deployment configuration is captured; remote operator action required.
- [x] Checksums are recorded.
- [x] No backup containing secrets or student data was written inside the repository or web root.
- [x] No restore was performed over any working database.
- [x] No database data or schema was changed.
- [x] Local backup owner and retention location are known.

**Step 0.2 status:** `BLOCKED AT STOP CONDITION — MSSQL BACKUP PROCESS/OWNER UNKNOWN`. The local source, `.env`, evidence, and MariaDB backup objectives are satisfied. Per the step prompt, execution must pause before Step 0.3 until the operator identifies the authoritative MSSQL backup process/owner or explicitly confirms how this pending item is governed.

## Authoritative source selection retained

- `db/school_management.sql` remains the designated current local MariaDB reconstruction baseline.
- `db/school_management_before_final_subject_cleanup.sql` remains historical/pre-cleanup evidence only.
- The fresh dump preserves the live transition-state data without normalization.

## Existing repository artifacts

The first file is the authoritative prompt-selected source for reconstructing the local Phase 0 MariaDB baseline. It is still not proof of the current contents of a running imported database. The second file is historical only. Neither replaces operator-controlled backup and restore verification.

| File | Size | SHA-256 | Structural observation |
|---|---:|---|---|
| `db/school_management.sql` | 4,192,063 bytes | `f143b909fd9df56a6cf0c08461bf79b3f9023426167ac8f85bb1ddb9a6494ceb` | Complete dump footer; 54 `CREATE TABLE`; 40 tables with `INSERT`; dump timestamp 2026-08-16 20:50:14 |
| `db/school_management_before_final_subject_cleanup.sql` | 4,098,949 bytes | `699f6cb819c360595f64c2fc7673a9b4ed2aa00e6d7ac4673866e62342075257` | Complete dump footer; 53 `CREATE TABLE`; 39 tables with `INSERT`; dump timestamp 2026-08-16 09:11:20 |

Structural validation means the artifacts were readable, had expected SQL structure, and ended normally. No restore was attempted because the destination identity and non-production safety were not established.

## Restore prerequisites

Before full system recovery readiness can be claimed, obtain the DBA/operator-authoritative MSSQL backup reference and the remote operator's deployed-folder/web-server backup evidence. The local Git bundle, `.env` escrow, and structurally validated MariaDB dump now exist. A future restore rehearsal must target an explicitly isolated non-production database; no restore was attempted during this step.

## Safety finding

The committed dumps contain user and ERP/student data. The current dump contains 38 non-empty values in the users password field; none matched the recognized adaptive-hash formats used by the inspection. Values, identities, and student data are intentionally omitted here. Treat the dumps as sensitive exposed artifacts and involve the repository owner/security lead before any history-rewrite, rotation, or removal action. Phase 0 made no such change.
