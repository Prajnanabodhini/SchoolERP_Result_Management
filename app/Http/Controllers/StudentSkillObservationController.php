<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\AcademicYear;
use App\Models\Standard;
use App\Models\Division;
use App\Models\Subject;
use App\Models\ExamMaster;
use App\Models\StudentSkillSubject;
use App\Models\StudentSkillObservation;
use App\Models\UserDesignation;
use App\Helpers\StudentHelper;
use App\Helpers\SkillCriteriaHelper;

class StudentSkillObservationController extends Controller
{
    /* =====================================================================
     | DATA FETCHING HELPERS
     | ===================================================================== */

    private function fetchErpStudents(string $yearId, int $standardId, int $divisionId): \Illuminate\Support\Collection
    {
        $result = StudentHelper::getStudentsDirectERP($yearId, $standardId, $divisionId);

        if ($result instanceof \Illuminate\Support\Collection) {
            return $result;
        } elseif (is_array($result)) {
            return collect($result)->map(fn ($r) => (object) $r);
        }

        return collect();
    }

    private function fetchAcademicYears(): \Illuminate\Support\Collection
    {
        return AcademicYear::where('is_active', 1)
            ->orderByDesc('year_name')
            ->get(['id', 'year_name'])
            ->map(fn ($y) => (object) [
                'id' => (int) $y->id, 
                'year_name' => (string) $y->year_name
            ]);
    }

    private function fetchStandards(): \Illuminate\Support\Collection
    {
        return Standard::where('is_active', 1)
            ->orderBy('display_order')
            ->get(['id', 'standard_name', 'display_order'])
            ->map(fn ($r) => (object) [
                'id'            => (int) $r->id,
                'standard_name' => (string) $r->standard_name,
                'display_order' => (int) $r->display_order,
            ]);
    }

    private function fetchDivisions(): \Illuminate\Support\Collection
    {
        return Division::where('is_active', 1)
            ->orderBy('division_name')
            ->get(['id', 'division_name'])
            ->map(fn ($r) => (object) [
                'id'            => (int) $r->id,
                'division_name' => (string) $r->division_name,
            ]);
    }

    /* ⭐ Removed cache logic so new subjects reflect instantly */
    private function fetchSkillSubjects(): \Illuminate\Support\Collection
    {
        $rows = Subject::whereIn('subject_type_id', [2, 3])
            ->where('is_active', 1)
            ->orderBy('subject_name')
            ->get(['id', 'subject_name']);

        $uniqueSubjects = [];

        foreach ($rows as $r) {
            $name = strtoupper(trim((string) $r->subject_name));
            if ($name === '') continue;

            if (!isset($uniqueSubjects[$name])) {
                $uniqueSubjects[$name] = (object) [
                    'id'           => (int) $r->id,
                    'subject_name' => (string) $r->subject_name,
                ];
            }
        }

        return collect(array_values($uniqueSubjects))
            ->sortBy('subject_name')
            ->values();
    }

