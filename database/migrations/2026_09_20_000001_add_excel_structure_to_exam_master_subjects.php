<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('exam_master_subjects')) {
            return;
        }

        Schema::table('exam_master_subjects', function (Blueprint $table) {
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
            if (!Schema::hasColumn('exam_master_subjects', 'structure_source_hash')) {
                $table->string('structure_source_hash', 64)->nullable();
            }
            if (!Schema::hasColumn('exam_master_subjects', 'excel_row_number')) {
                $table->unsignedInteger('excel_row_number')->nullable();
            }
            if (!Schema::hasColumn('exam_master_subjects', 'is_optional')) {
                $table->boolean('is_optional')->default(false);
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('exam_master_subjects')) {
            return;
        }

        $columns = [
            'theory_max_marks',
            'theory_passing_marks',
            'oral_max_marks',
            'oral_passing_marks',
            'practical_max_marks',
            'practical_passing_marks',
            'structure_source_hash',
            'excel_row_number',
            'is_optional',
        ];

        Schema::table('exam_master_subjects', function (Blueprint $table) use ($columns) {
            foreach ($columns as $column) {
                if (Schema::hasColumn('exam_master_subjects', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
