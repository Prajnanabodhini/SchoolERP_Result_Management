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
        Schema::create('exam_masters', function (Blueprint $table) {
            $table->id();
            $table->string('exam_name');
            
            // Foreign keys. Make sure these tables are created earlier in your migrations.
            $table->foreignId('standard_id')->constrained('standards')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            
            // Optional but recommended: add a status flag
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_masters');
    }
};