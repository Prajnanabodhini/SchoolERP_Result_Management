# Current local baseline-source counts

Parsed read-only from `db/school_management.sql` (SHA-256 `f143b909fd9df56a6cf0c08461bf79b3f9023426167ac8f85bb1ddb9a6494ceb`), the authoritative prompt-selected local MariaDB reconstruction baseline. These are SQL-source counts; live comparison is recorded separately.

| Table | Rows |
|---|---:|
| `academic_years` | 2 |
| `sections` | 6 |
| `standards` | 19 |
| `divisions` | 2 |
| `standard_year_mappings` | 52 |
| `division_year_mappings` | 85 |
| `subjects` | 76 |
| `standard_wise_subjects` | 95 |
| `exam_masters` | 10 |
| `exam_master_subjects` | 63 |
| `teacher_class_allocations` | 84 |
| `teacher_subject_allocations` | 99 |
| `teacher_marks_status` | 100 |
| `student_marks` | 3,037 |
| `student_results` | 319 |
| `student_result_details` | 1,045 |
| `mark_audit_logs` | 0 |
| `users` | 38 |

## Marks/result edge counts

| Classification | Rows |
|---|---:|
| Student marks absent | 113 |
| Student marks locked | 2,694 |
| Non-absent theory zero | 39 |
| Non-absent theory exactly at pass mark | 195 |
| Non-absent theory one below pass | 135 |
| Non-absent theory at maximum | 212 |
| Results PASS | 202 |
| Results FAIL | 117 |
| Results ranked | 202 |
| Result details absent | 34 |
| Result details exactly at pass | 60 |
| Result details one below pass | 53 |

PII-bearing row content was not copied into this evidence package.
