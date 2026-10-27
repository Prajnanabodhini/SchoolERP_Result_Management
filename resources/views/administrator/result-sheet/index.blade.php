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
    padding: 20px 20px 20px 20px;
}

/* =========================================================
   PAGE TITLE
   ========================================================= */
.result-sheet-page h2 {
    font-size: 20px !important;
    font-weight: 700 !important;
    color: #1D4ED8 !important;
    letter-spacing: 0.4px;
    margin: 0 0 10px 0 !important;
}

/* =========================================================
   FILTER ROW
   ========================================================= */
.result-filter { width: 100%; }
.result-filter-row {
    width: 100%;
    display: flex;
    align-items: center;
    flex-wrap: nowrap;
    gap: 6px;
}
.result-filter-label {
    flex: 0 0 auto;
    font-size: 11.5px !important;
    font-weight: 600 !important;
    color: #374151;
    white-space: nowrap;
}

/* ---------- SELECT WRAPPER + CUSTOM ARROW ---------- */
.result-filter-select-wrapper { position: relative; display: inline-block; }
.result-filter-select-wrapper .result-select-arrow {
    position: absolute;
    top: 50%;
    right: 9px;
    transform: translateY(-50%);
    width: 0;
    height: 0;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 6px solid #6b7280;
    pointer-events: none;
}

.result-filter select {
    height: 28px !important;
    padding: 3px 26px 3px 9px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 11.5px !important;
    line-height: 18px !important;
    color: #111827 !important;
    background: #fff !important;
    border: 1px solid #9CA3AF !important;
    border-radius: 5px !important;
    cursor: pointer;
    font-weight: 600 !important;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    transition: border-color .15s, box-shadow .15s;
}
.result-filter select:focus {
    border-color: #2563EB !important;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15) !important;
    outline: none !important;
}
.result-filter select,
.result-filter select option {
    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 11.5px !important;
    font-weight: 500 !important;
}
.result-filter .year-select      { width: 120px !important; }
.result-filter .exam-select      { width: 200px !important; }
.result-filter .division-select  { width: 115px !important; }

/* =========================================================
   BUTTONS (filter bar)
   ========================================================= */
.result-filter-button,
.result-filter-button.erp-btn,
.result-filter-button.erp-btn-save {
    height: 28px !important;
    min-height: 28px !important;
    min-width: 76px !important;
    padding: 0 14px !important;
    margin: 0 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 11.5px !important;
    line-height: 28px !important;
    font-weight: 700 !important;
    white-space: nowrap !important;
    text-align: center !important;
    border-radius: 5px !important;
    text-decoration: none !important;
    cursor: pointer;
    transition: background .15s, border-color .15s;
}
.result-filter-button.erp-btn-save {
    background: #16A34A !important;
    color: #fff !important;
    border: 1px solid #15803D !important;
}
.result-filter-button.erp-btn-save:hover {
    background: #15803D !important;
    border-color: #166534 !important;
}

/* =========================================================
   STUDENT COUNT BADGE
   ========================================================= */
.result-student-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 28px !important;
    min-height: 28px !important;
    padding: 0 10px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 11.5px !important;
    line-height: 1 !important;
    font-weight: 700 !important;
    color: #1e40af;
    background: #dbeafe;
    border: 1px solid #93c5fd;
    border-radius: 5px;
    white-space: nowrap;
}

/* =========================================================
   INFO BANNER
   ========================================================= */
.result-information-wrapper { width: 100%; margin-bottom: 10px; }

.result-information-grid {
    width: 100%;
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 8px;
}

