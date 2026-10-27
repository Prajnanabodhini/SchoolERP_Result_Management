<?php

// Run: php artisan make:migration add_preprimary_skill_fields_to_student_skill_observations_table

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_skill_observations', function (Blueprint $table) {
            $table->string('ground_activities_grade')->nullable();
            $table->string('trg_senses_grade')->nullable();
            $table->text('trg_senses_remark')->nullable();
            $table->string('assembly_grade')->nullable();
            $table->string('activity_book_grade')->nullable();
            $table->text('activity_book_remark')->nullable();
            $table->string('general_skills_grade')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('student_skill_observations', function (Blueprint $table) {
            $table->dropColumn([
                'ground_activities_grade',
                'trg_senses_grade', 'trg_senses_remark',
                'assembly_grade',
                'activity_book_grade', 'activity_book_remark',
                'general_skills_grade'
            ]);
        });
    }
};