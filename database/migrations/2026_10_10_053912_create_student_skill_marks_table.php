<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_skill_marks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('standard_id');
            $table->unsignedBigInteger('division_id');
            $table->unsignedBigInteger('exam_master_id');
            $table->unsignedBigInteger('student_id');
            $table->string('student_name');
            $table->string('roll_no')->nullable();
            $table->string('gr_no')->nullable();
            $table->enum('category', ['Oral', 'Practical', 'Activity']);
            $table->string('subject_name');
            $table->integer('max_marks')->default(0);
            $table->integer('marks_obtained')->nullable();
            $table->boolean('is_absent')->default(0);
            $table->integer('total_marks')->nullable();
            $table->string('grade', 10)->nullable();
            $table->string('skill_teacher_name')->nullable();
            $table->timestamps();

            $table->index(['academic_year_id', 'exam_master_id', 'student_id'], 'ssm_year_exam_student_idx');
            $table->index(['standard_id', 'division_id'], 'ssm_std_div_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_skill_marks');
    }
};