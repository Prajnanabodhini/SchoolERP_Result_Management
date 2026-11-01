<x-app-layout>

<style>
    /* UPDATED STYLES TO REMOVE INTERNAL SCROLLING */
    .manage-page {
        font-family: Arial, sans-serif;
        background: #f3f4f6;
        padding: 10px 15px;
        box-sizing: border-box;
        min-height: calc(100vh - 60px); /* Ensures background covers the screen */
    }

    /* Top Control Bar */
    .filter-bar {
        background: #fff;
        padding: 8px 15px;
        border-radius: 6px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 10px;
    }

    .filter-bar label {
        font-weight: 700;
        font-size: 12px;
        color: #374151;
        margin: 0;
    }

    .filter-bar select {
        padding: 4px 8px;
        border: 1px solid #9ca3af;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        min-width: 180px;
        outline: none;
        cursor: pointer;
    }
    
    .filter-bar select:focus {
        border-color: #2563eb;
    }

    .btn-save {
        background: #16a34a;
        color: #fff;
        padding: 6px 16px;
        border: none;
        border-radius: 4px;
        font-weight: 700;
        font-size: 12px;
        cursor: pointer;
        margin-left: auto;
    }
    
    .btn-save:hover { background: #15803d; }

    /* Main Two-Column Layout */
    .columns-wrapper {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        align-items: start; /* Prevents columns from stretching to match the tallest one */
    }

    .subject-column {
        background: #fff;
        border-radius: 6px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        border: 1px solid #e5e7eb;
    }

    .column-header {
        background: #1e3a8a;
        color: #fff;
        padding: 6px 12px;
        font-weight: 700;
        font-size: 11px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        border-top-left-radius: 5px;
        border-top-right-radius: 5px;
    }

    /* Changed from Grid to CSS Columns for Top-to-Bottom flow */
    .subject-list {
        padding: 6px;
        column-width: 180px; /* Auto-creates columns based on width */
        column-gap: 6px;
    }

    /* Individual Checkbox Item */
    .subject-item {
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 3px 6px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        cursor: pointer;
        transition: background 0.1s;
        
        /* Important for CSS Columns to prevent item splitting */
        break-inside: avoid; 
        margin-bottom: 4px;
        width: 100%;
        box-sizing: border-box;
    }

    .subject-item:hover {
        background: #eff6ff;
        border-color: #93c5fd;
    }

    .subject-item input[type="checkbox"] {
        width: 12px;
        height: 12px;
        cursor: pointer;
        accent-color: #2563eb;
        margin: 0;
        flex-shrink: 0;
    }

    /* Subject ID - Dark Black */
    .subject-item .subject-id {
        font-size: 11px;
        font-weight: 800;
        color: #000000;
        min-width: 22px;
        text-align: right;
        flex-shrink: 0;
    }

    /* Style for the Subject Name */
    .subject-item .subject-name {
        font-size: 11px;
        font-weight: 600;
        color: #374151;
        cursor: pointer;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        flex-grow: 1;
    }

    .subject-item input:checked ~ .subject-name {
        color: #1d4ed8;
    }
    
    /* Visual highlight for checked items */
    .subject-item.checked-bg {
        background: #dbeafe;
        border-color: #3b82f6;
    }
</style>

<div class="manage-page">
    
    @if(session('success'))
        <div style="background:#dcfce7;color:#166534;border:1px solid #86efac;padding:6px 12px;border-radius:4px;margin-bottom:10px;font-weight:600;font-size:12px;">
            {{ session('success') }}
        </div>
    @endif

    @if(!$selectedStandard)
        <!-- Initial View -->
        <div style="max-width: 400px; margin: 40px auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
            <h2 style="color: #1e3a8a; margin-top:0; margin-bottom: 15px; font-size: 16px;">Manage Subjects by Standard</h2>
            <form method="GET" action="{{ route('subjects.manage') }}" style="display: flex; flex-direction: column; gap: 10px;">
                <select name="standard_id" required style="padding: 8px; font-size: 13px; border: 1px solid #9ca3af; border-radius: 4px; width: 100%; outline: none;">
                    <option value="">-- Choose Standard --</option>
                    @foreach($standards as $std)
                        <option value="{{ $std->id }}">{{ $std->standard_name }}</option>
                    @endforeach
                </select>
                <button type="submit" style="background: #2563eb; color: white; padding: 8px; font-size: 13px; font-weight: bold; border: none; border-radius: 4px; cursor: pointer;">Load Subjects</button>
            </form>
        </div>
    @else
        <!-- Main Interface -->
        <form method="POST" action="{{ route('subjects.save-mappings') }}" id="mappingForm">
            @csrf
            <input type="hidden" name="standard_id" value="{{ $selectedStandard }}">

            <div class="filter-bar">
                <label for="standard_id">Standard:</label>
                <select name="standard_id" id="standard_id" onchange="window.location.href='{{ route('subjects.manage') }}?standard_id=' + this.value;">
                    @foreach($standards as $std)
                        <option value="{{ $std->id }}" {{ $selectedStandard == $std->id ? 'selected' : '' }}>
                            {{ $std->standard_name }}
                        </option>
                    @endforeach
                </select>
                
                <a href="{{ route('subjects.index') }}" style="text-decoration:none; background: #fff; color: #4b5563; border: 1px solid #d1d5db; padding: 6px 12px; border-radius: 4px; font-weight: 600; font-size:12px;">Cancel</a>
                
                <button type="submit" class="btn-save">Save Mappings</button>
            </div>

            <div class="columns-wrapper">
                <!-- Academic Subjects -->
                <div class="subject-column">
                    <div class="column-header">📚 Academic Subjects</div>
                    <div class="subject-list">
                        {{-- Sort by Name (case insensitive), then by ID to keep duplicates together --}}
                        @foreach(collect($academicSubjects)->sortBy([
                            fn($sub) => strtolower(trim($sub->subject_name)),
                            fn($sub) => $sub->id
                        ])->values() as $sub)
                            @php $checked = in_array((string)$sub->id, $mappedSubjectIds, true); @endphp
                            <label class="subject-item {{ $checked ? 'checked-bg' : '' }}">
                                <input type="checkbox" name="subject_ids[]" value="{{ $sub->id }}" {{ $checked ? 'checked' : '' }}>
                                <span class="subject-id">{{ $sub->id }}</span>
                                <span class="subject-name" title="{{ $sub->subject_name }}">{{ $sub->subject_name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Skill Subjects -->
                <div class="subject-column">
                    <div class="column-header">🎨 Skill Subjects</div>
                    <div class="subject-list">
                        {{-- Sort by Name (case insensitive), then by ID to keep duplicates together --}}
                        @foreach(collect($skillSubjects)->sortBy([
                            fn($sub) => strtolower(trim($sub->subject_name)),
                            fn($sub) => $sub->id
                        ])->values() as $sub)
                            @php $checked = in_array((string)$sub->id, $mappedSubjectIds, true); @endphp
                            <label class="subject-item {{ $checked ? 'checked-bg' : '' }}">
                                <input type="checkbox" name="subject_ids[]" value="{{ $sub->id }}" {{ $checked ? 'checked' : '' }}>
                                <span class="subject-id">{{ $sub->id }}</span>
                                <span class="subject-name" title="{{ $sub->subject_name }}">{{ $sub->subject_name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </form>
    @endif
</div>

<script>
    // Add visual cue when clicked
    document.querySelectorAll('.subject-item input[type="checkbox"]').forEach(cb => {
        cb.addEventListener('change', function() {
            if (this.checked) {
                this.closest('.subject-item').classList.add('checked-bg');
            } else {
                this.closest('.subject-item').classList.remove('checked-bg');
            }
        });
    });
</script>

</x-app-layout>