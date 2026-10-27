<?php

namespace App\Helpers;

use App\Models\ExamStructure;
use Illuminate\Support\Facades\DB;

/**
 * Exam-structure lookup.
 *
 * NO caching. Every call reads exam_structures directly, so changes made
 * via Exam Structure are reflected immediately in Exam Master / Marks Entry.
 *
 * Public API:
 *   getHardcodedExamStructure()
 *   getStandardSheetKey(string)
 *   findSheetForStandard(string)
 *   canonicalSubjectKey(string)
 *   matchExcelSubject(string, string, array)
 *   normalizeExamType($value)
 *   getPassingPercentage(int)
 *   getComponentValues(int, string, string)
 */
class ExamStructureHelper
{
    /* ================================================================
     | READ THE STRUCTURE — DB-backed, NO caching
     * ================================================================ */
    public static function getHardcodedExamStructure(): array
    {
        return self::buildStructureFromDb();
    }

    private static function buildStructureFromDb(): array
    {
        $examTypes = ['UNIT TEST 1', 'UNIT TEST 2', 'TERM 1', 'TERM 2'];

        $rows = ExamStructure::query()
            ->where('is_active', 1)
            ->orderBy('standard_key')
            ->orderBy('exam_type_order')
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();

        $standards = [];

        foreach ($rows as $r) {
            $std = strtoupper(trim((string) $r->standard_key));
            $et  = strtoupper(trim((string) $r->exam_type));

            if ($std === '' || $et === '') continue;

            if (!isset($standards[$std])) {
                $standards[$std] = [
                    'sheet'        => $std,
                    'exam_types'   => $examTypes,
                    'subjects'     => [],
                    'by_exam_type' => array_fill_keys($examTypes, []),
                ];
            }

            $row = [
                'subject_type'            => (string) $r->subject_type,
                'subject_name'            => (string) $r->subject_name,
                'subject_code'            => (string) ($r->subject_code ?? ''),
                'exam_type'               => $et,
                'theory_max_marks'        => (float) $r->theory_max_marks,
                'theory_passing_marks'    => (float) $r->theory_passing_marks,
                'oral_max_marks'          => (float) $r->oral_max_marks,
                'oral_passing_marks'      => (float) $r->oral_passing_marks,
                'practical_max_marks'     => (float) $r->practical_max_marks,
                'practical_passing_marks' => (float) $r->practical_passing_marks,
                'total_max_marks'         => (float) $r->total_max_marks,
                'total_passing_marks'     => (float) $r->total_passing_marks,
            ];

            $standards[$std]['subjects'][] = $row;

            if (!isset($standards[$std]['by_exam_type'][$et])) {
                $standards[$std]['by_exam_type'][$et] = [];
            }
            $standards[$std]['by_exam_type'][$et][] = $row;
        }

        return [
            'file'        => 'exam_structures (DB)',
            'source_hash' => 'db-v1',
            'standards'   => $standards,
            'exam_types'  => $examTypes,
        ];
    }

    /* ================================================================
     | STANDARD SHEET KEY
     * ================================================================ */
    public static function getStandardSheetKey(string $value): string
    {
        $v = strtoupper(preg_replace('/[^A-Z0-9]+/', '', trim($value)) ?? '');

        $canonical = ['NURSERY','JRKG','SRKG','1ST','2ND','3RD','4TH','5TH','6TH',
                      '7TH','8TH','9TH','10TH','11SCI','12SCI','11COM','12COM'];
        if (in_array($v, $canonical, true)) return $v;

        if (str_contains($v,'ELEVENTH') || str_starts_with($v,'XI') || $v === '11') {
            if (str_contains($v,'COMMERCE')) return '11COM';
            if (str_contains($v,'SCIENCE'))  return '11SCI';
        }
        if (str_contains($v,'TWELFTH') || str_starts_with($v,'XII') || $v === '12') {
            if (str_contains($v,'COMMERCE')) return '12COM';
            if (str_contains($v,'SCIENCE'))  return '12SCI';
        }

        if (str_contains($v,'NURSERY') || $v === 'NUR')                return 'NURSERY';
        if (str_contains($v,'JRKG')    || str_contains($v,'JUNIORKG')) return 'JRKG';
        if (str_contains($v,'SRKG')    || str_contains($v,'SENIORKG')) return 'SRKG';

        $words = ['FIRST'=>'1ST','SECOND'=>'2ND','THIRD'=>'3RD','FOURTH'=>'4TH',
                  'FIFTH'=>'5TH','SIXTH'=>'6TH','SEVENTH'=>'7TH','EIGHTH'=>'8TH',
                  'NINTH'=>'9TH','TENTH'=>'10TH'];
        if (isset($words[$v])) return $words[$v];

        $digits = ['1'=>'1ST','2'=>'2ND','3'=>'3RD','4'=>'4TH','5'=>'5TH',
                   '6'=>'6TH','7'=>'7TH','8'=>'8TH','9'=>'9TH','10'=>'10TH'];
        if (isset($digits[$v])) return $digits[$v];

        return $v;
    }

    public static function findSheetForStandard(string $standardName): ?array
    {
        $excel      = self::getHardcodedExamStructure();
        $desiredKey = self::getStandardSheetKey($standardName);

        foreach ($excel['standards'] as $sheetName => $sheet) {
            if (self::getStandardSheetKey($sheetName) === $desiredKey) return $sheet;
        }
        return null;
    }

