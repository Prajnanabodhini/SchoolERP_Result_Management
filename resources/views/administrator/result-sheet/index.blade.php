@extends('layouts.app')

<style>

/*
|--------------------------------------------------------------------------
| RESULT SHEET PAGE
|--------------------------------------------------------------------------
*/

.result-sheet-page,
.result-sheet-page * {
    box-sizing: border-box;
    font-family: Arial, Helvetica, sans-serif;
}


.result-sheet-page {
    width: 100%;

    /*
    | Approximately 2 cm top and left page spacing.
    */
    padding-top: 76px;
    padding-left: 76px;
    padding-right: 20px;
    padding-bottom: 20px;
}


/*
|--------------------------------------------------------------------------
| FILTER
|--------------------------------------------------------------------------
*/

.result-filter-wrapper {
    width: 100%;
}

.result-filter {
    width: 100%;
}

.result-filter-row {
    width: 100%;

    display: flex;

    align-items: center;

    flex-wrap: nowrap;

    gap: 7px;
}

.result-filter-label {
    flex: 0 0 auto;

    font-size: 13px;

    font-weight: 700;

    color: #374151;

    white-space: nowrap;
}

.result-filter select {
    height: 35px;

    padding:
        4px
        26px
        4px
        8px;

    font-size: 13px;

    line-height: 24px;

    color: #111827;

    background: #ffffff;

    border: 1px solid #9CA3AF;

    border-radius: 5px;

    cursor: pointer;

    font-weight: 600;

    min-width: 0;
}


.result-filter .year-select {
    width: 125px;
}


.result-filter .exam-select {
    width: 210px;
}


.result-filter .division-select {
    width: 75px;
}


.result-filter-button {
    height: 35px;

    padding:
        0
        12px;

    white-space: nowrap;

    font-size: 13px !important;

    font-weight: 700 !important;
}


/*
|--------------------------------------------------------------------------
| STUDENT COUNT
|--------------------------------------------------------------------------
*/

.result-student-row {
    display: flex;

    align-items: center;
}


.result-student-count {
    display: inline-flex;

    align-items: center;

    justify-content: center;

    height: 35px;

    padding:
        0
        10px;

    font-size: 13px;

    font-weight: 700;

    color: #1D4ED8;

    background: #DBEAFE;

    border: 1px solid #BFDBFE;

    border-radius: 5px;

    white-space: nowrap;
}


/*
|--------------------------------------------------------------------------
| RESULT INFORMATION
|--------------------------------------------------------------------------
*/

.result-information-wrapper {
    width: 100%;

    margin-bottom: 15px;
}


.result-information-grid {
    width: 100%;

    display: grid;

    grid-template-columns:
        repeat(
            4,
            minmax(0, 1fr)
        );

    gap: 8px;
}


.result-information-item {
    border:
        1px solid #D1D5DB;

    border-radius: 5px;

    background: #F9FAFB;

    padding:
        9px
        11px;

    min-width: 0;
}


.result-information-label {
    display: block;

    font-size: 11px;

    color: #6B7280;

    font-weight: 700;

    margin-bottom: 3px;
}


.result-information-value {
    display: block;

    font-size: 14px;

    color: #111827;

    font-weight: 700;

    white-space: nowrap;

    overflow: visible;
}


/*
|--------------------------------------------------------------------------
| STAFF NAME
|--------------------------------------------------------------------------
*/

.staff-name {
    font-weight: 700;
}


/*
|--------------------------------------------------------------------------
| MAIN RESULT TABLE
|--------------------------------------------------------------------------
|
| No fixed widths.
| No colgroup.
| Browser automatically sizes every column.
|--------------------------------------------------------------------------
*/

.result-sheet-wrapper {
    width: 100%;

    overflow: visible;

    margin-top: 0;
}


.result-sheet-table {
    width: 100%;

    max-width: 100%;

    border-collapse: collapse;

    border-spacing: 0;

    table-layout: auto;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size: 11px;

    border:
        1px solid #333;
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
        5px
        4px;
}


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

