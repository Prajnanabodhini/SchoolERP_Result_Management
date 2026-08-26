<?php

namespace App\Http\Controllers\Marks;

use App\Http\Controllers\Controller;

use App\Models\AcademicYear;
use App\Models\Standard;
use App\Models\ExamMaster;
use App\Http\Controllers\Marks\AdminMarksAssignmentService;
use App\Http\Controllers\Marks\AdminMarksEntryService;
use App\Http\Controllers\Marks\AdminMarksSubjectService;

use Illuminate\Http\Request;

class AdminMarksController extends Controller
{
    public function __construct(
        private AdminMarksAssignmentService $assignmentService,
        private AdminMarksSubjectService $subjectService,
        private AdminMarksEntryService $entryService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    |
    | IMPORTANT PERFORMANCE RULE
    |
    | The controller does NOT query student_marks here.
    |
    | Initial page:
    |
    |     Academic Years
    |     Standards
    |     Exams
    |     Current assignments
    |
    | Existing student marks are loaded only after a particular
    | teaching assignment is selected.
    |
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ) {
        /*
        |--------------------------------------------------------------------------
        | FILTERS
        |--------------------------------------------------------------------------
        */

        $academicYearId =
            $request->input(
                'academic_year_id'
            );

        $examMasterId =
            $request->input(
                'exam_master_id'
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
        | STANDARDS
        |--------------------------------------------------------------------------
        */

        $standards =
            Standard::query()
                ->where(
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
        | New Exam Master records have academic_year_id.
        |
        | Older records may have academic_year_id = NULL.
        |
        | Therefore:
        |
        | selected year
        |       ↓
        | current-year exams
        |       +
        | legacy exams with NULL year
        |
        |--------------------------------------------------------------------------
        */

        $examQuery =
            ExamMaster::query()
                ->where(
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

                    $query
                        ->where(
                            'academic_year_id',
                            (int) $academicYearId
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


        /*
        |--------------------------------------------------------------------------
        | PREPARE EXAM DISPLAY NAMES
        |--------------------------------------------------------------------------
        */

        $exams =
            $this->assignmentService
                ->prepareExams(
                    $exams,
                    $standards
                );


        /*
        |--------------------------------------------------------------------------
        | LOAD TEACHING ASSIGNMENTS
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | If no Exam is selected, the service should immediately return
        | an empty collection.
        |
        | This prevents a full database scan when the page is first opened.
        |
        |--------------------------------------------------------------------------
        */

        $assignments =
            $this->assignmentService
                ->getAssignments(
                    $academicYearId,
                    $examMasterId
                );


        /*
        |--------------------------------------------------------------------------
        | LOAD SELECTED MARK DATA
        |--------------------------------------------------------------------------
        |
        | This service is responsible for loading:
        |
        | - selected TSA
        | - selected subject
        | - students
        | - existing student marks
        | - mark configuration
        |
        | If nothing is selected, it should return empty data immediately.
        |
        |--------------------------------------------------------------------------
        */

        $selected =
            $this->entryService
                ->loadSelectedData(
                    $request,
                    $exams,
                    $this->subjectService
                );


        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

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

                    /*
                    |--------------------------------------------------------------------------
                    | STATUS FLAGS
                    |--------------------------------------------------------------------------
                    */

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
    |
    | Existing route can continue to point to edit().
    |
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
    | Called by AJAX when Administrator selects:
    |
    | Teacher Class Allocation + Exam
    |
    |--------------------------------------------------------------------------
    */

    public function getSubjects(
        Request $request
    ) {
        return $this->assignmentService
            ->getSubjects(
                $request,
                $this->subjectService
            );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE MARKS
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request
    ) {
        return $this->entryService
            ->update(
                $request,
                $this->subjectService
            );
    }


    /*
    |--------------------------------------------------------------------------
    | REOPEN MARKS
    |--------------------------------------------------------------------------
    */

    public function reopen(
        Request $request
    ) {
        return $this->entryService
            ->reopen(
                $request,
                $this->subjectService
            );
    }
}