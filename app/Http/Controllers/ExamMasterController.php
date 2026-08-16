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
    */

    private function getPassingPercentage($standardId)
    {
        return in_array((int) $standardId, [9, 10])
            ? 35
            : 40;
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE ACTUAL SUBJECT
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | Form may send:
    |
    |     standard_wise_subjects.id
    |
    | OR
    |
    |     subjects.id
    |
    | We always convert it to:
    |
    |     subjects.id
    |
    |--------------------------------------------------------------------------
    */

    private function resolveSubject(
        $formSubjectId,
        $standardId
    ) {
        if (empty($formSubjectId)) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | FIRST: Treat submitted ID as StandardWiseSubject.id
        |--------------------------------------------------------------------------
        */

        $sws = StandardWiseSubject::where(
            'id',
            $formSubjectId
        )
            ->where(
                'standard_id',
                $standardId
            )
            ->first();


        /*
        |--------------------------------------------------------------------------
        | If found, use its actual subject_id
        |--------------------------------------------------------------------------
        */

        if ($sws && !empty($sws->subject_id)) {

            $subject = Subject::where(
                'id',
                $sws->subject_id
            )
                ->where(
                    'is_active',
                    1
                )
                ->first();

            if ($subject) {
                return [
                    'sws'     => $sws,
                    'subject' => $subject,
                ];
            }
        }


        /*
        |--------------------------------------------------------------------------
        | SECOND: Submitted ID may already be subjects.id
        |--------------------------------------------------------------------------
        */

        $subject = Subject::where(
            'id',
            $formSubjectId
        )
            ->where(
                'is_active',
                1
            )
            ->first();


        if (!$subject) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Find StandardWiseSubject mapping
        |--------------------------------------------------------------------------
        */

        $sws = StandardWiseSubject::where(
            'standard_id',
            $standardId
        )
            ->where(
                'subject_id',
                $subject->id
            )
            ->first();


        if (!$sws) {
            return null;
        }


        return [
            'sws'     => $sws,
            'subject' => $subject,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $standards = Standard::where(
            'is_active',
            1
        )
            ->orderBy(
                'display_order'
            )
            ->get();


        $nextDisplayOrder =
            (ExamMaster::max('display_order') ?? 0) + 1;


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
    | STORE EXAM MASTER
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([
            'exam_name' =>
                'required|max:100',

            'standard_id' =>
                'required|exists:standards,id',
        ]);


        $examName =
            strtoupper(
                trim(
                    $request->exam_name
                )
            );


        $exists =
            ExamMaster::where(
                'standard_id',
                $request->standard_id
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


        DB::transaction(
            function () use (
                $request,
                $examName
            ) {

                $examMaster =
                    ExamMaster::create([

                        'exam_name' =>
                            $examName,

                        'standard_id' =>
                            $request->standard_id,

                        'max_marks' =>
                            0,

                        'passing_marks' =>
                            0,

                        'display_order' =>
                            $request->display_order ?? 0,

                        'is_active' =>
                            $request->has('is_active'),
                    ]);


                foreach (
                    $request->input(
                        'subjects',
                        []
                    ) as $row
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Ignore unselected subjects
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !isset($row['selected']) ||
                        $row['selected'] != 1
                    ) {
                        continue;
                    }


                    if (
                        empty(
                            $row['subject_id']
                        )
                    ) {
                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Resolve subject
                    |--------------------------------------------------------------------------
                    */

                    $resolved =
                        $this->resolveSubject(
                            $row['subject_id'],
                            $request->standard_id
                        );


                    if (!$resolved) {
                        continue;
                    }


                    $subject =
                        $resolved['subject'];


                    /*
                    |--------------------------------------------------------------------------
                    | Marks
                    |--------------------------------------------------------------------------
                    */

                    $maxMarks =
                        (float) (
                            $row['max_marks']
                            ?? 0
                        );


                    $percentage =
                        $this->getPassingPercentage(
                            $request->standard_id
                        );


                    $passingMarks =
                        (int) ceil(
                            $maxMarks *
                            ($percentage / 100)
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | SAVE ACTUAL subjects.id
                    |--------------------------------------------------------------------------
                    */

                    ExamMasterSubject::create([
    'exam_master_id' => $examMaster->id,
    'standard_id'    => $request->standard_id,
    'subject_id'     => $subject->id,
    'subject_name'   => $subject->subject_name,
    'max_marks'      => $maxMarks,
    'passing_marks'  => $passingMarks,
    'display_order'  => $row['display_order']
        ?? $resolved['sws']->sort_order
        ?? 0,
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
                'Exam Saved Successfully'
            );
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
        | Load current Subject Master subjects
        |--------------------------------------------------------------------------
        |
        | standard_wise_subjects is only used as the mapping table.
        |
        | Actual subject:
        |
        | sws.subject_id -> subjects.id
        |
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

                ->select(

                    /*
                    | Mapping ID
                    */

                    'sws.id as standard_wise_subject_id',

                    /*
                    | Actual Subject Master ID
                    */

                    's.id as subject_id',

                    /*
                    | Current Subject Master Name
                    */

                    's.subject_name',

                    'sws.sort_order',

                    'sws.is_optional',

                    DB::raw(
                        'COALESCE(ems.max_marks,40) as max_marks'
                    ),

                    DB::raw(
                        'COALESCE(ems.passing_marks,0) as passing_marks'
                    ),

                    DB::raw(
                        'COALESCE(ems.display_order,sws.sort_order,0) as display_order'
                    ),

                    DB::raw(
                        'CASE WHEN ems.id IS NULL THEN 0 ELSE 1 END as selected'
                    )
                )

                ->orderBy(
                    'sws.sort_order'
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
    */

    public function update(
        Request $request,
        $id
    ) {

        $request->validate([
            'exam_name' =>
                'required|max:100',

            'standard_id' =>
                'required|exists:standards,id',
        ]);


        $examMaster =
            ExamMaster::findOrFail(
                $id
            );


        DB::transaction(
            function () use (
                $request,
                $examMaster
            ) {

                $examMaster->update([

                    'exam_name' =>
                        strtoupper(
                            trim(
                                $request->exam_name
                            )
                        ),

                    'standard_id' =>
                        $request->standard_id,

                    'display_order' =>
                        $request->display_order
                        ?: $examMaster->display_order,

                    'is_active' =>
                        $request->has('is_active'),
                ]);


                /*
                |--------------------------------------------------------------------------
                | Remove old configuration
                |--------------------------------------------------------------------------
                |
                | We are only removing the configuration belonging
                | to this Exam Master.
                |
                |--------------------------------------------------------------------------
                */

                ExamMasterSubject::where(
                    'exam_master_id',
                    $examMaster->id
                )->delete();


                /*
                |--------------------------------------------------------------------------
                | Rebuild configuration
                |--------------------------------------------------------------------------
                */

                foreach (
                    $request->input(
                        'subjects',
                        []
                    ) as $row
                ) {

                    if (
                        !isset($row['selected']) ||
                        $row['selected'] != 1
                    ) {
                        continue;
                    }


                    if (
                        empty(
                            $row['subject_id']
                        )
                    ) {
                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Resolve mapping -> actual subject
                    |--------------------------------------------------------------------------
                    */

                    $resolved =
                        $this->resolveSubject(
                            $row['subject_id'],
                            $request->standard_id
                        );


                    if (!$resolved) {
                        continue;
                    }


                    $subject =
                        $resolved['subject'];


                    /*
                    |--------------------------------------------------------------------------
                    | Marks
                    |--------------------------------------------------------------------------
                    */

                    $maxMarks =
                        (float) (
                            $row['max_marks']
                            ?? 0
                        );


                    $percentage =
                        $this->getPassingPercentage(
                            $request->standard_id
                        );


                    $passingMarks =
                        (int) ceil(
                            $maxMarks *
                            ($percentage / 100)
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Save actual Subject Master ID
                    |--------------------------------------------------------------------------
                    */

                    ExamMasterSubject::create([
    'exam_master_id' => $examMaster->id,
    'standard_id'    => $request->standard_id,
    'subject_id'     => $subject->id,
    'subject_name'   => $subject->subject_name,
    'max_marks'      => $maxMarks,
    'passing_marks'  => $passingMarks,
    'display_order'  => $row['display_order']
        ?? $resolved['sws']->sort_order
        ?? 0,
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
                'Exam Updated Successfully'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | LOAD SUBJECTS
    |--------------------------------------------------------------------------
    */

    public function loadSubjects($standardId)
{
    $subjects = StandardWiseSubject::where(
            'standard_id',
            $standardId
        )
        ->where('is_active', 1)
        ->orderBy('sort_order')
        ->get([
            'id',
            'subject_name',
            'sort_order',
            'is_optional'
        ]);

    return response()->json($subjects);
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
                                    ->select('id')
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
                    | MARKS
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


                    DB::table(
                        'marks_entries'
                    )
                        ->where(
                            'exam_master_id',
                            $id
                        )
                        ->delete();


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

                    ExamSubject::where(
                        'exam_master_id',
                        $id
                    )->delete();


                    /*
                    |--------------------------------------------------------------------------
                    | EXAM MASTER SUBJECT CONFIGURATION
                    |--------------------------------------------------------------------------
                    */

                    ExamMasterSubject::where(
                        'exam_master_id',
                        $id
                    )->delete();


                    /*
                    |--------------------------------------------------------------------------
                    | EXAM MASTER
                    |--------------------------------------------------------------------------
                    */

                    ExamMaster::where(
                        'id',
                        $id
                    )->delete();
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

        } catch (
            \Exception $e
        ) {

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