    /* =====================================================================
     | FILTER SKILL SUBJECTS BY STANDARD
     | ===================================================================== */
    private function getFilteredSkillSubjects($standardOrName, \Illuminate\Support\Collection $allSubjects): \Illuminate\Support\Collection
    {
        if (is_string($standardOrName)) {
            $stdName = $standardOrName;
        } elseif (is_object($standardOrName) && isset($standardOrName->standard_name)) {
            $stdName = (string) $standardOrName->standard_name;
        } else {
            $std = Standard::find((int) $standardOrName);
            if (!$std) return collect();
            $stdName = (string) $std->standard_name;
        }

        $stdNameLower = strtolower(trim($stdName));
        $targetNames = [];

        if (preg_match('/(nursery|jr kg|sr kg|jrkg|srkg|pre-primary)/', $stdNameLower)) {
            // Added DRAWING to the pre-primary list
            $targetNames = [
                'GROUND ACTIVITIES', 'DRAWING', 'CRAFT', 'CLAY', 'LIFE SKILL', 'SONG', 'INDOOR GAME',
                'TRG. SENSES', 'ASSEMBLY', 'ACTIVITY BOOK', 'GENERAL SKILLS'
            ];
        } elseif (preg_match('/(first|second|\b1\b|\b2\b)/', $stdNameLower)) {
            $targetNames = ['SONG', 'STORY', 'DANCE', 'SANSKRIT BOOK', 'LIBRARY', 'ABACUS', 'PHYSICAL EDUCATION'];
        } elseif (preg_match('/(third|fourth|fifth|sixth|seventh|\b[3-7]\b)/', $stdNameLower)) {
            $targetNames = ['TABLA', 'MUSIC', 'DANCE', 'ACTING', 'SANSKRIT BOOK', 'COMPUTER', 'ROBOTICS', 'ABACUS', 'PHYSICAL EDUCATION'];
        } elseif (preg_match('/(eighth|\b8\b)/', $stdNameLower)) {
            $targetNames = ['TABLA', 'MUSIC', 'DANCE', 'ACTING', 'SANSKRIT BOOK', 'COMPUTER', 'ROBOTICS', 'VEDIC MATHS', 'PHYSICAL EDUCATION'];
        } elseif (preg_match('/(ninth|\b9\b)/', $stdNameLower)) {
            $targetNames = ['TABLA', 'MUSIC', 'DANCE', 'ACTING', 'SANSKRIT BOOK', 'COMPUTER', 'ROBOTICS', 'VEDIC MATHS', 'PHYSICAL EDUCATION'];
        } elseif (preg_match('/(tenth|\b10\b)/', $stdNameLower)) {
            $targetNames = ['COMPUTER', 'ROBOTICS', 'VEDIC MATHS', 'PHYSICAL EDUCATION'];
        } else {
            return $allSubjects;
        }

        $filtered = collect();

        foreach ($targetNames as $target) {
            $found = null;

            foreach ($allSubjects as $sub) {
                $sName = strtoupper(trim($sub->subject_name));
                $match = false;

                if ($target === 'SANSKRIT BOOK') {
                    $match = str_contains($sName, 'SANSKRIT') && str_contains($sName, 'BOOK');
                    if (!$match && str_contains($sName, 'PATHANTER')) $match = true;
                    if (!$match && str_contains($sName, 'BHARTI'))    $match = true;
                    if (!$match && $sName === 'SANSKRIT')             $match = true;
                } elseif ($target === 'TABLA') {
                    $match = str_contains($sName, 'TABLA') || str_contains($sName, 'TABALA');
                } elseif ($target === 'MUSIC') {
                    $match = str_contains($sName, 'MUSIC');
                } elseif ($target === 'SONG') {
                    $match = str_contains($sName, 'SONG');
                } elseif ($target === 'ACTING') {
                    $match = str_contains($sName, 'ACTING') || str_contains($sName, 'DRAMA');
                } elseif ($target === 'BOOKS') {
                    $match = $sName === 'BOOKS' || $sName === 'BOOK';
                } elseif ($target === 'INDOOR GAME') {
                    $match = str_contains($sName, 'INDOOR GAME');
                } elseif ($target === 'OUTDOOR GAME') {
                    $match = str_contains($sName, 'OUTDOOR GAME');
                } elseif ($target === 'VEDIC MATHS') {
                    $match = str_contains($sName, 'VEDIC MATHS') || str_contains($sName, 'VEDIC MATH') || $sName === 'VEDIC MATHS' || $sName === 'VEDIC MATH';
                } elseif ($target === 'PHYSICAL EDUCATION') {
                    $match = str_contains($sName, 'PHYSICAL EDUCATION') || str_contains($sName, 'PHYSICAL ED') || str_contains($sName, 'PHY ED') || str_contains($sName, 'PHY. ED') || $sName === 'PT' || $sName === 'P.E.' || $sName === 'P.E' || $sName === 'PE';
                } else {
                    $match = $sName === $target || str_contains($sName, $target);
                }

                if ($match) {
                    $found = clone $sub;
                    $found->subject_name = $target;
                    break;
                }
            }

            if ($found) {
                $filtered->push($found);
            }
        }

        return $filtered->unique('subject_name')->values();
    }

