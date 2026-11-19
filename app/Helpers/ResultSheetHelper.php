<?php

namespace App\Helpers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
     | PASSING PERCENTAGE PER STANDARD (HARDCODED 35%)
     |======================================================================| */

    public static function getPassingPercentage(?int $standardId = null): int
    {
        return 35;
    }

    public static function calculatePassingMarks($maxMarks, int $percentage = 35): int
    {
        $maxMarks = (float) $maxMarks;
        return $maxMarks > 0 ? (int) ceil(($maxMarks * $percentage) / 100) : 0;
    }

    /* ======================================================================
     | GRADE FROM PERCENTAGE
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
        $key = data_get($column, 'key');
        if (!empty($key)) return (string) $key;
        
        $subjectId = data_get($column, 'subject_id');
        if (!empty($subjectId)) return 'SUBJECT_' . (int) $subjectId;
        
        $mappingId = data_get($column, 'mapping_id');
        if (!empty($mappingId)) return 'MAPPING_' . (int) $mappingId;
        
        $subjectCode = data_get($column, 'subject_code');
        if (!empty($subjectCode)) return (string) $subjectCode;
        
        $subjectName = data_get($column, 'subject_name');
        if (!empty($subjectName)) return (string) $subjectName;
        
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

        $subjectId = (int) data_get($column, 'subject_id', 0);
        if ($subjectId > 0) {
            $keys[] = 'SUBJECT_' . $subjectId;
            $keys[] = 'subject_' . $subjectId;
            $keys[] = (string) $subjectId;
        }

        $mappingId = (int) data_get($column, 'mapping_id', 0);
        if ($mappingId > 0) {
            $keys[] = 'MAPPING_' . $mappingId;
            $keys[] = 'mapping_' . $mappingId;
            $keys[] = (string) $mappingId;
        }

        if (!empty(data_get($column, 'subject_code'))) $keys[] = (string) data_get($column, 'subject_code');
        if (!empty(data_get($column, 'subject_name'))) $keys[] = (string) data_get($column, 'subject_name');

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

    /* ======================================================================
     | STUDENT OPTIONAL SUBJECT DETECTION
     ====================================================================== */

    public static function isStudentOptional($student, $column): bool
    {
        if (!$student) {
            return false;
        }

        $map = self::toArray($student->subject_is_optional ?? []);
        if (!$map) {
            return false;
        }

        $key = self::getSubjectKey($column);

        if ($key !== '' && array_key_exists($key, $map)) {
            return (int) $map[$key] === 1;
        }

        $keys = [];
        if ($key !== '') {
            $keys[] = $key;
        }

        $subjectId = (int) data_get($column, 'subject_id', 0);
        if ($subjectId > 0) {
            $keys[] = 'SUBJECT_' . $subjectId;
            $keys[] = 'subject_' . $subjectId;
            $keys[] = (string) $subjectId;
        }

        $mappingId = (int) data_get($column, 'mapping_id', 0);
        if ($mappingId > 0) {
            $keys[] = 'MAPPING_' . $mappingId;
            $keys[] = 'mapping_' . $mappingId;
            $keys[] = (string) $mappingId;
        }

        if (!empty(data_get($column, 'subject_code'))) {
            $keys[] = (string) data_get($column, 'subject_code');
        }
        if (!empty(data_get($column, 'subject_name'))) {
            $keys[] = (string) data_get($column, 'subject_name');
        }

        foreach ($map as $storedKey => $value) {
            foreach (array_unique($keys) as $candidate) {
                if (strcasecmp(trim((string) $storedKey), trim((string) $candidate)) === 0) {
                    return (int) $value === 1;
                }
            }
        }

        return false;
    }

    public static function isColumnOptional($column): bool
    {
        if (data_get($column, 'is_optional') === null) return false;
        return (int) data_get($column, 'is_optional') === 1;
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
     | FORMATTING (ROUND DECIMALS)
     ====================================================================== */

    public static function formatMark($value)
    {
        if ($value === null || $value === '') return null;
        if (!is_numeric($value)) return $value;
        return (int) round((float) $value);
    }

    public static function displayNumber($value): string
    {
        if ($value === null || $value === '' || $value === '-') return '-';
        if (!is_numeric($value)) return (string) $value;
        return (string) (int) round((float) $value);
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
     | GROUP MARKS BY STUDENT AND SUBJECT (ROBUST ID MAPPING)
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
            $mappingId = (int) data_get($column, 'mapping_id', 0);
            $subjectId = (int) data_get($column, 'subject_id', 0);
            if ($mappingId <= 0 || $subjectId <= 0) return [];
            return [$mappingId => $subjectId];
        });

        // 1. Fetch mappings from TeacherMarksStatus and TeacherSubjectAllocation
        $tsaIds = $markRows->pluck('teacher_subject_allocation_id')->filter()->unique();
        $tsaToSubject = [];
        
        if ($tsaIds->isNotEmpty()) {
            $tmsToSubject = DB::table('teacher_marks_status')
                ->whereIn('teacher_subject_allocation_id', $tsaIds)
                ->pluck('subject_id', 'teacher_subject_allocation_id')
                ->toArray();
                
            $tsaAllocToSubject = DB::table('teacher_subject_allocations')
                ->whereIn('id', $tsaIds)
                ->pluck('subject_id', 'id')
                ->toArray();
                
            $tsaToSubject = array_merge($tsaAllocToSubject, $tmsToSubject);
        }

        // 2. Fetch subject names to handle legacy ID mismatch (e.g., 98 -> 10)
        $allStoredIds = $markRows->pluck('subject_id')
            ->merge($markRows->pluck('allocation_subject_id'))
            ->filter()
            ->unique()
            ->toArray();

        $subjectNames = [];
        if (!empty($allStoredIds)) {
            $subjectNames = DB::table('subjects')
                ->whereIn('id', $allStoredIds)
                ->pluck('subject_name', 'id')
                ->toArray();
        }

        // Create a reverse map of Subject Name -> Column Subject ID
        $nameToColumnSubjectId = [];
        foreach ($columns as $column) {
            $name = strtoupper(trim((string) data_get($column, 'subject_name', '')));
            if ($name !== '') {
                $nameToColumnSubjectId[$name] = (int) data_get($column, 'subject_id', 0);
            }
        }

        $map = [];

        foreach ($markRows as $row) {
            $studentId = (int) ($row->student_id ?? 0);
            if ($studentId <= 0) continue;

            $storedSubjectId     = (int) ($row->subject_id ?? 0);
            $allocationSubjectId = (int) ($row->allocation_subject_id ?? 0);
            $tsaId               = (int) ($row->teacher_subject_allocation_id ?? 0);
            $subjectId           = null;

            // Attempt 1: Direct subject_id match
            if ($storedSubjectId > 0 && $actualSubjectIds->has($storedSubjectId)) {
                $subjectId = $storedSubjectId;
            }
            
            // Attempt 2: Allocation subject_id match
            if ($subjectId === null && $allocationSubjectId > 0 && $actualSubjectIds->has($allocationSubjectId)) {
                $subjectId = $allocationSubjectId;
            }

            // Attempt 3: Mapping ID match
            if ($subjectId === null && $storedSubjectId > 0 && $mappingToSubject->has($storedSubjectId)) {
                $subjectId = (int) $mappingToSubject->get($storedSubjectId);
            }
            if ($subjectId === null && $allocationSubjectId > 0 && $mappingToSubject->has($allocationSubjectId)) {
                $subjectId = (int) $mappingToSubject->get($allocationSubjectId);
            }
            
            // Attempt 4: Name Match (Bypasses legacy ID mismatches)
            if ($subjectId === null && !empty($subjectNames[$storedSubjectId])) {
                $sName = strtoupper(trim($subjectNames[$storedSubjectId]));
                if (isset($nameToColumnSubjectId[$sName])) {
                    $subjectId = $nameToColumnSubjectId[$sName];
                }
            }
            if ($subjectId === null && !empty($subjectNames[$allocationSubjectId])) {
                $sName = strtoupper(trim($subjectNames[$allocationSubjectId]));
                if (isset($nameToColumnSubjectId[$sName])) {
                    $subjectId = $nameToColumnSubjectId[$sName];
                }
            }

            // Attempt 5: Teacher Subject Allocation fallback
            if ($subjectId === null && $tsaId > 0 && isset($tsaToSubject[$tsaId])) {
                $tsaSubjectId = (int) $tsaToSubject[$tsaId];
                
                if ($actualSubjectIds->has($tsaSubjectId)) {
                    $subjectId = $tsaSubjectId;
                } elseif ($mappingToSubject->has($tsaSubjectId)) {
                    $subjectId = (int) $mappingToSubject->get($tsaSubjectId);
                } elseif (!empty($subjectNames[$tsaSubjectId])) {
                    $sName = strtoupper(trim($subjectNames[$tsaSubjectId]));
                    if (isset($nameToColumnSubjectId[$sName])) {
                        $subjectId = $nameToColumnSubjectId[$sName];
                    }
                } else {
                    $matchedCol = $columns->firstWhere('subject_id', $tsaSubjectId);
                    if ($matchedCol) {
                        $subjectId = $tsaSubjectId;
                    }
                }
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
            $missingRequired = false;

            foreach ($columns as $column) {

                $key       = self::getSubjectKey($column);
                $subjectId = (int) data_get($column, 'subject_id', 0);

                /* Defaults */
                $student->subject_marks[$key]        = '-';
                $student->subject_grades[$key]       = '-';
                $student->subject_results[$key]      = '-';
                $student->subject_is_optional[$key]  = 0;
                $student->subject_max_used[$key]     = 0.0;
                $student->subject_passing_used[$key] = 0.0;

                $maxMarks         = (float) data_get($column, 'max_marks', 0);
                $isColumnOptional = (int) data_get($column, 'is_optional', 0) === 1;

                $row = $studentMarks[$subjectId] ?? null;

                if ($maxMarks <= 0 && $row) {
                    $maxMarks = self::extractMaxMarks($row);
                }
                
                if ($maxMarks <= 0) {
                    $maxMarks = 40;
                }

                /* 1. If the student is explicitly marked as OPTIONAL in student_marks */
                if ($row && isset($row->is_optional) && (int)$row->is_optional === 1) {
                    $student->subject_marks[$key]       = 'OPT';
                    $student->subject_grades[$key]      = 'OPT';
                    $student->subject_results[$key]     = 'OPT';
                    $student->subject_is_optional[$key] = 1;
                    continue;
                }

                /* 2. If no mark row exists */
                if (!$row) {
                    if ($isColumnOptional) {
                        $student->subject_marks[$key]       = 'OPT';
                        $student->subject_grades[$key]      = 'OPT';
                        $student->subject_results[$key]     = 'OPT';
                        $student->subject_is_optional[$key] = 1;
                    } else {
                        $missingRequired = true;
                        $student->has_incomplete_marks = true;
                    }
                    continue;
                }

                /* 3. If the student is ABSENT */
                if (self::isAbsent($row)) {
                    $student->subject_marks[$key]   = 'AB';
                    $student->subject_grades[$key]  = 'AB';
                    $student->subject_results[$key] = 'ABSENT';
                    $student->has_absent            = true;
                    $student->has_any_mark          = true;
                    
                    if ($maxMarks > 0) {
                        $studentMax += $maxMarks;
                        $student->subject_max_used[$key] = $maxMarks;
                    }
                    continue;
                }

                /* 4. Extract numeric mark */
                $obtained = self::extractObtainedMarks($row);

                if ($obtained === null) {
                    $missingRequired = true;
                    $student->has_incomplete_marks = true;
                    continue;
                }

                $obtained = max(0.0, $obtained);
                if ($maxMarks > 0) {
                    $obtained = min($obtained, $maxMarks);
                    $studentMax += $maxMarks;
                    $student->subject_max_used[$key] = $maxMarks;
                }

                $student->has_any_mark = true;
                
                // formatMark now rounds the obtained marks to nearest integer
                $student->subject_marks[$key] = self::formatMark($obtained);
                $studentTotal += $obtained;

                /* Subject grade */
                $subjectPercentage = $maxMarks > 0 ? ($obtained / $maxMarks) * 100 : 0;
                $student->subject_grades[$key] = self::getGradeFromPercentage($subjectPercentage);

                /* Effective passing marks */
                $passingMarks = (float) data_get($column, 'passing_marks', 0);
                if ($passingMarks <= 0 && $maxMarks > 0) {
                    $passingMarks = self::calculatePassingMarks($maxMarks, $passPercentage);
                }
                $student->subject_passing_used[$key] = $passingMarks;

                /* Subject PASS / FAIL */
                if ($passingMarks > 0 && $obtained < $passingMarks) {
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
             | OVERALL RESULT (AGGREGATE PASS LOGIC)
             * ========================================================== */

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

                if ($student->has_absent) {
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