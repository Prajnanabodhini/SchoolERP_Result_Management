<x-app-layout>

<style>
    /* STYLES UPDATED */
    .page-wrapper {
        padding: 8px;
        background-color: #f3f4f6;
        box-sizing: border-box;
    }

    .main-card {
        background: #fff;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        padding: 10px;
    }

    /* Top Bar */
    .top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }
    
    .top-bar h2 {
        margin: 0;
        font-size: 16px;
        color: #1d4ed8;
        font-weight: 700;
    }

    .btn-group {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    /* Unified Button Style */
    .btn-add {
        background: #1d4ed8;
        color: #fff;
        text-decoration: none;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }
    .btn-add:hover { background: #1e40af; }

    /* Filter Bar */
    .filter-bar {
        display: flex;
        gap: 8px;
        align-items: center;
        background: #f9fafb;
        padding: 8px;
        border-radius: 4px;
        border: 1px solid #e5e7eb;
        margin-bottom: 8px;
    }

    .filter-bar select {
        padding: 4px 8px;
        height: 28px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        width: 200px;
        outline: none;
    }

    .btn-search {
        height: 28px;
        padding: 0 12px;
        background: #1d4ed8;
        color: #fff;
        border: none;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }
    
    .btn-clear {
        height: 28px;
        padding: 0 12px;
        background: #6b7280;
        color: #fff;
        border: none;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }

    /* Table Container */
    .table-container {
        border: 1px solid #e5e7eb;
        border-radius: 4px;
    }

    /* Table Styling */
    .subj-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px; /* Medium font size */
        table-layout: fixed;
    }

    .subj-table th {
        background: #dbeafe;
        color: #1e40af;
        padding: 8px 4px;
        text-align: center;
        font-weight: 700;
        font-size: 12px;
        border-bottom: 2px solid #bfdbfe;
        border-right: 1px solid #cbd5e1;
    }

    .subj-table td {
        padding: 6px 4px;
        text-align: center;
        border-bottom: 1px solid #e5e7eb;
        border-right: 1px solid #e5e7eb;
        vertical-align: middle;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Allows long text to wrap inside the cell */
    .text-wrap {
        white-space: normal !important; 
        word-wrap: break-word !important;
    }

    .subj-table tr:nth-child(even) { background: #f9fafb; }
    .subj-table tr:hover { background: #fef9c3; }

    .text-left { text-align: left !important; padding-left: 8px !important; }
    
    /* Badges */
    .badge-academic {
        background: #e0f2fe; color: #0369a1;
        padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;
        display: inline-block;
    }
    .badge-skill {
        background: #fce7f3; color: #be185d;
        padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;
        display: inline-block;
    }

    /* Reduced Edit Button Size */
    .btn-edit {
        display: inline-block;
        background: #f59e0b; 
        color: #fff;
        padding: 4px 16px; /* Adjusted padding */
        border-radius: 4px;
        text-decoration: none; 
        font-size: 13px;
        font-weight: 600;
        /* width: 100%; <- REMOVED THIS */
    }
    .btn-edit:hover { background: #d97706; }
</style>

<div class="page-wrapper">
    <div class="main-card">
        
        {{-- Header --}}
        <div class="top-bar">
            <h2>Standard Wise Subject Mapping</h2>
            <div class="btn-group">
                <a href="{{ route('subjects.manage') }}" class="btn-add">⇄ Standard Mapping</a>
                <a href="{{ route('subjects.create') }}" class="btn-add">+ Add Subject</a>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div style="background:#dcfce7;color:#166534;padding:6px 10px;border-radius:4px;font-size:12px;margin-bottom:8px;font-weight:600;flex-shrink:0;">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div style="background:#fee2e2;color:#991b1b;padding:6px 10px;border-radius:4px;font-size:12px;margin-bottom:8px;font-weight:600;flex-shrink:0;">
                {{ session('error') }}
            </div>
        @endif

        {{-- Filter Bar --}}
        <form method="GET" action="{{ route('subjects.index') }}" style="flex-shrink:0;">
            <div class="filter-bar">
                <label style="font-size:13px;font-weight:700;color:#374151;">Standard:</label>
                <select name="standard_id" onchange="this.form.submit()" style="font-size:13px;">
                    <option value="">-- ALL STANDARDS --</option>
                    @foreach($standards as $standard)
                        <option value="{{ $standard->id }}" {{ (string)$standardFilter === (string)$standard->id ? 'selected' : '' }}>
                            {{ $standard->standard_name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn-search">Search</button>
                @if($standardFilter)
                    <a href="{{ route('subjects.index') }}" class="btn-clear">Clear</a>
                @endif
                <div style="margin-left:auto; font-size:13px; font-weight:700; color:#4b5563;">
                    Total Mapped Subjects: {{ $totalCount }}
                </div>
            </div>
        </form>

        {{-- Unified Table --}}
        <div class="table-container">
            <table class="subj-table">
                <colgroup>
                    <col style="width: 5%;">  {{-- ID --}}
                    <col style="width: 15%;"> {{-- Standard (Increased from 10%) --}}
                    <col style="width: 25%;"> {{-- Subject Name (Decreased from 28%) --}}
                    <col style="width: 10%;"> {{-- Code --}}
                    <col style="width: 10%;"> {{-- Short Name --}}
                    <col style="width: 10%;"> {{-- Type --}}
                    <col style="width: 7%;">  {{-- Optional --}}
                    <col style="width: 8%;">  {{-- Status --}}
                    <col style="width: 10%;"> {{-- Action (Decreased from 12%) --}}
                </colgroup>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Standard</th>
                        <th>Subject Name</th>
                        <th>Code</th>
                        <th>Short Name</th>
                        <th>Type</th>
                        <th>Optional</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Sort subjects: Standard ID first, then Academic (Type 1) before Skill (Type 2/3), then Subject Name alphabetically
                        $sortedSubjects = $subjects->sortBy(function($mapping) {
                            $standardId = $mapping->standard_id ?? 0;
                            $typeId = $mapping->subject->subject_type_id ?? 0;
                            $typePriority = ($typeId == 1) ? 1 : 2; 
                            $subjectName = $mapping->subject->subject_name ?? '';
                            
                            return sprintf('%05d_%d_%s', $standardId, $typePriority, $subjectName);
                        });
                    @endphp

                    @forelse($sortedSubjects as $mapping)
                        <tr>
                            <td>{{ $mapping->subject_id ?? '-' }}</td>
                            
                            <td class="text-wrap">{{ $mapping->standard->standard_name ?? $mapping->standard_name ?? '-' }}</td>
                            
                            <td class="text-left text-wrap" title="{{ $mapping->subject->subject_name ?? $mapping->subject_name ?? '' }}">
                                <strong>{{ $mapping->subject->subject_name ?? $mapping->subject_name ?? '-' }}</strong>
                            </td>
                            
                            <td>{{ $mapping->subject->subject_code ?? '-' }}</td>
                            <td>{{ $mapping->subject->short_name ?? '-' }}</td>
                            
                            <td>
                                @php 
                                    $typeId = $mapping->subject->subject_type_id ?? 0; 
                                @endphp
                                @if($typeId == 1)
                                    <span class="badge-academic">ACADEMIC</span>
                                @else
                                    <span class="badge-skill">SKILL</span>
                                @endif
                            </td>
                            
                            <td>
                                @if($mapping->is_optional)
                                    <span style="color:#2563eb;font-weight:700;">Yes</span>
                                @else
                                    <span style="color:#9ca3af;">No</span>
                                @endif
                            </td>
                            
                            <td>
                                @if($mapping->is_active)
                                    <span style="color:#16a34a;font-weight:700;">Active</span>
                                @else
                                    <span style="color:#dc2626;font-weight:700;">Inactive</span>
                                @endif
                            </td>
                            
                            <td style="padding: 4px;">
                                <a href="{{ route('subjects.edit', $mapping->id) }}" class="btn-edit">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="padding:20px; text-align:center; color:#6b7280; font-style:italic; font-size:14px;">
                                No subjects mapped to this standard yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

</x-app-layout>