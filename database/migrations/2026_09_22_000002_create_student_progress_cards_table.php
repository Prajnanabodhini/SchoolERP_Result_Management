<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_progress_cards', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('exam_master_id');
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('standard_id');
            $table->unsignedBigInteger('division_id');

            /* Personal */
            $table->string('phone', 40)->nullable();
            $table->string('email', 80)->nullable();
            $table->string('school_timing', 80)->nullable();
            $table->string('blood_group', 10)->nullable();
            $table->string('mother_tongue', 40)->nullable();

            /* Health — Term I and Term II */
            $table->string('term1_weight', 20)->nullable();
            $table->string('term1_height', 20)->nullable();
            $table->string('term2_weight', 20)->nullable();
            $table->string('term2_height', 20)->nullable();

            /* Attendance — JSON: month => { working, present, absent } */
            $table->json('attendance_data')->nullable();

            /* Teacher remarks */
            $table->string('passed_promoted_to', 80)->nullable();
            $table->string('school_reopens_on', 80)->nullable();

            /* Second-term grades — JSON: subject_id => grade */
            $table->json('second_term_grades')->nullable();

            /* Descriptive record — JSON: subject_id => { progress, interest, improvements } */
            $table->json('descriptive_records')->nullable();

            /* Audit */
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            /* Unique per student + exam + year */
            $table->unique(
                ['student_id', 'exam_master_id', 'academic_year_id'],
                'uk_progress_card_student_exam_year'
            );

            /* Indexes */
            $table->index('student_id');
            $table->index('exam_master_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_progress_cards');
    }
};