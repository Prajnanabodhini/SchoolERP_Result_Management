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

    .subj-form .quick-map-row {
        display: grid;
        grid-template-columns: 220px 350px;
        justify-content: start;
        gap: 12px;
        margin-bottom: 10px;
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

    .subj-form .btn-save {
        height: 32px; padding: 0 22px;
        background: #16a34a; color: #fff;
        border: none; border-radius: 5px;
        font-size: 12px; font-weight: 700; cursor: pointer;
    }
    .subj-form .btn-save:disabled { background: #9ca3af; cursor: not-allowed; }
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
            <h2 style="margin:0;color:#16a34a;font-size:18px;font-weight:700;">Add Subject</h2>
            <div style="display:flex; gap:10px; align-items:center;">
                <a href="{{ route('subjects.manage') }}" style="font-size:12px;color:#2563eb;font-weight:600;text-decoration:none;background:#eff6ff;padding:4px 8px;border-radius:4px;border:1px solid #bfdbfe;">
                    ⇄ Manage by Standard
                </a>
                <a href="{{ route('subjects.index') }}" style="font-size:12px;color:#6b7280;text-decoration:none;font-weight:600;">
                    ← Back to Subject Master
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;padding:7px 10px;border-radius:5px;margin-bottom:8px;font-size:12px;">
                <ul style="margin:0;padding-left:16px;">
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('subjects.store') }}" id="subjectCreateForm" class="subj-form">
            @csrf

            <div style="background:#eff6ff; border:1px solid #bfdbfe; padding:10px 12px; border-radius:8px; margin-bottom:12px;">
                <div style="color:#1e40af; font-size:12px; font-weight:700; margin-bottom:6px;">
                    🔍 Quick Map an Existing Subject (Optional)
                </div>
                
                <div class="quick-map-row">
                    <div>
                        <label for="quick_map_type" class="lbl" style="color:#1e40af;">1. Select Type</label>
                        <div class="select-wrap">
                            <select id="quick_map_type">
                                <option value="all">All Types</option>
                                <option value="academic">📚 Academic Subjects</option>
                                <option value="skill">🎨 Skill Subjects</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="existing_subject_selector" class="lbl" style="color:#1e40af;">2. Select Subject</label>
                        <div class="select-wrap">
                            <select id="existing_subject_selector">
                                <option value="">-- Create a NEW Subject from scratch --</option>
                                
                                @if(isset($existingAcademicSubjects) && $existingAcademicSubjects->isNotEmpty())
                                    @foreach($existingAcademicSubjects as $exSub)
                                        <option value="{{ $exSub->id }}" 
                                                data-name="{{ $exSub->subject_name }}" 
                                                data-code="{{ $exSub->subject_code }}" 
                                                data-short="{{ $exSub->short_name }}" 
                                                data-type="{{ $exSub->subject_type_id }}"
                                                data-category="academic">
                                            {{ $exSub->subject_name }} ({{ $exSub->subject_code }})
                                        </option>
                                    @endforeach
                                @endif

                                @if(isset($existingSkillSubjects) && $existingSkillSubjects->isNotEmpty())
                                    @foreach($existingSkillSubjects as $exSub)
                                        <option value="{{ $exSub->id }}" 
                                                data-name="{{ $exSub->subject_name }}" 
                                                data-code="{{ $exSub->subject_code }}" 
                                                data-short="{{ $exSub->short_name }}" 
                                                data-type="{{ $exSub->subject_type_id }}"
                                                data-category="skill">
                                            {{ $exSub->subject_name }} ({{ $exSub->subject_code }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
                
                <div style="font-size:11px; color:#3b82f6;">
                    Select a subject here to auto-fill details and map to additional standards.
                </div>
            </div>

            <div class="form-row">
                <div>
                    <label for="subject_name" class="lbl">SUBJECT NAME <span class="req">*</span></label>
                    <input type="text" name="subject_name" id="subject_name" value="{{ old('subject_name') }}" maxlength="255" placeholder="Enter Subject Name" required>
                </div>
                <div>
                    <label for="subject_code" class="lbl">SUBJECT CODE <span class="req">*</span></label>
                    <input type="text" name="subject_code" id="subject_code" value="{{ old('subject_code') }}" maxlength="50" placeholder="Enter Subject Code" style="text-transform:uppercase;" required>
                </div>
                <div>
                    <label for="short_name" class="lbl">SHORT NAME</label>
                    <input type="text" name="short_name" id="short_name" value="{{ old('short_name') }}" maxlength="20" placeholder="Enter Short Name">
                </div>
            </div>

            <div class="form-row">
                <div>
                    <label for="subject_type_id" class="lbl">SUBJECT TYPE <span class="req">*</span></label>
                    <div class="select-wrap">
                        <select name="subject_type_id" id="subject_type_id" required>
                            <option value="">Select Subject Type</option>
                            @foreach($subjectTypes as $subjectType)
                                <option value="{{ $subjectType->id }}" {{ (string)old('subject_type_id') === (string)$subjectType->id ? 'selected' : '' }}>
                                    {{ $subjectType->name ?? $subjectType->subject_type ?? $subjectType->type_name ?? $subjectType->description ?? 'Type ' . $subjectType->id }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label for="sort_order" class="lbl">DISPLAY ORDER <span class="req">*</span></label>
                    <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $nextSortOrder) }}" min="0" required>
                </div>
                <div style="display:flex; gap:10px;">
                    <div class="check-box" style="flex:1;">
                        <input type="checkbox" name="is_optional" value="1" id="is_optional" {{ old('is_optional') ? 'checked' : '' }}>
                        <label for="is_optional">Optional</label>
                    </div>
                    <div class="check-box" style="flex:1;">
                        <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label for="is_active">Active</label>
                    </div>
                </div>
            </div>

            <div style="margin-bottom:8px; margin-top:8px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                    <label class="lbl" style="margin-bottom:0;">MAP TO STANDARDS <span class="req">*</span></label>
                    <label style="font-size:11px; font-weight:600; color:#2563eb; cursor:pointer; display:flex; align-items:center; gap:4px;">
                        <input type="checkbox" id="selectAllStandards" style="cursor:pointer;"> Select All
                    </label>
                </div>

                <div class="std-picker">
                    @foreach($standards as $standard)
                        @php $checked = in_array((string)$standard->id, old('standard_ids', []), true); @endphp
                        <label class="std-item {{ $checked ? 'checked' : '' }}">
                            <input type="checkbox" name="standard_ids[]" value="{{ $standard->id }}" {{ $checked ? 'checked' : '' }} onchange="this.closest('.std-item').classList.toggle('checked', this.checked)">
                            <span>{{ $standard->standard_name }}</span>
                        </label>
                    @endforeach
                </div>
                <div style="font-size:10px;color:#9ca3af;margin-top:4px;">
                    Select one or multiple standards to map this subject to.
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:8px;">
                <button type="submit" id="saveSubjectButton" class="btn-save">Save</button>
                <a href="{{ route('subjects.index') }}" class="btn-cancel">Cancel</a>
            </div>

        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form          = document.getElementById('subjectCreateForm');
    const subjectName   = document.getElementById('subject_name');
    const subjectCode   = document.getElementById('subject_code');
    const shortName     = document.getElementById('short_name');
    const subjectType   = document.getElementById('subject_type_id');
    const saveButton    = document.getElementById('saveSubjectButton');
    const selectAll     = document.getElementById('selectAllStandards');
    const stdCheckboxes = document.querySelectorAll('input[name="standard_ids[]"]');
    
    const typeFilter    = document.getElementById('quick_map_type');
    const existingSelect= document.getElementById('existing_subject_selector');

    const allSubjectOptions = Array.from(existingSelect.options).slice(1);

    subjectCode.addEventListener('input', function () {
        this.value = this.value.toUpperCase();
    });

    selectAll.addEventListener('change', function() {
        stdCheckboxes.forEach(cb => {
            cb.checked = this.checked;
            cb.closest('.std-item').classList.toggle('checked', this.checked);
        });
    });

    function resetFields() {
        subjectName.value = '';
        subjectCode.value = '';
        shortName.value   = '';
        subjectType.value = '';
    }

    typeFilter.addEventListener('change', function() {
        const selectedType = this.value;
        existingSelect.innerHTML = '<option value="">-- Create a NEW Subject from scratch --</option>';
        
        allSubjectOptions.forEach(opt => {
            const category = opt.dataset.category;
            let show = false;
            
            if (selectedType === 'all') show = true;
            else if (selectedType === 'academic' && category === 'academic') show = true;
            else if (selectedType === 'skill' && category === 'skill') show = true;
            
            if (show) {
                const newOpt = document.createElement('option');
                newOpt.value = opt.value;
                newOpt.text = opt.text;
                newOpt.dataset.name = opt.dataset.name;
                newOpt.dataset.code = opt.dataset.code;
                newOpt.dataset.short = opt.dataset.short;
                newOpt.dataset.type = opt.dataset.type;
                newOpt.dataset.category = opt.dataset.category;
                existingSelect.appendChild(newOpt);
            }
        });

        existingSelect.value = '';
        resetFields();
    });

    /* QUICK MAP AUTO-FILL LOGIC (No longer locks fields) */
    existingSelect.addEventListener('change', function() {
        if (this.value) {
            const opt = this.options[this.selectedIndex];
            
            // Auto-fill fields
            subjectName.value = opt.dataset.name;
            subjectCode.value = opt.dataset.code;
            shortName.value   = opt.dataset.short || '';
            subjectType.value = opt.dataset.type;

        } else {
            resetFields();
        }
    });

    form.addEventListener('submit', function (event) {
        const checkedStandards = document.querySelectorAll('input[name="standard_ids[]"]:checked');

        if (checkedStandards.length === 0) {
            event.preventDefault();
            alert('Please select at least one Standard.');
            return;
        }

        if (!subjectName.value.trim()) {
            event.preventDefault();
            alert('Please enter Subject Name.');
            subjectName.focus();
            return;
        }

        if (!subjectCode.value.trim()) {
            event.preventDefault();
            alert('Please enter Subject Code.');
            subjectCode.focus();
            return;
        }

        if (!subjectType.value) {
            event.preventDefault();
            alert('Please select Subject Type.');
            subjectType.focus();
            return;
        }

        saveButton.disabled = true;
        saveButton.innerText = 'Saving...';
    });

});
</script>

</x-app-layout>