@extends('layouts.app')

@section('content')

<div class="erp-page">

    <div class="erp-card no-print">

        <h2 class="text-xl font-bold text-blue-700 mb-4">
            Student Report Card
        </h2>

        

<hr class="my-3">

<div style="
    display:flex;
    flex-direction:column;
    gap:10px;
    font-size:12px;
">

    <form method="POST"
      action="{{ route('report-card.search') }}"
      style="
        display:flex;
        align-items:center;
        gap:6px;
        flex-wrap:wrap;
      ">

        @csrf

        <label>Academic Year</label>

        <select name="academic_year_id"
                class="border rounded px-1 py-1"
                style="width:140px;"
                required>
            <option value="">Select</option>

            @foreach($academicYears as $year)
                <option value="{{ $year->id }}"
                    {{ ($academic_year_id ?? '') == $year->id ? 'selected' : '' }}>
                    {{ $year->year_name }}
                </option>
            @endforeach
        </select>

        <label>Exam</label>

        <select name="exam_master_id"
                class="border rounded px-1 py-1"
                style="width:140px;"
                required>
            <option value="">Select</option>

            @foreach($exams as $exam)
                <option value="{{ $exam->id }}"
                    {{ ($exam_master_id ?? '') == $exam->id ? 'selected' : '' }}>
                    {{ $exam->exam_name }}
                </option>
            @endforeach
        </select>

        <label>Standard</label>

        <select name="standard_id"
                class="border rounded px-1 py-1"
                style="width:120px;"
                required>
            <option value="">Select</option>

            @foreach($standards as $standard)
                <option value="{{ $standard->id }}"
                    {{ ($standard_id ?? '') == $standard->id ? 'selected' : '' }}>
                    {{ $standard->standard_name }}
                </option>
            @endforeach
        </select>

        <label>Division</label>

        <select name="division_id"
                class="border rounded px-1 py-1"
                style="width:90px;"
                required>
            <option value="">Select</option>

            @foreach($divisions as $division)
                <option value="{{ $division->id }}"
                    {{ ($division_id ?? '') == $division->id ? 'selected' : '' }}>
                    {{ $division->division_name }}
                </option>
            @endforeach
        </select>

        <button type="submit"
                class="erp-btn erp-btn-save"
                style="height:30px;padding:0 12px;">
            Search
        </button>

    </form>

    @if(isset($students) && count($students))

    <form method="POST"
      action="{{ route('report-card.show') }}"
      style="
        display:flex;
        align-items:center;
        gap:6px;
        flex-wrap:wrap;
      ">

        @csrf

        <input type="hidden"
               name="academic_year_id"
               value="{{ $academic_year_id }}">

        <input type="hidden"
               name="exam_master_id"
               value="{{ $exam_master_id }}">

        <input type="hidden"
               name="standard_id"
               value="{{ $standard_id }}">

        <input type="hidden"
               name="division_id"
               value="{{ $division_id }}">

        <label>Student</label>

        <select name="student_id"
                class="border rounded px-1 py-1"
                style="width:300px;"
                required>

            <option value="">Select Student</option>

            @foreach($students as $student)
                <option value="{{ $student->Studentid }}">
                    {{ $student->Studentid }} - {{ $student->studname }}
                </option>
            @endforeach

        </select>

        <button type="submit"
                class="erp-btn erp-btn-add"
                style="height:30px;padding:0 12px;">
            View Report Card
        </button>

        @if(isset($report) && $report)

        <a href="{{ route('report-card.print',[
            'student' => $report->student_id,
            'exam'    => $report->exam_master_id,
            'year'    => $report->academic_year_id
        ]) }}"
           target="_blank"
           class="erp-btn erp-btn-save"
           style="
                height:30px;
                line-height:30px;
                padding:0 12px;
                text-decoration:none;
                display:inline-block;
           ">
            Print Report Card
        </a>

        @endif

    </form>

    @endif

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const studentSelect =
        document.querySelector('select[form="viewReportForm"]');

    const hiddenStudent =
        document.getElementById('student_id_hidden');

    if(studentSelect && hiddenStudent)
    {
        studentSelect.addEventListener('change', function(){
            hiddenStudent.value = this.value;
        });

        hiddenStudent.value = studentSelect.value;
    }
});
</script>

@if(isset($report) && $report)
<div style="
    border:2px solid #000;
    padding:10px;
    margin-top:10px;
">
<div id="report-card-print">

{{-- <div class="erp-card mt-4"> --}}

    <div class="flex items-center justify-center gap-4">
{{-- 
    <img src="{{ asset('images/school-logo.png') }}"
         alt="School Logo"
         style="height:80px;width:auto;">

    <div class="text-center">

        <h2 class="text-2xl font-bold">
            PRAJNANABODHINI ENGLISH MEDIUM SCHOOL & JR. COLLEGE
        </h2>

        <h3 class="text-lg font-semibold">
            SHIRGAON / CHIKHALI
        </h3> --}}

        <hr style="
    border:1px solid #000;
    margin-top:8px;
    margin-bottom:8px;
">

<h3 style="
    font-size:20px;
    font-weight:bold;
    color:#000;
    text-align:center;
">
    REPORT CARD
</h3>

    {{-- </div> --}}
    </div>
    {{-- <hr class="my-4"> --}}

    <div style="
    border:1px solid #000;
    padding:4px;
    margin-top:4px;
">

    <table
    class="w-full"
    style="
        font-size:14px;
        border-collapse:collapse;
    ">

        <tr>
            <td width="18%">
                <span style="font-weight:bold;color:#000;">
    Student ID :
