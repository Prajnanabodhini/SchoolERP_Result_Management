<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\User;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Standard;
use App\Models\Division;
use App\Models\ExamMaster;
use App\Models\AcademicYear;
use App\Models\ExamMasterSubject;
use App\Models\TeacherClassAllocation;
use App\Models\TeacherSubjectAllocation;
use App\Models\TeacherMarksStatus;

class TeacherBulkAllocationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $standards = Standard::where('is_active', 1)
            ->orderBy('display_order')
            ->get();

        $academicYears = AcademicYear::where('is_active', 1)
            ->orderByDesc('id')
            ->get();

        $standardId = $request->input('standard_id', '');
        $academicYearId = $request->input('academic_year_id', '');
        $examMasterId = $request->input('exam_master_id', '');

        /*
        |--------------------------------------------------------------------------
        | EXAMS
        |--------------------------------------------------------------------------
        */

        $examsQuery = ExamMaster::where('is_active', 1)
            ->with('academicYear')
            ->orderBy('display_order')
            ->orderBy('exam_name');

        /*
        |--------------------------------------------------------------------------
        | ACADEMIC YEAR FILTER
        |--------------------------------------------------------------------------
        */

        if ($academicYearId !== '') {
            $examsQuery->where(
                'academic_year_id',
                (int) $academicYearId
            );
        }

        /*
        |--------------------------------------------------------------------------
        | STANDARD FILTER
        |--------------------------------------------------------------------------
        */

        if ($standardId !== '') {
            $examsQuery->where(
                'standard_id',
                (int) $standardId
            );
        }

        $exams = $examsQuery->get();

        /*
        |--------------------------------------------------------------------------
        | TEACHER CLASS ALLOCATIONS
        |--------------------------------------------------------------------------
        */

        $query = TeacherClassAllocation::with([
            'teacher',
            'academicYear',
            'section',
            'standard',
            'division',
            'subjectAllocations.exam',
        ]);

        /*
        |--------------------------------------------------------------------------
        | ACADEMIC YEAR FILTER
        |--------------------------------------------------------------------------
        */

        if ($academicYearId !== '') {
            $query->where(
                'academic_year_id',
                (int) $academicYearId
            );
        }

        /*
        |--------------------------------------------------------------------------
        | STANDARD FILTER
        |--------------------------------------------------------------------------
        */

        if ($standardId !== '') {
            $query->where(
                'standard_id',
                (int) $standardId
            );
        }

        /*
        |--------------------------------------------------------------------------
        | EXAM FILTER
        |--------------------------------------------------------------------------
        */

        if ($examMasterId !== '') {

            $query->whereHas(
                'subjectAllocations',
                function ($q) use ($examMasterId) {

                    $q->where(
                        'exam_master_id',
                        (int) $examMasterId
                    );
                }
            );
        }

        $allocations = $query
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | TSA IDS
        |--------------------------------------------------------------------------
        */

        $teacherSubjectAllocationIds =
            $allocations
                ->flatMap(function ($allocation) {

                    return $allocation
                        ->subjectAllocations
                        ->pluck('id');
                })
                ->filter()
                ->unique()
                ->values();

        /*
        |--------------------------------------------------------------------------
        | MARK STATUS
        |--------------------------------------------------------------------------
        */

        $statusSubjectMap = collect();

        if (
            $teacherSubjectAllocationIds->isNotEmpty()
        ) {

            $statusSubjectMap =
                DB::table(
                    'teacher_marks_status as tms'
                )
                ->whereIn(
                    'tms.teacher_subject_allocation_id',
                    $teacherSubjectAllocationIds
                )
                ->select([
                    'tms.teacher_subject_allocation_id',
                    'tms.academic_year_id',
                    'tms.standard_id',
                    'tms.division_id',
                    'tms.subject_id',
                    'tms.exam_master_id',
                    'tms.teacher_id',
                    'tms.status',
                ])
                ->get()
                ->keyBy(
                    'teacher_subject_allocation_id'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | STANDARD IDS
        |--------------------------------------------------------------------------
        */

        $standardIds = $allocations
            ->pluck('standard_id')
            ->filter()
            ->unique()
            ->map(
                fn ($id) => (int) $id
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | STANDARD WISE SUBJECTS
        |--------------------------------------------------------------------------
        */

        $standardSubjects = collect();

        if (
            $standardIds->isNotEmpty()
        ) {

            $standardSubjects =
                DB::table(
                    'standard_wise_subjects as sws'
                )
                ->leftJoin(
                    'standards as st',
                    'st.id',
                    '=',
                    'sws.standard_id'
                )
                ->leftJoin(
                    'subjects as s',
                    's.id',
                    '=',
                    'sws.subject_id'
                )
                ->whereIn(
                    'sws.standard_id',
                    $standardIds
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
                    'sws.id as mapping_id',
                    'sws.standard_id',
                    'sws.subject_id',
                    'st.standard_name',
                    's.subject_name',
                    's.subject_code',
                    's.short_name',
                    'sws.is_optional',
                    'sws.sort_order',
                ])
                ->orderBy(
                    'sws.standard_id'
                )
                ->orderBy(
                    'sws.sort_order'
                )
                ->orderBy(
                    'sws.id'
                )
                ->get()
                ->groupBy(
                    'standard_id'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | EXAM IDS
        |--------------------------------------------------------------------------
        */

        $examIds = $allocations
            ->flatMap(function ($allocation) {

                return $allocation
                    ->subjectAllocations
                    ->pluck('exam_master_id');
            })
            ->filter()
            ->unique()
            ->map(
                fn ($id) => (int) $id
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | EXAM SUBJECTS
        |--------------------------------------------------------------------------
        */

        $examSubjectRows = collect();

        if (
            $examIds->isNotEmpty()
        ) {

            $examSubjectRows =
                ExamMasterSubject::whereIn(
                    'exam_master_id',
                    $examIds
                )
                ->orderBy(
                    'display_order'
                )
                ->orderBy(
                    'id'
                )
                ->get([
                    'id',
                    'exam_master_id',
                    'standard_id',
                    'subject_id',
                    'subject_name',
                    'max_marks',
                    'passing_marks',
                    'display_order',
                ]);
        }

        $examSubjectsByExam =
            $examSubjectRows->groupBy(
                'exam_master_id'
            );

        /*
        |--------------------------------------------------------------------------
        | BUILD DISPLAY SUBJECTS
        |--------------------------------------------------------------------------
        */

        foreach (
            $allocations as $allocation
        ) {

            $allocation->displaySubjects =
                collect();

            $currentMappings =
                $standardSubjects->get(
                    (int) $allocation->standard_id,
                    collect()
                );

            foreach (
                $allocation->subjectAllocations
                as $subjectAllocation
            ) {

                $statusRecord =
                    $statusSubjectMap->get(
                        $subjectAllocation->id
                    );

                $examId =
                    (int) (
                        $subjectAllocation->exam_master_id
                        ??
                        $statusRecord?->exam_master_id
                        ??
                        0
                    );

                $tsaSubjectId =
                    (int) (
                        $subjectAllocation->subject_id
                        ??
                        0
                    );

                $tmsSubjectId =
                    (int) (
                        $statusRecord?->subject_id
                        ??
                        0
                    );

                /*
                |--------------------------------------------------------------------------
                | RESOLVE LEGACY IDS
                |--------------------------------------------------------------------------
                */

                $resolvedTsaSubjectId =
                    $this->resolveLegacySubjectId(
                        $tsaSubjectId,
                        (int) $allocation->standard_id
                    );

                $resolvedTmsSubjectId =
                    $this->resolveLegacySubjectId(
                        $tmsSubjectId,
                        (int) $allocation->standard_id
                    );

                $examSubjects =
                    $examSubjectsByExam->get(
                        $examId,
                        collect()
                    );

                $examSubject = null;

                $mappedSubject = null;

                /*
                |--------------------------------------------------------------------------
                | CANDIDATE SUBJECT IDS
                |--------------------------------------------------------------------------
                */

                $candidateSubjectIds =
                    collect([
                        $resolvedTmsSubjectId,
                        $resolvedTsaSubjectId,
                        $tmsSubjectId,
                        $tsaSubjectId,
                    ])
                    ->filter(
                        fn ($id) =>
                            (int) $id > 0
                    )
                    ->map(
                        fn ($id) =>
                            (int) $id
                    )
                    ->unique();

                /*
                |--------------------------------------------------------------------------
                | FIND CURRENT STANDARD MAPPING
                |--------------------------------------------------------------------------
                */

                foreach (
                    $candidateSubjectIds
                    as $candidateId
                ) {

                    $mappedSubject =
                        $currentMappings->first(
                            function (
                                $mapping
                            ) use (
                                $candidateId
                            ) {

                                return
                                    (int)
                                    $mapping->subject_id
                                    ===
                                    $candidateId;
                            }
                        );

                    if (
                        $mappedSubject
                    ) {
                        break;
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | FIND EXAM SUBJECT
                |--------------------------------------------------------------------------
                */

                foreach (
                    $candidateSubjectIds
                    as $candidateId
                ) {

                    $examSubject =
                        $examSubjects->first(
                            function (
                                $row
                            ) use (
                                $candidateId
                            ) {

                                return
                                    (int)
                                    $row->subject_id
                                    ===
                                    $candidateId;
                            }
                        );

                    if (
                        $examSubject
                    ) {
                        break;
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | LEGACY SWS ID
                |--------------------------------------------------------------------------
                */

                if (
                    !$mappedSubject
                ) {

                    foreach (
                        $candidateSubjectIds
                        as $candidateId
                    ) {

                        $mappedSubject =
                            $currentMappings->first(
                                function (
                                    $mapping
                                ) use (
                                    $candidateId
                                ) {

                                    return
                                        (int)
                                        $mapping->mapping_id
                                        ===
                                        $candidateId;
                                }
                            );

                        if (
                            $mappedSubject
                        ) {
                            break;
                        }
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | DEFAULTS
                |--------------------------------------------------------------------------
                */

                $correctSubjectId = 0;

                $subjectName = '-';

                $subjectCode = '';

                $shortName = '';

                $sortOrder = 9999;

                /*
                |--------------------------------------------------------------------------
                | STANDARD MAPPING MATCH
                |--------------------------------------------------------------------------
                */

                if (
                    $mappedSubject
                ) {

                    $correctSubjectId =
                        (int)
                        $mappedSubject->subject_id;

                    $subjectName =
                        $mappedSubject->subject_name
                        ?: '-';

                    $subjectCode =
                        $mappedSubject->subject_code
                        ?: '';

                    $shortName =
                        $mappedSubject->short_name
                        ?: '';

                    $sortOrder =
                        (int)
                        $mappedSubject->sort_order;
                }

                /*
                |--------------------------------------------------------------------------
                | EXAM SUBJECT MATCH
                |--------------------------------------------------------------------------
                */

                elseif (
                    $examSubject
                ) {

                    $examStoredSubjectId =
                        (int)
                        $examSubject->subject_id;

                    $directSubject =
                        Subject::where(
                            'id',
                            $examStoredSubjectId
                        )
                        ->where(
                            'is_active',
                            1
                        )
                        ->first();

                    if (
                        $directSubject
                    ) {

                        $correctSubjectId =
                            (int)
                            $directSubject->id;

                        $subjectName =
                            $directSubject->subject_name
                            ?: '-';

                        $subjectCode =
                            $directSubject->subject_code
                            ?: '';

                        $shortName =
                            $directSubject->short_name
                            ?: '';

                    } else {

                        $legacyMapping =
                            $currentMappings->first(
                                function (
                                    $mapping
                                ) use (
                                    $examStoredSubjectId
                                ) {

                                    return
                                        (int)
                                        $mapping->mapping_id
                                        ===
                                        $examStoredSubjectId;
                                }
                            );

                        if (
                            $legacyMapping
                        ) {

                            $correctSubjectId =
                                (int)
                                $legacyMapping->subject_id;

                            $subjectName =
                                $legacyMapping->subject_name
                                ?: '-';

                            $subjectCode =
                                $legacyMapping->subject_code
                                ?: '';

                            $shortName =
                                $legacyMapping->short_name
                                ?: '';

                            $sortOrder =
                                (int)
                                $legacyMapping->sort_order;

                        } else {

                            $correctSubjectId =
                                $examStoredSubjectId;

                            $subjectName =
                                $examSubject->subject_name
                                ?: '-';

                            $sortOrder =
                                (int) (
                                    $examSubject->display_order
                                    ??
                                    9999
                                );
                        }
                    }

                }

                /*
                |--------------------------------------------------------------------------
                | DIRECT SUBJECT FALLBACK
                |--------------------------------------------------------------------------
                */

                else {

                    $correctSubjectId =
                        $resolvedTmsSubjectId
                        ?:
                        $resolvedTsaSubjectId
                        ?:
                        $tmsSubjectId
                        ?:
                        $tsaSubjectId;

                    $subject =
                        Subject::where(
                            'id',
                            $correctSubjectId
                        )
                        ->where(
                            'is_active',
                            1
                        )
                        ->first();

                    if (
                        $subject
                    ) {

                        $subjectName =
                            $subject->subject_name
                            ?: '-';

                        $subjectCode =
                            $subject->subject_code
                            ?: '';

                        $shortName =
                            $subject->short_name
                            ?: '';
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | DISPLAY SUBJECT
                |--------------------------------------------------------------------------
                */

                $allocation
                    ->displaySubjects
                    ->push(
                        (object) [

                            'teacher_subject_allocation_id' =>
                                $subjectAllocation->id,

                            'subject_id' =>
                                $correctSubjectId,

                            'stored_subject_id' =>
                                $tsaSubjectId,

                            'subject_name' =>
                                $subjectName,

                            'subject_code' =>
                                $subjectCode,

                            'short_name' =>
                                $shortName,

                            'sort_order' =>
                                $sortOrder,

                            'status' =>
                                $statusRecord?->status
                                ??
                                'PENDING',

                            'exam_master_id' =>
                                $examId,
                        ]
                    );
            }

            $allocation->displaySubjects =
                $allocation
                    ->displaySubjects
                    ->unique(
                        'teacher_subject_allocation_id'
                    )
                    ->sortBy([
                        [
                            'sort_order',
                            'asc',
                        ],
                        [
                            'subject_name',
                            'asc',
                        ],
                    ])
                    ->values();
        }

        return view(
            'administrator.teacher-bulk-allocation.index',
            compact(
                'allocations',
                'standards',
                'academicYears',
                'exams',
                'academicYearId',
                'standardId',
                'examMasterId'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create(Request $request)
    {
        $teachers =
            User::where(
                'role',
                'Teacher'
            )
            ->where(
                'is_active',
                1
            )
            ->orderBy(
                'name'
            )
            ->get();

        $academicYears =
            AcademicYear::where(
                'is_active',
                1
            )
            ->orderByDesc(
                'id'
            )
            ->get();

        $sections =
            Section::orderBy(
                'display_order'
            )
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

        $divisions =
            Division::where(
                'is_active',
                1
            )
            ->orderBy(
                'display_order'
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | EXAMS
        |--------------------------------------------------------------------------
        |
        | If Academic Year has been selected in the request,
        | only exams from that year are returned.
        |
        */

        $selectedAcademicYearId =
            $request->input(
                'academic_year_id'
            );

        $examsQuery =
            ExamMaster::where(
                'is_active',
                1
            )
            ->orderBy(
                'display_order'
            )
            ->orderBy(
                'exam_name'
            );

        if (
            $selectedAcademicYearId !== null &&
            $selectedAcademicYearId !== ''
        ) {

            $examsQuery->where(
                'academic_year_id',
                (int)
                $selectedAcademicYearId
            );
        }

        $exams =
            $examsQuery->get();

        return view(
            'administrator.teacher-bulk-allocation.create',
            compact(
                'teachers',
                'academicYears',
                'sections',
                'standards',
                'divisions',
                'exams',
                'selectedAcademicYearId'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE LEGACY SUBJECT ID
    |--------------------------------------------------------------------------
    */

    private function resolveLegacySubjectId(
        $storedSubjectId,
        $standardId
    ) {
        $storedSubjectId =
            (int)
            $storedSubjectId;

        $standardId =
            (int)
            $standardId;

        if (
            $storedSubjectId <= 0 ||
            $standardId <= 0
        ) {
            return 0;
        }

        /*
        |--------------------------------------------------------------------------
        | CURRENT SUBJECT MASTER
        |--------------------------------------------------------------------------
        */

        $subjectExists =
            Subject::where(
                'id',
                $storedSubjectId
            )
            ->where(
                'is_active',
                1
            )
            ->exists();

        if (
            $subjectExists
        ) {
            return $storedSubjectId;
        }

        /*
        |--------------------------------------------------------------------------
        | LEGACY STANDARD WISE SUBJECT
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
            $mapping->subject_id
        ) {

            return (int)
                $mapping->subject_id;
        }

        return 0;
    }


    /*
    |--------------------------------------------------------------------------
    | GET EXAM MAPPED SUBJECTS
    |--------------------------------------------------------------------------
    */

    private function getExamMappedSubjects(
        $examMasterId,
        $standardId = null
    ) {
        if (
            !$examMasterId
        ) {
            return collect();
        }

        $exam =
            ExamMaster::find(
                $examMasterId
            );

        if (
            !$exam
        ) {
            return collect();
        }

        $standardId =
            $standardId
            ? (int)
                $standardId
            : (int)
                $exam->standard_id;

        if (
            !$standardId
        ) {
            return collect();
        }

        if (
            $exam->standard_id &&
            (int)
            $exam->standard_id !==
            $standardId
        ) {
            return collect();
        }

        $examSubjects =
            ExamMasterSubject::query()
            ->where(
                'exam_master_id',
                $examMasterId
            )
            ->whereNotNull(
                'subject_id'
            )
            ->orderBy(
                'display_order'
            )
            ->orderBy(
                'id'
            )
            ->get([
                'id',
                'exam_master_id',
                'standard_id',
                'subject_id',
                'subject_name',
                'max_marks',
                'passing_marks',
                'display_order',
            ]);

        if (
            $examSubjects->isEmpty()
        ) {
            return collect();
        }

        $standardMappings =
            DB::table(
                'standard_wise_subjects as sws'
            )
            ->leftJoin(
                'standards as st',
                'st.id',
                '=',
                'sws.standard_id'
            )
            ->leftJoin(
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
            ->select([
                'sws.id as mapping_id',
                'sws.standard_id',
                'sws.subject_id',
                'st.standard_name',
                's.subject_name',
                's.subject_code',
                's.short_name',
                'sws.is_optional',
                'sws.sort_order',
            ])
            ->orderBy(
                'sws.sort_order'
            )
            ->orderBy(
                'sws.id'
            )
            ->get();

        $standardName =
            optional(
                Standard::find(
                    $standardId
                )
            )->standard_name
            ?? '';

        $subjects =
            collect();

        foreach (
            $examSubjects as $examSubject
        ) {

            $storedSubjectId =
                (int)
                $examSubject->subject_id;

            if (
                $storedSubjectId <= 0
            ) {
                continue;
            }

            $mappedSubject =
                $standardMappings->first(
                    function (
                        $mapping
                    ) use (
                        $storedSubjectId
                    ) {

                        return
                            (int)
                            $mapping->subject_id
                            ===
                            $storedSubjectId;
                    }
                );

            /*
            |--------------------------------------------------------------------------
            | LEGACY FORMAT
            |--------------------------------------------------------------------------
            */

            if (
                !$mappedSubject
            ) {

                $mappedSubject =
                    $standardMappings->first(
                        function (
                            $mapping
                        ) use (
                            $storedSubjectId
                        ) {

                            return
                                (int)
                                $mapping->mapping_id
                                ===
                                $storedSubjectId;
                        }
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | DIRECT SUBJECT FALLBACK
            |--------------------------------------------------------------------------
            */

            if (
                !$mappedSubject
            ) {

                $directSubject =
                    Subject::where(
                        'id',
                        $storedSubjectId
                    )
                    ->where(
                        'is_active',
                        1
                    )
                    ->first();

                if (
                    $directSubject
                ) {

                    $subjects->push(
                        (object) [

                            'mapping_id' =>
                                null,

                            'standard_id' =>
                                $standardId,

                            'subject_id' =>
                                (int)
                                $directSubject->id,

                            'standard_name' =>
                                $standardName,

                            'subject_name' =>
                                $directSubject->subject_name
                                ?:
                                '-',

                            'subject_code' =>
                                $directSubject->subject_code
                                ?:
                                '',

                            'short_name' =>
                                $directSubject->short_name
                                ?:
                                '',

                            'is_optional' =>
                                0,

                            'sort_order' =>
                                (int) (
                                    $examSubject
                                        ->display_order
                                    ??
                                    9999
                                ),

                            'exam_subject_id' =>
                                $examSubject->id,

                            'max_marks' =>
                                $examSubject->max_marks,

                            'passing_marks' =>
                                $examSubject->passing_marks,

                            'display_order' =>
                                (int) (
                                    $examSubject
                                        ->display_order
                                    ??
                                    9999
                                ),
                        ]
                    );
                }

                continue;
            }

            $subjects->push(
                (object) [

                    'mapping_id' =>
                        (int)
                        $mappedSubject
                            ->mapping_id,

                    'standard_id' =>
                        (int)
                        $mappedSubject
                            ->standard_id,

                    'subject_id' =>
                        (int)
                        $mappedSubject
                            ->subject_id,

                    'standard_name' =>
                        $mappedSubject
                            ->standard_name
                        ?:
                        $standardName,

                    'subject_name' =>
                        $mappedSubject
                            ->subject_name
                        ?:
                        (
                            $examSubject
                                ->subject_name
                            ?:
                            '-'
                        ),

                    'subject_code' =>
                        $mappedSubject
                            ->subject_code
                        ?:
                        '',

                    'short_name' =>
                        $mappedSubject
                            ->short_name
                        ?:
                        '',

                    'is_optional' =>
                        (int) (
                            $mappedSubject
                                ->is_optional
                            ??
                            0
                        ),

                    'sort_order' =>
                        (int) (
                            $mappedSubject
                                ->sort_order
                            ??
                            9999
                        ),

                    'exam_subject_id' =>
                        $examSubject->id,

                    'max_marks' =>
                        $examSubject->max_marks,

                    'passing_marks' =>
                        $examSubject->passing_marks,

                    'display_order' =>
                        (int) (
                            $examSubject
                                ->display_order
                            ??
                            $mappedSubject
                                ->sort_order
                            ??
                            9999
                        ),
                ]
            );
        }

        return $subjects
            ->unique(
                'subject_id'
            )
            ->sortBy([
                [
                    'display_order',
                    'asc',
                ],
                [
                    'sort_order',
                    'asc',
                ],
                [
                    'subject_id',
                    'asc',
                ],
            ])
            ->values();
    }


    /*
    |--------------------------------------------------------------------------
    | GET EXAM DETAILS
    |--------------------------------------------------------------------------
    */

    public function getExamDetails(
        Request $request
    ) {
        $request->validate([
            'exam_master_id' =>
                'required|exists:exam_masters,id',

            'academic_year_id' =>
                'nullable|exists:academic_years,id',
        ]);

        $exam =
            ExamMaster::findOrFail(
                $request->exam_master_id
            );

        /*
        |--------------------------------------------------------------------------
        | ACADEMIC YEAR SAFETY CHECK
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'academic_year_id'
            ) &&
            (int)
            $exam->academic_year_id !==
            (int)
            $request->academic_year_id
        ) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Selected Exam does not belong to the selected Academic Year.',
                'standard' => null,
                'section' => null,
                'divisions' => [],
                'subjects' => [],
            ], 422);
        }

        $standard =
            Standard::where(
                'id',
                $exam->standard_id
            )
            ->where(
                'is_active',
                1
            )
            ->first();

        if (
            !$standard
        ) {

            return response()->json([
                'success' => false,
                'message' =>
                    'No Standard is assigned to this Exam.',
                'standard' => null,
                'section' => null,
                'divisions' => [],
                'subjects' => [],
            ]);
        }

        $section = null;

        if (
            $standard->section_id
        ) {

            $section =
                Section::find(
                    $standard->section_id
                );
        }

        $divisions =
            Division::where(
                'is_active',
                1
            )
            ->orderBy(
                'display_order'
            )
            ->get([
                'id',
                'division_name',
            ]);

        $subjects =
            $this->getExamMappedSubjects(
                $exam->id,
                $standard->id
            );

        return response()->json([

            'success' =>
                true,

            'exam' => [
                'id' =>
                    $exam->id,

                'exam_name' =>
                    $exam->exam_name,

                'academic_year_id' =>
                    (int)
                    $exam->academic_year_id,
            ],

            'standard' => [
                'id' =>
                    $standard->id,

                'standard_name' =>
                    $standard->standard_name,
            ],

            'section' =>
                $section
                ? [
                    'id' =>
                        $section->id,

                    'section_name' =>
                        $section->section_name,
                ]
                : null,

            'divisions' =>
                $divisions,

            'subjects' =>
                $subjects->map(
                    function (
                        $subject
                    ) {

                        return [

                            'subject_id' =>
                                (int)
                                $subject
                                    ->subject_id,

                            'subject_name' =>
                                $subject
                                    ->subject_name
                                ?:
                                '-',

                            'subject_code' =>
                                $subject
                                    ->subject_code
                                ?:
                                '',

                            'short_name' =>
                                $subject
                                    ->short_name
                                ?:
                                '',

                            'is_optional' =>
                                (int)
                                $subject
                                    ->is_optional,

                            'exam_subject_id' =>
                                (int)
                                $subject
                                    ->exam_subject_id,

                            'max_marks' =>
                                $subject
                                    ->max_marks,

                            'passing_marks' =>
                                $subject
                                    ->passing_marks,

                            'display_order' =>
                                $subject
                                    ->display_order,
                        ];
                    }
                )->values(),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | GET STANDARDS
    |--------------------------------------------------------------------------
    */

    public function getStandards(
        Request $request
    ) {
        $sectionId =
            $request->input(
                'section_id'
            );

        if (
            !$sectionId
        ) {
            return response()->json([]);
        }

        return response()->json(
            Standard::where(
                'section_id',
                $sectionId
            )
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
            ])
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GET SUBJECTS
    |--------------------------------------------------------------------------
    */

    public function getSubjects(
        Request $request
    ) {
        $examMasterId =
            $request->input(
                'exam_master_id'
            );

        if (
            !$examMasterId
        ) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Exam is required.',
                'subjects' => [],
            ], 422);
        }

        $exam =
            ExamMaster::find(
                $examMasterId
            );

        if (
            !$exam
        ) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Selected Exam was not found.',
                'subjects' => [],
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | OPTIONAL ACADEMIC YEAR CHECK
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'academic_year_id'
            ) &&
            (int)
            $exam->academic_year_id !==
            (int)
            $request->academic_year_id
        ) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Selected Exam does not belong to the selected Academic Year.',
                'subjects' => [],
            ], 422);
        }

        $standardId =
            (int)
            $exam->standard_id;

        if (
            !$standardId
        ) {

            return response()->json([
                'success' => false,
                'message' =>
                    'No Standard is assigned to this Exam.',
                'subjects' => [],
            ], 422);
        }

        $standard =
            Standard::find(
                $standardId
            );

        if (
            !$standard
        ) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Standard not found.',
                'subjects' => [],
            ], 404);
        }

        $subjects =
            $this->getExamMappedSubjects(
                $examMasterId,
                $standardId
            );

        return response()->json([

            'success' =>
                true,

            'exam' => [
                'id' =>
                    $exam->id,

                'exam_name' =>
                    $exam->exam_name,

                'academic_year_id' =>
                    (int)
                    $exam->academic_year_id,
            ],

            'standard' => [
                'id' =>
                    $standard->id,

                'standard_name' =>
                    $standard->standard_name,
            ],

            'subjects' =>
                $subjects->map(
                    function (
                        $subject
                    ) {

                        return [

                            'mapping_id' =>
                                $subject
                                    ->mapping_id,

                            'subject_id' =>
                                (int)
                                $subject
                                    ->subject_id,

                            'standard' =>
                                $subject
                                    ->standard_name,

                            'subject_name' =>
                                $subject
                                    ->subject_name
                                ?:
                                '-',

                            'subject_code' =>
                                $subject
                                    ->subject_code
                                ?:
                                '',

                            'short_name' =>
                                $subject
                                    ->short_name
                                ?:
                                '',

                            'is_optional' =>
                                (int)
                                $subject
                                    ->is_optional,

                            'max_marks' =>
                                $subject
                                    ->max_marks,

                            'passing_marks' =>
                                $subject
                                    ->passing_marks,

                            'display_order' =>
                                $subject
                                    ->display_order,
                        ];
                    }
                )->values(),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE SUBJECT
    |--------------------------------------------------------------------------
    */

    private function resolveSubject(
        $incomingSubjectId,
        $standardId,
        $examMasterId
    ) {
        if (
            !$incomingSubjectId ||
            !$standardId ||
            !$examMasterId
        ) {
            return null;
        }

        $incomingSubjectId =
            (int)
            $incomingSubjectId;

        $standardId =
            (int)
            $standardId;

        $examMasterId =
            (int)
            $examMasterId;

        /*
        |--------------------------------------------------------------------------
        | CURRENT FORMAT
        |--------------------------------------------------------------------------
        */

        $standardSubject =
            DB::table(
                'standard_wise_subjects as sws'
            )
            ->where(
                'sws.standard_id',
                $standardId
            )
            ->where(
                'sws.subject_id',
                $incomingSubjectId
            )
            ->where(
                'sws.is_active',
                1
            )
            ->first();

        /*
        |--------------------------------------------------------------------------
        | LEGACY FORMAT
        |--------------------------------------------------------------------------
        */

        if (
            !$standardSubject
        ) {

            $standardSubject =
                DB::table(
                    'standard_wise_subjects as sws'
                )
                ->where(
                    'sws.standard_id',
                    $standardId
                )
                ->where(
                    'sws.id',
                    $incomingSubjectId
                )
                ->where(
                    'sws.is_active',
                    1
                )
                ->first();
        }

        if (
            !$standardSubject
        ) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | SUBJECT MASTER
        |--------------------------------------------------------------------------
        */

        $subject =
            Subject::where(
                'id',
                $standardSubject->subject_id
            )
            ->where(
                'is_active',
                1
            )
            ->first();

        if (
            !$subject
        ) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | EXAM SUBJECT
        |--------------------------------------------------------------------------
        */

        $examSubject =
            ExamMasterSubject::where(
                'exam_master_id',
                $examMasterId
            )
            ->where(
                'subject_id',
                $subject->id
            )
            ->first();

        /*
        |--------------------------------------------------------------------------
        | LEGACY EXAM SUBJECT
        |--------------------------------------------------------------------------
        */

        if (
            !$examSubject
        ) {

            $examSubject =
                ExamMasterSubject::where(
                    'exam_master_id',
                    $examMasterId
                )
                ->where(
                    'subject_id',
                    $standardSubject->id
                )
                ->first();
        }

        if (
            !$examSubject
        ) {
            return null;
        }

        return [

            'subject' =>
                $subject,

            'examSubject' =>
                $examSubject,

            'standardSubject' =>
                $standardSubject,
        ];
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

            'user_id' =>
                'required|exists:users,id',

            'academic_year_id' =>
                'required|exists:academic_years,id',

            'exam_master_id' =>
                'required|exists:exam_masters,id',

            'rows' =>
                'required|array|min:1',

            'rows.*.standards' =>
                'required|array|min:1',

            'rows.*.standards.*' =>
                'required|exists:standards,id',

            'rows.*.divisions' =>
                'required|array|min:1',

            'rows.*.divisions.*' =>
                'required|exists:divisions,id',

            'rows.*.subjects' =>
                'required|array|min:1',

            'rows.*.subjects.*' =>
                'required|exists:subjects,id',
        ]);

        try {

            DB::transaction(
                function () use (
                    $request
                ) {

                    $exam =
                        ExamMaster::findOrFail(
                            $request->exam_master_id
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | CRITICAL ACADEMIC YEAR CHECK
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $exam->academic_year_id === null
                    ) {

                        throw new \Exception(
                            'Selected Exam does not have an Academic Year assigned.'
                        );
                    }

                    if (
                        (int)
                        $exam->academic_year_id !==
                        (int)
                        $request->academic_year_id
                    ) {

                        throw new \Exception(
                            'Selected Exam does not belong to the selected Academic Year.'
                        );
                    }

                    foreach (
                        $request->rows
                        as $row
                    ) {

                        foreach (
                            $row['standards']
                            as $standardId
                        ) {

                            $standard =
                                Standard::findOrFail(
                                    $standardId
                                );

                            if (
                                $exam->standard_id &&
                                (int)
                                $exam->standard_id !==
                                (int)
                                $standard->id
                            ) {

                                throw new \Exception(
                                    'Selected Standard does not belong to the selected Exam.'
                                );
                            }

                            if (
                                !$standard->section_id
                            ) {

                                throw new \Exception(
                                    "Section is not assigned to Standard {$standard->standard_name}."
                                );
                            }

                            foreach (
                                $row['divisions']
                                as $divisionId
                            ) {

                                $division =
                                    Division::findOrFail(
                                        $divisionId
                                    );

                                /*
                                |--------------------------------------------------------------------------
                                | FIND / CREATE CLASS ALLOCATION
                                |--------------------------------------------------------------------------
                                */

                                $classAllocation =
                                    TeacherClassAllocation::firstOrCreate(
                                        [

                                            'user_id' =>
                                                $request->user_id,

                                            'academic_year_id' =>
                                                $request
                                                    ->academic_year_id,

                                            'section_id' =>
                                                $standard
                                                    ->section_id,

                                            'standard_id' =>
                                                $standard
                                                    ->id,

                                            'division_id' =>
                                                $division
                                                    ->id,
                                        ],
                                        [

                                            'is_class_teacher' =>
                                                !empty(
                                                    $row[
                                                        'is_class_teacher'
                                                    ]
                                                ),
                                        ]
                                    );

                                /*
                                |--------------------------------------------------------------------------
                                | EXTRA SAFETY
                                |--------------------------------------------------------------------------
                                */

                                if (
                                    (int)
                                    $classAllocation
                                        ->academic_year_id !==
                                    (int)
                                    $exam->academic_year_id
                                ) {

                                    throw new \Exception(
                                        'Teacher Class Allocation Academic Year does not match the selected Exam Academic Year.'
                                    );
                                }

                                /*
                                |--------------------------------------------------------------------------
                                | SUBJECTS
                                |--------------------------------------------------------------------------
                                */

                                foreach (
                                    $row['subjects']
                                    as $selectedSubjectId
                                ) {

                                    $resolved =
                                        $this->resolveSubject(
                                            $selectedSubjectId,
                                            $standard->id,
                                            $exam->id
                                        );

                                    if (
                                        !$resolved
                                    ) {

                                        throw new \Exception(
                                            "Subject ID {$selectedSubjectId} is not valid for {$standard->standard_name} and selected Exam."
                                        );
                                    }

                                    $subject =
                                        $resolved['subject'];

                                    /*
                                    |--------------------------------------------------------------------------
                                    | EXISTING TSA
                                    |--------------------------------------------------------------------------
                                    */

                                    $existing =
                                        TeacherSubjectAllocation::where(
                                            'teacher_class_allocation_id',
                                            $classAllocation->id
                                        )
                                        ->where(
                                            'exam_master_id',
                                            $exam->id
                                        )
                                        ->get()
                                        ->first(
                                            function (
                                                $tsa
                                            ) use (
                                                $subject,
                                                $standard
                                            ) {

                                                return
                                                    $this->tsaRepresentsSubject(
                                                        $tsa,
                                                        $subject->id,
                                                        $standard->id
                                                    );
                                            }
                                        );

                                    if (
                                        $existing
                                    ) {

                                        $this->ensureTeacherMarksStatus(
                                            $existing,
                                            $request,
                                            $exam,
                                            $standard,
                                            $division,
                                            $subject
                                        );

                                        continue;
                                    }

                                    /*
                                    |--------------------------------------------------------------------------
                                    | CREATE NEW TSA
                                    |--------------------------------------------------------------------------
                                    */

                                    $subjectAllocation =
                                        TeacherSubjectAllocation::create([
                                            'teacher_class_allocation_id' =>
                                                $classAllocation->id,

                                            'subject_id' =>
                                                $subject->id,

                                            'exam_master_id' =>
                                                $exam->id,
                                        ]);

                                    /*
                                    |--------------------------------------------------------------------------
                                    | CREATE STATUS
                                    |--------------------------------------------------------------------------
                                    */

                                    $this->createTeacherMarksStatus(
                                        $subjectAllocation,
                                        $request,
                                        $exam,
                                        $standard,
                                        $division,
                                        $subject
                                    );
                                }
                            }
                        }
                    }
                }
            );

            return redirect()
                ->route(
                    'teacher-bulk-allocation.index'
                )
                ->with(
                    'success',
                    'Teacher Bulk Allocation Saved Successfully.'
                );

        } catch (
            \Throwable $e
        ) {

            Log::error(
                'Teacher Bulk Allocation Error',
                [

                    'message' =>
                        $e->getMessage(),

                    'trace' =>
                        $e->getTraceAsString(),
                ]
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK WHETHER TSA REPRESENTS SUBJECT
    |--------------------------------------------------------------------------
    */

    private function tsaRepresentsSubject(
        $tsa,
        $actualSubjectId,
        $standardId
    ) {
        $storedId =
            (int)
            $tsa->subject_id;

        $actualSubjectId =
            (int)
            $actualSubjectId;

        $standardId =
            (int)
            $standardId;

        if (
            $storedId <= 0 ||
            $actualSubjectId <= 0
        ) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | CURRENT FORMAT
        |--------------------------------------------------------------------------
        */

        if (
            $storedId ===
            $actualSubjectId
        ) {

            return true;
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
                $storedId
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
            (int)
            $mapping->subject_id ===
            $actualSubjectId
        ) {

            return true;
        }

        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE TEACHER MARKS STATUS
    |--------------------------------------------------------------------------
    */

    private function createTeacherMarksStatus(
        $tsa,
        Request $request,
        $exam,
        $standard,
        $division,
        $subject
    ) {

        return TeacherMarksStatus::create([

            'academic_year_id' =>
                $exam->academic_year_id
                ??
                $request->academic_year_id,

            'exam_master_id' =>
                $exam->id,

            'teacher_subject_allocation_id' =>
                $tsa->id,

            'standard_id' =>
                $standard->id,

            'division_id' =>
                $division->id,

            'subject_id' =>
                $subject->id,

            'teacher_id' =>
                $request->user_id,

            'status' =>
                'PENDING',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | ENSURE MARK STATUS
    |--------------------------------------------------------------------------
    */

    private function ensureTeacherMarksStatus(
        $tsa,
        Request $request,
        $exam,
        $standard,
        $division,
        $subject
    ) {
        $status =
            TeacherMarksStatus::where(
                'teacher_subject_allocation_id',
                $tsa->id
            )
            ->first();

        if (
            $status
        ) {

            /*
            |--------------------------------------------------------------------------
            | DO NOT CHANGE EXISTING STATUS.
            |--------------------------------------------------------------------------
            |
            | COMPLETED remains COMPLETED.
            | PENDING remains PENDING.
            |
            */

            return $status;
        }

        return $this->createTeacherMarksStatus(
            $tsa,
            $request,
            $exam,
            $standard,
            $division,
            $subject
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(
        $id
    ) {
        $allocation =
            TeacherClassAllocation::findOrFail(
                $id
            );

        $teachers =
            User::where(
                'role',
                'Teacher'
            )
            ->where(
                'is_active',
                1
            )
            ->orderBy(
                'name'
            )
            ->get();

        $academicYears =
            AcademicYear::where(
                'is_active',
                1
            )
            ->orderByDesc(
                'id'
            )
            ->get();

        $standard =
            Standard::findOrFail(
                $allocation->standard_id
            );

        $divisions =
            Division::where(
                'is_active',
                1
            )
            ->orderBy(
                'display_order'
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | ONLY EXAMS FROM ALLOCATION YEAR
        |--------------------------------------------------------------------------
        |
        | This is important after adding academic_year_id to exam_masters.
        |
        */

        $exams =
            ExamMaster::where(
                'is_active',
                1
            )
            ->where(
                'standard_id',
                $allocation->standard_id
            )
            ->where(
                'academic_year_id',
                $allocation->academic_year_id
            )
            ->orderBy(
                'display_order'
            )
            ->orderBy(
                'exam_name'
            )
            ->get();

        $existingSubjectAllocations =
            TeacherSubjectAllocation::where(
                'teacher_class_allocation_id',
                $allocation->id
            )
            ->orderBy(
                'id'
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | SELECT LATEST EXAM
        |--------------------------------------------------------------------------
        */

        $selectedExamId =
            $existingSubjectAllocations
                ->pluck(
                    'exam_master_id'
                )
                ->filter()
                ->last();

        /*
        |--------------------------------------------------------------------------
        | VERIFY SELECTED EXAM BELONGS TO ALLOCATION YEAR
        |--------------------------------------------------------------------------
        */

        if (
            $selectedExamId
        ) {

            $selectedExam =
                ExamMaster::find(
                    $selectedExamId
                );

            if (
                !$selectedExam ||
                (
                    $selectedExam->academic_year_id !== null &&
                    (int)
                    $selectedExam->academic_year_id !==
                    (int)
                    $allocation->academic_year_id
                )
            ) {

                $selectedExamId =
                    null;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | GET CURRENT SUBJECTS
        |--------------------------------------------------------------------------
        */

        $subjects =
            collect();

        if (
            $selectedExamId
        ) {

            $subjects =
                $this->getExamMappedSubjects(
                    $selectedExamId,
                    $allocation->standard_id
                );
        }

        /*
        |--------------------------------------------------------------------------
        | SELECTED SUBJECTS
        |--------------------------------------------------------------------------
        */

        $selectedSubjects =
            collect();

        if (
            $selectedExamId
        ) {

            $currentTsa =
                $existingSubjectAllocations
                    ->where(
                        'exam_master_id',
                        $selectedExamId
                    );

            foreach (
                $currentTsa as $tsa
            ) {

                $resolvedSubjectId =
                    $this->resolveLegacySubjectId(
                        $tsa->subject_id,
                        $allocation->standard_id
                    );

                if (
                    $resolvedSubjectId > 0
                ) {

                    $selectedSubjects->push(
                        $resolvedSubjectId
                    );
                }
            }
        }

        $selectedSubjects =
            $selectedSubjects
                ->unique()
                ->values()
                ->toArray();

        /*
        |--------------------------------------------------------------------------
        | FALLBACK SUBJECTS
        |--------------------------------------------------------------------------
        */

        if (
            !$selectedExamId ||
            $subjects->isEmpty()
        ) {

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
                    $allocation->standard_id
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

                    's.id as subject_id',

                    's.subject_name',

                    's.subject_code',

                    's.short_name',

                    'sws.is_optional',

                    'sws.sort_order',
                ])
                ->orderBy(
                    'sws.sort_order'
                )
                ->orderBy(
                    's.id'
                )
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | NORMALIZE SUBJECT COLLECTION
        |--------------------------------------------------------------------------
        */

        $subjects =
            collect(
                $subjects
            )
            ->map(
                function (
                    $subject
                ) {

                    return (object) [

                        'subject_id' =>
                            (int) (
                                $subject->subject_id
                                ??
                                $subject->id
                                ??
                                0
                            ),

                        'subject_name' =>
                            $subject->subject_name
                            ??
                            '-',

                        'subject_code' =>
                            $subject->subject_code
                            ??
                            '',

                        'short_name' =>
                            $subject->short_name
                            ??
                            '',

                        'is_optional' =>
                            (int) (
                                $subject->is_optional
                                ??
                                0
                            ),

                        'sort_order' =>
                            (int) (
                                $subject->sort_order
                                ??
                                $subject->display_order
                                ??
                                9999
                            ),
                    ];
                }
            )
            ->filter(
                function (
                    $subject
                ) {

                    return
                        $subject->subject_id > 0;
                }
            )
            ->unique(
                'subject_id'
            )
            ->sortBy([
                [
                    'sort_order',
                    'asc',
                ],
                [
                    'subject_name',
                    'asc',
                ],
            ])
            ->values();

        return view(
            'administrator.teacher-bulk-allocation.edit',
            compact(
                'allocation',
                'teachers',
                'academicYears',
                'exams',
                'divisions',
                'standard',
                'subjects',
                'selectedSubjects',
                'selectedExamId'
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

            'user_id' =>
                'required|exists:users,id',

            'academic_year_id' =>
                'required|exists:academic_years,id',

            'standard_id' =>
                'required|exists:standards,id',

            'division_id' =>
                'required|exists:divisions,id',

            'exam_master_id' =>
                'required|exists:exam_masters,id',

            'subjects' =>
                'required|array|min:1',

            'subjects.*' =>
                'required|exists:subjects,id',

        ], [

            'user_id.required' =>
                'Please select Teacher.',

            'academic_year_id.required' =>
                'Please select Academic Year.',

            'standard_id.required' =>
                'Standard is required.',

            'division_id.required' =>
                'Division is required.',

            'exam_master_id.required' =>
                'Please select Exam.',

            'subjects.required' =>
                'Please select at least one Subject.',

            'subjects.min' =>
                'Please select at least one Subject.',
        ]);

        $allocation =
            TeacherClassAllocation::findOrFail(
                $id
            );

        $exam =
            ExamMaster::findOrFail(
                $request->exam_master_id
            );

        $standard =
            Standard::findOrFail(
                $request->standard_id
            );

        /*
        |--------------------------------------------------------------------------
        | EXAM / ACADEMIC YEAR VALIDATION
        |--------------------------------------------------------------------------
        */

        if (
            $exam->academic_year_id === null
        ) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Selected Exam does not have an Academic Year assigned.'
                );
        }

        if (
            (int)
            $exam->academic_year_id !==
            (int)
            $request->academic_year_id
        ) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Selected Exam does not belong to the selected Academic Year.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | EXAM / STANDARD VALIDATION
        |--------------------------------------------------------------------------
        */

        if (
            $exam->standard_id &&
            (int)
            $exam->standard_id !==
            (int)
            $standard->id
        ) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Selected Exam does not belong to selected Standard.'
                );
        }

        if (
            !$standard->section_id
        ) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Section is not assigned to selected Standard.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | NORMALIZE SUBJECTS
        |--------------------------------------------------------------------------
        */

        $selectedSubjectIds =
            collect(
                $request->input(
                    'subjects',
                    []
                )
            )
            ->map(
                fn ($id) =>
                    (int) $id
            )
            ->filter(
                fn ($id) =>
                    $id > 0
            )
            ->unique()
            ->values();

        if (
            $selectedSubjectIds->isEmpty()
        ) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Please select at least one Subject.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | RESOLVE SELECTED SUBJECTS
        |--------------------------------------------------------------------------
        */

        $resolvedSubjects =
            collect();

        foreach (
            $selectedSubjectIds
            as $subjectId
        ) {

            $resolved =
                $this->resolveSubject(
                    $subjectId,
                    $standard->id,
                    $exam->id
                );

            if (
                !$resolved
            ) {

                return back()
                    ->withInput()
                    ->with(
                        'error',
                        "Subject ID {$subjectId} is not valid for "
                        . "{$standard->standard_name} "
                        . "and selected Exam."
                    );
            }

            $resolvedSubjects->put(
                (int)
                $resolved['subject']->id,
                $resolved
            );
        }

        /*
        |--------------------------------------------------------------------------
        | DIVISION
        |--------------------------------------------------------------------------
        */

        $division =
            Division::findOrFail(
                $request->division_id
            );

        try {

            DB::transaction(
                function () use (
                    $request,
                    $allocation,
                    $exam,
                    $standard,
                    $division,
                    $selectedSubjectIds,
                    $resolvedSubjects
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | EXISTING TSA COUNT
                    |--------------------------------------------------------------------------
                    */

                    $existingTsaCount =
                        TeacherSubjectAllocation::where(
                            'teacher_class_allocation_id',
                            $allocation->id
                        )
                        ->count();

                    /*
                    |--------------------------------------------------------------------------
                    | TARGET ALLOCATION
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $existingTsaCount > 0
                    ) {

                        $sameAllocation =
                            TeacherClassAllocation::where(
                                'user_id',
                                $request->user_id
                            )
                            ->where(
                                'academic_year_id',
                                $request->academic_year_id
                            )
                            ->where(
                                'section_id',
                                $standard->section_id
                            )
                            ->where(
                                'standard_id',
                                $standard->id
                            )
                            ->where(
                                'division_id',
                                $request->division_id
                            )
                            ->first();

                        if (
                            $sameAllocation
                        ) {

                            $targetAllocation =
                                $sameAllocation;

                        } else {

                            /*
                            |--------------------------------------------------------------------------
                            | OLD ALLOCATION STAYS UNTOUCHED
                            |--------------------------------------------------------------------------
                            */

                            $targetAllocation =
                                TeacherClassAllocation::create([

                                    'user_id' =>
                                        $request
                                            ->user_id,

                                    'academic_year_id' =>
                                        $request
                                            ->academic_year_id,

                                    'section_id' =>
                                        $standard
                                            ->section_id,

                                    'standard_id' =>
                                        $standard
                                            ->id,

                                    'division_id' =>
                                        $request
                                            ->division_id,

                                    'is_class_teacher' =>
                                        $allocation
                                            ->is_class_teacher
                                        ??
                                        0,
                                ]);
                        }

                    } else {

                        /*
                        |--------------------------------------------------------------------------
                        | NO TSA = SAFE TO UPDATE
                        |--------------------------------------------------------------------------
                        */

                        $allocation->update([

                            'user_id' =>
                                $request->user_id,

                            'academic_year_id' =>
                                $request
                                    ->academic_year_id,

                            'section_id' =>
                                $standard
                                    ->section_id,

                            'standard_id' =>
                                $standard
                                    ->id,

                            'division_id' =>
                                $request
                                    ->division_id,
                        ]);

                        $targetAllocation =
                            $allocation;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | EXTRA SAFETY
                    |--------------------------------------------------------------------------
                    */

                    if (
                        (int)
                        $targetAllocation
                            ->academic_year_id !==
                        (int)
                        $exam->academic_year_id
                    ) {

                        throw new \Exception(
                            'Target Teacher Class Allocation Academic Year does not match the selected Exam Academic Year.'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | EXISTING TSA
                    |--------------------------------------------------------------------------
                    */

                    $existingAllocations =
                        TeacherSubjectAllocation::where(
                            'teacher_class_allocation_id',
                            $targetAllocation->id
                        )
                        ->where(
                            'exam_master_id',
                            $exam->id
                        )
                        ->orderBy(
                            'id'
                        )
                        ->get();

                    /*
                    |--------------------------------------------------------------------------
                    | PROCESS SELECTED SUBJECTS
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        $selectedSubjectIds
                        as $selectedSubjectId
                    ) {

                        $selectedSubjectId =
                            (int)
                            $selectedSubjectId;

                        $resolved =
                            $resolvedSubjects->get(
                                $selectedSubjectId
                            );

                        if (
                            !$resolved
                        ) {
                            continue;
                        }

                        $subject =
                            $resolved['subject'];

                        /*
                        |--------------------------------------------------------------------------
                        | LOGICAL EXISTING TSA CHECK
                        |--------------------------------------------------------------------------
                        */

                        $existingTsa =
                            $existingAllocations->first(
                                function (
                                    $tsa
                                ) use (
                                    $subject,
                                    $standard
                                ) {

                                    return
                                        $this->tsaRepresentsSubject(
                                            $tsa,
                                            $subject->id,
                                            $standard->id
                                        );
                                }
                            );

                        if (
                            $existingTsa
                        ) {

                            /*
                            |--------------------------------------------------------------------------
                            | DO NOT MODIFY TSA
                            |--------------------------------------------------------------------------
                            */

                            $this->ensureTeacherMarksStatus(
                                $existingTsa,
                                $request,
                                $exam,
                                $standard,
                                $division,
                                $subject
                            );

                            continue;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | NEW TSA
                        |--------------------------------------------------------------------------
                        */

                        $newTsa =
                            TeacherSubjectAllocation::create([

                                'teacher_class_allocation_id' =>
                                    $targetAllocation->id,

                                'subject_id' =>
                                    $subject->id,

                                'exam_master_id' =>
                                    $exam->id,
                            ]);

                        /*
                        |--------------------------------------------------------------------------
                        | NEW STATUS
                        |--------------------------------------------------------------------------
                        */

                        $this->createTeacherMarksStatus(
                            $newTsa,
                            $request,
                            $exam,
                            $standard,
                            $division,
                            $subject
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | UNSELECTED SUBJECTS ARE NOT TOUCHED
                    |--------------------------------------------------------------------------
                    |
                    | Old TSA remains.
                    | Old marks remain.
                    | Old status remains.
                    |
                    |--------------------------------------------------------------------------
                    */
                }
            );

            return redirect()
                ->route(
                    'teacher-bulk-allocation.index'
                )
                ->with(
                    'success',
                    'Allocation updated successfully. Existing subject allocations and marks have been preserved.'
                );

        } catch (
            \Throwable $e
        ) {

            Log::error(
                'Teacher Bulk Allocation Update Error',
                [

                    'allocation_id' =>
                        $id,

                    'message' =>
                        $e->getMessage(),

                    'trace' =>
                        $e->getTraceAsString(),
                ]
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(
        $id
    ) {
        $allocation =
            TeacherClassAllocation::findOrFail(
                $id
            );

        $tsaIds =
            TeacherSubjectAllocation::where(
                'teacher_class_allocation_id',
                $allocation->id
            )
            ->pluck(
                'id'
            );

        /*
        |--------------------------------------------------------------------------
        | CHECK MARKS
        |--------------------------------------------------------------------------
        */

        if (
            $tsaIds->isNotEmpty()
        ) {

            $hasMarks =
                DB::table(
                    'student_marks'
                )
                ->whereIn(
                    'teacher_subject_allocation_id',
                    $tsaIds
                )
                ->exists();

            if (
                $hasMarks
            ) {

                return redirect()
                    ->route(
                        'teacher-bulk-allocation.index'
                    )
                    ->with(
                        'error',
                        'This allocation cannot be deleted because examination marks already exist. Existing marks and allocations are protected.'
                    );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | DELETE ONLY WHEN NO MARKS
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $allocation,
                $tsaIds
            ) {

                if (
                    $tsaIds->isNotEmpty()
                ) {

                    TeacherMarksStatus::whereIn(
                        'teacher_subject_allocation_id',
                        $tsaIds
                    )
                    ->delete();

                    TeacherSubjectAllocation::whereIn(
                        'id',
                        $tsaIds
                    )
                    ->delete();
                }

                $allocation->delete();
            }
        );

        return redirect()
            ->route(
                'teacher-bulk-allocation.index'
            )
            ->with(
                'success',
                'Allocation deleted successfully.'
            );
    }
}