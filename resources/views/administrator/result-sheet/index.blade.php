@extends('layouts.app')

<style>

/*
|--------------------------------------------------------------------------
| FILTER
|--------------------------------------------------------------------------
*/

.result-filter-wrapper {
    width: 100%;
    overflow: hidden;
}

.result-filter {
    width: 100%;
    box-sizing: border-box;
}

.result-filter-row {
    width: 100%;
    display: flex;
    align-items: center;
    flex-wrap: nowrap;
    gap: 7px;
    box-sizing: border-box;
}

.result-filter-label {
    flex: 0 0 auto;
    font-size: 12px;
    font-weight: 600;
    color: #374151;
    white-space: nowrap;
    margin: 0;
}

.result-filter select {
    height: 34px;
    min-height: 34px;
    padding: 4px 26px 4px 7px;
    font-size: 12px;
    line-height: 24px;
    color: #111827;
    background-color: #ffffff;
    border: 1px solid #9CA3AF;
    border-radius: 5px;
    box-sizing: border-box;
    min-width: 0;
    cursor: pointer;
}


/*
|--------------------------------------------------------------------------
| ACADEMIC YEAR
|--------------------------------------------------------------------------
*/

.result-filter .year-select {
    flex: 0 0 115px;
    width: 115px;
    min-width: 115px;
}


/*
|--------------------------------------------------------------------------
| EXAM
|--------------------------------------------------------------------------
*/

.result-filter .exam-select {
    flex: 0 0 190px;
    width: 190px;
    min-width: 190px;
}


/*
|--------------------------------------------------------------------------
| DIVISION
|--------------------------------------------------------------------------
*/

.result-filter .division-select {
    flex: 0 0 70px;
    width: 70px;
    min-width: 70px;
}


/*
|--------------------------------------------------------------------------
| BUTTONS
|--------------------------------------------------------------------------
*/

.result-filter-button {
    flex: 0 0 auto;
    height: 34px;
    padding-left: 10px;
    padding-right: 10px;
    white-space: nowrap;
}


/*
|--------------------------------------------------------------------------
| STUDENT COUNT
|--------------------------------------------------------------------------
*/

.result-student-row {
    display: flex;
    align-items: center;
    flex: 0 0 auto;
    margin: 0;
}

.result-student-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 34px;
    padding: 0 9px;
    font-size: 12px;
    font-weight: 700;
    color: #1D4ED8;
    background: #DBEAFE;
    border: 1px solid #BFDBFE;
    border-radius: 5px;
    white-space: nowrap;
    box-sizing: border-box;
}


/*
|--------------------------------------------------------------------------
| MAIN RESULT TABLE
|--------------------------------------------------------------------------
*/

.result-sheet-wrapper {
    width: 100%;
    overflow: hidden;
}

.result-sheet-table {
    width: 100%;
    border-collapse: collapse;
    border-spacing: 0;
    table-layout: fixed;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 10px;
    border: 1px solid #333;
}

.result-sheet-table th,
.result-sheet-table td {
    border: 1px solid #333 !important;
    border-style: solid !important;
    border-width: 1px !important;
    text-align: center;
    vertical-align: middle;
    padding: 4px 2px;
}

.result-sheet-table th {
    background: #dbeafe;
    font-weight: 700;
    line-height: 1.15;
    white-space: normal;
    overflow-wrap: break-word;
}

.result-sheet-table td {
    line-height: 1.2;
    white-space: nowrap;
}


/*
|--------------------------------------------------------------------------
| SUBJECT HEADER
|--------------------------------------------------------------------------
*/

.subject-code {
    display: block;
    font-size: 9px;
    font-weight: 700;
}

.subject-name {
    display: block;
    font-size: 8px;
    font-weight: 500;
}

.subject-max {
    display: block;
    font-size: 7px;
    font-weight: 400;
}


/*
|--------------------------------------------------------------------------
| STUDENT NAME
|--------------------------------------------------------------------------
*/

.student-name {
    text-align: left !important;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}


/*
|--------------------------------------------------------------------------
| STATUS
|--------------------------------------------------------------------------
*/

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

.pending-result {
    color: #92400e !important;
    font-weight: 700;
}


