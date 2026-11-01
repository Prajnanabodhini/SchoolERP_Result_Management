<x-app-layout>
<style>
.skill-page,.skill-page *{font-family:Arial,sans-serif!important;font-size:12px;box-sizing:border-box}
.skill-page h2{font-size:20px!important;font-weight:700!important;color:#1D4ED8!important;margin:0 0 14px}
.skill-card{background:#fff;border:1px solid #d1d5db;border-radius:8px;padding:12px;box-shadow:0 1px 3px rgba(0,0,0,.05);margin-bottom:14px}
.filter-section-title{font-size:12px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:.3px}
.filter-row{display:flex;align-items:flex-end;gap:8px;flex-wrap:nowrap;width:100%}
.filter-group{display:flex;flex-direction:column;gap:4px;flex:1 1 0;min-width:0}
.filter-group.filter-group-btn{flex:0 0 auto}
.filter-label{font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.filter-label .req{color:#DC2626}
.filter-select{height:34px;padding:4px 24px 4px 8px;font-size:12px;font-weight:400;border:1px solid #9CA3AF;border-radius:5px;background:#fff;color:#111827;width:100%;min-width:0;outline:none;appearance:none;-webkit-appearance:none;-moz-appearance:none;background-image:url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3e%3cpath fill='%236b7280' d='M0 0l5 6 5-6z'/%3e%3c/svg%3e");background-repeat:no-repeat;background-position:right 8px center;background-size:8px 5px}
.filter-select:focus{border-color:#2563EB;box-shadow:0 0 0 2px rgba(37,99,235,.15)}
.action-row{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:4px}
.btn{display:inline-flex;align-items:center;justify-content:center;height:34px;min-width:100px;padding:0 14px;border-radius:5px;font-size:12px;font-weight:700;text-decoration:none;cursor:pointer;border:1px solid transparent;transition:background .15s;white-space:nowrap}
.btn-primary{background:#16A34A;color:#fff;border-color:#15803D}
.btn-primary:hover{background:#15803D}
.btn-secondary{background:#6B7280;color:#fff;border-color:#4B5563}
.btn-secondary:hover{background:#4B5563}
.btn-print{background:#2563EB;color:#fff;border-color:#1D4ED8}
.btn-print:hover{background:#1D4ED8}
.page-header{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;margin-bottom:14px}
.page-header h2{margin:0 0 4px!important}
.page-header p{font-size:13px;color:#6B7280;margin:0}
.alert{padding:10px 14px;border-radius:6px;margin-bottom:14px;font-size:13px;font-weight:400}
.alert-success{background:#dcfce7;color:#166534;border:1px solid #86efac}
.info-banner{background:#eff6ff;border:1px solid #93c5fd;border-radius:6px;padding:10px 14px;margin-bottom:14px;display:flex;align-items:center;flex-wrap:wrap;gap:14px;font-size:12px}
.info-banner .info-item{display:inline-flex;align-items:center;gap:6px;color:#1e40af;font-weight:700}
.info-banner .info-key{font-size:10px;text-transform:uppercase;letter-spacing:.3px;color:#6B7280;font-weight:700}
.info-banner .info-sep{color:#93c5fd}
.info-banner .grade-legend{margin-left:auto;display:flex;align-items:center;gap:8px;flex-wrap:wrap;font-size:11px;color:#1e40af}
.info-banner .grade-pill{display:inline-flex;align-items:center;gap:4px;background:#fff;border:1px solid #93c5fd;border-radius:4px;padding:2px 6px;font-weight:400;color:#1e40af}

/* Table Wrapper - No horizontal scroll */
.table-wrapper{background:#fff;border:1px solid #d1d5db;border-radius:8px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.05);margin-bottom:14px;width:100%}

/* Fixed table layout to enforce 100% width */
.entry-table{width:100%;border-collapse:collapse;margin:0;table-layout:fixed;font-size:12px;}

.entry-table thead tr.hdr-top{background:#1e3a8a;color:#fff}
.entry-table thead tr.hdr-sub{background:#3b82f6;color:#fff}

.entry-table th{
    padding:6px 4px;
    font-size:12px;
    font-weight:400;
    text-align:center;
    border:1px solid #1e40af;
    color:#fff;
    text-transform:uppercase;
    letter-spacing:0;
    line-height:1.2;
    vertical-align:middle;
    word-wrap:break-word;
}
.entry-table th.left{text-align:left}
.entry-table th.th-total{background:#7c2d12 !important; border-color:#7c2d12 !important;}

.entry-table td{
    padding:4px;
    border:1px solid #d1d5db;
    vertical-align:middle;
    color:#111827;
    font-size:12px;
    font-weight:400;
    text-align:center;
    word-wrap:break-word;
}
.entry-table td.left{text-align:left}
.entry-table td.student-name{
    white-space:nowrap;
    text-align:left;
    overflow:hidden;
    text-overflow:ellipsis;
}
.entry-table tbody tr:nth-child(even){background:#f9fafb}
.entry-table tbody tr:hover{background:#fef9c3}
.subject-sep{border-left:2px solid #cbd5e1!important}

.mark-input{
    width:100%;
    max-width:50px;
    height:26px;
    padding:2px;
    font-size:13px;
    font-weight:400;
    text-align:center;
    border:1px solid #d1d5db;
    border-radius:4px;
    background:#fff;
    color:#111827;
    outline:none;
    -moz-appearance:textfield;
    appearance:textfield;
}
.mark-input:focus{border-color:#2563EB;box-shadow:0 0 0 2px rgba(37,99,235,.15)}
.mark-input::-webkit-outer-spin-button,.mark-input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}

td.grand-total-cell{background:#FFF7ED;color:#C2410C;font-size:13px;}
td.grade-cell{background:#FEFCE8;color:#CA8A04;font-size:13px;}

.status-cell{font-size:11px;text-transform:uppercase;white-space:nowrap}
.status-cell.status-present{color:#16a34a}

.empty-state{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:60px 20px;text-align:center;background:#fff;border:1px solid #d1d5db;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,.05)}
.empty-state-title{font-size:15px;font-weight:700;color:#374151;margin-bottom:4px}
.empty-state-subtitle{font-size:13px;color:#6B7280}
@media(max-width:1200px){.filter-row{flex-wrap:wrap}.filter-group{flex:1 1 160px}}
@media(max-width:900px){.filter-group{flex:1 1 100%}.btn{width:100%}}
</style>

@php
    $hasData = count($students) && !empty($skillStructure);
    $skillTeacher = auth()->user()->name ?? '-';

    // The controller provides the exact structure. We just use it.
    $columnGroups = $skillStructure;

    $grandMax = 0;
    foreach ($columnGroups as $criteria) {
        foreach ($criteria as $mx) {
            $grandMax += (int)$mx;
        }
    }

    $marksLookup = [];
    foreach ($existingMarks as $sid => $catData) {
        foreach ($catData as $cat => $subData) {
            foreach ($subData as $sub => $cell) {
                $marksLookup[$sid][$cat][$sub] = $cell;
            }
        }
    }

    $printParams = request()->only([
        'academic_year_id', 'standard_id', 'division_id', 'exam_master_id', 'skill',
    ]);

    // Calculate dynamic column widths to avoid horizontal scrolling
    $totalMarkCols = 0;
    foreach ($columnGroups as $criteria) {
        $totalMarkCols += count($criteria);
    }

    $wGr = 4;
    $wRoll = 4;
    $wName = 22;
    $wTotal = 6;
    $wGrade = 6;
    $wStatus = 8;
    
    $fixedWidthSum = $wGr + $wRoll + $wName + $wTotal + $wGrade + $wStatus;
    $remainingWidth = 100 - $fixedWidthSum;
    $wMarkCol = $totalMarkCols > 0 ? ($remainingWidth / $totalMarkCols) : 0;

    $palette = [
        ['top' => '#1e40af', 'sub' => '#3b82f6'],
        ['top' => '#166534', 'sub' => '#22c55e'],
        ['top' => '#6b21a8', 'sub' => '#a855f7'],
        ['top' => '#9a3412', 'sub' => '#f97316'],
        ['top' => '#115e59', 'sub' => '#14b8a6'],
        ['top' => '#be123c', 'sub' => '#f43f5e'],
    ];
@endphp

<div class="skill-page p-3">
    <div class="page-header">
        <div>
            <h2>Student Skill Marks Entry</h2>
            <p>Enter marks for all components. Total and Grade are auto-calculated.</p>
        </div>
        <div class="action-row no-print">
            
            <a href="{{ route('exam-progress.index', array_merge(request()->only(['academic_year_id', 'standard_id', 'division_id', 'exam_master_id']), ['subject_type' => 'marks'])) }}" class="btn btn-secondary">
                ⬅ Back
            </a>

            @if($hasData)
                <a href="{{ route('student-skill-marks.print', $printParams) }}" target="_blank" class="btn btn-print">🖨 Print</a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert" style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;">⚠ {{ session('error') }}</div>
    @endif

    <form method="GET" action="{{ route('student-skill-marks.index') }}" id="marksFilterForm">
        <div class="skill-card no-print">
            <div class="filter-section-title">Filters</div>
            <div class="filter-row">
                <div class="filter-group">
                    <label for="ms_year" class="filter-label">Academic Year <span class="req">*</span></label>
                    <select name="academic_year_id" id="ms_year" class="filter-select" required>
                        <option value="">Select</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->year_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="ms_standard" class="filter-label">Standard <span class="req">*</span></label>
                    <select name="standard_id" id="ms_standard" class="filter-select" required>
                        <option value="">Select</option>
                        @foreach($standards as $std)
                            <option value="{{ $std->id }}" {{ request('standard_id') == $std->id ? 'selected' : '' }}>{{ $std->standard_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="ms_division" class="filter-label">Division <span class="req">*</span></label>
                    <select name="division_id" id="ms_division" class="filter-select" required>
                        <option value="">Select</option>
                        @foreach($divisions as $div)
                            <option value="{{ $div->id }}" {{ request('division_id') == $div->id ? 'selected' : '' }}>{{ $div->division_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="ms_exam" class="filter-label">Exam <span class="req">*</span></label>
                    <select name="exam_master_id" id="ms_exam" class="filter-select" required>
                        <option value="">Select</option>
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}" data-standard-id="{{ $exam->standard_id }}" {{ request('exam_master_id') == $exam->id ? 'selected' : '' }}>{{ $exam->exam_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="ms_skill" class="filter-label">Skill <span class="req">*</span></label>
                    <select name="skill" id="ms_skill" class="filter-select" required>
                        <option value="S1" {{ $selectedSkill === 'S1' ? 'selected' : '' }}>Skill 1 — Arts</option>
                        <option value="S2" {{ $selectedSkill === 'S2' ? 'selected' : '' }}>Skill 2 — Work Exp</option>
                        <option value="S3" {{ $selectedSkill === 'S3' ? 'selected' : '' }}>Skill 3 — Physical Education &amp; Health</option>
                        <option value="S4" {{ $selectedSkill === 'S4' ? 'selected' : '' }}>Skill 4 — Other (Library &amp; Sanskrit Bharti)</option>
                    </select>
                </div>

                <div class="filter-group filter-group-btn">
                    <label class="filter-label" style="visibility:hidden;">Action</label>
                    <div style="display:flex;gap:8px;">
                        <button type="submit" class="btn btn-primary">Load</button>
                        <a href="{{ route('student-skill-marks.index') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @if($hasData)
        <div class="info-banner no-print">
            <span class="info-item">{{ count($students) }} Students</span>
            <span class="info-sep">|</span>
            <span class="info-item"><span class="info-key">Standard:</span> {{ $standard->standard_name ?? '-' }}</span>
            <span class="info-sep">|</span>
            <span class="info-item"><span class="info-key">Division:</span> {{ $division->division_name ?? '-' }}</span>
            <span class="info-sep">|</span>
            <span class="info-item"><span class="info-key">Exam:</span> {{ $examMaster->exam_name ?? '-' }}</span>
            <span class="info-sep">|</span>
            <span class="info-item"><span class="info-key">Skill:</span> {{ $skillLabel ?: '-' }}</span>
            <span class="info-sep">|</span>
            <span class="info-item"><span class="info-key">Skill Teacher:</span> {{ $skillTeacher }}</span>
            <span class="info-sep">|</span>
            <span class="info-item"><span class="info-key">Max:</span> {{ $grandMax }}</span>

            <div class="grade-legend">
                <span style="font-weight:700;">Grade Scale:</span>
                @foreach(['A1'=>'91+','A2'=>'81+','B1'=>'71+','B2'=>'61+','C1'=>'51+','C2'=>'41+','D'=>'33+'] as $g => $range)
                    <span class="grade-pill">{{ $g }} {{ $range }}</span>
                @endforeach
            </div>
        </div>

        <form method="POST" action="{{ route('student-skill-marks.save') }}" id="marksForm">
            @csrf
            <input type="hidden" name="academic_year_id" value="{{ request('academic_year_id') }}">
            <input type="hidden" name="standard_id" value="{{ request('standard_id') }}">
            <input type="hidden" name="division_id" value="{{ request('division_id') }}">
            <input type="hidden" name="exam_master_id" value="{{ request('exam_master_id') }}">
            <input type="hidden" name="skill" value="{{ $selectedSkill }}">
            <input type="hidden" name="max_marks" value="{{ $grandMax }}">

            <div class="table-wrapper">
                <table class="entry-table" id="marksTable">
                    <colgroup>
                        <col style="width: {{ $wGr }}%;">
                        <col style="width: {{ $wRoll }}%;">
                        <col style="width: {{ $wName }}%;">
                        @foreach($columnGroups as $subName => $criteria)
                            @foreach($criteria as $criterionName => $mx)
                                <col style="width: {{ $wMarkCol }}%;">
                            @endforeach
                        @endforeach
                        <col style="width: {{ $wTotal }}%;">
                        <col style="width: {{ $wGrade }}%;">
                        <col style="width: {{ $wStatus }}%;">
                    </colgroup>
                    <thead>
                        <tr class="hdr-top">
                            <th rowspan="2">GR NO</th>
                            <th rowspan="2">Roll No</th>
                            <th rowspan="2" class="left">Student Name</th>

                            @php $colorIndex = 0; @endphp
                            @foreach($columnGroups as $subName => $criteria)
                                @php 
                                    $colSpan = count($criteria); 
                                    $c = $palette[$colorIndex % count($palette)];
                                    $colorIndex++;
                                    
                                    $displaySubName = $subName;
                                    $stdName = strtoupper(trim($standard->standard_name ?? ''));
                                    if (strtoupper(trim($subName)) === 'ABACUS' && in_array($stdName, ['EIGHTH', 'NINTH', 'TENTH', 'VIII', 'IX', 'X'])) {
                                        $displaySubName = 'ABACUS/VEDIC MATH';
                                    }
                                @endphp
                                <th colspan="{{ $colSpan }}" class="th-cat" style="background-color: {{ $c['top'] }}; border-color: {{ $c['top'] }};">{{ $displaySubName }}</th>
                            @endforeach

                            <th rowspan="2" class="th-total subject-sep">Total<br>({{ $grandMax }})</th>
                            <th rowspan="2">Grade</th>
                            <th rowspan="2">Status</th>
                        </tr>

                        <tr class="hdr-sub">
                            @php $colorIndex = 0; @endphp
                            @foreach($columnGroups as $subName => $criteria)
                                @php 
                                    $c = $palette[$colorIndex % count($palette)];
                                    $colorIndex++;
                                @endphp
                                @foreach($criteria as $criterionName => $mx)
                                    <th class="th-sub" style="background-color: {{ $c['sub'] }}; border-color: {{ $c['sub'] }};">{{ $criterionName }}<br><span style="font-size:11px; font-weight:400;">({{ (int)$mx }})</span></th>
                                @endforeach
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                    @foreach($students as $student)
                        @php
                            $sid = $student->Studentid ?? $student->studentid ?? $student->id ?? 0;
                            $nameP1 = $student->studname ?? $student->StudentName ?? '';
                            $nameP2 = $student->fathername ?? '';
                            $fullName = trim($nameP1 . ' ' . $nameP2) ?: '-';
                            $rollNo = $student->rollno ?? $student->RollNo ?? '';
                            $grNo = $student->regno ?? $student->RegNo ?? '';

                            $savedTotal = 0;
                            foreach ($columnGroups as $subName => $criteria) {
                                foreach ($criteria as $criterionName => $mx) {
                                    $markCategory = $subName;
                                    $markSubject = $criterionName;
                                    $savedCell = $marksLookup[$sid][$markCategory][$markSubject] ?? null;
                                    $value = $savedCell['marks_obtained'] ?? null;
                                    if ($value !== null && $value !== '') {
                                        $savedTotal += (float)$value;
                                    }
                                }
                            }
                        @endphp

                        <tr class="mark-row" data-student="{{ $sid }}">
                            <td>{{ $grNo ?: '-' }}</td>
                            <td>{{ $rollNo ?: '-' }}</td>
                            <td class="left student-name">{{ $fullName }}</td>

                            @foreach($columnGroups as $subName => $criteria)
                                @foreach($criteria as $criterionName => $mx)
                                    @php
                                        $markCategory = $subName;
                                        $markSubject = $criterionName;
                                        $savedCell = $marksLookup[$sid][$markCategory][$markSubject] ?? null;
                                        $value = $savedCell['marks_obtained'] ?? '';
                                    @endphp

                                    <td>
                                        <input type="number"
                                               name="marks[{{ $sid }}][{{ $markCategory }}][{{ $markSubject }}][obtained]"
                                               class="mark-input"
                                               data-student="{{ $sid }}"
                                               data-cat="{{ $markCategory }}"
                                               data-sub="{{ $markSubject }}"
                                               data-group="{{ $subName }}"
                                               data-max="{{ $mx }}"
                                               value="{{ $value }}"
                                               min="0" max="{{ $mx }}" placeholder="—">

                                        <input type="hidden"
                                               name="marks[{{ $sid }}][{{ $markCategory }}][{{ $markSubject }}][max_marks]"
                                               value="{{ $mx }}">
                                    </td>
                                @endforeach
                            @endforeach

                            <td class="grand-total-cell subject-sep">{{ $savedTotal }}</td>
                            <td class="grade-cell">—</td>
                            <td>
                                <span class="status-cell status-present">PRESENT</span>

                                <input type="hidden" name="student_names[{{ $sid }}]" value="{{ $fullName }}">
                                <input type="hidden" name="roll_nos[{{ $sid }}]" value="{{ $rollNo }}">
                                <input type="hidden" name="gr_nos[{{ $sid }}]" value="{{ $grNo }}">
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="action-row" style="margin-top:14px;justify-content:space-between;align-items:center;">
                <div style="flex:0 0 340px;">
                    <label class="filter-label" style="display:block;margin-bottom:4px;">Skill Teacher Name</label>
                    <input type="text" name="skill_teacher_name" class="filter-select" style="background-image:none;padding-right:10px;" value="{{ $skillTeacher }}" readonly>
                </div>
                <div style="display:flex;gap:10px;">
                    <a href="{{ route('student-skill-marks.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Marks</button>
                </div>
            </div>
        </form>

    @elseif(request()->anyFilled(['academic_year_id','standard_id','division_id','exam_master_id']))
        <div class="empty-state">
            <div class="empty-state-title">No students found</div>
            <div class="empty-state-subtitle">No students found for the selected filters.</div>
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-title">Select your filters above</div>
            <div class="empty-state-subtitle">Pick Academic Year, Standard, Division, Exam and Skill, then click <strong>Load</strong>.</div>
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('marksFilterForm');
    
    // Auto-submit when ANY of the dropdowns change
    if (form) {
        const selects = form.querySelectorAll('select');
        selects.forEach(function(select) {
            select.addEventListener('change', function() {
                form.submit();
            });
        });
    }

    function gradeFromPct(p) {
        if (p >= 91) return 'A1';
        if (p >= 81) return 'A2';
        if (p >= 71) return 'B1';
        if (p >= 61) return 'B2';
        if (p >= 51) return 'C1';
        if (p >= 41) return 'C2';
        if (p >= 33) return 'D';
        if (p >= 21) return 'E1';
        if (p >= 1) return 'E2';
        return '—';
    }

    const GRAND_MAX = {{ (int)$grandMax }};

    function updateRow(row) {
        let total = 0;
        row.querySelectorAll('.mark-input').forEach(input => {
            const value = parseFloat(input.value);
            if (!Number.isNaN(value)) total += value;
        });

        const totalCell = row.querySelector('.grand-total-cell');
        const gradeCell = row.querySelector('.grade-cell');

        totalCell.textContent = total;
        gradeCell.textContent = (total > 0 && GRAND_MAX > 0) ? gradeFromPct((total / GRAND_MAX) * 100) : '—';
    }

    const csrfToken = document.querySelector('input[name="_token"]').value;
    let saveTimeouts = {};

    function triggerAutoSave(row) {
        const sid = row.dataset.student;
        if (!sid) return;

        const academic_year_id = document.querySelector('input[name="academic_year_id"]').value;
        const standard_id = document.querySelector('input[name="standard_id"]').value;
        const division_id = document.querySelector('input[name="division_id"]').value;
        const exam_master_id = document.querySelector('input[name="exam_master_id"]').value;
        const skill = document.querySelector('input[name="skill"]').value;
        const skill_teacher_name = document.querySelector('input[name="skill_teacher_name"]').value;
        
        const student_name = row.querySelector(`input[name="student_names[${sid}]"]`).value;
        const roll_no = row.querySelector(`input[name="roll_nos[${sid}]"]`).value;
        const gr_no = row.querySelector(`input[name="gr_nos[${sid}]"]`).value;

        const marks = {};
        row.querySelectorAll('.mark-input').forEach(input => {
            const cat = input.dataset.cat;
            const sub = input.dataset.sub;
            if (!marks[cat]) marks[cat] = {};
            if (!marks[cat][sub]) marks[cat][sub] = {};
            marks[cat][sub]['obtained'] = input.value;
        });

        row.style.opacity = '0.6';

        fetch('{{ route("student-skill-marks.auto-save") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                academic_year_id, standard_id, division_id, exam_master_id, skill, skill_teacher_name,
                student_id: sid,
                student_name, roll_no, gr_no,
                marks
            })
        })
        .then(res => res.json())
        .then(data => {
            row.style.opacity = '1';
            if (data.success) {
                const totalCell = row.querySelector('.grand-total-cell');
                const gradeCell = row.querySelector('.grade-cell');
                if (totalCell) totalCell.textContent = data.total;
                if (gradeCell) gradeCell.textContent = data.grade;

                const originalBg = row.style.backgroundColor;
                row.style.backgroundColor = '#dcfce7';
                setTimeout(() => { row.style.backgroundColor = originalBg; }, 800);
            }
        })
        .catch(err => {
            row.style.opacity = '1';
            console.error('Auto-save failed:', err);
        });
    }

    document.querySelectorAll('.mark-input').forEach(input => {
        input.addEventListener('input', function () {
            const max = parseFloat(this.dataset.max) || 0;
            const value = parseFloat(this.value);
            if (!Number.isNaN(value)) {
                if (value > max) this.value = max;
                if (value < 0) this.value = 0;
            }
            
            const row = this.closest('tr');
            updateRow(row);

            const sid = row.dataset.student;
            if (saveTimeouts[sid]) clearTimeout(saveTimeouts[sid]);
            saveTimeouts[sid] = setTimeout(() => {
                triggerAutoSave(row);
            }, 800);
        });
    });

    document.querySelectorAll('.mark-row').forEach(updateRow);
});
</script>
</x-app-layout>