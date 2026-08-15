<x-app-layout>

@php

    /*
    |--------------------------------------------------------------------------
    | Current Request Filters
    |--------------------------------------------------------------------------
    */

    $examMasterId =
        request('exam_master_id');

    $teacherSubjectAllocationId =
        request('teacher_subject_allocation_id');


    /*
    |--------------------------------------------------------------------------
    | Selected Assignment
    |--------------------------------------------------------------------------
    */

    $selectedAssignment = null;

    if (
        isset($assignments) &&
        $assignments instanceof \Illuminate\Support\Collection &&
        $teacherSubjectAllocationId
    ) {

        $selectedAssignment =
            $assignments->firstWhere(
                'id',
                $teacherSubjectAllocationId
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Selected Subject Name
    |--------------------------------------------------------------------------
    */

    $selectedSubjectName = '';

    if (
        isset($subjectConfig) &&
        $subjectConfig
    ) {

        $selectedSubjectName =
            $subjectConfig->subject_name
            ?? '';
    }


    if (
        $selectedSubjectName === '' &&
        isset($teacherSubjectAllocation) &&
        $teacherSubjectAllocation
    ) {

        $selectedSubjectName =
            optional(
                $teacherSubjectAllocation->standardWiseSubject
            )->subject_name
            ?? '';
    }


    if (
        $selectedSubjectName === '' &&
        $selectedAssignment
    ) {

        $selectedSubjectName =
            optional(
                $selectedAssignment->subject
            )->subject_name
            ?? '';
    }


    /*
    |--------------------------------------------------------------------------
    | Existing Marks Count
    |--------------------------------------------------------------------------
    */

    $existingMarksCount = 0;

    if (
        isset($existingMarks) &&
        $existingMarks
    ) {

        $existingMarksCount =
            is_countable($existingMarks)
                ? count($existingMarks)
                : 0;
    }


@endphp


<style>

    .filter-row {
        display:flex;
        align-items:center;
        gap:7px;
        flex-wrap:wrap;
    }

    .filter-label {
        font-weight:600;
        font-size:13px;
        color:#374151;
    }

    .filter-select {
        height:32px;
        font-size:12px;
        padding:3px 7px;
        border:1px solid #D1D5DB;
        border-radius:5px;
        background:white;
    }

    .exam-select {
        width:150px;
    }

    .assignment-select {
        width:400px;
    }

    .mark-input {
        width:60px;
        height:30px;
        padding:2px 4px;
        font-size:13px;
        text-align:center;
        border:1px solid #D1D5DB;
        border-radius:4px;
    }

    .mark-input::-webkit-outer-spin-button,
    .mark-input::-webkit-inner-spin-button {
        -webkit-appearance:none;
        margin:0;
    }

    .mark-input[type=number] {
        -moz-appearance:textfield;
        appearance:textfield;
    }

    .mark-input:focus {
        outline:none;
        border:2px solid #2563EB;
    }

    .mark-error {
        color:#DC2626;
        font-size:11px;
        margin-top:2px;
        min-height:14px;
    }

    .mark-valid {
        border:2px solid #16A34A !important;
    }

    .mark-invalid {
        border:2px solid #DC2626 !important;
    }

    .absent-input {
        background:#FEE2E2 !important;
        color:#991B1B;
    }

    .locked-input {
        background:#E5E7EB !important;
        color:#6B7280;
        cursor:not-allowed;
    }

    .status-present {
        color:#15803D;
        font-weight:bold;
    }

    .status-absent {
        color:#DC2626;
        font-weight:bold;
    }

    .attendance-present {
        background:#16A34A;
        color:white;
        padding:5px 10px;
        border-radius:5px;
        font-size:12px;
        font-weight:bold;
    }

    .attendance-absent {
        background:#DC2626;
        color:white;
        padding:5px 10px;
        border-radius:5px;
        font-size:12px;
        font-weight:bold;
    }

    .info-box {
        margin-top:15px;
        margin-bottom:15px;
        padding:12px;
        border-radius:5px;
    }

    .locked-box {
        background:#FEF3C7;
        border:1px solid #F59E0B;
        color:#92400E;
    }

    .saved-box {
        background:#ECFDF5;
        border:1px solid #10B981;
        color:#065F46;
    }

    .warning-box {
        background:#FEF3C7;
        border:1px solid #F59E0B;
        color:#92400E;
    }

    .student-count {
        background:#DBEAFE;
        color:#1E40AF;
        padding:6px 12px;
        border-radius:4px;
        font-weight:600;
        font-size:13px;
    }

    .marks-table th {
        white-space:nowrap;
        text-align:center;
        vertical-align:middle;
    }

    .marks-table td {
        vertical-align:middle;
    }

    .student-name {
        min-width:220px;
    }

</style>


<div class="bg-white rounded-lg shadow-lg p-6 border border-gray-200">


    {{-- ============================================================= --}}
    {{-- PAGE TITLE --}}
    {{-- ============================================================= --}}

    <h2 class="text-xl font-bold text-blue-600 mb-4">
        Examination Marks
    </h2>


    {{-- ============================================================= --}}
    {{-- SUCCESS MESSAGE --}}
    {{-- ============================================================= --}}

    @if(session('success'))

        <div class="info-box saved-box">

            <strong>
                ✓ {{ session('success') }}
            </strong>

            @if(
                session('success') ===
                'Marks Saved Successfully.'
            )

                <br>

                <span>
                    Marks are saved but not finally submitted.
                    Please click <b>Submit Final Marks</b>
                    after checking all marks.
                </span>

            @endif

        </div>

    @endif


    {{-- ============================================================= --}}
    {{-- ERROR MESSAGE --}}
    {{-- ============================================================= --}}

    @if(session('error'))

        <div class="info-box"
             style="
                background:#FEE2E2;
                border:1px solid #EF4444;
                color:#991B1B;
             ">

            <strong>
                Error:
            </strong>

            {{ session('error') }}

        </div>

    @endif


    {{-- ============================================================= --}}
    {{-- VALIDATION ERRORS --}}
    {{-- ============================================================= --}}

    @if($errors->any())

        <div class="info-box"
             style="
                background:#FEE2E2;
                border:1px solid #EF4444;
                color:#991B1B;
             ">

            <strong>
                Please correct the following:
            </strong>

            <ul style="margin-top:5px;margin-left:20px;">

                @foreach($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    {{-- ============================================================= --}}
    {{-- FILTER FORM --}}
    {{-- ============================================================= --}}

    <form
        method="GET"
        action="{{ route('marks-entry.index') }}"
        id="filterForm">

        <div class="filter-row">


            {{-- ===================================================== --}}
            {{-- EXAM --}}
            {{-- ===================================================== --}}

            <label class="filter-label">
                Exam
            </label>

            <select
                name="exam_master_id"
                id="exam_master_id"
                class="filter-select exam-select"
                onchange="document.getElementById('filterForm').submit();">

                <option value="">
                    Select
                </option>

                @foreach($exams as $examItem)

                    <option
                        value="{{ $examItem->id }}"
                        {{ (string)$examMasterId === (string)$examItem->id ? 'selected' : '' }}>

                        {{ $examItem->exam_name }}

                    </option>

                @endforeach

            </select>


            {{-- ===================================================== --}}
            {{-- TEACHING ASSIGNMENT --}}
            {{-- ===================================================== --}}

            <label class="filter-label">
                Teaching Assignment
            </label>

            <select
                name="teacher_subject_allocation_id"
                id="teacher_subject_allocation_id"
                class="filter-select assignment-select">

                <option value="">
                    Select
                </option>


                @if(isset($assignments) && count($assignments) > 0)

                    @foreach($assignments as $assignment)

                        @php

                            $assignmentSubjectName = '';

                            /*
                            |--------------------------------------------------------------------------
                            | Subject Name
                            |--------------------------------------------------------------------------
                            */

                            if (
                                isset($subjectConfig) &&
                                $subjectConfig &&
                                isset($teacherSubjectAllocation) &&
                                $teacherSubjectAllocation &&
                                $teacherSubjectAllocation->id ==
                                $assignment->id
                            ) {

                                $assignmentSubjectName =
                                    $subjectConfig->subject_name
                                    ?? '';
                            }


                            if (
                                $assignmentSubjectName === ''
                            ) {

                                $assignmentSubjectName =
                                    optional(
                                        $assignment->standardWiseSubject
                                    )->subject_name
                                    ?? '';
                            }


                            if (
                                $assignmentSubjectName === ''
                            ) {

                                $assignmentSubjectName =
                                    optional(
                                        $assignment->subject
                                    )->subject_name
                                    ?? '';
                            }


                            if (
                                $assignmentSubjectName === ''
                            ) {

                                $assignmentSubjectName =
                                    'Subject';
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Teacher
                            |--------------------------------------------------------------------------
                            */

                            $teacherName =
                                optional(
                                    optional(
                                        $assignment->allocation
                                    )->teacher
                                )->name
                                ?? '';


                            /*
                            |--------------------------------------------------------------------------
                            | Standard
                            |--------------------------------------------------------------------------
                            */

                            $standardName =
                                optional(
                                    optional(
                                        $assignment->allocation
                                    )->standard
                                )->standard_name
                                ?? '';


                            /*
                            |--------------------------------------------------------------------------
                            | Division
                            |--------------------------------------------------------------------------
                            */

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
                            {{ (string)$teacherSubjectAllocationId === (string)$assignment->id ? 'selected' : '' }}>

                            {{ $teacherName }}
                            -
                            {{ $assignmentSubjectName }}
                            -
                            {{ $standardName }}
                            {{ $divisionName }}

                        </option>

                    @endforeach

                @elseif($examMasterId)

                    <option value="">
                        No Teaching Assignment Available
                    </option>

                @endif

            </select>


            {{-- ===================================================== --}}
            {{-- LOAD BUTTON --}}
            {{-- ===================================================== --}}

            <button
                type="submit"
                class="erp-btn erp-btn-save">

                Load Students

            </button>


            {{-- ===================================================== --}}
            {{-- STUDENT COUNT --}}
            {{-- ===================================================== --}}

            @if(isset($students) && count($students) > 0)

                <span class="student-count">

                    {{ count($students) }} Students

                    @if($selectedAssignment)

                        |

                        {{ optional(
                            optional(
                                $selectedAssignment->allocation
                            )->teacher
                        )->name ?? '' }}

                        |

                        {{ $selectedSubjectName ?: 'Subject' }}

                        |

                        {{ optional(
                            optional(
                                $selectedAssignment->allocation
                            )->standard
                        )->standard_name ?? '' }}

                        {{ optional(
                            optional(
                                $selectedAssignment->allocation
                            )->division
                        )->division_name ?? '' }}

                    @endif

                </span>

            @endif

        </div>

    </form>


    {{-- ============================================================= --}}
    {{-- MARKS LOCKED --}}
    {{-- ============================================================= --}}

    {{-- @if(isset($marksLocked) && $marksLocked)

<div class="info-box saved-box">

    <strong>
        ✓ Final marks submitted successfully.
    </strong>

    <br><br>

    Marks for this teaching assignment have been
    submitted and locked.

    <br>

    No further action is required.

</div>

@endif --}}


    {{-- ============================================================= --}}
    {{-- MARKS SAVED BUT NOT SUBMITTED --}}
    {{-- ============================================================= --}}

    @if(
        !$marksLocked &&
        $existingMarksCount > 0 &&
        session('success') !== 'Marks Saved Successfully.'
    )

        <div class="info-box warning-box">

            <strong>
                ⚠ Marks are saved but not finally submitted.
            </strong>

            <br>

            Please verify all marks and click
            <b>Submit Final Marks</b>.

        </div>

    @endif


    {{-- ============================================================= --}}
    {{-- STUDENT MARKS --}}
    {{-- ============================================================= --}}

    @if(
    isset($students) &&
    count($students) > 0 &&
    !$marksLocked
)


        {{-- ========================================================= --}}
        {{-- SAVE FORM --}}
        {{-- ========================================================= --}}

        <form
            method="POST"
            action="{{ route('marks-entry.save') }}"
            id="marksSaveForm">

            @csrf


            <input
                type="hidden"
                name="exam_master_id"
                value="{{ $examMasterId }}">


            <input
                type="hidden"
                name="teacher_subject_allocation_id"
                value="{{ $teacherSubjectAllocationId }}">


            <input
                type="hidden"
                id="marksModified"
                name="marks_modified"
                value="0">


            <div style="
                overflow-x:auto;
                margin-top:15px;
            ">


                <table
                    class="w-full border border-gray-300 text-sm bg-white marks-table">


                    {{-- ================================================= --}}
                    {{-- TABLE HEADER --}}
                    {{-- ================================================= --}}

                    <thead class="bg-blue-100">

                        <tr>


                            <th class="border p-2">
                                GR No
                            </th>


                            <th class="border p-2">
                                Roll No
                            </th>


                            <th class="border p-2 student-name">
                                Student Name
                            </th>


                            <th class="border p-2">
                                Attendance
                            </th>


                            {{-- ========================================= --}}
                            {{-- THEORY --}}
                            {{-- ========================================= --}}

                            @if($showTheory)

                                <th class="border p-2">
                                    Theory Max
                                </th>

                                <th class="border p-2">
                                    Theory Pass
                                </th>

                                <th class="border p-2">
                                    Theory Obtained
                                </th>

                            @endif


                            {{-- ========================================= --}}
                            {{-- ORAL --}}
                            {{-- ========================================= --}}

                            @if($showOral)

                                <th class="border p-2">
                                    Oral Max
                                </th>

                                <th class="border p-2">
                                    Oral Pass
                                </th>

                                <th class="border p-2">
                                    Oral Obtained
                                </th>

                            @endif


                            {{-- ========================================= --}}
                            {{-- PRACTICAL --}}
                            {{-- ========================================= --}}

                            @if($showPractical)

                                <th class="border p-2">
                                    Practical Max
                                </th>

                                <th class="border p-2">
                                    Practical Pass
                                </th>

                                <th class="border p-2">
                                    Practical Obtained
                                </th>

                            @endif


                            <th class="border p-2">
                                Status
                            </th>

                        </tr>

                    </thead>


                    {{-- ================================================= --}}
                    {{-- TABLE BODY --}}
                    {{-- ================================================= --}}

                    <tbody>


                    @foreach($students as $record)


                        @php

                            /*
                            |--------------------------------------------------------------------------
                            | Existing Mark
                            |--------------------------------------------------------------------------
                            */

                            $studentMark = null;

                            if (
                                isset($existingMarks) &&
                                $existingMarks
                            ) {

                                if (
                                    method_exists(
                                        $existingMarks,
                                        'get'
                                    )
                                ) {

                                    $studentMark =
                                        $existingMarks->get(
                                            $record->Studentid
                                        );
                                }
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Absent
                            |--------------------------------------------------------------------------
                            */

                            $isAbsent =
                                $studentMark &&
                                $studentMark->is_absent
                                    ? true
                                    : false;


                            /*
                            |--------------------------------------------------------------------------
                            | Mark Values
                            |--------------------------------------------------------------------------
                            */

                            $theoryValue =
                                $studentMark
                                    ? $studentMark->theory_obtained_marks
                                    : null;

                            $oralValue =
                                $studentMark
                                    ? $studentMark->oral_obtained_marks
                                    : null;

                            $practicalValue =
                                $studentMark
                                    ? $studentMark->practical_obtained_marks
                                    : null;

                        @endphp


                        <tr
                            data-student-row="{{ $record->Studentid }}">


                            {{-- ========================================= --}}
                            {{-- GR NO --}}
                            {{-- ========================================= --}}

                            <td class="border p-2 text-center">

                                {{ $record->regno ?? '' }}

                            </td>


                            {{-- ========================================= --}}
                            {{-- ROLL NO --}}
                            {{-- ========================================= --}}

                            <td class="border p-2 text-center">

                                {{ $record->rollno ?? '' }}

                            </td>


                            {{-- ========================================= --}}
                            {{-- STUDENT NAME --}}
                            {{-- ========================================= --}}

                            <td class="border p-2">

                                
                                    {{ $record->studname ?? '' }}
                                

                                @if(!empty($record->fathername))

                                    <span style="color:#6B7280;">
                                        {{ $record->fathername }}
                                    </span>

                                @endif

                            </td>


                            {{-- ========================================= --}}
                            {{-- ATTENDANCE --}}
                            {{-- ========================================= --}}

                            <td class="border p-2 text-center">


                                <input
                                    type="hidden"
                                    name="is_absent[{{ $record->Studentid }}]"
                                    id="absent_{{ $record->Studentid }}"
                                    value="{{ $isAbsent ? 1 : 0 }}">


                                @if($marksLocked)

                                    <span
                                        class="{{ $isAbsent ? 'attendance-absent' : 'attendance-present' }}">

                                        {{ $isAbsent ? 'ABSENT' : 'PRESENT' }}

                                    </span>

                                @else

                                    <button
                                        type="button"
                                        id="btn_{{ $record->Studentid }}"
                                        onclick="toggleAbsent('{{ $record->Studentid }}')"
                                        class="{{ $isAbsent ? 'attendance-absent' : 'attendance-present' }}">

                                        {{ $isAbsent ? 'ABSENT' : 'PRESENT' }}

                                    </button>

                                @endif

                            </td>


                            {{-- ========================================= --}}
                            {{-- STUDENT ID --}}
                            {{-- ========================================= --}}

                            <input
                                type="hidden"
                                name="student_ids[]"
                                value="{{ $record->Studentid }}">


                            {{-- ================================================= --}}
                            {{-- THEORY --}}
                            {{-- ================================================= --}}

                            @if($showTheory)


                                <td class="border p-2 text-center">

                                    {{ (int)$theoryMaxMarks }}

                                </td>


                                <td class="border p-2 text-center">

                                    {{ (int)$theoryPassingMarks }}

                                </td>


                                <td class="border p-2 text-center">


                                    <input
                                        type="number"
                                        name="theory_marks[{{ $record->Studentid }}]"
                                        value="{{ old(
                                            'theory_marks.'.$record->Studentid,
                                            $theoryValue !== null ? (int)$theoryValue : ($isAbsent ? 0 : '')
                                        ) }}"
                                        min="0"
                                        max="{{ (float)$theoryMaxMarks }}"
                                        step="0.01"
                                        class="mark-input student-{{ $record->Studentid }} {{ $isAbsent ? 'absent-input' : '' }} {{ $marksLocked ? 'locked-input' : '' }}"
                                        data-student="{{ $record->Studentid }}"
                                        data-field="theory_obtained_marks"
                                        {{ ($isAbsent || $marksLocked) ? 'readonly' : '' }}>


                                    <div
                                        class="mark-error"
                                        data-error-for="theory_{{ $record->Studentid }}">
                                    </div>

                                </td>

                            @endif


                            {{-- ================================================= --}}
                            {{-- ORAL --}}
                            {{-- ================================================= --}}

                            @if($showOral)


                                <td class="border p-2 text-center">

                                    {{ (int)$oralMaxMarks }}

                                </td>


                                <td class="border p-2 text-center">

                                    {{ (int)$oralPassingMarks }}

                                </td>


                                <td class="border p-2 text-center">


                                    <input
                                        type="number"
                                        name="oral_marks[{{ $record->Studentid }}]"
                                        value="{{ old(
                                            'oral_marks.'.$record->Studentid,
                                            $oralValue !== null
                                                ? $oralValue
                                                : ($isAbsent ? 0 : '')
                                        ) }}"
                                        min="0"
                                        max="{{ (float)$oralMaxMarks }}"
                                        step="0.01"
                                        class="mark-input student-{{ $record->Studentid }} {{ $isAbsent ? 'absent-input' : '' }} {{ $marksLocked ? 'locked-input' : '' }}"
                                        data-student="{{ $record->Studentid }}"
                                        data-field="oral_obtained_marks"
                                        {{ ($isAbsent || $marksLocked) ? 'readonly' : '' }}>


                                    <div
                                        class="mark-error"
                                        data-error-for="oral_{{ $record->Studentid }}">
                                    </div>

                                </td>

                            @endif


                            {{-- ================================================= --}}
                            {{-- PRACTICAL --}}
                            {{-- ================================================= --}}

                            @if($showPractical)


                                <td class="border p-2 text-center">

                                    {{ (int)$practicalMaxMarks }}

                                </td>


                                <td class="border p-2 text-center">

                                    {{ (int)$practicalPassingMarks }}

                                </td>


                                <td class="border p-2 text-center">


                                    <input
                                        type="number"
                                        name="practical_marks[{{ $record->Studentid }}]"
                                        value="{{ old(
                                            'practical_marks.'.$record->Studentid,
                                            $practicalValue !== null
                                                ? $practicalValue
                                                : ($isAbsent ? 0 : '')
                                        ) }}"
                                        min="0"
                                        max="{{ (float)$practicalMaxMarks }}"
                                        step="0.01"
                                        class="mark-input student-{{ $record->Studentid }} {{ $isAbsent ? 'absent-input' : '' }} {{ $marksLocked ? 'locked-input' : '' }}"
                                        data-student="{{ $record->Studentid }}"
                                        data-field="practical_obtained_marks"
                                        {{ ($isAbsent || $marksLocked) ? 'readonly' : '' }}>


                                    <div
                                        class="mark-error"
                                        data-error-for="practical_{{ $record->Studentid }}">
                                    </div>

                                </td>

                            @endif


                            {{-- ========================================= --}}
                            {{-- STATUS --}}
                            {{-- ========================================= --}}

                            <td class="border p-2 text-center">

                                <span
                                    id="status_{{ $record->Studentid }}"
                                    class="{{ $isAbsent ? 'status-absent' : 'status-present' }}">

                                    {{ $isAbsent ? 'ABSENT' : 'PRESENT' }}

                                </span>

                            </td>


                        </tr>


                    @endforeach


                    </tbody>

                </table>

            </div>


            {{-- ========================================================= --}}
            {{-- SAVE BUTTON --}}
            {{-- ========================================================= --}}

