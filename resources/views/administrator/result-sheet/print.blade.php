<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Examination Result Sheet</title>

    <style>

        @page {
            size: A4 landscape;
            margin-top: 20mm;
            margin-left: 15mm;
            margin-right: 4mm;
            margin-bottom: 7mm;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            max-width: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #111827;
            background: #fff;
            overflow-x: hidden;
        }

        @media print {

            html,
            body {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow-x: hidden !important;
            }

            .no-print {
                display: none !important;
            }

            .print-page {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
            }

            a[href]::after {
                content: none !important;
                display: none !important;
            }

            a {
                text-decoration: none !important;
                color: inherit !important;
            }

            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-footer-group;
            }

            tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .analysis-block,
            .signature-area,
            .subject-wise-analysis-wrapper {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .signature-area {
                break-inside: avoid !important;
            }

            .result-sheet-wrapper,
            .analysis-table-wrapper,
            .subject-wise-analysis-wrapper {
                width: 100% !important;
                max-width: 100% !important;
                overflow: hidden !important;
                position: relative !important;
            }
        }

        .print-controls {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 99999;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .print-button,
        .excel-button,
        .close-button {
            border: 1px solid #374151;
            color: #fff;
            border-radius: 4px;
            padding: 7px 15px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
        }

        .print-button {
            background: #1d4ed8;
        }

        .excel-button {
            background: #15803d;
        }

        .close-button {
            background: #6b7280;
        }

        .print-page {
            width: 100%;
            max-width: 100%;
            padding: 0;
            margin: 0;
            overflow: hidden;
        }

        .school-header,
        .exam-info {
            width: 100%;
            max-width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            table-layout: auto;
        }

        .school-header {
            margin-bottom: 4px;
        }

        .school-header td,
        .exam-info td {
            border: none !important;
            vertical-align: middle;
        }

        .school-header td {
            padding: 1px 3px;
        }

        .school-logo-cell {
            width: 60px;
            text-align: center !important;
        }

        .school-logo {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }

        .school-title-cell {
            text-align: center !important;
        }

        .school-name {
            font-size: 19px;
            line-height: 22px;
            font-weight: 700;
            text-align: center;
            white-space: nowrap;
        }

        .school-location,
        .school-year {
            font-size: 13px;
            line-height: 16px;
            font-weight: 700;
            text-align: center;
            white-space: nowrap;
        }

        .exam-info {
            margin-bottom: 5px;
        }

        .exam-info td {
            font-size: 11px;
            padding: 2px 4px;
            text-align: left;
            font-weight: 600;
            white-space: nowrap;
        }

        .exam-info strong {
            font-weight: 700;
        }

        .result-sheet-wrapper,
        .analysis-table-wrapper,
        .subject-wise-analysis-wrapper {
            width: 100%;
            max-width: 100%;
            overflow: hidden;
        }

        .result-sheet-table,
        .analysis-table,
        .subject-wise-analysis-table {
            width: 100%;
            max-width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            table-layout: auto;
            border: 1px solid #333;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5px;
            min-width: 0;
        }

        .result-sheet-table th,
        .result-sheet-table td,
        .analysis-table th,
        .analysis-table td,
        .subject-wise-analysis-table th,
        .subject-wise-analysis-table td {
            border: 1px solid #333 !important;
            border-style: solid !important;
            border-width: 1px !important;
            text-align: center;
            vertical-align: middle;
            color: #111827 !important;
            min-width: 0;
            padding: 2px 2px;
        }

        .result-sheet-table th {
            background: #dbeafe;
            font-size: 9.5px;
            font-weight: 700 !important;
            line-height: 1.05;
            white-space: nowrap !important;
            overflow: visible;
            overflow-wrap: normal !important;
            word-break: normal !important;
        }

        .result-sheet-table td {
            font-size: 9.5px;
            font-weight: 500;
            line-height: 1.05;
            white-space: nowrap !important;
        }

        .result-sheet-table td:first-child {
            white-space: nowrap !important;
            font-weight: 700;
            width: auto !important;
            min-width: 0 !important;
        }

        .student-name-cell {
            text-align: left !important;
            width: auto !important;
            min-width: 0 !important;
            max-width: none !important;
            white-space: nowrap !important;
            font-weight: 700 !important;
            overflow: visible !important;
            text-overflow: clip !important;
            overflow-wrap: normal !important;
            word-break: normal !important;
            color: #111827 !important;
            line-height: 1.05 !important;
        }

        .subject-name {
            display: block;
            font-size: 9px;
            font-weight: 700 !important;
            line-height: 1.05;
            white-space: nowrap !important;
            overflow-wrap: normal !important;
            word-break: normal !important;
        }

        .subject-max {
            display: block;
            font-size: 7px;
            font-weight: 700 !important;
            line-height: 7.5px;
            white-space: nowrap !important;
            overflow-wrap: normal !important;
            word-break: normal !important;
        }

        .optional-label {
            display: block;
            font-size: 6px;
            line-height: 6.5px;
            font-weight: 700;
            white-space: nowrap !important;
        }

        .optional-mark {
            color: #92400e !important;
            font-weight: 700 !important;
        }

        .absent-mark,
        .fail-result {
            color: #991b1b !important;
            font-weight: 700 !important;
        }

        .pass-result {
            color: #166534 !important;
            font-weight: 700 !important;
        }

        .pending-result {
            color: #92400e !important;
            font-weight: 700 !important;
        }

        .analysis-block {
            width: 100%;
            max-width: 100%;
            margin-top: 7px;
            overflow: hidden;
        }

        .analysis-title {
            font-size: 12px;
            font-weight: 700 !important;
            color: #1d4ed8;
            margin: 5px 0 3px;
            white-space: nowrap;
        }

        .analysis-table,
        .subject-wise-analysis-table {
            width: 100%;
            max-width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            table-layout: auto;
            border: 1px solid #333;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5px;
            min-width: 0;
        }

        .analysis-table th,
        .analysis-table td,
        .subject-wise-analysis-table th,
        .subject-wise-analysis-table td {
            font-size: 9.5px;
            padding: 2px 2px;
            white-space: nowrap !important;
            min-width: 0;
        }

        .analysis-table th,
        .subject-wise-analysis-table th {
            background: #dbeafe;
            font-weight: 700 !important;
        }

        .analysis-table td:first-child,
        .subject-analysis-name {
            text-align: left !important;
            font-weight: 700 !important;
            white-space: nowrap !important;
            overflow-wrap: normal !important;
            word-break: normal !important;
        }

        .subject-wise-analysis-table td:first-child {
            text-align: left !important;
            font-weight: 700 !important;
            white-space: nowrap !important;
            overflow-wrap: normal !important;
            word-break: normal !important;
        }

        .analysis-total-row,
        .analysis-total-row td {
            font-weight: 700 !important;
            background: #e5e7eb !important;
        }

        .signature-area {
            width: 100%;
            max-width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 10px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .signature-box {
            width: 200px;
            text-align: center;
            font-size: 10px;
            font-weight: 700;
            page-break-inside: avoid;
        }

        .signature-line {
            display: block;
            border-bottom: 1px solid #111827;
            width: 100%;
            height: 16px;
            margin-bottom: 3px;
        }

        .signature-name {
            display: block;
            font-size: 10px;
            font-weight: 700;
            line-height: 11px;
            text-transform: uppercase;
            white-space: nowrap !important;
            overflow: visible;
            overflow-wrap: normal;
            word-break: normal;
        }

        .signature-designation {
            display: block;
            margin-top: 7px;
            font-size: 9px;
            font-weight: 700;
            white-space: nowrap;
        }

        @media screen {
            .print-page {
                padding: 10px;
            }
        }

    </style>
</head>

<body>

<div class="print-page">

    <div class="print-controls no-print">

        <button
            type="button"
            class="print-button"
            onclick="window.print()"
        >
            Print / Save PDF
        </button>

        <a
            href="{{ route('result-sheet.export-excel', [
                'academic_year_id' => request('academic_year_id'),
                'exam_master_id' => request('exam_master_id'),
                'division_id' => request('division_id')
            ]) }}"
            class="excel-button"
        >
            Save as Excel
        </a>

        <button
            type="button"
            class="close-button"
            onclick="window.close()"
        >
            Close
        </button>

    </div>

