<?php

namespace App\Http\Controllers\Marks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\ExamMaster;
use App\Models\TeacherMarksStatus;
use App\Models\TeacherSubjectAllocation;
use App\Models\StudentMark;
use App\Models\ExamMasterSubject;
use App\Helpers\StudentHelper;

class MarkEntryController extends Controller
{
    /**
     * Display Marks Entry page.
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

        $exam = null;
        $teacherSubjectAllocation = null;
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
        | RESTORE PREVIOUS SELECTION FROM SESSION
        |--------------------------------------------------------------------------
        */

        if (
            !$request->filled('teacher_subject_allocation_id') &&
            session()->has('marks_teacher_subject_allocation_id')
        ) {
            $request->merge([
                'teacher_subject_allocation_id' =>
                    session('marks_teacher_subject_allocation_id')
            ]);
        }

        if (
            !$request->filled('exam_master_id') &&
            session()->has('marks_exam_master_id')
        ) {
            $request->merge([
                'exam_master_id' =>
                    session('marks_exam_master_id')
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | LOAD ACTIVE EXAMS
        |--------------------------------------------------------------------------
        */

        $exams = ExamMaster::where('is_active', 1)
            ->orderBy('display_order')
            ->orderBy('exam_name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | LOAD TEACHER ASSIGNMENTS
        |--------------------------------------------------------------------------
        |
        | Only PENDING assignments are shown.
        |
        */

        if ($request->filled('exam_master_id')) {

            $statusQuery = TeacherMarksStatus::query()
                ->where(
                    'exam_master_id',
                    $request->exam_master_id
                )
                ->where(
                    'status',
                    'PENDING'
                );

            /*
            |--------------------------------------------------------------------------
            | TEACHER CAN SEE ONLY OWN ASSIGNMENTS
            |--------------------------------------------------------------------------
            */

            if (
                Auth::check() &&
                Auth::user()->role !== 'Administrator'
            ) {
                $statusQuery->where(
                    'teacher_id',
                    Auth::id()
                );
            }

            $pendingIds = $statusQuery
                ->pluck(
                    'teacher_subject_allocation_id'
                )
                ->filter()
                ->unique()
                ->values();

            if ($pendingIds->isNotEmpty()) {

                $assignments = TeacherSubjectAllocation::with([
    'allocation.teacher',
    'allocation.academicYear',
    'allocation.section',
    'allocation.standard',
    'allocation.division',
    'subject',
    'exam'
])
                ->whereIn(
                    'id',
                    $pendingIds
                )
                ->orderByDesc('id')
                ->get();
            }

            if ($assignments->isEmpty()) {
                $error = "No pending teaching assignment found for the selected exam.";
            }
        }

        /*
        |--------------------------------------------------------------------------
        | LOAD SELECTED TEACHER SUBJECT ALLOCATION
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('teacher_subject_allocation_id') &&
            $request->filled('exam_master_id')
        ) {

            $teacherSubjectAllocation =
    TeacherSubjectAllocation::with([
        'allocation.teacher',
        'allocation.academicYear',
        'allocation.section',
        'allocation.standard',
        'allocation.division',
        'subject',
        'exam'
    ])
                ->where(
                    'id',
                    $request->teacher_subject_allocation_id
                )
                ->where(
                    'exam_master_id',
                    $request->exam_master_id
                )
                ->first();

            if (!$teacherSubjectAllocation) {

                $error =
                    "Selected teaching assignment was not found.";
            }
        }

        /*
        |--------------------------------------------------------------------------
        | SECURITY CHECK
        |--------------------------------------------------------------------------
        |
        | Verify that the selected assignment belongs to the logged-in teacher.
        |
        */

        if (
            $teacherSubjectAllocation &&
            Auth::check() &&
            Auth::user()->role !== 'Administrator'
        ) {

            $validAssignment = TeacherMarksStatus::where(
                'teacher_subject_allocation_id',
                $teacherSubjectAllocation->id
            )
            ->where(
                'exam_master_id',
                $request->exam_master_id
            )
            ->where(
                'teacher_id',
                Auth::id()
            )
            ->exists();

            if (!$validAssignment) {

                $teacherSubjectAllocation = null;

                $error =
                    "You are not authorized to access this teaching assignment.";
            }
        }

        /*
        |--------------------------------------------------------------------------
        | LOAD EXAM
        |--------------------------------------------------------------------------
        */

        if ($request->filled('exam_master_id')) {

            $exam = ExamMaster::find(
                $request->exam_master_id
            );

            if (!$exam) {

                $error =
                    "Selected exam was not found.";
            }
        }

        /*
        |--------------------------------------------------------------------------
        | LOAD SUBJECT-WISE EXAM CONFIGURATION
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | Max Marks / Passing Marks are taken from:
        |
        | exam_master_subjects
        |
        | NOT directly from ExamMaster.
        |
        */

        if (
            $exam &&
            $teacherSubjectAllocation
        ) {

            $allocation =
                $teacherSubjectAllocation->allocation;

            /*
            |--------------------------------------------------------------------------
            | ACTUAL SUBJECT ID
            |--------------------------------------------------------------------------
            */

            $subjectId =
                $teacherSubjectAllocation->subject_id;

            /*
            |--------------------------------------------------------------------------
            | STANDARD ID
            |--------------------------------------------------------------------------
            */

            $standardId =
                $allocation
                    ? $allocation->standard_id
                    : null;

            /*
            |--------------------------------------------------------------------------
            | FIND EXAM SUBJECT CONFIGURATION
            |--------------------------------------------------------------------------
            */

            $subjectConfig =
                ExamMasterSubject::where(
                    'exam_master_id',
                    $exam->id
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

            if (!$subjectConfig) {

                $subjectName =
    optional(
        $teacherSubjectAllocation->subject
    )->subject_name
    ?? 'Selected Subject';
    
                $error =
                    "Marks configuration not found for "
                    . $subjectName
                    . " in "
                    . $exam->exam_name
                    . ". Please configure this subject in Exam Master.";

                $theoryMaxMarks = 0;
                $theoryPassingMarks = 0;
            }

            /*
            |--------------------------------------------------------------------------
            | SUBJECT MARKS
            |--------------------------------------------------------------------------
            */

            if ($subjectConfig) {

                $theoryMaxMarks =
                    $subjectConfig->max_marks ?? 0;

                $theoryPassingMarks =
                    $subjectConfig->passing_marks ?? 0;
            }

            /*
            |--------------------------------------------------------------------------
            | EXAM COMPONENTS
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
                    $exam->oral_max_marks ?? 0;

                $oralPassingMarks =
                    $exam->oral_passing_marks ?? 0;
            }

            /*
            |--------------------------------------------------------------------------
            | PRACTICAL
            |--------------------------------------------------------------------------
            */

            if ($showPractical) {

                $practicalMaxMarks =
                    $exam->practical_max_marks ?? 0;

                $practicalPassingMarks =
                    $exam->practical_passing_marks ?? 0;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | LOAD STUDENTS
        |--------------------------------------------------------------------------
        */

        if (
            $teacherSubjectAllocation &&
            $exam
        ) {

            $allocation =
                $teacherSubjectAllocation->allocation;

            if (!$allocation) {

                $error =
                    "Teacher class allocation not found.";

            } else {

                try {

                    /*
                    |--------------------------------------------------------------------------
                    | GET STUDENTS FROM OLD ERP
                    |--------------------------------------------------------------------------
                    */

                    $students =
                        StudentHelper::getStudentsDirectERP(
                            $allocation->academic_year_id,
                            $allocation->standard_id,
                            $allocation->division_id
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | SORT:
                    | FEMALE FIRST
                    | THEN NAME
                    |--------------------------------------------------------------------------
                    */

                    $students = $students
                        ->sort(function ($a, $b) {

                            $genderA =
                                strtoupper(
                                    trim(
                                        $a->gender ?? ''
                                    )
                                );

                            $genderB =
                                strtoupper(
                                    trim(
                                        $b->gender ?? ''
                                    )
                                );

                            if ($genderA !== $genderB) {

                                if (
                                    $genderA === 'FEMALE'
                                ) {
                                    return -1;
                                }

                                if (
                                    $genderB === 'FEMALE'
                                ) {
                                    return 1;
                                }
                            }

                            return strcmp(
                                strtoupper(
                                    trim(
                                        $a->studname ?? ''
                                    )
                                ),
                                strtoupper(
                                    trim(
                                        $b->studname ?? ''
                                    )
                                )
                            );
                        })
                        ->values();

                    /*
                    |--------------------------------------------------------------------------
                    | NO STUDENTS
                    |--------------------------------------------------------------------------
                    */

                    if ($students->count() === 0) {

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
                            "No students found for "
                            . $standardName
                            . " - "
                            . $divisionName
                            . ". Please verify Student Master.";
                    }

                } catch (\Throwable $e) {

                    report($e);

                    $error =
                        "Old ERP Error: "
                        . $e->getMessage();

                    $students = collect();
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | CHECK MARKS STATUS
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'teacher_subject_allocation_id'
            ) &&
            $request->filled(
                'exam_master_id'
            )
        ) {

            $statusQuery =
                TeacherMarksStatus::where(
                    'teacher_subject_allocation_id',
                    $request->teacher_subject_allocation_id
                )
                ->where(
                    'exam_master_id',
                    $request->exam_master_id
                );

            /*
            |--------------------------------------------------------------------------
            | TEACHER STATUS SECURITY
            |--------------------------------------------------------------------------
            */

            if (
                Auth::check() &&
                Auth::user()->role !== 'Administrator'
            ) {

                $statusQuery->where(
                    'teacher_id',
                    Auth::id()
                );
            }

            $marksStatus =
                $statusQuery->first();

            /*
            |--------------------------------------------------------------------------
            | COMPLETED = LOCKED
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

                $marksLocked = true;

                $message =
                    "Marks entry has already been completed and is locked.";
            }
            if ($marksLocked) {
    $students = collect();
}
        }

        /*
        |--------------------------------------------------------------------------
        | LOAD EXISTING MARKS
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('exam_master_id') &&
            $request->filled(
                'teacher_subject_allocation_id'
            )
        ) {

            $existingMarks =
                StudentMark::where(
                    'exam_master_id',
                    $request->exam_master_id
                )
                ->where(
                    'teacher_subject_allocation_id',
                    $request->teacher_subject_allocation_id
                )
                ->get()
                ->keyBy('student_id');
        }

        /*
        |--------------------------------------------------------------------------
        | SAVE SESSION VALUES
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'teacher_subject_allocation_id'
            )
        ) {

            session([
                'marks_teacher_subject_allocation_id' =>
                    $request->teacher_subject_allocation_id
            ]);
        }

        if (
            $request->filled('exam_master_id')
        ) {

            session([
                'marks_exam_master_id' =>
                    $request->exam_master_id
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'marks-entry.index',
            compact(
                'request',
                'exams',
                'assignments',
                'students',
                'exam',
                'teacherSubjectAllocation',
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