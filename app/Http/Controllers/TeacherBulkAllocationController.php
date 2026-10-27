<?php

namespace App\Http\Controllers;

use App\Helpers\TeacherBulkHelper;
use App\Helpers\AllocationStatusHelper;
use App\Helpers\StudentHelper;
use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\ExamMaster;
use App\Models\Standard;
use App\Models\TeacherClassAllocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class TeacherBulkAllocationController extends Controller
{
    /* =====================================================================
     | INDEX
     ===================================================================== */

    public function index(Request $request)
    {
        if ($request->filled('reset')) {
            return redirect()->route('teacher-bulk-allocation.index');
        }

        $hasAnyFilterValue =
            $request->filled('academic_year_id') ||
            $request->filled('standard_id')      ||
            $request->filled('division_id')      ||
            $request->filled('exam_master_id');

        $hasQueryString = count($request->query()) > 0;

        if ($hasQueryString && !$hasAnyFilterValue && !$request->filled('page')) {
            return redirect()->route('teacher-bulk-allocation.index');
        }

        $academicYearId = $request->input('academic_year_id', '');
        $standardId     = $request->input('standard_id', '');
        $divisionId     = $request->input('division_id', '');
        $examMasterId   = $request->input('exam_master_id', '');

        $exams = TeacherBulkHelper::exams($academicYearId, $standardId);

        $pairQuery = DB::table('teacher_class_allocations as tca')
            ->join(
                'teacher_subject_allocations as tsa',
                'tsa.teacher_class_allocation_id',
                '=',
                'tca.id'
            )
            ->select(
                'tca.id as allocation_id',
                DB::raw('COALESCE(tsa.exam_master_id, 0) as exam_master_id'),
                DB::raw('MIN(tsa.id) as first_tsa_id')
            )
            ->groupBy(
                'tca.id',
                DB::raw('COALESCE(tsa.exam_master_id, 0)')
            );

        if ($academicYearId !== '') {
            $pairQuery->where('tca.academic_year_id', (int) $academicYearId);
        }
        if ($standardId !== '') {
            $pairQuery->where('tca.standard_id', (int) $standardId);
        }
        if ($divisionId !== '') {
            $pairQuery->where('tca.division_id', (int) $divisionId);
        }
        if ($examMasterId !== '') {
            $pairQuery->where('tsa.exam_master_id', (int) $examMasterId);
        }

        $pairQuery
            ->orderByRaw('MIN(tsa.id) DESC')
            ->orderByDesc('tca.id');

        $pairs = $pairQuery->paginate(25)->withQueryString();

        $allocationIds = collect($pairs->items())
            ->pluck('allocation_id')
            ->unique()
            ->values();

        $allocationModels = TeacherClassAllocation::with([
            'teacher',
            'academicYear',
            'section',
            'standard',
            'division',
            'subjectAllocations.exam',
        ])
            ->whereIn('id', $allocationIds)
            ->get()
            ->keyBy('id');

        $rows = collect();

        foreach ($pairs->items() as $pair) {
            $alloc = $allocationModels->get($pair->allocation_id);
            if (!$alloc) continue;

            $clone = clone $alloc;

            $clone->setRelation(
                'subjectAllocations',
                $alloc->subjectAllocations
                    ->filter(function ($tsa) use ($pair) {
                        return (int) ($tsa->exam_master_id ?? 0)
                            === (int) $pair->exam_master_id;
                    })
                    ->values()
            );

            $clone->displayExamMasterId = (int) $pair->exam_master_id;
            $clone->displayRowId        = (int) $pair->first_tsa_id;

            $rows->push($clone);
        }

        $pairs->setCollection($rows);

        TeacherBulkHelper::prepareIndexAllocations($pairs);

        $pendingCount  = 0;
        $subjectStatus = [];
        $pendingExam   = null;

        if ($examMasterId !== '') {
            $pendingExam = ExamMaster::find((int) $examMasterId);

            if ($pendingExam) {
                $pendingCount  = max(0, (int) AllocationStatusHelper::pendingCountForExam((int) $examMasterId));
                $subjectStatus = collect(
                    AllocationStatusHelper::subjectStatusForExam((int) $examMasterId)
                )
                    ->map(fn ($r) => is_object($r) ? (array) $r : $r)
                    ->map(fn ($r) => (object) $r)
                    ->values()
                    ->all();
            }
        }

        $pendingSummary = [];
        $totalPending   = 0;

        if ($examMasterId === '') {
            $yearFilter = $academicYearId !== '' ? (int) $academicYearId : null;

            $pendingSummaryRows = AllocationStatusHelper::summaryForAcademicYear($yearFilter);

            $pendingSummaryRows = collect($pendingSummaryRows)
                ->map(fn ($r) => is_object($r) ? (array) $r : $r)
                ->values()
                ->all();

            $totalPending = array_sum(array_column($pendingSummaryRows, 'pending'));

            $pendingSummary = array_map(fn ($r) => (object) $r, $pendingSummaryRows);
        }

        return response()
            ->view('administrator.teacher-bulk-allocation.index', [
                'allocations'    => $pairs,
                'standards'      => TeacherBulkHelper::standards(),
                'academicYears'  => TeacherBulkHelper::academicYears(),
                'divisions'      => TeacherBulkHelper::divisions(),
                'exams'          => $exams,
                'academicYearId' => $academicYearId,
                'standardId'     => $standardId,
                'divisionId'     => $divisionId,
                'examMasterId'   => $examMasterId,
                'pendingCount'   => $pendingCount,
                'subjectStatus'  => $subjectStatus,
                'pendingExam'    => $pendingExam,
                'pendingSummary' => $pendingSummary,
                'totalPending'   => $totalPending,
            ])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    /* =====================================================================
     | CREATE
     ===================================================================== */

    public function create(Request $request)
    {
        $selectedAcademicYearId = $request->input('academic_year_id');

        return view('administrator.teacher-bulk-allocation.create', [
            'teachers'               => TeacherBulkHelper::teachers(),
            'academicYears'          => TeacherBulkHelper::academicYears(),
            'sections'               => TeacherBulkHelper::sections(),
            'standards'              => TeacherBulkHelper::standards(),
            'divisions'              => TeacherBulkHelper::divisions(),
            'divisionsByStandard'    => [],
            'exams'                  => TeacherBulkHelper::exams($selectedAcademicYearId),
            'selectedAcademicYearId' => $selectedAcademicYearId,
        ]);
    }

    /* =====================================================================
     | GET EXAM DETAILS
     ===================================================================== */

    public function getExamDetails(Request $request)
    {
        try {
            $request->validate([
                'exam_master_id'   => 'required|exists:exam_masters,id',
                'academic_year_id' => 'nullable|exists:academic_years,id',
            ]);

            $exam = ExamMaster::findOrFail($request->exam_master_id);
            $examAcademicYearId = (int) ($exam->academic_year_id ?? 0);

            if ($examAcademicYearId <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected Exam does not have an Academic Year assigned.',
                    'standard' => null, 'section' => null, 'divisions' => [], 'subjects' => [],
                ], 422);
            }

            $standard = Standard::where('id', $exam->standard_id)
                ->where('is_active', 1)
                ->first();

            if (!$standard) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Standard is assigned to this Exam.',
                    'standard' => null, 'section' => null, 'divisions' => [], 'subjects' => [],
                ], 422);
            }

            $section = $standard->section_id
                ? \App\Models\Section::find($standard->section_id)
                : null;

            $erpDivisions = StudentHelper::getERPDivisionsForStandard(
                $examAcademicYearId,
                (int) $standard->id
            );

            $divisions = collect($erpDivisions ?? [])->values();

            $subjects = collect(TeacherBulkHelper::getExamMappedSubjects($exam->id, $standard->id));

            return response()->json([
                'success'          => true,
                'academic_year_id' => $examAcademicYearId,
                'exam'             => [
                    'id'               => (int) $exam->id,
                    'exam_name'        => $exam->exam_name,
                    'academic_year_id' => $examAcademicYearId,
                ],
                'standard'         => [
                    'id'            => (int) $standard->id,
                    'standard_name' => $standard->standard_name,
                ],
                'section'          => $section ? [
                    'id'           => (int) $section->id,
                    'section_name' => $section->section_name,
                ] : null,
                'divisions'        => $divisions,
                'subjects'         => $subjects->map(fn ($subject) => [
                    'subject_id'      => (int) ($subject->subject_id ?? 0),
                    'subject_name'    => $subject->subject_name ?? '-',
                    'subject_code'    => $subject->subject_code ?? '',
                    'short_name'      => $subject->short_name ?? '',
                    'is_optional'     => (int) ($subject->is_optional ?? 0),
                    'exam_subject_id' => (int) ($subject->exam_subject_id ?? 0),
                    'max_marks'       => $subject->max_marks ?? 100,
                    'passing_marks'   => $subject->passing_marks ?? 35,
                    'display_order'   => $subject->display_order ?? 0,
                ])->values(),
            ]);
        } catch (Throwable $e) {
            Log::error('getExamDetails Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage(),
                'subjects' => []
            ], 500);
        }
    }

    /* =====================================================================
     | GET STANDARDS
     ===================================================================== */

    public function getStandards(Request $request)
    {
        $sectionId = $request->input('section_id');
        if (!$sectionId) return response()->json([]);

        return response()->json(
            Standard::where('section_id', $sectionId)
                ->where('is_active', 1)
                ->orderBy('display_order')
                ->get(['id', 'standard_name'])
        );
    }

    /* =====================================================================
     | GET SUBJECTS
     ===================================================================== */

    public function getSubjects(Request $request)
    {
        try {
            $examMasterId = $request->input('exam_master_id');

            if (!$examMasterId) {
                return response()->json(['success' => false, 'message' => 'Exam is required.', 'subjects' => []], 422);
            }

            $exam = ExamMaster::find($examMasterId);
            if (!$exam) {
                return response()->json(['success' => false, 'message' => 'Selected Exam was not found.', 'subjects' => []], 404);
            }

            $examYearId = (int) ($exam->academic_year_id ?? 0);
            if ($examYearId <= 0) {
                return response()->json(['success' => false, 'message' => 'Selected Exam does not have an Academic Year assigned.', 'subjects' => []], 422);
            }

            $standardId = (int) $exam->standard_id;
            if (!$standardId) {
                return response()->json(['success' => false, 'message' => 'No Standard is assigned to this Exam.', 'subjects' => []], 422);
            }

            $standard = Standard::find($standardId);
            if (!$standard) {
                return response()->json(['success' => false, 'message' => 'Standard not found.', 'subjects' => []], 404);
            }

            $subjects = collect(TeacherBulkHelper::getExamMappedSubjects($examMasterId, $standardId));

            return response()->json([
                'success'          => true,
                'academic_year_id' => $examYearId,
                'exam'             => [
                    'id'               => (int) $exam->id,
                    'exam_name'        => $exam->exam_name,
                    'academic_year_id' => $examYearId,
                ],
                'standard'         => [
                    'id'            => (int) $standard->id,
                    'standard_name' => $standard->standard_name,
                ],
                'subjects'         => $subjects->map(fn ($subject) => [
                    'mapping_id'    => $subject->mapping_id ?? null,
                    'subject_id'    => (int) ($subject->subject_id ?? 0),
                    'standard'      => $subject->standard_name ?? '',
                    'subject_name'  => $subject->subject_name ?? '-',
                    'subject_code'  => $subject->subject_code ?? '',
                    'short_name'    => $subject->short_name ?? '',
                    'is_optional'   => (int) ($subject->is_optional ?? 0),
                    'max_marks'     => $subject->max_marks ?? 100,
                    'passing_marks' => $subject->passing_marks ?? 35,
                    'display_order' => $subject->display_order ?? 0,
                ])->values(),
            ]);
        } catch (Throwable $e) {
            Log::error('getSubjects Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage(),
                'subjects' => []
            ], 500);
        }
    }

    /* =====================================================================
     | STORE
     ===================================================================== */

    public function store(Request $request)
    {
        $request->validate([
            'user_id'            => 'required|exists:users,id',
            'academic_year_id'   => 'required|exists:academic_years,id',
            'exam_master_id'     => 'required|exists:exam_masters,id',
            'rows'               => 'required|array|min:1',
            'rows.*.standards'   => 'required|array|min:1',
            'rows.*.standards.*' => 'required|exists:standards,id',
            'rows.*.divisions'   => 'required|array|min:1',
            'rows.*.divisions.*' => 'required|exists:divisions,id',
            'rows.*.subjects'    => 'required|array|min:1',
            'rows.*.subjects.*'  => 'required|exists:subjects,id',
        ]);

        $errors = $this->validateDivisionsHaveStudents($request);
        if (!empty($errors)) {
            return back()->withInput()->with('error', implode(' ', $errors));
        }

        try {
            DB::transaction(function () use ($request) {
                foreach ($request->rows as $row) {
                    $standardId = (int) $row['standards'][0];
                    $sectionId  = $row['section_id'] ?? null;
                    
                    // Prevent duplicate subjects in the array
                    $uniqueSubjects = array_unique($row['subjects']);

                    foreach ($row['divisions'] as $divisionId) {
                        $allocation = TeacherClassAllocation::create([
                            'user_id'          => $request->user_id,
                            'academic_year_id' => $request->academic_year_id,
                            'section_id'       => $sectionId,
                            'standard_id'      => $standardId,
                            'division_id'      => (int) $divisionId,
                        ]);

                        foreach ($uniqueSubjects as $subjectId) {
                            $conflicts = DB::table('teacher_subject_allocations as tsa')
                                ->join('teacher_class_allocations as tca', 'tsa.teacher_class_allocation_id', '=', 'tca.id')
                                ->where('tca.academic_year_id', $request->academic_year_id)
                                ->where('tca.standard_id', $standardId)
                                ->where('tca.division_id', (int) $divisionId)
                                ->where('tsa.exam_master_id', $request->exam_master_id)
                                ->where('tsa.subject_id', (int) $subjectId)
                                ->select('tsa.id', 'tsa.teacher_class_allocation_id')
                                ->get();

                            foreach ($conflicts as $conflict) {
                                DB::table('teacher_marks_status')->where('teacher_subject_allocation_id', $conflict->id)->delete();
                                DB::table('teacher_subject_allocations')->where('id', $conflict->id)->delete();
                                
                                $hasOther = DB::table('teacher_subject_allocations')->where('teacher_class_allocation_id', $conflict->teacher_class_allocation_id)->exists();
                                if (!$hasOther) {
                                    DB::table('teacher_class_allocations')->where('id', $conflict->teacher_class_allocation_id)->delete();
                                }
                            }

                            $tsaId = DB::table('teacher_subject_allocations')->insertGetId([
                                'teacher_class_allocation_id' => $allocation->id,
                                'exam_master_id'              => $request->exam_master_id,
                                'subject_id'                  => (int) $subjectId,
                                'created_at'                  => now(),
                                'updated_at'                  => now(),
                            ]);

                            DB::table('teacher_marks_status')->insert([
                                'teacher_subject_allocation_id' => $tsaId,
                                'academic_year_id'              => $request->academic_year_id,
                                'standard_id'                   => $standardId,
                                'division_id'                   => (int) $divisionId,
                                'exam_master_id'                => $request->exam_master_id,
                                'subject_id'                    => (int) $subjectId,
                                'teacher_id'                    => $request->user_id,
                                'status'                        => 'PENDING',
                                'created_at'                    => now(),
                                'updated_at'                    => now(),
                            ]);
                        }
                    }
                }
            });

            return redirect()->route('teacher-bulk-allocation.index')->with('success', 'Teacher Bulk Allocation Saved Successfully.');

        } catch (Throwable $e) {
            Log::error('Teacher Bulk Allocation Error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /* =====================================================================
     | EDIT
     ===================================================================== */

    public function edit($id)
    {
        $data = TeacherBulkHelper::buildEditData($id);

        $academicYearId =
            $data['allocation']->academic_year_id
            ?? ($data['allocations'][0]->academic_year_id ?? null);

        $divisionsByStandard = $academicYearId
            ? StudentHelper::getERPDivisionsByStandard((int) $academicYearId)
            : [];

        return view('administrator.teacher-bulk-allocation.edit', array_merge(
            $data,
            [
                'teachers'            => TeacherBulkHelper::teachers(),
                'academicYears'       => TeacherBulkHelper::academicYears(),
                'divisions'           => TeacherBulkHelper::divisions(),
                'divisionsByStandard' => $divisionsByStandard,
            ]
        ));
    }

    /* =====================================================================
     | UPDATE (FIXED AUTO-MIGRATION)
     ===================================================================== */

    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id'          => 'required|exists:users,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'standard_id'      => 'required|exists:standards,id',
            'division_id'      => 'required|exists:divisions,id',
            'exam_master_id'   => 'required|exists:exam_masters,id',
            'subjects'         => 'required|array|min:1',
            'subjects.*'       => 'required|exists:subjects,id',
        ]);

        $errors = $this->validateUpdateDivisionHasStudents($request);
        if (!empty($errors)) {
            return back()->withInput()->with('error', implode(' ', $errors));
        }

        try {
            DB::transaction(function () use ($request, $id) {
                $allocation = TeacherClassAllocation::findOrFail($id);
                
                $allocation->update([
                    'user_id'          => $request->user_id,
                    'academic_year_id' => $request->academic_year_id,
                    'standard_id'      => $request->standard_id,
                    'division_id'      => $request->division_id,
                ]);

                $examMasterId = $request->exam_master_id;
                
                // Ensure no duplicates in the incoming request
                $newSubjectIds = array_values(array_unique(array_map('intval', $request->subjects)));

                $existingAllocations = DB::table('teacher_subject_allocations')
                    ->where('teacher_class_allocation_id', $id)
                    ->where('exam_master_id', $examMasterId)
                    ->get();

                $existingSubjectIds = $existingAllocations->pluck('subject_id')->toArray();

                // Fetch subject codes to map renamed subjects (if ID changed)
                $allSubjectIds = array_merge($existingSubjectIds, $newSubjectIds);
                $subjectCodes = DB::table('subjects')->whereIn('id', $allSubjectIds)->pluck('subject_code', 'id')->toArray();

                $newIdsByCode = [];
                foreach ($newSubjectIds as $newId) {
                    $code = $subjectCodes[$newId] ?? null;
                    if ($code) $newIdsByCode[$code] = $newId;
                }

                $processedNewIds = [];
                
                foreach ($existingAllocations as $existing) {
                    $oldId = (int) $existing->subject_id;
                    $oldCode = $subjectCodes[$oldId] ?? null;

                    // 1. Keep if exact ID is selected
                    if (in_array($oldId, $newSubjectIds, true)) {
                        $processedNewIds[] = $oldId;
                    }
                    // 2. Auto-Migrate: If ID changed (e.g., renamed subject with same code), update the old records to the new ID
                    elseif ($oldCode && isset($newIdsByCode[$oldCode])) {
                        $newId = $newIdsByCode[$oldCode];
                        
                        DB::table('teacher_subject_allocations')->where('id', $existing->id)->update(['subject_id' => $newId]);
                        DB::table('teacher_marks_status')->where('teacher_subject_allocation_id', $existing->id)->update(['subject_id' => $newId]);
                        
                        // Migrate historical marks so they don't disappear
                        DB::table('student_marks')
                            ->where('academic_year_id', $request->academic_year_id)
                            ->where('standard_id', $request->standard_id)
                            ->where('division_id', $request->division_id)
                            ->where('exam_master_id', $examMasterId)
                            ->where('subject_id', $oldId)
                            ->update(['subject_id' => $newId]);

                        $processedNewIds[] = $newId;
                    }
                    // 3. Remove unchecked subjects
                    else {
                        DB::table('teacher_marks_status')->where('teacher_subject_allocation_id', $existing->id)->delete();
                        DB::table('teacher_subject_allocations')->where('id', $existing->id)->delete();
                    }
                }

                // 4. Add completely new subjects
                $subjectsToAdd = array_diff($newSubjectIds, $processedNewIds);

                foreach ($subjectsToAdd as $subId) {
                    $conflicts = DB::table('teacher_subject_allocations as tsa')
                        ->join('teacher_class_allocations as tca', 'tsa.teacher_class_allocation_id', '=', 'tca.id')
                        ->where('tca.academic_year_id', $request->academic_year_id)
                        ->where('tca.standard_id', $request->standard_id)
                        ->where('tca.division_id', $request->division_id)
                        ->where('tsa.exam_master_id', $examMasterId)
                        ->where('tsa.subject_id', $subId)
                        ->select('tsa.id', 'tsa.teacher_class_allocation_id')
                        ->get();

                    foreach ($conflicts as $conflict) {
                        DB::table('teacher_marks_status')->where('teacher_subject_allocation_id', $conflict->id)->delete();
                        DB::table('teacher_subject_allocations')->where('id', $conflict->id)->delete();
                        
                        $hasOther = DB::table('teacher_subject_allocations')->where('teacher_class_allocation_id', $conflict->teacher_class_allocation_id)->exists();
                        if (!$hasOther) {
                            DB::table('teacher_class_allocations')->where('id', $conflict->teacher_class_allocation_id)->delete();
                        }
                    }

                    $tsaId = DB::table('teacher_subject_allocations')->insertGetId([
                        'teacher_class_allocation_id' => $id,
                        'exam_master_id'              => $examMasterId,
                        'subject_id'                  => $subId,
                        'created_at'                  => now(),
                        'updated_at'                  => now(),
                    ]);

                    DB::table('teacher_marks_status')->insert([
                        'teacher_subject_allocation_id' => $tsaId,
                        'academic_year_id'              => $request->academic_year_id,
                        'standard_id'                   => $request->standard_id,
                        'division_id'                   => $request->division_id,
                        'exam_master_id'                => $examMasterId,
                        'subject_id'                    => $subId,
                        'teacher_id'                    => $request->user_id,
                        'status'                        => 'PENDING',
                        'created_at'                    => now(),
                        'updated_at'                    => now(),
                    ]);
                }
            });

            return redirect()->route('teacher-bulk-allocation.index')->with('success', 'Allocation updated successfully. Subject IDs and Marks were auto-migrated.');

        } catch (Throwable $e) {
            Log::error('Teacher Bulk Allocation Update Error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /* =====================================================================
     | DESTROY
     ===================================================================== */

    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $exists = DB::table('teacher_class_allocations')->where('id', $id)->exists();
                if (!$exists) {
                    throw new \RuntimeException("Allocation #{$id} not found.");
                }

                $tsaIds = DB::table('teacher_subject_allocations')->where('teacher_class_allocation_id', $id)->pluck('id')->toArray();

                if (!empty($tsaIds)) {
                    DB::table('teacher_marks_status')->whereIn('teacher_subject_allocation_id', $tsaIds)->delete();
                }

                DB::table('teacher_subject_allocations')->where('teacher_class_allocation_id', $id)->delete();
                DB::table('teacher_class_allocations')->where('id', $id)->delete();
            });

            return redirect()->route('teacher-bulk-allocation.index')->with('success', 'Allocation deleted successfully.');

        } catch (Throwable $e) {
            Log::error('Teacher Bulk Allocation Delete Error', ['message' => $e->getMessage()]);
            return redirect()->route('teacher-bulk-allocation.index')->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }

    /* =====================================================================
     | PRIVATE VALIDATION HELPERS
     ===================================================================== */

    private function validateDivisionsHaveStudents(Request $request): array
    {
        $errors = [];
        $academicYear = AcademicYear::find((int) $request->academic_year_id);
        if (!$academicYear) return $errors;

        foreach ($request->rows as $row) {
            $standardId = (int) ($row['standards'][0] ?? 0);
            if (!$standardId) continue;

            $standard = Standard::find($standardId);
            $stdName  = $standard->standard_name ?? "Std #{$standardId}";

            $validDivisions = StudentHelper::getERPDivisionsForStandard((int) $academicYear->id, $standardId);
            $validIds = collect($validDivisions ?? [])->pluck('id')->map(fn ($v) => (int) $v)->all();

            foreach ($row['divisions'] as $divisionId) {
                $divisionId = (int) $divisionId;
                if (!in_array($divisionId, $validIds, true)) {
                    $division = Division::find($divisionId);
                    $divName  = $division->division_name ?? "Division #{$divisionId}";
                    $errors[] = "Cannot save: No students found for {$stdName} - {$divName}.";
                }
            }
        }
        return array_values(array_unique($errors));
    }

    private function validateUpdateDivisionHasStudents(Request $request): array
    {
        $errors = [];
        $academicYear = AcademicYear::find((int) $request->academic_year_id);
        if (!$academicYear) return $errors;

        $standardId = (int) $request->standard_id;
        $divisionId = (int) $request->division_id;

        $standard = Standard::find($standardId);
        $division = Division::find($divisionId);

        $stdName = $standard->standard_name ?? "Std #{$standardId}";
        $divName = $division->division_name ?? "Division #{$divisionId}";

        $validDivisions = StudentHelper::getERPDivisionsForStandard((int) $academicYear->id, $standardId);
        $validIds = collect($validDivisions ?? [])->pluck('id')->map(fn ($v) => (int) $v)->all();

        if (!in_array($divisionId, $validIds, true)) {
            $errors[] = "Cannot save: No students found for {$stdName} - {$divName}.";
        }
        return $errors;
    }
}