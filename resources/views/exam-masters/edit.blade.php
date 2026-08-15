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

</style>


<div class="exam-form"
     style="max-width:1000px; margin:auto; padding:15px;">

    <div style="
        background:white;
        border-radius:12px;
        padding:20px;
        border:1px solid #d1d5db;
        box-shadow:0 4px 10px rgba(0,0,0,0.15);
    ">

        {{-- =====================================================
             TITLE
        ====================================================== --}}

        <h2 class="text-center text-blue-600 mb-6">
            Edit Exam
        </h2>


        {{-- =====================================================
             VALIDATION ERRORS
        ====================================================== --}}

        @if ($errors->any())

            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">

                <ul class="list-disc ml-5">

                    @foreach ($errors->all() as $error)

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
             FORM
        ====================================================== --}}

        <form method="POST"
              action="{{ route('exam-masters.update', $examMaster->id) }}">

            @csrf

            @method('PUT')


            {{-- =================================================
                 STANDARD / EXAM TYPE / EXAM NAME
            ================================================== --}}

            <div class="grid grid-cols-3 gap-4 mb-8">


                {{-- STANDARD --}}

                <div>

                    <label class="block font-semibold mb-2">
                        Standard
                    </label>

                    <select name="standard_id"
                            id="standard_id"
                            class="w-full border rounded p-2"
                            required>

                        <option value="">
                            Select Standard
                        </option>

                        @foreach($standards as $standard)

                            <option value="{{ $standard->id }}"
                                {{ old(
                                    'standard_id',
                                    $examMaster->standard_id
                                ) == $standard->id ? 'selected' : '' }}>

                                {{ $standard->standard_name }}

                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- EXAM TYPE --}}

                <div>

                    <label class="block font-semibold mb-2">
                        Exam Type
                    </label>

                    <select id="exam_type"
                            class="w-full border rounded p-2"
                            required>

                        <option value="">
                            Select Exam Type
                        </option>

                        <option value="UNIT TEST 1">
                            Unit Test 1
                        </option>

                        <option value="UNIT TEST 2">
                            Unit Test 2
                        </option>

                        <option value="UNIT TEST 3">
                            Unit Test 3
                        </option>

                        <option value="UNIT TEST 4">
                            Unit Test 4
                        </option>

                        <option value="TERM 1">
                            Term 1
                        </option>

                        <option value="TERM 2">
                            Term 2
                        </option>

                        <option value="ANNUAL">
                            Annual
                        </option>

                    </select>

                </div>


                {{-- EXAM NAME --}}

                <div>

                    <label class="block font-semibold mb-2">
                        Exam Name
                    </label>


                    {{-- Actual submitted value --}}

                    <input type="hidden"
                           name="exam_name"
                           id="exam_name"
                           value="{{ old(
                               'exam_name',
                               $examMaster->exam_name
                           ) }}">


                    {{-- Display only --}}

                    <input type="text"
                           id="exam_name_preview"
                           value="{{ old(
                               'exam_name',
                               $examMaster->exam_name
                           ) }}"
                           readonly
                           class="w-full border rounded p-2 bg-gray-100">

                </div>

            </div>


            {{-- =====================================================
                 SUBJECT WISE MARKS CONFIGURATION
            ====================================================== --}}

            <div class="border rounded section-box mb-4 bg-gray-50">

                <h3 class="font-bold mb-3">
                    Subject Wise Marks Configuration
                </h3>


                <table class="w-full border">

                    <thead>

                        <tr class="bg-blue-100">

                            {{-- SELECT ALL --}}

                            <th class="border p-2 text-center">

                                <label style="
                                    display:flex;
                                    align-items:center;
                                    justify-content:center;
                                    gap:5px;
                                ">

                                    <input type="checkbox"
                                           id="selectAllSubjects">

                                    <span>
                                        All
                                    </span>

                                </label>

                            </th>


                            {{-- SUBJECT --}}

                            <th class="border p-2">
                                Subject
                            </th>


                            {{-- MAX MARKS --}}

                            <th class="border p-2">
                                Max Marks
                            </th>


                            {{-- PASSING MARKS --}}

                            <th class="border p-2">
                                Passing Marks
                            </th>

                        </tr>

                    </thead>


                    <tbody id="subjectTableBody">


                        {{-- =================================================
                             EXISTING SUBJECTS
                        ================================================== --}}

                        @forelse($subjects as $index => $subject)

                            <tr>

                                {{-- CHECKBOX --}}

                                <td class="border p-2 text-center">

                                    <input type="checkbox"
                                           class="subject-checkbox"
                                           name="subjects[{{ $index }}][selected]"
                                           value="1"
                                           checked>

                                </td>


                                {{-- SUBJECT --}}

                                <td class="border p-2">

                                    {{ $subject->subject_name }}

                                    <input type="hidden"
                                           name="subjects[{{ $index }}][subject_id]"
                                           value="{{ $subject->subject_id }}">

                                    <input type="hidden"
                                           name="subjects[{{ $index }}][display_order]"
                                           value="{{ $subject->display_order ?? ($index + 1) }}">

                                </td>


                                {{-- MAX MARKS --}}

                                <td class="border p-2">

                                    <select name="subjects[{{ $index }}][max_marks]"
                                            class="max-mark w-full border rounded p-1">

                                        <option value="20"
                                            {{ (int)$subject->max_marks === 20 ? 'selected' : '' }}>
                                            20
                                        </option>

                                        <option value="25"
                                            {{ (int)$subject->max_marks === 25 ? 'selected' : '' }}>
                                            25
                                        </option>

                                        <option value="40"
                                            {{ (int)$subject->max_marks === 40 ? 'selected' : '' }}>
                                            40
                                        </option>

                                        <option value="50"
                                            {{ (int)$subject->max_marks === 50 ? 'selected' : '' }}>
                                            50
                                        </option>

                                        <option value="80"
                                            {{ (int)$subject->max_marks === 80 ? 'selected' : '' }}>
                                            80
                                        </option>

                                        <option value="100"
                                            {{ (int)$subject->max_marks === 100 ? 'selected' : '' }}>
                                            100
                                        </option>

                                    </select>

                                </td>


                                {{-- PASSING MARKS --}}

                                <td class="border p-2">

                                    <input type="number"
                                           readonly
                                           name="subjects[{{ $index }}][passing_marks]"
                                           value="{{ $subject->passing_marks }}"
                                           class="passing-mark w-full border rounded p-1 bg-gray-100">

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="4"
                                    class="border p-3 text-center">

                                    No subjects configured.

                                </td>

                            </tr>

                        @endforelse


                    </tbody>

                </table>

            </div>


            {{-- =====================================================
                 DISPLAY ORDER
            ====================================================== --}}

            <div class="mb-4">

                <label class="block font-semibold mb-2">
                    Display Order
                </label>

                <input type="number"
                       name="display_order"
                       value="{{ old(
                           'display_order',
                           $examMaster->display_order
                       ) }}"
                       readonly
                       class="w-full border rounded p-2 bg-gray-100">

            </div>


            {{-- =====================================================
                 ACTIVE + BUTTONS
            ====================================================== --}}

            <div class="flex justify-between items-center mt-6">


                {{-- ACTIVE --}}

                <label>

                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           {{ old(
                               'is_active',
                               $examMaster->is_active
                           ) ? 'checked' : '' }}>

                    Active

                </label>


                {{-- BUTTONS --}}

                <div>

                    <button type="submit"
                            class="erp-btn erp-btn-save">

                        Update

                    </button>


                    <a href="{{ route('exam-masters.index') }}"
                       class="erp-btn erp-btn-cancel">

                        Cancel

                    </a>

                </div>

            </div>


        </form>

    </div>

