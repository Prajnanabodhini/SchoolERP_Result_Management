<x-app-layout>

<style>
/* =========================================================
   PAGE
========================================================= */
.ssa-page,
.ssa-page * {
    font-family: Arial, sans-serif !important;
    font-size: 13px;
    box-sizing: border-box;
}

.ssa-page h2 {
    font-size: 21px !important;
    font-weight: 700 !important;
    color: #1D4ED8 !important;
    margin: 0 0 14px 0;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

/* =========================================================
   CARD
========================================================= */
.ssa-card {
    background: #ffffff;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 14px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    margin-bottom: 14px;
}

/* =========================================================
   FILTER ROW
========================================================= */
.ssa-filter {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 12px;
    margin-bottom: 14px;
}

.ssa-filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
    min-width: 240px;
}

.ssa-filter-label {
    font-size: 12px;
    font-weight: 700;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.ssa-select {
    height: 34px;
    padding: 4px 10px;
    font-size: 13px;
    font-weight: 600;
    border: 1px solid #9CA3AF;
    border-radius: 5px;
    background: #ffffff;
    color: #111827;
    outline: none;
    min-width: 240px;
}
.ssa-select:focus {
    border-color: #2563EB;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
}

/* =========================================================
   BUTTONS
========================================================= */
.ssa-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 34px;
    min-width: 150px;
    padding: 0 18px;
    border-radius: 5px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    border: 1px solid transparent;
    transition: background .15s;
}

.ssa-btn-primary {
    background: #16A34A;
    color: #ffffff;
    border-color: #15803D;
}
.ssa-btn-primary:hover { background: #15803D; }

/* =========================================================
   ALERT
========================================================= */
.ssa-alert {
    padding: 10px 14px;
    border-radius: 6px;
    margin-bottom: 14px;
    font-weight: 700;
    font-size: 13px;
}
.ssa-alert-success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #86efac;
}

/* =========================================================
   SUBJECT TABLE
========================================================= */
.ssa-table-wrapper {
    background: #ffffff;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    margin-bottom: 14px;
}

.ssa-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    margin: 0;
    font-size: 13px;
}

.ssa-table thead tr {
    background: #1e3a8a;
    color: #ffffff;
}

.ssa-table th {
    padding: 10px 14px;
    font-size: 13px;
    font-weight: 700;
    text-align: left;
    border: 1px solid #d1d5db;
    color: #ffffff;
    white-space: nowrap;
    letter-spacing: 0.3px;
}

.ssa-table td {
    padding: 10px 14px;
    border: 1px solid #d1d5db;
    vertical-align: middle;
    color: #111827;
    overflow-wrap: anywhere;
}

.ssa-table tbody tr:nth-child(even) {
    background: #f9fafb;
}

.ssa-table tbody tr:hover {
    background: #fef9c3;
}

/* Divider between the two columns */
.ssa-table td.col-academic {
    border-right: 2px solid #e5e7eb;
}

/* =========================================================
   SUBJECT CHECKBOX ROW
========================================================= */
.subject-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 4px 6px;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 600;
    color: #374151;
    transition: background .12s;
}

.subject-row:hover {
    background: #dbeafe;
}

.subject-row input[type="checkbox"] {
    width: 16px;
    height: 16px;
    cursor: pointer;
    accent-color: #2563EB;
    flex-shrink: 0;
    margin: 0;
}

.subject-row label {
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    color: #1f2937;
    user-select: none;
}

.subject-row input[type="checkbox"]:checked + label {
    color: #1e40af;
    font-weight: 700;
}

/* Empty cell placeholder */
.empty-cell {
    color: #d1d5db;
    font-style: italic;
    font-size: 12px;
}

