<x-app-layout>

<style>
    /* TABS */
    .subj-tabs {
        display: flex;
        gap: 6px;
        border-bottom: 2px solid #1d4ed8;
        margin-bottom: 12px;
    }
    .subj-tab {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 16px;
        border-radius: 6px 6px 0 0;
        font-size: 13px; font-weight: 700;
        text-decoration: none; cursor: pointer;
        border: 1px solid transparent; border-bottom: none;
        transition: background .15s;
    }
    .subj-tab.active   { background: #1d4ed8; color: #fff; border-color: #1d4ed8; }
    .subj-tab.inactive { background: #e5e7eb; color: #1f2937; border-color: #d1d5db; }
    .subj-tab.inactive:hover { background: #d1d5db; color: #111827; }
    .subj-tab .badge {
        display: inline-block; padding: 1px 8px;
        border-radius: 10px; font-size: 11px; font-weight: 700; line-height: 16px;
    }
    .subj-tab.active .badge   { background: rgba(255,255,255,.25); color: #fff; }
    .subj-tab.inactive .badge { background: #9ca3af; color: #fff; }

    /* FILTER BAR */
    .subj-filter-bar {
        display: flex; gap: 10px; align-items: center;
        flex-wrap: wrap;
        background: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 10px 12px;
        margin-bottom: 12px;
    }
    .subj-filter-bar .select-wrap { position: relative; display: inline-block; }
    .subj-filter-bar select {
        appearance: none; -webkit-appearance: none; -moz-appearance: none;
        padding: 0 32px 0 12px;
        height: 36px; width: 250px;
        border: 1px solid #9ca3af; border-radius: 6px;
        background: #fff; font-size: 13px; font-weight: 600; color: #111827;
        cursor: pointer; outline: none;
        transition: border-color .15s, box-shadow .15s;
    }
    .subj-filter-bar select:focus {
        border-color: #1d4ed8;
        box-shadow: 0 0 0 2px rgba(29,78,216,.15);
    }
    .subj-filter-bar select::-ms-expand { display: none; }
    .subj-filter-bar .select-wrap::after {
        content: ""; position: absolute; top: 50%; right: 12px;
        width: 0; height: 0; margin-top: -2px;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 6px solid #6b7280;
        pointer-events: none;
    }
    .subj-filter-bar .select-wrap:hover::after { border-top-color: #1f2937; }

    /* BUTTONS */
    .subj-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        height: 36px; padding: 0 18px;
        border-radius: 6px; font-size: 13px; font-weight: 700;
        text-decoration: none; cursor: pointer;
        border: 1px solid transparent; white-space: nowrap;
        transition: background .15s, border-color .15s;
    }
    .subj-btn-primary   { background: #1d4ed8; color: #fff; border-color: #1e40af; }
    .subj-btn-primary:hover { background: #1e40af; }
    .subj-btn-secondary { background: #6b7280; color: #fff; border-color: #4b5563; }
    .subj-btn-secondary:hover { background: #4b5563; }

    .subj-header-btn {
        display: inline-flex; align-items: center; justify-content: center;
        height: 38px; padding: 0 18px;
        border-radius: 6px; font-size: 13px; font-weight: 700;
        color: #fff; background: #1d4ed8; border: 1px solid #1e40af;
        text-decoration: none; white-space: nowrap;
        transition: background .15s;
    }
    .subj-header-btn:hover { background: #1e40af; }

    /* TABLE */
    .subj-table {
        width: 100%;
        border-collapse: collapse;
        background: #ffffff;
        font-size: 13px;
        table-layout: fixed;
    }
    .subj-table thead th {
        background: #bfdbfe;
        border: 1px solid #94a3b8;
        padding: 8px 6px;
        text-align: center;
        font-weight: 700; font-size: 12px; color: #111827;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .subj-table td {
        border: 1px solid #94a3b8;
        padding: 7px 6px;
        vertical-align: middle;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .subj-table tbody tr:hover { background: #fefce8; }
    .subj-table .center     { text-align: center; }
    .subj-table .bold       { font-weight: 600; }
    .subj-table .muted      { color: #6b7280; }
    .subj-table .active-yes { color: #15803d; font-weight: 700; }
    .subj-table .active-no  { color: #b91c1c; font-weight: 700; }
    .subj-table .optional-yes { color: #1d4ed8; font-weight: 700; }

    .subj-count-pill {
        display: inline-block; padding: 2px 9px;
        border-radius: 10px;
        background: #e0e7ff; color: #3730a3;
        font-size: 11px; font-weight: 700;
    }

    .subj-edit-btn {
        display: inline-block;
        background: #f59e0b; color: #fff;
        padding: 6px 14px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 13px; font-weight: 600;
    }
    .subj-edit-btn:hover { background: #d97706; }
</style>


<div class="max-w-7xl mx-auto py-4 px-4">

    <div class="bg-gradient-to-br from-red-100 via-yellow-100 to-orange-100
                rounded-xl shadow-xl border-4 border-amber-400 p-5">

        {{-- HEADER --}}
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-blue-700">
                Subject Master / Standard Wise Mapping
            </h2>
            <a href="{{ route('subjects.create') }}" class="subj-header-btn">
                + Add Subject
            </a>
        </div>

        {{-- FLASH --}}
        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-3">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 text-red-700 p-3 rounded mb-3">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded mb-3">
                <ul class="list-disc ml-5">
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        {{-- TABS --}}
        <div class="subj-tabs">
            <a href="{{ route('subjects.index', array_filter(['tab' => 'academic', 'standard_id' => request('standard_id')])) }}"
               class="subj-tab {{ $activeTab === 'academic' ? 'active' : 'inactive' }}">
                Academic <span class="badge">{{ $academicCount }}</span>
            </a>
            <a href="{{ route('subjects.index', array_filter(['tab' => 'skill', 'standard_id' => request('standard_id')])) }}"
               class="subj-tab {{ $activeTab === 'skill' ? 'active' : 'inactive' }}">
                Skill <span class="badge">{{ $skillCount }}</span>
            </a>
            <a href="{{ route('subjects.index', array_filter(['tab' => 'all', 'standard_id' => request('standard_id')])) }}"
               class="subj-tab {{ $activeTab === 'all' ? 'active' : 'inactive' }}">
                All <span class="badge">{{ $allCount }}</span>
            </a>
        </div>

        {{-- FILTER --}}
        <form method="GET" action="{{ route('subjects.index') }}">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            <div class="subj-filter-bar">
                <div class="select-wrap">
                    <select name="standard_id" onchange="this.form.submit()">
                        <option value="">ALL STANDARDS</option>
                        @foreach($standards as $standard)
                            <option value="{{ $standard->id }}"
                                {{ (string)request('standard_id') === (string)$standard->id ? 'selected' : '' }}>
                                {{ $standard->standard_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="subj-btn subj-btn-primary">Search</button>
                @if(request('standard_id'))
                    <a href="{{ route('subjects.index', ['tab' => $activeTab]) }}"
                       class="subj-btn subj-btn-secondary">Clear</a>
                @endif
            </div>
        </form>

        {{-- TABLE --}}
        <div class="overflow-x-auto">
            <table class="subj-table">
                <colgroup>
                    <col style="width:4%;">
                    <col style="width:8%;">
                    <col style="width:13%;">
                    <col style="width:20%;">
                    <col style="width:10%;">
                    <col style="width:10%;">
                    <col style="width:14%;">
                    <col style="width:7%;">
                    <col style="width:8%;">
                    <col style="width:6%;">
                </colgroup>

                <thead>
                    <tr>
                        <th>#</th>
                        <th>Subject ID</th>
                        <th>Standard</th>
                        <th>Subject Name</th>
                        <th>Subject Code</th>
                        <th>Short Name</th>
                        <th>Subject Type</th>
                        <th>Optional</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @if($activeTab === 'skill')

                        @forelse($subjects as $index => $subject)

                            @php
                                $firstMapping = \App\Models\StandardWiseSubject::where('subject_id', $subject->id)
                                    ->when(request('standard_id'), fn ($q) => $q->where('standard_id', request('standard_id')))
                                    ->orderBy('id')
                                    ->first();

                                $stdCount = $subject->standards_count ?? 0;

                                if ($stdCount === 0) {
                                    $standardSummary = '-';
                                } elseif ($stdCount === 1) {
                                    $standardSummary = $subject->standards_list;
                                } else {
                                    $standardSummary = $stdCount . ' Standards';
                                }

                                $isOptional = $firstMapping?->is_optional ?? 0;
                                $isActive   = $subject->is_active ?? 0;
                            @endphp

                            <tr>
                                <td class="center">{{ $index + 1 }}</td>

                                <td class="center bold">{{ $subject->id }}</td>

                                <td class="center" title="{{ $subject->standards_list ?: 'Not mapped to any standard yet' }}">
                                    @if($stdCount === 0)
                                        <span class="subj-count-pill" style="background:#fee2e2;color:#991b1b;">Unmapped</span>
                                    @else
                                        <span class="subj-count-pill">{{ $standardSummary }}</span>
                                    @endif
                                </td>

                                <td class="bold" title="{{ $subject->subject_name }}">
                                    {{ $subject->subject_name }}
                                </td>

                                <td class="center">{{ $subject->subject_code ?: '-' }}</td>

                                <td class="center">{{ $subject->short_name ?: '-' }}</td>

                                <td class="center">
                                    {{ $subject->subjectType->subject_type_name
                                        ?? $subject->subjectType->name
                                        ?? '-' }}
                                </td>

                                <td class="center">
                                    @if($isOptional)
                                        <span class="optional-yes">Yes</span>
                                    @else
                                        <span class="muted">No</span>
                                    @endif
                                </td>

                                <td class="center">
                                    @if($isActive)
                                        <span class="active-yes">Active</span>
                                    @else
                                        <span class="active-no">Inactive</span>
                                    @endif
                                </td>

                                <td class="center">
                                    @if($firstMapping)
                                        <a href="{{ route('subjects.edit', $firstMapping->id) }}"
                                           class="subj-edit-btn">Edit</a>
                                    @else
                                        <a href="{{ route('subjects.edit-master', $subject->id) }}"
                                           class="subj-edit-btn">Edit</a>
                                    @endif
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="10" class="center muted" style="padding:20px;">
                                    No Skill Subjects found
                                    @if(request('standard_id')) for the selected Standard @endif.
                                </td>
                            </tr>
                        @endforelse

                    @else

                        @forelse($subjects as $index => $mapping)
                            <tr>
                                <td class="center">{{ $index + 1 }}</td>

                                <td class="center bold">{{ $mapping->subject_id ?? '-' }}</td>

                                <td class="center" title="{{ $mapping->standard->standard_name ?? $mapping->standard_name ?? '' }}">
                                    {{ $mapping->standard->standard_name ?? $mapping->standard_name ?? '-' }}
                                </td>

                                <td class="bold" title="{{ $mapping->subject->subject_name ?? $mapping->subject_name ?? '' }}">
                                    {{ $mapping->subject->subject_name ?? $mapping->subject_name ?? '-' }}
                                </td>

                                <td class="center">{{ $mapping->subject->subject_code ?? '-' }}</td>

                                <td class="center">{{ $mapping->subject->short_name ?? '-' }}</td>

                                <td class="center">
                                    {{ $mapping->subject->subjectType->subject_type_name
                                        ?? $mapping->subject->subjectType->name
                                        ?? '-' }}
                                </td>

                                <td class="center">
                                    @if($mapping->is_optional)
                                        <span class="optional-yes">Yes</span>
                                    @else
                                        <span class="muted">No</span>
                                    @endif
                                </td>

                                <td class="center">
                                    @if($mapping->is_active)
                                        <span class="active-yes">Active</span>
                                    @else
                                        <span class="active-no">Inactive</span>
                                    @endif
                                </td>

                                <td class="center">
                                    <a href="{{ route('subjects.edit', $mapping->id) }}"
                                       class="subj-edit-btn">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="center muted" style="padding:20px;">
                                    No Subject Mapping Found for
                                    <strong>{{ ucfirst($activeTab) }}</strong>
                                    tab{{ request('standard_id') ? ' and the selected Standard' : '' }}.
                                </td>
                            </tr>
                        @endforelse

                    @endif
                </tbody>
            </table>
        </div>

    </div>

</div>

</x-app-layout>