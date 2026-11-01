<?php

namespace App\Http\Controllers\Marks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\AcademicYear;
use App\Models\ExamMaster;
use App\Models\Standard;
use App\Models\Division;
use App\Models\ExamMasterSubject;
use App\Models\TeacherSubjectAllocation;
use App\Models\TeacherMarksStatus;
use App\Models\StudentMark;

use App\Helpers\StudentHelper;
use App\Helpers\ResultHelper;
use App\Helpers\ExamSubjectConfigHelper;

class MarkViewController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | ADMINISTRATOR DETECTION
    |--------------------------------------------------------------------------
    */
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

    /*
    |--------------------------------------------------------------------------
    | RESOLVE ACTUAL SUBJECT ID
    |--------------------------------------------------------------------------
    */
    private function resolveActualSubjectId($storedSubjectId, $standardId): ?int
    {
        if ($storedSubjectId === null || $storedSubjectId === '' || !$standardId) {
            return null;
        }

        $storedSubjectId = (int) $storedSubjectId;
        $standardId      = (int) $standardId;

        if ($storedSubjectId <= 0 || $standardId <= 0) return null;

        /* CURRENT FORMAT */
        $currentSubject = DB::table('subjects')
            ->where('id', $storedSubjectId)
            ->where('is_active', 1)
            ->first();

        if ($currentSubject) {
            $mappingExists = DB::table('standard_wise_subjects')
                ->where('standard_id', $standardId)
                ->where('subject_id', $storedSubjectId)
                ->where('is_active', 1)
                ->exists();

            if ($mappingExists) return $storedSubjectId;
        }

        /* LEGACY FORMAT */
        $legacyMapping = DB::table('standard_wise_subjects')
            ->where('id', $storedSubjectId)
            ->where('standard_id', $standardId)
            ->where('is_active', 1)
            ->first();

        if ($legacyMapping && !empty($legacyMapping->subject_id)) {
            $actualSubject = DB::table('subjects')
                ->where('id', (int) $legacyMapping->subject_id)
                ->where('is_active', 1)
                ->first();

            if ($actualSubject) return (int) $actualSubject->id;
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | RESOLVE DISPLAY SUBJECT
    |--------------------------------------------------------------------------
    */
    private function resolveDisplaySubject($storedSubjectId, $standardId, $tmsSubjectId = null)
    {
        $standardId = (int) $standardId;
        if ($standardId <= 0) return null;

        /* PRIMARY - TSA SUBJECT */
        if ($storedSubjectId !== null && $storedSubjectId !== '' && (int) $storedSubjectId > 0) {
            $actualSubjectId = $this->resolveActualSubjectId($storedSubjectId, $standardId);
            if ($actualSubjectId) {
                $subject = DB::table('subjects')
                    ->where('id', $actualSubjectId)
                    ->where('is_active', 1)
                    ->first();
                if ($subject) return $subject;
            }
        }

        /* FALLBACK - TMS SUBJECT */
        if ($tmsSubjectId !== null && $tmsSubjectId !== '' && (int) $tmsSubjectId > 0) {
            $tmsSubjectId = (int) $tmsSubjectId;

            $subject = DB::table('subjects')
                ->where('id', $tmsSubjectId)
                ->where('is_active', 1)
                ->first();

            if ($subject) {
                $mappingExists = DB::table('standard_wise_subjects')
                    ->where('standard_id', $standardId)
                    ->where('subject_id', $tmsSubjectId)
                    ->where('is_active', 1)
                    ->exists();

                if ($mappingExists) return $subject;
            }

            $legacyMapping = DB::table('standard_wise_subjects as sws')
                ->leftJoin('subjects as s', 's.id', '=', 'sws.subject_id')
                ->where('sws.id', $tmsSubjectId)
                ->where('sws.standard_id', $standardId)
                ->where('sws.is_active', 1)
                ->where('s.is_active', 1)
                ->select(['s.id', 's.subject_name', 's.subject_code', 's.short_name'])
                ->first();

            if ($legacyMapping) return $legacyMapping;
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | VIEW MARKS
    |--------------------------------------------------------------------------
    */
    public function viewMarks(Request $request)
    {
        $records         = collect();
        $subjects        = collect();
        $exam            = null;
        $selectedTsa     = null;
        $selectedSubject = null;
        $showTheory      = false;
        $showOral        = false;
        $showPractical   = false;
        $error           = null;
        $passingPercentage = 40;

        /* Component config defaults */
        $theoryMaxMarks        = 0;
        $theoryPassingMarks    = 0;
        $oralMaxMarks          = 0;
        $oralPassingMarks      = 0;
        $practicalMaxMarks     = 0;
        $practicalPassingMarks = 0;
        $totalMaxMarks         = 0;
        $totalPassingMarks     = 0;

        $user = Auth::user();
        if (!$user) abort(403);

        $userId = (int) $user->id;
        $isAdministrator = $this->isAdministrator();

        $academicYearId = $request->input('academic_year_id');
        $examId         = $request->input('exam_master_id');
        $tsaId          = $request->input('teacher_subject_allocation_id');

        /* ACADEMIC YEARS */
        $academicYears = AcademicYear::query()
            ->where('is_active', 1)
            ->orderByDesc('id')
            ->get();

        /* EXAMS */
        $examQuery = ExamMaster::query()
            ->where('is_active', 1)
            ->orderBy('display_order')
            ->orderBy('exam_name');

        if ($academicYearId !== null && $academicYearId !== '') {
            $examQuery->where('academic_year_id', (int) $academicYearId);
        }

        $exams = $examQuery->get();

        /* STANDARDS / DIVISIONS */
        $standards = Standard::query()->where('is_active', 1)->orderBy('display_order')->get();
        $divisions = Division::query()->where('is_active', 1)->orderBy('division_name')->get();

        /* SELECTED EXAM */
        if ($examId) {
            $exam = $exams->firstWhere('id', (int) $examId);

            if (!$exam) {
                $error = 'Selected exam was not found.';
            }
        }

        /* SELECTED TEACHER SUBJECT ALLOCATION */
        if ($tsaId) {
            $tsaQuery = TeacherSubjectAllocation::query()
                ->with([
                    'allocation.teacher',
                    'allocation.academicYear',
                    'allocation.section',
                    'allocation.standard',
                    'allocation.division',
                    'exam',
                ])
                ->where('id', (int) $tsaId);

            if ($examId) $tsaQuery->where('exam_master_id', (int) $examId);

            if (!$isAdministrator) {
                $tsaQuery->whereHas('allocation', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                });
            }

            $selectedTsa = $tsaQuery->first();

            if (!$selectedTsa) {
                $error = 'Selected teaching assignment was not found or is not assigned to you.';
            }
        }

        /* CLASS ALLOCATION */
        $allocation = $selectedTsa ? $selectedTsa->allocation : null;

        if ($selectedTsa && !$allocation) {
            $selectedTsa = null;
            $error = 'Teacher class allocation was not found.';
        }

        /* STANDARD / DIVISION / ACADEMIC YEAR */
        $standardId       = $allocation ? (int) $allocation->standard_id       : 0;
        $divisionId       = $allocation ? (int) $allocation->division_id       : 0;
        $allocationYearId = $allocation ? (int) $allocation->academic_year_id  : 0;

        /* SYNC ACADEMIC YEAR FROM TSA */
        if ($allocationYearId > 0 && ($academicYearId === null || $academicYearId === '')) {
            $academicYearId = $allocationYearId;
            $request->merge(['academic_year_id' => $academicYearId]);
        }

        /* PASSING PERCENTAGE */
        if ($standardId > 0) {
            $passingPercentage = ResultHelper::getPassingPercentage($standardId);
        }

        /* RESOLVE SUBJECT */
        if ($selectedTsa && $allocation) {
            $statusQuery = TeacherMarksStatus::query()
                ->where('teacher_subject_allocation_id', $selectedTsa->id)
                ->where('exam_master_id', $selectedTsa->exam_master_id);

            if (!$isAdministrator) {
                $statusQuery->where('teacher_id', $userId);
            }

            $status = $statusQuery->first();

            $tmsSubjectId = $status ? $status->subject_id : null;

            $selectedSubject = $this->resolveDisplaySubject(
                $selectedTsa->subject_id,
                $standardId,
                $tmsSubjectId
            );

            if ($selectedSubject) {
                $selectedTsa->setRelation('subject', $selectedSubject);
                $selectedTsa->resolved_subject_id = (int) $selectedSubject->id;
            } else {
                $error = 'Subject could not be resolved from the selected teaching assignment.';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | SUBJECT CONFIG — single source of truth
        |--------------------------------------------------------------------------
        */
        if ($exam && $selectedTsa && $allocation) {

            $candidateSubjectIds = array_filter([
                $selectedTsa->resolved_subject_id ?? null,
                $selectedTsa->subject_id ?? null,
            ]);

            $subjectConfig = ExamSubjectConfigHelper::resolveExamSubjectConfig(
                (int) $exam->id,
                (int) $allocation->standard_id,
                $candidateSubjectIds
            );

            $flags = ExamSubjectConfigHelper::extractComponentFlags($subjectConfig);

            $showTheory            = $flags['showTheory'];
            $showOral              = $flags['showOral'];
            $showPractical         = $flags['showPractical'];
            $theoryMaxMarks        = $flags['theoryMaxMarks'];
            $theoryPassingMarks    = $flags['theoryPassingMarks'];
            $oralMaxMarks          = $flags['oralMaxMarks'];
            $oralPassingMarks      = $flags['oralPassingMarks'];
            $practicalMaxMarks     = $flags['practicalMaxMarks'];
            $practicalPassingMarks = $flags['practicalPassingMarks'];
            $totalMaxMarks         = $flags['totalMaxMarks'];
            $totalPassingMarks     = $flags['totalPassingMarks'];

            if (!$subjectConfig) {
                $error = 'Marks configuration not found for '
                       . ($selectedSubject->subject_name ?? 'Selected Subject') . '.';
            }
        }

        /* ONLY SELECTED SUBJECT (dropdown display) */
        if (isset($subjectConfig) && $subjectConfig) {
            $subjects = ExamMasterSubject::query()
                ->join('subjects', 'subjects.id', '=', 'exam_master_subjects.subject_id')
                ->where('exam_master_subjects.id', $subjectConfig->id)
                ->where('subjects.is_active', 1)
                ->select([
                    'subjects.id',
                    'subjects.subject_name',
                    'subjects.subject_code',
                    'subjects.short_name',
                    'exam_master_subjects.max_marks',
                    'exam_master_subjects.passing_marks',
                    'exam_master_subjects.display_order',
                ])
                ->get();

            $subjects = $subjects->map(function ($subject) use ($standardId) {
                $subject->passing_marks = ResultHelper::getPassingMarks($standardId, $subject->max_marks);
                return $subject;
            });
        }

        /* LOAD ASSIGNMENTS (for filter dropdown) */
        $assignmentQuery = TeacherSubjectAllocation::query()
            ->with([
                'allocation.teacher',
                'allocation.academicYear',
                'allocation.section',
                'allocation.standard',
                'allocation.division',
                'exam',
                'subject',
            ]);

        if ($examId) $assignmentQuery->where('exam_master_id', (int) $examId);

        if ($academicYearId !== null && $academicYearId !== '') {
            $assignmentQuery->whereHas('allocation', function ($q) use ($academicYearId) {
                $q->where('academic_year_id', (int) $academicYearId);
            });
        }

        if (!$isAdministrator) {
            $assignmentQuery->whereHas('allocation', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }

        $assignments = $assignmentQuery->orderByDesc('id')->get();

        /* LOAD STUDENTS EXACTLY LIKE MARK ENTRY */
        $students = collect();

        if ($allocation && $exam && $selectedTsa) {
            try {
                $students = StudentHelper::getStudentsDirectERP(
                    $allocation->academic_year_id,
                    $allocation->standard_id,
                    $allocation->division_id
                );

                $students = $students->sort(function ($a, $b) {
                    $genderA = strtoupper(trim($a->gender ?? ''));
                    $genderB = strtoupper(trim($b->gender ?? ''));

                    if ($genderA !== $genderB) {
                        if (in_array($genderA, ['F', 'FEMALE'], true)) return -1;
                        if (in_array($genderB, ['F', 'FEMALE'], true)) return 1;
                    }

                    return strcmp(
                        strtoupper(trim($a->studname ?? '')),
                        strtoupper(trim($b->studname ?? ''))
                    );
                })->values();
            } catch (\Throwable $e) {
                report($e);
                $students = collect();
                $error = 'Old ERP Error: ' . $e->getMessage();
            }
        }

                /* LOAD SAVED MARKS */
        $existingMarks = collect();

        if ($selectedTsa && $exam && $allocation) {
            $actualSubjectId = $selectedSubject->id ?? $selectedTsa->subject_id;
            
            $existingMarks = StudentMark::query()
                ->where('academic_year_id', $allocation->academic_year_id)
                ->where('section_id', $allocation->section_id)
                ->where('standard_id', $allocation->standard_id)
                ->where('division_id', $allocation->division_id)
                ->where('exam_master_id', $exam->id)
                ->where('subject_id', $actualSubjectId)
                ->get()
                ->keyBy('student_id');
        }

        /* BUILD VIEW RECORDS */
        foreach ($students as $student) {
            $studentId = (int) (
                $student->Studentid
                ?? $student->studentid
                ?? $student->student_id
                ?? $student->id
                ?? 0
            );

            if ($studentId <= 0) continue;

            $savedMark = $existingMarks->get($studentId);
            if (!$savedMark) continue;

            $studName   = trim((string) ($student->studname   ?? ''));
            $fatherName = trim((string) ($student->fathername ?? ''));

            $records->push((object) [
                'id'         => $savedMark->id,
                'student_id' => $studentId,
                'studname'   => $studName,
                'fathername' => $fatherName,
                'regno'      => $student->regno ?? $student->registration_no ?? $student->gr_no ?? '',
                'rollno'     => $student->rollno ?? $student->roll_no ?? '',
                'gender'     => $student->gender ?? '',

                'is_absent'   => (int) ($savedMark->is_absent   ?? 0),
                'is_optional' => (int) ($savedMark->is_optional ?? 0),
                'status'      => $savedMark->status ?? null,

                'theory_max_marks' => $savedMark->theory_max_marks
                    ?? ($subjectConfig->theory_max_marks ?? 0),

                'theory_passing_marks' => $savedMark->theory_passing_marks
                    ?? ($subjectConfig->theory_passing_marks ?? 0),

                'theory_obtained_marks' => $savedMark->theory_obtained_marks ?? null,

                'oral_max_marks' => $savedMark->oral_max_marks
                    ?? ($subjectConfig->oral_max_marks ?? 0),

                'oral_passing_marks' => $savedMark->oral_passing_marks
                    ?? ($subjectConfig->oral_passing_marks ?? 0),

                'oral_obtained_marks' => $savedMark->oral_obtained_marks ?? null,

                'practical_max_marks' => $savedMark->practical_max_marks
                    ?? ($subjectConfig->practical_max_marks ?? 0),

                'practical_passing_marks' => $savedMark->practical_passing_marks
                    ?? ($subjectConfig->practical_passing_marks ?? 0),

                'practical_obtained_marks' => $savedMark->practical_obtained_marks ?? null,

                'subject_name' => $selectedSubject->subject_name ?? '',
                'subject_code' => $selectedSubject->subject_code ?? '',
                'exam_name'    => $exam->exam_name ?? '',
            ]);
        }

        /* SORT RECORDS */
        $records = $records->sortBy(function ($student) {
            $rollNo = trim((string) ($student->rollno ?? ''));

            if ($rollNo !== '' && is_numeric($rollNo)) {
                return [0, (int) $rollNo];
            }

            return [1, strtoupper($rollNo)];
        })->values();

        /* RETURN VIEW */
        return view('marks-entry.view', compact(
            'records',
            'standards',
            'divisions',
            'subjects',
            'exams',
            'exam',
            'showTheory',
            'showOral',
            'showPractical',
            'selectedTsa',
            'selectedSubject',
            'error',
            'passingPercentage',
            'academicYears',
            'assignments',
            'academicYearId',

            /* ⭐ Component flags + max/pass */
            'theoryMaxMarks', 'theoryPassingMarks',
            'oralMaxMarks',   'oralPassingMarks',
            'practicalMaxMarks', 'practicalPassingMarks',
            'totalMaxMarks', 'totalPassingMarks'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | SEARCH MARKS
    |--------------------------------------------------------------------------
    */
    public function searchMarks(Request $request)
    {
        return $this->viewMarks($request);
    }
}