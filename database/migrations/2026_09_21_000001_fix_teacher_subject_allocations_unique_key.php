<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop the old unique key
        try {
            Schema::table('teacher_subject_allocations', function (Blueprint $table) {
                $table->dropUnique('uk_teacher_subject');
            });
        } catch (\Throwable $e) {
            // Ignore if it doesn't exist
        }

        // 2. Clean up any duplicate rows on the NEW key (safety net)
        DB::statement('
            DELETE t1 FROM teacher_subject_allocations t1
            INNER JOIN teacher_subject_allocations t2
            WHERE t1.id < t2.id
              AND t1.teacher_class_allocation_id = t2.teacher_class_allocation_id
              AND t1.subject_id = t2.subject_id
              AND t1.exam_master_id = t2.exam_master_id
        ');

        // 3. Add new unique key that INCLUDES exam_master_id
        Schema::table('teacher_subject_allocations', function (Blueprint $table) {
            $table->unique(
                ['teacher_class_allocation_id', 'subject_id', 'exam_master_id'],
                'uk_teacher_subject'
            );
        });
    }

    public function down(): void
    {
        try {
            Schema::table('teacher_subject_allocations', function (Blueprint $table) {
                $table->dropUnique('uk_teacher_subject');
            });
        } catch (\Throwable $e) {
            // Ignore
        }

        Schema::table('teacher_subject_allocations', function (Blueprint $table) {
            $table->unique(
                ['teacher_class_allocation_id', 'subject_id'],
                'uk_teacher_subject'
            );
        });
    }
};