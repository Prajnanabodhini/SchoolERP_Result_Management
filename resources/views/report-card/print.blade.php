<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Progress Card — {{ $report->full_student_name ?? '' }} ({{ $termLabel ?? 'Exam' }})</title>

    <style>
        /* 1.5cm Margin on all sides for Printing/PDF */
        @page { 
            size: A4 portrait; 
            margin: 15mm; 
        }
        
        * { box-sizing: border-box; }
        
        html, body {
            margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif;
            font-size: 9px; color: #111827; background: #e5e7eb; /* Light grey for screen viewing */
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
            overflow: hidden; /* Prevent body scroll */
        }

        .page-container {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 20px;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
        }

        /* The page itself */
        .page {
            /* A4 width minus 1.5cm margin left & right */
            width: 180mm; 
            /* A4 height minus 1.5cm margin top & bottom */
            height: 267mm; 
            background: #ffffff; 
            border: 1.5px solid #000000; /* Page Border */
            padding: 4mm; /* Padding inside the border */
            position: relative;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); 
            display: flex;
            flex-direction: column;
            overflow: hidden; /* Prevent internal scrolling */
        }

        /* ===== HEADER ===== */
        .school-header-card { border-bottom: 2px solid #1e3a8a; padding-bottom: 2px; margin-bottom: 4px; flex: none; }
        .school-header { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .school-header td { border: none !important; vertical-align: middle; padding: 0; }
        .school-logo-cell { width: 50px; text-align: center !important; }
        .school-logo { width: 42px; height: 42px; object-fit: contain; }
        .school-title-cell { text-align: center !important; }
        .school-name { font-size: 12px; line-height: 13px; font-weight: 700; color: #1e3a8a; white-space: nowrap; }
        .school-location { font-size: 9px; line-height: 10px; font-weight: 700; color: #374151; white-space: nowrap; }
        .school-address { font-size: 8px; line-height: 9px; font-weight: 700; color: #6B7280; white-space: nowrap; }
        .school-year { font-size: 8.5px; line-height: 10px; font-weight: 700; color: #6B7280; white-space: nowrap; }

        .progress-title { text-align: center; font-size: 13px; font-weight: 700; color: #1D4ED8; letter-spacing: 0.5px; margin: 2px 0 4px; flex: none; }

        .info-banner {
            background: #eff6ff; border: 1px solid #93c5fd; border-radius: 3px;
            padding: 3px 6px; margin-bottom: 5px; display: flex; align-items: center;
            flex-wrap: wrap; gap: 4px; font-size: 8.5px; flex: none;
        }
        .info-banner .info-item { display: inline-flex; align-items: center; gap: 3px; color: #1e40af; font-weight: 700; }
        .info-banner .info-sep { color: #93c5fd; }
        .info-banner .dot { display: inline-block; width: 4px; height: 4px; border-radius: 50%; background: #2563EB; }
        .term-badge { display: inline-block; background: #d97706; color: #ffffff; padding: 1px 6px; border-radius: 3px; font-weight: 700; font-size: 8px; margin-left: auto; }

        /* ===== SECTIONS & TABLES ===== */
        .section { margin-bottom: 5px; flex: none; }
        
        .sub-title-bar {
            font-weight: 700; font-size: 8.5px; background: #e5e7eb; border: 1px solid #d1d5db;
            border-bottom: 0; padding: 2px 6px; text-transform: uppercase; letter-spacing: 0.3px;
            color: #1e3a8a; border-radius: 3px 3px 0 0;
        }
        .table-wrap { border: 1px solid #d1d5db; border-top: 0; border-radius: 0 0 3px 3px; overflow: hidden; }

        .data-table { width: 100%; border-collapse: collapse; font-size: 8.5px; }
        .data-table thead tr { background: #1e3a8a; color: #ffffff; }
        .data-table th { padding: 2px 4px; font-size: 7.5px; font-weight: 700; text-align: center; border: 1px solid #1e40af; color: #ffffff; text-transform: uppercase; white-space: nowrap; }
        .data-table th.text-left { text-align: left; }
        .data-table td { padding: 2px 4px; border: 1px solid #d1d5db; vertical-align: middle; color: #111827; font-size: 8.5px; }
        .data-table td.center { text-align: center; }
        .data-table tbody tr:nth-child(even) { background: #f9fafb; }

        .info-table { width: 100%; border-collapse: collapse; font-size: 8.5px; }
        .info-table td { border: 1px solid #d1d5db; padding: 2px 4px; vertical-align: middle; }
        .info-table td.label { font-weight: 700; background: #f3f4f6; color: #374151; font-size: 7.5px; text-transform: uppercase; width: 22%; line-height: 1.1; }
        .info-table td.label-wide { width: 18%; }
        .info-table td.value { font-weight: 600; color: #111827; width: 28%; line-height: 1.1; }

        .layout-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 6px; margin-bottom: 5px; flex: none; }

        .remarks { border: 1px solid #fcd34d; background: #fffbeb; border-radius: 3px; padding: 4px 6px; margin-bottom: 5px; font-size: 8.5px; color: #92400e; flex: none; }
        .remarks .remark-title { font-weight: 700; font-size: 8px; margin-bottom: 2px; }
        .remarks ol { margin: 0 0 0 12px; padding: 0; }
        
        .important-note { border: 1px solid #93c5fd; background: #eff6ff; border-radius: 3px; padding: 4px 6px; font-size: 8.5px; color: #1e40af; flex: none; }
        .important-note .title { font-weight: 700; font-size: 8px; border-bottom: 1px solid #bfdbfe; padding-bottom: 1px; margin-bottom: 2px; }
        .important-note ul { margin: 0 0 0 12px; padding: 0; }
        .important-note .greeting { text-align: center; font-weight: 700; margin-bottom: 1px; }

        .summary-table { width: 60%; border-collapse: collapse; font-size: 8.5px; margin-top: 5px; flex: none; }
        .summary-table th, .summary-table td { border: 1px solid #d1d5db; padding: 2px 6px; }
        .summary-table th { background: #f3f4f6; color: #374151; font-size: 7.5px; text-transform: uppercase; text-align: left; font-weight: 700; }
        .summary-table td { text-align: center; font-weight: 700; color: #1e40af; }

        .signatures { display: flex; justify-content: space-between; margin-top: auto; padding-top: 15px; flex: none; }
        .signatures .sig-box { text-align: center; flex: 1; font-size: 8px; font-weight: 700; color: #374151; text-transform: uppercase; }
        .signatures .sig-line { border-top: 1px solid #9ca3af; margin: 15px 20px 3px; }

        /* ===== TOOLBAR (Screen only) ===== */
        .toolbar { position: fixed; top: 14px; right: 14px; z-index: 9999; display: flex; gap: 8px; }
        .toolbar .btn { display: inline-flex; align-items: center; justify-content: center; height: 32px; padding: 0 14px; border-radius: 4px; font-size: 12px; font-weight: 700; text-decoration: none; cursor: pointer; border: 1px solid transparent; }
        .btn-print { background: #16A34A; color: #fff; border-color: #15803D; }
        .btn-edit { background: #d97706; color: #fff; border-color: #b45309; }
        .btn-close { background: #6B7280; color: #fff; border-color: #4B5563; }

        /* ===== PRINT SETTINGS ===== */
        @media print {
            html, body { background: #fff; margin: 0; padding: 0; overflow: hidden; }
            .no-print { display: none !important; }
            .page-container { padding: 0; width: 100%; height: 100%; display: block; }
            
            .page {
                width: 100% !important;
                height: 100% !important; 
                border: 1.5px solid #000000 !important;
                padding: 4mm !important;
                box-shadow: none !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
                transform-origin: top left;
            }
            
            .table-wrap, .data-table, .info-table, .summary-table, .signatures, .important-note, .remarks, .layout-grid, tr, td, th { 
                page-break-inside: avoid !important; 
                break-inside: avoid !important; 
            }
            .data-table tbody tr:nth-child(even) { background: #f9fafb !important; }
        }
    </style>
</head>

@php
    $months = ['Jyeshtha','Ashadh','Shravan','Bhadrapad','Ashwin','Kartik','Margshirsh','Paush','Magh','Phalgun','Chaitra','Vaishakh'];
    $defaultTotalDays = ['Jyeshtha'=>31,'Ashadh'=>31,'Shravan'=>31,'Bhadrapad'=>31,'Ashwin'=>30,'Kartik'=>30,'Margshirsh'=>30,'Paush'=>30,'Magh'=>30,'Phalgun'=>30,'Chaitra'=>30,'Vaishakh'=>31];

    $savedAttendance  = $progress->attendance_data      ?? [];
    $savedDescriptive = $progress->descriptive_records  ?? [];

    $schoolCode = session('school_code', 'shirgaon');
    $yearLabel  = $report->year_name ?? '';

    $std = strtoupper(trim((string) ($report->standard_name ?? '')));
    
    $skillSubMap = [];
    $isTenth = preg_match('/\b(10|X|TENTH)\b/i', $std);
    $isFirstToNinth = preg_match('/\b(1|2|3|4|5|6|7|8|9|I|II|III|IV|V|VI|VII|VIII|IX|FIRST|SECOND|THIRD|FOURTH|FIFTH|SIXTH|SEVENTH|EIGHTH|NINTH)\b/i', $std);

    if ($isTenth) {
        $skillSubMap = ['SKILL 1' => '(Drawing + Acting/Dance/Music/Tabla)','SKILL 2' => '(Robotics + Computer + Vedic Math)','SKILL 3' => '','SKILL 4' => ''];
    } elseif ($isFirstToNinth) {
        $skillSubMap = ['SKILL 1' => '(Drawing + Acting/Dance/Music/Tabla)','SKILL 2' => '(Robotics + Computer + Abacus)','SKILL 3' => '','SKILL 4' => ''];
    } else {
        $skillSubMap = ['SKILL 1' => '(Arts/Drawing)','SKILL 2' => '(Work Experience)','SKILL 3' => '(PT & Health)','SKILL 4' => '(Library & Sanskrit Bharti)'];
    }

    $_nonSkills = collect($subjects)->filter(fn($s) => empty($s->is_graded))->values();
    $_skills = collect($subjects)->filter(fn($s) => !empty($s->is_graded))->sortBy(function($s) {
        if (preg_match('/^SKILL\s*([1-9])(?![0-9])/i', $s->subject_name, $m)) return (int)$m[1];
        return 1000;
    })->values();
    $subjects = $_nonSkills->concat($_skills);
@endphp

<body>

<div class="toolbar no-print">
    <a class="btn btn-edit" href="{{ route('report-card.progress-card.edit', [$report->student_id, $report->exam_master_id, $report->academic_year_id]) }}">Edit Details</a>
    <button type="button" class="btn btn-close" onclick="window.close()">Close</button>
    <button class="btn btn-print" onclick="printProgressCard()">Print / PDF</button>
</div>

<div class="page-container">
    <div class="page" id="printPage">
        
        {{-- HEADER --}}
        <div class="school-header-card">
            <table class="school-header">
                <tr>
                    <td class="school-logo-cell">
                        <img src="{{ asset('images/school-logo.png') }}" class="school-logo" alt="School Logo">
                    </td>
                    <td class="school-title-cell">
                        @if($schoolCode === 'chikhali')
                            <div class="school-name">PRAJNANABODHINI ENGLISH MEDIUM SCHOOL CHIKHALI</div>
                            <div class="school-address">S.No. 1517/1, Sonwane Vasti, Chikhali, Tal. Haveli, Dist. Pune, Maharashtra 411062</div>
                            <div class="school-year">Academic Year : {{ $yearLabel }}</div>
                        @else
                            <div class="school-name">PRAJNANABODHINI ENGLISH MEDIUM SCHOOL &amp; JR. COLLEGE</div>
                            <div class="school-location">SHIRGAON</div>
                            <div class="school-address">Gat No. 227, Shirgaon-Gahunje Rd, At. Post. Shirgaon, Tal. Maval, Dist. Pune, Maharashtra - 410506</div>
                            <div class="school-year">Academic Year : {{ $yearLabel }}</div>
                        @endif
                    </td>
                    <td class="school-logo-cell"></td>
                </tr>
            </table>
        </div>

        <div class="progress-title">Progress Card</div>

        {{-- INFO BANNER --}}
        <div class="info-banner">
            <span class="info-item"><span class="dot"></span> {{ $report->full_student_name ?? '' }}</span>
            <span class="info-sep">|</span>
            <span class="info-item">ID: {{ $report->student_id ?? '-' }}</span>
            <span class="info-sep">|</span>
            <span class="info-item">Roll: {{ $report->rollno ?? '-' }}</span>
            <span class="info-sep">|</span>
            <span class="info-item">{{ $report->standard_name ?? '' }} - {{ $report->division_name ?? '' }}</span>
            <span class="info-sep">|</span>
            <span class="info-item">Year: {{ $report->year_name ?? '________' }}</span>
            <span class="term-badge">{{ $termLabel ?? 'Exam' }}</span>
        </div>

        {{-- PERSONAL INFO --}}
        <div class="section">
            <div class="sub-title-bar">Personal Information</div>
            <div class="table-wrap">
                <table class="info-table">
                    <tr><td class="label">Name</td><td class="value" colspan="3">{{ $report->full_student_name ?? '' }}</td></tr>
                    <tr><td class="label">Student ID</td><td class="value">{{ $report->student_id ?? '' }}</td><td class="label label-wide">Roll No.</td><td class="value">{{ $report->rollno ?? '' }}</td></tr>
                    <tr><td class="label">Standard</td><td class="value">{{ $report->standard_name ?? '' }}</td><td class="label label-wide">Division</td><td class="value">{{ $report->division_name ?? '' }}</td></tr>
                    <tr><td class="label">Father's Name</td><td class="value" colspan="3">{{ $report->father_name ?? '' }}</td></tr>
                    <tr><td class="label">Mother's Name</td><td class="value" colspan="3">{{ $report->mother_name ?? '' }}</td></tr>
                    <tr><td class="label">Mother Tongue</td><td class="value">{{ $report->mother_tongue ?? 'MARATHI' }}</td><td class="label label-wide">Date of Birth</td><td class="value">{{ $report->date_of_birth ?? '' }}</td></tr>
                    <tr><td class="label">Address</td><td class="value" colspan="3">{{ $report->address ?? '' }}</td></tr>
                    <tr><td class="label">Ph / Mob</td><td class="value">{{ $progress->phone ?? '' }}</td><td class="label label-wide">Email Id</td><td class="value">{{ $progress->email ?? '' }}</td></tr>
                    <tr><td class="label">Health Info</td><td class="value">Blood: {{ $progress->blood_group ?? '' }}</td><td class="label label-wide">School Timing</td><td class="value">{{ $progress->school_timing ?? '' }}</td></tr>
                    <tr><td class="label">I Term</td><td class="value">Weight: {{ $progress->term1_weight ?? '____' }} Kg &nbsp; Height: {{ $progress->term1_height ?? '____' }} cm</td><td class="label label-wide">II Term</td><td class="value">Weight: {{ $progress->term2_weight ?? '____' }} Kg &nbsp; Height: {{ $progress->term2_height ?? '____' }} cm</td></tr>
                </table>
            </div>
        </div>

        {{-- TWO COLUMNS: ATTENDANCE & GRADE CHART --}}
        <div class="layout-grid">
            <div>
                <div class="sub-title-bar">Attendance</div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr><th class="text-left">Month</th><th>Total</th><th>Working</th><th>Present</th><th>Absent</th></tr>
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
                                    <td style="font-weight:700;">{{ $month }}</td>
                                    <td class="center">{!! $totalDays !== '' ? e($totalDays) : '-' !!}</td>
                                    <td class="center">{!! $working   !== '' ? e($working)   : '-' !!}</td>
                                    <td class="center">{!! $present   !== '' ? e($present)   : '-' !!}</td>
                                    <td class="center">{!! $absent    !== '' ? e($absent)    : '-' !!}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div>
                <div class="sub-title-bar">Grade Chart</div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead><tr><th>Marks</th><th>Grade</th></tr></thead>
                        <tbody>
                            <tr><td class="center">91% to 100%</td><td class="center" style="font-weight:700;">A1</td></tr>
                            <tr><td class="center">81% to 90%</td> <td class="center" style="font-weight:700;">A2</td></tr>
                            <tr><td class="center">71% to 80%</td> <td class="center" style="font-weight:700;">B1</td></tr>
                            <tr><td class="center">61% to 70%</td> <td class="center" style="font-weight:700;">B2</td></tr>
                            <tr><td class="center">51% to 60%</td> <td class="center" style="font-weight:700;">C1</td></tr>
                            <tr><td class="center">41% to 50%</td> <td class="center" style="font-weight:700;">C2</td></tr>
                            <tr><td class="center">33% to 40%</td> <td class="center" style="font-weight:700;">D</td></tr>
                            <tr><td class="center">21% to 32%</td> <td class="center" style="font-weight:700;">E1</td></tr>
                            <tr><td class="center">Below 21%</td>  <td class="center" style="font-weight:700;">E2</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- SUBJECT GRADES --}}
        <div class="section">
            <div class="sub-title-bar">Subject &amp; Grade</div>
            <div class="table-wrap">
                <table class="data-table" style="table-layout: fixed;">
                    <thead>
                        <tr><th style="width:30px;">Sr.</th><th class="text-left">Subject</th><th style="width:80px;">{{ $termLabel ?? 'Exam' }} Grade</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($subjects as $index => $subject)
                            @php
                                $skillKey = null;
                                if (preg_match('/^SKILL\s*([1-4])/i', $subject->subject_name, $m)) {
                                    $skillKey = 'SKILL ' . $m[1];
                                }
                            @endphp
                            <tr>
                                <td class="center" style="font-weight:700;">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                <td style="font-weight:600; white-space: normal !important; word-wrap: break-word !important;">
                                    {{ strtoupper($subject->subject_name) }} {{ $skillKey && isset($skillSubMap[$skillKey]) ? $skillSubMap[$skillKey] : '' }}
                                </td>
                                <td class="center" style="font-weight:700; color:#1e40af;">{{ $subject->grade ?? ($subject->subject_result ?? '-') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="center">No subjects found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- DESCRIPTIVE RECORD --}}
        <div class="section">
            <div class="sub-title-bar">Descriptive Record</div>
            <div class="table-wrap">
                <table class="data-table" style="table-layout: fixed;">
                    <thead>
                        <tr><th style="width:30px;">Sr.</th><th class="text-left" style="width:34%;">Exceptional Progress</th><th class="text-left" style="width:33%;">Interest / Hobbies</th><th class="text-left" style="width:33%;">Expected Improvements</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($subjects as $index => $subject)
                            @php
                                $key  = (int) $subject->subject_id;
                                $desc = $savedDescriptive[$key] ?? $savedDescriptive[(string) $key] ?? ['progress' => '', 'interest' => '', 'improvements' => ''];
                            @endphp
                            <tr>
                                <td class="center" style="font-weight:700;">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $desc['progress'] ?? '' }}</td>
                                <td>{{ $desc['interest'] ?? '' }}</td>
                                <td>{{ $desc['improvements'] ?? '' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="center">&nbsp;</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- REMARKS & IMPORTANT --}}
        <div class="layout-grid" style="grid-template-columns: 1fr 1fr;">
            <div>
                <div class="remarks">
                    <div class="remark-title">Teacher's Remarks :</div>
                    <ol>
                        <li>Passed and promoted to <strong>{{ $progress->passed_promoted_to ?? '______________________' }}</strong></li>
                        <li>School reopens on <strong>{{ $progress->school_reopens_on ?? '______________________' }}</strong></li>
                    </ol>
                </div>
            </div>
            <div>
                <div class="important-note">
                    <div class="title">Important</div>
                    <div class="greeting">Dear Parents,</div>
                    <ul>
                        <li>Study the report card carefully.</li>
                        <li>Return the card duly signed immediately.</li>
                        <li>Do not fold or spoil the card.</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- SUMMARY TABLE --}}
        <table class="summary-table">
            <tbody>
                <tr><th>Overall Percentage ({{ $termLabel ?? 'Exam' }})</th><td>{{ $report->percentage !== null ? number_format((float) $report->percentage, 2) . '%' : '-' }}</td></tr>
                <tr><th>Overall Grade</th><td>{{ $report->grade ?? '-' }}</td></tr>
                <tr><th>Result</th><td style="font-weight:700;">{{ strtoupper($report->result ?? '-') }}</td></tr>
            </tbody>
        </table>

        {{-- SIGNATURES --}}
        <div class="signatures">
            <div class="sig-box"><div class="sig-line"></div>Class Teacher's Sign</div>
            <div class="sig-box"><div class="sig-line"></div>Principal's Sign</div>
            <div class="sig-box"><div class="sig-line"></div>Parent's Sign</div>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Function to ensure page fits on A4 and doesn't cause scrolling
        function fitToPage() {
            const page = document.getElementById('printPage');
            if (!page) return;
            
            // Reset styles to measure true height
            page.style.transform = 'none';
            page.style.width = '180mm'; // Default exact width
            
            // Available height for content is roughly 267mm minus padding
            const maxHeightPx = (267 / 25.4) * 96; // ~1009px
            
            if (page.scrollHeight > maxHeightPx) {
                const scale = maxHeightPx / page.scrollHeight;
                page.style.transform = `scale(${scale})`;
                page.style.width = `${100 / scale}%`; 
                page.style.maxWidth = '210mm';
            }
        }

        // Run on load and resize
        window.addEventListener('load', fitToPage);
        window.addEventListener('resize', fitToPage);

        // Run before printing to ensure it fits exactly
        window.addEventListener('beforeprint', function() {
            const page = document.getElementById('printPage');
            if (!page) return;
            
            page.style.transform = 'none';
            page.style.width = '100%';
            
            const maxHeightPx = (267 / 25.4) * 96; 
            
            if (page.scrollHeight > maxHeightPx) {
                const scale = maxHeightPx / page.scrollHeight;
                page.style.transform = `scale(${scale})`;
                page.style.width = `${100 / scale}%`;
                page.style.transformOrigin = 'top left';
            }
        });

        window.addEventListener('afterprint', function() {
            const page = document.getElementById('printPage');
            if (page) {
                page.style.transform = 'none';
                page.style.width = '180mm';
            }
        });
    });

    function printProgressCard() {
        window.focus();
        window.print();
    }
</script>

</body>
</html>