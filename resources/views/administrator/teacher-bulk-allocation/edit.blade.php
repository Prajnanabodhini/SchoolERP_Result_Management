@extends('layouts.app')

@section('content')

<div style="
    max-width:1200px;
    margin:auto;
    padding:20px;
">

    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <div style="
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:20px;
    ">

        <h2 style="
            font-size:24px;
            font-weight:bold;
            color:#1d4ed8;
            margin:0;
        ">
            Edit Teacher Bulk Allocation
        </h2>

        <a
            href="{{ route('teacher-bulk-allocation.index') }}"
            class="erp-btn erp-btn-cancel"
        >
            Back
        </a>

    </div>


    {{-- =========================================================
         SUCCESS MESSAGE
    ========================================================== --}}

    @if(session('success'))

        <div style="
            background:#dcfce7;
            color:#166534;
            padding:12px;
            border-radius:6px;
            margin-bottom:15px;
        ">
            {{ session('success') }}
        </div>

    @endif


    {{-- =========================================================
         ERROR MESSAGE
    ========================================================== --}}

    @if(session('error'))

        <div style="
            background:#fee2e2;
            color:#991b1b;
            padding:12px;
            border-radius:6px;
            margin-bottom:15px;
        ">
            {{ session('error') }}
        </div>

    @endif


    {{-- =========================================================
         VALIDATION ERRORS
    ========================================================== --}}

    @if($errors->any())

        <div style="
            background:#fee2e2;
            color:#991b1b;
            padding:12px;
            border-radius:6px;
            margin-bottom:15px;
        ">

            <ul style="
                margin:0;
                padding-left:20px;
            ">

                @foreach($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    {{-- =========================================================
         IMPORTANT:
         NORMALIZE SAVED SUBJECT IDS
         
         This supports:
         1. [1,2,3]
         2. Collection of Subject models
         3. Collection containing subject_id
         4. Collection containing id
         5. Array of objects
    ========================================================== --}}

    @php

        $savedSubjectIds = collect($selectedSubjects ?? [])
            ->map(function ($item) {

                /*
                |--------------------------------------------------
                | If item is an array
                |--------------------------------------------------
                */

                if (is_array($item)) {

                    return $item['subject_id']
                        ?? $item['id']
                        ?? null;
                }


                /*
                |--------------------------------------------------
                | If item is an object / Eloquent model
                |--------------------------------------------------
                */

                if (is_object($item)) {

                    return $item->subject_id
                        ?? $item->id
                        ?? null;
                }


                /*
                |--------------------------------------------------
                | If item is already an integer/string ID
                |--------------------------------------------------
                */

                return $item;

            })
            ->filter(function ($id) {

                return $id !== null
                    && $id !== ''
                    && is_numeric($id);

            })
            ->map(function ($id) {

                return (int)$id;

            })
            ->unique()
            ->values()
            ->toArray();

    @endphp


    {{-- =========================================================
         DEBUG INFORMATION
         REMOVE THIS BLOCK AFTER TESTING IF NOT REQUIRED
    ========================================================== --}}

    {{--
    <div style="
        background:#eff6ff;
        border:1px solid #93c5fd;
        padding:10px;
        margin-bottom:15px;
        border-radius:5px;
    ">

        <strong>Saved Subject IDs:</strong>

        {{ implode(', ', $savedSubjectIds) }}

    </div>
    --}}


    {{-- =========================================================
         MAIN FORM
    ========================================================== --}}

    <form
        method="POST"
        action="{{ route('teacher-bulk-allocation.update', $allocation->id) }}"
        id="editAllocationForm"
    >

        @csrf

        @method('PUT')


        {{-- =====================================================
             ALLOCATION DETAILS
        ====================================================== --}}

        <div style="
            background:white;
            border:1px solid #d1d5db;
            border-radius:10px;
            padding:18px;
            box-shadow:0 3px 8px rgba(0,0,0,.08);
        ">

            <h3 style="
                font-size:18px;
                font-weight:bold;
                color:#2563eb;
                margin:0 0 15px 0;
            ">
                Allocation Details
            </h3>


            {{-- =================================================
                 FIRST ROW
            ================================================== --}}

            <div style="
                display:grid;
                grid-template-columns:
                    minmax(180px,1fr)
                    minmax(180px,1fr)
                    minmax(180px,1fr);
                gap:15px;
                margin-bottom:15px;
            ">


                {{-- TEACHER --}}

                <div>

                    <label style="
                        display:block;
                        font-weight:600;
                        margin-bottom:5px;
                    ">
                        Teacher
                    </label>

                    <select
                        name="user_id"
                        required
                        style="
                            width:100%;
                            padding:8px 10px;
                            border:1px solid #d1d5db;
                            border-radius:5px;
                            background:white;
                        "
                    >

                        <option value="">
                            Select Teacher
                        </option>

                        @foreach($teachers as $teacher)

                            <option
                                value="{{ $teacher->id }}"
                                {{ (int)$allocation->user_id === (int)$teacher->id ? 'selected' : '' }}
                            >
                                {{ $teacher->name }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- ACADEMIC YEAR --}}

                <div>

                    <label style="
                        display:block;
                        font-weight:600;
                        margin-bottom:5px;
                    ">
                        Academic Year
                    </label>

                    <select
                        name="academic_year_id"
                        required
                        style="
                            width:100%;
                            padding:8px 10px;
                            border:1px solid #d1d5db;
                            border-radius:5px;
                            background:white;
                        "
                    >

                        <option value="">
                            Select Academic Year
                        </option>

                        @foreach($academicYears as $year)

                            <option
                                value="{{ $year->id }}"
                                {{ (int)$allocation->academic_year_id === (int)$year->id ? 'selected' : '' }}
                            >
                                {{ $year->year_name }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- EXAM --}}

                <div>

                    <label style="
                        display:block;
                        font-weight:600;
                        margin-bottom:5px;
                    ">
                        Exam
                    </label>

                    <select
                        name="exam_master_id"
                        required
                        style="
                            width:100%;
                            padding:8px 10px;
                            border:1px solid #d1d5db;
                            border-radius:5px;
                            background:white;
                        "
                    >

                        <option value="">
                            Select Exam
                        </option>

                        @foreach($exams as $exam)

                            <option
                                value="{{ $exam->id }}"
                                {{ (int)($selectedExam ?? $allocation->exam_master_id ?? 0) === (int)$exam->id ? 'selected' : '' }}
                            >
                                {{ $exam->exam_name }}
                            </option>

                        @endforeach

                    </select>

                </div>

            </div>


            {{-- =================================================
                 SECOND ROW
                 STANDARD + DIVISION
            ================================================== --}}

            <div style="
                display:grid;
                grid-template-columns:
                    minmax(180px,1fr)
                    minmax(180px,1fr);
                gap:15px;
            ">


                {{-- =================================================
                     STANDARD - FROZEN TEXT BOX
                ================================================== --}}

                <div>

                    <label style="
                        display:block;
                        font-weight:600;
                        margin-bottom:5px;
                    ">
                        Standard
                    </label>

                    @php

                        /*
                        |--------------------------------------------------
                        | Try relationship first
                        |--------------------------------------------------
                        */

                        $displayStandardName =
                            $allocation->standard->standard_name
                            ?? $standard->standard_name
                            ?? '';


                        /*
                        |--------------------------------------------------
                        | If standard_name is directly available
                        |--------------------------------------------------
                        */

                        if (!$displayStandardName) {

                            $displayStandardName =
                                $allocation->standard_name
                                ?? '';

                        }

                    @endphp

                    <input
                        type="text"
                        value="{{ $displayStandardName }}"
                        readonly
                        style="
                            width:100%;
                            padding:8px 10px;
                            border:1px solid #d1d5db;
                            border-radius:5px;
                            background:#f3f4f6;
                            color:#374151;
                            font-weight:600;
                            cursor:not-allowed;
                        "
                    >

                    {{-- IMPORTANT:
                         Keep standard_id hidden so controller
                         continues receiving it.
                    --}}

                    <input
                        type="hidden"
                        name="standard_id"
                        value="{{ $allocation->standard_id }}"
                    >

                </div>


                {{-- =================================================
                     DIVISION
                ================================================== --}}

                <div>

                    <label style="
                        display:block;
                        font-weight:600;
                        margin-bottom:5px;
                    ">
                        Division
                    </label>

                    <select
                        name="division_id"
                        required
                        style="
                            width:100%;
                            padding:8px 10px;
                            border:1px solid #d1d5db;
                            border-radius:5px;
                            background:white;
                        "
                    >

                        <option value="">
                            Select Division
                        </option>

                        @foreach($divisions as $division)

                            <option
                                value="{{ $division->id }}"
                                {{ (int)$allocation->division_id === (int)$division->id ? 'selected' : '' }}
                            >
                                {{ $division->division_name }}
                            </option>

                        @endforeach

                    </select>

                </div>

            </div>

        </div>


        {{-- =========================================================
             SUBJECTS
        ========================================================== --}}

        <div style="
            background:white;
            border:1px solid #d1d5db;
            border-radius:10px;
            padding:18px;
            margin-top:15px;
            box-shadow:0 3px 8px rgba(0,0,0,.08);
        ">

            <div style="
                display:flex;
                justify-content:space-between;
                align-items:center;
                margin-bottom:12px;
            ">

                <h3 style="
                    font-size:18px;
                    font-weight:bold;
                    color:#2563eb;
                    margin:0;
                ">
                    Subjects
                </h3>

                <span style="
                    font-size:13px;
                    color:#6b7280;
                ">
                    Previously selected subjects are checked automatically.
                </span>

            </div>


            <div
                id="subjectsContainer"
                style="
                    border:1px solid #d1d5db;
                    padding:12px;
                    border-radius:6px;
                    background:#f9fafb;
                    min-height:50px;
                "
            >

                @forelse($subjects as $subject)

                    @php

                        /*
                        |==================================================
                        | DETERMINE ACTUAL SUBJECT ID
                        |==================================================
                        |
                        | The checkbox MUST submit subjects.id.
                        |
                        | If controller returns:
                        |
                        | subject_id = subjects.id
                        |
                        | use that.
                        |
                        | Otherwise use subject->id.
                        |
                        */

                        $actualSubjectId =
                            $subject->subject_id
                            ?? $subject->id
                            ?? null;


                        /*
                        |==================================================
                        | NORMALIZE TO INTEGER
                        |==================================================
                        */

                        $actualSubjectId =
                            is_numeric($actualSubjectId)
                                ? (int)$actualSubjectId
                                : null;


                        /*
                        |==================================================
                        | CHECK WHETHER THIS SUBJECT WAS PREVIOUSLY SAVED
                        |==================================================
                        */

                        $isChecked =
                            $actualSubjectId !== null
                            &&
                            in_array(
                                $actualSubjectId,
                                $savedSubjectIds,
                                true
                            );

                    @endphp


                    @if($actualSubjectId !== null)

                        <label
                            style="
                                display:inline-flex;
                                align-items:center;
                                gap:7px;
                                margin-right:10px;
                                margin-bottom:8px;
                                padding:7px 11px;
                                border:1px solid
                                    {{ $isChecked ? '#2563eb' : '#d1d5db' }};
                                border-radius:6px;
                                background:
                                    {{ $isChecked ? '#eff6ff' : 'white' }};
                                cursor:pointer;
                                font-size:14px;
                            "
                        >

                            <input
                                type="checkbox"
                                name="subjects[]"
                                value="{{ $actualSubjectId }}"
                                {{ $isChecked ? 'checked' : '' }}
                            >

                            <span
                                style="
                                    font-weight:
                                    {{ $isChecked ? '600' : '400' }};
                                "
                            >
                                {{ $subject->subject_name }}
                            </span>

                        </label>

                    @endif

                @empty

                    <span style="
                        color:#dc2626;
                    ">
                        No subjects found for this Standard.
                    </span>

                @endforelse

            </div>

        </div>


        {{-- =========================================================
             BUTTONS
        ========================================================== --}}

        <div style="
            margin-top:18px;
            display:flex;
            gap:10px;
            align-items:center;
        ">

            <button
                type="submit"
                class="erp-btn erp-btn-save"
            >
                Update Allocation
            </button>

            <a
                href="{{ route('teacher-bulk-allocation.index') }}"
                class="erp-btn erp-btn-cancel"
            >
                Cancel
            </a>

        </div>

    </form>

