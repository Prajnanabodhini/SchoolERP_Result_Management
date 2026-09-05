<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\StudentHelper;
use App\Models\TeacherClassAllocation;
use App\Models\Subject;

use App\Models\ExamMaster;
use App\Models\StudentMark;
use App\Models\TeacherMarksStatus;
use App\Models\TeacherSubjectAllocation;
use App\Models\MarkAuditLog;

use App\Helpers\ResultHelper;

/**
 * Edit-page specific helpers for marks management.
 *
 * Keeps controller actions thin while preserving the existing business
 * rules for loading, locking, updating and auditing marks.
 */
class EditMarkHelper
{
    /**
     * Determine whether Optional marks are enabled for the selected class.
     */
    public static function isOptionalEnabledForAllocation($allocation): bool
    {
        if (!$allocation) {
            return false;
        }

        $standardId = (int) ($allocation->standard_id ?? 0);

        if (in_array($standardId, [11, 12, 19, 20, 21, 22, 23, 24], true)) {
            return true;
        }

        $standardName = strtoupper(
            trim((string) optional($allocation->standard)->standard_name)
        );

        $normalized = trim(
            (string) preg_replace('/[\s\.\-]+/', ' ', $standardName)
        );

        return (bool) preg_match(
            '/^(?:11|11TH|ELEVENTH|XI|12|12TH|TWELFTH|XII)(?:\\s+(?:SCIENCE|COMMERCE|ARTS|VOCATIONAL|[A-Z0-9 .\\-]+))?/',
            $normalized
        );
    }

