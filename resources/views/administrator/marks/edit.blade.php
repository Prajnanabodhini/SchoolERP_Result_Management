<x-app-layout>

<style>

.result-sheet-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 15px;
}

.result-sheet-table th {
    border: 1px solid #d1d5db;
    background: #dbeafe;
    text-align: center;
    padding: 6px;
    white-space: nowrap;
    vertical-align: middle;
}

.result-sheet-table td {
    border: 1px solid #d1d5db;
    padding: 4px;
    white-space: nowrap;
    vertical-align: middle;
}

.student-name {
    text-align: left !important;
    min-width: 220px;
}

.center {
    text-align: center !important;
}

.mark-input {
    width: 70px;
    height: 32px;
    padding: 2px 4px;
    font-size: 15px;
    text-align: center;
    font-weight: bold;
    border: 1px solid #9ca3af;
    border-radius: 4px;
}

.mark-input::-webkit-outer-spin-button,
.mark-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.mark-input[type=number] {
    -moz-appearance: textfield;
    appearance: textfield;
}

.info-panel {
    background: #EFF6FF;
    border: 1px solid #BFDBFE;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 4px;
}

.info-row {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    font-size: 15px;
    margin-bottom: 4px;
}

.info-row:last-child {
    margin-bottom: 0;
}

.info-label {
    font-weight: bold;
    color: #1E40AF;
}

.action-btn {
    height: 36px;
    min-width: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-size: 15px;
    font-weight: 600;
}

#updateBtn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.component-header {
    min-width: 110px;
}

.component-title {
    font-size: 15px;
    font-weight: bold;
}

.config-header {
    min-width: 95px;
    font-size: 14px;
    font-weight: bold;
}

.config-value {
    font-size: 15px;
    font-weight: bold;
}

.attendance-btn {
    min-width: 90px;
    border: none;
    cursor: pointer;
    font-weight: bold;
}

.validation-error {
    display: none;
    width: 100%;
    margin-top: 10px;
    margin-bottom: 10px;
    padding: 10px;
    background: #FEE2E2;
    border: 1px solid #EF4444;
    color: #B91C1C;
    border-radius: 4px;
    font-weight: bold;
}

</style>


<div class="erp-page">

<div class="erp-card">

<h2 style="
    font-size:20px;
    font-weight:bold;
    color:#1d4ed8;
    margin-bottom:10px;
">
    EDIT EXAMINATION MARKS
</h2>


{{-- =========================================================
     SUCCESS MESSAGE
========================================================= --}}

@if(session('success'))

<div style="
    padding:10px;
    background:#DCFCE7;
    border:1px solid #22C55E;
    color:#15803D;
    margin-bottom:10px;
    border-radius:4px;
    font-weight:bold;
">
    {{ session('success') }}
</div>

@endif


{{-- =========================================================
     ERROR MESSAGE
========================================================= --}}

@if(session('error'))

<div style="
    padding:10px;
    background:#FEE2E2;
    border:1px solid #EF4444;
    color:#B91C1C;
    margin-bottom:10px;
    border-radius:4px;
    font-weight:bold;
">
    {{ session('error') }}
</div>

@endif


{{-- =========================================================
     VALIDATION ERRORS
========================================================= --}}

@if($errors->any())

<div style="
    padding:10px;
    background:#FEE2E2;
    border:1px solid #EF4444;
    color:#B91C1C;
    margin-bottom:10px;
    border-radius:4px;
    font-weight:bold;
">

    <ul style="margin:0;padding-left:20px;">

        @foreach($errors->all() as $error)

            <li>{{ $error }}</li>

        @endforeach

    </ul>

</div>

@endif


{{-- =========================================================
     INFORMATION PANEL
========================================================= --}}

