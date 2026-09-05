<?php

namespace App\Http\Controllers\Marks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\StudentMark;
use App\Models\TeacherMarksStatus;
use App\Models\TeacherSubjectAllocation;
use App\Models\ExamMaster;
use App\Models\ExamMasterSubject;

use App\Helpers\ExamHelper;
use App\Helpers\MarksHelper;
use App\Helpers\StudentHelper;

class MarkSubmitController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | FINAL SUBMIT
    |--------------------------------------------------------------------------
    */

    public function submitFinal(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'academic_year_id' =>
                'required|integer',

            'exam_master_id' =>
                'required|exists:exam_masters,id',

            'teacher_subject_allocation_id' =>
                'required|exists:teacher_subject_allocations,id',
        ], [
            'academic_year_id.required' =>
                'Academic Year is required.',

            'exam_master_id.required' =>
                'Exam is required.',

            'teacher_subject_allocation_id.required' =>
                'Teaching Assignment is required.',
        ]);


        /*
        |--------------------------------------------------------------------------
        | IDS
        |--------------------------------------------------------------------------
        */

        $academicYearId =
            (int) $request->academic_year_id;

        $examId =
            (int) $request->exam_master_id;

        $tsaId =
            (int) $request->teacher_subject_allocation_id;


        /*
        |--------------------------------------------------------------------------
        | LOAD TEACHER SUBJECT ALLOCATION
        |--------------------------------------------------------------------------
        */

        $tsa =
            TeacherSubjectAllocation::with([
                'allocation',
                'allocation.standard',
                'allocation.division',
                'allocation.teacher',
                'allocation.academicYear',
                'allocation.section',
                'subject',
                'exam',
            ])
            ->where(
                'id',
                $tsaId
            )
            ->where(
                'exam_master_id',
                $examId
            )
            ->first();


        if (!$tsa) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Teaching Assignment does not belong to the selected Exam.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | CLASS ALLOCATION
        |--------------------------------------------------------------------------
        */

        $allocation =
            $tsa->allocation;


        if (!$allocation) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Teacher Class Allocation not found.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | ACADEMIC YEAR CHECK
        |--------------------------------------------------------------------------
        */

        if (
            (int) $allocation->academic_year_id !==
            $academicYearId
        ) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Academic Year does not match the Teaching Assignment.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | AUTHENTICATION
        |--------------------------------------------------------------------------
        */

        if (!Auth::check()) {

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Please login before submitting marks.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | TEACHER AUTHORIZATION
        |--------------------------------------------------------------------------
        */

        if (
            Auth::user()->role !== 'Administrator' &&
            (int) $allocation->user_id !==
            (int) Auth::id()
        ) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'You are not authorized to submit marks for this assignment.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD EXAM
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
                ->with(
                    'error',
                    'Exam not found.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | VERIFY TSA EXAM
        |--------------------------------------------------------------------------
        */

        if (
            (int) $tsa->exam_master_id !==
            $examId
        ) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Selected Teaching Assignment does not belong to this Exam.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | CANONICAL SUBJECT ID
        |--------------------------------------------------------------------------
        */

        $actualSubjectId =
            (int) $tsa->subject_id;


        if (
            $actualSubjectId <= 0
        ) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'The Teaching Assignment does not contain a valid Subject ID.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | VERIFY SUBJECT
        |--------------------------------------------------------------------------
        */

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


        if (!$subject) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Subject ID '
                    . $actualSubjectId
                    . ' was not found in Subject Master.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | VERIFY SUBJECT MAPPING
        |--------------------------------------------------------------------------
        */

        $subjectMapped =
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
            ->exists();


        if (!$subjectMapped) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Subject '
                    . $actualSubjectId
                    . ' ('
                    . $subject->subject_name
                    . ') is not mapped to the selected Standard.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | OPTIONAL
        |--------------------------------------------------------------------------
        */

        $optionalEnabled =
            MarksHelper::isOptionalEnabled(
                $allocation->standard_id
            );


        /*
        |--------------------------------------------------------------------------
        | CHECK ALREADY COMPLETED
        |--------------------------------------------------------------------------
        */

        $alreadyCompleted =
            TeacherMarksStatus::where(
                'exam_master_id',
                $examId
            )
            ->where(
                'teacher_subject_allocation_id',
                $tsaId
            )
            ->where(
                'status',
                'COMPLETED'
            )
            ->exists();


        if ($alreadyCompleted) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Marks have already been finally submitted.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD ERP STUDENTS
        |--------------------------------------------------------------------------
        */

        try {

            $students =
                StudentHelper::getStudentsDirectERP(
                    $allocation->academic_year_id,
                    $allocation->standard_id,
                    $allocation->division_id
                );

        } catch (\Throwable $e) {

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to load students from Old ERP. '
                    . $e->getMessage()
                );
        }


        /*
        |--------------------------------------------------------------------------
        | CHECK STUDENTS
        |--------------------------------------------------------------------------
        */

        if (
            !$students ||
            $students->isEmpty()
        ) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'No students found for this Standard and Division.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | ERP STUDENT IDS
        |--------------------------------------------------------------------------
        */

        $erpStudentIds =
            MarksHelper::getStudentIds(
                $students
            );


        if (
            $erpStudentIds->isEmpty()
        ) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to determine student IDs from Old ERP.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD SAVED MARKS
        |--------------------------------------------------------------------------
        */

        $marks =
            StudentMark::where(
                'academic_year_id',
                $allocation->academic_year_id
            )
            ->where(
                'section_id',
                $allocation->section_id
            )
            ->where(
                'standard_id',
                $allocation->standard_id
            )
            ->where(
                'division_id',
                $allocation->division_id
            )
            ->where(
                'exam_master_id',
                $examId
            )
            ->where(
                'teacher_subject_allocation_id',
                $tsaId
            )
            ->where(
                'subject_id',
                $actualSubjectId
            )
            ->get();


        if (
            $marks->isEmpty()
        ) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Please save marks before final submission.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | SAVED STUDENT IDS
        |--------------------------------------------------------------------------
        */

        $savedStudentIds =
            MarksHelper::getSavedStudentIds(
                $marks
            );


        /*
        |--------------------------------------------------------------------------
        | MISSING STUDENTS
        |--------------------------------------------------------------------------
        */

        $missingStudents =
            $erpStudentIds
                ->diff(
                    $savedStudentIds
                )
                ->values();


        if (
            $missingStudents->isNotEmpty()
        ) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Marks are not saved for all students. '
                    . $missingStudents->count()
                    . ' student(s) are still pending.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | EXAM SUBJECT CONFIGURATION
        |--------------------------------------------------------------------------
        */

        $subjectConfig =
            ExamMasterSubject::where(
                'exam_master_id',
                $examId
            )
            ->where(
                'standard_id',
                $allocation->standard_id
            )
            ->where(
                'subject_id',
                $actualSubjectId
            )
            ->first();


        if (!$subjectConfig) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Marks configuration was not found for '
                    . $subject->subject_name
                    . ' in '
                    . $exam->exam_name
                    . '.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | COMPONENT CONFIGURATION
        |--------------------------------------------------------------------------
        */

        $components =
            MarksHelper::getComponentMaxMarks(
                $exam,
                $subjectConfig
            );


        $showTheory =
            $components['show_theory'];

        $showOral =
            $components['show_oral'];

        $showPractical =
            $components['show_practical'];

        $theoryMax =
            $components['theory_max'];

        $oralMax =
            $components['oral_max'];

        $practicalMax =
            $components['practical_max'];


        /*
        |--------------------------------------------------------------------------
        | VALIDATE SAVED MARKS
        |--------------------------------------------------------------------------
        */

        foreach (
            $marks as $mark
        ) {

            /*
            |--------------------------------------------------------------------------
            | OPTIONAL STUDENT
            |--------------------------------------------------------------------------
            |
            | Optional students do not require marks.
            |
            | IMPORTANT:
            | is_optional is NEVER changed here.
            |
            */

            if (
                $optionalEnabled &&
                MarksHelper::isOptionalStudent(
                    $mark
                )
            ) {

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | ABSENT STUDENT
            |--------------------------------------------------------------------------
            */

            if (
                MarksHelper::isAbsent(
                    $mark
                )
            ) {

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | THEORY
            |--------------------------------------------------------------------------
            */

            if ($showTheory) {

                $error =
                    MarksHelper::validateObtainedMarks(
                        $mark->theory_obtained_marks,
                        $theoryMax,
                        'Theory',
                        $mark->student_id
                    );


                if ($error !== null) {

                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            $error
                        );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | ORAL
            |--------------------------------------------------------------------------
            */

            if ($showOral) {

                $error =
                    MarksHelper::validateObtainedMarks(
                        $mark->oral_obtained_marks,
                        $oralMax,
                        'Oral',
                        $mark->student_id
                    );


                if ($error !== null) {

                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            $error
                        );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | PRACTICAL
            |--------------------------------------------------------------------------
            */

            if ($showPractical) {

                $error =
                    MarksHelper::validateObtainedMarks(
                        $mark->practical_obtained_marks,
                        $practicalMax,
                        'Practical',
                        $mark->student_id
                    );


                if ($error !== null) {

                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            $error
                        );
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | FINAL TRANSACTION
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $allocation,
                $tsa,
                $exam,
                $actualSubjectId,
                $academicYearId
            ) {

                /*
                |--------------------------------------------------------------------------
                | LOCK MARKS ONLY
                |--------------------------------------------------------------------------
                |
                | DO NOT modify:
                |
                | is_optional
                | is_absent
                | theory_obtained_marks
                | oral_obtained_marks
                | practical_obtained_marks
                |
                */

                StudentMark::where(
                    'academic_year_id',
                    $allocation->academic_year_id
                )
                ->where(
                    'section_id',
                    $allocation->section_id
                )
                ->where(
                    'standard_id',
                    $allocation->standard_id
                )
                ->where(
                    'division_id',
                    $allocation->division_id
                )
                ->where(
                    'exam_master_id',
                    $exam->id
                )
                ->where(
                    'teacher_subject_allocation_id',
                    $tsa->id
                )
                ->where(
                    'subject_id',
                    $actualSubjectId
                )
                ->update([
                    'is_locked' =>
                        1,

                    'updated_by' =>
                        Auth::id(),

                    'updated_at' =>
                        now(),
                ]);


                /*
                |--------------------------------------------------------------------------
                | TEACHER MARK STATUS
                |--------------------------------------------------------------------------
                */

                TeacherMarksStatus::updateOrCreate(
                    [
                        'exam_master_id' =>
                            $exam->id,

                        'teacher_subject_allocation_id' =>
                            $tsa->id,
                    ],
                    [
                        'academic_year_id' =>
                            $academicYearId,

                        'standard_id' =>
                            $allocation->standard_id,

                        'division_id' =>
                            $allocation->division_id,

                        'subject_id' =>
                            $actualSubjectId,

                        'teacher_id' =>
                            $allocation->user_id,

                        'status' =>
                            'COMPLETED',

                        'updated_at' =>
                            now(),
                    ]
                );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | UPDATE EXAM COMPLETION
        |--------------------------------------------------------------------------
        */

        try {

            ExamHelper::updateCompletionStatus(
                $allocation->academic_year_id,
                $exam->id,
                $allocation->standard_id,
                $allocation->division_id
            );

        } catch (\Throwable $e) {

            report($e);
        }


        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'marks-entry.index',
                [
                    'academic_year_id' =>
                        $academicYearId,

                    'exam_master_id' =>
                        $exam->id,

                    'teacher_subject_allocation_id' =>
                        $tsa->id,
                ]
            )
            ->with(
                'success',
                'Final marks submitted successfully. Marks are now locked for editing.'
            );
    }
}