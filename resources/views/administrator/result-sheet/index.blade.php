@extends('layouts.app')

<style>
/* ==========================================================================
   RESULT SHEET PAGE
   ========================================================================== */

.result-sheet-page,
.result-sheet-page * {
    box-sizing: border-box;
    font-family: Arial, Helvetica, sans-serif;
}

.result-sheet-page {
    width: 100%;
    padding-top: 76px;
    padding-left: 76px;
    padding-right: 20px;
    padding-bottom: 20px;
}

/* ==========================================================================
   FILTER
   ========================================================================== */

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
    padding: 4px 26px 4px 8px;
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

.result-filter-select-wrapper {
    position: relative;
    display: inline-block;
}

.result-filter-select-wrapper select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    padding-right: 30px;
}

.result-filter-arrow {
    position: absolute;
    top: 50%;
    right: 10px;
    width: 0;
    height: 0;
    transform: translateY(-35%);
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 6px solid #374151;
    pointer-events: none;
}

.result-filter-select-wrapper select:focus {
    outline: none;
    border-color: #2563EB;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12);
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
    padding: 0 12px;
    white-space: nowrap;
    font-size: 13px !important;
    font-weight: 700 !important;
}

/* ==========================================================================
   STUDENT COUNT
   ========================================================================== */

.result-student-row {
    display: flex;
    align-items: center;
}

.result-student-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 35px;
    padding: 0 10px;
    font-size: 13px;
    font-weight: 700;
    color: #1D4ED8;
    background: #DBEAFE;
    border: 1px solid #BFDBFE;
    border-radius: 5px;
    white-space: nowrap;
}

/* ==========================================================================
   RESULT INFORMATION
   ========================================================================== */

.result-information-wrapper {
    width: 100%;
    margin-bottom: 15px;
}

.result-information-grid {
    width: 100%;
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 8px;
}

.result-information-item {
    border: 1px solid #D1D5DB;
    border-radius: 5px;
    background: #F9FAFB;
    padding: 9px 11px;
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

.staff-name {
    font-weight: 700;
}

/* ==========================================================================
   MAIN RESULT TABLE
   ========================================================================== */

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
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11px;
    border: 1px solid #333;
}

.result-sheet-table th,
.result-sheet-table td {
    border: 1px solid #333 !important;
    border-style: solid !important;
    border-width: 1px !important;
    text-align: center;
    vertical-align: middle;
    padding: 5px 4px;
}

.result-sheet-table th {
    background: #dbeafe;
    font-size: 11px;
    font-weight: 700 !important;
    line-height: 1.1;
    white-space: normal;
    overflow-wrap: normal;
}

.result-sheet-table td {
    font-size: 10.5px;
    line-height: 1.15;
    white-space: nowrap;
}

.result-sheet-table td:first-child {
    white-space: nowrap;
    font-weight: 700;
}

/* ==========================================================================
   STUDENT NAME
   ========================================================================== */

.result-sheet-table th:nth-child(2),
.result-sheet-table td:nth-child(2) {
    width: auto;
    min-width: max-content;
}

.student-name {
    text-align: left !important;
    white-space: nowrap !important;
    font-weight: 700 !important;
    color: #111827 !important;
    overflow: visible !important;
    text-overflow: clip !important;
}

/* ==========================================================================
   SUBJECT HEADER
   ========================================================================== */

.subject-name {
    display: block;
    font-size: 9.5px;
    font-weight: 700 !important;
    line-height: 10px;
    white-space: nowrap;
}

.subject-max {
    display: block;
    font-size: 8px;
    font-weight: 700 !important;
    line-height: 9px;
    white-space: nowrap;
}

.result-sheet-table th[colspan="2"] {
    width: auto;
    white-space: nowrap;
}

.result-sheet-table td {
    overflow: visible !important;
    text-overflow: clip !important;
}

/* ==========================================================================
   STATUS
   ========================================================================== */

.absent-mark {
    color: #991b1b !important;
    font-weight: 700 !important;
}

.fail-result {
    color: #991b1b !important;
    font-weight: 700 !important;
}

.pass-result {
    color: #166534 !important;
    font-weight: 700 !important;
}

.pending-result {
    color: #92400e !important;
    font-weight: 700 !important;
}

.no-marks-message {
    color: #92400e;
    font-weight: 700;
    text-align: center;
    padding: 14px !important;
    background: #fffbeb;
}

