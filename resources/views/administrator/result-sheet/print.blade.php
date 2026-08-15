<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">

<title>Examination Result Sheet</title>

<style>

@page {
    size: A4 landscape;
    margin: 5mm;
}

* {
    box-sizing: border-box;
}

html,
body {
    width: 100%;
    margin: 0;
    padding: 0;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 9px;
    color: #111827;
    background: #fff;
}

/* =========================================================
   PRINT
========================================================= */

@media print {

    html,
    body {
        width: 100%;
        margin: 0;
        padding: 0;
    }

    .no-print {
        display: none !important;
    }

    thead {
        display: table-header-group;
    }

    tfoot {
        display: table-footer-group;
    }

    .result-sheet-table {
        page-break-inside: auto !important;
    }

    .result-sheet-table tr {
        page-break-inside: avoid !important;
        page-break-after: auto !important;
    }

}

/* =========================================================
   COMMON
========================================================= */

table,
table th,
table td {
    font-family: Arial, Helvetica, sans-serif !important;
    color: #111827 !important;
}

table {
    width: 100%;
    border-collapse: collapse;
}

/* =========================================================
   SCHOOL HEADER
========================================================= */

.school-header {
    width: 100%;
    border: none !important;
    margin: 0 auto 2px auto;
    table-layout: fixed;
}

.school-header td {
    border: none !important;
    padding: 1px 2px;
    vertical-align: middle;
}

.school-logo-cell {
    width: 80px;
    text-align: center;
}

.school-logo {
    width: 60px;
    height: 60px;
    object-fit: contain;
}

.school-title-cell {
    text-align: center !important;
}

.school-name {
    font-size: 17px;
    font-weight: 700;
    line-height: 20px;
    text-align: center !important;
}

.school-location {
    font-size: 10px;
    font-weight: 700;
    line-height: 18px;
    text-align: center !important;
}

.academic-year {
    font-size: 12px;
    font-weight: 500;
    line-height: 16px;
    text-align: center !important;
}

/* =========================================================
   EXAM INFORMATION
========================================================= */

.exam-info {
    width: 100%;
    border: none !important;
    margin-bottom: 4px;
    table-layout: fixed;
}

.exam-info td {
    border: none !important;
    font-size: 12px;
    text-align: left;
    padding: 2px 4px;
}

/* =========================================================
   RESULT TABLE
========================================================= */

.result-sheet-table {
    width: 100%;
    border-collapse: collapse !important;
    border-spacing: 0 !important;
    table-layout: fixed;
    font-size: 9px;
}

.result-sheet-table th,
.result-sheet-table td {
    border: 0.2px solid #000 !important;
    color: #03070f !important;
    text-align: center;
    vertical-align: middle;
}

.result-sheet-table th {
    background: #dbeafe;
    font-weight: 700;
    font-size: 8.5px;
    padding: 1px;
    line-height: 9px;
    white-space: normal;
}

.result-sheet-table td {
    font-size: 8.5px;
    font-weight: 500;
    padding: 3px 2px;
    line-height: 10px;
    white-space: nowrap;
}

/* =========================================================
   FIXED COLUMNS
========================================================= */

.result-sheet-table th.roll-column,
.result-sheet-table td.roll-column {
    width: 38px;
    font-size: 8.5px;
}

.result-sheet-table th.student-name-header,
.result-sheet-table td.student-name {
    width: 145px;
    min-width: 145px;
    max-width: 145px;
    text-align: left !important;
}

.result-sheet-table td.student-name {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* =========================================================
   NORMAL SUBJECT
========================================================= */

.normal-mark-column {
    width: 30px;
}

.normal-grade-column {
    width: 30px;
}

/* =========================================================
   COMPONENT
========================================================= */

.component-column {
    width: 32px;
}

/* =========================================================
   SKILL
========================================================= */

.skill-column {
    width: 70px;
}

/* =========================================================
   TOTAL / % / GRADE / RESULT
========================================================= */

.total-column {
    width: 50px;
}

.percentage-column {
    width: 43px;
}

.grade-column {
    width: 42px;
}

.result-column {
    width: 48px;
}

/* =========================================================
   SUBJECT HEADER
========================================================= */

.subject-max {
    display: block;
    font-size: 6.5px;
    font-weight: 500;
    line-height: 7px;
}

.component-max {
    display: block;
    font-size: 6.5px;
    font-weight: 500;
    line-height: 7px;
}

.component-name {
    font-size: 7.5px;
    font-weight: 700;
    line-height: 8px;
}

.group-header {
    font-weight: 700;
    font-size: 8px;
}

/* =========================================================
   MARKS
========================================================= */

.absent-mark {
    font-weight: 700;
    color: #991b1b !important;
}

.fail-result {
    font-weight: 700;
    color: #991b1b !important;
}

.pass-result {
    font-weight: 700;
    color: #166534 !important;
}

/* =========================================================
   ANALYSIS
========================================================= */

.analysis-title {
    margin-top: 15px;
    margin-bottom: 4px;
    font-size: 12px;
    font-weight: 700;
}

.analysis-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    font-size: 10px;
}

.analysis-table th,
.analysis-table td {
    border: 1px solid #111827 !important;
    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 10px;
    padding: 3px;
    text-align: center;
    vertical-align: middle;
}

.analysis-table th {
    background: #dbeafe;
    font-weight: 700;
}

.analysis-table td {
    font-weight: 600;
}

.analysis-subject {
    text-align: left !important;
    width: 110px;
}

.analysis-subject-name {
    width: 110px;
    text-align: left !important;
    white-space: nowrap;
}

/* =========================================================
   COMBINED ANALYSIS
========================================================= */

.combined-analysis-table {
    width: 100%;
    table-layout: fixed;
    font-size: 10px;
}

.combined-analysis-table th,
.combined-analysis-table td {
    text-align: center;
    padding: 2px 3px;
    font-size: 10px;
    color: #000 !important;
    border: 1px solid #111827 !important;
}