/*
|--------------------------------------------------------------------------
| ANALYSIS
|--------------------------------------------------------------------------
*/

.analysis-title {
    margin-top: 20px;
    margin-bottom: 8px;
    font-size: 15px;
    font-weight: 700;
    color: #1d4ed8;
}

.analysis-table-wrapper {
    width: 100%;
    overflow-x: auto;
}

.analysis-table {
    width: 100%;
    min-width: 1100px;
    border-collapse: collapse;
    table-layout: fixed;
    font-size: 9px;
    border: 1px solid #333;
}

.analysis-table th,
.analysis-table td {
    border: 1px solid #333 !important;
    border-style: solid !important;
    border-width: 1px !important;
    padding: 4px 3px;
    text-align: center;
    vertical-align: middle;
}

.analysis-table th {
    background: #dbeafe;
    font-weight: 700;
}

.analysis-table td:first-child {
    text-align: left;
    font-weight: 600;
}


/*
|--------------------------------------------------------------------------
| ANALYSIS TOTAL ROW
|--------------------------------------------------------------------------
*/

.analysis-total-row {
    font-weight: 700 !important;
    background: #e5e7eb !important;
}

.analysis-total-row td {
    font-weight: 700 !important;
    background: #e5e7eb !important;
}


/*
|--------------------------------------------------------------------------
| SUBJECT WISE ANALYSIS
|--------------------------------------------------------------------------
*/

.subject-wise-analysis-wrapper {
    width: 100%;
    overflow-x: auto;
}

.subject-wise-analysis-table {
    width: 100%;
    min-width: 1180px;
    border-collapse: collapse;
    table-layout: fixed;
    font-size: 9px;
    border: 1px solid #333;
}

.subject-wise-analysis-table th,
.subject-wise-analysis-table td {
    border: 1px solid #333 !important;
    border-style: solid !important;
    border-width: 1px !important;
    padding: 4px 3px;
    text-align: center;
    vertical-align: middle;
}

.subject-wise-analysis-table th {
    background: #dbeafe;
    font-weight: 700;
    white-space: nowrap;
}

.subject-wise-analysis-table .subject-analysis-name {
    width: 230px;
    min-width: 230px;
    max-width: 230px;
    text-align: left !important;
    font-weight: 700;
    white-space: normal !important;
    word-break: normal;
    overflow-wrap: break-word;
}

.subject-wise-analysis-table .analysis-number-column {
    width: 48px;
    min-width: 48px;
    max-width: 48px;
}

.subject-wise-analysis-table .analysis-total-column {
    width: 58px;
    min-width: 58px;
    max-width: 58px;
    font-weight: 700;
}


/*
|--------------------------------------------------------------------------
| RESPONSIVE FILTER
|--------------------------------------------------------------------------
*/

@media (max-width: 1050px) {

    .result-filter-row {
        flex-wrap: wrap;
    }
}

@media (max-width: 700px) {

    .result-filter-row {
        display: grid;
        grid-template-columns: auto minmax(100px, 1fr);
        gap: 7px;
    }

    .result-filter .year-select,
    .result-filter .exam-select,
    .result-filter .division-select {
        width: 100%;
        min-width: 0;
    }

    .result-filter-button {
        width: auto;
    }

    .result-student-row {
        width: 100%;
    }
}

</style>


@section('content')