/* ==========================================================================
   OVERALL ANALYSIS
   ========================================================================== */

.analysis-title {
    margin-top: 22px;
    margin-bottom: 9px;
    font-size: 17px;
    font-weight: 700 !important;
    color: #1d4ed8;
}

.analysis-table-wrapper {
    width: 100%;
    overflow: visible;
}

.analysis-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: auto;
    font-size: 11px;
    border: 1px solid #333;
}

.analysis-table th,
.analysis-table td {
    border: 1px solid #333 !important;
    padding: 6px 5px;
    text-align: center;
    vertical-align: middle;
    font-size: 11px;
    white-space: nowrap;
}

.analysis-table th {
    background: #dbeafe;
    font-weight: 700 !important;
}

.analysis-table td:first-child {
    text-align: left;
    font-weight: 700 !important;
}

/* ==========================================================================
   OVERALL TOTAL
   ========================================================================== */

.analysis-total-row {
    font-weight: 700 !important;
    background: #e5e7eb !important;
}

.analysis-total-row td {
    font-weight: 700 !important;
    background: #e5e7eb !important;
}

/* ==========================================================================
   SUBJECT WISE ANALYSIS
   ========================================================================== */

.subject-wise-analysis-wrapper {
    width: 100%;
    overflow: visible;
}

.subject-wise-analysis-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: auto;
    font-size: 11px;
    border: 1px solid #333;
}

.subject-wise-analysis-table th,
.subject-wise-analysis-table td {
    border: 1px solid #333 !important;
    padding: 6px 4px;
    text-align: center;
    vertical-align: middle;
    font-size: 11px;
    white-space: nowrap;
}

.subject-wise-analysis-table th {
    background: #dbeafe;
    font-weight: 700 !important;
}

.subject-wise-analysis-table .subject-analysis-name {
    text-align: left !important;
    font-weight: 700 !important;
    white-space: nowrap !important;
    overflow: visible !important;
    text-overflow: clip !important;
    width: auto;
    min-width: max-content;
}

/* ==========================================================================
   RESPONSIVE
   ========================================================================== */

@media (max-width: 1200px) {

    .result-sheet-page {
        padding-top: 60px;
        padding-left: 55px;
        padding-right: 15px;
    }

    .result-information-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .result-sheet-table {
        font-size: 10px;
    }

    .result-sheet-table th {
        font-size: 10px;
    }

    .result-sheet-table td {
        font-size: 9.5px;
    }
}

@media (max-width: 900px) {

    .result-sheet-page {
        padding-top: 45px;
        padding-left: 30px;
        padding-right: 10px;
    }

    .result-filter-row {
        flex-wrap: wrap;
    }

    .result-information-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .result-sheet-table {
        font-size: 9px;
    }

    .result-sheet-table th {
        font-size: 9px;
    }

    .result-sheet-table td {
        font-size: 8.5px;
    }

    .subject-wise-analysis-table {
        font-size: 9px;
    }

    .subject-wise-analysis-table th,
    .subject-wise-analysis-table td {
        font-size: 9px;
        padding: 4px 3px;
    }
}

@media (max-width: 700px) {

    .result-sheet-page {
        padding-top: 25px;
        padding-left: 15px;
        padding-right: 8px;
    }

    .result-filter-row {
        display: grid;
        grid-template-columns: auto minmax(100px, 1fr);
        gap: 7px;
    }

    .result-filter-select-wrapper {
        width: 100%;
    }

    .result-filter .year-select,
    .result-filter .exam-select,
    .result-filter .division-select {
        width: 100%;
    }

    .result-information-grid {
        grid-template-columns: 1fr;
    }

    .result-sheet-table {
        font-size: 8px;
    }

    .result-sheet-table th {
        font-size: 8px;
        padding: 3px 2px;
    }

    .result-sheet-table td {
        font-size: 7.5px;
        padding: 3px 2px;
    }

    .subject-name {
        font-size: 7px;
    }

    .subject-max {
        font-size: 6px;
    }

    .subject-wise-analysis-table,
    .subject-wise-analysis-table th,
    .subject-wise-analysis-table td {
        font-size: 7px;
        padding: 3px 2px;
    }
}
</style>

@section('content')

