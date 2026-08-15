<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_class_allocations', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id');

            $table->foreignId('academic_year_id');

            $table->foreignId('section_id');

            $table->foreignId('standard_id');

            $table->foreignId('division_id');

            $table->boolean('is_class_teacher')
                  ->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_class_allocations');
    }
};