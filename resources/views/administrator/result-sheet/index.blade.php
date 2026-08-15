blade
@extends('layouts.app')

<style>
/* =========================================================
   RESULT SHEET TABLE
========================================================= */

.result-sheet-table,
.result-sheet-table th,
.result-sheet-table td {
    font-family: Arial, sans-serif !important;
    font-size: 11px !important;
}

.result-sheet-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: auto;
    border: 1px solid #333 !important;
}

.result-sheet-table th,
.result-sheet-table td {
    border: 1px solid #333 !important;
}

.result-sheet-table th {
    background: #dbeafe;
    text-align: center;
    padding: 4px 3px;
    white-space: nowrap;
    font-weight: 700;
    vertical-align: middle;
}

.result-sheet-table td {
    text-align: center;
    padding: 3px;
    white-space: nowrap;
    vertical-align: middle;
    font-weight: 400;
}

/* =========================================================
   STUDENT NAME
========================================================= */

.student-name-header,
.student-name {
    width: 260px;
    min-width: 260px;
    text-align: left !important;
    white-space: nowrap;
}

/* =========================================================
   SUBJECT MAXIMUM
========================================================= */

.subject-max {
    display: block;
    font-family: Arial, sans-serif !important;
    font-size: 9px !important;
    font-weight: 400 !important;
}

/* =========================================================
   COMPONENT
========================================================= */

.component-name {
    display: block;
    font-family: Arial, sans-serif !important;
    font-size: 10px !important;
    font-weight: 700 !important;
}

.component-max {
    display: block;
    font-family: Arial, sans-serif !important;
    font-size: 9px !important;
    font-weight: 400 !important;
}

.mark-header,
.grade-header {
    font-size: 10px !important;
    font-weight: 700 !important;
}

.group-header {
    font-family: Arial, sans-serif !important;
    font-size: 11px !important;
    font-weight: 700 !important;
}

/* =========================================================
   MARK / RESULT
========================================================= */

.absent-mark {
    font-family: Arial, sans-serif !important;
    font-size: 11px !important;
    color: #b91c1c;
    font-weight: 700;
}

.pass-result {
    font-family: Arial, sans-serif !important;
    font-size: 11px !important;
    color: #166534;
    font-weight: 700;
}

.fail-result {
    font-family: Arial, sans-serif !important;
    font-size: 11px !important;
    color: #b91c1c;
    font-weight: 700;
}

.grade-value {
    font-weight: 700 !important;
}

/* =========================================================
   WRAPPER
========================================================= */

.result-sheet-wrapper {
    overflow-x: auto;
    margin-top: 10px;
}

/* =========================================================
   ANALYSIS
========================================================= */

.analysis-title {
    font-family: Arial, sans-serif !important;
    font-size: 16px !important;
    font-weight: 700;
    color: #1d4ed8;
    margin-bottom: 8px;
}

.analysis-gender-title {
    font-family: Arial, sans-serif !important;
    font-size: 14px !important;
    font-weight: 700;
    margin: 12px 0 8px;
}
</style>


@section('content')

<div class="erp-card no-print">

    {{-- =========================================================
         PAGE TITLE
    ========================================================= --}}

    <h2 style="
        font-size:20px;
        font-weight:bold;
        color:#1d4ed8;
        margin-bottom:8px;
    ">
        EXAMINATION RESULT SHEET
    </h2>


    {{-- =========================================================
         SEARCH FORM
    ========================================================= --}}

    <form method="POST"
          action="{{ route('result-sheet.search') }}">

        @csrf

        <div style="
            display:flex;
            align-items:center;
            gap:8px;
            flex-wrap:wrap;
            font-size:14px;
        ">

            {{-- Academic Year --}}

            <label style="font-size:14px;font-weight:600;">
                Academic Year
            </label>

            <select name="academic_year_id"
                    style="
                        width:125px;
                        height:28px;
                        font-size:14px;
                        padding:2px 4px;
                    "
                    required>

                <option value="">
                    Select
                </option>

                @foreach($academicYears as $year)

                    <option value="{{ $year->id }}"
                        {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>

                        {{ $year->year_name }}

                    </option>

                @endforeach

            </select>


            {{-- Exam --}}

            <label style="font-size:14px;font-weight:600;">
                Exam
            </label>

            <select name="exam_master_id"
                    style="
                        width:200px;
                        height:28px;
                        font-size:14px;
                        padding:2px 4px;
                    "
                    required>

                <option value="">
                    Select
                </option>

                @foreach($exams as $examItem)

                    <option value="{{ $examItem->id }}"
                        {{ request('exam_master_id') == $examItem->id ? 'selected' : '' }}>

                        {{ $examItem->exam_name }}

                    </option>

                @endforeach

            </select>


            {{-- Standard --}}

            <label style="font-size:14px;font-weight:600;">
                Standard
            </label>

            <select name="standard_id"
                    style="
                        width:120px;
                        height:28px;
                        font-size:14px;
                        padding:2px 4px;
                    "
                    required>

                <option value="">
                    Select
                </option>

                @foreach($standards as $standardItem)

                    <option value="{{ $standardItem->id }}"
                        {{ request('standard_id') == $standardItem->id ? 'selected' : '' }}>

                        {{ $standardItem->standard_name }}

                    </option>

                @endforeach

            </select>


            {{-- Division --}}

            <label style="font-size:14px;font-weight:600;">
                Division
            </label>

            <select name="division_id"
                    style="
                        width:80px;
                        height:28px;
                        font-size:14px;
                        padding:2px 4px;
                    "
                    required>

                <option value="">
                    Select
                </option>

                @foreach($divisions as $divisionItem)

                    <option value="{{ $divisionItem->id }}"
                        {{ request('division_id') == $divisionItem->id ? 'selected' : '' }}>

                        {{ $divisionItem->division_name }}

                    </option>

                @endforeach

            </select>


            {{-- Generate --}}

            <button type="submit"
                    class="erp-btn erp-btn-save">

                Generate

            </button>


            {{-- Print --}}

            @if($results->count())

                <a href="{{ route('result-sheet.print', [
                    'academic_year_id' => request('academic_year_id'),
                    'exam_master_id'   => request('exam_master_id'),
                    'standard_id'      => request('standard_id'),
                    'division_id'      => request('division_id'),
                ]) }}"
                   target="_blank"
                   class="erp-btn erp-btn-save">

                    Print

                </a>

                <span class="font-bold text-blue-700">

                    Total Students : {{ $results->count() }}

                </span>

            @endif

        </div>

    </form>

