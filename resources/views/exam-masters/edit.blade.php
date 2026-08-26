<x-app-layout>

<style>

.exam-form,
.exam-form * {
    font-family: Arial, sans-serif !important;
    font-size: 12px !important;
}

.exam-form h2 {
    font-size: 18px !important;
    font-weight: 600 !important;
}

.exam-form h3 {
    font-size: 14px !important;
    font-weight: 600 !important;
}

.exam-form input[type="text"],
.exam-form input[type="number"] {
    height: 30px !important;
    padding: 4px 8px !important;
}

.exam-form select {
    height: 34px !important;
}

.exam-form .erp-btn {
    font-size: 12px !important;
    padding: 5px 12px !important;
}

.exam-form .section-box {
    padding: 12px !important;
}

.subject-code {
    color: #6b7280;
    font-size: 11px !important;
    margin-top: 2px;
}

.passing-percentage-note {
    margin-top: 8px;
    margin-bottom: 10px;
    font-size: 11px !important;
    font-weight: 600;
    color: #2563eb;
}

.info-note {
    background: #fff7ed;
    border: 1px solid #fed7aa;
    color: #9a3412;
    padding: 10px 12px;
    border-radius: 6px;
    font-size: 13px !important;
    line-height: 1.5;
}

.academic-year-note {
    margin-top: 8px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #166534;
    padding: 8px 10px;
    border-radius: 6px;
    font-size: 11px !important;
    font-weight: 600;
}

</style>


