<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_progress_cards', function (Blueprint $table) {
            // Store the permanent ERP FeeMstStudent.Studentid separately
            // from the changeable SARAL/PEN value currently stored in `pen`.
            $table->string('studentid', 50)
                ->nullable()
                ->after('pen');

            $table->index(
                ['studentid', 'exam_master_id', 'academic_year_id'],
                'spc_studentid_exam_year_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('student_progress_cards', function (Blueprint $table) {
            $table->dropIndex('spc_studentid_exam_year_idx');
            $table->dropColumn('studentid');
        });
    }
};
