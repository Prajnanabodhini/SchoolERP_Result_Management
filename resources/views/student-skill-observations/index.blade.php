<x-app-layout>

<style>
/* =========================================================
   PAGE
   ========================================================= */
.skill-page,
.skill-page * {
    font-family: Arial, sans-serif !important;
    font-size: 13px;
    box-sizing: border-box;
}

.skill-page h2 {
    font-size: 21px !important;
    font-weight: 700 !important;
    color: #1D4ED8 !important;
    margin: 0 0 14px 0;
}

/* =========================================================
   CARDS
   ========================================================= */
.skill-card {
    background: #ffffff;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 14px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    margin-bottom: 14px;
}

/* =========================================================
   SECTION TITLES
   ========================================================= */
.filter-section-title {
    font-size: 12px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

/* =========================================================
   FILTER ROW
   ========================================================= */
.filter-row {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    flex-wrap: nowrap;
    margin-bottom: 0;
    width: 100%;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
    flex: 1 1 0;
    min-width: 0;
}

.filter-group.filter-group-btn {
    flex: 0 0 auto;
}

.filter-label {
    font-size: 11px;
    font-weight: 700;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    white-space: nowrap;
}

.filter-label .req { color: #DC2626; }

.filter-select {
    height: 34px;
    padding: 4px 26px 4px 8px;
    font-size: 12px;
    font-weight: 600;
    border: 1px solid #9CA3AF;
    border-radius: 5px;
    background: #ffffff;
    color: #111827;
    width: 100%;
    min-width: 0;
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3e%3cpath fill='%236b7280' d='M0 0l5 6 5-6z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 8px center;
    background-size: 8px 5px;
}
.filter-select:focus {
    border-color: #2563EB;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
}

/* =========================================================
   ACTION BUTTONS
   ========================================================= */
.action-row {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 4px;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 34px;
    min-width: 110px;
    padding: 0 16px;
    border-radius: 5px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    border: 1px solid transparent;
    transition: background .15s, transform .1s;
    box-sizing: border-box;
    white-space: nowrap;
}
.btn-primary   { background: #16A34A; color: #ffffff; border-color: #15803D; }
.btn-primary:hover   { background: #15803D; }

.btn-secondary { background: #6B7280; color: #ffffff; border-color: #4B5563; }
.btn-secondary:hover { background: #4B5563; }

.btn-info      { background: #2563EB; color: #ffffff; border-color: #1D4ED8; }
.btn-info:hover      { background: #1D4ED8; }

.btn-print     { background: #2563EB; color: #ffffff; border-color: #1D4ED8; }
.btn-print:hover     { background: #1D4ED8; }

/* =========================================================
   HEADER
   ========================================================= */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 14px;
}
.page-header h2 { margin: 0 0 4px 0 !important; }
.page-header p  { font-size: 13px; color: #6B7280; margin: 0; }

/* =========================================================
   ALERT
   ========================================================= */
.alert {
    padding: 10px 14px;
    border-radius: 6px;
    margin-bottom: 14px;
    font-weight: 700;
    font-size: 13px;
}
.alert-success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }

/* =========================================================
   INFO BANNER
   ========================================================= */
.info-banner {
    background: #eff6ff;
    border: 1px solid #93c5fd;
    border-radius: 6px;
    padding: 10px 14px;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 14px;
    font-size: 13px;
}
.info-banner .info-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #1e40af;
    font-weight: 700;
}
.info-banner .info-item .info-key {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    color: #6B7280;
    font-weight: 700;
}
.info-banner .info-sep { color: #93c5fd; }
.info-banner .grade-legend {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    font-size: 11px;
    color: #1e40af;
}
.info-banner .grade-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #ffffff;
    border: 1px solid #93c5fd;
    border-radius: 4px;
    padding: 2px 6px;
    font-weight: 700;
    color: #1e40af;
}
.info-banner .grade-pill span:first-child { font-size: 11px; font-weight: 800; }

/* =========================================================
   TABLE WRAPPER
   ========================================================= */
.table-wrapper {
    background: #ffffff;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    margin-bottom: 14px;
    width: 100%;
}

/* =========================================================
   ENTRY TABLE
   ========================================================= */
.entry-table {
    width: 100%;
    table-layout: fixed;
    border-collapse: collapse;
    margin: 0;
    font-size: 12px;
}

.entry-table col.col-gr     { width: 58px; }
.entry-table col.col-roll   { width: 58px; }
.entry-table col.col-name   { width: 24%; }
.entry-table col.col-pa     { width: 88px; }
.entry-table col.col-grade  { width: auto; }
.entry-table col.col-avg    { width: 80px; }
.entry-table col.col-remark { width: 18%; }
.entry-table col.col-status { width: 88px; }

.entry-table thead tr {
    background: #1e3a8a;
    color: #ffffff;
}

.entry-table th {
    padding: 8px 5px;
    font-size: 10.5px;
    font-weight: 700;
    text-align: left;
    border: 1px solid #1e40af;
    color: #ffffff;
    text-transform: uppercase;
    letter-spacing: 0.2px;
    white-space: normal;
    overflow-wrap: break-word;
    word-break: break-word;
    line-height: 1.15;
    vertical-align: middle;
    overflow: visible;
    text-overflow: clip;
}
.entry-table th.center { text-align: center; }

.entry-table th.th-grade      { background: #1e40af; }
.entry-table th.th-avg        { background: #92400e; }
.entry-table th.th-remark     { background: #166534; }
.entry-table th.th-attendance { background: #c2410c; }
.entry-table th.th-status     { background: #1e3a8a; }

.entry-table td {
    padding: 6px 4px;
    border: 1px solid #d1d5db;
    vertical-align: middle;
    color: #111827;
    font-size: 12px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    transition: background-color 0.3s ease;
}
.entry-table td.center { text-align: center; }

.entry-table td.student-name {
    font-weight: 600;
    white-space: normal;
    word-wrap: break-word;
    line-height: 1.2;
}
.entry-table tbody tr:nth-child(even) { background: #f9fafb; }
.entry-table tbody tr:hover { background: #fef9c3; }

/* =========================================================
   PRESENT / ABSENT TOGGLE
   ========================================================= */
.skill-toggle-btn {
    width: 100%;
    max-width: 78px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    font-size: 10px;
    font-weight: 700;
    color: #ffffff;
    border: 0;
    border-radius: 4px;
    cursor: pointer;
    white-space: nowrap;
    transition: background-color .15s ease;
    padding: 0;
}
.skill-present-btn { background-color: #16a34a; }
.skill-present-btn:hover { background-color: #15803d; }
.skill-absent-btn { background-color: #dc2626; }
.skill-absent-btn:hover { background-color: #b91c1c; }
.skill-toggle-btn .dot {
    display: inline-block;
    width: 6px; height: 6px;
    border-radius: 50%;
    background-color: #ffffff;
    opacity: .9;
    flex-shrink: 0;
}

/* =========================================================
   GRADE DROPDOWN
   ========================================================= */
.grade-select {
    width: 100%;
    max-width: 100%;
    height: 28px;
    padding: 0 16px 0 4px;
    font-size: 12px;
    font-weight: 700;
    text-align: center;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    background-color: #ffffff;
    color: #111827;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3e%3cpolyline points='6 9 12 15 18 9'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 2px center;
    background-size: 9px;
    outline: none;
}
.grade-select:focus {
    border-color: #2563EB;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
}

/* =========================================================
   WRITTEN MARKS INPUT (plain text box — no spinners)
   ========================================================= */
.mark-input {
    width: 100%;
    max-width: 80px;
    height: 28px;
    padding: 2px 6px;
    font-size: 12px;
    font-weight: 700;
    text-align: center;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    background: #fff;
    color: #111827;
    outline: none;
}
.mark-input:focus {
    border-color: #2563EB;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
}

/* =========================================================
   AVERAGE GRADE
   ========================================================= */
.avg-grade {
    width: 100%;
    max-width: 100%;
    height: 28px;
    padding: 0 4px;
    font-size: 12px;
    font-weight: 800;
    text-align: center;
    border: 1px solid #fbbf24;
    background: #fef3c7;
    color: #92400e;
    border-radius: 4px;
    cursor: not-allowed;
    outline: none;
}

/* =========================================================
   REMARK TEXT INPUT
   ========================================================= */
.remark-input {
    width: 100%;
    max-width: 100%;
    height: 28px;
    padding: 2px 6px;
    font-size: 12px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    background: #fff;
    outline: none;
}
.remark-input:focus {
    border-color: #2563EB;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
}

/* =========================================================
   STATUS CELL
   ========================================================= */
.status-cell {
    font-weight: 700;
    font-size: 11px;
    text-transform: uppercase;
    white-space: nowrap;
}
.status-cell.status-present { color: #16a34a; }
.status-cell.status-absent  { color: #dc2626; }

/* =========================================================
   EMPTY STATE
   ========================================================= */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    text-align: center;
    background: #ffffff;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}
.empty-state svg {
    width: 72px !important;
    height: 72px !important;
    max-width: 72px !important;
    max-height: 72px !important;
    display: block;
    margin: 0 auto 14px auto;
    opacity: 0.55;
}
.empty-state-title {
    font-size: 15px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 4px;
}
.empty-state-subtitle { font-size: 13px; color: #6B7280; }

/* =========================================================
   RESPONSIVE
   ========================================================= */
@media (max-width: 1200px) {
    .filter-row { flex-wrap: wrap; }
    .filter-group { flex: 1 1 180px; }
}

@media (max-width: 900px) {
    .info-banner .grade-legend { margin-left: 0; }
    .filter-group { flex: 1 1 100%; }
    .btn { width: 100%; }

    .table-wrapper { overflow-x: auto; }
    .entry-table { table-layout: auto; min-width: 900px; }
}
</style>


@php
    $gradeScale  = \App\Helpers\SkillCriteriaHelper::gradeScale();
    $gradeLabels = \App\Helpers\SkillCriteriaHelper::gradeLabels();

    /* Split criteria into "grade", "mark", and "remark" categories */
    $gradeCriteria  = [];
    $remarkCriteria = [];
    $markCriteria   = [];

    foreach ($criteria as $c) {
        if (stripos($c, 'remark') !== false) {
            $remarkCriteria[] = $c;
        } elseif (stripos($c, 'written') !== false || stripos($c, 'marks') !== false) {
            $markCriteria[] = $c;
        } else {
            $gradeCriteria[] = $c;
        }
    }

    /* Has the user loaded students? */
    $hasData = count($students) && !empty($criteria);

    /* Skill Teacher name — resolved by the controller */
    $skillTeacherName = $skillTeacherName ?? '-';

    /* Query params for the Print link */
    $printParams = request()->only([
        'academic_year_id',
        'exam_master_id',
        'standard_id',
        'division_id',
        'subject_id',
    ]);
@endphp


<div class="skill-page p-3">

    {{-- HEADER — Back button + Print button + Summary View --}}
    <div class="page-header">
        <div>
            <h2>Student Skill Observation</h2>
            <p>Enter skill observations for students by skill subject</p>
        </div>

        <div class="action-row no-print">

            {{-- ADDED BACK BUTTON --}}
            <a href="{{ route('exam-progress.index', array_merge(request()->only(['academic_year_id', 'standard_id', 'division_id', 'exam_master_id']), ['subject_type' => 'skill'])) }}" class="btn btn-secondary">
                ⬅ Back
            </a>

            @if($hasData)
                {{-- PRINT (skill-subject specific) --}}
                <a href="{{ route('student-skill-observations.print-entry', $printParams) }}"
                   target="_blank"
                   class="btn btn-print">
                    🖨 Print
                </a>
            @endif

            {{-- SUMMARY VIEW --}}
            <a href="{{ route('student-skill-observations.summary', request()->only(['academic_year_id','exam_master_id','standard_id','division_id'])) }}"
               class="btn btn-info">
                📊 Summary View
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(!empty($erpError))
        <div class="alert" style="background:#fee2e2;border-color:#fca5a5;color:#991b1b;">
            ⚠ {{ $erpError }}
        </div>
    @endif


    {{-- FILTER CARD --}}
    <form method="GET"
          action="{{ route('student-skill-observations.index') }}"
          id="obsFilterForm">

        <div class="skill-card no-print">

            <div class="filter-section-title">Filters</div>

            <div class="filter-row">

                <div class="filter-group">
                    <label for="obs_year" class="filter-label">Academic Year <span class="req">*</span></label>
                    <select name="academic_year_id" id="obs_year" class="filter-select" required>
                        <option value="">Select</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                {{ $year->year_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="obs_standard" class="filter-label">Standard <span class="req">*</span></label>
                    <select name="standard_id" id="obs_standard" class="filter-select" required>
                        <option value="">Select</option>
                        @foreach($standards as $std)
                            <option value="{{ $std->id }}" {{ request('standard_id') == $std->id ? 'selected' : '' }}>
                                {{ $std->standard_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="obs_division" class="filter-label">Division <span class="req">*</span></label>
                    <select name="division_id" id="obs_division" class="filter-select" required>
                        <option value="">Select</option>
                        @foreach($divisions as $div)
                            <option value="{{ $div->id }}" {{ request('division_id') == $div->id ? 'selected' : '' }}>
                                {{ $div->division_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="obs_exam" class="filter-label">Exam <span class="req">*</span></label>
                    <select name="exam_master_id" id="obs_exam" class="filter-select" required>
                        <option value="">Select</option>
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}"
                                    data-standard-id="{{ $exam->standard_id }}"
                                    {{ request('exam_master_id') == $exam->id ? 'selected' : '' }}>
                                {{ $exam->exam_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="obs_subject" class="filter-label">Skill Subject <span class="req">*</span></label>
                    <select name="subject_id" id="obs_subject" class="filter-select" required>
                        <option value="">Select</option>
                        @foreach($skillSubjects as $subject)
                            <option value="{{ $subject->id }}" 
                                    data-selected="{{ request('subject_id') == $subject->id ? '1' : '0' }}"
                                    {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->subject_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group filter-group-btn">
                    <label class="filter-label" style="visibility:hidden;">Action</label>
                    <div style="display:flex;gap:8px;">
                        <button type="submit" class="btn btn-primary">Load Students</button>
                        <a href="{{ route('student-skill-observations.index') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </div>

            </div>
        </div>
    </form>


    {{-- CONTENT --}}
    @if($hasData)

        {{-- INFO BANNER — includes Skill Teacher --}}
        <div class="info-banner no-print">
            <span class="info-item">
                <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#2563EB;"></span>
                {{ count($students) }} Students
            </span>
            <span class="info-sep">|</span>
            <span class="info-item">
                <span class="info-key">Subject:</span>
                {{ $subjectName }}
            </span>
            <span class="info-sep">|</span>
            <span class="info-item">
                <span class="info-key">Skill Teacher:</span>
                {{ $skillTeacherName }}
            </span>
            <span class="info-sep">|</span>
            <span class="info-item">{{ count($criteria) }} Criteria</span>
            <div class="grade-legend">
                <span style="font-weight:700;">Grade Scale:</span>
                @foreach($gradeLabels as $g => $lbl)
                    <span class="grade-pill">
                        <span>{{ $g }}</span>
                        <span style="font-weight:500;color:#4B5563;">{{ $lbl }}</span>
                    </span>
                @endforeach
            </div>
        </div>

        <form method="POST" action="{{ route('student-skill-observations.save') }}" id="obsForm">
            @csrf
            <input type="hidden" name="academic_year_id" value="{{ request('academic_year_id') }}">
            <input type="hidden" name="exam_master_id"   value="{{ request('exam_master_id') }}">
            <input type="hidden" name="standard_id"      value="{{ request('standard_id') }}">
            <input type="hidden" name="division_id"      value="{{ request('division_id') }}">
            <input type="hidden" name="subject_id"       value="{{ request('subject_id') }}">

            <div class="table-wrapper">
                <table class="entry-table">
                    <colgroup>
                        <col class="col-gr">
                        <col class="col-roll">
                        <col class="col-name">
                        <col class="col-pa">
                        @foreach($gradeCriteria as $c)
                            <col class="col-grade">
                        @endforeach
                        @foreach($markCriteria as $c)
                            <col class="col-grade">
                        @endforeach
                        <col class="col-avg">
                        @foreach($remarkCriteria as $c)
                            <col class="col-remark">
                        @endforeach
                        <col class="col-status">
                    </colgroup>

                    <thead>
                        <tr>
                            <th>GR NO</th>
                            <th>Roll No</th>
                            <th>Student Name</th>
                            <th class="center th-attendance">P / A</th>

                            {{-- Render Grade columns (Dropdowns) --}}
                            @foreach($gradeCriteria as $criterion)
                                <th class="center th-grade" title="{{ $criterion }}">
                                    {{ $criterion }}
                                </th>
                            @endforeach

                            {{-- Render Written Marks Column --}}
                            @foreach($markCriteria as $criterion)
                                <th class="center th-grade" style="background:#b45309;" title="{{ $criterion }} - 20M">
                                    {{ $criterion }} - 20M
                                </th>
                            @endforeach

                            <th class="center th-avg">Average Grade</th>

                            {{-- Render Remark columns (Text Inputs) --}}
                            @foreach($remarkCriteria as $criterion)
                                <th class="center th-remark" title="{{ $criterion }}">
                                    {{ $criterion }}
                                </th>
                            @endforeach

                            <th class="center th-status">Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($students as $student)
                            @php
                                $obs       = $existing[$student->Studentid] ?? null;
                                $savedData = $obs->observations ?? [];
                                $isAbsent  = (int) ($obs->is_absent ?? 0);
                            @endphp
                            <tr class="obs-row" data-student="{{ $student->Studentid }}">
                                <td class="center">{{ $student->regno ?? '-' }}</td>
                                <td class="center">{{ $student->rollno ?? '-' }}</td>
                                <td class="student-name"
                                    title="{{ trim(($student->studname ?? '') . ' ' . ($student->fathername ?? '')) }}">
                                    {{ trim(($student->studname ?? '') . ' ' . ($student->fathername ?? '')) }}
                                </td>

                                <td class="center">
                                    <button type="button"
                                            class="toggle-status-btn skill-toggle-btn {{ $isAbsent ? 'skill-absent-btn' : 'skill-present-btn' }}"
                                            data-student="{{ $student->Studentid }}">
                                        <span class="dot"></span>
                                        <span class="btn-label">{{ $isAbsent ? 'ABSENT' : 'PRESENT' }}</span>
                                    </button>
                                    <input type="hidden"
                                           name="observations[{{ $student->Studentid }}][is_absent]"
                                           value="{{ $isAbsent ? 1 : 0 }}"
                                           class="absent-hidden">
                                </td>

                                {{-- Dropdown Inputs --}}
                                @foreach($gradeCriteria as $criterion)
                                    @php $val = $savedData[$criterion] ?? ''; @endphp
                                    <td class="center">
                                        <select name="observations[{{ $student->Studentid }}][criteria][{{ $criterion }}]"
                                                data-criterion="{{ $criterion }}"
                                                class="criterion-input student-{{ $student->Studentid }} grade-input-dropdown grade-select"
                                                title="{{ $criterion }}"
                                                {{ $isAbsent ? 'disabled' : '' }}>
                                            <option value="">—</option>
                                            @foreach($gradeScale as $g)
                                                <option value="{{ $g }}" {{ $val == $g ? 'selected' : '' }}>{{ $g }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                @endforeach

                                {{-- Numeric Input for Written Marks (plain text box — no spinners) --}}
                                @foreach($markCriteria as $criterion)
                                    @php $val = $savedData[$criterion] ?? ''; @endphp
                                    <td class="center">
                                        <input type="text"
                                               name="observations[{{ $student->Studentid }}][criteria][{{ $criterion }}]"
                                               data-criterion="{{ $criterion }}"
                                               value="{{ $val }}"
                                               class="criterion-input student-{{ $student->Studentid }} mark-input"
                                               placeholder="0 - 20"
                                               inputmode="numeric"
                                               maxlength="2"
                                               autocomplete="off"
                                               {{ $isAbsent ? 'disabled' : '' }}>
                                    </td>
                                @endforeach

                                <td class="center">
                                    <input type="text"
                                           name="observations[{{ $student->Studentid }}][overall_grade]"
                                           class="avg-grade student-{{ $student->Studentid }}"
                                           value="{{ $obs->overall_grade ?? '' }}"
                                           readonly
                                           tabindex="-1">
                                </td>

                                {{-- Text Inputs (Remarks) --}}
                                @foreach($remarkCriteria as $criterion)
                                    @php $val = $savedData[$criterion] ?? ''; @endphp
                                    <td class="center">
                                        <input type="text"
                                               name="observations[{{ $student->Studentid }}][criteria][{{ $criterion }}]"
                                               value="{{ $val }}"
                                               class="criterion-input student-{{ $student->Studentid }} remark-input"
                                               placeholder="Enter…"
                                               {{ $isAbsent ? 'disabled' : '' }}>
                                    </td>
                                @endforeach

                                <td class="center">
                                    <span class="status-cell {{ $isAbsent ? 'status-absent' : 'status-present' }}">
                                        {{ $isAbsent ? 'ABSENT' : 'PRESENT' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="action-row" style="margin-top:14px;justify-content:flex-end;">
                <a href="{{ route('student-skill-observations.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Observations</button>
            </div>
        </form>

    @elseif(request()->anyFilled(['academic_year_id', 'standard_id', 'division_id', 'subject_id']))

        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                 fill="none" stroke="#9CA3AF" stroke-width="1.5"
                 stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="7"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <div class="empty-state-title">
                {{ !empty($erpError) ? 'Old ERP unreachable' : 'No students found' }}
            </div>
            <div class="empty-state-subtitle">
                @if(!empty($erpError))
                    The Old ERP database is currently unreachable. Please try again in a moment.
                @else
                    No students found in the selected class. Please verify Standard + Division.
                @endif
            </div>
        </div>

    @else

        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                 fill="none" stroke="#9CA3AF" stroke-width="1.5"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <div class="empty-state-title">Select your filters above</div>
            <div class="empty-state-subtitle">
                Pick an Academic Year, Standard, Division, Exam and Skill Subject, then click <strong>Load Students</strong>.
            </div>
        </div>

    @endif

</div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ============================================================
       1. AVERAGE GRADE (Client-side Calculation)
       ============================================================ */
    const gradeMap = { 'C': 1, 'B': 2, 'B+': 3, 'A': 4, 'A+': 5 };

    function gradeFromAverage(avg) {
        if (avg >= 4.5) return 'A+';
        if (avg >= 3.5) return 'A';
        if (avg >= 2.5) return 'B+';
        if (avg >= 1.5) return 'B';
        return 'C';
    }

    function computeRowAverage(row) {
        const avgInput = row.querySelector('.avg-grade');
        if (!avgInput) return;

        const absentHidden = row.querySelector('.absent-hidden');
        if (absentHidden && absentHidden.value === '1') {
            avgInput.value = '';
            return;
        }

        // 1. Check for Written Marks first (Out of 20)
        const markInput = row.querySelector('.mark-input');
        if (markInput && markInput.value !== '') {
            const marks = parseFloat(markInput.value);
            if (!isNaN(marks)) {
                const percentage = (marks / 20) * 100;
                if (percentage >= 90) avgInput.value = 'A+';
                else if (percentage >= 75) avgInput.value = 'A';
                else if (percentage >= 60) avgInput.value = 'B+';
                else if (percentage >= 45) avgInput.value = 'B';
                else if (marks > 0) avgInput.value = 'C';
                else avgInput.value = '';
                return; // Stop here, don't average dropdowns
            }
        }

        // 2. Fallback to Dropdowns (Existing Logic)
        const grades = [];
        row.querySelectorAll('.grade-input-dropdown').forEach(function (sel) {
            const v = (sel.value || '').trim().toUpperCase();
            if (gradeMap[v] !== undefined) {
                grades.push(gradeMap[v]);
            }
        });

        if (grades.length === 0) {
            avgInput.value = '';
            return;
        }

        const sum = grades.reduce((a, b) => a + b, 0);
        const avg = sum / grades.length;
        avgInput.value = gradeFromAverage(avg);
    }

    /* ============================================================
       2. AUTO-SAVE LOGIC
       ============================================================ */
    const csrfToken = document.querySelector('input[name="_token"]')?.value;

    function triggerAutoSave(element) {
        const row = element.closest('tr');
        if (!row) return;
        
        const studentId = row.dataset.student;
        if (!studentId) return;

        // Get Global Filters from the form
        const form = document.getElementById('obsForm');
        const formData = new FormData(form);

        // Gather Criteria Data for this specific row
        const criteria = {};
        row.querySelectorAll('.criterion-input').forEach(input => {
            const criterionName = input.dataset.criterion;
            if (criterionName) {
                criteria[criterionName] = input.value;
            }
        });

        // Get Absent Status
        const isAbsent = row.querySelector('.absent-hidden').value;

        // Prepare payload
        const payload = {
            academic_year_id: formData.get('academic_year_id'),
            exam_master_id: formData.get('exam_master_id'),
            standard_id: formData.get('standard_id'),
            division_id: formData.get('division_id'),
            subject_id: formData.get('subject_id'),
            student_id: studentId,
            is_absent: isAbsent,
            criteria: criteria
        };

        // Visual feedback - dim row
        row.style.opacity = '0.5';

        // Send AJAX
        fetch('{{ route("student-skill-observations.auto-save") }}', {
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
            row.style.opacity = '1';
            if (data.success) {
                // Update the average grade input from the backend response
                const avgInput = row.querySelector('.avg-grade');
                if (avgInput) avgInput.value = data.overall_grade;

                // Flash green effect
                const originalBg = row.style.backgroundColor;
                row.style.backgroundColor = '#dcfce7';
                setTimeout(() => { row.style.backgroundColor = originalBg; }, 800);
            }
        })
        .catch(err => {
            row.style.opacity = '1';
            console.error('Auto-save failed:', err);
        });
    }

    /* Attach Auto-Save listeners */
    document.querySelectorAll('.grade-input-dropdown').forEach(function (sel) {
        sel.addEventListener('change', function () {
            computeRowAverage(this.closest('tr')); // Update UI instantly
            triggerAutoSave(this);                 // Send to server
        });
    });

    /* Attach Auto-Save and live calculation to Written Marks inputs */
    document.querySelectorAll('.mark-input').forEach(function (input) {
        input.addEventListener('input', function () {
            // Allow digits only, clamp to 20
            let v = this.value.replace(/[^0-9]/g, '');
            if (v !== '' && parseInt(v, 10) > 20) v = '20';
            this.value = v;

            computeRowAverage(this.closest('tr')); // Live update grade
        });
        input.addEventListener('blur', function () {
            triggerAutoSave(this); // Save to DB on click away
        });
    });

    document.querySelectorAll('.remark-input').forEach(function (input) {
        // Use 'blur' so it saves when the user clicks away, preventing API spam on every keystroke
        input.addEventListener('blur', function () {
            triggerAutoSave(this);
        });
    });

    document.querySelectorAll('.obs-row').forEach(function (row) {
        computeRowAverage(row);
    });

    /* ============================================================
       3. PRESENT / ABSENT FLIP BUTTON
       ============================================================ */
    document.querySelectorAll('.toggle-status-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {

            const studentId = this.dataset.student;
            const row       = this.closest('tr');
            const hiddenEl  = row.querySelector('.absent-hidden');
            const labelEl   = this.querySelector('.btn-label');
            const statusEl  = row.querySelector('.status-cell');
            const inputs    = row.querySelectorAll('.student-' + studentId);
            const avgInput  = row.querySelector('.avg-grade');
            const isPresent = hiddenEl.value === '0';

            if (isPresent) {
                Swal.fire({
                    title: 'Mark as Absent?',
                    text: 'All criteria for this student will be cleared and disabled.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, Absent',
                    cancelButtonText: 'Cancel'
                }).then(function (r) {
                    if (!r.isConfirmed) return;
                    hiddenEl.value = '1';
                    btn.classList.remove('skill-present-btn');
                    btn.classList.add('skill-absent-btn');
                    labelEl.textContent = 'ABSENT';
                    inputs.forEach(function (el) {
                        el.value = '';
                        el.disabled = true;
                    });
                    if (avgInput) avgInput.value = '';
                    statusEl.textContent = 'ABSENT';
                    statusEl.className = 'status-cell status-absent';
                    
                    triggerAutoSave(btn); // Trigger Auto-Save
                });
            } else {
                Swal.fire({
                    title: 'Mark as Present?',
                    text: 'Criteria will be enabled for editing.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#16a34a',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, Present',
                    cancelButtonText: 'Cancel'
                }).then(function (r) {
                    if (!r.isConfirmed) return;
                    hiddenEl.value = '0';
                    btn.classList.remove('skill-absent-btn');
                    btn.classList.add('skill-present-btn');
                    labelEl.textContent = 'PRESENT';
                    inputs.forEach(function (el) {
                        el.disabled = false;
                    });
                    statusEl.textContent = 'PRESENT';
                    statusEl.className = 'status-cell status-present';
                    
                    triggerAutoSave(btn); // Trigger Auto-Save
                });
            }
        });
    });

    /* ============================================================
       4. CASCADING FILTER: Standard → Exam
       ============================================================ */
    const standardSelect = document.getElementById('obs_standard');
    const examSelect     = document.getElementById('obs_exam');

    if (standardSelect && examSelect) {

        const allExams = Array.from(examSelect.options).map(opt => ({
            value:      opt.value,
            text:       opt.text,
            standardId: opt.dataset.standardId || '',
        }));

        function rebuildExams() {
            const stdId        = standardSelect.value;
            const currentValue = examSelect.value;

            examSelect.innerHTML = '<option value="">Select</option>';
            let matchedExams = 0;

            allExams.forEach(opt => {
                if (opt.value === '') return;

                if (stdId && String(opt.standardId) === String(stdId)) {
                    const o = document.createElement('option');
                    o.value = opt.value;
                    o.textContent = opt.text;
                    o.dataset.standardId = opt.standardId;
                    if (opt.value === currentValue) o.selected = true;
                    examSelect.appendChild(o);
                    matchedExams++;
                }
            });

            if (matchedExams === 0) {
                const o = document.createElement('option');
                o.value = "";
                o.textContent = "No exams found for this standard";
                o.disabled = true;
                o.selected = true;
                examSelect.appendChild(o);
            }

            const stillThere = Array.from(examSelect.options).some(o => o.value === currentValue);
            if (!stillThere) examSelect.value = '';
        }

        rebuildExams();
        standardSelect.addEventListener('change', rebuildExams);
    }

    /* ============================================================
       5. CASCADING FILTER: Standard → Skill Subject
       ============================================================ */
    const skillSubjectsByStandard = @json($skillSubjectsByStandard ?? []);
    const subjectSelect = document.getElementById('obs_subject');

    if (standardSelect && subjectSelect) {
        const allSubjectOptions = Array.from(subjectSelect.options).map(opt => ({
            value: opt.value,
            text:  opt.text
        }));

        const initialSubjectId = "{{ request('subject_id') }}";

        function rebuildSubjects() {
            const stdId            = standardSelect.value;
            const currentSubjectId = subjectSelect.value || initialSubjectId;

            subjectSelect.innerHTML = '<option value="">Select</option>';
            const subjects = skillSubjectsByStandard[stdId];

            if (!stdId || !subjects || !subjects.length) {
                allSubjectOptions.forEach(opt => {
                    if (opt.value === '') return;
                    const o = document.createElement('option');
                    o.value = opt.value;
                    o.textContent = opt.text;
                    if (String(opt.value) === String(currentSubjectId)) o.selected = true;
                    subjectSelect.appendChild(o);
                });
                return;
            }

            subjects.forEach(sub => {
                const o = document.createElement('option');
                o.value       = sub.id;
                o.textContent = sub.subject_name;
                if (String(sub.id) === String(currentSubjectId)) o.selected = true;
                subjectSelect.appendChild(o);
            });

            const stillThere = Array.from(subjectSelect.options).some(o => o.value === currentSubjectId);
            if (!stillThere) subjectSelect.value = '';
        }

        rebuildSubjects();
        standardSelect.addEventListener('change', rebuildSubjects);
    }
});
</script>

</x-app-layout>