<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for resolving which component columns
 * (Theory / Oral / Practical) should be shown on Marks Entry / View Marks.
 *
 * Rule: a component column is shown ONLY when its *_max_marks > 0 in
 *       the exam_master_subjects row.
 */
class ExamSubjectConfigHelper
{
    /**
     * Resolve the exam_master_subjects row for a given exam + standard + subject.
     * Handles both modern subjects.id and legacy standard_wise_subjects.id.
     */
    public static function resolveExamSubjectConfig(int $examId, int $standardId, $subjectIds): ?object
    {
        if ($examId <= 0 || $standardId <= 0) return null;

        $subjectIds = is_array($subjectIds) ? $subjectIds : [$subjectIds];
        $subjectIds = array_values(array_unique(array_filter(array_map('intval', $subjectIds))));

        if (empty($subjectIds)) return null;

        /* ---------- 1) Direct match on subjects.id ---------- */
        $config = DB::table('exam_master_subjects')
            ->where('exam_master_id', $examId)
            ->where('standard_id', $standardId)
            ->whereIn('subject_id', $subjectIds)
            ->orderByDesc('id')
            ->first();

        if ($config) return $config;

        /* ---------- 2) Legacy / SWS fallback ---------- */
        $mappings = DB::table('standard_wise_subjects')
            ->where('standard_id', $standardId)
            ->where(function ($q) use ($subjectIds) {
                $q->whereIn('id', $subjectIds)
                  ->orWhereIn('subject_id', $subjectIds);
            })
            ->where('is_active', 1)
            ->get();

        $candidates = $subjectIds;
        foreach ($mappings as $m) {
            if (!empty($m->id))         $candidates[] = (int) $m->id;
            if (!empty($m->subject_id)) $candidates[] = (int) $m->subject_id;
        }
        $candidates = array_values(array_unique(array_filter($candidates)));

        return DB::table('exam_master_subjects')
            ->where('exam_master_id', $examId)
            ->where('standard_id', $standardId)
            ->whereIn('subject_id', $candidates)
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Convert an exam_master_subjects row into the exact flags the Blade needs.
     */
    public static function extractComponentFlags(?object $cfg): array
    {
        if (!$cfg) {
            return [
                'showTheory'            => false,
                'showOral'              => false,
                'showPractical'         => false,
                'theoryMaxMarks'        => 0.0,
                'theoryPassingMarks'    => 0.0,
                'oralMaxMarks'          => 0.0,
                'oralPassingMarks'      => 0.0,
                'practicalMaxMarks'     => 0.0,
                'practicalPassingMarks' => 0.0,
                'totalMaxMarks'         => 0.0,
                'totalPassingMarks'     => 0.0,
            ];
        }

        $tMax  = (float) ($cfg->theory_max_marks        ?? 0);
        $tPass = (float) ($cfg->theory_passing_marks    ?? 0);
        $oMax  = (float) ($cfg->oral_max_marks          ?? 0);
        $oPass = (float) ($cfg->oral_passing_marks      ?? 0);
        $pMax  = (float) ($cfg->practical_max_marks     ?? 0);
        $pPass = (float) ($cfg->practical_passing_marks ?? 0);

        /* ⭐ THE RULE: hide a component when its max is 0 */
        $showTheory    = $tMax > 0;
        $showOral      = $oMax > 0;
        $showPractical = $pMax > 0;

        /* Total — prefer explicit total_max_marks, otherwise sum of visible components */
        $totalMax  = (float) ($cfg->total_max_marks     ?? 0);
        $totalPass = (float) ($cfg->total_passing_marks ?? 0);

        if ($totalMax <= 0) {
            $totalMax = ($showTheory    ? $tMax : 0)
                      + ($showOral      ? $oMax : 0)
                      + ($showPractical ? $pMax : 0);
        }
        if ($totalPass <= 0) {
            $totalPass = ($showTheory    ? $tPass : 0)
                       + ($showOral      ? $oPass : 0)
                       + ($showPractical ? $pPass : 0);
        }

        return [
            'showTheory'            => $showTheory,
            'showOral'              => $showOral,
            'showPractical'         => $showPractical,
            'theoryMaxMarks'        => $tMax,
            'theoryPassingMarks'    => $tPass,
            'oralMaxMarks'          => $oMax,
            'oralPassingMarks'      => $oPass,
            'practicalMaxMarks'     => $pMax,
            'practicalPassingMarks' => $pPass,
            'totalMaxMarks'         => $totalMax,
            'totalPassingMarks'     => $totalPass,
        ];
    }
}