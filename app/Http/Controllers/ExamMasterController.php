<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\ExamMaster;
use App\Models\ExamMasterSubject;
use App\Models\Standard;
use App\Models\AcademicYear;
use App\Models\ExamSubject;

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
                function ($query) use (
                    $academicYearId
                ) {

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
    |
    | 35% passing:
    |
    | Nursery
    | JrKg
    | SrKg
    | 9th
    | 10th
    | 11th
    | 12th
    |
    | 40% passing:
    |
    | All other standards.
    |
    | For 9th-12th we use known Standard IDs.
    |
    | For Nursery / JrKg / SrKg we use the Standard Name because
    | their database IDs should not be assumed.
    |--------------------------------------------------------------------------
    */

    private function getPassingPercentage(
        $standardId
    ): float {

        $standardId =
            (int) $standardId;


        /*
        |--------------------------------------------------------------------------
        | 9TH / 10TH / 11TH / 12TH
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $standardId,
                [9, 10, 11, 12],
                true
            )
        ) {

            return 35.0;
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD STANDARD NAME
        |--------------------------------------------------------------------------
        */

        $standardName =
            DB::table(
                'standards'
            )
            ->where(
                'id',
                $standardId
            )
            ->value(
                'standard_name'
            );


        /*
        |--------------------------------------------------------------------------
        | NORMALIZE NAME
        |--------------------------------------------------------------------------
        */

        $normalizedName =
            preg_replace(
                '/[^A-Z0-9]+/',
                '',
                strtoupper(
                    trim(
                        (string) $standardName
                    )
                )
            );


        /*
        |--------------------------------------------------------------------------
        | NURSERY
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $normalizedName,
                [
                    'NURSERY',
                    'NUR',
                ],
                true
            )
        ) {

            return 35.0;
        }


        /*
        |--------------------------------------------------------------------------
        | JRKG
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $normalizedName,
                [
                    'JRKG',
                    'JUNIORKG',
                    'JUNIORKINDERGARTEN',
                ],
                true
            )
        ) {

            return 35.0;
        }


        /*
        |--------------------------------------------------------------------------
        | SRKG
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $normalizedName,
                [
                    'SRKG',
                    'SENIORKG',
                    'SENIORKINDERGARTEN',
                ],
                true
            )
        ) {

            return 35.0;
        }


        /*
        |--------------------------------------------------------------------------
        | DEFAULT
        |--------------------------------------------------------------------------
        */

        return 40.0;
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


        if (
            $maxMarks <= 0
        ) {

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
    | GET STANDARD NAME
    |--------------------------------------------------------------------------
    */

    private function getStandardName(
        $standardId
    ): string {

        return trim(
            (string) (
                DB::table(
                    'standards'
                )
                ->where(
                    'id',
                    (int) $standardId
                )
                ->value(
                    'standard_name'
                )
                ?? ''
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GET STANDARD SUBJECTS
    |--------------------------------------------------------------------------
    |
    | Authoritative subject list for the selected Standard.
    |
    | is_optional is included because 11th / 12th may have optional
    | subjects such as Biology, Geography, Mathematics, etc.
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
                    | PASSING PERCENTAGE
                    |--------------------------------------------------------------------------
                    |
                    | Calculated once for this standard.
                    |--------------------------------------------------------------------------
                    */

                    $passingPercentage =
                        $this->getPassingPercentage(
                            $standardId
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | SAVE SUBJECT CONFIGURATION
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        $standardSubjects as
                        $standardSubject
                    ) {

                        $subjectConfig =
                            $request->input(
                                'subjects.'
                                .
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

                            $maxMarks =
                                0;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | PASSING MARKS
                        |--------------------------------------------------------------------------
                        */

                        $passingMarks =
                            $maxMarks > 0
                                ? (int) ceil(
                                    $maxMarks *
                                    (
                                        $passingPercentage
                                        /
                                        100
                                    )
                                )
                                : 0;


                        $displayOrder =
                            isset(
                                $subjectConfig['display_order']
                            )
                                ? (int)
                                    $subjectConfig['display_order']
                                : (
                                    (int) (
                                        $standardSubject
                                            ->sort_order
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
        | LOAD STANDARD SUBJECTS
        |--------------------------------------------------------------------------
        */

        $standardSubjects =
            $this->getStandardSubjects(
                (int) $examMaster->standard_id
            );


        /*
        |--------------------------------------------------------------------------
        | LOAD EXISTING EXAM SUBJECT CONFIGURATION
        |--------------------------------------------------------------------------
        */

        $existingConfigs =
            ExamMasterSubject::query()
                ->where(
                    'exam_master_id',
                    $examMaster->id
                )
                ->where(
                    'standard_id',
                    $examMaster->standard_id
                )
                ->get()
                ->keyBy(
                    'subject_id'
                );


        /*
        |--------------------------------------------------------------------------
        | PASSING PERCENTAGE
        |--------------------------------------------------------------------------
        */

        $passingPercentage =
            $this->getPassingPercentage(
                $examMaster->standard_id
            );


        /*
        |--------------------------------------------------------------------------
        | BUILD EDIT SUBJECT DATA
        |--------------------------------------------------------------------------
        |
        | We intentionally build this collection in PHP instead of using
        | the old SQL CASE which only handled 9th and 10th.
        |--------------------------------------------------------------------------
        */

        $subjects =
            collect();


        foreach (
            $standardSubjects as
            $standardSubject
        ) {

            $existingConfig =
                $existingConfigs->get(
                    $standardSubject->subject_id
                );


            /*
            |--------------------------------------------------------------------------
            | MAX MARKS
            |--------------------------------------------------------------------------
            */

            $maxMarks =
                $existingConfig
                    ? (float)
                        $existingConfig->max_marks
                    : 40.0;


            /*
            |--------------------------------------------------------------------------
            | PASSING MARKS
            |--------------------------------------------------------------------------
            |
            | Existing configured value is preserved for existing records.
            |
            | If the subject was not previously configured, calculate using
            | the current standard's passing percentage.
            |--------------------------------------------------------------------------
            */

            if (
                $existingConfig
            ) {

                $passingMarks =
                    (int) (
                        $existingConfig->passing_marks
                        ?? 0
                    );

            } else {

                $passingMarks =
                    $maxMarks > 0
                        ? (int) ceil(
                            $maxMarks *
                            (
                                $passingPercentage
                                /
                                100
                            )
                        )
                        : 0;
            }


            /*
            |--------------------------------------------------------------------------
            | DISPLAY ORDER
            |--------------------------------------------------------------------------
            */

            $displayOrder =
                $existingConfig
                    ? (int) (
                        $existingConfig
                            ->display_order
                        ??
                        $standardSubject
                            ->sort_order
                        ??
                        0
                    )
                    : (int) (
                        $standardSubject
                            ->sort_order
                        ??
                        0
                    );


            /*
            |--------------------------------------------------------------------------
            | CONFIGURED
            |--------------------------------------------------------------------------
            */

            $configured =
                $existingConfig
                    ? 1
                    : 0;


            /*
            |--------------------------------------------------------------------------
            | BUILD OBJECT
            |--------------------------------------------------------------------------
            */

            $subjects->push(
                (object) [

                    'standard_wise_subject_id' =>
                        $standardSubject
                            ->standard_wise_subject_id,

                    'subject_id' =>
                        $standardSubject
                            ->subject_id,

                    'subject_name' =>
                        $standardSubject
                            ->subject_name,

                    'subject_code' =>
                        $standardSubject
                            ->subject_code,

                    'short_name' =>
                        $standardSubject
                            ->short_name,

                    'sort_order' =>
                        $standardSubject
                            ->sort_order,

                    /*
                    | Optional subject information
                    */

                    'is_optional' =>
                        (int) (
                            $standardSubject
                                ->is_optional
                            ?? 0
                        ),

                    'max_marks' =>
                        $maxMarks,

                    'passing_marks' =>
                        $passingMarks,

                    'display_order' =>
                        $displayOrder,

                    'configured' =>
                        $configured,
                ]
            );
        }


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
                    | PASSING PERCENTAGE
                    |--------------------------------------------------------------------------
                    */

                    $passingPercentage =
                        $this->getPassingPercentage(
                            $standardId
                        );


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
                                'subjects.'
                                .
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

                            $maxMarks =
                                0;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | PASSING MARKS
                        |--------------------------------------------------------------------------
                        */

                        $passingMarks =
                            $maxMarks > 0
                                ? (int) ceil(
                                    $maxMarks *
                                    (
                                        $passingPercentage
                                        /
                                        100
                                    )
                                )
                                : 0;


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
                                    (int) (
                                        $standardSubject
                                            ->sort_order
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
                function () use (
                    $id
                ) {

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
                    | LEGACY EXAM SUBJECTS
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