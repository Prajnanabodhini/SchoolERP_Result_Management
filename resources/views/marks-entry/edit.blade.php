<x-app-layout>

<style>

    .marks-entry-page,
    .marks-entry-page * {
        font-family: Arial, sans-serif !important;
        font-size: 13px;
    }

    .marks-entry-page h2 {
        font-size: 20px !important;
        font-weight: 700 !important;
        color: #2563EB !important;
    }

    .filter-row {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .tabs-container {
        display: flex;
        border-bottom: 2px solid #2563EB;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .active-tab {
        background: #2563EB;
        color: white !important;
        padding: 9px 18px;
        text-decoration: none;
        border-radius: 6px 6px 0 0;
        margin-right: 4px;
        font-weight: 700;
    }

    .inactive-tab {
        background: #E5E7EB;
        color: #111827 !important;
        padding: 9px 18px;
        text-decoration: none;
        border-radius: 6px 6px 0 0;
        margin-right: 4px;
        font-weight: 700;
    }

    .filter-select {
        height: 32px;
        padding: 3px 7px;
        border: 1px solid #9CA3AF;
        border-radius: 5px;
        background: #fff;
    }

    .exam-select {
        width: 220px;
    }

    .assignment-select {
        width: 340px;
    }

    .mark-input {
        width: 72px;
        height: 30px;
        padding: 3px 5px;
        font-size: 13px !important;
        text-align: center;
        border: 1px solid #9CA3AF;
        border-radius: 4px;
    }

    .mark-input:disabled {
        background: #F3F4F6;
        cursor: not-allowed;
    }

    .absent-checkbox {
        width: 17px;
        height: 17px;
        cursor: pointer;
    }

    .student-name {
        font-weight: 700;
        white-space: nowrap;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px !important;
        font-weight: 700;
    }

    .status-completed {
        background: #DCFCE7;
        color: #166534;
    }

    .status-pending {
        background: #FEF3C7;
        color: #92400E;
    }

    .student-count-badge {
        background: #DBEAFE;
        color: #1E40AF;
        padding: 6px 12px;
        border-radius: 5px;
        font-weight: 700;
        white-space: nowrap;
    }

    .locked-box {
        margin-bottom: 15px;
        padding: 12px;
        background: #FEF3C7;
        border: 1px solid #F59E0B;
        border-radius: 5px;
        color: #92400E;
    }

    .info-box {
        margin-bottom: 15px;
        padding: 10px 12px;
        background: #EFF6FF;
        border: 1px solid #BFDBFE;
        border-radius: 5px;
        color: #1E3A8A;
    }

    .table-wrapper {
        width: 100%;
        overflow-x: auto;
        background: #fff;
    }

    .marks-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 850px;
    }

    .marks-table th,
    .marks-table td {
        border: 1px solid #D1D5DB;
        padding: 6px;
        vertical-align: middle;
    }

    .marks-table th {
        background: #DBEAFE;
        text-align: center;
        font-weight: 700;
        white-space: nowrap;
    }

    .marks-table td.text-center {
        text-align: center;
    }

    .error-text {
        color: #DC2626;
        font-size: 11px !important;
        margin-top: 2px;
        min-height: 13px;
    }

    .present-text {
        color: #166534;
        font-weight: 700;
    }

    .absent-text {
        color: #991B1B;
        font-weight: 700;
    }

    .summary-box {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
        margin-bottom: 15px;
    }

    @media (max-width: 900px) {

        .exam-select,
        .assignment-select {
            width: 100%;
        }

        .filter-row {
            flex-direction: column;
            align-items: stretch;
        }

        .marks-entry-page .erp-btn {
            width: 100%;
        }
    }

</style>