@php

    /*
    |--------------------------------------------------------------------------
    | EXAM TYPE
    |--------------------------------------------------------------------------
    */

    $examNameUpper =
        strtoupper(
            trim(
                $examMaster->exam_name ?? ''
            )
        );


    $examTypes = [
        'UNIT TEST 1',
        'UNIT TEST 2',
        'UNIT TEST 3',
        'UNIT TEST 4',
        'TERM 1',
        'TERM 2',
        'ANNUAL',
    ];


    $examTypeValue = '';


    foreach ($examTypes as $type) {

        if (
            $examNameUpper === $type
            ||
            str_starts_with(
                $examNameUpper,
                $type . ' -'
            )
        ) {

            $examTypeValue =
                $type;

            break;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CURRENT ACADEMIC YEAR
    |--------------------------------------------------------------------------
    */

    $currentAcademicYearId =
        old(
            'academic_year_id',
            $examMaster->academic_year_id
        );

@endphp


<div
    class="exam-form"
    style="
        max-width:1000px;
        margin:auto;
        padding:15px;
    "
>

    <div
        style="
            background:white;
            border-radius:12px;
            padding:20px;
            border:1px solid #d1d5db;
            box-shadow:0 4px 10px rgba(0,0,0,.15);
        "
    >


        {{-- =====================================================
             TITLE
        ====================================================== --}}

        <h2 class="text-center text-blue-600 mb-4">
            Edit Exam
        </h2>


        {{-- =====================================================
             VALIDATION ERRORS
        ====================================================== --}}

        @if($errors->any())

            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">

                <ul class="list-disc ml-5">

                    @foreach($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        {{-- =====================================================
             SUCCESS
        ====================================================== --}}

        @if(session('success'))

            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">

                {{ session('success') }}

            </div>

        @endif


        {{-- =====================================================
             ERROR
        ====================================================== --}}

        @if(session('error'))

            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">

                {{ session('error') }}

            </div>

        @endif


        {{-- =====================================================
             FORM
        ====================================================== --}}

        <form
            method="POST"
            action="{{ route(
                'exam-masters.update',
                $examMaster->id
            ) }}"
            id="examEditForm"
        >

            @csrf

            @method('PUT')


            {{-- =================================================
                 ACADEMIC YEAR + STANDARD
            ================================================== --}}

            <div class="grid grid-cols-2 gap-4 mb-5">


                {{-- ACADEMIC YEAR --}}

                <div>

                    <label
                        for="academic_year_id"
                        class="block font-semibold mb-2"
                    >
                        Academic Year
                    </label>

                    <select
                        name="academic_year_id"
                        id="academic_year_id"
                        class="w-full border rounded p-2"
                        required
                    >

                        <option value="">
                            Select Academic Year
                        </option>

                        @foreach($academicYears as $academicYear)

                            <option
                                value="{{ $academicYear->id }}"
                                {{ (string)$currentAcademicYearId === (string)$academicYear->id ? 'selected' : '' }}
                            >
                                {{ $academicYear->year_name }}
                            </option>

                        @endforeach

                    </select>


                    <div
                        id="academicYearNote"
                        class="academic-year-note"
                    >
                    </div>


                    @error('academic_year_id')

                        <div class="text-red-600 mt-1">
                            {{ $message }}
                        </div>

                    @enderror

                </div>


                {{-- STANDARD --}}

                <div>

                    <label
                        for="standard_id"
                        class="block font-semibold mb-2"
                    >
                        Standard
                    </label>

                    <select
                        name="standard_id"
                        id="standard_id"
                        class="w-full border rounded p-2"
                        required
                    >

                        <option value="">
                            Select Standard
                        </option>

                        @foreach($standards as $standard)

                            <option
                                value="{{ $standard->id }}"
                                {{ (int)$examMaster->standard_id === (int)$standard->id ? 'selected' : '' }}
                            >
                                {{ $standard->standard_name }}
                            </option>

                        @endforeach

                    </select>

                </div>

            </div>


            {{-- =================================================
                 EXAM TYPE
            ================================================== --}}

            <div class="mb-5">

                <label
                    for="exam_type"
                    class="block font-semibold mb-2"
                >
                    Exam Type
                </label>

                <select
                    id="exam_type"
                    name="exam_type"
                    class="w-full border rounded p-2"
                    required
                >

                    <option value="">
                        Select Exam Type
                    </option>

                    @foreach($examTypes as $type)

                        <option
                            value="{{ $type }}"
                            {{ $examTypeValue === $type ? 'selected' : '' }}
                        >
                            {{ ucwords(strtolower($type)) }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- =================================================
                 EXAM NAME
            ================================================== --}}

            <input
                type="hidden"
                name="exam_name"
                id="exam_name"
                value="{{ old(
                    'exam_name',
                    $examMaster->exam_name
                ) }}"
            >


            <div class="mb-5">

                <label
                    for="exam_name_preview"
                    class="block font-semibold mb-2"
                >
                    Exam Name
                </label>

                <input
                    type="text"
                    id="exam_name_preview"
                    value="{{ old(
                        'exam_name',
                        $examMaster->exam_name
                    ) }}"
                    readonly
                    class="w-full border rounded p-2 bg-gray-100"
                >

            </div>


            {{-- =================================================
                 SUBJECT MARKS CONFIGURATION
            ================================================== --}}

            <div
                class="border rounded section-box mb-6 bg-gray-50"
            >

                <div
                    class="flex justify-between items-center mb-3"
                >

                    <h3 class="font-bold">
                        Subject Wise Marks Configuration
                    </h3>


                    <span
                        id="subjectLoading"
                        class="text-blue-600"
                        style="display:none;"
                    >
                        Loading subjects...
                    </span>

                </div>


                {{-- PASSING PERCENTAGE --}}

                <div
                    id="passingPercentageNote"
                    class="passing-percentage-note"
                >
                </div>


                {{-- SUBJECT TABLE --}}

                <div class="overflow-x-auto">

                    <table
                        class="w-full border bg-white"
                    >

                        <thead>

                            <tr class="bg-blue-100">

                                <th
                                    class="border p-2"
                                    style="width:45%;"
                                >
                                    Subject
                                </th>


                                <th
                                    class="border p-2"
                                    style="width:20%;"
                                >
                                    Max Marks
                                </th>


                                <th
                                    class="border p-2"
                                    style="width:20%;"
                                >
                                    Passing Marks
                                </th>


                                <th
                                    class="border p-2"
                                    style="width:15%;"
                                >
                                    Type
                                </th>

                            </tr>

                        </thead>


                        <tbody id="subjectTableBody">

                            @if($subjects->isEmpty())

                                <tr>

                                    <td
                                        colspan="4"
                                        class="border p-3 text-center text-red-600"
                                    >
                                        No active subjects are mapped
                                        to this Standard.
                                    </td>

                                </tr>

                            @else

                                @foreach(
                                    $subjects as $index => $subject
                                )

                                    @php

                                        $subjectId =
                                            (int) $subject->subject_id;


                                        $maxMarks =
                                            (float) (
                                                $subject->max_marks
                                                ?? 40
                                            );


                                        $passingPercentage =
                                            (
                                                (int)
                                                $examMaster->standard_id
                                            ) === 9
                                            ||
                                            (
                                                (int)
                                                $examMaster->standard_id
                                            ) === 10
                                                ? 35
                                                : 40;


                                        $passingMarks =
                                            $maxMarks > 0
                                                ? (int) ceil(
                                                    $maxMarks *
                                                    (
                                                        $passingPercentage /
                                                        100
                                                    )
                                                )
                                                : 0;


                                        $displayOrder =
                                            $subject->display_order
                                            ??
                                            $subject->sort_order
                                            ??
                                            (
                                                $index + 1
                                            );


                                        $isOptional =
                                            (
                                                (int) (
                                                    $subject->is_optional
                                                    ?? 0
                                                )
                                            ) === 1;

                                    @endphp


                                    <tr>

                                        {{-- SUBJECT --}}

                                        <td
                                            class="border p-2"
                                        >

                                            <div
                                                style="
                                                    font-weight:600;
                                                "
                                            >

                                                {{ $subject->subject_name }}

                                            </div>


                                            @if(
                                                !empty(
                                                    $subject->subject_code
                                                )
                                            )

                                                <div
                                                    class="subject-code"
                                                >

                                                    Code:
                                                    {{ $subject->subject_code }}

                                                </div>

                                            @endif


                                            {{-- ACTUAL SUBJECT MASTER ID --}}

                                            <input
                                                type="hidden"
                                                name="subjects[{{ $subjectId }}][subject_id]"
                                                value="{{ $subjectId }}"
                                            >


                                            {{-- DISPLAY ORDER --}}

                                            <input
                                                type="hidden"
                                                name="subjects[{{ $subjectId }}][display_order]"
                                                value="{{ $displayOrder }}"
                                            >

                                        </td>


                                        {{-- MAX MARKS --}}

                                        <td
                                            class="border p-2"
                                        >

                                            <select
                                                name="subjects[{{ $subjectId }}][max_marks]"
                                                class="max-mark w-full border rounded p-1"
                                                data-subject-id="{{ $subjectId }}"
                                            >

                                                @foreach(
                                                    [20,25,40,50,80,100]
                                                    as $maxOption
                                                )

                                                    <option
                                                        value="{{ $maxOption }}"
                                                        {{ (float)$maxMarks === (float)$maxOption ? 'selected' : '' }}
                                                    >
                                                        {{ $maxOption }}
                                                    </option>

                                                @endforeach


                                                @if(
                                                    $maxMarks > 0
                                                    &&
                                                    !in_array(
                                                        $maxMarks,
                                                        [
                                                            20.0,
                                                            25.0,
                                                            40.0,
                                                            50.0,
                                                            80.0,
                                                            100.0
                                                        ],
                                                        true
                                                    )
                                                )

                                                    <option
                                                        value="{{ $maxMarks }}"
                                                        selected
                                                    >
                                                        {{ $maxMarks }}
                                                    </option>

                                                @endif

                                            </select>

                                        </td>


                                        {{-- PASSING MARKS --}}

                                        <td
                                            class="border p-2"
                                        >

                                            <input
                                                type="number"
                                                readonly
                                                value="{{ $passingMarks }}"
                                                class="passing-mark w-full border rounded p-1 bg-gray-100"
                                                data-subject-id="{{ $subjectId }}"
                                            >


                                            <input
                                                type="hidden"
                                                name="subjects[{{ $subjectId }}][passing_marks]"
                                                value="{{ $passingMarks }}"
                                                class="passing-mark-hidden"
                                                data-subject-id="{{ $subjectId }}"
                                            >

                                        </td>


                                        {{-- TYPE --}}

                                        <td
                                            class="border p-2 text-center"
                                        >

                                            @if($isOptional)

                                                <span
                                                    style="
                                                        color:#92400e;
                                                        font-weight:600;
                                                    "
                                                >
                                                    Optional
                                                </span>

                                            @else

                                                <span
                                                    style="
                                                        color:#166534;
                                                        font-weight:600;
                                                    "
                                                >
                                                    Compulsory
                                                </span>

                                            @endif

                                        </td>

                                    </tr>

                                @endforeach

                            @endif

                        </tbody>

                    </table>

                </div>

            </div>


            {{-- =================================================
                 DISPLAY ORDER
            ================================================== --}}

            <div class="mb-5">

                <label
                    for="display_order"
                    class="block font-semibold mb-2"
                >
                    Display Order
                </label>

                <input
                    type="number"
                    id="display_order"
                    name="display_order"
                    value="{{ old(
                        'display_order',
                        $examMaster->display_order
                    ) }}"
                    readonly
                    class="w-full border rounded p-2 bg-gray-100"
                >

            </div>


            {{-- =================================================
                 ACTIVE
            ================================================== --}}

            <div class="mb-5">

                <label class="flex items-center gap-2">

                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        {{ old(
                            'is_active',
                            $examMaster->is_active
                        ) ? 'checked' : '' }}
                    >

                    <span>
                        Active
                    </span>

                </label>

            </div>


            {{-- =================================================
                 INFORMATION / WARNING
            ================================================== --}}

            <div class="info-note mb-5">

                <strong>Important:</strong>

                All active subjects currently mapped to the selected
                Standard are automatically included in this Exam.

                <br>

                Subject selection is controlled by the
                <strong>Standard Wise Subject Master</strong>.

                <br><br>

                You only need to change the
                <strong>Max Marks</strong>.

                Passing Marks are calculated automatically according
                to the Standard's passing percentage.

                <br><br>

                <strong>Academic Year:</strong>

                This Exam Master now belongs to the selected Academic
                Year. Do not change Academic Year for an exam that is
                already being used for teacher allocations or marks.

            </div>


            {{-- =================================================
                 BUTTONS
            ================================================== --}}

            <div
                class="flex justify-between items-center mt-6"
            >

                <div></div>


                <div class="flex gap-2">

                    <button
                        type="submit"
                        class="erp-btn erp-btn-save"
                        id="updateExamButton"
                    >
                        Update
                    </button>


                    <a
                        href="{{ route(
                            'exam-masters.index'
                        ) }}"
                        class="erp-btn erp-btn-cancel"
                    >
                        Cancel
                    </a>

                </div>

            </div>

        </form>

    </div>

