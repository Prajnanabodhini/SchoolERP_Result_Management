<x-app-layout>

<div style="width:100%;max-width:100%;margin:0;padding:12px;box-sizing:border-box;">

    <div style="width:100%;background:white;border-radius:10px;padding:14px;border:1px solid #d1d5db;box-shadow:0 3px 8px rgba(0,0,0,.12);box-sizing:border-box;">

        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
            <h2 style="margin:0;color:#1d4ed8;font-size:21px;font-weight:bold;">
                Teacher Bulk Allocation
            </h2>
        </div>

        @if(session('success'))
            <div style="background:#dcfce7;color:#166534;border:1px solid #86efac;padding:9px 12px;border-radius:6px;margin-bottom:12px;font-weight:bold;font-size:13px;">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;padding:9px 12px;border-radius:6px;margin-bottom:12px;font-weight:bold;font-size:13px;">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;padding:9px 12px;border-radius:6px;margin-bottom:12px;font-size:13px;">
                <strong>Please correct the following:</strong>
                <ul style="margin:5px 0 0 18px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $backToAllExamsUrl = route('teacher-bulk-allocation.index', array_filter([
                'academic_year_id' => $academicYearId,
                'standard_id'      => $standardId,
                'division_id'      => $divisionId,
            ]));
        @endphp

        @if(!empty($pendingSummary) && count($pendingSummary) > 0)

            <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:12px 14px;margin-bottom:14px;">
                <div style="display:flex;align-items:flex-start;gap:10px;margin-bottom:10px;">
                    <span style="color:#dc2626;font-size:18px;line-height:1;flex-shrink:0;margin-top:1px;">⚠</span>
                    <div style="flex:1;">
                        <div style="font-size:14px;font-weight:bold;color:#991b1b;">
                            {{ $totalPending }} {{ \Illuminate\Support\Str::plural('allocation', $totalPending) }}
                            pending across
                            {{ count($pendingSummary) }} {{ \Illuminate\Support\Str::plural('exam', count($pendingSummary)) }}
                        </div>
                        <div style="font-size:12px;color:#7f1d1d;margin-top:3px;">
                            Click any exam below to allocate teachers for its pending subjects.
                        </div>
                    </div>
                </div>

                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                    @foreach($pendingSummary as $item)
                        <a
                            href="{{ route('teacher-bulk-allocation.index', array_filter([
                                'academic_year_id' => $academicYearId,
                                'standard_id'      => $standardId,
                                'division_id'      => $divisionId,
                                'exam_master_id'   => $item->exam_id,
                            ])) }}"
                            style="
                                display:inline-flex;
                                align-items:center;
                                gap:6px;
                                background:#ffffff;
                                border:1px solid #fca5a5;
                                color:#991b1b;
                                border-radius:6px;
                                padding:5px 10px;
                                font-size:12px;
                                font-weight:600;
                                text-decoration:none;
                                transition:background .15s;
                            "
                            onmouseover="this.style.background='#fee2e2';"
                            onmouseout="this.style.background='#ffffff';"
                        >
                            <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#ef4444;flex-shrink:0;"></span>
                            {{ $item->exam_name }}
                            <span style="background:#fee2e2;color:#991b1b;border-radius:10px;padding:0 7px;font-size:11px;font-weight:700;line-height:16px;">
                                {{ $item->pending }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>

        @elseif($examMasterId === '' && empty($pendingSummary))

            <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:10px 14px;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
                <span style="color:#16a34a;font-size:16px;line-height:1;">✓</span>
                <span style="font-size:13px;font-weight:bold;color:#166534;">
                    All exams have teachers allocated — nothing pending
                </span>
            </div>

        @endif

        @if(!empty($pendingExam))

            @php
                $pendingItems = collect($subjectStatus)->filter(fn($s) => !$s->allocated);
                $groupedPending = $pendingItems
                    ->groupBy('subject_name')
                    ->map(function ($rows) {
                        return (object) [
                            'subject_name' => $rows->first()->subject_name,
                            'divisions'    => $rows->pluck('division_name')->sort()->values()->toArray(),
                        ];
                    })
                    ->values();
            @endphp

            <div style="margin-bottom:14px;border-radius:8px;overflow:hidden;">
                @if($pendingCount > 0)
                    <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:12px 14px;">
                        <div style="display:flex;align-items:flex-start;gap:10px;">
                            <div style="flex-shrink:0;font-size:18px;color:#dc2626;line-height:1;margin-top:1px;">⚠</div>
                            <div style="flex:1;">
                                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;flex-wrap:wrap;margin-bottom:6px;">
                                    <div style="font-size:14px;font-weight:bold;color:#991b1b;">
                                        {{ $pendingCount }}
                                        {{ \Illuminate\Support\Str::plural('allocation', $pendingCount) }}
                                        still pending for
                                        <strong>{{ $pendingExam->exam_name }}</strong>
                                    </div>
                                    <a
                                        href="{{ $backToAllExamsUrl }}"
                                        style="
                                            display:inline-flex;
                                            align-items:center;
                                            gap:6px;
                                            background:#ffffff;
                                            border:1px solid #fca5a5;
                                            color:#991b1b;
                                            border-radius:6px;
                                            padding:4px 10px;
                                            font-size:12px;
                                            font-weight:700;
                                            text-decoration:none;
                                            white-space:nowrap;
                                            flex-shrink:0;
                                            transition:background .15s;
                                        "
                                        onmouseover="this.style.background='#fee2e2';"
                                        onmouseout="this.style.background='#ffffff';"
                                    >
                                        <span style="font-size:14px;line-height:1;">←</span>
                                        Back to All Exams
                                    </a>
                                </div>

                                <div style="font-size:12px;color:#7f1d1d;margin-bottom:8px;">
                                    <strong>Pending Subjects (division-wise):</strong>
                                </div>
                                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                                    @foreach($groupedPending as $item)
                                        <span style="display:inline-flex;align-items:center;gap:5px;background:#ffffff;border:1px solid #fca5a5;color:#991b1b;border-radius:4px;padding:3px 8px;font-size:12px;font-weight:600;">
                                            {{ $item->subject_name }}
                                            <span style="background:#fee2e2;color:#991b1b;border-radius:10px;padding:0 6px;font-size:11px;font-weight:700;">
                                                {{ implode(', ', $item->divisions) }}
                                            </span>
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:10px 14px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span style="color:#16a34a;font-size:16px;line-height:1;">✓</span>
                                <span style="font-size:13px;font-weight:bold;color:#166534;">
                                    All subjects have teachers allocated in every division for
                                    <strong>{{ $pendingExam->exam_name }}</strong>
                                </span>
                            </div>
                            <a
                                href="{{ $backToAllExamsUrl }}"
                                style="
                                    display:inline-flex;
                                    align-items:center;
                                    gap:6px;
                                    background:#ffffff;
                                    border:1px solid #86efac;
                                    color:#166534;
                                    border-radius:6px;
                                    padding:4px 10px;
                                    font-size:12px;
                                    font-weight:700;
                                    text-decoration:none;
                                    white-space:nowrap;
                                    flex-shrink:0;
                                    transition:background .15s;
                                "
                                onmouseover="this.style.background='#dcfce7';"
                                onmouseout="this.style.background='#ffffff';"
                            >
                                <span style="font-size:14px;line-height:1;">←</span>
                                Back to All Exams
                            </a>
                        </div>
                    </div>
                @endif
            </div>

        @endif

        <div style="width:100%;background:#ffffff;border:1px solid #d1d5db;border-radius:7px;padding:10px;margin-bottom:12px;box-sizing:border-box;">
            <form method="GET" action="{{ route('teacher-bulk-allocation.index') }}">
                <div style="display:flex;align-items:flex-end;gap:8px;width:100%;flex-wrap:wrap;">

                    <div style="flex:0 0 170px;max-width:170px;">
                        <label for="standard_id" style="display:block;font-weight:600;margin-bottom:4px;font-size:13px;">
                            Standard
                        </label>
                        <select name="standard_id" id="standard_id" style="width:100%;height:35px;border:1px solid #d1d5db;border-radius:5px;padding:4px 8px;background:#fff;font-size:13px;box-sizing:border-box;">
                            <option value="">All Standards</option>
                            @foreach($standards as $standard)
                                <option value="{{ $standard->id }}" {{ (string)$standardId === (string)$standard->id ? 'selected' : '' }}>
                                    {{ $standard->standard_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div style="flex:0 0 130px;max-width:130px;">
                        <label for="division_id" style="display:block;font-weight:600;margin-bottom:4px;font-size:13px;">
                            Division
                        </label>
                        <select name="division_id" id="division_id" style="width:100%;height:35px;border:1px solid #d1d5db;border-radius:5px;padding:4px 8px;background:#fff;font-size:13px;box-sizing:border-box;">
                            <option value="">All</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}" {{ (string)$divisionId === (string)$division->id ? 'selected' : '' }}>
                                    {{ $division->division_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div style="flex:0 0 220px;max-width:220px;">
                        <label for="exam_master_id" style="display:block;font-weight:600;margin-bottom:4px;font-size:13px;">
                            Exam
                        </label>
                        <select name="exam_master_id" id="exam_master_id" style="width:100%;height:35px;border:1px solid #d1d5db;border-radius:5px;padding:4px 8px;background:#fff;font-size:13px;box-sizing:border-box;">
                            <option value="">All Exams</option>
                            @foreach($exams as $exam)
                                <option
                                    value="{{ $exam->id }}"
                                    data-standard-id="{{ $exam->standard_id ?? '' }}"
                                    {{ (string)$examMasterId === (string)$exam->id ? 'selected' : '' }}
                                >
                                    {{ $exam->exam_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" style="width:80px;height:35px;background:#16a34a;color:white;border:none;padding:0;border-radius:5px;cursor:pointer;font-weight:600;font-size:13px;box-sizing:border-box;">
                        Search
                    </button>

                    <a href="{{ route('teacher-bulk-allocation.index') }}?reset=1" style="width:70px;height:35px;display:inline-flex;align-items:center;justify-content:center;background:#6b7280;color:white;padding:0;border-radius:5px;text-decoration:none;font-weight:600;font-size:13px;box-sizing:border-box;">
                        Reset
                    </a>

                    <a href="{{ route('teacher-bulk-allocation.create') }}" style="width:auto;min-width:145px;height:35px;display:inline-flex;align-items:center;justify-content:center;background:#16a34a;color:white;padding:0 14px;border-radius:5px;text-decoration:none;font-weight:bold;font-size:13px;margin-left:auto;box-sizing:border-box;">
                        + New Allocation
                    </a>

                </div>
            </form>
        </div>

        <div style="width:100%;overflow:hidden;">
            <table style="width:100%;table-layout:fixed;border-collapse:collapse;margin:0;font-size:13px;">
                <colgroup>
                    <col style="width:5%;">
                    <col style="width:12%;">
                    <col style="width:8%;">
                    <col style="width:13%;">
                    <col style="width:11%;">
                    <col style="width:4%;">
                    <col style="width:15%;">
                    <col style="width:20%;">
                    <col style="width:12%;">
                </colgroup>

                <thead>
                    <tr style="background:#1e3a8a;color:white;">
                        <th style="padding:8px 4px;border:1px solid #d1d5db;text-align:center;font-size:13px;font-weight:bold;white-space:nowrap;">ID</th>
                        <th style="padding:8px 5px;border:1px solid #d1d5db;text-align:left;font-size:13px;font-weight:bold;white-space:nowrap;">Teacher</th>
                        <th style="padding:8px 5px;border:1px solid #d1d5db;text-align:left;font-size:13px;font-weight:bold;white-space:nowrap;">Academic Year</th>
                        <th style="padding:8px 5px;border:1px solid #d1d5db;text-align:left;font-size:13px;font-weight:bold;white-space:nowrap;">Section</th>
                        <th style="padding:8px 5px;border:1px solid #d1d5db;text-align:left;font-size:13px;font-weight:bold;white-space:nowrap;">Standard</th>
                        <th style="padding:8px 4px;border:1px solid #d1d5db;text-align:center;font-size:13px;font-weight:bold;white-space:nowrap;">Div.</th>
                        <th style="padding:8px 5px;border:1px solid #d1d5db;text-align:left;font-size:13px;font-weight:bold;white-space:nowrap;">Exam</th>
                        <th style="padding:8px 5px;border:1px solid #d1d5db;text-align:left;font-size:13px;font-weight:bold;white-space:nowrap;">Subjects</th>
                        <th style="padding:8px 4px;border:1px solid #d1d5db;text-align:center;font-size:13px;font-weight:bold;white-space:nowrap;">Action</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($allocations as $allocation)
                    @php
                        $examNames = $allocation->subjectAllocations
                            ->map(fn($item) => optional($item->exam)->exam_name)
                            ->filter()->unique()->values();
                    @endphp

                    <tr style="background:{{ $loop->even ? '#f9fafb' : '#ffffff' }};">
                        {{-- ✅ Show the child link's ID (unique per exam) --}}
                        <td style="padding:7px 4px;border:1px solid #d1d5db;text-align:center;vertical-align:middle;font-size:13px;font-weight:bold;color:#1e3a8a;">
                            {{ $allocation->displayRowId ?? $allocation->id }}
                        </td>

                        <td style="padding:7px 5px;border:1px solid #d1d5db;font-weight:bold;vertical-align:middle;overflow-wrap:anywhere;word-break:break-word;font-size:13px;">
                            {{ optional($allocation->teacher)->name ?? '-' }}
                        </td>

                        <td style="padding:7px 5px;border:1px solid #d1d5db;vertical-align:middle;overflow-wrap:anywhere;word-break:break-word;font-size:13px;">
                            {{ optional($allocation->academicYear)->year_name ?? '-' }}
                        </td>

                        <td style="padding:7px 5px;border:1px solid #d1d5db;vertical-align:middle;overflow-wrap:anywhere;word-break:break-word;font-size:13px;">
                            {{ optional($allocation->section)->section_name ?? '-' }}
                        </td>

                        <td style="padding:7px 5px;border:1px solid #d1d5db;font-weight:600;vertical-align:middle;overflow-wrap:anywhere;word-break:break-word;font-size:13px;">
                            {{ optional($allocation->standard)->standard_name ?? '-' }}
                        </td>

                        <td style="padding:7px 4px;border:1px solid #d1d5db;text-align:center;vertical-align:middle;font-size:13px;">
                            {{ optional($allocation->division)->division_name ?? '-' }}
                        </td>

                        <td style="padding:7px 5px;border:1px solid #d1d5db;vertical-align:middle;overflow-wrap:anywhere;word-break:break-word;font-size:13px;">
                            @if($examNames->isNotEmpty())
                                {{ $examNames->implode(', ') }}
                            @else
                                <span style="color:#9ca3af;">-</span>
                            @endif
                        </td>

                        <td style="padding:6px 5px;border:1px solid #d1d5db;vertical-align:middle;overflow:hidden;">
                            @if(isset($allocation->displaySubjects) && $allocation->displaySubjects->isNotEmpty())
                                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:4px;width:100%;">
                                    @foreach($allocation->displaySubjects as $subject)
                                        <span style="display:inline-flex;align-items:center;background:#dbeafe;color:#1e40af;border:1px solid #93c5fd;border-radius:4px;padding:3px 6px;font-size:12px;font-weight:bold;line-height:1.2;overflow-wrap:anywhere;word-break:break-word;box-sizing:border-box;">
                                            {{ $subject->subject_name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span style="color:#9ca3af;font-size:13px;">No subjects</span>
                            @endif
                        </td>

                        <td style="padding:6px 3px;border:1px solid #d1d5db;text-align:center;vertical-align:middle;">
                            <div style="display:flex;justify-content:center;align-items:center;gap:5px;flex-wrap:wrap;">
                                <a href="{{ route('teacher-bulk-allocation.edit', $allocation->id) }}" style="width:64px;height:30px;display:inline-flex;align-items:center;justify-content:center;box-sizing:border-box;background:#2563eb;color:white;padding:0;border-radius:4px;text-decoration:none;font-size:12px;font-weight:bold;">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('teacher-bulk-allocation.destroy', $allocation->id) }}" onsubmit="return confirm('Are you sure you want to delete this allocation?');" style="display:inline-block;margin:0;padding:0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="width:64px;height:30px;display:inline-flex;align-items:center;justify-content:center;box-sizing:border-box;background:#dc2626;color:white;border:none;padding:0;border-radius:4px;cursor:pointer;font-size:12px;font-weight:bold;">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="padding:25px 10px;text-align:center;color:#6b7280;border:1px solid #d1d5db;font-size:13px;">
                            No teacher allocations found.
                            <div style="margin-top:10px;">
                                <a href="{{ route('teacher-bulk-allocation.create') }}" style="display:inline-flex;align-items:center;justify-content:center;height:32px;background:#16a34a;color:white;padding:0 14px;border-radius:5px;text-decoration:none;font-weight:bold;font-size:13px;">
                                    Create First Allocation
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($allocations->hasPages())
            <div style="margin-top:14px;display:flex;justify-content:center;width:100%;">
                {{ $allocations->onEachSide(3)->links() }}
            </div>
        @endif

    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const standardSelect = document.getElementById('standard_id');
    const examSelect     = document.getElementById('exam_master_id');

    if (!standardSelect || !examSelect) return;

    const allExamOptions = Array.from(examSelect.options).map(opt => ({
        value:      opt.value,
        text:       opt.text,
        standardId: opt.dataset.standardId || '',
    }));

    const currentExamValue = examSelect.value;

    function rebuildExamDropdown(selectedStandard) {

        examSelect.innerHTML = '';

        allExamOptions.forEach(opt => {

            if (opt.value === '') {
                const o = document.createElement('option');
                o.value = '';
                o.textContent = opt.text;
                if (currentExamValue === '') o.selected = true;
                examSelect.appendChild(o);
                return;
            }

            const matches = (selectedStandard === '')
                || (String(opt.standardId) === String(selectedStandard));

            if (matches) {
                const o = document.createElement('option');
                o.value = opt.value;
                o.textContent = opt.text;
                if (opt.value === currentExamValue) o.selected = true;
                examSelect.appendChild(o);
            }
        });

        const stillExists = Array.from(examSelect.options).some(o => o.value === currentExamValue);
        if (!stillExists) examSelect.value = '';
    }

    rebuildExamDropdown(standardSelect.value);

    standardSelect.addEventListener('change', function () {
        rebuildExamDropdown(this.value);
    });

});
</script>

</x-app-layout>