/* =========================================================
   EMPTY STATE
========================================================= */
.ssa-empty-state {
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

.ssa-empty-state svg {
    width: 72px !important;
    height: 72px !important;
    max-width: 72px !important;
    max-height: 72px !important;
    display: block;
    margin: 0 auto 14px auto;
    opacity: 0.55;
}

.ssa-empty-title {
    font-size: 15px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 4px;
}

.ssa-empty-subtitle {
    font-size: 13px;
    color: #6B7280;
}

/* =========================================================
   FOOTER ACTION
========================================================= */
.ssa-action-footer {
    display: flex;
    justify-content: flex-end;
    margin-top: 14px;
}

/* =========================================================
   RESPONSIVE
========================================================= */
@media (max-width: 900px) {
    .ssa-table,
    .ssa-table thead,
    .ssa-table tbody,
    .ssa-table th,
    .ssa-table td,
    .ssa-table tr {
        display: block;
    }

    .ssa-table thead {
        display: none;
    }

    .ssa-table td.col-academic {
        border-right: none;
        border-bottom: 2px solid #e5e7eb;
    }

    .ssa-table td::before {
        content: attr(data-label);
        display: block;
        font-weight: 700;
        font-size: 11px;
        color: #1e3a8a;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-bottom: 6px;
    }
}
</style>


<div class="ssa-page p-3">

    <h2>Standard Subject Allocation</h2>

    @if(session('success'))
        <div class="ssa-alert ssa-alert-success">{{ session('success') }}</div>
    @endif


    {{-- =====================================================
         FILTER FORM
    ====================================================== --}}
    <form method="GET" action="{{ route('standard-subject-allocation.index') }}">

        <div class="ssa-card">

            <div class="ssa-filter">

                <div class="ssa-filter-group">
                    <label for="standard_id" class="ssa-filter-label">
                        Select Standard <span style="color:#dc2626;">*</span>
                    </label>
                    <select name="standard_id"
                            id="standard_id"
                            class="ssa-select">
                        <option value="">Select Standard</option>
                        @foreach($standards as $standard)
                            <option value="{{ $standard->id }}"
                                {{ (string)$selectedStandard === (string)$standard->id ? 'selected' : '' }}>
                                {{ $standard->standard_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="ssa-btn ssa-btn-primary">
                    Load Subjects
                </button>

            </div>

        </div>

    </form>


    {{-- =====================================================
         SUBJECTS TABLE
    ====================================================== --}}

    @if($selectedStandard && ($academicSubjects->count() || $skillSubjects->count()))

        <form method="POST" action="{{ route('standard-subject-allocation.save') }}">
            @csrf
            <input type="hidden" name="standard_id" value="{{ $selectedStandard }}">

            @php
                $maxRows = max($academicSubjects->count(), $skillSubjects->count());
            @endphp

            <div class="ssa-table-wrapper">

                <table class="ssa-table">

                    <thead>
                        <tr>
                            <th style="width:50%;">Academic Subjects</th>
                            <th style="width:50%;">Skill Subjects</th>
                        </tr>
                    </thead>

                    <tbody>

                        @for($i = 0; $i < $maxRows; $i++)

                            <tr>

                                {{-- ACADEMIC COLUMN --}}
                                <td class="col-academic" data-label="Academic Subjects">

                                    @if(isset($academicSubjects[$i]))

                                        @php
                                            $subject = $academicSubjects[$i];
                                            $isChecked = in_array($subject->id, $allocatedSubjects);
                                        @endphp

                                        <label class="subject-row">
                                            <input type="checkbox"
                                                   name="subjects[]"
                                                   value="{{ $subject->id }}"
                                                   {{ $isChecked ? 'checked' : '' }}>
                                            <label>{{ $subject->subject_name }}</label>
                                        </label>

                                    @else
                                        <span class="empty-cell">—</span>
                                    @endif

                                </td>


                                {{-- SKILL COLUMN --}}
                                <td data-label="Skill Subjects">

                                    @if(isset($skillSubjects[$i]))

                                        @php
                                            $subject = $skillSubjects[$i];
                                            $isChecked = in_array($subject->id, $allocatedSubjects);
                                        @endphp

                                        <label class="subject-row">
                                            <input type="checkbox"
                                                   name="subjects[]"
                                                   value="{{ $subject->id }}"
                                                   {{ $isChecked ? 'checked' : '' }}>
                                            <label>{{ $subject->subject_name }}</label>
                                        </label>

                                    @else
                                        <span class="empty-cell">—</span>
                                    @endif

                                </td>

                            </tr>

                        @endfor

                    </tbody>

                </table>

            </div>

            <div class="ssa-action-footer">
                <button type="submit" class="ssa-btn ssa-btn-primary">
                    Save Allocation
                </button>
            </div>

        </form>

    @elseif($selectedStandard)

        <div class="ssa-empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                 fill="none" stroke="#9CA3AF" stroke-width="1.5"
                 stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="7"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <div class="ssa-empty-title">No subjects found for this standard</div>
            <div class="ssa-empty-subtitle">
                Add academic or skill subjects to this standard from the Subject Master first.
            </div>
        </div>

    @else

        <div class="ssa-empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                 fill="none" stroke="#9CA3AF" stroke-width="1.5"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <div class="ssa-empty-title">Select a standard above</div>
            <div class="ssa-empty-subtitle">
                Choose a standard and click <strong>Load Subjects</strong> to begin.
            </div>
        </div>

    @endif

</div>

</x-app-layout>