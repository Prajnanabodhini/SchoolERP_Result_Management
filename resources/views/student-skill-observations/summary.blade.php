@extends('layouts.app')

<style>
/* =========================================================
   PAGE
   ========================================================= */
.result-sheet-page,
.result-sheet-page * {
    box-sizing: border-box;
    font-family: Arial, Helvetica, sans-serif;
}
.result-sheet-page {
    width: 100%;
    padding-top: 76px;
    padding-left: 76px;
    padding-right: 20px;
    padding-bottom: 20px;
    background: #fafbfc;
}

/* =========================================================
   PAGE TITLE
   ========================================================= */
.result-sheet-page h2 {
    font-size: 21px !important;
    font-weight: 700 !important;
    color: #1D4ED8 !important;
    letter-spacing: 0.4px;
    margin: 0 0 14px 0 !important;
}

/* =========================================================
   FILTER CARD
   ========================================================= */
.filter-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 16px 18px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    margin-bottom: 14px;
}
.filter-card h2 {
    margin: 0 0 14px 0 !important;
    padding-bottom: 10px;
    border-bottom: 1px solid #f1f5f9;
}

/* =========================================================
   FILTER ROW
   ========================================================= */
.result-filter { width: 100%; }
.result-filter-row {
    width: 100%;
    display: flex;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 10px;
}
.result-filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex: 1 1 130px;
    min-width: 0;
}
.result-filter-group.filter-group-action {
    flex: 0 0 auto;
    flex-direction: row;
    align-items: flex-end;
    gap: 8px;
}
.result-filter-label {
    flex: 0 0 auto;
    font-size: 10.5px !important;
    font-weight: 700 !important;
    color: #6B7280;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    white-space: nowrap;
}

.result-filter-select-wrapper { position: relative; display: block; width: 100%; }
.result-filter-select-wrapper .result-select-arrow {
    position: absolute;
    top: 50%;
    right: 11px;
    transform: translateY(-50%);
    width: 0;
    height: 0;
    border-left: 4px solid transparent;
    border-right: 4px solid transparent;
    border-top: 5px solid #6b7280;
    pointer-events: none;
}

.result-filter select {
    height: 34px !important;
    padding: 3px 28px 3px 10px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 12.5px !important;
    line-height: 22px !important;
    color: #111827 !important;
    background: #fff !important;
    border: 1px solid #d1d5db !important;
    border-radius: 6px !important;
    cursor: pointer;
    font-weight: 600 !important;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    transition: border-color .15s, box-shadow .15s;
    width: 100%;
    min-width: 0;
}
.result-filter select:focus {
    border-color: #2563EB !important;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15) !important;
    outline: none !important;
}
.result-filter select,
.result-filter select option {
    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 12.5px !important;
    font-weight: 500 !important;
}
.result-filter .year-select      { min-width: 110px; }
.result-filter .standard-select  { min-width: 130px; }
.result-filter .division-select  { min-width: 100px; }
.result-filter .exam-select      { min-width: 180px; }

/* =========================================================
   BUTTONS
   ========================================================= */
.result-filter-button,
.result-filter-button.erp-btn,
.result-filter-button.erp-btn-save {
    height: 34px !important;
    min-height: 34px !important;
    min-width: 88px !important;
    padding: 0 16px !important;
    margin: 0 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 12.5px !important;
    line-height: 34px !important;
    font-weight: 700 !important;
    white-space: nowrap !important;
    text-align: center !important;
    border-radius: 6px !important;
    text-decoration: none !important;
    cursor: pointer;
    transition: background .15s, border-color .15s, transform .1s;
}
.result-filter-button.erp-btn-save {
    background: #16A34A !important;
    color: #fff !important;
    border: 1px solid #15803D !important;
    box-shadow: 0 1px 2px rgba(22, 163, 74, 0.2);
}
.result-filter-button.erp-btn-save:hover {
    background: #15803D !important;
    border-color: #166534 !important;
    transform: translateY(-1px);
}
.result-filter-button.btn-print {
    background: #2563EB !important;
    color: #fff !important;
    border: 1px solid #1D4ED8 !important;
    box-shadow: 0 1px 2px rgba(37, 99, 235, 0.2);
}
.result-filter-button.btn-print:hover {
    background: #1D4ED8 !important;
    border-color: #1e40af !important;
    transform: translateY(-1px);
}

