<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Step 1 — Find and drop whatever single-subject unique index exists.
        | We can't rely on Laravel's default name because the index may have been
        | named differently in your DB. We detect it dynamically.
        |--------------------------------------------------------------------------
        */

        $indexes = DB::select("SHOW INDEX FROM student_skill_subjects");

        $dropNames = [];

        foreach ($indexes as $idx) {

            // Skip the primary key
            if ($idx->Key_name === 'PRIMARY') continue;

            // We only care about UNIQUE indexes
            if ((int) $idx->Non_unique !== 0) continue;

            $columns = collect($indexes)
                ->where('Key_name', $idx->Key_name)
                ->pluck('Column_name')
                ->sort()
                ->values()
                ->toArray();

            // The old unique was on (academic_year_id, student_id)
            if (
                in_array('academic_year_id', $columns, true) &&
                in_array('student_id', $columns, true) &&
                !in_array('subject_id', $columns, true)
            ) {
                $dropNames[$idx->Key_name] = true;
            }
        }

        foreach (array_keys($dropNames) as $indexName) {
            DB::statement("ALTER TABLE student_skill_subjects DROP INDEX `{$indexName}`");
        }

        /*
        |--------------------------------------------------------------------------
        | Step 2 — Add the new composite unique.
        | A student may hold the same subject only once per academic year.
        |--------------------------------------------------------------------------
        */

        Schema::table('student_skill_subjects', function (Blueprint $table) {
            $table->unique(
                ['academic_year_id', 'student_id', 'subject_id'],
                'sss_year_student_subject_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('student_skill_subjects', function (Blueprint $table) {
            $table->dropUnique('sss_year_student_subject_unique');
            $table->unique(['academic_year_id', 'student_id']);
        });
    }
};