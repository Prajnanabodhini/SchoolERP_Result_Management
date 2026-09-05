<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\ExamMaster;
use App\Models\Standard;
use App\Models\Division;

class ExamProgressController extends Controller
{
    private function isAdministrator(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasRole')) {
            if (
                $user->hasRole('Administrator') ||
                $user->hasRole('admin')
            ) {
                return true;
            }
        }

        $role = strtolower(
            trim(
                (string) ($user->role ?? '')
            )
        );

        return in_array(
            $role,
            [
                'administrator',
                'admin',
            ],
            true
        );
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        $userId = (int) $user->id;

        $isAdministrator =
            $this->isAdministrator();

        /*
        |--------------------------------------------------------------------------
        | FILTERS
        |--------------------------------------------------------------------------
        */

        $examId =
            $request->input(
                'exam_master_id',
                ''
            );

        $standardId =
            $request->input(
                'standard_id',
                ''
            );

        $divisionId =
            $request->input(
                'division_id',
                ''
            );


        /*
        |--------------------------------------------------------------------------
        | FORM DATA
        |--------------------------------------------------------------------------
        */

        $exams =
            ExamMaster::query()
                ->where(
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

        $standards =
            Standard::query()
                ->where(
                    'is_active',
                    1
                )
                ->orderBy(
                    'display_order'
                )
                ->get();

        $divisions =
            Division::query()
                ->where(
                    'is_active',
                    1
                )
                ->orderBy(
                    'division_name'
                )
                ->get();


        /*
        |--------------------------------------------------------------------------
        | MAIN QUERY
        |--------------------------------------------------------------------------
        */

        $query =
            DB::table(
                'teacher_marks_status as tms'
            )

            ->join(
                'teacher_subject_allocations as tsa',
                'tsa.id',
                '=',
                'tms.teacher_subject_allocation_id'
            )

            /*
            |--------------------------------------------------------------------------
            | STANDARD-WISE SUBJECT MAPPING
            |--------------------------------------------------------------------------
            |
            | Accept both:
            |
            | sws.subject_id = tsa.subject_id
            |
            | and old records where tsa.subject_id may contain
            | standard_wise_subjects.id.
            |
            */

            ->join(
                'standard_wise_subjects as sws',
                function ($join) {

                    $join->on(
                        'sws.standard_id',
                        '=',
                        'tms.standard_id'
                    )

                    ->where(
                        'sws.is_active',
                        1
                    )

                    ->where(
                        function ($q) {

                            $q->whereColumn(
                                'sws.subject_id',
                                '=',
                                'tsa.subject_id'
                            )

                            ->orWhereColumn(
                                'sws.id',
                                '=',
                                'tsa.subject_id'
                            );
                        }
                    );
                }
            )

            /*
            |--------------------------------------------------------------------------
            | SUBJECT
            |--------------------------------------------------------------------------
            */

            ->join(
                'subjects as subj',
                'subj.id',
                '=',
                DB::raw(
                    'COALESCE(sws.subject_id, tsa.subject_id)'
                )
            )

            /*
            |--------------------------------------------------------------------------
            | RELATED TABLES
            |--------------------------------------------------------------------------
            */

            ->leftJoin(
                'exam_masters as em',
                'em.id',
                '=',
                'tms.exam_master_id'
            )

            ->leftJoin(
                'standards as st',
                'st.id',
                '=',
                'tms.standard_id'
            )

            ->leftJoin(
                'divisions as d',
                'd.id',
                '=',
                'tms.division_id'
            )

            ->leftJoin(
                'users as u',
                'u.id',
                '=',
                'tms.teacher_id'
            )

            ->leftJoin(
                'teacher_class_allocations as tca',
                'tca.id',
                '=',
                'tsa.teacher_class_allocation_id'
            )

            ->whereNotNull(
                'tms.teacher_subject_allocation_id'
            );


        /*
        |--------------------------------------------------------------------------
        | FILTERS
        |--------------------------------------------------------------------------
        */

        if ($examId !== '') {
            $query->where(
                'tms.exam_master_id',
                (int) $examId
            );
        }

        if ($standardId !== '') {
            $query->where(
                'tms.standard_id',
                (int) $standardId
            );
        }

        if ($divisionId !== '') {
            $query->where(
                'tms.division_id',
                (int) $divisionId
            );
        }


        /*
        |--------------------------------------------------------------------------
        | TEACHER FILTER
        |--------------------------------------------------------------------------
        */

        if (!$isAdministrator) {
            $query->where(
                'tca.user_id',
                $userId
            );
        }


        /*
        |--------------------------------------------------------------------------
        | EFFECTIVE STATUS
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | teacher_marks_status may still say PENDING when existing
        | student marks belong to an older TSA.
        |
        | Therefore COMPLETED is determined by:
        |
        | Academic Year
        | Section
        | Standard
        | Division
        | Exam
        | Actual Subject
        |
        | teacher_subject_allocation_id is intentionally NOT used
        | while locating existing student marks.
        |
        */

        $effectiveStatusSql = "
            CASE

                WHEN EXISTS (

                    SELECT 1

                    FROM student_marks sm

                    WHERE sm.academic_year_id =
                        tms.academic_year_id

                    AND sm.section_id =
                        tca.section_id

                    AND sm.standard_id =
                        tms.standard_id

                    AND sm.division_id =
                        tms.division_id

                    AND sm.exam_master_id =
                        tms.exam_master_id

                    AND sm.subject_id =
                        COALESCE(
                            sws.subject_id,
                            tsa.subject_id
                        )

                )

                THEN 'COMPLETED'

                ELSE UPPER(
                    TRIM(
                        COALESCE(
                            tms.status,
                            ''
                        )
                    )
                )

            END
        ";


        /*
        |--------------------------------------------------------------------------
        | COUNTS
        |--------------------------------------------------------------------------
        */

        $total =
            (clone $query)
                ->count(
                    'tms.id'
                );


        $completed =
            (clone $query)
                ->whereRaw(
                    "
                    (
                        {$effectiveStatusSql}
                    ) = 'COMPLETED'
                    "
                )
                ->count(
                    'tms.id'
                );


        $pending =
            (clone $query)
                ->whereRaw(
                    "
                    (
                        {$effectiveStatusSql}
                    ) = 'PENDING'
                    "
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
                    'tms.id',

                    'tms.teacher_subject_allocation_id',

                    'tms.exam_master_id',

                    'tms.standard_id',

                    'tms.division_id',

                    'tms.teacher_id',

                    'tms.academic_year_id',

                    'tms.status',

                    'em.exam_name',

                    'em.display_order as exam_display_order',

                    'st.standard_name',

                    'st.display_order as standard_display_order',

                    'd.division_name',

                    'd.display_order as division_display_order',

                    'u.name as teacher_name',

                    'subj.subject_name',

                    'subj.subject_code',

                    'subj.id as resolved_subject_id',
                ])

