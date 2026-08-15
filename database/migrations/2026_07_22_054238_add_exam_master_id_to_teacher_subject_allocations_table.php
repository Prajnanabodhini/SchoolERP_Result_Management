<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table(
        'teacher_subject_allocations',
        function ($table) {

            $table->unsignedBigInteger(
                'exam_master_id'
            )->nullable();
        }
    );
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_subject_allocations', function (Blueprint $table) {
            //
        });
    }
};
