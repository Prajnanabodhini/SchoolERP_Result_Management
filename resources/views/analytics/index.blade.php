@extends('layouts.app')

@section('content')

<style>

.analytics-cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:12px;
}

.analytics-box{
    border:1px solid #d1d5db;
    border-radius:6px;
    background:#f8fafc;
    padding:15px;
}

.analytics-title{
    font-size:12px;
    font-weight:bold;
    color:#374151;
}

.analytics-value{
    font-size:24px;
    font-weight:bold;
    color:#1d4ed8;
    margin-top:8px;
}

.analytics-table{
    width:100%;
    border-collapse:collapse;
    font-size:12px;
}

.analytics-table th{
    background:#dbeafe;
    border:1px solid #d1d5db;
    padding:6px;
    text-align:center;
}

.analytics-table td{
    border:1px solid #d1d5db;
    padding:6px;
}

.chart-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:12px;
}

.chart-box{
    
    min-height:320px;
    height:auto;
    border:1px solid #d1d5db;
    border-radius:6px;
    background:#fff;
    padding:10px;
}

@media(max-width:1200px){
    .chart-grid{
        grid-template-columns:1fr;
    }
}

</style>

<div class="erp-page">

    {{-- FILTERS --}}

    <div class="erp-card">

        <h2 class="text-xl font-bold text-blue-700 mb-3">
            RESULT ANALYTICS DASHBOARD
        </h2>

        <form method="GET" action="{{ route('analytics.index') }}">

            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">

                <label>Academic Year</label>

                <select name="academic_year_id">
                    <option value="">Select Year</option>

                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}"
                            {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                            {{ $year->year_name }}
                        </option>
                    @endforeach
                </select>

                <label>Exam</label>

                <select name="exam_master_id">
                    <option value="">All Exams</option>

                    @foreach($exams as $exam)
                        <option value="{{ $exam->id }}"
                            {{ $examId == $exam->id ? 'selected' : '' }}>
                            {{ $exam->exam_name }}
                        </option>
                    @endforeach
                </select>

                <label>Standard</label>

                <select name="standard_id">
                    <option value="">All</option>

                    @foreach($standards as $standard)
                        <option value="{{ $standard->id }}"
                            {{ $standardId == $standard->id ? 'selected' : '' }}>
                            {{ $standard->standard_name }}
                        </option>
                    @endforeach
                </select>

                <label>Division</label>

                <select name="division_id">
                    <option value="">All</option>

                    @foreach($divisions as $division)
                        <option value="{{ $division->id }}"
                            {{ $divisionId == $division->id ? 'selected' : '' }}>
                            {{ $division->division_name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="erp-btn erp-btn-save">
                    Load Analytics
                </button>

            </div>

        </form>

    </div>

    {{-- CHARTS --}}

  <div class="erp-card mt-4">

    <h3 class="text-lg font-bold text-blue-700 mb-3">
        Management Dashboard Charts
    </h3>
<div class="chart-card">
    <div class="chart-title">
        Subject Wise Average Marks
    </div>

    <div class="chart-box medium">
        <canvas id="subjectChart"></canvas>
    </div>
</div>
<div class="chart-card">
    <div class="chart-title">
        Grade Distribution
    </div>

    <div class="chart-box small">
        <canvas id="gradeChart"></canvas>
    </div>
</div>
<div class="chart-card">
    <div class="chart-title">
        Pass vs Fail Analysis
    </div>

    <div class="chart-box small">
        <canvas id="passFailChart"></canvas>
    </div>
</div>

</div>
    {{-- KPI CARDS --}}

    <div class="erp-card mt-4">

        <div class="analytics-cards">

            <div class="analytics-box">
                <div class="analytics-title">Total Students</div>
                <div class="analytics-value">{{ $totalStudents }}</div>
            </div>

            <div class="analytics-box">
                <div class="analytics-title">Average Marks</div>
                <div class="analytics-value">{{ number_format($averageMarks,2) }}</div>
            </div>

            <div class="analytics-box">
                <div class="analytics-title">Highest Marks</div>
                <div class="analytics-value">{{ number_format($highestMarks,2) }}</div>
            </div>

            <div class="analytics-box">
                <div class="analytics-title">Lowest Marks</div>
                <div class="analytics-value">{{ number_format($lowestMarks,2) }}</div>
            </div>

            <div class="analytics-box">
                <div class="analytics-title">Pass %</div>
                <div class="analytics-value">{{ number_format($passPercentage,1) }}</div>
            </div>

            <div class="analytics-box">
                <div class="analytics-title">Fail %</div>
                <div class="analytics-value">{{ number_format($failPercentage,1) }}</div>
            </div>

        </div>

    </div>

    {{-- SUBJECT ANALYSIS --}}

    <div class="erp-card mt-4">

        <h3 class="text-lg font-bold text-blue-700 mb-3">
            Subject Wise Performance Analysis
        </h3>

        <table class="analytics-table">

            <thead>
                <tr>
                    <th>Sr.</th>
                    <th>Subject</th>
                    <th>Average Marks</th>
                </tr>
            </thead>

            <tbody>

                @forelse($subjectAnalysis as $index => $row)

                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $row->display_subject }}</td>
                        <td>{{ round($row->avg_marks,2) }}</td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="3">No Data Found</td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    {{-- TOP STUDENTS --}}

    <div class="erp-card mt-4">

        <h3 class="text-lg font-bold text-blue-700 mb-3">
            Top 10 Students
        </h3>

        <table class="analytics-table">

            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Student Name</th>
                    <th>Total Marks</th>
                </tr>
            </thead>

            <tbody>

                @forelse($topStudents as $index => $student)

                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $student->student_name }}</td>
                        <td>{{ round($student->total_marks,2) }}</td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="3">No Data Found</td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    {{-- RISK STUDENTS --}}

    <div class="erp-card mt-4">

        <h3 class="text-lg font-bold text-red-600 mb-3">
            Risk Students (Average Below 40)
        </h3>

        <table class="analytics-table">

            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Average Marks</th>
                </tr>
            </thead>

            <tbody>

                @forelse($riskStudents as $student)

                    <tr>
                        <td>{{ $student->student_name }}</td>
                        <td>{{ round($student->avg_marks,2) }}</td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="2">No Risk Students Found</td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>

