<x-app-layout>

<style>
/* =========================================================
   PAGE
========================================================= */
.exam-master-page,
.exam-master-page * {
    box-sizing: border-box;
    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 13px;
}

.exam-master-page {
    background: #f0f0f0;
    padding: 20px;
}

.exam-master-page h2 {
    margin: 0 !important;
    font-size: 21px !important;
    font-weight: 700 !important;
    color: #1D4ED8 !important;
    letter-spacing: 0.4px;
}

/* =========================================================
   CARD
========================================================= */
.exam-master-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    padding: 18px 20px;
    max-width: 100%;
}

.exam-master-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e5e7eb;
}

/* =========================================================
   FILTER ROW
========================================================= */
.exam-master-filter {
    display: flex;
    align-items: flex-end;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 16px;
}

.exam-master-filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
    flex: 0 0 auto;
}

.exam-master-filter-label {
    font-size: 11px !important;
    font-weight: 700 !important;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    white-space: nowrap;
}

/* =========================================================
   SELECT WRAPPER
========================================================= */
.exam-master-select-wrapper {
    position: relative;
    display: inline-block;
}

.exam-master-filter-select {
    height: 34px;
    padding: 4px 28px 4px 10px;
    font-size: 13px !important;
    font-weight: 600 !important;
    border: 1px solid #9CA3AF;
    border-radius: 6px;
    background: #ffffff;
    color: #111827;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    outline: none;
    transition: border-color .15s, box-shadow .15s;
}

.exam-master-filter-select:focus {
    border-color: #2563EB;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
}

.exam-master-select-year      { width: 170px; min-width: 170px; }
.exam-master-select-standard  { width: 190px; min-width: 190px; }
.exam-master-select-division  { width: 150px; min-width: 150px; }
.exam-master-select-exam      { width: 220px; min-width: 220px; }

.exam-master-select-arrow {
    position: absolute;
    top: 50%;
    right: 10px;
    transform: translateY(-50%);
    width: 0;
    height: 0;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 6px solid #6b7280;
    pointer-events: none;
}

/* =========================================================
   BUTTONS
========================================================= */
.exam-master-filter-button,
.exam-master-clear-button {
    height: 34px;
    min-width: 120px;
    padding: 0 18px !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    white-space: nowrap;
    cursor: pointer;
    border: 1px solid transparent;
    transition: background .15s, border-color .15s;
}

.exam-master-filter-button {
    background: #16A34A;
    color: #ffffff !important;
    border-color: #15803D;
}
.exam-master-filter-button:hover {
    background: #15803D;
    border-color: #166534;
}

.exam-master-clear-button {
    background: #6B7280;
    color: #ffffff !important;
    border-color: #4B5563;
}
.exam-master-clear-button:hover {
    background: #4B5563;
    border-color: #374151;
}

/* =========================================================
   ADD BUTTON
========================================================= */
.exam-master-page .erp-btn-add {
    height: 34px;
    padding: 0 18px !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    border-radius: 6px;
    background: #16A34A;
    color: #ffffff !important;
    border: 1px solid #15803D;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    white-space: nowrap;
    transition: background .15s, border-color .15s;
}
.exam-master-page .erp-btn-add:hover {
    background: #15803D;
    border-color: #166534;
}

/* =========================================================
   ALERTS
========================================================= */
.exam-master-page .alert-success,
.exam-master-page .alert-error {
    margin-bottom: 14px;
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 12.5px;
    font-weight: 600;
    line-height: 1.5;
}
.exam-master-page .alert-success {
    background: #dcfce7;
    border: 1px solid #86efac;
    color: #166534;
}
.exam-master-page .alert-error {
    background: #fee2e2;
    border: 1px solid #fca5a5;
    color: #991b1b;
}

/* =========================================================
   TABLE
========================================================= */
.exam-master-table-wrapper {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: #ffffff;
}

.exam-master-page table {
    width: 100%;
    table-layout: fixed;
    border-collapse: collapse;
    margin: 0;
    font-size: 13px !important;
    background: #ffffff;
}

.exam-master-page table thead tr {
    background: #1e3a8a;
}

