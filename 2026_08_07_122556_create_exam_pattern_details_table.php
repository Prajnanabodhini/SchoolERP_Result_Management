<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('exam_pattern_details', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('exam_pattern_id');

            $table->unsignedBigInteger('standard_id');

            $table->unsignedBigInteger('subject_id');

            $table->integer('display_order')->default(0);

            $table->timestamps();


            $table->foreign('exam_pattern_id')
                ->references('id')
                ->on('exam_patterns')
                ->cascadeOnDelete();


            $table->foreign('standard_id')
                ->references('id')
                ->on('standards')
                ->cascadeOnDelete();


            $table->foreign('subject_id')
                ->references('id')
                ->on('subjects')
                ->cascadeOnDelete();

        });
    }


    public function down()
    {
        Schema::dropIfExists('exam_pattern_details');
    }
};