.combined-analysis-table .analysis-subject-column {
    width: 105px;
}

.combined-analysis-table .analysis-subject-name {
    width: 105px;
    text-align: left !important;
    white-space: nowrap;
    font-weight: 600;
}

.gender-header {
    font-size: 10px;
    font-weight: 700;
}

/* =========================================================
   SIGNATURE
========================================================= */

.signature-area {
    margin-top: 22px;
    display: flex;
    justify-content: space-between;
    width: 100%;
}

.signature-box {
    width: 180px;
    text-align: center;
    font-size: 14px;
}

/* =========================================================
   PRINT BUTTON
========================================================= */

.no-print {
    position: fixed;
    top: 10px;
    right: 10px;
    z-index: 9999;
}

</style>

</head>

<body>

<div class="no-print">

    <button type="button"
            onclick="window.print()"
            style="
                padding:6px 14px;
                cursor:pointer;
                font-weight:bold;
            ">
        Print
    </button>

</div>


{{-- =========================================================
     SCHOOL HEADER
========================================================= --}}

<table class="school-header">

    <tr>

        <td class="school-logo-cell">

            <img src="{{ asset('images/school-logo.png') }}"
                 class="school-logo">

        </td>

        <td class="school-title-cell">

            <div class="school-name">
                PRAJNANABODHINI ENGLISH MEDIUM SCHOOL &amp; JR. COLLEGE
            </div>

            <div class="school-location">
                SHIRGAON / CHIKHALI
            </div>

            <div class="academic-year">
                Academic Year : {{ $yearName ?? '' }}
            </div>

        </td>

        <td style="width:80px;border:none !important;"></td>

    </tr>

</table>


{{-- =========================================================
     EXAM INFORMATION
========================================================= --}}

<table class="exam-info">

    <tr>

        <td>
            <strong>Academic Year :</strong>
            {{ $yearName ?? '' }}
        </td>

        <td>
            <strong>Exam :</strong>
            {{ $exam->exam_name ?? '' }}
        </td>

        <td>
            <strong>Standard :</strong>
            {{ $standard->standard_name ?? '' }}
        </td>

        <td>
            <strong>Division :</strong>
            {{ $division->division_name ?? '' }}
        </td>

        <td>
            <strong>Total Students :</strong>
            {{ $results->count() }}
        </td>

    </tr>

</table>


{{-- =========================================================
     SUBJECT PREPARATION
========================================================= --}}

@php

$academicSubjects = collect($academicSubjects ?? []);
$examSubjects     = collect($examSubjects ?? []);
$coSubjects       = collect($coSubjects ?? []);


/*
|--------------------------------------------------------------------------
| NORMALIZE SUBJECT NAME
|--------------------------------------------------------------------------
*/

$subjectNameUpper = function ($subject) {

    return strtoupper(
        trim(
            $subject->subject_name ?? ''
        )
    );

};


/*
|--------------------------------------------------------------------------
| FIND COMPONENT SUBJECTS
|--------------------------------------------------------------------------
*/

$math1Subject = $academicSubjects->first(
    function ($subject) use ($subjectNameUpper) {

        return in_array(
            $subjectNameUpper($subject),
            [
                'MATHEMATICS I',
                'MATHEMATICS 1',
                'MATH I',
                'MATH 1'
            ],
            true
        );

    }
);

$math2Subject = $academicSubjects->first(
    function ($subject) use ($subjectNameUpper) {

        return in_array(
            $subjectNameUpper($subject),
            [
                'MATHEMATICS II',
                'MATHEMATICS 2',
                'MATH II',
                'MATH 2'
            ],
            true
        );

    }
);

$sci1Subject = $academicSubjects->first(
    function ($subject) use ($subjectNameUpper) {

        return in_array(
            $subjectNameUpper($subject),
            [
                'SCIENCE I',
                'SCIENCE 1',
                'SCI I',
                'SCI 1'
            ],
            true
        );

    }
);

$sci2Subject = $academicSubjects->first(
    function ($subject) use ($subjectNameUpper) {

        return in_array(
            $subjectNameUpper($subject),
            [
                'SCIENCE II',
                'SCIENCE 2',
                'SCI II',
                'SCI 2'
            ],
            true
        );

    }
);

$historySubject = $academicSubjects->first(
    function ($subject) use ($subjectNameUpper) {

        return $subjectNameUpper($subject) === 'HISTORY';

    }
);

$geographySubject = $academicSubjects->first(
    function ($subject) use ($subjectNameUpper) {

        return $subjectNameUpper($subject) === 'GEOGRAPHY';

    }
);


/*
|--------------------------------------------------------------------------
| COMPONENT IDS
|--------------------------------------------------------------------------
*/

$math1Id = $math1Subject->id ?? null;
$math2Id = $math2Subject->id ?? null;

$sci1Id = $sci1Subject->id ?? null;
$sci2Id = $sci2Subject->id ?? null;

$historyId = $historySubject->id ?? null;
$geographyId = $geographySubject->id ?? null;


/*
|--------------------------------------------------------------------------
| MAX MARKS
|--------------------------------------------------------------------------
*/

$math1Max = (int)($math1Subject->max_marks ?? 0);
$math2Max = (int)($math2Subject->max_marks ?? 0);

$sci1Max = (int)($sci1Subject->max_marks ?? 0);
$sci2Max = (int)($sci2Subject->max_marks ?? 0);

$historyMax = (int)($historySubject->max_marks ?? 0);
$geographyMax = (int)($geographySubject->max_marks ?? 0);


/*
|--------------------------------------------------------------------------
| FALLBACK MAX MARKS
|--------------------------------------------------------------------------
*/

if ($math1Id && $math1Max === 0) {

    $math1Max = (int)DB::table('exam_master_subjects')
        ->where('exam_master_id', $exam->id ?? 0)
        ->where('subject_id', $math1Id)
        ->value('max_marks');

}

