<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Progress Card Details — {{ $report->full_student_name ?? '' }} ({{ $termLabel ?? 'Exam' }})</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 13px; background: #f0f0f0; margin: 0; padding: 20px; color: #111; }
        .card { max-width: 1100px; margin: auto; background: #fff; border: 1px solid #d1d5db; border-radius: 8px; padding: 20px; box-shadow: 0 4px 10px rgba(0,0,0,.08); }
        h1 { font-size: 20px; color: #2563eb; margin: 0 0 6px; }
        .subtitle { color: #6b7280; margin-bottom: 16px; font-size: 12px; }
        .student-info { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 10px 12px; border-radius: 5px; margin-bottom: 20px; font-size: 12px; }
        .term-badge { display: inline-block; background: #d97706; color: #fff; padding: 3px 10px; border-radius: 4px; font-weight: 700; font-size: 12px; margin-left: 6px; }
        .section { border: 1px solid #d1d5db; border-radius: 6px; margin-bottom: 18px; overflow: hidden; }
        .section-head { background: #dbeafe; color: #1e3a8a; font-weight: 700; padding: 8px 12px; font-size: 13px; border-bottom: 1px solid #d1d5db; }
        .section-body { padding: 12px; }
        .row { display: grid; gap: 12px; margin-bottom: 10px; }
        .row.cols-2 { grid-template-columns: 1fr 1fr; }
        .row.cols-3 { grid-template-columns: 1fr 1fr 1fr; }
        .row.cols-4 { grid-template-columns: 1fr 1fr 1fr 1fr; }
        label { display: block; font-weight: 600; margin-bottom: 4px; font-size: 12px; color: #374151; }
        input[type="text"], input[type="email"], input[type="number"] { width: 100%; height: 32px; padding: 4px 8px; border: 1px solid #9ca3af; border-radius: 4px; font-size: 12px; background: #fff; }
        input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,.15); }
        input[readonly] { background: #f3f4f6 !important; cursor: not-allowed !important; color: #6b7280 !important; }
        input[readonly]:focus { border-color: #9ca3af; box-shadow: none; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        table th, table td { border: 1px solid #d1d5db; padding: 5px 6px; vertical-align: middle; }
        table th { background: #f3f4f6; font-weight: 700; text-align: center; color: #374151; }
        table td input { width: 100%; height: 26px; padding: 3px 5px; border: 1px solid #d1d5db; border-radius: 3px; font-size: 12px; text-align: center; }
        table td.center { text-align: center; }
        .grade-cell { text-align: center; font-weight: 700; color: #1e40af; }
        .graded-row { background: #fef3c7; }
        .btn-row { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
        .btn { padding: 8px 20px; border: 0; border-radius: 5px; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; }
        .btn-save { background: #16a34a; color: #fff; } .btn-save:hover { background: #15803d; }
        .btn-back { background: #6b7280; color: #fff; } .btn-back:hover { background: #4b5563; }
        .alert-success { background: #dcfce7; border: 1px solid #86efac; color: #166534; padding: 9px 12px; border-radius: 5px; margin-bottom: 14px; font-weight: 600; }
        .alert-error { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 9px 12px; border-radius: 5px; margin-bottom: 14px; }

        /* ---------- Attendance table: equal width columns ---------- */
        .attendance-table { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 12px; }
        .attendance-table th, .attendance-table td { border: 1px solid #d1d5db; padding: 5px 6px; vertical-align: middle; width: 20%; }
        .attendance-table th { background: #f3f4f6; font-weight: 700; text-align: center; color: #374151; }
        .attendance-table td input { width: 100%; height: 26px; padding: 3px 5px; border: 1px solid #d1d5db; border-radius: 3px; font-size: 12px; text-align: center; }

        /* ---------- Subject & Grade table ---------- */
        .subject-table { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 12px; }
        .subject-table th, .subject-table td { border: 1px solid #d1d5db; padding: 5px 6px; vertical-align: middle; }
        .subject-table th { background: #f3f4f6; font-weight: 700; text-align: center; color: #374151; font-size: 11px; }
        .subject-table td.center { text-align: center; }
        .subject-table td.grade-cell { text-align: center; font-weight: 700; color: #1e40af; }

        /* ---------- Descriptive Record table ---------- */
        .desc-table { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 11px; }
        .desc-table th, .desc-table td { border: 1px solid #d1d5db; padding: 4px 5px; vertical-align: middle; }
        .desc-table th { background: #f3f4f6; font-weight: 700; text-align: center; font-size: 11px; }
        .desc-table td.center { text-align: center; }
        .desc-table td input { width: 100%; height: 26px; padding: 3px 5px; border: 1px solid #d1d5db; border-radius: 3px; font-size: 11px; text-align: left; }

        .sub-title-bar {
            font-weight: 700;
            font-size: 11px;
            background: #e5e7eb;
            border: 1px solid #d1d5db;
            border-bottom: 0;
            padding: 6px 8px;
            text-transform: uppercase;
            letter-spacing: .4px;
            text-align: center;
            color: #374151;
            margin-top: 12px;
        }

        @media (max-width: 800px) {
            .row.cols-2, .row.cols-3, .row.cols-4 { grid-template-columns: 1fr; }
        }
    </style>
</head>

<body>

<div class="card">

    <h1>Progress Card — Enter Details</h1>

    <div class="subtitle">Fill in the fields below. These values will appear on the printed Progress Card.</div>

    @if(session('success'))
        <div class="alert-success">✓ {{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert-error">
            <strong>Please correct:</strong>
            <ul style="margin:6px 0 0 20px;">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="student-info">
        <strong>{{ $report->full_student_name ?? '' }}</strong>
        &nbsp;|&nbsp; ID: <strong>{{ $report->student_id ?? '-' }}</strong>
        &nbsp;|&nbsp; Roll: <strong>{{ $report->rollno ?? '-' }}</strong>
        &nbsp;|&nbsp; Class: <strong>{{ $report->standard_name ?? '' }} - {{ $report->division_name ?? '' }}</strong>
        &nbsp;|&nbsp; Exam: <strong>{{ $report->exam_name ?? '' }}</strong>
        &nbsp;|&nbsp; Year: <strong>{{ $report->year_name ?? '' }}</strong>
        <span class="term-badge">{{ $termLabel ?? 'Exam' }}</span>
    </div>

    <form method="POST" action="{{ route('report-card.progress-card.save', [$report->student_id, $report->exam_master_id, $report->academic_year_id]) }}">
        @csrf

        <div class="section">
            <div class="section-head">Contact & Health Info</div>
            <div class="section-body">
                <div class="row cols-3">
                    <div><label>Ph / Mobile</label><input type="text" name="phone" value="{{ old('phone', $progress->phone ?? '') }}" maxlength="40"></div>
                    <div><label>Email</label><input type="email" name="email" value="{{ old('email', $progress->email ?? '') }}" maxlength="80"></div>
                    <div><label>School Timing</label><input type="text" name="school_timing" value="{{ old('school_timing', $progress->school_timing ?? '') }}" maxlength="80" placeholder="e.g. 7:00 AM - 12:30 PM"></div>
                </div>
                <div class="row cols-3">
                    <div><label>Blood Group</label><input type="text" name="blood_group" value="{{ old('blood_group', $progress->blood_group ?? '') }}" maxlength="10"></div>
                    <div><label>Mother Tongue</label><input type="text" name="mother_tongue" value="{{ old('mother_tongue', $progress->mother_tongue ?? ($report->mother_tongue ?? 'MARATHI')) }}" maxlength="40"></div>
                    <div></div>
                </div>
                <div class="row cols-4">
                    <div><label>I Term — Weight (kg)</label><input type="text" name="term1_weight" value="{{ old('term1_weight', $progress->term1_weight ?? '') }}" maxlength="20"></div>
                    <div><label>I Term — Height (cm)</label><input type="text" name="term1_height" value="{{ old('term1_height', $progress->term1_height ?? '') }}" maxlength="20"></div>
                    <div><label>II Term — Weight (kg)</label><input type="text" name="term2_weight" value="{{ old('term2_weight', $progress->term2_weight ?? '') }}" maxlength="20"></div>
                    <div><label>II Term — Height (cm)</label><input type="text" name="term2_height" value="{{ old('term2_height', $progress->term2_height ?? '') }}" maxlength="20"></div>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-head">Teacher's Remarks</div>
            <div class="section-body">
                <div class="row cols-2">
                    <div><label>Passed and promoted to</label><input type="text" name="passed_promoted_to" value="{{ old('passed_promoted_to', $progress->passed_promoted_to ?? '') }}" maxlength="80"></div>
                    <div><label>School reopens on</label><input type="text" name="school_reopens_on" value="{{ old('school_reopens_on', $progress->school_reopens_on ?? '') }}" maxlength="80"></div>
                </div>
            </div>
        </div>

        @php
            $monthOrder = ['Jyeshtha','Ashadh','Shravan','Bhadrapad','Ashwin','Kartik','Margshirsh','Paush','Magh','Phalgun','Chaitra','Vaishakh'];
            $attendance = $progress->attendance_data ?? [];

            $defaultTotalDays = [
                'Jyeshtha'   => 31,
                'Ashadh'     => 31,
                'Shravan'    => 31,
                'Bhadrapad'  => 31,
                'Ashwin'     => 30,
                'Kartik'     => 30,
                'Margshirsh' => 30,
                'Paush'      => 30,
                'Magh'       => 30,
                'Phalgun'    => 30,
                'Chaitra'    => 30,
                'Vaishakh'   => 31,
            ];
        @endphp

        <div class="section">
            <div class="section-head">Attendance</div>
            <div class="section-body">
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Total Days</th>
                            <th>Working Days</th>
                            <th>Days Present</th>
                            <th>Days Absent</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthOrder as $month)
                            @php
                                $totalDaysVal   = $defaultTotalDays[$month] ?? '';
                                $workingDaysVal = old("attendance.$month.working", $attendance[$month]['working'] ?? '');
                                $presentVal     = old("attendance.$month.present", $attendance[$month]['present'] ?? '');
                                $absentVal      = old("attendance.$month.absent",  $attendance[$month]['absent']  ?? '');
                            @endphp
                            <tr class="attendance-row" data-month="{{ $month }}">
                                <td><strong>{{ $month }}</strong></td>
                                <td>
                                    <input type="text"
                                           name="attendance[{{ $month }}][total_days]"
                                           value="{{ $totalDaysVal }}"
                                           maxlength="10"
                                           readonly>
                                </td>
                                <td>
                                    <input type="text"
                                           name="attendance[{{ $month }}][working]"
                                           value="{{ $workingDaysVal }}"
                                           maxlength="10"
                                           inputmode="numeric"
                                           pattern="[0-9]*"
                                           class="attendance-working"
                                           placeholder="e.g. 24">
                                </td>
                                <td>
                                    <input type="text"
                                           name="attendance[{{ $month }}][present]"
                                           value="{{ $presentVal }}"
                                           maxlength="10"
                                           inputmode="numeric"
                                           pattern="[0-9]*"
                                           class="attendance-present">
                                </td>
                                <td>
                                    <input type="text"
                                           name="attendance[{{ $month }}][absent]"
                                           value="{{ $absentVal }}"
                                           maxlength="10"
                                           class="attendance-absent"
                                           readonly>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div style="margin-top:8px;font-size:11px;color:#6b7280;">
                    <strong>Note:</strong> <strong>Total Days</strong> is fixed per Marathi calendar month. Enter <strong>Working Days</strong> and <strong>Days Present</strong> — <strong>Days Absent</strong> is calculated automatically.
                </div>
            </div>
        </div>

        @php
            $savedDescriptive = $progress->descriptive_records ?? [];
            $savedGraded      = $progress->second_term_grades ?? [];
        @endphp

        <div class="section">
            <div class="section-head">
                {{ $termLabel ?? 'Exam' }} Subject Grades & Descriptive Record
            </div>
            <div class="section-body">

                <div style="background:#fef3c7; border:1px solid #fcd34d; padding:8px 10px; border-radius:4px; font-size:11px; color:#92400e; margin-bottom:12px;">
                    <strong>Note:</strong> Highlighted rows (ART / W EXP / P ED &amp; HEALTH) are graded-only.
                    Type a grade like <strong>A1</strong>, <strong>B2</strong>, <strong>Good</strong>, or <strong>Excellent</strong>.
                    These do not affect the overall percentage.
                </div>

                {{-- ============ SUBJECT & GRADE TABLE ============ --}}
                <div class="sub-title-bar">Subject &amp; Grade</div>
                <table class="subject-table">
                    <thead>
                        <tr>
                            <th style="width:60px;">#</th>
                            <th style="width:60%;">Subject</th>
                            <th style="width:30%;">{{ $termLabel ?? 'Exam' }} Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subjects as $index => $subject)
                            @php
                                $key  = (int) $subject->subject_id;
                                $isGraded = !empty($subject->is_graded);
                            @endphp
                            <tr class="{{ $isGraded ? 'graded-row' : '' }}">
                                <td class="center">{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ strtoupper($subject->subject_name) }}</strong>
                                </td>
                                <td class="grade-cell">
                                    @if($isGraded)
                                        <input type="text"
                                               name="graded_subjects[{{ $key }}]"
                                               value="{{ old("graded_subjects.$key", $savedGraded[$key] ?? '') }}"
                                               maxlength="15"
                                               placeholder="A1 / Good"
                                               style="text-transform:uppercase;text-align:center;">
                                    @else
                                        {{ $subject->grade ?? '-' }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="center" style="padding:14px;">No subjects found.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- ============ DESCRIPTIVE RECORD TABLE ============ --}}
                <div class="sub-title-bar">Descriptive Record</div>
                <table class="desc-table">
                    <thead>
                        <tr>
                            <th style="width:60px;">#</th>
                            <th style="width:34%;">Exceptional Progress</th>
                            <th style="width:33%;">Interest / Hobbies</th>
                            <th style="width:33%;">Expected Improvements</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subjects as $index => $subject)
                            @php
                                $key  = (int) $subject->subject_id;
                                $desc = $savedDescriptive[$key]
                                     ?? $savedDescriptive[(string) $key]
                                     ?? ['progress' => '', 'interest' => '', 'improvements' => ''];
                            @endphp
                            <tr>
                                <td class="center">{{ $index + 1 }}</td>
                                <td><input type="text" name="descriptive[{{ $key }}][progress]"     value="{{ old("descriptive.$key.progress",     $desc['progress']     ?? '') }}" maxlength="200"></td>
                                <td><input type="text" name="descriptive[{{ $key }}][interest]"     value="{{ old("descriptive.$key.interest",     $desc['interest']     ?? '') }}" maxlength="200"></td>
                                <td><input type="text" name="descriptive[{{ $key }}][improvements]" value="{{ old("descriptive.$key.improvements", $desc['improvements'] ?? '') }}" maxlength="200"></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="center" style="padding:14px;">&nbsp;</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <div style="margin-top:10px;font-size:11px;color:#6b7280;">
                    <strong>Grade codes:</strong>
                    A1 (91-100%) · A2 (81-90%) · B1 (71-80%) · B2 (61-70%) ·
                    C1 (51-60%) · C2 (41-50%) · D (33-40%) · E1 (21-32%) · E2 (Below 21%)
                    · OPT (Optional not taken) · AB (Absent)
                </div>
            </div>
        </div>

        <div class="btn-row">
            <button type="submit" class="btn btn-save">Save Details</button>
            <a href="javascript:history.back()" class="btn btn-back">Cancel</a>
        </div>

    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ---------------------------------------------------------
    // Auto-calculate Days Absent = Working Days - Days Present
    // ---------------------------------------------------------
    function calculateAbsent(row) {
        const workingInput = row.querySelector('.attendance-working');
        const presentInput = row.querySelector('.attendance-present');
        const absentInput  = row.querySelector('.attendance-absent');

        const workingVal = parseInt(workingInput.value) || 0;
        const presentVal = parseInt(presentInput.value) || 0;

        if (workingInput.value.trim() === '' || presentInput.value.trim() === '') {
            absentInput.value = '';
            return;
        }

        let absent = workingVal - presentVal;

        if (absent < 0) {
            absent = 0;
            presentInput.value = workingVal;
        }

        absentInput.value = absent;
    }

    function sanitize(input) {
        input.value = input.value.replace(/[^0-9]/g, '');
    }

    document.querySelectorAll('.attendance-row').forEach(function (row) {
        const workingInput = row.querySelector('.attendance-working');
        const presentInput = row.querySelector('.attendance-present');

        workingInput.addEventListener('input', function () {
            sanitize(this);
            calculateAbsent(row);
        });

        presentInput.addEventListener('input', function () {
            sanitize(this);
            calculateAbsent(row);
        });

        workingInput.addEventListener('blur', function () {
            sanitize(this);
            calculateAbsent(row);
        });

        presentInput.addEventListener('blur', function () {
            sanitize(this);
            calculateAbsent(row);
        });

        calculateAbsent(row);
    });

});
</script>

</body>
</html>