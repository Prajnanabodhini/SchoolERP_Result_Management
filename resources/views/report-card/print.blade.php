<!DOCTYPE html>
<html>
<head>

<meta charset="utf-8">

<title>Report Card</title>

<style>

body{
    font-family:Arial,sans-serif;
    margin:15px;
    color:#000;
}

.school-title{
    text-align:center;
    font-size:22px;
    font-weight:bold;
}

.report-title{
    text-align:center;
    font-size:18px;
    margin-top:5px;
    margin-bottom:15px;
}

table{
    width:100%;
    border-collapse:collapse;
}

th,td{
    border:1px solid #000;
    padding:6px;
    font-size:13px;
}

.info td{
    border:none;
    padding:4px;
}

.footer{
    margin-top:40px;
    display:flex;
    justify-content:space-between;
}

@media print{
    @page{
        size:A4;
        margin:10mm;
    }
}

</style>

</head>

<body>

<div class="school-title">
    PRAJNANABODHINI ENGLISH MEDIUM SCHOOL & JR. COLLEGE
</div>

<div class="report-title">
    REPORT CARD
</div>

<table class="info">

<tr>
<td><strong>Student :</strong></td>
<td>{{ $report->full_student_name }}</td>

<td><strong>Roll No :</strong></td>
<td>{{ $report->rollno }}</td>
</tr>

<tr>
<td><strong>Standard :</strong></td>
<td>{{ $report->standard_name }}</td>

<td><strong>Division :</strong></td>
<td>{{ $report->division_name }}</td>
</tr>

<tr>
<td><strong>Exam :</strong></td>
<td>{{ $report->exam_name }}</td>

<td><strong>Academic Year :</strong></td>
<td>{{ $report->year_name }}</td>
</tr>

</table>

<br>

<table>

<thead>
<tr>
<th>Sr</th>
<th>Subject</th>
<th>Max</th>
<th>Pass</th>
<th>Obtained</th>
</tr>
</thead>

<tbody>

@foreach($subjects as $index=>$subject)

<tr>
<td>{{ $index+1 }}</td>
<td>{{ $subject->subject_name }}</td>
<td>{{ $subject->max_marks }}</td>
<td>{{ $subject->passing_marks }}</td>
<td>{{ $subject->obtained_marks }}</td>
</tr>

@endforeach

</tbody>

</table>

@php

$totalMax=$subjects->sum('max_marks');
$totalObt=$subjects->sum('obtained_marks');

$percentage=
$totalMax>0
? round(($totalObt*100)/$totalMax,2)
:0;

@endphp

<br>

<table>

<tr>
<td><strong>Total Marks</strong></td>
<td>{{ $totalMax }}</td>

<td><strong>Obtained</strong></td>
<td>{{ $totalObt }}</td>

<td><strong>Percentage</strong></td>
<td>{{ $percentage }}%</td>
</tr>

</table>

<div class="footer">

<div>
_____________________<br>
Class Teacher
</div>

<div>
_____________________<br>
Principal
</div>

</div>

<script>
window.onload=function(){
    window.print();
};
</script>

</body>
</html>