</div>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | ELEMENTS
        |--------------------------------------------------------------------------
        */

        const academicYearDropdown =
            document.getElementById(
                'academic_year_id'
            );


        const standardDropdown =
            document.getElementById(
                'standard_id'
            );


        const examType =
            document.getElementById(
                'exam_type'
            );


        const examName =
            document.getElementById(
                'exam_name'
            );


        const examNamePreview =
            document.getElementById(
                'exam_name_preview'
            );


        const tableBody =
            document.getElementById(
                'subjectTableBody'
            );


        const loading =
            document.getElementById(
                'subjectLoading'
            );


        const percentageNote =
            document.getElementById(
                'passingPercentageNote'
            );


        const academicYearNote =
            document.getElementById(
                'academicYearNote'
            );


        const form =
            document.getElementById(
                'examEditForm'
            );


        const updateButton =
            document.getElementById(
                'updateExamButton'
            );


        /*
        |--------------------------------------------------------------------------
        | PASSING PERCENTAGE
        |--------------------------------------------------------------------------
        */

        function getPassingPercentage()
        {
            const standardId =
                parseInt(
                    standardDropdown.value || 0,
                    10
                );


            if (
                standardId === 9
                ||
                standardId === 10
            ) {

                return 35;
            }


            return 40;
        }


        /*
        |--------------------------------------------------------------------------
        | ACADEMIC YEAR NOTE
        |--------------------------------------------------------------------------
        */

        function updateAcademicYearNote()
        {
            if (
                !academicYearDropdown.value
            ) {

                academicYearNote.textContent =
                    '';

                academicYearNote.style.display =
                    'none';

                return;
            }


            const selectedOption =
                academicYearDropdown.options[
                    academicYearDropdown.selectedIndex
                ];


            const yearText =
                selectedOption
                    ? selectedOption.text.trim()
                    : '';


            academicYearNote.textContent =
                'Selected Academic Year: '
                +
                yearText;


            academicYearNote.style.display =
                'block';
        }


        /*
        |--------------------------------------------------------------------------
        | DISPLAY PASSING PERCENTAGE
        |--------------------------------------------------------------------------
        */

        function updatePercentageNote()
        {
            if (
                !standardDropdown.value
            ) {

                percentageNote.textContent =
                    '';

                return;
            }


            percentageNote.textContent =
                'Passing percentage: '
                +
                getPassingPercentage()
                +
                '%';
        }


        /*
        |--------------------------------------------------------------------------
        | CALCULATE PASSING MARKS
        |--------------------------------------------------------------------------
        */

        function calculatePassingMarks(
            maxMarks
        )
        {
            const max =
                parseFloat(
                    maxMarks || 0
                );


            if (
                max <= 0
            ) {

                return 0;
            }


            return Math.ceil(
                max
                *
                getPassingPercentage()
                /
                100
            );
        }


        /*
        |--------------------------------------------------------------------------
        | BUILD EXAM NAME
        |--------------------------------------------------------------------------
        */

        function buildExamName()
        {
            const type =
                examType.value;


            const option =
                standardDropdown.options[
                    standardDropdown.selectedIndex
                ];


            const standardText =
                option
                &&
                option.value
                    ? option.text.trim()
                    : '';


            if (
                !type
                ||
                !standardText
            ) {

                examName.value =
                    '';

                examNamePreview.value =
                    '';

                return;
            }


            const generatedName =
                type
                +
                ' - '
                +
                standardText;


            examName.value =
                generatedName;


            examNamePreview.value =
                generatedName;
        }


        /*
        |--------------------------------------------------------------------------
        | BUILD MAX MARK OPTIONS
        |--------------------------------------------------------------------------
        */

        function buildMaxMarksOptions(
            selectedValue
        )
        {
            const values = [
                20,
                25,
                40,
                50,
                80,
                100
            ];


            const selected =
                parseFloat(
                    selectedValue || 40
                );


            let html =
                '';


            values.forEach(
                function (value) {

                    html += `
                        <option
                            value="${value}"
                            ${
                                selected === value
                                    ? 'selected'
                                    : ''
                            }
                        >
                            ${value}
                        </option>
                    `;

                }
            );


            if (
                selected > 0
                &&
                !values.includes(
                    selected
                )
            ) {

                html += `
                    <option
                        value="${selected}"
                        selected
                    >
                        ${selected}
                    </option>
                `;
            }


            return html;
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD SUBJECTS
        |--------------------------------------------------------------------------
        */

        async function loadSubjects(
            standardId,
            preserveValues = false
        )
        {
            if (
                !standardId
            ) {

                tableBody.innerHTML = `
                    <tr>
                        <td
                            colspan="4"
                            class="border p-3 text-center text-gray-500"
                        >
                            Select Standard First
                        </td>
                    </tr>
                `;


                updatePercentageNote();


                return;
            }


            loading.style.display =
                'inline';


            tableBody.innerHTML = `
                <tr>
                    <td
                        colspan="4"
                        class="border p-3 text-center"
                    >
                        Loading subjects...
                    </td>
                </tr>
            `;


            try {

                const response =
                    await fetch(
                        "{{ url('/exam-masters/load-subjects') }}/"
                        +
                        encodeURIComponent(
                            standardId
                        ),
                        {
                            method: 'GET',

                            headers: {

                                'Accept':
                                    'application/json',

                                'X-Requested-With':
                                    'XMLHttpRequest'

                            }
                        }
                    );


                if (
                    !response.ok
                ) {

                    throw new Error(
                        'HTTP '
                        +
                        response.status
                    );
                }


                const subjects =
                    await response.json();


                /*
                |--------------------------------------------------------------------------
                | Normally this is not used on initial load because PHP already
                | loaded the existing Exam Master configuration.
                |
                | If Standard is changed, we reload with default values.
                |--------------------------------------------------------------------------
                */

                renderSubjects(
                    subjects,
                    preserveValues
                );


            } catch (
                error
            ) {

                console.error(
                    'Unable to load subjects:',
                    error
                );


                tableBody.innerHTML = `
                    <tr>
                        <td
                            colspan="4"
                            class="border p-3 text-center text-red-600"
                        >
                            Unable to load subjects.
                        </td>
                    </tr>
                `;

            } finally {

                loading.style.display =
                    'none';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | RENDER SUBJECTS
        |--------------------------------------------------------------------------
        */

        function renderSubjects(
            subjects,
            preserveValues = false
        )
        {

            if (
                !Array.isArray(
                    subjects
                )
                ||
                subjects.length === 0
            ) {

                tableBody.innerHTML = `
                    <tr>
                        <td
                            colspan="4"
                            class="border p-3 text-center text-red-600"
                        >
                            No active subjects are mapped to this Standard.
                        </td>
                    </tr>
                `;


                updatePercentageNote();


                return;
            }


            let html =
                '';


            subjects.forEach(
                function (
                    subject,
                    index
                ) {

                    const subjectId =
                        subject.subject_id
                        ??
                        subject.id
                        ??
                        '';


                    const subjectName =
                        subject.subject_name
                        ??
                        '';


                    const subjectCode =
                        subject.subject_code
                        ??
                        '';


                    const isOptional =
                        Number(
                            subject.is_optional
                            ??
                            0
                        ) === 1;


                    let maxMarks =
                        40;


                    let displayOrder =
                        subject.sort_order
                        ??
                        (
                            index + 1
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | DEFAULT PASSING MARKS
                    |--------------------------------------------------------------------------
                    */

                    const passingMarks =
                        calculatePassingMarks(
                            maxMarks
                        );


                    html += `

                        <tr>

                            <td
                                class="border p-2"
                            >

                                <div
                                    style="
                                        font-weight:600;
                                    "
                                >
                                    ${escapeHtml(
                                        subjectName
                                    )}
                                </div>


                                ${
                                    subjectCode
                                        ? `
                                            <div
                                                class="subject-code"
                                            >
                                                Code:
                                                ${escapeHtml(
                                                    subjectCode
                                                )}
                                            </div>
                                        `
                                        : ''
                                }


                                <input
                                    type="hidden"
                                    name="subjects[${subjectId}][subject_id]"
                                    value="${escapeAttribute(
                                        subjectId
                                    )}"
                                >


                                <input
                                    type="hidden"
                                    name="subjects[${subjectId}][display_order]"
                                    value="${escapeAttribute(
                                        displayOrder
                                    )}"
                                >

                            </td>


                            <td
                                class="border p-2"
                            >

                                <select
                                    name="subjects[${subjectId}][max_marks]"
                                    class="max-mark w-full border rounded p-1"
                                    data-subject-id="${escapeAttribute(
                                        subjectId
                                    )}"
                                >

                                    ${buildMaxMarksOptions(
                                        maxMarks
                                    )}

                                </select>

                            </td>


                            <td
                                class="border p-2"
                            >

                                <input
                                    type="number"
                                    readonly
                                    value="${passingMarks}"
                                    class="passing-mark w-full border rounded p-1 bg-gray-100"
                                    data-subject-id="${escapeAttribute(
                                        subjectId
                                    )}"
                                >


                                <input
                                    type="hidden"
                                    name="subjects[${subjectId}][passing_marks]"
                                    value="${passingMarks}"
                                    class="passing-mark-hidden"
                                    data-subject-id="${escapeAttribute(
                                        subjectId
                                    )}"
                                >

                            </td>


                            <td
                                class="border p-2 text-center"
                            >

                                ${
                                    isOptional
                                        ? `
                                            <span
                                                style="
                                                    color:#92400e;
                                                    font-weight:600;
                                                "
                                            >
                                                Optional
                                            </span>
                                        `
                                        : `
                                            <span
                                                style="
                                                    color:#166534;
                                                    font-weight:600;
                                                "
                                            >
                                                Compulsory
                                            </span>
                                        `
                                }

                            </td>

                        </tr>

                    `;
                }
            );


            tableBody.innerHTML =
                html;


            bindMaxMarks();


            updatePercentageNote();
        }


        /*
        |--------------------------------------------------------------------------
        | BIND MAX MARKS
        |--------------------------------------------------------------------------
        */

        function bindMaxMarks()
        {
            document
                .querySelectorAll(
                    '.max-mark'
                )
                .forEach(
                    function (
                        select
                    ) {

                        select.addEventListener(
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


                                const passingMarks =
                                    calculatePassingMarks(
                                        this.value
                                    );


                                const visibleInput =
                                    row.querySelector(
                                        '.passing-mark'
                                    );


                                const hiddenInput =
                                    row.querySelector(
                                        '.passing-mark-hidden'
                                    );


                                if (
                                    visibleInput
                                ) {

                                    visibleInput.value =
                                        passingMarks;
                                }


                                if (
                                    hiddenInput
                                ) {

                                    hiddenInput.value =
                                        passingMarks;
                                }

                            }
                        );

                    }
                );
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE ALL PASSING MARKS
        |--------------------------------------------------------------------------
        */

        function updateAllPassingMarks()
        {
            document
                .querySelectorAll(
                    '.max-mark'
                )
                .forEach(
                    function (
                        select
                    ) {

                        const row =
                            select.closest(
                                'tr'
                            );


                        if (
                            !row
                        ) {

                            return;
                        }


                        const passingMarks =
                            calculatePassingMarks(
                                select.value
                            );


                        const visibleInput =
                            row.querySelector(
                                '.passing-mark'
                            );


                        const hiddenInput =
                            row.querySelector(
                                '.passing-mark-hidden'
                            );


                        if (
                            visibleInput
                        ) {

                            visibleInput.value =
                                passingMarks;
                        }


                        if (
                            hiddenInput
                        ) {

                            hiddenInput.value =
                                passingMarks;
                        }

                    }
                );
        }


        /*
        |--------------------------------------------------------------------------
        | ESCAPE HTML
        |--------------------------------------------------------------------------
        */

        function escapeHtml(
            value
        )
        {
            return String(
                value
            )
            .replace(
                /&/g,
                '&amp;'
            )
            .replace(
                /</g,
                '&lt;'
            )
            .replace(
                />/g,
                '&gt;'
            )
            .replace(
                /"/g,
                '&quot;'
            )
            .replace(
                /'/g,
                '&#039;'
            );
        }


        function escapeAttribute(
            value
        )
        {
            return escapeHtml(
                value
            );
        }


        /*
        |--------------------------------------------------------------------------
        | ACADEMIC YEAR CHANGE
        |--------------------------------------------------------------------------
        */

        academicYearDropdown.addEventListener(
            'change',
            function () {

                updateAcademicYearNote();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | STANDARD CHANGE
        |--------------------------------------------------------------------------
        */

        standardDropdown.addEventListener(
            'change',
            async function () {

                buildExamName();

                updatePercentageNote();


                /*
                | When the Standard changes, existing subject configuration
                | cannot safely be reused because the subject list changes.
                */

                await loadSubjects(
                    this.value,
                    false
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | EXAM TYPE CHANGE
        |--------------------------------------------------------------------------
        */

        examType.addEventListener(
            'change',
            function () {

                buildExamName();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | FORM SUBMIT
        |--------------------------------------------------------------------------
        */

        form.addEventListener(
            'submit',
            function (event) {

                /*
                |--------------------------------------------------------------------------
                | ACADEMIC YEAR
                |--------------------------------------------------------------------------
                */

                if (
                    !academicYearDropdown.value
                ) {

                    event.preventDefault();

                    alert(
                        'Please select Academic Year.'
                    );

                    academicYearDropdown.focus();

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | STANDARD
                |--------------------------------------------------------------------------
                */

                if (
                    !standardDropdown.value
                ) {

                    event.preventDefault();

                    alert(
                        'Please select Standard.'
                    );

                    standardDropdown.focus();

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | EXAM TYPE
                |--------------------------------------------------------------------------
                */

                if (
                    !examType.value
                ) {

                    event.preventDefault();

                    alert(
                        'Please select Exam Type.'
                    );

                    examType.focus();

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | SUBJECTS
                |--------------------------------------------------------------------------
                */

                const subjectInputs =
                    tableBody.querySelectorAll(
                        '.max-mark'
                    );


                if (
                    subjectInputs.length === 0
                ) {

                    event.preventDefault();

                    alert(
                        'No active subjects are mapped to the selected Standard.'
                    );

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | EXAM NAME
                |--------------------------------------------------------------------------
                */

                buildExamName();


                if (
                    !examName.value
                ) {

                    event.preventDefault();

                    alert(
                        'Unable to generate Exam Name.'
                    );

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | PASSING MARKS
                |--------------------------------------------------------------------------
                */

                updateAllPassingMarks();


                /*
                |--------------------------------------------------------------------------
                | PREVENT DOUBLE SUBMISSION
                |--------------------------------------------------------------------------
                */

                updateButton.disabled =
                    true;

                updateButton.innerText =
                    'Updating...';

            }
        );


        /*
        |--------------------------------------------------------------------------
        | INITIALIZATION
        |--------------------------------------------------------------------------
        */

        updateAcademicYearNote();

        buildExamName();

        updatePercentageNote();

        bindMaxMarks();

    });

</script>

</x-app-layout>