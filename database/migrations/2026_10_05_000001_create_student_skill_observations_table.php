<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_skill_observations', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('exam_master_id')->default(0);
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('subject_id');

            $table->json('observations')->nullable();
            $table->string('overall_grade', 10)->nullable();
            $table->boolean('is_absent')->default(0);
            $table->text('remark')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            // Ensure one observation row per student / subject / exam / year
            $table->unique(
                ['academic_year_id', 'exam_master_id', 'student_id', 'subject_id'],
                'unique_skill_observation'
            );

            $table->index('student_id');
            $table->index('subject_id');
            $table->index('academic_year_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_skill_observations');
    }
};