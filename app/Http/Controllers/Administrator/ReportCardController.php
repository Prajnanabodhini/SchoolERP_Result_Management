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
    private function isHighSchoolStandard($standardName): bool
    {
        $s = strtoupper(trim((string) $standardName));
        return $s !== '' && (preg_match('/\b(9|10)\b/', $s) || str_contains($s, 'NINTH') || str_contains($s, 'TENTH'));
    }

    /**
     * A valid stored PEN is exactly 11 decimal digits.
     * Keep it as a string so leading zeroes are not lost.
     */
    private function isElevenDigitPen($value): bool
    {
        return preg_match('/^\d{11}$/D', trim((string) $value)) === 1;
    }

    /**
     * saralid can contain either an 11-digit value or a 19-digit value.
     * Keep it as a string; it must never be converted to an integer.
     */
    private function isSupportedSaralId($value): bool
    {
        return preg_match('/^(?:\d{11}|\d{19})$/D', trim((string) $value)) === 1;
    }

    /**
     * PEN display/save precedence:
     * 1. An exactly 11-digit PEN already saved in student_progress_cards.
     * 2. Otherwise, saralid from MSSQL when it is 11 or 19 numeric digits.
     * 3. Otherwise, blank. StudentID is never a PEN fallback.
     */
    private function resolvePreferredPen($storedPen, $erpSaralId): string
    {
        $storedPen = trim((string) $storedPen);
        if ($this->isElevenDigitPen($storedPen)) {
            return $storedPen;
        }

        $erpSaralId = trim((string) $erpSaralId);
        if ($this->isSupportedSaralId($erpSaralId)) {
            return $erpSaralId;
        }

        return '';
    }

    private function isPrePrimaryStandard($standardName): bool
    {
        return preg_match('/\b(NURSERY|JR|SR|KINDERGARTEN|KG|LKG|UKG)\b/i', strtoupper(trim((string) $standardName))) === 1;
    }

    private function cleanStudentName(string $studname, string $fathername): string
    {
        $studname = trim($studname);
        $fathername = trim($fathername);
        if ($fathername === '') return $studname;
        $fatherUpper = strtoupper($fathername);
        $studUpper = strtoupper($studname);
        while (str_ends_with($studUpper, ' ' . $fatherUpper) || $studUpper === $fatherUpper) {
            if ($studUpper === $fatherUpper) {
                $studname = '';
                $studUpper = '';
                break;
            }
            $studname = trim(substr($studname, 0, -strlen($fatherUpper) - 1));
            $studUpper = strtoupper($studname);
        }
        return $studname . ' ' . $fathername;
    }

    private function ensureArray($data): array
    {
        if (is_array($data)) return $data;
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (is_array($decoded)) return $decoded;
            $unserialized = @unserialize($data);
            if (is_array($unserialized)) return $unserialized;
            return [];
        }
        return [];
    }

    private function normName($name): string
    {
        return strtoupper(preg_replace('/[^A-Z]/', '', (string) $name));
    }

    private function nameTokens($name): array
    {
        $name = strtoupper(trim((string) $name));
        if ($name === '') return [];
        return array_values(array_filter(preg_split('/\s+/', $name) ?: [], fn ($t) => $t !== ''));
    }

    private function getErpStudentByStudentid($studentId)
    {
        if (empty($studentId)) return null;
        try {
            return DB::connection('sqlsrv_olderp')->table('dbo.FeeMstStudent as f')
                ->select(['f.*', 'f.saralid as gov_student_id', 'f.taluka as legacy_taluka', 'f.district as legacy_district'])
                ->selectRaw("COALESCE(NULLIF(LTRIM(RTRIM(f.locaddr)), ''), NULLIF(LTRIM(RTRIM(f.peraddr)), '')) AS legacy_address")
                ->where('f.Studentid', $studentId)
                ->first();
        } catch (\Throwable $e) {
            Log::warning('getErpStudentByStudentid failed', ['id' => $studentId, 'err' => $e->getMessage()]);
            return null;
        }
    }

    private function resolveCanonicalStudent($inputId, $enrollment = null)
    {
        if (empty($inputId)) return null;
        $student = $this->getErpStudentByStudentid($inputId);
        if ($student) {
            if ($enrollment && !empty($enrollment->studname)) {
                $expected = $this->normName(($enrollment->studname ?? '') . ($enrollment->fathername ?? ''));
                $got = $this->normName(($student->studname ?? '') . ($student->fathername ?? ''));
                if ($expected && $got && strpos($got, $expected) === false && strpos($expected, $got) === false) {
                    Log::warning('resolveCanonicalStudent: Studentid matched but name mismatch (continuing).', [
                        'input_id' => $inputId,
                        'expected' => $enrollment->studname ?? '',
                        'found' => $student->studname ?? '',
                    ]);
                }
            }
            return $student;
        }
        if ($enrollment && !empty($enrollment->Studentid) && (string) $enrollment->Studentid !== (string) $inputId) {
            return $this->getErpStudentByStudentid($enrollment->Studentid);
        }
        return null;
    }

    private function getProgressCardRecord($studentId, $saralid, $examId, $yearId, $fallbackName = '')
    {
        $studentId    = trim((string) $studentId);
        $saralid      = trim((string) $saralid);
        $fallbackName = trim((string) $fallbackName);

        if ($studentId === '' && $saralid === '' && $fallbackName === '') return null;

        $findLatest = function (string $column, string $value) use ($examId, $yearId) {
            return StudentProgressCard::query()
                ->where($column, $value)
                ->where('exam_master_id', $examId)
                ->where('academic_year_id', $yearId)
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->first();
        };

        if ($studentId !== '') {
            if ($p = $findLatest('studentid', $studentId)) return $p;
            if ($p = $findLatest('pen', $studentId)) return $p;
        }

        if ($saralid !== '' && $saralid !== $studentId) {
            if ($p = $findLatest('pen', $saralid)) return $p;
        }

        if ($fallbackName !== '') {
            if ($p = $this->findProgressCardByFullName($fallbackName, $examId, $yearId)) return $p;
        }

        return null;
    }

    private function findProgressCardByFullName($targetName, $examId, $yearId)
    {
        $targetTokens = $this->nameTokens($targetName);
        if (empty($targetTokens)) return null;

        $candidates = StudentProgressCard::query()
            ->where('exam_master_id', $examId)
            ->where('academic_year_id', $yearId)
            ->whereNotNull('full_student_name')
            ->where('full_student_name', '<>', '')
            ->get();

        $matches = [];
        foreach ($candidates as $cand) {
            $candTokens = $this->nameTokens($cand->full_student_name);
            if (empty($candTokens)) continue;
            $min = min(count($targetTokens), count($candTokens));
            if ($min === 0) continue;
            if (count(array_intersect($targetTokens, $candTokens)) === $min) $matches[] = $cand;
        }

        if (count($matches) === 1) {
            return $matches[0];
        }

        return null;
    }

    private function buildReportFromProgressCard($progress, $examId = null, $yearId = null)
    {
        $examId     = $examId     ?? ($progress->exam_master_id ?? null);
        $yearId     = $yearId     ?? ($progress->academic_year_id ?? null);
        $standardId = $progress->standard_id ?? null;
        $divisionId = $progress->division_id ?? null;

        $exam     = $examId     ? DB::table('exam_masters')->where('id', $examId)->first()   : null;
        $year     = $yearId     ? DB::table('academic_years')->where('id', $yearId)->first() : null;
        $standard = $standardId ? DB::table('standards')->where('id', $standardId)->first()  : null;
        $division = $divisionId ? DB::table('divisions')->where('id', $divisionId)->first()  : null;

        // HYDRATE MISSING DATA FROM ERP (in-memory only, does NOT save to DB).
        // A valid 11-digit PEN already saved in MySQL always has priority.
        $student = null; // Lazy load
        $storedPen = trim((string) ($progress->pen ?? ''));

        if (!$this->isElevenDigitPen($storedPen)) {
            $student = $this->resolveCanonicalStudent($progress->studentid ?? null);
        }

        $progress->pen = $this->resolvePreferredPen(
            $storedPen,
            $student->saralid ?? null
        );

        // Also hydrate other missing data if needed
        if (empty($progress->full_student_name) || empty($progress->gr_no) || empty($progress->roll_no)) {
            if (!$student) {
                $student = $this->resolveCanonicalStudent($progress->studentid ?? $progress->pen);
            }
            if ($student) {
                if (empty($progress->full_student_name)) {
                    $studname = trim((string) ($student->studname ?? ''));
                    $fathername = trim((string) ($student->fathername ?? ''));
                    $progress->full_student_name = $this->cleanStudentName($studname, $fathername);
                    $progress->father_name = $fathername;
                }
                
                $erpYearId = $year ? ($year->old_year_id ?? substr((string) $year->year_name, 0, 4)) : null;
                if (empty($progress->gr_no) || empty($progress->roll_no)) {
                    $substudent = $this->getErpSubstudent($progress->studentid ?? $progress->pen, $erpYearId);
                    if ($substudent) {
                        if (empty($progress->gr_no)) {
                            $progress->gr_no = $substudent->regno ?? '';
                        }
                        if (empty($progress->roll_no)) {
                            $progress->roll_no = $substudent->rollno ?? '';
                        }
                    }
                }

                if (empty($progress->mother_name)) {
                    $progress->mother_name = $student->mothername ?? '';
                }
                if (empty($progress->gender)) {
                    $progress->gender = $student->gender ?? '';
                }
                if (empty($progress->date_of_birth)) {
                    $progress->date_of_birth = $student->birthdate ?? '';
                }
                if (empty($progress->aadhar_no)) {
                    $progress->aadhar_no = $student->adharno ?? '';
                }
            }
        }

        $fullName  = trim((string) ($progress->full_student_name ?? ''));
        $nameParts = $fullName !== '' ? preg_split('/\s+/', $fullName, 2) : [''];

        return (object) [
            'pen'                  => $progress->pen ?? '',
            'student_id'           => (int) $progress->studentid,
            'gov_student_id'       => $progress->pen ?? '',
            'studname'             => $nameParts[0] ?? '',
            'father_name'          => $progress->father_name ?? '',
            'full_student_name'    => $fullName,
            'mother_name'          => $progress->mother_name ?? '',
            'gender'               => $progress->gender ?? '',
            'date_of_birth'        => $progress->date_of_birth ?? '',
            'mother_tongue'        => $progress->mother_tongue ?? 'MARATHI',
            'address'              => $progress->address ?? '',
            'rollno'               => $progress->roll_no ?? '',
            'gr_no'                => $progress->gr_no ?? '',
            'aadhar_no'            => $progress->aadhar_no ?? '',
            'blood_group'          => $progress->blood_group ?? '',
            'phone'                => $progress->phone ?? '',
            'email'                => $progress->email ?? '',
            'school_timing'        => $progress->school_timing ?? '8:15am to 3:00pm',
            'exam_master_id'       => (int) $examId,
            'academic_year_id'     => (int) $yearId,
            'standard_id'          => (int) ($standardId ?? 0),
            'division_id'          => (int) ($divisionId ?? 0),
            'exam_name'            => $exam->exam_name ?? '',
            'standard_name'        => $standard->standard_name ?? '',
            'division_name'        => $division->division_name ?? '',
            'year_name'            => $year->year_name ?? '',
            'rank'                 => null,
            'percentage'           => $progress->percentage ?? null,
            'grade'                => $progress->grade ?? null,
            'result'               => $progress->result ?? null,
            'total_max_marks'      => $progress->total_max_marks ?? 0,
            'total_obtained_marks' => $progress->total_obtained_marks ?? 0,
        ];
    }

    private function buildProgressCardDataFromSaved($progress, $exam, $year, $examId, $yearId)
    {
        $report = $this->buildReportFromProgressCard($progress, $examId, $yearId);

        $realStudentId = (int) ($progress->studentid ?? 0);
        $pen = (string) ($progress->pen ?? '');

        $defaultAttendance = $this->defaultAttendanceData();
        $savedAttendance   = $this->ensureArray($progress->attendance_data ?? []);
        foreach ($defaultAttendance as $month => $defaults) {
            if (isset($savedAttendance[$month]) && is_array($savedAttendance[$month])) {
                $defaultAttendance[$month] = array_merge($defaults, $savedAttendance[$month]);
            }
        }

        $progress->attendance_data     = $defaultAttendance;
        $progress->descriptive_records = $this->ensureArray($progress->descriptive_records ?? []);
        $progress->second_term_grades  = $this->ensureArray($progress->second_term_grades ?? []);
        $progress->class_test_data     = $this->ensureArray($progress->class_test_data ?? []);
        $progress->school_days_data    = $this->ensureArray($progress->school_days_data ?? []);
        $progress->academic_grades     = $this->ensureArray($progress->academic_grades ?? []);
        $progress->skill_grades        = $this->ensureArray($progress->skill_grades ?? []);

        $teacherRemark = $this->ensureArray($progress->teacher_remark ?? []);
        if (!array_key_exists('text', $teacherRemark)) {
            $teacherRemark['text'] = '';
        }
        $progress->teacher_remark = $teacherRemark;

        $report->attendance_data     = $defaultAttendance;
        $report->teacher_remark      = $teacherRemark;
        $report->teacher_remark_text = $teacherRemark['text'] ?? '';

        $yearLabel = $year->year_name ?? '';
        $shakYear  = '';
        if ($yearLabel) {
            $parts    = explode('-', $yearLabel);
            $shakYear = count($parts) == 2
                ? 'Shak: ' . (intval($parts[0]) - 78) . '-' . (intval($parts[1]) - 78)
                : 'Shak: ' . (intval(substr($yearLabel, 0, 4)) - 78);
        }
        $report->shak_year = $shakYear;

        $examName = strtoupper(trim((string) ($exam->exam_name ?? '')));
        $termLabel = 'Exam';
        if (str_contains($examName, 'TERM 1'))         $termLabel = 'Term 1';
        elseif (str_contains($examName, 'TERM 2'))     $termLabel = 'Term 2';
        elseif (str_contains($examName, 'UNIT TEST 1'))$termLabel = 'Unit Test 1';
        elseif (str_contains($examName, 'UNIT TEST 2'))$termLabel = 'Unit Test 2';
        elseif (str_contains($examName, 'UNIT TEST 3'))$termLabel = 'Unit Test 3';
        elseif (str_contains($examName, 'UNIT TEST 4'))$termLabel = 'Unit Test 4';
        elseif (str_contains($examName, 'ANNUAL'))     $termLabel = 'Annual';

        $standardName = $report->standard_name ?? '';
        $isPrePrimary = $this->isPrePrimaryStandard($standardName);
        $isHighSchool = $this->isHighSchoolStandard($standardName);
        $skillObservations = collect();

        if ($isPrePrimary || $isHighSchool) {
            $skillObservations = DB::table('student_skill_observations as sso')
                ->leftJoin('subjects as s', 's.id', '=', 'sso.subject_id')
                ->where(function ($q) use ($realStudentId, $pen) {
                    $q->where('sso.student_id', $realStudentId);
                    if (!empty($pen) && (string) $pen !== (string) $realStudentId) $q->orWhere('sso.student_id', $pen);
                })
                ->where('sso.exam_master_id', $examId)
                ->where('sso.academic_year_id', $yearId)
                ->select('sso.*', 's.subject_name')->get();
        }

        $subjects = $this->buildProgressCardSubjects(
            $realStudentId,
            $examId,
            (int) ($progress->standard_id ?? 0),
            $yearId,
            $progress->second_term_grades,
            $standardName,
            $report->exam_name,
            $skillObservations,
            $pen
        );

        if ($report->percentage === null) {
            [$pct, $grade, $result, $tMax, $tObt] = $this->computeOverall($subjects);
            $report->percentage           = $pct;
            $report->grade                = $grade;
            $report->result               = $result;
            $report->total_max_marks      = $tMax;
            $report->total_obtained_marks = $tObt;
        }

        return compact('report', 'subjects', 'progress', 'termLabel', 'skillObservations') + [
            'academicAverageGrade'     => $this->calculateAcademicAverageGrade($subjects),
            'specialSkillAverageGrade' => $this->calculateSpecialSkillAverageGrade($subjects, $report->standard_name),
        ];
    }

    private function savedValue($saved, $fallback = '')
    {
        if ($saved === null) return $fallback ?? '';
        if (is_string($saved) && trim($saved) === '') return $fallback ?? '';
        return $saved;
    }

    public function index()
    {
        return view('report-card.index', [
            'academicYears' => AcademicYear::orderByDesc('year_name')->get(),
            'exams' => ExamMaster::all(),
            'standards' => Standard::all(),
            'divisions' => Division::all(),
            'students' => collect(),
            'report' => null,
            'subjects' => collect(),
            'error' => null,
        ]);
    }

    public function search(Request $request)
    {
        $students = collect();
        if ($request->filled('academic_year_id') && $request->filled('standard_id') && $request->filled('division_id')) {
            if ($year = AcademicYear::find($request->academic_year_id)) {
                $yearId = $year->old_year_id ?? substr($year->year_name, 0, 4);
                $students = StudentHelper::getStudentsDirectERP($yearId, $request->standard_id, $request->division_id);
                $students = $students->map(function ($student) {
                    $student->clean_full_name = $this->cleanStudentName($student->studname ?? '', $student->fathername ?? '');
                    return $student;
                });
            }
        }
        return view('report-card.index', [
            'academicYears' => AcademicYear::orderByDesc('year_name')->get(),
            'exams' => ExamMaster::all(),
            'standards' => Standard::all(),
            'divisions' => Division::all(),
            'students' => $students,
            'report' => null,
            'subjects' => collect(),
            'error' => null,
            'academic_year_id' => $request->academic_year_id,
            'exam_master_id' => $request->exam_master_id,
            'standard_id' => $request->standard_id,
            'division_id' => $request->division_id,
        ]);
    }

    public function show(Request $request)
    {
        set_time_limit(120);
        $students = collect();
        $report = null;
        $subjects = collect();
        $error = null;

        if ($request->filled('academic_year_id') && $request->filled('standard_id') && $request->filled('division_id')) {
            if ($academicYear = AcademicYear::find($request->academic_year_id)) {
                $erpYearId = $academicYear->old_year_id ?? substr((string) $academicYear->year_name, 0, 4);
                $students = StudentHelper::getStudentsDirectERP($erpYearId, $request->standard_id, $request->division_id);
                $students = $students->map(function ($student) {
                    $student->clean_full_name = $this->cleanStudentName($student->studname ?? '', $student->fathername ?? '');
                    return $student;
                });
            }
        }

        $viewData = [
            'academicYears' => AcademicYear::orderByDesc('year_name')->get(),
            'exams' => ExamMaster::all(),
            'standards' => Standard::all(),
            'divisions' => Division::all(),
            'students' => $students,
            'report' => null,
            'subjects' => collect(),
            'academic_year_id' => $request->academic_year_id,
            'exam_master_id' => $request->exam_master_id,
            'standard_id' => $request->standard_id,
            'division_id' => $request->division_id,
        ];

        if (!$request->filled('student_id')) {
            return view('report-card.index', $viewData)->with('error', 'Please select a Student.');
        }

        $requestedStudentId = trim((string) $request->student_id);
        $examId             = (int) $request->exam_master_id;
        $yearId             = (int) $request->academic_year_id;

        $progress = $this->getProgressCardRecord($requestedStudentId, $requestedStudentId, $examId, $yearId);

        if ($progress) {
            $report = $this->buildReportFromProgressCard($progress, $examId, $yearId);

            $manualGrades = $this->ensureArray($progress->second_term_grades ?? []);
            $subjects = $this->buildReportCardSubjects(
                (int) $progress->studentid,
                $examId,
                (int) ($progress->standard_id ?? 0),
                $yearId,
                $manualGrades,
                (string) ($progress->pen ?? '')
            );

            if ($report->percentage === null) {
                [$pct, $grade, $result, $tMax, $tObt] = $this->computeOverall($subjects);
                $report->percentage           = $pct;
                $report->grade                = $grade;
                $report->result               = $result;
                $report->total_max_marks      = $tMax;
                $report->total_obtained_marks = $tObt;
            }

            return view('report-card.index', array_merge($viewData, [
                'report'   => $report,
                'subjects' => $subjects,
                'error'    => null,
            ]));
        }

        $selectedEnrollment = $students->first(fn ($row) => (string) ($row->Studentid ?? '') === $requestedStudentId);
        if (!$selectedEnrollment) {
            return view('report-card.index', $viewData)->with('error', 'The selected student does not belong to the selected filters.');
        }

        $student = $this->resolveCanonicalStudent($requestedStudentId, $selectedEnrollment);
        if (!$student) return view('report-card.index', $viewData)->with('error', 'Student not found in ERP.');

        $realStudentId = $student->Studentid;
        $saralId = trim((string) ($student->saralid ?? ''));
        $substudent = $selectedEnrollment;

        $exam = DB::table('exam_masters')->where('id', $examId)->first();
        $standard = DB::table('standards')->where('id', $request->standard_id)->first();
        $division = DB::table('divisions')->where('id', $request->division_id)->first();
        $year = DB::table('academic_years')->where('id', $yearId)->first();

        $studname = trim((string) ($student->studname ?? ''));
        $fathername = trim((string) ($student->fathername ?? ''));
        $fullName = $this->cleanStudentName($studname, $fathername);

        $progress = $this->getProgressCardRecord($realStudentId, $saralId, $examId, $yearId, $fullName);
        $pen = $this->resolvePreferredPen(
            $progress->pen ?? null,
            $saralId
        );
        if ($progress) {
            $progress->pen = $pen; // In-memory only; the save action persists it.
        }
        $manualGrades = $this->ensureArray($progress->second_term_grades ?? []);
        $displayPen = $pen;

        $report = (object) [
            'pen' => $displayPen,
            'student_id' => (int) $realStudentId,
            'gov_student_id' => $displayPen,
            'studname' => $progress && $progress->full_student_name ? explode(' ', $progress->full_student_name)[0] : $studname,
            'father_name' => $progress->father_name ?? $fathername,
            'full_student_name' => $progress->full_student_name ?? $fullName,
            'mother_name' => $progress->mother_name ?? $student->mothername ?? '',
            'gender' => $progress->gender ?? $student->gender ?? '',
            'date_of_birth' => $progress->date_of_birth ?? $student->birthdate ?? '',
            'mother_tongue' => $progress->mother_tongue ?? $student->mtounge ?? 'MARATHI',
            'address' => $progress->address ?? $this->resolveStudentAddress($student),
            'rollno' => $progress->roll_no ?? $substudent->rollno ?? '',
            'gr_no' => $progress->gr_no ?? $substudent->regno ?? '',
            'aadhar_no' => $progress->aadhar_no ?? $student->adharno ?? '',
            'blood_group' => $progress->blood_group ?? '',
            'phone' => $progress->phone ?? $student->fathermobile ?? $student->resphone1 ?? '',
            'email' => $progress->email ?? $student->email ?? '',
            'school_timing' => $progress->school_timing ?? '8:15am to 3:00pm',
            'exam_master_id' => (int) $examId,
            'academic_year_id' => (int) $yearId,
            'standard_id' => (int) $request->standard_id,
            'division_id' => (int) $request->division_id,
            'exam_name' => $exam->exam_name ?? '',
            'standard_name' => $standard->standard_name ?? '',
            'division_name' => $division->division_name ?? '',
            'year_name' => $year->year_name ?? '',
            'rank' => null,
            'percentage' => $progress->percentage ?? null,
            'grade' => $progress->grade ?? null,
            'result' => $progress->result ?? null,
            'total_max_marks' => $progress->total_max_marks ?? 0,
            'total_obtained_marks' => $progress->total_obtained_marks ?? 0,
        ];

        $subjects = $this->buildReportCardSubjects(
            (int) $realStudentId, $examId,
            (int) $request->standard_id, $yearId,
            $manualGrades, $pen
        );

        if ($report->percentage === null) {
            [$pct, $grade, $result, $tMax, $tObt] = $this->computeOverall($subjects);
            $report->percentage = $pct;
            $report->grade = $grade;
            $report->result = $result;
            $report->total_max_marks = $tMax;
            $report->total_obtained_marks = $tObt;
        }

        return view('report-card.index', array_merge($viewData, [
            'report' => $report, 'subjects' => $subjects, 'error' => null,
        ]));
    }

    public function print($studentId, $examId, $yearId)
    {
        set_time_limit(120);

        if (!$year = DB::table('academic_years')->where('id', $yearId)->first()) {
            abort(404, 'Academic year not found.');
        }

        $requestedStudentId = trim((string) $studentId);

        $savedCard = $this->getProgressCardRecord($requestedStudentId, $requestedStudentId, $examId, $yearId);
        if ($savedCard) {
            $exam = DB::table('exam_masters')->where('id', $examId)->first();

            $report = $this->buildReportFromProgressCard($savedCard, $examId, $yearId);
            $manualGrades = $this->ensureArray($savedCard->second_term_grades ?? []);

            $subjects = $this->buildReportCardSubjects(
                (int) $savedCard->studentid,
                (int) $examId,
                (int) ($savedCard->standard_id ?? 0),
                (int) $yearId,
                $manualGrades,
                (string) ($savedCard->pen ?? '')
            );

            if ($report->percentage === null) {
                [$pct, $grade, $result, $tMax, $tObt] = $this->computeOverall($subjects);
                $report->percentage           = $pct;
                $report->grade                = $grade;
                $report->result               = $result;
                $report->total_max_marks      = $tMax;
                $report->total_obtained_marks = $tObt;
            }

            return view('report-card.print', compact('report', 'subjects'));
        }

        $student = $this->resolveCanonicalStudent($studentId);
        if (!$student) abort(404, 'Student not found in ERP.');

        $realStudentId = $student->Studentid;
        $saralId = trim((string) ($student->saralid ?? ''));
        $erpYearId = $year->old_year_id ?? substr((string) ($year->year_name ?? ''), 0, 4);
        $exam = DB::table('exam_masters')->where('id', $examId)->first();

        $studname = trim((string) ($student->studname ?? ''));
        $fathername = trim((string) ($student->fathername ?? ''));
        $fullName = $this->cleanStudentName($studname, $fathername);
        $progress = $this->getProgressCardRecord($realStudentId, $saralId, $examId, $yearId, $fullName);
        $pen = $this->resolvePreferredPen(
            $progress->pen ?? null,
            $saralId
        );
        if ($progress) {
            $progress->pen = $pen; // In-memory only; the save action persists it.
        }
        $substudent = $this->getErpSubstudent($realStudentId, $erpYearId);
        $standardId = $exam->standard_id ?? $progress->standard_id ?? null;

        $sampleMark = DB::table('student_marks')
            ->where(function ($q) use ($realStudentId, $pen) {
                $q->where('student_id', $realStudentId);
                if (!empty($pen) && (string) $pen !== (string) $realStudentId) $q->orWhere('student_id', $pen);
            })
            ->where('exam_master_id', $examId)->where('academic_year_id', $yearId)
            ->orderByDesc('id')->first();

        if (!$sampleMark) {
            $sampleMark = DB::table('student_marks')
                ->where(function ($q) use ($realStudentId, $pen) {
                    $q->where('student_id', $realStudentId);
                    if (!empty($pen) && (string) $pen !== (string) $realStudentId) $q->orWhere('student_id', $pen);
                })
                ->where('academic_year_id', $yearId)->orderByDesc('id')->first();
        }

        $standardId = $standardId ?? ($sampleMark->standard_id ?? null);
        $divisionId = $this->resolveLaravelDivisionId($realStudentId, $pen, $yearId, $examId, $standardId, $progress, $substudent, $erpYearId);

        if ($standardId && $divisionId && !$substudent) {
            $substudent = $this->getErpSubstudent($realStudentId, $erpYearId, $standardId, $divisionId);
            $progress = $progress ?? $this->getProgressCardRecord($realStudentId, $pen, $examId, $yearId, $fullName);
            $divisionId = $this->resolveLaravelDivisionId($realStudentId, $pen, $yearId, $examId, $standardId, $progress, $substudent, $erpYearId);
        }

        $standard = $standardId ? DB::table('standards')->where('id', $standardId)->first() : null;
        $division = $divisionId ? DB::table('divisions')->where('id', $divisionId)->first() : null;
        $manualGrades = $this->ensureArray($progress->second_term_grades ?? []);
        $displayPen = $pen;

        $report = (object) [
            'pen' => $displayPen, 'student_id' => (int) $realStudentId, 'gov_student_id' => $displayPen,
            'studname' => $progress && $progress->full_student_name ? explode(' ', $progress->full_student_name)[0] : $studname,
            'father_name' => $progress->father_name ?? $fathername,
            'full_student_name' => $progress->full_student_name ?? $fullName,
            'mother_name' => $progress->mother_name ?? $student->mothername ?? '',
            'gender' => $progress->gender ?? $student->gender ?? '',
            'date_of_birth' => $progress->date_of_birth ?? $student->birthdate ?? '',
            'mother_tongue' => $progress->mother_tongue ?? $student->mtounge ?? 'MARATHI',
            'address' => $progress->address ?? $this->resolveStudentAddress($student),
            'rollno' => $progress->roll_no ?? $substudent->rollno ?? '',
            'gr_no' => $progress->gr_no ?? $substudent->regno ?? '',
            'aadhar_no' => $progress->aadhar_no ?? $student->adharno ?? '',
            'blood_group' => $progress->blood_group ?? '',
            'phone' => $progress->phone ?? $student->fathermobile ?? $student->resphone1 ?? '',
            'email' => $progress->email ?? $student->email ?? '',
            'school_timing' => $progress->school_timing ?? '8:15am to 3:00pm',
            'exam_master_id' => (int) $examId, 'academic_year_id' => (int) $yearId,
            'standard_id' => (int) ($standardId ?? 0), 'division_id' => (int) ($divisionId ?? 0),
            'exam_name' => $exam->exam_name ?? '',
            'standard_name' => $standard->standard_name ?? '',
            'division_name' => $division->division_name ?? '',
            'year_name' => $year->year_name ?? '',
            'percentage' => $progress->percentage ?? null, 'grade' => $progress->grade ?? null, 'result' => $progress->result ?? null,
            'total_max_marks' => $progress->total_max_marks ?? 0, 'total_obtained_marks' => $progress->total_obtained_marks ?? 0,
        ];

        $subjects = $this->buildReportCardSubjects((int) $realStudentId, (int) $examId, (int) $standardId, (int) $yearId, $manualGrades, $pen);
        if ($report->percentage === null) {
            [$pct, $grade, $result, $tMax, $tObt] = $this->computeOverall($subjects);
            $report->percentage = $pct; $report->grade = $grade; $report->result = $result;
            $report->total_max_marks = $tMax; $report->total_obtained_marks = $tObt;
        }
        return view('report-card.print', compact('report', 'subjects'));
    }

    private function getProgressCardData($studentId, $examId, $yearId)
    {
        $exam = DB::table('exam_masters')->where('id', $examId)->first();
        $year = DB::table('academic_years')->where('id', $yearId)->first();
        if (!$exam) abort(404, 'Exam not found.');
        if (!$year) abort(404, 'Academic year not found.');

        $savedCard = $this->getProgressCardRecord($studentId, $studentId, $examId, $yearId);
        if ($savedCard) {
            return $this->buildProgressCardDataFromSaved($savedCard, $exam, $year, $examId, $yearId);
        }

        $student = $this->resolveCanonicalStudent($studentId);
        if (!$student) abort(404, 'Student not found in ERP.');

        $realStudentId = $student->Studentid;
        $saralId = trim((string) ($student->saralid ?? ''));

        $erpYearId = $year->old_year_id ?? substr((string) ($year->year_name ?? ''), 0, 4);
        $substudent = $this->getErpSubstudent($realStudentId, $erpYearId);
        $studname = trim((string) ($student->studname ?? ''));
        $fathername = trim((string) ($student->fathername ?? ''));
        $cleanedFullName = $this->cleanStudentName($studname, $fathername);

        $progress = $this->getProgressCardRecord($realStudentId, $saralId, $examId, $yearId, $cleanedFullName);
        $pen = $this->resolvePreferredPen(
            $progress->pen ?? null,
            $saralId
        );
        if ($progress) {
            $progress->pen = $pen; // In-memory only; the save action persists it.
        }
        $standardId = $exam->standard_id ?? ($progress->standard_id ?? null);

        $sampleMark = DB::table('student_marks')
            ->where(function ($q) use ($realStudentId, $pen) {
                $q->where('student_id', $realStudentId);
                if (!empty($pen) && (string) $pen !== (string) $realStudentId) $q->orWhere('student_id', $pen);
            })
            ->where('exam_master_id', $examId)->where('academic_year_id', $yearId)
            ->orderByDesc('id')->first();

        if (!$sampleMark) {
            $sampleMark = DB::table('student_marks')
                ->where(function ($q) use ($realStudentId, $pen) {
                    $q->where('student_id', $realStudentId);
                    if (!empty($pen) && (string) $pen !== (string) $realStudentId) $q->orWhere('student_id', $pen);
                })
                ->where('academic_year_id', $yearId)->orderByDesc('id')->first();
        }

        $standardId = $standardId ?? ($sampleMark->standard_id ?? null);
        if (!$standardId) {
            try {
                $skillStandardId = DB::table('student_result_details')
                    ->where('student_id', $realStudentId)->where('academic_year_id', $yearId)
                    ->orderByDesc('id')->value('standard_id');
                $standardId = $skillStandardId ?: null;
            } catch (\Throwable $e) { }
        }

        $divisionId = $this->resolveLaravelDivisionId($realStudentId, $pen, $yearId, $examId, $standardId, $progress, $substudent, $erpYearId);

        if ($standardId && $divisionId && !$substudent) {
            $substudent = $this->getErpSubstudent($realStudentId, $erpYearId, $standardId, $divisionId);
            $divisionId = $this->resolveLaravelDivisionId($realStudentId, $pen, $yearId, $examId, $standardId, $progress, $substudent, $erpYearId);
        }

        $standard = $standardId ? DB::table('standards')->where('id', $standardId)->first() : null;
        $division = $divisionId ? DB::table('divisions')->where('id', $divisionId)->first() : null;

        // CREATE IN-MEMORY MODEL (DO NOT SAVE TO DB YET)
        if (!$progress) {
            $progress = new StudentProgressCard([
                'studentid' => (string) $realStudentId,
                'pen' => (string) $pen,
                'exam_master_id' => $examId,
                'academic_year_id' => $yearId,
                'standard_id' => $standardId,
                'division_id' => $divisionId,
                'full_student_name' => $cleanedFullName,
                'father_name' => $fathername,
                'mother_name' => $student->mothername ?? '',
                'gender' => $student->gender ?? '',
                'date_of_birth' => $student->birthdate ?? null,
                'gr_no' => $substudent->regno ?? '',
                'roll_no' => $substudent->rollno ?? '',
                'aadhar_no' => $student->adharno ?? '',
                'mother_tongue' => $student->mtounge ?? 'MARATHI',
                'phone' => $student->fathermobile ?? $student->resphone1 ?? '',
                'email' => $student->email ?? '',
                'address' => $this->resolveStudentAddress($student),
                'school_timing' => '8:15am to 3:00pm',
                'attendance_data' => $this->defaultAttendanceData(),
                'teacher_remark' => ['text' => ''],
                'descriptive_records' => [],
                'second_term_grades' => [],
                'class_test_data' => [],
                'school_days_data' => [],
                'academic_grades' => [],
                'skill_grades' => [],
            ]);
        } elseif (empty($progress->studentid)) {
            $progress->studentid = (string) $realStudentId;
            $progress->save(); // Safe to save if we are just migrating a legacy row's ID
        }

        $examName = strtoupper(trim((string) ($exam->exam_name ?? '')));
        $termLabel = 'Exam';
        if (str_contains($examName, 'TERM 1')) $termLabel = 'Term 1';
        elseif (str_contains($examName, 'TERM 2')) $termLabel = 'Term 2';
        elseif (str_contains($examName, 'UNIT TEST 1')) $termLabel = 'Unit Test 1';
        elseif (str_contains($examName, 'UNIT TEST 2')) $termLabel = 'Unit Test 2';
        elseif (str_contains($examName, 'UNIT TEST 3')) $termLabel = 'Unit Test 3';
        elseif (str_contains($examName, 'UNIT TEST 4')) $termLabel = 'Unit Test 4';
        elseif (str_contains($examName, 'ANNUAL')) $termLabel = 'Annual';

        $defaultAttendance = $this->defaultAttendanceData();
        $savedAttendance = $this->ensureArray($progress->attendance_data ?? []);
        foreach ($defaultAttendance as $month => $defaults) {
            if (isset($savedAttendance[$month]) && is_array($savedAttendance[$month])) {
                $defaultAttendance[$month] = array_merge($defaults, $savedAttendance[$month]);
            }
        }

        $progress->attendance_data     = $defaultAttendance;
        $progress->descriptive_records = $this->ensureArray($progress->descriptive_records ?? []);
        $progress->second_term_grades  = $this->ensureArray($progress->second_term_grades ?? []);
        $progress->class_test_data     = $this->ensureArray($progress->class_test_data ?? []);
        $progress->school_days_data    = $this->ensureArray($progress->school_days_data ?? []);
        $progress->academic_grades     = $this->ensureArray($progress->academic_grades ?? []);
        $progress->skill_grades        = $this->ensureArray($progress->skill_grades ?? []);

        $teacherRemark = $this->ensureArray($progress->teacher_remark ?? []);
        if (!array_key_exists('text', $teacherRemark)) {
            $teacherRemark['text'] = '';
        }
        $progress->teacher_remark = $teacherRemark;

        $manualGrades = $progress->second_term_grades;
        $cleanStudName = $cleanedFullName;
        if ($fathername !== '' && str_ends_with(strtoupper($cleanedFullName), strtoupper($fathername))) {
            $cleanStudName = trim(substr($cleanedFullName, 0, -strlen($fathername)));
        }

        $yearLabel = $year->year_name ?? '';
        $shakYear = '';
        if ($yearLabel) {
            $parts = explode('-', $yearLabel);
            $shakYear = count($parts) == 2
                ? 'Shak: ' . (intval($parts[0]) - 78) . '-' . (intval($parts[1]) - 78)
                : 'Shak: ' . (intval(substr($yearLabel, 0, 4)) - 78);
        }

        $displayPen = $pen;

        $report = (object) [
            'pen' => $displayPen, 'student_id' => (int) $realStudentId, 'gov_student_id' => $displayPen,
            'studname' => $progress->full_student_name ? explode(' ', $progress->full_student_name)[0] : $cleanStudName,
            'father_name' => $this->savedValue($progress->father_name, $fathername),
            'full_student_name' => $this->savedValue($progress->full_student_name, $cleanedFullName),
            'mother_name' => $this->savedValue($progress->mother_name, $student->mothername ?? ''),
            'gender' => $this->savedValue($progress->gender, $student->gender ?? ''),
            'date_of_birth' => $this->savedValue($progress->date_of_birth, $student->birthdate ?? ''),
            'mother_tongue' => $this->savedValue($progress->mother_tongue, $student->mtounge ?? 'MARATHI'),
            'address' => $this->savedValue($progress->address, $this->resolveStudentAddress($student)),
            'rollno' => $this->savedValue($progress->roll_no, $substudent->rollno ?? ''),
            'gr_no' => $this->savedValue($progress->gr_no, $substudent->regno ?? ''),
            'aadhar_no' => $this->savedValue($progress->aadhar_no, $student->adharno ?? ''),
            'blood_group' => $progress->blood_group ?? '',
            'phone' => $this->savedValue($progress->phone, $student->fathermobile ?? $student->resphone1 ?? ''),
            'email' => $this->savedValue($progress->email, $student->email ?? ''),
            'school_timing' => $this->savedValue($progress->school_timing, '8:15am to 3:00pm'),
            'attendance_data' => $defaultAttendance,
            'teacher_remark' => $progress->teacher_remark,
            'teacher_remark_text' => $progress->teacher_remark['text'] ?? '',
            'exam_master_id' => (int) $examId, 'academic_year_id' => (int) $yearId,
            'standard_id' => (int) ($standardId ?? 0), 'division_id' => (int) ($divisionId ?? 0),
            'exam_name' => $exam->exam_name ?? '',
            'standard_name' => $standard->standard_name ?? '',
            'division_name' => $division->division_name ?? '',
            'year_name' => $yearLabel, 'shak_year' => $shakYear, 'rank' => null,
            'percentage' => $progress->percentage ?? null, 'grade' => $progress->grade ?? null, 'result' => $progress->result ?? null,
            'total_max_marks' => $progress->total_max_marks ?? 0, 'total_obtained_marks' => $progress->total_obtained_marks ?? 0,
        ];

        $standardName = $standard->standard_name ?? '';
        $isPrePrimary = $this->isPrePrimaryStandard($standardName);
        $isHighSchool = $this->isHighSchoolStandard($standardName);
        $skillObservations = collect();

        if ($isPrePrimary || $isHighSchool) {
            $skillObservations = DB::table('student_skill_observations as sso')
                ->leftJoin('subjects as s', 's.id', '=', 'sso.subject_id')
                ->where(function ($q) use ($realStudentId, $pen) {
                    $q->where('sso.student_id', $realStudentId);
                    if (!empty($pen) && (string) $pen !== (string) $realStudentId) $q->orWhere('sso.student_id', $pen);
                })
                ->where('sso.exam_master_id', $examId)->where('sso.academic_year_id', $yearId)
                ->select('sso.*', 's.subject_name')->get();
        }

        $subjects = $this->buildProgressCardSubjects(
            $realStudentId, $examId, $standardId, $yearId,
            $manualGrades, $report->standard_name, $report->exam_name,
            $skillObservations, $pen
        );

        if ($report->percentage === null) {
            [$pct, $grade, $result, $tMax, $tObt] = $this->computeOverall($subjects);
            $report->percentage = $pct; $report->grade = $grade; $report->result = $result;
            $report->total_max_marks = $tMax; $report->total_obtained_marks = $tObt;
        }

        return compact('report', 'subjects', 'progress', 'termLabel', 'skillObservations') + [
            'academicAverageGrade' => $this->calculateAcademicAverageGrade($subjects),
            'specialSkillAverageGrade' => $this->calculateSpecialSkillAverageGrade($subjects, $report->standard_name),
        ];
    }

    public function printProgressCard($studentId, $examId, $yearId)
    {
        set_time_limit(120);
        $data = $this->getProgressCardData($studentId, $examId, $yearId);
        $data['isEdit'] = false;
        return view('report-card.progress-card', $data);
    }

    public function editProgressDetails($studentId, $examId, $yearId)
    {
        set_time_limit(120);
        $data = $this->getProgressCardData($studentId, $examId, $yearId);
        $data['isEdit'] = true;
        return view('report-card.progress-card', $data);
    }

    
