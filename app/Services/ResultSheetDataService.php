<?php

namespace App\Services;

use App\Helpers\ResultSheetHelper;
use App\Helpers\StudentHelper;
use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\ExamMaster;
use App\Models\Standard;
use App\Models\UserDesignation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResultSheetDataService
{
    /*
    |--------------------------------------------------------------------------
    | BUILD RESULT SHEET DATA
    |--------------------------------------------------------------------------
    */

    public function build(
        int $academicYearId,
        int $examMasterId,
        int $standardId,
        int $divisionId
    ): array {
        /*
        |--------------------------------------------------------------------------
        | LOAD MASTER RECORDS
        |--------------------------------------------------------------------------
        */

        $academicYear =
            AcademicYear::find(
                $academicYearId
            );

        $exam =
            ExamMaster::find(
                $examMasterId
            );

        $standard =
            Standard::find(
                $standardId
            );

        $division =
            Division::find(
                $divisionId
            );


        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        if (
            !$academicYear ||
            !$exam ||
            !$standard ||
            !$division
        ) {

            return [
                'error' =>
                    'Invalid Academic Year, Exam, Standard or Division selected.',
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | PASSING PERCENTAGE
        |--------------------------------------------------------------------------
        */

        $passPercentage =
            ResultSheetHelper::getPassingPercentage(
                $standardId
            );


        /*
        |--------------------------------------------------------------------------
        | BUILD DISPLAY SUBJECT COLUMNS
        |--------------------------------------------------------------------------
        */

        $displayColumns =
            $this->buildDisplayColumns(
                $examMasterId,
                $standardId,
                $passPercentage
            );


        if (
            $displayColumns->isEmpty()
        ) {

            return [
                'error' =>
                    'No active Academic Standard Wise Subject Mapping found for '
                    .
                    (
                        $standard->standard_name
                        ??
                        'selected Standard'
                    )
                    .
                    '.',
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD ACTUAL STUDENT MARKS
        |--------------------------------------------------------------------------
        */

        $markRows =
            $this->loadStudentMarks(
                $academicYearId,
                $examMasterId,
                $standardId,
                $divisionId
            );


        /*
        |--------------------------------------------------------------------------
        | COMMON MARK GROUPING
        |--------------------------------------------------------------------------
        |
        | ResultSheetHelper owns this logic.
        |
        */

        $marksByStudent =
            ResultSheetHelper::groupMarksByStudentAndSubject(
                $markRows,
                $displayColumns
            );


        /*
        |--------------------------------------------------------------------------
        | LOAD STUDENTS FROM OLD ERP
        |--------------------------------------------------------------------------
        */

        $erpStudents =
            $this->loadERPStudents(
                $academicYearId,
                $standardId,
                $divisionId,
                $marksByStudent
                    ->keys()
                    ->map(
                        fn ($id) =>
                            (int) $id
                    )
                    ->all()
            );


        /*
        |--------------------------------------------------------------------------
        | COMMON STUDENT BUILDING
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | buildStudents() has been moved to ResultSheetHelper.
        |
        | This ensures every result-related controller/service uses
        | exactly the same optional-subject, marks, grade, total and
        | result calculation logic.
        |
        */

        $students =
            ResultSheetHelper::buildStudents(
                $erpStudents,
                $marksByStudent,
                $displayColumns,
                $passPercentage
            );


        /*
        |--------------------------------------------------------------------------
        | STAFF DESIGNATIONS
        |--------------------------------------------------------------------------
        */

        $designationData =
            $this->getDesignations(
                $academicYearId,
                $standardId,
                $divisionId
            );


        /*
        |--------------------------------------------------------------------------
        | TOTAL MAXIMUM MARKS
        |--------------------------------------------------------------------------
        |
        | Use the most common academic maximum among generated students.
        |
        */

        $totalMaxMarks = 0.0;


        if (
            $students->isNotEmpty()
        ) {

            $maxValues =
                $students
                    ->pluck(
                        'academic_max_display'
                    )
                    ->filter(
                        fn ($value) =>
                            is_numeric($value) &&
                            (float) $value > 0
                    )
                    ->map(
                        fn ($value) =>
                            (float) $value
                    );


            if (
                $maxValues->isNotEmpty()
            ) {

                $mode =
                    $maxValues
                        ->countBy(
                            fn ($value) =>
                                number_format(
                                    $value,
                                    4,
                                    '.',
                                    ''
                                )
                        )
                        ->sortDesc()
                        ->keys()
                        ->first();


                if (
                    $mode !== null
                ) {

                    $totalMaxMarks =
                        (float) $mode;

                } else {

                    $totalMaxMarks =
                        (float)
                        $maxValues->max();
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | VIEW DATA
        |--------------------------------------------------------------------------
        */

        return [

            'error' =>
                null,

            'viewData' => [

                /*
                |--------------------------------------------------------------------------
                | FILTER DATA
                |--------------------------------------------------------------------------
                */

                'exams' =>
                    ExamMaster::orderByDesc(
                        'id'
                    )->get(),

                'standards' =>
                    Standard::orderBy(
                        'display_order'
                    )->get(),

                'divisions' =>
                    Division::orderBy(
                        'division_name'
                    )->get(),

                'academicYears' =>
                    AcademicYear::orderByDesc(
                        'id'
                    )->get(),


                /*
                |--------------------------------------------------------------------------
                | RESULT DATA
                |--------------------------------------------------------------------------
                */

                'results' =>
                    $students,

                'displayColumns' =>
                    $displayColumns,

                'totalMaxMarks' =>
                    $totalMaxMarks,

                'passPercentage' =>
                    $passPercentage,


                /*
                |--------------------------------------------------------------------------
                | SELECTED RECORDS
                |--------------------------------------------------------------------------
                */

                'exam' =>
                    $exam,

                'standard' =>
                    $standard,

                'division' =>
                    $division,

                'academicYear' =>
                    $academicYear,


                /*
                |--------------------------------------------------------------------------
                | DESIGNATIONS
                |--------------------------------------------------------------------------
                */

                'classTeacher' =>
                    $designationData[
                        'classTeacher'
                    ],

                'principal' =>
                    $designationData[
                        'principal'
                    ],
            ],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | BUILD DISPLAY COLUMNS
    |--------------------------------------------------------------------------
    */

    private function buildDisplayColumns(
        int $examMasterId,
        int $standardId,
        int $passPercentage
    ): Collection {

        /*
        |--------------------------------------------------------------------------
        | LOAD STANDARD-WISE SUBJECTS
        |--------------------------------------------------------------------------
        */

        $subjects =
            DB::table(
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
            ->orderBy(
                'sws.sort_order'
            )
            ->orderBy(
                'sws.id'
            )
            ->select([
                'sws.id as mapping_id',
                'sws.subject_id',
                's.subject_name',
                's.subject_code',
                's.short_name',
                's.subject_type_id',
                'sws.subject_name as standard_subject_name',
                'sws.is_optional',
                'sws.sort_order',
            ])
            ->get();


        $columns =
            collect();


        /*
        |--------------------------------------------------------------------------
        | BUILD EACH COLUMN
        |--------------------------------------------------------------------------
        */

        foreach (
            $subjects as $subject
        ) {

            $subjectId =
                (int) (
                    $subject->subject_id
                    ?? 0
                );


            if (
                $subjectId <= 0
            ) {

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | ACADEMIC SUBJECTS ONLY
            |--------------------------------------------------------------------------
            */

            if (
                (int) (
                    $subject->subject_type_id
                    ?? 1
                ) !== 1
            ) {

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | SUBJECT NAME
            |--------------------------------------------------------------------------
            */

            $subjectName =
                trim(
                    (string) (
                        $subject->subject_name
                        ??
                        $subject->standard_subject_name
                        ??
                        ''
                    )
                );


            if (
                $subjectName === ''
            ) {

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | EXAM SUBJECT CONFIG
            |--------------------------------------------------------------------------
            */

            $config =
                $this->resolveExamSubjectConfig(
                    $examMasterId,
                    $standardId,
                    $subjectId,
                    (int) (
                        $subject->mapping_id
                        ?? 0
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | MAXIMUM MARKS
            |--------------------------------------------------------------------------
            */

            $maxMarks =
                $config
                ?
                (float) (
                    $config->max_marks
                    ?? 0
                )
                :
                0.0;


            /*
            |--------------------------------------------------------------------------
            | PASSING MARKS
            |--------------------------------------------------------------------------
            */

            $passingMarks =
                $config
                ?
                (float) (
                    $config->passing_marks
                    ?? 0
                )
                :
                0.0;


            /*
            |--------------------------------------------------------------------------
            | FALLBACK PASSING MARK
            |--------------------------------------------------------------------------
            */

            if (
                $passingMarks <= 0 &&
                $maxMarks > 0
            ) {

                $passingMarks =
                    ResultSheetHelper::calculatePassingMarks(
                        $maxMarks,
                        $passPercentage
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | SUBJECT CODE
            |--------------------------------------------------------------------------
            */

            $code =
                trim(
                    (string) (
                        $subject->subject_code
                        ?? ''
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | NORMALIZED SUBJECT NAME
            |--------------------------------------------------------------------------
            */

            $normalized =
                ResultSheetHelper::normalizeText(
                    $subjectName
                );


            /*
            |--------------------------------------------------------------------------
            | STANDARDIZED CODES
            |--------------------------------------------------------------------------
            */

            if (
                $normalized === 'HISTORY'
            ) {

                $code =
                    'HIST';

            } elseif (
                $normalized === 'GEOGRAPHY'
            ) {

                $code =
                    'GEO';
            }


            /*
            |--------------------------------------------------------------------------
            | FALLBACK CODE
            |--------------------------------------------------------------------------
            */

            if (
                $code === ''
            ) {

                $cleanSubjectName =
                    preg_replace(
                        '/[^A-Za-z0-9]/',
                        '',
                        $subjectName
                    );


                $code =
                    strtoupper(
                        substr(
                            $cleanSubjectName ?? '',
                            0,
                            4
                        )
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | RESULT SHEET COLUMN
            |--------------------------------------------------------------------------
            */

            $columns->push(
                (object) [

                    'key' =>
                        'SUBJECT_' .
                        $subjectId,

                    'mapping_id' =>
                        (int) (
                            $subject->mapping_id
                            ?? 0
                        ),

                    'subject_id' =>
                        $subjectId,

                    'subject_name' =>
                        $subjectName,

                    'subject_code' =>
                        $code,

                    'short_name' =>
                        $subject->short_name
                        ?:
                        $subjectName,

                    'max_marks' =>
                        $maxMarks,

                    'passing_marks' =>
                        $passingMarks,

                    /*
                    |--------------------------------------------------------------------------
                    | SUBJECT CONFIGURATION
                    |--------------------------------------------------------------------------
                    |
                    | This is NOT the student's final optional status.
                    | Individual student marks are evaluated later by
                    | ResultSheetHelper::buildStudents().
                    |
                    */

                    'is_optional' =>
                        (int) (
                            $subject->is_optional
                            ?? 0
                        ),

                    'sort_order' =>
                        (int) (
                            $subject->sort_order
                            ?? 9999
                        ),
                ]
            );
        }


        /*
        |--------------------------------------------------------------------------
        | FINAL SUBJECT ORDER
        |--------------------------------------------------------------------------
        */

        return $columns
            ->sortBy(
                fn ($subject) =>
                    $this->subjectSortWeight(
                        $subject
                    )
            )
            ->values();
    }


    /*
    |--------------------------------------------------------------------------
    | SUBJECT SORT WEIGHT
    |--------------------------------------------------------------------------
    */

    private function subjectSortWeight(
        $subject
    ): int {

        $name =
            strtoupper(
                trim(
                    (string) (
                        $subject->subject_name
                        ?? ''
                    )
                )
            );


        $code =
            strtoupper(
                trim(
                    (string) (
                        $subject->subject_code
                        ?? ''
                    )
                )
            );


        if (
            $name === 'ENGLISH' ||
            $code === 'ENG'
        ) {

            return 10;
        }


        if (
            $name === 'HINDI' ||
            $code === 'HIN'
        ) {

            return 20;
        }


        if (
            $name === 'SANSKRIT' ||
            $code === 'SAN'
        ) {

            return 20;
        }


        if (
            $name === 'MARATHI' ||
            $code === 'MAR'
        ) {

            return 30;
        }


        return
            1000 +
            (int) (
                $subject->sort_order
                ?? 9999
            );
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE EXAM SUBJECT CONFIGURATION
    |--------------------------------------------------------------------------
    */

    private function resolveExamSubjectConfig(
        int $examMasterId,
        int $standardId,
        int $subjectId,
        int $mappingId
    ) {

        if (
            !Schema::hasTable(
                'exam_master_subjects'
            )
        ) {

            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | FIRST: ACTUAL subjects.id
        |--------------------------------------------------------------------------
        */

        $config =
            DB::table(
                'exam_master_subjects'
            )
            ->where(
                'exam_master_id',
                $examMasterId
            )
            ->where(
                'standard_id',
                $standardId
            )
            ->where(
                'subject_id',
                $subjectId
            )
            ->first();


        if (
            $config
        ) {

            return $config;
        }


        /*
        |--------------------------------------------------------------------------
        | SECOND: LEGACY mapping ID
        |--------------------------------------------------------------------------
        */

        if (
            $mappingId > 0
        ) {

            $config =
                DB::table(
                    'exam_master_subjects'
                )
                ->where(
                    'exam_master_id',
                    $examMasterId
                )
                ->where(
                    'standard_id',
                    $standardId
                )
                ->where(
                    'subject_id',
                    $mappingId
                )
                ->first();


            if (
                $config
            ) {

                return $config;
            }
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | LOAD STUDENT MARKS
    |--------------------------------------------------------------------------
    */

    private function loadStudentMarks(
        int $academicYearId,
        int $examMasterId,
        int $standardId,
        int $divisionId
    ): Collection {

        if (
            !Schema::hasTable(
                'student_marks'
            )
        ) {

            return collect();
        }


        /*
        |--------------------------------------------------------------------------
        | AVAILABLE COLUMNS
        |--------------------------------------------------------------------------
        */

        $columns =
            Schema::getColumnListing(
                'student_marks'
            );


        /*
        |--------------------------------------------------------------------------
        | BASE QUERY
        |--------------------------------------------------------------------------
        */

        $query =
            DB::table(
                'student_marks as sm'
            );


        /*
        |--------------------------------------------------------------------------
        | APPLY AVAILABLE FILTERS
        |--------------------------------------------------------------------------
        */

        foreach (
            [
                'academic_year_id' =>
                    $academicYearId,

                'exam_master_id' =>
                    $examMasterId,

                'standard_id' =>
                    $standardId,

                'division_id' =>
                    $divisionId,
            ]
            as $field => $value
        ) {

            if (
                in_array(
                    $field,
                    $columns,
                    true
                )
            ) {

                $query->where(
                    'sm.' . $field,
                    $value
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | TEACHER SUBJECT ALLOCATION
        |--------------------------------------------------------------------------
        */

        $hasAllocation =
            Schema::hasTable(
                'teacher_subject_allocations'
            )
            &&
            in_array(
                'teacher_subject_allocation_id',
                $columns,
                true
            );


        if (
            $hasAllocation
        ) {

            $query->leftJoin(
                'teacher_subject_allocations as tsa',
                'tsa.id',
                '=',
                'sm.teacher_subject_allocation_id'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | SELECT STUDENT MARKS
        |--------------------------------------------------------------------------
        */

        $query->select(
            'sm.*'
        );


        /*
        |--------------------------------------------------------------------------
        | ADD ALLOCATION SUBJECT ID
        |--------------------------------------------------------------------------
        */

        if (
            $hasAllocation
        ) {

            $query->addSelect(
                'tsa.subject_id as allocation_subject_id'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | NEWEST RECORD FIRST
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                'id',
                $columns,
                true
            )
        ) {

            $query->orderByDesc(
                'sm.id'
            );
        }


        return $query->get();
    }


    /*
    |--------------------------------------------------------------------------
    | LOAD ERP STUDENTS
    |--------------------------------------------------------------------------
    */

    private function loadERPStudents(
        int $academicYearId,
        int $standardId,
        int $divisionId,
        array $fallbackStudentIds = []
    ): Collection {

        try {

            $students =
                StudentHelper::getStudentsDirectERP(
                    $academicYearId,
                    $standardId,
                    $divisionId
                );


            $students =
                collect(
                    $students
                );


            if (
                $students->isNotEmpty()
            ) {

                return $students;
            }

        } catch (\Throwable) {

            /*
            |------------------------------------------------------------------
            | FALLBACK BELOW
            |------------------------------------------------------------------
            */
        }


        return collect(
            $this->loadERPStudentsByIds(
                $fallbackStudentIds
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | LOAD ERP STUDENTS BY IDS
    |--------------------------------------------------------------------------
    */

    private function loadERPStudentsByIds(
        array $studentIds
    ): array {

        $students = [];


        if (
            empty($studentIds)
        ) {

            return $students;
        }


        try {

            $rows =
                DB::connection(
                    'sqlsrv_olderp'
                )
                ->table(
                    'FeeMstStudent as f'
                )
                ->leftJoin(
                    'SubStudentMst as ss',
                    'ss.Studentid',
                    '=',
                    'f.Studentid'
                )
                ->whereIn(
                    'f.Studentid',
                    array_map(
                        'intval',
                        $studentIds
                    )
                )
                ->select([
                    'f.Studentid',
                    'f.studname',
                    'f.fathername',
                    'f.gender',
                    'ss.rollno',
                ])
                ->get();


            foreach (
                $rows as $row
            ) {

                $id =
                    (int) (
                        $row->Studentid
                        ?? 0
                    );


                if (
                    $id > 0 &&
                    !isset(
                        $students[$id]
                    )
                ) {

                    $students[$id] =
                        $row;
                }
            }

        } catch (\Throwable) {

            /*
            |------------------------------------------------------------------
            | Keep result page usable.
            |------------------------------------------------------------------
            */
        }


        return $students;
    }


    /*
    |--------------------------------------------------------------------------
    | DESIGNATIONS
    |--------------------------------------------------------------------------
    */

    private function getDesignations(
        int $academicYearId,
        int $standardId,
        int $divisionId
    ): array {

        $classTeacher =
            null;

        $principal =
            null;


        /*
        |--------------------------------------------------------------------------
        | CLASS TEACHER - EXACT
        |--------------------------------------------------------------------------
        */

        try {

            $classTeacher =
                UserDesignation::query()
                    ->with([
                        'user',
                        'designation',
                    ])
                    ->where(
                        'academic_year_id',
                        $academicYearId
                    )
                    ->where(
                        'standard_id',
                        $standardId
                    )
                    ->where(
                        'division_id',
                        $divisionId
                    )
                    ->whereHas(
                        'designation',
                        function ($query) {

                            $query->where(
                                function ($q) {

                                    $q->whereRaw(
                                        'UPPER(TRIM(designation_name)) LIKE ?',
                                        [
                                            '%CLASS TEACHER%',
                                        ]
                                    )
                                    ->orWhereRaw(
                                        'UPPER(TRIM(designation_code)) LIKE ?',
                                        [
                                            '%CLASS_TEACHER%',
                                        ]
                                    )
                                    ->orWhereRaw(
                                        'UPPER(TRIM(designation_code)) LIKE ?',
                                        [
                                            '%CLASS-TEACHER%',
                                        ]
                                    );
                                }
                            );
                        }
                    )
                    ->orderByDesc(
                        'id'
                    )
                    ->first();

        } catch (\Throwable) {

            $classTeacher =
                null;
        }


        /*
        |--------------------------------------------------------------------------
        | CLASS TEACHER FALLBACK
        |--------------------------------------------------------------------------
        */

        if (
            !$classTeacher
        ) {

            try {

                $classTeacher =
                    UserDesignation::query()
                        ->with([
                            'user',
                            'designation',
                        ])
                        ->where(
                            'standard_id',
                            $standardId
                        )
                        ->where(
                            'division_id',
                            $divisionId
                        )
                        ->whereHas(
                            'designation',
                            function ($query) {

                                $query->where(
                                    function ($q) {

                                        $q->whereRaw(
                                            'UPPER(TRIM(designation_name)) LIKE ?',
                                            [
                                                '%CLASS TEACHER%',
                                            ]
                                        )
                                        ->orWhereRaw(
                                            'UPPER(TRIM(designation_code)) LIKE ?',
                                            [
                                                '%CLASS_TEACHER%',
                                            ]
                                        )
                                        ->orWhereRaw(
                                            'UPPER(TRIM(designation_code)) LIKE ?',
                                            [
                                                '%CLASS-TEACHER%',
                                            ]
                                        );
                                    }
                                );
                            }
                        )
                        ->orderByDesc(
                            'id'
                        )
                        ->first();

            } catch (\Throwable) {

                $classTeacher =
                    null;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | PRINCIPAL-PRI
        |--------------------------------------------------------------------------
        */

        try {

            $principal =
                UserDesignation::query()
                    ->with([
                        'user',
                        'designation',
                    ])
                    ->whereHas(
                        'designation',
                        function ($query) {

                            $query->where(
                                function ($q) {

                                    $q->whereRaw(
                                        'UPPER(TRIM(designation_name)) = ?',
                                        [
                                            'PRINCIPAL-PRI',
                                        ]
                                    )
                                    ->orWhereRaw(
                                        'UPPER(TRIM(designation_code)) = ?',
                                        [
                                            'PRINCIPAL-PRI',
                                        ]
                                    );
                                }
                            );
                        }
                    )
                    ->orderByDesc(
                        'id'
                    )
                    ->first();

        } catch (\Throwable) {

            $principal =
                null;
        }


        /*
        |--------------------------------------------------------------------------
        | PRINCIPAL-SEC
        |--------------------------------------------------------------------------
        */

        if (
            !$principal
        ) {

            try {

                $principal =
                    UserDesignation::query()
                        ->with([
                            'user',
                            'designation',
                        ])
                        ->whereHas(
                            'designation',
                            function ($query) {

                                $query->where(
                                    function ($q) {

                                        $q->whereRaw(
                                            'UPPER(TRIM(designation_name)) = ?',
                                            [
                                                'PRINCIPAL-SEC',
                                            ]
                                        )
                                        ->orWhereRaw(
                                            'UPPER(TRIM(designation_code)) = ?',
                                            [
                                                'PRINCIPAL-SEC',
                                            ]
                                        );
                                    }
                                );
                            }
                        )
                        ->orderByDesc(
                            'id'
                        )
                        ->first();

            } catch (\Throwable) {

                $principal =
                    null;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | GENERIC PRINCIPAL FALLBACK
        |--------------------------------------------------------------------------
        */

        if (
            !$principal
        ) {

            try {

                $principal =
                    UserDesignation::query()
                        ->with([
                            'user',
                            'designation',
                        ])
                        ->whereHas(
                            'designation',
                            function ($query) {

                                $query->where(
                                    function ($q) {

                                        $q->whereRaw(
                                            'UPPER(TRIM(designation_name)) LIKE ?',
                                            [
                                                '%PRINCIPAL%',
                                            ]
                                        )
                                        ->orWhereRaw(
                                            'UPPER(TRIM(designation_code)) LIKE ?',
                                            [
                                                '%PRINCIPAL%',
                                            ]
                                        );
                                    }
                                );
                            }
                        )
                        ->orderByDesc(
                            'id'
                        )
                        ->first();

            } catch (\Throwable) {

                $principal =
                    null;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | RETURN
        |--------------------------------------------------------------------------
        */

        return [

            'classTeacher' =>
                $classTeacher,

            'principal' =>
                $principal,
        ];
    }
}