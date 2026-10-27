<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds every column required by the progress card edit/print pages.
     * Uses hasColumn() guards so it's safe to re-run.
     */
    public function up(): void
    {
        Schema::table('student_progress_cards', function (Blueprint $table) {

            /* ------------------------------------------------------------
             | PERSONAL INFORMATION — editable fields
             | (Name, Father, Mother, Student ID, Roll, GR, Aadhar, Std, Div,
             |  DOB come from the ERP and are NOT stored here.)
             * ------------------------------------------------------------ */

            if (!Schema::hasColumn('student_progress_cards', 'mother_tongue')) {
                $table->string('mother_tongue', 40)->nullable()->after('division_id');
            }
            if (!Schema::hasColumn('student_progress_cards', 'blood_group')) {
                $table->string('blood_group', 10)->nullable()->after('mother_tongue');
            }
            if (!Schema::hasColumn('student_progress_cards', 'phone')) {
                $table->string('phone', 40)->nullable()->after('blood_group');
            }
            if (!Schema::hasColumn('student_progress_cards', 'email')) {
                $table->string('email', 80)->nullable()->after('phone');
            }
            if (!Schema::hasColumn('student_progress_cards', 'school_timing')) {
                $table->string('school_timing', 80)->nullable()->after('email');
            }
            if (!Schema::hasColumn('student_progress_cards', 'address')) {
                $table->string('address', 250)->nullable()->after('school_timing');
            }

            /* ------------------------------------------------------------
             | TERM I / TERM II — weight & height
             * ------------------------------------------------------------ */

            if (!Schema::hasColumn('student_progress_cards', 'term1_weight')) {
                $table->string('term1_weight', 10)->nullable()->after('address');
            }
            if (!Schema::hasColumn('student_progress_cards', 'term1_height')) {
                $table->string('term1_height', 10)->nullable()->after('term1_weight');
            }
            if (!Schema::hasColumn('student_progress_cards', 'term2_weight')) {
                $table->string('term2_weight', 10)->nullable()->after('term1_height');
            }
            if (!Schema::hasColumn('student_progress_cards', 'term2_height')) {
                $table->string('term2_height', 10)->nullable()->after('term2_weight');
            }

            /* ------------------------------------------------------------
             | TEACHER'S REMARKS (Term 2 / UT3 / UT4 / Annual only)
             * ------------------------------------------------------------ */

            if (!Schema::hasColumn('student_progress_cards', 'passed_promoted_to')) {
                $table->string('passed_promoted_to', 100)->nullable();
            }
            if (!Schema::hasColumn('student_progress_cards', 'school_reopens_on')) {
                $table->string('school_reopens_on', 100)->nullable();
            }

            /* ------------------------------------------------------------
             | JSON columns — all stored as arrays
             * ------------------------------------------------------------ */

            // Attendance: { "Jyeshtha": {"total_days":"31","working":"24","present":"22","absent":"2"}, ... }
            if (!Schema::hasColumn('student_progress_cards', 'attendance_data')) {
                $table->json('attendance_data')->nullable();
            }

            // Descriptive: { "subject_id": {"progress":"","interest":"","improvements":""}, ... }
            if (!Schema::hasColumn('student_progress_cards', 'descriptive_records')) {
                $table->json('descriptive_records')->nullable();
            }

            // Manual grades: { "subject_id": "A1", ... }
            if (!Schema::hasColumn('student_progress_cards', 'second_term_grades')) {
                $table->json('second_term_grades')->nullable();
            }

            // NEW — Class Test: { "attendance":"Present","result":"Good","remark":"..." }
            if (!Schema::hasColumn('student_progress_cards', 'class_test_data')) {
                $table->json('class_test_data')->nullable()
                      ->comment('{"attendance":"Present|Absent","result":"Good|Average","remark":"..."}');
            }

            // NEW — School Days Attendance:
            // { "15_aug":"Present","ganesh_sthapana":"Absent","ganesh_visarjan":"Present" }
            if (!Schema::hasColumn('student_progress_cards', 'school_days_data')) {
                $table->json('school_days_data')->nullable()
                      ->comment('{"15_aug":"Present|Absent","ganesh_sthapana":"...","ganesh_visarjan":"..."}');
            }

            /* ------------------------------------------------------------
             | AUDIT — who created / updated the row
             * ------------------------------------------------------------ */

            if (!Schema::hasColumn('student_progress_cards', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable();
            }
            if (!Schema::hasColumn('student_progress_cards', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_progress_cards', function (Blueprint $table) {
            $cols = [
                'mother_tongue', 'blood_group', 'phone', 'email', 'school_timing', 'address',
                'term1_weight', 'term1_height', 'term2_weight', 'term2_height',
                'passed_promoted_to', 'school_reopens_on',
                'attendance_data', 'descriptive_records', 'second_term_grades',
                'class_test_data', 'school_days_data',
                'created_by', 'updated_by',
            ];

            foreach ($cols as $c) {
                if (Schema::hasColumn('student_progress_cards', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};