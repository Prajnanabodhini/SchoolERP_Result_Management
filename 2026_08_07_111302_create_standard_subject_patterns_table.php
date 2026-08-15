<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('standard_subject_patterns', function (Blueprint $table) {

            $table->id();

            $table->foreignId('standard_id')
                ->constrained('standards')
                ->cascadeOnDelete();

            $table->foreignId('exam_pattern_id')
                ->constrained('exam_patterns')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('standard_subject_patterns');
    }
};