</div>


{{-- =========================================================
     ERROR
========================================================= --}}

@if(session('error'))

    <div class="erp-card mt-3"
         style="
            color:#b91c1c;
            font-weight:600;
            background:#fee2e2;
            border:1px solid #fecaca;
         ">

        {{ session('error') }}

    </div>

@endif


{{-- =========================================================
     SUCCESS
========================================================= --}}

@if(session('success'))

    <div class="erp-card mt-3"
         style="
            color:#166534;
            font-weight:600;
            background:#dcfce7;
            border:1px solid #bbf7d0;
         ">

        {{ session('success') }}

    </div>

@endif


{{-- =========================================================
     RESULT
========================================================= --}}

@if($results->count())

<div class="erp-card mt-4">

@php

/* ============================================================
   SUBJECT NAME HELPER
============================================================ */

$getSubjectName = function ($subject) {

    return strtoupper(
        trim(
            $subject->subject_name
            ?? $subject->name
            ?? ''
        )
    );

};


/* ============================================================
   EXAM SUBJECT COLLECTION
============================================================ */

$allExamSubjects = collect($examSubjects ?? []);

$allAcademicSubjects = collect($academicSubjects ?? []);


/* ============================================================
   FIND COMPONENT SUBJECTS
============================================================ */

$math1Subject = $allExamSubjects->first(
    fn($subject) =>
        $getSubjectName($subject) === 'MATHEMATICS I'
        || $getSubjectName($subject) === 'MAT1'
);

$math2Subject = $allExamSubjects->first(
    fn($subject) =>
        $getSubjectName($subject) === 'MATHEMATICS II'
        || $getSubjectName($subject) === 'MAT2'
);

$sci1Subject = $allExamSubjects->first(
    fn($subject) =>
        $getSubjectName($subject) === 'SCIENCE I'
        || $getSubjectName($subject) === 'SCI1'
);

$sci2Subject = $allExamSubjects->first(
    fn($subject) =>
        $getSubjectName($subject) === 'SCIENCE II'
        || $getSubjectName($subject) === 'SCI2'
);

$historySubject = $allExamSubjects->first(
    fn($subject) =>
        $getSubjectName($subject) === 'HISTORY'
        || $getSubjectName($subject) === 'HIS'
);

$geographySubject = $allExamSubjects->first(
    fn($subject) =>
        $getSubjectName($subject) === 'GEOGRAPHY'
        || $getSubjectName($subject) === 'GEO'
);

$evs1Subject = $allExamSubjects->first(
    fn($subject) =>
        in_array(
            $getSubjectName($subject),
            [
                'EVS1',
                'EVS I',
                'ENVIRONMENTAL STUDIES I'
            ],
            true
        )
);

$evs2Subject = $allExamSubjects->first(
    fn($subject) =>
        in_array(
            $getSubjectName($subject),
            [
                'EVS2',
                'EVS II',
                'ENVIRONMENTAL STUDIES II'
            ],
            true
        )
);


/* ============================================================
   COMPONENT IDS
============================================================ */

$math1Id = $math1Subject->id ?? null;
$math2Id = $math2Subject->id ?? null;

$sci1Id = $sci1Subject->id ?? null;
$sci2Id = $sci2Subject->id ?? null;

$historyId = $historySubject->id ?? null;
$geographyId = $geographySubject->id ?? null;

