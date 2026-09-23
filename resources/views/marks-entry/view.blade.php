<x-app-layout>

@php
    $H = \App\Helpers\MarksEntryBladeHelper::class;

    /* -------- component flags (theory / oral / practical) -------- */
    $flags = $H::resolveComponentFlags(
        $showTheory    ?? null,
        $showOral      ?? null,
        $showPractical ?? null,
        $records       ?? null
    );
    $showTheory    = $flags['theory'];
    $showOral      = $flags['oral'];
    $showPractical = $flags['practical'];

    /* -------- tab URLs -------- */
    $filterParams = array_filter([
        'exam_master_id'                => request('exam_master_id'),
        'teacher_subject_allocation_id' => request('teacher_subject_allocation_id'),
    ], fn ($v) => $v !== null && $v !== '');

    $marksEntryUrl = route('marks-entry.index') . $H::querySuffix($filterParams);
    $viewMarksUrl  = request()->fullUrl();

    $emptyCols = $H::columnCount($showTheory, $showOral, $showPractical);
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

    .selected-info {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 10px;
        padding: 8px 10px;
        border-radius: 4px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1e3a8a;
        font-size: 11px;
    }
    .selected-info-item      { font-weight: 700; }
    .selected-info-separator { color: #93c5fd; }

    .marks-table-wrapper {
        margin-top: 12px;
        overflow-x: auto;
        border: 1px solid #d1d5db;
        border-radius: 4px;
    }

    .marks-table {
        width: 100%;
        border-collapse: collapse;
        background: #ffffff;
        font-size: 11px;
    }
    .marks-table th {
        background: #dbeafe;
        color: #1e3a8a;
        border: 1px solid #cbd5e1;
        padding: 5px 4px;
        text-align: center;
        line-height: 1.15;
        font-weight: 700;
        font-size: 10px;
    }
    .marks-table td {
        border: 1px solid #d1d5db;
        padding: 3px 4px;
        white-space: nowrap;
        vertical-align: middle;
        font-size: 11px;
        line-height: 1.2;
    }
    .marks-table tbody tr:hover { background: #f8fafc; }

    .center            { text-align: center; }

    /* ---------- STUDENT NAME — matches edit blade (600, not 700) ---------- */
    .student-name-cell {
        min-width: 150px;
        max-width: 220px;
        white-space: normal !important;
        font-weight: 600;
        color: #111827;
        line-height: 1.25;
    }

    /* Prevent <strong> inside the cell from pushing weight to 700 */
    .student-name-cell strong {
        font-weight: 600;
    }

    .error-box {
        margin-top: 10px;
        padding: 8px 10px;
        border-radius: 4px;
        background: #fef2f2;
        border: 1px solid #fca5a5;
        color: #991b1b;
        font-size: 11px;
        font-weight: 600;
    }
    .warning-box {
        margin-top: 10px;
        padding: 8px 10px;
        border-radius: 4px;
        background: #fffbeb;
        border: 1px solid #fcd34d;
        color: #92400e;
        font-size: 11px;
        font-weight: 600;
    }

    {!! $H::tabStyles() !!}
    {!! $H::viewStyles() !!}
</style>

<div class="erp-page marks-view-page">
    <div class="marks-view-card">

        <h2 class="marks-view-title">Examination Marks</h2>

        {{-- ================= TABS (shared helper) ================= --}}
        {!! $H::renderTabs('view', $marksEntryUrl, $viewMarksUrl) !!}

        {{-- ================= ERRORS ================= --}}
        @if(!empty($error))
            <div class="error-box">{{ $error }}</div>
        @endif

        @if(session('error'))
            <div class="error-box">{{ session('error') }}</div>
        @endif

        {{-- ================= SELECTED INFO ================= --}}
        @if($selectedTsa && $exam)
            @php
                $teacherName = optional(optional($selectedTsa->allocation)->teacher)->name ?? 'Teacher';
                $subjectName = optional($selectedSubject)->subject_name ?? 'Subject';
                $stdName     = optional(optional($selectedTsa->allocation)->standard)->standard_name ?? '';
                $divName     = optional(optional($selectedTsa->allocation)->division)->division_name ?? '';
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

                            {{-- STUDENT NAME — bold 600, matches edit blade --}}
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

</x-app-layout>