.result-information-item {
    border: 1px solid #93c5fd;
    border-radius: 6px;
    background: #eff6ff;
    padding: 6px 10px;
    min-width: 0;
    line-height: 1.15;
}
.result-information-label {
    display: block;
    font-size: 9.5px;
    color: #1e40af;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-bottom: 2px;
}
.result-information-value {
    display: block;
    font-size: 12.5px;
    color: #1e3a8a;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* =========================================================
   SECTION BAR (above tables)
   ========================================================= */
.section-bar {
    font-weight: 700;
    font-size: 10.5px;
    background: #e5e7eb;
    border: 1px solid #d1d5db;
    border-bottom: 0;
    padding: 5px 10px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: #1e3a8a;
    border-radius: 6px 6px 0 0;
    margin-top: 10px;
}
.section-bar:first-of-type { margin-top: 0; }

/* =========================================================
   TABLE WRAPPERS — identical width across all tables
   ========================================================= */
.result-sheet-wrapper,
.analysis-table-wrapper,
.subject-wise-analysis-wrapper {
    width: 100%;
    max-width: 100%;
    overflow: hidden;               /* ⭐ stop tables from bleeding past the wrapper */
    border: 1px solid #d1d5db;
    border-top: 0;
    border-radius: 0 0 6px 6px;
    background: #fff;
}

/* =========================================================
   ALL TABLES — fixed layout for identical outer widths
   ========================================================= */
.result-sheet-table,
.analysis-table,
.subject-wise-analysis-table {
    width: 100%;
    max-width: 100%;
    border-collapse: collapse;
    border-spacing: 0;
    table-layout: fixed;            /* ⭐ column widths won't push the table wider */
    font-family: Arial, Helvetica, sans-serif;
    font-size: 10px;
}

.result-sheet-table th,
.result-sheet-table td,
.analysis-table th,
.analysis-table td,
.subject-wise-analysis-table th,
.subject-wise-analysis-table td {
    border: 1px solid #d1d5db !important;
    border-style: solid !important;
    border-width: 1px !important;
    text-align: center;
    vertical-align: middle;
    padding: 4px 3px;
    line-height: 1.15;
    color: #111827;
}

.result-sheet-table th,
.analysis-table th,
.subject-wise-analysis-table th {
    background: #1e3a8a;
    color: #ffffff !important;
    font-size: 9.5px;
    font-weight: 700 !important;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    white-space: normal;
    overflow-wrap: anywhere;
    border-color: #1e40af !important;
}
.result-sheet-table thead tr:nth-child(2) th,
.subject-wise-analysis-table thead tr:nth-child(2) th {
    background: #1e40af;
    font-size: 8.5px;
}

.result-sheet-table tbody tr:nth-child(even),
.analysis-table tbody tr:nth-child(even),
.subject-wise-analysis-table tbody tr:nth-child(even) {
    background: #f9fafb;
}
.result-sheet-table tbody tr:hover { background: #fef9c3; }

.result-sheet-table td:first-child {
    white-space: nowrap;
    font-weight: 700;
    color: #1e3a8a;
}

.student-name {
    text-align: left !important;
    white-space: nowrap !important;
    overflow: hidden;
    text-overflow: ellipsis;
    font-weight: 700 !important;
    color: #111827 !important;
}
.subject-name {
    display: block;
    font-size: 9px;
    font-weight: 700 !important;
    line-height: 10px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: #ffffff;
}
.subject-max {
    display: block;
    font-size: 7.5px;
    font-weight: 700 !important;
    line-height: 9px;
    white-space: nowrap;
    color: #bfdbfe;
}
.optional-label {
    display: block;
    font-size: 7px;
    font-weight: 700 !important;
    line-height: 8px;
    color: #fde68a;
}
.optional-mark { font-weight: 700 !important; color: #92400e !important; }
.absent-mark   { color: #991b1b !important; font-weight: 700 !important; }
.fail-result   { color: #991b1b !important; font-weight: 700 !important; }
.pass-result   { color: #166534 !important; font-weight: 700 !important; }
.pending-result{ color: #92400e !important; font-weight: 700 !important; }

.no-marks-message {
    color: #92400e;
    font-weight: 700;
    text-align: center;
    padding: 14px !important;
    background: #fffbeb;
}

/* =========================================================
   ANALYSIS SECTIONS
   ========================================================= */
.analysis-title {
    margin-top: 10px;
    margin-bottom: 0;
    font-size: 12px;
    font-weight: 700 !important;
    color: #1e3a8a;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    background: #e5e7eb;
    border: 1px solid #d1d5db;
    border-bottom: 0;
    padding: 5px 10px;
    border-radius: 6px 6px 0 0;
}

.analysis-table td:first-child,
.subject-analysis-name {
    text-align: left !important;
    font-weight: 700 !important;
    white-space: nowrap !important;
    overflow: hidden;
    text-overflow: ellipsis;
    color: #1e3a8a;
}

.analysis-total-row,
.analysis-total-row td {
    font-weight: 700 !important;
    background: #dbeafe !important;
    color: #1e3a8a !important;
    border-color: #93c5fd !important;
}

/* =========================================================
   ALERT CARDS
   ========================================================= */
.alert-card {
    padding: 10px 14px;
    border-radius: 6px;
    font-weight: 700;
    font-size: 12px;
    margin-top: 10px;
}
.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}
.alert-success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #86efac;
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
    .result-sheet-page { padding: 15px 15px 15px 15px; }
    .result-information-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .result-sheet-table,
    .analysis-table,
    .subject-wise-analysis-table { font-size: 9.5px; }
}
@media (max-width: 900px) {
    .result-sheet-page { padding: 12px; }
    .result-filter-row { flex-wrap: wrap; }
    .result-information-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .result-sheet-table,
    .analysis-table,
    .subject-wise-analysis-table { font-size: 9px; }
}
@media (max-width: 700px) {
    .result-sheet-page { padding: 10px; }
    .result-filter-row { display: grid; grid-template-columns: auto minmax(100px, 1fr); gap: 6px; }
    .result-filter-select-wrapper { width: 100%; }
    .result-filter .year-select,
    .result-filter .exam-select,
    .result-filter .division-select { width: 100% !important; }
    .result-information-grid { grid-template-columns: 1fr; }
    .result-sheet-table,
    .analysis-table,
    .subject-wise-analysis-table { font-size: 8px; }
    .result-sheet-table th,
    .analysis-table th,
    .subject-wise-analysis-table th { font-size: 7.5px; padding: 3px 2px; }
    .result-sheet-table td,
    .analysis-table td,
    .subject-wise-analysis-table td { font-size: 7.5px; padding: 3px 2px; }
    .subject-name { font-size: 7px; }
    .subject-max { font-size: 6px; }
    .optional-label { font-size: 5.5px; }
}
</style>

@section('content')
<div class="erp-page result-sheet-page">

@php
use App\Helpers\ResultSheetBladeHelper;

$sheet = ResultSheetBladeHelper::buildSheetData([
    'displayColumns'   => $displayColumns ?? [],
    'results'          => $results ?? [],
    'standard'         => $standard ?? null,
    'classTeacher'     => $classTeacher ?? null,
    'classTeacherName' => $classTeacherName ?? null,
    'principal'        => $principal ?? null,
    'principalName'    => $principalName ?? null,
    'passPercentage'   => $passPercentage ?? null,
]);

$columnCollection         = $sheet['columns'];
$sortedResults            = $sheet['students'];
$currentStandardId        = $sheet['standardId'];
$resultPassingPercentage  = (int) $sheet['passingPercentage'];
$studentCalculations      = $sheet['calculations'];
$displayTotalMaxMarks     = $sheet['displayTotalMaxMarks'];
$isSeniorOptionalStandard = $sheet['isSeniorOptionalStandard'];
$overallGradeAnalysis     = $sheet['overallGradeAnalysis'];
$subjectAnalysis          = $sheet['subjectAnalysis'];
$classTeacherName         = $sheet['classTeacherName'];
$principalName            = $sheet['principalName'];
$hasResultSheetData       = $sheet['hasData'];

$isSeniorOptionalColumn   = fn ($c) => ResultSheetBladeHelper::isSeniorOptionalColumn($c, $currentStandardId);
$isSeniorCompulsoryColumn = fn ($c) => ResultSheetBladeHelper::isSeniorCompulsoryColumn($c, $currentStandardId);
$displayNumber            = fn ($v) => ResultSheetBladeHelper::displayNumber($v);
@endphp

{{-- =========================================================
     FILTER CARD
========================================================= --}}
<div class="erp-card no-print">
    <h2>EXAMINATION RESULT SHEET</h2>
    <form method="POST" action="{{ route('result-sheet.search') }}" class="result-filter">
        @csrf
        <div class="result-filter-row">
            <label class="result-filter-label">Academic Year</label>
            <div class="result-filter-select-wrapper">
                <select name="academic_year_id" class="year-select" required>
                    <option value="">Select</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ (string) request('academic_year_id') === (string) $year->id ? 'selected' : '' }}>{{ $year->year_name }}</option>
                    @endforeach
                </select>
                <span class="result-select-arrow"></span>
            </div>
            <label class="result-filter-label">Exam</label>
            <div class="result-filter-select-wrapper">
                <select name="exam_master_id" class="exam-select" required>
                    <option value="">Select</option>
                    @foreach($exams as $examItem)
                        <option value="{{ $examItem->id }}" {{ (string) request('exam_master_id') === (string) $examItem->id ? 'selected' : '' }}>{{ $examItem->display_exam_name ?? $examItem->exam_name }}</option>
                    @endforeach
                </select>
                <span class="result-select-arrow"></span>
            </div>
            <label class="result-filter-label">Division</label>
            <div class="result-filter-select-wrapper">
                <select name="division_id" class="division-select" required>
                    <option value="">Select</option>
                    @foreach($divisions as $divisionItem)
                        <option value="{{ $divisionItem->id }}" {{ (string) request('division_id') === (string) $divisionItem->id ? 'selected' : '' }}>{{ $divisionItem->division_name }}</option>
                    @endforeach
                </select>
                <span class="result-select-arrow"></span>
            </div>
            <button type="submit" class="erp-btn erp-btn-save result-filter-button">Generate</button>
            @if($sortedResults->count() > 0 || $columnCollection->count() > 0)
                <a href="{{ route('result-sheet.print', ['academic_year_id'=>request('academic_year_id'),'exam_master_id'=>request('exam_master_id'),'division_id'=>request('division_id')]) }}" target="_blank" class="erp-btn erp-btn-save result-filter-button">Print</a>
            @endif
            @if($sortedResults->count() > 0)
                <div class="result-student-row"><span class="result-student-count">Students : {{ $sortedResults->count() }}</span></div>
            @endif
        </div>
    </form>
</div>

{{-- =========================================================
     ALERTS
========================================================= --}}
@if(session('error'))
    <div class="alert-card alert-error">{{ session('error') }}</div>
@endif

@if(session('success'))
    <div class="alert-card alert-success">{{ session('success') }}</div>
@endif

@if($hasResultSheetData)
<div class="erp-card mt-4">

    {{-- ============ INFO BANNER ============ --}}
    <div class="result-information-wrapper">
        <div class="result-information-grid">
            <div class="result-information-item">
                <span class="result-information-label">Academic Year</span>
                <span class="result-information-value">{{ $academicYear->year_name ?? '-' }}</span>
            </div>
            <div class="result-information-item">
                <span class="result-information-label">Exam</span>
                <span class="result-information-value">{{ $exam->display_exam_name ?? $exam->exam_name ?? '-' }}</span>
            </div>
            <div class="result-information-item">
                <span class="result-information-label">Standard / Division</span>
                <span class="result-information-value">{{ ($standard->standard_name ?? '-') . ' / ' . ($division->division_name ?? '-') }}</span>
            </div>
            <div class="result-information-item">
                <span class="result-information-label">Class Teacher</span>
                <span class="result-information-value">{{ $classTeacherName ?: '-' }}</span>
            </div>
            <div class="result-information-item">
                <span class="result-information-label">Principal</span>
                <span class="result-information-value">{{ $principalName ?: '-' }}</span>
            </div>
            <div class="result-information-item">
                <span class="result-information-label">Total Maximum Marks</span>
                <span class="result-information-value">{{ $displayNumber($displayTotalMaxMarks) }}</span>
            </div>
            <div class="result-information-item">
                <span class="result-information-label">Overall Pass %</span>
                <span class="result-information-value">{{ $resultPassingPercentage }}%</span>
            </div>
            <div class="result-information-item">
                <span class="result-information-label">Total Students</span>
                <span class="result-information-value">{{ $sortedResults->count() }}</span>
            </div>
        </div>
    </div>

    {{-- ============ RESULT SHEET ============ --}}
    <div class="section-bar">Result Sheet</div>
    <div class="result-sheet-wrapper">
        <table class="result-sheet-table">
            <thead>
                <tr>
                    <th style="width: 5%;">Roll No</th>
                    <th style="width: 15%;">Student Name</th>
                    @foreach($columnCollection as $column)
                        @php
                            $subjectMax = (float) ($column->max_marks ?? 0);
                            $subjectPassing = ResultSheetBladeHelper::resolveSubjectPassing($column, $currentStandardId);
                            $subjectIsOptional = $isSeniorOptionalStandard ? $isSeniorOptionalColumn($column) : ((int) ($column->is_optional ?? 0) === 1);
                        @endphp
                        <th colspan="2">
                            <span class="subject-name">{{ $column->subject_name ?? $column->subject_code ?? '-' }}</span>
                            @if($subjectIsOptional)<span class="optional-label">Optional Subject</span>@endif
                            <span class="subject-max">Max = {{ $displayNumber($subjectMax) }}</span>
                            <span class="subject-max">Pass = {{ $displayNumber($subjectPassing) }}</span>
                        </th>
                    @endforeach
                    <th>Total <span class="subject-max">Max = {{ $displayNumber($displayTotalMaxMarks) }}</span></th>
                    <th>Per. %</th>
                    <th>Grade</th>
                    <th>Result</th>
                </tr>
                <tr>
                    <th></th>
                    <th></th>
                    @foreach($columnCollection as $column)<th>Marks</th><th>Grade</th>@endforeach
                    <th></th><th></th><th></th><th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($sortedResults as $student)
                @php $studentCalc = $studentCalculations[ResultSheetBladeHelper::getStudentKey($student)] ?? null; @endphp
                @if($studentCalc)
                <tr>
                    <td>{{ $student->roll_no ?: '-' }}</td>
                    <td class="student-name" title="{{ $student->full_student_name ?? '' }}">{{ $student->full_student_name ?: '-' }}</td>
                    @foreach($studentCalc['subject_details'] as $detail)
                        @php $mark=$detail['mark'] ?? '-'; $grade=$detail['grade'] ?? '-'; $isOptional=!empty($detail['optional']); @endphp
                        <td>
                            @if($isOptional)<span class="optional-mark">OPT</span>
                            @elseif(strtoupper(trim((string)$mark)) === 'AB')<span class="absent-mark">AB</span>
                            @elseif($mark === '-')
                                -
                            @elseif(is_numeric($mark)){{ $displayNumber($mark) }}
                            @else{{ $mark }}@endif
                        </td>
                        <td>
                            @if($isOptional)<span class="optional-mark">OPT</span>
                            @elseif(strtoupper(trim((string)$grade)) === 'AB')<span class="absent-mark">AB</span>
                            @elseif(strtoupper(trim((string)$grade)) === 'F')<span class="fail-result">F</span>
                            @elseif($grade === '-')
                                -
                            @else{{ $grade }}@endif
                        </td>
                    @endforeach
                    <td>{{ $displayNumber($studentCalc['total_marks']) }}</td>
                    <td>{{ $studentCalc['percentage'] === null ? '-' : (int) round($studentCalc['percentage']).'%' }}</td>
                    <td>{{ $studentCalc['grade'] }}</td>
                    <td>
                        @if($studentCalc['result'] === 'PASS')<span class="pass-result">PASS</span>
                        @elseif($studentCalc['result'] === 'FAIL')<span class="fail-result">FAIL</span>
                        @else<span class="pending-result">PENDING</span>@endif
                    </td>
                </tr>
                @endif
            @empty
                <tr>
                    <td colspan="{{ 2 + ($columnCollection->count()*2) + 4 }}" class="no-marks-message">
                        No students found for the selected Standard and Division.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- ============ OVERALL GRADE / RESULT ANALYSIS ============ --}}
    <div class="analysis-title">Overall Grade / Result Analysis</div>
    <div class="analysis-table-wrapper">
        <table class="analysis-table">
            <thead>
                <tr>
                    <th>Grade / Result</th>
                    <th>Range</th>
                    <th>Girls</th>
                    <th>Boys</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
            @foreach(['A1','A2','B1','B2','C1','C2','D','E1','E2'] as $grade)
                @php $row=$overallGradeAnalysis[$grade] ?? ['range'=>'-','girls'=>0,'boys'=>0,'total'=>0]; @endphp
                <tr>
                    <td>{{ $grade }}</td>
                    <td>{{ $row['range'] ?? '-' }}</td>
                    <td>{{ $row['girls'] ?? 0 }}</td>
                    <td>{{ $row['boys'] ?? 0 }}</td>
                    <td>{{ $row['total'] ?? 0 }}</td>
                </tr>
            @endforeach
            <tr class="analysis-total-row">
                <td>TOTAL</td>
                <td>TOTAL</td>
                <td>{{ $overallGradeAnalysis['TOTAL']['girls'] ?? 0 }}</td>
                <td>{{ $overallGradeAnalysis['TOTAL']['boys'] ?? 0 }}</td>
                <td>{{ $overallGradeAnalysis['TOTAL']['total'] ?? 0 }}</td>
            </tr>
            </tbody>
        </table>
    </div>

    {{-- ============ SUBJECT WISE ANALYSIS ============ --}}
    <div class="analysis-title">Subject Wise Analysis</div>
    <div class="subject-wise-analysis-wrapper">
        <table class="subject-wise-analysis-table">
            <thead>
                <tr>
                    <th rowspan="2">Subject</th>
                    @foreach(['A1','A2','B1','B2','C1','C2','D','E1','E2','ABSENT'] as $category)
                        <th colspan="2">{{ $category }}</th>
                    @endforeach
                </tr>
                <tr>
                    @for($i=0;$i<10;$i++)<th>Girls</th><th>Boys</th>@endfor
                </tr>
            </thead>
            <tbody>
            @foreach($columnCollection as $column)
                @php
                    $subjectCode = trim((string)($column->subject_code ?? $column->subject_id ?? $column->subject_name ?? ''));
                    $analysis = $subjectAnalysis[$subjectCode] ?? [];
                @endphp
                <tr>
                    <td class="subject-analysis-name">{{ $column->subject_name ?? $subjectCode ?? '-' }}</td>
                    @foreach(['A1','A2','B1','B2','C1','C2','D','E1','E2'] as $category)
                        <td>{{ $analysis[$category.'_girls'] ?? 0 }}</td>
                        <td>{{ $analysis[$category.'_boys'] ?? 0 }}</td>
                    @endforeach
                    <td>{{ $analysis['absent_girls'] ?? 0 }}</td>
                    <td>{{ $analysis['absent_boys'] ?? 0 }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

@elseif(request()->has('academic_year_id') && request()->has('exam_master_id') && request()->has('division_id'))
    <div class="alert-card alert-warning" style="margin-top:12px;">
        No active Standard Wise Subjects were found for the selected Exam / Standard.
    </div>
@endif

</div>
@endsection