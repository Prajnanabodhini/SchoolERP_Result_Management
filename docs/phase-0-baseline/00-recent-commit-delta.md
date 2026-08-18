# Phase 0 — Recent Commit Delta

## Current-main reassessment execution — 2026-08-18

At `2026-08-18T11:07:22+05:30`, local HEAD and a fresh GitHub `refs/heads/main` query again matched at `1780866c61c0b7cb0e7a9652735ccdc312d671ad`; the Git tree is `0ce8b41c8a6e3b55974cfbe3275a9213561175fd`, and local `main` is 0 ahead / 0 behind `origin/main`. Tracked worktree and index are clean; the prompt pack, Phase 0 evidence, and pre-existing shortcut remain untracked. No tag points at HEAD.

The reassessment claims were rechecked against current source. The existing 171-route inventory exactly matches a fresh route-list capture after ignoring its historical metadata header (159 routes with `auth`, 12 without). Live SELECT-only MariaDB classification also exactly matches the earlier dump-derived Subject-ID counts, now proving the isolated local database contains 2,457 canonical-only and 1,887 legacy-only rows across the five transitional subject-reference tables, with zero both-valid and zero neither-valid rows. The remote deployment remains `NOT CONNECTED / NOT INSPECTABLE FROM THIS ENVIRONMENT`; no deployed comparison was inferred.

## New Step 0.1 execution freeze — 2026-08-18

At `2026-08-18T10:51:49.4603703+05:30`, local HEAD, local `origin/main`, and a fresh GitHub `refs/heads/main` query all returned `1780866c61c0b7cb0e7a9652735ccdc312d671ad`. The four-commit sequence and all changed-path/statistics evidence below were rechecked and remain unchanged. No tracked or staged modification exists; current untracked files are the authoritative prompt pack, preserved Phase 0 documentation/evidence, and the pre-existing shortcut.

**Capture time:** 2026-08-17T22:01:59.7907393+05:30  
**Compared branch:** `main`  
**Live GitHub `main`:** `1780866c61c0b7cb0e7a9652735ccdc312d671ad`

## Decision

**Observed fact:** the live GitHub `main` SHA matches the locally checked-out SHA and the SHA observed by `CURRENT_MAIN_REASSESSMENT.md`.

**Inference:** the reassessment's expanded evidence scope remains applicable. The source delta reinforces the need to baseline canonical and historical Subject-ID behavior separately. It does not justify any normalization or redesign during Phase 0.

## Commit sequence and size

| Commit | Timestamp/message | Changed-file summary | Observed scope |
|---|---|---|---|
| `1780866c61c0b7cb0e7a9652735ccdc312d671ad` | 2026-08-17 17:51:35 +0530 — `Latest Code` | 9 files; 2,488 insertions, 1,399 deletions | Admin marks, result generation, Subject workflow, `TeacherSubjectAllocation`, and related views |
| `3d67900a84cbc878d8c6d307978702ddc69d0aea` | `subject id and subject name mapping between subjects and standard_wise_subjects table` | 1 file; 1,427 insertions, 346 deletions | `db/school_management.sql` only |
| `7353d10bc415607635750075277a101678d1fe86` | `Standard ID mapping issue solved` | 19 files; 52,276 insertions, 13,430 deletions | Controllers/models/routes/views, main SQL dump, and addition of the pre-cleanup SQL dump |
| `aa18d1736412f7edb2bc1fa233889fe35dd3e8fd` | `Initial Commit` | repository import | Initial recorded repository state |

Large line counts in `7353d10` and `1780866` include broad formatting/content churn and SQL dump changes. The counts show review surface, not the number of independent behavioral changes.

## Exact changed paths after the initial commit

### `7353d10` — Standard ID mapping issue solved

```text
M app/Http/Controllers/Administrator/AdminMarksController.php
M app/Http/Controllers/Administrator/ResultGenerationController.php
M app/Http/Controllers/Administrator/ResultSheetController.php
M app/Http/Controllers/ExamMasterController.php
M app/Http/Controllers/ExamProgressController.php
M app/Http/Controllers/Marks/MarkEntryController.php
M app/Http/Controllers/TeacherBulkAllocationController.php
M app/Models/TeacherMarksStatus.php
M app/Models/TeacherSubjectAllocation.php
M db/school_management.sql
A db/school_management_before_final_subject_cleanup.sql
M resources/views/administrator/exam-progress/index.blade.php
M resources/views/administrator/marks/edit.blade.php
M resources/views/administrator/result-sheet/index.blade.php
M resources/views/administrator/result-sheet/print.blade.php
M resources/views/administrator/teacher-bulk-allocation/create.blade.php
M resources/views/administrator/teacher-bulk-allocation/index.blade.php
M resources/views/marks-entry/index.blade.php
M routes/web.php
```

