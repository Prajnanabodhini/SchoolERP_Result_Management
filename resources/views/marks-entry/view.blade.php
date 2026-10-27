<x-app-layout>

@php
    $H = \App\Helpers\MarksEntryBladeHelper::class;

    /* Safe defaults */
    $teacherSubjectAllocation = $teacherSubjectAllocation ?? $selectedTsa ?? null;
    $selectedTsa              = $selectedTsa              ?? $teacherSubjectAllocation;
    $selectedSubject          = $selectedSubject          ?? null;
    $exam                     = $exam                     ?? null;
    $records                  = $records                  ?? collect();
    $academicYears            = $academicYears            ?? collect();
    $exams                    = $exams                    ?? collect();
    $assignments              = $assignments              ?? collect();
    $error                    = $error                    ?? '';
    $showTheory               = $showTheory               ?? false;
    $showOral                 = $showOral                 ?? false;
    $showPractical            = $showPractical            ?? false;
    $isOptionalEnabled        = $isOptionalEnabled        ?? false;
    $selectedClassAllocation  = $selectedClassAllocation  ?? null;

    if (!$showTheory && !$showOral && !$showPractical) {
        $flags = $H::resolveComponentFlags(null, null, null, $records);
        $showTheory    = $flags['theory'];
        $showOral      = $flags['oral'];
        $showPractical = $flags['practical'];
    }

    $showOptionalColumn = $H::shouldShowOptionalColumn(
        $isOptionalEnabled ?? false,
        $H::getSelectedStandardId(
            $selectedClassAllocation ?? null,
            $teacherSubjectAllocation ?? null
        )
    );

    /* ⭐ Component max/pass — controller-provided values win */
    $theoryMaxMarks        = $theoryMaxMarks        ?? null;
    $theoryPassingMarks    = $theoryPassingMarks    ?? null;
    $oralMaxMarks          = $oralMaxMarks          ?? null;
    $oralPassingMarks      = $oralPassingMarks      ?? null;
    $practicalMaxMarks     = $practicalMaxMarks     ?? null;
    $practicalPassingMarks = $practicalPassingMarks ?? null;

    $resolveTheoryMax = function ($row) use (&$theoryMaxMarks) {
        if ($theoryMaxMarks === null) $theoryMaxMarks = (float) ($row->theory_max_marks ?? 0);
        return (int) $theoryMaxMarks;
    };
    $resolveTheoryPass = function ($row) use (&$theoryPassingMarks) {
        if ($theoryPassingMarks === null) $theoryPassingMarks = (float) ($row->theory_passing_marks ?? 0);
        return (int) $theoryPassingMarks;
    };
    $resolveOralMax = function ($row) use (&$oralMaxMarks) {
        if ($oralMaxMarks === null) $oralMaxMarks = (float) ($row->oral_max_marks ?? 0);
        return (int) $oralMaxMarks;
    };
    $resolveOralPass = function ($row) use (&$oralPassingMarks) {
        if ($oralPassingMarks === null) $oralPassingMarks = (float) ($row->oral_passing_marks ?? 0);
        return (int) $oralPassingMarks;
    };
    $resolvePracticalMax = function ($row) use (&$practicalMaxMarks) {
        if ($practicalMaxMarks === null) $practicalMaxMarks = (float) ($row->practical_max_marks ?? 0);
        return (int) $practicalMaxMarks;
    };
    $resolvePracticalPass = function ($row) use (&$practicalPassingMarks) {
        if ($practicalPassingMarks === null) $practicalPassingMarks = (float) ($row->practical_passing_marks ?? 0);
        return (int) $practicalPassingMarks;
    };

    $totalMaxMarks = $totalMaxMarks ?? 0;
    if ($totalMaxMarks == 0) {
        if ($showTheory)    $totalMaxMarks += (int) ($theoryMaxMarks    ?? 0);
        if ($showOral)      $totalMaxMarks += (int) ($oralMaxMarks      ?? 0);
        if ($showPractical) $totalMaxMarks += (int) ($practicalMaxMarks ?? 0);
    }

    /* Tab URLs */
    $filterParams = array_filter([
        'exam_master_id'                => request('exam_master_id'),
        'teacher_subject_allocation_id' => request('teacher_subject_allocation_id'),
    ], fn ($v) => $v !== null && $v !== '');

    $marksEntryUrl = route('marks-entry.index') . $H::querySuffix($filterParams);
    $viewMarksUrl  = request()->fullUrl();
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
        -moz-appearance: none;
        outline: none;
        transition: border-color .15s, box-shadow .15s;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%234B5563' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 9px center;
        background-size: 12px;
    }
    .filter-select:focus { border-color: #2563EB; box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15); }
    .filter-select:disabled { background-color: #F3F4F6; color: #6B7280; cursor: not-allowed; }

    .filter-group.w-academic-year { width: 140px; }
    .filter-group.w-exam          { width: 230px; }
    .filter-group.w-assignment    { width: 260px; }
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
    .erp-btn:hover, .erp-btn-save:hover { background: #15803D !important; border-color: #166534 !important; }
    .erp-btn.erp-btn-reset { background: #6B7280 !important; border-color: #4B5563 !important; }
    .erp-btn.erp-btn-reset:hover { background: #4B5563 !important; border-color: #374151 !important; }

    .summary-row { margin-top: 12px; display: flex; gap: 8px; flex-wrap: wrap; }

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

    .view-only-box {
        margin-top: 12px;
        padding: 10px 14px;
        border-radius: 6px;
        background: #eff6ff;
        border: 1px solid #93c5fd;
        color: #1e40af;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        font-size: 11px;
        font-weight: 700;
    }

    .error-box, .warning-box, .success-box {
        margin-bottom: 12px;
        padding: 10px 14px;
        border-radius: 8px;
        font-size: 12.5px;
        font-weight: 700;
        line-height: 1.5;
    }
    .error-box   { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; }
    .warning-box { background: #fffbeb; border: 1px solid #fcd34d; color: #92400e; }
    .success-box { background: #dcfce7; border: 1px solid #86efac; color: #166534; }

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

    .status-present  { color: #16A34A; font-weight: 700; font-size: 10.5px; letter-spacing: 0.2px; }
    .status-absent   { color: #DC2626; font-weight: 700; font-size: 10.5px; letter-spacing: 0.2px; }
    .status-optional { color: #D97706; font-weight: 700; font-size: 10.5px; letter-spacing: 0.2px; }

    .readonly-mark { font-weight: 700; color: #111827; font-size: 12px; }
    .readonly-mark.absent   { color: #DC2626 !important; }
    .readonly-mark.optional { color: #D97706 !important; }

    .optional-header { background: #D97706 !important; color: #ffffff !important; border-color: #B45309 !important; }
    .th-obtained { background: #1e40af !important; }

    .total-header { background: #7c2d12 !important; color: #fff !important; }
    .row-total-cell {
        background: #FFF7ED !important;
        color: #C2410C !important;
        font-weight: 800 !important;
        font-size: 12.5px !important;
    }
    .row-total-cell.total-absent   { color: #DC2626 !important; background: #FEF2F2 !important; }
    .row-total-cell.total-optional { color: #D97706 !important; background: #FFFBEB !important; }

    .no-record {
        border: 1px solid #d1d5db;
        padding: 20px 16px;
        text-align: center;
        color: #92400E;
        background: #fffbeb;
        font-weight: 600;
        font-size: 13px;
    }

    @media (max-width: 1100px) {
        .filter-group.w-academic-year { width: 130px; }
        .filter-group.w-exam          { width: 200px; }
        .filter-group.w-assignment    { width: 230px; }
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

    <div class="tabs-bar">
        <a href="{{ $marksEntryUrl }}" class="tab">Marks Entry</a>
        <a href="{{ $viewMarksUrl }}" class="tab active">View Marks</a>
    </div>

    @if(session('success'))
        <div class="success-box">✓ {{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="error-box"><strong>Error:</strong> {{ session('error') }}</div>
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

    <div class="erp-card">
        <form method="GET" action="{{ route('marks-entry.view') }}" id="marksFilterForm">
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
                            {!! $H::renderExamOptions($exams, request('exam_master_id')) !!}
                        </select>
                    </div>
                </div>

                <div class="filter-group w-assignment">
                    <label class="filter-label" for="teacher_subject_allocation_id">Teaching Assignment</label>
                    <div class="select-wrapper">
                        <select name="teacher_subject_allocation_id" id="teacher_subject_allocation_id"
                                class="filter-select"
                                {{ !request('exam_master_id') ? 'disabled' : '' }}>
                            @if(!request('exam_master_id'))
                                <option value="">Select Exam First</option>
                            @elseif($assignments->isEmpty())
                                <option value="">No Teaching Assignment</option>
                            @else
                                <option value="">Select Teaching Assignment</option>
                                {!! $H::renderAssignmentOptions($assignments, request('teacher_subject_allocation_id')) !!}
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
                    <a href="{{ route('marks-entry.view') }}" class="erp-btn erp-btn-reset">Reset</a>
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
                <span class="summary-badge total-badge">Students: {{ $records->count() }}</span>
                <span class="summary-badge total-badge">Teacher: {{ $teacherName }}</span>
                <span class="summary-badge total-badge">Subject: {{ $subjectName }}</span>
                <span class="summary-badge total-badge">Class: {{ $standardName }}@if($divisionName) - {{ $divisionName }}@endif</span>
                <span class="summary-badge total-badge">Exam: {{ $exam->display_exam_name ?? $exam->exam_name }}</span>
                <span class="summary-badge completed-badge">Total Max: {{ (int) $totalMaxMarks }}</span>
                @if($showOptionalColumn)
                    <span class="summary-badge pending-badge">Optional Selection: Enabled</span>
                @endif
            </div>

            <div class="view-only-box">
                View Only — Marks cannot be modified from this page.
            </div>
        @endif
    </div>

    @if($records->count() > 0)
        <div class="erp-card">
            <div class="result-table-wrapper">
                <table class="result-table">
                    <colgroup>
                        <col style="width:5%;">     {{-- GR --}}
                        <col style="width:5%;">     {{-- Roll --}}
                        <col style="width:20%;">    {{-- Name --}}
                        <col style="width:7.5%;">   {{-- Attendance --}}
                        @if($showOptionalColumn) <col style="width:6.5%;"> @endif {{-- Optional --}}

                        @if($showTheory)
                            <col style="width:4.5%;"> <col style="width:4.5%;"> <col style="width:5.5%;">
                        @endif
                        @if($showOral)
                            <col style="width:4.5%;"> <col style="width:4.5%;"> <col style="width:5.5%;">
                        @endif
                        @if($showPractical)
                            <col style="width:4.5%;"> <col style="width:4.5%;"> <col style="width:5.5%;">
                        @endif

                        <col style="width:7%;">     {{-- Total --}}
                        <col style="width:7%;">     {{-- Status --}}
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
                    @foreach($records as $row)
                        @php
                            $fullName = $H::getFullStudentName($row);
                            $grNo     = $H::getGrNo($row);
                            $rollNo   = $H::getRollNo($row);

                            $isOptional = isset($row->is_optional) && (int) $row->is_optional === 1;
                            $isAbsent   = !$isOptional && $H::isAbsent($row);

                            $theoryVal    = $row->theory_obtained_marks    ?? null;
                            $oralVal      = $row->oral_obtained_marks      ?? null;
                            $practicalVal = $row->practical_obtained_marks ?? null;

                            $rowTotal = 0;
                            if (!$isOptional && !$isAbsent) {
                                if ($showTheory)    $rowTotal += (float) ($theoryVal    ?? 0);
                                if ($showOral)      $rowTotal += (float) ($oralVal      ?? 0);
                                if ($showPractical) $rowTotal += (float) ($practicalVal ?? 0);
                            }

                            $rowTotalDisplay = $isOptional
                                ? 'OPT'
                                : ($isAbsent ? 'AB' : $rowTotal);

                            $rowTotalClass = 'row-total-cell'
                                . ($isOptional ? ' total-optional' : ($isAbsent ? ' total-absent' : ''));
                        @endphp

                        <tr>
                            <td class="center">{{ $grNo }}</td>
                            <td class="center">{{ $rollNo }}</td>

                            <td class="student-name" title="{{ $fullName }}">{{ $fullName ?: '-' }}</td>

                            <td class="center">
                                @if($isAbsent)
                                    <span class="status-absent">ABSENT</span>
                                @else
                                    <span class="status-present">PRESENT</span>
                                @endif
                            </td>

                            @if($showOptionalColumn)
                                <td class="center">
                                    @if($isOptional)
                                        <span class="status-optional">OPTIONAL</span>
                                    @else
                                        <span class="status-present">NORMAL</span>
                                    @endif
                                </td>
                            @endif

                            @if($showTheory)
                                <td class="center">{{ $resolveTheoryMax($row) }}</td>
                                <td class="center">{{ $resolveTheoryPass($row) }}</td>
                                <td class="center">
                                    @if($isOptional)
                                        <span class="readonly-mark optional">OPT</span>
                                    @elseif($isAbsent)
                                        <span class="readonly-mark absent">AB</span>
                                    @else
                                        <span class="readonly-mark">{{ $H::displayMark($theoryVal) }}</span>
                                    @endif
                                </td>
                            @endif

                            @if($showOral)
                                <td class="center">{{ $resolveOralMax($row) }}</td>
                                <td class="center">{{ $resolveOralPass($row) }}</td>
                                <td class="center">
                                    @if($isOptional)
                                        <span class="readonly-mark optional">OPT</span>
                                    @elseif($isAbsent)
                                        <span class="readonly-mark absent">AB</span>
                                    @else
                                        <span class="readonly-mark">{{ $H::displayMark($oralVal) }}</span>
                                    @endif
                                </td>
                            @endif

                            @if($showPractical)
                                <td class="center">{{ $resolvePracticalMax($row) }}</td>
                                <td class="center">{{ $resolvePracticalPass($row) }}</td>
                                <td class="center">
                                    @if($isOptional)
                                        <span class="readonly-mark optional">OPT</span>
                                    @elseif($isAbsent)
                                        <span class="readonly-mark absent">AB</span>
                                    @else
                                        <span class="readonly-mark">{{ $H::displayMark($practicalVal) }}</span>
                                    @endif
                                </td>
                            @endif

                            <td class="{{ $rowTotalClass }}">
                                {{ $rowTotalDisplay }}
                            </td>

                            <td class="center">
                                @if($isOptional)
                                    <span class="status-optional">OPTIONAL</span>
                                @elseif($isAbsent)
                                    <span class="status-absent">ABSENT</span>
                                @else
                                    <span class="status-present">PRESENT</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    @elseif(request()->filled('teacher_subject_allocation_id'))
        <div class="erp-card">
            <div class="no-record">
                No marks have been entered for the selected teaching assignment.
            </div>
        </div>
    @else
        <div class="erp-card">
            <div class="no-record" style="background: #eff6ff; border-color: #93c5fd; color: #1e40af;">
                Select your filters above and click <strong>Load</strong> to view marks.
            </div>
        </div>
    @endif

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const yearSelect       = document.getElementById('academic_year_id');
    const examSelect       = document.getElementById('exam_master_id');
    const assignmentSelect = document.getElementById('teacher_subject_allocation_id');
    const filterForm       = document.getElementById('marksFilterForm');

    if (!filterForm) return;

    if (yearSelect) {
        yearSelect.addEventListener('change', function () {
            if (assignmentSelect) {
                assignmentSelect.innerHTML = '<option value="">Select Exam First</option>';
                assignmentSelect.disabled = true;
            }
            filterForm.submit();
        });
    }

    if (examSelect) {
        examSelect.addEventListener('change', function () {
            if (assignmentSelect) {
                assignmentSelect.innerHTML = '<option value="">Loading assignments...</option>';
                assignmentSelect.disabled = true;
            }
            filterForm.submit();
        });
    }

});
</script>

</x-app-layout>