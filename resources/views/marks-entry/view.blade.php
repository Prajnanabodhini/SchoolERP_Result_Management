<x-app-layout>

@php
    $H = \App\Helpers\MarksEntryBladeHelper::class;

    /* Safe defaults — blade works even if controller doesn't pass these */
    $teacherSubjectAllocation = $teacherSubjectAllocation ?? null;
    $selectedTsa              = $selectedTsa              ?? $teacherSubjectAllocation;
    $selectedSubject          = $selectedSubject          ?? null;
    $exam                     = $exam                     ?? null;
    $records                  = $records                  ?? collect();
    $academicYears            = $academicYears            ?? collect();
    $exams                    = $exams                    ?? collect();
    $assignments              = $assignments              ?? collect();
    $error                    = $error                    ?? '';
    $showTheory               = $showTheory               ?? false;
    $showOral                 = $showOral                 ?? false;
    $showPractical            = $showPractical            ?? false;

    /* -------- component flags (fallback) -------- */
    if (!$showTheory && !$showOral && !$showPractical) {
        $flags = $H::resolveComponentFlags(null, null, null, $records);
        $showTheory    = $flags['theory'];
        $showOral      = $flags['oral'];
        $showPractical = $flags['practical'];
    }

    /* -------- tab URLs -------- */
    $filterParams = array_filter([
        'exam_master_id'                => request('exam_master_id'),
        'teacher_subject_allocation_id' => request('teacher_subject_allocation_id'),
    ], fn ($v) => $v !== null && $v !== '');

    $marksEntryUrl = route('marks-entry.index') . $H::querySuffix($filterParams);
    $viewMarksUrl  = request()->fullUrl();
@endphp

