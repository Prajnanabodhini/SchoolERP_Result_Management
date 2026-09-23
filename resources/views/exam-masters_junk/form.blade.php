@php


$mode =
    $mode
    ?? 'create';

$isEdit =
    $mode === 'edit';

$examMaster =
    $examMaster
    ?? null;

$academicYears =
    $academicYears
    ?? collect();

$standards =
    $standards
    ?? collect();

$subjects =
    $subjects
    ?? collect();

$initialStructure =
    $initialStructure
    ?? [
        'file' => null,
        'source_hash' => null,
        'standards' => [],
        'exam_types' => [],
    ];

$selectedStandardId =
    old(
        'standard_id',
        $examMaster->standard_id
        ?? ''
    );

$selectedAcademicYearId =
    old(
        'academic_year_id',
        $examMaster->academic_year_id
        ?? ''
    );

$selectedExamType =
    old(
        'exam_type',
        $examType
        ?? ''
    );

$selectedExamName =
    old(
        'exam_name',
        $examMaster->exam_name
        ?? ''
    );

$displayOrder =
    old(
        'display_order',
        $examMaster->display_order
        ?? ($nextDisplayOrder ?? 0)
    );


@endphp

<style>

.exam-form,
.exam-form * {
    font-family: Arial, sans-serif !important;
    box-sizing: border-box;
}

.exam-form {
    font-size: 12px;
}

.exam-form label {
    font-weight: 600;
    margin-bottom: 5px;
    display: block;
}

.exam-card {
    max-width: 1180px;
    margin: 20px auto;
    background: #ffffff;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    box-shadow: 0 3px 10px rgba(0,0,0,.12);
    padding: 18px;
}

.exam-title {
    background: #1E3A8A;
    color: #ffffff;
    padding: 10px 14px;
    border-radius: 7px;
    font-size: 17px;
    font-weight: 700;
    margin-bottom: 15px;
}

.exam-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

.exam-field {
    margin-bottom: 10px;
}

.exam-field input,
.exam-field select {
    width: 100%;
    height: 34px;
    border: 1px solid #9ca3af;
    border-radius: 5px;
    padding: 5px 8px;
    background: #ffffff;
}

.exam-field input[readonly] {
    background: #f3f4f6;
}

.excel-note {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    color: #1e40af;
    padding: 9px 11px;
    border-radius: 6px;
    margin-bottom: 12px;
    line-height: 1.5;
}

.warning-note {
    background: #fff7ed;
    border: 1px solid #fed7aa;
    color: #9a3412;
    padding: 9px 11px;
    border-radius: 6px;
    margin-top: 10px;
    line-height: 1.5;
}

.structure-box {
    border: 1px solid #d1d5db;
    border-radius: 7px;
    margin-top: 15px;
    overflow: hidden;
}

.structure-head {
    background: #f3f4f6;
    padding: 9px 12px;
    border-bottom: 1px solid #d1d5db;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.structure-head strong {
    font-size: 14px;
}

.loading-text {
    color: #1d4ed8;
    display: none;
}

.subject-wrapper {
    overflow-x: auto;
}

.subject-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 1050px;
}

.subject-table th,
.subject-table td {
    border: 1px solid #d1d5db;
    padding: 6px 7px;
    vertical-align: middle;
}

.subject-table th {
    background: #dbeafe;
    color: #111827;
    text-align: center;
    white-space: nowrap;
}

.subject-table td.number {
    text-align: center;
}

.subject-name {
    font-weight: 600;
}

.subject-code {
    margin-top: 2px;
    color: #6b7280;
    font-size: 10px;
}

.type-compulsory {
    color: #166534;
    font-weight: 700;
}

.type-optional {
    color: #92400e;
    font-weight: 700;
}

.component-on {
    color: #166534;
    font-weight: 700;
}

.component-off {
    color: #9ca3af;
}

.excel-source {
    font-size: 10px;
    color: #6b7280;
}

.bottom-bar {
    margin-top: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
}

.erp-btn {
    display: inline-block;
    border: none;
    border-radius: 5px;
    padding: 7px 14px;
    text-decoration: none;
    cursor: pointer;
    font-size: 12px;
    font-weight: 700;
}

.erp-btn-save {
    background: #166534;
    color: white;
}

.erp-btn-cancel {
    background: #6b7280;
    color: white;
}

@media (max-width: 900px) {

    .exam-grid {
        grid-template-columns: 1fr;
    }

}

