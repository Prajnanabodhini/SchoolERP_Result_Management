<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\User;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Standard;
use App\Models\Division;
use App\Models\ExamMaster;
use App\Models\AcademicYear;
use App\Models\ExamMasterSubject;
use App\Models\TeacherClassAllocation;
use App\Models\TeacherSubjectAllocation;
use App\Models\TeacherMarksStatus;

class TeacherBulkAllocationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $allocations = TeacherClassAllocation::with([
            'teacher',
            'academicYear',
            'section',
            'standard',
            'division',
            'subjectAllocations.subject',
            'subjectAllocations.exam',
        ])
        ->orderByDesc('id')
        ->paginate(25);

        return view(
            'administrator.teacher-bulk-allocation.index',
            compact('allocations')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create(Request $request)
    {
        $teachers = User::where('role', 'Teacher')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::orderBy('year_name')
            ->get();

        $sections = Section::orderBy('display_order')
            ->get();

        $standards = Standard::where('is_active', 1)
            ->orderBy('display_order')
            ->get();

        $divisions = Division::where('is_active', 1)
            ->orderBy('display_order')
            ->get();

        $exams = ExamMaster::where('is_active', 1)
            ->orderBy('display_order')
            ->get();

        return view(
            'administrator.teacher-bulk-allocation.create',
            compact(
                'teachers',
                'academicYears',
                'sections',
                'standards',
                'divisions',
                'exams'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GET EXAM DETAILS
    |--------------------------------------------------------------------------
    |
    | Returns:
    |   standard
    |   section
    |   divisions
    |   subjects
    |
    */

    public function getExamDetails(Request $request)
    {
        $request->validate([
            'exam_master_id' => 'required|exists:exam_masters,id',
        ]);

        $exam = ExamMaster::findOrFail(
            $request->exam_master_id
        );

        /*
        |--------------------------------------------------------------------------
        | STANDARD
        |--------------------------------------------------------------------------
        */

        $standard = null;

        if ($exam->standard_id) {

            $standard = Standard::where(
                'id',
                $exam->standard_id
            )
            ->where('is_active', 1)
            ->first();
        }

        /*
        |--------------------------------------------------------------------------
        | NO STANDARD
        |--------------------------------------------------------------------------
        */

        if (!$standard) {

            return response()->json([
                'success' => false,
                'message' => 'No Standard is assigned to this Exam.',
                'standard' => null,
                'section' => null,
                'divisions' => [],
                'subjects' => [],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | SECTION
        |--------------------------------------------------------------------------
        */

        $section = null;

        if ($standard->section_id) {

            $section = Section::find(
                $standard->section_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | DIVISIONS
        |--------------------------------------------------------------------------
        |
        | If divisions are not standard-specific in your DB,
        | this returns all active divisions.
        |
        */

        $divisions = Division::where(
            'is_active',
            1
        )
        ->orderBy(
            'display_order'
        )
        ->get([
            'id',
            'division_name',
        ]);

        /*
        |--------------------------------------------------------------------------
        | SUBJECTS
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | We DO NOT use standard_wise_subjects here.
        |
        | exam_master_subjects.subject_id
        |          ↓
        | subjects.id
        |
        */

        $subjects = ExamMasterSubject::query()

            ->join(
                'subjects',
                'subjects.id',
                '=',
                'exam_master_subjects.subject_id'
            )

            ->where(
                'exam_master_subjects.exam_master_id',
                $exam->id
            )

            ->where(
                'exam_master_subjects.standard_id',
                $standard->id
            )

            ->where(
                'subjects.is_active',
                1
            )

            ->select([
                'subjects.id as subject_id',
                'subjects.subject_name',

                /*
                | Exam configuration
                */

                'exam_master_subjects.id as exam_subject_id',
                'exam_master_subjects.max_marks',
                'exam_master_subjects.passing_marks',
                'exam_master_subjects.display_order',
            ])

            ->orderBy(
                'exam_master_subjects.display_order'
            )

            ->get();

        /*
        |--------------------------------------------------------------------------
        | RETURN
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,

            'exam' => [
                'id' => $exam->id,
                'exam_name' => $exam->exam_name,
            ],

            'standard' => [
                'id' => $standard->id,
                'standard_name' => $standard->standard_name,
            ],

            'section' => $section ? [
                'id' => $section->id,
                'section_name' => $section->section_name,
            ] : null,

            'divisions' => $divisions,

            'subjects' => $subjects,
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

        $standards = Standard::where(
            'section_id',
            $sectionId
        )
        ->where(
            'is_active',
            1
        )
        ->orderBy(
            'display_order'
        )
        ->get([
            'id',
            'standard_name',
        ]);

        return response()->json(
            $standards
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GET SUBJECTS
    |--------------------------------------------------------------------------
    |
    | This method is kept for compatibility with your existing route.
    |
    | It loads subjects DIRECTLY from exam_master_subjects.
    |
    */

    public function getSubjects(Request $request)
    {
        
        $examMasterId = $request->input(
            'exam_master_id'
        );

        $standardId = $request->input(
            'standard_id'
        );

        /*
        |--------------------------------------------------------------------------
        | If standard_ids[] is being sent by old JS
        |--------------------------------------------------------------------------
        */

        if (!$standardId) {

            $standardIds = $request->input(
                'standard_ids',
                []
            );

            if (
                is_array($standardIds) &&
                count($standardIds) > 0
            ) {
                $standardId = $standardIds[0];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        if (!$examMasterId || !$standardId) {
            return response()->json([]);
        }

        /*
        |--------------------------------------------------------------------------
        | DIRECT EXAM SUBJECT QUERY
        |--------------------------------------------------------------------------
        */

        $subjects = ExamMasterSubject::query()

            ->join(
                'subjects',
                'subjects.id',
                '=',
                'exam_master_subjects.subject_id'
            )

            ->where(
                'exam_master_subjects.exam_master_id',
                $examMasterId
            )

            ->where(
                'exam_master_subjects.standard_id',
                $standardId
            )

            ->where(
                'subjects.is_active',
                1
            )

            ->select([
                'subjects.id as subject_id',
                'subjects.subject_name',

                'exam_master_subjects.id as exam_subject_id',
                'exam_master_subjects.max_marks',
                'exam_master_subjects.passing_marks',
                'exam_master_subjects.display_order',
            ])

            ->orderBy(
                'exam_master_subjects.display_order'
            )

            ->get();

        return response()->json(
            $subjects
        );
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE SUBJECT
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | incomingSubjectId = subjects.id
    |
    | teacher_subject_allocations.subject_id = subjects.id
    |
    */

    private function resolveSubject(
        $incomingSubjectId,
        $standardId,
        $examMasterId
    ) {

        if (
            !$incomingSubjectId ||
            !$standardId ||
            !$examMasterId
        ) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | GET REAL SUBJECT
        |--------------------------------------------------------------------------
        */

        $subject = Subject::where(
            'id',
            $incomingSubjectId
        )
        ->where(
            'is_active',
            1
        )
        ->first();

        if (!$subject) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | VERIFY SUBJECT BELONGS TO EXAM
        |--------------------------------------------------------------------------
        */

        $examSubject = ExamMasterSubject::where(
            'exam_master_id',
            $examMasterId
        )
        ->where(
            'standard_id',
            $standardId
        )
        ->where(
            'subject_id',
            $subject->id
        )
        ->first();

        if (!$examSubject) {
            return null;
        }

        return [
            'subject' => $subject,
            'examSubject' => $examSubject,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([

            'user_id' =>
                'required|exists:users,id',

            'academic_year_id' =>
                'required|exists:academic_years,id',

            'exam_master_id' =>
                'required|exists:exam_masters,id',

            'rows' =>
                'required|array|min:1',

            'rows.*.standards' =>
                'required|array|min:1',

            'rows.*.standards.*' =>
                'required|exists:standards,id',

            'rows.*.divisions' =>
                'required|array|min:1',

            'rows.*.divisions.*' =>
                'required|exists:divisions,id',

            /*
            | IMPORTANT:
            | Subjects are REAL subjects.id
            */

            'rows.*.subjects' =>
                'required|array|min:1',

            'rows.*.subjects.*' =>
                'required|exists:subjects,id',

        ], [

            'user_id.required' =>
                'Please select Teacher.',

            'academic_year_id.required' =>
                'Please select Academic Year.',

            'exam_master_id.required' =>
                'Please select Exam.',

            'rows.required' =>
                'Please add at least one Allocation Row.',

            'rows.*.standards.required' =>
                'Please select Standard.',

            'rows.*.divisions.required' =>
                'Please select at least one Division.',

            'rows.*.subjects.required' =>
                'Please select at least one Subject.',
        ]);


        try {

            DB::transaction(function () use ($request) {

                /*
                |--------------------------------------------------------------------------
                | EXAM
                |--------------------------------------------------------------------------
                */

                $exam = ExamMaster::findOrFail(
                    $request->exam_master_id
                );


                /*
                |--------------------------------------------------------------------------
                | EACH ROW
                |--------------------------------------------------------------------------
                */

                foreach ($request->rows as $row) {

                    /*
                    |--------------------------------------------------------------------------
                    | STANDARDS
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        $row['standards']
                        as $standardId
                    ) {

                        $standard =
                            Standard::findOrFail(
                                $standardId
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | VERIFY EXAM STANDARD
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $exam->standard_id &&
                            (int) $exam->standard_id !==
                            (int) $standardId
                        ) {

                            throw new \Exception(
                                'Selected Standard does not belong to the selected Exam.'
                            );
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | SECTION
                        |--------------------------------------------------------------------------
                        */

                        if (!$standard->section_id) {

                            throw new \Exception(
                                "Section is not assigned to Standard {$standard->standard_name}."
                            );
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | DIVISIONS
                        |--------------------------------------------------------------------------
                        */

                        foreach (
                            $row['divisions']
                            as $divisionId
                        ) {

                            $division =
                                Division::findOrFail(
                                    $divisionId
                                );


                            /*
                            |--------------------------------------------------------------------------
                            | CLASS ALLOCATION
                            |--------------------------------------------------------------------------
                            */

                            $classAllocation =
                                TeacherClassAllocation::firstOrCreate(

                                    [
                                        'user_id' =>
                                            $request->user_id,

                                        'academic_year_id' =>
                                            $request->academic_year_id,

                                        'section_id' =>
                                            $standard->section_id,

                                        'standard_id' =>
                                            $standard->id,

                                        'division_id' =>
                                            $division->id,
                                    ],

                                    [
                                        'is_class_teacher' =>
                                            !empty(
                                                $row['is_class_teacher']
                                            ),
                                    ]
                                );


                            /*
                            |--------------------------------------------------------------------------
                            | SUBJECTS
                            |--------------------------------------------------------------------------
                            */

                            foreach (
                                $row['subjects']
                                as $selectedSubjectId
                            ) {

                                /*
                                |--------------------------------------------------------------------------
                                | RESOLVE REAL SUBJECT
                                |--------------------------------------------------------------------------
                                */

                                $resolved =
                                    $this->resolveSubject(
                                        $selectedSubjectId,
                                        $standardId,
                                        $exam->id
                                    );


                                if (!$resolved) {

                                    throw new \Exception(
                                        "Subject ID {$selectedSubjectId} is not valid for {$standard->standard_name} and selected Exam."
                                    );
                                }


                                $subject =
                                    $resolved['subject'];


                                /*
                                |--------------------------------------------------------------------------
                                | DUPLICATE CHECK
                                |--------------------------------------------------------------------------
                                */

                                $exists =
                                    TeacherSubjectAllocation::where(
                                        'teacher_class_allocation_id',
                                        $classAllocation->id
                                    )
                                    ->where(
                                        'subject_id',
                                        $subject->id
                                    )
                                    ->where(
                                        'exam_master_id',
                                        $exam->id
                                    )
                                    ->exists();


                                if ($exists) {

                                    throw new \Exception(
                                        "Allocation already exists for {$standard->standard_name} - {$division->division_name} - {$subject->subject_name}"
                                    );
                                }


                                /*
                                |--------------------------------------------------------------------------
                                | CREATE TEACHER SUBJECT ALLOCATION
                                |--------------------------------------------------------------------------
                                |
                                | ONLY columns that actually exist
                                | in teacher_subject_allocations.
                                |
                                */

                                $subjectAllocation =
                                    TeacherSubjectAllocation::create([

                                        'teacher_class_allocation_id' =>
                                            $classAllocation->id,

                                        'subject_id' =>
                                            $subject->id,

                                        'exam_master_id' =>
                                            $exam->id,
                                    ]);


                                /*
                                |--------------------------------------------------------------------------
                                | CREATE MARK STATUS
                                |--------------------------------------------------------------------------
                                */

                                TeacherMarksStatus::create([

                                    'academic_year_id' =>
                                        $request->academic_year_id,

                                    'exam_master_id' =>
                                        $exam->id,

                                    'teacher_subject_allocation_id' =>
                                        $subjectAllocation->id,

                                    'standard_id' =>
                                        $standardId,

                                    'division_id' =>
                                        $divisionId,

                                    'subject_id' =>
                                        $subject->id,

                                    'teacher_id' =>
                                        $request->user_id,

                                    'status' =>
                                        'PENDING',
                                ]);
                            }
                        }
                    }
                }
            });


            return redirect()
                ->route(
                    'teacher-bulk-allocation.index'
                )
                ->with(
                    'success',
                    'Teacher Bulk Allocation Saved Successfully.'
                );

        } catch (\Throwable $e) {

            Log::error(
                'Teacher Bulk Allocation Error',
                [
                    'message' =>
                        $e->getMessage(),

                    'trace' =>
                        $e->getTraceAsString(),
                ]
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {
        $allocation =
            TeacherClassAllocation::with([
                'teacher',
                'academicYear',
                'section',
                'standard',
                'division',
                'subjectAllocations.subject',
                'subjectAllocations.exam',
            ])
            ->findOrFail($id);


        /*
        |--------------------------------------------------------------------------
        | SELECTED EXAM
        |--------------------------------------------------------------------------
        */

        $selectedExam =
            $allocation
                ->subjectAllocations
                ->first()
                ?->exam_master_id;


        /*
        |--------------------------------------------------------------------------
        | SELECTED SUBJECTS
        |--------------------------------------------------------------------------
        |
        | These are subjects.id
        |
        */

        $selectedSubjects =
            $allocation
                ->subjectAllocations
                ->pluck(
                    'subject_id'
                )
                ->filter()
                ->values()
                ->toArray();


        /*
        |--------------------------------------------------------------------------
        | DROPDOWNS
        |--------------------------------------------------------------------------
        */

        $teachers =
            User::where(
                'role',
                'Teacher'
            )
            ->where(
                'is_active',
                1
            )
            ->orderBy(
                'name'
            )
            ->get();


        $academicYears =
            AcademicYear::orderBy(
                'year_name'
            )
            ->get();


        $sections =
            Section::orderBy(
                'display_order'
            )
            ->get();


        $standards =
            Standard::where(
                'is_active',
                1
            )
            ->orderBy(
                'display_order'
            )
            ->get();


        $divisions =
            Division::where(
                'is_active',
                1
            )
            ->orderBy(
                'display_order'
            )
            ->get();


        $exams =
            ExamMaster::where(
                'is_active',
                1
            )
            ->orderBy(
                'display_order'
            )
            ->get();


        /*
        |--------------------------------------------------------------------------
        | SUBJECTS
        |--------------------------------------------------------------------------
        */

        $subjects = collect();


        if ($selectedExam) {

            $subjects =
                ExamMasterSubject::query()

                ->join(
                    'subjects',
                    'subjects.id',
                    '=',
                    'exam_master_subjects.subject_id'
                )

                ->where(
                    'exam_master_subjects.exam_master_id',
                    $selectedExam
                )

                ->where(
                    'exam_master_subjects.standard_id',
                    $allocation->standard_id
                )

                ->where(
                    'subjects.is_active',
                    1
                )

                ->select([
                    'subjects.id as subject_id',
                    'subjects.subject_name',

                    'exam_master_subjects.max_marks',
                    'exam_master_subjects.passing_marks',
                    'exam_master_subjects.display_order',
                ])

                ->orderBy(
                    'exam_master_subjects.display_order'
                )

                ->get();
        }


        return view(
            'administrator.teacher-bulk-allocation.edit',
            compact(
                'allocation',
                'teachers',
                'academicYears',
                'sections',
                'standards',
                'divisions',
                'subjects',
                'exams',
                'selectedSubjects',
                'selectedExam'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        $id
    ) {

        $request->validate([

            'user_id' =>
                'required|exists:users,id',

            'academic_year_id' =>
                'required|exists:academic_years,id',

            'standard_id' =>
                'required|exists:standards,id',

            'division_id' =>
                'required|exists:divisions,id',

            'exam_master_id' =>
                'required|exists:exam_masters,id',

            'subjects' =>
                'required|array|min:1',

            'subjects.*' =>
                'required|exists:subjects,id',
        ]);


        $allocation =
            TeacherClassAllocation::findOrFail(
                $id
            );


        $exam =
            ExamMaster::findOrFail(
                $request->exam_master_id
            );


        $standard =
            Standard::findOrFail(
                $request->standard_id
            );


        /*
        |--------------------------------------------------------------------------
        | VERIFY EXAM STANDARD
        |--------------------------------------------------------------------------
        */

        if (
            $exam->standard_id &&
            (int) $exam->standard_id !==
            (int) $request->standard_id
        ) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Selected Exam does not belong to selected Standard.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | SECTION
        |--------------------------------------------------------------------------
        */

        if (!$standard->section_id) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Section is not assigned to selected Standard.'
                );
        }


        DB::transaction(function () use (
            $request,
            $allocation,
            $exam,
            $standard
        ) {

            /*
            |--------------------------------------------------------------------------
            | UPDATE CLASS ALLOCATION
            |--------------------------------------------------------------------------
            */

            $allocation->update([

                'user_id' =>
                    $request->user_id,

                'academic_year_id' =>
                    $request->academic_year_id,

                'section_id' =>
                    $standard->section_id,

                'standard_id' =>
                    $request->standard_id,

                'division_id' =>
                    $request->division_id,
            ]);


            /*
            |--------------------------------------------------------------------------
            | OLD SUBJECT ALLOCATION IDS
            |--------------------------------------------------------------------------
            */

            $oldIds =
                TeacherSubjectAllocation::where(
                    'teacher_class_allocation_id',
                    $allocation->id
                )
                ->pluck('id');


            /*
            |--------------------------------------------------------------------------
            | DELETE MARK STATUS
            |--------------------------------------------------------------------------
            */

            if ($oldIds->isNotEmpty()) {

                TeacherMarksStatus::whereIn(
                    'teacher_subject_allocation_id',
                    $oldIds
                )
                ->delete();
            }


            /*
            |--------------------------------------------------------------------------
            | DELETE SUBJECT ALLOCATIONS
            |--------------------------------------------------------------------------
            */

            TeacherSubjectAllocation::where(
                'teacher_class_allocation_id',
                $allocation->id
            )
            ->delete();


            /*
            |--------------------------------------------------------------------------
            | CREATE NEW SUBJECT ALLOCATIONS
            |--------------------------------------------------------------------------
            */

            foreach (
                $request->subjects
                as $subjectId
            ) {

                $resolved =
                    $this->resolveSubject(
                        $subjectId,
                        $request->standard_id,
                        $exam->id
                    );


                if (!$resolved) {

                    throw new \Exception(
                        "Subject ID {$subjectId} is not valid for selected Exam/Standard."
                    );
                }


                $subject =
                    $resolved['subject'];


                /*
                |--------------------------------------------------------------------------
                | CREATE
                |--------------------------------------------------------------------------
                */

                $subjectAllocation =
                    TeacherSubjectAllocation::create([

                        'teacher_class_allocation_id' =>
                            $allocation->id,

                        'subject_id' =>
                            $subject->id,

                        'exam_master_id' =>
                            $exam->id,
                    ]);


                /*
                |--------------------------------------------------------------------------
                | MARK STATUS
                |--------------------------------------------------------------------------
                */

                TeacherMarksStatus::create([

                    'academic_year_id' =>
                        $request->academic_year_id,

                    'exam_master_id' =>
                        $exam->id,

                    'teacher_subject_allocation_id' =>
                        $subjectAllocation->id,

                    'standard_id' =>
                        $request->standard_id,

                    'division_id' =>
                        $request->division_id,

                    'subject_id' =>
                        $subject->id,

                    'teacher_id' =>
                        $request->user_id,

                    'status' =>
                        'PENDING',
                ]);
            }
        });


        return redirect()
            ->route(
                'teacher-bulk-allocation.index'
            )
            ->with(
                'success',
                'Allocation updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {

            $allocation =
                TeacherClassAllocation::findOrFail(
                    $id
                );


            /*
            |--------------------------------------------------------------------------
            | SUBJECT ALLOCATION IDS
            |--------------------------------------------------------------------------
            */

            $subjectIds =
                TeacherSubjectAllocation::where(
                    'teacher_class_allocation_id',
                    $allocation->id
                )
                ->pluck('id');


            /*
            |--------------------------------------------------------------------------
            | MARK STATUS
            |--------------------------------------------------------------------------
            */

            if ($subjectIds->isNotEmpty()) {

                TeacherMarksStatus::whereIn(
                    'teacher_subject_allocation_id',
                    $subjectIds
                )
                ->delete();
            }


            /*
            |--------------------------------------------------------------------------
            | SUBJECT ALLOCATIONS
            |--------------------------------------------------------------------------
            */

            TeacherSubjectAllocation::where(
                'teacher_class_allocation_id',
                $allocation->id
            )
            ->delete();


            /*
            |--------------------------------------------------------------------------
            | CLASS ALLOCATION
            |--------------------------------------------------------------------------
            */

            $allocation->delete();
        });


        return redirect()
            ->route(
                'teacher-bulk-allocation.index'
            )
            ->with(
                'success',
                'Allocation deleted successfully.'
            );
    }
}