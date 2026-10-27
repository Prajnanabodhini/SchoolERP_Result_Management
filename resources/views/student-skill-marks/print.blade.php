<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Skill Marksheet - {{ $standard->standard_name ?? '' }} - {{ $division->division_name ?? '' }}</title>
    <style>
        * { box-sizing: border-box; }
        html, body { width: 100%; max-width: 100%; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 11px; color: #111827; background: #fff; }
        .print-page { width: 100%; padding: 5px; }
        .print-controls { position: fixed; top: 10px; right: 10px; z-index: 99999; display: flex; align-items: center; gap: 8px; }
        .print-btn { display: inline-flex; align-items: center; justify-content: center; padding: 8px 13px; border: 1px solid #374151; border-radius: 4px; font-size: 12px; font-weight: 700; color: #fff; text-decoration: none; cursor: pointer; white-space: nowrap; }
        .print-btn-blue { background: #2563eb; }
        .print-btn-green { background: #15803d; }
        .print-btn-gray { background: #6b7280; }

        /* Updated Header Styles */
        .school-header { width: 100%; border-collapse: collapse; margin-bottom: 3px; }
        .school-header td { border: none; padding: 1px; vertical-align: middle; }
        
        .school-logo-cell { width: 100px; text-align: center !important; }
        .school-logo { width: 88px; height: 88px; object-fit: contain; }
        
        .school-title-cell { text-align: center !important; }
        .school-name { font-size: 16px; line-height: 18px; font-weight: 800; color: #1e3a8a; text-align: center; white-space: nowrap; }
        .school-location { font-size: 12px; line-height: 14px; font-weight: 800; color: #1e3a8a; text-align: center; margin-top: 1px; white-space: nowrap; text-transform: uppercase; }
        .school-address { font-size: 10px; line-height: 12px; font-weight: 700; color: #475569; text-align: center; white-space: nowrap; margin-top: 2px; }
        .school-year { margin-top: 2px; font-size: 11px; line-height: 13px; font-weight: 700; color: #475569; text-align: center; white-space: nowrap; }

        .header-line { border-bottom: 1.5px solid #111827; margin: 4px 0 5px; }
        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        .details-table td { border: none; padding: 3px 4px; font-size: 10.5px; font-weight: 700; vertical-align: middle; }
        .grade-legend { display: flex; flex-wrap: wrap; align-items: center; gap: 5px; margin: 5px 0 7px; font-size: 10px; }
        .grade-pill { display: inline-block; padding: 2px 5px; border: 1px solid #93c5fd; border-radius: 3px; background: #eff6ff; color: #1e40af; font-size: 9px; font-weight: 700; }
        .max-marks { margin-left: auto; font-size: 10px; font-weight: 700; }
        .marks-wrapper { width: 100%; max-width: 100%; overflow: visible; }
        .marks-table { width: 100%; border-collapse: collapse; table-layout: auto; font-family: Arial, sans-serif; }
        .marks-table th { border: 1px solid #1e40af; background: #1e3a8a; color: #fff; padding: 5px 3px; font-size: 9px; font-weight: 700; text-align: center; vertical-align: middle; text-transform: uppercase; line-height: 1.2; overflow-wrap: anywhere; }
        .marks-table thead tr.sub-header th { background: #3b82f6; color: #fff; }
        .marks-table th.category-header { background: #1e40af; }
        .marks-table th.total-header { background: #7c2d12; }
        .marks-table td { border: 1px solid #d1d5db; padding: 4px 3px; text-align: center; vertical-align: middle; font-size: 9px; color: #111827; overflow-wrap: anywhere; }
        .marks-table th.left, .marks-table td.left { text-align: left; }
        .marks-table td.student-name { font-weight: 700; white-space: normal; overflow-wrap: anywhere; }
        .marks-table tbody tr:nth-child(even) { background: #f9fafb; }
        .marks-table .subject-sep { border-left: 2px solid #94a3b8 !important; }
        .marks-table td.total-cell { background: #fff7ed; color: #c2410c; font-weight: 800; }
        .marks-table td.grade-cell { background: #fefce8; color: #a16207; font-weight: 800; }
        .marks-table td.absent-cell { color: #dc2626; font-weight: 800; background: #fef2f2; }
        .signature-row { display: flex; justify-content: space-between; align-items: flex-end; gap: 25px; margin-top: 30px; page-break-inside: avoid; }
        .signature { width: 190px; text-align: center; }
        .signature-line { width: 100%; height: 24px; border-bottom: 1px solid #111827; margin-bottom: 5px; }
        .signature-name { font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .signature-role { font-size: 10px; font-weight: 700; margin-top: 3px; }

        @page { size: A4 landscape; margin: 8mm; }

        @media print {
            html, body { width: 100%; margin: 0; padding: 0; }
            .no-print { display: none !important; }
            .print-page { padding: 0; }
            .marks-wrapper { overflow: visible; }
            .marks-table { width: 100%; }
            .marks-table thead { display: table-header-group; }
            .marks-table tr { page-break-inside: avoid; }
            .school-header, .details-table, .signature-row { page-break-inside: avoid; }
            
            /* Print specific logo sizes */
            .school-logo-cell { width: 86px !important; }
            .school-logo { width: 78px !important; height: 78px !important; }
            
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>

<body>

@php
    $printSchoolCode = session('school_code', 'shirgaon');
    $printYearLabel = $academicYear->year_name ?? '';
    $printExamName = $examMaster->exam_name ?? '';
    $printStandard = $standard->standard_name ?? '';
    $printDivision = $division->division_name ?? '';

    $classTeacherName = $classTeacherName ?? '-';
    $principalName = $principalName ?? '-';
    $logoDataUri = $logoDataUri ?? '';
    $studentGrades = $studentGrades ?? [];
    $studentTotals = $studentTotals ?? [];
    $isExport = $isExport ?? false;

    $activeCats = [];
    foreach ($skillStructure as $categoryName => $criteria) {
        if (!is_array($criteria) || empty($criteria)) continue;
        if ($selectedSkill === 'S1' && strcasecmp(trim((string) $categoryName), 'Sanskrit') === 0) continue;
        $activeCats[] = $categoryName;
    }

    // Use category first layout for ALL slots
    $columnGroups = [];
    foreach ($activeCats as $categoryName) {
        if ($selectedSkill === 'S1' && strcasecmp(trim((string) $categoryName), 'Sanskrit') === 0) {
            continue;
        }
        if (!empty($skillStructure[$categoryName])) {
            $columnGroups[$categoryName] = $skillStructure[$categoryName];
        }
    }

    $grandMax = 0;
    foreach ($columnGroups as $criteria) {
        foreach ($criteria as $maxMarks) { $grandMax += (int) $maxMarks; }
    }

    $marksLookup = [];
    foreach (($existingMarks ?? []) as $studentId => $categoryData) {
        foreach ($categoryData as $categoryName => $subjectData) {
            foreach ($subjectData as $subjectName => $cell) {
                $marksLookup[$studentId][$categoryName][$subjectName] = $cell;
            }
        }
    }

    $getCell = function ($studentId, $groupName, $criterionName) use ($marksLookup) {
        return $marksLookup[$studentId][$groupName][$criterionName] ?? null;
    };

    $rowSummary = [];
    foreach ($students as $student) {
        $studentId = $student->Studentid ?? $student->studentid ?? $student->StudentID ?? $student->id ?? 0;
        $groupStates = [];
        $grandObtained = 0;
        $hasAnyMark = false;

        foreach ($columnGroups as $groupName => $criteria) {
            $groupAbsent = false;
            foreach ($criteria as $criterionName => $maxMarks) {
                $cell = $getCell($studentId, $groupName, $criterionName);
                if (is_array($cell) && !empty($cell['is_absent'])) { $groupAbsent = true; $hasAnyMark = true; }
                $value = is_array($cell) ? ($cell['marks_obtained'] ?? null) : null;
                if ($value !== null && $value !== '') { $hasAnyMark = true; }
            }
            $groupStates[$groupName] = $groupAbsent;
        }

        foreach ($columnGroups as $groupName => $criteria) {
            if (!empty($groupStates[$groupName])) continue;
            foreach ($criteria as $criterionName => $maxMarks) {
                $cell = $getCell($studentId, $groupName, $criterionName);
                $value = is_array($cell) ? ($cell['marks_obtained'] ?? null) : null;
                if ($value !== null && $value !== '') { $grandObtained += (float) $value; }
            }
        }

        $absentGroupCount = count(array_filter($groupStates));
        $groupCount = count($groupStates);
        $allAbsent = ($groupCount > 0 && $absentGroupCount === $groupCount);

        $grade = $studentGrades[$studentId] ?? '';

        if ($allAbsent) {
            $grade = 'AB';
        } elseif (!$grade && $hasAnyMark && $grandMax > 0) {
            $percentage = ($grandObtained / $grandMax) * 100;
            if ($percentage >= 91) $grade = 'A1';
            elseif ($percentage >= 81) $grade = 'A2';
            elseif ($percentage >= 71) $grade = 'B1';
            elseif ($percentage >= 61) $grade = 'B2';
            elseif ($percentage >= 51) $grade = 'C1';
            elseif ($percentage >= 41) $grade = 'C2';
            elseif ($percentage >= 33) $grade = 'D';
            elseif ($percentage >= 21) $grade = 'E1';
            elseif ($percentage >= 1) $grade = 'E2';
            else $grade = 'F';
        }

        $rowSummary[$studentId] = [
            'groupStates' => $groupStates, 'allAbsent' => $allAbsent,
            'total' => $grandObtained, 'grade' => $grade, 'hasAnyMark' => $hasAnyMark,
        ];
    }

    $totalSubCols = 0;
    foreach ($columnGroups as $criteria) { $totalSubCols += count($criteria); }
    // GR, Roll, Name (3) + Subcols + Total, Grade (2)
    $emptyColspan = 3 + $totalSubCols + 2;

    $displaySkillLabel = $skillLabel ?? $selectedSkill;
@endphp

@if(!request('export') && empty($isExport))
    <div class="print-controls no-print">
        <button type="button" class="print-btn print-btn-gray" onclick="window.close()">
            ✖ Close
        </button>
        <button type="button" class="print-btn print-btn-blue" onclick="window.print()">Print / Save PDF</button>
        @if(Route::has('student-skill-marks.export-excel'))
            <a href="{{ route('student-skill-marks.export-excel', request()->all()) }}" class="print-btn print-btn-green">Save as Excel</a>
        @endif
    </div>
@endif

<div class="print-page">

    <table class="school-header">
        <tr>
            <td class="school-logo-cell">
                @if($logoDataUri)
                    <img src="{{ $logoDataUri }}" class="school-logo" alt="School Logo">
                @else
                    <img src="{{ asset('images/school-logo.png') }}" class="school-logo" alt="School Logo">
                @endif
            </td>
            <td class="school-title-cell">
                @if($printSchoolCode === 'chikhali')
                    <div class="school-name">PRAJNANABODHINI ENGLISH MEDIUM SCHOOL CHIKHALI</div>
                    <div class="school-address">S.No. 1517/1, Sonwane Vasti, Chikhali, Tal. Haveli, Dist. Pune, Maharashtra 411062</div>
                @else
                    <div class="school-name">PRAJNANABODHINI ENGLISH MEDIUM SCHOOL &amp; JR. COLLEGE</div>
                    <div class="school-location">SHIRGAON</div>
                    <div class="school-address">Gat No. 227, Shirgaon-Gahunje Rd, At. Post. Shirgaon, Tal. Maval, Dist. Pune, Maharashtra - 410506</div>
                @endif
                <div class="school-year">Academic Year: {{ $printYearLabel }}</div>
            </td>
            <td style="width:100px;border:none!important;"></td>
        </tr>
    </table>

    <div class="header-line"></div>

    <table class="details-table">
        <tr>
            <td><strong>Exam:</strong> {{ $printExamName }}</td>
            <td><strong>Standard:</strong> {{ $printStandard }}</td>
            <td><strong>Division:</strong> {{ $printDivision }}</td>
            <td><strong>Skill:</strong> {{ $displaySkillLabel }}</td>
            <td style="text-align:right"><strong>Total Students:</strong> {{ count($students) }}</td>
        </tr>
    </table>

    <div class="grade-legend">
        <strong>Grade Scale:</strong>
        @foreach(['A1' => '91+','A2' => '81+','B1' => '71+','B2' => '61+','C1' => '51+','C2' => '41+','D'  => '33+','E1' => '21+','E2' => '1+'] as $gradeName => $range)
            <span class="grade-pill">{{ $gradeName }}: {{ $range }}</span>
        @endforeach
        <span class="max-marks">Maximum Marks: {{ $grandMax }}</span>
    </div>

    <div class="marks-wrapper">
        <table class="marks-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width:5%">GR No</th>
                    <th rowspan="2" style="width:5%">Roll No</th>
                    <th rowspan="2" class="left" style="width:20%">Student Name</th>
                    
                    @foreach($columnGroups as $groupName => $criteria)
                        @php $colSpan = count($criteria); @endphp
                        <th colspan="{{ $colSpan }}" class="category-header {{ $loop->first ? 'subject-sep' : '' }}" title="{{ $groupName }}">
                            {{ $groupName }}
                        </th>
                    @endforeach
                    
                    <th rowspan="2" class="total-header" style="width:7%">Total<br>({{ $grandMax }})</th>
                    <th rowspan="2" style="width:6%">Grade</th>
                </tr>
                <tr class="sub-header">
                    @foreach($columnGroups as $groupName => $criteria)
                        @foreach($criteria as $criterionName => $maxMarks)
                            <th>{{ $criterionName }}<br>({{ (int) $maxMarks }})</th>
                        @endforeach
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                    @php
                        $studentId = $student->Studentid ?? $student->studentid ?? $student->StudentID ?? $student->id ?? 0;
                        $namePart1 = $student->studname ?? $student->StudentName ?? $student->student_name ?? '';
                        $namePart2 = $student->fathername ?? '';
                        $fullName = trim($namePart1 . ' ' . $namePart2) ?: '-';
                        $grNo = $student->regno ?? $student->RegNo ?? $student->grno ?? '-';
                        $rollNo = $student->rollno ?? $student->RollNo ?? '-';
                        $summary = $rowSummary[$studentId] ?? ['groupStates'=>[],'allAbsent'=>false,'total'=>0,'grade'=>'','hasAnyMark'=>false];
                    @endphp
                    <tr>
                        <td>{{ $grNo }}</td>
                        <td>{{ $rollNo }}</td>
                        <td class="left student-name">{{ $fullName }}</td>
                        @foreach($columnGroups as $groupName => $criteria)
                            @php $groupAbsent = (bool) ($summary['groupStates'][$groupName] ?? false); @endphp
                            @foreach($criteria as $criterionName => $maxMarks)
                                @php
                                    $cell = $getCell($studentId, $groupName, $criterionName);
                                    $value = is_array($cell) ? ($cell['marks_obtained'] ?? null) : null;
                                @endphp
                                <td class="{{ $groupAbsent ? 'absent-cell' : '' }}">
                                    @if($groupAbsent) AB
                                    @elseif($value !== null && $value !== '') {{ $value }}
                                    @else — @endif
                                </td>
                            @endforeach
                        @endforeach
                        <td class="total-cell">
                            @if($summary['allAbsent']) AB
                            @elseif($summary['hasAnyMark']) {{ rtrim(rtrim(number_format((float) $summary['total'], 2, '.', ''), '0'), '.') }}
                            @else — @endif
                        </td>
                        <td class="grade-cell">{{ $summary['allAbsent'] ? 'AB' : ($summary['grade'] ?: '—') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $emptyColspan }}">No records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="signature-row">
        <div class="signature">
            <div class="signature-line"></div>
            <div class="signature-name">{{ $classTeacherName ?: '-' }}</div>
            <div class="signature-role">Class Teacher</div>
        </div>
        <div class="signature">
            <div class="signature-line"></div>
            <div class="signature-name">{{ $principalName ?: '-' }}</div>
            <div class="signature-role">Principal</div>
        </div>
    </div>

</div>
</body>
</html>