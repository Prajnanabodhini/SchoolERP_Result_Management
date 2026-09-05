<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('student_result_details', 'is_optional')) {
            Schema::table('student_result_details', function (Blueprint $table) {
                $table->boolean('is_optional')
                    ->default(false)
                    ->after('subject_result');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('student_result_details', 'is_optional')) {
            Schema::table('student_result_details', function (Blueprint $table) {
                $table->dropColumn('is_optional');
            });
        }
    }
};