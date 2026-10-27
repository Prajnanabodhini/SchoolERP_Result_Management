<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

use App\Models\ExamMaster;
use App\Models\ExamMasterSubject;
use App\Models\Standard;
use App\Models\AcademicYear;
use App\Models\ExamSubject;
use App\Models\Division;

use App\Helpers\ExamStructureHelper;
use App\Helpers\AllocationStatusHelper;
use App\Helpers\StudentHelper;

class ExamMasterController extends Controller
{
    private const SKILL_SUBJECT_TYPE_IDS = [2, 3];

    /* ================================================================
     | DELEGATED HELPERS
     * ================================================================ */
    private function getHardcodedExamStructure(): array
    {
        return ExamStructureHelper::getHardcodedExamStructure();
    }

    private function getStandardSheetKey(string $value): string
    {
        return ExamStructureHelper::getStandardSheetKey($value);
    }

    private function findSheetForStandard(string $standardName): ?array
    {
        return ExamStructureHelper::findSheetForStandard($standardName);
    }

    private function canonicalSubjectKey(string $value): string
    {
        return ExamStructureHelper::canonicalSubjectKey($value);
    }

    private function matchExcelSubject(string $dbName, string $examType, array $sheet): ?array
    {
        return ExamStructureHelper::matchExcelSubject($dbName, $examType, $sheet);
    }

    private function normalizeExamType($value): string
    {
        return ExamStructureHelper::normalizeExamType($value);
    }

    private function getPassingPercentage($standardId): float
    {
        return ExamStructureHelper::getPassingPercentage((int) $standardId);
    }

    /* ================================================================
     | MISC HELPERS
     * ================================================================ */
    private function isTermExam(string $examName): bool
    {
        $e = strtoupper(trim($examName));
        return str_starts_with($e,'TERM 1') || str_starts_with($e,'TERM 2');
    }

    private function getStandardSubjects(int $standardId)
    {
        return DB::table('standard_wise_subjects as sws')
            ->join('subjects as s','s.id','=','sws.subject_id')
            ->where('sws.standard_id',$standardId)
            ->where('sws.is_active',1)
            ->where('s.is_active',1)
            ->whereNotIn('s.subject_type_id', self::SKILL_SUBJECT_TYPE_IDS)
            ->orderBy('sws.sort_order')
            ->orderBy('s.id')
            ->select([
                's.id as subject_id','s.subject_name','s.subject_code','s.short_name',
                'sws.id as standard_wise_subject_id','sws.sort_order','sws.is_optional',
            ])
            ->get();
    }

    private function deriveComponentFlagsFromSheet(?array $sheet, string $examType): array
    {
        $hasTheory    = false;
        $hasOral      = false;
        $hasPractical = false;

        if ($sheet) {
            $rows = $sheet['by_exam_type'][$examType] ?? [];

            foreach ($rows as $r) {
                if ((float) ($r['theory_max_marks']    ?? 0) > 0) $hasTheory    = true;
                if ((float) ($r['oral_max_marks']      ?? 0) > 0) $hasOral      = true;
                if ((float) ($r['practical_max_marks'] ?? 0) > 0) $hasPractical = true;
            }
        }

        if (!$hasTheory && !$hasOral && !$hasPractical) {
            $hasTheory = true;
        }

        return [
            'has_theory'    => $hasTheory    ? 1 : 0,
            'has_oral'      => $hasOral      ? 1 : 0,
            'has_practical' => $hasPractical ? 1 : 0,
        ];
    }

