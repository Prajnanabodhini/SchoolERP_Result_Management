<x-app-layout>

<style>

.exam-master-page,
.exam-master-page * {
    font-family: Arial, sans-serif !important;
    font-size: 13px !important;
}

.exam-master-page h2 {
    font-size: 22px !important;
    font-weight: 700 !important;
    color: #1D4ED8 !important;
    margin: 0;
}

/* ==========================================================
   TABLE — styled like Teacher Bulk Allocation
   ========================================================== */

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
    color: #ffffff;
}

.exam-master-page table th {
    font-size: 13px !important;
    font-weight: 700 !important;
    text-align: left;
    padding: 8px 6px !important;
    border: 1px solid #d1d5db !important;
    color: #ffffff !important;
    white-space: nowrap;
    background: #1e3a8a;
}

.exam-master-page table td {
    font-size: 13px !important;
    padding: 7px 6px !important;
    border: 1px solid #d1d5db !important;
    vertical-align: middle;
    color: #111827;
    overflow-wrap: anywhere;
    word-break: break-word;
}

.exam-master-page table tbody tr:nth-child(even) {
    background: #f9fafb;
}

.exam-master-page table tbody tr:hover {
    background: #fef9c3;
}

/* ==========================================================
   BUTTONS
   ========================================================== */

.exam-master-page .erp-btn {
    font-size: 13px !important;
    padding: 5px 12px !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    border-radius: 5px;
    font-weight: 700;
    cursor: pointer;
    height: 34px;
    box-sizing: border-box;
}

