<x-app-layout>

<style>

    .marks-view-page,
    .marks-view-page * {
        box-sizing: border-box;
        font-family: Arial, sans-serif !important;
    }

    .marks-view-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,.06);
        padding: 20px;
    }

    .marks-view-title {
        margin: 0 0 15px;
        font-size: 20px;
        font-weight: 700;
        color: #2563eb;
    }

    .tabs-container {
        display: flex;
        border-bottom: 2px solid #2563EB;
        margin-bottom: 18px;
        gap: 4px;
    }

    .active-tab {
        background: #2563EB;
        color: #ffffff !important;
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 6px 6px 0 0;
        font-weight: 700;
    }

    .inactive-tab {
        background: #E5E7EB;
        color: #111827 !important;
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 6px 6px 0 0;
        font-weight: 700;
    }

    .selected-info {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
        padding: 10px 12px;
        border-radius: 5px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1e3a8a;
        font-size: 12px;
    }

    .selected-info-item {
        font-weight: 700;
    }

    .selected-info-separator {
        color: #93c5fd;
    }

    .view-only-box {
        margin-top: 12px;
        padding: 9px 12px;
        border-radius: 5px;
        background: #f3f4f6;
        border: 1px solid #d1d5db;
        color: #374151;
        font-size: 12px;
        font-weight: 600;
    }

    .marks-table-wrapper {
        margin-top: 18px;
        overflow-x: auto;
        border: 1px solid #d1d5db;
        border-radius: 5px;
    }

    .marks-table {
        width: 100%;
        border-collapse: collapse;
        background: #ffffff;
        font-size: 12px;
    }

    .marks-table th {
        background: #dbeafe;
        color: #1e3a8a;
        border: 1px solid #cbd5e1;
        padding: 8px 6px;
        text-align: center;
        white-space: nowrap;
        font-weight: 700;
    }

    .marks-table td {
        border: 1px solid #d1d5db;
        padding: 7px 6px;
        white-space: nowrap;
        vertical-align: middle;
    }

    .marks-table tbody tr:hover {
        background: #f8fafc;
    }

    .center {
        text-align: center;
    }

    .student-name-cell {
        min-width: 280px;
        white-space: normal !important;
        font-weight: 600;
    }

    .readonly-mark {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 58px;
        height: 28px;
        padding: 3px 8px;
        background: #f3f4f6;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        color: #111827;
        font-size: 12px;
        font-weight: 600;
    }

    .readonly-mark.absent {
        background: #fee2e2;
        border-color: #fca5a5;
        color: #991b1b;
    }

    .status-pass {
        display: inline-block;
        background: #dcfce7;
        color: #166534;
        border: 1px solid #86efac;
        padding: 4px 9px;
        border-radius: 4px;
        font-weight: 700;
        font-size: 11px;
    }

    .status-fail {
        display: inline-block;
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
        padding: 4px 9px;
        border-radius: 4px;
        font-weight: 700;
        font-size: 11px;
    }

    .status-absent {
        display: inline-block;
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
        padding: 4px 9px;
        border-radius: 4px;
        font-weight: 700;
        font-size: 11px;
    }

    .status-other {
        display: inline-block;
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #d1d5db;
        padding: 4px 9px;
        border-radius: 4px;
        font-weight: 700;
        font-size: 11px;
    }

    .student-count {
        display: inline-flex;
        align-items: center;
        background: #dbeafe;
        color: #1e40af;
        padding: 6px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }

    .error-box {
        margin-top: 12px;
        padding: 10px 12px;
        border-radius: 5px;
        background: #fef2f2;
        border: 1px solid #fca5a5;
        color: #991b1b;
        font-size: 12px;
        font-weight: 600;
    }

    .warning-box {
        margin-top: 12px;
        padding: 10px 12px;
        border-radius: 5px;
        background: #fffbeb;
        border: 1px solid #fcd34d;
        color: #92400e;
        font-size: 12px;
        font-weight: 600;
    }

</style>