<div class="erp-card no-print">

    <h2 style="
        font-size:20px;
        font-weight:700;
        color:#1d4ed8;
        margin:0 0 12px 0;
    ">
        EXAMINATION RESULT SHEET
    </h2>


    <div class="result-filter-wrapper">

        <form
            method="POST"
            action="{{ route('result-sheet.search') }}"
        >

            @csrf

            <div class="result-filter">

                <div class="result-filter-row">

                    {{-- =====================================================
                         ACADEMIC YEAR
                    ====================================================== --}}

                    <label class="result-filter-label">
                        Academic Year
                    </label>

                    <select
                        name="academic_year_id"
                        class="year-select"
                        required
                        title="Academic Year"
                    >

                        <option value="">
                            Select
                        </option>

                        @foreach($academicYears as $year)

                            <option
                                value="{{ $year->id }}"
                                {{ (string)request('academic_year_id') === (string)$year->id ? 'selected' : '' }}
                            >
                                {{ $year->year_name }}
                            </option>

                        @endforeach

                    </select>


                    {{-- =====================================================
                         EXAM
                    ====================================================== --}}

                    <label class="result-filter-label">
                        Exam
                    </label>

                    <select
                        name="exam_master_id"
                        class="exam-select"
                        required
                        title="Exam"
                    >

                        <option value="">
                            Select
                        </option>

                        @foreach($exams as $examItem)

                            <option
                                value="{{ $examItem->id }}"
                                {{ (string)request('exam_master_id') === (string)$examItem->id ? 'selected' : '' }}
                            >
                                {{
                                    $examItem->display_exam_name
                                    ?? $examItem->exam_name
                                }}
                            </option>

                        @endforeach

                    </select>


                    {{-- =====================================================
                         DIVISION
                    ====================================================== --}}

                    <label class="result-filter-label">
                        Division
                    </label>

                    <select
                        name="division_id"
                        class="division-select"
                        required
                        title="Division"
                    >

                        <option value="">
                            Select
                        </option>

                        @foreach($divisions as $divisionItem)

                            <option
                                value="{{ $divisionItem->id }}"
                                {{ (string)request('division_id') === (string)$divisionItem->id ? 'selected' : '' }}
                            >
                                {{ $divisionItem->division_name }}
                            </option>

                        @endforeach

                    </select>


                    {{-- =====================================================
                         GENERATE
                    ====================================================== --}}

                    <button
                        type="submit"
                        class="erp-btn erp-btn-save result-filter-button"
                    >
                        Generate
                    </button>


                    {{-- =====================================================
                         PRINT
                    ====================================================== --}}

                    @if($results->count() > 0)

                        <a
                            href="{{ route('result-sheet.print', [
                                'academic_year_id' => request('academic_year_id'),
                                'exam_master_id'   => request('exam_master_id'),
                                'standard_id'      => $standard->id ?? '',
                                'division_id'      => request('division_id'),
                            ]) }}"
                            target="_blank"
                            class="erp-btn erp-btn-save result-filter-button"
                        >
                            Print
                        </a>

                    @endif


                    {{-- =====================================================
                         STUDENT COUNT
                    ====================================================== --}}

                    @if($results->count() > 0)

                        <div class="result-student-row">

                            <span class="result-student-count">
                                Students : {{ $results->count() }}
                            </span>

                        </div>

                    @endif

                </div>

            </div>

        </form>

    </div>

</div>


{{-- =========================================================
     ERROR
========================================================== --}}

@if(session('error'))

    <div
        class="erp-card mt-3"
        style="
            color:#991b1b;
            background:#fee2e2;
            border:1px solid #fecaca;
            font-weight:600;
        "
    >
        {{ session('error') }}
    </div>

@endif


{{-- =========================================================
     SUCCESS
========================================================== --}}

@if(session('success'))

    <div
        class="erp-card mt-3"
        style="
            color:#166534;
            background:#dcfce7;
            border:1px solid #bbf7d0;
            font-weight:600;
        "
    >
        {{ session('success') }}
    </div>

@endif


{{-- =========================================================
     RESULT
========================================================== --}}

@if($results->count() > 0)