@php

use App\Helpers\ResultSheetBladeHelper;

$academicDisplayColumns =
    collect(
        $displayColumns ?? []
    )->values();

$rawResults =
    collect(
        $results ?? []
    );

$printStandardId =
    (int) (
        $standard->id
        ?? 0
    );

$isSeniorOptionalStandard =
    ResultSheetBladeHelper::isSeniorOptionalStandard(
        $printStandardId
    );

$printPassPercentage =
    isset($passPercentage)
        ? (float) $passPercentage
        : (float) \App\Helpers\MarksHelper::getPassingPercentage(
            $printStandardId
        );

$sortedResults =
    ResultSheetBladeHelper::getUniqueSortedStudents(
        $rawResults
    );

$studentCalculations =
    ResultSheetBladeHelper::buildStudentCalculations(
        $sortedResults,
        $academicDisplayColumns,
        $printStandardId,
        (int) $printPassPercentage
    );

$displayTotalMaxMarks =
    ResultSheetBladeHelper::calculateDisplayTotalMaxMarks(
        $academicDisplayColumns,
        $printStandardId
    );

$isSeniorOptionalColumn =
    fn ($column) =>
        ResultSheetBladeHelper::isSeniorOptionalColumn(
            $column,
            $printStandardId
        );

$isSeniorCompulsoryColumn =
    fn ($column) =>
        ResultSheetBladeHelper::isSeniorCompulsoryColumn(
            $column,
            $printStandardId
        );

