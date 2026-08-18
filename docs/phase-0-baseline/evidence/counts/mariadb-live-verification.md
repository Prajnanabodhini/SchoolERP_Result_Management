# Local MariaDB live verification

Captured: 2026-08-18 after the operator started the local development stack.  
Environment: isolated local Phase 0 instance  
Database: `school_management_phase0`  
Method: Laravel-configured SELECT-only queries; no row content or PII captured.

## Server and schema

| Observation | Value |
|---|---:|
| MariaDB server version | 12.3.2-MariaDB |
| Base tables | 54 |
| Designated reconstruction source | `db/school_management.sql` |

## Source-to-live count comparison

Every checked live count matches the count parsed previously from the designated SQL source.

| Table | SQL source | Live database | Match |
|---|---:|---:|---|
| `academic_years` | 2 | 2 | YES |
| `sections` | 6 | 6 | YES |
| `standards` | 19 | 19 | YES |
| `divisions` | 2 | 2 | YES |
| `standard_year_mappings` | 52 | 52 | YES |
| `division_year_mappings` | 85 | 85 | YES |
| `subjects` | 76 | 76 | YES |
| `standard_wise_subjects` | 95 | 95 | YES |
| `exam_masters` | 10 | 10 | YES |
| `exam_master_subjects` | 63 | 63 | YES |
| `teacher_class_allocations` | 84 | 84 | YES |
| `teacher_subject_allocations` | 99 | 99 | YES |
| `teacher_marks_status` | 100 | 100 | YES |
| `student_marks` | 3,037 | 3,037 | YES |
| `student_results` | 319 | 319 | YES |
| `student_result_details` | 1,045 | 1,045 | YES |
| `mark_audit_logs` | 0 | 0 | YES |
| `users` | 38 | 38 | YES |

This count match strongly supports that the configured local database was reconstructed from the designated source. It is not a byte-for-byte or row-value checksum.

## Credential-format classification

Without recording or displaying any password value: 38 users have non-empty password fields, 0 values match recognized bcrypt/Argon adaptive-hash prefixes, and 38 are classified as non-adaptive format. This confirms the earlier source-artifact finding against the isolated local database. No authentication credential was changed or tested.

## Migration observation

`php artisan migrate:status --no-interaction` was read-only. It reported `2026_08_08_003536_create_exam_master_subjects_table` as `Pending`, while the imported database already contains `exam_master_subjects` with 63 rows. The placeholder `xxxx_xx_xx_create_teacher_class_allocations_table` migration is recorded as ran. This is import/migration-history drift; no migration was executed.

## Current-main Subject-ID classification

Reassessed: 2026-08-18T11:07:22+05:30  
Repository SHA: `1780866c61c0b7cb0e7a9652735ccdc312d671ad`

Each row was evaluated in its Standard context. A canonical match means the stored value resolves through `standard_wise_subjects.subject_id`; a legacy match means it resolves through `standard_wise_subjects.id`. For teacher allocations, the Standard came from `teacher_class_allocations`; for result details, it came from `student_results`. Only aggregate counts were returned.

| Table | Total | Canonical only | Legacy only | Both valid | Neither valid |
|---|---:|---:|---:|---:|---:|
| `exam_master_subjects` | 63 | 49 | 14 | 0 | 0 |
| `teacher_subject_allocations` | 99 | 46 | 53 | 0 | 0 |
| `teacher_marks_status` | 100 | 71 | 29 | 0 | 0 |
| `student_marks` | 3,037 | 1,897 | 1,140 | 0 | 0 |
| `student_result_details` | 1,045 | 394 | 651 | 0 | 0 |

All 95 `standard_wise_subjects.subject_id` values resolve to a current `subjects.id`; zero invalid mapping references were found. These live results exactly match the committed-dump classification. No row contents, names, credentials, or PII were selected or recorded.
