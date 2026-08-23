<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>
        Examination Result Sheet
    </title>


    <style>

        /*
        |--------------------------------------------------------------------------
        | PAGE
        |--------------------------------------------------------------------------
        */

        @page {
            size: A4 landscape;

            /*
            | Approximately 2 cm top and left space.
            */
            margin-top: 20mm;
            margin-left: 20mm;

            /*
            | Right and bottom remain smaller.
            */
            margin-right: 7mm;
            margin-bottom: 7mm;
        }


        * {
            box-sizing: border-box;
        }


        html,
        body {
            width: 100%;

            margin: 0;

            padding: 0;
        }


        body {

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            font-size: 10px;

            color: #111827;

            background:
                #ffffff;
        }


        /*
        |--------------------------------------------------------------------------
        | PRINT
        |--------------------------------------------------------------------------
        */

        @media print {

            html,
            body {

                width: 100%;

                margin: 0;

                padding: 0;
            }


            .no-print {

                display:
                    none !important;
            }


            .print-page {

                width:
                    100%;
            }


            a[href]::after {

                content:
                    none !important;

                display:
                    none !important;
            }


            a {

                text-decoration:
                    none !important;

                color:
                    inherit !important;
            }


            thead {

                display:
                    table-header-group;
            }


            tfoot {

                display:
                    table-footer-group;
            }


            tr {

                page-break-inside:
                    avoid !important;
            }


            .analysis-block {

                page-break-inside:
                    avoid;
            }


            .signature-area {

                page-break-inside:
                    avoid;

                break-inside:
                    avoid;
            }


            .subject-wise-analysis-wrapper {

                page-break-inside:
                    avoid;
            }

        }


        /*
        |--------------------------------------------------------------------------
        | PRINT CONTROLS
        |--------------------------------------------------------------------------
        */

        .print-controls {

            position:
                fixed;

            top:
                10px;

            right:
                10px;

            z-index:
                99999;

            display:
                flex;

            align-items:
                center;

            gap:
                8px;
        }


        .print-button,
        .excel-button,
        .close-button {

            border:
                1px solid #374151;

            color:
                #ffffff;

            border-radius:
                4px;

            padding:
                7px 15px;

            font-size:
                13px;

            font-weight:
                700;

            cursor:
                pointer;

            text-decoration:
                none;

            display:
                inline-flex;

            align-items:
                center;

            justify-content:
                center;

            white-space:
                nowrap;
        }


        .print-button {

            background:
                #1d4ed8;
        }


        .excel-button {

            background:
                #15803d;
        }


        .close-button {

            background:
                #6b7280;
        }


        /*
        |--------------------------------------------------------------------------
        | PRINT PAGE
        |--------------------------------------------------------------------------
        */

        .print-page {

            width:
                100%;

            padding:
                0;

            margin:
                0;
        }


        /*
        |--------------------------------------------------------------------------
        | SCHOOL HEADER
        |--------------------------------------------------------------------------
        */

        .school-header {

            width:
                100%;

            border-collapse:
                collapse;

            table-layout:
                auto;

            margin-bottom:
                4px;
        }


        .school-header td {

            border:
                none !important;

            padding:
                1px 3px;

            vertical-align:
                middle;
        }


        .school-logo-cell {

            width:
                60px;

            text-align:
                center !important;
        }


        .school-logo {

            width:
                50px;

            height:
                50px;

            object-fit:
                contain;
        }


        .school-title-cell {

            text-align:
                center !important;
        }


        .school-name {

            font-size:
                17px;

            line-height:
                20px;

            font-weight:
                700;

            text-align:
                center;
        }


        .school-location {

            font-size:
                11px;

            line-height:
                14px;

            font-weight:
                700;

            text-align:
                center;
        }


        .school-year {

            font-size:
                11px;

            line-height:
                14px;

            font-weight:
                700;

            text-align:
                center;
        }


        /*
        |--------------------------------------------------------------------------
        | EXAM INFORMATION
        |--------------------------------------------------------------------------
        */

        .exam-info {

            width:
                100%;

            border-collapse:
                collapse;

            table-layout:
                auto;

            margin-bottom:
                5px;
        }


        .exam-info td {

            border:
                none !important;

            font-size:
                10px;

            padding:
                2px 4px;

            text-align:
                left;

            vertical-align:
                middle;

            font-weight:
                600;
        }


        .exam-info strong {

            font-weight:
                700;
        }


        /*
        |--------------------------------------------------------------------------
        | MAIN RESULT TABLE
        |--------------------------------------------------------------------------
        |
        | No fixed column widths.
        | Browser automatically adjusts all columns.
        |--------------------------------------------------------------------------
        */

        .result-sheet-wrapper {

            width:
                100%;

            overflow:
                visible;
        }


        .result-sheet-table {

            width:
                100%;

            max-width:
                100%;

            border-collapse:
                collapse;

            border-spacing:
                0;

            table-layout:
                auto;

            border:
                1px solid #333;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            font-size:
                9.5px;
        }


        .result-sheet-table th,
        .result-sheet-table td {

            border:
                1px solid #333 !important;

            border-style:
                solid !important;

            border-width:
                1px !important;

            text-align:
                center;

            vertical-align:
                middle;

            padding:
                3px 3px;

            color:
                #111827 !important;
        }


        /*
        |--------------------------------------------------------------------------
        | MAIN HEADER
        |--------------------------------------------------------------------------
        */

        .result-sheet-table th {

            background:
                #dbeafe;

            font-size:
                9px;

            font-weight:
                700 !important;

            line-height:
                1.05;

            white-space:
                normal;

            overflow-wrap:
                normal;
        }


        /*
        |--------------------------------------------------------------------------
        | BODY
        |--------------------------------------------------------------------------
        */

        .result-sheet-table td {

            font-size:
                8.8px;

            font-weight:
                500;

            line-height:
                1.05;

            white-space:
                nowrap;
        }


        /*
        |--------------------------------------------------------------------------
        | ROLL NUMBER
        |--------------------------------------------------------------------------
        */

        .result-sheet-table td:first-child {

            white-space:
                nowrap;

            font-weight:
                700;
        }


        /*
        |--------------------------------------------------------------------------
        | STUDENT NAME
        |--------------------------------------------------------------------------
        */

        .student-name-cell {

            text-align:
                left !important;

            white-space:
                nowrap !important;

            font-weight:
                700 !important;

            overflow:
                visible !important;

            text-overflow:
                clip !important;

            color:
                #111827 !important;
        }


        /*
        |--------------------------------------------------------------------------
        | SUBJECT HEADER
        |--------------------------------------------------------------------------
        */

        .subject-name {

            display:
                block;

            font-size:
                8.5px;

            font-weight:
                700 !important;

            line-height:
                9px;

            white-space:
                nowrap;
        }


        .subject-max {

            display:
                block;

            font-size:
                6.8px;

            font-weight:
                700 !important;

            line-height:
                7.5px;

            white-space:
                nowrap;
        }


        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        .absent-mark {

            color:
                #991b1b !important;

            font-weight:
                700 !important;
        }


        .fail-result {

            color:
                #991b1b !important;

            font-weight:
                700 !important;
        }


        .pass-result {

            color:
                #166534 !important;

            font-weight:
                700 !important;
        }


        /*
        |--------------------------------------------------------------------------
        | ANALYSIS
        |--------------------------------------------------------------------------
        */

        .analysis-block {

            margin-top:
                9px;
        }


        .analysis-title {

            font-size:
                12px;

            font-weight:
                700 !important;

            color:
                #1d4ed8;

            margin:
                7px 0 4px 0;
        }


        .analysis-table-wrapper {

            width:
                100%;

            overflow:
                visible;
        }


        .analysis-table {

            width:
                100%;

            max-width:
                100%;

            border-collapse:
                collapse;

            border-spacing:
                0;

            table-layout:
                auto;

            border:
                1px solid #333;

            font-size:
                8px;
        }


        .analysis-table th,
        .analysis-table td {

            border:
                1px solid #333 !important;

            padding:
                3px 3px;

            text-align:
                center;

            vertical-align:
                middle;

            color:
                #111827 !important;

            white-space:
                nowrap;
        }


        .analysis-table th {

            background:
                #dbeafe;

            font-weight:
                700 !important;
        }


        .analysis-table td:first-child {

            text-align:
                left !important;

            font-weight:
                700 !important;
        }


        /*
        |--------------------------------------------------------------------------
        | ANALYSIS TOTAL
        |--------------------------------------------------------------------------
        */

        .analysis-total-row {

            font-weight:
                700 !important;

            background:
                #e5e7eb !important;
        }


        .analysis-total-row td {

            font-weight:
                700 !important;

            background:
                #e5e7eb !important;
        }


        /*
        |--------------------------------------------------------------------------
        | SUBJECT WISE ANALYSIS
        |--------------------------------------------------------------------------
        |
        | Automatic sizing.
        | Subject name does not wrap.
        |--------------------------------------------------------------------------
        */

        .subject-wise-analysis-wrapper {

            width:
                100%;

            overflow:
                visible;
        }


        .subject-wise-analysis-table {

            width:
                100%;

            max-width:
                100%;

            border-collapse:
                collapse;

            border-spacing:
                0;

            table-layout:
                auto;

            border:
                1px solid #333;

            font-size:
                8px;
        }


        .subject-wise-analysis-table th,
        .subject-wise-analysis-table td {

            border:
                1px solid #333 !important;

            padding:
                3px 2px;

            text-align:
                center;

            vertical-align:
                middle;

            color:
                #111827 !important;

            white-space:
                nowrap;
        }


        .subject-wise-analysis-table th {

            background:
                #dbeafe;

            font-weight:
                700 !important;
        }


        .subject-wise-analysis-table
        .subject-analysis-name {

            width:
                auto;

            min-width:
                max-content;

            text-align:
                left !important;

            font-weight:
                700 !important;

            white-space:
                nowrap !important;

            overflow:
                visible !important;

            text-overflow:
                clip !important;
        }


        /*
        |--------------------------------------------------------------------------
        | SIGNATURES
        |--------------------------------------------------------------------------
        */

        .signature-area {

            width:
                100%;

            display:
                flex;

            justify-content:
                space-between;

            align-items:
                flex-end;

            margin-top:
                15px;

            page-break-inside:
                avoid;

            break-inside:
                avoid;
        }


        .signature-box {

            width:
                220px;

            text-align:
                center;

            font-size:
                10px;

            font-weight:
                700;

            page-break-inside:
                avoid;
        }


        .signature-line {

            display:
                block;

            border-bottom:
                1px solid #111827;

            width:
                100%;

            height:
                20px;

            margin-bottom:
                4px;
        }


        .signature-name {

            display:
                block;

            font-size:
                10px;

            font-weight:
                700;

            line-height:
                12px;

            text-transform:
                uppercase;
        }


        .signature-designation {

            display:
                block;

            margin-top:
                2px;

            font-size:
                9px;

            font-weight:
                700;
        }


        /*
        |--------------------------------------------------------------------------
        | SCREEN
        |--------------------------------------------------------------------------
        */

        @media screen {

            .print-page {

                padding:
                    10px;
            }

        }


        /*
        |--------------------------------------------------------------------------
        | SCREEN RESPONSIVE
        |--------------------------------------------------------------------------
        */

        @media screen and (max-width: 900px) {

            .result-sheet-table {

                font-size:
                    8px;
            }


            .result-sheet-table th {

                font-size:
                    8px;
            }


            .result-sheet-table td {

                font-size:
                    7.5px;
            }

        }

    </style>