</span>
            </td>
            <td width="32%">
                {{ $report->student_id }}
            </td>

            <td width="18%">
                <strong>Roll No :</strong>
            </td>
            <td width="32%">
                {{ $report->rollno }}
            </td>
        </tr>

        <tr>
            <td>
                <span style="font-weight:bold;color:#000;">
    Student Name :
</span>
                
            </td>
            <td>
                {{ $report->full_student_name }}
            </td>

            <td>
                <strong>Academic Year :</strong>
            </td>
            <td>
                {{ $report->year_name }}
            </td>
        </tr>

        <tr>
            <td>
                <span style="font-weight:bold;color:#000;">
    Exam :
</span>
            </td>
            <td>
                {{ $report->exam_name }}
            </td>

            <td>
                <strong>Rank :</strong>
            </td>
            <td>
                {{ $report->rank }}
            </td>
        </tr>

        <tr>
            <td>
                <span style="font-weight:bold;color:#000;">
    Standard :
</span>
            </td>
            <td>
                {{ $report->standard_name }}
            </td>

            <td>
                <strong>Division :</strong>
            </td>
            <td>
                {{ $report->division_name }}
            </td>
        </tr>

    </table>

</div>

       

        <hr class="my-4">

    <table
    class="w-full report-table"
    style="
        border-collapse:collapse;
        border:1px solid #000;
    ">

        <thead>
<tr>
    <th style="border:1px solid #000;padding:4px;" text-center">Sr</th>
    <th style="border:1px solid #000;padding:4px;"" style="text-align:left;">Subject</th>
    <th style="border:1px solid #000;padding:4px;" text-center">Max Marks</th>
    <th style="border:1px solid #000;padding:4px;" text-center">Passing</th>
    <th style="border:1px solid #000;padding:4px;" text-center">Obtained</th>    
    <th style="border:1px solid #000;padding:4px;" text-center">Result</th>
</tr>
</thead>
        <tbody>

@foreach($subjects as $index => $subject)

<tr>

    <td style="border:1px solid #000;padding:4px;text-align:center;">
        {{ $index + 1 }}
    </td>

    <td style="border:1px solid #000;padding:4px;" text-left">
        {{ $subject->subject_name }}
    </td>

    <td style="border:1px solid #000;padding:4px;text-align:center;">
    {{ $subject->max_marks }}
</td>

<td style="border:1px solid #000;padding:4px;text-align:center;">
    {{ $subject->passing_marks }}
</td>

<td style="border:1px solid #000;padding:4px;text-align:center;">
    {{ $subject->obtained_marks }}
</td>

<td style="border:1px solid #000;padding:4px;text-align:center;">
    {{ $subject->subject_result }}
</td>
</tr>

@endforeach

</tbody>
    </table>

    <hr class="my-4">

    <div class="grid grid-cols-2 gap-3">
@php

$totalMaxMarks =
    $subjects->sum('max_marks');

$totalObtainedMarks =
    $subjects->sum('obtained_marks');

$percentage =
    $totalMaxMarks > 0
    ? round(
        ($totalObtainedMarks * 100)
        / $totalMaxMarks,
        2
      )
    : 0;

@endphp
    </div>
<div></div>
<div class="flex items-left justify-left gap-4">
        <div class="flex items-left justify-left gap-4">
            <strong>Total Marks :</strong>
            {{ number_format($totalMaxMarks,2) }}
        </div>

        <div>
            <strong>Total Obtained Marks :</strong>
            {{ number_format($totalObtainedMarks,2) }}
        </div>

        <div>
            <strong>Percentage :</strong>
            {{ number_format($percentage,2) }} %
        </div>

        <div>
            <strong>Grade :</strong>
            {{ $report->grade }}
        </div>

        <div>
            <strong>Result :</strong>

            @if($report->result == 'PASS')

                <span class="text-green-700 font-bold">
                    PASS
                </span>

            @else

                <span class="text-red-700 font-bold">
                    FAIL
                </span>

            @endif

        </div>

    </div>
    </div>
    <div style="margin-top:20px;" class="flex justify-center gap-20">

    <div class="text-center">
        <div style="width:180px;">
            _____________________
        </div>
        <div class="mt-2 font-semibold">
            Class Teacher
        </div>
    </div>

    <div class="text-center">
        <div style="width:180px;">
            _____________________
        </div>
        <div class="mt-2 font-semibold">
            Principal
        </div>
    </div>

</div>
</div>
</div>
@endif

</div>

@endsection

<style>

@media print
{
    /* nav,
    .no-print,
    .bg-blue-100,
    .school-header
    {
        display:none !important;
    } */
    main
    {
        min-height:auto !important;
        height:auto !important;
        padding:0 !important;
        margin:0 !important;
    }
    #report-card-print
    {
        margin:0 !important;
        padding:0 !important;
        page-break-after:avoid !important;
    }
    @page
    {
        size:A4 portrait;
        margin:8mm;
    }
.report-table td,
.report-table th
{
    font-size:14px;
}
    nav,
    .no-print
    {
        display:none !important;
    }

    html,
    body
    {
        background:#fff !important;
        overflow:visible !important;
        height:auto !important;
        margin:0 !important;
        padding:0 !important;
        font-size:12px !important;
    }

    .erp-page,
    .erp-card
    {
        margin:0 !important;
        padding:0 !important;
        border:none !important;
        box-shadow:none !important;
        overflow:visible !important;
        height:auto !important;
    }

    table
    {
        page-break-inside:auto;
    }

    tr
    {
        page-break-inside:avoid;
    }

    th,
    td
    {
        padding:2px !important;
    }

    hr
    {
        margin:5px 0 !important;
    }

    .mt-20
    {
        margin-top:30px !important;
    }

    .mt-4
    {
        margin-top:5px !important;
    }

    .my-4
    {
        margin-top:5px !important;
        margin-bottom:5px !important;
    }
}

</style>