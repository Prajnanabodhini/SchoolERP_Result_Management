<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'exam_completion_status',
            function (Blueprint $table)
            {
                $table->id();

                $table->unsignedBigInteger(
                    'academic_year_id'
                );

                $table->unsignedBigInteger(
                    'exam_master_id'
                );

                $table->unsignedBigInteger(
                    'standard_id'
                );

                $table->unsignedBigInteger(
                    'division_id'
                );

                $table->string(
                    'status',
                    20
                )->default('PENDING');

                $table->timestamp(
                    'completed_at'
                )->nullable();

                $table->timestamps();

                $table->unique([
                    'academic_year_id',
                    'exam_master_id',
                    'standard_id',
                    'division_id'
                ], 'exam_completion_unique');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'exam_completion_status'
        );
    }
};