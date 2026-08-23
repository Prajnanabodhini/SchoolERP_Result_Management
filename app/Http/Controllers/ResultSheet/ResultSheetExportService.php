<?php

namespace App\Http\Controllers\Administrator;

class ResultSheetExportService
{
    /*
    |--------------------------------------------------------------------------
    | DOWNLOAD
    |--------------------------------------------------------------------------
    */

    public function download(
        array $viewData
    ) {

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


        $classTeacher =
            $viewData['classTeacher']
            ?? null;


        $principal =
            $viewData['principal']
            ?? null;


        $totalMaxMarks =
            $viewData['totalMaxMarks']
            ?? 0;


        /*
        |--------------------------------------------------------------------------
        | SORT BY ROLL NUMBER
        |--------------------------------------------------------------------------
        */

        $results =
            $results
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


        /*
        |--------------------------------------------------------------------------
        | FILE NAME
        |--------------------------------------------------------------------------
        */

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
            + $displayColumns->count()
            + 5;


        /*
        |--------------------------------------------------------------------------
        | BUILD HTML
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
        | SCHOOL HEADER
        |--------------------------------------------------------------------------
        */

        $html .= '<table>';


        $html .= '<tr>';

        $html .= '<td colspan="' .
            $columnCount .
            '" class="title">';

        $html .= e(
            'PRAJNANABODHINI ENGLISH MEDIUM SCHOOL & JR. COLLEGE'
        );

        $html .= '</td>';

        $html .= '</tr>';


        $html .= '<tr>';

        $html .= '<td colspan="' .
            $columnCount .
            '" class="subtitle">';

        $html .= e(
            'SHIRGAON / CHIKHALI'
        );

        $html .= '</td>';

        $html .= '</tr>';


        $html .= '<tr>';

        $html .= '<td colspan="' .
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

        $html .= '<td><strong>Academic Year</strong></td>';

        $html .= '<td>';

        $html .= e(
            $yearName
        );

        $html .= '</td>';


        $html .= '<td><strong>Exam</strong></td>';

        $html .= '<td>';

        $html .= e(
            $examName
        );

        $html .= '</td>';

        $html .= '</tr>';


        $html .= '<tr>';

        $html .= '<td><strong>Standard</strong></td>';

        $html .= '<td>';

        $html .= e(
            $standardName
        );

        $html .= '</td>';


        $html .= '<td><strong>Division</strong></td>';

        $html .= '<td>';

        $html .= e(
            $divisionName
        );

        $html .= '</td>';

        $html .= '</tr>';


        $html .= '<tr>';

        $html .= '<td><strong>Class Teacher</strong></td>';

        $html .= '<td>';

        $html .= e(
            $classTeacherName
        );

        $html .= '</td>';


        $html .= '<td><strong>Principal</strong></td>';

        $html .= '<td>';

        $html .= e(
            $principalName
        );

        $html .= '</td>';

        $html .= '</tr>';


        $html .= '<tr>';

        $html .= '<td><strong>Total Maximum Marks</strong></td>';

        $html .= '<td>';

        $html .= e(
            $this->formatExcelNumber(
                $totalMaxMarks
            )
        );

        $html .= '</td>';


        $html .= '<td><strong>Overall Pass %</strong></td>';

        $html .= '<td>';

        $html .= e(
            (
                $viewData['passPercentage']
                ?? 40
            ) . '%'
        );

        $html .= '</td>';

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
                '(Max Mark='
                . $maxMark
                . ')'
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


        $srNo =
            1;


        foreach (
            $results as $student
        ) {

            $html .= '<tr>';


            $html .= '<td class="center">';

            $html .= $srNo++;

            $html .= '</td>';


            $html .= '<td class="center">';

            $html .= e(
                (string) (
                    $student->roll_no
                    ?? ''
                )
            );

            $html .= '</td>';


            $html .= '<td>';

            $html .= e(
                $student->full_student_name
                ?? ''
            );

            $html .= '</td>';


            $html .= '<td class="center">';

            $html .= e(
                $student->gender
                ?? ''
            );

            $html .= '</td>';


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


                $html .= '<td class="center">';


                if (
                    $markText === 'AB'
                ) {

                    $html .=
                        '<span class="absent">'
                        . 'AB'
                        . '</span>';

                } else {

                    $html .= e(
                        (string) $mark
                    );
                }


                $html .= '</td>';
            }


            $html .= '<td class="center">';

            $html .= e(
                (string) (
                    $student->academic_total
                    ?? '-'
                )
            );

            $html .= '</td>';


            $studentMaxTotal =
                $student->academic_max_display
                ?? $totalMaxMarks
                ?? 0;


            $html .= '<td class="center">';

            $html .= e(
                $this->formatExcelNumber(
                    $studentMaxTotal
                )
            );

            $html .= '</td>';


            $html .= '<td class="center">';


            if (
                $student->calculated_percentage
                !==
                null
            ) {

                $html .= e(
                    (string) (
                        $student->calculated_percentage
                    )
                );

                $html .= '%';

            } else {

                $html .= '-';
            }


            $html .= '</td>';


            $html .= '<td class="center">';

            $html .= e(
                (string) (
                    $student->calculated_grade
                    ?? '-'
                )
            );

            $html .= '</td>';


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


            $html .= '<td class="center ' .
                $resultClass .
                '">';

            $html .= e(
                $studentResult
            );

            $html .= '</td>';


            $html .= '</tr>';
        }


