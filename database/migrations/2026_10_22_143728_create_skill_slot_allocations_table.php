<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skill_slot_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('standard_id');
            $table->unsignedBigInteger('division_id');
            $table->unsignedBigInteger('exam_master_id');
            $table->string('skill_slot', 10); // S1, S2, S3, S4
            $table->timestamps();

            // Prevent duplicate allocations
            $table->unique(['academic_year_id', 'standard_id', 'division_id', 'exam_master_id', 'skill_slot'], 'unique_skill_alloc');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skill_slot_allocations');
    }
};