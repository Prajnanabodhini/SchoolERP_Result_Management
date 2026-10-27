<x-app-layout>

@php
    $H = \App\Helpers\MarksEntryBladeHelper::class;

    $flags = $H::resolveComponentFlags(
        $showTheory    ?? null,
        $showOral      ?? null,
        $showPractical ?? null,
        $existingMarks ?? null
    );
    $showTheory    = $flags['theory'];
    $showOral      = $flags['oral'];
    $showPractical = $flags['practical'];

    $showOptionalColumn = $H::shouldShowOptionalColumn(
        $isOptionalEnabled ?? false,
        $H::getSelectedStandardId(
            $selectedClassAllocation ?? null,
            $teacherSubjectAllocation ?? null
        )
    );

    $entryRows = $H::prepareEntryRows(
        $students ?? [],
        $existingMarks ?? [],
        (bool) ($isOptionalEnabled ?? false),
        (bool) ($marksLocked ?? false)
    );

    $marksEntryParams = array_filter([
        'exam_master_id'                => request('exam_master_id'),
        'teacher_subject_allocation_id' => request('teacher_subject_allocation_id'),
    ], fn ($v) => $v !== null && $v !== '');

    $marksEntryUrl = route('marks-entry.index') . $H::querySuffix($marksEntryParams);

    $viewMarksParams = array_filter([
        'exam_master_id'                => request('exam_master_id'),
        'standard_id'                   => optional(optional($teacherSubjectAllocation?->allocation)->standard)->id,
        'division_id'                   => optional(optional($teacherSubjectAllocation?->allocation)->division)->id,
        'subject_id'                    => $teacherSubjectAllocation?->subject_id,
        'teacher_subject_allocation_id' => request('teacher_subject_allocation_id'),
    ], fn ($v) => $v !== null && $v !== '');

    $viewMarksUrl = route('marks-entry.view') . $H::querySuffix($viewMarksParams);

    $selectedExamId = request('exam_master_id');
    $selectedTsaId  = request('teacher_subject_allocation_id');
@endphp

