<?php

namespace App\Helpers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Subject;
use App\Models\StudentMark;
use App\Models\TeacherMarksStatus;
use App\Models\TeacherSubjectAllocation;

class MarksHelper
{
    /*
    |--------------------------------------------------------------------------
    | PASSING RULES
    |--------------------------------------------------------------------------
    */

    private const PASSING_35_STANDARD_IDS = [
        9,
        10,
        13,
        14,
        15,
        19,
        20,
        21,
        22,
        23,
        24,
    ];

    private const PASSING_40_STANDARD_IDS = [
        1,
        2,
        3,
        4,
        5,
        6,
        7,
        8,
    ];


    /*
    |--------------------------------------------------------------------------
    | OPTIONAL STANDARD IDS
    |--------------------------------------------------------------------------
    */

    private const OPTIONAL_STANDARD_IDS = [
        19,
        20,
        21,
        22,
        23,
        24,
    ];


    /*
    |--------------------------------------------------------------------------
    | STANDARD LISTS
    |--------------------------------------------------------------------------
    */

    public static function get35PercentStandardIds(): array
    {
        return self::PASSING_35_STANDARD_IDS;
    }

    public static function get40PercentStandardIds(): array
    {
        return self::PASSING_40_STANDARD_IDS;
    }

    public static function getOptionalStandardIds(): array
    {
        return self::OPTIONAL_STANDARD_IDS;
    }


    /*
    |--------------------------------------------------------------------------
    | ADMINISTRATOR
    |--------------------------------------------------------------------------
    */

    public static function isAdministrator(): bool
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
                (string) ($user->role ?? '')
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
    | OPTIONAL
    |--------------------------------------------------------------------------
    */

    public static function isOptionalEnabled($standardId): bool
    {
        if (
            $standardId === null ||
            $standardId === ''
        ) {
            return false;
        }

        return in_array(
            (int) $standardId,
            self::OPTIONAL_STANDARD_IDS,
            true
        );
    }

    public static function isOptionalEnabledForAllocation($allocation): bool
    {
        if (!$allocation) {
            return false;
        }

        return self::isOptionalEnabled(
            $allocation->standard_id ?? null
        );
    }

    public static function isOptionalStudent($mark): bool
    {
        if (!$mark) {
            return false;
        }

        return (int) (
            $mark->is_optional ?? 0
        ) === 1;
    }


    /*
    |--------------------------------------------------------------------------
    | ABSENT
    |--------------------------------------------------------------------------
    */

    public static function isAbsent($mark): bool
    {
        if (!$mark) {
            return false;
        }

        return (int) (
            $mark->is_absent ?? 0
        ) === 1;
    }


    /*
    |--------------------------------------------------------------------------
    | PASSING PERCENTAGE
    |--------------------------------------------------------------------------
    */

    public static function getPassingPercentage($standardId): int
    {
        if (
            $standardId === null ||
            $standardId === ''
        ) {
            return 40;
        }

        $standardId =
            (int) $standardId;

        if (
            in_array(
                $standardId,
                self::PASSING_35_STANDARD_IDS,
                true
            )
        ) {
            return 35;
        }

        if (
            in_array(
                $standardId,
                self::PASSING_40_STANDARD_IDS,
                true
            )
        ) {
            return 40;
        }

        return 40;
    }


    /*
    |--------------------------------------------------------------------------
    | PASSING MARKS
    |--------------------------------------------------------------------------
    */

