<?php

namespace App\Helpers;

use Illuminate\Support\Collection;

class ResultSheetBladeHelper
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

    public static function extractStaffName($record): string
    {
        if (!$record) {
            return '';
        }

        $possibleNames = [
            $record->user?->name ?? null,
            $record->teacher?->name ?? null,
            $record->staff?->name ?? null,
            $record->employee?->name ?? null,
            $record->name ?? null,
            $record->full_name ?? null,
            $record->teacher_name ?? null,
            $record->staff_name ?? null,
            $record->employee_name ?? null,
        ];

        foreach ($possibleNames as $possibleName) {
            $possibleName = trim((string) $possibleName);

            if ($possibleName !== '') {
                return $possibleName;
            }
        }

        return '';
    }

    public static function normalizeSubjectName($column): string
    {
        return strtoupper(
            trim(
                preg_replace(
                    '/\s+/',
                    ' ',
                    (string) (
                        $column->subject_name
                        ?? $column->subject_code
                        ?? ''
                    )
                )
            )
        );
    }

    public static function getSubjectKey($column): string
    {
        if (
            isset($column->key)
            && trim((string) $column->key) !== ''
        ) {
            return (string) $column->key;
        }

        if (
            isset($column->subject_id)
            && $column->subject_id !== ''
        ) {
            return 'SUBJECT_' . (int) $column->subject_id;
        }

        if (
            isset($column->mapping_id)
            && $column->mapping_id !== ''
        ) {
            return 'MAPPING_' . (int) $column->mapping_id;
        }

        if (!empty($column->subject_code)) {
            return (string) $column->subject_code;
        }

        if (!empty($column->subject_name)) {
            return (string) $column->subject_name;
        }

        return '';
    }

    public static function toArray($value): array
    {
        if ($value instanceof Collection) {
            return $value->toArray();
        }

        if (is_object($value)) {
            return (array) $value;
        }

        return is_array($value)
            ? $value
            : [];
    }

    public static function getCollectionValue(
        $collection,
        $column
    ) {
        $collection = self::toArray($collection);

        if (!$collection) {
            return null;
        }

        $keys = [];

        $subjectKey = self::getSubjectKey($column);

        if ($subjectKey !== '') {
            $keys[] = $subjectKey;
        }

        $subjectId = (int) (
            $column->subject_id ?? 0
        );

        if ($subjectId > 0) {
            $keys[] = 'SUBJECT_' . $subjectId;
            $keys[] = 'subject_' . $subjectId;
            $keys[] = (string) $subjectId;
        }

        $mappingId = (int) (
            $column->mapping_id ?? 0
        );

        if ($mappingId > 0) {
            $keys[] = 'MAPPING_' . $mappingId;
            $keys[] = 'mapping_' . $mappingId;
            $keys[] = (string) $mappingId;
        }

        if (!empty($column->subject_code)) {
            $keys[] = (string) $column->subject_code;
        }

        if (!empty($column->subject_name)) {
            $keys[] = (string) $column->subject_name;
        }

        /*
        |--------------------------------------------------------------------------
        | INFORMATION TECHNOLOGY ALIASES
        |--------------------------------------------------------------------------
        */

        $subjectName = self::normalizeSubjectName($column);

        if (
            $subjectName === 'INFORMATION TECHNOLOGY'
            || $subjectName === 'IT'
        ) {
            $keys[] = 'IT';
            $keys[] = 'it';
            $keys[] = 'INFORMATION TECHNOLOGY';
            $keys[] = 'information technology';
            $keys[] = 'INFORMATION_TECHNOLOGY';
            $keys[] = 'information_technology';
            $keys[] = 'INFORMATIONTECHNOLOGY';
            $keys[] = 'informationtechnology';
        }

        /*
        |--------------------------------------------------------------------------
        | REMOVE DUPLICATES
        |--------------------------------------------------------------------------
        */

        $keys = array_unique($keys);

        /*
        |--------------------------------------------------------------------------
        | DIRECT KEY MATCH
        |--------------------------------------------------------------------------
        */

        foreach ($keys as $possibleKey) {
            if (
                array_key_exists(
                    $possibleKey,
                    $collection
                )
            ) {
                return $collection[$possibleKey];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | CASE-INSENSITIVE MATCH
        |--------------------------------------------------------------------------
        */

        foreach (
            $collection as $storedKey => $storedValue
        ) {

            foreach (
                $keys as $possibleKey
            ) {

                if (
                    strcasecmp(
                        trim((string) $storedKey),
                        trim((string) $possibleKey)
                    ) === 0
                ) {
                    return $storedValue;
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | NORMALIZED KEY MATCH
        |--------------------------------------------------------------------------
        */

        foreach (
            $collection as $storedKey => $storedValue
        ) {

            $storedNormalized =
                strtoupper(
                    preg_replace(
                        '/[^A-Z0-9]/',
                        '',
                        (string) $storedKey
                    )
                );

            foreach (
                $keys as $possibleKey
            ) {

                $possibleNormalized =
                    strtoupper(
                        preg_replace(
                            '/[^A-Z0-9]/',
                            '',
                            (string) $possibleKey
                        )
                    );

                if (
                    $storedNormalized !== ''
                    && $storedNormalized === $possibleNormalized
                ) {
                    return $storedValue;
                }
            }
        }

        return null;
    }

    public static function getStudentMark(
        $student,
        $column
    ) {
        return self::getCollectionValue(
            $student->subject_marks ?? [],
            $column
        );
    }

    public static function getStudentGrade(
        $student,
        $column
    ) {
        /*
        |--------------------------------------------------------------------------
        | STORED GRADE
        |--------------------------------------------------------------------------
        */

        $grade = self::getCollectionValue(
            $student->subject_grades ?? [],
            $column
        );

        /*
        |--------------------------------------------------------------------------
        | OPTIONAL SUBJECT
        |--------------------------------------------------------------------------
        |
        | An optional subject must display:
        |
        | MARK  = OPT
        | GRADE = OPT
        |
        */

        if (
            self::getStudentOptionalFlag(
                $student,
                $column
            )
        ) {
            return 'OPT';
        }

        /*
        |--------------------------------------------------------------------------
        | MARK = OPT FALLBACK
        |--------------------------------------------------------------------------
        */

        $mark = self::getStudentMark(
            $student,
            $column
        );

        if (
            strtoupper(
                trim(
                    (string) ($mark ?? '')
                )
            ) === 'OPT'
        ) {
            return 'OPT';
        }

        /*
        |--------------------------------------------------------------------------
        | NORMAL GRADE
        |--------------------------------------------------------------------------
        */

        if (
            $grade === null
            || $grade === ''
        ) {
            return '-';
        }

        return strtoupper(
            trim(
                (string) $grade
            )
        );
    }

    public static function getStudentResult(
        $student,
        $column
    ) {
        return self::getCollectionValue(
            $student->subject_results ?? [],
            $column
        );
    }

    public static function getStudentOptionalFlag(
        $student,
        $column
    ): bool {

        /*
        |--------------------------------------------------------------------------
        | FIRST: STUDENT OPTIONAL MAP
        |--------------------------------------------------------------------------
        */

        $optionalValue =
            self::getCollectionValue(
                $student->subject_is_optional ?? [],
                $column
            );

        if (
            $optionalValue !== null
        ) {

            $value =
                strtoupper(
                    trim(
                        (string) $optionalValue
                    )
                );

            return in_array(
                $value,
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

        /*
        |--------------------------------------------------------------------------
        | SECOND: MARK = OPT
        |--------------------------------------------------------------------------
        */

        $mark =
            self::getStudentMark(
                $student,
                $column
            );

        return strtoupper(
            trim(
                (string) $mark
            )
        ) === 'OPT';
    }

    public static function isSeniorOptionalStandard(
        int $standardId
    ): bool {

        return in_array(
            $standardId,
            [
                19,
                20,
                21,
                22,
            ],
            true
        );
    }

    public static function isSeniorOptionalColumn(
        $column,
        int $standardId
    ): bool {

        if (
            !self::isSeniorOptionalStandard(
                $standardId
            )
        ) {
            return false;
        }

        return in_array(
            self::normalizeSubjectName(
                $column
            ),
            [
                'MARATHI',
                'MATHEMATICS',
                'BIOLOGY',
                'INFORMATION TECHNOLOGY',
                'IT',

                /*
                |--------------------------------------------------------------------------
                | IMPORTANT FIX
                |--------------------------------------------------------------------------
                | Geography is an optional subject for the senior
                | standards used by this result sheet.
                */

                'GEOGRAPHY',

                'GEOLOGY',
            ],
            true
        );
    }

    public static function isSeniorCompulsoryColumn(
        $column,
        int $standardId
    ): bool {

        if (
            !self::isSeniorOptionalStandard(
                $standardId
            )
        ) {
            return false;
        }

        return in_array(
            self::normalizeSubjectName(
                $column
            ),
            [
                'ENGLISH',
                'PHYSICS',
                'CHEMISTRY',
            ],
            true
        );
    }

    public static function getGradeFromPercentage(
        $percentage
    ): string {

        if (
            $percentage === null
            || $percentage === ''
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

        if ($percentage >= 21) {
            return 'E1';
        }

        if ($percentage >= 1) {
            return 'E2';
        }

        return 'F';
    }

    public static function displayNumber(
        $value
    ): string {

        if (
            $value === null
            || $value === ''
            || $value === '-'
        ) {
            return '-';
        }

        if (!is_numeric($value)) {
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

    public static function formatMark(
        $value
    ) {

        if (
            $value === null
            || $value === ''
        ) {
            return null;
        }

        $number =
            (float) $value;

        return floor($number) === $number
            ? (int) $number
            : round($number, 2);
    }

    public static function getStudentKey(
        $student
    ) {

        $studentKey =
            (int) (
                $student->student_id ?? 0
            );

        if (
            $studentKey > 0
        ) {
            return $studentKey;
        }

        return 'ROW_'
            . ($student->roll_no ?? '')
            . '_'
            . md5(
                (string) (
                    $student->full_student_name
                    ?? ''
                )
            );
    }

    public static function getUniqueSortedStudents(
        Collection $results
    ): Collection {

        $uniqueStudents = collect();

        $seenStudents = [];

        foreach (
            $results as $student
        ) {

            $studentId =
                (int) (
                    $student->student_id ?? 0
                );

            $rollNo =
                trim(
                    (string) (
                        $student->roll_no ?? ''
                    )
                );

            $studentName =
                trim(
                    (string) (
                        $student->full_student_name
                        ?? $student->student_name
                        ?? $student->full_name
                        ?? ''
                    )
                );

            $fatherName =
                trim(
                    (string) (
                        $student->father_name
                        ?? $student->father_full_name
                        ?? $student->father
                        ?? ''
                    )
                );

            $displayStudentName =
                $fatherName !== ''
                    ? trim(
                        $studentName
                        . ' '
                        . $fatherName
                    )
                    : $studentName;

            if (
                $studentId > 0
            ) {

                $uniqueKey =
                    'ID:' . $studentId;

            } elseif (
                $rollNo !== ''
            ) {

                $uniqueKey =
                    'ROLL:' .
                    strtoupper($rollNo);

            } elseif (
                $studentName !== ''
            ) {

                $uniqueKey =
                    'NAME:' .
                    strtoupper(
                        trim(
                            preg_replace(
                                '/\s+/',
                                ' ',
                                $studentName
                            )
                            ?? $studentName
                        )
                    );

            } else {

                continue;
            }

            if (
                isset(
                    $seenStudents[$uniqueKey]
                )
            ) {
                continue;
            }

            $seenStudents[$uniqueKey] = true;

            $student->full_student_name =
                $displayStudentName;

            $uniqueStudents->push(
                $student
            );
        }

        return $uniqueStudents
            ->sortBy(
                function ($student) {

                    $rollNo =
                        trim(
                            (string) (
                                $student->roll_no ?? ''
                            )
                        );

                    if (
                        $rollNo !== ''
                        && is_numeric($rollNo)
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
    }

    public static function calculateResultSheetStudent(
        $student,
        Collection $columns,
        int $standardId,
        int $passingPercentage
    ): array {

        $totalMarks = 0.0;
        $totalMaxMarks = 0.0;

        $activeSubjects = 0;
        $optionalSubjects = 0;
        $absentSubjects = 0;
        $failedSubjects = 0;

        $selectedOptionalCount = 0;
        $selectedOptionalKeys = [];

        $subjectDetails = [];

        $isSeniorOptionalStandard =
            self::isSeniorOptionalStandard(
                $standardId
            );

        /*
        |--------------------------------------------------------------------------
        | DETERMINE SELECTED SENIOR OPTIONAL SUBJECTS
        |--------------------------------------------------------------------------
        */

        if (
            $isSeniorOptionalStandard
        ) {

            foreach (
                $columns as $column
            ) {

                if (
                    !self::isSeniorOptionalColumn(
                        $column,
                        $standardId
                    )
                ) {
                    continue;
                }

                $mark =
                    self::getStudentMark(
                        $student,
                        $column
                    );

                $markText =
                    strtoupper(
                        trim(
                            (string) (
                                $mark ?? ''
                            )
                        )
                    );

                $hasActualMark =
                    (
                        $mark !== null
                        && $mark !== ''
                        && is_numeric($mark)
                    )
                    ||
                    $markText === 'AB';

                if (
                    !$hasActualMark
                ) {
                    continue;
                }

                $subjectKey =
                    self::getSubjectKey(
                        $column
                    );

                if (
                    $subjectKey === ''
                ) {
                    $subjectKey =
                        self::normalizeSubjectName(
                            $column
                        );
                }

                $selectedOptionalKeys[
                    $subjectKey
                ] = true;

                $selectedOptionalCount++;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | PROCESS ALL SUBJECT COLUMNS
        |--------------------------------------------------------------------------
        */

        foreach (
            $columns as $column
        ) {

            $maxMarks =
                (float) (
                    $column->max_marks ?? 0
                );

            $mark =
                self::getStudentMark(
                    $student,
                    $column
                );

            $markText =
                strtoupper(
                    trim(
                        (string) (
                            $mark ?? ''
                        )
                    )
                );

            $subjectIsSeniorOptional =
                $isSeniorOptionalStandard
                &&
                self::isSeniorOptionalColumn(
                    $column,
                    $standardId
                );

            $subjectKey =
                self::getSubjectKey(
                    $column
                );

            if (
                $subjectKey === ''
            ) {
                $subjectKey =
                    self::normalizeSubjectName(
                        $column
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | SENIOR OPTIONAL SUBJECT
            |--------------------------------------------------------------------------
            */

            if (
                $subjectIsSeniorOptional
            ) {

                $isSelectedOptional =
                    isset(
                        $selectedOptionalKeys[
                            $subjectKey
                        ]
                    );

                if (
                    !$isSelectedOptional
                ) {

                    $optionalSubjects++;

                    $subjectDetails[] = [
                        'column' =>
                            $column,

                        'mark' =>
                            'OPT',

                        'grade' =>
                            'OPT',

                        'optional' =>
                            true,

                        'selected_optional' =>
                            false,

                        'absent' =>
                            false,

                        'failed' =>
                            false,
                    ];

                    continue;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | NON-SENIOR OPTIONAL SUBJECT
            |--------------------------------------------------------------------------
            */

            if (
                !$isSeniorOptionalStandard
            ) {

                $isOptional =
                    self::getStudentOptionalFlag(
                        $student,
                        $column
                    );

                if (
                    $isOptional
                ) {

                    $optionalSubjects++;

                    $subjectDetails[] = [
                        'column' =>
                            $column,

                        'mark' =>
                            'OPT',

                        'grade' =>
                            'OPT',

                        'optional' =>
                            true,

                        'selected_optional' =>
                            false,

                        'absent' =>
                            false,

                        'failed' =>
                            false,
                    ];

                    continue;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | ABSENT
            |--------------------------------------------------------------------------
            */

            if (
                $markText === 'AB'
                || $markText === 'ABSENT'
            ) {

                $activeSubjects++;

                $totalMaxMarks +=
                    $maxMarks;

                $absentSubjects++;

                $subjectDetails[] = [
                    'column' =>
                        $column,

                    'mark' =>
                        'AB',

                    'grade' =>
                        'AB',

                    'optional' =>
                        false,

                    'selected_optional' =>
                        $subjectIsSeniorOptional,

                    'absent' =>
                        true,

                    'failed' =>
                        true,
                ];

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | EMPTY MARK
            |--------------------------------------------------------------------------
            */

            if (
                $mark === null
                || $mark === ''
            ) {

                $activeSubjects++;

                $totalMaxMarks +=
                    $maxMarks;

                $subjectDetails[] = [
                    'column' =>
                        $column,

                    'mark' =>
                        '-',

                    'grade' =>
                        '-',

                    'optional' =>
                        false,

                    'selected_optional' =>
                        $subjectIsSeniorOptional,

                    'absent' =>
                        false,

                    'failed' =>
                        false,
                ];

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | NUMERIC MARK
            |--------------------------------------------------------------------------
            */

            if (
                is_numeric($mark)
            ) {

                $numericMark =
                    (float) $mark;

                $activeSubjects++;

                $totalMarks +=
                    $numericMark;

                $totalMaxMarks +=
                    $maxMarks;

                $subjectPassing =
                    $maxMarks > 0
                        ? (int) ceil(
                            (
                                $maxMarks
                                *
                                $passingPercentage
                            ) / 100
                        )
                        : 0;

                $failed =
                    $numericMark
                    <
                    $subjectPassing;

                if (
                    $failed
                ) {
                    $failedSubjects++;
                }

                $subjectPercentage =
                    $maxMarks > 0
                        ? (
                            $numericMark
                            /
                            $maxMarks
                        ) * 100
                        : 0;

                $subjectGrade =
                    self::getGradeFromPercentage(
                        $subjectPercentage
                    );

                $subjectDetails[] = [
                    'column' =>
                        $column,

                    'mark' =>
                        $numericMark,

                    'grade' =>
                        $subjectGrade,

                    'optional' =>
                        false,

                    'selected_optional' =>
                        $subjectIsSeniorOptional,

                    'absent' =>
                        false,

                    'failed' =>
                        $failed,
                ];

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | OTHER STATUS
            |--------------------------------------------------------------------------
            */

            $activeSubjects++;

            $totalMaxMarks +=
                $maxMarks;

            /*
            |--------------------------------------------------------------------------
            | IMPORTANT:
            | If an unexpected OPT reaches here, force the grade to OPT.
            |--------------------------------------------------------------------------
            */

            $displayGrade =
                strtoupper(
                    trim(
                        (string) $mark
                    )
                ) === 'OPT'
                    ? 'OPT'
                    : '-';

            $subjectDetails[] = [
                'column' =>
                    $column,

                'mark' =>
                    $mark,

                'grade' =>
                    $displayGrade,

                'optional' =>
                    $displayGrade === 'OPT',

                'selected_optional' =>
                    $subjectIsSeniorOptional,

                'absent' =>
                    false,

                'failed' =>
                    false,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | NO MAXIMUM MARKS
        |--------------------------------------------------------------------------
        */

        if (
            $totalMaxMarks <= 0
        ) {

            return [
                'total_marks' =>
                    0,

                'total_max_marks' =>
                    0,

                'percentage' =>
                    null,

                'grade' =>
                    '-',

                'result' =>
                    'PENDING',

                'active_subjects' =>
                    0,

                'optional_subjects' =>
                    $optionalSubjects,

                'selected_optional_count' =>
                    $selectedOptionalCount,

                'absent_subjects' =>
                    $absentSubjects,

                'failed_subjects' =>
                    $failedSubjects,

                'subject_details' =>
                    $subjectDetails,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | OVERALL PERCENTAGE
        |--------------------------------------------------------------------------
        */

        $percentage =
            (
                $totalMarks
                /
                $totalMaxMarks
            ) * 100;

        /*
        |--------------------------------------------------------------------------
        | OVERALL GRADE
        |--------------------------------------------------------------------------
        */

        $overallGrade =
            self::getGradeFromPercentage(
                $percentage
            );

        /*
        |--------------------------------------------------------------------------
        | OVERALL RESULT
        |--------------------------------------------------------------------------
        */

        $result =
            $percentage >= $passingPercentage
                ? 'PASS'
                : 'FAIL';

        return [
            'total_marks' =>
                $totalMarks,

            'total_max_marks' =>
                $totalMaxMarks,

            'percentage' =>
                $percentage,

            'grade' =>
                $overallGrade,

            'result' =>
                $result,

            'active_subjects' =>
                $activeSubjects,

            'optional_subjects' =>
                $optionalSubjects,

            'selected_optional_count' =>
                $selectedOptionalCount,

            'absent_subjects' =>
                $absentSubjects,

            'failed_subjects' =>
                $failedSubjects,

            'subject_details' =>
                $subjectDetails,
        ];
    }

    public static function buildStudentCalculations(
        Collection $students,
        Collection $columns,
        int $standardId,
        int $passingPercentage
    ): array {

        $calculations = [];

        foreach (
            $students as $student
        ) {

            $studentKey =
                self::getStudentKey(
                    $student
                );

            $calculations[
                $studentKey
            ] =
                self::calculateResultSheetStudent(
                    $student,
                    $columns,
                    $standardId,
                    $passingPercentage
                );
        }

        return $calculations;
    }

    public static function calculateDisplayTotalMaxMarks(
        Collection $columns,
        int $standardId
    ): int {

        if (
            self::isSeniorOptionalStandard(
                $standardId
            )
        ) {

            $compulsoryColumns =
                $columns
                    ->filter(
                        fn ($column) =>
                            self::isSeniorCompulsoryColumn(
                                $column,
                                $standardId
                            )
                    )
                    ->values();

            $optionalColumns =
                $columns
                    ->filter(
                        fn ($column) =>
                            self::isSeniorOptionalColumn(
                                $column,
                                $standardId
                            )
                    )
                    ->values();

            $total = 0.0;

            /*
            |--------------------------------------------------------------------------
            | THREE COMPULSORY SUBJECTS
            |--------------------------------------------------------------------------
            */

            foreach (
                $compulsoryColumns->take(3)
                as $column
            ) {

                $total +=
                    (float) (
                        $column->max_marks ?? 0
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | THREE OPTIONAL SUBJECTS
            |--------------------------------------------------------------------------
            */

            foreach (
                $optionalColumns->take(3)
                as $column
            ) {

                $total +=
                    (float) (
                        $column->max_marks ?? 0
                    );
            }

            return (int) round(
                $total
            );
        }

        /*
        |--------------------------------------------------------------------------
        | NORMAL STANDARDS
        |--------------------------------------------------------------------------
        */

        return (int) round(
            $columns
                ->filter(
                    fn ($column) =>
                        (int) (
                            $column->is_optional ?? 0
                        ) !== 1
                )
                ->sum(
                    fn ($column) =>
                        (float) (
                            $column->max_marks ?? 0
                        )
                )
        );
    }

    public static function calculateSubjectAnalysis(
        Collection $students,
        Collection $columns,
        array $studentCalculations,
        bool $includeFail = true
    ): array {

        $subjectAnalysis = [];

        /*
        |--------------------------------------------------------------------------
        | INITIALIZE SUBJECT ANALYSIS
        |--------------------------------------------------------------------------
        */

        foreach (
            $columns as $column
        ) {

            $subjectCode =
                trim(
                    (string) (
                        $column->subject_code
                        ?? $column->subject_id
                        ?? $column->subject_name
                        ?? ''
                    )
                );

            $subjectAnalysis[
                $subjectCode
            ] = [

                'subject_name' =>
                    $column->subject_name
                    ?? $subjectCode,

                'A1_girls' =>
                    0,

                'A1_boys' =>
                    0,

                'A2_girls' =>
                    0,

                'A2_boys' =>
                    0,

                'B1_girls' =>
                    0,

                'B1_boys' =>
                    0,

                'B2_girls' =>
                    0,

                'B2_boys' =>
                    0,

                'C1_girls' =>
                    0,

                'C1_boys' =>
                    0,

                'C2_girls' =>
                    0,

                'C2_boys' =>
                    0,

                'D_girls' =>
                    0,

                'D_boys' =>
                    0,

                'E1_girls' =>
                    0,

                'E1_boys' =>
                    0,

                'E2_girls' =>
                    0,

                'E2_boys' =>
                    0,

                'F_girls' =>
                    0,

                'F_boys' =>
                    0,

                'absent_girls' =>
                    0,

                'absent_boys' =>
                    0,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | PROCESS STUDENTS
        |--------------------------------------------------------------------------
        */

        foreach (
            $students as $student
        ) {

            $studentKey =
                self::getStudentKey(
                    $student
                );

            $calc =
                $studentCalculations[
                    $studentKey
                ] ?? null;

            if (
                !$calc
            ) {
                continue;
            }

            $gender =
                strtoupper(
                    trim(
                        (string) (
                            $student->gender
                            ?? $student->sex
                            ?? ''
                        )
                    )
                );

            $genderSuffix =
                in_array(
                    $gender,
                    [
                        'F',
                        'FEMALE',
                        'GIRL',
                        'GIRLS',
                    ],
                    true
                )
                    ? 'girls'
                    : 'boys';

            foreach (
                $calc['subject_details']
                as $detail
            ) {

                $column =
                    $detail['column'];

                $subjectCode =
                    trim(
                        (string) (
                            $column->subject_code
                            ?? $column->subject_id
                            ?? $column->subject_name
                            ?? ''
                        )
                    );

                if (
                    !isset(
                        $subjectAnalysis[
                            $subjectCode
                        ]
                    )
                ) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | IGNORE UNSELECTED OPTIONAL SUBJECT
                |--------------------------------------------------------------------------
                */

                if (
                    !empty(
                        $detail['optional']
                    )
                    &&
                    empty(
                        $detail['selected_optional']
                    )
                ) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | ABSENT
                |--------------------------------------------------------------------------
                */

                if (
                    !empty(
                        $detail['absent']
                    )
                ) {

                    $subjectAnalysis[
                        $subjectCode
                    ][
                        'absent_' .
                        $genderSuffix
                    ]++;

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | FAIL
                |--------------------------------------------------------------------------
                */

                if (
                    !empty(
                        $detail['failed']
                    )
                ) {

                    if (
                        $includeFail
                    ) {

                        $subjectAnalysis[
                            $subjectCode
                        ][
                            'F_' .
                            $genderSuffix
                        ]++;
                    }

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | GRADE
                |--------------------------------------------------------------------------
                */

                $grade =
                    strtoupper(
                        trim(
                            (string) (
                                $detail['grade']
                                ?? ''
                            )
                        )
                    );

                if (
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
                            'E1',
                            'E2',
                        ],
                        true
                    )
                ) {

                    $subjectAnalysis[
                        $subjectCode
                    ][
                        $grade
                        . '_'
                        . $genderSuffix
                    ]++;
                }
            }
        }

        return $subjectAnalysis;
    }

    public static function subjectCode(
        $column
    ): string {

        return trim(
            (string) (
                $column->subject_code
                ?? $column->subject_id
                ?? $column->subject_name
                ?? ''
            )
        );
    }

    public static function isFemale(
        $student
    ): bool {

        return in_array(
            strtoupper(
                trim(
                    (string) (
                        $student->gender
                        ?? $student->sex
                        ?? ''
                    )
                )
            ),
            [
                'F',
                'FEMALE',
                'GIRL',
                'GIRLS',
            ],
            true
        );
    }

    public static function getOverallGradeAnalysis(
        Collection $students,
        array $calculations
    ): array {

        $ranges = [
            'A1' => '91-100%',
            'A2' => '81-90%',
            'B1' => '71-80%',
            'B2' => '61-70%',
            'C1' => '51-60%',
            'C2' => '41-50%',
            'D'  => '33-40%',
            'E1' => '21-32%',
            'E2' => '1-20%',
        ];

        $analysis = [];

        foreach (
            $ranges as $grade => $range
        ) {

            $analysis[
                $grade
            ] = [

                'range' =>
                    $range,

                'girls' =>
                    0,

                'boys' =>
                    0,

                'total' =>
                    0,
            ];
        }

        $analysis['TOTAL'] = [

            'range' =>
                'TOTAL',

            'girls' =>
                0,

            'boys' =>
                0,

            'total' =>
                $students->count(),
        ];

        foreach (
            $students as $student
        ) {

            $calc =
                $calculations[
                    self::getStudentKey(
                        $student
                    )
                ] ?? null;

            if (
                !$calc
            ) {
                continue;
            }

            $gender =
                self::isFemale(
                    $student
                )
                    ? 'girls'
                    : 'boys';

            $grade =
                strtoupper(
                    trim(
                        (string) (
                            $calc['grade']
                        )
                    )
                );

            if (
                isset(
                    $analysis[$grade]
                )
            ) {

                $analysis[
                    $grade
                ][$gender]++;

                $analysis[
                    $grade
                ]['total']++;
            }

            $analysis[
                'TOTAL'
            ][$gender]++;
        }

        return $analysis;
    }

    public static function preparePrintData(
        array $data
    ): array {

        $columns =
            collect(
                $data['displayColumns']
                ?? []
            )->values();

        $results =
            collect(
                $data['results']
                ?? []
            );

        $standardId =
            (int) (
                $data['standard']->id
                ?? 0
            );

        $passingPercentage =
            isset(
                $data['passPercentage']
            )
                ? (float) $data['passPercentage']
                : (float)
                    MarksHelper::getPassingPercentage(
                        $standardId
                    );

        $students =
            self::getUniqueSortedStudents(
                $results
            );

        $calculations =
            self::buildStudentCalculations(
                $students,
                $columns,
                $standardId,
                (int) $passingPercentage
            );

        $rawClassTeacher =
            self::extractStaffName(
                $data['classTeacher']
                ?? null
            );

        if (
            $rawClassTeacher === ''
            && isset(
                $data['classTeacherName']
            )
        ) {
            $rawClassTeacher =
                trim(
                    (string) (
                        $data['classTeacherName']
                    )
                );
        }

        $rawPrincipal =
            self::extractStaffName(
                $data['principal']
                ?? null
            );

        if (
            $rawPrincipal === ''
            && isset(
                $data['principalName']
            )
        ) {
            $rawPrincipal =
                trim(
                    (string) (
                        $data['principalName']
                    )
                );
        }

        return [

            'columns' =>
                $columns,

            'students' =>
                $students,

            'standardId' =>
                $standardId,

            'isSeniorOptionalStandard' =>
                self::isSeniorOptionalStandard(
                    $standardId
                ),

            'passingPercentage' =>
                $passingPercentage,

            'calculations' =>
                $calculations,

            'displayTotalMaxMarks' =>
                self::calculateDisplayTotalMaxMarks(
                    $columns,
                    $standardId
                ),

            'overallGradeAnalysis' =>
                self::getOverallGradeAnalysis(
                    $students,
                    $calculations
                ),

            'subjectAnalysis' =>
                self::calculateSubjectAnalysis(
                    $students,
                    $columns,
                    $calculations,
                    false
                ),

            'classTeacherName' =>
                self::formatStaffName(
                    $rawClassTeacher
                ),

            'principalName' =>
                self::formatStaffName(
                    $rawPrincipal
                ),

            'schoolCode' =>
                session(
                    'school_code',
                    'shirgaon'
                ),
        ];
    }

    public static function getSubjectHeader(
        $column,
        int $standardId
    ): array {

        $max =
            (float) (
                $column->max_marks ?? 0
            );

        return [

            'max' =>
                $max,

            'passing' =>
                $max > 0
                    ? MarksHelper::getPassingMarks(
                        $standardId,
                        $max
                    )
                    : 0,

            'optional' =>
                self::isSeniorOptionalStandard(
                    $standardId
                )
                    ? self::isSeniorOptionalColumn(
                        $column,
                        $standardId
                    )
                    : (
                        (int) (
                            $column->is_optional
                            ?? 0
                        ) === 1
                    ),
        ];
    }

    public static function displayResultMark(
        $value,
        callable $displayNumber
    ): string {

        $value =
            $value ?? '-';

        if (
            strtoupper(
                trim(
                    (string) $value
                )
            ) === 'AB'
        ) {
            return 'AB';
        }

        if (
            strtoupper(
                trim(
                    (string) $value
                )
            ) === 'OPT'
        ) {
            return 'OPT';
        }

        if (
            $value === '-'
        ) {
            return '-';
        }

        return is_numeric($value)
            ? $displayNumber($value)
            : (string) $value;
    }

    public static function displayResultGrade(
        $value
    ): string {

        if (
            $value === null
            || $value === ''
        ) {
            return '-';
        }

        $value =
            strtoupper(
                trim(
                    (string) $value
                )
            );

        if (
            $value === 'OPT'
        ) {
            return 'OPT';
        }

        if (
            $value === 'AB'
            || $value === 'ABSENT'
        ) {
            return 'AB';
        }

        return $value;
    }
}