<?php

namespace App\Http\Controllers\Marks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Subject;
use App\Models\ExamMaster;
use App\Models\TeacherMarksStatus;
use App\Models\TeacherSubjectAllocation;
use App\Models\StudentMark;
use App\Models\ExamMasterSubject;

class MarkSaveController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | RESOLVE ACTUAL SUBJECT ID
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | teacher_subject_allocations.subject_id
    | stores the actual subjects.id.
    |
    | Therefore we first verify that the ID exists in subjects.
    |
    */

    private function resolveActualSubjectId($allocationSubjectId)
    {
        if (!$allocationSubjectId) {
            return null;
        }

        $subject = Subject::where(
            'id',
            $allocationSubjectId
        )
        ->where(
            'is_active',
            1
        )
        ->first();

        if ($subject) {
            return (int) $subject->id;
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | VERIFY TEACHER ASSIGNMENT
    |--------------------------------------------------------------------------
    |
    | Administrator:
    |     Can access any assignment.
    |
    | Teacher:
    |     Can access only his/her own assignment.
    |
    */

    private function verifyTeacherAssignment(
        TeacherSubjectAllocation $teacherSubjectAllocation,
        $examMasterId
    ) {
        if (!Auth::check()) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Administrator
        |--------------------------------------------------------------------------
        */

        if (
            Auth::user()->role === 'Administrator'
        ) {
            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | Teacher
        |--------------------------------------------------------------------------
        */

        return TeacherMarksStatus::where(
            'teacher_subject_allocation_id',
            $teacherSubjectAllocation->id
        )
        ->where(
            'exam_master_id',
            $examMasterId
        )
        ->where(
            'teacher_id',
            Auth::id()
        )
        ->exists();
    }


    /*
    |--------------------------------------------------------------------------
    | SAVE MARKS
    |--------------------------------------------------------------------------
    */

    public function save(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | BASIC VALIDATION
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'exam_master_id' =>
                'required|exists:exam_masters,id',

            'teacher_subject_allocation_id' =>
                'required|exists:teacher_subject_allocations,id',

            'student_ids' =>
                'required|array|min:1',
        ], [

            'exam_master_id.required' =>
                'Please select Exam.',

            'teacher_subject_allocation_id.required' =>
                'Please select Teaching Assignment.',

            'student_ids.required' =>
                'No students found.',
        ]);


        /*
        |--------------------------------------------------------------------------
        | LOAD TEACHER SUBJECT ALLOCATION
        |--------------------------------------------------------------------------
        */

        $teacherSubjectAllocation =
            TeacherSubjectAllocation::with([
                'allocation',
                'allocation.standard',
                'allocation.division',
                'allocation.teacher',
                'allocation.academicYear',
                'allocation.section',
                'subject',
                'exam'
            ])
            ->find(
                $request->teacher_subject_allocation_id
            );


        if (!$teacherSubjectAllocation) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Teaching Assignment not found.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | VERIFY EXAM MATCH
        |--------------------------------------------------------------------------
        */

        if (
            (int) $teacherSubjectAllocation->exam_master_id !==
            (int) $request->exam_master_id
        ) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Selected Exam does not match the Teaching Assignment.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | VERIFY CLASS ALLOCATION
        |--------------------------------------------------------------------------
        */

        $allocation =
            $teacherSubjectAllocation->allocation;


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
        | VERIFY TEACHER AUTHORIZATION
        |--------------------------------------------------------------------------
        */

        if (
            !$this->verifyTeacherAssignment(
                $teacherSubjectAllocation,
                $request->exam_master_id
            )
        ) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'You are not authorized to enter marks for this Teaching Assignment.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | RESOLVE ACTUAL SUBJECT ID
        |--------------------------------------------------------------------------
        */

        $actualSubjectId =
            $this->resolveActualSubjectId(
                $teacherSubjectAllocation->subject_id
            );


        if (!$actualSubjectId) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to find the Subject Master record for this Teaching Assignment.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD SUBJECT
        |--------------------------------------------------------------------------
        */

        $actualSubject =
            Subject::find(
                $actualSubjectId
            );


        if (!$actualSubject) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Subject not found in Subject Master.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD EXAM
        |--------------------------------------------------------------------------
        */

        $exam =
            ExamMaster::find(
                $request->exam_master_id
            );


        if (!$exam) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Exam Master not found.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | CHECK TEACHER MARK STATUS
        |--------------------------------------------------------------------------
        */

        $marksStatusQuery =
            TeacherMarksStatus::where(
                'exam_master_id',
                $exam->id
            )
            ->where(
                'teacher_subject_allocation_id',
                $teacherSubjectAllocation->id
            );


        /*
        |--------------------------------------------------------------------------
        | TEACHER ONLY OWN STATUS
        |--------------------------------------------------------------------------
        */

        if (
            Auth::user()->role !== 'Administrator'
        ) {

            $marksStatusQuery->where(
                'teacher_id',
                Auth::id()
            );
        }


        $marksStatus =
            $marksStatusQuery->first();


        /*
        |--------------------------------------------------------------------------
        | FINAL SUBMISSION CHECK
        |--------------------------------------------------------------------------
        */

        if (
            $marksStatus &&
            strtoupper(
                trim(
                    $marksStatus->status ?? ''
                )
            ) === 'COMPLETED'
        ) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Final submission already completed. Marks cannot be modified.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | FIND EXAM SUBJECT CONFIGURATION
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | exam_master_subjects.subject_id
        | = actual subjects.id
        |
        */

        $subjectConfig =
            ExamMasterSubject::where(
                'exam_master_id',
                $exam->id
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
                    'Marks configuration not found for '
                    . $actualSubject->subject_name
                    . ' in '
                    . $exam->exam_name
                    . '. Please configure this subject in Exam Master.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | THEORY CONFIGURATION
        |--------------------------------------------------------------------------
        */

        $theoryMaxMarks =
            (float) (
                $subjectConfig->max_marks ?? 0
            );


        $theoryPassingMarks =
            (float) (
                $subjectConfig->passing_marks ?? 0
            );


        /*
        |--------------------------------------------------------------------------
        | VERIFY MAXIMUM MARKS
        |--------------------------------------------------------------------------
        */

        if (
            $theoryMaxMarks <= 0 &&
            $exam->has_theory
        ) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Maximum Theory Marks is not configured for '
                    . $actualSubject->subject_name
                    . '.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | EXAM COMPONENTS
        |--------------------------------------------------------------------------
        */

        $showTheory =
            (bool) (
                $exam->has_theory ?? 1
            );


        $showOral =
            (bool) (
                $exam->has_oral ?? 0
            );


        $showPractical =
            (bool) (
                $exam->has_practical ?? 0
            );


        /*
        |--------------------------------------------------------------------------
        | ORAL CONFIGURATION
        |--------------------------------------------------------------------------
        */

        $oralMaxMarks =
            (float) (
                $exam->oral_max_marks ?? 0
            );


        $oralPassingMarks =
            (float) (
                $exam->oral_passing_marks ?? 0
            );


        /*
        |--------------------------------------------------------------------------
        | PRACTICAL CONFIGURATION
        |--------------------------------------------------------------------------
        */

        $practicalMaxMarks =
            (float) (
                $exam->practical_max_marks ?? 0
            );


        $practicalPassingMarks =
            (float) (
                $exam->practical_passing_marks ?? 0
            );


        /*
        |--------------------------------------------------------------------------
        | BUILD VALIDATION RULES
        |--------------------------------------------------------------------------
        */

        $rules = [];


        foreach (
            $request->student_ids
            as $studentId
        ) {

            /*
            |--------------------------------------------------------------------------
            | ABSENT
            |--------------------------------------------------------------------------
            |
            | If student is absent, marks are not mandatory.
            |
            */

            $isAbsent =
                !empty(
                    $request->is_absent[$studentId] ?? 0
                );


            if ($isAbsent) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | THEORY
            |--------------------------------------------------------------------------
            */

            if ($showTheory) {

                $rules[
                    "theory_marks.$studentId"
                ] =
                    'required|numeric|min:0|max:'
                    . $theoryMaxMarks;
            }


            /*
            |--------------------------------------------------------------------------
            | ORAL
            |--------------------------------------------------------------------------
            */

            if ($showOral) {

                $rules[
                    "oral_marks.$studentId"
                ] =
                    'required|numeric|min:0|max:'
                    . $oralMaxMarks;
            }


            /*
            |--------------------------------------------------------------------------
            | PRACTICAL
            |--------------------------------------------------------------------------
            */

            if ($showPractical) {

                $rules[
                    "practical_marks.$studentId"
                ] =
                    'required|numeric|min:0|max:'
                    . $practicalMaxMarks;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDATE MARKS
        |--------------------------------------------------------------------------
        */

        $request->validate(
            $rules,
            [

                'required' =>
                    'Marks are required.',

                'numeric' =>
                    'Only numeric values are allowed.',

                'min' =>
                    'Marks cannot be negative.',

                'max' =>
                    'Marks exceed the maximum allowed marks.',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | CHECK EXISTING LOCKED MARKS
        |--------------------------------------------------------------------------
        |
        | Do NOT use section only.
        |
        | Check the exact:
        |
        | Academic Year
        | Standard
        | Division
        | Exam
        | Subject
        |
        */

        $marksLocked =
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
                'subject_id',
                $actualSubjectId
            )
            ->where(
                'is_locked',
                1
            )
            ->exists();


        if ($marksLocked) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Marks already submitted for this Exam, Standard, Division and Subject. Contact Administrator for modification.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | EXTRA MARK VALIDATION
        |--------------------------------------------------------------------------
        */

        foreach (
            $request->student_ids
            as $studentId
        ) {

            $isAbsent =
                !empty(
                    $request->is_absent[$studentId] ?? 0
                );


            /*
            |--------------------------------------------------------------------------
            | ABSENT STUDENT
            |--------------------------------------------------------------------------
            */

            if ($isAbsent) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | THEORY
            |--------------------------------------------------------------------------
            */

            if ($showTheory) {

                if (
                    !isset(
                        $request->theory_marks[$studentId]
                    )
                    ||
                    $request->theory_marks[$studentId] === ''
                ) {

                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            'Please enter Theory marks for all students.'
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
                    !isset(
                        $request->oral_marks[$studentId]
                    )
                    ||
                    $request->oral_marks[$studentId] === ''
                ) {

                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            'Please enter Oral marks for all students.'
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
                    !isset(
                        $request->practical_marks[$studentId]
                    )
                    ||
                    $request->practical_marks[$studentId] === ''
                ) {

                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            'Please enter Practical marks for all students.'
                        );
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | SAVE MARKS
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $request,
            $allocation,
            $teacherSubjectAllocation,
            $exam,
            $actualSubjectId,
            $theoryMaxMarks,
            $theoryPassingMarks,
            $oralMaxMarks,
            $oralPassingMarks,
            $practicalMaxMarks,
            $practicalPassingMarks,
            $showTheory,
            $showOral,
            $showPractical
        ) {

            foreach (
                $request->student_ids
                as $studentId
            ) {

                /*
                |--------------------------------------------------------------------------
                | ABSENT
                |--------------------------------------------------------------------------
                */

                $isAbsent =
                    !empty(
                        $request->is_absent[$studentId] ?? 0
                    );


                /*
                |--------------------------------------------------------------------------
                | THEORY
                |--------------------------------------------------------------------------
                */

                $theoryObtained =
                    $isAbsent
                        ? 0
                        : (
                            $showTheory
                                ? (
                                    $request->theory_marks[$studentId]
                                    ?? null
                                )
                                : null
                        );


                /*
                |--------------------------------------------------------------------------
                | ORAL
                |--------------------------------------------------------------------------
                */

                $oralObtained =
                    $isAbsent
                        ? 0
                        : (
                            $showOral
                                ? (
                                    $request->oral_marks[$studentId]
                                    ?? null
                                )
                                : null
                        );


                /*
                |--------------------------------------------------------------------------
                | PRACTICAL
                |--------------------------------------------------------------------------
                */

                $practicalObtained =
                    $isAbsent
                        ? 0
                        : (
                            $showPractical
                                ? (
                                    $request->practical_marks[$studentId]
                                    ?? null
                                )
                                : null
                        );


                /*
                |--------------------------------------------------------------------------
                | UPDATE OR CREATE
                |--------------------------------------------------------------------------
                */

                StudentMark::updateOrCreate(

                    [

                        'academic_year_id' =>
                            $allocation->academic_year_id,

                        'section_id' =>
                            $allocation->section_id,

                        'standard_id' =>
                            $allocation->standard_id,

                        'division_id' =>
                            $allocation->division_id,

                        'student_id' =>
                            $studentId,

                        'exam_master_id' =>
                            $exam->id,

                        /*
                        |--------------------------------------------------------------------------
                        | ACTUAL SUBJECT MASTER ID
                        |--------------------------------------------------------------------------
                        */

                        'subject_id' =>
                            $actualSubjectId,
                    ],

                    [

                        /*
                        |--------------------------------------------------------------------------
                        | TEACHER ASSIGNMENT
                        |--------------------------------------------------------------------------
                        */

                        'teacher_subject_allocation_id' =>
                            $teacherSubjectAllocation->id,


                        /*
                        |--------------------------------------------------------------------------
                        | THEORY
                        |--------------------------------------------------------------------------
                        */

                        'theory_max_marks' =>
                            $theoryMaxMarks,

                        'theory_passing_marks' =>
                            $theoryPassingMarks,

                        'theory_obtained_marks' =>
                            $theoryObtained,


                        /*
                        |--------------------------------------------------------------------------
                        | ORAL
                        |--------------------------------------------------------------------------
                        */

                        'oral_max_marks' =>
                            $oralMaxMarks,

                        'oral_passing_marks' =>
                            $oralPassingMarks,

                        'oral_obtained_marks' =>
                            $oralObtained,


                        /*
                        |--------------------------------------------------------------------------
                        | PRACTICAL
                        |--------------------------------------------------------------------------
                        */

                        'practical_max_marks' =>
                            $practicalMaxMarks,

                        'practical_passing_marks' =>
                            $practicalPassingMarks,

                        'practical_obtained_marks' =>
                            $practicalObtained,


                        /*
                        |--------------------------------------------------------------------------
                        | ABSENT
                        |--------------------------------------------------------------------------
                        */

                        'is_absent' =>
                            $isAbsent ? 1 : 0,


                        /*
                        |--------------------------------------------------------------------------
                        | KEEP UNLOCKED
                        |--------------------------------------------------------------------------
                        */

                        'is_locked' =>
                            0,


                        /*
                        |--------------------------------------------------------------------------
                        | USER
                        |--------------------------------------------------------------------------
                        */

                        'created_by' =>
                            Auth::id(),

                        'updated_by' =>
                            Auth::id(),
                    ]
                );
            }
        });


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
                $request->academic_year_id,

            'exam_master_id' =>
                $request->exam_master_id,

            'teacher_subject_allocation_id' =>
                $request->teacher_subject_allocation_id,

            'marks_saved' =>
                1,
        ]
    )
    ->with(
        'success',
        'Marks Saved Successfully.'
    );
    }


    /*
    |--------------------------------------------------------------------------
    | AUTO SAVE
    |--------------------------------------------------------------------------
    */

    public function autoSave(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        $request->validate([

            'student_id' =>
                'required',

            'exam_master_id' =>
                'required|exists:exam_masters,id',

            'teacher_subject_allocation_id' =>
                'required|exists:teacher_subject_allocations,id',

            'field' =>
                'required',

            'value' =>
                'required|numeric|min:0',
        ]);


        /*
        |--------------------------------------------------------------------------
        | LOAD ALLOCATION
        |--------------------------------------------------------------------------
        */

        $teacherSubjectAllocation =
            TeacherSubjectAllocation::with([
                'allocation',
                'subject'
            ])
            ->find(
                $request->teacher_subject_allocation_id
            );


        if (!$teacherSubjectAllocation) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Teaching Assignment not found.'
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | VERIFY EXAM
        |--------------------------------------------------------------------------
        */

        if (
            (int) $teacherSubjectAllocation->exam_master_id !==
            (int) $request->exam_master_id
        ) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Exam does not match Teaching Assignment.'
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | VERIFY TEACHER
        |--------------------------------------------------------------------------
        */

        if (
            !$this->verifyTeacherAssignment(
                $teacherSubjectAllocation,
                $request->exam_master_id
            )
        ) {

            return response()->json([
                'success' => false,
                'message' =>
                    'You are not authorized for this Teaching Assignment.'
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | RESOLVE SUBJECT
        |--------------------------------------------------------------------------
        */

        $actualSubjectId =
            $this->resolveActualSubjectId(
                $teacherSubjectAllocation->subject_id
            );


        if (!$actualSubjectId) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Unable to resolve Subject ID.'
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD ALLOCATION
        |--------------------------------------------------------------------------
        */

        $allocation =
            $teacherSubjectAllocation->allocation;


        if (!$allocation) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Teacher Class Allocation not found.'
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | CHECK MARKS STATUS
        |--------------------------------------------------------------------------
        */

        $statusQuery =
            TeacherMarksStatus::where(
                'exam_master_id',
                $request->exam_master_id
            )
            ->where(
                'teacher_subject_allocation_id',
                $teacherSubjectAllocation->id
            );


        if (
            Auth::user()->role !== 'Administrator'
        ) {

            $statusQuery->where(
                'teacher_id',
                Auth::id()
            );
        }


        $marksStatus =
            $statusQuery->first();


        if (
            $marksStatus &&
            strtoupper(
                trim(
                    $marksStatus->status ?? ''
                )
            ) === 'COMPLETED'
        ) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Marks entry has already been completed.'
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | ALLOWED FIELDS
        |--------------------------------------------------------------------------
        */

        $allowedFields = [

            'theory_obtained_marks',

            'oral_obtained_marks',

            'practical_obtained_marks',

        ];


        if (
            !in_array(
                $request->field,
                $allowedFields,
                true
            )
        ) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Invalid mark field.'
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | FIND MARK
        |--------------------------------------------------------------------------
        */

        $mark =
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
                'student_id',
                $request->student_id
            )
            ->where(
                'exam_master_id',
                $request->exam_master_id
            )
            ->where(
                'teacher_subject_allocation_id',
                $teacherSubjectAllocation->id
            )
            ->where(
                'subject_id',
                $actualSubjectId
            )
            ->first();


        if (!$mark) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Mark record not found. Please save marks first.'
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | CHECK INDIVIDUAL LOCK
        |--------------------------------------------------------------------------
        */

        if (
            (int) $mark->is_locked === 1
        ) {

            return response()->json([
                'success' => false,
                'message' =>
                    'This mark is locked and cannot be modified.'
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | CHECK FIELD MAXIMUM
        |--------------------------------------------------------------------------
        */

        $exam =
            ExamMaster::find(
                $request->exam_master_id
            );


        $subjectConfig =
            ExamMasterSubject::where(
                'exam_master_id',
                $exam->id
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

            return response()->json([
                'success' => false,
                'message' =>
                    'Subject marks configuration not found.'
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | FIELD MAXIMUM
        |--------------------------------------------------------------------------
        */

        $maxMarks = 0;


        switch ($request->field) {

            case 'theory_obtained_marks':

                $maxMarks =
                    (float) (
                        $subjectConfig->max_marks ?? 0
                    );

                break;


            case 'oral_obtained_marks':

                $maxMarks =
                    (float) (
                        $exam->oral_max_marks ?? 0
                    );

                break;


            case 'practical_obtained_marks':

                $maxMarks =
                    (float) (
                        $exam->practical_max_marks ?? 0
                    );

                break;
        }


        if (
            (float) $request->value >
            $maxMarks
        ) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Marks cannot exceed maximum marks of '
                    . $maxMarks
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | SAVE
        |--------------------------------------------------------------------------
        */

        $mark->{$request->field} =
            $request->value;

        $mark->updated_by =
            Auth::id();

        $mark->save();


        return response()->json([
            'success' => true,
            'message' =>
                'Marks saved successfully.'
        ]);
    }
}