@if(!$marksLocked)

<div class="mt-4 flex items-center gap-3">

    <button
        type="submit"
        id="saveMarksButton"
        class="erp-btn erp-btn-save">

        Save Marks

    </button>

</form>

<form
    id="finalSubmitForm"
    method="POST"
    action="{{ route('marks-entry.submit') }}"
    class="m-0">

    @csrf

    <input
        type="hidden"
        name="exam_master_id"
        value="{{ $examMasterId }}">

    <input
        type="hidden"
        name="teacher_subject_allocation_id"
        value="{{ $teacherSubjectAllocationId }}">

    <button
        type="submit"
        id="finalSubmitButton"
        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">

        Submit Final Marks

    </button>

</form>

</div>

@endif
    

        {{-- ========================================================= --}}
        {{-- NO STUDENTS --}}
        {{-- ========================================================= --}}

     @if(
    isset($students) &&
    count($students) == 0 &&
    $examMasterId &&
    $teacherSubjectAllocationId
)

<div
    style="
        margin-top:20px;
        padding:15px;
        background:#F3F4F6;
        border:1px solid #D1D5DB;
        border-radius:5px;
        color:#374151;
    ">

    No students found for the selected teaching assignment.

</div>

@endif
</div>

@endif
{{-- ================================================================= --}}
{{-- FORCE SECTION ERROR --}}
{{-- ================================================================= --}}

