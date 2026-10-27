<x-app-layout>

<style>
/* =========================================================
   PAGE
========================================================= */
.students-page,
.students-page * {
    box-sizing: border-box;
    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 13px;
}

.students-page {
    background: #f0f0f0;
    padding: 20px;
}

/* =========================================================
   CARD
========================================================= */
.students-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    padding: 18px 20px;
    max-width: 100%;
}

/* =========================================================
   PAGE TITLE
========================================================= */
.students-page h2 {
    margin: 0 0 14px !important;
    font-size: 21px !important;
    font-weight: 700 !important;
    color: #1D4ED8 !important;
    letter-spacing: 0.4px;
}

/* =========================================================
   FILTER ROW
========================================================= */
.students-filter-row {
    display: flex;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 16px;
}

.students-filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.students-filter-label {
    font-size: 11px;
    font-weight: 700;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    white-space: nowrap;
}

/* =========================================================
   SELECT WRAPPER
========================================================= */
.students-select-wrapper {
    position: relative;
    display: inline-block;
}

/* =========================================================
   SELECT
========================================================= */
.students-select {
    height: 34px;
    padding: 4px 28px 4px 10px;
    font-size: 13px;
    font-weight: 600;
    border: 1px solid #9CA3AF;
    border-radius: 6px;
    background-color: #ffffff;
    color: #111827;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    outline: none;
    transition: border-color .15s, box-shadow .15s;
}

.students-select:focus {
    border-color: #2563EB;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
}

/* Widths */
.students-select-year      { width: 170px; min-width: 170px; }
.students-select-standard  { width: 200px; min-width: 200px; }
.students-select-division  { width: 150px; min-width: 150px; }

/* =========================================================
   DROPDOWN ARROW
========================================================= */
.students-select-arrow {
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
   SEARCH BUTTON
========================================================= */
.students-search-btn {
    height: 34px;
    min-width: 130px;
    padding: 0 18px;
    font-size: 13px;
    font-weight: 700;
    border-radius: 6px;
    background: #16A34A;
    color: #ffffff;
    border: 1px solid #15803D;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    white-space: nowrap;
    transition: background .15s, border-color .15s;
}
.students-search-btn:hover {
    background: #15803D;
    border-color: #166534;
}

/* =========================================================
   TOTAL STUDENTS BADGE
========================================================= */
.students-count-badge {
    margin-left: auto;
    display: inline-flex;
    align-items: center;
    height: 34px;
    padding: 0 14px;
    background: #dbeafe;
    color: #1e40af;
    border: 1px solid #93c5fd;
    border-radius: 6px;
    font-size: 12.5px;
    font-weight: 700;
    letter-spacing: 0.2px;
    white-space: nowrap;
}

/* =========================================================
   TABLE WRAPPER
========================================================= */
.students-table-wrapper {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: #fff;
}

/* =========================================================
   TABLE
========================================================= */
.students-table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    font-size: 13px;
}

.students-table thead tr {
    background: #1e3a8a;
}

.students-table th {
    background: #1e3a8a;
    color: #ffffff !important;
    font-weight: 700;
    font-size: 11.5px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    padding: 10px 10px;
    text-align: left;
    white-space: nowrap;
    border: 1px solid #1e40af;
}

.students-table td {
    padding: 10px 10px;
    border: 1px solid #d1d5db;
    color: #111827;
    font-size: 13px;
    line-height: 1.35;
    vertical-align: middle;
    white-space: nowrap;
}

/* Zebra striping */
.students-table tbody tr:nth-child(even) {
    background: #f9fafb;
}

/* Row hover */
.students-table tbody tr:hover {
    background: #fef9c3;
}

/* Center helpers */
.students-table .center {
    text-align: center;
}

/* Student name emphasis */
.students-table .student-name {
    font-weight: 700;
    color: #111827;
}

/* Admission / roll number emphasis */
.students-table .student-code {
    font-weight: 700;
    color: #1e3a8a;
}

/* =========================================================
   STATUS
========================================================= */
.status-active {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 5px;
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: 0.3px;
    text-transform: uppercase;
    background: #DCFCE7;
    color: #166534;
    border: 1px solid #86EFAC;
}

.status-left {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 5px;
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: 0.3px;
    text-transform: uppercase;
    background: #FEE2E2;
    color: #991B1B;
    border: 1px solid #FCA5A5;
}

/* =========================================================
   NO RECORD
========================================================= */
.students-no-record {
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
    .students-page { padding: 12px; }
    .students-card { padding: 14px; }

    .students-filter-row {
        flex-direction: column;
        align-items: stretch;
    }

    .students-filter-group,
    .students-select,
    .students-select-year,
    .students-select-standard,
    .students-select-division {
        width: 100% !important;
        min-width: 100% !important;
    }

    .students-search-btn {
        width: 100%;
    }

    .students-count-badge {
        margin-left: 0;
        align-self: flex-start;
    }
}
</style>