$displayNumber =
    fn ($value) =>
        ResultSheetBladeHelper::displayNumber(
            $value
        );

$rawClassTeacherName =
    ResultSheetBladeHelper::extractStaffName(
        $classTeacher ?? null
    );

if (
    $rawClassTeacherName === ''
    && isset($classTeacherName)
) {
    $rawClassTeacherName =
        trim(
            (string) $classTeacherName
        );
}

$classTeacherName =
    ResultSheetBladeHelper::formatStaffName(
        $rawClassTeacherName
    );

$rawPrincipalName =
    ResultSheetBladeHelper::extractStaffName(
        $principal ?? null
    );

if (
    $rawPrincipalName === ''
    && isset($principalName)
) {
    $rawPrincipalName =
        trim(
            (string) $principalName
        );
}

$principalName =
    ResultSheetBladeHelper::formatStaffName(
        $rawPrincipalName
    );

$overallRanges = [
    'A1' => '91-100%',
    'A2' => '81-90%',
    'B1' => '71-80%',
    'B2' => '61-70%',
    'C1' => '51-60%',
    'C2' => '41-50%',
    'D'  => '33-40%',
    'E1' => '21-32%',
    'E2' => '1-20%'
];

$overallGradeAnalysis = [];

foreach (
    $overallRanges as $grade => $range
) {
    $overallGradeAnalysis[$grade] = [
        'range' => $range,
        'girls' => 0,
        'boys' => 0,
        'total' => 0
    ];
}

$overallGradeAnalysis['TOTAL'] = [
    'range' => 'TOTAL',
    'girls' => 0,
    'boys' => 0,
    'total' => $sortedResults->count()
];

foreach ($sortedResults as $student) {

    $calc =
        $studentCalculations[
            ResultSheetBladeHelper::getStudentKey(
                $student
            )
        ]
        ?? null;

    if (!$calc) {
        continue;
    }

    $gender =
        strtoupper(
            trim(
                (string) (
                    $student->gender
                    ?? $student->sex
                    ?? ''
                )
            )
        );

    $genderKey =
        in_array(
            $gender,
            [
                'F',
                'FEMALE',
                'GIRL',
                'GIRLS'
            ],
            true
        )
        ? 'girls'
        : 'boys';

    $grade =
        strtoupper(
            trim(
                (string) $calc['grade']
            )
        );

    if (
        isset(
            $overallGradeAnalysis[$grade]
        )
    ) {
        $overallGradeAnalysis[$grade][$genderKey]++;
        $overallGradeAnalysis[$grade]['total']++;
    }

    $overallGradeAnalysis['TOTAL'][$genderKey]++;
}

