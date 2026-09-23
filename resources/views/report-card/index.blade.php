@extends('layouts.app')

@section('content')

<style>
    .report-card-page, .report-card-page * { box-sizing: border-box; font-family: Arial, sans-serif; }
    .report-card-page { max-width: 1300px; margin: 12px auto; padding: 0 12px; }
    .rc-card { background: #fff; border: 1px solid #d1d5db; border-radius: 8px; padding: 16px; box-shadow: 0 2px 8px rgba(0,0,0,.05); margin-bottom: 12px; }
    .rc-title { font-size: 18px; font-weight: 700; color: #2563eb; margin: 0 0 12px; }
    .rc-filter-bar { display: flex; align-items: flex-end; flex-wrap: nowrap; gap: 8px; padding: 10px 0; border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb; }
    .rc-filter-bar.secondary { border-top: 0; border-bottom: 0; padding-top: 0; padding-bottom: 0; }
    .rc-filter-group { display: flex; flex-direction: column; gap: 3px; }
    .rc-filter-group label { font-size: 11px; font-weight: 700; color: #374151; white-space: nowrap; }
    .rc-filter-group select, .rc-filter-group input { height: 32px; padding: 4px 8px; font-size: 12px; border: 1px solid #9ca3af; border-radius: 4px; background: #fff; }
    .rc-filter-group select:focus, .rc-filter-group input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,.15); }
    .rc-filter-group select:disabled { background: #f3f4f6; color: #9ca3af; cursor: not-allowed; }
    .w-ay { width: 150px; } .w-exam { width: 210px; } .w-std { width: 120px; } .w-div { width: 90px; } .w-student { width: 340px; }
    .rc-btn { height: 32px; padding: 4px 14px; font-size: 12px; font-weight: 600; border: 0; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; white-space: nowrap; }
    .rc-btn-green { background: #16a34a; color: #fff; } .rc-btn-green:hover { background: #15803d; }
    .rc-btn-orange { background: #d97706; color: #fff; } .rc-btn-orange:hover { background: #b45309; }
    .alert-error { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 8px 10px; border-radius: 5px; margin-bottom: 10px; font-size: 12px; }
    .alert-info { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 8px 10px; border-radius: 5px; margin-bottom: 10px; font-size: 12px; }
    .alert-success { background: #dcfce7; border: 1px solid #86efac; color: #166534; padding: 8px 10px; border-radius: 5px; margin-bottom: 10px; font-size: 12px; }
    @media print { .no-print { display: none !important; } }
    @media (max-width: 900px) { .rc-filter-bar { flex-wrap: wrap; } .w-ay, .w-exam, .w-std, .w-div, .w-student { width: 100%; } }
</style>

<div class="report-card-page">

    <div class="rc-card no-print">

        <h2 class="rc-title">Student Report Card</h2>

        @if(!empty($error))
            <div class="alert-error">{{ $error }}</div>
        @elseif(session('error'))
            <div class="alert-error">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        {{-- SEARCH FORM: Academic Year → Standard → Division → Exam --}}
        <form method="POST" action="{{ route('report-card.search') }}" class="rc-filter-bar" id="rcSearchForm">
            @csrf

            {{-- 1. Academic Year --}}
            <div class="rc-filter-group">
                <label>Academic Year</label>
                <select name="academic_year_id" id="academic_year_id" class="w-ay" required>
                    <option value="">Select</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ ($academic_year_id ?? '') == $year->id ? 'selected' : '' }}>
                            {{ $year->year_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- 2. Standard --}}
            <div class="rc-filter-group">
                <label>Standard</label>
                <select name="standard_id" id="standard_id" class="w-std" required>
                    <option value="">Select</option>
                    @foreach($standards as $standard)
                        <option value="{{ $standard->id }}" {{ ($standard_id ?? '') == $standard->id ? 'selected' : '' }}>
                            {{ $standard->standard_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- 3. Division --}}
            <div class="rc-filter-group">
                <label>Division</label>
                <select name="division_id" id="division_id" class="w-div" required>
                    <option value="">Select</option>
                    @foreach($divisions as $division)
                        <option value="{{ $division->id }}" {{ ($division_id ?? '') == $division->id ? 'selected' : '' }}>
                            {{ $division->division_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- 4. Exam (loaded by AJAX from Standard) --}}
            <div class="rc-filter-group">
                <label>Exam</label>
                <select name="exam_master_id" id="exam_master_id" class="w-exam" required disabled>
                    <option value="">Select Standard first</option>
                </select>
            </div>

            <button type="submit" class="rc-btn rc-btn-green">Search</button>
        </form>

        {{-- STUDENT PICKER --}}
        @if(($academic_year_id ?? '') && ($standard_id ?? '') && ($division_id ?? ''))

            @if(count($students) === 0)
                <div class="alert-info" style="margin-top:12px;">
                    <strong>No students found</strong> for the selected Academic Year + Standard + Division.
                </div>
            @else
                <div class="rc-filter-bar secondary" style="margin-top:12px;">

                    <div class="rc-filter-group">
                        <label>Student</label>
                        <select id="student_id" class="w-student" required>
                            <option value="">Select Student</option>
                            @foreach($students as $student)
                                <option value="{{ $student->Studentid }}">
                                    {{ $student->Studentid }} - {{ $student->studname }}{{ !empty($student->fathername) ? ' ' . $student->fathername : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="button" class="rc-btn rc-btn-orange" onclick="openProgressCard()">
                        Progress Card
                    </button>

                </div>
            @endif

        @endif

    </div>

</div>

<script>
(function () {
    const aySel   = document.getElementById('academic_year_id');
    const stdSel  = document.getElementById('standard_id');
    const divSel  = document.getElementById('division_id');
    const examSel = document.getElementById('exam_master_id');
    const form    = document.getElementById('rcSearchForm');

    if (!aySel || !stdSel || !examSel || !form) return;

    // Server-side values (used to restore state after a Search submit)
    const preSelectedExam = "{{ $exam_master_id ?? '' }}";
    const examsUrl        = "{{ route('report-card.exams-by-standard') }}";
    const csrfToken       = (document.querySelector('meta[name="csrf-token"]')?.content)
                          || document.querySelector('input[name="_token"]')?.value
                          || '';

    let requestSeq = 0; // guard against out-of-order AJAX responses

    async function loadExams(standardId, academicYearId, selectValue) {
        if (!standardId) {
            examSel.innerHTML = '<option value="">Select Standard first</option>';
            examSel.disabled  = true;
            return;
        }

        const seq = ++requestSeq;

        examSel.disabled  = true;
        examSel.innerHTML = '<option value="">Loading…</option>';

        try {
            const res = await fetch(examsUrl, {
                method: 'POST',
                headers: {
                    'Content-Type':    'application/json',
                    'Accept':          'application/json',
                    'X-CSRF-TOKEN':    csrfToken,
                    'X-Requested-With':'XMLHttpRequest',
                },
                body: JSON.stringify({
                    standard_id:      standardId,
                    academic_year_id: academicYearId || null,
                }),
            });

            if (seq !== requestSeq) return; // a newer request superseded this one

            if (!res.ok) throw new Error('HTTP ' + res.status);
            const exams = await res.json();

            if (!exams.length) {
                examSel.innerHTML = '<option value="">No exams for this Standard</option>';
                examSel.disabled  = true;
                return;
            }

            let html = '<option value="">Select</option>';
            exams.forEach(function (e) {
                const isSel = String(selectValue) === String(e.id) ? ' selected' : '';
                const name  = String(e.exam_name).replace(/</g, '&lt;').replace(/>/g, '&gt;');
                html += '<option value="' + e.id + '"' + isSel + '>' + name + '</option>';
            });
            examSel.innerHTML = html;
            examSel.disabled  = false;

        } catch (err) {
            if (seq !== requestSeq) return;
            console.error('Exam load failed:', err);
            examSel.innerHTML = '<option value="">Error loading exams</option>';
            examSel.disabled  = true;
        }
    }

    // User changes Standard → reload exams, clear previous selection
    stdSel.addEventListener('change', function () {
        loadExams(stdSel.value, aySel.value, '');
    });

    // User changes Academic Year → reload exams (exams may be year-scoped)
    aySel.addEventListener('change', function () {
        if (stdSel.value) loadExams(stdSel.value, aySel.value, '');
    });

    // Block submit if no exam selected (disabled required attribute is ignored)
    form.addEventListener('submit', function (e) {
        if (!examSel.value) {
            e.preventDefault();
            alert('Please select an Exam.');
            return;
        }
    });

    // On page load after a Search submit → repopulate exams and reselect old value
    if (stdSel.value) {
        loadExams(stdSel.value, aySel.value, preSelectedExam);
    }
})();

function openProgressCard()
{
    const select = document.getElementById('student_id');
    if (!select || !select.value) {
        alert('Please select a Student first.');
        return;
    }
    const studentId = encodeURIComponent(select.value);
    const examId    = "{{ $exam_master_id ?? '' }}";
    const yearId    = "{{ $academic_year_id ?? '' }}";
    if (!examId || !yearId) { alert('Exam or Academic Year is missing.'); return; }
    const url = "{{ url('report-card/progress-card') }}/" + studentId + "/" + examId + "/" + yearId;
    window.open(url, '_blank');
}
</script>

@endsection