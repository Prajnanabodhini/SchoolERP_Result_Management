<x-app-layout>

<style>
    .subj-form * { box-sizing: border-box; }

    .subj-form label.lbl {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: #374151;
        margin-bottom: 3px;
        letter-spacing: 0.2px;
    }
    .subj-form .req { color: #dc2626; }

    .subj-form .select-wrap { position: relative; width: 100%; }

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
        outline: none;
    }

    .subj-form input[readonly] { background: #f3f4f6; color: #6b7280; }

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
        margin-bottom: 8px;
    }

    .subj-form .check-box {
        display: flex; align-items: center; gap: 6px;
        height: 34px; padding: 0 10px;
        border: 1px solid #d1d5db; border-radius: 5px; background: #f9fafb;
    }
    .subj-form .check-box input { width: 14px; height: 14px; cursor: pointer; }
    .subj-form .check-box label {
        font-size: 12px; font-weight: 600; color: #374151; margin: 0; cursor: pointer;
    }

    /* Standards checklist (master-only mode) */
    .std-picker {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 6px;
        max-height: 220px;
        overflow-y: auto;
        padding: 10px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background: #f9fafb;
    }
    .std-item {
        display: flex; align-items: center; gap: 6px;
        padding: 6px 10px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 600;
        color: #374151;
        cursor: pointer;
        transition: background .1s, border-color .1s;
    }
    .std-item:hover { background: #eff6ff; border-color: #93c5fd; }
    .std-item input { width: 14px; height: 14px; cursor: pointer; }
    .std-item.checked { background: #dbeafe; border-color: #3b82f6; color: #1e3a8a; }

    .subj-form .btn-update {
        height: 34px; padding: 0 22px;
        background: #ca8a04; color: #fff;
        border: none; border-radius: 5px;
        font-size: 12px; font-weight: 700; cursor: pointer;
    }
    .subj-form .btn-update:disabled { background: #9ca3af; cursor: not-allowed; }
    .subj-form .btn-cancel {
        height: 34px; padding: 0 22px;
        display: inline-flex; align-items: center; justify-content: center;
        background: #6b7280; color: #fff; border-radius: 5px;
        text-decoration: none; font-size: 12px; font-weight: 700;
    }
</style>


<div style="width:100%;max-width:100%;padding:10px;">

    <div style="width:100%;background:#fff;border-radius:10px;padding:14px 18px;border:1px solid #d1d5db;box-shadow:0 3px 8px rgba(0,0,0,.10);">

        {{-- HEADER --}}
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
            <h2 style="margin:0;color:#ca8a04;font-size:19px;font-weight:700;">
                {{ $mapping ? 'Edit Subject Mapping' : 'Edit Subject Master' }}
            </h2>
            <a href="{{ route('subjects.index', ['tab' => $mapping ? 'academic' : 'skill']) }}"
               style="font-size:12px;color:#6b7280;text-decoration:none;font-weight:600;">
                ← Back to Subjects
            </a>
        </div>

        {{-- MODE HINT --}}
        @if(!$mapping)
            <div style="background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;padding:8px 12px;border-radius:6px;font-size:12px;margin-bottom:12px;">
                This subject is currently <strong>unmapped</strong>. Edit its details and select the standards below to map it.
            </div>
        @endif

        {{-- FLASH --}}
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

        <form method="POST"
              action="{{ $mapping
                        ? route('subjects.update', $mapping->id)
                        : route('subjects.update-master', $masterSubject->id) }}"
              id="subjectEditForm"
              class="subj-form">
            @csrf
            @method('PUT')

            {{-- ROW 1 --}}
            <div class="form-row">
                <div>
                    <label class="lbl">SUBJECT MASTER ID</label>
                    <input type="text" value="{{ $masterSubject->id }}" readonly tabindex="-1">
                </div>
                <div>
                    <label for="subject_name" class="lbl">SUBJECT NAME <span class="req">*</span></label>
                    <input type="text" name="subject_name" id="subject_name"
                           value="{{ old('subject_name', $masterSubject->subject_name) }}"
                           maxlength="255" required>
                </div>
                <div>
                    <label for="subject_code" class="lbl">SUBJECT CODE <span class="req">*</span></label>
                    <input type="text" name="subject_code" id="subject_code"
                           value="{{ old('subject_code', $masterSubject->subject_code) }}"
                           maxlength="50" style="text-transform:uppercase;" required>
                </div>
            </div>

            {{-- ROW 2 --}}
            <div class="form-row">
                <div>
                    <label for="short_name" class="lbl">SHORT NAME</label>
                    <input type="text" name="short_name" id="short_name"
                           value="{{ old('short_name', $masterSubject->short_name) }}"
                           maxlength="20">
                </div>
                <div>
                    <label for="subject_type_id" class="lbl">SUBJECT TYPE <span class="req">*</span></label>
                    <div class="select-wrap">
                        <select name="subject_type_id" id="subject_type_id" required>
                            <option value="">Select Subject Type</option>
                            @foreach($subjectTypes as $type)
                                <option value="{{ $type->id }}"
                                    {{ (string)old('subject_type_id', $masterSubject->subject_type_id) === (string)$type->id ? 'selected' : '' }}>
                                    {{ $type->name ?? $type->subject_type ?? $type->type_name ?? $type->description ?? 'Type ' . $type->id }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label for="sort_order" class="lbl">
                        {{ $mapping ? 'DISPLAY ORDER' : 'DISPLAY ORDER (Master)' }}
                    </label>
                    <input type="number" name="sort_order" id="sort_order"
                           value="{{ old('sort_order', $mapping->sort_order ?? $masterSubject->display_order ?? 0) }}"
                           min="0" required>
                </div>
            </div>

            {{-- ROW 3 --}}
            <div class="form-row">
                @if($mapping)
                    <div>
                        <label class="lbl">&nbsp;</label>
                        <div class="check-box">
                            <input type="checkbox" name="is_optional" value="1" id="is_optional"
                                {{ old('is_optional', $mapping->is_optional) ? 'checked' : '' }}>
                            <label for="is_optional">Optional Subject</label>
                        </div>
                    </div>
                @else
                    <div></div>
                @endif

                <div>
                    <label class="lbl">&nbsp;</label>
                    <div class="check-box">
                        <input type="checkbox" name="is_active" value="1" id="is_active"
                            {{ old('is_active', $mapping->is_active ?? $masterSubject->is_active) ? 'checked' : '' }}>
                        <label for="is_active">Active</label>
                    </div>
                </div>

                <div></div>
            </div>

            {{-- STANDARDS --}}
            @if($mapping)

                <div style="margin-bottom:8px;">
                    <label for="standard_id" class="lbl">STANDARD</label>
                    <div class="select-wrap">
                        <select name="standard_id" id="standard_id" required>
                            <option value="">Select Standard</option>
                            @if(count($mappedStandardIds) > 1)
                                <option value="all" {{ old('standard_id') === 'all' ? 'selected' : '' }}>
                                    ★ All Standards (apply to all {{ count($mappedStandardIds) }} mappings)
                                </option>
                            @endif
                            @foreach($standards as $standard)
                                <option value="{{ $standard->id }}"
                                    {{ (string)old('standard_id', $mapping->standard_id) === (string)$standard->id ? 'selected' : '' }}>
                                    {{ $standard->standard_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div style="font-size:10px;color:#9ca3af;margin-top:2px;">
                        @if(count($mappedStandardIds) > 1)
                            Shared by {{ count($mappedStandardIds) }} standards — All Standards updates all mappings at once.
                        @else
                            Moving changes this mapping's standard.
                        @endif
                    </div>
                </div>

            @else

                <div style="margin-bottom:12px;">
                    <label class="lbl" style="margin-bottom:6px;">
                        MAP TO STANDARDS
                        <span style="color:#6b7280;font-weight:600;">({{ count($mappedStandardIds) }} selected)</span>
                    </label>

                    <div class="std-picker">
                        @foreach($standards as $standard)
                            @php $checked = in_array((int)$standard->id, $mappedStandardIds, true); @endphp
                            <label class="std-item {{ $checked ? 'checked' : '' }}">
                                <input type="checkbox"
                                       name="standard_ids[]"
                                       value="{{ $standard->id }}"
                                       {{ $checked ? 'checked' : '' }}
                                       onchange="this.closest('.std-item').classList.toggle('checked', this.checked)">
                                <span>{{ $standard->standard_name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

            @endif

            {{-- BUTTONS --}}
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:6px;">
                <button type="submit" id="updateSubjectButton" class="btn-update">Update</button>
                <a href="{{ route('subjects.index', ['tab' => $mapping ? 'academic' : 'skill']) }}"
                   class="btn-cancel">Cancel</a>
            </div>

        </form>

    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('subjectEditForm');
    const code = document.getElementById('subject_code');
    const btn  = document.getElementById('updateSubjectButton');
    const std  = document.getElementById('standard_id');

    code.addEventListener('input', function () { this.value = this.value.toUpperCase(); });

    form.addEventListener('submit', function (e) {
        if (std && !std.value) {
            e.preventDefault();
            alert('Please select Standard.');
            std.focus();
            return;
        }
        btn.disabled = true;
        btn.innerText = 'Updating...';
    });
});
</script>

</x-app-layout>