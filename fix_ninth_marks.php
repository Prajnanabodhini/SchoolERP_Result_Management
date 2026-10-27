<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$START = microtime(true);
echo "\n========================================\n FIXING NINTH STANDARD MARKS (LIVE)\n========================================\n\n";

DB::beginTransaction();
try {
    $standard = DB::table('standards')->whereRaw('UPPER(TRIM(standard_name)) = ?', ['NINTH'])->first();
    
    if (!$standard) {
        echo "❌ NINTH standard not found!\n";
        return;
    }

    $subjectsToUpdate = ['MATHEMATICS I', 'MATHEMATICS II', 'SCIENCE I', 'SCIENCE II', 'HISTORY', 'GEOGRAPHY'];
    $subjectIds = DB::table('subjects')->whereIn('subject_name', $subjectsToUpdate)->pluck('id')->toArray();

    if (Schema::hasTable('exam_master_subjects')) {
        $updated = DB::table('exam_master_subjects')
            ->where('standard_id', $standard->id)
            ->whereIn('subject_id', $subjectIds)
            ->update(['max_marks' => 20, 'passing_marks' => 7]);
        echo "✅ Updated {$updated} records in 'exam_master_subjects'.\n";
    }

    if (Schema::hasTable('exam_subjects')) {
        $updated2 = DB::table('exam_subjects')
            ->where('standard_id', $standard->id)
            ->whereIn('subject_id', $subjectIds)
            ->update(['max_marks' => 20, 'passing_marks' => 7]);
        echo "✅ Updated {$updated2} records in 'exam_subjects'.\n";
    }

    if (Schema::hasTable('exam_structures')) {
        $updated3 = DB::table('exam_structures')
            ->whereRaw('UPPER(TRIM(standard_name)) = ?', ['NINTH'])
            ->whereIn('subject_name', $subjectsToUpdate)
            ->whereIn('exam_type', ['UNIT TEST 1', 'UNIT TEST 2'])
            ->update([
                'theory_max_marks' => 20,
                'theory_passing_marks' => 7,
                'total_max_marks' => 20,
                'total_passing_marks' => 7
            ]);
        echo "✅ Updated {$updated3} records in 'exam_structures'.\n";
    }

    DB::commit();
    echo "\n🎉 Done! Please refresh your Result Sheet page.\n";

} catch (\Throwable $e) {
    DB::rollBack();
    echo "\n❌ Error: " . $e->getMessage() . "\n";
}