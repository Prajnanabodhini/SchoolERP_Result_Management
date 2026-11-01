<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
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
        
        // Clear all cache to be absolutely sure
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

        $byName = $this->findMasterByName($newName);
        if ($byName && $byName->id !== $subject->id) {
            return back()->withInput()->with('error', "A subject named \"{$byName->subject_name}\" already exists.");
        }

        $byCode = $this->findMasterByCode($newCode, (int) $subject->section_id, $newType);
        if ($byCode && $byCode->id !== $subject->id) {
            return back()->withInput()->with('error', "Code '{$newCode}' is already used by subject \"{$byCode->subject_name}\" for this type.");
        }

        DB::beginTransaction();
        try {
            // 1. Force update the master subject
            $subject->subject_name    = $newName;
            $subject->subject_code    = $newCode;
            $subject->short_name      = $request->filled('short_name') ? trim($request->short_name) : null;
            $subject->subject_type_id = $newType; 
            $subject->display_order   = (int) ($request->sort_order ?? 0);
            $subject->is_active       = $request->has('is_active') ? 1 : 0;
            $subject->save(); 

            // 2. Sync the Standard Mappings
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

            // Update names in mappings to match any name changes
            StandardWiseSubject::where('subject_id', $subject->id)->update([
                'subject_name' => $subject->subject_name,
                'is_active'    => $request->has('is_active') ? 1 : 0,
            ]);

            DB::commit();
            $this->clearSubjectCaches();

            return redirect()->route('subjects.index')->with('success', 'Subject and mappings updated successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            // Log the error so you can see it in storage/logs/laravel.log
            report($e);
            
            // Return the actual error message to the screen so you know why it failed
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
            // Only subjects with subject_type_id = 1
            $academicSubjects = Subject::where('subject_type_id', 1)
                ->where('is_active', 1)
                ->orderBy('subject_name')
                ->get();

            // Only subjects with subject_type_id = 2 or 3
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
}