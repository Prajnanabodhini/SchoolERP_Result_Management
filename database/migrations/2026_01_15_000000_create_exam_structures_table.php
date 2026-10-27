<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_structures', function (Blueprint $table) {
            $table->id();

            $table->string('standard_key', 20)->index();
            $table->string('standard_name', 60)->nullable();   // <-- MUST exist

            $table->string('exam_type', 30);
            $table->unsignedInteger('exam_type_order')->default(0);

            $table->string('subject_type', 30)->default('Academics');
            $table->string('subject_name', 120);
            $table->string('subject_code', 30)->nullable();
            $table->unsignedInteger('display_order')->default(0);

            $table->decimal('theory_max_marks',        8, 2)->default(0);
            $table->decimal('theory_passing_marks',    8, 2)->default(0);
            $table->decimal('oral_max_marks',          8, 2)->default(0);
            $table->decimal('oral_passing_marks',      8, 2)->default(0);
            $table->decimal('practical_max_marks',     8, 2)->default(0);
            $table->decimal('practical_passing_marks', 8, 2)->default(0);

            $table->decimal('total_max_marks',         8, 2)->default(0);
            $table->decimal('total_passing_marks',     8, 2)->default(0);

            $table->decimal('passing_percentage', 5, 2)->default(40);
            $table->boolean('is_compulsory')->default(true);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['standard_key', 'exam_type'], 'es_std_exam_idx');
            $table->unique(['standard_key', 'exam_type', 'subject_name'], 'es_unique_row');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_structures');
    }
};