if ($math2Id && $math2Max === 0) {

    $math2Max = (int)DB::table('exam_master_subjects')
        ->where('exam_master_id', $exam->id ?? 0)
        ->where('subject_id', $math2Id)
        ->value('max_marks');

}

if ($sci1Id && $sci1Max === 0) {

    $sci1Max = (int)DB::table('exam_master_subjects')
        ->where('exam_master_id', $exam->id ?? 0)
        ->where('subject_id', $sci1Id)
        ->value('max_marks');

}

if ($sci2Id && $sci2Max === 0) {

    $sci2Max = (int)DB::table('exam_master_subjects')
        ->where('exam_master_id', $exam->id ?? 0)
        ->where('subject_id', $sci2Id)
        ->value('max_marks');

}

if ($historyId && $historyMax === 0) {

    $historyMax = (int)DB::table('exam_master_subjects')
        ->where('exam_master_id', $exam->id ?? 0)
        ->where('subject_id', $historyId)
        ->value('max_marks');

}

if ($geographyId && $geographyMax === 0) {

    $geographyMax = (int)DB::table('exam_master_subjects')
        ->where('exam_master_id', $exam->id ?? 0)
        ->where('subject_id', $geographyId)
        ->value('max_marks');

}


/*
|--------------------------------------------------------------------------
| COMPONENT IDS
|--------------------------------------------------------------------------
*/

$componentIds = array_filter([
    $math1Id,
    $math2Id,
    $sci1Id,
    $sci2Id,
    $historyId,
    $geographyId
]);


/*
|--------------------------------------------------------------------------
| NORMAL SUBJECTS
|--------------------------------------------------------------------------
*/

$normalSubjects = $academicSubjects
    ->filter(function ($subject) use ($componentIds) {

        return !in_array(
            $subject->id,
            $componentIds,
            true
        );

    })
    ->values();


/*
|--------------------------------------------------------------------------
| SUBJECT ORDER
|--------------------------------------------------------------------------
*/

$subjectOrder = [

    'ENG'       => 1,
    'ENGLISH'   => 1,

    'SAN'       => 2,
    'SANSKRIT'  => 2,

    'MAR'       => 3,
    'MARATHI'   => 3,

];


/*
|--------------------------------------------------------------------------
| SORT NORMAL SUBJECTS
|--------------------------------------------------------------------------
*/

$normalSubjects = $normalSubjects
    ->sortBy(function ($subject) use ($subjectOrder) {

        $code = strtoupper(
            trim(
                $subject->short_name
                ?? $subject->subject_name
                ?? ''
            )
        );

        return $subjectOrder[$code] ?? 999;

    })
    ->values();


/*
|--------------------------------------------------------------------------
| GROUP FLAGS
|--------------------------------------------------------------------------
*/

$showMathGroup =
    $math1Id !== null ||
    $math2Id !== null;

$showScienceGroup =
    $sci1Id !== null ||
    $sci2Id !== null;

$showSocialGroup =
    $historyId !== null ||
    $geographyId !== null;


/*
|--------------------------------------------------------------------------
| TOTAL MAX MARKS
|--------------------------------------------------------------------------
*/

$totalMaxMarks = (int)($totalMaxMarks ?? 0);


/*
|--------------------------------------------------------------------------
| OVERALL GRADE FUNCTION
|--------------------------------------------------------------------------
|
| IMPORTANT:
| Grade is calculated from attempted subjects only.
|
*/

$calculateOverallGrade = function ($percentage) {

    if ($percentage === null) {
        return '-';
    }

    $percentage = (float)$percentage;

    if ($percentage >= 91) {
        return 'A1';
    }

    if ($percentage >= 81) {
        return 'A2';
    }

    if ($percentage >= 71) {
        return 'B1';
    }

    if ($percentage >= 61) {
        return 'B2';
    }

    if ($percentage >= 51) {
        return 'C1';
    }

    if ($percentage >= 41) {
        return 'C2';
    }

    return 'D';
};


/*
|--------------------------------------------------------------------------
| GET DETAIL MARK
|--------------------------------------------------------------------------
*/

$getDetailMark = function ($detail) {

    if (!$detail) {
        return null;
    }

    $mark = $detail['marks'] ?? null;

    if ($mark === null || $mark === '') {
        return null;
    }

    if (strtoupper(trim((string)$mark)) === 'AB') {
        return null;
    }

    if (!is_numeric($mark)) {
        return null;
    }

    return (float)$mark;
};


/*
|--------------------------------------------------------------------------
| IS ABSENT DETAIL
|--------------------------------------------------------------------------
*/

$isDetailAbsent = function ($detail) {

    if (!$detail) {
        return true;
    }

    $mark = $detail['marks'] ?? null;

    return
        ($detail['is_absent'] ?? 0) == 1
        ||
        strtoupper(trim((string)$mark)) === 'AB'
        ||
        strtoupper(trim((string)($detail['result'] ?? ''))) === 'ABSENT';

};


/*
|--------------------------------------------------------------------------
| IS DETAIL FAILED
|--------------------------------------------------------------------------
*/

$isDetailFailed = function ($detail) use ($isDetailAbsent) {

    if (!$detail) {
        return false;
    }

    if ($isDetailAbsent($detail)) {
        return false;
    }

    $result = strtoupper(
        trim(
            (string)($detail['result'] ?? '')
        )
    );

    $grade = strtoupper(
        trim(
            (string)($detail['grade'] ?? '')
        )
    );

    /*
     * Existing generated result has priority.
     */

    if ($result === 'FAIL') {
        return true;
    }

    /*
     * Grade F is also treated as failed.
     */

    if ($grade === 'F') {
        return true;
    }

    /*
     * If there is no result/grade, use pass_mark when available.
     */

    $mark = $getMark = $detail['marks'] ?? null;

    if (
        is_numeric($mark)
        &&
        isset($detail['pass_marks'])
        &&
        is_numeric($detail['pass_marks'])
    ) {

        return
            (float)$mark <
            (float)$detail['pass_marks'];

    }

    return false;

};