@if(session('force_section_error'))

    <div
        class="modal fade"
        id="sectionErrorModal"
        tabindex="-1"
        data-bs-backdrop="static"
        data-bs-keyboard="false">

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
                        class="btn btn-danger">

                        Login Again

                    </a>

                </div>

            </div>

        </div>

    </div>


    <script>

    document.addEventListener(
        'DOMContentLoaded',
        function()
        {

            let modal =
                document.getElementById(
                    'sectionErrorModal'
                );

            if(
                modal &&
                typeof bootstrap !== 'undefined'
            ) {

                new bootstrap.Modal(modal).show();

            }

        }
    );

    </script>

@endif


{{-- ================================================================= --}}
{{-- SWEET ALERT --}}
{{-- ================================================================= --}}

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


{{-- ================================================================= --}}
{{-- ABSENT / PRESENT --}}
{{-- ================================================================= --}}

<script>

function toggleAbsent(studentId)
{

    const flag =
        document.getElementById(
            'absent_' + studentId
        );


    if(!flag) {
        return;
    }


    if(flag.value == '0')
    {

        Swal.fire({

            icon:'warning',

            title:'Confirm Absent',

            text:
                'Student will be marked ABSENT and all marks will become 0.',

            showCancelButton:true,

            confirmButtonText:
                'Yes, Mark Absent',

            cancelButtonText:
                'Cancel'

        }).then(function(result)
        {

            if(result.isConfirmed)
            {
                makeAbsent(studentId);
            }

        });

    }
    else
    {

        Swal.fire({

            icon:'question',

            title:'Confirm Present',

            text:
                'Change student status back to PRESENT?',

            showCancelButton:true,

            confirmButtonText:
                'Yes, Present',

            cancelButtonText:
                'Cancel'

        }).then(function(result)
        {

            if(result.isConfirmed)
            {
                makePresent(studentId);
            }

        });

    }

}


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


    flag.value = '1';


    if(status)
    {

        status.innerHTML =
            'ABSENT';

        status.classList.remove(
            'status-present'
        );

        status.classList.add(
            'status-absent'
        );

    }


    if(button)
    {

        button.innerHTML =
            'ABSENT';

        button.classList.remove(
            'attendance-present'
        );

        button.classList.add(
            'attendance-absent'
        );

    }


    inputs.forEach(function(input)
    {

        input.value = 0;

        input.readOnly = true;

        input.classList.remove(
            'mark-valid',
            'mark-invalid'
        );

        input.classList.add(
            'absent-input'
        );

        input.style.border = '';

    });


    setModified();

}


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


    flag.value = '0';


    if(status)
    {

        status.innerHTML =
            'PRESENT';

        status.classList.remove(
            'status-absent'
        );

        status.classList.add(
            'status-present'
        );

    }


    if(button)
    {

        button.innerHTML =
            'PRESENT';

        button.classList.remove(
            'attendance-absent'
        );

        button.classList.add(
            'attendance-present'
        );

    }


    inputs.forEach(function(input)
    {

        input.readOnly = false;

        input.classList.remove(
            'absent-input'
        );

        input.style.background = '';


        /*
        |--------------------------------------------------------------------------
        | Do not automatically convert a legitimate 0 to blank unless
        | the student was previously absent.
        |--------------------------------------------------------------------------
        */

        if(input.value === '0')
        {
            input.value = '';
        }

    });


    setModified();

}


