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

    // Query string used for the student-detail links (keeps filters intact).
    $studentLinkQuery = http_build_query(array_filter([
        'academic_year_id' => $yearId     ?? request('academic_year_id'),
        'standard_id'      => $standardId ?? request('standard_id'),
        'division_id'      => $divisionId ?? request('division_id'),
        'exam_master_id'   => $examId     ?? request('exam_master_id'),
    ], fn($v) => !is_null($v) && $v !== ''));
@endphp

<style>
    .subj-header {
        display: flex; justify-content: space-between; align-items: center;
        background: #1e40af; color: #fff; padding: 16px 20px; border-radius: 8px;
        margin-bottom: 12px;
    }
    .subj-header h2 { margin: 0; font-size: 20px; }
    .subj-header .subj-meta {
        font-size: 12px; opacity: .95; margin-top: 6px; line-height: 1.7;
    }
    .subj-header .subj-meta strong { color: #fde68a; font-weight: 700; }

    .subj-kpi {
        display: grid; grid-template-columns: repeat(5, minmax(0,1fr)); gap: 10px;
    }
    @media (max-width: 900px) {
        .subj-kpi { grid-template-columns: repeat(2, minmax(0,1fr)); }
    }
    .subj-kpi .box {
        border: 1px solid #d1d5db; border-radius: 6px;
        background: #f8fafc; padding: 12px 14px;
    }
    .subj-kpi .title {
        font-size: 11px; font-weight: 700; color: #374151;
        text-transform: uppercase; letter-spacing: .3px;
    }
    .subj-kpi .value {
        font-size: 22px; font-weight: bold; color: #1d4ed8; margin-top: 6px;
    }

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

    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    @media (max-width: 1000px) { .two-col { grid-template-columns: 1fr; } }
</style>

<div class="erp-page">

    {{-- HEADER --}}
    <div class="subj-header">
        <div>
            <h2>{{ $subjectName }}</h2>
            <div class="subj-meta">
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
        <div class="subj-kpi">
            <div class="box">
                <div class="title">Students</div>
                <div class="value">{{ $totalCount }}</div>
            </div>
            <div class="box">
                <div class="title">Passed</div>
                <div class="value" style="color:#166534;">{{ $passed }}</div>
            </div>
            <div class="box">
                <div class="title">Failed</div>
                <div class="value" style="color:#991b1b;">{{ $failed }}</div>
            </div>
            <div class="box">
                <div class="title">Pass %</div>
                <div class="value">{{ $passPct }}%</div>
            </div>
            <div class="box">
                <div class="title">Class Avg</div>
                <div class="value">{{ number_format($classAvg, 2) }}</div>
            </div>
        </div>
    </div>

    {{-- Toppers + Weak --}}
    <div class="erp-card mt-4">
        <div class="two-col">

            <div>
                <h3 class="text-lg font-bold text-blue-700 mb-3">
                    Top 10 — {{ $subjectName }}
                </h3>
                <table class="analytics-table">
                    <thead>
                        <tr><th>Rank</th><th>Student</th><th>Obtained</th><th>%</th></tr>
                    </thead>
                    <tbody>
                        @forelse($toppers as $i => $row)
                            <tr>
                                <td align="center">{{ $i + 1 }}</td>
                                <td>
                                    <a class="student-link"
                                       href="{{ route('analytics.student', $row->student_id) }}{{ $studentLinkQuery ? '?' . $studentLinkQuery : '' }}">
                                        {{ $row->student_name }}
                                    </a>
                                </td>
                                <td align="center">{{ $row->obtained }} / {{ $row->max_marks }}</td>
                                <td align="center"><strong>{{ $row->percent }}%</strong></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" align="center">No Data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                <h3 class="text-lg font-bold text-red-600 mb-3">
                    Weak Students (Below 40%) — {{ $subjectName }}
                </h3>
                <table class="analytics-table">
                    <thead>
                        <tr><th>Sr.</th><th>Student</th><th>Obtained</th><th>%</th></tr>
                    </thead>
                    <tbody>
                        @forelse($weak as $i => $row)
                            <tr>
                                <td align="center">{{ $i + 1 }}</td>
                                <td>
                                    <a class="student-link"
                                       href="{{ route('analytics.student', $row->student_id) }}{{ $studentLinkQuery ? '?' . $studentLinkQuery : '' }}">
                                        {{ $row->student_name }}
                                    </a>
                                </td>
                                <td align="center">{{ $row->obtained }} / {{ $row->max_marks }}</td>
                                <td align="center">
                                    <strong style="color:#991b1b;">{{ $row->percent }}%</strong>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" align="center">No weak students. 👍</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    {{-- All students --}}
    <div class="erp-card mt-4">
        <h3 class="text-lg font-bold text-blue-700 mb-3">
            All Students — {{ $subjectName }}
        </h3>
        <table class="analytics-table">
            <thead>
                <tr><th>Sr.</th><th>Student</th><th>Obtained</th><th>Max</th><th>%</th><th>Status</th></tr>
            </thead>
            <tbody>
                @forelse($rows->sortByDesc('obtained')->values() as $i => $row)
                    <tr>
                        <td align="center">{{ $i + 1 }}</td>
                        <td>
                            <a class="student-link"
                               href="{{ route('analytics.student', $row->student_id) }}{{ $studentLinkQuery ? '?' . $studentLinkQuery : '' }}">
                                {{ $row->student_name }}
                            </a>
                        </td>
                        <td align="center">{{ $row->obtained }}</td>
                        <td align="center">{{ $row->max_marks }}</td>
                        <td align="center">{{ $row->percent }}%</td>
                        <td align="center">
                            @if($row->percent < 35)
                                <span style="color:#991b1b;font-weight:700;">FAIL</span>
                            @elseif($row->percent < 50)
                                <span style="color:#b45309;font-weight:700;">WEAK</span>
                            @else
                                <span style="color:#166534;font-weight:700;">PASS</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" align="center">No marks recorded for this subject.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection