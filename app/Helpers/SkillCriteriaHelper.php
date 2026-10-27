<?php

namespace App\Helpers;

use App\Models\Standard;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SkillCriteriaHelper
{
    /* =====================================================================
     | CRITERIA MAP
     | ===================================================================== */

    protected static array $criteria = [
        'SANSKRIT' => ['Pronunciation', 'Recitation (Stotra)', 'Concept Clearance', 'Writing', 'Marks', 'Remark'],
        'DANCE'    => ['Attentiveness', 'Learning', 'Presentation', 'Grace', 'Marks', 'Remark'],
        'MUSIC'    => ['Attentiveness', 'Learning', 'Presentation', 'Voice Quality', 'Marks', 'Remark'],
        'ACTING'   => ['Attentiveness', 'Learning', 'Presentation', 'Improvisation', 'Marks', 'Remark'],
        'TABALA'   => ['Attentiveness', 'Learning', 'Presentation', 'Accompanying Singer', 'Marks', 'Remark'],
        'LIBRARY'  => ['Reading', 'Meaningful Learning', 'Vocabulary', 'Ability to Express', 'Library Notes', 'Remark'],
        'ABACUS'   => ['Learning', 'Book Completion', 'Accuracy', 'Speed', 'Marks', 'Remark'],
        'VEDIC'    => ['Learning', 'Book Completion', 'Accuracy', 'Speed', 'Marks', 'Remark'],
        'COMPUTER' => ['Learning', 'C.W. (Activity Book)', 'Practical Marks', 'Innovative Thinking', 'Remark'],
        'ROBOTICS' => ['Learning', 'C.W. (Activity Book)', 'Practical Marks', 'Innovative Thinking', 'Written Marks', 'Remark'],
    ];

    public static function getCriteria(?string $subjectName): array
    {
        if (!$subjectName) return ['Marks', 'Remark'];

        $subjectName = strtoupper(trim($subjectName));

        foreach (self::$criteria as $key => $cols) {
            if (str_contains($subjectName, $key)) return $cols;
        }

        return ['Attentiveness', 'Learning', 'Presentation', 'Marks', 'Remark'];
    }

    public static function getCriterionType(string $label): string
    {
        $l = strtolower($label);
        if (str_contains($l, 'remark')) return 'text';
        if (str_contains($l, 'marks')) return 'number';
        return 'grade';
    }

    public static function gradeScale(): array
    {
        return ['C', 'B', 'B+', 'A', 'A+'];
    }

    public static function gradeLabels(): array
    {
        return [
            'C'  => 'Very Poor',
            'B'  => 'Poor',
            'B+' => 'Average',
            'A'  => 'Good',
            'A+' => 'Excellent',
        ];
    }

    public static function gradeNumericMap(): array
    {
        return ['C' => 1, 'B' => 2, 'B+' => 3, 'A' => 4, 'A+' => 5];
    }

    public static function computeAverageGrade(array $observations): ?string
    {
        $map = self::gradeNumericMap();
        $sum = 0;
        $count = 0;

        foreach ($observations as $key => $val) {
            $g = strtoupper(trim((string) $val));
            if (isset($map[$g])) {
                $sum += $map[$g];
                $count++;
            }
        }

        if ($count === 0) return null;

        $avg = $sum / $count;

        if ($avg >= 4.5) return 'A+';
        if ($avg >= 3.5) return 'A';
        if ($avg >= 2.5) return 'B+';
        if ($avg >= 1.5) return 'B';
        return 'C';
    }

    /* =====================================================================
     | SKILL SUBJECTS BY STANDARD
     | =====================================================================
     |
     | Standard groups:
     |   • Nursery / Jr KG / Sr KG  → craft, drama, life skill, clay, song,
     |                                 story, indoor/outdoor game, books, toy
     |   • 1st – 2nd                → song, story, dance, sanskrit bharti,
     |                                 library, ABACUS
     |   • 3rd – 8th                → tabla, music, dance, acting, sanskrit
     |                                 bharti, computer, robotics, ABACUS
     |   • 9th                     → tabla, music, dance, acting, sanskrit
     |                                 bharti, computer, robotics, VEDIC MATHS
     |   • 10th                    → computer, robotics, VEDIC MATHS
     |
     */

    public static function getSkillSubjectsByStandard(): array
    {
        try {
            $allSubjects = self::fetchAllSkillSubjects();

            if ($allSubjects->isEmpty()) {
                return [];
            }

            $result = [];

            $standards = Standard::where('is_active', 1)
                ->orderBy('display_order')
                ->get(['id', 'standard_name']);

            foreach ($standards as $std) {
                $filtered = self::filterForStandard((int) $std->id, $allSubjects);

                $list = $filtered->isNotEmpty() ? $filtered : $allSubjects;

                $result[(string) $std->id] = $list->map(fn ($s) => [
                    'id'           => (int) $s->id,
                    'subject_name' => (string) $s->subject_name,
                ])->values()->all();
            }

            return $result;

        } catch (\Throwable $e) {
            Log::error('SkillCriteriaHelper::getSkillSubjectsByStandard failed', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    /* =====================================================================
     | INTERNAL: FETCH ALL SKILL SUBJECTS
     | ===================================================================== */

    private static function fetchAllSkillSubjects(): \Illuminate\Support\Collection
    {
        $rows = Subject::whereIn('subject_type_id', [2, 3])
            ->where('is_active', 1)
            ->orderBy('id')
            ->get(['id', 'subject_name']);

        $unique = [];

        foreach ($rows as $r) {
            $name = strtoupper(trim((string) $r->subject_name));
            if ($name === '') continue;

            if (!isset($unique[$name])) {
                $unique[$name] = [
                    'id'           => (int) $r->id,
                    'subject_name' => (string) $r->subject_name,
                ];
            }
        }

        $list = array_values($unique);
        usort($list, fn ($a, $b) => strcmp($a['subject_name'], $b['subject_name']));

        return collect($list)->map(fn ($r) => (object) $r);
    }

    /* =====================================================================
     | INTERNAL: FILTER BY STANDARD NAME
     | ===================================================================== */

    private static function filterForStandard(int $standardId, \Illuminate\Support\Collection $allSubjects): \Illuminate\Support\Collection
    {
        $standard = Standard::find($standardId);
        if (!$standard) return collect();

        $stdNameLower = strtolower(trim((string) $standard->standard_name));
        $targetNames  = [];

        if (preg_match('/(nursery|jr kg|sr kg|jrkg|srkg|pre-primary)/', $stdNameLower)) {

            $targetNames = [
                'CRAFT', 'DRAMA', 'LIFE SKILL', 'CLAY', 'SONG', 'STORY',
                'INDOOR GAME', 'OUTDOOR GAME', 'BOOKS', 'TOY',
            ];

        } elseif (preg_match('/(first|second|\b1\b|\b2\b)/', $stdNameLower)) {

            // 1st – 2nd
            $targetNames = [
                'SONG', 'STORY', 'DANCE', 'SANSKRIT BHARTI', 'LIBRARY', 'ABACUS',
            ];

        } elseif (preg_match('/(third|fourth|fifth|sixth|seventh|eighth|\b[3-8]\b)/', $stdNameLower)) {

            // 3rd – 8th  → ABACUS
            $targetNames = [
                'TABLA', 'MUSIC', 'DANCE', 'ACTING', 'SANSKRIT BHARTI',
                'COMPUTER', 'ROBOTICS', 'ABACUS',
            ];

        } elseif (preg_match('/(ninth|\b9\b)/', $stdNameLower)) {

            // 9th  → VEDIC MATHS
            $targetNames = [
                'TABLA', 'MUSIC', 'DANCE', 'ACTING', 'SANSKRIT BHARTI',
                'COMPUTER', 'ROBOTICS', 'VEDIC MATHS',
            ];

        } elseif (preg_match('/(tenth|\b10\b)/', $stdNameLower)) {

            // 10th  → VEDIC MATHS
            $targetNames = [
                'COMPUTER', 'ROBOTICS', 'VEDIC MATHS',
            ];

        } else {
            return $allSubjects;
        }

        $filtered = collect();

        foreach ($targetNames as $target) {
            $found = null;

            foreach ($allSubjects as $sub) {
                $sName = strtoupper(trim((string) $sub->subject_name));
                $match = false;

                if ($target === 'SANSKRIT BHARTI') {
                    // ⭐ Matches SANSKRIT BHARTI, SANSKRIT BHARATI, or SANSKRIT BOOK
                    $match = str_contains($sName, 'SANSKRIT') || str_contains($sName, 'BHARTI') || str_contains($sName, 'BHARATI') || str_contains($sName, 'BOOK');
                } elseif ($target === 'TABLA') {
                    $match = str_contains($sName, 'TABLA') || str_contains($sName, 'TABALA');
                } elseif ($target === 'MUSIC') {
                    $match = str_contains($sName, 'MUSIC');
                } elseif ($target === 'SONG') {
                    $match = str_contains($sName, 'SONG');
                } elseif ($target === 'ACTING') {
                    $match = str_contains($sName, 'ACTING') || str_contains($sName, 'DRAMA');
                } elseif ($target === 'BOOKS') {
                    $match = str_contains($sName, 'BOOK');
                } elseif ($target === 'INDOOR GAME') {
                    $match = str_contains($sName, 'INDOOR GAME');
                } elseif ($target === 'OUTDOOR GAME') {
                    $match = str_contains($sName, 'OUTDOOR GAME');
                } else {
                    $match = $sName === $target || str_contains($sName, $target);
                }

                if ($match) {
                    $found = clone $sub;
                    // ⭐ Force the display name to the standardized spelling
                    $found->subject_name = $target; 
                    break;
                }
            }

            if ($found) {
                $filtered->push($found);
            }
        }

        return $filtered->unique('subject_name')->values();
    }
}