    /* ================================================================
     | SUBJECT KEY CANONICALIZATION  (with aliases)
     * ================================================================ */
    public static function canonicalSubjectKey(string $value): string
    {
        $v = strtoupper(trim($value));

        /* Roman numerals → digits: I, II, III, IV, V */
        $v = preg_replace_callback(
            '/(?<![A-Z])(III|IV|II|V|I)(?![A-Z])/',
            fn ($m) => ['I'=>'1','II'=>'2','III'=>'3','IV'=>'4','V'=>'5'][$m[1]] ?? $m[1],
            $v
        );

        /* Strip everything except A-Z and 0-9 */
        $v = preg_replace('/[^A-Z0-9]+/', '', $v) ?? '';

        /* ⭐ Subject aliases — normalise shorthand spellings */
        $aliases = [
            /* General Knowledge */
            'GK'                    => 'GENERALKNOWLEDGE',
            'GKNOWLEDGE'            => 'GENERALKNOWLEDGE',
            'GENKNOWLEDGE'          => 'GENERALKNOWLEDGE',
            'GENKNOW'               => 'GENERALKNOWLEDGE',
            'GENERALKNOWLEDGE'      => 'GENERALKNOWLEDGE',
            'GENERAL'               => 'GENERALKNOWLEDGE',
            'GEN'                   => 'GENERALKNOWLEDGE',

            /* Mathematics */
            'MATHEMATICS'           => 'MATH',
            'MATHEMATIC'            => 'MATH',
            'MATHS'                 => 'MATH',
            'MATH'                  => 'MATH',

            /* Commerce */
            'ORGANIZATIONOFCOMMERCEANDMANAGEMENT' => 'OCM',
            'ORGANISATIONOFCOMMERCEANDMANAGEMENT' => 'OCM',
            'OCM'                   => 'OCM',
        ];

        return $aliases[$v] ?? $v;
    }

    public static function matchExcelSubject(string $dbName, string $examType, array $sheet): ?array
    {
        $rows = $sheet['by_exam_type'][$examType] ?? [];
        if (empty($rows)) return null;

        $dbKey = self::canonicalSubjectKey($dbName);

        /* 1) Exact canonical match */
        foreach ($rows as $r) {
            if (self::canonicalSubjectKey($r['subject_name']) === $dbKey) return $r;
        }

        /* 2) Partial contains match (>= 4 chars) */
        if (strlen($dbKey) >= 4) {
            foreach ($rows as $r) {
                $rKey = self::canonicalSubjectKey($r['subject_name']);
                if (strlen($rKey) >= 4
                    && (str_contains($rKey,$dbKey) || str_contains($dbKey,$rKey))) {
                    return $r;
                }
            }
        }

        return null;
    }

    /* ================================================================
     | EXAM TYPE
     * ================================================================ */
    public static function normalizeExamType($value): string
    {
        $v = strtoupper(trim((string) $value));
        if ($v === '') return '';

        foreach (['UNIT TEST 1','UNIT TEST 2','UNIT TEST 3','UNIT TEST 4'] as $t) {
            if (str_contains($v, $t)) return $t;
        }
        if (str_contains($v,'TERM 1')) return 'TERM 1';
        if (str_contains($v,'TERM 2')) return 'TERM 2';
        if (str_contains($v,'ANNUAL')) return 'ANNUAL';

        return $v;
    }

    /* ================================================================
     | PASSING PERCENTAGE
     * ================================================================ */
    public static function getPassingPercentage(int $standardId): float
    {
        if (in_array($standardId, [9,10,11,12], true)) return 35.0;

        $name = DB::table('standards')->where('id', $standardId)->value('standard_name');
        $n    = preg_replace('/[^A-Z0-9]+/','', strtoupper(trim((string) $name)));

        if (in_array($n, ['NURSERY','NUR','JRKG','JUNIORKG','SRKG','SENIORKG'], true)) return 35.0;
        if (in_array($n, ['NINTH','9TH','IX','TENTH','10TH','X'], true)) return 35.0;
        if (str_contains($n,'ELEVENTH') || str_contains($n,'TWELFTH'))  return 35.0;

        return 40.0;
    }

    /* ================================================================
     | MAIN LOOKUP — used by EditMarkHelper
     * ================================================================ */
    public static function getComponentValues(
        int $standardId,
        string $examName,
        string $subjectName
    ): array {

        $standardName = DB::table('standards')
            ->where('id', $standardId)
            ->value('standard_name');

        if (!$standardName) {
            return self::emptyComponentValues();
        }

        $sheet = self::findSheetForStandard($standardName);
        if (!$sheet) {
            return self::emptyComponentValues();
        }

        $examType = self::normalizeExamType($examName);
        if ($examType === '') {
            return self::emptyComponentValues();
        }

        $row = self::matchExcelSubject($subjectName, $examType, $sheet);
        if (!$row) {
            return self::emptyComponentValues();
        }

        return [
            'theory_max_marks'        => (float) ($row['theory_max_marks']        ?? 0),
            'theory_passing_marks'    => (float) ($row['theory_passing_marks']    ?? 0),
            'oral_max_marks'          => (float) ($row['oral_max_marks']          ?? 0),
            'oral_passing_marks'      => (float) ($row['oral_passing_marks']      ?? 0),
            'practical_max_marks'     => (float) ($row['practical_max_marks']     ?? 0),
            'practical_passing_marks' => (float) ($row['practical_passing_marks'] ?? 0),
            'total_max_marks'         => (float) ($row['total_max_marks']         ?? 0),
            'total_passing_marks'     => (float) ($row['total_passing_marks']     ?? 0),
        ];
    }

    private static function emptyComponentValues(): array
    {
        return [
            'theory_max_marks'        => 0.0,
            'theory_passing_marks'    => 0.0,
            'oral_max_marks'          => 0.0,
            'oral_passing_marks'      => 0.0,
            'practical_max_marks'     => 0.0,
            'practical_passing_marks' => 0.0,
            'total_max_marks'         => 0.0,
            'total_passing_marks'     => 0.0,
        ];
    }
}