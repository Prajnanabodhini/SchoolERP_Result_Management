<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\StandardSubject;
use App\Models\TeacherClassAllocation;
use App\Models\TeacherSubjectAllocation;
use App\Models\ExamMaster;
use App\Models\TeacherMarksStatus;

class TeacherSubjectAllocationController extends Controller
{
    public function index(Request $request)
{
    $exams = ExamMaster::where('is_active', 1)
        ->orderBy('display_order')
        ->get();

    $selectedExamId = $request->exam_master_id;

    $statuses = TeacherMarksStatus::with([
    'exam',
    'teacher',
    'subject',
    'standard',
    'division'
]);

if (!empty($selectedExamId)) {

    $statuses->where(
        'exam_master_id',
        $selectedExamId
    );
}

$statuses = $statuses
    ->orderBy('exam_master_id')
    ->orderBy('standard_id')
    ->orderBy('division_id')
    ->get();

    return view(
        'administrator.teacher-subject-allocation.index',
        compact(
            
            'statuses',
            'exams',
            'selectedExamId'
        )
    );
}
    public function create()
    {
        $classAllocations = TeacherClassAllocation::with([
            'teacher',
            'standard',
            'division'
        ])
        ->orderBy('id', 'desc')
        ->get();

        $exams = ExamMaster::where(
    'is_active',
    1
)
->orderBy('display_order')
->get();

return view(
    'administrator.teacher-subject-allocation.create',
    compact(
        'classAllocations',
        'exams'
    )
);
    }

    public function getSubjects($allocationId)
    {
        $allocation =
            TeacherClassAllocation::findOrFail(
                $allocationId
            );

        $subjects = Subject::join(
                'standard_subjects',
                'subjects.id',
                '=',
                'standard_subjects.subject_id'
            )
            ->where(
                'standard_subjects.standard_id',
                $allocation->standard_id
            )
            ->select(
                'subjects.id',
                'subjects.subject_name'
            )
            ->orderBy('subject_name')
            ->get();

        return response()->json($subjects);
    }

    public function store(Request $request)
    {
        $request->validate([
    'teacher_class_allocation_id' => 'required',
    'subject_id' => 'required',
    'exam_master_id' => 'required'
]);
// dd([
//     'teacher_class_allocation_id' =>
//         $request->teacher_class_allocation_id,

//     'subject_id' =>
//         $request->subject_id
// ]);
        // $exists =
        //     TeacherSubjectAllocation::where(
        //         'teacher_class_allocation_id',
        //         $request->teacher_class_allocation_id
        //     )
        //     ->where(
        //         'subject_id',
        //         $request->subject_id
        //     )
        //     ->exists();
$exists =
    TeacherSubjectAllocation::where(
        'teacher_class_allocation_id',
        $request->teacher_class_allocation_id
    )
    ->where(
        'subject_id',
        $request->subject_id
    )
    ->where(
        'exam_master_id',
        $request->exam_master_id
    )
    ->exists();

if ($exists)
{
    return back()
        ->with(
            'error',
            'Subject already allocated.'
        );
}


        $allocation =
    TeacherSubjectAllocation::create([

        'teacher_class_allocation_id' =>
            $request->teacher_class_allocation_id,

        'subject_id' =>
            $request->subject_id,

        'exam_master_id' =>
            $request->exam_master_id
    ]);

        $classAllocation =
            TeacherClassAllocation::findOrFail(
                $request->teacher_class_allocation_id
            );

        TeacherMarksStatus::create([

    'academic_year_id' =>
        $classAllocation->academic_year_id,

    'exam_master_id' =>
        $request->exam_master_id,

    'teacher_subject_allocation_id' =>
        $allocation->id,

    'standard_id' =>
        $classAllocation->standard_id,

    'division_id' =>
        $classAllocation->division_id,

    'subject_id' =>
        $allocation->subject_id,

    'teacher_id' =>
        $classAllocation->user_id,

    'status' =>
        'PENDING'
]);
        return redirect()
            ->route(
                'teacher-subject-allocation.index'
            )
            ->with(
                'success',
                'Subject Allocated Successfully.'
            );
    }
}