                /*
                |--------------------------------------------------------------------------
                | EFFECTIVE STATUS
                |--------------------------------------------------------------------------
                */

                ->selectRaw(
                    "
                    {$effectiveStatusSql}
                    AS effective_status
                    "
                )

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

                ->paginate(50)

                ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | LOAD MARK COUNTS
        |--------------------------------------------------------------------------
        |
        | Do NOT rely only on teacher_subject_allocation_id.
        |
        | Existing marks may have an older TSA.
        |
        | Therefore count marks using:
        |
        | Academic Year
        | Section
        | Standard
        | Division
        | Exam
        | Subject
        |
        */

        foreach ($statuses as $status) {

            $status->status =
                strtoupper(
                    trim(
                        (string) (
                            $status->effective_status
                            ?? $status->status
                            ?? ''
                        )
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | MARK COUNT
            |--------------------------------------------------------------------------
            */

            $markCountQuery =
                DB::table(
                    'student_marks as sm'
                )

                ->where(
                    'sm.academic_year_id',
                    (int) $status->academic_year_id
                )

                ->where(
                    'sm.standard_id',
                    (int) $status->standard_id
                )

                ->where(
                    'sm.division_id',
                    (int) $status->division_id
                )

                ->where(
                    'sm.exam_master_id',
                    (int) $status->exam_master_id
                )

                ->where(
                    'sm.subject_id',
                    (int) $status->resolved_subject_id
                );


            /*
            |--------------------------------------------------------------------------
            | SECTION
            |--------------------------------------------------------------------------
            |
            | Find the section through the Teacher Class Allocation.
            |
            */

            $sectionId =
                DB::table(
                    'teacher_subject_allocations as tsa2'
                )

                ->join(
                    'teacher_class_allocations as tca2',
                    'tca2.id',
                    '=',
                    'tsa2.teacher_class_allocation_id'
                )

                ->where(
                    'tsa2.id',
                    (int) $status->teacher_subject_allocation_id
                )

                ->value(
                    'tca2.section_id'
                );


            if ($sectionId !== null) {
                $markCountQuery->where(
                    'sm.section_id',
                    (int) $sectionId
                );
            }


            $status->mark_count =
                (int) $markCountQuery
                    ->distinct(
                        'sm.student_id'
                    )
                    ->count(
                        'sm.student_id'
                    );
        }


        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
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