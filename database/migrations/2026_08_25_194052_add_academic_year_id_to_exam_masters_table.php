<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_masters', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_masters', 'academic_year_id')) {
                $table->unsignedBigInteger('academic_year_id')->nullable()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('exam_masters', function (Blueprint $table) {
            if (Schema::hasColumn('exam_masters', 'academic_year_id')) {
                $table->dropColumn('academic_year_id');
            }
        });
    }
};