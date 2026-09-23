<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Progress Card — {{ $report->full_student_name ?? '' }} ({{ $termLabel ?? 'Exam' }})</title>

    <style>
        @page { size: A4 portrait; margin: 10mm; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; font-family: 'Times New Roman', Times, serif; font-size: 11px; color: #000; background: #f0f0f0; }
        .page { width: 190mm; min-height: 277mm; margin: 0 auto 8mm; padding: 5mm; background: #fff; page-break-after: always; position: relative; }
        .page:last-child { page-break-after: auto; }

        /* ==========================================================
         | SCHOOL HEADER
         ========================================================== */

        .school-header {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            table-layout: fixed;
            margin-bottom: 8px;
        }

        .school-header td {
            border: none !important;
            vertical-align: middle;
            padding: 0 4px;
        }

        .school-logo-cell {
            width: 60px;
            text-align: center !important;
        }

        .school-logo {
            width: 52px;
            height: 52px;
            object-fit: contain;
        }

        .school-title-cell {
            text-align: center !important;
        }

        .school-name {
            font-size: 13px;
            line-height: 15px;
            font-weight: 700;
            text-align: center;
            white-space: nowrap;
            letter-spacing: 0.2px;
        }

        .school-location {
            font-size: 11px;
            line-height: 13px;
            font-weight: 700;
            text-align: center;
            white-space: nowrap;
        }

        .school-year {
            font-size: 10px;
            line-height: 12px;
            font-weight: 600;
            text-align: center;
            white-space: nowrap;
        }

        .school-address {
            font-size: 9px;
            line-height: 11px;
            text-align: center;
            white-space: nowrap;
            color: #333;
        }

        .school-header-divider {
            border-bottom: 2px solid #000;
            margin-top: 2px;
            margin-bottom: 8px;
        }

        /* ==========================================================
         | SECTION TITLES
         ========================================================== */

        .progress-title {
            text-align: center;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 1px;
            margin: 6px 0 3px;
            text-decoration: underline;
        }

        .session-line {
            text-align: center;
            font-size: 11px;
            margin-bottom: 10px;
        }

        .section-title {
            font-weight: 700;
            font-size: 11px;
            background: #e5e5e5;
            padding: 4px 8px;
            border: 1px solid #000;
            border-bottom: 0;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        /* ==========================================================
         | PERSONAL INFO
         ========================================================== */

        .personal-grid { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .personal-grid td { border: 1px solid #000; padding: 5px 8px; vertical-align: top; height: 26px; }
        .personal-grid .label { font-weight: 700; background: #f5f5f5; width: 22%; white-space: nowrap; }
        .personal-grid .value { width: 28%; }
        .personal-grid .label-wide { width: 18%; }

        /* ==========================================================
         | TWO COLUMN
         ========================================================== */

        .two-col { display: flex; gap: 8px; margin-bottom: 10px; }
        .two-col > div { flex: 1; }

        .grade-chart { width: 100%; border-collapse: collapse; font-size: 10px; }
        .grade-chart th, .grade-chart td { border: 1px solid #000; padding: 3px 5px; text-align: center; }
        .grade-chart th { background: #e5e5e5; font-weight: 700; }

        /* ==========================================================
         | ATTENDANCE TABLE
         | - table-layout: auto → fits content, no wrapping
         | - wide enough columns to hold "Working Days" header
         ========================================================== */

        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            table-layout: auto;
        }
        .attendance-table th,
        .attendance-table td {
            border: 1px solid #000;
            padding: 3px 6px;
            text-align: center;
            white-space: nowrap;
        }
        .attendance-table th {
            background: #e5e5e5;
            font-weight: 700;
        }
        .attendance-table th:first-child,
        .attendance-table td:first-child {
            text-align: left;
            padding-left: 8px;
            white-space: nowrap;
        }

        /* ==========================================================
         | REMARKS / NOTES
         ========================================================== */

        .remarks { border: 1px solid #000; padding: 8px; margin-bottom: 10px; line-height: 1.7; font-size: 11px; }
        .remarks .remark-title { font-weight: 700; margin-bottom: 6px; }
        .remarks ol { margin: 4px 0 4px 18px; padding: 0; }
        .remarks li { margin-bottom: 4px; }

        .important-note { border: 2px solid #000; padding: 8px; margin-bottom: 10px; background: #fafafa; font-size: 11px; }
        .important-note .title { font-weight: 700; text-align: center; text-decoration: underline; margin-bottom: 6px; }
        .important-note ul { margin: 0 0 0 16px; padding: 0; }
        .important-note li { margin-bottom: 3px; }

        /* ==========================================================
         | SIGNATURES
         ========================================================== */

        .signatures { display: flex; justify-content: space-between; margin-top: 30px; padding-top: 6px; page-break-inside: avoid; }
        .signatures .sig-box { text-align: center; flex: 1; font-size: 10px; }
        .signatures .sig-line { border-top: 1px solid #000; margin: 24px 12px 3px; }

        /* ==========================================================
         | SUBJECT TABLE
         | - wider column for subject names to prevent wrapping
         ========================================================== */

        .subject-table { width: 100%; border-collapse: collapse; font-size: 11px; table-layout: fixed; }
        .subject-table th,
        .subject-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: middle;
            white-space: nowrap;
        }
        .subject-table th { background: #e5e5e5; font-weight: 700; text-align: center; font-size: 10px; }
        .subject-table td.center { text-align: center; }
        .subject-table td.subject-name {
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .subject-table .grade-cell { text-align: center; font-weight: 700; }

        /* ==========================================================
         | SUB-TITLE BAR
         ========================================================== */

        .sub-title-bar {
            font-weight: 700;
            font-size: 10px;
            background: #e5e5e5;
            border: 1px solid #000;
            border-bottom: 0;
            padding: 4px 6px;
            text-transform: uppercase;
            letter-spacing: .4px;
            text-align: center;
            margin-top: 12px;
        }

        .sub-title-bar:first-child { margin-top: 0; }

        .subject-table, .desc-table { margin-bottom: 0; }

        /* ---------- Descriptive table ---------- */
        .desc-table { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 10px; }
        .desc-table th, .desc-table td { border: 1px solid #000; padding: 4px 5px; vertical-align: top; line-height: 1.35; }
        .desc-table th { background: #e5e5e5; font-weight: 700; text-align: center; font-size: 10px; }
        .desc-table td.center { text-align: center; }

        /* Consistent row height for both tables */
        .subject-table tbody tr, .desc-table tbody tr { height: 26px; }

        /* ==========================================================
         | PRINT
         ========================================================== */

        @media print {
            body { background: #fff; }
            .page { width: 100%; min-height: auto; margin: 0; padding: 0; page-break-after: always; }
            .page:last-child { page-break-after: auto; }
            .no-print { display: none !important; }
        }

        /* ==========================================================
         | TOOLBAR
         ========================================================== */

        .toolbar { position: fixed; top: 12px; right: 12px; z-index: 9999; display: flex; gap: 6px; }
        .toolbar button, .toolbar a { padding: 6px 14px; background: #2563eb; color: #fff; border: 0; border-radius: 4px; font-size: 12px; font-weight: 600; text-decoration: none; cursor: pointer; }
        .toolbar .btn-print { background: #16a34a; }
        .toolbar .btn-print:hover { background: #15803d; }
        .toolbar .btn-edit  { background: #d97706; }
        .toolbar .btn-edit:hover { background: #b45309; }
        .toolbar .btn-close { background: #6b7280; }
        .toolbar .btn-close:hover { background: #4b5563; }
    </style>
</head>

@php
    use App\Helpers\ResultSheetBladeHelper;

    $months = [
        'Jyeshtha','Ashadh','Shravan','Bhadrapad','Ashwin','Kartik',
        'Margshirsh','Paush','Magh','Phalgun','Chaitra','Vaishakh',
    ];

    // Default total calendar days per Marathi month (fallback for old records)
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

    $savedAttendance  = $progress->attendance_data      ?? [];
    $savedDescriptive = $progress->descriptive_records  ?? [];

    $schoolCode = session('school_code', 'shirgaon');
    $yearLabel  = $report->year_name ?? '';
@endphp

<body>

<div class="toolbar no-print">
    <a class="btn-edit" href="{{ route('report-card.progress-card.edit', [$report->student_id, $report->exam_master_id, $report->academic_year_id]) }}">Edit Details</a>
    <button type="button" class="btn-close" onclick="window.close()">Close</button>
    <button class="btn-print" onclick="window.print()">Print</button>
</div>


{{-- =========================================================
     PAGE 1 — FRONT
========================================================= --}}

<div class="page">

    {{-- School header --}}
    <table class="school-header">
        <tr>
            <td class="school-logo-cell">
                <img src="{{ asset('images/school-logo.png') }}" class="school-logo" alt="School Logo">
            </td>

            <td class="school-title-cell">
                @if($schoolCode === 'chikhali')
                    <div class="school-name">
                        PRAJNANABODHINI ENGLISH MEDIUM SCHOOL CHIKHALI
                    </div>
                    <div class="school-year">
                        Academic Year : {{ $yearLabel }}
                    </div>
                @else
                    <div class="school-name">
                        PRAJNANABODHINI ENGLISH MEDIUM SCHOOL &amp; JR. COLLEGE
                    </div>
                    <div class="school-location">
                        SHIRGAON
                    </div>
                    <div class="school-year">
                        Academic Year : {{ $yearLabel }}
                    </div>
                @endif
            </td>

            <td class="school-logo-cell"></td>
        </tr>
    </table>

    <div class="school-header-divider"></div>

    <div class="progress-title">Progress Card</div>

    <div class="session-line">
        Academic Year: <strong>{{ $report->year_name ?? '________' }}</strong>
        &nbsp;|&nbsp;
        Exam: <strong>{{ $termLabel ?? 'Exam' }}</strong>
    </div>

    <div class="section-title">Personal Information</div>

    <table class="personal-grid">
        <tr><td class="label">Name</td><td class="value" colspan="3">{{ $report->full_student_name ?? '' }}</td></tr>
        <tr><td class="label">Student ID</td><td class="value">{{ $report->student_id ?? '' }}</td><td class="label label-wide">Roll No.</td><td class="value">{{ $report->rollno ?? '' }}</td></tr>
        <tr><td class="label">Standard</td><td class="value">{{ $report->standard_name ?? '' }}</td><td class="label label-wide">Division</td><td class="value">{{ $report->division_name ?? '' }}</td></tr>
        <tr><td class="label">Father's Name</td><td class="value" colspan="3">{{ $report->father_name ?? '' }}</td></tr>
        <tr><td class="label">Mother's Name</td><td class="value" colspan="3">{{ $report->mother_name ?? '' }}</td></tr>
        <tr><td class="label">Mother Tongue</td><td class="value">{{ $report->mother_tongue ?? 'MARATHI' }}</td><td class="label label-wide">Date of Birth</td><td class="value">{{ $report->date_of_birth ?? '' }}</td></tr>
        <tr><td class="label">Address</td><td class="value" colspan="3" style="height:36px;">{{ $report->address ?? '' }}</td></tr>
        <tr><td class="label">Ph / Mob</td><td class="value">{{ $progress->phone ?? '' }}</td><td class="label label-wide">Email Id</td><td class="value">{{ $progress->email ?? '' }}</td></tr>
        <tr><td class="label">Health Info — Blood Group</td><td class="value">{{ $progress->blood_group ?? '' }}</td><td class="label label-wide">School Timing</td><td class="value">{{ $progress->school_timing ?? '' }}</td></tr>
        <tr><td class="label">I Term</td><td class="value">Weight: {{ $progress->term1_weight ?? '____' }} Kg &nbsp; Height: {{ $progress->term1_height ?? '____' }} cm</td><td class="label label-wide">II Term</td><td class="value">Weight: {{ $progress->term2_weight ?? '____' }} Kg &nbsp; Height: {{ $progress->term2_height ?? '____' }} cm</td></tr>
    </table>

    <div class="two-col">
        <div>
            <div class="section-title">Attendance</div>
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
                    @foreach ($months as $month)
                        @php
                            $row = $savedAttendance[$month] ?? [];
                            $totalDays = $row['total_days'] ?? ($defaultTotalDays[$month] ?? '');
                            $working   = $row['working']    ?? '';
                            $present   = $row['present']    ?? '';
                            $absent    = $row['absent']     ?? '';
                        @endphp
                        <tr>
                            <td>{{ $month }}</td>
                            <td>{!! $totalDays !== '' ? e($totalDays) : '&nbsp;' !!}</td>
                            <td>{!! $working   !== '' ? e($working)   : '&nbsp;' !!}</td>
                            <td>{!! $present   !== '' ? e($present)   : '&nbsp;' !!}</td>
                            <td>{!! $absent    !== '' ? e($absent)    : '&nbsp;' !!}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div>
            <div class="section-title">Grade Chart</div>
            <table class="grade-chart">
                <thead><tr><th>Marks</th><th>Grade</th></tr></thead>
                <tbody>
                    <tr><td>91% to 100%</td><td>A1</td></tr>
                    <tr><td>81% to 90%</td> <td>A2</td></tr>
                    <tr><td>71% to 80%</td> <td>B1</td></tr>
                    <tr><td>61% to 70%</td> <td>B2</td></tr>
                    <tr><td>51% to 60%</td> <td>C1</td></tr>
                    <tr><td>41% to 50%</td> <td>C2</td></tr>
                    <tr><td>33% to 40%</td> <td>D</td></tr>
                    <tr><td>21% to 32%</td> <td>E1</td></tr>
                    <tr><td>Below 21%</td>  <td>E2</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="remarks">
        <div class="remark-title">Teacher's Remarks :</div>
        <ol>
            <li>Passed and promoted to {{ $progress->passed_promoted_to ?? '______________________' }}</li>
            <li>School reopens on {{ $progress->school_reopens_on ?? '______________________' }}</li>
        </ol>
    </div>

    <div class="important-note">
        <div class="title">IMPORTANT</div>
        <div style="text-align:center;font-weight:700;margin-bottom:6px;">Dear Parents,</div>
        <ul>
            <li>Study the report card carefully.</li>
            <li>Return the card duly signed immediately.</li>
            <li>Do not fold or spoil the card.</li>
            <li>Report will not be issued unless all dues are cleared.</li>
        </ul>
    </div>

</div>


{{-- =========================================================
     PAGE 2 — SUBJECT GRADES  +  DESCRIPTIVE RECORD
========================================================= --}}

<div class="page">

    {{-- School header --}}
    <table class="school-header">
        <tr>
            <td class="school-logo-cell">
                <img src="{{ asset('images/school-logo.png') }}" class="school-logo" alt="School Logo">
            </td>

            <td class="school-title-cell">
                @if($schoolCode === 'chikhali')
                    <div class="school-name">
                        PRAJNANABODHINI ENGLISH MEDIUM SCHOOL CHIKHALI
                    </div>
                    <div class="school-year">
                        Academic Year : {{ $yearLabel }}
                    </div>
                @else
                    <div class="school-name">
                        PRAJNANABODHINI ENGLISH MEDIUM SCHOOL &amp; JR. COLLEGE
                    </div>
                    <div class="school-location">
                        SHIRGAON
                    </div>
                    <div class="school-year">
                        Academic Year : {{ $yearLabel }}
                    </div>
                @endif
            </td>

            <td class="school-logo-cell"></td>
        </tr>
    </table>

    <div class="school-header-divider"></div>

    <div class="progress-title">Subject-wise Grade Record — {{ $termLabel ?? 'Exam' }}</div>

    <div class="session-line">
        <strong>{{ $report->full_student_name ?? '' }}</strong>
        &nbsp;|&nbsp; Roll No: <strong>{{ $report->rollno ?? '' }}</strong>
        &nbsp;|&nbsp; Std: <strong>{{ $report->standard_name ?? '' }}</strong>
        &nbsp;|&nbsp; Div: <strong>{{ $report->division_name ?? '' }}</strong>
    </div>

    {{-- ============ SUBJECT & GRADE TABLE (top, full width) ============ --}}
    <div class="sub-title-bar">Subject &amp; Grade</div>
    <table class="subject-table">
        <thead>
            <tr>
                <th style="width:60px;">Sr.</th>
                <th>Subject</th>
                <th style="width:160px;">{{ $termLabel ?? 'Exam' }} Grade</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($subjects as $index => $subject)
                <tr>
                    <td class="center">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td class="subject-name">
                        {{ strtoupper($subject->subject_name) }}
                    </td>
                    <td class="grade-cell">
                        {{ $subject->grade ?? ($subject->subject_result ?? '-') }}
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="center" style="padding:20px;">No subjects found for this Standard.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- ============ DESCRIPTIVE RECORD ============ --}}
    <div class="sub-title-bar">Descriptive Record</div>
    <table class="desc-table">
        <thead>
            <tr>
                <th style="width:60px;">Sr.</th>
                <th style="width:34%;">Exceptional Progress</th>
                <th style="width:33%;">Interest / Hobbies</th>
                <th style="width:33%;">Expected Improvements</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($subjects as $index => $subject)
                @php
                    $key  = (int) $subject->subject_id;
                    $desc = $savedDescriptive[$key]
                         ?? $savedDescriptive[(string) $key]
                         ?? ['progress' => '', 'interest' => '', 'improvements' => ''];
                @endphp
                <tr>
                    <td class="center">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $desc['progress'] ?? '' }}</td>
                    <td>{{ $desc['interest'] ?? '' }}</td>
                    <td>{{ $desc['improvements'] ?? '' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="center" style="padding:20px;">&nbsp;</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="subject-table" style="width:70%;margin-top:12px;">
        <tbody>
            <tr>
                <th style="text-align:left;width:50%;">Overall Percentage ({{ $termLabel ?? 'Exam' }})</th>
                <td class="center">{{ $report->percentage !== null ? number_format((float) $report->percentage, 2) . '%' : '-' }}</td>
            </tr>
            <tr>
                <th style="text-align:left;">Overall Grade</th>
                <td class="center">{{ $report->grade ?? '-' }}</td>
            </tr>
            <tr>
                <th style="text-align:left;">Result</th>
                <td class="center" style="font-weight:700;">{{ strtoupper($report->result ?? '-') }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Signatures --}}
    <div class="signatures">
        <div class="sig-box"><div class="sig-line"></div>Class Teacher's Sign</div>
        <div class="sig-box"><div class="sig-line"></div>Principal's Sign</div>
        <div class="sig-box"><div class="sig-line"></div>Parent's Sign</div>
    </div>

</div>

</body>
</html>