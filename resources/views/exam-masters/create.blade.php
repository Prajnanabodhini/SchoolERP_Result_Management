<x-app-layout>

<style>
.exam-form, .exam-form * { font-family: Arial, sans-serif !important; font-size: 12px !important; }
.exam-form h2 { font-size:18px !important; font-weight:600 !important; }
.exam-form h3 { font-size:14px !important; font-weight:600 !important; }
.exam-form input[type="text"], .exam-form input[type="number"] { height:30px !important; padding:4px 8px !important; }
.exam-form select { height:34px !important; }
.exam-form input[type="checkbox"] { width:16px; height:16px; }
.exam-form .erp-btn { font-size:12px !important; padding:5px 12px !important; }
.subject-code { color:#6b7280; font-size:11px !important; margin-top:2px; }
.passing-percentage-note { margin:8px 0 10px; font-size:11px !important; color:#2563eb; font-weight:600; }
.info-note { background:#eff6ff; border:1px solid #bfdbfe; color:#1e40af; padding:10px 12px; border-radius:6px; font-size:12px !important; line-height:1.5; }
table.subj-tbl { width:100%; border-collapse:collapse; background:#fff; }
table.subj-tbl th, table.subj-tbl td { border:1px solid #d1d5db; padding:6px 7px; font-size:12px; vertical-align:middle; }
table.subj-tbl th { background:#dbeafe; font-weight:700; text-align:center; white-space:nowrap; }
table.subj-tbl td.num { text-align:center; }
.readonly-cell { background:#f3f4f6; }
</style>

<div class="exam-form" style="max-width:1200px;margin:auto;padding:15px;">
<div style="background:#fff;border-radius:12px;padding:20px;border:1px solid #d1d5db;box-shadow:0 4px 10px rgba(0,0,0,.15);">

    <h2 class="text-center text-green-600 mb-4">Add Exam</h2>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <ul class="list-disc ml-5">@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
        </div>
    @endif
    @if(session('error'))   <div class="bg-red-100 text-red-700 p-3 rounded mb-4">{{ session('error') }}</div> @endif
    @if(session('success')) <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div> @endif

    <form method="POST" action="{{ route('exam-masters.store') }}" id="examForm">
        @csrf

        {{-- ROW 1: Academic Year + Standard --}}
        <div class="grid grid-cols-2 gap-4 mb-5">
            <div>
                <label class="block font-semibold mb-2">Academic Year</label>
                <select name="academic_year_id" id="academic_year_id" class="w-full border rounded p-2" required>
                    <option value="">Select Academic Year</option>
                    @foreach($academicYears as $y)
                        <option value="{{ $y->id }}" {{ (string) old('academic_year_id') === (string) $y->id ? 'selected' : '' }}>{{ $y->year_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-2">Standard</label>
                <select name="standard_id" id="standard_id" class="w-full border rounded p-2" required>
                    <option value="">Select Standard</option>
                    @foreach($standards as $s)
                        <option value="{{ $s->id }}" {{ (string) old('standard_id') === (string) $s->id ? 'selected' : '' }}>{{ $s->standard_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- ROW 2: Exam Type + Exam Name (side by side) --}}
        <div class="grid grid-cols-2 gap-4 mb-5">
            <div>
                <label class="block font-semibold mb-2">Exam Type</label>
                <select id="exam_type" name="exam_type" class="w-full border rounded p-2" required>
                    <option value="">Select Exam Type</option>
                    @foreach(['UNIT TEST 1','UNIT TEST 2','UNIT TEST 3','UNIT TEST 4','TERM 1','TERM 2','ANNUAL'] as $t)
                        <option value="{{ $t }}" {{ old('exam_type') === $t ? 'selected' : '' }}>{{ ucwords(strtolower($t)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-2">Exam Name</label>
                <input type="hidden" name="exam_name" id="exam_name" value="{{ old('exam_name') }}">
                <input type="text" id="exam_name_preview" value="{{ old('exam_name') }}" readonly class="w-full border rounded p-2 bg-gray-100">
            </div>
        </div>

        <div class="border rounded section-box mb-6 bg-gray-50" style="padding:12px;">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-bold">Subject Wise Marks (from Excel Structure)</h3>
                <span id="subjectLoading" class="text-blue-600" style="display:none;">Loading subjects...</span>
            </div>
            <div id="passingPercentageNote" class="passing-percentage-note" style="display:none;"></div>
            <div class="overflow-x-auto">
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
                        <tr><td colspan="10" class="border p-3 text-center text-gray-500">Select Academic Year, Standard and Exam Type</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mb-5">
            <label class="block font-semibold mb-2">Display Order</label>
            <input type="number" id="display_order" name="display_order" value="{{ old('display_order', $nextDisplayOrder) }}" readonly class="w-full border rounded p-2 bg-gray-100">
        </div>

        <div class="mb-5">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                <span>Active</span>
            </label>
        </div>

        <div class="info-note mb-5">
            <strong>Note:</strong> The marks structure (Theory / Oral / Practical / Total) is loaded from the Academic Exam Structure. Select Academic Year, Standard and Exam Type, then click <strong>Save</strong>.
        </div>

        <div class="flex justify-between items-center mt-6">
            <div></div>
            <div class="flex gap-2">
                <button type="submit" class="erp-btn erp-btn-save" id="saveExamButton">Save</button>
                <a href="{{ route('exam-masters.index') }}" class="erp-btn erp-btn-cancel">Cancel</a>
            </div>
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
        if (!acY.value) { tbody.innerHTML = `<tr><td colspan="10" class="border p-3 text-center text-gray-500">Select Academic Year First</td></tr>`; return; }
        if (!stdId || !examT) { tbody.innerHTML = `<tr><td colspan="10" class="border p-3 text-center text-gray-500">Select Standard and Exam Type</td></tr>`; return; }

        load.style.display = 'inline';
        tbody.innerHTML = `<tr><td colspan="10" class="border p-3 text-center">Loading...</td></tr>`;

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
            tbody.innerHTML = `<tr><td colspan="10" class="border p-3 text-center text-red-600">Unable to load subjects.</td></tr>`;
        } finally {
            load.style.display = 'none';
        }
    }

    function renderSubjects(subjects) {
        if (!Array.isArray(subjects) || subjects.length === 0) {
            tbody.innerHTML = `<tr><td colspan="10" class="border p-3 text-center text-red-600">No active subjects mapped.</td></tr>`;
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
                        <div style="font-weight:600;">${esc(s.subject_name)}</div>
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
                    <td class="num">${optional ? '<span style="color:#92400e;font-weight:600;">Optional</span>' : '<span style="color:#166534;font-weight:600;">Compulsory</span>'}</td>
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