@extends('layouts.app')

<style>
.result-sheet-page,.result-sheet-page *{box-sizing:border-box;font-family:Arial,Helvetica,sans-serif}.result-sheet-page{width:100%;padding-top:76px;padding-left:76px;padding-right:20px;padding-bottom:20px}.result-filter{width:100%}.result-filter-row{width:100%;display:flex;align-items:center;flex-wrap:nowrap;gap:7px}.result-filter-label{flex:0 0 auto;font-size:12px!important;font-weight:600!important;color:#374151;white-space:nowrap}.result-filter select{height:30px!important;padding:3px 25px 3px 8px!important;font-family:Arial,Helvetica,sans-serif!important;font-size:12px!important;line-height:20px!important;color:#111827!important;background:#fff!important;border:1px solid #9CA3AF!important;border-radius:4px!important;cursor:pointer;font-weight:500!important}.result-filter select,.result-filter select option{font-family:Arial,Helvetica,sans-serif!important;font-size:12px!important;font-weight:500!important}.result-filter .year-select{width:115px!important}.result-filter .exam-select{width:190px!important}.result-filter .division-select{width:65px!important}.result-filter-button,.result-filter-button.erp-btn,.result-filter-button.erp-btn-save{height:30px!important;min-height:30px!important;min-width:70px!important;padding:0 12px!important;margin:0!important;display:inline-flex!important;align-items:center!important;justify-content:center!important;font-family:Arial,Helvetica,sans-serif!important;font-size:12px!important;line-height:30px!important;font-weight:600!important;white-space:nowrap!important;text-align:center!important;border-radius:4px!important}.result-student-count{display:inline-flex;align-items:center;justify-content:center;height:30px!important;min-height:30px!important;padding:0 9px!important;font-family:Arial,Helvetica,sans-serif!important;font-size:12px!important;line-height:1!important;font-weight:600!important;color:#1D4ED8;background:#DBEAFE;border:1px solid #BFDBFE;border-radius:4px;white-space:nowrap}.result-information-wrapper{width:100%;margin-bottom:15px}.result-information-grid{width:100%;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px}.result-information-item{border:1px solid #D1D5DB;border-radius:5px;background:#F9FAFB;padding:9px 11px;min-width:0}.result-information-label{display:block;font-size:11px;color:#6B7280;font-weight:700;margin-bottom:3px}.result-information-value{display:block;font-size:14px;color:#111827;font-weight:700;white-space:nowrap;overflow:visible}.result-sheet-wrapper{width:100%;overflow:visible;margin-top:0}.result-sheet-table{width:100%;max-width:100%;border-collapse:collapse;border-spacing:0;table-layout:auto;font-family:Arial,Helvetica,sans-serif;font-size:11px;border:1px solid #333}.result-sheet-table th,.result-sheet-table td{border:1px solid #333!important;border-style:solid!important;border-width:1px!important;text-align:center;vertical-align:middle;padding:5px 4px}.result-sheet-table th{background:#dbeafe;font-size:11px;font-weight:700!important;line-height:1.1;white-space:normal;overflow-wrap:normal}.result-sheet-table td{font-size:10.5px;line-height:1.15;white-space:nowrap}.result-sheet-table td:first-child{white-space:nowrap;font-weight:700}.student-name{ text-align:left!important;white-space:nowrap!important;font-weight:700!important;color:#111827!important}.subject-name{display:block;font-size:9.5px;font-weight:700!important;line-height:10px;white-space:nowrap}.subject-max{display:block;font-size:8px;font-weight:700!important;line-height:9px;white-space:nowrap}.optional-label{display:block;font-size:7.5px;font-weight:700!important;line-height:8px}.optional-mark{font-weight:700!important;color:#92400e!important}.absent-mark{color:#991b1b!important;font-weight:700!important}.fail-result{color:#991b1b!important;font-weight:700!important}.pass-result{color:#166534!important;font-weight:700!important}.pending-result{color:#92400e!important;font-weight:700!important}.no-marks-message{color:#92400e;font-weight:700;text-align:center;padding:14px!important;background:#fffbeb}.analysis-title{margin-top:22px;margin-bottom:9px;font-size:17px;font-weight:700!important;color:#1d4ed8}.analysis-table-wrapper,.subject-wise-analysis-wrapper{width:100%;overflow:visible}.analysis-table,.subject-wise-analysis-table{width:100%;border-collapse:collapse;table-layout:auto;font-size:11px;border:1px solid #333}.analysis-table th,.analysis-table td,.subject-wise-analysis-table th,.subject-wise-analysis-table td{border:1px solid #333!important;padding:6px 5px;text-align:center;vertical-align:middle;font-size:11px;white-space:nowrap}.analysis-table th,.subject-wise-analysis-table th{background:#dbeafe;font-weight:700!important}.analysis-table td:first-child,.subject-analysis-name{text-align:left!important;font-weight:700!important;white-space:nowrap!important}.analysis-total-row,.analysis-total-row td{font-weight:700!important;background:#e5e7eb!important}@media(max-width:1200px){.result-sheet-page{padding-top:60px;padding-left:55px;padding-right:15px}.result-information-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.result-sheet-table{font-size:10px}.result-sheet-table th{font-size:10px}.result-sheet-table td{font-size:9.5px}}@media(max-width:900px){.result-sheet-page{padding-top:45px;padding-left:30px;padding-right:10px}.result-filter-row{flex-wrap:wrap}.result-information-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.result-sheet-table{font-size:9px}.result-sheet-table th{font-size:9px}.result-sheet-table td{font-size:8.5px}}@media(max-width:700px){.result-sheet-page{padding-top:25px;padding-left:15px;padding-right:8px}.result-filter-row{display:grid;grid-template-columns:auto minmax(100px,1fr);gap:7px}.result-filter-select-wrapper{width:100%}.result-filter .year-select,.result-filter .exam-select,.result-filter .division-select{width:100%!important}.result-information-grid{grid-template-columns:1fr}.result-sheet-table{font-size:8px}.result-sheet-table th{font-size:8px;padding:3px 2px}.result-sheet-table td{font-size:7.5px;padding:3px 2px}.subject-name{font-size:7px}.subject-max{font-size:6px}.optional-label{font-size:5.5px}.analysis-table th,.analysis-table td,.subject-wise-analysis-table th,.subject-wise-analysis-table td{font-size:7px;padding:3px 2px}}
</style>

@section('content')
<div class="erp-page result-sheet-page">

@php
use App\Helpers\ResultSheetBladeHelper;

$resultCollection = collect($results ?? []);
$columnCollection = collect($displayColumns ?? []);
$sortedResults = ResultSheetBladeHelper::getUniqueSortedStudents($resultCollection);

$currentStandardId = (int) ($standard->id ?? 0);
$resultPassingPercentage = (int) \App\Helpers\MarksHelper::getPassingPercentage($currentStandardId);

$studentCalculations = ResultSheetBladeHelper::buildStudentCalculations(
    $sortedResults,
    $columnCollection,
    $currentStandardId,
    $resultPassingPercentage
);

$displayTotalMaxMarks = ResultSheetBladeHelper::calculateDisplayTotalMaxMarks(
    $columnCollection,
    $currentStandardId
);

$isSeniorOptionalStandard = ResultSheetBladeHelper::isSeniorOptionalStandard($currentStandardId);

$isSeniorOptionalColumn = fn ($column) => ResultSheetBladeHelper::isSeniorOptionalColumn($column, $currentStandardId);
$isSeniorCompulsoryColumn = fn ($column) => ResultSheetBladeHelper::isSeniorCompulsoryColumn($column, $currentStandardId);

$extractStaffName = fn ($record) => ResultSheetBladeHelper::extractStaffName($record);
$formatStaffName = fn ($name) => ResultSheetBladeHelper::formatStaffName($name);

$rawClassTeacherName = $extractStaffName($classTeacher ?? null);
if ($rawClassTeacherName === '' && isset($classTeacherName)) {
    $rawClassTeacherName = trim((string) $classTeacherName);
}
$classTeacherName = $formatStaffName($rawClassTeacherName);

$rawPrincipalName = $extractStaffName($principal ?? null);
if ($rawPrincipalName === '' && isset($principalName)) {
    $rawPrincipalName = trim((string) $principalName);
}
$principalName = $formatStaffName($rawPrincipalName);

$displayNumber = fn ($value) => ResultSheetBladeHelper::displayNumber($value);

$analysisRanges = [
    'A1' => '91-100%',
    'A2' => '81-90%',
    'B1' => '71-80%',
    'B2' => '61-70%',
    'C1' => '51-60%',
    'C2' => '41-50%',
    'D'  => '33-40%',
    'E1' => '21-32%',
    'E2' => '1-20%',
    'F'  => '0%',
];

$overallGradeAnalysis = [];
foreach ($analysisRanges as $grade => $range) {
    $overallGradeAnalysis[$grade] = [
        'range' => $range,
        'girls' => 0,
        'boys' => 0,
        'total' => 0,
    ];
}
$overallGradeAnalysis['TOTAL'] = [
    'range' => 'TOTAL',
    'girls' => 0,
    'boys' => 0,
    'total' => $sortedResults->count(),
];

foreach ($sortedResults as $student) {
    $calc = $studentCalculations[ResultSheetBladeHelper::getStudentKey($student)] ?? null;
    if (!$calc) continue;

    $gender = strtoupper(trim((string) ($student->gender ?? $student->sex ?? '')));
    $genderKey = in_array($gender, ['F','FEMALE','GIRL','GIRLS'], true) ? 'girls' : 'boys';
    $grade = strtoupper(trim((string) $calc['grade']));

    if (isset($overallGradeAnalysis[$grade])) {
        $overallGradeAnalysis[$grade][$genderKey]++;
        $overallGradeAnalysis[$grade]['total']++;
    }
    $overallGradeAnalysis['TOTAL'][$genderKey]++;
}

$subjectAnalysis = ResultSheetBladeHelper::calculateSubjectAnalysis(
    $sortedResults,
    $columnCollection,
    $studentCalculations,
    true
);

$hasResultSheetData = $columnCollection->count() > 0;
@endphp

<div class="erp-card no-print">
    <h2 style="font-size:21px;font-weight:700;color:#1d4ed8;margin:0 0 13px 0;">EXAMINATION RESULT SHEET</h2>
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
            </div>
            <label class="result-filter-label">Exam</label>
            <div class="result-filter-select-wrapper">
                <select name="exam_master_id" class="exam-select" required>
                    <option value="">Select</option>
                    @foreach($exams as $examItem)
                        <option value="{{ $examItem->id }}" {{ (string) request('exam_master_id') === (string) $examItem->id ? 'selected' : '' }}>{{ $examItem->display_exam_name ?? $examItem->exam_name }}</option>
                    @endforeach
                </select>
            </div>
            <label class="result-filter-label">Division</label>
            <div class="result-filter-select-wrapper">
                <select name="division_id" class="division-select" required>
                    <option value="">Select</option>
                    @foreach($divisions as $divisionItem)
                        <option value="{{ $divisionItem->id }}" {{ (string) request('division_id') === (string) $divisionItem->id ? 'selected' : '' }}>{{ $divisionItem->division_name }}</option>
                    @endforeach
                </select>
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

@if(session('error'))
<div class="erp-card mt-3" style="color:#991b1b;background:#fee2e2;border:1px solid #fecaca;font-weight:700;">{{ session('error') }}</div>
@endif

@if(session('success'))
<div class="erp-card mt-3" style="color:#166534;background:#dcfce7;border:1px solid #bbf7d0;font-weight:700;">{{ session('success') }}</div>
@endif

@if($hasResultSheetData)
<div class="erp-card mt-4">
    <div class="result-information-wrapper">
        <div class="result-information-grid">
            <div class="result-information-item"><span class="result-information-label">Academic Year</span><span class="result-information-value">{{ $academicYear->year_name ?? '-' }}</span></div>
            <div class="result-information-item"><span class="result-information-label">Exam</span><span class="result-information-value">{{ $exam->display_exam_name ?? $exam->exam_name ?? '-' }}</span></div>
            <div class="result-information-item"><span class="result-information-label">Standard / Division</span><span class="result-information-value">{{ ($standard->standard_name ?? '-') . ' / ' . ($division->division_name ?? '-') }}</span></div>
            <div class="result-information-item"><span class="result-information-label">Class Teacher</span><span class="result-information-value">{{ $classTeacherName ?: '-' }}</span></div>
            <div class="result-information-item"><span class="result-information-label">Principal</span><span class="result-information-value">{{ $principalName ?: '-' }}</span></div>
            <div class="result-information-item"><span class="result-information-label">Total Maximum Marks</span><span class="result-information-value">{{ $displayNumber($displayTotalMaxMarks) }}</span></div>
            <div class="result-information-item"><span class="result-information-label">Overall Pass %</span><span class="result-information-value">{{ $resultPassingPercentage }}%</span></div>
            <div class="result-information-item"><span class="result-information-label">Total Students</span><span class="result-information-value">{{ $sortedResults->count() }}</span></div>
        </div>
    </div>

    <div class="result-sheet-wrapper">
        <table class="result-sheet-table">
            <thead>
                <tr>
                    <th>Roll No</th>
                    <th>Student Name</th>
                    @foreach($columnCollection as $column)
                        @php
                            $subjectMax = (float) ($column->max_marks ?? 0);
                            $subjectPassing = $subjectMax > 0 ? \App\Helpers\MarksHelper::getPassingMarks($currentStandardId, $subjectMax) : 0;
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
                <tr><th></th><th></th>@foreach($columnCollection as $column)<th>Marks</th><th>Grade</th>@endforeach<th></th><th></th><th></th><th></th></tr>
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
                <tr><td colspan="{{ 2 + ($columnCollection->count()*2) + 4 }}" class="no-marks-message">No students found for the selected Standard and Division.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="analysis-title">Overall Grade / Result Analysis</div>
    <div class="analysis-table-wrapper">
        <table class="analysis-table">
            <thead><tr><th>Grade / Result</th><th>Range</th><th>Girls</th><th>Boys</th><th>Total</th></tr></thead>
            <tbody>
            @foreach(['A1','A2','B1','B2','C1','C2','D','E1','E2'] as $grade)
                @php $row=$overallGradeAnalysis[$grade] ?? ['range'=>'-','girls'=>0,'boys'=>0,'total'=>0]; @endphp
                <tr><td>{{ $grade }}</td><td>{{ $row['range'] ?? '-' }}</td><td>{{ $row['girls'] ?? 0 }}</td><td>{{ $row['boys'] ?? 0 }}</td><td>{{ $row['total'] ?? 0 }}</td></tr>
            @endforeach
            <tr class="analysis-total-row"><td>TOTAL</td><td>TOTAL</td><td>{{ $overallGradeAnalysis['TOTAL']['girls'] ?? 0 }}</td><td>{{ $overallGradeAnalysis['TOTAL']['boys'] ?? 0 }}</td><td>{{ $overallGradeAnalysis['TOTAL']['total'] ?? 0 }}</td></tr>
            </tbody>
        </table>
    </div>

    <div class="analysis-title">Subject Wise Analysis</div>
    <div class="subject-wise-analysis-wrapper">
        <table class="subject-wise-analysis-table">
            <thead>
                <tr><th rowspan="2">Subject</th>@foreach(['A1','A2','B1','B2','C1','C2','D','E1','E2','ABSENT'] as $category)<th colspan="2">{{ $category }}</th>@endforeach</tr>
                <tr>@for($i=0;$i<10;$i++)<th>Girls</th><th>Boys</th>@endfor</tr>
            </thead>
            <tbody>
            @foreach($columnCollection as $column)
                @php $subjectCode=trim((string)($column->subject_code ?? $column->subject_id ?? $column->subject_name ?? '')); $analysis=$subjectAnalysis[$subjectCode] ?? []; @endphp
                <tr>
                    <td class="subject-analysis-name">{{ $column->subject_name ?? $subjectCode ?? '-' }}</td>
                    @foreach(['A1','A2','B1','B2','C1','C2','D','E1','E2'] as $category)
                        <td>{{ $analysis[$category.'_girls'] ?? 0 }}</td><td>{{ $analysis[$category.'_boys'] ?? 0 }}</td>
                    @endforeach
                    <td>{{ $analysis['absent_girls'] ?? 0 }}</td><td>{{ $analysis['absent_boys'] ?? 0 }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@elseif(request()->has('academic_year_id') && request()->has('exam_master_id') && request()->has('division_id'))
<div class="erp-card mt-4"><div style="color:#92400e;background:#fffbeb;border:1px solid #fde68a;padding:15px;font-weight:700;border-radius:5px;">No active Standard Wise Subjects were found for the selected Exam / Standard.</div></div>
@endif

</div>
@endsection
