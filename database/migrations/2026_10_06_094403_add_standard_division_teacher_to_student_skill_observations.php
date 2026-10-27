<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds standard_id, division_id, and skill_teacher_id to the
     * student_skill_observations table so each record is self-contained
     * and can be queried directly without an ERP lookup.
     */
    public function up(): void
    {
        Schema::table('student_skill_observations', function (Blueprint $table) {

            // Place new columns right after academic_year_id
            // for logical grouping (year → standard → division → teacher)
            $table->unsignedBigInteger('standard_id')
                ->nullable()
                ->after('academic_year_id');

            $table->unsignedBigInteger('division_id')
                ->nullable()
                ->after('standard_id');

            $table->unsignedBigInteger('skill_teacher_id')
                ->nullable()
                ->after('division_id');

            // Composite index — helps the summary/entry queries
            // which filter by (standard, division, exam, subject)
            $table->index(
                ['standard_id', 'division_id'],
                'idx_skill_obs_std_div'
            );

            // Index for teacher-based reports
            $table->index('skill_teacher_id', 'idx_skill_obs_teacher');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_skill_observations', function (Blueprint $table) {

            // Drop indexes first (good practice)
            $table->dropIndex('idx_skill_obs_std_div');
            $table->dropIndex('idx_skill_obs_teacher');

            // Then drop the columns
            $table->dropColumn([
                'standard_id',
                'division_id',
                'skill_teacher_id',
            ]);
        });
    }
};