<div class="erp-card mt-4">

    @php

        /*
        |--------------------------------------------------------------------------
        | DISPLAY COLUMNS
        |--------------------------------------------------------------------------
        */

        $academicDisplayColumns =
            collect(
                $displayColumns ?? []
            )->values();


        /*
        |--------------------------------------------------------------------------
        | SORT STUDENTS BY ROLL NUMBER
        |--------------------------------------------------------------------------
        */

        $sortedResults =
            collect($results)
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


        /*
        |--------------------------------------------------------------------------
        | TABLE WIDTH
        |--------------------------------------------------------------------------
        */

        $subjectCount =
            $academicDisplayColumns->count();


        $subjectArea =
            61;


        $eachSubjectWidth =
            $subjectCount > 0
                ? $subjectArea / $subjectCount
                : 5;


        $eachSubColumnWidth =
            $eachSubjectWidth / 2;


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

                <col style="width:5%;">

                <col style="width:15%;">


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


                <col style="width:6%;">

                <col style="width:4%;">

                <col style="width:4%;">

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

                        @php

                            $subjectMax =
                                (float)(
                                    $column->max_marks
                                    ?? 0
                                );


                            $subjectMaxDisplay =
                                floor($subjectMax) === $subjectMax

                                    ? (int)$subjectMax

                                    : number_format(
                                        $subjectMax,
                                        2
                                    );

                        @endphp


                        <th colspan="2">

                            <span class="subject-code">
                                {{
                                    $column->subject_code
                                    ?? ''
                                }}
                            </span>

                            <span class="subject-name">
                                {{
                                    $column->subject_name
                                    ?? '-'
                                }}
                            </span>

                            <span class="subject-max">
                                Max =
                                {{ $subjectMaxDisplay }}
                            </span>

                        </th>

                    @endforeach


                    <th rowspan="2">

                        Total

                        <span class="subject-max">

                            Max =
                            {{
                                floor(
                                    $displayTotalMaxMarks
                                ) ===
                                $displayTotalMaxMarks

                                    ? (int)$displayTotalMaxMarks

                                    : number_format(
                                        $displayTotalMaxMarks,
                                        2
                                    )
                            }}

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

            {{-- =====================================================
                 STUDENTS SORTED BY ROLL NUMBER
            ====================================================== --}}

            @foreach(
                $sortedResults as $student
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
                        class="student-name"
                        title="{{ $student->full_student_name ?? '' }}"
                    >
                        {{
                            $student->full_student_name
                            ?? '-'
                        }}
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

                                {{
                                    floor(
                                        (float)$mark
                                    ) ===
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


                        {{-- SUBJECT GRADE --}}

                        <td>

                            @if(
                                $gradeText === 'AB'
                            )

                                <span class="absent-mark">
                                    AB
                                </span>

                            @elseif(
                                $gradeText === 'F'
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

                        @php

                            $academicTotal =
                                $student->academic_total
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
                            is_numeric($academicTotal)
                        )

                            {{
                                floor(
                                    (float)$academicTotal
                                ) ===
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


                    {{-- PERCENTAGE --}}

                    <td>

                        @if(
                            $student->calculated_percentage === null
                        )

                            -

                        @else

                            {{
                                (int)(
                                    $student->calculated_percentage
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
                            $studentResult === 'PENDING'
                        )

                            <span class="pending-result">
                                PENDING
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
         FIXED ORDER + TOTAL AFTER FAIL
    ========================================================== --}}

    <div class="analysis-title">
        Overall Grade / Result Analysis
    </div>


    <div class="analysis-table-wrapper">

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

                /*
                |--------------------------------------------------------------------------
                | FIXED DISPLAY ORDER
                |--------------------------------------------------------------------------
                |
                | TOTAL IS NOT ALLOWED TO APPEAR BEFORE FAIL.
                |
                */

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
                    $overallGradeAnalysis ?? []
                )
            )

                {{-- =================================================
                     GRADES + PASS + FAIL
                ================================================== --}}

                @foreach(
                    $overallOrder as $grade
                )

                    @php

                        $row =
                            $overallGradeAnalysis[$grade]
                            ??
                            [
                                'range' => '-',
                                'girls' => 0,
                                'boys' => 0,
                                'total' => 0,
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


                {{-- =================================================
                     TOTAL
                     ALWAYS AFTER FAIL
                ================================================== --}}

                @php

                    $totalRow =
                        $overallGradeAnalysis['TOTAL']
                        ??
                        [
                            'range' => 'TOTAL',

                            'girls' =>
                                (
                                    $overallGradeAnalysis['PASS']['girls']
                                    ?? 0
                                )
                                +
                                (
                                    $overallGradeAnalysis['FAIL']['girls']
                                    ?? 0
                                ),

                            'boys' =>
                                (
                                    $overallGradeAnalysis['PASS']['boys']
                                    ?? 0
                                )
                                +
                                (
                                    $overallGradeAnalysis['FAIL']['boys']
                                    ?? 0
                                ),

                            'total' =>
                                (
                                    $overallGradeAnalysis['PASS']['total']
                                    ?? 0
                                )
                                +
                                (
                                    $overallGradeAnalysis['FAIL']['total']
                                    ?? 0
                                ),
                        ];

                @endphp


                <tr class="analysis-total-row">

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

    <div class="analysis-title">
        Subject Wise Analysis
    </div>


    @php

        /*
        |--------------------------------------------------------------------------
        | SUBJECT ANALYSIS COLLECTIONS
        |--------------------------------------------------------------------------
        */

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
        | SAME SUBJECT ORDER AS RESULT TABLE
        |--------------------------------------------------------------------------
        */

        $analysisSubjects =
            $academicDisplayColumns
                ->values();


        /*
        |--------------------------------------------------------------------------
        | SUBJECT ANALYSIS CATEGORIES
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

                <col style="width:230px;">


                @foreach(
                    $analysisCategories as $category
                )

                    <col style="width:48px;">

                    <col style="width:48px;">

                @endforeach


                <col style="width:58px;">

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

            {{-- =====================================================
                 SUBJECT ROWS
            ====================================================== --}}

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


                    $rowTotal =
                        (int)$girls['total']
                        +
                        (int)$boys['total'];

                @endphp


                <tr>

                    <td
                        class="subject-analysis-name"
                        title="{{ $subjectFullName }}"
                    >
                        {{ $subjectFullName }}
                    </td>


                    {{-- =================================================
                         A1
                    ================================================== --}}

                    <td>
                        {{ $girls['A1'] }}
                    </td>

                    <td>
                        {{ $boys['A1'] }}
                    </td>


                    {{-- =================================================
                         A2
                    ================================================== --}}

                    <td>
                        {{ $girls['A2'] }}
                    </td>

                    <td>
                        {{ $boys['A2'] }}
                    </td>


                    {{-- =================================================
                         B1
                    ================================================== --}}

                    <td>
                        {{ $girls['B1'] }}
                    </td>

                    <td>
                        {{ $boys['B1'] }}
                    </td>


                    {{-- =================================================
                         B2
                    ================================================== --}}

                    <td>
                        {{ $girls['B2'] }}
                    </td>

                    <td>
                        {{ $boys['B2'] }}
                    </td>


                    {{-- =================================================
                         C1
                    ================================================== --}}

                    <td>
                        {{ $girls['C1'] }}
                    </td>

                    <td>
                        {{ $boys['C1'] }}
                    </td>


                    {{-- =================================================
                         C2
                    ================================================== --}}

                    <td>
                        {{ $girls['C2'] }}
                    </td>

                    <td>
                        {{ $boys['C2'] }}
                    </td>


                    {{-- =================================================
                         D
                    ================================================== --}}

                    <td>
                        {{ $girls['D'] }}
                    </td>

                    <td>
                        {{ $boys['D'] }}
                    </td>


                    {{-- =================================================
                         FAIL
                    ================================================== --}}

                    <td>
                        {{ $girls['fail'] }}
                    </td>

                    <td>
                        {{ $boys['fail'] }}
                    </td>


                    {{-- =================================================
                         ABSENT
                    ================================================== --}}

                    <td>
                        {{ $girls['absent'] }}
                    </td>

                    <td>
                        {{ $boys['absent'] }}
                    </td>


                    {{-- =================================================
                         TOTAL
                    ================================================== --}}

                    <td class="analysis-total-column">
                        {{ $rowTotal }}
                    </td>

                </tr>

            @endforeach


            {{-- =====================================================
                 SUBJECT ANALYSIS GRAND TOTAL
            ====================================================== --}}

            @php

                /*
                |--------------------------------------------------------------------------
                | GRAND CATEGORY TOTALS
                |--------------------------------------------------------------------------
                */

                $grandSubjectGirlsTotal =
                    $girlsBySubject->sum(
                        fn($row) =>
                            (int)(
                                $row['total']
                                ?? 0
                            )
                    );


                $grandSubjectBoysTotal =
                    $boysBySubject->sum(
                        fn($row) =>
                            (int)(
                                $row['total']
                                ?? 0
                            )
                    );

            @endphp


            <tr
                class="analysis-total-row"
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
                        $grandSubjectGirlsTotal
                        +
                        $grandSubjectBoysTotal
                    }}

                </td>

            </tr>

            </tbody>

        </table>

    </div>

</div>

@endif

@endsection