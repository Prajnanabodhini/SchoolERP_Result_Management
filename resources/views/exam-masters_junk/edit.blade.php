<x-app-layout>


<style>
    /*
    |--------------------------------------------------------------------------
    | BASE
    |--------------------------------------------------------------------------
    */

    .exam-edit-page,
    .exam-edit-page * {
        font-family: Arial, sans-serif !important;
        font-size: 13px !important;
    }

    .exam-edit-page h2 {
        font-size: 20px !important;
        font-weight: 700 !important;
        color: #1D4ED8 !important;
        margin: 0;
    }

    .exam-edit-page h3 {
        font-size: 15px !important;
        font-weight: 700 !important;
    }

    .exam-edit-page label {
        font-size: 12px !important;
        font-weight: 700 !important;
        color: #374151;
    }

    .exam-edit-page input,
    .exam-edit-page select {
        font-size: 13px !important;
    }

    /*
    |--------------------------------------------------------------------------
    | INPUTS
    |--------------------------------------------------------------------------
    */

    .exam-edit-page .erp-input,
    .exam-edit-page .erp-select {
        width: 100%;
        height: 34px;
        padding: 5px 9px;
        border: 1px solid #9CA3AF;
        border-radius: 5px;
        background: #FFFFFF;
        color: #111827;
        box-sizing: border-box;
    }

    .exam-edit-page .erp-input:focus,
    .exam-edit-page .erp-select:focus {
        outline: none;
        border-color: #2563EB;
        box-shadow: 0 0 0 1px #2563EB;
    }

    .exam-edit-page .readonly-input {
        background: #F3F4F6;
        color: #374151;
    }

    /*
    |--------------------------------------------------------------------------
    | BUTTONS
    |--------------------------------------------------------------------------
    */

    .exam-edit-page .erp-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 34px;
        padding: 5px 13px;
        border-radius: 5px;
        border: 1px solid transparent;
        font-size: 13px !important;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        box-sizing: border-box;
    }

    .exam-edit-page .btn-update {
        background: #16A34A;
        color: #FFFFFF !important;
        border-color: #15803D;
    }

    .exam-edit-page .btn-update:hover {
        background: #15803D;
    }

    .exam-edit-page .btn-cancel {
        background: #6B7280;
        color: #FFFFFF !important;
        border-color: #4B5563;
    }

    .exam-edit-page .btn-cancel:hover {
        background: #4B5563;
    }

    /*
    |--------------------------------------------------------------------------
    | BOXES
    |--------------------------------------------------------------------------
    */

    .exam-edit-page .section-box {
        background: #FFFFFF;
        border: 1px solid #D1D5DB;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
    }

    .exam-edit-page .info-box {
        background: #EFF6FF;
        border: 1px solid #BFDBFE;
        color: #1E40AF;
        border-radius: 6px;
        padding: 10px 12px;
        line-height: 1.45;
    }

    .exam-edit-page .term-box {
        background: #FFF7ED;
        border: 1px solid #FDBA74;
        border-radius: 7px;
        padding: 12px;
    }

    .exam-edit-page .term-component {
        background: #FFFFFF;
        border: 1px solid #D1D5DB;
        border-radius: 6px;
        padding: 10px;
    }

    /*
    |--------------------------------------------------------------------------
    | SUBJECT TABLE
    |--------------------------------------------------------------------------
    */

    .exam-edit-page table {
        width: 100%;
        border-collapse: collapse;
        background: #FFFFFF;
    }

    .exam-edit-page th {
        background: #DBEAFE;
        border: 1px solid #9CA3AF;
        padding: 8px;
        text-align: center;
        font-weight: 700 !important;
        white-space: nowrap;
    }

    .exam-edit-page td {
        border: 1px solid #D1D5DB;
        padding: 7px;
        vertical-align: middle;
    }

    .exam-edit-page tbody tr:hover {
        background: #FEFCE8;
    }

    .subject-name {
        font-weight: 700;
    }

    .subject-code {
        margin-top: 2px;
        color: #6B7280;
        font-size: 11px !important;
    }

    .optional-badge {
        display: inline-block;
        margin-left: 6px;
        padding: 2px 6px;
        border-radius: 4px;
        background: #FEF3C7;
        color: #92400E;
        border: 1px solid #F59E0B;
        font-size: 10px !important;
        font-weight: 700;
    }

    .required-badge {
        display: inline-block;
        margin-left: 6px;
        padding: 2px 6px;
        border-radius: 4px;
        background: #DBEAFE;
        color: #1E40AF;
        border: 1px solid #93C5FD;
        font-size: 10px !important;
        font-weight: 700;
    }

    /*
    |--------------------------------------------------------------------------
    | TERM TOTALS
    |--------------------------------------------------------------------------
    */

    .term-total {
        margin-top: 10px;
        padding: 8px 10px;
        background: #EFF6FF;
        border: 1px solid #BFDBFE;
        border-radius: 5px;
        color: #1E40AF;
        font-weight: 700;
    }

    /*
    |--------------------------------------------------------------------------
    | MESSAGES
    |--------------------------------------------------------------------------
    */

    .message-success {
        background: #DCFCE7;
        border: 1px solid #86EFAC;
        color: #166534;
        padding: 10px 12px;
        border-radius: 6px;
        margin-bottom: 12px;
        font-weight: 600;
    }

    .message-error {
        background: #FEE2E2;
        border: 1px solid #FCA5A5;
        color: #991B1B;
        padding: 10px 12px;
        border-radius: 6px;
        margin-bottom: 12px;
    }

    .passing-note {
        margin-top: 6px;
        color: #1E40AF;
        font-size: 11px !important;
        font-weight: 700;
    }

    .hidden {
        display: none !important;
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSIVE
    |--------------------------------------------------------------------------
    */

    @media (max-width: 800px) {

        .exam-edit-grid {
            grid-template-columns: 1fr !important;
        }

        .exam-edit-actions {
            flex-direction: column !important;
            align-items: stretch !important;
        }

        .exam-edit-actions .erp-btn {
            width: 100%;
        }

        .term-component-grid {
            grid-template-columns: 1fr !important;
        }
    }
</style>


@php

    /*
    |--------------------------------------------------------------------------
    | EXAM TYPE
    |--------------------------------------------------------------------------
    |
    | Prefer the stored exam_type.
    |
    */

    $currentExamType =
        old(
            'exam_type',
            $examMaster->exam_type
                ?? ''
        );

    $currentExamType =
        strtoupper(
            trim(
                (string) $currentExamType
            )
        );


    /*
    |--------------------------------------------------------------------------
    | FALLBACK EXAM TYPE FROM EXAM NAME
    |--------------------------------------------------------------------------
    */

    if (
        $currentExamType === ''
        &&
        !empty($examMaster->exam_name)
    ) {

        $parts =
            explode(
                ' - ',
                strtoupper(
                    trim(
                        (string) $examMaster->exam_name
                    )
                ),
                2
            );

        $currentExamType =
            trim(
                $parts[0] ?? ''
            );
    }


    /*
    |--------------------------------------------------------------------------
    | STANDARD ID
    |--------------------------------------------------------------------------
    */

    $currentStandardId =
        (int) (
            old(
                'standard_id',
                $examMaster->standard_id
                ?? 0
            )
        );


    /*
    |--------------------------------------------------------------------------
    | PASSING PERCENTAGE
    |--------------------------------------------------------------------------
    */

    $standardNameForPercentage =
        strtoupper(
            trim(
                (string) (
                    optional(
                        $standards->firstWhere(
                            'id',
                            $currentStandardId
                        )
                    )->standard_name
                    ?? ''
                )
            )
        );

    $normalizedStandardNameForPercentage =
        preg_replace(
            '/[^A-Z0-9]+/',
            '',
            $standardNameForPercentage
        ) ?? '';


    $is35PercentStandard =
        in_array(
            $currentStandardId,
            [
                9,
                10,
                11,
                12,
            ],
            true
        )
        ||
        in_array(
            $normalizedStandardNameForPercentage,
            [
                'NURSERY',
                'NUR',
                'JRKG',
                'JUNIORKG',
                'JUNIORKINDERGARTEN',
                'SRKG',
                'SENIORKG',
                'SENIORKINDERGARTEN',
            ],
            true
        )
        ||
        in_array(
            $normalizedStandardNameForPercentage,
            [
                'NINTH',
                '9TH',
                'IX',
                'TENTH',
                '10TH',
                'X',
            ],
            true
        )
        ||
        str_contains(
            $normalizedStandardNameForPercentage,
            'ELEVENTH'
        )
        ||
        $normalizedStandardNameForPercentage === 'XI'
        ||
        str_contains(
            $normalizedStandardNameForPercentage,
            'TWELFTH'
        )
        ||
        $normalizedStandardNameForPercentage === 'XII';


    $passingPercentage =
        $is35PercentStandard
            ? 35
            : 40;


    /*
    |--------------------------------------------------------------------------
    | TERM EXAM
    |--------------------------------------------------------------------------
    */

    $isTermExam =
        str_starts_with(
            $currentExamType,
            'TERM 1'
        )
        ||
        str_starts_with(
            $currentExamType,
            'TERM 2'
        );


    /*
    |--------------------------------------------------------------------------
    | EXISTING TERM VALUES
    |--------------------------------------------------------------------------
    */

    $existingHasTheory =
        (int) (
            old(
                'has_theory',
                $examMaster->has_theory
                ?? 0
            )
        ) === 1;

    $existingHasOral =
        (int) (
            old(
                'has_oral',
                $examMaster->has_oral
                ?? 0
            )
        ) === 1;

    $existingHasPractical =
        (int) (
            old(
                'has_practical',
                $examMaster->has_practical
                ?? 0
            )
        ) === 1;


    $existingTheoryMax =
        (float) (
            old(
                'theory_max_marks',
                $examMaster->theory_max_marks
                ?? 0
            )
        );

    $existingTheoryPassing =
        (float) (
            old(
                'theory_passing_marks',
                $examMaster->theory_passing_marks
                ?? 0
            )
        );


    $existingOralMax =
        (float) (
            old(
                'oral_max_marks',
                $examMaster->oral_max_marks
                ?? 0
            )
        );

    $existingOralPassing =
        (float) (
            old(
                'oral_passing_marks',
                $examMaster->oral_passing_marks
                ?? 0
            )
        );


    $existingPracticalMax =
        (float) (
            old(
                'practical_max_marks',
                $examMaster->practical_max_marks
                ?? 0
            )
        );

    $existingPracticalPassing =
        (float) (
            old(
                'practical_passing_marks',
                $examMaster->practical_passing_marks
                ?? 0
            )
        );


    $existingTermTotalMax =
        $existingTheoryMax
        +
        $existingOralMax
        +
        $existingPracticalMax;


    $existingTermTotalPassing =
        $existingTheoryPassing
        +
        $existingOralPassing
        +
        $existingPracticalPassing;

@endphp


<div class="max-w-6xl mx-auto py-5 px-4 exam-edit-page">

    <div
        style="
            background:
                linear-gradient(
                    135deg,
                    #fee2e2,
                    #fef3c7,
                    #ffedd5
                );
            border:4px solid #f59e0b;
            border-radius:12px;
            box-shadow:0 6px 18px rgba(0,0,0,.15);
            padding:18px;
        "
    >

        {{-- ==========================================================
             HEADER
        =========================================================== --}}

        <div
            style="
                display:flex;
                justify-content:space-between;
                align-items:center;
                gap:10px;
                margin-bottom:15px;
            "
            class="exam-edit-header"
        >

            <div>

                <h2>
                    Edit Exam Master
                </h2>

                <div
                    style="
                        margin-top:4px;
                        color:#4B5563;
                        font-size:12px !important;
                        font-weight:600;
                    "
                >
                    Exam ID:
                    {{ $examMaster->id }}
                </div>

            </div>


            <a
                href="{{ route('exam-masters.index') }}"
                class="erp-btn btn-cancel"
            >
                Back to Exam Master
            </a>

        </div>


        {{-- ==========================================================
             SUCCESS
        =========================================================== --}}

        @if(session('success'))

            <div class="message-success">
                {{ session('success') }}
            </div>

        @endif


        {{-- ==========================================================
             ERROR
        =========================================================== --}}

        @if(session('error'))

            <div class="message-error">
                {{ session('error') }}
            </div>

        @endif


        {{-- ==========================================================
             VALIDATION ERRORS
        =========================================================== --}}

        @if($errors->any())

            <div class="message-error">

                <strong>
                    Please correct the following:
                </strong>

                <ul
                    style="
                        margin-top:6px;
                        padding-left:20px;
                    "
                >

                    @foreach($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        {{-- ==========================================================
             FORM
        =========================================================== --}}

        <form
            method="POST"
            action="{{ route(
                'exam-masters.update',
                $examMaster->id
            ) }}"
            id="examMasterEditForm"
        >

            @csrf

            @method('PUT')


            {{-- ======================================================
                 BASIC INFORMATION
            ======================================================= --}}

            <div class="section-box">

                <h3 class="mb-3">
                    Exam Information
                </h3>


                <div
                    class="
                        grid
                        grid-cols-2
                        gap-4
                        exam-edit-grid
                    "
                >

                    {{-- ACADEMIC YEAR --}}

                    <div>

                        <label
                            for="academic_year_id"
                        >
                            Academic Year
                        </label>

                        <select
                            name="academic_year_id"
                            id="academic_year_id"
                            class="erp-select"
                            required
                        >

                            <option value="">
                                Select Academic Year
                            </option>

                            @foreach($academicYears as $academicYear)

                                <option
                                    value="{{ $academicYear->id }}"
                                    {{
                                        (string) old(
                                            'academic_year_id',
                                            $examMaster->academic_year_id
                                        )
                                        ===
                                        (string) $academicYear->id
                                            ? 'selected'
                                            : ''
                                    }}
                                >

                                    {{ $academicYear->year_name }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- STANDARD --}}

                    <div>

                        <label
                            for="standard_id"
                        >
                            Standard
                        </label>

                        <select
                            name="standard_id"
                            id="standard_id"
                            class="erp-select"
                            required
                        >

                            <option value="">
                                Select Standard
                            </option>

                            @foreach($standards as $standard)

                                <option
                                    value="{{ $standard->id }}"
                                    {{
                                        (string) old(
                                            'standard_id',
                                            $examMaster->standard_id
                                        )
                                        ===
                                        (string) $standard->id
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

                    <div>

                        <label
                            for="exam_type"
                        >
                            Exam Type
                        </label>

                        <select
                            name="exam_type"
                            id="exam_type"
                            class="erp-select"
                            required
                        >

                            <option value="">
                                Select Exam Type
                            </option>

                            <option
                                value="UNIT TEST 1"
                                {{ $currentExamType === 'UNIT TEST 1' ? 'selected' : '' }}
                            >
                                Unit Test 1
                            </option>

                            <option
                                value="UNIT TEST 2"
                                {{ $currentExamType === 'UNIT TEST 2' ? 'selected' : '' }}
                            >
                                Unit Test 2
                            </option>

                            <option
                                value="UNIT TEST 3"
                                {{ $currentExamType === 'UNIT TEST 3' ? 'selected' : '' }}
                            >
                                Unit Test 3
                            </option>

                            <option
                                value="UNIT TEST 4"
                                {{ $currentExamType === 'UNIT TEST 4' ? 'selected' : '' }}
                            >
                                Unit Test 4
                            </option>

                            <option
                                value="TERM 1"
                                {{ $currentExamType === 'TERM 1' ? 'selected' : '' }}
                            >
                                Term 1
                            </option>

                            <option
                                value="TERM 2"
                                {{ $currentExamType === 'TERM 2' ? 'selected' : '' }}
                            >
                                Term 2
                            </option>

                            <option
                                value="ANNUAL"
                                {{ $currentExamType === 'ANNUAL' ? 'selected' : '' }}
                            >
                                Annual
                            </option>

                        </select>

                    </div>


                    {{-- EXAM NAME --}}

                    <div>

                        <label
                            for="exam_name_preview"
                        >
                            Exam Name
                        </label>

                        <input
                            type="hidden"
                            name="exam_name"
                            id="exam_name"
                            value="{{ old(
                                'exam_name',
                                $examMaster->exam_name
                            ) }}"
                        >

                        <input
                            type="text"
                            id="exam_name_preview"
                            value="{{ old(
                                'exam_name',
                                $examMaster->exam_name
                            ) }}"
                            class="erp-input readonly-input"
                            readonly
                        >

                    </div>

                </div>


                {{-- CURRENT PASSING RULE --}}

                <div
                    id="passingPercentageNote"
                    class="passing-note"
                >
                    Passing Percentage:
                    <strong>
                        {{ $passingPercentage }}%
                    </strong>
                </div>

            </div>


            {{-- ======================================================
                 TERM COMPONENT CONFIGURATION
            ======================================================= --}}

            <div
                id="termConfigurationBox"
                class="term-box mb-4"
                style="{{ $isTermExam ? '' : 'display:none;' }}"
            >

                <div
                    style="
                        font-size:15px !important;
                        font-weight:700;
                        color:#9A3412;
                        margin-bottom:10px;
                    "
                >
                    Term Exam Component Configuration
                </div>


                <div class="info-box mb-3">

                    Only Term 1 and Term 2 use the Theory, Oral and
                    Practical component configuration. The values below
                    are saved at Exam Master level and used consistently
                    for the subjects of this Term exam.

                </div>


                <div
                    class="
                        grid
                        grid-cols-3
                        gap-3
                        term-component-grid
                    "
                >

                    {{-- THEORY --}}

                    <div class="term-component">

                        <label
                            style="
                                display:flex;
                                align-items:center;
                                gap:7px;
                                margin-bottom:8px;
                            "
                        >

                            <input
                                type="checkbox"
                                name="has_theory"
                                id="has_theory"
                                value="1"
                                {{ $existingHasTheory ? 'checked' : '' }}
                            >

                            Theory

                        </label>


                        <div
                            id="theoryFields"
                            style="{{ $existingHasTheory ? '' : 'display:none;' }}"
                        >

                            <label>
                                Theory Max Marks
                            </label>

                            <input
                                type="number"
                                name="theory_max_marks"
                                id="theory_max_marks"
                                value="{{ $existingTheoryMax }}"
                                min="0"
                                step="0.01"
                                class="erp-input"
                            >


                            <div style="height:7px;"></div>


                            <label>
                                Theory Passing Marks
                            </label>

                            <input
                                type="number"
                                name="theory_passing_marks"
                                id="theory_passing_marks"
                                value="{{ $existingTheoryPassing }}"
                                min="0"
                                step="0.01"
                                class="erp-input"
                            >

                        </div>

                    </div>


                    {{-- ORAL --}}

                    <div class="term-component">

                        <label
                            style="
                                display:flex;
                                align-items:center;
                                gap:7px;
                                margin-bottom:8px;
                            "
                        >

                            <input
                                type="checkbox"
                                name="has_oral"
                                id="has_oral"
                                value="1"
                                {{ $existingHasOral ? 'checked' : '' }}
                            >

                            Oral

                        </label>


                        <div
                            id="oralFields"
                            style="{{ $existingHasOral ? '' : 'display:none;' }}"
                        >

                            <label>
                                Oral Max Marks
                            </label>

                            <input
                                type="number"
                                name="oral_max_marks"
                                id="oral_max_marks"
                                value="{{ $existingOralMax }}"
                                min="0"
                                step="0.01"
                                class="erp-input"
                            >


                            <div style="height:7px;"></div>


                            <label>
                                Oral Passing Marks
                            </label>

                            <input
                                type="number"
                                name="oral_passing_marks"
                                id="oral_passing_marks"
                                value="{{ $existingOralPassing }}"
                                min="0"
                                step="0.01"
                                class="erp-input"
                            >

                        </div>

                    </div>


                    {{-- PRACTICAL --}}

                    <div class="term-component">

                        <label
                            style="
                                display:flex;
                                align-items:center;
                                gap:7px;
                                margin-bottom:8px;
                            "
                        >

                            <input
                                type="checkbox"
                                name="has_practical"
                                id="has_practical"
                                value="1"
                                {{ $existingHasPractical ? 'checked' : '' }}
                            >

                            Practical

                        </label>


                        <div
                            id="practicalFields"
                            style="{{ $existingHasPractical ? '' : 'display:none;' }}"
                        >

                            <label>
                                Practical Max Marks
                            </label>

                            <input
                                type="number"
                                name="practical_max_marks"
                                id="practical_max_marks"
                                value="{{ $existingPracticalMax }}"
                                min="0"
                                step="0.01"
                                class="erp-input"
                            >


                            <div style="height:7px;"></div>


                            <label>
                                Practical Passing Marks
                            </label>

                            <input
                                type="number"
                                name="practical_passing_marks"
                                id="practical_passing_marks"
                                value="{{ $existingPracticalPassing }}"
                                min="0"
                                step="0.01"
                                class="erp-input"
                            >

                        </div>

                    </div>

                </div>


                <div
                    id="termTotalBox"
                    class="term-total"
                >
                    Total Max Marks:
                    <strong id="termTotalMax">
                        {{ \App\Helpers\ResultSheetBladeHelper::displayNumber($existingTermTotalMax) }}
                    </strong>

                    &nbsp;&nbsp;|&nbsp;&nbsp;

                    Total Passing Marks:
                    <strong id="termTotalPassing">
                        {{ \App\Helpers\ResultSheetBladeHelper::displayNumber($existingTermTotalPassing) }}
                    </strong>
                </div>

            </div>


            {{-- ======================================================
                 SUBJECT CONFIGURATION
            ======================================================= --}}

            <div class="section-box">

                <div
                    style="
                        display:flex;
                        justify-content:space-between;
                        align-items:center;
                        gap:10px;
                        margin-bottom:10px;
                    "
                >

                    <h3>
                        Subject Wise Configuration
                    </h3>

                    <span
                        id="subjectLoading"
                        style="
                            display:none;
                            color:#2563EB;
                            font-weight:700;
                        "
                    >
                        Loading subjects...
                    </span>

                </div>


                <div class="info-box mb-3">

                    <strong>
                        Note:
                    </strong>

                    For non-Term exams, each subject keeps its own
                    Max Marks and the controller calculates the passing
                    marks using the standard's {{ $passingPercentage }}%
                    rule.

                    For Term exams, the Exam Master Theory / Oral /
                    Practical component configuration is used.

                </div>


                <div class="overflow-x-auto">

                    <table>

                        <thead>

                            <tr>

                                <th style="width:6%;">
                                    #
                                </th>

                                <th style="width:38%; text-align:left;">
                                    Subject
                                </th>

                                <th style="width:20%;">
                                    Max Marks
                                </th>

                                <th style="width:20%;">
                                    Passing Marks
                                </th>

                                <th style="width:16%;">
                                    Type
                                </th>

                            </tr>

                        </thead>


                        <tbody id="subjectTableBody">

                            @forelse($subjects as $index => $subject)

                                @php

                                    $subjectId =
                                        (int) (
                                            $subject->subject_id
                                            ?? 0
                                        );

                                    $subjectMaxMarks =
                                        (float) (
                                            old(
                                                "subjects.$subjectId.max_marks",
                                                $subject->max_marks
                                                ?? 40
                                            )
                                        );

                                    $subjectPassingMarks =
                                        (float) (
                                            old(
                                                "subjects.$subjectId.passing_marks",
                                                $subject->passing_marks
                                                ?? (
                                                    $subjectMaxMarks > 0
                                                        ? ceil(
                                                            $subjectMaxMarks
                                                            *
                                                            (
                                                                $passingPercentage
                                                                /
                                                                100
                                                            )
                                                        )
                                                        : 0
                                                )
                                            )
                                        );

                                    $isOptionalSubject =
                                        (int) (
                                            $subject->is_optional
                                            ?? 0
                                        ) === 1;

                                    $displayOrder =
                                        (int) (
                                            old(
                                                "subjects.$subjectId.display_order",
                                                $subject->display_order
                                                ?? $subject->sort_order
                                                ?? ($index + 1)
                                            )
                                        );

                                @endphp


                                <tr>

                                    {{-- NUMBER --}}

                                    <td class="text-center">
                                        {{ $index + 1 }}
                                    </td>


                                    {{-- SUBJECT --}}

                                    <td>

                                        <div class="subject-name">

                                            {{ $subject->subject_name }}

                                            @if($isOptionalSubject)

                                                <span class="optional-badge">
                                                    OPTIONAL
                                                </span>

                                            @else

                                                <span class="required-badge">
                                                    COMPULSORY
                                                </span>

                                            @endif

                                        </div>


                                        @if(
                                            !empty($subject->subject_code)
                                            ||
                                            !empty($subject->short_name)
                                        )

                                            <div class="subject-code">

                                                @if(!empty($subject->subject_code))

                                                    Code:
                                                    {{ $subject->subject_code }}

                                                @endif

                                                @if(
                                                    !empty($subject->subject_code)
                                                    &&
                                                    !empty($subject->short_name)
                                                )

                                                    &nbsp;|&nbsp;

                                                @endif

                                                @if(!empty($subject->short_name))

                                                    Short:
                                                    {{ $subject->short_name }}

                                                @endif

                                            </div>

                                        @endif


                                        {{-- SUBJECT ID --}}

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
                                            class="subject-display-order"
                                        >

                                    </td>


                                    {{-- MAX MARKS --}}

                                    <td class="text-center">

                                        @if($isTermExam)

                                            <span
                                                style="
                                                    color:#1E40AF;
                                                    font-weight:700;
                                                "
                                            >
                                                Uses Term Components
                                            </span>

                                            <input
                                                type="hidden"
                                                name="subjects[{{ $subjectId }}][max_marks]"
                                                value="{{ $subjectMaxMarks }}"
                                                class="subject-max-input"
                                            >

                                        @else

                                            <select
                                                name="subjects[{{ $subjectId }}][max_marks]"
                                                class="erp-select subject-max-input"
                                            >

                                                @foreach([
                                                    20,
                                                    25,
                                                    40,
                                                    50,
                                                    80,
                                                    100
                                                ] as $possibleMax)

                                                    <option
                                                        value="{{ $possibleMax }}"
                                                        {{
                                                            (float) $subjectMaxMarks
                                                            ===
                                                            (float) $possibleMax
                                                                ? 'selected'
                                                                : ''
                                                        }}
                                                    >
                                                        {{ $possibleMax }}
                                                    </option>

                                                @endforeach


                                                @if(
                                                    $subjectMaxMarks > 0
                                                    &&
                                                    !in_array(
                                                        (float) $subjectMaxMarks,
                                                        [
                                                            20,
                                                            25,
                                                            40,
                                                            50,
                                                            80,
                                                            100
                                                        ],
                                                        true
                                                    )
                                                )

                                                    <option
                                                        value="{{ $subjectMaxMarks }}"
                                                        selected
                                                    >
                                                        {{ $subjectMaxMarks }}
                                                    </option>

                                                @endif

                                            </select>

                                        @endif

                                    </td>


                                    {{-- PASSING MARKS --}}

                                    <td class="text-center">

                                        @if($isTermExam)

                                            <span
                                                style="
                                                    color:#1E40AF;
                                                    font-weight:700;
                                                "
                                            >
                                                Uses Term Components
                                            </span>

                                            <input
                                                type="hidden"
                                                name="subjects[{{ $subjectId }}][passing_marks]"
                                                value="{{ $subjectPassingMarks }}"
                                                class="subject-passing-input"
                                            >

                                        @else

                                            <input
                                                type="number"
                                                name="subjects[{{ $subjectId }}][passing_marks]"
                                                value="{{ $subjectPassingMarks }}"
                                                min="0"
                                                step="0.01"
                                                readonly
                                                class="
                                                    erp-input
                                                    readonly-input
                                                    subject-passing-input
                                                "
                                            >

                                        @endif

                                    </td>


                                    {{-- TYPE --}}

                                    <td class="text-center">

                                        @if($isOptionalSubject)

                                            <span
                                                style="
                                                    color:#92400E;
                                                    font-weight:700;
                                                "
                                            >
                                                Optional
                                            </span>

                                        @else

                                            <span
                                                style="
                                                    color:#1E40AF;
                                                    font-weight:700;
                                                "
                                            >
                                                Compulsory
                                            </span>

                                        @endif

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="5"
                                        class="text-center"
                                        style="
                                            padding:20px;
                                            color:#6B7280;
                                        "
                                    >
                                        No subjects are mapped to this Standard.
                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>


            {{-- ======================================================
                 DISPLAY ORDER + STATUS
            ======================================================= --}}

            <div class="section-box">

                <div
                    class="
                        grid
                        grid-cols-2
                        gap-4
                        exam-edit-grid
                    "
                >

                    {{-- DISPLAY ORDER --}}

                    <div>

                        <label
                            for="display_order"
                        >
                            Display Order
                        </label>

                        <input
                            type="number"
                            name="display_order"
                            id="display_order"
                            value="{{ old(
                                'display_order',
                                $examMaster->display_order
                            ) }}"
                            min="0"
                            class="erp-input readonly-input"
                            readonly
                        >

                    </div>


                    {{-- STATUS --}}

                    <div>

                        <label
                            style="
                                display:block;
                                margin-bottom:8px;
                            "
                        >
                            Status
                        </label>

                        <label
                            style="
                                display:flex;
                                align-items:center;
                                gap:8px;
                                font-weight:700;
                            "
                        >

                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                {{
                                    old(
                                        'is_active',
                                        $examMaster->is_active
                                    )
                                        ? 'checked'
                                        : ''
                                }}
                            >

                            Active

                        </label>

                    </div>

                </div>

            </div>


            {{-- ======================================================
                 ACTIONS
            ======================================================= --}}

            <div
                class="
                    flex
                    justify-end
                    items-center
                    gap-2
                    exam-edit-actions
                "
            >

                <a
                    href="{{ route('exam-masters.index') }}"
                    class="erp-btn btn-cancel"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="erp-btn btn-update"
                >
                    Update Exam
                </button>

            </div>

        </form>

    </div>

