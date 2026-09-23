<?php

namespace App\Helpers;

use Illuminate\Support\Collection;

class ResultSheetBladeHelper
{
    /* ======================================================================
     | Staff name helpers
     ====================================================================== */

    public static function formatStaffName($name): string
    {
        $name = str_replace('.', ' ', trim((string) $name));
        $name = preg_replace('/\s+/', ' ', $name) ?? '';
        return ucwords(strtolower(trim($name)));
    }

    public static function extractStaffName($record): string
    {
        if (!$record) return '';

        $candidates = [
            $record->user?->name ?? null,
            $record->teacher?->name ?? null,
            $record->staff?->name ?? null,
            $record->employee?->name ?? null,
            $record->name ?? null,
            $record->full_name ?? null,
            $record->teacher_name ?? null,
            $record->staff_name ?? null,
            $record->employee_name ?? null,
        ];

        foreach ($candidates as $candidate) {
            $candidate = trim((string) $candidate);
            if ($candidate !== '') return $candidate;
        }

        return '';
    }

    /* ======================================================================
     | Column key / display helpers
     ====================================================================== */

    public static function normalizeSubjectName($column): string
    {
        return strtoupper(trim(preg_replace(
            '/\s+/',
            ' ',
            (string) ($column->subject_name ?? $column->subject_code ?? '')
        )));
    }

    public static function getSubjectKey($column): string
    {
        if (isset($column->key) && trim((string) $column->key) !== '') {
            return (string) $column->key;
        }
        if (isset($column->subject_id) && $column->subject_id !== '') {
            return 'SUBJECT_' . (int) $column->subject_id;
        }
        if (isset($column->mapping_id) && $column->mapping_id !== '') {
            return 'MAPPING_' . (int) $column->mapping_id;
        }
        if (!empty($column->subject_code)) return (string) $column->subject_code;
        if (!empty($column->subject_name)) return (string) $column->subject_name;
        return '';
    }

    public static function toArray($value): array
    {
        if ($value instanceof Collection) return $value->toArray();
        if (is_object($value))            return (array) $value;
        return is_array($value) ? $value : [];
    }

