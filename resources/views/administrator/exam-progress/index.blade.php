<x-app-layout>

<div class="p-3">

    {{-- =========================================================
         PAGE TITLE
    ========================================================== --}}

    <div class="mb-3">

        <h2 style="
            font-size:22px;
            font-weight:600;
            color:#92400E;
            margin:0;
        ">
            Exam Progress Dashboard
        </h2>

    </div>


    {{-- =========================================================
         FILTER
    ========================================================== --}}

    <div class="bg-white border rounded shadow p-3">

        <form
            method="GET"
            action="{{ route('exam-progress.index') }}"
            id="examProgressFilterForm"
        >

            <div style="
                display:flex;
                align-items:center;
                gap:10px;
                flex-wrap:wrap;
            ">

                {{-- =================================================
                     EXAM
                ================================================== --}}

                <label
                    for="exam_master_id"
                    style="
                        font-size:14px;
                        font-weight:600;
                        white-space:nowrap;
                    "
                >
                    Exam
                </label>

                <select
                    name="exam_master_id"
                    id="exam_master_id"
                    style="
                        width:240px;
                        min-width:240px;
                        height:34px;
                        padding:4px 8px;
                        font-size:14px;
                        border:1px solid #D1D5DB;
                        border-radius:5px;
                        background:#fff;
                    "
                >

                    <option value="">
                        All Exams
                    </option>

                    @foreach($exams as $exam)

                        <option
                            value="{{ $exam->id }}"
                            {{ (string)$examId === (string)$exam->id ? 'selected' : '' }}
                        >
                            {{
                                $exam->display_exam_name
                                ?? $exam->exam_name
                            }}
                        </option>

                    @endforeach

                </select>


                {{-- =================================================
                     STANDARD
                ================================================== --}}

                <label
                    for="standard_id"
                    style="
                        font-size:14px;
                        font-weight:600;
                        white-space:nowrap;
                    "
                >
                    Standard
                </label>

                <select
                    name="standard_id"
                    id="standard_id"
                    style="
                        width:190px;
                        min-width:190px;
                        height:34px;
                        padding:4px 8px;
                        font-size:14px;
                        border:1px solid #D1D5DB;
                        border-radius:5px;
                        background:#fff;
                    "
                >

                    <option value="">
                        All Standards
                    </option>

                    @foreach($standards as $standard)

                        <option
                            value="{{ $standard->id }}"
                            {{ (string)$standardId === (string)$standard->id ? 'selected' : '' }}
                        >
                            {{ $standard->standard_name }}
                        </option>

                    @endforeach

                </select>


                {{-- =================================================
                     DIVISION
                ================================================== --}}

                <label
                    for="division_id"
                    style="
                        font-size:14px;
                        font-weight:600;
                        white-space:nowrap;
                    "
                >
                    Division
                </label>

                <select
                    name="division_id"
                    id="division_id"
                    style="
                        width:150px;
                        min-width:150px;
                        height:34px;
                        padding:4px 8px;
                        font-size:14px;
                        border:1px solid #D1D5DB;
                        border-radius:5px;
                        background:#fff;
                    "
                >

                    <option value="">
                        All Divisions
                    </option>

                    @foreach($divisions as $division)

                        <option
                            value="{{ $division->id }}"
                            {{ (string)$divisionId === (string)$division->id ? 'selected' : '' }}
                        >
                            {{ $division->division_name }}
                        </option>

                    @endforeach

                </select>


                {{-- =================================================
                     SEARCH
                ================================================== --}}

                <button
                    type="submit"
                    class="erp-btn erp-btn-save"
                    style="
                        height:34px;
                        padding:0 15px;
                        font-size:14px;
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
                        href="{{ route('exam-progress.index') }}"
                        style="
                            height:34px;
                            display:inline-flex;
                            align-items:center;
                            padding:0 15px;
                            font-size:14px;
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

        <div style="
            margin-top:12px;
            display:flex;
            gap:10px;
            flex-wrap:wrap;
        ">

            {{-- COMPLETED --}}

            <span style="
                background:#DCFCE7;
                color:#166534;
                padding:6px 12px;
                border-radius:4px;
                font-size:14px;
                font-weight:600;
            ">
                COMPLETED : {{ $completed }}
            </span>


            {{-- PENDING --}}

            <span style="
                background:#FEF3C7;
                color:#92400E;
                padding:6px 12px;
                border-radius:4px;
                font-size:14px;
                font-weight:600;
            ">
                PENDING : {{ $pending }}
            </span>


            {{-- TOTAL --}}

            <span style="
                background:#DBEAFE;
                color:#1E40AF;
                padding:6px 12px;
                border-radius:4px;
                font-size:14px;
                font-weight:600;
            ">
                TOTAL : {{ $total }}
            </span>

        </div>

    </div>


    {{-- =========================================================
         RESULT TABLE
    ========================================================== --}}

    <div class="bg-white border rounded shadow p-3 mt-4">

        <div style="
            width:100%;
            overflow-x:auto;
        ">

            <table style="
                width:100%;
                border-collapse:collapse;
                background:#fff;
                font-size:14px;
            ">

                <thead>

                    <tr style="
                        background:#DBEAFE;
                        font-weight:700;
                    ">

                        {{-- EXAM --}}

                        <th style="
                            border:1px solid #D1D5DB;
                            padding:8px;
                            text-align:left;
                            white-space:nowrap;
                        ">
                            Exam
                        </th>


                        {{-- SUBJECT --}}

                        <th style="
                            border:1px solid #D1D5DB;
                            padding:8px;
                            text-align:left;
                            white-space:nowrap;
                        ">
                            Subject
                        </th>


                        {{-- STANDARD --}}

                        <th style="
                            border:1px solid #D1D5DB;
                            padding:8px;
                            text-align:left;
                            white-space:nowrap;
                        ">
                            Standard
                        </th>


                        {{-- DIVISION --}}

                        <th style="
                            border:1px solid #D1D5DB;
                            padding:8px;
                            text-align:center;
                            white-space:nowrap;
                        ">
                            Division
                        </th>


                        {{-- TEACHER --}}

                        <th style="
                            border:1px solid #D1D5DB;
                            padding:8px;
                            text-align:left;
                            white-space:nowrap;
                        ">
                            Teacher
                        </th>


                        {{-- STATUS --}}

                        <th style="
                            border:1px solid #D1D5DB;
                            padding:8px;
                            text-align:center;
                            white-space:nowrap;
                        ">
                            Status
                        </th>


                        {{-- ACTION --}}

                        <th style="
                            border:1px solid #D1D5DB;
                            padding:8px;
                            text-align:center;
                            white-space:nowrap;
                        ">
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

                            $subjectName =
                                '-';
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | IDs
                        |--------------------------------------------------------------------------
                        */

                        $tsaId =
                            (int)(
                                $status->teacher_subject_allocation_id
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
                                $status->resolved_subject_id
                                ?? 0
                            );

                    @endphp


                    <tr>

                        {{-- =================================================
                             EXAM
                        ================================================== --}}

                        <td style="
                            border:1px solid #D1D5DB;
                            padding:8px;
                            white-space:nowrap;
                        ">

                            {{
                                $status->exam_name
                                ?: '-'
                            }}

                        </td>


                        {{-- =================================================
                             SUBJECT
                        ================================================== --}}

                        <td style="
                            border:1px solid #D1D5DB;
                            padding:8px;
                            white-space:nowrap;
                            font-weight:600;
                        ">

                            {{ $subjectName }}

                            @if(
                                $subjectCode !== ''
                                &&
                                $subjectCode !== '-'
                            )

                                <span style="
                                    color:#6B7280;
                                    font-size:11px;
                                    font-weight:500;
                                    margin-left:4px;
                                ">
                                    ({{ $subjectCode }})
                                </span>

                            @endif

                        </td>


                        {{-- =================================================
                             STANDARD
                        ================================================== --}}

                        <td style="
                            border:1px solid #D1D5DB;
                            padding:8px;
                            white-space:nowrap;
                        ">

                            {{
                                $status->standard_name
                                ?: '-'
                            }}

                        </td>


                        {{-- =================================================
                             DIVISION
                        ================================================== --}}

                        <td style="
                            border:1px solid #D1D5DB;
                            padding:8px;
                            text-align:center;
                            white-space:nowrap;
                        ">

                            {{
                                $status->division_name
                                ?: '-'
                            }}

                        </td>


                        {{-- =================================================
                             TEACHER
                        ================================================== --}}

                        <td style="
                            border:1px solid #D1D5DB;
                            padding:8px;
                            white-space:nowrap;
                        ">

                            {{
                                $status->teacher_name
                                ?: '-'
                            }}

                        </td>


                        {{-- =================================================
                             STATUS
                        ================================================== --}}

                        <td style="
                            border:1px solid #D1D5DB;
                            padding:8px;
                            text-align:center;
                            white-space:nowrap;
                        ">

                            @if(
                                $currentStatus === 'COMPLETED'
                            )

                                <span style="
                                    background:#DCFCE7;
                                    color:#166534;
                                    padding:4px 10px;
                                    border-radius:4px;
                                    font-size:13px;
                                    font-weight:600;
                                    display:inline-block;
                                ">
                                    COMPLETED
                                </span>

                            @elseif(
                                $currentStatus === 'PENDING'
                            )

                                <span style="
                                    background:#FEF3C7;
                                    color:#92400E;
                                    padding:4px 10px;
                                    border-radius:4px;
                                    font-size:13px;
                                    font-weight:600;
                                    display:inline-block;
                                ">
                                    PENDING
                                </span>

                            @else

                                <span style="
                                    background:#F3F4F6;
                                    color:#374151;
                                    padding:4px 10px;
                                    border-radius:4px;
                                    font-size:13px;
                                    font-weight:600;
                                    display:inline-block;
                                ">
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

                        <td style="
                            border:1px solid #D1D5DB;
                            padding:8px;
                            text-align:center;
                            white-space:nowrap;
                        ">

                            {{-- =================================================
                                 COMPLETED → VIEW MARKS TAB
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
                                    href="{{ url('/marks-entry/view') . '?' . http_build_query([
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
                                    ]) }}"
                                    class="erp-btn erp-btn-save"
                                    style="
                                        height:32px;
                                        padding:0 12px;
                                        display:inline-flex;
                                        align-items:center;
                                        justify-content:center;
                                        font-size:13px;
                                        text-decoration:none;
                                    "
                                >
                                    View Marks
                                </a>


                            {{-- =================================================
                                 PENDING → MARKS ENTRY TAB
                            ================================================== --}}

                            @elseif(
                                $currentStatus === 'PENDING'
                                &&
                                $tsaId > 0
                                &&
                                $statusExamId > 0
                            )

                                <a
                                    href="{{ url('/marks-entry') . '?' . http_build_query([
                                        'exam_master_id' =>
                                            $statusExamId,

                                        'teacher_subject_allocation_id' =>
                                            $tsaId,
                                    ]) }}"
                                    class="erp-btn erp-btn-save"
                                    style="
                                        height:32px;
                                        padding:0 12px;
                                        display:inline-flex;
                                        align-items:center;
                                        justify-content:center;
                                        font-size:13px;
                                        text-decoration:none;
                                    "
                                >
                                    Enter Marks
                                </a>


                            {{-- =================================================
                                 OTHER STATUS
                            ================================================== --}}

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
                            style="
                                border:1px solid #D1D5DB;
                                padding:20px;
                                text-align:center;
                                color:#6B7280;
                            "
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
========================================================== --}}

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