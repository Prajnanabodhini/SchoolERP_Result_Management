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
    color: #2563eb;
    font-weight: 600;
}

.info-note {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    color: #1e40af;
    padding: 10px 12px;
    border-radius: 6px;
    font-size: 13px !important;
    line-height: 1.5;
}

</style>


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

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        {{-- =====================================================
             ERROR MESSAGE
        ====================================================== --}}

        @if(session('error'))

            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                {{ session('error') }}
            </div>

        @endif


        {{-- =====================================================
             SUCCESS MESSAGE
        ====================================================== --}}

        @if(session('success'))

            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                {{ session('success') }}
            </div>

        @endif


        {{-- =====================================================
             FORM
        ====================================================== --}}

        <form
            method="POST"
            action="{{ route('exam-masters.store') }}"
            id="examForm"
        >

            @csrf


            {{-- =================================================
                 STANDARD + EXAM TYPE
            ================================================== --}}

            <div class="grid grid-cols-2 gap-4 mb-5">


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
                                {{ (string)old('standard_id') === (string)$standard->id ? 'selected' : '' }}
                            >
                                {{ $standard->standard_name }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- EXAM TYPE --}}

                <div>

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

                        <option
                            value="UNIT TEST 1"
                            {{ old('exam_type') === 'UNIT TEST 1' ? 'selected' : '' }}
                        >
                            Unit Test 1
                        </option>

                        <option
                            value="UNIT TEST 2"
                            {{ old('exam_type') === 'UNIT TEST 2' ? 'selected' : '' }}
                        >
                            Unit Test 2
                        </option>

                        <option
                            value="UNIT TEST 3"
                            {{ old('exam_type') === 'UNIT TEST 3' ? 'selected' : '' }}
                        >
                            Unit Test 3
                        </option>

                        <option
                            value="UNIT TEST 4"
                            {{ old('exam_type') === 'UNIT TEST 4' ? 'selected' : '' }}
                        >
                            Unit Test 4
                        </option>

                        <option
                            value="TERM 1"
                            {{ old('exam_type') === 'TERM 1' ? 'selected' : '' }}
                        >
                            Term 1
                        </option>

                        <option
                            value="TERM 2"
                            {{ old('exam_type') === 'TERM 2' ? 'selected' : '' }}
                        >
                            Term 2
                        </option>

                        <option
                            value="ANNUAL"
                            {{ old('exam_type') === 'ANNUAL' ? 'selected' : '' }}
                        >
                            Annual
                        </option>

                    </select>

                </div>

            </div>


            {{-- =================================================
                 EXAM NAME
            ================================================== --}}

            <input
                type="hidden"
                name="exam_name"
                id="exam_name"
                value="{{ old('exam_name') }}"
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
                    value="{{ old('exam_name') }}"
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

                <div class="flex justify-between items-center mb-3">

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
                    style="display:none;"
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

                            <tr>

                                <td
                                    colspan="4"
                                    class="border p-3 text-center text-gray-500"
                                >
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
                        $nextDisplayOrder
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
                        {{ old('is_active', true) ? 'checked' : '' }}
                    >

                    <span>
                        Active
                    </span>

                </label>

            </div>


            {{-- =================================================
                 INFORMATION
            ================================================== --}}

            <div class="info-note mb-5">

                <strong>Note:</strong>

                All active subjects mapped to the selected Standard
                are automatically included in this Exam.

                <br>

                Subject selection is controlled by the
                <strong>Standard Wise Subject Master</strong>.

                <br>

                The same Subject Master can be used by multiple
                Standards.

                <br><br>

                You only need to configure the
                <strong>Max Marks</strong> for each subject.

                Passing Marks are calculated automatically.

            </div>


            {{-- =================================================
                 BUTTONS
            ================================================== --}}

            <div class="flex justify-between items-center mt-6">

                <div></div>

                <div class="flex gap-2">

                    <button
                        type="submit"
                        class="erp-btn erp-btn-save"
                        id="saveExamButton"
                    >
                        Save
                    </button>


                    <a
                        href="{{ route('exam-masters.index') }}"
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


        const form =
            document.getElementById(
                'examForm'
            );


        const saveButton =
            document.getElementById(
                'saveExamButton'
            );


        /*
        |--------------------------------------------------------------------------
        | OLD VALUES AFTER VALIDATION FAILURE
        |--------------------------------------------------------------------------
        */

        const oldSubjects =
            @json(old('subjects', []));


        /*
        |--------------------------------------------------------------------------
        | PASSING PERCENTAGE
        |--------------------------------------------------------------------------
        |
        | 9th  = 35%
        | 10th = 35%
        | Others = 40%
        |
        */

        function getPassingPercentage()
        {

            const standardId =
                parseInt(
                    standardDropdown.value || 0,
                    10
                );


            if (
                standardId === 9 ||
                standardId === 10
            ) {

                return 35;
            }


            return 40;
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE PERCENTAGE NOTE
        |--------------------------------------------------------------------------
        */

        function updatePercentageNote()
        {

            if (
                !standardDropdown.value
            ) {

                percentageNote.style.display =
                    'none';

                percentageNote.textContent =
                    '';

                return;
            }


            percentageNote.textContent =
                'Passing percentage: ' +
                getPassingPercentage() +
                '%';


            percentageNote.style.display =
                'block';
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


            const percentage =
                getPassingPercentage();


            return Math.ceil(
                max *
                percentage /
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


            const selectedOption =
                standardDropdown.options[
                    standardDropdown.selectedIndex
                ];


            const standardText =
                selectedOption &&
                selectedOption.value
                    ? selectedOption.text.trim()
                    : '';


            if (
                !type ||
                !standardDropdown.value
            ) {

                examName.value =
                    '';

                examNamePreview.value =
                    '';

                return;
            }


            const generatedName =
                type +
                ' - ' +
                standardText;


            examName.value =
                generatedName;


            examNamePreview.value =
                generatedName;
        }


        /*
        |--------------------------------------------------------------------------
        | GET OLD SUBJECT CONFIGURATION
        |--------------------------------------------------------------------------
        */

        function getOldSubject(
            subjectId
        )
        {

            if (
                !oldSubjects ||
                typeof oldSubjects !== 'object'
            ) {

                return null;
            }


            /*
            | Laravel sends subjects keyed by subjects.id.
            */

            if (
                oldSubjects[
                    subjectId
                ] !== undefined
            ) {

                return oldSubjects[
                    subjectId
                ];
            }


            /*
            | Backward compatibility for old indexed form.
            */

            if (
                Array.isArray(
                    oldSubjects
                )
            ) {

                for (
                    let i = 0;
                    i < oldSubjects.length;
                    i++
                ) {

                    if (
                        String(
                            oldSubjects[i].subject_id
                        ) === String(
                            subjectId
                        )
                    ) {

                        return oldSubjects[i];
                    }
                }

            }


            return null;
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
                            ${selected === value ? 'selected' : ''}
                        >
                            ${value}
                        </option>
                    `;

                }
            );


            /*
            |--------------------------------------------------------------------------
            | SUPPORT CUSTOM EXISTING VALUE
            |--------------------------------------------------------------------------
            */

            if (
                !values.includes(
                    selected
                )
                &&
                selected > 0
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
            standardId
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
                        'HTTP ' +
                        response.status
                    );
                }


                const subjects =
                    await response.json();


                renderSubjects(
                    subjects
                );

            } catch (
                error
            ) {

                console.error(
                    'Subject loading error:',
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
            subjects
        )
        {

            if (
                !Array.isArray(subjects)
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

                    /*
                    |--------------------------------------------------------------------------
                    | ACTUAL SUBJECT MASTER ID
                    |--------------------------------------------------------------------------
                    */

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


                    /*
                    |--------------------------------------------------------------------------
                    | OPTIONAL
                    |--------------------------------------------------------------------------
                    */

                    const isOptional =
                        Number(
                            subject.is_optional
                            ??
                            0
                        ) === 1;


                    /*
                    |--------------------------------------------------------------------------
                    | OLD CONFIGURATION
                    |--------------------------------------------------------------------------
                    */

                    const oldRow =
                        getOldSubject(
                            subjectId
                        );


                    let maxMarks =
                        40;


                    let displayOrder =
                        subject.sort_order
                        ??
                        (
                            index + 1
                        );


                    if (
                        oldRow
                    ) {

                        if (
                            oldRow.max_marks !==
                            undefined
                        ) {

                            maxMarks =
                                oldRow.max_marks;
                        }


                        if (
                            oldRow.display_order !==
                            undefined
                        ) {

                            displayOrder =
                                oldRow.display_order;
                        }
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | PASSING MARKS
                    |--------------------------------------------------------------------------
                    */

                    const passingMarks =
                        calculatePassingMarks(
                            maxMarks
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | TABLE ROW
                    |--------------------------------------------------------------------------
                    */

                    html += `

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


                                <!--
                                    ACTUAL SUBJECT MASTER ID
                                -->

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


                            {{-- MAX MARKS --}}

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


                            {{-- PASSING MARKS --}}

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


                            {{-- TYPE --}}

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


            bindPassingMarks();

            updatePercentageNote();
        }


        /*
        |--------------------------------------------------------------------------
        | BIND MAX MARK EVENTS
        |--------------------------------------------------------------------------
        */

        function bindPassingMarks()
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

                                const subjectId =
                                    this.dataset
                                        .subjectId;


                                const maxMarks =
                                    parseFloat(
                                        this.value
                                    )
                                    ||
                                    0;


                                const passingMarks =
                                    calculatePassingMarks(
                                        maxMarks
                                    );


                                const row =
                                    this.closest(
                                        'tr'
                                    );


                                /*
                                |--------------------------------------------------------------------------
                                | DISPLAY PASSING
                                |--------------------------------------------------------------------------
                                */

                                const passingInput =
                                    row.querySelector(
                                        '.passing-mark'
                                    );


                                if (
                                    passingInput
                                ) {

                                    passingInput.value =
                                        passingMarks;
                                }


                                /*
                                |--------------------------------------------------------------------------
                                | HIDDEN PASSING VALUE
                                |--------------------------------------------------------------------------
                                */

                                const hiddenInput =
                                    row.querySelector(
                                        '.passing-mark-hidden'
                                    );


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
        | HTML ESCAPE
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
        | STANDARD CHANGE
        |--------------------------------------------------------------------------
        */

        standardDropdown.addEventListener(
            'change',
            function () {

                buildExamName();

                updatePercentageNote();

                loadSubjects(
                    this.value
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

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | SUBJECTS
                |--------------------------------------------------------------------------
                */

                const subjectRows =
                    tableBody.querySelectorAll(
                        '.max-mark'
                    );


                if (
                    subjectRows.length === 0
                ) {

                    event.preventDefault();

                    alert(
                        'No active subjects are mapped to the selected Standard.'
                    );

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | GENERATE NAME
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
                | UPDATE HIDDEN PASSING MARKS
                |--------------------------------------------------------------------------
                */

                tableBody
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


                            const passing =
                                calculatePassingMarks(
                                    select.value
                                );


                            const hidden =
                                row.querySelector(
                                    '.passing-mark-hidden'
                                );


                            const display =
                                row.querySelector(
                                    '.passing-mark'
                                );


                            if (
                                hidden
                            ) {

                                hidden.value =
                                    passing;
                            }


                            if (
                                display
                            ) {

                                display.value =
                                    passing;
                            }

                        }
                    );


                /*
                |--------------------------------------------------------------------------
                | PREVENT DOUBLE SUBMISSION
                |--------------------------------------------------------------------------
                */

                saveButton.disabled =
                    true;

                saveButton.innerText =
                    'Saving...';

            }
        );


        /*
        |--------------------------------------------------------------------------
        | INITIAL LOAD
        |--------------------------------------------------------------------------
        */

        buildExamName();

        updatePercentageNote();


        if (
            standardDropdown.value
        ) {

            loadSubjects(
                standardDropdown.value
            );
        }

    });

</script>

</x-app-layout>