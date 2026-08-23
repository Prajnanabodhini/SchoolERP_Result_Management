<?php

namespace App\Http\Controllers\Marks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\AcademicYear;
use App\Models\ExamMaster;
use App\Models\TeacherClassAllocation;
use App\Models\TeacherMarksStatus;
use App\Models\TeacherSubjectAllocation;
use App\Models\StudentMark;
use App\Models\ExamMasterSubject;
use App\Helpers\StudentHelper;

class MarkEntryController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Resolve Actual Subject ID
    |--------------------------------------------------------------------------
    |
    | TeacherSubjectAllocation.subject_id supports:
    |
    | CURRENT FORMAT:
    |     subject_id = subjects.id
    |
    | LEGACY FORMAT:
    |     subject_id = standard_wise_subjects.id
    |
    |--------------------------------------------------------------------------
    */

    private function resolveActualSubjectId(
        $storedSubjectId,
        $standardId
    ) {
        if (
            $storedSubjectId === null ||
            $storedSubjectId === '' ||
            !$standardId
        ) {
            return null;
        }

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
        | subject_id = subjects.id
        |--------------------------------------------------------------------------
        */

        $currentSubject =
            DB::table('subjects')
                ->where(
                    'id',
                    $storedSubjectId
                )
                ->where(
                    'is_active',
                    1
                )
                ->first();

        if ($currentSubject) {

            $mappingExists =
                DB::table(
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
                ->exists();

            if ($mappingExists) {
                return $storedSubjectId;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | LEGACY FORMAT
        | subject_id = standard_wise_subjects.id
        |--------------------------------------------------------------------------
        */

        $legacyMapping =
            DB::table(
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

        if (
            $legacyMapping &&
            !empty($legacyMapping->subject_id)
        ) {

            $actualSubject =
                DB::table('subjects')
                    ->where(
                        'id',
                        (int) $legacyMapping->subject_id
                    )
                    ->where(
                        'is_active',
                        1
                    )
                    ->first();

            if ($actualSubject) {
                return (int) $actualSubject->id;
            }
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Display Subject
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | 1. TeacherSubjectAllocation.subject_id is PRIMARY.
    | 2. TeacherMarksStatus.subject_id is ONLY a legacy fallback.
    |
    | This prevents an old/conflicting status subject ID from causing
    | the teacher's assignment to display the wrong subject.
    |
    |--------------------------------------------------------------------------
    */

    private function resolveDisplaySubject(
        $storedSubjectId,
        $standardId,
        $tmsSubjectId = null
    ) {
        $standardId = (int) $standardId;

        if ($standardId <= 0) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | 1. PRIMARY:
        |    TeacherSubjectAllocation.subject_id
        |--------------------------------------------------------------------------
        */

        if (
            $storedSubjectId !== null &&
            $storedSubjectId !== '' &&
            (int) $storedSubjectId > 0
        ) {

            $actualSubjectId =
                $this->resolveActualSubjectId(
                    $storedSubjectId,
                    $standardId
                );

            if ($actualSubjectId) {

                $subject =
                    DB::table('subjects')
                        ->where(
                            'id',
                            $actualSubjectId
                        )
                        ->where(
                            'is_active',
                            1
                        )
                        ->first();

                if ($subject) {
                    return $subject;
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 2. LEGACY FALLBACK:
        |    TeacherMarksStatus.subject_id
        |--------------------------------------------------------------------------
        */

        if (
            $tmsSubjectId !== null &&
            $tmsSubjectId !== '' &&
            (int) $tmsSubjectId > 0
        ) {

            $tmsSubjectId = (int) $tmsSubjectId;

            /*
            |--------------------------------------------------------------------------
            | TMS.subject_id = subjects.id
            |--------------------------------------------------------------------------
            */

            $subject =
                DB::table('subjects')
                    ->where(
                        'id',
                        $tmsSubjectId
                    )
                    ->where(
                        'is_active',
                        1
                    )
                    ->first();

            if ($subject) {

                $mappingExists =
                    DB::table(
                        'standard_wise_subjects'
                    )
                    ->where(
                        'standard_id',
                        $standardId
                    )
                    ->where(
                        'subject_id',
                        $tmsSubjectId
                    )
                    ->where(
                        'is_active',
                        1
                    )
                    ->exists();

                if ($mappingExists) {
                    return $subject;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | TMS.subject_id = standard_wise_subjects.id
            |--------------------------------------------------------------------------
            */

            $legacyMapping =
                DB::table(
                    'standard_wise_subjects as sws'
                )
                ->leftJoin(
                    'subjects as s',
                    's.id',
                    '=',
                    'sws.subject_id'
                )
                ->where(
                    'sws.id',
                    $tmsSubjectId
                )
                ->where(
                    'sws.standard_id',
                    $standardId
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
                    's.id',
                    's.subject_name',
                    's.subject_code',
                    's.short_name',
                ])
                ->first();

            if ($legacyMapping) {
                return $legacyMapping;
            }
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Administrator Detection
    |--------------------------------------------------------------------------
    */

    private function isAdministrator()
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Spatie Role
        |--------------------------------------------------------------------------
        */

        if (method_exists($user, 'hasRole')) {

            if (
                $user->hasRole('Administrator') ||
                $user->hasRole('admin')
            ) {
                return true;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Plain role column
        |--------------------------------------------------------------------------
        */

        $role =
            strtolower(
                trim(
                    (string) (
                        $user->role
                        ?? ''
                    )
                )
            );

        return in_array(
            $role,
            [
                'administrator',
                'admin',
            ],
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | MARKS ENTRY INDEX
    |--------------------------------------------------------------------------
    |
    | NO SESSION USED.
    |
    | Flow:
    |
    | Academic Year
    |      ↓
    | Exam
    |      ↓
    | Teaching Assignment
    |      ↓
    | Students
    |
    | No Class / Division filter.
    |
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


        /*
        |--------------------------------------------------------------------------
        | AUTH
        |--------------------------------------------------------------------------
        */

        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        $userId = Auth::id();

        $isAdministrator =
            $this->isAdministrator();


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

        $exams =
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
                )
                ->get();


        /*
        |--------------------------------------------------------------------------
        | SELECTED EXAM
        |--------------------------------------------------------------------------
        */

        if ($examId) {

            $exam =
                $exams->firstWhere(
                    'id',
                    (int) $examId
                );

            if (!$exam) {

                $error =
                    'Selected exam was not found.';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | TEACHING ASSIGNMENTS
        |--------------------------------------------------------------------------
        |
        | No is_active condition because the table does not have that
        | column.
        |
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


        /*
        |--------------------------------------------------------------------------
        | EXAM FILTER
        |--------------------------------------------------------------------------
        */

        if ($examId) {

            $assignmentQuery->where(
                'exam_master_id',
                $examId
            );
        }


        /*
        |--------------------------------------------------------------------------
        | ACADEMIC YEAR FILTER
        |--------------------------------------------------------------------------
        */

        if ($academicYearId) {

            $assignmentQuery->whereHas(
                'allocation',
                function ($query) use (
                    $academicYearId
                ) {

                    $query->where(
                        'academic_year_id',
                        $academicYearId
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | TEACHER SECURITY
        |--------------------------------------------------------------------------
        */

        if (!$isAdministrator) {

            $assignmentQuery->whereHas(
                'allocation',
                function ($query) use (
                    $userId
                ) {

                    $query->where(
                        'user_id',
                        $userId
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD ASSIGNMENTS
        |--------------------------------------------------------------------------
        */

        $assignments =
            $assignmentQuery
                ->orderByDesc(
                    'id'
                )
                ->get();


        /*
        |--------------------------------------------------------------------------
        | STATUS RECORDS
        |--------------------------------------------------------------------------
        */

        $assignmentIds =
            $assignments
                ->pluck('id')
                ->filter()
                ->unique()
                ->values();


        $allStatuses =
            collect();


        if ($assignmentIds->isNotEmpty()) {

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

            $allStatuses =
                $statusQuery->get([
                    'id',
                    'teacher_subject_allocation_id',
                    'subject_id',
                    'teacher_id',
                    'exam_master_id',
                    'standard_id',
                    'division_id',
                    'status',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | RESOLVE SUBJECTS FOR ASSIGNMENT LIST
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | TSA subject is primary.
        | TMS subject is only fallback.
        |--------------------------------------------------------------------------
        */

        $assignments->each(
            function ($assignment) use (
                $allStatuses
            ) {

                $allocation =
                    $assignment->allocation;

                if (!$allocation) {
                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Find matching status
                |--------------------------------------------------------------------------
                */

                $status =
                    $allStatuses
                        ->where(
                            'teacher_subject_allocation_id',
                            $assignment->id
                        )
                        ->where(
                            'exam_master_id',
                            $assignment->exam_master_id
                        )
                        ->first();


                /*
                |--------------------------------------------------------------------------
                | Fallback status
                |--------------------------------------------------------------------------
                */

                if (!$status) {

                    $status =
                        $allStatuses
                            ->firstWhere(
                                'teacher_subject_allocation_id',
                                $assignment->id
                            );
                }


                $tmsSubjectId =
                    $status
                        ? $status->subject_id
                        : null;


                /*
                |--------------------------------------------------------------------------
                | RESOLVE SUBJECT
                |--------------------------------------------------------------------------
                */

                $actualSubject =
                    $this->resolveDisplaySubject(
                        $assignment->subject_id,
                        $allocation->standard_id,
                        $tmsSubjectId
                    );


                if ($actualSubject) {

                    $assignment->setRelation(
                        'subject',
                        $actualSubject
                    );

                    $assignment->resolved_subject_id =
                        (int) $actualSubject->id;
                }


                /*
                |--------------------------------------------------------------------------
                | DATA USED BY BLADE
                |--------------------------------------------------------------------------
                */

                $assignment->resolved_academic_year_id =
                    $allocation->academic_year_id;

                $assignment->resolved_class_allocation_id =
                    $assignment->teacher_class_allocation_id;

                $assignment->resolved_exam_master_id =
                    $assignment->exam_master_id;

                $assignment->resolved_standard_id =
                    $allocation->standard_id;

                $assignment->resolved_division_id =
                    $allocation->division_id;

                $assignment->resolved_status =
                    $status
                        ? $status->status
                        : null;
            }
        );


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
                        $tsaId
                    );


            /*
            |--------------------------------------------------------------------------
            | SELECTED EXAM
            |--------------------------------------------------------------------------
            */

            if ($examId) {

                $tsaQuery->where(
                    'exam_master_id',
                    $examId
                );
            }


            /*
            |--------------------------------------------------------------------------
            | SELECTED ACADEMIC YEAR
            |--------------------------------------------------------------------------
            */

            if ($academicYearId) {

                $tsaQuery->whereHas(
                    'allocation',
                    function ($query) use (
                        $academicYearId
                    ) {

                        $query->where(
                            'academic_year_id',
                            $academicYearId
                        );
                    }
                );
            }


            /*
            |--------------------------------------------------------------------------
            | TEACHER SECURITY
            |--------------------------------------------------------------------------
            */

            if (!$isAdministrator) {

                $tsaQuery->whereHas(
                    'allocation',
                    function ($query) use (
                        $userId
                    ) {

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
                    'Selected teaching assignment was not found or is not valid for the selected exam.';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | GET CLASS ALLOCATION FROM TSA
        |--------------------------------------------------------------------------
        */

        if (
            $teacherSubjectAllocation
        ) {

            $selectedClassAllocation =
                $teacherSubjectAllocation
                    ->allocation;

            if (!$selectedClassAllocation) {

                $teacherSubjectAllocation =
                    null;

                $error =
                    'Teacher class allocation not found.';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | GET EXAM FROM TSA
        |--------------------------------------------------------------------------
        */

        if (
            $teacherSubjectAllocation &&
            !$exam
        ) {

            $exam =
                $exams->firstWhere(
                    'id',
                    $teacherSubjectAllocation
                        ->exam_master_id
                );

            if (!$exam) {

                $error =
                    'Exam linked to the teaching assignment was not found.';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | VERIFY TSA / EXAM
        |--------------------------------------------------------------------------
        */

        if (
            $teacherSubjectAllocation &&
            $exam
        ) {

            if (
                (int)
                $teacherSubjectAllocation
                    ->exam_master_id
                !==
                (int)
                $exam->id
            ) {

                $teacherSubjectAllocation =
                    null;

                $selectedClassAllocation =
                    null;

                $error =
                    'Selected teaching assignment does not belong to the selected exam.';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | RESOLVE SELECTED SUBJECT
        |--------------------------------------------------------------------------
        |
        | TSA is PRIMARY.
        |--------------------------------------------------------------------------
        */

        if (
            $teacherSubjectAllocation
        ) {

            $allocation =
                $teacherSubjectAllocation
                    ->allocation;


            /*
            |--------------------------------------------------------------------------
            | Find status
            |--------------------------------------------------------------------------
            */

            $status =
                $allStatuses
                    ->where(
                        'teacher_subject_allocation_id',
                        $teacherSubjectAllocation->id
                    )
                    ->where(
                        'exam_master_id',
                        $teacherSubjectAllocation
                            ->exam_master_id
                    )
                    ->first();


            /*
            |--------------------------------------------------------------------------
            | Direct status fallback
            |--------------------------------------------------------------------------
            */

            if (!$status) {

                $statusQuery =
                    TeacherMarksStatus::query()
                        ->where(
                            'teacher_subject_allocation_id',
                            $teacherSubjectAllocation->id
                        )
                        ->where(
                            'exam_master_id',
                            $teacherSubjectAllocation
                                ->exam_master_id
                        );

                if (!$isAdministrator) {

                    $statusQuery->where(
                        'teacher_id',
                        $userId
                    );
                }

                $status =
                    $statusQuery->first();
            }


            $tmsSubjectId =
                $status
                    ? $status->subject_id
                    : null;


            /*
            |--------------------------------------------------------------------------
            | IMPORTANT:
            |
            | TeacherSubjectAllocation.subject_id is resolved FIRST.
            |--------------------------------------------------------------------------
            */

            $actualSubject =
                $this->resolveDisplaySubject(
                    $teacherSubjectAllocation
                        ->subject_id,
                    $allocation->standard_id,
                    $tmsSubjectId
                );


            if ($actualSubject) {

                $teacherSubjectAllocation
                    ->setRelation(
                        'subject',
                        $actualSubject
                    );

                $teacherSubjectAllocation
                    ->resolved_subject_id =
                    (int)
                    $actualSubject->id;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | EXAM SUBJECT CONFIGURATION
        |--------------------------------------------------------------------------
        */

        if (
            $exam &&
            $teacherSubjectAllocation
        ) {

            $allocation =
                $teacherSubjectAllocation
                    ->allocation;


            $actualSubjectId =
                $teacherSubjectAllocation
                    ->resolved_subject_id
                ?? null;


            /*
            |--------------------------------------------------------------------------
            | FALLBACK ACTUAL SUBJECT
            |--------------------------------------------------------------------------
            */

            if (!$actualSubjectId) {

                $actualSubjectId =
                    $this->resolveActualSubjectId(
                        $teacherSubjectAllocation
                            ->subject_id,
                        $allocation->standard_id
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | CURRENT CONFIGURATION
            |--------------------------------------------------------------------------
            */

            if ($actualSubjectId) {

                $subjectConfig =
                    ExamMasterSubject::query()
                        ->where(
                            'exam_master_id',
                            $exam->id
                        )
                        ->where(
                            'standard_id',
                            $allocation
                                ->standard_id
                        )
                        ->where(
                            'subject_id',
                            $actualSubjectId
                        )
                        ->first();
            }


            /*
            |--------------------------------------------------------------------------
            | LEGACY CONFIGURATION
            |--------------------------------------------------------------------------
            */

            if (
                !$subjectConfig &&
                $actualSubjectId
            ) {

                $mapping =
                    DB::table(
                        'standard_wise_subjects'
                    )
                    ->where(
                        'standard_id',
                        $allocation->standard_id
                    )
                    ->where(
                        'subject_id',
                        $actualSubjectId
                    )
                    ->where(
                        'is_active',
                        1
                    )
                    ->first();


                if ($mapping) {

                    $subjectConfig =
                        ExamMasterSubject::query()
                            ->where(
                                'exam_master_id',
                                $exam->id
                            )
                            ->where(
                                'standard_id',
                                $allocation
                                    ->standard_id
                            )
                            ->where(
                                'subject_id',
                                $mapping->id
                            )
                            ->first();
                }
            }


            /*
            |--------------------------------------------------------------------------
            | MARK CONFIGURATION
            |--------------------------------------------------------------------------
            */

            if ($subjectConfig) {

                $theoryMaxMarks =
                    $subjectConfig->max_marks
                    ?? 0;

                $theoryPassingMarks =
                    $subjectConfig->passing_marks
                    ?? 0;

            } else {

                $subjectName =
                    optional(
                        $teacherSubjectAllocation->subject
                    )->subject_name
                    ??
                    'Selected Subject';


                $standardName =
                    optional(
                        $allocation->standard
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

            $showTheory =
                (bool) $exam->has_theory;

            $showOral =
                (bool) $exam->has_oral;

            $showPractical =
                (bool) $exam->has_practical;


            /*
            |--------------------------------------------------------------------------
            | ORAL
            |--------------------------------------------------------------------------
            */

            if ($showOral) {

                $oralMaxMarks =
                    $exam->oral_max_marks
                    ?? 0;

                $oralPassingMarks =
                    $exam->oral_passing_marks
                    ?? 0;
            }


            /*
            |--------------------------------------------------------------------------
            | PRACTICAL
            |--------------------------------------------------------------------------
            */

            if ($showPractical) {

                $practicalMaxMarks =
                    $exam->practical_max_marks
                    ?? 0;

                $practicalPassingMarks =
                    $exam->practical_passing_marks
                    ?? 0;
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
            $exam
        ) {

            $allocation =
                $selectedClassAllocation;

            try {

                $students =
                    StudentHelper::getStudentsDirectERP(
                        $allocation->academic_year_id,
                        $allocation->standard_id,
                        $allocation->division_id
                    );


                /*
                |--------------------------------------------------------------------------
                | GIRLS FIRST
                |--------------------------------------------------------------------------
                */

                $students =
                    $students
                        ->sort(
                            function (
                                $a,
                                $b
                            ) {

                                $genderA =
                                    strtoupper(
                                        trim(
                                            $a->gender
                                            ?? ''
                                        )
                                    );

                                $genderB =
                                    strtoupper(
                                        trim(
                                            $b->gender
                                            ?? ''
                                        )
                                    );


                                if (
                                    $genderA !==
                                    $genderB
                                ) {

                                    if (
                                        in_array(
                                            $genderA,
                                            [
                                                'F',
                                                'FEMALE'
                                            ],
                                            true
                                        )
                                    ) {
                                        return -1;
                                    }

                                    if (
                                        in_array(
                                            $genderB,
                                            [
                                                'F',
                                                'FEMALE'
                                            ],
                                            true
                                        )
                                    ) {
                                        return 1;
                                    }
                                }


                                return strcmp(
                                    strtoupper(
                                        trim(
                                            $a->studname
                                            ?? ''
                                        )
                                    ),
                                    strtoupper(
                                        trim(
                                            $b->studname
                                            ?? ''
                                        )
                                    )
                                );
                            }
                        )
                        ->values();


                /*
                |--------------------------------------------------------------------------
                | NO STUDENTS
                |--------------------------------------------------------------------------
                */

                if ($students->isEmpty()) {

                    $standardName =
                        optional(
                            $allocation->standard
                        )->standard_name
                        ??
                        'Selected Standard';


                    $divisionName =
                        optional(
                            $allocation->division
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

            } catch (\Throwable $e) {

                report($e);

                $students =
                    collect();

                $error =
                    'Old ERP Error: '
                    . $e->getMessage();
            }
        }


        /*
        |--------------------------------------------------------------------------
        | MARK STATUS
        |--------------------------------------------------------------------------
        */

        if (
            $teacherSubjectAllocation &&
            $exam
        ) {

            $marksStatusQuery =
                TeacherMarksStatus::query()
                    ->where(
                        'teacher_subject_allocation_id',
                        $teacherSubjectAllocation->id
                    )
                    ->where(
                        'exam_master_id',
                        $exam->id
                    );


            if (!$isAdministrator) {

                $marksStatusQuery->where(
                    'teacher_id',
                    $userId
                );
            }


            $marksStatus =
                $marksStatusQuery
                    ->first();


            /*
            |--------------------------------------------------------------------------
            | COMPLETED
            |--------------------------------------------------------------------------
            */

            if (
                $marksStatus &&
                strtoupper(
                    trim(
                        $marksStatus->status
                        ?? ''
                    )
                ) === 'COMPLETED'
            ) {

                $marksLocked =
                    true;

                $message =
                    'Marks entry has already been completed and is locked.';

                $students =
                    collect();
            }
        }


        /*
        |--------------------------------------------------------------------------
        | EXISTING MARKS
        |--------------------------------------------------------------------------
        */

        if (
            $teacherSubjectAllocation &&
            $exam
        ) {

            $existingMarks =
                StudentMark::query()
                    ->where(
                        'exam_master_id',
                        $exam->id
                    )
                    ->where(
                        'teacher_subject_allocation_id',
                        $teacherSubjectAllocation->id
                    )
                    ->get()
                    ->keyBy(
                        'student_id'
                    );
        }


        /*
        |--------------------------------------------------------------------------
        | RETURN
        |--------------------------------------------------------------------------
        */

        return view(
            'marks-entry.index',
            compact(
                'request',
                'academicYears',
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
                'existingMarks'
            )
        );
    }
}