    private function syncStudentMarks(int $examMasterId, int $standardId): void
    {
        try {
            Artisan::call('marks:fix-max-marks', [
                '--exam-id'     => $examMasterId,
                '--standard-id' => $standardId,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /* ================================================================
     | INDEX
     * ================================================================ */
    public function index(Request $request)
    {
        $academicYearId = $request->input('academic_year_id');
        $standardId     = $request->input('standard_id');
        $divisionId     = $request->input('division_id');
        $examId         = $request->input('exam_master_id');

        $examMasters = ExamMaster::with(['standard','academicYear'])
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->when($standardId,     fn ($q) => $q->where('standard_id', $standardId))
            ->when($examId,         fn ($q) => $q->where('id', $examId))
            ->when($divisionId, function ($q) use ($divisionId) {
                $q->whereIn('id', function ($sub) use ($divisionId) {
                    $sub->select('exam_master_id')
                        ->from('teacher_marks_status')
                        ->where('division_id', (int) $divisionId)
                        ->whereNotNull('teacher_subject_allocation_id');
                });
            })
            ->orderByDesc('id')
            ->get();

        $examFilterQuery = ExamMaster::query()->where('is_active', 1);

        if (!empty($academicYearId)) $examFilterQuery->where('academic_year_id', (int) $academicYearId);
        if (!empty($standardId))     $examFilterQuery->where('standard_id', (int) $standardId);
        if (!empty($divisionId)) {
            $examFilterQuery->whereIn('id', function ($sub) use ($divisionId) {
                $sub->select('exam_master_id')
                    ->from('teacher_marks_status')
                    ->where('division_id', (int) $divisionId)
                    ->whereNotNull('teacher_subject_allocation_id');
            });
        }

        $examOptions = $examFilterQuery->orderByDesc('id')->get(['id', 'exam_name']);

        $academicYears = AcademicYear::where('is_active', 1)->orderByDesc('id')->get();
        $standards     = Standard::where('is_active', 1)->orderBy('display_order')->get();
        $divisions     = Division::where('is_active', 1)->orderBy('division_name')->get();

        $examIds       = $examMasters->pluck('id')->toArray();
        $pendingCounts = AllocationStatusHelper::pendingCountForExams($examIds);
        $breakdowns    = AllocationStatusHelper::pendingBreakdownForExams($examIds);

        foreach ($examMasters as $exam) {
            $validDivisions = StudentHelper::getERPDivisionsForStandard(
                (int) $exam->academic_year_id,
                (int) $exam->standard_id
            );

            if ($validDivisions->isEmpty()) continue;

            $validNames = $validDivisions
                ->pluck('division_name')
                ->map(fn ($v) => strtoupper(trim((string) $v)))
                ->all();

            $filtered = [];
            foreach (($breakdowns[$exam->id] ?? []) as $divName => $count) {
                if (in_array(strtoupper(trim((string) $divName)), $validNames, true)) {
                    $filtered[$divName] = $count;
                }
            }

            $breakdowns[$exam->id]    = $filtered;
            $pendingCounts[$exam->id] = array_sum($filtered);
        }

        return view('exam-masters.index', compact(
            'examMasters','academicYears','standards','divisions','examOptions',
            'academicYearId','standardId','divisionId','examId',
            'pendingCounts','breakdowns'
        ));
    }

    /* ================================================================
     | CREATE
     * ================================================================ */
    public function create()
    {
        $academicYears    = AcademicYear::where('is_active',1)->orderByDesc('id')->get();
        $standards        = Standard::where('is_active',1)->orderBy('display_order')->get();
        $nextDisplayOrder = (ExamMaster::max('display_order') ?? 0) + 1;

        return view('exam-masters.create', compact('academicYears','standards','nextDisplayOrder'));
    }

    /* ================================================================
     | STORE
     * ================================================================ */
    public function store(Request $request)
    {
        $request->validate([
            'academic_year_id' => ['required','integer','exists:academic_years,id'],
            'exam_name'        => ['required','string','max:100'],
            'standard_id'      => ['required','integer','exists:standards,id'],
            'display_order'    => ['nullable','integer','min:0'],
            'subjects'         => ['nullable','array'],
        ]);

        $academicYearId = (int) $request->academic_year_id;
        $standardId     = (int) $request->standard_id;
        $examName       = strtoupper(trim($request->exam_name));
        $examType       = $this->normalizeExamType($examName);

        $standard   = Standard::find($standardId);
        $sheet      = $standard ? $this->findSheetForStandard($standard->standard_name) : null;
        $passingPct = $this->getPassingPercentage($standardId);

        $componentFlags = $this->deriveComponentFlagsFromSheet($sheet, $examType);

        $exists = ExamMaster::where('academic_year_id',$academicYearId)
            ->where('standard_id',$standardId)
            ->where('exam_name',$examName)->exists();

        if ($exists) {
            return back()->withInput()->with('error','Exam Master already exists.');
        }

        try {
            $examMaster = null;

            DB::transaction(function () use (
                $request, $academicYearId, $examName, $examType, $standardId,
                $componentFlags, $sheet, $passingPct, &$examMaster
            ) {

                $examMaster = ExamMaster::create([
                    'academic_year_id' => $academicYearId,
                    'exam_name'        => $examName,
                    'standard_id'      => $standardId,
                    'max_marks'        => 0,
                    'passing_marks'    => 0,
                    'display_order'    => $request->filled('display_order') ? (int) $request->display_order : 0,
                    'is_active'        => $request->boolean('is_active', true),
                ]);

                $examMaster->forceFill([
                    'has_theory'    => $componentFlags['has_theory'],
                    'has_oral'      => $componentFlags['has_oral'],
                    'has_practical' => $componentFlags['has_practical'],
                ])->save();

                $standardSubjects = $this->getStandardSubjects($standardId);
                if ($standardSubjects->isEmpty()) {
                    throw new \RuntimeException('No active subjects mapped.');
                }

                $cols = Schema::getColumnListing('exam_master_subjects');

                foreach ($standardSubjects as $ss) {
                    $excel = $sheet ? $this->matchExcelSubject($ss->subject_name, $examType, $sheet) : null;

                    $maxMarks  = $excel ? (float) $excel['total_max_marks']    : 40.0;
                    $passMarks = $excel ? (float) $excel['total_passing_marks'] :
                        ($maxMarks > 0 ? (int) ceil($maxMarks * $passingPct / 100) : 0);

                    $data = [
                        'exam_master_id' => $examMaster->id,
                        'standard_id'    => $standardId,
                        'subject_id'     => $ss->subject_id,
                        'subject_name'   => $ss->subject_name,
                        'max_marks'      => $maxMarks,
                        'passing_marks'  => $passMarks,
                        'display_order'  => (int) ($ss->sort_order ?? 0),
                    ];

                    $opt = [
                        'exam_type'               => $examType,
                        'theory_max_marks'        => (float) ($excel['theory_max_marks']        ?? 0),
                        'theory_passing_marks'    => (float) ($excel['theory_passing_marks']    ?? 0),
                        'oral_max_marks'          => (float) ($excel['oral_max_marks']          ?? 0),
                        'oral_passing_marks'      => (float) ($excel['oral_passing_marks']      ?? 0),
                        'practical_max_marks'     => (float) ($excel['practical_max_marks']     ?? 0),
                        'practical_passing_marks' => (float) ($excel['practical_passing_marks'] ?? 0),
                        'total_max_marks'         => $maxMarks,
                        'total_passing_marks'     => $passMarks,
                        'is_optional'             => (int) ($ss->is_optional ?? 0),
                    ];
                    foreach ($opt as $c => $val) {
                        if (in_array($c, $cols, true)) $data[$c] = $val;
                    }

                    // FIX: Use updateOrCreate instead of create
                    ExamMasterSubject::updateOrCreate(
                        ['exam_master_id' => $examMaster->id, 'subject_id' => $ss->subject_id],
                        $data
                    );
                }
            });

            if ($examMaster) {
                $this->syncStudentMarks($examMaster->id, $standardId);
            }

            return redirect()->route('exam-masters.index')->with('success','Exam Saved Successfully.');

        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error','Exam save failed: '.$e->getMessage());
        }
    }

    /* ================================================================
     | EDIT
     * ================================================================ */
    public function edit(ExamMaster $examMaster)
    {
        $academicYears = AcademicYear::where('is_active',1)->orderByDesc('id')->get();
        $standards     = Standard::where('is_active',1)->orderBy('display_order')->get();

        $standard   = Standard::find($examMaster->standard_id);
        $examType   = $this->normalizeExamType($examMaster->exam_name);
        $sheet      = $standard ? $this->findSheetForStandard($standard->standard_name) : null;
        $passingPct = $this->getPassingPercentage($examMaster->standard_id);

        $standardSubjects = $this->getStandardSubjects((int) $examMaster->standard_id);

        $existingConfigs = ExamMasterSubject::where('exam_master_id',$examMaster->id)
            ->where('standard_id',$examMaster->standard_id)
            ->get()->keyBy('subject_id');

        $subjects = collect();

        foreach ($standardSubjects as $ss) {

            $existing = $existingConfigs->get($ss->subject_id);
            $excel    = $sheet ? $this->matchExcelSubject($ss->subject_name, $examType, $sheet) : null;

            /* ⭐ Excel (fresh structure) wins; only fall back to saved snapshot */
            if ($excel) {
                $tMax  = (float) ($excel['theory_max_marks']        ?? 0);
                $tPass = (float) ($excel['theory_passing_marks']    ?? 0);
                $oMax  = (float) ($excel['oral_max_marks']          ?? 0);
                $oPass = (float) ($excel['oral_passing_marks']      ?? 0);
                $pMax  = (float) ($excel['practical_max_marks']     ?? 0);
                $pPass = (float) ($excel['practical_passing_marks'] ?? 0);

                $maxMarks  = (float) ($excel['total_max_marks']     ?? 0);
                $passMarks = (float) ($excel['total_passing_marks'] ?? 0);

                if ($maxMarks <= 0) {
                    $maxMarks = $tMax + $oMax + $pMax;
                }
                if ($passMarks <= 0) {
                    $passMarks = $tPass + $oPass + $pPass;
                }
            } elseif ($existing) {
                $tMax  = (float) $existing->theory_max_marks;
                $tPass = (float) $existing->theory_passing_marks;
                $oMax  = (float) $existing->oral_max_marks;
                $oPass = (float) $existing->oral_passing_marks;
                $pMax  = (float) $existing->practical_max_marks;
                $pPass = (float) $existing->practical_passing_marks;

                $maxMarks  = (float) $existing->max_marks;
                $passMarks = (float) $existing->passing_marks;
            } else {
                $tMax = $tPass = $oMax = $oPass = $pMax = $pPass = 0.0;

                $maxMarks  = 40.0;
                $passMarks = (int) ceil($maxMarks * $passingPct / 100);
            }

            $subjects->push((object) [
                'standard_wise_subject_id' => $ss->standard_wise_subject_id,
                'subject_id'               => $ss->subject_id,
                'subject_name'             => $ss->subject_name,
                'subject_code'             => $ss->subject_code,
                'short_name'               => $ss->short_name,
                'sort_order'               => $ss->sort_order,
                'is_optional'              => (int) ($ss->is_optional ?? 0),
                'theory_max_marks'         => $tMax,
                'theory_passing_marks'     => $tPass,
                'oral_max_marks'           => $oMax,
                'oral_passing_marks'       => $oPass,
                'practical_max_marks'      => $pMax,
                'practical_passing_marks'  => $pPass,
                'max_marks'                => (float) $maxMarks,
                'passing_marks'            => (float) $passMarks,
                'display_order'            => (int) ($existing->display_order ?? $ss->sort_order ?? 0),
                'configured'               => $existing ? 1 : 0,
            ]);
        }

        return view('exam-masters.edit', compact(
            'examMaster','academicYears','standards','subjects'
        ) + ['examTypeValue' => $examType]);
    }

    /* ================================================================
     | UPDATE
     * ================================================================ */
    public function update(Request $request, $id)
    {
        $request->validate([
            'academic_year_id' => ['required','integer','exists:academic_years,id'],
            'exam_name'        => ['required','string','max:100'],
            'standard_id'      => ['required','integer','exists:standards,id'],
            'display_order'    => ['nullable','integer','min:0'],
            'subjects'         => ['nullable','array'],
        ]);

        $examMaster     = ExamMaster::findOrFail($id);
        $academicYearId = (int) $request->academic_year_id;
        $standardId     = (int) $request->standard_id;
        $examName       = strtoupper(trim($request->exam_name));
        $examType       = $this->normalizeExamType($examName);

        $standard   = Standard::find($standardId);
        $sheet      = $standard ? $this->findSheetForStandard($standard->standard_name) : null;
        $passingPct = $this->getPassingPercentage($standardId);

        $componentFlags = $this->deriveComponentFlagsFromSheet($sheet, $examType);

        $duplicate = ExamMaster::where('academic_year_id',$academicYearId)
            ->where('standard_id',$standardId)
            ->where('exam_name',$examName)
            ->where('id','!=',$examMaster->id)->exists();

        if ($duplicate) {
            return back()->withInput()->with('error','Another Exam Master already exists.');
        }

        try {
            DB::transaction(function () use (
                $request, $examMaster, $academicYearId, $standardId, $examName, $examType,
                $componentFlags, $sheet, $passingPct
            ) {

                $examMaster->update([
                    'academic_year_id' => $academicYearId,
                    'exam_name'        => $examName,
                    'standard_id'      => $standardId,
                    'display_order'    => $request->filled('display_order') ? (int) $request->display_order : $examMaster->display_order,
                    'is_active'        => $request->boolean('is_active', true),
                ]);

                $examMaster->forceFill([
                    'has_theory'    => $componentFlags['has_theory'],
                    'has_oral'      => $componentFlags['has_oral'],
                    'has_practical' => $componentFlags['has_practical'],
                ])->save();

                $standardSubjects = $this->getStandardSubjects($standardId);
                if ($standardSubjects->isEmpty()) {
                    throw new \RuntimeException('No active subjects mapped.');
                }

                // FIX: Removed the delete() statement to preserve exam_master_subject IDs and prevent cascade deletions.
                // ExamMasterSubject::where('exam_master_id',$examMaster->id)->delete();

                $cols = Schema::getColumnListing('exam_master_subjects');
                $processedSubjectIds = [];

                foreach ($standardSubjects as $ss) {
                    $processedSubjectIds[] = $ss->subject_id;

                    $excel = $sheet ? $this->matchExcelSubject($ss->subject_name, $examType, $sheet) : null;

                    $maxMarks  = $excel ? (float) $excel['total_max_marks']    : 40.0;
                    $passMarks = $excel ? (float) $excel['total_passing_marks'] :
                        ($maxMarks > 0 ? (int) ceil($maxMarks * $passingPct / 100) : 0);

                    $data = [
                        'exam_master_id' => $examMaster->id,
                        'standard_id'    => $standardId,
                        'subject_id'     => $ss->subject_id,
                        'subject_name'   => $ss->subject_name,
                        'max_marks'      => $maxMarks,
                        'passing_marks'  => $passMarks,
                        'display_order'  => (int) ($ss->sort_order ?? 0),
                    ];

                    $opt = [
                        'exam_type'               => $examType,
                        'theory_max_marks'        => (float) ($excel['theory_max_marks']        ?? 0),
                        'theory_passing_marks'    => (float) ($excel['theory_passing_marks']    ?? 0),
                        'oral_max_marks'          => (float) ($excel['oral_max_marks']          ?? 0),
                        'oral_passing_marks'      => (float) ($excel['oral_passing_marks']      ?? 0),
                        'practical_max_marks'     => (float) ($excel['practical_max_marks']     ?? 0),
                        'practical_passing_marks' => (float) ($excel['practical_passing_marks'] ?? 0),
                        'total_max_marks'         => $maxMarks,
                        'total_passing_marks'     => $passMarks,
                        'is_optional'             => (int) ($ss->is_optional ?? 0),
                    ];
                    foreach ($opt as $c => $val) {
                        if (in_array($c, $cols, true)) $data[$c] = $val;
                    }

                    // FIX: Use updateOrCreate to prevent changing the ID of existing subject configurations
                    ExamMasterSubject::updateOrCreate(
                        ['exam_master_id' => $examMaster->id, 'subject_id' => $ss->subject_id],
                        $data
                    );
                }

                // Clean up only the subjects that were completely removed from the standard mapping
                ExamMasterSubject::where('exam_master_id', $examMaster->id)
                    ->whereNotIn('subject_id', $processedSubjectIds)
                    ->delete();
            });

            $this->syncStudentMarks($examMaster->id, $standardId);

            return redirect()->route('exam-masters.index')->with('success','Exam Updated Successfully.');

        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error','Exam update failed: '.$e->getMessage());
        }
    }

    /* ================================================================
     | LOAD SUBJECTS
     * ================================================================ */
    public function loadSubjects(Request $request, $standardId)
    {
        $standardId = (int) $standardId;

        $standard = Standard::find($standardId);
        if (!$standard) return response()->json(['error' => 'Standard not found.'], 404);

        $examType   = $this->normalizeExamType($request->query('exam_type',''));
        $sheet      = $this->findSheetForStandard($standard->standard_name);
        $passingPct = $this->getPassingPercentage($standardId);

        $subjects = DB::table('standard_wise_subjects as sws')
            ->join('subjects as s','s.id','=','sws.subject_id')
            ->where('sws.standard_id',$standardId)
            ->where('sws.is_active',1)
            ->where('s.is_active',1)
            ->whereNotIn('s.subject_type_id', self::SKILL_SUBJECT_TYPE_IDS)
            ->orderBy('sws.sort_order')
            ->orderBy('s.id')
            ->select([
                's.id as id','s.id as subject_id','s.subject_name','s.subject_code','s.short_name',
                'sws.id as standard_wise_subject_id','sws.sort_order','sws.is_optional',
            ])
            ->get();

        $result = $subjects->map(function ($sub) use ($sheet, $examType, $passingPct) {

            $excel = ($sheet && $examType)
                ? $this->matchExcelSubject($sub->subject_name, $examType, $sheet)
                : null;

            $tM = (float) ($excel['theory_max_marks']        ?? 0);
            $tP = (float) ($excel['theory_passing_marks']    ?? 0);
            $oM = (float) ($excel['oral_max_marks']          ?? 0);
            $oP = (float) ($excel['oral_passing_marks']      ?? 0);
            $pM = (float) ($excel['practical_max_marks']     ?? 0);
            $pP = (float) ($excel['practical_passing_marks'] ?? 0);

            $totalMax  = $tM + $oM + $pM;
            $totalPass = $tP + $oP + $pP;

            if (!$excel) {
                $totalMax  = 40.0;
                $totalPass = (int) ceil($totalMax * $passingPct / 100);
            }

            return [
                'subject_id'               => (int) $sub->subject_id,
                'subject_name'             => $sub->subject_name,
                'subject_code'             => $sub->subject_code,
                'short_name'               => $sub->short_name,
                'standard_wise_subject_id' => (int) $sub->standard_wise_subject_id,
                'sort_order'               => (int) $sub->sort_order,
                'is_optional'              => (int) $sub->is_optional,
                'exam_type'                => $examType,
                'theory_max_marks'         => $tM,
                'theory_passing_marks'     => $tP,
                'oral_max_marks'           => $oM,
                'oral_passing_marks'       => $oP,
                'practical_max_marks'      => $pM,
                'practical_passing_marks'  => $pP,
                'total_max_marks'          => $totalMax,
                'total_passing_marks'      => $totalPass,
                'max_marks'                => $totalMax,
                'passing_marks'            => $totalPass,
                'has_excel_structure'      => $excel !== null,
            ];
        })->values();

        return response()->json([
            'sheet'              => $sheet['sheet'] ?? null,
            'exam_type'          => $examType,
            'standard_name'      => $standard->standard_name,
            'passing_percentage' => $passingPct,
            'subjects'           => $result,
        ]);
    }

    /* ================================================================
     | DESTROY
     * ================================================================ */
    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                DB::table('student_result_details')
                    ->whereIn('student_result_id', function ($q) use ($id) {
                        $q->select('id')->from('student_results')->where('exam_master_id',$id);
                    })->delete();
                DB::table('student_results')->where('exam_master_id',$id)->delete();
                DB::table('student_marks')->where('exam_master_id',$id)->delete();

                if (Schema::hasTable('marks_entries'))        DB::table('marks_entries')->where('exam_master_id',$id)->delete();
                if (Schema::hasTable('teacher_marks_status')) DB::table('teacher_marks_status')->where('exam_master_id',$id)->delete();
                if (Schema::hasTable('exam_subjects'))        ExamSubject::where('exam_master_id',$id)->delete();

                ExamMasterSubject::where('exam_master_id',$id)->delete();
                ExamMaster::where('id',$id)->delete();
            });

            return redirect()->route('exam-masters.index')
                ->with('success','Exam and all related data deleted successfully.');

        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error','Delete failed: '.$e->getMessage());
        }
    }
}