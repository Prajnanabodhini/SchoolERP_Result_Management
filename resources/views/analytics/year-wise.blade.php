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
    .analytics-filters select {
        height: 34px; padding: 4px 28px 4px 8px; font-size: 12px;
        border: 1px solid #9ca3af; border-radius: 4px; background: #fff;
        appearance: none; -webkit-appearance: none; -moz-appearance: none; cursor: pointer;
    }
    .analytics-filters select:focus {
        outline: none; border-color: #2563eb;
        box-shadow: 0 0 0 2px rgba(37,99,235,.15);
    }
    .filter-ay { width: 150px; }
    .filter-std { width: 130px; }
    .filter-div { width: 100px; }

    .analytics-placeholder {
        border: 2px dashed #bfdbfe; background: #eff6ff; color: #1e40af;
        padding: 32px 20px; border-radius: 8px; text-align: center; margin-top: 16px;
    }
    .analytics-placeholder h3 { margin: 0 0 8px; font-size: 18px; }
    .analytics-placeholder .steps {
        display: inline-block; text-align: left; margin: 12px auto 0;
        background: #fff; border: 1px solid #bfdbfe; border-radius: 6px;
        padding: 12px 18px; font-size: 13px; color: #1e3a8a;
    }

    .analytics-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    .analytics-table th {
        background: #dbeafe; border: 1px solid #d1d5db;
        padding: 6px; text-align: center;
    }
    .analytics-table td { border: 1px solid #d1d5db; padding: 6px; }

    .chart-grid {
        display: grid; grid-template-columns: 1fr 1fr; gap: 12px;
    }
    @media (max-width: 1000px) { .chart-grid { grid-template-columns: 1fr; } }
    .chart-box {
        min-height: 340px; border: 1px solid #d1d5db; border-radius: 6px;
        background: #fff; padding: 10px;
    }
    .chart-title {
        font-size: 13px; font-weight: 700; color: #1e40af; margin-bottom: 8px;
    }

    .exam-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 10px;
    }
    .exam-kpi-card {
        border: 1px solid #d1d5db; border-radius: 6px;
        background: #f8fafc; padding: 12px 14px;
    }
    .exam-kpi-name {
        font-size: 11px; font-weight: 700; color: #1e40af;
        text-transform: uppercase; letter-spacing: .3px;
        margin-bottom: 8px;
    }
    .exam-kpi-row {
        display: flex; justify-content: space-between;
        font-size: 12px; margin-bottom: 4px; color: #374151;
    }
    .exam-kpi-row .val { font-weight: 700; color: #111827; }
</style>

<div class="erp-page">

    <div class="erp-card">
        <h2 class="text-xl font-bold text-blue-700 mb-3">YEAR-WISE EXAM ANALYSIS</h2>

        <form method="GET" action="{{ route('analytics.year-wise') }}">
            <div class="analytics-filters">

                <div class="filter-group">
                    <label>Academic Year <span style="color:#dc2626;">*</span></label>
                    <div class="analytics-select-wrap">
                        <select name="academic_year_id" required>
                            <option value="">Select</option>
                            @foreach($academicYears as $y)
                                <option value="{{ $y->id }}" {{ request('academic_year_id') == $y->id ? 'selected' : '' }}>
                                    {{ $y->year_name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="analytics-select-arrow"></span>
                    </div>
                </div>

                <div class="filter-group">
                    <label>Standard <span style="color:#dc2626;">*</span></label>
                    <div class="analytics-select-wrap">
                        <select name="standard_id" required>
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
                        <select name="division_id" required>
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

                <button type="submit" class="erp-btn erp-btn-save" style="height:34px;">
                    Load Analysis
                </button>

                @if($hasFilters ?? false)
                    <a href="{{ route('analytics.year-wise') }}"
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
        <h3>Compare all exams for a year</h3>
        <p>See how students performed across every exam in a selected year.</p>
        <ul class="steps">
            <li>1. Choose <strong>Academic Year</strong></li>
            <li>2. Choose <strong>Standard</strong></li>
            <li>3. Choose <strong>Division</strong></li>
            <li>4. Click <strong>Load Analysis</strong></li>
        </ul>
    </div>

@elseif(!empty($emptyMsg))

    <div class="erp-card mt-4" style="background:#fffbeb;border:1px solid #fde68a;color:#92400e;padding:15px;font-weight:700;border-radius:5px;">
        {{ $emptyMsg }}
    </div>

@else

    <div class="erp-card mt-4">
        <h3 class="text-lg font-bold text-blue-700 mb-3">
            Exam-wise Summary — {{ $yearModel->year_name ?? '' }} · {{ $standardName ?? '' }} {{ $divisionName ?? '' }}
        </h3>

        <div class="exam-kpi-grid">
            @foreach($exams as $exam)
                @php $k = $examKPI[$exam->id]; @endphp
                <div class="exam-kpi-card">
                    <div class="exam-kpi-name">{{ $k['exam_name'] }}</div>
                    <div class="exam-kpi-row"><span>Students</span><span class="val">{{ $k['total'] }}</span></div>
                    <div class="exam-kpi-row"><span>Average %</span><span class="val" style="color:#1d4ed8;">{{ number_format($k['avg_pct'], 2) }}%</span></div>
                    <div class="exam-kpi-row"><span>Pass %</span><span class="val" style="color:#166534;">{{ number_format($k['pass_pct'], 1) }}%</span></div>
                    <div class="exam-kpi-row"><span>Highest</span><span class="val">{{ number_format($k['highest'], 1) }}%</span></div>
                    <div class="exam-kpi-row"><span>Lowest</span><span class="val">{{ number_format($k['lowest'], 1) }}%</span></div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="erp-card mt-4">
        <h3 class="text-lg font-bold text-blue-700 mb-3">Charts</h3>
        <div class="chart-grid">
            <div>
                <div class="chart-title">Exam-wise Average %</div>
                <div class="chart-box"><canvas id="ywAvgChart"></canvas></div>
            </div>
            <div>
                <div class="chart-title">Exam-wise Pass %</div>
                <div class="chart-box"><canvas id="ywPassChart"></canvas></div>
            </div>
        </div>

        <div style="margin-top:16px;">
            <div class="chart-title">Subject-wise Average % across Exams</div>
            <div class="chart-box" style="min-height:420px;"><canvas id="ywSubjectChart"></canvas></div>
        </div>
    </div>

    <div class="erp-card mt-4">
        <h3 class="text-lg font-bold text-blue-700 mb-3">Subject-wise Comparison (Average %)</h3>
        <div style="overflow-x:auto;">
            <table class="analytics-table">
                <thead>
                    <tr>
                        <th style="min-width:140px;">Subject</th>
                        @foreach($exams as $exam)
                            <th>{{ $exam->exam_name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($subjectNames as $subId => $subName)
                        <tr>
                            <td style="font-weight:600;">{{ $subName }}</td>
                            @foreach($exams as $exam)
                                @php $pct = $subjectExamMatrix[$subId][$exam->id] ?? null; @endphp
                                <td align="center">
                                    @if($pct === null)
                                        <span style="color:#9ca3af;">-</span>
                                    @else
                                        <strong style="color:{{ $pct >= 75 ? '#166534' : ($pct >= 50 ? '#b45309' : '#991b1b') }};">
                                            {{ number_format($pct, 1) }}%
                                        </strong>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="{{ 1 + count($exams) }}" align="center">No subjects found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="erp-card mt-4">
        <h3 class="text-lg font-bold text-blue-700 mb-3">Student-wise Comparison (Score %)</h3>
        <div style="overflow-x:auto;">
            <table class="analytics-table">
                <thead>
                    <tr>
                        <th style="min-width:220px;">Student</th>
                        @foreach($exams as $exam)
                            <th>{{ $exam->exam_name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($studentExamMatrix as $sid => $row)
                        <tr>
                            <td style="font-weight:600;">{{ $row['name'] }}</td>
                            @foreach($exams as $exam)
                                @php $pct = $row['exams'][$exam->id] ?? null; @endphp
                                <td align="center">
                                    @if($pct === null)
                                        <span style="color:#9ca3af;">-</span>
                                    @else
                                        <strong style="color:{{ $pct >= 75 ? '#166534' : ($pct >= 35 ? '#1d4ed8' : '#991b1b') }};">
                                            {{ number_format($pct, 1) }}%
                                        </strong>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="{{ 1 + count($exams) }}" align="center">No students found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

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

    const examLabels = @json($chartExamLabels);

    new Chart(document.getElementById('ywAvgChart'), {
        type: 'bar',
        data: {
            labels: examLabels,
            datasets: [{ label: 'Average %', data: @json($chartExamAvgPct), backgroundColor: '#3b82f6' }]
        },
        options: {
            plugins: {
                datalabels: {
                    color: '#000', anchor: 'end', align: 'top',
                    font: { weight: 'bold' },
                    formatter: v => v + '%'
                }
            },
            scales: { y: { beginAtZero: true, max: 100, title: { display: true, text: '%' } } }
        }
    });

    new Chart(document.getElementById('ywPassChart'), {
        type: 'bar',
        data: {
            labels: examLabels,
            datasets: [{ label: 'Pass %', data: @json($chartExamPassPct), backgroundColor: '#16a34a' }]
        },
        options: {
            plugins: {
                datalabels: {
                    color: '#000', anchor: 'end', align: 'top',
                    font: { weight: 'bold' },
                    formatter: v => v + '%'
                }
            },
            scales: { y: { beginAtZero: true, max: 100, title: { display: true, text: '%' } } }
        }
    });

    const subjectLabels = @json(array_values($subjectNames));
    const subjectMatrix = @json($subjectExamMatrix);
    const subjectIds = @json(array_keys($subjectNames));
    const examIds = @json($exams->pluck('id')->all());

    const palette = ['#3b82f6','#8b5cf6','#ec4899','#f97316','#eab308','#16a34a','#0ea5e9','#dc2626','#14b8a6','#a855f7'];

    const datasets = examIds.map((examId, idx) => ({
        label: examLabels[idx],
        data: subjectIds.map(sid => subjectMatrix[sid] ? (subjectMatrix[sid][examId] ?? 0) : 0),
        backgroundColor: palette[idx % palette.length],
    }));

    new Chart(document.getElementById('ywSubjectChart'), {
        type: 'bar',
        data: { labels: subjectLabels, datasets: datasets },
        options: {
            plugins: { datalabels: { display: false } },
            scales: {
                y: { beginAtZero: true, max: 100, title: { display: true, text: 'Average %' } },
                x: { ticks: { maxRotation: 45, minRotation: 0, autoSkip: false } }
            }
        }
    });
    </script>

@endif

</div>

@endsection