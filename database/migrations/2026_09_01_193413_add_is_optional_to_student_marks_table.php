<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('student_marks', 'is_optional')) {
            Schema::table('student_marks', function (Blueprint $table) {
                $table->boolean('is_optional')
                    ->default(0)
                    ->after('is_absent');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('student_marks', 'is_optional')) {
            Schema::table('student_marks', function (Blueprint $table) {
                $table->dropColumn('is_optional');
            });
        }
    }
};