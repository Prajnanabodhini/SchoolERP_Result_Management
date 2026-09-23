@extends('layouts.app')

@section('content')

<style>
    /* ---------- Filter bar ---------- */
    .analytics-filters {
        display: flex; flex-wrap: nowrap; gap: 10px;
        align-items: flex-end; overflow-x: auto; padding-bottom: 4px;
    }
    .analytics-filters .filter-group {
        display: flex; flex-direction: column; gap: 3px; flex-shrink: 0;
    }
    .analytics-filters label {
        font-size: 11px; font-weight: 700; color: #374151; white-space: nowrap;
    }
    .analytics-filters select {
        height: 34px; padding: 4px 8px; font-size: 12px;
        border: 1px solid #9ca3af; border-radius: 4px; background: #fff;
    }
    .analytics-filters select:focus {
        outline: none; border-color: #2563eb;
        box-shadow: 0 0 0 2px rgba(37,99,235,.15);
    }
    .analytics-filters select:disabled {
        background: #f3f4f6; color: #9ca3af; cursor: not-allowed;
    }
    .filter-ay   { width: 150px; }
    .filter-exam { width: 220px; }
    .filter-std  { width: 130px; }
    .filter-div  { width: 100px; }

    /* ---------- Placeholder ---------- */
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

    /* ---------- KPI cards — single row of 6 ---------- */
    .analytics-cards {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 10px;
    }
    @media (max-width: 1200px) {
        .analytics-cards { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (max-width: 700px) {
        .analytics-cards { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    .analytics-box {
        border: 1px solid #d1d5db; border-radius: 6px;
        background: #f8fafc; padding: 12px 14px;
    }
    .analytics-title {
        font-size: 11px; font-weight: 700; color: #374151;
        text-transform: uppercase; letter-spacing: .3px;
    }
    .analytics-value {
        font-size: 22px; font-weight: bold; color: #1d4ed8; margin-top: 6px;
        line-height: 1.1;
    }

    /* ---------- Tables ---------- */
    .analytics-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    .analytics-table th {
        background: #dbeafe; border: 1px solid #d1d5db;
        padding: 6px; text-align: center;
    }
    .analytics-table td { border: 1px solid #d1d5db; padding: 6px; }
    .analytics-table a.student-link {
        color: #1d4ed8; font-weight: 600; text-decoration: none;
    }
    .analytics-table a.student-link:hover { text-decoration: underline; }

    /* ---------- Subject chips ---------- */
    .subject-link-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 10px;
        margin-top: 6px;
    }
    .subject-link-card {
        display: flex; flex-direction: column; justify-content: center;
        padding: 12px 14px;
        border: 1px solid #bfdbfe;
        border-radius: 6px;
        background: #eff6ff;
        text-decoration: none;
        transition: transform .1s, box-shadow .1s, background .1s;
    }
    .subject-link-card:hover {
        background: #dbeafe;
        box-shadow: 0 2px 8px rgba(37,99,235,.15);
        transform: translateY(-1px);
    }
    .subject-link-card .sub-name {
        font-size: 13px; font-weight: 700; color: #1e40af;
    }
    .subject-link-card .sub-meta {
        font-size: 11px; color: #374151; margin-top: 4px;
    }
    .subject-link-card .sub-pass {
        font-size: 11px; font-weight: 700; margin-top: 4px;
    }

    /* ---------- Charts ---------- */
    .chart-grid {
        display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;
    }
    .chart-box {
        min-height: 320px; border: 1px solid #d1d5db; border-radius: 6px;
        background: #fff; padding: 10px;
    }
    .chart-title {
        font-size: 13px; font-weight: 700; color: #1e40af; margin-bottom: 8px;
    }
    @media (max-width: 1200px) { .chart-grid { grid-template-columns: 1fr; } }

    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    @media (max-width: 1000px) { .two-col { grid-template-columns: 1fr; } }
</style>

<div class="erp-page">

    {{-- FILTERS --}}
    <div class="erp-card">
        <h2 class="text-xl font-bold text-blue-700 mb-3">RESULT ANALYTICS DASHBOARD</h2>

        <form method="GET" action="{{ route('analytics.index') }}" id="analyticsFilterForm">
            <div class="analytics-filters">

                <div class="filter-group">
                    <label>Academic Year <span style="color:#dc2626;">*</span></label>
                    <select name="academic_year_id" id="ay_sel" class="filter-ay" required>
                        <option value="">Select Year</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}"
                                {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                {{ $year->year_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label>Standard <span style="color:#dc2626;">*</span></label>
                    <select name="standard_id" id="std_sel" class="filter-std" required>
                        <option value="">Select</option>
                        @foreach($standards as $standard)
                            <option value="{{ $standard->id }}"
                                {{ request('standard_id') == $standard->id ? 'selected' : '' }}>
                                {{ $standard->standard_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label>Division <span style="color:#dc2626;">*</span></label>
                    <select name="division_id" id="div_sel" class="filter-div" required>
                        <option value="">Select</option>
                        @foreach($divisions as $division)
                            <option value="{{ $division->id }}"
                                {{ request('division_id') == $division->id ? 'selected' : '' }}>
                                {{ $division->division_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label>Exam <span style="color:#6b7280;">(optional)</span></label>
                    <select name="exam_master_id" id="exam_sel" class="filter-exam">
                        <option value="">Select Standard first</option>
                    </select>
                </div>

                <button type="submit" class="erp-btn erp-btn-save" style="height:34px;">
                    Load Analytics
                </button>

                @if($hasFilters)
                    <a href="{{ route('analytics.index') }}"
                       class="erp-btn"
                       style="height:34px;background:#e5e7eb;color:#374151;text-decoration:none;">
                        Reset
                    </a>
                @endif

            </div>
        </form>
    </div>

    {{-- Filter script --}}
    <script>
    (function () {
        const form    = document.getElementById('analyticsFilterForm');
        if (!form) return;

        const aySel   = document.getElementById('ay_sel');
        const stdSel  = document.getElementById('std_sel');
        const divSel  = document.getElementById('div_sel');
        const examSel = document.getElementById('exam_sel');

        const preSelectedExam = "{{ request('exam_master_id') }}";
        const examsUrl        = "{{ route('report-card.exams-by-standard') }}";
        const csrfToken       = document.querySelector('input[name="_token"]')?.value
                              || document.querySelector('meta[name="csrf-token"]')?.content
                              || '';

        let requestSeq = 0;

        async function loadExams(standardId, academicYearId, selectValue) {
            if (!standardId) {
                examSel.innerHTML = '<option value="">Select Standard first</option>';
                examSel.disabled  = true;
                return;
            }
            const seq = ++requestSeq;
            examSel.disabled  = true;
            examSel.innerHTML = '<option value="">Loading…</option>';

            try {
                const res = await fetch(examsUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type':    'application/json',
                        'Accept':          'application/json',
                        'X-CSRF-TOKEN':    csrfToken,
                        'X-Requested-With':'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        standard_id:      standardId,
                        academic_year_id: academicYearId || null,
                    }),
                });

                if (seq !== requestSeq) return;
                if (!res.ok) throw new Error('HTTP ' + res.status);

                const exams = await res.json();

                let html = '<option value="">All Exams</option>';
                exams.forEach(function (e) {
                    const isSel = String(selectValue) === String(e.id) ? ' selected' : '';
                    const name  = String(e.exam_name).replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    html += '<option value="' + e.id + '"' + isSel + '>' + name + '</option>';
                });
                examSel.innerHTML = html;
                examSel.disabled  = false;
            } catch (err) {
                if (seq !== requestSeq) return;
                console.error('Exam load failed:', err);
                examSel.innerHTML = '<option value="">Error loading exams</option>';
                examSel.disabled  = true;
            }
        }

        stdSel.addEventListener('change', function () {
            loadExams(stdSel.value, aySel.value, '');
        });

        aySel.addEventListener('change', function () {
            if (stdSel.value) loadExams(stdSel.value, aySel.value, examSel.value);
        });

        form.addEventListener('submit', function (e) {
            if (!aySel.value || !stdSel.value || !divSel.value) {
                e.preventDefault();
                alert('Please select Academic Year, Standard and Division.');
            }
        });

        if (stdSel.value) {
            loadExams(stdSel.value, aySel.value, preSelectedExam);
        } else {
            examSel.disabled = true;
        }
    })();
    </script>

@if(!$hasFilters)

    <div class="analytics-placeholder">
        <h3>Select filters to load analytics</h3>
        <p>Whole-database averages aren't useful here — pick a scope first.</p>
        <ul class="steps">
            <li>1. Choose an <strong>Academic Year</strong></li>
            <li>2. Choose a <strong>Standard</strong></li>
            <li>3. Choose a <strong>Division</strong></li>
            <li>4. (Optionally) choose an <strong>Exam</strong>, then click <strong>Load Analytics</strong></li>
        </ul>
    </div>

@else

    {{-- KPI CARDS — single row of 6 --}}
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
                <div class="analytics-value" style="color:#166534;">{{ number_format($passPercentage,1) }}</div>
            </div>
            <div class="analytics-box">
                <div class="analytics-title">Fail %</div>
                <div class="analytics-value" style="color:#991b1b;">{{ number_format($failPercentage,1) }}</div>
            </div>
        </div>
    </div>

    {{-- CHARTS --}}
    <div class="erp-card mt-4">
        <h3 class="text-lg font-bold text-blue-700 mb-3">Management Dashboard Charts</h3>
        <div class="chart-grid">
            <div>
                <div class="chart-title">Subject Wise Average Marks</div>
                <div class="chart-box"><canvas id="subjectChart"></canvas></div>
            </div>
            <div>
                <div class="chart-title">Grade Distribution</div>
                <div class="chart-box"><canvas id="gradeChart"></canvas></div>
            </div>
            <div>
                <div class="chart-title">Pass vs Fail</div>
                <div class="chart-box"><canvas id="passFailChart"></canvas></div>
            </div>
        </div>
    </div>

    {{-- SUBJECT-wise pass % + STANDARD comparison --}}
    <div class="erp-card mt-4">
        <div class="two-col">
            <div>
                <h3 class="text-lg font-bold text-blue-700 mb-3">Subject-wise Pass Analysis</h3>
                <table class="analytics-table">
                    <thead>
                        <tr>
                            <th>Sr.</th><th>Subject</th><th>Students</th>
                            <th>Passed</th><th>Pass %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subjectStats as $i => $row)
                            <tr>
                                <td align="center">{{ $i + 1 }}</td>
                                <td>{{ $row->subject_name }}</td>
                                <td align="center">{{ $row->total_count }}</td>
                                <td align="center">{{ $row->pass_count }}</td>
                                <td align="center">
                                    <strong style="color: {{ $row->pass_percentage >= 75 ? '#166534' : ($row->pass_percentage >= 50 ? '#b45309' : '#991b1b') }};">
                                        {{ $row->pass_percentage }}%
                                    </strong>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" align="center">No Data Found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                <h3 class="text-lg font-bold text-blue-700 mb-3">Standard-wise Comparison</h3>
                <table class="analytics-table">
                    <thead><tr><th>Standard</th><th>Students</th><th>Avg Marks</th></tr></thead>
                    <tbody>
                        @forelse($standardComparison as $row)
                            <tr>
                                <td>{{ $row->standard_name }}</td>
                                <td align="center">{{ $row->student_count }}</td>
                                <td align="center">{{ round($row->avg_marks, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" align="center">No Data Found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- SUBJECT LINKS --}}
    <div class="erp-card mt-4">
        <h3 class="text-lg font-bold text-blue-700 mb-3">Explore Subject-wise Detail</h3>
        <p style="font-size:12px;color:#6b7280;margin:0 0 10px;">
            Click any subject to see that subject's toppers and weak students for the current filter.
        </p>

        <div class="subject-link-grid">
            @forelse($subjectStats as $row)
                <a class="subject-link-card"
                   href="{{ route('analytics.subject', $row->subject_id) }}?academic_year_id={{ request('academic_year_id') }}&standard_id={{ request('standard_id') }}&division_id={{ request('division_id') }}&exam_master_id={{ request('exam_master_id') }}">
                    <div class="sub-name">{{ $row->subject_name }}</div>
                    <div class="sub-meta">
                        {{ $row->total_count }} student(s) &middot; {{ $row->pass_count }} passed
                    </div>
                    <div class="sub-pass"
                         style="color: {{ $row->pass_percentage >= 75 ? '#166534' : ($row->pass_percentage >= 50 ? '#b45309' : '#991b1b') }};">
                        Pass {{ $row->pass_percentage }}%
                    </div>
                </a>
            @empty
                <div class="alert-info">No subjects found in the current filter.</div>
            @endforelse
        </div>
    </div>

    {{-- TOP 10 TOPPERS --}}
    <div class="erp-card mt-4">
        <h3 class="text-lg font-bold text-blue-700 mb-3">Top 10 Toppers</h3>
        <table class="analytics-table">
            <thead><tr><th>Rank</th><th>Student Name</th><th>Total Marks</th><th>Action</th></tr></thead>
            <tbody>
                @forelse($topStudents as $i => $student)
                    <tr>
                        <td align="center">{{ $i + 1 }}</td>
                        <td>
                            <a class="student-link"
                               href="{{ route('analytics.student', $student->student_id) }}?academic_year_id={{ request('academic_year_id') }}&exam_master_id={{ request('exam_master_id') }}">
                                {{ $student->student_name }}
                            </a>
                        </td>
                        <td align="center">{{ round($student->total_marks, 2) }}</td>
                        <td align="center">
                            <a class="student-link"
                               href="{{ route('analytics.student', $student->student_id) }}?academic_year_id={{ request('academic_year_id') }}&exam_master_id={{ request('exam_master_id') }}">
                                View →
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" align="center">No Data Found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- TOP 10 WEAK STUDENTS --}}
    <div class="erp-card mt-4">
        <h3 class="text-lg font-bold text-red-600 mb-3">Top 10 Weak Students (Average Below 40)</h3>
        <table class="analytics-table">
            <thead><tr><th>Rank</th><th>Student Name</th><th>Average Marks</th><th>Action</th></tr></thead>
            <tbody>
                @forelse($riskStudents as $i => $student)
                    <tr>
                        <td align="center">{{ $i + 1 }}</td>
                        <td>
                            <a class="student-link"
                               href="{{ route('analytics.student', $student->student_id) }}?academic_year_id={{ request('academic_year_id') }}&exam_master_id={{ request('exam_master_id') }}">
                                {{ $student->student_name }}
                            </a>
                        </td>
                        <td align="center">{{ round($student->avg_marks, 2) }}</td>
                        <td align="center">
                            <a class="student-link"
                               href="{{ route('analytics.student', $student->student_id) }}?academic_year_id={{ request('academic_year_id') }}&exam_master_id={{ request('exam_master_id') }}">
                                View →
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" align="center">No weak students in the current filter. 👍</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Chart scripts --}}
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

    new Chart(document.getElementById('subjectChart'), {
        type: 'bar',
        data: {
            labels: @json($subjectLabels),
            datasets: [{ label: 'Average Marks', data: @json($subjectMarks), backgroundColor: '#3b82f6' }]
        },
        options: {
            plugins: {
                datalabels: { color: '#000', anchor: 'end', align: 'top', font: { weight: 'bold' } }
            },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Marks' } },
                x: { title: { display: true, text: 'Subject' }, ticks: { maxRotation: 45, minRotation: 0, autoSkip: false } }
            }
        }
    });

    new Chart(document.getElementById('passFailChart'), {
        type: 'bar',
        data: {
            labels: ['Pass', 'Fail'],
            datasets: [{
                label: 'Percentage',
                data: [{{ round($passPercentage,2) }}, {{ round($failPercentage,2) }}],
                backgroundColor: ['#16a34a', '#dc2626']
            }]
        },
        options: {
            plugins: {
                datalabels: { color: '#000', anchor: 'end', align: 'top', font: { weight: 'bold' }, formatter: v => v + '%' }
            },
            scales: {
                y: { beginAtZero: true, max: 100, title: { display: true, text: 'Percentage (%)' } }
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
                backgroundColor: ['#16a34a','#22c55e','#84cc16','#eab308','#f97316','#ef4444','#dc2626','#991b1b','#6b7280','#111827']
            }]
        },
        options: {
            cutout: '60%',
            plugins: {
                datalabels: { color: '#000', font: { weight: 'bold' }, formatter: v => v > 0 ? v : '' }
            }
        }
    });
    </script>

@endif

</div>

@endsection