<div class="marks-entry-page bg-white rounded-lg shadow-lg p-6 border border-gray-200">

    {{-- =========================================================
         TITLE
    ========================================================== --}}

    <h2 class="mb-4">
        Examination Marks
    </h2>


    {{-- =========================================================
         TABS
    ========================================================== --}}

    <div class="tabs-container">

        <a
            href="{{ route(
                'marks-entry.index',
                request()->query()
            ) }}"
            class="{{ request()->routeIs('marks-entry.index')
                ? 'active-tab'
                : 'inactive-tab' }}"
        >
            Marks Entry
        </a>


        <a
            href="{{ route(
                'marks-entry.view',
                request()->query()
            ) }}"
            class="{{ request()->routeIs('marks-entry.view')
                ? 'active-tab'
                : 'inactive-tab' }}"
        >
            View / Edit Marks
        </a>

    </div>


    {{-- =========================================================
         SESSION ERROR
    ========================================================== --}}

    @if(session('error'))

        <div style="
            margin-bottom:15px;
            padding:10px;
            background:#FEE2E2;
            border:1px solid #EF4444;
            border-radius:5px;
            color:#DC2626;
            font-weight:700;
        ">
            {{ session('error') }}
        </div>

    @endif


    {{-- =========================================================
         SESSION SUCCESS
    ========================================================== --}}

    @if(session('success'))

        <div style="
            margin-bottom:15px;
            padding:10px;
            background:#DCFCE7;
            border:1px solid #22C55E;
            border-radius:5px;
            color:#15803D;
            font-weight:700;
        ">
            {{ session('success') }}
        </div>

    @endif


    {{-- =========================================================
         CONTROLLER ERROR
    ========================================================== --}}

    @if(!empty($error))

        <div style="
            margin-bottom:15px;
            padding:10px;
            background:#FEE2E2;
            border:1px solid #EF4444;
            border-radius:5px;
        ">

            <strong style="color:#DC2626;">
                Error :
            </strong>

            <span style="color:#DC2626;">
                {{ $error }}
            </span>

        </div>

    @endif


    {{-- =========================================================
         FILTER FORM
    ========================================================== --}}

    <form
        method="GET"
        action="{{ route('marks-entry.edit') }}"
    >

        <div class="filter-row mb-5">

            {{-- EXAM --}}

            <label class="font-semibold">
                Exam
            </label>

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
                        {{ (string)request('exam_master_id') ===
                           (string)$examItem->id
                            ? 'selected'
                            : '' }}
                    >
                        {{ $examItem->display_exam_name
                            ?? $examItem->exam_name }}
                    </option>

                @endforeach

            </select>


            {{-- TEACHING ASSIGNMENT --}}

            <label class="font-semibold">
                Teaching Assignment
            </label>

            <select
                name="teacher_subject_allocation_id"
                id="teacher_subject_allocation_id"
                class="filter-select assignment-select"
            >

                <option value="">
                    Select Teaching Assignment
                </option>

                @foreach($assignments as $assignment)

                    @php

                        $selectionKey =
                            $assignment->resolved_selection_key
                            ?? (
                                $assignment->id
                                . '|'
                                . (
                                    $assignment->resolved_subject_id
                                    ?? $assignment->subject_id
                                )
                            );

                        $assignmentSubjectName =
                            optional(
                                $assignment->subject
                            )->subject_name
                            ?? '-';

                        $teacherName =
                            optional(
                                optional(
                                    $assignment->allocation
                                )->teacher
                            )->name
                            ?? '-';

                        $standardName =
                            optional(
                                optional(
                                    $assignment->allocation
                                )->standard
                            )->standard_name
                            ?? '-';

                        $divisionName =
                            optional(
                                optional(
                                    $assignment->allocation
                                )->division
                            )->division_name
                            ?? '-';

                        $statusName =
                            strtoupper(
                                trim(
                                    (string)(
                                        $assignment->resolved_status
                                        ?? 'PENDING'
                                    )
                                )
                            );

                    @endphp

                    <option
                        value="{{ $selectionKey }}"
                        {{ request('teacher_subject_allocation_id') ===
                           $selectionKey
                            ? 'selected'
                            : '' }}
                    >

                        {{ $teacherName }}
                        -
                        {{ $assignmentSubjectName }}
                        -
                        {{ $standardName }}
                        -
                        {{ $divisionName }}
                        -
                        [{{ $statusName }}]

                    </option>

                @endforeach

            </select>


            {{-- LOAD --}}

            <button
                type="submit"
                class="erp-btn erp-btn-save"
            >
                Load Students
            </button>


            {{-- STUDENT COUNT --}}

            @if($students->isNotEmpty())

                <span class="student-count-badge">

                    {{ $students->count() }}
                    Students

                </span>

            @endif

        </div>

    </form>


    {{-- =========================================================
         SELECTED ASSIGNMENT SUMMARY
    ========================================================== --}}

    @if($teacherSubjectAllocation)

        @php

            $selectedTeacher =
                optional(
                    optional(
                        $selectedClassAllocation
                    )->teacher
                )->name
                ?? '-';

            $selectedSubject =
                optional(
                    $teacherSubjectAllocation->subject
                )->subject_name
                ?? '-';

            $selectedStandard =
                optional(
                    optional(
                        $selectedClassAllocation
                    )->standard
                )->standard_name
                ?? '-';

            $selectedDivision =
                optional(
                    optional(
                        $selectedClassAllocation
                    )->division
                )->division_name
                ?? '-';

            $selectedExamName =
                optional(
                    $exam
                )->display_exam_name
                ??
                optional(
                    $exam
                )->exam_name
                ??
                '-';

            $selectedStatus =
                strtoupper(
                    trim(
                        (string)(
                            $teacherSubjectAllocation->resolved_status
                            ?? 'PENDING'
                        )
                    )
                );

        @endphp


        <div class="info-box">

            <strong>Teacher:</strong>
            {{ $selectedTeacher }}

            &nbsp; | &nbsp;

            <strong>Subject:</strong>
            {{ $selectedSubject }}

            &nbsp; | &nbsp;

            <strong>Class:</strong>
            {{ $selectedStandard }}
            -
            {{ $selectedDivision }}

            &nbsp; | &nbsp;

            <strong>Exam:</strong>
            {{ $selectedExamName }}

            &nbsp; | &nbsp;

            <strong>Status:</strong>

            <span
                class="status-badge {{
                    $selectedStatus === 'COMPLETED'
                        ? 'status-completed'
                        : 'status-pending'
                }}"
            >
                {{ $selectedStatus }}
            </span>

        </div>

    @endif


    {{-- =========================================================
         LOCKED MESSAGE
    ========================================================== --}}

    @if($marksLocked)

        <div class="locked-box">

            <strong>
                Marks already submitted and locked.
            </strong>

            <br>

            Contact Administrator for modification.

        </div>

    @endif


    {{-- =========================================================
         MESSAGE
    ========================================================== --}}

    @if(!empty($message))

        <div class="info-box">

            {{ $message }}

        </div>

    @endif


    {{-- =========================================================
         STUDENT MARKS FORM
    ========================================================== --}}

    @if($students->isNotEmpty())

        <form
            method="POST"
            action="{{ route('marks-entry.update') }}"
            id="marksEntryForm"
        >

            @csrf


            {{-- =================================================
                 EXAM
            ================================================== --}}

            <input
                type="hidden"
                name="exam_master_id"
                value="{{ $exam?->id
                    ?? request('exam_master_id') }}"
            >


            {{-- =================================================
                 TSA
            ================================================== --}}

            <input
                type="hidden"
                name="teacher_subject_allocation_id"
                value="{{
                    request(
                        'teacher_subject_allocation_id'
                    )
                }}"
            >


            <div class="table-wrapper">

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

                        </tr>

                    </thead>


                    <tbody>

                    @foreach($students as $record)

                        @php

                            /*
                            |--------------------------------------------------------------------------
                            | STUDENT ID
                            |--------------------------------------------------------------------------
                            |
                            | StudentHelper normally provides Studentid.
                            |
                            | Fallbacks are included for compatibility.
                            |--------------------------------------------------------------------------
                            */

                            $studentId =
                                $record->Studentid
                                ?? $record->student_id
                                ?? $record->id
                                ?? null;


                            $existingMark =
                                $existingMarks->get(
                                    (string)$studentId
                                )
                                ??
                                $existingMarks->get(
                                    $studentId
                                );


                            $isAbsent =
                                (int)(
                                    $existingMark?->is_absent
                                    ?? 0
                                ) === 1;


                            $theoryValue =
                                $existingMark
                                    ? $existingMark->theory_obtained_marks
                                    : '';


                            $oralValue =
                                $existingMark
                                    ? $existingMark->oral_obtained_marks
                                    : '';


                            $practicalValue =
                                $existingMark
                                    ? $existingMark->practical_obtained_marks
                                    : '';

                        @endphp


                        <tr>

                            {{-- GR NO --}}

                            <td class="text-center">

                                {{
                                    $record->regno
                                    ?? $record->registration_no
                                    ?? $record->admission_no
                                    ?? '-'
                                }}

                            </td>


                            {{-- ROLL NO --}}

                            <td class="text-center">

                                {{
                                    $record->rollno
                                    ?? $record->roll_no
                                    ?? $record->roll_number
                                    ?? '-'
                                }}

                            </td>


                            {{-- STUDENT NAME --}}

                            <td class="student-name">

                                {{
                                    $record->studname
                                    ?? $record->student_name
                                    ?? $record->full_student_name
                                    ?? '-'
                                }}

                            </td>


                            {{-- ATTENDANCE --}}

                            <td class="text-center">

                                <input
                                    type="hidden"
                                    name="is_absent[{{ $studentId }}]"
                                    value="0"
                                >

                                <label
                                    style="
                                        display:flex;
                                        align-items:center;
                                        justify-content:center;
                                        gap:5px;
                                    "
                                >

                                    <input
                                        type="checkbox"
                                        name="is_absent[{{ $studentId }}]"
                                        value="1"
                                        class="absent-checkbox"
                                        data-student-id="{{ $studentId }}"
                                        {{ $isAbsent ? 'checked' : '' }}
                                        {{ $marksLocked ? 'disabled' : '' }}
                                    >

                                    <span
                                        class="{{ $isAbsent
                                            ? 'absent-text'
                                            : 'present-text' }}"
                                    >
                                        {{ $isAbsent
                                            ? 'ABSENT'
                                            : 'PRESENT' }}
                                    </span>

                                </label>

                            </td>


                            {{-- THEORY --}}

                            @if($showTheory)

                                <td class="text-center">

                                    {{ (int)$theoryMaxMarks }}

                                </td>


                                <td class="text-center">

                                    {{ (int)$theoryPassingMarks }}

                                </td>


                                <td>

                                    <input
                                        type="number"
                                        name="theory_marks[{{ $studentId }}]"
                                        value="{{ old(
                                            'theory_marks.'.$studentId,
                                            $theoryValue
                                        ) }}"
                                        min="0"
                                        max="{{ $theoryMaxMarks }}"
                                        step="0.01"
                                        class="mark-input"
                                        data-max="{{ $theoryMaxMarks }}"
                                        data-student-id="{{ $studentId }}"
                                        {{ $marksLocked ? 'readonly' : '' }}
                                    >

                                    <div class="error-text"></div>

                                    @error(
                                        'theory_marks.'.$studentId
                                    )

                                        <div class="error-text">
                                            {{ $message }}
                                        </div>

                                    @enderror

                                </td>

                            @endif


                            {{-- ORAL --}}

                            @if($showOral)

                                <td class="text-center">

                                    {{ (int)$oralMaxMarks }}

                                </td>


                                <td class="text-center">

                                    {{ (int)$oralPassingMarks }}

                                </td>


                                <td>

                                    <input
                                        type="number"
                                        name="oral_marks[{{ $studentId }}]"
                                        value="{{ old(
                                            'oral_marks.'.$studentId,
                                            $oralValue
                                        ) }}"
                                        min="0"
                                        max="{{ $oralMaxMarks }}"
                                        step="0.01"
                                        class="mark-input"
                                        data-max="{{ $oralMaxMarks }}"
                                        data-student-id="{{ $studentId }}"
                                        {{ $marksLocked ? 'readonly' : '' }}
                                    >

                                    <div class="error-text"></div>

                                    @error(
                                        'oral_marks.'.$studentId
                                    )

                                        <div class="error-text">
                                            {{ $message }}
                                        </div>

                                    @enderror

                                </td>

                            @endif


                            {{-- PRACTICAL --}}

                            @if($showPractical)

                                <td class="text-center">

                                    {{ (int)$practicalMaxMarks }}

                                </td>


                                <td class="text-center">

                                    {{ (int)$practicalPassingMarks }}

                                </td>


                                <td>

                                    <input
                                        type="number"
                                        name="practical_marks[{{ $studentId }}]"
                                        value="{{ old(
                                            'practical_marks.'.$studentId,
                                            $practicalValue
                                        ) }}"
                                        min="0"
                                        max="{{ $practicalMaxMarks }}"
                                        step="0.01"
                                        class="mark-input"
                                        data-max="{{ $practicalMaxMarks }}"
                                        data-student-id="{{ $studentId }}"
                                        {{ $marksLocked ? 'readonly' : '' }}
                                    >

                                    <div class="error-text"></div>

                                    @error(
                                        'practical_marks.'.$studentId
                                    )

                                        <div class="error-text">
                                            {{ $message }}
                                        </div>

                                    @enderror

                                </td>

                            @endif

                        </tr>

                    @endforeach

                    </tbody>

                </table>

            </div>


            {{-- =================================================
                 UPDATE BUTTON
            ================================================== --}}

            @if(!$marksLocked)

                <div class="mt-5">

                    <button
                        type="submit"
                        class="erp-btn erp-btn-save"
                        id="saveMarksButton"
                    >
                        Save / Update Marks
                    </button>

                </div>

            @endif

        </form>


    @else

        @if(
            request('teacher_subject_allocation_id')
            &&
            empty($error)
        )

            <div style="
                padding:12px;
                background:#FEF3C7;
                border:1px solid #F59E0B;
                border-radius:5px;
                color:#92400E;
                font-weight:600;
            ">
                No students found for the selected teaching assignment.
            </div>

        @endif

    @endif

