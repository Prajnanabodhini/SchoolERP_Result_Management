<?php

namespace App\Http\Controllers\Marks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\StudentMark;
use App\Models\ExamMaster;
use App\Models\Standard;
use App\Models\Division;
use App\Models\Subject;
use App\Models\ExamMasterSubject;
use App\Models\StandardWiseSubject;

class MarkViewController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | VIEW MARKS
    |--------------------------------------------------------------------------
    */

    public function viewMarks(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Initial Values
        |--------------------------------------------------------------------------
        */

        $records = collect();
        $subjects = collect();

        $exam = null;

        $showTheory = false;
        $showOral = false;
        $showPractical = false;

        /*
        |--------------------------------------------------------------------------
        | Load Exams
        |--------------------------------------------------------------------------
        */

        $exams = ExamMaster::where('is_active', 1)
            ->orderBy('display_order')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Load Standards
        |--------------------------------------------------------------------------
        */

        $standards = Standard::where('is_active', 1)
            ->orderBy('display_order')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Load Divisions
        |--------------------------------------------------------------------------
        */

        $divisions = Division::where('is_active', 1)
            ->orderBy('division_name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Selected Exam
        |--------------------------------------------------------------------------
        */

        if ($request->filled('exam_master_id')) {

            $exam = ExamMaster::find(
                $request->exam_master_id
            );

            if ($exam) {

                $showTheory =
                    (bool) $exam->has_theory;

                $showOral =
                    (bool) $exam->has_oral;

                $showPractical =
                    (bool) $exam->has_practical;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | LOAD SUBJECTS
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | Subjects are loaded from exam_master_subjects.
        |
        | This guarantees that the View Marks page displays only subjects
        | configured for the selected Exam + Standard.
        |
        */

        if (
            $request->filled('exam_master_id') &&
            $request->filled('standard_id')
        ) {

            $subjects = ExamMasterSubject::query()

                ->join(
                    'subjects',
                    'subjects.id',
                    '=',
                    'exam_master_subjects.subject_id'
                )

                ->where(
                    'exam_master_subjects.exam_master_id',
                    $request->exam_master_id
                )

                ->where(
                    'exam_master_subjects.standard_id',
                    $request->standard_id
                )

                ->where(
                    'subjects.is_active',
                    1
                )

                ->select([
                    'subjects.id',
                    'subjects.subject_name',

                    'exam_master_subjects.max_marks',
                    'exam_master_subjects.passing_marks',
                    'exam_master_subjects.display_order'
                ])

                ->orderBy(
                    'exam_master_subjects.display_order'
                )

                ->orderBy(
                    'subjects.subject_name'
                )

                ->get()
                ->unique('id')
                ->values();
        }

        /*
        |--------------------------------------------------------------------------
        | LOAD MARK RECORDS
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('exam_master_id') &&
            $request->filled('standard_id') &&
            $request->filled('division_id') &&
            $request->filled('subject_id')
        ) {

            /*
            |--------------------------------------------------------------------------
            | Verify Selected Subject Belongs To Exam
            |--------------------------------------------------------------------------
            */

            $subjectExists =
                ExamMasterSubject::where(
                    'exam_master_id',
                    $request->exam_master_id
                )
                ->where(
                    'standard_id',
                    $request->standard_id
                )
                ->where(
                    'subject_id',
                    $request->subject_id
                )
                ->exists();

            if (!$subjectExists) {

                return redirect()
                    ->back()
                    ->withInput()
                    ->with(
                        'error',
                        'Selected subject is not configured for the selected Exam and Standard.'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | Build Query
            |--------------------------------------------------------------------------
            */

            $query = StudentMark::query()

                /*
                |--------------------------------------------------------------------------
                | Old ERP Student Table
                |--------------------------------------------------------------------------
                */

                ->leftJoin(
                    'feemststudent as fs',
                    'fs.Studentid',
                    '=',
                    'student_marks.student_id'
                )

                /*
                |--------------------------------------------------------------------------
                | Subject
                |--------------------------------------------------------------------------
                */

                ->join(
                    'subjects',
                    'subjects.id',
                    '=',
                    'student_marks.subject_id'
                )

                /*
                |--------------------------------------------------------------------------
                | Exam
                |--------------------------------------------------------------------------
                */

                ->join(
                    'exam_masters',
                    'exam_masters.id',
                    '=',
                    'student_marks.exam_master_id'
                )

                /*
                |--------------------------------------------------------------------------
                | Filters
                |--------------------------------------------------------------------------
                */

                ->where(
                    'student_marks.exam_master_id',
                    $request->exam_master_id
                )

                ->where(
                    'student_marks.standard_id',
                    $request->standard_id
                )

                ->where(
                    'student_marks.division_id',
                    $request->division_id
                )

                ->where(
                    'student_marks.subject_id',
                    $request->subject_id
                );

            /*
            |--------------------------------------------------------------------------
            | Select
            |--------------------------------------------------------------------------
            */

            $records = $query

                ->select([
                    'student_marks.*',

                    'fs.studname',

                    'subjects.subject_name',

                    'exam_masters.exam_name'
                ])

                ->orderBy(
                    'fs.studname'
                )

                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'marks-entry.view',
            compact(
                'records',
                'standards',
                'divisions',
                'subjects',
                'exams',
                'exam',
                'showTheory',
                'showOral',
                'showPractical'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SEARCH MARKS
    |--------------------------------------------------------------------------
    */

    public function searchMarks(Request $request)
    {
        return $this->viewMarks($request);
    }
}