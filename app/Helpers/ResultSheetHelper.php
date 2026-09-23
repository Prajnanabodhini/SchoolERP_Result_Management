<?php

namespace App\Helpers;

use Illuminate\Support\Collection;

class ResultSheetHelper
{
    /* ======================================================================
     | STAFF NAME
     ====================================================================== */

    public static function formatStaffName($name): string
    {
        $name = str_replace('.', ' ', trim((string) $name));
        $name = preg_replace('/\s+/', ' ', $name) ?? '';
        return ucwords(strtolower(trim($name)));
    }

    public static function normalizeText($value): string
    {
        return preg_replace('/[^A-Z0-9]+/', '', strtoupper(trim((string) $value))) ?? '';
    }

    public static function normalizeGender($gender): string
    {
        $value = strtoupper(trim((string) $gender));

        if (in_array($value, ['1', 'M', 'MALE', 'BOY', 'BOYS', 'MAN', 'MEN'], true)) return 'MALE';
        if (in_array($value, ['2', 'F', 'FEMALE', 'GIRL', 'GIRLS', 'WOMAN', 'WOMEN'], true)) return 'FEMALE';

        $value = preg_replace('/[^A-Z]/', '', $value) ?? '';
        if (in_array($value, ['M', 'MALE'], true))   return 'MALE';
        if (in_array($value, ['F', 'FEMALE'], true)) return 'FEMALE';

        return 'UNKNOWN';
    }

    /* ======================================================================
     | PASSING PERCENTAGE PER STANDARD
     |======================================================================|
     | Delegates to ResultHelper which already has the exact 35% / 40%
     | rule for every standard ID.
     ====================================================================== */

    public static function getPassingPercentage(?int $standardId = null): int
    {
        return ResultHelper::getPassingPercentage($standardId);
    }

    public static function calculatePassingMarks($maxMarks, int $percentage = 35): int
    {
        $maxMarks = (float) $maxMarks;
        return $maxMarks > 0 ? (int) ceil(($maxMarks * $percentage) / 100) : 0;
    }

    /* ======================================================================
     | GRADE FROM PERCENTAGE
     |======================================================================|
     | Ranges used everywhere in this application:
     |
     |   A1 : 91 – 100
     |   A2 : 81 – 90
     |   B1 : 71 – 80
     |   B2 : 61 – 70
     |   C1 : 51 – 60
     |   C2 : 41 – 50
     |   D  : 33 – 40
     |   E1 : 21 – 32
     |   E2 : 1  – 20
     |   F  : < 1
     |
     | IMPORTANT: E1 and E2 MUST exist. Without them, students with
     | 21–32% or 1–20% collapse into F and disappear from the
     | Overall Grade Analysis table.
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

    /* ======================================================================
     | COLUMN KEY RESOLUTION
     ====================================================================== */

    public static function getSubjectKey($column): string
    {
        if (!empty($column->key))          return (string) $column->key;
        if (!empty($column->subject_id))   return 'SUBJECT_' . (int) $column->subject_id;
        if (!empty($column->mapping_id))   return 'MAPPING_' . (int) $column->mapping_id;
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

    public static function findCollectionValue($collection, $column)
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

        foreach (array_unique($keys) as $key) {
            if (array_key_exists($key, $collection)) {
                return $collection[$key];
            }
        }

        foreach ($collection as $storedKey => $value) {
            foreach ($keys as $key) {
                if (strcasecmp(trim((string) $storedKey), trim((string) $key)) === 0) {
                    return $value;
                }
            }
        }

        return null;
    }

    /* ======================================================================
     | STUDENT ACCESSORS
     ====================================================================== */

    public static function getStudentMark($student, $column)
    {
        return self::findCollectionValue($student->subject_marks ?? [], $column);
    }

    public static function getStudentGrade($student, $column)
    {
        return self::findCollectionValue($student->subject_grades ?? [], $column);
    }

    public static function getStudentResult($student, $column)
    {
        return self::findCollectionValue($student->subject_results ?? [], $column);
    }

    public static function isColumnOptional($column): bool
    {
        if (!isset($column->is_optional)) return false;
        return (int) $column->is_optional === 1;
    }

    public static function isStudentOptional($student, $column): bool
    {
        $mark = self::getStudentMark($student, $column);

        if ($mark !== null && $mark !== '') {
            $text = strtoupper(trim((string) $mark));
            if ($text === 'OPT') return true;
            if (is_numeric($mark)) return false;
        }

        $optionalMap = self::toArray($student->subject_is_optional ?? []);
        $value = self::findCollectionValue($optionalMap, $column);

        if ($value !== null) {
            $text = strtoupper(trim((string) $value));
            return in_array($text, ['1', 'TRUE', 'YES', 'Y', 'OPT'], true);
        }

        return false;
    }

