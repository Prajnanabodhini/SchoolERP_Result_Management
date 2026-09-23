<?php

namespace App\Http\Controllers;

use App\Helpers\TeacherBulkHelper;
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
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $academicYearId = $request->input('academic_year_id', '');
        $standardId     = $request->input('standard_id', '');
        $examMasterId   = $request->input('exam_master_id', '');

        $exams = TeacherBulkHelper::exams($academicYearId, $standardId);

        /*
        |--------------------------------------------------------------------------
        | PAGINATE BY (CLASS ALLOCATION, EXAM) PAIRS
        |--------------------------------------------------------------------------
        |
        | A single TeacherClassAllocation can have TSAs across multiple exams.
        | We want ONE row per (allocation + exam) so a teacher allocated for
        | both Unit Test 1 and Term 1 gets two separate rows.
        |
        | Sort by "latest activity" — GREATEST(TSA.updated_at, TSA.created_at,
        | TCA.updated_at, TCA.created_at) so recently-edited allocations
        | bubble to the top.
        |
        */

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
                DB::raw(
                    'MAX(
                        GREATEST(
                            COALESCE(tsa.updated_at, tsa.created_at),
                            COALESCE(tca.updated_at, tca.created_at)
                        )
                    ) as last_touched'
                )
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

        if ($examMasterId !== '') {
            $pairQuery->where('tsa.exam_master_id', (int) $examMasterId);
        }

        $pairQuery
            ->orderByDesc('last_touched')
            ->orderByDesc('tca.id');

        $pairs = $pairQuery
            ->paginate(25)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | LOAD THE CLASS ALLOCATIONS FOR THIS PAGE
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | BUILD ONE DISPLAY ROW PER (ALLOCATION, EXAM)
        |--------------------------------------------------------------------------
        */

        $rows = collect();

        foreach ($pairs->items() as $pair) {

            $alloc = $allocationModels->get($pair->allocation_id);
            if (!$alloc) {
                continue;
            }

            /* Shallow clone so we don't mutate the shared model */
            $clone = clone $alloc;

            /* Keep ONLY the TSAs that belong to this exam */
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

            $rows->push($clone);
        }

        /* Swap the paginator's items so the blade renders our split rows */
        $pairs->setCollection($rows);

        /*
        |--------------------------------------------------------------------------
        | COMPUTE displaySubjects FOR EACH ROW
        |--------------------------------------------------------------------------
        */

        TeacherBulkHelper::prepareIndexAllocations($pairs);

        return view('administrator.teacher-bulk-allocation.index', [
            'allocations'    => $pairs,
            'standards'      => TeacherBulkHelper::standards(),
            'academicYears'  => TeacherBulkHelper::academicYears(),
            'exams'          => $exams,
            'academicYearId' => $academicYearId,
            'standardId'     => $standardId,
            'examMasterId'   => $examMasterId,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create(Request $request)
    {
        $selectedAcademicYearId = $request->input('academic_year_id');

        return view('administrator.teacher-bulk-allocation.create', [
            'teachers'      => TeacherBulkHelper::teachers(),
            'academicYears' => TeacherBulkHelper::academicYears(),
            'sections'      => TeacherBulkHelper::sections(),
            'standards'     => TeacherBulkHelper::standards(),
            'divisions'     => TeacherBulkHelper::divisions(),
            'exams'         => TeacherBulkHelper::exams($selectedAcademicYearId),
            'selectedAcademicYearId' => $selectedAcademicYearId,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GET EXAM DETAILS
    |--------------------------------------------------------------------------
    */

    public function getExamDetails(Request $request)
    {
        $request->validate([
            'exam_master_id'   => 'required|exists:exam_masters,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
        ]);

        $exam = ExamMaster::findOrFail($request->exam_master_id);
        $examAcademicYearId = (int) ($exam->academic_year_id ?? 0);

        if ($examAcademicYearId <= 0) {
            return response()->json([
                'success'  => false,
                'message'  => 'Selected Exam does not have an Academic Year assigned.',
                'standard' => null,
                'section'  => null,
                'divisions'=> [],
                'subjects' => [],
            ], 422);
        }

        $standard = Standard::where('id', $exam->standard_id)
            ->where('is_active', 1)
            ->first();

        if (!$standard) {
            return response()->json([
                'success'  => false,
                'message'  => 'No Standard is assigned to this Exam.',
                'standard' => null,
                'section'  => null,
                'divisions'=> [],
                'subjects' => [],
            ], 422);
        }

        $section   = $standard->section_id ? \App\Models\Section::find($standard->section_id) : null;
        $divisions = TeacherBulkHelper::divisions();
        $subjects  = TeacherBulkHelper::getExamMappedSubjects($exam->id, $standard->id);

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
                'subject_id'       => (int) $subject->subject_id,
                'subject_name'     => $subject->subject_name ?: '-',
                'subject_code'     => $subject->subject_code ?: '',
                'short_name'       => $subject->short_name ?: '',
                'is_optional'      => (int) $subject->is_optional,
                'exam_subject_id'  => (int) $subject->exam_subject_id,
                'max_marks'        => $subject->max_marks,
                'passing_marks'    => $subject->passing_marks,
                'display_order'    => $subject->display_order,
            ])->values(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GET STANDARDS
    |--------------------------------------------------------------------------
    */

    public function getStandards(Request $request)
    {
        $sectionId = $request->input('section_id');

        if (!$sectionId) {
            return response()->json([]);
        }

        return response()->json(
            Standard::where('section_id', $sectionId)
                ->where('is_active', 1)
                ->orderBy('display_order')
                ->get(['id', 'standard_name'])
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GET SUBJECTS
    |--------------------------------------------------------------------------
    */

    public function getSubjects(Request $request)
    {
        $examMasterId = $request->input('exam_master_id');

        if (!$examMasterId) {
            return response()->json([
                'success'  => false,
                'message'  => 'Exam is required.',
                'subjects' => [],
            ], 422);
        }

        $exam = ExamMaster::find($examMasterId);

        if (!$exam) {
            return response()->json([
                'success'  => false,
                'message'  => 'Selected Exam was not found.',
                'subjects' => [],
            ], 404);
        }

        $examYearId = (int) ($exam->academic_year_id ?? 0);

        if ($examYearId <= 0) {
            return response()->json([
                'success'  => false,
                'message'  => 'Selected Exam does not have an Academic Year assigned.',
                'subjects' => [],
            ], 422);
        }

        $standardId = (int) $exam->standard_id;

        if (!$standardId) {
            return response()->json([
                'success'  => false,
                'message'  => 'No Standard is assigned to this Exam.',
                'subjects' => [],
            ], 422);
        }

        $standard = Standard::find($standardId);

        if (!$standard) {
            return response()->json([
                'success'  => false,
                'message'  => 'Standard not found.',
                'subjects' => [],
            ], 404);
        }

        $subjects = TeacherBulkHelper::getExamMappedSubjects($examMasterId, $standardId);

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
                'mapping_id'       => $subject->mapping_id,
                'subject_id'       => (int) $subject->subject_id,
                'standard'         => $subject->standard_name,
                'subject_name'     => $subject->subject_name ?: '-',
                'subject_code'     => $subject->subject_code ?: '',
                'short_name'       => $subject->short_name ?: '',
                'is_optional'      => (int) $subject->is_optional,
                'max_marks'        => $subject->max_marks,
                'passing_marks'    => $subject->passing_marks,
                'display_order'    => $subject->display_order,
            ])->values(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

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

        try {
            DB::transaction(function () use ($request) {
                TeacherBulkHelper::store($request);
            });

            return redirect()
                ->route('teacher-bulk-allocation.index')
                ->with('success', 'Teacher Bulk Allocation Saved Successfully.');

        } catch (Throwable $e) {
            Log::error('Teacher Bulk Allocation Error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {
        $data = TeacherBulkHelper::buildEditData($id);

        return view('administrator.teacher-bulk-allocation.edit', array_merge(
            $data,
            [
                'teachers'      => TeacherBulkHelper::teachers(),
                'academicYears' => TeacherBulkHelper::academicYears(),
                'divisions'     => TeacherBulkHelper::divisions(),
            ]
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

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
        ], [
            'user_id.required'          => 'Please select Teacher.',
            'academic_year_id.required' => 'Please select Academic Year.',
            'standard_id.required'      => 'Standard is required.',
            'division_id.required'      => 'Division is required.',
            'exam_master_id.required'   => 'Please select Exam.',
            'subjects.required'         => 'Please select at least one Subject.',
            'subjects.min'              => 'Please select at least one Subject.',
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                TeacherBulkHelper::update($request, $id);
            });

            return redirect()
                ->route('teacher-bulk-allocation.index')
                ->with('success', 'Allocation updated successfully. Existing subject allocations and marks have been preserved.');

        } catch (Throwable $e) {
            Log::error('Teacher Bulk Allocation Update Error', [
                'allocation_id' => $id,
                'message'       => $e->getMessage(),
                'trace'         => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {
            TeacherBulkHelper::destroy($id);

            return redirect()
                ->route('teacher-bulk-allocation.index')
                ->with('success', 'Allocation deleted successfully.');

        } catch (Throwable $e) {
            Log::error('Teacher Bulk Allocation Delete Error', [
                'allocation_id' => $id,
                'message'       => $e->getMessage(),
                'trace'         => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('teacher-bulk-allocation.index')
                ->with('error', $e->getMessage());
        }
    }
}