/*
|--------------------------------------------------------------------------
| CALCULATE STUDENT OVERALL RESULT
|--------------------------------------------------------------------------
|
| RULE:
|
| 1. AB is ignored for total and percentage.
| 2. Only present subjects contribute to total.
| 3. Maximum marks is only for attempted subjects.
| 4. All subjects AB => ABSENT.
| 5. If at least one subject is present:
|       percentage = obtained / attempted_max * 100
| 6. If any attempted subject is failed => FAIL.
| 7. Otherwise => PASS.
|
*/

$calculateStudentOverall = function (
    $student
) use (
    $normalSubjects,
    $math1Id,
    $math2Id,
    $sci1Id,
    $sci2Id,
    $historyId,
    $geographyId,
    $math1Max,
    $math2Max,
    $sci1Max,
    $sci2Max,
    $historyMax,
    $geographyMax,
    $isDetailAbsent,
    $isDetailFailed,
    $calculateOverallGrade
) {

    $details = $student->details ?? [];

    $totalObtained = 0;
    $attemptedMax = 0;
    $presentCount = 0;
    $failedCount = 0;


    /*
     * --------------------------------------------------------
     * NORMAL SUBJECTS
     * --------------------------------------------------------
     */

    foreach ($normalSubjects as $subject) {

        $detail =
            $details[$subject->id] ?? null;

        if (!$detail) {
            continue;
        }

        if ($isDetailAbsent($detail)) {
            continue;
        }

        $mark = $detail['marks'] ?? null;

        if (!is_numeric($mark)) {
            continue;
        }

        $maxMark = (int)($subject->max_marks ?? 0);

        if ($maxMark <= 0) {
            continue;
        }

        $totalObtained += (float)$mark;

        $attemptedMax += $maxMark;

        $presentCount++;


        if ($isDetailFailed($detail)) {
            $failedCount++;
        }

    }


    /*
     * --------------------------------------------------------
     * COMPONENT SUBJECTS
     * --------------------------------------------------------
     */

    $componentSubjects = [

        [
            'id'  => $math1Id,
            'max' => $math1Max
        ],

        [
            'id'  => $math2Id,
            'max' => $math2Max
        ],

        [
            'id'  => $sci1Id,
            'max' => $sci1Max
        ],

        [
            'id'  => $sci2Id,
            'max' => $sci2Max
        ],

        [
            'id'  => $historyId,
            'max' => $historyMax
        ],

        [
            'id'  => $geographyId,
            'max' => $geographyMax
        ]

    ];


    foreach ($componentSubjects as $component) {

        $subjectId = $component['id'];
        $maxMark   = (int)$component['max'];

        if (!$subjectId || $maxMark <= 0) {
            continue;
        }

        $detail =
            $details[$subjectId] ?? null;

        if (!$detail) {
            continue;
        }

        if ($isDetailAbsent($detail)) {
            continue;
        }

        $mark = $detail['marks'] ?? null;

        if (!is_numeric($mark)) {
            continue;
        }

        $totalObtained += (float)$mark;

        $attemptedMax += $maxMark;

        $presentCount++;


        if ($isDetailFailed($detail)) {
            $failedCount++;
        }

    }


    /*
     * --------------------------------------------------------
     * ALL SUBJECTS ABSENT
     * --------------------------------------------------------
     */

    if ($presentCount === 0 || $attemptedMax <= 0) {

        return [

            'total'          => 0,

            'attempted_max'  => 0,

            'percentage'     => null,

            'grade'          => '-',

            'result'         => 'ABSENT',

            'present_count'  => 0,

            'failed_count'   => 0

        ];

    }


    /*
     * --------------------------------------------------------
     * CALCULATE PERCENTAGE
     * --------------------------------------------------------
     */

    $percentage =
        ($totalObtained / $attemptedMax) * 100;


    /*
     * --------------------------------------------------------
     * CALCULATE GRADE
     * --------------------------------------------------------
     */

    $grade =
        $calculateOverallGrade($percentage);


    /*
     * --------------------------------------------------------
     * CALCULATE RESULT
     * --------------------------------------------------------
     *
     * If at least one attempted subject failed,
     * overall result is FAIL.
     *
     * Otherwise PASS.
     *
     */

    $result =
        $failedCount > 0
            ? 'FAIL'
            : 'PASS';


    return [

        'total' =>
            $totalObtained,

        'attempted_max' =>
            $attemptedMax,

        'percentage' =>
            $percentage,

        'grade' =>
            $grade,

        'result' =>
            $result,

        'present_count' =>
            $presentCount,

        'failed_count' =>
            $failedCount

    ];

};

@endphp


{{-- =========================================================
     RESULT TABLE
========================================================= --}}

