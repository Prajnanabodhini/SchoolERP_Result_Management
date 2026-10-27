<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Skill Observation — {{ $subjectName ?? '' }}</title>
    <style>
        html, body {
            width: 100%;
            max-width: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden !important;
            box-sizing: border-box;
        }
        *, *::before, *::after {
            box-sizing: border-box;
        }
        table {
            table-layout: fixed !important;
        }
        
        /* Print specific styles for the logo */
        @media print {
            .no-print { display: none !important; }
            .school-logo { width: 78px !important; height: 78px !important; }
            .school-logo-cell { width: 86px !important; }
        }
    </style>
</head>
<body style="font-family: Arial, sans-serif; font-size: 11px; color: #111827; background: #fff;">

<div style="width: 100%; max-width: 100%; padding: 4px; overflow: hidden;">

    {{-- PRINT CONTROLS (Hidden during export and print) --}}
    @if(!request('export') && empty($isExport))
    <div class="no-print" style="position: fixed; top: 10px; right: 10px; z-index: 99999; display: flex; align-items: center; gap: 8px;">
        <button type="button" style="background: #1d4ed8; color: #fff; border: 1px solid #374151; border-radius: 4px; padding: 7px 15px; font-size: 13px; font-weight: 700; cursor: pointer;" onclick="window.print()">
            🖨 Print / Save PDF
        </button>

        @if(Route::has('student-skill-observations.export-entry-excel'))
            <a href="{{ route('student-skill-observations.export-entry-excel', [
                    'academic_year_id' => request('academic_year_id'),
                    'standard_id'      => request('standard_id'),
                    'division_id'      => request('division_id'),
                    'exam_master_id'   => request('exam_master_id'),
                    'subject_id'       => request('subject_id'),
                ]) }}"
               style="background: #15803d; color: #fff; border: 1px solid #374151; border-radius: 4px; padding: 7px 15px; font-size: 13px; font-weight: 700; text-decoration: none; display: inline-block;">
                📊 Save as Excel
            </a>
        @endif

        <button type="button" style="background: #6b7280; color: #fff; border: 1px solid #374151; border-radius: 4px; padding: 7px 15px; font-size: 13px; font-weight: 700; cursor: pointer;" onclick="window.close()">
            ✕ Close
        </button>
    </div>
    @endif

@php
    $printSchoolCode = session('school_code', 'shirgaon');

    $gradeCriteria  = [];
    $remarkCriteria = [];
    foreach (($criteria ?? []) as $c) {
        if (stripos($c, 'remark') !== false) {
            $remarkCriteria[] = $c;
        } else {
            $gradeCriteria[] = $c;
        }
    }

    $skillTeacherName = $skillTeacherName ?? '-';

    $logoDataUri = $logoDataUri ?? '';