</div>


{{-- =========================================================
     WRONG SECTION
========================================================= --}}

@if(session('force_section_error'))

    <div
        class="modal fade"
        id="sectionErrorModal"
        tabindex="-1"
        data-bs-backdrop="static"
        data-bs-keyboard="false"
    >

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header bg-danger text-white">

                    <h5 class="modal-title">
                        Wrong Section Selected
                    </h5>

                </div>

                <div class="modal-body">

                    {{ session('force_section_error') }}

                </div>

                <div class="modal-footer">

                    <a
                        href="{{ route('logout') }}"
                        class="btn btn-danger"
                    >
                        Login Again
                    </a>

                </div>

            </div>

        </div>

    </div>


    <script>

        document.addEventListener(
            'DOMContentLoaded',
            function () {

                const modalElement =
                    document.getElementById(
                        'sectionErrorModal'
                    );

                if (
                    modalElement &&
                    typeof bootstrap !== 'undefined'
                ) {

                    new bootstrap.Modal(
                        modalElement
                    ).show();

                }

            }
        );

    </script>

@endif


{{-- =========================================================
     FRONTEND VALIDATION
========================================================= --}}

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

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
                function (input) {

                    const errorDiv =
                        input.parentNode.querySelector(
                            '.error-text'
                        );


                    function validateInput() {

                        if (
                            input.readOnly ||
                            input.disabled
                        ) {
                            return true;
                        }


                        const value =
                            input.value.trim();


                        const max =
                            parseFloat(
                                input.dataset.max
                                || input.max
                                || 0
                            );


                        input.style.border =
                            '1px solid #9CA3AF';


                        if (
                            errorDiv
                        ) {

                            errorDiv.textContent =
                                '';
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | BLANK
                        |--------------------------------------------------------------------------
                        */

                        if (
                            value === ''
                        ) {

                            input.style.border =
                                '2px solid #DC2626';

                            if (errorDiv) {

                                errorDiv.textContent =
                                    'Marks required';
                            }

                            return false;
                        }


                        const numericValue =
                            parseFloat(
                                value
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | INVALID NUMBER
                        |--------------------------------------------------------------------------
                        */

                        if (
                            Number.isNaN(
                                numericValue
                            )
                        ) {

                            input.style.border =
                                '2px solid #DC2626';

                            if (errorDiv) {

                                errorDiv.textContent =
                                    'Enter valid marks';
                            }

                            return false;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | NEGATIVE
                        |--------------------------------------------------------------------------
                        */

                        if (
                            numericValue < 0
                        ) {

                            input.style.border =
                                '2px solid #DC2626';

                            if (errorDiv) {

                                errorDiv.textContent =
                                    'Marks cannot be negative';
                            }

                            return false;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | MAX
                        |--------------------------------------------------------------------------
                        */

                        if (
                            numericValue > max
                        ) {

                            input.style.border =
                                '2px solid #DC2626';

                            if (errorDiv) {

                                errorDiv.textContent =
                                    'Maximum allowed marks is '
                                    + max;
                            }

                            return false;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | VALID
                        |--------------------------------------------------------------------------
                        */

                        input.style.border =
                            '2px solid #16A34A';

                        return true;
                    }


                    input.addEventListener(
                        'input',
                        validateInput
                    );


                    input.addEventListener(
                        'blur',
                        validateInput
                    );

                }
            );


        /*
        |--------------------------------------------------------------------------
        | ABSENT TOGGLE
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll(
                '.absent-checkbox'
            )
            .forEach(
                function (checkbox) {

                    checkbox.addEventListener(
                        'change',
                        function () {

                            const row =
                                this.closest(
                                    'tr'
                                );


                            if (
                                !row
                            ) {
                                return;
                            }


                            const statusText =
                                this.parentNode
                                    .querySelector(
                                        'span'
                                    );


                            const markInputs =
                                row.querySelectorAll(
                                    '.mark-input'
                                );


                            if (
                                this.checked
                            ) {

                                if (
                                    statusText
                                ) {

                                    statusText.textContent =
                                        'ABSENT';

                                    statusText.classList
                                        .remove(
                                            'present-text'
                                        );

                                    statusText.classList
                                        .add(
                                            'absent-text'
                                        );
                                }


                                markInputs.forEach(
                                    function (input) {

                                        input.value =
                                            0;

                                        input.readOnly =
                                            true;

                                        input.style.border =
                                            '1px solid #9CA3AF';

                                        const errorDiv =
                                            input.parentNode
                                                .querySelector(
                                                    '.error-text'
                                                );

                                        if (
                                            errorDiv
                                        ) {

                                            errorDiv.textContent =
                                                '';
                                        }

                                    }
                                );

                            } else {

                                if (
                                    statusText
                                ) {

                                    statusText.textContent =
                                        'PRESENT';

                                    statusText.classList
                                        .remove(
                                            'absent-text'
                                        );

                                    statusText.classList
                                        .add(
                                            'present-text'
                                        );
                                }


                                markInputs.forEach(
                                    function (input) {

                                        input.readOnly =
                                            false;

                                    }
                                );
                            }

                        }
                    );

                }
            );


        /*
        |--------------------------------------------------------------------------
        | FORM SUBMIT
        |--------------------------------------------------------------------------
        */

        const form =
            document.getElementById(
                'marksEntryForm'
            );


        if (
            form
        ) {

            form.addEventListener(
                'submit',
                function (event) {

                    let valid =
                        true;


                    /*
                    |--------------------------------------------------------------------------
                    | VALIDATE ONLY PRESENT STUDENTS
                    |--------------------------------------------------------------------------
                    */

                    document
                        .querySelectorAll(
                            '.mark-input'
                        )
                        .forEach(
                            function (input) {

                                const row =
                                    input.closest(
                                        'tr'
                                    );


                                const absentCheckbox =
                                    row
                                        ? row.querySelector(
                                            '.absent-checkbox'
                                        )
                                        : null;


                                if (
                                    absentCheckbox
                                    &&
                                    absentCheckbox.checked
                                ) {

                                    return;
                                }


                                if (
                                    !input.readOnly
                                    &&
                                    !validateMarkInput(
                                        input
                                    )
                                ) {

                                    valid =
                                        false;
                                }

                            }
                        );


                    if (
                        !valid
                    ) {

                        event.preventDefault();

                        alert(
                            'Please correct the marks before submitting.'
                        );

                        return;
                    }


                    const saveButton =
                        document.getElementById(
                            'saveMarksButton'
                        );


                    if (
                        saveButton
                    ) {

                        saveButton.disabled =
                            true;

                        saveButton.innerText =
                            'Saving...';
                    }

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | COMMON VALIDATION FUNCTION
        |--------------------------------------------------------------------------
        */

        function validateMarkInput(
            input
        ) {

            const value =
                input.value.trim();


            const max =
                parseFloat(
                    input.dataset.max
                    || input.max
                    || 0
                );


            const errorDiv =
                input.parentNode.querySelector(
                    '.error-text'
                );


            input.style.border =
                '1px solid #9CA3AF';


            if (
                errorDiv
            ) {

                errorDiv.textContent =
                    '';
            }


            if (
                value === ''
            ) {

                input.style.border =
                    '2px solid #DC2626';

                if (
                    errorDiv
                ) {

                    errorDiv.textContent =
                        'Marks required';
                }

                return false;
            }


            const numericValue =
                parseFloat(
                    value
                );


            if (
                Number.isNaN(
                    numericValue
                )
            ) {

                input.style.border =
                    '2px solid #DC2626';

                if (
                    errorDiv
                ) {

                    errorDiv.textContent =
                        'Enter valid marks';
                }

                return false;
            }


            if (
                numericValue < 0
            ) {

                input.style.border =
                    '2px solid #DC2626';

                if (
                    errorDiv
                ) {

                    errorDiv.textContent =
                        'Marks cannot be negative';
                }

                return false;
            }


            if (
                numericValue > max
            ) {

                input.style.border =
                    '2px solid #DC2626';

                if (
                    errorDiv
                ) {

                    errorDiv.textContent =
                        'Maximum allowed marks is '
                        + max;
                }

                return false;
            }


            input.style.border =
                '2px solid #16A34A';

            return true;
        }

    }
);

</script>


</x-app-layout>