<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_result_details', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger(
                'student_result_id'
            );

            $table->unsignedBigInteger(
                'subject_id'
            );

            $table->decimal(
                'max_marks',
                8,
                2
            )->default(0);

            $table->decimal(
                'obtained_marks',
                8,
                2
            )->default(0);

            $table->decimal(
                'passing_marks',
                8,
                2
            )->default(0);

            $table->string(
                'subject_result',
                20
            )->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'student_result_details'
        );
    }
};