</head>


<body>

<div class="print-page">


    {{-- =========================================================
         PRINT CONTROLS
    ========================================================== --}}

    <div class="print-controls no-print">

        <button
            type="button"
            class="print-button"
            onclick="window.print()"
        >
            Print / Save PDF
        </button>


        <a
            href="{{ route('result-sheet.export-excel', [
                'academic_year_id' =>
                    request('academic_year_id'),

                'exam_master_id' =>
                    request('exam_master_id'),

                'division_id' =>
                    request('division_id'),
            ]) }}"
            class="excel-button"
        >
            Save as Excel
        </a>


        <button
            type="button"
            class="close-button"
            onclick="window.close()"
        >
            Close
        </button>

    </div>


    {{-- =========================================================
         STAFF NAME PREPARATION
    ========================================================== --}}

    @php

        /*
        |--------------------------------------------------------------------------
        | FORMAT STAFF NAME
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | ratnamala.kapase
        |
        | becomes:
        |
        | RATNAMALA KAPASE
        |--------------------------------------------------------------------------
        */

        $formatStaffName =
            function ($name) {

                $name =
                    str_replace(
                        '.',
                        ' ',
                        trim(
                            (string)$name
                        )
                    );


                $name =
                    preg_replace(
                        '/\s+/',
                        ' ',
                        $name
                    );


                return strtoupper(
                    trim($name)
                );
            };


        $classTeacherName =
            $formatStaffName(
                $classTeacher?->user?->name
                ?? ''
            );


        $principalName =
            $formatStaffName(
                $principal?->user?->name
                ?? ''
            );

    @endphp


    {{-- =========================================================
         SCHOOL HEADER
    ========================================================== --}}

    <table class="school-header">

        <tr>

            <td class="school-logo-cell">

                <img
                    src="{{ asset('images/school-logo.png') }}"
                    class="school-logo"
                    alt="School Logo"
                >

            </td>


            <td class="school-title-cell">

                <div class="school-name">

                    PRAJNANABODHINI ENGLISH MEDIUM SCHOOL
                    &amp; JR. COLLEGE

                </div>


                <div class="school-location">

                    SHIRGAON / CHIKHALI

                </div>


                <div class="school-year">

                    Academic Year :

                    {{
                        $academicYear->year_name
                        ?? ($yearName ?? '')
                    }}

                </div>

            </td>


            <td
                style="
                    width:60px;
                    border:none !important;
                "
            ></td>

        </tr>

    </table>


    {{-- =========================================================
         EXAM INFORMATION
         NO DUPLICATE ACADEMIC YEAR
    ========================================================== --}}

    <table class="exam-info">

        <tr>

            <td>

                <strong>
                    Exam :
                </strong>

                {{
                    $exam->display_exam_name
                    ?? (
                        $exam->exam_name
                        ?? ''
                    )
                }}

            </td>


            <td>

                <strong>
                    Standard :
                </strong>

                {{
                    $standard->standard_name
                    ?? ''
                }}

            </td>


            <td>

                <strong>
                    Division :
                </strong>

                {{
                    $division->division_name
                    ?? ''
                }}

            </td>


            <td>

                <strong>
                    Class Teacher :
                </strong>

                {{
                    $classTeacherName
                    ?: '-'
                }}

            </td>


            <td>

                <strong>
                    Total Students :
                </strong>

                {{ $results->count() }}

            </td>

        </tr>

    </table>


    {{-- =========================================================
         DATA PREPARATION
    ========================================================== --}}

    @php

        /*
        |--------------------------------------------------------------------------
        | SUBJECTS
        |--------------------------------------------------------------------------
        */

        $academicDisplayColumns =
            collect(
                $displayColumns
                ?? []
            )->values();


        /*
        |--------------------------------------------------------------------------
        | TOTAL MAX MARKS
        |--------------------------------------------------------------------------
        */

        $displayTotalMaxMarks =
            (float)(
                $totalMaxMarks
                ?? 0
            );


        /*
        |--------------------------------------------------------------------------
        | SORT STUDENTS BY ROLL NUMBER
        |--------------------------------------------------------------------------
        */

        $sortedResults =
            collect(
                $results
                ?? []
            )
            ->sortBy(
                function ($student) {

                    $rollNo =
                        trim(
                            (string)(
                                $student->roll_no
                                ?? ''
                            )
                        );


                    if (
                        $rollNo !== ''
                        &&
                        is_numeric($rollNo)
                    ) {

                        return [
                            0,
                            (int)$rollNo,
                        ];
                    }


                    return [
                        1,
                        strtoupper($rollNo),
                    ];
                }
            )
            ->values();

    @endphp


    {{-- =========================================================
         MAIN RESULT TABLE
    ========================================================== --}}

    <div class="result-sheet-wrapper">

        <table class="result-sheet-table">

            <thead>

                <tr>


                    {{-- ROLL --}}

                    <th>
                        Roll No
                    </th>


                    {{-- STUDENT --}}

                    <th>
                        Student Name
                    </th>


                    {{-- SUBJECTS --}}

                    @foreach(
                        $academicDisplayColumns
                        as $column
                    )

                        @php

                            $subjectMax =
                                (float)(
                                    $column->max_marks
                                    ?? 0
                                );


                            $subjectPassing =
                                (float)(
                                    $column->passing_marks
                                    ?? 0
                                );


                            $subjectMaxDisplay =
                                floor(
                                    $subjectMax
                                )
                                ===
                                $subjectMax

                                    ? (int)
                                        $subjectMax

                                    : number_format(
                                        $subjectMax,
                                        2
                                    );


                            $subjectPassingDisplay =
                                floor(
                                    $subjectPassing
                                )
                                ===
                                $subjectPassing

                                    ? (int)
                                        $subjectPassing

                                    : number_format(
                                        $subjectPassing,
                                        2
                                    );

                        @endphp


                        <th
                            colspan="2"
                        >

                            <span
                                class="subject-name"
                            >

                                {{
                                    $column->subject_name
                                    ?? '-'
                                }}

                            </span>


                            <span
                                class="subject-max"
                            >

                                Max =
                                {{ $subjectMaxDisplay }}

                            </span>


                            <span
                                class="subject-max"
                            >

                                Pass =
                                {{ $subjectPassingDisplay }}

                            </span>

                        </th>

                    @endforeach


                    {{-- TOTAL --}}

                    <th>

                        Total

                        <span
                            class="subject-max"
                        >

                            Max =

                            {{
                                floor(
                                    $displayTotalMaxMarks
                                )
                                ===
                                $displayTotalMaxMarks

                                    ? (int)
                                        $displayTotalMaxMarks

                                    : number_format(
                                        $displayTotalMaxMarks,
                                        2
                                    )
                            }}

                        </span>

                    </th>


                    {{-- PERCENTAGE --}}

                    <th>
                        Per. %
                    </th>


                    {{-- GRADE --}}

                    <th>
                        Grade
                    </th>


                    {{-- RESULT --}}

                    <th>
                        Result
                    </th>

                </tr>


                {{-- SECOND HEADER ROW --}}

                <tr>

                    <th></th>

                    <th></th>


                    @foreach(
                        $academicDisplayColumns
                        as $column
                    )

                        <th>
                            Marks
                        </th>

                        <th>
                            Grade
                        </th>

                    @endforeach


                    <th></th>

                    <th></th>

                    <th></th>

                    <th></th>

                </tr>

            </thead>


            <tbody>


            {{-- =====================================================
                 STUDENTS
            ====================================================== --}}

            @forelse(
                $sortedResults
                as $student
            )

                <tr>


                    {{-- ROLL NUMBER --}}

                    <td>

                        {{
                            $student->roll_no
                            ?: '-'
                        }}

                    </td>


                    {{-- STUDENT NAME --}}

                    <td
                        class="student-name-cell"
                        title="{{
                            $student->full_student_name
                            ?? ''
                        }}"
                    >

                        {{
                            $student->full_student_name
                            ?? '-'
                        }}

                    </td>


                    {{-- SUBJECTS --}}

                    @foreach(
                        $academicDisplayColumns
                        as $column
                    )

                        @php

                            $subjectKey =
                                $column->key;


                            $mark =
                                $student->subject_marks[
                                    $subjectKey
                                ]
                                ?? '-';


                            $grade =
                                $student->subject_grades[
                                    $subjectKey
                                ]
                                ?? '-';


                            $markText =
                                strtoupper(
                                    trim(
                                        (string)$mark
                                    )
                                );


                            $gradeText =
                                strtoupper(
                                    trim(
                                        (string)$grade
                                    )
                                );

                        @endphp


                        {{-- MARK --}}

                        <td>

                            @if(
                                $markText === 'AB'
                            )

                                <span
                                    class="absent-mark"
                                >
                                    AB
                                </span>

                            @elseif(
                                $mark === '-'
                                ||
                                $mark === null
                                ||
                                $mark === ''
                            )

                                -

                            @elseif(
                                is_numeric($mark)
                            )

                                {{
                                    floor(
                                        (float)$mark
                                    )
                                    ===
                                    (float)$mark

                                        ? (int)$mark

                                        : number_format(
                                            (float)$mark,
                                            2
                                        )
                                }}

                            @else

                                {{ $mark }}

                            @endif

                        </td>


                        {{-- GRADE --}}

                        <td>

                            @if(
                                $gradeText === 'AB'
                            )

                                <span
                                    class="absent-mark"
                                >
                                    AB
                                </span>

                            @elseif(
                                $gradeText === 'F'
                            )

                                <span
                                    class="fail-result"
                                >
                                    F
                                </span>

                            @elseif(
                                $grade === '-'
                                ||
                                $grade === null
                                ||
                                $grade === ''
                            )

                                -

                            @else

                                {{ $grade }}

                            @endif

                        </td>

                    @endforeach


                    {{-- TOTAL --}}

                    <td>

                        @php

                            $academicTotal =
                                $student
                                    ->academic_total
                                ?? null;

                        @endphp


                        @if(
                            $academicTotal === null
                            ||
                            $academicTotal === ''
                            ||
                            $academicTotal === '-'
                        )

                            -

                        @elseif(
                            is_numeric(
                                $academicTotal
                            )
                        )

                            {{
                                floor(
                                    (float)$academicTotal
                                )
                                ===
                                (float)$academicTotal

                                    ? (int)$academicTotal

                                    : number_format(
                                        (float)$academicTotal,
                                        2
                                    )
                            }}

                        @else

                            {{ $academicTotal }}

                        @endif

                    </td>


                    {{-- PER % --}}

                    <td>

                        @if(
                            $student
                                ->calculated_percentage
                            === null
                        )

                            -

                        @else

                            {{
                                (int)(
                                    $student
                                        ->calculated_percentage
                                )
                            }}%

                        @endif

                    </td>


                    {{-- OVERALL GRADE --}}

                    <td>

                        @php

                            $overallGrade =
                                strtoupper(
                                    trim(
                                        (string)(
                                            $student
                                                ->calculated_grade
                                            ?? '-'
                                        )
                                    )
                                );

                        @endphp


                        @if(
                            $overallGrade === 'F'
                        )

                            <span
                                class="fail-result"
                            >
                                F
                            </span>

                        @elseif(
                            $overallGrade === 'AB'
                        )

                            <span
                                class="absent-mark"
                            >
                                AB
                            </span>

                        @elseif(
                            $overallGrade === '-'
                        )

                            -

                        @else

                            {{ $overallGrade }}

                        @endif

                    </td>


                    {{-- RESULT --}}

                    <td>

                        @php

                            $studentResult =
                                strtoupper(
                                    trim(
                                        (string)(
                                            $student
                                                ->result
                                            ?? '-'
                                        )
                                    )
                                );

                        @endphp


                        @if(
                            $studentResult === 'PASS'
                        )

                            <span
                                class="pass-result"
                            >
                                PASS
                            </span>

                        @elseif(
                            $studentResult === 'FAIL'
                        )

                            <span
                                class="fail-result"
                            >
                                FAIL
                            </span>

                        @elseif(
                            $studentResult === 'AB'
                        )

                            <span
                                class="absent-mark"
                            >
                                AB
                            </span>

                        @else

                            -

                        @endif

                    </td>

                </tr>


            @empty

                <tr>

                    <td
                        colspan="{{
                            4
                            +
                            (
                                $academicDisplayColumns->count()
                                * 2
                            )
                            + 2
                        }}"
                        style="
                            padding:10px;
                            text-align:center;
                            font-weight:700;
                        "
                    >

                        No result records found.

                    </td>

                </tr>

            @endforelse


            </tbody>

        </table>

    </div>


    {{-- =========================================================
         OVERALL GRADE / RESULT ANALYSIS
    ========================================================== --}}

    <div class="analysis-block">

        <div class="analysis-title">

            Overall Grade / Result Analysis

        </div>


        <table class="analysis-table">

            <thead>

                <tr>

                    <th>
                        Grade / Result
                    </th>

                    <th>
                        Range
                    </th>

                    <th>
                        Girls
                    </th>

                    <th>
                        Boys
                    </th>

                    <th>
                        Total
                    </th>

                </tr>

            </thead>


            <tbody>

            @php

                $overallOrder = [

                    'A1',
                    'A2',
                    'B1',
                    'B2',
                    'C1',
                    'C2',
                    'D',
                    'F',
                    'PASS',
                    'FAIL',

                ];

            @endphp


            @if(
                !empty(
                    $overallGradeAnalysis
                    ?? []
                )
            )

                @foreach(
                    $overallOrder
                    as $grade
                )

                    @php

                        $row =
                            $overallGradeAnalysis[
                                $grade
                            ]
                            ??
                            [
                                'range' =>
                                    '-',

                                'girls' =>
                                    0,

                                'boys' =>
                                    0,

                                'total' =>
                                    0,
                            ];

                    @endphp


                    <tr>

                        <td>
                            {{ $grade }}
                        </td>

                        <td>
                            {{
                                $row['range']
                                ?? '-'
                            }}
                        </td>

                        <td>
                            {{
                                $row['girls']
                                ?? 0
                            }}
                        </td>

                        <td>
                            {{
                                $row['boys']
                                ?? 0
                            }}
                        </td>

                        <td>
                            {{
                                $row['total']
                                ?? 0
                            }}
                        </td>

                    </tr>

                @endforeach


                @php

                    $totalRow =
                        $overallGradeAnalysis[
                            'TOTAL'
                        ]
                        ??
                        [

                            'girls' =>
                                (
                                    $overallGradeAnalysis[
                                        'PASS'
                                    ]['girls']
                                    ?? 0
                                )
                                +
                                (
                                    $overallGradeAnalysis[
                                        'FAIL'
                                    ]['girls']
                                    ?? 0
                                ),

                            'boys' =>
                                (
                                    $overallGradeAnalysis[
                                        'PASS'
                                    ]['boys']
                                    ?? 0
                                )
                                +
                                (
                                    $overallGradeAnalysis[
                                        'FAIL'
                                    ]['boys']
                                    ?? 0
                                ),

                            'total' =>
                                (
                                    $overallGradeAnalysis[
                                        'PASS'
                                    ]['total']
                                    ?? 0
                                )
                                +
                                (
                                    $overallGradeAnalysis[
                                        'FAIL'
                                    ]['total']
                                    ?? 0
                                ),

                        ];

                @endphp


                <tr
                    class="analysis-total-row"
                >

                    <td>
                        TOTAL
                    </td>

                    <td>
                        TOTAL
                    </td>

                    <td>
                        {{
                            $totalRow['girls']
                            ?? 0
                        }}
                    </td>

                    <td>
                        {{
                            $totalRow['boys']
                            ?? 0
                        }}
                    </td>

                    <td>
                        {{
                            $totalRow['total']
                            ?? 0
                        }}
                    </td>

                </tr>


            @else

                <tr>

                    <td colspan="5">

                        No overall analysis available.

                    </td>

                </tr>

            @endif

            </tbody>

        </table>

    </div>


    {{-- =========================================================
         SUBJECT WISE ANALYSIS
    ========================================================== --}}

    <div class="analysis-block">

        <div class="analysis-title">

            Subject Wise Analysis

        </div>


        @php

            $girlsBySubject =
                collect(
                    $girlsSubjectAnalysis
                    ?? []
                )->keyBy(
                    'subject_code'
                );


            $boysBySubject =
                collect(
                    $boysSubjectAnalysis
                    ?? []
                )->keyBy(
                    'subject_code'
                );


            $analysisSubjects =
                $academicDisplayColumns
                    ->values();


            $analysisCategories = [

                'A1',
                'A2',
                'B1',
                'B2',
                'C1',
                'C2',
                'D',
                'FAIL',
                'ABSENT',

            ];

        @endphp


        <div
            class="subject-wise-analysis-wrapper"
        >

            <table
                class="subject-wise-analysis-table"
            >

                <thead>

                    <tr>

                        <th rowspan="2">

                            Subject

                        </th>


                        @foreach(
                            $analysisCategories
                            as $category
                        )

                            <th colspan="2">

                                {{ $category }}

                            </th>

                        @endforeach

                    </tr>


                    <tr>

                        @foreach(
                            $analysisCategories
                            as $category
                        )

                            <th>
                                Girls
                            </th>

                            <th>
                                Boys
                            </th>

                        @endforeach

                    </tr>

                </thead>


                <tbody>


                {{-- =================================================
                     SUBJECT ROWS
                ================================================== --}}

                @foreach(
                    $analysisSubjects
                    as $analysisSubject
                )

                    @php

                        $subjectCode =
                            trim(
                                (string)(
                                    $analysisSubject
                                        ->subject_code
                                    ?? ''
                                )
                            );


                        $subjectFullName =
                            trim(
                                (string)(
                                    $analysisSubject
                                        ->subject_name
                                    ?? ''
                                )
                            );


                        if (
                            $subjectFullName === ''
                        ) {

                            $subjectFullName =
                                $subjectCode
                                ?: '-';
                        }


                        $defaultAnalysis = [

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

                            'pending' =>
                                0,

                            'total' =>
                                0,

                        ];


                        $girls =
                            array_merge(
                                $defaultAnalysis,

                                $girlsBySubject->get(
                                    $subjectCode,
                                    []
                                )
                            );


                        $boys =
                            array_merge(
                                $defaultAnalysis,

                                $boysBySubject->get(
                                    $subjectCode,
                                    []
                                )
                            );

                    @endphp


                    <tr>

                        <td
                            class="
                                subject-analysis-name
                            "
                            title="{{ $subjectFullName }}"
                        >

                            {{ $subjectFullName }}

                        </td>


                        {{-- A1 --}}

                        <td>
                            {{ $girls['A1'] }}
                        </td>

                        <td>
                            {{ $boys['A1'] }}
                        </td>


                        {{-- A2 --}}

                        <td>
                            {{ $girls['A2'] }}
                        </td>

                        <td>
                            {{ $boys['A2'] }}
                        </td>


                        {{-- B1 --}}

                        <td>
                            {{ $girls['B1'] }}
                        </td>

                        <td>
                            {{ $boys['B1'] }}
                        </td>


                        {{-- B2 --}}

                        <td>
                            {{ $girls['B2'] }}
                        </td>

                        <td>
                            {{ $boys['B2'] }}
                        </td>


                        {{-- C1 --}}

                        <td>
                            {{ $girls['C1'] }}
                        </td>

                        <td>
                            {{ $boys['C1'] }}
                        </td>


                        {{-- C2 --}}

                        <td>
                            {{ $girls['C2'] }}
                        </td>

                        <td>
                            {{ $boys['C2'] }}
                        </td>


                        {{-- D --}}

                        <td>
                            {{ $girls['D'] }}
                        </td>

                        <td>
                            {{ $boys['D'] }}
                        </td>


                        {{-- FAIL --}}

                        <td>
                            {{ $girls['fail'] }}
                        </td>

                        <td>
                            {{ $boys['fail'] }}
                        </td>


                        {{-- ABSENT --}}

                        <td>
                            {{ $girls['absent'] }}
                        </td>

                        <td>
                            {{ $boys['absent'] }}
                        </td>

                    </tr>

                @endforeach


                {{-- =================================================
                     TOTAL ROW
                ================================================== --}}

                @php

                    $categoryKeys = [

                        'A1',
                        'A2',
                        'B1',
                        'B2',
                        'C1',
                        'C2',
                        'D',
                        'fail',
                        'absent',

                    ];


                    $grandTotals = [];


                    foreach (
                        $categoryKeys
                        as $categoryKey
                    ) {

                        $grandTotals[
                            $categoryKey
                            . '_girls'
                        ] =
                            $girlsBySubject->sum(
                                fn($row) =>
                                    (int)(
                                        $row[
                                            $categoryKey
                                        ]
                                        ?? 0
                                    )
                            );


                        $grandTotals[
                            $categoryKey
                            . '_boys'
                        ] =
                            $boysBySubject->sum(
                                fn($row) =>
                                    (int)(
                                        $row[
                                            $categoryKey
                                        ]
                                        ?? 0
                                    )
                            );

                    }

                @endphp


                <tr
                    class="analysis-total-row"
                >

                    <td
                        class="
                            subject-analysis-name
                        "
                    >

                        TOTAL

                    </td>


                    {{-- A1 --}}

                    <td>
                        {{
                            $grandTotals[
                                'A1_girls'
                            ]
                            ?? 0
                        }}
                    </td>

                    <td>
                        {{
                            $grandTotals[
                                'A1_boys'
                            ]
                            ?? 0
                        }}
                    </td>


                    {{-- A2 --}}

                    <td>
                        {{
                            $grandTotals[
                                'A2_girls'
                            ]
                            ?? 0
                        }}
                    </td>

                    <td>
                        {{
                            $grandTotals[
                                'A2_boys'
                            ]
                            ?? 0
                        }}
                    </td>


                    {{-- B1 --}}

                    <td>
                        {{
                            $grandTotals[
                                'B1_girls'
                            ]
                            ?? 0
                        }}
                    </td>

                    <td>
                        {{
                            $grandTotals[
                                'B1_boys'
                            ]
                            ?? 0
                        }}
                    </td>


                    {{-- B2 --}}

                    <td>
                        {{
                            $grandTotals[
                                'B2_girls'
                            ]
                            ?? 0
                        }}
                    </td>

                    <td>
                        {{
                            $grandTotals[
                                'B2_boys'
                            ]
                            ?? 0
                        }}
                    </td>


                    {{-- C1 --}}

                    <td>
                        {{
                            $grandTotals[
                                'C1_girls'
                            ]
                            ?? 0
                        }}
                    </td>

                    <td>
                        {{
                            $grandTotals[
                                'C1_boys'
                            ]
                            ?? 0
                        }}
                    </td>


                    {{-- C2 --}}

                    <td>
                        {{
                            $grandTotals[
                                'C2_girls'
                            ]
                            ?? 0
                        }}
                    </td>

                    <td>
                        {{
                            $grandTotals[
                                'C2_boys'
                            ]
                            ?? 0
                        }}
                    </td>


                    {{-- D --}}

                    <td>
                        {{
                            $grandTotals[
                                'D_girls'
                            ]
                            ?? 0
                        }}
                    </td>

                    <td>
                        {{
                            $grandTotals[
                                'D_boys'
                            ]
                            ?? 0
                        }}
                    </td>


                    {{-- FAIL --}}

                    <td>
                        {{
                            $grandTotals[
                                'fail_girls'
                            ]
                            ?? 0
                        }}
                    </td>

                    <td>
                        {{
                            $grandTotals[
                                'fail_boys'
                            ]
                            ?? 0
                        }}
                    </td>


                    {{-- ABSENT --}}

                    <td>
                        {{
                            $grandTotals[
                                'absent_girls'
                            ]
                            ?? 0
                        }}
                    </td>

                    <td>
                        {{
                            $grandTotals[
                                'absent_boys'
                            ]
                            ?? 0
                        }}
                    </td>

                </tr>

                </tbody>

            </table>

        </div>

    </div>


    {{-- =========================================================
         SIGNATURES
    ========================================================== --}}

    <div class="signature-area">


        {{-- =====================================================
             CLASS TEACHER
        ====================================================== --}}

        <div class="signature-box">

            <span class="signature-line"></span>

            <span class="signature-name">

                {{
                    $classTeacherName
                    ?: '-'
                }}

            </span>

            <span class="signature-designation">

                CLASS TEACHER

            </span>

        </div>


        {{-- =====================================================
             PRINCIPAL
        ====================================================== --}}

        <div class="signature-box">

            <span class="signature-line"></span>

            <span class="signature-name">

                {{
                    $principalName
                    ?: '-'
                }}

            </span>

            <span class="signature-designation">

                PRINCIPAL

            </span>

        </div>

    </div>


</div>

</body>

</html>