<div class="students-page">

    <div class="students-card">

        <h2>Student Master</h2>

        {{-- ============================= --}}
        {{-- Filters                        --}}
        {{-- ============================= --}}
        <form method="GET" action="{{ route('students.index') }}">

            <div class="students-filter-row">

                <div class="students-filter-group">
                    <label class="students-filter-label">
                        Academic Year
                    </label>
                    <div class="students-select-wrapper">
                        <select name="academic_year_id"
                                class="students-select students-select-year">
                            <option value="">Select Year</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}"
                                    {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                    {{ $year->year_name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="students-select-arrow"></span>
                    </div>
                </div>

                <div class="students-filter-group">
                    <label class="students-filter-label">
                        Standard
                    </label>
                    <div class="students-select-wrapper">
                        <select name="standard_id"
                                class="students-select students-select-standard">
                            <option value="">Select Standard</option>
                            @foreach($standards as $standard)
                                <option value="{{ $standard->id }}"
                                    {{ request('standard_id') == $standard->id ? 'selected' : '' }}>
                                    {{ $standard->standard_name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="students-select-arrow"></span>
                    </div>
                </div>

                <div class="students-filter-group">
                    <label class="students-filter-label">
                        Division
                    </label>
                    <div class="students-select-wrapper">
                        <select name="division_id"
                                class="students-select students-select-division">
                            <option value="">Select Division</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}"
                                    {{ request('division_id') == $division->id ? 'selected' : '' }}>
                                    {{ $division->division_name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="students-select-arrow"></span>
                    </div>
                </div>

                <div class="students-filter-group">
                    <button type="submit" class="students-search-btn">
                        Search
                    </button>
                </div>

                @if($students->count())
                    <span class="students-count-badge">
                        Total Students : {{ $students->count() }}
                    </span>
                @endif

            </div>

        </form>

        {{-- ============================= --}}
        {{-- Student Table                  --}}
        {{-- ============================= --}}
        <div class="students-table-wrapper">

            <table class="students-table">

                <thead>
                    <tr>
                        <th>Admission No</th>
                        <th>Roll No</th>
                        <th>Student Name</th>
                        <th>Caste</th>
                        <th>Mobile</th>
                        <th class="center">Date of Birth</th>
                        <th class="center">Admission Date</th>
                        <th class="center">Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($students as $student)

                        <tr>

                            <td class="student-code">
                                {{ $student->regno }}
                            </td>

                            <td class="student-code">
                                {{ $student->rollno }}
                            </td>

                            <td class="student-name">
                                {{ $student->full_name }}
                            </td>

                            {{-- Caste --}}
                            <td>
                                {{ trim($student->caste ?? '') !== '' ? trim($student->caste) : '-' }}
                            </td>

                            <td>
                                {{ $student->fathermobile ?? '-' }}
                            </td>

                            {{-- Date of Birth --}}
                            <td class="center">
                                @php
                                    $dob = trim($student->birthdate ?? '');
                                    $dobFormatted = $dob;

                                    try {
                                        if (str_contains($dob, '/')) {
                                            $dobFormatted = \Carbon\Carbon::createFromFormat('d/m/Y', $dob)->format('d-m-Y');
                                        } elseif (str_contains($dob, '-')) {
                                            $dobFormatted = \Carbon\Carbon::createFromFormat('d-m-Y', $dob)->format('d-m-Y');
                                        }
                                    } catch (\Exception $e) {
                                        $dobFormatted = $dob;
                                    }
                                @endphp
                                {{ $dobFormatted }}
                            </td>

                            {{-- Admission Date --}}
                            <td class="center">
                                @if(!empty($student->admitdate))
                                    @php
                                        $admit = trim($student->admitdate);
                                        $admitFormatted = $admit;

                                        try {
                                            if (str_contains($admit, '/')) {
                                                $admitFormatted = \Carbon\Carbon::createFromFormat('d/m/Y', $admit)->format('d-m-Y');
                                            } elseif (str_contains($admit, '-')) {
                                                $admitFormatted = \Carbon\Carbon::createFromFormat('d-m-Y', $admit)->format('d-m-Y');
                                            }
                                        } catch (\Exception $e) {
                                            $admitFormatted = $admit;
                                        }
                                    @endphp
                                    {{ $admitFormatted }}
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="center">
                                @if(strtoupper(trim($student->status ?? 'ACTIVE')) === 'ACTIVE')
                                    <span class="status-active">Active</span>
                                @else
                                    <span class="status-left">Left</span>
                                @endif
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="students-no-record">
                                No Students Found
                            </td>
                        </tr>

                    @endforelse
                </tbody>

            </table>

        </div>

    </div>

</div>

</x-app-layout>