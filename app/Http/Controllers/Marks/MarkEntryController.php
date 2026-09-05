<?php

namespace App\Http\Controllers\Marks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\AcademicYear;
use App\Models\ExamMaster;
use App\Models\TeacherMarksStatus;
use App\Models\TeacherSubjectAllocation;
use App\Models\StudentMark;

use App\Helpers\StudentHelper;
use App\Helpers\MarksHelper;

class MarkEntryController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | INITIAL VALUES
        |--------------------------------------------------------------------------
        */

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

        $marksLocked = false;

        $existingMarks = collect();

        $marksStatus = null;

        $isOptionalEnabled = false;

        $passingPercentage = 40;


        /*
        |--------------------------------------------------------------------------
        | AUTH
        |--------------------------------------------------------------------------
        */

        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        $userId = (int) Auth::id();

        $isAdministrator =
            MarksHelper::isAdministrator();


        /*
        |--------------------------------------------------------------------------
        | REQUEST VALUES
        |--------------------------------------------------------------------------
        */

        $academicYearId =
            $request->input(
                'academic_year_id'
            );

        $examId =
            $request->input(
                'exam_master_id'
            );

        $tsaId =
            $request->input(
                'teacher_subject_allocation_id'
            );


        /*
        |--------------------------------------------------------------------------
        | ACADEMIC YEARS
        |--------------------------------------------------------------------------
        */

        $academicYears =
            AcademicYear::query()
                ->where(
                    'is_active',
                    1
                )
                ->orderByDesc(
                    'id'
                )
                ->get();


        /*
        |--------------------------------------------------------------------------
        | EXAMS
        |--------------------------------------------------------------------------
        */

        $examQuery =
            ExamMaster::query()
                ->where(
                    'is_active',
                    1
                )
                ->orderBy(
                    'display_order'
                )
                ->orderBy(
                    'exam_name'
                );

        if (
            $academicYearId !== null &&
            $academicYearId !== ''
        ) {

            $examQuery->where(
                'academic_year_id',
                (int) $academicYearId
            );
        }

        $exams =
            $examQuery->get();


        /*
        |--------------------------------------------------------------------------
        | SELECTED EXAM
        |--------------------------------------------------------------------------
        */

        if ($examId) {

            $exam =
                ExamMaster::where(
                    'id',
                    (int) $examId
                )
                ->where(
                    'is_active',
                    1
                )
                ->first();

            if (!$exam) {

                $error =
                    'Selected exam was not found.';

            } else {

                $yearError =
                    MarksHelper::validateExamAcademicYear(
                        $exam,
                        null,
                        $academicYearId
                    );

                if ($yearError) {

                    $error =
                        $yearError;

                    $exam = null;
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | SELECTED TSA
        |--------------------------------------------------------------------------
        */

        if ($tsaId) {

            $tsaQuery =
                TeacherSubjectAllocation::query()
                    ->with([
                        'allocation.teacher',
                        'allocation.academicYear',
                        'allocation.section',
                        'allocation.standard',
                        'allocation.division',
                        'exam',
                    ])
                    ->where(
                        'id',
                        (int) $tsaId
                    );

            if ($examId) {

                $tsaQuery->where(
                    'exam_master_id',
                    (int) $examId
                );
            }

            if (!$isAdministrator) {

                $tsaQuery->whereHas(
                    'allocation',
                    function ($query) use ($userId) {

                        $query->where(
                            'user_id',
                            $userId
                        );
                    }
                );
            }

            $teacherSubjectAllocation =
                $tsaQuery->first();


            if (!$teacherSubjectAllocation) {

                $error =
                    'Selected teaching assignment was not found or is not assigned to you.';

            } else {

                $selectedClassAllocation =
                    $teacherSubjectAllocation
                        ->allocation;


                if (!$selectedClassAllocation) {

                    $teacherSubjectAllocation =
                        null;

                    $error =
                        'Teacher class allocation not found.';

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | OPTIONAL / PASSING
                    |--------------------------------------------------------------------------
                    */

                    $isOptionalEnabled =
                        MarksHelper::isOptionalEnabledForAllocation(
                            $selectedClassAllocation
                        );

                    $passingPercentage =
                        MarksHelper::getPassingPercentage(
                            $selectedClassAllocation
                                ->standard_id
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | RESOLVE YEAR
                    |--------------------------------------------------------------------------
                    */

                    $resolvedYear =
                        $selectedClassAllocation
                            ->academic_year_id
                        ?? null;

                    if (
                        $resolvedYear !== null &&
                        $resolvedYear !== ''
                    ) {

                        $academicYearId =
                            (int) $resolvedYear;

                        $request->merge([
                            'academic_year_id' =>
                                $academicYearId,
                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | EXAM FROM TSA
                    |--------------------------------------------------------------------------
                    */

                    $tsaExam =
                        $teacherSubjectAllocation
                            ->exam;

                    if (!$tsaExam) {

                        $error =
                            'Exam linked to the selected teaching assignment was not found.';

                    } else {

                        $yearError =
                            MarksHelper::validateExamAcademicYear(
                                $tsaExam,
                                $selectedClassAllocation,
                                $academicYearId
                            );

                        if ($yearError) {

                            $error =
                                $yearError;

                        } else {

                            $exam =
                                $tsaExam;

                            if (
                                !$exams->contains(
                                    'id',
                                    $exam->id
                                )
                            ) {

                                $exams->push(
                                    $exam
                                );

                                $exams =
                                    $exams
                                        ->sortBy([
                                            [
                                                'display_order',
                                                'asc',
                                            ],
                                            [
                                                'exam_name',
                                                'asc',
                                            ],
                                        ])
                                        ->values();
                            }
                        }
                    }
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | EXAM YEAR CHECK
        |--------------------------------------------------------------------------
        */

        if (
            $exam &&
            !$teacherSubjectAllocation
        ) {

            $yearError =
                MarksHelper::validateExamAcademicYear(
                    $exam,
                    null,
                    $academicYearId
                );

            if ($yearError) {

                $error =
                    $yearError;

                $exam = null;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD ASSIGNMENTS
        |--------------------------------------------------------------------------
        */

        $assignmentQuery =
            TeacherSubjectAllocation::query()
                ->with([
                    'allocation.teacher',
                    'allocation.academicYear',
                    'allocation.section',
                    'allocation.standard',
                    'allocation.division',
                    'exam',
                ]);

        if ($examId) {

            $assignmentQuery->where(
                'exam_master_id',
                (int) $examId
            );
        }

        if (
            $academicYearId !== null &&
            $academicYearId !== ''
        ) {

            $assignmentQuery->whereHas(
                'allocation',
                function ($query) use ($academicYearId) {

                    $query->where(
                        'academic_year_id',
                        (int) $academicYearId
                    );
                }
            );
        }

        if (!$isAdministrator) {

            $assignmentQuery->whereHas(
                'allocation',
                function ($query) use ($userId) {

                    $query->where(
                        'user_id',
                        $userId
                    );
                }
            );
        }

        $assignments =
            $assignmentQuery
                ->orderByDesc('id')
                ->get();


        /*
        |--------------------------------------------------------------------------
        | REMOVE INVALID ASSIGNMENTS
        |--------------------------------------------------------------------------
        */

        $assignments =
            $assignments
                ->filter(
                    function ($assignment) {

                        $allocation =
                            $assignment->allocation;

                        $assignmentExam =
                            $assignment->exam;

                        if (
                            !$allocation ||
                            !$assignmentExam
                        ) {
                            return false;
                        }

                        if (
                            $assignmentExam
                                ->academic_year_id === null
                        ) {
                            return true;
                        }

                        return
                            (int)
                            $assignmentExam
                                ->academic_year_id
                            ===
                            (int)
                            $allocation
                                ->academic_year_id;
                    }
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | LOAD STATUS
        |--------------------------------------------------------------------------
        */

        $assignmentIds =
            $assignments
                ->pluck('id')
                ->filter()
                ->unique()
                ->values();

        $allStatuses = collect();

        if (
            $assignmentIds->isNotEmpty()
        ) {

            $statusQuery =
                TeacherMarksStatus::query()
                    ->whereIn(
                        'teacher_subject_allocation_id',
                        $assignmentIds
                    );

            if (!$isAdministrator) {

                $statusQuery->where(
                    'teacher_id',
                    $userId
                );
            }

            if (
                $academicYearId !== null &&
                $academicYearId !== ''
            ) {

                $statusQuery->where(
                    'academic_year_id',
                    (int) $academicYearId
                );
            }

            $allStatuses =
                $statusQuery->get([
                    'id',
                    'teacher_subject_allocation_id',
                    'subject_id',
                    'teacher_id',
                    'exam_master_id',
                    'standard_id',
                    'division_id',
                    'academic_year_id',
                    'status',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD SUBJECTS
        |--------------------------------------------------------------------------
        */

        $standardIds =
            $assignments
                ->pluck(
                    'allocation.standard_id'
                )
                ->filter()
                ->unique()
                ->values();

        $allSubjects = collect();

        if (
            $standardIds->isNotEmpty()
        ) {

            $allSubjects =
                DB::table(
                    'subjects as s'
                )
                ->join(
                    'standard_wise_subjects as sws',
                    'sws.subject_id',
                    '=',
                    's.id'
                )
                ->whereIn(
                    'sws.standard_id',
                    $standardIds
                )
                ->where(
                    's.is_active',
                    1
                )
                ->where(
                    'sws.is_active',
                    1
                )
                ->select([
                    's.id',
                    's.subject_name',
                    's.subject_code',
                    's.short_name',
                ])
                ->distinct()
                ->get()
                ->keyBy('id');
        }


        /*
        |--------------------------------------------------------------------------
        | SUBJECT MAP
        |--------------------------------------------------------------------------
        */

        $subjectMap =
            MarksHelper::buildSubjectResolutionMap(
                $assignments
            );


        /*
        |--------------------------------------------------------------------------
        | STATUS MAP
        |--------------------------------------------------------------------------
        */

        $statusMap =
            $allStatuses->keyBy(
                function ($status) {

                    return
                        $status
                            ->teacher_subject_allocation_id
                        . ':'
                        .
                        $status
                            ->exam_master_id;
                }
            );


        /*
        |--------------------------------------------------------------------------
        | RESOLVE ASSIGNMENTS
        |--------------------------------------------------------------------------
        */

        foreach (
            $assignments as $assignment
        ) {

            $allocation =
                $assignment
                    ->allocation;

            if (!$allocation) {
                continue;
            }

            $statusKey =
                $assignment->id
                . ':'
                .
                $assignment->exam_master_id;

            $status =
                $statusMap->get(
                    $statusKey
                );

            if (!$status) {

                $status =
                    $allStatuses->firstWhere(
                        'teacher_subject_allocation_id',
                        $assignment->id
                    );
            }

            $tmsSubjectId =
                $status
                    ? $status->subject_id
                    : null;

            $actualSubject =
                MarksHelper::resolveDisplaySubject(
                    $assignment->subject_id,
                    $allocation->standard_id,
                    $tmsSubjectId,
                    $subjectMap,
                    $allSubjects
                );

            if ($actualSubject) {

                $assignment->setRelation(
                    'subject',
                    $actualSubject
                );

                $assignment->resolved_subject_id =
                    (int) $actualSubject->id;
            }

            $assignment->resolved_academic_year_id =
                $allocation
                    ->academic_year_id;

            $assignment->resolved_class_allocation_id =
                $assignment
                    ->teacher_class_allocation_id;

            $assignment->resolved_exam_master_id =
                $assignment
                    ->exam_master_id;

            $assignment->resolved_standard_id =
                $allocation
                    ->standard_id;

            $assignment->resolved_division_id =
                $allocation
                    ->division_id;

            $assignment->resolved_status =
                $status
                    ? strtoupper(
                        trim(
                            (string) (
                                $status->status ?? ''
                            )
                        )
                    )
                    : null;
        }


        /*
        |--------------------------------------------------------------------------
        | SELECTED STATUS
        |--------------------------------------------------------------------------
        */

        if (
            $teacherSubjectAllocation
        ) {

            $selectedAllocation =
                $teacherSubjectAllocation
                    ->allocation;

            $isOptionalEnabled =
                MarksHelper::isOptionalEnabledForAllocation(
                    $selectedAllocation
                );

            $passingPercentage =
                MarksHelper::getPassingPercentage(
                    $selectedAllocation
                        ->standard_id
                );

            $statusKey =
                $teacherSubjectAllocation->id
                . ':'
                .
                $teacherSubjectAllocation
                    ->exam_master_id;

            $marksStatus =
                $statusMap->get(
                    $statusKey
                );

            if (!$marksStatus) {

                $marksStatus =
                    TeacherMarksStatus::query()
                        ->where(
                            'teacher_subject_allocation_id',
                            $teacherSubjectAllocation->id
                        )
                        ->where(
                            'exam_master_id',
                            $teacherSubjectAllocation
                                ->exam_master_id
                        )
                        ->when(
                            !$isAdministrator,
                            function ($query) use ($userId) {

                                $query->where(
                                    'teacher_id',
                                    $userId
                                );
                            }
                        )
                        ->when(
                            $academicYearId !== null &&
                            $academicYearId !== '',
                            function ($query) use (
                                $academicYearId
                            ) {

                                $query->where(
                                    'academic_year_id',
                                    (int) $academicYearId
                                );
                            }
                        )
                        ->orderByDesc('id')
                        ->first();
            }

            $tmsSubjectId =
                $marksStatus
                    ? $marksStatus->subject_id
                    : null;

            $actualSubject =
                MarksHelper::resolveDisplaySubject(
                    $teacherSubjectAllocation
                        ->subject_id,
                    $selectedAllocation
                        ->standard_id,
                    $tmsSubjectId,
                    $subjectMap,
                    $allSubjects
                );

            if ($actualSubject) {

                $teacherSubjectAllocation
                    ->setRelation(
                        'subject',
                        $actualSubject
                    );

                $teacherSubjectAllocation
                    ->resolved_subject_id =
                    (int) $actualSubject->id;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | SUBJECT CONFIGURATION
        |--------------------------------------------------------------------------
        */

        if (
            $exam &&
            $teacherSubjectAllocation
        ) {

            $allocation =
                $teacherSubjectAllocation
                    ->allocation;

            $yearError =
                MarksHelper::validateExamAcademicYear(
                    $exam,
                    $allocation,
                    $academicYearId
                );

            if ($yearError) {

                $error =
                    $yearError;

                $students = collect();

                $existingMarks = collect();

            } else {

                $isOptionalEnabled =
                    MarksHelper::isOptionalEnabledForAllocation(
                        $allocation
                    );

                $passingPercentage =
                    MarksHelper::getPassingPercentage(
                        $allocation->standard_id
                    );

                $actualSubjectId =
                    $teacherSubjectAllocation
                        ->resolved_subject_id
                    ?? null;

                if ($actualSubjectId) {

                    $subjectConfig =
                        MarksHelper::resolveExamSubjectConfig(
                            $exam,
                            $allocation->standard_id,
                            $actualSubjectId
                        );
                }

                if (!$subjectConfig) {

                    $subjectName =
                        optional(
                            $teacherSubjectAllocation
                                ->subject
                        )->subject_name
                        ??
                        'Selected Subject';

                    $standardName =
                        optional(
                            $allocation
                                ->standard
                        )->standard_name
                        ??
                        'Selected Standard';

                    $error =
                        'Marks configuration not found for '
                        . $subjectName
                        . ' in '
                        . $standardName
                        . ' - '
                        . $exam->exam_name
                        . '. Please configure this subject in Exam Master.';
                }

                /*
                |--------------------------------------------------------------------------
                | COMPONENTS
                |--------------------------------------------------------------------------
                */

                $componentData =
                    MarksHelper::getComponentMaxMarks(
                        $exam,
                        $subjectConfig
                    );

                $showTheory =
                    $componentData['show_theory'];

                $showOral =
                    $componentData['show_oral'];

                $showPractical =
                    $componentData['show_practical'];

                $theoryMaxMarks =
                    $componentData['theory_max'];

                $oralMaxMarks =
                    $componentData['oral_max'];

                $practicalMaxMarks =
                    $componentData['practical_max'];


                /*
                |--------------------------------------------------------------------------
                | PASSING MARKS
                |--------------------------------------------------------------------------
                */

                $theoryPassingMarks =
                    MarksHelper::getPassingMarks(
                        $allocation->standard_id,
                        $theoryMaxMarks
                    );

                $oralPassingMarks =
                    MarksHelper::getPassingMarks(
                        $allocation->standard_id,
                        $oralMaxMarks
                    );

                $practicalPassingMarks =
                    MarksHelper::getPassingMarks(
                        $allocation->standard_id,
                        $practicalMaxMarks
                    );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD STUDENTS
        |--------------------------------------------------------------------------
        */

        if (
            $teacherSubjectAllocation &&
            $selectedClassAllocation &&
            $exam &&
            !$error
        ) {

            $allocation =
                $selectedClassAllocation;

            $isOptionalEnabled =
                MarksHelper::isOptionalEnabledForAllocation(
                    $allocation
                );

            $passingPercentage =
                MarksHelper::getPassingPercentage(
                    $allocation->standard_id
                );

            $yearError =
                MarksHelper::validateExamAcademicYear(
                    $exam,
                    $allocation,
                    $academicYearId
                );

            if ($yearError) {

                $error =
                    $yearError;

                $students = collect();

            } else {

                $erpAcademicYearId =
                    (int) (
                        $allocation->academic_year_id
                        ?? 0
                    );

                $erpStandardId =
                    (int) (
                        $allocation->standard_id
                        ?? 0
                    );

                $erpDivisionId =
                    (int) (
                        $allocation->division_id
                        ?? 0
                    );

                try {

                    if (
                        $erpAcademicYearId > 0 &&
                        $erpStandardId > 0 &&
                        $erpDivisionId > 0
                    ) {

                        $students =
                            StudentHelper::getStudentsDirectERP(
                                $erpAcademicYearId,
                                $erpStandardId,
                                $erpDivisionId
                            );

                        $students =
                            MarksHelper::sortStudentsByRoll(
                                $students
                            );
                    }

                } catch (\Throwable $e) {

                    report($e);

                    $students = collect();

                    $error =
                        'Old ERP Error: ' .
                        $e->getMessage();
                }

                if (
                    $students->isEmpty()
                ) {

                    $standardName =
                        optional(
                            $allocation
                                ->standard
                        )->standard_name
                        ??
                        'Selected Standard';

                    $divisionName =
                        optional(
                            $allocation
                                ->division
                        )->division_name
                        ??
                        'Selected Division';

                    $error =
                        'No students found for '
                        . $standardName
                        . ' - '
                        . $divisionName
                        . '. Please verify Old ERP student mapping.';
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | LOCK STATUS
        |--------------------------------------------------------------------------
        */

        if (
            $teacherSubjectAllocation &&
            $exam
        ) {

            $currentStatus =
                strtoupper(
                    trim(
                        (string) (
                            $marksStatus->status
                            ?? ''
                        )
                    )
                );

            if (
                $currentStatus === 'COMPLETED'
            ) {

                $marksLocked = true;

                $message =
                    'Marks entry has already been completed and is locked.';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | EXISTING MARKS
        |--------------------------------------------------------------------------
        */

        if (
            $teacherSubjectAllocation &&
            $exam &&
            $selectedClassAllocation &&
            !$error
        ) {

            $actualSubjectId =
                $teacherSubjectAllocation
                    ->resolved_subject_id
                ?? null;

            /*
            |--------------------------------------------------------------------------
            | LOAD EXISTING MARKS
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            | Existing student_marks records are identified by the actual class
            | and subject, not by teacher_subject_allocation_id.
            |
            | Older records may contain a different TSA ID. Therefore the lookup
            | intentionally does NOT use teacher_subject_allocation_id.
            |
            | The collection is keyed by student_id because
            | MarksEntryBladeHelper::getExistingMark() reads it by student_id.
            |
            |--------------------------------------------------------------------------
            */

            $existingMarks =
                StudentMark::query()
                    ->where(
                        'academic_year_id',
                        $selectedClassAllocation->academic_year_id
                    )
                    ->where(
                        'section_id',
                        $selectedClassAllocation->section_id
                    )
                    ->where(
                        'standard_id',
                        $selectedClassAllocation->standard_id
                    )
                    ->where(
                        'division_id',
                        $selectedClassAllocation->division_id
                    )
                    ->where(
                        'exam_master_id',
                        $exam->id
                    )
                    ->where(
                        'subject_id',
                        $actualSubjectId
                    )
                    ->get()
                    ->keyBy(function ($mark) {
                        return (string) $mark->student_id;
                    });

            /*
            |--------------------------------------------------------------------------
            | EXISTING MARKS = LOCKED
            |--------------------------------------------------------------------------
            |
            | If any marks already exist for this exact Academic Year + Section +
            | Standard + Division + Exam + Subject combination, the page is locked.
            | This also handles legacy marks saved under another TSA ID.
            |
            |--------------------------------------------------------------------------
            */

            if ($existingMarks->isNotEmpty()) {

                $marksLocked = true;

                /*
                | Keep all matching records locked.
                */
                StudentMark::query()
                    ->where(
                        'academic_year_id',
                        $selectedClassAllocation->academic_year_id
                    )
                    ->where(
                        'section_id',
                        $selectedClassAllocation->section_id
                    )
                    ->where(
                        'standard_id',
                        $selectedClassAllocation->standard_id
                    )
                    ->where(
                        'division_id',
                        $selectedClassAllocation->division_id
                    )
                    ->where(
                        'exam_master_id',
                        $exam->id
                    )
                    ->where(
                        'subject_id',
                        $actualSubjectId
                    )
                    ->where(
                        'is_locked',
                        '!=',
                        1
                    )
                    ->update([
                        'is_locked' => 1,
                    ]);

                /*
                | Refresh the collection so the Blade receives the current
                | locked records.
                */
                $existingMarks =
                    StudentMark::query()
                        ->where(
                            'academic_year_id',
                            $selectedClassAllocation->academic_year_id
                        )
                        ->where(
                            'section_id',
                            $selectedClassAllocation->section_id
                        )
                        ->where(
                            'standard_id',
                            $selectedClassAllocation->standard_id
                        )
                        ->where(
                            'division_id',
                            $selectedClassAllocation->division_id
                        )
                        ->where(
                            'exam_master_id',
                            $exam->id
                        )
                        ->where(
                            'subject_id',
                            $actualSubjectId
                        )
                        ->get()
                        ->keyBy(function ($mark) {
                            return (string) $mark->student_id;
                        });
            }
        }


        /*
        |--------------------------------------------------------------------------
        | REQUEST YEAR
        |--------------------------------------------------------------------------
        */

        if (
            $academicYearId !== null &&
            $academicYearId !== ''
        ) {

            $request->merge([
                'academic_year_id' =>
                    $academicYearId,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'marks-entry.index',
            compact(
                'request',
                'academicYears',
                'academicYearId',
                'assignments',
                'exams',
                'students',
                'exam',
                'teacherSubjectAllocation',
                'selectedClassAllocation',
                'subjectConfig',
                'showTheory',
                'showOral',
                'showPractical',
                'marksLocked',
                'message',
                'error',
                'theoryMaxMarks',
                'theoryPassingMarks',
                'oralMaxMarks',
                'oralPassingMarks',
                'practicalMaxMarks',
                'practicalPassingMarks',
                'existingMarks',
                'isOptionalEnabled',
                'passingPercentage'
            )
        );
    }
}