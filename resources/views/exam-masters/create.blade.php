<x-app-layout>

<style>
/* =========================================================
   PAGE
========================================================= */
.exam-form,
.exam-form * {
    box-sizing: border-box;
    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 13px;
}

.exam-form {
    background: #f0f0f0;
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}

/* =========================================================
   CARD
========================================================= */
.exam-form-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    padding: 18px 20px;
}

/* =========================================================
   TITLES
========================================================= */
.exam-form h2 {
    margin: 0 0 6px !important;
    font-size: 21px !important;
    font-weight: 700 !important;
    color: #1D4ED8 !important;
    text-align: center;
    letter-spacing: 0.4px;
}

.exam-form h3 {
    margin: 0 !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    color: #1e3a8a;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}

/* =========================================================
   FORM LABELS
========================================================= */
.exam-form label.block,
.exam-form .form-label {
    display: block;
    font-size: 11px !important;
    font-weight: 700 !important;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 6px !important;
}

/* =========================================================
   INPUTS + SELECTS
========================================================= */
.exam-form input[type="text"],
.exam-form input[type="number"],
.exam-form select {
    height: 34px !important;
    padding: 4px 10px !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    border: 1px solid #9CA3AF !important;
    border-radius: 6px !important;
    background: #ffffff !important;
    color: #111827 !important;
    outline: none;
    width: 100%;
    transition: border-color .15s, box-shadow .15s;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
}

.exam-form input[type="text"]:focus,
.exam-form input[type="number"]:focus,
.exam-form select:focus {
    border-color: #2563EB !important;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15) !important;
}

.exam-form input[readonly],
.exam-form input:read-only {
    background: #F3F4F6 !important;
    color: #6B7280 !important;
    cursor: not-allowed !important;
}

/* Selects need a custom arrow */
.exam-form select {
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3e%3cpath fill='%236b7280' d='M0 0l5 6 5-6z'/%3e%3c/svg%3e") !important;
    background-repeat: no-repeat !important;
    background-position: right 12px center !important;
    padding-right: 32px !important;
}

/* Checkbox */
.exam-form input[type="checkbox"] {
    width: 16px;
    height: 16px;
    accent-color: #16A34A;
    cursor: pointer;
}

/* =========================================================
   ALERT BOXES
========================================================= */
.exam-form .alert-box {
    margin-bottom: 14px;
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 12.5px;
    font-weight: 600;
    line-height: 1.5;
}
.exam-form .alert-error {
    background: #fee2e2;
    border: 1px solid #fca5a5;
    color: #991b1b;
}
.exam-form .alert-error ul {
    margin: 6px 0 0 20px;
    padding: 0;
}
.exam-form .alert-success {
    background: #dcfce7;
    border: 1px solid #86efac;
    color: #166534;
}

/* =========================================================
   SECTION BOX (Subject Wise Marks)
========================================================= */
.exam-form .section-box {
    background: #fafbfc !important;
    border: 1px solid #d1d5db !important;
    border-radius: 8px !important;
    padding: 14px 16px !important;
    margin-bottom: 16px !important;
}

.exam-form .section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e5e7eb;
}

.exam-form #subjectLoading {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #dbeafe;
    color: #1e40af;
    border: 1px solid #93c5fd;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: 0.3px;
}

/* =========================================================
   PASSING PERCENTAGE NOTE
========================================================= */
.passing-percentage-note {
    margin: 0 0 12px;
    padding: 9px 14px;
    font-size: 12px !important;
    font-weight: 600;
    color: #1e40af;
    background: #eff6ff;
    border: 1px solid #93c5fd;
    border-radius: 8px;
    line-height: 1.45;
}

/* =========================================================
   SUBJECT TABLE
========================================================= */
.subject-table-wrapper {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: #ffffff;
}

table.subj-tbl {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    font-size: 12.5px;
}

table.subj-tbl thead tr {
    background: #1e3a8a;
}

table.subj-tbl th {
    background: #1e3a8a;
    color: #ffffff !important;
    font-weight: 700 !important;
    font-size: 11px !important;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    text-align: center;
    white-space: nowrap;
    padding: 9px 8px;
    border: 1px solid #1e40af;
    line-height: 1.15;
}

table.subj-tbl th:first-child {
    text-align: left;
}

table.subj-tbl td {
    border: 1px solid #d1d5db;
    padding: 8px 10px;
    font-size: 12.5px;
    vertical-align: middle;
    color: #111827;
    line-height: 1.3;
}

