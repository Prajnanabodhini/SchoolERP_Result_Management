<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\ExamMaster;
use App\Models\Standard;
use App\Models\StudentProgressCard;
use App\Helpers\StudentHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportCardController extends Controller
{
    private function isHighSchoolStandard($standardName): bool {
        $s = strtoupper(trim((string) $standardName));
        return $s !== '' && (preg_match('/\b(9|10)\b/', $s) || str_contains($s, 'NINTH') || str_contains($s, 'TENTH'));
    }

    private function isPrePrimaryStandard($standardName): bool {
        return preg_match('/\b(NURSERY|JR|SR|KINDERGARTEN|KG|LKG|UKG)\b/i', strtoupper(trim((string) $standardName))) === 1;
    }

    private function isPrimaryStandard($standardName): bool {
        return preg_match('/\b(1ST|2ND|FIRST|SECOND)\b/i', strtoupper(trim((string) $standardName))) === 1;
    }

    private function cleanStudentName(string $studname, string $fathername): string {
        $studname = trim($studname); $fathername = trim($fathername);
        if ($fathername === '') return $studname;
        $fatherUpper = strtoupper($fathername); $studUpper = strtoupper($studname);
        while (str_ends_with($studUpper, ' ' . $fatherUpper) || $studUpper === $fatherUpper) {
            if ($studUpper === $fatherUpper) { $studname = ''; $studUpper = ''; break; }
            $studname = trim(substr($studname, 0, -strlen($fatherUpper) - 1)); $studUpper = strtoupper($studname);
        }
        return $studname . ' ' . $fathername;
    }

    public function index() {
        return view('report-card.index', [
            'academicYears' => AcademicYear::orderByDesc('year_name')->get(), 'exams' => ExamMaster::all(),
            'standards' => Standard::all(), 'divisions' => Division::all(), 'students' => collect(), 'report' => null, 'subjects' => collect(), 'error' => null,
        ]);
    }

    public function search(Request $request) {
        $students = collect();
        if ($request->filled('academic_year_id') && $request->filled('standard_id') && $request->filled('division_id')) {
            if ($year = AcademicYear::find($request->academic_year_id)) {
                $yearId = $year->old_year_id ?? substr($year->year_name, 0, 4);
                $students = StudentHelper::getStudentsDirectERP($yearId, $request->standard_id, $request->division_id);
            }
        }
        return view('report-card.index', [
            'academicYears' => AcademicYear::orderByDesc('year_name')->get(), 'exams' => ExamMaster::all(),
            'standards' => Standard::all(), 'divisions' => Division::all(), 'students' => $students, 'report' => null,
            'subjects' => collect(), 'error' => null, 'academic_year_id' => $request->academic_year_id,
            'exam_master_id' => $request->exam_master_id, 'standard_id' => $request->standard_id, 'division_id' => $request->division_id,
        ]);
    }

    public function show(Request $request) {
        set_time_limit(120);
        $students = collect(); $report = null; $subjects = collect(); $error = null;
        
        if ($request->filled('academic_year_id') && $request->filled('standard_id') && $request->filled('division_id')) {
            if ($academicYear = AcademicYear::find($request->academic_year_id)) {
                $erpYearId = $academicYear->old_year_id ?? substr((string) $academicYear->year_name, 0, 4);
                $students = StudentHelper::getStudentsDirectERP($erpYearId, $request->standard_id, $request->division_id);
            }
        }

        $viewData = [
            'academicYears' => AcademicYear::orderByDesc('year_name')->get(), 'exams' => ExamMaster::all(),
            'standards' => Standard::all(), 'divisions' => Division::all(), 'students' => $students,
            'report' => null, 'subjects' => collect(), 'academic_year_id' => $request->academic_year_id,
            'exam_master_id' => $request->exam_master_id, 'standard_id' => $request->standard_id, 'division_id' => $request->division_id,
        ];

        if (!$request->filled('student_id')) return view('report-card.index', $viewData)->with('error', 'Please select a Student.');
        
        $selectedEnrollment = $students->first(fn($row) => (string)($row->Studentid ?? '') === (string)$request->student_id);
        if (!$selectedEnrollment) return view('report-card.index', $viewData)->with('error', 'The selected student does not belong to the selected filters.');

        $student = $this->getErpStudent($request->student_id);
        if (!$student) return view('report-card.index', $viewData)->with('error', 'Student not found in ERP.');

        $substudent = $selectedEnrollment;
        $exam = DB::table('exam_masters')->where('id', $request->exam_master_id)->first();
        $standard = DB::table('standards')->where('id', $request->standard_id)->first();
        $division = DB::table('divisions')->where('id', $request->division_id)->first();
        $year = DB::table('academic_years')->where('id', $request->academic_year_id)->first();
        $progress = StudentProgressCard::where('student_id', $request->student_id)->where('exam_master_id', $request->exam_master_id)->where('academic_year_id', $request->academic_year_id)->first();
        $manualGrades = $progress->second_term_grades ?? [];
        $studname = trim((string) ($student->studname ?? '')); $fathername = trim((string) ($student->fathername ?? ''));
        $fullName = $this->cleanStudentName($studname, $fathername);

        $report = (object) [
            'student_id' => (int) $request->student_id, 'gov_student_id' => $student->saralid ?? '', 'studname' => $studname,
            'father_name' => $fathername, 'full_student_name' => $fullName, 'mother_name' => $student->mothername ?? '',
            'gender' => $student->gender ?? '', 'date_of_birth' => $student->birthdate ?? '',
            'mother_tongue' => $progress->mother_tongue ?? $student->mtounge ?? 'MARATHI',
            'address' => $progress->address ?? $this->resolveStudentAddress($student), 'rollno' => $substudent->rollno ?? '',
            'gr_no' => $substudent->regno ?? '', 'aadhar_no' => $student->adharno ?? '', 'blood_group' => $progress->blood_group ?? '',
            'phone' => $progress->phone ?? $student->fathermobile ?? $student->resphone1 ?? '', 'email' => $progress->email ?? $student->email ?? '',
            'school_timing' => $progress->school_timing ?? '8:15am to 3:00pm', 'exam_master_id' => (int) $request->exam_master_id,
            'academic_year_id' => (int) $request->academic_year_id, 'standard_id' => (int) $request->standard_id,
            'division_id' => (int) $request->division_id, 'exam_name' => $exam->exam_name ?? '', 'standard_name' => $standard->standard_name ?? '',
            'division_name' => $division->division_name ?? '', 'year_name' => $year->year_name ?? '', 'rank' => null, 'percentage' => null,
            'grade' => null, 'result' => null, 'total_max_marks' => 0, 'total_obtained_marks' => 0,
        ];

        $subjects = $this->buildReportCardSubjects((int)$request->student_id, (int)$request->exam_master_id, (int)$request->standard_id, (int)$request->academic_year_id, $manualGrades);
        [$pct, $grade, $result, $tMax, $tObt] = $this->computeOverall($subjects);
        $report->percentage = $pct; $report->grade = $grade; $report->result = $result; $report->total_max_marks = $tMax; $report->total_obtained_marks = $tObt;

        return view('report-card.index', array_merge($viewData, ['report' => $report, 'subjects' => $subjects, 'error' => null]));
    }

    public function print($studentId, $examId, $yearId) {
        set_time_limit(120);
        if (!$student = $this->getErpStudent($studentId)) abort(404, 'Student not found in ERP.');
        if (!$year = DB::table('academic_years')->where('id', $yearId)->first()) abort(404, 'Academic year not found.');

        $erpYearId = $year->old_year_id ?? substr((string) ($year->year_name ?? ''), 0, 4);
        $exam = DB::table('exam_masters')->where('id', $examId)->first();
        $progress = StudentProgressCard::where('student_id', $studentId)->where('exam_master_id', $examId)->where('academic_year_id', $yearId)->first();
        $sampleMark = DB::table('student_marks')->where('student_id', $studentId)->where('exam_master_id', $examId)->where('academic_year_id', $yearId)->first();
        
        $standardId = $sampleMark->standard_id ?? $progress->standard_id ?? $exam->standard_id ?? null;
        $divisionId = $sampleMark->division_id ?? $progress->division_id ?? null;
        $substudent = $this->getErpSubstudent($studentId, $erpYearId, $standardId, $divisionId);
        $standard = $standardId ? DB::table('standards')->where('id', $standardId)->first() : null;
        $division = $divisionId ? DB::table('divisions')->where('id', $divisionId)->first() : null;
        $manualGrades = $progress->second_term_grades ?? [];
        $studname = trim((string) ($student->studname ?? '')); $fathername = trim((string) ($student->fathername ?? ''));
        $fullName = $this->cleanStudentName($studname, $fathername);

        $report = (object) [
            'student_id' => (int) $studentId, 'gov_student_id' => $student->saralid ?? '', 'studname' => $studname, 'father_name' => $fathername,
            'full_student_name' => $fullName, 'mother_name' => $student->mothername ?? '', 'gender' => $student->gender ?? '',
            'date_of_birth' => $student->birthdate ?? '', 'mother_tongue' => $progress->mother_tongue ?? $student->mtounge ?? 'MARATHI',
            'address' => $progress->address ?? $this->resolveStudentAddress($student), 'rollno' => $substudent->rollno ?? '',
            'gr_no' => $substudent->regno ?? '', 'aadhar_no' => $student->adharno ?? '', 'blood_group' => $progress->blood_group ?? '',
            'phone' => $progress->phone ?? $student->fathermobile ?? $student->resphone1 ?? '', 'email' => $progress->email ?? $student->email ?? '',
            'school_timing' => $progress->school_timing ?? '8:15am to 3:00pm', 'exam_master_id' => (int) $examId,
            'academic_year_id' => (int) $yearId, 'standard_id' => (int) ($standardId ?? 0), 'division_id' => (int) ($divisionId ?? 0),
            'exam_name' => $exam->exam_name ?? '', 'standard_name' => $standard->standard_name ?? '', 'division_name' => $division->division_name ?? '',
            'year_name' => $year->year_name ?? '', 'percentage' => null, 'grade' => null, 'result' => null, 'total_max_marks' => 0, 'total_obtained_marks' => 0,
        ];

        $subjects = $this->buildReportCardSubjects((int)$studentId, (int)$examId, (int)$standardId, (int)$yearId, $manualGrades);
        [$pct, $grade, $result, $tMax, $tObt] = $this->computeOverall($subjects);
        $report->percentage = $pct; $report->grade = $grade; $report->result = $result; $report->total_max_marks = $tMax; $report->total_obtained_marks = $tObt;

        return view('report-card.print', compact('report', 'subjects'));
    }

    private function getProgressCardData($studentId, $examId, $yearId) {
        $progress = StudentProgressCard::where('student_id', $studentId)->where('exam_master_id', $examId)->where('academic_year_id', $yearId)->first();
        if (!$student = $this->getErpStudent($studentId)) abort(404, 'Student not found in ERP.');
        $exam = DB::table('exam_masters')->where('id', $examId)->first(); $year = DB::table('academic_years')->where('id', $yearId)->first();
        if (!$exam) abort(404, 'Exam not found.'); if (!$year) abort(404, 'Academic year not found.');

        $erpYearId = $year->old_year_id ?? substr((string) ($year->year_name ?? ''), 0, 4);
        $substudent = $this->getErpSubstudent($studentId, $erpYearId);
        $standardId = $progress->standard_id ?? null; $divisionId = $progress->division_id ?? null;

        if (!$standardId || !$divisionId) {
            $sampleMark = DB::table('student_marks')->where('student_id', $studentId)->where('exam_master_id', $examId)->where('academic_year_id', $yearId)->first() 
                ?? DB::table('student_marks')->where('student_id', $studentId)->where('academic_year_id', $yearId)->first();
            $standardId = $standardId ?? $sampleMark->standard_id ?? $exam->standard_id ?? null;
            $divisionId = $divisionId ?? $sampleMark->division_id ?? null;
        }

        if (!$divisionId && ($skillMark = DB::table('student_skill_marks')->where('student_id', $studentId)->where('academic_year_id', $yearId)->first())) {
            $divisionId = $skillMark->division_id ?? null; $standardId = $standardId ?? $skillMark->standard_id ?? null;
        }

        if (!$divisionId && $substudent && ($divField = $substudent->div ?? $substudent->division ?? null)) {
            if ($divRec = DB::table('divisions')->where('division_name', $divField)->first()) $divisionId = $divRec->id;
        }

        if (!$standardId && $substudent && ($stdField = $substudent->class ?? $substudent->standard ?? null)) {
            if ($stdRec = DB::table('standards')->where('standard_name', 'LIKE', '%'.$stdField.'%')->first()) $standardId = $stdRec->id;
        }

        if ($standardId && $divisionId) $substudent = $this->getErpSubstudent($studentId, $erpYearId, $standardId, $divisionId);

        $standard = $standardId ? DB::table('standards')->where('id', $standardId)->first() : null;
        $division = $divisionId ? DB::table('divisions')->where('id', $divisionId)->first() : null;
        $examName = strtoupper(trim((string) ($exam->exam_name ?? ''))); $termLabel = 'Exam';
        if (str_contains($examName, 'TERM 1')) $termLabel = 'Term 1'; elseif (str_contains($examName, 'TERM 2')) $termLabel = 'Term 2';
        elseif (str_contains($examName, 'UNIT TEST 1')) $termLabel = 'Unit Test 1'; elseif (str_contains($examName, 'UNIT TEST 2')) $termLabel = 'Unit Test 2';
        elseif (str_contains($examName, 'UNIT TEST 3')) $termLabel = 'Unit Test 3'; elseif (str_contains($examName, 'UNIT TEST 4')) $termLabel = 'Unit Test 4';
        elseif (str_contains($examName, 'ANNUAL')) $termLabel = 'Annual';

        if (!$progress) {
            $progress = new StudentProgressCard(); $progress->attendance_data = $this->defaultAttendanceData();
            $progress->descriptive_records = []; $progress->second_term_grades = [];
        }

        $manualGrades = $progress->second_term_grades ?? [];
        $studname = trim((string) ($student->studname ?? '')); $fathername = trim((string) ($student->fathername ?? ''));
        $cleanedFullName = $this->cleanStudentName($studname, $fathername);
        $cleanStudName = $cleanedFullName;
        if ($fathername !== '' && str_ends_with(strtoupper($cleanedFullName), strtoupper($fathername))) {
            $cleanStudName = trim(substr($cleanedFullName, 0, -strlen($fathername)));
        }

        $yearLabel = $year->year_name ?? ''; $shakYear = '';
        if ($yearLabel) {
            $parts = explode('-', $yearLabel);
            $shakYear = count($parts) == 2 ? 'Shak: ' . (intval($parts[0]) - 78) . '-' . (intval($parts[1]) - 78) : 'Shak: ' . (intval(substr($yearLabel, 0, 4)) - 78);
        }

        $report = (object) [
            'student_id' => (int) $studentId, 'gov_student_id' => $student->saralid ?? '', 'studname' => $cleanStudName,
            'father_name' => $fathername, 'full_student_name' => $cleanedFullName, 'mother_name' => $student->mothername ?? '',
            'gender' => $student->gender ?? '', 'date_of_birth' => $student->birthdate ?? '', 'mother_tongue' => $progress->mother_tongue ?? $student->mtounge ?? 'MARATHI',
            'address' => $progress->address ?? $this->resolveStudentAddress($student), 'rollno' => $substudent->rollno ?? '',
            'gr_no' => $substudent->regno ?? '', 'aadhar_no' => $student->adharno ?? '', 'blood_group' => $progress->blood_group ?? '',
            'phone' => $progress->phone ?? $student->fathermobile ?? $student->resphone1 ?? '', 'email' => $progress->email ?? $student->email ?? '',
            'school_timing' => $progress->school_timing ?? '8:15am to 3:00pm', 'exam_master_id' => (int) $examId, 'academic_year_id' => (int) $yearId,
            'standard_id' => (int) ($standardId ?? 0), 'division_id' => (int) ($divisionId ?? 0), 'exam_name' => $exam->exam_name ?? '',
            'standard_name' => $standard->standard_name ?? '', 'division_name' => $division->division_name ?? '', 'year_name' => $year->year_name ?? '',
            'shak_year' => $shakYear, 'rank' => null, 'percentage' => null, 'grade' => null, 'result' => null, 'total_max_marks' => 0, 'total_obtained_marks' => 0,
        ];

        $subjects = $this->buildProgressCardSubjects($studentId, $examId, $standardId, $yearId, $manualGrades, $report->standard_name, $report->exam_name);
        [$pct, $grade, $result, $tMax, $tObt] = $this->computeOverall($subjects);
        $report->percentage = $pct; $report->grade = $grade; $report->result = $result; $report->total_max_marks = $tMax; $report->total_obtained_marks = $tObt;

        return compact('report', 'subjects', 'progress', 'termLabel') + [
            'academicAverageGrade' => $this->calculateAcademicAverageGrade($subjects),
            'specialSkillAverageGrade' => $this->calculateSpecialSkillAverageGrade($subjects)
        ];
    }

    public function printProgressCard($studentId, $examId, $yearId) {
        set_time_limit(120); $data = $this->getProgressCardData($studentId, $examId, $yearId); $data['isEdit'] = false;
        return view('report-card.progress-card', $data);
    }

    public function editProgressDetails($studentId, $examId, $yearId) {
        set_time_limit(120); $data = $this->getProgressCardData($studentId, $examId, $yearId); $data['isEdit'] = true;
        return view('report-card.progress-card', $data);
    }

    public function saveProgressDetails(Request $request, $studentId, $examId, $yearId) {
        $standardId = null; $divisionId = null;
        if ($sampleMark = DB::table('student_marks')->where('student_id', $studentId)->where('exam_master_id', $examId)->where('academic_year_id', $yearId)->first()) {
            $standardId = $sampleMark->standard_id; $divisionId = $sampleMark->division_id;
        }
        if (!$divisionId && ($skillMark = DB::table('student_skill_marks')->where('student_id', $studentId)->where('exam_master_id', $examId)->where('academic_year_id', $yearId)->first())) {
            $standardId = $standardId ?? $skillMark->standard_id ?? null; $divisionId = $skillMark->division_id ?? null;
        }
        if ((!$standardId || !$divisionId) && ($existingProgress = StudentProgressCard::where('student_id', $studentId)->where('exam_master_id', $examId)->where('academic_year_id', $yearId)->first())) {
            $standardId = $standardId ?? $existingProgress->standard_id; $divisionId = $divisionId ?? $existingProgress->division_id;
        }
        if (!$standardId && ($examMaster = DB::table('exam_masters')->where('id', $examId)->first())) $standardId = $examMaster->standard_id;
        if (!$standardId && $request->filled('standard_name') && ($std = DB::table('standards')->where('standard_name', $request->input('standard_name'))->first())) $standardId = $std->id;
        if (!$divisionId && $request->filled('division_name') && ($div = DB::table('divisions')->where('division_name', $request->input('division_name'))->first())) $divisionId = $div->id;
        if (!$divisionId && ($firstDiv = DB::table('divisions')->first())) $divisionId = $firstDiv->id; if (!$divisionId) $divisionId = 1;
        if (!$standardId && ($firstStd = DB::table('standards')->first())) $standardId = $firstStd->id; if (!$standardId) $standardId = 1;

        $mssqlUpdatesFee = []; $mssqlUpdatesSub = [];
        foreach (['student_name'=>'studname','father_name'=>'fathername','gov_student_id'=>'saralid','aadhar_no'=>'adharno','date_of_birth'=>'birthdate'] as $reqKey => $dbCol) {
            if ($request->filled($reqKey)) $mssqlUpdatesFee[$dbCol] = $request->input($reqKey);
        }
        if ($request->filled('gr_no')) $mssqlUpdatesSub['regno'] = $request->input('gr_no');
        if ($request->filled('rollno')) $mssqlUpdatesSub['rollno'] = $request->input('rollno');

        if (!empty($mssqlUpdatesFee)) DB::connection('sqlsrv_olderp')->table('dbo.FeeMstStudent')->where('Studentid', $studentId)->update($mssqlUpdatesFee);

        if (!empty($mssqlUpdatesSub)) {
            $erpYearId = AcademicYear::find($yearId)?->old_year_id ?? AcademicYear::find($yearId)?->year_name;
            $erpYearId = $erpYearId ? substr((string)$erpYearId, 0, 4) : null;
            $enrollment = ($erpYearId && $standardId && $divisionId) ? $this->getErpSubstudent($studentId, $erpYearId, $standardId, $divisionId) : null;

            if ($erpYearId === null || $erpYearId === '') {
                Log::warning('Report card roll/GR update skipped: ERP academic year could not be resolved.', compact('studentId', 'yearId'));
            } elseif ($enrollment) {
                DB::connection('sqlsrv_olderp')->table('SubStudentMst')->where('Studentid', $studentId)->where('yearid', $erpYearId)->where('standardid', $enrollment->standardid)->where('divisionid', $enrollment->divisionid)->update($mssqlUpdatesSub);
            } else {
                $count = DB::connection('sqlsrv_olderp')->table('SubStudentMst')->where('Studentid', $studentId)->where('yearid', $erpYearId)->count();
                if ($count === 1) DB::connection('sqlsrv_olderp')->table('SubStudentMst')->where('Studentid', $studentId)->where('yearid', $erpYearId)->update($mssqlUpdatesSub);
            }
        }

        $months = ['Jyeshtha','Ashadh','Shravan','Bhadrapad','Ashwin','Kartik','Margshirsh','Paush','Magh','Phalgun','Chaitra','Vaishakh'];
        $defaultTotalDays = ['Jyeshtha'=>31, 'Ashadh'=>31, 'Shravan'=>31, 'Bhadrapad'=>31, 'Ashwin'=>30, 'Kartik'=>30, 'Margshirsh'=>30, 'Paush'=>30, 'Magh'=>30, 'Phalgun'=>30, 'Chaitra'=>30, 'Vaishakh'=>31];
        $attendance = []; $descriptive = []; $manualGrades = [];
        
        foreach ($months as $month) {
            $attendance[$month] = [
                'total_days' => (string) $request->input("attendance.$month.total_days", $defaultTotalDays[$month] ?? ''),
                'working' => (string) $request->input("attendance.$month.working", ''),
                'present' => (string) $request->input("attendance.$month.present", ''),
                'absent' => (string) $request->input("attendance.$month.absent", '')
            ];
        }
        foreach ((array) $request->input('descriptive', []) as $sid => $fields) {
            $descriptive[$sid] = ['progress' => trim((string) ($fields['progress'] ?? '')), 'interest' => trim((string) ($fields['interest'] ?? '')), 'improvements' => trim((string) ($fields['improvements'] ?? ''))];
        }
        foreach ((array) $request->input('graded_subjects', []) as $key => $grade) {
            if (trim((string)$grade) !== '') $manualGrades[$key] = trim((string)$grade);
        }
        $classTestData = ['attendance' => $request->input('class_test.attendance'), 'result' => $request->input('class_test.result'), 'remark' => $request->input('class_test.remark')];
        $schoolDaysAttendance = [];
        foreach (['15_aug','ganesh_sthapana','ganesh_visarjan','date_15_aug','date_ganesh_sthapana','date_ganesh_visarjan'] as $k) {
            $schoolDaysAttendance[$k] = $request->input("school_days.$k");
        }

        StudentProgressCard::updateOrCreate(
            ['student_id' => $studentId, 'exam_master_id' => $examId, 'academic_year_id' => $yearId],
            [
                'standard_id' => $standardId, 'division_id' => $divisionId, 'phone' => $request->input('phone'), 'email' => $request->input('email'),
                'school_timing' => $request->input('school_timing'), 'blood_group' => $request->input('blood_group'), 'mother_tongue' => $request->input('mother_tongue'),
                'address' => $request->input('address'), 'term1_weight' => $request->input('term1_weight'), 'term1_height' => $request->input('term1_height'),
                'term2_weight' => $request->input('term2_weight'), 'term2_height' => $request->input('term2_height'), 'attendance_data' => $attendance,
                'passed_promoted_to' => $request->input('passed_promoted_to'), 'school_reopens_on' => $request->input('school_reopens_on'),
                'descriptive_records' => $descriptive, 'second_term_grades' => $manualGrades, 'class_test_data' => $classTestData,
                'school_days_data' => $schoolDaysAttendance, 'updated_by' => Auth::id(), 'created_by' => Auth::id(),
            ]
        );
        return redirect()->route('report-card.progress-card', [$studentId, $examId, $yearId])->with('success', 'Progress card details saved.');
    }

    private function getErpStudent($studentId) {
        return DB::connection('sqlsrv_olderp')->table('dbo.FeeMstStudent as f')
            ->select(['f.*', 'f.saralid as gov_student_id', 'f.taluka as legacy_taluka', 'f.district as legacy_district'])
            ->selectRaw("COALESCE(NULLIF(LTRIM(RTRIM(f.locaddr)), ''), NULLIF(LTRIM(RTRIM(f.peraddr)), '')) AS legacy_address")
            ->where('f.Studentid', $studentId)->first();
    }

    private function getErpSubstudent($studentId, $erpYearId = null, $standardId = null, $divisionId = null) {
        if ($erpYearId === null || trim((string) $erpYearId) === '') return null;
        if ($standardId > 0 && $divisionId > 0) {
            try {
                $match = StudentHelper::getStudentsDirectERP($erpYearId, (int) $standardId, (int) $divisionId)->first(fn($row) => (string)($row->Studentid ?? '') === (string)$studentId);
                if (!$match) Log::warning('No matching ERP enrollment found.', ['student_id'=>$studentId, 'erp_year_id'=>$erpYearId, 'standard_id'=>$standardId, 'division_id'=>$divisionId]);
                return $match;
            } catch (\Throwable $e) { return null; }
        }
        try {
            $rows = DB::connection('sqlsrv_olderp')->table('SubStudentMst')->where('Studentid', $studentId)->where('yearid', $erpYearId)->orderBy('standardid')->orderBy('divisionid')->orderBy('rollno')->get();
            if ($rows->isEmpty()) return null;
            if ($rows->count() > 1) { Log::warning('Multiple ERP enrollments exist.', compact('studentId', 'erpYearId')); return null; }
            return $rows->first();
        } catch (\Throwable $e) { return null; }
    }

    private function resolveStudentAddress($student): string {
        if (!$student) return '';
        $parts = array_filter([
            trim((string) ($student->peraddr ?? '')), trim((string) ($student->locaddr ?? '')), trim((string) ($student->legacy_address ?? '')),
            trim((string) ($student->location ?? '')), trim((string) ($student->taluka ?? '')), trim((string) ($student->legacy_taluka ?? '')),
            trim((string) ($student->district ?? '')), trim((string) ($student->legacy_district ?? '')),
        ], fn ($v) => $v !== '');
        return implode(', ', array_unique($parts));
    }

    private function getSubjectMarksMap($studentId, $examId, $standardId): \Illuminate\Support\Collection {
        $marks = DB::table('student_marks as sm')
            ->leftJoin('subjects as sub', 'sub.id', '=', 'sm.subject_id')
            ->leftJoin('standard_wise_subjects as sws', function($join) use ($standardId) {
                $join->on('sws.subject_id', '=', 'sub.id')
                     ->where('sws.standard_id', '=', $standardId);
            })
            ->where('sm.student_id', $studentId)->where('sm.exam_master_id', $examId)
            ->select('sm.*', 'sub.subject_name as master_name', 'sws.subject_name as sws_name')->get();
            
        if ($marks->isEmpty()) return collect();

        $marks = $marks->map(function($m) {
            $m->master_subject_name = !empty($m->sws_name) ? $m->sws_name : $m->master_name;
            unset($m->master_name, $m->sws_name);
            return $m;
        });

        $swsMap = $standardId ? DB::table('standard_wise_subjects')->where('standard_id', $standardId)->get()->keyBy('id') : collect();
        $result = collect();
        foreach ($marks as $mark) {
            $storedId = (int) $mark->subject_id; if ($storedId <= 0) continue;
            if (!$result->has($storedId)) $result->put($storedId, $mark);
            if (!empty($mark->master_subject_name)) $result->put('NAME_' . strtoupper(trim($mark->master_subject_name)), $mark);
            if ($swsMap->has($storedId)) {
                $subjectName = $swsMap[$storedId]->subject_name;
                $result->put('NAME_' . strtoupper(trim($subjectName)), $mark);
                if (!$result->has((int) $swsMap[$storedId]->subject_id)) $result->put((int) $swsMap[$storedId]->subject_id, $mark);
            }
        }
        return $result;
    }

    private function mapSubjectToSkill($subjectName): array {
        $name = strtoupper(trim((string) $subjectName));
        if (str_contains($name, 'OTHERS') || str_contains($name, 'LIBRARY')) return ['id' => 4, 'slot' => 'S4'];
        if (str_contains($name, 'WORK EXPERIENCE') || str_contains($name, 'WORK EXP')) return ['id' => 2, 'slot' => 'S2'];
        if (str_contains($name, 'PT & HEALTH') || str_contains($name, 'PHYSICAL EDUCATION')) return ['id' => 3, 'slot' => 'S3'];
        if (str_contains($name, 'ART') || str_contains($name, 'DRAWING')) return ['id' => 1, 'slot' => 'S1'];
        return ['id' => null, 'slot' => null];
    }

    private function subjectNameToKey($subjectName): string {
        return str_replace([' ', '/', '-', '.'], '_', strtoupper(trim((string) $subjectName)));
    }

    private function markHasAnyObtained($mark): bool {
        if (!$mark) return false;
        foreach (['theory_obtained_marks', 'oral_obtained_marks', 'practical_obtained_marks'] as $f) {
            if (isset($mark->{$f}) && $mark->{$f} !== null && $mark->{$f} !== '') return true;
        }
        return false;
    }

    private function buildReportCardSubjects($studentId, $examId, $standardId, $academicYearId, array $manualGrades = []): \Illuminate\Support\Collection {
        $examSubjects = DB::table('exam_master_subjects as ems')
            ->join('subjects as s', 's.id', '=', 'ems.subject_id')
            ->leftJoin('standard_wise_subjects as sws', function($join) use ($standardId) {
                $join->on('sws.subject_id', '=', 's.id')
                     ->where('sws.standard_id', '=', $standardId);
            })
            ->where('ems.exam_master_id', $examId)->when($standardId, fn ($q) => $q->where('ems.standard_id', $standardId))
            ->orderBy('ems.display_order')->orderBy('s.id')
            ->select([
                's.id as subject_id', 
                's.subject_code', 'ems.max_marks', 'ems.passing_marks', 'ems.display_order', DB::raw('0 as is_graded'),
                's.subject_name as master_name', 'sws.subject_name as sws_name'
            ])->get();

        $examSubjects = $examSubjects->map(function ($ex) {
            $ex->subject_name = !empty($ex->sws_name) ? $ex->sws_name : $ex->master_name;
            unset($ex->master_name, $ex->sws_name);
            return $ex;
        });

        if ($examSubjects->isEmpty() && $standardId) {
            $examSubjects = DB::table('standard_wise_subjects as sws')
                ->join('subjects as s', 's.id', '=', 'sws.subject_id')
                ->where('sws.standard_id', $standardId)->where('sws.is_active', 1)->where('s.is_active', 1)
                ->orderBy('sws.sort_order')->orderBy('s.id')
                ->select([
                    's.id as subject_id', 
                    's.subject_code', DB::raw('NULL as max_marks'), DB::raw('NULL as passing_marks'), DB::raw('sws.sort_order as display_order'), DB::raw('0 as is_graded'),
                    's.subject_name as master_name', 'sws.subject_name as sws_name'
                ])->get();

            $examSubjects = $examSubjects->map(function ($ex) {
                $ex->subject_name = !empty($ex->sws_name) ? $ex->sws_name : $ex->master_name;
                unset($ex->master_name, $ex->sws_name);
                return $ex;
            });
        }
        
        $examSubjects = $this->ensureAdditionalSubjects($examSubjects, $standardId);
        $marksBySubject = $this->getSubjectMarksMap($studentId, $examId, $standardId);
        $skillGrades = [];
        foreach (DB::table('student_skill_marks as ssm')->leftJoin('subjects as s', 's.id', '=', 'ssm.skill_id')->where('ssm.academic_year_id', $academicYearId)->where('ssm.student_id', $studentId)->select('ssm.*', 's.subject_name as skill_subject_name')->get() as $mark) {
            $grade = null;
            if (isset($mark->is_absent) && $mark->is_absent == 1) $grade = (!empty($mark->grade) && in_array(strtoupper(trim($mark->grade)), ['F', 'AB'])) ? strtoupper(trim($mark->grade)) : 'AB';
            elseif (!empty($mark->grade)) $grade = strtoupper(trim($mark->grade));
            if ($grade) {
                $isCurrentExam = ($mark->exam_master_id == $examId);
                if (isset($mark->skill_id) && $mark->skill_id > 0) { $sid = (int) $mark->skill_id; if ($isCurrentExam || !isset($skillGrades[$sid])) $skillGrades[$sid] = $grade; }
                if (isset($mark->skill_slot) && !empty($mark->skill_slot)) { $slot = trim($mark->skill_slot); if ($isCurrentExam || !isset($skillGrades[$slot])) $skillGrades[$slot] = $grade; }
                if (!empty($mark->skill_subject_name)) { $name = strtoupper(trim($mark->skill_subject_name)); if ($isCurrentExam || !isset($skillGrades[$name])) $skillGrades[$name] = $grade; }
            }
        }

        return $examSubjects->map(function ($ex) use ($marksBySubject, $manualGrades, $skillGrades) {
            $subjectId = (int) $ex->subject_id;
            if (!empty($ex->is_graded)) {
                $grade = null; $skillMap = $this->mapSubjectToSkill($ex->subject_name); $skillId = $skillMap['id']; $skillSlot = $skillMap['slot'];
                if ($skillId && isset($skillGrades[$skillId])) $grade = $skillGrades[$skillId];
                elseif ($skillSlot && isset($skillGrades[$skillSlot])) $grade = $skillGrades[$skillSlot];
                elseif ($subjectId && isset($skillGrades[$subjectId])) $grade = $skillGrades[$subjectId];
                if (!$grade) { $nameKey = $this->subjectNameToKey($ex->subject_name); $grade = $manualGrades[$subjectId] ?? $manualGrades[(string) $subjectId] ?? $manualGrades[$nameKey] ?? '-'; }
                return (object) ['subject_id' => $subjectId, 'subject_name' => $ex->subject_name, 'subject_code' => $ex->subject_code, 'max_marks' => 0, 'passing_marks' => 0, 'obtained_marks' => 0, 'subject_result' => $grade, 'is_graded' => 1, 'has_marks' => $grade !== '-' && $grade !== ''];
            }
            
            $mark = $marksBySubject->get($subjectId) ?? $marksBySubject->get('NAME_' . strtoupper(trim($ex->subject_name)));
            $obtained = $maxSum = $passSum = 0.0;
            if ($mark) {
                $obtained += (float) ($mark->theory_obtained_marks ?? 0) + (float) ($mark->oral_obtained_marks ?? 0) + (float) ($mark->practical_obtained_marks ?? 0);
                $maxSum += (float) ($mark->theory_max_marks ?? 0) + (float) ($mark->oral_max_marks ?? 0) + (float) ($mark->practical_max_marks ?? 0);
                $passSum += (float) ($mark->theory_passing_marks ?? 0) + (float) ($mark->oral_passing_marks ?? 0) + (float) ($mark->practical_passing_marks ?? 0);
            }
            $maxMarks = (float) ($ex->max_marks ?? 0);
            if ($maxMarks <= 0 && $maxSum > 0) $maxMarks = $maxSum; if ($maxMarks <= 0 && $mark) $maxMarks = 40;
            $passMarks = (float) ($ex->passing_marks ?? 0);
            if ($passMarks <= 0 && $passSum > 0) $passMarks = $passSum; if ($passMarks <= 0 && $maxMarks > 0) $passMarks = (int) ceil(($maxMarks * 35) / 100);
            $hasMarks = $this->markHasAnyObtained($mark); $subjectResult = '-';
            if ($hasMarks) {
                $isOptional = (int) ($mark->is_optional ?? 0) === 1; $isAbsent = (int) ($mark->is_absent ?? 0) === 1;
                if ($isOptional && $obtained <= 0) $subjectResult = 'OPT'; elseif ($isAbsent) $subjectResult = 'AB'; elseif ($obtained >= $passMarks) $subjectResult = 'PASS'; else $subjectResult = 'FAIL';
            }
            return (object) ['subject_id' => $subjectId, 'subject_name' => $ex->subject_name, 'subject_code' => $ex->subject_code, 'max_marks' => $maxMarks, 'passing_marks' => $passMarks, 'obtained_marks' => $obtained, 'subject_result' => $subjectResult, 'is_graded' => 0, 'has_marks' => $hasMarks];
        })->values();
    }

    private function buildProgressCardSubjects($studentId, $examId, $standardId, $academicYearId, array $manualGrades = [], $standardName = '', $examName = ''): \Illuminate\Support\Collection {
        $isPrePrimary = $this->isPrePrimaryStandard($standardName);
        
        $examSubjects = DB::table('exam_master_subjects as ems')
            ->join('subjects as s', 's.id', '=', 'ems.subject_id')
            ->leftJoin('standard_wise_subjects as sws', function($join) use ($standardId) {
                $join->on('sws.subject_id', '=', 's.id')
                     ->where('sws.standard_id', '=', $standardId);
            })
            ->where('ems.exam_master_id', $examId)->when($standardId, fn ($q) => $q->where('ems.standard_id', $standardId))
            ->orderBy('ems.display_order')->orderBy('s.id')
            ->select([
                's.id as subject_id', 
                's.subject_code', 'ems.max_marks', 'ems.passing_marks', 'ems.display_order', DB::raw('0 as is_graded'),
                's.subject_name as master_name', 'sws.subject_name as sws_name'
            ])->get();

        $examSubjects = $examSubjects->map(function ($ex) {
            $ex->subject_name = !empty($ex->sws_name) ? $ex->sws_name : $ex->master_name;
            unset($ex->master_name, $ex->sws_name);
            return $ex;
        });

        $prePrimarySkills = ['CRAFT', 'DRAMA', 'LIFE SKILL', 'CLAY', 'SONG', 'INDOOR GAME', 'OUTDOOR GAME', 'BOOKS', 'TOY'];
        $skillGrades = [];
        foreach (DB::table('student_skill_marks as ssm')->leftJoin('subjects as s', 's.id', '=', 'ssm.skill_id')->where('ssm.academic_year_id', $academicYearId)->where('ssm.student_id', $studentId)->select('ssm.*', 's.subject_name as skill_subject_name')->get() as $mark) {
            $grade = !empty($mark->grade) ? strtoupper(trim($mark->grade)) : null;
            if ($grade) {
                $isCurrentExam = ($mark->exam_master_id == $examId);
                if (isset($mark->skill_id) && $mark->skill_id > 0) { $sid = (int) $mark->skill_id; if ($isCurrentExam || !isset($skillGrades[$sid])) $skillGrades[$sid] = $grade; }
                if (isset($mark->skill_slot) && !empty($mark->skill_slot)) { $slot = trim($mark->skill_slot); if ($isCurrentExam || !isset($skillGrades[$slot])) $skillGrades[$slot] = $grade; }
                if (!empty($mark->skill_subject_name)) { $name = strtoupper(trim($mark->skill_subject_name)); if ($isCurrentExam || !isset($skillGrades[$name])) $skillGrades[$name] = $grade; }
            }
        }
        
        // 👇 MODIFIED: Allow fetching observation grades for High School standards too 👇
        if ($isPrePrimary || $this->isHighSchoolStandard($standardName)) {
            $newSkillSubjects = ['WATER SECURITY', 'DEFENCE STUDIES', 'DEFENSE SCIENCE'];
            
            $query = DB::table('student_skill_observations as sso')
                ->leftJoin('subjects as s', 's.id', '=', 'sso.subject_id')
                ->where('sso.student_id', $studentId)
                ->where('sso.exam_master_id', $examId)
                ->where('sso.academic_year_id', $academicYearId);

            // If it's a high school standard, only fetch the new specific subjects from observations
            if (!$isPrePrimary) {
                $query->where(function($q) use ($newSkillSubjects) {
                    foreach ($newSkillSubjects as $subName) {
                        $q->orWhereRaw('UPPER(TRIM(s.subject_name)) = ?', [$subName]);
                    }
                });
            }

            foreach ($query->select('sso.*', 's.subject_name')->get() as $obs) {
                if (isset($obs->subject_id) && $obs->subject_id > 0) {
                    $grade = (isset($obs->is_absent) && $obs->is_absent == 1) ? 'AB' : (!empty($obs->overall_grade) ? strtoupper(trim($obs->overall_grade)) : null);
                    if ($grade) { 
                        $skillGrades[(int) $obs->subject_id] = $grade; 
                        if (!empty($obs->subject_name)) {
                            $skillGrades[strtoupper(trim($obs->subject_name))] = $grade; 
                        }
                    }
                }
            }
        }
        // 👆 END OF MODIFIED BLOCK 👆

        $examSubjects = $examSubjects->map(function ($ex) use ($isPrePrimary, $prePrimarySkills) {
            $isSkill = false; $name = strtoupper(trim((string)$ex->subject_name));
            if ($name === 'STORY' || $name === 'MARATHI STORY') { $ex->is_graded = 0; return $ex; }
            if ($isPrePrimary) { foreach ($prePrimarySkills as $skill) { if (str_contains($name, $skill)) { $isSkill = true; break; } } }
            if ($isSkill) { $ex->is_graded = 1; $ex->display_order = 900; }
            return $ex;
        });

        if ($isPrePrimary) {
            $existingNames = $examSubjects->map(fn($s) => strtoupper(trim((string)$s->subject_name)))->toArray();
            $skillIdMap = DB::table('subjects')->select('id', DB::raw('UPPER(TRIM(subject_name)) as normalized_name'))->get()->pluck('id', 'normalized_name')->toArray();
            $order = 900;
            foreach ($prePrimarySkills as $skillName) {
                $found = false; foreach ($existingNames as $exName) { if (str_contains($exName, $skillName) || str_contains($skillName, $exName)) { $found = true; break; } }
                if (!$found) {
                    $actualId = $skillIdMap[$skillName] ?? 0;
                    if ($actualId == 0) { foreach ($skillIdMap as $dbName => $dbId) { if (str_contains($dbName, $skillName) || str_contains($skillName, $dbName)) { $actualId = $dbId; break; } } }
                    $examSubjects->push((object) ['subject_id' => $actualId, 'subject_name' => $skillName, 'subject_code' => '', 'max_marks' => 0, 'passing_marks' => 0, 'display_order' => $order++, 'is_graded' => 1]);
                    $existingNames[] = $skillName;
                }
            }
        }

        if ($this->isHighSchoolStandard($standardName)) {
            $mergedSubjects = collect(); $normalize = fn ($s) => trim(preg_replace('/[\s\-_]+/', ' ', strtoupper((string) $s)));
            $findExact = fn (array $c) => $examSubjects->first(fn($s) => in_array($normalize($s->subject_name), array_map($normalize, $c), true));
            
            $groups = [
                ['candidates' => ['MATHEMATICS I','MATHEMATICS 1','MATH I','MATH 1','MATHS I','MATHS 1','MATHEMATICS II','MATHEMATICS 2','MATH II','MATH 2','MATHS II','MATHS 2'], 'name' => 'MATHEMATICS', 'code' => 'MATH', 'order' => 100],
                ['candidates' => ['SCIENCE I','SCIENCE 1','SCI I','SCI 1','SCIENCE II','SCIENCE 2','SCI II','SCI 2'], 'name' => 'SCIENCE & TECH', 'code' => 'SCI', 'order' => 200],
                ['candidates' => ['HISTORY', 'HIST', 'GEOGRAPHY', 'GEO'], 'name' => 'SOCIAL SCIENCE', 'code' => 'SST', 'order' => 300]
            ];

            foreach ($groups as $group) {
                $matches = collect($group['candidates'])->map(fn($c) => $findExact([$c]))->filter()->unique('subject_id')->values();
                if ($matches->isNotEmpty()) {
                    $mergedSubjects->push((object) [
                        'subject_id' => $matches->first()->subject_id, 'subject_name' => $group['name'], 'subject_code' => $group['code'],
                        'max_marks' => $matches->sum('max_marks'), 'passing_marks' => $matches->sum('passing_marks'),
                        'display_order' => $matches->min('display_order') ?? $group['order'], 'is_graded' => 0,
                        'merged_ids' => $matches->pluck('subject_id')->filter()->unique()->values()->toArray(),
                    ]);
                }
            }
            $usedIds = $mergedSubjects->pluck('merged_ids')->flatten()->toArray();
            $examSubjects = $mergedSubjects->merge($examSubjects->filter(fn ($s) => !in_array($s->subject_id, $usedIds, true)));
        }

        if (!$isPrePrimary) $examSubjects = $this->ensureAdditionalSubjects($examSubjects, $standardId, $standardName);
        $examSubjects = $examSubjects->map(function ($ex) { if (preg_match('/^SKILL\s*([1-9])(?![0-9])/i', strtoupper(trim((string) ($ex->subject_name ?? ''))), $m)) $ex->display_order = 920 + (int) $m[1]; return $ex; })->sortBy('display_order')->values();
        $marksBySubject = $this->getSubjectMarksMap($studentId, $examId, $standardId);
        $examNameStr = strtoupper(trim((string) $examName)); $isTerm = str_contains($examNameStr, 'TERM'); $isUnitTest = str_contains($examNameStr, 'UNIT TEST');

        return $examSubjects->map(function ($ex) use ($marksBySubject, $manualGrades, $skillGrades, $isTerm, $isUnitTest) {
            $subjectId = (int) $ex->subject_id;
            if (!empty($ex->is_graded)) {
                $grade = null; $skillMap = $this->mapSubjectToSkill($ex->subject_name); $skillId = $skillMap['id']; $skillSlot = $skillMap['slot'];
                if ($skillId && isset($skillGrades[$skillId])) $grade = $skillGrades[$skillId]; elseif ($skillSlot && isset($skillGrades[$skillSlot])) $grade = $skillGrades[$skillSlot]; elseif ($subjectId && isset($skillGrades[$subjectId])) $grade = $skillGrades[$subjectId];
                if (!$grade) {
                    $nameKey = strtoupper(trim($ex->subject_name)); $grade = $skillGrades[$nameKey] ?? null;
                    if (!$grade) { foreach ($skillGrades as $k => $v) { if (is_string($k) && (str_contains($k, $nameKey) || str_contains($nameKey, $k))) { $grade = $v; break; } } }
                    $grade = $grade ?? $manualGrades[$subjectId] ?? $manualGrades[(string) $subjectId] ?? $manualGrades[$this->subjectNameToKey($ex->subject_name)] ?? '-';
                }
                return (object) ['subject_id' => $subjectId, 'subject_name' => $ex->subject_name, 'subject_code' => $ex->subject_code ?? '', 'max_marks' => 0, 'obtained_marks' => 0, 'grade' => $grade, 'is_graded' => 1, 'has_marks' => $grade !== '-' && $grade !== ''];
            }

            $obtained = $maxSum = 0.0; $firstMark = null; $idsToCheck = (isset($ex->merged_ids) && is_array($ex->merged_ids) && !empty($ex->merged_ids)) ? $ex->merged_ids : [$subjectId];
            foreach ($idsToCheck as $sid) {
                $m = $marksBySubject->get($sid) ?? $marksBySubject->get('NAME_' . strtoupper(trim($ex->subject_name)));
                if (!$m) continue; if ($firstMark === null) $firstMark = $m;
                $obtained += (float) ($m->theory_obtained_marks ?? 0) + (float) ($m->oral_obtained_marks ?? 0) + (float) ($m->practical_obtained_marks ?? 0);
                $maxSum += (float) ($m->theory_max_marks ?? 0) + (float) ($m->oral_max_marks ?? 0) + (float) ($m->practical_max_marks ?? 0);
            }

            $maxMarks = (float) ($ex->max_marks ?? 0);
            if (isset($ex->merged_ids) && !empty($ex->merged_ids)) {
                if (in_array(strtoupper($ex->subject_name), ['MATHEMATICS', 'SCIENCE & TECH', 'SOCIAL SCIENCE'])) {
                    if ($isTerm) $maxMarks = 100; elseif ($isUnitTest) $maxMarks = 40; elseif ($maxSum > 0) $maxMarks = $maxSum;
                } else { if ($maxMarks <= 0 && $maxSum > 0) $maxMarks = $maxSum; }
            } else {
                if ($maxMarks <= 0 && $maxSum > 0) $maxMarks = $maxSum; if ($maxMarks <= 0 && $firstMark) $maxMarks = 40;
            }

            $hasMarks = false; foreach ($idsToCheck as $sid) { if ($this->markHasAnyObtained($marksBySubject->get($sid) ?? $marksBySubject->get('NAME_' . strtoupper(trim($ex->subject_name))))) { $hasMarks = true; break; } }
            $grade = '-';
            if ($hasMarks && $firstMark) {
                $isOptional = (int) ($firstMark->is_optional ?? 0) === 1; $isAbsent = (int) ($firstMark->is_absent ?? 0) === 1;
                if ($isOptional && $obtained <= 0) $grade = 'OPT'; elseif ($isAbsent) $grade = 'AB'; else $grade = $this->gradeFromPercentage($maxMarks > 0 ? ($obtained / $maxMarks) * 100 : 0);
            }
            return (object) ['subject_id' => $subjectId, 'subject_name' => $ex->subject_name, 'subject_code' => $ex->subject_code, 'max_marks' => $maxMarks, 'obtained_marks' => $obtained, 'grade' => $grade, 'is_graded' => 0, 'has_marks' => $hasMarks];
        })->values();
    }

    private function ensureAdditionalSubjects($examSubjects, $standardId, $standardName = ''): \Illuminate\Support\Collection {
        $s = strtoupper(trim((string) $standardName));
        if (preg_match('/\b(11|12)\b/', $s) || str_contains($s, 'ELEVENTH') || str_contains($s, 'TWELFTH') || str_contains($s, 'XI') || str_contains($s, 'XII')) return $examSubjects;
        $existingNames = $examSubjects->map(fn ($s) => strtoupper(trim((string) $s->subject_name)))->all();
        $is9thOr10th = $this->isHighSchoolStandard($standardName);
        $additional = [
            ['display' => 'DEFENCE STUDIES', 'search' => ['DEFENCE STUDIES', 'DEFENSE SCIENCE', 'DISCI', 'D.S.', 'DS'], 'order' => 900, 'only_hs' => true],
            ['display' => 'WATER SECURITY', 'search' => ['WATER SECURITY', 'W. SECURITY', 'W\.S.', 'WS'], 'order' => 901, 'only_hs' => true],
            ['display' => 'SKILL 1 - ARTS', 'search' => ['ART', 'DRAWING', 'A/D/M/T', 'ARTS', 'CRAFT', 'COLOURING'], 'order' => 902, 'only_hs' => false],
            ['display' => 'SKILL 2 - WORK EXP', 'search' => ['WORK EXPERIENCE', 'W EXP', 'W\.E.', 'WORK EDUCATION', 'VEDIC MATH', 'ABACUS', 'COMPUTER', 'ROBOTICS'], 'order' => 903, 'only_hs' => false],
            ['display' => 'SKILL 3 - PHYSICAL EDUCATION & HEALTH', 'search' => ['PT & HEALTH', 'PT', 'UPASNA', 'CHINTANIKA', 'P ED & HEALTH', 'P ED', 'PED', 'PHYSICAL EDUCATION', 'HEALTH'], 'order' => 904, 'only_hs' => false],
            ['display' => 'SKILL 4 - OTHER (LIBRARY & SANSKRIT BHARTI)', 'search' => ['OTHERS', 'SANSKRIT BHARTI', 'LIBRARY'], 'order' => 905, 'only_hs' => false],
        ];
        $allSearchNames = collect($additional)->pluck('search')->flatten()->unique()->toArray();
        $subjectLookup = empty($allSearchNames) ? collect() : DB::table('subjects')->whereIn('subject_name', $allSearchNames)->where('is_active', 1)->get()->keyBy('subject_name');

        foreach ($additional as $add) {
            if ($add['only_hs'] && !$is9thOr10th) continue;
            $found = false; foreach ($add['search'] as $name) { if (in_array($name, $existingNames, true)) { $found = true; break; } }
            if ($found) continue;
            $realSubject = null; foreach ($add['search'] as $name) { if (isset($subjectLookup[$name])) { $realSubject = $subjectLookup[$name]; break; } }
            $examSubjects->push((object) ['subject_id' => $realSubject ? (int) $realSubject->id : 0, 'subject_name' => $add['display'], 'subject_code' => $realSubject->subject_code ?? '', 'max_marks' => 0, 'passing_marks' => 0, 'display_order' => $add['order'], 'is_graded' => 1]);
            $existingNames[] = $add['display'];
        }
        return $examSubjects->sortBy(fn ($s) => (int) ($s->display_order ?? 999))->values();
    }

    private function computeOverall($subjects): array {
        $totalMax = $totalObt = 0.0; $anyMark = false; $hasFail = false;
        foreach ($subjects as $s) {
            if (!empty($s->is_graded)) continue;
            $status = strtoupper(trim((string) ($s->subject_result ?? $s->grade ?? '')));
            if ($status === 'OPT') continue;
            $max = (float) ($s->max_marks ?? 0); if ($max > 0) $totalMax += $max;
            $hasMarks = property_exists($s, 'has_marks') ? (bool) $s->has_marks : ($status !== '' && $status !== '-');
            if ($hasMarks) { $anyMark = true; $totalObt += (float) ($s->obtained_marks ?? 0); if (in_array($status, ['FAIL', 'AB', 'ABSENT'], true)) $hasFail = true; }
        }
        if (!$anyMark || $totalMax <= 0) return [null, '-', 'PENDING', $totalMax, $totalObt];
        $pct = round(($totalObt * 100) / $totalMax, 2);
        return [$pct, $this->gradeFromPercentage($pct), ($hasFail || $pct < 35) ? 'FAIL' : 'PASS', $totalMax, $totalObt];
    }

    private function gradeFromPercentage($percentage): string {
        $p = (float) $percentage;
        if ($p >= 91) return 'A1'; if ($p >= 81) return 'A2'; if ($p >= 71) return 'B1'; if ($p >= 61) return 'B2';
        if ($p >= 51) return 'C1'; if ($p >= 41) return 'C2'; if ($p >= 33) return 'D'; if ($p >= 21) return 'E1'; if ($p >= 1) return 'E2';
        return 'F';
    }

    private function gradeToPoint($grade): int {
        return ['A1'=>9, 'A2'=>8, 'B1'=>7, 'B2'=>6, 'C1'=>5, 'C2'=>4, 'D'=>3, 'E1'=>2, 'E2'=>1, 'F'=>0][strtoupper(trim((string) $grade))] ?? 0;
    }

    private function pointToGrade($point): string {
        if ($point >= 8.5) return 'A1'; if ($point >= 7.5) return 'A2'; if ($point >= 6.5) return 'B1'; if ($point >= 5.5) return 'B2';
        if ($point >= 4.5) return 'C1'; if ($point >= 3.5) return 'C2'; if ($point >= 2.5) return 'D'; if ($point >= 1.5) return 'E1'; if ($point >= 0.5) return 'E2';
        return 'F';
    }

    private function calculateAcademicAverageGrade($subjects): string {
        $totalPoints = $count = 0;
        foreach ($subjects as $s) {
            if (!empty($s->is_graded) || (property_exists($s, 'has_marks') && !$s->has_marks)) continue;
            $grade = strtoupper(trim((string) ($s->grade ?? '-'))); $status = strtoupper(trim((string) ($s->subject_result ?? '-')));
            if (in_array($status, ['OPT', 'AB', 'ABSENT'], true)) continue;
            if (in_array($grade, ['-', 'PASS', 'FAIL', 'AB', 'OPT', 'ABSENT', ''], true)) {
                $max = (float) ($s->max_marks ?? 0); $obt = (float) ($s->obtained_marks ?? 0);
                if ($max > 0) $grade = $this->gradeFromPercentage(($obt / $max) * 100); else continue;
            }
            if (in_array($grade, ['A1','A2','B1','B2','C1','C2','D','E1','E2','F'], true)) { $totalPoints += $this->gradeToPoint($grade); $count++; }
        }
        return $count === 0 ? '-' : $this->pointToGrade($totalPoints / $count);
    }

    private function calculateSpecialSkillAverageGrade($subjects): string {
        $totalPoints = $count = 0; $hasAcademic = false;
        $skillGradeMap = ['C' => 1, 'B' => 2, 'B+' => 3, 'A' => 4, 'A+' => 5];
        $academicGradeMap = ['A1'=>9, 'A2'=>8, 'B1'=>7, 'B2'=>6, 'C1'=>5, 'C2'=>4, 'D'=>3, 'E1'=>2, 'E2'=>1, 'F'=>0];
        foreach ($subjects as $s) {
            if (empty($s->is_graded)) continue;
            $grade = strtoupper(trim((string) ($s->grade ?? $s->subject_result ?? '-')));
            if (in_array($grade, ['-', 'OPT', 'AB', 'PASS', 'FAIL', ''], true)) continue;
            if (isset($skillGradeMap[$grade])) { $totalPoints += $skillGradeMap[$grade]; $count++; }
            elseif (isset($academicGradeMap[$grade])) { $totalPoints += $academicGradeMap[$grade]; $count++; $hasAcademic = true; }
        }
        if ($count === 0) return '-';
        $avgValue = $totalPoints / $count;
        if ($hasAcademic) return $this->pointToGrade($avgValue);
        if ($avgValue >= 4.5) return 'A+'; if ($avgValue >= 3.5) return 'A'; if ($avgValue >= 2.5) return 'B+'; if ($avgValue >= 1.5) return 'B';
        return 'C';
    }

    private function defaultAttendanceData(): array {
        $data = [];
        foreach (['Jyeshtha'=>31, 'Ashadh'=>31, 'Shravan'=>31, 'Bhadrapad'=>31, 'Ashwin'=>30, 'Kartik'=>30, 'Margshirsh'=>30, 'Paush'=>30, 'Magh'=>30, 'Phalgun'=>30, 'Chaitra'=>30, 'Vaishakh'=>31] as $month => $days) {
            $data[$month] = ['total_days' => (string) $days, 'working' => '', 'present' => '', 'absent' => ''];
        }
        return $data;
    }

    public function examsByStandard(Request $request) {
        try {
            $data = $request->validate(['standard_id' => 'required|integer', 'academic_year_id' => 'nullable|integer']);
            $query = ExamMaster::query()->where('standard_id', $data['standard_id']);
            if (!empty($data['academic_year_id'])) $query->where(fn($q) => $q->where('academic_year_id', $data['academic_year_id'])->orWhereNull('academic_year_id'));
            return response()->json($query->orderBy('exam_name')->get(['id', 'exam_name']));
        } catch (\Throwable $e) {
            Log::error('examsByStandard failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load exams.'], 500);
        }
    }
}