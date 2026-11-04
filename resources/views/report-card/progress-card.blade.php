<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ isset($isEdit) && $isEdit ? 'Edit' : '' }} Progress Card — {{ $report->full_student_name ?? '' }} ({{ $termLabel ?? 'Exam' }})</title>

    <style>
        @page { size: A4 portrait; margin: 0; }
        * { box-sizing: border-box; }
        
        /* BASE FONT & LINE HEIGHT */
        html, body { margin: 0; padding: 0; font-family: 'Helvetica Neue', Arial, Helvetica, sans-serif; font-size: 11px; color: #000; background: #e5e7eb; -webkit-print-color-adjust: exact; print-color-adjust: exact; line-height: 1.15; overflow-x: hidden; }

        /* ===== PAGE SHELL ===== */
        .page { 
            width: 210mm; 
            min-height: 297mm; 
            padding: 5mm; 
            margin: 20px auto; 
            background: #fff; 
            position: relative; 
            display: block; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
        }
        
        .page-frame { 
            width: 100%; 
            min-height: calc(297mm - 10mm);
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

        .school-name     { font-size: 14px; line-height: 15px; font-weight: 800; text-align: center; white-space: nowrap; }
        .school-location { font-size: 12px; line-height: 13px; font-weight: 800; text-align: center; margin-top: 0; white-space: nowrap; }
        .school-address  { font-size: 9px; line-height: 10px; font-weight: 700; text-align: center; margin-top: 0; white-space: nowrap; }
        .school-year     { margin-top: 1px; font-size: 10.5px; line-height: 11px; font-weight: 700; text-align: center; white-space: nowrap; }

        .progress-title { text-align: center; font-size: 13px; font-weight: 800; margin: 3px 0 4px; letter-spacing: 0.8px; text-transform: uppercase; flex: none; }

        /* ===== SECTIONS ===== */
        .section { margin-bottom: 5px; flex: none; }
        .sub-title-bar { font-weight: 800; font-size: 10px; background: #fff; border: 1px solid #000; border-bottom: 0; padding: 2px 6px; text-transform: uppercase; letter-spacing: 0.3px; line-height: 1.15; }
        .table-wrap { border: 1px solid #000; background: #fff; }

        /* ===== DATA TABLES ===== */
        .data-table { width: 100%; border-collapse: collapse; font-size: 11px; table-layout: fixed; }
        .data-table th { background: #f4f4f4; padding: 2px 4px; font-size: 10px; font-weight: 800; text-align: center; border: 1px solid #000; text-transform: uppercase; white-space: nowrap; line-height: 1.15; }
        .data-table th.text-left { text-align: left; }
        .data-table td { padding: 2.5px 4px; border: 1px solid #000; vertical-align: middle; font-size: 10.5px; line-height: 1.15; font-weight: 400; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .data-table td.center { text-align: center; }

        /* Fix for Total Row Bold */
        .total-row td {
            font-weight: 700 !important;
        }

        /* ===== SUBJECT TABLE ===== */
        .subject-table td { padding: 2.5px 4px; font-size: 10.5px; line-height: 1.15; }
        .subject-table td.subject-name { text-align: left; white-space: normal !important; word-wrap: break-word !important; word-break: break-word !important; }
        .subject-table tr.skills-divider td { background: #fff !important; font-weight: 800 !important; font-size: 10px; text-transform: uppercase; text-align: left !important; padding: 2px 4px !important; border: 1px solid #000 !important; }

        /* ===== GRADE CHART ===== */
        .grade-chart-table th { font-size: 8.5px !important; padding: 2px 1px !important; white-space: normal !important; word-break: break-word; text-align: center; vertical-align: middle; }
        .grade-chart-table td { font-size: 10px !important; padding: 2px 1px !important; text-align: center; vertical-align: middle; }

        /* ===== ATTENDANCE TABLE ===== */
        .attendance-table td.month-cell { white-space: nowrap; line-height: 1.15; text-align: left; padding: 2.5px 4px; font-size: 10.5px; font-weight: 400; }
        .attendance-table td.month-cell .overlap { font-weight: 400; font-size: 10px; line-height: 1.15; text-transform: uppercase; }

        /* ===== DESCRIPTIVE ROW (FIXED) ===== */
        .descriptive-table .descriptive-comment-row td { 
            height: auto !important; /* Allows the row to expand */
            min-height: 28px; 
            vertical-align: top; 
            padding: 6px 8px; 
            background: #fff; 
            font-size: 11px; 
            white-space: normal !important; /* Forces text to wrap */
            word-wrap: break-word !important; /* Breaks long words */
            overflow: visible !important; /* Removes the hidden overflow */
            text-overflow: clip !important; /* Removes the "..." */
            line-height: 1.3;
        }

        /* ===== PERSONAL INFO TABLE ===== */
        .info-table { width: 100%; border-collapse: collapse; font-size: 10.5px; table-layout: fixed; }
        .info-table td { border: 1px solid #000; padding: 2.5px 4px; vertical-align: middle; line-height: 1.15; font-weight: 400; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .info-table td.label { font-weight: 700; background: #f4f4f4; font-size: 10px; text-transform: uppercase; white-space: normal; word-break: break-word; }
        .info-table td.value { font-weight: 400; white-space: normal; word-break: break-word; }
        .info-table td.label-full { font-weight: 700; background: #f4f4f4; font-size: 10px; text-transform: uppercase; vertical-align: top; padding-top: 2.5px; }
        .info-table td.value-full { font-weight: 400; vertical-align: top; padding-top: 2.5px; white-space: normal; word-break: break-word; }
        .info-table .name-row-value { font-weight: 700 !important; font-size: 12px; letter-spacing: 0.2px; }

        /* ===== SIDE-BY-SIDE SKILLS ===== */
        .skills-flex-row { display: flex; gap: 4px; margin-bottom: 3px; align-items: stretch; flex: none; width: 100%; }
        .skills-flex-col { flex: 1; display: flex; flex-direction: column; width: 50%; min-width: 0; }
        .skills-flex-col .table-wrap { flex: 1; display: flex; flex-direction: column; }
        .skills-flex-col .data-table { height: 100%; }

        /* ===== IMPORTANT NOTE ===== */
        .important-note { border: 1px solid #000; background: #fff; padding: 4px 9px; font-size: 11.5px; line-height: 1.25; margin-bottom: 3px; flex: none; }
        .important-note .title { font-weight: 800; text-transform: uppercase; font-size: 11.5px; border-bottom: 1px solid #000; padding-bottom: 1.5px; margin-bottom: 2.5px; letter-spacing: 0.3px; }
        .important-note .greeting { text-align: center; font-weight: 400; margin-bottom: 2px; font-size: 11.5px; }
        .important-note ul { margin: 0 0 0 16px; padding: 0; }
        .important-note li { margin-bottom: 0; font-weight: 400; font-size: 11.5px; }

        /* ===== SIGNATURES ===== */
        .signatures { display: flex; justify-content: space-between; margin-top: auto; padding-top: 6px; page-break-inside: avoid; break-inside: avoid; flex: none; }
        .signatures .sig-box { text-align: center; flex: 1; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; }
        .signatures .sig-line { border-top: 1.5px solid #000; margin: 6px 15px 3px; }

        /* ===== TOOLBAR (screen only) ===== */
        .toolbar { position: fixed; top: 14px; right: 14px; z-index: 9999; display: flex; gap: 8px; }
        .toolbar .btn { display: inline-flex; align-items: center; justify-content: center; height: 36px; padding: 0 18px; border-radius: 6px; font-family: Arial, sans-serif; font-size: 12.5px; font-weight: 700; text-decoration: none; cursor: pointer; border: 1px solid transparent; transition: all .15s; }
        .btn-print { background: #16A34A; color: #fff; border-color: #15803D; }
        .btn-print:hover { background: #15803D; }
        .btn-edit { background: #d97706; color: #fff; border-color: #b45309; }
        .btn-edit:hover { background: #b45309; }
        .btn-close { background: #6B7280; color: #fff; border-color: #4B5563; }
        .btn-close:hover { background: #4B5563; }
        .btn-save { background: #16A34A; color: #fff; border-color: #15803D; }
        .btn-save:hover { background: #15803D; }
        .edit-badge { background: #d97706; color: #fff; font-size: 10px; font-weight: 800; letter-spacing: 0.7px; padding: 6px 12px; border-radius: 5px; text-transform: uppercase; }

        /* ===== EDIT INPUTS (Screen Only) ===== */
        .edit-input { width: 100%; height: 20px; padding: 1px 6px; font-family: inherit; font-size: 10.5px; font-weight: 400; color: #000; background: #fff; border: 1px solid #cbd5e1; border-radius: 3px; outline: none; line-height: 1.15; }
        .edit-input:focus { border-color: #16A34A; box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.15); }
        .edit-input.center { text-align: center; }
        .info-table .edit-input { height: 20px; font-size: 10.5px; padding: 0 4px; }
        .subject-table .edit-input { height: 20px; font-size: 10.5px; padding: 0 2px; text-align: center; }
        textarea.edit-input { height: auto; min-height: 40px; padding: 3px 5px; line-height: 1.2; resize: vertical; vertical-align: top; font-family: inherit; border: 1px solid #cbd5e1; }
        .sd-select { width: 100%; height: 20px; padding: 0 4px; font-family: inherit; font-size: 10.5px; font-weight: 400; color: #000; background: #fff; border: 1px solid #cbd5e1; border-radius: 3px; outline: none; }
        .sd-select:focus { border-color: #16A34A; box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.15); }
        .remark-input { display: inline-block; width: 60%; height: 18px; padding: 0 6px; font-family: inherit; font-size: 10.5px; font-weight: 400; color: #000; background: #fff; border: 1px solid #cbd5e1; border-radius: 3px; outline: none; }
        .remark-input:focus { border-color: #16A34A; }
        .preprimary-col { min-width: 0; }

        /* ========================================================
           PRE-PRIMARY COMPACT MODE 
           ======================================================== */
        .pre-primary-mode .page-frame { padding: 3mm !important; }
        .pre-primary-mode .section { margin-bottom: 2px !important; }
        .pre-primary-mode .sub-title-bar { padding: 1.2px 5px !important; font-size: 9.5px !important; }
        .pre-primary-mode .data-table th { padding: 1.2px 3.5px !important; font-size: 9.5px !important; line-height: 1.05 !important; }
        .pre-primary-mode .data-table td { padding: 1.5px 3.5px !important; font-size: 10.5px !important; line-height: 1.08 !important; }
        .pre-primary-mode .info-table td { padding: 1.5px 3.5px !important; font-size: 10.5px !important; line-height: 1.08 !important; }
        .pre-primary-mode .skills-flex-row { margin-bottom: 1.5px !important; flex-grow: 0 !important; }
        
        /* Force tables to stretch and fill identical height */
        .pre-primary-mode .skills-flex-col { display: flex !important; flex-direction: column !important; }
        .pre-primary-mode .skills-flex-col .table-wrap { flex: 1 !important; display: flex !important; flex-direction: column !important; }
        .pre-primary-mode .skills-flex-col .data-table { height: 100% !important; margin-bottom: 0 !important; }
        .pre-primary-mode .skills-flex-col .data-table tbody { height: 100% !important; }
        .pre-primary-mode .skills-flex-col .data-table tr { height: 1px !important; }

        .pre-primary-mode .important-note { padding: 1.5px 6px !important; font-size: 9.5px !important; margin-bottom: 1.5px !important; }
        .pre-primary-mode .signatures { padding-top: 4px !important; margin-top: auto !important; }
        .pre-primary-mode .progress-title { margin: 1.5px 0 2px !important; font-size: 12px !important; }

        /* ===== PRINT ===== */
        @media print {
            @page { size: A4 portrait; margin: 5mm; }
            
            html, body { width: 100% !important; height: 100% !important; margin: 0 !important; padding: 0 !important; overflow: hidden !important; background: #fff !important; }
            .no-print, .toolbar, .edit-badge { display: none !important; }
            
            .page { width: 100% !important; height: 100% !important; min-height: 0 !important; margin: 0 !important; padding: 0 !important; box-shadow: none !important; page-break-after: avoid !important; page-break-inside: avoid !important; break-inside: avoid !important; overflow: hidden !important; }
            .page-frame { width: 100% !important; height: 100% !important; min-height: 0 !important; border: 1.5px solid #000 !important; padding: 4.5mm !important; overflow: hidden !important; page-break-after: avoid !important; page-break-inside: avoid !important; break-inside: avoid !important; display: flex !important; flex-direction: column !important; }
            
            .pre-primary-mode .page-frame { padding: 3mm !important; }

            .data-table th { background: #f4f4f4 !important; color: #000 !important; border: 1px solid #000 !important; }
            .data-table td { border: 1px solid #000 !important; color: #000 !important; }
            .info-table td.label, .info-table td.label-full { background: #f4f4f4 !important; }
            
            .data-table tr { page-break-inside: avoid !important; break-inside: avoid !important; }
            .info-table tr { page-break-inside: avoid !important; break-inside: avoid !important; }

            /* Hide input borders and backgrounds */
            .edit-input, .sd-select, .remark-input, textarea.edit-input {
                border: none !important;
                outline: none !important;
                background: transparent !important;
                box-shadow: none !important;
                color: #000 !important;
                font-family: inherit !important;
                font-size: inherit !important;
                font-weight: inherit !important;
                padding: 0 !important;
                margin: 0 !important;
                height: auto !important;
                min-height: 0 !important;
                -webkit-appearance: none !important;
                appearance: none !important;
                resize: none !important;
                overflow: visible !important;
            }
            .sd-select { text-indent: -9999px; }
            .remark-input { border-bottom: 1px solid #000 !important; }
        }
    </style>
</head>

@php
    $isEdit = $isEdit ?? false;
    
    $term1Months = ['Jyeshtha','Ashadh','Shravan','Bhadrapad','Ashwin','Kartik'];
    $term2Months = ['Margshirsh','Paush','Magh','Phalgun','Chaitra','Vaishakh'];
    $monthMeta = [
        'Chaitra'=>['en'=>'Chaitra','overlap'=>'March – April'],'Vaishakh'=>['en'=>'Vaishakh','overlap'=>'April – May'],
        'Jyeshtha'=>['en'=>'Jyeshtha','overlap'=>'May – June'],'Ashadh'=>['en'=>'Ashadh','overlap'=>'June – July'],
        'Shravan'=>['en'=>'Shravan','overlap'=>'July – August'],'Bhadrapad'=>['en'=>'Bhadrapad','overlap'=>'August – September'],
        'Ashwin'=>['en'=>'Ashwin','overlap'=>'September – October'],'Kartik'=>['en'=>'Kartik','overlap'=>'October – November'],
        'Margshirsh'=>['en'=>'Margshirsh','overlap'=>'November – December'],'Paush'=>['en'=>'Paush','overlap'=>'December – January'],
        'Magh'=>['en'=>'Magh','overlap'=>'January – February'],'Phalgun'=>['en'=>'Phalgun','overlap'=>'February – March'],
    ];
    $defaultTotalDays = ['Jyeshtha'=>30,'Ashadh'=>31,'Shravan'=>31,'Bhadrapad'=>30,'Ashwin'=>31,'Kartik'=>30,'Margshirsh'=>30,'Paush'=>30,'Magh'=>30,'Phalgun'=>30,'Chaitra'=>30];

    $savedAttendance = $progress->attendance_data ?? [];
    $schoolCode      = session('school_code', 'shirgaon');
    $yearLabel       = $report->year_name ?? '';

    $std = strtoupper(trim((string) ($report->standard_name ?? '')));

    $skillSubMap = [];
    $isTenth = preg_match('/\b(10|X|TENTH)\b/i', $std);
    $isEighthNinth = preg_match('/\b(8|9|VIII|IX|EIGHTH|NINTH)\b/i', $std);
    $isFirstToNinth = preg_match('/\b(1|2|3|4|5|6|7|8|9|I|II|III|IV|V|VI|VII|VIII|IX|FIRST|SECOND|THIRD|FOURTH|FIFTH|SIXTH|SEVENTH|EIGHTH|NINTH)\b/i', $std);

    if ($isTenth) {
        $skillSubMap = ['SKILL 1' => '(DRAWING + ACTING/DANCE/MUSIC/TABLA)', 'SKILL 2' => '(ROBOTICS + COMPUTER)', 'SKILL 3' => '', 'SKILL 4' => ''];
    } elseif ($isEighthNinth) {
        $skillSubMap = ['SKILL 1' => '(DRAWING + ACTING/DANCE/MUSIC/TABLA)', 'SKILL 2' => '(ROBOTICS + COMPUTER + VEDIC MATH)', 'SKILL 3' => '', 'SKILL 4' => ''];
    } elseif ($isFirstToNinth) {
        $skillSubMap = ['SKILL 1' => '(DRAWING + ACTING/DANCE/MUSIC/TABLA)', 'SKILL 2' => '(ROBOTICS + COMPUTER + ABACUS)', 'SKILL 3' => '', 'SKILL 4' => ''];
    } else {
        $skillSubMap = ['SKILL 1' => '(ARTS/DRAWING)', 'SKILL 2' => '(WORK EXPERIENCE)', 'SKILL 3' => '(PT & HEALTH)', 'SKILL 4' => '(LIBRARY & SANSKRIT BHARTI)'];
    }

    $isHighSchool = preg_match('/\b(9|10)\b/', $std) || str_contains($std, 'NINTH') || str_contains($std, 'TENTH');
    $hideSpecialSkillAvg = preg_match('/\b(11|12)\b/', $std) || str_contains($std, 'ELEVENTH') || str_contains($std, 'TWELFTH') || str_contains($std, 'XI') || str_contains($std, 'XII');
    $showMarks = preg_match('/\b(5|8|9|10|11|12)\b/', $std) || str_contains($std, 'FIFTH') || str_contains($std, 'EIGHTH') || str_contains($std, 'NINTH') || str_contains($std, 'TENTH') || str_contains($std, 'ELEVENTH') || str_contains($std, 'TWELFTH');

    $isMiddleSchool = preg_match('/\b(6|7|8)\b/', $std) || str_contains($std, 'SIXTH') || str_contains($std, 'SEVENTH') || str_contains($std, 'EIGHTH') || in_array($std, ['VI', 'VII', 'VIII']);
    $isPrePrimary = preg_match('/\b(NURSERY|JR|SR|KINDERGARTEN|KG|LKG|UKG)\b/i', $std) === 1;

    if ($isMiddleSchool && isset($subjects)) {
        $history = $subjects->first(fn($s) => str_contains(strtoupper($s->subject_name), 'HISTORY'));
        $geography = $subjects->first(fn($s) => str_contains(strtoupper($s->subject_name), 'GEOGRAPHY'));
        if ($history && $geography) {
            $socialScience = clone $history;
            $socialScience->subject_name = 'SOCIAL SCIENCE';
            $socialScience->obtained_marks = ($history->obtained_marks ?? 0) + ($geography->obtained_marks ?? 0);
            $socialScience->max_marks = ($history->max_marks ?? 0) + ($geography->max_marks ?? 0);
            $pct = $socialScience->max_marks > 0 ? ($socialScience->obtained_marks / $socialScience->max_marks) * 100 : 0;
            if ($pct >= 91) $socialScience->grade = 'A1'; elseif ($pct >= 81) $socialScience->grade = 'A2'; elseif ($pct >= 71) $socialScience->grade = 'B1';
            elseif ($pct >= 61) $socialScience->grade = 'B2'; elseif ($pct >= 51) $socialScience->grade = 'C1'; elseif ($pct >= 41) $socialScience->grade = 'C2';
            elseif ($pct >= 33) $socialScience->grade = 'D'; elseif ($pct >= 21) $socialScience->grade = 'E1'; else $socialScience->grade = 'E2';

            $newSubjects = collect();
            foreach ($subjects as $subj) {
                $name = strtoupper($subj->subject_name);
                if (str_contains($name, 'HISTORY')) $newSubjects->push($socialScience);
                elseif (!str_contains($name, 'GEOGRAPHY')) $newSubjects->push($subj);
            }
            $subjects = $newSubjects;
        }
    }

    $hasDob     = !empty(trim((string) ($report->date_of_birth ?? '')));
    $hasAddress = !empty(trim((string) ($report->address ?? '')));
    $hasGrNo    = !empty(trim((string) ($report->gr_no ?? '')));
    $hasPhone   = !empty(trim((string) ($report->phone ?? $report->mobile ?? '')));
    $hasEmail   = !empty(trim((string) ($report->email ?? '')));

    $termText = strtoupper(trim((string) ($termLabel ?? '')));
    $isTerm2 = str_contains($termText, 'TERM 2') || str_contains($termText, 'UNIT TEST 3') || str_contains($termText, 'UNIT TEST 4') || str_contains($termText, 'ANNUAL');

    if ($isTerm2) {
        $termRowLabel = 'Term II'; $termWeight = trim((string) ($progress->term2_weight ?? '')); $termHeight = trim((string) ($progress->term2_height ?? ''));
    } else {
        $termRowLabel = 'Term I'; $termWeight = trim((string) ($progress->term1_weight ?? '')); $termHeight = trim((string) ($progress->term1_height ?? ''));
    }

    $term1WeightVal = old('term1_weight', $progress->term1_weight ?? '');
    $term1HeightVal = old('term1_height', $progress->term1_height ?? '');
    $term2WeightVal = old('term2_weight', $progress->term2_weight ?? '');
    $term2HeightVal = old('term2_height', $progress->term2_height ?? '');

    $savedDescriptive = $progress->descriptive_records  ?? [];
    $savedGraded      = $progress->second_term_grades   ?? [];
    $savedClassTest   = $progress->class_test_data      ?? [];

    $descriptiveRecords = [];
    $rawDesc = $progress->descriptive_records ?? [];
    if (is_array($rawDesc)) $descriptiveRecords = $rawDesc;
    elseif (is_string($rawDesc) && $rawDesc !== '') { $decoded = json_decode($rawDesc, true); if (is_array($decoded)) $descriptiveRecords = $decoded; }
    
    $descriptiveSubjects = $subjects->take(3);
    $firstDescKey = (int) ($descriptiveSubjects->first()->subject_id ?? 0);
    $firstDesc    = $savedDescriptive[$firstDescKey] ?? $savedDescriptive[(string) $firstDescKey] ?? ['progress' => '', 'interest' => '', 'improvements' => ''];
    
    $teacherRemarkText = $progress->teacher_remark['text'] ?? ($firstDesc['progress'] ?? '');

    $gradeOptions = ['A1'=>'A1 (91% to 100%)','A2'=>'A2 (81% to 90%)','B1'=>'B1 (71% to 80%)','B2'=>'B2 (61% to 70%)','C1'=>'C1 (51% to 60%)','C2'=>'C2 (41% to 50%)','D'=>'D (33% to 40%)','E1'=>'E1 (21% to 32%)','E2'=>'E2 (Below 21%)'];
    $skillGradeOptions = ['A+', 'A', 'B+', 'B', 'C+', 'C'];
    $academicGradeOptions = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2', 'D', 'E1', 'E2'];

    $subjectNameToKey = fn ($name) => str_replace([' ', '/', '-', '.'], '_', strtoupper(trim((string) $name)));

    $calculateSkillAverage = function($grades) {
        $map = ['A+'=>5, 'A'=>4, 'B+'=>3, 'B'=>2, 'C+'=>1, 'C'=>0]; $total = 0; $count = 0;
        foreach($grades as $g) { $g = strtoupper(trim($g)); if(isset($map[$g])) { $total += $map[$g]; $count++; } }
        if($count === 0) return '-'; $avg = $total / $count;
        if($avg >= 4.5) return 'A+'; if($avg >= 3.5) return 'A'; if($avg >= 2.5) return 'B+'; if($avg >= 1.5) return 'B'; if($avg >= 0.5) return 'C+'; return 'C';
    };

    $prePrimarySkillStructure = [
        'A) GROUND ACTIVITIES' => ['criteria' => ['FLEXIBILITY', 'STAMINA', 'GROSS MOTOR SKILL']],
        'B) ART SKILLS' => ['criteria' => ['COLOURING / DRAWING', 'CRAFT', 'CLAY', 'LIFE SKILLS', 'SONG', 'INDOOR GAME']],
        'C) TRG. SENSES' => ['criteria' => []],
        'D) ASSEMBLY' => ['criteria' => ['ANTHEM, PLEDGE, ETC.', 'STOTRA (SANSKRIT, MARATHI)']],
        'E) ACTIVITY BOOK' => ['criteria' => []],
        'F) GENERAL SKILLS' => ['criteria' => [
            'ATTENTIVENESS', 'FOOD HABITS', 'NEATNESS', 'INTERACTION WITH OTHERS',
            'EXPRESSION OF FEELINGS', 'FOLLOWING INSTRUCTIONS', 'PARTICIPATION IN SCHOOL ACTIVITY',
            'HAPPINESS & ENERGY LEVEL', 'OBEDIENCE', 'HELPING'
        ]]
    ];

    $academicSubjects = collect();
    $skillSubjects = collect();
    $criteriaGrades = [];
    $subjectGrades = [];
    $groupGrades = [];

    if ($isPrePrimary) {
        $skillObservations = \Illuminate\Support\Facades\DB::table('student_skill_observations as sso')
            ->leftJoin('subjects as s', 's.id', '=', 'sso.subject_id')
            ->where('sso.student_id', $report->student_id)
            ->where('sso.exam_master_id', $report->exam_master_id)
            ->where('sso.academic_year_id', $report->academic_year_id)
            ->select('sso.*', 's.subject_name')
            ->get();

        foreach ($skillObservations as $obs) {
            $subName = strtoupper(trim($obs->subject_name ?? ''));
            if ($subName) $subjectGrades[$subName] = $obs->overall_grade;
            if (!empty($obs->observations)) {
                $json = is_array($obs->observations) ? $obs->observations : json_decode($obs->observations, true);
                if (is_array($json)) { foreach ($json as $k => $v) $criteriaGrades[strtoupper(trim($k))] = $v; }
            }
            if ($subName === 'GROUND ACTIVITIES' && $obs->ground_activities_grade) $groupGrades['A) GROUND ACTIVITIES'] = $obs->ground_activities_grade;
            if ($subName === 'TRG. SENSES' && $obs->trg_senses_grade) $groupGrades['C) TRG. SENSES'] = $obs->trg_senses_grade;
            if ($subName === 'ASSEMBLY' && $obs->assembly_grade) $groupGrades['D) ASSEMBLY'] = $obs->assembly_grade;
            if ($subName === 'ACTIVITY BOOK' && $obs->activity_book_grade) $groupGrades['E) ACTIVITY BOOK'] = $obs->activity_book_grade;
            if ($subName === 'GENERAL SKILLS' && $obs->general_skills_grade) $groupGrades['F) GENERAL SKILLS'] = $obs->general_skills_grade;
        }

        foreach ($subjects as $sub) {
            if (!empty($sub->is_graded)) $skillSubjects->push($sub);
            else $academicSubjects->push($sub);
        }

        $getGrade = function($name) use ($criteriaGrades, $subjectGrades) {
            $nameUpper = strtoupper(trim($name));
            if (isset($criteriaGrades[$nameUpper])) return $criteriaGrades[$nameUpper];
            if (isset($subjectGrades[$nameUpper])) return $subjectGrades[$nameUpper];
            foreach ($subjectGrades as $k => $v) { if (str_contains($k, $nameUpper) || str_contains($nameUpper, $k)) return $v; }
            foreach ($criteriaGrades as $k => $v) { if (str_contains($k, $nameUpper) || str_contains($nameUpper, $k)) return $v; }
            return '-';
        };

        $calculateAcademicAverage = function($grades) {
            $map = ['A1'=>9, 'A2'=>8, 'B1'=>7, 'B2'=>6, 'C1'=>5, 'C2'=>4, 'D'=>3, 'E1'=>2, 'E2'=>1, 'F'=>0];
            $total = 0; $count = 0;
            foreach($grades as $g) {
                $g = strtoupper(trim($g));
                if(isset($map[$g])) { $total += $map[$g]; $count++; }
            }
            if($count === 0) return '-';
            $avg = $total / $count;
            if($avg >= 8.5) return 'A1'; if($avg >= 7.5) return 'A2'; if($avg >= 6.5) return 'B1'; if($avg >= 5.5) return 'B2';
            if($avg >= 4.5) return 'C1'; if($avg >= 3.5) return 'C2'; if($avg >= 2.5) return 'D'; if($avg >= 1.5) return 'E1'; if($avg >= 0.5) return 'E2';
            return 'F';
        };

        $leftSkills = [
            'A) GROUND ACTIVITIES' => $prePrimarySkillStructure['A) GROUND ACTIVITIES'],
            'B) ART SKILLS' => $prePrimarySkillStructure['B) ART SKILLS'],
            'C) TRG. SENSES' => $prePrimarySkillStructure['C) TRG. SENSES'],
            'D) ASSEMBLY' => $prePrimarySkillStructure['D) ASSEMBLY'],
        ];
        $rightSkills = [
            'E) ACTIVITY BOOK' => $prePrimarySkillStructure['E) ACTIVITY BOOK'],
            'F) GENERAL SKILLS' => $prePrimarySkillStructure['F) GENERAL SKILLS'],
        ];
    }
@endphp

<!-- Add pre-primary-mode class if it is a pre-primary standard -->
<body class="{{ $isPrePrimary ? 'pre-primary-mode' : '' }}">

@if($isEdit)
<div class="toolbar no-print">
    <span class="edit-badge">Edit Mode</span>
    <button form="progressForm" type="submit" class="btn btn-save">Save Details</button>
    <button type="button" class="btn btn-close" onclick="window.close()">Close</button>
    <button type="button" class="btn btn-print" onclick="printProgressCard()">Print</button>
</div>
<form id="progressForm" method="POST" action="{{ route('report-card.progress-card.save', [$report->student_id, $report->exam_master_id, $report->academic_year_id]) }}">
    @csrf
    <!-- Hidden fields for snapshot totals -->
    <input type="hidden" name="hidden_percentage" value="{{ $report->percentage }}">
    <input type="hidden" name="hidden_grade" value="{{ $report->grade }}">
    <input type="hidden" name="hidden_result" value="{{ $report->result }}">
    <input type="hidden" name="hidden_total_max" value="{{ $report->total_max_marks }}">
    <input type="hidden" name="hidden_total_obtained" value="{{ $report->total_obtained_marks }}">
    
    <input type="hidden" name="father_name" value="{{ $report->father_name }}">
    <input type="hidden" name="mother_name" value="{{ $report->mother_name }}">
    <input type="hidden" name="gender" value="{{ $report->gender }}">
    <input type="hidden" name="standard_id" value="{{ $report->standard_id }}">
    <input type="hidden" name="division_id" value="{{ $report->division_id }}">
@else
<div class="toolbar no-print">
    <a class="btn btn-edit" href="{{ route('report-card.progress-card.edit', [$report->student_id, $report->exam_master_id, $report->academic_year_id]) }}">Edit Details</a>
    <button type="button" class="btn btn-close" onclick="window.close()">Close</button>
    <button type="button" class="btn btn-print" onclick="printProgressCard()">Print</button>
</div>
@endif

<div class="page">
<div class="page-frame">

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
                        <div class="school-year">Academic Year : {{ $yearLabel }} | {{ $report->shak_year ?? '' }}</div>
                    @else
                        <div class="school-name">PRAJNANABODHINI ENGLISH MEDIUM SCHOOL &amp; JR. COLLEGE</div>
                        <div class="school-location">SHIRGAON</div>
                        <div class="school-address">Gat No. 227, Shirgaon-Gahunje Rd, At. Post. Shirgaon, Tal. Maval, Dist. Pune, Maharashtra - 410506</div>
                        <div class="school-year">Academic Year : {{ $yearLabel }} | {{ $report->shak_year ?? '' }}</div>
                    @endif
                </td>
                <td class="school-logo-cell"></td>
            </tr>
        </table>
    </div>

    <div class="progress-title">
        Progress Card @if(!empty($termLabel) && $termLabel !== 'Exam') — {{ $termLabel }} @endif
    </div>

    <div class="section">
        <div class="sub-title-bar">Personal Information</div>
        <div class="table-wrap">
            <table class="info-table">
                <colgroup>
                    <col style="width: 18%;">
                    <col style="width: 22%;">
                    <col style="width: 18%;">
                    <col style="width: 42%;">
                </colgroup>
                <tr>
                    <td class="label-full">Student Name</td>
                    <td class="value-full name-row-value" colspan="3">
                        @if($isEdit)
                            <input type="text" name="student_name" class="edit-input" value="{{ $report->full_student_name ?? '' }}">
                        @else
                            {{ $report->full_student_name ?? '' }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="label">Standard</td>
                    <td class="value">{{ $report->standard_name ?? '' }}</td>
                    <td class="label">Division</td>
                    <td class="value">{{ $report->division_name ?? '' }}</td>
                </tr>
                <tr>
                    <td class="label">GR No.</td>
                    <td class="value">
                        @if($isEdit)
                            <input type="text" name="gr_no" class="edit-input" value="{{ $hasGrNo ? $report->gr_no : '' }}">
                        @else
                            {{ $hasGrNo ? $report->gr_no : '' }}
                        @endif
                    </td>
                    <td class="label">Roll No.</td>
                    <td class="value">
                        @if($isEdit)
                            <input type="text" name="rollno" class="edit-input" value="{{ $report->rollno ?? '' }}">
                        @else
                            {{ $report->rollno ?? '' }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="label">PEN</td>
                    <td class="value">
                        @if($isEdit)
                            <input type="text" name="gov_student_id" class="edit-input" value="{{ $report->gov_student_id ?? '' }}">
                        @else
                            {{ $report->gov_student_id ?? '' }}
                        @endif
                    </td>
                    <td class="label">Aadhar No.</td>
                    <td class="value">
                        @if($isEdit)
                            <input type="text" name="aadhar_no" class="edit-input" value="{{ $report->aadhar_no ?? '' }}">
                        @else
                            {{ $report->aadhar_no ?? '' }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="label" style="white-space: nowrap;">Parent Contact No.</td>
                    <td class="value">
                        @if($isEdit)
                            <input type="text" name="phone" class="edit-input" maxlength="40" value="{{ old('phone', $progress->phone ?? '') }}">
                        @else
                            {{ $hasPhone ? ($report->phone ?? $report->mobile) : '' }}
                        @endif
                    </td>
                    <td class="label">Email</td>
                    <td class="value">
                        @if($isEdit)
                            <input type="email" name="email" class="edit-input" maxlength="80" value="{{ old('email', $progress->email ?? '') }}">
                        @else
                            {{ $hasEmail ? $report->email : '' }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="label">Date of Birth</td>
                    <td class="value">
                        @if($isEdit)
                            <input type="text" name="date_of_birth" class="edit-input" value="{{ $hasDob ? $report->date_of_birth : '' }}">
                        @else
                            {{ $hasDob ? $report->date_of_birth : '' }}
                        @endif
                    </td>
                    <td class="label">{{ $termRowLabel }}</td>
                    <td class="value" style="padding: 2.5px 4px;">
                        <div style="display:flex; align-items:center; gap:2px; flex-wrap: nowrap; white-space: nowrap; overflow: hidden; width: 100%;">
                            <span style="font-weight:600; flex-shrink:0; white-space: nowrap;">Weight:</span>
                            @if($isEdit)
                                @if($isTerm2)
                                    <input type="text" name="term2_weight" class="edit-input center" maxlength="6" style="width:25px; flex-shrink:0; padding:0; font-size:9px;" value="{{ $term2WeightVal }}">
                                    <input type="hidden" name="term1_weight" value="{{ $term1WeightVal }}">
                                @else
                                    <input type="text" name="term1_weight" class="edit-input center" maxlength="6" style="width:25px; flex-shrink:0; padding:0; font-size:9px;" value="{{ $term1WeightVal }}">
                                    <input type="hidden" name="term2_weight" value="{{ $term2WeightVal }}">
                                @endif
                            @else
                                {{ $termWeight !== '' ? $termWeight : '____' }}
                            @endif
                            <span style="flex-shrink:0; white-space: nowrap;">Kg</span>
                            
                            <span style="margin-left:2px; font-weight:600; flex-shrink:0; white-space: nowrap;">Height:</span>
                            @if($isEdit)
                                @if($isTerm2)
                                    <input type="text" name="term2_height" class="edit-input center" maxlength="6" style="width:25px; flex-shrink:0; padding:0; font-size:9px;" value="{{ $term2HeightVal }}">
                                    <input type="hidden" name="term1_height" value="{{ $term1HeightVal }}">
                                @else
                                    <input type="text" name="term1_height" class="edit-input center" maxlength="6" style="width:25px; flex-shrink:0; padding:0; font-size:9px;" value="{{ $term1HeightVal }}">
                                    <input type="hidden" name="term2_height" value="{{ $term2HeightVal }}">
                                @endif
                            @else
                                {{ $termHeight !== '' ? $termHeight : '____' }}
                            @endif
                            <span style="flex-shrink:0; white-space: nowrap;">cm</span>

                            <span style="margin-left:2px; font-weight:600; flex-shrink:0; white-space: nowrap;">Blood Group:</span>
                            @if($isEdit)
                                <input type="text" name="blood_group" class="edit-input center" maxlength="5" style="width:40px; flex-shrink:0; padding:0; font-size:9px;" value="{{ old('blood_group', $progress->blood_group ?? '') }}">
                            @else
                                {{ $progress->blood_group ?? '' }}
                            @endif
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="label-full">Address</td>
                    <td class="value-full" colspan="3">
                        @if($isEdit)
                            <input type="text" name="address" class="edit-input" maxlength="250" value="{{ old('address', $report->address ?? '') }}">
                        @else
                            {{ $hasAddress ? $report->address : '' }}
                        @endif
                    </td>
                </tr>
            </table>
            @if($isEdit)
                <input type="hidden" name="mother_tongue" value="{{ old('mother_tongue', $progress->mother_tongue ?? ($report->mother_tongue ?? 'MARATHI')) }}">
            @endif
        </div>
    </div>

    @if($isPrePrimary)
        <!-- Academic Subjects (Full Width) -->
        <div class="section">
            <div class="sub-title-bar">ACADEMIC SUBJECTS</div>
            <div class="table-wrap">
                <table class="data-table subject-table">
                    <colgroup><col style="width: 10%;"><col style="width: 60%;"><col style="width: 30%;"></colgroup>
                    <thead>
                        <tr>
                            <th>Sr.</th>
                            <th class="text-left">Subject</th>
                            <th>GRADE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $academicGrades = []; $academicCount = 1; @endphp
                        @forelse($academicSubjects as $sub)
                            @php
                                $g = $sub->grade ?? '-';
                                if($g !== '-') $academicGrades[] = $g;
                            @endphp
                            <tr>
                                <td class="center">{{ str_pad($academicCount++, 2, '0', STR_PAD_LEFT) }}</td>
                                <td class="subject-name">{{ strtoupper($sub->subject_name) }}</td>
                                <td class="center" style="font-weight:700;">
                                    @if($isEdit)
                                        <select name="graded_subjects[{{ $sub->subject_id }}]" class="sd-select">
                                            <option value="">-</option>
                                            @foreach($academicGradeOptions as $gOpt)
                                                <option value="{{ $gOpt }}" {{ $g == $gOpt ? 'selected' : '' }}>{{ $gOpt }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        {{ $g }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="center">No subjects found.</td></tr>
                        @endforelse
                        <tr style="background: #f4f4f4;">
                            <td class="center"></td>
                            <td class="subject-name" style="font-weight: 700;">Average Grade</td>
                            <td class="center" style="font-weight: 700;">{{ $calculateAcademicAverage($academicGrades) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Special Skills Side by Side -->
        <div class="skills-flex-row">
            <!-- Special Skills 1 -->
            <div class="skills-flex-col">
                <div class="sub-title-bar">SPECIAL SKILLS 1</div>
                <div class="table-wrap">
                    <table class="data-table subject-table">
                        <colgroup><col style="width: 75%;"><col style="width: 25%;"></colgroup>
                        <thead>
                            <tr>
                                <th class="text-left">SKILLS</th>
                                <th>GRADE</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leftSkills as $groupName => $groupData)
                                @php $hasCriteria = count($groupData['criteria']) > 0; @endphp
                                <tr>
                                    @if($hasCriteria)
                                        <td colspan="2" class="subject-name group-header" style="text-transform: uppercase; font-weight:700; background: #f4f4f4;">{{ $groupName }}</td>
                                    @else
                                        <td class="subject-name group-header" style="text-transform: uppercase; font-weight:700; background: #f4f4f4;">{{ $groupName }}</td>
                                        <td class="center" style="font-weight:700;">{{ $groupGrades[$groupName] ?? '-' }}</td>
                                    @endif
                                </tr>
                                @foreach($groupData['criteria'] as $criterion)
                                    @php 
                                        $c = $getGrade($criterion);
                                        if($c !== '-') $leftGrades[] = $c; 
                                    @endphp
                                    <tr class="criteria-row">
                                        <td class="subject-name" style="text-transform: uppercase; padding-left: 15px !important;">{{ $criterion }}</td>
                                        <td class="center" style="font-weight:700;">
                                            @if($isEdit)
                                                <select name="graded_subjects[{{ $criterion }}]" class="sd-select">
                                                    <option value="">-</option>
                                                    @foreach($skillGradeOptions as $gOpt)
                                                        <option value="{{ $gOpt }}" {{ $getGrade($criterion) == $gOpt ? 'selected' : '' }}>{{ $gOpt }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                {{ $getGrade($criterion) }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Special Skills 2 -->
            <div class="skills-flex-col">
                <div class="sub-title-bar">SPECIAL SKILLS 2</div>
                <div class="table-wrap">
                    <table class="data-table subject-table">
                        <colgroup><col style="width: 75%;"><col style="width: 25%;"></colgroup>
                        <thead>
                            <tr>
                                <th class="text-left">SKILLS</th>
                                <th>GRADE</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rightSkills as $groupName => $groupData)
                                @php $hasCriteria = count($groupData['criteria']) > 0; @endphp
                                <tr>
                                    @if($hasCriteria)
                                        <td colspan="2" class="subject-name group-header" style="text-transform: uppercase; font-weight:700; background: #f4f4f4;">{{ $groupName }}</td>
                                    @else
                                        <td class="subject-name group-header" style="text-transform: uppercase; font-weight:700; background: #f4f4f4;">{{ $groupName }}</td>
                                        <td class="center" style="font-weight:700;">{{ $groupGrades[$groupName] ?? '-' }}</td>
                                    @endif
                                </tr>
                                @foreach($groupData['criteria'] as $criterion)
                                    @php 
                                        $c = $getGrade($criterion);
                                        if($c !== '-') $rightGrades[] = $c; 
                                    @endphp
                                    <tr class="criteria-row">
                                        <td class="subject-name" style="text-transform: uppercase; padding-left: 15px !important;">{{ $criterion }}</td>
                                        <td class="center" style="font-weight:700;">
                                            @if($isEdit)
                                                <select name="graded_subjects[{{ $criterion }}]" class="sd-select">
                                                    <option value="">-</option>
                                                    @foreach($skillGradeOptions as $gOpt)
                                                        <option value="{{ $gOpt }}" {{ $getGrade($criterion) == $gOpt ? 'selected' : '' }}>{{ $gOpt }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                {{ $getGrade($criterion) }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @else
        @php
            $_nonSkills = $subjects->filter(fn($s) => empty($s->is_graded))->values();
            $_skills = $subjects->filter(function($s) use ($std) {
                if (empty($s->is_graded)) return false;
                if (preg_match('/\b(10|X|TENTH)\b/i', $std)) {
                    if (preg_match('/^SKILL\s*1/i', $s->subject_name)) return false;
                    if (preg_match('/^SKILL\s*4/i', $s->subject_name)) return false;
                }
                return true;
            })->sortBy(function($s) {
                if (preg_match('/^SKILL\s*([1-9])(?![0-9])/i', $s->subject_name, $m)) return (int)$m[1];
                return 1000;
            })->values();
        @endphp

        <!-- Academic Subjects -->
        @if($_nonSkills->isNotEmpty())
        <div class="section">
            <div class="sub-title-bar">Academic Subject &amp; Grade</div>
            <div class="table-wrap">
                <table class="data-table subject-table">
                    <colgroup>
                        @if($showMarks)
                            <col style="width: 7%;"><col style="width: 63%;"><col style="width: 15%;"><col style="width: 15%;">
                        @else
                            <col style="width: 7%;"><col style="width: 77%;"><col style="width: 16%;">
                        @endif
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Sr.</th>
                            <th class="text-left">Subject</th>
                            @if($showMarks) <th>Marks</th> @endif
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $academicCounter = 1; @endphp
                        @foreach($_nonSkills as $subject)
                            @php
                                $subjectNameUpper = strtoupper(trim($subject->subject_name));
                                $isManualOverride = in_array($subjectNameUpper, ['DEFENSE SCIENCE', 'DEFENCE STUDIES', 'WATER SECURITY']);
                                $gradeKey = $isManualOverride ? $subjectNameToKey($subject->subject_name) : (int) $subject->subject_id;
                                $manualGrade = $savedGraded[$gradeKey] ?? '';
                                
                                $dbGrade = (!empty($subject->grade) && $subject->grade !== '-') ? $subject->grade : '';
                                $displayGrade = $dbGrade !== '' ? $dbGrade : ($manualGrade !== '' ? $manualGrade : '-');
                                $selectedGrade = $dbGrade !== '' ? $dbGrade : $manualGrade;
                            @endphp
                            <tr>
                                <td class="center">{{ str_pad($academicCounter++, 2, '0', STR_PAD_LEFT) }}</td>
                                <td class="subject-name">{{ strtoupper($subject->subject_name) }}</td>
                                @if($showMarks)
                                    <td class="center">
                                        @if(!($subject->has_marks ?? true)) &mdash; / {{ number_format($subject->max_marks, 0) }}
                                        @else {{ number_format($subject->obtained_marks, 0) }} / {{ number_format($subject->max_marks, 0) }} @endif
                                    </td>
                                @endif
                                <td class="center">
                                    @if($isManualOverride && $isEdit)
                                        <select name="graded_subjects[{{ $gradeKey }}]" class="sd-select" style="height:20px;padding:0 4px;">
                                            <option value="">Select</option>
                                            @foreach($gradeOptions as $gValue => $gLabel)
                                                <option value="{{ $gValue }}" {{ old("graded_subjects.$gradeKey", $selectedGrade) == $gValue ? 'selected' : '' }}>{{ $gLabel }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        {{ $displayGrade }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        @if($showMarks)
                            <tr style="background: #f4f4f4;">
                                <td class="center"></td>
                                <td class="subject-name" style="font-weight: 700;">Academic Average percentage and Grade</td>
                                <td class="center" style="font-weight: 700;">{{ $report->percentage !== null ? $report->percentage . '%' : '-' }}</td>
                                <td class="center" style="font-weight: 700;">{{ $academicAverageGrade ?? '-' }}</td>
                            </tr>
                        @else
                            <tr style="background: #f4f4f4;">
                                <td class="center"></td>
                                <td class="subject-name" style="font-weight: 700;">Academic Average Grade</td>
                                <td class="center" style="font-weight: 700;">{{ $academicAverageGrade ?? '-' }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Special Skills -->
        @if($_skills->isNotEmpty())
        <div class="section">
            <div class="sub-title-bar">Special Skills</div>
            <div class="table-wrap">
                <table class="data-table subject-table">
                    <colgroup>
                        @if($showMarks)
                            <col style="width: 7%;"><col style="width: 63%;"><col style="width: 15%;"><col style="width: 15%;">
                        @else
                            <col style="width: 7%;"><col style="width: 77%;"><col style="width: 16%;">
                        @endif
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Sr.</th>
                            <th class="text-left">Skill</th>
                            @if($showMarks) <th>Marks</th> @endif
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $skillCounter = 1; @endphp
                        @foreach($_skills as $subject)
                            @php
                                $skillKey = null;
                                if (preg_match('/^SKILL\s*([1-4])/i', $subject->subject_name, $m)) {
                                    $skillKey = 'SKILL ' . $m[1];
                                }
                                $subjectNameUpper = strtoupper(trim($subject->subject_name));
                                $isManualOverride = in_array($subjectNameUpper, ['DEFENSE SCIENCE', 'DEFENCE STUDIES', 'WATER SECURITY']);
                                $gradeKey = $isManualOverride ? $subjectNameToKey($subject->subject_name) : (int) $subject->subject_id;
                                $manualGrade = $savedGraded[$gradeKey] ?? '';
                                
                                $dbGrade = (!empty($subject->grade) && $subject->grade !== '-') ? $subject->grade : '';
                                $displayGrade = $dbGrade !== '' ? $dbGrade : ($manualGrade !== '' ? $manualGrade : '-');
                                $selectedGrade = $dbGrade !== '' ? $dbGrade : $manualGrade;
                            @endphp
                            <tr>
                                <td class="center">{{ str_pad($skillCounter++, 2, '0', STR_PAD_LEFT) }}</td>
                                <td class="subject-name">
                                    {{ strtoupper($subject->subject_name) }} {{ $skillKey && isset($skillSubMap[$skillKey]) ? $skillSubMap[$skillKey] : '' }}
                                </td>
                                @if($showMarks)
                                    <td class="center">&mdash;</td>
                                @endif
                                <td class="center">
                                    @if($isManualOverride && $isEdit)
                                        <select name="graded_subjects[{{ $gradeKey }}]" class="sd-select" style="height:20px;padding:0 4px;">
                                            <option value="">Select</option>
                                            @foreach($gradeOptions as $gValue => $gLabel)
                                                <option value="{{ $gValue }}" {{ old("graded_subjects.$gradeKey", $selectedGrade) == $gValue ? 'selected' : '' }}>{{ $gLabel }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        {{ $displayGrade }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        @if(!$hideSpecialSkillAvg)
                            <tr style="background: #f4f4f4;">
                                <td class="center"></td>
                                <td class="subject-name" style="font-weight: 700;">Special Skill Average Grade</td>
                                @if($showMarks) <td class="center"></td> @endif
                                <td class="center" style="font-weight: 700;">{{ $specialSkillAverageGrade ?? '-' }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    @endif

    <div class="section">
        <div class="sub-title-bar">Attendance — Term 1</div>
        <div class="table-wrap">
            <table class="data-table attendance-table" id="attendanceTableT1">
                <colgroup>
                    <col style="width: 40%;">
                    <col style="width: 15%;">
                    <col style="width: 15%;">
                    <col style="width: 15%;">
                    <col style="width: 15%;">
                </colgroup>
                <thead>
                    <tr>
                        <th class="text-left">Month</th>
                        <th>Total Days</th>
                        <th>Working Days</th>
                        <th>Days Present</th>
                        <th>Attendance %</th>
                    </tr>
                </thead>
                <tbody>
                    @php $t1TotalDays = 0; $t1Working = 0; $t1Present = 0; @endphp
                    @foreach ($term1Months as $month)
                        @php
                            $meta = $monthMeta[$month] ?? ['en'=>$month,'overlap'=>''];
                            $row = $savedAttendance[$month] ?? [];
                            $totalDays = $row['total_days'] ?? ($defaultTotalDays[$month] ?? '');
                            $working = old("attendance.$month.working", $row['working'] ?? '');
                            $present = old("attendance.$month.present", $row['present'] ?? '');
                            $absent = old("attendance.$month.absent", $row['absent'] ?? '');

                            $totalDaysNum = (int) ($totalDays !== '' ? $totalDays : 0);
                            $workingNum = (int) ($working !== '' ? $working : 0);
                            $presentNum = (int) ($present !== '' ? $present : 0);

                            $t1TotalDays += $totalDaysNum;
                            $t1Working += $workingNum;
                            $t1Present += $presentNum;

                            $rowPct = $workingNum > 0 ? round(($presentNum / $workingNum) * 100, 1) : '-';
                        @endphp
                        <tr class="attendance-row" data-month="{{ $month }}">
                            <td class="month-cell"><span class="mr-name">{{ $meta['en'] }}</span> @if(!empty($meta['overlap'])) <span class="overlap">/ {{ $meta['overlap'] }}</span> @endif</td>
                            <td class="center">
                                @if($isEdit) <input type="text" name="attendance[{{ $month }}][total_days]" class="edit-input center attendance-total" maxlength="10" inputmode="numeric" value="{{ $totalDays }}"> @else {!! $totalDays !== '' ? e($totalDays) : '-' !!} @endif
                            </td>
                            <td class="center">
                                @if($isEdit) <input type="text" name="attendance[{{ $month }}][working]" class="edit-input center attendance-working" maxlength="10" inputmode="numeric" value="{{ $working }}"> @else {!! $working !== '' ? e($working) : '-' !!} @endif
                            </td>
                            <td class="center">
                                @if($isEdit) <input type="text" name="attendance[{{ $month }}][present]" class="edit-input center attendance-present" maxlength="10" inputmode="numeric" value="{{ $present }}"> @else {!! $present !== '' ? e($present) : '-' !!} @endif
                            </td>
                            <td class="center attendance-pct">{!! $rowPct !== '-' ? $rowPct . '%' : '-' !!}</td>
                            @if($isEdit) <input type="hidden" name="attendance[{{ $month }}][absent]" class="attendance-absent" value="{{ $absent }}"> @endif
                        </tr>
                    @endforeach
                    <tr class="total-row" style="background: #f4f4f4;">
                        <td class="text-left">TOTAL</td>
                        <td class="center t-days">{{ $t1TotalDays > 0 ? $t1TotalDays : '-' }}</td>
                        <td class="center t-working">{{ $t1Working > 0 ? $t1Working : '-' }}</td>
                        <td class="center t-present">{{ $t1Present > 0 ? $t1Present : '-' }}</td>
                        <td class="center t-pct">{{ $t1Working > 0 ? round(($t1Present / $t1Working) * 100, 1) . '%' : '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @if($isTerm2)
        <div class="section">
            <div class="sub-title-bar">Attendance — Term 2</div>
            <div class="table-wrap">
                <table class="data-table attendance-table" id="attendanceTableT2">
                    <colgroup>
                        <col style="width: 40%;">
                        <col style="width: 15%;">
                        <col style="width: 15%;">
                        <col style="width: 15%;">
                        <col style="width: 15%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="text-left">Month</th>
                            <th>Total Days</th>
                            <th>Working Days</th>
                            <th>Days Present</th>
                            <th>Attendance %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $t2TotalDays = 0; $t2Working = 0; $t2Present = 0; @endphp
                        @foreach ($term2Months as $month)
                            @php
                                $meta = $monthMeta[$month] ?? ['en'=>$month,'overlap'=>''];
                                $row = $savedAttendance[$month] ?? [];
                                $totalDays = $row['total_days'] ?? ($defaultTotalDays[$month] ?? '');
                                $working = old("attendance.$month.working", $row['working'] ?? '');
                                $present = old("attendance.$month.present", $row['present'] ?? '');
                                $absent = old("attendance.$month.absent", $row['absent'] ?? '');

                                $totalDaysNum = (int) ($totalDays !== '' ? $totalDays : 0);
                                $workingNum = (int) ($working !== '' ? $working : 0);
                                $presentNum = (int) ($present !== '' ? $present : 0);

                                $t2TotalDays += $totalDaysNum;
                                $t2Working += $workingNum;
                                $t2Present += $presentNum;

                                $rowPct = $workingNum > 0 ? round(($presentNum / $workingNum) * 100, 1) : '-';
                            @endphp
                            <tr class="attendance-row" data-month="{{ $month }}">
                                <td class="month-cell"><span class="mr-name">{{ $meta['en'] }}</span> @if(!empty($meta['overlap'])) <span class="overlap">/ {{ $meta['overlap'] }}</span> @endif</td>
                                <td class="center">
                                    @if($isEdit) <input type="text" name="attendance[{{ $month }}][total_days]" class="edit-input center attendance-total" maxlength="10" inputmode="numeric" value="{{ $totalDays }}"> @else {!! $totalDays !== '' ? e($totalDays) : '-' !!} @endif
                                </td>
                                <td class="center">
                                    @if($isEdit) <input type="text" name="attendance[{{ $month }}][working]" class="edit-input center attendance-working" maxlength="10" inputmode="numeric" value="{{ $working }}"> @else {!! $working !== '' ? e($working) : '-' !!} @endif
                                </td>
                                <td class="center">
                                    @if($isEdit) <input type="text" name="attendance[{{ $month }}][present]" class="edit-input center attendance-present" maxlength="10" inputmode="numeric" value="{{ $present }}"> @else {!! $present !== '' ? e($present) : '-' !!} @endif
                                </td>
                                <td class="center attendance-pct">{!! $rowPct !== '-' ? $rowPct . '%' : '-' !!}</td>
                                @if($isEdit) <input type="hidden" name="attendance[{{ $month }}][absent]" class="attendance-absent" value="{{ $absent }}"> @endif
                            </tr>
                        @endforeach
                        <tr class="total-row" style="background: #f4f4f4;">
                            <td class="text-left">TOTAL</td>
                            <td class="center t-days">{{ $t2TotalDays > 0 ? $t2TotalDays : '-' }}</td>
                            <td class="center t-working">{{ $t2Working > 0 ? $t2Working : '-' }}</td>
                            <td class="center t-present">{{ $t2Present > 0 ? $t2Present : '-' }}</td>
                            <td class="center t-pct">{{ $t2Working > 0 ? round(($t2Present / $t2Working) * 100, 1) . '%' : '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($isTerm2)
        <div class="important-note" style="margin-bottom: 3px;">
            <div class="title">Teacher's Remarks :</div>
            <ol style="margin: 1px 0 0 14px; padding: 0;">
                <li>
                    Passed and promoted to 
                    @if($isEdit)
                        <input type="text" name="passed_promoted_to" class="remark-input" maxlength="80" value="{{ old('passed_promoted_to', $progress->passed_promoted_to ?? '') }}" spellcheck="true" autocorrect="on" autocapitalize="sentences">
                    @else
                        <span>{{ $progress->passed_promoted_to ?? '______________________' }}</span>
                    @endif
                </li>
                <li>
                    School reopens on 
                    @if($isEdit)
                        <input type="text" name="school_reopens_on" class="remark-input" maxlength="80" value="{{ old('school_reopens_on', $progress->school_reopens_on ?? '') }}" spellcheck="true" autocorrect="on" autocapitalize="sentences">
                    @else
                        <span>{{ $progress->school_reopens_on ?? '______________________' }}</span>
                    @endif
                </li>
            </ol>
        </div>
    @endif

    <!-- Descriptive Record -->
    <div class="section descriptive-section">
        <div class="sub-title-bar">Descriptive Record</div>
        <div class="table-wrap">
            <table class="data-table descriptive-table">
                <colgroup><col style="width: 25%;"><col style="width: 75%;"></colgroup>
                <tbody>
                    <tr class="descriptive-comment-row">
                        <td style="font-weight: 700; background: #f4f4f4; vertical-align: top; padding-top: 8px;">Teacher Remark</td>
                        <td style="vertical-align: top; padding-top: 8px;">
                            @if($isEdit)
                                <textarea name="teacher_remark" class="edit-input" rows="3" spellcheck="true" lang="en" autocorrect="on" autocapitalize="sentences">{{ old("teacher_remark", $teacherRemarkText) }}</textarea>
                            @else
                                {{ $teacherRemarkText }}
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @if($isPrePrimary)
    <div class="section">
        <div class="sub-title-bar">Grade Scale</div>
        <div class="table-wrap" style="padding: 2px 8px; font-size: 9.5px; font-weight: 600; line-height: 1.25;">
            <strong>A+</strong> = EXCELLENT &nbsp;|&nbsp;
            <strong>A</strong> = VERY GOOD &nbsp;|&nbsp;
            <strong>B+</strong> = GOOD &nbsp;|&nbsp;
            <strong>B</strong> = NEEDS HELP &nbsp;|&nbsp;
            <strong>C+</strong> = PRACTICE NEEDED/MORE ATTENTION &nbsp;|&nbsp;
            <strong>C</strong> = NEEDS MOTIVATION
        </div>
    </div>
    @endif

    <div class="section">
        <div class="sub-title-bar">Grade Chart</div>
        <div class="table-wrap">
            <table class="data-table grade-chart-table">
                <colgroup>
                    <col style="width: 11.11%;"><col style="width: 11.11%;"><col style="width: 11.11%;">
                    <col style="width: 11.11%;"><col style="width: 11.11%;"><col style="width: 11.11%;">
                    <col style="width: 11.11%;"><col style="width: 11.11%;"><col style="width: 11.12%;">
                </colgroup>
                <thead>
                    <tr>
                        <th>91% to 100%</th>
                        <th>81% to 90%</th>
                        <th>71% to 80%</th>
                        <th>61% to 70%</th>
                        <th>51% to 60%</th>
                        <th>41% to 50%</th>
                        <th>33% to 40%</th>
                        <th>21% to 32%</th>
                        <th>Below 21%</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="center">A1</td>
                        <td class="center">A2</td>
                        <td class="center">B1</td>
                        <td class="center">B2</td>
                        <td class="center">C1</td>
                        <td class="center">C2</td>
                        <td class="center">D</td>
                        <td class="center">E1</td>
                        <td class="center">E2</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="important-note">
        <div class="title">Important</div>
        <div class="greeting">Dear Parents,</div>
        <ul><li>Study the report card carefully.</li><li>Preserve it carefully.</li><li>This progress report stands as a testament to our collective hard work, celebrating how far your child has come while highlighting the vast potential still left to unlock.<br><br></li></ul>        
    </div>

    <div class="signatures">
        <div class="sig-box"><div class="sig-line"></div>Class Teacher's Sign</div>
        <div class="sig-box"><div class="sig-line"></div>Principal's Sign</div>
        <div class="sig-box"><div class="sig-line"></div>Parent's Sign</div>
    </div>

</div>{{-- /.page-frame --}}
</div>{{-- /.page --}}

@if($isEdit)
</form>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if($isEdit)
        function updateRowAndTotals(tableId) {
            const table = document.getElementById(tableId);
            if (!table) return;

            let totalDays = 0, totalWorking = 0, totalPresent = 0;

            table.querySelectorAll('tbody tr.attendance-row').forEach(function (row) {
                const totalInput = row.querySelector('.attendance-total');
                const workingInput = row.querySelector('.attendance-working');
                const presentInput = row.querySelector('.attendance-present');
                const absentInput  = row.querySelector('.attendance-absent');
                const pctCell = row.querySelector('.attendance-pct');

                const totalVal = parseInt(totalInput.value) || 0;
                const workingVal = parseInt(workingInput.value) || 0;
                const presentVal = parseInt(presentInput.value) || 0;

                totalDays += totalVal;
                totalWorking += workingVal;
                totalPresent += presentVal;

                if (workingInput.value.trim() === '' || presentInput.value.trim() === '') {
                    absentInput.value = '';
                    pctCell.textContent = '-';
                } else {
                    let absent = workingVal - presentVal;
                    if (absent < 0) {
                        absent = 0;
                        presentInput.value = workingVal;
                    }
                    absentInput.value = absent;
                    pctCell.textContent = workingVal > 0 ? (presentVal / workingVal * 100).toFixed(1) + '%' : '-';
                }
            });

            const totalRow = table.querySelector('.total-row');
            if (totalRow) {
                totalRow.querySelector('.t-days').textContent = totalDays > 0 ? totalDays : '-';
                totalRow.querySelector('.t-working').textContent = totalWorking > 0 ? totalWorking : '-';
                totalRow.querySelector('.t-present').textContent = totalPresent > 0 ? totalPresent : '-';
                totalRow.querySelector('.t-pct').textContent = totalWorking > 0 ? (totalPresent / totalWorking * 100).toFixed(1) + '%' : '-';
            }
        }

        function sanitize(input) {
            input.value = input.value.replace(/[^0-9]/g, '');
        }

        document.querySelectorAll('.attendance-row').forEach(function (row) {
            const totalInput = row.querySelector('.attendance-total');
            const workingInput = row.querySelector('.attendance-working');
            const presentInput = row.querySelector('.attendance-present');
            const table = row.closest('table');

            if (totalInput) {
                totalInput.addEventListener('input', function () { sanitize(this); updateRowAndTotals(table.id); });
                totalInput.addEventListener('blur',  function () { sanitize(this); updateRowAndTotals(table.id); });
            }
            if (workingInput) {
                workingInput.addEventListener('input', function () { sanitize(this); updateRowAndTotals(table.id); });
                workingInput.addEventListener('blur',  function () { sanitize(this); updateRowAndTotals(table.id); });
            }
            if (presentInput) {
                presentInput.addEventListener('input', function () { sanitize(this); updateRowAndTotals(table.id); });
                presentInput.addEventListener('blur',  function () { sanitize(this); updateRowAndTotals(table.id); });
            }
        });
        @endif
    });

    (function () {
        let originalFrameStyles = null;

        function restorePrintFit() {
            const frame = document.querySelector('.page-frame');
            if (!frame || !originalFrameStyles) return;

            frame.style.width = originalFrameStyles.width;
            frame.style.height = originalFrameStyles.height;
            frame.style.transform = originalFrameStyles.transform;
            frame.style.transformOrigin = originalFrameStyles.transformOrigin;
            originalFrameStyles = null;
        }

        function fitProgressCardForPrint() {
            const frame = document.querySelector('.page-frame');
            if (!frame) return;

            if (!originalFrameStyles) {
                originalFrameStyles = {
                    width: frame.style.width,
                    height: frame.style.height,
                    transform: frame.style.transform,
                    transformOrigin: frame.style.transformOrigin
                };
            }

            /* Start from the normal A4 frame, then measure after print CSS is active. */
            frame.style.width = '100%';
            frame.style.height = '100%';
            frame.style.transform = 'none';
            frame.style.transformOrigin = 'top left';
            void frame.offsetHeight;

            const availableHeight = frame.clientHeight;
            const contentHeight = frame.scrollHeight;

            if (availableHeight > 0 && contentHeight > availableHeight + 2) {
                /* Expand the layout box and scale it back to the exact A4 frame size. */
                const scale = Math.max(0.40, Math.min(1, (availableHeight - 1) / contentHeight));
                frame.style.width = (100 / scale).toFixed(3) + '%';
                frame.style.height = (100 / scale).toFixed(3) + '%';
                frame.style.transform = 'scale(' + scale.toFixed(4) + ')';
                frame.style.transformOrigin = 'top left';
            }
        }

        window.addEventListener('beforeprint', fitProgressCardForPrint);
        window.addEventListener('afterprint', restorePrintFit);

        window.printProgressCard = function () {
            window.focus();
            window.print();
        };

        window.closeProgressCard = function () {
            if (window.opener && !window.opener.closed) {
                window.close();
                return;
            }
            if (window.history.length > 1) {
                window.history.back();
                return;
            }
            window.close();
        };
    })();
</script>

</body>
</html>