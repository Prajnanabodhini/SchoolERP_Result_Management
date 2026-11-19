<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function pct35($max): float
    {
        $max = (float) $max;
        if ($max <= 0) return 0.0;
        return (float) (int) ceil(round($max * 0.35, 6));
    }

    public function up(): void
    {
        /* -------- 1. exam_structures -------- */
        DB::table('exam_structures')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $r) {
                $tP = $this->pct35((float) $r->theory_max_marks);
                $oP = $this->pct35((float) $r->oral_max_marks);
                $pP = $this->pct35((float) $r->practical_max_marks);
                $tp = $this->pct35((float) $r->total_max_marks);

                DB::table('exam_structures')->where('id', $r->id)->update([
                    'theory_passing_marks'    => $tP,
                    'oral_passing_marks'      => $oP,
                    'practical_passing_marks' => $pP,
                    'total_passing_marks'     => $tp,
                    'passing_percentage'      => 35,
                ]);
            }
        });

        /* -------- 2. exam_master_subjects (persisted snapshots) -------- */
        DB::table('exam_master_subjects')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $r) {
                $tP   = $this->pct35((float) $r->theory_max_marks);
                $oP   = $this->pct35((float) $r->oral_max_marks);
                $pP   = $this->pct35((float) $r->practical_max_marks);
                $tmax = (float) $r->total_max_marks;
                $mmax = (float) $r->max_marks;

                DB::table('exam_master_subjects')->where('id', $r->id)->update([
                    'theory_passing_marks'    => $tP,
                    'oral_passing_marks'      => $oP,
                    'practical_passing_marks' => $pP,
                    'passing_marks'           => $this->pct35($mmax > 0 ? $mmax : $tmax),
                    'total_passing_marks'     => $this->pct35($tmax > 0 ? $tmax : $mmax),
                ]);
            }
        });
    }

    public function down(): void
    {
        // No automatic rollback — restore from backup if needed.
    }
};