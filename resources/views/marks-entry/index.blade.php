<x-app-layout>

<style>

    /*
    |--------------------------------------------------------------------------
    | PAGE
    |--------------------------------------------------------------------------
    */

    .marks-entry-page,
    .marks-entry-page * {
        box-sizing: border-box;
        font-family: Arial, sans-serif !important;
    }


    /*
    |--------------------------------------------------------------------------
    | CARD
    |--------------------------------------------------------------------------
    */

    .marks-entry-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,.06);
        padding: 20px;
    }


    /*
    |--------------------------------------------------------------------------
    | TITLE
    |--------------------------------------------------------------------------
    */

    .marks-entry-title {
        margin: 0 0 15px;
        font-size: 20px;
        font-weight: 700;
        color: #2563eb;
    }


    /*
    |--------------------------------------------------------------------------
    | FILTERS
    |--------------------------------------------------------------------------
    */

    .filter-row {
        display: flex;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 10px;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .filter-label {
        font-size: 12px;
        font-weight: 600;
        color: #374151;
    }

    .filter-select-wrapper {
        position: relative;
        display: inline-block;
    }

    .filter-select {
        height: 34px;
        padding: 5px 30px 5px 10px;
        border: 1px solid #d1d5db;
        border-radius: 5px;
        background: #ffffff;
        color: #111827;
        font-size: 12px;
        outline: none;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
    }

    .filter-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 1px #2563eb;
    }

    .dropdown-arrow {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 6px solid #6b7280;
        pointer-events: none;
    }

    .academic-year-select {
        width: 175px;
    }

    .exam-select {
        width: 270px;
    }

    .assignment-select {
        width: 350px;
    }


    /*
    |--------------------------------------------------------------------------
    | BUTTONS
    |--------------------------------------------------------------------------
    */

    .erp-btn {
        min-height: 34px;
        height: auto;
        min-width: max-content;
        width: auto;
        padding: 7px 16px;
        border: 0;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        line-height: 1.2;
    }

    .erp-btn-save {
        background: #2563eb;
        color: #ffffff;
    }

    .erp-btn-save:hover {
        background: #1d4ed8;
    }

    .erp-btn-green {
        background: #16a34a;
        color: #ffffff;
        min-width: 150px;
    }

    .erp-btn-green:hover {
        background: #15803d;
    }

    .erp-btn-green:disabled {
        background: #9ca3af !important;
        color: #ffffff !important;
        cursor: not-allowed !important;
        opacity: 0.85;
    }


    /*
    |--------------------------------------------------------------------------
    | MESSAGE BOXES
    |--------------------------------------------------------------------------
    */

    .error-box {
        margin-top: 15px;
        padding: 10px 12px;
        border-radius: 5px;
        background: #fef2f2;
        border: 1px solid #fca5a5;
        color: #991b1b;
        font-size: 12px;
    }

    .warning-box {
        margin-top: 15px;
        padding: 10px 12px;
        border-radius: 5px;
        background: #fffbeb;
        border: 1px solid #fcd34d;
        color: #92400e;
        font-size: 12px;
    }

    .saved-box {
        margin-top: 15px;
        padding: 10px 12px;
        border-radius: 5px;
        background: #fffbeb;
        border: 1px solid #f59e0b;
        color: #92400e;
        font-size: 12px;
        font-weight: 600;
    }

    .success-box {
        margin-top: 15px;
        padding: 10px 12px;
        border-radius: 5px;
        background: #ecfdf5;
        border: 1px solid #86efac;
        color: #166534;
        font-size: 12px;
        font-weight: 600;
    }


    /*
    |--------------------------------------------------------------------------
    | SELECTED INFORMATION
    |--------------------------------------------------------------------------
    */

    .selected-info {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 15px;
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


    /*
    |--------------------------------------------------------------------------
    | MARKS TABLE
    |--------------------------------------------------------------------------
    */

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
    }


    /*
    |--------------------------------------------------------------------------
    | MARK INPUT
    |--------------------------------------------------------------------------
    */

    .mark-input {
        width: 62px;
        height: 30px;
        padding: 3px;
        border: 1px solid #9ca3af;
        border-radius: 4px;
        text-align: center;
        font-size: 13px;
        -moz-appearance: textfield;
        appearance: textfield;
    }

    .mark-input::-webkit-outer-spin-button,
    .mark-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .mark-input:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 1px #2563eb;
    }

    .mark-input:read-only {
        background: #f3f4f6;
        color: #6b7280;
        cursor: not-allowed;
    }


    /*
    |--------------------------------------------------------------------------
    | ATTENDANCE
    |--------------------------------------------------------------------------
    */

    .attendance-btn {
        min-width: 82px;
        padding: 5px 9px;
        border: 0;
        border-radius: 4px;
        color: #ffffff;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
    }

    .attendance-btn:focus {
        outline: none;
    }

    .present-btn {
        background: #16a34a;
    }

    .present-btn:hover {
        background: #15803d;
    }

    .absent-btn {
        background: #dc2626;
    }

    .absent-btn:hover {
        background: #b91c1c;
    }


    /*
    |--------------------------------------------------------------------------
    | OPTIONAL BUTTON
    |--------------------------------------------------------------------------
    */

    .optional-btn {
        min-width: 82px;
        padding: 5px 9px;
        border: 0;
        border-radius: 4px;
        color: #ffffff;
        background: #6b7280;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
    }

    .optional-btn:focus {
        outline: none;
    }

    .optional-btn:hover {
        background: #4b5563;
    }

    .optional-active-btn {
        background: #d97706 !important;
    }

    .optional-active-btn:hover {
        background: #b45309 !important;
    }

    .status-present {
        color: #16a34a;
        font-weight: 700;
    }

    .status-absent {
        color: #dc2626;
        font-weight: 700;
    }

    .status-optional {
        color: #d97706;
        font-weight: 700;
    }


    /*
    |--------------------------------------------------------------------------
    | OPTIONAL HEADER
    |--------------------------------------------------------------------------
    */

    .optional-header {
        background: #fef3c7 !important;
        color: #92400e !important;
    }


    /*
    |--------------------------------------------------------------------------
    | STATUS CELL
    |--------------------------------------------------------------------------
    */

    .status-cell-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
    }


    /*
    |--------------------------------------------------------------------------
    | ACTION ROW
    |--------------------------------------------------------------------------
    */

    .marks-action-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 15px;
        flex-wrap: wrap;
    }

    .student-count {
        margin-left: auto;
        background: #dbeafe;
        color: #1e40af;
        padding: 6px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }


    /*
    |--------------------------------------------------------------------------
    | MOBILE
    |--------------------------------------------------------------------------
    */

    @media (max-width: 900px) {

        .filter-group {
            width: 100%;
        }

        .academic-year-select,
        .exam-select,
        .assignment-select {
            width: 100%;
        }

        .student-count {
            margin-left: 0;
        }
    }

