<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_results', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('exam_master_id');
            $table->unsignedBigInteger('standard_id');
            $table->unsignedBigInteger('division_id');
            $table->unsignedBigInteger('student_id');

            $table->decimal('total_max_marks',10,2)->default(0);
            $table->decimal('total_obtained_marks',10,2)->default(0);
            $table->decimal('percentage',6,2)->default(0);

            $table->string('grade',10)->nullable();
            $table->string('result',20)->nullable();

            $table->integer('rank')->nullable();

            $table->timestamp('generated_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_results');
    }
};