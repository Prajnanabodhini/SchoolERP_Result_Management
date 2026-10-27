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
         * Identify which skill each row belongs to.
         * The default preserves existing rows until they are classified.
         */
        if (!Schema::hasColumn('student_skill_marks', 'skill_slot')) {
            Schema::table('student_skill_marks', function (Blueprint $table) {
                $table->string('skill_slot', 2)
                    ->default('S1')
                    ->after('category');
            });
        }

        /*
         * The old ENUM only supports Oral, Practical, Activity.
         * Skills now also need Health, Upasana, Chintanika,
         * Sanskrit, Library and Sanskrit Bharati categories.
         */
        DB::statement(
            'ALTER TABLE `student_skill_marks`
             MODIFY `category` VARCHAR(100) NOT NULL'
        );

        /*
         * Classify legacy Work Experience records as S2.
         */
        DB::table('student_skill_marks')
            ->where('skill_slot', 'S1')
            ->whereIn('subject_name', [
                'Craft',
                'Abacus',
                'Computer',
                'Robotics',
            ])
            ->update(['skill_slot' => 'S2']);

        /*
         * Classify legacy Physical Education records as S3.
         * These are the criterion names used by the previous controller.
         */
        DB::table('student_skill_marks')
            ->where('skill_slot', 'S1')
            ->whereIn('subject_name', [
                'Observation',
                'Uniform',
                'Oral',
                'Practical',
                'PT Period',
                'PT Periods',
                'PT Practical Exam',
            ])
            ->update(['skill_slot' => 'S3']);
    }

    public function down(): void
    {
        /*
         * Intentionally do not remove skill_slot or revert category to ENUM.
         * New assessment data can depend on both changes, and reverting
         * could mix skills or make valid category values incompatible.
         */
    }
};