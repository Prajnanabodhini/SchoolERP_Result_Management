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
        $standardId = $request->input('standard_id', ''); 
        $examMasterId = $request->input('exam_master_id', ''); 
 
        $exams = TeacherBulkHelper::exams($academicYearId, $standardId); 
 
        $query = TeacherClassAllocation::with([ 
            'teacher', 
            'academicYear', 
            'section', 
            'standard', 
            'division', 
            'subjectAllocations.exam', 
        ]); 
 
        if ($academicYearId !== '') { 
            $query->where('academic_year_id', (int) $academicYearId); 
        } 
 
        if ($standardId !== '') { 
            $query->where('standard_id', (int) $standardId); 
        } 
 
        if ($examMasterId !== '') { 
            $query->whereHas('subjectAllocations', function ($q) use ($examMasterId) { 
                $q->where('exam_master_id', (int) $examMasterId); 
            }); 
        } 
 
        $allocations = $query->orderByDesc('id')->paginate(25)->withQueryString(); 
 
        TeacherBulkHelper::prepareIndexAllocations($allocations); 
 
        return view('administrator.teacher-bulk-allocation.index', [ 
            'allocations' => $allocations, 
            'standards' => TeacherBulkHelper::standards(), 
            'academicYears' => TeacherBulkHelper::academicYears(), 
            'exams' => $exams, 
            'academicYearId' => $academicYearId, 
            'standardId' => $standardId, 
            'examMasterId' => $examMasterId, 
        ]); 
    } 
 
    public function create(Request $request) 
    { 
        $selectedAcademicYearId = $request->input('academic_year_id'); 
 
        return view('administrator.teacher-bulk-allocation.create', [ 
            'teachers' => TeacherBulkHelper::teachers(), 
            'academicYears' => TeacherBulkHelper::academicYears(), 
            'sections' => TeacherBulkHelper::sections(), 
            'standards' => TeacherBulkHelper::standards(), 
            'divisions' => TeacherBulkHelper::divisions(), 
            'exams' => TeacherBulkHelper::exams($selectedAcademicYearId), 
            'selectedAcademicYearId' => $selectedAcademicYearId, 
        ]); 
    } 
 
    public function getExamDetails(Request $request) 
    { 
        $request->validate([ 
            'exam_master_id' => 'required|exists:exam_masters,id', 
            'academic_year_id' => 'nullable|exists:academic_years,id', 
        ]); 
 
        $exam = ExamMaster::findOrFail($request->exam_master_id); 
        $examAcademicYearId = (int) ($exam->academic_year_id ?? 0); 
 
        if ($examAcademicYearId <= 0) { 
            return response()->json([ 
                'success' => false, 
                'message' => 'Selected Exam does not have an Academic Year assigned.', 
                'standard' => null, 
                'section' => null, 
                'divisions' => [], 
                'subjects' => [], 
            ], 422); 
        } 
 
        $standard = Standard::where('id', $exam->standard_id) 
            ->where('is_active', 1) 
            ->first(); 
 
        if (!$standard) { 
            return response()->json([ 
                'success' => false, 
                'message' => 'No Standard is assigned to this Exam.', 
                'standard' => null, 
                'section' => null, 
                'divisions' => [], 
                'subjects' => [], 
            ], 422); 
        } 
 
        $section = $standard->section_id ? \App\Models\Section::find($standard->section_id) : null; 
        $divisions = TeacherBulkHelper::divisions(); 
        $subjects = TeacherBulkHelper::getExamMappedSubjects($exam->id, $standard->id); 
 
        return response()->json([ 
            'success' => true, 
            'academic_year_id' => $examAcademicYearId, 
            'exam' => [ 
                'id' => (int) $exam->id, 
                'exam_name' => $exam->exam_name, 
                'academic_year_id' => $examAcademicYearId, 
            ], 
            'standard' => [ 
                'id' => (int) $standard->id, 
                'standard_name' => $standard->standard_name, 
            ], 
            'section' => $section ? [ 
                'id' => (int) $section->id, 
                'section_name' => $section->section_name, 
            ] : null, 
            'divisions' => $divisions, 
            'subjects' => $subjects->map(fn ($subject) => [ 
                'subject_id' => (int) $subject->subject_id, 
                'subject_name' => $subject->subject_name ?: '-', 
                'subject_code' => $subject->subject_code ?: '', 
                'short_name' => $subject->short_name ?: '', 
                'is_optional' => (int) $subject->is_optional, 
                'exam_subject_id' => (int) $subject->exam_subject_id, 
                'max_marks' => $subject->max_marks, 
                'passing_marks' => $subject->passing_marks, 
                'display_order' => $subject->display_order, 
            ])->values(), 
        ]); 
    } 
 
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
 
    public function getSubjects(Request $request) 
    { 
        $examMasterId = $request->input('exam_master_id'); 
 
        if (!$examMasterId) { 
            return response()->json([ 
                'success' => false, 
                'message' => 'Exam is required.', 
                'subjects' => [], 
            ], 422); 
        } 
 
        $exam = ExamMaster::find($examMasterId); 
 
        if (!$exam) { 
            return response()->json([ 
                'success' => false, 
                'message' => 'Selected Exam was not found.', 
                'subjects' => [], 
            ], 404); 
        } 
 
        $examYearId = (int) ($exam->academic_year_id ?? 0); 
 
        if ($examYearId <= 0) { 
            return response()->json([ 
                'success' => false, 
                'message' => 'Selected Exam does not have an Academic Year assigned.', 
                'subjects' => [], 
            ], 422); 
        } 
 
        $standardId = (int) $exam->standard_id; 
 
        if (!$standardId) { 
            return response()->json([ 
                'success' => false, 
                'message' => 'No Standard is assigned to this Exam.', 
                'subjects' => [], 
            ], 422); 
        } 
 
        $standard = Standard::find($standardId); 
 
        if (!$standard) { 
            return response()->json([ 
                'success' => false, 
                'message' => 'Standard not found.', 
                'subjects' => [], 
            ], 404); 
        } 
 
        $subjects = TeacherBulkHelper::getExamMappedSubjects($examMasterId, $standardId); 
 
        return response()->json([ 
            'success' => true, 
            'academic_year_id' => $examYearId, 
            'exam' => [ 
                'id' => (int) $exam->id, 
                'exam_name' => $exam->exam_name, 
                'academic_year_id' => $examYearId, 
            ], 
            'standard' => [ 
                'id' => (int) $standard->id, 
                'standard_name' => $standard->standard_name, 
            ], 
            'subjects' => $subjects->map(fn ($subject) => [ 
                'mapping_id' => $subject->mapping_id, 
                'subject_id' => (int) $subject->subject_id, 
                'standard' => $subject->standard_name, 
                'subject_name' => $subject->subject_name ?: '-', 
                'subject_code' => $subject->subject_code ?: '', 
                'short_name' => $subject->short_name ?: '', 
                'is_optional' => (int) $subject->is_optional, 
                'max_marks' => $subject->max_marks, 
                'passing_marks' => $subject->passing_marks, 
                'display_order' => $subject->display_order, 
            ])->values(), 
        ]); 
    } 
 
    public function store(Request $request) 
    { 
        $request->validate([ 
            'user_id' => 'required|exists:users,id', 
            'academic_year_id' => 'required|exists:academic_years,id', 
            'exam_master_id' => 'required|exists:exam_masters,id', 
            'rows' => 'required|array|min:1', 
            'rows.*.standards' => 'required|array|min:1', 
            'rows.*.standards.*' => 'required|exists:standards,id', 
            'rows.*.divisions' => 'required|array|min:1', 
            'rows.*.divisions.*' => 'required|exists:divisions,id', 
            'rows.*.subjects' => 'required|array|min:1', 
            'rows.*.subjects.*' => 'required|exists:subjects,id', 
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
                'trace' => $e->getTraceAsString(), 
            ]); 
 
            return back() 
                ->withInput() 
                ->with('error', $e->getMessage()); 
        } 
    } 
 
    public function edit($id) 
    { 
        $data = TeacherBulkHelper::buildEditData($id); 
 
        return view('administrator.teacher-bulk-allocation.edit', array_merge( 
            $data, 
            [ 
                'teachers' => TeacherBulkHelper::teachers(), 
                'academicYears' => TeacherBulkHelper::academicYears(), 
                'divisions' => TeacherBulkHelper::divisions(), 
            ] 
        )); 
    } 
 
    public function update(Request $request, $id) 
    { 
        $request->validate([ 
            'user_id' => 'required|exists:users,id', 
            'academic_year_id' => 'required|exists:academic_years,id', 
            'standard_id' => 'required|exists:standards,id', 
            'division_id' => 'required|exists:divisions,id', 
            'exam_master_id' => 'required|exists:exam_masters,id', 
            'subjects' => 'required|array|min:1', 
            'subjects.*' => 'required|exists:subjects,id', 
        ], [ 
            'user_id.required' => 'Please select Teacher.', 
            'academic_year_id.required' => 'Please select Academic Year.', 
            'standard_id.required' => 'Standard is required.', 
            'division_id.required' => 'Division is required.', 
            'exam_master_id.required' => 'Please select Exam.', 
            'subjects.required' => 'Please select at least one Subject.', 
            'subjects.min' => 'Please select at least one Subject.', 
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
                'message' => $e->getMessage(), 
                'trace' => $e->getTraceAsString(), 
            ]); 
 
            return back() 
                ->withInput() 
                ->with('error', $e->getMessage()); 
        } 
    } 
 
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
                'message' => $e->getMessage(), 
                'trace' => $e->getTraceAsString(), 
            ]); 
 
            return redirect() 
                ->route('teacher-bulk-allocation.index') 
                ->with('error', $e->getMessage()); 
        } 
    } 
}