<div class="erp-page marks-view-page">

    <div class="marks-view-card">

        {{-- =========================================================
             TITLE
        ========================================================== --}}

        <h2 class="marks-view-title">
            Examination Marks
        </h2>


        {{-- =========================================================
             TABS
        ========================================================== --}}

        <div class="tabs-container">

            <a
                href="{{ route('marks-entry.index', array_filter([
                    'exam_master_id' =>
                        request('exam_master_id'),

                    'teacher_subject_allocation_id' =>
                        request('teacher_subject_allocation_id'),
                ], fn($value) => $value !== null && $value !== '')) }}"
                class="inactive-tab"
            >
                Marks Entry
            </a>


            <a
                href="{{ request()->fullUrl() }}"
                class="active-tab"
            >
                View Marks
            </a>

        </div>


        {{-- =========================================================
             ERROR
        ========================================================== --}}

        @if(!empty($error))

            <div class="error-box">
                {{ $error }}
            </div>

        @endif


        @if(session('error'))

            <div class="error-box">
                {{ session('error') }}
            </div>

        @endif


        {{-- =========================================================
             SELECTED INFORMATION
        ========================================================== --}}

        @if($selectedTsa && $exam)

            @php

                $teacherName =
                    optional(
                        optional(
                            $selectedTsa->allocation
                        )->teacher
                    )->name
                    ?? 'Teacher';

                $selectedSubjectName =
                    optional(
                        $selectedSubject
                    )->subject_name
                    ?? 'Subject';

                $standardName =
                    optional(
                        optional(
                            $selectedTsa->allocation
                        )->standard
                    )->standard_name
                    ?? '';

                $divisionName =
                    optional(
                        optional(
                            $selectedTsa->allocation
                        )->division
                    )->division_name
                    ?? '';

            @endphp


            <div class="selected-info">

                <span>
                    <span class="selected-info-item">
                        Teacher:
                    </span>
                    {{ $teacherName }}
                </span>


                <span class="selected-info-separator">
                    |
                </span>


                <span>
                    <span class="selected-info-item">
                        Subject:
                    </span>
                    {{ $selectedSubjectName }}
                </span>


                <span class="selected-info-separator">
                    |
                </span>


                <span>
                    <span class="selected-info-item">
                        Class:
                    </span>

                    {{ $standardName }}

                    @if($divisionName !== '')
                        - {{ $divisionName }}
                    @endif

                </span>


                <span class="selected-info-separator">
                    |
                </span>


                <span>
                    <span class="selected-info-item">
                        Exam:
                    </span>

                    {{
                        $exam->display_exam_name
                        ?? $exam->exam_name
                    }}

                </span>


                <span class="selected-info-separator">
                    |
                </span>


                <span>
                    <span class="selected-info-item">
                        Students:
                    </span>

                    {{ $records->count() }}

                </span>

            </div>


            <div class="view-only-box">

                View Only — Marks cannot be modified from this page.

            </div>

        @endif


        {{-- =========================================================
             MARKS TABLE
        ========================================================== --}}

        @if($records->count() > 0)

            <div class="marks-table-wrapper">

                <table class="marks-table">

                    <thead>

                        <tr>

                            <th>
                                GR No
                            </th>

                            <th>
                                Roll No
                            </th>

                            <th>
                                Student Name
                            </th>


                            @if($showTheory)

                                <th>
                                    Theory Max
                                </th>

                                <th>
                                    Theory Pass
                                </th>

                                <th>
                                    Theory Obtained
                                </th>

                            @endif


                            @if($showOral)

                                <th>
                                    Oral Max
                                </th>

                                <th>
                                    Oral Pass
                                </th>

                                <th>
                                    Oral Obtained
                                </th>

                            @endif


                            @if($showPractical)

                                <th>
                                    Practical Max
                                </th>

                                <th>
                                    Practical Pass
                                </th>

                                <th>
                                    Practical Obtained
                                </th>

                            @endif


                            {{-- STATUS IS REQUIRED --}}

                            <th>
                                Status
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    @foreach($records as $row)

                        @php

                            /*
                            |--------------------------------------------------------------------------
                            | COMPLETE STUDENT NAME
                            |--------------------------------------------------------------------------
                            */

                            $studentName =
                                trim(
                                    (string)(
                                        $row->studname
                                        ?? ''
                                    )
                                );

                            $fatherName =
                                trim(
                                    (string)(
                                        $row->fathername
                                        ?? ''
                                    )
                                );

                            $fullStudentName =
                                trim(
                                    $studentName
                                    . ' '
                                    . $fatherName
                                );

                            if (
                                $fullStudentName === ''
                            ) {

                                $fullStudentName = '-';
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | GR / ROLL
                            |--------------------------------------------------------------------------
                            */

                            $grNo =
                                $row->regno
                                ?? $row->registration_no
                                ?? $row->gr_no
                                ?? '-';

                            $rollNo =
                                $row->rollno
                                ?? $row->roll_no
                                ?? '-';


                            /*
                            |--------------------------------------------------------------------------
                            | ABSENT
                            |--------------------------------------------------------------------------
                            */

                            $isAbsent =
                                isset($row->is_absent)
                                &&
                                (int)$row->is_absent === 1;

                            if (
                                !$isAbsent &&
                                isset($row->status)
                            ) {

                                $isAbsent =
                                    strtoupper(
                                        trim(
                                            (string)$row->status
                                        )
                                    ) === 'AB';
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | MARKS
                            |--------------------------------------------------------------------------
                            */

                            $theoryObtained =
                                $row->theory_obtained_marks
                                ?? null;

                            $oralObtained =
                                $row->oral_obtained_marks
                                ?? null;

                            $practicalObtained =
                                $row->practical_obtained_marks
                                ?? null;


                            /*
                            |--------------------------------------------------------------------------
                            | STATUS
                            |--------------------------------------------------------------------------
                            |
                            | Keep Status column.
                            |
                            | Prefer saved status if available.
                            | If absent, display ABSENT.
                            | Otherwise display PRESENT.
                            |
                            |--------------------------------------------------------------------------
                            */

                            $statusText =
                                strtoupper(
                                    trim(
                                        (string)(
                                            $row->status
                                            ?? ''
                                        )
                                    )
                                );

                            if ($isAbsent) {

                                $statusText = 'ABSENT';

                            } elseif (
                                $statusText === ''
                            ) {

                                $statusText = 'PRESENT';
                            }

                        @endphp


                        <tr>

                            {{-- GR NO --}}

                            <td class="center">
                                {{ $grNo }}
                            </td>


                            {{-- ROLL NO --}}

                            <td class="center">
                                {{ $rollNo }}
                            </td>


                            {{-- STUDENT NAME --}}

                            <td
                                class="student-name-cell"
                                title="{{ $fullStudentName }}"
                            >
                                {{ $fullStudentName }}
                            </td>


                            {{-- THEORY --}}

                            @if($showTheory)

                                <td class="center">

                                    {{
                                        isset($row->theory_max_marks)
                                            ? (int)$row->theory_max_marks
                                            : '-'
                                    }}

                                </td>


                                <td class="center">

                                    {{
                                        isset($row->theory_passing_marks)
                                            ? (int)$row->theory_passing_marks
                                            : '-'
                                    }}

                                </td>


                                <td class="center">

                                    @if($isAbsent)

                                        <span class="readonly-mark absent">
                                            AB
                                        </span>

                                    @elseif(
                                        $theoryObtained !== null &&
                                        $theoryObtained !== ''
                                    )

                                        <span class="readonly-mark">

                                            {{
                                                floor(
                                                    (float)$theoryObtained
                                                ) ===
                                                (float)$theoryObtained
                                                    ? (int)$theoryObtained
                                                    : number_format(
                                                        (float)$theoryObtained,
                                                        2
                                                    )
                                            }}

                                        </span>

                                    @else

                                        <span class="readonly-mark">
                                            -
                                        </span>

                                    @endif

                                </td>

                            @endif


                            {{-- ORAL --}}

                            @if($showOral)

                                <td class="center">

                                    {{
                                        isset($row->oral_max_marks)
                                            ? (int)$row->oral_max_marks
                                            : '-'
                                    }}

                                </td>


                                <td class="center">

                                    {{
                                        isset($row->oral_passing_marks)
                                            ? (int)$row->oral_passing_marks
                                            : '-'
                                    }}

                                </td>


                                <td class="center">

                                    @if($isAbsent)

                                        <span class="readonly-mark absent">
                                            AB
                                        </span>

                                    @elseif(
                                        $oralObtained !== null &&
                                        $oralObtained !== ''
                                    )

                                        <span class="readonly-mark">

                                            {{
                                                floor(
                                                    (float)$oralObtained
                                                ) ===
                                                (float)$oralObtained
                                                    ? (int)$oralObtained
                                                    : number_format(
                                                        (float)$oralObtained,
                                                        2
                                                    )
                                            }}

                                        </span>

                                    @else

                                        <span class="readonly-mark">
                                            -
                                        </span>

                                    @endif

                                </td>

                            @endif


                            {{-- PRACTICAL --}}

                            @if($showPractical)

                                <td class="center">

                                    {{
                                        isset($row->practical_max_marks)
                                            ? (int)$row->practical_max_marks
                                            : '-'
                                    }}

                                </td>


                                <td class="center">

                                    {{
                                        isset($row->practical_passing_marks)
                                            ? (int)$row->practical_passing_marks
                                            : '-'
                                    }}

                                </td>


                                <td class="center">

                                    @if($isAbsent)

                                        <span class="readonly-mark absent">
                                            AB
                                        </span>

                                    @elseif(
                                        $practicalObtained !== null &&
                                        $practicalObtained !== ''
                                    )

                                        <span class="readonly-mark">

                                            {{
                                                floor(
                                                    (float)$practicalObtained
                                                ) ===
                                                (float)$practicalObtained
                                                    ? (int)$practicalObtained
                                                    : number_format(
                                                        (float)$practicalObtained,
                                                        2
                                                    )
                                            }}

                                        </span>

                                    @else

                                        <span class="readonly-mark">
                                            -
                                        </span>

                                    @endif

                                </td>

                            @endif


                            {{-- =================================================
                                 STATUS
                            ================================================== --}}

                            <td class="center">

                                @if($statusText === 'PASS')

                                    <span class="status-pass">
                                        PASS
                                    </span>

                                @elseif($statusText === 'FAIL')

                                    <span class="status-fail">
                                        FAIL
                                    </span>

                                @elseif($statusText === 'ABSENT')

                                    <span class="status-absent">
                                        ABSENT
                                    </span>

                                @else

                                    <span class="status-other">
                                        {{ $statusText }}
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @endforeach

                    </tbody>

                </table>

            </div>


            {{-- =========================================================
                 STUDENT COUNT
            ========================================================== --}}

            <div style="
                margin-top:15px;
                display:flex;
                justify-content:flex-end;
            ">

                <span class="student-count">
                    {{ $records->count() }} Students
                </span>

            </div>


        @elseif(
            request()->filled(
                'teacher_subject_allocation_id'
            )
        )

            <div class="warning-box">
                No marks have been entered for the selected teaching assignment.
            </div>

        @endif

    </div>

</div>

</x-app-layout>