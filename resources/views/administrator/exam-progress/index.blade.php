<x-app-layout>

<style>

/* =========================================================
   PAGE
========================================================= */

.exam-progress-page,
.exam-progress-page * {
    font-family: Arial, sans-serif !important;
    font-size: 14px;
}


/* =========================================================
   FILTER ROW
========================================================= */

.filter-row {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}


/* =========================================================
   FILTER LABEL
========================================================= */

.filter-label {
    font-size: 14px !important;
    font-weight: 600;
    white-space: nowrap;
}


/* =========================================================
   SELECT WRAPPER
========================================================= */

.select-wrapper {
    position: relative;
    display: inline-block;
}


/* =========================================================
   SELECT
========================================================= */

.filter-select {

    height: 34px;

    padding: 4px 34px 4px 9px;

    font-size: 14px !important;

    border: 1px solid #D1D5DB;

    border-radius: 5px;

    background-color: #FFFFFF;

    color: #111827;

    cursor: pointer;

    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;

    outline: none;
}


/* =========================================================
   SELECT WIDTHS
========================================================= */

.exam-select {
    width: 240px;
    min-width: 240px;
}

.standard-select {
    width: 190px;
    min-width: 190px;
}

.division-select {
    width: 150px;
    min-width: 150px;
}


/* =========================================================
   DROPDOWN ICON
========================================================= */

.select-arrow {

    position: absolute;

    top: 50%;

    right: 10px;

    transform:
        translateY(-50%)
        rotate(0deg);

    width: 0;
    height: 0;

    border-left: 5px solid transparent;
    border-right: 5px solid transparent;

    border-top: 6px solid #374151;

    pointer-events: none;
}


/* =========================================================
   SELECT FOCUS
========================================================= */

.filter-select:focus {

    border-color: #2563EB;

    box-shadow:
        0 0 0 2px rgba(37, 99, 235, 0.15);

}


/* =========================================================
   SUMMARY
========================================================= */

.summary-row {

    margin-top: 12px;

    display: flex;

    gap: 10px;

    flex-wrap: wrap;

}


/* =========================================================
   SUMMARY BADGES
========================================================= */

.summary-badge {

    padding: 6px 12px;

    border-radius: 4px;

    font-size: 14px !important;

    font-weight: 600;

    white-space: nowrap;
}


.completed-badge {

    background: #DCFCE7;

    color: #166534;
}


.pending-badge {

    background: #FEF3C7;

    color: #92400E;
}


.total-badge {

    background: #DBEAFE;

    color: #1E40AF;
}


/* =========================================================
   TABLE
========================================================= */

.result-table-wrapper {

    width: 100%;

    overflow-x: auto;
}


.result-table {

    width: 100%;

    border-collapse: collapse;

    background: #FFFFFF;

    font-size: 14px !important;
}


.result-table th {

    background: #DBEAFE;

    font-weight: 700 !important;

    border: 1px solid #D1D5DB;

    padding: 8px;

    white-space: nowrap;
}


.result-table td {

    border: 1px solid #D1D5DB;

    padding: 8px;

    white-space: nowrap;
}


.result-table th.left,
.result-table td.left {

    text-align: left;
}


.result-table th.center,
.result-table td.center {

    text-align: center;
}


/* =========================================================
   SUBJECT
========================================================= */

.subject-name {

    font-weight: 600;
}


.subject-code {

    color: #6B7280;

    font-size: 11px !important;

    font-weight: 500;

    margin-left: 4px;
}


/* =========================================================
   STATUS
========================================================= */

.status-badge {

    display: inline-block;

    padding: 4px 10px;

    border-radius: 4px;

    font-size: 13px !important;

    font-weight: 600;
}


.status-completed {

    background: #DCFCE7;

    color: #166534;
}


.status-pending {

    background: #FEF3C7;

    color: #92400E;
}


.status-other {

    background: #F3F4F6;

    color: #374151;
}


/* =========================================================
   ACTION
========================================================= */

