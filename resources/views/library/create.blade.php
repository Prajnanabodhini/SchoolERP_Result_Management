<x-app-layout>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.default.min.css">

<style>
.lib-form-page, .lib-form-page * { font-family: Arial, sans-serif !important; font-size: 13px; box-sizing: border-box; }
.lib-form-page h2 { font-size: 21px !important; font-weight: 700 !important; color: #1D4ED8 !important; margin: 0; text-transform: uppercase; letter-spacing: 0.3px; }
.lib-form-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; }
.lib-form-card { width: 100%; background: #fff; border: 1px solid #d1d5db; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.05); overflow: visible; }
.lib-tabs { display: flex; border-bottom: 1px solid #e5e7eb; background: #f9fafb; }
.lib-tab { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 48px; padding: 0 20px; border: 0; background: transparent; cursor: pointer; font-size: 14px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.4px; border-bottom: 3px solid transparent; transition: color .15s, background .15s, border-color .15s; }
.lib-tab:hover { color: #1d4ed8; background: #eff6ff; }
.lib-tab.active { color: #1d4ed8; background: #fff; border-bottom-color: #2563eb; }
.lib-tab-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: currentColor; opacity: 0.75; }

.lib-form-body { padding: 22px; }
.lib-pane { display: none; }
.lib-pane.active { display: block; }

.lib-section-title { font-size: 12px; font-weight: 700; color: #1e40af; text-transform: uppercase; letter-spacing: 0.5px; padding-bottom: 8px; margin-bottom: 14px; border-bottom: 2px solid #dbeafe; }
.lib-row { display: grid; gap: 16px 20px; margin-bottom: 16px; }
.lib-row.cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.lib-row.cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
.lib-group { display: flex; flex-direction: column; gap: 6px; position: relative; }
.lib-label { font-size: 11px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: .3px; }
.lib-label .req { color: #dc2626; }

.lib-select-wrap { position: relative; }
.lib-control { height: 38px; padding: 6px 34px 6px 12px; border: 1px solid #9ca3af; border-radius: 5px; background: #fff; color: #111827; font-size: 13px; font-weight: 600; outline: none; width: 100%; appearance: none; -webkit-appearance: none; -moz-appearance: none; cursor: pointer; transition: border-color .12s, box-shadow .12s; }
.lib-control:focus { border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,.15); }
.lib-control:disabled { background: #f3f4f6; color: #9ca3af; cursor: not-allowed; }
.lib-control.text { padding-right: 12px; cursor: text; }
.lib-control.text[readonly] { background: #f9fafb; cursor: default; }

.lib-arrow { position: absolute; top: 50%; right: 12px; transform: translateY(-50%); width: 0; height: 0; border-left: 5px solid transparent; border-right: 5px solid transparent; border-top: 6px solid #6b7280; pointer-events: none; }
.lib-select-wrap.disabled .lib-arrow { border-top-color: #cbd5e1; }

.lib-alert { padding: 10px 14px; border-radius: 6px; margin-bottom: 14px; font-weight: 700; }
.lib-alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

.lib-form-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 22px; border-top: 1px solid #e5e7eb; background: #f9fafb; }
.lib-btn { display: inline-flex; align-items: center; justify-content: center; height: 38px; min-width: 130px; padding: 0 20px; border-radius: 5px; font-size: 13px; font-weight: 700; text-decoration: none; cursor: pointer; border: 1px solid transparent; white-space: nowrap; box-sizing: border-box; transition: background .15s; }
.lib-btn-primary { background: #16a34a; color: #fff; border-color: #15803d; }
.lib-btn-primary:hover { background: #15803d; }
.lib-btn-gray { background: #6b7280; color: #fff; border-color: #4b5563; }
.lib-btn-gray:hover { background: #4b5563; }
.lib-back-link { display: inline-flex; align-items: center; gap: 6px; padding: 0 14px; height: 38px; border: 1px solid #d1d5db; border-radius: 5px; background: #f9fafb; color: #374151; font-size: 13px; font-weight: 700; text-decoration: none; transition: background .12s; }
.lib-back-link:hover { background: #f3f4f6; color: #111827; }

/* ---------- Book selector container ---------- */
.book-select-container { max-width: 520px; width: 100%; }

/* ---------- Tom Select — flattened ---------- */
.ts-wrapper,
.ts-wrapper.single,
.ts-wrapper.single.input-active {
    width: 100% !important;
    border: 0 !important;
    background: transparent !important;
    padding: 0 !important;
    box-shadow: none !important;
    min-height: 0 !important;
}

.ts-wrapper .ts-control,
.ts-wrapper.single .ts-control {
    min-height: 38px !important;
    height: 38px !important;
    padding: 6px 40px 6px 12px !important;
    border: 1px solid #9ca3af !important;
    border-radius: 5px !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    background: #fff !important;
    color: #111827 !important;
    box-shadow: none !important;
    line-height: 24px !important;
    display: flex !important;
    align-items: center !important;
}

.ts-wrapper.focus .ts-control,
.ts-wrapper.single.focus .ts-control {
    border-color: #2563eb !important;
    box-shadow: 0 0 0 2px rgba(37,99,235,.15) !important;
}

.ts-wrapper .ts-control > input {
    font-weight: 600 !important;
    font-size: 13px !important;
    height: 24px !important;
    padding: 0 !important;
    margin: 0 !important;
    border: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
}

.ts-wrapper .ts-control > .item {
    font-weight: 600 !important;
    font-size: 13px !important;
    color: #111827 !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    max-width: calc(100% - 40px) !important;
}

.ts-wrapper .ts-control > .placeholder {
    color: #9ca3af !important;
    font-weight: 600 !important;
    font-size: 13px !important;
}

.ts-dropdown {
    z-index: 9999 !important;
    border: 1px solid #9ca3af !important;
    border-radius: 5px !important;
    font-size: 13px !important;
    box-shadow: 0 6px 16px rgba(0,0,0,.12) !important;
    padding: 4px 0 !important;
    margin-top: 2px !important;
    background: #fff !important;
    width: 100% !important;
    max-width: 100% !important;
}
.ts-dropdown .option {
    padding: 6px 12px !important;
    border-bottom: 1px solid #f3f4f6;
    cursor: pointer !important;
}
.ts-dropdown .option:last-child { border-bottom: 0; }
.ts-dropdown .option.active,
.ts-dropdown .option:hover {
    background: #eff6ff !important;
    color: #1e40af !important;
}

.ts-rich-option .ts-opt-title {
    font-weight: 700;
    color: #111827;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 13px;
}
.ts-rich-option .ts-opt-sub {
    font-size: 11px;
    color: #6b7280;
    margin-top: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ts-dropdown .option.active .ts-opt-title,
.ts-dropdown .option:hover .ts-opt-title { color: #1e40af; }
.ts-dropdown .option.active .ts-opt-sub,
.ts-dropdown .option:hover .ts-opt-sub { color: #1e40af; }

.ts-wrapper .clear-button {
    top: 50% !important;
    right: 10px !important;
    transform: translateY(-50%) !important;
    margin: 0 !important;
    padding: 0 4px !important;
    color: #6b7280 !important;
    font-size: 16px !important;
    line-height: 1 !important;
    background: transparent !important;
    border: 0 !important;
}
.ts-wrapper .clear-button:hover { color: #dc2626 !important; }

.ts-wrapper.single .ts-control::after {
    content: '' !important;
    position: absolute !important;
    top: 50% !important;
    right: 14px !important;
    margin: 0 !important;
    transform: translateY(-50%) !important;
    border-left: 5px solid transparent !important;
    border-right: 5px solid transparent !important;
    border-top: 6px solid #6b7280 !important;
    width: 0 !important;
    height: 0 !important;
    pointer-events: none !important;
}

@media (max-width: 900px) {
    .lib-row.cols-2, .lib-row.cols-3 { grid-template-columns: 1fr; }
    .book-select-container { max-width: 100%; }
}
</style>


<div class="lib-form-page p-3">

    <div class="lib-form-header">
        <h2>Issue a Book</h2>
        <a href="{{ route('library.index') }}" class="lib-back-link">
            <span style="font-size:16px;line-height:1;">←</span> Back to List
        </a>
    </div>

    @if(session('error'))
        <div class="lib-alert lib-alert-error">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="lib-alert lib-alert-error">
            <ul style="margin:0;padding-left:18px;">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('library.store') }}" id="issueForm">
        @csrf

        <input type="hidden" name="borrower_type" id="borrower_type" value="student">
        <input type="hidden" name="borrower_id"   id="borrower_id">
        <input type="hidden" name="borrower_name" id="borrower_name">
        <input type="hidden" name="borrower_class" id="borrower_class_hidden" value="">

        <div class="lib-form-card">

            <div class="lib-tabs">
                <button type="button" class="lib-tab active" id="tab-student" onclick="switchTab('student')">
                    <span class="lib-tab-dot"></span> Student
                </button>
                <button type="button" class="lib-tab" id="tab-teacher" onclick="switchTab('teacher')">
                    <span class="lib-tab-dot"></span> Teacher
                </button>
            </div>


            <div class="lib-form-body">

                {{-- STUDENT PANE --}}
                <div class="lib-pane active" id="pane-student">
                    <div class="lib-section-title">Borrower — Student</div>

                    <div class="lib-row cols-3">
                        <div class="lib-group">
                            <label class="lib-label" for="student_academic_year_id">Academic Year <span class="req">*</span></label>
                            <div class="lib-select-wrap">
                                <select name="academic_year_id" id="student_academic_year_id" class="lib-control" onchange="resetStudentCascade()">
                                    <option value="">Select Academic Year</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ session('yearid') == $year->id ? 'selected' : '' }}>
                                            {{ $year->year_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="lib-arrow"></span>
                            </div>
                        </div>
                        <div class="lib-group">
                            <label class="lib-label" for="student_standard_id">Standard <span class="req">*</span></label>
                            <div class="lib-select-wrap">
                                <select name="standard_id" id="student_standard_id" class="lib-control" onchange="resetDivisionAndStudents()">
                                    <option value="">Select Standard</option>
                                    @foreach($standards as $standard)
                                        <option value="{{ $standard->id }}">{{ $standard->standard_name }}</option>
                                    @endforeach
                                </select>
                                <span class="lib-arrow"></span>
                            </div>
                        </div>
                        <div class="lib-group">
                            <label class="lib-label" for="student_division_id">Division <span class="req">*</span></label>
                            <div class="lib-select-wrap">
                                <select name="division_id" id="student_division_id" class="lib-control" onchange="loadStudents()">
                                    <option value="">Select Division</option>
                                    @foreach($divisions as $division)
                                        <option value="{{ $division->id }}">{{ $division->division_name }}</option>
                                    @endforeach
                                </select>
                                <span class="lib-arrow"></span>
                            </div>
                        </div>
                    </div>

                    <div class="lib-row cols-2">
                        <div class="lib-group">
                            <label class="lib-label" for="student_id">Student <span class="req">*</span></label>
                            <div class="lib-select-wrap disabled" id="student_select_wrap">
                                <select id="student_id" class="lib-control" disabled onchange="onStudentSelected()">
                                    <option value="">Select Standard + Division first</option>
                                </select>
                                <span class="lib-arrow"></span>
                            </div>
                        </div>
                        <div class="lib-group">
                            <label class="lib-label" for="student_class">Class (auto-filled)</label>
                            <input type="text" id="student_class" class="lib-control text" readonly>
                        </div>
                    </div>
                </div>


                {{-- TEACHER PANE --}}
                <div class="lib-pane" id="pane-teacher">
                    <div class="lib-section-title">Borrower — Teacher</div>

                    <div class="lib-row cols-3">
                        <div class="lib-group">
                            <label class="lib-label" for="teacher_standard_id">Standard <span class="req">*</span></label>
                            <div class="lib-select-wrap">
                                <select name="standard_id" id="teacher_standard_id" class="lib-control" onchange="updateTeacherClass()">
                                    <option value="">Select Standard</option>
                                    @foreach($standards as $standard)
                                        <option value="{{ $standard->id }}">{{ $standard->standard_name }}</option>
                                    @endforeach
                                </select>
                                <span class="lib-arrow"></span>
                            </div>
                        </div>
                        <div class="lib-group">
                            <label class="lib-label" for="teacher_division_id">Division <span class="req">*</span></label>
                            <div class="lib-select-wrap">
                                <select name="division_id" id="teacher_division_id" class="lib-control" onchange="updateTeacherClass()">
                                    <option value="">Select Division</option>
                                    @foreach($divisions as $division)
                                        <option value="{{ $division->id }}">{{ $division->division_name }}</option>
                                    @endforeach
                                </select>
                                <span class="lib-arrow"></span>
                            </div>
                        </div>
                        <div class="lib-group">
                            <label class="lib-label" for="teacher_class">Class (auto-filled)</label>
                            <input type="text" id="teacher_class" class="lib-control text" readonly>
                        </div>
                    </div>

                    <div class="lib-row cols-2">
                        <div class="lib-group">
                            <label class="lib-label" for="teacher_id">Teacher <span class="req">*</span></label>
                            <div class="lib-select-wrap">
                                <select id="teacher_id" class="lib-control" onchange="onTeacherSelected()">
                                    <option value="">Select Teacher</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                    @endforeach
                                </select>
                                <span class="lib-arrow"></span>
                            </div>
                        </div>
                    </div>
                </div>


                {{-- BOOK DETAILS --}}
                <div class="lib-section-title" style="margin-top:8px;">Book Details</div>

                <div class="lib-row cols-2">
                    <div class="lib-group book-select-container">
                        <label class="lib-label" for="book_id">Book Name <span class="req">*</span></label>
                        <select name="book_id" id="book_id" required>
                            {{-- Blank option so Tom Select starts empty --}}
                            <option value=""></option>
                            @foreach($books as $book)
                                <option value="{{ $book->id }}"
                                        data-code="{{ $book->book_code }}"
                                        data-author="{{ $book->author }}"
                                        data-name="{{ $book->book_name }}"
                                        data-available="{{ $book->available_copies }}"
                                        data-total="{{ $book->total_copies }}"
                                        {{ (string) old('book_id') === (string) $book->id ? 'selected' : '' }}>
                                    {{ $book->book_name }}
                                    @if($book->book_code) ({{ $book->book_code }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="lib-row cols-3">
                    <div class="lib-group">
                        <label class="lib-label" for="book_code">Book Code</label>
                        <input type="text" name="book_code" id="book_code" value="{{ old('book_code') }}" class="lib-control text" readonly>
                    </div>
                    <div class="lib-group">
                        <label class="lib-label" for="author">Author</label>
                        <input type="text" name="author" id="author" value="{{ old('author') }}" class="lib-control text" readonly>
                    </div>
                    <div class="lib-group">
                        <label class="lib-label" for="book_name">Book Name (text)</label>
                        <input type="text" name="book_name" id="book_name" value="{{ old('book_name') }}" class="lib-control text" readonly>
                    </div>
                </div>

                <div class="lib-row cols-3">
                    <div class="lib-group">
                        <label class="lib-label" for="issue_date">Issue Date <span class="req">*</span></label>
                        <input type="date" name="issue_date" id="issue_date" value="{{ old('issue_date', date('Y-m-d')) }}" class="lib-control text" required>
                    </div>
                    <div class="lib-group">
                        <label class="lib-label" for="due_date">Due Date</label>
                        <input type="date" name="due_date" id="due_date" value="{{ old('due_date', date('Y-m-d', strtotime('+14 days'))) }}" class="lib-control text">
                    </div>
                    <div class="lib-group">
                        <label class="lib-label" for="remarks">Remarks</label>
                        <input type="text" name="remarks" id="remarks" value="{{ old('remarks') }}" class="lib-control text" placeholder="Optional notes">
                    </div>
                </div>

            </div>

            <div class="lib-form-footer">
                <a href="{{ route('library.index') }}" class="lib-btn lib-btn-gray">Cancel</a>
                <button type="submit" class="lib-btn lib-btn-primary">Issue Book</button>
            </div>

        </div>
    </form>
</div>


<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
/* ============================================================
   Build book lookup map
============================================================ */
const BOOK_DATA = {};
document.querySelectorAll('#book_id option').forEach(function (opt) {
    if (!opt.value) return;
    BOOK_DATA[opt.value] = {
        code:      opt.getAttribute('data-code')      || '',
        author:    opt.getAttribute('data-author')    || '',
        name:      opt.getAttribute('data-name')      || opt.textContent.trim(),
        available: opt.getAttribute('data-available') || '',
        total:     opt.getAttribute('data-total')     || ''
    };
});

function fillBookFields(value) {
    const d = BOOK_DATA[value] || { code: '', author: '', name: '' };
    document.getElementById('book_code').value = d.code;
    document.getElementById('author').value    = d.author;
    document.getElementById('book_name').value = d.name;
}

/* ============================================================
   TAB SWITCH
============================================================ */
function switchTab(mode) {
    document.getElementById('borrower_type').value = mode;
    document.getElementById('tab-student').classList.toggle('active', mode === 'student');
    document.getElementById('tab-teacher').classList.toggle('active', mode === 'teacher');
    document.getElementById('pane-student').classList.toggle('active', mode === 'student');
    document.getElementById('pane-teacher').classList.toggle('active', mode === 'teacher');
    syncBorrowerClass();
}

function syncBorrowerClass() {
    const mode = document.getElementById('borrower_type').value;
    const hidden = document.getElementById('borrower_class_hidden');
    hidden.value = mode === 'student'
        ? (document.getElementById('student_class').value || '')
        : (document.getElementById('teacher_class').value || '');
}

/* ============================================================
   STUDENT CASCADE
============================================================ */
function resetStudentCascade() {
    document.getElementById('student_standard_id').value = '';
    document.getElementById('student_division_id').value = '';
    resetStudentList('Select Standard + Division first');
}
function resetDivisionAndStudents() {
    document.getElementById('student_division_id').value = '';
    resetStudentList('Select Standard + Division first');
}
function resetStudentList(placeholder) {
    const sel  = document.getElementById('student_id');
    const wrap = document.getElementById('student_select_wrap');
    sel.innerHTML = '<option value="">' + placeholder + '</option>';
    sel.disabled = true;
    wrap.classList.add('disabled');
    document.getElementById('borrower_id').value = '';
    document.getElementById('borrower_name').value = '';
    document.getElementById('student_class').value = '';
    syncBorrowerClass();
}
function loadStudents() {
    const yearId     = document.getElementById('student_academic_year_id').value;
    const standardId = document.getElementById('student_standard_id').value;
    const divisionId = document.getElementById('student_division_id').value;
    const sel        = document.getElementById('student_id');
    const wrap       = document.getElementById('student_select_wrap');

    updateStudentClass();

    if (!yearId || !standardId || !divisionId) {
        resetStudentList('Select Standard + Division first');
        return;
    }

    sel.innerHTML = '<option value="">Loading students…</option>';
    sel.disabled = true;
    wrap.classList.add('disabled');

    const url = '{{ route('library.get-students') }}'
        + '?academic_year_id=' + encodeURIComponent(yearId)
        + '&standard_id='      + encodeURIComponent(standardId)
        + '&division_id='      + encodeURIComponent(divisionId);

    fetch(url)
        .then(r => r.json())
        .then(data => {
            if (data.error) { sel.innerHTML = '<option value="">Error: ' + data.error + '</option>'; return; }
            if (!Array.isArray(data) || data.length === 0) {
                sel.innerHTML = '<option value="">No students found</option>';
                return;
            }
            let html = '<option value="">Select Student</option>';
            data.forEach(s => {
                html += '<option value="' + escapeAttr(s.id) + '"'
                      + ' data-name="' + escapeAttr(s.name) + '">'
                      + (s.rollno ? s.rollno + ' - ' : '')
                      + escapeHtml(s.name)
                      + '</option>';
            });
            sel.innerHTML = html;
            sel.disabled = false;
            wrap.classList.remove('disabled');
        })
        .catch(() => { sel.innerHTML = '<option value="">Search failed</option>'; });
}
function onStudentSelected() {
    const sel = document.getElementById('student_id');
    const opt = sel.options[sel.selectedIndex];
    if (!sel.value) return;
    document.getElementById('borrower_id').value   = sel.value;
    document.getElementById('borrower_name').value = opt.getAttribute('data-name') || opt.text;
}
function updateStudentClass() {
    const stdSel  = document.getElementById('student_standard_id');
    const divSel  = document.getElementById('student_division_id');
    const out     = document.getElementById('student_class');
    const stdText = stdSel.options[stdSel.selectedIndex]?.text || '';
    const divText = divSel.options[divSel.selectedIndex]?.text || '';
    out.value = (stdSel.value && divSel.value) ? (stdText + ' - ' + divText) : '';
    syncBorrowerClass();
}

/* ============================================================
   TEACHER
============================================================ */
function onTeacherSelected() {
    const sel = document.getElementById('teacher_id');
    const opt = sel.options[sel.selectedIndex];
    if (!sel.value) return;
    document.getElementById('borrower_id').value   = sel.value;
    document.getElementById('borrower_name').value = opt.text;
}
function updateTeacherClass() {
    const stdSel  = document.getElementById('teacher_standard_id');
    const divSel  = document.getElementById('teacher_division_id');
    const out     = document.getElementById('teacher_class');
    const stdText = stdSel.options[stdSel.selectedIndex]?.text || '';
    const divText = divSel.options[divSel.selectedIndex]?.text || '';
    out.value = (stdSel.value && divSel.value) ? (stdText + ' - ' + divText) : '';
    syncBorrowerClass();
}

/* ============================================================
   HELPERS
============================================================ */
function escapeHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}
function escapeAttr(s) {
    return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

/* ============================================================
   SUBMIT VALIDATION
============================================================ */
document.getElementById('issueForm').addEventListener('submit', function (e) {
    const type = document.getElementById('borrower_type').value;
    const id   = document.getElementById('borrower_id').value;
    const name = document.getElementById('borrower_name').value;

    if (type === 'student') {
        if (!document.getElementById('student_academic_year_id').value) { e.preventDefault(); alert('Please select Academic Year.'); return; }
        if (!document.getElementById('student_standard_id').value)      { e.preventDefault(); alert('Please select Standard.'); return; }
        if (!document.getElementById('student_division_id').value)      { e.preventDefault(); alert('Please select Division.'); return; }
    } else {
        if (!document.getElementById('teacher_standard_id').value)      { e.preventDefault(); alert('Please select Standard.'); return; }
        if (!document.getElementById('teacher_division_id').value)      { e.preventDefault(); alert('Please select Division.'); return; }
    }

    if (!id || !name) {
        e.preventDefault();
        alert('Please select a ' + (type === 'student' ? 'student' : 'teacher') + ' from the dropdown.');
        return;
    }

    if (!document.getElementById('book_id').value) {
        e.preventDefault();
        alert('Please select a Book.');
        return;
    }

    syncBorrowerClass();

    if (!document.getElementById('borrower_class_hidden').value) {
        e.preventDefault();
        alert('Class is empty. Please re-select Standard and Division.');
    }
});

/* ============================================================
   BOOT — Tom Select
============================================================ */
document.addEventListener('DOMContentLoaded', function () {
    syncBorrowerClass();

    const tom = new TomSelect('#book_id', {
        searchField: ['text'],
        maxOptions: 500,
        placeholder: 'Select Book',
        allowEmptyOption: true,           /* allows empty option to act as placeholder */
        onChange: function (value) {
            fillBookFields(value);
        },
        onFocus: function () {
            this.setTextboxValue('');
            this.refreshOptions(false);
            this.open();
        },
        onBlur: function () {
            if (this.getValue()) {
                this.setTextboxValue(this.getItem(this.getValue()).text);
            }
        },
        render: {
            /* Hides the blank placeholder option from the dropdown list */
            option: function (data, escape) {
                if (!data.value) return '';   /* empty string hides the option row */

                const d = BOOK_DATA[data.value];
                if (!d) return '<div>' + escape(data.text) + '</div>';

                return '<div class="ts-rich-option">' +
                            '<div class="ts-opt-title">' + escape(d.name) + '</div>' +
                            '<div class="ts-opt-sub">' +
                                (d.code ? escape(d.code) + ' · ' : '') +
                                (d.author ? escape(d.author) + ' · ' : '') +
                                'Available: ' + escape(d.available) + '/' + escape(d.total) +
                            '</div>' +
                        '</div>';
            }
        }
    });

    /* Prefill with any previously-selected book (validation-failure case) */
    fillBookFields(document.getElementById('book_id').value);
});
</script>

</x-app-layout>