    /* ======================================================================
     | ABSENT DETECTION
     ====================================================================== */

    public static function isAbsent($row): bool
    {
        if (!$row) return false;

        foreach (['is_absent', 'status', 'marks_status', 'attendance_status', 'mark_status'] as $field) {
            if (!isset($row->{$field})) continue;
            $value = strtoupper(trim((string) $row->{$field}));
            if (in_array($value, ['1', 'TRUE', 'YES', 'Y', 'AB', 'A', 'ABS', 'ABSENT', 'NOT PRESENT', 'NOTPRESENT'], true)) {
                return true;
            }
        }

        foreach (['theory_obtained_marks', 'oral_obtained_marks', 'practical_obtained_marks'] as $field) {
            if (!isset($row->{$field})) continue;
            $value = strtoupper(trim((string) $row->{$field}));
            if (in_array($value, ['AB', 'A', 'ABS', 'ABSENT'], true)) return true;
        }

        return false;
    }

    /* ======================================================================
     | MARK EXTRACTION
     ====================================================================== */

    public static function extractObtainedMarks($row): ?float
    {
        if (!$row) return null;

        foreach (['obtained_marks', 'mark', 'marks'] as $field) {
            if (isset($row->{$field}) && $row->{$field} !== '' && $row->{$field} !== null && is_numeric($row->{$field})) {
                return (float) $row->{$field};
            }
        }

        $total = 0.0;
        $found = false;
        foreach (['theory_obtained_marks', 'oral_obtained_marks', 'practical_obtained_marks'] as $field) {
            if (isset($row->{$field}) && $row->{$field} !== '' && $row->{$field} !== null && is_numeric($row->{$field})) {
                $total += (float) $row->{$field};
                $found = true;
            }
        }

        return $found ? $total : null;
    }

    public static function extractMaxMarks($row): float
    {
        if (!$row) return 0.0;
        if (isset($row->max_marks) && is_numeric($row->max_marks)) {
            return (float) $row->max_marks;
        }

        $total = 0.0;
        foreach (['theory_max_marks', 'oral_max_marks', 'practical_max_marks'] as $field) {
            if (isset($row->{$field}) && $row->{$field} !== '' && $row->{$field} !== null && is_numeric($row->{$field})) {
                $total += (float) $row->{$field};
            }
        }
        return $total;
    }

    public static function extractPassingMarks($row): float
    {
        if (!$row) return 0.0;
        if (isset($row->passing_marks) && is_numeric($row->passing_marks)) {
            return (float) $row->passing_marks;
        }

        $total = 0.0;
        foreach (['theory_passing_marks', 'oral_passing_marks', 'practical_passing_marks'] as $field) {
            if (isset($row->{$field}) && $row->{$field} !== '' && $row->{$field} !== null && is_numeric($row->{$field})) {
                $total += (float) $row->{$field};
            }
        }
        return $total;
    }

    /* ======================================================================
     | FORMATTING
     ====================================================================== */

