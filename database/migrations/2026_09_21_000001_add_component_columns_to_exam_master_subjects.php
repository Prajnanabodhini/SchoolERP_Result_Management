<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_master_subjects', function (Blueprint $table) {

            if (!Schema::hasColumn('exam_master_subjects', 'exam_type')) {
                $table->string('exam_type', 40)->nullable();
            }
            if (!Schema::hasColumn('exam_master_subjects', 'theory_max_marks')) {
                $table->decimal('theory_max_marks', 8, 2)->default(0);
            }
            if (!Schema::hasColumn('exam_master_subjects', 'theory_passing_marks')) {
                $table->decimal('theory_passing_marks', 8, 2)->default(0);
            }
            if (!Schema::hasColumn('exam_master_subjects', 'oral_max_marks')) {
                $table->decimal('oral_max_marks', 8, 2)->default(0);
            }
            if (!Schema::hasColumn('exam_master_subjects', 'oral_passing_marks')) {
                $table->decimal('oral_passing_marks', 8, 2)->default(0);
            }
            if (!Schema::hasColumn('exam_master_subjects', 'practical_max_marks')) {
                $table->decimal('practical_max_marks', 8, 2)->default(0);
            }
            if (!Schema::hasColumn('exam_master_subjects', 'practical_passing_marks')) {
                $table->decimal('practical_passing_marks', 8, 2)->default(0);
            }
            if (!Schema::hasColumn('exam_master_subjects', 'total_max_marks')) {
                $table->decimal('total_max_marks', 8, 2)->default(0);
            }
            if (!Schema::hasColumn('exam_master_subjects', 'total_passing_marks')) {
                $table->decimal('total_passing_marks', 8, 2)->default(0);
            }
        });
    }

    public function down(): void
    {
        // no-op
    }
};