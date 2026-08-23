<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\ExamMaster;
use App\Models\ExamMasterSubject;
use App\Models\Standard;
use App\Models\StandardWiseSubject;
use App\Models\ExamSubject;
use App\Models\Subject;

class ExamMasterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $examMasters = ExamMaster::with('standard')
            ->orderByDesc('id')
            ->get();

        return view(
            'exam-masters.index',
            compact('examMasters')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PASSING PERCENTAGE
    |--------------------------------------------------------------------------
    |
    | 9th  = 35%
    | 10th = 35%
    | Other standards = 40%
    |
    |--------------------------------------------------------------------------
    */

    private function getPassingPercentage($standardId): float
    {
        return in_array(
            (int) $standardId,
            [9, 10],
            true
        )
            ? 35.0
            : 40.0;
    }


    /*
    |--------------------------------------------------------------------------
    | CALCULATE PASSING MARKS
    |--------------------------------------------------------------------------
    */

    private function calculatePassingMarks(
        $maxMarks,
        $standardId
    ): int {

        $maxMarks =
            (float) $maxMarks;

        if ($maxMarks <= 0) {
            return 0;
        }

        $percentage =
            $this->getPassingPercentage(
                $standardId
            );

        return (int) ceil(
            $maxMarks *
            (
                $percentage / 100
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | LOAD AUTHORITATIVE STANDARD SUBJECTS
    |--------------------------------------------------------------------------
    |
    | standard_wise_subjects + subjects
    |
    | The actual subject ID ALWAYS comes from:
    |
    | subjects.id
    |
    |--------------------------------------------------------------------------
    */

    private function getStandardSubjects(
        int $standardId
    ) {

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
            'sws.is_active',
            1
        )
        ->where(
            's.is_active',
            1
        )
        ->orderBy(
            'sws.sort_order'
        )
        ->orderBy(
            's.id'
        )
        ->select([

            /*
            | Actual Subject Master ID
            */

            's.id as subject_id',

            /*
            | Subject Master
            */

            's.subject_name',

            's.subject_code',

            's.short_name',

            /*
            | Standard-wise mapping
            */

            'sws.id as standard_wise_subject_id',

            'sws.standard_id',

            'sws.sort_order',

            'sws.is_optional',

        ])
        ->get();
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $standards =
            Standard::where(
                'is_active',
                1
            )
            ->orderBy(
                'display_order'
            )
            ->get();


        $nextDisplayOrder =
            (
                ExamMaster::max(
                    'display_order'
                ) ?? 0
            ) + 1;


        return view(
            'exam-masters.create',
            compact(
                'standards',
                'nextDisplayOrder'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    |
    | ALL active subjects of selected Standard are automatically saved.
    |
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ) {

        $request->validate([

            'exam_name' => [
                'required',
                'string',
                'max:100',
            ],

            'standard_id' => [
                'required',
                'integer',
                'exists:standards,id',
            ],

            'display_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            /*
            | The Blade sends the Max Marks configuration.
            | Subject IDs are keys and are already actual subjects.id.
            */

            'subjects' => [
                'required',
                'array',
                'min:1',
            ],

        ]);


        $standardId =
            (int) $request->standard_id;


        $examName =
            strtoupper(
                trim(
                    $request->exam_name
                )
            );


        /*
        |--------------------------------------------------------------------------
        | DUPLICATE EXAM CHECK
        |--------------------------------------------------------------------------
        */

        $exists =
            ExamMaster::where(
                'standard_id',
                $standardId
            )
            ->where(
                'exam_name',
                $examName
            )
            ->exists();


        if ($exists) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Exam Master already exists for this Standard.'
                );
        }


        try {

            DB::transaction(
                function () use (
                    $request,
                    $examName,
                    $standardId
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | CREATE EXAM MASTER
                    |--------------------------------------------------------------------------
                    */

                    $examMaster =
                        ExamMaster::create([

                            'exam_name' =>
                                $examName,

                            'standard_id' =>
                                $standardId,

                            'max_marks' =>
                                0,

                            'passing_marks' =>
                                0,

                            'display_order' =>
                                $request->filled(
                                    'display_order'
                                )
                                    ? (int)
                                        $request->display_order
                                    : 0,

                            'is_active' =>
                                $request->boolean(
                                    'is_active'
                                ),

                        ]);


                    /*
                    |--------------------------------------------------------------------------
                    | LOAD AUTHORITATIVE SUBJECTS
                    |--------------------------------------------------------------------------
                    */

                    $standardSubjects =
                        $this->getStandardSubjects(
                            $standardId
                        );


                    if (
                        $standardSubjects->isEmpty()
                    ) {

                        throw new \RuntimeException(
                            'No active subjects are mapped to the selected Standard.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | SAVE ALL SUBJECTS
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        $standardSubjects as
                        $standardSubject
                    ) {

                        /*
                        |--------------------------------------------------------------------------
                        | GET CONFIGURATION SUBMITTED FOR THIS SUBJECT
                        |--------------------------------------------------------------------------
                        |
                        | subjects[actual_subject_id][max_marks]
                        |
                        */

                        $subjectConfig =
                            $request->input(
                                'subjects.' .
                                $standardSubject->subject_id,
                                []
                            );


                        $maxMarks =
                            isset(
                                $subjectConfig['max_marks']
                            )
                                ? (float)
                                    $subjectConfig['max_marks']
                                : 40.0;


                        if (
                            $maxMarks < 0
                        ) {
                            $maxMarks = 0;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | PASSING MARKS
                        |--------------------------------------------------------------------------
                        */

                        $passingMarks =
                            $this->calculatePassingMarks(
                                $maxMarks,
                                $standardId
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | DISPLAY ORDER
                        |--------------------------------------------------------------------------
                        */

                        $displayOrder =
                            isset(
                                $subjectConfig['display_order']
                            )
                                ? (int)
                                    $subjectConfig['display_order']
                                : (
                                    (int)
                                    (
                                        $standardSubject->sort_order
                                        ?? 0
                                    )
                                );


                        /*
                        |--------------------------------------------------------------------------
                        | CREATE EXAM SUBJECT CONFIGURATION
                        |--------------------------------------------------------------------------
                        */

                        ExamMasterSubject::create([

                            'exam_master_id' =>
                                $examMaster->id,

                            'standard_id' =>
                                $standardId,

                            /*
                            | IMPORTANT:
                            | ACTUAL subjects.id
                            */

                            'subject_id' =>
                                $standardSubject->subject_id,

                            'subject_name' =>
                                $standardSubject->subject_name,

                            'max_marks' =>
                                $maxMarks,

                            'passing_marks' =>
                                $passingMarks,

                            'display_order' =>
                                $displayOrder,
                        ]);
                    }
                }
            );


            return redirect()
                ->route(
                    'exam-masters.index'
                )
                ->with(
                    'success',
                    'Exam Saved Successfully.'
                );

        } catch (\Throwable $e) {

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Exam save failed: ' .
                    $e->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(
        ExamMaster $examMaster
    ) {

        $standards =
            Standard::where(
                'is_active',
                1
            )
            ->orderBy(
                'display_order'
            )
            ->get();


        /*
        |--------------------------------------------------------------------------
        | LOAD ALL SUBJECTS OF SELECTED STANDARD
        |--------------------------------------------------------------------------
        */

        $subjects =
            DB::table(
                'standard_wise_subjects as sws'
            )
            ->join(
                'subjects as s',
                's.id',
                '=',
                'sws.subject_id'
            )
            ->leftJoin(
                'exam_master_subjects as ems',
                function ($join) use (
                    $examMaster
                ) {

                    $join->on(
                        'ems.subject_id',
                        '=',
                        's.id'
                    )
                    ->where(
                        'ems.exam_master_id',
                        '=',
                        $examMaster->id
                    )
                    ->where(
                        'ems.standard_id',
                        '=',
                        $examMaster->standard_id
                    );
                }
            )
            ->where(
                'sws.standard_id',
                $examMaster->standard_id
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

                /*
                | Mapping reference only
                */

                'sws.id as standard_wise_subject_id',

                /*
                | ACTUAL subject master ID
                */

                's.id as subject_id',

                's.subject_name',

                's.subject_code',

                's.short_name',

                'sws.sort_order',

                'sws.is_optional',

                /*
                | Existing max marks
                */

                DB::raw(
                    'COALESCE(
                        ems.max_marks,
                        40
                    ) as max_marks'
                ),

                /*
                | Existing passing marks.
                | If no exam configuration exists,
                | calculate according to Standard.
                */

                DB::raw(
                    '
                    CASE
                        WHEN ems.id IS NOT NULL
                            THEN ems.passing_marks

                        WHEN sws.standard_id IN (9,10)
                            THEN CEIL(
                                40 * 0.35
                            )

                        ELSE CEIL(
                                40 * 0.40
                            )
                    END AS passing_marks
                    '
                ),

                /*
                | Existing display order
                */

                DB::raw(
                    '
                    COALESCE(
                        ems.display_order,
                        sws.sort_order,
                        0
                    ) AS display_order
                    '
                ),

                /*
                | Existing exam configuration
                */

                DB::raw(
                    '
                    CASE
                        WHEN ems.id IS NULL
                            THEN 0
                        ELSE 1
                    END AS configured
                    '
                ),

            ])
            ->orderBy(
                'sws.sort_order'
            )
            ->orderBy(
                's.id'
            )
            ->get();


        return view(
            'exam-masters.edit',
            compact(
                'examMaster',
                'standards',
                'subjects'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    |
    | All active subjects for the Standard are rebuilt into
    | exam_master_subjects.
    |
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        $id
    ) {

        $request->validate([

            'exam_name' => [
                'required',
                'string',
                'max:100',
            ],

            'standard_id' => [
                'required',
                'integer',
                'exists:standards,id',
            ],

            'display_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'subjects' => [
                'required',
                'array',
                'min:1',
            ],

        ]);


        $examMaster =
            ExamMaster::findOrFail(
                $id
            );


        $standardId =
            (int)$request->standard_id;


        $examName =
            strtoupper(
                trim(
                    $request->exam_name
                )
            );


        /*
        |--------------------------------------------------------------------------
        | DUPLICATE CHECK
        |--------------------------------------------------------------------------
        */

        $duplicate =
            ExamMaster::where(
                'standard_id',
                $standardId
            )
            ->where(
                'exam_name',
                $examName
            )
            ->where(
                'id',
                '!=',
                $examMaster->id
            )
            ->exists();


        if ($duplicate) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Another Exam Master already exists for this Standard.'
                );
        }


        try {

            DB::transaction(
                function () use (
                    $request,
                    $examMaster,
                    $standardId,
                    $examName
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | UPDATE EXAM MASTER
                    |--------------------------------------------------------------------------
                    */

                    $examMaster->update([

                        'exam_name' =>
                            $examName,

                        'standard_id' =>
                            $standardId,

                        'display_order' =>
                            $request->filled(
                                'display_order'
                            )
                                ? (int)
                                    $request->display_order
                                : $examMaster->display_order,

                        'is_active' =>
                            $request->boolean(
                                'is_active'
                            ),

                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | LOAD ALL ACTIVE SUBJECTS
                    |--------------------------------------------------------------------------
                    */

                    $standardSubjects =
                        $this->getStandardSubjects(
                            $standardId
                        );


                    if (
                        $standardSubjects->isEmpty()
                    ) {

                        throw new \RuntimeException(
                            'No active subjects are mapped to the selected Standard.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | REMOVE OLD EXAM SUBJECT CONFIGURATION
                    |--------------------------------------------------------------------------
                    */

                    ExamMasterSubject::where(
                        'exam_master_id',
                        $examMaster->id
                    )
                    ->delete();


                    /*
                    |--------------------------------------------------------------------------
                    | SAVE ALL CURRENT STANDARD SUBJECTS
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        $standardSubjects as
                        $standardSubject
                    ) {

                        $subjectConfig =
                            $request->input(
                                'subjects.' .
                                $standardSubject->subject_id,
                                []
                            );


                        $maxMarks =
                            isset(
                                $subjectConfig['max_marks']
                            )
                                ? (float)
                                    $subjectConfig['max_marks']
                                : 40.0;


                        if (
                            $maxMarks < 0
                        ) {
                            $maxMarks = 0;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | PASSING MARKS
                        |--------------------------------------------------------------------------
                        */

                        $passingMarks =
                            $this->calculatePassingMarks(
                                $maxMarks,
                                $standardId
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | DISPLAY ORDER
                        |--------------------------------------------------------------------------
                        */

                        $displayOrder =
                            isset(
                                $subjectConfig[
                                    'display_order'
                                ]
                            )
                                ? (int)
                                    $subjectConfig[
                                        'display_order'
                                    ]
                                : (
                                    (int)
                                    (
                                        $standardSubject->sort_order
                                        ?? 0
                                    )
                                );


                        /*
                        |--------------------------------------------------------------------------
                        | CREATE CONFIGURATION
                        |--------------------------------------------------------------------------
                        */

                        ExamMasterSubject::create([

                            'exam_master_id' =>
                                $examMaster->id,

                            'standard_id' =>
                                $standardId,

                            'subject_id' =>
                                $standardSubject->subject_id,

                            'subject_name' =>
                                $standardSubject->subject_name,

                            'max_marks' =>
                                $maxMarks,

                            'passing_marks' =>
                                $passingMarks,

                            'display_order' =>
                                $displayOrder,

                        ]);
                    }
                }
            );


            return redirect()
                ->route(
                    'exam-masters.index'
                )
                ->with(
                    'success',
                    'Exam Updated Successfully.'
                );

        } catch (\Throwable $e) {

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Exam update failed: ' .
                    $e->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | LOAD SUBJECTS
    |--------------------------------------------------------------------------
    |
    | AJAX endpoint.
    |
    | Returns ACTUAL subjects.id.
    |
    |--------------------------------------------------------------------------
    */

    public function loadSubjects(
        $standardId
    ) {

        $standardId =
            (int)$standardId;


        $subjects =
            DB::table(
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
                'sws.is_active',
                1
            )
            ->where(
                's.is_active',
                1
            )
            ->orderBy(
                'sws.sort_order'
            )
            ->orderBy(
                's.id'
            )
            ->select([

                /*
                | ACTUAL subjects.id
                */

                's.id as id',

                's.id as subject_id',

                's.subject_name',

                's.subject_code',

                's.short_name',

                /*
                | Standard mapping ID
                */

                'sws.id as standard_wise_subject_id',

                'sws.sort_order',

                'sws.is_optional',

            ])
            ->get();


        return response()->json(
            $subjects
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE EXAM MASTER
    |--------------------------------------------------------------------------
    */

    public function destroy(
        $id
    ) {

        try {

            DB::transaction(
                function () use ($id) {

                    /*
                    |--------------------------------------------------------------------------
                    | RESULT DETAILS
                    |--------------------------------------------------------------------------
                    */

                    DB::table(
                        'student_result_details'
                    )
                    ->whereIn(
                        'student_result_id',
                        function ($query) use (
                            $id
                        ) {

                            $query
                                ->select(
                                    'id'
                                )
                                ->from(
                                    'student_results'
                                )
                                ->where(
                                    'exam_master_id',
                                    $id
                                );
                        }
                    )
                    ->delete();


                    /*
                    |--------------------------------------------------------------------------
                    | RESULTS
                    |--------------------------------------------------------------------------
                    */

                    DB::table(
                        'student_results'
                    )
                    ->where(
                        'exam_master_id',
                        $id
                    )
                    ->delete();


                    /*
                    |--------------------------------------------------------------------------
                    | STUDENT MARKS
                    |--------------------------------------------------------------------------
                    */

                    DB::table(
                        'student_marks'
                    )
                    ->where(
                        'exam_master_id',
                        $id
                    )
                    ->delete();


                    /*
                    |--------------------------------------------------------------------------
                    | LEGACY MARKS
                    |--------------------------------------------------------------------------
                    */

                    if (
                        DB::getSchemaBuilder()
                            ->hasTable(
                                'marks_entries'
                            )
                    ) {

                        DB::table(
                            'marks_entries'
                        )
                        ->where(
                            'exam_master_id',
                            $id
                        )
                        ->delete();
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | TEACHER MARK STATUS
                    |--------------------------------------------------------------------------
                    */

                    DB::table(
                        'teacher_marks_status'
                    )
                    ->where(
                        'exam_master_id',
                        $id
                    )
                    ->delete();


                    /*
                    |--------------------------------------------------------------------------
                    | EXAM SUBJECTS
                    |--------------------------------------------------------------------------
                    */

                    if (
                        DB::getSchemaBuilder()
                            ->hasTable(
                                'exam_subjects'
                            )
                    ) {

                        ExamSubject::where(
                            'exam_master_id',
                            $id
                        )
                        ->delete();
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | EXAM MASTER SUBJECTS
                    |--------------------------------------------------------------------------
                    */

                    ExamMasterSubject::where(
                        'exam_master_id',
                        $id
                    )
                    ->delete();


                    /*
                    |--------------------------------------------------------------------------
                    | EXAM MASTER
                    |--------------------------------------------------------------------------
                    */

                    ExamMaster::where(
                        'id',
                        $id
                    )
                    ->delete();
                }
            );


            return redirect()
                ->route(
                    'exam-masters.index'
                )
                ->with(
                    'success',
                    'Exam and all related data deleted successfully.'
                );

        } catch (\Throwable $e) {

            report($e);

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Delete failed: ' .
                    $e->getMessage()
                );
        }
    }
}