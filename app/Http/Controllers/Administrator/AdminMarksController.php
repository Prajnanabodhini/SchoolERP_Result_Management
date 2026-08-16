<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
    | RESOLVE ACTUAL SUBJECT ID
    |--------------------------------------------------------------------------
    |
    | AUTHORITATIVE SOURCE:
    |
    | standard_wise_subjects
    |
    | Supports both existing formats:
    |
    | 1. stored value = standard_wise_subjects.subject_id
    | 2. stored value = standard_wise_subjects.id
    |
    | The selected STANDARD is always applied.
    |
    */

    private function resolveActualSubjectId(
        $storedSubjectId,
        $standardId
    ) {
        if (
            !$storedSubjectId ||
            !$standardId
        ) {
            return null;
        }

        $storedSubjectId = (int) $storedSubjectId;
        $standardId = (int) $standardId;


        /*
        |--------------------------------------------------------------------------
        | FORMAT 1
        |--------------------------------------------------------------------------
        |
        | storedSubjectId = subjects.id
        |
        | Match directly against standard_wise_subjects.subject_id
        |
        */

        $mappingBySubjectId =
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
            ->first();


        if (
            $mappingBySubjectId &&
            !empty(
                $mappingBySubjectId->subject_id
            )
        ) {
            return (int)
                $mappingBySubjectId->subject_id;
        }


        /*
        |--------------------------------------------------------------------------
        | FORMAT 2
        |--------------------------------------------------------------------------
        |
        | storedSubjectId = standard_wise_subjects.id
        |
        */

        $mappingById =
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
            $mappingById &&
            !empty(
                $mappingById->subject_id
            )
        ) {
            return (int)
                $mappingById->subject_id;
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE DISPLAY SUBJECT
    |--------------------------------------------------------------------------
    |
    | NEVER use teacher_marks_status.subject_id directly as subjects.id.
    |
    | Always resolve through standard_wise_subjects.
    |
    */

    private function resolveDisplaySubject(
        $storedSubjectId,
        $standardId
    ) {
        $actualSubjectId =
            $this->resolveActualSubjectId(
                $storedSubjectId,
                $standardId
            );


        if (!$actualSubjectId) {
            return null;
        }


        return DB::table(
            'standard_wise_subjects as sws'
        )
        ->join(
            'subjects as s',
            's.id',
            '=',
            'sws.subject_id'
        )
        ->where(
            'sws.standard_id',
            $standardId
        )
        ->where(
            'sws.subject_id',
            $actualSubjectId
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
        /*
        |--------------------------------------------------------------------------
        | DIRECT STANDARD
        |--------------------------------------------------------------------------
        */

        if (
            !empty(
                $exam->standard_id
            )
        ) {

            $standard =
                $standards->firstWhere(
                    'id',
                    (int)
                    $exam->standard_id
                );


            if ($standard) {

                return [
                    'id' =>
                        (int)
                        $standard->id,

                    'name' =>
                        $standard->standard_name,
                ];
            }
        }


        /*
        |--------------------------------------------------------------------------
        | EXAM NAME FALLBACK
        |--------------------------------------------------------------------------
        */

        $examName =
            strtoupper(
                trim(
                    (string)
                    $exam->exam_name
                )
            );


        $normalizedExamName =
            preg_replace(
                '/[^A-Z0-9]/',
                '',
                $examName
            );


        $sortedStandards =
            $standards->sortByDesc(
                function ($standard) {

                    return strlen(
                        preg_replace(
                            '/[^A-Z0-9]/',
                            '',
                            strtoupper(
                                trim(
                                    (string)
                                    $standard->standard_name
                                )
                            )
                        )
                    );
                }
            );


        foreach (
            $sortedStandards as $standard
        ) {

            $standardName =
                strtoupper(
                    trim(
                        (string)
                        $standard->standard_name
                    )
                );


            $normalizedStandardName =
                preg_replace(
                    '/[^A-Z0-9]/',
                    '',
                    $standardName
                );


            if (
                $normalizedStandardName !== ''
                &&
                (
                    str_ends_with(
                        $normalizedExamName,
                        $normalizedStandardName
                    )
                    ||
                    str_contains(
                        $normalizedExamName,
                        $normalizedStandardName
                    )
                )
            ) {

                return [
                    'id' =>
                        (int)
                        $standard->id,

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

        foreach (
            $exams as $exam
        ) {

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
                    (string)
                    $exam->exam_name
                );


            $standardName =
                trim(
                    (string)
                    (
                        $resolved['name']
                        ?? ''
                    )
                );


            $normalizedExam =
                preg_replace(
                    '/[^A-Z0-9]/',
                    '',
                    strtoupper(
                        $examName
                    )
                );


            $normalizedStandard =
                preg_replace(
                    '/[^A-Z0-9]/',
                    '',
                    strtoupper(
                        $standardName
                    )
                );


            if (
                $standardName !== ''
                &&
                $normalizedStandard !== ''
                &&
                !str_ends_with(
                    $normalizedExam,
                    $normalizedStandard
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
    | BUILD ADMIN ASSIGNMENTS FROM teacher_marks_status
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | Exam Progress already uses teacher_marks_status as its source.
    |
    | We do the same here.
    |
    | This allows Admin to see records such as:
    |
    | TSA reference 113
    | Tenth-B
    | Marathi
    | Completed
    |
    | even when teacher_subject_allocations.id = 113 does not exist.
    |
    */

    private function getAssignments(
        $academicYearId = null,
        $examId = null
    ) {

        /*
        |--------------------------------------------------------------------------
        | STATUS QUERY
        |--------------------------------------------------------------------------
        */

        $statusQuery =
            TeacherMarksStatus::query();


        if ($academicYearId) {

            $statusQuery->where(
                'academic_year_id',
                $academicYearId
            );
        }


        if ($examId) {

            $statusQuery->where(
                'exam_master_id',
                $examId
            );
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD
        |--------------------------------------------------------------------------
        */

        $statuses =
            $statusQuery
                ->orderByDesc(
                    'id'
                )
                ->get();


        /*
        |--------------------------------------------------------------------------
        | BUILD ASSIGNMENT COLLECTION
        |--------------------------------------------------------------------------
        */

        $assignments =
            collect();


        foreach (
            $statuses as $status
        ) {

            /*
            |--------------------------------------------------------------------------
            | STANDARD
            |--------------------------------------------------------------------------
            */

            $standard =
                Standard::find(
                    $status->standard_id
                );


            /*
            |--------------------------------------------------------------------------
            | DIVISION
            |--------------------------------------------------------------------------
            */

            $division =
                Division::find(
                    $status->division_id
                );


            /*
            |--------------------------------------------------------------------------
            | ACADEMIC YEAR
            |--------------------------------------------------------------------------
            */

            $academicYear =
                AcademicYear::find(
                    $status->academic_year_id
                );


            /*
            |--------------------------------------------------------------------------
            | TEACHER
            |--------------------------------------------------------------------------
            */

            $teacher =
                User::find(
                    $status->teacher_id
                );


            /*
            |--------------------------------------------------------------------------
            | SUBJECT
            |--------------------------------------------------------------------------
            |
            | AUTHORITATIVE:
            |
            | TMS.subject_id
            |       ↓
            | standard_wise_subjects
            |       ↓
            | subjects
            |
            */

            $actualSubject =
                $this->resolveDisplaySubject(
                    $status->subject_id,
                    $status->standard_id
                );


            if (!$actualSubject) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | CLASS ALLOCATION
            |--------------------------------------------------------------------------
            |
            | We try to find the real TeacherClassAllocation.
            |
            | This is not required for orphaned TSA records.
            |
            */

            $allocation =
                TeacherClassAllocation::with([
                    'teacher',
                    'academicYear',
                    'section',
                    'standard',
                    'division',
                ])
                ->where(
                    'academic_year_id',
                    $status->academic_year_id
                )
                ->where(
                    'standard_id',
                    $status->standard_id
                )
                ->where(
                    'division_id',
                    $status->division_id
                )
                ->when(
                    $status->teacher_id,
                    function ($query) use ($status) {

                        $query->where(
                            'user_id',
                            $status->teacher_id
                        );
                    }
                )
                ->orderByDesc(
                    'id'
                )
                ->first();


            /*
            |--------------------------------------------------------------------------
            | SYNTHETIC ALLOCATION
            |--------------------------------------------------------------------------
            |
            | If no matching TeacherClassAllocation exists, create an
            | in-memory model only. It is NOT inserted into DB.
            |
            */

            if (!$allocation) {

                $allocation =
                    new TeacherClassAllocation();


                $allocation->academic_year_id =
                    $status->academic_year_id;


                $allocation->standard_id =
                    $status->standard_id;


                $allocation->division_id =
                    $status->division_id;


                $allocation->user_id =
                    $status->teacher_id;


                $allocation->setRelation(
                    'teacher',
                    $teacher
                );


                $allocation->setRelation(
                    'academicYear',
                    $academicYear
                );


                $allocation->setRelation(
                    'standard',
                    $standard
                );


                $allocation->setRelation(
                    'division',
                    $division
                );
            }


            /*
            |--------------------------------------------------------------------------
            | BUILD TSA-LIKE MODEL
            |--------------------------------------------------------------------------
            |
            | The Blade expects:
            |
            | $assignment->id
            | $assignment->allocation
            | $assignment->subject
            |
            | Use TMS teacher_subject_allocation_id as the ID.
            |
            */

            $assignment =
                new TeacherSubjectAllocation();


            $assignment->id =
                $status
                    ->teacher_subject_allocation_id;


            $assignment->teacher_class_allocation_id =
                optional(
                    $allocation
                )->id;


            $assignment->exam_master_id =
                $status->exam_master_id;


            /*
            | IMPORTANT:
            |
            | Store the resolved actual subject ID.
            |
            */

            $assignment->subject_id =
                $actualSubject->id;


            /*
            |--------------------------------------------------------------------------
            | RELATIONSHIPS
            |--------------------------------------------------------------------------
            */

            $assignment->setRelation(
                'allocation',
                $allocation
            );


            $assignment->setRelation(
                'subject',
                $actualSubject
            );


            $assignment->setRelation(
                'exam',
                ExamMaster::find(
                    $status->exam_master_id
                )
            );


            /*
            |--------------------------------------------------------------------------
            | BLADE VALUES
            |--------------------------------------------------------------------------
            */

            $assignment->resolved_subject_id =
                (int)
                $actualSubject->id;


            $assignment->resolved_academic_year_id =
                $status->academic_year_id;


            $assignment->resolved_class_allocation_id =
                optional(
                    $allocation
                )->id;


            $assignment->resolved_exam_master_id =
                $status->exam_master_id;


            $assignment->resolved_standard_id =
                $status->standard_id;


            $assignment->resolved_division_id =
                $status->division_id;


            $assignment->resolved_status =
                strtoupper(
                    trim(
                        (string)
                        (
                            $status->status
                            ?? 'PENDING'
                        )
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | STATUS ID
            |--------------------------------------------------------------------------
            */

            $assignment->resolved_status_id =
                $status->id;


            /*
            |--------------------------------------------------------------------------
            | TMS TEACHER
            |--------------------------------------------------------------------------
            */

            $assignment->resolved_teacher_id =
                $status->teacher_id;


            $assignment->resolved_tms_subject_id =
                $status->subject_id;


            /*
            |--------------------------------------------------------------------------
            | AVOID DUPLICATES
            |--------------------------------------------------------------------------
            |
            | One record per:
            |
            | teacher_subject_allocation_id + exam
            |
            */

            $duplicateKey =
                $status->exam_master_id
                . '-'
                .
                $status->teacher_subject_allocation_id;


            $alreadyExists =
                $assignments->contains(
                    function ($item) use (
                        $duplicateKey
                    ) {

                        return (
                            $item->resolved_exam_master_id
                            . '-'
                            .
                            $item->id
                        ) === $duplicateKey;
                    }
                );


            if (!$alreadyExists) {

                $assignments->push(
                    $assignment
                );
            }
        }


        return $assignments
            ->sortByDesc(
                'id'
            )
            ->values();
    }


    /*
    |--------------------------------------------------------------------------
    | BUILD SELECTED ASSIGNMENT FROM TMS
    |--------------------------------------------------------------------------
    */

    private function buildSelectedAssignment(
        TeacherMarksStatus $status
    ) {

        /*
        |--------------------------------------------------------------------------
        | STANDARD
        |--------------------------------------------------------------------------
        */

        $standard =
            Standard::find(
                $status->standard_id
            );


        /*
        |--------------------------------------------------------------------------
        | DIVISION
        |--------------------------------------------------------------------------
        */

        $division =
            Division::find(
                $status->division_id
            );


        /*
        |--------------------------------------------------------------------------
        | ACADEMIC YEAR
        |--------------------------------------------------------------------------
        */

        $academicYear =
            AcademicYear::find(
                $status->academic_year_id
            );


        /*
        |--------------------------------------------------------------------------
        | TEACHER
        |--------------------------------------------------------------------------
        */

        $teacher =
            User::find(
                $status->teacher_id
            );


        /*
        |--------------------------------------------------------------------------
        | SUBJECT
        |--------------------------------------------------------------------------
        */

        $actualSubject =
            $this->resolveDisplaySubject(
                $status->subject_id,
                $status->standard_id
            );


        if (!$actualSubject) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | REAL CLASS ALLOCATION IF AVAILABLE
        |--------------------------------------------------------------------------
        */

        $allocation =
            TeacherClassAllocation::with([
                'teacher',
                'academicYear',
                'section',
                'standard',
                'division',
            ])
            ->where(
                'academic_year_id',
                $status->academic_year_id
            )
            ->where(
                'standard_id',
                $status->standard_id
            )
            ->where(
                'division_id',
                $status->division_id
            )
            ->when(
                $status->teacher_id,
                function ($query) use ($status) {

                    $query->where(
                        'user_id',
                        $status->teacher_id
                    );
                }
            )
            ->orderByDesc(
                'id'
            )
            ->first();


        /*
        |--------------------------------------------------------------------------
        | SYNTHETIC CLASS ALLOCATION
        |--------------------------------------------------------------------------
        */

        if (!$allocation) {

            $allocation =
                new TeacherClassAllocation();


            $allocation->academic_year_id =
                $status->academic_year_id;


            $allocation->standard_id =
                $status->standard_id;


            $allocation->division_id =
                $status->division_id;


            $allocation->user_id =
                $status->teacher_id;


            $allocation->setRelation(
                'teacher',
                $teacher
            );


            $allocation->setRelation(
                'academicYear',
                $academicYear
            );


            $allocation->setRelation(
                'standard',
                $standard
            );


            $allocation->setRelation(
                'division',
                $division
            );
        }


        /*
        |--------------------------------------------------------------------------
        | BUILD TSA-LIKE MODEL
        |--------------------------------------------------------------------------
        */

        $tsa =
            new TeacherSubjectAllocation();


        $tsa->id =
            $status
                ->teacher_subject_allocation_id;


        $tsa->teacher_class_allocation_id =
            optional(
                $allocation
            )->id;


        $tsa->exam_master_id =
            $status->exam_master_id;


        $tsa->subject_id =
            $actualSubject->id;


        $tsa->resolved_subject_id =
            $actualSubject->id;


        $tsa->resolved_status =
            strtoupper(
                trim(
                    (string)
                    (
                        $status->status
                        ?? 'PENDING'
                    )
                )
            );


        $tsa->resolved_status_id =
            $status->id;


        $tsa->resolved_teacher_id =
            $status->teacher_id;


        $tsa->resolved_academic_year_id =
            $status->academic_year_id;


        $tsa->resolved_standard_id =
            $status->standard_id;


        $tsa->resolved_division_id =
            $status->division_id;


        $tsa->resolved_tms_subject_id =
            $status->subject_id;


        $tsa->setRelation(
            'allocation',
            $allocation
        );


        $tsa->setRelation(
            'subject',
            $actualSubject
        );


        $tsa->setRelation(
            'exam',
            ExamMaster::find(
                $status->exam_master_id
            )
        );


        return $tsa;
    }


    /*
    |--------------------------------------------------------------------------
    | LOAD SELECTED DATA
    |--------------------------------------------------------------------------
    */

    private function loadSelectedData(
        Request $request,
        $exams,
        $assignments
    ) {

        $students =
            collect();


        $existingMarks =
            collect();


        $exam =
            null;


        $teacherSubjectAllocation =
            null;


        $selectedClassAllocation =
            null;


        $subjectConfig =
            null;


        $error =
            '';


        $message =
            '';


        $showTheory =
            false;


        $showOral =
            false;


        $showPractical =
            false;


        $theoryMaxMarks =
            0;


        $theoryPassingMarks =
            0;


        $oralMaxMarks =
            0;


        $oralPassingMarks =
            0;


        $practicalMaxMarks =
            0;


        $practicalPassingMarks =
            0;


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
        | EXAM
        |--------------------------------------------------------------------------
        */

        if ($examId) {

            $exam =
                $exams->firstWhere(
                    'id',
                    (int)
                    $examId
                );


            if (!$exam) {

                $error =
                    'Selected exam was not found.';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | SELECTED ASSIGNMENT
        |--------------------------------------------------------------------------
        */

        if ($tsaId) {

            /*
            |--------------------------------------------------------------------------
            | FIND TMS RECORD
            |--------------------------------------------------------------------------
            */

            $selectedStatus =
                TeacherMarksStatus::query()
                    ->where(
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


            if (!$selectedStatus) {

                $error =
                    'Selected teaching assignment status was not found.';

            } else {

                /*
                |--------------------------------------------------------------------------
                | ACADEMIC YEAR CHECK
                |--------------------------------------------------------------------------
                */

                if (
                    $academicYearId &&
                    (int)
                    $selectedStatus->academic_year_id
                    !==
                    (int)
                    $academicYearId
                ) {

                    $error =
                        'Selected teaching assignment does not belong to the selected academic year.';

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | EXAM
                    |--------------------------------------------------------------------------
                    */

                    if (!$exam) {

                        $exam =
                            $exams->firstWhere(
                                'id',
                                (int)
                                $selectedStatus
                                    ->exam_master_id
                            );
                    }


                    if (!$exam) {

                        $error =
                            'Exam linked to the selected teaching assignment was not found.';

                    } else {

                        /*
                        |--------------------------------------------------------------------------
                        | BUILD SELECTED TSA
                        |--------------------------------------------------------------------------
                        */

                        $teacherSubjectAllocation =
                            $this->buildSelectedAssignment(
                                $selectedStatus
                            );


                        if (
                            !$teacherSubjectAllocation
                        ) {

                            $error =
                                'The selected subject is not mapped in standard_wise_subjects for the selected standard.';

                        } else {

                            $allocation =
                                $teacherSubjectAllocation
                                    ->allocation;


                            $selectedClassAllocation =
                                $allocation;


                            /*
                            |--------------------------------------------------------------------------
                            | ACTUAL SUBJECT
                            |--------------------------------------------------------------------------
                            */

                            $actualSubjectId =
                                $teacherSubjectAllocation
                                    ->resolved_subject_id
                                ?? null;


                            /*
                            |--------------------------------------------------------------------------
                            | EXAM SUBJECT CONFIGURATION
                            |--------------------------------------------------------------------------
                            */

                            if ($actualSubjectId) {

                                /*
                                |--------------------------------------------------------------------------
                                | CURRENT FORMAT
                                |--------------------------------------------------------------------------
                                */

                                $subjectConfig =
                                    ExamMasterSubject::where(
                                        'exam_master_id',
                                        $exam->id
                                    )
                                    ->where(
                                        'standard_id',
                                        $selectedStatus
                                            ->standard_id
                                    )
                                    ->where(
                                        'subject_id',
                                        $actualSubjectId
                                    )
                                    ->first();


                                /*
                                |--------------------------------------------------------------------------
                                | LEGACY FORMAT
                                |--------------------------------------------------------------------------
                                */

                                if (
                                    !$subjectConfig
                                ) {

                                    $mapping =
                                        DB::table(
                                            'standard_wise_subjects'
                                        )
                                        ->where(
                                            'standard_id',
                                            $selectedStatus
                                                ->standard_id
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
                                            ExamMasterSubject::where(
                                                'exam_master_id',
                                                $exam->id
                                            )
                                            ->where(
                                                'standard_id',
                                                $selectedStatus
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
                                        $subjectConfig
                                            ->max_marks
                                        ?? 0;


                                    $theoryPassingMarks =
                                        $subjectConfig
                                            ->passing_marks
                                        ?? 0;

                                } else {

                                    $subjectName =
                                        optional(
                                            $teacherSubjectAllocation
                                                ->subject
                                        )->subject_name
                                        ??
                                        'Selected Subject';


                                    $standard =
                                        Standard::find(
                                            $selectedStatus
                                                ->standard_id
                                        );


                                    $standardName =
                                        optional(
                                            $standard
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
                                    (bool)
                                    $exam->has_theory;


                                $showOral =
                                    (bool)
                                    $exam->has_oral;


                                $showPractical =
                                    (bool)
                                    $exam->has_practical;


                                /*
                                |--------------------------------------------------------------------------
                                | UNIT TEST 1
                                |--------------------------------------------------------------------------
                                */

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


                                /*
                                |--------------------------------------------------------------------------
                                | ORAL
                                |--------------------------------------------------------------------------
                                */

                                if (
                                    $showOral
                                ) {

                                    $oralMaxMarks =
                                        $exam
                                            ->oral_max_marks
                                        ?? 0;


                                    $oralPassingMarks =
                                        $exam
                                            ->oral_passing_marks
                                        ?? 0;
                                }


                                /*
                                |--------------------------------------------------------------------------
                                | PRACTICAL
                                |--------------------------------------------------------------------------
                                */

                                if (
                                    $showPractical
                                ) {

                                    $practicalMaxMarks =
                                        $exam
                                            ->practical_max_marks
                                        ?? 0;


                                    $practicalPassingMarks =
                                        $exam
                                            ->practical_passing_marks
                                        ?? 0;
                                }
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | LOAD ERP STUDENTS
                            |--------------------------------------------------------------------------
                            */

                            try {

                                $students =
                                    StudentHelper::getStudentsDirectERP(
                                        $selectedStatus
                                            ->academic_year_id,
                                        $selectedStatus
                                            ->standard_id,
                                        $selectedStatus
                                            ->division_id
                                    );


                                $students =
                                    collect(
                                        $students
                                    );


                                /*
                                |--------------------------------------------------------------------------
                                | FEMALES FIRST
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
                                                            ]
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
                                                            ]
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

                            } catch (
                                \Throwable $e
                            ) {

                                report(
                                    $e
                                );


                                $students =
                                    collect();


                                $error =
                                    'Old ERP Error: '
                                    .
                                    $e->getMessage();
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | EXISTING MARKS
                            |--------------------------------------------------------------------------
                            |
                            | IMPORTANT:
                            |
                            | We deliberately query by the TMS TSA reference.
                            |
                            | This supports orphaned TSA references such as:
                            |
                            | teacher_subject_allocation_id = 113
                            |
                            | where StudentMark rows still exist.
                            |
                            */

                            $existingMarks =
                                StudentMark::where(
                                    'exam_master_id',
                                    $exam->id
                                )
                                ->where(
                                    'teacher_subject_allocation_id',
                                    $selectedStatus
                                        ->teacher_subject_allocation_id
                                )
                                ->get()
                                ->keyBy(
                                    'student_id'
                                );


                            /*
                            |--------------------------------------------------------------------------
                            | MESSAGE
                            |--------------------------------------------------------------------------
                            */

                            $statusName =
                                strtoupper(
                                    trim(
                                        (string)
                                        (
                                            $selectedStatus
                                                ->status
                                            ?? 'PENDING'
                                        )
                                    )
                                );


                            $message =
                                'Status: '
                                . $statusName
                                . '. Administrator can modify these marks.';


                            /*
                            |--------------------------------------------------------------------------
                            | ADMIN IS NEVER LOCKED
                            |--------------------------------------------------------------------------
                            */

                            $marksLocked =
                                false;
                        }
                    }
                }
            }
        }


        return [
            'students' =>
                $students,

            'existingMarks' =>
                $existingMarks,

            'exam' =>
                $exam,

            'teacherSubjectAllocation' =>
                $teacherSubjectAllocation,

            'selectedClassAllocation' =>
                $selectedClassAllocation,

            'subjectConfig' =>
                $subjectConfig,

            'showTheory' =>
                $showTheory,

            'showOral' =>
                $showOral,

            'showPractical' =>
                $showPractical,

            'theoryMaxMarks' =>
                $theoryMaxMarks,

            'theoryPassingMarks' =>
                $theoryPassingMarks,

            'oralMaxMarks' =>
                $oralMaxMarks,

            'oralPassingMarks' =>
                $oralPassingMarks,

            'practicalMaxMarks' =>
                $practicalMaxMarks,

            'practicalPassingMarks' =>
                $practicalPassingMarks,

            'marksLocked' =>
                false,

            'message' =>
                $message,

            'error' =>
                $error,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    |
    | One Blade only:
    |
    | administrator.marks.edit
    |
    */

    public function index(
        Request $request
    ) {

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
                'id'
            )
            ->get([
                'id',
                'standard_name',
            ]);


        /*
        |--------------------------------------------------------------------------
        | EXAMS
        |--------------------------------------------------------------------------
        */

        $exams =
            ExamMaster::where(
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


        $exams =
            $this->prepareExams(
                $exams,
                $standards
            );


        /*
        |--------------------------------------------------------------------------
        | ASSIGNMENTS
        |--------------------------------------------------------------------------
        */

        $assignments =
            $this->getAssignments(
                $request->input(
                    'academic_year_id'
                ),
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
                $exams,
                $assignments
            );


        /*
        |--------------------------------------------------------------------------
        | RETURN
        |--------------------------------------------------------------------------
        */

        return view(
            'administrator.marks.edit',
            array_merge(
                [
                    'academicYears' =>
                        $academicYears,

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
    |
    | Existing route retained.
    |
    | This method still uses the actual TeacherClassAllocation,
    | but subject resolution is always through standard_wise_subjects.
    |
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


        $allocationId =
            (int)
            $request->allocation_id;


        $examMasterId =
            (int)
            $request->exam_master_id;


        $classAllocation =
            TeacherClassAllocation::findOrFail(
                $allocationId
            );


        $standardId =
            (int)
            $classAllocation->standard_id;


        /*
        |--------------------------------------------------------------------------
        | TEACHER MARK STATUS SOURCE
        |--------------------------------------------------------------------------
        */

        $statuses =
            TeacherMarksStatus::where(
                'exam_master_id',
                $examMasterId
            )
            ->where(
                'standard_id',
                $standardId
            )
            ->where(
                'division_id',
                $classAllocation->division_id
            )
            ->where(
                'academic_year_id',
                $classAllocation->academic_year_id
            )
            ->where(
                'teacher_id',
                $classAllocation->user_id
            )
            ->orderByDesc(
                'id'
            )
            ->get();


        $subjects =
            collect();


        foreach (
            $statuses as $status
        ) {

            $actualSubject =
                $this->resolveDisplaySubject(
                    $status->subject_id,
                    $standardId
                );


            if (!$actualSubject) {
                continue;
            }


            $subjects->push(
                (object) [

                    'teacher_subject_allocation_id' =>
                        (int)
                        $status
                            ->teacher_subject_allocation_id,

                    'subject_id' =>
                        (int)
                        $actualSubject->id,

                    'subject_name' =>
                        $actualSubject
                            ->subject_name,

                    'subject_code' =>
                        $actualSubject
                            ->subject_code
                        ?? '',

                    'short_name' =>
                        $actualSubject
                            ->short_name
                        ?? '',

                ]
            );
        }


        return response()->json(
            $subjects
                ->unique(
                    'teacher_subject_allocation_id'
                )
                ->values()
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE MARKS
    |--------------------------------------------------------------------------
    |
    | Admin may edit:
    |
    | PENDING
    | COMPLETED
    |
    | Existing marks are updated.
    |
    | Pending assignments with no marks can create records.
    |
    | Orphaned completed records such as TSA 113 can be updated
    | because StudentMark rows already exist.
    |
    */

    public function update(
        Request $request
    ) {

        $request->validate([

            'teacher_subject_allocation_id' =>
                'required|integer',

            'exam_master_id' =>
                'required|exists:exam_masters,id',

            'student_ids' =>
                'required|array|min:1',

        ]);


        $tsaId =
            (int)
            $request
                ->teacher_subject_allocation_id;


        $examId =
            (int)
            $request->exam_master_id;


        /*
        |--------------------------------------------------------------------------
        | TMS IS THE SOURCE OF THE SELECTED ASSIGNMENT
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


        if (!$status) {

            return back()
                ->withInput()
                ->withErrors([
                    'teacher_subject_allocation_id' =>
                        'The selected teaching assignment could not be found.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | STANDARD / DIVISION
        |--------------------------------------------------------------------------
        */

        $standardId =
            (int)
            $status->standard_id;


        $divisionId =
            (int)
            $status->division_id;


        $academicYearId =
            (int)
            $status->academic_year_id;


        /*
        |--------------------------------------------------------------------------
        | AUTHORITATIVE SUBJECT
        |--------------------------------------------------------------------------
        */

        $actualSubjectId =
            $this->resolveActualSubjectId(
                $status->subject_id,
                $standardId
            );


        if (!$actualSubjectId) {

            return back()
                ->withInput()
                ->withErrors([
                    'subject_id' =>
                        'The selected subject is not mapped in standard_wise_subjects for this standard.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | EXAM CONFIGURATION
        |--------------------------------------------------------------------------
        */

        $subjectConfig =
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
                $actualSubjectId
            )
            ->first();


        /*
        |--------------------------------------------------------------------------
        | LEGACY EXAM CONFIGURATION
        |--------------------------------------------------------------------------
        */

        if (!$subjectConfig) {

            $mapping =
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
                ->first();


            if ($mapping) {

                $subjectConfig =
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
                        $mapping->id
                    )
                    ->first();
            }
        }


        if (!$subjectConfig) {

            return back()
                ->withInput()
                ->withErrors([
                    'subject_id' =>
                        'Marks configuration was not found for the selected subject and exam.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | MARK LIMITS
        |--------------------------------------------------------------------------
        */

        $theoryMax =
            (float)
            (
                $subjectConfig->max_marks
                ?? 0
            );


        $exam =
            ExamMaster::find(
                $examId
            );


        $showOral =
            $exam
                ? (bool)
                    $exam->has_oral
                : false;


        $showPractical =
            $exam
                ? (bool)
                    $exam->has_practical
                : false;


        $examName =
            strtoupper(
                trim(
                    (string)
                    (
                        $exam->exam_name
                        ?? ''
                    )
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


        $oralMax =
            $showOral
                ? (float)
                    (
                        $exam->oral_max_marks
                        ?? 0
                    )
                : 0;


        $practicalMax =
            $showPractical
                ? (float)
                    (
                        $exam->practical_max_marks
                        ?? 0
                    )
                : 0;


        /*
        |--------------------------------------------------------------------------
        | CHECK WHETHER TSA RECORD ACTUALLY EXISTS
        |--------------------------------------------------------------------------
        |
        | Important for orphaned TSA references.
        |
        */

        $tsaExists =
            TeacherSubjectAllocation::where(
                'id',
                $tsaId
            )
            ->where(
                'exam_master_id',
                $examId
            )
            ->exists();


        /*
        |--------------------------------------------------------------------------
        | TRANSACTION
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $request,
                $tsaId,
                $examId,
                $standardId,
                $divisionId,
                $academicYearId,
                $actualSubjectId,
                $theoryMax,
                $oralMax,
                $practicalMax,
                $showOral,
                $showPractical,
                $tsaExists
            ) {

                foreach (
                    $request->student_ids as $studentId
                ) {

                    $studentId =
                        (string)
                        $studentId;


                    /*
                    |--------------------------------------------------------------------------
                    | EXISTING MARK
                    |--------------------------------------------------------------------------
                    */

                    $mark =
                        StudentMark::where(
                            'exam_master_id',
                            $examId
                        )
                        ->where(
                            'teacher_subject_allocation_id',
                            $tsaId
                        )
                        ->where(
                            'student_id',
                            $studentId
                        )
                        ->first();


                    /*
                    |--------------------------------------------------------------------------
                    | OLD VALUES
                    |--------------------------------------------------------------------------
                    */

                    $oldTheory =
                        $mark
                            ? $mark
                                ->theory_obtained_marks
                            : null;


                    $oldOral =
                        $mark
                            ? $mark
                                ->oral_obtained_marks
                            : null;


                    $oldPractical =
                        $mark
                            ? $mark
                                ->practical_obtained_marks
                            : null;


                    /*
                    |--------------------------------------------------------------------------
                    | ABSENT
                    |--------------------------------------------------------------------------
                    */

                    $isAbsent =
                        (
                            (int)
                            (
                                $request
                                    ->is_absent[$studentId]
                                    ?? 0
                            )
                        ) === 1
                            ? 1
                            : 0;


                    /*
                    |--------------------------------------------------------------------------
                    | MARK VALUES
                    |--------------------------------------------------------------------------
                    */

                    $theory =
                        $request
                            ->theory_marks[$studentId]
                            ?? null;


                    $oral =
                        $request
                            ->oral_marks[$studentId]
                            ?? null;


                    $practical =
                        $request
                            ->practical_marks[$studentId]
                            ?? null;


                    /*
                    |--------------------------------------------------------------------------
                    | ABSENT = ZERO
                    |--------------------------------------------------------------------------
                    */

                    if ($isAbsent) {

                        $theory =
                            0;

                        $oral =
                            0;

                        $practical =
                            0;

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
                    | NUMERIC VALIDATION
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $theory !== null &&
                        $theory !== ''
                    ) {

                        $theory =
                            (float)
                            $theory;


                        if (
                            $theory < 0 ||
                            $theory > $theoryMax
                        ) {

                            throw new \RuntimeException(
                                'Invalid theory marks for student ID '
                                . $studentId
                            );
                        }
                    }


                    if (
                        $showOral &&
                        $oral !== null &&
                        $oral !== ''
                    ) {

                        $oral =
                            (float)
                            $oral;


                        if (
                            $oral < 0 ||
                            $oral > $oralMax
                        ) {

                            throw new \RuntimeException(
                                'Invalid oral marks for student ID '
                                . $studentId
                            );
                        }
                    }


                    if (
                        $showPractical &&
                        $practical !== null &&
                        $practical !== ''
                    ) {

                        $practical =
                            (float)
                            $practical;


                        if (
                            $practical < 0 ||
                            $practical > $practicalMax
                        ) {

                            throw new \RuntimeException(
                                'Invalid practical marks for student ID '
                                . $studentId
                            );
                        }
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | UPDATE EXISTING RECORD
                    |--------------------------------------------------------------------------
                    */

                    if ($mark) {

                        $mark->update([

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


                    } else {

                        /*
                        |--------------------------------------------------------------------------
                        | CREATE NEW RECORD
                        |--------------------------------------------------------------------------
                        |
                        | This is allowed only when the underlying TSA exists.
                        |
                        | For orphaned TSA references, existing StudentMark rows
                        | can be edited, but new rows are not created because the
                        | original TSA row is missing.
                        |
                        */

                        if (!$tsaExists) {

                            throw new \RuntimeException(
                                'Cannot create a new mark record because the teaching allocation '
                                . $tsaId
                                . ' no longer exists. Existing marks can still be corrected.'
                            );
                        }


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
                            $mark
                                ->theory_obtained_marks,

                        'old_oral_marks' =>
                            $oldOral,

                        'new_oral_marks' =>
                            $mark
                                ->oral_obtained_marks,

                        'old_practical_marks' =>
                            $oldPractical,

                        'new_practical_marks' =>
                            $mark
                                ->practical_obtained_marks,

                        'remarks' =>
                            $mark->wasRecentlyCreated
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


        /*
        |--------------------------------------------------------------------------
        | DO NOT CHANGE STATUS
        |--------------------------------------------------------------------------
        */

        TeacherMarksStatus::where(
            'id',
            $status->id
        )
        ->update([
            'updated_at' =>
                now(),
        ]);


        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        */

        return redirect()->route(
            'result-generation.admin-marks.edit',
            [
                'academic_year_id' =>
                    $academicYearId,

                'exam_master_id' =>
                    $examId,

                'teacher_subject_allocation_id' =>
                    $tsaId,

                'marks_updated' =>
                    1,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | REOPEN
    |--------------------------------------------------------------------------
    |
    | Existing functionality retained.
    |
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
        ]);


        /*
        |--------------------------------------------------------------------------
        | AUTHORITATIVE SUBJECT
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
        | MARKS
        |--------------------------------------------------------------------------
        */

        $marks =
            StudentMark::where(
                'exam_master_id',
                $request->exam_master_id
            )
            ->where(
                'subject_id',
                $actualSubjectId
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
                'exam_master_id' =>
                    $request->exam_master_id,

                'teacher_subject_allocation_id' =>
                    $request->input(
                        'teacher_subject_allocation_id'
                    ),

                'marks_reopened' =>
                    1,
            ]
        );
    }
}