    private function fetchCriteria(string $subjectName): array
    {
        if ($subjectName === '') return [];

        $name = strtoupper(trim($subjectName));

        // --- New Pre-Primary Criteria ---
        if (str_contains($name, 'GROUND ACTIVITIES')) {
            return ['FLEXIBILITY', 'STAMINA', 'GROSS MOTOR SKILL'];
        }
        
        // Added Criteria for DRAWING
        if (str_contains($name, 'DRAWING')) {
            return ['Accuracy', 'Neatness & Presentation', 'Creativity'];
        }

        if (str_contains($name, 'TRG. SENSES')) {
            return ['Grade', 'Remark'];
        }
        if (str_contains($name, 'ASSEMBLY')) {
            return ['ANTHEM, PLEDGE, ETC.', 'STOTRA (SANSKRIT, MARATHI)'];
        }
        if (str_contains($name, 'ACTIVITY BOOK')) {
            return ['Grade', 'Remark'];
        }
        if (str_contains($name, 'GENERAL SKILLS')) {
            return [
                'ATTENTIVENESS', 'FOOD HABITS', 'NEATNESS', 'INTERACTION WITH OTHERS', 
                'EXPRESSION OF FEELINGS', 'FOLLOWING INSTRUCTIONS', 'PARTICIPATION IN SCHOOL ACTIVITY', 
                'HAPPINESS & ENERGY LEVEL', 'OBEDIENCE', 'HELPING'
            ];
        }

        // --- Existing Criteria ---
        if (str_contains($name, 'ABACUS') || str_contains($name, 'VEDIC MATHS') || str_contains($name, 'VEDIC MATH')) return ['Learning', 'Book Completion', 'Accuracy', 'Speed', 'Marks', 'Remark'];
        if (str_contains($name, 'COMPUTER')) return ['Learning', 'C.W. (activity book)', 'Practical Marks', 'Innovative Thinking', 'Remark'];
        if (str_contains($name, 'ROBOTICS')) return ['Learning', 'C.W. (activity book)', 'Practical Marks', 'Innovative Thinking', 'Written Marks', 'Remark'];
        if (str_contains($name, 'INDOOR GAME') || str_contains($name, 'OUTDOOR GAME')) return ['Coordination', 'Attentiveness', 'Neatness'];
        if (str_contains($name, 'CRAFT')) return ['Folding Accuracy', 'Handling Coordination', 'Neatness & Presentation', 'Creativity'];
        if (str_contains($name, 'LIFE SKILL')) return ['Object Recognition', 'Handling Skills', 'Ease Participation', 'Neatness & Completion'];
        if (str_contains($name, 'CLAY')) return ['Shape & Size', 'Neatness & Finishing', 'Creativity', 'Independent Work'];
        if (str_contains($name, 'MUSIC') || str_contains($name, 'SONG')) return ['Attentiveness', 'Learning', 'Presentation', 'Voice Quality', 'Rhythm & Melody', 'Remark'];
        if (str_contains($name, 'TABLA') || str_contains($name, 'TABALA')) return ['Attentiveness', 'Learning', 'Presentation', 'Accompanying singer during performance', 'Remark'];
        if (str_contains($name, 'STORY')) return ['Attentiveness', 'Learning', 'Presentation', 'Retelling & Sequence', 'Remark'];
        if (str_contains($name, 'PT') || str_contains($name, 'PHYSICAL EDUCATION')) return ['Oral', 'PT Practical Exam', 'Uniform', 'PT Periods'];

        $defaultCriteria = SkillCriteriaHelper::getCriteria($subjectName);
        return array_values(array_filter($defaultCriteria, function($item) {
            return strtolower(trim($item)) !== 'marks';
        }));
    }

    private function resolveErpYearId(?AcademicYear $academicYear): string
    {
        if (!$academicYear) return '';
        return (string) substr((string) $academicYear->year_name, 0, 4);
    }