</div>


{{-- =============================================================
     SWEET ALERT
============================================================== --}}

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const form =
        document.getElementById('editAllocationForm');


    if (!form) {
        return;
    }


    /*
    |--------------------------------------------------------------
    | FORM VALIDATION
    |--------------------------------------------------------------
    */

    form.addEventListener('submit', function (event) {

        const teacher =
            document.querySelector(
                '[name="user_id"]'
            );


        const academicYear =
            document.querySelector(
                '[name="academic_year_id"]'
            );


        const exam =
            document.querySelector(
                '[name="exam_master_id"]'
            );


        const standard =
            document.querySelector(
                '[name="standard_id"]'
            );


        const division =
            document.querySelector(
                '[name="division_id"]'
            );


        const subjects =
            document.querySelectorAll(
                'input[name="subjects[]"]:checked'
            );


        /*
        |----------------------------------------------------------
        | TEACHER
        |----------------------------------------------------------
        */

        if (!teacher || !teacher.value) {

            event.preventDefault();

            Swal.fire({
                icon:'error',
                title:'Validation Error',
                text:'Please select Teacher.'
            });

            return;
        }


        /*
        |----------------------------------------------------------
        | ACADEMIC YEAR
        |----------------------------------------------------------
        */

        if (!academicYear || !academicYear.value) {

            event.preventDefault();

            Swal.fire({
                icon:'error',
                title:'Validation Error',
                text:'Please select Academic Year.'
            });

            return;
        }


        /*
        |----------------------------------------------------------
        | EXAM
        |----------------------------------------------------------
        */

        if (!exam || !exam.value) {

            event.preventDefault();

            Swal.fire({
                icon:'error',
                title:'Validation Error',
                text:'Please select Exam.'
            });

            return;
        }


        /*
        |----------------------------------------------------------
        | STANDARD
        |----------------------------------------------------------
        |
        | Standard is now a frozen text box.
        | We still submit standard_id as hidden field.
        |
        */

        if (!standard || !standard.value) {

            event.preventDefault();

            Swal.fire({
                icon:'error',
                title:'Validation Error',
                text:'Standard information is missing.'
            });

            return;
        }


        /*
        |----------------------------------------------------------
        | DIVISION
        |----------------------------------------------------------
        */

        if (!division || !division.value) {

            event.preventDefault();

            Swal.fire({
                icon:'error',
                title:'Validation Error',
                text:'Please select Division.'
            });

            return;
        }


        /*
        |----------------------------------------------------------
        | SUBJECTS
        |----------------------------------------------------------
        */

        if (subjects.length === 0) {

            event.preventDefault();

            Swal.fire({
                icon:'error',
                title:'Validation Error',
                text:'Please select at least one Subject.'
            });

            return;
        }

    });


    /*
    |--------------------------------------------------------------
    | VISUAL CHECKBOX HIGHLIGHT
    |--------------------------------------------------------------
    */

    document
        .querySelectorAll(
            'input[name="subjects[]"]'
        )
        .forEach(function (checkbox) {

            function updateLabel() {

                const label =
                    checkbox.closest('label');

                if (!label) {
                    return;
                }


                if (checkbox.checked) {

                    label.style.borderColor =
                        '#2563eb';

                    label.style.background =
                        '#eff6ff';

                } else {

                    label.style.borderColor =
                        '#d1d5db';

                    label.style.background =
                        'white';

                }

            }


            checkbox.addEventListener(
                'change',
                updateLabel
            );


            /*
            | Initial state
            */

            updateLabel();

        });

});

</script>

@endsection