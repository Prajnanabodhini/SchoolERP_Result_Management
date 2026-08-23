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
        | TEACHER AUTHORIZATION
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
        |
        | IMPORTANT:
        |
        | teacher_subject_allocations.subject_id
        | is expected to contain subjects.id.
        |
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
        | VERIFY SUBJECT EXISTS
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
        | VERIFY SUBJECT BELONGS TO STANDARD
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
        | ALREADY COMPLETED
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
        | NORMALIZE ERP STUDENTS
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
        | BUILD ERP STUDENT IDS
        |--------------------------------------------------------------------------
        |
        | Marks Entry uses Studentid.
        | Support Studentid, student_id and id.
        |
        */

        $erpStudentIds =
            collect($students)
                ->map(
                    function ($student) {

                        if (
                            isset(
                                $student->Studentid
                            ) &&
                            $student->Studentid !== ''
                        ) {

                            return (string)
                                $student->Studentid;
                        }


                        if (
                            isset(
                                $student->student_id
                            ) &&
                            $student->student_id !== ''
                        ) {

                            return (string)
                                $student->student_id;
                        }


                        if (
                            isset(
                                $student->id
                            ) &&
                            $student->id !== ''
                        ) {

                            return (string)
                                $student->id;
                        }


                        return null;
                    }
                )
                ->filter()
                ->unique()
                ->values();


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
        |
        | Filter by exact:
        |
        | Academic Year
        | Section
        | Standard
        | Division
        | Exam
        | TSA
        | Subject
        |
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
            $marks
                ->pluck(
                    'student_id'
                )
                ->map(
                    fn ($id) =>
                        (string) $id
                )
                ->filter()
                ->unique()
                ->values();


        /*
        |--------------------------------------------------------------------------
        | FIND MISSING STUDENTS
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
        | COMPONENT VISIBILITY
        |--------------------------------------------------------------------------
        */

        $showTheory =
            (bool) (
                $exam->has_theory ?? true
            );


        $showOral =
            (bool) (
                $exam->has_oral ?? false
            );


        $showPractical =
            (bool) (
                $exam->has_practical ?? false
            );


        /*
        |--------------------------------------------------------------------------
        | UNIT TEST 1
        |--------------------------------------------------------------------------
        |
        | Requirement:
        | Oral and Practical are not applicable.
        |
        */

        $examName =
            strtoupper(
                trim(
                    (string) (
                        $exam->exam_name ?? ''
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


        /*
        |--------------------------------------------------------------------------
        | MAX MARKS
        |--------------------------------------------------------------------------
        */

        $theoryMax =
            (float) (
                $subjectConfig->max_marks
                ?? 0
            );


        $oralMax =
            $showOral
                ? (float) (
                    $exam->oral_max_marks
                    ?? 0
                )
                : 0;


        $practicalMax =
            $showPractical
                ? (float) (
                    $exam->practical_max_marks
                    ?? 0
                )
                : 0;


        /*
        |--------------------------------------------------------------------------
        | VALIDATE ALL SAVED MARKS
        |--------------------------------------------------------------------------
        */

        foreach (
            $marks as $mark
        ) {

            /*
            |--------------------------------------------------------------------------
            | ABSENT
            |--------------------------------------------------------------------------
            */

            if (
                (int) $mark->is_absent === 1
            ) {

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | THEORY
            |--------------------------------------------------------------------------
            */

            if ($showTheory) {

                if (
                    $mark->theory_obtained_marks === null
                    ||
                    $mark->theory_obtained_marks === ''
                ) {

                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            'Theory marks are missing for one or more students.'
                        );
                }


                $theoryObtained =
                    (float)
                    $mark->theory_obtained_marks;


                if (
                    $theoryObtained < 0
                    ||
                    (
                        $theoryMax > 0 &&
                        $theoryObtained > $theoryMax
                    )
                ) {

                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            'Invalid Theory marks found for Student ID '
                            . $mark->student_id
                            . '.'
                        );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | ORAL
            |--------------------------------------------------------------------------
            */

            if ($showOral) {

                if (
                    $mark->oral_obtained_marks === null
                    ||
                    $mark->oral_obtained_marks === ''
                ) {

                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            'Oral marks are missing for one or more students.'
                        );
                }


                $oralObtained =
                    (float)
                    $mark->oral_obtained_marks;


                if (
                    $oralObtained < 0
                    ||
                    (
                        $oralMax > 0 &&
                        $oralObtained > $oralMax
                    )
                ) {

                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            'Invalid Oral marks found for Student ID '
                            . $mark->student_id
                            . '.'
                        );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | PRACTICAL
            |--------------------------------------------------------------------------
            */

            if ($showPractical) {

                if (
                    $mark->practical_obtained_marks === null
                    ||
                    $mark->practical_obtained_marks === ''
                ) {

                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            'Practical marks are missing for one or more students.'
                        );
                }


                $practicalObtained =
                    (float)
                    $mark->practical_obtained_marks;


                if (
                    $practicalObtained < 0
                    ||
                    (
                        $practicalMax > 0 &&
                        $practicalObtained > $practicalMax
                    )
                ) {

                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            'Invalid Practical marks found for Student ID '
                            . $mark->student_id
                            . '.'
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
                $marks,
                $tsa,
                $allocation,
                $exam,
                $actualSubjectId,
                $academicYearId
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
                ->where(
                    'subject_id',
                    $actualSubjectId
                )
                ->update([
                    'subject_id' =>
                        $actualSubjectId,

                    'is_locked' =>
                        1,

                    'updated_by' =>
                        Auth::id(),

                    'updated_at' =>
                        now(),
                ]);


                /*
                |--------------------------------------------------------------------------
                | UPDATE TEACHER MARK STATUS
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

                        /*
                        |--------------------------------------------------------------
                        | IMPORTANT:
                        | Always store subjects.id.
                        |--------------------------------------------------------------
                        */

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

            /*
            |--------------------------------------------------------------------------
            | Do not make a successful marks submission appear
            | unsuccessful because an optional completion helper failed.
            |--------------------------------------------------------------------------
            */

            report($e);
        }


        /*
        |--------------------------------------------------------------------------
        | SUCCESS REDIRECT
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