    public static function getPassingMarks(
        $standardId,
        $maxMarks
    ): int {

        $maxMarks =
            (float) $maxMarks;

        if (
            $maxMarks <= 0
        ) {
            return 0;
        }

        $percentage =
            self::getPassingPercentage(
                $standardId
            );

        return (int) ceil(
            (
                $maxMarks *
                $percentage
            ) / 100
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PASS / FAIL
    |--------------------------------------------------------------------------
    */

    public static function isPassing(
        $standardId,
        $marks,
        $maxMarks
    ): bool {

        if (
            $marks === null ||
            $marks === ''
        ) {
            return false;
        }

        $passingMarks =
            self::getPassingMarks(
                $standardId,
                $maxMarks
            );

        return
            (float) $marks >=
            (float) $passingMarks;
    }


    /*
    |--------------------------------------------------------------------------
    | MARK FORMATTING
    |--------------------------------------------------------------------------
    */

    public static function formatMark($value): string
    {
        if (
            $value === null ||
            $value === ''
        ) {
            return '';
        }

        $number =
            (float) $value;

        if (
            floor($number) == $number
        ) {
            return (string) ((int) $number);
        }

        return rtrim(
            rtrim(
                number_format(
                    $number,
                    2,
                    '.',
                    ''
                ),
                '0'
            ),
            '.'
        );
    }

    public static function formatMarkOrNull($value): ?string
    {
        $formatted =
            self::formatMark(
                $value
            );

        return $formatted === ''
            ? null
            : $formatted;
    }


    /*
    |--------------------------------------------------------------------------
    | UNIT TEST 1
    |--------------------------------------------------------------------------
    */

    public static function isUnitTest1($examName): bool
    {
        $examName =
            strtoupper(
                trim(
                    (string) $examName
                )
            );

        return str_contains(
            $examName,
            'UNIT TEST 1'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EXAM COMPONENTS
    |--------------------------------------------------------------------------
    */

    public static function getExamComponents($exam): array
    {
        $showTheory =
            (bool) (
                $exam->has_theory ?? true
            );

        $showOral =
            (bool) (
                $exam->has_oral ?? false
            );

        $showPractical =
            (bool) (
                $exam->has_practical ?? false
            );

        if (
            self::isUnitTest1(
                $exam->exam_name ?? ''
            )
        ) {
            $showOral = false;
            $showPractical = false;
        }

        return [
            'show_theory' =>
                $showTheory,

            'show_oral' =>
                $showOral,

            'show_practical' =>
                $showPractical,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | COMPONENT MAX MARKS
    |--------------------------------------------------------------------------
    */

    public static function getComponentMaxMarks(
        $exam,
        $subjectConfig
    ): array {

        $components =
            self::getExamComponents(
                $exam
            );

        $theoryMax =
            (float) (
                $subjectConfig->max_marks ?? 0
            );

        $oralMax =
            $components['show_oral']
                ? (float) (
                    $exam->oral_max_marks ?? 0
                )
                : 0;

        $practicalMax =
            $components['show_practical']
                ? (float) (
                    $exam->practical_max_marks ?? 0
                )
                : 0;

        return [
            'show_theory' =>
                $components['show_theory'],

            'show_oral' =>
                $components['show_oral'],

            'show_practical' =>
                $components['show_practical'],

            'theory_max' =>
                $theoryMax,

            'oral_max' =>
                $oralMax,

            'practical_max' =>
                $practicalMax,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | MARK VALIDATION
    |--------------------------------------------------------------------------
    */

    public static function hasMark($value): bool
    {
        return !(
            $value === null ||
            $value === ''
        );
    }

    public static function validateObtainedMarks(
        $value,
        $maxMarks,
        string $component,
        $studentId
    ): ?string {

        if (
            !self::hasMark(
                $value
            )
        ) {
            return
                $component .
                ' marks are missing for one or more students.';
        }

        $obtained =
            (float) $value;

        $maxMarks =
            (float) $maxMarks;

        if (
            $obtained < 0 ||
            (
                $maxMarks > 0 &&
                $obtained > $maxMarks
            )
        ) {
            return
                'Invalid ' .
                $component .
                ' marks found for Student ID ' .
                $studentId .
                '.';
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | STUDENT ID
    |--------------------------------------------------------------------------
    */

    public static function getStudentId($student): ?string
    {
        if (
            isset($student->Studentid) &&
            $student->Studentid !== ''
        ) {
            return (string) $student->Studentid;
        }

        if (
            isset($student->student_id) &&
            $student->student_id !== ''
        ) {
            return (string) $student->student_id;
        }

        if (
            isset($student->id) &&
            $student->id !== ''
        ) {
            return (string) $student->id;
        }

        return null;
    }

    public static function getStudentIds($students): Collection
    {
        return collect($students)
            ->map(
                function ($student) {
                    return self::getStudentId(
                        $student
                    );
                }
            )
            ->filter()
            ->unique()
            ->values();
    }

    public static function getSavedStudentIds($marks): Collection
    {
        return collect($marks)
            ->pluck('student_id')
            ->map(
                fn ($id) =>
                    (string) $id
            )
            ->filter()
            ->unique()
            ->values();
    }


    /*
    |--------------------------------------------------------------------------
    | ROLL NUMBER
    |--------------------------------------------------------------------------
    */

    public static function getRollNumber($student): ?int
    {
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
            $roll === null ||
            $roll === ''
        ) {
            return null;
        }

        return (int) $roll;
    }

    public static function sortStudentsByRoll($students): Collection
    {
        return collect($students)
            ->sortBy(
                function ($student) {

                    $roll =
                        self::getRollNumber(
                            $student
                        );

                    return
                        $roll === null
                            ? PHP_INT_MAX
                            : $roll;
                }
            )
            ->values();
    }


    /*
    |--------------------------------------------------------------------------
    | EXAM ACADEMIC YEAR
    |--------------------------------------------------------------------------
    */

    public static function validateExamAcademicYear(
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

        if (!$examAcademicYearId) {
            return
                'Selected Exam does not have an Academic Year assigned.';
        }

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
    | SUBJECT RESOLUTION MAP
    |--------------------------------------------------------------------------
    */

    public static function buildSubjectResolutionMap(
        $assignments
    ): Collection {

        $map =
            collect();

        if (
            !$assignments ||
            $assignments->isEmpty()
        ) {
            return $map;
        }

        $standardIds =
            $assignments
                ->pluck('allocation.standard_id')
                ->filter()
                ->unique()
                ->values();

        if (
            $standardIds->isEmpty()
        ) {
            return $map;
        }

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

        foreach (
            $mappings as $mapping
        ) {

            $currentKey =
                (int) $mapping->standard_id .
                ':subject:' .
                (int) $mapping->subject_id;

            $map->put(
                $currentKey,
                $mapping
            );

            $legacyKey =
                (int) $mapping->standard_id .
                ':sws:' .
                (int) $mapping->sws_id;

            $map->put(
                $legacyKey,
                $mapping
            );
        }

        return $map;
    }


    /*
    |--------------------------------------------------------------------------
    | ACTUAL SUBJECT ID
    |--------------------------------------------------------------------------
    */

    public static function resolveActualSubjectId(
        $storedSubjectId,
        $standardId = null,
        $subjectMap = null
    ): ?int {

        if (
            $storedSubjectId === null ||
            $storedSubjectId === ''
        ) {
            return null;
        }

        $storedSubjectId =
            (int) $storedSubjectId;

        if (
            $storedSubjectId <= 0
        ) {
            return null;
        }


        /*
        |----------------------------------------------------------------------
        | WITH STANDARD + MAP
        |----------------------------------------------------------------------
        */

        if (
            $standardId &&
            $subjectMap instanceof Collection
        ) {

            $standardId =
                (int) $standardId;

            $currentKey =
                $standardId .
                ':subject:' .
                $storedSubjectId;

            $mapping =
                $subjectMap->get(
                    $currentKey
                );

            if ($mapping) {
                return (int)
                    $mapping->actual_subject_id;
            }

            $legacyKey =
                $standardId .
                ':sws:' .
                $storedSubjectId;

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
        |----------------------------------------------------------------------
        | DIRECT SUBJECT
        |----------------------------------------------------------------------
        */

        $subject =
            DB::table(
                'subjects'
            )
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

            if (!$standardId) {
                return $storedSubjectId;
            }

            $exists =
                DB::table(
                    'standard_wise_subjects'
                )
                ->where(
                    'standard_id',
                    (int) $standardId
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
        |----------------------------------------------------------------------
        | LEGACY SWS
        |----------------------------------------------------------------------
        */

        if ($standardId) {

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
                    (int) $standardId
                )
                ->where(
                    'is_active',
                    1
                )
                ->first();

            if (
                $mapping &&
                !empty(
                    $mapping->subject_id
                )
            ) {
                return (int)
                    $mapping->subject_id;
            }
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | TEACHER AUTHORIZATION
    |--------------------------------------------------------------------------
    */

    public static function verifyTeacherAssignment(
        TeacherSubjectAllocation $teacherSubjectAllocation,
        $examMasterId
    ): bool {

        if (!Auth::check()) {
            return false;
        }

        if (
            self::isAdministrator()
        ) {
            return true;
        }

        return TeacherMarksStatus::where(
            'teacher_subject_allocation_id',
            $teacherSubjectAllocation->id
        )
        ->where(
            'exam_master_id',
            $examMasterId
        )
        ->where(
            'teacher_id',
            Auth::id()
        )
        ->exists();
    }


    /*
    |--------------------------------------------------------------------------
    | BOOLEAN REQUEST VALUE
    |--------------------------------------------------------------------------
    */

    public static function toBoolean($value): bool
    {
        if (
            is_bool($value)
        ) {
            return $value;
        }

        if (
            $value === null
        ) {
            return false;
        }

        $normalized =
            strtolower(
                trim(
                    (string) $value
                )
            );

        if (
            in_array(
                $normalized,
                [
                    '1',
                    'true',
                    'yes',
                    'on',
                ],
                true
            )
        ) {
            return true;
        }

        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | ALLOWED MARK FIELDS
    |--------------------------------------------------------------------------
    */

    public static function getAllowedMarkFields(): array
    {
        return [
            'theory_obtained_marks',
            'oral_obtained_marks',
            'practical_obtained_marks',
            'is_optional',
        ];
    }

    public static function isAllowedMarkField(
        $field
    ): bool {

        return in_array(
            $field,
            self::getAllowedMarkFields(),
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FIELD MAXIMUM
    |--------------------------------------------------------------------------
    */

    public static function getFieldMaxMarks(
        string $field,
        $exam,
        $subjectConfig
    ): float {

        switch ($field) {

            case 'theory_obtained_marks':

                return (float) (
                    $subjectConfig->max_marks ?? 0
                );


            case 'oral_obtained_marks':

                return (float) (
                    $exam->oral_max_marks ?? 0
                );


            case 'practical_obtained_marks':

                return (float) (
                    $exam->practical_max_marks ?? 0
                );
        }

        return 0;
    }


    /*
    |--------------------------------------------------------------------------
    | FIND STUDENT MARK
    |--------------------------------------------------------------------------
    */

    public static function findStudentMark(
        $allocation,
        $studentId,
        $examId,
        $tsaId,
        $subjectId
    ) {

        return StudentMark::where(
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
            'student_id',
            $studentId
        )
        ->where(
            'exam_master_id',
            $examId
        )
        ->where(
            'teacher_subject_allocation_id',
            $tsaId
        )
        ->where(
            'subject_id',
            $subjectId
        )
        ->first();
    }


    /*
    |--------------------------------------------------------------------------
    | MARK STATUS
    |--------------------------------------------------------------------------
    */

    public static function getTeacherMarksStatus(
        $examId,
        $tsaId,
        $isAdministrator = false,
        $userId = null
    ) {

        $query =
            TeacherMarksStatus::where(
                'exam_master_id',
                $examId
            )
            ->where(
                'teacher_subject_allocation_id',
                $tsaId
            );

        if (
            !$isAdministrator
        ) {

            $query->where(
                'teacher_id',
                $userId ?? Auth::id()
            );
        }

        return $query->first();
    }


    /*
    |--------------------------------------------------------------------------
    | SUBJECT CONFIGURATION
    |--------------------------------------------------------------------------
    */

    public static function getSubjectConfig(
        $examId,
        $standardId,
        $subjectId
    ) {

        return DB::table(
            'exam_master_subjects'
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
            'subject_id',
            $subjectId
        )
        ->first();
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE SUBJECT CONFIGURATION
    |--------------------------------------------------------------------------
    */

    public static function resolveExamSubjectConfig(
        $exam,
        $standardId,
        $actualSubjectId
    ) {

        if (
            !$exam ||
            !$standardId ||
            !$actualSubjectId
        ) {
            return null;
        }


        /*
        |----------------------------------------------------------------------
        | CURRENT SUBJECT ID
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
                $standardId
            )
            ->where(
                'subject_id',
                $actualSubjectId
            )
            ->first();

        if ($subjectConfig) {
            return $subjectConfig;
        }


        /*
        |----------------------------------------------------------------------
        | LEGACY SWS FALLBACK
        |----------------------------------------------------------------------
        */

        $mapping =
            DB::table(
                'standard_wise_subjects'
            )
            ->where(
                'standard_id',
                $standardId
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

        if (!$mapping) {
            return null;
        }

        return DB::table(
            'exam_master_subjects'
        )
        ->where(
            'exam_master_id',
            $exam->id
        )
        ->where(
            'standard_id',
            $standardId
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


    /*
    |--------------------------------------------------------------------------
    | RESOLVE DISPLAY SUBJECT
    |--------------------------------------------------------------------------
    */

    public static function resolveDisplaySubject(
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
        |----------------------------------------------------------------------
        | PRIMARY SUBJECT
        |----------------------------------------------------------------------
        */

        $actualSubjectId =
            self::resolveActualSubjectId(
                $storedSubjectId,
                $standardId,
                $subjectMap
            );

        if ($actualSubjectId) {

            if (
                $subjectCollection instanceof Collection
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
                DB::table(
                    'subjects'
                )
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
        |----------------------------------------------------------------------
        | TMS SUBJECT
        |----------------------------------------------------------------------
        */

        if (
            $tmsSubjectId !== null &&
            $tmsSubjectId !== '' &&
            (int) $tmsSubjectId > 0
        ) {

            $tmsSubjectId =
                (int) $tmsSubjectId;


            /*
            | Current
            */

            if (
                $subjectMap instanceof Collection
            ) {

                $currentKey =
                    $standardId .
                    ':subject:' .
                    $tmsSubjectId;

                $mapping =
                    $subjectMap->get(
                        $currentKey
                    );

                if ($mapping) {

                    $actualSubjectId =
                        (int)
                        $mapping->actual_subject_id;

                    if (
                        $subjectCollection instanceof Collection
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
            | Legacy
            */

            if (
                $subjectMap instanceof Collection
            ) {

                $legacyKey =
                    $standardId .
                    ':sws:' .
                    $tmsSubjectId;

                $mapping =
                    $subjectMap->get(
                        $legacyKey
                    );

                if ($mapping) {

                    $actualSubjectId =
                        (int)
                        $mapping->actual_subject_id;

                    if (
                        $subjectCollection instanceof Collection
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
                        DB::table(
                            'subjects'
                        )
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
    | TSA REPRESENTS SUBJECT
    |--------------------------------------------------------------------------
    */

    public static function tsaRepresentsSubject(
        $tsa,
        $actualSubjectId,
        $standardId
    ): bool {

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

        if (
            $storedSubjectId ===
            $actualSubjectId
        ) {
            return true;
        }

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

        return
            $mapping &&
            (int) $mapping->subject_id ===
            $actualSubjectId;
    }


    /*
    |--------------------------------------------------------------------------
    | RELATED TSA IDS
    |--------------------------------------------------------------------------
    */

    public static function getRelatedTeacherSubjectAllocationIds(
        $currentTsa,
        $allocation,
        $actualSubjectId,
        $examId
    ): Collection {

        $ids =
            collect();

        if (
            !$currentTsa ||
            !$allocation
        ) {
            return $ids;
        }

        $ids->push(
            (int) $currentTsa->id
        );

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

        $possibleSubjectIds =
            collect();

        if ($actualSubjectId) {

            $actualSubjectId =
                (int) $actualSubjectId;

            $possibleSubjectIds->push(
                $actualSubjectId
            );

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
                $legacyMappings as $mapping
            ) {
                $possibleSubjectIds->push(
                    (int) $mapping->id
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

        $related =
            $query
                ->pluck('id')
                ->map(
                    fn ($id) =>
                        (int) $id
                );

        return $ids
            ->merge($related)
            ->unique()
            ->values();
    }


    /*
    |--------------------------------------------------------------------------
    | EXISTING MARKS
    |--------------------------------------------------------------------------
    */

    public static function loadExistingMarks(
        $teacherSubjectAllocation,
        $allocation,
        $actualSubjectId,
        $examId
    ): Collection {

        $empty =
            collect();

        if (
            !$teacherSubjectAllocation ||
            !$allocation ||
            !$examId
        ) {
            return $empty;
        }

        $tsaIds =
            self::getRelatedTeacherSubjectAllocationIds(
                $teacherSubjectAllocation,
                $allocation,
                $actualSubjectId,
                $examId
            );

        if ($tsaIds->isEmpty()) {
            return $empty;
        }

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
                ->orderByDesc('id')
                ->get();

        $result =
            collect();

        foreach (
            $marks as $mark
        ) {

            $studentId =
                (string) $mark->student_id;

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
    | OPTIONAL MARK VALUES
    |--------------------------------------------------------------------------
    */

    public static function getOptionalMarkValues(
        $request,
        $studentId,
        $optionalEnabled
    ): array {

        $isOptional =
            $optionalEnabled &&
            self::toBoolean(
                $request->is_optional[$studentId]
                ?? false
            );

        $isAbsent =
            self::toBoolean(
                $request->is_absent[$studentId]
                ?? false
            );

        if ($isOptional) {
            $isAbsent = false;
        }

        return [
            'is_optional' =>
                $isOptional,

            'is_absent' =>
                $isAbsent,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | OBTAINED MARK
    |--------------------------------------------------------------------------
    */

    public static function resolveObtainedMark(
        $request,
        $field,
        $studentId,
        $isAbsent,
        $isOptional,
        $enabled
    ) {

        if (
            $isAbsent ||
            $isOptional
        ) {
            return 0;
        }

        if (!$enabled) {
            return null;
        }

        return
            $request->{$field}[$studentId]
            ?? null;
    }


    /*
    |--------------------------------------------------------------------------
    | COMPONENT VALIDATION RULES
    |--------------------------------------------------------------------------
    */

    public static function buildStudentMarkValidationRules(
        $request,
        array $studentIds,
        bool $optionalEnabled,
        bool $showTheory,
        bool $showOral,
        bool $showPractical,
        $theoryMaxMarks,
        $oralMaxMarks,
        $practicalMaxMarks
    ): array {

        $rules = [];

        foreach (
            $studentIds as $studentId
        ) {

            $values =
                self::getOptionalMarkValues(
                    $request,
                    $studentId,
                    $optionalEnabled
                );

            if (
                $values['is_optional'] ||
                $values['is_absent']
            ) {
                continue;
            }

            if ($showTheory) {

                $rules[
                    "theory_marks.$studentId"
                ] =
                    'required|numeric|min:0|max:' .
                    $theoryMaxMarks;
            }

            if ($showOral) {

                $rules[
                    "oral_marks.$studentId"
                ] =
                    'required|numeric|min:0|max:' .
                    $oralMaxMarks;
            }

            if ($showPractical) {

                $rules[
                    "practical_marks.$studentId"
                ] =
                    'required|numeric|min:0|max:' .
                    $practicalMaxMarks;
            }
        }

        return $rules;
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK REQUESTED MARK FIELDS
    |--------------------------------------------------------------------------
    */

    public static function validateStudentMarksPresence(
        $request,
        array $studentIds,
        bool $optionalEnabled,
        bool $showTheory,
        bool $showOral,
        bool $showPractical
    ): ?string {

        foreach (
            $studentIds as $studentId
        ) {

            $values =
                self::getOptionalMarkValues(
                    $request,
                    $studentId,
                    $optionalEnabled
                );

            if (
                $values['is_optional'] ||
                $values['is_absent']
            ) {
                continue;
            }

            if ($showTheory) {

                if (
                    !isset(
                        $request->theory_marks[$studentId]
                    )
                    ||
                    $request->theory_marks[$studentId] === ''
                ) {
                    return
                        'Please enter Theory marks for all students.';
                }
            }

            if ($showOral) {

                if (
                    !isset(
                        $request->oral_marks[$studentId]
                    )
                    ||
                    $request->oral_marks[$studentId] === ''
                ) {
                    return
                        'Please enter Oral marks for all students.';
                }
            }

            if ($showPractical) {

                if (
                    !isset(
                        $request->practical_marks[$studentId]
                    )
                    ||
                    $request->practical_marks[$studentId] === ''
                ) {
                    return
                        'Please enter Practical marks for all students.';
                }
            }
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | STATUS IS COMPLETED
    |--------------------------------------------------------------------------
    */

    public static function isCompletedStatus(
        $marksStatus
    ): bool {

        if (!$marksStatus) {
            return false;
        }

        return strtoupper(
            trim(
                (string) (
                    $marksStatus->status ?? ''
                )
            )
        ) === 'COMPLETED';
    }


    /*
    |--------------------------------------------------------------------------
    | FIELD MAXIMUM VALIDATION
    |--------------------------------------------------------------------------
    */

    public static function validateFieldMaximum(
        $value,
        $maxMarks
    ): bool {

        return
            (float) $value <=
            (float) $maxMarks;
    }
}