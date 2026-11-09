<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add subject_id to exam_structures
        if (!Schema::hasColumn('exam_structures', 'subject_id')) {
            Schema::table('exam_structures', function (Blueprint $table) {
                $table->unsignedBigInteger('subject_id')->nullable()->after('subject_name');
            });
        }

        // Backfill existing data (Handling collation mismatch)
        DB::statement("
            UPDATE exam_structures es
            JOIN subjects s ON CONVERT(s.subject_name USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(es.subject_name USING utf8mb4) COLLATE utf8mb4_unicode_ci
            SET es.subject_id = s.id
            WHERE es.subject_id IS NULL
        ");

        // 2. Add subject_id to student_skill_marks
        if (!Schema::hasColumn('student_skill_marks', 'subject_id')) {
            Schema::table('student_skill_marks', function (Blueprint $table) {
                $table->unsignedBigInteger('subject_id')->nullable()->after('subject_name');
            });
        }

        // Backfill existing data (Handling collation mismatch)
        DB::statement("
            UPDATE student_skill_marks ssm
            JOIN subjects s ON CONVERT(s.subject_name USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(ssm.subject_name USING utf8mb4) COLLATE utf8mb4_unicode_ci
            SET ssm.subject_id = s.id
            WHERE ssm.subject_id IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('exam_structures', function (Blueprint $table) {
            $table->dropColumn('subject_id');
        });
        Schema::table('student_skill_marks', function (Blueprint $table) {
            $table->dropColumn('subject_id');
        });
    }
};