.exam-master-page .erp-btn-add {
    background: #16A34A;
    color: #ffffff !important;
    border: 1px solid #15803D;
}
.exam-master-page .erp-btn-add:hover { background: #15803D; }

.exam-master-page .erp-btn-edit {
    flex: 0 0 auto;
    width: 85px;
    height: 30px;
    padding: 0 !important;
    font-size: 12px !important;
    background: #2563EB;
    color: #ffffff !important;
    border: 1px solid #1D4ED8;
}
.exam-master-page .erp-btn-edit:hover { background: #1D4ED8; }

.exam-master-page .erp-btn-delete {
    flex: 0 0 auto;
    min-width: 105px;
    height: 30px;
    padding: 0 10px !important;
    font-size: 12px !important;
    background: #DC2626;
    color: #ffffff !important;
    border: 1px solid #B91C1C;
}
.exam-master-page .erp-btn-delete:hover { background: #B91C1C; }

.exam-master-page .text-green-700,
.exam-master-page .text-red-700 {
    font-size: 13px !important;
    font-weight: 600 !important;
}

/* ==========================================================
   FILTER
   ========================================================== */

.exam-master-filter {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 15px;
}

.exam-master-filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.exam-master-filter-label {
    font-size: 12px !important;
    font-weight: 700 !important;
    color: #374151;
}

.exam-master-filter-select {
    height: 34px;
    min-width: 190px;
    padding: 4px 9px;
    font-size: 12px !important;
    font-weight: 600 !important;
    border: 1px solid #9CA3AF;
    border-radius: 5px;
    background: #ffffff;
    color: #111827;
}

.exam-master-filter-select:focus {
    outline: none;
    border-color: #2563EB;
    box-shadow: 0 0 0 1px #2563EB;
}

.exam-master-filter-button {
    height: 34px;
    padding: 5px 12px !important;
    background: #2563EB;
    color: #ffffff;
    border: 1px solid #1D4ED8;
}
.exam-master-filter-button:hover { background: #1D4ED8; }

.exam-master-clear-button {
    height: 34px;
    padding: 5px 12px !important;
    background: #6B7280;
    color: #ffffff !important;
    border: 1px solid #4B5563;
}
.exam-master-clear-button:hover { background: #4B5563; }

/* ==========================================================
   PASSING PERCENTAGE
   ========================================================== */

.passing-percentage-35 {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 4px;
    background: #DCFCE7;
    color: #166534;
    border: 1px solid #86EFAC;
    font-weight: 700;
    font-size: 12px !important;
}

.passing-percentage-40 {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 4px;
    background: #DBEAFE;
    color: #1E40AF;
    border: 1px solid #93C5FD;
    font-weight: 700;
    font-size: 12px !important;
}

/* ==========================================================
   ACTIVE / INACTIVE
   ========================================================== */

.active-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 4px;
    background: #DCFCE7;
    color: #166534;
    border: 1px solid #86EFAC;
    font-weight: 700;
    font-size: 12px !important;
}

.inactive-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 4px;
    background: #FEE2E2;
    color: #991B1B;
    border: 1px solid #FCA5A5;
    font-weight: 700;
    font-size: 12px !important;
}

/* ==========================================================
   TEACHER ALLOCATION STATUS
   ========================================================== */

.alloc-ready {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 8px;
    border-radius: 12px;
    background: #DCFCE7;
    color: #166534;
    border: 1px solid #86EFAC;
    font-weight: 700;
    font-size: 12px !important;
    text-decoration: none;
    white-space: nowrap;
}

.alloc-pending {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 8px;
    border-radius: 12px;
    background: #FEE2E2;
    color: #991B1B;
    border: 1px solid #FCA5A5;
    font-weight: 700;
    font-size: 12px !important;
    text-decoration: none;
    white-space: nowrap;
}

.alloc-pending:hover { background: #FECACA; }

.alloc-dot {
    display: inline-block;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    flex-shrink: 0;
}

.alloc-dot-green { background: #22C55E; }
.alloc-dot-red   { background: #EF4444; }

.alloc-not-configured {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 8px;
    border-radius: 12px;
    background: #FFEDD5;
    color: #9A3412;
    border: 1px solid #FDBA74;
    font-weight: 700;
    font-size: 12px !important;
    white-space: nowrap;
}

/* ==========================================================
   RESPONSIVE
   ========================================================== */

@media (max-width: 800px) {

    .exam-master-filter {
        flex-direction: column;
        align-items: stretch;
    }

    .exam-master-filter-group { width: 100%; }
    .exam-master-filter-select { width: 100%; }
    .exam-master-filter .erp-btn { width: 100%; }

}

</style>


<div class="max-w-7xl mx-auto py-4 px-4 exam-master-page">

    <div class="
        bg-gradient-to-br from-red-100 via-yellow-100 to-orange-100
        rounded-xl shadow-xl border-4 border-amber-400 p-5
    ">

        {{-- HEADER --}}
        <div class="flex justify-between items-center mb-4">
            <h2>Exam Master</h2>
            <a href="{{ route('exam-masters.create') }}" class="erp-btn erp-btn-add">
                + Add Exam
            </a>
        </div>


        {{-- FILTER --}}
        <form method="GET" action="{{ route('exam-masters.index') }}">
            <div class="exam-master-filter">

                <div class="exam-master-filter-group">
                    <label for="academic_year_id" class="exam-master-filter-label">
                        Academic Year
                    </label>

                    <select name="academic_year_id" id="academic_year_id" class="exam-master-filter-select">
                        <option value="">All Academic Years</option>

                        @foreach($academicYears as $academicYear)
                            <option
                                value="{{ $academicYear->id }}"
                                {{ (string)($academicYearId ?? request('academic_year_id')) === (string)$academicYear->id ? 'selected' : '' }}
                            >
                                {{ $academicYear->year_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="erp-btn exam-master-filter-button">
                    Filter
                </button>

                <a href="{{ route('exam-masters.index') }}" class="erp-btn exam-master-clear-button">
                    Clear
                </a>

            </div>
        </form>


        {{-- SUCCESS --}}
        @if(session('success'))
            <div class="bg-green-100 border border-green-300 text-green-700 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif


        {{-- ERROR --}}
        @if(session('error'))
            <div class="bg-red-100 border border-red-300 text-red-700 p-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif


        {{-- TABLE --}}
        <div style="width:100%;overflow:hidden;">

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
                        $standardId = (int) ($exam->standard_id ?? 0);

                        $standardName = strtoupper(trim(
                            (string) (optional($exam->standard)->standard_name ?? '')
                        ));

                        $normalizedStandardName = preg_replace('/[^A-Z0-9]+/', '', $standardName) ?? '';

                        $is35PercentStandard =
                            in_array($standardId, [9, 10, 11, 12], true)
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

                        <td style="text-align:center;font-weight:700;">
                            {{ $exam->id }}
                        </td>

                        <td style="text-align:center;">
                            {{ $exam->academicYear->year_name ?? '-' }}
                        </td>

                        <td style="font-weight:600;">
                            {{ $exam->exam_name }}
                        </td>

                        <td style="text-align:center;">
                            {{ $exam->standard_id }}
                        </td>

                        <td style="font-weight:600;">
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
                                    <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#F97316;"></span>
                                    NO SUBJECTS
                                </span>
                            @elseif($pending > 0)
                                <a
                                    href="{{ route('teacher-bulk-allocation.index', ['exam_master_id' => $exam->id]) }}"
                                    class="alloc-pending"
                                    title="Click to allocate · {{ $tooltip }}"
                                >
                                    <span class="alloc-dot alloc-dot-red"></span>
                                    {{ $pending }} PENDING
                                </a>

                                @if(!empty($breakdown))
                                    <div style="margin-top:4px;font-size:10px;color:#7f1d1d;font-weight:600;line-height:1.3;">
                                        @foreach($breakdown as $divName => $count)
                                            <span style="display:inline-block;background:#FEE2E2;border-radius:3px;padding:0 4px;margin:1px 1px 0 0;">
                                                {{ $divName }}: {{ $count }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            @else
                                <span class="alloc-ready">
                                    <span class="alloc-dot alloc-dot-green"></span>
                                    READY
                                </span>
                            @endif
                        </td>

                        <td style="text-align:center;">
                            @if($exam->is_active)
                                <span class="active-badge">ACTIVE</span>
                            @else
                                <span class="inactive-badge">INACTIVE</span>
                            @endif
                        </td>

                        <td style="text-align:center;white-space:nowrap;">
                            <div style="display:flex;justify-content:center;align-items:center;gap:5px;flex-wrap:wrap;">

                                <a href="{{ route('exam-masters.edit', $exam->id) }}"
   class="erp-btn erp-btn-edit"
   style="width:140px;min-width:140px;flex:0 0 50px;height:30px;padding:0;">
    Edit
</a>

                                <form
                                    action="{{ route('exam-masters.destroy', $exam->id) }}"
                                    method="POST"
                                    style="display:inline-block;margin:0;padding:0;"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="erp-btn erp-btn-delete"
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
                        <td colspan="9" style="padding:25px 10px;text-align:center;color:#6b7280;">
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
</script>

</x-app-layout>