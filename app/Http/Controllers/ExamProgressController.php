<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\ExamMaster;
use App\Models\Standard;
use App\Models\Division;

class ExamProgressController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    |
    | Subject resolution supports BOTH legacy formats:
    |
    | FORMAT 1
    | teacher_marks_status.subject_id
    |     = standard_wise_subjects.id
    |
    | FORMAT 2
    | teacher_marks_status.subject_id
    |     = standard_wise_subjects.subject_id
    |
    | The STANDARD is always used while resolving the subject.
    |
    */

    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | FILTER VALUES
        |--------------------------------------------------------------------------
        */

        $examId = $request->input(
            'exam_master_id',
            ''
        );

        $standardId = $request->input(
            'standard_id',
            ''
        );

        $divisionId = $request->input(
            'division_id',
            ''
        );


        /*
        |--------------------------------------------------------------------------
        | EXAM DROPDOWN
        |--------------------------------------------------------------------------
        */

        $exams = ExamMaster::where(
            'is_active',
            1
        )
            ->orderBy(
                'display_order'
            )
            ->orderBy(
                'exam_name'
            )
            ->get();


        /*
        |--------------------------------------------------------------------------
        | STANDARD DROPDOWN
        |--------------------------------------------------------------------------
        */

        $standards = Standard::where(
            'is_active',
            1
        )
            ->orderBy(
                'display_order'
            )
            ->get();


        /*
        |--------------------------------------------------------------------------
        | DIVISION DROPDOWN
        |--------------------------------------------------------------------------
        */

        $divisions = Division::where(
            'is_active',
            1
        )
            ->orderBy(
                'display_order'
            )
            ->get();


        /*
        |--------------------------------------------------------------------------
        | BASE QUERY
        |--------------------------------------------------------------------------
        */

        $query = DB::table(
            'teacher_marks_status as tms'
        )


            /*
            |--------------------------------------------------------------------------
            | EXAM
            |--------------------------------------------------------------------------
            */

            ->leftJoin(
                'exam_masters as em',
                'em.id',
                '=',
                'tms.exam_master_id'
            )


            /*
            |--------------------------------------------------------------------------
            | STANDARD
            |--------------------------------------------------------------------------
            */

            ->leftJoin(
                'standards as st',
                'st.id',
                '=',
                'tms.standard_id'
            )


            /*
            |--------------------------------------------------------------------------
            | DIVISION
            |--------------------------------------------------------------------------
            */

            ->leftJoin(
                'divisions as d',
                'd.id',
                '=',
                'tms.division_id'
            )


            /*
            |--------------------------------------------------------------------------
            | TEACHER
            |--------------------------------------------------------------------------
            */

            ->leftJoin(
                'users as u',
                'u.id',
                '=',
                'tms.teacher_id'
            )


            /*
            |--------------------------------------------------------------------------
            | SUBJECT RESOLUTION #1
            |--------------------------------------------------------------------------
            |
            | TMS.subject_id = standard_wise_subjects.id
            |
            | Example:
            |
            | SIXTH
            | TMS 52
            | SWS id 52
            | SWS subject_id 1
            | ENGLISH
            |
            */

            ->leftJoin(
                'standard_wise_subjects as sws_by_id',
                function ($join) {

                    $join->on(
                        'sws_by_id.id',
                        '=',
                        'tms.subject_id'
                    );

                    $join->on(
                        'sws_by_id.standard_id',
                        '=',
                        'tms.standard_id'
                    );

                    $join->where(
                        'sws_by_id.is_active',
                        1
                    );
                }
            )


            /*
            |--------------------------------------------------------------------------
            | SUBJECT RESOLUTION #2
            |--------------------------------------------------------------------------
            |
            | TMS.subject_id = standard_wise_subjects.subject_id
            |
            | Example:
            |
            | SEVENTH
            | TMS 10
            | SWS subject_id 10
            | MATHEMATICS
            |
            */

            ->leftJoin(
                'standard_wise_subjects as sws_by_subject',
                function ($join) {

                    $join->on(
                        'sws_by_subject.subject_id',
                        '=',
                        'tms.subject_id'
                    );

                    $join->on(
                        'sws_by_subject.standard_id',
                        '=',
                        'tms.standard_id'
                    );

                    $join->where(
                        'sws_by_subject.is_active',
                        1
                    );
                }
            )


            /*
            |--------------------------------------------------------------------------
            | CURRENT SUBJECT FROM FORMAT #1
            |--------------------------------------------------------------------------
            */

            ->leftJoin(
                'subjects as s_by_id',
                function ($join) {

                    $join->on(
                        's_by_id.id',
                        '=',
                        'sws_by_id.subject_id'
                    );

                    $join->where(
                        's_by_id.is_active',
                        1
                    );
                }
            )


            /*
            |--------------------------------------------------------------------------
            | CURRENT SUBJECT FROM FORMAT #2
            |--------------------------------------------------------------------------
            */

            ->leftJoin(
                'subjects as s_by_subject',
                function ($join) {

                    $join->on(
                        's_by_subject.id',
                        '=',
                        'sws_by_subject.subject_id'
                    );

                    $join->where(
                        's_by_subject.is_active',
                        1
                    );
                }
            );


        /*
        |--------------------------------------------------------------------------
        | EXAM FILTER
        |--------------------------------------------------------------------------
        */

        if (
            $examId !== null &&
            $examId !== ''
        ) {

            $query->where(
                'tms.exam_master_id',
                (int) $examId
            );
        }


        /*
        |--------------------------------------------------------------------------
        | STANDARD FILTER
        |--------------------------------------------------------------------------
        */

        if (
            $standardId !== null &&
            $standardId !== ''
        ) {

            $query->where(
                'tms.standard_id',
                (int) $standardId
            );
        }


        /*
        |--------------------------------------------------------------------------
        | DIVISION FILTER
        |--------------------------------------------------------------------------
        */

        if (
            $divisionId !== null &&
            $divisionId !== ''
        ) {

            $query->where(
                'tms.division_id',
                (int) $divisionId
            );
        }


        /*
        |--------------------------------------------------------------------------
        | TOTAL COUNT
        |--------------------------------------------------------------------------
        */

        $total =
            (clone $query)
                ->count(
                    'tms.id'
                );


        /*
        |--------------------------------------------------------------------------
        | COMPLETED COUNT
        |--------------------------------------------------------------------------
        */

        $completed =
            (clone $query)
                ->whereRaw(
                    "UPPER(TRIM(tms.status)) = 'COMPLETED'"
                )
                ->count(
                    'tms.id'
                );


        /*
        |--------------------------------------------------------------------------
        | PENDING COUNT
        |--------------------------------------------------------------------------
        */

        $pending =
            (clone $query)
                ->whereRaw(
                    "UPPER(TRIM(tms.status)) = 'PENDING'"
                )
                ->count(
                    'tms.id'
                );


        /*
        |--------------------------------------------------------------------------
        | STATUS RECORDS
        |--------------------------------------------------------------------------
        */

        $statuses =
            (clone $query)
                ->select([

                    /*
                    |----------------------------------------------------------
                    | TMS
                    |----------------------------------------------------------
                    */

                    'tms.id',

                    'tms.teacher_subject_allocation_id',

                    'tms.exam_master_id',

                    'tms.standard_id',

                    'tms.division_id',

                    'tms.subject_id',

                    'tms.teacher_id',

                    'tms.academic_year_id',

                    'tms.status',


                    /*
                    |----------------------------------------------------------
                    | EXAM
                    |----------------------------------------------------------
                    */

                    'em.exam_name',

                    'em.display_order as exam_display_order',


                    /*
                    |----------------------------------------------------------
                    | STANDARD
                    |----------------------------------------------------------
                    */

                    'st.standard_name',

                    'st.display_order as standard_display_order',


                    /*
                    |----------------------------------------------------------
                    | DIVISION
                    |----------------------------------------------------------
                    */

                    'd.division_name',

                    'd.display_order as division_display_order',


                    /*
                    |----------------------------------------------------------
                    | TEACHER
                    |----------------------------------------------------------
                    */

                    'u.name as teacher_name',


                    /*
                    |----------------------------------------------------------
                    | SUBJECT
                    |----------------------------------------------------------
                    |
                    | sws_by_id has PRIORITY.
                    |
                    | If no mapping by ID, use mapping by subject_id.
                    |
                    */

                    DB::raw(
                        "COALESCE(
                            NULLIF(
                                TRIM(s_by_id.subject_name),
                                ''
                            ),
                            NULLIF(
                                TRIM(s_by_subject.subject_name),
                                ''
                            ),
                            '-'
                        ) AS subject_name"
                    ),

                ])


                /*
                |--------------------------------------------------------------------------
                | ORDER
                |--------------------------------------------------------------------------
                */

                ->orderBy(
                    'em.display_order'
                )

                ->orderBy(
                    'st.display_order'
                )

                ->orderBy(
                    'd.display_order'
                )

                ->orderBy(
                    'tms.id'
                )


                /*
                |--------------------------------------------------------------------------
                | PAGINATION
                |--------------------------------------------------------------------------
                */

                ->paginate(
                    50
                )
                ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | NORMALIZE STATUS / SUBJECT
        |--------------------------------------------------------------------------
        */

        foreach (
            $statuses as $status
        ) {

            $status->subject_name =
                trim(
                    (string) (
                        $status->subject_name
                        ?? ''
                    )
                );


            if (
                $status->subject_name === ''
            ) {

                $status->subject_name =
                    '-';
            }


            $status->status =
                strtoupper(
                    trim(
                        (string) (
                            $status->status
                            ?? ''
                        )
                    )
                );
        }


        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        |
        | Your current Blade expects:
        |
        | $statuses
        | $exams
        | $standards
        | $divisions
        | $examId
        | $standardId
        | $divisionId
        | $completed
        | $pending
        | $total
        |
        */

        return view(
            'administrator.exam-progress.index',
            compact(
                'statuses',
                'exams',
                'standards',
                'divisions',
                'examId',
                'standardId',
                'divisionId',
                'completed',
                'pending',
                'total'
            )
        );
    }
}