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
use App\Models\UserDesignation;

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
                'exams' =>
                    ExamMaster::orderByDesc('id')->get(),

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

        $standardId =
            (int) (
                $exam->standard_id
                ?? 0
            );

        if ($standardId <= 0) {
            if (Schema::hasTable('exam_master_subjects')) {
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

        if (!empty($data['error'])) {
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

        $classTeacher = null;

        try {
            $classTeacher = UserDesignation::query()
    ->with([
        'user',
        'designation',
    ])
    ->where('academic_year_id', $academicYearId)
    ->where('standard_id', $standardId)
    ->where('division_id', $divisionId)
    ->whereHas('designation', function ($query) {
        $query->where(function ($q) {
            $q->whereRaw(
                'UPPER(TRIM(designation_name)) LIKE ?',
                ['%CLASS TEACHER%']
            )
            ->orWhereRaw(
                'UPPER(TRIM(designation_code)) LIKE ?',
                ['%CLASS_TEACHER%']
            )
            ->orWhereRaw(
                'UPPER(TRIM(designation_code)) LIKE ?',
                ['%CLASS-TEACHER%']
            );
        });
    })
    ->orderByDesc('id')
    ->first();

        } catch (\Throwable $e) {
            $classTeacher = null;
        }

        /*
        |--------------------------------------------------------------------------
        | PRINCIPAL
        |--------------------------------------------------------------------------
        */

        $principal = null;

        if ($sectionId > 0) {
            try {
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
                            function ($query) use ($sectionId) {
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
            } catch (\Throwable $e) {
                $principal = null;
            }
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
    | EXAM SUBJECT CONFIGURATION
    |--------------------------------------------------------------------------
    */

    private function resolveExamSubjectConfig(
        int $examMasterId,
        int $standardId,
        int $canonicalSubjectId,
        int $mappingId
    ) {
        if (
            !Schema::hasTable(
                'exam_master_subjects'
            )
        ) {
            return null;
        }

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

        /*
        |--------------------------------------------------------------------------
        | OLD MAPPING FALLBACK
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | SELECTED DATA
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
            $this->getOverallPassPercentage(
                $standard->standard_name
            );

        /*
        |--------------------------------------------------------------------------
        | STANDARD WISE SUBJECTS
        |--------------------------------------------------------------------------
        |
        | STANDARD_WISE_SUBJECTS is the source of truth for the columns.
        |
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
                'sws.subject_name as standard_subject_name',
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
                        ??
                        $subject->standard_subject_name
                        ??
                        ''
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

                if ($short !== '') {
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

            /*
            |--------------------------------------------------------------------------
            | SPECIAL CODES
            |--------------------------------------------------------------------------
            */

            $normalizedName =
                $this->normalizeSubjectText(
                    $subjectName
                );

            if (
                $normalizedName === 'HISTORY'
            ) {
                $subjectCode = 'HIST';
            }

            if (
                $normalizedName === 'GEOGRAPHY'
            ) {
                $subjectCode = 'GEO';
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

            /*
            |--------------------------------------------------------------------------
            | MAXIMUM MARKS
            |--------------------------------------------------------------------------
            */

            $maxMarks =
                $examConfig
                    ? (float) (
                        $examConfig->max_marks
                        ?? 0
                    )
                    : 0;

            /*
            |--------------------------------------------------------------------------
            | PASSING MARKS
            |--------------------------------------------------------------------------
            */

            $passingMarks =
                $examConfig
                    ? (float) (
                        $examConfig->passing_marks
                        ?? 0
                    )
                    : 0;

            /*
            |--------------------------------------------------------------------------
            | FALLBACK PASSING MARKS
            |--------------------------------------------------------------------------
            */

            if (
                $passingMarks <= 0 &&
                $maxMarks > 0
            ) {
                $passingMarks =
                    round(
                        (
                            $maxMarks *
                            $passPercentage
                        ) / 100,
                        2
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | COLUMN
            |--------------------------------------------------------------------------
            */

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
                            1000 +
                            (int) (
                                $subject->sort_order
                                ?? 9999
                            );
                    }
                )
                ->values();

        /*
        |--------------------------------------------------------------------------
        | TOTAL MAXIMUM MARKS
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | Always use ALL displayed subjects.
        | Missing marks do NOT reduce the denominator.
        |
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
        | LOAD MARKS
        |--------------------------------------------------------------------------
        |
        | NO "NO STUDENT MARKS FOUND" ERROR.
        |
        | Even when marks are missing, subjects still appear and show "-".
        |
        */

        $allMarks =
            $this->loadMarks(
                $academicYearId,
                $examMasterId,
                $standardId,
                $divisionId
            );

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

        $marksByStudent = [];

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
                    $marksByStudent[$studentId]
                )
            ) {
                $marksByStudent[$studentId] = [];
            }

            /*
            |--------------------------------------------------------------------------
            | KEEP NEWEST ROW
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
        | UNIQUE STUDENT IDS
        |--------------------------------------------------------------------------
        */

        $studentIds =
            array_values(
                array_unique(
                    array_filter(
                        array_map(
                            'intval',
                            array_keys(
                                $marksByStudent
                            )
                        ),
                        fn ($id) =>
                            $id > 0
                    )
                )
            );

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
        | BUILD RESULT ROWS
        |--------------------------------------------------------------------------
        */

        $results =
            collect();

        foreach (
            $studentIds as $studentId
        ) {
            $studentId =
                (int) $studentId;

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
            | STUDENT DATA
            |--------------------------------------------------------------------------
            */

            $gender =
                $this->normalizeGender(
                    $erp->gender ?? ''
                );

            $rollNo =
                trim(
                    (string) (
                        $erp->rollno
                        ?? ''
                    )
                );

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

                    'subject_passing_used' =>
                        [],

                    'academic_total' =>
                        0,

                    /*
                    |--------------------------------------------------------------------------
                    | IMPORTANT:
                    | ALWAYS FULL TOTAL MAX
                    |--------------------------------------------------------------------------
                    */

                    'academic_max_used' =>
                        $totalMaxMarks,

                    'academic_max_display' =>
                        $totalMaxMarks,

                    'calculated_percentage' =>
                        null,

                    'calculated_grade' =>
                        '-',

                    'result' =>
                        'PENDING',

                    'has_absent' =>
                        false,

                    'has_any_mark' =>
                        false,
                ];

            /*
            |--------------------------------------------------------------------------
            | PROCESS ALL STANDARD-WISE SUBJECTS
            |--------------------------------------------------------------------------
            */

            foreach (
                $displayColumns as $column
            ) {
                $subjectId =
                    (int) (
                        $column->subject_id
                        ?? 0
                    );

                $key =
                    $column->key
                    ??
                    'SUBJECT_' . $subjectId;

                /*
                |--------------------------------------------------------------------------
                | DEFAULT
                |--------------------------------------------------------------------------
                */

                $student->subject_marks[
                    $key
                ] = '-';

                $student->subject_grades[
                    $key
                ] = '-';

                $student->subject_results[
                    $key
                ] = '-';

                $student->subject_max_used[
                    $key
                ] =
                    (float) (
                        $column->max_marks
                        ?? 0
                    );

                $student->subject_passing_used[
                    $key
                ] =
                    (float) (
                        $column->passing_marks
                        ?? 0
                    );

                /*
                |--------------------------------------------------------------------------
                | FIND MARK BY CANONICAL SUBJECT ID
                |--------------------------------------------------------------------------
                */

                $markRow =
                    $studentMarks[
                        $subjectId
                    ] ?? null;

                if (
                    !$markRow
                ) {
                    /*
                    | No mark:
                    | keep "-"
                    */
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
                        $key
                    ] = 'AB';

                    $student->subject_grades[
                        $key
                    ] = 'AB';

                    $student->subject_results[
                        $key
                    ] = 'ABSENT';

                    $student->has_absent =
                        true;

                    $student->has_any_mark =
                        true;

                    /*
                    | AB contributes 0 to total.
                    | Maximum remains full total.
                    */

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | OBTAINED
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

                $student->has_any_mark =
                    true;

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
                | SAFETY
                |--------------------------------------------------------------------------
                */

                if (
                    $maxMarks > 0 &&
                    $obtained > $maxMarks
                ) {
                    $obtained =
                        $maxMarks;
                }

                if (
                    $obtained < 0
                ) {
                    $obtained = 0;
                }

                /*
                |--------------------------------------------------------------------------
                | DISPLAY MARK
                |--------------------------------------------------------------------------
                */

                $student->subject_marks[
                    $key
                ] =
                    $this->formatMark(
                        $obtained
                    );

                /*
                |--------------------------------------------------------------------------
                | TOTAL
                |--------------------------------------------------------------------------
                */

                $student->academic_total +=
                    $obtained;

                /*
                |--------------------------------------------------------------------------
                | SUBJECT PERCENTAGE
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
                    $key
                ] =
                    $this->getGradeFromPercentage(
                        $subjectPercentage
                    );

                /*
                |--------------------------------------------------------------------------
                | SUBJECT RESULT
                |--------------------------------------------------------------------------
                */

                if (
                    $passingMarks > 0
                ) {
                    $student->subject_results[
                        $key
                    ] =
                        $obtained >= $passingMarks
                            ? 'PASS'
                            : 'FAIL';
                } else {
                    $student->subject_results[
                        $key
                    ] = 'PASS';
                }
            }

            /*
            |--------------------------------------------------------------------------
            | TOTAL
            |--------------------------------------------------------------------------
            */

            $student->academic_total =
                $this->formatMark(
                    $student->academic_total
                );

            /*
            |--------------------------------------------------------------------------
            | ALWAYS FULL TOTAL MAX
            |--------------------------------------------------------------------------
            */

            $student->academic_max_used =
                $totalMaxMarks;

            $student->academic_max_display =
                $totalMaxMarks;

            /*
            |--------------------------------------------------------------------------
            | NO MARKS AT ALL
            |--------------------------------------------------------------------------
            */

            if (
                !$student->has_any_mark &&
                !$student->has_absent
            ) {
                $student->calculated_percentage =
                    null;

                $student->calculated_grade =
                    '-';

                $student->result =
                    'PENDING';

                $results->push(
                    $student
                );

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | OVERALL PERCENTAGE
            |--------------------------------------------------------------------------
            |
            | ALWAYS:
            |
            | Obtained / ALL SUBJECT MAXIMUM
            |
            |--------------------------------------------------------------------------
            */

            if (
                $totalMaxMarks > 0
            ) {
                $percentage =
                    (
                        (float) $student->academic_total
                        /
                        (float) $totalMaxMarks
                    )
                    * 100;

                $student->calculated_percentage =
                    round(
                        $percentage,
                        2
                    );
            } else {
                $student->calculated_percentage =
                    null;
            }

            /*
            |--------------------------------------------------------------------------
            | OVERALL GRADE
            |--------------------------------------------------------------------------
            */

            if (
                $student->calculated_percentage !== null
            ) {
                $student->calculated_grade =
                    $this->getGradeFromPercentage(
                        $student->calculated_percentage
                    );
            } else {
                $student->calculated_grade =
                    '-';
            }

            /*
            |--------------------------------------------------------------------------
            | FAILED SUBJECT
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
                                ]
                                ??
                                '-'
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
                $student->calculated_percentage !== null
                &&
                $student->calculated_percentage >=
                $passPercentage
            ) {
                $student->result =
                    'PASS';

            } else {
                $student->result =
                    'FAIL';
            }

            /*
            |--------------------------------------------------------------------------
            | ADD STUDENT
            |--------------------------------------------------------------------------
            */

            $results->push(
                $student
            );
        }

        /*
        |--------------------------------------------------------------------------
        | UNIQUE STUDENT ROWS
        |--------------------------------------------------------------------------
        |
        | Student ID is the unique key.
        |
        */

        $uniqueStudents = [];

        foreach (
            $results as $student
        ) {
            $studentId =
                (int) (
                    $student->student_id
                    ?? 0
                );

            if (
                $studentId <= 0
            ) {
                continue;
            }

            $uniqueStudents[
                'STUDENT_' . $studentId
            ] =
                $student;
        }

        $results =
            collect(
                array_values(
                    $uniqueStudents
                )
            );

        /*
        |--------------------------------------------------------------------------
        | SORT
        |--------------------------------------------------------------------------
        */

        $results =
            $results
                ->sortBy(
                    function ($student) {
                        $rollNo =
                            trim(
                                (string) (
                                    $student->roll_no
                                    ?? ''
                                )
                            );

                        if (
                            $rollNo !== '' &&
                            is_numeric($rollNo)
                        ) {
                            return [
                                0,
                                (int) $rollNo,
                            ];
                        }

                        return [
                            1,
                            strtoupper($rollNo),
                        ];
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
        | RETURN
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
    | LOAD MARKS
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | Uses the actual student_marks table structure.
    |
    | There is NO obtained_marks column.
    |
    |--------------------------------------------------------------------------
    */

    private function loadMarks(
        int $academicYearId,
        int $examMasterId,
        int $standardId,
        int $divisionId
    ) {
        if (
            !Schema::hasTable(
                'student_marks'
            )
        ) {
            return collect();
        }

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

        if (
            in_array(
                'academic_year_id',
                $columns,
                true
            )
        ) {
            $query->where(
                'academic_year_id',
                $academicYearId
            );
        }

        /*
        |--------------------------------------------------------------------------
        | EXAM
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                'exam_master_id',
                $columns,
                true
            )
        ) {
            $query->where(
                'exam_master_id',
                $examMasterId
            );
        }

        /*
        |--------------------------------------------------------------------------
        | STANDARD
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                'standard_id',
                $columns,
                true
            )
        ) {
            $query->where(
                'standard_id',
                $standardId
            );
        }

        /*
        |--------------------------------------------------------------------------
        | DIVISION
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                'division_id',
                $columns,
                true
            )
        ) {
            $query->where(
                'division_id',
                $divisionId
            );
        }

        /*
        |--------------------------------------------------------------------------
        | NEWEST FIRST
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
                (int) (
                    $column->subject_id
                    ?? 0
                );

            $mappingId =
                (int) (
                    $column->mapping_id
                    ?? 0
                );

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
                    ?? ''
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
        | 1. CANONICAL SUBJECT ID
        |--------------------------------------------------------------------------
        */

        if (
            $storedSubjectId > 0 &&
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
            $storedSubjectId > 0 &&
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
        | 3. SUBJECT CODE
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
                            (string) (
                                $markRow->{$field}
                            )
                        )
                    );

                if (
                    $code !== '' &&
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
        | 4. SUBJECT NAME
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
                    $name !== '' &&
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
    | NORMALIZE SUBJECT TEXT
    |--------------------------------------------------------------------------
    */

    private function normalizeSubjectText(
        $value
    ): string {
        return
            preg_replace(
                '/[^A-Z0-9]+/',
                '',
                strtoupper(
                    trim(
                        (string) $value
                    )
                )
            ) ?? '';
    }

    /*
    |--------------------------------------------------------------------------
    | NORMALIZE GENDER
    |--------------------------------------------------------------------------
    */

    private function normalizeGender(
        $gender
    ): string {
        $original =
            trim(
                (string) $gender
            );

        /*
        |--------------------------------------------------------------------------
        | ERP VALUES
        |--------------------------------------------------------------------------
        |
        | 1 = Male
        | 2 = Female
        |
        */

        if (
            $original === '1'
        ) {
            return 'MALE';
        }

        if (
            $original === '2'
        ) {
            return 'FEMALE';
        }

        $gender =
            strtoupper(
                $original
            );

        $gender =
            preg_replace(
                '/[^A-Z]/',
                '',
                $gender
            ) ?? '';

        if (
            in_array(
                $gender,
                [
                    'F',
                    'FEMALE',
                    'GIRL',
                    'GIRLS',
                    'WOMAN',
                    'WOMEN',
                ],
                true
            )
        ) {
            return 'FEMALE';
        }

        if (
            in_array(
                $gender,
                [
                    'M',
                    'MALE',
                    'BOY',
                    'BOYS',
                    'MAN',
                    'MEN',
                ],
                true
            )
        ) {
            return 'MALE';
        }

        return 'UNKNOWN';
    }

    /*
    |--------------------------------------------------------------------------
    | EXTRACT MARK
    |--------------------------------------------------------------------------
    |
    | Uses only the actual student_marks fields.
    |
    |--------------------------------------------------------------------------
    */

    private function extractObtainedMarks(
        $row
    ): ?float {
        if (!$row) {
            return null;
        }

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
                ) &&
                $row->{$field} !== null &&
                $row->{$field} !== '' &&
                is_numeric(
                    $row->{$field}
                )
            ) {
                $found = true;

                $total +=
                    (float) $row->{$field};
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
    |
    | IMPORTANT:
    |
    | student_marks.is_absent is tinyint(1).
    |
    |--------------------------------------------------------------------------
    */

    private function isAbsentMark(
        $row
    ): bool {
        if (!$row) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | REAL FIELD IN YOUR TABLE
        |--------------------------------------------------------------------------
        */

        if (
            isset(
                $row->is_absent
            )
        ) {
            $value =
                strtoupper(
                    trim(
                        (string) (
                            $row->is_absent
                        )
                    )
                );

            if (
                in_array(
                    $value,
                    [
                        '1',
                        'TRUE',
                        'YES',
                        'Y',
                        'AB',
                        'A',
                        'ABS',
                        'ABSENT',
                    ],
                    true
                )
            ) {
                return true;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | OPTIONAL LEGACY STATUS FIELDS
        |--------------------------------------------------------------------------
        */

        foreach (
            [
                'status',
                'marks_status',
                'attendance_status',
                'mark_status',
            ] as $field
        ) {
            if (
                !isset(
                    $row->{$field}
                )
            ) {
                continue;
            }

            $value =
                strtoupper(
                    trim(
                        (string) (
                            $row->{$field}
                        )
                    )
                );

            if (
                in_array(
                    $value,
                    [
                        'AB',
                        'A',
                        'ABS',
                        'ABSENT',
                        'NOT PRESENT',
                        'NOTPRESENT',
                    ],
                    true
                )
            ) {
                return true;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | CHECK COMPONENT FIELDS FOR AB
        |--------------------------------------------------------------------------
        */

        foreach (
            [
                'theory_obtained_marks',
                'oral_obtained_marks',
                'practical_obtained_marks',
            ] as $field
        ) {
            if (
                !isset(
                    $row->{$field}
                )
            ) {
                continue;
            }

            $value =
                strtoupper(
                    trim(
                        (string) (
                            $row->{$field}
                        )
                    )
                );

            if (
                in_array(
                    $value,
                    [
                        'AB',
                        'A',
                        'ABS',
                        'ABSENT',
                    ],
                    true
                )
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

            /*
            |--------------------------------------------------------------------------
            | ONE ERP ROW PER STUDENT ID
            |--------------------------------------------------------------------------
            */

            foreach (
                $rows as $row
            ) {
                $studentId =
                    (int) (
                        $row->Studentid
                        ?? 0
                    );

                if (
                    $studentId <= 0
                ) {
                    continue;
                }

                if (
                    !isset(
                        $students[$studentId]
                    )
                ) {
                    $students[$studentId] =
                        $row;
                }
            }
        } catch (
            \Throwable $e
        ) {
            /*
            |--------------------------------------------------------------------------
            | Keep result usable if ERP connection fails.
            |--------------------------------------------------------------------------
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
            (float) $mark;

        if (
            floor($mark) === $mark
        ) {
            return (int) $mark;
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
                    'NINE',
                    'TEN',
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
            (float) $percentage;

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
    | OVERALL GRADE ANALYSIS
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

            'TOTAL' => [
                'range' => 'TOTAL',
                'girls' => 0,
                'boys' => 0,
                'total' => 0,
            ],
        ];

        foreach (
            $results as $student
        ) {
            $result =
                strtoupper(
                    trim(
                        (string) (
                            $student->result
                            ?? '-'
                        )
                    )
                );

            if (
                !in_array(
                    $result,
                    [
                        'PASS',
                        'FAIL',
                    ],
                    true
                )
            ) {
                continue;
            }

            $gender =
                $this->normalizeGender(
                    $student->gender
                    ?? ''
                );

            $genderKey = match ($gender) {
                'FEMALE' => 'girls',
                'MALE' => 'boys',
                default => null,
            };

            if ($genderKey !== null) {
                $analysis['TOTAL'][
                    $genderKey
                ]++;
            }

            $analysis['TOTAL']['total']++;

            $grade =
                strtoupper(
                    trim(
                        (string) (
                            $student->calculated_grade
                            ?? '-'
                        )
                    )
                );

            if (
                isset(
                    $analysis[$grade]
                ) &&
                in_array(
                    $grade,
                    [
                        'A1',
                        'A2',
                        'B1',
                        'B2',
                        'C1',
                        'C2',
                        'D',
                        'F',
                    ],
                    true
                )
            ) {
                $analysis[$grade]['total']++;

                if ($genderKey !== null) {
                    $analysis[$grade][
                        $genderKey
                    ]++;
                }
            }

            if (
                $result === 'PASS'
            ) {
                $analysis['PASS']['total']++;

                if ($genderKey !== null) {
                    $analysis['PASS'][
                        $genderKey
                    ]++;
                }
            } else {
                $analysis['FAIL']['total']++;

                if ($genderKey !== null) {
                    $analysis['FAIL'][
                        $genderKey
                    ]++;
                }
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
        $analysis = [];

        $requestedGender =
            $this->normalizeGender(
                $gender
            );

        /*
        |--------------------------------------------------------------------------
        | SUBJECT BY SUBJECT
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | One student must be counted only once for one subject.
        |
        | This prevents duplicate rows / duplicate counting from inflating
        | A1, A2, B1, B2, C1, C2, D, FAIL and ABSENT totals.
        |
        | No other result/marks calculation is changed.
        |--------------------------------------------------------------------------
        */

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

                'A1' =>
                    0,

                'A2' =>
                    0,

                'B1' =>
                    0,

                'B2' =>
                    0,

                'C1' =>
                    0,

                'C2' =>
                    0,

                'D' =>
                    0,

                'fail' =>
                    0,

                'absent' =>
                    0,

                'total' =>
                    0,
            ];

            /*
            |--------------------------------------------------------------------------
            | UNIQUE STUDENTS FOR THIS SUBJECT
            |--------------------------------------------------------------------------
            |
            | Student ID is the primary uniqueness key.
            |--------------------------------------------------------------------------
            */

            $countedStudents = [];

            foreach (
                $results as $student
            ) {

                /*
                |--------------------------------------------------------------------------
                | STUDENT ID
                |--------------------------------------------------------------------------
                */

                $studentId =
                    (int) (
                        $student->student_id
                        ?? $student->id
                        ?? 0
                    );

                if (
                    $studentId <= 0
                ) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | GENDER
                |--------------------------------------------------------------------------
                */

                $studentGender =
                    $this->normalizeGender(
                        $student->gender
                        ?? ''
                    );

                if (
                    $studentGender !==
                    $requestedGender
                ) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | DUPLICATE PROTECTION
                |--------------------------------------------------------------------------
                |
                | A student may appear more than once in the result collection.
                | Count the student only once for the selected subject.
                |--------------------------------------------------------------------------
                */

                if (
                    isset(
                        $countedStudents[$studentId]
                    )
                ) {
                    continue;
                }

                $key =
                    $subject->key
                    ??
                    'SUBJECT_' .
                    (
                        (int) (
                            $subject->subject_id
                            ?? 0
                        )
                    );

                $mark =
                    $student->subject_marks[
                        $key
                    ] ?? '-';

                $grade =
                    $student->subject_grades[
                        $key
                    ] ?? '-';

                $markText =
                    strtoupper(
                        trim(
                            (string) $mark
                        )
                    );

                $gradeText =
                    strtoupper(
                        trim(
                            (string) $grade
                        )
                    );

                /*
                |--------------------------------------------------------------------------
                | NO MARK
                |--------------------------------------------------------------------------
                |
                | '-' means no mark was entered.
                | Do not count it as FAIL or ABSENT.
                |--------------------------------------------------------------------------
                */

                if (
                    $mark === '-'
                    ||
                    $mark === ''
                    ||
                    $mark === null
                ) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | FROM HERE THE STUDENT IS COUNTED
                | FOR THIS SUBJECT
                |--------------------------------------------------------------------------
                */

                $countedStudents[$studentId] = true;

                /*
                |--------------------------------------------------------------------------
                | ABSENT
                |--------------------------------------------------------------------------
                */

                if (
                    $markText === 'AB'
                    ||
                    $gradeText === 'AB'
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
                    $gradeText === 'F'
                ) {
                    $row['fail']++;

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | NORMAL GRADES
                |--------------------------------------------------------------------------
                */

                if (
                    in_array(
                        $gradeText,
                        [
                            'A1',
                            'A2',
                            'B1',
                            'B2',
                            'C1',
                            'C2',
                            'D',
                        ],
                        true
                    )
                ) {
                    $row[$gradeText]++;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | TOTAL
            |--------------------------------------------------------------------------
            */

            $row['total'] =
                $row['A1']
                +
                $row['A2']
                +
                $row['B1']
                +
                $row['B2']
                +
                $row['C1']
                +
                $row['C2']
                +
                $row['D']
                +
                $row['fail']
                +
                $row['absent'];

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
                (int) $request->exam_master_id
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
            (int) (
                $exam->standard_id
                ?? 0
            );

        if (
            $standardId <= 0 &&
            Schema::hasTable(
                'exam_master_subjects'
            )
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
                (int) $request->academic_year_id,
                (int) $request->exam_master_id,
                $standardId,
                (int) $request->division_id
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
    | EXPORT EXCEL
    |--------------------------------------------------------------------------
    */

    public function exportExcel(
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
                (int) $request->exam_master_id
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
            (int) (
                $exam->standard_id
                ?? 0
            );

        if (
            $standardId <= 0 &&
            Schema::hasTable(
                'exam_master_subjects'
            )
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
                (int) $request->academic_year_id,
                (int) $request->exam_master_id,
                $standardId,
                (int) $request->division_id
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

        $viewData =
            $data['viewData'];

        $results =
            collect(
                $viewData['results']
                ?? []
            );

        $displayColumns =
            collect(
                $viewData['displayColumns']
                ?? []
            );

        $exam =
            $viewData['exam']
            ?? null;

        $standard =
            $viewData['standard']
            ?? null;

        $division =
            $viewData['division']
            ?? null;

        $academicYear =
            $viewData['academicYear']
            ?? null;

        $totalMaxMarks =
            $viewData['totalMaxMarks']
            ?? 0;

        $classTeacher =
            $viewData['classTeacher']
            ?? null;

        $principal =
            $viewData['principal']
            ?? null;

        /*
        |--------------------------------------------------------------------------
        | SORT
        |--------------------------------------------------------------------------
        */

        $results =
            $results
                ->sortBy(
                    function ($student) {
                        $rollNo =
                            trim(
                                (string) (
                                    $student->roll_no
                                    ?? ''
                                )
                            );

                        if (
                            $rollNo !== '' &&
                            is_numeric($rollNo)
                        ) {
                            return [
                                0,
                                (int) $rollNo,
                            ];
                        }

                        return [
                            1,
                            strtoupper($rollNo),
                        ];
                    }
                )
                ->values();

        /*
        |--------------------------------------------------------------------------
        | DISPLAY NAMES
        |--------------------------------------------------------------------------
        */

        $yearName =
            $academicYear->year_name
            ?? $academicYear->name
            ?? 'Year';

        $examName =
            $exam->display_exam_name
            ?? $exam->exam_name
            ?? 'Exam';

        $standardName =
            $standard->standard_name
            ?? 'Standard';

        $divisionName =
            $division->division_name
            ?? 'Division';

        $classTeacherName =
            (
                $classTeacher &&
                $classTeacher->user
            )
                ? $classTeacher->user->name
                : '-';

        $principalName =
            (
                $principal &&
                $principal->user
            )
                ? $principal->user->name
                : '-';

        $fileName =
            'Result_Sheet_'
            . $this->cleanExcelFileName(
                $yearName
            )
            . '_'
            . $this->cleanExcelFileName(
                $examName
            )
            . '_'
            . $this->cleanExcelFileName(
                $standardName
            )
            . '_'
            . $this->cleanExcelFileName(
                $divisionName
            )
            . '.xls';

        /*
        |--------------------------------------------------------------------------
        | COLUMN COUNT
        |--------------------------------------------------------------------------
        */

        $columnCount =
            4
            +
            $displayColumns->count()
            +
            5;

        /*
        |--------------------------------------------------------------------------
        | HTML EXCEL
        |--------------------------------------------------------------------------
        */

        $html = '';

        $html .= '<html>';
        $html .= '<head>';

        $html .= '
            <meta
                http-equiv="Content-Type"
                content="text/html; charset=UTF-8"
            >
        ';

        $html .= '
            <style>

                body {
                    font-family: Arial, sans-serif;
                    font-size: 11px;
                }

                table {
                    border-collapse: collapse;
                    width: 100%;
                }

                th {
                    background: #dbeafe;
                    color: #1e3a8a;
                    border: 1px solid #888888;
                    padding: 6px;
                    text-align: center;
                    font-weight: bold;
                    vertical-align: middle;
                }

                td {
                    border: 1px solid #999999;
                    padding: 5px;
                    vertical-align: middle;
                }

                .title {
                    font-size: 18px;
                    font-weight: bold;
                    text-align: center;
                }

                .subtitle {
                    font-size: 14px;
                    font-weight: bold;
                    text-align: center;
                }

                .center {
                    text-align: center;
                }

                .pass {
                    color: green;
                    font-weight: bold;
                }

                .fail {
                    color: red;
                    font-weight: bold;
                }

                .absent {
                    color: red;
                    font-weight: bold;
                }

            </style>
        ';

        $html .= '</head>';
        $html .= '<body>';

        /*
        |--------------------------------------------------------------------------
        | SCHOOL TITLE
        |--------------------------------------------------------------------------
        */

        $html .= '<table>';

        $html .= '<tr>';

        $html .=
            '<td colspan="' .
            $columnCount .
            '" class="title">';

        $html .= e(
            'PRAJNANABODHINI ENGLISH MEDIUM SCHOOL & JR. COLLEGE'
        );

        $html .= '</td>';

        $html .= '</tr>';

        $html .= '<tr>';

        $html .=
            '<td colspan="' .
            $columnCount .
            '" class="subtitle">';

        $html .= e(
            'SHIRGAON / CHIKHALI'
        );

        $html .= '</td>';

        $html .= '</tr>';

        $html .= '<tr>';

        $html .=
            '<td colspan="' .
            $columnCount .
            '" class="subtitle">';

        $html .= e(
            'RESULT SHEET'
        );

        $html .= '</td>';

        $html .= '</tr>';

        $html .= '</table>';

        $html .= '<br>';

        /*
        |--------------------------------------------------------------------------
        | INFORMATION
        |--------------------------------------------------------------------------
        */

        $html .= '<table>';

        $html .= '<tr>';

        $html .=
            '<td><strong>Academic Year</strong></td>';

        $html .=
            '<td>' .
            e($yearName) .
            '</td>';

        $html .=
            '<td><strong>Exam</strong></td>';

        $html .=
            '<td>' .
            e($examName) .
            '</td>';

        $html .= '</tr>';

        $html .= '<tr>';

        $html .=
            '<td><strong>Standard</strong></td>';

        $html .=
            '<td>' .
            e($standardName) .
            '</td>';

        $html .=
            '<td><strong>Division</strong></td>';

        $html .=
            '<td>' .
            e($divisionName) .
            '</td>';

        $html .= '</tr>';

        $html .= '<tr>';

        $html .=
            '<td><strong>Class Teacher</strong></td>';

        $html .=
            '<td>' .
            e($classTeacherName) .
            '</td>';

        $html .=
            '<td><strong>Principal</strong></td>';

        $html .=
            '<td>' .
            e($principalName) .
            '</td>';

        $html .= '</tr>';

        $html .= '<tr>';

        $html .=
            '<td><strong>Total Maximum Marks</strong></td>';

        $html .=
            '<td>' .
            e(
                $this->formatExcelNumber(
                    $totalMaxMarks
                )
            ) .
            '</td>';

        $html .=
            '<td><strong>Overall Pass %</strong></td>';

        $html .=
            '<td>' .
            e(
                (
                    $viewData['passPercentage']
                    ?? 40
                ) . '%'
            ) .
            '</td>';

        $html .= '</tr>';

        $html .= '</table>';

        $html .= '<br>';

        /*
        |--------------------------------------------------------------------------
        | RESULT TABLE
        |--------------------------------------------------------------------------
        */

        $html .= '<table>';

        $html .= '<thead>';

        $html .= '<tr>';

        $html .= '<th>Sr. No.</th>';
        $html .= '<th>Roll No.</th>';
        $html .= '<th>Student Name</th>';
        $html .= '<th>Gender</th>';

        foreach (
            $displayColumns as $column
        ) {
            $maxMark =
                $this->formatExcelNumber(
                    $column->max_marks
                    ?? 0
                );

            $html .= '<th>';

            $html .= e(
                $column->subject_name
            );

            $html .= '<br>';

            $html .= e(
                '(Max Mark=' .
                $maxMark .
                ')'
            );

            $html .= '</th>';
        }

        $html .= '<th>Total</th>';
        $html .= '<th>Max Total</th>';
        $html .= '<th>Percentage</th>';
        $html .= '<th>Grade</th>';
        $html .= '<th>Result</th>';

        $html .= '</tr>';

        $html .= '</thead>';

        $html .= '<tbody>';

        $srNo = 1;

        foreach (
            $results as $student
        ) {
            $html .= '<tr>';

            $html .=
                '<td class="center">' .
                $srNo++ .
                '</td>';

            $html .=
                '<td class="center">' .
                e(
                    (string) (
                        $student->roll_no
                        ?? ''
                    )
                ) .
                '</td>';

            $html .=
                '<td>' .
                e(
                    $student->full_student_name
                    ?? ''
                ) .
                '</td>';

            $html .=
                '<td class="center">' .
                e(
                    $student->gender
                    ?? ''
                ) .
                '</td>';

            foreach (
                $displayColumns as $column
            ) {
                $mark =
                    $student->subject_marks[
                        $column->key
                    ] ?? '-';

                $markText =
                    strtoupper(
                        trim(
                            (string) $mark
                        )
                    );

                $html .=
                    '<td class="center">';

                if (
                    $markText === 'AB'
                ) {
                    $html .=
                        '<span class="absent">AB</span>';
                } else {
                    $html .=
                        e(
                            (string) $mark
                        );
                }

                $html .= '</td>';
            }

            $html .=
                '<td class="center">' .
                e(
                    (string) (
                        $student->academic_total
                        ?? '-'
                    )
                ) .
                '</td>';

            $html .=
                '<td class="center">' .
                e(
                    $this->formatExcelNumber(
                        $student->academic_max_display
                        ?? $totalMaxMarks
                    )
                ) .
                '</td>';

            $html .=
                '<td class="center">';

            if (
                $student->calculated_percentage
                !== null
            ) {
                $html .=
                    e(
                        (string) (
                            $student->calculated_percentage
                        )
                    )
                    . '%';
            } else {
                $html .= '-';
            }

            $html .= '</td>';

            $html .=
                '<td class="center">' .
                e(
                    (string) (
                        $student->calculated_grade
                        ?? '-'
                    )
                ) .
                '</td>';

            $studentResult =
                strtoupper(
                    trim(
                        (string) (
                            $student->result
                            ?? '-'
                        )
                    )
                );

            $resultClass =
                $studentResult === 'PASS'
                    ? 'pass'
                    : (
                        $studentResult === 'FAIL'
                            ? 'fail'
                            : ''
                    );

            $html .=
                '<td class="center ' .
                $resultClass .
                '">';

            $html .=
                e(
                    $studentResult
                );

            $html .= '</td>';

            $html .= '</tr>';
        }

        if (
            $results->isEmpty()
        ) {
            $html .= '<tr>';

            $html .=
                '<td colspan="' .
                $columnCount .
                '" style="text-align:center;">';

            $html .=
                'No result records found.';

            $html .= '</td>';

            $html .= '</tr>';
        }

        $html .= '</tbody>';

        $html .= '</table>';

        /*
        |--------------------------------------------------------------------------
        | OVERALL ANALYSIS
        |--------------------------------------------------------------------------
        */

        $overallGradeAnalysis =
            $viewData[
                'overallGradeAnalysis'
            ]
            ?? [];

        if (
            !empty(
                $overallGradeAnalysis
            )
        ) {
            $html .= '<br>';

            $html .=
                '<h3>Overall Grade / Result Analysis</h3>';

            $html .= '<table>';

            $html .= '<thead>';

            $html .= '<tr>';

            $html .=
                '<th>Grade / Result</th>';

            $html .= '<th>Range</th>';
            $html .= '<th>Girls</th>';
            $html .= '<th>Boys</th>';
            $html .= '<th>Total</th>';

            $html .= '</tr>';

            $html .= '</thead>';

            $html .= '<tbody>';

            foreach (
                $overallGradeAnalysis
                as $grade => $analysis
            ) {
                $rowStyle =
                    $grade === 'TOTAL'
                        ? ' style="font-weight:bold;"'
                        : '';

                $html .=
                    '<tr' .
                    $rowStyle .
                    '>';

                $html .=
                    '<td class="center">' .
                    e($grade) .
                    '</td>';

                $html .=
                    '<td>' .
                    e(
                        $analysis['range']
                        ?? ''
                    ) .
                    '</td>';

                $html .=
                    '<td class="center">' .
                    e(
                        $analysis['girls']
                        ?? 0
                    ) .
                    '</td>';

                $html .=
                    '<td class="center">' .
                    e(
                        $analysis['boys']
                        ?? 0
                    ) .
                    '</td>';

                $html .=
                    '<td class="center">' .
                    e(
                        $analysis['total']
                        ?? 0
                    ) .
                    '</td>';

                $html .= '</tr>';
            }

            $html .= '</tbody>';

            $html .= '</table>';
        }

        /*
        |--------------------------------------------------------------------------
        | GIRLS SUBJECT ANALYSIS
        |--------------------------------------------------------------------------
        */

        $girlsSubjectAnalysis =
            $viewData[
                'girlsSubjectAnalysis'
            ]
            ?? [];

        if (
            !empty(
                $girlsSubjectAnalysis
            )
        ) {
            $html .= '<br>';

            $html .=
                '<h3>Girls Subject Analysis</h3>';

            $html .= '<table>';

            $html .= '<thead>';

            $html .= '<tr>';

            $html .= '<th>Subject</th>';

            foreach (
                [
                    'A1',
                    'A2',
                    'B1',
                    'B2',
                    'C1',
                    'C2',
                    'D',
                    'Fail',
                    'Absent',
                    'Total',
                ] as $field
            ) {
                $html .=
                    '<th>' .
                    $field .
                    '</th>';
            }

            $html .= '</tr>';

            $html .= '</thead>';

            $html .= '<tbody>';

            foreach (
                $girlsSubjectAnalysis
                as $analysis
            ) {
                $html .= '<tr>';

                $html .=
                    '<td>' .
                    e(
                        $analysis['subject_name']
                        ??
                        $analysis['subject']
                        ??
                        '-'
                    ) .
                    '</td>';

                foreach (
                    [
                        'A1',
                        'A2',
                        'B1',
                        'B2',
                        'C1',
                        'C2',
                        'D',
                        'fail',
                        'absent',
                        'total',
                    ] as $field
                ) {
                    $html .=
                        '<td class="center">' .
                        e(
                            $analysis[$field]
                            ?? 0
                        ) .
                        '</td>';
                }

                $html .= '</tr>';
            }

            $html .= '</tbody>';

            $html .= '</table>';
        }

        /*
        |--------------------------------------------------------------------------
        | BOYS SUBJECT ANALYSIS
        |--------------------------------------------------------------------------
        */

        $boysSubjectAnalysis =
            $viewData[
                'boysSubjectAnalysis'
            ]
            ?? [];

        if (
            !empty(
                $boysSubjectAnalysis
            )
        ) {
            $html .= '<br>';

            $html .=
                '<h3>Boys Subject Analysis</h3>';

            $html .= '<table>';

            $html .= '<thead>';

            $html .= '<tr>';

            $html .= '<th>Subject</th>';

            foreach (
                [
                    'A1',
                    'A2',
                    'B1',
                    'B2',
                    'C1',
                    'C2',
                    'D',
                    'Fail',
                    'Absent',
                    'Total',
                ] as $field
            ) {
                $html .=
                    '<th>' .
                    $field .
                    '</th>';
            }

            $html .= '</tr>';

            $html .= '</thead>';

            $html .= '<tbody>';

            foreach (
                $boysSubjectAnalysis
                as $analysis
            ) {
                $html .= '<tr>';

                $html .=
                    '<td>' .
                    e(
                        $analysis['subject_name']
                        ??
                        $analysis['subject']
                        ??
                        '-'
                    ) .
                    '</td>';

                foreach (
                    [
                        'A1',
                        'A2',
                        'B1',
                        'B2',
                        'C1',
                        'C2',
                        'D',
                        'fail',
                        'absent',
                        'total',
                    ] as $field
                ) {
                    $html .=
                        '<td class="center">' .
                        e(
                            $analysis[$field]
                            ?? 0
                        ) .
                        '</td>';
                }

                $html .= '</tr>';
            }

            $html .= '</tbody>';

            $html .= '</table>';
        }

        $html .= '</body>';
        $html .= '</html>';

        return response(
            $html,
            200,
            [
                'Content-Type' =>
                    'application/vnd.ms-excel; charset=UTF-8',

                'Content-Disposition' =>
                    'attachment; filename="' .
                    $fileName .
                    '"',

                'Cache-Control' =>
                    'max-age=0',

                'Pragma' =>
                    'public',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CLEAN EXCEL FILE NAME
    |--------------------------------------------------------------------------
    */

    private function cleanExcelFileName(
        string $value
    ): string {
        $value =
            preg_replace(
                '/[^A-Za-z0-9_-]+/',
                '_',
                $value
            );

        return trim(
            $value,
            '_'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FORMAT EXCEL NUMBER
    |--------------------------------------------------------------------------
    */

    private function formatExcelNumber(
        $value
    ): string {
        $value =
            (float) $value;

        if (
            floor($value) === $value
        ) {
            return (string) (
                (int) $value
            );
        }

        return number_format(
            $value,
            2,
            '.',
            ''
        );
    }
}