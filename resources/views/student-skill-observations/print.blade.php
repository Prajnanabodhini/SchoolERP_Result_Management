<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Skill Observation Summary</title>
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
        
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        @media print {
            .no-print { display: none !important; }
            
            /* Print specific styles for the logo */
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

        @if(Route::has('student-skill-observations.export-excel'))
            <a href="{{ route('student-skill-observations.export-excel', [
                    'academic_year_id' => request('academic_year_id'),
                    'standard_id'      => request('standard_id'),
                    'division_id'      => request('division_id'),
                    'exam_master_id'   => request('exam_master_id'),
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
    use App\Helpers\SkillCriteriaHelper;

    $printAcademicYear = $academicYears->firstWhere('id', (int) request('academic_year_id'));
    $printStandard     = $standards->firstWhere('id', (int) request('standard_id'));
    $printDivision     = $divisions->firstWhere('id', (int) request('division_id'));

    $printExamName = 'All Exams';
    if (request('exam_master_id')) {
        $printExamName = optional($exams->firstWhere('id', (int) request('exam_master_id')))->exam_name ?? 'All Exams';
    }

    $printSchoolCode  = session('school_code', 'shirgaon');
    $printYearLabel   = $printAcademicYear->year_name ?? '';

    $classTeacherName = $classTeacherName ?? '-';
    $principalName    = $principalName    ?? '-';

    $logoDataUri      = $logoDataUri ?? '';
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
                    <div style="font-size: 11px; line-height: 13px; font-weight: 700; color: #475569; text-align: center; margin-top: 2px;">Academic Year : {{ $printYearLabel }}</div>
                @else
                    <div style="font-size: 16px; line-height: 18px; font-weight: 800; color: #1e3a8a; text-align: center; white-space: nowrap;">PRAJNANABODHINI ENGLISH MEDIUM SCHOOL &amp; JR. COLLEGE</div>
                    <div style="font-size: 12px; line-height: 14px; font-weight: 800; color: #1e3a8a; text-align: center; white-space: nowrap; text-transform: uppercase; margin-top: 1px;">SHIRGAON</div>
                    <div style="font-size: 10px; line-height: 12px; font-weight: 700; color: #475569; text-align: center; white-space: nowrap; margin-top: 2px;">Gat No. 227, Shirgaon-Gahunje Rd, At. Post. Shirgaon, Tal. Maval, Dist. Pune, Maharashtra - 410506</div>
                    <div style="font-size: 11px; line-height: 13px; font-weight: 700; color: #475569; text-align: center; margin-top: 2px;">Academic Year : {{ $printYearLabel }}</div>
                @endif
            </td>
            <td style="width: 100px; border: none; padding: 0;"></td>
        </tr>
    </table>

    <div style="border-bottom: 1.5px solid #000; margin-top: 2px; margin-bottom: 3px;"></div>

    {{-- EXAM INFO ROW --}}
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 3px; border: none;">
        <tr>
            <td style="font-size: 10.5px; padding: 1px; text-align: center; font-weight: 700; white-space: nowrap; border: none;"><strong>Exam :</strong> {{ $printExamName }}</td>
            <td style="font-size: 10.5px; padding: 1px; text-align: center; font-weight: 700; white-space: nowrap; border: none;"><strong>Standard :</strong> {{ $printStandard->standard_name ?? '' }}</td>
            <td style="font-size: 10.5px; padding: 1px; text-align: center; font-weight: 700; white-space: nowrap; border: none;"><strong>Division :</strong> {{ $printDivision->division_name ?? '' }}</td>
            <td style="font-size: 10.5px; padding: 1px; text-align: center; font-weight: 700; white-space: nowrap; border: none;"><strong>Total Students :</strong> {{ count($students) }}</td>
        </tr>
    </table>

    {{-- SUMMARY TABLE --}}
    <div style="width: 100%; max-width: 100%; overflow: hidden;">
        <table style="width: 100%; max-width: 100%; border-collapse: collapse; border: 1px solid #333; font-family: Arial, sans-serif;">
            <thead>
                <tr>
                    <th style="background-color: #DBEAFE; border: 1px solid #333; padding: 2px 1px; text-align: center; font-weight: 700; font-size: 10px; text-transform: uppercase; width: 5%; word-wrap: break-word; white-space: normal;">Roll No</th>
                    <th style="background-color: #DBEAFE; border: 1px solid #333; padding: 2px 1px; text-align: left;   font-weight: 700; font-size: 10px; text-transform: uppercase; width: 18%; word-wrap: break-word; white-space: normal;">Student Name</th>

                    @foreach($skillSubjects as $sub)
                        <th style="background-color: #DBEAFE; border: 1px solid #333; padding: 2px 1px; text-align: center; font-weight: 700; font-size: 10px; text-transform: uppercase; word-wrap: break-word; white-space: normal;">{{ $sub->subject_name }}</th>
                    @endforeach

                    <th style="background-color: #DBEAFE; border: 1px solid #333; padding: 2px 1px; text-align: center; font-weight: 700; font-size: 10px; text-transform: uppercase; width: 7%; word-wrap: break-word; white-space: normal;">Average Grade</th>
                    <th style="background-color: #DBEAFE; border: 1px solid #333; padding: 2px 1px; text-align: center; font-weight: 700; font-size: 10px; text-transform: uppercase; width: 8%; word-wrap: break-word; white-space: normal;">Remark</th>
                    <th style="background-color: #DBEAFE; border: 1px solid #333; padding: 2px 1px; text-align: center; font-weight: 700; font-size: 10px; text-transform: uppercase; width: 6%; word-wrap: break-word; white-space: normal;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                    @php
                        $studentName = trim(($student->studname ?? '') . ' ' . ($student->fathername ?? ''));

                        $hasAbsent = false;
                        $hasPresent = false;
                        foreach ($skillSubjects as $sub) {
                            $key = $student->Studentid . '|' . $sub->id;
                            $obs = $observations[$key] ?? null;
                            if ($obs) {
                                if ($obs->is_absent) {
                                    $hasAbsent = true;
                                } else {
                                    $hasPresent = true;
                                }
                            }
                        }
                        $overallStatus = $hasPresent ? 'PRESENT' : ($hasAbsent ? 'ABSENT' : '-');
                    @endphp
                    <tr>
                        <td style="border: 1px solid #333; padding: 1px; text-align: center; font-size: 9px; font-weight: bold;">{{ $student->rollno ?: '-' }}</td>
                        <td style="border: 1px solid #333; padding: 1px; text-align: left; font-size: 9px; font-weight: bold; white-space: normal; word-wrap: break-word;" title="{{ $studentName }}">{{ $studentName ?: '-' }}</td>

                        @foreach($skillSubjects as $sub)
                            @php
                                $key         = $student->Studentid . '|' . $sub->id;
                                $obs         = $observations[$key] ?? null;
                                $cell        = '';
                                $cellColor   = '#9CA3AF';
                                $bgColor     = '#ffffff';

                                if ($obs) {
                                    if ($obs->is_absent) {
                                        $cell = 'AB';
                                        $cellColor = '#C2410C';
                                        $bgColor = '#FFF7ED';
                                    } else {
                                        $avg = $obs->overall_grade;
                                        if (!$avg && !empty($obs->observations)) {
                                            $avg = SkillCriteriaHelper::computeAverageGrade($obs->observations);
                                        }
                                        $cell = $avg ?: '';
                                        $cellColor = '#111827';
                                        $bgColor = '#DBEAFE';
                                    }
                                }
                            @endphp
                            <td style="border: 1px solid #333; padding: 1px; text-align: center; font-size: 9px; font-weight: bold; color: {{ $cellColor }}; background-color: {{ $bgColor }}; white-space: nowrap;">
                                {{ $cell }}
                            </td>
                        @endforeach

                        <td style="border: 1px solid #333; padding: 1px; text-align: center; font-size: 9px; font-weight: bold; color: #92400E; background-color: #FEF3C7; white-space: nowrap;">
                            {{ $studentAverages[$student->Studentid] ?? '-' }}
                        </td>

                        <td style="border: 1px solid #333; padding: 1px; text-align: left; font-size: 9px; white-space: normal; word-wrap: break-word;">
                            &nbsp;
                        </td>

                        <td style="border: 1px solid #333; padding: 1px; text-align: center; font-size: 9px; font-weight: bold; white-space: nowrap; color: {{ $overallStatus === 'ABSENT' ? '#DC2626' : ($overallStatus === 'PRESENT' ? '#16A34A' : '#111827') }};">
                            {{ $overallStatus }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 4 + $skillSubjects->count() }}" style="border: 1px solid #333; padding: 4px; text-align: center; font-weight: bold; font-size: 10px;">
                            No result records found.
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