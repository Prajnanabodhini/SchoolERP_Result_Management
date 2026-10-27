<x-app-layout>

@php
    $H = \App\Helpers\EditMarkHelper::class;

    /* ---- Consolidated blade state (replaces ~80 lines of inline @php) ---- */
    $blade = $H::extractEditBladeState(
        $existingMarks               ?? collect(),
        $assignments                 ?? collect(),
        request('teacher_subject_allocation_id'),
        $isOptionalEnabled           ?? false,
        $teacherSubjectAllocation    ?? null,
        $selectedClassAllocation     ?? null
    );

    $lastModifiedAt     = $blade['lastModifiedAt'];
    $lastModifiedByName = $blade['lastModifiedByName'];
    $showOptionalColumn = $blade['showOptionalColumn'];
    $currentStatus      = $blade['currentStatus'];
    $statusBadgeClass   = $blade['statusBadgeClass'];

    $selectedTsaId          = request('teacher_subject_allocation_id');
    $selectedExamId         = request('exam_master_id');
    $selectedAcademicYearId = request('academic_year_id');
    $marksUpdated           = request()->boolean('marks_updated');
    $marksReopened          = request()->boolean('marks_reopened');

    $selectedSubjectName = optional($teacherSubjectAllocation)->subject->subject_name ?? '';
    $studentCount        = isset($students) ? $students->count() : 0;
@endphp