<div class="info-panel">

    {{-- STATUS --}}

    <div class="info-row">

        <span>

            <span class="info-label">
                Mark Entry Status :
            </span>

            @if(
                $statusRecord &&
                strtoupper(trim($statusRecord->status ?? '')) === 'COMPLETED'
            )

                <span style="
                    background:#DCFCE7;
                    color:#15803D;
                    padding:4px 10px;
                    border-radius:4px;
                    font-weight:bold;
                ">
                    COMPLETED
                </span>

            @else

                <span style="
                    background:#FEF3C7;
                    color:#B45309;
                    padding:4px 10px;
                    border-radius:4px;
                    font-weight:bold;
                ">
                    PENDING
                </span>

            @endif

        </span>


        @if($statusRecord)

            <span>

                <span class="info-label">
                    Last Updated By :
                </span>

                {{ $statusRecord->teacher->name ?? '-' }}

            </span>


            <span>

                <span class="info-label">
                    Last Updated On :
                </span>

                {{
                    $lastUpdated
                    ? \Carbon\Carbon::parse($lastUpdated)->format('d-M-Y h:i A')
                    : '-'
                }}

            </span>

        @endif

    </div>


    {{-- ACADEMIC / EXAM / TEACHER --}}

    <div class="info-row">

        <span>

            <span class="info-label">
                Academic Year :
            </span>

            {{ $academicYear->year_name ?? '-' }}

        </span>


        <span>

            <span class="info-label">
                Exam :
            </span>

            {{ $exam->exam_name ?? '-' }}

        </span>


        <span>

            <span class="info-label">
                Teacher :
            </span>

            {{ $teacher->name ?? '-' }}

        </span>

    </div>


    {{-- STANDARD / DIVISION / SUBJECT / STUDENTS --}}

    <div class="info-row">

        <span>

            <span class="info-label">
                Standard :
            </span>

            {{ $standard->standard_name ?? '-' }}

        </span>


        <span>

            <span class="info-label">
                Division :
            </span>

            {{ $division->division_name ?? '-' }}

        </span>


        <span>

            <span class="info-label">
                Subject :
            </span>

            {{ $subject->subject_name ?? '-' }}

        </span>


        <span>

            <span class="info-label">
                Students :
            </span>

            {{ $students->count() }}

        </span>

    </div>

</div>


{{-- =========================================================
     MARK FORM
========================================================= --}}

<form
    method="POST"
    action="{{ route('result-generation.admin-marks.update') }}"
    id="marksForm"
>

@csrf


{{-- =========================================================
     HIDDEN COMMON VALUES
========================================================= --}}

<input
    type="hidden"
    name="exam_master_id"
    value="{{ $exam->id ?? '' }}"
>

<input
    type="hidden"
    name="standard_id"
    value="{{ $standard->id ?? '' }}"
>

<input
    type="hidden"
    name="division_id"
    value="{{ $division->id ?? '' }}"
>

<input
    type="hidden"
    name="subject_id"
    value="{{ $subject->id ?? '' }}"
>

<input
    type="hidden"
    name="teacher_id"
    value="{{ $teacher->id ?? '' }}"
>


{{-- =========================================================
     MARK TABLE
========================================================= --}}

<div style="overflow-x:auto;">

<table class="result-sheet-table">

<thead>

<tr>

    <th>
        Roll
    </th>

    <th>
        Reg No
    </th>

    <th>
        Student Name
    </th>

    <th>
        Attendance
    </th>

    <th>
        Status
    </th>

    <th class="config-header">
        Max Marks
    </th>

    <th class="config-header">
        Passing Marks
    </th>


    @if($exam->has_theory)

        <th class="component-header">
            <div class="component-title">
                Theory
            </div>
        </th>

    @endif


    @if($exam->has_oral)

        <th class="component-header">
            <div class="component-title">
                Oral
            </div>
        </th>

    @endif


    @if($exam->has_practical)

        <th class="component-header">
            <div class="component-title">
                Practical
            </div>
        </th>

    @endif

</tr>

</thead>


<tbody>

@foreach($students as $student)

@php

    $totalMax =
        (int)($subjectConfig->max_marks ?? 0);

    $passingMarks =
        (int)($subjectConfig->passing_marks ?? 0);

    /*
    |--------------------------------------------------------------------------
    | Component maximum marks
    |--------------------------------------------------------------------------
    |
    | If separate component values exist, use them.
    | Otherwise fall back safely.
    |
    */

    $theoryMax =
        (int)(
            $subjectConfig->theory_max_marks
            ?? $exam->theory_max_marks
            ?? $totalMax
        );

    $oralMax =
        (int)(
            $subjectConfig->oral_max_marks
            ?? $exam->oral_max_marks
            ?? 0
        );

    $practicalMax =
        (int)(
            $subjectConfig->practical_max_marks
            ?? $exam->practical_max_marks
            ?? 0
        );