.action-button {

    height: 32px;

    padding: 0 12px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    font-size: 13px !important;

    text-decoration: none;

}


/* =========================================================
   NO RECORD
========================================================= */

.no-record {

    border: 1px solid #D1D5DB;

    padding: 20px;

    text-align: center;

    color: #6B7280;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 900px) {

    .filter-row {

        flex-direction: column;

        align-items: stretch;
    }

    .filter-label {

        margin-top: 3px;
    }

    .select-wrapper {

        width: 100%;
    }

    .exam-select,
    .standard-select,
    .division-select {

        width: 100%;

        min-width: 100%;
    }

    .filter-row .erp-btn {

        width: 100%;
    }

}


/* =========================================================
   SMALL SCREENS
========================================================= */

@media (max-width: 600px) {

    .exam-progress-page {

        padding: 8px !important;
    }

    .result-table {

        font-size: 13px !important;
    }

}

</style>


<div class="exam-progress-page p-3">

    {{-- =========================================================
         PAGE TITLE
    ========================================================== --}}

    <div class="mb-3">

        <h2 style="
            font-size:22px !important;
            font-weight:600;
            color:#92400E;
            margin:0;
        ">
            Exam Progress Dashboard
        </h2>

    </div>


    {{-- =========================================================
         FILTER SECTION
    ========================================================== --}}

    <div class="bg-white border rounded shadow p-3">

        <form
            method="GET"
            action="{{ route('exam-progress.index') }}"
            id="examProgressFilterForm"
        >

            <div class="filter-row">

                {{-- =================================================
                     EXAM
                ================================================== --}}

                <label
                    for="exam_master_id"
                    class="filter-label"
                >
                    Exam
                </label>


                <div class="select-wrapper">

                    <select
                        name="exam_master_id"
                        id="exam_master_id"
                        class="filter-select exam-select"
                    >

                        <option value="">
                            All Exams
                        </option>


                        @foreach($exams as $exam)

                            <option
                                value="{{ $exam->id }}"
                                {{ (string)$examId ===
                                   (string)$exam->id
                                    ? 'selected'
                                    : '' }}
                            >

                                {{
                                    $exam->display_exam_name
                                    ?? $exam->exam_name
                                }}

                            </option>

                        @endforeach

                    </select>


                    <span class="select-arrow"></span>

                </div>


                {{-- =================================================
                     STANDARD
                ================================================== --}}

                <label
                    for="standard_id"
                    class="filter-label"
                >
                    Standard
                </label>


                <div class="select-wrapper">

                    <select
                        name="standard_id"
                        id="standard_id"
                        class="filter-select standard-select"
                    >

                        <option value="">
                            All Standards
                        </option>


                        @foreach($standards as $standard)

                            <option
                                value="{{ $standard->id }}"
                                {{ (string)$standardId ===
                                   (string)$standard->id
                                    ? 'selected'
                                    : '' }}
                            >

                                {{ $standard->standard_name }}

                            </option>

                        @endforeach

                    </select>


                    <span class="select-arrow"></span>

                </div>


                {{-- =================================================
                     DIVISION
                ================================================== --}}

                <label
                    for="division_id"
                    class="filter-label"
                >
                    Division
                </label>


                <div class="select-wrapper">

                    <select
                        name="division_id"
                        id="division_id"
                        class="filter-select division-select"
                    >

                        <option value="">
                            All Divisions
                        </option>


                        @foreach($divisions as $division)

                            <option
                                value="{{ $division->id }}"
                                {{ (string)$divisionId ===
                                   (string)$division->id
                                    ? 'selected'
                                    : '' }}
                            >

                                {{ $division->division_name }}

                            </option>

                        @endforeach

                    </select>


                    <span class="select-arrow"></span>

                </div>


                {{-- =================================================
                     SEARCH
                ================================================== --}}

                <button
                    type="submit"
                    class="erp-btn erp-btn-save"
                    style="
                        height:34px;
                        padding:0 15px;
                        font-size:14px !important;
                    "
                >
                    Search
                </button>


                {{-- =================================================
                     RESET
                ================================================== --}}

                @if(
                    ($examId !== null && $examId !== '')
                    ||
                    ($standardId !== null && $standardId !== '')
                    ||
                    ($divisionId !== null && $divisionId !== '')
                )

                    <a
                        href="{{ route(
                            'exam-progress.index'
                        ) }}"
                        style="
                            height:34px;
                            display:inline-flex;
                            align-items:center;
                            padding:0 15px;
                            font-size:14px !important;
                            border:1px solid #D1D5DB;
                            border-radius:5px;
                            background:#F9FAFB;
                            color:#374151;
                            text-decoration:none;
                        "
                    >
                        Reset
                    </a>

                @endif

            </div>

        </form>


        {{-- =========================================================
             SUMMARY
        ========================================================== --}}

        <div class="summary-row">

            <span
                class="summary-badge completed-badge"
            >
                COMPLETED : {{ $completed }}
            </span>


            <span
                class="summary-badge pending-badge"
            >
                PENDING : {{ $pending }}
            </span>


            <span
                class="summary-badge total-badge"
            >
                TOTAL : {{ $total }}
            </span>

        </div>

    </div>


    {{-- =========================================================
         RESULT TABLE
    ========================================================== --}}

    <div class="bg-white border rounded shadow p-3 mt-4">

        <div class="result-table-wrapper">

            <table class="result-table">

                <thead>

                    <tr>

                        <th class="left">
                            Exam
                        </th>

                        <th class="left">
                            Subject
                        </th>

                        <th class="left">
                            Standard
                        </th>

                        <th class="center">
                            Division
                        </th>

                        <th class="left">
                            Teacher
                        </th>

                        <th class="center">
                            Status
                        </th>

                        <th class="center">
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>

                @forelse($statuses as $status)

                    @php

                        /*
                        |--------------------------------------------------------------------------
                        | STATUS
                        |--------------------------------------------------------------------------
                        */

                        $currentStatus =
                            strtoupper(
                                trim(
                                    (string)(
                                        $status->status
                                        ?? ''
                                    )
                                )
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | SUBJECT
                        |--------------------------------------------------------------------------
                        */

                        $subjectName =
                            trim(
                                (string)(
                                    $status->subject_name
                                    ?? ''
                                )
                            );


                        $subjectCode =
                            trim(
                                (string)(
                                    $status->subject_code
                                    ?? ''
                                )
                            );


                        if (
                            $subjectName === ''
                        ) {

                            $subjectName = '-';

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | IDS
                        |--------------------------------------------------------------------------
                        */

                        $tsaId =
                            (int)(
                                $status
                                    ->teacher_subject_allocation_id
                                ?? 0
                            );


                        $statusExamId =
                            (int)(
                                $status->exam_master_id
                                ?? 0
                            );


                        $statusStandardId =
                            (int)(
                                $status->standard_id
                                ?? 0
                            );


                        $statusDivisionId =
                            (int)(
                                $status->division_id
                                ?? 0
                            );


                        $resolvedSubjectId =
                            (int)(
                                $status
                                    ->resolved_subject_id
                                ?? 0
                            );

                    @endphp


                    <tr>

                        {{-- =================================================
                             EXAM
                        ================================================== --}}

                        <td class="left">

                            {{
                                $status->exam_name
                                ?: '-'
                            }}

                        </td>


                        {{-- =================================================
                             SUBJECT
                        ================================================== --}}

                        <td class="left subject-name">

                            {{ $subjectName }}


                            @if(
                                $subjectCode !== ''
                                &&
                                $subjectCode !== '-'
                            )

                                <span class="subject-code">
                                    ({{ $subjectCode }})
                                </span>

                            @endif

                        </td>


                        {{-- =================================================
                             STANDARD
                        ================================================== --}}

                        <td class="left">

                            {{
                                $status->standard_name
                                ?: '-'
                            }}

                        </td>


                        {{-- =================================================
                             DIVISION
                        ================================================== --}}

                        <td class="center">

                            {{
                                $status->division_name
                                ?: '-'
                            }}

                        </td>


                        {{-- =================================================
                             TEACHER
                        ================================================== --}}

                        <td class="left">

                            {{
                                $status->teacher_name
                                ?: '-'
                            }}

                        </td>


                        {{-- =================================================
                             STATUS
                        ================================================== --}}

                        <td class="center">

                            @if(
                                $currentStatus === 'COMPLETED'
                            )

                                <span
                                    class="
                                        status-badge
                                        status-completed
                                    "
                                >
                                    COMPLETED
                                </span>

                            @elseif(
                                $currentStatus === 'PENDING'
                            )

                                <span
                                    class="
                                        status-badge
                                        status-pending
                                    "
                                >
                                    PENDING
                                </span>

                            @else

                                <span
                                    class="
                                        status-badge
                                        status-other
                                    "
                                >

                                    {{
                                        $currentStatus !== ''
                                            ? $currentStatus
                                            : '-'
                                    }}

                                </span>

                            @endif

                        </td>


                        {{-- =================================================
                             ACTION
                        ================================================== --}}

                        <td class="center">

                            {{-- =================================================
                                 COMPLETED
                            ================================================== --}}

                            @if(
                                $currentStatus === 'COMPLETED'
                                &&
                                $tsaId > 0
                                &&
                                $statusExamId > 0
                                &&
                                $statusStandardId > 0
                                &&
                                $statusDivisionId > 0
                                &&
                                $resolvedSubjectId > 0
                            )

                                <a
                                    href="{{
                                        url('/marks-entry/view')
                                        . '?'
                                        . http_build_query([
                                            'exam_master_id' =>
                                                $statusExamId,

                                            'standard_id' =>
                                                $statusStandardId,

                                            'division_id' =>
                                                $statusDivisionId,

                                            'subject_id' =>
                                                $resolvedSubjectId,

                                            'teacher_subject_allocation_id' =>
                                                $tsaId,
                                        ])
                                    }}"
                                    class="erp-btn erp-btn-save action-button"
                                >
                                    View Marks
                                </a>


                            {{-- =================================================
                                 PENDING
                            ================================================== --}}

                            @elseif(
                                $currentStatus === 'PENDING'
                                &&
                                $tsaId > 0
                                &&
                                $statusExamId > 0
                            )

                                <a
                                    href="{{
                                        url('/marks-entry')
                                        . '?'
                                        . http_build_query([
                                            'exam_master_id' =>
                                                $statusExamId,

                                            'teacher_subject_allocation_id' =>
                                                $tsaId,
                                        ])
                                    }}"
                                    class="erp-btn erp-btn-save action-button"
                                >
                                    Enter Marks
                                </a>


                            @else

                                <span style="
                                    color:#9CA3AF;
                                ">
                                    -
                                </span>

                            @endif

                        </td>

                    </tr>


                @empty

                    <tr>

                        <td
                            colspan="7"
                            class="no-record"
                        >
                            No pending or completed records found.
                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>


        {{-- =========================================================
             PAGINATION
        ========================================================== --}}

        @if($statuses->hasPages())

            <div style="
                margin-top:15px;
                display:flex;
                justify-content:center;
            ">

                {{
                    $statuses
                        ->onEachSide(5)
                        ->links()
                }}

            </div>

        @endif

    </div>

</div>


{{-- =========================================================
     AUTO SEARCH WHEN STANDARD CHANGES
========================================================= --}}

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const form =
            document.getElementById(
                'examProgressFilterForm'
            );


        const standardSelect =
            document.getElementById(
                'standard_id'
            );


        if (
            form &&
            standardSelect
        ) {

            standardSelect.addEventListener(
                'change',
                function () {

                    form.submit();

                }
            );

        }

    }
);

</script>


</x-app-layout>