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
use App\Models\StandardWiseSubject;

class TeacherBulkAllocationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
{
    /*
    |--------------------------------------------------------------------------
    | STANDARDS
    |--------------------------------------------------------------------------
    */

    $standards = Standard::where('is_active', 1)
        ->orderBy('display_order')
        ->get();


    /*
    |--------------------------------------------------------------------------
    | FILTERS
    |--------------------------------------------------------------------------
    */

    $standardId = $request->input('standard_id', '');
    $examMasterId = $request->input('exam_master_id', '');


    /*
    |--------------------------------------------------------------------------
    | EXAMS
    |--------------------------------------------------------------------------
    |
    | If Standard is selected, only exams of that Standard are shown.
    |
    */

    $examsQuery = ExamMaster::where('is_active', 1)
        ->orderBy('display_order')
        ->orderBy('exam_name');

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


    /*
    |--------------------------------------------------------------------------
    | PAGINATION
    |--------------------------------------------------------------------------
    */

    $allocations = $query
        ->orderByDesc('id')
        ->paginate(25)
        ->withQueryString();


    /*
    |--------------------------------------------------------------------------
    | GET ALL TEACHER SUBJECT ALLOCATION IDS
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
    | GET TEACHER MARK STATUS
    |--------------------------------------------------------------------------
    */

    $statusSubjectMap = collect();