table.subj-tbl tbody tr:nth-child(even) {
    background: #f9fafb;
}

table.subj-tbl tbody tr:hover {
    background: #fef9c3;
}

table.subj-tbl td.num {
    text-align: center;
}

table.subj-tbl td.num strong {
    color: #1e3a8a;
    font-weight: 700;
}

.readonly-cell {
    background: #f3f4f6;
    color: #4b5563;
    font-weight: 600;
}

/* Subject code under name */
.subject-code {
    color: #6B7280;
    font-size: 11px !important;
    font-weight: 600;
    margin-top: 3px;
}

/* Compulsory / Optional labels */
.type-compulsory {
    color: #166534;
    font-weight: 700;
    font-size: 11px;
    letter-spacing: 0.3px;
    text-transform: uppercase;
}
.type-optional {
    color: #92400E;
    font-weight: 700;
    font-size: 11px;
    letter-spacing: 0.3px;
    text-transform: uppercase;
}

/* =========================================================
   INFO NOTE
========================================================= */
.info-note {
    background: #eff6ff;
    border: 1px solid #93c5fd;
    color: #1e40af;
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 12.5px !important;
    line-height: 1.55;
    font-weight: 600;
}
.info-note strong { color: #1e3a8a; }

/* =========================================================
   BUTTONS
========================================================= */
.exam-form .erp-btn {
    height: 34px;
    min-width: 130px;
    padding: 0 18px !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    white-space: nowrap;
    cursor: pointer;
    border: 1px solid transparent;
    transition: background .15s, border-color .15s;
}

.exam-form .erp-btn-save {
    background: #16A34A !important;
    color: #ffffff !important;
    border-color: #15803D !important;
}
.exam-form .erp-btn-save:hover {
    background: #15803D !important;
    border-color: #166534 !important;
}
.exam-form .erp-btn-save:disabled {
    background: #9CA3AF !important;
    border-color: #6B7280 !important;
    cursor: not-allowed !important;
    opacity: .85;
}

.exam-form .erp-btn-cancel {
    background: #6B7280 !important;
    color: #ffffff !important;
    border-color: #4B5563 !important;
}
.exam-form .erp-btn-cancel:hover {
    background: #4B5563 !important;
    border-color: #374151 !important;
}

/* Action row */
.exam-form .action-row {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid #e5e7eb;
}

/* =========================================================
   RESPONSIVE
========================================================= */
@media (max-width: 900px) {
    .exam-form { padding: 12px; }
    .exam-form-card { padding: 14px; }
    .exam-form .grid-cols-2 { grid-template-columns: 1fr !important; }
}
</style>


<div class="exam-form">

    <div class="exam-form-card">

        <h2>Add Exam</h2>

        {{-- =====================================================
             ALERTS
        ====================================================== --}}
        @if ($errors->any())
            <div class="alert-box alert-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('error'))
            <div class="alert-box alert-error">{{ session('error') }}</div>
        @endif

        @if(session('success'))
            <div class="alert-box alert-success">{{ session('success') }}</div>
        @endif


        <form method="POST" action="{{ route('exam-masters.store') }}" id="examForm">
            @csrf

            {{-- =========================================================
                 ROW 1: Academic Year + Standard
            ========================================================= --}}
            <div class="grid grid-cols-2 gap-4 mb-5" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;">

                <div>
                    <label class="form-label">Academic Year</label>
                    <select name="academic_year_id" id="academic_year_id" required>
                        <option value="">Select Academic Year</option>
                        @foreach($academicYears as $y)
                            <option value="{{ $y->id }}" {{ (string) old('academic_year_id') === (string) $y->id ? 'selected' : '' }}>
                                {{ $y->year_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Standard</label>
                    <select name="standard_id" id="standard_id" required>
                        <option value="">Select Standard</option>
                        @foreach($standards as $s)
                            <option value="{{ $s->id }}" {{ (string) old('standard_id') === (string) $s->id ? 'selected' : '' }}>
                                {{ $s->standard_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>


            {{-- =========================================================
                 ROW 2: Exam Type + Exam Name
            ========================================================= --}}
            <div class="grid grid-cols-2 gap-4 mb-5" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;">

                <div>
                    <label class="form-label">Exam Type</label>
                    <select id="exam_type" name="exam_type" required>
                        <option value="">Select Exam Type</option>
                        @foreach(['UNIT TEST 1','UNIT TEST 2','UNIT TEST 3','UNIT TEST 4','TERM 1','TERM 2','ANNUAL'] as $t)
                            <option value="{{ $t }}" {{ old('exam_type') === $t ? 'selected' : '' }}>
                                {{ ucwords(strtolower($t)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Exam Name</label>
                    <input type="hidden" name="exam_name" id="exam_name" value="{{ old('exam_name') }}">
                    <input type="text" id="exam_name_preview" value="{{ old('exam_name') }}" readonly>
                </div>

            </div>


            {{-- =========================================================
                 SUBJECT TABLE
            ========================================================= --}}
            <div class="section-box">

                <div class="section-header">
                    <h3>Subject Wise Marks (from Excel Structure)</h3>
                    <span id="subjectLoading" style="display:none;">Loading subjects...</span>
                </div>

                <div id="passingPercentageNote" class="passing-percentage-note" style="display:none;"></div>

                <div class="subject-table-wrapper">

                    <table class="subj-tbl">
                        <thead>
                            <tr>
                                <th style="width:18%;text-align:left;">Subject</th>
                                <th>Theory<br>Max</th>
                                <th>Theory<br>Pass</th>
                                <th>Oral<br>Max</th>
                                <th>Oral<br>Pass</th>
                                <th>Practical<br>Max</th>
                                <th>Practical<br>Pass</th>
                                <th>Total<br>Max</th>
                                <th>Total<br>Pass</th>
                                <th style="width:9%;">Type</th>
                            </tr>
                        </thead>
                        <tbody id="subjectTableBody">
                            <tr>
                                <td colspan="10" style="padding:20px;text-align:center;color:#6b7280;background:#fffbeb;font-weight:600;">
                                    Select Academic Year, Standard and Exam Type
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div>
            </div>


            {{-- =========================================================
                 DISPLAY ORDER
            ========================================================= --}}
            <div style="margin-bottom:16px;">
                <label class="form-label">Display Order</label>
                <input type="number" id="display_order" name="display_order"
                       value="{{ old('display_order', $nextDisplayOrder) }}"
                       readonly
                       style="max-width:220px;">
            </div>


            {{-- =========================================================
                 IS ACTIVE
            ========================================================= --}}
            <div style="margin-bottom:16px;">
                <label style="display:inline-flex;align-items:center;gap:8px;cursor:pointer;font-weight:700;color:#374151;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <span>Active</span>
                </label>
            </div>


            {{-- =========================================================
                 INFO NOTE
            ========================================================= --}}
            <div class="info-note" style="margin-bottom:16px;">
                <strong>Note:</strong> The marks structure (Theory / Oral / Practical / Total) is loaded from the Academic Exam Structure. Select Academic Year, Standard and Exam Type, then click <strong>Save</strong>.
            </div>


            {{-- =========================================================
                 ACTION ROW
            ========================================================= --}}
            <div class="action-row">
                <a href="{{ route('exam-masters.index') }}" class="erp-btn erp-btn-cancel">
                    Cancel
                </a>
                <button type="submit" class="erp-btn erp-btn-save" id="saveExamButton">
                    Save
                </button>
            </div>

        </form>

    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const acY   = document.getElementById('academic_year_id');
    const std   = document.getElementById('standard_id');
    const et    = document.getElementById('exam_type');
    const eName = document.getElementById('exam_name');
    const ePrev = document.getElementById('exam_name_preview');
    const tbody = document.getElementById('subjectTableBody');
    const load  = document.getElementById('subjectLoading');
    const note  = document.getElementById('passingPercentageNote');
    const form  = document.getElementById('examForm');
    const saveBtn = document.getElementById('saveExamButton');

    const esc = v => String(v ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    const fmt = v => { const n = Number(v || 0); return n <= 0 ? '—' : String(n); };

    function buildExamName() {
        const type = et.value;
        const opt  = std.options[std.selectedIndex];
        const stdT = opt && opt.value ? opt.text.trim() : '';
        if (!type || !stdT) { eName.value=''; ePrev.value=''; return; }
        const g = type + ' - ' + stdT;
        eName.value = g; ePrev.value = g;
    }

    async function loadSubjects() {
        const stdId = std.value, examT = et.value;
        if (!acY.value) { tbody.innerHTML = `<tr><td colspan="10" style="padding:20px;text-align:center;color:#6b7280;background:#fffbeb;font-weight:600;">Select Academic Year First</td></tr>`; return; }
        if (!stdId || !examT) { tbody.innerHTML = `<tr><td colspan="10" style="padding:20px;text-align:center;color:#6b7280;background:#fffbeb;font-weight:600;">Select Standard and Exam Type</td></tr>`; return; }

        load.style.display = 'inline-flex';
        tbody.innerHTML = `<tr><td colspan="10" style="padding:20px;text-align:center;color:#6b7280;">Loading...</td></tr>`;

        try {
            const url = "{{ url('/exam-masters/load-subjects') }}/" + encodeURIComponent(stdId) + '?exam_type=' + encodeURIComponent(examT);
            const resp = await fetch(url, { headers: { 'Accept':'application/json','X-Requested-With':'XMLHttpRequest' }});
            if (!resp.ok) throw new Error('HTTP ' + resp.status);
            const data = await resp.json();
            const subjects = Array.isArray(data) ? data : (data.subjects || []);
            renderSubjects(subjects);
            if (data.passing_percentage) {
                note.textContent = 'Standard: ' + (data.standard_name||'') + ' | Sheet: ' + (data.sheet||'-') + ' | Passing %: ' + data.passing_percentage;
                note.style.display = 'block';
            }
        } catch (err) {
            console.error(err);
            tbody.innerHTML = `<tr><td colspan="10" style="padding:20px;text-align:center;color:#991b1b;background:#fee2e2;font-weight:600;">Unable to load subjects.</td></tr>`;
        } finally {
            load.style.display = 'none';
        }
    }

    function renderSubjects(subjects) {
        if (!Array.isArray(subjects) || subjects.length === 0) {
            tbody.innerHTML = `<tr><td colspan="10" style="padding:20px;text-align:center;color:#991b1b;background:#fee2e2;font-weight:600;">No active subjects mapped.</td></tr>`;
            return;
        }
        let html = '';
        subjects.forEach((s, i) => {
            const id = s.subject_id ?? s.id ?? '';
            const optional = Number(s.is_optional ?? 0) === 1;
            const tMax = Number(s.total_max_marks ?? s.max_marks ?? 0);
            const tPass = Number(s.total_passing_marks ?? s.passing_marks ?? 0);
            html += `
                <tr>
                    <td>
                        <div style="font-weight:700;color:#111827;">${esc(s.subject_name)}</div>
                        ${s.subject_code ? `<div class="subject-code">Code: ${esc(s.subject_code)}</div>` : ''}
                        <input type="hidden" name="subjects[${esc(id)}][subject_id]" value="${esc(id)}">
                        <input type="hidden" name="subjects[${esc(id)}][display_order]" value="${esc(s.sort_order ?? (i+1))}">
                        <input type="hidden" name="subjects[${esc(id)}][max_marks]" value="${esc(tMax)}">
                        <input type="hidden" name="subjects[${esc(id)}][passing_marks]" value="${esc(tPass)}">
                    </td>
                    <td class="num readonly-cell">${fmt(s.theory_max_marks)}</td>
                    <td class="num readonly-cell">${fmt(s.theory_passing_marks)}</td>
                    <td class="num readonly-cell">${fmt(s.oral_max_marks)}</td>
                    <td class="num readonly-cell">${fmt(s.oral_passing_marks)}</td>
                    <td class="num readonly-cell">${fmt(s.practical_max_marks)}</td>
                    <td class="num readonly-cell">${fmt(s.practical_passing_marks)}</td>
                    <td class="num"><strong>${fmt(tMax)}</strong></td>
                    <td class="num"><strong>${fmt(tPass)}</strong></td>
                    <td class="num">${optional ? '<span class="type-optional">Optional</span>' : '<span class="type-compulsory">Compulsory</span>'}</td>
                </tr>`;
        });
        tbody.innerHTML = html;
    }

    acY.addEventListener('change', loadSubjects);
    std.addEventListener('change', () => { buildExamName(); loadSubjects(); });
    et.addEventListener('change',  () => { buildExamName(); loadSubjects(); });

    form.addEventListener('submit', function (e) {
        if (!acY.value) { e.preventDefault(); alert('Please select Academic Year.'); return; }
        if (!std.value) { e.preventDefault(); alert('Please select Standard.'); return; }
        if (!et.value)  { e.preventDefault(); alert('Please select Exam Type.'); return; }
        if (tbody.querySelectorAll('input[name$="[subject_id]"]').length === 0) {
            e.preventDefault(); alert('No subjects loaded.'); return;
        }
        buildExamName();
        saveBtn.disabled = true; saveBtn.innerText = 'Saving...';
    });

    buildExamName();
});
</script>

</x-app-layout>