$evs1Id = $evs1Subject->id ?? null;
$evs2Id = $evs2Subject->id ?? null;


/* ============================================================
   COMPONENT MAX MARKS
============================================================ */

$math1Max = (int)($math1Subject->max_marks ?? 0);
$math2Max = (int)($math2Subject->max_marks ?? 0);

$sci1Max = (int)($sci1Subject->max_marks ?? 0);
$sci2Max = (int)($sci2Subject->max_marks ?? 0);

$historyMax = (int)($historySubject->max_marks ?? 0);
$geographyMax = (int)($geographySubject->max_marks ?? 0);

$evs1Max = (int)($evs1Subject->max_marks ?? 0);
$evs2Max = (int)($evs2Subject->max_marks ?? 0);


/* ============================================================
   GROUP TOTAL MAXIMUM
============================================================ */

$mathTotalMax =
    $math1Max +
    $math2Max;

$scienceTotalMax =
    $sci1Max +
    $sci2Max;

$socialTotalMax =
    $historyMax +
    $geographyMax;

$evsTotalMax =
    $evs1Max +
    $evs2Max;


/* ============================================================
   COMPONENT IDS
============================================================ */

$componentIds = collect([
    $math1Id,
    $math2Id,
    $sci1Id,
    $sci2Id,
    $historyId,
    $geographyId,
    $evs1Id,
    $evs2Id,
])
->filter(fn($id) => $id !== null)
->map(fn($id) => (int)$id)
->values()
->toArray();


/* ============================================================
   NORMAL SUBJECTS
============================================================ */

$normalSubjects = $allExamSubjects
    ->filter(function ($subject) use ($componentIds) {

        return !in_array(
            (int)($subject->id ?? 0),
            $componentIds,
            true
        );

    })
    ->values();


/* ============================================================
   SUBJECT ORDER
============================================================ */

$subjectOrder = [
    'ENG' => 1,
    'ENGLISH' => 1,

    'SAN' => 2,
    'SANSKRIT' => 2,

    'MAR' => 3,
    'MARATHI' => 3,
];


/* ============================================================
   SORT NORMAL SUBJECTS
============================================================ */

$normalSubjects = $normalSubjects
    ->sortBy(function ($subject) use (
        $subjectOrder,
        $getSubjectName
    ) {

        $shortName = strtoupper(
            trim(
                $subject->short_name ?? ''
            )
        );

        $subjectName =
            $getSubjectName($subject);

        return
            $subjectOrder[$shortName]
            ?? $subjectOrder[$subjectName]
            ?? (
                (int)(
                    $subject->display_order
                    ?? $subject->sort_order
                    ?? 999
                )
            );

    })
    ->values();


/* ============================================================
   ACADEMIC TOTAL MAXIMUM
============================================================ */

$normalTotalMax = $normalSubjects->sum(
    fn($subject) =>
        (int)($subject->max_marks ?? 0)
);

$academicTotalMax =
    $normalTotalMax
    + (
        ($math1Id && $math2Id)
            ? $mathTotalMax
            : 0
    )
    + (
        ($sci1Id && $sci2Id)
            ? $scienceTotalMax
            : 0
    )
    + (
        ($historyId && $geographyId)
            ? $socialTotalMax
            : 0
    )
    + (
        ($evs1Id && $evs2Id)
            ? $evsTotalMax
            : 0
    );


/* ============================================================
   FINAL GRADE CALCULATOR
============================================================ */

/*
|--------------------------------------------------------------------------
| IMPORTANT
|--------------------------------------------------------------------------
|
| Never use the old saved grade from:
|
|     $detail['grade']
|     $detail['calculated_grade']
|     $row->calculated_grade
|
| Grade is calculated ONLY from marks and maximum marks.
|
*/

$calculateSubjectGrade = function (
    $mark,
    $maxMarks,
    $isAbsent = false
) {

    /* ABSENT */

    if (
        $isAbsent ||
        strtoupper(trim((string)$mark)) === 'AB'
    ) {
        return 'AB';
    }


    /* NO MARK */

    if ($mark === null || $mark === '') {
        return '-';
    }


    /* INVALID MARK */

    if (!is_numeric($mark)) {
        return '-';
    }


    $mark = (float)$mark;

    $maxMarks = (float)$maxMarks;


    /* INVALID MAX MARK */

    if ($maxMarks <= 0) {
        return '-';
    }


    /* PERCENTAGE */

    $percentage =
        ($mark / $maxMarks) * 100;


    /* ========================================================
       GRADING SYSTEM
    ======================================================== */

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

    if ($percentage >= 33) {
        return 'D';
    }

    return 'F';
};


/* ============================================================
   DISPLAY MARK
============================================================ */

$displayMark = function (
    $mark,
    $isAbsent = false
) {

    if (
        $isAbsent ||
        strtoupper(trim((string)$mark)) === 'AB'
    ) {
        return 'AB';
    }

    if ($mark === null || $mark === '') {
        return '-';
    }

    return is_numeric($mark)
        ? (int)$mark
        : $mark;
};

