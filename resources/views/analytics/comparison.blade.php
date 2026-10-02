@extends('layouts.app')

@section('content')

<style>
    .analytics-filters {
        display: flex; flex-wrap: wrap; gap: 10px;
        align-items: flex-end; padding-bottom: 4px;
    }
    .analytics-filters .filter-group {
        display: flex; flex-direction: column; gap: 3px; flex-shrink: 0;
    }
    .analytics-filters label {
        font-size: 11px; font-weight: 700; color: #374151; white-space: nowrap;
    }
    .analytics-select-wrap { position: relative; display: inline-block; }
    .analytics-select-wrap .analytics-select-arrow {
        position: absolute; top: 50%; right: 10px; transform: translateY(-50%);
        width: 0; height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 6px solid #6b7280;
        pointer-events: none;
    }
    .analytics-select-wrap.disabled .analytics-select-arrow { border-top-color: #cbd5e1; }

    .analytics-filters select {
        height: 34px;
        padding: 4px 28px 4px 8px;
        font-size: 12px;
        border: 1px solid #9ca3af;
        border-radius: 4px;
        background: #fff;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        cursor: pointer;
    }
    .analytics-filters select:focus {
        outline: none; border-color: #2563eb;
        box-shadow: 0 0 0 2px rgba(37,99,235,.15);
    }
    .analytics-filters select:disabled {
        background: #f3f4f6; color: #9ca3af; cursor: not-allowed;
    }
    .filter-ay   { width: 130px; }
    .filter-exam { width: 200px; }
    .filter-std  { width: 130px; }
    .filter-div  { width: 100px; }

    .vs-divider {
        display: inline-flex; align-items: center; justify-content: center;
        height: 34px; padding: 0 8px;
        font-weight: 700; color: #6b7280; font-size: 12px;
        align-self: flex-end;
    }

    .analytics-placeholder {
        border: 2px dashed #bfdbfe; background: #eff6ff; color: #1e40af;
        padding: 32px 20px; border-radius: 8px; text-align: center; margin-top: 16px;
    }
    .analytics-placeholder h3 { margin: 0 0 8px; font-size: 18px; }
    .analytics-placeholder p  { margin: 4px 0; font-size: 13px; }
    .analytics-placeholder .steps {
        display: inline-block; text-align: left; margin: 12px auto 0;
        background: #fff; border: 1px solid #bfdbfe; border-radius: 6px;
        padding: 12px 18px; font-size: 13px; color: #1e3a8a;
    }
    .analytics-placeholder .steps li { margin: 4px 0; }

    .cmp-cards {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }
    @media (max-width: 1100px) { .cmp-cards { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 600px)  { .cmp-cards { grid-template-columns: 1fr; } }

    .cmp-box {
        border: 1px solid #d1d5db; border-radius: 6px;
        background: #f8fafc; padding: 12px 14px;
    }
    .cmp-title {
        font-size: 11px; font-weight: 700; color: #374151;
        text-transform: uppercase; letter-spacing: .3px;
        margin-bottom: 8px;
    }
    .cmp-row {
        display: flex; justify-content: space-between;
        font-size: 12px; margin-bottom: 4px; color: #374151;
    }
    .cmp-row .val { font-weight: 700; color: #111827; }
    .cmp-row .val.exam1 { color: #1d4ed8; }
    .cmp-row .val.exam2 { color: #7c3aed; }

    .cmp-delta {
        margin-top: 8px; padding-top: 8px;
        border-top: 1px dashed #cbd5e1;
        display: flex; justify-content: space-between; align-items: center;
        font-weight: 700;
    }
    .delta-up   { color: #166534; }
    .delta-down { color: #991b1b; }
    .delta-flat { color: #6b7280; }

    .analytics-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    .analytics-table th {
        background: #dbeafe; border: 1px solid #d1d5db;
        padding: 6px; text-align: center;
    }
    .analytics-table td { border: 1px solid #d1d5db; padding: 6px; }
    .analytics-table a.student-link {
        color: #1d4ed8; font-weight: 600; text-decoration: none; cursor: pointer;
    }
    .analytics-table a.student-link:hover { text-decoration: underline; }

    .td-up   { color: #166534; font-weight: 700; }
    .td-down { color: #991b1b; font-weight: 700; }
    .td-flat { color: #6b7280; font-weight: 700; }

    .status-pill {
        display: inline-block;
        padding: 2px 8px; border-radius: 10px;
        font-size: 10px; font-weight: 700;
    }
    .status-improved { background: #dcfce7; color: #166534; }
    .status-declined { background: #fee2e2; color: #991b1b; }
    .status-same     { background: #e5e7eb; color: #374151; }

    .chart-grid {
        display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;
    }
    @media (max-width: 1000px) { .chart-grid { grid-template-columns: 1fr; } }
    .chart-box {
        min-height: 320px; border: 1px solid #d1d5db; border-radius: 6px;
        background: #fff; padding: 10px;
    }
    .chart-title {
        font-size: 13px; font-weight: 700; color: #1e40af; margin-bottom: 8px;
    }

    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    @media (max-width: 1000px) { .two-col { grid-template-columns: 1fr; } }

    /* ---------- Modal ---------- */
    .detail-modal {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,.5); z-index: 9999;
        align-items: center; justify-content: center; padding: 20px;
    }
    .detail-modal-inner {
        background: #fff; border-radius: 8px;
        max-width: 900px; width: 100%; max-height: 90vh; overflow: auto;
    }
    .detail-modal-header {
        padding: 14px 18px; border-bottom: 1px solid #e5e7eb;
        display: flex; justify-content: space-between; align-items: center;
    }
    .detail-modal-title {
        margin: 0; font-size: 16px; font-weight: 700; color: #1d4ed8;
    }
    .detail-modal-close {
        background: transparent; border: 0; font-size: 22px; cursor: pointer;
        color: #6b7280; line-height: 1; padding: 4px 8px; border-radius: 4px;
    }
    .detail-modal-close:hover { background: #f3f4f6; color: #111827; }
    .detail-modal-body { padding: 14px 18px; }
</style>

<div class="erp-page">

    {{-- ===================== FILTERS ===================== --}}
    <div class="erp-card">
        <h2 class="text-xl font-bold text-blue-700 mb-3">EXAM COMPARISON ANALYSIS</h2>

        <form method="GET" action="{{ route('analytics.comparison') }}" id="cmpFilterForm">
            <div class="analytics-filters">

                <div class="filter-group">
                    <label>Standard <span style="color:#dc2626;">*</span></label>
                    <div class="analytics-select-wrap">
                        <select name="standard_id" id="cmp_std" class="filter-std" required>
                            <option value="">Select</option>
                            @foreach($standards as $std)
                                <option value="{{ $std->id }}" {{ request('standard_id') == $std->id ? 'selected' : '' }}>
                                    {{ $std->standard_name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="analytics-select-arrow"></span>
                    </div>
                </div>

                <div class="filter-group">
                    <label>Division <span style="color:#dc2626;">*</span></label>
                    <div class="analytics-select-wrap">
                        <select name="division_id" id="cmp_div" class="filter-div" required>
                            <option value="">Select</option>
                            @foreach($divisions as $div)
                                <option value="{{ $div->id }}" {{ request('division_id') == $div->id ? 'selected' : '' }}>
                                    {{ $div->division_name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="analytics-select-arrow"></span>
                    </div>
                </div>

                <div class="filter-group">
                    <label>Year 1 <span style="color:#dc2626;">*</span></label>
                    <div class="analytics-select-wrap">
                        <select name="year1_id" id="cmp_year1" class="filter-ay" required>
                            <option value="">Select</option>
                            @foreach($academicYears as $y)
                                <option value="{{ $y->id }}" {{ request('year1_id') == $y->id ? 'selected' : '' }}>
                                    {{ $y->year_name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="analytics-select-arrow"></span>
                    </div>
                </div>

                <div class="filter-group">
                    <label>Exam 1 <span style="color:#dc2626;">*</span></label>
                    <div class="analytics-select-wrap">
                        <select name="exam1_id" id="cmp_exam1" class="filter-exam" required>
                            <option value="">Select</option>
                            @foreach($exams as $ex)
                                <option value="{{ $ex->id }}" {{ request('exam1_id') == $ex->id ? 'selected' : '' }}>
                                    {{ $ex->exam_name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="analytics-select-arrow"></span>
                    </div>
                </div>

                <span class="vs-divider">VS</span>

                <div class="filter-group">
                    <label>Year 2 <span style="color:#dc2626;">*</span></label>
                    <div class="analytics-select-wrap">
                        <select name="year2_id" id="cmp_year2" class="filter-ay" required>
                            <option value="">Select</option>
                            @foreach($academicYears as $y)
                                <option value="{{ $y->id }}" {{ request('year2_id') == $y->id ? 'selected' : '' }}>
                                    {{ $y->year_name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="analytics-select-arrow"></span>
                    </div>
                </div>

                <div class="filter-group">
                    <label>Exam 2 <span style="color:#dc2626;">*</span></label>
                    <div class="analytics-select-wrap">
                        <select name="exam2_id" id="cmp_exam2" class="filter-exam" required>
                            <option value="">Select</option>
                            @foreach($exams as $ex)
                                <option value="{{ $ex->id }}" {{ request('exam2_id') == $ex->id ? 'selected' : '' }}>
                                    {{ $ex->exam_name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="analytics-select-arrow"></span>
                    </div>
                </div>

                <button type="submit" class="erp-btn erp-btn-save" style="height:34px;">
                    Compare
                </button>

                @if($hasFilters ?? false)
                    <a href="{{ route('analytics.comparison') }}"
                       class="erp-btn"
                       style="height:34px;background:#e5e7eb;color:#374151;text-decoration:none;">
                        Reset
                    </a>
                @endif

            </div>
        </form>
    </div>

@if(!($hasFilters ?? false))

    <div class="analytics-placeholder">
        <h3>Select two exams to compare</h3>
        <p>Compare any two exams — same year or across years — for the same class.</p>
        <ul class="steps">
            <li>1. Choose <strong>Standard</strong> and <strong>Division</strong></li>
            <li>2. Choose <strong>Year 1</strong> + <strong>Exam 1</strong> (baseline)</li>
            <li>3. Choose <strong>Year 2</strong> + <strong>Exam 2</strong> (comparison)</li>
            <li>4. Click <strong>Compare</strong></li>
        </ul>
    </div>

@elseif(!empty($emptyMsg))

    <div class="erp-card mt-4" style="background:#fffbeb;border:1px solid #fde68a;color:#92400e;padding:15px;font-weight:700;border-radius:5px;">
        {{ $emptyMsg }}
    </div>

@else

    <div class="erp-card mt-4">
        <h3 class="text-lg font-bold text-blue-700 mb-3">
            Overall Comparison —
            <span style="color:#1d4ed8;">{{ $kpi['exam1']['label'] }} ({{ $kpi['exam1']['year_label'] }})</span>
            <span style="color:#6b7280;">vs</span>
            <span style="color:#7c3aed;">{{ $kpi['exam2']['label'] }} ({{ $kpi['exam2']['year_label'] }})</span>
        </h3>

        <div class="cmp-cards">

            <div class="cmp-box">
                <div class="cmp-title">Average %</div>
                <div class="cmp-row"><span>Exam 1</span><span class="val exam1">{{ number_format($kpi['exam1']['avg_pct'], 2) }}%</span></div>
                <div class="cmp-row"><span>Exam 2</span><span class="val exam2">{{ number_format($kpi['exam2']['avg_pct'], 2) }}%</span></div>
                <div class="cmp-delta">
                    <span>Change</span>
                    <span class="{{ $kpi['delta']['avg_pct'] > 0 ? 'delta-up' : ($kpi['delta']['avg_pct'] < 0 ? 'delta-down' : 'delta-flat') }}">
                        {{ $kpi['delta']['avg_pct'] > 0 ? '↑ +' : ($kpi['delta']['avg_pct'] < 0 ? '↓ ' : '= ') }}{{ number_format($kpi['delta']['avg_pct'], 2) }}%
                    </span>
                </div>
            </div>

            <div class="cmp-box">
                <div class="cmp-title">Pass %</div>
                <div class="cmp-row"><span>Exam 1</span><span class="val exam1">{{ number_format($kpi['exam1']['pass_pct'], 1) }}%</span></div>
                <div class="cmp-row"><span>Exam 2</span><span class="val exam2">{{ number_format($kpi['exam2']['pass_pct'], 1) }}%</span></div>
                <div class="cmp-delta">
                    <span>Change</span>
                    <span class="{{ $kpi['delta']['pass_pct'] > 0 ? 'delta-up' : ($kpi['delta']['pass_pct'] < 0 ? 'delta-down' : 'delta-flat') }}">
                        {{ $kpi['delta']['pass_pct'] > 0 ? '↑ +' : ($kpi['delta']['pass_pct'] < 0 ? '↓ ' : '= ') }}{{ number_format($kpi['delta']['pass_pct'], 1) }}%
                    </span>
                </div>
            </div>

            <div class="cmp-box">
                <div class="cmp-title">Highest %</div>
                <div class="cmp-row"><span>Exam 1</span><span class="val exam1">{{ number_format($kpi['exam1']['highest'], 2) }}%</span></div>
                <div class="cmp-row"><span>Exam 2</span><span class="val exam2">{{ number_format($kpi['exam2']['highest'], 2) }}%</span></div>
                <div class="cmp-delta">
                    <span>Change</span>
                    <span class="{{ $kpi['delta']['highest'] > 0 ? 'delta-up' : ($kpi['delta']['highest'] < 0 ? 'delta-down' : 'delta-flat') }}">
                        {{ $kpi['delta']['highest'] > 0 ? '↑ +' : ($kpi['delta']['highest'] < 0 ? '↓ ' : '= ') }}{{ number_format($kpi['delta']['highest'], 2) }}%
                    </span>
                </div>
            </div>

            <div class="cmp-box">
                <div class="cmp-title">Student Movement</div>
                <div class="cmp-row"><span>Improved</span><span class="val" style="color:#166534;">{{ $improvedCount }}</span></div>
                <div class="cmp-row"><span>Declined</span><span class="val" style="color:#991b1b;">{{ $declinedCount }}</span></div>
                <div class="cmp-row"><span>Same</span><span class="val" style="color:#6b7280;">{{ $sameCount }}</span></div>
            </div>

        </div>
    </div>

    <div class="erp-card mt-4">
        <h3 class="text-lg font-bold text-blue-700 mb-3">Comparison Charts</h3>
        <div class="chart-grid">
            <div>
                <div class="chart-title">Subject-wise Average %</div>
                <div class="chart-box"><canvas id="cmpSubjectChart"></canvas></div>
            </div>
            <div>
                <div class="chart-title">Student Movement</div>
                <div class="chart-box"><canvas id="cmpMovementChart"></canvas></div>
            </div>
        </div>
    </div>

    <div class="erp-card mt-4">
        <h3 class="text-lg font-bold text-blue-700 mb-3">Subject-wise Comparison</h3>
        <table class="analytics-table">
            <thead>
                <tr>
                    <th>Sr.</th>
                    <th>Subject</th>
                    <th>Exam 1 Avg %</th>
                    <th>Exam 2 Avg %</th>
                    <th>Change</th>
                    <th>Trend</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subjectComparison as $i => $row)
                    <tr>
                        <td align="center">{{ $i + 1 }}</td>
                        <td>{{ $row->subject_name }}</td>
                        <td align="center">
                            {{ $row->avg1_pct !== null ? number_format($row->avg1_pct, 2).'%' : '-' }}
                            @if($row->pass1_pct !== null)
                                <div style="font-size:10px;color:#6b7280;">Pass: {{ $row->pass1_pct }}%</div>
                            @endif
                        </td>
                        <td align="center">
                            {{ $row->avg2_pct !== null ? number_format($row->avg2_pct, 2).'%' : '-' }}
                            @if($row->pass2_pct !== null)
                                <div style="font-size:10px;color:#6b7280;">Pass: {{ $row->pass2_pct }}%</div>
                            @endif
                        </td>
                        <td align="center">
                            @if($row->delta === null)
                                <span class="td-flat">-</span>
                            @elseif($row->delta > 0)
                                <span class="td-up">↑ +{{ number_format($row->delta, 2) }}%</span>
                            @elseif($row->delta < 0)
                                <span class="td-down">↓ {{ number_format($row->delta, 2) }}%</span>
                            @else
                                <span class="td-flat">= 0</span>
                            @endif
                        </td>
                        <td align="center">
                            @if($row->delta === null)
                                <span class="status-pill status-same">N/A</span>
                            @elseif($row->delta > 0.5)
                                <span class="status-pill status-improved">IMPROVED</span>
                            @elseif($row->delta < -0.5)
                                <span class="status-pill status-declined">DECLINED</span>
                            @else
                                <span class="status-pill status-same">STABLE</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" align="center">No subjects to compare.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="erp-card mt-4">
        <h3 class="text-lg font-bold text-blue-700 mb-3">Student-wise Comparison</h3>
        <table class="analytics-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Student Name</th>
                    <th>Exam 1 %</th>
                    <th>Exam 2 %</th>
                    <th>Change</th>
                    <th>Status</th>
                    <th>Detail</th>
                </tr>
            </thead>
            <tbody>
                @forelse($studentComparison as $i => $s)
                    <tr>
                        <td align="center">{{ $i + 1 }}</td>
                        <td>{{ $s->name }}</td>
                        <td align="center">{{ $s->e1_pct !== null ? number_format($s->e1_pct, 2).'%' : '-' }}</td>
                        <td align="center">{{ $s->e2_pct !== null ? number_format($s->e2_pct, 2).'%' : '-' }}</td>
                        <td align="center">
                            @if($s->delta === null)
                                <span class="td-flat">-</span>
                            @elseif($s->delta > 0)
                                <span class="td-up">↑ +{{ number_format($s->delta, 2) }}%</span>
                            @elseif($s->delta < 0)
                                <span class="td-down">↓ {{ number_format($s->delta, 2) }}%</span>
                            @else
                                <span class="td-flat">= 0</span>
                            @endif
                        </td>
                        <td align="center">
                            @if(!$s->both_exams)
                                <span class="status-pill status-same">ONE EXAM</span>
                            @elseif($s->status === 'improved')
                                <span class="status-pill status-improved">IMPROVED</span>
                            @elseif($s->status === 'declined')
                                <span class="status-pill status-declined">DECLINED</span>
                            @else
                                <span class="status-pill status-same">STABLE</span>
                            @endif
                        </td>
                        <td align="center">
                            <a href="javascript:void(0)"
                               onclick="showDetail('{{ $s->id }}', '{{ addslashes($s->name) }}')"
                               class="student-link">View →</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" align="center">No students to compare.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="erp-card mt-4">
        <div class="two-col">
            <div>
                <h3 class="text-lg font-bold text-green-700 mb-3">Top 10 Improvers</h3>
                <table class="analytics-table">
                    <thead>
                        <tr><th>Rank</th><th>Student</th><th>Before</th><th>After</th><th>Change</th></tr>
                    </thead>
                    <tbody>
                        @forelse($improvers as $i => $s)
                            <tr>
                                <td align="center">{{ $i + 1 }}</td>
                                <td>{{ $s->name }}</td>
                                <td align="center">{{ number_format($s->e1_pct, 2) }}%</td>
                                <td align="center">{{ number_format($s->e2_pct, 2) }}%</td>
                                <td align="center" class="td-up">↑ +{{ number_format($s->delta, 2) }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" align="center">No improvers found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                <h3 class="text-lg font-bold text-red-600 mb-3">Top 10 Decliners</h3>
                <table class="analytics-table">
                    <thead>
                        <tr><th>Rank</th><th>Student</th><th>Before</th><th>After</th><th>Change</th></tr>
                    </thead>
                    <tbody>
                        @forelse($decliners as $i => $s)
                            <tr>
                                <td align="center">{{ $i + 1 }}</td>
                                <td>{{ $s->name }}</td>
                                <td align="center">{{ number_format($s->e1_pct, 2) }}%</td>
                                <td align="center">{{ number_format($s->e2_pct, 2) }}%</td>
                                <td align="center" class="td-down">↓ {{ number_format($s->delta, 2) }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" align="center">No decliners found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ===================== DETAIL MODAL ===================== --}}
    <div id="detailModal" class="detail-modal">
        <div class="detail-modal-inner">
            <div class="detail-modal-header">
                <h3 id="detailTitle" class="detail-modal-title"></h3>
                <button type="button" class="detail-modal-close" onclick="closeDetail()">&times;</button>
            </div>
            <div class="detail-modal-body">
                <table class="analytics-table">
                    <thead>
                        <tr>
                            <th rowspan="2" style="vertical-align:middle;">Subject</th>
                            <th colspan="3">{{ $kpi['exam1']['label'] }}</th>
                            <th colspan="3">{{ $kpi['exam2']['label'] }}</th>
                        </tr>
                        <tr>
                            <th>Max</th><th>Pass</th><th>Obt.</th>
                            <th>Max</th><th>Pass</th><th>Obt.</th>
                        </tr>
                    </thead>
                    <tbody id="detailBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ===================== CHART SCRIPTS ===================== --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script>
    Chart.register(ChartDataLabels);
    Chart.defaults.responsive = true;
    Chart.defaults.maintainAspectRatio = false;
    Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
    Chart.defaults.font.size = 11;
    Chart.defaults.plugins.legend.position = 'bottom';
    Chart.defaults.layout.padding = { top: 20, bottom: 10, left: 10, right: 20 };

    new Chart(document.getElementById('cmpSubjectChart'), {
        type: 'bar',
        data: {
            labels: @json($chartSubjectLabels),
            datasets: [
                { label: '{{ $kpi["exam1"]["label"] }} (%)', data: @json($chartSubjectPct1), backgroundColor: '#3b82f6' },
                { label: '{{ $kpi["exam2"]["label"] }} (%)', data: @json($chartSubjectPct2), backgroundColor: '#8b5cf6' }
            ]
        },
        options: {
            plugins: {
                datalabels: {
                    color: '#000', anchor: 'end', align: 'top',
                    font: { weight: 'bold', size: 10 },
                    formatter: v => v != null ? v + '%' : ''
                }
            },
            scales: {
                y: { beginAtZero: true, max: 100, title: { display: true, text: 'Average %' } },
                x: { ticks: { maxRotation: 45, minRotation: 0, autoSkip: false } }
            }
        }
    });

    new Chart(document.getElementById('cmpMovementChart'), {
        type: 'doughnut',
        data: {
            labels: ['Improved', 'Declined', 'Stable'],
            datasets: [{
                data: [{{ $improvedCount }}, {{ $declinedCount }}, {{ $sameCount }}],
                backgroundColor: ['#16a34a', '#dc2626', '#6b7280']
            }]
        },
        options: {
            cutout: '60%',
            plugins: {
                datalabels: { color: '#000', font: { weight: 'bold' }, formatter: v => v > 0 ? v : '' }
            }
        }
    });

    /* ============ DETAIL MODAL ============ */
    function showDetail(studentId, studentName) {
        document.getElementById('detailTitle').textContent = studentName + ' — Subject Detail';
        document.getElementById('detailBody').innerHTML = '<tr><td colspan="7" align="center">Loading…</td></tr>';
        document.getElementById('detailModal').style.display = 'flex';

        const url = '{{ route('analytics.comparison.student', ':sid') }}'.replace(':sid', studentId)
            + '?exam1_id={{ $exam1Id }}&exam2_id={{ $exam2Id }}';

        fetch(url)
            .then(r => r.json())
            .then(data => {
                if (!data.rows || !data.rows.length) {
                    document.getElementById('detailBody').innerHTML = '<tr><td colspan="7" align="center">No data</td></tr>';
                    return;
                }
                let html = '';
                data.rows.forEach(function (row) {
                    const obt1 = row.obt1 !== null ? row.obt1 : '-';
                    const obt2 = row.obt2 !== null ? row.obt2 : '-';
                    const pass1 = (row.obt1 !== null && row.pass1 !== null && row.obt1 >= row.pass1);
                    const pass2 = (row.obt2 !== null && row.pass2 !== null && row.obt2 >= row.pass2);
                    html += '<tr>';
                    html += '<td>' + row.subject + '</td>';
                    html += '<td align="center">' + (row.max1 ?? '-') + '</td>';
                    html += '<td align="center">' + (row.pass1 ?? '-') + '</td>';
                    html += '<td align="center" style="font-weight:700;color:' + (pass1 ? '#166534' : (row.obt1 === null ? '#6b7280' : '#991b1b')) + ';">' + obt1 + '</td>';
                    html += '<td align="center">' + (row.max2 ?? '-') + '</td>';
                    html += '<td align="center">' + (row.pass2 ?? '-') + '</td>';
                    html += '<td align="center" style="font-weight:700;color:' + (pass2 ? '#166534' : (row.obt2 === null ? '#6b7280' : '#991b1b')) + ';">' + obt2 + '</td>';
                    html += '</tr>';
                });
                document.getElementById('detailBody').innerHTML = html;
            })
            .catch(function () {
                document.getElementById('detailBody').innerHTML = '<tr><td colspan="7" align="center">Failed to load</td></tr>';
            });
    }

    function closeDetail() {
        document.getElementById('detailModal').style.display = 'none';
    }

    document.getElementById('detailModal').addEventListener('click', function (e) {
        if (e.target.id === 'detailModal') closeDetail();
    });
    </script>

@endif

</div>

@endsection