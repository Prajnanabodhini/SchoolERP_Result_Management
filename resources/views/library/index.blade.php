<x-app-layout>

<style>
.lib-page, .lib-page * { font-family: Arial, sans-serif !important; font-size: 13px; box-sizing: border-box; }
.lib-page h2 { font-size: 21px !important; font-weight: 700 !important; color: #1D4ED8 !important; margin: 0; text-transform: uppercase; letter-spacing: 0.3px; }
.lib-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; }
.lib-card { background: #fff; border: 1px solid #d1d5db; border-radius: 8px; padding: 14px; box-shadow: 0 1px 3px rgba(0,0,0,.05); margin-bottom: 14px; }
.lib-alert { padding: 10px 14px; border-radius: 6px; margin-bottom: 14px; font-weight: 700; }
.lib-alert-success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.lib-alert-error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
.lib-summary { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 14px; }
.lib-summary-badge { padding: 6px 12px; border-radius: 4px; font-size: 13px; font-weight: 700; white-space: nowrap; }
.lib-badge-total    { background: #dbeafe; color: #1e40af; }
.lib-badge-issued   { background: #fef3c7; color: #92400e; }
.lib-badge-returned { background: #dcfce7; color: #166534; }
.lib-badge-overdue  { background: #fee2e2; color: #991b1b; }
.lib-filter { display: flex; align-items: flex-end; flex-wrap: wrap; gap: 12px; }
.lib-filter-group { display: flex; flex-direction: column; gap: 4px; }
.lib-filter-label { font-size: 11px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.3px; }
.lib-input, .lib-select { height: 34px; padding: 4px 10px; border: 1px solid #9ca3af; border-radius: 5px; background: #fff; color: #111827; font-size: 13px; font-weight: 600; outline: none; min-width: 180px; }
.lib-input:focus, .lib-select:focus { border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,.15); }
.lib-btn { display: inline-flex; align-items: center; justify-content: center; height: 34px; min-width: 110px; padding: 0 16px; border-radius: 5px; font-size: 13px; font-weight: 700; text-decoration: none; cursor: pointer; border: 1px solid transparent; white-space: nowrap; box-sizing: border-box; transition: background .15s; }
.lib-btn-primary { background: #16a34a; color: #fff; border-color: #15803d; }
.lib-btn-primary:hover { background: #15803d; }
.lib-btn-blue { background: #2563eb; color: #fff; border-color: #1d4ed8; }
.lib-btn-blue:hover { background: #1d4ed8; }
.lib-btn-gray { background: #6b7280; color: #fff; border-color: #4b5563; }
.lib-btn-gray:hover { background: #4b5563; }

/* SMALL ACTION BUTTONS — min-width:0 cancels the 110px from .lib-btn */
.lib-btn-sm {
    width: 60px;
    height: 38px;
    min-width: 60px;
    padding: 0;
    font-size: 12px;
    font-weight: 700;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    vertical-align: middle;
    box-sizing: border-box;
    border-radius: 4px;
    text-decoration: none;
    cursor: pointer;
    border: 1px solid transparent;
    white-space: nowrap;
    transition: background .15s;
}

.lib-table-wrap { background: #fff; border: 1px solid #d1d5db; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.05); }
.lib-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin: 0; font-size: 13px; }
.lib-table thead tr { background: #1e3a8a; color: #fff; }
.lib-table th { padding: 9px 6px; font-size: 11px; font-weight: 700; text-align: left; border: 1px solid #d1d5db; color: #fff; white-space: nowrap; text-transform: uppercase; letter-spacing: 0.3px; }
.lib-table th.center { text-align: center; }
.lib-table td { padding: 8px 6px; border: 1px solid #d1d5db; vertical-align: middle; color: #111827; overflow: hidden; text-transform: uppercase; }
.lib-table tbody tr:nth-child(even) { background: #f9fafb; }
.lib-table tbody tr:hover { background: #fef9c3; }
.lib-table td.center { text-align: center; }

.lib-nowrap { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%; display: block; }

.lib-status { display: inline-block; padding: 3px 9px; border-radius: 4px; font-size: 11px; font-weight: 700; letter-spacing: .3px; white-space: nowrap; text-transform: uppercase; }
.lib-status-issued   { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
.lib-status-returned { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.lib-status-overdue  { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

/* Type badges */
.lib-type-student { display: inline-block; padding: 3px 9px; border-radius: 4px; font-size: 11px; font-weight: 700; letter-spacing: .3px; white-space: nowrap; text-transform: uppercase;
                    background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
.lib-type-teacher { display: inline-block; padding: 3px 9px; border-radius: 4px; font-size: 11px; font-weight: 700; letter-spacing: .3px; white-space: nowrap; text-transform: uppercase;
                    background: #ede9fe; color: #5b21b6; border: 1px solid #c4b5fd; }

.lib-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 20px; text-align: center; background: #fff; border: 1px solid #d1d5db; border-radius: 8px; }
.lib-empty-title { font-size: 15px; font-weight: 700; color: #374151; margin-bottom: 4px; }
.lib-empty-subtitle { font-size: 13px; color: #6b7280; }
</style>


<div class="lib-page p-3">

    <div class="lib-header">
        <h2>Library — Book Issue / Return</h2>
        <a href="{{ route('library.create') }}" class="lib-btn lib-btn-primary">+ Issue Book</a>
    </div>

    @if(session('success'))
        <div class="lib-alert lib-alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="lib-alert lib-alert-error">{{ session('error') }}</div>
    @endif

    <div class="lib-summary">
        <span class="lib-summary-badge lib-badge-total">TOTAL : {{ $summary['total'] }}</span>
        <span class="lib-summary-badge lib-badge-issued">ISSUED : {{ $summary['issued'] }}</span>
        <span class="lib-summary-badge lib-badge-returned">RETURNED : {{ $summary['returned'] }}</span>
        <span class="lib-summary-badge lib-badge-overdue">OVERDUE : {{ $summary['overdue'] }}</span>
    </div>

    <div class="lib-card">
        <form method="GET" action="{{ route('library.index') }}">
            <div class="lib-filter">
                <div class="lib-filter-group">
                    <label class="lib-filter-label" for="search">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Book / Borrower / Class" class="lib-input" style="min-width:260px;">
                </div>
                <div class="lib-filter-group">
                    <label class="lib-filter-label" for="status">Status</label>
                    <select name="status" id="status" class="lib-select" style="min-width:150px;">
                        <option value="">All</option>
                        <option value="issued"   {{ request('status') === 'issued'   ? 'selected' : '' }}>Issued</option>
                        <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Returned</option>
                        <option value="overdue"  {{ request('status') === 'overdue'  ? 'selected' : '' }}>Overdue</option>
                    </select>
                </div>
                <div class="lib-filter-group">
                    <label class="lib-filter-label" for="borrower_type">Type</label>
                    <select name="borrower_type" id="borrower_type" class="lib-select" style="min-width:140px;">
                        <option value="">All</option>
                        <option value="student" {{ request('borrower_type') === 'student' ? 'selected' : '' }}>Student</option>
                        <option value="teacher" {{ request('borrower_type') === 'teacher' ? 'selected' : '' }}>Teacher</option>
                    </select>
                </div>
                <button type="submit" class="lib-btn lib-btn-blue">Search</button>
                <a href="{{ route('library.index') }}" class="lib-btn lib-btn-gray">Reset</a>
            </div>
        </form>
    </div>

    @if($issues->count())

        <div class="lib-table-wrap">
            <table class="lib-table">
                <colgroup>
                    <col style="width:4%;">    {{-- Sr.         --}}
                    <col style="width:14%;">   {{-- Book        --}}
                    <col style="width:16%;">   {{-- Borrower    --}}
                    <col style="width:9%;">    {{-- Type        --}}
                    <col style="width:12%;">   {{-- Class       --}}
                    <col style="width:8%;">    {{-- Issue Date  --}}
                    <col style="width:8%;">    {{-- Due Date    --}}
                    <col style="width:8%;">    {{-- Return Date --}}
                    <col style="width:9%;">    {{-- Status      --}}
                    <col style="width:12%;">   {{-- Action      --}}
                </colgroup>

                <thead>
                    <tr>
                        <th class="center">Sr.</th>
                        <th>Book</th>
                        <th>Borrower</th>
                        <th class="center">Type</th>
                        <th class="center">Class</th>
                        <th class="center">Issue Date</th>
                        <th class="center">Due Date</th>
                        <th class="center">Return Date</th>
                        <th class="center">Status</th>
                        <th class="center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($issues as $i => $issue)
                        @php
                            $rowNumber = ($issues->currentPage() - 1) * $issues->perPage() + $i + 1;

                            $statusClass = $issue->is_overdue
                                ? 'lib-status-overdue'
                                : ($issue->status === 'returned' ? 'lib-status-returned' : 'lib-status-issued');

                            $statusLabel = $issue->is_overdue
                                ? 'OVERDUE (' . $issue->days_overdue . 'd)'
                                : strtoupper($issue->status);

                            $isTeacher = strtolower((string) $issue->borrower_type) === 'teacher';
                        @endphp

                        <tr>
                            <td class="center" style="font-weight:700;">{{ $rowNumber }}</td>

                            {{-- BOOK --}}
                            <td>
                                <div class="lib-nowrap" style="font-weight:600;" title="{{ $issue->book_name }}">
                                    {{ $issue->book_name }}
                                </div>
                                @if($issue->book_code || $issue->author)
                                    <div class="lib-nowrap" style="font-size:11px;color:#6b7280;"
                                         title="{{ trim(($issue->book_code ?? '') . ' ' . ($issue->author ?? '')) }}">
                                        @if($issue->book_code){{ $issue->book_code }}@endif
                                        @if($issue->book_code && $issue->author) · @endif
                                        @if($issue->author){{ $issue->author }}@endif
                                    </div>
                                @endif
                            </td>

                            {{-- BORROWER --}}
                            <td>
                                <div class="lib-nowrap" style="font-weight:600;" title="{{ $issue->borrower_name }}">
                                    {{ $issue->borrower_name }}
                                </div>
                                <div class="lib-nowrap" style="font-size:11px;color:#6b7280;">
                                    ID: {{ $issue->borrower_id }}
                                </div>
                            </td>

                            {{-- TYPE --}}
                            <td class="center">
                                @if($isTeacher)
                                    <span class="lib-type-teacher">Teacher</span>
                                @else
                                    <span class="lib-type-student">Student</span>
                                @endif
                            </td>

                            {{-- CLASS --}}
                            <td class="center">
                                <div class="lib-nowrap" title="{{ $issue->borrower_class }}">
                                    {{ $issue->borrower_class ?: '-' }}
                                </div>
                            </td>

                            {{-- DATES --}}
                            <td class="center">
                                <div class="lib-nowrap">
                                    {{ $issue->issue_date ? $issue->issue_date->format('d-m-Y') : '-' }}
                                </div>
                            </td>
                            <td class="center">
                                <div class="lib-nowrap">
                                    {{ $issue->due_date ? $issue->due_date->format('d-m-Y') : '-' }}
                                </div>
                            </td>
                            <td class="center">
                                <div class="lib-nowrap">
                                    {{ $issue->return_date ? $issue->return_date->format('d-m-Y') : '-' }}
                                </div>
                            </td>

                            {{-- STATUS --}}
                            <td class="center">
                                <span class="lib-status {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>

                            {{-- ACTION --}}
                            <td class="center">
                                <div style="display:flex;justify-content:center;align-items:center;gap:6px;flex-wrap:nowrap;">

                                    @if($issue->status === 'issued')
                                        <form method="POST"
                                              action="{{ route('library.return', $issue->id) }}"
                                              onsubmit="return confirm('Mark this book as returned?');"
                                              style="display:inline-flex;margin:0;padding:0;vertical-align:middle;">
                                            @csrf
                                            <button type="submit" class="lib-btn lib-btn-primary lib-btn-sm">Return</button>
                                        </form>
                                    @endif

                                    <button type="button"
                                            onclick="window.location.href='{{ route('library.edit', $issue->id) }}';"
                                            class="lib-btn lib-btn-blue lib-btn-sm">Edit</button>

                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($issues->hasPages())
            <div style="margin-top:14px;display:flex;justify-content:center;">
                {{ $issues->onEachPage(3)->links() }}
            </div>
        @endif

    @else
        <div class="lib-empty">
            <div class="lib-empty-title">No library records found</div>
            <div class="lib-empty-subtitle">Click <strong>+ Issue Book</strong> to record the first book issue.</div>
        </div>
    @endif

</div>

</x-app-layout>