</style>


<div class="erp-page marks-entry-page">

    <div class="marks-entry-card">

        {{-- ==========================================================
             TITLE
        =========================================================== --}}

        <h2 class="marks-entry-title">
            Examination Marks
        </h2>


        {{-- ==========================================================
             SUCCESS MESSAGE
        =========================================================== --}}

        @if(session('success'))

            <div class="success-box">
                ✓ {{ session('success') }}
            </div>

        @endif


        {{-- ==========================================================
             ERROR MESSAGE
        =========================================================== --}}

        @if(session('error'))

            <div class="error-box">

                <strong>Error:</strong>

                {{ session('error') }}

            </div>

        @endif


        {{-- ==========================================================
             VALIDATION ERRORS
        =========================================================== --}}

        @if($errors->any())

            <div class="error-box">

                <strong>Please correct the following:</strong>

                <ul style="margin:6px 0 0 20px;">

                    @foreach($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        {{-- ==========================================================
             SAVED BUT NOT SUBMITTED
        =========================================================== --}}

        @if(
            request()->boolean('marks_saved') &&
            !$marksLocked
        )

            <div class="saved-box">

                ⚠ Marks are saved but
                <strong>NOT finally submitted</strong>.

                Please click
                <strong>Submit Final Marks</strong>
                to complete the submission.

            </div>

        @endif


        {{-- ==========================================================
             LOCK MESSAGE
        =========================================================== --}}

        @if($marksLocked)

            <div class="warning-box">

                <strong>
                    Marks entry has already been completed and is locked.
                </strong>

                <br>

                Contact Admin for modification.

            </div>

        @endif


        {{-- ==========================================================
             FILTER FORM
        =========================================================== --}}

        <form
            method="GET"
            action="{{ route('marks-entry.index') }}"
            id="marksFilterForm"
        >

            <div class="filter-row">

                {{-- ==================================================
                     ACADEMIC YEAR
                =================================================== --}}

                <div class="filter-group">

                    <label
                        class="filter-label"
                        for="academic_year_id"
                    >
                        Academic Year
                    </label>

                    <div class="filter-select-wrapper">

                        <select
                            name="academic_year_id"
                            id="academic_year_id"
                            class="filter-select academic-year-select"
                        >

                            <option value="">
                                All Academic Years
                            </option>

                            @foreach($academicYears as $year)

                                <option
                                    value="{{ $year->id }}"
                                    {{ (string)request('academic_year_id') === (string)$year->id ? 'selected' : '' }}
                                >
                                    {{
                                        $year->year_name
                                        ?? $year->name
                                        ?? $year->id
                                    }}
                                </option>

                            @endforeach

                        </select>

                        <span class="dropdown-arrow"></span>

                    </div>

                </div>


                {{-- ==================================================
                     EXAM
                =================================================== --}}

                <div class="filter-group">

                    <label
                        class="filter-label"
                        for="exam_master_id"
                    >
                        Exam
                    </label>

                    <div class="filter-select-wrapper">

                        <select
                            name="exam_master_id"
                            id="exam_master_id"
                            class="filter-select exam-select"
                        >

                            <option value="">
                                Select Exam
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

                        <span class="dropdown-arrow"></span>

                    </div>

                </div>


                {{-- ==================================================
                     TEACHING ASSIGNMENT
                =================================================== --}}

                <div class="filter-group">

                    <label
                        class="filter-label"
                        for="teacher_subject_allocation_id"
                    >
                        Teaching Assignment
                    </label>

                    <div class="filter-select-wrapper">

                        <select
                            name="teacher_subject_allocation_id"
                            id="teacher_subject_allocation_id"
                            class="filter-select assignment-select"
                            {{ !request()->filled('exam_master_id') ? 'disabled' : '' }}
                        >

                            @if(!request()->filled('exam_master_id'))

                                <option value="">
                                    Select Exam First
                                </option>

                            @elseif($assignments->isEmpty())

                                <option value="">
                                    No Teaching Assignment
                                </option>

                            @else

                                <option value="">
                                    Select Teaching Assignment
                                </option>

                                @foreach($assignments as $assignment)

                                    @php

                                        $teacherName =
                                            optional(
                                                optional(
                                                    $assignment->allocation
                                                )->teacher
                                            )->name
                                            ?? 'Teacher';

                                        $subjectName =
                                            optional(
                                                $assignment->subject
                                            )->subject_name
                                            ?? 'Subject';

                                        $standardName =
                                            optional(
                                                optional(
                                                    $assignment->allocation
                                                )->standard
                                            )->standard_name
                                            ?? '';

                                        $divisionName =
                                            optional(
                                                optional(
                                                    $assignment->allocation
                                                )->division
                                            )->division_name
                                            ?? '';

                                    @endphp

                                    <option
                                        value="{{ $assignment->id }}"
                                        {{ (string)request('teacher_subject_allocation_id') === (string)$assignment->id ? 'selected' : '' }}
                                    >
                                        {{ $teacherName }}
                                        -
                                        {{ $subjectName }}
                                        -
                                        {{ $standardName }}

                                        @if($divisionName)
                                            -
                                            {{ $divisionName }}
                                        @endif
                                    </option>

                                @endforeach

                            @endif

                        </select>

                        <span class="dropdown-arrow"></span>

                    </div>

                </div>


                {{-- ==================================================
                     LOAD
                =================================================== --}}

                <div class="filter-group">

                    <button
                        type="submit"
                        class="erp-btn erp-btn-save"
                        id="loadStudentsButton"
                    >
                        Load Students
                    </button>

                </div>

            </div>

        </form>


        {{-- ==========================================================
             FORMAT MARKS
        =========================================================== --}}

        @php

            $formatMark = function ($value) {

                if (
                    $value === null ||
                    $value === ''
                ) {
                    return '';
                }

                $number = (float) $value;

                if (
                    floor($number) == $number
                ) {
                    return (string) (int) $number;
                }

                return rtrim(
                    rtrim(
                        number_format(
                            $number,
                            2,
                            '.',
                            ''
                        ),
                        '0'
                    ),
                    '.'
                );
            };


            $selectedAllocationForOptional =
                $teacherSubjectAllocation?->allocation
                ?? $selectedClassAllocation
                ?? null;


            $selectedStandardId =
                (int) (
                    $selectedAllocationForOptional?->standard_id
                    ?? 0
                );


            $optionalStandardIds = [
                19,
                20,
                21,
                22,
                23,
                24,
            ];


            $showOptionalColumn =
                (bool) (
                    $isOptionalEnabled
                    ?? false
                )
                ||
                in_array(
                    $selectedStandardId,
                    $optionalStandardIds,
                    true
                );

        @endphp


        {{-- ==========================================================
             SELECTED INFORMATION
        =========================================================== --}}

        @if(
            $teacherSubjectAllocation &&
            $exam
        )

            <div class="selected-info">

                <span>

                    <span class="selected-info-item">
                        Teacher:
                    </span>

                    {{
                        optional(
                            optional(
                                $teacherSubjectAllocation
                                    ->allocation
                            )->teacher
                        )->name
                        ?? 'Teacher'
                    }}

                </span>


                <span class="selected-info-separator">
                    |
                </span>


                <span>

                    <span class="selected-info-item">
                        Subject:
                    </span>

                    {{
                        optional(
                            $teacherSubjectAllocation->subject
                        )->subject_name
                        ?? 'Subject'
                    }}

                </span>


                <span class="selected-info-separator">
                    |
                </span>


                <span>

                    <span class="selected-info-item">
                        Class:
                    </span>

                    {{
                        optional(
                            optional(
                                $teacherSubjectAllocation
                                    ->allocation
                            )->standard
                        )->standard_name
                        ?? ''
                    }}

                    @if(
                        optional(
                            optional(
                                $teacherSubjectAllocation
                                    ->allocation
                            )->division
                        )->division_name
                    )

                        -
                        {{
                            optional(
                                optional(
                                    $teacherSubjectAllocation
                                        ->allocation
                                )->division
                            )->division_name
                        }}

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

                    {{ $students->count() }}

                </span>


                @if($showOptionalColumn)

                    <span class="selected-info-separator">
                        |
                    </span>

                    <span
                        style="
                            color:#b45309;
                            font-weight:700;
                        "
                    >
                        Optional Selection: Enabled
                    </span>

                @endif

            </div>

        @endif


        {{-- ==========================================================
             MARKS TABLE
        =========================================================== --}}

        @if(
            isset($students) &&
            $students->count() > 0
        )

            <div class="marks-table-wrapper">

                {{-- ==================================================
                     SAVE MARKS FORM
                =================================================== --}}

                <form
                    method="POST"
                    action="{{ route('marks-entry.save') }}"
                    id="marksSaveForm"
                >

                    @csrf


                    <input
                        type="hidden"
                        name="academic_year_id"
                        value="{{ request('academic_year_id') }}"
                    >


                    <input
                        type="hidden"
                        name="exam_master_id"
                        value="{{ request('exam_master_id') }}"
                    >


                    <input
                        type="hidden"
                        name="teacher_subject_allocation_id"
                        value="{{ request('teacher_subject_allocation_id') }}"
                    >


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

                                <th>
                                    Attendance
                                </th>


                                @if($showOptionalColumn)

                                    <th class="optional-header">
                                        Optional
                                    </th>

                                @endif


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


                                <th>
                                    Status
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                        @foreach(
                            $students->sortBy(function ($student) {

                                $rollNo =
                                    $student->rollno
                                    ??
                                    $student->roll_no
                                    ??
                                    $student->roll_number
                                    ??
                                    $student->roll
                                    ??
                                    null;

                                if (
                                    $rollNo === null ||
                                    $rollNo === ''
                                ) {
                                    return PHP_INT_MAX;
                                }

                                return is_numeric($rollNo)
                                    ? (int) $rollNo
                                    : PHP_INT_MAX;

                            })->values()
                            as $record
                        )

                            @php

                                $studentId =
                                    $record->Studentid
                                    ??
                                    $record->student_id
                                    ??
                                    $record->id;


                                $studentMark =
                                    $existingMarks[$studentId]
                                    ?? null;


                                $isAbsent =
                                    $studentMark &&
                                    (int)
                                    (
                                        $studentMark->is_absent
                                        ?? 0
                                    ) === 1;


                                $isOptional =
                                    $studentMark &&
                                    (int)
                                    (
                                        $studentMark->is_optional
                                        ?? 0
                                    ) === 1;


                                $studentName =
                                    trim(
                                        (string) (
                                            $record->studname
                                            ?? ''
                                        )
                                    );


                                $fatherName =
                                    trim(
                                        (string) (
                                            $record->fathername
                                            ?? ''
                                        )
                                    );


                                $studentFullName =
                                    trim(
                                        $studentName
                                        . ' '
                                        . $fatherName
                                    );


                                $marksReadOnly =
                                    $isAbsent ||
                                    $isOptional ||
                                    $marksLocked;


                                $theoryObtainedValue =
                                    $formatMark(
                                        $studentMark
                                            ? $studentMark->theory_obtained_marks
                                            : null
                                    );


                                $oralObtainedValue =
                                    $formatMark(
                                        $studentMark
                                            ? $studentMark->oral_obtained_marks
                                            : null
                                    );


                                $practicalObtainedValue =
                                    $formatMark(
                                        $studentMark
                                            ? $studentMark->practical_obtained_marks
                                            : null
                                    );


                                if (
                                    old(
                                        'theory_marks.' . $studentId,
                                        null
                                    ) !== null
                                ) {

                                    $theoryObtainedValue =
                                        $formatMark(
                                            old(
                                                'theory_marks.' . $studentId
                                            )
                                        );

                                }


                                if (
                                    old(
                                        'oral_marks.' . $studentId,
                                        null
                                    ) !== null
                                ) {

                                    $oralObtainedValue =
                                        $formatMark(
                                            old(
                                                'oral_marks.' . $studentId
                                            )
                                        );

                                }


                                if (
                                    old(
                                        'practical_marks.' . $studentId,
                                        null
                                    ) !== null
                                ) {

                                    $practicalObtainedValue =
                                        $formatMark(
                                            old(
                                                'practical_marks.' . $studentId
                                            )
                                        );

                                }

                            @endphp


                            <tr>

                                {{-- ==================================================
                                     GR NO
                                =================================================== --}}

                                <td class="center">

                                    {{
                                        $record->regno
                                        ??
                                        $record->registration_no
                                        ??
                                        $record->gr_no
                                        ??
                                        '-'
                                    }}

                                    <input
                                        type="hidden"
                                        name="student_ids[]"
                                        value="{{ $studentId }}"
                                    >

                                </td>


                                {{-- ==================================================
                                     ROLL NO
                                =================================================== --}}

                                <td class="center">

                                    {{
                                        $record->rollno
                                        ??
                                        $record->roll_no
                                        ??
                                        $record->roll_number
                                        ??
                                        $record->roll
                                        ??
                                        '-'
                                    }}

                                </td>


                                {{-- ==================================================
                                     STUDENT NAME
                                =================================================== --}}

                                <td class="student-name-cell">

                                    {{ $studentFullName ?: '-' }}

                                </td>


                                {{-- ==================================================
                                     ATTENDANCE
                                =================================================== --}}

                                <td class="center">

                                    <input
                                        type="hidden"
                                        name="is_absent[{{ $studentId }}]"
                                        id="absent_{{ $studentId }}"
                                        value="{{ $isAbsent ? 1 : 0 }}"
                                    >


                                    @if(!$marksLocked)

                                        <button
                                            type="button"
                                            id="btn_{{ $studentId }}"
                                            class="attendance-btn {{ $isAbsent ? 'absent-btn' : 'present-btn' }}"
                                            onclick="toggleAbsent('{{ $studentId }}', event)"
                                        >
                                            {{ $isAbsent ? 'ABSENT' : 'PRESENT' }}
                                        </button>

                                    @else

                                        <span
                                            class="{{
                                                $isAbsent
                                                    ? 'status-absent'
                                                    : 'status-present'
                                            }}"
                                        >
                                            {{ $isAbsent ? 'ABSENT' : 'PRESENT' }}
                                        </span>

                                    @endif

                                </td>


                                {{-- ==================================================
                                     OPTIONAL
                                =================================================== --}}

                                @if($showOptionalColumn)

                                    <td class="center">

                                        <input
                                            type="hidden"
                                            name="is_optional[{{ $studentId }}]"
                                            id="optional_{{ $studentId }}"
                                            value="{{ $isOptional ? 1 : 0 }}"
                                        >


                                        @if(!$marksLocked)

                                            <button
                                                type="button"
                                                id="optional_btn_{{ $studentId }}"
                                                class="optional-btn {{ $isOptional ? 'optional-active-btn' : '' }}"
                                                onclick="toggleOptional('{{ $studentId }}', event)"
                                            >
                                                {{ $isOptional ? 'OPTIONAL' : 'NORMAL' }}
                                            </button>

                                        @else

                                            <span
                                                class="{{
                                                    $isOptional
                                                        ? 'status-optional'
                                                        : 'status-present'
                                                }}"
                                            >
                                                {{ $isOptional ? 'OPTIONAL' : 'NORMAL' }}
                                            </span>

                                        @endif

                                    </td>

                                @endif


                                {{-- ==================================================
                                     THEORY
                                =================================================== --}}

                                @if($showTheory)

                                    <td class="center">

                                        {{ (int) $theoryMaxMarks }}

                                    </td>

                                    <td class="center">

                                        {{ (int) $theoryPassingMarks }}

                                    </td>

                                    <td class="center">

                                        <input
                                            type="number"
                                            name="theory_marks[{{ $studentId }}]"
                                            value="{{ $theoryObtainedValue }}"
                                            min="0"
                                            max="{{ (int)$theoryMaxMarks }}"
                                            step="1"
                                            class="mark-input student-{{ $studentId }}"
                                            {{ $marksReadOnly ? 'readonly' : '' }}
                                            {{ !$marksReadOnly ? 'required' : '' }}
                                        >

                                    </td>

                                @endif


                                {{-- ==================================================
                                     ORAL
                                =================================================== --}}

                                @if($showOral)

                                    <td class="center">

                                        {{ (int) $oralMaxMarks }}

                                    </td>

                                    <td class="center">

                                        {{ (int) $oralPassingMarks }}

                                    </td>

                                    <td class="center">

                                        <input
                                            type="number"
                                            name="oral_marks[{{ $studentId }}]"
                                            value="{{ $oralObtainedValue }}"
                                            min="0"
                                            max="{{ (int)$oralMaxMarks }}"
                                            step="1"
                                            class="mark-input student-{{ $studentId }}"
                                            {{ $marksReadOnly ? 'readonly' : '' }}
                                            {{ !$marksReadOnly ? 'required' : '' }}
                                        >

                                    </td>

                                @endif


                                {{-- ==================================================
                                     PRACTICAL
                                =================================================== --}}

                                @if($showPractical)

                                    <td class="center">

                                        {{ (int) $practicalMaxMarks }}

                                    </td>

                                    <td class="center">

                                        {{ (int) $practicalPassingMarks }}

                                    </td>

                                    <td class="center">

                                        <input
                                            type="number"
                                            name="practical_marks[{{ $studentId }}]"
                                            value="{{ $practicalObtainedValue }}"
                                            min="0"
                                            max="{{ (int)$practicalMaxMarks }}"
                                            step="1"
                                            class="mark-input student-{{ $studentId }}"
                                            {{ $marksReadOnly ? 'readonly' : '' }}
                                            {{ !$marksReadOnly ? 'required' : '' }}
                                        >

                                    </td>

                                @endif


                                {{-- ==================================================
                                     STATUS
                                =================================================== --}}

                                <td
                                    class="center"
                                    id="status_{{ $studentId }}"
                                >

                                    <div class="status-cell-wrapper">

                                        @if($isOptional)

                                            <span class="status-optional">
                                                OPTIONAL
                                            </span>

                                        @elseif($isAbsent)

                                            <span class="status-absent">
                                                ABSENT
                                            </span>

                                        @else

                                            <span class="status-present">
                                                PRESENT
                                            </span>

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @endforeach

                        </tbody>

                    </table>


                    {{-- ==========================================================
                         SAVE / FINAL SUBMIT BUTTONS
                    =========================================================== --}}

                    <div class="marks-action-row">

                        @if(!$marksLocked)

                            <button
                                type="submit"
                                class="erp-btn erp-btn-save"
                                id="saveMarksButton"
                                
                            >
                                Save Marks
                            </button>

                        @endif


                        <button
                            type="button"
                            class="erp-btn erp-btn-green"
                            id="submitFinalButton"
                            {{ $marksLocked ? 'disabled' : '' }}
                        >

                            {{
                                $marksLocked
                                    ? 'Marks Submitted'
                                    : 'Submit Final Marks'
                            }}

                        </button>


                        <span class="student-count">

                            {{ $students->count() }}
                            Students

                        </span>

                    </div>

                </form>


                {{-- ==========================================================
                     FINAL SUBMIT FORM
                =========================================================== --}}

                @if($teacherSubjectAllocation)

                    <form
                        method="POST"
                        action="{{ route('marks-entry.submit') }}"
                        id="finalSubmitForm"
                        style="display:none;"
                    >

                        @csrf

                        <input
                            type="hidden"
                            name="academic_year_id"
                            value="{{ request('academic_year_id') }}"
                        >

                        <input
                            type="hidden"
                            name="exam_master_id"
                            value="{{ request('exam_master_id') }}"
                        >

                        <input
                            type="hidden"
                            name="teacher_subject_allocation_id"
                            value="{{ request('teacher_subject_allocation_id') }}"
                        >

                    </form>

                @endif

            </div>


        @elseif(
            request()->filled(
                'teacher_subject_allocation_id'
            ) &&
            !$marksLocked
        )

            <div class="warning-box">

                No students found for the selected teaching assignment.

                Please verify the Old ERP student mapping.

            </div>

        @endif

    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>