.exam-master-page table th {
    font-size: 11.5px !important;
    font-weight: 700 !important;
    text-align: left;
    padding: 8px 8px !important;
    border: 1px solid #1e40af !important;
    color: #ffffff !important;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    white-space: nowrap;
    background: #1e3a8a;
}

.exam-master-page table td {
    font-size: 13px !important;
    padding: 6px 8px !important;
    border: 1px solid #d1d5db !important;
    vertical-align: middle;
    color: #111827;
    overflow-wrap: anywhere;
    word-break: break-word;
    line-height: 1.25;
}

.exam-master-page table tbody tr:nth-child(even) {
    background: #f9fafb;
}

.exam-master-page table tbody tr:hover {
    background: #fef9c3;
}

/* =========================================================
   ACTION CELL — Edit + Force Delete on one row
========================================================= */
.action-cell {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    flex-wrap: nowrap;
    white-space: nowrap;
}

.action-delete-form {
    display: inline-block;
    margin: 0;
    padding: 0;
}

/* =========================================================
   ACTION BUTTONS (inside table)
========================================================= */
.exam-master-page .erp-btn-edit,
.exam-master-page .erp-btn-delete {
    height: 28px;
    padding: 0 12px !important;
    font-size: 11.5px !important;
    font-weight: 700 !important;
    border-radius: 5px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    white-space: nowrap;
    border: 1px solid transparent;
    cursor: pointer;
    transition: background .15s, border-color .15s;
}

.exam-master-page .erp-btn-edit {
    background: #2563EB;
    color: #ffffff !important;
    border-color: #1D4ED8;
    min-width: 70px;
}
.exam-master-page .erp-btn-edit:hover {
    background: #1D4ED8;
    border-color: #1e40af;
}

.exam-master-page .erp-btn-delete {
    background: #DC2626;
    color: #ffffff !important;
    border-color: #B91C1C;
    min-width: 95px;
}
.exam-master-page .erp-btn-delete:hover {
    background: #B91C1C;
    border-color: #991b1b;
}

/* =========================================================
   BADGES
========================================================= */
.passing-percentage-35,
.passing-percentage-40,
.active-badge,
.inactive-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 5px;
    font-weight: 700;
    font-size: 11.5px !important;
    letter-spacing: 0.3px;
    text-transform: uppercase;
    border: 1px solid transparent;
}

.passing-percentage-35 {
    background: #DCFCE7;
    color: #166534;
    border-color: #86EFAC;
}

.passing-percentage-40 {
    background: #DBEAFE;
    color: #1E40AF;
    border-color: #93C5FD;
}

.active-badge {
    background: #DCFCE7;
    color: #166534;
    border-color: #86EFAC;
}

.inactive-badge {
    background: #FEE2E2;
    color: #991B1B;
    border-color: #FCA5A5;
}

/* =========================================================
   TEACHER ALLOCATION STATUS
========================================================= */
.alloc-cell-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 4px;
}

.alloc-ready,
.alloc-pending,
.alloc-not-configured {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 6px;
    font-weight: 700;
    font-size: 11.5px !important;
    text-decoration: none;
    white-space: nowrap;
    border: 1px solid transparent;
    letter-spacing: 0.2px;
}

.alloc-ready {
    background: #DCFCE7;
    color: #166534;
    border-color: #86EFAC;
}

