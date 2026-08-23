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


    /*
    |--------------------------------------------------------------------------
    | RESOLVE ACTUAL SUBJECT ID
    |--------------------------------------------------------------------------
    |
    | CURRENT FORMAT:
    |
    |   stored ID = subjects.id
    |
    | LEGACY FORMAT:
    |
    |   stored ID = standard_wise_subjects.id
    |
    |--------------------------------------------------------------------------
    */

    private function resolveActualSubjectId(
        $storedSubjectId,
        $standardId = null
    ): ?int {

        if (
            $storedSubjectId === null ||
            $storedSubjectId === ''
        ) {
            return null;
        }

        $storedSubjectId = (int) $storedSubjectId;

        if ($storedSubjectId <= 0) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | 1. CURRENT FORMAT
        |--------------------------------------------------------------------------
        |
        | Direct subjects.id
        |
        */

        $subject = DB::table('subjects')
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
            return (int) $subject->id;
        }


        /*
        |--------------------------------------------------------------------------
        | 2. LEGACY FORMAT
        |--------------------------------------------------------------------------
        |
        | standard_wise_subjects.id
        |
        */

        if (!$standardId) {
            return null;
        }

        $standardId = (int) $standardId;

        if ($standardId <= 0) {
            return null;
        }

        $mapping = DB::table(
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

            $subject = DB::table('subjects')
                ->where(
                    'id',
                    (int) $mapping->subject_id
                )
                ->where(
                    'is_active',
                    1
                )
                ->first();

            if ($subject) {
                return (int) $subject->id;
            }
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE SUBJECT
    |--------------------------------------------------------------------------
    |
    | Priority:
    |
    | 1. teacher_marks_status.subject_id
    | 2. teacher_subject_allocations.subject_id
    | 3. exam_master_subjects.subject_id
    | 4. exam_master_subjects.subject_name
    |
    |--------------------------------------------------------------------------
    */

    private function resolveSubject(
        $statusSubjectId,
        $tsaSubjectId,
        $examMasterId,
        $standardId
    ) {

        /*
        |--------------------------------------------------------------------------
        | CANDIDATE IDS
        |--------------------------------------------------------------------------
        */

        $candidateIds = collect([
            $statusSubjectId,
            $tsaSubjectId,
        ])
            ->filter(function ($id) {

                return $id !== null
                    && $id !== ''
                    && is_numeric($id)
                    && (int) $id > 0;
            })
            ->map(function ($id) {

                return (int) $id;
            })
            ->unique()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | 1. DIRECT subjects.id
        |--------------------------------------------------------------------------
        */

        foreach ($candidateIds as $candidateId) {

            $subject = DB::table('subjects')
                ->where(
                    'id',
                    $candidateId
                )
                ->where(
                    'is_active',
                    1
                )
                ->first();

            if ($subject) {
                return $subject;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 2. LEGACY standard_wise_subjects.id
        |--------------------------------------------------------------------------
        */

        if ($standardId) {

            foreach ($candidateIds as $candidateId) {

                $mapping = DB::table(
                    'standard_wise_subjects'
                )
                    ->where(
                        'id',
                        $candidateId
                    )
                    ->where(
                        'standard_id',
                        (int) $standardId
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

                    $subject = DB::table('subjects')
                        ->where(
                            'id',
                            (int) $mapping->subject_id
                        )
                        ->where(
                            'is_active',
                            1
                        )
                        ->first();

                    if ($subject) {
                        return $subject;
                    }
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 3. EXAM MASTER SUBJECTS
        |--------------------------------------------------------------------------
        */

        if ($examMasterId) {

            $examSubjects = DB::table(
                'exam_master_subjects'
            )
                ->where(
                    'exam_master_id',
                    (int) $examMasterId
                )
                ->orderBy(
                    'display_order'
                )
                ->orderBy(
                    'id'
                )
                ->get([
                    'id',
                    'subject_id',
                    'subject_name',
                    'display_order',
                ]);


            /*
            |--------------------------------------------------------------------------
            | 3A. MATCH BY SUBJECT ID
            |--------------------------------------------------------------------------
            */

            foreach ($examSubjects as $examSubject) {

                $examSubjectId =
                    (int) (
                        $examSubject->subject_id
                        ?? 0
                    );

                if ($examSubjectId <= 0) {
                    continue;
                }


                /*
                | Try subjects.id
                */

                $subject = DB::table('subjects')
                    ->where(
                        'id',
                        $examSubjectId
                    )
                    ->where(
                        'is_active',
                        1
                    )
                    ->first();

                if ($subject) {
                    return $subject;
                }


                /*
                | Try standard_wise_subjects.id
                */

                if ($standardId) {

                    $mapping = DB::table(
                        'standard_wise_subjects'
                    )
                        ->where(
                            'id',
                            $examSubjectId
                        )
                        ->where(
                            'standard_id',
                            (int) $standardId
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

                        $subject = DB::table('subjects')
                            ->where(
                                'id',
                                (int) $mapping->subject_id
                            )
                            ->where(
                                'is_active',
                                1
                            )
                            ->first();

                        if ($subject) {
                            return $subject;
                        }
                    }
                }
            }


            /*
            |--------------------------------------------------------------------------
            | 3B. MATCH BY SUBJECT NAME
            |--------------------------------------------------------------------------
            */

            foreach ($examSubjects as $examSubject) {

                $examSubjectName = trim(
                    (string) (
                        $examSubject->subject_name
                        ?? ''
                    )
                );

                if ($examSubjectName === '') {
                    continue;
                }


                $subject = DB::table('subjects')
                    ->where(
                        'is_active',
                        1
                    )
                    ->whereRaw(
                        'UPPER(TRIM(subject_name)) = ?',
                        [
                            strtoupper(
                                $examSubjectName
                            )
                        ]
                    )
                    ->first();

                if ($subject) {
                    return $subject;
                }
            }
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | AUTHENTICATION
        |--------------------------------------------------------------------------
        */

        $user = Auth::user();

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
        | EXAMS
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
                ->get();


        /*
        |--------------------------------------------------------------------------
        | DIVISIONS
        |--------------------------------------------------------------------------
        */

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
        | BASE QUERY
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
        | Normal records are checked against the teacher class allocation.
        |
        | Orphaned old records are checked against tms.teacher_id.
        |
        |--------------------------------------------------------------------------
        */

        if (!$isAdministrator) {

            $query->where(function ($securityQuery) use ($userId) {

                /*
                | Normal TSA/TCA relationship
                */

                $securityQuery->whereExists(
                    function ($subQuery) use ($userId) {

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


                /*
                | Orphaned old TMS record
                */

                $securityQuery->orWhere(
                    'tms.teacher_id',
                    $userId
                );
            });
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
        |
        | IMPORTANT:
        |
        | tms.subject_id is explicitly loaded because orphaned status
        | records can still contain a valid subject ID.
        |
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

                    'tms.subject_id',

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
        | LOAD TSA IDS
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


        /*
        |--------------------------------------------------------------------------
        | LOAD TSA RECORDS
        |--------------------------------------------------------------------------
        */

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
        | LOAD TCA IDS
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


        /*
        |--------------------------------------------------------------------------
        | LOAD TCA RECORDS
        |--------------------------------------------------------------------------
        */

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
        | RESOLVE EACH DASHBOARD ROW
        |--------------------------------------------------------------------------
        */

        foreach (
            $statuses as $status
        ) {

            /*
            |------------------------------------------------------------------
            | NORMALIZE STATUS
            |------------------------------------------------------------------
            */

            $status->status =
                strtoupper(
                    trim(
                        (string) (
                            $status->status
                            ?? ''
                        )
                    )
                );


            /*
            |------------------------------------------------------------------
            | DEFAULT DISPLAY VALUES
            |------------------------------------------------------------------
            */

            $status->subject_name =
                '-';

            $status->subject_code =
                '-';

            $status->resolved_subject_id =
                null;

            $status->has_teacher_subject_allocation =
                false;


            /*
            |------------------------------------------------------------------
            | GET TSA
            |------------------------------------------------------------------
            */

            $tsa =
                $allocations->get(
                    $status->teacher_subject_allocation_id
                );


            /*
            |--------------------------------------------------------------------------
            | CASE 1: TSA EXISTS
            |--------------------------------------------------------------------------
            */

            if ($tsa) {

                $status->has_teacher_subject_allocation =
                    true;


                /*
                |------------------------------------------------------------------
                | GET TCA
                |------------------------------------------------------------------
                */

                $tca =
                    $classAllocations->get(
                        $tsa->teacher_class_allocation_id
                    );


                /*
                |------------------------------------------------------------------
                | TEACHER SECURITY
                |------------------------------------------------------------------
                */

                if (
                    !$isAdministrator &&
                    $tca &&
                    (int) $tca->user_id !== $userId
                ) {
                    continue;
                }


                /*
                |------------------------------------------------------------------
                | STANDARD
                |------------------------------------------------------------------
                */

                $actualStandardId =
                    (int) (
                        $tca?->standard_id
                        ?: $status->standard_id
                    );


                /*
                |------------------------------------------------------------------
                | RESOLVE SUBJECT
                |------------------------------------------------------------------
                */

                $subject =
                    $this->resolveSubject(
                        $status->subject_id,
                        $tsa->subject_id ?? null,
                        $status->exam_master_id,
                        $actualStandardId
                    );


                /*
                |------------------------------------------------------------------
                | DISPLAY
                |------------------------------------------------------------------
                */

                if ($subject) {

                    $status->subject_name =
                        trim(
                            (string) (
                                $subject->subject_name
                                ?? ''
                            )
                        );

                    $status->subject_code =
                        trim(
                            (string) (
                                $subject->subject_code
                                ?? ''
                            )
                        );

                    $status->resolved_subject_id =
                        (int) $subject->id;
                }


                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | CASE 2: ORPHANED TMS RECORD
            |--------------------------------------------------------------------------
            |
            | TMS exists but TSA no longer exists.
            |
            | Example:
            |
            | TMS 113
            | subject_id = 2
            | status = COMPLETED
            |
            |--------------------------------------------------------------------------
            */

            if (!$isAdministrator) {

                /*
                | Teacher users may see their own orphaned TMS records
                */

                if (
                    (int) $status->teacher_id !== $userId
                ) {
                    continue;
                }
            }


            /*
            |------------------------------------------------------------------
            | STANDARD
            |------------------------------------------------------------------
            */

            $actualStandardId =
                (int) (
                    $status->standard_id
                    ?? 0
                );


            /*
            |------------------------------------------------------------------
            | RESOLVE DIRECTLY FROM TMS SUBJECT ID
            |------------------------------------------------------------------
            */

            $subject = null;


            if (
                $status->subject_id !== null &&
                (int) $status->subject_id > 0
            ) {

                $subject =
                    DB::table('subjects')
                        ->where(
                            'id',
                            (int) $status->subject_id
                        )
                        ->where(
                            'is_active',
                            1
                        )
                        ->first();
            }


            /*
            |------------------------------------------------------------------
            | LEGACY FALLBACK
            |------------------------------------------------------------------
            */

            if (
                !$subject &&
                $actualStandardId > 0 &&
                $status->subject_id
            ) {

                $mapping =
                    DB::table(
                        'standard_wise_subjects'
                    )
                    ->where(
                        'id',
                        (int) $status->subject_id
                    )
                    ->where(
                        'standard_id',
                        $actualStandardId
                    )
                    ->where(
                        'is_active',
                        1
                    )
                    ->first();


                if (
                    $mapping &&
                    !empty(
                        $mapping->subject_id
                    )
                ) {

                    $subject =
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
                }
            }


            /*
            |------------------------------------------------------------------
            | DISPLAY ORPHANED SUBJECT
            |------------------------------------------------------------------
            */

            if ($subject) {

                $status->subject_name =
                    trim(
                        (string) (
                            $subject->subject_name
                            ?? ''
                        )
                    );

                $status->subject_code =
                    trim(
                        (string) (
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