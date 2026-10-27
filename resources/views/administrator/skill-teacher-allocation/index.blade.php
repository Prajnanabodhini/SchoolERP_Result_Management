<x-app-layout>

<style>
    /* Base Reset & Variables */
    .exam-progress-page, .exam-progress-page * { 
        box-sizing: border-box; 
        font-family: 'Inter', system-ui, -apple-system, sans-serif !important; 
    }
    
    .exam-progress-page { 
        width: 100%; 
        max-width: 100%; 
        background: #f8fafc; 
        padding: 12px; 
        overflow-x: hidden; 
        color: #1f2937;
    }

    .exam-progress-page h2 { 
        margin: 0 0 10px !important; 
        font-size: 18px !important; 
        font-weight: 700 !important; 
        color: #1e3a8a !important; 
        letter-spacing: -0.3px; 
    }

    .exam-progress-page .erp-card { 
        width: 100%; 
        max-width: 100%; 
        background: #ffffff !important; 
        border: 1px solid #cbd5e1 !important;
        border-radius: 6px !important; 
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important; 
        padding: 10px !important; 
        margin-bottom: 12px; 
        overflow: hidden; 
    }

    /* Tabs */
    .tabs-bar { 
        display: flex; 
        gap: 6px; 
        margin-bottom: 10px; 
        padding-bottom: 8px; 
        border-bottom: 1px solid #cbd5e1; 
        flex-wrap: wrap; 
    }
    .tab { 
        display: inline-flex; 
        align-items: center; 
        justify-content: center; 
        padding: 6px 14px; 
        font-size: 11px; 
        font-weight: 600; 
        text-transform: uppercase; 
        letter-spacing: 0.3px; 
        text-decoration: none; 
        color: #4b5563; 
        background: #f8fafc; 
        border: 1px solid #cbd5e1; 
        border-radius: 4px; 
        cursor: pointer; 
        transition: all 0.2s ease; 
    }
    .tab:hover { 
        background: #f1f5f9; 
        color: #1e40af; 
        border-color: #94a3b8; 
    }
    .tab.active { 
        background: #1e3a8a; 
        color: #ffffff; 
        border-color: #1e3a8a; 
        box-shadow: 0 1px 2px rgba(30, 58, 138, 0.2); 
    }

    /* Filters */
    .filter-row { 
        display: flex; 
        align-items: flex-end; 
        gap: 8px; 
        width: 100%; 
        flex-wrap: wrap; 
    }
    .filter-group { 
        display: flex; 
        flex-direction: column; 
        gap: 3px; 
        min-width: 0; 
        flex: 0 1 auto; 
    }
    .filter-label { 
        font-size: 10px !important; 
        font-weight: 700 !important; 
        color: #475569; 
        text-transform: uppercase; 
        letter-spacing: 0.3px; 
        white-space: nowrap; 
        padding: 0; 
        margin: 0; 
    }
    .select-wrapper { 
        position: relative; 
        display: block; 
        width: 100%; 
    }
    .filter-select { 
        height: 30px; 
        width: 100%; 
        padding: 2px 24px 2px 8px; 
        font-size: 12px !important; 
        font-weight: 500; 
        border: 1px solid #cbd5e1; 
        border-radius: 4px; 
        background-color: #ffffff; 
        color: #111827; 
        cursor: pointer; 
        appearance: none; 
        -webkit-appearance: none; 
        outline: none; 
        transition: border-color 0.15s, box-shadow 0.15s; 
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'/%3e%3c/svg%3e"); 
        background-repeat: no-repeat; 
        background-position: right 8px center; 
        background-size: 10px; 
    }
    .filter-select:focus { 
        border-color: #3b82f6; 
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1); 
    }
    
    .filter-group.w-academic-year { width: 130px; } 
    .filter-group.w-standard { width: 140px; } 
    .filter-group.w-division { width: 90px; } 
    .filter-group.w-exam { width: 200px; }
    
    .filter-group.filter-group-btn { 
        flex: 0 0 auto; 
        margin-left: 0; 
    }

    /* Unified Button Styling */
    .sys-btn {
        height: 30px !important;
        padding: 0 12px !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        border-radius: 4px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        white-space: nowrap !important;
        text-decoration: none !important;
        transition: all 0.15s ease !important;
        box-sizing: border-box !important;
        border: 1px solid transparent !important;
        min-width: 80px !important;
        cursor: pointer;
        line-height: 1;
        text-overflow: clip;
        overflow: visible;
    }

    .sys-btn:hover:not(:disabled) { 
        filter: brightness(0.95);
    }
    .sys-btn:active:not(:disabled) { 
        transform: translateY(1px); 
    }

    /* Button Colors */
    .btn-primary { background: #2563eb !important; color: #ffffff !important; border-color: #1d4ed8 !important; }
    .btn-success { background: #16a34a !important; color: #ffffff !important; border-color: #15803d !important; }
    .btn-secondary { background: #f1f5f9 !important; color: #334155 !important; border-color: #cbd5e1 !important; }
    .btn-secondary:hover:not(:disabled) { background: #e2e8f0 !important; }
    .btn-danger { background: #dc2626 !important; color: #ffffff !important; border-color: #b91c1c !important; }

    /* PERFECTLY ALIGNED ACTION BUTTONS (Matches screenshot) */
    .action-btn {
        height: 28px !important;      /* Identical height */
        width: 68px !important;       /* Identical fixed width */
        padding: 0 !important;        /* Remove padding to rely on flex centering */
        font-size: 12px !important;   /* Clear text visibility */
        margin: 0 !important; 
        border-radius: 4px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        box-sizing: border-box !important;
    }

    .sys-btn:disabled { 
        background: #f1f5f9 !important; 
        color: #94a3b8 !important;
        border-color: #cbd5e1 !important; 
        cursor: not-allowed !important; 
        transform: none !important;
    }

    /* Badges */
    .summary-row { margin-top: 10px; display: flex; gap: 6px; flex-wrap: wrap; }
    .summary-badge { 
        display: inline-flex; 
        align-items: center; 
        padding: 3px 8px; 
        border-radius: 4px; 
        font-size: 10px !important; 
        font-weight: 700; 
        letter-spacing: 0.3px; 
        text-transform: uppercase; 
        border: 1px solid transparent; 
        white-space: nowrap; 
    }
    .completed-badge { background: #f0fdf4; color: #166534; border-color: #86efac; } 
    .pending-badge { background: #fffbeb; color: #92400e; border-color: #fcd34d; } 
    .total-badge { background: #eff6ff; color: #1e40af; border-color: #93c5fd; }

    /* Table */
    .result-table-wrapper { 
        width: 100%; 
        max-width: 100%; 
        overflow-x: auto; 
        border: 1px solid #cbd5e1; 
        border-radius: 4px; 
        background: #fff; 
    }
    .result-table { 
        width: 100%; 
        border-collapse: collapse; 
        background: #ffffff; 
        font-size: 12px !important; 
        table-layout: fixed; 
    }
    
    /* Darker table borders */
    .result-table th { 
        background: #f1f5f9; 
        color: #334155 !important; 
        font-weight: 600 !important; 
        font-size: 11px !important; 
        text-transform: uppercase; 
        letter-spacing: 0.3px; 
        border: 1px solid #cbd5e1; 
        border-bottom: 2px solid #94a3b8; 
        padding: 6px 8px; 
        white-space: nowrap; 
        text-align: left; 
    }
    .result-table td { 
        border: 1px solid #cbd5e1; 
        padding: 6px 8px; 
        white-space: nowrap; 
        font-size: 12px; 
        color: #1f2937; 
        vertical-align: middle; 
        line-height: 1.3; 
        overflow: hidden; 
        text-overflow: ellipsis; 
    }
    .result-table td.subject-name { white-space: normal !important; word-break: break-word; }
    .result-table tbody tr:hover { background: #f8fafc; }
    
    .result-table th.left, .result-table td.left { text-align: left; } 
    .result-table th.center, .result-table td.center { text-align: center; }
    
    .subject-name { font-weight: 600; color: #111827; } 
    
    .status-badge { 
        display: inline-block; 
        padding: 2px 6px; 
        border-radius: 12px; 
        font-size: 10px !important; 
        font-weight: 700; 
        letter-spacing: 0.3px; 
        text-transform: uppercase; 
        border: 1px solid transparent; 
        white-space: nowrap; 
    }
    .status-completed { background: #f0fdf4; color: #166534; border-color: #86efac; } 
    .status-pending { background: #fffbeb; color: #92400e; border-color: #fcd34d; } 
    .status-other { background: #f1f5f9; color: #334155; border-color: #cbd5e1; }

    .no-record { 
        padding: 20px 12px; 
        text-align: center; 
        color: #92400e; 
        background: #fffbeb; 
        font-weight: 500; 
        font-size: 12px; 
        border: 1px solid #fcd34d;
    }

    /* Responsive */
    @media (max-width: 1100px) { 
        .filter-group.w-academic-year { width: 120px; } 
        .filter-group.w-standard { width: 130px; } 
        .filter-group.w-division { width: 80px; } 
        .filter-group.w-exam { width: 180px; } 
    }
    @media (max-width: 900px) { 
        .filter-row { gap: 6px; } 
        .filter-group.w-academic-year, .filter-group.w-standard, .filter-group.w-division, .filter-group.w-exam { flex: 1 1 130px; width: auto; } 
    }
    @media (max-width: 700px) { 
        .exam-progress-page { padding: 8px; } 
        .tabs-bar { flex-direction: column; } 
        .tab { justify-content: center; width: 100%; } 
        .filter-row { flex-direction: column; align-items: stretch; } 
        .filter-group, .filter-group.w-academic-year, .filter-group.w-standard, .filter-group.w-division, .filter-group.w-exam, .filter-group.filter-group-btn { width: 100%; flex: 1 1 100%; margin-left: 0; } 
        .sys-btn { width: 100%; } 
        .action-btn { width: 68px !important; } /* Keep action buttons exact on mobile */
        .result-table th.col-standard, .result-table td.col-standard, .result-table th.col-division, .result-table td.col-division, .result-table th.col-teacher, .result-table td.col-teacher { display: none; } 
    }
</style>

<div class="exam-progress-page">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 10px;">
        <h2 style="margin: 0 !important;">Skill Teacher Allocation</h2>
        
        @if(($subjectType ?? 'grade') === 'grade')
            <a href="{{ route('skill-teacher-allocation.create') }}" class="sys-btn btn-primary">
                + New Allocation
            </a>
        @endif
    </div>

    @php
        $activeTab = $subjectType ?? 'grade';
        $cleanParams = [];
        if (!empty($academicYearId))  $cleanParams['academic_year_id'] = $academicYearId;
        if (!empty($standardId))      $cleanParams['standard_id']      = $standardId;
        if (!empty($divisionId))      $cleanParams['division_id']      = $divisionId;
        if (!empty($examMasterId))    $cleanParams['exam_master_id']   = $examMasterId;

        $gradeTabUrl = route('skill-teacher-allocation.index', array_merge($cleanParams, ['subject_type' => 'grade']));
        $markTabUrl  = route('skill-teacher-allocation.index', array_merge($cleanParams, ['subject_type' => 'mark']));
    @endphp

    <div class="tabs-bar">
        <a href="{{ $gradeTabUrl }}" class="tab {{ $activeTab === 'grade' ? 'active' : '' }}">Skill Grade Allocations</a>
        <a href="{{ $markTabUrl }}"  class="tab {{ $activeTab === 'mark'  ? 'active' : '' }}">Skill Mark Allocations (S1-S4)</a>
    </div>

    @if(session('success'))
        <div style="background:#f0fdf4;color:#166534;border:1px solid #86efac;padding:6px 10px;border-radius:4px;margin-bottom:10px;font-weight:600;font-size:12px;display:flex;align-items:center;gap:6px;">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;padding:6px 10px;border-radius:4px;margin-bottom:10px;font-weight:600;font-size:12px;display:flex;align-items:center;gap:6px;">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="erp-card">
        <form method="GET" action="{{ route('skill-teacher-allocation.index') }}" id="skillAllocationFilterForm">
            <input type="hidden" name="subject_type" value="{{ $activeTab }}">

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
                                <option value="{{ $exam->id }}" {{ (string)$examMasterId === (string)$exam->id ? 'selected' : '' }}>{{ $exam->display_exam_name ?? $exam->exam_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="filter-group filter-group-btn">
                    <label class="filter-label" style="visibility:hidden;">.</label>
                    <button type="submit" class="sys-btn btn-success">Filter</button>
                </div>

                @if(($examMasterId !== null && $examMasterId !== '') || ($standardId !== null && $standardId !== '') || ($divisionId !== null && $divisionId !== '') || ($academicYearId !== null && $academicYearId !== ''))
                    <div class="filter-group filter-group-btn">
                        <label class="filter-label" style="visibility:hidden;">.</label>
                        <a href="{{ route('skill-teacher-allocation.index', ['subject_type' => $activeTab]) }}" class="sys-btn btn-secondary">Reset</a>
                    </div>
                @endif
            </div>
        </form>

        @if($activeTab === 'mark')
            <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #cbd5e1;">
                <form action="{{ route('skill-teacher-allocation.generate-skill-marks') }}" method="POST" style="display: inline-block;">
                    @csrf
                    <input type="hidden" name="academic_year_id" value="{{ $academicYearId }}">
                    <input type="hidden" name="exam_master_id" value="{{ $examMasterId }}">
                    <input type="hidden" name="standard_id" value="{{ $standardId }}">
                    <button type="submit" class="sys-btn btn-primary" style="min-width: 200px; padding: 0 12px !important;"
                            {{ ($academicYearId === '' || $examMasterId === '' || $isAllocationGenerated || !$canGenerateAllocation) ? 'disabled' : '' }}>
                        @if($isAllocationGenerated)
                            Allocations Generated
                        @else
                            Generate & Save S1-S4 Allocations
                        @endif
                    </button>
                </form>
                @if($academicYearId === '' || $examMasterId === '')
                    <span style="font-size: 11px; color: #dc2626; margin-left: 10px; font-weight: 600;">* Select Academic Year and Exam to enable</span>
                @elseif(!$canGenerateAllocation)
                    <span style="font-size: 11px; color: #dc2626; margin-left: 10px; font-weight: 600;">* Allocations can only be generated for Term 1 & Term 2 exams</span>
                @endif
            </div>
        @endif

        <div class="summary-row">
            <span class="summary-badge completed-badge">Completed : {{ $completedCount }}</span>
            <span class="summary-badge pending-badge">Pending : {{ $pendingCount }}</span>
            <span class="summary-badge total-badge">Total : {{ $totalCount }}</span>
        </div>
    </div>

    <div class="erp-card" style="padding: 0 !important;">
        <div class="result-table-wrapper" style="border: none; border-radius: 6px;">
            <table class="result-table">
                <colgroup>
                    @if($activeTab === 'mark')
                        <col style="width:5%;">    {{-- ID --}}
                        <col style="width:22%;">   {{-- Exam --}}
                        <col style="width:12%;">   {{-- Standard --}}
                        <col style="width:7%;">    {{-- Division --}}
                        <col style="width:43%;">   {{-- Slot --}}
                        <col style="width:11%;">   {{-- Status --}}
                    @else
                        <col style="width:5%;">    {{-- ID --}}
                        <col style="width:15%;">   {{-- Exam --}}
                        <col style="width:15%;">   {{-- Teacher --}}
                        <col style="width:10%;">   {{-- Year --}}
                        <col style="width:8%;">    {{-- Standard --}}
                        <col style="width:7%;">    {{-- Div --}}
                        <col style="width:18%;">   {{-- Subjects --}}
                        <col style="width:22%;">   {{-- Actions --}}
                    @endif
                </colgroup>

                <thead>
                    <tr>
                        <th class="center">ID</th>
                        <th class="left">Exam</th>
                        
                        {{-- HEADERS FOR MARK TAB --}}
                        @if($activeTab === 'mark')
                            <th class="left">Standard</th>
                            <th class="center col-division">Division</th>
                            <th class="left">Slot</th>
                            <th class="center">Status</th>
                        @else
                        {{-- HEADERS FOR GRADE TAB --}}
                            <th class="left">Teacher</th>
                            <th class="left col-standard">Year</th>
                            <th class="center col-division">Standard</th>
                            <th class="center">Div</th>
                            <th class="left">Skill Subjects</th>
                            <th class="center">Actions</th>
                        @endif
                    </tr>
                </thead>

                <tbody>
                @if($activeTab === 'grade')
                    @forelse($allocations as $allocation)
                        @php
                            $subjectNames = $allocation->subjectAllocations->map(fn ($item) => optional($item->subject)->subject_name)->filter()->unique()->values();
                            $examNames = $allocation->subjectAllocations->map(fn ($item) => optional($item->exam)->exam_name)->filter()->unique()->values();
                        @endphp
                        <tr>
                            <td class="center" style="font-weight:700;color:#1e3a8a;">{{ $allocation->id }}</td>
                            <td class="left" title="{{ $examNames->implode(', ') }}">{{ $examNames->implode(', ') ?: '-' }}</td>
                            <td class="left" style="font-weight:600;">{{ optional($allocation->teacher)->name ?? '-' }}</td>
                            <td class="left col-standard">{{ optional($allocation->academicYear)->year_name ?? '-' }}</td>
                            <td class="center col-division">{{ optional($allocation->standard)->standard_name ?? '-' }}</td>
                            <td class="center">
                                @if($allocation->division)
                                    <span style="display:inline-block;background:#f0fdf4;color:#166534;border:1px solid #86efac;border-radius:3px;padding:1px 6px;font-size:10px;font-weight:700;">{{ $allocation->division->division_name }}</span>
                                @else - @endif
                            </td>
                            <td class="left subject-name">
                                @foreach($subjectNames as $subjectName)
                                    <span style="display:inline-block;background:#eff6ff;color:#1d4ed8;border:1px solid #93c5fd;border-radius:3px;padding:1px 6px;font-size:10px;font-weight:600;margin:1px;">{{ $subjectName }}</span>
                                @endforeach
                            </td>
                            <td class="center">
                                <!-- Exact match to screenshot -->
                                <div style="display:flex; justify-content:center; align-items:center; gap:6px;">
                                    <a href="{{ route('skill-teacher-allocation.edit', $allocation->id) }}" class="sys-btn btn-primary action-btn">Edit</a>
                                    <form method="POST" action="{{ route('skill-teacher-allocation.destroy', $allocation->id) }}" onsubmit="return confirm('Are you sure you want to delete this allocation?');" style="margin:0; padding:0; display:flex;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="sys-btn btn-danger action-btn">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="no-record">No skill grade allocations found.</td></tr>
                    @endforelse
                @else
                    @forelse($slotAllocations as $slot)
                        <tr>
                            <td class="center" style="font-weight:700;color:#1e3a8a;">{{ $slot->id }}</td>
                            <td class="left">{{ $slot->exam_name }}</td>
                            <td class="left">{{ $slot->standard_name }}</td>
                            <td class="center">
                                <span style="display:inline-block;background:#f0fdf4;color:#166534;border:1px solid #86efac;border-radius:3px;padding:1px 6px;font-size:10px;font-weight:700;">{{ $slot->division_name }}</span>
                            </td>
                            <td class="left subject-name" style="font-weight:500;">{{ $slot->full_slot_name }}</td>
                            <td class="center">
                                @if($slot->status === 'COMPLETED')
                                    <span class="status-badge status-completed">Completed</span>
                                @else
                                    <span class="status-badge status-pending">Pending</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="no-record">No skill mark allocations generated yet.</td></tr>
                    @endforelse
                @endif
                </tbody>
            </table>
        </div>

        @if($activeTab === 'grade' && $allocations->hasPages())
            <div style="padding: 8px; border-top: 1px solid #cbd5e1; display:flex; justify-content:center;">
                {{ $allocations->onEachSide(5)->links() }}
            </div>
        @elseif($activeTab === 'mark' && $slotAllocations->hasPages())
            <div style="padding: 8px; border-top: 1px solid #cbd5e1; display:flex; justify-content:center;">
                {{ $slotAllocations->onEachSide(5)->links() }}
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('skillAllocationFilterForm');
    const academicYearSelect = document.getElementById('academic_year_id');
    const standardSelect     = document.getElementById('standard_id');
    const divisionSelect     = document.getElementById('division_id');
    const examSelect         = document.getElementById('exam_master_id');
    
    if (!form) return;
    
    function resetExamAndSubmit() { 
        if (examSelect) examSelect.value = ''; 
        form.submit(); 
    }
    
    if (academicYearSelect) academicYearSelect.addEventListener('change', resetExamAndSubmit);
    if (standardSelect)     standardSelect.addEventListener('change', resetExamAndSubmit);
    if (divisionSelect)     divisionSelect.addEventListener('change', resetExamAndSubmit);
    if (examSelect)         examSelect.addEventListener('change', function () { form.submit(); });
});
</script>
</x-app-layout>