.alloc-pending {
    background: #FEE2E2;
    color: #991B1B;
    border-color: #FCA5A5;
}
.alloc-pending:hover { background: #FECACA; }

.alloc-not-configured {
    background: #FFEDD5;
    color: #9A3412;
    border-color: #FDBA74;
}

.alloc-dot {
    display: inline-block;
    width: 7px;
    height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
}
.alloc-dot-green { background: #22C55E; }
.alloc-dot-red   { background: #EF4444; }

.alloc-breakdown {
    display: inline-flex;
    flex-wrap: wrap;
    gap: 3px;
    margin: 0;
    font-size: 10.5px;
    color: #7f1d1d;
    font-weight: 600;
    line-height: 1;
}
.alloc-breakdown span {
    display: inline-block;
    background: #FEE2E2;
    border-radius: 4px;
    padding: 3px 6px;
    white-space: nowrap;
}

/* =========================================================
   NO RECORD
========================================================= */
.exam-master-no-record {
    padding: 24px 20px !important;
    text-align: center;
    color: #92400E;
    background: #fffbeb;
    font-weight: 600;
    font-size: 13.5px;
}

/* =========================================================
   RESPONSIVE
========================================================= */
@media (max-width: 900px) {
    .exam-master-page { padding: 12px; }
    .exam-master-card { padding: 14px; }

    .exam-master-header { flex-direction: column; align-items: stretch; }
    .exam-master-header .erp-btn-add { width: 100%; }

    .exam-master-filter {
        flex-direction: column;
        align-items: stretch;
    }

    .exam-master-filter-group,
    .exam-master-filter-select,
    .exam-master-select-year,
    .exam-master-select-standard,
    .exam-master-select-division,
    .exam-master-select-exam {
        width: 100% !important;
        min-width: 100% !important;
    }

    .exam-master-filter-button,
    .exam-master-clear-button {
        width: 100%;
    }
}
</style>


<div class="exam-master-page">

    <div class="exam-master-card">

        {{-- =====================================================
             HEADER
        ====================================================== --}}
        <div class="exam-master-header">
            <h2>Exam Master</h2>
            <a href="{{ route('exam-masters.create') }}" class="erp-btn-add">
                + Add Exam
            </a>
        </div>


        {{-- =====================================================
             FILTERS
        ====================================================== --}}
        <form method="GET" action="{{ route('exam-masters.index') }}" id="examMasterFilterForm">

            <div class="exam-master-filter">

                {{-- Academic Year --}}
                <div class="exam-master-filter-group">
                    <label for="academic_year_id" class="exam-master-filter-label">
                        Academic Year
                    </label>
                    <div class="exam-master-select-wrapper">
                        <select name="academic_year_id"
                                id="academic_year_id"
                                class="exam-master-filter-select exam-master-select-year">
                            <option value="">All Academic Years</option>
                            @foreach($academicYears as $academicYear)
                                <option value="{{ $academicYear->id }}"
                                    {{ (string)($academicYearId ?? '') === (string)$academicYear->id ? 'selected' : '' }}>
                                    {{ $academicYear->year_name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="exam-master-select-arrow"></span>
                    </div>
                </div>

                {{-- Standard --}}
                <div class="exam-master-filter-group">
                    <label for="standard_id" class="exam-master-filter-label">
                        Standard
                    </label>
                    <div class="exam-master-select-wrapper">
                        <select name="standard_id"
                                id="standard_id"
                                class="exam-master-filter-select exam-master-select-standard">
                            <option value="">All Standards</option>
                            @foreach($standards as $standard)
                                <option value="{{ $standard->id }}"
                                    {{ (string)($standardId ?? '') === (string)$standard->id ? 'selected' : '' }}>
                                    {{ $standard->standard_name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="exam-master-select-arrow"></span>
                    </div>
                </div>

                {{-- Division --}}
                <div class="exam-master-filter-group">
                    <label for="division_id" class="exam-master-filter-label">
                        Division
                    </label>
                    <div class="exam-master-select-wrapper">
                        <select name="division_id"
                                id="division_id"
                                class="exam-master-filter-select exam-master-select-division">
                            <option value="">All Divisions</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}"
                                    {{ (string)($divisionId ?? '') === (string)$division->id ? 'selected' : '' }}>
                                    {{ $division->division_name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="exam-master-select-arrow"></span>
                    </div>
                </div>

                {{-- Exam (filtered by Academic Year + Standard + Division server-side) --}}
                <div class="exam-master-filter-group">
                    <label for="exam_master_id" class="exam-master-filter-label">
                        Exam
                    </label>
                    <div class="exam-master-select-wrapper">
                        <select name="exam_master_id"
                                id="exam_master_id"
                                class="exam-master-filter-select exam-master-select-exam">
                            <option value="">All Exams</option>
                            @foreach($examOptions as $examOpt)
                                <option value="{{ $examOpt->id }}"
                                    {{ (string)($examId ?? '') === (string)$examOpt->id ? 'selected' : '' }}>
                                    {{ $examOpt->exam_name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="exam-master-select-arrow"></span>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="exam-master-filter-group">
                    <button type="submit" class="exam-master-filter-button">
                        Filter
                    </button>
                </div>

                <div class="exam-master-filter-group">
                    <a href="{{ route('exam-masters.index') }}" class="exam-master-clear-button">
                        Clear
                    </a>
                </div>

            </div>
        </form>


        {{-- =====================================================
             ALERTS
        ====================================================== --}}
        @if(session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert-error">
                {{ session('error') }}
            </div>
        @endif


        {{-- =====================================================
             TABLE
        ====================================================== --}}
        <div class="exam-master-table-wrapper">

            <table>

                <colgroup>
                    <col style="width:6%;">   {{-- Exam ID --}}
                    <col style="width:9%;">   {{-- Academic Year --}}
                    <col style="width:17%;">  {{-- Exam Name --}}
                    <col style="width:6%;">   {{-- Standard ID --}}
                    <col style="width:11%;">  {{-- Standard Name --}}
                    <col style="width:7%;">   {{-- Passing % --}}
                    <col style="width:20%;">  {{-- Teacher Allocation --}}
                    <col style="width:8%;">   {{-- Status --}}
                    <col style="width:16%;">  {{-- Action --}}
                </colgroup>

                <thead>
                    <tr>
                        <th style="text-align:center;">Exam ID</th>
                        <th style="text-align:center;">Academic Year</th>
                        <th>Exam Name</th>
                        <th style="text-align:center;">Standard ID</th>
                        <th>Standard Name</th>
                        <th style="text-align:center;">Passing %</th>
                        <th>Teacher Allocation</th>
                        <th style="text-align:center;">Status</th>
                        <th style="text-align:center;">Action</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($examMasters as $exam)

                    @php
                        $standardIdRow = (int) ($exam->standard_id ?? 0);

                        $standardNameRow = strtoupper(trim(
                            (string) (optional($exam->standard)->standard_name ?? '')
                        ));

                        $normalizedStandardName = preg_replace('/[^A-Z0-9]+/', '', $standardNameRow) ?? '';

                        $is35PercentStandard =
                            in_array($standardIdRow, [9, 10, 11, 12], true)
                            || in_array($normalizedStandardName, [
                                'NURSERY','NUR','JRKG','JUNIORKG','JUNIORKINDERGARTEN',
                                'SRKG','SENIORKG','SENIORKINDERGARTEN',
                                'NINTH','9TH','IX',
                                'TENTH','10TH','X',
                            ], true)
                            || str_contains($normalizedStandardName, 'ELEVENTH')
                            || $normalizedStandardName === 'XI'
                            || str_contains($normalizedStandardName, 'TWELFTH')
                            || $normalizedStandardName === 'XII';

                        $passingPercentage = $is35PercentStandard ? 35 : 40;

                        $pendingCount = $pendingCounts[$exam->id] ?? 0;
                    @endphp

                    <tr>

                        <td style="text-align:center;font-weight:700;color:#1e3a8a;">
                            {{ $exam->id }}
                        </td>

                        <td style="text-align:center;">
                            {{ $exam->academicYear->year_name ?? '-' }}
                        </td>

                        <td style="font-weight:700;">
                            {{ $exam->exam_name }}
                        </td>

                        <td style="text-align:center;">
                            {{ $exam->standard_id }}
                        </td>

                        <td style="font-weight:700;">
                            {{ $exam->standard->standard_name ?? '-' }}
                        </td>

                        <td style="text-align:center;">
                            <span class="{{ $passingPercentage === 35 ? 'passing-percentage-35' : 'passing-percentage-40' }}">
                                {{ $passingPercentage }}%
                            </span>
                        </td>

                        {{-- TEACHER ALLOCATION STATUS --}}
                        <td>
                            @php
                                $pending   = $pendingCounts[$exam->id] ?? 0;
                                $breakdown = $breakdowns[$exam->id] ?? [];
                                $tooltip   = !empty($breakdown)
                                    ? collect($breakdown)->map(fn($c, $d) => "$d: $c pending")->implode(' · ')
                                    : '';
                            @endphp

                            @if($pending === -1)
                                <span
                                    class="alloc-not-configured"
                                    title="This exam has no subjects configured. Edit the exam to add subjects first."
                                >
                                    <span class="alloc-dot" style="background:#F97316;"></span>
                                    NO SUBJECTS
                                </span>
                            @elseif($pending > 0)
                                <div class="alloc-cell-row">
                                    <a
                                        href="{{ route('teacher-bulk-allocation.index', ['exam_master_id' => $exam->id]) }}"
                                        class="alloc-pending"
                                        title="Click to allocate · {{ $tooltip }}"
                                    >
                                        <span class="alloc-dot alloc-dot-red"></span>
                                        {{ $pending }} PENDING
                                    </a>

                                    @if(!empty($breakdown))
                                        <div class="alloc-breakdown">
                                            @foreach($breakdown as $divName => $count)
                                                <span>{{ $divName }}: {{ $count }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @else
                                <span class="alloc-ready">
                                    <span class="alloc-dot alloc-dot-green"></span>
                                    READY
                                </span>
                            @endif
                        </td>

                        <td style="text-align:center;">
                            @if($exam->is_active)
                                <span class="active-badge">Active</span>
                            @else
                                <span class="inactive-badge">Inactive</span>
                            @endif
                        </td>

                        <td style="text-align:center;white-space:nowrap;">
                            <div class="action-cell">
                                <a href="{{ route('exam-masters.edit', $exam->id) }}"
                                   class="erp-btn-edit">
                                    Edit
                                </a>

                                <form
                                    action="{{ route('exam-masters.destroy', $exam->id) }}"
                                    method="POST"
                                    class="action-delete-form"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="erp-btn-delete"
                                        onclick="return confirmExamDelete();"
                                    >
                                        Force Delete
                                    </button>
                                </form>
                            </div>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="9" class="exam-master-no-record">
                            No Records Found
                        </td>
                    </tr>

                @endforelse
                </tbody>

            </table>

        </div>

    </div>

</div>


<script>
function confirmExamDelete() {
    return confirm(
        'FINAL WARNING!\n\n' +
        'You are about to permanently delete this Exam.\n\n' +
        'Depending on the controller/database relationships, ' +
        'the following related records may also be deleted:\n\n' +
        '• Exam Subject Configuration\n' +
        '• Teacher Marks Status\n' +
        '• Marks Entries\n' +
        '• Student Marks\n' +
        '• Student Results\n' +
        '• Result Details\n\n' +
        'THIS ACTION CANNOT BE UNDONE.\n\n' +
        'Click OK only if you are sure you want to delete this Exam.'
    );
}

document.addEventListener('DOMContentLoaded', function () {

    const form           = document.getElementById('examMasterFilterForm');
    const academicSelect = document.getElementById('academic_year_id');
    const standardSelect = document.getElementById('standard_id');
    const divisionSelect = document.getElementById('division_id');
    const examSelect     = document.getElementById('exam_master_id');

    if (!form) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | ACADEMIC YEAR CHANGE → reload so the Exam dropdown is re-filtered
    |--------------------------------------------------------------------------
    */
    if (academicSelect) {
        academicSelect.addEventListener('change', function () {
            if (examSelect) examSelect.value = '';
            form.submit();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | STANDARD CHANGE → reload so the Exam dropdown is re-filtered
    |--------------------------------------------------------------------------
    */
    if (standardSelect) {
        standardSelect.addEventListener('change', function () {
            if (examSelect) examSelect.value = '';
            form.submit();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | DIVISION CHANGE → reload so the Exam dropdown is re-filtered
    |--------------------------------------------------------------------------
    */
    if (divisionSelect) {
        divisionSelect.addEventListener('change', function () {
            if (examSelect) examSelect.value = '';
            form.submit();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | EXAM CHANGE → submit
    |--------------------------------------------------------------------------
    */
    if (examSelect) {
        examSelect.addEventListener('change', function () {
            form.submit();
        });
    }

});
</script>

</x-app-layout>