$subjectAnalysis =
    ResultSheetBladeHelper::calculateSubjectAnalysis(
        $sortedResults,
        $academicDisplayColumns,
        $studentCalculations,
        false
    );

$schoolCode =
    session(
        'school_code',
        'shirgaon'
    );

@endphp


<table class="school-header">
    <tr>

        <td class="school-logo-cell">

            <img
                src="{{ asset('images/school-logo.png') }}"
                class="school-logo"
                alt="School Logo"
            >

        </td>

        <td class="school-title-cell">

            @if($schoolCode === 'chikhali')

                <div class="school-name">
                    PRAJNANABODHINI ENGLISH MEDIUM SCHOOL CHIKHALI
                </div>

                <div class="school-year">
                    Academic Year :
                    {{ $academicYear->year_name ?? '' }}
                </div>

            @else

                <div class="school-name">
                    PRAJNANABODHINI ENGLISH MEDIUM SCHOOL &amp; JR. COLLEGE
                </div>

                <div class="school-location">
                    SHIRGAON
                </div>

                <div class="school-year">
                    Academic Year :
                    {{ $academicYear->year_name ?? '' }}
                </div>

            @endif

        </td>

        <td style="width:60px;border:none!important;"></td>

    </tr>
</table>


<table class="exam-info">
    <tr>

        <td>
            <strong>Exam :</strong>
            {{
                $exam->display_exam_name
                ?? $exam->exam_name
                ?? ''
            }}
        </td>

        <td>
            <strong>Standard :</strong>
            {{
                $standard->standard_name
                ?? ''
            }}
        </td>

        <td>
            <strong>Division :</strong>
            {{
                $division->division_name
                ?? ''
            }}
        </td>

        <td>
            <strong>Class Teacher :</strong>
            {{
                $classTeacherName
                ?: '-'
            }}
        </td>

        <td>
            <strong>Principal :</strong>
            {{
                $principalName
                ?: '-'
            }}
        </td>

        <td>
            <strong>Total Students :</strong>
            {{
                $sortedResults->count()
            }}
        </td>

    </tr>
</table>


