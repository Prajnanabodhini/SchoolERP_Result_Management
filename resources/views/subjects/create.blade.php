<x-app-layout>

<style>
    /* Compact form styling */
    .subj-form * { box-sizing: border-box; }

    .subj-form label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: #374151;
        margin-bottom: 3px;
        letter-spacing: 0.2px;
    }

    .subj-form .req { color: #dc2626; }

    /* Field wrapper for select-with-arrow */
    .subj-form .select-wrap {
        position: relative;
        width: 100%;
    }

    /* Base input + select styling */
    .subj-form input[type="text"],
    .subj-form input[type="number"],
    .subj-form select {
        width: 100%;
        height: 34px;
        border: 1px solid #9ca3af;
        border-radius: 5px;
        padding: 4px 8px;
        font-size: 12px;
        background: #fff;
        color: #111827;
        outline: none;
        transition: border-color .15s, box-shadow .15s;
    }

    .subj-form input[type="text"]:focus,
    .subj-form input[type="number"]:focus,
    .subj-form select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 2px rgba(37,99,235,.15);
    }

    /* Native select reset (so our custom arrow shows) */
    .subj-form select {
        padding-right: 30px;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        cursor: pointer;
        background-image: none;
    }

    /* Hide the default IE/Edge arrow */
    .subj-form select::-ms-expand { display: none; }

    /* Custom dropdown arrow */
    .subj-form .select-wrap::after {
        content: "";
        position: absolute;
        top: 50%;
        right: 11px;
        width: 0;
        height: 0;
        margin-top: -2px;
        border-left: 4px solid transparent;
        border-right: 4px solid transparent;
        border-top: 5px solid #6b7280;
        pointer-events: none;
        transition: border-top-color .15s;
    }

    .subj-form .select-wrap:hover::after {
        border-top-color: #1f2937;
    }

    /* Grid rows */
    .subj-form .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 10px;
        margin-bottom: 8px;
    }

    .subj-form .form-row.row-3 {
        grid-template-columns: 1fr 1fr auto;
        align-items: end;
        margin-bottom: 6px;
    }

    /* Checkbox boxes */
    .subj-form .check-box {
        display: flex;
        align-items: center;
        gap: 6px;
        height: 34px;
        padding: 0 10px;
        border: 1px solid #d1d5db;
        border-radius: 5px;
        background: #f9fafb;
    }

    .subj-form .check-box input { width: 14px; height: 14px; cursor: pointer; }
    .subj-form .check-box label {
        font-size: 12px;
        font-weight: 600;
        color: #374151;
        cursor: pointer;
        margin: 0;
    }

    /* Buttons */
    .subj-form .btn-save {
        height: 34px;
        padding: 0 22px;
        background: #16a34a;
        color: #fff;
        border: none;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
    }
    .subj-form .btn-save:disabled {
        background: #9ca3af;
        cursor: not-allowed;
    }
    .subj-form .btn-cancel {
        height: 34px;
        padding: 0 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #6b7280;
        color: #fff;
        border-radius: 5px;
        text-decoration: none;
        font-size: 12px;
        font-weight: 700;
    }
    .subj-form .btn-cancel:hover { background: #4b5563; }
</style>


<div style="width:100%;max-width:100%;padding:10px;">

    <div style="width:100%;background:#fff;border-radius:10px;padding:14px 18px;border:1px solid #d1d5db;box-shadow:0 3px 8px rgba(0,0,0,.10);">

        {{-- HEADER --}}
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
            <h2 style="margin:0;color:#16a34a;font-size:19px;font-weight:700;">Add Subject</h2>
            <a href="{{ route('subjects.index') }}"
               style="font-size:12px;color:#6b7280;text-decoration:none;font-weight:600;">
                ← Back to Subject Master
            </a>
        </div>

        {{-- VALIDATION ERRORS --}}
        @if ($errors->any())
            <div style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;padding:7px 10px;border-radius:5px;margin-bottom:8px;font-size:12px;">
                <ul style="margin:0;padding-left:16px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('error'))
            <div style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;padding:7px 10px;border-radius:5px;margin-bottom:8px;font-size:12px;">
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div style="background:#dcfce7;color:#166534;border:1px solid #86efac;padding:7px 10px;border-radius:5px;margin-bottom:8px;font-size:12px;">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('subjects.store') }}" id="subjectCreateForm" class="subj-form">
            @csrf

            {{-- ============================================================
                 ROW 1 — Standard | Subject Name | Subject Code
            ============================================================= --}}
            <div class="form-row">

                {{-- STANDARD --}}
                <div>
                    <label for="standard_id">STANDARD <span class="req">*</span></label>
                    <div class="select-wrap">
                        <select name="standard_id" id="standard_id" required>
                            <option value="">Select Standard</option>
                            <option value="all" {{ old('standard_id') === 'all' ? 'selected' : '' }}>
                                ★ All Standards
                            </option>
                            @foreach($standards as $standard)
                                <option value="{{ $standard->id }}"
                                    {{ (string)old('standard_id') === (string)$standard->id ? 'selected' : '' }}>
                                    {{ $standard->standard_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div style="font-size:10px;color:#9ca3af;margin-top:2px;line-height:1.2;">
                        ★ All Standards maps to every active standard.
                    </div>
                </div>

                {{-- SUBJECT NAME --}}
                <div>
                    <label for="subject_name">SUBJECT NAME <span class="req">*</span></label>
                    <input type="text" name="subject_name" id="subject_name"
                           value="{{ old('subject_name') }}"
                           maxlength="255"
                           placeholder="Enter Subject Name"
                           required>
                </div>

                {{-- SUBJECT CODE --}}
                <div>
                    <label for="subject_code">SUBJECT CODE <span class="req">*</span></label>
                    <input type="text" name="subject_code" id="subject_code"
                           value="{{ old('subject_code') }}"
                           maxlength="50"
                           placeholder="Enter Subject Code"
                           style="text-transform:uppercase;"
                           required>
                </div>

            </div>

            {{-- ============================================================
                 ROW 2 — Short Name | Subject Type | Display Order
            ============================================================= --}}
            <div class="form-row">

                {{-- SHORT NAME --}}
                <div>
                    <label for="short_name">SHORT NAME</label>
                    <input type="text" name="short_name" id="short_name"
                           value="{{ old('short_name') }}"
                           maxlength="20"
                           placeholder="Enter Short Name">
                </div>

                {{-- SUBJECT TYPE --}}
                <div>
                    <label for="subject_type_id">SUBJECT TYPE <span class="req">*</span></label>
                    <div class="select-wrap">
                        <select name="subject_type_id" id="subject_type_id" required>
                            <option value="">Select Subject Type</option>
                            @foreach($subjectTypes as $subjectType)
                                <option value="{{ $subjectType->id }}"
                                    {{ (string)old('subject_type_id') === (string)$subjectType->id ? 'selected' : '' }}>
                                    {{ $subjectType->name
                                        ?? $subjectType->subject_type
                                        ?? $subjectType->type_name
                                        ?? $subjectType->description
                                        ?? 'Type ' . $subjectType->id }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- DISPLAY ORDER --}}
                <div>
                    <label for="sort_order">DISPLAY ORDER <span class="req">*</span></label>
                    <input type="number" name="sort_order" id="sort_order"
                           value="{{ old('sort_order', $nextSortOrder) }}"
                           min="0"
                           required>
                </div>

            </div>

            {{-- ============================================================
                 ROW 3 — Optional | Active | Buttons
            ============================================================= --}}
            <div class="form-row row-3">

                {{-- OPTIONAL --}}
                <div class="check-box">
                    <input type="checkbox" name="is_optional" value="1" id="is_optional"
                           {{ old('is_optional') ? 'checked' : '' }}>
                    <label for="is_optional">Optional Subject</label>
                </div>

                {{-- ACTIVE --}}
                <div class="check-box">
                    <input type="checkbox" name="is_active" value="1" id="is_active"
                           {{ old('is_active', true) ? 'checked' : '' }}>
                    <label for="is_active">Active</label>
                </div>

                {{-- BUTTONS --}}
                <div style="display:flex;gap:8px;height:34px;">
                    <button type="submit" id="saveSubjectButton" class="btn-save">Save</button>
                    <a href="{{ route('subjects.index') }}" class="btn-cancel">Cancel</a>
                </div>

            </div>

            {{-- INFO BANNER --}}
            <div style="background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;padding:7px 12px;border-radius:5px;font-size:11px;line-height:1.4;margin-top:8px;">
                <strong>Note:</strong> Same subject can be reused across standards.
                <strong>All Standards</strong> maps to every active standard; existing mappings are skipped.
                If the Subject Master already exists, the system reuses the existing Subject ID.
            </div>

        </form>

    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const form        = document.getElementById('subjectCreateForm');
    const standard    = document.getElementById('standard_id');
    const subjectName = document.getElementById('subject_name');
    const subjectCode = document.getElementById('subject_code');
    const subjectType = document.getElementById('subject_type_id');
    const saveButton  = document.getElementById('saveSubjectButton');

    /* UPPERCASE SUBJECT CODE */
    subjectCode.addEventListener('input', function () {
        this.value = this.value.toUpperCase();
    });

    /* CLIENT VALIDATION */
    form.addEventListener('submit', function (event) {

        if (!standard.value) {
            event.preventDefault();
            alert('Please select Standard.');
            standard.focus();
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