    public static function getCollectionValue($collection, $column)
    {
        $collection = self::toArray($collection);
        if (!$collection) return null;

        $keys = [];
        $subjectKey = self::getSubjectKey($column);
        if ($subjectKey !== '') $keys[] = $subjectKey;

        $subjectId = (int) ($column->subject_id ?? 0);
        if ($subjectId > 0) {
            $keys[] = 'SUBJECT_' . $subjectId;
            $keys[] = 'subject_' . $subjectId;
            $keys[] = (string) $subjectId;
        }

        $mappingId = (int) ($column->mapping_id ?? 0);
        if ($mappingId > 0) {
            $keys[] = 'MAPPING_' . $mappingId;
            $keys[] = 'mapping_' . $mappingId;
            $keys[] = (string) $mappingId;
        }

        if (!empty($column->subject_code)) $keys[] = (string) $column->subject_code;
        if (!empty($column->subject_name)) $keys[] = (string) $column->subject_name;

        $keys = array_values(array_unique($keys));

        foreach ($keys as $k) {
            if (array_key_exists($k, $collection)) return $collection[$k];
        }

        foreach ($collection as $storedKey => $storedValue) {
            foreach ($keys as $k) {
                if (strcasecmp(trim((string) $storedKey), trim((string) $k)) === 0) {
                    return $storedValue;
                }
            }
        }

        foreach ($collection as $storedKey => $storedValue) {
            $sn = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) $storedKey));
            foreach ($keys as $k) {
                $kn = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) $k));
                if ($sn !== '' && $sn === $kn) return $storedValue;
            }
        }

        return null;
    }

    /* ======================================================================
     | Student mark / grade / result accessors
     ====================================================================== */

    public static function getStudentMark($student, $column)
    {
        return self::getCollectionValue($student->subject_marks ?? [], $column);
    }

    public static function getStudentGrade($student, $column): string
    {
        if (self::getStudentOptionalFlag($student, $column)) return 'OPT';

        $grade = self::getCollectionValue($student->subject_grades ?? [], $column);

        $mark = self::getStudentMark($student, $column);
        if (strtoupper(trim((string) ($mark ?? ''))) === 'OPT') return 'OPT';

        if ($grade === null || $grade === '') return '-';
        return strtoupper(trim((string) $grade));
    }

    public static function getStudentResult($student, $column)
    {
        return self::getCollectionValue($student->subject_results ?? [], $column);
    }

    public static function getStudentOptionalFlag($student, $column): bool
    {
        $mark = self::getStudentMark($student, $column);

        if ($mark !== null && strtoupper(trim((string) $mark)) === 'OPT') {
            return true;
        }

        $optionalMap = self::toArray($student->subject_is_optional ?? []);
        $value = self::getCollectionValue($optionalMap, $column);

        if ($value !== null) {
            $v = strtoupper(trim((string) $value));
            return in_array($v, ['1', 'TRUE', 'YES', 'Y', 'OPT'], true);
        }

        return false;
    }

    /* ======================================================================
     | Senior standard / optional column flags
     ====================================================================== */

    public static function isSeniorOptionalStandard(int $standardId): bool
    {
        return in_array($standardId, [19, 20, 21, 22], true);
    }

    public static function isSeniorOptionalColumn($column, int $standardId): bool
    {
        if (!isset($column->is_optional)) return false;
        return (int) $column->is_optional === 1;
    }

    public static function isSeniorCompulsoryColumn($column, int $standardId): bool
    {
        if (!isset($column->is_optional)) return true;
        return (int) $column->is_optional !== 1;
    }

    /* ======================================================================
     | 12 Science detection
     ====================================================================== */

    public static function isScienceStandard12(int $standardId): bool
    {
        $ids = config('result_sheet.science_12_standard_ids', [20]);

        if (!empty($ids) && in_array($standardId, $ids, true)) {
            return true;
        }

        return $standardId === 20;
    }

    /* ======================================================================
     | Exam-master enrichment
     |
     | Reads exam_master_subjects (only columns max_marks, passing_marks)
     | and force-overwrites each column's passing_marks / fills max_marks.
     ====================================================================== */

    public static function enrichColumnsWithExamMaster(
        Collection $columns,
        int $examMasterId,
        int $standardId
    ): Collection {
        if ($examMasterId <= 0 || $standardId <= 0 || $columns->isEmpty()) {
            return $columns;
        }

        try {
            $rows = \DB::table('exam_master_subjects')
                ->select('subject_id', 'max_marks', 'passing_marks')
                ->where('exam_master_id', $examMasterId)
                ->where('standard_id', $standardId)
                ->get();
        } catch (\Throwable $e) {
            return $columns;
        }

        if ($rows->isEmpty()) return $columns;

        /* Build [subject_id => row] lookup */
        $lookup = [];
        foreach ($rows as $row) {
            $sid = (int) ($row->subject_id ?? 0);
            if ($sid > 0) {
                $lookup[$sid] = $row;
            }
        }

        if (empty($lookup)) return $columns;

        return $columns->map(function ($col) use ($lookup) {

            $sid = (int) ($col->subject_id ?? 0);
            if ($sid <= 0 || !isset($lookup[$sid])) {
                return $col;
            }

            $row = $lookup[$sid];

            /* -------- passing_marks -------- */
            if (isset($row->passing_marks)
                && $row->passing_marks !== null
                && (float) $row->passing_marks > 0
            ) {
                $col->passing_marks = (float) $row->passing_marks;
            }

            /* -------- max_marks (fill if missing) -------- */
            if ((!isset($col->max_marks) || (float) $col->max_marks <= 0)
                && isset($row->max_marks)
                && $row->max_marks !== null
                && (float) $row->max_marks > 0
            ) {
                $col->max_marks = (float) $row->max_marks;
            }

            return $col;
        });
    }

    /* ======================================================================
     | Passing marks resolution
     ====================================================================== */

    public static function resolveSubjectPassing($column, int $standardId): float
    {
        if (isset($column->passing_marks)
            && $column->passing_marks !== null
            && $column->passing_marks !== ''
            && (float) $column->passing_marks > 0
        ) {
            return (float) $column->passing_marks;
        }

        $max = (float) ($column->max_marks ?? 0);
        if ($max <= 0) return 0.0;

        return (float) \App\Helpers\MarksHelper::getPassingMarks($standardId, $max);
    }

    /* ======================================================================
     | Grade + number display
     ====================================================================== */

    public static function getGradeFromPercentage($percentage): string
    {
        if ($percentage === null || $percentage === '') return '-';
        $p = (float) $percentage;

        if ($p >= 91) return 'A1';
        if ($p >= 81) return 'A2';
        if ($p >= 71) return 'B1';
        if ($p >= 61) return 'B2';
        if ($p >= 51) return 'C1';
        if ($p >= 41) return 'C2';
        if ($p >= 33) return 'D';
        if ($p >= 21) return 'E1';
        if ($p >= 1)  return 'E2';
        return 'F';
    }

    public static function displayNumber($value): string
    {
        if ($value === null || $value === '' || $value === '-') return '-';
        if (!is_numeric($value)) return (string) $value;
        $number = (float) $value;
        return floor($number) === $number
            ? (string) ((int) $number)
            : number_format($number, 2, '.', '');
    }

    public static function formatMark($value)
    {
        if ($value === null || $value === '') return null;
        if (!is_numeric($value)) return $value;
        $number = (float) $value;
        return floor($number) === $number ? (int) $number : round($number, 2);
    }

    /* ======================================================================
     | Unique student list
     ====================================================================== */

    public static function getStudentKey($student)
    {
        $studentKey = (int) ($student->student_id ?? 0);
        if ($studentKey > 0) return $studentKey;
        return 'ROW_' . ($student->roll_no ?? '') . '_' . md5((string) ($student->full_student_name ?? ''));
    }

    public static function getUniqueSortedStudents(Collection $results): Collection
    {
        $uniqueStudents = collect();
        $seenStudents = [];

        foreach ($results as $student) {
            $studentId = (int) ($student->student_id ?? 0);
            $rollNo    = trim((string) ($student->roll_no ?? ''));

            $studentName = trim((string) ($student->full_student_name ?? $student->student_name ?? $student->full_name ?? ''));
            $fatherName  = trim((string) ($student->father_name ?? $student->father_full_name ?? $student->father ?? ''));

            $displayStudentName = $fatherName !== ''
                ? trim($studentName . ' ' . $fatherName)
                : $studentName;

            if ($studentId > 0) {
                $uniqueKey = 'ID:' . $studentId;
            } elseif ($rollNo !== '') {
                $uniqueKey = 'ROLL:' . strtoupper($rollNo);
            } elseif ($studentName !== '') {
                $uniqueKey = 'NAME:' . strtoupper(trim(preg_replace('/\s+/', ' ', $studentName) ?? $studentName));
            } else {
                continue;
            }

            if (isset($seenStudents[$uniqueKey])) continue;
            $seenStudents[$uniqueKey] = true;

            $student->full_student_name = $displayStudentName;
            $uniqueStudents->push($student);
        }

        return $uniqueStudents
            ->sortBy(function ($student) {
                $rollNo = trim((string) ($student->roll_no ?? ''));
                if ($rollNo !== '' && is_numeric($rollNo)) return [0, (int) $rollNo];
                return [1, strtoupper($rollNo)];
            })
            ->values();
    }

    /* ======================================================================
     | CALCULATE — per student
     ====================================================================== */

    public static function calculateResultSheetStudent(
        $student,
        Collection $columns,
        int $standardId,
        int $passingPercentage
    ): array {

        if (isset($student->calculated_percentage) || isset($student->academic_total)) {

            $subjectDetails = [];

            foreach ($columns as $column) {
                $subjectDetails[] = [
                    'column'            => $column,
                    'mark'              => self::getStudentMark($student, $column),
                    'grade'             => self::getStudentGrade($student, $column),
                    'optional'          => self::getStudentOptionalFlag($student, $column),
                    'selected_optional' => !self::getStudentOptionalFlag($student, $column),
                    'absent'            => strtoupper(trim((string) self::getStudentMark($student, $column))) === 'AB',
                    'failed'            => strtoupper(trim((string) self::getStudentResult($student, $column))) === 'FAIL',
                ];
            }

            return [
                'total_marks'             => $student->academic_total ?? 0,
                'total_max_marks'         => $student->academic_max_used ?? 0,
                'percentage'              => $student->calculated_percentage ?? null,
                'grade'                   => $student->calculated_grade ?? '-',
                'result'                  => $student->result ?? 'PENDING',
                'active_subjects'         => 0,
                'optional_subjects'       => 0,
                'selected_optional_count' => 0,
                'absent_subjects'         => $student->has_absent ? 1 : 0,
                'failed_subjects'         => 0,
                'subject_details'         => $subjectDetails,
            ];
        }

        $totalMarks     = 0.0;
        $totalMaxMarks  = 0.0;
        $hasFail        = false;
        $hasAbsent      = false;
        $hasAnyMark     = false;

        foreach ($columns as $column) {

            $isColumnOptional  = (int) ($column->is_optional ?? 0) === 1;
            $isStudentOptional = self::getStudentOptionalFlag($student, $column);

            if ($isColumnOptional && $isStudentOptional) {
                continue;
            }

            $maxMarks = (float) ($column->max_marks ?? 0);
            if ($maxMarks > 0) $totalMaxMarks += $maxMarks;

            $mark = self::getStudentMark($student, $column);

            if ($mark === null || $mark === '' || strtoupper(trim((string) $mark)) === 'OPT') {
                continue;
            }

            if (strtoupper(trim((string) $mark)) === 'AB') {
                $hasAbsent = true;
                $hasAnyMark = true;
                continue;
            }

            if (is_numeric($mark)) {
                $hasAnyMark = true;
                $totalMarks += (float) $mark;

                $passing = self::resolveSubjectPassing($column, $standardId);

                if ($passing > 0 && (float) $mark < $passing) {
                    $hasFail = true;
                }
            }
        }

        $percentage = $totalMaxMarks > 0
            ? round(($totalMarks / $totalMaxMarks) * 100, 2)
            : null;

        $result = 'PENDING';
        if ($hasAnyMark) {
            if ($hasAbsent || $hasFail) {
                $result = 'FAIL';
            } elseif ($percentage !== null && $percentage >= $passingPercentage) {
                $result = 'PASS';
            } else {
                $result = 'FAIL';
            }
        }

        return [
            'total_marks'             => $totalMarks,
            'total_max_marks'         => $totalMaxMarks,
            'percentage'              => $percentage,
            'grade'                   => self::getGradeFromPercentage($percentage),
            'result'                  => $result,
            'active_subjects'         => 0,
            'optional_subjects'       => 0,
            'selected_optional_count' => 0,
            'absent_subjects'         => $hasAbsent ? 1 : 0,
            'failed_subjects'         => $hasFail ? 1 : 0,
            'subject_details'         => [],
        ];
    }

    public static function buildStudentCalculations(
        Collection $students,
        Collection $columns,
        int $standardId,
        int $passingPercentage
    ): array {

        $calculations = [];
        foreach ($students as $student) {
            $calculations[self::getStudentKey($student)] = self::calculateResultSheetStudent(
                $student, $columns, $standardId, $passingPercentage
            );
        }
        return $calculations;
    }

    /* ======================================================================
     | Display total max marks
     ====================================================================== */

    public static function calculateDisplayTotalMaxMarks(
        Collection $columns,
        int $standardId
    ): int {
        if (self::isScienceStandard12($standardId)) {
            return (int) round(self::calculateScience12TotalMax($columns));
        }

        return (int) round(
            $columns->sum(fn ($column) => (float) ($column->max_marks ?? 0))
        );
    }

    private static function calculateScience12TotalMax(Collection $columns): float
    {
        $compulsoryMax = 0.0;
        $optionalMaxes = [];

        foreach ($columns as $column) {
            $max = (float) ($column->max_marks ?? 0);
            $isOptional = (int) ($column->is_optional ?? 0) === 1;

            if ($isOptional) {
                $optionalMaxes[] = $max;
            } else {
                $compulsoryMax += $max;
            }
        }

        $optionalSlots = (int) config('result_sheet.science_12_optional_slots', 3);

        rsort($optionalMaxes);
        $optionalMax = array_sum(array_slice($optionalMaxes, 0, $optionalSlots));

        return $compulsoryMax + $optionalMax;
    }

    public static function calculateStudentEffectiveMax(
        $student,
        Collection $columns
    ): float {
        $total = 0.0;

        foreach ($columns as $column) {
            $isColOptional = (int) ($column->is_optional ?? 0) === 1;
            $isStudentOpt  = self::getStudentOptionalFlag($student, $column);

            if ($isColOptional && $isStudentOpt) continue;

            $total += (float) ($column->max_marks ?? 0);
        }

        return $total;
    }

    /* ======================================================================
     | Subject analysis
     ====================================================================== */

    public static function calculateSubjectAnalysis(
        Collection $students,
        Collection $columns,
        array $studentCalculations,
        bool $includeFail = true
    ): array {

        $subjectAnalysis = [];

        foreach ($columns as $column) {
            $code = self::subjectCode($column);
            $subjectAnalysis[$code] = [
                'subject_name' => $column->subject_name ?? $code,
                'A1_girls' => 0, 'A1_boys' => 0,
                'A2_girls' => 0, 'A2_boys' => 0,
                'B1_girls' => 0, 'B1_boys' => 0,
                'B2_girls' => 0, 'B2_boys' => 0,
                'C1_girls' => 0, 'C1_boys' => 0,
                'C2_girls' => 0, 'C2_boys' => 0,
                'D_girls'  => 0, 'D_boys'  => 0,
                'E1_girls' => 0, 'E1_boys' => 0,
                'E2_girls' => 0, 'E2_boys' => 0,
                'F_girls'  => 0, 'F_boys'  => 0,
                'absent_girls' => 0, 'absent_boys' => 0,
            ];
        }

        foreach ($students as $student) {
            $key  = self::getStudentKey($student);
            $calc = $studentCalculations[$key] ?? null;
            if (!$calc || empty($calc['subject_details'])) continue;

            $gender = self::isFemale($student) ? 'girls' : 'boys';

            foreach ($calc['subject_details'] as $detail) {
                $column = $detail['column'] ?? null;
                if (!$column) continue;
                $code = self::subjectCode($column);
                if (!isset($subjectAnalysis[$code])) continue;

                if (!empty($detail['optional']) && empty($detail['selected_optional'])) {
                    continue;
                }

                if (!empty($detail['absent'])) {
                    $subjectAnalysis[$code]['absent_' . $gender]++;
                    continue;
                }

                if (!empty($detail['failed'])) {
                    if ($includeFail) $subjectAnalysis[$code]['F_' . $gender]++;
                    continue;
                }

                $grade = strtoupper(trim((string) ($detail['grade'] ?? '')));
                if (in_array($grade, ['A1','A2','B1','B2','C1','C2','D','E1','E2'], true)) {
                    $subjectAnalysis[$code][$grade . '_' . $gender]++;
                }
            }
        }

        return $subjectAnalysis;
    }

    public static function subjectCode($column): string
    {
        return trim((string) ($column->subject_code ?? $column->subject_id ?? $column->subject_name ?? ''));
    }

    public static function isFemale($student): bool
    {
        return in_array(
            strtoupper(trim((string) ($student->gender ?? $student->sex ?? ''))),
            ['F', 'FEMALE', 'GIRL', 'GIRLS'],
            true
        );
    }

    /* ======================================================================
     | Overall grade analysis
     ====================================================================== */

    public static function getOverallGradeAnalysis(Collection $students, array $calculations): array
    {
        $ranges = [
            'A1' => '91-100%',
            'A2' => '81-90%',
            'B1' => '71-80%',
            'B2' => '61-70%',
            'C1' => '51-60%',
            'C2' => '41-50%',
            'D'  => '33-40%',
            'E1' => '21-32%',
            'E2' => '1-20%',
        ];

        $analysis = [];
        foreach ($ranges as $grade => $range) {
            $analysis[$grade] = ['range' => $range, 'girls' => 0, 'boys' => 0, 'total' => 0];
        }
        $analysis['TOTAL'] = ['range' => 'TOTAL', 'girls' => 0, 'boys' => 0, 'total' => $students->count()];

        foreach ($students as $student) {
            $calc = $calculations[self::getStudentKey($student)] ?? null;
            if (!$calc) continue;

            $gender = self::isFemale($student) ? 'girls' : 'boys';
            $grade  = strtoupper(trim((string) ($calc['grade'] ?? '')));

            if (isset($analysis[$grade])) {
                $analysis[$grade][$gender]++;
                $analysis[$grade]['total']++;
            }

            $analysis['TOTAL'][$gender]++;
        }

        return $analysis;
    }

    /* ======================================================================
     | Unified sheet-data builder
     ====================================================================== */

    public static function buildSheetData(array $data): array
    {
        $columns  = collect($data['displayColumns'] ?? [])->values();
        $results  = collect($data['results'] ?? []);
        $standard = $data['standard'] ?? null;

        $standardId = (int) ($standard?->id ?? 0);

        /* -------- exam_master_id resolution --------
         | Prefer explicit key, else exam object, else the request.
         */
        $examMasterId = (int) (
            $data['examMasterId']
            ?? ($data['examMaster']->id ?? null)
            ?? ($data['exam']->id ?? null)
            ?? request('exam_master_id')
            ?? 0
        );

        /* -------- inject passing / max from exam master -------- */
        $columns = self::enrichColumnsWithExamMaster($columns, $examMasterId, $standardId);

        $passingPercentage = isset($data['passPercentage']) && $data['passPercentage'] !== null
            ? (float) $data['passPercentage']
            : (float) \App\Helpers\MarksHelper::getPassingPercentage($standardId);

        $students = self::getUniqueSortedStudents($results);

        $calculations = self::buildStudentCalculations(
            $students,
            $columns,
            $standardId,
            (int) $passingPercentage
        );

        $rawClassTeacher = self::extractStaffName($data['classTeacher'] ?? null);
        if ($rawClassTeacher === '' && isset($data['classTeacherName'])) {
            $rawClassTeacher = trim((string) $data['classTeacherName']);
        }

        $rawPrincipal = self::extractStaffName($data['principal'] ?? null);
        if ($rawPrincipal === '' && isset($data['principalName'])) {
            $rawPrincipal = trim((string) $data['principalName']);
        }

        return [
            'columns'                  => $columns,
            'students'                 => $students,
            'standardId'               => $standardId,
            'isScienceStandard12'      => self::isScienceStandard12($standardId),
            'isSeniorOptionalStandard' => self::isSeniorOptionalStandard($standardId),
            'passingPercentage'        => $passingPercentage,
            'calculations'             => $calculations,
            'displayTotalMaxMarks'     => self::calculateDisplayTotalMaxMarks($columns, $standardId),
            'overallGradeAnalysis'     => self::getOverallGradeAnalysis($students, $calculations),
            'subjectAnalysis'          => self::calculateSubjectAnalysis($students, $columns, $calculations, false),
            'classTeacherName'         => self::formatStaffName($rawClassTeacher),
            'principalName'            => self::formatStaffName($rawPrincipal),
            'schoolCode'               => session('school_code', 'shirgaon'),
            'hasData'                  => $columns->count() > 0,
        ];
    }

    public static function preparePrintData(array $data): array
    {
        return self::buildSheetData($data);
    }

    /* ======================================================================
     | Subject header
     ====================================================================== */

    public static function getSubjectHeader($column, int $standardId): array
    {
        return [
            'max'      => (float) ($column->max_marks ?? 0),
            'passing'  => self::resolveSubjectPassing($column, $standardId),
            'optional' => (int) ($column->is_optional ?? 0) === 1,
        ];
    }

    /* ======================================================================
     | Display helpers
     ====================================================================== */

    public static function displayResultMark($value, callable $displayNumber): string
    {
        $value = $value ?? '-';
        $text  = strtoupper(trim((string) $value));

        if ($text === 'AB' || $text === 'ABSENT') return 'AB';
        if ($text === 'OPT')                       return 'OPT';
        if ($value === '-')                        return '-';

        return is_numeric($value) ? $displayNumber($value) : (string) $value;
    }

    public static function displayResultGrade($value): string
    {
        if ($value === null || $value === '') return '-';
        $v = strtoupper(trim((string) $value));
        if ($v === 'OPT') return 'OPT';
        if ($v === 'AB' || $v === 'ABSENT') return 'AB';
        return $v;
    }
}