<table class="result-sheet-table">

    <colgroup>

        <col style="width:38px;">

        <col style="width:145px;">


        @foreach($normalSubjects as $subject)

            <col style="width:30px;">
            <col style="width:30px;">

        @endforeach


        @if($showMathGroup)

            <col style="width:32px;">
            <col style="width:32px;">

            <col style="width:32px;">
            <col style="width:32px;">

        @endif


        @if($showScienceGroup)

            <col style="width:32px;">
            <col style="width:32px;">

            <col style="width:32px;">
            <col style="width:32px;">

        @endif


        @if($showSocialGroup)

            <col style="width:32px;">
            <col style="width:32px;">

            <col style="width:32px;">
            <col style="width:32px;">

        @endif


        @if($showSkillColumn)

            <col style="width:70px;">

        @endif


        <col style="width:50px;">

        <col style="width:43px;">

        <col style="width:42px;">

        <col style="width:48px;">

    </colgroup>


    <thead>

        <tr>

            <th rowspan="2"
                class="roll-column">
                Roll No
            </th>


            <th rowspan="2"
                class="student-name-header">
                Student Name
            </th>


            @foreach($normalSubjects as $subject)

                <th colspan="2"
                    class="group-header">

                    {{ $subject->subject_name ?? '-' }}

                    <span class="subject-max">
                        Max Mark = {{ (int)($subject->max_marks ?? 0) }}
                    </span>

                </th>

            @endforeach


            @if($showMathGroup)

                <th colspan="4"
                    class="group-header">

                    MATHEMATICS

                    <span class="subject-max">
                        Math Total =
                        ({{ $math1Max + $math2Max }})
                    </span>

                </th>

            @endif


            @if($showScienceGroup)

                <th colspan="4"
                    class="group-header">

                    SCIENCE

                    <span class="subject-max">
                        Science Total =
                        ({{ $sci1Max + $sci2Max }})
                    </span>

                </th>

            @endif


            @if($showSocialGroup)

                <th colspan="4"
                    class="group-header">

                    SOCIAL SCIENCE

                    <span class="subject-max">
                        Social Sci Total =
                        ({{ $historyMax + $geographyMax }})
                    </span>

                </th>

            @endif


            @if($showSkillColumn)

                <th rowspan="2"
                    class="skill-column">

                    Skill

                </th>

            @endif


            <th rowspan="2"
                class="total-column">

                Total Marks

                <span class="subject-max">
                    Attempted Max
                </span>

            </th>


            <th rowspan="2"
                class="percentage-column">

                Percentage
                <br>
                (%)

            </th>


            <th rowspan="2"
                class="grade-column">

                Grade

            </th>


            <th rowspan="2"
                class="result-column">

                Result

            </th>

        </tr>


        <tr>

            @foreach($normalSubjects as $subject)

                <th class="normal-mark-column">
                    Mark
                </th>

                <th class="normal-grade-column">
                    Grade
                </th>

            @endforeach


            @if($showMathGroup)

                <th class="component-column">

                    <span class="component-name">
                        MAT1
                    </span>

                    <span class="component-max">
                        ({{ $math1Max }})
                    </span>

                </th>

                <th class="component-column">
                    Grade
                </th>


                <th class="component-column">

                    <span class="component-name">
                        MAT2
                    </span>

                    <span class="component-max">
                        ({{ $math2Max }})
                    </span>

                </th>

                <th class="component-column">
                    Grade
                </th>

            @endif


            @if($showScienceGroup)

                <th class="component-column">

                    <span class="component-name">
                        SCI1
                    </span>

                    <span class="component-max">
                        ({{ $sci1Max }})
                    </span>

                </th>

                <th class="component-column">
                    Grade
                </th>


                <th class="component-column">

                    <span class="component-name">
                        SCI2
                    </span>

                    <span class="component-max">
                        ({{ $sci2Max }})
                    </span>

                </th>

                <th class="component-column">
                    Grade
                </th>

            @endif


            @if($showSocialGroup)

                <th class="component-column">

                    <span class="component-name">
                        HIS
                    </span>

                    <span class="component-max">
                        ({{ $historyMax }})
                    </span>

                </th>

                <th class="component-column">
                    Grade
                </th>


                <th class="component-column">

                    <span class="component-name">
                        GEO
                    </span>

                    <span class="component-max">
                        ({{ $geographyMax }})
                    </span>

                </th>

                <th class="component-column">
                    Grade
                </th>

            @endif

        </tr>

    </thead>


    <tbody>

    @foreach($results as $row)

        @php

        /*
        |--------------------------------------------------------------------------
        | NEW OVERALL CALCULATION
        |--------------------------------------------------------------------------
        */

        $overall =
            $calculateStudentOverall($row);


        $details =
            $row->details ?? [];


        /*
        |--------------------------------------------------------------------------
        | COMPONENT DETAILS
        |--------------------------------------------------------------------------
        */

        $mat1Detail =
            $math1Id !== null
                ? ($details[$math1Id] ?? null)
                : null;

        $mat2Detail =
            $math2Id !== null
                ? ($details[$math2Id] ?? null)
                : null;

        $sci1Detail =
            $sci1Id !== null
                ? ($details[$sci1Id] ?? null)
                : null;

        $sci2Detail =
            $sci2Id !== null
                ? ($details[$sci2Id] ?? null)
                : null;

        $historyDetail =
            $historyId !== null
                ? ($details[$historyId] ?? null)
                : null;

        $geographyDetail =
            $geographyId !== null
                ? ($details[$geographyId] ?? null)
                : null;


        /*
        |--------------------------------------------------------------------------
        | COMPONENT MARKS
        |--------------------------------------------------------------------------
        */

        $mat1Mark =
            $mat1Detail['marks'] ?? null;

        $mat2Mark =
            $mat2Detail['marks'] ?? null;

        $sci1Mark =
            $sci1Detail['marks'] ?? null;

        $sci2Mark =
            $sci2Detail['marks'] ?? null;

        $historyMark =
            $historyDetail['marks'] ?? null;

        $geographyMark =
            $geographyDetail['marks'] ?? null;


        /*
        |--------------------------------------------------------------------------
        | COMPONENT GRADES
        |--------------------------------------------------------------------------
        */

        $mat1Grade =
            $mat1Detail['grade'] ?? '';

        $mat2Grade =
            $mat2Detail['grade'] ?? '';

        $sci1Grade =
            $sci1Detail['grade'] ?? '';

        $sci2Grade =
            $sci2Detail['grade'] ?? '';

        $historyGrade =
            $historyDetail['grade'] ?? '';

        $geographyGrade =
            $geographyDetail['grade'] ?? '';

        @endphp


        <tr>


            {{-- =================================================
                 ROLL NO
            ================================================== --}}

            <td class="roll-column">

                {{ $row->roll_no ?? '-' }}

            </td>


            {{-- =================================================
                 STUDENT NAME
            ================================================== --}}

            <td class="student-name">

                {{ $row->full_student_name ?? '-' }}

            </td>


            {{-- =================================================
                 NORMAL SUBJECTS
            ================================================== --}}

            @foreach($normalSubjects as $subject)

                @php

                    $detail =
                        $details[$subject->id] ?? null;

                    $mark =
                        $detail['marks'] ?? null;

                    $grade =
                        $detail['grade'] ?? '';

                    $isAbsent =
                        $isDetailAbsent($detail);

                @endphp


                <td class="normal-mark-column">

                    @if($isAbsent)

                        <span class="absent-mark">
                            AB
                        </span>

                    @elseif($mark !== null && $mark !== '')

                        {{ is_numeric($mark) ? (int)$mark : $mark }}

                    @else

                        -

                    @endif

                </td>


                <td class="normal-grade-column">

                    @if($isAbsent)

                        AB

                    @elseif($grade !== '' && $grade !== null)

                        {{ $grade }}

                    @else

                        -

                    @endif

                </td>

            @endforeach


            {{-- =================================================
                 MATHEMATICS
            ================================================== --}}

            @if($showMathGroup)

                <td class="component-column">

                    @if($isDetailAbsent($mat1Detail))

                        <span class="absent-mark">
                            AB
                        </span>

                    @elseif($mat1Mark !== null && $mat1Mark !== '')

                        {{ is_numeric($mat1Mark) ? (int)$mat1Mark : $mat1Mark }}

                    @else

                        -

                    @endif

                </td>


                <td class="component-column">

                    @if($isDetailAbsent($mat1Detail))

                        AB

                    @elseif($mat1Grade !== '')

                        {{ $mat1Grade }}

                    @else

                        -

                    @endif

                </td>


                <td class="component-column">

                    @if($isDetailAbsent($mat2Detail))

                        <span class="absent-mark">
                            AB
                        </span>

                    @elseif($mat2Mark !== null && $mat2Mark !== '')

                        {{ is_numeric($mat2Mark) ? (int)$mat2Mark : $mat2Mark }}

                    @else

                        -

                    @endif

                </td>


                <td class="component-column">

                    @if($isDetailAbsent($mat2Detail))

                        AB

                    @elseif($mat2Grade !== '')

                        {{ $mat2Grade }}

                    @else

                        -

                    @endif

                </td>

            @endif


            {{-- =================================================
                 SCIENCE
            ================================================== --}}

            @if($showScienceGroup)

                <td class="component-column">

                    @if($isDetailAbsent($sci1Detail))

                        <span class="absent-mark">
                            AB
                        </span>

                    @elseif($sci1Mark !== null && $sci1Mark !== '')

                        {{ is_numeric($sci1Mark) ? (int)$sci1Mark : $sci1Mark }}

                    @else

                        -

                    @endif

                </td>


                <td class="component-column">

                    @if($isDetailAbsent($sci1Detail))

                        AB

                    @elseif($sci1Grade !== '')

                        {{ $sci1Grade }}

                    @else

                        -

                    @endif

                </td>


                <td class="component-column">

                    @if($isDetailAbsent($sci2Detail))

                        <span class="absent-mark">
                            AB
                        </span>

                    @elseif($sci2Mark !== null && $sci2Mark !== '')

                        {{ is_numeric($sci2Mark) ? (int)$sci2Mark : $sci2Mark }}

                    @else

                        -

                    @endif

                </td>


                <td class="component-column">

                    @if($isDetailAbsent($sci2Detail))

                        AB

                    @elseif($sci2Grade !== '')

                        {{ $sci2Grade }}

                    @else

                        -

                    @endif

                </td>

            @endif


            {{-- =================================================
                 SOCIAL SCIENCE
            ================================================== --}}

            @if($showSocialGroup)

                <td class="component-column">

                    @if($isDetailAbsent($historyDetail))

                        <span class="absent-mark">
                            AB
                        </span>

                    @elseif($historyMark !== null && $historyMark !== '')

                        {{ is_numeric($historyMark) ? (int)$historyMark : $historyMark }}

                    @else

                        -

                    @endif

                </td>


                <td class="component-column">

                    @if($isDetailAbsent($historyDetail))

                        AB

                    @elseif($historyGrade !== '')

                        {{ $historyGrade }}

                    @else

                        -

                    @endif

                </td>


                <td class="component-column">

                    @if($isDetailAbsent($geographyDetail))

                        <span class="absent-mark">
                            AB
                        </span>

                    @elseif($geographyMark !== null && $geographyMark !== '')

                        {{ is_numeric($geographyMark) ? (int)$geographyMark : $geographyMark }}

                    @else

                        -

                    @endif

                </td>


                <td class="component-column">

                    @if($isDetailAbsent($geographyDetail))

                        AB

                    @elseif($geographyGrade !== '')

                        {{ $geographyGrade }}

                    @else

                        -

                    @endif

                </td>

            @endif


            {{-- =================================================
                 SKILL
            ================================================== --}}

            @if($showSkillColumn)

                <td class="skill-column">

                    @if(!empty($row->skill_subject))

                        {{ $row->skill_subject }}

                        @if(
                            $row->skill_mark !== ''
                            &&
                            $row->skill_mark !== null
                        )

                            (
                            {{
                                is_numeric($row->skill_mark)
                                    ? (int)$row->skill_mark
                                    : $row->skill_mark
                            }}
                            )

                        @endif

                    @else

                        -

                    @endif

                </td>

            @endif


            {{-- =================================================
                 TOTAL
            ================================================== --}}

            <td class="total-column">

                @if($overall['result'] === 'ABSENT')

                    0

                @else

                    {{ (int)round($overall['total']) }}

                @endif

            </td>


            {{-- =================================================
                 PERCENTAGE
            ================================================== --}}

            <td class="percentage-column">

                @if($overall['percentage'] === null)

                    -

                @else

                    {{ number_format($overall['percentage'], 2) }}

                @endif

            </td>


            {{-- =================================================
                 OVERALL GRADE
            ================================================== --}}

            <td class="grade-column">

                {{ $overall['grade'] }}

            </td>


            {{-- =================================================
                 OVERALL RESULT
            ================================================== --}}

            <td class="result-column">

                @if($overall['result'] === 'PASS')

                    <span class="pass-result">
                        PASS
                    </span>

                @elseif($overall['result'] === 'ABSENT')

                    <span class="absent-mark">
                        ABSENT
                    </span>

                @else

                    <span class="fail-result">
                        FAIL
                    </span>

                @endif

            </td>

        </tr>

    @endforeach

    </tbody>