    /**
     * Build all data required by the marks edit screen.
     */
    public static function editData(Request $request): array
    {


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
                    self::isOptionalEnabledForAllocation(
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
                        true;

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

        return compact(
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
        );
    
    }

    /**
     * Apply submitted marks updates and write audit entries.
     */
    public static function updateMarks(Request $request)
    {


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

        $marks =
            StudentMark::whereIn(
                'id',
                $markIds
            )
            ->get()
            ->keyBy(
                'id'
            );


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
                                $id
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
                self::isOptionalEnabledForAllocation(
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
                                    $id
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
                            $id
                        ]
                        ?? null;


                $oral =
                    $request
                        ->oral_marks[
                            $id
                        ]
                        ?? null;


                $practical =
                    $request
                        ->practical_marks[
                            $id
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
                'Teacher Marks Update';


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


    /*
    |--------------------------------------------------------------------------
    | ADMIN MARKS ENTRY OPERATIONS
    |--------------------------------------------------------------------------
    |
    | Extracted from AdminMarksEntryService without changing business logic.
    | The service remains a thin compatibility facade while these methods keep
    | the existing validation, database queries, audit logging and redirects.
    |
    |--------------------------------------------------------------------------
    */

    public static function emptySelectedData(): array
    {
        return [

            'students' =>
                collect(),

            'existingMarks' =>
                collect(),

            'exam' =>
                null,

            'teacherSubjectAllocation' =>
                null,

            'selectedClassAllocation' =>
                null,

            'subjectConfig' =>
                null,

            'showTheory' =>
                false,

            'showOral' =>
                false,

            'showPractical' =>
                false,

            'theoryMaxMarks' =>
                0,

            'theoryPassingMarks' =>
                0,

            'oralMaxMarks' =>
                0,

            'oralPassingMarks' =>
                0,

            'practicalMaxMarks' =>
                0,

            'practicalPassingMarks' =>
                0,

            /*
            |--------------------------------------------------------------------------
            | ADMINISTRATOR CAN ALWAYS EDIT
            |--------------------------------------------------------------------------
            */

            'marksLocked' =>
                false,

            /*
            |--------------------------------------------------------------------------
            | OPTIONAL
            |--------------------------------------------------------------------------
            */

            'isOptionalEnabled' =>
                false,

            /*
            |--------------------------------------------------------------------------
            | PASSING
            |--------------------------------------------------------------------------
            */

            'passingPercentage' =>
                40,

            'message' =>
                '',

            'error' =>
                '',
        ];
    }


    /**
 * Determine whether the Optional feature is enabled for a given standard.
 *
 * @param int|null $standardId
 * @param mixed|null $allocation  (optional) TeacherClassAllocation or related model
 * @return bool
 */
public static function isOptionalEnabledForStandard($standardId, $allocation = null): bool
{
    $standardId = (int) $standardId;

    // Include all 11th & 12th standards (including streams)
    if (in_array($standardId, [11, 12, 19, 20, 21, 22, 23, 24], true)) {
        return true;
    }

    // Fallback: check standard name if allocation is provided
    if ($allocation) {
        $standardName = strtoupper(trim((string) optional($allocation->standard)->standard_name));
        $normalized = trim((string) preg_replace('/[\s\.\-]+/', ' ', $standardName));

        if (in_array($normalized, [
            '11', '11TH', 'ELEVENTH', 'XI',
            '12', '12TH', 'TWELFTH', 'XII',
        ], true)) {
            return true;
        }
    }

    return false;
}


    public static function getPassingPercentage(
        $standardId
    ): int {

        return ResultHelper::getPassingPercentage(
            $standardId
        );
    }


    public static function getEffectiveStatus(
        $status,
        $academicYearId,
        $sectionId,
        $standardId,
        $divisionId,
        $examId,
        $actualSubjectId
    ): string {

        $storedStatus =
            strtoupper(
                trim(
                    (string) (
                        $status?->status
                        ??
                        ''
                    )
                )
            );


        /*
        |--------------------------------------------------------------------------
        | VALIDATE CONTEXT
        |--------------------------------------------------------------------------
        */

        if (
            (int) $academicYearId <= 0
            ||
            (int) $sectionId <= 0
            ||
            (int) $standardId <= 0
            ||
            (int) $divisionId <= 0
            ||
            (int) $examId <= 0
            ||
            (int) $actualSubjectId <= 0
        ) {

            return $storedStatus !== ''
                ? $storedStatus
                : 'PENDING';
        }


        /*
        |--------------------------------------------------------------------------
        | POSSIBLE SUBJECT IDS
        |--------------------------------------------------------------------------
        |
        | Current format:
        |     subjects.id
        |
        | Legacy format:
        |     standard_wise_subjects.id
        |
        |--------------------------------------------------------------------------
        */

        $possibleSubjectIds =
            collect([
                (int) $actualSubjectId,
            ])
            ->merge(
                DB::table(
                    'standard_wise_subjects'
                )
                ->where(
                    'standard_id',
                    (int) $standardId
                )
                ->where(
                    'subject_id',
                    (int) $actualSubjectId
                )
                ->where(
                    'is_active',
                    1
                )
                ->pluck(
                    'id'
                )
            )
            ->map(
                fn ($id) => (int) $id
            )
            ->filter(
                fn ($id) => $id > 0
            )
            ->unique()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | STUDENT MARKS EXIST
        |--------------------------------------------------------------------------
        |
        | This intentionally does NOT require TSA.
        |
        | This matches ExamProgressController:
        |
        | academic_year + section + standard + division + exam + subject
        |
        |--------------------------------------------------------------------------
        */

        $marksExist =
            DB::table(
                'student_marks'
            )
            ->where(
                'academic_year_id',
                (int) $academicYearId
            )
            ->where(
                'section_id',
                (int) $sectionId
            )
            ->where(
                'standard_id',
                (int) $standardId
            )
            ->where(
                'division_id',
                (int) $divisionId
            )
            ->where(
                'exam_master_id',
                (int) $examId
            )
            ->whereIn(
                'subject_id',
                $possibleSubjectIds
            )
            ->exists();


        if ($marksExist) {

            return 'COMPLETED';
        }


        /*
        |--------------------------------------------------------------------------
        | FALLBACK TO STORED TMS STATUS
        |--------------------------------------------------------------------------
        */

        return $storedStatus !== ''
            ? $storedStatus
            : 'PENDING';
    }


    public static function loadSelectedDataForAdminEntry(
        Request $request,
        $exams,
        $subjectService
    ) {

        $data =
            self::emptySelectedData();


        $selectionValue =
            $request->input(
                'teacher_subject_allocation_id'
            );


        if (!$selectionValue) {

            return $data;
        }


        [
            $tsaId,
            $selectedSubjectId
        ] =
            self::parseSelection(
                $request
            );


        if (!$tsaId) {

            $data['error'] =
                'Invalid teaching assignment.';

            return $data;
        }


        /*
        |--------------------------------------------------------------------------
        | REQUEST EXAM
        |--------------------------------------------------------------------------
        */

        $requestedExamId =
            $request->input(
                'exam_master_id'
            );


        /*
        |--------------------------------------------------------------------------
        | LOAD TEACHER MARKS STATUS
        |--------------------------------------------------------------------------
        |
        | No section_id is selected here because the table does not contain it.
        |
        |--------------------------------------------------------------------------
        */

        $status =
            TeacherMarksStatus::query()
                ->where(
                    'teacher_subject_allocation_id',
                    $tsaId
                )
                ->when(
                    $requestedExamId !== null
                    &&
                    $requestedExamId !== '',
                    function ($query) use (
                        $requestedExamId
                    ) {

                        $query->where(
                            'exam_master_id',
                            (int) $requestedExamId
                        );
                    }
                )
                ->orderByDesc(
                    'id'
                )
                ->first();


        /*
        |--------------------------------------------------------------------------
        | LOAD TSA
        |--------------------------------------------------------------------------
        */

        $tsa =
            TeacherSubjectAllocation::query()
                ->with([
                    'allocation.teacher',
                    'allocation.academicYear',
                    'allocation.section',
                    'allocation.standard',
                    'allocation.division',
                ])
                ->find(
                    $tsaId
                );


        if (
            !$tsa
            ||
            !$tsa->allocation
        ) {

            $data['error'] =
                'Teacher class allocation not found.';

            return $data;
        }


        $allocation =
            $tsa->allocation;


        /*
        |--------------------------------------------------------------------------
        | EXAM ID
        |--------------------------------------------------------------------------
        */

        $examId =
            (int) (
                $requestedExamId
                ??
                $tsa->exam_master_id
            );


        if (!$examId) {

            $data['error'] =
                'Exam linked to the selected teaching assignment was not found.';

            return $data;
        }


        /*
        |--------------------------------------------------------------------------
        | EXAM
        |--------------------------------------------------------------------------
        */

        $exam =
            $exams->firstWhere(
                'id',
                $examId
            );


        if (!$exam) {

            $exam =
                ExamMaster::find(
                    $examId
                );
        }


        if (!$exam) {

            $data['error'] =
                'Exam linked to the selected teaching assignment was not found.';

            return $data;
        }


        $data['exam'] =
            $exam;


        /*
        |--------------------------------------------------------------------------
        | STANDARD
        |--------------------------------------------------------------------------
        */

        $standardId =
            (int) (
                $status?->standard_id
                ??
                $allocation->standard_id
            );


        /*
        |--------------------------------------------------------------------------
        | DIVISION
        |--------------------------------------------------------------------------
        */

        $divisionId =
            (int) (
                $status?->division_id
                ??
                $allocation->division_id
            );


        /*
        |--------------------------------------------------------------------------
        | ACADEMIC YEAR
        |--------------------------------------------------------------------------
        */

        $academicYearId =
            (int) (
                $status?->academic_year_id
                ??
                $allocation->academic_year_id
                ??
                $exam->academic_year_id
                ??
                0
            );


        /*
        |--------------------------------------------------------------------------
        | SECTION
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | teacher_marks_status does NOT contain section_id.
        | Always take section_id from class allocation.
        |
        |--------------------------------------------------------------------------
        */

        $sectionId =
            (int) (
                $allocation->section_id
                ??
                0
            );


        /*
        |--------------------------------------------------------------------------
        | OPTIONAL
        |--------------------------------------------------------------------------
        */

        $data['isOptionalEnabled'] =
            self::isOptionalEnabledForStandard(
                $standardId,
                $allocation
            );


        $data['passingPercentage'] =
            self::getPassingPercentage(
                $standardId
            );


        /*
        |--------------------------------------------------------------------------
        | VALIDATE ACADEMIC YEAR
        |--------------------------------------------------------------------------
        */

        $requestedAcademicYearId =
            $request->input(
                'academic_year_id'
            );


        if (
            $requestedAcademicYearId !== null
            &&
            $requestedAcademicYearId !== ''
            &&
            $academicYearId > 0
            &&
            (int) $requestedAcademicYearId
                !==
            $academicYearId
        ) {

            $data['error'] =
                'Selected teaching assignment does not belong to the selected academic year.';

            return $data;
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDATE CLASS DATA
        |--------------------------------------------------------------------------
        */

        if (
            $academicYearId <= 0
            ||
            $sectionId <= 0
            ||
            $standardId <= 0
            ||
            $divisionId <= 0
        ) {

            $data['error'] =
                'Unable to determine Academic Year, Section, Standard or Division.';

            return $data;
        }


        /*
        |--------------------------------------------------------------------------
        | SUBJECT
        |--------------------------------------------------------------------------
        */

        $actualSubjectId =
            self::resolveSelectedSubject(
                $selectedSubjectId,
                $tsa,
                $status,
                $examId,
                $standardId,
                $divisionId,
                $subjectService
            );


        if (!$actualSubjectId) {

            $data['error'] =
                'Unable to resolve the actual Subject Master ID.';

            return $data;
        }


        /*
        |--------------------------------------------------------------------------
        | SUBJECT MASTER
        |--------------------------------------------------------------------------
        */

        $subject =
            Subject::query()
                ->where(
                    'id',
                    $actualSubjectId
                )
                ->where(
                    'is_active',
                    1
                )
                ->first([
                    'id',
                    'subject_name',
                    'subject_code',
                    'short_name',
                ]);


        if (!$subject) {

            $data['error'] =
                'The selected subject was not found.';

            return $data;
        }


        /*
        |--------------------------------------------------------------------------
        | STANDARD MAPPING
        |--------------------------------------------------------------------------
        */

        if (
            !$subjectService->isMappedToStandard(
                $actualSubjectId,
                $standardId
            )
        ) {

            $data['error'] =
                'The selected subject is not mapped to the selected Standard.';

            return $data;
        }


        /*
        |--------------------------------------------------------------------------
        | BUILD DISPLAY ASSIGNMENT
        |--------------------------------------------------------------------------
        */

        $displayAssignment =
            self::buildDisplayAssignment(
                $tsa,
                $exam,
                $subject,
                $allocation,
                $status,
                $selectedSubjectId
            );


        /*
        |--------------------------------------------------------------------------
        | EFFECTIVE STATUS
        |--------------------------------------------------------------------------
        */

        $effectiveStatus =
            self::getEffectiveStatus(
                $status,
                $academicYearId,
                $sectionId,
                $standardId,
                $divisionId,
                $examId,
                $actualSubjectId
            );


        $displayAssignment->resolved_status =
            $effectiveStatus;


        $data['teacherSubjectAllocation'] =
            $displayAssignment;


        $data['selectedClassAllocation'] =
            $allocation;


        /*
        |--------------------------------------------------------------------------
        | SUBJECT CONFIGURATION
        |--------------------------------------------------------------------------
        */

        $subjectConfig =
            $subjectService->getSubjectConfig(
                $examId,
                $standardId,
                $actualSubjectId
            );


        if (!$subjectConfig) {

            $data['error'] =
                'Marks configuration was not found for '
                . $subject->subject_name
                . ' in '
                . $exam->exam_name
                . '.';

            return $data;
        }


        $data['subjectConfig'] =
            $subjectConfig;


        /*
        |--------------------------------------------------------------------------
        | COMPONENT CONFIGURATION
        |--------------------------------------------------------------------------
        */

        $component =
            self::getComponentConfig(
                $exam,
                $subjectConfig,
                $standardId
            );


        $data =
            array_merge(
                $data,
                $component
            );


        /*
        |--------------------------------------------------------------------------
        | LOAD STUDENTS
        |--------------------------------------------------------------------------
        */

        try {

            $data['students'] =
                self::loadStudents(
                    $academicYearId,
                    $standardId,
                    $divisionId
                );

        } catch (
            \Throwable $e
        ) {

            report($e);

            $data['students'] =
                collect();

            $data['error'] =
                'Old ERP Error: '
                . $e->getMessage();
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD EXISTING MARKS
        |--------------------------------------------------------------------------
        */

        $data['existingMarks'] =
            self::loadExistingMarks(
                $examId,
                $actualSubjectId,
                $academicYearId,
                $sectionId,
                $standardId,
                $divisionId,
                $subjectService
            );


        /*
        |--------------------------------------------------------------------------
        | ADMINISTRATOR CAN ALWAYS MODIFY
        |--------------------------------------------------------------------------
        */

        $data['marksLocked'] =
            false;


        /*
        |--------------------------------------------------------------------------
        | MESSAGE
        |--------------------------------------------------------------------------
        */

        if (
            $effectiveStatus === 'COMPLETED'
        ) {

            $data['message'] =
                'Status: COMPLETED. Administrator can modify these marks.';

        } elseif (
            $displayAssignment->is_historical
            ??
            false
        ) {

            $data['message'] =
                'Historical marks recovered from Student Marks. Administrator can modify these marks.';

        } else {

            $data['message'] =
                'Status: '
                . $effectiveStatus
                . '. Administrator can modify these marks.';
        }


        return $data;
    }


    public static function parseSelection(
        Request $request
    ): array {

        $value =
            $request->input(
                'teacher_subject_allocation_id'
            );


        if (
            $value === null
            ||
            $value === ''
        ) {

            return [
                null,
                null,
            ];
        }


        $tsaId =
            null;

        $subjectId =
            null;


        if (
            str_contains(
                (string) $value,
                '|'
            )
        ) {

            $parts =
                explode(
                    '|',
                    (string) $value
                );


            $tsaId =
                isset($parts[0])
                    ? (int) $parts[0]
                    : null;


            $subjectId =
                isset($parts[1])
                &&
                $parts[1] !== ''
                    ? (int) $parts[1]
                    : null;

        } else {

            $tsaId =
                (int) $value;


            if (
                $request->filled(
                    'subject_id'
                )
            ) {

                $subjectId =
                    (int)
                    $request->input(
                        'subject_id'
                    );
            }
        }


        return [
            $tsaId,
            $subjectId,
        ];
    }


    public static function resolveSelectedSubject(
        $selectedSubjectId,
        $tsa,
        $status,
        $examId,
        $standardId,
        $divisionId,
        $subjectService
    ) {

        /*
        |--------------------------------------------------------------------------
        | 1. EXPLICIT SUBJECT
        |--------------------------------------------------------------------------
        */

        if ($selectedSubjectId) {

            $actual =
                $subjectService
                    ->resolveActualSubjectId(
                        $selectedSubjectId,
                        $standardId
                    );


            if ($actual) {

                return $actual;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 2. HISTORICAL SUBJECT
        |--------------------------------------------------------------------------
        */

        $historicalSubjectIds =
            StudentMark::query()
                ->where(
                    'teacher_subject_allocation_id',
                    $tsa->id
                )
                ->where(
                    'exam_master_id',
                    $examId
                )
                ->where(
                    'standard_id',
                    $standardId
                )
                ->where(
                    'division_id',
                    $divisionId
                )
                ->whereNotNull(
                    'subject_id'
                )
                ->orderByDesc(
                    'id'
                )
                ->pluck(
                    'subject_id'
                )
                ->unique()
                ->values();


        foreach (
            $historicalSubjectIds
            as $storedSubjectId
        ) {

            $actual =
                $subjectService
                    ->resolveActualSubjectId(
                        $storedSubjectId,
                        $standardId
                    );


            if ($actual) {

                return $actual;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 3. TMS SUBJECT
        |--------------------------------------------------------------------------
        */

        if (
            $status
            &&
            $status->subject_id
        ) {

            $actual =
                $subjectService
                    ->resolveActualSubjectId(
                        $status->subject_id,
                        $standardId
                    );


            if ($actual) {

                return $actual;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 4. TSA SUBJECT
        |--------------------------------------------------------------------------
        */

        if (
            $tsa
            &&
            $tsa->subject_id
        ) {

            $actual =
                $subjectService
                    ->resolveActualSubjectId(
                        $tsa->subject_id,
                        $standardId
                    );


            if ($actual) {

                return $actual;
            }
        }


        return null;
    }


    public static function buildDisplayAssignment(
        $tsa,
        $exam,
        $subject,
        $allocation,
        $status,
        $selectedSubjectId
    ) {

        $assignment =
            new TeacherSubjectAllocation();


        $assignment->id =
            (int) $tsa->id;


        $assignment->teacher_class_allocation_id =
            (int)
            $tsa->teacher_class_allocation_id;


        $assignment->exam_master_id =
            (int) $exam->id;


        $assignment->subject_id =
            (int) $subject->id;


        $assignment->setRelation(
            'allocation',
            $allocation
        );


        $assignment->setRelation(
            'subject',
            $subject
        );


        $assignment->setRelation(
            'exam',
            $exam
        );


        $assignment->resolved_subject_id =
            (int) $subject->id;


        $assignment->resolved_academic_year_id =
            (int)
            $allocation->academic_year_id;


        $assignment->resolved_section_id =
            (int)
            $allocation->section_id;


        $assignment->resolved_class_allocation_id =
            (int) $allocation->id;


        $assignment->resolved_exam_master_id =
            (int) $exam->id;


        $assignment->resolved_standard_id =
            (int) $allocation->standard_id;


        $assignment->resolved_division_id =
            (int) $allocation->division_id;


        $assignment->resolved_teacher_id =
            $allocation->user_id
                ? (int)
                $allocation->user_id
                : null;


        $assignment->resolved_tms_subject_id =
            $status?->subject_id;


        /*
        |--------------------------------------------------------------------------
        | STORED STATUS
        |--------------------------------------------------------------------------
        */

        $assignment->resolved_status =
            strtoupper(
                trim(
                    (string) (
                        $status?->status
                        ??
                        'PENDING'
                    )
                )
            );


        $assignment->resolved_status_id =
            $status?->id;


        $assignment->is_historical =
            (
                $selectedSubjectId
                &&
                $status
                &&
                (int) $selectedSubjectId
                !==
                (int) (
                    $status->subject_id
                    ??
                    0
                )
            );


        $assignment->resolved_selection_key =
            $tsa->id
            . '|'
            . $subject->id;


        return $assignment;
    }


    public static function loadStudents(
        $academicYearId,
        $standardId,
        $divisionId
    ) {

        $students =
            StudentHelper::getStudentsDirectERP(
                $academicYearId,
                $standardId,
                $divisionId
            );


        return collect($students)
            ->sortBy(
                function ($student) {

                    $roll =
                        $student->roll_no
                        ??
                        $student->roll_number
                        ??
                        $student->roll
                        ??
                        $student->student_roll_no
                        ??
                        null;


                    if (
                        $roll === null
                        ||
                        $roll === ''
                    ) {

                        return PHP_INT_MAX;
                    }


                    return (int) $roll;
                }
            )
            ->values();
    }


    public static function loadExistingMarks(
        $examId,
        $actualSubjectId,
        $academicYearId,
        $sectionId,
        $standardId,
        $divisionId,
        $subjectService
    ) {

        $possibleSubjectIds =
            $subjectService->getPossibleSubjectIds(
                $actualSubjectId,
                $standardId
            );


        if (
            $possibleSubjectIds->isEmpty()
        ) {

            return collect();
        }


        $marks =
            StudentMark::query()
                ->where(
                    'academic_year_id',
                    $academicYearId
                )
                ->where(
                    'section_id',
                    $sectionId
                )
                ->where(
                    'exam_master_id',
                    $examId
                )
                ->whereIn(
                    'subject_id',
                    $possibleSubjectIds
                )
                ->orderByDesc(
                    'id'
                )
                ->get();


        if (
            $marks->isEmpty()
        ) {

            return collect();
        }


        /*
        |--------------------------------------------------------------------------
        | RESOLVE SUBJECT IDS
        |--------------------------------------------------------------------------
        */

        foreach (
            $marks as $mark
        ) {

            $resolved =
                $subjectService
                    ->resolveActualSubjectId(
                        $mark->subject_id,
                        $standardId
                    );


            if ($resolved) {

                $mark->resolved_subject_id =
                    (int) $resolved;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | KEEP STANDARD / DIVISION
        |--------------------------------------------------------------------------
        */

        $marks =
            $marks->filter(
                function ($mark) use (
                    $standardId,
                    $divisionId
                ) {

                    return
                        (int)
                        $mark->standard_id
                        ===
                        (int) $standardId

                        &&

                        (int)
                        $mark->division_id
                        ===
                        (int) $divisionId;
                }
            );


        /*
        |--------------------------------------------------------------------------
        | ONE MARK PER STUDENT
        |--------------------------------------------------------------------------
        */

        return $marks
            ->unique(
                'student_id'
            )
            ->keyBy(
                'student_id'
            );
    }


    public static function getComponentConfig(
        $exam,
        $subjectConfig,
        $standardId
    ): array {

        $showTheory =
            true;


        $showOral =
            (bool) (
                $exam->has_oral
                ??
                false
            );


        $showPractical =
            (bool) (
                $exam->has_practical
                ??
                false
            );


        $examName =
            strtoupper(
                trim(
                    (string)
                    $exam->exam_name
                )
            );


        /*
        |--------------------------------------------------------------------------
        | UNIT TEST 1
        |--------------------------------------------------------------------------
        */

        if (
            str_contains(
                $examName,
                'UNIT TEST 1'
            )
        ) {

            $showOral =
                false;

            $showPractical =
                false;
        }


        /*
        |--------------------------------------------------------------------------
        | THEORY
        |--------------------------------------------------------------------------
        */

        $theoryMaxMarks =
            (float) (
                $subjectConfig->max_marks
                ??
                0
            );


        $theoryPassingMarks =
            ResultHelper::getPassingMarks(
                $standardId,
                $theoryMaxMarks
            );


        return [

            'showTheory' =>
                $showTheory,

            'showOral' =>
                $showOral,

            'showPractical' =>
                $showPractical,

            'theoryMaxMarks' =>
                $theoryMaxMarks,

            'theoryPassingMarks' =>
                $theoryPassingMarks,

            'oralMaxMarks' =>
                $showOral
                    ? (float) (
                        $exam->oral_max_marks
                        ??
                        0
                    )
                    : 0,

            'oralPassingMarks' =>
                $showOral
                    ? (float) (
                        $exam->oral_passing_marks
                        ??
                        0
                    )
                    : 0,

            'practicalMaxMarks' =>
                $showPractical
                    ? (float) (
                        $exam->practical_max_marks
                        ??
                        0
                    )
                    : 0,

            'practicalPassingMarks' =>
                $showPractical
                    ? (float) (
                        $exam->practical_passing_marks
                        ??
                        0
                    )
                    : 0,
        ];
    }


    public static function validateMark(
        $value,
        $max,
        $required,
        $label,
        $studentId
    ) {

        if (
            $value !== null
            &&
            $value !== ''
        ) {

            $value =
                (float) $value;


            if (
                $value < 0
                ||
                $value > $max
            ) {

                throw new \RuntimeException(
                    'Invalid '
                    . $label
                    . ' marks for student ID '
                    . $studentId
                    . '. Maximum allowed marks: '
                    . $max
                );
            }


            return $value;
        }


        if (
            $required
            &&
            $max > 0
        ) {

            throw new \RuntimeException(
                $label
                . ' marks are required for student ID '
                . $studentId
            );
        }


        return null;
    }


    public static function updateAdminMarks(
        Request $request,
        $subjectService
    ) {

        $request->validate([

            'teacher_subject_allocation_id' =>
                'required',

            'exam_master_id' =>
                'required|integer|exists:exam_masters,id',

            'student_ids' =>
                'required|array|min:1',
        ]);


        /*
        |--------------------------------------------------------------------------
        | PARSE SELECTION
        |--------------------------------------------------------------------------
        */

        [
            $tsaId,
            $selectedSubjectId
        ] =
            self::parseSelection(
                $request
            );


        $examId =
            (int) $request->exam_master_id;


        if (!$tsaId) {

            return back()
                ->withInput()
                ->withErrors([
                    'teacher_subject_allocation_id' =>
                        'Invalid teaching assignment.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | TSA
        |--------------------------------------------------------------------------
        */

        $tsa =
            TeacherSubjectAllocation::with([
                'allocation.standard',
                'allocation.division',
                'allocation.section',
            ])
            ->find(
                $tsaId
            );


        if (!$tsa) {

            return back()
                ->withInput()
                ->withErrors([
                    'teacher_subject_allocation_id' =>
                        'Teaching assignment was not found.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        $status =
            TeacherMarksStatus::query()
                ->where(
                    'teacher_subject_allocation_id',
                    $tsaId
                )
                ->where(
                    'exam_master_id',
                    $examId
                )
                ->orderByDesc(
                    'id'
                )
                ->first();


        /*
        |--------------------------------------------------------------------------
        | CLASS ALLOCATION
        |--------------------------------------------------------------------------
        */

        $classAllocation =
            TeacherClassAllocation::with([
                'standard',
            ])
            ->find(
                $tsa->teacher_class_allocation_id
            );


        if (!$classAllocation) {

            return back()
                ->withInput()
                ->withErrors([
                    'teacher_subject_allocation_id' =>
                        'Teacher class allocation was not found.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | STANDARD
        |--------------------------------------------------------------------------
        */

        $standardId =
            (int) (
                $status?->standard_id
                ??
                $classAllocation->standard_id
            );


        /*
        |--------------------------------------------------------------------------
        | DIVISION
        |--------------------------------------------------------------------------
        */

        $divisionId =
            (int) (
                $status?->division_id
                ??
                $classAllocation->division_id
            );


        /*
        |--------------------------------------------------------------------------
        | ACADEMIC YEAR
        |--------------------------------------------------------------------------
        */

        $academicYearId =
            (int) (
                $status?->academic_year_id
                ??
                $classAllocation->academic_year_id
                ??
                0
            );


        /*
        |--------------------------------------------------------------------------
        | SECTION
        |--------------------------------------------------------------------------
        |
        | teacher_marks_status DOES NOT contain section_id.
        |
        |--------------------------------------------------------------------------
        */

        $sectionId =
            (int) (
                $classAllocation->section_id
                ??
                0
            );


        /*
        |--------------------------------------------------------------------------
        | EXAM
        |--------------------------------------------------------------------------
        */

        $exam =
            ExamMaster::query()
                ->where(
                    'id',
                    $examId
                )
                ->where(
                    'is_active',
                    1
                )
                ->first();


        if (!$exam) {

            return back()
                ->withInput()
                ->withErrors([
                    'exam_master_id' =>
                        'The selected exam was not found.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | OPTIONAL
        |--------------------------------------------------------------------------
        */

        $isOptionalEnabled =
            self::isOptionalEnabledForStandard(
                $standardId,
                $classAllocation
            );


        /*
        |--------------------------------------------------------------------------
        | SUBJECT
        |--------------------------------------------------------------------------
        */

        $actualSubjectId =
            self::resolveSubjectForUpdate(
                $selectedSubjectId,
                $status,
                $tsa,
                $examId,
                $standardId,
                $divisionId,
                $subjectService
            );


        if (!$actualSubjectId) {

            return back()
                ->withInput()
                ->withErrors([
                    'subject_id' =>
                        'Unable to resolve the actual Subject Master ID.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | SUBJECT MASTER
        |--------------------------------------------------------------------------
        */

        $subject =
            Subject::query()
                ->where(
                    'id',
                    $actualSubjectId
                )
                ->where(
                    'is_active',
                    1
                )
                ->first();


        if (!$subject) {

            return back()
                ->withInput()
                ->withErrors([
                    'subject_id' =>
                        'The selected subject was not found.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | STANDARD MAPPING
        |--------------------------------------------------------------------------
        */

        if (
            !$subjectService->isMappedToStandard(
                $actualSubjectId,
                $standardId
            )
        ) {

            return back()
                ->withInput()
                ->withErrors([
                    'subject_id' =>
                        'The selected subject is not mapped to the selected Standard.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | SUBJECT CONFIGURATION
        |--------------------------------------------------------------------------
        */

        $subjectConfig =
            $subjectService->getSubjectConfig(
                $examId,
                $standardId,
                $actualSubjectId
            );


        if (!$subjectConfig) {

            return back()
                ->withInput()
                ->withErrors([
                    'subject_id' =>
                        'Marks configuration was not found for '
                        . $subject->subject_name
                        . ' in '
                        . $exam->exam_name
                        . '.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | COMPONENTS
        |--------------------------------------------------------------------------
        */

        $component =
            self::getComponentConfig(
                $exam,
                $subjectConfig,
                $standardId
            );


        $theoryMax =
            $component['theoryMaxMarks'];


        $showTheory =
            $component['showTheory'];


        $showOral =
            $component['showOral'];


        $showPractical =
            $component['showPractical'];


        $oralMax =
            $component['oralMaxMarks'];


        $practicalMax =
            $component['practicalMaxMarks'];


        /*
        |--------------------------------------------------------------------------
        | SAVE
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $request,
                $tsaId,
                $examId,
                $standardId,
                $divisionId,
                $academicYearId,
                $sectionId,
                $actualSubjectId,
                $theoryMax,
                $oralMax,
                $practicalMax,
                $showTheory,
                $showOral,
                $showPractical,
                $isOptionalEnabled
            ) {

                foreach (
                    $request->student_ids
                    as $studentId
                ) {

                    $studentId =
                        (string) $studentId;


                    /*
                    |--------------------------------------------------------------------------
                    | FIND EXISTING MARK
                    |--------------------------------------------------------------------------
                    */

                    $mark =
                        StudentMark::query()
                            ->where(
                                'academic_year_id',
                                $academicYearId
                            )
                            ->where(
                                'section_id',
                                $sectionId
                            )
                            ->where(
                                'exam_master_id',
                                $examId
                            )
                            ->where(
                                'subject_id',
                                $actualSubjectId
                            )
                            ->where(
                                'student_id',
                                $studentId
                            )
                            ->orderByDesc(
                                'id'
                            )
                            ->first();


                    /*
                    |--------------------------------------------------------------------------
                    | OLD VALUES
                    |--------------------------------------------------------------------------
                    */

                    $oldTheory =
                        $mark?->theory_obtained_marks;


                    $oldOral =
                        $mark?->oral_obtained_marks;


                    $oldPractical =
                        $mark?->practical_obtained_marks;


                    $oldOptional =
                        (int) (
                            $mark?->is_optional
                            ??
                            0
                        );


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
                                        $studentId
                                    ]
                                    ??
                                    0
                            )
                        ) === 1
                            ? 1
                            : 0;


                    /*
                    |--------------------------------------------------------------------------
                    | OPTIONAL
                    |--------------------------------------------------------------------------
                    */

                    $isOptional =
                        $isOptionalEnabled
                        &&
                        (
                            (int) (
                                $request
                                    ->is_optional[
                                        $studentId
                                    ]
                                    ??
                                    0
                            )
                        ) === 1
                            ? 1
                            : 0;


                    /*
                    |--------------------------------------------------------------------------
                    | OPTIONAL PRIORITY
                    |--------------------------------------------------------------------------
                    */

                    if ($isOptional) {

                        $isAbsent =
                            0;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | REQUEST MARKS
                    |--------------------------------------------------------------------------
                    */

                    $theory =
                        $request
                            ->theory_marks[
                                $studentId
                            ]
                            ??
                            null;


                    $oral =
                        $request
                            ->oral_marks[
                                $studentId
                            ]
                            ??
                            null;


                    $practical =
                        $request
                            ->practical_marks[
                                $studentId
                            ]
                            ??
                            null;


                    /*
                    |--------------------------------------------------------------------------
                    | OPTIONAL / ABSENT
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $isOptional
                        ||
                        $isAbsent
                    ) {

                        $theory =
                            0;

                        $oral =
                            0;

                        $practical =
                            0;

                    } else {

                        if (!$showOral) {

                            $oral =
                                null;
                        }


                        if (!$showPractical) {

                            $practical =
                                null;
                        }
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | THEORY
                    |--------------------------------------------------------------------------
                    */

                    $theory =
                        self::validateMark(
                            $theory,
                            $theoryMax,
                            !$isAbsent && !$isOptional,
                            'Theory',
                            $studentId
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | ORAL
                    |--------------------------------------------------------------------------
                    */

                    if ($showOral) {

                        $oral =
                            self::validateMark(
                                $oral,
                                $oralMax,
                                !$isAbsent && !$isOptional,
                                'Oral',
                                $studentId
                            );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | PRACTICAL
                    |--------------------------------------------------------------------------
                    */

                    if ($showPractical) {

                        $practical =
                            self::validateMark(
                                $practical,
                                $practicalMax,
                                !$isAbsent && !$isOptional,
                                'Practical',
                                $studentId
                            );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | SAVE DATA
                    |--------------------------------------------------------------------------
                    */

                    $saveData = [

                        'teacher_subject_allocation_id' =>
                            $tsaId,

                        'subject_id' =>
                            $actualSubjectId,

                        'academic_year_id' =>
                            $academicYearId,

                        'section_id' =>
                            $sectionId,

                        'standard_id' =>
                            $standardId,

                        'division_id' =>
                            $divisionId,

                        'theory_obtained_marks' =>
                            $theory,

                        'oral_obtained_marks' =>
                            $oral,

                        'practical_obtained_marks' =>
                            $practical,

                        'is_absent' =>
                            $isAbsent,

                        'is_optional' =>
                            $isOptional,

                        /*
                        |--------------------------------------------------------------------------
                        | ADMIN UPDATE KEEPS MARK UNLOCKED
                        |--------------------------------------------------------------------------
                        */

                        'is_locked' =>
                            0,

                        'updated_by' =>
                            Auth::id(),
                    ];


                    /*
                    |--------------------------------------------------------------------------
                    | UPDATE EXISTING
                    |--------------------------------------------------------------------------
                    */

                    if ($mark) {

                        $mark->update(
                            $saveData
                        );

                        $wasCreated =
                            false;

                    } else {

                        /*
                        |--------------------------------------------------------------------------
                        | CREATE NEW
                        |--------------------------------------------------------------------------
                        */

                        $mark =
                            StudentMark::create(
                                array_merge(
                                    $saveData,
                                    [
                                        'student_id' =>
                                            $studentId,

                                        'exam_master_id' =>
                                            $examId,

                                        'created_by' =>
                                            Auth::id(),
                                    ]
                                )
                            );

                        $wasCreated =
                            true;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | AUDIT LOG
                    |--------------------------------------------------------------------------
                    */

                    $auditRemarks =
                        $wasCreated
                            ? (
                                $isOptional
                                    ? 'Admin Marks Entry - OPTIONAL'
                                    : (
                                        $isAbsent
                                            ? 'Admin Marks Entry - ABSENT'
                                            : 'Admin Marks Entry'
                                    )
                            )
                            : (
                                $isOptional
                                    ? (
                                        $oldOptional
                                            ? 'Admin Marks Correction - OPTIONAL'
                                            : 'Admin Marks Correction - MARKED OPTIONAL'
                                    )
                                    : (
                                        $oldOptional
                                            ? 'Admin Marks Correction - OPTIONAL REMOVED'
                                            : (
                                                $isAbsent
                                                    ? 'Admin Marks Correction - ABSENT'
                                                    : 'Admin Marks Correction - PRESENT'
                                            )
                                    )
                            );


                    MarkAuditLog::create([

                        'student_mark_id' =>
                            $mark->id,

                        'student_id' =>
                            $mark->student_id,

                        'exam_master_id' =>
                            $mark->exam_master_id,

                        'subject_id' =>
                            $actualSubjectId,

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
                            $auditRemarks,

                        'ip_address' =>
                            $request->ip(),

                        'user_agent' =>
                            $request->userAgent(),
                    ]);
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        */

        return redirect()->route(
            'result-generation.admin-marks.edit',
            [

                'academic_year_id' =>
                    $academicYearId,

                'exam_master_id' =>
                    $examId,

                'teacher_subject_allocation_id' =>
                    $tsaId
                    . '|'
                    . $actualSubjectId,

                'subject_id' =>
                    $actualSubjectId,

                'standard_id' =>
                    $standardId,

                'division_id' =>
                    $divisionId,

                'marks_updated' =>
                    1,
            ]
        )
        ->with(
            'success',
            'Marks Updated Successfully.'
        );
    }


    public static function resolveSubjectForUpdate(
        $selectedSubjectId,
        $status,
        $tsa,
        $examId,
        $standardId,
        $divisionId,
        $subjectService
    ) {

        /*
        |--------------------------------------------------------------------------
        | 1. SELECTED SUBJECT
        |--------------------------------------------------------------------------
        */

        if ($selectedSubjectId) {

            $actual =
                $subjectService
                    ->resolveActualSubjectId(
                        $selectedSubjectId,
                        $standardId
                    );


            if ($actual) {

                return $actual;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 2. EXISTING STUDENT MARK
        |--------------------------------------------------------------------------
        */

        $historicalSubject =
            StudentMark::query()
                ->where(
                    'teacher_subject_allocation_id',
                    $tsa->id
                )
                ->where(
                    'exam_master_id',
                    $examId
                )
                ->where(
                    'standard_id',
                    $standardId
                )
                ->where(
                    'division_id',
                    $divisionId
                )
                ->whereNotNull(
                    'subject_id'
                )
                ->orderByDesc(
                    'id'
                )
                ->value(
                    'subject_id'
                );


        if ($historicalSubject) {

            $actual =
                $subjectService
                    ->resolveActualSubjectId(
                        $historicalSubject,
                        $standardId
                    );


            if ($actual) {

                return $actual;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 3. TMS
        |--------------------------------------------------------------------------
        */

        if (
            $status
            &&
            $status->subject_id
        ) {

            $actual =
                $subjectService
                    ->resolveActualSubjectId(
                        $status->subject_id,
                        $standardId
                    );


            if ($actual) {

                return $actual;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 4. TSA
        |--------------------------------------------------------------------------
        */

        if (
            $tsa
            &&
            $tsa->subject_id
        ) {

            $actual =
                $subjectService
                    ->resolveActualSubjectId(
                        $tsa->subject_id,
                        $standardId
                    );


            if ($actual) {

                return $actual;
            }
        }


        return null;
    }


    public static function reopenAdminMarks(
        Request $request,
        $subjectService
    ) {

        $request->validate([

            'exam_master_id' =>
                'required',

            'subject_id' =>
                'required',

            'standard_id' =>
                'required',

            'division_id' =>
                'required',

            'academic_year_id' =>
                'required',

            'section_id' =>
                'required',
        ]);


        $examId =
            (int) $request->exam_master_id;


        $standardId =
            (int) $request->standard_id;


        $divisionId =
            (int) $request->division_id;


        $academicYearId =
            (int) $request->academic_year_id;


        $sectionId =
            (int) $request->section_id;


        /*
        |--------------------------------------------------------------------------
        | ACTUAL SUBJECT
        |--------------------------------------------------------------------------
        */

        $actualSubjectId =
            $subjectService
                ->resolveActualSubjectId(
                    $request->subject_id,
                    $standardId
                );


        if (!$actualSubjectId) {

            return back()
                ->with(
                    'error',
                    'Unable to resolve Subject Master ID.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | POSSIBLE SUBJECT IDS
        |--------------------------------------------------------------------------
        */

        $possibleSubjectIds =
            $subjectService
                ->getPossibleSubjectIds(
                    $actualSubjectId,
                    $standardId
                );


        /*
        |--------------------------------------------------------------------------
        | LOAD MARKS
        |--------------------------------------------------------------------------
        */

        $marks =
            StudentMark::query()
                ->where(
                    'academic_year_id',
                    $academicYearId
                )
                ->where(
                    'section_id',
                    $sectionId
                )
                ->where(
                    'exam_master_id',
                    $examId
                )
                ->whereIn(
                    'subject_id',
                    $possibleSubjectIds
                )
                ->where(
                    'standard_id',
                    $standardId
                )
                ->where(
                    'division_id',
                    $divisionId
                )
                ->get();


        if (
            $marks->isEmpty()
        ) {

            return back()
                ->with(
                    'error',
                    'No marks found for the selected Subject.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | TRANSACTION
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $request,
                $marks,
                $examId
            ) {

                foreach (
                    $marks as $mark
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | UNLOCK
                    |--------------------------------------------------------------------------
                    */

                    $mark->update([

                        'is_locked' =>
                            0,

                        'updated_by' =>
                            Auth::id(),
                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | AUDIT
                    |--------------------------------------------------------------------------
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
                            'REOPEN',

                        'remarks' =>
                            $request->remarks
                            ??
                            'Marks reopened by admin',

                        'ip_address' =>
                            $request->ip(),

                        'user_agent' =>
                            $request->userAgent(),
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | RESET STATUS
                |--------------------------------------------------------------------------
                */

                $tsaIds =
                    $marks
                        ->pluck(
                            'teacher_subject_allocation_id'
                        )
                        ->filter()
                        ->unique()
                        ->values();


                if (
                    $tsaIds->isNotEmpty()
                ) {

                    TeacherMarksStatus::query()
                        ->where(
                            'exam_master_id',
                            $examId
                        )
                        ->whereIn(
                            'teacher_subject_allocation_id',
                            $tsaIds
                        )
                        ->update([

                            'status' =>
                                'PENDING',

                            'updated_at' =>
                                now(),
                        ]);
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        */

        return redirect()->route(
            'result-generation.admin-marks.edit',
            [

                'academic_year_id' =>
                    $academicYearId,

                'exam_master_id' =>
                    $examId,

                'teacher_subject_allocation_id' =>
                    $request->input(
                        'teacher_subject_allocation_id'
                    ),

                'subject_id' =>
                    $actualSubjectId,

                'standard_id' =>
                    $standardId,

                'division_id' =>
                    $divisionId,

                'section_id' =>
                    $sectionId,

                'marks_reopened' =>
                    1,
            ]
        )
        ->with(
            'success',
            'Marks reopened successfully.'
        );
    }
}