<div class="erp-page result-sheet-page">

    {{-- =========================================================
         NORMALIZE DATA FIRST
    ========================================================== --}}

    @php

        $resultCollection = collect($results ?? []);

        $columnCollection = collect($displayColumns ?? []);

        /*
        |--------------------------------------------------------------------------
        | UNIQUE STUDENTS
        |--------------------------------------------------------------------------
        |
        | Priority:
        |
        | 1. student_id
        | 2. roll_no
        | 3. student name
        |
        | This prevents duplicate rows from appearing in the result table.
        |
        */

        $uniqueStudents = collect();

        $seenStudents = [];

        foreach ($resultCollection as $student) {

            $studentId = (int) (
                $student->student_id
                ?? 0
            );

            $rollNo = trim(
                (string) (
                    $student->roll_no
                    ?? ''
                )
            );

            $studentName = trim(
                (string) (
                    $student->full_student_name
                    ?? ''
                )
            );

            /*
            |--------------------------------------------------------------------------
            | BUILD UNIQUE KEY
            |--------------------------------------------------------------------------
            */

            if ($studentId > 0) {

                $uniqueKey =
                    'ID:' . $studentId;

            } elseif ($rollNo !== '') {

                $uniqueKey =
                    'ROLL:' .
                    strtoupper($rollNo);

            } elseif ($studentName !== '') {

                $normalizedName =
                    strtoupper(
                        preg_replace(
                            '/\s+/',
                            ' ',
                            $studentName
                        )
                    );

                $uniqueKey =
                    'NAME:' .
                    trim($normalizedName);

            } else {

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | SKIP DUPLICATE
            |--------------------------------------------------------------------------
            */

            if (
                isset(
                    $seenStudents[$uniqueKey]
                )
            ) {
                continue;
            }

            $seenStudents[$uniqueKey] = true;

            $uniqueStudents->push(
                $student
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SORT UNIQUE STUDENTS
        |--------------------------------------------------------------------------
        */

        $sortedResults =
            $uniqueStudents
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
                                (int)$rollNo
                            ];
                        }

                        return [
                            1,
                            strtoupper($rollNo)
                        ];
                    }
                )
                ->values();

        /*
        |--------------------------------------------------------------------------
        | RESULT SHEET EXISTS WHEN SUBJECT MAPPING EXISTS
        |--------------------------------------------------------------------------
        */

        $hasResultSheetData =
            $columnCollection->count() > 0;

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

        /*
        |--------------------------------------------------------------------------
        | DISPLAY NUMBER
        |--------------------------------------------------------------------------
        */

        $displayNumber =
            function ($value) {

                if (
                    $value === null
                    ||
                    $value === ''
                    ||
                    $value === '-'
                ) {

                    return '-';
                }

                if (
                    is_numeric($value)
                ) {

                    $number =
                        (float)$value;

                    if (
                        floor($number)
                        ==
                        $number
                    ) {

                        return (int)$number;
                    }

                    return number_format(
                        $number,
                        2
                    );
                }

                return $value;
            };

        /*
        |--------------------------------------------------------------------------
        | SUBJECT KEY
        |--------------------------------------------------------------------------
        */

        $getSubjectKey =
            function ($column) {

                if (
                    isset($column->key)
                    &&
                    trim(
                        (string)$column->key
                    ) !== ''
                ) {

                    return (string)$column->key;
                }

                if (
                    isset($column->subject_id)
                    &&
                    $column->subject_id !== ''
                ) {

                    return
                        'subject_' .
                        (int)$column->subject_id;
                }

                if (
                    isset($column->mapping_id)
                    &&
                    $column->mapping_id !== ''
                ) {

                    return
                        'mapping_' .
                        (int)$column->mapping_id;
                }

                if (
                    isset($column->subject_code)
                    &&
                    trim(
                        (string)$column->subject_code
                    ) !== ''
                ) {

                    return
                        (string)$column->subject_code;
                }

                return '';
            };

        /*
        |--------------------------------------------------------------------------
        | GET STUDENT SUBJECT VALUE
        |--------------------------------------------------------------------------
        */

        $getStudentSubjectValue =
            function (
                $student,
                $column,
                $type
            ) use (
                $getSubjectKey
            ) {

                $collection =
                    $type === 'mark'
                        ? (
                            $student->subject_marks
                            ?? []
                        )
                        : (
                            $student->subject_grades
                            ?? []
                        );

                if (
                    $collection
                    instanceof
                    \Illuminate\Support\Collection
                ) {

                    $collection =
                        $collection->toArray();
                }

                if (
                    !is_array($collection)
                ) {

                    return null;
                }

                $possibleKeys = [];

                /*
                |--------------------------------------------------------------------------
                | PRIMARY KEY
                |--------------------------------------------------------------------------
                */

                $key =
                    $getSubjectKey(
                        $column
                    );

                if (
                    $key !== ''
                ) {

                    $possibleKeys[] =
                        $key;
                }

                /*
                |--------------------------------------------------------------------------
                | SUBJECT ID
                |--------------------------------------------------------------------------
                */

                if (
                    isset(
                        $column->subject_id
                    )
                ) {

                    $subjectId =
                        (int)$column->subject_id;

                    if (
                        $subjectId > 0
                    ) {

                        $possibleKeys[] =
                            'subject_' .
                            $subjectId;

                        $possibleKeys[] =
                            (string)$subjectId;

                        $possibleKeys[] =
                            'SUBJECT_' .
                            $subjectId;
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | MAPPING ID
                |--------------------------------------------------------------------------
                */

                if (
                    isset(
                        $column->mapping_id
                    )
                ) {

                    $mappingId =
                        (int)$column->mapping_id;

                    if (
                        $mappingId > 0
                    ) {

                        $possibleKeys[] =
                            'mapping_' .
                            $mappingId;

                        $possibleKeys[] =
                            (string)$mappingId;
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | CODE
                |--------------------------------------------------------------------------
                */

                if (
                    !empty(
                        $column->subject_code
                    )
                ) {

                    $possibleKeys[] =
                        (string)$column->subject_code;
                }

                /*
                |--------------------------------------------------------------------------
                | NAME
                |--------------------------------------------------------------------------
                */

                if (
                    !empty(
                        $column->subject_name
                    )
                ) {

                    $possibleKeys[] =
                        (string)$column->subject_name;
                }

                $possibleKeys =
                    array_values(
                        array_unique(
                            $possibleKeys
                        )
                    );

                /*
                |--------------------------------------------------------------------------
                | DIRECT LOOKUP
                |--------------------------------------------------------------------------
                */

                foreach (
                    $possibleKeys as $possibleKey
                ) {

                    if (
                        array_key_exists(
                            $possibleKey,
                            $collection
                        )
                    ) {

                        return
                            $collection[
                                $possibleKey
                            ];
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | CASE INSENSITIVE LOOKUP
                |--------------------------------------------------------------------------
                */

                foreach (
                    $collection as
                    $storedKey =>
                    $storedValue
                ) {

                    $storedNormalized =
                        strtolower(
                            trim(
                                (string)$storedKey
                            )
                        );

                    foreach (
                        $possibleKeys as
                        $possibleKey
                    ) {

                        if (
                            $storedNormalized
                            ===
                            strtolower(
                                trim(
                                    (string)$possibleKey
                                )
                            )
                        ) {

                            return $storedValue;
                        }
                    }
                }

                return null;
            };

    @endphp


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

                        <div class="result-filter-select-wrapper">

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
                                        {{ (string)request('academic_year_id') === (string)$year->id ? 'selected' : '' }}
                                    >
                                        {{ $year->year_name }}
                                    </option>

                                @endforeach

                            </select>

                            <span class="result-filter-arrow"></span>

                        </div>


                        {{-- EXAM --}}
                        <label class="result-filter-label">
                            Exam
                        </label>

                        <div class="result-filter-select-wrapper">

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
                                        {{ (string)request('exam_master_id') === (string)$examItem->id ? 'selected' : '' }}
                                    >
                                        {{ $examItem->display_exam_name ?? $examItem->exam_name }}
                                    </option>

                                @endforeach

                            </select>

                            <span class="result-filter-arrow"></span>

                        </div>


                        {{-- DIVISION --}}
                        <label class="result-filter-label">
                            Division
                        </label>

                        <div class="result-filter-select-wrapper">

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
                                        {{ (string)request('division_id') === (string)$divisionItem->id ? 'selected' : '' }}
                                    >
                                        {{ $divisionItem->division_name }}
                                    </option>

                                @endforeach

                            </select>

                            <span class="result-filter-arrow"></span>

                        </div>


                        {{-- GENERATE --}}
                        <button
                            type="submit"
                            class="erp-btn erp-btn-save result-filter-button"
                        >
                            Generate
                        </button>


                        {{-- PRINT --}}
                        @if(
                            $sortedResults->count() > 0
                            ||
                            $columnCollection->count() > 0
                        )

                            <a
                                href="{{ route('result-sheet.print', [
                                    'academic_year_id' => request('academic_year_id'),
                                    'exam_master_id' => request('exam_master_id'),
                                    'division_id' => request('division_id'),
                                ]) }}"
                                target="_blank"
                                class="erp-btn erp-btn-save result-filter-button"
                            >
                                Print
                            </a>

                        @endif


                        {{-- UNIQUE STUDENT COUNT --}}
                        @if(
                            $sortedResults->count() > 0
                        )

                            <div class="result-student-row">

                                <span class="result-student-count">
                                    Students : {{ $sortedResults->count() }}
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
         RESULT SHEET
    ========================================================== --}}

    @if($hasResultSheetData)

        <div class="erp-card mt-4">

            {{-- =====================================================
                 INFORMATION - 8 ITEMS IN 4 COLUMNS
            ====================================================== --}}

            <div class="result-information-wrapper">

                <div class="result-information-grid">

                    {{-- 1. Academic Year --}}
                    <div class="result-information-item">

                        <span class="result-information-label">
                            Academic Year
                        </span>

                        <span class="result-information-value">
                            {{ $academicYear->year_name ?? '-' }}
                        </span>

                    </div>

                    {{-- 2. Exam --}}
                    <div class="result-information-item">

                        <span class="result-information-label">
                            Exam
                        </span>

                        <span class="result-information-value">
                            {{ $exam->display_exam_name ?? $exam->exam_name ?? '-' }}
                        </span>

                    </div>

                    {{-- 3. Standard / Division (COMBINED) --}}
                    <div class="result-information-item">

                        <span class="result-information-label">
                            Standard / Division
                        </span>

                        <span class="result-information-value">
                            {{ ($standard->standard_name ?? '-') . ' / ' . ($division->division_name ?? '-') }}
                        </span>

                    </div>

                    {{-- 4. Class Teacher --}}
                    <div class="result-information-item">

                        <span class="result-information-label">
                            Class Teacher
                        </span>

                        <span class="result-information-value">
                            {{ $classTeacherName ?: '-' }}
                        </span>

                    </div>

                    {{-- 5. Principal --}}
                    <div class="result-information-item">

                        <span class="result-information-label">
                            Principal
                        </span>

                        <span class="result-information-value">
                            {{ $principalName ?: '-' }}
                        </span>

                    </div>

                    {{-- 6. Total Maximum Marks --}}
                    <div class="result-information-item">

                        <span class="result-information-label">
                            Total Maximum Marks
                        </span>

                        <span class="result-information-value">
                            {{ $displayNumber($displayTotalMaxMarks) }}
                        </span>

                    </div>

                    {{-- 7. Overall Pass % --}}
                    <div class="result-information-item">

                        <span class="result-information-label">
                            Overall Pass %
                        </span>

                        <span class="result-information-value">
                            {{ $passPercentage ?? 40 }}%
                        </span>

                    </div>

                    {{-- 8. Total Students --}}
                    <div class="result-information-item">

                        <span class="result-information-label">
                            Total Students
                        </span>

                        <span class="result-information-value">
                            {{ $sortedResults->count() }}
                        </span>

                    </div>

                </div>

            </div>


            {{-- =====================================================
                 MAIN RESULT TABLE
            ====================================================== --}}

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

                            @foreach($columnCollection as $column)

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

                                @endphp

                                <th colspan="2">

                                    <span class="subject-name">
                                        {{
                                            $column->subject_name
                                            ?? $column->subject_code
                                            ?? '-'
                                        }}
                                    </span>

                                    <span class="subject-max">
                                        Max = {{
                                            $displayNumber(
                                                $subjectMax
                                            )
                                        }}
                                    </span>

                                    <span class="subject-max">
                                        Pass = {{
                                            $displayNumber(
                                                $subjectPassing
                                            )
                                        }}
                                    </span>

                                </th>

                            @endforeach

                            <th>

                                Total

                                <span class="subject-max">
                                    Max = {{
                                        $displayNumber(
                                            $displayTotalMaxMarks
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

                            @foreach($columnCollection as $column)

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

                    @forelse($sortedResults as $student)

                        <tr>

                            <td>
                                {{ $student->roll_no ?: '-' }}
                            </td>


                            <td
                                class="student-name"
                                title="{{ $student->full_student_name ?? '' }}"
                            >
                                {{ $student->full_student_name ?? '-' }}
                            </td>


                            @foreach($columnCollection as $column)

                                @php

                                    $mark =
                                        $getStudentSubjectValue(
                                            $student,
                                            $column,
                                            'mark'
                                        );

                                    $grade =
                                        $getStudentSubjectValue(
                                            $student,
                                            $column,
                                            'grade'
                                        );

                                    if (
                                        $mark === null
                                        ||
                                        $mark === ''
                                    ) {

                                        $mark = '-';
                                    }

                                    if (
                                        $grade === null
                                        ||
                                        $grade === ''
                                    ) {

                                        $grade = '-';
                                    }

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
                                    )

                                        -

                                    @elseif(
                                        is_numeric($mark)
                                    )

                                        {{
                                            $displayNumber(
                                                $mark
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
                                    )

                                        -

                                    @else

                                        {{ $grade }}

                                    @endif

                                </td>

                            @endforeach


                            {{-- TOTAL --}}

                            <td>

                                {{
                                    $displayNumber(
                                        $student->academic_total
                                        ?? 0
                                    )
                                }}

                            </td>


                            {{-- PERCENTAGE --}}

                            <td>

                                @if(
                                    $student->calculated_percentage === null
                                    ||
                                    $student->calculated_percentage === ''
                                )

                                    -

                                @else

                                    {{ (int) round((float) $student->calculated_percentage) }}%

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

                    @empty

                        <tr>

                            <td
                                colspan="{{ 2 + ($columnCollection->count() * 2) + 4 }}"
                                class="no-marks-message"
                            >
                                No students found for the selected
                                Standard and Division.
                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>


            {{-- =====================================================
                 OVERALL GRADE / RESULT ANALYSIS
            ====================================================== --}}

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

                    @foreach(
                        $overallOrder
                        as $grade
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

                    @endforeach

                    @php

                        $totalGirls =
                            $overallGradeAnalysis['TOTAL']['girls']
                            ??
                            (
                                ($overallGradeAnalysis['PASS']['girls'] ?? 0)
                                +
                                ($overallGradeAnalysis['FAIL']['girls'] ?? 0)
                            );

                        $totalBoys =
                            $overallGradeAnalysis['TOTAL']['boys']
                            ??
                            (
                                ($overallGradeAnalysis['PASS']['boys'] ?? 0)
                                +
                                ($overallGradeAnalysis['FAIL']['boys'] ?? 0)
                            );

                        $totalStudents =
                            $overallGradeAnalysis['TOTAL']['total']
                            ??
                            (
                                ($overallGradeAnalysis['PASS']['total'] ?? 0)
                                +
                                ($overallGradeAnalysis['FAIL']['total'] ?? 0)
                            );

                    @endphp

                    <tr class="analysis-total-row">

                        <td>
                            TOTAL
                        </td>

                        <td>
                            TOTAL
                        </td>

                        <td>
                            {{ $totalGirls }}
                        </td>

                        <td>
                            {{ $totalBoys }}
                        </td>

                        <td>
                            {{ $totalStudents }}
                        </td>

                    </tr>

                    </tbody>

                </table>

            </div>


            {{-- =====================================================
                 SUBJECT WISE ANALYSIS
            ====================================================== --}}

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

                $analysisSubjects =
                    $columnCollection->values();

            @endphp


            <div class="subject-wise-analysis-wrapper">

                <table class="subject-wise-analysis-table">

                    <thead>

                        <tr>

                            <th rowspan="2">
                                Subject
                            </th>

                            <th colspan="2">
                                A1
                            </th>

                            <th colspan="2">
                                A2
                            </th>

                            <th colspan="2">
                                B1
                            </th>

                            <th colspan="2">
                                B2
                            </th>

                            <th colspan="2">
                                C1
                            </th>

                            <th colspan="2">
                                C2
                            </th>

                            <th colspan="2">
                                D
                            </th>

                            <th colspan="2">
                                FAIL
                            </th>

                            <th colspan="2">
                                ABSENT
                            </th>

                        </tr>

                        <tr>

                            @for($i = 0; $i < 9; $i++)

                                <th>
                                    Girls
                                </th>

                                <th>
                                    Boys
                                </th>

                            @endfor

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
                            <td>{{ $girls['A1'] ?? 0 }}</td>
                            <td>{{ $boys['A1'] ?? 0 }}</td>

                            {{-- A2 --}}
                            <td>{{ $girls['A2'] ?? 0 }}</td>
                            <td>{{ $boys['A2'] ?? 0 }}</td>

                            {{-- B1 --}}
                            <td>{{ $girls['B1'] ?? 0 }}</td>
                            <td>{{ $boys['B1'] ?? 0 }}</td>

                            {{-- B2 --}}
                            <td>{{ $girls['B2'] ?? 0 }}</td>
                            <td>{{ $boys['B2'] ?? 0 }}</td>

                            {{-- C1 --}}
                            <td>{{ $girls['C1'] ?? 0 }}</td>
                            <td>{{ $boys['C1'] ?? 0 }}</td>

                            {{-- C2 --}}
                            <td>{{ $girls['C2'] ?? 0 }}</td>
                            <td>{{ $boys['C2'] ?? 0 }}</td>

                            {{-- D --}}
                            <td>{{ $girls['D'] ?? 0 }}</td>
                            <td>{{ $boys['D'] ?? 0 }}</td>

                            {{-- FAIL --}}
                            <td>{{ $girls['fail'] ?? 0 }}</td>
                            <td>{{ $boys['fail'] ?? 0 }}</td>

                            {{-- ABSENT --}}
                            <td>{{ $girls['absent'] ?? 0 }}</td>
                            <td>{{ $boys['absent'] ?? 0 }}</td>

                        </tr>

                    @endforeach


                    {{-- =================================================
                         SUBJECT ANALYSIS TOTAL
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
                                $categoryKey . '_girls'
                            ] =
                                $girlsBySubject->sum(
                                    fn($row) =>
                                        (int)(
                                            $row[$categoryKey]
                                            ?? 0
                                        )
                                );

                            $grandTotals[
                                $categoryKey . '_boys'
                            ] =
                                $boysBySubject->sum(
                                    fn($row) =>
                                        (int)(
                                            $row[$categoryKey]
                                            ?? 0
                                        )
                                );
                        }

                    @endphp

                    <tr class="analysis-total-row">

                        <td class="subject-analysis-name">
                            TOTAL
                        </td>

                        <td>
                            {{ $grandTotals['A1_girls'] ?? 0 }}
                        </td>

                        <td>
                            {{ $grandTotals['A1_boys'] ?? 0 }}
                        </td>

                        <td>
                            {{ $grandTotals['A2_girls'] ?? 0 }}
                        </td>

                        <td>
                            {{ $grandTotals['A2_boys'] ?? 0 }}
                        </td>

                        <td>
                            {{ $grandTotals['B1_girls'] ?? 0 }}
                        </td>

                        <td>
                            {{ $grandTotals['B1_boys'] ?? 0 }}
                        </td>

                        <td>
                            {{ $grandTotals['B2_girls'] ?? 0 }}
                        </td>

                        <td>
                            {{ $grandTotals['B2_boys'] ?? 0 }}
                        </td>

                        <td>
                            {{ $grandTotals['C1_girls'] ?? 0 }}
                        </td>

                        <td>
                            {{ $grandTotals['C1_boys'] ?? 0 }}
                        </td>

                        <td>
                            {{ $grandTotals['C2_girls'] ?? 0 }}
                        </td>

                        <td>
                            {{ $grandTotals['C2_boys'] ?? 0 }}
                        </td>

                        <td>
                            {{ $grandTotals['D_girls'] ?? 0 }}
                        </td>

                        <td>
                            {{ $grandTotals['D_boys'] ?? 0 }}
                        </td>

                        <td>
                            {{ $grandTotals['fail_girls'] ?? 0 }}
                        </td>

                        <td>
                            {{ $grandTotals['fail_boys'] ?? 0 }}
                        </td>

                        <td>
                            {{ $grandTotals['absent_girls'] ?? 0 }}
                        </td>

                        <td>
                            {{ $grandTotals['absent_boys'] ?? 0 }}
                        </td>

                    </tr>

                    </tbody>

                </table>

            </div>

        </div>

    @elseif(
        request()->has('academic_year_id')
        &&
        request()->has('exam_master_id')
        &&
        request()->has('division_id')
    )

        <div class="erp-card mt-4">

            <div
                style="
                    color:#92400e;
                    background:#fffbeb;
                    border:1px solid #fde68a;
                    padding:15px;
                    font-weight:700;
                    border-radius:5px;
                "
            >
                No active Standard Wise Subjects were found for the
                selected Exam / Standard.
            </div>

        </div>

    @endif

</div>

@endsection