<div class="result-sheet-wrapper">

    <table class="result-sheet-table">

        <thead>

            <tr>

                <th>Roll No</th>

                <th>Student Name</th>

                @foreach(
                    $academicDisplayColumns
                    as $column
                )

                    @php

                    $subjectMax =
                        (float) (
                            $column->max_marks
                            ?? 0
                        );

                    $subjectPassing =
                        $subjectMax > 0
                            ? \App\Helpers\MarksHelper::getPassingMarks(
                                $printStandardId,
                                $subjectMax
                            )
                            : 0;

                    $subjectIsOptional =
                        $isSeniorOptionalStandard
                            ? $isSeniorOptionalColumn(
                                $column
                            )
                            : (
                                (int) (
                                    $column->is_optional
                                    ?? 0
                                ) === 1
                            );

                    @endphp

                    <th colspan="2">

                        <span class="subject-name">
                            {{
                                $column->subject_name
                                ??
                                $column->subject_code
                                ??
                                '-'
                            }}
                        </span>

                        @if($subjectIsOptional)

                            <span class="optional-label">
                                Optional Subject
                            </span>

                        @endif

                        <span class="subject-max">
                            Max =
                            {{ $displayNumber($subjectMax) }}
                        </span>

                        <span class="subject-max">
                            Pass =
                            {{ $displayNumber($subjectPassing) }}
                        </span>

                    </th>

                @endforeach

                <th>
                    Total

                    <span class="subject-max">
                        Max =
                        {{ $displayNumber($displayTotalMaxMarks) }}
                    </span>
                </th>

                <th>
                    Per. %
                </th>

                <th>
                    Grade
                </th>

                <th>
                    Result
                </th>

            </tr>


            <tr>

                <th></th>

                <th></th>

                @foreach(
                    $academicDisplayColumns
                    as $column
                )

                    <th>Marks</th>

                    <th>Grade</th>

                @endforeach

                <th></th>
                <th></th>
                <th></th>
                <th></th>

            </tr>

        </thead>


        <tbody>

            @forelse(
                $sortedResults
                as $student
            )

                @php

                $studentCalc =
                    $studentCalculations[
                        ResultSheetBladeHelper::getStudentKey(
                            $student
                        )
                    ]
                    ?? null;

                @endphp

                @if($studentCalc)

                    <tr>

                        <td>
                            {{
                                $student->roll_no
                                ?: '-'
                            }}
                        </td>

                        <td
                            class="student-name-cell"
                            title="{{ $student->full_student_name ?? '' }}"
                        >
                            {{
                                $student->full_student_name
                                ?: '-'
                            }}
                        </td>


                        @foreach(
                            $studentCalc['subject_details']
                            as $detail
                        )

                            @php

                            $mark =
                                $detail['mark']
                                ?? '-';

                            $grade =
                                $detail['grade']
                                ?? '-';

                            $isOptional =
                                !empty(
                                    $detail['optional']
                                );

                            @endphp


                            <td>

                                @if($isOptional)

                                    <span class="optional-mark">
                                        OPT
                                    </span>

                                @elseif(
                                    strtoupper(
                                        trim(
                                            (string) $mark
                                        )
                                    ) === 'AB'
                                )

                                    <span class="absent-mark">
                                        AB
                                    </span>

                                @elseif($mark === '-')

                                    -

                                @elseif(is_numeric($mark))

                                    {{
                                        $displayNumber(
                                            $mark
                                        )
                                    }}

                                @else

                                    {{ $mark }}

                                @endif

                            </td>


                            <td>

                                @if($isOptional)

                                    <span class="optional-mark">
                                        OPT
                                    </span>

                                @elseif(
                                    strtoupper(
                                        trim(
                                            (string) $grade
                                        )
                                    ) === 'AB'
                                )

                                    <span class="absent-mark">
                                        AB
                                    </span>

                                @elseif($grade === '-')

                                    -

                                @else

                                    {{ $grade }}

                                @endif

                            </td>

                        @endforeach


                        <td>
                            {{
                                $displayNumber(
                                    $studentCalc['total_marks']
                                )
                            }}
                        </td>


                        <td>

                            {{
                                $studentCalc['percentage']
                                === null
                                    ? '-'
                                    : (
                                        (int) round(
                                            $studentCalc['percentage']
                                        )
                                        . '%'
                                    )
                            }}

                        </td>


                        <td>
                            {{ $studentCalc['grade'] }}
                        </td>


                        <td>

                            @if(
                                $studentCalc['result']
                                === 'PASS'
                            )

                                <span class="pass-result">
                                    PASS
                                </span>

                            @elseif(
                                $studentCalc['result']
                                === 'FAIL'
                            )

                                <span class="fail-result">
                                    FAIL
                                </span>

                            @else

                                <span class="pending-result">
                                    PENDING
                                </span>

                            @endif

                        </td>

                    </tr>

                @endif

            @empty

                <tr>

                    <td
                        colspan="{{
                            2
                            +
                            (
                                $academicDisplayColumns->count()
                                * 2
                            )
                            +
                            4
                        }}"
                        style="
                            padding:10px;
                            text-align:center;
                            font-weight:700;
                        "
                    >
                        No result records found.
                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>

</div>