@endphp


<tr>

    {{-- ROLL --}}

    <td class="center">

        {{ $student->rollno ?? '' }}

    </td>


    {{-- REGISTRATION NUMBER --}}

    <td class="center">

        {{ $student->regno ?? '' }}

    </td>


    {{-- STUDENT NAME --}}

    <td class="student-name">

        {{ $student->studname ?? '' }}

        @if(!empty($student->fathername))

            {{ $student->fathername }}

        @endif

    </td>


    {{-- =====================================================
         ATTENDANCE
    ====================================================== --}}

    <td class="center">

        <input
            type="hidden"
            name="is_absent[{{ $student->id }}]"
            id="absent_{{ $student->id }}"
            value="{{ $student->is_absent ? 1 : 0 }}"
        >


        <button
            type="button"
            id="btn_{{ $student->id }}"
            onclick="toggleAbsent({{ $student->id }})"
            class="attendance-btn
                {{ $student->is_absent
                    ? 'bg-red-600'
                    : 'bg-green-600'
                }}
                text-white px-3 py-1 rounded"
        >

            {{ $student->is_absent
                ? 'ABSENT'
                : 'PRESENT'
            }}

        </button>

    </td>


    {{-- =====================================================
         STATUS
    ====================================================== --}}

    <td class="center">

        <span
            id="status_{{ $student->id }}"
            class="{{
                $student->is_absent
                ? 'text-red-600'
                : 'text-green-600'
            }}"
            style="font-weight:bold;"
        >

            {{ $student->is_absent
                ? 'ABSENT'
                : 'PRESENT'
            }}

        </span>

    </td>


    {{-- MAX MARKS --}}

    <td class="center config-value">

        {{ $totalMax }}

    </td>


    {{-- PASSING MARKS --}}

    <td class="center config-value">

        {{ $passingMarks }}

    </td>


    {{-- =====================================================
         THEORY
    ====================================================== --}}

    @if($exam->has_theory)

        <td class="center">

            <input
                type="number"
                step="1"
                min="0"
                max="{{ $theoryMax }}"
                data-max="{{ $theoryMax }}"
                data-student="{{ $student->id }}"
                name="theory_marks[{{ $student->id }}]"
                value="{{
                    $student->theory_obtained_marks !== null
                    ? (int)$student->theory_obtained_marks
                    : ''
                }}"
                class="
                    mark-input
                    mark-field
                    student-{{ $student->id }}
                "
                {{ $student->is_absent ? 'readonly' : '' }}
            >

        </td>

    @endif


    {{-- =====================================================
         ORAL
    ====================================================== --}}

    @if($exam->has_oral)

        <td class="center">

            <input
                type="number"
                step="1"
                min="0"
                max="{{ $oralMax }}"
                data-max="{{ $oralMax }}"
                data-student="{{ $student->id }}"
                name="oral_marks[{{ $student->id }}]"
                value="{{
                    $student->oral_obtained_marks !== null
                    ? (int)$student->oral_obtained_marks
                    : ''
                }}"
                class="
                    mark-input
                    mark-field
                    student-{{ $student->id }}
                "
                {{ $student->is_absent ? 'readonly' : '' }}
            >

        </td>

    @endif


    {{-- =====================================================
         PRACTICAL
    ====================================================== --}}

    @if($exam->has_practical)

        <td class="center">

            <input
                type="number"
                step="1"
                min="0"
                max="{{ $practicalMax }}"
                data-max="{{ $practicalMax }}"
                data-student="{{ $student->id }}"
                name="practical_marks[{{ $student->id }}]"
                value="{{
                    $student->practical_obtained_marks !== null
                    ? (int)$student->practical_obtained_marks
                    : ''
                }}"
                class="
                    mark-input
                    mark-field
                    student-{{ $student->id }}
                "
                {{ $student->is_absent ? 'readonly' : '' }}
            >

        </td>

    @endif


    {{-- =====================================================
         MARK ID
    ====================================================== --}}

    <td style="display:none;">

        <input
            type="hidden"
            name="mark_ids[]"
            value="{{ $student->id }}"
        >

    </td>

