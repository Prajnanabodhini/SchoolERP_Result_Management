<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_masters', function (Blueprint $table) {
            $table->unique(
                ['exam_name', 'standard_id', 'academic_year_id'],
                'uk_exam_masters_name_std_year'
            );
        });
    }

    public function down(): void
    {
        Schema::table('exam_masters', function (Blueprint $table) {
            $table->dropUnique('uk_exam_masters_name_std_year');
        });
    }
};