<style>
    /* =========================================================
       PAGE
       ========================================================= */
    .marks-entry-page,
    .marks-entry-page * {
        box-sizing: border-box;
        font-family: Arial, Helvetica, sans-serif !important;
    }

    .marks-entry-page {
        background: #f0f0f0;
        padding: 20px;
    }

    .marks-entry-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        padding: 18px 20px;
        max-width: 100%;
    }

    .marks-entry-title {
        margin: 0 0 14px;
        font-size: 21px;
        font-weight: 700;
        color: #1D4ED8;
        letter-spacing: 0.4px;
    }

    /* =========================================================
       FILTER ROW
       ========================================================= */
    .filter-row {
        display: flex;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 10px;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .filter-label {
        font-size: 11px;
        font-weight: 700;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .filter-select-wrapper {
        position: relative;
        display: inline-block;
    }

    .filter-select {
        height: 32px;
        padding: 3px 28px 3px 10px;
        border: 1px solid #9CA3AF;
        border-radius: 6px;
        background: #fff;
        color: #111827;
        font-size: 12px;
        font-weight: 600;
        outline: none;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        transition: border-color .15s, box-shadow .15s;
    }
    .filter-select:focus {
        border-color: #2563EB;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
    }
    .filter-select:disabled {
        background: #F3F4F6;
        color: #6B7280;
        cursor: not-allowed;
    }

    .dropdown-arrow {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 6px solid #6b7280;
        pointer-events: none;
    }

    .academic-year-select { width: 160px; }
    .exam-select          { width: 230px; }
    .assignment-select    { width: 290px; }

    /* =========================================================
       BUTTONS
       ========================================================= */
    .erp-btn {
        min-height: 32px;
        min-width: max-content;
        padding: 0 16px;
        border: 1px solid transparent;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        line-height: 1;
        transition: background .15s, border-color .15s;
    }

    .erp-btn-save {
        background: #16A34A;
        color: #fff;
        border-color: #15803D;
        min-width: 160px;
    }
    .erp-btn-save:hover { background: #15803D; border-color: #166534; }

    .erp-btn-green {
        background: #16A34A;
        color: #fff;
        border-color: #15803D;
        min-width: 160px;
    }
    .erp-btn-green:hover { background: #15803D; border-color: #166534; }
    .erp-btn-green:disabled {
        background: #9CA3AF !important;
        border-color: #6B7280 !important;
        cursor: not-allowed !important;
        opacity: .85;
    }

    /* =========================================================
       ALERT CARDS
       ========================================================= */
    .error-box,
    .warning-box,
    .saved-box,
    .success-box {
        margin-top: 14px;
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.5;
    }
    .error-box {
        background: #fee2e2;
        border: 1px solid #fca5a5;
        color: #991b1b;
    }
    .warning-box {
        background: #fffbeb;
        border: 1px solid #fcd34d;
        color: #92400e;
    }
    .saved-box {
        background: #fffbeb;
        border: 1px solid #fcd34d;
        color: #92400e;
    }
    .success-box {
        background: #dcfce7;
        border: 1px solid #86efac;
        color: #166534;
    }
    .error-box ul { margin: 6px 0 0 20px; padding: 0; }

    /* =========================================================
       SELECTED INFO BANNER
       ========================================================= */
    .selected-info {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 14px;
        padding: 9px 14px;
        border-radius: 8px;
        background: #eff6ff;
        border: 1px solid #93c5fd;
        color: #1e40af;
        font-size: 11.5px;
        font-weight: 600;
    }
    .selected-info-item {
        font-weight: 700;
        color: #1e3a8a;
        text-transform: uppercase;
        font-size: 10px;
        letter-spacing: 0.3px;
    }
    .selected-info-separator { color: #93c5fd; }

    /* =========================================================
       TABLE WRAPPER (No horizontal scrolling)
       ========================================================= */
    .marks-table-wrapper {
        margin-top: 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        overflow-x: hidden; /* Prevents horizontal scrolling */
        max-width: 100%;
    }

    /* =========================================================
       DATA TABLE — IDENTICAL TO VIEW PAGE
       ========================================================= */
    .marks-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        font-size: 11px; /* Matches View page */
        table-layout: fixed; /* Ensures strict column sizing */
    }

    .marks-table thead tr {
        background: #1e3a8a;
    }

    .marks-table th {
        background: #1e3a8a;
        color: #ffffff !important;
        border: 1px solid #1e40af;
        padding: 6px 4px; /* Spacious padding */
        text-align: center;
        line-height: 1.15;
        font-weight: 700;
        font-size: 10px; /* Matches View page */
        text-transform: uppercase;
        letter-spacing: 0.3px;
        white-space: nowrap;
    }

    .marks-table td {
        border: 1px solid #d1d5db;
        padding: 5px 4px; /* Spacious padding */
        white-space: nowrap;
        vertical-align: middle;
        font-size: 11px; /* Matches View page */
        line-height: 1.25;
        color: #111827;
        overflow: hidden;
    }

    .marks-table tbody tr:nth-child(even) { background: #f9fafb; }
    .marks-table tbody tr:hover           { background: #fef9c3; }

    .center { text-align: center; }

    /* =========================================================
       STUDENT NAME — NOWRAP, TRUNCATE WITH ELLIPSIS
       ========================================================= */
    .student-name-cell {
        white-space: nowrap !important;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 11px; /* Matches View page */
        font-weight: 700;
    }

    /* =========================================================
       COLUMN WIDTHS (Calculated precisely to fit 100%)
       ========================================================= */
    .marks-table col.c-gr     { width: 4%;  }
    .marks-table col.c-roll   { width: 4%;  }
    .marks-table col.c-name   { width: 22%; } /* Takes remaining space */
    .marks-table col.c-att    { width: 9%;  }
    .marks-table col.c-opt    { width: 8%;  }
    .marks-table col.c-max    { width: 5%;  }
    .marks-table col.c-pass   { width: 5%;  }
    .marks-table col.c-obt    { width: 5%;  }
    .marks-table col.c-status { width: 6%;  }

    /* =========================================================
       MARK INPUTS
       ========================================================= */
    .mark-input {
        width: 100%;     /* Fills the 5% column width */
        height: 26px;
        padding: 1px 2px;
        border: 1px solid #9CA3AF;
        border-radius: 3px;
        text-align: center;
        font-size: 11px;
        font-weight: 600;
        -moz-appearance: textfield;
        appearance: textfield;
        transition: border-color .15s, box-shadow .15s;
        margin: 0 auto;
        display: block;
    }
    .mark-input::-webkit-outer-spin-button,
    .mark-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .mark-input:focus {
        outline: none;
        border-color: #2563EB;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
    }
    .mark-input:read-only {
        background: #F3F4F6;
        color: #6B7280;
        cursor: not-allowed;
    }

    /* =========================================================
       ATTENDANCE / OPTIONAL BUTTONS
       ========================================================= */
    .attendance-btn,
    .optional-btn {
        width: 100%;     /* Fills the 9% / 8% column width */
        padding: 5px 2px;
        border: 1px solid transparent;
        border-radius: 4px;
        color: #fff;
        font-size: 9px; /* Fits the column width */
        font-weight: 700;
        cursor: pointer;
        line-height: 1;
        letter-spacing: 0.2px;
        text-transform: uppercase;
        transition: background .15s;
        margin: 0 auto;
        display: block;
        white-space: nowrap;
    }

    .present-btn        { background: #16A34A; border-color: #15803D; }
    .present-btn:hover  { background: #15803D; }
    .absent-btn         { background: #DC2626; border-color: #B91C1C; }
    .absent-btn:hover   { background: #B91C1C; }
    .optional-btn       { background: #6B7280; border-color: #4B5563; }
    .optional-btn:hover { background: #4B5563; }
    .optional-active-btn       { background: #D97706 !important; border-color: #B45309 !important; }
    .optional-active-btn:hover { background: #B45309 !important; }

    /* =========================================================
       STATUS TEXT
       ========================================================= */
    .status-present  { color: #16A34A; font-weight: 700; font-size: 10px; letter-spacing: 0.3px; }
    .status-absent   { color: #DC2626; font-weight: 700; font-size: 10px; letter-spacing: 0.3px; }
    .status-optional { color: #D97706; font-weight: 700; font-size: 10px; letter-spacing: 0.3px; }

    .optional-header {
        background: #D97706 !important;
        color: #ffffff !important;
        border-color: #B45309 !important;
    }

    .status-cell-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
    }

    /* =========================================================
       READ-ONLY MARK VALUES (For ABSENT/OPTIONAL display)
       ========================================================= */
    .mark-text {
        font-weight: 700;
        font-size: 11px;
        display: none;
    }
    .mark-text.readonly-mark.absent   { color: #DC2626 !important; }
    .mark-text.readonly-mark.optional { color: #D97706 !important; }

    /* =========================================================
       ACTION ROW
       ========================================================= */
    .marks-action-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 14px;
        flex-wrap: wrap;
    }

    .student-count {
        margin-left: auto;
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #93c5fd;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: 0.2px;
    }

    /* =========================================================
       RESPONSIVE
       ========================================================= */
    @media (max-width: 900px) {
        .marks-entry-page { padding: 12px; }
        .marks-entry-card { padding: 14px; }
        .filter-group { width: 100%; }
        .academic-year-select,
        .exam-select,
        .assignment-select { width: 100%; }
        .student-count { margin-left: 0; }
    }
</style>

<div class="erp-page marks-entry-page">

    <div class="marks-entry-card">

        <h2 class="marks-entry-title">Examination Marks</h2>

        {!! $H::renderTabs('entry', $marksEntryUrl, $viewMarksUrl) !!}

        @if(session('success'))
            <div class="success-box">✓ {{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="error-box"><strong>Error:</strong> {{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="error-box">
                <strong>Please correct the following:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(request()->boolean('marks_saved') && !$marksLocked)
            <div class="saved-box">
                ⚠ Marks are saved but <strong>NOT finally submitted</strong>.
                Please click <strong>Submit Final Marks</strong> to complete the submission.
            </div>
        @endif

        @if($marksLocked)
            <div class="warning-box">
                <strong>Marks entry has already been completed and is locked.</strong><br>
                Contact Admin for modification.
            </div>
        @endif

        <form method="GET" action="{{ route('marks-entry.index') }}" id="marksFilterForm">
            <div class="filter-row">

                <div class="filter-group">
                    <label class="filter-label" for="academic_year_id">Academic Year</label>
                    <div class="filter-select-wrapper">
                        <select name="academic_year_id" id="academic_year_id" class="filter-select academic-year-select">
                            <option value="">All Academic Years</option>
                            {!! $H::renderYearOptions($academicYears, request('academic_year_id')) !!}
                        </select>
                        <span class="dropdown-arrow"></span>
                    </div>
                </div>

                <div class="filter-group">
                    <label class="filter-label" for="exam_master_id">Exam</label>
                    <div class="filter-select-wrapper">
                        <select name="exam_master_id" id="exam_master_id" class="filter-select exam-select">
                            <option value="">Select Exam</option>
                            {!! $H::renderExamOptions($exams, $selectedExamId) !!}
                        </select>
                        <span class="dropdown-arrow"></span>
                    </div>
                </div>

                <div class="filter-group">
                    <label class="filter-label" for="teacher_subject_allocation_id">Teaching Assignment</label>
                    <div class="filter-select-wrapper">
                        <select name="teacher_subject_allocation_id" id="teacher_subject_allocation_id"
                                class="filter-select assignment-select"
                                {{ !$selectedExamId ? 'disabled' : '' }}>
                            @if(!$selectedExamId)
                                <option value="">Select Exam First</option>
                            @elseif($assignments->isEmpty())
                                <option value="">No Teaching Assignment</option>
                            @else
                                <option value="">Select Teaching Assignment</option>
                                {!! $H::renderAssignmentOptions($assignments, $selectedTsaId) !!}
                            @endif
                        </select>
                        <span class="dropdown-arrow"></span>
                    </div>
                </div>

                <div class="filter-group">
                    <button type="submit" class="erp-btn erp-btn-save" id="loadStudentsButton">
                        Load Students
                    </button>
                </div>
            </div>
        </form>

        @if($teacherSubjectAllocation && $exam)
            @php
                $teacherName  = optional(optional($teacherSubjectAllocation->allocation)->teacher)->name ?? 'Teacher';
                $subjectName  = optional($teacherSubjectAllocation->subject)->subject_name ?? 'Subject';
                $standardName = optional(optional($teacherSubjectAllocation->allocation)->standard)->standard_name ?? '';
                $divisionName = optional(optional($teacherSubjectAllocation->allocation)->division)->division_name ?? '';
            @endphp

            <div class="selected-info">
                <span><span class="selected-info-item">Teacher:</span> {{ $teacherName }}</span>
                <span class="selected-info-separator">|</span>
                <span><span class="selected-info-item">Subject:</span> {{ $subjectName }}</span>
                <span class="selected-info-separator">|</span>
                <span>
                    <span class="selected-info-item">Class:</span>
                    {{ $standardName }}@if($divisionName) - {{ $divisionName }}@endif
                </span>
                <span class="selected-info-separator">|</span>
                <span><span class="selected-info-item">Exam:</span> {{ $exam->display_exam_name ?? $exam->exam_name }}</span>
                <span class="selected-info-separator">|</span>
                <span><span class="selected-info-item">Students:</span> {{ $entryRows->count() }}</span>

                @if($showOptionalColumn)
                    <span class="selected-info-separator">|</span>
                    <span style="color:#b45309;font-weight:700;">Optional Selection: Enabled</span>
                @endif
            </div>
        @endif

        @if($entryRows->count() > 0)
            <div class="marks-table-wrapper">

                <form method="POST" action="{{ route('marks-entry.save') }}" id="marksSaveForm">
                    @csrf
                    <input type="hidden" name="academic_year_id" value="{{ request('academic_year_id') }}">
                    <input type="hidden" name="exam_master_id" value="{{ $selectedExamId }}">
                    <input type="hidden" name="teacher_subject_allocation_id" value="{{ $selectedTsaId }}">

                    <table class="marks-table">
                        <colgroup>
                            <col class="c-gr">
                            <col class="c-roll">
                            <col class="c-name">
                            <col class="c-att">
                            @if($showOptionalColumn) <col class="c-opt"> @endif
                            @if($showTheory)
                                <col class="c-max"> <col class="c-pass"> <col class="c-obt">
                            @endif
                            @if($showOral)
                                <col class="c-max"> <col class="c-pass"> <col class="c-obt">
                            @endif
                            @if($showPractical)
                                <col class="c-max"> <col class="c-pass"> <col class="c-obt">
                            @endif
                            <col class="c-status">
                        </colgroup>

                        <thead>
                            <tr>
                                <th>GR<br>No</th>
                                <th>Roll<br>No</th>
                                <th>Student Name</th>
                                <th>Atten-<br>dance</th>

                                @if($showOptionalColumn)
                                    <th class="optional-header">Opt-<br>ional</th>
                                @endif

                                @if($showTheory)
                                    <th>Theory<br>Max</th>
                                    <th>Theory<br>Pass</th>
                                    <th class="th-obtained">Theory<br>Obt.</th>
                                @endif

                                @if($showOral)
                                    <th>Oral<br>Max</th>
                                    <th>Oral<br>Pass</th>
                                    <th class="th-obtained">Oral<br>Obt.</th>
                                @endif

                                @if($showPractical)
                                    <th>Pract.<br>Max</th>
                                    <th>Pract.<br>Pass</th>
                                    <th class="th-obtained">Pract.<br>Obt.</th>
                                @endif

                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                        @foreach($entryRows as $row)
                            @php $sid = $row['student_id']; @endphp

                            <tr>
                                <td class="center">
                                    {{ $row['gr_no'] }}
                                    <input type="hidden" name="student_ids[]" value="{{ $sid }}">
                                </td>

                                <td class="center">{{ $row['roll_no'] }}</td>

                                <td class="student-name-cell" title="{{ $row['full_name'] }}">{{ $row['full_name'] ?: '-' }}</td>

                                <td class="center">
                                    <input type="hidden"
                                           name="is_absent[{{ $sid }}]"
                                           id="absent_{{ $sid }}"
                                           value="{{ $row['is_absent'] ? 1 : 0 }}">

                                    @if(!$marksLocked)
                                        <button type="button"
                                                id="btn_{{ $sid }}"
                                                class="attendance-btn {{ $row['is_absent'] ? 'absent-btn' : 'present-btn' }}"
                                                onclick="toggleAbsent('{{ $sid }}', event)">
                                            {{ $row['is_absent'] ? 'ABSENT' : 'PRESENT' }}
                                        </button>
                                    @else
                                        <span class="{{ $row['is_absent'] ? 'status-absent' : 'status-present' }}">
                                            {{ $row['is_absent'] ? 'ABSENT' : 'PRESENT' }}
                                        </span>
                                    @endif
                                </td>

                                @if($showOptionalColumn)
                                    <td class="center">
                                        <input type="hidden"
                                               name="is_optional[{{ $sid }}]"
                                               id="optional_{{ $sid }}"
                                               value="{{ $row['is_optional'] ? 1 : 0 }}">

                                        @if(!$marksLocked)
                                            <button type="button"
                                                    id="optional_btn_{{ $sid }}"
                                                    class="optional-btn {{ $row['is_optional'] ? 'optional-active-btn' : '' }}"
                                                    onclick="toggleOptional('{{ $sid }}', event)">
                                                {{ $row['is_optional'] ? 'OPTIONAL' : 'NORMAL' }}
                                            </button>
                                        @else
                                            <span class="{{ $row['is_optional'] ? 'status-optional' : 'status-present' }}">
                                                {{ $row['is_optional'] ? 'OPTIONAL' : 'NORMAL' }}
                                            </span>
                                        @endif
                                    </td>
                                @endif

                                @if($showTheory)
                                    <td class="center">{{ (int) $theoryMaxMarks }}</td>
                                    <td class="center">{{ (int) $theoryPassingMarks }}</td>
                                    <td class="center">
                                        <input type="number"
                                               name="theory_marks[{{ $sid }}]"
                                               value="{{ $row['theory_value'] }}"
                                               min="0" max="{{ (int) $theoryMaxMarks }}" step="1"
                                               class="mark-input student-{{ $sid }}"
                                               {{ $row['marks_readonly'] ? 'readonly' : '' }}
                                               {{ !$row['marks_readonly'] ? 'required' : '' }}>
                                    </td>
                                @endif

                                @if($showOral)
                                    <td class="center">{{ (int) $oralMaxMarks }}</td>
                                    <td class="center">{{ (int) $oralPassingMarks }}</td>
                                    <td class="center">
                                        <input type="number"
                                               name="oral_marks[{{ $sid }}]"
                                               value="{{ $row['oral_value'] }}"
                                               min="0" max="{{ (int) $oralMaxMarks }}" step="1"
                                               class="mark-input student-{{ $sid }}"
                                               {{ $row['marks_readonly'] ? 'readonly' : '' }}
                                               {{ !$row['marks_readonly'] ? 'required' : '' }}>
                                    </td>
                                @endif

                                @if($showPractical)
                                    <td class="center">{{ (int) $practicalMaxMarks }}</td>
                                    <td class="center">{{ (int) $practicalPassingMarks }}</td>
                                    <td class="center">
                                        <input type="number"
                                               name="practical_marks[{{ $sid }}]"
                                               value="{{ $row['practical_value'] }}"
                                               min="0" max="{{ (int) $practicalMaxMarks }}" step="1"
                                               class="mark-input student-{{ $sid }}"
                                               {{ $row['marks_readonly'] ? 'readonly' : '' }}
                                               {{ !$row['marks_readonly'] ? 'required' : '' }}>
                                    </td>
                                @endif

                                <td class="center" id="status_{{ $sid }}">
                                    <div class="status-cell-wrapper">
                                        @if($row['is_optional'])
                                            <span class="status-optional">OPTIONAL</span>
                                        @elseif($row['is_absent'])
                                            <span class="status-absent">ABSENT</span>
                                        @else
                                            <span class="status-present">PRESENT</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    <div class="marks-action-row">
                        @if(!$marksLocked)
                            <button type="submit" class="erp-btn erp-btn-save" id="saveMarksButton">
                                Save Marks
                            </button>
                        @endif

                        <button type="button"
                                class="erp-btn erp-btn-green"
                                id="submitFinalButton"
                                {{ $marksLocked ? 'disabled' : '' }}>
                            {{ $marksLocked ? 'Marks Submitted' : 'Submit Final Marks' }}
                        </button>

                        <span class="student-count">{{ $entryRows->count() }} Students</span>
                    </div>
                </form>

                @if($teacherSubjectAllocation)
                    <form method="POST" action="{{ route('marks-entry.submit') }}"
                          id="finalSubmitForm" style="display:none;">
                        @csrf
                        <input type="hidden" name="academic_year_id" value="{{ request('academic_year_id') }}">
                        <input type="hidden" name="exam_master_id" value="{{ $selectedExamId }}">
                        <input type="hidden" name="teacher_subject_allocation_id" value="{{ $selectedTsaId }}">
                    </form>
                @endif

            </div>

        @elseif(request()->filled('teacher_subject_allocation_id') && !$marksLocked)
            <div class="warning-box">
                No students found for the selected teaching assignment.
                Please verify the Old ERP student mapping.
            </div>
        @endif

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
(function () {
    'use strict';

    function getElement(id) { return document.getElementById(id); }
    function getStudentInputs(studentId) { return document.querySelectorAll('.student-' + studentId); }

    /* =========================================================
       VISUAL SWAP HELPERS
       ========================================================= */
    function showMarkAsText(input, textToShow, className) {
        const parent = input.parentNode;
        let span = parent.querySelector('.mark-text');
        if (!span) {
            span = document.createElement('span');
            span.className = 'mark-text';
            parent.appendChild(span);
        }
        span.textContent = textToShow;
        span.className = 'mark-text ' + className;
        span.style.display = 'block';
        input.style.display = 'none';
    }

    function showMarkAsInput(input) {
        const parent = input.parentNode;
        const span = parent.querySelector('.mark-text');
        if (span) { span.style.display = 'none'; }
        input.style.display = 'block';
        if (input.value === '0') input.value = '';
        input.readOnly = false;
        input.required = true;
        input.style.background = '';
        input.style.border = '1px solid #9ca3af';
    }

    /* =========================================================
       STATE HANDLERS
       ========================================================= */
    function setInputEnabled(input) {
        input.readOnly = false;
        input.required = true;
        if (input.value === '0') { input.value = ''; }
        input.style.background = '';
        input.style.border = '1px solid #9ca3af';
        showMarkAsInput(input);
    }

    function updateAttendanceButton(studentId, isAbsent) {
        const button = getElement('btn_' + studentId);
        if (!button) { return; }
        button.type = 'button';
        if (isAbsent) {
            button.textContent = 'ABSENT';
            button.classList.remove('present-btn');
            button.classList.add('absent-btn');
        } else {
            button.textContent = 'PRESENT';
            button.classList.remove('absent-btn');
            button.classList.add('present-btn');
        }
    }

    function updateOptionalButton(studentId, isOptional) {
        const button = getElement('optional_btn_' + studentId);
        if (!button) { return; }
        button.type = 'button';
        if (isOptional) {
            button.textContent = 'OPTIONAL';
            button.classList.add('optional-active-btn');
        } else {
            button.textContent = 'NORMAL';
            button.classList.remove('optional-active-btn');
        }
    }

    function updateStatus(studentId, statusType) {
        const status = getElement('status_' + studentId);
        if (!status) { return; }
        if (statusType === 'ABSENT') {
            status.innerHTML = '<div class="status-cell-wrapper"><span class="status-absent">ABSENT</span></div>';
        } else if (statusType === 'OPTIONAL') {
            status.innerHTML = '<div class="status-cell-wrapper"><span class="status-optional">OPTIONAL</span></div>';
        } else {
            status.innerHTML = '<div class="status-cell-wrapper"><span class="status-present">PRESENT</span></div>';
        }
    }

    function setPresentState(studentId) {
        const absentFlag = getElement('absent_' + studentId);
        const optionalFlag = getElement('optional_' + studentId);
        if (!absentFlag) { return; }
        absentFlag.value = '0';
        if (optionalFlag) { optionalFlag.value = '0'; }
        updateAttendanceButton(studentId, false);
        updateOptionalButton(studentId, false);
        updateStatus(studentId, 'PRESENT');
        getStudentInputs(studentId).forEach(setInputEnabled);
    }

    function setAbsentState(studentId) {
        const absentFlag = getElement('absent_' + studentId);
        const optionalFlag = getElement('optional_' + studentId);
        if (!absentFlag) { return; }
        absentFlag.value = '1';
        if (optionalFlag) { optionalFlag.value = '0'; }
        updateAttendanceButton(studentId, true);
        updateOptionalButton(studentId, false);
        updateStatus(studentId, 'ABSENT');
        getStudentInputs(studentId).forEach(function (input) {
            input.value = '0';
            input.readOnly = true;
            input.required = false;
            input.style.background = '#fee2e2';
            input.style.border = '1px solid #fca5a5';
            showMarkAsText(input, 'AB', 'readonly-mark absent');
        });
    }

    function setOptionalState(studentId) {
        const absentFlag = getElement('absent_' + studentId);
        const optionalFlag = getElement('optional_' + studentId);
        if (!optionalFlag) { return; }
        if (absentFlag) { absentFlag.value = '0'; }
        optionalFlag.value = '1';
        updateAttendanceButton(studentId, false);
        updateOptionalButton(studentId, true);
        updateStatus(studentId, 'OPTIONAL');
        getStudentInputs(studentId).forEach(function (input) {
            input.value = '0';
            input.readOnly = true;
            input.required = false;
            input.style.background = '#fff7ed';
            input.style.border = '1px solid #f59e0b';
            showMarkAsText(input, 'OPT', 'readonly-mark optional');
        });
    }

    function setNormalState(studentId) {
        const absentFlag = getElement('absent_' + studentId);
        const optionalFlag = getElement('optional_' + studentId);
        if (absentFlag) { absentFlag.value = '0'; }
        if (optionalFlag) { optionalFlag.value = '0'; }
        updateAttendanceButton(studentId, false);
        updateOptionalButton(studentId, false);
        updateStatus(studentId, 'PRESENT');
        getStudentInputs(studentId).forEach(setInputEnabled);
    }

    window.toggleAbsent = function (studentId, event) {
        if (event) { event.preventDefault(); event.stopPropagation(); }

        const absentFlag = getElement('absent_' + studentId);
        const optionalFlag = getElement('optional_' + studentId);
        if (!absentFlag) { return; }

        if (optionalFlag && optionalFlag.value === '1') {
            Swal.fire({
                icon: 'question',
                title: 'Student is Optional',
                text: 'Remove Optional status before changing Attendance?',
                showCancelButton: true,
                confirmButtonText: 'Yes, Continue',
                cancelButtonText: 'Cancel'
            }).then(function (result) {
                if (!result.isConfirmed) { return; }
                setNormalState(studentId);
                Swal.fire({
                    icon: 'warning',
                    title: 'Confirm Absent',
                    text: 'Student will be marked ABSENT and all marks will become 0.',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Mark Absent',
                    cancelButtonText: 'Cancel'
                }).then(function (secondResult) {
                    if (secondResult.isConfirmed) { setAbsentState(studentId); }
                });
            });
            return;
        }

        if (absentFlag.value === '0') {
            Swal.fire({
                icon: 'warning',
                title: 'Confirm Absent',
                text: 'Student will be marked ABSENT and all marks will become 0.',
                showCancelButton: true,
                confirmButtonText: 'Yes, Mark Absent',
                cancelButtonText: 'Cancel'
            }).then(function (result) {
                if (result.isConfirmed) { setAbsentState(studentId); }
            });
            return;
        }

        Swal.fire({
            icon: 'question',
            title: 'Confirm Present',
            text: 'Change student status back to PRESENT?',
            showCancelButton: true,
            confirmButtonText: 'Yes, Present',
            cancelButtonText: 'Cancel'
        }).then(function (result) {
            if (result.isConfirmed) { setPresentState(studentId); }
        });
    };

    window.toggleOptional = function (studentId, event) {
        if (event) { event.preventDefault(); event.stopPropagation(); }

        const optionalFlag = getElement('optional_' + studentId);
        const absentFlag   = getElement('absent_' + studentId);
        if (!optionalFlag) { return; }

        if (absentFlag && absentFlag.value === '1') {
            Swal.fire({
                icon: 'warning',
                title: 'Student is Absent',
                text: 'An absent student cannot be marked as Optional.',
                confirmButtonText: 'OK'
            });
            return;
        }

        if (optionalFlag.value === '0') {
            Swal.fire({
                icon: 'warning',
                title: 'Mark Student Optional?',
                text: 'This student will be excluded from marks calculation for this subject.',
                showCancelButton: true,
                confirmButtonText: 'Yes, Optional',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#d97706'
            }).then(function (result) {
                if (result.isConfirmed) { setOptionalState(studentId); }
            });
            return;
        }

        Swal.fire({
            icon: 'question',
            title: 'Remove Optional Status?',
            text: 'This student will become a normal PRESENT student.',
            showCancelButton: true,
            confirmButtonText: 'Yes, Normal',
            cancelButtonText: 'Cancel'
        }).then(function (result) {
            if (result.isConfirmed) { setNormalState(studentId); }
        });
    };

    function validateMarksForm() {
        const rows = document.querySelectorAll('#marksSaveForm tbody tr');
        let hasError = false;
        rows.forEach(function (row) {
            const optionalField = row.querySelector('input[name^="is_optional["]');
            const absentField   = row.querySelector('input[name^="is_absent["]');
            const isOptional = optionalField && optionalField.value === '1';
            const isAbsent   = absentField   && absentField.value === '1';
            if (isOptional || isAbsent) { return; }

            row.querySelectorAll('.mark-input').forEach(function (input) {
                if (input.readOnly || input.style.display === 'none') { return; }
                if (input.value.trim() === '') {
                    hasError = true;
                    input.style.border = '2px solid #dc2626';
                }
            });
        });
        return !hasError;
    }

    function setupMarkValidation() {
        document.querySelectorAll('.mark-input').forEach(function (input) {
            input.addEventListener('input', function () {
                if (this.readOnly || this.style.display === 'none') { return; }
                const max = parseFloat(this.max);
                const value = this.value.trim();
                if (value === '') {
                    this.style.border = '1px solid #9ca3af';
                    return;
                }
                const number = parseFloat(value);
                this.style.border = (Number.isNaN(number) || number < 0 || number > max)
                    ? '2px solid #dc2626'
                    : '1px solid #16a34a';
            });
        });
    }

    function setupSaveForm() {
        const saveForm   = getElement('marksSaveForm');
        const saveButton = getElement('saveMarksButton');
        if (!saveForm) { return; }

        saveForm.addEventListener('submit', function (event) {
            event.preventDefault();

            if (!validateMarksForm()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please enter marks for all present students.',
                    confirmButtonText: 'OK'
                });
                return;
            }

            Swal.fire({
                icon: 'question',
                title: 'Save Marks',
                html:
                    '<div style="text-align:left">' +
                    'Your marks will be saved as a <b>draft</b>.' +
                    '<br><br>' +
                    'You can still edit them before final submission.' +
                    '<br><br>' +
                    'Please verify the marks carefully.' +
                    '<br><br>' +
                    '<b>Continue and save?</b>' +
                    '</div>',
                showCancelButton: true,
                confirmButtonText: 'Yes, Save Marks',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#2563eb'
            }).then(function (result) {
                if (!result.isConfirmed) { return; }

                if (saveButton) {
                    saveButton.disabled = true;
                    saveButton.textContent = 'Saving...';
                }

                HTMLFormElement.prototype.submit.call(saveForm);
            });
        });
    }

    function setupFinalSubmit() {
        const submitFinalButton = getElement('submitFinalButton');
        const finalSubmitForm   = getElement('finalSubmitForm');
        if (!submitFinalButton || !finalSubmitForm) { return; }

        submitFinalButton.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            if (submitFinalButton.disabled) { return; }

            if (!validateMarksForm()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please enter marks for all present students before final submission.',
                    confirmButtonText: 'OK'
                });
                return;
            }

            Swal.fire({
                icon: 'warning',
                title: 'Final Marks Submission',
                html:
                    '<div style="text-align:left">' +
                    '<b>This is the FINAL submission of marks.</b><br><br>' +
                    'Please check all marks carefully.<br><br>' +
                    'After final submission:' +
                    '<ul style="margin-top:8px;margin-left:20px;">' +
                    '<li>Marks will be locked.</li>' +
                    '<li>Teacher cannot modify the marks.</li>' +
                    '<li>Administrator intervention will be required for corrections.</li>' +
                    '</ul></div>',
                showCancelButton: true,
                confirmButtonText: 'Submit Final Marks',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#16a34a'
            }).then(function (result) {
                if (!result.isConfirmed) { return; }
                submitFinalButton.disabled = true;
                submitFinalButton.textContent = 'Submitting...';
                HTMLFormElement.prototype.submit.call(finalSubmitForm);
            });
        });
    }

    function setupFilters() {
        const yearSelect       = getElement('academic_year_id');
        const examSelect       = getElement('exam_master_id');
        const assignmentSelect = getElement('teacher_subject_allocation_id');
        const filterForm       = getElement('marksFilterForm');

        if (yearSelect && filterForm) {
            yearSelect.addEventListener('change', function () {
                if (assignmentSelect) {
                    assignmentSelect.innerHTML = '<option value="">Select Exam First</option>';
                    assignmentSelect.disabled = true;
                }
                filterForm.submit();
            });
        }

        if (examSelect && filterForm) {
            examSelect.addEventListener('change', function () {
                if (assignmentSelect) {
                    assignmentSelect.innerHTML = '<option value="">Loading assignments...</option>';
                    assignmentSelect.disabled = true;
                }
                filterForm.submit();
            });
        }
    }

    function protectRowButtons() {
        document
            .querySelectorAll('#marksSaveForm button[id^="btn_"],#marksSaveForm button[id^="optional_btn_"]')
            .forEach(function (button) { button.type = 'button'; });
    }

    function initialize() {
        protectRowButtons();
        setupMarkValidation();
        setupSaveForm();
        setupFinalSubmit();
        setupFilters();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }
})();
</script>

</x-app-layout>