<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\ExamMaster;
use App\Models\Standard;
use App\Models\Division;
use App\Models\Subject;
use App\Models\AcademicYear;

class ResultSheetController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        return view(
            'administrator.result-sheet.index',
            [
                'exams' => ExamMaster::orderByDesc('id')->get(),

                'standards' =>
                    Standard::orderBy('display_order')->get(),

                'divisions' =>
                    Division::orderBy('division_name')->get(),

                'academicYears' =>
                    AcademicYear::orderByDesc('id')->get(),

                'results' => collect(),

                'examSubjects' => collect(),

                'academicSubjects' => collect(),

                'coSubjects' => collect(),

                'showSkillColumn' => false,

                'academicYear' => null,

                'exam' => null,

                'standard' => null,

                'division' => null,

                'yearName' => null,

                'totalMaxMarks' => 0,

                'girlsSubjectAnalysis' => [],

                'boysSubjectAnalysis' => [],
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */

    public function search(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'academic_year_id' => ['required', 'integer'],
            'exam_master_id'   => ['required', 'integer'],
            'standard_id'      => ['required', 'integer'],
            'division_id'      => ['required', 'integer'],
        ]);


        /*
        |--------------------------------------------------------------------------
        | IDS
        |--------------------------------------------------------------------------
        */

        $academicYearId = (int) $request->academic_year_id;

        $examMasterId = (int) $request->exam_master_id;

        $standardId = (int) $request->standard_id;

        $divisionId = (int) $request->division_id;


        /*
        |--------------------------------------------------------------------------
        | MASTER DATA
        |--------------------------------------------------------------------------
        */

        $exams =
            ExamMaster::orderByDesc('id')->get();

        $standards =
            Standard::orderBy('display_order')->get();

        $divisions =
            Division::orderBy('division_name')->get();

        $academicYears =
            AcademicYear::orderByDesc('id')->get();


        $academicYear =
            AcademicYear::find($academicYearId);

        $exam =
            ExamMaster::find($examMasterId);

        $standard =
            Standard::find($standardId);

        $division =
            Division::find($divisionId);


        /*
        |--------------------------------------------------------------------------
        | CHECK GENERATED RESULT
        |--------------------------------------------------------------------------
        */

        $resultExists =
            DB::table('student_results')
                ->where('academic_year_id', $academicYearId)
                ->where('exam_master_id', $examMasterId)
                ->where('standard_id', $standardId)
                ->where('division_id', $divisionId)
                ->exists();


        if (!$resultExists) {

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Result is not generated for selected Academic Year, Exam, Standard and Division.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | STANDARD WISE SUBJECT ORDER
        |--------------------------------------------------------------------------
        */

        $standardWiseSubjects =
            DB::table('standard_wise_subjects')
                ->where('standard_id', $standardId)
                ->where('is_active', 1)
                ->orderBy('sort_order')
                ->get();


        /*
        |--------------------------------------------------------------------------
        | SUBJECT ORDER MAP
        |--------------------------------------------------------------------------
        */

        $subjectOrderMap = [];

        foreach ($standardWiseSubjects as $swSubject) {

            $name =
                strtoupper(
                    trim(
                        $swSubject->subject_name ?? ''
                    )
                );

            if ($name === '') {
                continue;
            }

            $subjectOrderMap[$name] = [
                'sort_order' =>
                    (int) ($swSubject->sort_order ?? 9999),

                'is_optional' =>
                    (int) ($swSubject->is_optional ?? 0),
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | GET ALL GENERATED SUBJECT IDS
        |--------------------------------------------------------------------------
        |
        | This is VERY important.
        |
        | We do NOT manually select Marathi / Sanskrit / English etc.
        |
        | Every subject actually present in student_result_details is loaded.
        |
        */

        $generatedSubjectIds =
            DB::table('student_result_details as srd')
                ->join(
                    'student_results as sr',
                    'sr.id',
                    '=',
                    'srd.student_result_id'
                )
                ->where(
                    'sr.academic_year_id',
                    $academicYearId
                )
                ->where(
                    'sr.exam_master_id',
                    $examMasterId
                )
                ->where(
                    'sr.standard_id',
                    $standardId
                )
                ->where(
                    'sr.division_id',
                    $divisionId
                )
                ->whereNotNull('srd.subject_id')
                ->select('srd.subject_id')
                ->distinct()
                ->pluck('subject_id')
                ->map(fn($id) => (int) $id)
                ->values();


        /*
        |--------------------------------------------------------------------------
        | LOAD SUBJECT MASTER
        |--------------------------------------------------------------------------
        */

        $subjects =
            $generatedSubjectIds->isNotEmpty()
                ? Subject::whereIn(
                    'id',
                    $generatedSubjectIds->toArray()
                )
                    ->get()
                    ->keyBy('id')
                : collect();


        /*
        |--------------------------------------------------------------------------
        | NORMALIZE SUBJECT NAME
        |--------------------------------------------------------------------------
        */

        $normalizeSubjectName = function ($name) {

            return strtoupper(
                preg_replace(
                    '/[^A-Z0-9]+/',
                    '',
                    trim($name ?? '')
                )
            );
        };


        /*
        |--------------------------------------------------------------------------
        | RAW SUBJECT COLLECTION
        |--------------------------------------------------------------------------
        */

        $rawSubjects = collect();


        foreach ($generatedSubjectIds as $subjectId) {

            $subjectId = (int) $subjectId;

            $subject =
                $subjects->get($subjectId);


            $subjectName =
                trim(
                    $subject->subject_name ?? ''
                );


            if ($subjectName === '') {

                $subjectName =
                    'Subject ' . $subjectId;
            }


            $subjectNameUpper =
                strtoupper($subjectName);


            /*
            |--------------------------------------------------------------------------
            | SORT ORDER
            |--------------------------------------------------------------------------
            */

            $sortOrder =
                $subjectOrderMap[$subjectNameUpper]['sort_order']
                ?? 9999;


            /*
            |--------------------------------------------------------------------------
            | OPTIONAL
            |--------------------------------------------------------------------------
            */

            $isOptional =
                $subjectOrderMap[$subjectNameUpper]['is_optional']
                ?? 0;


            /*
            |--------------------------------------------------------------------------
            | SUBJECT TYPE
            |--------------------------------------------------------------------------
            */

            $subjectTypeId =
                (int) (
                    $subject->subject_type_id ?? 1
                );


            /*
            |--------------------------------------------------------------------------
            | MAX MARKS
            |--------------------------------------------------------------------------
            */

            $maxMarks =
                DB::table('student_result_details as srd')
                    ->join(
                        'student_results as sr',
                        'sr.id',
                        '=',
                        'srd.student_result_id'
                    )
                    ->where(
                        'sr.academic_year_id',
                        $academicYearId
                    )
                    ->where(
                        'sr.exam_master_id',
                        $examMasterId
                    )
                    ->where(
                        'sr.standard_id',
                        $standardId
                    )
                    ->where(
                        'sr.division_id',
                        $divisionId
                    )
                    ->where(
                        'srd.subject_id',
                        $subjectId
                    )
                    ->max('srd.max_marks');


            /*
            |--------------------------------------------------------------------------
            | PASSING MARKS
            |--------------------------------------------------------------------------
            */

            $passingMarks =
                DB::table('student_result_details as srd')
                    ->join(
                        'student_results as sr',
                        'sr.id',
                        '=',
                        'srd.student_result_id'
                    )
                    ->where(
                        'sr.academic_year_id',
                        $academicYearId
                    )
                    ->where(
                        'sr.exam_master_id',
                        $examMasterId
                    )
                    ->where(
                        'sr.standard_id',
                        $standardId
                    )
                    ->where(
                        'sr.division_id',
                        $divisionId
                    )
                    ->where(
                        'srd.subject_id',
                        $subjectId
                    )
                    ->max('srd.passing_marks');


            $rawSubjects->push(
                (object) [

                    'id' =>
                        $subjectId,

                    'subject_name' =>
                        $subjectName,

                    'short_name' =>
                        !empty($subject->short_name)
                            ? $subject->short_name
                            : $subjectName,

                    'max_marks' =>
                        (int) ($maxMarks ?? 0),

                    'passing_marks' =>
                        (int) ($passingMarks ?? 0),

                    'sort_order' =>
                        $sortOrder,

                    'is_optional' =>
                        $isOptional,

                    'subject_type_id' =>
                        $subjectTypeId,
                ]
            );
        }


        /*
        |--------------------------------------------------------------------------
        | SORT RAW SUBJECTS
        |--------------------------------------------------------------------------
        */

        $rawSubjects =
            $rawSubjects
                ->sortBy([
                    [
                        'sort_order',
                        'asc'
                    ],
                    [
                        'subject_name',
                        'asc'
                    ],
                ])
                ->values();


        /*
        |--------------------------------------------------------------------------
        | COMPONENT GROUPS
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | MAT1 + MAT2
        | SCI1 + SCI2
        | HISTORY + GEOGRAPHY
        | EVS1 + EVS2
        |
        */

        $componentGroups = [

            'MATHEMATICS' => [
                'MAT1',
                'MAT2',
                'MATH1',
                'MATH2',
                'MATHEMATICS1',
                'MATHEMATICS2',
            ],

            'SCIENCE' => [
                'SCI1',
                'SCI2',
                'SCIENCE1',
                'SCIENCE2',
            ],

            'SOCIAL SCIENCE' => [
                'HISTORY',
                'HIST',
                'GEOGRAPHY',
                'GEO',
            ],

            'EVS' => [
                'EVS1',
                'EVS2',
            ],
        ];


        /*
        |--------------------------------------------------------------------------
        | COMPONENT MAP
        |--------------------------------------------------------------------------
        */

        $componentMap = [];

        foreach ($componentGroups as $groupName => $components) {

            foreach ($components as $component) {

                $componentMap[
                    $normalizeSubjectName($component)
                ] = $groupName;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | BUILD FINAL EXAM SUBJECTS
        |--------------------------------------------------------------------------
        */

        $examSubjects = collect();

        $usedComponentIds = [];


        foreach ($rawSubjects as $subject) {

            $normalized =
                $normalizeSubjectName(
                    $subject->subject_name
                );


            /*
            |--------------------------------------------------------------------------
            | IS COMPONENT?
            |--------------------------------------------------------------------------
            */

            if (isset($componentMap[$normalized])) {

                $groupName =
                    $componentMap[$normalized];


                /*
                |--------------------------------------------------------------------------
                | ALREADY CREATED
                |--------------------------------------------------------------------------
                */

                if (
                    $examSubjects->contains(
                        function ($item) use ($groupName) {

                            return strtoupper(
                                $item->subject_name
                            ) === strtoupper($groupName);
                        }
                    )
                ) {

                    $usedComponentIds[] =
                        $subject->id;

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | GET GROUP SUBJECTS
                |--------------------------------------------------------------------------
                */

                $groupSubjects =
                    $rawSubjects->filter(
                        function ($item)
                        use (
                            $componentMap,
                            $groupName,
                            $normalizeSubjectName
                        ) {

                            $name =
                                $normalizeSubjectName(
                                    $item->subject_name
                                );

                            return
                                isset(
                                    $componentMap[$name]
                                )
                                &&
                                $componentMap[$name]
                                    === $groupName;
                        }
                    )
                    ->values();


                /*
                |--------------------------------------------------------------------------
                | ONLY COMBINE IF TWO OR MORE COMPONENTS EXIST
                |--------------------------------------------------------------------------
                */

                if ($groupSubjects->count() >= 2) {

                    $componentIds =
                        $groupSubjects
                            ->pluck('id')
                            ->map(fn($id) => (int) $id)
                            ->values()
                            ->toArray();


                    $combinedMax =
                        $groupSubjects->sum(
                            fn($item) =>
                                (int) $item->max_marks
                        );


                    $combinedPass =
                        $groupSubjects->sum(
                            fn($item) =>
                                (int) $item->passing_marks
                        );


                    foreach ($componentIds as $id) {

                        $usedComponentIds[] = $id;
                    }


                    $groupSort =
                        $groupSubjects->min(
                            fn($item) =>
                                (int) (
                                    $item->sort_order
                                    ?? 9999
                                )
                        );


                    $firstSubject =
                        $groupSubjects->first();


                    $examSubjects->push(
                        (object) [

                            'id' =>
                                $componentIds[0],

                            'component_ids' =>
                                $componentIds,

                            'is_combined' =>
                                true,

                            'subject_name' =>
                                $groupName,

                            'short_name' =>
                                $groupName,

                            'max_marks' =>
                                (int) $combinedMax,

                            'passing_marks' =>
                                (int) $combinedPass,

                            'sort_order' =>
                                $groupSort,

                            'is_optional' =>
                                $firstSubject->is_optional,

                            'subject_type_id' =>
                                $firstSubject->subject_type_id,
                        ]
                    );


                    continue;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | NORMAL SUBJECT
            |--------------------------------------------------------------------------
            |
            | Marathi
            | Sanskrit
            | English
            | Hindi
            | Computer
            | Robotics
            | etc.
            |
            */

            if (
                in_array(
                    (int) $subject->id,
                    $usedComponentIds,
                    true
                )
            ) {
                continue;
            }


            $examSubjects->push(
                (object) [

                    'id' =>
                        (int) $subject->id,

                    'component_ids' =>
                        [(int) $subject->id],

                    'is_combined' =>
                        false,

                    'subject_name' =>
                        $subject->subject_name,

                    'short_name' =>
                        $subject->short_name,

                    'max_marks' =>
                        (int) $subject->max_marks,

                    'passing_marks' =>
                        (int) $subject->passing_marks,

                    'sort_order' =>
                        (int) $subject->sort_order,

                    'is_optional' =>
                        (int) $subject->is_optional,

                    'subject_type_id' =>
                        (int) $subject->subject_type_id,
                ]
            );
        }


        /*
        |--------------------------------------------------------------------------
        | FINAL SORT
        |--------------------------------------------------------------------------
        */

        $examSubjects =
            $examSubjects
                ->sortBy([
                    [
                        'sort_order',
                        'asc'
                    ],
                    [
                        'subject_name',
                        'asc'
                    ],
                ])
                ->values();


        /*
        |--------------------------------------------------------------------------
        | SUBJECT TYPES
        |--------------------------------------------------------------------------
        |
        | 1 = Academic
        | 2 = Skill
        | 3 = Co-Scholastic
        |
        */

        $academicSubjects =
            $examSubjects
                ->filter(
                    fn($subject) =>
                        (int) $subject->subject_type_id === 1
                )
                ->values();


        $coSubjects =
            $examSubjects
                ->filter(
                    fn($subject) =>
                        (int) $subject->subject_type_id === 3
                )
                ->values();


        $showSkillColumn =
            $examSubjects->contains(
                fn($subject) =>
                    (int) $subject->subject_type_id === 2
            );


        /*
        |--------------------------------------------------------------------------
        | TOTAL MAX MARKS
        |--------------------------------------------------------------------------
        */

        $totalMaxMarks =
            $academicSubjects->sum(
                fn($subject) =>
                    (int) ($subject->max_marks ?? 0)
            );


        /*
        |--------------------------------------------------------------------------
        | LOAD STUDENT RESULTS
        |--------------------------------------------------------------------------
        */

        $results =
            DB::table('student_results as sr')
                ->where(
                    'sr.academic_year_id',
                    $academicYearId
                )
                ->where(
                    'sr.exam_master_id',
                    $examMasterId
                )
                ->where(
                    'sr.standard_id',
                    $standardId
                )
                ->where(
                    'sr.division_id',
                    $divisionId
                )
                ->orderBy('sr.rank')
                ->get();


        /*
        |--------------------------------------------------------------------------
        | ERP STUDENTS
        |--------------------------------------------------------------------------
        */

        $studentIds =
            $results
                ->pluck('student_id')
                ->filter()
                ->map(fn($id) => (int) $id)
                ->values()
                ->toArray();


        $erpStudents = collect();


        if (!empty($studentIds)) {

            $erpStudents =
                DB::connection('sqlsrv_olderp')
                    ->table('FeeMstStudent as f')
                    ->leftJoin(
                        'SubStudentMst as ss',
                        'ss.Studentid',
                        '=',
                        'f.Studentid'
                    )
                    ->whereIn(
                        'f.Studentid',
                        $studentIds
                    )
                    ->select(
                        'f.Studentid',
                        'f.studname',
                        'f.fathername',
                        'f.gender',
                        'ss.rollno'
                    )
                    ->get()
                    ->keyBy('Studentid');
        }


        /*
        |--------------------------------------------------------------------------
        | PROCESS STUDENTS
        |--------------------------------------------------------------------------
        */

        foreach ($results as $student) {

            /*
            |--------------------------------------------------------------------------
            | INITIAL VALUES
            |--------------------------------------------------------------------------
            */

            $student->academic_total = 0;

            $student->academic_max_marks =
                $totalMaxMarks;

            $student->calculated_percentage = 0;

            $student->calculated_grade = 'D';

            $student->has_absent = false;

            $student->subject_marks = [];

            $student->details = [];


            /*
            |--------------------------------------------------------------------------
            | ERP DATA
            |--------------------------------------------------------------------------
            */

            $erp =
                $erpStudents[
                    $student->student_id
                ] ?? null;


            $student->gender =
                $erp->gender ?? '';


            $student->roll_no =
                $erp->rollno ?? '';


            $student->full_student_name =
                $erp
                    ? trim(
                        ($erp->studname ?? '') .
                        ' ' .
                        ($erp->fathername ?? '')
                    )
                    : 'Student ID : ' .
                        $student->student_id;


            /*
            |--------------------------------------------------------------------------
            | RESULT DETAILS
            |--------------------------------------------------------------------------
            */

            $details =
                DB::table(
                    'student_result_details'
                )
                    ->where(
                        'student_result_id',
                        $student->id
                    )
                    ->get();


            foreach ($details as $detail) {

                $subjectResult =
                    strtoupper(
                        trim(
                            $detail->subject_result ?? ''
                        )
                    );


                $isAbsent =
                    $subjectResult === 'ABSENT'
                    ||
                    strtoupper(
                        trim(
                            (string) (
                                $detail->obtained_marks
                                ?? ''
                            )
                        )
                    ) === 'AB';


                $student->details[
                    (int) $detail->subject_id
                ] = [

                    'marks' =>
                        $isAbsent
                            ? 'AB'
                            : (
                                $detail->obtained_marks === null
                                    ? null
                                    : (
                                        is_numeric(
                                            $detail->obtained_marks
                                        )
                                            ? (float)
                                                $detail->obtained_marks
                                            : $detail->obtained_marks
                                    )
                            ),

                    'max_marks' =>
                        (int) (
                            $detail->max_marks ?? 0
                        ),

                    'passing_marks' =>
                        (int) (
                            $detail->passing_marks ?? 0
                        ),

                    'grade' =>
                        $detail->grade,

                    'result' =>
                        $detail->subject_result,

                    'is_absent' =>
                        $isAbsent ? 1 : 0,
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | BUILD SUBJECT MARKS
            |--------------------------------------------------------------------------
            */

            foreach ($examSubjects as $subject) {

                $componentIds =
                    $subject->component_ids
                    ?? [$subject->id];


                $totalMarks = 0;

                $hasAnyMark = false;

                $hasAbsent = false;


                foreach ($componentIds as $componentId) {

                    $detail =
                        $student->details[
                            (int) $componentId
                        ] ?? null;


                    if (!$detail) {
                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | ABSENT
                    |--------------------------------------------------------------------------
                    */

                    if (
                        ($detail['is_absent'] ?? 0) == 1
                    ) {

                        $hasAbsent = true;

                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | MARKS
                    |--------------------------------------------------------------------------
                    */

                    if (
                        isset($detail['marks'])
                        &&
                        is_numeric($detail['marks'])
                    ) {

                        $totalMarks +=
                            (float) $detail['marks'];

                        $hasAnyMark = true;
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | DISPLAY VALUE
                |--------------------------------------------------------------------------
                */

                if ($hasAbsent) {

                    $student->subject_marks[
                        $subject->id
                    ] = 'AB';

                } elseif ($hasAnyMark) {

                    $student->subject_marks[
                        $subject->id
                    ] = $totalMarks;

                } else {

                    $student->subject_marks[
                        $subject->id
                    ] = '-';
                }


                /*
                |--------------------------------------------------------------------------
                | ACADEMIC ABSENT
                |--------------------------------------------------------------------------
                */

                if (
                    (int) $subject->subject_type_id === 1
                    &&
                    $hasAbsent
                ) {

                    $student->has_absent = true;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | ACADEMIC TOTAL
            |--------------------------------------------------------------------------
            */

            $student->academic_total = 0;


            foreach ($academicSubjects as $subject) {

                $mark =
                    $student->subject_marks[
                        $subject->id
                    ] ?? '-';


                if (
                    strtoupper(
                        (string) $mark
                    ) === 'AB'
                ) {

                    $student->has_absent = true;

                    continue;
                }


                if (is_numeric($mark)) {

                    $student->academic_total +=
                        (float) $mark;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | PERCENTAGE
            |--------------------------------------------------------------------------
            */

            if ($student->academic_max_marks > 0) {

                $student->calculated_percentage =
                    round(
                        (
                            $student->academic_total /
                            $student->academic_max_marks
                        ) * 100
                    );

            } else {

                $student->calculated_percentage = 0;
            }


            /*
            |--------------------------------------------------------------------------
            | OVERALL GRADE
            |--------------------------------------------------------------------------
            */

            $student->calculated_grade =
                $this->getGradeFromPercentage(
                    $student->calculated_percentage
                );


            /*
            |--------------------------------------------------------------------------
            | RESULT
            |--------------------------------------------------------------------------
            */

            $student->result = 'PASS';


            foreach ($student->details as $detail) {

                $subjectResult =
                    strtoupper(
                        trim(
                            $detail['result'] ?? ''
                        )
                    );


                if (
                    $subjectResult === 'FAIL'
                    ||
                    $subjectResult === 'ABSENT'
                    ||
                    ($detail['is_absent'] ?? 0) == 1
                ) {

                    $student->result = 'FAIL';

                    break;
                }
            }


            if ($student->has_absent) {

                $student->result = 'FAIL';
            }


            /*
            |--------------------------------------------------------------------------
            | SKILL SUBJECT
            |--------------------------------------------------------------------------
            */

            $student->skill_subject = '';

            $student->skill_mark = '';


            if ($showSkillColumn) {

                $skillSubject =
                    $examSubjects->first(
                        fn($subject) =>
                            (int) $subject->subject_type_id === 2
                    );


                if ($skillSubject) {

                    $student->skill_subject =
                        $skillSubject->subject_name;


                    $student->skill_mark =
                        $student->subject_marks[
                            $skillSubject->id
                        ] ?? '';
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | SORT STUDENTS
        |--------------------------------------------------------------------------
        */

        $results =
            collect($results)
                ->sort(
                    function ($a, $b) {

                        $priorityA =
                            strtoupper(
                                $a->gender ?? ''
                            ) === 'FEMALE'
                                ? 1
                                : 2;


                        $priorityB =
                            strtoupper(
                                $b->gender ?? ''
                            ) === 'FEMALE'
                                ? 1
                                : 2;


                        if ($priorityA != $priorityB) {

                            return
                                $priorityA <=>
                                $priorityB;
                        }


                        return strcmp(
                            strtoupper(
                                $a->full_student_name ?? ''
                            ),
                            strtoupper(
                                $b->full_student_name ?? ''
                            )
                        );
                    }
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | GRADE ANALYSIS
        |--------------------------------------------------------------------------
        */

        $grades = [
            'A1',
            'A2',
            'B1',
            'B2',
            'C1',
            'C2',
            'D',
        ];


        $girlsSubjectAnalysis =
            $this->buildSubjectAnalysis(
                $results,
                $academicSubjects,
                'FEMALE',
                $grades
            );


        $boysSubjectAnalysis =
            $this->buildSubjectAnalysis(
                $results,
                $academicSubjects,
                'MALE',
                $grades
            );


        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'administrator.result-sheet.index',
            compact(
                'exams',
                'standards',
                'divisions',
                'academicYears',

                'results',

                'examSubjects',

                'academicSubjects',

                'coSubjects',

                'showSkillColumn',

                'academicYear',

                'exam',

                'standard',

                'division',

                'totalMaxMarks',

                'girlsSubjectAnalysis',

                'boysSubjectAnalysis'
            )
        )->with(
            'yearName',
            $academicYear?->year_name
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GRADE
    |--------------------------------------------------------------------------
    */

    private function getGradeFromPercentage($percentage)
    {
        $percentage = (float) $percentage;


        if ($percentage >= 91) {
            return 'A1';
        }

        if ($percentage >= 81) {
            return 'A2';
        }

        if ($percentage >= 71) {
            return 'B1';
        }

        if ($percentage >= 61) {
            return 'B2';
        }

        if ($percentage >= 51) {
            return 'C1';
        }

        if ($percentage >= 41) {
            return 'C2';
        }

        if ($percentage >= 33) {
            return 'D';
        }

        return 'FAIL';
    }


    /*
    |--------------------------------------------------------------------------
    | PRINT
    |--------------------------------------------------------------------------
    */

    public function print(Request $request)
    {
        $request->validate([
            'academic_year_id' => ['required', 'integer'],
            'exam_master_id'   => ['required', 'integer'],
            'standard_id'      => ['required', 'integer'],
            'division_id'      => ['required', 'integer'],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Check result
        |--------------------------------------------------------------------------
        */

        $resultExists =
            DB::table('student_results')
                ->where(
                    'academic_year_id',
                    (int) $request->academic_year_id
                )
                ->where(
                    'exam_master_id',
                    (int) $request->exam_master_id
                )
                ->where(
                    'standard_id',
                    (int) $request->standard_id
                )
                ->where(
                    'division_id',
                    (int) $request->division_id
                )
                ->exists();


        if (!$resultExists) {

            return redirect()
                ->route('result-sheet.index')
                ->with(
                    'error',
                    'Result is not generated for selected Academic Year, Exam, Standard and Division.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Build result
        |--------------------------------------------------------------------------
        */

        $response =
            $this->search($request);


        if (
            !$response instanceof
            \Illuminate\View\View
        ) {

            return $response;
        }


        $data =
            $response->getData();


        $results =
            collect(
                $data['results'] ?? []
            );


        $examSubjects =
            collect(
                $data['examSubjects'] ?? []
            );


        $academicSubjects =
            collect(
                $data['academicSubjects'] ?? []
            );


        $coSubjects =
            collect(
                $data['coSubjects'] ?? []
            );


        $showSkillColumn =
            $data['showSkillColumn'] ?? false;


        $totalMaxMarks =
            $academicSubjects->sum(
                fn($subject) =>
                    (int) (
                        $subject->max_marks ?? 0
                    )
            );


        /*
        |--------------------------------------------------------------------------
        | ANALYSIS
        |--------------------------------------------------------------------------
        */

        $grades = [
            'A1',
            'A2',
            'B1',
            'B2',
            'C1',
            'C2',
            'D',
        ];


        $girlsSubjectAnalysis =
            $this->buildSubjectAnalysis(
                $results,
                $academicSubjects,
                'FEMALE',
                $grades
            );


        $boysSubjectAnalysis =
            $this->buildSubjectAnalysis(
                $results,
                $academicSubjects,
                'MALE',
                $grades
            );


        /*
        |--------------------------------------------------------------------------
        | MASTER DATA
        |--------------------------------------------------------------------------
        */

        $academicYear =
            AcademicYear::find(
                (int) $request->academic_year_id
            );


        $exam =
            ExamMaster::find(
                (int) $request->exam_master_id
            );


        $standard =
            Standard::find(
                (int) $request->standard_id
            );


        $division =
            Division::find(
                (int) $request->division_id
            );


        /*
        |--------------------------------------------------------------------------
        | PRINT VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'administrator.result-sheet.print',
            [

                'results' =>
                    $results,

                'examSubjects' =>
                    $examSubjects,

                'academicSubjects' =>
                    $academicSubjects,

                'coSubjects' =>
                    $coSubjects,

                'showSkillColumn' =>
                    $showSkillColumn,

                'totalMaxMarks' =>
                    $totalMaxMarks,

                'exam' =>
                    $exam,

                'standard' =>
                    $standard,

                'division' =>
                    $division,

                'academicYear' =>
                    $academicYear,

                'yearName' =>
                    $academicYear?->year_name,

                'girlsSubjectAnalysis' =>
                    $girlsSubjectAnalysis,

                'boysSubjectAnalysis' =>
                    $boysSubjectAnalysis,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SUBJECT ANALYSIS
    |--------------------------------------------------------------------------
    */

    private function buildSubjectAnalysis(
        $results,
        $subjects,
        $gender,
        $grades
    ) {

        $analysis = [];


        foreach ($subjects as $subject) {

            $row = [

                'subject' =>
                    $subject->subject_name,

                'fail' => 0,

                'absent' => 0,

                'total' => 0,
            ];


            foreach ($grades as $grade) {

                $row[$grade] = 0;
            }


            foreach ($results as $student) {

                if (
                    strtoupper(
                        trim(
                            $student->gender ?? ''
                        )
                    ) !== strtoupper($gender)
                ) {

                    continue;
                }


                $componentIds =
                    $subject->component_ids
                    ?? [$subject->id];


                $totalMarks = 0;

                $hasMarks = false;

                $hasAbsent = false;

                $hasFail = false;

                $maxMarks = 0;


                foreach ($componentIds as $componentId) {

                    $detail =
                        $student->details[
                            (int) $componentId
                        ] ?? null;


                    if (!$detail) {
                        continue;
                    }


                    $maxMarks +=
                        (float) (
                            $detail['max_marks'] ?? 0
                        );


                    if (
                        ($detail['is_absent'] ?? 0) == 1
                        ||
                        strtoupper(
                            trim(
                                $detail['result'] ?? ''
                            )
                        ) === 'ABSENT'
                    ) {

                        $hasAbsent = true;

                        continue;
                    }


                    if (
                        strtoupper(
                            trim(
                                $detail['result'] ?? ''
                            )
                        ) === 'FAIL'
                    ) {

                        $hasFail = true;
                    }


                    if (
                        isset($detail['marks'])
                        &&
                        is_numeric($detail['marks'])
                    ) {

                        $totalMarks +=
                            (float) $detail['marks'];

                        $hasMarks = true;
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | ABSENT
                |--------------------------------------------------------------------------
                */

                if ($hasAbsent) {

                    $row['absent']++;

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | FAIL
                |--------------------------------------------------------------------------
                */

                if ($hasFail) {

                    $row['fail']++;

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | NO MARKS
                |--------------------------------------------------------------------------
                */

                if (!$hasMarks) {

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | GRADE
                |--------------------------------------------------------------------------
                */

                $percentage =
                    $maxMarks > 0
                        ? round(
                            (
                                $totalMarks /
                                $maxMarks
                            ) * 100
                        )
                        : 0;


                $grade =
                    $this->getGradeFromPercentage(
                        $percentage
                    );


                if (isset($row[$grade])) {

                    $row[$grade]++;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | TOTAL
            |--------------------------------------------------------------------------
            */

            $row['total'] =
                ($row['A1'] ?? 0) +
                ($row['A2'] ?? 0) +
                ($row['B1'] ?? 0) +
                ($row['B2'] ?? 0) +
                ($row['C1'] ?? 0) +
                ($row['C2'] ?? 0) +
                ($row['D'] ?? 0) +
                ($row['fail'] ?? 0) +
                ($row['absent'] ?? 0);


            $analysis[] = $row;
        }


        return $analysis;
    }
}