<style>
    /* =========================================================
       PAGE
       ========================================================= */
    .admin-marks-page,
    .admin-marks-page * {
        box-sizing: border-box;
        font-family: Arial, Helvetica, sans-serif !important;
    }

    .admin-marks-page {
        background: #f0f0f0;
        padding: 20px;
    }

    .admin-marks-page h2 {
        margin: 0 0 14px;
        font-size: 21px !important;
        font-weight: 700 !important;
        color: #1D4ED8 !important;
        letter-spacing: 0.4px;
    }

    /* Card wrapper (uses .erp-card from layout + these enhancements) */
    .admin-marks-page > .erp-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        padding: 18px 20px;
    }

    .admin-marks-page > .erp-card + .erp-card {
        margin-top: 14px;
    }

    /* =========================================================
       FILTER ROW
       ========================================================= */
    .admin-filter-row {
        display: flex;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 10px;
    }

    .admin-filter-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
        flex: 0 0 auto;
    }

    .admin-filter-label {
        font-size: 11px;
        font-weight: 700;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .admin-filter-wrapper {
        position: relative;
        display: inline-block;
    }

    .admin-filter-select {
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
    .admin-filter-select:focus {
        border-color: #2563EB;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
    }
    .admin-filter-select:disabled {
        background: #F3F4F6;
        color: #6B7280;
        cursor: not-allowed;
    }

    .admin-dropdown-arrow {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 6px solid #6b7280;
        pointer-events: none;
    }

    .admin-academic-year-select { width: 170px; }
    .admin-exam-select          { width: 230px; }
    .admin-assignment-group     { flex: 0 1 auto; min-width: 260px; max-width: 440px; }
    .admin-assignment-wrapper   { width: max-content; max-width: 100%; }
    .admin-assignment-select    { width: max-content; min-width: 280px; max-width: 440px; }

    .admin-filter-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 0 0 auto;
    }

    /* =========================================================
       BUTTONS
       ========================================================= */
    .admin-erp-btn {
        height: 32px;
        min-width: 140px;
        padding: 0 16px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        white-space: nowrap;
        border: 1px solid transparent;
        transition: background .15s, border-color .15s;
    }
    .admin-btn-blue {
        background: #16A34A;
        color: #fff;
        border-color: #15803D;
    }
    .admin-btn-blue:hover { background: #15803D; border-color: #166534; }

    .admin-btn-gray {
        background: #6B7280;
        color: #fff;
        border-color: #4B5563;
    }
    .admin-btn-gray:hover { background: #4B5563; border-color: #374151; }

    .admin-erp-btn:disabled {
        background: #9CA3AF !important;
        border-color: #6B7280 !important;
        opacity: .85;
        cursor: not-allowed;
    }

    /* =========================================================
       ALERT BOXES
       ========================================================= */
    .admin-info-box {
        margin-top: 14px;
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.5;
    }
    .admin-info-box:first-of-type { margin-top: 0; }

    .admin-success-box {
        background: #dcfce7;
        border: 1px solid #86efac;
        color: #166534;
    }
    .admin-warning-box {
        background: #fffbeb;
        border: 1px solid #fcd34d;
        color: #92400e;
    }
    .admin-error-box {
        background: #fee2e2;
        border: 1px solid #fca5a5;
        color: #991b1b;
    }
    .admin-error-box ul { margin: 0; padding-left: 20px; }

    /* =========================================================
       SELECTED INFO BANNER
       ========================================================= */
    .admin-selected-info {
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
    .admin-selected-item {
        font-weight: 700;
        color: #1e3a8a;
        text-transform: uppercase;
        font-size: 10px;
        letter-spacing: 0.3px;
    }
    .admin-selected-separator { color: #93c5fd; }

    .admin-modified-info {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
        padding: 9px 14px;
        border-radius: 8px;
        background: #f9fafb;
        border: 1px solid #d1d5db;
        color: #374151;
        font-size: 11.5px;
        font-weight: 600;
    }
    .admin-modified-title {
        font-weight: 700;
        color: #1e3a8a;
        text-transform: uppercase;
        font-size: 10px;
        letter-spacing: 0.3px;
    }
    .admin-modified-value { font-weight: 700; color: #111827; }
    .admin-modified-separator { color: #94a3b8; }

    /* =========================================================
       STATUS BADGES
       ========================================================= */
    .admin-status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 3px 10px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.3px;
    }
    .admin-status-pending   { background: #FEF3C7; color: #92400E; }
    .admin-status-completed { background: #DCFCE7; color: #166534; }
    .admin-status-locked    { background: #FEE2E2; color: #991B1B; }
    .admin-status-default   { background: #E5E7EB; color: #374151; }

    /* =========================================================
       MARKS CARD HEADER
       ========================================================= */
    .admin-marks-card { margin-top: 14px; }

    .admin-marks-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding-bottom: 10px;
        margin-bottom: 12px;
        border-bottom: 1px solid #e5e7eb;
    }
    .admin-marks-header-title {
        font-size: 13px;
        font-weight: 700;
        color: #1e3a8a;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .admin-marks-header-subtitle {
        margin-top: 3px;
        color: #6b7280;
        font-size: 11.5px;
        font-weight: 600;
    }
    .admin-marks-header-subtitle strong { color: #111827; font-weight: 700; }

    .admin-student-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 12px;
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #93c5fd;
        border-radius: 6px;
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: 0.2px;
        white-space: nowrap;
    }

    /* =========================================================
       MARKS TABLE
       ========================================================= */
    .admin-marks-table-wrapper {
        overflow-x: auto;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: #fff;
    }

    .admin-marks-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        background: #fff;
        font-size: 11px;
    }

    .admin-marks-table col.c-gr       { width: 5%;  }
    .admin-marks-table col.c-roll     { width: 5%;  }
    .admin-marks-table col.c-name     { width: 26%; }
    .admin-marks-table col.c-attend   { width: 8%;  }
    .admin-marks-table col.c-optional { width: 7%;  }
    .admin-marks-table col.c-max      { width: 6%;  }
    .admin-marks-table col.c-obt      { width: 7%;  }
    .admin-marks-table col.c-status   { width: 8%;  }

    .admin-marks-table th,
    .admin-marks-table td {
        border: 1px solid #d1d5db;
        padding: 5px 4px;
        text-align: center;
        vertical-align: middle;
        font-size: 11px;
        line-height: 1.25;
        word-break: break-word;
        color: #111827;
    }

    .admin-marks-table thead tr {
        background: #1e3a8a;
    }

    .admin-marks-table th {
        background: #1e3a8a;
        color: #ffffff !important;
        border-color: #1e40af;
        font-weight: 700;
        font-size: 10px;
        line-height: 1.15;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        white-space: nowrap;
    }

    /* Optional column header highlight */
    .admin-marks-table th.admin-optional-header {
        background: #D97706 !important;
        color: #ffffff !important;
        border-color: #B45309 !important;
    }

    .admin-marks-table tbody tr:nth-child(even) { background: #f9fafb; }
    .admin-marks-table tbody tr:hover           { background: #fef9c3; }

    .admin-marks-table .admin-center { text-align: center; }

    .admin-marks-table .admin-student-name {
        text-align: left !important;
        white-space: normal !important;
        font-weight: 700;
        color: #111827;
        line-height: 1.25;
    }
    .admin-marks-table .admin-student-name strong { font-weight: 700; }

    /* =========================================================
       MARK INPUT
       ========================================================= */
    .admin-mark-input {
        width: 100%;
        max-width: 56px;
        height: 28px;
        padding: 2px 4px;
        border: 1px solid #9CA3AF;
        border-radius: 4px;
        text-align: center;
        font-size: 11px;
        font-weight: 600;
        background: #ffffff;
        box-sizing: border-box;
        transition: border-color .15s, box-shadow .15s;
    }
    .admin-mark-input:focus {
        outline: none;
        border-color: #2563EB;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
    }
    .admin-mark-input:read-only {
        background: #F3F4F6;
        color: #6B7280;
        cursor: not-allowed;
    }
    .admin-absent-input {
        background: #FEE2E2 !important;
        color: #991B1B !important;
        border-color: #FCA5A5 !important;
    }

    /* =========================================================
       ATTENDANCE / OPTIONAL BUTTONS
       ========================================================= */
    .admin-attendance-btn,
    .admin-optional-btn {
        min-width: 72px;
        padding: 5px 8px;
        border: 1px solid transparent;
        border-radius: 5px;
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        cursor: pointer;
        line-height: 1;
        letter-spacing: 0.3px;
        text-transform: uppercase;
        white-space: nowrap;
        transition: background .15s;
    }
    .admin-present-btn         { background: #16A34A; border-color: #15803D; }
    .admin-present-btn:hover   { background: #15803D; }
    .admin-absent-btn          { background: #DC2626; border-color: #B91C1C; }
    .admin-absent-btn:hover    { background: #B91C1C; }
    .admin-optional-btn        { background: #6B7280; border-color: #4B5563; }
    .admin-optional-btn:hover  { background: #4B5563; }
    .admin-optional-active-btn       { background: #D97706 !important; border-color: #B45309 !important; }
    .admin-optional-active-btn:hover { background: #B45309 !important; }

    .admin-optional-input {
        background: #fff7ed !important;
        border-color: #f59e0b !important;
    }

    /* Status text inside table */
    .admin-status-optional { color: #D97706; font-weight: 700; font-size: 10px; letter-spacing: 0.3px; }
    .admin-status-present  { color: #16A34A; font-weight: 700; font-size: 10px; letter-spacing: 0.3px; }
    .admin-status-absent   { color: #DC2626; font-weight: 700; font-size: 10px; letter-spacing: 0.3px; }

    /* =========================================================
       ACTION ROW
       ========================================================= */
    .admin-action-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 14px;
        padding-top: 12px;
        border-top: 1px solid #e5e7eb;
        flex-wrap: wrap;
    }
    .admin-action-note {
        margin-left: auto;
        font-size: 11.5px;
        color: #6b7280;
        font-weight: 600;
    }

    /* =========================================================
       RESPONSIVE
       ========================================================= */
    @media (max-width: 900px) {
        .admin-marks-page { padding: 12px; }
        .admin-marks-page > .erp-card { padding: 14px; }

        .admin-filter-group { width: 100%; }
        .admin-academic-year-select,
        .admin-exam-select,
        .admin-assignment-select { width: 100%; max-width: none; }
        .admin-assignment-wrapper,
        .admin-assignment-group  { width: 100%; max-width: none; }
        .admin-filter-actions { width: 100%; }
        .admin-filter-actions .admin-erp-btn { flex: 1; min-width: 0; }

        .admin-marks-header { flex-direction: column; align-items: flex-start; }
        .admin-action-note  { margin-left: 0; }
    }
</style>

<div class="erp-page admin-marks-page">

    {{-- ============ FILTER CARD ============ --}}
    <div class="erp-card">

        <h2>Edit Examination Marks</h2>

        @if($errors->any())
            <div class="admin-info-box admin-error-box">
                <ul>
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($marksUpdated)
            <div class="admin-info-box admin-success-box">
                <strong>✓ Marks updated successfully.</strong>
                The current teaching assignment status has not been changed.
            </div>
        @endif

        @if($marksReopened)
            <div class="admin-info-box admin-success-box">
                <strong>✓ Marks reopened successfully.</strong>
            </div>
        @endif

        @if(!empty($error))
            <div class="admin-info-box admin-error-box">
                <strong>Error:</strong> {{ $error }}
            </div>
        @endif

        <form method="GET"
              action="{{ route('result-generation.admin-marks.edit') }}"
              id="adminMarksFilterForm">

            <div class="admin-filter-row">

                <div class="admin-filter-group">
                    <label class="admin-filter-label">Academic Year</label>
                    <div class="admin-filter-wrapper">
                        <select name="academic_year_id" id="admin_academic_year_id"
                                class="admin-filter-select admin-academic-year-select">
                            <option value="">All Academic Years</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}"
                                    {{ (string)$selectedAcademicYearId === (string)$year->id ? 'selected' : '' }}>
                                    {{ $year->year_name ?? $year->name ?? $year->id }}
                                </option>
                            @endforeach
                        </select>
                        <span class="admin-dropdown-arrow"></span>
                    </div>
                </div>

                <div class="admin-filter-group">
                    <label class="admin-filter-label">Exam</label>
                    <div class="admin-filter-wrapper">
                        <select name="exam_master_id" id="admin_exam_master_id"
                                class="admin-filter-select admin-exam-select">
                            <option value="">Select Exam</option>
                            @foreach($exams as $examItem)
                                <option value="{{ $examItem->id }}"
                                    {{ (string)$selectedExamId === (string)$examItem->id ? 'selected' : '' }}>
                                    {{ $examItem->display_exam_name ?? $examItem->exam_name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="admin-dropdown-arrow"></span>
                    </div>
                </div>

                <div class="admin-filter-group admin-assignment-group">
                    <label class="admin-filter-label">Teaching Assignment</label>
                    <div class="admin-filter-wrapper admin-assignment-wrapper">
                        <select name="teacher_subject_allocation_id"
                                id="admin_teacher_subject_allocation_id"
                                class="admin-filter-select admin-assignment-select"
                                {{ !$selectedExamId ? 'disabled' : '' }}>
                            @if(!$selectedExamId)
                                <option value="">Select Exam First</option>
                            @elseif($assignments->isEmpty())
                                <option value="">No Teaching Assignment</option>
                            @else
                                <option value="">Select Teaching Assignment</option>
                                @foreach($assignments as $assignment)
                                    @php
                                        $tName = optional(optional($assignment->allocation)->teacher)->name ?? 'Teacher';
                                        $sName = optional($assignment->subject)->subject_name ?? 'Subject';
                                        $stName = optional(optional($assignment->allocation)->standard)->standard_name ?? '';
                                        $dvName = optional(optional($assignment->allocation)->division)->division_name ?? '';
                                        $stStatus = strtoupper(trim((string)($assignment->resolved_status ?? 'PENDING')));
                                    @endphp
                                    <option value="{{ $assignment->id }}"
                                        data-academic-year-id="{{ $assignment->resolved_academic_year_id ?? '' }}"
                                        data-exam-id="{{ $assignment->resolved_exam_master_id ?? $assignment->exam_master_id }}"
                                        {{ (string)$selectedTsaId === (string)$assignment->id ? 'selected' : '' }}>
                                        {{ $tName }} - {{ $sName }} - {{ $stName }}
                                        @if($dvName && $dvName !== '-') - {{ $dvName }}@endif
                                        [{{ $stStatus }}]
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <span class="admin-dropdown-arrow"></span>
                    </div>
                </div>

                <div class="admin-filter-actions">
                    <button type="submit"
                            id="adminLoadMarksButton"
                            class="admin-erp-btn admin-btn-blue"
                            {{ !$selectedTsaId ? 'disabled' : '' }}>
                        Load Marks
                    </button>

                    <button type="button"
                            onclick="window.location.href='{{ route('result-generation.admin-marks.edit') }}';"
                            class="admin-erp-btn admin-btn-gray">
                        Reset
                    </button>
                </div>
            </div>
        </form>

        {{-- ============ SELECTED INFO ============ --}}
        @if($teacherSubjectAllocation && $exam)
            <div class="admin-selected-info">
                <span><span class="admin-selected-item">Teacher:</span>
                    {{ optional(optional($teacherSubjectAllocation->allocation)->teacher)->name ?? 'Teacher' }}</span>
                <span class="admin-selected-separator">|</span>

                <span><span class="admin-selected-item">Subject:</span>
                    {{ optional($teacherSubjectAllocation->subject)->subject_name ?? 'Subject' }}</span>
                <span class="admin-selected-separator">|</span>

                <span><span class="admin-selected-item">Class:</span>
                    {{ optional(optional($teacherSubjectAllocation->allocation)->standard)->standard_name ?? '' }}
                    @php $dn = optional(optional($teacherSubjectAllocation->allocation)->division)->division_name; @endphp
                    @if($dn) - {{ $dn }}@endif
                </span>
                <span class="admin-selected-separator">|</span>

                <span><span class="admin-selected-item">Exam:</span>
                    {{ $exam->display_exam_name ?? $exam->exam_name }}</span>
                <span class="admin-selected-separator">|</span>

                <span><span class="admin-selected-item">Status:</span>
                    <span class="admin-status-badge {{ $statusBadgeClass }}">{{ $currentStatus }}</span>
                </span>
                <span class="admin-selected-separator">|</span>

                <span><span class="admin-selected-item">Students:</span> {{ $studentCount }}</span>

                @if($showOptionalColumn)
                    <span class="admin-selected-separator">|</span>
                    <span style="color:#b45309;font-weight:700;">Optional Selection: Enabled</span>
                @endif
            </div>

            {{-- ============ LAST MODIFIED ============ --}}
            @if($lastModifiedAt)
                <div class="admin-modified-info">
                    <span class="admin-modified-title">Last Modified By:</span>
                    <span class="admin-modified-value">{{ $lastModifiedByName }}</span>
                    <span class="admin-modified-separator">|</span>
                    <span class="admin-modified-title">Last Modified On:</span>
                    <span class="admin-modified-value">
                        {{ \Carbon\Carbon::parse($lastModifiedAt)->format('d-m-Y H:i:s') }}
                    </span>
                </div>
            @else
                <div class="admin-modified-info">
                    <span class="admin-modified-title">Last Modified:</span>
                    <span>No marks have been modified yet.</span>
                </div>
            @endif

            @if(!empty($message))
                <div class="admin-info-box admin-warning-box" style="margin-top:10px;">
                    {{ $message }}
                </div>
            @endif
        @endif
    </div>

    {{-- ============ MARKS CARD ============ --}}
    @if($teacherSubjectAllocation && $studentCount > 0)
        <div class="erp-card admin-marks-card">
            <div class="admin-marks-header">
                <div>
                    <div class="admin-marks-header-title">Edit Marks</div>
                    <div class="admin-marks-header-subtitle">
                        Subject: <strong>{{ $selectedSubjectName }}</strong>
                        &nbsp;|&nbsp;
                        Teacher: <strong>{{ optional(optional($teacherSubjectAllocation->allocation)->teacher)->name ?? '' }}</strong>
                        &nbsp;|&nbsp;
                        Class: <strong>
                            {{ optional(optional($teacherSubjectAllocation->allocation)->standard)->standard_name ?? '' }}
                            @php $dn2 = optional(optional($teacherSubjectAllocation->allocation)->division)->division_name; @endphp
                            @if($dn2) - {{ $dn2 }}@endif
                        </strong>
                    </div>
                </div>
                <div class="admin-student-count">{{ $studentCount }} Students</div>
            </div>

            <form method="POST" action="{{ route('admin-marks.update') }}" id="adminMarksForm">
                @csrf
                @method('PUT')

                <input type="hidden" name="teacher_subject_allocation_id" value="{{ $teacherSubjectAllocation->id }}">
                <input type="hidden" name="exam_master_id" value="{{ $exam->id }}">

                <div class="admin-marks-table-wrapper">
                    <table class="admin-marks-table">
                        <colgroup>
                            <col class="c-gr">
                            <col class="c-roll">
                            <col class="c-name">
                            <col class="c-attend">
                            @if($showOptionalColumn)
                                <col class="c-optional">
                            @endif
                            @if($showTheory)
                                <col class="c-max">
                                <col class="c-max">
                                <col class="c-obt">
                            @endif
                            @if($showOral)
                                <col class="c-max">
                                <col class="c-max">
                                <col class="c-obt">
                            @endif
                            @if($showPractical)
                                <col class="c-max">
                                <col class="c-max">
                                <col class="c-obt">
                            @endif
                            <col class="c-status">
                        </colgroup>

                        <thead>
                            <tr>
                                <th>GR No</th>
                                <th>Roll No</th>
                                <th style="text-align:left;">Student Name</th>
                                <th>Attendance</th>

                                @if($showOptionalColumn)
                                    <th class="admin-optional-header">Optional</th>
                                @endif

                                @if($showTheory)
                                    <th>Theory Max</th>
                                    <th>Theory Pass</th>
                                    <th>Theory Obt.</th>
                                @endif

                                @if($showOral)
                                    <th>Oral Max</th>
                                    <th>Oral Pass</th>
                                    <th>Oral Obt.</th>
                                @endif

                                @if($showPractical)
                                    <th>Prac. Max</th>
                                    <th>Prac. Pass</th>
                                    <th>Prac. Obt.</th>
                                @endif

                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                        @foreach($students as $record)
                            @php
                                $studentId = $record->Studentid
                                    ?? $record->student_id
                                    ?? $record->id;

                                $studentMark = $existingMarks->get($studentId);

                                $isAbsent = $studentMark && ((int) $studentMark->is_absent === 1);
                                $isOptional = $isOptionalEnabled
                                    && $studentMark
                                    && ((int) ($studentMark->is_optional ?? 0) === 1);

                                if ($isOptional) { $isAbsent = false; }

                                $fatherName = $record->fathername ?? $record->father_name ?? $record->father ?? '';
                                $studentFullName = trim(($record->studname ?? '') . ' ' . $fatherName);

                                $theoryValue    = $studentMark?->theory_obtained_marks;
                                $oralValue      = $studentMark?->oral_obtained_marks;
                                $practicalValue = $studentMark?->practical_obtained_marks;
                            @endphp

                            <tr>
                                <td class="admin-center">
                                    {{ $record->regno ?? '-' }}
                                    <input type="hidden" name="student_ids[]" value="{{ $studentId }}">
                                </td>
                                <td class="admin-center">{{ $record->rollno ?? '-' }}</td>

                                <td class="admin-student-name">
                                    <strong>{{ $studentFullName ?: '-' }}</strong>
                                </td>

                                <td class="admin-center">
                                    <input type="hidden"
                                           name="is_absent[{{ $studentId }}]"
                                           id="admin_absent_{{ $studentId }}"
                                           value="{{ $isAbsent ? 1 : 0 }}">
                                    <button type="button"
                                            id="admin_attendance_btn_{{ $studentId }}"
                                            class="admin-attendance-btn {{ $isAbsent ? 'admin-absent-btn' : 'admin-present-btn' }}"
                                            onclick="toggleAdminAttendance('{{ $studentId }}')">
                                        {{ $isAbsent ? 'ABSENT' : 'PRESENT' }}
                                    </button>
                                </td>

                                @if($showOptionalColumn)
                                    <td class="admin-center">
                                        <input type="hidden"
                                               name="is_optional[{{ $studentId }}]"
                                               id="admin_optional_{{ $studentId }}"
                                               value="{{ $isOptional ? 1 : 0 }}">
                                        <button type="button"
                                                id="admin_optional_btn_{{ $studentId }}"
                                                class="admin-optional-btn {{ $isOptional ? 'admin-optional-active-btn' : '' }}"
                                                onclick="toggleAdminOptional('{{ $studentId }}')">
                                            {{ $isOptional ? 'OPTIONAL' : 'NORMAL' }}
                                        </button>
                                    </td>
                                @endif

                                @if($showTheory)
                                    <td class="admin-center">{{ (int) $theoryMaxMarks }}</td>
                                    <td class="admin-center">{{ (int) $theoryPassingMarks }}</td>
                                    <td class="admin-center">
                                        <input type="text"
                                               name="theory_marks[{{ $studentId }}]"
                                               value="{{ old('theory_marks.' . $studentId, $H::formatIntegerMark($theoryValue)) }}"
                                               inputmode="numeric"
                                               pattern="[0-9]*"
                                               autocomplete="off"
                                               maxlength="4"
                                               data-max="{{ (int) $theoryMaxMarks }}"
                                               class="admin-mark-input admin-mark-input-{{ $studentId }} {{ $isAbsent ? 'admin-absent-input' : '' }}"
                                               data-student="{{ $studentId }}"
                                               {{ ($isAbsent || $isOptional) ? 'readonly' : '' }}>
                                    </td>
                                @endif

                                @if($showOral)
                                    <td class="admin-center">{{ (int) $oralMaxMarks }}</td>
                                    <td class="admin-center">{{ (int) $oralPassingMarks }}</td>
                                    <td class="admin-center">
                                        <input type="text"
                                               name="oral_marks[{{ $studentId }}]"
                                               value="{{ old('oral_marks.' . $studentId, $H::formatIntegerMark($oralValue)) }}"
                                               inputmode="numeric"
                                               pattern="[0-9]*"
                                               autocomplete="off"
                                               maxlength="4"
                                               data-max="{{ (int) $oralMaxMarks }}"
                                               class="admin-mark-input admin-mark-input-{{ $studentId }} {{ $isAbsent ? 'admin-absent-input' : '' }}"
                                               data-student="{{ $studentId }}"
                                               {{ ($isAbsent || $isOptional) ? 'readonly' : '' }}>
                                    </td>
                                @endif

                                @if($showPractical)
                                    <td class="admin-center">{{ (int) $practicalMaxMarks }}</td>
                                    <td class="admin-center">{{ (int) $practicalPassingMarks }}</td>
                                    <td class="admin-center">
                                        <input type="text"
                                               name="practical_marks[{{ $studentId }}]"
                                               value="{{ old('practical_marks.' . $studentId, $H::formatIntegerMark($practicalValue)) }}"
                                               inputmode="numeric"
                                               pattern="[0-9]*"
                                               autocomplete="off"
                                               maxlength="4"
                                               data-max="{{ (int) $practicalMaxMarks }}"
                                               class="admin-mark-input admin-mark-input-{{ $studentId }} {{ $isAbsent ? 'admin-absent-input' : '' }}"
                                               data-student="{{ $studentId }}"
                                               {{ ($isAbsent || $isOptional) ? 'readonly' : '' }}>
                                    </td>
                                @endif

                                <td class="admin-center">
                                    <span id="admin_status_{{ $studentId }}"
                                          class="{{ $isOptional ? 'admin-status-optional' : ($isAbsent ? 'admin-status-absent' : 'admin-status-present') }}">
                                        {{ $isOptional ? 'OPTIONAL' : ($isAbsent ? 'ABSENT' : 'PRESENT') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="admin-action-row">
                    <button type="submit" class="admin-erp-btn admin-btn-blue" id="adminUpdateMarksButton">
                        Update Marks
                    </button>
                    <span class="admin-student-count">{{ $studentCount }} Students</span>

                    @if($existingMarks->count() === 0)
                        <span class="admin-action-note">
                            No saved marks existed. Update Marks will create the mark records.
                        </span>
                    @else
                        <span class="admin-action-note">
                            Existing marks can be corrected by Administrator.
                        </span>
                    @endif
                </div>
            </form>

            <div class="admin-info-box admin-warning-box" style="margin-top:14px;margin-bottom:0;">
                <strong>Administrator Access:</strong>
                Marks can be entered or corrected for <strong>PENDING</strong> and <strong>COMPLETED</strong> assignments.
                Administrator changes do not change the current teaching assignment status.
            </div>
        </div>

    @elseif($teacherSubjectAllocation && $studentCount === 0)
        <div class="erp-card"
             style="margin-top:14px;padding:14px 18px;background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;font-size:12px;font-weight:600;border-radius:8px;">
            No students were found for the selected class/division in the Old ERP student source.
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const academicYear = document.getElementById('admin_academic_year_id');
    const exam         = document.getElementById('admin_exam_master_id');
    const assignment   = document.getElementById('admin_teacher_subject_allocation_id');
    const loadButton   = document.getElementById('adminLoadMarksButton');
    const filterForm   = document.getElementById('adminMarksFilterForm');

    function updateLoadButton() {
        if (!loadButton) return;
        loadButton.disabled = !(exam && exam.value && assignment && assignment.value);
    }

    if (academicYear) {
        academicYear.addEventListener('change', function () {
            if (exam && exam.value && filterForm) filterForm.submit();
            else {
                if (assignment) { assignment.value = ''; assignment.disabled = true; }
                updateLoadButton();
            }
        });
    }

    if (exam) {
        exam.addEventListener('change', function () {
            if (filterForm) filterForm.submit();
        });
    }

    if (assignment) {
        assignment.addEventListener('change', updateLoadButton);
    }

    updateLoadButton();

    // ------- integer-only mark input -------
    document.querySelectorAll('.admin-mark-input').forEach(function (input) {
        input.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '');
            const max = parseInt(this.dataset.max || '0', 10);
            if (this.value !== '' && max > 0 && parseInt(this.value, 10) > max) {
                this.value = String(max);
            }
            this.style.border = this.value === '' ? '1px solid #9ca3af' : '1px solid #16a34a';
        });
        input.addEventListener('keydown', function (e) {
            const allowed = ['Backspace','Delete','Tab','ArrowLeft','ArrowRight','Home','End'];
            if (allowed.includes(e.key)) return;
            if (!/^[0-9]$/.test(e.key)) e.preventDefault();
        });
    });

    // ------- update form -------
    const adminMarksForm = document.getElementById('adminMarksForm');
    if (adminMarksForm) {
        adminMarksForm.addEventListener('submit', function (e) {
            e.preventDefault();
            let hasError = false;
            document.querySelectorAll('.admin-mark-input').forEach(function (input) {
                if (input.readOnly) return;
                const v = input.value.trim();
                if (v === '') return;
                if (!/^\d+$/.test(v)) { hasError = true; input.style.border = '2px solid #dc2626'; return; }
                const max = parseInt(input.dataset.max || '0', 10);
                if (max > 0 && parseInt(v, 10) > max) { hasError = true; input.style.border = '2px solid #dc2626'; }
            });
            if (hasError) {
                Swal.fire({ icon:'error', title:'Validation Error', text:'Please enter valid whole-number marks only.' });
                return;
            }
            Swal.fire({
                icon:'warning', title:'Update Marks',
                text:'Are you sure you want to update these marks?',
                showCancelButton:true, confirmButtonText:'Yes, Update Marks', cancelButtonText:'Cancel'
            }).then(r => { if (r.isConfirmed) adminMarksForm.submit(); });
        });
    }
});

/* ------- Optional toggle ------- */
function toggleAdminOptional(studentId) {
    const optionalField = document.getElementById('admin_optional_' + studentId);
    const optionalButton = document.getElementById('admin_optional_btn_' + studentId);
    const absentField = document.getElementById('admin_absent_' + studentId);
    const attendanceButton = document.getElementById('admin_attendance_btn_' + studentId);
    const status = document.getElementById('admin_status_' + studentId);
    const inputs = document.querySelectorAll('.admin-mark-input-' + studentId);

    if (!optionalField || !optionalButton || !status) return;

    if (absentField && absentField.value === '1') {
        Swal.fire({ icon:'warning', title:'Student is Absent',
            text:'An absent student cannot be marked as Optional.', confirmButtonText:'OK' });
        return;
    }

    if (optionalField.value === '0') {
        Swal.fire({
            icon:'warning', title:'Mark Student Optional?',
            text:'This student will be excluded from marks calculation for this subject.',
            showCancelButton:true, confirmButtonText:'Yes, Optional', cancelButtonText:'Cancel',
            confirmButtonColor:'#d97706'
        }).then(function (r) {
            if (!r.isConfirmed) return;
            optionalField.value = '1';
            if (absentField) absentField.value = '0';
            if (attendanceButton) {
                attendanceButton.textContent = 'PRESENT';
                attendanceButton.classList.remove('admin-absent-btn');
                attendanceButton.classList.add('admin-present-btn');
            }
            optionalButton.textContent = 'OPTIONAL';
            optionalButton.classList.add('admin-optional-active-btn');
            status.textContent = 'OPTIONAL';
            status.classList.remove('admin-status-present','admin-status-absent');
            status.classList.add('admin-status-optional');
            inputs.forEach(function (i) {
                i.value = '0'; i.readOnly = true;
                i.classList.remove('admin-absent-input');
                i.classList.add('admin-optional-input');
            });
        });
        return;
    }

    Swal.fire({
        icon:'question', title:'Remove Optional Status?',
        text:'This student will become a normal PRESENT student.',
        showCancelButton:true, confirmButtonText:'Yes, Normal', cancelButtonText:'Cancel'
    }).then(function (r) {
        if (!r.isConfirmed) return;
        optionalField.value = '0';
        optionalButton.textContent = 'NORMAL';
        optionalButton.classList.remove('admin-optional-active-btn');
        status.textContent = 'PRESENT';
        status.classList.remove('admin-status-optional','admin-status-absent');
        status.classList.add('admin-status-present');
        if (absentField) absentField.value = '0';
        if (attendanceButton) {
            attendanceButton.textContent = 'PRESENT';
            attendanceButton.classList.remove('admin-absent-btn');
            attendanceButton.classList.add('admin-present-btn');
        }
        inputs.forEach(function (i) {
            i.readOnly = false;
            i.classList.remove('admin-optional-input');
            if (i.value === '0') i.value = '';
        });
    });
}

/* ------- Attendance toggle ------- */
function toggleAdminAttendance(studentId) {
    const hidden = document.getElementById('admin_absent_' + studentId);
    const button = document.getElementById('admin_attendance_btn_' + studentId);
    const status = document.getElementById('admin_status_' + studentId);
    const inputs = document.querySelectorAll('.admin-mark-input-' + studentId);
    if (!hidden || !button || !status) return;

    const optionalField = document.getElementById('admin_optional_' + studentId);
    if (optionalField && optionalField.value === '1') {
        Swal.fire({
            icon:'question', title:'Student is Optional',
            text:'Remove Optional status before changing Attendance?',
            showCancelButton:true, confirmButtonText:'Yes, Continue', cancelButtonText:'Cancel'
        }).then(function (r) { if (r.isConfirmed) toggleAdminOptional(studentId); });
        return;
    }

    if (hidden.value === '1') {
        hidden.value = '0';
        button.textContent = 'PRESENT';
        button.classList.remove('admin-absent-btn');
        button.classList.add('admin-present-btn');
        status.textContent = 'PRESENT';
        status.classList.remove('admin-status-absent');
        status.classList.add('admin-status-present');
        inputs.forEach(function (i) {
            i.readOnly = false;
            i.classList.remove('admin-absent-input');
        });
        return;
    }

    hidden.value = '1';
    button.textContent = 'ABSENT';
    button.classList.remove('admin-present-btn');
    button.classList.add('admin-absent-btn');
    status.textContent = 'ABSENT';
    status.classList.remove('admin-status-present');
    status.classList.add('admin-status-absent');
    inputs.forEach(function (i) {
        i.value = '0'; i.readOnly = true;
        i.classList.add('admin-absent-input');
    });
}
</script>

</x-app-layout>