</style>

<div class="exam-form">


<div class="exam-card">

    <div class="exam-title">
        {{ $isEdit ? 'Edit Exam Master' : 'Create Exam Master' }}
    </div>


    {{-- ==========================================================
         MESSAGES
    =========================================================== --}}

    @if($errors->any())

        <div class="warning-note">

            <strong>Please correct the following:</strong>

            <ul style="margin:6px 0 0 18px;">

                @foreach($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    @if(session('error'))

        <div class="warning-note">
            {{ session('error') }}
        </div>

    @endif


    @if(session('success'))

        <div
            style="
                background:#f0fdf4;
                border:1px solid #bbf7d0;
                color:#166534;
                padding:9px 11px;
                border-radius:6px;
                margin-bottom:12px;
            "
        >
            {{ session('success') }}
        </div>

    @endif


    {{-- ==========================================================
         EXCEL SOURCE
    =========================================================== --}}

    <div class="excel-note">

        <strong>Academic Exam Structure:</strong>

        @if(!empty($initialStructure['file']))

            {{ $initialStructure['file'] }}

        @else

            No Excel workbook detected.

        @endif

        <br>

        Standard and Exam Type determine the exact marks structure.

        <br>

        The component values displayed below come from the Excel
        structure and are not recalculated by the form.

    </div>


    @if(!empty($initialStructure['error']))

        <div class="warning-note">

            <strong>Excel Reader Error:</strong>

            {{ $initialStructure['error'] }}

            <br><br>

            Check this directory:

            <strong>
                storage/Academic Exam Structure
            </strong>

        </div>

    @endif


    {{-- ==========================================================
         FORM
    =========================================================== --}}

    <form
        method="POST"
        action="{{
            $isEdit
                ? route('exam-masters.update', $examMaster->id)
                : route('exam-masters.store')
        }}"
        id="examMasterForm"
    >

        @csrf

        @if($isEdit)
            @method('PUT')
        @endif


        {{-- ======================================================
             BASIC INFORMATION
        ======================================================= --}}

        <div class="exam-grid">

            {{-- ACADEMIC YEAR --}}

            <div class="exam-field">

                <label for="academic_year_id">
                    Academic Year
                </label>

                <select
                    name="academic_year_id"
                    id="academic_year_id"
                    required
                >

                    <option value="">
                        Select Academic Year
                    </option>

                    @foreach(
                        $academicYears
                        as $academicYear
                    )

                        <option
                            value="{{ $academicYear->id }}"
                            {{
                                (string)$selectedAcademicYearId
                                ===
                                (string)$academicYear->id
                                    ? 'selected'
                                    : ''
                            }}
                        >

                            {{
                                $academicYear->year_name
                                ?? $academicYear->name
                            }}

                        </option>

                    @endforeach

                </select>

            </div>


            {{-- STANDARD --}}

            <div class="exam-field">

                <label for="standard_id">
                    Standard
                </label>

                <select
                    name="standard_id"
                    id="standard_id"
                    required
                >

                    <option value="">
                        Select Standard
                    </option>

                    @foreach(
                        $standards
                        as $standard
                    )

                        <option
                            value="{{ $standard->id }}"
                            {{
                                (string)$selectedStandardId
                                ===
                                (string)$standard->id
                                    ? 'selected'
                                    : ''
                            }}
                        >

                            {{ $standard->standard_name }}

                        </option>

                    @endforeach

                </select>

            </div>


            {{-- EXAM TYPE --}}

            <div class="exam-field">

                <label for="exam_type">
                    Exam Type
                </label>

                <select
                    name="exam_type"
                    id="exam_type"
                    required
                >

                    <option value="">
                        Select Standard First
                    </option>

                    @if($selectedExamType !== '')

                        <option
                            value="{{ $selectedExamType }}"
                            selected
                        >
                            {{ $selectedExamType }}
                        </option>

                    @endif

                </select>

            </div>

        </div>


        {{-- ======================================================
             EXAM NAME
        ======================================================= --}}

        <div class="exam-grid">

            <div class="exam-field">

                <label for="exam_name_preview">
                    Exam Name
                </label>

                <input
                    type="text"
                    id="exam_name_preview"
                    value="{{ $selectedExamName }}"
                    readonly
                >

                <input
                    type="hidden"
                    name="exam_name"
                    id="exam_name"
                    value="{{ $selectedExamName }}"
                >

            </div>


            <div class="exam-field">

                <label for="display_order">
                    Display Order
                </label>

                <input
                    type="number"
                    min="0"
                    name="display_order"
                    id="display_order"
                    value="{{ $displayOrder }}"
                    readonly
                >

            </div>


            <div
                class="exam-field"
                style="
                    display:flex;
                    align-items:end;
                    padding-bottom:5px;
                "
            >

                <label
                    style="
                        display:flex;
                        gap:7px;
                        align-items:center;
                        margin:0;
                    "
                >

                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        style="
                            width:auto;
                            height:auto;
                        "
                        {{
                            old(
                                'is_active',
                                $examMaster->is_active ?? 1
                            )
                            ? 'checked'
                            : ''
                        }}
                    >

                    Active

                </label>

            </div>

        </div>


        {{-- ======================================================
             STRUCTURE
        ======================================================= --}}

        <div class="structure-box">

            <div class="structure-head">

                <strong>
                    Excel Exam Structure
                </strong>

                <span
                    id="subjectLoading"
                    class="loading-text"
                >
                    Loading...
                </span>

            </div>


            <div
                style="
                    padding:10px 12px;
                    background:#ffffff;
                    border-bottom:1px solid #e5e7eb;
                "
            >

                <span id="structureInfo">
                    Select Standard and Exam Type.
                </span>

            </div>


            <div class="subject-wrapper">

                <table class="subject-table">

                    <thead>

                        <tr>

                            <th style="width:40px;">
                                #
                            </th>

                            <th style="min-width:250px;">
                                Subject
                            </th>

                            <th>
                                Theory Max
                            </th>

                            <th>
                                Theory Pass
                            </th>

                            <th>
                                Oral Max
                            </th>

                            <th>
                                Oral Pass
                            </th>

                            <th>
                                Practical Max
                            </th>

                            <th>
                                Practical Pass
                            </th>

                            <th>
                                Total Max
                            </th>

                            <th>
                                Total Pass
                            </th>

                            <th>
                                Type
                            </th>

                        </tr>

                    </thead>


                    <tbody id="subjectTableBody">

                        @if($subjects->isNotEmpty())

                            @foreach(
                                $subjects
                                as $index => $subject
                            )

                                <tr>

                                    <td class="number">
                                        {{ $index + 1 }}
                                    </td>

                                    <td>

                                        <div class="subject-name">

                                            {{ $subject['subject_name'] }}

                                        </div>

                                        @if(
                                            !empty(
                                                $subject['subject_code']
                                            )
                                        )

                                            <div class="subject-code">

                                                Code:
                                                {{ $subject['subject_code'] }}

                                            </div>

                                        @endif


                                        <input
                                            type="hidden"
                                            name="subjects[{{ $subject['subject_id'] }}][subject_id]"
                                            value="{{ $subject['subject_id'] }}"
                                        >

                                        <input
                                            type="hidden"
                                            name="subjects[{{ $subject['subject_id'] }}][display_order]"
                                            value="{{ $subject['sort_order'] }}"
                                        >

                                        <input
                                            type="hidden"
                                            name="subjects[{{ $subject['subject_id'] }}][selected]"
                                            value="1"
                                        >

                                    </td>


                                    <td class="number">

                                        {{
                                            $subject['theory_max_marks']
                                            > 0
                                                ? rtrim(
                                                    rtrim(
                                                        number_format(
                                                            $subject['theory_max_marks'],
                                                            2,
                                                            '.',
                                                            ''
                                                        ),
                                                        '0'
                                                    ),
                                                    '.'
                                                )
                                                : '—'
                                        }}

                                    </td>


                                    <td class="number">

                                        {{
                                            $subject['theory_passing_marks']
                                            > 0
                                                ? rtrim(
                                                    rtrim(
                                                        number_format(
                                                            $subject['theory_passing_marks'],
                                                            2,
                                                            '.',
                                                            ''
                                                        ),
                                                        '0'
                                                    ),
                                                    '.'
                                                )
                                                : '—'
                                        }}

                                    </td>


                                    <td class="number">

                                        {{
                                            $subject['oral_max_marks']
                                            > 0
                                                ? rtrim(
                                                    rtrim(
                                                        number_format(
                                                            $subject['oral_max_marks'],
                                                            2,
                                                            '.',
                                                            ''
                                                        ),
                                                        '0'
                                                    ),
                                                    '.'
                                                )
                                                : '—'
                                        }}

                                    </td>


                                    <td class="number">

                                        {{
                                            $subject['oral_passing_marks']
                                            > 0
                                                ? rtrim(
                                                    rtrim(
                                                        number_format(
                                                            $subject['oral_passing_marks'],
                                                            2,
                                                            '.',
                                                            ''
                                                        ),
                                                        '0'
                                                    ),
                                                    '.'
                                                )
                                                : '—'
                                        }}

                                    </td>


                                    <td class="number">

                                        {{
                                            $subject['practical_max_marks']
                                            > 0
                                                ? rtrim(
                                                    rtrim(
                                                        number_format(
                                                            $subject['practical_max_marks'],
                                                            2,
                                                            '.',
                                                            ''
                                                        ),
                                                        '0'
                                                    ),
                                                    '.'
                                                )
                                                : '—'
                                        }}

                                    </td>


                                    <td class="number">

                                        {{
                                            $subject['practical_passing_marks']
                                            > 0
                                                ? rtrim(
                                                    rtrim(
                                                        number_format(
                                                            $subject['practical_passing_marks'],
                                                            2,
                                                            '.',
                                                            ''
                                                        ),
                                                        '0'
                                                    ),
                                                    '.'
                                                )
                                                : '—'
                                        }}

                                    </td>


                                    <td class="number">

                                        <strong>

                                            {{
                                                rtrim(
                                                    rtrim(
                                                        number_format(
                                                            $subject['total_max_marks'],
                                                            2,
                                                            '.',
                                                            ''
                                                        ),
                                                        '0'
                                                    ),
                                                    '.'
                                                )
                                            }}

                                        </strong>

                                    </td>


                                    <td class="number">

                                        <strong>

                                            {{
                                                rtrim(
                                                    rtrim(
                                                        number_format(
                                                            $subject['total_passing_marks'],
                                                            2,
                                                            '.',
                                                            ''
                                                        ),
                                                        '0'
                                                    ),
                                                    '.'
                                                )
                                            }}

                                        </strong>

                                    </td>


                                    <td class="number">

                                        @if(
                                            (int)$subject['is_optional'] === 1
                                        )

                                            <span class="type-optional">
                                                Optional
                                            </span>

                                        @else

                                            <span class="type-compulsory">
                                                Compulsory
                                            </span>

                                        @endif

                                    </td>

                                </tr>

                            @endforeach

                        @else

                            <tr>

                                <td
                                    colspan="11"
                                    style="
                                        text-align:center;
                                        padding:20px;
                                        color:#6b7280;
                                    "
                                >

                                    Select Standard and Exam Type
                                    to load the Excel structure.

                                </td>

                            </tr>

                        @endif

                    </tbody>

                </table>

            </div>

        </div>


        {{-- ======================================================
             WARNING
        ======================================================= --}}

        <div class="warning-note">

            <strong>Important:</strong>

            The marks structure is read from the Academic Exam
            Structure Excel workbook.

            Theory, Oral, Practical, Total and Passing Marks are
            therefore not automatically converted to generic
            40/35/100 values.

            <br><br>

            This is important because different standards have
            different component structures.

        </div>


        {{-- ======================================================
             BUTTONS
        ======================================================= --}}

        <div class="bottom-bar">

            <div
                style="
                    color:#6b7280;
                    font-size:10px;
                "
            >

                Excel:
                {{ $initialStructure['file'] ?? '-' }}

            </div>


            <div
                style="
                    display:flex;
                    gap:7px;
                "
            >

                <button
                    type="submit"
                    class="erp-btn erp-btn-save"
                >

                    {{
                        $isEdit
                            ? 'Update Exam'
                            : 'Save Exam'
                    }}

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

        const standardSelect =
            document.getElementById(
                'standard_id'
            );

        const examTypeSelect =
            document.getElementById(
                'exam_type'
            );

        const examNameInput =
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

        const structureInfo =
            document.getElementById(
                'structureInfo'
            );


        /*
        |--------------------------------------------------------------------------
        | ESCAPE HTML
        |--------------------------------------------------------------------------
        */

        function escapeHtml(
            value
        ) {

            return String(
                value ?? ''
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


        /*
        |--------------------------------------------------------------------------
        | FORMAT NUMBER
        |--------------------------------------------------------------------------
        */

        function formatNumber(
            value
        ) {

            const number =
                Number(
                    value || 0
                );

            if (
                number <= 0
            ) {
                return '—';
            }

            if (
                Number.isInteger(
                    number
                )
            ) {
                return String(
                    number
                );
            }

            return number
                .toFixed(2)
                .replace(
                    /\.?0+$/,
                    ''
                );
        }


        /*
        |--------------------------------------------------------------------------
        | BUILD EXAM NAME
        |--------------------------------------------------------------------------
        */

        function updateExamName()
        {
            const option =
                standardSelect.options[
                    standardSelect.selectedIndex
                ];

            const standardText =
                option
                ? option.text.trim()
                : '';

            const examType =
                examTypeSelect.value;

            if (
                !standardText
                ||
                !examType
            ) {

                return;
            }

            const generated =
                examType
                + ' - '
                + standardText;

            examNameInput.value =
                generated;

            examNamePreview.value =
                generated;
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD STANDARD
        |--------------------------------------------------------------------------
        */

        async function loadStandardStructure()
        {

            const standardId =
                standardSelect.value;

            if (
                !standardId
            ) {

                examTypeSelect.innerHTML =
                    '<option value="">Select Standard First</option>';

                tableBody.innerHTML =
                    `
                    <tr>
                        <td
                            colspan="11"
                            style="
                                text-align:center;
                                padding:20px;
                                color:#6b7280;
                            "
                        >
                            Select Standard First
                        </td>
                    </tr>
                    `;

                structureInfo.textContent =
                    'Select Standard and Exam Type.';

                return;
            }


            loading.style.display =
                'inline';


            try {

                const response =
                    await fetch(
                        "{{
                            url('/exam-masters/load-subjects')
                        }}/"
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


                const data =
                    await response.json();


                if (
                    data.error
                ) {

                    throw new Error(
                        data.error
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | EXAM TYPES
                |--------------------------------------------------------------------------
                */

                examTypeSelect.innerHTML =
                    '<option value="">Select Exam Type</option>';


                (
                    data.exam_types
                    || []
                )
                .forEach(
                    function (
                        examType
                    ) {

                        const option =
                            document.createElement(
                                'option'
                            );

                        option.value =
                            examType;

                        option.textContent =
                            examType;

                        examTypeSelect.appendChild(
                            option
                        );
                    }
                );


                /*
                |--------------------------------------------------------------------------
                | EDIT: RESTORE CURRENT EXAM TYPE
                |--------------------------------------------------------------------------
                */

                const currentExamType =
                    @json($selectedExamType);


                if (
                    currentExamType
                ) {

                    const existing =
                        Array.from(
                            examTypeSelect.options
                        )
                        .find(
                            option =>
                                option.value
                                ===
                                currentExamType
                        );

                    if (
                        existing
                    ) {

                        examTypeSelect.value =
                            currentExamType;

                    } else {

                        const option =
                            document.createElement(
                                'option'
                            );

                        option.value =
                            currentExamType;

                        option.textContent =
                            currentExamType;

                        option.selected =
                            true;

                        examTypeSelect.appendChild(
                            option
                        );
                    }

                } else {

                    if (
                        examTypeSelect.options.length
                        > 1
                    ) {

                        examTypeSelect.selectedIndex =
                            1;
                    }
                }


                updateExamName();

                await loadExamTypeStructure();


            } catch (
                error
            ) {

                console.error(
                    error
                );

                examTypeSelect.innerHTML =
                    '<option value="">Unable to load Exam Types</option>';

                tableBody.innerHTML =
                    `
                    <tr>
                        <td
                            colspan="11"
                            style="
                                text-align:center;
                                padding:20px;
                                color:#dc2626;
                            "
                        >
                            ${escapeHtml(
                                error.message
                                ||
                                'Unable to load Excel structure.'
                            )}
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
        | LOAD SELECTED EXAM TYPE
        |--------------------------------------------------------------------------
        */

        async function loadExamTypeStructure()
        {

            const standardId =
                standardSelect.value;

            const examType =
                examTypeSelect.value;

            if (
                !standardId
                ||
                !examType
            ) {

                return;
            }


            loading.style.display =
                'inline';


            structureInfo.textContent =
                'Loading Excel structure...';


            try {

                const url =
                    "{{
                        url('/exam-masters/load-subjects')
                    }}/"
                    +
                    encodeURIComponent(
                        standardId
                    )
                    +
                    '?exam_type='
                    +
                    encodeURIComponent(
                        examType
                    );


                const response =
                    await fetch(
                        url,
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


                const data =
                    await response.json();


                if (
                    data.error
                ) {

                    throw new Error(
                        data.error
                    );
                }


                renderSubjects(
                    data.subjects
                    || []
                );


                structureInfo.textContent =
                    'Sheet: '
                    +
                    (
                        data.sheet
                        || '-'
                    )
                    +
                    ' | Exam Type: '
                    +
                    examType
                    +
                    ' | Passing: '
                    +
                    (
                        data.passing_percentage
                        ?? '-'
                    )
                    +
                    '%';


                updateExamName();


            } catch (
                error
            ) {

                console.error(
                    error
                );

                tableBody.innerHTML =
                    `
                    <tr>
                        <td
                            colspan="11"
                            style="
                                text-align:center;
                                padding:20px;
                                color:#dc2626;
                            "
                        >
                            ${escapeHtml(
                                error.message
                                ||
                                'Unable to load Excel structure.'
                            )}
                        </td>
                    </tr>
                    `;

                structureInfo.textContent =
                    'Excel structure could not be loaded.';

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
        ) {

            if (
                !Array.isArray(
                    subjects
                )
                ||
                subjects.length === 0
            ) {

                tableBody.innerHTML =
                    `
                    <tr>
                        <td
                            colspan="11"
                            style="
                                text-align:center;
                                padding:20px;
                                color:#dc2626;
                            "
                        >
                            No active subjects with Excel structure were found.
                        </td>
                    </tr>
                    `;

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
                        Number(
                            subject.subject_id
                            || 0
                        );

                    const isOptional =
                        Number(
                            subject.is_optional
                            || 0
                        ) === 1;


                    html +=
                        `
                        <tr>

                            <td class="number">
                                ${index + 1}
                            </td>

                            <td>

                                <div
                                    class="subject-name"
                                >
                                    ${escapeHtml(
                                        subject.subject_name
                                    )}
                                </div>

                                ${
                                    subject.subject_code
                                    ?
                                    `
                                    <div
                                        class="subject-code"
                                    >
                                        Code:
                                        ${escapeHtml(
                                            subject.subject_code
                                        )}
                                    </div>
                                    `
                                    :
                                    ''
                                }

                                <input
                                    type="hidden"
                                    name="subjects[${subjectId}][subject_id]"
                                    value="${subjectId}"
                                >

                                <input
                                    type="hidden"
                                    name="subjects[${subjectId}][display_order]"
                                    value="${Number(
                                        subject.sort_order
                                        || index + 1
                                    )}"
                                >

                                <input
                                    type="hidden"
                                    name="subjects[${subjectId}][selected]"
                                    value="1"
                                >

                            </td>


                            <td class="number">
                                ${formatNumber(
                                    subject.theory_max_marks
                                )}
                            </td>

                            <td class="number">
                                ${formatNumber(
                                    subject.theory_passing_marks
                                )}
                            </td>

                            <td class="number">
                                ${formatNumber(
                                    subject.oral_max_marks
                                )}
                            </td>

                            <td class="number">
                                ${formatNumber(
                                    subject.oral_passing_marks
                                )}
                            </td>

                            <td class="number">
                                ${formatNumber(
                                    subject.practical_max_marks
                                )}
                            </td>

                            <td class="number">
                                ${formatNumber(
                                    subject.practical_passing_marks
                                )}
                            </td>

                            <td class="number">

                                <strong>
                                    ${formatNumber(
                                        subject.total_max_marks
                                    )}
                                </strong>

                            </td>

                            <td class="number">

                                <strong>
                                    ${formatNumber(
                                        subject.total_passing_marks
                                    )}
                                </strong>

                            </td>

                            <td class="number">

                                ${
                                    isOptional
                                    ?
                                    '<span class="type-optional">Optional</span>'
                                    :
                                    '<span class="type-compulsory">Compulsory</span>'
                                }

                            </td>

                        </tr>
                        `;
                }
            );


            tableBody.innerHTML =
                html;
        }


        /*
        |--------------------------------------------------------------------------
        | EVENTS
        |--------------------------------------------------------------------------
        */

        standardSelect.addEventListener(
            'change',
            function () {

                loadStandardStructure();

            }
        );


        examTypeSelect.addEventListener(
            'change',
            function () {

                updateExamName();

                loadExamTypeStructure();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | INITIAL PAGE LOAD
        |--------------------------------------------------------------------------
        */

        if (
            standardSelect.value
        ) {

            loadStandardStructure();

        }

    }
);

</script>
