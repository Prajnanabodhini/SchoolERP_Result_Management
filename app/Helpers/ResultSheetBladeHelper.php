<?php

namespace App\Helpers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ResultSheetBladeHelper
{
    public static function formatStaffName($name): string
    {
        $name = str_replace('.', ' ', trim((string) $name));
        $name = preg_replace('/\s+/', ' ', $name) ?? '';
        return ucwords(strtolower(trim($name)));
    }

    public static function extractStaffName($record): string
    {
        if (!$record) return '';
        $candidates = [data_get($record, 'user.name'), data_get($record, 'teacher.name'), data_get($record, 'staff.name'), data_get($record, 'employee.name'), data_get($record, 'name'), data_get($record, 'full_name'), data_get($record, 'teacher_name'), data_get($record, 'staff_name'), data_get($record, 'employee_name')];
        foreach ($candidates as $candidate) {
            $candidate = trim((string) $candidate);
            if ($candidate !== '') return $candidate;
        }
        return '';
    }

    public static function normalizeSubjectName($column): string
    {
        return strtoupper(trim(preg_replace('/\s+/', ' ', (string) (data_get($column, 'subject_name', data_get($column, 'subject_code', ''))))));
    }

    public static function getSubjectKey($column): string
    {
        if (data_get($column, 'key') && trim((string) data_get($column, 'key')) !== '') return (string) data_get($column, 'key');
        if (data_get($column, 'subject_id') !== null && data_get($column, 'subject_id') !== '') return 'SUBJECT_' . (int) data_get($column, 'subject_id');
        if (data_get($column, 'mapping_id') !== null && data_get($column, 'mapping_id') !== '') return 'MAPPING_' . (int) data_get($column, 'mapping_id');
        if (!empty(data_get($column, 'subject_code'))) return (string) data_get($column, 'subject_code');
        if (!empty(data_get($column, 'subject_name'))) return (string) data_get($column, 'subject_name');
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
        $subjectId = (int) data_get($column, 'subject_id', 0);
        if ($subjectId > 0) { $keys[] = 'SUBJECT_' . $subjectId; $keys[] = 'subject_' . $subjectId; $keys[] = (string) $subjectId; }
        $mappingId = (int) data_get($column, 'mapping_id', 0);
        if ($mappingId > 0) { $keys[] = 'MAPPING_' . $mappingId; $keys[] = 'mapping_' . $mappingId; $keys[] = (string) $mappingId; }
        if (!empty(data_get($column, 'subject_code'))) $keys[] = (string) data_get($column, 'subject_code');
        if (!empty(data_get($column, 'subject_name'))) $keys[] = (string) data_get($column, 'subject_name');
        $keys = array_values(array_unique($keys));
        foreach ($keys as $k) { if (array_key_exists($k, $collection)) return $collection[$k]; }
        foreach ($collection as $storedKey => $storedValue) { foreach ($keys as $k) { if (strcasecmp(trim((string) $storedKey), trim((string) $k)) === 0) return $storedValue; } }
        foreach ($collection as $storedKey => $storedValue) {
            $sn = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) $storedKey));
            foreach ($keys as $k) {
                $kn = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) $k));
                if ($sn !== '' && $sn === $kn) return $storedValue;
            }
        }
        return null;
    }

    public static function getStudentMark($student, $column) { return self::getCollectionValue(data_get($student, 'subject_marks', []), $column); }

    public static function getStudentGrade($student, $column): string
    {
        if (self::getStudentOptionalFlag($student, $column)) return 'OPT';
        $grade = self::getCollectionValue(data_get($student, 'subject_grades', []), $column);
        $mark = self::getStudentMark($student, $column);
        if (strtoupper(trim((string) ($mark ?? ''))) === 'OPT') return 'OPT';
        if ($grade === null || $grade === '') return '-';
        return strtoupper(trim((string) $grade));
    }

    public static function getStudentResult($student, $column) { return self::getCollectionValue(data_get($student, 'subject_results', []), $column); }

    public static function getStudentOptionalFlag($student, $column): bool
    {
        $mark = self::getStudentMark($student, $column);
        if ($mark !== null && strtoupper(trim((string) $mark)) === 'OPT') return true;
        $optionalMap = self::toArray(data_get($student, 'subject_is_optional', []));
        $value = self::getCollectionValue($optionalMap, $column);
        if ($value !== null) {
            $v = strtoupper(trim((string) $value));
            return in_array($v, ['1', 'TRUE', 'YES', 'Y', 'OPT'], true);
        }
        return false;
    }

    public static function isSeniorOptionalStandard(int $standardId): bool { return in_array($standardId, [19, 20, 21, 22], true); }
    public static function isSeniorOptionalColumn($column, int $standardId): bool { return data_get($column, 'is_optional') !== null && (int) data_get($column, 'is_optional') === 1; }
    public static function isSeniorCompulsoryColumn($column, int $standardId): bool { return data_get($column, 'is_optional') === null || (int) data_get($column, 'is_optional') !== 1; }
    public static function isScienceStandard12(int $standardId): bool { return $standardId === 20; }

    public static function enrichColumnsWithExamMaster(Collection $columns, int $examMasterId, int $standardId): Collection
    {
        if ($examMasterId <= 0 || $standardId <= 0 || $columns->isEmpty()) return $columns;
        try {
            $rows = DB::table('exam_master_subjects')->select('subject_id', 'max_marks', 'passing_marks')->where('exam_master_id', $examMasterId)->where('standard_id', $standardId)->get();
        } catch (\Throwable $e) { return $columns; }
        if ($rows->isEmpty()) return $columns;
        $lookup = [];
        foreach ($rows as $row) { $sid = (int) data_get($row, 'subject_id', 0); if ($sid > 0) $lookup[$sid] = $row; }
        if (empty($lookup)) return $columns;
        return $columns->map(function ($col) use ($lookup) {
            $sid = (int) data_get($col, 'subject_id', 0);
            if ($sid <= 0 || !isset($lookup[$sid])) return $col;
            $row = $lookup[$sid];
            if (data_get($row, 'passing_marks') !== null && (float) data_get($row, 'passing_marks') > 0) data_set($col, 'passing_marks', (float) data_get($row, 'passing_marks'));
            if ((data_get($col, 'max_marks') === null || (float) data_get($col, 'max_marks') <= 0) && data_get($row, 'max_marks') !== null && (float) data_get($row, 'max_marks') > 0) data_set($col, 'max_marks', (float) data_get($row, 'max_marks'));
            return $col;
        });
    }

    public static function resolveSubjectPassing($column, int $standardId): float
    {
        if (data_get($column, 'passing_marks') !== null && data_get($column, 'passing_marks') !== '' && (float) data_get($column, 'passing_marks') > 0) return (float) data_get($column, 'passing_marks');
        $max = (float) data_get($column, 'max_marks', 0);
        if ($max <= 0) return 0.0;
        return (float) \App\Helpers\MarksHelper::getPassingMarks($standardId, $max);
    }

    public static function getGradeFromPercentage($percentage): string
    {
        if ($percentage === null || $percentage === '') return '-';
        $p = (float) $percentage;
        if ($p >= 91) return 'A1'; if ($p >= 81) return 'A2'; if ($p >= 71) return 'B1';
        if ($p >= 61) return 'B2'; if ($p >= 51) return 'C1'; if ($p >= 41) return 'C2';
        if ($p >= 33) return 'D';  if ($p >= 21) return 'E1'; if ($p >= 1)  return 'E2';
        return 'F';
    }

    public static function displayNumber($value): string
    {
        if ($value === null || $value === '' || $value === '-') return '-';
        if (!is_numeric($value)) return (string) $value;
        $number = (float) $value;
        return floor($number) === $number ? (string) ((int) $number) : number_format($number, 2, '.', '');
    }

    public static function formatMark($value)
    {
        if ($value === null || $value === '') return null;
        if (!is_numeric($value)) return $value;
        $number = (float) $value;
        return floor($number) === $number ? (int) $number : round($number, 2);
    }

    public static function getStudentKey($student)
    {
        $studentKey = (int) data_get($student, 'student_id', 0);
        if ($studentKey > 0) return $studentKey;
        return 'ROW_' . data_get($student, 'roll_no', '') . '_' . md5((string) data_get($student, 'full_student_name', ''));
    }

    public static function getUniqueSortedStudents(Collection $results): Collection
    {
        $uniqueStudents = collect();
        $seenStudents = [];
        foreach ($results as $student) {
            $studentId = (int) data_get($student, 'student_id', 0);
            $rollNo    = trim((string) data_get($student, 'roll_no', ''));
            $studentName = trim((string) data_get($student, 'full_student_name', data_get($student, 'student_name', data_get($student, 'full_name', ''))));
            $fatherName  = trim((string) data_get($student, 'father_name', data_get($student, 'father_full_name', data_get($student, 'father', ''))));
            $displayStudentName = $fatherName !== '' ? trim($studentName . ' ' . $fatherName) : $studentName;
            if ($studentId > 0) { $uniqueKey = 'ID:' . $studentId; }
            elseif ($rollNo !== '') { $uniqueKey = 'ROLL:' . strtoupper($rollNo); }
            elseif ($studentName !== '') { $uniqueKey = 'NAME:' . strtoupper(trim(preg_replace('/\s+/', ' ', $studentName) ?? $studentName)); }
            else { continue; }
            if (isset($seenStudents[$uniqueKey])) continue;
            $seenStudents[$uniqueKey] = true;
            
            if (is_object($student)) {
                $student->full_student_name = $displayStudentName;
            } else {
                $student['full_student_name'] = $displayStudentName;
                $student = (object) $student;
            }
            $uniqueStudents->push($student);
        }
        return $uniqueStudents->sortBy(function ($student) {
            $rollNo = trim((string) data_get($student, 'roll_no', ''));
            if ($rollNo !== '' && is_numeric($rollNo)) return [0, (int) $rollNo];
            return [1, strtoupper($rollNo)];
        })->values();
    }

    public static function calculateResultSheetStudent($student, Collection $columns, int $standardId, int $passingPercentage): array
    {
        if (data_get($student, 'calculated_percentage') !== null || data_get($student, 'academic_total') !== null) {
            $subjectDetails = [];
            foreach ($columns as $column) {
                $subjectDetails[] = [
                    'column' => $column, 'mark' => self::getStudentMark($student, $column), 'grade' => self::getStudentGrade($student, $column),
                    'optional' => self::getStudentOptionalFlag($student, $column), 'selected_optional' => !self::getStudentOptionalFlag($student, $column),
                    'absent' => strtoupper(trim((string) self::getStudentMark($student, $column))) === 'AB', 'failed' => strtoupper(trim((string) self::getStudentResult($student, $column))) === 'FAIL',
                ];
            }
            return [
                'total_marks' => data_get($student, 'academic_total', 0), 
                'total_max_marks' => data_get($student, 'academic_max_used', 0), 
                'percentage' => data_get($student, 'calculated_percentage'), 
                'grade' => data_get($student, 'calculated_grade', '-'), 
                'result' => data_get($student, 'result', 'PENDING'), 
                'active_subjects' => 0, 'optional_subjects' => 0, 'selected_optional_count' => 0, 
                'absent_subjects' => data_get($student, 'has_absent') ? 1 : 0, 
                'failed_subjects' => 0, 
                'subject_details' => $subjectDetails
            ];
        }
        $totalMarks = 0.0; $totalMaxMarks = 0.0; $hasFail = false; $hasAbsent = false; $hasAnyMark = false;
        foreach ($columns as $column) {
            $isColumnOptional  = (int) data_get($column, 'is_optional', 0) === 1;
            $isStudentOptional = self::getStudentOptionalFlag($student, $column);
            if ($isColumnOptional && $isStudentOptional) continue;
            $maxMarks = (float) data_get($column, 'max_marks', 0);
            if ($maxMarks > 0) $totalMaxMarks += $maxMarks;
            $mark = self::getStudentMark($student, $column);
            if ($mark === null || $mark === '' || strtoupper(trim((string) $mark)) === 'OPT') continue;
            if (strtoupper(trim((string) $mark)) === 'AB') { $hasAbsent = true; $hasAnyMark = true; continue; }
            if (is_numeric($mark)) {
                $hasAnyMark = true; $totalMarks += (float) $mark;
                $passing = self::resolveSubjectPassing($column, $standardId);
                if ($passing > 0 && (float) $mark < $passing) $hasFail = true;
            }
        }
        $percentage = $totalMaxMarks > 0 ? round(($totalMarks / $totalMaxMarks) * 100, 2) : null;
        $result = 'PENDING';
        if ($hasAnyMark) {
            if ($hasAbsent || $hasFail) $result = 'FAIL';
            elseif ($percentage !== null && $percentage >= $passingPercentage) $result = 'PASS';
            else $result = 'FAIL';
        }
        return ['total_marks' => $totalMarks, 'total_max_marks' => $totalMaxMarks, 'percentage' => $percentage, 'grade' => self::getGradeFromPercentage($percentage), 'result' => $result, 'active_subjects' => 0, 'optional_subjects' => 0, 'selected_optional_count' => 0, 'absent_subjects' => $hasAbsent ? 1 : 0, 'failed_subjects' => $hasFail ? 1 : 0, 'subject_details' => []];
    }

    public static function buildStudentCalculations(Collection $students, Collection $columns, int $standardId, int $passingPercentage): array
    {
        $calculations = [];
        foreach ($students as $student) { $calculations[self::getStudentKey($student)] = self::calculateResultSheetStudent($student, $columns, $standardId, $passingPercentage); }
        return $calculations;
    }

    public static function calculateDisplayTotalMaxMarks(Collection $columns, int $standardId): int
    {
        if (self::isScienceStandard12($standardId)) return (int) round(self::calculateScience12TotalMax($columns));
        return (int) round($columns->sum(fn ($column) => (float) data_get($column, 'max_marks', 0)));
    }

    private static function calculateScience12TotalMax(Collection $columns): float
    {
        $compulsoryMax = 0.0; $optionalMaxes = [];
        foreach ($columns as $column) {
            $max = (float) data_get($column, 'max_marks', 0);
            $isOptional = (int) data_get($column, 'is_optional', 0) === 1;
            if ($isOptional) $optionalMaxes[] = $max; else $compulsoryMax += $max;
        }
        $optionalSlots = (int) config('result_sheet.science_12_optional_slots', 3);
        rsort($optionalMaxes);
        return $compulsoryMax + array_sum(array_slice($optionalMaxes, 0, $optionalSlots));
    }

    public static function calculateStudentEffectiveMax($student, Collection $columns): float
    {
        $total = 0.0;
        foreach ($columns as $column) {
            $isColOptional = (int) data_get($column, 'is_optional', 0) === 1;
            $isStudentOpt  = self::getStudentOptionalFlag($student, $column);
            if ($isColOptional && $isStudentOpt) continue;
            $total += (float) data_get($column, 'max_marks', 0);
        }
        return $total;
    }

    public static function calculateSubjectAnalysis(Collection $students, Collection $columns, array $studentCalculations, bool $includeFail = true): array
    {
        $subjectAnalysis = [];
        foreach ($columns as $column) {
            $code = self::subjectCode($column);
            $subjectAnalysis[$code] = ['subject_name' => data_get($column, 'subject_name', $code), 'A1_girls' => 0, 'A1_boys' => 0, 'A2_girls' => 0, 'A2_boys' => 0, 'B1_girls' => 0, 'B1_boys' => 0, 'B2_girls' => 0, 'B2_boys' => 0, 'C1_girls' => 0, 'C1_boys' => 0, 'C2_girls' => 0, 'C2_boys' => 0, 'D_girls' => 0, 'D_boys' => 0, 'E1_girls' => 0, 'E1_boys' => 0, 'E2_girls' => 0, 'E2_boys' => 0, 'F_girls' => 0, 'F_boys' => 0, 'absent_girls' => 0, 'absent_boys' => 0];
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
                if (!empty($detail['optional']) && empty($detail['selected_optional'])) continue;
                if (!empty($detail['absent'])) { $subjectAnalysis[$code]['absent_' . $gender]++; continue; }
                if (!empty($detail['failed'])) { if ($includeFail) $subjectAnalysis[$code]['F_' . $gender]++; continue; }
                $grade = strtoupper(trim((string) ($detail['grade'] ?? '')));
                if (in_array($grade, ['A1','A2','B1','B2','C1','C2','D','E1','E2','F'], true)) $subjectAnalysis[$code][$grade . '_' . $gender]++;
            }
        }
        return $subjectAnalysis;
    }

    public static function subjectCode($column): string { return trim((string) (data_get($column, 'subject_code', data_get($column, 'subject_id', data_get($column, 'subject_name', ''))))); }
    
    public static function isFemale($student): bool { 
        $gender = (string) data_get($student, 'gender', data_get($student, 'sex', ''));
        return in_array(strtoupper(trim($gender)), ['F', 'FEMALE', 'GIRL', 'GIRLS'], true); 
    }

    public static function getOverallGradeAnalysis(Collection $students, array $calculations): array
    {
        $ranges = ['A1' => '91-100%', 'A2' => '81-90%', 'B1' => '71-80%', 'B2' => '61-70%', 'C1' => '51-60%', 'C2' => '41-50%', 'D' => '33-40%', 'E1' => '21-32%', 'E2' => '1-20%', 'ABSENT' => 'Absent'];
        $analysis = [];
        foreach ($ranges as $grade => $range) $analysis[$grade] = ['range' => $range, 'girls' => 0, 'boys' => 0, 'total' => 0];
        $analysis['TOTAL'] = ['range' => 'TOTAL', 'girls' => 0, 'boys' => 0, 'total' => $students->count()];
        
        foreach ($students as $student) {
            $calc = $calculations[self::getStudentKey($student)] ?? null;
            if (!$calc) continue;
            $gender = self::isFemale($student) ? 'girls' : 'boys';
            $grade  = strtoupper(trim((string) ($calc['grade'] ?? '')));
            
            // Check if the student is fully absent (0 marks and has absent flag)
            if (($calc['absent_subjects'] ?? 0) > 0 && ($calc['total_marks'] ?? 0) == 0) {
                $grade = 'ABSENT';
            }
            
            if (isset($analysis[$grade])) { 
                $analysis[$grade][$gender]++; 
                $analysis[$grade]['total']++; 
            }
            $analysis['TOTAL'][$gender]++;
        }
        return $analysis;
    }

    private static function getCasteCategory($student): string
    {
        $catId = (int) data_get($student, 'category_id', data_get($student, 'MstCaste_CategoryID', data_get($student, 'categoryid', data_get($student, 'FeeMst_CategoryID', 0))));
        if ($catId > 0) {
            $idMap = [1 => 'OPEN', 2 => 'OBC', 3 => 'SC', 4 => 'NT', 5 => 'SBC', 6 => 'VJNT', 7 => 'ST', 8 => 'OPEN', 9 => 'OBC', 10 => 'NT', 11 => 'VJNT', 12 => 'NT', 13 => 'NT', 14 => 'VJNT', 15 => 'SBC', 16 => 'OPEN'];
            if (isset($idMap[$catId])) return $idMap[$catId];
        }
        $rawCat = trim((string) data_get($student, 'category_name', data_get($student, 'Category_12old_Name', data_get($student, 'Category', data_get($student, 'category', '')))));
        if ($rawCat !== '') {
            $cleanCat = preg_replace('/[^A-Z]/', '', strtoupper(trim($rawCat)));
            if (in_array($cleanCat, ['SC', 'SCHEDULEDCASTE'])) return 'SC';
            if (in_array($cleanCat, ['ST', 'SCHEDULEDTRIBE'])) return 'ST';
            if (in_array($cleanCat, ['VJNT', 'VJ', 'VJA', 'VIMUKTAJATI'])) return 'VJNT';
            if (in_array($cleanCat, ['NT', 'NTC', 'NTB', 'NTD', 'NOMADIC'])) return 'NT';
            if (in_array($cleanCat, ['SBC', 'SBC3'])) return 'SBC';
            if (in_array($cleanCat, ['OBC', 'BC', 'OTHERBACKWARDCLASS'])) return 'OBC';
            if (in_array($cleanCat, ['OPEN', 'GENERAL', 'HINDU'])) return 'OPEN';
        }
        $casteStr = trim((string) data_get($student, 'caste', data_get($student, 'MstCaste_Name', data_get($student, 'Caste', ''))));
        $c = preg_replace('/[^A-Z]/', '', strtoupper(trim($casteStr)));
        if ($c === '') return 'OPEN';
        if (preg_match('/(MAHAR|MANG|CHAMBAR|CHAMBHAR|BOUDHA|NAVABAUDHA|DHOR)/', $c)) return 'SC';
        if (preg_match('/(LAMANI|GOND|KOLAM|BAIGA|ANDH)/', $c)) return 'ST';
        if (preg_match('/(VJNT|VADAR)/', $c)) return 'VJNT';
        if (preg_match('/(NT|NTB|NTC|NTD|VANJARI)/', $c)) return 'NT';
        if (preg_match('/(SBC|KOLI|DHANGAR|PARIT|SUTAR|MOCHI)/', $c)) return 'SBC';
        if (preg_match('/(OBC|KUNBI|MALI|LOHAR|KUMBHAR|GUJAR|KIRAD|TELLI|BANJARA)/', $c)) return 'OBC';
        return 'OPEN';
    }

    public static function calculateCasteWiseAnalysis(Collection $students, array $calculations): array
    {
        $studentIds = $students->map(function($student) {
            return data_get($student, 'student_id', data_get($student, 'Studentid', data_get($student, 'old_student_id', data_get($student, 'id'))));
        })->filter()->unique()->toArray();

        $casteMap = collect();
        if (!empty($studentIds)) {
            try {
                $casteMap = DB::connection('sqlsrv_olderp')
                    ->table('FeeMstStudent as fs')
                    ->leftJoin('MstCaste as mc', 'mc.castid', '=', 'fs.casteid')
                    ->leftJoin('catagory_12old as cat', 'cat.categoryid', '=', 'mc.categoryid')
                    ->whereIn('fs.Studentid', $studentIds)
                    ->select('fs.Studentid', 'mc.Caste as caste', 'mc.categoryid as category_id', 'cat.Categoryname as category_name')
                    ->get()
                    ->keyBy('Studentid');
            } catch (\Exception $e) {
                \Log::error('CasteWiseAnalysis query failed: ' . $e->getMessage());
            }
        }

        $castes = ['SC', 'ST', 'VJNT', 'NT', 'OBC', 'SBC', 'OPEN']; 
        $grades = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2', 'D', 'E1', 'E2', 'ABSENT'];
        
        $analysis = [];
        $structure = [];
        foreach ($grades as $grade) { $structure[$grade . '_girls'] = 0; $structure[$grade . '_boys'] = 0; }
        $structure['total_girls'] = 0;
        $structure['total_boys'] = 0;
        
        foreach ($castes as $caste) { $analysis[$caste] = $structure; }
        $analysis['TOTAL'] = $structure;

        foreach ($students as $student) {
            $calc = $calculations[self::getStudentKey($student)] ?? null;
            if (!$calc) continue;

            $gender = self::isFemale($student) ? 'girls' : 'boys';
            $sid = data_get($student, 'student_id', data_get($student, 'Studentid', data_get($student, 'old_student_id', data_get($student, 'id'))));
            $dbCaste = $casteMap->get($sid); 
            
            if ($dbCaste) {
                if (is_object($student)) {
                    $student->caste = data_get($dbCaste, 'caste');
                    $student->category_name = data_get($dbCaste, 'category_name');
                    $student->category_id = data_get($dbCaste, 'category_id');
                } else {
                    $student['caste'] = data_get($dbCaste, 'caste');
                    $student['category_name'] = data_get($dbCaste, 'category_name');
                    $student['category_id'] = data_get($dbCaste, 'category_id');
                }
            }

            $caste  = self::getCasteCategory($student);
            $analysis[$caste]['total_' . $gender]++;
            $analysis['TOTAL']['total_' . $gender]++;

            $grade  = strtoupper(trim((string) ($calc['grade'] ?? '')));
            
            if (($calc['absent_subjects'] ?? 0) > 0 && ($calc['total_marks'] ?? 0) == 0) {
                $grade = 'ABSENT';
            }
            
            if (in_array($grade, $grades, true)) {
                $analysis[$caste][$grade . '_' . $gender]++;
                $analysis['TOTAL'][$grade . '_' . $gender]++;
            }
        }
        return $analysis;
    }

    public static function buildSheetData(array $data): array
    {
        $columns  = collect($data['displayColumns'] ?? [])->values();
        $results  = collect($data['results'] ?? []);
        $standard = $data['standard'] ?? null;
        $standardId = (int) data_get($standard, 'id', 0);

        $examMasterId = (int) (
            $data['examMasterId'] ?? data_get($data, 'examMaster.id') ?? data_get($data, 'exam.id') ?? request('exam_master_id') ?? 0
        );

        $columns = self::enrichColumnsWithExamMaster($columns, $examMasterId, $standardId);
        $passingPercentage = isset($data['passPercentage']) && $data['passPercentage'] !== null ? (float) $data['passPercentage'] : (float) \App\Helpers\MarksHelper::getPassingPercentage($standardId);
        $students = self::getUniqueSortedStudents($results);
        $calculations = self::buildStudentCalculations($students, $columns, $standardId, (int) $passingPercentage);

        $rawClassTeacher = self::extractStaffName($data['classTeacher'] ?? null);
        if ($rawClassTeacher === '' && isset($data['classTeacherName'])) $rawClassTeacher = trim((string) $data['classTeacherName']);

        $rawPrincipal = self::extractStaffName($data['principal'] ?? null);
        if ($rawPrincipal === '' && isset($data['principalName'])) $rawPrincipal = trim((string) $data['principalName']);

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
            'casteWiseAnalysis'        => self::calculateCasteWiseAnalysis($students, $calculations),
            'classTeacherName'         => self::formatStaffName($rawClassTeacher),
            'principalName'            => self::formatStaffName($rawPrincipal),
            'schoolCode'               => session('school_code', 'shirgaon'),
            'hasData'                  => $columns->count() > 0,
        ];
    }

    public static function preparePrintData(array $data): array { return self::buildSheetData($data); }

    public static function getSubjectHeader($column, int $standardId): array
    {
        return ['max' => (float) data_get($column, 'max_marks', 0), 'passing' => self::resolveSubjectPassing($column, $standardId), 'optional' => (int) data_get($column, 'is_optional', 0) === 1];
    }

    public static function displayResultMark($value, callable $displayNumber): string
    {
        $value = $value ?? '-'; $text = strtoupper(trim((string) $value));
        if ($text === 'AB' || $text === 'ABSENT') return 'AB';
        if ($text === 'OPT') return 'OPT';
        if ($value === '-') return '-';
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