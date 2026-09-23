<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('student_skill_marks', function (Blueprint $table) {
            $table->decimal('max_marks', 5, 2)->nullable()->after('marks_obtained');
            $table->decimal('passing_marks', 5, 2)->nullable()->after('max_marks');
        });
    }

    public function down()
    {
        Schema::table('student_skill_marks', function (Blueprint $table) {
            $table->dropColumn(['max_marks', 'passing_marks']);
        });
    }
};