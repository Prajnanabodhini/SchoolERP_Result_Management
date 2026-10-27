<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;

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

    public function index(Request $request)
    {
        $activeTab = $request->input('tab', 'academic');
        if (!in_array($activeTab, ['academic', 'skill', 'all'], true)) {
            $activeTab = 'academic';
        }

        $standardFilter = $request->filled('standard_id') ? (int) $request->standard_id : null;

        if ($activeTab === 'skill') {
            $subjectQuery = Subject::with('subjectType')->whereIn('subject_type_id', self::SKILL_SUBJECT_TYPE_IDS);
            if ($standardFilter) {
                $subjectQuery->whereHas('standardWiseSubjects', fn ($q) => $q->where('standard_id', $standardFilter));
            }
            $subjects = $subjectQuery->orderBy('subject_name')->get()->map(function ($subject) use ($standardFilter) {
                $mappings = StandardWiseSubject::with('standard')->where('subject_id', $subject->id)->when($standardFilter, fn ($q) => $q->where('standard_id', $standardFilter))->orderBy('standard_id')->orderBy('sort_order')->get();
                $standardsList = $mappings->map(fn ($m) => $m->standard->standard_name ?? $m->standard_name ?? '')->filter()->values();
                $subject->standards_list = $standardsList->implode(', ');
                $subject->standards_count = $standardsList->count();
                $subject->max_sort_order = $mappings->max('sort_order') ?? 0;
                return $subject;
            });
        } else {
            $query = StandardWiseSubject::with(['standard', 'subject', 'subject.subjectType']);
            if ($standardFilter) $query->where('standard_id', $standardFilter);
            if ($activeTab === 'academic') $query->whereHas('subject', fn ($q) => $q->where('subject_type_id', 1));
            $subjects = $query->orderBy('standard_id')->orderBy('sort_order')->orderBy('id')->get();
        }

        $countBase = StandardWiseSubject::query();
        if ($standardFilter) $countBase->where('standard_id', $standardFilter);
        $academicCount = (clone $countBase)->whereHas('subject', fn ($q) => $q->where('subject_type_id', 1))->count();
        $skillCount = (clone $countBase)->whereHas('subject', fn ($q) => $q->whereIn('subject_type_id', self::SKILL_SUBJECT_TYPE_IDS))->count();
        $allCount = (clone $countBase)->count();

        $standards = Standard::where('is_active', 1)->orderBy('display_order')->get();

        return view('subjects.index', compact('subjects', 'standards', 'activeTab', 'academicCount', 'skillCount', 'allCount'));
    }

    public function create()
    {
        $standards = Standard::where('is_active', 1)->orderBy('display_order')->get();
        $subjectTypes = DB::table('subject_types')->where('is_active', 1)->orderBy('id')->get();
        $nextSortOrder = (StandardWiseSubject::max('sort_order') ?? 0) + 1;
        return view('subjects.create', compact('standards', 'subjectTypes', 'nextSortOrder'));
    }

    private function findExistingMasterSubject(string $subjectCode, int $sectionId, int $subjectTypeId)
    {
        return Subject::where('subject_code', strtoupper(trim($subjectCode)))->where('section_id', $sectionId)->where('subject_type_id', $subjectTypeId)->first();
    }

    private function standardAlreadyHasSubject(int $standardId, int $subjectId, ?int $ignoreMappingId = null): bool
    {
        $query = StandardWiseSubject::where('standard_id', $standardId)->where('subject_id', $subjectId);
        if ($ignoreMappingId) $query->where('id', '!=', $ignoreMappingId);
        return $query->exists();
    }

    public function store(Request $request)
    {
        $request->validate([
            'standard_id' => ['required', function ($attribute, $value, $fail) {
                if ($value === 'all') return;
                if (!Standard::where('id', (int) $value)->exists()) $fail('The selected standard is invalid.');
            }],
            'subject_name'    => ['required', 'string', 'max:255'],
            'subject_code'    => ['required', 'string', 'max:50'],
            'short_name'      => ['nullable', 'string', 'max:20'],
            'subject_type_id' => ['required', 'integer', 'exists:subject_types,id'],
            'sort_order'      => ['nullable', 'integer', 'min:0'],
            'is_optional'     => ['nullable'],
            'is_active'       => ['nullable'],
        ]);

        $isAllStandards = ($request->standard_id === 'all');
        $subjectName = trim((string) $request->subject_name);

        if ($isAllStandards) {
            $standardIds = Standard::where('is_active', 1)->orderBy('display_order')->pluck('id')->toArray();
            if (empty($standardIds)) return back()->withInput()->with('error', 'No active standards found.');
        } else {
            $standardIds = [(int) $request->standard_id];
        }

        try {
            $createdCount = 0; $skippedCount = 0;
            DB::transaction(function () use ($request, $standardIds, $isAllStandards, $subjectName, &$createdCount, &$skippedCount) {
                $subjectCode   = strtoupper(trim((string) $request->subject_code));
                $shortName     = $request->filled('short_name') ? trim((string) $request->short_name) : null;
                $subjectTypeId = (int) $request->subject_type_id;
                $sortOrder     = (int) ($request->sort_order ?? 0);
                $isOptional    = $request->has('is_optional') ? 1 : 0;
                $isActive      = $request->has('is_active') ? 1 : 0;

                if ($isAllStandards) {
                    $masterSubject = Subject::where('subject_name', $subjectName)->first() ?? Subject::where('subject_code', $subjectCode)->where('subject_type_id', $subjectTypeId)->first();
                    if (!$masterSubject) {
                        $firstStandard = Standard::find($standardIds[0]);
                        $masterSubject = Subject::create([
                            'subject_name' => $subjectName, 'subject_code' => $subjectCode, 'short_name' => $shortName,
                            'section_id' => $firstStandard ? $firstStandard->section_id : 0, 'subject_type_id' => $subjectTypeId,
                            'display_order' => $sortOrder, 'is_active' => $isActive,
                        ]);
                    }
                    foreach ($standardIds as $standardId) {
                        $standard = Standard::find($standardId);
                        if (!$standard || $this->standardAlreadyHasSubject($standardId, $masterSubject->id)) { $skippedCount++; continue; }
                        $this->safeCreateMapping(['standard_id' => $standardId, 'subject_id' => $masterSubject->id, 'standard_name' => $standard->standard_name, 'subject_name' => $masterSubject->subject_name, 'sort_order' => $sortOrder, 'is_optional' => $isOptional, 'is_active' => $isActive]) ? $createdCount++ : $skippedCount++;
                    }
                } else {
                    $standardId = (int) $request->standard_id;
                    $standard = Standard::findOrFail($standardId);
                    $masterSubject = Subject::where('subject_name', $subjectName)->first() ?? $this->findExistingMasterSubject($subjectCode, (int)$standard->section_id, $subjectTypeId);
                    if (!$masterSubject) {
                        $masterSubject = Subject::create([
                            'subject_name' => $subjectName, 'subject_code' => $subjectCode, 'short_name' => $shortName,
                            'section_id' => (int)$standard->section_id, 'subject_type_id' => $subjectTypeId, 'display_order' => $sortOrder, 'is_active' => $isActive,
                        ]);
                    }
                    if ($this->standardAlreadyHasSubject($standardId, $masterSubject->id)) { $skippedCount++; }
                    else {
                        $this->safeCreateMapping(['standard_id' => $standardId, 'subject_id' => $masterSubject->id, 'standard_name' => $standard->standard_name, 'subject_name' => $masterSubject->subject_name, 'sort_order' => $sortOrder, 'is_optional' => $isOptional, 'is_active' => $isActive]) ? $createdCount++ : $skippedCount++;
                    }
                }
            });

            $this->clearSubjectCaches();
            if ($createdCount === 0 && $skippedCount > 0) return redirect()->route('subjects.index')->with('success', 'Subject was already mapped. Nothing new was added.');
            if ($createdCount === 0) return redirect()->route('subjects.index')->with('success', 'No new mappings were created.');

            $message = "Subject mapped to {$createdCount} standard(s) successfully." . ($skippedCount > 0 ? " Skipped {$skippedCount}." : "");
            return $isAllStandards ? redirect()->route('subjects.index')->with('success', $message) : redirect()->route('subjects.index', ['standard_id' => $request->standard_id])->with('success', $message);
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'Unexpected error: ' . $e->getMessage());
        }
    }

    public function edit(StandardWiseSubject $subject)
    {
        $subject->load(['standard', 'subject', 'subject.subjectType']);
        $mapping = $subject; $masterSubject = $subject->subject;
        if (!$masterSubject) abort(404, 'Subject master not found.');
        $standards = Standard::where('is_active', 1)->orderBy('display_order')->get();
        $subjectTypes = DB::table('subject_types')->where('is_active', 1)->orderBy('id')->get();
        $mappedStandardIds = StandardWiseSubject::where('subject_id', $masterSubject->id)->pluck('standard_id')->map(fn ($v) => (int) $v)->toArray();
        return view('subjects.edit', compact('mapping', 'masterSubject', 'standards', 'subjectTypes', 'mappedStandardIds'));
    }

    public function editMaster(Subject $subject)
    {
        $subject->load('subjectType');
        $mapping = null; $masterSubject = $subject;
        $standards = Standard::where('is_active', 1)->orderBy('display_order')->get();
        $subjectTypes = DB::table('subject_types')->where('is_active', 1)->orderBy('id')->get();
        $mappedStandardIds = StandardWiseSubject::where('subject_id', $masterSubject->id)->pluck('standard_id')->map(fn ($v) => (int) $v)->toArray();
        return view('subjects.edit', compact('mapping', 'masterSubject', 'standards', 'subjectTypes', 'mappedStandardIds'));
    }

    public function update(Request $request, StandardWiseSubject $subject)
    {
        $request->validate([
            'standard_id'     => ['required', function ($attribute, $value, $fail) {
                if ($value === 'all') return;
                if (!Standard::where('id', (int) $value)->exists()) $fail('Invalid standard.');
            }],
            'subject_name'    => ['required', 'string', 'max:255'],
            'subject_code'    => ['required', 'string', 'max:50'],
            'short_name'      => ['nullable', 'string', 'max:20'],
            'subject_type_id' => ['required', 'integer', 'exists:subject_types,id'],
            'sort_order'      => ['nullable', 'integer', 'min:0'],
            'is_optional'     => ['nullable'],
            'is_active'       => ['nullable'],
        ]);

        $isAllStandards = ($request->standard_id === 'all');
        $subjectName = trim((string) $request->subject_name);
        $subjectCode = strtoupper(trim((string) $request->subject_code));

        try {
            DB::transaction(function () use ($request, $subject, $subjectName, $subjectCode, $isAllStandards) {
                if (!$subject->subject_id) throw new \Exception('Invalid Subject ID.');
                $masterSubject = Subject::findOrFail((int) $subject->subject_id);
                
                $shortName     = $request->filled('short_name') ? trim((string) $request->short_name) : null;
                $subjectTypeId = (int) $request->subject_type_id;
                $sortOrder     = (int) ($request->sort_order ?? 0);
                $isOptional    = $request->has('is_optional') ? 1 : 0;
                $isActive      = $request->has('is_active') ? 1 : 0;

                // 1. Update Master Subject (NO MORE FORKING!)
                $updateData = [
                    'subject_code'    => $subjectCode,
                    'short_name'      => $shortName,
                    'subject_type_id' => $subjectTypeId,
                    'display_order'   => $sortOrder,
                    'is_active'       => $isActive,
                ];
                if ($subjectName !== trim((string)$masterSubject->subject_name)) {
                    $updateData['subject_name'] = $subjectName;
                }
                $masterSubject->update($updateData);

                // 2. If specific standard mapping was edited
                if (!$isAllStandards) {
                    $standard = Standard::findOrFail((int) $request->standard_id);
                    $subject->update([
                        'standard_id'   => $standard->id,
                        'subject_id'    => $masterSubject->id,
                        'standard_name' => $standard->standard_name,
                        'subject_name'  => $masterSubject->subject_name,
                        'sort_order'    => $sortOrder,
                        'is_optional'   => $isOptional,
                        'is_active'     => $isActive,
                    ]);
                } else {
                    StandardWiseSubject::where('subject_id', $masterSubject->id)->update(['sort_order' => $sortOrder, 'is_optional' => $isOptional, 'is_active' => $isActive]);
                }

                // 3. Sync the new name to ALL standard mappings instantly
                StandardWiseSubject::where('subject_id', $masterSubject->id)->update(['subject_name' => $masterSubject->subject_name]);
            });

            $this->clearSubjectCaches();
            return redirect()->route('subjects.index', ['standard_id' => $request->standard_id])->with('success', 'Subject updated successfully.');

        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateMaster(Request $request, Subject $subject)
    {
        // Orphan subject update logic remains similar...
        $request->validate([
            'subject_name' => ['required', 'string', 'max:255', Rule::unique('subjects', 'subject_name')->ignore($subject->id)],
            'subject_code' => ['required', 'string', 'max:50'],
            'subject_type_id' => ['required', 'integer', 'exists:subject_types,id'],
            'standard_ids' => ['nullable', 'array']
        ]);

        DB::beginTransaction();
        try {
            $subjectName = trim($request->subject_name);
            $updateData = ['subject_code' => strtoupper(trim($request->subject_code)), 'short_name' => $request->filled('short_name') ? trim($request->short_name) : null, 'subject_type_id' => (int) $request->subject_type_id, 'display_order' => (int) ($request->sort_order ?? 0), 'is_active' => $request->has('is_active') ? 1 : 0];
            if ($subjectName !== trim($subject->subject_name)) $updateData['subject_name'] = $subjectName;
            $subject->update($updateData);

            $desiredIds  = array_map('intval', $request->input('standard_ids', []));
            $existingIds = StandardWiseSubject::where('subject_id', $subject->id)->pluck('standard_id')->map(fn ($v) => (int) $v)->toArray();
            
            foreach (array_diff($desiredIds, $existingIds) as $standardId) {
                if ($standard = Standard::find($standardId)) $this->safeCreateMapping(['standard_id' => $standardId, 'subject_id' => $subject->id, 'standard_name' => $standard->standard_name, 'subject_name' => $subject->subject_name, 'sort_order' => 0, 'is_optional' => 0, 'is_active' => 1]);
            }
            if (!empty($toRemove = array_diff($existingIds, $desiredIds))) StandardWiseSubject::where('subject_id', $subject->id)->whereIn('standard_id', $toRemove)->delete();

            StandardWiseSubject::where('subject_id', $subject->id)->update(['subject_name' => $subject->subject_name, 'is_active' => $request->has('is_active') ? 1 : 0]);
            DB::commit(); $this->clearSubjectCaches();
            return redirect()->route('subjects.index', ['tab' => 'skill'])->with('success', "Subject updated.");
        } catch (\Throwable $e) {
            DB::rollBack(); report($e);
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(StandardWiseSubject $subject)
    {
        try {
            DB::transaction(function () use ($subject) {
                $subjectId = $subject->subject_id; $subject->delete();
                if ($subjectId && !StandardWiseSubject::where('subject_id', $subjectId)->where('is_active', 1)->exists()) {
                    Subject::where('id', $subjectId)->update(['is_active' => 0]);
                }
            });
            $this->clearSubjectCaches();
            return redirect()->route('subjects.index', ['standard_id' => request('standard_id')])->with('success', 'Deleted.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}