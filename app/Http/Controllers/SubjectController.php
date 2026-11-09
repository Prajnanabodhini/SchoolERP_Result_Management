<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

use App\Models\Subject;
use App\Models\Standard;
use App\Models\StandardWiseSubject;

class SubjectController extends Controller
{
    private const SKILL_SUBJECT_TYPE_IDS = [2, 3];

    private function clearSubjectCaches(): void
    {
        foreach (['academic_years', 'standards', 'divisions', 'skill_subjects'] as $key) {
            Cache::forget('skill_obs:v8:' . $key);
            Cache::forget('skill_obs:v7:' . $key);
            Cache::forget('skill_obs:v6:' . $key);
            Cache::forget('skill_obs:v4:' . $key);
            Cache::forget('skill_obs:v3:' . $key);
            Cache::forget('skill_obs:v2:' . $key);
        }
        Cache::flush();
    }

    private function safeCreateMapping(array $attributes): bool
    {
        try {
            StandardWiseSubject::create($attributes);
            return true;
        } catch (QueryException $e) {
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry')) {
                return false;
            }
            throw $e;
        }
    }

    private function normalizeName(string $name): string
    {
        return strtoupper(trim(preg_replace('/\s+/', ' ', $name)));
    }

    private function findMasterByName(string $name): ?Subject
    {
        return Subject::whereRaw('UPPER(TRIM(subject_name)) = ?', [$this->normalizeName($name)])
            ->orderBy('id')
            ->first();
    }

    private function findMasterByCode(string $code, int $sectionId, int $typeId): ?Subject
    {
        return Subject::where('subject_code', strtoupper(trim($code)))
            ->where('section_id', $sectionId)
            ->where('subject_type_id', $typeId)
            ->first();
    }

    private function mergeSubjectData($masterId, $dupId)
    {
        $masterSubject = Subject::find($masterId);
        $dupSubject = Subject::find($dupId);

        if (!$masterSubject || !$dupSubject) return;

        // 1. Standard Mappings
        $mappedStandards = StandardWiseSubject::where('subject_id', $masterId)->pluck('standard_id')->toArray();
        StandardWiseSubject::where('subject_id', $dupId)->whereIn('standard_id', $mappedStandards)->delete();
        StandardWiseSubject::where('subject_id', $dupId)->update(['subject_id' => $masterId]);

        // 2. Exam Structures (Now uses subject_id)
        if (Schema::hasTable('exam_structures') && Schema::hasColumn('exam_structures', 'subject_id')) {
            $dupStructures = DB::table('exam_structures')->where('subject_id', $dupId)->get();
            foreach ($dupStructures as $dupStruct) {
                $masterExists = DB::table('exam_structures')
                    ->where('standard_key', $dupStruct->standard_key)
                    ->where('exam_type', $dupStruct->exam_type)
                    ->where('subject_id', $masterId)
                    ->exists();

                if ($masterExists) {
                    DB::table('exam_structures')->where('id', $dupStruct->id)->delete();
                } else {
                    DB::table('exam_structures')->where('id', $dupStruct->id)->update([
                        'subject_id' => $masterId,
                        'subject_name' => $masterSubject->subject_name,
                        'subject_code' => $masterSubject->subject_code,
                    ]);
                }
            }
        }

        // 3. Exam Master Subjects
        if (Schema::hasTable('exam_master_subjects') && Schema::hasColumn('exam_master_subjects', 'subject_id')) {
            $dupConfigs = DB::table('exam_master_subjects')->where('subject_id', $dupId)->get();
            foreach ($dupConfigs as $config) {
                $masterConfig = DB::table('exam_master_subjects')
                    ->where('exam_master_id', $config->exam_master_id)
                    ->where('subject_id', $masterId)
                    ->first();
                
                if ($masterConfig) {
                    DB::table('exam_master_subjects')->where('id', $config->id)->delete();
                } else {
                    DB::table('exam_master_subjects')->where('id', $config->id)->update(['subject_id' => $masterId]);
                }
            }
        }

        // 4. Student Skill Marks (Now uses subject_id)
        if (Schema::hasTable('student_skill_marks') && Schema::hasColumn('student_skill_marks', 'subject_id')) {
            DB::table('student_skill_marks')->where('subject_id', $dupId)->update([
                'subject_id' => $masterId,
                'subject_name' => $masterSubject->subject_name
            ]);
        }

        // 5. Teacher Allocations
        if (Schema::hasTable('teacher_subject_allocations') && Schema::hasColumn('teacher_subject_allocations', 'subject_id')) {
            $dupAllocations = DB::table('teacher_subject_allocations')->where('subject_id', $dupId)->get();
            
            foreach ($dupAllocations as $dupAlloc) {
                $masterAlloc = DB::table('teacher_subject_allocations')
                    ->where('subject_id', $masterId)
                    ->where('teacher_class_allocation_id', $dupAlloc->teacher_class_allocation_id)
                    ->where('exam_master_id', $dupAlloc->exam_master_id)
                    ->first();

                if ($masterAlloc) {
                    $dupMarks = DB::table('student_marks')->where('teacher_subject_allocation_id', $dupAlloc->id)->get();
                    foreach ($dupMarks as $mark) {
                        $exists = DB::table('student_marks')
                            ->where('teacher_subject_allocation_id', $masterAlloc->id)
                            ->where('student_id', $mark->student_id)
                            ->where('exam_master_id', $mark->exam_master_id)
                            ->exists();
                            
                        if ($exists) {
                            DB::table('student_marks')->where('id', $mark->id)->delete();
                        } else {
                            DB::table('student_marks')->where('id', $mark->id)->update([
                                'teacher_subject_allocation_id' => $masterAlloc->id,
                                'subject_id' => $masterId
                            ]);
                        }
                    }

                    $dupStatuses = DB::table('teacher_marks_status')->where('teacher_subject_allocation_id', $dupAlloc->id)->get();
                    foreach ($dupStatuses as $status) {
                        $exists = DB::table('teacher_marks_status')
                            ->where('teacher_subject_allocation_id', $masterAlloc->id)
                            ->where('exam_master_id', $status->exam_master_id)
                            ->exists();
                            
                        if ($exists) {
                            DB::table('teacher_marks_status')->where('id', $status->id)->delete();
                        } else {
                            DB::table('teacher_marks_status')->where('id', $status->id)->update([
                                'teacher_subject_allocation_id' => $masterAlloc->id,
                                'subject_id' => $masterId
                            ]);
                        }
                    }

                    DB::table('teacher_subject_allocations')->where('id', $dupAlloc->id)->delete();
                } else {
                    DB::table('teacher_subject_allocations')->where('id', $dupAlloc->id)->update(['subject_id' => $masterId]);
                }
            }
        }

        // 6. Orphaned Student Marks
        if (Schema::hasTable('student_marks') && Schema::hasColumn('student_marks', 'subject_id')) {
            DB::table('student_marks')->where('subject_id', $dupId)->update(['subject_id' => $masterId]);
        }

        // 7. Orphaned Teacher Marks Status
        if (Schema::hasTable('teacher_marks_status') && Schema::hasColumn('teacher_marks_status', 'subject_id')) {
            DB::table('teacher_marks_status')->where('subject_id', $dupId)->update(['subject_id' => $masterId]);
        }

        // 8. Mark Audit Logs
        if (Schema::hasTable('mark_audit_logs') && Schema::hasColumn('mark_audit_logs', 'subject_id')) {
            DB::table('mark_audit_logs')->where('subject_id', $dupId)->update(['subject_id' => $masterId]);
        }

        // 9. Skill Observations
        if (Schema::hasTable('student_skill_observations') && Schema::hasColumn('student_skill_observations', 'subject_id')) {
            DB::table('student_skill_observations')->where('subject_id', $dupId)->update(['subject_id' => $masterId]);
        }

        // 10. Skill Allocations
        if (Schema::hasTable('skill_teacher_subject_allocations') && Schema::hasColumn('skill_teacher_subject_allocations', 'subject_id')) {
            DB::table('skill_teacher_subject_allocations')->where('subject_id', $dupId)->update(['subject_id' => $masterId]);
        }

        // 11. Finally, delete the duplicate subject
        DB::table('subjects')->where('id', $dupId)->delete();
    }

    public function index(Request $request)
    {
        $standardFilter = $request->filled('standard_id') ? (int) $request->standard_id : null;

        $query = StandardWiseSubject::with(['standard', 'subject', 'subject.subjectType'])
            ->when($standardFilter, fn ($q) => $q->where('standard_id', $standardFilter));
            
        $subjects = $query->orderBy('standard_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $standards = Standard::where('is_active', 1)->orderBy('display_order')->get();
        
        $totalCount = $subjects->count();

        return view('subjects.index', compact('subjects', 'standards', 'standardFilter', 'totalCount'));
    }

    public function create()
    {
        $standards     = Standard::where('is_active', 1)->orderBy('display_order')->get();
        $subjectTypes  = DB::table('subject_types')->where('is_active', 1)->orderBy('id')->get();
        $nextSortOrder = (StandardWiseSubject::max('sort_order') ?? 0) + 1;
        
        $existingAcademicSubjects = Subject::where('is_active', 1)->where('subject_type_id', 1)->orderBy('subject_name')->get();
        $existingSkillSubjects    = Subject::where('is_active', 1)->whereIn('subject_type_id', self::SKILL_SUBJECT_TYPE_IDS)->orderBy('subject_name')->get();

        return view('subjects.create', compact('standards', 'subjectTypes', 'nextSortOrder', 'existingAcademicSubjects', 'existingSkillSubjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'standard_ids'    => ['required', 'array', 'min:1'],
            'standard_ids.*'  => ['integer', 'exists:standards,id'],
            'subject_name'    => ['required', 'string', 'max:255'],
            'subject_code'    => ['required', 'string', 'max:50'],
            'short_name'      => ['nullable', 'string', 'max:20'],
            'subject_type_id' => ['required', 'integer', 'exists:subject_types,id'],
            'sort_order'      => ['nullable', 'integer', 'min:0'],
            'is_optional'     => ['nullable'],
            'is_active'       => ['nullable'],
        ]);

        $standardIds  = array_map('intval', $request->standard_ids);
        $subjectName  = trim((string) $request->subject_name);

        try {
            $createdCount = 0;
            $skippedCount = 0;
            $reusedMaster = false;

            DB::transaction(function () use ($request, $standardIds, $subjectName, &$createdCount, &$skippedCount, &$reusedMaster) {
                $subjectCode   = strtoupper(trim((string) $request->subject_code));
                $shortName     = $request->filled('short_name') ? trim((string) $request->short_name) : null;
                $subjectTypeId = (int) $request->subject_type_id;
                $sortOrder     = (int) ($request->sort_order ?? 0);
                $isOptional    = $request->has('is_optional') ? 1 : 0;
                $isActive      = $request->has('is_active') ? 1 : 0;

                $masterSubject = $this->findMasterByName($subjectName);

                if ($masterSubject) {
                    $reusedMaster = true;
                } else {
                    $firstStandard = Standard::find($standardIds[0]);
                    $sectionId     = $firstStandard ? (int) $firstStandard->section_id : 0;

                    $masterSubject = $this->findMasterByCode($subjectCode, $sectionId, $subjectTypeId);

                    if (!$masterSubject) {
                        $masterSubject = Subject::create([
                            'subject_name'    => $subjectName,
                            'subject_code'    => $subjectCode,
                            'short_name'      => $shortName,
                            'section_id'      => $sectionId,
                            'subject_type_id' => $subjectTypeId,
                            'display_order'   => $sortOrder,
                            'is_active'       => $isActive,
                        ]);
                    } else {
                        $reusedMaster = true;
                    }
                }

                foreach ($standardIds as $standardId) {
                    $standard = Standard::find($standardId);
                    if (!$standard) { $skippedCount++; continue; }

                    if (StandardWiseSubject::where('standard_id', $standardId)->where('subject_id', $masterSubject->id)->exists()) {
                        $skippedCount++;
                        continue;
                    }

                    $ok = $this->safeCreateMapping([
                        'standard_id'   => $standardId,
                        'subject_id'    => $masterSubject->id,
                        'standard_name' => $standard->standard_name,
                        'subject_name'  => $masterSubject->subject_name,
                        'sort_order'    => $sortOrder,
                        'is_optional'   => $isOptional,
                        'is_active'     => $isActive,
                    ]);
                    $ok ? $createdCount++ : $skippedCount++;
                }
            });

            $this->clearSubjectCaches();

            if ($createdCount === 0 && $skippedCount > 0) {
                return redirect()->route('subjects.index')->with('success', 'Subject was already mapped to the selected standard(s). Nothing new was added.');
            }
            if ($createdCount === 0) {
                return redirect()->route('subjects.index')->with('success', 'No new mappings were created.');
            }

            $message = ($reusedMaster
                    ? "Mapped existing subject \"{$subjectName}\" to {$createdCount} standard(s)."
                    : "Subject \"{$subjectName}\" created and mapped to {$createdCount} standard(s).")
                . ($skippedCount > 0 ? " Skipped {$skippedCount} already-mapped." : "");

            return redirect()->route('subjects.index')->with('success', $message);

        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'Unexpected error: ' . $e->getMessage());
        }
    }

    public function edit(StandardWiseSubject $subject)
    {
        return redirect()->route('subjects.edit-master', $subject->subject_id);
    }

    public function editMaster(Subject $subject)
    {
        $subject->load('subjectType');
        $masterSubject = $subject;
        $standards     = Standard::where('is_active', 1)->orderBy('display_order')->get();
        $subjectTypes  = DB::table('subject_types')->where('is_active', 1)->orderBy('id')->get();
        
        $mappedStandardIds = StandardWiseSubject::where('subject_id', $masterSubject->id)
            ->pluck('standard_id')->map(fn ($v) => (string) $v)->toArray();

        return view('subjects.edit', compact('masterSubject', 'standards', 'subjectTypes', 'mappedStandardIds'));
    }

    public function updateMaster(Request $request, Subject $subject)
    {
        $request->validate([
            'subject_name'    => ['required', 'string', 'max:255'],
            'subject_code'    => ['required', 'string', 'max:50'],
            'subject_type_id' => ['required', 'integer', 'exists:subject_types,id'],
            'standard_ids'    => ['nullable', 'array'],
        ]);

        $newName = trim((string) $request->subject_name);
        $newCode = strtoupper(trim((string) $request->subject_code));
        $newType = (int) $request->subject_type_id;

        // Check if this name already belongs to another subject ID
        $existingMaster = $this->findMasterByName($newName);
        
        DB::beginTransaction();
        try {
            if ($existingMaster && $existingMaster->id !== $subject->id) {
                // 🚀 AUTO-MERGE: The name already exists! Merge this subject into the existing master.
                $this->mergeSubjectData($existingMaster->id, $subject->id);

                DB::commit();
                $this->clearSubjectCaches();

                return redirect()->route('subjects.index')->with('success', 'Duplicate subject detected. All records were successfully merged into the existing Master Subject.');
            }

            // Normal Update (if no duplicate name conflict exists)
            $byCode = $this->findMasterByCode($newCode, (int) $subject->section_id, $newType);
            if ($byCode && $byCode->id !== $subject->id) {
                return back()->withInput()->with('error', "Code '{$newCode}' is already used by subject \"{$byCode->subject_name}\" for this type.");
            }

            $subject->subject_name    = $newName;
            $subject->subject_code    = $newCode;
            $subject->short_name      = $request->filled('short_name') ? trim($request->short_name) : null;
            $subject->subject_type_id = $newType; 
            $subject->display_order   = (int) ($request->sort_order ?? 0);
            $subject->is_active       = $request->has('is_active') ? 1 : 0;
            $subject->save(); 

            // Update existing mappings
            StandardWiseSubject::where('subject_id', $subject->id)->update([
                'subject_name' => $subject->subject_name,
                'is_active'    => $request->has('is_active') ? 1 : 0,
            ]);

            // Sync Standard Mappings (Add / Remove)
            $desiredIds  = array_map('intval', $request->input('standard_ids', []));
            $existingIds = StandardWiseSubject::where('subject_id', $subject->id)
                ->pluck('standard_id')->map(fn ($v) => (int) $v)->toArray();

            $sortOrder  = (int) ($request->sort_order ?? 0);
            $isOptional = $request->has('is_optional') ? 1 : 0;

            // Add new mappings
            foreach (array_diff($desiredIds, $existingIds) as $standardId) {
                if ($standard = Standard::find($standardId)) {
                    $this->safeCreateMapping([
                        'standard_id'   => $standardId,
                        'subject_id'    => $subject->id,
                        'standard_name' => $standard->standard_name,
                        'subject_name'  => $subject->subject_name,
                        'sort_order'    => $sortOrder,
                        'is_optional'   => $isOptional,
                        'is_active'     => 1,
                    ]);
                }
            }

            // Remove unchecked mappings
            if (!empty($toRemove = array_diff($existingIds, $desiredIds))) {
                StandardWiseSubject::where('subject_id', $subject->id)
                    ->whereIn('standard_id', $toRemove)
                    ->delete();
            }

            DB::commit();
            $this->clearSubjectCaches();

            return redirect()->route('subjects.index')->with('success', 'Subject and mappings updated successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()->with('error', 'Database Error: ' . $e->getMessage());
        }
    }

    public function manage(Request $request)
    {
        $standards = Standard::where('is_active', 1)->orderBy('display_order')->get();
        $selectedStandard = $request->input('standard_id');
        
        $academicSubjects = collect();
        $skillSubjects = collect();
        $mappedSubjectIds = [];

        if ($selectedStandard) {
            $academicSubjects = Subject::where('subject_type_id', 1)
                ->where('is_active', 1)
                ->orderBy('subject_name')
                ->get();

            $skillSubjects = Subject::whereIn('subject_type_id', self::SKILL_SUBJECT_TYPE_IDS)
                ->where('is_active', 1)
                ->orderBy('subject_name')
                ->get();

            $mappedSubjectIds = StandardWiseSubject::where('standard_id', $selectedStandard)
                ->pluck('subject_id')
                ->map(fn($id) => (string)$id) 
                ->toArray();
        }

        return view('subjects.manage', compact('standards', 'selectedStandard', 'academicSubjects', 'skillSubjects', 'mappedSubjectIds'));
    }

    public function saveMappings(Request $request)
    {
        $request->validate([
            'standard_id' => 'required|exists:standards,id',
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'exists:subjects,id'
        ]);

        $standardId = $request->standard_id;
        $selectedSubjectIds = array_map('strval', $request->input('subject_ids', []));
        $standard = Standard::find($standardId);

        DB::beginTransaction();
        try {
            $currentMappings = StandardWiseSubject::where('standard_id', $standardId)
                ->pluck('subject_id')
                ->map(fn($id) => (string)$id)
                ->toArray();
            
            $toAdd = array_diff($selectedSubjectIds, $currentMappings);
            $toRemove = array_diff($currentMappings, $selectedSubjectIds);

            if (!empty($toRemove)) {
                StandardWiseSubject::where('standard_id', $standardId)->whereIn('subject_id', $toRemove)->delete();
            }

            foreach ($toAdd as $subId) {
                $subject = Subject::find($subId);
                if ($subject) {
                    $this->safeCreateMapping([
                        'standard_id'   => $standardId,
                        'subject_id'    => $subId,
                        'standard_name' => $standard->standard_name,
                        'subject_name'  => $subject->subject_name,
                        'sort_order'    => $subject->display_order ?? 0,
                        'is_optional'   => 0,
                        'is_active'     => 1,
                    ]);
                }
            }

            DB::commit();
            $this->clearSubjectCaches();

            return redirect()->route('subjects.manage', ['standard_id' => $standardId])->with('success', 'Standard mappings updated successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()->with('error', 'Error updating mappings: ' . $e->getMessage());
        }
    }

    public function destroy(StandardWiseSubject $subject)
    {
        try {
            DB::transaction(function () use ($subject) {
                $subjectId = $subject->subject_id;
                $subject->delete();
                if ($subjectId && !StandardWiseSubject::where('subject_id', $subjectId)->where('is_active', 1)->exists()) {
                    Subject::where('id', $subjectId)->update(['is_active' => 0]);
                }
            });
            $this->clearSubjectCaches();
            return redirect()->route('subjects.index', ['standard_id' => request('standard_id')])
                ->with('success', 'Deleted.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

        public function mergeAllDuplicates()
    {
        // ✅ FIX: Group by both name AND type to prevent Academic/Skill conflicts
        $duplicates = DB::table('subjects')
            ->select('subject_name', 'subject_type_id', DB::raw('COUNT(*) as count'))
            ->groupBy('subject_name', 'subject_type_id')
            ->having('count', '>', 1)
            ->get();

        $mergedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($duplicates as $dup) {
                $subjects = DB::table('subjects')
                    ->where('subject_name', $dup->subject_name)
                    ->where('subject_type_id', $dup->subject_type_id)
                    ->orderBy('id', 'asc')
                    ->get();

                $masterSubject = $subjects->first();
                $duplicateIds = $subjects->skip(1)->pluck('id')->toArray();

                if (empty($duplicateIds)) continue;

                foreach ($duplicateIds as $dupId) {
                    $this->mergeSubjectData($masterSubject->id, $dupId);
                    $mergedCount++;
                }
            }

            DB::commit();
            $this->clearSubjectCaches(); 
            
            return "Successfully merged {$mergedCount} duplicate subjects into their master IDs!";
        } catch (\Throwable $e) {
            DB::rollBack();
            return "Merge failed: " . $e->getMessage();
        }
    }
}