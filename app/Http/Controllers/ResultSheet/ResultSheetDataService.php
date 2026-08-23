<?php

namespace App\Http\Controllers\Administrator;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Models\ExamMaster;
use App\Models\Standard;
use App\Models\Division;
use App\Models\AcademicYear;
use App\Models\UserDesignation;
use App\Models\TeacherSubjectAllocation;

class ResultSheetDataService
{
    protected ResultSheetAnalysisService $analysisService;


    /*
    |--------------------------------------------------------------------------
    | CONSTRUCTOR
    |--------------------------------------------------------------------------
    */

    public function __construct(
        ResultSheetAnalysisService $analysisService
    ) {
        $this->analysisService =
            $analysisService;
    }


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

                'exams' =>
                    ExamMaster::orderByDesc('id')
                        ->get(),

                'standards' =>
                    Standard::orderBy('display_order')
                        ->get(),

                'divisions' =>
                    Division::orderBy('division_name')
                        ->get(),

                'academicYears' =>
                    AcademicYear::orderByDesc('id')
                        ->get(),

                'results' =>
                    collect(),

                'displayColumns' =>
                    collect(),

                'totalMaxMarks' =>
                    0,

                'passPercentage' =>
                    40,

                'exam' =>
                    null,

                'standard' =>
                    null,

                'division' =>
                    null,

                'academicYear' =>
                    null,

                'classTeacher' =>
                    null,

                'principal' =>
                    null,

                'overallGradeAnalysis' =>
                    [],

                'girlsSubjectAnalysis' =>
                    [],

                'boysSubjectAnalysis' =>
                    [],
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */

    public function search(
        int $academicYearId,
        int $examMasterId,
        int $divisionId
    ) {

        $exam =
            ExamMaster::find(
                $examMasterId
            );


        if (!$exam) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Selected Exam was not found.'
                );
        }


        $standardId =
            $this->resolveStandardId(
                $exam
            );


        if (
            $standardId <= 0
        ) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'The selected Exam is not mapped to a Standard.'
                );
        }


        $data =
            $this->buildResultSheetData(
                $academicYearId,
                $examMasterId,
                $standardId,
                $divisionId
            );


        if (
            !empty(
                $data['error']
            )
        ) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    $data['error']
                );
        }


        return view(
            'administrator.result-sheet.index',
            $data['viewData']
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PRINT
    |--------------------------------------------------------------------------
    */

    public function print(
        int $academicYearId,
        int $examMasterId,
        int $divisionId
    ) {

        $exam =
            ExamMaster::find(
                $examMasterId
            );


        if (!$exam) {

            return redirect()
                ->route(
                    'result-sheet.index'
                )
                ->with(
                    'error',
                    'Selected Exam was not found.'
                );
        }


        $standardId =
            $this->resolveStandardId(
                $exam
            );


        if (
            $standardId <= 0
        ) {

            return redirect()
                ->route(
                    'result-sheet.index'
                )
                ->with(
                    'error',
                    'The selected Exam is not mapped to a Standard.'
                );
        }


        $data =
            $this->buildResultSheetData(
                $academicYearId,
                $examMasterId,
                $standardId,
                $divisionId
            );


        if (
            !empty(
                $data['error']
            )
        ) {

            return redirect()
                ->route(
                    'result-sheet.index'
                )
                ->with(
                    'error',
                    $data['error']
                );
        }


        return view(
            'administrator.result-sheet.print',
            $data['viewData']
        );
    }


    /*
    |--------------------------------------------------------------------------
    | BUILD FOR EXPORT
    |--------------------------------------------------------------------------
    */

    public function buildForExport(
        int $academicYearId,
        int $examMasterId,
        int $divisionId
    ): array {

        $exam =
            ExamMaster::find(
                $examMasterId
            );


        if (!$exam) {

            return [
                'error' =>
                    'Selected Exam was not found.',

                'viewData' =>
                    [],
            ];
        }


        $standardId =
            $this->resolveStandardId(
                $exam
            );


        if (
            $standardId <= 0
        ) {

            return [
                'error' =>
                    'The selected Exam is not mapped to a Standard.',

                'viewData' =>
                    [],
            ];
        }


        return $this->buildResultSheetData(
            $academicYearId,
            $examMasterId,
            $standardId,
            $divisionId
        );
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE STANDARD
    |--------------------------------------------------------------------------
    */

    private function resolveStandardId(
        ExamMaster $exam
    ): int {

        $standardId =
            (int) (
                $exam->standard_id
                ?? 0
            );


        if (
            $standardId <= 0
        ) {

            $standardId =
                (int) (
                    DB::table(
                        'exam_master_subjects'
                    )
                    ->where(
                        'exam_master_id',
                        $exam->id
                    )
                    ->whereNotNull(
                        'standard_id'
                    )
                    ->value(
                        'standard_id'
                    )
                    ?? 0
                );
        }


        return $standardId;
    }


    /*
    |--------------------------------------------------------------------------
    | BUILD RESULT SHEET DATA
    |--------------------------------------------------------------------------
    */

    private function buildResultSheetData(
        int $academicYearId,
        int $examMasterId,
        int $standardId,
        int $divisionId
    ): array {

        /*
        |--------------------------------------------------------------------------
        | MASTER DATA
        |--------------------------------------------------------------------------
        */

        $exams =
            ExamMaster::orderByDesc('id')
                ->get();


        $standards =
            Standard::orderBy('display_order')
                ->get();


        $divisions =
            Division::orderBy('division_name')
                ->get();


        $academicYears =
            AcademicYear::orderByDesc('id')
                ->get();


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


        if (
            !$academicYear ||
            !$exam ||
            !$standard ||
            !$division
        ) {

            return [
                'error' =>
                    'Invalid Academic Year, Exam, Standard or Division selected.',

                'academicYear' =>
                    $academicYear,

                'viewData' =>
                    [],
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | CLASS TEACHER + PRINCIPAL
        |--------------------------------------------------------------------------
        */

        $designationData =
            $this->getResultSheetDesignations(
                $academicYearId,
                $standardId,
                $divisionId
            );


        $classTeacher =
            $designationData['classTeacher']
            ?? null;


        $principal =
            $designationData['principal']
            ?? null;


        /*
        |--------------------------------------------------------------------------
        | PASSING PERCENTAGE
        |--------------------------------------------------------------------------
        */

        $passPercentage =
            $this->analysisService
                ->getOverallPassPercentage(
                    $standard->standard_name
                );


        /*
        |--------------------------------------------------------------------------
        | STANDARD WISE SUBJECTS
        |--------------------------------------------------------------------------
        */

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
                'sws.id'
            )
            ->select([
                'sws.id as mapping_id',
                'sws.subject_id as subject_id',
                'st.standard_name as standard',
                's.subject_name as subject_name',
                's.subject_code as subject_code',
                's.short_name as short_name',
                's.subject_type_id as subject_type_id',
                'sws.is_optional as is_optional',
                'sws.sort_order as sort_order',
            ])
            ->get();


        if (
            $standardSubjects->isEmpty()
        ) {

            return [
                'error' =>
                    'No active Standard Wise Subject Mapping found for '
                    . $standard->standard_name
                    . '.',

                'academicYear' =>
                    $academicYear,

                'viewData' =>
                    [],
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | DISPLAY COLUMNS
        |--------------------------------------------------------------------------
        */

        $displayColumns =
            $this->buildDisplayColumns(
                $standardSubjects,
                $examMasterId,
                $standardId
            );


        /*
        |--------------------------------------------------------------------------
        | TOTAL MAX MARKS
        |--------------------------------------------------------------------------
        */

        $totalMaxMarks =
            (float) $displayColumns->sum(
                fn ($column) =>
                    (float) (
                        $column->max_marks
                        ?? 0
                    )
            );


        /*
        |--------------------------------------------------------------------------
        | LOAD MARKS
        |--------------------------------------------------------------------------
        */

        $allMarks =
            $this->loadMarks(
                $academicYearId,
                $examMasterId,
                $standardId,
                $divisionId
            );


        if (
            $allMarks->isEmpty()
        ) {

            return [
                'error' =>
                    'No student marks found for the selected Academic Year, Exam, Standard and Division.',

                'academicYear' =>
                    $academicYear,

                'viewData' =>
                    [],
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | SUBJECT MATCH MAP
        |--------------------------------------------------------------------------
        */

        $subjectMatchMap =
            $this->buildSubjectMatchMap(
                $displayColumns
            );


        /*
        |--------------------------------------------------------------------------
        | MARKS BY STUDENT
        |--------------------------------------------------------------------------
        */

        $marksByStudent =
            $this->buildMarksByStudent(
                $allMarks,
                $subjectMatchMap
            );


        $studentIds =
            array_keys(
                $marksByStudent
            );


        if (
            empty($studentIds)
        ) {

            return [
                'error' =>
                    'No valid student marks found for the selected combination.',

                'academicYear' =>
                    $academicYear,

                'viewData' =>
                    [],
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | ERP STUDENTS
        |--------------------------------------------------------------------------
        */

        $erpStudents =
            $this->loadERPStudentsByIds(
                $studentIds
            );


        /*
        |--------------------------------------------------------------------------
        | BUILD RESULT OBJECTS
        |--------------------------------------------------------------------------
        */

        $results =
            $this->analysisService
                ->buildResults(
                    $marksByStudent,
                    $erpStudents,
                    $displayColumns,
                    $totalMaxMarks,
                    $passPercentage
                );


        /*
        |--------------------------------------------------------------------------
        | OVERALL ANALYSIS
        |--------------------------------------------------------------------------
        */

        $overallGradeAnalysis =
            $this->analysisService
                ->buildOverallGradeAnalysis(
                    $results
                );


        /*
        |--------------------------------------------------------------------------
        | GIRLS ANALYSIS
        |--------------------------------------------------------------------------
        */

        $girlsSubjectAnalysis =
            $this->analysisService
                ->buildSubjectAnalysis(
                    $results,
                    $displayColumns,
                    'FEMALE'
                );


        /*
        |--------------------------------------------------------------------------
        | BOYS ANALYSIS
        |--------------------------------------------------------------------------
        */

        $boysSubjectAnalysis =
            $this->analysisService
                ->buildSubjectAnalysis(
                    $results,
                    $displayColumns,
                    'MALE'
                );


        /*
        |--------------------------------------------------------------------------
        | VIEW DATA
        |--------------------------------------------------------------------------
        */

        return [

            'error' =>
                null,

            'academicYear' =>
                $academicYear,

            'viewData' => [

                'exams' =>
                    $exams,

                'standards' =>
                    $standards,

                'divisions' =>
                    $divisions,

                'academicYears' =>
                    $academicYears,

                'results' =>
                    $results,

                'displayColumns' =>
                    $displayColumns,

                'totalMaxMarks' =>
                    $totalMaxMarks,

                'passPercentage' =>
                    $passPercentage,

                'exam' =>
                    $exam,

                'standard' =>
                    $standard,

                'division' =>
                    $division,

                'academicYear' =>
                    $academicYear,

                'classTeacher' =>
                    $classTeacher,

                'principal' =>
                    $principal,

                'overallGradeAnalysis' =>
                    $overallGradeAnalysis,

                'girlsSubjectAnalysis' =>
                    $girlsSubjectAnalysis,

                'boysSubjectAnalysis' =>
                    $boysSubjectAnalysis,
            ],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | DISPLAY COLUMNS
    |--------------------------------------------------------------------------
    */

    private function buildDisplayColumns(
        $standardSubjects,
        int $examMasterId,
        int $standardId
    ) {

        $displayColumns =
            collect();


        foreach (
            $standardSubjects as $subject
        ) {

            $subjectId =
                (int) (
                    $subject->subject_id
                    ?? 0
                );


            $mappingId =
                (int) (
                    $subject->mapping_id
                    ?? 0
                );


            $subjectName =
                trim(
                    (string) (
                        $subject->subject_name
                        ?? ''
                    )
                );


            if (
                $subjectId <= 0 ||
                $subjectName === ''
            ) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | ACADEMIC ONLY
            |--------------------------------------------------------------------------
            */

            $subjectTypeId =
                (int) (
                    $subject->subject_type_id
                    ?? 1
                );


            if (
                $subjectTypeId !== 1
            ) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | SUBJECT CODE
            |--------------------------------------------------------------------------
            */

            $subjectCode =
                trim(
                    (string) (
                        $subject->subject_code
                        ?? ''
                    )
                );


            if (
                $subjectCode === ''
            ) {

                $short =
                    trim(
                        (string) (
                            $subject->short_name
                            ?? ''
                        )
                    );


                if (
                    $short !== ''
                ) {

                    $short =
                        preg_replace(
                            '/[^A-Za-z0-9]+/',
                            '',
                            $short
                        );


                    $subjectCode =
                        strtoupper(
                            substr(
                                $short,
                                0,
                                4
                            )
                        );
                }
            }


            if (
                $subjectCode === ''
            ) {

                $clean =
                    preg_replace(
                        '/[^A-Za-z0-9]+/',
                        '',
                        $subjectName
                    );


                $subjectCode =
                    strtoupper(
                        substr(
                            $clean,
                            0,
                            4
                        )
                    );
            }


            $normalizedName =
                $this->analysisService
                    ->normalizeSubjectText(
                        $subjectName
                    );


            if (
                $normalizedName === 'HISTORY'
            ) {

                $subjectCode =
                    'HIST';
            }


            if (
                $normalizedName === 'GEOGRAPHY'
            ) {

                $subjectCode =
                    'GEO';
            }


            /*
            |--------------------------------------------------------------------------
            | EXAM CONFIG
            |--------------------------------------------------------------------------
            */

            $examConfig =
                $this->resolveExamSubjectConfig(
                    $examMasterId,
                    $standardId,
                    $subjectId,
                    $mappingId
                );


            $maxMarks =
                $examConfig
                    ? (float) (
                        $examConfig->max_marks
                        ?? 0
                    )
                    : 0;


            $passingMarks =
                $examConfig
                    ? (float) (
                        $examConfig->passing_marks
                        ?? 0
                    )
                    : 0;


            $displayColumns->push(
                (object) [

                    'key' =>
                        'SUBJECT_' . $subjectId,

                    'mapping_id' =>
                        $mappingId,

                    'subject_id' =>
                        $subjectId,

                    'standard' =>
                        $subject->standard,

                    'subject_name' =>
                        $subjectName,

                    'subject_code' =>
                        $subjectCode,

                    'short_name' =>
                        $subject->short_name
                        ?: $subjectName,

                    'max_marks' =>
                        $maxMarks,

                    'passing_marks' =>
                        $passingMarks,

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
        | SORT SUBJECTS
        |--------------------------------------------------------------------------
        */

        return $displayColumns
            ->sortBy(
                function ($subject) {

                    $name =
                        strtoupper(
                            trim(
                                $subject->subject_name
                            )
                        );


                    $code =
                        strtoupper(
                            trim(
                                $subject->subject_code
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
                        1000
                        +
                        (int) $subject->sort_order;
                }
            )
            ->values();
    }


    /*
    |--------------------------------------------------------------------------
    | EXAM SUBJECT CONFIG
    |--------------------------------------------------------------------------
    */

    private function resolveExamSubjectConfig(
        int $examMasterId,
        int $standardId,
        int $canonicalSubjectId,
        int $mappingId
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
                $canonicalSubjectId
            )
            ->first();


        if ($config) {
            return $config;
        }


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


            if ($config) {
                return $config;
            }
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | MARKS
    |--------------------------------------------------------------------------
    */

    private function loadMarks(
        int $academicYearId,
        int $examMasterId,
        int $standardId,
        int $divisionId
    ) {

        $columns =
            Schema::getColumnListing(
                'student_marks'
            );


        $query =
            DB::table(
                'student_marks'
            );


        $yearColumn =
            $this->findFirstExistingColumn(
                $columns,
                [
                    'academic_year_id',
                    'year_id',
                ]
            );


        if ($yearColumn) {

            $query->where(
                $yearColumn,
                $academicYearId
            );
        }


        $examColumn =
            $this->findFirstExistingColumn(
                $columns,
                [
                    'exam_master_id',
                    'exam_id',
                ]
            );


        if ($examColumn) {

            $query->where(
                $examColumn,
                $examMasterId
            );
        }


        $standardColumn =
            $this->findFirstExistingColumn(
                $columns,
                [
                    'standard_id',
                ]
            );


        if ($standardColumn) {

            $query->where(
                $standardColumn,
                $standardId
            );
        }


        $divisionColumn =
            $this->findFirstExistingColumn(
                $columns,
                [
                    'division_id',
                ]
            );


        if ($divisionColumn) {

            $query->where(
                $divisionColumn,
                $divisionId
            );
        }


        if (
            in_array(
                'id',
                $columns,
                true
            )
        ) {

            $query->orderByDesc(
                'id'
            );
        }


        return $query->get();
    }


    /*
    |--------------------------------------------------------------------------
    | SUBJECT MATCH MAP
    |--------------------------------------------------------------------------
    */

    private function buildSubjectMatchMap(
        $displayColumns
    ): array {

        $map = [

            'canonical' =>
                [],

            'mapping' =>
                [],

            'code' =>
                [],

            'name' =>
                [],
        ];


        foreach (
            $displayColumns as $column
        ) {

            $subjectId =
                (int) $column->subject_id;


            $mappingId =
                (int) $column->mapping_id;


            $code =
                strtoupper(
                    trim(
                        (string) (
                            $column->subject_code
                            ?? ''
                        )
                    )
                );


            $name =
                $this->analysisService
                    ->normalizeSubjectText(
                        $column->subject_name
                    );


            if (
                $subjectId > 0
            ) {

                $map['canonical'][
                    $subjectId
                ] =
                    $subjectId;
            }


            if (
                $mappingId > 0
            ) {

                $map['mapping'][
                    $mappingId
                ] =
                    $subjectId;
            }


            if (
                $code !== ''
            ) {

                $map['code'][
                    $code
                ] =
                    $subjectId;
            }


            if (
                $name !== ''
            ) {

                $map['name'][
                    $name
                ] =
                    $subjectId;
            }
        }


        return $map;
    }


    /*
    |--------------------------------------------------------------------------
    | BUILD MARKS BY STUDENT
    |--------------------------------------------------------------------------
    */

    private function buildMarksByStudent(
        $allMarks,
        array $subjectMatchMap
    ): array {

        $marksByStudent =
            [];


        foreach (
            $allMarks as $markRow
        ) {

            $studentId =
                (int) (
                    $markRow->student_id
                    ?? 0
                );


            if (
                $studentId <= 0
            ) {
                continue;
            }


            $displaySubjectId =
                $this->resolveMarkSubjectId(
                    $markRow,
                    $subjectMatchMap
                );


            if (
                $displaySubjectId === null
            ) {
                continue;
            }


            if (
                !isset(
                    $marksByStudent[
                        $studentId
                    ]
                )
            ) {

                $marksByStudent[
                    $studentId
                ] = [];
            }


            if (
                !isset(
                    $marksByStudent[
                        $studentId
                    ][$displaySubjectId]
                )
            ) {

                $marksByStudent[
                    $studentId
                ][$displaySubjectId] =
                    $markRow;
            }
        }


        return $marksByStudent;
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE MARK SUBJECT
    |--------------------------------------------------------------------------
    */

    private function resolveMarkSubjectId(
        $markRow,
        array $subjectMatchMap
    ): ?int {

        $storedSubjectId =
            (int) (
                $markRow->subject_id
                ?? 0
            );


        /*
        |--------------------------------------------------------------------------
        | CANONICAL
        |--------------------------------------------------------------------------
        */

        if (
            isset(
                $subjectMatchMap['canonical'][
                    $storedSubjectId
                ]
            )
        ) {

            return
                $subjectMatchMap['canonical'][
                    $storedSubjectId
                ];
        }


        /*
        |--------------------------------------------------------------------------
        | MAPPING
        |--------------------------------------------------------------------------
        */

        if (
            isset(
                $subjectMatchMap['mapping'][
                    $storedSubjectId
                ]
            )
        ) {

            return
                $subjectMatchMap['mapping'][
                    $storedSubjectId
                ];
        }


        /*
        |--------------------------------------------------------------------------
        | CODE
        |--------------------------------------------------------------------------
        */

        foreach (
            [
                'subject_code',
                'code',
            ] as $field
        ) {

            if (
                isset(
                    $markRow->{$field}
                )
            ) {

                $code =
                    strtoupper(
                        trim(
                            (string) $markRow->{$field}
                        )
                    );


                if (
                    isset(
                        $subjectMatchMap['code'][
                            $code
                        ]
                    )
                ) {

                    return
                        $subjectMatchMap['code'][
                            $code
                        ];
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | NAME
        |--------------------------------------------------------------------------
        */

        foreach (
            [
                'subject_name',
                'subject',
            ] as $field
        ) {

            if (
                isset(
                    $markRow->{$field}
                )
            ) {

                $name =
                    $this->analysisService
                        ->normalizeSubjectText(
                            $markRow->{$field}
                        );


                if (
                    isset(
                        $subjectMatchMap['name'][
                            $name
                        ]
                    )
                ) {

                    return
                        $subjectMatchMap['name'][
                            $name
                        ];
                }
            }
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | ERP STUDENTS
    |--------------------------------------------------------------------------
    */

    private function loadERPStudentsByIds(
        array $studentIds
    ): array {

        $students =
            [];


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
                ->select(
                    'f.Studentid',
                    'f.studname',
                    'f.fathername',
                    'f.gender',
                    'ss.rollno'
                )
                ->get();


            foreach (
                $rows as $row
            ) {

                $students[
                    (int) $row->Studentid
                ] =
                    $row;
            }

        } catch (
            \Throwable $e
        ) {

            /*
            |--------------------------------------------------------------------------
            | ERP failure should not crash result processing.
            |--------------------------------------------------------------------------
            */
        }


        return $students;
    }


    /*
    |--------------------------------------------------------------------------
    | CLASS TEACHER + PRINCIPAL
    |--------------------------------------------------------------------------
    */

    private function getResultSheetDesignations(
        int $academicYearId,
        int $standardId,
        int $divisionId
    ): array {

        $standard =
            Standard::find(
                $standardId
            );


        if (!$standard) {

            return [
                'classTeacher' =>
                    null,

                'principal' =>
                    null,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | SECTION FROM STANDARD
        |--------------------------------------------------------------------------
        */

        $sectionId =
            (int) (
                $standard->section_id
                ?? 0
            );


        /*
        |--------------------------------------------------------------------------
        | CLASS TEACHER
        |--------------------------------------------------------------------------
        */

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
                                    'UPPER(TRIM(designation_name)) = ?',
                                    [
                                        'CLASS TEACHER'
                                    ]
                                )
                                ->orWhereRaw(
                                    'UPPER(TRIM(designation_code)) = ?',
                                    [
                                        'CLASS_TEACHER'
                                    ]
                                )
                                ->orWhereRaw(
                                    'UPPER(TRIM(designation_code)) = ?',
                                    [
                                        'CLASS-TEACHER'
                                    ]
                                );

                            }
                        );
                    }
                )
                ->orderByDesc('id')
                ->first();


        /*
        |--------------------------------------------------------------------------
        | PRINCIPAL
        |--------------------------------------------------------------------------
        */

        $principal =
            null;


        if (
            $sectionId > 0
        ) {

            $principal =
                UserDesignation::query()
                    ->with([
                        'user',
                        'designation',
                        'designation.section',
                    ])
                    ->where(
                        'academic_year_id',
                        $academicYearId
                    )
                    ->whereHas(
                        'designation',
                        function ($query) use (
                            $sectionId
                        ) {

                            $query
                                ->where(
                                    'section_id',
                                    $sectionId
                                )
                                ->where(
                                    function ($q) {

                                        $q->whereRaw(
                                            'UPPER(TRIM(designation_name)) LIKE ?',
                                            [
                                                '%PRINCIPAL%'
                                            ]
                                        )
                                        ->orWhereRaw(
                                            'UPPER(TRIM(designation_code)) LIKE ?',
                                            [
                                                '%PRINCIPAL%'
                                            ]
                                        );

                                    }
                                );
                        }
                    )
                    ->orderByDesc('id')
                    ->first();
        }


        return [
            'classTeacher' =>
                $classTeacher,

            'principal' =>
                $principal,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | FIRST EXISTING COLUMN
    |--------------------------------------------------------------------------
    */

    private function findFirstExistingColumn(
        array $columns,
        array $candidates
    ): ?string {

        foreach (
            $candidates as $candidate
        ) {

            if (
                in_array(
                    $candidate,
                    $columns,
                    true
                )
            ) {

                return $candidate;
            }
        }


        return null;
    }
}