</table>


{{-- =========================================================
     OVERALL GRADE ANALYSIS
========================================================= --}}

@php

$analysis = [

    'A1' => [
        'range' => '91-100',
        'girls' => 0,
        'boys' => 0
    ],

    'A2' => [
        'range' => '81-90',
        'girls' => 0,
        'boys' => 0
    ],

    'B1' => [
        'range' => '71-80',
        'girls' => 0,
        'boys' => 0
    ],

    'B2' => [
        'range' => '61-70',
        'girls' => 0,
        'boys' => 0
    ],

    'C1' => [
        'range' => '51-60',
        'girls' => 0,
        'boys' => 0
    ],

    'C2' => [
        'range' => '41-50',
        'girls' => 0,
        'boys' => 0
    ],

    'D' => [
        'range' => 'Below 41',
        'girls' => 0,
        'boys' => 0
    ]

];


$absent = [
    'girls' => 0,
    'boys' => 0
];


$left = [
    'girls' => 0,
    'boys' => 0
];


foreach($results as $student) {


    /*
    |--------------------------------------------------------------------------
    | GENDER
    |--------------------------------------------------------------------------
    */

    $gender =
        strtoupper($student->gender ?? '') === 'FEMALE'
            ? 'girls'
            : 'boys';


    /*
    |--------------------------------------------------------------------------
    | LEFT STUDENT
    |--------------------------------------------------------------------------
    */

    if (
        strtoupper(
            trim(
                (string)($student->result ?? '')
            )
        ) === 'LEFT'
    ) {

        $left[$gender]++;

        continue;

    }


    /*
    |--------------------------------------------------------------------------
    | CALCULATE SAME OVERALL RESULT
    |--------------------------------------------------------------------------
    */

    $overall =
        $calculateStudentOverall($student);


    /*
    |--------------------------------------------------------------------------
    | ALL SUBJECTS ABSENT
    |--------------------------------------------------------------------------
    */

    if ($overall['result'] === 'ABSENT') {

        $absent[$gender]++;

        continue;

    }


    /*
    |--------------------------------------------------------------------------
    | OVERALL GRADE
    |--------------------------------------------------------------------------
    */

    $grade =
        $overall['grade'];


    if (isset($analysis[$grade])) {

        $analysis[$grade][$gender]++;

    }

}

@endphp


<div class="analysis-title">
    Overall Grade Analysis
</div>


<table class="analysis-table">

    <thead>

        <tr>

            <th>Percentage</th>
            <th>Grade</th>
            <th>Girls</th>
            <th>Boys</th>
            <th>Total</th>

        </tr>

    </thead>


    <tbody>

    @foreach($analysis as $grade => $data)

        <tr>

            <td>
                {{ $data['range'] }}
            </td>

            <td>
                {{ $grade }}
            </td>

            <td>
                {{ $data['girls'] }}
            </td>

            <td>
                {{ $data['boys'] }}
            </td>

            <td>
                {{ $data['girls'] + $data['boys'] }}
            </td>

        </tr>

    @endforeach


    <tr>

        <td>-</td>

        <td>ABSENT</td>

        <td>
            {{ $absent['girls'] }}
        </td>

        <td>
            {{ $absent['boys'] }}
        </td>

        <td>
            {{ $absent['girls'] + $absent['boys'] }}
        </td>

    </tr>


    <tr>

        <td>-</td>

        <td>LEFT</td>

        <td>
            {{ $left['girls'] }}
        </td>

        <td>
            {{ $left['boys'] }}
        </td>

        <td>
            {{ $left['girls'] + $left['boys'] }}
        </td>

    </tr>


    <tr style="font-weight:700;background:#e5e7eb;">

        <th colspan="2">
            TOTAL
        </th>

        <th>
            {{
                collect($analysis)->sum('girls')
                + $absent['girls']
                + $left['girls']
            }}
        </th>

        <th>
            {{
                collect($analysis)->sum('boys')
                + $absent['boys']
                + $left['boys']
            }}
        </th>

        <th>
            {{
                collect($analysis)->sum('girls')
                +
                collect($analysis)->sum('boys')
                +
                $absent['girls']
                +
                $absent['boys']
                +
                $left['girls']
                +
                $left['boys']
            }}
        </th>

    </tr>

    </tbody>

</table>


{{-- =========================================================
     SUBJECT-WISE ANALYSIS
========================================================= --}}

@if(
    !empty($girlsSubjectAnalysis)
    ||
    !empty($boysSubjectAnalysis)
)

<div class="analysis-title">
    Subject-wise Analysis
</div>


@php

$girlsBySubject =
    collect($girlsSubjectAnalysis ?? [])
        ->keyBy('subject');

$boysBySubject =
    collect($boysSubjectAnalysis ?? [])
        ->keyBy('subject');


$analysisSubjects =
    collect(
        array_unique(
            array_merge(
                $girlsBySubject->keys()->toArray(),
                $boysBySubject->keys()->toArray()
            )
        )
    );


$subjectOrder = [

    'ENGLISH' => 1,
    'SANSKRIT' => 2,
    'MARATHI' => 3,
    'MATHEMATICS' => 4,
    'SCIENCE' => 5,
    'SOCIAL SCIENCE' => 6,

];


$analysisSubjects =
    $analysisSubjects
        ->sortBy(function ($subject) use ($subjectOrder) {

            return
                $subjectOrder[
                    strtoupper(trim($subject))
                ] ?? 999;

        })
        ->values();


$analysisCategories = [

    'A1',
    'A2',
    'B1',
    'B2',
    'C1',
    'C2',
    'D',
    'FAIL',
    'ABSENT'

];


