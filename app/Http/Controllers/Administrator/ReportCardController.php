<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\ExamMaster;
use App\Models\Standard;
use App\Models\StudentProgressCard;
use App\Models\StudentSkillMark;
use App\Helpers\StudentHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportCardController extends Controller
{
    /* ======================================================================
     | INDEX
     ====================================================================== */

    public function index()
    {
        $exams         = ExamMaster::all();
        $standards     = Standard::all();
        $divisions     = Division::all();
        $academicYears = AcademicYear::orderByDesc('year_name')->get();

        $students      = collect();
        $report        = null;
        $subjects      = collect();
        $error         = null;

        return view('report-card.index', compact(
            'academicYears', 'exams', 'standards', 'divisions',
            'students', 'report', 'subjects', 'error'
        ));
    }


    /* ======================================================================
     | SEARCH
     ====================================================================== */

    public function search(Request $request)
    {
        $exams         = ExamMaster::all();
        $standards     = Standard::all();
        $divisions     = Division::all();
        $academicYears = AcademicYear::orderByDesc('year_name')->get();

        $students      = collect();
        $report        = null;
        $subjects      = collect();
        $error         = null;

        if (
            $request->filled('academic_year_id') &&
            $request->filled('standard_id') &&
            $request->filled('division_id')
        ) {
            $academicYear = AcademicYear::find($request->academic_year_id);
            if ($academicYear) {
                $yearId = $academicYear->old_year_id
                    ?? substr($academicYear->year_name, 0, 4);
                $students = StudentHelper::getStudentsDirectERP(
                    $yearId,
                    $request->standard_id,
                    $request->division_id
                );
            }
        }

        return view('report-card.index', compact(
            'academicYears', 'exams', 'standards', 'divisions',
            'students', 'report', 'subjects', 'error'
        ))->with([
            'academic_year_id' => $request->academic_year_id,
            'exam_master_id'   => $request->exam_master_id,
            'standard_id'      => $request->standard_id,
            'division_id'      => $request->division_id,
        ]);
    }


    /* ======================================================================
     | SHOW
     ====================================================================== */

    public function show(Request $request)
    {
        set_time_limit(300);

        $exams         = ExamMaster::all();
        $standards     = Standard::all();
        $divisions     = Division::all();
        $academicYears = AcademicYear::orderByDesc('year_name')->get();

        $students      = collect();
        $report        = null;
        $subjects      = collect();
        $error         = null;

        if (
            $request->academic_year_id &&
            $request->standard_id &&
            $request->division_id
        ) {
            $academicYear = AcademicYear::find($request->academic_year_id);
            if ($academicYear) {
                $yearId = $academicYear->old_year_id
                    ?? substr($academicYear->year_name, 0, 4);
                $students = StudentHelper::getStudentsDirectERP(
                    $yearId,
                    $request->standard_id,
                    $request->division_id
                );
            }
        }

        if (!$request->filled('student_id')) {
            return view('report-card.index', compact(
                'academicYears', 'exams', 'standards', 'divisions',
                'students', 'report', 'subjects', 'error'
            ))->with([
                'error'            => 'Please select a Student.',
                'academic_year_id' => $request->academic_year_id,
                'exam_master_id'   => $request->exam_master_id,
                'standard_id'      => $request->standard_id,
                'division_id'      => $request->division_id,
            ]);
        }

        $student = DB::table('feemststudent')->where('Studentid', $request->student_id)->first();
        if (!$student) {
            return view('report-card.index', compact(
                'academicYears', 'exams', 'standards', 'divisions',
                'students', 'report', 'subjects', 'error'
            ))->with([
                'error'            => 'Student not found in ERP.',
                'academic_year_id' => $request->academic_year_id,
                'exam_master_id'   => $request->exam_master_id,
                'standard_id'      => $request->standard_id,
                'division_id'      => $request->division_id,
            ]);
        }

        $substudent = DB::table('substudentmst')->where('Studentid', $request->student_id)->first();
        $exam       = DB::table('exam_masters')->where('id', $request->exam_master_id)->first();
        $standard   = DB::table('standards')->where('id', $request->standard_id)->first();
        $division   = DB::table('divisions')->where('id', $request->division_id)->first();
        $year       = DB::table('academic_years')->where('id', $request->academic_year_id)->first();

        $progress = StudentProgressCard::where('student_id', $request->student_id)
            ->where('exam_master_id', $request->exam_master_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->first();

        $manualGrades = $progress->second_term_grades ?? [];

        $fullName = trim(
            (string) ($student->studname   ?? '') . ' ' .
            (string) ($student->fathername ?? '')
        );

        $report = (object) [
            'student_id'            => (int) $request->student_id,
            'full_student_name'     => $fullName,
            'father_name'           => $student->fathername  ?? '',
            'mother_name'           => $student->mothername  ?? '',
            'gender'                => $student->gender      ?? '',
            'date_of_birth'         => $student->birthdate   ?? '',
            'mother_tongue'         => $student->mtounge     ?? 'MARATHI',
            'address'               => $student->locaddr     ?? '',
            'rollno'                => $substudent->rollno   ?? '',
            'exam_master_id'        => (int) $request->exam_master_id,
            'academic_year_id'      => (int) $request->academic_year_id,
            'standard_id'           => (int) $request->standard_id,
            'division_id'           => (int) $request->division_id,
            'exam_name'             => $exam->exam_name         ?? '',
            'standard_name'         => $standard->standard_name ?? '',
            'division_name'         => $division->division_name ?? '',
            'year_name'             => $year->year_name         ?? '',
            'rank'                  => null,
            'percentage'            => null,
            'grade'                 => null,
            'result'                => null,
            'total_max_marks'       => 0,
            'total_obtained_marks'  => 0,
        ];

        $subjects = $this->buildReportCardSubjects(
            (int) $request->student_id,
            (int) $request->exam_master_id,
            (int) $request->standard_id,
            (int) $request->academic_year_id,
            $manualGrades
        );

        [$pct, $grade, $result, $tMax, $tObt] = $this->computeOverall($subjects);

        $report->percentage           = $pct;
        $report->grade                = $grade;
        $report->result               = $result;
        $report->total_max_marks      = $tMax;
        $report->total_obtained_marks = $tObt;

        return view('report-card.index', compact(
            'academicYears', 'exams', 'standards', 'divisions',
            'students', 'report', 'subjects', 'error'
        ))->with([
            'academic_year_id' => $request->academic_year_id,
            'exam_master_id'   => $request->exam_master_id,
            'standard_id'      => $request->standard_id,
            'division_id'      => $request->division_id,
        ]);
    }


    /* ======================================================================
     | PRINT
     ====================================================================== */

    public function print($studentId, $examId, $yearId)
    {
        $student = DB::table('feemststudent')->where('Studentid', $studentId)->first();
        if (!$student) abort(404);

        $substudent = DB::table('substudentmst')->where('Studentid', $studentId)->first();
        $exam       = DB::table('exam_masters')->where('id', $examId)->first();
        $year       = DB::table('academic_years')->where('id', $yearId)->first();

        $sampleMark = DB::table('student_marks')
            ->where('student_id', $studentId)
            ->where('exam_master_id', $examId)
            ->first();

        $standardId = $sampleMark->standard_id ?? $exam->standard_id ?? null;
        $standard   = $standardId ? DB::table('standards')->where('id', $standardId)->first() : null;
        $division   = $sampleMark ? DB::table('divisions')->where('id', $sampleMark->division_id)->first() : null;

        $progress     = StudentProgressCard::where('student_id', $studentId)
            ->where('exam_master_id', $examId)
            ->where('academic_year_id', $yearId)
            ->first();

        $manualGrades = $progress->second_term_grades ?? [];

        $fullName = trim(
            (string) ($student->studname   ?? '') . ' ' .
            (string) ($student->fathername ?? '')
        );

        $report = (object) [
            'student_id'            => $studentId,
            'full_student_name'     => $fullName,
            'rollno'                => $substudent->rollno ?? '',
            'exam_name'             => $exam->exam_name ?? '',
            'standard_name'         => $standard->standard_name ?? '',
            'division_name'         => $division->division_name ?? '',
            'year_name'             => $year->year_name ?? '',
            'percentage'            => null,
            'grade'                 => null,
            'result'                => null,
            'total_max_marks'       => 0,
            'total_obtained_marks'  => 0,
        ];

        $subjects = $this->buildReportCardSubjects(
            (int) $studentId,
            (int) $examId,
            (int) $standardId,
            (int) $yearId,
            $manualGrades
        );

        [$pct, $grade, $result, $tMax, $tObt] = $this->computeOverall($subjects);

        $report->percentage           = $pct;
        $report->grade                = $grade;
        $report->result               = $result;
        $report->total_max_marks      = $tMax;
        $report->total_obtained_marks = $tObt;

        return view('report-card.print', compact('report', 'subjects'));
    }


    /* ======================================================================
     | PROGRESS CARD — PRINT
     ====================================================================== */

    public function printProgressCard($studentId, $examId, $yearId)
    {
        set_time_limit(300);

        $student = DB::table('feemststudent')->where('Studentid', $studentId)->first();
        if (!$student) abort(404, 'Student not found in ERP.');

        $substudent = DB::table('substudentmst')->where('Studentid', $studentId)->first();
        $exam       = DB::table('exam_masters')->where('id', $examId)->first();
        $year       = DB::table('academic_years')->where('id', $yearId)->first();

        $sampleMark = DB::table('student_marks')
            ->where('student_id', $studentId)
            ->where('exam_master_id', $examId)
            ->first();

        $standardId = $sampleMark->standard_id ?? $exam->standard_id ?? null;
        $divisionId = $sampleMark->division_id ?? null;

        $standard = $standardId ? DB::table('standards')->where('id', $standardId)->first() : null;
        $division = $divisionId ? DB::table('divisions')->where('id', $divisionId)->first() : null;

        $examName  = strtoupper(trim((string) ($exam->exam_name ?? '')));
        $termLabel = 'Exam';
        if (str_contains($examName, 'TERM 1'))      $termLabel = 'Term 1';
        elseif (str_contains($examName, 'TERM 2'))  $termLabel = 'Term 2';
        elseif (str_contains($examName, 'UNIT TEST 1')) $termLabel = 'Unit Test 1';
        elseif (str_contains($examName, 'UNIT TEST 2')) $termLabel = 'Unit Test 2';
        elseif (str_contains($examName, 'UNIT TEST 3')) $termLabel = 'Unit Test 3';
        elseif (str_contains($examName, 'UNIT TEST 4')) $termLabel = 'Unit Test 4';
        elseif (str_contains($examName, 'ANNUAL'))  $termLabel = 'Annual';

        $progress = StudentProgressCard::where('student_id', $studentId)
            ->where('exam_master_id', $examId)
            ->where('academic_year_id', $yearId)
            ->first();

        if (!$progress) {
            $progress                      = new StudentProgressCard();
            $progress->attendance_data     = $this->defaultAttendanceData();
            $progress->descriptive_records = [];
            $progress->second_term_grades  = [];
        }

        $manualGrades = $progress->second_term_grades ?? [];

        $fullName = trim(
            (string) ($student->studname   ?? '') . ' ' .
            (string) ($student->fathername ?? '')
        );

        $report = (object) [
            'student_id'            => (int) $studentId,
            'full_student_name'     => $fullName,
            'father_name'           => $student->fathername  ?? '',
            'mother_name'           => $student->mothername  ?? '',
            'gender'                => $student->gender      ?? '',
            'date_of_birth'         => $student->birthdate   ?? '',
            'mother_tongue'         => $student->mtounge     ?? 'MARATHI',
            'address'               => $student->locaddr     ?? '',
            'rollno'                => $substudent->rollno   ?? '',
            'exam_master_id'        => (int) $examId,
            'academic_year_id'      => (int) $yearId,
            'standard_id'           => (int) ($standardId ?? 0),
            'division_id'           => (int) ($divisionId ?? 0),
            'exam_name'             => $exam->exam_name         ?? '',
            'standard_name'         => $standard->standard_name ?? '',
            'division_name'         => $division->division_name ?? '',
            'year_name'             => $year->year_name         ?? '',
            'rank'                  => null,
            'percentage'            => null,
            'grade'                 => null,
            'result'                => null,
            'total_max_marks'       => 0,
            'total_obtained_marks'  => 0,
        ];

        $subjects = $this->buildProgressCardSubjects($studentId, $examId, $standardId, $yearId, $manualGrades);

        [$pct, $grade, $result, $tMax, $tObt] = $this->computeOverall($subjects);

        $report->percentage           = $pct;
        $report->grade                = $grade;
        $report->result               = $result;
        $report->total_max_marks      = $tMax;
        $report->total_obtained_marks = $tObt;

        return view(
            'report-card.progress-card',
            compact('report', 'subjects', 'progress', 'termLabel')
        );
    }


    /* ======================================================================
     | PROGRESS CARD — EDIT
     ====================================================================== */

    public function editProgressDetails($studentId, $examId, $yearId)
    {
        $student = DB::table('feemststudent')->where('Studentid', $studentId)->first();
        if (!$student) abort(404, 'Student not found.');

        $substudent = DB::table('substudentmst')->where('Studentid', $studentId)->first();
        $exam       = DB::table('exam_masters')->where('id', $examId)->first();
        $year       = DB::table('academic_years')->where('id', $yearId)->first();

        $sampleMark = DB::table('student_marks')
            ->where('student_id', $studentId)
            ->where('exam_master_id', $examId)
            ->first();

        $standardId = $sampleMark->standard_id ?? $exam->standard_id ?? null;
        $divisionId = $sampleMark->division_id ?? null;

        $standard = $standardId ? DB::table('standards')->where('id', $standardId)->first() : null;
        $division = $divisionId ? DB::table('divisions')->where('id', $divisionId)->first() : null;

        $examName  = strtoupper(trim((string) ($exam->exam_name ?? '')));
        $termLabel = 'Exam';
        if (str_contains($examName, 'TERM 1'))      $termLabel = 'Term 1';
        elseif (str_contains($examName, 'TERM 2'))  $termLabel = 'Term 2';
        elseif (str_contains($examName, 'UNIT TEST 1')) $termLabel = 'Unit Test 1';
        elseif (str_contains($examName, 'UNIT TEST 2')) $termLabel = 'Unit Test 2';
        elseif (str_contains($examName, 'UNIT TEST 3')) $termLabel = 'Unit Test 3';
        elseif (str_contains($examName, 'UNIT TEST 4')) $termLabel = 'Unit Test 4';
        elseif (str_contains($examName, 'ANNUAL'))  $termLabel = 'Annual';

        $progress = StudentProgressCard::where('student_id', $studentId)
            ->where('exam_master_id', $examId)
            ->where('academic_year_id', $yearId)
            ->first();

        if (!$progress) {
            $progress                      = new StudentProgressCard();
            $progress->attendance_data     = $this->defaultAttendanceData();
            $progress->descriptive_records = [];
            $progress->second_term_grades  = [];
        }

        $manualGrades = $progress->second_term_grades ?? [];

        $fullName = trim(
            (string) ($student->studname   ?? '') . ' ' .
            (string) ($student->fathername ?? '')
        );

        $report = (object) [
            'student_id'        => (int) $studentId,
            'full_student_name' => $fullName,
            'father_name'       => $student->fathername ?? '',
            'mother_name'       => $student->mothername ?? '',
            'mother_tongue'     => $student->mtounge    ?? 'MARATHI',
            'rollno'            => $substudent->rollno  ?? '',
            'exam_master_id'    => (int) $examId,
            'academic_year_id'  => (int) $yearId,
            'exam_name'         => $exam->exam_name         ?? '',
            'standard_name'     => $standard->standard_name ?? '',
            'division_name'     => $division->division_name ?? '',
            'year_name'         => $year->year_name         ?? '',
        ];

        $subjects = $this->buildProgressCardSubjects($studentId, $examId, $standardId, $yearId, $manualGrades);

        return view(
            'report-card.progress-card-edit',
            compact('report', 'subjects', 'progress', 'termLabel')
        );
    }


    /* ======================================================================
     | PROGRESS CARD — SAVE
     ====================================================================== */

    public function saveProgressDetails(Request $request, $studentId, $examId, $yearId)
    {
        $sampleMark = DB::table('student_marks')
            ->where('student_id', $studentId)
            ->where('exam_master_id', $examId)
            ->first();

        $standardId = $sampleMark->standard_id ?? null;
        $divisionId = $sampleMark->division_id ?? null;

        $months = [
            'Jyeshtha','Ashadh','Shravan','Bhadrapad','Ashwin','Kartik',
            'Margshirsh','Paush','Magh','Phalgun','Chaitra','Vaishakh',
        ];

        $defaultTotalDays = [
            'Jyeshtha'   => 31, 'Ashadh'     => 31, 'Shravan'    => 31, 'Bhadrapad'  => 31,
            'Ashwin'     => 30, 'Kartik'     => 30, 'Margshirsh' => 30, 'Paush'      => 30,
            'Magh'       => 30, 'Phalgun'    => 30, 'Chaitra'    => 30, 'Vaishakh'   => 31,
        ];

        $attendance = [];
        foreach ($months as $month) {
            $attendance[$month] = [
                'total_days' => (string) ($defaultTotalDays[$month] ?? ''),
                'working'    => (string) $request->input("attendance.$month.working", ''),
                'present'    => (string) $request->input("attendance.$month.present", ''),
                'absent'     => (string) $request->input("attendance.$month.absent",  ''),
            ];
        }

        $descriptive = [];
        foreach ((array) $request->input('descriptive', []) as $sid => $fields) {
            $descriptive[$sid] = [
                'progress'     => trim((string) ($fields['progress']     ?? '')),
                'interest'     => trim((string) ($fields['interest']     ?? '')),
                'improvements' => trim((string) ($fields['improvements'] ?? '')),
            ];
        }

        $manualGrades = [];
        foreach ((array) $request->input('graded_subjects', []) as $sid => $grade) {
            $grade = trim((string) $grade);
            if ($grade !== '') {
                $manualGrades[(int) $sid] = $grade;
            }
        }

        StudentProgressCard::updateOrCreate(
            [
                'student_id'       => $studentId,
                'exam_master_id'   => $examId,
                'academic_year_id' => $yearId,
            ],
            [
                'standard_id'         => $standardId,
                'division_id'         => $divisionId,
                'phone'               => $request->input('phone'),
                'email'               => $request->input('email'),
                'school_timing'       => $request->input('school_timing'),
                'blood_group'         => $request->input('blood_group'),
                'mother_tongue'       => $request->input('mother_tongue'),
                'term1_weight'        => $request->input('term1_weight'),
                'term1_height'        => $request->input('term1_height'),
                'term2_weight'        => $request->input('term2_weight'),
                'term2_height'        => $request->input('term2_height'),
                'attendance_data'     => $attendance,
                'passed_promoted_to'  => $request->input('passed_promoted_to'),
                'school_reopens_on'   => $request->input('school_reopens_on'),
                'descriptive_records' => $descriptive,
                'second_term_grades'  => $manualGrades,
                'updated_by'          => Auth::id(),
                'created_by'          => Auth::id(),
            ]
        );

        return redirect()
            ->route('report-card.progress-card', [$studentId, $examId, $yearId])
            ->with('success', 'Progress card details saved.');
    }


    /* ======================================================================
     | HELPERS
     ====================================================================== */

    private function getSubjectMarksMap($studentId, $examId, $standardId): \Illuminate\Support\Collection
    {
        $marks = DB::table('student_marks')
            ->where('student_id', $studentId)
            ->where('exam_master_id', $examId)
            ->get();

        if ($marks->isEmpty()) return collect();

        $swsMap = collect();
        if ($standardId) {
            $swsMap = DB::table('standard_wise_subjects')
                ->where('standard_id', $standardId)
                ->get()
                ->keyBy('id');
        }

        $result = collect();

        foreach ($marks as $mark) {
            $storedId = (int) $mark->subject_id;
            if ($storedId <= 0) continue;

            if (!$result->has($storedId)) {
                $result->put($storedId, $mark);
            }

            if ($swsMap->has($storedId)) {
                $masterId = (int) $swsMap[$storedId]->subject_id;
                if (!$result->has($masterId)) {
                    $result->put($masterId, $mark);
                }
            }
        }

        return $result;
    }


    private function buildReportCardSubjects($studentId, $examId, $standardId, $academicYearId, array $manualGrades = []): \Illuminate\Support\Collection
    {
        $examSubjects = DB::table('exam_master_subjects as ems')
            ->join('subjects as s', 's.id', '=', 'ems.subject_id')
            ->where('ems.exam_master_id', $examId)
            ->when($standardId, fn ($q) => $q->where('ems.standard_id', $standardId))
            ->orderBy('ems.display_order')
            ->orderBy('s.id')
            ->select([
                's.id as subject_id',
                's.subject_name',
                's.subject_code',
                'ems.max_marks',
                'ems.passing_marks',
                'ems.display_order',
                DB::raw('0 as is_graded'),
            ])
            ->get();

        if ($examSubjects->isEmpty() && $standardId) {
            $examSubjects = DB::table('standard_wise_subjects as sws')
                ->join('subjects as s', 's.id', '=', 'sws.subject_id')
                ->where('sws.standard_id', $standardId)
                ->where('sws.is_active', 1)
                ->where('s.is_active', 1)
                ->orderBy('sws.sort_order')
                ->orderBy('s.id')
                ->select([
                    's.id as subject_id',
                    's.subject_name',
                    's.subject_code',
                    DB::raw('NULL as max_marks'),
                    DB::raw('NULL as passing_marks'),
                    DB::raw('sws.sort_order as display_order'),
                    DB::raw('0 as is_graded'),
                ])
                ->get();
        }

        $examSubjects = $this->ensureAdditionalSubjects($examSubjects, $standardId);

        // NOTE: Skill subjects are no longer appended here

        $marksBySubject = $this->getSubjectMarksMap($studentId, $examId, $standardId);

        return $examSubjects->map(function ($ex) use ($marksBySubject, $manualGrades) {

            $subjectId = (int) $ex->subject_id;

            if (!empty($ex->is_graded)) {
                $grade = $manualGrades[$subjectId]
                    ?? $manualGrades[(string) $subjectId]
                    ?? '-';

                return (object) [
                    'subject_id'     => $subjectId,
                    'subject_name'   => $ex->subject_name,
                    'subject_code'   => $ex->subject_code,
                    'max_marks'      => 0,
                    'passing_marks'  => 0,
                    'obtained_marks' => 0,
                    'subject_result' => $grade,
                    'is_graded'      => 1,
                ];
            }

            $mark = $marksBySubject->get($subjectId);

            $obtained = 0.0;
            $maxSum   = 0.0;
            $passSum  = 0.0;

            if ($mark) {
                if ((float) $mark->theory_max_marks > 0) {
                    $obtained += (float) $mark->theory_obtained_marks;
                    $maxSum   += (float) $mark->theory_max_marks;
                    $passSum  += (float) $mark->theory_passing_marks;
                }
                if ((float) $mark->oral_max_marks > 0) {
                    $obtained += (float) $mark->oral_obtained_marks;
                    $maxSum   += (float) $mark->oral_max_marks;
                    $passSum  += (float) $mark->oral_passing_marks;
                }
                if ((float) $mark->practical_max_marks > 0) {
                    $obtained += (float) $mark->practical_obtained_marks;
                    $maxSum   += (float) $mark->practical_max_marks;
                    $passSum  += (float) $mark->practical_passing_marks;
                }
            }

            $maxMarks = (float) ($ex->max_marks ?? 0);
            if ($maxMarks <= 0 && $maxSum > 0) $maxMarks = $maxSum;
            if ($maxMarks <= 0 && $mark)       $maxMarks = 40;

            $passMarks = (float) ($ex->passing_marks ?? 0);
            if ($passMarks <= 0 && $passSum > 0) $passMarks = $passSum;
            if ($passMarks <= 0 && $maxMarks > 0) {
                $passMarks = (int) ceil(($maxMarks * 35) / 100);
            }

            $subjectResult = '-';

            if ($mark) {
                $isOptional = (int) ($mark->is_optional ?? 0) === 1;
                $isAbsent   = (int) ($mark->is_absent   ?? 0) === 1;

                if ($isOptional && $obtained <= 0) {
                    $subjectResult = 'OPT';
                } elseif ($isAbsent) {
                    $subjectResult = 'AB';
                } elseif ($obtained >= $passMarks) {
                    $subjectResult = 'PASS';
                } else {
                    $subjectResult = 'FAIL';
                }
            }

            return (object) [
                'subject_id'     => $subjectId,
                'subject_name'   => $ex->subject_name,
                'subject_code'   => $ex->subject_code,
                'max_marks'      => $maxMarks,
                'passing_marks'  => $passMarks,
                'obtained_marks' => $obtained,
                'subject_result' => $subjectResult,
                'is_graded'      => 0,
            ];
        })->values();
    }


    private function buildProgressCardSubjects($studentId, $examId, $standardId, $academicYearId, array $manualGrades = []): \Illuminate\Support\Collection
    {
        $examSubjects = DB::table('exam_master_subjects as ems')
            ->join('subjects as s', 's.id', '=', 'ems.subject_id')
            ->where('ems.exam_master_id', $examId)
            ->when($standardId, fn ($q) => $q->where('ems.standard_id', $standardId))
            ->orderBy('ems.display_order')
            ->orderBy('s.id')
            ->select([
                's.id as subject_id',
                's.subject_name',
                's.subject_code',
                'ems.max_marks',
                'ems.passing_marks',
                'ems.display_order',
                DB::raw('0 as is_graded'),
            ])
            ->get();

        if ($examSubjects->isEmpty() && $standardId) {
            $examSubjects = DB::table('standard_wise_subjects as sws')
                ->join('subjects as s', 's.id', '=', 'sws.subject_id')
                ->where('sws.standard_id', $standardId)
                ->where('sws.is_active', 1)
                ->where('s.is_active', 1)
                ->orderBy('sws.sort_order')
                ->orderBy('s.id')
                ->select([
                    's.id as subject_id',
                    's.subject_name',
                    's.subject_code',
                    DB::raw('NULL as max_marks'),
                    DB::raw('NULL as passing_marks'),
                    DB::raw('sws.sort_order as display_order'),
                    DB::raw('0 as is_graded'),
                ])
                ->get();
        }

        $examSubjects = $this->ensureAdditionalSubjects($examSubjects, $standardId);

        // NOTE: Skill subjects are no longer appended here

        $marksBySubject = $this->getSubjectMarksMap($studentId, $examId, $standardId);

        return $examSubjects->map(function ($ex) use ($marksBySubject, $manualGrades) {

            $subjectId = (int) $ex->subject_id;

            if (!empty($ex->is_graded)) {
                $grade = $manualGrades[$subjectId]
                    ?? $manualGrades[(string) $subjectId]
                    ?? '-';

                return (object) [
                    'subject_id'     => $subjectId,
                    'subject_name'   => $ex->subject_name,
                    'subject_code'   => $ex->subject_code,
                    'max_marks'      => 0,
                    'obtained_marks' => 0,
                    'grade'          => $grade,
                    'is_graded'      => 1,
                ];
            }

            $mark = $marksBySubject->get($subjectId);

            $obtained = 0.0;
            $maxSum   = 0.0;

            if ($mark) {
                if ((float) $mark->theory_max_marks > 0) {
                    $obtained += (float) $mark->theory_obtained_marks;
                    $maxSum   += (float) $mark->theory_max_marks;
                }
                if ((float) $mark->oral_max_marks > 0) {
                    $obtained += (float) $mark->oral_obtained_marks;
                    $maxSum   += (float) $mark->oral_max_marks;
                }
                if ((float) $mark->practical_max_marks > 0) {
                    $obtained += (float) $mark->practical_obtained_marks;
                    $maxSum   += (float) $mark->practical_max_marks;
                }
            }

            $maxMarks = (float) ($ex->max_marks ?? 0);
            if ($maxMarks <= 0 && $maxSum > 0) $maxMarks = $maxSum;
            if ($maxMarks <= 0 && $mark)       $maxMarks = 40;

            $grade = '-';

            if ($mark) {
                $isOptional = (int) ($mark->is_optional ?? 0) === 1;
                $isAbsent   = (int) ($mark->is_absent   ?? 0) === 1;

                if ($isOptional && $obtained <= 0) {
                    $grade = 'OPT';
                } elseif ($isAbsent) {
                    $grade = 'AB';
                } else {
                    $pct   = $maxMarks > 0 ? ($obtained / $maxMarks) * 100 : 0;
                    $grade = $this->gradeFromPercentage($pct);
                }
            }

            return (object) [
                'subject_id'     => $subjectId,
                'subject_name'   => $ex->subject_name,
                'subject_code'   => $ex->subject_code,
                'max_marks'      => $maxMarks,
                'obtained_marks' => $obtained,
                'grade'          => $grade,
                'is_graded'      => 0,
            ];
        })->values();
    }


    private function ensureAdditionalSubjects($examSubjects, $standardId): \Illuminate\Support\Collection
    {
        $existingNames = $examSubjects
            ->map(fn ($s) => strtoupper(trim((string) $s->subject_name)))
            ->all();

        $additional = [
            [
                'display' => 'ART',
                'search'  => ['ART', 'ARTS', 'DRAWING', 'CRAFT', 'COLOURING', 'PT'],
                'order'   => 900,
            ],
            [
                'display' => 'W EXP',
                'search'  => ['W EXP', 'WE', 'WORK EXPERIENCE', 'WORK EDUCATION'],
                'order'   => 901,
            ],
            [
                'display' => 'P ED & HEALTH',
                'search'  => ['P ED & HEALTH', 'P ED', 'PED', 'PHYSICAL EDUCATION', 'HEALTH'],
                'order'   => 902,
            ],
        ];

        foreach ($additional as $add) {

            $found = false;
            foreach ($add['search'] as $name) {
                if (in_array($name, $existingNames, true)) {
                    $found = true;
                    break;
                }
            }
            if ($found) continue;

            $realSubject = DB::table('subjects')
                ->whereIn('subject_name', $add['search'])
                ->where('is_active', 1)
                ->first();

            $examSubjects->push((object) [
                'subject_id'    => $realSubject ? (int) $realSubject->id : 0,
                'subject_name'  => $add['display'],
                'subject_code'  => $realSubject->subject_code ?? '',
                'max_marks'     => 0,
                'passing_marks' => 0,
                'display_order' => $add['order'],
                'is_graded'     => 1,
            ]);

            $existingNames[] = $add['display'];
        }

        return $examSubjects
            ->sortBy(fn ($s) => (int) ($s->display_order ?? 999))
            ->values();
    }


    private function computeOverall($subjects): array
    {
        $totalMax = 0.0;
        $totalObt = 0.0;
        $anyMark  = false;
        $hasFail  = false;

        foreach ($subjects as $s) {

            if (!empty($s->is_graded)) continue;

            $status = strtoupper(trim((string) ($s->subject_result ?? $s->grade ?? '')));

            if ($status === 'OPT') continue;

            $max = (float) ($s->max_marks ?? 0);
            if ($max > 0) $totalMax += $max;

            if ($status !== '' && $status !== '-') {
                $anyMark   = true;
                $totalObt += (float) ($s->obtained_marks ?? 0);

                if (in_array($status, ['FAIL', 'AB', 'ABSENT'], true)) {
                    $hasFail = true;
                }
            }
        }

        if (!$anyMark || $totalMax <= 0) {
            return [null, '-', 'PENDING', $totalMax, $totalObt];
        }

        $pct    = round(($totalObt * 100) / $totalMax, 2);
        $grade  = $this->gradeFromPercentage($pct);
        $result = ($hasFail || $pct < 35) ? 'FAIL' : 'PASS';

        return [$pct, $grade, $result, $totalMax, $totalObt];
    }


    private function gradeFromPercentage($percentage): string
    {
        $p = (float) $percentage;
        if ($p >= 91) return 'A1';
        if ($p >= 81) return 'A2';
        if ($p >= 71) return 'B1';
        if ($p >= 61) return 'B2';
        if ($p >= 51) return 'C1';
        if ($p >= 41) return 'C2';
        if ($p >= 33) return 'D';
        if ($p >= 21) return 'E1';
        if ($p >= 1)  return 'E2';
        return 'F';
    }


    private function defaultAttendanceData(): array
    {
        $defaults = [
            'Jyeshtha'   => 31, 'Ashadh'     => 31, 'Shravan'    => 31, 'Bhadrapad'  => 31,
            'Ashwin'     => 30, 'Kartik'     => 30, 'Margshirsh' => 30, 'Paush'      => 30,
            'Magh'       => 30, 'Phalgun'    => 30, 'Chaitra'    => 30, 'Vaishakh'   => 31,
        ];

        $data = [];
        foreach ($defaults as $month => $days) {
            $data[$month] = [
                'total_days' => (string) $days,
                'working'    => '',
                'present'    => '',
                'absent'     => '',
            ];
        }
        return $data;
    }

    public function examsByStandard(Request $request)
    {
        $data = $request->validate([
            'standard_id'      => 'required|integer',
            'academic_year_id' => 'nullable|integer',
        ]);

        $query = ExamMaster::query()->where('standard_id', $data['standard_id']);

        if (!empty($data['academic_year_id'])) {
            $query->where(function ($q) use ($data) {
                $q->where('academic_year_id', $data['academic_year_id'])
                  ->orWhereNull('academic_year_id');
            });
        }

        $exams = $query->orderBy('exam_name')->get(['id', 'exam_name']);

        return response()->json($exams);
    }
}