    /* =====================================================================
     | SHARED DATA COLLECTOR
     | ===================================================================== */
    private function collectEntryData(Request $request): array
    {
        $academicYears    = $this->fetchAcademicYears();
        $standards        = $this->fetchStandards();
        $divisions        = $this->fetchDivisions();
        $allSkillSubjects = $this->fetchSkillSubjects();

        $selectedStandardId = (int) $request->input('standard_id', 0);

        if ($selectedStandardId > 0) {
            $filtered = $this->getFilteredSkillSubjects($selectedStandardId, $allSkillSubjects);
            $skillSubjects = $filtered->isNotEmpty() ? $filtered : $allSkillSubjects;
        } else {
            $skillSubjects = $allSkillSubjects;
        }

        $skillSubjectsByStandard = [];
        foreach ($standards as $std) {
            $filtered = $this->getFilteredSkillSubjects($std, $allSkillSubjects);
            $list     = $filtered->isNotEmpty() ? $filtered : $allSkillSubjects;

            $skillSubjectsByStandard[(string) $std->id] = $list->map(fn ($s) => [
                'id'           => (int) $s->id,
                'subject_name' => (string) $s->subject_name,
            ])->values()->all();
        }

        $exams = ExamMaster::where('is_active', 1)->orderBy('exam_name')->get(['id', 'exam_name', 'standard_id']);

        $students    = collect();
        $criteria    = [];
        $subjectName = null;
        $existing    = collect();
        $erpError    = null;

        $allFiltersPresent = $request->filled(['academic_year_id', 'standard_id', 'division_id', 'subject_id']);

        if ($allFiltersPresent) {
            $academicYear = AcademicYear::find($request->academic_year_id, ['id', 'year_name']);
            $subject      = Subject::find($request->subject_id, ['id', 'subject_name']);

            if ($academicYear && $subject) {
                $subjectName = $subject->subject_name;
                $criteria    = $this->fetchCriteria($subjectName);

                $yearId = $this->resolveErpYearId($academicYear);

                try {
                    $students = $this->fetchErpStudents($yearId, (int) $request->standard_id, (int) $request->division_id);
                } catch (\Throwable $e) {
                    Log::warning('Skill Observation — Old ERP lookup failed', ['message' => $e->getMessage(), 'year_id' => $yearId, 'standard_id' => $request->standard_id, 'division_id' => $request->division_id]);
                    $students = collect();
                    $erpError = 'The Old ERP database is currently unreachable. Please try again in a moment.';
                }

                if ($students->isNotEmpty()) {
                    $studentIds = $students->pluck('Studentid')->all();
                    $existing = StudentSkillObservation::where('academic_year_id', $academicYear->id)
                        ->where('standard_id', (int) $request->standard_id)
                        ->where('division_id', (int) $request->division_id)
                        ->where('exam_master_id', $request->exam_master_id ?: 0)
                        ->where('subject_id', $subject->id)
                        ->whereIn('student_id', $studentIds)
                        ->get(['student_id', 'observations', 'is_absent', 'overall_grade'])
                        ->keyBy('student_id');
                }
            }
        }

        $skillTeacherName = '';
        if ($request->filled(['academic_year_id', 'standard_id', 'division_id', 'exam_master_id', 'subject_id'])) {
            $skillTeacherName = $this->resolveSkillTeacherName(
                (int) $request->academic_year_id,
                (int) $request->standard_id,
                (int) $request->division_id,
                (int) $request->exam_master_id,
                (int) $request->subject_id
            );
        }

        return compact(
            'academicYears', 'standards', 'divisions', 'exams', 'skillSubjects',
            'skillSubjectsByStandard', 'students', 'criteria', 'subjectName',
            'existing', 'erpError', 'skillTeacherName'
        );
    }

    public function index(Request $request)
    {
        $data = $this->collectEntryData($request);
        return view('student-skill-observations.index', $data);
    }

    public function printEntry(Request $request)
    {
        $data = $this->collectEntryData($request);

        $academicYear = AcademicYear::find($request->academic_year_id, ['id', 'year_name']);
        $standard     = Standard::find($request->standard_id, ['id', 'standard_name']);
        $division     = Division::find($request->division_id, ['id', 'division_name']);

        $examName = 'All Exams';
        if ($request->filled('exam_master_id')) {
            $examName = optional(ExamMaster::find($request->exam_master_id, ['id', 'exam_name']))->exam_name ?? 'All Exams';
        }

        $academicYearId = (int) $request->input('academic_year_id', 0);
        $standardId     = (int) $request->input('standard_id', 0);
        $divisionId     = (int) $request->input('division_id', 0);

        $classTeacherName = $this->resolveClassTeacherName($academicYearId, $standardId, $divisionId);
        $principalName    = $this->resolvePrincipalName();

        $viewData = array_merge($data, [
            'printAcademicYearName' => optional($academicYear)->year_name ?? '',
            'printStandardName'     => optional($standard)->standard_name ?? '',
            'printDivisionName'     => optional($division)->division_name ?? '',
            'printExamName'         => $examName,
            'classTeacherName'      => $classTeacherName,
            'principalName'         => $principalName,
        ]);

        if ($request->boolean('export')) return $viewData;

        return view('student-skill-observations.print-entry', $viewData);
    }

