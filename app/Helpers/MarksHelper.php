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
        9, 10, 13, 14, 15, 19, 20, 21, 22, 23, 24,
    ];

    private const PASSING_40_STANDARD_IDS = [
        1, 2, 3, 4, 5, 6, 7, 8,
    ];

    /*
    |--------------------------------------------------------------------------
    | OPTIONAL STANDARD IDS
    |--------------------------------------------------------------------------
    */

    private const OPTIONAL_STANDARD_IDS = [
        19, 20, 21, 22, 23, 24,
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
            if ($user->hasRole('Administrator') || $user->hasRole('admin')) {
                return true;
            }
        }

        $role = strtolower(trim((string) ($user->role ?? '')));

        return in_array($role, ['administrator', 'admin'], true);
    }

    /*
    |--------------------------------------------------------------------------
    | OPTIONAL
    |--------------------------------------------------------------------------
    */

    public static function isOptionalEnabled($standardId): bool
    {
        if ($standardId === null || $standardId === '') {
            return false;
        }

        return in_array((int) $standardId, self::OPTIONAL_STANDARD_IDS, true);
    }

    public static function isOptionalEnabledForAllocation($allocation): bool
    {
        if (!$allocation) {
            return false;
        }

        return self::isOptionalEnabled($allocation->standard_id ?? null);
    }

    public static function isOptionalStudent($mark): bool
    {
        if (!$mark) {
            return false;
        }

        return (int) ($mark->is_optional ?? 0) === 1;
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

        return (int) ($mark->is_absent ?? 0) === 1;
    }

    /*
    |--------------------------------------------------------------------------
    | PASSING PERCENTAGE
    |--------------------------------------------------------------------------
    */

    public static function getPassingPercentage($standardId): int
    {
        if ($standardId === null || $standardId === '') {
            return 40;
        }

        $standardId = (int) $standardId;

        if (in_array($standardId, self::PASSING_35_STANDARD_IDS, true)) {
            return 35;
        }

        if (in_array($standardId, self::PASSING_40_STANDARD_IDS, true)) {
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
        $maxMarks,
        $explicitPassingMarks = null
    ): int {
        $maxMarks = (float) $maxMarks;

        if ($maxMarks <= 0) {
            return 0;
        }

        if (
            $explicitPassingMarks !== null &&
            $explicitPassingMarks !== '' &&
            is_numeric($explicitPassingMarks)
        ) {
            return (int) ceil(max(0, min((float) $explicitPassingMarks, $maxMarks)));
        }

        $percentage = self::getPassingPercentage($standardId);

        return (int) ceil(($maxMarks * $percentage) / 100);
    }

    /*
    |--------------------------------------------------------------------------
    | PASS / FAIL
    |--------------------------------------------------------------------------
    */

    public static function isPassing(
        $standardId,
        $marks,
        $maxMarks,
        $explicitPassingMarks = null
    ): bool {
        if ($marks === null || $marks === '') {
            return false;
        }

        $passingMarks = self::getPassingMarks($standardId, $maxMarks, $explicitPassingMarks);

        return (float) $marks >= (float) $passingMarks;
    }

    /*
    |--------------------------------------------------------------------------
    | MARK FORMATTING
    |--------------------------------------------------------------------------
    */

    public static function formatMark($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $number = (float) $value;

        if (floor($number) == $number) {
            return (string) ((int) $number);
        }

        return rtrim(rtrim(number_format($number, 2, '.', ''), '0'), '.');
    }

    public static function formatMarkOrNull($value): ?string
    {
        $formatted = self::formatMark($value);
        return $formatted === '' ? null : $formatted;
    }

    /*
    |--------------------------------------------------------------------------
    | UNIT TEST 1
    |--------------------------------------------------------------------------
    */

    public static function isUnitTest1($examName): bool
    {
        $examName = strtoupper(trim((string) $examName));
        return str_contains($examName, 'UNIT TEST 1');
    }

    /*
    |--------------------------------------------------------------------------
    | TERM 1 / TERM 2
    |--------------------------------------------------------------------------
    */

    public static function isTermExam($examName): bool
    {
        $examName = strtoupper(trim((string) $examName));

        $normalized = preg_replace('/[^A-Z0-9]+/', ' ', $examName);
        $normalized = trim((string) $normalized);

        return preg_match('/\bTERM\s+(?:1|2|I|II)\b/', $normalized) === 1
            || preg_match('/\b(?:FIRST|SECOND)\s+TERM\b/', $normalized) === 1;
    }

    /*
    |--------------------------------------------------------------------------
    | EXAM COMPONENTS
    |--------------------------------------------------------------------------
    |
    | Legacy snapshot-based flags. Prefer ExamStructureHelper when available.
    |--------------------------------------------------------------------------
    */

    public static function getExamComponents($exam, $subjectConfig = null): array
    {
        if ($subjectConfig) {

            $theoryMax = self::hasProperty($subjectConfig, 'theory_max_marks')
                ? self::numberProperty($subjectConfig, 'theory_max_marks', null)
                : null;

            $oralMax = self::hasProperty($subjectConfig, 'oral_max_marks')
                ? self::numberProperty($subjectConfig, 'oral_max_marks', null)
                : null;

            $practicalMax = self::hasProperty($subjectConfig, 'practical_max_marks')
                ? self::numberProperty($subjectConfig, 'practical_max_marks', null)
                : null;

            if (
                $theoryMax !== null ||
                $oralMax !== null ||
                $practicalMax !== null
            ) {
                return [
                    'show_theory'    => ($theoryMax    ?? 0) > 0,
                    'show_oral'      => ($oralMax      ?? 0) > 0,
                    'show_practical' => ($practicalMax ?? 0) > 0,
                ];
            }
        }

        $showTheory    = (bool) ($exam->has_theory    ?? false);
        $showOral      = (bool) ($exam->has_oral      ?? false);
        $showPractical = (bool) ($exam->has_practical ?? false);

        return [
            'show_theory'    => $showTheory,
            'show_oral'      => $showOral,
            'show_practical' => $showPractical,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | COMPONENT MAX MARKS + PASSING MARKS
    |--------------------------------------------------------------------------
    |
    | PRIMARY SOURCE: ExamStructureHelper
    |
    | The Exam Master edit page displays exactly these values. Reading from
    | the same helper guarantees the teacher/admin Mark Entry pages always
    | agree with the Exam Master configuration.
    |
    | FALLBACK: subject snapshot (exam_master_subjects) — used only when
    | the exam structure has no entry for the (standard, exam, subject).
    |--------------------------------------------------------------------------
    */

    public static function getComponentMaxMarks($exam, $subjectConfig): array
    {
        /* ==========================================================
         | 1. Resolve identifiers for the ExamStructureHelper lookup
         ========================================================== */

        $standardId = self::numberProperty($subjectConfig, 'standard_id', null)
            ?? self::numberProperty($exam, 'standard_id', null);

        $subjectName = self::stringValue($subjectConfig, 'subject_name');
        $examName    = self::stringValue($exam, 'exam_name');

        $structure = null;

        if ($standardId && $subjectName !== '' && $examName !== '') {
            $structure = \App\Helpers\ExamStructureHelper::getComponentValues(
                (int) $standardId,
                $examName,
                $subjectName
            );
        }

        /* ==========================================================
         | 2. If the structure has real data, use it
         ========================================================== */

        if (
            $structure &&
            (
                $structure['theory_max_marks']    > 0 ||
                $structure['oral_max_marks']      > 0 ||
                $structure['practical_max_marks'] > 0
            )
        ) {
            $theoryMax     = (float) $structure['theory_max_marks'];
            $theoryPass    = (float) $structure['theory_passing_marks'];
            $oralMax       = (float) $structure['oral_max_marks'];
            $oralPass      = (float) $structure['oral_passing_marks'];
            $practicalMax  = (float) $structure['practical_max_marks'];
            $practicalPass = (float) $structure['practical_passing_marks'];

            return [
                'show_theory'    => $theoryMax    > 0,
                'show_oral'      => $oralMax      > 0,
                'show_practical' => $practicalMax > 0,

                'theory_max'        => $theoryMax,
                'theory_passing'    => $theoryPass,

                'oral_max'          => $oralMax,
                'oral_passing'      => $oralPass,

                'practical_max'     => $practicalMax,
                'practical_passing' => $practicalPass,

                'total_max'         => (float) $structure['total_max_marks'],
                'total_passing'     => (float) $structure['total_passing_marks'],
            ];
        }

        /* ==========================================================
         | 3. Fallback — snapshot-based logic (legacy)
         ========================================================== */

        $components = self::getExamComponents($exam, $subjectConfig);

        $theoryMax = self::numberProperty($subjectConfig, 'theory_max_marks', null);

        if ($theoryMax === null) {
            $theoryMax = self::numberProperty($subjectConfig, 'max_marks', 0);
        }

        $oralMax = self::numberProperty($subjectConfig, 'oral_max_marks', null);

        if ($oralMax === null) {
            $oralMax = self::numberProperty($exam, 'oral_max_marks', 0);
        }

        $practicalMax = self::numberProperty($subjectConfig, 'practical_max_marks', null);

        if ($practicalMax === null) {
            $practicalMax = self::numberProperty($exam, 'practical_max_marks', 0);
        }

        if (!$components['show_theory'])    $theoryMax    = 0;
        if (!$components['show_oral'])      $oralMax      = 0;
        if (!$components['show_practical']) $practicalMax = 0;

        $theoryPassing = self::getComponentPassingMarks(
            $standardId,
            $subjectConfig,
            'theory',
            $theoryMax
        );

        $oralPassing = self::getComponentPassingMarks(
            $standardId,
            $subjectConfig,
            'oral',
            $oralMax,
            $exam
        );

        $practicalPassing = self::getComponentPassingMarks(
            $standardId,
            $subjectConfig,
            'practical',
            $practicalMax,
            $exam
        );

        return [
            'show_theory'    => $components['show_theory'],
            'show_oral'      => $components['show_oral'],
            'show_practical' => $components['show_practical'],

            'theory_max'        => (float) $theoryMax,
            'theory_passing'    => (float) $theoryPassing,

            'oral_max'          => (float) $oralMax,
            'oral_passing'      => (float) $oralPassing,

            'practical_max'     => (float) $practicalMax,
            'practical_passing' => (float) $practicalPassing,

            'total_max' => self::getSubjectTotalMaxMarks(
                $subjectConfig,
                $theoryMax + $oralMax + $practicalMax
            ),

            'total_passing' => self::getSubjectTotalPassingMarks(
                $standardId,
                $subjectConfig,
                $theoryPassing + $oralPassing + $practicalPassing
            ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | TERM ASSESSMENT STRUCTURE
    |--------------------------------------------------------------------------
    */

    public static function getTermAssessmentStructure(
        $exam,
        $subjectConfig,
        $standardId = null
    ): array {
        $structure = self::getComponentMaxMarks($exam, $subjectConfig);

        return [
            'is_term' => self::isTermExam($exam->exam_name ?? ''),

            'show_theory'    => $structure['show_theory'],
            'show_oral'      => $structure['show_oral'],
            'show_practical' => $structure['show_practical'],

            'theory_max'        => (float) $structure['theory_max'],
            'theory_passing'    => (float) $structure['theory_passing'],

            'oral_max'          => (float) $structure['oral_max'],
            'oral_passing'      => (float) $structure['oral_passing'],

            'practical_max'     => (float) $structure['practical_max'],
            'practical_passing' => (float) $structure['practical_passing'],

            'total_max'     => (float) $structure['total_max'],
            'total_passing' => (float) $structure['total_passing'],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | SUBJECT TOTAL MAXIMUM
    |--------------------------------------------------------------------------
    */

    public static function getSubjectTotalMaxMarks(
        $subjectConfig,
        $componentSum = 0
    ): float {
        $explicitTotal = self::numberProperty($subjectConfig, 'max_marks', null);

        if ($explicitTotal !== null && $explicitTotal > 0) {
            return (float) $explicitTotal;
        }

        $aggregateTotal = self::numberProperty($subjectConfig, 'aggregate_max_marks', null);

        if ($aggregateTotal !== null && $aggregateTotal > 0) {
            return (float) $aggregateTotal;
        }

        return (float) $componentSum;
    }

    /*
    |--------------------------------------------------------------------------
    | SUBJECT TOTAL PASSING MARKS
    |--------------------------------------------------------------------------
    */

    public static function getSubjectTotalPassingMarks(
        $standardId,
        $subjectConfig,
        $componentPassSum = 0
    ): float {
        $explicitTotal = self::numberProperty($subjectConfig, 'passing_marks', null);

        if ($explicitTotal !== null && $explicitTotal > 0) {
            return (float) $explicitTotal;
        }

        $aggregatePassing = self::numberProperty($subjectConfig, 'aggregate_passing_marks', null);

        if ($aggregatePassing !== null && $aggregatePassing > 0) {
            return (float) $aggregatePassing;
        }

        return (float) $componentPassSum;
    }

    /*
    |--------------------------------------------------------------------------
    | COMPONENT PASSING MARKS
    |--------------------------------------------------------------------------
    */

    public static function getComponentPassingMarks(
        $standardId,
        $subjectConfig,
        string $component,
        $maxMarks,
        $exam = null
    ): float {
        $maxMarks = (float) $maxMarks;

        if ($maxMarks <= 0) {
            return 0.0;
        }

        $propertyMap = [
            'theory'    => ['theory_passing_marks', 'passing_marks'],
            'oral'      => ['oral_passing_marks'],
            'practical' => ['practical_passing_marks'],
        ];

        foreach ($propertyMap[$component] ?? [] as $property) {
            $value = self::numberProperty($subjectConfig, $property, null);

            if ($value === null || $value <= 0) {
                continue;
            }

            if ($property === 'passing_marks' && $component !== 'theory') {
                continue;
            }

            return min((float) $value, $maxMarks);
        }

        if ($component === 'oral' && $exam) {
            $value = self::numberProperty($exam, 'oral_passing_marks', null);

            if ($value !== null && $value > 0) {
                return min((float) $value, $maxMarks);
            }
        }

        if ($component === 'practical' && $exam) {
            $value = self::numberProperty($exam, 'practical_passing_marks', null);

            if ($value !== null && $value > 0) {
                return min((float) $value, $maxMarks);
            }
        }

        return (float) self::getPassingMarks($standardId, $maxMarks);
    }

    /*
    |--------------------------------------------------------------------------
    | NORMALIZED SUBJECT STRUCTURE
    |--------------------------------------------------------------------------
    */

    public static function getNormalizedSubjectStructure(
        $exam,
        $subjectConfig,
        $standardId = null
    ): array {
        $structure = self::getComponentMaxMarks($exam, $subjectConfig);

        return [
            'show_theory'    => (bool) $structure['show_theory'],
            'show_oral'      => (bool) $structure['show_oral'],
            'show_practical' => (bool) $structure['show_practical'],

            'theory_max'        => (float) $structure['theory_max'],
            'theory_passing'    => (float) $structure['theory_passing'],

            'oral_max'          => (float) $structure['oral_max'],
            'oral_passing'      => (float) $structure['oral_passing'],

            'practical_max'     => (float) $structure['practical_max'],
            'practical_passing' => (float) $structure['practical_passing'],

            'total_max'     => (float) $structure['total_max'],
            'total_passing' => (float) $structure['total_passing'],

            'is_optional' => (bool) (
                self::numberProperty($subjectConfig, 'is_optional', 0) > 0
            ),

            'excel_row' => self::numberProperty($subjectConfig, 'excel_row_number', null),

            'structure_source_hash' => self::hasProperty($subjectConfig, 'structure_source_hash')
                ? (string) ($subjectConfig->structure_source_hash ?? '')
                : '',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | INTERNAL SAFE PROPERTY HELPERS
    |--------------------------------------------------------------------------
    */

    private static function hasProperty($object, string $property): bool
    {
        if ($object === null) {
            return false;
        }

        if (is_object($object)) {
            return property_exists($object, $property) || isset($object->{$property});
        }

        if (is_array($object)) {
            return array_key_exists($property, $object);
        }

        return false;
    }

    private static function numberProperty(
        $object,
        string $property,
        $default = 0
    ): ?float {
        if ($object === null) {
            return $default === null ? null : (float) $default;
        }

        if (is_array($object)) {
            if (!array_key_exists($property, $object)) {
                return $default === null ? null : (float) $default;
            }
            $value = $object[$property];
        } elseif (is_object($object)) {
            if (!property_exists($object, $property) && !isset($object->{$property})) {
                return $default === null ? null : (float) $default;
            }
            $value = $object->{$property};
        } else {
            return $default === null ? null : (float) $default;
        }

        if ($value === null || $value === '') {
            return $default === null ? null : (float) $default;
        }

        if (!is_numeric($value)) {
            return $default === null ? null : (float) $default;
        }

        return (float) $value;
    }

    private static function stringValue($object, string $property): string
    {
        if ($object === null) {
            return '';
        }

        if (is_array($object)) {
            return isset($object[$property])
                ? trim((string) $object[$property])
                : '';
        }

        if (is_object($object)) {
            return isset($object->{$property})
                ? trim((string) $object->{$property})
                : '';
        }

        return '';
    }

    /*
    |--------------------------------------------------------------------------
    | MARK VALIDATION
    |--------------------------------------------------------------------------
    */

    public static function hasMark($value): bool
    {
        return !($value === null || $value === '');
    }

    public static function validateObtainedMarks(
        $value,
        $maxMarks,
        string $component,
        $studentId
    ): ?string {
        if (!self::hasMark($value)) {
            return $component . ' marks are missing for one or more students.';
        }

        $obtained = (float) $value;
        $maxMarks = (float) $maxMarks;

        if ($obtained < 0 || ($maxMarks > 0 && $obtained > $maxMarks)) {
            return 'Invalid ' . $component . ' marks found for Student ID ' . $studentId . '.';
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
        if (isset($student->Studentid) && $student->Studentid !== '') {
            return (string) $student->Studentid;
        }

        if (isset($student->student_id) && $student->student_id !== '') {
            return (string) $student->student_id;
        }

        if (isset($student->id) && $student->id !== '') {
            return (string) $student->id;
        }

        return null;
    }

    public static function getStudentIds($students): Collection
    {
        return collect($students)
            ->map(fn ($student) => self::getStudentId($student))
            ->filter()
            ->unique()
            ->values();
    }

    public static function getSavedStudentIds($marks): Collection
    {
        return collect($marks)
            ->pluck('student_id')
            ->map(fn ($id) => (string) $id)
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
        $roll = $student->roll_no
            ?? $student->roll_number
            ?? $student->roll
            ?? $student->student_roll_no
            ?? null;

        if ($roll === null || $roll === '') {
            return null;
        }

        return (int) $roll;
    }

    public static function sortStudentsByRoll($students): Collection
    {
        return collect($students)
            ->sortBy(function ($student) {
                $roll = self::getRollNumber($student);
                return $roll === null ? PHP_INT_MAX : $roll;
            })
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

        $examAcademicYearId = $exam->academic_year_id !== null
            ? (int) $exam->academic_year_id
            : null;

        if (!$examAcademicYearId) {
            return 'Selected Exam does not have an Academic Year assigned.';
        }

        if ($requestedAcademicYearId !== null && $requestedAcademicYearId !== '') {
            if ($examAcademicYearId !== (int) $requestedAcademicYearId) {
                return 'Selected Exam does not belong to the selected Academic Year.';
            }
        }

        if ($allocation) {
            $allocationAcademicYearId = $allocation->academic_year_id !== null
                ? (int) $allocation->academic_year_id
                : null;

            if ($allocationAcademicYearId && $examAcademicYearId !== $allocationAcademicYearId) {
                return 'Selected Exam does not belong to the Academic Year of the selected Teaching Assignment.';
            }
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | SUBJECT RESOLUTION MAP
    |--------------------------------------------------------------------------
    */

    public static function buildSubjectResolutionMap($assignments): Collection
    {
        $map = collect();

        if (!$assignments || $assignments->isEmpty()) {
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
            ->join('subjects as s', 's.id', '=', 'sws.subject_id')
            ->whereIn('sws.standard_id', $standardIds)
            ->where('sws.is_active', 1)
            ->where('s.is_active', 1)
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
            $currentKey = (int) $mapping->standard_id . ':subject:' . (int) $mapping->subject_id;
            $map->put($currentKey, $mapping);

            $legacyKey = (int) $mapping->standard_id . ':sws:' . (int) $mapping->sws_id;
            $map->put($legacyKey, $mapping);
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
        if ($storedSubjectId === null || $storedSubjectId === '') {
            return null;
        }

        $storedSubjectId = (int) $storedSubjectId;

        if ($storedSubjectId <= 0) {
            return null;
        }

        if ($standardId && $subjectMap instanceof Collection) {
            $standardId = (int) $standardId;

            $currentKey = $standardId . ':subject:' . $storedSubjectId;
            $mapping = $subjectMap->get($currentKey);

            if ($mapping) {
                return (int) $mapping->actual_subject_id;
            }

            $legacyKey = $standardId . ':sws:' . $storedSubjectId;
            $mapping = $subjectMap->get($legacyKey);

            if ($mapping) {
                return (int) $mapping->actual_subject_id;
            }
        }

        $subject = DB::table('subjects')
            ->where('id', $storedSubjectId)
            ->where('is_active', 1)
            ->first();

        if ($subject) {
            if (!$standardId) {
                return $storedSubjectId;
            }

            $exists = DB::table('standard_wise_subjects')
                ->where('standard_id', (int) $standardId)
                ->where('subject_id', $storedSubjectId)
                ->where('is_active', 1)
                ->exists();

            if ($exists) {
                return $storedSubjectId;
            }
        }

        if ($standardId) {
            $mapping = DB::table('standard_wise_subjects')
                ->where('id', $storedSubjectId)
                ->where('standard_id', (int) $standardId)
                ->where('is_active', 1)
                ->first();

            if ($mapping && !empty($mapping->subject_id)) {
                return (int) $mapping->subject_id;
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

        if (self::isAdministrator()) {
            return true;
        }

        return TeacherMarksStatus::where('teacher_subject_allocation_id', $teacherSubjectAllocation->id)
            ->where('exam_master_id', $examMasterId)
            ->where('teacher_id', Auth::id())
            ->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | BOOLEAN REQUEST VALUE
    |--------------------------------------------------------------------------
    */

    public static function toBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === null) {
            return false;
        }

        $normalized = strtolower(trim((string) $value));

        if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
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

    public static function isAllowedMarkField($field): bool
    {
        return in_array($field, self::getAllowedMarkFields(), true);
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
        $components = self::getComponentMaxMarks($exam, $subjectConfig);

        return match ($field) {
            'theory_obtained_marks'    => (float) $components['theory_max'],
            'oral_obtained_marks'      => (float) $components['oral_max'],
            'practical_obtained_marks' => (float) $components['practical_max'],
            default                    => 0.0,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | FIELD PASSING MARKS
    |--------------------------------------------------------------------------
    */

    public static function getFieldPassingMarks(
        string $field,
        $standardId,
        $exam,
        $subjectConfig
    ): float {
        $components = self::getComponentMaxMarks($exam, $subjectConfig);

        return match ($field) {
            'theory_obtained_marks'    => (float) $components['theory_passing'],
            'oral_obtained_marks'      => (float) $components['oral_passing'],
            'practical_obtained_marks' => (float) $components['practical_passing'],
            default                    => 0.0,
        };
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
        return StudentMark::where('academic_year_id', $allocation->academic_year_id)
            ->where('section_id', $allocation->section_id)
            ->where('standard_id', $allocation->standard_id)
            ->where('division_id', $allocation->division_id)
            ->where('student_id', $studentId)
            ->where('exam_master_id', $examId)
            ->where('teacher_subject_allocation_id', $tsaId)
            ->where('subject_id', $subjectId)
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
        $query = TeacherMarksStatus::where('exam_master_id', $examId)
            ->where('teacher_subject_allocation_id', $tsaId);

        if (!$isAdministrator) {
            $query->where('teacher_id', $userId ?? Auth::id());
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
        return DB::table('exam_master_subjects')
            ->where('exam_master_id', $examId)
            ->where('standard_id', $standardId)
            ->where('subject_id', $subjectId)
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
        if (!$exam || !$standardId || !$actualSubjectId) {
            return null;
        }

        $subjectConfig = DB::table('exam_master_subjects')
            ->where('exam_master_id', $exam->id)
            ->where('standard_id', $standardId)
            ->where('subject_id', $actualSubjectId)
            ->first();

        if ($subjectConfig) {
            return $subjectConfig;
        }

        $mapping = DB::table('standard_wise_subjects')
            ->where('standard_id', $standardId)
            ->where('subject_id', $actualSubjectId)
            ->where('is_active', 1)
            ->first();

        if (!$mapping) {
            return null;
        }

        return DB::table('exam_master_subjects')
            ->where('exam_master_id', $exam->id)
            ->where('standard_id', $standardId)
            ->whereIn('subject_id', [$actualSubjectId, $mapping->id])
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
        $standardId = (int) $standardId;

        if ($standardId <= 0) {
            return null;
        }

        $actualSubjectId = self::resolveActualSubjectId($storedSubjectId, $standardId, $subjectMap);

        if ($actualSubjectId) {
            if ($subjectCollection instanceof Collection) {
                $subject = $subjectCollection->get($actualSubjectId);

                if ($subject) {
                    return $subject;
                }
            }

            $subject = DB::table('subjects')
                ->where('id', $actualSubjectId)
                ->where('is_active', 1)
                ->first();

            if ($subject) {
                return $subject;
            }
        }

        if ($tmsSubjectId !== null && $tmsSubjectId !== '' && (int) $tmsSubjectId > 0) {
            $tmsSubjectId = (int) $tmsSubjectId;

            if ($subjectMap instanceof Collection) {
                $currentKey = $standardId . ':subject:' . $tmsSubjectId;
                $mapping = $subjectMap->get($currentKey);

                if ($mapping) {
                    $actualSubjectId = (int) $mapping->actual_subject_id;

                    if ($subjectCollection instanceof Collection) {
                        $subject = $subjectCollection->get($actualSubjectId);

                        if ($subject) {
                            return $subject;
                        }
                    }
                }
            }

            if ($subjectMap instanceof Collection) {
                $legacyKey = $standardId . ':sws:' . $tmsSubjectId;
                $mapping = $subjectMap->get($legacyKey);

                if ($mapping) {
                    $actualSubjectId = (int) $mapping->actual_subject_id;

                    if ($subjectCollection instanceof Collection) {
                        $subject = $subjectCollection->get($actualSubjectId);

                        if ($subject) {
                            return $subject;
                        }
                    }

                    $subject = DB::table('subjects')
                        ->where('id', $actualSubjectId)
                        ->where('is_active', 1)
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
        $storedSubjectId = (int) ($tsa->subject_id ?? 0);
        $actualSubjectId = (int) $actualSubjectId;
        $standardId = (int) $standardId;

        if ($storedSubjectId <= 0 || $actualSubjectId <= 0 || $standardId <= 0) {
            return false;
        }

        if ($storedSubjectId === $actualSubjectId) {
            return true;
        }

        $mapping = DB::table('standard_wise_subjects')
            ->where('id', $storedSubjectId)
            ->where('standard_id', $standardId)
            ->where('is_active', 1)
            ->first();

        return $mapping && (int) $mapping->subject_id === $actualSubjectId;
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
        $ids = collect();

        if (!$currentTsa || !$allocation) {
            return $ids;
        }

        $ids->push((int) $currentTsa->id);

        $query = TeacherSubjectAllocation::query()
            ->where('exam_master_id', (int) $examId)
            ->where('teacher_class_allocation_id', (int) $currentTsa->teacher_class_allocation_id);

        $possibleSubjectIds = collect();

        if ($actualSubjectId) {
            $actualSubjectId = (int) $actualSubjectId;
            $possibleSubjectIds->push($actualSubjectId);

            $legacyMappings = DB::table('standard_wise_subjects')
                ->where('standard_id', (int) $allocation->standard_id)
                ->where('subject_id', $actualSubjectId)
                ->where('is_active', 1)
                ->get();

            foreach ($legacyMappings as $mapping) {
                $possibleSubjectIds->push((int) $mapping->id);
            }
        }

        if ($possibleSubjectIds->isNotEmpty()) {
            $query->whereIn('subject_id', $possibleSubjectIds->unique()->values()->all());
        }

        $related = $query->pluck('id')->map(fn ($id) => (int) $id);

        return $ids->merge($related)->unique()->values();
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
        $empty = collect();

        if (!$teacherSubjectAllocation || !$allocation || !$examId) {
            return $empty;
        }

        $tsaIds = self::getRelatedTeacherSubjectAllocationIds(
            $teacherSubjectAllocation,
            $allocation,
            $actualSubjectId,
            $examId
        );

        if ($tsaIds->isEmpty()) {
            return $empty;
        }

        $marks = StudentMark::query()
            ->where('exam_master_id', (int) $examId)
            ->whereIn('teacher_subject_allocation_id', $tsaIds)
            ->orderByDesc('id')
            ->get();

        $result = collect();

        foreach ($marks as $mark) {
            $studentId = (string) $mark->student_id;

            if (!$result->has($studentId)) {
                $result->put($studentId, $mark);
                continue;
            }

            if ((int) $mark->teacher_subject_allocation_id === (int) $teacherSubjectAllocation->id) {
                $result->put($studentId, $mark);
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
        $isOptional = $optionalEnabled && self::toBoolean(
            self::requestValue($request, 'is_optional', $studentId, false)
        );

        $isAbsent = self::toBoolean(
            self::requestValue($request, 'is_absent', $studentId, false)
        );

        if ($isOptional) {
            $isAbsent = false;
        }

        return [
            'is_optional' => $isOptional,
            'is_absent'   => $isAbsent,
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
        if ($isAbsent || $isOptional) {
            return 0;
        }

        if (!$enabled) {
            return null;
        }

        return self::requestValue($request, $field, $studentId, null);
    }

    /*
    |--------------------------------------------------------------------------
    | SAFE REQUEST VALUE
    |--------------------------------------------------------------------------
    */

    private static function requestValue(
        $request,
        string $field,
        $studentId,
        $default = null
    ) {
        if (!$request || !method_exists($request, 'input')) {
            return $default;
        }

        $value = $request->input($field . '.' . $studentId);

        return $value === null ? $default : $value;
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

        foreach ($studentIds as $studentId) {
            $values = self::getOptionalMarkValues($request, $studentId, $optionalEnabled);

            if ($values['is_optional'] || $values['is_absent']) {
                continue;
            }

            if ($showTheory) {
                $rules["theory_marks.$studentId"] = 'required|numeric|min:0|max:' . $theoryMaxMarks;
            }

            if ($showOral) {
                $rules["oral_marks.$studentId"] = 'required|numeric|min:0|max:' . $oralMaxMarks;
            }

            if ($showPractical) {
                $rules["practical_marks.$studentId"] = 'required|numeric|min:0|max:' . $practicalMaxMarks;
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
        foreach ($studentIds as $studentId) {
            $values = self::getOptionalMarkValues($request, $studentId, $optionalEnabled);

            if ($values['is_optional'] || $values['is_absent']) {
                continue;
            }

            if ($showTheory) {
                $v = self::requestValue($request, 'theory_marks', $studentId, null);
                if ($v === null || $v === '') {
                    return 'Please enter Theory marks for all students.';
                }
            }

            if ($showOral) {
                $v = self::requestValue($request, 'oral_marks', $studentId, null);
                if ($v === null || $v === '') {
                    return 'Please enter Oral marks for all students.';
                }
            }

            if ($showPractical) {
                $v = self::requestValue($request, 'practical_marks', $studentId, null);
                if ($v === null || $v === '') {
                    return 'Please enter Practical marks for all students.';
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

    public static function isCompletedStatus($marksStatus): bool
    {
        if (!$marksStatus) {
            return false;
        }

        return strtoupper(trim((string) ($marksStatus->status ?? ''))) === 'COMPLETED';
    }

    /*
    |--------------------------------------------------------------------------
    | FIELD MAXIMUM VALIDATION
    |--------------------------------------------------------------------------
    */

    public static function validateFieldMaximum($value, $maxMarks): bool
    {
        return (float) $value <= (float) $maxMarks;
    }
}