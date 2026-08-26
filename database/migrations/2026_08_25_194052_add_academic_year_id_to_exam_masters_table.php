<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exam_masters', function (Blueprint $table) {

            $table->foreignId('academic_year_id')
                ->nullable()
                ->after('id')
                ->constrained('academic_years')
                ->nullOnDelete();

            $table->index([
                'academic_year_id',
                'standard_id',
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | SAFE BACKFILL FROM TEACHER MARKS STATUS
        |--------------------------------------------------------------------------
        |
        | Only assign an academic year automatically when an exam is associated
        | with exactly ONE academic year in existing data.
        |
        | If an exam was used in multiple academic years, we leave it NULL.
        | Those records must be reviewed manually.
        |
        */

        $rows = DB::table('teacher_marks_status')
            ->whereNotNull('academic_year_id')
            ->whereNotNull('exam_master_id')
            ->select(
                'exam_master_id',
                'academic_year_id'
            )
            ->distinct()
            ->get()
            ->groupBy('exam_master_id');

        foreach ($rows as $examMasterId => $examYears) {

            $yearIds = $examYears
                ->pluck('academic_year_id')
                ->filter()
                ->unique()
                ->values();

            /*
            |--------------------------------------------------------------------------
            | Only one academic year = safe to backfill
            |--------------------------------------------------------------------------
            */

            if ($yearIds->count() === 1) {

                DB::table('exam_masters')
                    ->where('id', $examMasterId)
                    ->whereNull('academic_year_id')
                    ->update([
                        'academic_year_id' =>
                            $yearIds->first(),
                    ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | SECONDARY BACKFILL FROM TEACHER CLASS ALLOCATIONS
        |--------------------------------------------------------------------------
        |
        | For exams not found in teacher_marks_status, inspect TSA -> TCA.
        | Again, only backfill when exactly one year is found.
        |
        */

        $tsaRows = DB::table(
                'teacher_subject_allocations as tsa'
            )
            ->join(
                'teacher_class_allocations as tca',
                'tca.id',
                '=',
                'tsa.teacher_class_allocation_id'
            )
            ->whereNotNull('tsa.exam_master_id')
            ->whereNotNull('tca.academic_year_id')
            ->select(
                'tsa.exam_master_id',
                'tca.academic_year_id'
            )
            ->distinct()
            ->get()
            ->groupBy('exam_master_id');

        foreach ($tsaRows as $examMasterId => $examYears) {

            $yearIds = $examYears
                ->pluck('academic_year_id')
                ->filter()
                ->unique()
                ->values();

            if ($yearIds->count() === 1) {

                DB::table('exam_masters')
                    ->where('id', $examMasterId)
                    ->whereNull('academic_year_id')
                    ->update([
                        'academic_year_id' =>
                            $yearIds->first(),
                    ]);
            }
        }
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_masters', function (Blueprint $table) {

            /*
            | Drop index before dropping the column.
            */
            $table->dropIndex([
                'exam_masters_academic_year_id_standard_id_index',
            ]);

            $table->dropForeign([
                'academic_year_id',
            ]);

            $table->dropColumn(
                'academic_year_id'
            );
        });
    }
};