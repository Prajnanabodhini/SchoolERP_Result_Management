@extends('layouts.app')

@section('content')

@php
    // Build the Result Analytics Dashboard URL with the user's current filters.
    // Falls back to request() query params if a blade variable isn't passed.
    $backParams = array_filter([
        'academic_year_id' => $yearId     ?? request('academic_year_id'),
        'standard_id'      => $standardId ?? request('standard_id'),
        'division_id'      => $divisionId ?? request('division_id'),
        'exam_master_id'   => $examId     ?? request('exam_master_id'),
    ], fn($v) => !is_null($v) && $v !== '');

    $backUrl = route('analytics.index') . ($backParams ? '?' . http_build_query($backParams) : '');
@endphp

<style>
    .sd-header {
        display: flex; justify-content: space-between; align-items: center;
        background: #1e40af; color: #fff; padding: 16px 20px; border-radius: 8px;
        margin-bottom: 12px;
    }
    .sd-header h2 { margin: 0; font-size: 20px; }
    .sd-header .sd-meta { font-size: 12px; opacity: .95; margin-top: 6px; line-height: 1.7; }
    .sd-header .sd-meta strong { color: #fde68a; font-weight: 700; }

    .sd-kpi {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
    }
    .sd-kpi .box {
        border: 1px solid #d1d5db; border-radius: 6px; background: #f8fafc; padding: 14px;
    }
    .sd-kpi .title { font-size: 12px; font-weight: bold; color: #374151; }
    .sd-kpi .value { font-size: 22px; font-weight: bold; color: #1d4ed8; margin-top: 6px; }

    /* ---------- Marks table ---------- */
    .marks-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    .marks-table th, .marks-table td {
        border: 1px solid #d1d5db;
        padding: 6px 8px;
        text-align: center;
        white-space: nowrap;
    }
    .marks-table thead th {
        background: #dbeafe;
        color: #1e3a8a;
        font-weight: 700;
        font-size: 11px;
    }
    .marks-table td.subject-cell {
        text-align: left;
        font-weight: 600;
    }
    .marks-table tbody tr:nth-child(even) { background: #f8fafc; }
    .marks-table tbody tr:hover { background: #eff6ff; }

    .marks-table th.th-theory    { background: #dbeafe; }
    .marks-table th.th-oral      { background: #dcfce7; }
    .marks-table th.th-practical { background: #fef3c7; }
    .marks-table th.th-total     { background: #e0e7ff; }

    .chart-box {
        min-height: 340px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background: #fff;
        padding: 12px;
    }
    .chart-title { font-size: 13px; font-weight: 700; color: #1e40af; margin-bottom: 8px; }
    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    @media (max-width: 1000px) { .two-col { grid-template-columns: 1fr; } }
</style>

<div class="erp-page">

    {{-- HEADER --}}
    <div class="sd-header">
        <div>
            <h2>{{ $studentName }}</h2>
            <div class="sd-meta">
                <strong>Student ID:</strong> {{ $studentId }}
                &nbsp;|&nbsp;
                <strong>Academic Year:</strong> {{ $yearName }}
                &nbsp;|&nbsp;
                <strong>Standard:</strong> {{ $standardName }}
                &nbsp;|&nbsp;
                <strong>Division:</strong> {{ $divisionName }}
                &nbsp;|&nbsp;
                <strong>Exam:</strong> {{ $examName }}
            </div>
        </div>
        {{-- Back → Result Analytics Dashboard with the SAME selected filters --}}
        <a href="{{ $backUrl }}"
           class="erp-btn"
           style="background:#fff;color:#1e40af;font-weight:700;">
           ← Back
        </a>
    </div>

    {{-- KPI --}}
    <div class="erp-card">
        <div class="sd-kpi">
            <div class="box">
                <div class="title">Total Obtained</div>
                <div class="value">{{ number_format($totalObtained,2) }} / {{ number_format($totalMax,2) }}</div>
            </div>
            <div class="box">
                <div class="title">Percentage</div>
                <div class="value">{{ $percentage }}%</div>
            </div>
            <div class="box">
                <div class="title">Grade</div>
                <div class="value">{{ $grade }}</div>
            </div>
            <div class="box">
                <div class="title">Class Rank</div>
                <div class="value">#{{ $rank }}</div>
            </div>
            <div class="box">
                <div class="title">Class Average %</div>
                <div class="value">{{ $classPercentAvg }}%</div>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="erp-card mt-4">
        <div class="two-col">
            <div>
                @if($examTrend->count() > 1)
                    <div class="chart-title">Exam-wise Percentage Trend</div>
                    <div class="chart-box"><canvas id="trendChart"></canvas></div>
                @else
                    <div class="chart-title">Subject-wise Percentage (this exam)</div>
                    <div class="chart-box"><canvas id="subjectPercentChart"></canvas></div>
                @endif
            </div>
            <div>
                <div class="chart-title">Subject-wise: Student vs Class Average</div>
                <div class="chart-box"><canvas id="compareChart"></canvas></div>
            </div>
        </div>
    </div>

    {{-- Subject-wise marks --}}
    <div class="erp-card mt-4">
        <h3 class="text-lg font-bold text-blue-700 mb-3">Subject-wise Marks</h3>

        <div style="overflow-x:auto;">
        <table class="marks-table">
            <thead>
                <tr>
                    <th rowspan="2" style="vertical-align:middle;">Sr.</th>
                    <th rowspan="2" style="vertical-align:middle;text-align:left;">Subject</th>
                    <th colspan="3" class="th-theory">Theory</th>
                    <th colspan="3" class="th-oral">Oral</th>
                    <th colspan="3" class="th-practical">Practical</th>
                    <th colspan="3" class="th-total">Total</th>
                    <th rowspan="2" style="vertical-align:middle;">%</th>
                    <th rowspan="2" style="vertical-align:middle;">Status</th>
                </tr>
                <tr>
                    <th class="th-theory">Max</th>
                    <th class="th-theory">Pass</th>
                    <th class="th-theory">Obtained</th>

                    <th class="th-oral">Max</th>
                    <th class="th-oral">Pass</th>
                    <th class="th-oral">Obtained</th>

                    <th class="th-practical">Max</th>
                    <th class="th-practical">Pass</th>
                    <th class="th-practical">Obtained</th>

                    <th class="th-total">Max</th>
                    <th class="th-total">Pass</th>
                    <th class="th-total">Obtained</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subjectMarks as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="subject-cell">{{ $row->subject_name }}</td>

                        {{-- Theory --}}
                        <td>{{ $row->theory_max     > 0 ? $row->theory_max     : '-' }}</td>
                        <td>{{ $row->theory_max     > 0 ? $row->theory_passing : '-' }}</td>
                        <td>{{ $row->theory_max     > 0 ? $row->theory_obtained: '-' }}</td>

                        {{-- Oral --}}
                        <td>{{ $row->oral_max       > 0 ? $row->oral_max       : '-' }}</td>
                        <td>{{ $row->oral_max       > 0 ? $row->oral_passing   : '-' }}</td>
                        <td>{{ $row->oral_max       > 0 ? $row->oral_obtained  : '-' }}</td>

                        {{-- Practical --}}
                        <td>{{ $row->practical_max  > 0 ? $row->practical_max  : '-' }}</td>
                        <td>{{ $row->practical_max  > 0 ? $row->practical_passing : '-' }}</td>
                        <td>{{ $row->practical_max  > 0 ? $row->practical_obtained: '-' }}</td>

                        {{-- Total --}}
                        <td>{{ $row->max }}</td>
                        <td><strong>{{ $row->passing }}</strong></td>
                        <td><strong>{{ $row->obtained }}</strong></td>

                        <td>{{ $row->percent }}%</td>

                        <td>
                            @if($row->is_absent)
                                <span style="color:#6b7280;font-weight:700;">ABSENT</span>
                            @elseif($row->obtained < $row->passing)
                                <span style="color:#991b1b;font-weight:700;">FAIL</span>
                            @elseif($row->percent < 50)
                                <span style="color:#b45309;font-weight:700;">WEAK</span>
                            @else
                                <span style="color:#166534;font-weight:700;">PASS</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="17">No marks recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
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

@if($examTrend->count() > 1)
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: @json($examTrend->pluck('exam_name')),
        datasets: [{
            label: 'Percentage (%)',
            data: @json($examTrend->pluck('percent')),
            borderColor: '#1d4ed8',
            backgroundColor: 'rgba(29,78,216,.15)',
            fill: true,
            tension: 0.3,
            pointRadius: 6,
            pointBackgroundColor: '#1d4ed8'
        }]
    },
    options: {
        plugins: {
            legend: { display: true, position: 'bottom' },
            datalabels: {
                color: '#000', align: 'top', anchor: 'end',
                font: { weight: 'bold' },
                formatter: v => v + '%'
            },
            title: {
                display: true,
                text: 'Exam-wise Percentage Trend',
                color: '#1e40af',
                font: { size: 12, weight: 'bold' }
            }
        },
        scales: {
            y: {
                beginAtZero: true, max: 100,
                title: { display: true, text: 'Percentage (%)', color: '#374151', font: { size: 12, weight: 'bold' } },
                ticks: { stepSize: 10, color: '#374151' }
            },
            x: {
                title: { display: true, text: 'Exam', color: '#374151', font: { size: 12, weight: 'bold' } },
                ticks: { color: '#374151', maxRotation: 40, minRotation: 0, autoSkip: false }
            }
        }
    }
});
@else
new Chart(document.getElementById('subjectPercentChart'), {
    type: 'bar',
    data: {
        labels: @json($subjectMarks->pluck('subject_name')),
        datasets: [{
            label: 'Percentage (%)',
            data: @json($subjectMarks->pluck('percent')),
            backgroundColor: @json($subjectMarks->map(fn($r) => $r->obtained < $r->passing ? '#dc2626' : '#16a34a')),
            borderRadius: 4
        }]
    },
    options: {
        indexAxis: 'y',
        plugins: {
            legend: { display: false },
            datalabels: {
                color: '#000', anchor: 'end', align: 'right',
                font: { weight: 'bold' },
                formatter: v => v + '%'
            },
            title: {
                display: true,
                text: 'Subject-wise Percentage (this exam)',
                color: '#1e40af',
                font: { size: 12, weight: 'bold' }
            }
        },
        scales: {
            x: {
                beginAtZero: true, max: 100,
                title: { display: true, text: 'Percentage (%)', color: '#374151', font: { size: 12, weight: 'bold' } }
            },
            y: {
                title: { display: true, text: 'Subject', color: '#374151', font: { size: 12, weight: 'bold' } },
                ticks: { color: '#374151', autoSkip: false, font: { size: 11 } }
            }
        }
    }
});
@endif

new Chart(document.getElementById('compareChart'), {
    type: 'bar',
    data: {
        labels: @json($subjectComparison->pluck('subject')),
        datasets: [
            { label: 'Student Marks', data: @json($subjectComparison->pluck('student')),   backgroundColor: '#1d4ed8' },
            { label: 'Class Average', data: @json($subjectComparison->pluck('class_avg')), backgroundColor: '#94a3b8' }
        ]
    },
    options: {
        plugins: {
            legend: { display: true, position: 'bottom' },
            datalabels: {
                color: '#000', anchor: 'end', align: 'top',
                font: { weight: 'bold' },
                formatter: v => Number(v).toFixed(1)
            },
            title: {
                display: true,
                text: 'Subject-wise: Student vs Class Average',
                color: '#1e40af',
                font: { size: 12, weight: 'bold' }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: { display: true, text: 'Marks', color: '#374151', font: { size: 12, weight: 'bold' } },
                ticks: { color: '#374151' }
            },
            x: {
                title: { display: true, text: 'Subject', color: '#374151', font: { size: 12, weight: 'bold' } },
                ticks: { color: '#374151', autoSkip: false, maxRotation: 60, minRotation: 45, font: { size: 10 } }
            }
        }
    }
});
</script>

@endsection