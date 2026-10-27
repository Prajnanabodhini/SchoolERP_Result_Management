<?php

namespace App\Http\Controllers;

use App\Helpers\TeacherBulkHelper;
use App\Helpers\StudentHelper;
use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\SkillTeacherClassAllocation;
use App\Models\SkillTeacherSubjectAllocation;
use App\Models\ExamMaster;
use App\Models\Standard;
use App\Models\Subject;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Throwable;

class SkillTeacherAllocationController extends Controller
{
    public function index(Request $request)
    {
        $academicYearId = $request->input('academic_year_id', '');
        $standardId     = $request->input('standard_id', '');
        $divisionId     = $request->input('division_id', '');
        $examMasterId   = $request->input('exam_master_id', '');
        
        $subjectType    = $request->input('subject_type', 'grade'); 

        $exams = TeacherBulkHelper::exams($academicYearId, $standardId);

        $isAllocationGenerated = false;
        $canGenerateAllocation = false;

        if ($subjectType === 'mark' && $academicYearId !== '' && $examMasterId !== '') {
            $isAllocationGeneratedQuery = DB::table('skill_slot_allocations')
                ->where('academic_year_id', (int)$academicYearId)
                ->where('exam_master_id', (int)$examMasterId);
            
            if ($standardId !== '') {
                $isAllocationGeneratedQuery->where('standard_id', (int)$standardId);
            }
            
            $isAllocationGenerated = $isAllocationGeneratedQuery->exists();

            // Check if the selected exam is a Term 1 or Term 2 exam
            $selectedExam = ExamMaster::find($examMasterId);
            if ($selectedExam) {
                $examNameUpper = strtoupper(trim((string) $selectedExam->exam_name));
                if (str_contains($examNameUpper, 'TERM 1') || str_contains($examNameUpper, 'TERM 2')) {
                    $canGenerateAllocation = true;
                }
            }
        }

        $allocations = collect(); 
        $slotAllocations = collect(); 
        $completedCount = 0;
        $pendingCount = 0;
        $totalCount = 0;

        if ($subjectType === 'grade') {
            $query = SkillTeacherClassAllocation::with([
                'teacher',
                'academicYear',
                'section',
                'standard',
                'division',
                'subjectAllocations.exam',
                'subjectAllocations.subject'
            ]);

            if ($academicYearId !== '') $query->where('academic_year_id', (int) $academicYearId);
            if ($standardId !== '')     $query->where('standard_id', (int) $standardId);
            if ($divisionId !== '')     $query->where('division_id', (int) $divisionId);
            if ($examMasterId !== '') {
                $query->whereHas('subjectAllocations', function ($q) use ($examMasterId) {
                    $q->where('exam_master_id', (int) $examMasterId);
                });
            }

            $allGradeAllocations = $query->orderByDesc('id')->get();
            $totalCount = $allGradeAllocations->count();

            // --- CALCULATE SUMMARY FOR GRADE TAB (Same logic as Dashboard) ---
            $sql = <<<'SQL'
                SELECT
                    stsa.id AS tsa_id, stca.academic_year_id, stca.standard_id, stca.division_id,
                    stsa.exam_master_id, stsa.subject_id,
                    (SELECT COUNT(DISTINCT student_id) FROM student_skill_observations sso
                     WHERE sso.academic_year_id = stca.academic_year_id
                       AND sso.standard_id      = stca.standard_id
                       AND sso.division_id      = stca.division_id
                       AND sso.exam_master_id   = stsa.exam_master_id
                       AND sso.subject_id       = stsa.subject_id
                    ) AS recorded_students
                FROM skill_teacher_subject_allocations stsa
                INNER JOIN skill_teacher_class_allocations stca ON stca.id = stsa.skill_teacher_class_allocation_id
                WHERE 1=1
            SQL;
            
            $b = [];
            if ($academicYearId !== '') { $sql .= " AND stca.academic_year_id = ? "; $b[] = (int) $academicYearId; }
            if ($standardId !== '')     { $sql .= " AND stca.standard_id = ? ";     $b[] = (int) $standardId; }
            if ($divisionId !== '')     { $sql .= " AND stca.division_id = ? ";     $b[] = (int) $divisionId; }
            if ($examMasterId !== '')   { $sql .= " AND stsa.exam_master_id = ? ";  $b[] = (int) $examMasterId; }

            $allRows = collect(DB::select($sql, $b));

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

            $totalStudentsMap = [];
            $uniqueClasses = $allRows->map(fn($r) => [
                'academic_year_id' => $r->academic_year_id,
                'standard_id'      => $r->standard_id,
                'division_id'      => $r->division_id,
            ])->unique()->values();

            foreach ($uniqueClasses as $class) {
                $academicYear = AcademicYear::find($class['academic_year_id']);
                if ($academicYear) {
                    $yearId = $academicYear->old_year_id ?? substr($academicYear->year_name, 0, 4);
                    try {
                        $students = StudentHelper::getStudentsDirectERP($yearId, $class['standard_id'], $class['division_id']);
                        $key = $class['academic_year_id'] . '_' . $class['standard_id'] . '_' . $class['division_id'];
                        $totalStudentsMap[$key] = is_countable($students) ? count($students) : 0;
                    } catch (\Throwable $e) {
                        $key = $class['academic_year_id'] . '_' . $class['standard_id'] . '_' . $class['division_id'];
                        $totalStudentsMap[$key] = 0;
                    }
                }
            }

            $obsQuery = DB::table('student_skill_observations')
                ->select('id', 'academic_year_id', 'standard_id', 'division_id', 'exam_master_id', 'subject_id', 'student_id', 'observations', 'overall_grade', 'is_absent');
            
            $uniqueCombinations = $allRows->map(fn($r) => [
                'academic_year_id' => $r->academic_year_id,
                'standard_id'      => $r->standard_id,
                'division_id'      => $r->division_id,
                'exam_master_id'   => $r->exam_master_id,
                'subject_id'       => $r->subject_id,
            ])->unique();

            if ($uniqueCombinations->isNotEmpty()) {
                $obsQuery->where(function($q) use ($uniqueCombinations) {
                    foreach ($uniqueCombinations as $comb) {
                        $q->orWhere(function($subQ) use ($comb) {
                            $subQ->where('academic_year_id', $comb['academic_year_id'])
                                 ->where('standard_id', $comb['standard_id'])
                                 ->where('division_id', $comb['division_id'])
                                 ->where('exam_master_id', $comb['exam_master_id'])
                                 ->where('subject_id', $comb['subject_id']);
                        });
                    }
                });
            }

            $allObservations = $obsQuery->get();
            $groupedObservations = $allObservations->groupBy(fn($item) => $item->academic_year_id . '_' . $item->standard_id . '_' . $item->division_id . '_' . $item->exam_master_id . '_' . $item->subject_id);

            foreach ($allRows as $row) {
                $classKey = $row->academic_year_id . '_' . $row->standard_id . '_' . $row->division_id;
                $obsKey = $classKey . '_' . $row->exam_master_id . '_' . $row->subject_id;

                $totalStudents = $totalStudentsMap[$classKey] ?? 0;
                $classObs = $groupedObservations->get($obsKey, collect());
                $latestObsPerStudent = $classObs->sortByDesc('id')->unique('student_id');
                $completedStudentsCount = 0;

                $expectedKeys = $hardcodedCriteria[$row->subject_id] ?? [];
                $expectedKeysLower = array_map('strtolower', $expectedKeys);

                foreach ($latestObsPerStudent as $obs) {
                    if (in_array($obs->is_absent, [1, true, '1'])) {
                        $completedStudentsCount++;
                        continue;
                    }

                    $obsData = json_decode($obs->observations, true);
                    if (!is_array($obsData) || empty($obsData)) continue;

                    $savedDataLower = array_change_key_case(array_map('trim', $obsData), CASE_LOWER);

                    if (!empty($expectedKeysLower)) {
                        $missingKeys = array_diff($expectedKeysLower, array_keys($savedDataLower));
                        if (!empty($missingKeys)) continue;
                    }

                    $isComplete = true;
                    foreach ($savedDataLower as $key => $grade) {
                        if (stripos($key, 'remark') !== false) continue;
                        $grade = trim((string)$grade);
                        if (empty($grade) || preg_match('/^[-\s—–\x{2010}-\x{2015}]*$/u', $grade)) {
                            $isComplete = false; break;
                        }
                    }

                    if ($isComplete) {
                        $overall = trim((string)$obs->overall_grade);
                        if (empty($overall) || preg_match('/^[-\s—–\x{2010}-\x{2015}]*$/u', $overall)) $isComplete = false;
                    }

                    if ($isComplete) $completedStudentsCount++;
                }

                if ($totalStudents > 0 && $completedStudentsCount >= $totalStudents) {
                    $completedCount++;
                } else {
                    $pendingCount++;
                }
            }

            // Paginate manually for the Grade tab
            $perPage = 25;
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $currentItems = $allGradeAllocations->slice(($currentPage - 1) * $perPage, $perPage)->values();
            $allocations = new LengthAwarePaginator($currentItems, $allGradeAllocations->count(), $perPage, $currentPage, [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]);

        } 
        elseif ($subjectType === 'mark') {
            // Use LEFT JOIN to ensure counts are correct even if related records are missing
            $query = DB::table('skill_slot_allocations as ssa')
                ->leftJoin('standards as st', 'st.id', '=', 'ssa.standard_id')
                ->leftJoin('divisions as d', 'd.id', '=', 'ssa.division_id')
                ->leftJoin('exam_masters as em', 'em.id', '=', 'ssa.exam_master_id')
                ->leftJoin('academic_years as ay', 'ay.id', '=', 'ssa.academic_year_id')
                ->select('ssa.*', 'st.standard_name', 'd.division_name', 'em.exam_name', 'ay.year_name');

            if ($academicYearId !== '') $query->where('ssa.academic_year_id', (int)$academicYearId);
            if ($examMasterId !== '')   $query->where('ssa.exam_master_id', (int)$examMasterId);
            if ($standardId !== '')     $query->where('ssa.standard_id', (int)$standardId);
            if ($divisionId !== '')     $query->where('ssa.division_id', (int)$divisionId);

            $allAllocations = $query->orderBy('ssa.id', 'desc')->get();
            $totalCount = $allAllocations->count();

            if ($allAllocations->isNotEmpty()) {
                $totalStudentsMap = [];
                $uniqueClasses = $allAllocations->unique(fn($item) => $item->academic_year_id . '_' . $item->standard_id . '_' . $item->division_id);
                
                $academicYearIds = $uniqueClasses->pluck('academic_year_id')->unique();
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

                $marksQuery = DB::table('student_skill_marks')
                    ->select('student_id', 'academic_year_id', 'standard_id', 'division_id', 'exam_master_id', 'skill_slot', 'marks_obtained', 'is_absent');
                
                if ($academicYearId !== '') $marksQuery->where('academic_year_id', (int)$academicYearId);
                if ($examMasterId !== '')   $marksQuery->where('exam_master_id', (int)$examMasterId);
                
                $allMarks = $marksQuery->get()
                    ->groupBy(fn($item) => $item->academic_year_id . '_' . $item->standard_id . '_' . $item->division_id . '_' . $item->exam_master_id . '_' . $item->skill_slot);

                $slotLabels = [
                    'S1' => 'S1: ARTS (Drawing, acting, dance, music, tabla)',
                    'S2' => 'S2: WORK EXP (Computer, Robotics, Abacus)',
                    'S3' => 'S3: PHYSICAL EDUCATION & HEALTH',
                    'S4' => 'S4: LIBRARY + SANSKRIT BHARTI',
                ];

                foreach ($allAllocations as $alloc) {
                    $classKey = $alloc->academic_year_id . '_' . $alloc->standard_id . '_' . $alloc->division_id;
                    $totalStudents = $totalStudentsMap[$classKey] ?? 0;
                    
                    $slotKey = $classKey . '_' . $alloc->exam_master_id . '_' . $alloc->skill_slot;
                    $classMarks = $allMarks->get($slotKey, collect());

                    $completedStudentsCount = 0;
                    $groupedByStudent = $classMarks->groupBy('student_id');

                    foreach ($groupedByStudent as $studentId => $marks) {
                        $hasNulls = $marks->contains(fn($m) => is_null($m->marks_obtained));
                        if (!$hasNulls) $completedStudentsCount++;
                    }

                    if ($totalStudents > 0 && $completedStudentsCount >= $totalStudents) {
                        $alloc->status = 'COMPLETED';
                        $completedCount++;
                    } else {
                        $alloc->status = 'PENDING';
                        $pendingCount++;
                    }
                    $alloc->full_slot_name = $slotLabels[$alloc->skill_slot] ?? $alloc->skill_slot;
                }
            }

            $perPage = 50;
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $currentItems = $allAllocations->slice(($currentPage - 1) * $perPage, $perPage)->values();
            $slotAllocations = new LengthAwarePaginator($currentItems, $allAllocations->count(), $perPage, $currentPage, [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]);
        }

        return view('administrator.skill-teacher-allocation.index', [
            'allocations'           => $allocations,
            'slotAllocations'       => $slotAllocations,
            'completedCount'        => $completedCount,
            'pendingCount'          => $pendingCount,
            'totalCount'            => $totalCount,
            'standards'             => TeacherBulkHelper::standards(),
            'academicYears'         => TeacherBulkHelper::academicYears(),
            'divisions'             => TeacherBulkHelper::divisions(),
            'exams'                 => $exams,
            'academicYearId'        => $academicYearId,
            'standardId'            => $standardId,
            'divisionId'            => $divisionId,
            'examMasterId'          => $examMasterId,
            'subjectType'           => $subjectType,
            'isAllocationGenerated' => $isAllocationGenerated,
            'canGenerateAllocation' => $canGenerateAllocation,
        ]);
    }