</tr>

@endforeach

</tbody>

</table>

</div>


{{-- =========================================================
     BUTTONS / VALIDATION
========================================================= --}}

<div style="
    margin-top:15px;
    display:flex;
    align-items:center;
    gap:8px;
    flex-wrap:wrap;
">

    <div
        id="validationError"
        class="validation-error"
    ></div>


    <button
        type="submit"
        id="updateBtn"
        class="erp-btn erp-btn-save"
        disabled
    >
        Update Marks
    </button>


    <a
        href="{{ route('result-generation.admin-marks.index') }}"
        class="erp-btn action-btn"
        style="
            background:#6B7280;
            color:white;
        "
    >
        Back
    </a>

</div>


</form>

</div>

</div>


{{-- =========================================================
     SWEET ALERT
========================================================= --}}

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const inputs =
        document.querySelectorAll('.mark-field');

    const updateBtn =
        document.getElementById('updateBtn');

    const errorBox =
        document.getElementById('validationError');


    /*
    ============================================================
    VALIDATE ALL MARKS
    ============================================================
    */

    function validateAll(showMessage = false)
    {
        let valid = true;

        let errors = [];


        inputs.forEach(function(input) {

            /*
            ----------------------------------------------------
            ABSENT STUDENT
            ----------------------------------------------------
            */

            if (input.readOnly) {
                return;
            }


            const value =
                input.value.trim();

            const max =
                parseInt(
                    input.dataset.max || '0'
                );


            /*
            ----------------------------------------------------
            BLANK
            ----------------------------------------------------
            */

            if (value === '') {

                valid = false;

                errors.push(
                    'Please enter all marks.'
                );

                return;

            }


            /*
            ----------------------------------------------------
            INTEGER ONLY
            ----------------------------------------------------
            */

            if (!/^\d+$/.test(value)) {

                valid = false;

                errors.push(
                    'Marks must be whole numbers.'
                );

                return;

            }


            const numericValue =
                parseInt(value);


            /*
            ----------------------------------------------------
            RANGE
            ----------------------------------------------------
            */

            if (
                numericValue < 0 ||
                numericValue > max
            ) {

                valid = false;

                errors.push(
                    'Marks cannot exceed the maximum marks.'
                );

            }

        });


        /*
        --------------------------------------------------------
        SHOW FIRST UNIQUE ERROR
        --------------------------------------------------------
        */

        if (showMessage && !valid) {

            const uniqueErrors =
                [...new Set(errors)];

            errorBox.innerHTML =
                uniqueErrors
                    .map(error => '• ' + error)
                    .join('<br>');

            errorBox.style.display =
                'block';

        }
        else {

            errorBox.innerHTML = '';

            errorBox.style.display =
                'none';

        }


        /*
        --------------------------------------------------------
        UPDATE BUTTON
        --------------------------------------------------------
        */

        updateBtn.disabled =
            !valid;


        return valid;
    }


    /*
    ============================================================
    INPUT EVENTS
    ============================================================
    */

    inputs.forEach(function(input) {

        input.addEventListener(
            'input',
            function() {

                /*
                Automatically prevent value
                greater than maximum.
                */

                const max =
                    parseInt(
                        input.dataset.max || '0'
                    );

                if (
                    input.value !== '' &&
                    parseInt(input.value) > max
                ) {

                    input.value = max;

                }

                validateAll(false);

            }
        );


        input.addEventListener(
            'change',
            function() {

                validateAll(false);

            }
        );

    });


    /*
    ============================================================
    FORM SUBMIT
    ============================================================
    */

    document
        .getElementById('marksForm')
        .addEventListener(
            'submit',
            function(event) {

                if (!validateAll(true)) {

                    event.preventDefault();

                    errorBox.scrollIntoView({
                        behavior:'smooth',
                        block:'center'
                    });

                    return false;

                }

            }
        );


    /*
    ============================================================
    INITIAL STATE
    ============================================================
    */

    document
        .querySelectorAll('.mark-input')
        .forEach(function(markBox) {

            const studentId =
                markBox.dataset.student;

            const absentField =
                document.getElementById(
                    'absent_' + studentId
                );


            if (
                absentField &&
                absentField.value === '1'
            ) {

                markBox.readOnly = true;

                markBox.value = 0;

                markBox.style.background =
                    '#E5E7EB';

                markBox.style.color =
                    '#6B7280';

            }

        });


    validateAll(false);

});