Chart.register(ChartDataLabels);

Chart.defaults.plugins.datalabels = {
    color: '#000',
    font: {
        weight: 'bold',
        size: 11
    }
};
Chart.defaults.plugins.legend.position = 'bottom';

Chart.defaults.plugins.datalabels = {
    font:{
        size:11,
        weight:'bold'
    }
};

Chart.defaults.elements.bar.borderRadius = 4;
Chart.defaults.responsive = true;
    Chart.defaults.maintainAspectRatio = false;

Chart.defaults.font.family =
    "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";

Chart.defaults.font.size = 11;

Chart.defaults.plugins.legend.position = 'bottom';

Chart.defaults.plugins.legend.labels = {
    boxWidth:12,
    padding:15,
    font:{
        size:11
    }
};

Chart.defaults.layout.padding = {
    top:10,
    bottom:10,
    left:10,
    right:10
};

</script>
<script>
Chart.register(ChartDataLabels);

Chart.defaults.responsive = true;
Chart.defaults.maintainAspectRatio = false;

Chart.defaults.plugins.legend.position = 'bottom';

Chart.defaults.font.family =
    "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";

Chart.defaults.font.size = 11;

/* SUBJECT WISE */

new Chart(
    document.getElementById('subjectChart'),
    {
        type:'bar',
        data:{
            labels:@json($subjectLabels),
            datasets:[{
                label:'Average Marks',
                data:@json($subjectMarks)
            }]
        },
        options:{
            responsive:true,
            maintainAspectRatio:false,
            plugins:{
                datalabels:{
                    color:'#000000',
    anchor:'center',
    align:'center',
    font:{
        weight:'bold'
    }
}
            }
        }
    }
);

/* PASS FAIL */

new Chart(
    document.getElementById('passFailChart'),
    {
        type:'bar',
        data:{
            labels:['Pass','Fail'],
            datasets:[{
                label:'Percentage',
                data:[
                    {{ round($passPercentage,2) }},
                    {{ round($failPercentage,2) }}
                ]
            }]
        },
        options:{
            responsive:true,
            maintainAspectRatio:false,
            plugins:{
                datalabels:{
                    color:'#000000',
    anchor:'center',
    align:'center',
    font:{
        weight:'bold'
    }
}
            }
        }
    }
);

/* GRADE DISTRIBUTION */

new Chart(
    document.getElementById('gradeChart'),
    {
        type:'doughnut',
        data:{
            labels:[
                'A1','A2','B1','B2',
                'C1','C2','D','E',
                'Absent','Left'
            ],
            datasets:[{
                data:[
                    {{ $gradeCounts['A1'] ?? 0 }},
                    {{ $gradeCounts['A2'] ?? 0 }},
                    {{ $gradeCounts['B1'] ?? 0 }},
                    {{ $gradeCounts['B2'] ?? 0 }},
                    {{ $gradeCounts['C1'] ?? 0 }},
                    {{ $gradeCounts['C2'] ?? 0 }},
                    {{ $gradeCounts['D'] ?? 0 }},
                    {{ $gradeCounts['E'] ?? 0 }},
                    {{ $gradeCounts['Absent'] ?? 0 }},
                    {{ $gradeCounts['Left'] ?? 0 }}
                ]
            }]
        },
        options:{
            responsive:true,
            maintainAspectRatio:false,
            cutout:'60%',
            plugins:{
                legend:{
                    position:'bottom'
                },
                datalabels:{
                    color:'#000000',
                    font:{
                        weight:'bold',
                        size:11
                    },
                    formatter:(value)=> value > 0 ? value : ''
                }
            }
        }
    }
);
</script>

@endsection