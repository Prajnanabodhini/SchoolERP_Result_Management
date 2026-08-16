<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>
        Examination Result Sheet
    </title>

    <style>

        @page {
            size: A4 landscape;
            margin: 5mm;
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
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #111827;
            background: #ffffff;
        }


        /* =========================================================
           PRINT
        ========================================================== */

        @media print {

            html,
            body {
                width: 100%;
                margin: 0;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            .print-page {
                width: 100%;
            }

            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-footer-group;
            }

            tr {
                page-break-inside: avoid !important;
            }

            .analysis-block {
                page-break-inside: avoid;
            }
        }


        /* =========================================================
           PRINT BUTTON
        ========================================================== */

        .print-controls {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 99999;
        }

        .print-button {
            border: 1px solid #374151;
            background: #1d4ed8;
            color: #ffffff;
            border-radius: 4px;
            padding: 7px 15px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }


        /* =========================================================
           SCHOOL HEADER
        ========================================================== */

        .school-header {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 3px;
        }

        .school-header td {
            border: none !important;
            padding: 1px 3px;
            vertical-align: middle;
        }

        .school-logo-cell {
            width: 75px;
            text-align: center !important;
        }

        .school-logo {
            width: 55px;
            height: 55px;
            object-fit: contain;
        }

        .school-title-cell {
            text-align: center !important;
        }

        .school-name {
            font-size: 18px;
            line-height: 21px;
            font-weight: 700;
            text-align: center;
        }

        .school-location {
            font-size: 11px;
            line-height: 14px;
            font-weight: 700;
            text-align: center;
        }

        .school-year {
            font-size: 12px;
            line-height: 14px;
            font-weight: 600;
            text-align: center;
        }


        /* =========================================================
           EXAM INFORMATION
        ========================================================== */

        .exam-info {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 5px;
        }

        .exam-info td {
            border: none !important;
            font-size: 10.5px;
            padding: 2px 4px;
            text-align: left;
            vertical-align: middle;
        }


        /* =========================================================
           MAIN RESULT TABLE
        ========================================================== */

        .result-sheet-wrapper {
            width: 100%;
            overflow: visible;
        }

        .result-sheet-table {
            width: 100%;
            max-width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            table-layout: fixed;

            border: 1px solid #333;

            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5px;
        }

        .result-sheet-table th,
        .result-sheet-table td {
            border: 1px solid #333 !important;
            border-style: solid !important;
            border-width: 1px !important;

            color: #111827 !important;

            text-align: center;
            vertical-align: middle;
        }

        .result-sheet-table th {
            background: #dbeafe;
            font-size: 9px;
            font-weight: 700;

            padding: 3px 1px;

            line-height: 1.05;

            white-space: normal;
            overflow-wrap: break-word;
            word-break: normal;
        }

        .result-sheet-table td {
            font-size: 9px;
            font-weight: 500;

            padding: 3px 1px;

            line-height: 1.05;

            white-space: nowrap;
        }


        /* =========================================================
           SUBJECT HEADER
        ========================================================== */

        .subject-code {
            display: block;
            font-size: 8.5px;
            font-weight: 700;
            line-height: 9px;
        }

        .subject-name {
            display: block;
            font-size: 7.5px;
            font-weight: 600;
            line-height: 8px;
        }

        .subject-max {
            display: block;
            font-size: 6.8px;
            font-weight: 400;
            line-height: 7.2px;
        }


        /* =========================================================
           STUDENT NAME
        ========================================================== */

        .student-name-cell {
            text-align: left !important;
            white-space: nowrap !important;
            overflow: hidden;
            text-overflow: ellipsis;
        }


        /* =========================================================
           RESULT VALUES
        ========================================================== */

        .absent-mark {
            color: #991b1b !important;
            font-weight: 700;
        }

        .fail-result {
            color: #991b1b !important;
            font-weight: 700;
        }

        .pass-result {
            color: #166534 !important;
            font-weight: 700;
        }


        /* =========================================================
           ANALYSIS
        ========================================================== */

        .analysis-block {
            margin-top: 10px;
        }

        .analysis-title {
            font-size: 13px;
            font-weight: 700;
            color: #1d4ed8;
            margin: 9px 0 4px 0;
        }

        .analysis-table-wrapper {
            width: 100%;
            overflow: visible;
        }

        .analysis-table {
            width: 100%;
            max-width: 100%;
            border-collapse: collapse;
            border-spacing: 0;

            border: 1px solid #333;

            font-size: 8.5px;
        }

        .analysis-table th,
        .analysis-table td {
            border: 1px solid #333 !important;
            border-style: solid !important;
            border-width: 1px !important;

            text-align: center;
            vertical-align: middle;

            padding: 3px 2px;

            color: #111827 !important;
        }

        .analysis-table th {
            background: #dbeafe;
            font-weight: 700;
        }

        .analysis-table td:first-child {
            text-align: left !important;
            font-weight: 600;
        }


        /* =========================================================
           SUBJECT WISE ANALYSIS
        ========================================================== */

        .subject-wise-analysis-wrapper {
            width: 100%;
            overflow: visible;
        }

        .subject-wise-analysis-table {
            width: 100%;
            max-width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            table-layout: fixed;

            border: 1px solid #333;

            font-size: 8.5px;
        }

        .subject-wise-analysis-table th,
        .subject-wise-analysis-table td {
            border: 1px solid #333 !important;
            border-style: solid !important;
            border-width: 1px !important;

            padding: 3px 2px;

            text-align: center;
            vertical-align: middle;

            color: #111827 !important;
        }

        .subject-wise-analysis-table th {
            background: #dbeafe;
            font-weight: 700;
            white-space: nowrap;
        }

        .subject-wise-analysis-table .subject-analysis-name {
            width: 155px;
            min-width: 155px;
            max-width: 155px;

            text-align: left !important;
            font-weight: 700;

            white-space: normal !important;
            word-break: normal;
            overflow-wrap: break-word;
        }

        .subject-wise-analysis-table .analysis-number-column {
            width: 33px;
            min-width: 33px;
            max-width: 33px;
        }

        .subject-wise-analysis-table .analysis-total-column {
            width: 40px;
            min-width: 40px;
            max-width: 40px;

            font-weight: 700;
        }


        /* =========================================================
           SIGNATURES
        ========================================================== */

        .signature-area {
            width: 100%;
            display: flex;
            justify-content: space-between;
            margin-top: 18px;
        }

        .signature-box {
            width: 180px;
            text-align: center;
            font-size: 10px;
            font-weight: 600;
        }

    </style>