function setModified()
{

    const flag =
        document.getElementById(
            'marksModified'
        );

    if(flag)
    {
        flag.value = '1';
    }

}

</script>


{{-- ================================================================= --}}
{{-- MARK VALIDATION --}}
{{-- ================================================================= --}}

<script>

document.addEventListener(
    'DOMContentLoaded',
    function()
    {

        document
            .querySelectorAll('.mark-input')
            .forEach(function(input)
            {

                input.addEventListener(
                    'input',
                    function()
                    {

                        setModified();

                        validateMarkInput(this);

                    }
                );


                input.addEventListener(
                    'change',
                    function()
                    {

                        validateMarkInput(this);

                    }
                );

            });

    }
);


function validateMarkInput(input)
{

    if(input.readOnly)
    {
        input.classList.remove(
            'mark-invalid',
            'mark-valid'
        );

        return true;
    }


    const errorDiv =
        input.parentNode.querySelector(
            '.mark-error'
        );


    let value =
        input.value.trim();


    let max =
        parseFloat(input.max);


    input.classList.remove(
        'mark-invalid',
        'mark-valid'
    );


    if(errorDiv)
    {
        errorDiv.innerHTML = '';
    }


    /*
    |--------------------------------------------------------------------------
    | Blank
    |--------------------------------------------------------------------------
    */

    if(value === '')
    {

        input.classList.add(
            'mark-invalid'
        );

        if(errorDiv)
        {
            errorDiv.innerHTML =
                'Marks required';
        }

        return false;
    }


    value =
        parseFloat(value);


    /*
    |--------------------------------------------------------------------------
    | Negative
    |--------------------------------------------------------------------------
    */

    if(value < 0)
    {

        input.classList.add(
            'mark-invalid'
        );

        if(errorDiv)
        {
            errorDiv.innerHTML =
                'Marks cannot be negative';
        }

        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | Maximum
    |--------------------------------------------------------------------------
    */

    if(!isNaN(max) && value > max)
    {

        input.classList.add(
            'mark-invalid'
        );

        if(errorDiv)
        {
            errorDiv.innerHTML =
                'Maximum allowed marks is ' + max;
        }

        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | Valid
    |--------------------------------------------------------------------------
    */

    input.classList.add(
        'mark-valid'
    );

    return true;

}

</script>


{{-- ================================================================= --}}
{{-- SAVE FORM VALIDATION --}}
{{-- ================================================================= --}}

<script>

document.addEventListener(
    'DOMContentLoaded',
    function()
    {

        const saveForm =
            document.getElementById(
                'marksSaveForm'
            );


        if(!saveForm)
        {
            return;
        }


        saveForm.addEventListener(
            'submit',
            function(event)
            {

                let hasError = false;


                document
                    .querySelectorAll('.mark-input')
                    .forEach(function(input)
                    {

                        /*
                        |--------------------------------------------------------------------------
                        | Absent / locked inputs do not need validation.
                        |--------------------------------------------------------------------------
                        */

                        if(input.readOnly)
                        {
                            return;
                        }


                        if(
                            !validateMarkInput(input)
                        )
                        {

                            hasError = true;

                        }

                    });


                if(hasError)
                {

                    event.preventDefault();


                    Swal.fire({

                        icon:'error',

                        title:'Validation Error',

                        text:
                            'Please enter valid marks for all students before saving.'

                    });

                    return false;

                }


                /*
                |--------------------------------------------------------------------------
                | Prevent double click
                |--------------------------------------------------------------------------
                */

                const button =
                    document.getElementById(
                        'saveMarksButton'
                    );


                if(button)
                {

                    button.disabled = true;

                    button.innerHTML =
                        'Saving Marks...';

                }

            }
        );

    }
);

</script>


{{-- ================================================================= --}}
{{-- FINAL SUBMISSION --}}
{{-- ================================================================= --}}

<script>

document.addEventListener(
    'DOMContentLoaded',
    function()
    {

        const finalForm =
            document.getElementById(
                'finalSubmitForm'
            );


        if(!finalForm)
        {
            return;
        }


        finalForm.addEventListener(
            'submit',
            function(event)
            {

                event.preventDefault();


                /*
                |--------------------------------------------------------------------------
                | Check whether marks were modified after last Save
                |--------------------------------------------------------------------------
                */

                const modifiedFlag =
                    document.getElementById(
                        'marksModified'
                    );


                if(
                    modifiedFlag &&
                    modifiedFlag.value === '1'
                )
                {

                    Swal.fire({

                        icon:'warning',

                        title:'Save Required',

                        text:
                            'Marks have been modified. Please click Save Marks before Final Submission.',

                        confirmButtonText:
                            'OK'

                    });

                    return;

                }


                /*
                |--------------------------------------------------------------------------
                | Validate all visible mark fields
                |--------------------------------------------------------------------------
                */

                let hasError = false;


                document
                    .querySelectorAll('.mark-input')
                    .forEach(function(input)
                    {

                        if(input.readOnly)
                        {
                            return;
                        }


                        if(
                            !validateMarkInput(input)
                        )
                        {

                            hasError = true;

                        }

                    });


                if(hasError)
                {

                    Swal.fire({

                        icon:'error',

                        title:'Validation Error',

                        text:
                            'Please enter valid marks for all students before final submission.'

                    });

                    return;

                }


                /*
                |--------------------------------------------------------------------------
                | Final Confirmation
                |--------------------------------------------------------------------------
                */

                Swal.fire({

                    icon:'warning',

                    title:'Final Marks Submission',

                    html:`

                        <div style="text-align:left">

                            <b>
                                This is the FINAL submission of marks.
                            </b>

                            <br><br>

                            Please check all marks carefully.

                            <br><br>

                            After final submission:

                            <ul style="
                                margin-top:8px;
                                margin-left:20px;
                            ">

                                <li>
                                    Marks will be locked.
                                </li>

                                <li>
                                    Teacher will not be able to edit marks.
                                </li>

                                <li>
                                    Admin intervention will be required
                                    for corrections.
                                </li>

                            </ul>

                        </div>

                    `,

                    showCancelButton:true,

                    confirmButtonText:
                        'Submit Final Marks',

                    cancelButtonText:
                        'Cancel',

                    confirmButtonColor:
                        '#16A34A'

                }).then(function(result)
                {

                    if(result.isConfirmed)
                    {

                        const button =
                            document.getElementById(
                                'finalSubmitButton'
                            );


                        if(button)
                        {

                            button.disabled = true;

                            button.innerHTML =
                                'Submitting...';

                        }


                        finalForm.submit();

                    }

                });

            }
        );

    }
);

</script>


{{-- ================================================================= --}}
{{-- AUTO SAVE --}}
{{-- ================================================================= --}}

<script>

document.addEventListener(
    'DOMContentLoaded',
    function()
    {

        document
            .querySelectorAll('.mark-input')
            .forEach(function(input)
            {

                input.addEventListener(
                    'change',
                    function()
                    {

                        /*
                        |--------------------------------------------------------------------------
                        | Do not autosave absent / locked fields
                        |--------------------------------------------------------------------------
                        */

                        if(this.readOnly)
                        {
                            return;
                        }


                        let value =
                            this.value.trim();


                        if(value === '')
                        {
                            return;
                        }


                        let max =
                            parseFloat(this.max);


                        let numericValue =
                            parseFloat(value);


                        if(
                            isNaN(numericValue) ||
                            numericValue < 0 ||
                            numericValue > max
                        )
                        {
                            return;
                        }


                        fetch(
                            "{{ route('marks-entry.autosave') }}",
                            {

                                method:'POST',

                                headers:{

                                    'Content-Type':
                                        'application/json',

                                    'X-CSRF-TOKEN':
                                        '{{ csrf_token() }}',

                                    'Accept':
                                        'application/json'

                                },

                                body:JSON.stringify({

                                    student_id:
                                        this.dataset.student,

                                    field:
                                        this.dataset.field,

                                    value:
                                        value,

                                    exam_master_id:
                                        '{{ $examMasterId }}',

                                    teacher_subject_allocation_id:
                                        '{{ $teacherSubjectAllocationId }}'

                                })

                            }
                        )
                        .then(function(response)
                        {

                            if(!response.ok)
                            {

                                throw new Error(
                                    'Auto save failed'
                                );

                            }

                            return response.json();

                        })
                        .then(function(data)
                        {

                            console.log(
                                'Auto saved',
                                data
                            );

                        })
                        .catch(function(error)
                        {

                            console.error(
                                'Auto save error:',
                                error
                            );

                        });

                    }
                );

            });

    }
);

</script>


{{-- ================================================================= --}}
{{-- RESTORE ABSENT STATE --}}
{{-- ================================================================= --}}

<script>

document.addEventListener(
    'DOMContentLoaded',
    function()
    {

        document
            .querySelectorAll('.mark-input')
            .forEach(function(input)
            {

                const studentId =
                    input.dataset.student;


                const absentField =
                    document.getElementById(
                        'absent_' + studentId
                    );


                if(
                    absentField &&
                    absentField.value === '1'
                )
                {

                    input.readOnly = true;

                    input.classList.add(
                        'absent-input'
                    );

                }

            });

    }
);

</script>


</x-app-layout>