        if (
            $results->isEmpty()
        ) {

            $html .= '<tr>';

            $html .= '<td colspan="' .
                $columnCount .
                '">';

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

            $html .= '<h3>';

            $html .=
                'Overall Grade / Result Analysis';

            $html .= '</h3>';

            $html .= '<table>';

            $html .= '<thead>';

            $html .= '<tr>';

            $html .= '<th>Grade / Result</th>';

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

                $html .= '<tr>';

                $html .= '<td class="center">';

                $html .= e(
                    $grade
                );

                $html .= '</td>';


                $html .= '<td>';

                $html .= e(
                    $analysis['range']
                    ?? ''
                );

                $html .= '</td>';


                $html .= '<td class="center">';

                $html .= e(
                    $analysis['girls']
                    ?? 0
                );

                $html .= '</td>';


                $html .= '<td class="center">';

                $html .= e(
                    $analysis['boys']
                    ?? 0
                );

                $html .= '</td>';


                $html .= '<td class="center">';

                $html .= e(
                    $analysis['total']
                    ?? 0
                );

                $html .= '</td>';


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

        $this->appendSubjectAnalysisTable(
            $html,
            $viewData[
                'girlsSubjectAnalysis'
            ] ?? [],
            'Girls Subject Analysis'
        );


        /*
        |--------------------------------------------------------------------------
        | BOYS SUBJECT ANALYSIS
        |--------------------------------------------------------------------------
        */

        $this->appendSubjectAnalysisTable(
            $html,
            $viewData[
                'boysSubjectAnalysis'
            ] ?? [],
            'Boys Subject Analysis'
        );


        /*
        |--------------------------------------------------------------------------
        | CLOSE HTML
        |--------------------------------------------------------------------------
        */

        $html .= '</body>';

        $html .= '</html>';


        /*
        |--------------------------------------------------------------------------
        | RESPONSE
        |--------------------------------------------------------------------------
        */

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
    | SUBJECT ANALYSIS TABLE
    |--------------------------------------------------------------------------
    */

    private function appendSubjectAnalysisTable(
        string &$html,
        array $analysisRows,
        string $title
    ): void {

        if (
            empty($analysisRows)
        ) {

            return;
        }


        $html .= '<br>';

        $html .= '<h3>';

        $html .= e(
            $title
        );

        $html .= '</h3>';


        $html .= '<table>';

        $html .= '<thead>';

        $html .= '<tr>';

        $html .= '<th>Subject</th>';

        $html .= '<th>A1</th>';
        $html .= '<th>A2</th>';
        $html .= '<th>B1</th>';
        $html .= '<th>B2</th>';
        $html .= '<th>C1</th>';
        $html .= '<th>C2</th>';
        $html .= '<th>D</th>';
        $html .= '<th>Fail</th>';
        $html .= '<th>Absent</th>';
        $html .= '<th>Total</th>';

        $html .= '</tr>';

        $html .= '</thead>';

        $html .= '<tbody>';


        foreach (
            $analysisRows as $analysis
        ) {

            $html .= '<tr>';


            $html .= '<td>';

            $html .= e(
                $analysis['subject_name']
                ??
                $analysis['subject']
                ??
                '-'
            );

            $html .= '</td>';


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

                $html .= '<td class="center">';

                $html .= e(
                    $analysis[$field]
                    ?? 0
                );

                $html .= '</td>';
            }


            $html .= '</tr>';
        }


        $html .= '</tbody>';

        $html .= '</table>';
    }


    /*
    |--------------------------------------------------------------------------
    | CLEAN FILE NAME
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