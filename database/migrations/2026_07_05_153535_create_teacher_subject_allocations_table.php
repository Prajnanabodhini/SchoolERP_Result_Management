<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_subject_allocations', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('teacher_class_allocation_id');

            $table->unsignedBigInteger('subject_id');

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_subject_allocations');
    }
};