    public static function formatMark($value)
    {
        if ($value === null || $value === '') return null;
        $number = (float) $value;
        return floor($number) === $number ? (int) $number : round($number, 2);
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

    public static function emptySubjectAnalysis(): array
    {
        return [
            'A1' => 0, 'A2' => 0, 'B1' => 0, 'B2' => 0,
            'C1' => 0, 'C2' => 0, 'D' => 0,
            'E1' => 0, 'E2' => 0,
            'fail' => 0, 'absent' => 0, 'pending' => 0, 'total' => 0,
        ];
    }

    public static function cleanFileName(string $value): string
    {
        $value = preg_replace('/[^A-Za-z0-9\_-]+/', '_', $value) ?? '';
        return trim($value, '_');
    }

    /* ======================================================================
     | GROUP MARKS BY STUDENT AND SUBJECT
     ====================================================================== */

    public static function groupMarksByStudentAndSubject(
        Collection $markRows,
        Collection $columns
    ): Collection {

        $actualSubjectIds = $columns
            ->pluck('subject_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->flip();

        $mappingToSubject = $columns->mapWithKeys(function ($column) {
            $mappingId = (int) ($column->mapping_id ?? 0);
            $subjectId = (int) ($column->subject_id ?? 0);
            if ($mappingId <= 0 || $subjectId <= 0) return [];
            return [$mappingId => $subjectId];
        });

        $map = [];

        foreach ($markRows as $row) {
            $studentId = (int) ($row->student_id ?? 0);
            if ($studentId <= 0) continue;

            $storedSubjectId     = (int) ($row->subject_id ?? 0);
            $allocationSubjectId = (int) ($row->allocation_subject_id ?? 0);
            $subjectId           = null;

            if ($storedSubjectId > 0 && $actualSubjectIds->has($storedSubjectId)) {
                $subjectId = $storedSubjectId;
            }
            if ($subjectId === null && $allocationSubjectId > 0 && $actualSubjectIds->has($allocationSubjectId)) {
                $subjectId = $allocationSubjectId;
            }
            if ($subjectId === null && $storedSubjectId > 0 && $mappingToSubject->has($storedSubjectId)) {
                $subjectId = (int) $mappingToSubject->get($storedSubjectId);
            }

            if ($subjectId === null) continue;

            if (!isset($map[$studentId])) $map[$studentId] = [];
            if (!isset($map[$studentId][$subjectId])) {
                $map[$studentId][$subjectId] = $row;
            }
        }

        return collect($map);
    }

    /* ======================================================================
     | BUILD STUDENTS
     |======================================================================|
     | This is the definitive result-sheet student builder.
     |
     | PASS / FAIL RULES
     | ----------------
     | Each subject uses its own passing marks:
     |
     |   1. If exam_master_subjects.passing_marks > 0 → use it.
     |   2. Else compute as ceil( max_marks × passing_percentage / 100 )
     |      where passing_percentage is 35 or 40 depending on the standard.
     |
     | A student FAILS a subject when the obtained mark is below the
     | subject's passing marks.
     |
     | OVERALL RESULT
     | --------------
     |   FAIL  if any subject is FAIL or ABSENT,
     |         OR overall percentage < standard passing percentage.
     |
     |   PASS  only when every subject passes AND the overall percentage
     |         meets the standard's passing percentage.
     |
     |   PENDING  when any required mark is missing.
     ====================================================================== */

    public static function buildStudents(
        Collection $erpStudents,
        Collection $marksByStudent,
        Collection $columns,
        int $passPercentage
    ): Collection {

        $students = collect();

        foreach ($erpStudents as $erp) {

            $studentId = (int) ($erp->Studentid ?? 0);
            if ($studentId <= 0) continue;

            $studentMarks = (array) ($marksByStudent->get($studentId, []));

            $student = (object) [
                'student_id'            => $studentId,
                'id'                    => null,
                'roll_no'               => trim((string) ($erp->rollno ?? '')),
                'full_student_name'     => trim((string) ($erp->studname ?? '')),
                'father_name'           => trim((string) ($erp->fathername ?? '')),
                'gender'                => self::normalizeGender($erp->gender ?? ''),
                'subject_marks'         => [],
                'subject_grades'        => [],
                'subject_results'       => [],
                'subject_is_optional'   => [],
                'subject_max_used'      => [],
                'subject_passing_used'  => [],
                'academic_total'        => 0,
                'academic_max_used'     => 0,
                'academic_max_display'  => 0,
                'calculated_percentage' => null,
                'calculated_grade'      => '-',
                'result'                => 'PENDING',
                'has_absent'            => false,
                'has_any_mark'          => false,
                'has_incomplete_marks'  => false,
            ];

            if ($student->full_student_name === '') {
                $student->full_student_name = 'Student ID : ' . $studentId;
            }

            $studentTotal    = 0.0;
            $studentMax      = 0.0;
            $hasFail         = false;
            $missingRequired = false;

            foreach ($columns as $column) {

                $key       = self::getSubjectKey($column);
                $subjectId = (int) ($column->subject_id ?? 0);

                /* Defaults */
                $student->subject_marks[$key]        = '-';
                $student->subject_grades[$key]       = '-';
                $student->subject_results[$key]      = '-';
                $student->subject_is_optional[$key]  = 0;
                $student->subject_max_used[$key]     = 0.0;
                $student->subject_passing_used[$key] = 0.0;

                $maxMarks         = (float) ($column->max_marks ?? 0);
                $isColumnOptional = (int) ($column->is_optional ?? 0) === 1;

                /* ----------------------------------------------------------
                 | Effective passing marks for THIS subject
                 | 1. Use DB value if available.
                 | 2. Otherwise compute from the standard's percentage.
                 * ---------------------------------------------------------- */

                $passingMarks = (float) ($column->passing_marks ?? 0);

                if ($passingMarks <= 0 && $maxMarks > 0) {
                    $passingMarks = self::calculatePassingMarks($maxMarks, $passPercentage);
                }

                $row = $studentMarks[$subjectId] ?? null;

                /* ----------------------------------------------------------
                 | OPTIONAL SUBJECT HANDLING
                 * ---------------------------------------------------------- */

                if ($isColumnOptional) {

                    if (!$row) {
                        $student->subject_marks[$key]       = 'OPT';
                        $student->subject_grades[$key]      = 'OPT';
                        $student->subject_results[$key]     = 'OPT';
                        $student->subject_is_optional[$key] = 1;
                        continue;
                    }

                    $rowIsOptional = false;
                    if (isset($row->is_optional)) {
                        $v = strtoupper(trim((string) $row->is_optional));
                        $rowIsOptional = in_array($v, ['1', 'TRUE', 'YES', 'Y'], true);
                    }

                    if ($rowIsOptional) {
                        $student->subject_marks[$key]       = 'OPT';
                        $student->subject_grades[$key]      = 'OPT';
                        $student->subject_results[$key]     = 'OPT';
                        $student->subject_is_optional[$key] = 1;
                        continue;
                    }
                }

                /* ----------------------------------------------------------
                 | NORMAL SUBJECT
                 * ---------------------------------------------------------- */

                if ($maxMarks > 0) {
                    $studentMax += $maxMarks;
                    $student->subject_max_used[$key] = $maxMarks;
                }

                $student->subject_passing_used[$key] = $passingMarks;

                /* Missing mark row for a compulsory subject */
                if (!$row) {
                    $missingRequired = true;
                    $student->has_incomplete_marks = true;
                    continue;
                }

                /* Absent */
                if (self::isAbsent($row)) {
                    $student->subject_marks[$key]   = 'AB';
                    $student->subject_grades[$key]  = 'AB';
                    $student->subject_results[$key] = 'ABSENT';
                    $student->has_absent            = true;
                    $student->has_any_mark          = true;
                    continue;
                }

                /* Extract numeric mark */
                $obtained = self::extractObtainedMarks($row);

                if ($obtained === null) {
                    $missingRequired = true;
                    $student->has_incomplete_marks = true;
                    continue;
                }

                $obtained = max(0.0, $obtained);
                if ($maxMarks > 0) {
                    $obtained = min($obtained, $maxMarks);
                }

                $student->has_any_mark = true;
                $student->subject_marks[$key] = self::formatMark($obtained);
                $studentTotal += $obtained;

                /* Subject grade */
                $subjectPercentage = $maxMarks > 0 ? ($obtained / $maxMarks) * 100 : 0;
                $student->subject_grades[$key] = self::getGradeFromPercentage($subjectPercentage);

                /* Subject PASS / FAIL — uses the correct passing marks for this subject */
                if ($passingMarks > 0 && $obtained < $passingMarks) {
                    $hasFail = true;
                    $student->subject_results[$key] = 'FAIL';
                } else {
                    $student->subject_results[$key] = 'PASS';
                }
            }

            /* ==========================================================
             | FINAL TOTALS
             * ========================================================== */

            $student->academic_total       = self::formatMark($studentTotal);
            $student->academic_max_used    = $studentMax;
            $student->academic_max_display = $studentMax;

            /* ==========================================================
             | OVERALL RESULT
             * ==========================================================
             | FAIL    — any subject failed / absent,
             |           OR overall % < standard passing %
             | PASS    — all subjects passed AND overall % ≥ passing %
             | PENDING — any required mark missing
             */

            if ($missingRequired || !$student->has_any_mark) {

                $student->calculated_percentage = null;
                $student->calculated_grade      = '-';
                $student->result                = 'PENDING';

            } else {

                $student->calculated_percentage = $studentMax > 0
                    ? round(($studentTotal / $studentMax) * 100, 2)
                    : null;

                $student->calculated_grade = $student->calculated_percentage !== null
                    ? self::getGradeFromPercentage($student->calculated_percentage)
                    : '-';

                if ($student->has_absent || $hasFail) {
                    $student->result = 'FAIL';
                } elseif (
                    $student->calculated_percentage !== null &&
                    $student->calculated_percentage >= $passPercentage
                ) {
                    $student->result = 'PASS';
                } else {
                    $student->result = 'FAIL';
                }
            }

            $students->push($student);
        }

        return $students
            ->unique('student_id')
            ->sortBy(function ($student) {
                $roll = trim((string) ($student->roll_no ?? ''));
                if ($roll !== '' && is_numeric($roll)) return [0, (int) $roll];
                return [1, strtoupper($roll)];
            })
            ->values();
    }
}