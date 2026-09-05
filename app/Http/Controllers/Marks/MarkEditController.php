<?php

namespace App\Http\Controllers\Marks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\StudentMark;
use App\Models\ExamMaster;
use App\Models\TeacherSubjectAllocation;
use App\Models\TeacherMarksStatus;
use App\Models\MarkAuditLog;

use App\Helpers\ResultHelper;
use App\Helpers\MarksHelper;

class MarkEditController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | OPTIONAL FEATURE
    |--------------------------------------------------------------------------
    |
    | Optional is available ONLY for 11th and 12th.
    |
    */

    private function isOptionalEnabledForAllocation(
        $allocation
    ): bool {

        if (!$allocation) {
            return false;
        }

        $standardId =
            (int) (
                $allocation->standard_id ?? 0
            );

        /*
        |--------------------------------------------------------------------------
        | PRIMARY CHECK
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $standardId,
                [11, 12],
                true
            )
        ) {
            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | STANDARD NAME FALLBACK
        |--------------------------------------------------------------------------
        */

        $standardName =
            strtoupper(
                trim(
                    (string) optional(
                        $allocation->standard
                    )->standard_name
                )
            );

        $normalized =
            preg_replace(
                '/[\s\.\-]+/',
                ' ',
                $standardName
            );

        $normalized =
            trim(
                (string) $normalized
            );

        return in_array(
            $normalized,
            [
                '11',
                '11TH',
                'ELEVENTH',
                'XI',

                '12',
                '12TH',
                'TWELFTH',
                'XII',
            ],
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(
        Request $request
    ) {

        $isAdministrator = MarksHelper::isAdministrator();

        $students =
            collect();

        $assignments =
            TeacherSubjectAllocation::with([
                'allocation.standard',
                'allocation.division',
                'subject'
            ])
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

        $exam =
            null;

        $teacherSubjectAllocation =
            null;

        $showTheory =
            false;

        $showOral =
            false;

        $showPractical =
            false;

        $theoryMaxMarks =
            0;

        $theoryPassingMarks =
            0;

        $oralMaxMarks =
            0;

        $oralPassingMarks =
            0;

        $practicalMaxMarks =
            0;

        $practicalPassingMarks =
            0;

        /*
        |--------------------------------------------------------------------------
        | OPTIONAL BUTTON FLAG
        |--------------------------------------------------------------------------
        */

        $isOptionalEnabled =
            false;

        /*
        |--------------------------------------------------------------------------
        | PASSING PERCENTAGE
        |--------------------------------------------------------------------------
        */

        $passingPercentage =
            40;

        /*
        |--------------------------------------------------------------------------
        | MARK STATUS
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | Existing marks are authoritative.
        |
        | If marks already exist for:
        |
        | Academic Year
        | Section
        | Standard
        | Division
        | Exam
        | Subject
        |
        | then this Edit page must be locked, even when those marks
        | belong to an older teacher_subject_allocation_id.
        |
        */

        $marksLocked =
            false;

        $marksStatus =
            'PENDING';

        $existingMarks =
            collect();


        if (
            $request->filled(
                'exam_master_id'
            )
            &&
            $request->filled(
                'teacher_subject_allocation_id'
            )
        ) {

            /*
            |--------------------------------------------------------------------------
            | EXAM
            |--------------------------------------------------------------------------
            */

            $exam =
                ExamMaster::find(
                    $request->exam_master_id
                );


            if ($exam) {

                $showTheory =
                    (bool)
                    $exam->has_theory;

                $showOral =
                    (bool)
                    $exam->has_oral;

                $showPractical =
                    (bool)
                    $exam->has_practical;

                $theoryMaxMarks =
                    $exam->theory_max_marks
                    ?? 0;

                $theoryPassingMarks =
                    $exam->theory_passing_marks
                    ?? 0;

                $oralMaxMarks =
                    $exam->oral_max_marks
                    ?? 0;

                $oralPassingMarks =
                    $exam->oral_passing_marks
                    ?? 0;

                $practicalMaxMarks =
                    $exam->practical_max_marks
                    ?? 0;

                $practicalPassingMarks =
                    $exam->practical_passing_marks
                    ?? 0;
            }


            /*
            |--------------------------------------------------------------------------
            | TEACHER SUBJECT ALLOCATION
            |--------------------------------------------------------------------------
            */

            $teacherSubjectAllocation =
                TeacherSubjectAllocation::with([
                    'allocation.standard',
                    'allocation.division',
                    'allocation.section',
                    'subject'
                ])
                ->find(
                    $request->teacher_subject_allocation_id
                );


            if ($teacherSubjectAllocation) {

                $allocation =
                    $teacherSubjectAllocation
                        ->allocation;


                /*
                |--------------------------------------------------------------------------
                | OPTIONAL FEATURE
                |--------------------------------------------------------------------------
                */

                $isOptionalEnabled =
                    $this->isOptionalEnabledForAllocation(
                        $allocation
                    );


                /*
                |--------------------------------------------------------------------------
                | PASSING PERCENTAGE
                |--------------------------------------------------------------------------
                */

                if ($allocation) {

                    $passingPercentage =
                        ResultHelper::getPassingPercentage(
                            $allocation->standard_id
                        );
                }


                /*
                |--------------------------------------------------------------------------
                | ACTUAL SUBJECT
                |--------------------------------------------------------------------------
                */

                $actualSubjectId =
                    (int) (
                        $teacherSubjectAllocation
                            ->subject_id
                        ?? 0
                    );


                /*
                |--------------------------------------------------------------------------
                | LOAD EXISTING MARKS
                |--------------------------------------------------------------------------
                |
                | IMPORTANT:
                |
                | Do NOT use teacher_subject_allocation_id here.
                |
                | Existing marks may belong to an older TSA.
                |
                */

                $existingMarksQuery =
                    StudentMark::query()
                        ->where(
                            'academic_year_id',
                            $allocation->academic_year_id
                        )
                        ->where(
                            'section_id',
                            $allocation->section_id
                        )
                        ->where(
                            'standard_id',
                            $allocation->standard_id
                        )
                        ->where(
                            'division_id',
                            $allocation->division_id
                        )
                        ->where(
                            'exam_master_id',
                            $exam?->id
                        )
                        ->where(
                            'subject_id',
                            $actualSubjectId
                        );

                $existingMarks =
                    $existingMarksQuery
                        ->get()
                        ->keyBy(
                            'student_id'
                        );


                /*
                |--------------------------------------------------------------------------
                | EXISTING MARKS = COMPLETED / LOCKED
                |--------------------------------------------------------------------------
                */

                if (
                    $existingMarks->isNotEmpty()
                ) {

                    $marksLocked =
                        !$isAdministrator;

                    $marksStatus =
                        'COMPLETED';


                    /*
                    |--------------------------------------------------------------------------
                    | KEEP CURRENT TEACHER MARK STATUS IN SYNC
                    |--------------------------------------------------------------------------
                    |
                    | Exam Progress reads teacher_marks_status.
                    |
                    | Update the current TSA status if it exists.
                    |
                    */

                    TeacherMarksStatus::query()
                        ->where(
                            'teacher_subject_allocation_id',
                            $teacherSubjectAllocation->id
                        )
                        ->where(
                            'exam_master_id',
                            $exam->id
                        )
                        ->when(
                            Auth::user()?->role !== 'Administrator',
                            function ($query) {
                                $query->where(
                                    'teacher_id',
                                    Auth::id()
                                );
                            }
                        )
                        ->update([
                            'status' =>
                                'COMPLETED',

                            'updated_at' =>
                                now(),
                        ]);


                    /*
                    |--------------------------------------------------------------------------
                    | KEEP EXISTING MARKS LOCKED
                    |--------------------------------------------------------------------------
                    */

                    if (!$isAdministrator) {
                        $existingMarksQuery
                            ->where(
                                'is_locked',
                                '!=',
                                1
                            )
                            ->update([
                                'is_locked' =>
                                    1,

                                'updated_at' =>
                                    now(),
                            ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | REFRESH EXISTING MARKS
                    |--------------------------------------------------------------------------
                    */

                    $existingMarks =
                        StudentMark::query()
                            ->where(
                                'academic_year_id',
                                $allocation->academic_year_id
                            )
                            ->where(
                                'section_id',
                                $allocation->section_id
                            )
                            ->where(
                                'standard_id',
                                $allocation->standard_id
                            )
                            ->where(
                                'division_id',
                                $allocation->division_id
                            )
                            ->where(
                                'exam_master_id',
                                $exam->id
                            )
                            ->where(
                                'subject_id',
                                $actualSubjectId
                            )
                            ->get()
                            ->keyBy(
                                'student_id'
                            );
                }


                /*
                |--------------------------------------------------------------------------
                | LOAD STUDENTS FROM OLD ERP
                |--------------------------------------------------------------------------
                |
                | Load from the actual existing marks, not TSA.
                |
                */

                $studentIds =
                    $existingMarks
                        ->pluck(
                            'student_id'
                        )
                        ->unique()
                        ->toArray();


                if (
                    !empty($studentIds)
                ) {

                    $students =
                        DB::connection(
                            'sqlsrv_olderp'
                        )
                        ->table(
                            'SubStudentMst as s'
                        )
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
                        ->orderByRaw(
                            'CAST(s.rollno AS INT)'
                        )
                        ->get();


                    /*
                    |--------------------------------------------------------------------------
                    | MAP MARKS TO STUDENTS
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        $students as $student
                    ) {

                        $mark =
                            $existingMarks->get(
                                $student->Studentid
                            );


                        $student->mark_id =
                            $mark->id
                            ?? null;


                        $student->theory_obtained_marks =
                            $mark->theory_obtained_marks
                            ?? '';


                        $student->oral_obtained_marks =
                            $mark->oral_obtained_marks
                            ?? '';


                        $student->practical_obtained_marks =
                            $mark->practical_obtained_marks
                            ?? '';


                        /*
                        |--------------------------------------------------------------------------
                        | ABSENT
                        |--------------------------------------------------------------------------
                        */

                        $student->is_absent =
                            (int) (
                                $mark->is_absent
                                ?? 0
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | OPTIONAL
                        |--------------------------------------------------------------------------
                        */

                        $student->is_optional =
                            (int) (
                                $mark->is_optional
                                ?? 0
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | LOCK
                        |--------------------------------------------------------------------------
                        */

                        $student->is_locked =
                            (int) (
                                $mark->is_locked
                                ?? 0
                            );
                    }
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'marks-entry.edit',
            compact(
                'students',
                'marksLocked',
                'marksStatus',
                'existingMarks',
                'assignments',
                'exams',
                'exam',
                'teacherSubjectAllocation',
                'showTheory',
                'showOral',
                'showPractical',
                'theoryMaxMarks',
                'theoryPassingMarks',
                'oralMaxMarks',
                'oralPassingMarks',
                'practicalMaxMarks',
                'practicalPassingMarks',
                'isOptionalEnabled',
                'passingPercentage'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE MARKS
    |--------------------------------------------------------------------------
    */

    public function updateMarks(
        Request $request
    ) {

        $isAdministrator = MarksHelper::isAdministrator();

        /*
        |--------------------------------------------------------------------------
        | IDS
        |--------------------------------------------------------------------------
        */

        $markIds =
            $request->input(
                'mark_ids',
                []
            );

        $studentIds =
            $request->input(
                'student_ids',
                []
            );

        /*
        | The Edit Blade submits student_ids[] and fields keyed by
        | student id. Keep mark_ids[] support for backward compatibility.
        */
        if (empty($markIds) && !empty($studentIds)) {
            $examId =
                (int) $request->input(
                    'exam_master_id'
                );

            $tsa =
                TeacherSubjectAllocation::find(
                    $request->input(
                        'teacher_subject_allocation_id'
                    )
                );

            $subjectId =
                $tsa?->subject_id;

            if (!$examId || !$subjectId) {
                return redirect()
                    ->back()
                    ->with(
                        'error',
                        'Unable to identify the selected examination or subject.'
                    );
            }

            $marks =
                StudentMark::whereIn(
                    'student_id',
                    $studentIds
                )
                ->where(
                    'exam_master_id',
                    $examId
                )
                ->where(
                    'subject_id',
                    $subjectId
                )
                ->get()
                ->keyBy('id');

            $markIds =
                $marks
                    ->keys()
                    ->values()
                    ->all();
        }


        /*
        |--------------------------------------------------------------------------
        | Nothing to update
        |--------------------------------------------------------------------------
        */

        if (
            empty($markIds)
        ) {

            return redirect()
                ->back()
                ->with(
                    'success',
                    'No marks were changed.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD MARKS
        |--------------------------------------------------------------------------
        */

        if (!isset($marks)) {
            $marks =
                StudentMark::whereIn(
                    'id',
                    $markIds
                )
                ->get()
                ->keyBy(
                    'id'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | SERVER-SIDE LOCK CHECK
        |--------------------------------------------------------------------------
        |
        | The Edit page may be visually locked, but this backend check is
        | also required so a manually submitted request cannot change
        | completed/locked marks.
        |
        */

        foreach (
            $marks as $mark
        ) {

            if (
                !$isAdministrator
                &&
                (int) (
                    $mark->is_locked
                    ?? 0
                ) === 1
            ) {

                return redirect()
                    ->back()
                    ->with(
                        'error',
                        'Marks have already been completed and locked. They cannot be modified.'
                    );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE
        |--------------------------------------------------------------------------
        */

        foreach (
            $markIds as $id
        ) {

            $mark =
                $marks->get(
                    $id
                );


            if (!$mark) {
                continue;
            }


            $requestKey =
                !empty($studentIds)
                    ? $mark->student_id
                    : $id;

            /*
            |--------------------------------------------------------------------------
            | OLD VALUES
            |--------------------------------------------------------------------------
            */

            $oldTheory =
                $mark->theory_obtained_marks;


            $oldOral =
                $mark->oral_obtained_marks;


            $oldPractical =
                $mark->practical_obtained_marks;


            $oldOptional =
                (int) (
                    $mark->is_optional
                    ?? 0
                );


            $oldAbsent =
                (int) (
                    $mark->is_absent
                    ?? 0
                );


            /*
            |--------------------------------------------------------------------------
            | CURRENT VALUES
            |--------------------------------------------------------------------------
            */

            $isOptional =
                (
                    (int) (
                        $request
                            ->is_optional[
                                $requestKey
                            ]
                            ?? 0
                    )
                ) === 1
                    ? 1
                    : 0;


            /*
            |--------------------------------------------------------------------------
            | IMPORTANT
            |--------------------------------------------------------------------------
            |
            | We only allow Optional if the student's standard is 11th/12th.
            |
            */

            $allocation =
                $mark->teacherSubjectAllocation
                ?? null;


            /*
            |--------------------------------------------------------------------------
            | Load allocation if relationship isn't available.
            |--------------------------------------------------------------------------
            */

            if (!$allocation) {

                $allocation =
                    TeacherSubjectAllocation::with([
                        'allocation.standard'
                    ])
                    ->find(
                        $mark
                            ->teacher_subject_allocation_id
                    );

                $allocation =
                    $allocation
                        ? $allocation->allocation
                        : null;
            }


            $isOptionalAllowed =
                $this->isOptionalEnabledForAllocation(
                    $allocation
                );


            /*
            |--------------------------------------------------------------------------
            | NON 11/12 CANNOT BE OPTIONAL
            |--------------------------------------------------------------------------
            */

            if (!$isOptionalAllowed) {

                $isOptional =
                    0;
            }


            /*
            |--------------------------------------------------------------------------
            | OPTIONAL TAKES PRIORITY
            |--------------------------------------------------------------------------
            */

            if ($isOptional) {

                $isAbsent =
                    0;

                $theory =
                    0;

                $oral =
                    0;

                $practical =
                    0;

            } else {

                /*
                |--------------------------------------------------------------------------
                | ABSENT
                |--------------------------------------------------------------------------
                */

                $isAbsent =
                    (
                        (int) (
                            $request
                                ->is_absent[
                                    $requestKey
                                ]
                                ?? 0
                        )
                    ) === 1
                        ? 1
                        : 0;


                /*
                |--------------------------------------------------------------------------
                | MARKS
                |--------------------------------------------------------------------------
                */

                $theory =
                    $request
                        ->theory_marks[
                            $requestKey
                        ]
                        ?? null;


                $oral =
                    $request
                        ->oral_marks[
                            $requestKey
                        ]
                        ?? null;


                $practical =
                    $request
                        ->practical_marks[
                            $requestKey
                        ]
                        ?? null;


                /*
                |--------------------------------------------------------------------------
                | ABSENT = ZERO
                |--------------------------------------------------------------------------
                */

                if ($isAbsent) {

                    $theory =
                        0;

                    $oral =
                        0;

                    $practical =
                        0;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | UPDATE
            |--------------------------------------------------------------------------
            */

            $mark->update([

                'theory_obtained_marks' =>
                    $theory,

                'oral_obtained_marks' =>
                    $oral,

                'practical_obtained_marks' =>
                    $practical,

                'is_absent' =>
                    $isAbsent,

                /*
                |--------------------------------------------------------------------------
                | OPTIONAL
                |--------------------------------------------------------------------------
                */

                'is_optional' =>
                    $isOptional,

                'updated_by' =>
                    Auth::id(),
            ]);


            /*
            |--------------------------------------------------------------------------
            | AUDIT LOG
            |--------------------------------------------------------------------------
            */

            $remarks =
                $isAdministrator
                    ? 'Administrator Marks Update'
                    : 'Teacher Marks Update';


            if (
                $isOptional &&
                !$oldOptional
            ) {

                $remarks =
                    'Teacher Marks Update - MARKED OPTIONAL';

            } elseif (
                !$isOptional &&
                $oldOptional
            ) {

                $remarks =
                    'Teacher Marks Update - OPTIONAL REMOVED';

            } elseif (
                $isOptional
            ) {

                $remarks =
                    'Teacher Marks Update - OPTIONAL';

            } elseif (
                $isAbsent
            ) {

                $remarks =
                    'Teacher Marks Update - ABSENT';
            }


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
                    $isAdministrator
                        ? 'ADMIN_UPDATE'
                        : 'TEACHER_UPDATE',

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
                    $remarks,

                'ip_address' =>
                    request()->ip(),

                'user_agent' =>
                    request()->userAgent()
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->back()
            ->with(
                'success',
                'Marks Updated Successfully.'
            );
    }
}
