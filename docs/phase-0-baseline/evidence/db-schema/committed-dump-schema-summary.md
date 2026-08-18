# MariaDB baseline-source schema summary

`db/school_management.sql` is the designated current local MariaDB reconstruction baseline. The pre-cleanup file is historical only. This remains source-file evidence, not a fresh live-database schema query.

| Artifact | Bytes | CREATE TABLE statements | Tables with INSERT statements | Completion marker |
|---|---:|---:|---:|---|
| `db/school_management.sql` | 4,192,063 | 54 | 40 | 2026-08-16 20:50:14 |
| `db/school_management_before_final_subject_cleanup.sql` | 4,098,949 | 53 | 39 | 2026-08-16 09:11:20 |

The dumps identify MariaDB 12.3.2 as the exporting server artifact version. That is not evidence of the currently running MariaDB service version.

Schema inspection also found two repository migrations that each create `teacher_class_allocations`:

- `database/migrations/2026_07_05_043212_create_teacher_class_allocations_table.php`
- `database/migrations/xxxx_xx_xx_create_teacher_class_allocations_table.php`

No schema command, migration, or restore was run.
