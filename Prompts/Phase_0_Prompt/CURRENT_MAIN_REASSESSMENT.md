# SchoolERP Result Management System
## Current `main` Reassessment Before Phase 0

**Repository:** `Prajnanabodhini/SchoolERP_Result_Management`  
**Reassessment date:** 2026-08-17  
**Observed GitHub `main` HEAD during reassessment:**  
`1780866c61c0b7cb0e7a9652735ccdc312d671ad` — `Latest Code`

> The SHA above is an observed reference only. The Phase 0 executor must query Git again immediately before freezing the baseline. If `main` has moved, use the new HEAD and record the difference.

---

## 1. Decision

The **frozen target architecture does not change** and the **Phase 0 step sequence does not change**.

Phase 0 remains:

1. Freeze current working state.
2. Back up application/databases/configuration.
3. Document current end-to-end flows.
4. Establish representative baseline test cases.
5. Capture numerical, functional, and visual outputs.

What changes is the **evidence scope**, because recent commits materially changed Standard/Subject ID handling.

---

## 2. Recent commit sequence observed

1. `1780866c61c0b7cb0e7a9652735ccdc312d671ad` — `Latest Code`
2. `3d67900a84cbc878d8c6d307978702ddc69d0aea` — `subject id and subject name mapping between subjects and standard_wise_subjects table`
3. `7353d10bc415607635750075277a101678d1fe86` — `Standard ID mapping issue solved`
4. `aa18d1736412f7edb2bc1fa233889fe35dd3e8fd` — `Initial Commit`

Re-query this at execution time.

---

## 3. Updated current-state findings that Phase 0 must capture

### 3.1 Current/new subject writes are increasingly canonical

Important active modules now increasingly use:

`subject_id = subjects.id`

for new/current writes.

Current Result Generation explicitly validates `student_marks.subject_id` as `subjects.id`, verifies the Subject is mapped to the selected Standard through `standard_wise_subjects`, and writes the same canonical Subject Master ID into `student_result_details`.

The older audit finding that current Result Generation treats `student_marks.subject_id` as `standard_wise_subjects.id` is therefore superseded.

### 3.2 Historical compatibility still exists

Several modules still accept or resolve older rows where a subject-related column may contain:

`standard_wise_subjects.id`

instead of:

`subjects.id`

Do not normalize these rows during Phase 0. Count/classify them safely.

### 3.3 Transitional Eloquent relationships remain

`TeacherSubjectAllocation.subject_id` is currently exposed through relationships to both `Subject` and `StandardWiseSubject`.

The Dashboard uses the Standard Wise Subject relationship even though current bulk-allocation writes use canonical `subjects.id`.

Capture a dashboard pending-subject baseline case.

### 3.4 Subject Master target meaning remains unchanged

Frozen target:

- `subjects` = reusable/global Subject Master.
- `standard_wise_subjects` = allocation/mapping of an existing Subject Master subject to a specific Standard/Class.

Current Add Subject behavior still creates:
1. a new `subjects` row, and
2. a new `standard_wise_subjects` row

together for one selected Standard.

That is a current-state divergence to document, not fix, in Phase 0.

### 3.5 MSSQL target ownership remains unchanged

Frozen target MSSQL authority remains:
- Academic Year
- Section
- Standard
- Division
- Student
- GR and required School ERP data

Current code is mixed:
- Year/Section selection reads MSSQL.
- student records are fetched from MSSQL in active flows.
- local Academic Year/Standard/Division IDs and mapping tables are still used to reach MSSQL.
- Exam/Allocation/Result screens still depend heavily on local academic masters.

Phase 0 must document this translation chain.

### 3.6 Authentication current state changed partially

- Public `/register` routes are currently absent.
- Admin-created/updated passwords are still stored directly.
- custom login still compares submitted password directly with the stored value.
- Change Password and Forgot/Reset Password paths use Laravel hashing.

Do not alter production credentials to test this.

### 3.7 Alternate Marks Edit remains unsafe

The alternate marks-edit path still lacks the ownership/lock/server-side bounds protections of the primary save/submit path.

Do not exploit it against production.

### 3.8 Authorization remains largely route-level `auth`

Most administrative/business routes remain protected only by generic authentication rather than hard role/permission middleware.

### 3.9 Route surface to baseline

Explicitly classify:
- public `/teacher-bulk-allocation/exam-details`
- public `/test-page`
- authenticated state-changing GET `/erp-student-sync/{year}`
- authenticated state-changing GET `/erp-sync/students/{year}`
- alternate marks edit/update routes
- all Administrator-intended routes

Also record stale route candidates:
- `StudentController::getDivisions()`
- `ExamMasterController::getSubjects()`

### 3.10 Migration/test drift remains

- conflicting `teacher_class_allocations` create migrations remain.
- auth feature tests still post `email` while actual login expects `name`.

Do not fix in Phase 0.

---

## 4. Phase 0 additions

Add:
- recent-commit delta report,
- deployed-vs-GitHub source reconciliation,
- subject-ID contract matrix,
- canonical-vs-historical row classification,
- current Subject Master workflow characterization,
- dashboard subject-display regression evidence,
- authentication-path contract,
- public/stale/state-changing route inventory,
- SQL-dump fingerprints,
- local-to-MSSQL mapping evidence.

Do not redesign or normalize anything during Phase 0.
