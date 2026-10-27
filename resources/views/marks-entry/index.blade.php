<x-app-layout>

@php
    $H = \App\Helpers\MarksEntryBladeHelper::class;

    $flags = $H::resolveComponentFlags(
        $showTheory    ?? null,
        $showOral      ?? null,
        $showPractical ?? null,
        $existingMarks ?? null
    );

    /* Controller-provided flags win; fallback to helper only if null */
    $showTheory    = $showTheory    ?? $flags['theory'];
    $showOral      = $showOral      ?? $flags['oral'];
    $showPractical = $showPractical ?? $flags['practical'];

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

    $totalMaxMarks     = $totalMaxMarks     ?? 0;
    $totalPassingMarks = $totalPassingMarks ?? 0;

    $theoryMaxMarks        = $theoryMaxMarks        ?? 0;
    $theoryPassingMarks    = $theoryPassingMarks    ?? 0;
    $oralMaxMarks          = $oralMaxMarks          ?? 0;
    $oralPassingMarks      = $oralPassingMarks      ?? 0;
    $practicalMaxMarks     = $practicalMaxMarks     ?? 0;
    $practicalPassingMarks = $practicalPassingMarks ?? 0;

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
    .exam-progress-page,
    .exam-progress-page * {
        box-sizing: border-box;
        font-family: Arial, Helvetica, sans-serif !important;
        font-size: 13px;
    }

    .exam-progress-page {
        width: 100%;
        max-width: 100%;
        background: #f0f0f0;
        padding: 12px;
        overflow-x: hidden;
    }

    .exam-progress-page h2 {
        margin: 0 0 10px !important;
        font-size: 20px !important;
        font-weight: 700 !important;
        color: #1D4ED8 !important;
        letter-spacing: 0.4px;
    }

    .exam-progress-page .erp-card {
        width: 100%;
        max-width: 100%;
        background: #ffffff !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 10px !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08) !important;
        padding: 12px 14px !important;
        margin-bottom: 12px;
        overflow: hidden;
    }

    .tabs-bar {
        display: flex;
        gap: 6px;
        margin-bottom: 12px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e5e7eb;
        flex-wrap: wrap;
    }

    .tab {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 26px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        text-decoration: none;
        color: #4b5563;
        background: #f3f4f6;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        cursor: pointer;
        transition: all .15s;
        line-height: 1;
    }
    .tab:hover { background: #e5e7eb; color: #1e40af; border-color: #9ca3af; }
    .tab.active {
        background: #1e3a8a;
        color: #ffffff;
        border-color: #1e3a8a;
        box-shadow: 0 2px 6px rgba(30, 58, 138, 0.25);
    }

    .status-banner {
        margin-top: 12px;
        margin-bottom: 12px;
        padding: 12px 16px;
        background: #fffbeb;
        border: 1px solid #fcd34d;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        color: #92400e;
    }
    .status-banner .status-success { color: #166534; }
    .status-banner .status-warning { color: #92400e; }

    .filter-row {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        width: 100%;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 0;
        flex: 0 1 auto;
    }

    .filter-label {
        font-size: 10.5px !important;
        font-weight: 700 !important;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        white-space: nowrap;
        padding: 0;
        margin: 0;
    }

    .select-wrapper { position: relative; display: block; width: 100%; }

    .filter-select {
        height: 34px;
        width: 100%;
        padding: 4px 28px 4px 10px;
        font-size: 12.5px !important;
        font-weight: 600;
        border: 1px solid #9CA3AF;
        border-radius: 6px;
        background-color: #ffffff;
        color: #111827;
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
        outline: none;
        transition: border-color .15s, box-shadow .15s;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%234B5563' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 9px center;
        background-size: 12px;
    }
    .filter-select:focus {
        border-color: #2563EB;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
    }

    .filter-group.w-academic-year { width: 140px; }
    .filter-group.w-exam          { width: 230px; }
    .filter-group.w-assignment    { width: 380px; }
    .filter-group.filter-group-btn { flex: 0 0 auto; margin-left: 0; }

    .erp-btn, .erp-btn-save {
        height: 34px !important;
        min-width: 84px;
        padding: 0 16px !important;
        font-size: 12.5px !important;
        font-weight: 700 !important;
        border-radius: 6px !important;
        background: #16A34A !important;
        color: #fff !important;
        border: 1px solid #15803D !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        text-decoration: none !important;
        white-space: nowrap;
        transition: background .15s, border-color .15s;
        line-height: 1;
    }
    .erp-btn:hover, .erp-btn-save:hover {
        background: #15803D !important;
        border-color: #166534 !important;
    }
    .erp-btn.erp-btn-reset {
        background: #6B7280 !important;
        border-color: #4B5563 !important;
    }
    .erp-btn.erp-btn-reset:hover {
        background: #4B5563 !important;
        border-color: #374151 !important;
    }

    .summary-row {
        margin-top: 12px;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .summary-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px !important;
        font-weight: 700;
        letter-spacing: 0.3px;
        text-transform: uppercase;
        border: 1px solid transparent;
        white-space: nowrap;
    }
    .completed-badge { background: #DCFCE7; color: #166534; border-color: #86EFAC; }
    .pending-badge   { background: #FEF3C7; color: #92400E; border-color: #FCD34D; }
    .total-badge     { background: #DBEAFE; color: #1E40AF; border-color: #93C5FD; }

    .result-table-wrapper {
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: #fff;
    }

    .result-table {
        width: 100%;
        border-collapse: collapse;
        background: #FFFFFF;
        font-size: 12.5px !important;
        table-layout: fixed;
    }
    .result-table thead tr { background: #1e3a8a; }

    .result-table th {
        background: #1e3a8a;
        color: #FFFFFF !important;
        font-weight: 700 !important;
        font-size: 9.5px !important;
        text-transform: uppercase;
        letter-spacing: 0.1px;
        line-height: 1.15;
        border: 1px solid #1e40af;
        padding: 6px 3px;
        white-space: normal;
        text-align: center;
        overflow: visible;
        text-overflow: clip;
        word-break: normal;
    }

    .result-table td {
        border: 1px solid #d1d5db;
        padding: 5px 4px;
        white-space: nowrap;
        font-size: 12px;
        color: #111827;
        vertical-align: middle;
        line-height: 1.3;
        overflow: hidden;
        text-overflow: ellipsis;
        text-align: center;
    }

    .result-table tbody tr:nth-child(even) { background: #f9fafb; }
    .result-table tbody tr:hover           { background: #fef9c3; }
    .result-table th.left,
    .result-table td.left   { text-align: left; }

    .student-name {
        font-weight: 700;
        color: #111827;
        text-align: left !important;
        white-space: nowrap !important;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .row-total-cell {
        background: #FFF7ED !important;
        color: #C2410C !important;
        font-weight: 800 !important;
        font-size: 13px !important;
    }
    .row-total-cell.total-absent   { color: #DC2626 !important; }
    .row-total-cell.total-optional { color: #D97706 !important; }

    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 5px;
        font-size: 11px !important;
        font-weight: 700;
        letter-spacing: 0.3px;
        text-transform: uppercase;
        border: 1px solid transparent;
        white-space: nowrap;
    }
    .status-completed { background: #DCFCE7; color: #166534; border-color: #86EFAC; }
    .status-pending   { background: #FEF3C7; color: #92400E; border-color: #FCD34D; }
    .status-other     { background: #E5E7EB; color: #374151; border-color: #D1D5DB; }

    .no-record {
        border: 1px solid #d1d5db;
        padding: 20px 16px;
        text-align: center;
        color: #92400E;
        background: #fffbeb;
        font-weight: 600;
        font-size: 13px;
    }

    .mark-input {
        width: 100%;
        height: 26px;
        padding: 2px 4px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        text-align: center;
        font-size: 12px;
        font-weight: 700;
        -moz-appearance: textfield;
        appearance: textfield;
        transition: border-color .15s, box-shadow .15s;
        margin: 0 auto;
        display: block;
        background: #fff;
        color: #111827;
        outline: none;
    }
    .mark-input::-webkit-outer-spin-button,
    .mark-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .mark-input:focus { border-color: #2563EB; box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15); }
    .mark-input:read-only { background: #F3F4F6; color: #6B7280; cursor: not-allowed; }

    .skill-toggle-btn {
        width: 100%;
        max-width: 74px;
        height: 26px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        font-size: 9.5px;
        font-weight: 700;
        color: #fff;
        border: 0;
        border-radius: 4px;
        cursor: pointer;
        white-space: nowrap;
        transition: background-color .15s ease;
        padding: 0;
        margin: 0 auto;
        letter-spacing: 0.2px;
    }
    .skill-present-btn { background-color: #16a34a; }
    .skill-present-btn:hover { background-color: #15803d; }
    .skill-absent-btn { background-color: #dc2626; }
    .skill-absent-btn:hover { background-color: #b91c1c; }
    .skill-toggle-btn .dot {
        display: inline-block;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #fff;
        opacity: .9;
        flex-shrink: 0;
    }

    .status-cell {
        font-weight: 700;
        font-size: 10.5px;
        text-transform: uppercase;
        white-space: nowrap;
        letter-spacing: 0.2px;
    }
    .status-cell.status-present { color: #16a34a; }
    .status-cell.status-absent  { color: #dc2626; }
    .status-cell.status-optional { color: #D97706; }

    .mark-text { font-weight: 700; font-size: 12.5px; display: none; }
    .mark-text.readonly-mark.absent   { color: #DC2626 !important; }
    .mark-text.readonly-mark.optional { color: #D97706 !important; }

    .marks-action-row {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 10px;
        margin-top: 14px;
        flex-wrap: wrap;
    }

    @media (max-width: 1100px) {
        .filter-group.w-academic-year { width: 130px; }
        .filter-group.w-exam          { width: 200px; }
        .filter-group.w-assignment    { width: 300px; }
    }
    @media (max-width: 900px) {
        .filter-row { gap: 6px; }
        .filter-group.w-academic-year,
        .filter-group.w-exam,
        .filter-group.w-assignment { flex: 1 1 140px; width: auto; }
    }
    @media (max-width: 700px) {
        .exam-progress-page { padding: 8px; }
        .tabs-bar { flex-direction: column; }
        .tab { justify-content: center; }
        .filter-row { flex-direction: column; align-items: stretch; }
        .filter-group,
        .filter-group.w-academic-year,
        .filter-group.w-exam,
        .filter-group.w-assignment,
        .filter-group.filter-group-btn { width: 100%; flex: 1 1 100%; margin-left: 0; }
        .erp-btn, .erp-btn-save { width: 100%; }
        .marks-action-row { flex-direction: column; align-items: stretch; justify-content: center; }
        .marks-action-row .erp-btn { width: 100%; }
    }
</style>

<div class="exam-progress-page">

    {{-- HEADER WITH BACK BUTTON --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
        <h2 style="margin: 0 !important;">Examination Marks</h2>
        <a href="{{ route('exam-progress.index', ['subject_type' => 'academic']) }}" class="erp-btn erp-btn-reset" style="text-decoration: none;">
            ⬅ Back
        </a>
    </div>

    @if(session('error'))
        <div class="error-box">⚠ {{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="error-box">
            <strong>Please correct the following:</strong>
            <ul style="margin: 6px 0 0 20px; padding: 0;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="tabs-bar">
        <a href="{{ $marksEntryUrl }}" class="tab active">Marks Entry</a>
        <a href="{{ $viewMarksUrl }}" class="tab">View Marks</a>
    </div>

    @if(session('success') || (request()->boolean('marks_saved') && !$marksLocked))
        <div class="status-banner">
            @if(session('success'))
                <span class="status-success">✓ {{ session('success') }}</span>
            @endif
            @if(request()->boolean('marks_saved') && !$marksLocked)
                <span class="status-warning">⚠ Marks are saved but <strong>NOT finally submitted</strong>. Please click <strong>Submit Final Marks</strong> to complete the submission.</span>
            @endif
        </div>
    @endif

    <div class="erp-card">
        <form method="GET" action="{{ route('marks-entry.index') }}" id="marksFilterForm">
            <div class="filter-row">
                <div class="filter-group w-academic-year">
                    <label class="filter-label" for="academic_year_id">Academic Year</label>
                    <div class="select-wrapper">
                        <select name="academic_year_id" id="academic_year_id" class="filter-select">
                            <option value="">All Academic Years</option>
                            {!! $H::renderYearOptions($academicYears, request('academic_year_id')) !!}
                        </select>
                    </div>
                </div>

                <div class="filter-group w-exam">
                    <label class="filter-label" for="exam_master_id">Exam</label>
                    <div class="select-wrapper">
                        <select name="exam_master_id" id="exam_master_id" class="filter-select">
                            <option value="">Select Exam</option>
                            {!! $H::renderExamOptions($exams, $selectedExamId) !!}
                        </select>
                    </div>
                </div>

                <div class="filter-group w-assignment">
                    <label class="filter-label" for="teacher_subject_allocation_id">Teaching Assignment</label>
                    <div class="select-wrapper">
                        <select name="teacher_subject_allocation_id" id="teacher_subject_allocation_id"
                                class="filter-select"
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
                    </div>
                </div>

                <div class="filter-group filter-group-btn">
                    <label class="filter-label" style="visibility:hidden;">Action</label>
                    <button type="submit" class="erp-btn erp-btn-save" id="loadStudentsButton">Load</button>
                </div>

                <div class="filter-group filter-group-btn">
                    <label class="filter-label" style="visibility:hidden;">Action</label>
                    <a href="{{ route('marks-entry.index') }}" class="erp-btn erp-btn-reset">Reset</a>
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
            <div class="summary-row">
                <span class="summary-badge total-badge">Students: {{ $entryRows->count() }}</span>
                <span class="summary-badge total-badge">Teacher: {{ $teacherName }}</span>
                <span class="summary-badge total-badge">Subject: {{ $subjectName }}</span>
                <span class="summary-badge total-badge">Class: {{ $standardName }}@if($divisionName) - {{ $divisionName }}@endif</span>
                <span class="summary-badge total-badge">Exam: {{ $exam->display_exam_name ?? $exam->exam_name }}</span>
                <span class="summary-badge completed-badge">Total Max: {{ (int) $totalMaxMarks }}</span>
                @if($showOptionalColumn)
                    <span class="summary-badge pending-badge">Optional Selection: Enabled</span>
                @endif
            </div>
        @endif
    </div>

    @if($entryRows->count() > 0)
        <div class="erp-card">
            <form method="POST" action="{{ route('marks-entry.save') }}" id="marksSaveForm" novalidate>
                @csrf
                <input type="hidden" name="academic_year_id" value="{{ request('academic_year_id') }}">
                <input type="hidden" name="exam_master_id" value="{{ $selectedExamId }}">
                <input type="hidden" name="teacher_subject_allocation_id" value="{{ $selectedTsaId }}">

                <div class="result-table-wrapper">
                    <table class="result-table">
                        <colgroup>
                            <col style="width:5%;">
                            <col style="width:5%;">
                            <col style="width:20%;">
                            <col style="width:8%;">
                            @if($showOptionalColumn) <col style="width:6.5%;"> @endif

                            @if($showTheory)
                                <col style="width:4.5%;"> <col style="width:4.5%;"> <col style="width:5.5%;">
                            @endif
                            @if($showOral)
                                <col style="width:4.5%;"> <col style="width:4.5%;"> <col style="width:5.5%;">
                            @endif
                            @if($showPractical)
                                <col style="width:4.5%;"> <col style="width:4.5%;"> <col style="width:5.5%;">
                            @endif

                            <col style="width:7%;">
                            <col style="width:7%;">
                        </colgroup>

                        <thead>
                            <tr>
                                <th>GR<br>NO</th>
                                <th>ROLL<br>NO</th>
                                <th class="left">STUDENT<br>NAME</th>
                                <th>ATTEN-<br>DANCE</th>

                                @if($showOptionalColumn)
                                    <th class="optional-header">OPT-<br>IONAL</th>
                                @endif

                                @if($showTheory)
                                    <th>THEORY<br>MAX</th>
                                    <th>THEORY<br>PASS</th>
                                    <th class="th-obtained">THEORY<br>OBT.</th>
                                @endif

                                @if($showOral)
                                    <th>ORAL<br>MAX</th>
                                    <th>ORAL<br>PASS</th>
                                    <th class="th-obtained">ORAL<br>OBT.</th>
                                @endif

                                @if($showPractical)
                                    <th>PRACT.<br>MAX</th>
                                    <th>PRACT.<br>PASS</th>
                                    <th class="th-obtained">PRACT.<br>OBT.</th>
                                @endif

                                <th class="total-header">
                                    TOTAL<br>({{ (int) $totalMaxMarks }})
                                </th>

                                <th>STATUS</th>
                            </tr>
                        </thead>

                        <tbody>
                        @foreach($entryRows as $row)
                            @php
                                $sid = $row['student_id'];

                                $rowTotal = 0;
                                if (!$row['is_absent'] && !$row['is_optional']) {
                                    if ($showTheory)    $rowTotal += (float) ($row['theory_value']    ?? 0);
                                    if ($showOral)      $rowTotal += (float) ($row['oral_value']      ?? 0);
                                    if ($showPractical) $rowTotal += (float) ($row['practical_value'] ?? 0);
                                }
                                $rowTotalDisplay = $row['is_optional']
                                    ? 'OPT'
                                    : ($row['is_absent'] ? 'AB' : $rowTotal);
                            @endphp

                            <tr>
                                <td class="center">
                                    {{ $row['gr_no'] }}
                                    <input type="hidden" name="student_ids[]" value="{{ $sid }}">
                                </td>

                                <td class="center">{{ $row['roll_no'] }}</td>

                                <td class="student-name" title="{{ $row['full_name'] }}">{{ $row['full_name'] ?: '-' }}</td>

                                <td class="center">
                                    <input type="hidden"
                                           name="is_absent[{{ $sid }}]"
                                           id="absent_{{ $sid }}"
                                           value="{{ $row['is_absent'] ? 1 : 0 }}">

                                    @if(!$marksLocked)
                                        <button type="button"
                                                id="btn_{{ $sid }}"
                                                class="skill-toggle-btn {{ $row['is_absent'] ? 'skill-absent-btn' : 'skill-present-btn' }}"
                                                onclick="toggleAbsent('{{ $sid }}', event)">
                                            <span class="dot"></span>
                                            <span class="btn-label">{{ $row['is_absent'] ? 'ABSENT' : 'PRESENT' }}</span>
                                        </button>
                                    @else
                                        <span class="status-cell {{ $row['is_absent'] ? 'status-absent' : 'status-present' }}">
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
                                                    class="skill-toggle-btn {{ $row['is_optional'] ? 'skill-absent-btn' : 'skill-present-btn' }}"
                                                    onclick="toggleOptional('{{ $sid }}', event)"
                                                    style="{{ $row['is_optional'] ? 'background-color:#D97706;' : '' }}">
                                                <span class="dot"></span>
                                                <span class="btn-label">{{ $row['is_optional'] ? 'OPTIONAL' : 'NORMAL' }}</span>
                                            </button>
                                        @else
                                            <span class="status-cell {{ $row['is_optional'] ? 'status-optional' : 'status-present' }}">
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
                                               data-student="{{ $sid }}"
                                               {{ $row['marks_readonly'] ? 'readonly' : '' }}>
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
                                               data-student="{{ $sid }}"
                                               {{ $row['marks_readonly'] ? 'readonly' : '' }}>
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
                                               data-student="{{ $sid }}"
                                               {{ $row['marks_readonly'] ? 'readonly' : '' }}>
                                    </td>
                                @endif

                                <td class="row-total-cell {{ $row['is_optional'] ? 'total-optional' : ($row['is_absent'] ? 'total-absent' : '') }}"
                                    id="total_{{ $sid }}"
                                    data-student="{{ $sid }}">
                                    {{ $rowTotalDisplay }}
                                </td>

                                <td class="center" id="status_{{ $sid }}">
                                    <span class="status-cell {{ $row['is_optional'] ? 'status-optional' : ($row['is_absent'] ? 'status-absent' : 'status-present') }}">
                                        {{ $row['is_optional'] ? 'OPTIONAL' : ($row['is_absent'] ? 'ABSENT' : 'PRESENT') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="marks-action-row">
                    @if(!$marksLocked)
                        <button type="submit"
                                formaction="{{ route('marks-entry.save') }}"
                                id="saveMarksButton"
                                class="erp-btn erp-btn-reset"
                                style="min-width: 170px;">
                            Save Marks
                        </button>
                    @endif

                    <button type="submit"
                            formaction="{{ route('marks-entry.submit') }}"
                            id="submitFinalButton"
                            class="erp-btn erp-btn-save"
                            style="min-width: 170px;"
                            {{ $marksLocked ? 'disabled' : '' }}>
                        {{ $marksLocked ? 'Marks Submitted' : 'Submit Final Marks' }}
                    </button>
                </div>
            </form>
        </div>

    @elseif(request()->filled('teacher_subject_allocation_id') && !$marksLocked)
        <div class="erp-card">
            <div class="no-record">
                No students found for the selected teaching assignment.
                Please verify the Old ERP student mapping.
            </div>
        </div>
    @else
        <div class="erp-card">
            <div class="no-record" style="background: #eff6ff; border-color: #93c5fd; color: #1e40af;">
                Select your filters above and click <strong>Load</strong> to view students.
            </div>
        </div>
    @endif

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
(function () {
    'use strict';

    function getElement(id) { return document.getElementById(id); }
    function getStudentInputs(studentId) { return document.querySelectorAll('.student-' + studentId); }

    /* ⭐ DYNAMIC TOTAL — sum of visible component inputs */
    function recalculateTotal(studentId) {
        const totalCell = getElement('total_' + studentId);
        if (!totalCell) return;

        const absentInput   = getElement('absent_' + studentId);
        const optionalInput = getElement('optional_' + studentId);

        const isAbsent   = absentInput   && absentInput.value === '1';
        const isOptional = optionalInput && optionalInput.value === '1';

        if (isOptional) {
            totalCell.textContent = 'OPT';
            totalCell.className = 'row-total-cell total-optional';
            return;
        }
        if (isAbsent) {
            totalCell.textContent = 'AB';
            totalCell.className = 'row-total-cell total-absent';
            return;
        }

        let total = 0;
        getStudentInputs(studentId).forEach(function (input) {
            const raw = (input.value ?? '').toString().trim();
            if (raw === '' || raw === 'AB' || raw === 'OPT') return;
            const v = parseFloat(raw);
            if (!isNaN(v)) total += v;
        });

        totalCell.textContent = total;
        totalCell.className = 'row-total-cell';
    }

    /* ============================================================
       ⭐ AUTO-SAVE LOGIC
       ============================================================ */
    const csrfToken = document.querySelector('input[name="_token"]')?.value;
    let saveTimeouts = {};

    function triggerAutoSave(studentId) {
        if (!csrfToken) return;

        const form = document.getElementById('marksSaveForm');
        if (!form) return;

        const absentInput = document.getElementById('absent_' + studentId);
        const optionalInput = document.getElementById('optional_' + studentId);
        
        const theoryInput = document.querySelector(`input[name="theory_marks[${studentId}]"]`);
        const oralInput = document.querySelector(`input[name="oral_marks[${studentId}]"]`);
        const practicalInput = document.querySelector(`input[name="practical_marks[${studentId}]"]`);

        const payload = {
            _token: csrfToken,
            teacher_subject_allocation_id: form.querySelector('input[name="teacher_subject_allocation_id"]').value,
            exam_master_id: form.querySelector('input[name="exam_master_id"]').value,
            student_id: studentId,
            is_absent: { [studentId]: absentInput ? parseInt(absentInput.value) : 0 },
            is_optional: { [studentId]: optionalInput ? parseInt(optionalInput.value) : 0 },
            theory_marks: { [studentId]: theoryInput ? theoryInput.value : '' },
            oral_marks: { [studentId]: oralInput ? oralInput.value : '' },
            practical_marks: { [studentId]: practicalInput ? practicalInput.value : '' }
        };

        const row = theoryInput ? theoryInput.closest('tr') : null;
        if (row) row.style.opacity = '0.6';

        fetch('{{ route("marks-entry.autosave") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (row) row.style.opacity = '1';
            if (data.success) {
                if (row) {
                    const originalBg = row.style.backgroundColor;
                    row.style.backgroundColor = '#dcfce7';
                    setTimeout(() => { row.style.backgroundColor = originalBg; }, 800);
                }
            } else {
                Swal.fire('Auto-save Error', data.message || 'Failed to save.', 'error');
            }
        })
        .catch(err => {
            if (row) row.style.opacity = '1';
            console.error('Auto-save error:', err);
        });
    }

    function attachTotalListeners() {
        document.querySelectorAll('.mark-input').forEach(function (input) {
            const studentId = input.dataset.student;
            if (!studentId) return;
            
            input.addEventListener('input',  function () { 
                recalculateTotal(studentId); 
                if (saveTimeouts[studentId]) clearTimeout(saveTimeouts[studentId]);
                saveTimeouts[studentId] = setTimeout(() => triggerAutoSave(studentId), 800);
            });
            
            input.addEventListener('change', function () { 
                recalculateTotal(studentId); 
                triggerAutoSave(studentId); 
            });
        });
    }

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
        input.style.background = '';
        input.style.border = '1px solid #d1d5db';
    }

    function setInputEnabled(input) {
        input.readOnly = false;
        if (input.value === '0') { input.value = ''; }
        input.style.background = '';
        input.style.border = '1px solid #d1d5db';
        showMarkAsInput(input);
    }

    function updateAttendanceButton(studentId, isAbsent) {
        const button = getElement('btn_' + studentId);
        if (!button) { return; }
        button.type = 'button';
        const label = button.querySelector('.btn-label');
        if (isAbsent) {
            if (label) label.textContent = 'ABSENT';
            button.classList.remove('skill-present-btn');
            button.classList.add('skill-absent-btn');
        } else {
            if (label) label.textContent = 'PRESENT';
            button.classList.remove('skill-absent-btn');
            button.classList.add('skill-present-btn');
        }
    }

    function updateOptionalButton(studentId, isOptional) {
        const button = getElement('optional_btn_' + studentId);
        if (!button) { return; }
        button.type = 'button';
        const label = button.querySelector('.btn-label');
        if (isOptional) {
            if (label) label.textContent = 'OPTIONAL';
            button.classList.remove('skill-present-btn');
            button.classList.add('skill-absent-btn');
            button.style.backgroundColor = '#D97706';
        } else {
            if (label) label.textContent = 'NORMAL';
            button.classList.remove('skill-absent-btn');
            button.classList.add('skill-present-btn');
            button.style.backgroundColor = '';
        }
    }

    function updateStatus(studentId, statusType) {
        const status = getElement('status_' + studentId);
        if (!status) { return; }
        if (statusType === 'ABSENT') {
            status.innerHTML = '<span class="status-cell status-absent">ABSENT</span>';
        } else if (statusType === 'OPTIONAL') {
            status.innerHTML = '<span class="status-cell status-optional">OPTIONAL</span>';
        } else {
            status.innerHTML = '<span class="status-cell status-present">PRESENT</span>';
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
        recalculateTotal(studentId);
        triggerAutoSave(studentId); // ⭐ Auto-Save
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
            input.style.background = '#fee2e2';
            input.style.border = '1px solid #fca5a5';
            showMarkAsText(input, 'AB', 'readonly-mark absent');
        });
        recalculateTotal(studentId);
        triggerAutoSave(studentId); // ⭐ Auto-Save
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
            input.style.background = '#fff7ed';
            input.style.border = '1px solid #f59e0b';
            showMarkAsText(input, 'OPT', 'readonly-mark optional');
        });
        recalculateTotal(studentId);
        triggerAutoSave(studentId); // ⭐ Auto-Save
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
        recalculateTotal(studentId);
        triggerAutoSave(studentId); // ⭐ Auto-Save
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

    function validateMarksForSave() {
        const rows = document.querySelectorAll('#marksSaveForm tbody tr');
        let hasError = false;
        let errorMessage = '';

        rows.forEach(function (row) {
            const optionalField = row.querySelector('input[name^="is_optional["]');
            const absentField   = row.querySelector('input[name^="is_absent["]');
            const isOptional = optionalField && optionalField.value === '1';
            const isAbsent   = absentField   && absentField.value === '1';
            if (isOptional || isAbsent) { return; }

            row.querySelectorAll('.mark-input').forEach(function (input) {
                if (input.readOnly || input.style.display === 'none') { return; }
                const value = input.value.trim();

                if (value === '') { input.style.border = '1px solid #d1d5db'; return; }

                if (!/^\d+(\.\d+)?$/.test(value)) {
                    hasError = true;
                    errorMessage = 'Only numeric values are allowed.';
                    input.style.border = '2px solid #dc2626';
                    return;
                }

                const number = parseFloat(value);
                const max = parseFloat(input.max);

                if (isNaN(number)) { hasError = true; errorMessage = 'Please enter a valid mark.'; input.style.border = '2px solid #dc2626'; return; }
                if (number < 0)    { hasError = true; errorMessage = 'Marks cannot be negative.'; input.style.border = '2px solid #dc2626'; return; }
                if (number > max)  { hasError = true; errorMessage = 'Marks cannot exceed ' + max + '.'; input.style.border = '2px solid #dc2626'; return; }

                input.style.border = '1px solid #16a34a';
            });
        });

        if (hasError) {
            Swal.fire({ icon: 'error', title: 'Validation Error', text: errorMessage, confirmButtonText: 'OK' });
            return false;
        }
        return true;
    }

    function validateMarksForSubmit() {
        const rows = document.querySelectorAll('#marksSaveForm tbody tr');
        let hasError = false;
        let errorMessage = '';

        rows.forEach(function (row) {
            const optionalField = row.querySelector('input[name^="is_optional["]');
            const absentField   = row.querySelector('input[name^="is_absent["]');
            const isOptional = optionalField && optionalField.value === '1';
            const isAbsent   = absentField   && absentField.value === '1';
            if (isOptional || isAbsent) { return; }

            row.querySelectorAll('.mark-input').forEach(function (input) {
                if (input.readOnly || input.style.display === 'none') { return; }
                const value = input.value.trim();

                if (value === '') { hasError = true; errorMessage = 'All marks must be filled before final submission.'; input.style.border = '2px solid #dc2626'; return; }
                if (!/^\d+(\.\d+)?$/.test(value)) { hasError = true; errorMessage = 'Only numeric values are allowed.'; input.style.border = '2px solid #dc2626'; return; }

                const number = parseFloat(value);
                const max = parseFloat(input.max);

                if (number < 0 || number > max) { hasError = true; errorMessage = 'Marks must be between 0 and ' + max + '.'; input.style.border = '2px solid #dc2626'; return; }

                input.style.border = '1px solid #16a34a';
            });
        });

        if (hasError) {
            Swal.fire({ icon: 'error', title: 'Validation Error', text: errorMessage, confirmButtonText: 'OK' });
            return false;
        }
        return true;
    }

    function setupMarkValidation() {
        document.querySelectorAll('.mark-input').forEach(function (input) {
            input.addEventListener('input', function () {
                if (this.readOnly || this.style.display === 'none') return;
                const max = parseFloat(this.max);
                const value = this.value.trim();

                if (value === '') { this.style.border = '1px solid #d1d5db'; return; }
                if (!/^\d*\.?\d*$/.test(value)) { this.value = value.replace(/[^\d.]/g, ''); return; }

                const number = parseFloat(this.value);
                if (!isNaN(number)) {
                    if (number > max) this.value = max;
                    if (number < 0)   this.value = 0;
                }
                const finalVal = parseFloat(this.value);
                this.style.border = (isNaN(finalVal) || finalVal < 0 || finalVal > max)
                    ? '2px solid #dc2626'
                    : '1px solid #16a34a';
            });
        });
    }

    function setupFormSubmit() {
        const saveForm = getElement('marksSaveForm');
        if (!saveForm) return;

        saveForm.addEventListener('submit', function (event) {
            event.preventDefault();
            const submitter = event.submitter;
            const isFinalSubmit = submitter && submitter.id === 'submitFinalButton';
            const isValid = isFinalSubmit ? validateMarksForSubmit() : validateMarksForSave();
            if (!isValid) return;

            const title = isFinalSubmit ? 'Final Marks Submission' : 'Save Marks';
            const html = isFinalSubmit
                ? '<div style="text-align:left"><b>This is the FINAL submission of marks.</b><br><br>Please check all marks carefully.<br><br>After final submission:<ul style="margin-top:8px;margin-left:20px;"><li>Marks will be locked.</li><li>Teacher cannot modify the marks.</li><li>Administrator intervention will be required for corrections.</li></ul></div>'
                : '<div style="text-align:left">Your marks will be saved as a <b>draft</b>.<br><br>You can leave some marks blank and complete them later.<br><br>You can still edit them before final submission.<br><br><b>Continue and save?</b></div>';

            Swal.fire({
                icon: isFinalSubmit ? 'warning' : 'question',
                title: title,
                html: html,
                showCancelButton: true,
                confirmButtonText: isFinalSubmit ? 'Submit Final Marks' : 'Yes, Save Marks',
                cancelButtonText: 'Cancel',
                confirmButtonColor: isFinalSubmit ? '#16a34a' : '#2563eb'
            }).then(function (result) {
                if (!result.isConfirmed) return;

                if (submitter) {
                    submitter.disabled = true;
                    submitter.textContent = isFinalSubmit ? 'Submitting...' : 'Saving...';
                }
                if (submitter && submitter.hasAttribute('formaction')) {
                    saveForm.setAttribute('action', submitter.getAttribute('formaction'));
                }
                HTMLFormElement.prototype.submit.call(saveForm);
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
                if (assignmentSelect) { assignmentSelect.innerHTML = '<option value="">Select Exam First</option>'; assignmentSelect.disabled = true; }
                filterForm.submit();
            });
        }

        if (examSelect && filterForm) {
            examSelect.addEventListener('change', function () {
                if (assignmentSelect) { assignmentSelect.innerHTML = '<option value="">Loading assignments...</option>'; assignmentSelect.disabled = true; }
                filterForm.submit();
            });
        }
    }

    function protectRowButtons() {
        document
            .querySelectorAll('#marksSaveForm button[id^="btn_"],#marksSaveForm button[id^="optional_btn_"]')
            .forEach(function (button) { button.type = 'button'; });
    }

    function restoreButtonStates() {
        document.querySelectorAll('#marksSaveForm tbody tr').forEach(function (row) {
            const absentInput   = row.querySelector('input[name^="is_absent["]');
            const optionalInput = row.querySelector('input[name^="is_optional["]');
            const attendanceBtn = row.querySelector('button[id^="btn_"]');
            const optionalBtn   = row.querySelector('button[id^="optional_btn_"]');
            const statusCell    = row.querySelector('td[id^="status_"] .status-cell');

            if (!absentInput) return;

            const sid = absentInput.name.match(/\[(\d+)\]/)?.[1];
            const isAbsent   = absentInput.value === '1';
            const isOptional = optionalInput && optionalInput.value === '1';

            if (attendanceBtn) {
                const label = attendanceBtn.querySelector('.btn-label');
                if (isAbsent) {
                    attendanceBtn.classList.remove('skill-present-btn');
                    attendanceBtn.classList.add('skill-absent-btn');
                    if (label) label.textContent = 'ABSENT';
                } else {
                    attendanceBtn.classList.remove('skill-absent-btn');
                    attendanceBtn.classList.add('skill-present-btn');
                    if (label) label.textContent = 'PRESENT';
                }
            }

            if (optionalBtn) {
                const label = optionalBtn.querySelector('.btn-label');
                if (isOptional) {
                    optionalBtn.style.backgroundColor = '#D97706';
                    if (label) label.textContent = 'OPTIONAL';
                } else {
                    optionalBtn.style.backgroundColor = '';
                    if (label) label.textContent = 'NORMAL';
                }
            }

            if (statusCell) {
                if (isOptional) { statusCell.textContent = 'OPTIONAL'; statusCell.className = 'status-cell status-optional'; }
                else if (isAbsent) { statusCell.textContent = 'ABSENT'; statusCell.className = 'status-cell status-absent'; }
                else { statusCell.textContent = 'PRESENT'; statusCell.className = 'status-cell status-present'; }
            }

            if (sid) recalculateTotal(sid);
        });
    }

    window.addEventListener('pageshow', function () { restoreButtonStates(); });

    function initialize() {
        protectRowButtons();
        setupMarkValidation();
        setupFormSubmit();
        setupFilters();
        attachTotalListeners();

        document.querySelectorAll('#marksSaveForm tbody tr').forEach(function (row) {
            const absentInput = row.querySelector('input[name^="is_absent["]');
            if (!absentInput) return;
            const sid = absentInput.name.match(/\[(\d+)\]/)?.[1];
            if (sid) recalculateTotal(sid);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }
})();
</script>

</x-app-layout>