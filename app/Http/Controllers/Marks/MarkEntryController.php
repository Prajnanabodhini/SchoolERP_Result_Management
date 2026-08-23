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
            trim(
                (string)($user->role ?? '')
            )
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
    | RESOLVE SUBJECT MAP
    |--------------------------------------------------------------------------
    |
    | Builds subject mapping ONCE.
    |
    | Supports:
    |
    | CURRENT:
    | TSA.subject_id = subjects.id
    |
    | LEGACY:
    | TSA.subject_id = standard_wise_subjects.id
    |
    */

    private function buildSubjectResolutionMap(
        $assignments
    ) {
        $map = collect();

        if ($assignments->isEmpty()) {
            return $map;
        }

        /*
        |--------------------------------------------------------------------------
        | STANDARD IDS
        |--------------------------------------------------------------------------
        */

        $standardIds = $assignments
            ->pluck('allocation.standard_id')
            ->filter()
            ->unique()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | STORED SUBJECT IDS
        |--------------------------------------------------------------------------
        */

        $storedSubjectIds = $assignments
            ->pluck('subject_id')
            ->filter()
            ->map(fn($id) => (int)$id)
            ->unique()
            ->values();

        if (
            $standardIds->isEmpty() ||
            $storedSubjectIds->isEmpty()
        ) {
            return $map;
        }

        /*
        |--------------------------------------------------------------------------
        | LOAD STANDARD WISE SUBJECT MAPPINGS
        |--------------------------------------------------------------------------
        */

        $mappings =
            DB::table(
                'standard_wise_subjects as sws'
            )
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

        /*
        |--------------------------------------------------------------------------
        | Build lookup
        |--------------------------------------------------------------------------
        */

        foreach ($mappings as $mapping) {

            /*
            |--------------------------------------------------------------------------
            | Legacy key
            |
            | standard + sws.id
            |--------------------------------------------------------------------------
            */

            $legacyKey =
                $mapping->standard_id
                . ':'
                . $mapping->sws_id;

            $map->put(
                $legacyKey,
                $mapping
            );


            /*
            |--------------------------------------------------------------------------
            | Current key
            |
            | standard + subjects.id
            |--------------------------------------------------------------------------
            */

            $currentKey =
                $mapping->standard_id
                . ':'
                . $mapping->subject_id;

            /*
            | Current format must win.
            */

            if (!$map->has($currentKey)) {

                $map->put(
                    $currentKey,
                    $mapping
                );
            }
        }

        return $map;
    }


    /*
    |--------------------------------------------------------------------------
    | ACTUAL SUBJECT ID
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

        $storedSubjectId = (int)$storedSubjectId;
        $standardId = (int)$standardId;

        if (
            $storedSubjectId <= 0 ||
            $standardId <= 0
        ) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | FAST MAP
        |--------------------------------------------------------------------------
        */

        if ($subjectMap instanceof \Illuminate\Support\Collection) {

            $key =
                $standardId
                . ':'
                . $storedSubjectId;

            $mapping =
                $subjectMap->get($key);

            if ($mapping) {

                return (int)$mapping->actual_subject_id;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | FALLBACK CURRENT FORMAT
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
        | FALLBACK LEGACY FORMAT
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
            return (int)$mapping->subject_id;
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
        $standardId = (int)$standardId;

        if ($standardId <= 0) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | PRIMARY - TSA SUBJECT
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
        | LEGACY FALLBACK - TMS SUBJECT
        |--------------------------------------------------------------------------
        */

        if (
            $tmsSubjectId !== null &&
            $tmsSubjectId !== '' &&
            (int)$tmsSubjectId > 0
        ) {

            $tmsSubjectId =
                (int)$tmsSubjectId;


            /*
            |--------------------------------------------------------------------------
            | TMS ID = SUBJECT ID
            |--------------------------------------------------------------------------
            */

            if (
                $subjectCollection instanceof
                \Illuminate\Support\Collection
            ) {

                $subject =
                    $subjectCollection->get(
                        $tmsSubjectId
                    );

                if ($subject) {

                    $key =
                        $standardId
                        . ':'
                        . $tmsSubjectId;

                    if (
                        $subjectMap instanceof
                        \Illuminate\Support\Collection &&
                        $subjectMap->has($key)
                    ) {
                        return $subject;
                    }
                }
            }


            /*
            |--------------------------------------------------------------------------
            | TMS ID = SWS ID
            |--------------------------------------------------------------------------
            */

            if (
                $subjectMap instanceof
                \Illuminate\Support\Collection
            ) {

                $key =
                    $standardId
                    . ':'
                    . $tmsSubjectId;

                $mapping =
                    $subjectMap->get($key);

                if ($mapping) {

                    $actualSubjectId =
                        (int)$mapping->actual_subject_id;

                    if (
                        $subjectCollection instanceof
                        \Illuminate\Support\Collection
                    ) {

                        return $subjectCollection->get(
                            $actualSubjectId
                        );
                    }
                }
            }
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | INITIAL VALUES
        |--------------------------------------------------------------------------
        */

        $students = collect();
        $assignments = collect();
        $academicYears = collect();
        $exams = collect();

        $exam = null;

        $teacherSubjectAllocation = null;
        $selectedClassAllocation = null;
        $subjectConfig = null;

        $error = '';
        $message = '';

        $showTheory = false;
        $showOral = false;
        $showPractical = false;

        $theoryMaxMarks = 0;
        $theoryPassingMarks = 0;

        $oralMaxMarks = 0;
        $oralPassingMarks = 0;

        $practicalMaxMarks = 0;
        $practicalPassingMarks = 0;

        $marksLocked = false;

        $existingMarks = collect();

        $marksStatus = null;


        /*
        |--------------------------------------------------------------------------
        | AUTH
        |--------------------------------------------------------------------------
        */

        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        $userId =
            (int)Auth::id();

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
                ->orderByDesc('id')
                ->get();


        /*
        |--------------------------------------------------------------------------
        | EXAMS
        |--------------------------------------------------------------------------
        */

        $exams =
            ExamMaster::query()
                ->where(
                    'is_active',
                    1
                )
                ->orderBy('display_order')
                ->orderBy('exam_name')
                ->get();


        /*
        |--------------------------------------------------------------------------
        | EXAM
        |--------------------------------------------------------------------------
        */

        if ($examId) {

            $exam =
                $exams->firstWhere(
                    'id',
                    (int)$examId
                );

            if (!$exam) {

                $error =
                    'Selected exam was not found.';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | SELECTED TSA FIRST
        |--------------------------------------------------------------------------
        |
        | This is important for:
        |
        | Exam Progress -> Marks Entry
        |
        | because TSA tells us the actual academic year.
        |
        */

        if ($tsaId) {

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
                        (int)$tsaId
                    );


            if ($examId) {

                $tsaQuery->where(
                    'exam_master_id',
                    (int)$examId
                );
            }


            if (!$isAdministrator) {

                $tsaQuery->whereHas(
                    'allocation',
                    function ($query) use (
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


            if (!$teacherSubjectAllocation) {

                $error =
                    'Selected teaching assignment was not found or is not assigned to you.';

            } else {

                $selectedClassAllocation =
                    $teacherSubjectAllocation
                        ->allocation;


                if (!$selectedClassAllocation) {

                    $teacherSubjectAllocation =
                        null;

                    $error =
                        'Teacher class allocation not found.';

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | RESOLVE YEAR IMMEDIATELY
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
                            (int)$resolvedYear;


                        $request->merge([
                            'academic_year_id' =>
                                $academicYearId,
                        ]);
                    }
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | EXAM FROM TSA
        |--------------------------------------------------------------------------
        */

        if (
            $teacherSubjectAllocation &&
            !$exam
        ) {

            $exam =
                $exams->firstWhere(
                    'id',
                    $teacherSubjectAllocation
                        ->exam_master_id
                );
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD ASSIGNMENTS
        |--------------------------------------------------------------------------
        |
        | Now filtered by the correct academic year.
        |
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


        if ($examId) {

            $assignmentQuery->where(
                'exam_master_id',
                $examId
            );
        }


        if (
            $academicYearId !== null &&
            $academicYearId !== ''
        ) {

            $assignmentQuery->whereHas(
                'allocation',
                function ($query) use (
                    $academicYearId
                ) {

                    $query->where(
                        'academic_year_id',
                        $academicYearId
                    );
                }
            );
        }


        if (!$isAdministrator) {

            $assignmentQuery->whereHas(
                'allocation',
                function ($query) use (
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
                ->orderByDesc('id')
                ->get();


        /*
        |--------------------------------------------------------------------------
        | LOAD ALL STATUS RECORDS ONCE
        |--------------------------------------------------------------------------
        */

        $assignmentIds =
            $assignments
                ->pluck('id')
                ->filter()
                ->unique()
                ->values();


        $allStatuses =
            collect();


        if ($assignmentIds->isNotEmpty()) {

            $statusQuery =
                TeacherMarksStatus::query()
                    ->whereIn(
                        'teacher_subject_allocation_id',
                        $assignmentIds
                    );


            if (!$isAdministrator) {

                $statusQuery->where(
                    'teacher_id',
                    $userId
                );
            }


            $allStatuses =
                $statusQuery
                    ->get([
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
        | LOAD SUBJECTS ONCE
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


        if ($standardIds->isNotEmpty()) {

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
                ->keyBy('id');
        }


        /*
        |--------------------------------------------------------------------------
        | BUILD SUBJECT MAP ONCE
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
        |
        | Eliminates repeated collection where() calls.
        |
        */

        $statusMap =
            $allStatuses->keyBy(
                function ($status) {

                    return
                        $status->teacher_subject_allocation_id
                        . ':'
                        . $status->exam_master_id;
                }
            );


        /*
        |--------------------------------------------------------------------------
        | RESOLVE ASSIGNMENTS
        |--------------------------------------------------------------------------
        */

        foreach ($assignments as $assignment) {

            $allocation =
                $assignment->allocation;

            if (!$allocation) {
                continue;
            }


            $statusKey =
                $assignment->id
                . ':'
                . $assignment->exam_master_id;


            $status =
                $statusMap->get(
                    $statusKey
                );


            if (!$status) {

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


            if ($actualSubject) {

                $assignment->setRelation(
                    'subject',
                    $actualSubject
                );

                $assignment->resolved_subject_id =
                    (int)$actualSubject->id;
            }


            $assignment->resolved_academic_year_id =
                $allocation->academic_year_id;

            $assignment->resolved_class_allocation_id =
                $assignment->teacher_class_allocation_id;

            $assignment->resolved_exam_master_id =
                $assignment->exam_master_id;

            $assignment->resolved_standard_id =
                $allocation->standard_id;

            $assignment->resolved_division_id =
                $allocation->division_id;

            $assignment->resolved_status =
                $status
                    ? strtoupper(
                        trim(
                            (string)(
                                $status->status ?? ''
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

        if ($teacherSubjectAllocation) {

            $selectedAllocation =
                $teacherSubjectAllocation
                    ->allocation;


            $statusKey =
                $teacherSubjectAllocation->id
                . ':'
                . $teacherSubjectAllocation
                    ->exam_master_id;


            $marksStatus =
                $statusMap->get(
                    $statusKey
                );


            /*
            |--------------------------------------------------------------------------
            | FALLBACK
            |--------------------------------------------------------------------------
            */

            if (!$marksStatus) {

                $marksStatus =
                    TeacherMarksStatus::query()
                        ->where(
                            'teacher_subject_allocation_id',
                            $teacherSubjectAllocation->id
                        )
                        ->where(
                            'exam_master_id',
                            $teacherSubjectAllocation
                                ->exam_master_id
                        )
                        ->when(
                            !$isAdministrator,
                            function ($query) use (
                                $userId
                            ) {

                                $query->where(
                                    'teacher_id',
                                    $userId
                                );
                            }
                        )
                        ->orderByDesc('id')
                        ->first();
            }


            /*
            |--------------------------------------------------------------------------
            | SUBJECT
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


            if ($actualSubject) {

                $teacherSubjectAllocation
                    ->setRelation(
                        'subject',
                        $actualSubject
                    );

                $teacherSubjectAllocation
                    ->resolved_subject_id =
                        (int)$actualSubject->id;
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


            $actualSubjectId =
                $teacherSubjectAllocation
                    ->resolved_subject_id
                ?? null;


            /*
            |--------------------------------------------------------------------------
            | CURRENT CONFIGURATION
            |--------------------------------------------------------------------------
            */

            if ($actualSubjectId) {

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
                    ->first();


                if ($mapping) {

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
            | CONFIGURATION ERROR
            |--------------------------------------------------------------------------
            */

            if (!$subjectConfig) {

                $subjectName =
                    optional(
                        $teacherSubjectAllocation->subject
                    )->subject_name
                    ?? 'Selected Subject';


                $standardName =
                    optional(
                        $allocation->standard
                    )->standard_name
                    ?? 'Selected Standard';


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
                (bool)$exam->has_theory;

            $showOral =
                (bool)$exam->has_oral;

            $showPractical =
                (bool)$exam->has_practical;


            if ($subjectConfig) {

                $theoryMaxMarks =
                    $subjectConfig->max_marks
                    ?? 0;

                $theoryPassingMarks =
                    $subjectConfig->passing_marks
                    ?? 0;
            }


            if ($showOral) {

                $oralMaxMarks =
                    $exam->oral_max_marks
                    ?? 0;

                $oralPassingMarks =
                    $exam->oral_passing_marks
                    ?? 0;
            }


            if ($showPractical) {

                $practicalMaxMarks =
                    $exam->practical_max_marks
                    ?? 0;

                $practicalPassingMarks =
                    $exam->practical_passing_marks
                    ?? 0;
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
            $exam
        ) {

            $allocation =
                $selectedClassAllocation;


            $erpAcademicYearId =
                (int)(
                    $allocation->academic_year_id
                    ?? 0
                );


            $erpStandardId =
                (int)(
                    $allocation->standard_id
                    ?? 0
                );


            $erpDivisionId =
                (int)(
                    $allocation->division_id
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
                            collect($students);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | GIRLS FIRST
                    |--------------------------------------------------------------------------
                    */

                    $students =
                        $students
                            ->sort(
                                function (
                                    $a,
                                    $b
                                ) {

                                    $genderA =
                                        strtoupper(
                                            trim(
                                                $a->gender
                                                ?? ''
                                            )
                                        );


                                    $genderB =
                                        strtoupper(
                                            trim(
                                                $b->gender
                                                ?? ''
                                            )
                                        );


                                    if (
                                        $genderA !==
                                        $genderB
                                    ) {

                                        if (
                                            in_array(
                                                $genderA,
                                                [
                                                    'F',
                                                    'FEMALE',
                                                ],
                                                true
                                            )
                                        ) {
                                            return -1;
                                        }


                                        if (
                                            in_array(
                                                $genderB,
                                                [
                                                    'F',
                                                    'FEMALE',
                                                ],
                                                true
                                            )
                                        ) {
                                            return 1;
                                        }
                                    }


                                    return strcmp(
                                        strtoupper(
                                            trim(
                                                $a->studname
                                                ?? ''
                                            )
                                        ),
                                        strtoupper(
                                            trim(
                                                $b->studname
                                                ?? ''
                                            )
                                        )
                                    );
                                }
                            )
                            ->values();
                }

            } catch (\Throwable $e) {

                report($e);

                $students =
                    collect();

                $error =
                    'Old ERP Error: '
                    . $e->getMessage();
            }


            /*
            |--------------------------------------------------------------------------
            | NO STUDENTS
            |--------------------------------------------------------------------------
            */

            if ($students->isEmpty()) {

                $standardName =
                    optional(
                        $allocation->standard
                    )->standard_name
                    ?? 'Selected Standard';


                $divisionName =
                    optional(
                        $allocation->division
                    )->division_name
                    ?? 'Selected Division';


                $error =
                    'No students found for '
                    . $standardName
                    . ' - '
                    . $divisionName
                    . '. Please verify Old ERP student mapping.';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | COMPLETED / PENDING
        |--------------------------------------------------------------------------
        */

        if (
            $teacherSubjectAllocation &&
            $exam
        ) {

            $currentStatus =
                strtoupper(
                    trim(
                        (string)(
                            $marksStatus->status
                            ?? ''
                        )
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | ONLY COMPLETED LOCKS
            |--------------------------------------------------------------------------
            */

            if (
                $currentStatus === 'COMPLETED'
            ) {

                $marksLocked = true;

                $message =
                    'Marks entry has already been completed and is locked.';

                $students =
                    collect();
            }

            /*
            |--------------------------------------------------------------------------
            | PENDING
            |--------------------------------------------------------------------------
            |
            | Students remain visible.
            |
            */
        }


        /*
        |--------------------------------------------------------------------------
        | EXISTING MARKS
        |--------------------------------------------------------------------------
        */

        if (
            $teacherSubjectAllocation &&
            $exam
        ) {

            $existingMarks =
                StudentMark::query()
                    ->where(
                        'exam_master_id',
                        $exam->id
                    )
                    ->where(
                        'teacher_subject_allocation_id',
                        $teacherSubjectAllocation->id
                    )
                    ->get()
                    ->keyBy('student_id');
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
        | RETURN
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