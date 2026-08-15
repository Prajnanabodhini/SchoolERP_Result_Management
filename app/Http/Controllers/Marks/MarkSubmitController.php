<?php

namespace App\Http\Controllers\Marks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\StudentMark;
use App\Models\TeacherMarksStatus;
use App\Models\TeacherSubjectAllocation;
use App\Models\TeacherClassAllocation;
use App\Models\ExamMaster;
use App\Helpers\ExamHelper;
use App\Helpers\StudentHelper;

class MarkSubmitController extends Controller
{
    /**
     * ============================================================
     * FINAL SUBMIT
     * ============================================================
     */
    public function submitFinal(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'exam_master_id' =>
                'required|exists:exam_masters,id',

            'teacher_subject_allocation_id' =>
                'required|exists:teacher_subject_allocations,id',
        ]);


        /*
        |--------------------------------------------------------------------------
        | LOAD TEACHER SUBJECT ALLOCATION
        |--------------------------------------------------------------------------
        */

        $tsa = TeacherSubjectAllocation::with([
            'allocation',
            'subject',
            'exam',
        ])->find(
            $request->teacher_subject_allocation_id
        );


        if (!$tsa) {

            return back()->with(
                'error',
                'Teaching Assignment not found.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | SECURITY
        |--------------------------------------------------------------------------
        | Teacher can submit only his/her own allocation.
        |--------------------------------------------------------------------------
        */

        $allocation = $tsa->allocation;


        if (!$allocation) {

            return back()->with(
                'error',
                'Teacher Class Allocation not found.'
            );
        }


        if (
            Auth::user()->role !== 'Administrator' &&
            (int) $allocation->user_id !== (int) Auth::id()
        ) {

            return back()->with(
                'error',
                'You are not authorized to submit marks for this assignment.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | EXAM
        |--------------------------------------------------------------------------
        */

        $exam = ExamMaster::find(
            $request->exam_master_id
        );


        if (!$exam) {

            return back()->with(
                'error',
                'Exam not found.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | MAKE SURE TSA BELONGS TO SELECTED EXAM
        |--------------------------------------------------------------------------
        */

        if (
            (int) $tsa->exam_master_id !==
            (int) $exam->id
        ) {

            return back()->with(
                'error',
                'Selected teaching assignment does not belong to this exam.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | ALREADY COMPLETED?
        |--------------------------------------------------------------------------
        */

        $alreadyCompleted =
            TeacherMarksStatus::where(
                'exam_master_id',
                $exam->id
            )
            ->where(
                'teacher_subject_allocation_id',
                $tsa->id
            )
            ->where(
                'status',
                'COMPLETED'
            )
            ->exists();


        if ($alreadyCompleted) {

            return back()->with(
                'error',
                'Marks have already been finally submitted.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD STUDENTS
        |--------------------------------------------------------------------------
        */

        try {

            $students =
                StudentHelper::getStudentsDirectERP(
                    $allocation->academic_year_id,
                    $allocation->standard_id,
                    $allocation->division_id
                );

            $totalStudents =
                $students->count();

        } catch (\Throwable $e) {

            report($e);

            return back()->with(
                'error',
                'Unable to load students from Old ERP. '
                . $e->getMessage()
            );
        }


        if ($totalStudents <= 0) {

            return back()->with(
                'error',
                'No students found for this Standard and Division.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD SAVED MARKS
        |--------------------------------------------------------------------------
        */

        $marks = StudentMark::where(
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
        ->get();


        /*
        |--------------------------------------------------------------------------
        | NO MARKS
        |--------------------------------------------------------------------------
        */

        if ($marks->count() === 0) {

            return back()->with(
                'error',
                'Please save marks before final submission.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | CHECK ALL STUDENTS HAVE MARK RECORDS
        |--------------------------------------------------------------------------
        |
        | We compare student IDs from ERP against saved marks.
        |
        */

        $savedStudentIds =
            $marks
                ->pluck('student_id')
                ->map(fn ($id) => (string) $id)
                ->values()
                ->toArray();


        $missingStudents = [];


        foreach ($students as $student) {

            $studentId =
                (string) ($student->id ?? $student->student_id ?? '');


            if (
                $studentId !== '' &&
                !in_array(
                    $studentId,
                    $savedStudentIds,
                    true
                )
            ) {

                $missingStudents[] =
                    $studentId;
            }
        }


        if (count($missingStudents) > 0) {

            return back()->with(
                'error',
                'Marks are not saved for all students. '
                . count($missingStudents)
                . ' student(s) are still pending.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDATE EACH MARK
        |--------------------------------------------------------------------------
        */

        foreach ($marks as $mark) {

            /*
            |--------------------------------------------------------------------------
            | ABSENT STUDENT
            |--------------------------------------------------------------------------
            */

            if ((int) $mark->is_absent === 1) {

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | THEORY
            |--------------------------------------------------------------------------
            */

            if ((int) $exam->has_theory === 1) {

                if (
                    $mark->theory_obtained_marks === null ||
                    $mark->theory_obtained_marks === ''
                ) {

                    return back()->with(
                        'error',
                        'Theory marks are missing for one or more students.'
                    );
                }


                if (
                    $mark->theory_max_marks > 0 &&
                    (float) $mark->theory_obtained_marks >
                    (float) $mark->theory_max_marks
                ) {

                    return back()->with(
                        'error',
                        'Theory marks exceed maximum marks for one or more students.'
                    );
                }


                if (
                    (float) $mark->theory_obtained_marks < 0
                ) {

                    return back()->with(
                        'error',
                        'Theory marks cannot be negative.'
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | ORAL
            |--------------------------------------------------------------------------
            */

            if ((int) $exam->has_oral === 1) {

                if (
                    $mark->oral_obtained_marks === null ||
                    $mark->oral_obtained_marks === ''
                ) {

                    return back()->with(
                        'error',
                        'Oral marks are missing for one or more students.'
                    );
                }


                if (
                    $exam->oral_max_marks > 0 &&
                    (float) $mark->oral_obtained_marks >
                    (float) $exam->oral_max_marks
                ) {

                    return back()->with(
                        'error',
                        'Oral marks exceed maximum marks for one or more students.'
                    );
                }


                if (
                    (float) $mark->oral_obtained_marks < 0
                ) {

                    return back()->with(
                        'error',
                        'Oral marks cannot be negative.'
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | PRACTICAL
            |--------------------------------------------------------------------------
            */

            if ((int) $exam->has_practical === 1) {

                if (
                    $mark->practical_obtained_marks === null ||
                    $mark->practical_obtained_marks === ''
                ) {

                    return back()->with(
                        'error',
                        'Practical marks are missing for one or more students.'
                    );
                }


                if (
                    $exam->practical_max_marks > 0 &&
                    (float) $mark->practical_obtained_marks >
                    (float) $exam->practical_max_marks
                ) {

                    return back()->with(
                        'error',
                        'Practical marks exceed maximum marks for one or more students.'
                    );
                }


                if (
                    (float) $mark->practical_obtained_marks < 0
                ) {

                    return back()->with(
                        'error',
                        'Practical marks cannot be negative.'
                    );
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | FINAL TRANSACTION
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $marks,
            $tsa,
            $allocation,
            $exam
        ) {

            /*
            |--------------------------------------------------------------------------
            | LOCK STUDENT MARKS
            |--------------------------------------------------------------------------
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
            ->update([
                'is_locked' =>
                    1,

                'updated_by' =>
                    Auth::id(),
            ]);


            /*
            |--------------------------------------------------------------------------
            | MARKS STATUS = COMPLETED
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
                        $allocation->academic_year_id,

                    'standard_id' =>
                        $allocation->standard_id,

                    'division_id' =>
                        $allocation->division_id,

                    /*
                    | IMPORTANT:
                    | subject_id must be actual subjects.id
                    */

                    'subject_id' =>
                        $tsa->subject_id,

                    'teacher_id' =>
                        $allocation->user_id,

                    'status' =>
                        'COMPLETED',
                ]
            );
        });


        /*
        |--------------------------------------------------------------------------
        | UPDATE EXAM COMPLETION
        |--------------------------------------------------------------------------
        */

        ExamHelper::updateCompletionStatus(

            $allocation->academic_year_id,

            $exam->id,

            $allocation->standard_id,

            $allocation->division_id
        );


        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'marks-entry.index',
                [
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