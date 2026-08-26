<?php

namespace App\Http\Controllers\Marks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\AcademicYear;
use App\Models\ExamMaster;
use App\Models\TeacherMarksStatus;
use App\Models\TeacherSubjectAllocation;
use App\Models\StudentMark;
use App\Models\ExamMasterSubject;
use App\Helpers\StudentHelper;

class MarkEntryController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | ADMINISTRATOR
    |--------------------------------------------------------------------------
    */

    private function isAdministrator(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasRole')) {

            if (
                $user->hasRole('Administrator') ||
                $user->hasRole('admin')
            ) {
                return true;
            }
        }

        $role = strtolower(
            trim((string) ($user->role ?? ''))
        );

        return in_array(
            $role,
            [
                'administrator',
                'admin',
            ],
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE EXAM ACADEMIC YEAR
    |--------------------------------------------------------------------------
    |
    | Exam Master now contains academic_year_id.
    |
    | This method validates:
    |
    |     Exam Academic Year
    |             =
    |     Allocation Academic Year
    |
    | If $requestedAcademicYearId is provided, it is also checked.
    |
    */

    private function validateExamAcademicYear(
        $exam,
        $allocation = null,
        $requestedAcademicYearId = null
    ): ?string {

        if (!$exam) {
            return 'Selected exam was not found.';
        }

        $examAcademicYearId =
            $exam->academic_year_id !== null
                ? (int) $exam->academic_year_id
                : null;

        /*
        |--------------------------------------------------------------------------
        | EXAM MUST HAVE ACADEMIC YEAR
        |--------------------------------------------------------------------------
        */

        if (!$examAcademicYearId) {

            return
                'Selected Exam does not have an Academic Year assigned.';
        }

        /*
        |--------------------------------------------------------------------------
        | REQUEST YEAR CHECK
        |--------------------------------------------------------------------------
        */

        if (
            $requestedAcademicYearId !== null &&
            $requestedAcademicYearId !== ''
        ) {

            if (
                $examAcademicYearId !==
                (int) $requestedAcademicYearId
            ) {

                return
                    'Selected Exam does not belong to the selected Academic Year.';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | ALLOCATION YEAR CHECK
        |--------------------------------------------------------------------------
        */

        if ($allocation) {

            $allocationAcademicYearId =
                $allocation->academic_year_id !== null
                    ? (int) $allocation->academic_year_id
                    : null;

            if (
                $allocationAcademicYearId &&
                $examAcademicYearId !==
                $allocationAcademicYearId
            ) {

                return
                    'Selected Exam does not belong to the Academic Year of the selected Teaching Assignment.';
            }
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | BUILD SUBJECT RESOLUTION MAP
    |--------------------------------------------------------------------------
    |
    | Supports both:
    |
    | CURRENT:
    | TSA.subject_id = subjects.id
    |
    | LEGACY:
    | TSA.subject_id = standard_wise_subjects.id
    |
    | IMPORTANT:
    | Current and legacy keys are kept separate.
    |
    */

    private function buildSubjectResolutionMap($assignments)
    {
        $map = collect();

        if ($assignments->isEmpty()) {
            return $map;
        }

        $standardIds = $assignments
            ->pluck('allocation.standard_id')
            ->filter()
            ->unique()
            ->values();

        if ($standardIds->isEmpty()) {
            return $map;
        }

        $mappings = DB::table('standard_wise_subjects as sws')
            ->join(
                'subjects as s',
                's.id',
                '=',
                'sws.subject_id'
            )
            ->whereIn(
                'sws.standard_id',
                $standardIds
            )
            ->where(
                'sws.is_active',
                1
            )
            ->where(
                's.is_active',
                1
            )
            ->select([
                'sws.id as sws_id',
                'sws.standard_id',
                'sws.subject_id',
                's.id as actual_subject_id',
                's.subject_name',
                's.subject_code',
                's.short_name',
            ])
            ->get();

        foreach ($mappings as $mapping) {

            /*
            |--------------------------------------------------------------------------
            | CURRENT FORMAT
            |--------------------------------------------------------------------------
            */

            $currentKey =
                (int) $mapping->standard_id
                . ':subject:'
                . (int) $mapping->subject_id;

            $map->put(
                $currentKey,
                $mapping
            );

            /*
            |--------------------------------------------------------------------------
            | LEGACY FORMAT
            |--------------------------------------------------------------------------
            */

            $legacyKey =
                (int) $mapping->standard_id
                . ':sws:'
                . (int) $mapping->sws_id;

            $map->put(
                $legacyKey,
                $mapping
            );
        }

        return $map;
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE ACTUAL SUBJECT ID
    |--------------------------------------------------------------------------
    */

    private function resolveActualSubjectId(
        $storedSubjectId,
        $standardId,
        $subjectMap = null
    ) {
        if (
            $storedSubjectId === null ||
            $storedSubjectId === '' ||
            !$standardId
        ) {
            return null;
        }

        $storedSubjectId =
            (int) $storedSubjectId;

        $standardId =
            (int) $standardId;

        if (
            $storedSubjectId <= 0 ||
            $standardId <= 0
        ) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | CURRENT FORMAT
        |--------------------------------------------------------------------------
        */

        if (
            $subjectMap instanceof
            \Illuminate\Support\Collection
        ) {

            $currentKey =
                $standardId
                . ':subject:'
                . $storedSubjectId;

            $mapping =
                $subjectMap->get(
                    $currentKey
                );

            if ($mapping) {

                return (int)
                    $mapping->actual_subject_id;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | LEGACY FORMAT
        |--------------------------------------------------------------------------
        */

        if (
            $subjectMap instanceof
            \Illuminate\Support\Collection
        ) {

            $legacyKey =
                $standardId
                . ':sws:'
                . $storedSubjectId;

            $mapping =
                $subjectMap->get(
                    $legacyKey
                );

            if ($mapping) {

                return (int)
                    $mapping->actual_subject_id;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | DIRECT SUBJECT FALLBACK
        |--------------------------------------------------------------------------
        */

        $subject =
            DB::table('subjects')
                ->where(
                    'id',
                    $storedSubjectId
                )
                ->where(
                    'is_active',
                    1
                )
                ->first();

        if ($subject) {

            $exists =
                DB::table(
                    'standard_wise_subjects'
                )
                ->where(
                    'standard_id',
                    $standardId
                )
                ->where(
                    'subject_id',
                    $storedSubjectId
                )
                ->where(
                    'is_active',
                    1
                )
                ->exists();

            if ($exists) {

                return $storedSubjectId;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | DIRECT LEGACY SWS FALLBACK
        |--------------------------------------------------------------------------
        */

        $mapping =
            DB::table(
                'standard_wise_subjects'
            )
            ->where(
                'id',
                $storedSubjectId
            )
            ->where(
                'standard_id',
                $standardId
            )
            ->where(
                'is_active',
                1
            )
            ->first();

        if (
            $mapping &&
            !empty($mapping->subject_id)
        ) {

            return (int)
                $mapping->subject_id;
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE DISPLAY SUBJECT
    |--------------------------------------------------------------------------
    */

    private function resolveDisplaySubject(
        $storedSubjectId,
        $standardId,
        $tmsSubjectId = null,
        $subjectMap = null,
        $subjectCollection = null
    ) {
        $standardId =
            (int) $standardId;

        if (
            $standardId <= 0
        ) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | PRIMARY: TSA SUBJECT
        |--------------------------------------------------------------------------
        */

        $actualSubjectId =
            $this->resolveActualSubjectId(
                $storedSubjectId,
                $standardId,
                $subjectMap
            );

        if ($actualSubjectId) {

            if (
                $subjectCollection instanceof
                \Illuminate\Support\Collection
            ) {

                $subject =
                    $subjectCollection->get(
                        $actualSubjectId
                    );

                if ($subject) {
                    return $subject;
                }
            }

            $subject =
                DB::table('subjects')
                    ->where(
                        'id',
                        $actualSubjectId
                    )
                    ->where(
                        'is_active',
                        1
                    )
                    ->first();

            if ($subject) {
                return $subject;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | SECONDARY: TEACHER MARK STATUS SUBJECT
        |--------------------------------------------------------------------------
        */

        if (
            $tmsSubjectId !== null &&
            $tmsSubjectId !== '' &&
            (int) $tmsSubjectId > 0
        ) {

            $tmsSubjectId =
                (int) $tmsSubjectId;

            /*
            |--------------------------------------------------------------------------
            | TMS = CURRENT SUBJECT ID
            |--------------------------------------------------------------------------
            */

            if (
                $subjectMap instanceof
                \Illuminate\Support\Collection
            ) {

                $currentKey =
                    $standardId
                    . ':subject:'
                    . $tmsSubjectId;

                $mapping =
                    $subjectMap->get(
                        $currentKey
                    );

                if ($mapping) {

                    $actualSubjectId =
                        (int)
                        $mapping->actual_subject_id;

                    if (
                        $subjectCollection instanceof
                        \Illuminate\Support\Collection
                    ) {

                        $subject =
                            $subjectCollection->get(
                                $actualSubjectId
                            );

                        if ($subject) {
                            return $subject;
                        }
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | TMS = LEGACY SWS ID
            |--------------------------------------------------------------------------
            */

            if (
                $subjectMap instanceof
                \Illuminate\Support\Collection
            ) {

                $legacyKey =
                    $standardId
                    . ':sws:'
                    . $tmsSubjectId;

                $mapping =
                    $subjectMap->get(
                        $legacyKey
                    );

                if ($mapping) {

                    $actualSubjectId =
                        (int)
                        $mapping->actual_subject_id;

                    if (
                        $subjectCollection instanceof
                        \Illuminate\Support\Collection
                    ) {

                        $subject =
                            $subjectCollection->get(
                                $actualSubjectId
                            );

                        if ($subject) {
                            return $subject;
                        }
                    }

                    $subject =
                        DB::table('subjects')
                            ->where(
                                'id',
                                $actualSubjectId
                            )
                            ->where(
                                'is_active',
                                1
                            )
                            ->first();

                    if ($subject) {
                        return $subject;
                    }
                }
            }
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK WHETHER TSA REPRESENTS SUBJECT
    |--------------------------------------------------------------------------
    */

    private function tsaRepresentsSubject(
        $tsa,
        $actualSubjectId,
        $standardId
    ) {
        $storedSubjectId =
            (int) (
                $tsa->subject_id ?? 0
            );

        $actualSubjectId =
            (int) $actualSubjectId;

        $standardId =
            (int) $standardId;

        if (
            $storedSubjectId <= 0 ||
            $actualSubjectId <= 0 ||
            $standardId <= 0
        ) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | CURRENT FORMAT
        |--------------------------------------------------------------------------
        */

        if (
            $storedSubjectId ===
            $actualSubjectId
        ) {

            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | LEGACY FORMAT
        |--------------------------------------------------------------------------
        */

        $mapping =
            DB::table(
                'standard_wise_subjects'
            )
            ->where(
                'id',
                $storedSubjectId
            )
            ->where(
                'standard_id',
                $standardId
            )
            ->where(
                'is_active',
                1
            )
            ->first();

        if (
            $mapping &&
            (int)
            $mapping->subject_id ===
            $actualSubjectId
        ) {

            return true;
        }

        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | GET RELATED TSA IDS
    |--------------------------------------------------------------------------
    */

    private function getRelatedTeacherSubjectAllocationIds(
        $currentTsa,
        $allocation,
        $actualSubjectId,
        $examId
    ) {
        $ids = collect();

        if (
            !$currentTsa ||
            !$allocation
        ) {
            return $ids;
        }

        /*
        |--------------------------------------------------------------------------
        | CURRENT TSA
        |--------------------------------------------------------------------------
        */

        $ids->push(
            (int) $currentTsa->id
        );

        /*
        |--------------------------------------------------------------------------
        | SAME CLASS ALLOCATION + EXAM
        |--------------------------------------------------------------------------
        */

        $query =
            TeacherSubjectAllocation::query()
                ->where(
                    'exam_master_id',
                    (int) $examId
                )
                ->where(
                    'teacher_class_allocation_id',
                    (int)
                    $currentTsa
                        ->teacher_class_allocation_id
                );

        /*
        |--------------------------------------------------------------------------
        | POSSIBLE SUBJECT IDS
        |--------------------------------------------------------------------------
        */

        $possibleSubjectIds =
            collect();

        if (
            $actualSubjectId
        ) {

            $actualSubjectId =
                (int) $actualSubjectId;

            /*
            | Current Subjects ID
            */

            $possibleSubjectIds->push(
                $actualSubjectId
            );

            /*
            | Legacy SWS IDs
            */

            $legacyMappings =
                DB::table(
                    'standard_wise_subjects'
                )
                ->where(
                    'standard_id',
                    (int)
                    $allocation->standard_id
                )
                ->where(
                    'subject_id',
                    $actualSubjectId
                )
                ->where(
                    'is_active',
                    1
                )
                ->get();

            foreach (
                $legacyMappings
                as $legacyMapping
            ) {

                $possibleSubjectIds->push(
                    (int)
                    $legacyMapping->id
                );
            }
        }

        if (
            $possibleSubjectIds->isNotEmpty()
        ) {

            $query->whereIn(
                'subject_id',
                $possibleSubjectIds
                    ->unique()
                    ->values()
                    ->all()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | RELATED RECORDS
        |--------------------------------------------------------------------------
        */

        $related =
            $query
                ->pluck('id')
                ->map(
                    fn ($id) =>
                        (int) $id
                );

        $ids =
            $ids
                ->merge($related)
                ->unique()
                ->values();

        return $ids;
    }


    /*
    |--------------------------------------------------------------------------
    | LOAD EXISTING MARKS INCLUDING OLD TSA
    |--------------------------------------------------------------------------
    */

    private function loadExistingMarks(
        $teacherSubjectAllocation,
        $allocation,
        $actualSubjectId,
        $examId
    ) {
        $empty =
            collect();

        if (
            !$teacherSubjectAllocation ||
            !$allocation ||
            !$examId
        ) {
            return $empty;
        }

        /*
        |--------------------------------------------------------------------------
        | GET CURRENT + RELATED TSA IDS
        |--------------------------------------------------------------------------
        */

        $tsaIds =
            $this->getRelatedTeacherSubjectAllocationIds(
                $teacherSubjectAllocation,
                $allocation,
                $actualSubjectId,
                $examId
            );

        if (
            $tsaIds->isEmpty()
        ) {
            return $empty;
        }

        /*
        |--------------------------------------------------------------------------
        | FETCH MARKS
        |--------------------------------------------------------------------------
        */

        $marks =
            StudentMark::query()
                ->where(
                    'exam_master_id',
                    (int) $examId
                )
                ->whereIn(
                    'teacher_subject_allocation_id',
                    $tsaIds
                )
                ->orderByDesc(
                    'id'
                )
                ->get();

        /*
        |--------------------------------------------------------------------------
        | ONE MARK PER STUDENT
        |--------------------------------------------------------------------------
        |
        | Current TSA wins.
        |
        */

        $result =
            collect();

        foreach (
            $marks as $mark
        ) {

            $studentId =
                (string)
                $mark->student_id;

            if (
                !$result->has(
                    $studentId
                )
            ) {

                $result->put(
                    $studentId,
                    $mark
                );

                continue;
            }

            /*
            |----------------------------------------------------------------------
            | CURRENT TSA PRIORITY
            |----------------------------------------------------------------------
            */

            if (
                (int)
                $mark->teacher_subject_allocation_id
                ===
                (int)
                $teacherSubjectAllocation->id
            ) {

                $result->put(
                    $studentId,
                    $mark
                );
            }
        }

        return $result;
    }


    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ) {
        /*
        |--------------------------------------------------------------------------
        | INITIAL VALUES
        |--------------------------------------------------------------------------
        */

        $students =
            collect();

        $assignments =
            collect();

        $academicYears =
            collect();

        $exams =
            collect();

        $exam =
            null;

        $teacherSubjectAllocation =
            null;

        $selectedClassAllocation =
            null;

        $subjectConfig =
            null;

        $error =
            '';

        $message =
            '';

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

        $marksLocked =
            false;

        $existingMarks =
            collect();

        $marksStatus =
            null;


        /*
        |--------------------------------------------------------------------------
        | AUTH
        |--------------------------------------------------------------------------
        */

        $user =
            Auth::user();

        if (
            !$user
        ) {
            abort(403);
        }

        $userId =
            (int)
            Auth::id();

        $isAdministrator =
            $this->isAdministrator();


        /*
        |--------------------------------------------------------------------------
        | REQUEST
        |--------------------------------------------------------------------------
        */

        $academicYearId =
            $request->input(
                'academic_year_id'
            );

        $examId =
            $request->input(
                'exam_master_id'
            );

        $tsaId =
            $request->input(
                'teacher_subject_allocation_id'
            );


        /*
        |--------------------------------------------------------------------------
        | ACADEMIC YEARS
        |--------------------------------------------------------------------------
        */

        $academicYears =
            AcademicYear::query()
                ->where(
                    'is_active',
                    1
                )
                ->orderByDesc(
                    'id'
                )
                ->get();


        /*
        |--------------------------------------------------------------------------
        | EXAMS
        |--------------------------------------------------------------------------
        |
        | If Academic Year is already selected, show exams belonging
        | to that Academic Year.
        |
        */

        $examQuery =
            ExamMaster::query()
                ->where(
                    'is_active',
                    1
                )
                ->orderBy(
                    'display_order'
                )
                ->orderBy(
                    'exam_name'
                );

        if (
            $academicYearId !== null &&
            $academicYearId !== ''
        ) {

            $examQuery->where(
                'academic_year_id',
                (int)
                $academicYearId
            );
        }

        $exams =
            $examQuery->get();


        /*
        |--------------------------------------------------------------------------
        | SELECTED EXAM
        |--------------------------------------------------------------------------
        */

        if (
            $examId
        ) {

            /*
            |--------------------------------------------------------------------------
            | Do not rely only on filtered collection.
            | Fetch the actual exam for validation.
            |--------------------------------------------------------------------------
            */

            $exam =
                ExamMaster::where(
                    'id',
                    (int) $examId
                )
                ->where(
                    'is_active',
                    1
                )
                ->first();

            if (
                !$exam
            ) {

                $error =
                    'Selected exam was not found.';

            } else {

                $yearError =
                    $this->validateExamAcademicYear(
                        $exam,
                        null,
                        $academicYearId
                    );

                if (
                    $yearError
                ) {

                    $error =
                        $yearError;

                    $exam =
                        null;
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD SELECTED TSA
        |--------------------------------------------------------------------------
        */

        if (
            $tsaId
        ) {

            $tsaQuery =
                TeacherSubjectAllocation::query()
                    ->with([
                        'allocation.teacher',
                        'allocation.academicYear',
                        'allocation.section',
                        'allocation.standard',
                        'allocation.division',
                        'exam',
                    ])
                    ->where(
                        'id',
                        (int) $tsaId
                    );

            if (
                $examId
            ) {

                $tsaQuery->where(
                    'exam_master_id',
                    (int) $examId
                );
            }

            /*
            |--------------------------------------------------------------------------
            | NORMAL TEACHER RESTRICTION
            |--------------------------------------------------------------------------
            */

            if (
                !$isAdministrator
            ) {

                $tsaQuery->whereHas(
                    'allocation',
                    function (
                        $query
                    ) use (
                        $userId
                    ) {

                        $query->where(
                            'user_id',
                            $userId
                        );
                    }
                );
            }

            $teacherSubjectAllocation =
                $tsaQuery->first();

            if (
                !$teacherSubjectAllocation
            ) {

                $error =
                    'Selected teaching assignment was not found or is not assigned to you.';

            } else {

                $selectedClassAllocation =
                    $teacherSubjectAllocation
                        ->allocation;

                if (
                    !$selectedClassAllocation
                ) {

                    $teacherSubjectAllocation =
                        null;

                    $error =
                        'Teacher class allocation not found.';

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | ACADEMIC YEAR FROM ALLOCATION
                    |--------------------------------------------------------------------------
                    */

                    $resolvedYear =
                        $selectedClassAllocation
                            ->academic_year_id
                        ?? null;

                    if (
                        $resolvedYear !== null &&
                        $resolvedYear !== ''
                    ) {

                        $academicYearId =
                            (int)
                            $resolvedYear;

                        $request->merge([
                            'academic_year_id' =>
                                $academicYearId,
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | EXAM FROM TSA
                    |--------------------------------------------------------------------------
                    */

                    $tsaExam =
                        $teacherSubjectAllocation
                            ->exam;

                    if (
                        !$tsaExam
                    ) {

                        $error =
                            'Exam linked to the selected teaching assignment was not found.';

                    } else {

                        /*
                        |--------------------------------------------------------------------------
                        | IMPORTANT:
                        | TSA allocation year must match Exam year.
                        |--------------------------------------------------------------------------
                        */

                        $yearError =
                            $this->validateExamAcademicYear(
                                $tsaExam,
                                $selectedClassAllocation,
                                $academicYearId
                            );

                        if (
                            $yearError
                        ) {

                            $error =
                                $yearError;

                        } else {

                            /*
                            |--------------------------------------------------------------------------
                            | ALWAYS USE TSA EXAM
                            |--------------------------------------------------------------------------
                            */

                            $exam =
                                $tsaExam;

                            /*
                            |--------------------------------------------------------------------------
                            | Ensure the exam exists in the displayed list.
                            |--------------------------------------------------------------------------
                            */

                            if (
                                !$exams->contains(
                                    'id',
                                    $exam->id
                                )
                            ) {

                                $exams->push(
                                    $exam
                                );

                                $exams =
                                    $exams
                                        ->sortBy([
                                            [
                                                'display_order',
                                                'asc',
                                            ],
                                            [
                                                'exam_name',
                                                'asc',
                                            ],
                                        ])
                                        ->values();
                            }
                        }
                    }
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | IF ONLY EXAM WAS SELECTED, APPLY YEAR CHECK
        |--------------------------------------------------------------------------
        */

        if (
            $exam &&
            !$teacherSubjectAllocation
        ) {

            $yearError =
                $this->validateExamAcademicYear(
                    $exam,
                    null,
                    $academicYearId
                );

            if (
                $yearError
            ) {

                $error =
                    $yearError;

                $exam =
                    null;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD ASSIGNMENTS
        |--------------------------------------------------------------------------
        */

        $assignmentQuery =
            TeacherSubjectAllocation::query()
                ->with([
                    'allocation.teacher',
                    'allocation.academicYear',
                    'allocation.section',
                    'allocation.standard',
                    'allocation.division',
                    'exam',
                ]);

        if (
            $examId
        ) {

            $assignmentQuery->where(
                'exam_master_id',
                (int) $examId
            );
        }

        if (
            $academicYearId !== null &&
            $academicYearId !== ''
        ) {

            $assignmentQuery->whereHas(
                'allocation',
                function (
                    $query
                ) use (
                    $academicYearId
                ) {

                    $query->where(
                        'academic_year_id',
                        (int)
                        $academicYearId
                    );
                }
            );
        }

        if (
            !$isAdministrator
        ) {

            $assignmentQuery->whereHas(
                'allocation',
                function (
                    $query
                ) use (
                    $userId
                ) {

                    $query->where(
                        'user_id',
                        $userId
                    );
                }
            );
        }

        $assignments =
            $assignmentQuery
                ->orderByDesc(
                    'id'
                )
                ->get();


        /*
        |--------------------------------------------------------------------------
        | FILTER OUT INVALID EXAM/YEAR COMBINATIONS
        |--------------------------------------------------------------------------
        |
        | This is intentionally done in memory to avoid changing the
        | existing relationship/query behavior.
        |
        */

        $assignments =
            $assignments
                ->filter(
                    function (
                        $assignment
                    ) {

                        $allocation =
                            $assignment
                                ->allocation;

                        $assignmentExam =
                            $assignment
                                ->exam;

                        if (
                            !$allocation ||
                            !$assignmentExam
                        ) {

                            return false;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Legacy exam with NULL academic year:
                        | keep only if no year filtering is being done elsewhere.
                        |
                        | Since current Exam Masters now have Academic Year,
                        | normal current records must match.
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $assignmentExam
                                ->academic_year_id === null
                        ) {

                            return true;
                        }

                        return
                            (int)
                            $assignmentExam
                                ->academic_year_id
                            ===
                            (int)
                            $allocation
                                ->academic_year_id;
                    }
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | LOAD STATUS
        |--------------------------------------------------------------------------
        */

        $assignmentIds =
            $assignments
                ->pluck(
                    'id'
                )
                ->filter()
                ->unique()
                ->values();

        $allStatuses =
            collect();

        if (
            $assignmentIds->isNotEmpty()
        ) {

            $statusQuery =
                TeacherMarksStatus::query()
                    ->whereIn(
                        'teacher_subject_allocation_id',
                        $assignmentIds
                    );

            if (
                !$isAdministrator
            ) {

                $statusQuery->where(
                    'teacher_id',
                    $userId
                );
            }

            /*
            |--------------------------------------------------------------------------
            | ALSO KEEP STATUS YEAR CONSISTENT WHEN YEAR IS SELECTED
            |--------------------------------------------------------------------------
            */

            if (
                $academicYearId !== null &&
                $academicYearId !== ''
            ) {

                $statusQuery->where(
                    'academic_year_id',
                    (int)
                    $academicYearId
                );
            }

            $allStatuses =
                $statusQuery->get([
                    'id',
                    'teacher_subject_allocation_id',
                    'subject_id',
                    'teacher_id',
                    'exam_master_id',
                    'standard_id',
                    'division_id',
                    'academic_year_id',
                    'status',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD SUBJECTS
        |--------------------------------------------------------------------------
        */

        $standardIds =
            $assignments
                ->pluck(
                    'allocation.standard_id'
                )
                ->filter()
                ->unique()
                ->values();

        $allSubjects =
            collect();

        if (
            $standardIds->isNotEmpty()
        ) {

            $allSubjects =
                DB::table(
                    'subjects as s'
                )
                ->join(
                    'standard_wise_subjects as sws',
                    'sws.subject_id',
                    '=',
                    's.id'
                )
                ->whereIn(
                    'sws.standard_id',
                    $standardIds
                )
                ->where(
                    's.is_active',
                    1
                )
                ->where(
                    'sws.is_active',
                    1
                )
                ->select([
                    's.id',
                    's.subject_name',
                    's.subject_code',
                    's.short_name',
                ])
                ->distinct()
                ->get()
                ->keyBy(
                    'id'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | SUBJECT MAP
        |--------------------------------------------------------------------------
        */

        $subjectMap =
            $this->buildSubjectResolutionMap(
                $assignments
            );


        /*
        |--------------------------------------------------------------------------
        | STATUS MAP
        |--------------------------------------------------------------------------
        */

        $statusMap =
            $allStatuses->keyBy(
                function (
                    $status
                ) {

                    return
                        $status
                            ->teacher_subject_allocation_id
                        . ':'
                        . $status
                            ->exam_master_id;
                }
            );


        /*
        |--------------------------------------------------------------------------
        | RESOLVE ASSIGNMENTS
        |--------------------------------------------------------------------------
        */

        foreach (
            $assignments
            as $assignment
        ) {

            $allocation =
                $assignment
                    ->allocation;

            if (
                !$allocation
            ) {
                continue;
            }

            $statusKey =
                $assignment->id
                . ':'
                . $assignment
                    ->exam_master_id;

            $status =
                $statusMap->get(
                    $statusKey
                );

            if (
                !$status
            ) {

                $status =
                    $allStatuses->firstWhere(
                        'teacher_subject_allocation_id',
                        $assignment->id
                    );
            }

            $tmsSubjectId =
                $status
                    ? $status->subject_id
                    : null;

            $actualSubject =
                $this->resolveDisplaySubject(
                    $assignment->subject_id,
                    $allocation->standard_id,
                    $tmsSubjectId,
                    $subjectMap,
                    $allSubjects
                );

            if (
                $actualSubject
            ) {

                $assignment->setRelation(
                    'subject',
                    $actualSubject
                );

                $assignment->resolved_subject_id =
                    (int)
                    $actualSubject->id;
            }

            $assignment->resolved_academic_year_id =
                $allocation
                    ->academic_year_id;

            $assignment->resolved_class_allocation_id =
                $assignment
                    ->teacher_class_allocation_id;

            $assignment->resolved_exam_master_id =
                $assignment
                    ->exam_master_id;

            $assignment->resolved_standard_id =
                $allocation
                    ->standard_id;

            $assignment->resolved_division_id =
                $allocation
                    ->division_id;

            $assignment->resolved_status =
                $status
                    ? strtoupper(
                        trim(
                            (string) (
                                $status->status
                                ?? ''
                            )
                        )
                    )
                    : null;
        }


        /*
        |--------------------------------------------------------------------------
        | SELECTED STATUS
        |--------------------------------------------------------------------------
        */

        if (
            $teacherSubjectAllocation
        ) {

            $selectedAllocation =
                $teacherSubjectAllocation
                    ->allocation;

            $statusKey =
                $teacherSubjectAllocation
                    ->id
                . ':'
                .
                $teacherSubjectAllocation
                    ->exam_master_id;

            $marksStatus =
                $statusMap->get(
                    $statusKey
                );

            /*
            |--------------------------------------------------------------------------
            | FALLBACK CURRENT TSA STATUS
            |--------------------------------------------------------------------------
            */

            if (
                !$marksStatus
            ) {

                $marksStatus =
                    TeacherMarksStatus::query()
                        ->where(
                            'teacher_subject_allocation_id',
                            $teacherSubjectAllocation
                                ->id
                        )
                        ->where(
                            'exam_master_id',
                            $teacherSubjectAllocation
                                ->exam_master_id
                        )
                        ->when(
                            !$isAdministrator,
                            function (
                                $query
                            ) use (
                                $userId
                            ) {

                                $query->where(
                                    'teacher_id',
                                    $userId
                                );
                            }
                        )
                        ->when(
                            $academicYearId !== null &&
                            $academicYearId !== '',
                            function (
                                $query
                            ) use (
                                $academicYearId
                            ) {

                                $query->where(
                                    'academic_year_id',
                                    (int)
                                    $academicYearId
                                );
                            }
                        )
                        ->orderByDesc(
                            'id'
                        )
                        ->first();
            }

            /*
            |--------------------------------------------------------------------------
            | RESOLVE SELECTED SUBJECT
            |--------------------------------------------------------------------------
            */

            $tmsSubjectId =
                $marksStatus
                    ? $marksStatus->subject_id
                    : null;

            $actualSubject =
                $this->resolveDisplaySubject(
                    $teacherSubjectAllocation
                        ->subject_id,
                    $selectedAllocation
                        ->standard_id,
                    $tmsSubjectId,
                    $subjectMap,
                    $allSubjects
                );

            if (
                $actualSubject
            ) {

                $teacherSubjectAllocation
                    ->setRelation(
                        'subject',
                        $actualSubject
                    );

                $teacherSubjectAllocation
                    ->resolved_subject_id =
                        (int)
                        $actualSubject->id;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | EXAM SUBJECT CONFIGURATION
        |--------------------------------------------------------------------------
        */

        if (
            $exam &&
            $teacherSubjectAllocation
        ) {

            $allocation =
                $teacherSubjectAllocation
                    ->allocation;

            /*
            |--------------------------------------------------------------------------
            | FINAL ACADEMIC YEAR SAFETY CHECK
            |--------------------------------------------------------------------------
            */

            $yearError =
                $this->validateExamAcademicYear(
                    $exam,
                    $allocation,
                    $academicYearId
                );

            if (
                $yearError
            ) {

                $error =
                    $yearError;

                $students =
                    collect();

                $existingMarks =
                    collect();

            } else {

                $actualSubjectId =
                    $teacherSubjectAllocation
                        ->resolved_subject_id
                    ?? null;

                /*
                |--------------------------------------------------------------------------
                | CURRENT CONFIGURATION
                |--------------------------------------------------------------------------
                */

                if (
                    $actualSubjectId
                ) {

                    $subjectConfig =
                        ExamMasterSubject::query()
                            ->where(
                                'exam_master_id',
                                $exam->id
                            )
                            ->where(
                                'standard_id',
                                $allocation->standard_id
                            )
                            ->where(
                                'subject_id',
                                $actualSubjectId
                            )
                            ->first();
                }

                /*
                |--------------------------------------------------------------------------
                | LEGACY CONFIGURATION
                |--------------------------------------------------------------------------
                */

                if (
                    !$subjectConfig &&
                    $actualSubjectId
                ) {

                    $mapping =
                        DB::table(
                            'standard_wise_subjects'
                        )
                        ->where(
                            'standard_id',
                            $allocation
                                ->standard_id
                        )
                        ->where(
                            'subject_id',
                            $actualSubjectId
                        )
                        ->where(
                            'is_active',
                            1
                        )
                        ->first();

                    if (
                        $mapping
                    ) {

                        $subjectConfig =
                            ExamMasterSubject::query()
                                ->where(
                                    'exam_master_id',
                                    $exam->id
                                )
                                ->where(
                                    'standard_id',
                                    $allocation
                                        ->standard_id
                                )
                                ->whereIn(
                                    'subject_id',
                                    [
                                        $actualSubjectId,
                                        $mapping->id,
                                    ]
                                )
                                ->first();
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | CONFIGURATION NOT FOUND
                |--------------------------------------------------------------------------
                */

                if (
                    !$subjectConfig
                ) {

                    $subjectName =
                        optional(
                            $teacherSubjectAllocation
                                ->subject
                        )->subject_name
                        ??
                        'Selected Subject';

                    $standardName =
                        optional(
                            $allocation
                                ->standard
                        )->standard_name
                        ??
                        'Selected Standard';

                    $error =
                        'Marks configuration not found for '
                        . $subjectName
                        . ' in '
                        . $standardName
                        . ' - '
                        . $exam->exam_name
                        . '. Please configure this subject in Exam Master.';
                }

                /*
                |--------------------------------------------------------------------------
                | COMPONENTS
                |--------------------------------------------------------------------------
                */

                $showTheory =
                    (bool)
                    $exam->has_theory;

                $showOral =
                    (bool)
                    $exam->has_oral;

                $showPractical =
                    (bool)
                    $exam->has_practical;

                if (
                    $subjectConfig
                ) {

                    $theoryMaxMarks =
                        $subjectConfig
                            ->max_marks
                        ?? 0;

                    $theoryPassingMarks =
                        $subjectConfig
                            ->passing_marks
                        ?? 0;
                }

                if (
                    $showOral
                ) {

                    $oralMaxMarks =
                        $exam
                            ->oral_max_marks
                        ?? 0;

                    $oralPassingMarks =
                        $exam
                            ->oral_passing_marks
                        ?? 0;
                }

                if (
                    $showPractical
                ) {

                    $practicalMaxMarks =
                        $exam
                            ->practical_max_marks
                        ?? 0;

                    $practicalPassingMarks =
                        $exam
                            ->practical_passing_marks
                        ?? 0;
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD STUDENTS
        |--------------------------------------------------------------------------
        */

        if (
            $teacherSubjectAllocation &&
            $selectedClassAllocation &&
            $exam &&
            !$error
        ) {

            $allocation =
                $selectedClassAllocation;

            /*
            |--------------------------------------------------------------------------
            | FINAL SAFETY CHECK BEFORE ERP LOAD
            |--------------------------------------------------------------------------
            */

            $yearError =
                $this->validateExamAcademicYear(
                    $exam,
                    $allocation,
                    $academicYearId
                );

            if (
                $yearError
            ) {

                $error =
                    $yearError;

                $students =
                    collect();

            } else {

                $erpAcademicYearId =
                    (int) (
                        $allocation
                            ->academic_year_id
                        ?? 0
                    );

                $erpStandardId =
                    (int) (
                        $allocation
                            ->standard_id
                        ?? 0
                    );

                $erpDivisionId =
                    (int) (
                        $allocation
                            ->division_id
                        ?? 0
                    );

                try {

                    if (
                        $erpAcademicYearId > 0 &&
                        $erpStandardId > 0 &&
                        $erpDivisionId > 0
                    ) {

                        $students =
                            StudentHelper::getStudentsDirectERP(
                                $erpAcademicYearId,
                                $erpStandardId,
                                $erpDivisionId
                            );

                        if (
                            !$students instanceof
                            \Illuminate\Support\Collection
                        ) {

                            $students =
                                collect(
                                    $students
                                );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | SORT BY ROLL NUMBER
                        |--------------------------------------------------------------------------
                        */

                        $students =
                            $students
                                ->sortBy(
                                    function (
                                        $student
                                    ) {

                                        $roll =
                                            $student->roll_no
                                            ??
                                            $student->roll_number
                                            ??
                                            $student->roll
                                            ??
                                            $student
                                                ->student_roll_no
                                            ??
                                            null;

                                        if (
                                            $roll === null ||
                                            $roll === ''
                                        ) {

                                            return PHP_INT_MAX;
                                        }

                                        return (int)
                                            $roll;
                                    }
                                )
                                ->values();
                    }

                } catch (
                    \Throwable $e
                ) {

                    report(
                        $e
                    );

                    $students =
                        collect();

                    $error =
                        'Old ERP Error: '
                        . $e->getMessage();
                }

                if (
                    $students->isEmpty()
                ) {

                    $standardName =
                        optional(
                            $allocation
                                ->standard
                        )->standard_name
                        ??
                        'Selected Standard';

                    $divisionName =
                        optional(
                            $allocation
                                ->division
                        )->division_name
                        ??
                        'Selected Division';

                    $error =
                        'No students found for '
                        . $standardName
                        . ' - '
                        . $divisionName
                        . '. Please verify Old ERP student mapping.';
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | COMPLETED / PENDING
        |--------------------------------------------------------------------------
        |
        | ONLY CURRENT TSA STATUS SHOULD LOCK ENTRY.
        |
        */

        if (
            $teacherSubjectAllocation &&
            $exam
        ) {

            $currentStatus =
                strtoupper(
                    trim(
                        (string) (
                            $marksStatus
                                ->status
                            ?? ''
                        )
                    )
                );

            if (
                $currentStatus ===
                'COMPLETED'
            ) {

                $marksLocked =
                    true;

                $message =
                    'Marks entry has already been completed and is locked.';

                /*
                |--------------------------------------------------------------------------
                | IMPORTANT
                |--------------------------------------------------------------------------
                |
                | Keep students available.
                | The Blade can display existing marks in locked mode.
                |
                */
            }
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD EXISTING MARKS
        |--------------------------------------------------------------------------
        |
        | CURRENT TSA + OLD RELATED TSA.
        |
        */

        if (
            $teacherSubjectAllocation &&
            $exam &&
            $selectedClassAllocation &&
            !$error
        ) {

            $actualSubjectId =
                $teacherSubjectAllocation
                    ->resolved_subject_id
                ?? null;

            $existingMarks =
                $this->loadExistingMarks(
                    $teacherSubjectAllocation,
                    $selectedClassAllocation,
                    $actualSubjectId,
                    $exam->id
                );
        }


        /*
        |--------------------------------------------------------------------------
        | REQUEST YEAR
        |--------------------------------------------------------------------------
        */

        if (
            $academicYearId !== null &&
            $academicYearId !== ''
        ) {

            $request->merge([
                'academic_year_id' =>
                    $academicYearId,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'marks-entry.index',
            compact(
                'request',
                'academicYears',
                'academicYearId',
                'assignments',
                'exams',
                'students',
                'exam',
                'teacherSubjectAllocation',
                'selectedClassAllocation',
                'subjectConfig',
                'showTheory',
                'showOral',
                'showPractical',
                'marksLocked',
                'message',
                'error',
                'theoryMaxMarks',
                'theoryPassingMarks',
                'oralMaxMarks',
                'oralPassingMarks',
                'practicalMaxMarks',
                'practicalPassingMarks',
                'existingMarks'
            )
        );
    }
}