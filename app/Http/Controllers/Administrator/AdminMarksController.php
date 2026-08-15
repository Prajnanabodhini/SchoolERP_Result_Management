<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Subject;
use App\Models\Standard;
use App\Models\Division;
use App\Models\ExamMaster;
use App\Models\StudentMark;
use App\Models\TeacherClassAllocation;
use App\Models\TeacherSubjectAllocation;
use App\Models\TeacherMarksStatus;
use App\Models\MarkAuditLog;

use App\Helpers\StudentHelper;

class AdminMarksController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Resolve Actual Subject ID
    |--------------------------------------------------------------------------
    |
    | teacher_subject_allocations.subject_id
    | may contain standard_wise_subjects.id.
    |
    | Return actual subjects.id.
    |
    */

    private function resolveActualSubjectId($allocationSubjectId)
    {
        if (!$allocationSubjectId) {
            return null;
        }

        /*
        |----------------------------------------------------------------------
        | First check Standard Wise Subject
        |----------------------------------------------------------------------
        */

        $standardWiseSubject = DB::table(
            'standard_wise_subjects'
        )
        ->where(
            'id',
            $allocationSubjectId
        )
        ->first();

        if ($standardWiseSubject) {

            /*
            |------------------------------------------------------------------
            | Normal mapping
            |------------------------------------------------------------------
            */

            if (!empty($standardWiseSubject->subject_id)) {

                return (int) $standardWiseSubject->subject_id;
            }

            /*
            |------------------------------------------------------------------
            | Legacy mapping by subject name
            |------------------------------------------------------------------
            */

            if (!empty($standardWiseSubject->subject_name)) {

                $subjectName = strtoupper(
                    trim(
                        $standardWiseSubject->subject_name
                    )
                );

                $subject = Subject::whereRaw(
                    'UPPER(TRIM(subject_name)) = ?',
                    [$subjectName]
                )->first();

                if ($subject) {

                    return (int) $subject->id;
                }
            }
        }

        /*
        |----------------------------------------------------------------------
        | Fallback
        |----------------------------------------------------------------------
        |
        | Some old records may already contain subjects.id.
        |
        */

        $subject = Subject::find(
            $allocationSubjectId
        );

        if ($subject) {

            return (int) $subject->id;
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Get Subjects
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | Return the StandardWiseSubject ID as allocation_subject_id.
    |
    */

    public function getSubjects(Request $request)
    {
        $request->validate([
            'allocation_id' => 'required'
        ]);

        $subjects =
            TeacherSubjectAllocation::query()

                ->join(
                    'standard_wise_subjects',
                    'standard_wise_subjects.id',
                    '=',
                    'teacher_subject_allocations.subject_id'
                )

                ->leftJoin(
                    'subjects',
                    'subjects.id',
                    '=',
                    'standard_wise_subjects.subject_id'
                )

                ->where(
                    'teacher_subject_allocations.teacher_class_allocation_id',
                    $request->allocation_id
                )

                ->select(
                    'teacher_subject_allocations.subject_id as allocation_subject_id',

                    DB::raw(
                        'COALESCE(subjects.subject_name, standard_wise_subjects.subject_name) as subject_name'
                    )
                )

                ->distinct()

                ->orderBy(
                    'subject_name'
                )

                ->get();

        return response()->json(
            $subjects
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Admin Marks Correction Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $exams =
            ExamMaster::where(
                'is_active',
                1
            )
            ->orderBy(
                'display_order'
            )
            ->get();


        $classAllocations =
            TeacherClassAllocation::with([
                'teacher',
                'standard',
                'division',
                'academicYear'
            ])
            ->orderBy(
                'id',
                'desc'
            )
            ->get();


        return view(
            'administrator.marks.index',
            compact(
                'exams',
                'classAllocations'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Edit Marks
    |--------------------------------------------------------------------------
    */

    public function edit(Request $request)
    {
        $request->validate([
            'teacher_class_allocation_id' => 'required',
            'subject_id' => 'required',
            'exam_master_id' => 'required'
        ]);


        /*
        |----------------------------------------------------------------------
        | Find Teacher Subject Allocation
        |----------------------------------------------------------------------
        |
        | subject_id here is:
        |
        | standard_wise_subjects.id
        |
        */

        $allocation =
            TeacherSubjectAllocation::with([
                'teacherClassAllocation.teacher',
                'teacherClassAllocation.standard',
                'teacherClassAllocation.division',
                'teacherClassAllocation.academicYear',
                'standardWiseSubject.subject'
            ])
            ->where(
                'teacher_class_allocation_id',
                $request->teacher_class_allocation_id
            )
            ->where(
                'subject_id',
                $request->subject_id
            )
            ->first();


        if (!$allocation) {

            return redirect()
                ->route(
                    'result-generation.admin-marks.index'
                )
                ->with(
                    'error',
                    'Subject Allocation Not Found.'
                );
        }


        /*
        |----------------------------------------------------------------------
        | Class Allocation
        |----------------------------------------------------------------------
        */

        $classAllocation =
            $allocation->teacherClassAllocation;


        if (!$classAllocation) {

            return redirect()
                ->route(
                    'result-generation.admin-marks.index'
                )
                ->with(
                    'error',
                    'Teacher Class Allocation Not Found.'
                );
        }


        /*
        |----------------------------------------------------------------------
        | Actual Subject ID
        |----------------------------------------------------------------------
        */

        $actualSubjectId =
            $this->resolveActualSubjectId(
                $allocation->subject_id
            );


        if (!$actualSubjectId) {

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Unable to resolve Subject Master ID.'
                );
        }


        /*
        |----------------------------------------------------------------------
        | Subject
        |----------------------------------------------------------------------
        */

        $subject =
            Subject::find(
                $actualSubjectId
            );


        /*
        |----------------------------------------------------------------------
        | Exam
        |----------------------------------------------------------------------
        */

        $exam =
            ExamMaster::find(
                $request->exam_master_id
            );


        if (!$exam) {

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Exam Master not found.'
                );
        }


        /*
        |----------------------------------------------------------------------
        | Load Student Marks
        |----------------------------------------------------------------------
        |
        | student_marks.subject_id uses ACTUAL subjects.id.
        |
        */

        $students =
            StudentMark::where(
                'teacher_subject_allocation_id',
                $allocation->id
            )
            ->where(
                'exam_master_id',
                $request->exam_master_id
            )
            ->where(
                'subject_id',
                $actualSubjectId
            )
            ->orderBy(
                'student_id'
            )
            ->get();


        /*
        |----------------------------------------------------------------------
        | ERP Students
        |----------------------------------------------------------------------
        */

        $erpStudents =
            StudentHelper::getStudentsDirectERP(
                $classAllocation->academic_year_id,
                $classAllocation->standard_id,
                $classAllocation->division_id
            );


        /*
        |----------------------------------------------------------------------
        | Girls First
        |----------------------------------------------------------------------
        */

        $erpStudents =
            $erpStudents
            ->sort(function ($a, $b) {

                $genderA = strtoupper(
                    trim(
                        $a->gender ?? ''
                    )
                );

                $genderB = strtoupper(
                    trim(
                        $b->gender ?? ''
                    )
                );


                if ($genderA !== $genderB) {

                    if (
                        in_array(
                            $genderA,
                            ['F', 'FEMALE']
                        )
                    ) {
                        return -1;
                    }

                    if (
                        in_array(
                            $genderB,
                            ['F', 'FEMALE']
                        )
                    ) {
                        return 1;
                    }
                }


                return strcmp(
                    strtoupper(
                        trim(
                            $a->studname ?? ''
                        )
                    ),
                    strtoupper(
                        trim(
                            $b->studname ?? ''
                        )
                    )
                );
            })
            ->keyBy(
                'Studentid'
            );


        /*
        |----------------------------------------------------------------------
        | Merge ERP Student Data
        |----------------------------------------------------------------------
        */

        foreach ($students as $student) {

            $erp =
                $erpStudents[
                    $student->student_id
                ] ?? null;


            $student->rollno =
                $erp->rollno ?? '';

            $student->regno =
                $erp->regno ?? '';

            $student->studname =
                $erp->studname ?? '';

            $student->fathername =
                $erp->fathername ?? '';

            $student->gender =
                $erp->gender ?? '';
        }


        /*
        |----------------------------------------------------------------------
        | Sort Students
        |----------------------------------------------------------------------
        */

        $students =
            $students
            ->sort(function ($a, $b) {

                $genderA =
                    strtoupper(
                        trim(
                            $a->gender ?? ''
                        )
                    );

                $genderB =
                    strtoupper(
                        trim(
                            $b->gender ?? ''
                        )
                    );


                if ($genderA !== $genderB) {

                    if (
                        in_array(
                            $genderA,
                            ['F', 'FEMALE']
                        )
                    ) {
                        return -1;
                    }

                    if (
                        in_array(
                            $genderB,
                            ['F', 'FEMALE']
                        )
                    ) {
                        return 1;
                    }
                }


                return strcmp(
                    strtoupper(
                        trim(
                            $a->studname ?? ''
                        )
                    ),
                    strtoupper(
                        trim(
                            $b->studname ?? ''
                        )
                    )
                );
            })
            ->values();


        /*
        |----------------------------------------------------------------------
        | Subject Configuration
        |----------------------------------------------------------------------
        */

        $subjectConfig =
            DB::table(
                'exam_master_subjects'
            )
            ->where(
                'exam_master_id',
                $exam->id
            )
            ->where(
                'standard_id',
                $classAllocation->standard_id
            )
            ->where(
                'subject_id',
                $actualSubjectId
            )
            ->first();


        /*
        |----------------------------------------------------------------------
        | Fallback exam_subjects
        |----------------------------------------------------------------------
        */

        if (!$subjectConfig) {

            $subjectConfig =
                DB::table(
                    'exam_subjects'
                )
                ->where(
                    'exam_master_id',
                    $exam->id
                )
                ->where(
                    'standard_id',
                    $classAllocation->standard_id
                )
                ->where(
                    'subject_id',
                    $actualSubjectId
                )
                ->first();
        }


        /*
        |----------------------------------------------------------------------
        | Theory
        |----------------------------------------------------------------------
        */

        $theoryMaxMarks =
            (float) (
                $subjectConfig->max_marks ?? 0
            );


        $theoryPassingMarks =
            (float) (
                $subjectConfig->passing_marks ?? 0
            );


        /*
        |----------------------------------------------------------------------
        | Oral
        |----------------------------------------------------------------------
        */

        $oralMaxMarks =
            (float) (
                $exam->oral_max_marks ?? 0
            );


        $oralPassingMarks =
            (float) (
                $exam->oral_passing_marks ?? 0
            );


        /*
        |----------------------------------------------------------------------
        | Practical
        |----------------------------------------------------------------------
        */

        $practicalMaxMarks =
            (float) (
                $exam->practical_max_marks ?? 0
            );


        $practicalPassingMarks =
            (float) (
                $exam->practical_passing_marks ?? 0
            );


        /*
        |----------------------------------------------------------------------
        | Status
        |----------------------------------------------------------------------
        */

        $statusRecord =
            TeacherMarksStatus::with(
                'teacher'
            )
            ->where(
                'teacher_subject_allocation_id',
                $allocation->id
            )
            ->where(
                'exam_master_id',
                $exam->id
            )
            ->first();


        /*
        |----------------------------------------------------------------------
        | Last Updated
        |----------------------------------------------------------------------
        */

        $lastUpdated =
            StudentMark::where(
                'teacher_subject_allocation_id',
                $allocation->id
            )
            ->where(
                'exam_master_id',
                $exam->id
            )
            ->max(
                'updated_at'
            );


        /*
        |----------------------------------------------------------------------
        | Return View
        |----------------------------------------------------------------------
        */

        return view(
            'administrator.marks.edit',
            compact(
                'students',
                'teacher',
                'standard',
                'division',
                'subject',
                'exam',
                'allocation',
                'academicYear',
                'statusRecord',
                'lastUpdated',
                'subjectConfig',

                'theoryMaxMarks',
                'theoryPassingMarks',

                'oralMaxMarks',
                'oralPassingMarks',

                'practicalMaxMarks',
                'practicalPassingMarks'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Marks
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        $request->validate([
            'mark_ids' => 'required|array|min:1'
        ]);


        $allocationId = null;
        $examId = null;


        DB::transaction(function () use (
            $request,
            &$allocationId,
            &$examId
        ) {

            foreach ($request->mark_ids as $id) {

                $mark =
                    StudentMark::find($id);


                if (!$mark) {
                    continue;
                }


                $allocationId =
                    $mark->teacher_subject_allocation_id;

                $examId =
                    $mark->exam_master_id;


                /*
                |------------------------------------------------------------------
                | Old Values
                |------------------------------------------------------------------
                */

                $oldTheory =
                    $mark->theory_obtained_marks;

                $oldOral =
                    $mark->oral_obtained_marks;

                $oldPractical =
                    $mark->practical_obtained_marks;


                /*
                |------------------------------------------------------------------
                | Attendance
                |------------------------------------------------------------------
                */

                $isAbsent =
                    !empty(
                        $request->is_absent[$id] ?? 0
                    )
                    ? 1
                    : 0;


                /*
                |------------------------------------------------------------------
                | New Values
                |------------------------------------------------------------------
                */

                $theoryMarks =
                    $request->theory_marks[$id]
                    ?? null;

                $oralMarks =
                    $request->oral_marks[$id]
                    ?? null;

                $practicalMarks =
                    $request->practical_marks[$id]
                    ?? null;


                /*
                |------------------------------------------------------------------
                | Absent
                |------------------------------------------------------------------
                */

                if ($isAbsent) {

                    $theoryMarks = 0;

                    $oralMarks = 0;

                    $practicalMarks = 0;
                }


                /*
                |------------------------------------------------------------------
                | Update
                |------------------------------------------------------------------
                */

                $mark->update([

                    'theory_obtained_marks' =>
                        $theoryMarks,

                    'oral_obtained_marks' =>
                        $oralMarks,

                    'practical_obtained_marks' =>
                        $practicalMarks,

                    'is_absent' =>
                        $isAbsent,

                    'updated_by' =>
                        Auth::id()
                ]);


                /*
                |------------------------------------------------------------------
                | Audit
                |------------------------------------------------------------------
                */

                MarkAuditLog::create([

                    'student_mark_id' =>
                        $mark->id,

                    'student_id' =>
                        $mark->student_id,

                    'exam_master_id' =>
                        $mark->exam_master_id,

                    'subject_id' =>
                        $mark->subject_id,

                    'teacher_id' =>
                        Auth::id(),

                    'action' =>
                        'ADMIN_UPDATE',

                    'old_theory_marks' =>
                        $oldTheory,

                    'new_theory_marks' =>
                        $mark->theory_obtained_marks,

                    'old_oral_marks' =>
                        $oldOral,

                    'new_oral_marks' =>
                        $mark->oral_obtained_marks,

                    'old_practical_marks' =>
                        $oldPractical,

                    'new_practical_marks' =>
                        $mark->practical_obtained_marks,

                    'remarks' =>
                        $isAbsent
                            ? 'Admin Marks Correction - ABSENT'
                            : 'Admin Marks Correction - PRESENT',

                    'ip_address' =>
                        $request->ip(),

                    'user_agent' =>
                        $request->userAgent()
                ]);
            }
        });


        /*
        |----------------------------------------------------------------------
        | Update Status Timestamp
        |----------------------------------------------------------------------
        */

        if ($allocationId && $examId) {

            TeacherMarksStatus::where(
                'teacher_subject_allocation_id',
                $allocationId
            )
            ->where(
                'exam_master_id',
                $examId
            )
            ->update([
                'updated_at' => now()
            ]);
        }


        return back()->with(
            'success',
            'Marks Updated Successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Reopen Marks
    |--------------------------------------------------------------------------
    */

    public function reopen(Request $request)
    {
        $request->validate([
            'exam_master_id' => 'required',
            'subject_id' => 'required',
            'standard_id' => 'required',
            'division_id' => 'required'
        ]);


        /*
        |----------------------------------------------------------------------
        | IMPORTANT
        |----------------------------------------------------------------------
        |
        | Here subject_id is ACTUAL subjects.id because student_marks
        | uses actual Subject Master ID.
        |
        */

        $marks =
            StudentMark::where(
                'exam_master_id',
                $request->exam_master_id
            )
            ->where(
                'subject_id',
                $request->subject_id
            )
            ->where(
                'standard_id',
                $request->standard_id
            )
            ->where(
                'division_id',
                $request->division_id
            )
            ->get();


        if ($marks->isEmpty()) {

            return back()->with(
                'error',
                'No marks found for the selected Subject.'
            );
        }


        DB::transaction(function () use (
            $request,
            $marks
        ) {

            foreach ($marks as $mark) {

                $mark->update([

                    'is_locked' =>
                        0,

                    'updated_by' =>
                        Auth::id()
                ]);


                MarkAuditLog::create([

                    'student_mark_id' =>
                        $mark->id,

                    'student_id' =>
                        $mark->student_id,

                    'exam_master_id' =>
                        $mark->exam_master_id,

                    'subject_id' =>
                        $mark->subject_id,

                    'teacher_id' =>
                        Auth::id(),

                    'action' =>
                        'REOPEN',

                    'remarks' =>
                        $request->remarks
                        ??
                        'Marks reopened by admin',

                    'ip_address' =>
                        $request->ip(),

                    'user_agent' =>
                        $request->userAgent()
                ]);
            }


            /*
            |------------------------------------------------------------------
            | Find Teacher Subject Allocation IDs
            |------------------------------------------------------------------
            */

            $allocationIds =
                $marks
                ->pluck(
                    'teacher_subject_allocation_id'
                )
                ->filter()
                ->unique()
                ->values();


            /*
            |------------------------------------------------------------------
            | Change Status
            |------------------------------------------------------------------
            */

            if ($allocationIds->isNotEmpty()) {

                TeacherMarksStatus::where(
                    'exam_master_id',
                    $request->exam_master_id
                )
                ->whereIn(
                    'teacher_subject_allocation_id',
                    $allocationIds
                )
                ->update([
                    'status' => 'PENDING',
                    'updated_at' => now()
                ]);
            }
        });


        return back()->with(
            'success',
            'Marks reopened successfully.'
        );
    }
}