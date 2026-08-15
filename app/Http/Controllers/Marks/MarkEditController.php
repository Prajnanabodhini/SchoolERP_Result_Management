<?php

namespace App\Http\Controllers\Marks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\StudentMark;
use App\Models\ExamMaster;
use App\Models\TeacherSubjectAllocation;
use App\Models\MarkAuditLog;

class MarkEditController extends Controller
{
    public function edit(Request $request)
    {
        $students = collect();

        $assignments = TeacherSubjectAllocation::with([
            'allocation.standard',
            'allocation.division',
            'subject'
        ])->get();

        $exams = ExamMaster::where('is_active', 1)
            ->orderBy('display_order')
            ->get();

        $exam = null;

        $showTheory = false;
        $showOral = false;
        $showPractical = false;

        $theoryMaxMarks = 0;
        $theoryPassingMarks = 0;

        $oralMaxMarks = 0;
        $oralPassingMarks = 0;

        $practicalMaxMarks = 0;
        $practicalPassingMarks = 0;

        if (
            $request->filled('exam_master_id')
            &&
            $request->filled('teacher_subject_allocation_id')
        ) {

            $exam = ExamMaster::find(
                $request->exam_master_id
            );

            if ($exam) {

                $showTheory =
                    (bool)$exam->has_theory;

                $showOral =
                    (bool)$exam->has_oral;

                $showPractical =
                    (bool)$exam->has_practical;

                $theoryMaxMarks =
                    $exam->theory_max_marks ?? 0;

                $theoryPassingMarks =
                    $exam->theory_passing_marks ?? 0;

                $oralMaxMarks =
                    $exam->oral_max_marks ?? 0;

                $oralPassingMarks =
                    $exam->oral_passing_marks ?? 0;

                $practicalMaxMarks =
                    $exam->practical_max_marks ?? 0;

                $practicalPassingMarks =
                    $exam->practical_passing_marks ?? 0;
            }

            $teacherSubjectAllocation =
                TeacherSubjectAllocation::with([
                    'allocation.standard',
                    'allocation.division',
                    'subject'
                ])
                ->find(
                    $request->teacher_subject_allocation_id
                );

            if ($teacherSubjectAllocation) {

                $allocation =
                    $teacherSubjectAllocation->allocation;

                /*
            |--------------------------------------------------------------------------
            | Load Students From OLD ERP
            |--------------------------------------------------------------------------
            */
                $savedMarks = StudentMark::where(
                    'exam_master_id',
                    $request->exam_master_id
                )
                    ->where(
                        'teacher_subject_allocation_id',
                        $request->teacher_subject_allocation_id
                    )
                    ->get();

                $studentIds = $savedMarks
                    ->pluck('student_id')
                    ->unique()
                    ->toArray();

                $students = DB::connection('sqlsrv_olderp')
                    ->table('SubStudentMst as s')
                    ->join(
                        'FeeMstStudent as f',
                        'f.Studentid',
                        '=',
                        's.Studentid'
                    )
                    ->whereIn(
                        's.Studentid',
                        $studentIds
                    )
                    ->select(
                        's.Studentid',
                        's.regno',
                        's.rollno',
                        'f.studname'
                    )
                    ->orderByRaw('CAST(s.rollno AS INT)')
                    ->get();

                $savedMarks = $savedMarks->keyBy('student_id');

                foreach ($students as $student) {
                    $mark =
                        $savedMarks[$student->Studentid]
                        ?? null;

                    $student->mark_id =
                        $mark->id ?? null;

                    $student->theory_obtained_marks =
                        $mark->theory_obtained_marks ?? '';

                    $student->oral_obtained_marks =
                        $mark->oral_obtained_marks ?? '';

                    $student->practical_obtained_marks =
                        $mark->practical_obtained_marks ?? '';
                }

                /*
            |--------------------------------------------------------------------------
            | Load Saved Marks
            |--------------------------------------------------------------------------
            */

                $savedMarks =
                    $savedMarks->keyBy('student_id');

                foreach ($students as $student) {
                    $mark =
                        $savedMarks[$student->Studentid]
                        ?? null;

                    $student->mark_id =
                        $mark->id ?? null;

                    $student->theory_obtained_marks =
                        $mark->theory_obtained_marks ?? '';

                    $student->oral_obtained_marks =
                        $mark->oral_obtained_marks ?? '';

                    $student->practical_obtained_marks =
                        $mark->practical_obtained_marks ?? '';
                }

                foreach ($students as $student) {
                    // dd([
                    //     'student_helper_ids' =>
                    //     $students->pluck('Studentid')->take(20)->toArray(),

                    //     'saved_mark_ids' =>
                    //     $savedMarks->pluck('student_id')->take(20)->toArray(),
                    // ]);
                    if (isset($savedMarks[$student->Studentid])) {

                        // dd([
                        //     'student_helper_ids' =>
                        //     $students->pluck('Studentid')->take(20),

                        //     'saved_mark_ids' =>
                        //     $savedMarks->pluck('student_id')->take(20),
                        // ]);
                    }
                }
                /*
            |--------------------------------------------------------------------------
            | Debug (remove after testing)
            |--------------------------------------------------------------------------
            */
                // dd([
                //     'students_count' => count($students),
                //     'first_student' => $students->first()
                // ]);
            }
        }

        $marksLocked = false;

        return view(
            'marks-entry.edit',
            compact(
                'students',
                'marksLocked',
                'assignments',
                'exams',
                'exam',
                'showTheory',
                'showOral',
                'showPractical',
                'theoryMaxMarks',
                'theoryPassingMarks',
                'oralMaxMarks',
                'oralPassingMarks',
                'practicalMaxMarks',
                'practicalPassingMarks'
            )
        );
    }


    public function updateMarks(Request $request)
    {
        foreach ($request->mark_ids as $id) {

            $mark = StudentMark::find($id);

            if (!$mark) {
                continue;
            }

            $oldTheory =
    $mark->theory_obtained_marks;

$oldOral =
    $mark->oral_obtained_marks;

$oldPractical =
    $mark->practical_obtained_marks;

$mark->update([

    'theory_obtained_marks' =>
        $request->theory_marks[$id] ?? null,

    'oral_obtained_marks' =>
        $request->oral_marks[$id] ?? null,

    'practical_obtained_marks' =>
        $request->practical_marks[$id] ?? null,

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
        'TEACHER_UPDATE',

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
        'Teacher Marks Update',

    'ip_address' =>
        request()->ip(),

    'user_agent' =>
        request()->userAgent()
]);
        }

        return redirect()
            ->back()
            ->with(
                'success',
                'Marks Updated Successfully.'
            );
    }
}
