<?php

namespace App\Http\Controllers\Marks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Subject;
use App\Models\ExamMaster;
use App\Models\StudentMark;
use App\Models\TeacherSubjectAllocation;

use App\Helpers\MarksHelper;

class MarkSaveController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | SAVE MARKS (DRAFT)
    |--------------------------------------------------------------------------
    |
    | This method saves marks as a draft.
    |
    | IMPORTANT:
    | - Draft save does NOT lock marks.
    | - Empty fields are allowed for present students.
    | - Strict validation is handled during final submission.
    | - Subject/exam-specific maximum and passing marks come from
    |   MarksHelper component configuration.
    |
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
                'exam',
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
        | VERIFY EXAM
        |--------------------------------------------------------------------------
        */

        if (
            (int) $teacherSubjectAllocation->exam_master_id
            !==
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
        | CLASS ALLOCATION
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
        | OPTIONAL FEATURE
        |--------------------------------------------------------------------------
        */

        $isOptionalEnabled =
            MarksHelper::isOptionalEnabledForAllocation(
                $allocation
            );


        /*
        |--------------------------------------------------------------------------
        | TEACHER AUTHORIZATION
        |--------------------------------------------------------------------------
        */

        if (
            !MarksHelper::verifyTeacherAssignment(
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
        | RESOLVE ACTUAL SUBJECT
        |--------------------------------------------------------------------------
        */

        $actualSubjectId =
            MarksHelper::resolveActualSubjectId(
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
            Subject::where(
                'id',
                $actualSubjectId
            )
            ->where(
                'is_active',
                1
            )
            ->first();

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

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Exam Master not found.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDATE EXAM ACADEMIC YEAR
        |--------------------------------------------------------------------------
        */

        $yearError =
            MarksHelper::validateExamAcademicYear(
                $exam,
                $allocation,
                $allocation->academic_year_id
            );

        if ($yearError) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    $yearError
                );
        }


        /*
        |--------------------------------------------------------------------------
        | TEACHER MARK STATUS
        |--------------------------------------------------------------------------
        */

        $isAdministrator =
            MarksHelper::isAdministrator();

        $marksStatus =
            MarksHelper::getTeacherMarksStatus(
                $exam->id,
                $teacherSubjectAllocation->id,
                $isAdministrator,
                Auth::id()
            );


        /*
        |--------------------------------------------------------------------------
        | FINAL SUBMISSION CHECK
        |--------------------------------------------------------------------------
        */

        if (
            MarksHelper::isCompletedStatus(
                $marksStatus
            )
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
        | SUBJECT CONFIGURATION
        |--------------------------------------------------------------------------
        */

        $subjectConfig =
            MarksHelper::resolveExamSubjectConfig(
                $exam,
                $allocation->standard_id,
                $actualSubjectId
            );

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
        | EXAM COMPONENTS
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | Term 1 / Term 2 use the dedicated term assessment structure.
        |
        | All other exams continue to use getComponentMaxMarks().
        |
        */

        if (
            MarksHelper::isTermExam(
                $exam->exam_name ?? ''
            )
        ) {

            $components =
                MarksHelper::getTermAssessmentStructure(
                    $exam,
                    $subjectConfig,
                    $allocation->standard_id
                );

        } else {

            $components =
                MarksHelper::getComponentMaxMarks(
                    $exam,
                    $subjectConfig
                );
        }


        /*
        |--------------------------------------------------------------------------
        | COMPONENT FLAGS
        |--------------------------------------------------------------------------
        */

        $showTheory =
            (bool) (
                $components['show_theory']
                ?? false
            );

        $showOral =
            (bool) (
                $components['show_oral']
                ?? false
            );

        $showPractical =
            (bool) (
                $components['show_practical']
                ?? false
            );


        /*
        |--------------------------------------------------------------------------
        | MAXIMUM MARKS
        |--------------------------------------------------------------------------
        */

        $theoryMaxMarks =
            $components['theory_max']
            ?? 0;

        $oralMaxMarks =
            $components['oral_max']
            ?? 0;

        $practicalMaxMarks =
            $components['practical_max']
            ?? 0;


        /*
        |--------------------------------------------------------------------------
        | PASSING MARKS
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | Never recalculate these values using a generic percentage here.
        |
        | MarksHelper has already resolved the configured passing marks for
        | this exact Exam + Subject + Standard.
        |
        */

        $theoryPassingMarks =
            $components['theory_passing']
            ?? 0;

        $oralPassingMarks =
            $components['oral_passing']
            ?? 0;

        $practicalPassingMarks =
            $components['practical_passing']
            ?? 0;


        /*
        |--------------------------------------------------------------------------
        | THEORY MAXIMUM CHECK
        |--------------------------------------------------------------------------
        */

        if (
            $showTheory &&
            $theoryMaxMarks <= 0
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
        | ORAL MAXIMUM CHECK
        |--------------------------------------------------------------------------
        */

        if (
            $showOral &&
            $oralMaxMarks <= 0
        ) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Maximum Oral Marks is not configured for '
                    . $actualSubject->subject_name
                    . '.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | PRACTICAL MAXIMUM CHECK
        |--------------------------------------------------------------------------
        */

        if (
            $showPractical &&
            $practicalMaxMarks <= 0
        ) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Maximum Practical Marks is not configured for '
                    . $actualSubject->subject_name
                    . '.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | CHECK EXISTING RECORDS
        |--------------------------------------------------------------------------
        |
        | Prevent cross-class conflicts.
        |
        */

        foreach ($request->student_ids as $studentId) {

            $existingMark =
                StudentMark::where(
                    'academic_year_id',
                    $allocation->academic_year_id
                )
                ->where(
                    'section_id',
                    $allocation->section_id
                )
                ->where(
                    'student_id',
                    $studentId
                )
                ->where(
                    'exam_master_id',
                    $exam->id
                )
                ->where(
                    'subject_id',
                    $actualSubjectId
                )
                ->first();

            if (!$existingMark) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | SAME CLASS
            |--------------------------------------------------------------------------
            */

            $sameStandard =
                (int) $existingMark->standard_id
                ===
                (int) $allocation->standard_id;

            $sameDivision =
                (int) $existingMark->division_id
                ===
                (int) $allocation->division_id;

            if ($sameStandard && $sameDivision) {

                /*
                |----------------------------------------------------------------------
                | ALREADY LOCKED
                |----------------------------------------------------------------------
                */

                if (
                    (int) $existingMark->is_locked === 1
                ) {

                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            'Marks for Student ID '
                            . $studentId
                            . ' have already been submitted for '
                            . $actualSubject->subject_name
                            . ' in '
                            . $exam->exam_name
                            . '. Marks cannot be modified.'
                        );
                }

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | DIFFERENT STANDARD / DIVISION
            |--------------------------------------------------------------------------
            */

            $existingDivisionName =
                DB::table('divisions')
                    ->where(
                        'id',
                        $existingMark->division_id
                    )
                    ->value('division_name');

            $currentDivisionName =
                optional(
                    $allocation->division
                )->division_name;

            $existingStandardName =
                DB::table('standards')
                    ->where(
                        'id',
                        $existingMark->standard_id
                    )
                    ->value('standard_name');

            $currentStandardName =
                optional(
                    $allocation->standard
                )->standard_name;

            $existingDivisionName =
                $existingDivisionName
                ?: 'Division ID ' . $existingMark->division_id;

            $currentDivisionName =
                $currentDivisionName
                ?: 'Division ID ' . $allocation->division_id;

            $existingStandardName =
                $existingStandardName
                ?: 'Standard ID ' . $existingMark->standard_id;

            $currentStandardName =
                $currentStandardName
                ?: 'Standard ID ' . $allocation->standard_id;

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Marks could not be saved for Student ID '
                    . $studentId
                    . '. A mark record already exists for '
                    . $actualSubject->subject_name
                    . ' in '
                    . $exam->exam_name
                    . ' under '
                    . $existingStandardName
                    . ' / '
                    . $existingDivisionName
                    . '. You are trying to save marks under '
                    . $currentStandardName
                    . ' / '
                    . $currentDivisionName
                    . '. Please verify the selected Standard and Division.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | CHECK SELECTED CLASS FOR LOCKED RECORDS
        |--------------------------------------------------------------------------
        |
        | Additional safety check.
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
                    'Marks have already been submitted for this Exam, Subject and Division. Marks cannot be modified.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | SAVE MARKS (DRAFT – NO LOCKING)
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
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
                $showPractical,
                $isOptionalEnabled
            ) {

                foreach ($request->student_ids as $studentId) {

                    /*
                    |--------------------------------------------------------------------------
                    | OPTIONAL / ABSENT
                    |--------------------------------------------------------------------------
                    */

                    $values =
                        MarksHelper::getOptionalMarkValues(
                            $request,
                            $studentId,
                            $isOptionalEnabled
                        );

                    $isOptional =
                        $values['is_optional'];

                    $isAbsent =
                        $values['is_absent'];


                    /*
                    |--------------------------------------------------------------------------
                    | OBTAINED MARKS
                    |--------------------------------------------------------------------------
                    */

                    $theoryObtained =
                        MarksHelper::resolveObtainedMark(
                            $request,
                            'theory_marks',
                            $studentId,
                            $isAbsent,
                            $isOptional,
                            $showTheory
                        );

                    $oralObtained =
                        MarksHelper::resolveObtainedMark(
                            $request,
                            'oral_marks',
                            $studentId,
                            $isAbsent,
                            $isOptional,
                            $showOral
                        );

                    $practicalObtained =
                        MarksHelper::resolveObtainedMark(
                            $request,
                            'practical_marks',
                            $studentId,
                            $isAbsent,
                            $isOptional,
                            $showPractical
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | FIND EXISTING RECORD
                    |--------------------------------------------------------------------------
                    */

                    $existingMark =
                        StudentMark::where(
                            'academic_year_id',
                            $allocation->academic_year_id
                        )
                        ->where(
                            'section_id',
                            $allocation->section_id
                        )
                        ->where(
                            'student_id',
                            $studentId
                        )
                        ->where(
                            'exam_master_id',
                            $exam->id
                        )
                        ->where(
                            'subject_id',
                            $actualSubjectId
                        )
                        ->first();


                    /*
                    |--------------------------------------------------------------------------
                    | UPDATE EXISTING DRAFT
                    |--------------------------------------------------------------------------
                    */

                    if ($existingMark) {

                        /*
                        |------------------------------------------------------------------
                        | UPDATE CLASS INFORMATION
                        |------------------------------------------------------------------
                        */

                        $existingMark->standard_id =
                            $allocation->standard_id;

                        $existingMark->division_id =
                            $allocation->division_id;

                        $existingMark->teacher_subject_allocation_id =
                            $teacherSubjectAllocation->id;


                        /*
                        |------------------------------------------------------------------
                        | MAXIMUM / PASSING
                        |------------------------------------------------------------------
                        */

                        $existingMark->theory_max_marks =
                            $theoryMaxMarks;

                        $existingMark->theory_passing_marks =
                            $theoryPassingMarks;

                        $existingMark->oral_max_marks =
                            $oralMaxMarks;

                        $existingMark->oral_passing_marks =
                            $oralPassingMarks;

                        $existingMark->practical_max_marks =
                            $practicalMaxMarks;

                        $existingMark->practical_passing_marks =
                            $practicalPassingMarks;


                        /*
                        |------------------------------------------------------------------
                        | OBTAINED
                        |------------------------------------------------------------------
                        */

                        $existingMark->theory_obtained_marks =
                            $theoryObtained;

                        $existingMark->oral_obtained_marks =
                            $oralObtained;

                        $existingMark->practical_obtained_marks =
                            $practicalObtained;


                        /*
                        |------------------------------------------------------------------
                        | STATUS
                        |------------------------------------------------------------------
                        */

                        $existingMark->is_absent =
                            $isAbsent ? 1 : 0;

                        $existingMark->is_optional =
                            $isOptional ? 1 : 0;


                        /*
                        |------------------------------------------------------------------
                        | IMPORTANT: DRAFT MUST NOT BE LOCKED
                        |------------------------------------------------------------------
                        */

                        $existingMark->is_locked = 0;


                        /*
                        |------------------------------------------------------------------
                        | AUDIT
                        |------------------------------------------------------------------
                        */

                        $existingMark->updated_by =
                            Auth::id();

                        $existingMark->save();

                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | CREATE NEW RECORD
                    |--------------------------------------------------------------------------
                    */

                    StudentMark::create([

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

                        'subject_id' =>
                            $actualSubjectId,

                        'teacher_subject_allocation_id' =>
                            $teacherSubjectAllocation->id,


                        /*
                        |------------------------------------------------------------------
                        | THEORY
                        |------------------------------------------------------------------
                        */

                        'theory_max_marks' =>
                            $theoryMaxMarks,

                        'theory_passing_marks' =>
                            $theoryPassingMarks,

                        'theory_obtained_marks' =>
                            $theoryObtained,


                        /*
                        |------------------------------------------------------------------
                        | ORAL
                        |------------------------------------------------------------------
                        */

                        'oral_max_marks' =>
                            $oralMaxMarks,

                        'oral_passing_marks' =>
                            $oralPassingMarks,

                        'oral_obtained_marks' =>
                            $oralObtained,


                        /*
                        |------------------------------------------------------------------
                        | PRACTICAL
                        |------------------------------------------------------------------
                        */

                        'practical_max_marks' =>
                            $practicalMaxMarks,

                        'practical_passing_marks' =>
                            $practicalPassingMarks,

                        'practical_obtained_marks' =>
                            $practicalObtained,


                        /*
                        |------------------------------------------------------------------
                        | STATUS
                        |------------------------------------------------------------------
                        */

                        'is_absent' =>
                            $isAbsent ? 1 : 0,

                        'is_optional' =>
                            $isOptional ? 1 : 0,

                        /*
                        | IMPORTANT:
                        | Draft records remain unlocked.
                        */

                        'is_locked' =>
                            0,

                        /*
                        |------------------------------------------------------------------
                        | AUDIT
                        |------------------------------------------------------------------
                        */

                        'created_by' =>
                            Auth::id(),

                        'updated_by' =>
                            Auth::id(),
                    ]);
                }
            }
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
                'Marks Saved Successfully (Draft).'
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
        ]);


        /*
        |--------------------------------------------------------------------------
        | LOAD TSA
        |--------------------------------------------------------------------------
        */

        $teacherSubjectAllocation =
            TeacherSubjectAllocation::with([
                'allocation',
                'allocation.standard',
                'allocation.division',
                'subject',
                'exam',
            ])
            ->find(
                $request->teacher_subject_allocation_id
            );

        if (!$teacherSubjectAllocation) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Teaching Assignment not found.',
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | VERIFY EXAM
        |--------------------------------------------------------------------------
        */

        if (
            (int) $teacherSubjectAllocation->exam_master_id
            !==
            (int) $request->exam_master_id
        ) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Exam does not match Teaching Assignment.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | ALLOCATION
        |--------------------------------------------------------------------------
        */

        $allocation =
            $teacherSubjectAllocation->allocation;

        if (!$allocation) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Teacher Class Allocation not found.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | OPTIONAL
        |--------------------------------------------------------------------------
        */

        $isOptionalEnabled =
            MarksHelper::isOptionalEnabledForAllocation(
                $allocation
            );


        /*
        |--------------------------------------------------------------------------
        | TEACHER AUTHORIZATION
        |--------------------------------------------------------------------------
        */

        if (
            !MarksHelper::verifyTeacherAssignment(
                $teacherSubjectAllocation,
                $request->exam_master_id
            )
        ) {

            return response()->json([
                'success' => false,
                'message' =>
                    'You are not authorized for this Teaching Assignment.',
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | SUBJECT
        |--------------------------------------------------------------------------
        */

        $actualSubjectId =
            MarksHelper::resolveActualSubjectId(
                $teacherSubjectAllocation->subject_id
            );

        if (!$actualSubjectId) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Unable to resolve Subject ID.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD EXAM
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

            return response()->json([
                'success' => false,
                'message' =>
                    'Exam not found.',
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDATE EXAM ACADEMIC YEAR
        |--------------------------------------------------------------------------
        */

        $yearError =
            MarksHelper::validateExamAcademicYear(
                $exam,
                $allocation,
                $allocation->academic_year_id
            );

        if ($yearError) {

            return response()->json([
                'success' => false,
                'message' =>
                    $yearError,
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        $isAdministrator =
            MarksHelper::isAdministrator();

        $marksStatus =
            MarksHelper::getTeacherMarksStatus(
                $request->exam_master_id,
                $teacherSubjectAllocation->id,
                $isAdministrator,
                Auth::id()
            );

        if (
            MarksHelper::isCompletedStatus(
                $marksStatus
            )
        ) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Marks entry has already been completed.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | ALLOWED FIELD
        |--------------------------------------------------------------------------
        */

        if (
            !MarksHelper::isAllowedMarkField(
                $request->field
            )
        ) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Invalid mark field.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | OPTIONAL FIELD
        |--------------------------------------------------------------------------
        */

        if (
            $request->field === 'is_optional'
        ) {

            if (!$isOptionalEnabled) {

                return response()->json([
                    'success' => false,
                    'message' =>
                        'Optional status is available only for 11th and 12th standards.',
                ], 422);
            }


            /*
            |--------------------------------------------------------------------------
            | OPTIONAL VALUE
            |--------------------------------------------------------------------------
            */

            $optionalValue =
                MarksHelper::toBoolean(
                    $request->value
                );


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
                    'student_id',
                    $request->student_id
                )
                ->where(
                    'exam_master_id',
                    $request->exam_master_id
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
                        'Mark record not found. Please save marks first.',
                ], 404);
            }


            /*
            |--------------------------------------------------------------------------
            | CROSS STANDARD / DIVISION
            |--------------------------------------------------------------------------
            */

            if (
                (int) $mark->standard_id
                !==
                (int) $allocation->standard_id
                ||
                (int) $mark->division_id
                !==
                (int) $allocation->division_id
            ) {

                return response()->json([
                    'success' => false,
                    'message' =>
                        'This student already has marks for this Exam and Subject under another Standard or Division. Please verify the selected class and Division.',
                ], 422);
            }


            /*
            |--------------------------------------------------------------------------
            | INDIVIDUAL LOCK
            |--------------------------------------------------------------------------
            */

            if (
                (int) $mark->is_locked === 1
            ) {

                return response()->json([
                    'success' => false,
                    'message' =>
                        'Marks have already been submitted for this student. This mark is locked and cannot be modified.',
                ], 422);
            }


            /*
            |--------------------------------------------------------------------------
            | SAVE OPTIONAL
            |--------------------------------------------------------------------------
            */

            $mark->is_optional =
                $optionalValue ? 1 : 0;


            /*
            |--------------------------------------------------------------------------
            | OPTIONAL OVERRIDES ABSENT
            |--------------------------------------------------------------------------
            */

            if ($optionalValue) {

                $mark->is_absent =
                    0;

                $mark->theory_obtained_marks =
                    0;

                $mark->oral_obtained_marks =
                    0;

                $mark->practical_obtained_marks =
                    0;
            }


            $mark->updated_by =
                Auth::id();

            $mark->save();


            return response()->json([
                'success' => true,
                'message' =>
                    $optionalValue
                        ? 'Student marked as Optional.'
                        : 'Student marked as applicable.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | NUMERIC VALUE
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'value' =>
                'required|numeric|min:0',
        ], [

            'value.required' =>
                'Marks value is required.',

            'value.numeric' =>
                'Only numeric values are allowed.',

            'value.min' =>
                'Marks cannot be negative.',
        ]);


        /*
        |--------------------------------------------------------------------------
        | FIND EXISTING MARK
        |--------------------------------------------------------------------------
        |
        | Current database identity:
        |
        | academic_year_id
        | section_id
        | exam_master_id
        | subject_id
        | student_id
        |
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
                'student_id',
                $request->student_id
            )
            ->where(
                'exam_master_id',
                $request->exam_master_id
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
                    'Mark record not found. Please save marks first.',
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | CROSS STANDARD / DIVISION
        |--------------------------------------------------------------------------
        */

        if (
            (int) $mark->standard_id
            !==
            (int) $allocation->standard_id
            ||
            (int) $mark->division_id
            !==
            (int) $allocation->division_id
        ) {

            return response()->json([
                'success' => false,
                'message' =>
                    'This student already has marks for this Exam and Subject under another Standard or Division. Please verify the selected class and Division.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | INDIVIDUAL LOCK
        |--------------------------------------------------------------------------
        */

        if (
            (int) $mark->is_locked === 1
        ) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Marks have already been submitted for this student. This mark is locked and cannot be modified.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | SUBJECT CONFIGURATION
        |--------------------------------------------------------------------------
        */

        $subjectConfig =
            MarksHelper::resolveExamSubjectConfig(
                $exam,
                $allocation->standard_id,
                $actualSubjectId
            );

        if (!$subjectConfig) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Subject marks configuration not found.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | FIELD MAXIMUM
        |--------------------------------------------------------------------------
        |
        | getFieldMaxMarks() internally uses the appropriate exam/component
        | configuration. This keeps AutoSave consistent with normal Save.
        |
        */

        $maxMarks =
            MarksHelper::getFieldMaxMarks(
                $request->field,
                $exam,
                $subjectConfig
            );


        /*
        |--------------------------------------------------------------------------
        | MAXIMUM CHECK
        |--------------------------------------------------------------------------
        */

        if (
            !MarksHelper::validateFieldMaximum(
                $request->value,
                $maxMarks
            )
        ) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Marks cannot exceed maximum marks of '
                    . MarksHelper::formatMark(
                        $maxMarks
                    ),
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | OPTIONAL PROTECTION
        |--------------------------------------------------------------------------
        */

        if (
            MarksHelper::isOptionalStudent(
                $mark
            )
        ) {

            return response()->json([
                'success' => false,
                'message' =>
                    'This student is marked Optional. Disable Optional before entering marks.',
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


        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,
            'message' =>
                'Marks saved successfully.',
        ]);
    }
}