@endphp


{{-- ============================================================
     RESULT TABLE
============================================================ --}}

<div class="result-sheet-wrapper">

<table class="result-sheet-table">

<thead>

{{-- =========================================================
     HEADER ROW 1
========================================================= --}}

<tr>

    <th rowspan="2">
        Roll No
    </th>

    <th rowspan="2"
        class="student-name-header">

        Student Name

    </th>


    {{-- NORMAL SUBJECTS --}}

    @foreach($normalSubjects as $subject)

        <th colspan="2">

            {{ $subject->subject_name
                ?? $subject->name
                ?? '-' }}

            <span class="subject-max">

                Max Marks={{ (int)($subject->max_marks ?? 0) }}

            </span>

        </th>

    @endforeach


    {{-- MATHEMATICS --}}

    @if($math1Id !== null && $math2Id !== null)

        <th colspan="4"
            class="group-header">

            MATHEMATICS

            <span class="subject-max">

                Math Total = {{ $mathTotalMax }}

            </span>

        </th>

    @endif


    {{-- SCIENCE --}}

    @if($sci1Id !== null && $sci2Id !== null)

        <th colspan="4"
            class="group-header">

            SCIENCE

            <span class="subject-max">

                Science Total = {{ $scienceTotalMax }}

            </span>

        </th>

    @endif


    {{-- SOCIAL SCIENCE --}}

    @if($historyId !== null && $geographyId !== null)

        <th colspan="4"
            class="group-header">

            SOCIAL SCIENCE

            <span class="subject-max">

                Social Sci Total = {{ $socialTotalMax }}

            </span>

        </th>

    @endif


    {{-- EVS --}}

    @if($evs1Id !== null && $evs2Id !== null)

        <th colspan="4"
            class="group-header">

            EVS

            <span class="subject-max">

                EVS Total = {{ $evsTotalMax }}

            </span>

        </th>

    @endif


    {{-- SKILL --}}

    @if(
        $showSkillColumn &&
        request('exam_master_id') != 1
    )

        <th rowspan="2">
            Skill
        </th>

    @endif


    {{-- TOTAL --}}

    <th rowspan="2">

        Total Mark

        <span class="subject-max">

            ({{ (int)$academicTotalMax }})

        </span>

    </th>


    {{-- PERCENTAGE --}}

    <th rowspan="2">

        Percentage
        <br>
        (%)

    </th>


    {{-- OVERALL GRADE --}}

    <th rowspan="2">
        Grade
    </th>


    {{-- RESULT --}}

    <th rowspan="2">
        Result
    </th>

</tr>


{{-- =========================================================
     HEADER ROW 2
========================================================= --}}

<tr>

    {{-- NORMAL SUBJECTS --}}

    @foreach($normalSubjects as $subject)

        <th class="mark-header">
            Marks
        </th>

        <th class="grade-header">
            Grade
        </th>

    @endforeach


    {{-- MATHEMATICS --}}

    @if($math1Id !== null && $math2Id !== null)

        <th>

            <span class="component-name">
                MAT1
            </span>

            <span class="component-max">
                Marks ({{ $math1Max }})
            </span>

        </th>

        <th>
            Grade
        </th>

        <th>

            <span class="component-name">
                MAT2
            </span>

            <span class="component-max">
                Marks ({{ $math2Max }})
            </span>

        </th>

        <th>
            Grade
        </th>

    @endif


    {{-- SCIENCE --}}

    @if($sci1Id !== null && $sci2Id !== null)

        <th>

            <span class="component-name">
                SCI1
            </span>

            <span class="component-max">
                Marks ({{ $sci1Max }})
            </span>

        </th>

        <th>
            Grade
        </th>

        <th>

            <span class="component-name">
                SCI2
            </span>

            <span class="component-max">
                Marks ({{ $sci2Max }})
            </span>

        </th>

        <th>
            Grade
        </th>

    @endif


    {{-- SOCIAL SCIENCE --}}

    @if($historyId !== null && $geographyId !== null)

        <th>

            <span class="component-name">
                HIS
            </span>

            <span class="component-max">
                Marks ({{ $historyMax }})
            </span>

        </th>

        <th>
            Grade
        </th>

        <th>

            <span class="component-name">
                GEO
            </span>

            <span class="component-max">
                Marks ({{ $geographyMax }})
            </span>

        </th>

        <th>
            Grade
        </th>

    @endif


    {{-- EVS --}}

    @if($evs1Id !== null && $evs2Id !== null)

        <th>

            <span class="component-name">
                EVS1
            </span>

            <span class="component-max">
                Marks ({{ $evs1Max }})
            </span>

        </th>

        <th>
            Grade
        </th>

        <th>

            <span class="component-name">
                EVS2
            </span>

            <span class="component-max">
                Marks ({{ $evs2Max }})
            </span>

        </th>

        <th>
            Grade
        </th>

    @endif

