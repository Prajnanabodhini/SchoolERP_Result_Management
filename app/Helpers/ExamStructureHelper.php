<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

/**
 * Shared lookup for the hardcoded exam structure.
 *
 * Used by:
 *   - ExamMasterController  → to build the Exam Master edit page
 *   - EditMarkHelper        → to derive theory/oral/practical values
 *                              for the Mark Entry page
 *
 * The structure itself is a PHP array — no DB table involved.
 */
class ExamStructureHelper
{
    /* ================================================================
     | HARDCODED STRUCTURE
     | Tuple: [tMax, tPass, oMax, oPass, pMax, pPass]
     | Total = tMax + oMax + pMax
     * ================================================================ */

    public static function getHardcodedExamStructure(): array
    {
        static $cache = null;
        if ($cache !== null) return $cache;

        $examTypes = ['UNIT TEST 1', 'UNIT TEST 2', 'TERM 1', 'TERM 2'];
        $data      = [];

        $push = function ($std, $et, $names, $tM, $tP, $oM, $oP, $pM, $pP) use (&$data) {
            foreach ((array) $names as $n) {
                $data[] = [$std, $et, $n, $tM, $tP, $oM, $oP, $pM, $pP];
            }
        };

        /* ---------- NURSERY ---------- */
        $nur = ['MARATHI','ENGLISH','GK','CONVERSATION','POEM','STORY'];
        foreach (['UNIT TEST 1','UNIT TEST 2','TERM 1'] as $et) {
            $push('NURSERY',$et,$nur, 0,0,25,10,0,0);
        }
        $push('NURSERY','TERM 2',$nur, 20,8,5,2,0,0);
        $nurSkill = ['CRAFT','COLOURING','CLAY','LIFESKILL','SONG','MARATHI STORY',
                     'INDOOR GAME','OUTDOOR GAME','TOY LIB','SHLOKA','T.SENSE'];
        $push('NURSERY','TERM 1',$nurSkill, 0,0,0,0,10,4);
        $push('NURSERY','TERM 2',$nurSkill, 0,0,0,0,10,4);

        /* ---------- JRKG ---------- */
        foreach ($examTypes as $et) {
            $push('JRKG',$et,['MARATHI'],       20,8,5,2,0,0);
            $push('JRKG',$et,['ENGLISH'],        0,0,10,4,0,0);
            $push('JRKG',$et,['MATHEMATICS'],   20,8,5,2,0,0);
            $push('JRKG',$et,['GK','CONVERSATION','POEM','STORY'], 0,0,25,10,0,0);
        }
        $jrSkill = ['CRAFT','COLOURING','CLAY','LIFESKILL','SONG','MARATHI STORY',
                    'INDOOR GAME','OUTDOOR GAME','TOY LIB','SHLOKA','T.SENSE'];
        $push('JRKG','TERM 1',$jrSkill, 0,0,0,0,10,4);
        $push('JRKG','TERM 2',$jrSkill, 0,0,0,0,10,4);

        /* ---------- SRKG ---------- */
        foreach ($examTypes as $et) {
            $push('SRKG',$et,['MARATHI','ENGLISH','MATHEMATICS'], 20,8,5,2,0,0);
            $push('SRKG',$et,['GK','CONVERSATION','POEM','STORY'], 0,0,25,10,0,0);
        }
        $srSkill = ['CRAFT','DRAWING','CLAY','LIFESKILL','SONG','MARATHI STORY',
                    'INDOOR GAME','OUTDOOR GAME','TOY LIB','SHLOKA','T.SENSE'];
        $push('SRKG','TERM 1',$srSkill, 0,0,0,0,10,4);
        $push('SRKG','TERM 2',$srSkill, 0,0,0,0,10,4);

        /* ---------- 1ST / 2ND ---------- */
        foreach (['1ST','2ND'] as $std) {
            $acad = ['MARATHI','ENGLISH','MATHEMATICS','EVS'];
            foreach (['UNIT TEST 1','UNIT TEST 2'] as $et) $push($std,$et,$acad, 20,8,0,0,0,0);
            foreach (['TERM 1','TERM 2'] as $et)             $push($std,$et,$acad, 20,8,10,4,70,28);
            foreach (['TERM 1','TERM 2'] as $et)             $push($std,$et,['PT','Art','WE'], 0,0,0,0,100,40);
        }

        /* ---------- 3RD / 4TH ---------- */
        foreach (['3RD','4TH'] as $std) {
            $acad = ['MARATHI','ENGLISH','MATHEMATICS','EVS1','EVS2'];
            foreach (['UNIT TEST 1','UNIT TEST 2'] as $et) $push($std,$et,$acad, 20,8,0,0,0,0);
            foreach (['TERM 1','TERM 2'] as $et)             $push($std,$et,$acad, 30,12,10,4,60,24);
            foreach (['TERM 1','TERM 2'] as $et)             $push($std,$et,['PT','Art','WE'], 0,0,0,0,100,40);
        }

        /* ---------- 5TH ---------- */
        $a5 = ['ENGLISH','HINDI','MARATHI','MATHEMATICS'];
        $b5 = ['EVS1','EVS2'];
        foreach (['UNIT TEST 1','UNIT TEST 2'] as $et) {
            $push('5TH',$et,$a5, 40,16,0,0,0,0);
            $push('5TH',$et,$b5, 20,8,0,0,0,0);
        }
        foreach (['TERM 1','TERM 2'] as $et) {
            $push('5TH',$et,$a5, 40,16,10,4,50,20);
            $push('5TH',$et,$b5, 20,8,5,2,25,10);
            $push('5TH',$et,['PT','Art','WE'], 0,0,0,0,100,40);
        }

        /* ---------- 6TH ---------- */
        foreach (['UNIT TEST 1','UNIT TEST 2'] as $et) {
            $push('6TH',$et,$a5, 40,16,0,0,0,0);
            $push('6TH',$et,['SCIENCE'], 40,16,0,0,0,0);
            $push('6TH',$et,['HISTORY','GEOGRAPHY'], 20,8,0,0,0,0);
        }
        foreach (['TERM 1','TERM 2'] as $et) {
            $push('6TH',$et,$a5, 40,16,10,4,50,20);
            $push('6TH',$et,['SCIENCE'], 20,8,10,4,50,20);
            $push('6TH',$et,['HISTORY','GEOGRAPHY'], 20,8,10,4,25,10);
            $push('6TH',$et,['PT','Art','WE'], 0,0,0,0,100,40);
        }

        /* ---------- 7TH ---------- */
        $a7 = ['ENGLISH','HINDI','MARATHI','MATHEMATICS','SCIENCE'];
        foreach (['UNIT TEST 1','UNIT TEST 2'] as $et) {
            $push('7TH',$et,$a7, 40,16,0,0,0,0);
            $push('7TH',$et,['HISTORY','GEOGRAPHY'], 20,8,0,0,0,0);
        }
        foreach (['TERM 1','TERM 2'] as $et) {
            $push('7TH',$et,$a7, 50,20,10,4,40,16);
            $push('7TH',$et,['HISTORY','GEOGRAPHY'], 25,10,5,2,20,8);
            $push('7TH',$et,['PT','Art','WE'], 0,0,0,0,100,40);
        }

        /* ---------- 8TH ---------- */
        $a8 = ['ENGLISH','SANSKRIT','MARATHI','MATHEMATICS','SCIENCE'];
        foreach (['UNIT TEST 1','UNIT TEST 2'] as $et) {
            $push('8TH',$et,$a8, 40,16,0,0,0,0);
            $push('8TH',$et,['HISTORY','GEOGRAPHY'], 20,8,0,0,0,0);
        }
        foreach (['TERM 1','TERM 2'] as $et) {
            $push('8TH',$et,$a8, 50,20,10,4,40,16);
            $push('8TH',$et,['HISTORY','GEOGRAPHY'], 25,10,5,2,20,8);
        }
        $push('8TH','TERM 1',['PT','Art','WE'], 0,0,0,0,100,40);

        /* ---------- 9TH ---------- */
        foreach (['UNIT TEST 1','UNIT TEST 2'] as $et) {
            $push('9TH',$et,['ENGLISH','SANSKRIT','MARATHI'], 40,14,0,0,0,0);
            $push('9TH',$et,['MATHEMATICS I','MATHEMATICS II','SCIENCE I','SCIENCE II'], 20,7,0,0,0,0);
            $push('9TH',$et,['HISTORY','GEOGRAPHY'], 20,7,0,0,0,0);
        }
        foreach (['TERM 1','TERM 2'] as $et) {
            $push('9TH',$et,['ENGLISH','SANSKRIT','MARATHI'], 80,28,20,7,0,0);
            $push('9TH',$et,['MATHEMATICS I','MATHEMATICS II','SCIENCE I','SCIENCE II'], 40,14,0,0,10,4);
            $push('9TH',$et,['HISTORY','GEOGRAPHY'], 40,14,10,4,0,0);
        }

        /* ---------- 10TH ---------- */
        foreach (['UNIT TEST 1','UNIT TEST 2'] as $et) {
            $push('10TH',$et,['ENGLISH','SANSKRIT','MARATHI'], 40,14,0,0,0,0);
            $push('10TH',$et,['MATHEMATICS I','MATHEMATICS II','SCIENCE I','SCIENCE II','HISTORY','GEOGRAPHY'], 20,7,0,0,0,0);
        }
        foreach (['TERM 1','TERM 2'] as $et) {
            $push('10TH',$et,['ENGLISH','SANSKRIT','MARATHI'], 80,28,20,7,0,0);
            $push('10TH',$et,['MATHEMATICS I','MATHEMATICS II','SCIENCE I','SCIENCE II','HISTORY','GEOGRAPHY'], 40,14,10,4,0,0);
        }

        /* ---------- 11SCI / 12SCI ---------- */
        foreach (['11SCI','12SCI'] as $std) {
            foreach (['UNIT TEST 1','UNIT TEST 2'] as $et) {
                $push($std,$et,['MARATHI','ENGLISH','MATHEMATICS','GEOGRAPHY'], 25,8,0,0,0,0);
                $push($std,$et,['PHYSICS','CHEMISTRY','BIOLOGY'], 25,8,0,0,0,0);
                $push($std,$et,['INFORMATION TECHNOLOGY'], 25,8,0,0,0,0);
            }
            foreach (['TERM 1','TERM 2'] as $et) {
                $push($std,$et,['MARATHI','ENGLISH','MATHEMATICS','GEOGRAPHY'], 80,28,20,7,0,0);
                $push($std,$et,['PHYSICS','CHEMISTRY','BIOLOGY'], 70,25,0,0,30,11);
                $push($std,$et,['INFORMATION TECHNOLOGY'], 50,18,0,0,50,17);
            }
        }

        /* ---------- 11COM / 12COM ---------- */
        $com = ['MARATHI','ENGLISH','ECONOMICS','ACCOUNTS',
                'ORGANIZATION OF COMMERCE AND MANAGEMENT','SECRETARIAL PRACTICE'];
        foreach (['11COM','12COM'] as $std) {
            foreach (['UNIT TEST 1','UNIT TEST 2'] as $et) $push($std,$et,$com, 25,8,0,0,0,0);
            foreach (['TERM 1','TERM 2'] as $et)             $push($std,$et,$com, 80,28,20,7,0,0);
        }

        /* ---------- GROUP ---------- */
        $standards = [];
        foreach ($data as $r) {
            [$std,$et,$name,$tM,$tP,$oM,$oP,$pM,$pP] = $r;

            if (!isset($standards[$std])) {
                $standards[$std] = [
                    'sheet'        => $std,
                    'exam_types'   => $examTypes,
                    'subjects'     => [],
                    'by_exam_type' => array_fill_keys($examTypes, []),
                ];
            }

            $row = [
                'subject_type'            => 'Academics',
                'subject_name'            => $name,
                'exam_type'               => $et,
                'theory_max_marks'        => (float) $tM,
                'theory_passing_marks'    => (float) $tP,
                'oral_max_marks'          => (float) $oM,
                'oral_passing_marks'      => (float) $oP,
                'practical_max_marks'     => (float) $pM,
                'practical_passing_marks' => (float) $pP,
                'total_max_marks'         => (float) ($tM + $oM + $pM),
                'total_passing_marks'     => (float) ($tP + $oP + $pP),
            ];
            $standards[$std]['subjects'][]          = $row;
            $standards[$std]['by_exam_type'][$et][] = $row;
        }

        return $cache = [
            'file'        => 'Academic Exam Structure - Chikhali 2026-27',
            'source_hash' => 'hardcoded-v6',
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

        if (str_contains($v,'NURSERY') || $v === 'NUR')               return 'NURSERY';
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
     | SUBJECT KEY CANONICALIZATION
     * ================================================================ */
    public static function canonicalSubjectKey(string $value): string
    {
        $v = strtoupper(trim($value));

        $v = preg_replace_callback(
            '/(?<![A-Z])(III|IV|II|V|I)(?![A-Z])/',
            fn ($m) => ['I'=>'1','II'=>'2','III'=>'3','IV'=>'4','V'=>'5'][$m[1]] ?? $m[1],
            $v
        );

        $v = preg_replace('/[^A-Z0-9]+/', '', $v) ?? '';

        $v = str_replace(['MATHEMATICS','MATHEMATIC','MATHS'], 'MATH', $v);
        $v = str_replace('ORGANIZATIONOFCOMMERCEANDMANAGEMENT', 'OCM', $v);

        return $v;
    }

    public static function matchExcelSubject(string $dbName, string $examType, array $sheet): ?array
    {
        $rows = $sheet['by_exam_type'][$examType] ?? [];
        if (empty($rows)) return null;

        $dbKey = self::canonicalSubjectKey($dbName);

        foreach ($rows as $r) {
            if (self::canonicalSubjectKey($r['subject_name']) === $dbKey) return $r;
        }

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
     |
     | Returns the correct theory/oral/practical values for a
     | (standard, exam, subject) tuple, straight from the hardcoded
     | structure the Exam Master edit page uses.
     | ================================================================ */
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