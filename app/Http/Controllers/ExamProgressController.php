<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\ExamMaster;
use App\Models\Standard;
use App\Models\Division;
use App\Models\TeacherSubjectAllocation;

class ExamProgressController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | ADMINISTRATOR DETECTION
    |--------------------------------------------------------------------------
    */

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

        $role =
            strtolower(
                trim(
                    (string) (
                        $user->role ?? ''
                    )
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


    /*
    |--------------------------------------------------------------------------
    | RESOLVE ACTUAL SUBJECT ID
    |--------------------------------------------------------------------------
    |
    | TeacherSubjectAllocation.subject_id can contain:
    |
    | CURRENT:
    |     subjects.id
    |
    | LEGACY:
    |     standard_wise_subjects.id
    |
    |--------------------------------------------------------------------------
    */

    private function resolveActualSubjectId(
        $storedSubjectId,
        $standardId
    ): ?int {

        if (
            $storedSubjectId === null ||
            $storedSubjectId === '' ||
            !$standardId
        ) {
            return null;
        }

        $storedSubjectId =
            (int) $storedSubjectId;

        $standardId =
            (int) $standardId;

        if (
            $storedSubjectId <= 0 ||
            $standardId <= 0
        ) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | CURRENT FORMAT
        |--------------------------------------------------------------------------
        */

        $subject =
            DB::table('subjects')
                ->where(
                    'id',
                    $storedSubjectId
                )
                ->where(
                    'is_active',
                    1
                )
                ->first();

        if ($subject) {

            $mappingExists =
                DB::table(
                    'standard_wise_subjects'
                )
                ->where(
                    'standard_id',
                    $standardId
                )
                ->where(
                    'subject_id',
                    $storedSubjectId
                )
                ->where(
                    'is_active',
                    1
                )
                ->exists();

            if ($mappingExists) {
                return $storedSubjectId;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | LEGACY FORMAT
        |--------------------------------------------------------------------------
        */

        $mapping =
            DB::table(
                'standard_wise_subjects'
            )
            ->where(
                'id',
                $storedSubjectId
            )
            ->where(
                'standard_id',
                $standardId
            )
            ->where(
                'is_active',
                1
            )
            ->first();

        if (
            $mapping &&
            !empty($mapping->subject_id)
        ) {

            $legacySubject =
                DB::table('subjects')
                    ->where(
                        'id',
                        (int) $mapping->subject_id
                    )
                    ->where(
                        'is_active',
                        1
                    )
                    ->first();

            if ($legacySubject) {
                return (int) $legacySubject->id;
            }
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE SUBJECT FROM TSA
    |--------------------------------------------------------------------------
    */

    private function resolveSubjectFromAllocation(
        $tsa
    ) {
        if (!$tsa) {
            return null;
        }

        $standardId =
            (int) (
                $tsa->standard_id ?? 0
            );

        $storedSubjectId =
            $tsa->subject_id ?? null;

        if (
            $standardId <= 0 ||
            $storedSubjectId === null
        ) {
            return null;
        }

        $actualSubjectId =
            $this->resolveActualSubjectId(
                $storedSubjectId,
                $standardId
            );

        if (!$actualSubjectId) {
            return null;
        }

        return DB::table('subjects')
            ->where(
                'id',
                $actualSubjectId
            )
            ->where(
                'is_active',
                1
            )
            ->first();
    }


    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $user =
            Auth::user();

        if (!$user) {
            abort(403);
        }

        $userId =
            (int) $user->id;

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
        | DROPDOWNS
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
        | BASE STATUS QUERY
        |--------------------------------------------------------------------------
        */

        $query =
            DB::table(
                'teacher_marks_status as tms'
            )
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
            );


        /*
        |--------------------------------------------------------------------------
        | FILTER EXAM
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
        | FILTER STANDARD
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
        | FILTER DIVISION
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
        | TEACHER SECURITY
        |--------------------------------------------------------------------------
        |
        | Teacher sees only TSA records belonging to their
        | teacher_class_allocation.user_id.
        |
        |--------------------------------------------------------------------------
        */

        if (!$isAdministrator) {

            $query->whereExists(
                function ($subQuery) use (
                    $userId
                ) {

                    $subQuery
                        ->select(
                            DB::raw('1')
                        )
                        ->from(
                            'teacher_subject_allocations as filter_tsa'
                        )
                        ->join(
                            'teacher_class_allocations as filter_tca',
                            'filter_tca.id',
                            '=',
                            'filter_tsa.teacher_class_allocation_id'
                        )
                        ->whereColumn(
                            'filter_tsa.id',
                            'tms.teacher_subject_allocation_id'
                        )
                        ->where(
                            'filter_tca.user_id',
                            $userId
                        );
                }
            );
        }


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
                    "UPPER(TRIM(tms.status)) = 'COMPLETED'"
                )
                ->count(
                    'tms.id'
                );

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
        | LOAD STATUS RECORDS
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
                ])
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
                ->paginate(
                    50
                )
                ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | LOAD TSA
        |--------------------------------------------------------------------------
        */

        $tsaIds =
            collect(
                $statuses->items()
            )
            ->pluck(
                'teacher_subject_allocation_id'
            )
            ->filter()
            ->unique()
            ->values();


        $allocations =
            collect();

        if (
            $tsaIds->isNotEmpty()
        ) {

            $allocations =
                TeacherSubjectAllocation::query()
                    ->whereIn(
                        'id',
                        $tsaIds
                    )
                    ->get([
                        'id',
                        'teacher_class_allocation_id',
                        'subject_id',
                        'exam_master_id',
                    ])
                    ->keyBy(
                        'id'
                    );
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD CLASS ALLOCATIONS
        |--------------------------------------------------------------------------
        */

        $tcaIds =
            $allocations
                ->pluck(
                    'teacher_class_allocation_id'
                )
                ->filter()
                ->unique()
                ->values();


        $classAllocations =
            collect();

        if (
            $tcaIds->isNotEmpty()
        ) {

            $classAllocations =
                DB::table(
                    'teacher_class_allocations'
                )
                ->whereIn(
                    'id',
                    $tcaIds
                )
                ->get([
                    'id',
                    'user_id',
                    'standard_id',
                    'division_id',
                    'academic_year_id',
                ])
                ->keyBy(
                    'id'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | RESOLVE SUBJECT FOR EACH ROW
        |--------------------------------------------------------------------------
        */

        foreach (
            $statuses as $status
        ) {

            $status->status =
                strtoupper(
                    trim(
                        (string)(
                            $status->status ?? ''
                        )
                    )
                );

            $status->subject_name =
                '-';

            $status->subject_code =
                '-';

            $status->resolved_subject_id =
                null;


            $tsa =
                $allocations->get(
                    $status->teacher_subject_allocation_id
                );

            if (!$tsa) {
                continue;
            }


            $tca =
                $classAllocations->get(
                    $tsa->teacher_class_allocation_id
                );

            if (!$tca) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | TEACHER SAFETY
            |--------------------------------------------------------------------------
            */

            if (
                !$isAdministrator &&
                (int) $tca->user_id !== $userId
            ) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | STANDARD FROM CLASS ALLOCATION
            |--------------------------------------------------------------------------
            */

            $tsa->standard_id =
                (int) (
                    $tca->standard_id
                    ?: $status->standard_id
                );


            /*
            |--------------------------------------------------------------------------
            | RESOLVE SUBJECT
            |--------------------------------------------------------------------------
            */

            $subject =
                $this->resolveSubjectFromAllocation(
                    $tsa
                );


            if ($subject) {

                $status->subject_name =
                    trim(
                        (string)(
                            $subject->subject_name
                            ?? ''
                        )
                    );

                $status->subject_code =
                    trim(
                        (string)(
                            $subject->subject_code
                            ?? ''
                        )
                    );

                $status->resolved_subject_id =
                    (int) $subject->id;
            }
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