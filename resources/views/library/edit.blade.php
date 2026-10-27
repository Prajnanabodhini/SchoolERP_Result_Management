<x-app-layout>

<style>
.lib-form-page, .lib-form-page * { font-family: Arial, sans-serif !important; box-sizing: border-box; }
.lib-form-page { font-size: 12px; }
.lib-form-page h2 { font-size: 18px !important; font-weight: 700 !important; color: #1D4ED8 !important; margin: 0; text-transform: uppercase; letter-spacing: 0.3px; }
.lib-form-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; }

.lib-form-card {
    width: 100%; background: #fff; border: 1px solid #d1d5db; border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,.05); overflow: visible;
}

/* ---- Small pill tabs at top ---- */
.lib-tabs-bar {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    padding: 8px 14px; border-bottom: 1px solid #e5e7eb; background: #f9fafb; flex-wrap: wrap;
}
.lib-tabs { display: inline-flex; gap: 3px; padding: 3px; background: #e5e7eb; border-radius: 6px; }
.lib-tab {
    display: inline-flex; align-items: center; gap: 6px;
    height: 26px; padding: 0 14px; border: 0; background: transparent; color: #4b5563;
    border-radius: 4px; font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .5px;
    cursor: pointer; user-select: none; transition: all .12s;
}
.lib-tab:hover { color: #1e40af; }
.lib-tab.active { background: #fff; color: #1d4ed8; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
.lib-tab-dot { display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: #9ca3af; }
.lib-tab.active .lib-tab-dot { background: #2563eb; }
.lib-tabs-hint { font-size: 10.5px; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: .3px; }
.lib-tabs-hint b { color: #1e40af; }

/* ---- Body ---- */
.lib-form-body { padding: 14px; }

/* ---- 2 EQUAL COLUMNS ---- */
.lib-2col-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px 22px;
    align-items: start;
}

.lib-col {
    display: flex;
    flex-direction: column;
    gap: 10px;
    min-width: 0;
}

.lib-col-title {
    font-size: 10.5px; font-weight: 800; color: #1e40af;
    text-transform: uppercase; letter-spacing: .6px;
    padding-bottom: 4px; margin-bottom: 2px;
    border-bottom: 1px dashed #bfdbfe;
    display: flex; align-items: center; justify-content: space-between;
}

.lib-pane { display: none; flex-direction: column; gap: 10px; }
.lib-pane.active { display: flex; }

.lib-field {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
    position: relative;
}
.lib-label {
    font-size: 10.5px; font-weight: 700; color: #374151;
    text-transform: uppercase; letter-spacing: .3px;
    height: 14px; line-height: 14px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.lib-label .req { color: #dc2626; }

.lib-select-wrap { position: relative; }

.lib-control {
    display: block;
    height: 32px !important;
    min-height: 32px !important;
    max-height: 32px !important;
    padding: 0 10px;
    border: 1px solid #9ca3af;
    border-radius: 5px;
    background: #fff;
    color: #111827;
    font-size: 12.5px; font-weight: 600;
    line-height: 30px;
    outline: none; width: 100%;
    transition: border-color .12s, box-shadow .12s;
}
select.lib-control {
    padding-right: 32px;
    appearance: none; -webkit-appearance: none; -moz-appearance: none;
    cursor: pointer;
}
.lib-control:focus { border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,.15); }
.lib-control:disabled { background: #f3f4f6; color: #9ca3af; cursor: not-allowed; }
.lib-control.text { padding-right: 10px; cursor: text; }
.lib-control.text[readonly] { background: #f9fafb; cursor: default; }

.lib-control::placeholder { color: #9ca3af; font-weight: 500; font-style: italic; }

.lib-arrow {
    position: absolute; top: 50%; right: 10px; transform: translateY(-50%);
    width: 0; height: 0;
    border-left: 4px solid transparent; border-right: 4px solid transparent;
    border-top: 5px solid #6b7280;
    pointer-events: none;
}
.lib-select-wrap.disabled .lib-arrow { border-top-color: #cbd5e1; }

.lib-alert { padding: 8px 12px; border-radius: 6px; margin-bottom: 10px; font-weight: 700; font-size: 12px; }
.lib-alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

.lib-remarks-wrap {
    margin-top: 14px;
    padding-top: 14px;
    border-top: 1px dashed #bfdbfe;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0 22px;
}
.lib-remarks-wrap .lib-field { grid-column: 1 / -1; }

.lib-form-footer {
    display: flex; justify-content: flex-end; align-items: center; gap: 8px;
    padding: 10px 14px; border-top: 1px solid #e5e7eb; background: #f9fafb;
}
.lib-btn {
    display: inline-flex; align-items: center; justify-content: center;
    height: 34px; min-width: 120px; padding: 0 18px;
    border-radius: 5px; font-size: 12.5px; font-weight: 700;
    text-decoration: none; cursor: pointer;
    border: 1px solid transparent; white-space: nowrap;
    box-sizing: border-box; transition: background .15s;
}
.lib-btn-primary { background: #16a34a; color: #fff; border-color: #15803d; }
.lib-btn-primary:hover { background: #15803d; }
.lib-btn-gray { background: #6b7280; color: #fff; border-color: #4b5563; }
.lib-btn-gray:hover { background: #4b5563; }
.lib-back-link {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 0 12px; height: 32px;
    border: 1px solid #d1d5db; border-radius: 5px;
    background: #f9fafb; color: #374151;
    font-size: 12px; font-weight: 700; text-decoration: none;
    transition: background .12s;
}
.lib-back-link:hover { background: #f3f4f6; color: #111827; }

/* ==========================================================
   ⭐ BOOK SEARCH — plain textbox + result list
   ========================================================== */
.book-search-wrap { position: relative; width: 100%; }

.book-search-results {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 9999;
    margin-top: 2px;
    background: #fff;
    border: 1px solid #9ca3af;
    border-radius: 5px;
    box-shadow: 0 6px 16px rgba(0,0,0,.12);
    max-height: 260px;
    overflow-y: auto;
    padding: 3px 0;
}
.book-search-results.show { display: block; }

.book-search-item {
    padding: 6px 10px;
    border-bottom: 1px solid #f3f4f6;
    cursor: pointer;
    transition: background .1s;
}
.book-search-item:last-child { border-bottom: 0; }
.book-search-item:hover { background: #eff6ff; }

.book-search-item .bsi-title {
    font-weight: 700;
    color: #111827;
    font-size: 12.5px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.book-search-item .bsi-sub {
    font-size: 10.5px;
    color: #6b7280;
    margin-top: 1px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.book-search-item:hover .bsi-title,
.book-search-item:hover .bsi-sub { color: #1e40af; }

.book-search-empty {
    padding: 8px 10px;
    font-size: 11.5px;
    color: #6b7280;
    font-style: italic;
    text-align: center;
}

@media (max-width: 780px) {
    .lib-2col-grid { grid-template-columns: 1fr; gap: 14px; }
    .lib-remarks-wrap { grid-template-columns: 1fr; }
}
</style>

@php
    /* -----------------------------------------------------------
     | Resolve the currently-selected book id.
     | Priority:
     |   1. old('book_id')      — after validation failure
     |   2. $issue->book_id     — if column exists in future
     |   3. match by book_code  — unique key on library_books
     |   4. match by book_name  — last resort for legacy rows
     | ----------------------------------------------------------- */
    $selectedBookId = old('book_id', $issue->book_id ?? null);

    if (!$selectedBookId && !empty($issue->book_code)) {
        $matched = $books->firstWhere('book_code', $issue->book_code);
        if ($matched) $selectedBookId = $matched->id;
    }

    if (!$selectedBookId && !empty($issue->book_name)) {
        $matched = $books->firstWhere('book_name', $issue->book_name);
        if ($matched) $selectedBookId = $matched->id;
    }

    $mode = old('borrower_type', $issue->borrower_type);
@endphp


<div class="lib-form-page p-2">

    <div class="lib-form-header">
        <h2>Edit Issue Record</h2>
        <a href="{{ route('library.index') }}" class="lib-back-link">
            <span style="font-size:14px;line-height:1;">←</span> Back to List
        </a>
    </div>

    @if(session('error'))
        <div class="lib-alert lib-alert-error">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="lib-alert lib-alert-error">
            <ul style="margin:0;padding-left:16px;">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('library.update', $issue->id) }}" id="issueForm">
        @csrf
        @method('PUT')

        <input type="hidden" name="borrower_type"  id="borrower_type"       value="{{ $mode }}">
        <input type="hidden" name="borrower_id"    id="borrower_id"         value="{{ old('borrower_id',   $issue->borrower_id) }}">
        <input type="hidden" name="borrower_name"  id="borrower_name"       value="{{ old('borrower_name', $issue->borrower_name) }}">
        <input type="hidden" name="borrower_class" id="borrower_class_hidden" value="{{ old('borrower_class', $issue->borrower_class) }}">

        <input type="hidden" name="book_id"        id="book_id_hidden"      value="{{ $selectedBookId }}">
        <input type="hidden" name="book_name"      id="book_name_hidden"    value="{{ old('book_name', $issue->book_name) }}">

        <div class="lib-form-card">

            <div class="lib-tabs-bar">
                <div class="lib-tabs">
                    <button type="button" class="lib-tab {{ $mode === 'student' ? 'active' : '' }}" id="tab-student" onclick="switchTab('student')">
                        <span class="lib-tab-dot"></span> Student
                    </button>
                    <button type="button" class="lib-tab {{ $mode === 'teacher' ? 'active' : '' }}" id="tab-teacher" onclick="switchTab('teacher')">
                        <span class="lib-tab-dot"></span> Teacher
                    </button>
                </div>
                <span class="lib-tabs-hint">
                    Current borrower: <b>{{ $issue->borrower_name }}</b> (ID: {{ $issue->borrower_id }})
                </span>
            </div>

            <div class="lib-form-body">

                <div class="lib-2col-grid">

                    {{-- ================= LEFT: BORROWER ================= --}}
                    <div class="lib-col">
                        <div class="lib-col-title">
                            <span>Borrower Details</span>
                        </div>

                        {{-- STUDENT PANE --}}
                        <div class="lib-pane {{ $mode === 'student' ? 'active' : '' }}" id="pane-student">
                            <div class="lib-field">
                                <label class="lib-label" for="student_academic_year_id">Academic Year <span class="req">*</span></label>
                                <div class="lib-select-wrap">
                                    <select name="academic_year_id" id="student_academic_year_id" class="lib-control" onchange="resetStudentCascade()">
                                        <option value="">Select Year</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}" {{ session('yearid') == $year->id ? 'selected' : '' }}>
                                                {{ $year->year_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="lib-arrow"></span>
                                </div>
                            </div>

                            <div class="lib-field">
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

                            <div class="lib-field">
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

                            <div class="lib-field">
                                <label class="lib-label" for="student_id">Student <span class="req">*</span></label>
                                <div class="lib-select-wrap disabled" id="student_select_wrap">
                                    <select id="student_id" class="lib-control" disabled onchange="onStudentSelected()">
                                        <option value="">Select Standard + Division first</option>
                                    </select>
                                    <span class="lib-arrow"></span>
                                </div>
                            </div>

                            <div class="lib-field">
                                <label class="lib-label" for="student_class">Class (auto)</label>
                                <input type="text" id="student_class" class="lib-control text" readonly
                                       value="{{ old('borrower_class', $issue->borrower_class) }}">
                            </div>
                        </div>

                        {{-- TEACHER PANE --}}
                        <div class="lib-pane {{ $mode === 'teacher' ? 'active' : '' }}" id="pane-teacher">

                            <div class="lib-field">
                                <label class="lib-label" for="teacher_academic_year_id">Academic Year <span class="req">*</span></label>
                                <div class="lib-select-wrap">
                                    <select name="academic_year_id" id="teacher_academic_year_id" class="lib-control">
                                        <option value="">Select Year</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}" {{ session('yearid') == $year->id ? 'selected' : '' }}>
                                                {{ $year->year_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="lib-arrow"></span>
                                </div>
                            </div>

                            <div class="lib-field">
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

                            <div class="lib-field">
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

                            <div class="lib-field">
                                <label class="lib-label" for="teacher_id">Teacher <span class="req">*</span></label>
                                <div class="lib-select-wrap">
                                    <select id="teacher_id" class="lib-control" onchange="onTeacherSelected()">
                                        <option value="">Select Teacher</option>
                                        @foreach($teachers as $teacher)
                                            <option value="{{ $teacher->id }}"
                                                {{ (string) $issue->borrower_id === (string) $teacher->id && $mode === 'teacher' ? 'selected' : '' }}>
                                                {{ $teacher->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="lib-arrow"></span>
                                </div>
                            </div>

                            <div class="lib-field">
                                <label class="lib-label" for="teacher_class">Class (auto)</label>
                                <input type="text" id="teacher_class" class="lib-control text" readonly
                                       value="{{ old('borrower_class', $issue->borrower_class) }}">
                            </div>
                        </div>
                    </div>

                    {{-- ================= RIGHT: BOOK DETAILS ================= --}}
                    <div class="lib-col">
                        <div class="lib-col-title">
                            <span>Book Details</span>
                        </div>

                        <div class="lib-field book-search-wrap">
                            <label class="lib-label" for="book_search_input">Book Name <span class="req">*</span></label>
                            <input type="text"
                                   id="book_search_input"
                                   class="lib-control"
                                   placeholder="Type here to search book…"
                                   value="{{ old('book_name', $issue->book_name) }}"
                                   autocomplete="off"
                                   required>
                            <div class="book-search-results" id="book_search_results"></div>
                        </div>

                        <div class="lib-field">
                            <label class="lib-label" for="book_code">Book Code</label>
                            <input type="text" name="book_code" id="book_code" value="{{ old('book_code', $issue->book_code) }}" class="lib-control text" readonly>
                        </div>

                        <div class="lib-field">
                            <label class="lib-label" for="author">Author</label>
                            <input type="text" name="author" id="author" value="{{ old('author', $issue->author) }}" class="lib-control text" readonly>
                        </div>

                        <div class="lib-field">
                            <label class="lib-label" for="issue_date">Issue Date <span class="req">*</span></label>
                            <input type="date" name="issue_date" id="issue_date" value="{{ old('issue_date', $issue->issue_date ? $issue->issue_date->format('Y-m-d') : '') }}" class="lib-control text" required>
                        </div>

                        <div class="lib-field">
                            <label class="lib-label" for="due_date">Due Date</label>
                            <input type="date" name="due_date" id="due_date" value="{{ old('due_date', $issue->due_date ? $issue->due_date->format('Y-m-d') : '') }}" class="lib-control text">
                        </div>
                    </div>

                </div>

                <div class="lib-remarks-wrap">
                    <div class="lib-field">
                        <label class="lib-label" for="remarks">Remarks</label>
                        <input type="text" name="remarks" id="remarks" value="{{ old('remarks', $issue->remarks) }}" class="lib-control text" placeholder="Optional notes">
                    </div>
                </div>

            </div>

            <div class="lib-form-footer">
                <a href="{{ route('library.index') }}" class="lib-btn lib-btn-gray">Cancel</a>
                <button type="submit" class="lib-btn lib-btn-primary">Update Record</button>
            </div>

        </div>
    </form>
</div>


<script>
const CURRENT_BORROWER_ID = @json((string) $issue->borrower_id);
const CURRENT_CLASS_LABEL = @json((string) $issue->borrower_class);

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
    document.getElementById('student_class').value = '';
    syncBorrowerClass();
}
function loadStudents(selectId = null) {
    const yearId     = document.getElementById('student_academic_year_id').value;
    const standardId = document.getElementById('student_standard_id').value;
    const divisionId = document.getElementById('student_division_id').value;
    const sel        = document.getElementById('student_id');
    const wrap       = document.getElementById('student_select_wrap');

    updateStudentClass();

    if (!yearId || !standardId || !divisionId) {
        resetStudentList('Select Standard + Division first');
        return Promise.resolve();
    }

    sel.innerHTML = '<option value="">Loading students…</option>';
    sel.disabled = true;
    wrap.classList.add('disabled');

    const url = '{{ route('library.get-students') }}'
        + '?academic_year_id=' + encodeURIComponent(yearId)
        + '&standard_id='      + encodeURIComponent(standardId)
        + '&division_id='      + encodeURIComponent(divisionId);

    return fetch(url)
        .then(r => r.json())
        .then(data => {
            if (data.error) { sel.innerHTML = '<option value="">Error: ' + data.error + '</option>'; return; }
            if (!Array.isArray(data) || data.length === 0) {
                sel.innerHTML = '<option value="">No students found</option>';
                return;
            }
            let html = '<option value="">Select Student</option>';
            data.forEach(s => {
                const isSelected = selectId && String(s.id) === String(selectId);
                html += '<option value="' + escapeAttr(s.id) + '"'
                      + ' data-name="' + escapeAttr(s.name) + '"'
                      + (isSelected ? ' selected' : '') + '>'
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
   AUTO-PRESELECT STANDARD + DIVISION FROM CLASS LABEL
   ("SEVENTH - B" → matches option text)
============================================================ */
function preselectFromClassLabel(prefix) {
    if (!CURRENT_CLASS_LABEL) return false;

    const parts = String(CURRENT_CLASS_LABEL).split(' - ');
    if (parts.length < 2) return false;

    const targetStd = parts[0].trim().toUpperCase();
    const targetDiv = parts[1].trim().toUpperCase();

    const stdSel = document.getElementById(prefix + '_standard_id');
    const divSel = document.getElementById(prefix + '_division_id');

    for (let i = 0; i < stdSel.options.length; i++) {
        if (stdSel.options[i].text.trim().toUpperCase() === targetStd) {
            stdSel.selectedIndex = i; break;
        }
    }
    for (let i = 0; i < divSel.options.length; i++) {
        if (divSel.options[i].text.trim().toUpperCase() === targetDiv) {
            divSel.selectedIndex = i; break;
        }
    }

    return stdSel.value && divSel.value;
}

/* ============================================================
   ⭐ BOOK SEARCH — plain textbox + result dropdown
============================================================ */
(function () {
    const input       = document.getElementById('book_search_input');
    const resultsBox  = document.getElementById('book_search_results');
    const hiddenId    = document.getElementById('book_id_hidden');
    const hiddenName  = document.getElementById('book_name_hidden');
    const codeField   = document.getElementById('book_code');
    const authorField = document.getElementById('author');

    const SEARCH_URL  = '{{ route('library.books.search') }}';
    const MIN_CHARS   = 1;
    const DEBOUNCE_MS = 220;

    let   timer       = null;
    let   lastQuery   = '';
    let   requestSeq  = 0;

    function clearSelection() {
        hiddenId.value    = '';
        hiddenName.value  = '';
        codeField.value   = '';
        authorField.value = '';
    }

    function closeResults() {
        resultsBox.classList.remove('show');
        resultsBox.innerHTML = '';
    }

    function showMessage(text) {
        resultsBox.innerHTML = '<div class="book-search-empty">' + escapeHtml(text) + '</div>';
        resultsBox.classList.add('show');
    }

    function renderResults(books) {
        if (!books.length) {
            showMessage('No books found');
            return;
        }

        resultsBox.innerHTML = books.map(b => {
            const code  = b.book_code ? escapeHtml(b.book_code) : '';
            const auth  = b.author    ? escapeHtml(b.author)    : '';
            const avail = (b.available_copies ?? '-') + '/' + (b.total_copies ?? '-');
            return `
                <div class="book-search-item"
                     data-id="${escapeAttr(b.id)}"
                     data-name="${escapeAttr(b.book_name)}"
                     data-code="${escapeAttr(b.book_code || '')}"
                     data-author="${escapeAttr(b.author || '')}">
                    <div class="bsi-title">${escapeHtml(b.book_name)}</div>
                    <div class="bsi-sub">
                        ${code ? code + ' · ' : ''}${auth ? auth + ' · ' : ''}Available: ${avail}
                    </div>
                </div>`;
        }).join('');

        resultsBox.classList.add('show');

        resultsBox.querySelectorAll('.book-search-item').forEach(el => {
            el.addEventListener('click', function () {
                input.value       = el.dataset.name;
                hiddenId.value    = el.dataset.id;
                hiddenName.value  = el.dataset.name;
                codeField.value   = el.dataset.code;
                authorField.value = el.dataset.author;
                closeResults();
            });
        });
    }

    function search(q) {
        if (q === lastQuery) return;
        lastQuery = q;

        const seq = ++requestSeq;
        showMessage('Searching…');

        fetch(SEARCH_URL + '?q=' + encodeURIComponent(q), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (seq !== requestSeq) return;
            renderResults(Array.isArray(data) ? data : []);
        })
        .catch(() => {
            if (seq !== requestSeq) return;
            showMessage('Search failed. Try again.');
        });
    }

    input.addEventListener('input', function () {
        const q = input.value.trim();

        if (hiddenId.value && input.value !== hiddenName.value) {
            clearSelection();
        }

        clearTimeout(timer);

        if (q.length < MIN_CHARS) {
            lastQuery = '';
            closeResults();
            return;
        }

        timer = setTimeout(() => search(q), DEBOUNCE_MS);
    });

    document.addEventListener('click', function (e) {
        if (!input.contains(e.target) && !resultsBox.contains(e.target)) {
            closeResults();
        }
    });

    input.addEventListener('focus', function () {
        const q = input.value.trim();
        if (q.length >= MIN_CHARS && resultsBox.innerHTML.trim() === '') {
            search(q);
        }
    });
})();

/* ============================================================
   SUBMIT VALIDATION
============================================================ */
document.getElementById('issueForm').addEventListener('submit', function (e) {
    const type = document.getElementById('borrower_type').value;
    const id   = document.getElementById('borrower_id').value;
    const name = document.getElementById('borrower_name').value;

    if (!id || !name) {
        e.preventDefault();
        alert('Borrower is missing. Please select a ' + (type === 'student' ? 'student' : 'teacher') + ' from the dropdown.');
        return;
    }

    if (!document.getElementById('book_id_hidden').value) {
        e.preventDefault();
        alert('Please search and select a Book from the results.');
        return;
    }

    syncBorrowerClass();

    if (type === 'student') {
        document.querySelectorAll('#pane-teacher [name]').forEach(el => el.disabled = true);
    } else {
        document.querySelectorAll('#pane-student [name]').forEach(el => el.disabled = true);
    }
});

/* ============================================================
   INIT
============================================================ */
document.addEventListener('DOMContentLoaded', function () {
    switchTab(document.getElementById('borrower_type').value);
    syncBorrowerClass();

    if (document.getElementById('borrower_type').value === 'student') {
        if (preselectFromClassLabel('student')) {
            loadStudents(CURRENT_BORROWER_ID);
        }
    } else {
        if (preselectFromClassLabel('teacher')) {
            updateTeacherClass();
        }
    }
});
</script>

</x-app-layout>