    public function exportEntryExcel(Request $request)
    {
        $request->merge(['export' => 1]);
        $viewData = $this->printEntry($request);
        $viewData['isExport']    = true;
        $viewData['logoDataUri'] = $this->schoolLogoDataUri();

        $html = view('student-skill-observations.print-entry', $viewData)->render();
        $html = "\xEF\xBB\xBF" . $html;

        $filename = 'skill_observation_' . date('Ymd_His') . '.xls';

        return response($html, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma'              => 'public',
        ]);
    }

    /* =====================================================================
     | MANUAL SAVE (BULK) - Kept for safety
     | ===================================================================== */
    public function save(Request $request)
    {
        $request->validate(['academic_year_id' => 'required', 'standard_id' => 'required', 'division_id' => 'required', 'subject_id' => 'required']);
        $academicYearId = $request->academic_year_id;
        $standardId     = (int) $request->standard_id;
        $divisionId     = (int) $request->division_id;
        $examMasterId   = $request->exam_master_id ?: 0;
        $subjectId      = $request->subject_id;
        $teacherId      = Auth::id();

        $subject     = Subject::find($subjectId, ['id', 'subject_name']);
        $allCriteria = $subject ? $this->fetchCriteria($subject->subject_name) : [];

        foreach ($request->observations ?? [] as $studentId => $data) {
            $isAbsent = !empty($data['is_absent']) ? 1 : 0;
            $observations = [];

            if ($isAbsent) {
                foreach ($allCriteria as $criterion) $observations[$criterion] = 'AB';
            } else {
                foreach ((array) ($data['criteria'] ?? []) as $criterion => $value) {
                    $value = trim((string) $value);
                    if ($value !== '') $observations[$criterion] = $value;
                }
            }

            if (!$isAbsent && empty($observations)) continue;

            $overallGrade = $isAbsent ? 'AB' : SkillCriteriaHelper::computeAverageGrade($observations);

            $updateData = [
                'standard_id'      => $standardId,
                'division_id'      => $divisionId,
                'skill_teacher_id' => $teacherId,
                'observations'     => $observations,
                'is_absent'        => $isAbsent,
                'overall_grade'    => $overallGrade,
                'updated_by'       => $teacherId,
                'created_by'       => $teacherId
            ];

            // Map to new specific columns
            if ($subject) {
                $subjectName = strtoupper(trim($subject->subject_name));
                if (str_contains($subjectName, 'GROUND ACTIVITIES')) {
                    $updateData['ground_activities_grade'] = $overallGrade;
                } elseif (str_contains($subjectName, 'TRG. SENSES')) {
                    $updateData['trg_senses_grade'] = $overallGrade;
                    $updateData['trg_senses_remark'] = $observations['Remark'] ?? null;
                } elseif (str_contains($subjectName, 'ASSEMBLY')) {
                    $updateData['assembly_grade'] = $overallGrade;
                } elseif (str_contains($subjectName, 'ACTIVITY BOOK')) {
                    $updateData['activity_book_grade'] = $overallGrade;
                    $updateData['activity_book_remark'] = $observations['Remark'] ?? null;
                } elseif (str_contains($subjectName, 'GENERAL SKILLS')) {
                    $updateData['general_skills_grade'] = $overallGrade;
                }
            }

            StudentSkillObservation::updateOrCreate(
                ['academic_year_id' => $academicYearId, 'exam_master_id' => $examMasterId, 'student_id' => $studentId, 'subject_id' => $subjectId],
                $updateData
            );
        }

        return redirect()->back()->with('success', 'Skill observations saved successfully.');
    }

    /* =====================================================================
     | AUTO SAVE (AJAX) - Called on input change
     | ===================================================================== */
    public function autoSave(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required',
            'standard_id'      => 'required',
            'division_id'      => 'required',
            'subject_id'       => 'required',
            'student_id'       => 'required'
        ]);

        $academicYearId = $request->academic_year_id;
        $standardId     = (int) $request->standard_id;
        $divisionId     = (int) $request->division_id;
        $examMasterId   = $request->exam_master_id ?: 0;
        $subjectId      = $request->subject_id;
        $studentId      = $request->student_id;
        $teacherId      = Auth::id();

        $subject = Subject::find($subjectId, ['id', 'subject_name']);
        if (!$subject) {
            return response()->json(['success' => false, 'message' => 'Subject not found']);
        }

        $allCriteria = $this->fetchCriteria($subject->subject_name);
        $isAbsent    = !empty($request->is_absent) ? 1 : 0;
        $observations = [];

        if ($isAbsent) {
            foreach ($allCriteria as $criterion) {
                $observations[$criterion] = 'AB';
            }
        } else {
            foreach ((array) $request->criteria as $criterion => $value) {
                $value = trim((string) $value);
                if ($value !== '') {
                    $observations[$criterion] = $value;
                }
            }
        }

        $overallGrade = $isAbsent ? 'AB' : SkillCriteriaHelper::computeAverageGrade($observations);

        $updateData = [
            'standard_id'      => $standardId,
            'division_id'      => $divisionId,
            'skill_teacher_id' => $teacherId,
            'observations'     => $observations,
            'is_absent'        => $isAbsent,
            'overall_grade'    => $overallGrade,
            'updated_by'       => $teacherId,
            'created_by'       => $teacherId
        ];

        // Map to new specific columns
        $subjectName = strtoupper(trim($subject->subject_name));
        if (str_contains($subjectName, 'GROUND ACTIVITIES')) {
            $updateData['ground_activities_grade'] = $overallGrade;
        } elseif (str_contains($subjectName, 'TRG. SENSES')) {
            $updateData['trg_senses_grade'] = $overallGrade;
            $updateData['trg_senses_remark'] = $observations['Remark'] ?? null;
        } elseif (str_contains($subjectName, 'ASSEMBLY')) {
            $updateData['assembly_grade'] = $overallGrade;
        } elseif (str_contains($subjectName, 'ACTIVITY BOOK')) {
            $updateData['activity_book_grade'] = $overallGrade;
            $updateData['activity_book_remark'] = $observations['Remark'] ?? null;
        } elseif (str_contains($subjectName, 'GENERAL SKILLS')) {
            $updateData['general_skills_grade'] = $overallGrade;
        }

        StudentSkillObservation::updateOrCreate(
            [
                'academic_year_id' => $academicYearId,
                'exam_master_id'   => $examMasterId,
                'student_id'       => $studentId,
                'subject_id'       => $subjectId
            ],
            $updateData
        );

        return response()->json([
            'success' => true,
            'overall_grade' => $overallGrade
        ]);
    }

    /* =====================================================================
     | SUMMARY
     | ===================================================================== */
    public function summary(Request $request)
    {
        $academicYears = $this->fetchAcademicYears();
        $standards     = $this->fetchStandards();
        $divisions     = $this->fetchDivisions();

        $exams = ExamMaster::where('is_active', 1)->orderBy('exam_name')->get(['id', 'exam_name', 'standard_id', 'academic_year_id']);

        $students      = collect();
        $skillSubjects = collect();
        $observations  = collect();
        $allocations   = collect();
        $erpError      = null;

        if ($request->filled(['academic_year_id', 'standard_id', 'division_id'])) {
            $academicYear = AcademicYear::find($request->academic_year_id, ['id', 'year_name']);
            if ($academicYear) {
                $allSkillSubjects = $this->fetchSkillSubjects();
                $skillSubjects = $this->getFilteredSkillSubjects((int) $request->standard_id, $allSkillSubjects);
                $yearId = $this->resolveErpYearId($academicYear);

                try {
                    $students = $this->fetchErpStudents($yearId, (int) $request->standard_id, (int) $request->division_id);
                } catch (\Throwable $e) {
                    Log::warning('Skill Summary — Old ERP lookup failed', ['message' => $e->getMessage(), 'year_id' => $yearId, 'standard_id' => $request->standard_id, 'division_id' => $request->division_id]);
                    $students = collect();
                    $erpError = 'The Old ERP database is currently unreachable. Please try again in a moment.';
                }

                if ($students->isNotEmpty()) {
                    $studentIds = $students->pluck('Studentid')->all();
                    $allocations = StudentSkillSubject::where('academic_year_id', $academicYear->id)->whereIn('student_id', $studentIds)->pluck('subject_id', 'student_id');
                    $observations = StudentSkillObservation::where('academic_year_id', $academicYear->id)
                        ->where('standard_id', (int) $request->standard_id)
                        ->where('division_id', (int) $request->division_id)
                        ->where('exam_master_id', $request->exam_master_id ?: 0)
                        ->whereIn('student_id', $studentIds)
                        ->get(['student_id', 'subject_id', 'observations', 'is_absent', 'overall_grade'])
                        ->keyBy(fn ($row) => $row->student_id . '|' . $row->subject_id);
                }
            }
        }

        $studentAverages = [];
        if ($students->isNotEmpty() && $skillSubjects->isNotEmpty()) {
            $gradeMap = ['C' => 1, 'B' => 2, 'B+' => 3, 'A' => 4, 'A+' => 5];
            foreach ($students as $student) {
                $grades = [];
                foreach ($skillSubjects as $sub) {
                    $key = $student->Studentid . '|' . $sub->id;
                    $obs = $observations[$key] ?? null;
                    if ($obs && !$obs->is_absent) {
                        $avg = $obs->overall_grade;
                        if (!$avg && !empty($obs->observations)) {
                            $avg = SkillCriteriaHelper::computeAverageGrade($obs->observations);
                        }
                        if ($avg) $grades[] = $avg;
                    }
                }

                if (count($grades) > 0) {
                    $sum = 0; $count = 0;
                    foreach ($grades as $g) {
                        if (isset($gradeMap[$g])) {
                            $sum += $gradeMap[$g];
                            $count++;
                        }
                    }
                    if ($count > 0) {
                        $avgValue = $sum / $count;
                        if ($avgValue >= 4.5) $studentAverages[$student->Studentid] = 'A+';
                        elseif ($avgValue >= 3.5) $studentAverages[$student->Studentid] = 'A';
                        elseif ($avgValue >= 2.5) $studentAverages[$student->Studentid] = 'B+';
                        elseif ($avgValue >= 1.5) $studentAverages[$student->Studentid] = 'B';
                        else $studentAverages[$student->Studentid] = 'C';
                    } else {
                        $studentAverages[$student->Studentid] = '-';
                    }
                } else {
                    $studentAverages[$student->Studentid] = '-';
                }
            }
        }

        $academicYearId = (int) $request->input('academic_year_id', 0);
        $standardId     = (int) $request->input('standard_id', 0);
        $divisionId     = (int) $request->input('division_id', 0);

        $classTeacherName = $this->resolveClassTeacherName($academicYearId, $standardId, $divisionId);
        $principalName    = $this->resolvePrincipalName();

        $payload = [
            'academicYears' => $academicYears, 'standards' => $standards, 'divisions' => $divisions, 'exams' => $exams,
            'students' => $students, 'skillSubjects' => $skillSubjects, 'allocations' => $allocations, 'observations' => $observations,
            'studentAverages' => $studentAverages,
            'classTeacherName' => $classTeacherName, 'principalName' => $principalName, 'erpError' => $erpError,
        ];

        if ($request->boolean('export')) return $payload;
        if ($request->boolean('print')) return view('student-skill-observations.print', $payload);

        return view('student-skill-observations.summary', $payload);
    }

    public function exportExcel(Request $request)
    {
        $request->merge(['export' => 1]);
        $payload = $this->summary($request);
        $payload['isExport']    = true;
        $payload['logoDataUri'] = $this->schoolLogoDataUri();

        $html = view('student-skill-observations.print', $payload)->render();
        $html = "\xEF\xBB\xBF" . $html;

        $filename = 'skill_observation_summary_' . date('Ymd_His') . '.xls';

        return response($html, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma'              => 'public',
        ]);
    }

    private function schoolLogoDataUri(): string
    {
        $candidates = [
            public_path('images/school-logo.png'),
            public_path('images/logo.png'),
            base_path('public/images/school-logo.png'),
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                $mime = function_exists('mime_content_type') ? (mime_content_type($path) ?: 'image/png') : 'image/png';
                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
            }
        }
        return '';
    }

    private function resolveSkillTeacherName(int $academicYearId, int $standardId, int $divisionId, int $examMasterId, int $subjectId): string
    {
        if ($academicYearId <= 0 || $standardId <= 0 || $divisionId <= 0 || $examMasterId <= 0 || $subjectId <= 0) return '';

        $name = DB::table('skill_teacher_class_allocations as stca')
            ->join('skill_teacher_subject_allocations as stsa', 'stsa.skill_teacher_class_allocation_id', '=', 'stca.id')
            ->join('users as u', 'u.id', '=', 'stca.user_id')
            ->where('stca.academic_year_id', $academicYearId)
            ->where('stca.standard_id', $standardId)
            ->where('stca.division_id', $divisionId)
            ->where('stsa.exam_master_id', $examMasterId)
            ->where('stsa.subject_id', $subjectId)
            ->orderByDesc('stca.id')
            ->value('u.name');

        return $name ? trim((string) $name) : '';
    }

    private function resolveClassTeacherName(int $academicYearId, int $standardId, int $divisionId): string
    {
        if ($standardId <= 0 || $divisionId <= 0) return '-';

        $matchesClassTeacher = function ($query) {
            $query->where(function ($q) {
                $q->whereRaw('UPPER(TRIM(designation_name)) LIKE ?', ['%CLASS TEACHER%'])
                  ->orWhereRaw('UPPER(TRIM(designation_code)) LIKE ?', ['%CLASS_TEACHER%'])
                  ->orWhereRaw('UPPER(TRIM(designation_code)) LIKE ?', ['%CLASS-TEACHER%']);
            });
        };

        try {
            $record = UserDesignation::query()
                ->with(['user', 'designation'])
                ->where('academic_year_id', $academicYearId)
                ->where('standard_id', $standardId)
                ->where('division_id', $divisionId)
                ->whereHas('designation', $matchesClassTeacher)
                ->orderByDesc('id')
                ->first();

            if ($record && $record->user && !empty($record->user->name)) return trim((string) $record->user->name);
        } catch (\Throwable $e) {
            Log::warning('resolveClassTeacherName (exact) failed: ' . $e->getMessage());
        }

        try {
            $record = UserDesignation::query()
                ->with(['user', 'designation'])
                ->where('standard_id', $standardId)
                ->where('division_id', $divisionId)
                ->whereHas('designation', $matchesClassTeacher)
                ->orderByDesc('id')
                ->first();

            if ($record && $record->user && !empty($record->user->name)) return trim((string) $record->user->name);
        } catch (\Throwable $e) {
            Log::warning('resolveClassTeacherName (fallback) failed: ' . $e->getMessage());
        }

        return '-';
    }

    private function resolvePrincipalName(): string
    {
        $tryExact = function (string $value) {
            try {
                $record = UserDesignation::query()
                    ->with(['user', 'designation'])
                    ->whereHas('designation', function ($q) use ($value) {
                        $q->where(function ($qq) use ($value) {
                            $qq->whereRaw('UPPER(TRIM(designation_name)) = ?', [$value])
                               ->orWhereRaw('UPPER(TRIM(designation_code)) = ?', [$value]);
                        });
                    })->orderByDesc('id')->first();

                return ($record && $record->user && !empty($record->user->name)) ? trim((string) $record->user->name) : null;
            } catch (\Throwable $e) {
                Log::warning("resolvePrincipalName (exact {$value}) failed: " . $e->getMessage());
                return null;
            }
        };

        $name = $tryExact('PRINCIPAL-PRI');
        if ($name) return $name;

        $name = $tryExact('PRINCIPAL-SEC');
        if ($name) return $name;

        try {
            $record = UserDesignation::query()
                ->with(['user', 'designation'])
                ->whereHas('designation', function ($q) {
                    $q->where(function ($qq) {
                        $qq->whereRaw('UPPER(TRIM(designation_name)) LIKE ?', ['%PRINCIPAL%'])
                           ->orWhereRaw('UPPER(TRIM(designation_code)) LIKE ?', ['%PRINCIPAL%']);
                    });
                })->orderByDesc('id')->first();

            if ($record && $record->user && !empty($record->user->name)) return trim((string) $record->user->name);
        } catch (\Throwable $e) {
            Log::warning('resolvePrincipalName (generic) failed: ' . $e->getMessage());
        }

        return '-';
    }
}