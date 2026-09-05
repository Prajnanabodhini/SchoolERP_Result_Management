<?php

namespace App\Helpers;

use Illuminate\Support\Collection;

class ResultSheetHelper
{
    public static function formatStaffName($name): string
    {
        $name = str_replace('.', ' ', trim((string) $name));
        $name = preg_replace('/\s+/', ' ', $name) ?? '';

        return ucwords(
            strtolower(
                trim($name)
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | NORMALIZE TEXT
    |--------------------------------------------------------------------------
    */

    public static function normalizeText($value): string
    {
        return preg_replace(
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

    public static function normalizeGender($gender): string
    {
        $value =
            strtoupper(
                trim(
                    (string) $gender
                )
            );

        if (
            in_array(
                $value,
                [
                    '1',
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

        if (
            in_array(
                $value,
                [
                    '2',
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

        $value =
            preg_replace(
                '/[^A-Z]/',
                '',
                $value
            ) ?? '';

        if (
            in_array(
                $value,
                [
                    'M',
                    'MALE',
                ],
                true
            )
        ) {
            return 'MALE';
        }

        if (
            in_array(
                $value,
                [
                    'F',
                    'FEMALE',
                ],
                true
            )
        ) {
            return 'FEMALE';
        }

        return 'UNKNOWN';
    }


    /*
    |--------------------------------------------------------------------------
    | PASSING PERCENTAGE
    |--------------------------------------------------------------------------
    */

    public static function getPassingPercentage(
        ?int $standardId = null
    ): int {
        return 35;
    }


    /*
    |--------------------------------------------------------------------------
    | CALCULATE PASSING MARKS
    |--------------------------------------------------------------------------
    */

    public static function calculatePassingMarks(
        $maxMarks,
        int $percentage = 35
    ): int {
        $maxMarks =
            (float) $maxMarks;

        return $maxMarks > 0
            ? (int) ceil(
                (
                    $maxMarks *
                    $percentage
                ) / 100
            )
            : 0;
    }


    /*
    |--------------------------------------------------------------------------
    | GRADE
    |--------------------------------------------------------------------------
    */

    public static function getGradeFromPercentage(
        $percentage
    ): string {
        if (
            $percentage === null ||
            $percentage === ''
        ) {
            return '-';
        }

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
    | SUBJECT KEY
    |--------------------------------------------------------------------------
    */

    public static function getSubjectKey($column): string
    {
        if (
            !empty($column->key)
        ) {
            return (string) $column->key;
        }

        if (
            !empty($column->subject_id)
        ) {
            return 'SUBJECT_' . (int) $column->subject_id;
        }

        if (
            !empty($column->mapping_id)
        ) {
            return 'MAPPING_' . (int) $column->mapping_id;
        }

        if (
            !empty($column->subject_code)
        ) {
            return (string) $column->subject_code;
        }

        return '';
    }


    /*
    |--------------------------------------------------------------------------
    | TO ARRAY
    |--------------------------------------------------------------------------
    */

    public static function toArray($value): array
    {
        if (
            $value instanceof Collection
        ) {
            return $value->toArray();
        }

        if (
            is_object($value)
        ) {
            return (array) $value;
        }

        return is_array($value)
            ? $value
            : [];
    }


    /*
    |--------------------------------------------------------------------------
    | FIND COLLECTION VALUE
    |--------------------------------------------------------------------------
    */

    public static function findCollectionValue(
        $collection,
        $column
    ) {
        $collection =
            self::toArray(
                $collection
            );

        if (
            !$collection
        ) {
            return null;
        }

        $keys = [];

        $subjectKey =
            self::getSubjectKey(
                $column
            );

        if (
            $subjectKey !== ''
        ) {
            $keys[] =
                $subjectKey;
        }

        $subjectId =
            (int) (
                $column->subject_id ?? 0
            );

        if (
            $subjectId > 0
        ) {
            $keys[] =
                'SUBJECT_' .
                $subjectId;

            $keys[] =
                'subject_' .
                $subjectId;

            $keys[] =
                (string) $subjectId;
        }

        $mappingId =
            (int) (
                $column->mapping_id ?? 0
            );

        if (
            $mappingId > 0
        ) {
            $keys[] =
                'MAPPING_' .
                $mappingId;

            $keys[] =
                'mapping_' .
                $mappingId;

            $keys[] =
                (string) $mappingId;
        }

        if (
            !empty(
                $column->subject_code
            )
        ) {
            $keys[] =
                (string)
                $column->subject_code;
        }

        if (
            !empty(
                $column->subject_name
            )
        ) {
            $keys[] =
                (string)
                $column->subject_name;
        }

        foreach (
            array_unique($keys)
            as $key
        ) {

            if (
                array_key_exists(
                    $key,
                    $collection
                )
            ) {
                return $collection[$key];
            }
        }

        foreach (
            $collection
            as $storedKey => $value
        ) {

            foreach (
                $keys
                as $key
            ) {

                if (
                    strcasecmp(
                        trim(
                            (string) $storedKey
                        ),
                        trim(
                            (string) $key
                        )
                    ) === 0
                ) {
                    return $value;
                }
            }
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | STUDENT MARK
    |--------------------------------------------------------------------------
    */

    public static function getStudentMark(
        $student,
        $column
    ) {
        return self::findCollectionValue(
            $student->subject_marks ?? [],
            $column
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STUDENT GRADE
    |--------------------------------------------------------------------------
    */

    public static function getStudentGrade(
        $student,
        $column
    ) {
        return self::findCollectionValue(
            $student->subject_grades ?? [],
            $column
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STUDENT RESULT
    |--------------------------------------------------------------------------
    */

    public static function getStudentResult(
        $student,
        $column
    ) {
        return self::findCollectionValue(
            $student->subject_results ?? [],
            $column
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STUDENT OPTIONAL
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | Actual student_marks row has priority over
    | standard_wise_subjects.is_optional.
    |
    */

    public static function isStudentOptional(
        $student,
        $column
    ): bool {

        /*
        |--------------------------------------------------------------------------
        | ACTUAL STUDENT MARK ROW
        |--------------------------------------------------------------------------
        */

        $mark =
            self::getStudentMark(
                $student,
                $column
            );

        if (
            $mark !== null
        ) {

            /*
            |--------------------------------------------------------------------------
            | Actual mark row contains is_optional
            |--------------------------------------------------------------------------
            */

            if (
                isset(
                    $mark->is_optional
                )
            ) {

                $value =
                    strtoupper(
                        trim(
                            (string)
                            $mark->is_optional
                        )
                    );

                return in_array(
                    $value,
                    [
                        '1',
                        'TRUE',
                        'YES',
                        'Y',
                    ],
                    true
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Actual mark row exists.
            |
            | Do not fall back to subject configuration.
            |--------------------------------------------------------------------------
            */

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | NO ACTUAL STUDENT MARK
        |--------------------------------------------------------------------------
        |
        | Now use subject-level optional configuration.
        |
        */

        $optionalMap =
            self::toArray(
                $student->subject_is_optional
                ?? []
            );

        $value =
            self::findCollectionValue(
                $optionalMap,
                $column
            );

        if (
            $value !== null
        ) {

            $valueText =
                strtoupper(
                    trim(
                        (string)
                        $value
                    )
                );

            return in_array(
                $valueText,
                [
                    '1',
                    'TRUE',
                    'YES',
                    'Y',
                    'OPT',
                ],
                true
            );
        }

        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | FORMAT MARK
    |--------------------------------------------------------------------------
    */

    public static function formatMark(
        $value
    ) {
        if (
            $value === null ||
            $value === ''
        ) {
            return null;
        }

        $number =
            (float) $value;

        return floor($number) === $number
            ? (int) $number
            : round($number, 2);
    }


    /*
    |--------------------------------------------------------------------------
    | DISPLAY NUMBER
    |--------------------------------------------------------------------------
    */

    public static function displayNumber(
        $value
    ): string {
        if (
            $value === null ||
            $value === '' ||
            $value === '-'
        ) {
            return '-';
        }

        if (
            !is_numeric($value)
        ) {
            return (string) $value;
        }

        $number =
            (float) $value;

        return floor($number) === $number
            ? (string) ((int) $number)
            : number_format(
                $number,
                2,
                '.',
                ''
            );
    }


    /*
    |--------------------------------------------------------------------------
    | ABSENT
    |--------------------------------------------------------------------------
    */

    public static function isAbsent(
        $row
    ): bool {
        if (
            !$row
        ) {
            return false;
        }

        foreach (
            [
                'is_absent',
                'status',
                'marks_status',
                'attendance_status',
                'mark_status',
            ]
            as $field
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
                        (string)
                        $row->{$field}
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
                        'NOT PRESENT',
                        'NOTPRESENT',
                    ],
                    true
                )
            ) {
                return true;
            }
        }

        foreach (
            [
                'theory_obtained_marks',
                'oral_obtained_marks',
                'practical_obtained_marks',
            ]
            as $field
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
                        (string)
                        $row->{$field}
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
    | EXTRACT OBTAINED MARKS
    |--------------------------------------------------------------------------
    */

    public static function extractObtainedMarks(
        $row
    ): ?float {
        if (
            !$row
        ) {
            return null;
        }

        foreach (
            [
                'obtained_marks',
                'mark',
                'marks',
            ]
            as $field
        ) {

            if (
                isset(
                    $row->{$field}
                ) &&
                $row->{$field} !== '' &&
                $row->{$field} !== null &&
                is_numeric(
                    $row->{$field}
                )
            ) {

                return (float)
                    $row->{$field};
            }
        }

        $total = 0.0;
        $found = false;

        foreach (
            [
                'theory_obtained_marks',
                'oral_obtained_marks',
                'practical_obtained_marks',
            ]
            as $field
        ) {

            if (
                isset(
                    $row->{$field}
                ) &&
                $row->{$field} !== '' &&
                $row->{$field} !== null &&
                is_numeric(
                    $row->{$field}
                )
            ) {

                $total +=
                    (float)
                    $row->{$field};

                $found = true;
            }
        }

        return $found
            ? $total
            : null;
    }


    /*
    |--------------------------------------------------------------------------
    | EXTRACT MAX MARKS
    |--------------------------------------------------------------------------
    */

    public static function extractMaxMarks(
        $row
    ): float {
        if (
            !$row
        ) {
            return 0.0;
        }

        if (
            isset($row->max_marks) &&
            is_numeric(
                $row->max_marks
            )
        ) {
            return (float)
                $row->max_marks;
        }

        $total = 0.0;

        foreach (
            [
                'theory_max_marks',
                'oral_max_marks',
                'practical_max_marks',
            ]
            as $field
        ) {

            if (
                isset(
                    $row->{$field}
                ) &&
                $row->{$field} !== '' &&
                $row->{$field} !== null &&
                is_numeric(
                    $row->{$field}
                )
            ) {

                $total +=
                    (float)
                    $row->{$field};
            }
        }

        return $total;
    }


    /*
    |--------------------------------------------------------------------------
    | EXTRACT PASSING MARKS
    |--------------------------------------------------------------------------
    */

    public static function extractPassingMarks(
        $row
    ): float {
        if (
            !$row
        ) {
            return 0.0;
        }

        if (
            isset($row->passing_marks) &&
            is_numeric(
                $row->passing_marks
            )
        ) {
            return (float)
                $row->passing_marks;
        }

        $total = 0.0;

        foreach (
            [
                'theory_passing_marks',
                'oral_passing_marks',
                'practical_passing_marks',
            ]
            as $field
        ) {

            if (
                isset(
                    $row->{$field}
                ) &&
                $row->{$field} !== '' &&
                $row->{$field} !== null &&
                is_numeric(
                    $row->{$field}
                )
            ) {

                $total +=
                    (float)
                    $row->{$field};
            }
        }

        return $total;
    }


    /*
    |--------------------------------------------------------------------------
    | EMPTY SUBJECT ANALYSIS
    |--------------------------------------------------------------------------
    */

    public static function emptySubjectAnalysis(): array
    {
        return [
            'A1' => 0,
            'A2' => 0,
            'B1' => 0,
            'B2' => 0,
            'C1' => 0,
            'C2' => 0,
            'D' => 0,
            'fail' => 0,
            'absent' => 0,
            'pending' => 0,
            'total' => 0,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | CLEAN FILE NAME
    |--------------------------------------------------------------------------
    */

    public static function cleanFileName(
        string $value
    ): string {
        $value =
            preg_replace(
                '/[^A-Za-z0-9\_-]+/',
                '_',
                $value
            ) ?? '';

        return trim(
            $value,
            '_'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GROUP MARKS BY STUDENT AND SUBJECT
    |--------------------------------------------------------------------------
    |
    | IMPORTANT PRIORITY:
    |
    | 1. student_marks.subject_id
    | |---- This is the authoritative subject id.
    |
    | 2. teacher_subject_allocations.subject_id
    |
    | 3. legacy standard_wise_subjects.id mapping
    |
    |--------------------------------------------------------------------------
    */

    public static function groupMarksByStudentAndSubject(
        Collection $markRows,
        Collection $columns
    ): Collection {

        /*
        |--------------------------------------------------------------------------
        | RESULT-SHEET SUBJECT IDS
        |--------------------------------------------------------------------------
        */

        $actualSubjectIds =
            $columns
                ->pluck(
                    'subject_id'
                )
                ->map(
                    fn ($id) =>
                        (int) $id
                )
                ->filter(
                    fn ($id) =>
                        $id > 0
                )
                ->flip();


        /*
        |--------------------------------------------------------------------------
        | STANDARD-WISE MAPPING
        |--------------------------------------------------------------------------
        |
        | standard_wise_subjects.id
        |          ->
        | subjects.id
        |
        */

        $mappingToSubject =
            $columns
                ->mapWithKeys(
                    function ($column) {

                        $mappingId =
                            (int) (
                                $column->mapping_id
                                ?? 0
                            );

                        $subjectId =
                            (int) (
                                $column->subject_id
                                ?? 0
                            );

                        if (
                            $mappingId <= 0 ||
                            $subjectId <= 0
                        ) {
                            return [];
                        }

                        return [
                            $mappingId =>
                                $subjectId,
                        ];
                    }
                );


        $map = [];


        /*
        |--------------------------------------------------------------------------
        | PROCESS EVERY MARK ROW
        |--------------------------------------------------------------------------
        */

        foreach (
            $markRows as $row
        ) {

            $studentId =
                (int) (
                    $row->student_id
                    ?? 0
                );

            if (
                $studentId <= 0
            ) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | ACTUAL STUDENT_MARKS SUBJECT ID
            |--------------------------------------------------------------------------
            */

            $storedSubjectId =
                (int) (
                    $row->subject_id
                    ?? 0
                );


            /*
            |--------------------------------------------------------------------------
            | TEACHER ALLOCATION SUBJECT ID
            |--------------------------------------------------------------------------
            */

            $allocationSubjectId =
                (int) (
                    $row->allocation_subject_id
                    ?? 0
                );


            $subjectId = null;


            /*
            |--------------------------------------------------------------------------
            | PRIORITY 1: student_marks.subject_id
            |--------------------------------------------------------------------------
            |
            | Example:
            |
            | student_marks.subject_id = 96
            | subjects.id              = 96
            | INFORMATION TECHNOLOGY
            |
            */

            if (
                $storedSubjectId > 0 &&
                $actualSubjectIds->has(
                    $storedSubjectId
                )
            ) {

                $subjectId =
                    $storedSubjectId;
            }


            /*
            |--------------------------------------------------------------------------
            | PRIORITY 2: ALLOCATION SUBJECT
            |--------------------------------------------------------------------------
            */

            if (
                $subjectId === null &&
                $allocationSubjectId > 0 &&
                $actualSubjectIds->has(
                    $allocationSubjectId
                )
            ) {

                $subjectId =
                    $allocationSubjectId;
            }


            /*
            |--------------------------------------------------------------------------
            | PRIORITY 3: LEGACY MAPPING
            |--------------------------------------------------------------------------
            */

            if (
                $subjectId === null &&
                $storedSubjectId > 0 &&
                $mappingToSubject->has(
                    $storedSubjectId
                )
            ) {

                $subjectId =
                    (int)
                    $mappingToSubject->get(
                        $storedSubjectId
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | NO SUBJECT MATCH
            |--------------------------------------------------------------------------
            */

            if (
                $subjectId === null
            ) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | INITIALIZE STUDENT
            |--------------------------------------------------------------------------
            */

            if (
                !isset(
                    $map[$studentId]
                )
            ) {

                $map[$studentId] = [];
            }


            /*
            |--------------------------------------------------------------------------
            | NEWEST MARK WINS
            |--------------------------------------------------------------------------
            |
            | loadStudentMarks() returns newest first.
            |
            */

            if (
                !isset(
                    $map[
                        $studentId
                    ][$subjectId]
                )
            ) {

                $map[
                    $studentId
                ][$subjectId] =
                    $row;
            }
        }


        return collect(
            $map
        );
    }

    /*
|--------------------------------------------------------------------------
| BUILD STUDENTS
|--------------------------------------------------------------------------
|
| Common result-sheet student builder.
|
| This method is intentionally kept in ResultSheetHelper so that
| ResultSheetController, ResultSheetDataService, print/export logic,
| and any other result-related service can use the exact same logic.
|
*/

public static function buildStudents(
    Collection $erpStudents,
    Collection $marksByStudent,
    Collection $columns,
    int $passPercentage
): Collection {

    $students = collect();


    /*
    |--------------------------------------------------------------------------
    | LOOP ERP STUDENTS
    |--------------------------------------------------------------------------
    */

    foreach (
        $erpStudents as $erp
    ) {

        $studentId =
            (int) (
                $erp->Studentid ?? 0
            );


        if (
            $studentId <= 0
        ) {

            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | STUDENT MARKS
        |--------------------------------------------------------------------------
        */

        $studentMarks =
            (array) (
                $marksByStudent->get(
                    $studentId,
                    []
                )
            );


        /*
        |--------------------------------------------------------------------------
        | INITIAL STUDENT OBJECT
        |--------------------------------------------------------------------------
        */

        $student =
            (object) [

                'student_id' =>
                    $studentId,

                'id' =>
                    null,

                'roll_no' =>
                    trim(
                        (string) (
                            $erp->rollno ?? ''
                        )
                    ),

                'full_student_name' =>
                    trim(
                        (string) (
                            $erp->studname ?? ''
                        )
                    ),

                'father_name' =>
                    trim(
                        (string) (
                            $erp->fathername ?? ''
                        )
                    ),

                'gender' =>
                    self::normalizeGender(
                        $erp->gender ?? ''
                    ),

                'subject_marks' =>
                    [],

                'subject_grades' =>
                    [],

                'subject_results' =>
                    [],

                'subject_is_optional' =>
                    [],

                'subject_max_used' =>
                    [],

                'subject_passing_used' =>
                    [],

                'academic_total' =>
                    0,

                'academic_max_used' =>
                    0,

                'academic_max_display' =>
                    0,

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

                'has_incomplete_marks' =>
                    false,
            ];


        /*
        |--------------------------------------------------------------------------
        | FALLBACK STUDENT NAME
        |--------------------------------------------------------------------------
        */

        if (
            $student->full_student_name === ''
        ) {

            $student->full_student_name =
                'Student ID : ' .
                $studentId;
        }


        /*
        |--------------------------------------------------------------------------
        | TOTAL VARIABLES
        |--------------------------------------------------------------------------
        */

        $studentTotal = 0.0;

        $studentMax = 0.0;

        $hasFail = false;

        $requiredSubjectMissing = false;


        /*
        |--------------------------------------------------------------------------
        | PROCESS SUBJECTS
        |--------------------------------------------------------------------------
        */

        foreach (
            $columns as $column
        ) {

            $key =
                self::getSubjectKey(
                    $column
                );


            /*
            |--------------------------------------------------------------------------
            | SUBJECT ID
            |--------------------------------------------------------------------------
            */

            $subjectId =
                (int) (
                    $column->subject_id ?? 0
                );


            /*
            |--------------------------------------------------------------------------
            | SUBJECT OPTIONAL CONFIGURATION
            |--------------------------------------------------------------------------
            */

            $isColumnOptional =
                (int) (
                    $column->is_optional ?? 0
                ) === 1;


            /*
            |--------------------------------------------------------------------------
            | ACTUAL STUDENT MARK ROW
            |--------------------------------------------------------------------------
            */

            $row =
                $studentMarks[
                    $subjectId
                ] ?? null;


            /*
            |--------------------------------------------------------------------------
            | DEFAULTS
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

            $student->subject_is_optional[
                $key
            ] = 0;

            $student->subject_max_used[
                $key
            ] = 0.0;

            $student->subject_passing_used[
                $key
            ] = 0.0;


            /*
            |--------------------------------------------------------------------------
            | MAXIMUM MARKS
            |--------------------------------------------------------------------------
            */

            $maxMarks =
                (float) (
                    $column->max_marks ?? 0
                );


            /*
            |--------------------------------------------------------------------------
            | PASSING MARKS
            |--------------------------------------------------------------------------
            */

            $passingMarks =
                (float) (
                    $column->passing_marks ?? 0
                );


            /*
            |--------------------------------------------------------------------------
            | ABSENT
            |--------------------------------------------------------------------------
            */

            $isAbsent =
                $row
                ?
                self::isAbsent(
                    $row
                )
                :
                false;


            /*
            |--------------------------------------------------------------------------
            | OBTAINED MARK
            |--------------------------------------------------------------------------
            */

            $obtained =
                $row
                ?
                self::extractObtainedMarks(
                    $row
                )
                :
                null;


            /*
            |--------------------------------------------------------------------------
            | ACTUAL student_marks.is_optional
            |--------------------------------------------------------------------------
            */

            $rowIsOptional = false;


            if (
                $row &&
                isset(
                    $row->is_optional
                )
            ) {

                $optionalValue =
                    strtoupper(
                        trim(
                            (string)
                            $row->is_optional
                        )
                    );


                $rowIsOptional =
                    in_array(
                        $optionalValue,
                        [
                            '1',
                            'TRUE',
                            'YES',
                            'Y',
                        ],
                        true
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | STORED STATUS VALUES
            |--------------------------------------------------------------------------
            */

            $storedResult =
                strtoupper(
                    trim(
                        (string) (
                            $row->subject_result
                            ?? ''
                        )
                    )
                );


            $storedGrade =
                strtoupper(
                    trim(
                        (string) (
                            $row->grade
                            ?? ''
                        )
                    )
                );


            $storedObtained =
                strtoupper(
                    trim(
                        (string) (
                            $row->obtained_marks
                            ?? ''
                        )
                    )
                );


            $rowMark =
                strtoupper(
                    trim(
                        (string) (
                            $row->mark ?? ''
                        )
                    )
                );


            $rowMarks =
                strtoupper(
                    trim(
                        (string) (
                            $row->marks ?? ''
                        )
                    )
                );


            $rowSaysOptional =
                $storedResult === 'OPT'
                ||
                $storedGrade === 'OPT'
                ||
                $storedObtained === 'OPT'
                ||
                $rowMark === 'OPT'
                ||
                $rowMarks === 'OPT';


            /*
            |--------------------------------------------------------------------------
            | OPTIONAL SUBJECT
            |--------------------------------------------------------------------------
            */

            if (
                $isColumnOptional
            ) {

                /*
                |--------------------------------------------------------------------------
                | NO ROW
                |--------------------------------------------------------------------------
                */

                if (
                    !$row
                ) {

                    $student->subject_marks[
                        $key
                    ] = 'OPT';

                    $student->subject_grades[
                        $key
                    ] = 'OPT';

                    $student->subject_results[
                        $key
                    ] = 'OPT';

                    $student->subject_is_optional[
                        $key
                    ] = 1;

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | ACTUAL DATABASE OPTIONAL FLAG
                |--------------------------------------------------------------------------
                |
                | This takes priority over the numeric value.
                |
                | is_optional = 1
                | obtained = 0
                |
                | means:
                |
                | OPT
                |
                */

                if (
                    $rowIsOptional
                ) {

                    $student->subject_marks[
                        $key
                    ] = 'OPT';

                    $student->subject_grades[
                        $key
                    ] = 'OPT';

                    $student->subject_results[
                        $key
                    ] = 'OPT';

                    $student->subject_is_optional[
                        $key
                    ] = 1;

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | STORED OPT WITHOUT ACTUAL OPTIONAL FLAG
                |--------------------------------------------------------------------------
                */

                if (
                    $rowSaysOptional &&
                    $obtained === null &&
                    !$isAbsent
                ) {

                    $student->subject_marks[
                        $key
                    ] = 'OPT';

                    $student->subject_grades[
                        $key
                    ] = 'OPT';

                    $student->subject_results[
                        $key
                    ] = 'OPT';

                    $student->subject_is_optional[
                        $key
                    ] = 1;

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | SELECTED OPTIONAL
                |--------------------------------------------------------------------------
                |
                | is_optional = 0
                |
                | Numeric 0 is a real mark.
                |
                */

                $student->subject_is_optional[
                    $key
                ] = 0;
            }


            /*
            |--------------------------------------------------------------------------
            | ADD MAXIMUM TO STUDENT TOTAL
            |--------------------------------------------------------------------------
            */

            if (
                $maxMarks > 0
            ) {

                $studentMax +=
                    $maxMarks;

                $student->subject_max_used[
                    $key
                ] =
                    $maxMarks;
            }


            /*
            |--------------------------------------------------------------------------
            | PASSING MARK FALLBACK
            |--------------------------------------------------------------------------
            */

            if (
                $passingMarks <= 0 &&
                $maxMarks > 0
            ) {

                $passingMarks =
                    self::calculatePassingMarks(
                        $maxMarks,
                        $passPercentage
                    );
            }


            $student->subject_passing_used[
                $key
            ] =
                $passingMarks;


            /*
            |--------------------------------------------------------------------------
            | MISSING ROW
            |--------------------------------------------------------------------------
            */

            if (
                !$row
            ) {

                $requiredSubjectMissing =
                    true;

                $student->has_incomplete_marks =
                    true;

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | ABSENT
            |--------------------------------------------------------------------------
            */

            if (
                $isAbsent
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

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | MISSING OBTAINED MARK
            |--------------------------------------------------------------------------
            */

            if (
                $obtained === null
            ) {

                $requiredSubjectMissing =
                    true;

                $student->has_incomplete_marks =
                    true;

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | NORMALIZE
            |--------------------------------------------------------------------------
            */

            $obtained =
                max(
                    0.0,
                    $obtained
                );


            if (
                $maxMarks > 0
            ) {

                $obtained =
                    min(
                        $obtained,
                        $maxMarks
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | ACTUAL MARK
            |--------------------------------------------------------------------------
            */

            $student->has_any_mark =
                true;


            $student->subject_marks[
                $key
            ] =
                self::formatMark(
                    $obtained
                );


            /*
            |--------------------------------------------------------------------------
            | ADD TO TOTAL
            |--------------------------------------------------------------------------
            */

            $studentTotal +=
                $obtained;


            /*
            |--------------------------------------------------------------------------
            | SUBJECT PERCENTAGE
            |--------------------------------------------------------------------------
            */

            $subjectPercentage =
                $maxMarks > 0
                ?
                (
                    $obtained /
                    $maxMarks
                ) * 100
                :
                0;


            /*
            |--------------------------------------------------------------------------
            | GRADE
            |--------------------------------------------------------------------------
            */

            $grade =
                self::getGradeFromPercentage(
                    $subjectPercentage
                );


            $student->subject_grades[
                $key
            ] =
                $grade;


            /*
            |--------------------------------------------------------------------------
            | PASS / FAIL
            |--------------------------------------------------------------------------
            */

            if (
                $passingMarks > 0 &&
                $obtained < $passingMarks
            ) {

                $hasFail =
                    true;

                $student->subject_results[
                    $key
                ] =
                    'FAIL';

            } else {

                $student->subject_results[
                    $key
                ] =
                    'PASS';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | FINAL TOTAL
        |--------------------------------------------------------------------------
        */

        $student->academic_total =
            self::formatMark(
                $studentTotal
            );


        $student->academic_max_used =
            $studentMax;


        $student->academic_max_display =
            $studentMax;


        /*
        |--------------------------------------------------------------------------
        | FINAL RESULT
        |--------------------------------------------------------------------------
        */

        if (
            $requiredSubjectMissing ||
            !$student->has_any_mark
        ) {

            $student->calculated_percentage =
                null;

            $student->calculated_grade =
                '-';

            $student->result =
                'PENDING';

        } else {

            $student->calculated_percentage =
                $studentMax > 0
                ?
                round(
                    (
                        $studentTotal /
                        $studentMax
                    ) * 100,
                    2
                )
                :
                null;


            $student->calculated_grade =
                $student->calculated_percentage !== null
                ?
                self::getGradeFromPercentage(
                    $student->calculated_percentage
                )
                :
                '-';


            if (
                $student->has_absent ||
                $hasFail
            ) {

                $student->result =
                    'FAIL';

            } elseif (
                $student->calculated_percentage !== null &&
                $student->calculated_percentage >=
                $passPercentage
            ) {

                $student->result =
                    'PASS';

            } else {

                $student->result =
                    'FAIL';
            }
        }


        $students->push(
            $student
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UNIQUE STUDENTS
    |--------------------------------------------------------------------------
    */

    return $students
        ->unique(
            'student_id'
        )
        ->sortBy(
            function ($student) {

                $roll =
                    trim(
                        (string) (
                            $student->roll_no
                            ?? ''
                        )
                    );


                if (
                    $roll !== '' &&
                    is_numeric($roll)
                ) {

                    return [
                        0,
                        (int) $roll,
                    ];
                }


                return [
                    1,
                    strtoupper($roll),
                ];
            }
        )
        ->values();
}
}