.result-sheet-table th {

    background:
        #dbeafe;

    font-size:
        11px;

    font-weight:
        700 !important;

    line-height:
        1.1;

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
        10.5px;

    line-height:
        1.15;

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
|
| Bold.
| No hiding.
| No ellipsis.
| No fixed width.
|--------------------------------------------------------------------------
*/

.result-sheet-table th:nth-child(2),
.result-sheet-table td:nth-child(2) {

    width:
        auto;

    min-width:
        max-content;
}


.student-name {

    text-align:
        left !important;

    white-space:
        nowrap !important;

    font-weight:
        700 !important;

    color:
        #111827 !important;

    overflow:
        visible !important;

    text-overflow:
        clip !important;
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
        9.5px;

    font-weight:
        700 !important;

    line-height:
        10px;

    white-space:
        nowrap;
}


.subject-max {

    display:
        block;

    font-size:
        8px;

    font-weight:
        700 !important;

    line-height:
        9px;

    white-space:
        nowrap;
}


/*
|--------------------------------------------------------------------------
| SUBJECT CELLS
|--------------------------------------------------------------------------
*/

.result-sheet-table th[colspan="2"] {

    width:
        auto;

    white-space:
        nowrap;
}


.result-sheet-table td {

    overflow:
        visible !important;

    text-overflow:
        clip !important;
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


.pending-result {

    color:
        #92400e !important;

    font-weight:
        700 !important;
}


/*
|--------------------------------------------------------------------------
| OVERALL ANALYSIS
|--------------------------------------------------------------------------
*/

.analysis-title {

    margin-top:
        22px;

    margin-bottom:
        9px;

    font-size:
        17px;

    font-weight:
        700 !important;

    color:
        #1d4ed8;
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

    border-collapse:
        collapse;

    table-layout:
        auto;

    font-size:
        11px;

    border:
        1px solid #333;
}


.analysis-table th,
.analysis-table td {

    border:
        1px solid #333 !important;

    padding:
        6px
        5px;

    text-align:
        center;

    vertical-align:
        middle;

    font-size:
        11px;

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
        left;

    font-weight:
        700 !important;
}


/*
|--------------------------------------------------------------------------
| OVERALL TOTAL
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

    border-collapse:
        collapse;

    table-layout:
        auto;

    font-size:
        11px;

    border:
        1px solid #333;
}


.subject-wise-analysis-table th,
.subject-wise-analysis-table td {

    border:
        1px solid #333 !important;

    padding:
        6px
        4px;

    text-align:
        center;

    vertical-align:
        middle;

    font-size:
        11px;

    white-space:
        nowrap;
}


.subject-wise-analysis-table th {

    background:
        #dbeafe;

    font-weight:
        700 !important;
}


/*
|--------------------------------------------------------------------------
| SUBJECT NAME COLUMN
|--------------------------------------------------------------------------
*/

.subject-wise-analysis-table
.subject-analysis-name {

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

    width:
        auto;

    min-width:
        max-content;
}


/*
|--------------------------------------------------------------------------
| RESPONSIVE
|--------------------------------------------------------------------------
*/

@media (max-width: 1200px) {

    .result-sheet-page {

        padding-top:
            60px;

        padding-left:
            55px;

        padding-right:
            15px;
    }

    .result-information-grid {

        grid-template-columns:
            repeat(
                2,
                minmax(0, 1fr)
            );
    }

    .result-sheet-table {

        font-size:
            10px;
    }

    .result-sheet-table th {

        font-size:
            10px;
    }

    .result-sheet-table td {

        font-size:
            9.5px;
    }
}


@media (max-width: 900px) {

    .result-sheet-page {

        padding-top:
            45px;

        padding-left:
            30px;

        padding-right:
            10px;
    }

    .result-filter-row {

        flex-wrap:
            wrap;
    }

    .result-information-grid {

        grid-template-columns:
            repeat(
                2,
                minmax(0, 1fr)
            );
    }

    .result-sheet-table {

        font-size:
            9px;
    }

    .result-sheet-table th {

        font-size:
            9px;
    }

    .result-sheet-table td {

        font-size:
            8.5px;
    }

    .subject-wise-analysis-table {

        font-size:
            9px;
    }

    .subject-wise-analysis-table th,
    .subject-wise-analysis-table td {

        font-size:
            9px;

        padding:
            4px
            3px;
    }
}


@media (max-width: 700px) {

    .result-sheet-page {

        padding-top:
            25px;

        padding-left:
            15px;

        padding-right:
            8px;
    }

    .result-filter-row {

        display:
            grid;

        grid-template-columns:
            auto
            minmax(
                100px,
                1fr
            );

        gap:
            7px;
    }

    .result-filter .year-select,
    .result-filter .exam-select,
    .result-filter .division-select {

        width:
            100%;
    }

    .result-information-grid {

        grid-template-columns:
            1fr;
    }

    .result-sheet-table {

        font-size:
            8px;
    }

    .result-sheet-table th {

        font-size:
            8px;

        padding:
            3px
            2px;
    }

    .result-sheet-table td {

        font-size:
            7.5px;

        padding:
            3px
            2px;
    }

    .subject-name {

        font-size:
            7px;
    }

    .subject-max {

        font-size:
            6px;
    }

    .subject-wise-analysis-table,
    .subject-wise-analysis-table th,
    .subject-wise-analysis-table td {

        font-size:
            7px;

        padding:
            3px
            2px;
    }
}

</style>


@section('content')

<div class="erp-page result-sheet-page">


    {{-- =========================================================
         FILTER
    ========================================================== --}}

    <div class="erp-card no-print">

        <h2 style="
            font-size:21px;
            font-weight:700;
            color:#1d4ed8;
            margin:0 0 13px 0;
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


                        {{-- ACADEMIC YEAR --}}

                        <label class="result-filter-label">
                            Academic Year
                        </label>

                        <select
                            name="academic_year_id"
                            class="year-select"
                            required
                        >

                            <option value="">
                                Select
                            </option>

                            @foreach($academicYears as $year)

                                <option
                                    value="{{ $year->id }}"
                                    {{
                                        (string)request(
                                            'academic_year_id'
                                        )
                                        ===
                                        (string)$year->id
                                            ? 'selected'
                                            : ''
                                    }}
                                >

                                    {{ $year->year_name }}

                                </option>

                            @endforeach

                        </select>


                        {{-- EXAM --}}

                        <label class="result-filter-label">
                            Exam
                        </label>

                        <select
                            name="exam_master_id"
                            class="exam-select"
                            required
                        >

                            <option value="">
                                Select
                            </option>

                            @foreach($exams as $examItem)

                                <option
                                    value="{{ $examItem->id }}"
                                    {{
                                        (string)request(
                                            'exam_master_id'
                                        )
                                        ===
                                        (string)$examItem->id
                                            ? 'selected'
                                            : ''
                                    }}
                                >

                                    {{
                                        $examItem->display_exam_name
                                        ?? $examItem->exam_name
                                    }}

                                </option>

                            @endforeach

                        </select>


                        {{-- DIVISION --}}

                        <label class="result-filter-label">
                            Division
                        </label>

                        <select
                            name="division_id"
                            class="division-select"
                            required
                        >

                            <option value="">
                                Select
                            </option>

                            @foreach($divisions as $divisionItem)

                                <option
                                    value="{{ $divisionItem->id }}"
                                    {{
                                        (string)request(
                                            'division_id'
                                        )
                                        ===
                                        (string)$divisionItem->id
                                            ? 'selected'
                                            : ''
                                    }}
                                >

                                    {{ $divisionItem->division_name }}

                                </option>

                            @endforeach

                        </select>


                        {{-- GENERATE --}}

                        <button
                            type="submit"
                            class="erp-btn erp-btn-save result-filter-button"
                        >
                            Generate
                        </button>


                        {{-- PRINT --}}

                        @if($results->count() > 0)

                            <a
                                href="{{ route('result-sheet.print', [
                                    'academic_year_id' =>
                                        request(
                                            'academic_year_id'
                                        ),

                                    'exam_master_id' =>
                                        request(
                                            'exam_master_id'
                                        ),

                                    'standard_id' =>
                                        $standard->id
                                        ?? '',

                                    'division_id' =>
                                        request(
                                            'division_id'
                                        ),
                                ]) }}"
                                target="_blank"
                                class="erp-btn erp-btn-save result-filter-button"
                            >
                                Print
                            </a>

                        @endif


                        {{-- STUDENT COUNT --}}

                        @if($results->count() > 0)

                            <div class="result-student-row">

                                <span class="result-student-count">

                                    Students :
                                    {{ $results->count() }}

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
                font-weight:700;
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
                font-weight:700;
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
                | DISPLAY SUBJECTS
                |--------------------------------------------------------------------------
                */

                $academicDisplayColumns =
                    collect(
                        $displayColumns
                        ?? []
                    )->values();


                /*
                |--------------------------------------------------------------------------
                | SORT STUDENTS
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
                | STAFF NAME
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


                        return ucwords(
                            strtolower(
                                trim($name)
                            )
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
                 INFORMATION
            ========================================================== --}}

            <div class="result-information-wrapper">

                <div class="result-information-grid">


                    <div class="result-information-item">

                        <span class="result-information-label">
                            Exam
                        </span>

                        <span class="result-information-value">

                            {{
                                $exam->display_exam_name
                                ?? $exam->exam_name
                                ?? '-'
                            }}

                        </span>

                    </div>


                    <div class="result-information-item">

                        <span class="result-information-label">
                            Standard
                        </span>

                        <span class="result-information-value">

                            {{
                                $standard->standard_name
                                ?? '-'
                            }}

                        </span>

                    </div>


                    <div class="result-information-item">

                        <span class="result-information-label">
                            Division
                        </span>

                        <span class="result-information-value">

                            {{
                                $division->division_name
                                ?? '-'
                            }}

                        </span>

                    </div>


                    <div class="result-information-item">

                        <span class="result-information-label">
                            Class Teacher
                        </span>

                        <span class="result-information-value">

                            {{
                                $classTeacherName
                                ?: '-'
                            }}

                        </span>

                    </div>


                    <div class="result-information-item">

                        <span class="result-information-label">
                            Principal
                        </span>

                        <span class="result-information-value">

                            {{
                                $principalName
                                ?: '-'
                            }}

                        </span>

                    </div>


                    <div class="result-information-item">

                        <span class="result-information-label">
                            Total Maximum Marks
                        </span>

                        <span class="result-information-value">

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

                    </div>


                    <div class="result-information-item">

                        <span class="result-information-label">
                            Overall Pass %
                        </span>

                        <span class="result-information-value">

                            {{
                                $passPercentage
                                ?? 40
                            }}%

                        </span>

                    </div>


                    <div class="result-information-item">

                        <span class="result-information-label">
                            Total Students
                        </span>

                        <span class="result-information-value">

                            {{ $results->count() }}

                        </span>

                    </div>

                </div>

            </div>


            {{-- =========================================================
                 MAIN TABLE
            ========================================================== --}}

            <div class="result-sheet-wrapper">

                <table class="result-sheet-table">

                    <thead>

                        <tr>

                            <th>
                                Roll No
                            </th>


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


                            <th>
                                Per. %
                            </th>


                            <th>
                                Grade
                            </th>


                            <th>
                                Result
                            </th>

                        </tr>


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

                    @foreach(
                        $sortedResults as $student
                    )

                        <tr>

                            {{-- ROLL --}}

                            <td>

                                {{
                                    $student->roll_no
                                    ?: '-'
                                }}

                            </td>


                            {{-- STUDENT NAME --}}

                            <td
                                class="student-name"
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


                            {{-- GRADE --}}

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
                                    $studentResult === 'PENDING'
                                )

                                    <span
                                        class="pending-result"
                                    >
                                        PENDING
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

                    @endforeach

                    </tbody>

                </table>

            </div>


            {{-- =========================================================
                 OVERALL ANALYSIS
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
                            class="subject-analysis-name"
                        >
                            TOTAL
                        </td>


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

    @endif

</div>

@endsection