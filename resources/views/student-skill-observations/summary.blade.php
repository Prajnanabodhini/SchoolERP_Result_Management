@if(request('print') || request('export'))
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Skill Observation Summary</title>
        <style>
            @page { size: A4 landscape; margin: 5mm; }
            html, body { 
                margin: 0; 
                padding: 0; 
                background: #fff; 
                font-family: 'Helvetica Neue', Arial, Helvetica, sans-serif; 
                font-size: 11px; 
                color: #000; 
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact;
                line-height: 1.15; 
                overflow-x: hidden; 
            }
            * { box-sizing: border-box; }
            
            .page { 
                width: 100%; 
                min-height: 100%; 
                padding: 5px; 
                background: #fff; 
            }
            .page-frame { 
                width: 100%; 
                border: 1.5px solid #000; 
                padding: 4.5mm;
                display: flex; 
                flex-direction: column; 
                background: #fff; 
            }

            /* ===== SCHOOL HEADER ===== */
            .school-header-card { padding-bottom: 2px; margin-bottom: 4px; border-bottom: 2px solid #000; flex: none; }
            .school-header { width: 100%; border-collapse: collapse; table-layout: fixed; }
            .school-header td { border: none !important; vertical-align: middle; padding: 0 3px; }
            .school-logo-cell { width: 60px; text-align: center !important; }
            .school-logo { width: 55px; height: 55px; object-fit: contain; }
            .school-title-cell { text-align: center !important; }

            .school-name     { font-size: 14px; line-height: 15px; font-weight: 800; text-align: center; white-space: nowrap; color: #000; }
            .school-location { font-size: 12px; line-height: 13px; font-weight: 800; text-align: center; margin-top: 0; white-space: nowrap; color: #000; }
            .school-address  { font-size: 9px; line-height: 10px; font-weight: 700; text-align: center; margin-top: 0; white-space: nowrap; color: #000; }
            .school-year     { margin-top: 1px; font-size: 10.5px; line-height: 11px; font-weight: 700; text-align: center; white-space: nowrap; color: #000; }

            /* ===== INFO ROW ===== */
            .info-row { width: 100%; border-collapse: collapse; margin-bottom: 5px; border: none; }
            .info-row td { font-size: 11px; padding: 2px; text-align: center; font-weight: 700; white-space: nowrap; border: none; }

            /* ===== DATA TABLES ===== */
            .data-table { width: 100%; border-collapse: collapse; font-size: 11px; table-layout: fixed; }
            .data-table th { background: #f4f4f4; padding: 4px 3px; font-size: 9.5px; font-weight: 800; text-align: center; border: 1px solid #000; text-transform: uppercase; white-space: normal; word-wrap: break-word; line-height: 1.15; color: #000; }
            .data-table td { padding: 3px 4px; border: 1px solid #000; vertical-align: middle; font-size: 10px; line-height: 1.15; font-weight: 400; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #000; }
            .data-table td.center { text-align: center; }
            .data-table th.text-left { text-align: left; }
            .data-table td.text-left { text-align: left; white-space: normal; word-wrap: break-word; }

            /* ===== SIGNATURES ===== */
            .signatures { display: flex; justify-content: space-between; margin-top: auto; padding-top: 15px; page-break-inside: avoid; flex: none; }
            .signatures .sig-box { text-align: center; flex: 1; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; }
            .signatures .sig-line { border-top: 1.5px solid #000; margin: 6px 15px 3px; }

            /* ===== TOOLBAR ===== */
            .toolbar { position: fixed; top: 14px; right: 14px; z-index: 9999; display: flex; gap: 8px; }
            .toolbar .btn { display: inline-flex; align-items: center; justify-content: center; height: 36px; padding: 0 18px; border-radius: 6px; font-family: Arial, sans-serif; font-size: 12.5px; font-weight: 700; text-decoration: none; cursor: pointer; border: 1px solid transparent; transition: all .15s; }
            .btn-print { background: #16A34A; color: #fff; border-color: #15803D; }
            .btn-close { background: #6B7280; color: #fff; border-color: #4B5563; }
            .btn-excel { background: #15803d; color: #fff; border-color: #166534; }

            @media print {
                .toolbar { display: none !important; }
            }
        </style>
    </head>
    <body>
        
        @if(!request('export') && empty($isExport))
        <div class="toolbar no-print">
            <button type="button" class="btn btn-print" onclick="window.print()">🖨 Print / Save PDF</button>
            @if(Route::has('student-skill-observations.export-excel'))
                <a href="{{ route('student-skill-observations.export-excel', request()->only(['academic_year_id', 'standard_id', 'division_id', 'exam_master_id'])) }}" class="btn btn-excel">📊 Save as Excel</a>
            @endif
            <button type="button" class="btn btn-close" onclick="window.close()">✕ Close</button>
        </div>
        @endif

        @php
            $printYearName     = optional($academicYears->firstWhere('id', (int) request('academic_year_id')))->year_name ?? '';
            $printStandardName = optional($standards->firstWhere('id', (int) request('standard_id')))->standard_name ?? '';
            $printDivisionName = optional($divisions->firstWhere('id', (int) request('division_id')))->division_name ?? '';
            $printExamName     = 'All Exams';
            if (request('exam_master_id')) {
                $printExamName = optional($exams->firstWhere('id', (int) request('exam_master_id')))->exam_name ?? 'All Exams';
            }

            $printSchoolCode  = session('school_code', 'shirgaon');
            $classTeacherName = $classTeacherName ?? '-';
            $principalName    = $principalName    ?? '-';
            $logoDataUri      = $logoDataUri ?? '';

            /* ✅ CANONICAL MAPPING SYSTEM */
            $standardId = request('standard_id');
            $selectedStandard = $standards->firstWhere('id', $standardId);
            $stdName = strtolower(trim($selectedStandard->standard_name ?? ''));
            $stdNameCompact = preg_replace('/[^a-z0-9]/', '', $stdName);
            $canonicalMap = [];

            if (in_array($stdNameCompact, ['nursery', 'jrkg', 'srkg', 'preprimary', 'kg', 'kindergarten'])) {
                $canonicalMap = [
                    'CRAFT' => '/craft/i', 'DRAMA' => '/drama/i', 'LIFE SKILL' => '/life/i',
                    'CLAY' => '/clay/i', 'SONG' => '/song/i', 'STORY' => '/stor/i',
                    'INDOOR GAME' => '/indoor/i', 'OUTDOOR GAME' => '/outdoor/i',
                    'BOOKS' => '/book/i', 'TOYS' => '/toy/i',
                ];
            } elseif (preg_match('/(^|\D)(1|2|first|second)(\D|$)/i', $stdName)) {
                $canonicalMap = [
                    'SONG' => '/song/i', 'STORY' => '/stor/i', 'DANCE' => '/dance/i',
                    'SANSKRIT BHARTI' => '/sanskrit.*bharti/i', 'LIBRARY' => '/library/i',
                    'ABACUS' => '/abacus/i', 'PHYSICAL EDUCATION' => '/physical|pe\b|health/i',
                ];
            } elseif (preg_match('/(^|\D)(10|11|12|tenth|eleventh|twelfth)(\D|$)/i', $stdName)) {
                $canonicalMap = [
                    'COMPUTER' => '/computer/i', 'ROBOTICS' => '/robot/i', 'VEDIC MATHS' => '/vedic/i',
                    'PHYSICAL EDUCATION' => '/physical|pe\b|health/i', 'DEFENCE STUDIES' => '/defence|defense/i',
                    'WATER SECURITY' => '/water/i',
                ];
            } elseif (preg_match('/(^|\D)(3|4|5|6|7|8|9|third|fourth|fifth|sixth|seventh|eighth|ninth)(\D|$)/i', $stdName)) {
                $canonicalMap = [
                    'TABLA' => '/tabla/i', 'MUSIC' => '/music/i', 'DANCE' => '/dance/i', 'ACTING' => '/acting|drama/i',
                    'SANSKRIT BOOK' => '/sanskrit/i', 'COMPUTER' => '/computer/i', 'ROBOTICS' => '/robot/i',
                    'PHYSICAL EDUCATION' => '/physical|pe\b|health/i', 'ABACUS' => '/abacus/i',
                ];
            }

            if (!empty($canonicalMap) && !empty($skillSubjects)) {
                $orderedSubjects = collect();
                $usedSubjectIds = [];
                foreach ($canonicalMap as $displayName => $regex) {
                    $matched = $skillSubjects->first(function($sub) use ($regex, $usedSubjectIds) {
                        return !in_array($sub->id, $usedSubjectIds) && preg_match($regex, $sub->subject_name);
                    });
                    if ($matched) {
                        $matched->display_name = $displayName;
                        $orderedSubjects->push($matched);
                        $usedSubjectIds[] = $matched->id;
                    } else {
                        $dummy = new \stdClass();
                        $dummy->id = 0;
                        $dummy->subject_name = $displayName;
                        $dummy->display_name = $displayName;
                        $orderedSubjects->push($dummy);
                    }
                }
                $skillSubjects = $orderedSubjects;
            }
        @endphp

        <div class="page">
            <div class="page-frame">
                {{-- SCHOOL HEADER --}}
                <div class="school-header-card">
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
                                    <div class="school-year">Academic Year : {{ $printYearName }}</div>
                                @else
                                    <div class="school-name">PRAJNANABODHINI ENGLISH MEDIUM SCHOOL &amp; JR. COLLEGE</div>
                                    <div class="school-location">SHIRGAON</div>
                                    <div class="school-address">Gat No. 227, Shirgaon-Gahunje Rd, At. Post. Shirgaon, Tal. Maval, Dist. Pune, Maharashtra - 410506</div>
                                    <div class="school-year">Academic Year : {{ $printYearName }}</div>
                                @endif
                            </td>
                            <td class="school-logo-cell"></td>
                        </tr>
                    </table>
                </div>

                {{-- EXAM INFO ROW --}}
                <table class="info-row">
                    <tr>
                        <td><strong>Exam :</strong> {{ $printExamName }}</td>
                        <td><strong>Standard :</strong> {{ $printStandardName }}</td>
                        <td><strong>Division :</strong> {{ $printDivisionName }}</td>
                        <td><strong>Total Students :</strong> {{ count($students) }}</td>
                    </tr>
                </table>

                {{-- DATA TABLE --}}
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">Roll No</th>
                            <th style="width: 18%;" class="text-left">Student Name</th>
                            @foreach($skillSubjects as $sub)
                                <th>{{ $sub->display_name ?? $sub->subject_name }}</th>
                            @endforeach
                            <th style="width: 7%;">Average Grade</th>
                            <th style="width: 8%;">Remark</th>
                            <th style="width: 6%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                            @php
                                $studentName = trim(($student->studname ?? '') . ' ' . ($student->fathername ?? ''));
                                $hasAbsent = false; $hasPresent = false;
                                foreach ($skillSubjects as $sub) {
                                    $key = $student->Studentid . '|' . $sub->id;
                                    $obs = $observations[$key] ?? null;
                                    if ($obs) { if ($obs->is_absent) $hasAbsent = true; else $hasPresent = true; }
                                }
                                $overallStatus = $hasPresent ? 'PRESENT' : ($hasAbsent ? 'ABSENT' : '-');
                            @endphp
                            <tr>
                                <td class="center">{{ $student->rollno ?: '-' }}</td>
                                <td class="text-left" title="{{ $studentName }}">{{ $studentName ?: '-' }}</td>
                                @foreach($skillSubjects as $sub)
                                    @php
                                        $key = $student->Studentid . '|' . $sub->id;
                                        $obs = $observations[$key] ?? null;
                                        $cell = ''; $cellColor = '#000'; $bgColor = '#fff';
                                        if ($obs) {
                                            if ($obs->is_absent) {
                                                $cell = 'AB'; $cellColor = '#C2410C'; $bgColor = '#FFF7ED';
                                            } else {
                                                $avg = $obs->overall_grade;
                                                if (!$avg && !empty($obs->observations)) {
                                                    $avg = \App\Helpers\SkillCriteriaHelper::computeAverageGrade($obs->observations);
                                                }
                                                $cell = $avg ?: ''; $cellColor = '#000'; $bgColor = '#fff';
                                            }
                                        }
                                    @endphp
                                    <td class="center" style="color: {{ $cellColor }}; background-color: {{ $bgColor }}; font-weight: 700;">{{ $cell }}</td>
                                @endforeach
                                <td class="center" style="font-weight: 700;">{{ $studentAverages[$student->Studentid] ?? '-' }}</td>
                                <td class="center">&nbsp;</td>
                                <td class="center" style="font-weight: 700; color: {{ $overallStatus === 'ABSENT' ? '#DC2626' : ($overallStatus === 'PRESENT' ? '#16A34A' : '#000') }};">{{ $overallStatus }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ 5 + $skillSubjects->count() }}" class="center">No result records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- SIGNATURES --}}
                <div class="signatures">
                    <div class="sig-box">
                        <div class="sig-line"></div>
                        {{ $classTeacherName ?: '-' }}<br>
                        <span style="font-size: 9px;">CLASS TEACHER</span>
                    </div>
                    <div class="sig-box">
                        <div class="sig-line"></div>
                        {{ $principalName ?: '-' }}<br>
                        <span style="font-size: 9px;">PRINCIPAL</span>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    @php return; @endphp
@endif

@extends('layouts.app')

@section('content')
@php
    $gradeLabels = \App\Helpers\SkillCriteriaHelper::gradeLabels();

    $printYearName     = optional($academicYears->firstWhere('id', (int) request('academic_year_id')))->year_name ?? '';
    $printStandardName = optional($standards->firstWhere('id', (int) request('standard_id')))->standard_name ?? '';
    $printDivisionName = optional($divisions->firstWhere('id', (int) request('division_id')))->division_name ?? '';
    $printExamName     = 'All Exams';
    if (request('exam_master_id')) {
        $printExamName = optional($exams->firstWhere('id', (int) request('exam_master_id')))->exam_name ?? 'All Exams';
    }

    $printSchoolCode  = session('school_code', 'shirgaon');
    $classTeacherName = $classTeacherName ?? '-';
    $principalName    = $principalName    ?? '-';
    $logoDataUri      = $logoDataUri ?? '';

    /* ✅ CANONICAL MAPPING SYSTEM */
    $standardId = request('standard_id');
    $selectedStandard = $standards->firstWhere('id', $standardId);
    $stdName = strtolower(trim($selectedStandard->standard_name ?? ''));
    $stdNameCompact = preg_replace('/[^a-z0-9]/', '', $stdName);
    $canonicalMap = [];

    if (in_array($stdNameCompact, ['nursery', 'jrkg', 'srkg', 'preprimary', 'kg', 'kindergarten'])) {
        $canonicalMap = [
            'CRAFT' => '/craft/i', 'DRAMA' => '/drama/i', 'LIFE SKILL' => '/life/i',
            'CLAY' => '/clay/i', 'SONG' => '/song/i', 'STORY' => '/stor/i',
            'INDOOR GAME' => '/indoor/i', 'OUTDOOR GAME' => '/outdoor/i',
            'BOOKS' => '/book/i', 'TOYS' => '/toy/i',
        ];
    } elseif (preg_match('/(^|\D)(1|2|first|second)(\D|$)/i', $stdName)) {
        $canonicalMap = [
            'SONG' => '/song/i', 'STORY' => '/stor/i', 'DANCE' => '/dance/i',
            'SANSKRIT BHARTI' => '/sanskrit.*bharti/i', 'LIBRARY' => '/library/i',
            'ABACUS' => '/abacus/i', 'PHYSICAL EDUCATION' => '/physical|pe\b|health/i',
        ];
    } elseif (preg_match('/(^|\D)(10|11|12|tenth|eleventh|twelfth)(\D|$)/i', $stdName)) {
        $canonicalMap = [
            'COMPUTER' => '/computer/i', 'ROBOTICS' => '/robot/i', 'VEDIC MATHS' => '/vedic/i',
            'PHYSICAL EDUCATION' => '/physical|pe\b|health/i', 'DEFENCE STUDIES' => '/defence|defense/i',
            'WATER SECURITY' => '/water/i',
        ];
    } elseif (preg_match('/(^|\D)(3|4|5|6|7|8|9|third|fourth|fifth|sixth|seventh|eighth|ninth)(\D|$)/i', $stdName)) {
        $canonicalMap = [
            'TABLA' => '/tabla/i', 'MUSIC' => '/music/i', 'DANCE' => '/dance/i', 'ACTING' => '/acting|drama/i',
            'SANSKRIT BOOK' => '/sanskrit/i', 'COMPUTER' => '/computer/i', 'ROBOTICS' => '/robot/i',
            'PHYSICAL EDUCATION' => '/physical|pe\b|health/i', 'ABACUS' => '/abacus/i',
        ];
    }

    if (!empty($canonicalMap) && !empty($skillSubjects)) {
        $orderedSubjects = collect();
        $usedSubjectIds = [];
        foreach ($canonicalMap as $displayName => $regex) {
            $matched = $skillSubjects->first(function($sub) use ($regex, $usedSubjectIds) {
                return !in_array($sub->id, $usedSubjectIds) && preg_match($regex, $sub->subject_name);
            });
            if ($matched) {
                $matched->display_name = $displayName;
                $orderedSubjects->push($matched);
                $usedSubjectIds[] = $matched->id;
            } else {
                $dummy = new \stdClass();
                $dummy->id = 0;
                $dummy->subject_name = $displayName;
                $dummy->display_name = $displayName;
                $orderedSubjects->push($dummy);
            }
        }
        $skillSubjects = $orderedSubjects;
    }
    $hasData = count($students) && $skillSubjects->isNotEmpty();
@endphp

<style>
    .result-sheet-page { width: 100%; padding: 20px; background: #fafbfc; }
    .filter-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 16px 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); margin-bottom: 14px; }
    .result-filter-row { display: flex; align-items: flex-end; flex-wrap: wrap; gap: 10px; }
    .result-filter-group { display: flex; flex-direction: column; gap: 4px; flex: 1 1 130px; min-width: 0; }
    .result-filter-label { font-size: 10.5px; font-weight: 700; color: #6B7280; text-transform: uppercase; letter-spacing: 0.4px; white-space: nowrap; }
    .result-filter select { height: 34px; padding: 3px 28px 3px 10px; font-size: 12.5px; color: #111827; background: #fff; border: 1px solid #d1d5db; border-radius: 6px; cursor: pointer; font-weight: 600; appearance: none; width: 100%; }
    .result-filter select:focus { border-color: #2563EB; outline: none; }
    .btn-load { background: #16A34A; color: #fff; border: 1px solid #15803D; height: 34px; padding: 0 16px; border-radius: 6px; font-weight: 700; cursor: pointer; }
    .btn-print { background: #2563EB; color: #fff; border: 1px solid #1D4ED8; height: 34px; padding: 0 16px; border-radius: 6px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; }
    .result-information-wrapper { width: 100%; margin-bottom: 14px; }
    .result-information-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
    .result-information-item { border: 1px solid #bfdbfe; border-radius: 8px; background: #eff6ff; padding: 10px 14px; }
    .result-information-label { display: block; font-size: 9.5px; color: #1e40af; font-weight: 700; text-transform: uppercase; margin-bottom: 5px; }
    .result-information-value { display: block; font-size: 13.5px; color: #1e3a8a; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .section-bar-row { display: flex; justify-content: space-between; align-items: center; background: #f3f4f6; border: 1px solid #d1d5db; border-bottom: 0; padding: 9px 14px; border-radius: 8px 8px 0 0; }
    .section-title { font-weight: 700; font-size: 12px; text-transform: uppercase; color: #1e3a8a; }
    .grade-scale { display: flex; gap: 6px; }
    .grade-pill { background: #fff; border: 1px solid #93c5fd; border-radius: 4px; padding: 3px 8px; font-size: 10.5px; font-weight: 700; color: #1e40af; }
    .result-sheet-wrapper { border: 1px solid #d1d5db; border-top: 0; border-radius: 0 0 8px 8px; background: #fff; overflow-x: auto; }
    .skill-summary-table { width: 100%; border-collapse: collapse; font-size: 11px; }
    .skill-summary-table th, .skill-summary-table td { border: 1px solid #d1d5db; text-align: center; vertical-align: middle; padding: 7px 6px; }
    .skill-summary-table th { background: #1e3a8a; color: #fff; font-size: 10px; font-weight: 700; text-transform: uppercase; }
    .skill-summary-table td { font-size: 10.5px; color: #111827; white-space: nowrap; }
    .skill-summary-table td.td-name { text-align: left; white-space: normal; font-weight: 700; }
    .skill-summary-table td.td-empty { color: #9CA3AF; background: #fff; }
    .skill-summary-table td.td-ab { color: #C2410C; background: #FFF7ED; font-weight: 700; }
    .skill-summary-table td.td-filled { color: #111827; background: #DBEAFE; font-weight: 700; }
    .skill-summary-table td.td-avg { color: #92400E; background: #FEF3C7; font-weight: 800; }
</style>

<div class="erp-page result-sheet-page">
    <div class="filter-card no-print">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid #f1f5f9;">
            <h2 style="margin: 0; font-size: 20px; font-weight: 700; color: #1D4ED8;">SKILL OBSERVATION SUMMARY</h2>
            <a href="{{ route('exam-progress.index', array_merge(request()->only(['academic_year_id', 'standard_id', 'division_id', 'exam_master_id']), ['subject_type' => 'skill'])) }}" class="btn-print" style="background: #6b7280; border-color: #4b5563; text-decoration: none;">⬅ Back</a>
        </div>
        <form method="GET" action="{{ route('student-skill-observations.summary') }}" class="result-filter" id="summaryFilterForm">
            <div class="result-filter-row">
                <div class="result-filter-group">
                    <label class="result-filter-label">Academic Year</label>
                    <select name="academic_year_id" id="sum_year" required>
                        <option value="">Select</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ (string) request('academic_year_id') === (string) $year->id ? 'selected' : '' }}>{{ $year->year_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="result-filter-group">
                    <label class="result-filter-label">Standard</label>
                    <select name="standard_id" id="sum_standard" required>
                        <option value="">Select</option>
                        @foreach($standards as $std)
                            <option value="{{ $std->id }}" {{ (string) request('standard_id') === (string) $std->id ? 'selected' : '' }}>{{ $std->standard_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="result-filter-group">
                    <label class="result-filter-label">Division</label>
                    <select name="division_id" id="sum_division" required>
                        <option value="">Select</option>
                        @foreach($divisions as $div)
                            <option value="{{ $div->id }}" {{ (string) request('division_id') === (string) $div->id ? 'selected' : '' }}>{{ $div->division_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="result-filter-group">
                    <label class="result-filter-label">Exam</label>
                    <select name="exam_master_id" id="sum_exam">
                        <option value="">All Exams</option>
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}" data-standard-id="{{ $exam->standard_id }}" {{ (string) request('exam_master_id') === (string) $exam->id ? 'selected' : '' }}>{{ $exam->exam_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="result-filter-group" style="flex: 0 0 auto; flex-direction: row; align-items: flex-end; gap: 8px;">
                    <button type="submit" class="btn-load">🔍 Load</button>
                    @if($hasData)
                        <a href="{{ route('student-skill-observations.summary', array_merge(request()->query(), ['print' => 1])) }}" target="_blank" class="btn-print">🖨 Print</a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    @if($hasData)
        <div class="erp-card" style="background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 16px 18px;">
            <div class="result-information-wrapper">
                <div class="result-information-grid">
                    <div class="result-information-item">
                        <span class="result-information-label">Academic Year</span>
                        <span class="result-information-value">{{ $printYearName ?: '-' }}</span>
                    </div>
                    <div class="result-information-item">
                        <span class="result-information-label">Exam</span>
                        <span class="result-information-value" title="{{ $printExamName }}">{{ $printExamName }}</span>
                    </div>
                    <div class="result-information-item">
                        <span class="result-information-label">Standard / Division</span>
                        <span class="result-information-value">{{ ($printStandardName ?: '-') . ' / ' . ($printDivisionName ?: '-') }}</span>
                    </div>
                    <div class="result-information-item">
                        <span class="result-information-label">Total Students</span>
                        <span class="result-information-value">{{ count($students) }}</span>
                    </div>
                </div>
            </div>
            <div class="section-bar-row">
                <span class="section-title">Skill Observation Summary</span>
                <div class="grade-scale no-print">
                    <span style="font-weight: 700; font-size: 10px; color: #1e3a8a; margin-right: 4px;">Grade Scale:</span>
                    @foreach($gradeLabels as $g => $lbl)
                        <span class="grade-pill"><span>{{ $g }}</span> <span style="font-weight:500;color:#4B5563;">{{ $lbl }}</span></span>
                    @endforeach
                </div>
            </div>
            <div class="result-sheet-wrapper">
                <table class="skill-summary-table">
                    <thead>
                        <tr>
                            <th style="width: 55px;">Roll No</th>
                            <th style="min-width: 180px; text-align: left;">Student Name</th>
                            @foreach($skillSubjects as $sub)
                                <th title="{{ $sub->display_name ?? $sub->subject_name }}">{{ $sub->display_name ?? $sub->subject_name }}</th>
                            @endforeach
                            <th style="width: 80px;">Average Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                            @php $studentName = trim(($student->studname ?? '') . ' ' . ($student->fathername ?? '')); @endphp
                            <tr>
                                <td class="td-roll" style="font-weight: 700; color: #1e3a8a;">{{ $student->rollno ?? '-' }}</td>
                                <td class="td-name" title="{{ $studentName }}">{{ $studentName }}</td>
                                @foreach($skillSubjects as $sub)
                                    @php
                                        $cell = '-';
                                        $cellClass = 'td-empty';
                                        $subId = $sub->id;
                                        if ($subId) {
                                            $key = $student->Studentid . '|' . $subId;
                                            $obs = $observations[$key] ?? null;
                                            if ($obs) {
                                                if ($obs->is_absent) {
                                                    $cell = 'AB';
                                                    $cellClass = 'td-ab';
                                                } else {
                                                    $avg = $obs->overall_grade;
                                                    if (!$avg && !empty($obs->observations)) {
                                                        $avg = \App\Helpers\SkillCriteriaHelper::computeAverageGrade($obs->observations);
                                                    }
                                                    $cell = $avg ?: '-';
                                                    $cellClass = 'td-filled';
                                                }
                                            }
                                        }
                                    @endphp
                                    <td class="{{ $cellClass }}">{{ $cell }}</td>
                                @endforeach
                                <td class="td-avg">{{ $studentAverages[$student->Studentid] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @elseif(request()->anyFilled(['academic_year_id', 'standard_id', 'division_id']))
        <div style="background: #fffbeb; border: 1px solid #fcd34d; color: #92400e; padding: 12px 16px; border-radius: 8px; font-weight: 700; font-size: 13px; margin-top: 14px;">
            No students or skill subjects found for the selected filters.
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const yearSelect     = document.getElementById('sum_year');
    const standardSelect = document.getElementById('sum_standard');
    const examSelect     = document.getElementById('sum_exam');

    if (standardSelect && examSelect) {
        const allExams = Array.from(examSelect.options).map(opt => ({
            value: opt.value, text: opt.text, standardId: opt.dataset.standardId || ''
        }));

        function rebuildExams() {
            const stdId = standardSelect.value;
            const currentValue = examSelect.value;
            examSelect.innerHTML = '';

            allExams.forEach(opt => {
                if (opt.value === '') {
                    const o = document.createElement('option');
                    o.value = ''; o.textContent = opt.text;
                    examSelect.appendChild(o);
                    return;
                }
                if (!stdId || String(opt.standardId) === String(stdId)) {
                    const o = document.createElement('option');
                    o.value = opt.value; o.textContent = opt.text; o.dataset.standardId = opt.standardId;
                    if (opt.value === currentValue) o.selected = true;
                    examSelect.appendChild(o);
                }
            });

            const stillThere = Array.from(examSelect.options).some(o => o.value === currentValue);
            if (!stillThere) examSelect.value = '';
        }
        standardSelect.addEventListener('change', rebuildExams);
        if (yearSelect) yearSelect.addEventListener('change', rebuildExams);
        rebuildExams();
    }
});
</script>
@endsection