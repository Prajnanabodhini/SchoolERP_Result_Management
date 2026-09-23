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

        $isOptionalEnabled =
            false;

        $passingPercentage =
            40;

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


                $isOptionalEnabled =
                    self::isOptionalEnabledForAllocation(
                        $allocation
                    );


                if ($allocation) {

                    $passingPercentage =
                        ResultHelper::getPassingPercentage(
                            $allocation->standard_id
                        );
                }


                $actualSubjectId =
                    (int) (
                        $teacherSubjectAllocation
                            ->subject_id
                        ?? 0
                    );


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


                if (
                    $existingMarks->isNotEmpty()
                ) {

                    $marksLocked =
                        true;

                    $marksStatus =
                        'COMPLETED';


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


                        $student->is_absent =
                            (int) (
                                $mark->is_absent
                                ?? 0
                            );


                        $student->is_optional =
                            (int) (
                                $mark->is_optional
                                ?? 0
                            );


                        $student->is_locked =
                            (int) (
                                $mark->is_locked
                                ?? 0
                            );
                    }
                }
            }
        }


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
        $markIds =
            $request->input(
                'mark_ids',
                []
            );


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


        $marks =
            StudentMark::whereIn(
                'id',
                $markIds
            )
            ->get()
            ->keyBy(
                'id'
            );


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


            $allocation =
                $mark->teacherSubjectAllocation
                ?? null;


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


            if (!$isOptionalAllowed) {

                $isOptional =
                    0;
            }


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


                if ($isAbsent) {

                    $theory =
                        0;

                    $oral =
                        0;

                    $practical =
                        0;
                }
            }


            $mark->update([

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

                'updated_by' =>
                    Auth::id(),
            ]);


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

            'marksLocked' =>
                false,

            'isOptionalEnabled' =>
                false,

            'passingPercentage' =>
                40,

            'message' =>
                '',

            'error' =>
                '',
        ];
    }


    public static function isOptionalEnabledForStandard($standardId, $allocation = null): bool
    {
        $standardId = (int) $standardId;

        if (in_array($standardId, [11, 12, 19, 20, 21, 22, 23, 24], true)) {
            return true;
        }

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


        $requestedExamId =
            $request->input(
                'exam_master_id'
            );


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


        $standardId =
            (int) (
                $status?->standard_id
                ??
                $allocation->standard_id
            );


        $divisionId =
            (int) (
                $status?->division_id
                ??
                $allocation->division_id
            );


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


        $sectionId =
            (int) (
                $allocation->section_id
                ??
                0
            );


        $data['isOptionalEnabled'] =
            self::isOptionalEnabledForStandard(
                $standardId,
                $allocation
            );


        $data['passingPercentage'] =
            self::getPassingPercentage(
                $standardId
            );


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


        $displayAssignment =
            self::buildDisplayAssignment(
                $tsa,
                $exam,
                $subject,
                $allocation,
                $status,
                $selectedSubjectId
            );


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


        $data['marksLocked'] =
            false;


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


        return $marks
            ->unique(
                'student_id'
            )
            ->keyBy(
                'student_id'
            );
    }


    /**
     * Resolve the theory / oral / practical configuration for a single
     * subject in a single exam.
     *
     * Priority:
     *   1. exam_master_subjects split columns (if they exist and are > 0)
     *   2. Hardcoded exam structure (same source the Exam Master edit page uses)
     *   3. Whole-subject max as theory (last-resort fallback)
     */
    public static function getComponentConfig(
        $exam,
        $subjectConfig,
        $standardId
    ): array {

        /* ==========================================================
         | 1. Split columns on exam_master_subjects
         ========================================================== */

        $subjectMax     = (float) ($subjectConfig->max_marks     ?? 0);
        $subjectPassing = (float) ($subjectConfig->passing_marks ?? 0);

        $theoryMax     = (float) ($subjectConfig->theory_max_marks        ?? 0);
        $theoryPass    = (float) ($subjectConfig->theory_passing_marks    ?? 0);
        $oralMax       = (float) ($subjectConfig->oral_max_marks          ?? 0);
        $oralPass      = (float) ($subjectConfig->oral_passing_marks      ?? 0);
        $practicalMax  = (float) ($subjectConfig->practical_max_marks     ?? 0);
        $practicalPass = (float) ($subjectConfig->practical_passing_marks ?? 0);

        /* ==========================================================
         | 2. Hardcoded exam structure
         ========================================================== */

        if ($theoryMax <= 0 && $oralMax <= 0 && $practicalMax <= 0) {

            $subjectName = (string) (
                $subjectConfig->subject_name
                ?? ''
            );

            $values = \App\Helpers\ExamStructureHelper::getComponentValues(
                (int) $standardId,
                (string) ($exam->exam_name ?? ''),
                $subjectName
            );

            $theoryMax     = (float) $values['theory_max_marks'];
            $theoryPass    = (float) $values['theory_passing_marks'];
            $oralMax       = (float) $values['oral_max_marks'];
            $oralPass      = (float) $values['oral_passing_marks'];
            $practicalMax  = (float) $values['practical_max_marks'];
            $practicalPass = (float) $values['practical_passing_marks'];
        }

        /* ==========================================================
         | 3. Last-resort: treat whole subject as theory
         ========================================================== */

        if ($theoryMax <= 0 && $oralMax <= 0 && $practicalMax <= 0) {

            $theoryMax  = $subjectMax;
            $theoryPass = $subjectPassing > 0
                ? $subjectPassing
                : ResultHelper::getPassingMarks($standardId, $subjectMax);
        }

        /* ==========================================================
         | Show flags
         ========================================================== */

        $showTheory    = $theoryMax    > 0;
        $showOral      = $oralMax      > 0;
        $showPractical = $practicalMax > 0;

        return [
            'showTheory'            => $showTheory,
            'showOral'              => $showOral,
            'showPractical'         => $showPractical,

            'theoryMaxMarks'        => $theoryMax,
            'theoryPassingMarks'    => $theoryPass,

            'oralMaxMarks'          => $showOral      ? $oralMax       : 0,
            'oralPassingMarks'      => $showOral      ? $oralPass      : 0,

            'practicalMaxMarks'     => $showPractical ? $practicalMax  : 0,
            'practicalPassingMarks' => $showPractical ? $practicalPass : 0,
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


        $standardId =
            (int) (
                $status?->standard_id
                ??
                $classAllocation->standard_id
            );


        $divisionId =
            (int) (
                $status?->division_id
                ??
                $classAllocation->division_id
            );


        $academicYearId =
            (int) (
                $status?->academic_year_id
                ??
                $classAllocation->academic_year_id
                ??
                0
            );


        $sectionId =
            (int) (
                $classAllocation->section_id
                ??
                0
            );


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


        $isOptionalEnabled =
            self::isOptionalEnabledForStandard(
                $standardId,
                $classAllocation
            );


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


                    if ($isOptional) {

                        $isAbsent =
                            0;
                    }


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


                    $theory =
                        self::validateMark(
                            $theory,
                            $theoryMax,
                            !$isAbsent && !$isOptional,
                            'Theory',
                            $studentId
                        );


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

                        'is_locked' =>
                            0,

                        'updated_by' =>
                            Auth::id(),
                    ];


                    if ($mark) {

                        $mark->update(
                            $saveData
                        );

                        $wasCreated =
                            false;

                    } else {

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


        $possibleSubjectIds =
            $subjectService
                ->getPossibleSubjectIds(
                    $actualSubjectId,
                    $standardId
                );


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


        DB::transaction(
            function () use (
                $request,
                $marks,
                $examId
            ) {

                foreach (
                    $marks as $mark
                ) {

                    $mark->update([

                        'is_locked' =>
                            0,

                        'updated_by' =>
                            Auth::id(),
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
                            $request->userAgent(),
                    ]);
                }


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

        /* ======================================================================
     | BLADE STATE EXTRACTION
     |
     | Consolidates all the per-request computations the admin marks edit
     | blade needs. Keeps the blade itself thin.
     ====================================================================== */

    public static function extractEditBladeState(
        $existingMarks,
        $assignments,
        $selectedTsaId,
        $isOptionalEnabled,
        $teacherSubjectAllocation,
        $selectedClassAllocation
    ): array {

        /* ---------- Last modified ---------- */
        $latest = collect($existingMarks ?? [])
            ->filter(fn ($m) => !empty($m->updated_at))
            ->sortByDesc(fn ($m) => $m->updated_at)
            ->first();

        $lastModifiedAt   = $latest->updated_at ?? null;
        $lastModifiedById = $latest->updated_by ?? null;

        $lastModifiedByName = '';
        if ($lastModifiedById) {
            try {
                $lastModifiedByName = optional(\App\Models\User::find($lastModifiedById))->name
                    ?? ('User ID ' . $lastModifiedById);
            } catch (\Throwable $e) {
                $lastModifiedByName = 'User ID ' . $lastModifiedById;
            }
        }

        /* ---------- Optional column flag ---------- */
        $allocation = $teacherSubjectAllocation?->allocation
            ?? $selectedClassAllocation
            ?? null;

        $standardId = (int) ($allocation?->standard_id ?? 0);

        $showOptionalColumn = (bool) ($isOptionalEnabled ?? false)
            || in_array($standardId, [19, 20, 21, 22, 23, 24], true);

        /* ---------- Current status ---------- */
        $currentStatus = 'PENDING';
        if ($selectedTsaId && $assignments) {
            $rec = $assignments->firstWhere('id', $selectedTsaId);
            if ($rec) {
                $currentStatus = strtoupper(trim((string) ($rec->resolved_status ?? 'PENDING')));
            }
        }

        return [
            'lastModifiedAt'     => $lastModifiedAt,
            'lastModifiedByName' => $lastModifiedByName,
            'showOptionalColumn' => $showOptionalColumn,
            'currentStatus'      => $currentStatus,
            'statusBadgeClass'   => self::statusBadgeClass($currentStatus),
        ];
    }


    /**
     * CSS class suffix for a status badge.
     */
    public static function statusBadgeClass(string $status): string
    {
        return match (strtoupper(trim($status))) {
            'COMPLETED' => 'admin-status-completed',
            'LOCKED'    => 'admin-status-locked',
            'PENDING'   => 'admin-status-pending',
            default     => 'admin-status-default',
        };
    }


    /**
     * Format a mark as an integer string (empty string when null).
     */
    public static function formatIntegerMark($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        return (string) (int) round((float) $value);
    }
}