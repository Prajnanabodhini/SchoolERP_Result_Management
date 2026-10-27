@extends('layouts.app')

@section('content')
<style>
    .skill-page, .skill-page * { font-family: Arial, sans-serif !important; font-size: 13px; box-sizing: border-box; }
    .skill-page h2 { font-size: 21px !important; font-weight: 700 !important; color: #1D4ED8 !important; margin: 0 0 4px 0 !important; }
    .page-header { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 14px; }
    .page-header p { font-size: 13px; color: #6B7280; margin: 0; }
    .skill-card { background: #ffffff; border: 1px solid #d1d5db; border-radius: 8px; padding: 14px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05); margin-bottom: 14px; }
    .filter-section-title { font-size: 12px; font-weight: 700; color: #374151; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.3px; }
    .filter-row { display: flex; align-items: flex-end; gap: 20px; flex-wrap: wrap; margin-bottom: 14px; }
    .filter-group { display: flex; flex-direction: column; gap: 5px; }
    .filter-label { font-size: 12px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.3px; }
    .filter-label .req { color: #DC2626; }
    .filter-select-wrap { position: relative; display: inline-block; }
    .filter-select-wrap select { width: 100%; appearance: none; -webkit-appearance: none; -moz-appearance: none; padding-right: 28px; }
    .filter-select-arrow { position: absolute; top: 50%; right: 10px; transform: translateY(-50%); width: 0; height: 0; border-left: 5px solid transparent; border-right: 5px solid transparent; border-top: 6px solid #6b7280; pointer-events: none; }
    .filter-select-wrap.disabled .filter-select-arrow { border-top-color: #cbd5e1; }
    .filter-select, .filter-input { height: 36px; padding: 4px 10px; font-size: 13px; font-weight: 600; border: 1px solid #9CA3AF; border-radius: 5px; background: #ffffff; color: #111827; min-width: 140px; outline: none; cursor: pointer; }
    .filter-input { padding-right: 10px; cursor: text; }
    .filter-select:focus, .filter-input:focus { border-color: #2563EB; box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15); }
    .filter-select:disabled { background: #F3F4F6; color: #9CA3AF; cursor: not-allowed; }
    .w-ay { width: 150px; } .w-exam { width: 210px; } .w-std { width: 130px; } .w-div { width: 100px; } .w-student { width: 340px; }
    .action-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-top: 4px; }
    .btn { display: inline-flex; align-items: center; justify-content: center; height: 36px; min-width: 140px; padding: 0 18px; border-radius: 5px; font-size: 13px; font-weight: 700; text-decoration: none; cursor: pointer; border: 1px solid transparent; transition: background .15s; box-sizing: border-box; }
    .btn-primary { background: #16A34A; color: #ffffff; border-color: #15803D; } .btn-primary:hover { background: #15803D; }
    .btn-secondary { background: #6B7280; color: #ffffff; border-color: #4B5563; } .btn-secondary:hover { background: #4B5563; }
    .btn-info { background: #2563EB; color: #ffffff; border-color: #1D4ED8; } .btn-info:hover { background: #1D4ED8; }
    .btn-warning { background: #D97706; color: #ffffff; border-color: #B45309; } .btn-warning:hover { background: #B45309; }
    .alert { padding: 10px 14px; border-radius: 6px; margin-bottom: 14px; font-weight: 700; font-size: 13px; }
    .alert-success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
    .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
    .alert-info { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
    .info-banner { background: #eff6ff; border: 1px solid #93c5fd; border-radius: 6px; padding: 10px 14px; margin-bottom: 14px; display: flex; align-items: center; flex-wrap: wrap; gap: 14px; font-size: 13px; }
    .info-banner .info-item { display: inline-flex; align-items: center; gap: 6px; color: #1e40af; font-weight: 700; }
    .info-banner .info-sep { color: #93c5fd; }
    @media (max-width: 900px) { .filter-select { min-width: 100%; } .filter-select-wrap { width: 100%; } .w-ay, .w-exam, .w-std, .w-div, .w-student { width: 100%; } }
    @media (max-width: 768px) { .filter-group { width: 100%; } .btn { width: 100%; } }
</style>

<div class="skill-page p-3">
    <div class="page-header no-print">
        <div>
            <h2>Student Report Card</h2>
            <p>Search and view student progress cards by class and exam</p>
        </div>
    </div>

    @if(!empty($error)) <div class="alert alert-error">{{ $error }}</div> @elseif(session('error')) <div class="alert alert-error">{{ session('error') }}</div> @endif
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

    <div class="skill-card no-print">
        <div class="filter-section-title">Search Filters</div>
        <form method="POST" action="{{ route('report-card.search') }}" id="rcSearchForm">
            @csrf
            <div class="filter-row">
                <div class="filter-group">
                    <label class="filter-label">Academic Year <span class="req">*</span></label>
                    <div class="filter-select-wrap">
                        <select name="academic_year_id" id="academic_year_id" class="filter-select w-ay" required>
                            <option value="">Select</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ ($academic_year_id ?? '') == $year->id ? 'selected' : '' }}>{{ $year->year_name }}</option>
                            @endforeach
                        </select>
                        <span class="filter-select-arrow"></span>
                    </div>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Standard <span class="req">*</span></label>
                    <div class="filter-select-wrap">
                        <select name="standard_id" id="standard_id" class="filter-select w-std" required>
                            <option value="">Select</option>
                            @foreach($standards as $standard)
                                <option value="{{ $standard->id }}" {{ ($standard_id ?? '') == $standard->id ? 'selected' : '' }}>{{ $standard->standard_name }}</option>
                            @endforeach
                        </select>
                        <span class="filter-select-arrow"></span>
                    </div>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Division <span class="req">*</span></label>
                    <div class="filter-select-wrap">
                        <select name="division_id" id="division_id" class="filter-select w-div" required>
                            <option value="">Select</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}" {{ ($division_id ?? '') == $division->id ? 'selected' : '' }}>{{ $division->division_name }}</option>
                            @endforeach
                        </select>
                        <span class="filter-select-arrow"></span>
                    </div>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Exam <span class="req">*</span></label>
                    <div class="filter-select-wrap disabled" id="exam_select_wrap">
                        <select name="exam_master_id" id="exam_master_id" class="filter-select w-exam" required disabled>
                            <option value="">Select Standard first</option>
                        </select>
                        <span class="filter-select-arrow"></span>
                    </div>
                </div>
                <div class="filter-group">
                    <label class="filter-label" style="visibility:hidden;">Action</label>
                    <div class="action-row" style="margin-top:0;"><button type="submit" class="btn btn-primary">Search</button></div>
                </div>
            </div>
        </form>
    </div>

    @if(($academic_year_id ?? '') && ($standard_id ?? '') && ($division_id ?? ''))
        @php
            $dropdownStudents = collect($students ?? [])
                ->filter(fn ($student) => trim((string) ($student->Studentid ?? '')) !== '')
                ->unique(fn ($student) => (string) $student->Studentid)
                ->values();
        @endphp

        <div class="skill-card no-print">
            <div class="filter-section-title">Select Student</div>
            @if($dropdownStudents->isEmpty())
                <div class="alert-info" style="margin:0;"><strong>No students found</strong> for the selected Academic Year + Standard + Division.</div>
            @else
                <div class="filter-row" style="margin-bottom:0;">
                    <div class="filter-group" style="flex:1;max-width:400px;">
                        <label class="filter-label">Student</label>
                        <div class="filter-select-wrap">
                            <select id="student_id" class="filter-select w-student" required>
                                <option value="">Select Student</option>
                                @foreach($dropdownStudents as $student)
                                    @php
                                        $dropdownStudentId = trim((string) ($student->Studentid ?? ''));
                                        $dropdownRollNo = trim((string) ($student->rollno ?? ''));
                                        $dropdownStudentName = trim((string) ($student->full_name ?? ''));
                                        if ($dropdownStudentName === '') {
                                            $dropdownStudentName = trim(trim((string) ($student->studname ?? '')) . ' ' . trim((string) ($student->fathername ?? '')));
                                        }
                                    @endphp
                                    @if($dropdownStudentId !== '')
                                        <option value="{{ $dropdownStudentId }}">
                                            Roll No: {{ $dropdownRollNo !== '' ? $dropdownRollNo : '—' }} | {{ $dropdownStudentName !== '' ? $dropdownStudentName : 'Name unavailable' }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <span class="filter-select-arrow"></span>
                        </div>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label" style="visibility:hidden;">Action</label>
                        <div class="action-row" style="margin-top:0;">
                            <button type="button" class="btn btn-warning" onclick="openProgressCard()">Progress Card</button>
                        </div>
                    </div>
                </div>
                <div class="info-banner" style="margin-top:14px;margin-bottom:0;">
                    <span class="info-item"><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#2563EB;"></span> {{ $dropdownStudents->count() }} Students</span>
                    <span class="info-sep">|</span>
                    <span class="info-item">{{ optional(\App\Models\Standard::find($standard_id))->standard_name ?? '' }} - {{ optional(\App\Models\Division::find($division_id))->division_name ?? '' }}</span>
                </div>
            @endif
        </div>
    @endif
</div>

<script>
(function () {
    const aySel = document.getElementById('academic_year_id'), stdSel = document.getElementById('standard_id'), divSel = document.getElementById('division_id'), examSel = document.getElementById('exam_master_id'), examWrap = document.getElementById('exam_select_wrap'), form = document.getElementById('rcSearchForm');
    if (!aySel || !stdSel || !examSel || !form) return;
    const preSelectedExam = "{{ $exam_master_id ?? '' }}", examsUrl = "{{ route('report-card.exams-by-standard') }}", csrfToken = (document.querySelector('meta[name="csrf-token"]')?.content) || document.querySelector('input[name="_token"]')?.value || '';
    let requestSeq = 0;

    async function loadExams(standardId, academicYearId, selectValue) {
        if (!standardId) {
            examSel.innerHTML = '<option value="">Select Standard first</option>'; examSel.disabled = true; if (examWrap) examWrap.classList.add('disabled'); return;
        }
        const seq = ++requestSeq; examSel.disabled = true; examSel.innerHTML = '<option value="">Loading…</option>'; if (examWrap) examWrap.classList.add('disabled');
        try {
            const res = await fetch(examsUrl, {
                method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ standard_id: standardId, academic_year_id: academicYearId || null })
            });
            if (seq !== requestSeq) return;
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const exams = await res.json();
            if (!exams.length) { examSel.innerHTML = '<option value="">No exams for this Standard</option>'; examSel.disabled = true; if (examWrap) examWrap.classList.add('disabled'); return; }
            let html = '<option value="">Select</option>';
            exams.forEach(e => {
                const isSel = String(selectValue) === String(e.id) ? ' selected' : '', name = String(e.exam_name).replace(/</g, '&lt;').replace(/>/g, '&gt;');
                html += '<option value="' + e.id + '"' + isSel + '>' + name + '</option>';
            });
            examSel.innerHTML = html; examSel.disabled = false; if (examWrap) examWrap.classList.remove('disabled');
        } catch (err) {
            if (seq !== requestSeq) return; console.error('Exam load failed:', err);
            examSel.innerHTML = '<option value="">Error loading exams</option>'; examSel.disabled = true; if (examWrap) examWrap.classList.add('disabled');
        }
    }

    stdSel.addEventListener('change', () => loadExams(stdSel.value, aySel.value, ''));
    aySel.addEventListener('change', () => { if (stdSel.value) loadExams(stdSel.value, aySel.value, ''); });
    form.addEventListener('submit', e => { if (!examSel.value) { e.preventDefault(); alert('Please select an Exam.'); } });
    if (stdSel.value) loadExams(stdSel.value, aySel.value, preSelectedExam);
})();

function openProgressCard() {
    const select = document.getElementById('student_id');
    if (!select || !select.value) { alert('Please select a Student first.'); return; }
    const studentId = encodeURIComponent(select.value), examId = "{{ $exam_master_id ?? '' }}", yearId = "{{ $academic_year_id ?? '' }}";
    if (!examId || !yearId) { alert('Exam or Academic Year is missing.'); return; }
    window.open("{{ url('report-card/progress-card') }}/" + studentId + "/" + examId + "/" + yearId, '_blank');
}
</script>
@endsection