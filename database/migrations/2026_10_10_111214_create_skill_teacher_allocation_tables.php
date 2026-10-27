<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | SKILL TEACHER CLASS ALLOCATIONS
        |--------------------------------------------------------------------------
        */
        Schema::create('skill_teacher_class_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->foreignId('academic_year_id')
                  ->constrained('academic_years')
                  ->cascadeOnDelete();
            $table->foreignId('section_id')
                  ->nullable()
                  ->constrained('sections')
                  ->nullOnDelete();
            $table->foreignId('standard_id')
                  ->constrained('standards')
                  ->cascadeOnDelete();
            $table->foreignId('division_id')
                  ->constrained('divisions')
                  ->cascadeOnDelete();
            $table->boolean('is_class_teacher')->default(false);
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | SKILL TEACHER SUBJECT ALLOCATIONS
        |--------------------------------------------------------------------------
        |
        | NOTE: subject_id and exam_master_id do NOT have foreign key
        | constraints because the referenced tables may not exist yet
        | (or may exist in a different database). This is intentional.
        |
        */
        Schema::create('skill_teacher_subject_allocations', function (Blueprint $table) {
            $table->id();

            // Foreign key to the class allocation (this table exists)
            $table->unsignedBigInteger('skill_teacher_class_allocation_id');
            $table->foreign('skill_teacher_class_allocation_id', 'fk_skill_sub_class_alloc')
                  ->references('id')
                  ->on('skill_teacher_class_allocations')
                  ->cascadeOnDelete();

            // Plain IDs without FK constraints
            $table->unsignedBigInteger('exam_master_id');
            $table->unsignedBigInteger('subject_id');

            $table->timestamps();

            // Helpful indexes for lookups
            $table->index('exam_master_id');
            $table->index('subject_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skill_teacher_subject_allocations');
        Schema::dropIfExists('skill_teacher_class_allocations');
    }
};