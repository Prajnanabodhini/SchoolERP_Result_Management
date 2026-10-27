<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

use App\Models\ExamMaster;
use App\Models\Standard;
use App\Models\Division;
use App\Models\AcademicYear;
use App\Helpers\StudentHelper;

class ExamProgressController extends Controller
{
    private function isAdministrator(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        if (method_exists($user, 'hasRole')) {
            if ($user->hasRole('Administrator') || $user->hasRole('admin')) {
                return true;
            }
        }

        $role = strtolower(trim((string) ($user->role ?? '')));

        return in_array($role, ['administrator', 'admin'], true);
    }

    /**
     * Skill subjects that are subset-allocated: only a portion of the class
     * is enrolled (e.g. 10 of 40 students in Tabla). For these we mark the
     * allocation COMPLETED as soon as at least one student has been graded,
     * and we skip the costly total-students ERP lookup.
     */
    private function isSubsetAllocatedSkillSubject(?string $subjectName): bool
    {
        $name = strtoupper(trim((string) $subjectName));
        if ($name === '') return false;

        foreach (['MUSIC', 'DANCE', 'ACTING', 'TABLA'] as $needle) {
            if (str_contains($name, $needle)) return true;
        }

        return false;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) abort(403);

        $userId          = (int) $user->id;
        $isAdministrator = $this->isAdministrator();

        $examId         = trim((string) $request->input('exam_master_id', ''));
        $standardId     = trim((string) $request->input('standard_id', ''));
        $divisionId     = trim((string) $request->input('division_id', ''));
        $academicYearId = trim((string) $request->input('academic_year_id', ''));

        $subjectType = strtolower(trim((string) $request->input('subject_type', 'academic')));
        if (!in_array($subjectType, ['academic', 'skill', 'marks'], true)) {
            $subjectType = 'academic';
        }

        $standards = Standard::query()->where('is_active', 1)->orderBy('display_order')->get();
        $divisions = Division::query()->where('is_active', 1)->orderBy('division_name')->get();
        $academicYears = AcademicYear::query()->where('is_active', 1)->orderByDesc('year_name')->get(['id', 'year_name']);

        $examQuery = ExamMaster::query()->where('is_active', 1);
        if ($academicYearId !== '') {
            $examQuery->where('academic_year_id', (int) $academicYearId);
        }
        if ($standardId !== '') {
            $examQuery->where('standard_id', (int) $standardId);
        }
        $exams = $examQuery->orderBy('display_order')->orderBy('exam_name')->get();

