<?php

namespace App\Http\Controllers;

use App\Models\ExamStructure;
use App\Models\Subject;
use App\Models\Standard;
use App\Models\StandardWiseSubject;
use App\Models\AcademicYear;
use App\Helpers\ExamStructureHelper;
use App\Services\ExamStructureSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamStructureController extends Controller
{
    private const SKILL_SUBJECT_TYPE_IDS = [2, 3];

    private function getStandardSubjects(int $standardId)
    {
        return StandardWiseSubject::query()
            ->join('subjects as s', 's.id', '=', 'standard_wise_subjects.subject_id')
            ->where('standard_wise_subjects.standard_id', $standardId)
            ->where('standard_wise_subjects.is_active', 1)
            ->where('s.is_active', 1)
            ->whereNotIn('s.subject_type_id', self::SKILL_SUBJECT_TYPE_IDS)
            ->orderBy('standard_wise_subjects.sort_order')
            ->orderBy('standard_wise_subjects.id')
            ->select([
                's.id as subject_id',
                's.subject_name',
                's.subject_code',
                's.short_name',
                'standard_wise_subjects.sort_order',
            ])
            ->get();
    }

    /**
     * Column groupings based on standard and exam type to match Excel exactly.
     */
    private function getTableLayouts(string $standardKey): array
    {
        $std = strtoupper($standardKey);
        if ($std === '1ST') $std = 'FIRST';
        if ($std === '2ND') $std = 'SECOND';
        if ($std === '3RD') $std = 'THIRD';
        if ($std === '4TH') $std = 'FOURTH';
        if ($std === '5TH') $std = 'FIFTH';
        if ($std === '6TH') $std = 'SIXTH';
        if ($std === '7TH') $std = 'SEVENTH';
        if ($std === '8TH') $std = 'EIGHTH';

        $map = [
            'FIRST' => [
                'TERM 1' => ['sum_lbl' => 'Summative = 30 Marks', 'sum_cols' => ['theory', 'oral'], 'form_lbl' => 'Formative = 70 Marks', 'form_cols' => ['practical']],
                'TERM 2' => ['sum_lbl' => 'Summative = 70 Marks', 'sum_cols' => ['theory', 'oral'], 'form_lbl' => 'Formative = 30 Marks', 'form_cols' => ['practical']],
            ],
            'SECOND' => [
                'TERM 1' => ['sum_lbl' => 'Summative = 30 Marks', 'sum_cols' => ['theory', 'oral'], 'form_lbl' => 'Formative = 70 Marks', 'form_cols' => ['practical']],
                'TERM 2' => ['sum_lbl' => 'Summative = 70 Marks', 'sum_cols' => ['theory', 'oral'], 'form_lbl' => 'Formative = 30 Marks', 'form_cols' => ['practical']],
            ],
            'THIRD' => [
                'TERM 1' => ['sum_lbl' => 'Summative = 40 Marks', 'sum_cols' => ['theory', 'oral'], 'form_lbl' => 'Formative = 60 Marks', 'form_cols' => ['practical']],
                'TERM 2' => ['sum_lbl' => 'Summative = 40 Marks', 'sum_cols' => ['theory', 'oral'], 'form_lbl' => 'Formative = 60 Marks', 'form_cols' => ['practical']],
            ],
            'FOURTH' => [
                'TERM 1' => ['sum_lbl' => 'Summative = 40 Marks', 'sum_cols' => ['theory', 'oral'], 'form_lbl' => 'Formative = 60 Marks', 'form_cols' => ['practical']],
                'TERM 2' => ['sum_lbl' => 'Summative = 40 Marks', 'sum_cols' => ['theory', 'oral'], 'form_lbl' => 'Formative = 60 Marks', 'form_cols' => ['practical']],
            ],
            'FIFTH' => [
                'TERM 1' => ['sum_lbl' => 'Summative = 50 Marks', 'sum_cols' => ['theory', 'oral'], 'form_lbl' => 'Formative = 50 Marks', 'form_cols' => ['practical']],
                'TERM 2' => ['sum_lbl' => 'Summative = 50 Marks', 'sum_cols' => ['theory', 'oral'], 'form_lbl' => 'Formative = 50 Marks', 'form_cols' => ['practical']],
            ],
            'SIXTH' => [
                'TERM 1' => ['sum_lbl' => 'Summative = 50 Marks', 'sum_cols' => ['theory', 'oral'], 'form_lbl' => 'Formative = 50 Marks', 'form_cols' => ['practical']],
                'TERM 2' => ['sum_lbl' => 'Summative = 50 Marks', 'sum_cols' => ['theory', 'oral'], 'form_lbl' => 'Formative = 50 Marks', 'form_cols' => ['practical']],
            ],
            'SEVENTH' => [
                'TERM 1' => ['sum_lbl' => 'Summative = 40 Marks', 'sum_cols' => ['practical'], 'form_lbl' => 'Formative = 60 Marks', 'form_cols' => ['theory', 'oral']],
                'TERM 2' => ['sum_lbl' => 'Summative = 40 Marks', 'sum_cols' => ['practical'], 'form_lbl' => 'Formative = 60 Marks', 'form_cols' => ['theory', 'oral']],
            ],
            'EIGHTH' => [
                'TERM 1' => ['sum_lbl' => 'Summative = 60 Marks', 'sum_cols' => ['theory', 'oral'], 'form_lbl' => 'Formative = 40 Marks', 'form_cols' => ['practical']],
                'TERM 2' => ['sum_lbl' => 'Summative = 60 Marks', 'sum_cols' => ['theory', 'oral'], 'form_lbl' => 'Formative = 0 Marks', 'form_cols' => ['practical']],
            ],
        ];

        return $map[$std] ?? [];
    }

    private function buildGrouped($subjects, string $standardKey, array $showExamTypes): array
    {
        if ($subjects->isEmpty() || empty($showExamTypes)) return [];

        $existing = ExamStructure::where('standard_key', $standardKey)
            ->whereIn('exam_type', $showExamTypes)
            ->get();

        $existingIndex = [];
        foreach ($existing as $row) {
            $k = $row->exam_type . '|' . ExamStructureHelper::canonicalSubjectKey($row->subject_name);
            $existingIndex[$k] = $row;
        }

        $grouped = [];
        foreach ($showExamTypes as $et) {
            foreach ($subjects as $sub) {
                $k = $et . '|' . ExamStructureHelper::canonicalSubjectKey($sub->subject_name);
                $row = $existingIndex[$k] ?? null;

                $grouped[$et][] = (object) [
                    'subject_id'              => $sub->subject_id,
                    'subject_name'            => $sub->subject_name,
                    'subject_code'            => $sub->subject_code,
                    'exam_type'               => $et,
                    'theory_max_marks'        => (float) ($row->theory_max_marks        ?? 0),
                    'theory_passing_marks'    => (float) ($row->theory_passing_marks    ?? 0),
                    'oral_max_marks'          => (float) ($row->oral_max_marks          ?? 0),
                    'oral_passing_marks'      => (float) ($row->oral_passing_marks      ?? 0),
                    'practical_max_marks'     => (float) ($row->practical_max_marks     ?? 0),
                    'practical_passing_marks' => (float) ($row->practical_passing_marks ?? 0),
                    'total_max_marks'         => (float) ($row->total_max_marks         ?? 0),
                    'total_passing_marks'     => (float) ($row->total_passing_marks     ?? 0),
                    'passing_percentage'      => (float) ($row->passing_percentage      ?? 40),
                    'is_active'               => (bool)  ($row->is_active               ?? true),
                    'row_exists'              => $row !== null,
                ];
            }
        }

        return $grouped;
    }

    public function index(Request $request)
    {
        $academicYears = AcademicYear::where('is_active', 1)->orderByDesc('id')->get();
        $standards     = Standard::where('is_active', 1)->orderBy('display_order')->get();

        $academicYearId = $request->input('academic_year_id');
        $standardId     = $request->input('standard_id');
        $examTypeInput  = $request->input('exam_type');

        $examTypes = array_keys(ExamStructure::EXAM_TYPE_ORDER);

        $selectedStandard    = null;
        $selectedStandardKey = null;
        if (!empty($standardId)) {
            $selectedStandard = Standard::find($standardId);
            if ($selectedStandard) {
                $selectedStandardKey = ExamStructureHelper::getStandardSheetKey($selectedStandard->standard_name);
            }
        }

        $selectedExamType = null;
        if (!empty($examTypeInput)) {
            $et = strtoupper($examTypeInput);
            if (in_array($et, $examTypes, true)) {
                $selectedExamType = $et;
            }
        }

        $subjects = $standardId ? $this->getStandardSubjects((int) $standardId) : collect();
        $showExamTypes = $selectedExamType ? [$selectedExamType] : $examTypes;

        $grouped = [];
        $tableLayouts = [];

        if ($selectedStandardKey) {
            $grouped = $this->buildGrouped($subjects, $selectedStandardKey, $showExamTypes);
            $tableLayouts = $this->getTableLayouts($selectedStandardKey);
        }

        return view('exam-structures.index', compact(
            'academicYears', 'standards', 'examTypes',
            'academicYearId', 'standardId',
            'selectedStandard', 'selectedStandardKey', 'selectedExamType',
            'subjects', 'grouped', 'tableLayouts'
        ));
    }

    public function edit(Request $request, string $standardKey)
    {
        $standardKey = strtoupper($standardKey);

        $standard = null;
        foreach (Standard::where('is_active', 1)->get() as $s) {
            if (ExamStructureHelper::getStandardSheetKey($s->standard_name) === $standardKey) {
                $standard = $s;
                break;
            }
        }

        if (!$standard) {
            return redirect()->route('exam-structures.index')
                ->with('error', "Standard {$standardKey} not found.");
        }

        $examTypeFilter = $request->query('exam_type');
        $examTypes = array_keys(ExamStructure::EXAM_TYPE_ORDER);

        $showExamTypes = ($examTypeFilter && in_array(strtoupper($examTypeFilter), $examTypes, true))
            ? [strtoupper($examTypeFilter)]
            : $examTypes;

        $subjects = $this->getStandardSubjects((int) $standard->id);
        $grouped  = $this->buildGrouped($subjects, $standardKey, $showExamTypes);
        $tableLayouts = $this->getTableLayouts($standardKey);

        return view('exam-structures.edit', compact(
            'standard', 'standardKey', 'grouped', 'examTypes', 'examTypeFilter', 'tableLayouts'
        ));
    }

    public function update(Request $request, string $standardKey)
    {
        $standardKey = strtoupper($standardKey);
        $payload = $request->input('rows', []);

        $touchedExamTypes = [];
        foreach (array_keys($payload) as $et) {
            $et = strtoupper(trim((string) $et));
            if ($et !== '') $touchedExamTypes[] = $et;
        }

        DB::transaction(function () use ($payload, $standardKey) {
            foreach ($payload as $examType => $subjectRows) {
                $examType = strtoupper(trim((string) $examType));
                if ($examType === '') continue;

                foreach ($subjectRows as $subjectId => $fields) {
                    $subject = Subject::find((int) $subjectId);
                    if (!$subject) continue;

                    $subName = $subject->subject_name;
                    $canon   = ExamStructureHelper::canonicalSubjectKey($subName);

                    $existingRow = null;
                    foreach (ExamStructure::where('standard_key', $standardKey)
                                ->where('exam_type', $examType)->get() as $c) {
                        if (ExamStructureHelper::canonicalSubjectKey($c->subject_name) === $canon) {
                            $existingRow = $c;
                            break;
                        }
                    }

                    $componentFields = [
                        'theory_max_marks','theory_passing_marks',
                        'oral_max_marks','oral_passing_marks',
                        'practical_max_marks','practical_passing_marks',
                    ];

                    $data = [];
                    foreach ($componentFields as $f) {
                        if (array_key_exists($f, $fields)) {
                            $data[$f] = (float) $fields[$f];
                        } elseif ($existingRow) {
                            $data[$f] = (float) $existingRow->{$f};
                        } else {
                            $data[$f] = 0.0;
                        }
                    }

                    // ⭐ FORCE AUTO-CALCULATION FOR TOTAL MARKS
                    $data['total_max_marks'] = $data['theory_max_marks'] + $data['oral_max_marks'] + $data['practical_max_marks'];
                    $data['total_passing_marks'] = $data['theory_passing_marks'] + $data['oral_passing_marks'] + $data['practical_passing_marks'];

                    if (array_key_exists('passing_percentage', $fields)) {
                        $data['passing_percentage'] = (float) $fields['passing_percentage'];
                    } elseif ($existingRow) {
                        $data['passing_percentage'] = (float) $existingRow->passing_percentage;
                    } else {
                        $data['passing_percentage'] = 40.0;
                    }

                    $data['is_active'] = !empty($fields['is_active']);

                    if ($existingRow) {
                        $existingRow->update($data);
                    } else {
                        ExamStructure::create(array_merge($data, [
                            'standard_key'    => $standardKey,
                            'standard_name'   => $standardKey,
                            'exam_type'       => $examType,
                            'exam_type_order' => ExamStructure::EXAM_TYPE_ORDER[$examType] ?? 0,
                            'subject_name'    => $subName,
                            'subject_code'    => $subject->subject_code,
                            'subject_type'    => 'Academics',
                        ]));
                    }
                }
            }
        });

        $standardId = null;
        foreach (Standard::where('is_active', 1)->get() as $s) {
            if (ExamStructureHelper::getStandardSheetKey($s->standard_name) === $standardKey) {
                $standardId = (int) $s->id;
                break;
            }
        }

        if ($standardId) {
            /** @var ExamStructureSyncService $sync */
            $sync = app(ExamStructureSyncService::class);

            if (!empty($touchedExamTypes)) {
                foreach ($touchedExamTypes as $et) {
                    $sync->syncStandardFromDatabase($standardId, $et);
                }
            } else {
                $sync->syncStandardFromDatabase($standardId);
            }
        }

        if ($request->filled('_exam_type')) {
            return redirect()
                ->route('exam-structures.edit', [
                    'standardKey' => $standardKey,
                    'exam_type'   => $request->input('_exam_type'),
                ])
                ->with('success', 'Exam structure updated. Totals auto-calculated.');
        }

        return redirect()
            ->route('exam-structures.edit', $standardKey)
            ->with('success', 'Exam structure updated. Totals auto-calculated.');
    }
}