</div>


{{-- ==============================================================
     JAVASCRIPT
============================================================== --}}

<script>

    document.addEventListener(
        'DOMContentLoaded',
        function () {

            const form =
                document.getElementById(
                    'examMasterEditForm'
                );

            const academicYear =
                document.getElementById(
                    'academic_year_id'
                );

            const standard =
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

            const termConfigurationBox =
                document.getElementById(
                    'termConfigurationBox'
                );

            const hasTheory =
                document.getElementById(
                    'has_theory'
                );

            const hasOral =
                document.getElementById(
                    'has_oral'
                );

            const hasPractical =
                document.getElementById(
                    'has_practical'
                );

            const theoryFields =
                document.getElementById(
                    'theoryFields'
                );

            const oralFields =
                document.getElementById(
                    'oralFields'
                );

            const practicalFields =
                document.getElementById(
                    'practicalFields'
                );

            const theoryMax =
                document.getElementById(
                    'theory_max_marks'
                );

            const theoryPassing =
                document.getElementById(
                    'theory_passing_marks'
                );

            const oralMax =
                document.getElementById(
                    'oral_max_marks'
                );

            const oralPassing =
                document.getElementById(
                    'oral_passing_marks'
                );

            const practicalMax =
                document.getElementById(
                    'practical_max_marks'
                );

            const practicalPassing =
                document.getElementById(
                    'practical_passing_marks'
                );

            const termTotalMax =
                document.getElementById(
                    'termTotalMax'
                );

            const termTotalPassing =
                document.getElementById(
                    'termTotalPassing'
                );

            const passingPercentageNote =
                document.getElementById(
                    'passingPercentageNote'
                );

            const tableBody =
                document.getElementById(
                    'subjectTableBody'
                );

            const loading =
                document.getElementById(
                    'subjectLoading'
                );


            /*
            |--------------------------------------------------------------------------
            | GET STANDARD TEXT
            |--------------------------------------------------------------------------
            */

            function getStandardText()
            {
                if (
                    !standard
                    ||
                    !standard.value
                ) {
                    return '';
                }

                const selectedOption =
                    standard.options[
                        standard.selectedIndex
                    ];

                return selectedOption
                    ? selectedOption.text.trim()
                    : '';
            }


            /*
            |--------------------------------------------------------------------------
            | BUILD EXAM NAME
            |--------------------------------------------------------------------------
            */

            function buildExamName()
            {
                const type =
                    examType
                        ? examType.value.trim()
                        : '';

                const standardText =
                    getStandardText();

                if (
                    !type
                    ||
                    !standardText
                ) {

                    if (examName) {
                        examName.value = '';
                    }

                    if (examNamePreview) {
                        examNamePreview.value = '';
                    }

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
            | IS TERM EXAM
            |--------------------------------------------------------------------------
            */

            function isTermExamSelected()
            {
                const type =
                    examType
                        ? examType.value.trim().toUpperCase()
                        : '';

                return (
                    type.indexOf('TERM 1') === 0
                    ||
                    type.indexOf('TERM 2') === 0
                );
            }


            /*
            |--------------------------------------------------------------------------
            | PASSING PERCENTAGE BY STANDARD
            |--------------------------------------------------------------------------
            */

            function getPassingPercentage()
            {
                const selectedOption =
                    standard.options[
                        standard.selectedIndex
                    ];

                const standardId =
                    parseInt(
                        standard.value || 0,
                        10
                    );

                const standardText =
                    selectedOption
                        ? selectedOption.text
                            .trim()
                            .toUpperCase()
                        : '';

                const normalized =
                    standardText.replace(
                        /[^A-Z0-9]+/g,
                        ''
                    );

                if (
                    [
                        9,
                        10,
                        11,
                        12
                    ].includes(
                        standardId
                    )
                ) {
                    return 35;
                }

                if (
                    [
                        'NURSERY',
                        'NUR',
                        'JRKG',
                        'JUNIORKG',
                        'JUNIORKINDERGARTEN',
                        'SRKG',
                        'SENIORKG',
                        'SENIORKINDERGARTEN',
                        'NINTH',
                        '9TH',
                        'IX',
                        'TENTH',
                        '10TH',
                        'X'
                    ].includes(
                        normalized
                    )
                ) {
                    return 35;
                }

                if (
                    normalized.indexOf(
                        'ELEVENTH'
                    ) >= 0
                    ||
                    normalized === 'XI'
                ) {
                    return 35;
                }

                if (
                    normalized.indexOf(
                        'TWELFTH'
                    ) >= 0
                    ||
                    normalized === 'XII'
                ) {
                    return 35;
                }

                return 40;
            }


            /*
            |--------------------------------------------------------------------------
            | UPDATE PASSING NOTE
            |--------------------------------------------------------------------------
            */

            function updatePassingPercentageNote()
            {
                if (!passingPercentageNote) {
                    return;
                }

                const percentage =
                    getPassingPercentage();

                passingPercentageNote.innerHTML =
                    'Passing Percentage: <strong>'
                    +
                    percentage
                    +
                    '%</strong>';
            }


            /*
            |--------------------------------------------------------------------------
            | UPDATE TERM FIELDS
            |--------------------------------------------------------------------------
            */

            function updateTermConfiguration()
            {
                const isTerm =
                    isTermExamSelected();

                if (termConfigurationBox) {

                    termConfigurationBox.style.display =
                        isTerm
                            ? ''
                            : 'none';
                }

                if (!isTerm) {
                    return;
                }

                if (
                    theoryFields
                    &&
                    hasTheory
                ) {

                    theoryFields.style.display =
                        hasTheory.checked
                            ? ''
                            : 'none';
                }

                if (
                    oralFields
                    &&
                    hasOral
                ) {

                    oralFields.style.display =
                        hasOral.checked
                            ? ''
                            : 'none';
                }

                if (
                    practicalFields
                    &&
                    hasPractical
                ) {

                    practicalFields.style.display =
                        hasPractical.checked
                            ? ''
                            : 'none';
                }

                calculateTermTotals();
            }


            /*
            |--------------------------------------------------------------------------
            | PASSING MARK CALCULATION
            |--------------------------------------------------------------------------
            */

            function calculatePassing(
                maxValue
            )
            {
                const max =
                    parseFloat(
                        maxValue || 0
                    );

                if (
                    !max
                    ||
                    max < 0
                ) {
                    return 0;
                }

                const percentage =
                    getPassingPercentage();

                return Math.ceil(
                    max
                    *
                    percentage
                    /
                    100
                );
            }


            /*
            |--------------------------------------------------------------------------
            | UPDATE TERM PASSING MARKS
            |--------------------------------------------------------------------------
            */

            function updateTermPassingMarks()
            {
                if (
                    hasTheory
                    &&
                    hasTheory.checked
                    &&
                    theoryMax
                    &&
                    theoryPassing
                ) {

                    theoryPassing.value =
                        calculatePassing(
                            theoryMax.value
                        );
                }

                if (
                    hasOral
                    &&
                    hasOral.checked
                    &&
                    oralMax
                    &&
                    oralPassing
                ) {

                    oralPassing.value =
                        calculatePassing(
                            oralMax.value
                        );
                }

                if (
                    hasPractical
                    &&
                    hasPractical.checked
                    &&
                    practicalMax
                    &&
                    practicalPassing
                ) {

                    practicalPassing.value =
                        calculatePassing(
                            practicalMax.value
                        );
                }

                calculateTermTotals();
            }


            /*
            |--------------------------------------------------------------------------
            | TERM TOTALS
            |--------------------------------------------------------------------------
            */

            function calculateTermTotals()
            {
                const theory =
                    hasTheory
                    &&
                    hasTheory.checked
                        ? parseFloat(
                            theoryMax
                                ? theoryMax.value
                                : 0
                        ) || 0
                        : 0;

                const oral =
                    hasOral
                    &&
                    hasOral.checked
                        ? parseFloat(
                            oralMax
                                ? oralMax.value
                                : 0
                        ) || 0
                        : 0;

                const practical =
                    hasPractical
                    &&
                    hasPractical.checked
                        ? parseFloat(
                            practicalMax
                                ? practicalMax.value
                                : 0
                        ) || 0
                        : 0;

                const theoryPass =
                    hasTheory
                    &&
                    hasTheory.checked
                        ? parseFloat(
                            theoryPassing
                                ? theoryPassing.value
                                : 0
                        ) || 0
                        : 0;

                const oralPass =
                    hasOral
                    &&
                    hasOral.checked
                        ? parseFloat(
                            oralPassing
                                ? oralPassing.value
                                : 0
                        ) || 0
                        : 0;

                const practicalPass =
                    hasPractical
                    &&
                    hasPractical.checked
                        ? parseFloat(
                            practicalPassing
                                ? practicalPassing.value
                                : 0
                        ) || 0
                        : 0;

                const totalMax =
                    theory
                    +
                    oral
                    +
                    practical;

                const totalPassing =
                    theoryPass
                    +
                    oralPass
                    +
                    practicalPass;

                if (termTotalMax) {
                    termTotalMax.textContent =
                        formatNumber(
                            totalMax
                        );
                }

                if (termTotalPassing) {
                    termTotalPassing.textContent =
                        formatNumber(
                            totalPassing
                        );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | FORMAT NUMBER
            |--------------------------------------------------------------------------
            */

            function formatNumber(
                value
            )
            {
                const number =
                    parseFloat(
                        value || 0
                    );

                if (
                    Number.isInteger(
                        number
                    )
                ) {
                    return String(
                        number
                    );
                }

                return number.toFixed(
                    2
                ).replace(
                    /\.?0+$/,
                    ''
                );
            }


            /*
            |--------------------------------------------------------------------------
            | SUBJECT PASSING MARKS
            |--------------------------------------------------------------------------
            */

            function updateSubjectPassingMarks()
            {
                const percentage =
                    getPassingPercentage();

                document
                    .querySelectorAll(
                        '.subject-max-input'
                    )
                    .forEach(
                        function (input) {

                            if (
                                input.type === 'hidden'
                            ) {
                                return;
                            }

                            const row =
                                input.closest(
                                    'tr'
                                );

                            if (!row) {
                                return;
                            }

                            const passingInput =
                                row.querySelector(
                                    '.subject-passing-input'
                                );

                            if (!passingInput) {
                                return;
                            }

                            const max =
                                parseFloat(
                                    input.value || 0
                                );

                            if (max > 0) {

                                passingInput.value =
                                    Math.ceil(
                                        max
                                        *
                                        percentage
                                        /
                                        100
                                    );
                            } else {

                                passingInput.value =
                                    0;
                            }
                        }
                    );
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

                let html = '';

                values.forEach(
                    function (value) {

                        html +=
                            '<option value="'
                            +
                            value
                            +
                            '" '
                            +
                            (
                                selected ===
                                value
                                    ? 'selected'
                                    : ''
                            )
                            +
                            '>'
                            +
                            value
                            +
                            '</option>';

                    }
                );


                if (
                    selected > 0
                    &&
                    !values.includes(
                        selected
                    )
                ) {

                    html +=
                        '<option value="'
                        +
                        selected
                        +
                        '" selected>'
                        +
                        selected
                        +
                        '</option>';
                }

                return html;
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

                    tableBody.innerHTML =
                        '<tr>'
                        +
                        '<td colspan="5" '
                        +
                        'style="padding:20px;'
                        +
                        'text-align:center;'
                        +
                        'color:#6B7280;">'
                        +
                        'No subjects are mapped to this Standard.'
                        +
                        '</td>'
                        +
                        '</tr>';

                    return;
                }


                const isTerm =
                    isTermExamSelected();

                let html = '';


                subjects.forEach(
                    function (
                        subject,
                        index
                    ) {

                        const subjectId =
                            parseInt(
                                subject.subject_id
                                ??
                                subject.id
                                ??
                                0,
                                10
                            );

                        const subjectName =
                            subject.subject_name
                            ??
                            '';

                        const subjectCode =
                            subject.subject_code
                            ??
                            '';

                        const shortName =
                            subject.short_name
                            ??
                            '';

                        const isOptional =
                            parseInt(
                                subject.is_optional
                                ??
                                0,
                                10
                            ) === 1;

                        const maxMarks =
                            40;

                        const passingMarks =
                            calculatePassing(
                                maxMarks
                            );

                        const typeHtml =
                            isOptional
                                ? '<span style="'
                                  +
                                  'color:#92400E;'
                                  +
                                  'font-weight:700;">'
                                  +
                                  'Optional'
                                  +
                                  '</span>'
                                : '<span style="'
                                  +
                                  'color:#1E40AF;'
                                  +
                                  'font-weight:700;">'
                                  +
                                  'Compulsory'
                                  +
                                  '</span>';


                        let codeHtml = '';

                        if (
                            subjectCode
                            ||
                            shortName
                        ) {

                            codeHtml =
                                '<div class="subject-code">'
                                +
                                (
                                    subjectCode
                                        ? 'Code: '
                                          +
                                          escapeHtml(
                                              subjectCode
                                          )
                                        : ''
                                )
                                +
                                (
                                    subjectCode
                                    &&
                                    shortName
                                        ? ' &nbsp;|&nbsp; '
                                        : ''
                                )
                                +
                                (
                                    shortName
                                        ? 'Short: '
                                          +
                                          escapeHtml(
                                              shortName
                                          )
                                        : ''
                                )
                                +
                                '</div>';
                        }


                        const maxField =
                            isTerm

                                ? '<span style="'
                                  +
                                  'color:#1E40AF;'
                                  +
                                  'font-weight:700;">'
                                  +
                                  'Uses Term Components'
                                  +
                                  '</span>'
                                  +
                                  '<input type="hidden" '
                                  +
                                  'name="subjects['
                                  +
                                  subjectId
                                  +
                                  '][max_marks]" '
                                  +
                                  'value="'
                                  +
                                  maxMarks
                                  +
                                  '">'

                                : '<select '
                                  +
                                  'name="subjects['
                                  +
                                  subjectId
                                  +
                                  '][max_marks]" '
                                  +
                                  'class="erp-select '
                                  +
                                  'subject-max-input">'
                                  +
                                  buildMaxMarksOptions(
                                      maxMarks
                                  )
                                  +
                                  '</select>';


                        const passingField =
                            isTerm

                                ? '<span style="'
                                  +
                                  'color:#1E40AF;'
                                  +
                                  'font-weight:700;">'
                                  +
                                  'Uses Term Components'
                                  +
                                  '</span>'
                                  +
                                  '<input type="hidden" '
                                  +
                                  'name="subjects['
                                  +
                                  subjectId
                                  +
                                  '][passing_marks]" '
                                  +
                                  'value="'
                                  +
                                  passingMarks
                                  +
                                  '">'

                                : '<input type="number" '
                                  +
                                  'name="subjects['
                                  +
                                  subjectId
                                  +
                                  '][passing_marks]" '
                                  +
                                  'value="'
                                  +
                                  passingMarks
                                  +
                                  '" readonly '
                                  +
                                  'class="erp-input '
                                  +
                                  'readonly-input '
                                  +
                                  'subject-passing-input">';


                        html +=
                            '<tr>'

                            +

                            '<td class="text-center">'
                            +
                            (
                                index + 1
                            )
                            +
                            '</td>'

                            +

                            '<td>'

                            +

                            '<div class="subject-name">'
                            +
                            escapeHtml(
                                subjectName
                            )
                            +
                            (
                                isOptional
                                    ? '<span class="optional-badge">'
                                      +
                                      'OPTIONAL'
                                      +
                                      '</span>'
                                    : '<span class="required-badge">'
                                      +
                                      'COMPULSORY'
                                      +
                                      '</span>'
                            )
                            +
                            '</div>'

                            +

                            codeHtml

                            +

                            '<input type="hidden" '
                            +
                            'name="subjects['
                            +
                            subjectId
                            +
                            '][subject_id]" '
                            +
                            'value="'
                            +
                            subjectId
                            +
                            '">'

                            +

                            '<input type="hidden" '
                            +
                            'name="subjects['
                            +
                            subjectId
                            +
                            '][display_order]" '
                            +
                            'value="'
                            +
                            (
                                index + 1
                            )
                            +
                            '">'

                            +

                            '</td>'

                            +

                            '<td class="text-center">'
                            +
                            maxField
                            +
                            '</td>'

                            +

                            '<td class="text-center">'
                            +
                            passingField
                            +
                            '</td>'

                            +

                            '<td class="text-center">'
                            +
                            typeHtml
                            +
                            '</td>'

                            +

                            '</tr>';
                    }
                );


                tableBody.innerHTML =
                    html;


                bindSubjectMaxFields();

                updateSubjectPassingMarks();
            }


            /*
            |--------------------------------------------------------------------------
            | SUBJECT MAX FIELD BINDING
            |--------------------------------------------------------------------------
            */

            function bindSubjectMaxFields()
            {
                document
                    .querySelectorAll(
                        '.subject-max-input'
                    )
                    .forEach(
                        function (
                            input
                        ) {

                            input.addEventListener(
                                'change',
                                function () {

                                    const row =
                                        input.closest(
                                            'tr'
                                        );

                                    if (!row) {
                                        return;
                                    }

                                    const passingInput =
                                        row.querySelector(
                                            '.subject-passing-input'
                                        );

                                    if (!passingInput) {
                                        return;
                                    }

                                    const max =
                                        parseFloat(
                                            input.value
                                            || 0
                                        );

                                    passingInput.value =
                                        Math.ceil(
                                            max
                                            *
                                            getPassingPercentage()
                                            /
                                            100
                                        );
                                }
                            );
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
            | LOAD SUBJECTS FROM SERVER
            |--------------------------------------------------------------------------
            */

            async function loadSubjects(
                standardId
            )
            {
                if (
                    !standardId
                ) {

                    tableBody.innerHTML =
                        '<tr>'
                        +
                        '<td colspan="5" '
                        +
                        'style="padding:15px;'
                        +
                        'text-align:center;'
                        +
                        'color:#6B7280;">'
                        +
                        'Select Standard First'
                        +
                        '</td>'
                        +
                        '</tr>';

                    return;
                }


                if (loading) {
                    loading.style.display =
                        'inline';
                }


                tableBody.innerHTML =
                    '<tr>'
                    +
                    '<td colspan="5" '
                    +
                    'style="padding:15px;'
                    +
                    'text-align:center;">'
                    +
                    'Loading subjects...'
                    +
                    '</td>'
                    +
                    '</tr>';


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


                    renderSubjects(
                        subjects
                    );

                } catch (
                    error
                ) {

                    console.error(
                        'Unable to load subjects:',
                        error
                    );


                    tableBody.innerHTML =
                        '<tr>'
                        +
                        '<td colspan="5" '
                        +
                        'style="padding:15px;'
                        +
                        'text-align:center;'
                        +
                        'color:#DC2626;">'
                        +
                        'Unable to load subjects.'
                        +
                        '</td>'
                        +
                        '</tr>';

                } finally {

                    if (loading) {
                        loading.style.display =
                            'none';
                    }
                }
            }


            /*
            |--------------------------------------------------------------------------
            | STANDARD CHANGE
            |--------------------------------------------------------------------------
            */

            standard.addEventListener(
                'change',
                function () {

                    buildExamName();

                    updatePassingPercentageNote();

                    /*
                    | Reload the authoritative subjects for
                    | the newly selected Standard.
                    */

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

                    updateTermConfiguration();

                    if (
                        isTermExamSelected()
                    ) {

                        /*
                        | Existing subject rows stay in place.
                        | Only their display mode changes.
                        */

                        document
                            .querySelectorAll(
                                '.subject-max-input'
                            )
                            .forEach(
                                function (
                                    input
                                ) {

                                    if (
                                        input.type !==
                                        'hidden'
                                    ) {
                                        input.disabled =
                                            true;
                                    }
                                }
                            );

                    } else {

                        document
                            .querySelectorAll(
                                '.subject-max-input'
                            )
                            .forEach(
                                function (
                                    input
                                ) {

                                    input.disabled =
                                        false;
                                }
                            );

                        updateSubjectPassingMarks();
                    }
                }
            );


            /*
            |--------------------------------------------------------------------------
            | TERM CHECKBOXES
            |--------------------------------------------------------------------------
            */

            if (hasTheory) {

                hasTheory.addEventListener(
                    'change',
                    function () {

                        theoryFields.style.display =
                            this.checked
                                ? ''
                                : 'none';

                        if (
                            !this.checked
                            &&
                            theoryMax
                        ) {
                            theoryMax.value =
                                0;
                        }

                        if (
                            !this.checked
                            &&
                            theoryPassing
                        ) {
                            theoryPassing.value =
                                0;
                        }

                        updateTermPassingMarks();
                    }
                );
            }


            if (hasOral) {

                hasOral.addEventListener(
                    'change',
                    function () {

                        oralFields.style.display =
                            this.checked
                                ? ''
                                : 'none';

                        if (
                            !this.checked
                            &&
                            oralMax
                        ) {
                            oralMax.value =
                                0;
                        }

                        if (
                            !this.checked
                            &&
                            oralPassing
                        ) {
                            oralPassing.value =
                                0;
                        }

                        updateTermPassingMarks();
                    }
                );
            }


            if (hasPractical) {

                hasPractical.addEventListener(
                    'change',
                    function () {

                        practicalFields.style.display =
                            this.checked
                                ? ''
                                : 'none';

                        if (
                            !this.checked
                            &&
                            practicalMax
                        ) {
                            practicalMax.value =
                                0;
                        }

                        if (
                            !this.checked
                            &&
                            practicalPassing
                        ) {
                            practicalPassing.value =
                                0;
                        }

                        updateTermPassingMarks();
                    }
                );
            }


            /*
            |--------------------------------------------------------------------------
            | TERM MAX MARKS INPUTS
            |--------------------------------------------------------------------------
            */

            [
                theoryMax,
                oralMax,
                practicalMax
            ]
            .forEach(
                function (
                    input
                ) {

                    if (!input) {
                        return;
                    }

                    input.addEventListener(
                        'input',
                        function () {
                            updateTermPassingMarks();
                        }
                    );
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

                    buildExamName();

                    /*
                    |--------------------------------------------------------------
                    | TERM VALIDATION
                    |--------------------------------------------------------------
                    */

                    if (
                        isTermExamSelected()
                    ) {

                        const theorySelected =
                            hasTheory
                            &&
                            hasTheory.checked;

                        const oralSelected =
                            hasOral
                            &&
                            hasOral.checked;

                        const practicalSelected =
                            hasPractical
                            &&
                            hasPractical.checked;

                        if (
                            !theorySelected
                            &&
                            !oralSelected
                            &&
                            !practicalSelected
                        ) {

                            event.preventDefault();

                            alert(
                                'For Term 1 / Term 2, select at least one component: Theory, Oral or Practical.'
                            );

                            return;
                        }


                        if (
                            theorySelected
                            &&
                            (
                                !theoryMax
                                ||
                                parseFloat(
                                    theoryMax.value
                                    || 0
                                ) <= 0
                            )
                        ) {

                            event.preventDefault();

                            alert(
                                'Theory Max Marks must be greater than zero.'
                            );

                            theoryMax.focus();

                            return;
                        }


                        if (
                            oralSelected
                            &&
                            (
                                !oralMax
                                ||
                                parseFloat(
                                    oralMax.value
                                    || 0
                                ) <= 0
                            )
                        ) {

                            event.preventDefault();

                            alert(
                                'Oral Max Marks must be greater than zero.'
                            );

                            oralMax.focus();

                            return;
                        }


                        if (
                            practicalSelected
                            &&
                            (
                                !practicalMax
                                ||
                                parseFloat(
                                    practicalMax.value
                                    || 0
                                ) <= 0
                            )
                        ) {

                            event.preventDefault();

                            alert(
                                'Practical Max Marks must be greater than zero.'
                            );

                            practicalMax.focus();

                            return;
                        }

                        updateTermPassingMarks();
                    }


                    /*
                    |--------------------------------------------------------------
                    | DISABLE ONLY VISUAL TERM SUBJECT MAX SELECTS
                    |--------------------------------------------------------------
                    |
                    | Hidden values are already submitted.
                    |
                    */

                    document
                        .querySelectorAll(
                            '.subject-max-input'
                        )
                        .forEach(
                            function (
                                input
                            ) {

                                if (
                                    input.type !==
                                    'hidden'
                                ) {

                                    input.disabled =
                                        false;
                                }
                            }
                        );
                }
            );


            /*
            |--------------------------------------------------------------------------
            | INITIALIZATION
            |--------------------------------------------------------------------------
            */

            buildExamName();

            updatePassingPercentageNote();

            updateTermConfiguration();

            bindSubjectMaxFields();

            updateSubjectPassingMarks();

        }
    );

</script>


</x-app-layout>