(function () {

    'use strict';


    /*
    |--------------------------------------------------------------------------
    | COMMON HELPERS
    |--------------------------------------------------------------------------
    */

    function getElement(id)
    {
        return document.getElementById(id);
    }


    function getStudentInputs(studentId)
    {
        return document.querySelectorAll(
            '.student-' + studentId
        );
    }


    function resetInputStyle(input)
    {
        input.style.background = '';
        input.style.border = '1px solid #9ca3af';
    }


    function setInputEnabled(input)
    {
        input.readOnly = false;
        input.required = true;

        /*
        |--------------------------------------------------------------------------
        | Remove automatic zero when returning to PRESENT/NORMAL
        |--------------------------------------------------------------------------
        */

        if (input.value === '0') {
            input.value = '';
        }

        resetInputStyle(input);
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE ATTENDANCE BUTTON
    |--------------------------------------------------------------------------
    */

    function updateAttendanceButton(
        studentId,
        isAbsent
    )
    {
        const button =
            getElement(
                'btn_' + studentId
            );

        if (!button) {
            return;
        }

        button.type = 'button';

        if (isAbsent) {

            button.textContent =
                'ABSENT';

            button.classList.remove(
                'present-btn'
            );

            button.classList.add(
                'absent-btn'
            );

        } else {

            button.textContent =
                'PRESENT';

            button.classList.remove(
                'absent-btn'
            );

            button.classList.add(
                'present-btn'
            );

        }
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE OPTIONAL BUTTON
    |--------------------------------------------------------------------------
    */

    function updateOptionalButton(
        studentId,
        isOptional
    )
    {
        const button =
            getElement(
                'optional_btn_' + studentId
            );

        if (!button) {
            return;
        }

        button.type = 'button';

        if (isOptional) {

            button.textContent =
                'OPTIONAL';

            button.classList.add(
                'optional-active-btn'
            );

        } else {

            button.textContent =
                'NORMAL';

            button.classList.remove(
                'optional-active-btn'
            );

        }
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE STATUS
    |--------------------------------------------------------------------------
    */

    function updateStatus(
        studentId,
        statusType
    )
    {
        const status =
            getElement(
                'status_' + studentId
            );

        if (!status) {
            return;
        }


        if (statusType === 'ABSENT') {

            status.innerHTML =
                '<div class="status-cell-wrapper">' +
                    '<span class="status-absent">ABSENT</span>' +
                '</div>';

        }
        else if (statusType === 'OPTIONAL') {

            status.innerHTML =
                '<div class="status-cell-wrapper">' +
                    '<span class="status-optional">OPTIONAL</span>' +
                '</div>';

        }
        else {

            status.innerHTML =
                '<div class="status-cell-wrapper">' +
                    '<span class="status-present">PRESENT</span>' +
                '</div>';

        }
    }


    /*
    |--------------------------------------------------------------------------
    | SET PRESENT
    |--------------------------------------------------------------------------
    */

    function setPresentState(studentId)
    {
        const absentFlag =
            getElement(
                'absent_' + studentId
            );

        const optionalFlag =
            getElement(
                'optional_' + studentId
            );


        if (!absentFlag) {
            return;
        }


        absentFlag.value =
            '0';


        if (optionalFlag) {

            optionalFlag.value =
                '0';

        }


        updateAttendanceButton(
            studentId,
            false
        );


        updateOptionalButton(
            studentId,
            false
        );


        updateStatus(
            studentId,
            'PRESENT'
        );


        getStudentInputs(studentId).forEach(
            function (input) {

                setInputEnabled(
                    input
                );

            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SET ABSENT
    |--------------------------------------------------------------------------
    */

    function setAbsentState(studentId)
    {
        const absentFlag =
            getElement(
                'absent_' + studentId
            );

        const optionalFlag =
            getElement(
                'optional_' + studentId
            );


        if (!absentFlag) {
            return;
        }


        absentFlag.value =
            '1';


        if (optionalFlag) {

            optionalFlag.value =
                '0';

        }


        updateAttendanceButton(
            studentId,
            true
        );


        updateOptionalButton(
            studentId,
            false
        );


        updateStatus(
            studentId,
            'ABSENT'
        );


        getStudentInputs(studentId).forEach(
            function (input) {

                input.value =
                    '0';

                input.readOnly =
                    true;

                input.required =
                    false;

                input.style.background =
                    '#fee2e2';

                input.style.border =
                    '1px solid #fca5a5';

            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SET OPTIONAL
    |--------------------------------------------------------------------------
    */

    function setOptionalState(studentId)
    {
        const absentFlag =
            getElement(
                'absent_' + studentId
            );

        const optionalFlag =
            getElement(
                'optional_' + studentId
            );


        if (!optionalFlag) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Optional is always Present
        |--------------------------------------------------------------------------
        */

        if (absentFlag) {

            absentFlag.value =
                '0';

        }


        optionalFlag.value =
            '1';


        updateAttendanceButton(
            studentId,
            false
        );


        updateOptionalButton(
            studentId,
            true
        );


        updateStatus(
            studentId,
            'OPTIONAL'
        );


        getStudentInputs(studentId).forEach(
            function (input) {

                input.value =
                    '0';

                input.readOnly =
                    true;

                input.required =
                    false;

                input.style.background =
                    '#fff7ed';

                input.style.border =
                    '1px solid #f59e0b';

            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SET NORMAL
    |--------------------------------------------------------------------------
    */

    function setNormalState(studentId)
    {
        const absentFlag =
            getElement(
                'absent_' + studentId
            );

        const optionalFlag =
            getElement(
                'optional_' + studentId
            );


        if (absentFlag) {

            absentFlag.value =
                '0';

        }


        if (optionalFlag) {

            optionalFlag.value =
                '0';

        }


        updateAttendanceButton(
            studentId,
            false
        );


        updateOptionalButton(
            studentId,
            false
        );


        updateStatus(
            studentId,
            'PRESENT'
        );


        getStudentInputs(studentId).forEach(
            function (input) {

                setInputEnabled(
                    input
                );

            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ATTENDANCE TOGGLE
    |--------------------------------------------------------------------------
    |
    | PRESENT -> ABSENT
    | ABSENT  -> PRESENT
    |
    */

    window.toggleAbsent = function (
        studentId,
        event
    )
    {

        if (event) {

            event.preventDefault();
            event.stopPropagation();

        }


        const absentFlag =
            getElement(
                'absent_' + studentId
            );

        const optionalFlag =
            getElement(
                'optional_' + studentId
            );


        if (!absentFlag) {

            console.error(
                'Absent field not found for student:',
                studentId
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | OPTIONAL -> ATTENDANCE
        |--------------------------------------------------------------------------
        */

        if (
            optionalFlag &&
            optionalFlag.value === '1'
        ) {

            Swal.fire({

                icon:
                    'question',

                title:
                    'Student is Optional',

                text:
                    'Remove Optional status before changing Attendance?',

                showCancelButton:
                    true,

                confirmButtonText:
                    'Yes, Continue',

                cancelButtonText:
                    'Cancel'

            }).then(
                function (result) {

                    if (
                        !result.isConfirmed
                    ) {
                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Remove Optional first
                    |--------------------------------------------------------------------------
                    */

                    setNormalState(
                        studentId
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Then ask for Absent
                    |--------------------------------------------------------------------------
                    */

                    Swal.fire({

                        icon:
                            'warning',

                        title:
                            'Confirm Absent',

                        text:
                            'Student will be marked ABSENT and all marks will become 0.',

                        showCancelButton:
                            true,

                        confirmButtonText:
                            'Yes, Mark Absent',

                        cancelButtonText:
                            'Cancel'

                    }).then(
                        function (secondResult) {

                            if (
                                secondResult.isConfirmed
                            ) {

                                setAbsentState(
                                    studentId
                                );

                            }

                        }
                    );

                }
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | PRESENT -> ABSENT
        |--------------------------------------------------------------------------
        */

        if (
            absentFlag.value === '0'
        ) {

            Swal.fire({

                icon:
                    'warning',

                title:
                    'Confirm Absent',

                text:
                    'Student will be marked ABSENT and all marks will become 0.',

                showCancelButton:
                    true,

                confirmButtonText:
                    'Yes, Mark Absent',

                cancelButtonText:
                    'Cancel'

            }).then(
                function (result) {

                    if (
                        !result.isConfirmed
                    ) {
                        return;
                    }


                    setAbsentState(
                        studentId
                    );

                }
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | ABSENT -> PRESENT
        |--------------------------------------------------------------------------
        */

        Swal.fire({

            icon:
                'question',

            title:
                'Confirm Present',

            text:
                'Change student status back to PRESENT?',

            showCancelButton:
                true,

            confirmButtonText:
                'Yes, Present',

            cancelButtonText:
                'Cancel'

        }).then(
            function (result) {

                if (
                    !result.isConfirmed
                ) {
                    return;
                }


                setPresentState(
                    studentId
                );

            }
        );

    };


    /*
    |--------------------------------------------------------------------------
    | OPTIONAL TOGGLE
    |--------------------------------------------------------------------------
    |
    | NORMAL   -> OPTIONAL
    | OPTIONAL -> NORMAL
    |
    */

    window.toggleOptional = function (
        studentId,
        event
    )
    {

        if (event) {

            event.preventDefault();
            event.stopPropagation();

        }


        const optionalFlag =
            getElement(
                'optional_' + studentId
            );

        const absentFlag =
            getElement(
                'absent_' + studentId
            );


        if (!optionalFlag) {

            console.error(
                'Optional field not found for student:',
                studentId
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | ABSENT STUDENT
        |--------------------------------------------------------------------------
        */

        if (
            absentFlag &&
            absentFlag.value === '1'
        ) {

            Swal.fire({

                icon:
                    'warning',

                title:
                    'Student is Absent',

                text:
                    'An absent student cannot be marked as Optional.',

                confirmButtonText:
                    'OK'

            });

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | NORMAL -> OPTIONAL
        |--------------------------------------------------------------------------
        */

        if (
            optionalFlag.value === '0'
        ) {

            Swal.fire({

                icon:
                    'warning',

                title:
                    'Mark Student Optional?',

                text:
                    'This student will be excluded from marks calculation for this subject.',

                showCancelButton:
                    true,

                confirmButtonText:
                    'Yes, Optional',

                cancelButtonText:
                    'Cancel',

                confirmButtonColor:
                    '#d97706'

            }).then(
                function (result) {

                    if (
                        !result.isConfirmed
                    ) {
                        return;
                    }


                    setOptionalState(
                        studentId
                    );

                }
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | OPTIONAL -> NORMAL
        |--------------------------------------------------------------------------
        */

        Swal.fire({

            icon:
                'question',

            title:
                'Remove Optional Status?',

            text:
                'This student will become a normal PRESENT student.',

            showCancelButton:
                true,

            confirmButtonText:
                'Yes, Normal',

            cancelButtonText:
                'Cancel'

        }).then(
            function (result) {

                if (
                    !result.isConfirmed
                ) {
                    return;
                }


                setNormalState(
                    studentId
                );

            }
        );

    };


    /*
    |--------------------------------------------------------------------------
    | MARK INPUT VALIDATION
    |--------------------------------------------------------------------------
    */

    function setupMarkValidation()
    {

        document
            .querySelectorAll(
                '.mark-input'
            )
            .forEach(
                function (input) {

                    input.addEventListener(
                        'input',
                        function () {

                            if (
                                this.readOnly
                            ) {
                                return;
                            }


                            const max =
                                parseFloat(
                                    this.max
                                );


                            const value =
                                this.value.trim();


                            if (
                                value === ''
                            ) {

                                this.style.border =
                                    '1px solid #9ca3af';

                                return;
                            }


                            const number =
                                parseFloat(
                                    value
                                );


                            if (
                                Number.isNaN(number) ||
                                number < 0 ||
                                number > max
                            ) {

                                this.style.border =
                                    '2px solid #dc2626';

                            } else {

                                this.style.border =
                                    '1px solid #16a34a';

                            }

                        }
                    );

                }
            );

    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE MARKS
    |--------------------------------------------------------------------------
    */

    function validateMarksForm()
    {

        const rows =
            document.querySelectorAll(
                '#marksSaveForm tbody tr'
            );


        let hasError =
            false;


        rows.forEach(
            function (row) {

                const optionalField =
                    row.querySelector(
                        'input[name^="is_optional["]'
                    );

                const absentField =
                    row.querySelector(
                        'input[name^="is_absent["]'
                    );


                const isOptional =
                    optionalField &&
                    optionalField.value === '1';


                const isAbsent =
                    absentField &&
                    absentField.value === '1';


                if (isOptional) {
                    return;
                }


                if (isAbsent) {
                    return;
                }


                row
                    .querySelectorAll(
                        '.mark-input'
                    )
                    .forEach(
                        function (input) {

                            if (
                                input.readOnly
                            ) {
                                return;
                            }


                            if (
                                input.value.trim() === ''
                            ) {

                                hasError =
                                    true;

                                input.style.border =
                                    '2px solid #dc2626';

                            }

                        }
                    );

            }
        );


        return !hasError;
    }


    /*
    |--------------------------------------------------------------------------
    | SAVE FORM
    |--------------------------------------------------------------------------
    */

    function setupSaveForm()
    {

        const saveForm =
            getElement(
                'marksSaveForm'
            );


        if (!saveForm) {
            return;
        }


        saveForm.addEventListener(
            'submit',
            function (event) {

                /*
                |--------------------------------------------------------------------------
                | Do not block normal browser submission for empty
                | optional/absent rows.
                |--------------------------------------------------------------------------
                */

                if (
                    !validateMarksForm()
                ) {

                    event.preventDefault();


                    Swal.fire({

                        icon:
                            'error',

                        title:
                            'Validation Error',

                        text:
                            'Please enter marks for all present students.',

                        confirmButtonText:
                            'OK'

                    });

                }

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | FINAL SUBMISSION
    |--------------------------------------------------------------------------
    */

    function setupFinalSubmit()
    {

        const submitFinalButton =
            getElement(
                'submitFinalButton'
            );

        const finalSubmitForm =
            getElement(
                'finalSubmitForm'
            );


        if (
            !submitFinalButton ||
            !finalSubmitForm
        ) {
            return;
        }


        submitFinalButton.addEventListener(
            'click',
            function (event) {

                event.preventDefault();
                event.stopPropagation();


                if (
                    submitFinalButton.disabled
                ) {
                    return;
                }


                if (
                    !validateMarksForm()
                ) {

                    Swal.fire({

                        icon:
                            'error',

                        title:
                            'Validation Error',

                        text:
                            'Please enter marks for all present students before final submission.',

                        confirmButtonText:
                            'OK'

                    });

                    return;
                }


                Swal.fire({

                    icon:
                        'warning',

                    title:
                        'Final Marks Submission',

                    html:
                        '<div style="text-align:left">' +

                        '<b>This is the FINAL submission of marks.</b>' +

                        '<br><br>' +

                        'Please check all marks carefully.' +

                        '<br><br>' +

                        'After final submission:' +

                        '<ul style="margin-top:8px;margin-left:20px;">' +

                        '<li>Marks will be locked.</li>' +

                        '<li>Teacher cannot modify the marks.</li>' +

                        '<li>Administrator intervention will be required for corrections.</li>' +

                        '</ul>' +

                        '</div>',

                    showCancelButton:
                        true,

                    confirmButtonText:
                        'Submit Final Marks',

                    cancelButtonText:
                        'Cancel',

                    confirmButtonColor:
                        '#16a34a'

                }).then(
                    function (result) {

                        if (
                            !result.isConfirmed
                        ) {
                            return;
                        }


                        submitFinalButton.disabled =
                            true;


                        submitFinalButton.textContent =
                            'Submitting...';


                        HTMLFormElement
                            .prototype
                            .submit
                            .call(
                                finalSubmitForm
                            );

                    }
                );

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | FILTERS
    |--------------------------------------------------------------------------
    */

    function setupFilters()
    {

        const yearSelect =
            getElement(
                'academic_year_id'
            );

        const examSelect =
            getElement(
                'exam_master_id'
            );

        const assignmentSelect =
            getElement(
                'teacher_subject_allocation_id'
            );

        const filterForm =
            getElement(
                'marksFilterForm'
            );


        /*
        |--------------------------------------------------------------------------
        | ACADEMIC YEAR
        |--------------------------------------------------------------------------
        */

        if (
            yearSelect &&
            filterForm
        ) {

            yearSelect.addEventListener(
                'change',
                function () {

                    if (
                        assignmentSelect
                    ) {

                        assignmentSelect.innerHTML =
                            '<option value="">Select Exam First</option>';

                        assignmentSelect.disabled =
                            true;

                    }


                    filterForm.submit();

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | EXAM
        |--------------------------------------------------------------------------
        */

        if (
            examSelect &&
            filterForm
        ) {

            examSelect.addEventListener(
                'change',
                function () {

                    if (
                        assignmentSelect
                    ) {

                        assignmentSelect.innerHTML =
                            '<option value="">Loading assignments...</option>';

                        assignmentSelect.disabled =
                            true;

                    }


                    filterForm.submit();

                }
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | FORCE ROW BUTTON TYPE
    |--------------------------------------------------------------------------
    */

    function protectRowButtons()
    {

        document
            .querySelectorAll(
                '#marksSaveForm button[id^="btn_"],' +
                '#marksSaveForm button[id^="optional_btn_"]'
            )
            .forEach(
                function (button) {

                    button.type =
                        'button';

                }
            );

    }


    /*
    |--------------------------------------------------------------------------
    | INITIALIZE
    |--------------------------------------------------------------------------
    */

    function initialize()
    {

        protectRowButtons();

        setupMarkValidation();

        setupSaveForm();

        setupFinalSubmit();

        setupFilters();

    }


    /*
    |--------------------------------------------------------------------------
    | START
    |--------------------------------------------------------------------------
    */

    if (
        document.readyState === 'loading'
    ) {

        document.addEventListener(
            'DOMContentLoaded',
            initialize
        );

    } else {

        initialize();

    }

})();

</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // Get the save form
    const saveForm = document.getElementById('marksEntryForm');
    if (!saveForm) {
        console.log('Save form not found');
        return;
    }

    // 1. Remove all existing submit event listeners (brute force)
    // Create a new submit handler that completely replaces the old one
    const newSubmitHandler = function (e) {
        // Allow empty values - just submit the form
        console.log('Save form submitted - allowing empty values');
        const saveButton = document.getElementById('saveMarksButton');
        if (saveButton) {
            saveButton.disabled = true;
            saveButton.innerText = 'Saving...';
        }
        // The form will submit naturally
        return true;
    };

    // 2. Remove any existing submit listeners by cloning the form
    const newForm = saveForm.cloneNode(true);
    saveForm.parentNode.replaceChild(newForm, saveForm);

    // 3. Add our new submit handler
    newForm.addEventListener('submit', newSubmitHandler);
    newForm.setAttribute('novalidate', 'novalidate');

    // 4. Remove required attributes from all mark inputs
    newForm.querySelectorAll('.mark-input').forEach(function (input) {
        input.removeAttribute('required');
    });

    // 5. Also add formnovalidate to the save button
    const saveButton = document.getElementById('saveMarksButton');
    if (saveButton) {
        saveButton.setAttribute('formnovalidate', 'formnovalidate');
    }

    console.log('Save form override complete - empty values allowed');
});
</script>

</x-app-layout>