public function saveProgressDetails(Request $request, $studentId, $examId, $yearId)
{
    /*
    |--------------------------------------------------------------------------
    | 1. VALIDATION
    |--------------------------------------------------------------------------
    */

    $request->validate(
        [
            // The current Blade form posts gov_student_id. Keep pen as a
            // backwards-compatible alias for older forms.
            'gov_student_id' => ['nullable', 'string', 'max:50', 'regex:/^(?:\d{11}|\d{19})$/D'],
            'pen' => ['nullable', 'string', 'max:50', 'regex:/^(?:\d{11}|\d{19})$/D'],

            'attendance' => ['sometimes', 'array'],
            'attendance.*.total_days' => ['nullable', 'integer', 'min:0', 'max:366'],
            'attendance.*.working' => ['nullable', 'integer', 'min:0', 'max:366'],
            'attendance.*.present' => ['nullable', 'integer', 'min:0', 'max:366'],
        ],
        [
            'gov_student_id.regex' => 'PEN must contain exactly 11 digits. A 19-digit value is accepted only for the existing SARAL ID format.',
            'pen.regex' => 'PEN must contain exactly 11 digits. A 19-digit value is accepted only for the existing SARAL ID format.',
            'gov_student_id.max' => 'PEN must not exceed 50 characters.',
            'pen.max' => 'PEN must not exceed 50 characters.',

            'attendance.*.total_days.integer' => 'Total Days must contain a whole number.',
            'attendance.*.total_days.min' => 'Total Days cannot be negative.',
            'attendance.*.total_days.max' => 'Total Days cannot exceed 366.',

            'attendance.*.working.integer' => 'Working Days must contain a whole number.',
            'attendance.*.working.min' => 'Working Days cannot be negative.',
            'attendance.*.working.max' => 'Working Days cannot exceed 366.',

            'attendance.*.present.integer' => 'Days Present must contain a whole number.',
            'attendance.*.present.min' => 'Days Present cannot be negative.',
            'attendance.*.present.max' => 'Days Present cannot exceed 366.',
        ]
    );

    /*
    |--------------------------------------------------------------------------
    | 2. RESOLVE PERMANENT ERP STUDENT ID
    |--------------------------------------------------------------------------
    */

    $student = $this->resolveCanonicalStudent($studentId);

    if (!$student) {
        Log::warning('saveProgressDetails: student not found.', [
            'route_student_id' => $studentId,
        ]);

        return redirect()->back()
            ->withInput()
            ->with('error', 'Student not found in ERP.');
    }

    $realStudentId = (string) $student->Studentid;
    $saralid       = !empty($student->saralid)
        ? (string) $student->saralid
        : null;

    $examId = (int) $examId;
    $yearId = (int) $yearId;

    $studname   = trim((string) ($student->studname ?? ''));
    $fathername = trim((string) ($student->fathername ?? ''));

    $fullName = $this->cleanStudentName($studname, $fathername);

    /*
    |--------------------------------------------------------------------------
    | 3. FIND EXISTING PROGRESS CARD
    |--------------------------------------------------------------------------
    */

    $existing = $this->getProgressCardRecord(
        $realStudentId,
        $saralid,
        $examId,
        $yearId,
        $fullName
    );

    Log::info('saveProgressDetails: started.', [
        'studentid' => $realStudentId,
        'exam_id' => $examId,
        'year_id' => $yearId,
        'existing_id' => $existing->id ?? null,
        'existing_pen_length' => strlen(trim((string) ($existing->pen ?? ''))),
    ]);

    /*
    |--------------------------------------------------------------------------
    | 4. PRESERVE EXISTING RESULT INFORMATION
    |--------------------------------------------------------------------------
    */

    $standardId = $request->input(
        'standard_id',
        $existing->standard_id ?? null
    );

    $divisionId = $request->input(
        'division_id',
        $existing->division_id ?? null
    );

    $pct = $request->input(
        'hidden_percentage',
        $existing->percentage ?? null
    );

    $grade = $request->input(
        'hidden_grade',
        $existing->grade ?? null
    );

    $result = $request->input(
        'hidden_result',
        $existing->result ?? null
    );

    $tMax = $request->input(
        'hidden_total_max',
        $existing->total_max_marks ?? 0
    );

    $tObt = $request->input(
        'hidden_total_obtained',
        $existing->total_obtained_marks ?? 0
    );

    /*
    |--------------------------------------------------------------------------
    | 5. ATTENDANCE DATA
    |--------------------------------------------------------------------------
    | All twelve months are preserved.
    | Saved values take priority over defaults.
    |--------------------------------------------------------------------------
    */

    $months = [
        'Jyeshtha',
        'Ashadh',
        'Shravan',
        'Bhadrapad',
        'Ashwin',
        'Kartik',
        'Margshirsh',
        'Paush',
        'Magh',
        'Phalgun',
        'Chaitra',
        'Vaishakh',
    ];

    $defaultTotalDays = [
        'Jyeshtha'   => 31,
        'Ashadh'     => 31,
        'Shravan'    => 31,
        'Bhadrapad'  => 31,
        'Ashwin'     => 30,
        'Kartik'     => 30,
        'Margshirsh' => 30,
        'Paush'      => 30,
        'Magh'       => 30,
        'Phalgun'    => 30,
        'Chaitra'    => 30,
        'Vaishakh'   => 31,
    ];

    $defaultWorkingDays = [
        'Jyeshtha'  => 6,
        'Ashadh'    => 23,
        'Shravan'   => 26,
        'Bhadrapad' => 22,
        'Ashwin'    => 22,
        'Kartik'    => 10,
    ];

    $oldAttendance = $this->ensureArray(
        $existing->attendance_data ?? []
    );

    $attendance = [];

    foreach ($months as $month) {

        $oldMonth = $oldAttendance[$month] ?? [];

        if (!is_array($oldMonth)) {
            $oldMonth = [];
        }

        /*
        |--------------------------------------------------------------------------
        | TOTAL DAYS
        |--------------------------------------------------------------------------
        */

        $oldTotalDays = trim(
            (string) ($oldMonth['total_days'] ?? '')
        );

        $totalDaysFallback = $oldTotalDays !== ''
            ? $oldTotalDays
            : (string) ($defaultTotalDays[$month] ?? '');

        $totalDays = trim((string) $request->input(
            "attendance.{$month}.total_days",
            $totalDaysFallback
        ));

        if ($totalDays === '') {
            $totalDays = $totalDaysFallback;
        }

        /*
        |--------------------------------------------------------------------------
        | WORKING DAYS
        |--------------------------------------------------------------------------
        | Saved database value has priority over hardcoded defaults.
        */

        $oldWorking = trim(
            (string) ($oldMonth['working'] ?? '')
        );

        $workingFallback = $oldWorking !== ''
            ? $oldWorking
            : (string) ($defaultWorkingDays[$month] ?? '');

        $working = trim((string) $request->input(
            "attendance.{$month}.working",
            $workingFallback
        ));

        /*
        |--------------------------------------------------------------------------
        | DAYS PRESENT
        |--------------------------------------------------------------------------
        */

        $oldPresent = trim(
            (string) ($oldMonth['present'] ?? '')
        );

        $present = trim((string) $request->input(
            "attendance.{$month}.present",
            $oldPresent
        ));

        /*
        |--------------------------------------------------------------------------
        | VALIDATE ATTENDANCE VALUES
        |--------------------------------------------------------------------------
        */

        $totalDaysNum = (int) $totalDays;
        $workingNum   = (int) $working;
        $presentNum   = (int) $present;

        if (
            $totalDays !== '' &&
            $working !== '' &&
            $workingNum > $totalDaysNum
        ) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    "Working Days cannot exceed Total Days for {$month}."
                );
        }

        if (
            $working !== '' &&
            $present !== '' &&
            $presentNum > $workingNum
        ) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    "Days Present cannot exceed Working Days for {$month}."
                );
        }

        /*
        |--------------------------------------------------------------------------
        | ATTENDANCE PERCENTAGE
        |--------------------------------------------------------------------------
        */

        $percentage = null;

        if ($workingNum > 0 && $present !== '') {
            $percentage = round(
                ($presentNum / $workingNum) * 100,
                1
            );
        }

        /*
        |--------------------------------------------------------------------------
        | ABSENT DAYS
        |--------------------------------------------------------------------------
        */

        $absent = '';

        if ($working !== '' && $present !== '') {
            $absent = (string) max(
                0,
                $workingNum - $presentNum
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FINAL MONTH RECORD
        |--------------------------------------------------------------------------
        */

        $attendance[$month] = [
            'total_days' => $totalDays,
            'working'    => $working,
            'present'    => $present,
            'percentage' => $percentage,
            'absent'     => $absent,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | 6. DESCRIPTIVE RECORDS
    |--------------------------------------------------------------------------
    */

    $descriptive = $this->ensureArray(
        $existing->descriptive_records ?? []
    );

    if ($request->has('descriptive')) {

        $descriptive = [];

        foreach ((array) $request->input('descriptive', []) as $sid => $fields) {

            $fields = is_array($fields) ? $fields : [];

            $descriptive[$sid] = [
                'progress' => trim((string) ($fields['progress'] ?? '')),
                'interest' => trim((string) ($fields['interest'] ?? '')),
                'improvements' => trim((string) ($fields['improvements'] ?? '')),
            ];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 7. SUBJECT GRADES
    |--------------------------------------------------------------------------
    */

    $manualGrades = $this->ensureArray(
        $existing->second_term_grades ?? []
    );

    $academicGrades = $this->ensureArray(
        $existing->academic_grades ?? []
    );

    $skillGrades = $this->ensureArray(
        $existing->skill_grades ?? []
    );

    if ($request->has('graded_subjects')) {

        foreach ((array) $request->input('graded_subjects', []) as $key => $grd) {

            $grd = trim((string) $grd);

            if ($grd === '') {
                unset(
                    $manualGrades[$key],
                    $academicGrades[$key],
                    $skillGrades[$key]
                );

                continue;
            }

            $manualGrades[$key] = $grd;

            if (
                is_numeric($key) ||
                str_contains(strtoupper((string) $key), 'DEFENCE') ||
                str_contains(strtoupper((string) $key), 'WATER')
            ) {
                $academicGrades[$key] = $grd;
            } else {
                $skillGrades[$key] = $grd;
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 8. TEACHER REMARK
    |--------------------------------------------------------------------------
    */

    $oldTeacherRemark = $this->ensureArray(
        $existing->teacher_remark ?? []
    );

    $teacherRemark = [
        'text' => $request->input(
            'teacher_remark',
            $oldTeacherRemark['text'] ?? ''
        ),
    ];

    /*
    |--------------------------------------------------------------------------
    | 9. CLASS TEST DATA
    |--------------------------------------------------------------------------
    */

    $oldClassTestData = $this->ensureArray(
        $existing->class_test_data ?? []
    );

    $classTestData = [
        'attendance' => $request->input(
            'class_test.attendance',
            $oldClassTestData['attendance'] ?? null
        ),

        'result' => $request->input(
            'class_test.result',
            $oldClassTestData['result'] ?? null
        ),

        'remark' => $request->input(
            'class_test.remark',
            $oldClassTestData['remark'] ?? null
        ),
    ];

    /*
    |--------------------------------------------------------------------------
    | 10. SCHOOL DAYS DATA
    |--------------------------------------------------------------------------
    */

    $oldSchoolDaysData = $this->ensureArray(
        $existing->school_days_data ?? []
    );

    $schoolDaysAttendance = [];

    $schoolDayKeys = [
        '15_aug',
        'ganesh_sthapana',
        'ganesh_visarjan',
        'date_15_aug',
        'date_ganesh_sthapana',
        'date_ganesh_visarjan',
    ];

    foreach ($schoolDayKeys as $key) {

        $schoolDaysAttendance[$key] = $request->input(
            "school_days.{$key}",
            $oldSchoolDaysData[$key] ?? null
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 11. DETERMINE PEN
    |--------------------------------------------------------------------------
    */

    // Current edit form uses gov_student_id. Accept pen as an alias so
    // older versions of the form continue to work.
    $submittedPen = trim((string) (
        $request->input('gov_student_id') ?: $request->input('pen', '')
    ));

    $existingPen = $existing
        ? trim((string) ($existing->pen ?? ''))
        : '';

    // An explicit valid 11-digit value typed by the user must be saved.
    // Otherwise preserve a valid 11-digit MySQL PEN; if none exists, use
    // MSSQL saralid (11 or 19 digits). StudentID is never used as a PEN.
    if ($this->isElevenDigitPen($submittedPen)) {
        $finalPen = $submittedPen;
        $penSource = 'user_input';
    } else {
        $finalPen = $this->resolvePreferredPen($existingPen, $saralid);
        $penSource = $this->isElevenDigitPen($existingPen)
            ? 'mysql'
            : ($finalPen !== '' ? 'mssql_saralid' : 'empty');
    }

    /*
    |--------------------------------------------------------------------------
    | 12. JSON ENCODER
    |--------------------------------------------------------------------------
    | IMPORTANT:
    | Query Builder does not automatically apply Eloquent array casts.
    | Therefore, every JSON-backed field must be encoded before saving.
    |--------------------------------------------------------------------------
    */

    $encodeJson = static function ($value): string {

        $json = json_encode(
            $value,
            JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
        );

        if ($json === false) {
            throw new \RuntimeException(
                'JSON encoding failed: ' . json_last_error_msg()
            );
        }

        return $json;
    };

    /*
    |--------------------------------------------------------------------------
    | 13. PREPARE DATABASE RECORD
    |--------------------------------------------------------------------------
    */

    $updateData = [

        'studentid' => $realStudentId,
        'pen' => $finalPen,

        'exam_master_id' => $examId,
        'academic_year_id' => $yearId,

        'full_student_name' => $request->input(
            'student_name',
            $existing->full_student_name ?? $fullName
        ),

        'father_name' => $request->input(
            'father_name',
            $existing->father_name ?? $fathername
        ),

        'mother_name' => $request->input(
            'mother_name',
            $existing->mother_name ?? ($student->mothername ?? '')
        ),

        'gender' => $request->input(
            'gender',
            $existing->gender ?? ($student->gender ?? '')
        ),

        'date_of_birth' => $request->input(
            'date_of_birth',
            $existing->date_of_birth ?? ($student->birthdate ?? null)
        ),

        'gr_no' => $request->input(
            'gr_no',
            $existing->gr_no ?? ''
        ),

        'roll_no' => $request->input(
            'rollno',
            $existing->roll_no ?? ''
        ),

        'aadhar_no' => $request->input(
            'aadhar_no',
            $existing->aadhar_no ?? ($student->adharno ?? '')
        ),

        'standard_id' => $standardId,
        'division_id' => $divisionId,

        'phone' => $request->input(
            'phone',
            $existing->phone ?? ($student->fathermobile ?? $student->resphone1 ?? '')
        ),

        'email' => $request->input(
            'email',
            $existing->email ?? ($student->email ?? '')
        ),

        'school_timing' => $request->input(
            'school_timing',
            $existing->school_timing ?? '8:15am to 3:00pm'
        ),

        'blood_group' => $request->input(
            'blood_group',
            $existing->blood_group ?? ''
        ),

        'mother_tongue' => $request->input(
            'mother_tongue',
            $existing->mother_tongue ?? ($student->mtounge ?? 'MARATHI')
        ),

        'address' => $request->input(
            'address',
            $existing->address ?? $this->resolveStudentAddress($student)
        ),

        'term1_weight' => $request->input(
            'term1_weight',
            $existing->term1_weight ?? null
        ),

        'term1_height' => $request->input(
            'term1_height',
            $existing->term1_height ?? null
        ),

        'term2_weight' => $request->input(
            'term2_weight',
            $existing->term2_weight ?? null
        ),

        'term2_height' => $request->input(
            'term2_height',
            $existing->term2_height ?? null
        ),

        /*
        |--------------------------------------------------------------------------
        | JSON COLUMNS - ALL ENCODED
        |--------------------------------------------------------------------------
        */

        'attendance_data' => $encodeJson($attendance),

        'descriptive_records' => $encodeJson($descriptive),

        'second_term_grades' => $encodeJson($manualGrades),

        'class_test_data' => $encodeJson($classTestData),

        'school_days_data' => $encodeJson($schoolDaysAttendance),

        'teacher_remark' => $encodeJson($teacherRemark),

        'academic_grades' => $encodeJson($academicGrades),

        'skill_grades' => $encodeJson($skillGrades),

        /*
        |--------------------------------------------------------------------------
        | OTHER PROGRESS CARD FIELDS
        |--------------------------------------------------------------------------
        */

        'passed_promoted_to' => $request->input(
            'passed_promoted_to',
            $existing->passed_promoted_to ?? null
        ),

        'school_reopens_on' => $request->input(
            'school_reopens_on',
            $existing->school_reopens_on ?? null
        ),

        'percentage' => $pct,
        'grade' => $grade,
        'result' => $result,

        'total_max_marks' => $tMax,
        'total_obtained_marks' => $tObt,

        'updated_by' => Auth::id(),
        'updated_at' => now(),
    ];

    /*
    |--------------------------------------------------------------------------
    | 14. INSERT OR UPDATE
    |--------------------------------------------------------------------------
    */

    try {

        $savedId = DB::transaction(function () use (
            $existing,
            $updateData,
            $realStudentId,
            $examId,
            $yearId,
            $finalPen
        ) {

            if ($existing) {

                /*
                | Update existing record.
                | Use DB table directly because JSON has already been encoded.
                */

                DB::table('student_progress_cards')
                    ->where('id', $existing->id)
                    ->update($updateData);

                $savedId = (int) $existing->id;

            } else {

                /*
                | Insert new record.
                */

                $insertData = array_merge(
                    $updateData,
                    [
                        'created_by' => Auth::id(),
                        'created_at' => now(),
                    ]
                );

                $savedId = DB::table('student_progress_cards')
                    ->insertGetId($insertData);
            }

            /*
            |--------------------------------------------------------------------------
            | REMOVE DUPLICATE RECORDS FOR THIS STUDENT / EXAM / YEAR
            |--------------------------------------------------------------------------
            */

            DB::table('student_progress_cards')
                ->where('studentid', $realStudentId)
                ->where('exam_master_id', $examId)
                ->where('academic_year_id', $yearId)
                ->where('id', '<>', $savedId)
                ->delete();

            /*
            |--------------------------------------------------------------------------
            | VERIFY THE RECORD EXISTS
            |--------------------------------------------------------------------------
            */

            $recordExists = DB::table('student_progress_cards')
                ->where('id', $savedId)
                ->exists();

            if (!$recordExists) {
                throw new \RuntimeException(
                    'Progress card record was not found after saving.'
                );
            }

            $savedPen = trim((string) DB::table('student_progress_cards')
                ->where('id', $savedId)
                ->value('pen'));

            if ($savedPen !== trim((string) $finalPen)) {
                throw new \RuntimeException(
                    'The progress card row was saved, but the PEN value did not persist correctly.'
                );
            }

            return $savedId;
        });

        Log::info('saveProgressDetails: saved successfully.', [
            'saved_id' => $savedId,
            'studentid' => $realStudentId,
            'exam_id' => $examId,
            'year_id' => $yearId,
            'pen_source' => $penSource,
            'pen_length' => strlen($finalPen),
        ]);

    } catch (\Throwable $e) {

        Log::error('saveProgressDetails FAILED.', [
            'studentid' => $realStudentId,
            'exam_id' => $examId,
            'year_id' => $yearId,
            'existing_id' => $existing->id ?? null,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        return redirect()->back()
            ->withInput()
            ->with(
                'error',
                'Progress card could not be saved. Please check the Laravel log for the exact database error.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | 15. SUCCESS REDIRECT
    |--------------------------------------------------------------------------
    */

    return redirect()
        ->route(
            'report-card.progress-card',
            [$realStudentId, $examId, $yearId]
        )
        ->with('success', 'Progress card details saved successfully.');
}


    private function getErpSubstudent($studentId, $erpYearId = null, $standardId = null, $divisionId = null)
    {
        if ($erpYearId === null || trim((string) $erpYearId) === '') return null;
        if ($standardId > 0 && $divisionId > 0) {
            try {
                return StudentHelper::getStudentsDirectERP($erpYearId, (int) $standardId, (int) $divisionId)
                    ->first(fn ($row) => (string) ($row->Studentid ?? '') === (string) $studentId);
            } catch (\Throwable $e) { return null; }
        }
        try {
            $rows = DB::connection('sqlsrv_olderp')->table('SubStudentMst')
                ->where('Studentid', $studentId)->where('yearid', $erpYearId)
                ->orderBy('standardid')->orderBy('divisionid')->orderBy('rollno')->get();
            return $rows->isEmpty() ? null : $rows->first();
        } catch (\Throwable $e) { return null; }
    }

    private function resolveStudentAddress($student): string
    {
        if (!$student) return '';
        $parts = array_filter([
            trim((string) ($student->peraddr ?? '')),
            trim((string) ($student->locaddr ?? '')),
            trim((string) ($student->legacy_address ?? '')),
            trim((string) ($student->location ?? '')),
            trim((string) ($student->taluka ?? '')),
            trim((string) ($student->legacy_taluka ?? '')),
            trim((string) ($student->district ?? '')),
            trim((string) ($student->legacy_district ?? '')),
        ], fn ($value) => $value !== '');
        return implode(', ', array_unique($parts));
    }

    private function getStudentMarksDivisionId($studentId, $pen, $academicYearId, $examId = null, $standardId = null): ?int
    {
        $baseQuery = function () use ($studentId, $pen, $academicYearId, $standardId) {
            $query = DB::table('student_marks')
                ->where(function ($q) use ($studentId, $pen) {
                    $q->where('student_id', $studentId);
                    if (!empty($pen) && (string) $pen !== (string) $studentId) $q->orWhere('student_id', $pen);
                })
                ->where('academic_year_id', $academicYearId)
                ->whereNotNull('division_id')->where('division_id', '>', 0);
            if (!empty($standardId)) $query->where('standard_id', $standardId);
            return $query;
        };

        $candidates = [];
        if (!empty($examId)) $candidates[] = $baseQuery()->where('exam_master_id', $examId)->orderByDesc('id')->value('division_id');
        $candidates[] = $baseQuery()->orderByDesc('id')->value('division_id');

        foreach ($candidates as $candidate) {
            $candidate = (int) $candidate;
            if ($candidate > 0 && DB::table('divisions')->where('id', $candidate)->exists()) return $candidate;
        }
        return null;
    }

    private function getErpDivisionName($substudent, $erpYearId): ?string
    {
        if (!$substudent) return null;
        $erpDivisionId = $substudent->divisionid ?? null;
        if ($erpDivisionId !== null && $erpDivisionId !== '') {
            try {
                $query = DB::connection('sqlsrv_olderp')->table('DivisionMst')->where('divisionid', $erpDivisionId);
                $lookupYearId = $substudent->yearid ?? $erpYearId;
                if ($lookupYearId !== null && trim((string) $lookupYearId) !== '') $query->where('yearid', $lookupYearId);
                if (!empty($substudent->standardid)) $query->where('standardid', $substudent->standardid);
                $erpDivision = $query->first();
                $divisionName = trim((string) ($erpDivision->division ?? ''));
                if ($divisionName !== '') return $divisionName;
            } catch (\Throwable $e) { }
        }
        foreach (['div', 'division'] as $field) {
            $name = trim((string) ($substudent->{$field} ?? ''));
            if ($name !== '') return $name;
        }
        return null;
    }

    private function getLaravelDivisionIdByName($divisionName): ?int
    {
        $normalizedName = mb_strtoupper(trim(preg_replace('/\s+/', ' ', (string) $divisionName)));
        if ($normalizedName === '') return null;
        $id = DB::table('divisions')->whereRaw('UPPER(TRIM(division_name)) = ?', [$normalizedName])->value('id');
        return $id ? (int) $id : null;
    }

    private function resolveLaravelDivisionId($studentId, $pen, $academicYearId, $examId, $standardId, $progress, $substudent, $erpYearId): ?int
    {
        $marksDivisionId = $this->getStudentMarksDivisionId($studentId, $pen, $academicYearId, $examId, $standardId);
        if ($marksDivisionId) return $marksDivisionId;

        $erpDivisionName = $this->getErpDivisionName($substudent, $erpYearId);
        $normalizedErpName = mb_strtoupper(trim(preg_replace('/\s+/', ' ', (string) $erpDivisionName)));

        $progressDivisionId = (int) ($progress->division_id ?? 0);
        if ($progressDivisionId > 0) {
            $progressDivision = DB::table('divisions')->where('id', $progressDivisionId)->first();
            if ($progressDivision) {
                $savedName = mb_strtoupper(trim(preg_replace('/\s+/', ' ', (string) $progressDivision->division_name)));
                if ($normalizedErpName === '' || $savedName === $normalizedErpName) return $progressDivisionId;
            }
        }

        try {
            $skillDivisionQuery = DB::table('student_result_details')
                ->where('student_id', $studentId)->where('academic_year_id', $academicYearId)
                ->when($standardId, fn ($q) => $q->where('standard_id', $standardId))
                ->whereNotNull('division_id');
            $skillDivisionId = (int) $skillDivisionQuery->where('division_id', '>', 0)->orderByDesc('id')->value('division_id');
            if ($skillDivisionId > 0 && DB::table('divisions')->where('id', $skillDivisionId)->exists()) return $skillDivisionId;
        } catch (\Throwable $e) { }

        return $this->getLaravelDivisionIdByName($erpDivisionName);
    }

    private function getSubjectMarksMap($studentId, $examId, $standardId, $swsMap = null, $pen = null): \Illuminate\Support\Collection
    {
        $examInfo = DB::table('exam_masters')->where('id', $examId)->first();
        $examIds = [$examId];
        if ($examInfo && $examInfo->exam_name) {
            $relatedIds = DB::table('exam_masters')
                ->where('exam_name', $examInfo->exam_name)->where('standard_id', $standardId)
                ->when($examInfo->academic_year_id, fn ($q) => $q->where('academic_year_id', $examInfo->academic_year_id))
                ->pluck('id')->toArray();
            $examIds = array_unique(array_merge($examIds, $relatedIds));
        }

        $rawMarks = DB::table('student_marks')
            ->where(function ($q) use ($studentId, $pen) {
                $q->where('student_id', $studentId);
                if (!empty($pen) && (string) $pen !== (string) $studentId) $q->orWhere('student_id', $pen);
            })
            ->whereIn('exam_master_id', $examIds)->get();

        if ($rawMarks->isEmpty()) return collect();

        $tsaIds = $rawMarks->pluck('teacher_subject_allocation_id')->filter()->unique()->toArray();
        $tsaMap = collect();
        if (!empty($tsaIds)) {
            $tsaMap = DB::table('teacher_subject_allocations')->whereIn('id', $tsaIds)->pluck('subject_id', 'id');
        }

        if (!$swsMap) $swsMap = DB::table('standard_wise_subjects')->where('standard_id', $standardId)->get();

        $firstSws = $swsMap->first();
        $swsIdColumn = $firstSws && isset($firstSws->standard_wise_subject_id) ? 'standard_wise_subject_id' : 'id';
        $swsIdToSubjectId = $swsMap->pluck('subject_id', $swsIdColumn)->toArray();
        $subjectIdToSwsName = $swsMap->pluck('subject_name', 'subject_id')->toArray();

        $allRequiredSubjectIds = array_unique(array_merge(
            $rawMarks->pluck('subject_id')->toArray(),
            array_values($swsIdToSubjectId),
            array_values($tsaMap->toArray())
        ));
        $dbSubjects = DB::table('subjects')->whereIn('id', $allRequiredSubjectIds)->pluck('subject_name', 'id')->toArray();
        $result = collect();

        foreach ($rawMarks as $mark) {
            $storedId = (int) $mark->subject_id;
            $tsaId = $mark->teacher_subject_allocation_id;
            $actualId = $storedId;
            if (!empty($tsaId) && $tsaMap->has($tsaId)) $actualId = (int) $tsaMap->get($tsaId);
            elseif (isset($swsIdToSubjectId[$storedId])) $actualId = (int) $swsIdToSubjectId[$storedId];

            $finalName = $dbSubjects[$actualId] ?? $dbSubjects[$storedId] ?? null;
            if (isset($subjectIdToSwsName[$actualId])) $finalName = $subjectIdToSwsName[$actualId] ?? $finalName;
            elseif (isset($subjectIdToSwsName[$storedId])) $finalName = $subjectIdToSwsName[$storedId] ?? $finalName;

            $result->put($actualId, $mark);
            if ($finalName) $result->put('NAME_' . strtoupper(trim($finalName)), $mark);
        }
        return $result;
    }

    private function mapSubjectToSkill($subjectName): array
    {
        $name = strtoupper(trim((string) $subjectName));
        if (str_contains($name, 'OTHERS') || str_contains($name, 'LIBRARY')) return ['slot' => 'S4'];
        if (str_contains($name, 'WORK EXPERIENCE') || str_contains($name, 'WORK EXP')) return ['slot' => 'S2'];
        if (str_contains($name, 'PT & HEALTH') || str_contains($name, 'PHYSICAL EDUCATION')) return ['slot' => 'S3'];
        if (str_contains($name, 'ART') || str_contains($name, 'DRAWING')) return ['slot' => 'S1'];
        return ['slot' => null];
    }

    private function subjectNameToKey($subjectName): string
    {
        return str_replace([' ', '/', '-', '.'], '_', strtoupper(trim((string) $subjectName)));
    }

    private function markHasAnyObtained($mark): bool
    {
        if (!$mark) return false;
        foreach (['theory_obtained_marks', 'oral_obtained_marks', 'practical_obtained_marks'] as $field) {
            if (isset($mark->{$field}) && $mark->{$field} !== null && $mark->{$field} !== '') return true;
        }
        return false;
    }

    private function buildReportCardSubjects($studentId, $examId, $standardId, $academicYearId, array $manualGrades = [], $pen = null): \Illuminate\Support\Collection
    {
        $standardName = DB::table('standards')->where('id', $standardId)->value('standard_name') ?? '';
        $stdNameUpper = strtoupper(trim($standardName));
        $is11or12 = preg_match('/\b(11|12)\b/', $stdNameUpper) || str_contains($stdNameUpper, 'ELEVENTH') || str_contains($stdNameUpper, 'TWELFTH') || str_contains($stdNameUpper, 'XI') || str_contains($stdNameUpper, 'XII');
        $is10orHigher = preg_match('/\b(10|11|12)\b/', $stdNameUpper) || str_contains($stdNameUpper, 'TENTH') || str_contains($stdNameUpper, 'ELEVENTH') || str_contains($stdNameUpper, 'TWELFTH') || in_array($stdNameUpper, ['X', 'XI', 'XII']);

        $swsMap = $standardId ? DB::table('standard_wise_subjects')->where('standard_id', $standardId)->get()->keyBy('subject_id') : collect();
        $examSubjects = DB::table('exam_master_subjects as ems')
            ->join('subjects as s', 's.id', '=', 'ems.subject_id')
            ->leftJoin('standard_wise_subjects as sws', function ($join) use ($standardId) {
                $join->on('sws.subject_id', '=', 's.id')->where('sws.standard_id', '=', $standardId);
            })
            ->where('ems.exam_master_id', $examId)->when($standardId, fn ($q) => $q->where('ems.standard_id', $standardId))
            ->select([
                's.id as subject_id', 's.subject_code', 'ems.max_marks', 'ems.passing_marks',
                DB::raw('COALESCE(NULLIF(sws.sort_order, 0), ems.display_order, s.id) as display_order'),
                DB::raw('0 as is_graded'),
                's.subject_name as master_name', 'sws.subject_name as sws_name',
            ])->get();

        $examSubjects = $examSubjects->map(function ($ex) {
            $ex->subject_name = !empty($ex->sws_name) ? $ex->sws_name : $ex->master_name;
            unset($ex->master_name, $ex->sws_name);
            return $ex;
        })->unique('subject_id')->values();

        if ($examSubjects->isEmpty() && $standardId) {
            $examSubjects = DB::table('standard_wise_subjects as sws')
                ->join('subjects as s', 's.id', '=', 'sws.subject_id')
                ->where('sws.standard_id', $standardId)->where('sws.is_active', 1)->where('s.is_active', 1)
                ->orderBy('sws.sort_order')->orderBy('s.id')
                ->select([
                    's.id as subject_id', 's.subject_code',
                    DB::raw('NULL as max_marks'), DB::raw('NULL as passing_marks'),
                    DB::raw('sws.sort_order as display_order'), DB::raw('0 as is_graded'),
                    's.subject_name as master_name', 'sws.subject_name as sws_name',
                ])->get();
            $examSubjects = $examSubjects->map(function ($ex) {
                $ex->subject_name = !empty($ex->sws_name) ? $ex->sws_name : $ex->master_name;
                unset($ex->master_name, $ex->sws_name);
                return $ex;
            })->unique('subject_id')->values();
        }

        if ($is11or12) {
            $examSubjects = $examSubjects->reject(fn ($s) => strtoupper(trim($s->subject_name)) === 'COMPUTER SCIENCE')->values();
        }

        $skillNamesToRemove = ['MUSIC', 'DRAWING', 'SANSKRIT BHARTI', 'DANCE', 'ACTING', 'TABLA', 'ABACUS', 'COMPUTER', 'ROBOTICS', 'CRAFT', 'PHYSICAL EDUCATION', 'DEFENCE STUDIES', 'WATER SECURITY', 'T/S/A/D', 'PT', 'ART', 'WE'];
        if ($this->isHighSchoolStandard($standardName) || $is10orHigher) {
            $examSubjects = $examSubjects->reject(function ($s) use ($skillNamesToRemove) {
                $name = strtoupper(trim($s->subject_name));
                foreach ($skillNamesToRemove as $skill) {
                    if ($name === $skill || str_contains($name, $skill)) return true;
                }
                return false;
            })->values();
        }

        $examSubjects = $this->ensureAdditionalSubjects($examSubjects, $standardId, $standardName);
        
        // Sort to put MARATHI first, then ENGLISH, then others based on display order
        $examSubjects = $examSubjects->sort(function ($a, $b) {
            $aName = strtoupper(trim((string) $a->subject_name));
            $bName = strtoupper(trim((string) $b->subject_name));
            
            $getWeight = function($name) {
                if (str_contains($name, 'MARATHI')) return 1;
                if (str_contains($name, 'ENGLISH')) return 2;
                return 10;
            };
            
            $aWeight = $getWeight($aName);
            $bWeight = $getWeight($bName);
            
            if ($aWeight === $bWeight) {
                return ($a->display_order ?? 999) <=> ($b->display_order ?? 999);
            }
            return $aWeight <=> $bWeight;
        })->values();

        $marksBySubject = $this->getSubjectMarksMap($studentId, $examId, $standardId, $swsMap, $pen);

        $skillGrades = [];
        $skillMarks = collect();
        try {
            $skillMarks = DB::table('student_skill_marks as ssm')
                ->leftJoin('subjects as s', 's.id', '=', 'ssm.subject_id')
                ->where('ssm.exam_master_id', $examId)
                ->where(function ($q) use ($studentId, $pen) {
                    $q->where('ssm.student_id', $studentId);
                    if (!empty($pen) && (string) $pen !== (string) $studentId) $q->orWhere('ssm.student_id', $pen);
                })
                ->select('ssm.*', 's.subject_name as skill_subject_name')->get();
        } catch (\Exception $e) {}

        if ($skillMarks->isEmpty()) {
            try {
                $skillMarks = DB::table('student_result_details as srd')
                    ->join('student_results as sr', 'sr.id', '=', 'srd.student_result_id')
                    ->leftJoin('subjects as s', 's.id', '=', 'srd.subject_id')
                    ->where('sr.exam_master_id', $examId)
                    ->where(function ($q) use ($studentId, $pen) {
                        $q->where('sr.student_id', $studentId);
                        if (!empty($pen) && (string) $pen !== (string) $studentId) $q->orWhere('sr.student_id', $pen);
                    })
                    ->select('srd.*', 's.subject_name as skill_subject_name', 'sr.student_id')->get();
            } catch (\Exception $e) {}
        }

        foreach ($skillMarks as $mark) {
            $grade = null;
            if (isset($mark->is_absent) && $mark->is_absent == 1) {
                $grade = (!empty($mark->grade) && in_array(strtoupper(trim($mark->grade)), ['F', 'AB'])) ? strtoupper(trim($mark->grade)) : 'AB';
            } elseif (!empty($mark->grade)) {
                $grade = strtoupper(trim($mark->grade));
            }
            if ($grade) {
                $isCurrentExam = isset($mark->exam_master_id) && $mark->exam_master_id == $examId;
                if (isset($mark->skill_slot) && !empty($mark->skill_slot)) {
                    $rawSlot = strtoupper(trim($mark->skill_slot));
                    $slot = is_numeric($rawSlot) ? 'S' . $rawSlot : $rawSlot;
                    if ($isCurrentExam || !isset($skillGrades[$slot])) $skillGrades[$slot] = $grade;
                }
                if (!empty($mark->skill_subject_name)) {
                    $name = strtoupper(trim($mark->skill_subject_name));
                    if ($isCurrentExam || !isset($skillGrades[$name])) $skillGrades[$name] = $grade;
                }
            }
        }

        return $examSubjects->map(function ($ex) use ($marksBySubject, $manualGrades, $skillGrades, $is10orHigher) {
            $subjectId = (int) $ex->subject_id;
            if (!empty($ex->is_graded)) {
                $grade = null;
                $skillMap = $this->mapSubjectToSkill($ex->subject_name);
                $skillSlot = $skillMap['slot'];
                if ($skillSlot && isset($skillGrades[$skillSlot])) $grade = $skillGrades[$skillSlot];
                if (!$grade) {
                    $nameKey = strtoupper(trim($ex->subject_name));
                    $grade = $skillGrades[$nameKey] ?? null;
                    if (!$grade) {
                        $searchTerms = [
                            'SKILL 1' => ['ART', 'DRAWING', 'A/D/M/T', 'CRAFT', 'COLOURING'],
                            'SKILL 2' => ['WORK EXP', 'ABACUS', 'COMPUTER', 'ROBOTICS', 'WORK EXPERIENCE'],
                            'SKILL 3' => ['PT', 'HEALTH', 'PHYSICAL', 'UPASNA', 'CHINTANIKA', 'P ED'],
                            'SKILL 4' => ['LIBRARY', 'SANSKRIT', 'OTHERS', 'SANSKRIT BHARTI'],
                        ];
                        $matchedSkillKey = null;
                        foreach ($searchTerms as $sKey => $terms) {
                            if (str_contains($nameKey, $sKey)) { $matchedSkillKey = $sKey; break; }
                        }
                        if ($matchedSkillKey) {
                            foreach ($searchTerms[$matchedSkillKey] as $term) {
                                foreach ($skillGrades as $key => $value) {
                                    if (is_string($key) && str_contains($key, $term)) { $grade = $value; break 2; }
                                }
                            }
                        }
                        if (!$grade) {
                            foreach ($skillGrades as $key => $value) {
                                if (is_string($key) && (str_contains($key, $nameKey) || str_contains($nameKey, $key))) { $grade = $value; break; }
                            }
                        }
                    }
                    $grade = $grade ?? $manualGrades[$subjectId] ?? $manualGrades[(string) $subjectId] ?? $manualGrades[$this->subjectNameToKey($ex->subject_name)] ?? '-';
                }
                return (object) [
                    'subject_id' => $subjectId, 'subject_name' => $ex->subject_name, 'subject_code' => $ex->subject_code,
                    'max_marks' => 0, 'passing_marks' => 0, 'obtained_marks' => 0,
                    'subject_result' => $grade, 'is_graded' => 1, 'has_marks' => $grade !== '-' && $grade !== '',
                ];
            }

            $mark = $marksBySubject->get('NAME_' . strtoupper(trim($ex->subject_name))) ?? $marksBySubject->get($subjectId);
            $obtained = $maxSum = $passSum = 0.0;
            if ($mark) {
                $obtained += (float) ($mark->theory_obtained_marks ?? 0) + (float) ($mark->oral_obtained_marks ?? 0) + (float) ($mark->practical_obtained_marks ?? 0);
                $maxSum += (float) ($mark->theory_max_marks ?? 0) + (float) ($mark->oral_max_marks ?? 0) + (float) ($mark->practical_max_marks ?? 0);
                $passSum += (float) ($mark->theory_passing_marks ?? 0) + (float) ($mark->oral_passing_marks ?? 0) + (float) ($mark->practical_passing_marks ?? 0);
            }
            $maxMarks = (float) ($ex->max_marks ?? 0);
            if ($maxMarks <= 0 && $maxSum > 0) $maxMarks = $maxSum;
            if ($maxMarks <= 0 && $mark) $maxMarks = 40;
            $passMarks = (float) ($ex->passing_marks ?? 0);
            if ($is10orHigher) {
                $passMarks = (int) ceil(($maxMarks * 35) / 100);
            } else {
                if ($passMarks <= 0 && $passSum > 0) $passMarks = $passSum;
                if ($passMarks <= 0 && $maxMarks > 0) $passMarks = (int) ceil(($maxMarks * 35) / 100);
            }
            $hasMarks = $this->markHasAnyObtained($mark);
            $subjectResult = '-';
            if ($hasMarks) {
                $isOptional = (int) ($mark->is_optional ?? 0) === 1;
                $isAbsent = (int) ($mark->is_absent ?? 0) === 1;
                if ($isOptional && $obtained <= 0) $subjectResult = 'OPT';
                elseif ($isAbsent) $subjectResult = 'AB';
                elseif ($obtained >= $passMarks) $subjectResult = 'PASS';
                else $subjectResult = 'FAIL';
            }
            return (object) [
                'subject_id' => $subjectId, 'subject_name' => $ex->subject_name, 'subject_code' => $ex->subject_code,
                'max_marks' => $maxMarks, 'passing_marks' => $passMarks, 'obtained_marks' => $obtained,
                'subject_result' => $subjectResult, 'is_graded' => 0, 'has_marks' => $hasMarks,
            ];
        })->values();
    }

    private function buildProgressCardSubjects($studentId, $examId, $standardId, $academicYearId, array $manualGrades = [], $standardName = '', $examName = '', $skillObservations = null, $pen = null): \Illuminate\Support\Collection
    {
        $isPrePrimary = $this->isPrePrimaryStandard($standardName);
        $stdNameUpper = strtoupper(trim((string) $standardName));
        $is11or12 = preg_match('/\b(11|12)\b/', $stdNameUpper) || str_contains($stdNameUpper, 'ELEVENTH') || str_contains($stdNameUpper, 'TWELFTH') || str_contains($stdNameUpper, 'XI') || str_contains($stdNameUpper, 'XII');
        $is10orHigher = preg_match('/\b(10|11|12)\b/', $stdNameUpper) || str_contains($stdNameUpper, 'TENTH') || str_contains($stdNameUpper, 'ELEVENTH') || str_contains($stdNameUpper, 'TWELFTH') || in_array($stdNameUpper, ['X', 'XI', 'XII']);

        $swsMap = $standardId ? DB::table('standard_wise_subjects')->where('standard_id', $standardId)->get()->keyBy('subject_id') : collect();
        $examSubjects = DB::table('standard_wise_subjects as sws')
            ->join('subjects as s', 's.id', '=', 'sws.subject_id')
            ->where('sws.standard_id', $standardId)->where('sws.is_active', 1)->where('s.is_active', 1)
            ->whereNotIn('s.subject_type_id', [2, 3])
            ->orderBy('sws.sort_order')->orderBy('sws.id')
            ->select([
                's.id as subject_id', 's.subject_name as master_name', 'sws.subject_name as sws_name',
                's.subject_code', 'sws.sort_order as default_display_order',
            ])->get();

        $examDetails = DB::table('exam_master_subjects')
            ->where('exam_master_id', $examId)->when($standardId, fn ($q) => $q->where('standard_id', $standardId))
            ->get()->keyBy('subject_id');

        $examSubjects = $examSubjects->map(function ($sub) use ($examDetails) {
            $ex = $examDetails->get($sub->subject_id);
            return (object) [
                'subject_id' => $sub->subject_id,
                'subject_name' => !empty($sub->sws_name) ? $sub->sws_name : $sub->master_name,
                'subject_code' => $sub->subject_code,
                'max_marks' => $ex ? $ex->max_marks : 0,
                'passing_marks' => $ex ? $ex->passing_marks : 0,
                'display_order' => ($sub->default_display_order > 0) ? $sub->default_display_order : ($ex ? $ex->display_order : 999),
                'is_graded' => 0,
            ];
        })->unique('subject_id')->values();

        if ($is11or12) {
            $examSubjects = $examSubjects->reject(fn ($s) => strtoupper(trim($s->subject_name)) === 'COMPUTER SCIENCE')->values();
        }

        $skillNamesToRemove = ['MUSIC', 'DRAWING', 'SANSKRIT BHARTI', 'DANCE', 'ACTING', 'TABLA', 'ABACUS', 'COMPUTER', 'ROBOTICS', 'CRAFT', 'PHYSICAL EDUCATION', 'DEFENCE STUDIES', 'WATER SECURITY', 'T/S/A/D', 'PT', 'ART', 'WE'];
        if ($this->isHighSchoolStandard($standardName) || $is10orHigher) {
            $examSubjects = $examSubjects->reject(function ($s) use ($skillNamesToRemove) {
                $name = strtoupper(trim($s->subject_name));
                foreach ($skillNamesToRemove as $skill) {
                    if ($name === $skill || str_contains($name, $skill)) return true;
                }
                return false;
            })->values();
        }

        $prePrimarySkills = ['CRAFT', 'DRAMA', 'LIFE SKILL', 'CLAY', 'SONG', 'INDOOR GAME', 'OUTDOOR GAME', 'BOOKS', 'TOY'];
        $skillGrades = [];
        $skillMarks = collect();
        try {
            $skillMarks = DB::table('student_skill_marks as ssm')
                ->leftJoin('subjects as s', 's.id', '=', 'ssm.subject_id')
                ->where('ssm.exam_master_id', $examId)
                ->where(function ($q) use ($studentId, $pen) {
                    $q->where('ssm.student_id', $studentId);
                    if (!empty($pen) && (string) $pen !== (string) $studentId) $q->orWhere('ssm.student_id', $pen);
                })
                ->select('ssm.*', 's.subject_name as skill_subject_name')->get();
        } catch (\Exception $e) {}

        if ($skillMarks->isEmpty()) {
            try {
                $skillMarks = DB::table('student_result_details as srd')
                    ->join('student_results as sr', 'sr.id', '=', 'srd.student_result_id')
                    ->leftJoin('subjects as s', 's.id', '=', 'srd.subject_id')
                    ->where('sr.exam_master_id', $examId)
                    ->where(function ($q) use ($studentId, $pen) {
                        $q->where('sr.student_id', $studentId);
                        if (!empty($pen) && (string) $pen !== (string) $studentId) $q->orWhere('sr.student_id', $pen);
                    })
                    ->select('srd.*', 's.subject_name as skill_subject_name', 'sr.student_id')->get();
            } catch (\Exception $e) {}
        }

        foreach ($skillMarks as $mark) {
            $grade = !empty($mark->grade) ? strtoupper(trim($mark->grade)) : null;
            if ($grade) {
                $isCurrentExam = isset($mark->exam_master_id) && $mark->exam_master_id == $examId;
                if (isset($mark->skill_slot) && !empty($mark->skill_slot)) {
                    $rawSlot = strtoupper(trim($mark->skill_slot));
                    $slot = is_numeric($rawSlot) ? 'S' . $rawSlot : $rawSlot;
                    if ($isCurrentExam || !isset($skillGrades[$slot])) $skillGrades[$slot] = $grade;
                }
                if (!empty($mark->skill_subject_name)) {
                    $name = strtoupper(trim($mark->skill_subject_name));
                    if ($isCurrentExam || !isset($skillGrades[$name])) $skillGrades[$name] = $grade;
                }
            }
        }

        if ($isPrePrimary || $this->isHighSchoolStandard($standardName)) {
            $newSkillSubjects = ['WATER SECURITY', 'DEFENCE STUDIES', 'DEFENSE SCIENCE'];
            $obsCollection = $skillObservations ?? collect();
            if (!$isPrePrimary) {
                $obsCollection = $obsCollection->filter(function ($obs) use ($newSkillSubjects) {
                    $name = strtoupper(trim($obs->subject_name ?? ''));
                    return in_array($name, $newSkillSubjects);
                });
            }
            foreach ($obsCollection as $obs) {
                if (isset($obs->subject_id) && $obs->subject_id > 0) {
                    $grade = (isset($obs->is_absent) && $obs->is_absent == 1) ? 'AB' : (!empty($obs->overall_grade) ? strtoupper(trim($obs->overall_grade)) : null);
                    if ($grade) {
                        $skillGrades[(int) $obs->subject_id] = $grade;
                        if (!empty($obs->subject_name)) $skillGrades[strtoupper(trim($obs->subject_name))] = $grade;
                    }
                }
            }
        }

        $examSubjects = $examSubjects->map(function ($ex) use ($isPrePrimary, $prePrimarySkills) {
            $isSkill = false;
            $name = strtoupper(trim((string) $ex->subject_name));
            if ($isPrePrimary) {
                foreach ($prePrimarySkills as $skill) {
                    if (str_contains($name, $skill)) { $isSkill = true; break; }
                }
            }
            if ($isSkill) { $ex->is_graded = 1; $ex->display_order = 900; }
            return $ex;
        });

        if ($isPrePrimary) {
            $existingNames = $examSubjects->map(fn ($s) => strtoupper(trim((string) $s->subject_name)))->toArray();
            $skillIdMap = DB::table('subjects')->select('id', DB::raw('UPPER(TRIM(subject_name)) as normalized_name'))->get()->pluck('id', 'normalized_name')->toArray();
            $order = 900;
            foreach ($prePrimarySkills as $skillName) {
                $found = false;
                foreach ($existingNames as $exName) {
                    if (str_contains($exName, $skillName) || str_contains($skillName, $exName)) { $found = true; break; }
                }
                if (!$found) {
                    $actualId = $skillIdMap[$skillName] ?? 0;
                    if ($actualId == 0) {
                        foreach ($skillIdMap as $dbName => $dbId) {
                            if (str_contains($dbName, $skillName) || str_contains($skillName, $dbName)) { $actualId = $dbId; break; }
                        }
                    }
                    $examSubjects->push((object) [
                        'subject_id' => $actualId, 'subject_name' => $skillName, 'subject_code' => '',
                        'max_marks' => 0, 'passing_marks' => 0, 'display_order' => $order++, 'is_graded' => 1,
                    ]);
                    $existingNames[] = $skillName;
                }
            }
        }

        if ($this->isHighSchoolStandard($standardName)) {
            $mergedSubjects = collect();
            $normalize = fn ($s) => trim(preg_replace('/[\s\-_]+/', ' ', strtoupper((string) $s)));
            $findExact = fn (array $candidates) => $examSubjects->first(fn ($s) => in_array($normalize($s->subject_name), array_map($normalize, $candidates), true));
            $groups = [
                ['candidates' => ['MATHEMATICS I', 'MATHEMATICS 1', 'MATH I', 'MATH 1', 'MATHS I', 'MATHS 1', 'MATHEMATICS II', 'MATHEMATICS 2', 'MATH II', 'MATH 2', 'MATHS II', 'MATHS 2'], 'name' => 'MATHEMATICS', 'code' => 'MATH', 'order' => 100],
                ['candidates' => ['SCIENCE I', 'SCIENCE 1', 'SCI I', 'SCI 1', 'SCIENCE II', 'SCIENCE 2', 'SCI II', 'SCI 2'], 'name' => 'SCIENCE & TECH', 'code' => 'SCI', 'order' => 200],
                ['candidates' => ['HISTORY', 'HIST', 'GEOGRAPHY', 'GEO'], 'name' => 'SOCIAL SCIENCE', 'code' => 'SST', 'order' => 300],
            ];
            foreach ($groups as $group) {
                $matches = collect($group['candidates'])->map(fn ($candidate) => $findExact([$candidate]))->filter()->unique('subject_id')->values();
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
        
        // Sort to put MARATHI first, then ENGLISH, then others
        $examSubjects = $examSubjects->map(function ($ex) {
            if (preg_match('/^SKILL\s*([1-9])(?![0-9])/i', strtoupper(trim((string) ($ex->subject_name ?? ''))), $m)) {
                $ex->display_order = 920 + (int) $m[1];
            }
            return $ex;
        })->sort(function ($a, $b) {
            $aName = strtoupper(trim((string) $a->subject_name));
            $bName = strtoupper(trim((string) $b->subject_name));
            
            $getWeight = function($name) {
                if (str_contains($name, 'MARATHI')) return 1;
                if (str_contains($name, 'ENGLISH')) return 2;
                return 10;
            };
            
            $aWeight = $getWeight($aName);
            $bWeight = $getWeight($bName);
            
            if ($aWeight === $bWeight) {
                return ($a->display_order ?? 999) <=> ($b->display_order ?? 999);
            }
            return $aWeight <=> $bWeight;
        })->values();

        $marksBySubject = $this->getSubjectMarksMap($studentId, $examId, $standardId, $swsMap, $pen);
        $examNameStr = strtoupper(trim((string) $examName));
        $isTerm = str_contains($examNameStr, 'TERM');
        $isUnitTest = str_contains($examNameStr, 'UNIT TEST');

        return $examSubjects->map(function ($ex) use ($marksBySubject, $manualGrades, $skillGrades, $isTerm, $isUnitTest, $is10orHigher, $isPrePrimary) {
            $subjectId = (int) $ex->subject_id;
            if (!empty($ex->is_graded)) {
                $grade = null;
                $skillMap = $this->mapSubjectToSkill($ex->subject_name);
                $skillSlot = $skillMap['slot'];
                if ($skillSlot && isset($skillGrades[$skillSlot])) $grade = $skillGrades[$skillSlot];
                if (!$grade) {
                    $nameKey = strtoupper(trim($ex->subject_name));
                    $grade = $skillGrades[$nameKey] ?? null;
                    if (!$grade) {
                        $searchTerms = [
                            'SKILL 1' => ['ART', 'DRAWING', 'A/D/M/T', 'CRAFT', 'COLOURING'],
                            'SKILL 2' => ['WORK EXP', 'ABACUS', 'COMPUTER', 'ROBOTICS', 'WORK EXPERIENCE'],
                            'SKILL 3' => ['PT', 'HEALTH', 'PHYSICAL', 'UPASNA', 'CHINTANIKA', 'P ED'],
                            'SKILL 4' => ['LIBRARY', 'SANSKRIT', 'OTHERS', 'SANSKRIT BHARTI'],
                        ];
                        $matchedSkillKey = null;
                        foreach ($searchTerms as $sKey => $terms) {
                            if (str_contains($nameKey, $sKey)) { $matchedSkillKey = $sKey; break; }
                        }
                        if ($matchedSkillKey) {
                            foreach ($searchTerms[$matchedSkillKey] as $term) {
                                foreach ($skillGrades as $key => $value) {
                                    if (is_string($key) && str_contains($key, $term)) { $grade = $value; break 2; }
                                }
                            }
                        }
                    }
                    if (!$grade) {
                        foreach ($skillGrades as $key => $value) {
                            if (is_string($key) && (str_contains($key, $nameKey) || str_contains($nameKey, $key))) { $grade = $value; break; }
                        }
                    }
                    $grade = $grade ?? $manualGrades[$subjectId] ?? $manualGrades[(string) $subjectId] ?? $manualGrades[$this->subjectNameToKey($ex->subject_name)] ?? '-';
                }
                return (object) [
                    'subject_id' => $subjectId, 'subject_name' => $ex->subject_name, 'subject_code' => $ex->subject_code ?? '',
                    'max_marks' => 0, 'obtained_marks' => 0, 'grade' => $grade, 'subject_result' => $grade,
                    'is_graded' => 1, 'has_marks' => $grade !== '-' && $grade !== '',
                ];
            }

            $obtained = $maxSum = 0.0;
            $firstMark = null;
            $idsToCheck = (isset($ex->merged_ids) && is_array($ex->merged_ids) && !empty($ex->merged_ids)) ? $ex->merged_ids : [$subjectId];
            foreach ($idsToCheck as $sid) {
                $mark = $marksBySubject->get('NAME_' . strtoupper(trim($ex->subject_name))) ?? $marksBySubject->get($sid);
                if (!$mark) continue;
                if ($firstMark === null) $firstMark = $mark;
                $obtained += (float) ($mark->theory_obtained_marks ?? 0) + (float) ($mark->oral_obtained_marks ?? 0) + (float) ($mark->practical_obtained_marks ?? 0);
                $maxSum += (float) ($mark->theory_max_marks ?? 0) + (float) ($mark->oral_max_marks ?? 0) + (float) ($mark->practical_max_marks ?? 0);
            }

            $maxMarks = (float) ($ex->max_marks ?? 0);
            if (isset($ex->merged_ids) && !empty($ex->merged_ids)) {
                if (in_array(strtoupper($ex->subject_name), ['MATHEMATICS', 'SCIENCE & TECH', 'SOCIAL SCIENCE'])) {
                    if ($isTerm) $maxMarks = 100;
                    elseif ($isUnitTest) $maxMarks = 40;
                    elseif ($maxSum > 0) $maxMarks = $maxSum;
                } elseif ($maxMarks <= 0 && $maxSum > 0) {
                    $maxMarks = $maxSum;
                }
            } else {
                if ($maxMarks <= 0 && $maxSum > 0) $maxMarks = $maxSum;
                if ($maxMarks <= 0 && $firstMark) $maxMarks = 40;
            }

            $passMarks = (float) ($ex->passing_marks ?? 0);
            if ($is10orHigher) $passMarks = (int) ceil(($maxMarks * 35) / 100);
            elseif ($passMarks <= 0 && $maxMarks > 0) $passMarks = (int) ceil(($maxMarks * 35) / 100);

            $hasMarks = false;
            foreach ($idsToCheck as $sid) {
                if ($this->markHasAnyObtained($marksBySubject->get('NAME_' . strtoupper(trim($ex->subject_name))) ?? $marksBySubject->get($sid))) {
                    $hasMarks = true; break;
                }
            }

            $grade = '-';
            if ($hasMarks && $firstMark) {
                $isOptional = (int) ($firstMark->is_optional ?? 0) === 1;
                $isAbsent = (int) ($firstMark->is_absent ?? 0) === 1;
                if ($isOptional && $obtained <= 0) $grade = 'OPT';
                elseif ($isAbsent) $grade = 'AB';
                else $grade = $this->gradeFromPercentage($maxMarks > 0 ? ($obtained / $maxMarks) * 100 : 0);
            }
            if ($isPrePrimary && $grade === '-') {
                $grade = $manualGrades[$subjectId] ?? $manualGrades[(string) $subjectId] ?? $manualGrades[$this->subjectNameToKey($ex->subject_name)] ?? '-';
            }
            return (object) [
                'subject_id' => $subjectId, 'subject_name' => $ex->subject_name, 'subject_code' => $ex->subject_code,
                'max_marks' => $maxMarks, 'obtained_marks' => $obtained, 'grade' => $grade, 'subject_result' => $grade,
                'is_graded' => 0, 'has_marks' => $hasMarks || ($isPrePrimary && $grade !== '-'),
            ];
        })->values();
    }

    private function ensureAdditionalSubjects($examSubjects, $standardId, $standardName = ''): \Illuminate\Support\Collection
    {
        $s = strtoupper(trim((string) $standardName));
        if (preg_match('/\b(11|12)\b/', $s) || str_contains($s, 'ELEVENTH') || str_contains($s, 'TWELFTH') || str_contains($s, 'XI') || str_contains($s, 'XII')) {
            return $examSubjects;
        }

        $existingNames = $examSubjects->map(fn ($subject) => strtoupper(trim((string) $subject->subject_name)))->all();
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
        $subjectLookup = empty($allSearchNames) ? collect()
            : DB::table('subjects')->whereIn('subject_name', $allSearchNames)->where('is_active', 1)->get()->keyBy('subject_name');

        foreach ($additional as $add) {
            if ($add['only_hs'] && !$is9thOr10th) continue;
            $found = false;
            foreach ($add['search'] as $name) {
                if (in_array($name, $existingNames, true)) { $found = true; break; }
            }
            if ($found) continue;

            $realSubject = null;
            foreach ($add['search'] as $name) {
                if (isset($subjectLookup[$name])) { $realSubject = $subjectLookup[$name]; break; }
            }
            $examSubjects->push((object) [
                'subject_id' => $realSubject ? (int) $realSubject->id : 0,
                'subject_name' => $add['display'],
                'subject_code' => $realSubject->subject_code ?? '',
                'max_marks' => 0, 'passing_marks' => 0,
                'display_order' => $add['order'], 'is_graded' => 1,
            ]);
            $existingNames[] = $add['display'];
        }
        return $examSubjects->sortBy(fn ($subject) => (int) ($subject->display_order ?? 999))->values();
    }

    private function computeOverall($subjects): array
    {
        $totalMax = $totalObt = 0.0;
        $anyMark = false;
        $hasFail = false;
        foreach ($subjects as $subject) {
            if (!empty($subject->is_graded)) continue;
            $status = strtoupper(trim((string) ($subject->subject_result ?? $subject->grade ?? '')));
            if ($status === 'OPT') continue;
            $max = (float) ($subject->max_marks ?? 0);
            if ($max > 0) $totalMax += $max;
            $hasMarks = property_exists($subject, 'has_marks') ? (bool) $subject->has_marks : ($status !== '' && $status !== '-');
            if ($hasMarks) {
                $anyMark = true;
                $totalObt += (float) ($subject->obtained_marks ?? 0);
                if (in_array($status, ['FAIL', 'AB', 'ABSENT'], true)) $hasFail = true;
            }
        }
        if (!$anyMark || $totalMax <= 0) return [null, '-', 'PENDING', $totalMax, $totalObt];
        $pct = round(($totalObt * 100) / $totalMax, 2);
        return [$pct, $this->gradeFromPercentage($pct), ($hasFail || $pct < 35) ? 'FAIL' : 'PASS', $totalMax, $totalObt];
    }

    private function gradeFromPercentage($percentage): string
    {
        $p = (float) $percentage;
        if ($p >= 91) return 'A1'; if ($p >= 81) return 'A2'; if ($p >= 71) return 'B1'; if ($p >= 61) return 'B2';
        if ($p >= 51) return 'C1'; if ($p >= 41) return 'C2'; if ($p >= 33) return 'D'; if ($p >= 21) return 'E1';
        if ($p >= 1) return 'E2'; return 'F';
    }

    private function gradeToPoint($grade): int
    {
        return ['A1' => 9, 'A2' => 8, 'B1' => 7, 'B2' => 6, 'C1' => 5, 'C2' => 4, 'D' => 3, 'E1' => 2, 'E2' => 1, 'F' => 0][strtoupper(trim((string) $grade))] ?? 0;
    }

    private function pointToGrade($point): string
    {
        if ($point >= 8.5) return 'A1'; if ($point >= 7.5) return 'A2'; if ($point >= 6.5) return 'B1'; if ($point >= 5.5) return 'B2';
        if ($point >= 4.5) return 'C1'; if ($point >= 3.5) return 'C2'; if ($point >= 2.5) return 'D'; if ($point >= 1.5) return 'E1';
        if ($point >= 0.5) return 'E2'; return 'F';
    }

    private function calculateAcademicAverageGrade($subjects): string
    {
        $totalPoints = $count = 0;
        foreach ($subjects as $subject) {
            if (!empty($subject->is_graded) || (property_exists($subject, 'has_marks') && !$subject->has_marks)) continue;
            $grade = strtoupper(trim((string) ($subject->grade ?? '-')));
            $status = strtoupper(trim((string) ($subject->subject_result ?? '-')));
            if (in_array($status, ['OPT', 'AB', 'ABSENT'], true)) continue;
            if (in_array($grade, ['-', 'PASS', 'FAIL', 'AB', 'OPT', 'ABSENT', ''], true)) {
                $max = (float) ($subject->max_marks ?? 0);
                $obt = (float) ($subject->obtained_marks ?? 0);
                if ($max > 0) $grade = $this->gradeFromPercentage(($obt / $max) * 100);
                else continue;
            }
            if (in_array($grade, ['A1', 'A2', 'B1', 'B2', 'C1', 'C2', 'D', 'E1', 'E2', 'F'], true)) {
                $totalPoints += $this->gradeToPoint($grade);
                $count++;
            }
        }
        return $count === 0 ? '-' : $this->pointToGrade($totalPoints / $count);
    }

    private function calculateSpecialSkillAverageGrade($subjects, $standardName = ''): string
    {
        $totalPoints = $count = 0;
        $hasAcademic = false;
        $skillGradeMap = ['C' => 1, 'B' => 2, 'B+' => 3, 'A' => 4, 'A+' => 5];
        $academicGradeMap = ['A1' => 9, 'A2' => 8, 'B1' => 7, 'B2' => 6, 'C1' => 5, 'C2' => 4, 'D' => 3, 'E1' => 2, 'E2' => 1, 'F' => 0];
        $stdNameUpper = strtoupper(trim((string) $standardName));
        $isTenth = preg_match('/\b(10|X|TENTH)\b/i', $stdNameUpper);

        foreach ($subjects as $subject) {
            if (empty($subject->is_graded)) continue;
            if ($isTenth) {
                if (preg_match('/^SKILL\s*1/i', $subject->subject_name)) continue;
                if (preg_match('/^SKILL\s*4/i', $subject->subject_name)) continue;
            }
            $grade = strtoupper(trim((string) ($subject->grade ?? $subject->subject_result ?? '-')));
            if (in_array($grade, ['-', 'OPT', 'AB', 'PASS', 'FAIL', ''], true)) continue;
            if (isset($skillGradeMap[$grade])) { $totalPoints += $skillGradeMap[$grade]; $count++; }
            elseif (isset($academicGradeMap[$grade])) { $totalPoints += $academicGradeMap[$grade]; $count++; $hasAcademic = true; }
        }

        if ($count === 0) return '-';
        $avgValue = $totalPoints / $count;
        if ($hasAcademic) return $this->pointToGrade($avgValue);
        if ($avgValue >= 4.5) return 'A+'; if ($avgValue >= 3.5) return 'A'; if ($avgValue >= 2.5) return 'B+';
        if ($avgValue >= 1.5) return 'B'; return 'C';
    }

    private function defaultAttendanceData(): array
    {
        $data = [];
        foreach ([
            'Jyeshtha' => 31, 'Ashadh' => 31, 'Shravan' => 31, 'Bhadrapad' => 31,
            'Ashwin' => 30, 'Kartik' => 30, 'Margshirsh' => 30, 'Paush' => 30,
            'Magh' => 30, 'Phalgun' => 30, 'Chaitra' => 30, 'Vaishakh' => 31,
        ] as $month => $days) {
            $data[$month] = ['total_days' => (string) $days, 'working' => '', 'present' => '', 'absent' => ''];
        }
        return $data;
    }

    public function examsByStandard(Request $request)
    {
        try {
            $data = $request->validate([
                'standard_id' => 'required|integer',
                'academic_year_id' => 'nullable|integer',
            ]);
            $query = ExamMaster::query()->where('standard_id', $data['standard_id']);
            if (!empty($data['academic_year_id'])) {
                $query->where(fn ($q) => $q->where('academic_year_id', $data['academic_year_id'])->orWhereNull('academic_year_id'));
            }
            return response()->json($query->orderBy('exam_name')->get(['id', 'exam_name']));
        } catch (\Throwable $e) {
            Log::error('examsByStandard failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load exams.'], 500);
        }
    }
}