<x-app-layout>

<style>
/* =========================================================
   PAGE
========================================================= */
.skill-page,
.skill-page * {
    font-family: Arial, sans-serif !important;
    font-size: 13px;
    box-sizing: border-box;
}

.skill-page h2 {
    font-size: 21px !important;
    font-weight: 700 !important;
    color: #1D4ED8 !important;
    margin: 0 0 14px 0;
}

/* =========================================================
   CARDS
========================================================= */
.skill-card {
    background: #ffffff;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 14px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    margin-bottom: 14px;
}

/* =========================================================
   STANDARD GRID
========================================================= */
.filter-section-title {
    font-size: 12px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.standard-grid {
    border: 1px solid #d1d5db;
    border-radius: 6px;
    overflow: hidden;
    margin-bottom: 14px;
}

.standard-row {
    display: grid;
    grid-template-columns: 190px 1fr;
    align-items: stretch;
    border-bottom: 1px solid #e5e7eb;
}
.standard-row:last-child { border-bottom: none; }
.standard-row:nth-child(even) { background: #f9fafb; }

.standard-row-label {
    padding: 9px 14px;
    font-size: 12px;
    font-weight: 700;
    color: #1e3a8a;
    letter-spacing: 0.3px;
    border-right: 1px solid #e5e7eb;
    background: #f3f4f6;
    display: flex;
    align-items: center;
}

.standard-row-options {
    padding: 8px 12px;
    display: flex;
    flex-wrap: wrap;
    gap: 12px 18px;
    align-items: center;
}

.standard-option,
.division-option {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    font-weight: 600;
    color: #111827;
    cursor: pointer;
    white-space: nowrap;
}

.standard-option input[type="checkbox"],
.division-option input[type="checkbox"] {
    width: 15px;
    height: 15px;
    cursor: pointer;
    accent-color: #2563EB;
    margin: 0;
}

/* =========================================================
   FILTER ROW
========================================================= */
.filter-row {
    display: flex;
    align-items: flex-end;
    gap: 20px;
    flex-wrap: wrap;
    margin-bottom: 14px;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.filter-label {
    font-size: 12px;
    font-weight: 700;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.filter-select {
    height: 34px;
    padding: 4px 10px;
    font-size: 13px;
    font-weight: 600;
    border: 1px solid #9CA3AF;
    border-radius: 5px;
    background: #ffffff;
    color: #111827;
    min-width: 210px;
    outline: none;
}
.filter-select:focus {
    border-color: #2563EB;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
}

.division-options {
    display: flex;
    flex-wrap: wrap;
    gap: 12px 18px;
    padding: 6px 0 8px 0;
}

/* =========================================================
   ACTION BUTTONS
========================================================= */
.action-row {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 4px;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 36px;
    min-width: 140px;
    padding: 0 18px;
    border-radius: 5px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    border: 1px solid transparent;
    transition: background .15s;
    box-sizing: border-box;
}

.btn-primary {
    background: #16A34A; color: #ffffff; border-color: #15803D;
}
.btn-primary:hover { background: #15803D; }

.btn-secondary {
    background: #6B7280; color: #ffffff; border-color: #4B5563;
}
.btn-secondary:hover { background: #4B5563; }

.btn-danger {
    background: #DC2626; color: #ffffff; border-color: #B91C1C;
}
.btn-danger:hover { background: #B91C1C; }

/* =========================================================
   BULK BAR
========================================================= */
.bulk-bar {
    background: #eff6ff;
    border: 1px solid #93c5fd;
    border-radius: 8px;
    padding: 12px 14px;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}

.bulk-bar-title {
    font-size: 13px;
    font-weight: 700;
    color: #1e40af;
    white-space: nowrap;
}

.bulk-bar-hint {
    font-size: 12px;
    color: #1e40af;
    opacity: 0.8;
    margin-left: auto;
    white-space: nowrap;
}

.bulk-select {
    height: 34px;
    padding: 4px 10px;
    font-size: 13px;
    font-weight: 600;
    border: 1px solid #93c5fd;
    border-radius: 5px;
    background: #ffffff;
    color: #111827;
    min-width: 240px;
    outline: none;
    cursor: pointer;
}
.bulk-select:focus {
    border-color: #2563EB;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
}

.btn-apply-all {
    height: 34px;
    padding: 0 18px;
    font-size: 13px;
    font-weight: 700;
    border-radius: 5px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background .15s;
    border: 1px solid #1e3a8a;
    background: #1e40af;
    color: #ffffff;
}
.btn-apply-all:hover { background: #1e3a8a; }

.btn-remove-all {
    height: 34px;
    padding: 0 18px;
    font-size: 13px;
    font-weight: 700;
    border-radius: 5px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background .15s;
    border: 1px solid #B91C1C;
    background: #DC2626;
    color: #ffffff;
}
.btn-remove-all:hover { background: #B91C1C; }

/* =========================================================
   ALERT
========================================================= */
.alert {
    padding: 10px 14px;
    border-radius: 6px;
    margin-bottom: 14px;
    font-weight: 700;
    font-size: 13px;
}
.alert-success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #86efac;
}

/* =========================================================
   EMPTY STATE
========================================================= */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    text-align: center;
    background: #ffffff;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.empty-state svg,
.empty-state img {
    width: 72px !important;
    height: 72px !important;
    max-width: 72px !important;
    max-height: 72px !important;
    display: block;
    margin: 0 auto 14px auto;
    opacity: 0.55;
}

.empty-state-title {
    font-size: 15px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 4px;
}

.empty-state-subtitle {
    font-size: 13px;
    color: #6B7280;
}

/* =========================================================
   STUDENTS TABLE
========================================================= */
.table-wrapper {
    background: #ffffff;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.students-table {
    width: 100%;
    table-layout: fixed;
    border-collapse: collapse;
    margin: 0;
    font-size: 13px;
}

.students-table thead tr {
    background: #1e3a8a;
    color: #ffffff;
}

.students-table th {
    padding: 9px 8px;
    font-size: 13px;
    font-weight: 700;
    text-align: left;
    border: 1px solid #d1d5db;
    color: #ffffff;
    white-space: nowrap;
}
.students-table th.center { text-align: center; }

.students-table td {
    padding: 8px;
    border: 1px solid #d1d5db;
    vertical-align: top;
    color: #111827;
    overflow-wrap: anywhere;
    word-break: break-word;
}

.students-table tbody tr:nth-child(even) { background: #f9fafb; }
.students-table tbody tr:hover { background: #fef9c3; }

/* Subject checkbox pills */
.subject-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 8px;
    margin: 2px 4px 2px 0;
    border-radius: 4px;
    border: 1px solid #d1d5db;
    background: #f9fafb;
    font-size: 12px;
    font-weight: 600;
    color: #374151;
    cursor: pointer;
    white-space: nowrap;
    transition: background .12s, border-color .12s;
}

.subject-pill input[type="checkbox"] {
    width: 14px;
    height: 14px;
    cursor: pointer;
    accent-color: #2563EB;
    margin: 0;
}

.subject-pill.checked {
    background: #dbeafe;
    border-color: #93c5fd;
    color: #1e40af;
}

/* =========================================================
   RESPONSIVE
========================================================= */
@media (max-width: 900px) {

    .standard-row { grid-template-columns: 1fr; }

    .standard-row-label {
        border-right: none;
        border-bottom: 1px solid #e5e7eb;
        justify-content: center;
    }

    .filter-select { min-width: 100%; }
    .bulk-bar-hint { display: none; }
}
</style>


@php
    /*
    |--------------------------------------------------------------------------
    | Build lookup maps for Class / Division names.
    |--------------------------------------------------------------------------
    */
    $standardMap = $standards->pluck('standard_name', 'id');
    $divisionMap = $divisions->pluck('division_name', 'id');
@endphp


<div class="skill-page p-3">

    <h2>Student Skill Subject Allocation</h2>

    {{-- SUCCESS --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif


    {{-- =====================================================
         FILTER FORM
    ====================================================== --}}
    <form method="GET"
          action="{{ route('student-skill-subject-allocation.index') }}"
          id="skillFilterForm">

        <div class="skill-card">

            <div class="filter-section-title">Standards</div>

            <div class="standard-grid">

                @php
                    $groupedStandards = $standards->groupBy(function ($s) {
                        return optional($s->section)->section_name
                            ?? $s->section_name
                            ?? 'All Standards';
                    });

                    $selectedStandardIds = array_map('strval', (array) request('standard_ids', []));
                    $selectedDivisionIds = array_map('strval', (array) request('division_ids', []));
                @endphp

                @foreach($groupedStandards as $sectionName => $sectionStandards)

                    <div class="standard-row">

                        <div class="standard-row-label">
                            {{ strtoupper($sectionName) }}
                        </div>

                        <div class="standard-row-options">

                            @foreach($sectionStandards as $standard)

                                <label class="standard-option">
                                    <input type="checkbox"
                                           name="standard_ids[]"
                                           value="{{ $standard->id }}"
                                           {{ in_array((string)$standard->id, $selectedStandardIds, true) ? 'checked' : '' }}>
                                    {{ $standard->standard_name }}
                                </label>

                            @endforeach

                        </div>

                    </div>

                @endforeach

            </div>


            <div class="filter-row">

                <div class="filter-group">
                    <label for="academic_year_id" class="filter-label">Academic Year</label>
                    <select name="academic_year_id"
                            id="academic_year_id"
                            class="filter-select">
                        <option value="">Select Academic Year</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}"
                                {{ (string)request('academic_year_id') === (string)$year->id ? 'selected' : '' }}>
                                {{ $year->year_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group" style="flex:1;min-width:280px;">
                    <label class="filter-label">Divisions</label>
                    <div class="division-options">
                        @foreach($divisions as $division)
                            <label class="division-option">
                                <input type="checkbox"
                                       name="division_ids[]"
                                       value="{{ $division->id }}"
                                       {{ in_array((string)$division->id, $selectedDivisionIds, true) ? 'checked' : '' }}>
                                {{ $division->division_name }}
                            </label>
                        @endforeach
                    </div>
                </div>

            </div>


            <div class="action-row">
                <button type="submit" class="btn btn-primary">Load Students</button>
                <a href="{{ route('student-skill-subject-allocation.index') }}"
                   class="btn btn-secondary">Reset</a>
            </div>

        </div>

    </form>


    {{-- =====================================================
         CONTENT
    ====================================================== --}}

    @if($students->isNotEmpty())

        {{-- ============ BULK APPLY ============ --}}
        <form method="POST"
              action="{{ route('student-skill-subject-allocation.bulk-allocate') }}"
              style="margin-bottom:8px;">
            @csrf
            <input type="hidden" name="academic_year_id" value="{{ request('academic_year_id') }}">

            @foreach($students as $s)
                <input type="hidden" name="student_ids[]" value="{{ $s->Studentid }}">
            @endforeach

            <div class="bulk-bar">

                <span class="bulk-bar-title">
                    Add one subject to all {{ $students->count() }} students:
                </span>

                <select name="subject_id" class="bulk-select" required>
                    <option value="">— Select a Skill Subject —</option>
                    @foreach($skillSubjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->subject_name }}</option>
                    @endforeach
                </select>

                <button type="submit"
                        class="btn-apply-all"
                        onclick="return confirm('Add this subject to ALL {{ $students->count() }} loaded students? Their existing subjects will be kept.');">
                    Apply to All
                </button>

                <span class="bulk-bar-hint">
                    Or set individually in the table below
                </span>

            </div>
        </form>


        {{-- ============ BULK REMOVE ============ --}}
        <form method="POST"
              action="{{ route('student-skill-subject-allocation.bulk-remove') }}"
              style="margin-bottom:14px;">
            @csrf
            <input type="hidden" name="academic_year_id" value="{{ request('academic_year_id') }}">

            @foreach($students as $s)
                <input type="hidden" name="student_ids[]" value="{{ $s->Studentid }}">
            @endforeach

            <div class="bulk-bar" style="background:#fef2f2;border-color:#fca5a5;">

                <span class="bulk-bar-title" style="color:#991b1b;">
                    Remove one subject from all {{ $students->count() }} students:
                </span>

                <select name="subject_id" class="bulk-select" style="border-color:#fca5a5;" required>
                    <option value="">— Select a Skill Subject —</option>
                    @foreach($skillSubjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->subject_name }}</option>
                    @endforeach
                </select>

                <button type="submit"
                        class="btn-remove-all"
                        onclick="return confirm('Remove this subject from ALL {{ $students->count() }} loaded students? Their other subjects will be kept.');">
                    Remove from All
                </button>

                <span class="bulk-bar-hint" style="color:#991b1b;">
                    Use this to correct a wrongly assigned subject
                </span>

            </div>
        </form>


        {{-- ============ INDIVIDUAL TABLE ============ --}}
        <form method="POST"
              action="{{ route('student-skill-subject-allocation.save') }}">
            @csrf
            <input type="hidden" name="academic_year_id" value="{{ request('academic_year_id') }}">

            <div class="table-wrapper">

                <table class="students-table">

                    <colgroup>
                        <col style="width:5%;">
                        <col style="width:8%;">
                        <col style="width:30%;">
                        <col style="width:12%;">
                        <col style="width:6%;">
                        <col style="width:39%;">
                    </colgroup>

                    <thead>
                        <tr>
                            <th class="center">#</th>
                            <th>Roll No</th>
                            <th>Student Name</th>
                            <th>Class</th>
                            <th class="center">Div.</th>
                            <th>Skill Subjects (check all that apply)</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($students as $index => $student)

                            @php
                                $standardId = $student->class_standard_id ?? null;
                                $divisionId = $student->class_division_id ?? null;

                                $className    = $standardId !== null ? ($standardMap[$standardId] ?? null) : null;
                                $divisionName = $divisionId !== null ? ($divisionMap[$divisionId] ?? null) : null;

                                if ((!$className || !$divisionName) && !empty($student->class_label)) {
                                    $parts = array_map('trim', explode(' - ', $student->class_label, 2));
                                    $className    = $className    ?: ($parts[0] ?? '');
                                    $divisionName = $divisionName ?: ($parts[1] ?? '');
                                }

                                $selected = array_map('intval', $student->selected_subjects ?? []);

                                // Student name + father name combined
                                $fullStudentName = trim(
                                    ($student->studname ?? '') . ' ' . ($student->fathername ?? '')
                                );

                                if ($fullStudentName === '') {
                                    $fullStudentName = '-';
                                }
                            @endphp

                            <tr>
                                <td class="center" style="font-weight:700;">{{ $index + 1 }}</td>
                                <td>{{ $student->rollno ?? '-' }}</td>
                                <td style="font-weight:600;">{{ $fullStudentName }}</td>
                                <td>{{ $className ?: '-' }}</td>
                                <td class="center">{{ $divisionName ?: '-' }}</td>

                                <td>
                                    <div style="display:flex;flex-wrap:wrap;gap:4px 6px;">

                                        @foreach($skillSubjects as $subject)

                                            @php $isChecked = in_array((int)$subject->id, $selected, true); @endphp

                                            <label class="subject-pill {{ $isChecked ? 'checked' : '' }}">
                                                <input type="checkbox"
                                                       name="skill_subjects[{{ $student->Studentid }}][]"
                                                       value="{{ $subject->id }}"
                                                       {{ $isChecked ? 'checked' : '' }}>
                                                {{ $subject->subject_name }}
                                            </label>

                                        @endforeach

                                    </div>
                                </td>
                            </tr>

                        @endforeach
                    </tbody>

                </table>

            </div>

            <div class="action-row" style="margin-top:14px;">
                <button type="submit" class="btn btn-primary">Save Allocations</button>
            </div>

        </form>


    @elseif(
        request()->filled('academic_year_id')
        || request()->filled('standard_ids')
        || request()->filled('division_ids')
    )

        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                 fill="none" stroke="#9CA3AF" stroke-width="1.5"
                 stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="7"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <div class="empty-state-title">No students found</div>
            <div class="empty-state-subtitle">Try adjusting your filters.</div>
        </div>

    @else

        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                 fill="none" stroke="#9CA3AF" stroke-width="1.5"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <div class="empty-state-title">Select your filters above</div>
            <div class="empty-state-subtitle">
                Pick an Academic Year, Standards and Divisions, then click <strong>Load Students</strong>.
            </div>
        </div>

    @endif

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    // Live styling for subject pills as they're toggled
    document.querySelectorAll('.subject-pill input[type="checkbox"]').forEach(function (cb) {

        cb.addEventListener('change', function () {
            this.closest('.subject-pill')
                .classList.toggle('checked', this.checked);
        });
    });

});
</script>

</x-app-layout>