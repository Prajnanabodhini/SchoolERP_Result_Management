<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\ExamMaster;
use App\Models\ExamMasterSubject;
use App\Models\Standard;
use App\Models\AcademicYear;
use App\Models\ExamSubject;
use App\Models\Subject;

class ExamMasterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $academicYearId =
            $request->input('academic_year_id');

        $examMasters =
            ExamMaster::with([
                'standard',
                'academicYear',
            ])
            ->when(
                $academicYearId,
                function ($query) use ($academicYearId) {

                    $query->where(
                        'academic_year_id',
                        $academicYearId
                    );
                }
            )
            ->orderByDesc('id')
            ->get();

        $academicYears =
            AcademicYear::where(
                'is_active',
                1
            )
            ->orderByDesc('id')
            ->get();

        return view(
            'exam-masters.index',
            compact(
                'examMasters',
                'academicYears',
                'academicYearId'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PASSING PERCENTAGE
    |--------------------------------------------------------------------------
    */

    private function getPassingPercentage(
        $standardId
    ): float {
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
    | LOAD STANDARD SUBJECTS
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

            's.id as subject_id',

            's.subject_name',

            's.subject_code',

            's.short_name',

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
        $academicYears =
            AcademicYear::where(
                'is_active',
                1
            )
            ->orderByDesc('id')
            ->get();

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
                'academicYears',
                'standards',
                'nextDisplayOrder'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ) {
        $request->validate([

            'academic_year_id' => [
                'required',
                'integer',
                'exists:academic_years,id',
            ],

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

        ], [

            'academic_year_id.required' =>
                'Please select Academic Year.',

            'exam_name.required' =>
                'Exam Name is required.',

            'standard_id.required' =>
                'Please select Standard.',

            'subjects.required' =>
                'At least one subject configuration is required.',
        ]);

        $academicYearId =
            (int) $request->academic_year_id;

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
        | DUPLICATE EXAM
        |--------------------------------------------------------------------------
        |
        | Same Exam Name is allowed in different Academic Years.
        |
        */

        $exists =
            ExamMaster::where(
                'academic_year_id',
                $academicYearId
            )
            ->where(
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
                    'Exam Master already exists for this Academic Year and Standard.'
                );
        }


        try {

            DB::transaction(
                function () use (
                    $request,
                    $academicYearId,
                    $examName,
                    $standardId
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | CREATE EXAM
                    |--------------------------------------------------------------------------
                    */

                    $examMaster =
                        ExamMaster::create([

                            'academic_year_id' =>
                                $academicYearId,

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
                    | AUTHORITATIVE SUBJECTS
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
                    | SAVE SUBJECT CONFIG
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

                        $passingMarks =
                            $this->calculatePassingMarks(
                                $maxMarks,
                                $standardId
                            );

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
                    'Exam Saved Successfully.'
                );

        } catch (\Throwable $e) {

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Exam save failed: '
                    . $e->getMessage()
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
        $academicYears =
            AcademicYear::where(
                'is_active',
                1
            )
            ->orderByDesc('id')
            ->get();

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
        | LOAD SUBJECTS
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

                'sws.id as standard_wise_subject_id',

                's.id as subject_id',

                's.subject_name',

                's.subject_code',

                's.short_name',

                'sws.sort_order',

                'sws.is_optional',

                DB::raw(
                    'COALESCE(
                        ems.max_marks,
                        40
                    ) as max_marks'
                ),

                DB::raw(
                    '
                    CASE
                        WHEN ems.id IS NOT NULL
                            THEN ems.passing_marks

                        WHEN sws.standard_id IN (9,10)
                            THEN CEIL(40 * 0.35)

                        ELSE CEIL(40 * 0.40)
                    END AS passing_marks
                    '
                ),

                DB::raw(
                    '
                    COALESCE(
                        ems.display_order,
                        sws.sort_order,
                        0
                    ) AS display_order
                    '
                ),

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
                'academicYears',
                'standards',
                'subjects'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        $id
    ) {
        $request->validate([

            'academic_year_id' => [
                'required',
                'integer',
                'exists:academic_years,id',
            ],

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

        ], [

            'academic_year_id.required' =>
                'Please select Academic Year.',

            'exam_name.required' =>
                'Exam Name is required.',

            'standard_id.required' =>
                'Please select Standard.',
        ]);

        $examMaster =
            ExamMaster::findOrFail(
                $id
            );

        $academicYearId =
            (int) $request->academic_year_id;

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
        | DUPLICATE CHECK
        |--------------------------------------------------------------------------
        */

        $duplicate =
            ExamMaster::where(
                'academic_year_id',
                $academicYearId
            )
            ->where(
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
                    'Another Exam Master already exists for this Academic Year and Standard.'
                );
        }


        try {

            DB::transaction(
                function () use (
                    $request,
                    $examMaster,
                    $academicYearId,
                    $standardId,
                    $examName
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | UPDATE EXAM
                    |--------------------------------------------------------------------------
                    */

                    $examMaster->update([

                        'academic_year_id' =>
                            $academicYearId,

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
                    | STANDARD SUBJECTS
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
                    | REBUILD EXAM SUBJECTS
                    |--------------------------------------------------------------------------
                    */

                    ExamMasterSubject::where(
                        'exam_master_id',
                        $examMaster->id
                    )
                    ->delete();


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

                        $passingMarks =
                            $this->calculatePassingMarks(
                                $maxMarks,
                                $standardId
                            );

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
                    'Exam update failed: '
                    . $e->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | LOAD SUBJECTS
    |--------------------------------------------------------------------------
    */

    public function loadSubjects(
        $standardId
    ) {
        $standardId =
            (int) $standardId;

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

                's.id as id',

                's.id as subject_id',

                's.subject_name',

                's.subject_code',

                's.short_name',

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
    | DESTROY
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
                    | TEACHER STATUS
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
                    'Delete failed: '
                    . $e->getMessage()
                );
        }
    }
}