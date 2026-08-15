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

</style>


<div class="exam-form"
     style="max-width:1000px;margin:auto;padding:15px;">

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

        <h2 class="text-center text-green-600 mb-4">
            Add Exam
        </h2>


        {{-- =====================================================
             VALIDATION ERRORS
        ====================================================== --}}

        @if ($errors->any())

            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">

                <ul class="list-disc ml-5">

                    @foreach ($errors->all() as $error)

                        <li>{{ $error }}</li>

                    @endforeach

                </ul>

            </div>

        @endif


        {{-- =====================================================
             FORM
        ====================================================== --}}

        <form method="POST"
              action="{{ route('exam-masters.store') }}">

            @csrf


            {{-- =================================================
                 STANDARD + EXAM TYPE
            ================================================== --}}

            <div class="grid grid-cols-2 gap-4 mb-5">


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
                                {{ old('standard_id') == $standard->id ? 'selected' : '' }}>

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

                        <option value="UNIT TEST 1"
                            {{ old('exam_type') == 'UNIT TEST 1' ? 'selected' : '' }}>
                            Unit Test 1
                        </option>

                        <option value="UNIT TEST 2"
                            {{ old('exam_type') == 'UNIT TEST 2' ? 'selected' : '' }}>
                            Unit Test 2
                        </option>

                        <option value="UNIT TEST 3"
                            {{ old('exam_type') == 'UNIT TEST 3' ? 'selected' : '' }}>
                            Unit Test 3
                        </option>

                        <option value="UNIT TEST 4"
                            {{ old('exam_type') == 'UNIT TEST 4' ? 'selected' : '' }}>
                            Unit Test 4
                        </option>

                        <option value="TERM 1"
                            {{ old('exam_type') == 'TERM 1' ? 'selected' : '' }}>
                            Term 1
                        </option>

                        <option value="TERM 2"
                            {{ old('exam_type') == 'TERM 2' ? 'selected' : '' }}>
                            Term 2
                        </option>

                        <option value="ANNUAL"
                            {{ old('exam_type') == 'ANNUAL' ? 'selected' : '' }}>
                            Annual
                        </option>

                    </select>

                </div>

            </div>


            {{-- =================================================
                 GENERATED EXAM NAME
            ================================================== --}}

            <input type="hidden"
                   name="exam_name"
                   id="exam_name"
                   value="{{ old('exam_name') }}">


            <div class="mb-5">

                <label class="block font-semibold mb-2">
                    Exam Name
                </label>

                <input type="text"
                       id="exam_name_preview"
                       value="{{ old('exam_name') }}"
                       readonly
                       class="w-full border rounded p-2 bg-gray-100">

            </div>


            {{-- =================================================
                 SUBJECT MARKS CONFIGURATION
            ================================================== --}}

            <div class="border rounded section-box mb-6 bg-gray-50">

                <div class="flex justify-between items-center mb-3">

                    <h3 class="font-bold">
                        Subject Wise Marks Configuration
                    </h3>

                    <span id="subjectLoading"
                          class="text-blue-600"
                          style="display:none;">

                        Loading subjects...

                    </span>

                </div>


                <div class="overflow-x-auto">

                    <table class="w-full border bg-white">

                        <thead>

                            <tr class="bg-blue-100">

                                <th class="border p-2 text-center"
                                    style="width:60px;">

                                    <label style="
                                        display:flex;
                                        align-items:center;
                                        justify-content:center;
                                        gap:5px;
                                    ">

                                        <input type="checkbox"
                                               id="selectAllSubjects"
                                               checked>

                                        <span>All</span>

                                    </label>

                                </th>


                                <th class="border p-2">
                                    Subject
                                </th>


                                <th class="border p-2"
                                    style="width:150px;">

                                    Max Marks

                                </th>


                                <th class="border p-2"
                                    style="width:150px;">

                                    Passing Marks

                                </th>

                            </tr>

                        </thead>


                        <tbody id="subjectTableBody">

                            <tr>

                                <td colspan="4"
                                    class="border p-3 text-center text-gray-500">

                                    Select Standard First

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>


            {{-- =================================================
                 DISPLAY ORDER
            ================================================== --}}

            <div class="mb-5">

                <label class="block font-semibold mb-2">
                    Display Order
                </label>

                <input type="number"
                       name="display_order"
                       value="{{ old('display_order', $nextDisplayOrder) }}"
                       readonly
                       class="w-full border rounded p-2 bg-gray-100">

            </div>


            {{-- =================================================
                 ACTIVE + BUTTONS
            ================================================== --}}

            <div class="flex justify-between items-center mt-6">


                <label class="flex items-center gap-2">

                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           {{ old('is_active', true) ? 'checked' : '' }}>

                    Active

                </label>


                <div class="flex gap-2">

                    <button type="submit"
                            class="erp-btn erp-btn-save">

                        Save

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


