<x-app-layout>

<style>
    .subj-form * { box-sizing: border-box; }

    .subj-form label.lbl {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: #374151;
        margin-bottom: 2px;
        letter-spacing: 0.2px;
    }
    .subj-form .req { color: #dc2626; }

    .subj-form .select-wrap { position: relative; width: 100%; }

    .subj-form input[type="text"],
    .subj-form input[type="number"],
    .subj-form select {
        width: 100%;
        height: 32px;
        border: 1px solid #9ca3af;
        border-radius: 5px;
        padding: 4px 8px;
        font-size: 12px;
        background: #fff;
        outline: none;
    }

    /* Only the ID field is readonly */
    .subj-form input[readonly] { background: #f3f4f6; color: #6b7280; cursor: not-allowed; }

    .subj-form input:focus,
    .subj-form select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 2px rgba(37,99,235,.15);
    }

    .subj-form select {
        padding-right: 30px;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        cursor: pointer;
    }

    .subj-form .select-wrap::after {
        content: "";
        position: absolute;
        top: 50%; right: 11px;
        width: 0; height: 0; margin-top: -2px;
        border-left: 4px solid transparent;
        border-right: 4px solid transparent;
        border-top: 5px solid #6b7280;
        pointer-events: none;
    }

    .subj-form .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 10px;
        margin-bottom: 6px;
    }

    .subj-form .check-box {
        display: flex; align-items: center; gap: 6px;
        height: 32px; padding: 0 10px;
        border: 1px solid #d1d5db; border-radius: 5px; background: #f9fafb;
    }
    .subj-form .check-box input { width: 14px; height: 14px; cursor: pointer; }
    .subj-form .check-box label {
        font-size: 12px; font-weight: 600; color: #374151; margin: 0; cursor: pointer;
    }

    .std-picker {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
        gap: 4px;
        max-height: 130px;
        overflow-y: auto;
        padding: 8px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background: #f9fafb;
    }
    .std-item {
        display: flex; align-items: center; gap: 6px;
        padding: 4px 8px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 5px;
        font-size: 11px;
        font-weight: 600;
        color: #374151;
        cursor: pointer;
        transition: background .1s, border-color .1s;
    }
    .std-item:hover { background: #eff6ff; border-color: #93c5fd; }
    .std-item input { width: 14px; height: 14px; cursor: pointer; }
    .std-item.checked { background: #dbeafe; border-color: #3b82f6; color: #1e3a8a; }

    .subj-form .btn-update {
        height: 32px; padding: 0 22px;
        background: #ca8a04; color: #fff;
        border: none; border-radius: 5px;
        font-size: 12px; font-weight: 700; cursor: pointer;
    }
    .subj-form .btn-update:disabled { background: #9ca3af; cursor: not-allowed; }
    .subj-form .btn-cancel {
        height: 32px; padding: 0 22px;
        display: inline-flex; align-items: center; justify-content: center;
        background: #6b7280; color: #fff; border-radius: 5px;
        text-decoration: none; font-size: 12px; font-weight: 700;
    }
</style>

<div style="width:100%;max-width:100%;padding:6px;">

    <div style="width:100%;background:#fff;border-radius:10px;padding:12px 16px;border:1px solid #d1d5db;box-shadow:0 3px 8px rgba(0,0,0,.10);">

        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
            <h2 style="margin:0;color:#ca8a04;font-size:18px;font-weight:700;">Edit Subject</h2>
            <div style="display:flex; gap:10px; align-items:center;">
                <a href="{{ route('subjects.manage') }}" style="font-size:12px;color:#2563eb;font-weight:600;text-decoration:none;background:#eff6ff;padding:4px 8px;border-radius:4px;border:1px solid #bfdbfe;">
                    ⇄ Manage by Standard
                </a>
                <a href="{{ route('subjects.index') }}" style="font-size:12px;color:#6b7280;text-decoration:none;font-weight:600;">
                    ← Back to Subject Master
                </a>
            </div>
        </div>

        @if(session('error'))
            <div style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;padding:7px 10px;border-radius:5px;margin-bottom:8px;font-size:12px;">
                {{ session('error') }}
            </div>
        @endif
        @if ($errors->any())
            <div style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;padding:7px 10px;border-radius:5px;margin-bottom:8px;font-size:12px;">
                <ul style="margin:0;padding-left:16px;">
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('subjects.update-master', $masterSubject->id) }}" id="subjectEditForm" class="subj-form">
            @csrf
            @method('PUT')

            <div class="form-row">
                <div>
                    <label class="lbl">SUBJECT MASTER ID</label>
                    <input type="text" value="{{ $masterSubject->id }}" readonly tabindex="-1">
                </div>
                <div>
                    <label for="subject_name" class="lbl">SUBJECT NAME <span class="req">*</span></label>
                    <input type="text" name="subject_name" id="subject_name" value="{{ old('subject_name', $masterSubject->subject_name) }}" maxlength="255" required>
                </div>
                <div>
                    <label for="subject_code" class="lbl">SUBJECT CODE <span class="req">*</span></label>
                    <input type="text" name="subject_code" id="subject_code" value="{{ old('subject_code', $masterSubject->subject_code) }}" maxlength="50" style="text-transform:uppercase;" required>
                </div>
            </div>

            <div class="form-row">
                <div>
                    <label for="short_name" class="lbl">SHORT NAME</label>
                    <input type="text" name="short_name" id="short_name" value="{{ old('short_name', $masterSubject->short_name) }}" maxlength="20">
                </div>
                <div>
                    <label for="subject_type_id" class="lbl">SUBJECT TYPE <span class="req">*</span></label>
                    <div class="select-wrap">
                        <select name="subject_type_id" id="subject_type_id" required>
                            <option value="">Select Subject Type</option>
                            @foreach($subjectTypes as $subjectType)
                                <option value="{{ $subjectType->id }}" {{ (string)old('subject_type_id', $masterSubject->subject_type_id) === (string)$subjectType->id ? 'selected' : '' }}>
                                    {{ $subjectType->name ?? $subjectType->subject_type ?? $subjectType->type_name ?? $subjectType->description ?? 'Type ' . $subjectType->id }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label for="sort_order" class="lbl">DISPLAY ORDER <span class="req">*</span></label>
                    <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $masterSubject->display_order ?? 0) }}" min="0" required>
                </div>
            </div>

            <div class="form-row">
                <div style="display:flex; gap:10px;">
                    <div class="check-box" style="flex:1;">
                        <input type="checkbox" name="is_optional" value="1" id="is_optional" {{ old('is_optional', $masterSubject->is_optional ?? false) ? 'checked' : '' }}>
                        <label for="is_optional">Optional</label>
                    </div>
                    <div class="check-box" style="flex:1;">
                        <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $masterSubject->is_active) ? 'checked' : '' }}>
                        <label for="is_active">Active</label>
                    </div>
                </div>
                <div style="grid-column: span 2;"></div>
            </div>

            <div style="margin-bottom:8px; margin-top:8px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                    <label class="lbl" style="margin-bottom:0;">MAP TO STANDARDS <span class="req">*</span></label>
                    <label style="font-size:11px; font-weight:600; color:#2563eb; cursor:pointer; display:flex; align-items:center; gap:4px;">
                        <input type="checkbox" id="selectAllStandards" style="cursor:pointer;"> Select All
                    </label>
                </div>

                <div class="std-picker">
                    @php 
                        // Ensure both old input and DB IDs are treated as strings for accurate comparison
                        $selectedStandards = array_map('strval', old('standard_ids', $mappedStandardIds)); 
                    @endphp
                    
                    @foreach($standards as $standard)
                        @php $checked = in_array((string)$standard->id, $selectedStandards, true); @endphp
                        <label class="std-item {{ $checked ? 'checked' : '' }}">
                            <input type="checkbox" name="standard_ids[]" value="{{ $standard->id }}" {{ $checked ? 'checked' : '' }} onchange="this.closest('.std-item').classList.toggle('checked', this.checked)">
                            <span>{{ $standard->standard_name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:8px;">
                <button type="submit" id="updateSubjectButton" class="btn-update">Update</button>
                <a href="{{ route('subjects.index') }}" class="btn-cancel">Cancel</a>
            </div>

        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form          = document.getElementById('subjectEditForm');
    const subjectCode   = document.getElementById('subject_code');
    const saveButton    = document.getElementById('updateSubjectButton');
    const selectAll     = document.getElementById('selectAllStandards');
    const stdCheckboxes = document.querySelectorAll('input[name="standard_ids[]"]');

    /* UPPERCASE SUBJECT CODE */
    subjectCode.addEventListener('input', function () {
        this.value = this.value.toUpperCase();
    });

    /* SELECT ALL TOGGLE */
    selectAll.addEventListener('change', function() {
        stdCheckboxes.forEach(cb => {
            cb.checked = this.checked;
            cb.closest('.std-item').classList.toggle('checked', this.checked);
        });
    });

    /* CLIENT VALIDATION */
    form.addEventListener('submit', function (event) {
        const checkedStandards = document.querySelectorAll('input[name="standard_ids[]"]:checked');

        if (checkedStandards.length === 0) {
            event.preventDefault();
            alert('Please select at least one Standard.');
            return;
        }

        saveButton.disabled = true;
        saveButton.innerText = 'Updating...';
    });

});
</script>

</x-app-layout>