<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_progress_cards', function (Blueprint $table) {
            // 1. Rename student_id to pen
            $table->renameColumn('student_id', 'pen');

            // 2. Add Snapshot Identity Fields
            $table->string('full_student_name')->nullable()->after('pen');
            $table->string('father_name')->nullable()->after('full_student_name');
            $table->string('mother_name')->nullable()->after('father_name');
            $table->string('gender')->nullable()->after('mother_name');
            $table->string('date_of_birth')->nullable()->after('gender');
            $table->string('gr_no')->nullable()->after('date_of_birth');
            $table->string('roll_no')->nullable()->after('gr_no');
            $table->string('aadhar_no')->nullable()->after('roll_no');

            // 3. Add Array Columns for Grades and Remarks
            $table->json('teacher_remark')->nullable()->after('school_reopens_on');
            $table->json('academic_grades')->nullable()->after('teacher_remark');
            $table->json('skill_grades')->nullable()->after('academic_grades');

            // 4. Add Result Snapshot Columns
            $table->decimal('percentage', 5, 2)->nullable()->after('skill_grades');
            $table->string('grade', 5)->nullable()->after('percentage');
            $table->string('result', 20)->nullable()->after('grade');
            $table->decimal('total_max_marks', 8, 2)->nullable()->after('result');
            $table->decimal('total_obtained_marks', 8, 2)->nullable()->after('total_max_marks');
        });
    }

    public function down(): void
    {
        Schema::table('student_progress_cards', function (Blueprint $table) {
            $table->renameColumn('pen', 'student_id');
            $table->dropColumn([
                'full_student_name', 'father_name', 'mother_name', 'gender', 
                'date_of_birth', 'gr_no', 'roll_no', 'aadhar_no',
                'teacher_remark', 'academic_grades', 'skill_grades',
                'percentage', 'grade', 'result', 'total_max_marks', 'total_obtained_marks'
            ]);
        });
    }
};