        if ($subjectType === 'skill') {

            // -----------------------------------------------------------------
            // 1) Fetch allocations (no heavy subqueries — the subquery that
            //    computed recorded_students was running for every row).
            // -----------------------------------------------------------------
            $sql = <<<'SQL'
                SELECT
                    stsa.id                AS tsa_id,
                    stca.id                AS class_allocation_id,
                    stca.user_id           AS user_id,
                    stca.academic_year_id  AS academic_year_id,
                    stca.standard_id       AS standard_id,
                    stca.division_id       AS division_id,
                    stsa.exam_master_id    AS exam_master_id,
                    stsa.subject_id        AS subject_id,
                    em.exam_name           AS exam_name,
                    em.display_order       AS exam_display_order,
                    st.standard_name       AS standard_name,
                    st.display_order       AS standard_display_order,
                    d.division_name        AS division_name,
                    d.display_order        AS division_display_order,
                    u.name                 AS teacher_name,
                    subj.subject_name      AS subject_name,
                    subj.subject_code      AS subject_code,
                    subj.id                AS resolved_subject_id,
                    'PENDING' AS effective_status
                FROM skill_teacher_subject_allocations stsa
                INNER JOIN skill_teacher_class_allocations stca
                        ON stca.id = stsa.skill_teacher_class_allocation_id
                LEFT JOIN exam_masters em ON em.id = stsa.exam_master_id
                LEFT JOIN standards st    ON st.id  = stca.standard_id
                LEFT JOIN divisions d     ON d.id   = stca.division_id
                LEFT JOIN users u         ON u.id   = stca.user_id
                LEFT JOIN subjects subj   ON subj.id = stsa.subject_id
                WHERE 1=1
            SQL;

            $bindings = [];

            if (!$isAdministrator) {
                $sql .= " AND stca.user_id = ? ";
                $bindings[] = $userId;
            }
            if ($examId !== '')         { $sql .= " AND stsa.exam_master_id = ? ";  $bindings[] = (int) $examId; }
            if ($standardId !== '')     { $sql .= " AND stca.standard_id = ? ";     $bindings[] = (int) $standardId; }
            if ($divisionId !== '')     { $sql .= " AND stca.division_id = ? ";     $bindings[] = (int) $divisionId; }
            if ($academicYearId !== '') { $sql .= " AND stca.academic_year_id = ? ";$bindings[] = (int) $academicYearId; }

            $sql .= " ORDER BY stsa.id DESC, em.display_order ASC, st.display_order ASC, d.display_order ASC ";

            $allRows = collect(DB::select($sql, $bindings));

            $hardcodedCriteria = [
                48  => ['Accuracy', 'Neatness & Presentation', 'Creativity'],
                49  => ['Pronunciation', 'Recitation (Stotra)', 'Concept Clearance', 'Writing'],
                50  => ['Attentiveness', 'Learning', 'Presentation', 'Grace'],
                51  => ['Attentiveness', 'Learning', 'Presentation', 'Improvisation'],
                53  => ['Attentiveness', 'Learning', 'Presentation', 'Accompanying singer during performance'],
                54  => ['Reading', 'Meaningful Learning', 'Vocabulary', 'Ability to Express', 'Library Notes'],
                55  => ['Learning', 'Book Completion', 'Accuracy', 'Speed', 'Marks'],
                56  => ['Learning', 'Book Completion', 'Accuracy', 'Speed', 'Marks'],
                57  => ['Learning', 'C.W. (activity book)', 'Practical Marks', 'Innovative Thinking'],
                58  => ['Learning', 'C.W. (activity book)', 'Practical Marks', 'Innovative Thinking', 'Written Marks'],
                59  => ['Attentiveness', 'Learning', 'Presentation', 'Voice Quality', 'Rhythm & Melody'],
                60  => ['Attentiveness', 'Learning', 'Presentation', 'Retelling & Sequence'],
                61  => ['Folding Accuracy', 'Handling Coordination', 'Neatness & Presentation', 'Creativity'],
                63  => ['Oral', 'PT Practical Exam', 'Uniform', 'PT Periods'],
                64  => ['Coordination', 'Attentiveness', 'Neatness'],
                65  => ['Coordination', 'Attentiveness', 'Neatness'],
                66  => ['Shape & Size', 'Neatness & Finishing', 'Creativity', 'Independent Work'],
                99  => ['Attentiveness', 'Learning', 'Presentation'],
                100 => ['Object Recognition', 'Handling Skills', 'Ease Participation', 'Neatness & Completion'],
                101 => ['Attentiveness', 'Learning', 'Presentation'],
                102 => ['Attentiveness', 'Learning', 'Presentation'],
                106 => ['FLEXIBILITY', 'STAMINA', 'GROSS MOTOR SKILL'],
                107 => ['Grade'],
                108 => ['ANTHEM, PLEDGE, ETC.', 'STOTRA (SANSKRIT, MARATHI)'],
                109 => ['Grade'],
                110 => ['ATTENTIVENESS', 'FOOD HABITS', 'NEATNESS', 'INTERACTION WITH OTHERS', 'EXPRESSION OF FEELINGS', 'FOLLOWING INSTRUCTIONS', 'PARTICIPATION IN SCHOOL ACTIVITY', 'HAPPINESS & ENERGY LEVEL', 'OBEDIENCE', 'HELPING']
            ];

            // -----------------------------------------------------------------
            // 2) Compute total students ONLY for classes that have at least one
            //    non-subset skill allocation. Subset skills (Music / Dance /
            //    Acting / Tabla) never need a total count.
            // -----------------------------------------------------------------
            $classesNeedingTotal = [];
            foreach ($allRows as $r) {
                if ($this->isSubsetAllocatedSkillSubject($r->subject_name ?? '')) continue;

                $key = $r->academic_year_id . '_' . $r->standard_id . '_' . $r->division_id;
                if (!isset($classesNeedingTotal[$key])) {
                    $classesNeedingTotal[$key] = [
                        'academic_year_id' => $r->academic_year_id,
                        'standard_id'      => $r->standard_id,
                        'division_id'      => $r->division_id,
                    ];
                }
            }

            $totalStudentsMap = [];
            if (!empty($classesNeedingTotal)) {
                $academicYearIds = array_values(array_unique(array_column($classesNeedingTotal, 'academic_year_id')));
                $academicYearsMap = AcademicYear::whereIn('id', $academicYearIds)->get()->keyBy('id');

                foreach ($classesNeedingTotal as $key => $class) {
                    $academicYear = $academicYearsMap->get($class['academic_year_id']);
                    if (!$academicYear) {
                        $totalStudentsMap[$key] = 0;
                        continue;
                    }
                    $yearId = $academicYear->old_year_id ?? substr($academicYear->year_name, 0, 4);
                    try {
                        $students = StudentHelper::getStudentsDirectERP($yearId, $class['standard_id'], $class['division_id']);
                        $totalStudentsMap[$key] = is_countable($students) ? count($students) : 0;
                    } catch (\Throwable $e) {
                        $totalStudentsMap[$key] = 0;
                    }
                }
            }

            // -----------------------------------------------------------------
            // 3) Fetch observations with a single simple WHERE IN — replaces
            //    the giant (a OR b OR c …) clause that was killing the query.
            // -----------------------------------------------------------------
            $allObservations = collect();
            if ($allRows->isNotEmpty()) {
                $allObservations = DB::table('student_skill_observations')
                    ->select('id', 'academic_year_id', 'standard_id', 'division_id', 'exam_master_id', 'subject_id', 'student_id', 'observations', 'overall_grade', 'is_absent')
                    ->whereIn('academic_year_id', $allRows->pluck('academic_year_id')->unique()->filter()->values()->all())
                    ->whereIn('exam_master_id',   $allRows->pluck('exam_master_id')->unique()->filter()->values()->all())
                    ->whereIn('standard_id',      $allRows->pluck('standard_id')->unique()->filter()->values()->all())
                    ->whereIn('division_id',      $allRows->pluck('division_id')->unique()->filter()->values()->all())
                    ->whereIn('subject_id',       $allRows->pluck('subject_id')->unique()->filter()->values()->all())
                    ->get();
            }

            $groupedObservations = $allObservations->groupBy(
                fn($item) => $item->academic_year_id . '_' . $item->standard_id . '_' . $item->division_id . '_' . $item->exam_master_id . '_' . $item->subject_id
            );

            // -----------------------------------------------------------------
            // 4) Compute status per allocation
            // -----------------------------------------------------------------
            foreach ($allRows as $row) {
                $classKey = $row->academic_year_id . '_' . $row->standard_id . '_' . $row->division_id;
                $obsKey   = $classKey . '_' . $row->exam_master_id . '_' . $row->subject_id;

                $classObs = $groupedObservations->get($obsKey, collect());
                $latestObsPerStudent = $classObs->sortByDesc('id')->unique('student_id');
                $completedStudentsCount = 0;

                $isSubset = $this->isSubsetAllocatedSkillSubject($row->subject_name ?? '');

                $expectedKeys      = $hardcodedCriteria[$row->subject_id] ?? [];
                $expectedKeysLower = array_map('strtolower', $expectedKeys);

                foreach ($latestObsPerStudent as $obs) {
                    if (in_array($obs->is_absent, [1, true, '1'])) {
                        $completedStudentsCount++;
                        continue;
                    }

                    $obsData = json_decode($obs->observations, true);
                    if (!is_array($obsData) || empty($obsData)) continue;

                    $savedDataLower = array_change_key_case(array_map('trim', $obsData), CASE_LOWER);

                    // FIX: For subset subjects (Music, Dance, etc.), if ANY grade is entered, count it.
                    // This avoids strict criteria mismatches and correctly marks the subject as completed.
                    if ($isSubset) {
                        $hasAnyGrade = false;
                        foreach ($savedDataLower as $key => $grade) {
                            if (stripos($key, 'remark') !== false) continue;
                            $grade = trim((string) $grade);
                            if (!empty($grade) && !preg_match('/^[-\s—–\x{2010}-\x{2015}]*$/u', $grade)) {
                                $hasAnyGrade = true;
                                break;
                            }
                        }
                        if ($hasAnyGrade) {
                            $completedStudentsCount++;
                        }
                        continue; // Skip strict validation for subsets
                    }

                    if (!empty($expectedKeysLower)) {
                        $missingKeys = array_diff($expectedKeysLower, array_keys($savedDataLower));
                        if (!empty($missingKeys)) continue;
                    }

                    $isComplete = true;
                    foreach ($savedDataLower as $key => $grade) {
                        if (stripos($key, 'remark') !== false) continue;
                        $grade = trim((string) $grade);
                        if (empty($grade) || preg_match('/^[-\s—–\x{2010}-\x{2015}]*$/u', $grade)) {
                            $isComplete = false;
                            break;
                        }
                    }

                    if ($isComplete) {
                        $overall = trim((string) $obs->overall_grade);
                        if (empty($overall) || preg_match('/^[-\s—–\x{2010}-\x{2015}]*$/u', $overall)) {
                            $isComplete = false;
                        }
                    }

                    if ($isComplete) $completedStudentsCount++;
                }

                if ($isSubset) {
                    // Music / Dance / Acting / Tabla — subset-allocated
                    $row->effective_status = $completedStudentsCount > 0 ? 'COMPLETED' : 'PENDING';
                } else {
                    $totalStudents = $totalStudentsMap[$classKey] ?? 0;
                    $row->effective_status = ($totalStudents > 0 && $completedStudentsCount >= $totalStudents)
                        ? 'COMPLETED'
                        : 'PENDING';
                }
            }

        } elseif ($subjectType === 'marks') {

            $allRows = collect();

            $allocationsQuery = DB::table('skill_slot_allocations as ssa')
                ->leftJoin('standards as st', 'st.id', '=', 'ssa.standard_id')
                ->leftJoin('divisions as d', 'd.id', '=', 'ssa.division_id')
                ->leftJoin('exam_masters as em', 'em.id', '=', 'ssa.exam_master_id')
                ->leftJoin('academic_years as ay', 'ay.id', '=', 'ssa.academic_year_id')
                ->select('ssa.*', 'st.standard_name', 'd.division_name', 'em.exam_name', 'ay.year_name');

            if ($academicYearId !== '') $allocationsQuery->where('ssa.academic_year_id', $academicYearId);
            if ($examId !== '')         $allocationsQuery->where('ssa.exam_master_id', $examId);
            if ($standardId !== '')     $allocationsQuery->where('ssa.standard_id', $standardId);
            if ($divisionId !== '')     $allocationsQuery->where('ssa.division_id', $divisionId);

            $allocations = $allocationsQuery->orderBy('ssa.id', 'desc')->get();

            if ($allocations->isNotEmpty()) {

                $totalStudentsMap = [];
                $uniqueClasses = $allocations->unique(fn($item) => $item->academic_year_id . '_' . $item->standard_id . '_' . $item->division_id);

                $academicYearIds  = $uniqueClasses->pluck('academic_year_id')->unique()->values()->all();
                $academicYearsMap = AcademicYear::whereIn('id', $academicYearIds)->get()->keyBy('id');

                foreach ($uniqueClasses as $cls) {
                    $academicYear = $academicYearsMap->get($cls->academic_year_id);
                    if ($academicYear) {
                        $yearId = $academicYear->old_year_id ?? substr($academicYear->year_name, 0, 4);
                        $mapKey = $cls->academic_year_id . '_' . $cls->standard_id . '_' . $cls->division_id;
                        try {
                            $students = StudentHelper::getStudentsDirectERP($yearId, $cls->standard_id, $cls->division_id);
                            $totalStudentsMap[$mapKey] = is_countable($students) ? count($students) : 0;
                        } catch (\Throwable $e) {
                            $totalStudentsMap[$mapKey] = 0;
                        }
                    }
                }

                // Narrow marks query to the classes / exams actually in scope
                $marksQuery = DB::table('student_skill_marks')
                    ->select('student_id', 'academic_year_id', 'standard_id', 'division_id', 'exam_master_id', 'skill_slot', 'marks_obtained', 'is_absent')
                    ->whereIn('academic_year_id', $allocations->pluck('academic_year_id')->unique()->filter()->values()->all())
                    ->whereIn('exam_master_id',   $allocations->pluck('exam_master_id')->unique()->filter()->values()->all())
                    ->whereIn('standard_id',      $allocations->pluck('standard_id')->unique()->filter()->values()->all())
                    ->whereIn('division_id',      $allocations->pluck('division_id')->unique()->filter()->values()->all());

                $allMarks = $marksQuery->get()->groupBy(
                    fn($item) => $item->academic_year_id . '_' . $item->standard_id . '_' . $item->division_id . '_' . $item->exam_master_id . '_' . $item->skill_slot
                );

                $slotLabels = [
                    'S1' => 'S1: ARTS (Drawing, acting, dance, music, tabla)',
                    'S2' => 'S2: WORK EXP (Computer, Robotics, Abacus)',
                    'S3' => 'S3: PHYSICAL EDUCATION & HEALTH',
                    'S4' => 'S4: LIBRARY + SANSKRIT BHARTI',
                ];

                foreach ($allocations as $alloc) {
                    $classKey      = $alloc->academic_year_id . '_' . $alloc->standard_id . '_' . $alloc->division_id;
                    $totalStudents = $totalStudentsMap[$classKey] ?? 0;

                    $slotKey    = $classKey . '_' . $alloc->exam_master_id . '_' . $alloc->skill_slot;
                    $classMarks = $allMarks->get($slotKey, collect());

                    $completedStudentsCount = 0;
                    $groupedByStudent = $classMarks->groupBy('student_id');

                    foreach ($groupedByStudent as $studentId => $marks) {
                        $hasNulls = $marks->contains(fn($m) => is_null($m->marks_obtained));
                        if (!$hasNulls) $completedStudentsCount++;
                    }

                    $status = 'PENDING';
                    if ($totalStudents > 0 && $completedStudentsCount >= $totalStudents) {
                        $status = 'COMPLETED';
                    }

                    $allRows->push((object) [
                        'tsa_id'           => $alloc->id,
                        'academic_year_id' => $alloc->academic_year_id,
                        'standard_id'      => $alloc->standard_id,
                        'division_id'      => $alloc->division_id,
                        'exam_master_id'   => $alloc->exam_master_id,
                        'subject_id'       => 0,
                        'exam_name'        => $alloc->exam_name,
                        'standard_name'    => $alloc->standard_name,
                        'division_name'    => $alloc->division_name,
                        'subject_name'     => $slotLabels[$alloc->skill_slot] ?? $alloc->skill_slot,
                        'subject_code'     => $alloc->skill_slot,
                        'teacher_name'     => '-',
                        'effective_status' => $status,
                    ]);
                }
            }

        } else {

            $sql = <<<'SQL'
                SELECT
                    tms.teacher_subject_allocation_id AS tsa_id,
                    tca.id                AS class_allocation_id,
                    tca.user_id           AS user_id,
                    tms.academic_year_id  AS academic_year_id,
                    tms.standard_id       AS standard_id,
                    tms.division_id       AS division_id,
                    tms.exam_master_id    AS exam_master_id,
                    em.exam_name          AS exam_name,
                    em.display_order      AS exam_display_order,
                    st.standard_name      AS standard_name,
                    st.display_order      AS standard_display_order,
                    d.division_name       AS division_name,
                    d.display_order       AS division_display_order,
                    u.name                AS teacher_name,
                    subj.subject_name     AS subject_name,
                    subj.subject_code     AS subject_code,
                    subj.id               AS resolved_subject_id,
                    CASE
                        WHEN UPPER(TRIM(COALESCE(tms.status, ''))) = 'COMPLETED' THEN 'COMPLETED'
                        WHEN EXISTS (
                            SELECT 1 FROM student_marks sm
                            WHERE sm.academic_year_id = tms.academic_year_id
                              AND sm.section_id       = tca.section_id
                              AND sm.standard_id      = tms.standard_id
                              AND sm.division_id      = tms.division_id
                              AND sm.exam_master_id   = tms.exam_master_id
                              AND sm.subject_id       = subj.id
                              AND sm.is_locked        = 1
                        )
                        THEN 'COMPLETED'
                        ELSE UPPER(TRIM(COALESCE(tms.status, 'PENDING')))
                    END                   AS effective_status
                FROM teacher_marks_status tms
                INNER JOIN teacher_subject_allocations tsa ON tsa.id = tms.teacher_subject_allocation_id
                INNER JOIN subjects subj
                        ON subj.id = COALESCE(
                            (SELECT sws1.subject_id FROM standard_wise_subjects sws1
                             WHERE sws1.standard_id = tms.standard_id
                               AND sws1.subject_id = tsa.subject_id
                               AND sws1.is_active = 1 LIMIT 1),
                            (SELECT sws2.subject_id FROM standard_wise_subjects sws2
                             WHERE sws2.standard_id = tms.standard_id
                               AND sws2.id = tsa.subject_id
                               AND sws2.is_active = 1 LIMIT 1),
                            tsa.subject_id
                        )
                LEFT JOIN exam_masters em ON em.id = tms.exam_master_id
                LEFT JOIN standards st    ON st.id  = tms.standard_id
                LEFT JOIN divisions d     ON d.id   = tms.division_id
                LEFT JOIN users u         ON u.id   = tms.teacher_id
                LEFT JOIN teacher_class_allocations tca ON tca.id = tsa.teacher_class_allocation_id
                WHERE tms.teacher_subject_allocation_id IS NOT NULL
            SQL;

            $bindings = [];

            if (!$isAdministrator) {
                $sql .= " AND tca.user_id = ? ";
                $bindings[] = $userId;
            }
            if ($examId !== '')         { $sql .= " AND tms.exam_master_id = ? ";  $bindings[] = (int) $examId; }
            if ($standardId !== '')     { $sql .= " AND tms.standard_id = ? ";     $bindings[] = (int) $standardId; }
            if ($divisionId !== '')     { $sql .= " AND tms.division_id = ? ";     $bindings[] = (int) $divisionId; }
            if ($academicYearId !== '') { $sql .= " AND tms.academic_year_id = ? ";$bindings[] = (int) $academicYearId; }

            $sql .= " ORDER BY tms.teacher_subject_allocation_id DESC, em.display_order ASC, st.display_order ASC, d.display_order ASC, tms.id DESC ";

            $allRows = collect(DB::select($sql, $bindings));
        }

        foreach ($allRows as $row) {
            $row->status = strtoupper(trim((string) ($row->effective_status ?? '')));
        }

        $total     = $allRows->count();
        $completed = $allRows->where('effective_status', 'COMPLETED')->count();
        $pending   = $allRows->where('effective_status', 'PENDING')->count();

        $perPage     = 50;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $items       = $allRows->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $statuses = new LengthAwarePaginator(
            $items, $allRows->count(), $perPage, $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        return view('administrator.exam-progress.index', compact(
            'statuses', 'exams', 'standards', 'divisions', 'academicYears',
            'examId', 'standardId', 'divisionId', 'academicYearId',
            'completed', 'pending', 'total', 'subjectType'
        ));
    }
}