# Subject-ID contract matrix

The code currently accepts two historical meanings in several `subject_id` columns. The classification below was re-run against the live isolated MariaDB database `school_management_phase0` on 2026-08-18 using SELECT-only, standard-context-aware queries. The results exactly match the earlier classification from `db/school_management.sql`, the designated reconstruction source. No row was normalized.

| Table / model | Column | Current new-write meaning | Historical alternate | Live classification | Evidence |
|---|---|---|---|---:|---|
| `subjects` | `id` | Canonical Subject Master | N/A | 76 canonical rows | Subject model/migration/dump |
| `standard_wise_subjects` | `id` | Standard-subject mapping ID | N/A | 95 mapping rows | Mapping model/migration/dump |
| `standard_wise_subjects` | `subject_id` | `subjects.id` | N/A | 95 valid canonical references | Mapping model/dump |
| `exam_master_subjects` | `subject_id` | `subjects.id` | Mapping ID | 49 canonical-only; 14 legacy-only | Exam Master write/read code and live context classification |
| `teacher_subject_allocations` | `subject_id` | `subjects.id` | Mapping ID | 46 canonical-only; 53 legacy-only | Bulk allocation controller, compatibility relations, live context classification |
| `teacher_marks_status` | `subject_id` | `subjects.id` | Mapping ID | 71 canonical-only; 29 legacy-only | Allocation/submit controllers and live context classification |
| `student_marks` | `subject_id` | `subjects.id` | Mapping ID | 1,897 canonical-only; 1,140 legacy-only | Primary mark save/submit compatibility logic and live context classification |
| `student_result_details` | `subject_id` | `subjects.id` | Mapping ID | 394 canonical-only; 651 legacy-only | Result generation and live context classification |

The live classifier found zero rows valid in both namespaces and zero rows valid in neither namespace. All 95 `standard_wise_subjects.subject_id` values resolve to `subjects.id`. Full method and results are preserved in `evidence/counts/mariadb-live-verification.md`.

## Current-main reassessment verification — 2026-08-18

- Local HEAD and a fresh GitHub `main` query match at `1780866c61c0b7cb0e7a9652735ccdc312d671ad`.
- Current Result Generation validates canonical Subject Master IDs, verifies the selected Standard mapping, and writes the canonical ID to result details.
- Admin Marks, Mark Entry, Exam Master, and bulk-allocation paths retain compatibility resolution for historical mapping IDs.
- `TeacherSubjectAllocation` still exposes both Subject Master and Standard Wise Subject relationships over the same stored column.
- No repair, normalization, result generation, marks action, or other write was executed.

## Current versus frozen target workflow

- **Frozen target:** Subject Master is reusable; Standard Wise Subject Master maps a canonical subject to a standard.
- **Current observed Add Subject:** one request creates a new Subject Master and one Standard Wise mapping together. Update edits both; delete removes a mapping and may deactivate the master if unused.
- **Current new allocation writes:** validate a canonical subject plus its standard mapping, then store `subjects.id` in allocation/status tables.
- **Historical compatibility:** exam, allocation, marks, and result paths contain resolution logic for mapping IDs still stored in `subject_id`.

## Dashboard characterization

The dashboard-facing `standardWiseSubject` relation joins `teacher_subject_allocations.subject_id` to `standard_wise_subjects.id`. New allocations store `subjects.id`, so a numerically matching but unrelated mapping can produce the wrong label. Artifact case `DASH-SUBJ-001` demonstrates the collision candidate: pending allocation/status row 47 stores canonical subject ID 2, while mapping ID 2 identifies a different subject. Live UI confirmation is pending.