</tr>

</thead>


{{-- =========================================================
     STUDENTS
========================================================= --}}

<tbody>

@foreach($results as $row)

@php

$details = $row->details ?? [];


/* ============================================================
   MAT1
============================================================ */

$mat1Detail =
    $math1Id !== null
        ? ($details[$math1Id] ?? null)
        : null;

$mat1Mark =
    $mat1Detail['marks'] ?? null;

$mat1Absent =
    ($mat1Detail['is_absent'] ?? 0) == 1;

$mat1Grade =
    $calculateSubjectGrade(
        $mat1Mark,
        $math1Max,
        $mat1Absent
    );


/* ============================================================
   MAT2
============================================================ */

$mat2Detail =
    $math2Id !== null
        ? ($details[$math2Id] ?? null)
        : null;

$mat2Mark =
    $mat2Detail['marks'] ?? null;

$mat2Absent =
    ($mat2Detail['is_absent'] ?? 0) == 1;

$mat2Grade =
    $calculateSubjectGrade(
        $mat2Mark,
        $math2Max,
        $mat2Absent
    );


/* ============================================================
   SCI1
============================================================ */

$sci1Detail =
    $sci1Id !== null
        ? ($details[$sci1Id] ?? null)
        : null;

$sci1Mark =
    $sci1Detail['marks'] ?? null;

$sci1Absent =
    ($sci1Detail['is_absent'] ?? 0) == 1;

$sci1Grade =
    $calculateSubjectGrade(
        $sci1Mark,
        $sci1Max,
        $sci1Absent
    );


/* ============================================================
   SCI2
============================================================ */

$sci2Detail =
    $sci2Id !== null
        ? ($details[$sci2Id] ?? null)
        : null;

$sci2Mark =
    $sci2Detail['marks'] ?? null;

$sci2Absent =
    ($sci2Detail['is_absent'] ?? 0) == 1;

$sci2Grade =
    $calculateSubjectGrade(
        $sci2Mark,
        $sci2Max,
        $sci2Absent
    );


/* ============================================================
   HISTORY
============================================================ */

$historyDetail =
    $historyId !== null
        ? ($details[$historyId] ?? null)
        : null;

$historyMark =
    $historyDetail['marks'] ?? null;

$historyAbsent =
    ($historyDetail['is_absent'] ?? 0) == 1;

$historyGrade =
    $calculateSubjectGrade(
        $historyMark,
        $historyMax,
        $historyAbsent
    );


/* ============================================================
   GEOGRAPHY
============================================================ */

$geographyDetail =
    $geographyId !== null
        ? ($details[$geographyId] ?? null)
        : null;

$geographyMark =
    $geographyDetail['marks'] ?? null;

$geographyAbsent =
    ($geographyDetail['is_absent'] ?? 0) == 1;

$geographyGrade =
    $calculateSubjectGrade(
        $geographyMark,
        $geographyMax,
        $geographyAbsent
    );


/* ============================================================
   EVS1
============================================================ */

$evs1Detail =
    $evs1Id !== null
        ? ($details[$evs1Id] ?? null)
        : null;

$evs1Mark =
    $evs1Detail['marks'] ?? null;

$evs1Absent =
    ($evs1Detail['is_absent'] ?? 0) == 1;

$evs1Grade =
    $calculateSubjectGrade(
        $evs1Mark,
        $evs1Max,
        $evs1Absent
    );


/* ============================================================
   EVS2
============================================================ */

$evs2Detail =
    $evs2Id !== null
        ? ($details[$evs2Id] ?? null)
        : null;

$evs2Mark =
    $evs2Detail['marks'] ?? null;

$evs2Absent =
    ($evs2Detail['is_absent'] ?? 0) == 1;

$evs2Grade =
    $calculateSubjectGrade(
        $evs2Mark,
        $evs2Max,
        $evs2Absent
    );


/* ============================================================
   OVERALL SUBJECT RESULT
============================================================ */

$hasFail = false;

$hasAbsent = false;


/* ------------------------------------------------------------
   NORMAL SUBJECTS
------------------------------------------------------------ */

foreach($normalSubjects as $subject) {

    $detail =
        $details[$subject->id] ?? null;

    $mark =
        $detail['marks'] ?? null;

    $isAbsent =
        ($detail['is_absent'] ?? 0) == 1;

    $maxMarks =
        (int)($subject->max_marks ?? 0);

    $grade =
        $calculateSubjectGrade(
            $mark,
            $maxMarks,
            $isAbsent
        );

    if($grade === 'AB') {
        $hasAbsent = true;
    }

    if($grade === 'F') {
        $hasFail = true;
    }
}


/* ------------------------------------------------------------
   COMPONENT SUBJECTS
------------------------------------------------------------ */

