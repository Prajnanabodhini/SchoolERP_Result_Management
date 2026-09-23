<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('student_skill_marks', function (Blueprint $table) {
            if (!Schema::hasColumn('student_skill_marks', 'is_absent')) {
                $table->boolean('is_absent')->default(0)->after('grade');
            }
            if (!Schema::hasColumn('student_skill_marks', 'percentage')) {
                $table->decimal('percentage', 5, 2)->nullable()->after('is_absent');
            }
        });
    }

    public function down()
    {
        Schema::table('student_skill_marks', function (Blueprint $table) {
            $table->dropColumn(['is_absent', 'percentage']);
        });
    }
};