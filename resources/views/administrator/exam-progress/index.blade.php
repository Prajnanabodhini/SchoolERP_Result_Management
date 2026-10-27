<x-app-layout>

{{-- SweetAlert2 CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
.exam-progress-page, .exam-progress-page * { box-sizing: border-box; font-family: Arial, Helvetica, sans-serif !important; font-size: 13px; }
.exam-progress-page { width: 100%; max-width: 100%; background: #f0f0f0; padding: 12px; overflow-x: hidden; }
.exam-progress-page h2 { margin: 0 0 10px !important; font-size: 20px !important; font-weight: 700 !important; color: #1D4ED8 !important; letter-spacing: 0.4px; }
.exam-progress-page .erp-card { width: 100%; max-width: 100%; background: #ffffff !important; border: 1px solid #e5e7eb !important; border-radius: 10px !important; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08) !important; padding: 12px 14px !important; margin-bottom: 12px; overflow: hidden; }
.tabs-bar { display: flex; gap: 6px; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 2px solid #e5e7eb; flex-wrap: wrap; }
.tab { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 8px 26px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; text-decoration: none; color: #4b5563; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 8px; cursor: pointer; transition: all .15s; line-height: 1; }
.tab:hover { background: #e5e7eb; color: #1e40af; border-color: #9ca3af; }
.tab.active { background: #1e3a8a; color: #ffffff; border-color: #1e3a8a; box-shadow: 0 2px 6px rgba(30, 58, 138, 0.25); }
.filter-row { display: flex; align-items: flex-end; gap: 8px; width: 100%; flex-wrap: wrap; }
.filter-group { display: flex; flex-direction: column; gap: 4px; min-width: 0; flex: 0 1 auto; }
.filter-label { font-size: 10.5px !important; font-weight: 700 !important; color: #374151; text-transform: uppercase; letter-spacing: 0.3px; white-space: nowrap; padding: 0; margin: 0; }
.select-wrapper { position: relative; display: block; width: 100%; }
.filter-select { height: 34px; width: 100%; padding: 4px 28px 4px 10px; font-size: 12.5px !important; font-weight: 600; border: 1px solid #9CA3AF; border-radius: 6px; background-color: #ffffff; color: #111827; cursor: pointer; appearance: none; -webkit-appearance: none; -moz-appearance: none; outline: none; transition: border-color .15s, box-shadow .15s; background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%234B5563' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'/%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: right 9px center; background-size: 12px; }
.filter-select:focus { border-color: #2563EB; box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15); }
.filter-group.w-academic-year { width: 140px; } .filter-group.w-standard { width: 150px; } .filter-group.w-division { width: 100px; } .filter-group.w-exam { width: 230px; }
.filter-group.filter-group-btn { flex: 0 0 auto; margin-left: 0; }
.erp-btn, .erp-btn-save { height: 34px !important; min-width: 84px; padding: 0 16px !important; font-size: 12.5px !important; font-weight: 700 !important; border-radius: 6px !important; background: #16A34A !important; color: #fff !important; border: 1px solid #15803D !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; text-decoration: none !important; white-space: nowrap; transition: background .15s, border-color .15s; line-height: 1; }
.erp-btn:hover, .erp-btn-save:hover { background: #15803D !important; border-color: #166534 !important; }
.erp-btn.erp-btn-reset { background: #6B7280 !important; border-color: #4B5563 !important; } .erp-btn.erp-btn-reset:hover { background: #4B5563 !important; border-color: #374151 !important; }
.summary-row { margin-top: 12px; display: flex; gap: 8px; flex-wrap: wrap; }
.summary-badge { display: inline-flex; align-items: center; padding: 6px 12px; border-radius: 6px; font-size: 12px !important; font-weight: 700; letter-spacing: 0.3px; text-transform: uppercase; border: 1px solid transparent; white-space: nowrap; }
.completed-badge { background: #DCFCE7; color: #166534; border-color: #86EFAC; } .pending-badge { background: #FEF3C7; color: #92400E; border-color: #FCD34D; } .total-badge { background: #DBEAFE; color: #1E40AF; border-color: #93C5FD; }
.result-table-wrapper { width: 100%; max-width: 100%; overflow: hidden; border: 1px solid #d1d5db; border-radius: 8px; background: #fff; }
.result-table { width: 100%; border-collapse: collapse; background: #FFFFFF; font-size: 12.5px !important; table-layout: fixed; }
.result-table thead tr { background: #1e3a8a; }
.result-table th { background: #1e3a8a; color: #FFFFFF !important; font-weight: 700 !important; font-size: 11.5px !important; text-transform: uppercase; letter-spacing: 0.4px; border: 1px solid #1e40af; padding: 8px 6px; white-space: nowrap; text-align: left; overflow: hidden; text-overflow: ellipsis; }
.result-table td { border: 1px solid #d1d5db; padding: 8px 6px; white-space: nowrap; font-size: 12.5px; color: #111827; vertical-align: middle; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; }
.result-table td.subject-name { white-space: normal !important; word-break: break-word; line-height: 1.4; }
.result-table tbody tr:nth-child(even) { background: #f9fafb; } .result-table tbody tr:hover { background: #fef9c3; }
.result-table th.left, .result-table td.left { text-align: left; } .result-table th.center, .result-table td.center { text-align: center; }
.subject-name { font-weight: 700; color: #111827; } .subject-code { color: #6B7280; font-size: 11px !important; font-weight: 600; margin-left: 4px; }
.status-badge { display: inline-block; padding: 4px 10px; border-radius: 5px; font-size: 11px !important; font-weight: 700; letter-spacing: 0.3px; text-transform: uppercase; border: 1px solid transparent; white-space: nowrap; }
.status-completed { background: #DCFCE7; color: #166534; border-color: #86EFAC; } .status-pending { background: #FEF3C7; color: #92400E; border-color: #FCD34D; } .status-other { background: #E5E7EB; color: #374151; border-color: #D1D5DB; }
.action-button { height: 28px !important; padding: 0 12px !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; font-size: 11.5px !important; font-weight: 700 !important; text-decoration: none !important; border-radius: 5px !important; background: #16A34A !important; color: #fff !important; border: 1px solid #15803D !important; white-space: nowrap; transition: background .15s, border-color .15s; min-width: 0; }
.action-button:hover { background: #15803D !important; border-color: #166534 !important; }
.no-record { border: 1px solid #d1d5db; padding: 20px 16px; text-align: center; color: #92400E; background: #fffbeb; font-weight: 600; font-size: 13px; }
.exam-progress-page .pagination, .exam-progress-page nav[role="navigation"] { margin-top: 12px; } .exam-progress-page nav svg { width: 15px; height: 15px; }
@media (max-width: 1100px) { .filter-group.w-academic-year { width: 130px; } .filter-group.w-standard { width: 140px; } .filter-group.w-division { width: 90px; } .filter-group.w-exam { width: 200px; } }
@media (max-width: 900px) { .filter-row { gap: 6px; } .filter-group.w-academic-year, .filter-group.w-standard, .filter-group.w-division, .filter-group.w-exam { flex: 1 1 140px; width: auto; } }
@media (max-width: 700px) { .exam-progress-page { padding: 8px; } .tabs-bar { flex-direction: column; } .tab { justify-content: center; } .filter-row { flex-direction: column; align-items: stretch; } .filter-group, .filter-group.w-academic-year, .filter-group.w-standard, .filter-group.w-division, .filter-group.w-exam, .filter-group.filter-group-btn { width: 100%; flex: 1 1 100%; margin-left: 0; } .erp-btn, .erp-btn-save { width: 100%; } .result-table th.col-standard, .result-table td.col-standard, .result-table th.col-division, .result-table td.col-division, .result-table th.col-teacher, .result-table td.col-teacher { display: none; } }
</style>

<div class="exam-progress-page">

    <h2>Exam Progress Dashboard</h2>

    @php
        $activeTab = $subjectType ?? 'academic';
        $cleanParams = [];
        if (!empty($academicYearId))  $cleanParams['academic_year_id'] = $academicYearId;
        if (!empty($standardId))      $cleanParams['standard_id']      = $standardId;
        if (!empty($divisionId))      $cleanParams['division_id']      = $divisionId;
        if (!empty($examId))          $cleanParams['exam_master_id']   = $examId;

        $academicTabUrl = route('exam-progress.index', array_merge($cleanParams, ['subject_type' => 'academic']));
        $skillTabUrl    = route('exam-progress.index', array_merge($cleanParams, ['subject_type' => 'skill']));
        $marksTabUrl    = route('exam-progress.index', array_merge($cleanParams, ['subject_type' => 'marks']));
    @endphp

    <div class="tabs-bar">
        <a href="{{ $academicTabUrl }}" class="tab {{ $activeTab === 'academic' ? 'active' : '' }}">Academic</a>
        <a href="{{ $skillTabUrl }}"    class="tab {{ $activeTab === 'skill'    ? 'active' : '' }}">Skill Grades</a>
        <a href="{{ $marksTabUrl }}"    class="tab {{ $activeTab === 'marks'    ? 'active' : '' }}">Skill Marks</a>
    </div>

    <div class="erp-card">
        <form method="GET" action="{{ route('exam-progress.index') }}" id="examProgressFilterForm">
            <input type="hidden" name="subject_type" value="{{ $subjectType ?? 'academic' }}">

            <div class="filter-row">
                <div class="filter-group w-academic-year">
                    <label for="academic_year_id" class="filter-label">Academic Year</label>
                    <div class="select-wrapper">
                        <select name="academic_year_id" id="academic_year_id" class="filter-select">
                            <option value="">All Years</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ (string)$academicYearId === (string)$year->id ? 'selected' : '' }}>{{ $year->year_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="filter-group w-standard">
                    <label for="standard_id" class="filter-label">Standard</label>
                    <div class="select-wrapper">
                        <select name="standard_id" id="standard_id" class="filter-select">
                            <option value="">All Standards</option>
                            @foreach($standards as $standard)
                                <option value="{{ $standard->id }}" {{ (string)$standardId === (string)$standard->id ? 'selected' : '' }}>{{ $standard->standard_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="filter-group w-division">
                    <label for="division_id" class="filter-label">Division</label>
                    <div class="select-wrapper">
                        <select name="division_id" id="division_id" class="filter-select">
                            <option value="">All</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}" {{ (string)$divisionId === (string)$division->id ? 'selected' : '' }}>{{ $division->division_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="filter-group w-exam">
                    <label for="exam_master_id" class="filter-label">Exam</label>
                    <div class="select-wrapper">
                        <select name="exam_master_id" id="exam_master_id" class="filter-select">
                            <option value="">All Exams</option>
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}" {{ (string)$examId === (string)$exam->id ? 'selected' : '' }}>{{ $exam->display_exam_name ?? $exam->exam_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="filter-group filter-group-btn">
                    <label class="filter-label" style="visibility:hidden;">.</label>
                    <button type="submit" class="erp-btn erp-btn-save">Search</button>
                </div>

                @if(($examId !== null && $examId !== '') || ($standardId !== null && $standardId !== '') || ($divisionId !== null && $divisionId !== '') || ($academicYearId !== null && $academicYearId !== ''))
                    <div class="filter-group filter-group-btn">
                        <label class="filter-label" style="visibility:hidden;">.</label>
                        <a href="{{ route('exam-progress.index', ['subject_type' => $subjectType ?? 'academic']) }}" class="erp-btn erp-btn-reset">Reset</a>
                    </div>
                @endif
            </div>
        </form>

        <div class="summary-row">
            <span class="summary-badge completed-badge">Completed : {{ $completed }}</span>
            <span class="summary-badge pending-badge">Pending : {{ $pending }}</span>
            <span class="summary-badge total-badge">Total : {{ $total }}</span>
        </div>
    </div>

    <div class="erp-card">
        <div class="result-table-wrapper">
            <table class="result-table">
                <colgroup>
                    @if($activeTab === 'marks')
                        <col style="width:5%;"> <col style="width:22%;"> <col style="width:33%;"> <col class="col-standard" style="width:12%;"> <col class="col-division" style="width:7%;"> <col style="width:10%;"> <col style="width:11%;">
                    @else
                        <col style="width:5%;"> <col style="width:18%;"> <col style="width:20%;"> <col class="col-standard" style="width:13%;"> <col class="col-division" style="width:7%;"> <col class="col-teacher" style="width:15%;"> <col style="width:11%;"> <col style="width:11%;">
                    @endif
                </colgroup>

                <thead>
                    <tr>
                        <th class="center">ID</th>
                        <th class="left">Exam</th>
                        <th class="left">Subject</th>
                        <th class="left col-standard">Standard</th>
                        <th class="center col-division">Div</th>
                        @if($activeTab !== 'marks')
                            <th class="left col-teacher">Teacher</th>
                        @endif
                        <th class="center">Status</th>
                        <th class="center">Action</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($statuses as $status)
                    @php
                        $currentStatus = strtoupper(trim((string)($status->status ?? '')));
                        $subjectName   = trim((string)($status->subject_name ?? ''));
                        $subjectCode   = trim((string)($status->subject_code ?? ''));
                        if ($subjectName === '') { $subjectName = '-'; }

                        $tsaId                = (int)($status->tsa_id ?? 0);
                        $statusExamId         = (int)($status->exam_master_id ?? 0);
                        $statusStandardId     = (int)($status->standard_id ?? 0);
                        $statusDivisionId     = (int)($status->division_id ?? 0);
                        $statusAcademicYearId = (int)($status->academic_year_id ?? 0);
                        $resolvedSubjectId    = (int)($status->resolved_subject_id ?? 0);

                        $examName     = $status->exam_name ?: '-';
                        $standardName = $status->standard_name ?: '-';
                        $divisionName = $status->division_name ?: '-';
                        $teacherName  = $status->teacher_name ?: '-';
                    @endphp

                    <tr>
                        <td class="center" style="font-weight:700;color:#1e3a8a;">{{ $tsaId > 0 ? $tsaId : '-' }}</td>
                        <td class="left" title="{{ $examName }}">{{ $examName }}</td>
                        <td class="left subject-name" title="{{ $subjectName }}">
                            {{ $subjectName }}
                            @if($subjectCode !== '' && $subjectCode !== '-')
                                <span class="subject-code">({{ $subjectCode }})</span>
                            @endif
                        </td>
                        <td class="left col-standard" title="{{ $standardName }}">{{ $standardName }}</td>
                        <td class="center col-division" title="{{ $divisionName }}">{{ $divisionName }}</td>

                        @if($activeTab !== 'marks')
                            <td class="left col-teacher" title="{{ $teacherName }}">{{ $teacherName }}</td>
                        @endif

                        <td class="center">
                            @if($currentStatus === 'COMPLETED')
                                <span class="status-badge status-completed">Completed</span>
                            @elseif($currentStatus === 'PENDING')
                                <span class="status-badge status-pending">Pending</span>
                            @else
                                <span class="status-badge status-other">{{ $currentStatus !== '' ? $currentStatus : '-' }}</span>
                            @endif
                        </td>

                        <td class="center">
                            @if($activeTab === 'skill')
                                @if($tsaId > 0 && $statusExamId > 0 && $statusStandardId > 0 && $statusDivisionId > 0 && $resolvedSubjectId > 0)
                                    <a href="{{ route('student-skill-observations.index', ['academic_year_id' => $statusAcademicYearId, 'exam_master_id' => $statusExamId, 'standard_id' => $statusStandardId, 'division_id' => $statusDivisionId, 'subject_id' => $resolvedSubjectId]) }}" class="action-button">
                                        {{ $currentStatus === 'COMPLETED' ? 'View Mark' : 'Enter Mark' }}
                                    </a>
                                @else
                                    <span style="color:#9CA3AF;">-</span>
                                @endif
                            @elseif($activeTab === 'marks')
                                @if($statusExamId > 0 && $statusStandardId > 0 && $statusDivisionId > 0 && $subjectCode !== '')
                                    <a href="{{ route('student-skill-marks.index', ['academic_year_id' => $statusAcademicYearId, 'exam_master_id' => $statusExamId, 'standard_id' => $statusStandardId, 'division_id' => $statusDivisionId, 'skill' => $subjectCode]) }}" class="action-button">
                                        {{ $currentStatus === 'COMPLETED' ? 'View Mark' : 'Enter Mark' }}
                                    </a>
                                @else
                                    <span style="color:#9CA3AF;">-</span>
                                @endif
                            @else
                                @if($currentStatus === 'COMPLETED' && $tsaId > 0 && $statusExamId > 0 && $statusStandardId > 0 && $statusDivisionId > 0 && $resolvedSubjectId > 0)
                                    <a href="{{ url('/marks-entry/view') . '?' . http_build_query(['exam_master_id' => $statusExamId, 'standard_id' => $statusStandardId, 'division_id' => $statusDivisionId, 'subject_id' => $resolvedSubjectId, 'teacher_subject_allocation_id' => $tsaId]) }}" class="action-button">View Mark</a>
                                @elseif($currentStatus === 'PENDING' && $tsaId > 0 && $statusExamId > 0)
                                    <a href="{{ url('/marks-entry') . '?' . http_build_query(['exam_master_id' => $statusExamId, 'teacher_subject_allocation_id' => $tsaId]) }}" class="action-button">Enter Mark</a>
                                @else
                                    <span style="color:#9CA3AF;">-</span>
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $activeTab === 'marks' ? '7' : '8' }}" class="no-record">
                            No records found for <strong>{{ $activeTab === 'skill' ? 'Skill Grades' : ($activeTab === 'marks' ? 'Skill Marks' : 'Academic') }}</strong>.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($statuses->hasPages())
            <div style="margin-top:12px;display:flex;justify-content:center;">
                {{ $statuses->onEachSide(5)->links() }}
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form               = document.getElementById('examProgressFilterForm');
    const academicYearSelect = document.getElementById('academic_year_id');
    const standardSelect     = document.getElementById('standard_id');
    const divisionSelect     = document.getElementById('division_id');
    const examSelect         = document.getElementById('exam_master_id');
    if (!form) return;
    function resetExamAndSubmit() { if (examSelect) examSelect.value = ''; form.submit(); }
    if (academicYearSelect) academicYearSelect.addEventListener('change', resetExamAndSubmit);
    if (standardSelect)     standardSelect.addEventListener('change', resetExamAndSubmit);
    if (divisionSelect)     divisionSelect.addEventListener('change', resetExamAndSubmit);
    if (examSelect) examSelect.addEventListener('change', function () { form.submit(); });
});
</script>
</x-app-layout>