$totals = [

    'A1_girls'=>0,
    'A1_boys'=>0,

    'A2_girls'=>0,
    'A2_boys'=>0,

    'B1_girls'=>0,
    'B1_boys'=>0,

    'B2_girls'=>0,
    'B2_boys'=>0,

    'C1_girls'=>0,
    'C1_boys'=>0,

    'C2_girls'=>0,
    'C2_boys'=>0,

    'D_girls'=>0,
    'D_boys'=>0,

    'FAIL_girls'=>0,
    'FAIL_boys'=>0,

    'ABSENT_girls'=>0,
    'ABSENT_boys'=>0,

];


foreach($analysisSubjects as $subjectName) {

    $girls =
        $girlsBySubject->get(
            $subjectName,
            []
        );

    $boys =
        $boysBySubject->get(
            $subjectName,
            []
        );


    foreach($analysisCategories as $category) {

        $key = $category;

        if($category === 'FAIL') {
            $key = 'fail';
        }

        if($category === 'ABSENT') {
            $key = 'absent';
        }


        $totals[$category . '_girls'] +=
            $girls[$key] ?? 0;

        $totals[$category . '_boys'] +=
            $boys[$key] ?? 0;

    }

}


$grandTotal =
    array_sum($totals);

@endphp


<table class="analysis-table combined-analysis-table">

    <thead>

        <tr>

            <th rowspan="2"
                class="analysis-subject-column">
                Subject
            </th>


            @foreach($analysisCategories as $category)

                <th colspan="2">
                    {{ $category }}
                </th>

            @endforeach


            <th rowspan="2">
                TOTAL
            </th>

        </tr>


        <tr>

            @foreach($analysisCategories as $category)

                <th class="gender-header">
                    Girls
                </th>

                <th class="gender-header">
                    Boys
                </th>

            @endforeach

        </tr>

    </thead>


    <tbody>


    @foreach($analysisSubjects as $subjectName)

        @php

        $girls =
            $girlsBySubject->get(
                $subjectName,
                []
            );

        $boys =
            $boysBySubject->get(
                $subjectName,
                []
            );


        $rowTotal =

            ($girls['A1'] ?? 0) +
            ($boys['A1'] ?? 0) +

            ($girls['A2'] ?? 0) +
            ($boys['A2'] ?? 0) +

            ($girls['B1'] ?? 0) +
            ($boys['B1'] ?? 0) +

            ($girls['B2'] ?? 0) +
            ($boys['B2'] ?? 0) +

            ($girls['C1'] ?? 0) +
            ($boys['C1'] ?? 0) +

            ($girls['C2'] ?? 0) +
            ($boys['C2'] ?? 0) +

            ($girls['D'] ?? 0) +
            ($boys['D'] ?? 0) +

            ($girls['fail'] ?? 0) +
            ($boys['fail'] ?? 0) +

            ($girls['absent'] ?? 0) +
            ($boys['absent'] ?? 0);

        @endphp


        <tr>

            <td class="analysis-subject-name">
                {{ $subjectName }}
            </td>


            <td>{{ $girls['A1'] ?? 0 }}</td>
            <td>{{ $boys['A1'] ?? 0 }}</td>

            <td>{{ $girls['A2'] ?? 0 }}</td>
            <td>{{ $boys['A2'] ?? 0 }}</td>

            <td>{{ $girls['B1'] ?? 0 }}</td>
            <td>{{ $boys['B1'] ?? 0 }}</td>

            <td>{{ $girls['B2'] ?? 0 }}</td>
            <td>{{ $boys['B2'] ?? 0 }}</td>

            <td>{{ $girls['C1'] ?? 0 }}</td>
            <td>{{ $boys['C1'] ?? 0 }}</td>

            <td>{{ $girls['C2'] ?? 0 }}</td>
            <td>{{ $boys['C2'] ?? 0 }}</td>

            <td>{{ $girls['D'] ?? 0 }}</td>
            <td>{{ $boys['D'] ?? 0 }}</td>

            <td>{{ $girls['fail'] ?? 0 }}</td>
            <td>{{ $boys['fail'] ?? 0 }}</td>

            <td>{{ $girls['absent'] ?? 0 }}</td>
            <td>{{ $boys['absent'] ?? 0 }}</td>

            <td>{{ $rowTotal }}</td>

        </tr>

    @endforeach


    <tr style="font-weight:700;background:#e5e7eb;">

        <td class="analysis-subject-name">
            TOTAL
        </td>

        <td>{{ $totals['A1_girls'] }}</td>
        <td>{{ $totals['A1_boys'] }}</td>

        <td>{{ $totals['A2_girls'] }}</td>
        <td>{{ $totals['A2_boys'] }}</td>

        <td>{{ $totals['B1_girls'] }}</td>
        <td>{{ $totals['B1_boys'] }}</td>

        <td>{{ $totals['B2_girls'] }}</td>
        <td>{{ $totals['B2_boys'] }}</td>

        <td>{{ $totals['C1_girls'] }}</td>
        <td>{{ $totals['C1_boys'] }}</td>

        <td>{{ $totals['C2_girls'] }}</td>
        <td>{{ $totals['C2_boys'] }}</td>

        <td>{{ $totals['D_girls'] }}</td>
        <td>{{ $totals['D_boys'] }}</td>

        <td>{{ $totals['FAIL_girls'] }}</td>
        <td>{{ $totals['FAIL_boys'] }}</td>

        <td>{{ $totals['ABSENT_girls'] }}</td>
        <td>{{ $totals['ABSENT_boys'] }}</td>

        <td>{{ $grandTotal }}</td>

    </tr>

    </tbody>

</table>

@endif


{{-- =========================================================
     SIGNATURES
========================================================= --}}

<div class="signature-area">

    <div class="signature-box">

        _______________________

        <br><br>

        Class Teacher

    </div>


    <div class="signature-box">

        _______________________

        <br><br>

        Principal

    </div>

</div>


</body>
</html>