/* =============================================================
   TOGGLE ABSENT
============================================================= */

function toggleAbsent(studentId)
{

    const flag =
        document.getElementById(
            'absent_' + studentId
        );


    if (flag.value == 0) {

        Swal.fire({

            icon: 'warning',

            title: 'Confirm Absent',

            text:
                'Student will be marked ABSENT and all marks will become 0.',

            showCancelButton: true,

            confirmButtonText:
                'Yes, Mark Absent',

            cancelButtonText:
                'Cancel'

        }).then(function(result) {

            if (result.isConfirmed) {

                makeAbsent(studentId);

            }

        });

    }
    else {

        Swal.fire({

            icon: 'question',

            title: 'Confirm Present',

            text:
                'Change student status back to PRESENT?',

            showCancelButton: true,

            confirmButtonText:
                'Yes, Present',

            cancelButtonText:
                'Cancel'

        }).then(function(result) {

            if (result.isConfirmed) {

                makePresent(studentId);

            }

        });

    }

}


/* =============================================================
   MAKE ABSENT
============================================================= */

function makeAbsent(studentId)
{

    const flag =
        document.getElementById(
            'absent_' + studentId
        );

    const btn =
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


    flag.value = 1;


    /*
    ------------------------------------------------------------
    BUTTON
    ------------------------------------------------------------
    */

    btn.innerHTML =
        'ABSENT';

    btn.classList.remove(
        'bg-green-600'
    );

    btn.classList.add(
        'bg-red-600'
    );


    /*
    ------------------------------------------------------------
    STATUS
    ------------------------------------------------------------
    */

    status.innerHTML =
        'ABSENT';

    status.classList.remove(
        'text-green-600'
    );

    status.classList.add(
        'text-red-600'
    );


    /*
    ------------------------------------------------------------
    MARKS
    ------------------------------------------------------------
    */

    inputs.forEach(function(input) {

        input.value = 0;

        input.readOnly = true;

        input.style.background =
            '#E5E7EB';

        input.style.color =
            '#6B7280';

    });


    /*
    ------------------------------------------------------------
    VALIDATION
    ------------------------------------------------------------
    */

    if (
        typeof validateAll === 'function'
    ) {

        validateAll(false);

    }

}


/* =============================================================
   MAKE PRESENT
============================================================= */

function makePresent(studentId)
{

    const flag =
        document.getElementById(
            'absent_' + studentId
        );

    const btn =
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


    flag.value = 0;


    /*
    ------------------------------------------------------------
    BUTTON
    ------------------------------------------------------------
    */

    btn.innerHTML =
        'PRESENT';

    btn.classList.remove(
        'bg-red-600'
    );

    btn.classList.add(
        'bg-green-600'
    );


    /*
    ------------------------------------------------------------
    STATUS
    ------------------------------------------------------------
    */

    status.innerHTML =
        'PRESENT';

    status.classList.remove(
        'text-red-600'
    );

    status.classList.add(
        'text-green-600'
    );


    /*
    ------------------------------------------------------------
    MARKS
    ------------------------------------------------------------
    */

    inputs.forEach(function(input) {

        input.readOnly = false;

        input.style.background = '';

        input.style.color = '';


        /*
        If the previous value was 0
        clear it so the user must
        enter the actual mark.
        */

        if (input.value == '0') {

            input.value = '';

        }

    });


    /*
    ------------------------------------------------------------
    VALIDATION
    ------------------------------------------------------------
    */

    if (
        typeof validateAll === 'function'
    ) {

        validateAll(false);

    }

}

</script>


</x-app-layout>