@endphp

    {{-- SCHOOL HEADER --}}
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 2px; border: none;">
        <tr>
            <td class="school-logo-cell" style="width: 100px; text-align: center; vertical-align: middle; border: none; padding: 0;">
                @if($logoDataUri)
                    <img src="{{ $logoDataUri }}" class="school-logo" width="88" height="88" alt="School Logo" style="width: 88px; height: 88px; object-fit: contain;">
                @else
                    <img src="{{ asset('images/school-logo.png') }}" class="school-logo" width="88" height="88" alt="School Logo" style="width: 88px; height: 88px; object-fit: contain;">
                @endif
            </td>
            <td style="text-align: center; vertical-align: middle; border: none; padding: 0;">
                @if($printSchoolCode === 'chikhali')
                    <div style="font-size: 16px; line-height: 18px; font-weight: 800; color: #1e3a8a; text-align: center; white-space: nowrap;">PRAJNANABODHINI ENGLISH MEDIUM SCHOOL CHIKHALI</div>
                    <div style="font-size: 10px; line-height: 12px; font-weight: 700; color: #475569; text-align: center; white-space: nowrap; margin-top: 2px;">S.No. 1517/1, Sonwane Vasti, Chikhali, Tal. Haveli, Dist. Pune, Maharashtra 411062</div>
                    <div style="font-size: 11px; line-height: 13px; font-weight: 700; color: #475569; text-align: center; margin-top: 2px;">Academic Year : {{ $printAcademicYearName }}</div>
                @else
                    <div style="font-size: 16px; line-height: 18px; font-weight: 800; color: #1e3a8a; text-align: center; white-space: nowrap;">PRAJNANABODHINI ENGLISH MEDIUM SCHOOL &amp; JR. COLLEGE</div>
                    <div style="font-size: 12px; line-height: 14px; font-weight: 800; color: #1e3a8a; text-align: center; white-space: nowrap; text-transform: uppercase; margin-top: 1px;">SHIRGAON</div>
                    <div style="font-size: 10px; line-height: 12px; font-weight: 700; color: #475569; text-align: center; white-space: nowrap; margin-top: 2px;">Gat No. 227, Shirgaon-Gahunje Rd, At. Post. Shirgaon, Tal. Maval, Dist. Pune, Maharashtra - 410506</div>
                    <div style="font-size: 11px; line-height: 13px; font-weight: 700; color: #475569; text-align: center; margin-top: 2px;">Academic Year : {{ $printAcademicYearName }}</div>
                @endif
            </td>
            <td style="width: 100px; border: none; padding: 0;"></td>
        </tr>
    </table>

    <div style="border-bottom: 1.5px solid #000; margin-top: 2px; margin-bottom: 3px;"></div>

    {{-- =========================================================
         EXAM INFO ROW — fixed 6-column layout, no overlap
    ========================================================== --}}
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 3px; border: none; table-layout: fixed;">
        <colgroup>
            <col style="width: 22%;">
            <col style="width: 16%;">
            <col style="width: 12%;">
            <col style="width: 20%;">
            <col style="width: 20%;">
            <col style="width: 10%;">
        </colgroup>
        <tr>
            <td style="font-size: 10.5px; padding: 1px 2px; text-align: center; font-weight: 700; border: none; word-wrap: break-word; overflow-wrap: break-word; white-space: normal; line-height: 1.2;">
                <strong>Exam :</strong> {{ $printExamName }}
            </td>
            <td style="font-size: 10.5px; padding: 1px 2px; text-align: center; font-weight: 700; border: none; word-wrap: break-word; overflow-wrap: break-word; white-space: normal; line-height: 1.2;">
                <strong>Standard :</strong> {{ $printStandardName }}
            </td>
            <td style="font-size: 10.5px; padding: 1px 2px; text-align: center; font-weight: 700; border: none; word-wrap: break-word; overflow-wrap: break-word; white-space: normal; line-height: 1.2;">
                <strong>Division :</strong> {{ $printDivisionName }}
            </td>
            <td style="font-size: 10.5px; padding: 1px 2px; text-align: center; font-weight: 700; border: none; word-wrap: break-word; overflow-wrap: break-word; white-space: normal; line-height: 1.2;">
                <strong>Skill Subject :</strong> {{ $subjectName ?? '-' }}
            </td>
            <td style="font-size: 10.5px; padding: 1px 2px; text-align: center; font-weight: 700; border: none; word-wrap: break-word; overflow-wrap: break-word; white-space: normal; line-height: 1.2;">
                <strong>Skill Teacher :</strong> {{ $skillTeacherName }}
            </td>
            <td style="font-size: 10.5px; padding: 1px 2px; text-align: center; font-weight: 700; border: none; word-wrap: break-word; overflow-wrap: break-word; white-space: normal; line-height: 1.2;">
                <strong>Students :</strong> {{ count($students) }}
            </td>
        </tr>
    </table>

    {{-- OBSERVATION TABLE --}}
    <div style="width: 100%; max-width: 100%; overflow: hidden;">
        <table style="width: 100%; max-width: 100%; border-collapse: collapse; border: 1px solid #333; font-family: Arial, sans-serif;">
            <thead>
                <tr>
                    <th style="background-color: #DBEAFE; border: 1px solid #333; padding: 2px 1px; text-align: center; font-weight: 700; font-size: 10px; text-transform: uppercase; width: 4%; word-wrap: break-word; white-space: normal;">GR No</th>
                    <th style="background-color: #DBEAFE; border: 1px solid #333; padding: 2px 1px; text-align: center; font-weight: 700; font-size: 10px; text-transform: uppercase; width: 5%; word-wrap: break-word; white-space: normal;">Roll No</th>
                    <th style="background-color: #DBEAFE; border: 1px solid #333; padding: 2px 1px; text-align: left;   font-weight: 700; font-size: 10px; text-transform: uppercase; width: 18%; word-wrap: break-word; white-space: normal;">Student Name</th>

                    @foreach($gradeCriteria as $criterion)
                        <th style="background-color: #DBEAFE; border: 1px solid #333; padding: 2px 1px; text-align: center; font-weight: 700; font-size: 10px; text-transform: uppercase; word-wrap: break-word; white-space: normal;">{{ $criterion }}</th>
                    @endforeach

                    <th style="background-color: #DBEAFE; border: 1px solid #333; padding: 2px 1px; text-align: center; font-weight: 700; font-size: 10px; text-transform: uppercase; width: 7%; word-wrap: break-word; white-space: normal;">Average Grade</th>

                    @foreach($remarkCriteria as $criterion)
                        <th style="background-color: #DBEAFE; border: 1px solid #333; padding: 2px 1px; text-align: center; font-weight: 700; font-size: 10px; text-transform: uppercase; width: 8%; word-wrap: break-word; white-space: normal;">{{ $criterion }}</th>
                    @endforeach

                    <th style="background-color: #DBEAFE; border: 1px solid #333; padding: 2px 1px; text-align: center; font-weight: 700; font-size: 10px; text-transform: uppercase; width: 6%; word-wrap: break-word; white-space: normal;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                    @php
                        $obs       = $existing[$student->Studentid] ?? null;
                        $savedData = $obs->observations ?? [];
                        $isAbsent  = (int) ($obs->is_absent ?? 0);
                        $avgGrade  = $obs->overall_grade ?? '';

                        if (!$avgGrade && !$isAbsent && !empty($savedData)) {
                            $avgGrade = \App\Helpers\SkillCriteriaHelper::computeAverageGrade($savedData);
                        }

                        $studentName = trim(($student->studname ?? '') . ' ' . ($student->fathername ?? ''));
                    @endphp
                    <tr>
                        <td style="border: 1px solid #333; padding: 1px; text-align: center; font-size: 9px;">{{ $student->regno ?? '-' }}</td>
                        <td style="border: 1px solid #333; padding: 1px; text-align: center; font-size: 9px; font-weight: bold;">{{ $student->rollno ?? '-' }}</td>
                        <td style="border: 1px solid #333; padding: 1px; text-align: left; font-size: 9px; font-weight: bold; white-space: normal; word-wrap: break-word;" title="{{ $studentName }}">{{ $studentName ?: '-' }}</td>

                        @foreach($gradeCriteria as $criterion)
                            @php
                                $val = $savedData[$criterion] ?? '';
                                if ($isAbsent && $val === '') $val = 'AB';
                                $cellColor = '#1e3a8a';
                                $bgColor = '#ffffff';
                                if ($val === 'AB') { $cellColor = '#C2410C'; $bgColor = '#FFF7ED'; }
                                elseif ($val !== '') { $cellColor = '#1e3a8a'; $bgColor = '#DBEAFE'; }
                            @endphp
                            <td style="border: 1px solid #333; padding: 1px; text-align: center; font-size: 9px; font-weight: bold; color: {{ $cellColor }}; background-color: {{ $bgColor }}; white-space: nowrap;">
                                {{ $val ?: '—' }}
                            </td>
                        @endforeach

                        <td style="border: 1px solid #333; padding: 1px; text-align: center; font-size: 9px; font-weight: bold; color: #92400E; background-color: #FEF3C7; white-space: nowrap;">
                            {{ $isAbsent ? 'AB' : ($avgGrade ?: '—') }}
                        </td>

                        @foreach($remarkCriteria as $criterion)
                            @php
                                $val = $savedData[$criterion] ?? '';
                                if ($isAbsent && $val === '') $val = 'AB';
                            @endphp
                            <td style="border: 1px solid #333; padding: 1px; text-align: center; font-size: 9px; white-space: normal; word-wrap: break-word;">{{ $val ?: '—' }}</td>
                        @endforeach

                        <td style="border: 1px solid #333; padding: 1px; text-align: center; font-size: 9px; font-weight: bold; color: {{ $isAbsent ? '#DC2626' : '#16A34A' }}; white-space: nowrap;">
                            {{ $isAbsent ? 'ABSENT' : 'PRESENT' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 3 + count($gradeCriteria) + 1 + count($remarkCriteria) + 1 }}" style="border: 1px solid #333; padding: 4px; text-align: center; font-weight: bold; font-size: 10px;">
                            No students found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- SIGNATURE AREA --}}
    <div style="width: 100%; display: flex; justify-content: space-between; align-items: flex-end; margin-top: 15px;">
        <div style="width: 180px; text-align: center;">
            <span style="display: block; border-bottom: 1px solid #111827; width: 100%; height: 22px; margin-bottom: 5px;"></span>
            <span style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 3px;">{{ $classTeacherName ?: '-' }}</span>
            <span style="display: block; font-size: 10.5px; font-weight: 700; text-transform: uppercase; color: #374151;">Class Teacher</span>
        </div>
        <div style="width: 180px; text-align: center;">
            <span style="display: block; border-bottom: 1px solid #111827; width: 100%; height: 22px; margin-bottom: 5px;"></span>
            <span style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 3px;">{{ $principalName ?: '-' }}</span>
            <span style="display: block; font-size: 10.5px; font-weight: 700; text-transform: uppercase; color: #374151;">Principal</span>
        </div>
    </div>

</div>

</body>
</html>