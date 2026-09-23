<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Helpers\ExamStructureHelper;

class FixStaleMaxMarks extends Command
{
    protected $signature = 'marks:fix-max-marks
                            {--exam-id= : Limit to this exam_master_id}
                            {--standard-id= : Limit to this standard_id}
                            {--dry-run : Report changes without saving}';

    protected $description = 'Sync stale theory/oral/practical max + passing marks on student_marks from ExamStructureHelper.';

    public function handle(): int
    {
        $dryRun     = (bool) $this->option('dry-run');
        $examId     = $this->option('exam-id');
        $standardId = $this->option('standard-id');

        $examMap    = DB::table('exam_masters')->pluck('exam_name', 'id')->toArray();
        $subjectMap = DB::table('subjects')->pluck('subject_name', 'id')->toArray();

        $query = DB::table('student_marks')
            ->select([
                'id','standard_id','exam_master_id','subject_id',
                'theory_max_marks','oral_max_marks','practical_max_marks',
            ]);

        if ($examId)     $query->where('exam_master_id', (int) $examId);
        if ($standardId) $query->where('standard_id',    (int) $standardId);

        $total   = (clone $query)->count();
        $updated = 0;
        $skipped = 0;

        $this->info("Processing {$total} student_marks rows…");

        // Cache sheet + exam type per standard to avoid repeated work
        $sheetCache = [];

        $query->orderBy('id')->chunk(500, function ($rows) use (
            $examMap, $subjectMap, $dryRun,
            &$updated, &$skipped, &$sheetCache
        ) {
            foreach ($rows as $r) {

                $examName    = $examMap[$r->exam_master_id] ?? '';
                $subjectName = $subjectMap[$r->subject_id]  ?? '';

                if ($examName === '' || $subjectName === '') {
                    $skipped++;
                    continue;
                }

                /* ---------------------------------------------------------
                 | STRICT CANONICAL MATCH
                 |
                 | We must NOT trust ExamStructureHelper's partial-match
                 | fallback here (STORY would falsely match HISTORY).
                 | Instead we look up the sheet + exam type ourselves and
                 | require an EXACT canonical-name match.
                 --------------------------------------------------------- */

                $stdId = (int) $r->standard_id;

                if (!isset($sheetCache[$stdId])) {
                    $stdName = DB::table('standards')->where('id', $stdId)->value('standard_name') ?? '';
                    $sheetCache[$stdId] = [
                        'standard_name' => $stdName,
                        'sheet'         => $stdName ? ExamStructureHelper::findSheetForStandard($stdName) : null,
                    ];
                }

                $sheet    = $sheetCache[$stdId]['sheet'];
                $examType = ExamStructureHelper::normalizeExamType($examName);

                if (!$sheet || $examType === '') {
                    $skipped++;
                    continue;
                }

                $expectedCanon = ExamStructureHelper::canonicalSubjectKey($subjectName);

                $excel = null;
                foreach (($sheet['by_exam_type'][$examType] ?? []) as $candidate) {
                    if (ExamStructureHelper::canonicalSubjectKey($candidate['subject_name']) === $expectedCanon) {
                        $excel = $candidate;
                        break;
                    }
                }

                if (!$excel) {
                    $skipped++;
                    continue;
                }

                $tMax = (float) ($excel['theory_max_marks']    ?? 0);
                $tPass= (float) ($excel['theory_passing_marks']?? 0);
                $oMax = (float) ($excel['oral_max_marks']      ?? 0);
                $oPass= (float) ($excel['oral_passing_marks']  ?? 0);
                $pMax = (float) ($excel['practical_max_marks'] ?? 0);
                $pPass= (float) ($excel['practical_passing_marks'] ?? 0);

                if ($tMax <= 0 && $oMax <= 0 && $pMax <= 0) {
                    $skipped++;
                    continue;
                }

                // Skip rows already correct
                if (
                    abs((float) $r->theory_max_marks    - $tMax) < 0.01 &&
                    abs((float) $r->oral_max_marks      - $oMax) < 0.01 &&
                    abs((float) $r->practical_max_marks - $pMax) < 0.01
                ) {
                    continue;
                }

                if ($dryRun) {
                    $this->line(sprintf(
                        '  [DRY] id=%d  %s / %s   %s → %s',
                        $r->id,
                        $examName,
                        $subjectName,
                        (float) $r->theory_max_marks,
                        $tMax
                    ));
                    $updated++;
                    continue;
                }

                DB::table('student_marks')
                    ->where('id', $r->id)
                    ->update([
                        'theory_max_marks'        => $tMax,
                        'theory_passing_marks'    => $tPass,
                        'oral_max_marks'          => $oMax,
                        'oral_passing_marks'      => $oPass,
                        'practical_max_marks'     => $pMax,
                        'practical_passing_marks' => $pPass,
                        'updated_at'              => now(),
                    ]);

                $updated++;
            }
        });

        $this->newLine();
        $this->info($dryRun
            ? "DRY RUN: {$updated} rows WOULD be updated, {$skipped} skipped."
            : "DONE: {$updated} rows updated, {$skipped} skipped.");

        if ($dryRun) {
            $this->warn('No changes were written. Re-run without --dry-run to apply.');
        }

        return self::SUCCESS;
    }
}