    public function generateSkillMarksAllocations(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|integer',
            'exam_master_id'   => 'required|integer',
            'standard_id'      => 'nullable|integer', // Added validation
        ]);

        $academicYearId = $request->academic_year_id;
        $examMasterId   = $request->exam_master_id;
        $standardId     = $request->filled('standard_id') ? (int)$request->standard_id : null;

        $examMaster = ExamMaster::find($examMasterId);
        if (!$examMaster) {
            return redirect()->back()->with('error', 'Exam not found.');
        }

        if (!$standardId) {
            $standardId = $examMaster->standard_id;
        }

        // Backend Validation: Ensure it's a Term 1 or Term 2 exam
        $examNameUpper = strtoupper(trim((string) $examMaster->exam_name));
        if (!str_contains($examNameUpper, 'TERM 1') && !str_contains($examNameUpper, 'TERM 2')) {
            return redirect()->back()->with('error', 'Allocations can only be generated for Term 1 and Term 2 exams.');
        }

        // Check if already generated
        $existsQuery = DB::table('skill_slot_allocations')
            ->where('academic_year_id', $academicYearId)
            ->where('exam_master_id', $examMasterId);
        
        if ($standardId) {
            $existsQuery->where('standard_id', $standardId);
        }

        if ($existsQuery->exists()) {
            return redirect()->back()->with('error', 'Allocations have already been generated for this criteria.');
        }

        $divisions = Division::where('is_active', 1)
            ->whereIn('division_name', ['A', 'B'])
            ->get();

        $academicYear = AcademicYear::find($academicYearId);
        $yearId = $academicYear->old_year_id ?? substr($academicYear->year_name, 0, 4);

        $slots = ['S1', 'S2', 'S3', 'S4'];
        $insertData = [];

        foreach ($divisions as $div) {
            try {
                $students = StudentHelper::getStudentsDirectERP($yearId, $standardId, $div->id);
                $studentCount = is_countable($students) ? count($students) : 0;
                
                if ($studentCount > 0) {
                    foreach ($slots as $slot) {
                        $insertData[] = [
                            'academic_year_id' => $academicYearId,
                            'standard_id'      => $standardId,
                            'division_id'      => $div->id,
                            'exam_master_id'   => $examMasterId,
                            'skill_slot'       => $slot,
                            'created_at'       => now(),
                            'updated_at'       => now(),
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::error("Failed to check students for allocation generation", ['div' => $div->id, 'error' => $e->getMessage()]);
            }
        }

        if (!empty($insertData)) {
            DB::table('skill_slot_allocations')->insert($insertData);
            return redirect()->back()->with('success', 'Allocations generated successfully for ' . $examMaster->exam_name);
        }

        return redirect()->back()->with('error', 'No students found in any division for this exam.');
    }

    public function create(Request $request)
    {
        $selectedAcademicYearId = $request->input('academic_year_id');

        $skillSubjectsByStandard = $this->getSkillSubjectsByStandard();

        $divisionsByStandard = $selectedAcademicYearId
            ? StudentHelper::getERPDivisionsByStandard((int) $selectedAcademicYearId)
            : [];

        return view('administrator.skill-teacher-allocation.create', [
            'teachers'                => TeacherBulkHelper::teachers(),
            'academicYears'           => TeacherBulkHelper::academicYears(),
            'sections'                => TeacherBulkHelper::sections(),
            'standards'               => TeacherBulkHelper::standards(),
            'divisions'               => TeacherBulkHelper::divisions(),
            'divisionsByStandard'     => $divisionsByStandard,
            'exams'                   => TeacherBulkHelper::exams($selectedAcademicYearId),
            'skillSubjectsByStandard' => $skillSubjectsByStandard,
            'selectedAcademicYearId'  => $selectedAcademicYearId,
        ]);
    }

    public function getExamDetails(Request $request)
    {
        $request->validate([
            'exam_master_id'   => 'required|exists:exam_masters,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
        ]);

        $exam = ExamMaster::findOrFail($request->exam_master_id);
        $examAcademicYearId = (int) ($exam->academic_year_id ?? 0);

        if ($examAcademicYearId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Selected Exam does not have an Academic Year assigned.',
                'standard' => null, 'section' => null, 'divisions' => [], 'subjects' => [],
            ], 422);
        }

        $standard = Standard::where('id', $exam->standard_id)
            ->where('is_active', 1)
            ->first();

        if (!$standard) {
            return response()->json([
                'success' => false,
                'message' => 'No Standard is assigned to this Exam.',
                'standard' => null, 'section' => null, 'divisions' => [], 'subjects' => [],
            ], 422);
        }

        $section = $standard->section_id ? Section::find($standard->section_id) : null;
        $divisions = TeacherBulkHelper::divisions();
        $erpDivisions = StudentHelper::getERPDivisionsForStandard($examAcademicYearId, (int) $standard->id);

        if ($erpDivisions->isNotEmpty()) {
            $validIds  = $erpDivisions->pluck('id')->map(fn ($v) => (int) $v)->all();
            $divisions = $divisions->filter(fn ($d) => in_array((int) $d->id, $validIds, true))->values();
        } else {
            $divisions = collect();
        }

        $subjects = TeacherBulkHelper::getExamMappedSubjects($exam->id, $standard->id)
            ->filter(function ($subject) {
                $isSkillType = in_array((int) ($subject->subject_type_id ?? 0), [2, 3], true) || empty($subject->subject_type_id);
                $isNotStory = strtoupper(trim((string)($subject->subject_name ?? ''))) !== 'STORY';
                return $isSkillType && $isNotStory;
            })->values();

        return response()->json([
            'success'          => true,
            'academic_year_id' => $examAcademicYearId,
            'exam'             => ['id' => (int) $exam->id, 'exam_name' => $exam->exam_name, 'academic_year_id' => $examAcademicYearId],
            'standard'         => ['id' => (int) $standard->id, 'standard_name' => $standard->standard_name],
            'section'          => $section ? ['id' => (int) $section->id, 'section_name' => $section->section_name] : null,
            'divisions'        => $divisions->values(),
            'subjects'         => $subjects->map(fn ($subject) => [
                'subject_id'      => (int) $subject->subject_id,
                'subject_name'    => $subject->subject_name ?: '-',
                'subject_code'    => $subject->subject_code ?: '',
                'short_name'      => $subject->short_name ?: '',
                'is_optional'     => (int) $subject->is_optional,
                'exam_subject_id' => (int) ($subject->exam_subject_id ?? 0),
                'max_marks'       => $subject->max_marks,
                'passing_marks'   => $subject->passing_marks,
                'display_order'   => $subject->display_order,
            ])->values(),
        ]);
    }

    public function getStandards(Request $request)
    {
        $sectionId = $request->input('section_id');
        if (!$sectionId) return response()->json([]);

        return response()->json(
            Standard::where('section_id', $sectionId)->where('is_active', 1)->orderBy('display_order')->get(['id', 'standard_name'])
        );
    }

    public function getSubjects(Request $request)
    {
        $examMasterId = $request->input('exam_master_id');

        if (!$examMasterId) {
            return response()->json(['success' => false, 'message' => 'Exam is required.', 'subjects' => []], 422);
        }

        $exam = ExamMaster::find($examMasterId);

        if (!$exam) {
            return response()->json(['success' => false, 'message' => 'Selected Exam was not found.', 'subjects' => []], 404);
        }

        $examYearId = (int) ($exam->academic_year_id ?? 0);
        if ($examYearId <= 0) {
            return response()->json(['success' => false, 'message' => 'Selected Exam does not have an Academic Year assigned.', 'subjects' => []], 422);
        }

        $standardId = (int) $exam->standard_id;
        if (!$standardId) {
            return response()->json(['success' => false, 'message' => 'No Standard is assigned to this Exam.', 'subjects' => []], 422);
        }

        $standard = Standard::find($standardId);
        if (!$standard) {
            return response()->json(['success' => false, 'message' => 'Standard not found.', 'subjects' => []], 404);
        }

        $subjects = TeacherBulkHelper::getExamMappedSubjects($examMasterId, $standardId)
            ->filter(fn($s) => strtoupper(trim((string)$s->subject_name)) !== 'STORY')->values();

        return response()->json([
            'success'          => true,
            'academic_year_id' => $examYearId,
            'exam'             => ['id' => (int) $exam->id, 'exam_name' => $exam->exam_name, 'academic_year_id' => $examYearId],
            'standard'         => ['id' => (int) $standard->id, 'standard_name' => $standard->standard_name],
            'subjects'         => $subjects->map(fn ($subject) => [
                'mapping_id'    => $subject->mapping_id ?? null,
                'subject_id'    => (int) $subject->subject_id,
                'standard'      => $subject->standard_name ?? '',
                'subject_name'  => $subject->subject_name ?: '-',
                'subject_code'  => $subject->subject_code ?: '',
                'short_name'    => $subject->short_name ?: '',
                'is_optional'   => (int) $subject->is_optional,
                'max_marks'     => $subject->max_marks,
                'passing_marks' => $subject->passing_marks,
                'display_order' => $subject->display_order,
            ])->values(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id'            => 'required|exists:users,id',
            'academic_year_id'   => 'required|exists:academic_years,id',
            'exam_master_id'     => 'required|exists:exam_masters,id',
            'rows'               => 'required|array|min:1',
            'rows.*.standards'   => 'required|array|min:1',
            'rows.*.standards.*' => 'required|exists:standards,id',
            'rows.*.divisions'   => 'required|array|min:1',
            'rows.*.divisions.*' => 'required|exists:divisions,id',
            'rows.*.subjects'    => 'required|array|min:1',
            'rows.*.subjects.*'  => 'required|exists:subjects,id',
        ]);

        $errors = $this->validateDivisionsHaveStudents($request);
        if (!empty($errors)) {
            return back()->withInput()->with('error', implode(' ', $errors));
        }

        try {
            DB::transaction(function () use ($request) {
                $this->saveAllocation($request);
            });

            return redirect()->route('skill-teacher-allocation.index')->with('success', 'Skill Teacher Allocation Saved Successfully.');

        } catch (Throwable $e) {
            Log::error('Skill Teacher Allocation Store Error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $allocation = SkillTeacherClassAllocation::with(['subjectAllocations.exam', 'subjectAllocations.subject'])->findOrFail($id);
        $selectedAcademicYearId = $allocation->academic_year_id;
        $allocationIds = $this->findSiblingAllocationIds($allocation);

        $existingDivisionIds = SkillTeacherClassAllocation::whereIn('id', $allocationIds)->pluck('division_id')->unique()->values()->all();
        $existingSubjectIds = SkillTeacherSubjectAllocation::whereIn('skill_teacher_class_allocation_id', $allocationIds)->pluck('subject_id')->unique()->values()->all();
        $skillSubjectsByStandard = $this->getSkillSubjectsByStandard();

        return view('administrator.skill-teacher-allocation.edit', [
            'allocation'              => $allocation,
            'existingDivisionIds'     => $existingDivisionIds,
            'existingSubjectIds'      => $existingSubjectIds,
            'teachers'                => TeacherBulkHelper::teachers(),
            'academicYears'           => TeacherBulkHelper::academicYears(),
            'sections'                => TeacherBulkHelper::sections(),
            'standards'               => TeacherBulkHelper::standards(),
            'divisions'               => TeacherBulkHelper::divisions(),
            'exams'                   => TeacherBulkHelper::exams($selectedAcademicYearId),
            'skillSubjectsByStandard' => $skillSubjectsByStandard,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id'          => 'required|exists:users,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'standard_id'      => 'required|exists:standards,id',
            'divisions'        => 'required|array|min:1',
            'divisions.*'      => 'required|exists:divisions,id',
            'exam_master_id'   => 'required|exists:exam_masters,id',
            'subjects'         => 'required|array|min:1',
            'subjects.*'       => 'required|exists:subjects,id',
        ], [
            'user_id.required'          => 'Please select Teacher.',
            'academic_year_id.required' => 'Please select Academic Year.',
            'standard_id.required'      => 'Standard is required.',
            'divisions.required'        => 'Please select at least one Division.',
            'divisions.min'             => 'Please select at least one Division.',
            'exam_master_id.required'   => 'Please select Exam.',
            'subjects.required'         => 'Please select at least one Skill Subject.',
            'subjects.min'              => 'Please select at least one Skill Subject.',
        ]);

        $errors = $this->validateUpdateDivisionsHaveStudents($request);
        if (!empty($errors)) {
            return back()->withInput()->with('error', implode(' ', $errors));
        }

        try {
            DB::transaction(function () use ($request, $id) {
                $allocation = SkillTeacherClassAllocation::with('subjectAllocations')->findOrFail($id);
                $allocationIds = $this->findSiblingAllocationIds($allocation);

                SkillTeacherSubjectAllocation::whereIn('skill_teacher_class_allocation_id', $allocationIds)->delete();
                SkillTeacherClassAllocation::whereIn('id', $allocationIds)->delete();

                foreach ($request->divisions as $divisionId) {
                    $newAllocation = SkillTeacherClassAllocation::create([
                        'user_id'          => $request->user_id,
                        'academic_year_id' => $request->academic_year_id,
                        'section_id'       => $request->section_id,
                        'standard_id'      => $request->standard_id,
                        'division_id'      => $divisionId,
                        'is_class_teacher' => false,
                    ]);

                    foreach ($request->subjects as $subjectId) {
                        SkillTeacherSubjectAllocation::create([
                            'skill_teacher_class_allocation_id' => $newAllocation->id,
                            'exam_master_id'                    => $request->exam_master_id,
                            'subject_id'                        => $subjectId,
                        ]);
                    }
                }
            });

            return redirect()->route('skill-teacher-allocation.index')->with('success', 'Skill Teacher Allocation updated successfully.');

        } catch (Throwable $e) {
            Log::error('Skill Teacher Allocation Update Error', ['allocation_id' => $id, 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $allocation = SkillTeacherClassAllocation::findOrFail($id);
            $allocation->delete();
            return redirect()->route('skill-teacher-allocation.index')->with('success', 'Skill Teacher Allocation deleted successfully.');

        } catch (Throwable $e) {
            Log::error('Skill Teacher Allocation Delete Error', ['allocation_id' => $id, 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->route('skill-teacher-allocation.index')->with('error', $e->getMessage());
        }
    }

    private function validateDivisionsHaveStudents(Request $request): array
    {
        $errors = [];
        $academicYearId = (int) $request->academic_year_id;
        $academicYear   = AcademicYear::find($academicYearId);
        if (!$academicYear) return $errors;

        $yearId = $academicYear->old_year_id ?? substr($academicYear->year_name, 0, 4);

        foreach ($request->rows as $rowIndex => $row) {
            $standardId = (int) ($row['standards'][0] ?? 0);
            if (!$standardId) continue;

            $standard = Standard::find($standardId);
            $stdName  = $standard->standard_name ?? "Std #{$standardId}";

            foreach ($row['divisions'] as $divisionId) {
                $divisionId = (int) $divisionId;
                $division   = \App\Models\Division::find($divisionId);
                $divName    = $division->division_name ?? "Division #{$divisionId}";

                if (!$this->divisionHasStudents($yearId, $standardId, $divisionId)) {
                    $errors[] = "Cannot allocate: No students found for {$stdName} - {$divName} in the selected academic year.";
                }
            }
        }
        return array_values(array_unique($errors));
    }

    private function validateUpdateDivisionsHaveStudents(Request $request): array
    {
        $errors = [];
        $academicYearId = (int) $request->academic_year_id;
        $academicYear   = AcademicYear::find($academicYearId);
        if (!$academicYear) return $errors;

        $yearId     = $academicYear->old_year_id ?? substr($academicYear->year_name, 0, 4);
        $standardId = (int) $request->standard_id;
        $standard = Standard::find($standardId);
        $stdName  = $standard->standard_name ?? "Std #{$standardId}";

        foreach ($request->divisions as $divisionId) {
            $divisionId = (int) $divisionId;
            $division   = \App\Models\Division::find($divisionId);
            $divName    = $division->division_name ?? "Division #{$divisionId}";

            if (!$this->divisionHasStudents($yearId, $standardId, $divisionId)) {
                $errors[] = "Cannot allocate: No students found for {$stdName} - {$divName} in the selected academic year.";
            }
        }
        return array_values(array_unique($errors));
    }

    private function divisionHasStudents($yearId, int $standardId, int $divisionId): bool
    {
        try {
            $students = StudentHelper::getStudentsDirectERP($yearId, $standardId, $divisionId);
            return !empty($students) && count($students) > 0;
        } catch (Throwable $e) {
            Log::warning('divisionHasStudents check failed — failing open', [
                'year_id' => $yearId, 'standard_id' => $standardId, 'division_id' => $divisionId, 'error' => $e->getMessage(),
            ]);
            return true;
        }
    }

    private function saveAllocation(Request $request)
    {
        foreach ($request->rows as $row) {
            foreach ($row['divisions'] as $divisionId) {
                $allocation = SkillTeacherClassAllocation::create([
                    'user_id'          => $request->user_id,
                    'academic_year_id' => $request->academic_year_id,
                    'section_id'       => $row['section_id'] ?? null,
                    'standard_id'      => $row['standards'][0],
                    'division_id'      => $divisionId,
                    'is_class_teacher' => $request->is_class_teacher ?? false,
                ]);

                foreach ($row['subjects'] as $subjectId) {
                    SkillTeacherSubjectAllocation::create([
                        'skill_teacher_class_allocation_id' => $allocation->id,
                        'exam_master_id'                    => $request->exam_master_id,
                        'subject_id'                        => $subjectId,
                    ]);
                }
            }
        }
    }

    private function getSkillSubjectsByStandard()
    {
        $data = \App\Helpers\SkillCriteriaHelper::getSkillSubjectsByStandard();
        $swapStandardIds = [8, 9, 10];
        $physicalEduStandardIds = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $craftDrawingStandardIds = [1, 2, 3, 4, 5, 6, 7, 8, 9];

        $vedicMathsId = (int) Subject::query()->whereRaw('UPPER(TRIM(subject_name)) = ?', ['VEDIC MATHS'])->where('is_active', 1)->orderBy('id')->value('id');
        $physicalEduId = (int) Subject::query()->where(function ($q) {
            $q->whereRaw('UPPER(TRIM(subject_name)) = ?', ['PHYSICAL EDUCATION'])->orWhereRaw('UPPER(TRIM(subject_name)) = ?', ['PE'])->orWhereRaw('UPPER(TRIM(subject_name)) = ?', ['PT'])->orWhereRaw('UPPER(TRIM(subject_code)) = ?', ['PE']);
        })->where('is_active', 1)->orderBy('id')->value('id');
        $craftId = (int) Subject::query()->whereRaw('UPPER(TRIM(subject_name)) = ?', ['CRAFT'])->where('is_active', 1)->orderBy('id')->value('id');
        $drawingId = (int) Subject::query()->whereRaw('UPPER(TRIM(subject_name)) = ?', ['DRAWING'])->where('is_active', 1)->orderBy('id')->value('id');

        foreach ($swapStandardIds as $stdId) {
            if (isset($data[$stdId])) { $key = $stdId; } elseif (isset($data[(string) $stdId])) { $key = (string) $stdId; } else { continue; }
            $entries = $data[$key];
            if ($entries instanceof \Illuminate\Support\Collection) { $entries = $entries->all(); }
            if (!is_array($entries)) { continue; }

            foreach ($entries as $idx => $sub) {
                if (is_array($sub)) { $name = strtoupper(trim((string) ($sub['subject_name'] ?? ''))); } elseif (is_object($sub)) { $name = strtoupper(trim((string) ($sub->subject_name ?? ''))); } elseif (is_string($sub)) { $name = strtoupper(trim($sub)); } else { continue; }
                if ($name !== 'ABACUS') { continue; }
                if (is_array($sub)) {
                    $data[$key][$idx]['subject_name'] = 'VEDIC MATHS'; $data[$key][$idx]['short_name'] = 'VM';
                    if ($vedicMathsId > 0) { $data[$key][$idx]['id'] = $vedicMathsId; }
                } elseif (is_object($sub)) {
                    $sub->subject_name = 'VEDIC MATHS'; $sub->short_name = 'VM';
                    if ($vedicMathsId > 0) { $sub->id = $vedicMathsId; }
                } elseif (is_string($sub)) { $data[$key][$idx] = 'VEDIC MATHS'; }
            }
        }

        if ($physicalEduId > 0) {
            foreach ($physicalEduStandardIds as $stdId) {
                if (isset($data[$stdId])) { $key = $stdId; } elseif (isset($data[(string) $stdId])) { $key = (string) $stdId; } else { continue; }
                $entries = $data[$key]; $isColl = $entries instanceof \Illuminate\Support\Collection; $asArray = $isColl ? $entries->all() : $entries;
                if (!is_array($asArray)) { continue; }
                $alreadyPresent = false;
                foreach ($asArray as $sub) {
                    if (is_array($sub)) { $name = strtoupper(trim((string) ($sub['subject_name'] ?? ''))); } elseif (is_object($sub)) { $name = strtoupper(trim((string) ($sub->subject_name ?? ''))); } elseif (is_string($sub)) { $name = strtoupper(trim($sub)); } else { continue; }
                    if ($name === 'PHYSICAL EDUCATION' || $name === 'PE' || $name === 'PT') { $alreadyPresent = true; break; }
                }
                if ($alreadyPresent) { continue; }
                if ($isColl) { $asArray->push((object) ['id' => $physicalEduId, 'subject_name' => 'PHYSICAL EDUCATION', 'short_name' => 'PE']); $data[$key] = $asArray; } 
                else { $asArray[] = ['id' => $physicalEduId, 'subject_name' => 'PHYSICAL EDUCATION', 'short_name' => 'PE']; $data[$key] = $asArray; }
            }
        }

        if ($craftId > 0 || $drawingId > 0) {
            foreach ($craftDrawingStandardIds as $stdId) {
                if (isset($data[$stdId])) { $key = $stdId; } elseif (isset($data[(string) $stdId])) { $key = (string) $stdId; } else { continue; }
                $entries = $data[$key]; $isColl = $entries instanceof \Illuminate\Support\Collection; $asArray = $isColl ? $entries->all() : $entries;
                if (!is_array($asArray)) { continue; }
                $namesPresent = [];
                foreach ($asArray as $sub) {
                    if (is_array($sub)) { $namesPresent[] = strtoupper(trim((string) ($sub['subject_name'] ?? ''))); } elseif (is_object($sub)) { $namesPresent[] = strtoupper(trim((string) ($sub->subject_name ?? ''))); } elseif (is_string($sub)) { $namesPresent[] = strtoupper(trim($sub)); }
                }
                if ($craftId > 0 && !in_array('CRAFT', $namesPresent, true)) {
                    if ($isColl) { $asArray->push((object) ['id' => $craftId, 'subject_name' => 'CRAFT', 'short_name' => 'CRAFT']); } else { $asArray[] = ['id' => $craftId, 'subject_name' => 'CRAFT', 'short_name' => 'CRAFT']; }
                }
                if ($drawingId > 0 && !in_array('DRAWING', $namesPresent, true)) {
                    if ($isColl) { $asArray->push((object) ['id' => $drawingId, 'subject_name' => 'DRAWING', 'short_name' => 'DRAWING']); } else { $asArray[] = ['id' => $drawingId, 'subject_name' => 'DRAWING', 'short_name' => 'DRAWING']; }
                }
                $data[$key] = $asArray;
            }
        }

        $prePrimaryStandardIds = \App\Models\Standard::where('is_active', 1)->where(function($q) {
            $q->whereRaw('LOWER(standard_name) LIKE ?', ['%nursery%'])->orWhereRaw('LOWER(standard_name) LIKE ?', ['%jr kg%'])->orWhereRaw('LOWER(standard_name) LIKE ?', ['%sr kg%'])->orWhereRaw('LOWER(standard_name) LIKE ?', ['%jrkg%'])->orWhereRaw('LOWER(standard_name) LIKE ?', ['%srkg%'])->orWhereRaw('LOWER(standard_name) LIKE ?', ['%pre%primary%']);
        })->pluck('id')->toArray();

        if (!empty($prePrimaryStandardIds)) {
            $requiredNames = ['GROUND ACTIVITIES', 'DRAWING', 'CRAFT', 'CLAY', 'LIFE SKILL', 'SONG', 'INDOOR GAME', 'TRG. SENSES', 'ASSEMBLY', 'ACTIVITY BOOK', 'GENERAL SKILLS'];
            $dbSubjects = Subject::where('is_active', 1)->get();
            $orderedSubjects = [];
            foreach ($requiredNames as $reqName) {
                $found = $dbSubjects->first(function($s) use ($reqName) { $sName = strtoupper(trim($s->subject_name)); return $sName === $reqName || str_contains($sName, $reqName); });
                if ($found) { $orderedSubjects[] = ['id' => (int) $found->id, 'subject_name' => $reqName, 'short_name' => $reqName]; }
            }
            foreach ($prePrimaryStandardIds as $stdId) {
                if (isset($data[$stdId])) { $key = $stdId; } elseif (isset($data[(string)$stdId])) { $key = (string)$stdId; } else { $key = $stdId; }
                $data[$key] = $orderedSubjects;
            }
        }

        foreach ($data as $key => $entries) {
            $isColl  = $entries instanceof \Illuminate\Support\Collection; $asArray = $isColl ? $entries->all() : $entries;
            if (!is_array($asArray)) { continue; }
            $filteredArray = array_filter($asArray, function($sub) {
                if (is_array($sub)) { $name = strtoupper(trim((string) ($sub['subject_name'] ?? ''))); } elseif (is_object($sub)) { $name = strtoupper(trim((string) ($sub->subject_name ?? ''))); } elseif (is_string($sub)) { $name = strtoupper(trim($sub)); } else { return true; }
                return $name !== 'STORY';
            });
            $data[$key] = $isColl ? collect(array_values($filteredArray)) : array_values($filteredArray);
        }

        return $data;
    }

    private function findSiblingAllocationIds(SkillTeacherClassAllocation $allocation): array
    {
        $examIds = $allocation->subjectAllocations->pluck('exam_master_id')->unique()->values()->all();
        if (empty($examIds)) { return [$allocation->id]; }

        $ids = SkillTeacherClassAllocation::where('user_id', $allocation->user_id)
            ->where('academic_year_id', $allocation->academic_year_id)
            ->where('standard_id', $allocation->standard_id)
            ->whereHas('subjectAllocations', function ($q) use ($examIds) { $q->whereIn('exam_master_id', $examIds); })
            ->pluck('id')->all();

        return !empty($ids) ? $ids : [$allocation->id];
    }
}