$componentGrades = [

    $mat1Grade,
    $mat2Grade,

    $sci1Grade,
    $sci2Grade,

    $historyGrade,
    $geographyGrade,

    $evs1Grade,
    $evs2Grade,

];


foreach($componentGrades as $grade) {

    if($grade === 'AB') {
        $hasAbsent = true;
    }

    if($grade === 'F') {
        $hasFail = true;
    }
}


/* ============================================================
   FINAL RESULT
============================================================ */

$finalResult =
    ($hasFail || $hasAbsent)
        ? 'FAIL'
        : 'PASS';


/* ============================================================
   OVERALL GRADE
============================================================ */

$overallTotal =
    (float)($row->academic_total ?? 0);

$overallMax =
    (float)$academicTotalMax;


/*
|--------------------------------------------------------------------------
| IMPORTANT
|--------------------------------------------------------------------------
| Overall grade is calculated from total marks.
|--------------------------------------------------------------------------
*/

$overallGrade =
    $calculateSubjectGrade(
        $overallTotal,
        $overallMax,
        false
    );

@endphp


<tr>

    {{-- =====================================================
         ROLL NUMBER
    ====================================================== --}}

    <td>
        {{ $row->roll_no ?? '-' }}
    </td>


    {{-- =====================================================
         STUDENT NAME
    ====================================================== --}}

    <td class="student-name">
        {{ $row->full_student_name ?? '-' }}
    </td>


    {{-- =====================================================
         NORMAL SUBJECTS
    ====================================================== --}}

    @foreach($normalSubjects as $subject)

        @php

        $detail =
            $details[$subject->id] ?? null;

        $mark =
            $detail['marks'] ?? null;

        $isAbsent =
            ($detail['is_absent'] ?? 0) == 1;

        $maxMarks =
            (int)($subject->max_marks ?? 0);

        /*
         * ALWAYS calculate from marks.
         */
        $subjectGrade =
            $calculateSubjectGrade(
                $mark,
                $maxMarks,
                $isAbsent
            );

        @endphp


        {{-- MARKS --}}

        <td>

            @if(
                $isAbsent ||
                strtoupper(trim((string)$mark)) === 'AB'
            )

                <span class="absent-mark">
                    AB
                </span>

            @elseif(
                $mark !== null &&
                $mark !== ''
            )

                {{ $displayMark($mark) }}

            @else

                -

            @endif

        </td>


        {{-- GRADE --}}

        <td class="grade-value">

            @if($subjectGrade === 'AB')

                <span class="absent-mark">
                    AB
                </span>

            @elseif($subjectGrade === 'F')

                <span class="fail-result">
                    F
                </span>

            @else

                {{ $subjectGrade }}

            @endif

        </td>

    @endforeach


    {{-- =====================================================
         MATHEMATICS
    ====================================================== --}}

    @if($math1Id !== null && $math2Id !== null)

        {{-- MAT1 MARK --}}

        <td>

            @if(
                $mat1Absent ||
                strtoupper(trim((string)$mat1Mark)) === 'AB'
            )

                <span class="absent-mark">
                    AB
                </span>

            @elseif(
                $mat1Mark !== null &&
                $mat1Mark !== ''
            )

                {{ $displayMark($mat1Mark) }}

            @else

                -

            @endif

        </td>


        {{-- MAT1 GRADE --}}

        <td class="grade-value">

            @if($mat1Grade === 'AB')

                <span class="absent-mark">
                    AB
                </span>

            @elseif($mat1Grade === 'F')

                <span class="fail-result">
                    F
                </span>

            @else

                {{ $mat1Grade }}

            @endif

        </td>


        {{-- MAT2 MARK --}}

        <td>

            @if(
                $mat2Absent ||
                strtoupper(trim((string)$mat2Mark)) === 'AB'
            )

                <span class="absent-mark">
                    AB
                </span>

            @elseif(
                $mat2Mark !== null &&
                $mat2Mark !== ''
            )

                {{ $displayMark($mat2Mark) }}

            @else

                -

            @endif

        </td>


        {{-- MAT2 GRADE --}}

        <td class="grade-value">

            @if($mat2Grade === 'AB')

                <span class="absent-mark">
                    AB
                </span>

            @elseif($mat2Grade === 'F')

                <span class="fail-result">
                    F
                </span>

            @else

                {{ $mat2Grade }}

            @endif

        </td>

    @endif


    {{-- =====================================================
         SCIENCE
    ====================================================== --}}

    @if($sci1Id !== null && $sci2Id !== null)

        {{-- SCI1 MARK --}}

        <td>

            @if(
                $sci1Absent ||
                strtoupper(trim((string)$sci1Mark)) === 'AB'
            )

                <span class="absent-mark">
                    AB
                </span>

            @elseif(
                $sci1Mark !== null &&
                $sci1Mark !== ''
            )

                {{ $displayMark($sci1Mark) }}

            @else

                -

            @endif

        </td>


        {{-- SCI1 GRADE --}}

        <td class="grade-value">

            @if($sci1Grade === 'AB')

                <span class="absent-mark">
                    AB
                </span>

            @elseif($sci1Grade === 'F')

                <span class="fail-result">
                    F
                </span>

            @else

                {{ $sci1Grade }}

            @endif

        </td>


        {{-- SCI2 MARK --}}

        <td>

            @if(
                $sci2Absent ||
                strtoupper(trim((string)$sci2Mark)) === 'AB'
            )

                <span class="absent-mark">
                    AB
                </span>

            @elseif(
                $sci2Mark !== null &&
                $sci2Mark !== ''
            )

                {{ $displayMark($sci2Mark) }}

            @else

                -

            @endif

        </td>


        {{-- SCI2 GRADE --}}

        <td class="grade-value">

            @if($sci2Grade === 'AB')

                <span class="absent-mark">
                    AB
                </span>

            @elseif($sci2Grade === 'F')

                <span class="fail-result">
                    F
                </span>

            @else

                {{ $sci2Grade }}

            @endif

        </td>

    @endif


    {{-- =====================================================
         SOCIAL SCIENCE
    ====================================================== --}}

    @if($historyId !== null && $geographyId !== null)

        {{-- HISTORY MARK --}}

        <td>

            @if(
                $historyAbsent ||
                strtoupper(trim((string)$historyMark)) === 'AB'
            )

                <span class="absent-mark">
                    AB
                </span>

            @elseif(
                $historyMark !== null &&
                $historyMark !== ''
            )

                {{ $displayMark($historyMark) }}

            @else

                -

            @endif

        </td>


        {{-- HISTORY GRADE --}}

        <td class="grade-value">

            @if($historyGrade === 'AB')

                <span class="absent-mark">
                    AB
                </span>

            @elseif($historyGrade === 'F')

                <span class="fail-result">
                    F
                </span>

            @else

                {{ $historyGrade }}

            @endif

        </td>


        {{-- GEOGRAPHY MARK --}}

        <td>

            @if(
                $geographyAbsent ||
                strtoupper(trim((string)$geographyMark)) === 'AB'
            )

                <span class="absent-mark">
                    AB
                </span>

            @elseif(
                $geographyMark !== null &&
                $geographyMark !== ''
            )

                {{ $displayMark($geographyMark) }}

            @else

                -

            @endif

        </td>


        {{-- GEOGRAPHY GRADE --}}

        <td class="grade-value">

            @if($geographyGrade === 'AB')

                <span class="absent-mark">
                    AB
                </span>

            @elseif($geographyGrade === 'F')

                <span class="fail-result">
                    F
                </span>

            @else

                {{ $geographyGrade }}

            @endif

        </td>

    @endif


    {{-- =====================================================
         EVS
    ====================================================== --}}

    @if($evs1Id !== null && $evs2Id !== null)

        {{-- EVS1 MARK --}}

        <td>

            @if(
                $evs1Absent ||
                strtoupper(trim((string)$evs1Mark)) === 'AB'
            )

                <span class="absent-mark">
                    AB
                </span>

            @elseif(
                $evs1Mark !== null &&
                $evs1Mark !== ''
            )

                {{ $displayMark($evs1Mark) }}

            @else

                -

            @endif

        </td>


        {{-- EVS1 GRADE --}}

        <td class="grade-value">

            @if($evs1Grade === 'AB')

                <span class="absent-mark">
                    AB
                </span>

            @elseif($evs1Grade === 'F')

                <span class="fail-result">
                    F
                </span>

            @else

                {{ $evs1Grade }}

            @endif

        </td>


        {{-- EVS2 MARK --}}

        <td>

            @if(
                $evs2Absent ||
                strtoupper(trim((string)$evs2Mark)) === 'AB'
            )

                <span class="absent-mark">
                    AB
                </span>

            @elseif(
                $evs2Mark !== null &&
                $evs2Mark !== ''
            )

                {{ $displayMark($evs2Mark) }}

            @else

                -

            @endif

        </td>


        {{-- EVS2 GRADE --}}

        <td class="grade-value">

            @if($evs2Grade === 'AB')

                <span class="absent-mark">
                    AB
                </span>

            @elseif($evs2Grade === 'F')

                <span class="fail-result">
                    F
                </span>

            @else

                {{ $evs2Grade }}

            @endif

        </td>

    @endif


    {{-- =====================================================
         SKILL
    ====================================================== --}}

    @if(
        $showSkillColumn &&
        request('exam_master_id') != 1
    )

        <td>

            @if(!empty($row->skill_subject))

                {{ $row->skill_subject }}

                @if(
                    $row->skill_mark !== null &&
                    $row->skill_mark !== ''
                )

                    @if(
                        strtoupper(
                            trim((string)$row->skill_mark)
                        ) === 'AB'
                    )

                        <span class="absent-mark">
                            (AB)
                        </span>

                    @else

                        (
                        {{
                            is_numeric($row->skill_mark)
                                ? (int)$row->skill_mark
                                : $row->skill_mark
                        }}
                        )

                    @endif

                @endif

            @else

                -

            @endif

        </td>

    @endif


    {{-- =====================================================
         TOTAL
    ====================================================== --}}

    <td>

        {{ (int)($row->academic_total ?? 0) }}

    </td>


    {{-- =====================================================
         PERCENTAGE
    ====================================================== --}}

    <td>

        {{ round($row->calculated_percentage ?? 0) }}

    </td>


    {{-- =====================================================
         OVERALL GRADE
    ====================================================== --}}

    <td class="grade-value">

        @if($overallGrade === 'AB')

            <span class="absent-mark">
                AB
            </span>

        @elseif($overallGrade === 'F')

            <span class="fail-result">
                F
            </span>

        @else

            {{ $overallGrade }}

        @endif

    </td>


    {{-- =====================================================
         RESULT
    ====================================================== --}}

    <td>

        @if($finalResult === 'PASS')

            <span class="pass-result">
                PASS
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

