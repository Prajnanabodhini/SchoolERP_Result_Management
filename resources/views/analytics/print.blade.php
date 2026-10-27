<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Result Analysis Report — {{ request('standard_id') ? 'Standard' : 'All' }}</title>

    <style>
        @page { size: A4 portrait; margin: 8mm; }
        * { box-sizing: border-box; }
        html, body {
            margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif;
            font-size: 11px; color: #111827; background: #f0f0f0;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }

        .report-page {
            width: 194mm; min-height: auto; margin: 6mm auto; padding: 5mm;
            background: #ffffff; border: 1px solid #e5e7eb; border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .report-header { display: flex; align-items: center; justify-content: center; gap: 16px; padding-bottom: 5px; }
        .report-header .logo { width: 60px; height: 60px; object-fit: contain; }
        .report-header .school-block { line-height: 1.15; text-align: center; max-width: 80%; }
        .report-header .school-name { font-size: 18px; font-weight: 700; color: #1e3a8a; letter-spacing: 0.3px; }
        .report-header .school-sub { font-size: 11px; font-weight: 700; color: #374151; margin-top: 3px; line-height: 1.3; }

        .report-divider { border-bottom: 3px solid #1e3a8a; margin-bottom: 8px; }
        .report-title-block { text-align: center; margin-bottom: 8px; line-height: 1.2; }
        .report-title-block .report-title { font-size: 14px; font-weight: 700; color: #1d4ed8; text-transform: uppercase; letter-spacing: 0.6px; }
        .report-title-block .report-sub { font-size: 9.5px; color: #6b7280; margin-top: 1px; }

        .filter-summary {
            display: flex; flex-wrap: wrap; justify-content: center; gap: 5px 16px;
            background: #eff6ff; border: 1px solid #93c5fd; border-radius: 5px;
            padding: 6px 14px; margin-bottom: 9px; font-size: 10.5px; color: #1e40af; font-weight: 700;
        }
        .filter-summary .key { font-size: 8.5px; text-transform: uppercase; letter-spacing: 0.3px; color: #6b7280; margin-right: 3px; }

        .section-title { font-size: 11.5px; font-weight: 700; color: #1e40af; text-transform: uppercase; letter-spacing: 0.4px; margin: 9px 0 5px; border-left: 4px solid #1e40af; padding-left: 8px; }

        .kpi-row { display: grid; grid-template-columns: repeat(6, 1fr); gap: 8px; margin-bottom: 6px; }
        .kpi-card { border: 1px solid #d1d5db; border-radius: 4px; background: #f8fafc; padding: 6px 10px; }
        .kpi-card .kpi-label { font-size: 8.5px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.2px; }
        .kpi-card .kpi-value { font-size: 16px; font-weight: 700; color: #1d4ed8; line-height: 1.1; margin-top: 2px; }

        .charts-row { display: grid; grid-template-columns: 1.15fr 1fr 1fr; gap: 10px; margin-bottom: 8px; page-break-inside: avoid; }
        .chart-card { border: 1px solid #d1d5db; border-radius: 4px; background: #fff; padding: 5px; }
        .chart-card .chart-caption { font-size: 10.5px; font-weight: 700; color: #1e40af; text-align: center; margin-bottom: 3px; }
        .chart-card .chart-body { position: relative; height: 138px; width: 100%; }
        .chart-card canvas { display: block; width: 100% !important; height: 100% !important; }

        .report-table { width: 100%; border-collapse: collapse; font-size: 10.5px; margin-bottom: 6px; page-break-inside: avoid; }
        .report-table thead th { background: #1e3a8a; color: #fff; padding: 6px 8px; text-align: center; font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.2px; border: 1px solid #1e40af; white-space: nowrap; }
        .report-table tbody td { padding: 4px 8px; border: 1px solid #d1d5db; color: #111827; vertical-align: middle; }
        .report-table tbody tr:nth-child(even) td { background: #f9fafb; }
        .report-table td.center { text-align: center; }
        .report-table td.subject-name { font-weight: 600; }

        .pass-good { color: #166534; font-weight: 700; }
        .pass-mid  { color: #b45309; font-weight: 700; }
        .pass-low  { color: #991b1b; font-weight: 700; }

        .two-tables { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; page-break-inside: avoid; margin-bottom: 4px; }

        .report-footer { text-align: center; font-size: 9px; color: #6b7280; border-top: 1px solid #e5e7eb; padding-top: 4px; margin-top: 4px; }

        .signatures { display: flex; justify-content: space-around; margin-top: 14px; padding-top: 4px; page-break-inside: avoid; }
        .signatures .sig-box { text-align: center; width: 220px; font-size: 10.5px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.3px; }
        .signatures .sig-line { border-top: 1.5px solid #9ca3af; margin: 20px 16px 5px; }

        .toolbar { position: fixed; top: 14px; right: 14px; z-index: 9999; display: flex; gap: 8px; }
        .toolbar .btn { display: inline-flex; align-items: center; justify-content: center; height: 36px; padding: 0 18px; border-radius: 5px; font-size: 13px; font-weight: 700; text-decoration: none; cursor: pointer; border: 1px solid transparent; }
        .btn-print { background: #16A34A; color: #fff; border-color: #15803D; }
        .btn-print:hover { background: #15803D; }
        .btn-close { background: #6B7280; color: #fff; border-color: #4B5563; }
        .btn-close:hover { background: #4B5563; }

        @media print {
            html, body { background: #fff; margin: 0 !important; padding: 0 !important; height: 100% !important; }
            .no-print { display: none !important; }
            .report-page { width: 100% !important; height: 100% !important; margin: 0 !important; padding: 0 !important; border: 0 !important; border-radius: 0 !important; box-shadow: none !important; display: flex !important; flex-direction: column !important; }
            .signatures { margin-top: auto !important; padding-bottom: 10px !important; }
            .section-title, .kpi-card, .chart-card, .report-table, .two-tables, .signatures, .report-title-block { page-break-inside: avoid !important; }
            .report-table tr { page-break-inside: avoid !important; }
        }
    </style>
</head>

<body>

@php
    $schoolCode = session('school_code', 'shirgaon');
    $ayName   = optional($academicYears->firstWhere('id', request('academic_year_id')))->year_name    ?? 'All';
    $stdName  = optional($standards->firstWhere('id', request('standard_id')))->standard_name          ?? 'All';
    $divName  = optional($divisions->firstWhere('id', request('division_id')))->division_name          ?? 'All';
    $examName = request('exam_master_id') ? (\App\Models\ExamMaster::find(request('exam_master_id'))->exam_name ?? 'All Exams') : 'All Exams';
    $reportDate = now()->format('d M Y, h:i A');
@endphp

<div class="toolbar no-print">
    <button type="button" class="btn btn-close" onclick="window.close()">✕ Close</button>
    <button type="button" class="btn btn-print" onclick="window.print()">🖨 Print / Save as PDF</button>
</div>

<div class="report-page">

    {{-- SCHOOL HEADER --}}
    <div class="report-header">
        <img src="{{ asset('images/school-logo.png') }}" alt="School Logo" class="logo">
        <div class="school-block">
            @if($schoolCode === 'chikhali')
                <div class="school-name">PRAJNANABODHINI ENGLISH MEDIUM SCHOOL CHIKHALI</div>
                <div class="school-sub">S.No. 1517/1, Sonwane Vasti, Chikhali, Tal. Haveli, Dist. Pune, Maharashtra 411062</div>
                <div class="school-sub" style="margin-top: 2px;">Academic Year : {{ $ayName }}</div>
            @else
                <div class="school-name">PRAJNANABODHINI ENGLISH MEDIUM SCHOOL &amp; JR. COLLEGE</div>
                <div class="school-sub">SHIRGAON &nbsp;·&nbsp; Gat No. 227, Shirgaon-Gahunje Rd, At. Post. Shirgaon, Tal. Maval, Dist. Pune, Maharashtra - 410506</div>
                <div class="school-sub" style="margin-top: 2px;">Academic Year : {{ $ayName }}</div>
            @endif
        </div>
    </div>

    <div class="report-divider"></div>

    <div class="report-title-block">
        <div class="report-title">Result Analysis Report</div>
        <div class="report-sub">Generated on {{ $reportDate }}</div>
    </div>

    <div class="filter-summary">
        <span><span class="key">Academic Year:</span> {{ $ayName }}</span>
        <span><span class="key">Standard:</span> {{ $stdName }}</span>
        <span><span class="key">Division:</span> {{ $divName }}</span>
        <span><span class="key">Exam:</span> {{ $examName }}</span>
        <span><span class="key">Total Students:</span> {{ $totalStudents }}</span>
    </div>

    <div class="section-title">Overall Performance Summary</div>
    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-label">Total Students</div>
            <div class="kpi-value">{{ $totalStudents }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Average Marks</div>
            <div class="kpi-value">{{ number_format($averageMarks, 2) }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Highest Marks</div>
            <div class="kpi-value">{{ number_format($highestMarks, 2) }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Lowest Marks</div>
            <div class="kpi-value">{{ number_format($lowestMarks, 2) }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Pass %</div>
            <div class="kpi-value" style="color:#166534;">{{ number_format($passPercentage, 1) }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Fail %</div>
            <div class="kpi-value" style="color:#991b1b;">{{ number_format($failPercentage, 1) }}</div>
        </div>
    </div>

    <div class="section-title">Performance Charts</div>
    <div class="charts-row">
        <div class="chart-card">
            <div class="chart-caption">Subject-wise Average Marks</div>
            <div class="chart-body"><canvas id="subjectChart"></canvas></div>
        </div>
        <div class="chart-card">
            <div class="chart-caption">Grade Distribution</div>
            <div class="chart-body"><canvas id="gradeChart"></canvas></div>
        </div>
        <div class="chart-card">
            <div class="chart-caption">Pass vs Fail</div>
            <div class="chart-body"><canvas id="passFailChart"></canvas></div>
        </div>
    </div>

    <div class="section-title">Subject-wise Pass Analysis</div>
    <table class="report-table">
        <thead>
            <tr>
                <th style="width:40px;">Sr.</th>
                <th style="text-align:left;">Subject</th>
                <th style="width:85px;">Students</th>
                <th style="width:85px;">Passed</th>
                <th style="width:85px;">Failed</th>
                <th style="width:95px;">Pass %</th>
            </tr>
        </thead>
        <tbody>
            @forelse($subjectStats as $i => $row)
                @php
                    $failed = max(0, $row->total_count - $row->pass_count);
                    $cls    = $row->pass_percentage >= 75 ? 'pass-good' : ($row->pass_percentage >= 50 ? 'pass-mid' : 'pass-low');
                @endphp
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td class="subject-name">{{ $row->subject_name }}</td>
                    <td class="center">{{ $row->total_count }}</td>
                    <td class="center">{{ $row->pass_count }}</td>
                    <td class="center">{{ $failed }}</td>
                    <td class="center {{ $cls }}">{{ $row->pass_percentage }}%</td>
                </tr>
            @empty
                <tr><td colspan="6" class="center">No subjects found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="two-tables">
        <div>
            <div class="section-title" style="border-left-color:#16a34a; color:#166534;">Top 10 Toppers</div>
            <table class="report-table">
                <thead>
                    <tr>
                        <th style="width:36px;">#</th>
                        <th style="text-align:left;">Student Name</th>
                        <th style="width:75px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topStudents as $i => $student)
                        <tr>
                            <td class="center">{{ $i + 1 }}</td>
                            <td class="subject-name">{{ $student->student_name }}</td>
                            <td class="center">{{ round($student->total_marks, 0) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="center">No data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            <div class="section-title" style="border-left-color:#dc2626; color:#991b1b;">Needs Attention (Avg &lt; 40)</div>
            <table class="report-table">
                <thead>
                    <tr>
                        <th style="width:36px;">#</th>
                        <th style="text-align:left;">Student Name</th>
                        <th style="width:75px;">Average</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riskStudents as $i => $student)
                        <tr>
                            <td class="center">{{ $i + 1 }}</td>
                            <td class="subject-name">{{ $student->student_name }}</td>
                            <td class="center">{{ round($student->avg_marks, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="center">No weak students. 👍</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="signatures">
        <div class="sig-box"><div class="sig-line"></div>Class Teacher</div>
        <div class="sig-box"><div class="sig-line"></div>Principal</div>
    </div>

    <div class="report-footer">
        Result Analysis Report &nbsp;·&nbsp; {{ $ayName }} &nbsp;·&nbsp; {{ $stdName }} / {{ $divName }} &nbsp;·&nbsp; Generated on {{ $reportDate }}
    </div>

</div>

{{-- CHART.JS --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
Chart.register(ChartDataLabels);

Chart.defaults.responsive = true;
Chart.defaults.maintainAspectRatio = false;
Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
Chart.defaults.font.size = 9;
Chart.defaults.plugins.legend.position = 'bottom';
Chart.defaults.plugins.legend.labels.boxWidth = 10;
Chart.defaults.plugins.legend.labels.padding = 4;
Chart.defaults.layout.padding = { top: 10, bottom: 2, left: 4, right: 6 };

new Chart(document.getElementById('subjectChart'), {
    type: 'bar',
    data: {
        labels: @json($subjectLabels),
        datasets: [{
            label: 'Average Marks',
            data: @json($subjectMarks),
            backgroundColor: '#3b82f6',
            borderRadius: 3,
        }]
    },
    options: {
        plugins: {
            legend: { display: false },
            datalabels: {
                color: '#000', anchor: 'end', align: 'top',
                font: { weight: 'bold', size: 8 },
                formatter: v => v > 0 ? Math.round(v * 100) / 100 : ''
            }
        },
        scales: {
            y: { beginAtZero: true, ticks: { font: { size: 8 } }, title: { display: true, text: 'Marks', font: { size: 8 } } },
            x: { ticks: { font: { size: 8 }, maxRotation: 55, minRotation: 40, autoSkip: false } }
        }
    }
});

new Chart(document.getElementById('gradeChart'), {
    type: 'doughnut',
    data: {
        labels: ['A1','A2','B1','B2','C1','C2','D','E','Absent','Left'],
        datasets: [{
            data: [
                {{ $gradeCounts['A1'] ?? 0 }}, {{ $gradeCounts['A2'] ?? 0 }},
                {{ $gradeCounts['B1'] ?? 0 }}, {{ $gradeCounts['B2'] ?? 0 }},
                {{ $gradeCounts['C1'] ?? 0 }}, {{ $gradeCounts['C2'] ?? 0 }},
                {{ $gradeCounts['D']  ?? 0 }}, {{ $gradeCounts['E']  ?? 0 }},
                {{ $gradeCounts['Absent'] ?? 0 }}, {{ $gradeCounts['Left'] ?? 0 }}
            ],
            backgroundColor: ['#16a34a','#22c55e','#84cc16','#eab308','#f97316','#ef4444','#dc2626','#991b1b','#6b7280','#111827'],
            borderWidth: 1, borderColor: '#fff'
        }]
    },
    options: {
        cutout: '55%',
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 8 }, boxWidth: 8, padding: 4 } },
            datalabels: { color: '#fff', font: { weight: 'bold', size: 8 }, formatter: v => v > 0 ? v : '' }
        }
    }
});

new Chart(document.getElementById('passFailChart'), {
    type: 'bar',
    data: {
        labels: ['Pass', 'Fail'],
        datasets: [{
            label: 'Percentage',
            data: [{{ round($passPercentage, 2) }}, {{ round($failPercentage, 2) }}],
            backgroundColor: ['#16a34a', '#dc2626'],
            borderRadius: 4,
        }]
    },
    options: {
        plugins: {
            legend: { display: false },
            datalabels: {
                color: function(context) { return context.dataset.data[context.dataIndex] >= 75 ? '#ffffff' : '#000000'; },
                anchor: function(context) { return context.dataset.data[context.dataIndex] >= 75 ? 'center' : 'end'; },
                align: function(context) { return context.dataset.data[context.dataIndex] >= 75 ? 'center' : 'top'; },
                font: { weight: 'bold', size: 9 },
                formatter: v => v + '%'
            }
        },
        scales: {
            y: { beginAtZero: true, suggestedMax: 100, grace: '5%', ticks: { font: { size: 8 } }, title: { display: true, text: 'Percentage (%)', font: { size: 8 } } },
            x: { ticks: { font: { size: 10 } } }
        }
    }
});
</script>
</body>
</html>