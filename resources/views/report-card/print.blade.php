<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report Card</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 15px; color: #000; }
        .school-title { text-align: center; font-size: 22px; font-weight: bold; }
        .report-title { text-align: center; font-size: 18px; margin-top: 5px; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px; font-size: 13px; }
        .info td { border: none; padding: 4px; }
        .footer { margin-top: 40px; display: flex; justify-content: space-between; }
        @media print { @page { size: A4; margin: 10mm; } .no-print { display: none !important; } }
    </style>
</head>
<body>

    <div class="school-title">PRAJNANABODHINI ENGLISH MEDIUM SCHOOL &amp; JR. COLLEGE</div>
    <div class="report-title">REPORT CARD</div>

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
                <th>Result</th>
            </tr>
        </thead>
        <tbody>
            @foreach($subjects as $index => $subject)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $subject->subject_name }}</td>
                    <td>{{ $subject->max_marks > 0 ? (int) $subject->max_marks : '-' }}</td>
                    <td>{{ $subject->passing_marks > 0 ? (int) $subject->passing_marks : '-' }}</td>
                    <td>{{ $subject->subject_result === '-' ? '-' : (int) $subject->obtained_marks }}</td>
                    <td>{{ $subject->subject_result }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br>

    <table>
        <tr>
            <td><strong>Total Marks</strong></td>
            <td>{{ number_format((float) ($report->total_max_marks ?? 0), 2) }}</td>
            <td><strong>Obtained</strong></td>
            <td>{{ number_format((float) ($report->total_obtained_marks ?? 0), 2) }}</td>
            <td><strong>Percentage</strong></td>
            <td>{{ $report->percentage !== null ? number_format((float) $report->percentage, 2) . '%' : '-' }}</td>
            <td><strong>Result</strong></td>
            <td>{{ $report->result ?? '-' }}</td>
        </tr>
    </table>

    <div class="footer">
        <div>_____________________<br>Class Teacher</div>
        <div>_____________________<br>Principal</div>
    </div>

    <div class="no-print" style="position:fixed;top:12px;right:12px;display:flex;gap:6px;">
        <a href="javascript:window.print()" style="padding:6px 14px;background:#16a34a;color:#fff;text-decoration:none;border-radius:4px;font-weight:600;font-size:12px;">Print</a>
        <a href="javascript:history.back()" style="padding:6px 14px;background:#6b7280;color:#fff;text-decoration:none;border-radius:4px;font-weight:600;font-size:12px;">Back</a>
    </div>

    <script>window.addEventListener('load', function () { window.print(); });</script>
</body>
</html>