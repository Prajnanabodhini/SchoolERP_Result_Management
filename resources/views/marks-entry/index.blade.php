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
        height: 34px;
        padding: 5px 14px;
        border: 0;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
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
    }


    .erp-btn-green:hover {
        background: #15803d;
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


    .status-present {
        color: #16a34a;
        font-weight: 700;
    }


    .status-absent {
        color: #dc2626;
        font-weight: 700;
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
             ERROR
        =========================================================== --}}

        @if(!empty($error))

            <div class="error-box">

                <strong>Error:</strong>

                {{ $error }}

            </div>

        @endif


        {{-- ==========================================================
             SAVED BUT NOT SUBMITTED
        =========================================================== --}}

        @if(request()->boolean('marks_saved'))

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

                    <label class="filter-label">
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
                                    {{ $year->year_name ?? $year->name ?? $year->id }}
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

                    <label class="filter-label">
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

                    <label class="filter-label">
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
             SELECTED INFORMATION
        =========================================================== --}}

        @if($teacherSubjectAllocation && $exam)

            <div class="selected-info">


                {{-- TEACHER --}}

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


                {{-- SUBJECT --}}

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


                {{-- CLASS --}}

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


                {{-- EXAM --}}

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


                {{-- STUDENTS --}}

                <span>

                    <span class="selected-info-item">
                        Students:
                    </span>

                    {{ $students->count() }}

                </span>

            </div>

        @endif


        {{-- ==========================================================
             MARKS TABLE
        =========================================================== --}}

        @if($students->count() > 0)

            <div class="marks-table-wrapper">

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

                        @foreach($students as $record)

                            @php

                                $studentId =
                                    $record->Studentid
                                    ?? $record->student_id
                                    ?? $record->id;

                                $studentMark =
                                    $existingMarks[$studentId]
                                    ?? null;

                                $isAbsent =
                                    $studentMark &&
                                    (int)$studentMark->is_absent === 1;

                                $studentFullName =
                                    trim(
                                        ($record->studname ?? '')
                                        . ' '
                                        . ($record->fathername ?? '')
                                    );

                            @endphp


                            <tr>


                                {{-- GR NO --}}

                                <td class="center">

                                    {{ $record->regno ?? '-' }}

                                </td>


                                {{-- ROLL NO --}}

                                <td class="center">

                                    {{ $record->rollno ?? '-' }}

                                </td>


                                {{-- FULL NAME --}}

                                <td class="student-name-cell">

                                    {{ $studentFullName ?: '-' }}

                                </td>


                                {{-- ATTENDANCE --}}

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
                                            onclick="toggleAbsent('{{ $studentId }}')"
                                        >

                                            {{ $isAbsent ? 'ABSENT' : 'PRESENT' }}

                                        </button>

                                    @else

                                        <span
                                            class="{{ $isAbsent ? 'status-absent' : 'status-present' }}"
                                        >
                                            {{ $isAbsent ? 'ABSENT' : 'PRESENT' }}
                                        </span>

                                    @endif

                                </td>


                                <input
                                    type="hidden"
                                    name="student_ids[]"
                                    value="{{ $studentId }}"
                                >


                                {{-- THEORY --}}

                                @if($showTheory)

                                    <td class="center">
                                        {{ (int)$theoryMaxMarks }}
                                    </td>

                                    <td class="center">
                                        {{ (int)$theoryPassingMarks }}
                                    </td>

                                    <td class="center">

                                        <input
                                            type="number"
                                            name="theory_marks[{{ $studentId }}]"
                                            value="{{
                                                old(
                                                    'theory_marks.'.$studentId,
                                                    $studentMark
                                                        ? (int)$studentMark->theory_obtained_marks
                                                        : ''
                                                )
                                            }}"
                                            min="0"
                                            max="{{ (float)$theoryMaxMarks }}"
                                            step="1"
                                            class="mark-input student-{{ $studentId }}"
                                            {{ $isAbsent || $marksLocked ? 'readonly' : '' }}
                                            {{ !$isAbsent && !$marksLocked ? 'required' : '' }}
                                        >

                                    </td>

                                @endif


                                {{-- ORAL --}}

                                @if($showOral)

                                    <td class="center">
                                        {{ (int)$oralMaxMarks }}
                                    </td>

                                    <td class="center">
                                        {{ (int)$oralPassingMarks }}
                                    </td>

                                    <td class="center">

                                        <input
                                            type="number"
                                            name="oral_marks[{{ $studentId }}]"
                                            value="{{
                                                old(
                                                    'oral_marks.'.$studentId,
                                                    $studentMark
                                                        ? (int)$studentMark->oral_obtained_marks
                                                        : ''
                                                )
                                            }}"
                                            min="0"
                                            max="{{ (float)$oralMaxMarks }}"
                                            step="1"
                                            class="mark-input student-{{ $studentId }}"
                                            {{ $isAbsent || $marksLocked ? 'readonly' : '' }}
                                        >

                                    </td>

                                @endif


                                {{-- PRACTICAL --}}

                                @if($showPractical)

                                    <td class="center">
                                        {{ (int)$practicalMaxMarks }}
                                    </td>

                                    <td class="center">
                                        {{ (int)$practicalPassingMarks }}
                                    </td>

                                    <td class="center">

                                        <input
                                            type="number"
                                            name="practical_marks[{{ $studentId }}]"
                                            value="{{
                                                old(
                                                    'practical_marks.'.$studentId,
                                                    $studentMark
                                                        ? (int)$studentMark->practical_obtained_marks
                                                        : ''
                                                )
                                            }}"
                                            min="0"
                                            max="{{ (float)$practicalMaxMarks }}"
                                            step="1"
                                            class="mark-input student-{{ $studentId }}"
                                            {{ $isAbsent || $marksLocked ? 'readonly' : '' }}
                                        >

                                    </td>

                                @endif


                                {{-- STATUS --}}

                                <td
                                    class="center"
                                    id="status_{{ $studentId }}"
                                >

                                    <span
                                        class="{{ $isAbsent ? 'status-absent' : 'status-present' }}"
                                    >
                                        {{ $isAbsent ? 'ABSENT' : 'PRESENT' }}
                                    </span>

                                </td>

                            </tr>

                        @endforeach

                        </tbody>

                    </table>


                    {{-- ==================================================
                         BUTTON ROW
                    =================================================== --}}

                    <div class="marks-action-row">

                        @if(!$marksLocked)

                            <button
                                type="submit"
                                class="erp-btn erp-btn-save"
                            >
                                Save Marks
                            </button>


                            @if(request()->boolean('marks_saved'))

                                <button
                                    type="button"
                                    class="erp-btn erp-btn-green"
                                    id="submitFinalButton"
                                >
                                    Submit Final Marks
                                </button>

                            @endif

                        @endif


                        <span class="student-count">
                            {{ $students->count() }} Students
                        </span>

                    </div>

                </form>


                {{-- ==================================================
                     FINAL SUBMIT FORM
                =================================================== --}}

                @if(
                    !$marksLocked &&
                    request()->boolean('marks_saved')
                )

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

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | DROPDOWNS
        |--------------------------------------------------------------------------
        */

        const yearSelect =
            document.getElementById(
                'academic_year_id'
            );

        const examSelect =
            document.getElementById(
                'exam_master_id'
            );

        const assignmentSelect =
            document.getElementById(
                'teacher_subject_allocation_id'
            );


        /*
        |--------------------------------------------------------------------------
        | YEAR CHANGE
        |--------------------------------------------------------------------------
        */

        yearSelect.addEventListener(
            'change',
            function () {

                assignmentSelect.innerHTML =
                    '<option value="">Select Exam First</option>';

                assignmentSelect.disabled =
                    true;


                document
                    .getElementById(
                        'marksFilterForm'
                    )
                    .submit();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | EXAM CHANGE
        |--------------------------------------------------------------------------
        */

        examSelect.addEventListener(
            'change',
            function () {

                assignmentSelect.innerHTML =
                    '<option value="">Loading assignments...</option>';

                assignmentSelect.disabled =
                    true;


                document
                    .getElementById(
                        'marksFilterForm'
                    )
                    .submit();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | MARK VALIDATION
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll(
                '.mark-input'
            )
            .forEach(
                function(input) {

                    input.addEventListener(
                        'input',
                        function() {

                            if (this.readOnly) {
                                return;
                            }


                            const max =
                                parseFloat(
                                    this.max
                                );


                            const value =
                                this.value;


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
                                isNaN(number) ||
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


        /*
        |--------------------------------------------------------------------------
        | FINAL SUBMIT
        |--------------------------------------------------------------------------
        */

        const submitFinalButton =
            document.getElementById(
                'submitFinalButton'
            );


        const finalSubmitForm =
            document.getElementById(
                'finalSubmitForm'
            );


        if (
            submitFinalButton &&
            finalSubmitForm
        ) {

            submitFinalButton.addEventListener(
                'click',
                function() {

                    /*
                    |--------------------------------------------------------------------------
                    | VALIDATE MARKS
                    |--------------------------------------------------------------------------
                    */

                    let hasError =
                        false;


                    document
                        .querySelectorAll(
                            '.mark-input'
                        )
                        .forEach(
                            function(input) {

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


                    if (hasError) {

                        Swal.fire({

                            icon:
                                'error',

                            title:
                                'Validation Error',

                            text:
                                'Please enter marks for all students before final submission.'

                        });

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | CONFIRM
                    |--------------------------------------------------------------------------
                    */

                    Swal.fire({

                        icon:
                            'warning',

                        title:
                            'Final Marks Submission',

                        html:
                            '<div style="text-align:left">' +
                            '<b>This is final mark submission.</b>' +
                            '<br><br>' +
                            'Check all marks carefully before submitting.' +
                            '<br><br>' +
                            'After final submission you cannot modify marks.' +
                            '</div>',

                        showCancelButton:
                            true,

                        confirmButtonText:
                            'Submit Final Marks',

                        cancelButtonText:
                            'Cancel'

                    }).then(
                        function(result) {

                            if (
                                result.isConfirmed
                            ) {

                                finalSubmitForm.submit();

                            }

                        }
                    );

                }
            );

        }

    }
);


/*
|--------------------------------------------------------------------------
| ABSENT / PRESENT
|--------------------------------------------------------------------------
*/

function toggleAbsent(studentId)
{
    const flag =
        document.getElementById(
            'absent_' + studentId
        );


    if (!flag) {
        return;
    }


    if (
        flag.value === '0'
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
            function(result) {

                if (
                    result.isConfirmed
                ) {

                    makeAbsent(
                        studentId
                    );

                }

            }
        );

    } else {

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
            function(result) {

                if (
                    result.isConfirmed
                ) {

                    makePresent(
                        studentId
                    );

                }

            }
        );
    }
}


/*
|--------------------------------------------------------------------------
| MAKE ABSENT
|--------------------------------------------------------------------------
*/

function makeAbsent(studentId)
{
    const flag =
        document.getElementById(
            'absent_' + studentId
        );

    const button =
        document.getElementById(
            'btn_' + studentId
        );

    const status =
        document.getElementById(
            'status_' + studentId
        );

    const inputs =
        document.querySelectorAll(
            '.student-' + studentId
        );


    if (!flag) {
        return;
    }


    flag.value =
        '1';


    if (button) {

        button.innerHTML =
            'ABSENT';

        button.classList.remove(
            'present-btn'
        );

        button.classList.add(
            'absent-btn'
        );

    }


    if (status) {

        status.innerHTML =
            '<span class="status-absent">ABSENT</span>';

    }


    inputs.forEach(
        function(input) {

            input.value =
                '0';

            input.readOnly =
                true;

            input.style.background =
                '#fee2e2';

        }
    );
}


/*
|--------------------------------------------------------------------------
| MAKE PRESENT
|--------------------------------------------------------------------------
*/

function makePresent(studentId)
{
    const flag =
        document.getElementById(
            'absent_' + studentId
        );

    const button =
        document.getElementById(
            'btn_' + studentId
        );

    const status =
        document.getElementById(
            'status_' + studentId
        );

    const inputs =
        document.querySelectorAll(
            '.student-' + studentId
        );


    if (!flag) {
        return;
    }


    flag.value =
        '0';


    if (button) {

        button.innerHTML =
            'PRESENT';

        button.classList.remove(
            'absent-btn'
        );

        button.classList.add(
            'present-btn'
        );

    }


    if (status) {

        status.innerHTML =
            '<span class="status-present">PRESENT</span>';

    }


    inputs.forEach(
        function(input) {

            input.readOnly =
                false;

            input.style.background =
                '';

            if (
                input.value === '0'
            ) {

                input.value =
                    '';

            }

        }
    );
}

</script>

</x-app-layout>