<style>
    .marks-view-page,
    .marks-view-page * {
        box-sizing: border-box;
        font-family: Arial, sans-serif !important;
    }

    .marks-view-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,.06);
        padding: 12px;
    }

    .marks-view-title {
        margin: 0 0 10px;
        font-size: 18px;
        font-weight: 700;
        color: #2563eb;
    }

    /* ================= FILTER (same as entry page) ================= */
    .filter-row { display: flex; align-items: flex-end; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
    .filter-group { display: flex; flex-direction: column; gap: 3px; }
    .filter-label { font-size: 11px; font-weight: 600; color: #374151; }
    .filter-select-wrapper { position: relative; display: inline-block; }

    .filter-select {
        height: 30px; padding: 3px 24px 3px 8px;
        border: 1px solid #d1d5db; border-radius: 4px;
        background: #fff; color: #111827; font-size: 11px;
        outline: none; appearance: none; -webkit-appearance: none; -moz-appearance: none;
    }
    .filter-select:focus { border-color: #2563eb; box-shadow: 0 0 0 1px #2563eb; }
    .filter-select:disabled { background: #f3f4f6; color: #9ca3af; cursor: not-allowed; }

    .dropdown-arrow {
        position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
        border-left: 4px solid transparent; border-right: 4px solid transparent;
        border-top: 5px solid #6b7280; pointer-events: none;
    }

    .academic-year-select { width: 150px; }
    .exam-select          { width: 220px; }
    .assignment-select    { width: 280px; }

    .erp-btn {
        min-height: 30px; min-width: max-content; padding: 5px 12px;
        border: 0; border-radius: 4px; font-size: 11px; font-weight: 600;
        cursor: pointer; text-decoration: none;
        display: inline-flex; align-items: center; justify-content: center;
        white-space: nowrap; line-height: 1.2;
    }
    .erp-btn-save       { background: #2563eb; color: #fff; min-width: 150px; }
    .erp-btn-save:hover { background: #1d4ed8; }

    /* ================= END FILTER ================= */

    .selected-info {
        display: flex; align-items: center; flex-wrap: wrap; gap: 6px;
        margin-top: 10px; padding: 8px 10px; border-radius: 4px;
        background: #eff6ff; border: 1px solid #bfdbfe;
        color: #1e3a8a; font-size: 11px;
    }
    .selected-info-item      { font-weight: 700; }
    .selected-info-separator { color: #93c5fd; }

    .marks-table-wrapper { margin-top: 12px; overflow-x: auto; border: 1px solid #d1d5db; border-radius: 4px; }
    .marks-table { width: 100%; border-collapse: collapse; background: #fff; font-size: 11px; }
    .marks-table th {
        background: #dbeafe; color: #1e3a8a; border: 1px solid #cbd5e1;
        padding: 5px 4px; text-align: center; line-height: 1.15;
        font-weight: 700; font-size: 10px;
    }
    .marks-table td { border: 1px solid #d1d5db; padding: 3px 4px; white-space: nowrap; vertical-align: middle; font-size: 11px; line-height: 1.2; }
    .marks-table tbody tr:hover { background: #f8fafc; }

    .center            { text-align: center; }
    .student-name-cell {
        min-width: 150px; max-width: 220px;
        white-space: normal !important;
        font-weight: 600; color: #111827; line-height: 1.25;
    }
    .student-name-cell strong { font-weight: 600; }

    .error-box   { margin-top: 10px; padding: 8px 10px; border-radius: 4px; background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; font-size: 11px; font-weight: 600; }
    .warning-box { margin-top: 10px; padding: 8px 10px; border-radius: 4px; background: #fffbeb; border: 1px solid #fcd34d; color: #92400e; font-size: 11px; font-weight: 600; }

    .view-only-box {
        margin-top: 10px; padding: 8px 10px; border-radius: 4px;
        background: #fef3c7; border: 1px solid #fcd34d;
        color: #92400e; font-size: 11px; font-weight: 600; text-align: center;
    }

    {!! $H::tabStyles() !!}
    {!! $H::viewStyles() !!}

    @media (max-width: 900px) {
        .filter-group { width: 100%; }
        .academic-year-select, .exam-select, .assignment-select { width: 100%; }
    }
</style>

<div class="erp-page marks-view-page">
    <div class="marks-view-card">

        <h2 class="marks-view-title">Examination Marks</h2>

        {{-- ================= TABS ================= --}}
        {!! $H::renderTabs('view', $marksEntryUrl, $viewMarksUrl) !!}


        {{-- ================= FILTER ================= --}}
        <form method="GET" action="{{ route('marks-entry.view') }}" id="marksFilterForm">
            <div class="filter-row">

                {{-- Academic Year --}}
                <div class="filter-group">
                    <label class="filter-label" for="academic_year_id">Academic Year</label>
                    <div class="filter-select-wrapper">
                        <select name="academic_year_id" id="academic_year_id" class="filter-select academic-year-select">
                            <option value="">All Academic Years</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ (string) request('academic_year_id') === (string) $year->id ? 'selected' : '' }}>
                                    {{ $year->year_name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="dropdown-arrow"></span>
                    </div>
                </div>

                {{-- Exam --}}
                <div class="filter-group">
                    <label class="filter-label" for="exam_master_id">Exam</label>
                    <div class="filter-select-wrapper">
                        <select name="exam_master_id" id="exam_master_id" class="filter-select exam-select">
                            <option value="">Select Exam</option>
                            @foreach($exams as $ex)
                                <option value="{{ $ex->id }}" {{ (string) request('exam_master_id') === (string) $ex->id ? 'selected' : '' }}>
                                    {{ $ex->exam_name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="dropdown-arrow"></span>
                    </div>
                </div>

                {{-- Teaching Assignment --}}
                <div class="filter-group">
                    <label class="filter-label" for="teacher_subject_allocation_id">Teaching Assignment</label>
                    <div class="filter-select-wrapper">
                        <select name="teacher_subject_allocation_id" id="teacher_subject_allocation_id"
                                class="filter-select assignment-select"
                                {{ !request('exam_master_id') ? 'disabled' : '' }}>
                            @if(!request('exam_master_id'))
                                <option value="">Select Exam First</option>
                            @elseif($assignments->isEmpty())
                                <option value="">No Teaching Assignment</option>
                            @else
                                <option value="">Select Teaching Assignment</option>
                                @foreach($assignments as $tsa)
                                    @php
                                        $tName = optional(optional($tsa->allocation)->teacher)->name ?? 'Teacher';
                                        $sName = optional($tsa->subject)->subject_name ?? 'Subject';
                                        $std   = optional(optional($tsa->allocation)->standard)->standard_name ?? '';
                                        $div   = optional(optional($tsa->allocation)->division)->division_name ?? '';
                                        $class = $std . ($div !== '' ? ' - ' . $div : '');
                                    @endphp
                                    <option value="{{ $tsa->id }}" {{ (string) request('teacher_subject_allocation_id') === (string) $tsa->id ? 'selected' : '' }}>
                                        {{ $tName }} — {{ $sName }} — {{ $class }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <span class="dropdown-arrow"></span>
                    </div>
                </div>

                {{-- Load Students --}}
                <div class="filter-group">
                    <button type="submit" class="erp-btn erp-btn-save">
                        Load Students
                    </button>
                </div>

            </div>
        </form>


        {{-- ================= ERRORS ================= --}}
        @if(!empty($error))
            <div class="error-box">{{ $error }}</div>
        @endif

        @if(session('error'))
            <div class="error-box">{{ session('error') }}</div>
        @endif


        {{-- ================= SELECTED INFO ================= --}}
        @if($teacherSubjectAllocation && $exam)
            @php
                $teacherName = optional(optional($teacherSubjectAllocation->allocation)->teacher)->name ?? 'Teacher';
                $subjectName = optional($teacherSubjectAllocation->subject)->subject_name ?? 'Subject';
                $stdName     = optional(optional($teacherSubjectAllocation->allocation)->standard)->standard_name ?? '';
                $divName     = optional(optional($teacherSubjectAllocation->allocation)->division)->division_name ?? '';
            @endphp

            <div class="selected-info">
                <span><span class="selected-info-item">Teacher:</span> {{ $teacherName }}</span>
                <span class="selected-info-separator">|</span>
                <span><span class="selected-info-item">Subject:</span> {{ $subjectName }}</span>
                <span class="selected-info-separator">|</span>
                <span>
                    <span class="selected-info-item">Class:</span>
                    {{ $stdName }}@if($divName !== '') - {{ $divName }}@endif
                </span>
                <span class="selected-info-separator">|</span>
                <span><span class="selected-info-item">Exam:</span> {{ $exam->display_exam_name ?? $exam->exam_name }}</span>
                <span class="selected-info-separator">|</span>
                <span><span class="selected-info-item">Students:</span> {{ $records->count() }}</span>
            </div>

            <div class="view-only-box">
                View Only — Marks cannot be modified from this page.
            </div>
        @endif


        {{-- ================= MARKS TABLE ================= --}}
        @if($records->count() > 0)
            <div class="marks-table-wrapper">
                <table class="marks-table">
                    <thead>
                        <tr>
                            <th>GR No</th>
                            <th>Roll No</th>
                            <th>Student Name</th>

                            @if($showTheory)
                                <th>Theory Max</th>
                                <th>Theory Pass</th>
                                <th>Theory Obtained</th>
                            @endif

                            @if($showOral)
                                <th>Oral Max</th>
                                <th>Oral Pass</th>
                                <th>Oral Obtained</th>
                            @endif

                            @if($showPractical)
                                <th>Practical Max</th>
                                <th>Practical Pass</th>
                                <th>Practical Obtained</th>
                            @endif

                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                    @foreach($records as $row)
                        @php
                            $fullName = $H::getFullStudentName($row);
                            $grNo     = $H::getGrNo($row);
                            $rollNo   = $H::getRollNo($row);

                            $isOptional = isset($row->is_optional) && (int) $row->is_optional === 1;
                            $isAbsent   = !$isOptional && $H::isAbsent($row);

                            $theoryVal    = $row->theory_obtained_marks    ?? null;
                            $oralVal      = $row->oral_obtained_marks      ?? null;
                            $practicalVal = $row->practical_obtained_marks ?? null;

                            $statusText = $isOptional ? 'OPT'
                                        : ($isAbsent ? 'ABSENT'
                                        : strtoupper(trim((string) ($row->status ?? 'PRESENT'))));

                            if ($statusText === '') { $statusText = 'PRESENT'; }

                            $statusClass = $H::statusClass($statusText);
                        @endphp

                        <tr>
                            <td class="center">{{ $grNo }}</td>
                            <td class="center">{{ $rollNo }}</td>

                            <td class="student-name-cell" title="{{ $fullName }}">
                                <strong>{{ $fullName }}</strong>
                            </td>

                            @if($showTheory)
                                <td class="center">{{ isset($row->theory_max_marks)     ? (int) $row->theory_max_marks     : '-' }}</td>
                                <td class="center">{{ isset($row->theory_passing_marks) ? (int) $row->theory_passing_marks : '-' }}</td>
                                <td class="center">
                                    @if($isOptional)
                                        <span class="readonly-mark optional">OPT</span>
                                    @elseif($isAbsent)
                                        <span class="readonly-mark absent">AB</span>
                                    @else
                                        <span class="readonly-mark">{{ $H::displayMark($theoryVal) }}</span>
                                    @endif
                                </td>
                            @endif

                            @if($showOral)
                                <td class="center">{{ isset($row->oral_max_marks)     ? (int) $row->oral_max_marks     : '-' }}</td>
                                <td class="center">{{ isset($row->oral_passing_marks) ? (int) $row->oral_passing_marks : '-' }}</td>
                                <td class="center">
                                    @if($isOptional)
                                        <span class="readonly-mark optional">OPT</span>
                                    @elseif($isAbsent)
                                        <span class="readonly-mark absent">AB</span>
                                    @else
                                        <span class="readonly-mark">{{ $H::displayMark($oralVal) }}</span>
                                    @endif
                                </td>
                            @endif

                            @if($showPractical)
                                <td class="center">{{ isset($row->practical_max_marks)     ? (int) $row->practical_max_marks     : '-' }}</td>
                                <td class="center">{{ isset($row->practical_passing_marks) ? (int) $row->practical_passing_marks : '-' }}</td>
                                <td class="center">
                                    @if($isOptional)
                                        <span class="readonly-mark optional">OPT</span>
                                    @elseif($isAbsent)
                                        <span class="readonly-mark absent">AB</span>
                                    @else
                                        <span class="readonly-mark">{{ $H::displayMark($practicalVal) }}</span>
                                    @endif
                                </td>
                            @endif

                            <td class="center">
                                <span class="{{ $statusClass }}">
                                    {{ $statusText }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top:12px;display:flex;justify-content:flex-end;">
                <span class="student-count">{{ $records->count() }} Students</span>
            </div>

        @elseif(request()->filled('teacher_subject_allocation_id'))
            <div class="warning-box">
                No marks have been entered for the selected teaching assignment.
            </div>
        @endif

    </div>
</div>


{{-- =====================================================
     AUTO-SUBMIT on year / exam change
     ====================================================== --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const yearSelect       = document.getElementById('academic_year_id');
    const examSelect       = document.getElementById('exam_master_id');
    const assignmentSelect = document.getElementById('teacher_subject_allocation_id');
    const filterForm       = document.getElementById('marksFilterForm');

    if (!filterForm) return;

    if (yearSelect) {
        yearSelect.addEventListener('change', function () {
            if (assignmentSelect) {
                assignmentSelect.innerHTML = '<option value="">Select Exam First</option>';
                assignmentSelect.disabled = true;
            }
            filterForm.submit();
        });
    }

    if (examSelect) {
        examSelect.addEventListener('change', function () {
            if (assignmentSelect) {
                assignmentSelect.innerHTML = '<option value="">Loading assignments...</option>';
                assignmentSelect.disabled = true;
            }
            filterForm.submit();
        });
    }

});
</script>

</x-app-layout>