.result-student-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 34px !important;
    padding: 0 14px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    color: #1e40af;
    background: #dbeafe;
    border: 1px solid #93c5fd;
    border-radius: 6px;
    white-space: nowrap;
    margin-left: auto;
}

/* =========================================================
   INFO BANNER
   ========================================================= */
.result-information-wrapper { width: 100%; margin-bottom: 14px; }
.result-information-grid {
    width: 100%;
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 10px;
}
.result-information-item {
    border: 1px solid #bfdbfe;
    border-radius: 8px;
    background: linear-gradient(180deg, #f0f7ff 0%, #eff6ff 100%);
    padding: 10px 14px;
    min-width: 0;
    box-shadow: 0 1px 2px rgba(30, 64, 175, 0.04);
}
.result-information-label {
    display: block;
    font-size: 9.5px;
    color: #1e40af;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
    opacity: 0.85;
}
.result-information-value {
    display: block;
    font-size: 13.5px;
    color: #1e3a8a;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* =========================================================
   SECTION BAR + GRADE SCALE
   ========================================================= */
.section-bar-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    background: linear-gradient(180deg, #f3f4f6 0%, #e5e7eb 100%);
    border: 1px solid #d1d5db;
    border-bottom: 0;
    padding: 9px 14px;
    border-radius: 8px 8px 0 0;
}
.section-bar-row .section-title {
    font-weight: 700;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #1e3a8a;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.section-bar-row .section-title::before {
    content: "";
    display: inline-block;
    width: 4px;
    height: 16px;
    background: #1D4ED8;
    border-radius: 2px;
}
.section-bar-row .grade-scale {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
    margin-left: auto;
}
.section-bar-row .grade-scale-label {
    font-weight: 700;
    font-size: 10px;
    color: #1e3a8a;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-right: 2px;
}
.section-bar-row .grade-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #ffffff;
    border: 1px solid #93c5fd;
    border-radius: 4px;
    padding: 3px 8px;
    font-weight: 700;
    color: #1e40af;
    font-size: 10.5px;
    line-height: 1;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.02);
}
.section-bar-row .grade-pill span:first-child { font-weight: 800; }

/* =========================================================
   TABLE WRAPPER
   ========================================================= */