<div class="analysis-block">

    <div class="analysis-title">
        Overall Grade / Result Analysis
    </div>


    <table class="analysis-table">

        <thead>

            <tr>

                <th>Grade / Result</th>
                <th>Range</th>
                <th>Girls</th>
                <th>Boys</th>
                <th>Total</th>

            </tr>

        </thead>


        <tbody>

            @foreach(
                [
                    'A1',
                    'A2',
                    'B1',
                    'B2',
                    'C1',
                    'C2',
                    'D',
                    'E1',
                    'E2'
                ]
                as $grade
            )

                @php

                $row =
                    $overallGradeAnalysis[
                        $grade
                    ]
                    ??
                    [
                        'range' => '-',
                        'girls' => 0,
                        'boys' => 0,
                        'total' => 0
                    ];

                @endphp


                <tr>

                    <td>{{ $grade }}</td>

                    <td>
                        {{ $row['range'] ?? '-' }}
                    </td>

                    <td>
                        {{ $row['girls'] ?? 0 }}
                    </td>

                    <td>
                        {{ $row['boys'] ?? 0 }}
                    </td>

                    <td>
                        {{ $row['total'] ?? 0 }}
                    </td>

                </tr>

            @endforeach


            <tr class="analysis-total-row">

                <td>TOTAL</td>

                <td>TOTAL</td>

                <td>
                    {{
                        $overallGradeAnalysis[
                            'TOTAL'
                        ]['girls']
                        ?? 0
                    }}
                </td>

                <td>
                    {{
                        $overallGradeAnalysis[
                            'TOTAL'
                        ]['boys']
                        ?? 0
                    }}
                </td>

                <td>
                    {{
                        $overallGradeAnalysis[
                            'TOTAL'
                        ]['total']
                        ?? 0
                    }}
                </td>

            </tr>

        </tbody>

    </table>

</div>


<div class="analysis-block">

    <div class="analysis-title">
        Subject Wise Analysis
    </div>


    <table class="subject-wise-analysis-table">

        <thead>

            <tr>

                <th rowspan="2">
                    Subject
                </th>

                @foreach(
                    [
                        'A1',
                        'A2',
                        'B1',
                        'B2',
                        'C1',
                        'C2',
                        'D',
                        'E1',
                        'E2',
                        'ABSENT'
                    ]
                    as $category
                )

                    <th colspan="2">
                        {{ $category }}
                    </th>

                @endforeach

            </tr>


            <tr>

                @for(
                    $i = 0;
                    $i < 10;
                    $i++
                )

                    <th>Girls</th>

                    <th>Boys</th>

                @endfor

            </tr>

        </thead>


        <tbody>

            @foreach(
                $academicDisplayColumns
                as $column
            )

                @php

                $subjectCode =
                    trim(
                        (string) (
                            $column->subject_code
                            ??
                            $column->subject_id
                            ??
                            $column->subject_name
                            ??
                            ''
                        )
                    );

                $analysis =
                    $subjectAnalysis[
                        $subjectCode
                    ]
                    ?? [];

                @endphp


                <tr>

                    <td class="subject-analysis-name">

                        {{
                            $column->subject_name
                            ??
                            $subjectCode
                            ??
                            '-'
                        }}

                    </td>


                    @foreach(
                        [
                            'A1',
                            'A2',
                            'B1',
                            'B2',
                            'C1',
                            'C2',
                            'D',
                            'E1',
                            'E2'
                        ]
                        as $category
                    )

                        <td>

                            {{
                                $analysis[
                                    $category . '_girls'
                                ]
                                ?? 0
                            }}

                        </td>


                        <td>

                            {{
                                $analysis[
                                    $category . '_boys'
                                ]
                                ?? 0
                            }}

                        </td>

                    @endforeach


                    <td>

                        {{
                            $analysis[
                                'absent_girls'
                            ]
                            ?? 0
                        }}

                    </td>


                    <td>

                        {{
                            $analysis[
                                'absent_boys'
                            ]
                            ?? 0
                        }}

                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>

</div>


<div class="signature-area">

    <div class="signature-box">

        <span class="signature-line"></span>

        <span
            class="signature-name"
            style="margin-top:12px;"
        >
            {{
                $classTeacherName
                ?: '-'
            }}
        </span>

        <span class="signature-designation">
            CLASS TEACHER
        </span>

    </div>


    <div class="signature-box">

        <span class="signature-line"></span>

        <span
            class="signature-name"
            style="margin-top:12px;"
        >
            {{
                $principalName
                ?: '-'
            }}
        </span>

        <span class="signature-designation">
            PRINCIPAL
        </span>

    </div>

</div>


</div>

</body>
</html>