</div>


{{-- =========================================================
     RESULT ANALYSIS
========================================================= --}}

@if(
    !empty($girlsSubjectAnalysis) ||
    !empty($boysSubjectAnalysis)
)

<div style="
    margin-top:20px;
    overflow-x:auto;
">

    <h3 class="analysis-title">
        RESULT ANALYSIS
    </h3>


    {{-- =====================================================
         GIRLS
    ====================================================== --}}

    @if(!empty($girlsSubjectAnalysis))

        <h4 class="analysis-gender-title">
            Girls
        </h4>

        <table class="result-sheet-table">

            <thead>

                <tr>

                    <th>Subject</th>
                    <th>A1</th>
                    <th>A2</th>
                    <th>B1</th>
                    <th>B2</th>
                    <th>C1</th>
                    <th>C2</th>
                    <th>D</th>
                    <th>Fail</th>
                    <th>Absent</th>
                    <th>Total</th>

                </tr>

            </thead>

            <tbody>

                @foreach($girlsSubjectAnalysis as $analysis)

                    <tr>

                        <td style="
                            text-align:left !important;
                            font-weight:600;
                        ">

                            {{ $analysis['subject'] }}

                        </td>

                        <td>
                            {{ $analysis['A1'] ?? 0 }}
                        </td>

                        <td>
                            {{ $analysis['A2'] ?? 0 }}
                        </td>

                        <td>
                            {{ $analysis['B1'] ?? 0 }}
                        </td>

                        <td>
                            {{ $analysis['B2'] ?? 0 }}
                        </td>

                        <td>
                            {{ $analysis['C1'] ?? 0 }}
                        </td>

                        <td>
                            {{ $analysis['C2'] ?? 0 }}
                        </td>

                        <td>
                            {{ $analysis['D'] ?? 0 }}
                        </td>

                        <td class="fail-result">
                            {{ $analysis['fail'] ?? 0 }}
                        </td>

                        <td class="absent-mark">
                            {{ $analysis['absent'] ?? 0 }}
                        </td>

                        <td>
                            {{ $analysis['total'] ?? 0 }}
                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

    @endif


    {{-- =====================================================
         BOYS
    ====================================================== --}}

    @if(!empty($boysSubjectAnalysis))

        <h4 class="analysis-gender-title"
            style="margin-top:15px;">

            Boys

        </h4>

        <table class="result-sheet-table">

            <thead>

                <tr>

                    <th>Subject</th>
                    <th>A1</th>
                    <th>A2</th>
                    <th>B1</th>
                    <th>B2</th>
                    <th>C1</th>
                    <th>C2</th>
                    <th>D</th>
                    <th>Fail</th>
                    <th>Absent</th>
                    <th>Total</th>

                </tr>

            </thead>

            <tbody>

                @foreach($boysSubjectAnalysis as $analysis)

                    <tr>

                        <td style="
                            text-align:left !important;
                            font-weight:600;
                        ">

                            {{ $analysis['subject'] }}

                        </td>

                        <td>
                            {{ $analysis['A1'] ?? 0 }}
                        </td>

                        <td>
                            {{ $analysis['A2'] ?? 0 }}
                        </td>

                        <td>
                            {{ $analysis['B1'] ?? 0 }}
                        </td>

                        <td>
                            {{ $analysis['B2'] ?? 0 }}
                        </td>

                        <td>
                            {{ $analysis['C1'] ?? 0 }}
                        </td>

                        <td>
                            {{ $analysis['C2'] ?? 0 }}
                        </td>

                        <td>
                            {{ $analysis['D'] ?? 0 }}
                        </td>

                        <td class="fail-result">
                            {{ $analysis['fail'] ?? 0 }}
                        </td>

                        <td class="absent-mark">
                            {{ $analysis['absent'] ?? 0 }}
                        </td>

                        <td>
                            {{ $analysis['total'] ?? 0 }}
                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

    @endif

</div>

@endif

</div>

@endif

@endsection

