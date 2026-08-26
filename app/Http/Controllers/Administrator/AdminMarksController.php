<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Subject;
use App\Models\User;
use App\Models\Standard;
use App\Models\Division;
use App\Models\AcademicYear;
use App\Models\ExamMaster;
use App\Models\StudentMark;
use App\Models\TeacherClassAllocation;
use App\Models\TeacherSubjectAllocation;
use App\Models\TeacherMarksStatus;
use App\Models\MarkAuditLog;
use App\Models\ExamMasterSubject;

use App\Helpers\StudentHelper;

class AdminMarksController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | ADMINISTRATOR
    |--------------------------------------------------------------------------
    */

    private function isAdministrator(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasRole')) {

            if (
                $user->hasRole('Administrator') ||
                $user->hasRole('admin')
            ) {
                return true;
            }
        }

        return in_array(
            strtolower(
                trim(
                    (string) ($user->role ?? '')
                )
            ),
            [
                'administrator',
                'admin',
            ],
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | NORMALIZE TEXT
    |--------------------------------------------------------------------------
    */

    private function normalizeText($value): string
    {
        return preg_replace(
            '/[^A-Z0-9]/',
            '',
            strtoupper(
                trim(
                    (string) $value
                )
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE ACTUAL SUBJECT ID
    |--------------------------------------------------------------------------
    |
    | Supports:
    |
    | CURRENT:
    |     stored ID = subjects.id
    |
    | LEGACY:
    |     stored ID = standard_wise_subjects.id
    |
    */

    private function resolveActualSubjectId(
        $storedSubjectId,
        $standardId
    ) {
        $storedSubjectId = (int) $storedSubjectId;
        $standardId = (int) $standardId;

        if (
            $storedSubjectId <= 0 ||
            $standardId <= 0
        ) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | CURRENT FORMAT
        |--------------------------------------------------------------------------
        */

        $current = DB::table(
            'standard_wise_subjects'
        )
            ->where(
                'standard_id',
                $standardId
            )
            ->where(
                'subject_id',
                $storedSubjectId
            )
            ->where(
                'is_active',
                1
            )
            ->first();

        if ($current) {
            return (int) $current->subject_id;
        }

        /*
        |--------------------------------------------------------------------------
        | LEGACY FORMAT
        |--------------------------------------------------------------------------
        */

        $legacy = DB::table(
            'standard_wise_subjects'
        )
            ->where(
                'id',
                $storedSubjectId
            )
            ->where(
                'standard_id',
                $standardId
            )
            ->where(
                'is_active',
                1
            )
            ->first();

        if ($legacy) {
            return (int) $legacy->subject_id;
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | GET ACTUAL SUBJECT
    |--------------------------------------------------------------------------
    */

    private function getActualSubject(
        $subjectId,
        $standardId
    ) {
        static $cache = [];

        $cacheKey =
            (int) $standardId
            . ':'
            . (int) $subjectId;

        if (
            array_key_exists(
                $cacheKey,
                $cache
            )
        ) {
            return $cache[$cacheKey];
        }

        $actualSubjectId =
            $this->resolveActualSubjectId(
                $subjectId,
                $standardId
            );

        if (!$actualSubjectId) {

            $cache[$cacheKey] = null;

            return null;
        }

        $subject =
            Subject::where(
                'id',
                $actualSubjectId
            )
            ->where(
                'is_active',
                1
            )
            ->first([
                'id',
                'subject_name',
                'subject_code',
                'short_name',
            ]);

        $cache[$cacheKey] = $subject;

        return $subject;
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE EXAM STANDARD
    |--------------------------------------------------------------------------
    */

    private function resolveExamStandard(
        ExamMaster $exam,
        $standards
    ) {
        if ($exam->standard_id) {

            $standard =
                $standards->firstWhere(
                    'id',
                    (int) $exam->standard_id
                );

            if ($standard) {

                return [
                    'id' =>
                        (int) $standard->id,

                    'name' =>
                        $standard->standard_name,
                ];
            }
        }

        $normalizedExam =
            $this->normalizeText(
                $exam->exam_name
            );

        $sortedStandards =
            $standards->sortByDesc(
                function ($standard) {

                    return strlen(
                        $this->normalizeText(
                            $standard->standard_name
                        )
                    );
                }
            );

        foreach (
            $sortedStandards as $standard
        ) {

            $normalizedStandard =
                $this->normalizeText(
                    $standard->standard_name
                );

            if (
                $normalizedStandard !== ''
                &&
                (
                    str_ends_with(
                        $normalizedExam,
                        $normalizedStandard
                    )
                    ||
                    str_contains(
                        $normalizedExam,
                        $normalizedStandard
                    )
                )
            ) {

                return [
                    'id' =>
                        (int) $standard->id,

                    'name' =>
                        $standard->standard_name,
                ];
            }
        }

        return [
            'id' => null,
            'name' => null,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | PREPARE EXAMS
    |--------------------------------------------------------------------------
    */

    private function prepareExams(
        $exams,
        $standards
    ) {
        foreach ($exams as $exam) {

            $resolved =
                $this->resolveExamStandard(
                    $exam,
                    $standards
                );

            $exam->resolved_standard_id =
                $resolved['id'];

            $exam->resolved_standard_name =
                $resolved['name'];

            $examName =
                trim(
                    (string) $exam->exam_name
                );

            $standardName =
                trim(
                    (string) (
                        $resolved['name'] ?? ''
                    )
                );

            if (
                $standardName !== ''
                &&
                !str_ends_with(
                    $this->normalizeText($examName),
                    $this->normalizeText($standardName)
                )
            ) {

                $exam->display_exam_name =
                    $examName
                    . ' - '
                    . $standardName;

            } else {

                $exam->display_exam_name =
                    $examName;
            }
        }

        return $exams;
    }


    /*
    |--------------------------------------------------------------------------
    | EXAM ACADEMIC YEAR VALIDATION
    |--------------------------------------------------------------------------
    |
    | New exams have:
    |
    |     exam_masters.academic_year_id
    |
    | Old exams may have NULL.
    |
    */

    private function validateExamAcademicYear(
        $exam,
        $requestedAcademicYearId = null,
        $allocation = null,
        $status = null
    ): ?string {

        if (!$exam) {
            return 'Selected Exam was not found.';
        }

        $examYear =
            $exam->academic_year_id !== null
            &&
            $exam->academic_year_id !== ''
                ? (int) $exam->academic_year_id
                : null;

        if (
            $requestedAcademicYearId !== null
            &&
            $requestedAcademicYearId !== ''
            &&
            $examYear !== null
            &&
            $examYear !== (int) $requestedAcademicYearId
        ) {

            return
                'Selected Exam does not belong to the selected Academic Year.';
        }

        if ($allocation) {

            $allocationYear =
                $allocation->academic_year_id !== null
                &&
                $allocation->academic_year_id !== ''
                    ? (int) $allocation->academic_year_id
                    : null;

            if (
                $examYear !== null
                &&
                $allocationYear !== null
                &&
                $examYear !== $allocationYear
            ) {

                return
                    'Selected Exam does not belong to the Academic Year of the selected Teacher Class Allocation.';
            }
        }

        if ($status) {

            $statusYear =
                $status->academic_year_id !== null
                &&
                $status->academic_year_id !== ''
                    ? (int) $status->academic_year_id
                    : null;

            if (
                $examYear !== null
                &&
                $statusYear !== null
                &&
                $examYear !== $statusYear
            ) {

                return
                    'Selected Exam does not belong to the Academic Year recorded in Teacher Marks Status.';
            }
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE SUBJECT FOR ASSIGNMENT
    |--------------------------------------------------------------------------
    |
    | This is deliberately lightweight.
    |
    | Historical student_marks are NOT queried here during initial page load.
    |
    */

    private function resolveSubjectForAssignment(
        $statusSubjectId,
        $tsaSubjectId,
        $standardId
    ) {

        /*
        |--------------------------------------------------------------------------
        | TMS FIRST
        |--------------------------------------------------------------------------
        */

        if ($statusSubjectId) {

            $subject =
                $this->getActualSubject(
                    $statusSubjectId,
                    $standardId
                );

            if ($subject) {
                return $subject;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | TSA SECOND
        |--------------------------------------------------------------------------
        */

        if ($tsaSubjectId) {

            $subject =
                $this->getActualSubject(
                    $tsaSubjectId,
                    $standardId
                );

            if ($subject) {
                return $subject;
            }
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | BUILD ASSIGNMENT
    |--------------------------------------------------------------------------
    */

    private function buildAssignmentFromData(
        $status,
        $tsa,
        $exam,
        $subject
    ) {
        if (
            !$status ||
            !$tsa ||
            !$exam ||
            !$subject ||
            !$tsa->allocation
        ) {
            return null;
        }

        $allocation =
            $tsa->allocation;

        $assignment =
            new TeacherSubjectAllocation();

        $assignment->id =
            (int) $tsa->id;

        $assignment->teacher_class_allocation_id =
            (int) $tsa->teacher_class_allocation_id;

        $assignment->exam_master_id =
            (int) $status->exam_master_id;

        /*
        |--------------------------------------------------------------------------
        | ALWAYS ACTUAL SUBJECT MASTER ID
        |--------------------------------------------------------------------------
        */

        $assignment->subject_id =
            (int) $subject->id;

        $assignment->setRelation(
            'allocation',
            $allocation
        );

        $assignment->setRelation(
            'subject',
            $subject
        );

        $assignment->setRelation(
            'exam',
            $exam
        );

        $assignment->resolved_subject_id =
            (int) $subject->id;

        $assignment->resolved_academic_year_id =
            (int) $allocation->academic_year_id;

        $assignment->resolved_class_allocation_id =
            (int) $allocation->id;

        $assignment->resolved_exam_master_id =
            (int) $exam->id;

        $assignment->resolved_standard_id =
            (int) $allocation->standard_id;

        $assignment->resolved_division_id =
            (int) $allocation->division_id;

        $assignment->resolved_teacher_id =
            (int) $allocation->user_id;

        $assignment->resolved_tms_subject_id =
            (int) $status->subject_id;

        $assignment->resolved_status =
            strtoupper(
                trim(
                    (string) (
                        $status->status
                        ??
                        'PENDING'
                    )
                )
            );

        $assignment->resolved_status_id =
            $status->id;

        $assignment->is_historical =
            false;

        $assignment->resolved_selection_key =
            $tsa->id
            . '|'
            . $subject->id;

        return $assignment;
    }


    /*
    |--------------------------------------------------------------------------
    | GET CURRENT ASSIGNMENTS - OPTIMIZED
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | Only teacher_marks_status is queried here.
    |
    | student_marks is NOT scanned.
    |
    */

    private function getAssignments(
        $academicYearId = null,
        $examId = null
    ) {
        $assignments =
            collect();

        /*
        |--------------------------------------------------------------------------
        | WITHOUT EXAM / YEAR
        |--------------------------------------------------------------------------
        |
        | Returning all historical/current records makes the page slow.
        |
        | We intentionally return no assignments until an Exam is selected.
        |
        */

        if (
            !$academicYearId &&
            !$examId
        ) {
            return $assignments;
        }

        /*
        |--------------------------------------------------------------------------
        | TEACHER MARK STATUSES
        |--------------------------------------------------------------------------
        */

        $statusQuery =
            TeacherMarksStatus::query()
            ->when(
                $academicYearId,
                function ($query) use (
                    $academicYearId
                ) {

                    $query->where(
                        'academic_year_id',
                        $academicYearId
                    );
                }
            )
            ->when(
                $examId,
                function ($query) use (
                    $examId
                ) {

                    $query->where(
                        'exam_master_id',
                        $examId
                    );
                }
            )
            ->select([
                'id',
                'academic_year_id',
                'exam_master_id',
                'teacher_subject_allocation_id',
                'teacher_id',
                'standard_id',
                'division_id',
                'subject_id',
                'status',
            ])
            ->orderByDesc('id');

        $statuses =
            $statusQuery->get();

        if (
            $statuses->isEmpty()
        ) {
            return $assignments;
        }

        /*
        |--------------------------------------------------------------------------
        | BATCH LOAD TSA
        |--------------------------------------------------------------------------
        */

        $tsaIds =
            $statuses
                ->pluck(
                    'teacher_subject_allocation_id'
                )
                ->filter()
                ->unique()
                ->values();

        $tsas =
            TeacherSubjectAllocation::query()
            ->with([
                'allocation.teacher',
                'allocation.academicYear',
                'allocation.section',
                'allocation.standard',
                'allocation.division',
            ])
            ->whereIn(
                'id',
                $tsaIds
            )
            ->get()
            ->keyBy('id');

        /*
        |--------------------------------------------------------------------------
        | BATCH LOAD EXAMS
        |--------------------------------------------------------------------------
        */

        $examIds =
            $statuses
                ->pluck(
                    'exam_master_id'
                )
                ->filter()
                ->unique()
                ->values();

        $examModels =
            ExamMaster::whereIn(
                'id',
                $examIds
            )
            ->get()
            ->keyBy('id');

        /*
        |--------------------------------------------------------------------------
        | BATCH LOAD STANDARD SUBJECT MAPPINGS
        |--------------------------------------------------------------------------
        */

        $standardIds =
            $statuses
                ->pluck(
                    'standard_id'
                )
                ->filter()
                ->unique()
                ->values();

        $subjectMappings =
            DB::table(
                'standard_wise_subjects as sws'
            )
            ->join(
                'subjects as s',
                's.id',
                '=',
                'sws.subject_id'
            )
            ->whereIn(
                'sws.standard_id',
                $standardIds
            )
            ->where(
                'sws.is_active',
                1
            )
            ->where(
                's.is_active',
                1
            )
            ->select([
                'sws.id as mapping_id',
                'sws.standard_id',
                'sws.subject_id',
                's.id as actual_subject_id',
                's.subject_name',
                's.subject_code',
                's.short_name',
            ])
            ->get();

        /*
        |--------------------------------------------------------------------------
        | INDEX SUBJECT MAPPINGS
        |--------------------------------------------------------------------------
        */

        $subjectByCurrentId =
            $subjectMappings
                ->groupBy(
                    function ($row) {

                        return
                            (int) $row->standard_id
                            . ':'
                            . (int) $row->subject_id;
                    }
                );

        $subjectByLegacyId =
            $subjectMappings
                ->groupBy(
                    function ($row) {

                        return
                            (int) $row->standard_id
                            . ':'
                            . (int) $row->mapping_id;
                    }
                );

        /*
        |--------------------------------------------------------------------------
        | BUILD ASSIGNMENTS
        |--------------------------------------------------------------------------
        */

        foreach (
            $statuses as $status
        ) {

            $tsa =
                $tsas->get(
                    $status->teacher_subject_allocation_id
                );

            if (
                !$tsa ||
                !$tsa->allocation
            ) {
                continue;
            }

            $exam =
                $examModels->get(
                    $status->exam_master_id
                );

            if (!$exam) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | ACADEMIC YEAR
            |--------------------------------------------------------------------------
            */

            if (
                $academicYearId &&
                $status->academic_year_id &&
                (int) $status->academic_year_id !==
                (int) $academicYearId
            ) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | EXAM ACADEMIC YEAR
            |--------------------------------------------------------------------------
            */

            $yearError =
                $this->validateExamAcademicYear(
                    $exam,
                    $academicYearId,
                    $tsa->allocation,
                    $status
                );

            if ($yearError) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | RESOLVE SUBJECT WITHOUT QUERY
            |--------------------------------------------------------------------------
            */

            $standardId =
                (int) $status->standard_id;

            $storedStatusSubjectId =
                (int) $status->subject_id;

            $storedTsaSubjectId =
                (int) $tsa->subject_id;

            $subject = null;

            /*
            |--------------------------------------------------------------------------
            | TMS SUBJECT AS CURRENT SUBJECT ID
            |--------------------------------------------------------------------------
            */

            $key =
                $standardId
                . ':'
                . $storedStatusSubjectId;

            $mappingRows =
                $subjectByCurrentId->get(
                    $key,
                    collect()
                );

            $mapping =
                $mappingRows->first();

            /*
            |--------------------------------------------------------------------------
            | TMS SUBJECT AS LEGACY SWS ID
            |--------------------------------------------------------------------------
            */

            if (!$mapping) {

                $key =
                    $standardId
                    . ':'
                    . $storedStatusSubjectId;

                $mappingRows =
                    $subjectByLegacyId->get(
                        $key,
                        collect()
                    );

                $mapping =
                    $mappingRows->first();
            }

            /*
            |--------------------------------------------------------------------------
            | TSA SUBJECT AS CURRENT SUBJECT ID
            |--------------------------------------------------------------------------
            */

            if (!$mapping) {

                $key =
                    $standardId
                    . ':'
                    . $storedTsaSubjectId;

                $mappingRows =
                    $subjectByCurrentId->get(
                        $key,
                        collect()
                    );

                $mapping =
                    $mappingRows->first();
            }

            /*
            |--------------------------------------------------------------------------
            | TSA SUBJECT AS LEGACY SWS ID
            |--------------------------------------------------------------------------
            */

            if (!$mapping) {

                $key =
                    $standardId
                    . ':'
                    . $storedTsaSubjectId;

                $mappingRows =
                    $subjectByLegacyId->get(
                        $key,
                        collect()
                    );

                $mapping =
                    $mappingRows->first();
            }

            if ($mapping) {

                $subject =
                    (object) [

                        'id' =>
                            (int) $mapping->actual_subject_id,

                        'subject_name' =>
                            $mapping->subject_name,

                        'subject_code' =>
                            $mapping->subject_code,

                        'short_name' =>
                            $mapping->short_name,
                    ];
            }

            if (!$subject) {
                continue;
            }

            $assignment =
                $this->buildAssignmentFromData(
                    $status,
                    $tsa,
                    $exam,
                    $subject
                );

            if (!$assignment) {
                continue;
            }

            $key =
                $assignment->resolved_selection_key;

            if (
                !$assignments->contains(
                    function ($item) use ($key) {

                        return
                            $item->resolved_selection_key ===
                            $key;
                    }
                )
            ) {

                $assignments->push(
                    $assignment
                );
            }
        }

        return $assignments
            ->sortBy([
                [
                    'resolved_standard_id',
                    'asc'
                ],
                [
                    'resolved_division_id',
                    'asc'
                ],
                [
                    'subject.subject_name',
                    'asc'
                ],
            ])
            ->values();
    }


    /*
    |--------------------------------------------------------------------------
    | GET SUBJECT CONFIGURATION
    |--------------------------------------------------------------------------
    */

    private function getSubjectConfig(
        $examId,
        $standardId,
        $subjectId
    ) {
        static $cache = [];

        $cacheKey =
            (int) $examId
            . '|'
            . (int) $standardId
            . '|'
            . (int) $subjectId;

        if (
            array_key_exists(
                $cacheKey,
                $cache
            )
        ) {
            return $cache[$cacheKey];
        }

        /*
        |--------------------------------------------------------------------------
        | CURRENT
        |--------------------------------------------------------------------------
        */

        $config =
            ExamMasterSubject::where(
                'exam_master_id',
                $examId
            )
            ->where(
                'standard_id',
                $standardId
            )
            ->where(
                'subject_id',
                $subjectId
            )
            ->first();

        if ($config) {

            $cache[$cacheKey] =
                $config;

            return $config;
        }

        /*
        |--------------------------------------------------------------------------
        | LEGACY
        |--------------------------------------------------------------------------
        */

        $mappingIds =
            DB::table(
                'standard_wise_subjects'
            )
            ->where(
                'standard_id',
                $standardId
            )
            ->where(
                'subject_id',
                $subjectId
            )
            ->where(
                'is_active',
                1
            )
            ->pluck(
                'id'
            );

        if (
            $mappingIds->isNotEmpty()
        ) {

            $config =
                ExamMasterSubject::where(
                    'exam_master_id',
                    $examId
                )
                ->where(
                    'standard_id',
                    $standardId
                )
                ->whereIn(
                    'subject_id',
                    $mappingIds
                )
                ->first();

            if ($config) {

                $cache[$cacheKey] =
                    $config;

                return $config;
            }
        }

        $cache[$cacheKey] =
            null;

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | LOAD STUDENTS
    |--------------------------------------------------------------------------
    */

    private function loadStudents(
        $academicYearId,
        $standardId,
        $divisionId
    ) {
        try {

            $students =
                StudentHelper::getStudentsDirectERP(
                    $academicYearId,
                    $standardId,
                    $divisionId
                );

            return collect(
                $students
            )
            ->sortBy(
                function ($student) {

                    $roll =
                        $student->roll_no
                        ??
                        $student->roll_number
                        ??
                        $student->roll
                        ??
                        $student->student_roll_no
                        ??
                        null;

                    if (
                        $roll === null ||
                        $roll === ''
                    ) {
                        return PHP_INT_MAX;
                    }

                    return (int) $roll;
                }
            )
            ->values();

        } catch (
            \Throwable $e
        ) {

            report($e);

            throw new \RuntimeException(
                'Old ERP Error: '
                . $e->getMessage()
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | LOAD EXISTING MARKS
    |--------------------------------------------------------------------------
    |
    | This happens ONLY after an assignment is selected.
    |
    | Supports current subjects.id and legacy SWS.id.
    |
    */

    private function loadExistingMarks(
        $examId,
        $subjectId,
        $standardId,
        $divisionId
    ) {
        $examId =
            (int) $examId;

        $subjectId =
            (int) $subjectId;

        $standardId =
            (int) $standardId;

        $divisionId =
            (int) $divisionId;

        if (
            $examId <= 0 ||
            $subjectId <= 0 ||
            $standardId <= 0 ||
            $divisionId <= 0
        ) {
            return collect();
        }

        /*
        |--------------------------------------------------------------------------
        | POSSIBLE SUBJECT IDS
        |--------------------------------------------------------------------------
        */

        $possibleSubjectIds =
            collect([
                $subjectId,
            ]);

        $legacyIds =
            DB::table(
                'standard_wise_subjects'
            )
            ->where(
                'standard_id',
                $standardId
            )
            ->where(
                'subject_id',
                $subjectId
            )
            ->where(
                'is_active',
                1
            )
            ->pluck(
                'id'
            );

        if (
            $legacyIds->isNotEmpty()
        ) {

            $possibleSubjectIds =
                $possibleSubjectIds
                    ->merge(
                        $legacyIds
                    );
        }

        $possibleSubjectIds =
            $possibleSubjectIds
                ->map(
                    fn ($id) =>
                        (int) $id
                )
                ->filter(
                    fn ($id) =>
                        $id > 0
                )
                ->unique()
                ->values();

        /*
        |--------------------------------------------------------------------------
        | LOAD MARKS
        |--------------------------------------------------------------------------
        */

        $marks =
            StudentMark::query()
            ->where(
                'exam_master_id',
                $examId
            )
            ->whereIn(
                'subject_id',
                $possibleSubjectIds
            )
            ->where(
                'standard_id',
                $standardId
            )
            ->where(
                'division_id',
                $divisionId
            )
            ->orderByDesc(
                'id'
            )
            ->get();

        return $marks
            ->unique(
                'student_id'
            )
            ->keyBy(
                'student_id'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | COMPONENT CONFIGURATION
    |--------------------------------------------------------------------------
    */

    private function getComponentConfig(
        $exam,
        $subjectConfig
    ) {
        $showTheory =
            true;

        $showOral =
            (bool) (
                $exam->has_oral ??
                false
            );

        $showPractical =
            (bool) (
                $exam->has_practical ??
                false
            );

        $examName =
            strtoupper(
                trim(
                    (string)
                    $exam->exam_name
                )
            );

        if (
            str_contains(
                $examName,
                'UNIT TEST 1'
            )
        ) {

            $showOral =
                false;

            $showPractical =
                false;
        }

        return [

            'showTheory' =>
                $showTheory,

            'showOral' =>
                $showOral,

            'showPractical' =>
                $showPractical,

            'theoryMaxMarks' =>
                (float) (
                    $subjectConfig->max_marks
                    ??
                    0
                ),

            'theoryPassingMarks' =>
                (float) (
                    $subjectConfig->passing_marks
                    ??
                    0
                ),

            'oralMaxMarks' =>
                $showOral
                    ? (float) (
                        $exam->oral_max_marks
                        ??
                        0
                    )
                    : 0,

            'oralPassingMarks' =>
                $showOral
                    ? (float) (
                        $exam->oral_passing_marks
                        ??
                        0
                    )
                    : 0,

            'practicalMaxMarks' =>
                $showPractical
                    ? (float) (
                        $exam->practical_max_marks
                        ??
                        0
                    )
                    : 0,

            'practicalPassingMarks' =>
                $showPractical
                    ? (float) (
                        $exam->practical_passing_marks
                        ??
                        0
                    )
                    : 0,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | PARSE SELECTION
    |--------------------------------------------------------------------------
    */

    private function parseSelection(
        Request $request
    ) {
        $value =
            $request->input(
                'teacher_subject_allocation_id'
            );

        $tsaId =
            null;

        $subjectId =
            null;

        if (
            $value &&
            str_contains(
                (string) $value,
                '|'
            )
        ) {

            $parts =
                array_pad(
                    explode(
                        '|',
                        $value
                    ),
                    2,
                    null
                );

            $tsaId =
                (int) (
                    $parts[0] ?? 0
                );

            $subjectId =
                !empty(
                    $parts[1]
                )
                    ? (int) $parts[1]
                    : null;

        } else {

            $tsaId =
                $value
                    ? (int) $value
                    : null;

            $subjectId =
                $request->filled(
                    'subject_id'
                )
                    ? (int) $request->input(
                        'subject_id'
                    )
                    : null;
        }

        return [
            'tsa_id' =>
                $tsaId,

            'subject_id' =>
                $subjectId,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | LOAD SELECTED DATA
    |--------------------------------------------------------------------------
    |
    | This is where historical marks are recovered.
    |
    */

    private function loadSelectedData(
        Request $request,
        $exams
    ) {
        $data = [

            'students' =>
                collect(),

            'existingMarks' =>
                collect(),

            'exam' =>
                null,

            'teacherSubjectAllocation' =>
                null,

            'selectedClassAllocation' =>
                null,

            'subjectConfig' =>
                null,

            'showTheory' =>
                false,

            'showOral' =>
                false,

            'showPractical' =>
                false,

            'theoryMaxMarks' =>
                0,

            'theoryPassingMarks' =>
                0,

            'oralMaxMarks' =>
                0,

            'oralPassingMarks' =>
                0,

            'practicalMaxMarks' =>
                0,

            'practicalPassingMarks' =>
                0,

            'marksLocked' =>
                false,

            'message' =>
                '',

            'error' =>
                '',
        ];

        $academicYearId =
            $request->input(
                'academic_year_id'
            );

        $examId =
            $request->input(
                'exam_master_id'
            );

        $selection =
            $this->parseSelection(
                $request
            );

        $tsaId =
            $selection['tsa_id'];

        $selectedSubjectId =
            $selection['subject_id'];

        /*
        |--------------------------------------------------------------------------
        | EXAM
        |--------------------------------------------------------------------------
        */

        if ($examId) {

            $data['exam'] =
                $exams->firstWhere(
                    'id',
                    (int) $examId
                );

            if (
                !$data['exam']
            ) {

                $data['exam'] =
                    ExamMaster::find(
                        (int) $examId
                    );
            }

            if (
                !$data['exam']
            ) {

                $data['error'] =
                    'Selected exam was not found.';

                return $data;
            }

            $examYearError =
                $this->validateExamAcademicYear(
                    $data['exam'],
                    $academicYearId
                );

            if (
                $examYearError
            ) {

                $data['error'] =
                    $examYearError;

                return $data;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | NO TSA = ONLY FILTER SCREEN
        |--------------------------------------------------------------------------
        */

        if (!$tsaId) {
            return $data;
        }

        /*
        |--------------------------------------------------------------------------
        | TMS STATUS
        |--------------------------------------------------------------------------
        */

        $status =
            TeacherMarksStatus::where(
                'teacher_subject_allocation_id',
                $tsaId
            )
            ->when(
                $examId,
                function ($query) use (
                    $examId
                ) {

                    $query->where(
                        'exam_master_id',
                        $examId
                    );
                }
            )
            ->orderByDesc(
                'id'
            )
            ->first();

        /*
        |--------------------------------------------------------------------------
        | REAL TSA
        |--------------------------------------------------------------------------
        */

        $realTsa =
            TeacherSubjectAllocation::find(
                $tsaId
            );

        $classAllocation =
            null;

        if (
            $realTsa
        ) {

            $classAllocation =
                TeacherClassAllocation::with([
                    'teacher',
                    'academicYear',
                    'section',
                    'standard',
                    'division',
                ])
                ->find(
                    $realTsa
                        ->teacher_class_allocation_id
                );
        }

        /*
        |--------------------------------------------------------------------------
        | HISTORICAL MARK
        |--------------------------------------------------------------------------
        |
        | Search current ID and legacy SWS IDs.
        |
        */

        $historicalMark =
            null;

        if (
            $selectedSubjectId
            &&
            $examId
        ) {

            $possibleIds =
                collect([
                    (int) $selectedSubjectId,
                ]);

            $legacyIds =
                DB::table(
                    'standard_wise_subjects'
                )
                ->where(
                    'subject_id',
                    (int) $selectedSubjectId
                )
                ->where(
                    'is_active',
                    1
                )
                ->pluck(
                    'id'
                );

            if (
                $legacyIds->isNotEmpty()
            ) {

                $possibleIds =
                    $possibleIds
                        ->merge(
                            $legacyIds
                        );
            }

            $historicalMark =
                StudentMark::where(
                    'teacher_subject_allocation_id',
                    $tsaId
                )
                ->where(
                    'exam_master_id',
                    $examId
                )
                ->whereIn(
                    'subject_id',
                    $possibleIds
                        ->unique()
                        ->values()
                        ->all()
                )
                ->orderByDesc(
                    'id'
                )
                ->first();
        }

        /*
        |--------------------------------------------------------------------------
        | HISTORICAL STATUS FALLBACK
        |--------------------------------------------------------------------------
        */

        if (
            !$status
            &&
            $historicalMark
        ) {

            $status =
                new TeacherMarksStatus();

            $status->teacher_subject_allocation_id =
                $tsaId;

            $status->exam_master_id =
                $examId;

            $status->teacher_id =
                null;

            $status->standard_id =
                $historicalMark->standard_id;

            $status->division_id =
                $historicalMark->division_id;

            $status->academic_year_id =
                $academicYearId;

            $status->subject_id =
                $historicalMark->subject_id;

            $status->status =
                'COMPLETED';
        }

        if (
            !$status
        ) {

            $data['error'] =
                'Selected teaching assignment status was not found.';

            return $data;
        }

        /*
        |--------------------------------------------------------------------------
        | EXAM FALLBACK
        |--------------------------------------------------------------------------
        */

        if (
            !$data['exam']
        ) {

            $data['exam'] =
                ExamMaster::find(
                    (int) $status->exam_master_id
                );
        }

        if (
            !$data['exam']
        ) {

            $data['error'] =
                'Exam linked to the selected teaching assignment was not found.';

            return $data;
        }

        /*
        |--------------------------------------------------------------------------
        | YEAR
        |--------------------------------------------------------------------------
        */

        $resolvedAcademicYearId =
            $status->academic_year_id
            ??
            $academicYearId
            ??
            $classAllocation?->academic_year_id
            ??
            $data['exam']->academic_year_id;

        if (
            !$resolvedAcademicYearId
        ) {

            $data['error'] =
                'Unable to determine Academic Year for the selected teaching assignment.';

            return $data;
        }

        $yearError =
            $this->validateExamAcademicYear(
                $data['exam'],
                $academicYearId,
                $classAllocation,
                $status
            );

        if (
            $yearError
        ) {

            $data['error'] =
                $yearError;

            return $data;
        }

        /*
        |--------------------------------------------------------------------------
        | SUBJECT ID
        |--------------------------------------------------------------------------
        */

        $subjectId =
            $selectedSubjectId;

        if (
            $historicalMark
        ) {

            $subjectId =
                $this->resolveActualSubjectId(
                    $historicalMark->subject_id,
                    $status->standard_id
                )
                ??
                $subjectId;
        }

        if (
            !$subjectId
            &&
            $status
        ) {

            $subjectId =
                $this->resolveActualSubjectId(
                    $status->subject_id,
                    $status->standard_id
                );
        }

        if (
            !$subjectId
            &&
            $realTsa
        ) {

            $subjectId =
                $this->resolveActualSubjectId(
                    $realTsa->subject_id,
                    $status->standard_id
                );
        }

        if (
            !$subjectId
        ) {

            $data['error'] =
                'Unable to resolve the actual Subject Master ID.';

            return $data;
        }

        /*
        |--------------------------------------------------------------------------
        | SUBJECT
        |--------------------------------------------------------------------------
        */

        $subject =
            Subject::where(
                'id',
                $subjectId
            )
            ->where(
                'is_active',
                1
            )
            ->first();

        if (
            !$subject
        ) {

            $data['error'] =
                'The selected subject was not found.';

            return $data;
        }

        /*
        |--------------------------------------------------------------------------
        | VALID STANDARD SUBJECT
        |--------------------------------------------------------------------------
        */

        $validMapping =
            DB::table(
                'standard_wise_subjects'
            )
            ->where(
                'standard_id',
                $status->standard_id
            )
            ->where(
                'subject_id',
                $subjectId
            )
            ->where(
                'is_active',
                1
            )
            ->exists();

        if (
            !$validMapping
        ) {

            $data['error'] =
                'The selected subject is not mapped to the selected Standard.';

            return $data;
        }

        /*
        |--------------------------------------------------------------------------
        | BUILD SELECTED ASSIGNMENT
        |--------------------------------------------------------------------------
        */

        $assignment =
            $this->buildSelectedAssignment(
                $status,
                $realTsa,
                $data['exam'],
                $subject,
                $resolvedAcademicYearId,
                $classAllocation
            );

        if (!$assignment) {

            $data['error'] =
                'The selected subject could not be resolved.';

            return $data;
        }

        $data['teacherSubjectAllocation'] =
            $assignment;

        $data['selectedClassAllocation'] =
            $assignment->allocation;

        /*
        |--------------------------------------------------------------------------
        | SUBJECT CONFIG
        |--------------------------------------------------------------------------
        */

        $data['subjectConfig'] =
            $this->getSubjectConfig(
                $data['exam']->id,
                $status->standard_id,
                $subjectId
            );

        if (
            !$data['subjectConfig']
        ) {

            $data['error'] =
                'Marks configuration was not found for '
                . $subject->subject_name
                . ' in '
                . $data['exam']->exam_name
                . '.';

            return $data;
        }

        /*
        |--------------------------------------------------------------------------
        | COMPONENTS
        |--------------------------------------------------------------------------
        */

        $data =
            array_merge(
                $data,
                $this->getComponentConfig(
                    $data['exam'],
                    $data['subjectConfig']
                )
            );

        /*
        |--------------------------------------------------------------------------
        | STUDENTS
        |--------------------------------------------------------------------------
        */

        try {

            $data['students'] =
                $this->loadStudents(
                    $resolvedAcademicYearId,
                    $status->standard_id,
                    $status->division_id
                );

        } catch (
            \Throwable $e
        ) {

            $data['error'] =
                $e->getMessage();
        }

        /*
        |--------------------------------------------------------------------------
        | EXISTING MARKS
        |--------------------------------------------------------------------------
        |
        | This query happens ONLY after selection.
        |
        */

        $data['existingMarks'] =
            $this->loadExistingMarks(
                $data['exam']->id,
                $subjectId,
                $status->standard_id,
                $status->division_id
            );

        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        $statusName =
            strtoupper(
                trim(
                    (string) (
                        $status->status
                        ??
                        'PENDING'
                    )
                )
            );

        if (
            $assignment->is_historical
        ) {

            $data['message'] =
                'Historical marks recovered from Student Marks. Administrator can modify these marks.';

        } else {

            $data['message'] =
                'Status: '
                . $statusName
                . '. Administrator can modify these marks.';
        }

        return $data;
    }


    /*
    |--------------------------------------------------------------------------
    | BUILD SELECTED ASSIGNMENT
    |--------------------------------------------------------------------------
    */

    private function buildSelectedAssignment(
        $status,
        $realTsa,
        $exam,
        $subject,
        $academicYearId,
        $allocation
    ) {
        if (
            !$status ||
            !$realTsa ||
            !$exam ||
            !$subject ||
            !$allocation
        ) {
            return null;
        }

        $assignment =
            new TeacherSubjectAllocation();

        $assignment->id =
            (int) $realTsa->id;

        $assignment->teacher_class_allocation_id =
            (int) $realTsa->teacher_class_allocation_id;

        $assignment->exam_master_id =
            (int) $exam->id;

        $assignment->subject_id =
            (int) $subject->id;

        $assignment->setRelation(
            'allocation',
            $allocation
        );

        $assignment->setRelation(
            'subject',
            $subject
        );

        $assignment->setRelation(
            'exam',
            $exam
        );

        $assignment->resolved_subject_id =
            (int) $subject->id;

        $assignment->resolved_academic_year_id =
            (int) $academicYearId;

        $assignment->resolved_class_allocation_id =
            (int) $allocation->id;

        $assignment->resolved_exam_master_id =
            (int) $exam->id;

        $assignment->resolved_standard_id =
            (int) $allocation->standard_id;

        $assignment->resolved_division_id =
            (int) $allocation->division_id;

        $assignment->resolved_teacher_id =
            (int) $allocation->user_id;

        $assignment->resolved_tms_subject_id =
            (int) $status->subject_id;

        $assignment->resolved_status =
            strtoupper(
                trim(
                    (string) (
                        $status->status
                        ??
                        'PENDING'
                    )
                )
            );

        $assignment->resolved_status_id =
            $status->id;

        $assignment->is_historical =
            false;

        /*
        |--------------------------------------------------------------------------
        | HISTORICAL SUBJECT DETECTION
        |--------------------------------------------------------------------------
        */

        if (
            (int) $status->subject_id !==
            (int) $subject->id
        ) {

            $assignment->is_historical =
                true;
        }

        $assignment->resolved_selection_key =
            $realTsa->id
            . '|'
            . $subject->id;

        return $assignment;
    }


    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ) {
        $academicYearId =
            $request->input(
                'academic_year_id'
            );

        /*
        |--------------------------------------------------------------------------
        | ACADEMIC YEARS
        |--------------------------------------------------------------------------
        */

        $academicYears =
            AcademicYear::where(
                'is_active',
                1
            )
            ->orderByDesc(
                'id'
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | STANDARDS
        |--------------------------------------------------------------------------
        */

        $standards =
            Standard::where(
                'is_active',
                1
            )
            ->orderBy(
                'display_order'
            )
            ->get([
                'id',
                'standard_name',
            ]);

        /*
        |--------------------------------------------------------------------------
        | EXAMS
        |--------------------------------------------------------------------------
        |
        | When Academic Year is selected:
        |
        | show matching exams + legacy exams with NULL year.
        |
        */

        $examQuery =
            ExamMaster::where(
                'is_active',
                1
            );

        if (
            $academicYearId !== null
            &&
            $academicYearId !== ''
        ) {

            $examQuery->where(
                function ($query) use (
                    $academicYearId
                ) {

                    $query->where(
                        'academic_year_id',
                        $academicYearId
                    )
                    ->orWhereNull(
                        'academic_year_id'
                    );
                }
            );
        }

        $exams =
            $examQuery
            ->orderBy(
                'display_order'
            )
            ->orderBy(
                'exam_name'
            )
            ->get();

        $exams =
            $this->prepareExams(
                $exams,
                $standards
            );

        /*
        |--------------------------------------------------------------------------
        | ASSIGNMENTS
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | If no Exam is selected, this returns immediately.
        |
        */

        $assignments =
            $this->getAssignments(
                $academicYearId,
                $request->input(
                    'exam_master_id'
                )
            );

        /*
        |--------------------------------------------------------------------------
        | SELECTED DATA
        |--------------------------------------------------------------------------
        */

        $selected =
            $this->loadSelectedData(
                $request,
                $exams
            );

        return view(
            'administrator.marks.edit',
            array_merge(
                [
                    'academicYears' =>
                        $academicYears,

                    'standards' =>
                        $standards,

                    'exams' =>
                        $exams,

                    'assignments' =>
                        $assignments,

                    'success' =>
                        $request->boolean(
                            'marks_updated'
                        ),

                    'marksUpdated' =>
                        $request->boolean(
                            'marks_updated'
                        ),

                    'marksReopened' =>
                        $request->boolean(
                            'marks_reopened'
                        ),
                ],
                $selected
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(
        Request $request
    ) {
        return $this->index(
            $request
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GET SUBJECTS
    |--------------------------------------------------------------------------
    */

    public function getSubjects(
        Request $request
    ) {
        $request->validate([
            'allocation_id' =>
                'required|exists:teacher_class_allocations,id',

            'exam_master_id' =>
                'required|exists:exam_masters,id',
        ]);

        $allocation =
            TeacherClassAllocation::findOrFail(
                $request->allocation_id
            );

        $examId =
            (int) $request->exam_master_id;

        $standardId =
            (int) $allocation->standard_id;

        $exam =
            ExamMaster::find(
                $examId
            );

        if (!$exam) {

            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Selected Exam was not found.',

                'subjects' =>
                    [],
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | YEAR CHECK
        |--------------------------------------------------------------------------
        */

        $yearError =
            $this->validateExamAcademicYear(
                $exam,
                $allocation->academic_year_id,
                $allocation
            );

        if ($yearError) {

            return response()->json([
                'success' =>
                    false,

                'message' =>
                    $yearError,

                'subjects' =>
                    [],
            ], 422);
        }

        $subjects =
            collect();

        /*
        |--------------------------------------------------------------------------
        | CURRENT TMS SUBJECTS
        |--------------------------------------------------------------------------
        */

        $statuses =
            TeacherMarksStatus::where(
                'exam_master_id',
                $examId
            )
            ->where(
                'standard_id',
                $standardId
            )
            ->where(
                'division_id',
                $allocation->division_id
            )
            ->where(
                'academic_year_id',
                $allocation->academic_year_id
            )
            ->where(
                'teacher_id',
                $allocation->user_id
            )
            ->orderByDesc(
                'id'
            )
            ->get();

        foreach (
            $statuses as $status
        ) {

            $actualSubject =
                $this->getActualSubject(
                    $status->subject_id,
                    $standardId
                );

            if (!$actualSubject) {

                /*
                |--------------------------------------------------------------------------
                | LEGACY FALLBACK THROUGH TSA
                |--------------------------------------------------------------------------
                */

                $tsa =
                    TeacherSubjectAllocation::find(
                        $status->teacher_subject_allocation_id
                    );

                if ($tsa) {

                    $actualSubject =
                        $this->getActualSubject(
                            $tsa->subject_id,
                            $standardId
                        );
                }
            }

            if (!$actualSubject) {
                continue;
            }

            $this->pushSubjectOption(
                $subjects,
                $status->teacher_subject_allocation_id,
                $actualSubject,
                false
            );
        }

        /*
        |--------------------------------------------------------------------------
        | HISTORICAL SUBJECTS
        |--------------------------------------------------------------------------
        |
        | Only this AJAX request scans StudentMark,
        | not the initial page.
        |
        */

        $historicalMarks =
            StudentMark::query()
            ->where(
                'exam_master_id',
                $examId
            )
            ->where(
                'standard_id',
                $standardId
            )
            ->where(
                'division_id',
                $allocation->division_id
            )
            ->whereNotNull(
                'teacher_subject_allocation_id'
            )
            ->whereNotNull(
                'subject_id'
            )
            ->select([
                'teacher_subject_allocation_id',
                'subject_id',
            ])
            ->distinct()
            ->get();

        foreach (
            $historicalMarks as $markInfo
        ) {

            $actualSubject =
                $this->getActualSubject(
                    $markInfo->subject_id,
                    $standardId
                );

            if (!$actualSubject) {
                continue;
            }

            $this->pushSubjectOption(
                $subjects,
                $markInfo->teacher_subject_allocation_id,
                $actualSubject,
                true
            );
        }

        return response()->json(
            $subjects->values()
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ADD SUBJECT OPTION
    |--------------------------------------------------------------------------
    */

    private function pushSubjectOption(
        &$subjects,
        $tsaId,
        $subject,
        $historical
    ) {
        $selectionKey =
            $tsaId
            . '|'
            . $subject->id;

        if (
            $subjects->contains(
                function ($item) use (
                    $selectionKey
                ) {

                    return
                        $item->selection_key ===
                        $selectionKey;
                }
            )
        ) {
            return;
        }

        $subjects->push(
            (object) [

                'teacher_subject_allocation_id' =>
                    (int) $tsaId,

                'subject_id' =>
                    (int) $subject->id,

                'subject_name' =>
                    $subject->subject_name,

                'subject_code' =>
                    $subject->subject_code
                    ??
                    '',

                'short_name' =>
                    $subject->short_name
                    ??
                    '',

                'selection_key' =>
                    $selectionKey,

                'is_historical' =>
                    $historical,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE MARK
    |--------------------------------------------------------------------------
    */

    private function validateMark(
        $value,
        $max,
        $required,
        $label,
        $studentId
    ) {
        if (
            $value !== null
            &&
            $value !== ''
        ) {

            $value =
                (float) $value;

            if (
                $value < 0
                ||
                $value > $max
            ) {

                throw new \RuntimeException(
                    'Invalid '
                    . $label
                    . ' marks for student ID '
                    . $studentId
                    . '. Maximum allowed marks: '
                    . $max
                );
            }

            return $value;
        }

        if (
            $required
            &&
            $max > 0
        ) {

            throw new \RuntimeException(
                $label
                . ' marks are required for student ID '
                . $studentId
            );
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE MARKS
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request
    ) {
        $request->validate([
            'teacher_subject_allocation_id' =>
                'required',

            'exam_master_id' =>
                'required|integer|exists:exam_masters,id',

            'student_ids' =>
                'required|array|min:1',
        ]);

        $selection =
            $this->parseSelection(
                $request
            );

        $tsaId =
            $selection['tsa_id'];

        $selectedSubjectId =
            $selection['subject_id'];

        $examId =
            (int) $request->exam_master_id;

        if (!$tsaId) {

            return back()
                ->withInput()
                ->withErrors([
                    'teacher_subject_allocation_id' =>
                        'Invalid teaching assignment.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        $status =
            TeacherMarksStatus::where(
                'teacher_subject_allocation_id',
                $tsaId
            )
            ->where(
                'exam_master_id',
                $examId
            )
            ->orderByDesc(
                'id'
            )
            ->first();

        /*
        |--------------------------------------------------------------------------
        | REAL TSA
        |--------------------------------------------------------------------------
        */

        $realTsa =
            TeacherSubjectAllocation::find(
                $tsaId
            );

        $classAllocation =
            null;

        if ($realTsa) {

            $classAllocation =
                TeacherClassAllocation::find(
                    $realTsa
                        ->teacher_class_allocation_id
                );
        }

        /*
        |--------------------------------------------------------------------------
        | HISTORICAL MARK
        |--------------------------------------------------------------------------
        */

        $historicalMark =
            null;

        if (
            $selectedSubjectId
        ) {

            $possibleIds =
                collect([
                    (int) $selectedSubjectId,
                ]);

            $legacyIds =
                DB::table(
                    'standard_wise_subjects'
                )
                ->where(
                    'subject_id',
                    (int) $selectedSubjectId
                )
                ->where(
                    'is_active',
                    1
                )
                ->pluck(
                    'id'
                );

            if (
                $legacyIds->isNotEmpty()
            ) {

                $possibleIds =
                    $possibleIds
                        ->merge(
                            $legacyIds
                        );
            }

            $historicalMark =
                StudentMark::where(
                    'exam_master_id',
                    $examId
                )
                ->where(
                    'teacher_subject_allocation_id',
                    $tsaId
                )
                ->whereIn(
                    'subject_id',
                    $possibleIds
                        ->unique()
                        ->values()
                        ->all()
                )
                ->orderByDesc(
                    'id'
                )
                ->first();
        }

        /*
        |--------------------------------------------------------------------------
        | STANDARD / DIVISION / YEAR
        |--------------------------------------------------------------------------
        */

        $standardId =
            optional($status)->standard_id
            ??
            optional($historicalMark)->standard_id
            ??
            optional($classAllocation)->standard_id;

        $divisionId =
            optional($status)->division_id
            ??
            optional($historicalMark)->division_id
            ??
            optional($classAllocation)->division_id;

        $academicYearId =
            optional($status)->academic_year_id
            ??
            optional($classAllocation)->academic_year_id;

        if (
            !$standardId ||
            !$divisionId ||
            !$academicYearId
        ) {

            return back()
                ->withInput()
                ->withErrors([
                    'teacher_subject_allocation_id' =>
                        'Unable to determine Standard, Division or Academic Year for this teaching assignment.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | EXAM
        |--------------------------------------------------------------------------
        */

        $exam =
            ExamMaster::where(
                'id',
                $examId
            )
            ->where(
                'is_active',
                1
            )
            ->first();

        if (!$exam) {

            return back()
                ->withInput()
                ->withErrors([
                    'exam_master_id' =>
                        'The selected exam was not found.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | ACADEMIC YEAR VALIDATION
        |--------------------------------------------------------------------------
        */

        $yearError =
            $this->validateExamAcademicYear(
                $exam,
                $academicYearId,
                $classAllocation,
                $status
            );

        if ($yearError) {

            return back()
                ->withInput()
                ->withErrors([
                    'exam_master_id' =>
                        $yearError,
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | ACTUAL SUBJECT
        |--------------------------------------------------------------------------
        */

        $actualSubjectId =
            $selectedSubjectId;

        if (
            !$actualSubjectId &&
            $historicalMark
        ) {

            $actualSubjectId =
                $this->resolveActualSubjectId(
                    $historicalMark->subject_id,
                    $standardId
                );
        }

        if (
            !$actualSubjectId &&
            $status
        ) {

            $actualSubjectId =
                $this->resolveActualSubjectId(
                    $status->subject_id,
                    $standardId
                );
        }

        if (
            !$actualSubjectId &&
            $realTsa
        ) {

            $actualSubjectId =
                $this->resolveActualSubjectId(
                    $realTsa->subject_id,
                    $standardId
                );
        }

        if (!$actualSubjectId) {

            return back()
                ->withInput()
                ->withErrors([
                    'subject_id' =>
                        'Unable to resolve the actual Subject Master ID.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | SUBJECT
        |--------------------------------------------------------------------------
        */

        $subject =
            Subject::where(
                'id',
                $actualSubjectId
            )
            ->where(
                'is_active',
                1
            )
            ->first();

        if (!$subject) {

            return back()
                ->withInput()
                ->withErrors([
                    'subject_id' =>
                        'The selected subject was not found.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | STANDARD MAPPING
        |--------------------------------------------------------------------------
        */

        $validMapping =
            DB::table(
                'standard_wise_subjects'
            )
            ->where(
                'standard_id',
                $standardId
            )
            ->where(
                'subject_id',
                $actualSubjectId
            )
            ->where(
                'is_active',
                1
            )
            ->exists();

        if (!$validMapping) {

            return back()
                ->withInput()
                ->withErrors([
                    'subject_id' =>
                        'The selected subject is not mapped to the selected Standard.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | SUBJECT CONFIG
        |--------------------------------------------------------------------------
        */

        $subjectConfig =
            $this->getSubjectConfig(
                $examId,
                $standardId,
                $actualSubjectId
            );

        if (!$subjectConfig) {

            return back()
                ->withInput()
                ->withErrors([
                    'subject_id' =>
                        'Marks configuration was not found for '
                        . $subject->subject_name
                        . ' in '
                        . $exam->exam_name
                        . '.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | COMPONENTS
        |--------------------------------------------------------------------------
        */

        $component =
            $this->getComponentConfig(
                $exam,
                $subjectConfig
            );

        $theoryMax =
            $component['theoryMaxMarks'];

        $showOral =
            $component['showOral'];

        $showPractical =
            $component['showPractical'];

        $oralMax =
            $component['oralMaxMarks'];

        $practicalMax =
            $component['practicalMaxMarks'];

        /*
        |--------------------------------------------------------------------------
        | SAVE
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $request,
                $tsaId,
                $examId,
                $standardId,
                $divisionId,
                $actualSubjectId,
                $theoryMax,
                $oralMax,
                $practicalMax,
                $showOral,
                $showPractical
            ) {

                /*
                |--------------------------------------------------------------------------
                | LEGACY SWS IDs FOR THIS SUBJECT
                |--------------------------------------------------------------------------
                */

                $legacySubjectIds =
                    DB::table(
                        'standard_wise_subjects'
                    )
                    ->where(
                        'standard_id',
                        $standardId
                    )
                    ->where(
                        'subject_id',
                        $actualSubjectId
                    )
                    ->where(
                        'is_active',
                        1
                    )
                    ->pluck(
                        'id'
                    );

                foreach (
                    $request->student_ids
                    as $studentId
                ) {

                    $studentId =
                        (string)
                        $studentId;

                    /*
                    |--------------------------------------------------------------------------
                    | CURRENT MARK
                    |--------------------------------------------------------------------------
                    */

                    $mark =
                        StudentMark::where(
                            'exam_master_id',
                            $examId
                        )
                        ->where(
                            'student_id',
                            $studentId
                        )
                        ->where(
                            'subject_id',
                            $actualSubjectId
                        )
                        ->where(
                            'standard_id',
                            $standardId
                        )
                        ->where(
                            'division_id',
                            $divisionId
                        )
                        ->orderByDesc(
                            'id'
                        )
                        ->first();

                    /*
                    |--------------------------------------------------------------------------
                    | LEGACY MARK
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !$mark
                        &&
                        $legacySubjectIds->isNotEmpty()
                    ) {

                        $mark =
                            StudentMark::where(
                                'exam_master_id',
                                $examId
                            )
                            ->where(
                                'student_id',
                                $studentId
                            )
                            ->whereIn(
                                'subject_id',
                                $legacySubjectIds
                            )
                            ->where(
                                'standard_id',
                                $standardId
                            )
                            ->where(
                                'division_id',
                                $divisionId
                            )
                            ->orderByDesc(
                                'id'
                            )
                            ->first();
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | OLD VALUES
                    |--------------------------------------------------------------------------
                    */

                    $oldTheory =
                        $mark?->theory_obtained_marks;

                    $oldOral =
                        $mark?->oral_obtained_marks;

                    $oldPractical =
                        $mark?->practical_obtained_marks;

                    /*
                    |--------------------------------------------------------------------------
                    | ABSENT
                    |--------------------------------------------------------------------------
                    */

                    $isAbsent =
                        (
                            (int) (
                                $request
                                    ->is_absent[$studentId]
                                    ??
                                    0
                            )
                        ) === 1
                            ? 1
                            : 0;

                    /*
                    |--------------------------------------------------------------------------
                    | INPUT
                    |--------------------------------------------------------------------------
                    */

                    $theory =
                        $request
                            ->theory_marks[$studentId]
                            ??
                            null;

                    $oral =
                        $request
                            ->oral_marks[$studentId]
                            ??
                            null;

                    $practical =
                        $request
                            ->practical_marks[$studentId]
                            ??
                            null;

                    /*
                    |--------------------------------------------------------------------------
                    | ABSENT = ZERO
                    |--------------------------------------------------------------------------
                    */

                    if ($isAbsent) {

                        $theory = 0;
                        $oral = 0;
                        $practical = 0;

                    } else {

                        if (!$showOral) {
                            $oral = null;
                        }

                        if (!$showPractical) {
                            $practical = null;
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | VALIDATE THEORY
                    |--------------------------------------------------------------------------
                    */

                    $theory =
                        $this->validateMark(
                            $theory,
                            $theoryMax,
                            !$isAbsent,
                            'Theory',
                            $studentId
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | VALIDATE ORAL
                    |--------------------------------------------------------------------------
                    */

                    if ($showOral) {

                        $oral =
                            $this->validateMark(
                                $oral,
                                $oralMax,
                                !$isAbsent,
                                'Oral',
                                $studentId
                            );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | VALIDATE PRACTICAL
                    |--------------------------------------------------------------------------
                    */

                    if ($showPractical) {

                        $practical =
                            $this->validateMark(
                                $practical,
                                $practicalMax,
                                !$isAbsent,
                                'Practical',
                                $studentId
                            );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | UPDATE EXISTING MARK
                    |--------------------------------------------------------------------------
                    */

                    if ($mark) {

                        $mark->update([

                            'teacher_subject_allocation_id' =>
                                $tsaId,

                            /*
                            | Normalize old legacy subject ID to actual
                            | Subject Master ID when admin saves it.
                            */

                            'subject_id' =>
                                $actualSubjectId,

                            'standard_id' =>
                                $standardId,

                            'division_id' =>
                                $divisionId,

                            'theory_obtained_marks' =>
                                $theory,

                            'oral_obtained_marks' =>
                                $oral,

                            'practical_obtained_marks' =>
                                $practical,

                            'is_absent' =>
                                $isAbsent,

                            'updated_by' =>
                                Auth::id(),
                        ]);

                        $wasCreated = false;

                    } else {

                        /*
                        |--------------------------------------------------------------------------
                        | CREATE
                        |--------------------------------------------------------------------------
                        */

                        $mark =
                            StudentMark::create([

                                'student_id' =>
                                    $studentId,

                                'exam_master_id' =>
                                    $examId,

                                'teacher_subject_allocation_id' =>
                                    $tsaId,

                                'subject_id' =>
                                    $actualSubjectId,

                                'standard_id' =>
                                    $standardId,

                                'division_id' =>
                                    $divisionId,

                                'theory_obtained_marks' =>
                                    $theory,

                                'oral_obtained_marks' =>
                                    $oral,

                                'practical_obtained_marks' =>
                                    $practical,

                                'is_absent' =>
                                    $isAbsent,

                                'is_locked' =>
                                    0,

                                'updated_by' =>
                                    Auth::id(),
                            ]);

                        $wasCreated = true;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | AUDIT
                    |--------------------------------------------------------------------------
                    */

                    MarkAuditLog::create([

                        'student_mark_id' =>
                            $mark->id,

                        'student_id' =>
                            $mark->student_id,

                        'exam_master_id' =>
                            $mark->exam_master_id,

                        'subject_id' =>
                            $actualSubjectId,

                        'teacher_id' =>
                            Auth::id(),

                        'action' =>
                            'ADMIN_UPDATE',

                        'old_theory_marks' =>
                            $oldTheory,

                        'new_theory_marks' =>
                            $mark->theory_obtained_marks,

                        'old_oral_marks' =>
                            $oldOral,

                        'new_oral_marks' =>
                            $mark->oral_obtained_marks,

                        'old_practical_marks' =>
                            $oldPractical,

                        'new_practical_marks' =>
                            $mark->practical_obtained_marks,

                        'remarks' =>
                            $wasCreated
                                ? 'Admin Marks Entry'
                                : (
                                    $isAbsent
                                        ? 'Admin Marks Correction - ABSENT'
                                        : 'Admin Marks Correction - PRESENT'
                                ),

                        'ip_address' =>
                            $request->ip(),

                        'user_agent' =>
                            $request->userAgent(),
                    ]);
                }
            }
        );

        return redirect()->route(
            'result-generation.admin-marks.edit',
            [

                'academic_year_id' =>
                    $academicYearId,

                'exam_master_id' =>
                    $examId,

                'teacher_subject_allocation_id' =>
                    $tsaId
                    . '|'
                    . $actualSubjectId,

                'subject_id' =>
                    $actualSubjectId,

                'marks_updated' =>
                    1,
            ]
        )
        ->with(
            'success',
            'Marks Updated Successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | REOPEN
    |--------------------------------------------------------------------------
    */

    public function reopen(
        Request $request
    ) {
        $request->validate([
            'exam_master_id' =>
                'required',

            'subject_id' =>
                'required',

            'standard_id' =>
                'required',

            'division_id' =>
                'required',

            'academic_year_id' =>
                'nullable|exists:academic_years,id',
        ]);

        /*
        |--------------------------------------------------------------------------
        | EXAM
        |--------------------------------------------------------------------------
        */

        $exam =
            ExamMaster::where(
                'id',
                $request->exam_master_id
            )
            ->where(
                'is_active',
                1
            )
            ->first();

        if (!$exam) {

            return back()->with(
                'error',
                'Selected Exam was not found.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SUBJECT
        |--------------------------------------------------------------------------
        */

        $actualSubjectId =
            $this->resolveActualSubjectId(
                $request->subject_id,
                $request->standard_id
            );

        if (!$actualSubjectId) {

            return back()->with(
                'error',
                'Unable to resolve Subject Master ID.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | YEAR
        |--------------------------------------------------------------------------
        */

        $reopenAcademicYearId =
            $request->input(
                'academic_year_id'
            );

        if (
            !$reopenAcademicYearId
        ) {

            $reopenAcademicYearId =
                TeacherMarksStatus::where(
                    'exam_master_id',
                    $request->exam_master_id
                )
                ->where(
                    'standard_id',
                    $request->standard_id
                )
                ->where(
                    'division_id',
                    $request->division_id
                )
                ->orderByDesc(
                    'id'
                )
                ->value(
                    'academic_year_id'
                )
                ??
                $exam->academic_year_id;
        }

        $yearError =
            $this->validateExamAcademicYear(
                $exam,
                $reopenAcademicYearId
            );

        if ($yearError) {

            return back()->with(
                'error',
                $yearError
            );
        }

        /*
        |--------------------------------------------------------------------------
        | POSSIBLE SUBJECT IDS
        |--------------------------------------------------------------------------
        */

        $possibleSubjectIds =
            collect([
                (int) $actualSubjectId,
            ]);

        $legacyIds =
            DB::table(
                'standard_wise_subjects'
            )
            ->where(
                'standard_id',
                $request->standard_id
            )
            ->where(
                'subject_id',
                $actualSubjectId
            )
            ->where(
                'is_active',
                1
            )
            ->pluck(
                'id'
            );

        if (
            $legacyIds->isNotEmpty()
        ) {

            $possibleSubjectIds =
                $possibleSubjectIds
                    ->merge(
                        $legacyIds
                    );
        }

        /*
        |--------------------------------------------------------------------------
        | LOAD MARKS
        |--------------------------------------------------------------------------
        */

        $marks =
            StudentMark::where(
                'exam_master_id',
                $request->exam_master_id
            )
            ->whereIn(
                'subject_id',
                $possibleSubjectIds
                    ->unique()
                    ->values()
                    ->all()
            )
            ->where(
                'standard_id',
                $request->standard_id
            )
            ->where(
                'division_id',
                $request->division_id
            )
            ->get();

        if (
            $marks->isEmpty()
        ) {

            return back()->with(
                'error',
                'No marks found for the selected Subject.'
            );
        }

        DB::transaction(
            function () use (
                $request,
                $marks
            ) {

                foreach (
                    $marks as $mark
                ) {

                    $mark->update([

                        'is_locked' =>
                            0,

                        'updated_by' =>
                            Auth::id(),
                    ]);

                    MarkAuditLog::create([

                        'student_mark_id' =>
                            $mark->id,

                        'student_id' =>
                            $mark->student_id,

                        'exam_master_id' =>
                            $mark->exam_master_id,

                        'subject_id' =>
                            $mark->subject_id,

                        'teacher_id' =>
                            Auth::id(),

                        'action' =>
                            'REOPEN',

                        'remarks' =>
                            $request->remarks
                            ??
                            'Marks reopened by admin',

                        'ip_address' =>
                            $request->ip(),

                        'user_agent' =>
                            $request->userAgent(),
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | RESET STATUS
                |--------------------------------------------------------------------------
                */

                $allocationIds =
                    $marks
                        ->pluck(
                            'teacher_subject_allocation_id'
                        )
                        ->filter()
                        ->unique()
                        ->values();

                if (
                    $allocationIds->isNotEmpty()
                ) {

                    TeacherMarksStatus::where(
                        'exam_master_id',
                        $request->exam_master_id
                    )
                    ->whereIn(
                        'teacher_subject_allocation_id',
                        $allocationIds
                    )
                    ->update([
                        'status' =>
                            'PENDING',

                        'updated_at' =>
                            now(),
                    ]);
                }
            }
        );

        return redirect()->route(
            'result-generation.admin-marks.edit',
            [

                'academic_year_id' =>
                    $reopenAcademicYearId,

                'exam_master_id' =>
                    $request->exam_master_id,

                'teacher_subject_allocation_id' =>
                    $request->input(
                        'teacher_subject_allocation_id'
                    ),

                'subject_id' =>
                    $actualSubjectId,

                'marks_reopened' =>
                    1,
            ]
        );
    }
}