{{-- ============================================================
     JAVASCRIPT
============================================================= --}}

<script>

document.addEventListener('DOMContentLoaded', function () {

    const standardDropdown =
        document.getElementById('standard_id');

    const examType =
        document.getElementById('exam_type');

    const examName =
        document.getElementById('exam_name');

    const examNamePreview =
        document.getElementById('exam_name_preview');

    const tableBody =
        document.getElementById('subjectTableBody');

    const selectAll =
        document.getElementById('selectAllSubjects');

    const loading =
        document.getElementById('subjectLoading');


    /* =========================================================
       GENERATE EXAM NAME
    ========================================================== */

    function buildExamName()
    {

        const exam =
            examType.value;

        const selectedOption =
            standardDropdown.options[
                standardDropdown.selectedIndex
            ];

        const standardText =
            selectedOption
                ? selectedOption.text.trim()
                : '';


        if (!exam || !standardDropdown.value)
        {

            examName.value = '';

            examNamePreview.value = '';

            return;
        }


        const generatedName =
            exam + ' - ' + standardText;


        examName.value =
            generatedName;

        examNamePreview.value =
            generatedName;
    }


    examType.addEventListener(
        'change',
        buildExamName
    );


    standardDropdown.addEventListener(
        'change',
        function () {

            buildExamName();

            loadSubjects(this.value);

        }
    );


    /* =========================================================
       LOAD SUBJECTS
    ========================================================== */

    function loadSubjects(standardId)
    {

        if (!standardId)
        {

            tableBody.innerHTML = `
                <tr>
                    <td colspan="4"
                        class="border p-3 text-center text-gray-500">

                        Select Standard First

                    </td>
                </tr>
            `;

            return;
        }


        loading.style.display = 'inline';


        tableBody.innerHTML = `
            <tr>
                <td colspan="4"
                    class="border p-3 text-center">

                    Loading subjects...

                </td>
            </tr>
        `;


        fetch(
            "{{ url('/exam-masters/load-subjects') }}/"
            + encodeURIComponent(standardId),
            {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }
        )
        .then(function (response) {

            if (!response.ok)
            {
                throw new Error(
                    'Failed to load subjects'
                );
            }

            return response.json();

        })
        .then(function (subjects) {

            loading.style.display = 'none';

            renderSubjects(subjects);

        })
        .catch(function (error) {

            console.error(error);

            loading.style.display = 'none';

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


    /* =========================================================
       RENDER SUBJECTS
    ========================================================== */

    function renderSubjects(subjects)
    {

        if (!Array.isArray(subjects)
            || subjects.length === 0)
        {

            tableBody.innerHTML = `
                <tr>
                    <td colspan="4"
                        class="border p-3 text-center text-red-600">

                        No subjects mapped to this standard.

                    </td>
                </tr>
            `;

            selectAll.checked = false;

            return;
        }


        let html = '';


        subjects.forEach(function (subject, index) {


            /*
             * IMPORTANT:
             * subject.id must be the SUBJECT MASTER ID.
             */

            const subjectId =
                subject.subject_id ?? subject.id;


            const subjectName =
                subject.subject_name ?? '';


            const subjectCode =
                subject.subject_code ?? '';


            html += `

                <tr>

                    <td class="border p-2 text-center">

                        <input
                            type="checkbox"
                            class="subject-checkbox"
                            name="subjects[${index}][selected]"
                            value="1"
                            checked
                        >

                    </td>


                    <td class="border p-2">

                        <div class="font-semibold">

                            ${escapeHtml(subjectName)}

                        </div>

                        ${
                            subjectCode
                            ? `
                                <div class="text-gray-500"
                                     style="font-size:11px !important;">

                                    Code:
                                    ${escapeHtml(subjectCode)}

                                </div>
                              `
                            : ''
                        }


                        <input
                            type="hidden"
                            name="subjects[${index}][subject_id]"
                            value="${subjectId}"
                        >


                        <input
                            type="hidden"
                            name="subjects[${index}][display_order]"
                            value="${index + 1}"
                        >

                    </td>


                    <td class="border p-2">

                        <select
                            name="subjects[${index}][max_marks]"
                            class="max-mark w-full border rounded p-1"
                        >

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


                    <td class="border p-2">

                        <input
                            type="number"
                            readonly
                            name="subjects[${index}][passing_marks]"
                            value="16"
                            class="passing-mark w-full border rounded p-1 bg-gray-100"
                        >

                    </td>

                </tr>

            `;
        });


        tableBody.innerHTML =
            html;


        bindPassingMarks();

        bindSubjectCheckboxes();

        updateSelectAll();

    }


    /* =========================================================
       PASSING MARK CALCULATION
    ========================================================== */

    function bindPassingMarks()
    {

        document
            .querySelectorAll('.max-mark')
            .forEach(function (select) {

                select.addEventListener(
                    'change',
                    function () {

                        const maxMarks =
                            parseFloat(this.value) || 0;


                        const passingMarks =
                            Math.ceil(
                                maxMarks * 0.40
                            );


                        const row =
                            this.closest('tr');


                        const passingInput =
                            row.querySelector(
                                '.passing-mark'
                            );


                        if (passingInput)
                        {
                            passingInput.value =
                                passingMarks;
                        }

                    }
                );

            });

    }


    /* =========================================================
       SELECT ALL
    ========================================================== */

    selectAll.addEventListener(
        'change',
        function () {

            document
                .querySelectorAll('.subject-checkbox')
                .forEach(function (checkbox) {

                    checkbox.checked =
                        selectAll.checked;

                });

        }
    );


    /* =========================================================
       INDIVIDUAL CHECKBOXES
    ========================================================== */

    function bindSubjectCheckboxes()
    {

        document
            .querySelectorAll('.subject-checkbox')
            .forEach(function (checkbox) {

                checkbox.addEventListener(
                    'change',
                    updateSelectAll
                );

            });

    }


    /* =========================================================
       UPDATE SELECT ALL STATE
    ========================================================== */

    function updateSelectAll()
    {

        const checkboxes =
            document.querySelectorAll(
                '.subject-checkbox'
            );


        const checked =
            document.querySelectorAll(
                '.subject-checkbox:checked'
            );


        if (checkboxes.length === 0)
        {

            selectAll.checked = false;

            selectAll.indeterminate = false;

            return;
        }


        selectAll.checked =
            checkboxes.length === checked.length;


        selectAll.indeterminate =
            checked.length > 0
            &&
            checked.length < checkboxes.length;

    }


    /* =========================================================
       HTML ESCAPE
    ========================================================== */

    function escapeHtml(value)
    {

        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

    }


    /* =========================================================
       INITIAL LOAD
    ========================================================== */

    buildExamName();


    /*
     * If validation failed and standard was retained,
     * automatically load its subjects again.
     */

    if (standardDropdown.value)
    {
        loadSubjects(
            standardDropdown.value
        );
    }

});

</script>

</x-app-layout>