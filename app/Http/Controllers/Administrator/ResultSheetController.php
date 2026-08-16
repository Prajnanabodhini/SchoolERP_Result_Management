<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Models\ExamMaster;
use App\Models\Standard;
use App\Models\Division;
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

    public function search(Request $request)
    {
        $request->validate([
            'academic_year_id' => [
                'required',
                'integer',
            ],

            'exam_master_id' => [
                'required',
                'integer',
            ],

            'division_id' => [
                'required',
                'integer',
            ],
        ]);


        $exam =
            ExamMaster::find(
                (int) $request->exam_master_id
            );


        if (!$exam) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Selected Exam was not found.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | STANDARD FROM EXAM
        |--------------------------------------------------------------------------
        */

        $standardId =
            (int) (
                $exam->standard_id
                ?? 0
            );


        if ($standardId <= 0) {

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


        if ($standardId <= 0) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'The selected Exam is not mapped to a Standard.'
                );
        }


        $data =
            $this->buildResultSheetData(
                (int) $request->academic_year_id,
                (int) $request->exam_master_id,
                $standardId,
                (int) $request->division_id
            );


        if (
            !empty($data['error'])
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


        if ($mappingId > 0) {

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
            ExamMaster::orderByDesc('id')->get();

        $standards =
            Standard::orderBy('display_order')->get();

        $divisions =
            Division::orderBy('division_name')->get();

        $academicYears =
            AcademicYear::orderByDesc('id')->get();


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
        | PASSING PERCENTAGE
        |--------------------------------------------------------------------------
        */

        $passPercentage =
            $this->getOverallPassPercentage(
                $standard->standard_name
            );


        /*
        |--------------------------------------------------------------------------
        | STANDARD WISE SUBJECTS
        |--------------------------------------------------------------------------
        |
        | These determine the fixed columns.
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
        | BUILD DISPLAY COLUMNS
        |--------------------------------------------------------------------------
        */

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
                $this->normalizeSubjectText(
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
            | EXAM MASTER CONFIGURATION
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
        | SUBJECT ORDER
        |--------------------------------------------------------------------------
        */

        $displayColumns =
            $displayColumns
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


        /*
        |--------------------------------------------------------------------------
        | FIXED TOTAL MAX
        |--------------------------------------------------------------------------
        |
        | Header remains the total of all displayed subject max marks.
        |--------------------------------------------------------------------------
        */

        $totalMaxMarks =
            (float) (
                $displayColumns->sum(
                    fn ($column) =>
                        (float) (
                            $column->max_marks
                            ?? 0
                        )
                )
            );


        /*
        |--------------------------------------------------------------------------
        | LOAD ALL MARKS
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
                $standardSubjects,
                $displayColumns
            );


        /*
        |--------------------------------------------------------------------------
        | MARKS BY STUDENT / SUBJECT
        |--------------------------------------------------------------------------
        */

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


            /*
            |--------------------------------------------------------------------------
            | FIND DISPLAY SUBJECT
            |--------------------------------------------------------------------------
            */

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


            /*
            |--------------------------------------------------------------------------
            | KEEP LATEST ROW
            |--------------------------------------------------------------------------
            */

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


        /*
        |--------------------------------------------------------------------------
        | UNIQUE STUDENT LIST
        |--------------------------------------------------------------------------
        */

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
        | BUILD STUDENTS
        |--------------------------------------------------------------------------
        */

        $results =
            collect();


        foreach (
            $studentIds as $studentId
        ) {

            $studentMarks =
                $marksByStudent[
                    $studentId
                ] ?? [];


            $erp =
                $erpStudents[
                    $studentId
                ] ?? null;


            /*
            |--------------------------------------------------------------------------
            | STUDENT DETAILS
            |--------------------------------------------------------------------------
            */

            $gender =
                strtoupper(
                    trim(
                        (string) (
                            $erp->gender
                            ?? ''
                        )
                    )
                );


            $rollNo =
                $erp->rollno
                ?? '';


            $studentName =
                trim(
                    (string) (
                        $erp->studname
                        ?? ''
                    )
                );


            $fatherName =
                trim(
                    (string) (
                        $erp->fathername
                        ?? ''
                    )
                );


            $fullName =
                trim(
                    $studentName
                    . ' '
                    . $fatherName
                );


            if (
                $fullName === ''
            ) {

                $fullName =
                    'Student ID : '
                    . $studentId;
            }


            /*
            |--------------------------------------------------------------------------
            | STUDENT OBJECT
            |--------------------------------------------------------------------------
            */

            $student =
                (object) [

                    'id' =>
                        null,

                    'student_id' =>
                        $studentId,

                    'gender' =>
                        $gender,

                    'roll_no' =>
                        $rollNo,

                    'full_student_name' =>
                        $fullName,

                    'subject_marks' =>
                        [],

                    'subject_grades' =>
                        [],

                    'subject_results' =>
                        [],

                    'subject_max_used' =>
                        [],

                    'academic_total' =>
                        0,

                    'academic_max_used' =>
                        0,

                    'academic_max_display' =>
                        $totalMaxMarks,

                    'calculated_percentage' =>
                        null,

                    'calculated_grade' =>
                        '-',

                    'result' =>
                        '-',

                    'has_absent' =>
                        false,
                ];


            /*
            |--------------------------------------------------------------------------
            | PROCESS EVERY FIXED SUBJECT
            |--------------------------------------------------------------------------
            */

            foreach (
                $displayColumns as $column
            ) {

                $subjectId =
                    (int) $column->subject_id;


                /*
                |--------------------------------------------------------------------------
                | DEFAULT DISPLAY
                |--------------------------------------------------------------------------
                */

                $student->subject_marks[
                    $column->key
                ] = '-';


                $student->subject_grades[
                    $column->key
                ] = '-';


                $student->subject_results[
                    $column->key
                ] = '-';


                /*
                |--------------------------------------------------------------------------
                | FIND MARK ROW
                |--------------------------------------------------------------------------
                */

                $markRow =
                    $studentMarks[
                        $subjectId
                    ] ?? null;


                /*
                |--------------------------------------------------------------------------
                | NO MARK
                |--------------------------------------------------------------------------
                */

                if (
                    !$markRow
                ) {

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | ABSENT
                |--------------------------------------------------------------------------
                */

                if (
                    $this->isAbsentMark(
                        $markRow
                    )
                ) {

                    $student->subject_marks[
                        $column->key
                    ] = 'AB';


                    $student->subject_grades[
                        $column->key
                    ] = 'AB';


                    $student->subject_results[
                        $column->key
                    ] = 'ABSENT';


                    $student->has_absent =
                        true;


                    /*
                    | AB is included in applicable max.
                    */

                    $student->academic_max_used +=
                        (float) (
                            $column->max_marks
                            ?? 0
                        );


                    $student->subject_max_used[
                        $column->key
                    ] =
                        (float) (
                            $column->max_marks
                            ?? 0
                        );


                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | OBTAINED MARK
                |--------------------------------------------------------------------------
                */

                $obtained =
                    $this->extractObtainedMarks(
                        $markRow
                    );


                if (
                    $obtained === null
                ) {

                    continue;
                }


                $maxMarks =
                    (float) (
                        $column->max_marks
                        ?? 0
                    );


                $passingMarks =
                    (float) (
                        $column->passing_marks
                        ?? 0
                    );


                /*
                |--------------------------------------------------------------------------
                | STUDENT MARK
                |--------------------------------------------------------------------------
                */

                $student->subject_marks[
                    $column->key
                ] =
                    $this->formatMark(
                        $obtained
                    );


                /*
                |--------------------------------------------------------------------------
                | SUBJECT MAX USED
                |--------------------------------------------------------------------------
                */

                $student->academic_max_used +=
                    $maxMarks;


                $student->subject_max_used[
                    $column->key
                ] =
                    $maxMarks;


                /*
                |--------------------------------------------------------------------------
                | TOTAL
                |--------------------------------------------------------------------------
                */

                $student->academic_total +=
                    $obtained;


                /*
                |--------------------------------------------------------------------------
                | SUBJECT %
                |--------------------------------------------------------------------------
                */

                $subjectPercentage =
                    $maxMarks > 0
                        ? (
                            $obtained /
                            $maxMarks
                        ) * 100
                        : 0;


                /*
                |--------------------------------------------------------------------------
                | SUBJECT GRADE
                |--------------------------------------------------------------------------
                */

                $student->subject_grades[
                    $column->key
                ] =
                    $this->getGradeFromPercentage(
                        $subjectPercentage
                    );


                /*
                |--------------------------------------------------------------------------
                | SUBJECT RESULT
                |--------------------------------------------------------------------------
                */

                $student->subject_results[
                    $column->key
                ] =
                    $obtained >= $passingMarks
                        ? 'PASS'
                        : 'FAIL';
            }


            /*
            |--------------------------------------------------------------------------
            | FORMAT TOTAL
            |--------------------------------------------------------------------------
            */

            $student->academic_total =
                $this->formatMark(
                    $student->academic_total
                );


            $student->academic_max_used =
                (float) $student->academic_max_used;


            /*
            |--------------------------------------------------------------------------
            | NO MARKS AT ALL
            |--------------------------------------------------------------------------
            */

            if (
                $student->academic_max_used <= 0
            ) {

                $student->calculated_percentage =
                    null;

                $student->calculated_grade =
                    '-';

                $student->result =
                    '-';

            } else {

                /*
                |--------------------------------------------------------------------------
                | DYNAMIC PERCENTAGE
                |--------------------------------------------------------------------------
                |
                | Only subjects having marks/AB are included.
                |--------------------------------------------------------------------------
                */

                $student->calculated_percentage =
                    (
                        (float) $student->academic_total
                        /
                        (float) $student->academic_max_used
                    )
                    * 100;


                /*
                |--------------------------------------------------------------------------
                | ROUND %
                |--------------------------------------------------------------------------
                */

                $student->calculated_percentage =
                    (int) round(
                        $student->calculated_percentage
                    );


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
                | CHECK FAILED SUBJECT
                |--------------------------------------------------------------------------
                */

                $hasFailedSubject =
                    false;


                foreach (
                    $displayColumns as $column
                ) {

                    $subjectResult =
                        strtoupper(
                            trim(
                                (string) (
                                    $student->subject_results[
                                        $column->key
                                    ] ?? '-'
                                )
                            )
                        );


                    if (
                        $subjectResult === 'FAIL'
                    ) {

                        $hasFailedSubject =
                            true;

                        break;
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | FINAL RESULT
                |--------------------------------------------------------------------------
                */

                if (
                    $student->has_absent
                ) {

                    $student->result =
                        'FAIL';

                } elseif (
                    $hasFailedSubject
                ) {

                    $student->result =
                        'FAIL';

                } elseif (
                    $student->calculated_percentage
                    >=
                    $passPercentage
                ) {

                    $student->result =
                        'PASS';

                } else {

                    $student->result =
                        'FAIL';
                }
            }


            $results->push(
                $student
            );
        }


        /*
        |--------------------------------------------------------------------------
        | SORT BY STUDENT NAME
        |--------------------------------------------------------------------------
        */

        $results =
            $results
                ->sort(
                    function (
                        $a,
                        $b
                    ) {

                        return strcmp(
                            strtoupper(
                                $a->full_student_name
                                ?? ''
                            ),
                            strtoupper(
                                $b->full_student_name
                                ?? ''
                            )
                        );
                    }
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | ANALYSIS
        |--------------------------------------------------------------------------
        */

        $overallGradeAnalysis =
            $this->buildOverallGradeAnalysis(
                $results
            );


        $girlsSubjectAnalysis =
            $this->buildSubjectAnalysis(
                $results,
                $displayColumns,
                'FEMALE'
            );


        $boysSubjectAnalysis =
            $this->buildSubjectAnalysis(
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

                /*
                | Header maximum remains all fixed subjects.
                */

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
    | LOAD MARKS
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


        /*
        |--------------------------------------------------------------------------
        | ACADEMIC YEAR
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | EXAM
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | STANDARD
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | DIVISION
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | LATEST ROW FIRST
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
                'id'
            );
        }


        return $query->get();
    }


    /*
    |--------------------------------------------------------------------------
    | BUILD SUBJECT MATCH MAP
    |--------------------------------------------------------------------------
    */

    private function buildSubjectMatchMap(
        $standardSubjects,
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
                $this->normalizeSubjectText(
                    $column->subject_name
                );


            if ($subjectId > 0) {

                $map['canonical'][
                    $subjectId
                ] =
                    $subjectId;
            }


            if ($mappingId > 0) {

                $map['mapping'][
                    $mappingId
                ] =
                    $subjectId;
            }


            if ($code !== '') {

                $map['code'][
                    $code
                ] =
                    $subjectId;
            }


            if ($name !== '') {

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
    | RESOLVE MARK SUBJECT ID
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
        | 1. EXACT CANONICAL SUBJECT ID
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
        | 2. LEGACY MAPPING ID
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
        | 3. SUBJECT CODE FROM MARK ROW
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
        | 4. SUBJECT NAME FROM MARK ROW
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
                    $this->normalizeSubjectText(
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


        /*
        |--------------------------------------------------------------------------
        | 5. SOME LEGACY DATA STORES SUBJECT ID AS STRING
        |--------------------------------------------------------------------------
        */

        $storedSubjectString =
            trim(
                (string) (
                    $markRow->subject_id
                    ?? ''
                )
            );


        if (
            $storedSubjectString !== ''
        ) {

            foreach (
                $subjectMatchMap['canonical']
                as $canonicalId => $targetId
            ) {

                if (
                    (string)$canonicalId ===
                    $storedSubjectString
                ) {

                    return
                        $targetId;
                }
            }


            foreach (
                $subjectMatchMap['mapping']
                as $mappingId => $targetId
            ) {

                if (
                    (string)$mappingId ===
                    $storedSubjectString
                ) {

                    return
                        $targetId;
                }
            }
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | NORMALIZE SUBJECT TEXT
    |--------------------------------------------------------------------------
    */

    private function normalizeSubjectText(
        $value
    ): string {

        $value =
            strtoupper(
                trim(
                    (string)$value
                )
            );


        $value =
            preg_replace(
                '/[^A-Z0-9]+/',
                '',
                $value
            );


        return $value;
    }


    /*
    |--------------------------------------------------------------------------
    | EXTRACT MARK
    |--------------------------------------------------------------------------
    */

    private function extractObtainedMarks(
        $row
    ): ?float {

        /*
        |--------------------------------------------------------------------------
        | DIRECT TOTAL MARK
        |--------------------------------------------------------------------------
        */

        foreach (
            [
                'obtained_marks',
                'marks',
                'total_obtained_marks',
            ] as $field
        ) {

            if (
                isset(
                    $row->{$field}
                )
                &&
                $row->{$field} !== ''
                &&
                $row->{$field} !== null
                &&
                is_numeric(
                    $row->{$field}
                )
            ) {

                return
                    (float)$row->{$field};
            }
        }


        /*
        |--------------------------------------------------------------------------
        | COMPONENT MARKS
        |--------------------------------------------------------------------------
        */

        $found =
            false;


        $total =
            0;


        foreach (
            [
                'theory_obtained_marks',
                'oral_obtained_marks',
                'practical_obtained_marks',
            ] as $field
        ) {

            if (
                isset(
                    $row->{$field}
                )
                &&
                $row->{$field} !== ''
                &&
                $row->{$field} !== null
                &&
                is_numeric(
                    $row->{$field}
                )
            ) {

                $found =
                    true;

                $total +=
                    (float)$row->{$field};
            }
        }


        return
            $found
                ? $total
                : null;
    }


    /*
    |--------------------------------------------------------------------------
    | ABSENT
    |--------------------------------------------------------------------------
    */

    private function isAbsentMark(
        $row
    ): bool {

        if (
            isset(
                $row->is_absent
            )
            &&
            (int)$row->is_absent === 1
        ) {

            return true;
        }


        foreach (
            [
                'status',
                'marks_status',
                'attendance_status',
            ] as $field
        ) {

            if (
                isset(
                    $row->{$field}
                )
                &&
                strtoupper(
                    trim(
                        (string)$row->{$field}
                    )
                ) === 'AB'
            ) {

                return true;
            }
        }


        foreach (
            [
                'obtained_marks',
                'marks',
            ] as $field
        ) {

            if (
                isset(
                    $row->{$field}
                )
                &&
                strtoupper(
                    trim(
                        (string)$row->{$field}
                    )
                ) === 'AB'
            ) {

                return true;
            }
        }


        return false;
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
                    (int)$row->Studentid
                ] =
                    $row;
            }

        } catch (
            \Throwable $e
        ) {
            /*
            | Keep result usable even when ERP connection fails.
            */
        }


        return $students;
    }


    /*
    |--------------------------------------------------------------------------
    | FIND COLUMN
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


    /*
    |--------------------------------------------------------------------------
    | FORMAT MARK
    |--------------------------------------------------------------------------
    */

    private function formatMark(
        $mark
    ) {

        $mark =
            (float)$mark;


        if (
            floor($mark) === $mark
        ) {

            return (int)$mark;
        }


        return round(
            $mark,
            2
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PASS %
    |--------------------------------------------------------------------------
    */

    private function getOverallPassPercentage(
        string $standardName
    ): int {

        $name =
            strtoupper(
                trim(
                    $standardName
                )
            );


        if (
            in_array(
                $name,
                [
                    'NINTH',
                    'TENTH',
                    '9TH',
                    '10TH',
                    'IX',
                    'X',
                ],
                true
            )
        ) {

            return 35;
        }


        return 40;
    }


    /*
    |--------------------------------------------------------------------------
    | GRADE
    |--------------------------------------------------------------------------
    */

    private function getGradeFromPercentage(
        $percentage
    ): string {

        $percentage =
            (float)$percentage;


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

        return 'F';
    }


    /*
    |--------------------------------------------------------------------------
    | OVERALL ANALYSIS
    |--------------------------------------------------------------------------
    */

    private function buildOverallGradeAnalysis(
        $results
    ): array {

        $analysis = [

            'A1' => [
                'range' => '91-100%',
                'girls' => 0,
                'boys' => 0,
                'total' => 0,
            ],

            'A2' => [
                'range' => '81-90%',
                'girls' => 0,
                'boys' => 0,
                'total' => 0,
            ],

            'B1' => [
                'range' => '71-80%',
                'girls' => 0,
                'boys' => 0,
                'total' => 0,
            ],

            'B2' => [
                'range' => '61-70%',
                'girls' => 0,
                'boys' => 0,
                'total' => 0,
            ],

            'C1' => [
                'range' => '51-60%',
                'girls' => 0,
                'boys' => 0,
                'total' => 0,
            ],

            'C2' => [
                'range' => '41-50%',
                'girls' => 0,
                'boys' => 0,
                'total' => 0,
            ],

            'D' => [
                'range' => '33-40%',
                'girls' => 0,
                'boys' => 0,
                'total' => 0,
            ],

            'F' => [
                'range' => 'Below 33%',
                'girls' => 0,
                'boys' => 0,
                'total' => 0,
            ],

            'PASS' => [
                'range' => 'PASS',
                'girls' => 0,
                'boys' => 0,
                'total' => 0,
            ],

            'FAIL' => [
                'range' => 'FAIL',
                'girls' => 0,
                'boys' => 0,
                'total' => 0,
            ],
        ];


        foreach (
            $results as $student
        ) {

            if (
                ($student->result ?? '-') === '-'
            ) {

                continue;
            }


            $gender =
                strtoupper(
                    trim(
                        $student->gender
                        ?? ''
                    )
                ) === 'FEMALE'
                    ? 'girls'
                    : 'boys';


            $grade =
                strtoupper(
                    trim(
                        $student->calculated_grade
                        ?? '-'
                    )
                );


            if (
                isset(
                    $analysis[$grade]
                )
            ) {

                $analysis[$grade][$gender]++;
                $analysis[$grade]['total']++;
            }


            $result =
                strtoupper(
                    trim(
                        $student->result
                        ?? '-'
                    )
                );


            if (
                $result === 'PASS'
            ) {

                $analysis['PASS'][$gender]++;
                $analysis['PASS']['total']++;

            } elseif (
                $result === 'FAIL'
            ) {

                $analysis['FAIL'][$gender]++;
                $analysis['FAIL']['total']++;
            }
        }


        return $analysis;
    }


    /*
    |--------------------------------------------------------------------------
    | SUBJECT ANALYSIS
    |--------------------------------------------------------------------------
    */

    private function buildSubjectAnalysis(
        $results,
        $subjects,
        string $gender
    ): array {

        $analysis =
            [];


        foreach (
            $subjects as $subject
        ) {

            $row = [

                'subject' =>
                    $subject->subject_code,

                'subject_name' =>
                    $subject->subject_name,

                'subject_code' =>
                    $subject->subject_code,

                'A1' => 0,
                'A2' => 0,
                'B1' => 0,
                'B2' => 0,
                'C1' => 0,
                'C2' => 0,
                'D' => 0,

                'fail' => 0,
                'absent' => 0,
                'total' => 0,
            ];


            foreach (
                $results as $student
            ) {

                $studentGender =
                    strtoupper(
                        trim(
                            $student->gender
                            ?? ''
                        )
                    );


                if (
                    $studentGender !==
                    strtoupper($gender)
                ) {

                    continue;
                }


                $mark =
                    $student->subject_marks[
                        $subject->key
                    ] ?? '-';


                $grade =
                    $student->subject_grades[
                        $subject->key
                    ] ?? '-';


                /*
                |--------------------------------------------------------------------------
                | NO MARK
                |--------------------------------------------------------------------------
                */

                if (
                    $mark === '-'
                ) {

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | ABSENT
                |--------------------------------------------------------------------------
                */

                if (
                    strtoupper(
                        trim(
                            (string)$mark
                        )
                    ) === 'AB'
                ) {

                    $row['absent']++;

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | FAIL
                |--------------------------------------------------------------------------
                */

                if (
                    strtoupper(
                        trim(
                            (string)$grade
                        )
                    ) === 'F'
                ) {

                    $row['fail']++;

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | GRADE
                |--------------------------------------------------------------------------
                */

                if (
                    isset(
                        $row[$grade]
                    )
                ) {

                    $row[$grade]++;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | ANALYSIS TOTAL
            |--------------------------------------------------------------------------
            */

            $row['total'] =
                $row['A1']
                + $row['A2']
                + $row['B1']
                + $row['B2']
                + $row['C1']
                + $row['C2']
                + $row['D']
                + $row['fail']
                + $row['absent'];


            $analysis[] =
                $row;
        }


        return $analysis;
    }


    /*
    |--------------------------------------------------------------------------
    | PRINT
    |--------------------------------------------------------------------------
    */

    public function print(
        Request $request
    ) {

        $request->validate([
            'academic_year_id' => [
                'required',
                'integer',
            ],

            'exam_master_id' => [
                'required',
                'integer',
            ],

            'division_id' => [
                'required',
                'integer',
            ],
        ]);


        $exam =
            ExamMaster::find(
                (int)$request->exam_master_id
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
            (int)(
                $exam->standard_id
                ?? 0
            );


        if (
            $standardId <= 0
        ) {

            $standardId =
                (int)(
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
                (int)$request->academic_year_id,
                (int)$request->exam_master_id,
                $standardId,
                (int)$request->division_id
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
}