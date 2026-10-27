<?php

namespace App\Http\Controllers\Marks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\AcademicYear;
use App\Models\ExamMaster;
use App\Models\TeacherMarksStatus;
use App\Models\TeacherSubjectAllocation;
use App\Models\StudentMark;

use App\Helpers\StudentHelper;
use App\Helpers\MarksHelper;
use App\Helpers\ExamSubjectConfigHelper;

class MarkEntryController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $students = collect();
        $assignments = collect();
        $academicYears = collect();
        $exams = collect();
        $exam = null;
        $teacherSubjectAllocation = null;
        $selectedClassAllocation = null;
        $subjectConfig = null;
        $error = '';
        $message = '';
        $showTheory = false;
        $showOral = false;
        $showPractical = false;
        $theoryMaxMarks = 0;
        $theoryPassingMarks = 0;
        $oralMaxMarks = 0;
        $oralPassingMarks = 0;
        $practicalMaxMarks = 0;
        $practicalPassingMarks = 0;
        $totalMaxMarks = 0;
        $totalPassingMarks = 0;
        $marksLocked = false;
        $existingMarks = collect();
        $marksStatus = null;
        $isOptionalEnabled = false;
        $passingPercentage = 40;

        $user = Auth::user();
        if (!$user) abort(403);

        $userId = (int) Auth::id();
        $isAdministrator = MarksHelper::isAdministrator();

        $academicYearId = $request->input('academic_year_id');
        $examId = $request->input('exam_master_id');
        $tsaId = $request->input('teacher_subject_allocation_id');

        $academicYears = AcademicYear::query()
                ->where('is_active', 1)
                ->orderByDesc('id')
                ->get();

        $examQuery = ExamMaster::query()
                ->where('is_active', 1)
                ->orderBy('display_order')
                ->orderBy('exam_name');

        if ($academicYearId !== null && $academicYearId !== '') {
            $examQuery->where('academic_year_id', (int) $academicYearId);
        }

        $exams = $examQuery->get();

        if ($examId) {
            $exam = ExamMaster::where('id', (int) $examId)->where('is_active', 1)->first();

            if (!$exam) {
                $error = 'Selected exam was not found.';
            } else {
                $yearError = MarksHelper::validateExamAcademicYear($exam, null, $academicYearId);
                if ($yearError) {
                    $error = $yearError;
                    $exam = null;
                }
            }
        }

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

            $teacherSubjectAllocation = $tsaQuery->first();

            if (!$teacherSubjectAllocation) {
                $error = 'Selected teaching assignment was not found or is not assigned to you.';
            } else {
                $selectedClassAllocation = $teacherSubjectAllocation->allocation;

                if (!$selectedClassAllocation) {
                    $teacherSubjectAllocation = null;
                    $error = 'Teacher class allocation not found.';
                } else {
                    $isOptionalEnabled = MarksHelper::isOptionalEnabledForAllocation($selectedClassAllocation);
                    $passingPercentage = MarksHelper::getPassingPercentage($selectedClassAllocation->standard_id);

                    $resolvedYear = $selectedClassAllocation->academic_year_id ?? null;
                    if ($resolvedYear !== null && $resolvedYear !== '') {
                        $academicYearId = (int) $resolvedYear;
                        $request->merge(['academic_year_id' => $academicYearId]);
                    }

                    $tsaExam = $teacherSubjectAllocation->exam;
                    if (!$tsaExam) {
                        $error = 'Exam linked to the selected teaching assignment was not found.';
                    } else {
                        $yearError = MarksHelper::validateExamAcademicYear($tsaExam, $selectedClassAllocation, $academicYearId);
                        if ($yearError) {
                            $error = $yearError;
                        } else {
                            $exam = $tsaExam;
                            if (!$exams->contains('id', $exam->id)) {
                                $exams->push($exam);
                                $exams = $exams->sortBy([
                                    ['display_order', 'asc'],
                                    ['exam_name', 'asc'],
                                ])->values();
                            }
                        }
                    }
                }
            }
        }

        if ($exam && !$teacherSubjectAllocation) {
            $yearError = MarksHelper::validateExamAcademicYear($exam, null, $academicYearId);
            if ($yearError) {
                $error = $yearError;
                $exam = null;
            }
        }

        $assignmentQuery = TeacherSubjectAllocation::query()
                ->with([
                    'allocation.teacher',
                    'allocation.academicYear',
                    'allocation.section',
                    'allocation.standard',
                    'allocation.division',
                    'exam',
                ]);

        if ($examId) $assignmentQuery->where('exam_master_id', (int) $examId);

        if ($academicYearId !== null && $academicYearId !== '') {
            $assignmentQuery->whereHas('allocation', function ($query) use ($academicYearId) {
                    $query->where('academic_year_id', (int) $academicYearId);
                });
        }

        if (!$isAdministrator) {
            $assignmentQuery->whereHas('allocation', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                });
        }

        $assignments = $assignmentQuery->orderByDesc('id')->get();

        $assignments = $assignments->filter(function ($assignment) {
                    $allocation = $assignment->allocation;
                    $assignmentExam = $assignment->exam;
                    if (!$allocation || !$assignmentExam) return false;
                    if ($assignmentExam->academic_year_id === null) return true;
                    return (int) $assignmentExam->academic_year_id === (int) $allocation->academic_year_id;
                })->values();

        $assignmentIds = $assignments->pluck('id')->filter()->unique()->values();
        $allStatuses = collect();

        if ($assignmentIds->isNotEmpty()) {
            $statusQuery = TeacherMarksStatus::query()
                    ->whereIn('teacher_subject_allocation_id', $assignmentIds);

            if (!$isAdministrator) $statusQuery->where('teacher_id', $userId);
            if ($academicYearId !== null && $academicYearId !== '') {
                $statusQuery->where('academic_year_id', (int) $academicYearId);
            }

            $allStatuses = $statusQuery->get([
                    'id', 'teacher_subject_allocation_id', 'subject_id', 'teacher_id',
                    'exam_master_id', 'standard_id', 'division_id', 'academic_year_id', 'status',
                ]);
        }

        $standardIds = $assignments->pluck('allocation.standard_id')->filter()->unique()->values();
        $allSubjects = collect();

        if ($standardIds->isNotEmpty()) {
            $allSubjects = DB::table('subjects as s')
                ->join('standard_wise_subjects as sws', 'sws.subject_id', '=', 's.id')
                ->whereIn('sws.standard_id', $standardIds)
                ->where('s.is_active', 1)
                ->where('sws.is_active', 1)
                ->select(['s.id', 's.subject_name', 's.subject_code', 's.short_name'])
                ->distinct()
                ->get()
                ->keyBy('id');
        }

        $subjectMap = MarksHelper::buildSubjectResolutionMap($assignments);

        $statusMap = $allStatuses->keyBy(function ($status) {
                    return $status->teacher_subject_allocation_id . ':' . $status->exam_master_id;
                });

        foreach ($assignments as $assignment) {
            $allocation = $assignment->allocation;
            if (!$allocation) continue;

            $statusKey = $assignment->id . ':' . $assignment->exam_master_id;
            $status = $statusMap->get($statusKey);
            if (!$status) {
                $status = $allStatuses->firstWhere('teacher_subject_allocation_id', $assignment->id);
            }

            $tmsSubjectId = $status ? $status->subject_id : null;

            $actualSubject = MarksHelper::resolveDisplaySubject(
                    $assignment->subject_id,
                    $allocation->standard_id,
                    $tmsSubjectId,
                    $subjectMap,
                    $allSubjects
                );

            if ($actualSubject) {
                $assignment->setRelation('subject', $actualSubject);
                $assignment->resolved_subject_id = (int) $actualSubject->id;
            }

            $assignment->resolved_academic_year_id = $allocation->academic_year_id;
            $assignment->resolved_class_allocation_id = $assignment->teacher_class_allocation_id;
            $assignment->resolved_exam_master_id = $assignment->exam_master_id;
            $assignment->resolved_standard_id = $allocation->standard_id;
            $assignment->resolved_division_id = $allocation->division_id;
            $assignment->resolved_status = $status ? strtoupper(trim((string) ($status->status ?? ''))) : null;
        }

        if ($teacherSubjectAllocation) {
            $selectedAllocation = $teacherSubjectAllocation->allocation;

            $isOptionalEnabled = MarksHelper::isOptionalEnabledForAllocation($selectedAllocation);
            $passingPercentage = MarksHelper::getPassingPercentage($selectedAllocation->standard_id);

            $statusKey = $teacherSubjectAllocation->id . ':' . $teacherSubjectAllocation->exam_master_id;
            $marksStatus = $statusMap->get($statusKey);

            if (!$marksStatus) {
                $marksStatus = TeacherMarksStatus::query()
                        ->where('teacher_subject_allocation_id', $teacherSubjectAllocation->id)
                        ->where('exam_master_id', $teacherSubjectAllocation->exam_master_id)
                        ->when(!$isAdministrator, function ($query) use ($userId) {
                                $query->where('teacher_id', $userId);
                            })
                        ->when($academicYearId !== null && $academicYearId !== '', function ($query) use ($academicYearId) {
                                $query->where('academic_year_id', (int) $academicYearId);
                            })
                        ->orderByDesc('id')
                        ->first();
            }

            $tmsSubjectId = $marksStatus ? $marksStatus->subject_id : null;

            $actualSubject = MarksHelper::resolveDisplaySubject(
                    $teacherSubjectAllocation->subject_id,
                    $selectedAllocation->standard_id,
                    $tmsSubjectId,
                    $subjectMap,
                    $allSubjects
                );

            if ($actualSubject) {
                $teacherSubjectAllocation->setRelation('subject', $actualSubject);
                $teacherSubjectAllocation->resolved_subject_id = (int) $actualSubject->id;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | SUBJECT CONFIG — single source of truth (ExamSubjectConfigHelper)
        |--------------------------------------------------------------------------
        */
        if ($exam && $teacherSubjectAllocation && $selectedClassAllocation) {

            $candidateSubjectIds = array_filter([
                $teacherSubjectAllocation->resolved_subject_id ?? null,
                $teacherSubjectAllocation->subject_id ?? null,
            ]);

            $subjectConfig = ExamSubjectConfigHelper::resolveExamSubjectConfig(
                (int) $exam->id,
                (int) $selectedClassAllocation->standard_id,
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

            Log::info('Marks Entry — Subject Config Resolved', [
                'exam_id'            => $exam->id,
                'standard_id'        => $selectedClassAllocation->standard_id,
                'candidate_ids'      => $candidateSubjectIds,
                'matched_subject_id' => $subjectConfig->subject_id ?? null,
                'show_theory'        => $showTheory,
                'show_oral'          => $showOral,
                'show_practical'     => $showPractical,
                'total_max'          => $totalMaxMarks,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | LOAD STUDENTS
        |--------------------------------------------------------------------------
        */
        if ($teacherSubjectAllocation && $selectedClassAllocation && $exam && !$error) {
            $allocation = $selectedClassAllocation;

            $isOptionalEnabled = MarksHelper::isOptionalEnabledForAllocation($allocation);
            $passingPercentage = MarksHelper::getPassingPercentage($allocation->standard_id);

            $yearError = MarksHelper::validateExamAcademicYear($exam, $allocation, $academicYearId);

            if ($yearError) {
                $error = $yearError;
                $students = collect();
            } else {
                $erpAcademicYearId = (int) ($allocation->academic_year_id ?? 0);
                $erpStandardId = (int) ($allocation->standard_id ?? 0);
                $erpDivisionId = (int) ($allocation->division_id ?? 0);

                try {
                    if ($erpAcademicYearId > 0 && $erpStandardId > 0 && $erpDivisionId > 0) {
                        $students = StudentHelper::getStudentsDirectERP(
                                $erpAcademicYearId, $erpStandardId, $erpDivisionId
                            );
                        $students = MarksHelper::sortStudentsByRoll($students);
                    }
                } catch (\Throwable $e) {
                    report($e);
                    $students = collect();
                    $error = 'Old ERP Error: ' . $e->getMessage();
                }

                if ($students->isEmpty()) {
                    $standardName = optional($allocation->standard)->standard_name ?? 'Selected Standard';
                    $divisionName = optional($allocation->division)->division_name ?? 'Selected Division';
                    $error = 'No students found for ' . $standardName . ' - ' . $divisionName . '.';
                }
            }
        }

        if ($teacherSubjectAllocation && $exam) {
            $currentStatus = strtoupper(trim((string) ($marksStatus->status ?? '')));
            if ($currentStatus === 'COMPLETED') {
                $marksLocked = true;
                $message = 'Marks entry has already been completed and is locked.';
            }
        }

        if ($teacherSubjectAllocation && $exam && $selectedClassAllocation && !$error) {
            $actualSubjectId = $teacherSubjectAllocation->subject_id;

            $existingMarks = StudentMark::query()
                    ->where('academic_year_id', $selectedClassAllocation->academic_year_id)
                    ->where('section_id', $selectedClassAllocation->section_id)
                    ->where('standard_id', $selectedClassAllocation->standard_id)
                    ->where('division_id', $selectedClassAllocation->division_id)
                    ->where('exam_master_id', $exam->id)
                    ->where('subject_id', $actualSubjectId)
                    ->get()
                    ->keyBy(function ($mark) { return (string) $mark->student_id; });

            if ($existingMarks->where('is_locked', 1)->isNotEmpty()) {
                $marksLocked = true;
            }
        }

        if ($academicYearId !== null && $academicYearId !== '') {
            $request->merge(['academic_year_id' => $academicYearId]);
        }

        return view('marks-entry.index', compact(
            'request', 'academicYears', 'academicYearId', 'assignments', 'exams',
            'students', 'exam', 'teacherSubjectAllocation', 'selectedClassAllocation',
            'subjectConfig', 'showTheory', 'showOral', 'showPractical',
            'marksLocked', 'message', 'error',
            'theoryMaxMarks', 'theoryPassingMarks',
            'oralMaxMarks', 'oralPassingMarks',
            'practicalMaxMarks', 'practicalPassingMarks',
            'existingMarks', 'isOptionalEnabled', 'passingPercentage',
            'totalMaxMarks', 'totalPassingMarks'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | VIEW (READ-ONLY)
    |--------------------------------------------------------------------------
    */
    public function view(Request $request)
    {
        $academicYears = collect();
        $exams         = collect();
        $assignments   = collect();

        $exam                    = null;
        $teacherSubjectAllocation = null;
        $selectedClassAllocation = null;
        $records                 = collect();
        $error                   = '';
        $showTheory              = false;
        $showOral                = false;
        $showPractical           = false;
        $marksLocked             = false;
        $totalMaxMarks           = 0;
        $totalPassingMarks       = 0;
        $theoryMaxMarks          = 0;
        $theoryPassingMarks      = 0;
        $oralMaxMarks            = 0;
        $oralPassingMarks        = 0;
        $practicalMaxMarks       = 0;
        $practicalPassingMarks   = 0;

        $user = Auth::user();
        if (!$user) abort(403);

        $userId          = (int) Auth::id();
        $isAdministrator = MarksHelper::isAdministrator();

        $academicYearId = $request->input('academic_year_id');
        $examId         = $request->input('exam_master_id');
        $tsaId          = $request->input('teacher_subject_allocation_id');

        $academicYears = AcademicYear::query()
            ->where('is_active', 1)
            ->orderByDesc('id')
            ->get();

        $examQuery = ExamMaster::query()
            ->where('is_active', 1)
            ->orderBy('display_order')
            ->orderBy('exam_name');

        if ($academicYearId !== null && $academicYearId !== '') {
            $examQuery->where('academic_year_id', (int) $academicYearId);
        }

        $exams = $examQuery->get();

        if ($examId) {
            $exam = ExamMaster::where('id', (int) $examId)->where('is_active', 1)->first();
            if (!$exam) $error = 'Selected exam was not found.';
        }

        if ($tsaId) {
            $tsaQuery = TeacherSubjectAllocation::query()
                ->with([
                    'allocation.teacher', 'allocation.academicYear', 'allocation.section',
                    'allocation.standard', 'allocation.division', 'exam',
                ])
                ->where('id', (int) $tsaId);

            if ($examId) $tsaQuery->where('exam_master_id', (int) $examId);
            if (!$isAdministrator) {
                $tsaQuery->whereHas('allocation', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                });
            }

            $teacherSubjectAllocation = $tsaQuery->first();

            if (!$teacherSubjectAllocation) {
                $error = 'Selected teaching assignment was not found.';
            } else {
                $selectedClassAllocation = $teacherSubjectAllocation->allocation;
                if (!$selectedClassAllocation) {
                    $teacherSubjectAllocation = null;
                    $error = 'Teacher class allocation not found.';
                } else {
                    $resolvedYear = $selectedClassAllocation->academic_year_id ?? null;
                    if ($resolvedYear !== null && $resolvedYear !== '') {
                        $academicYearId = (int) $resolvedYear;
                        $request->merge(['academic_year_id' => $academicYearId]);
                    }
                    if (!$exam) $exam = $teacherSubjectAllocation->exam;
                }
            }
        }

        $assignmentQuery = TeacherSubjectAllocation::query()
            ->with([
                'allocation.teacher', 'allocation.academicYear', 'allocation.section',
                'allocation.standard', 'allocation.division', 'exam',
            ]);

        if ($examId) $assignmentQuery->where('exam_master_id', (int) $examId);

        if ($academicYearId !== null && $academicYearId !== '') {
            $assignmentQuery->whereHas('allocation', function ($query) use ($academicYearId) {
                $query->where('academic_year_id', (int) $academicYearId);
            });
        }

        if (!$isAdministrator) {
            $assignmentQuery->whereHas('allocation', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            });
        }

        $assignments = $assignmentQuery->orderByDesc('id')->get();

        $assignments = $assignments->filter(function ($assignment) {
            $allocation = $assignment->allocation;
            $assignmentExam = $assignment->exam;
            if (!$allocation || !$assignmentExam) return false;
            if ($assignmentExam->academic_year_id === null) return true;
            return (int) $assignmentExam->academic_year_id === (int) $allocation->academic_year_id;
        })->values();

        /*
        |--------------------------------------------------------------------------
        | SUBJECT CONFIG — same source of truth as Marks Entry
        |--------------------------------------------------------------------------
        */
        if ($exam && $teacherSubjectAllocation && $selectedClassAllocation) {

            $candidateSubjectIds = array_filter([
                $teacherSubjectAllocation->resolved_subject_id ?? null,
                $teacherSubjectAllocation->subject_id ?? null,
            ]);

            $subjectConfig = ExamSubjectConfigHelper::resolveExamSubjectConfig(
                (int) $exam->id,
                (int) $selectedClassAllocation->standard_id,
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
        }

        if ($teacherSubjectAllocation && $exam && $selectedClassAllocation && !$error) {
            $records = StudentMark::query()
                ->where('academic_year_id', $selectedClassAllocation->academic_year_id)
                ->where('standard_id',     $selectedClassAllocation->standard_id)
                ->where('division_id',     $selectedClassAllocation->division_id)
                ->where('exam_master_id',  $exam->id)
                ->where('subject_id',      $teacherSubjectAllocation->subject_id)
                ->get();

            if ($records->where('is_locked', 1)->isNotEmpty()) {
                $marksLocked = true;
            }
        }

        if ($academicYearId !== null && $academicYearId !== '') {
            $request->merge(['academic_year_id' => $academicYearId]);
        }

        return view('marks-entry.view', compact(
            'request', 'academicYears', 'academicYearId', 'exams', 'assignments',
            'exam', 'teacherSubjectAllocation', 'selectedClassAllocation', 'records',
            'error', 'showTheory', 'showOral', 'showPractical', 'marksLocked',
            'totalMaxMarks', 'totalPassingMarks',
            'theoryMaxMarks', 'theoryPassingMarks',
            'oralMaxMarks', 'oralPassingMarks',
            'practicalMaxMarks', 'practicalPassingMarks'
        ));
    }
}