### `3d67900` — subject ID/name mapping dump update

```text
M db/school_management.sql
```

### `1780866` — Latest Code

```text
M app/Http/Controllers/Administrator/AdminMarksController.php
M app/Http/Controllers/Administrator/ResultGenerationController.php
M app/Http/Controllers/SubjectController.php
M app/Models/TeacherSubjectAllocation.php
M resources/views/administrator/result-generation/index.blade.php
M resources/views/marks-entry/index.blade.php
M resources/views/subjects/create.blade.php
M resources/views/subjects/edit.blade.php
M resources/views/subjects/index.blade.php
```

## Subject-ID-relevant observations at current HEAD

| Observation | Classification | Current source evidence | Phase 0 consequence |
|---|---|---|---|
| Add Subject creates a `subjects` row, then creates a `standard_wise_subjects` row whose `subject_id` is the new `subjects.id` | Observed fact | `app/Http/Controllers/SubjectController.php:251`, `:292`, `:296` | Characterize this current divergence from the frozen reusable-master target; do not change it |
| Result generation states that `student_marks.subject_id` must be `subjects.id`, verifies mappings through `standard_wise_subjects`, and writes the mark's canonical Subject ID to result detail | Observed fact | `app/Http/Controllers/Administrator/ResultGenerationController.php:205`, `:277-291`, `:828-842` | Baseline current canonical writes and validate historical rows separately |
| Admin marks resolution accepts a stored canonical Subject ID or a historical `standard_wise_subjects.id` | Observed fact | `app/Http/Controllers/Administrator/AdminMarksController.php:33-38`, `:66-135` | Preserve and count both representations; do not invoke write/repair paths during Phase 0 |
| `TeacherSubjectAllocation` exposes relationships to both `Subject` and `StandardWiseSubject` using the same `subject_id` foreign-key value | Observed fact | `app/Models/TeacherSubjectAllocation.php:18-30`, `:59-64` | Dashboard/relationship display requires a regression baseline |
| Both committed SQL dumps changed or were introduced in the recent sequence | Observed fact | commit path lists and SHA-256 evidence | Treat dumps as artifacts, not proof of live database state |

## Delta interpretation

- **Observed fact:** `7353d10` touched the broad standard/subject flow across allocation, marks, results, views, routes, models, and database artifacts.
- **Observed fact:** `3d67900` changed only the main SQL dump; it did not change runtime PHP code.
- **Observed fact:** `1780866` further changed Subject creation/editing, admin marks, result generation, subject relationships, and their UI surfaces.
- **Inference:** a regression baseline taken before these commits is insufficient for the current `main` branch.
- **Authoritative environment boundary (2026-08-18 correction):** the production-testing deployment is on a separate system that is not connected or inspectable from this environment. No local IIS result can identify or compare that deployment.
- **Future target:** `subjects` is a reusable global Subject Master and `standard_wise_subjects` is a per-standard mapping. This target was not implemented or altered in Step 0.1.

## Required follow-through in later Phase 0 steps

For the local-only Phase 0 baseline, later steps must capture:

1. the full subject-ID contract matrix and safe canonical-vs-historical row counts;
2. current Subject Master create/edit/delete behavior without executing destructive production actions;
3. a dashboard pending-subject display case;
4. authentication-path and route-exposure contracts;
5. local-to-MSSQL translation evidence;
6. numerical, functional, and visual outputs for the changed flows.

No row was normalized, no state-changing route was invoked, and no application behavior was changed while producing this report.

## Evidence commands

```text
git ls-remote origin refs/heads/main
git log --oneline --decorate -20
git log --name-status -10
git show --stat --oneline --summary <commit>
git diff-tree --no-commit-id --name-status -r <commit>
git diff 3d67900 1780866 -- <subject-relevant source paths>
rg -n "subject_id|standard_wise_subjects|StandardWiseSubject|Subject::create" <subject-relevant source paths>
```
