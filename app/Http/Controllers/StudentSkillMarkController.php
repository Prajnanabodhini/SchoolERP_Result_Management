<?php

namespace App\Http\Controllers;

use App\Helpers\StudentHelper;
use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\ExamMaster;
use App\Models\Standard;
use App\Models\StudentSkillMark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class StudentSkillMarkController extends Controller
{
    private function getSkillStructure($standardId, string $slot): array
    {
        $standard = Standard::find($standardId);
        if (!$standard) return [];

        $stdName = strtoupper((string) $standard->standard_name);
        
        // Matches "1", "2", "FIRST", "SECOND", "1ST", "2ND"
        $isPrimary = (bool) preg_match('/\b(1|2|I|II|FIRST|SECOND|1ST|2ND)\b/i', $stdName);
        
        // ADDED: Detect Tenth Standard
        $isTenth = (bool) preg_match('/\b(10|X|TENTH)\b/i', $stdName);

        if ($slot === 'S3') {
            return [
                'label' => 'PHYSICAL EDUCATION & HEALTH',
                'categories' => [
                    'Health' => [
                        'Oral' => 10,
                        'PT Practical Exam' => 30,
                        'Uniform' => 20,
                        'PT Periods' => 10,
                    ],
                    'Upasana' => [
                        'Attentiveness, Learning, Chanting' => 10,
                    ],
                    'Chintanika' => [
                        'Participation, Notes in Diary / Review Response' => 20,
                    ],
                ],
            ];
        }

        if ($slot === 'S4') {
            return [
                'label' => 'OTHER (LIBRARY + SANSKRIT BHARTI)',
                'categories' => [
                    'Library' => [
                        'Reading Book' => 15,
                        'Presenting Review' => 15,
                        'Notes in Diary' => 10,
                    ],
                    'Sanskrit Bharti' => [
                        'Written' => 20,
                        'Oral' => 20,
                        'Text Book' => 20,
                    ],
                ],
            ];
        }

        if ($slot === 'S2') {
            if ($isPrimary) {
                return [
                    'label' => 'WORK EXP (Craft + Abacus)',
                    'categories' => [
                        'Craft' => [
                            'NB' => 20,
                            'Exam' => 30,
                        ],
                        'Abacus' => [
                            'NB' => 20,
                            'Handling' => 10,
                            'Exam' => 20,
                        ],
                    ],
                ];
            }

            // ADDED: For Tenth standard, remove Abacus entirely. Total becomes 70.
            if ($isTenth) {
                return [
                    'label' => 'WORK EXP (Computer + Robotics)',
                    'categories' => [
                        'Computer' => [
                            'NB' => 10,
                            'Practical' => 15,
                            'W' => 10,
                        ],
                        'Robotics' => [
                            'NB' => 10,
                            'Practical' => 15,
                            'W' => 10,
                        ],
                    ],
                ];
            }

            return [
                'label' => 'WORK EXP (Abacus + Computer + Robotics)',
                'categories' => [
                    'Abacus' => [
                        'NB' => 10,
                        'Exam' => 20,
                    ],
                    'Computer' => [
                        'NB' => 10,
                        'Practical' => 15,
                        'W' => 10,
                    ],
                    'Robotics' => [
                        'NB' => 10,
                        'Practical' => 15,
                        'W' => 10,
                    ],
                ],
            ];
        }

        // S1 Logic
        if ($isPrimary) {
            return [
                'label' => 'ARTS (Music + Dance + Story + Drawing)',
                'categories' => [
                    'Music' => [
                        'Practical' => 20,
                    ],
                    'Dance' => [
                        'Practical' => 20,
                    ],
                    'Story' => [
                        'Narration' => 10,
                    ],
                    'Drawing' => [
                        'Book' => 20,
                        'Exam' => 30,
                    ],
                ],
            ];
        }

        return [
            'label' => 'ARTS (Drawing + A/D/M/T)',
            'categories' => [
                'Drawing' => [
                    'Book' => 20,
                    'Practical' => 15,
                    'Exam' => 15,
                ],
                'A/D/M/T' => [
                    'W' => 10,
                    'NB' => 20,
                    'Practical' => 20,
                ],
            ],
        ];
    }

    private function fetchClassStudents($yearId, $standardId, $divisionId)
    {
        $students = StudentHelper::getStudentsDirectERPSlim($yearId, $standardId, $divisionId);

        return $students
            ->sortBy(function ($student) {
                $roll = trim((string) ($student->rollno ?? $student->RollNo ?? ''));
                return $roll === '' ? PHP_INT_MAX : (int) $roll;
            })
            ->values();
    }

    public function index(Request $request)
    {
        $academicYears = AcademicYear::select('id', 'year_name')->orderByDesc('year_name')->get();
        $standards = Standard::select('id', 'standard_name', 'display_order')->where('is_active', 1)->orderBy('display_order')->get();
        $divisions = Division::select('id', 'division_name')->where('is_active', 1)->orderBy('division_name')->get();

        $examQuery = ExamMaster::select('id', 'exam_name', 'academic_year_id', 'standard_id');
        if ($request->filled('academic_year_id')) $examQuery->where('academic_year_id', $request->academic_year_id);
        if ($request->filled('standard_id')) $examQuery->where('standard_id', $request->standard_id);
        $exams = $examQuery->orderBy('exam_name')->get();

        $students = collect();
        $skillStructure = [];
        $existingMarks = [];
        $teacherName = '';
        $skillLabel = '';
        $academicYear = null;
        $examMaster = null;
        $standard = null;
        $division = null;

        $selectedSkill = (string) $request->input('skill', 'S1');
        if (!in_array($selectedSkill, ['S1', 'S2', 'S3', 'S4'], true)) {
            $selectedSkill = 'S1';
        }

        if ($request->filled(['academic_year_id', 'exam_master_id', 'division_id', 'standard_id'])) {
            $academicYear = AcademicYear::find($request->academic_year_id);
            $examMaster = ExamMaster::find($request->exam_master_id);
            $standard = Standard::find($request->standard_id);
            $division = Division::find($request->division_id);

            if (
                $academicYear && $examMaster && $standard && $division
                && (int) $examMaster->academic_year_id === (int) $academicYear->id
                && (int) $examMaster->standard_id === (int) $standard->id
            ) {
                $struct = $this->getSkillStructure($standard->id, $selectedSkill);
                $skillStructure = $struct['categories'] ?? [];
                $skillLabel = $struct['label'] ?? '';

                $yearId = $academicYear->old_year_id ?? substr((string) $academicYear->year_name, 0, 4);
                $students = $this->fetchClassStudents($yearId, $standard->id, $division->id);

                $allowedNames = [];
                foreach ($skillStructure as $criteria) {
                    foreach ($criteria as $criterionName => $maxMarks) {
                        $allowedNames[] = $criterionName;
                    }
                }

                $marksQuery = StudentSkillMark::select(
                        'student_id', 'category', 'subject_name',
                        'marks_obtained', 'is_absent', 'skill_teacher_name'
                    )
                    ->where('academic_year_id', $academicYear->id)
                    ->where('exam_master_id', $examMaster->id)
                    ->where('standard_id', $standard->id)
                    ->where('division_id', $division->id)
                    ->where('skill_slot', $selectedSkill)
                    ->whereIn('subject_name', array_values(array_unique($allowedNames)))
                    ->get();

                foreach ($marksQuery as $mark) {
                    $existingMarks[$mark->student_id][$mark->category][$mark->subject_name] = [
                        'marks_obtained' => $mark->marks_obtained,
                        'is_absent' => $mark->is_absent,
                    ];
                    if ($mark->skill_teacher_name) {
                        $teacherName = $mark->skill_teacher_name;
                    }
                }
            }
        }

        return view('student-skill-marks.index', compact(
            'academicYears', 'standards', 'divisions', 'exams',
            'students', 'skillStructure', 'existingMarks', 'teacherName',
            'selectedSkill', 'skillLabel', 'academicYear', 'examMaster',
            'standard', 'division'
        ));
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => ['required', 'integer'],
            'standard_id' => ['required', 'integer'],
            'division_id' => ['required', 'integer'],
            'exam_master_id' => ['required', 'integer'],
            'skill' => ['required', 'in:S1,S2,S3,S4'],
            'marks' => ['required', 'array'],
            'student_names' => ['sometimes', 'array'],
            'roll_nos' => ['sometimes', 'array'],
            'gr_nos' => ['sometimes', 'array'],
            'is_absent' => ['sometimes', 'array'],
            'skill_teacher_name' => ['nullable', 'string', 'max:255'],
        ]);

        $academicYearId = (int) $validated['academic_year_id'];
        $standardId = (int) $validated['standard_id'];
        $divisionId = (int) $validated['division_id'];
        $examMasterId = (int) $validated['exam_master_id'];
        $selectedSkill = (string) $validated['skill'];
        $skillId = (int) str_replace('S', '', $selectedSkill);
        $teacherName = trim((string) $request->input('skill_teacher_name', ''));
        $now = now();

        $academicYear = AcademicYear::findOrFail($academicYearId);
        $standard = Standard::findOrFail($standardId);
        $division = Division::findOrFail($divisionId);
        $examMaster = ExamMaster::findOrFail($examMasterId);

        if (
            (int) $examMaster->academic_year_id !== $academicYearId
            || (int) $examMaster->standard_id !== $standardId
        ) {
            throw ValidationException::withMessages([
                'exam_master_id' => 'The selected exam does not belong to the selected academic year and standard.',
            ]);
        }

        $studentIds = array_values(array_unique(array_map('intval', array_keys($validated['marks']))));

        $struct = $this->getSkillStructure($standardId, $selectedSkill);
        $skillStructure = $struct['categories'] ?? [];

        if (empty($skillStructure)) {
            return redirect()->back()->withInput()->with('error', 'No criteria are configured for this skill.');
        }

        $categoryFirstLayout = true;
        $allRows = [];

        try {
            foreach ($validated['marks'] as $studentIdKey => $submittedCategories) {
                $studentId = (int) $studentIdKey;

                if ($studentId <= 0 || !is_array($submittedCategories)) continue;

                $studentName = trim((string) $request->input("student_names.{$studentId}", ''));
                $rollNo = trim((string) $request->input("roll_nos.{$studentId}", ''));
                $grNo = trim((string) $request->input("gr_nos.{$studentId}", ''));
                $absenceInput = $request->input("is_absent.{$studentId}", []);

                $groupAbsences = [];
                $legacyStudentAbsent = false;
                if (is_array($absenceInput)) {
                    $groupAbsences = $absenceInput;
                } else {
                    $legacyStudentAbsent = ((int) $absenceInput === 1);
                }

                $studentTotal = 0;
                $totalMax = 0;
                $hasAnyMark = false;
                $studentRows = [];
                $groupStates = [];

                foreach ($skillStructure as $category => $criteria) {
                    foreach ($criteria as $criterionName => $configuredMax) {
                        $maxMarks = (int) $configuredMax;
                        $data = $submittedCategories[$category][$criterionName] ?? [];
                        if (!is_array($data)) $data = [];

                        $groupName = $categoryFirstLayout ? $category : $criterionName;
                        $isAbsent = $legacyStudentAbsent || ((int) ($groupAbsences[$groupName] ?? 0) === 1);
                        $rawMarks = $data['obtained'] ?? null;
                        $marksObtained = null;

                        $groupStates[$groupName] = $isAbsent;
                        $totalMax += $maxMarks;

                        if ($isAbsent) {
                            $marksObtained = 0;
                            $hasAnyMark = true;
                        } elseif ($rawMarks !== null && $rawMarks !== '') {
                            if (!is_numeric($rawMarks) || (float) $rawMarks < 0 || (float) $rawMarks > $maxMarks) {
                                return redirect()->back()->withInput()->with('error', "Invalid marks for {$criterionName}. Enter a value between 0 and {$maxMarks}.");
                            }
                            $marksObtained = (int) $rawMarks;
                            $studentTotal += $marksObtained;
                            $hasAnyMark = true;
                        }

                        $studentRows[] = [
                            'academic_year_id' => $academicYearId, 'standard_id' => $standardId,
                            'division_id' => $divisionId, 'exam_master_id' => $examMasterId,
                            'student_id' => $studentId, 'student_name' => $studentName,
                            'roll_no' => $rollNo !== '' ? $rollNo : null, 'gr_no' => $grNo !== '' ? $grNo : null,
                            'skill_slot' => $selectedSkill, 'skill_id' => $skillId,
                            'category' => $category, 'subject_name' => $criterionName,
                            'max_marks' => $maxMarks, 'marks_obtained' => $marksObtained,
                            'is_absent' => $isAbsent ? 1 : 0, 'total_marks' => 0, 'grade' => null,
                            'skill_teacher_name' => $teacherName !== '' ? $teacherName : null,
                            'created_at' => $now, 'updated_at' => $now,
                        ];
                    }
                }

                if (!$hasAnyMark) continue;

                $activeGroupCount = count($groupStates);
                $absentGroupCount = count(array_filter($groupStates));
                $allGroupsAbsent = ($activeGroupCount > 0 && $absentGroupCount === $activeGroupCount);

                $percentage = $totalMax > 0 ? ($studentTotal / $totalMax) * 100 : 0;
                $grade = $allGroupsAbsent ? 'AB' : $this->gradeFromPercentage($percentage);

                foreach ($studentRows as &$row) {
                    $row['total_marks'] = $studentTotal;
                    $row['grade'] = $grade;
                }
                unset($row);

                $allRows = array_merge($allRows, $studentRows);
            }

            DB::transaction(function () use ($academicYearId, $standardId, $divisionId, $examMasterId, $selectedSkill, $studentIds, $allRows) {
                if (!empty($studentIds)) {
                    StudentSkillMark::where('academic_year_id', $academicYearId)
                        ->where('standard_id', $standardId)
                        ->where('division_id', $divisionId)
                        ->where('exam_master_id', $examMasterId)
                        ->where('skill_slot', $selectedSkill)
                        ->whereIn('student_id', $studentIds)
                        ->delete();
                }
                foreach (array_chunk($allRows, 500) as $chunk) {
                    StudentSkillMark::insert($chunk);
                }
            });

            if (empty($allRows)) {
                return redirect()->back()->with('success', 'No marks were entered. Existing marks for these students and this skill were cleared.');
            }

            return redirect()->back()->with('success', 'Skill Marks Saved Successfully.');

        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withInput()->with('error', 'Unable to save skill marks. Please check the application log.');
        }
    }

    public function autoSave(Request $request)
    {
        try {
            $validated = $request->validate([
                'academic_year_id' => ['required', 'integer'],
                'standard_id' => ['required', 'integer'],
                'division_id' => ['required', 'integer'],
                'exam_master_id' => ['required', 'integer'],
                'skill' => ['required', 'in:S1,S2,S3,S4'],
                'student_id' => ['required', 'integer'],
                'marks' => ['required', 'array'],
                'student_name' => ['nullable', 'string'],
                'roll_no' => ['nullable', 'string'],
                'gr_no' => ['nullable', 'string'],
                'is_absent' => ['sometimes', 'array'],
                'skill_teacher_name' => ['nullable', 'string', 'max:255'],
            ]);

            $academicYearId = (int) $validated['academic_year_id'];
            $standardId = (int) $validated['standard_id'];
            $divisionId = (int) $validated['division_id'];
            $examMasterId = (int) $validated['exam_master_id'];
            $selectedSkill = (string) $validated['skill'];
            $skillId = (int) str_replace('S', '', $selectedSkill);
            $studentId = (int) $validated['student_id'];
            $teacherName = trim((string) $request->input('skill_teacher_name', ''));
            $now = now();

            $struct = $this->getSkillStructure($standardId, $selectedSkill);
            $skillStructure = $struct['categories'] ?? [];

            if (empty($skillStructure)) {
                return response()->json(['success' => false, 'message' => 'No criteria configured.']);
            }

            $categoryFirstLayout = true;
            $allRows = [];

            $submittedCategories = $validated['marks'];
            $studentName = trim((string) $request->input("student_name", ''));
            $rollNo = trim((string) $request->input("roll_no", ''));
            $grNo = trim((string) $request->input("gr_no", ''));
            $absenceInput = $request->input("is_absent", []);

            $groupAbsences = [];
            $legacyStudentAbsent = false;
            if (is_array($absenceInput)) {
                $groupAbsences = $absenceInput;
            } else {
                $legacyStudentAbsent = ((int) $absenceInput === 1);
            }

            $studentTotal = 0;
            $totalMax = 0;
            $hasAnyMark = false;
            $groupStates = [];

            foreach ($skillStructure as $category => $criteria) {
                foreach ($criteria as $criterionName => $configuredMax) {
                    $maxMarks = (int) $configuredMax;
                    $data = $submittedCategories[$category][$criterionName] ?? [];
                    if (!is_array($data)) $data = [];

                    $groupName = $categoryFirstLayout ? $category : $criterionName;
                    $isAbsent = $legacyStudentAbsent || ((int) ($groupAbsences[$groupName] ?? 0) === 1);
                    $rawMarks = $data['obtained'] ?? null;
                    $marksObtained = null;

                    $groupStates[$groupName] = $isAbsent;
                    $totalMax += $maxMarks;

                    if ($isAbsent) {
                        $marksObtained = 0;
                        $hasAnyMark = true;
                    } elseif ($rawMarks !== null && $rawMarks !== '') {
                        $marksObtained = (int) $rawMarks;
                        $studentTotal += $marksObtained;
                        $hasAnyMark = true;
                    }

                    $allRows[] = [
                        'academic_year_id' => $academicYearId, 'standard_id' => $standardId,
                        'division_id' => $divisionId, 'exam_master_id' => $examMasterId,
                        'student_id' => $studentId, 'student_name' => $studentName,
                        'roll_no' => $rollNo !== '' ? $rollNo : null, 'gr_no' => $grNo !== '' ? $grNo : null,
                        'skill_slot' => $selectedSkill, 'skill_id' => $skillId,
                        'category' => $category, 'subject_name' => $criterionName,
                        'max_marks' => $maxMarks, 'marks_obtained' => $marksObtained,
                        'is_absent' => $isAbsent ? 1 : 0, 'total_marks' => 0, 'grade' => null,
                        'skill_teacher_name' => $teacherName !== '' ? $teacherName : null,
                        'created_at' => $now, 'updated_at' => $now,
                    ];
                }
            }

            if (!$hasAnyMark) {
                StudentSkillMark::where('academic_year_id', $academicYearId)
                    ->where('standard_id', $standardId)
                    ->where('division_id', $divisionId)
                    ->where('exam_master_id', $examMasterId)
                    ->where('skill_slot', $selectedSkill)
                    ->where('student_id', $studentId)
                    ->delete();
                return response()->json(['success' => true, 'total' => 0, 'grade' => '-']);
            }

            $activeGroupCount = count($groupStates);
            $absentGroupCount = count(array_filter($groupStates));
            $allGroupsAbsent = ($activeGroupCount > 0 && $absentGroupCount === $activeGroupCount);

            $percentage = $totalMax > 0 ? ($studentTotal / $totalMax) * 100 : 0;
            $grade = $allGroupsAbsent ? 'AB' : $this->gradeFromPercentage($percentage);

            foreach ($allRows as &$row) {
                $row['total_marks'] = $studentTotal;
                $row['grade'] = $grade;
            }
            unset($row);

            DB::transaction(function () use ($academicYearId, $standardId, $divisionId, $examMasterId, $selectedSkill, $studentId, $allRows) {
                StudentSkillMark::where('academic_year_id', $academicYearId)
                    ->where('standard_id', $standardId)
                    ->where('division_id', $divisionId)
                    ->where('exam_master_id', $examMasterId)
                    ->where('skill_slot', $selectedSkill)
                    ->where('student_id', $studentId)
                    ->delete();
                StudentSkillMark::insert($allRows);
            });

            return response()->json([
                'success' => true,
                'total' => $allGroupsAbsent ? 'AB' : $studentTotal,
                'grade' => $grade
            ]);

        } catch (\Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'Auto-save failed.'], 500);
        }
    }

    private function buildPrintData(Request $request): array
    {
        $academicYear = AcademicYear::findOrFail($request->input('academic_year_id'));
        $standard = Standard::findOrFail($request->input('standard_id'));
        $division = Division::findOrFail($request->input('division_id'));
        $examMaster = ExamMaster::findOrFail($request->input('exam_master_id'));

        if (
            (int) $examMaster->academic_year_id !== (int) $academicYear->id
            || (int) $examMaster->standard_id !== (int) $standard->id
        ) {
            abort(422, 'The selected exam does not match the academic year and standard.');
        }

        $selectedSkill = (string) $request->input('skill', 'S1');
        if (!in_array($selectedSkill, ['S1', 'S2', 'S3', 'S4'], true)) {
            $selectedSkill = 'S1';
        }

        $struct = $this->getSkillStructure($standard->id, $selectedSkill);
        $skillStructure = $struct['categories'] ?? [];
        $skillLabel = $struct['label'] ?? '';

        $allowedNames = [];
        foreach ($skillStructure as $criteria) {
            foreach ($criteria as $criterionName => $maxMarks) {
                $allowedNames[] = $criterionName;
            }
        }

        $marksQuery = StudentSkillMark::select(
                'student_id', 'category', 'subject_name', 'marks_obtained',
                'is_absent', 'total_marks', 'grade', 'skill_teacher_name'
            )
            ->where('academic_year_id', $academicYear->id)
            ->where('exam_master_id', $examMaster->id)
            ->where('standard_id', $standard->id)
            ->where('division_id', $division->id)
            ->where('skill_slot', $selectedSkill)
            ->whereIn('subject_name', array_values(array_unique($allowedNames)))
            ->get();

        $existingMarks = [];
        $studentTotals = [];
        $studentGrades = [];
        $teacherName = '';

        foreach ($marksQuery as $mark) {
            $existingMarks[$mark->student_id][$mark->category][$mark->subject_name] = [
                'marks_obtained' => $mark->marks_obtained,
                'is_absent' => $mark->is_absent,
            ];

            $studentTotals[$mark->student_id] = $mark->total_marks;
            $studentGrades[$mark->student_id] = $mark->grade ?: '-';

            if ($mark->skill_teacher_name) {
                $teacherName = $mark->skill_teacher_name;
            }
        }

        $yearId = $academicYear->old_year_id ?? substr((string) $academicYear->year_name, 0, 4);
        $students = $this->fetchClassStudents($yearId, $standard->id, $division->id);

        $skillSubjects = collect();
        foreach ($skillStructure as $category => $criteria) {
            foreach ($criteria as $criterionName => $maxMarks) {
                $skillSubjects->push((object) [
                    'subject_name' => $criterionName,
                    'max_marks' => $maxMarks,
                    'category' => $category,
                ]);
            }
        }

        $classTeacherName = $this->resolveClassTeacherName(
            (int) $academicYear->id, (int) $standard->id, (int) $division->id
        );
        $principalName = $this->resolvePrincipalName();
        $logoDataUri = $this->schoolLogoDataUri();

        return compact(
            'academicYear', 'standard', 'division', 'examMaster',
            'students', 'skillStructure', 'skillSubjects', 'existingMarks',
            'studentTotals', 'studentGrades', 'teacherName', 'classTeacherName',
            'principalName', 'logoDataUri', 'selectedSkill', 'skillLabel'
        );
    }

    public function print(Request $request)
    {
        return view('student-skill-marks.print', $this->buildPrintData($request));
    }

    public function exportExcel(Request $request)
    {
        $data = $this->buildPrintData($request);
        $data['isExport'] = true;

        $html = view('student-skill-marks.print', $data)->render();
        $html = "\xEF\xBB\xBF" . $html;
        $standardName = preg_replace('/[^A-Za-z0-9_-]+/', '_', (string) ($data['standard']->standard_name ?? 'STD'));
        $filename = 'skill_marks_' . $standardName . '_' . $data['selectedSkill'] . '_' . date('Ymd_His') . '.xls';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function gradeFromPercentage($percentage): string
    {
        $percentage = (float) $percentage;
        if ($percentage >= 91) return 'A1';
        if ($percentage >= 81) return 'A2';
        if ($percentage >= 71) return 'B1';
        if ($percentage >= 61) return 'B2';
        if ($percentage >= 51) return 'C1';
        if ($percentage >= 41) return 'C2';
        if ($percentage >= 33) return 'D';
        if ($percentage >= 21) return 'E1';
        if ($percentage >= 1) return 'E2';
        return 'F';
    }

    private function resolveClassTeacherName(int $academicYearId, int $standardId, int $divisionId): string
    {
        if ($standardId <= 0 || $divisionId <= 0) return '-';

        if (Schema::hasTable('class_teachers') && Schema::hasTable('users')) {
            $query = DB::table('class_teachers as ct')
                ->join('users as u', 'u.id', '=', 'ct.user_id')
                ->where('ct.standard_id', $standardId)
                ->where('ct.division_id', $divisionId);

            if ($academicYearId > 0 && Schema::hasColumn('class_teachers', 'academic_year_id')) {
                $query->where('ct.academic_year_id', $academicYearId);
            }

            $name = $query->value('u.name');
            if (!empty($name)) return trim((string) $name);
        }

        if (Schema::hasTable('teacher_class_allocations') && Schema::hasTable('users')) {
            $query = DB::table('teacher_class_allocations as tca')
                ->join('users as u', 'u.id', '=', 'tca.user_id')
                ->where('tca.standard_id', $standardId)
                ->where('tca.division_id', $divisionId);

            if ($academicYearId > 0 && Schema::hasColumn('teacher_class_allocations', 'academic_year_id')) {
                $query->where('tca.academic_year_id', $academicYearId);
            }

            $name = $query->value('u.name');
            if (!empty($name)) return trim((string) $name);
        }

        return '-';
    }

    private function resolvePrincipalName(): string
    {
        if (Schema::hasTable('principals')) {
            $query = DB::table('principals');
            if (Schema::hasColumn('principals', 'is_active')) {
                $query->where('is_active', 1);
            }
            $name = $query->orderByDesc('id')->value('name');
            if (!empty($name)) return trim((string) $name);
        }

        if (Schema::hasTable('users')) {
            $query = DB::table('users');
            if (Schema::hasColumn('users', 'role')) {
                $query->whereRaw("LOWER(TRIM(role)) IN ('principal', 'admin-principal')");
            } elseif (Schema::hasColumn('users', 'role_id') && Schema::hasTable('roles')) {
                $query->join('roles', 'roles.id', '=', 'users.role_id')
                    ->whereRaw("LOWER(TRIM(roles.name)) = 'principal'");
            } else {
                return '-';
            }

            $name = $query->orderByDesc('users.id')->value('users.name');
            if (!empty($name)) return trim((string) $name);
        }

        $configured = config('school.principal_name');
        return !empty($configured) ? trim((string) $configured) : '-';
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
                $mime = function_exists('mime_content_type')
                    ? (mime_content_type($path) ?: 'image/png')
                    : 'image/png';
                $contents = file_get_contents($path);
                if ($contents !== false) {
                    return 'data:' . $mime . ';base64,' . base64_encode($contents);
                }
            }
        }

        return '';
    }
}