</head>


<body>


<div class="print-page">


    {{-- =========================================================
         PRINT BUTTON
    ========================================================== --}}

    <div class="print-controls no-print">

        <button
            type="button"
            class="print-button"
            onclick="window.print()"
        >
            Print
        </button>

    </div>


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
                    PRAJNANABODHINI ENGLISH MEDIUM SCHOOL &amp; JR. COLLEGE
                </div>

                <div class="school-location">
                    SHIRGAON / CHIKHALI
                </div>

                <div class="school-year">
                    Academic Year :
                    {{ $academicYear->year_name ?? ($yearName ?? '') }}
                </div>

            </td>


            <td style="width:75px;border:none !important;"></td>

        </tr>

    </table>


    {{-- =========================================================
         EXAM INFORMATION
    ========================================================== --}}

    <table class="exam-info">

        <tr>

            <td>
                <strong>Academic Year :</strong>
                {{ $academicYear->year_name ?? ($yearName ?? '') }}
            </td>

            <td>
                <strong>Exam :</strong>
                {{ $exam->display_exam_name ?? ($exam->exam_name ?? '') }}
            </td>

            <td>
                <strong>Standard :</strong>
                {{ $standard->standard_name ?? '' }}
            </td>

            <td>
                <strong>Division :</strong>
                {{ $division->division_name ?? '' }}
            </td>

            <td>
                <strong>Total Students :</strong>
                {{ $results->count() }}
            </td>

        </tr>

    </table>


    {{-- =========================================================
         SUBJECT DISPLAY DATA
    ========================================================== --}}

    @php

        /*
        |--------------------------------------------------------------------------
        | SAME SUBJECT LIST AS INDEX
        |--------------------------------------------------------------------------
        */

        $academicDisplayColumns =
            collect(
                $displayColumns ?? []
            )->values();


        $subjectCount =
            $academicDisplayColumns->count();


        /*
        |--------------------------------------------------------------------------
        | SAME WIDTH LOGIC AS INDEX
        |--------------------------------------------------------------------------
        */

        $subjectArea =
            61;


        $eachSubjectWidth =
            $subjectCount > 0
                ? $subjectArea / $subjectCount
                : 5;


        $eachSubColumnWidth =
            $eachSubjectWidth / 2;


        /*
        |--------------------------------------------------------------------------
        | FIXED TOTAL MAX
        |--------------------------------------------------------------------------
        */

        $displayTotalMaxMarks =
            (float)(
                $totalMaxMarks ?? 0
            );

    @endphp


    {{-- =========================================================
         MAIN RESULT TABLE
    ========================================================== --}}

    <div class="result-sheet-wrapper">

        <table class="result-sheet-table">

            <colgroup>

                {{-- ROLL --}}

                <col style="width:5%;">


                {{-- STUDENT NAME --}}

                <col style="width:15%;">


                {{-- SUBJECTS --}}

                @foreach(
                    $academicDisplayColumns as $column
                )

                    <col
                        style="
                            width:{{ $eachSubColumnWidth }}%;
                        "
                    >

                    <col
                        style="
                            width:{{ $eachSubColumnWidth }}%;
                        "
                    >

                @endforeach


                {{-- TOTAL --}}

                <col style="width:6%;">


                {{-- PERCENTAGE --}}

                <col style="width:4%;">


                {{-- GRADE --}}

                <col style="width:4%;">


                {{-- RESULT --}}

                <col style="width:5%;">

            </colgroup>


            <thead>

                <tr>

                    <th rowspan="2">
                        Roll No
                    </th>

                    <th rowspan="2">
                        Student Name
                    </th>


                    @foreach(
                        $academicDisplayColumns as $column
                    )

                        <th colspan="2">

                            <span class="subject-code">
                                {{ $column->subject_code ?? '' }}
                            </span>

                            <span class="subject-name">
                                {{ $column->subject_name ?? '-' }}
                            </span>

                            <span class="subject-max">
                                Max =
                                {{ (int)(
                                    $column->max_marks ?? 0
                                ) }}
                            </span>

                        </th>

                    @endforeach


                    <th rowspan="2">

                        Total

                        <span class="subject-max">
                            Max =
                            {{ (int)$displayTotalMaxMarks }}
                        </span>

                    </th>


                    <th rowspan="2">
                        %
                    </th>


                    <th rowspan="2">
                        Grade
                    </th>


                    <th rowspan="2">
                        Result
                    </th>

                </tr>


                <tr>

                    @foreach(
                        $academicDisplayColumns as $column
                    )

                        <th>
                            Marks
                        </th>

                        <th>
                            Grade
                        </th>

                    @endforeach

                </tr>

            </thead>


            <tbody>

            @foreach(
                $results as $student
            )

                <tr>

                    {{-- ROLL --}}

                    <td>
                        {{ $student->roll_no ?: '-' }}
                    </td>


                    {{-- STUDENT NAME --}}

                    <td
                        class="student-name-cell"
                        title="{{ $student->full_student_name ?? '' }}"
                    >
                        {{ $student->full_student_name ?? '-' }}
                    </td>


                    {{-- SUBJECTS --}}

                    @foreach(
                        $academicDisplayColumns as $column
                    )

                        @php

                            $subjectKey =
                                $column->key;

                            $mark =
                                $student->subject_marks[
                                    $subjectKey
                                ] ?? '-';

                            $grade =
                                $student->subject_grades[
                                    $subjectKey
                                ] ?? '-';

                        @endphp


                        {{-- MARK --}}

                        <td>

                            @if(
                                strtoupper(
                                    trim(
                                        (string)$mark
                                    )
                                ) === 'AB'
                            )

                                <span class="absent-mark">
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

                                {{ (int)$mark }}

                            @else

                                {{ $mark }}

                            @endif

                        </td>


                        {{-- GRADE --}}

                        <td>

                            @if(
                                strtoupper(
                                    trim(
                                        (string)$grade
                                    )
                                ) === 'AB'
                            )

                                <span class="absent-mark">
                                    AB
                                </span>

                            @elseif(
                                strtoupper(
                                    trim(
                                        (string)$grade
                                    )
                                ) === 'F'
                            )

                                <span class="fail-result">
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
                        {{ (int)round(
                            $student->academic_total ?? 0
                        ) }}
                    </td>


                    {{-- PERCENTAGE --}}

                    <td>

                        @if(
                            $student->calculated_percentage === null
                        )

                            -

                        @else

                            {{ (int)(
                                $student->calculated_percentage
                            ) }}%

                        @endif

                    </td>


                    {{-- OVERALL GRADE --}}

                    <td>

                        @php

                            $overallGrade =
                                strtoupper(
                                    trim(
                                        (string)(
                                            $student->calculated_grade
                                            ?? '-'
                                        )
                                    )
                                );

                        @endphp


                        @if(
                            $overallGrade === 'F'
                        )

                            <span class="fail-result">
                                F
                            </span>

                        @elseif(
                            $overallGrade === 'AB'
                        )

                            <span class="absent-mark">
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
                                            $student->result
                                            ?? '-'
                                        )
                                    )
                                );

                        @endphp


                        @if(
                            $studentResult === 'PASS'
                        )

                            <span class="pass-result">
                                PASS
                            </span>

                        @elseif(
                            $studentResult === 'FAIL'
                        )

                            <span class="fail-result">
                                FAIL
                            </span>

                        @elseif(
                            $studentResult === 'AB'
                        )

                            <span class="absent-mark">
                                AB
                            </span>

                        @else

                            -

                        @endif

                    </td>

                </tr>

            @endforeach

            </tbody>

        </table>

    </div>


    {{-- =========================================================
         OVERALL GRADE / RESULT ANALYSIS
         PENDING REMOVED
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

            @if(
                !empty(
                    $overallGradeAnalysis ?? []
                )
            )

                @foreach(
                    $overallGradeAnalysis as $grade => $row
                )

                    @php

                        $normalizedGrade =
                            strtoupper(
                                trim(
                                    (string)$grade
                                )
                            );

                    @endphp


                    @if(
                        $normalizedGrade !== 'PENDING'
                    )

                        <tr>

                            <td>
                                {{ $grade }}
                            </td>

                            <td>
                                {{ $row['range'] ?? '-' }}
                            </td>

                            <td>
                                {{ $row['girls'] ?? 0 }}
                            </td>

                            <td>
                                {{ $row['boys'] ?? 0 }}
                            </td>

                            <td>
                                {{ $row['total'] ?? 0 }}
                            </td>

                        </tr>

                    @endif

                @endforeach

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
         PENDING REMOVED
    ========================================================== --}}

    <div class="analysis-block">

        <div class="analysis-title">
            Subject Wise Analysis
        </div>


        @php

            $girlsBySubject =
                collect(
                    $girlsSubjectAnalysis ?? []
                )->keyBy(
                    'subject_code'
                );


            $boysBySubject =
                collect(
                    $boysSubjectAnalysis ?? []
                )->keyBy(
                    'subject_code'
                );


            /*
            |--------------------------------------------------------------------------
            | SAME SUBJECT ORDER AS MAIN TABLE
            |--------------------------------------------------------------------------
            */

            $analysisSubjects =
                $academicDisplayColumns
                    ->values();


            /*
            |--------------------------------------------------------------------------
            | NO PENDING
            |--------------------------------------------------------------------------
            */

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


        <div class="subject-wise-analysis-wrapper">

            <table class="subject-wise-analysis-table">

                <colgroup>

                    {{-- SUBJECT --}}

                    <col style="width:155px;">


                    {{-- NUMERIC COLUMNS --}}

                    @foreach(
                        $analysisCategories as $category
                    )

                        <col style="width:33px;">
                        <col style="width:33px;">

                    @endforeach


                    {{-- TOTAL --}}

                    <col style="width:40px;">

                </colgroup>


                <thead>

                    <tr>

                        <th rowspan="2">
                            Subject
                        </th>


                        @foreach(
                            $analysisCategories as $category
                        )

                            <th colspan="2">
                                {{ $category }}
                            </th>

                        @endforeach


                        <th rowspan="2">
                            Total
                        </th>

                    </tr>


                    <tr>

                        @foreach(
                            $analysisCategories as $category
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

                @foreach(
                    $analysisSubjects as $analysisSubject
                )

                    @php

                        $subjectCode =
                            trim(
                                (string)(
                                    $analysisSubject->subject_code
                                    ?? ''
                                )
                            );


                        $subjectFullName =
                            trim(
                                (string)(
                                    $analysisSubject->subject_name
                                    ?? ''
                                )
                            );


                        if (
                            $subjectFullName === ''
                        ) {

                            $subjectFullName =
                                $subjectCode ?: '-';
                        }


                        $defaultAnalysis = [

                            'A1' => 0,
                            'A2' => 0,
                            'B1' => 0,
                            'B2' => 0,
                            'C1' => 0,
                            'C2' => 0,
                            'D'  => 0,

                            'fail' =>
                                0,

                            'absent' =>
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


                        $rowTotal =
                            (int)$girls['total']
                            +
                            (int)$boys['total'];

                    @endphp


                    <tr>

                        {{-- SUBJECT NAME --}}

                        <td
                            class="subject-analysis-name"
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


                        {{-- TOTAL --}}

                        <td class="analysis-total-column">
                            {{ $rowTotal }}
                        </td>

                    </tr>

                @endforeach


                {{-- =====================================================
                     GRAND TOTAL
                ====================================================== --}}

                <tr
                    style="
                        font-weight:700;
                        background:#e5e7eb;
                    "
                >

                    <td class="subject-analysis-name">
                        TOTAL
                    </td>


                    @foreach(
                        $analysisCategories as $category
                    )

                        @php

                            $analysisKey =
                                $category === 'FAIL'
                                    ? 'fail'
                                    : (
                                        $category === 'ABSENT'
                                            ? 'absent'
                                            : $category
                                    );


                            $girlsTotal =
                                $girlsBySubject->sum(
                                    fn($row) =>
                                        (int)(
                                            $row[
                                                $analysisKey
                                            ]
                                            ?? 0
                                        )
                                );


                            $boysTotal =
                                $boysBySubject->sum(
                                    fn($row) =>
                                        (int)(
                                            $row[
                                                $analysisKey
                                            ]
                                            ?? 0
                                        )
                                );

                        @endphp


                        <td>
                            {{ $girlsTotal }}
                        </td>

                        <td>
                            {{ $boysTotal }}
                        </td>

                    @endforeach


                    <td class="analysis-total-column">

                        {{
                            $girlsBySubject->sum(
                                fn($row) =>
                                    (int)(
                                        $row['total']
                                        ?? 0
                                    )
                            )
                            +
                            $boysBySubject->sum(
                                fn($row) =>
                                    (int)(
                                        $row['total']
                                        ?? 0
                                    )
                            )
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

        <div class="signature-box">

            _______________________

            <br><br>

            Class Teacher

        </div>


        <div class="signature-box">

            _______________________

            <br><br>

            Principal

        </div>

    </div>


</div>


</body>

</html>