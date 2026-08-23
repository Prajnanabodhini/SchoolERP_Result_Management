<?php

namespace App\Http\Controllers\Administrator;

class ResultSheetAnalysisService
{
    /*
    |--------------------------------------------------------------------------
    | BUILD RESULTS
    |--------------------------------------------------------------------------
    */

    public function buildResults(
        array $marksByStudent,
        array $erpStudents,
        $displayColumns,
        float $totalMaxMarks,
        int $passPercentage
    ) {

        $results =
            collect();


        foreach (
            $marksByStudent as $studentId => $studentMarks
        ) {

            $studentId =
                (int) $studentId;


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
            | PROCESS SUBJECTS
            |--------------------------------------------------------------------------
            */

            foreach (
                $displayColumns as $column
            ) {

                $subjectId =
                    (int) $column->subject_id;


                $student->subject_marks[
                    $column->key
                ] = '-';


                $student->subject_grades[
                    $column->key
                ] = '-';


                $student->subject_results[
                    $column->key
                ] = '-';


                $markRow =
                    $studentMarks[
                        $subjectId
                    ] ?? null;


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


                    $maxMarks =
                        (float) (
                            $column->max_marks
                            ?? 0
                        );


                    $student->academic_max_used +=
                        $maxMarks;


                    $student->subject_max_used[
                        $column->key
                    ] =
                        $maxMarks;


                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | OBTAINED MARKS
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
                | MARK
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
                | MAX
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
                | PERCENTAGE
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
                | GRADE
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
                | RESULT
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
            | TOTAL
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
            | NO MARKS
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
                | OVERALL PERCENTAGE
                |--------------------------------------------------------------------------
                */

                $student->calculated_percentage =
                    (
                        (float) $student->academic_total
                        /
                        (float) $student->academic_max_used
                    ) * 100;


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
        | SORT BY ROLL NUMBER
        |--------------------------------------------------------------------------
        */

        return $results
            ->sortBy(
                function ($student) {

                    $rollNo =
                        $student->roll_no
                        ?? '';


                    return is_numeric($rollNo)
                        ? (int) $rollNo
                        : PHP_INT_MAX;
                }
            )
            ->values();
    }


    /*
    |--------------------------------------------------------------------------
    | OVERALL PASS PERCENTAGE
    |--------------------------------------------------------------------------
    */

    public function getOverallPassPercentage(
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

    public function getGradeFromPercentage(
        $percentage
    ): string {

        $percentage =
            (float) $percentage;


        if (
            $percentage >= 91
        ) {
            return 'A1';
        }


        if (
            $percentage >= 81
        ) {
            return 'A2';
        }


        if (
            $percentage >= 71
        ) {
            return 'B1';
        }


        if (
            $percentage >= 61
        ) {
            return 'B2';
        }


        if (
            $percentage >= 51
        ) {
            return 'C1';
        }


        if (
            $percentage >= 41
        ) {
            return 'C2';
        }


        if (
            $percentage >= 33
        ) {
            return 'D';
        }


        return 'F';
    }


    /*
    |--------------------------------------------------------------------------
    | OVERALL GRADE ANALYSIS
    |--------------------------------------------------------------------------
    */

    public function buildOverallGradeAnalysis(
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

            $normalizedGender =
                $this->normalizeGender(
                    $student->gender
                    ?? ''
                );


            if (
                $normalizedGender === 'FEMALE'
            ) {

                $genderKey =
                    'girls';

            } elseif (
                $normalizedGender === 'MALE'
            ) {

                $genderKey =
                    'boys';

            } else {

                $genderKey =
                    null;
            }


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


            $analysis['TOTAL']['total']++;


            if (
                $genderKey !== null
            ) {

                $analysis['TOTAL'][
                    $genderKey
                ]++;
            }


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
                )
                &&
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

                if (
                    $genderKey !== null
                ) {

                    $analysis[$grade][
                        $genderKey
                    ]++;
                }


                $analysis[$grade]['total']++;
            }


            if (
                $result === 'PASS'
            ) {

                if (
                    $genderKey !== null
                ) {

                    $analysis['PASS'][
                        $genderKey
                    ]++;
                }


                $analysis['PASS']['total']++;

            } else {

                if (
                    $genderKey !== null
                ) {

                    $analysis['FAIL'][
                        $genderKey
                    ]++;
                }


                $analysis['FAIL']['total']++;
            }
        }


        $analysis['TOTAL']['total'] =
            $analysis['PASS']['total']
            +
            $analysis['FAIL']['total'];


        $analysis['TOTAL']['girls'] =
            $analysis['PASS']['girls']
            +
            $analysis['FAIL']['girls'];


        $analysis['TOTAL']['boys'] =
            $analysis['PASS']['boys']
            +
            $analysis['FAIL']['boys'];


        return $analysis;
    }


    /*
    |--------------------------------------------------------------------------
    | SUBJECT ANALYSIS
    |--------------------------------------------------------------------------
    */

    public function buildSubjectAnalysis(
        $results,
        $subjects,
        string $gender
    ): array {

        $analysis =
            [];


        $requestedGender =
            $this->normalizeGender(
                $gender
            );


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


                $mark =
                    $student->subject_marks[
                        $subject->key
                    ] ?? '-';


                $grade =
                    $student->subject_grades[
                        $subject->key
                    ] ?? '-';


                if (
                    $mark === '-'
                ) {

                    continue;
                }


                if (
                    strtoupper(
                        trim(
                            (string) $mark
                        )
                    ) === 'AB'
                ) {

                    $row['absent']++;

                    continue;
                }


                if (
                    strtoupper(
                        trim(
                            (string) $grade
                        )
                    ) === 'F'
                ) {

                    $row['fail']++;

                    continue;
                }


                if (
                    isset(
                        $row[$grade]
                    )
                ) {

                    $row[$grade]++;
                }
            }


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
    | NORMALIZE SUBJECT
    |--------------------------------------------------------------------------
    */

    public function normalizeSubjectText(
        $value
    ): string {

        $value =
            strtoupper(
                trim(
                    (string) $value
                )
            );


        return preg_replace(
            '/[^A-Z0-9]+/',
            '',
            $value
        );
    }


    /*
    |--------------------------------------------------------------------------
    | NORMALIZE GENDER
    |--------------------------------------------------------------------------
    */

    public function normalizeGender(
        $gender
    ): string {

        $gender =
            strtoupper(
                trim(
                    (string) $gender
                )
            );


        $gender =
            preg_replace(
                '/[^A-Z]/',
                '',
                $gender
            );


        if (
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
    */

    public function extractObtainedMarks(
        $row
    ): ?float {

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
                    (float) $row->{$field};
            }
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
    */

    public function isAbsentMark(
        $row
    ): bool {

        if (
            isset(
                $row->is_absent
            )
            &&
            (int) $row->is_absent === 1
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
                        (string) $row->{$field}
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
    | FORMAT MARK
    |--------------------------------------------------------------------------
    */

    public function formatMark(
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
}