</div>


{{-- =========================================================
     JAVASCRIPT
========================================================= --}}

<script>

document.addEventListener('DOMContentLoaded', function () {


    /* =========================================================
       ELEMENTS
    ========================================================= */

    const examType =
        document.getElementById('exam_type');

    const standard =
        document.getElementById('standard_id');

    const examName =
        document.getElementById('exam_name');

    const preview =
        document.getElementById('exam_name_preview');

    const tableBody =
        document.getElementById('subjectTableBody');


    /* =========================================================
       EXISTING EXAM NAME
       Example:
       UNIT TEST 1 - 5th
    ========================================================= */

    function setInitialExamType()
    {

        const existingExamName =
            @json($examMaster->exam_name);


        if (!existingExamName) {
            return;
        }


        const examTypes = [

            'UNIT TEST 1',
            'UNIT TEST 2',
            'UNIT TEST 3',
            'UNIT TEST 4',
            'TERM 1',
            'TERM 2',
            'ANNUAL'

        ];


        examTypes.forEach(function(type) {

            if (
                existingExamName
                    .toUpperCase()
                    .startsWith(type)
            ) {

                examType.value = type;

            }

        });

    }


    /* =========================================================
       BUILD EXAM NAME
    ========================================================= */

    function buildExamName()
    {

        const exam =
            examType.value;


        const selectedOption =
            standard.options[
                standard.selectedIndex
            ];


        const standardText =
            selectedOption
                ? selectedOption.text.trim()
                : '';


        if (
            !exam ||
            !standard.value
        ) {

            examName.value = '';

            preview.value = '';

            return;

        }


        const generatedName =
            exam + ' - ' + standardText;


        examName.value =
            generatedName;


        preview.value =
            generatedName;

    }


    /* =========================================================
       PASSING MARKS = 40%
       ROUND UP
    ========================================================= */

    function bindPassingMarks()
    {

        document
            .querySelectorAll('.max-mark')
            .forEach(function(select) {


                function updatePassing()
                {

                    const maxMarks =
                        parseFloat(
                            select.value
                        ) || 0;


                    const passingMarks =
                        Math.ceil(
                            maxMarks * 0.40
                        );


                    const row =
                        select.closest('tr');


                    const passingInput =
                        row
                            ? row.querySelector(
                                '.passing-mark'
                            )
                            : null;


                    if (passingInput) {

                        passingInput.value =
                            passingMarks;

                    }

                }


                updatePassing();


                select.addEventListener(
                    'change',
                    updatePassing
                );

            });

    }


    /* =========================================================
       SELECT ALL
    ========================================================= */

    function bindSelectAll()
    {

        const selectAll =
            document.getElementById(
                'selectAllSubjects'
            );


        if (!selectAll) {
            return;
        }


        const checkboxes =
            document.querySelectorAll(
                '.subject-checkbox'
            );


        /*
        | Set initial state
        */

        const total =
            checkboxes.length;


        const checked =
            document.querySelectorAll(
                '.subject-checkbox:checked'
            ).length;


        selectAll.checked =
            total > 0 &&
            total === checked;


        /*
        | Select / unselect all
        */

        selectAll.onchange =
            function() {

                document
                    .querySelectorAll(
                        '.subject-checkbox'
                    )
                    .forEach(function(chk) {

                        chk.checked =
                            selectAll.checked;

                    });

            };


        /*
        | Individual checkbox
        */

        checkboxes.forEach(
            function(chk) {

                chk.onchange =
                    function() {

                        const total =
                            document.querySelectorAll(
                                '.subject-checkbox'
                            ).length;


                        const checked =
                            document.querySelectorAll(
                                '.subject-checkbox:checked'
                            ).length;


                        selectAll.checked =
                            total > 0 &&
                            total === checked;

                    };

            }
        );

    }


    /* =========================================================
       LOAD SUBJECTS WHEN STANDARD CHANGES
    ========================================================= */

    standard.addEventListener(
        'change',
        function() {


            const standardId =
                this.value;


            /*
            | Update Exam Name
            */

            buildExamName();


            if (!standardId) {

                tableBody.innerHTML = `
                    <tr>
                        <td colspan="4"
                            class="border p-3 text-center">

                            Select Standard First

                        </td>
                    </tr>
                `;

                return;

            }


            /*
            | Load Standard Wise Subjects
            */

            fetch(
                "{{ url('/exam-masters/load-subjects') }}/"
                + standardId
            )

            .then(function(response) {

                if (!response.ok) {

                    throw new Error(
                        'Unable to load subjects'
                    );

                }

                return response.json();

            })

            .then(function(subjects) {


                let html = '';


                if (!subjects.length) {

                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="4"
                                class="border p-3 text-center">

                                No subjects found for this standard.

                            </td>
                        </tr>
                    `;

                    bindSelectAll();

                    return;

                }


                subjects.forEach(
                    function(subject, index) {


                        /*
                        | New standard = default values
                        */

                        const maxMarks =
                            40;


                        const passingMarks =
                            Math.ceil(
                                maxMarks * 0.40
                            );


                        html += `

                        <tr>

                            {{-- CHECKBOX --}}

                            <td class="border p-2 text-center">

                                <input type="checkbox"
                                       class="subject-checkbox"
                                       name="subjects[${index}][selected]"
                                       value="1"
                                       checked>

                            </td>


                            {{-- SUBJECT --}}

                            <td class="border p-2">

                                ${subject.subject_name}

                                <input type="hidden"
                                       name="subjects[${index}][subject_id]"
                                       value="${subject.id}">

                                <input type="hidden"
                                       name="subjects[${index}][display_order]"
                                       value="${index + 1}">

                            </td>


                            {{-- MAX MARKS --}}

                            <td class="border p-2">

                                <select
                                    name="subjects[${index}][max_marks]"
                                    class="max-mark w-full border rounded p-1">

                                    <option value="20">
                                        20
                                    </option>

                                    <option value="25">
                                        25
                                    </option>

                                    <option value="40"
                                            selected>
                                        40
                                    </option>

                                    <option value="50">
                                        50
                                    </option>

                                    <option value="80">
                                        80
                                    </option>

                                    <option value="100">
                                        100
                                    </option>

                                </select>

                            </td>


                            {{-- PASSING MARKS --}}

                            <td class="border p-2">

                                <input type="number"
                                       readonly
                                       name="subjects[${index}][passing_marks]"
                                       value="${passingMarks}"
                                       class="passing-mark w-full border rounded p-1 bg-gray-100">

                            </td>

                        </tr>

                        `;

                    }
                );


                tableBody.innerHTML =
                    html;


                bindPassingMarks();

                bindSelectAll();

            })

            .catch(function(error) {

                console.error(
                    'Subject loading error:',
                    error
                );


                tableBody.innerHTML = `
                    <tr>
                        <td colspan="4"
                            class="border p-3 text-center text-red-600">

                            Unable to load subjects.

                        </td>
                    </tr>
                `;

            });

        }
    );


    /* =========================================================
       EXAM TYPE CHANGE
    ========================================================= */

    examType.addEventListener(
        'change',
        function() {

            buildExamName();

        }
    );


    /* =========================================================
       INITIAL PAGE LOAD
    ========================================================= */

    setInitialExamType();

    buildExamName();

    bindPassingMarks();

    bindSelectAll();

});

</script>


</x-app-layout>