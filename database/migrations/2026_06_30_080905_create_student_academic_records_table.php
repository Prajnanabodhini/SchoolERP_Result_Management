<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_academic_records', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('student_id');

            $table->unsignedBigInteger('academic_year_id');

            $table->unsignedBigInteger('standard_id');

            $table->unsignedBigInteger('division_id');

            $table->string('roll_no')->nullable();

            $table->bigInteger('old_substudent_id')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_academic_records');
    }
};