.result-sheet-wrapper {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #d1d5db;
    border-top: 0;
    border-radius: 0 0 8px 8px;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

/* =========================================================
   SKILL SUMMARY TABLE
   ========================================================= */
.skill-summary-table {
    width: 100%;
    max-width: 100%;
    border-collapse: collapse;
    border-spacing: 0;
    table-layout: auto;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11px;
}
.skill-summary-table th,
.skill-summary-table td {
    border: 1px solid #d1d5db !important;
    border-style: solid !important;
    border-width: 1px !important;
    text-align: center;
    vertical-align: middle;
    padding: 7px 6px;
}
.skill-summary-table thead tr { background: #1e3a8a; }
.skill-summary-table th {
    background: #1e3a8a;
    color: #ffffff !important;
    font-size: 10px;
    font-weight: 700 !important;
    line-height: 1.15;
    white-space: normal;
    overflow-wrap: break-word;
    word-break: break-word;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    border-color: #1e40af !important;
}
.skill-summary-table td {
    font-size: 10.5px;
    line-height: 1.2;
    white-space: nowrap;
    color: #111827;
}
.skill-summary-table tbody tr:nth-child(even) { background: #f9fafb; }
.skill-summary-table tbody tr:hover { background: #fef9c3; }
.skill-summary-table td.td-roll {
    white-space: nowrap;
    font-weight: 700;
    color: #1e3a8a;
}
.skill-summary-table td.td-name {
    text-align: left !important;
    white-space: normal !important;
    overflow-wrap: break-word;
    word-break: break-word;
    font-weight: 700 !important;
    color: #111827 !important;
    line-height: 1.2;
}
.skill-summary-table td.td-empty  { color: #9CA3AF !important; background: #ffffff; }
.skill-summary-table td.td-ab     { color: #C2410C !important; background: #FFF7ED !important; font-weight: 700 !important; }
.skill-summary-table td.td-filled { color: #111827 !important; background: #DBEAFE !important; font-weight: 700 !important; }
.skill-summary-table td.td-avg    { color: #92400E !important; background: #FEF3C7 !important; font-weight: 800 !important; }

/* =========================================================
   ALERT CARDS
   ========================================================= */
.alert-card {
    padding: 12px 16px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 13px;
    margin-top: 14px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}
.alert-warning {
    background: #fffbeb;
    color: #92400e;
    border: 1px solid #fcd34d;
    font-weight: 600;
}

/* =========================================================
   RESPONSIVE
   ========================================================= */
@media (max-width: 1200px) {
    .result-sheet-page { padding-top: 60px; padding-left: 55px; padding-right: 15px; }
    .result-information-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 900px) {
    .result-sheet-page { padding-top: 45px; padding-left: 30px; padding-right: 10px; }
    .result-information-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .section-bar-row { flex-direction: column; align-items: flex-start; }
    .section-bar-row .grade-scale { margin-left: 0; }
    .result-student-count { margin-left: 0; }
}
@media (max-width: 700px) {
    .result-sheet-page { padding-top: 25px; padding-left: 15px; padding-right: 8px; }
    .result-filter-row { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .result-filter-group { flex: 1 1 100%; }
    .result-filter-group.filter-group-action { grid-column: 1 / -1; flex-direction: row; }
    .result-information-grid { grid-template-columns: 1fr; }
    .result-student-count { margin-left: 0; }
}
</style>

@section('content')
<div class="erp-page result-sheet-page">

@php
    $gradeLabels = \App\Helpers\SkillCriteriaHelper::gradeLabels();

    $printYearName     = optional($academicYears->firstWhere('id', (int) request('academic_year_id')))->year_name ?? '';
    $printStandardName = optional($standards->firstWhere('id', (int) request('standard_id')))->standard_name ?? '';
    $printDivisionName = optional($divisions->firstWhere('id', (int) request('division_id')))->division_name ?? '';
    $printExamName     = 'All Exams';
    if (request('exam_master_id')) {
        $printExamName = optional($exams->firstWhere('id', (int) request('exam_master_id')))->exam_name ?? 'All Exams';
    }

    $hasData = count($students) && $skillSubjects->isNotEmpty();
@endphp

{{-- =========================================================
     FILTER CARD
========================================================= --}}
<div class="filter-card no-print">
    
    {{-- HEADER WITH BACK BUTTON --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid #f1f5f9;">
        <h2 style="margin: 0 !important; padding: 0 !important; border: none !important; font-size: 20px !important; font-weight: 700 !important; color: #1D4ED8 !important;">
            SKILL OBSERVATION SUMMARY
        </h2>
        <a href="{{ route('exam-progress.index', array_merge(request()->only(['academic_year_id', 'standard_id', 'division_id', 'exam_master_id']), ['subject_type' => 'skill'])) }}" class="result-filter-button erp-btn erp-btn-reset" style="text-decoration: none;">
            ⬅ Back
        </a>
    </div>

    <form method="GET" action="{{ route('student-skill-observations.summary') }}" class="result-filter" id="summaryFilterForm">
        <div class="result-filter-row">

            <div class="result-filter-group">
                <label class="result-filter-label">Academic Year</label>
                <div class="result-filter-select-wrapper">
                    <select name="academic_year_id" id="sum_year" class="year-select" required>
                        <option value="">Select</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ (string) request('academic_year_id') === (string) $year->id ? 'selected' : '' }}>
                                {{ $year->year_name }}
                            </option>
                        @endforeach
                    </select>
                    <span class="result-select-arrow"></span>
                </div>
            </div>

            <div class="result-filter-group">
                <label class="result-filter-label">Standard</label>
                <div class="result-filter-select-wrapper">
                    <select name="standard_id" id="sum_standard" class="standard-select" required>
                        <option value="">Select</option>
                        @foreach($standards as $std)
                            <option value="{{ $std->id }}" {{ (string) request('standard_id') === (string) $std->id ? 'selected' : '' }}>
                                {{ $std->standard_name }}
                            </option>
                        @endforeach
                    </select>
                    <span class="result-select-arrow"></span>
                </div>
            </div>

            <div class="result-filter-group">
                <label class="result-filter-label">Division</label>
                <div class="result-filter-select-wrapper">
                    <select name="division_id" id="sum_division" class="division-select" required>
                        <option value="">Select</option>
                        @foreach($divisions as $div)
                            <option value="{{ $div->id }}" {{ (string) request('division_id') === (string) $div->id ? 'selected' : '' }}>
                                {{ $div->division_name }}
                            </option>
                        @endforeach
                    </select>
                    <span class="result-select-arrow"></span>
                </div>
            </div>

            <div class="result-filter-group">
                <label class="result-filter-label">Exam</label>
                <div class="result-filter-select-wrapper">
                    <select name="exam_master_id" id="sum_exam" class="exam-select">
                        <option value="">All Exams</option>
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}"
                                    data-standard-id="{{ $exam->standard_id }}"
                                    data-year-id="{{ $exam->academic_year_id }}"
                                    {{ (string) request('exam_master_id') === (string) $exam->id ? 'selected' : '' }}>
                                {{ $exam->exam_name }}
                            </option>
                        @endforeach
                    </select>
                    <span class="result-select-arrow"></span>
                </div>
            </div>

            <div class="result-filter-group filter-group-action">
                <button type="submit" class="erp-btn erp-btn-save result-filter-button">
                    🔍 Load
                </button>

                @if($hasData)
                    <a href="{{ route('student-skill-observations.summary', array_merge(request()->query(), ['print' => 1])) }}"
                       target="_blank"
                       class="erp-btn result-filter-button btn-print">
                        🖨 Print
                    </a>
                @endif
            </div>

            @if($hasData)
                <div class="result-filter-group filter-group-action" style="flex:0 0 auto;">
                    <span class="result-student-count">Students : {{ count($students) }}</span>
                </div>
            @endif

        </div>
    </form>
</div>

{{-- =========================================================
     ALERTS
========================================================= --}}
@if(session('error'))
    <div class="alert-card alert-warning">{{ session('error') }}</div>
@endif

@if(session('success'))
    <div class="alert-card alert-warning" style="background:#dcfce7;border-color:#86efac;color:#166534;">
        {{ session('success') }}
    </div>
@endif

{{-- =========================================================
     CONTENT
========================================================= --}}
@if($hasData)
<div class="erp-card mt-4" style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,.06);padding:16px 18px;">

    {{-- ============ INFO BANNER — 4 columns ============ --}}
    <div class="result-information-wrapper">
        <div class="result-information-grid">
            <div class="result-information-item">
                <span class="result-information-label">Academic Year</span>
                <span class="result-information-value">{{ $printYearName ?: '-' }}</span>
            </div>
            <div class="result-information-item">
                <span class="result-information-label">Exam</span>
                <span class="result-information-value" title="{{ $printExamName }}">{{ $printExamName }}</span>
            </div>
            <div class="result-information-item">
                <span class="result-information-label">Standard / Division</span>
                <span class="result-information-value">{{ ($printStandardName ?: '-') . ' / ' . ($printDivisionName ?: '-') }}</span>
            </div>
            <div class="result-information-item">
                <span class="result-information-label">Total Students</span>
                <span class="result-information-value">{{ count($students) }}</span>
            </div>
        </div>
    </div>

    {{-- ============ SECTION BAR + GRADE SCALE ============ --}}
    <div class="section-bar-row">
        <span class="section-title">Skill Observation Summary</span>

        <div class="grade-scale no-print">
            <span class="grade-scale-label">Grade Scale:</span>
            @foreach($gradeLabels as $g => $lbl)
                <span class="grade-pill">
                    <span>{{ $g }}</span>
                    <span style="font-weight:500;color:#4B5563;">{{ $lbl }}</span>
                </span>
            @endforeach
        </div>
    </div>

    {{-- ============ SUMMARY TABLE ============ --}}
    <div class="result-sheet-wrapper">
        <table class="skill-summary-table">
            <thead>
                <tr>
                    <th style="width:55px;">Roll No</th>
                    <th style="min-width:180px;text-align:left;">Student Name</th>

                    @foreach($skillSubjects as $sub)
                        <th title="{{ $sub->subject_name }}">
                            {{ $sub->subject_name }}
                        </th>
                    @endforeach

                    <th style="width:80px;">Average Grade</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $student)
                    @php
                        $studentName = trim(($student->studname ?? '') . ' ' . ($student->fathername ?? ''));
                    @endphp
                    <tr>
                        <td class="td-roll">{{ $student->rollno ?? '-' }}</td>
                        <td class="td-name" title="{{ $studentName }}">{{ $studentName }}</td>

                        @foreach($skillSubjects as $sub)
                            @php
                                $cell         = '';
                                $cellClass    = 'td-empty';
                                $subId        = $sub->id;

                                if ($subId) {
                                    $key = $student->Studentid . '|' . $subId;
                                    $obs = $observations[$key] ?? null;

                                    if ($obs) {
                                        if ($obs->is_absent) {
                                            $cell      = 'AB';
                                            $cellClass = 'td-ab';
                                        } else {
                                            $avg = $obs->overall_grade;
                                            if (!$avg && !empty($obs->observations)) {
                                                $avg = \App\Helpers\SkillCriteriaHelper::computeAverageGrade($obs->observations);
                                            }
                                            $cell      = $avg ?: '';
                                            $cellClass = 'td-filled';
                                        }
                                    }
                                }
                            @endphp
                            <td class="{{ $cellClass }}">{{ $cell }}</td>
                        @endforeach

                        <td class="td-avg">{{ $studentAverages[$student->Studentid] ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

@elseif(request()->anyFilled(['academic_year_id', 'standard_id', 'division_id']))
    <div class="alert-card alert-warning" style="margin-top:16px;">
        No students or skill subjects found for the selected filters.
    </div>
@endif

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ============================================================
       CASCADING FILTER: Standard → Exam
       ============================================================ */
    const yearSelect     = document.getElementById('sum_year');
    const standardSelect = document.getElementById('sum_standard');
    const examSelect     = document.getElementById('sum_exam');

    if (standardSelect && examSelect) {

        const allExams = Array.from(examSelect.options).map(opt => ({
            value:      opt.value,
            text:       opt.text,
            standardId: opt.dataset.standardId || '',
            yearId:     opt.dataset.yearId     || '',
        }));

        function rebuildExams() {
            const stdId        = standardSelect.value;
            const yearId       = yearSelect ? yearSelect.value : '';
            const currentValue = examSelect.value;

            examSelect.innerHTML = '';

            allExams.forEach(opt => {
                if (opt.value === '') {
                    const o = document.createElement('option');
                    o.value = '';
                    o.textContent = opt.text;
                    examSelect.appendChild(o);
                    return;
                }

                const matchesStd  = !stdId  || String(opt.standardId) === String(stdId);
                const matchesYear = !yearId || !opt.yearId || String(opt.yearId) === String(yearId);

                if (matchesStd && matchesYear) {
                    const o = document.createElement('option');
                    o.value = opt.value;
                    o.textContent = opt.text;
                    o.dataset.standardId = opt.standardId;
                    o.dataset.yearId     = opt.yearId;
                    if (opt.value === currentValue) o.selected = true;
                    examSelect.appendChild(o);
                }
            });

            const stillThere = Array.from(examSelect.options).some(o => o.value === currentValue);
            if (!stillThere) examSelect.value = '';
        }

        standardSelect.addEventListener('change', rebuildExams);
        if (yearSelect) {
            yearSelect.addEventListener('change', rebuildExams);
        }
        rebuildExams();
    }
});
</script>

@endsection