    if ($teacherSubjectAllocationIds->isNotEmpty()) {

        $statusSubjectMap = DB::table(
            'teacher_marks_status as tms'
        )
            ->whereIn(
                'tms.teacher_subject_allocation_id',
                $teacherSubjectAllocationIds
            )
            ->select([
                'tms.teacher_subject_allocation_id',
                'tms.standard_id',
                'tms.division_id',
                'tms.subject_id',
                'tms.exam_master_id',
                'tms.status',
            ])
            ->get()
            ->keyBy(
                'teacher_subject_allocation_id'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | GET ALL STANDARDS USED IN CURRENT PAGE
    |--------------------------------------------------------------------------
    */

    $standardIds = $allocations
        ->pluck('standard_id')
        ->filter()
        ->unique()
        ->map(fn ($id) => (int) $id)
        ->values();


    /*
    |--------------------------------------------------------------------------
    | GET STANDARD-WISE SUBJECT MAPPINGS
    |--------------------------------------------------------------------------
    |
    | This is the same source used by Standard Wise Subject Mapping.
    |
    */

    $standardSubjects = collect();

    if ($standardIds->isNotEmpty()) {

        $standardSubjects = DB::table(
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
    | GET EXAM SUBJECT CONFIGURATION
    |--------------------------------------------------------------------------
    |
    | We need the subjects actually selected for each Exam.
    |
    */

    $examIds = $allocations
        ->flatMap(function ($allocation) {

            return $allocation
                ->subjectAllocations
                ->pluck('exam_master_id');

        })
        ->filter()
        ->unique()
        ->map(fn ($id) => (int) $id)
        ->values();


    $examSubjectRows = collect();

    if ($examIds->isNotEmpty()) {

        $examSubjectRows =
            ExamMasterSubject::whereIn(
                'exam_master_id',
                $examIds
            )
            ->orderBy('display_order')
            ->orderBy('id')
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


    /*
    |--------------------------------------------------------------------------
    | GROUP EXAM SUBJECTS
    |--------------------------------------------------------------------------
    */

    $examSubjectsByExam =
        $examSubjectRows->groupBy(
            'exam_master_id'
        );


    /*
    |--------------------------------------------------------------------------
    | BUILD DISPLAY SUBJECTS
    |--------------------------------------------------------------------------
    */

    foreach ($allocations as $allocation) {

        $allocation->displaySubjects =
            collect();


        /*
        |--------------------------------------------------------------------------
        | CURRENT STANDARD MAPPINGS
        |--------------------------------------------------------------------------
        */

        $currentMappings =
            $standardSubjects->get(
                (int) $allocation->standard_id,
                collect()
            );


        /*
        |--------------------------------------------------------------------------
        | EACH TEACHER SUBJECT ALLOCATION
        |--------------------------------------------------------------------------
        */

        foreach (
            $allocation->subjectAllocations
            as $subjectAllocation
        ) {

            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */

            $statusRecord =
                $statusSubjectMap->get(
                    $subjectAllocation->id
                );


            /*
            |--------------------------------------------------------------------------
            | EXAM ID
            |--------------------------------------------------------------------------
            */

            $examId =
                (int) (
                    $subjectAllocation->exam_master_id
                    ?? $statusRecord?->exam_master_id
                    ?? 0
                );


            /*
            |--------------------------------------------------------------------------
            | SUBJECT IDS FROM OLD / NEW DATA
            |--------------------------------------------------------------------------
            */

            $tsaSubjectId =
                (int) (
                    $subjectAllocation->subject_id
                    ?? 0
                );


            $tmsSubjectId =
                (int) (
                    $statusRecord?->subject_id
                    ?? 0
                );


            /*
            |--------------------------------------------------------------------------
            | EXAM SUBJECT RECORDS
            |--------------------------------------------------------------------------
            */

            $examSubjects =
                $examSubjectsByExam->get(
                    $examId,
                    collect()
                );


            /*
            |--------------------------------------------------------------------------
            | RESOLVE SUBJECT
            |--------------------------------------------------------------------------
            |
            | Priority:
            |
            | 1. Exam subject subject_id = subjects.id
            | 2. Exam subject subject_id = old sws.id
            | 3. TSA subject ID against current mapping
            | 4. TMS subject ID against current mapping
            |
            */

            $examSubject = null;
            $mappedSubject = null;


            /*
            |--------------------------------------------------------------------------
            | 1. TRY TMS ID AS subjects.id AGAINST EXAM SUBJECTS
            |--------------------------------------------------------------------------
            */

            if ($tmsSubjectId > 0) {

                $examSubject =
                    $examSubjects->first(
                        function ($row) use (
                            $tmsSubjectId
                        ) {

                            return (int)
                                $row->subject_id
                                === $tmsSubjectId;
                        }
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | 2. TRY TSA ID AS subjects.id
            |--------------------------------------------------------------------------
            */

            if (!$examSubject && $tsaSubjectId > 0) {

                $examSubject =
                    $examSubjects->first(
                        function ($row) use (
                            $tsaSubjectId
                        ) {

                            return (int)
                                $row->subject_id
                                === $tsaSubjectId;
                        }
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | 3. OLD DATA:
            |
            | exam_master_subjects.subject_id may be sws.id
            |--------------------------------------------------------------------------
            */

            if (!$examSubject && $tmsSubjectId > 0) {

                $examSubject =
                    $examSubjects->first(
                        function ($row) use (
                            $tmsSubjectId
                        ) {

                            return
                                (int) $row->subject_id
                                ===
                                (int) $tmsSubjectId;
                        }
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | 4. STANDARD-WISE MAPPING USING TMS ID
            |--------------------------------------------------------------------------
            */

            if (!$mappedSubject && $tmsSubjectId > 0) {

                $mappedSubject =
                    $currentMappings->first(
                        function ($mapping) use (
                            $tmsSubjectId
                        ) {

                            return
                                (int) $mapping->subject_id
                                ===
                                $tmsSubjectId;
                        }
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | 5. STANDARD-WISE MAPPING USING TSA ID
            |--------------------------------------------------------------------------
            */

            if (!$mappedSubject && $tsaSubjectId > 0) {

                $mappedSubject =
                    $currentMappings->first(
                        function ($mapping) use (
                            $tsaSubjectId
                        ) {

                            return
                                (int) $mapping->subject_id
                                ===
                                $tsaSubjectId;
                        }
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | 6. OLD TSA/TMS VALUE MAY BE sws.id
            |--------------------------------------------------------------------------
            */

            if (!$mappedSubject && $tmsSubjectId > 0) {

                $mappedSubject =
                    $currentMappings->first(
                        function ($mapping) use (
                            $tmsSubjectId
                        ) {

                            return
                                (int) $mapping->mapping_id
                                ===
                                $tmsSubjectId;
                        }
                    );
            }


            if (!$mappedSubject && $tsaSubjectId > 0) {

                $mappedSubject =
                    $currentMappings->first(
                        function ($mapping) use (
                            $tsaSubjectId
                        ) {

                            return
                                (int) $mapping->mapping_id
                                ===
                                $tsaSubjectId;
                        }
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | DETERMINE FINAL SUBJECT
            |--------------------------------------------------------------------------
            */

            if ($mappedSubject) {

                $correctSubjectId =
                    (int) $mappedSubject->subject_id;

                $subjectName =
                    $mappedSubject->subject_name ?: '-';

                $subjectCode =
                    $mappedSubject->subject_code ?: '';

                $shortName =
                    $mappedSubject->short_name ?: '';

                $sortOrder =
                    (int) $mappedSubject->sort_order;

            } elseif ($examSubject) {

                /*
                |--------------------------------------------------------------------------
                | EXAM SUBJECT EXISTS
                |--------------------------------------------------------------------------
                */

                $examStoredSubjectId =
                    (int) $examSubject->subject_id;


                /*
                | Try as real subjects.id
                */

                $subject =
                    Subject::where(
                        'id',
                        $examStoredSubjectId
                    )
                    ->where(
                        'is_active',
                        1
                    )
                    ->first();


                if ($subject) {

                    $correctSubjectId =
                        (int) $subject->id;

                    $subjectName =
                        $subject->subject_name ?: '-';

                    $subjectCode =
                        $subject->subject_code ?: '';

                    $shortName =
                        $subject->short_name ?: '';

                    $sortOrder = 9999;

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | OLD VALUE WAS sws.id
                    |--------------------------------------------------------------------------
                    */

                    $oldMapping =
                        $currentMappings->first(
                            function ($mapping) use (
                                $examStoredSubjectId
                            ) {

                                return
                                    (int) $mapping->mapping_id
                                    ===
                                    $examStoredSubjectId;
                            }
                        );


                    if ($oldMapping) {

                        $correctSubjectId =
                            (int) $oldMapping->subject_id;

                        $subjectName =
                            $oldMapping->subject_name ?: '-';

                        $subjectCode =
                            $oldMapping->subject_code ?: '';

                        $shortName =
                            $oldMapping->short_name ?: '';

                        $sortOrder =
                            (int) $oldMapping->sort_order;

                    } else {

                        $correctSubjectId =
                            $examStoredSubjectId;

                        $subjectName =
                            $examSubject->subject_name ?: '-';

                        $subjectCode = '';

                        $shortName = '';

                        $sortOrder =
                            (int) (
                                $examSubject->display_order
                                ?? 9999
                            );
                    }
                }

            } else {

                /*
                |--------------------------------------------------------------------------
                | FINAL FALLBACK
                |--------------------------------------------------------------------------
                */

                $correctSubjectId =
                    $tmsSubjectId ?: $tsaSubjectId;

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


                if ($subject) {

                    $subjectName =
                        $subject->subject_name ?: '-';

                    $subjectCode =
                        $subject->subject_code ?: '';

                    $shortName =
                        $subject->short_name ?: '';

                } else {

                    $subjectName = '-';

                    $subjectCode = '';

                    $shortName = '';
                }

                $sortOrder = 9999;
            }


            /*
            |--------------------------------------------------------------------------
            | ADD TO DISPLAY
            |--------------------------------------------------------------------------
            */

            $allocation->displaySubjects->push(
                (object) [

                    'teacher_subject_allocation_id' =>
                        $subjectAllocation->id,

                    'subject_id' =>
                        $correctSubjectId,

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
                        ?? 'PENDING',

                    'exam_master_id' =>
                        $examId,
                ]
            );
        }


        /*
        |--------------------------------------------------------------------------
        | SORT + REMOVE DUPLICATES
        |--------------------------------------------------------------------------
        */

        $allocation->displaySubjects =
            $allocation->displaySubjects
                ->unique(
                    function ($subject) {

                        return
                            $subject->teacher_subject_allocation_id;
                    }
                )
                ->sortBy([
                    ['sort_order', 'asc'],
                    ['subject_name', 'asc'],
                ])
                ->values();
    }


    /*
    |--------------------------------------------------------------------------
    | RETURN
    |--------------------------------------------------------------------------
    */

    return view(
        'administrator.teacher-bulk-allocation.index',
        compact(
            'allocations',
            'standards',
            'exams',
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
        $teachers = User::where(
            'role',
            'Teacher'
        )
            ->where(
                'is_active',
                1
            )
            ->orderBy('name')
            ->get();


        $academicYears = AcademicYear::orderBy(
            'year_name'
        )->get();


        $sections = Section::orderBy(
            'display_order'
        )->get();


        $standards = Standard::where(
            'is_active',
            1
        )
            ->orderBy('display_order')
            ->get();


        $divisions = Division::where(
            'is_active',
            1
        )
            ->orderBy('display_order')
            ->get();


        $exams = ExamMaster::where(
            'is_active',
            1
        )
            ->orderBy('display_order')
            ->orderBy('exam_name')
            ->get();


        return view(
            'administrator.teacher-bulk-allocation.create',
            compact(
                'teachers',
                'academicYears',
                'sections',
                'standards',
                'divisions',
                'exams'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GET EXAM MAPPED SUBJECTS
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | We DO NOT require exam_master_subjects.standard_id
    | to match the Standard anymore.
    |
    | The Exam itself already has standard_id.
    |
    | Subject names always come from:
    |
    | standard_wise_subjects -> subjects
    |
    */

    private function getExamMappedSubjects($examMasterId, $standardId = null)
    {
        if (!$examMasterId) {
            return collect();
        }

        /*
        |--------------------------------------------------------------------------
        | GET EXAM
        |--------------------------------------------------------------------------
        */

        $exam = ExamMaster::find($examMasterId);

        if (!$exam) {
            return collect();
        }

        /*
        |--------------------------------------------------------------------------
        | STANDARD
        |--------------------------------------------------------------------------
        */

        $standardId = $standardId
            ? (int) $standardId
            : (int) $exam->standard_id;

        if (!$standardId) {
            return collect();
        }

        /*
        |--------------------------------------------------------------------------
        | VERIFY EXAM / STANDARD
        |--------------------------------------------------------------------------
        */

        if (
            $exam->standard_id &&
            (int) $exam->standard_id !== $standardId
        ) {
            return collect();
        }

        /*
        |--------------------------------------------------------------------------
        | LOAD EXAM SUBJECT CONFIGURATION
        |--------------------------------------------------------------------------
        |
        | exam_master_subjects.subject_id may contain either:
        |
        | 1. subjects.id (current format)
        | 2. standard_wise_subjects.id (legacy format)
        |
        | Do not filter the standard-wise mapping query by these IDs yet.
        | We must be able to test BOTH representations.
        |
        |--------------------------------------------------------------------------
        */

        $examSubjects = ExamMasterSubject::query()
            ->where(
                'exam_master_id',
                $examMasterId
            )
            ->whereNotNull('subject_id')
            ->orderBy('display_order')
            ->orderBy('id')
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

        if ($examSubjects->isEmpty()) {
            return collect();
        }

        /*
        |--------------------------------------------------------------------------
        | LOAD ALL STANDARD-WISE SUBJECT MAPPINGS
        |--------------------------------------------------------------------------
        */

        $standardMappings = DB::table(
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
            ->orderBy('sws.sort_order')
            ->orderBy('sws.id')
            ->get();

        if ($standardMappings->isEmpty()) {
            return collect();
        }

        /*
        |--------------------------------------------------------------------------
        | STANDARD NAME
        |--------------------------------------------------------------------------
        */

        $standardName = optional(
            Standard::find($standardId)
        )->standard_name ?? '';

        /*
        |--------------------------------------------------------------------------
        | BUILD SUBJECT LIST
        |--------------------------------------------------------------------------
        */

        $subjects = collect();

        foreach ($examSubjects as $examSubject) {

            $storedSubjectId = (int) $examSubject->subject_id;

            if ($storedSubjectId <= 0) {
                continue;
            }

            $mappedSubject = null;

            /*
            |--------------------------------------------------------------------------
            | CASE 1: CURRENT FORMAT
            | exam_master_subjects.subject_id = subjects.id
            |--------------------------------------------------------------------------
            */

            $mappedSubject = $standardMappings->first(
                function ($mapping) use ($storedSubjectId) {
                    return (int) $mapping->subject_id === $storedSubjectId;
                }
            );

            /*
            |--------------------------------------------------------------------------
            | CASE 2: LEGACY FORMAT
            | exam_master_subjects.subject_id = standard_wise_subjects.id
            |--------------------------------------------------------------------------
            */

            if (!$mappedSubject) {

                $mappedSubject = $standardMappings->first(
                    function ($mapping) use ($storedSubjectId) {
                        return (int) $mapping->mapping_id === $storedSubjectId;
                    }
                );
            }

            /*
            |--------------------------------------------------------------------------
            | CASE 3: DIRECT SUBJECT FALLBACK
            |--------------------------------------------------------------------------
            |
            | If the exam subject uses a valid subjects.id but that subject has
            | no current standard_wise_subjects row, still allow the subject to
            | appear in the Edit page. Store the real subjects.id.
            |
            |--------------------------------------------------------------------------
            */

            if (!$mappedSubject) {

                $directSubject = Subject::query()
                    ->where(
                        'id',
                        $storedSubjectId
                    )
                    ->where(
                        'is_active',
                        1
                    )
                    ->first();

                if ($directSubject) {

                    $subjects->push(
                        (object) [
                            'mapping_id' => null,
                            'standard_id' => $standardId,
                            'subject_id' => (int) $directSubject->id,
                            'standard_name' => $standardName,
                            'subject_name' => $directSubject->subject_name ?: '-',
                            'subject_code' => $directSubject->subject_code ?: '',
                            'short_name' => $directSubject->short_name ?: '',
                            'is_optional' => 0,
                            'sort_order' => (int) ($examSubject->display_order ?? 9999),
                            'exam_subject_id' => $examSubject->id,
                            'max_marks' => $examSubject->max_marks,
                            'passing_marks' => $examSubject->passing_marks,
                            'display_order' => (int) ($examSubject->display_order ?? 9999),
                        ]
                    );
                }

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | ADD MAPPED SUBJECT
            |--------------------------------------------------------------------------
            */

            $subjects->push(
                (object) [
                    'mapping_id' => (int) $mappedSubject->mapping_id,
                    'standard_id' => (int) $mappedSubject->standard_id,
                    'subject_id' => (int) $mappedSubject->subject_id,
                    'standard_name' => $mappedSubject->standard_name ?: $standardName,
                    'subject_name' => $mappedSubject->subject_name ?: ($examSubject->subject_name ?: '-'),
                    'subject_code' => $mappedSubject->subject_code ?: '',
                    'short_name' => $mappedSubject->short_name ?: '',
                    'is_optional' => (int) ($mappedSubject->is_optional ?? 0),
                    'sort_order' => (int) ($mappedSubject->sort_order ?? 9999),
                    'exam_subject_id' => $examSubject->id,
                    'max_marks' => $examSubject->max_marks,
                    'passing_marks' => $examSubject->passing_marks,
                    'display_order' => (int) (
                        $examSubject->display_order
                        ?? $mappedSubject->sort_order
                        ?? 9999
                    ),
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | REMOVE DUPLICATES + SORT
        |--------------------------------------------------------------------------
        */

        return $subjects
            ->unique(
                function ($subject) {
                    return $subject->subject_id;
                }
            )
            ->sortBy([
                ['display_order', 'asc'],
                ['sort_order', 'asc'],
                ['subject_id', 'asc'],
            ])
            ->values();
    }

    /*
    |--------------------------------------------------------------------------
    | GET EXAM DETAILS
    |--------------------------------------------------------------------------
    */

   public function getExamDetails(Request $request)
{
    $request->validate([
        'exam_master_id' =>
            'required|exists:exam_masters,id',
    ]);

    $exam = ExamMaster::findOrFail(
        $request->exam_master_id
    );

    $standard = Standard::where(
        'id',
        $exam->standard_id
    )
        ->where(
            'is_active',
            1
        )
        ->first();

    if (!$standard) {
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

    if ($standard->section_id) {
        $section = Section::find(
            $standard->section_id
        );
    }

    $divisions = Division::where(
        'is_active',
        1
    )
        ->orderBy('display_order')
        ->get([
            'id',
            'division_name',
        ]);

    /*
    |--------------------------------------------------------------------------
    | SAME QUERY AS getSubjects()
    |--------------------------------------------------------------------------
    */

    $subjects = DB::table(
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
        ->innerJoin(
            'exam_master_subjects as ems',
            function ($join) use ($exam, $standard) {

                $join->on(
                    'ems.subject_id',
                    '=',
                    'sws.subject_id'
                );

                $join->where(
                    'ems.exam_master_id',
                    '=',
                    $exam->id
                );

                $join->where(
                    'ems.standard_id',
                    '=',
                    $standard->id
                );
            }
        )
        ->where(
            'sws.standard_id',
            $standard->id
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
            'sws.subject_id',
            'st.standard_name',
            's.subject_name',
            's.subject_code',
            's.short_name',
            'sws.is_optional',
            'sws.sort_order',

            'ems.id as exam_subject_id',
            'ems.max_marks',
            'ems.passing_marks',
            'ems.display_order',
        ])
        ->orderBy('sws.sort_order')
        ->orderBy('sws.id')
        ->get();

    return response()->json([

        'success' => true,

        'exam' => [
            'id' =>
                $exam->id,

            'exam_name' =>
                $exam->exam_name,
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
            $subjects->map(function ($subject) {

                return [

                    'subject_id' =>
                        (int) $subject->subject_id,

                    'subject_name' =>
                        $subject->subject_name ?: '-',

                    'subject_code' =>
                        $subject->subject_code ?: '',

                    'short_name' =>
                        $subject->short_name ?: '',

                    'is_optional' =>
                        (int) $subject->is_optional,

                    'exam_subject_id' =>
                        (int) $subject->exam_subject_id,

                    'max_marks' =>
                        $subject->max_marks,

                    'passing_marks' =>
                        $subject->passing_marks,

                    'display_order' =>
                        $subject->display_order
                        ?? $subject->sort_order,
                ];
            })->values(),
    ]);
}


    /*
    |--------------------------------------------------------------------------
    | GET STANDARDS
    |--------------------------------------------------------------------------
    */

    public function getStandards(Request $request)
    {
        $sectionId =
            $request->input('section_id');


        if (!$sectionId) {
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

    public function getSubjects(Request $request)
{
    $examMasterId = $request->input('exam_master_id');

    if (!$examMasterId) {
        return response()->json([
            'success' => false,
            'message' => 'Exam is required.',
            'subjects' => [],
        ], 422);
    }

    /*
    |--------------------------------------------------------------------------
    | GET EXAM
    |--------------------------------------------------------------------------
    */

    $exam = ExamMaster::find($examMasterId);

    if (!$exam) {
        return response()->json([
            'success' => false,
            'message' => 'Selected Exam was not found.',
            'subjects' => [],
        ], 404);
    }

    /*
    |--------------------------------------------------------------------------
    | GET STANDARD FROM EXAM
    |--------------------------------------------------------------------------
    */

    $standardId = (int) $exam->standard_id;

    if (!$standardId) {
        return response()->json([
            'success' => false,
            'message' => 'No Standard is assigned to this Exam.',
            'subjects' => [],
        ], 422);
    }

    /*
    |--------------------------------------------------------------------------
    | GET STANDARD
    |--------------------------------------------------------------------------
    */

    $standard = Standard::find($standardId);

    if (!$standard) {
        return response()->json([
            'success' => false,
            'message' => 'Standard not found.',
            'subjects' => [],
        ], 404);
    }

    /*
    |--------------------------------------------------------------------------
    | GET EXAM SUBJECT ROWS
    |--------------------------------------------------------------------------
    |
    | These are the subjects checked on Exam Master.
    |
    */

    $examSubjects = ExamMasterSubject::where(
        'exam_master_id',
        $examMasterId
    )
        ->orderBy('display_order')
        ->orderBy('id')
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

    /*
    |--------------------------------------------------------------------------
    | NO SUBJECTS
    |--------------------------------------------------------------------------
    */

    if ($examSubjects->isEmpty()) {

        return response()->json([
            'success' => true,

            'exam' => [
                'id' => $exam->id,
                'exam_name' => $exam->exam_name,
            ],

            'standard' => [
                'id' => $standard->id,
                'standard_name' => $standard->standard_name,
            ],

            'subjects' => [],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CURRENT STANDARD-WISE SUBJECT MAPPING
    |--------------------------------------------------------------------------
    |
    | This is the authoritative source:
    |
    | standard_wise_subjects
    |        ↓
    | subjects.id
    |        ↓
    | subjects.subject_name
    |
    */

    $standardMappings = DB::table(
        'standard_wise_subjects as sws'
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
            's.subject_name',
            's.subject_code',
            's.short_name',
            'sws.is_optional',
            'sws.sort_order',
        ])
        ->orderBy('sws.sort_order')
        ->orderBy('sws.id')
        ->get();

    /*
    |--------------------------------------------------------------------------
    | MAP SUBJECTS
    |--------------------------------------------------------------------------
    */

    $subjects = collect();

    foreach ($examSubjects as $examSubject) {

        $storedSubjectId = (int) $examSubject->subject_id;

        if (!$storedSubjectId) {
            continue;
        }

        $mappedSubject = null;

        /*
        |--------------------------------------------------------------------------
        | CASE 1
        |--------------------------------------------------------------------------
        |
        | Correct database:
        |
        | exam_master_subjects.subject_id = subjects.id
        |
        */

        $mappedSubject = $standardMappings->first(
            function ($mapping) use ($storedSubjectId) {
                return (int) $mapping->subject_id === $storedSubjectId;
            }
        );

        /*
        |--------------------------------------------------------------------------
        | CASE 2
        |--------------------------------------------------------------------------
        |
        | Old database:
        |
        | exam_master_subjects.subject_id = standard_wise_subjects.id
        |
        | Your existing data contains this format for some exams.
        |
        */

        if (!$mappedSubject) {

            $mappedSubject = $standardMappings->first(
                function ($mapping) use ($storedSubjectId) {
                    return (int) $mapping->mapping_id === $storedSubjectId;
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SKIP INVALID SUBJECT
        |--------------------------------------------------------------------------
        */

        if (!$mappedSubject) {
            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | ADD SUBJECT
        |--------------------------------------------------------------------------
        */

        $subjects->push([
            'mapping_id' =>
                (int) $mappedSubject->mapping_id,

            /*
            | IMPORTANT:
            | This is ACTUAL subjects.id
            */
            'subject_id' =>
                (int) $mappedSubject->subject_id,

            'standard' =>
                $standard->standard_name,

            'subject_name' =>
                $mappedSubject->subject_name ?: '-',

            'subject_code' =>
                $mappedSubject->subject_code ?: '',

            'short_name' =>
                $mappedSubject->short_name ?: '',

            'is_optional' =>
                (int) $mappedSubject->is_optional,

            'max_marks' =>
                $examSubject->max_marks,

            'passing_marks' =>
                $examSubject->passing_marks,

            'display_order' =>
                $examSubject->display_order
                ?? $mappedSubject->sort_order,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SORT
    |--------------------------------------------------------------------------
    */

    $subjects = $subjects
        ->sortBy([
            ['display_order', 'asc'],
            ['subject_id', 'asc'],
        ])
        ->values();

    /*
    |--------------------------------------------------------------------------
    | RETURN
    |--------------------------------------------------------------------------
    */

    return response()->json([
        'success' => true,

        'exam' => [
            'id' =>
                $exam->id,

            'exam_name' =>
                $exam->exam_name,
        ],

        'standard' => [
            'id' =>
                $standard->id,

            'standard_name' =>
                $standard->standard_name,
        ],

        'subjects' =>
            $subjects,
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


        /*
        |--------------------------------------------------------------------------
        | CURRENT STANDARD-WISE SUBJECT
        |--------------------------------------------------------------------------
        */

        $standardSubject = DB::table(
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


        if (!$standardSubject) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | SUBJECT MASTER
        |--------------------------------------------------------------------------
        */

        $subject = Subject::where(
            'id',
            $standardSubject->subject_id
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
        | VERIFY SUBJECT BELONGS TO EXAM
        |--------------------------------------------------------------------------
        |
        | DO NOT require standard_id here.
        |
        */

        $examSubject = ExamMasterSubject::where(
            'exam_master_id',
            $examMasterId
        )
            ->where(
                'subject_id',
                $subject->id
            )
            ->first();


        if (!$examSubject) {
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

    public function store(Request $request)
    {
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

        ], [

            'user_id.required' =>
                'Please select Teacher.',

            'academic_year_id.required' =>
                'Please select Academic Year.',

            'exam_master_id.required' =>
                'Please select Exam.',

            'rows.required' =>
                'Please add at least one Allocation Row.',

            'rows.*.standards.required' =>
                'Please select Standard.',

            'rows.*.divisions.required' =>
                'Please select at least one Division.',

            'rows.*.subjects.required' =>
                'Please select at least one Subject.',
        ]);


        try {

            DB::transaction(function () use ($request) {

                $exam = ExamMaster::findOrFail(
                    $request->exam_master_id
                );


                foreach ($request->rows as $row) {

                    foreach ($row['standards'] as $standardId) {

                        $standard = Standard::findOrFail(
                            $standardId
                        );


                        /*
                        |--------------------------------------------------------------------------
                        | EXAM/STANDARD VALIDATION
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $exam->standard_id &&
                            (int) $exam->standard_id !==
                            (int) $standard->id
                        ) {

                            throw new \Exception(
                                'Selected Standard does not belong to the selected Exam.'
                            );
                        }


                        if (!$standard->section_id) {

                            throw new \Exception(
                                "Section is not assigned to Standard {$standard->standard_name}."
                            );
                        }


                        foreach ($row['divisions'] as $divisionId) {

                            $division =
                                Division::findOrFail(
                                    $divisionId
                                );


                            /*
                            |--------------------------------------------------------------------------
                            | CLASS ALLOCATION
                            |--------------------------------------------------------------------------
                            */

                            $classAllocation =
                                TeacherClassAllocation::firstOrCreate(

                                    [
                                        'user_id' =>
                                            $request->user_id,

                                        'academic_year_id' =>
                                            $request->academic_year_id,

                                        'section_id' =>
                                            $standard->section_id,

                                        'standard_id' =>
                                            $standard->id,

                                        'division_id' =>
                                            $division->id,
                                    ],

                                    [
                                        'is_class_teacher' =>
                                            !empty(
                                                $row['is_class_teacher']
                                            ),
                                    ]
                                );


                            /*
                            |--------------------------------------------------------------------------
                            | SUBJECT ALLOCATIONS
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


                                if (!$resolved) {

                                    throw new \Exception(
                                        "Subject ID {$selectedSubjectId} is not valid for {$standard->standard_name} and selected Exam."
                                    );
                                }


                                $subject =
                                    $resolved['subject'];


                                /*
                                |--------------------------------------------------------------------------
                                | DUPLICATE CHECK
                                |--------------------------------------------------------------------------
                                */

                                $exists =
                                    TeacherSubjectAllocation::where(
                                        'teacher_class_allocation_id',
                                        $classAllocation->id
                                    )
                                    ->where(
                                        'subject_id',
                                        $subject->id
                                    )
                                    ->where(
                                        'exam_master_id',
                                        $exam->id
                                    )
                                    ->exists();


                                if ($exists) {

                                    throw new \Exception(
                                        "Allocation already exists for {$standard->standard_name} - {$division->division_name} - {$subject->subject_name}"
                                    );
                                }


                                /*
                                |--------------------------------------------------------------------------
                                | CREATE TEACHER SUBJECT ALLOCATION
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
                                | CREATE MARK STATUS
                                |--------------------------------------------------------------------------
                                */

                                TeacherMarksStatus::create([

                                    'academic_year_id' =>
                                        $request->academic_year_id,

                                    'exam_master_id' =>
                                        $exam->id,

                                    'teacher_subject_allocation_id' =>
                                        $subjectAllocation->id,

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
                        }
                    }
                }
            });


            return redirect()
                ->route(
                    'teacher-bulk-allocation.index'
                )
                ->with(
                    'success',
                    'Teacher Bulk Allocation Saved Successfully.'
                );

        } catch (\Throwable $e) {

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
    | EDIT
    |--------------------------------------------------------------------------
    */

    /*
|--------------------------------------------------------------------------
| EDIT
|--------------------------------------------------------------------------
*/

public function edit($id)
{
    /*
    |--------------------------------------------------------------------------
    | LOAD CLASS ALLOCATION
    |--------------------------------------------------------------------------
    */

    $allocation = TeacherClassAllocation::findOrFail($id);


    /*
    |--------------------------------------------------------------------------
    | LOAD MASTER DATA
    |--------------------------------------------------------------------------
    */

    $teachers = User::where('role', 'Teacher')
        ->where('is_active', 1)
        ->orderBy('name')
        ->get();


    $academicYears = AcademicYear::orderBy('year_name')
        ->get();


    $standard = Standard::findOrFail(
        $allocation->standard_id
    );


    $divisions = Division::where('is_active', 1)
        ->orderBy('display_order')
        ->get();


    /*
    |--------------------------------------------------------------------------
    | LOAD EXAMS FOR THIS STANDARD
    |--------------------------------------------------------------------------
    |
    | The Standard is already fixed for an existing allocation.
    | Therefore show exams belonging to this Standard.
    |
    */

    $exams = ExamMaster::where('is_active', 1)
        ->where('standard_id', $allocation->standard_id)
        ->orderBy('display_order')
        ->orderBy('exam_name')
        ->get();


    /*
    |--------------------------------------------------------------------------
    | LOAD EXISTING TEACHER SUBJECT ALLOCATIONS
    |--------------------------------------------------------------------------
    */

    $existingSubjectAllocations =
        TeacherSubjectAllocation::where(
            'teacher_class_allocation_id',
            $allocation->id
        )
        ->orderBy('id')
        ->get();


    /*
    |--------------------------------------------------------------------------
    | DETERMINE SELECTED EXAM
    |--------------------------------------------------------------------------
    |
    | TeacherClassAllocation itself does not contain exam_master_id.
    | The exam is stored in teacher_subject_allocations.
    |
    */

    $selectedExamId =
        $existingSubjectAllocations
            ->pluck('exam_master_id')
            ->filter()
            ->first();


    /*
    |--------------------------------------------------------------------------
    | SELECTED SUBJECT IDS
    |--------------------------------------------------------------------------
    |
    | These are REAL subjects.id values.
    |
    */

    $selectedSubjects =
        $existingSubjectAllocations
            ->pluck('subject_id')
            ->filter()
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values()
            ->toArray();


    /*
    |--------------------------------------------------------------------------
    | LOAD SUBJECTS
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | Subject list comes from:
    |
    | exam_master_subjects
    |          ↓
    | standard_wise_subjects
    |          ↓
    | subjects
    |
    | getExamMappedSubjects() already handles:
    |
    | 1. subjects.id
    | 2. legacy standard_wise_subjects.id
    |
    */

    $subjects = collect();

    if ($selectedExamId) {

        $subjects = $this->getExamMappedSubjects(
            $selectedExamId,
            $allocation->standard_id
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FALLBACK
    |--------------------------------------------------------------------------
    |
    | If there is no existing exam yet, load all active subjects
    | mapped to this Standard.
    |
    */

    if (!$selectedExamId || $subjects->isEmpty()) {

        $subjects = DB::table(
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
    | NORMALIZE SUBJECT OBJECTS
    |--------------------------------------------------------------------------
    |
    | This makes the Blade receive:
    |
    | subject_id
    | subject_name
    | subject_code
    | short_name
    | sort_order
    |
    */

    $subjects = collect($subjects)
        ->map(function ($subject) {

            return (object) [

                'subject_id' =>
                    (int) (
                        $subject->subject_id
                        ?? $subject->id
                        ?? 0
                    ),

                'subject_name' =>
                    $subject->subject_name
                    ?? '-',

                'subject_code' =>
                    $subject->subject_code
                    ?? '',

                'short_name' =>
                    $subject->short_name
                    ?? '',

                'is_optional' =>
                    (int) (
                        $subject->is_optional
                        ?? 0
                    ),

                'sort_order' =>
                    (int) (
                        $subject->sort_order
                        ?? $subject->display_order
                        ?? 9999
                    ),
            ];
        })
        ->filter(function ($subject) {

            return $subject->subject_id > 0;
        })
        ->unique('subject_id')
        ->sortBy([
            ['sort_order', 'asc'],
            ['subject_name', 'asc'],
        ])
        ->values();


    /*
    |--------------------------------------------------------------------------
    | RETURN EDIT VIEW
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | Your index/create views are under:
    |
    | resources/views/administrator/teacher-bulk-allocation/
    |
    | Therefore edit must use:
    |
    | administrator.teacher-bulk-allocation.edit
    |
    */

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
        | EXAM/STANDARD CHECK
        |--------------------------------------------------------------------------
        */

        if (
            $exam->standard_id &&
            (int) $exam->standard_id !==
            (int) $request->standard_id
        ) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Selected Exam does not belong to selected Standard.'
                );
        }


        if (!$standard->section_id) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Section is not assigned to selected Standard.'
                );
        }


        DB::transaction(
            function () use (
                $request,
                $allocation,
                $exam,
                $standard
            ) {

                /*
                |--------------------------------------------------------------------------
                | UPDATE CLASS ALLOCATION
                |--------------------------------------------------------------------------
                */

                $allocation->update([

                    'user_id' =>
                        $request->user_id,

                    'academic_year_id' =>
                        $request->academic_year_id,

                    'section_id' =>
                        $standard->section_id,

                    'standard_id' =>
                        $standard->id,

                    'division_id' =>
                        $request->division_id,
                ]);


                /*
                |--------------------------------------------------------------------------
                | DELETE OLD STATUS
                |--------------------------------------------------------------------------
                */

                $oldIds =
                    TeacherSubjectAllocation::where(
                        'teacher_class_allocation_id',
                        $allocation->id
                    )
                    ->pluck('id');


                if ($oldIds->isNotEmpty()) {

                    TeacherMarksStatus::whereIn(
                        'teacher_subject_allocation_id',
                        $oldIds
                    )
                    ->delete();
                }


                /*
                |--------------------------------------------------------------------------
                | DELETE OLD SUBJECT ALLOCATIONS
                |--------------------------------------------------------------------------
                */

                TeacherSubjectAllocation::where(
                    'teacher_class_allocation_id',
                    $allocation->id
                )
                ->delete();


                /*
                |--------------------------------------------------------------------------
                | CREATE NEW SUBJECT ALLOCATIONS
                |--------------------------------------------------------------------------
                */

                foreach (
                    $request->subjects
                    as $subjectId
                ) {

                    $resolved =
                        $this->resolveSubject(
                            $subjectId,
                            $standard->id,
                            $exam->id
                        );


                    if (!$resolved) {

                        throw new \Exception(
                            "Subject ID {$subjectId} is not valid for selected Exam/Standard."
                        );
                    }


                    $subject =
                        $resolved['subject'];


                    /*
                    |--------------------------------------------------------------------------
                    | SUBJECT ALLOCATION
                    |--------------------------------------------------------------------------
                    */

                    $subjectAllocation =
                        TeacherSubjectAllocation::create([

                            'teacher_class_allocation_id' =>
                                $allocation->id,

                            'subject_id' =>
                                $subject->id,

                            'exam_master_id' =>
                                $exam->id,
                        ]);


                    /*
                    |--------------------------------------------------------------------------
                    | MARK STATUS
                    |--------------------------------------------------------------------------
                    */

                    TeacherMarksStatus::create([

                        'academic_year_id' =>
                            $request->academic_year_id,

                        'exam_master_id' =>
                            $exam->id,

                        'teacher_subject_allocation_id' =>
                            $subjectAllocation->id,

                        'standard_id' =>
                            $standard->id,

                        'division_id' =>
                            $request->division_id,

                        'subject_id' =>
                            $subject->id,

                        'teacher_id' =>
                            $request->user_id,

                        'status' =>
                            'PENDING',
                    ]);
                }
            }
        );


        return redirect()
            ->route(
                'teacher-bulk-allocation.index'
            )
            ->with(
                'success',
                'Allocation updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        DB::transaction(
            function () use ($id) {

                $allocation =
                    TeacherClassAllocation::findOrFail(
                        $id
                    );


                $subjectIds =
                    TeacherSubjectAllocation::where(
                        'teacher_class_allocation_id',
                        $allocation->id
                    )
                    ->pluck('id');


                if ($subjectIds->isNotEmpty()) {

                    TeacherMarksStatus::whereIn(
                        'teacher_subject_allocation_id',
                        $subjectIds
                    )
                    ->delete();
                }


                TeacherSubjectAllocation::where(
                    'teacher_class_allocation_id',
                    $allocation->id
                )
                ->delete();


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