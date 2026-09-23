<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('exam_masters')) {
            return;
        }

        Schema::table('exam_masters', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_masters', 'exam_type')) {
                $table->string('exam_type', 50)->nullable();
            }
            if (!Schema::hasColumn('exam_masters', 'exam_structure_hash')) {
                $table->string('exam_structure_hash', 64)->nullable();
            }
        });

        if (Schema::hasColumn('exam_masters', 'exam_type') && Schema::hasColumn('exam_masters', 'exam_name')) {
            $rows = DB::table('exam_masters')->select('id', 'exam_name')->get();

            foreach ($rows as $row) {
                if (!empty($row->exam_type)) {
                    continue;
                }

                $name = strtoupper(trim((string) $row->exam_name));
                $type = null;

                foreach ([
                    'UNIT TEST 1',
                    'UNIT TEST 2',
                    'UNIT TEST 3',
                    'UNIT TEST 4',
                    'TERM 1',
                    'TERM 2',
                    'ANNUAL',
                ] as $candidate) {
                    if (str_starts_with($name, $candidate)) {
                        $type = $candidate;
                        break;
                    }
                }

                if ($type) {
                    DB::table('exam_masters')
                        ->where('id', $row->id)
                        ->update(['exam_type' => $type]);
                }
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('exam_masters')) {
            return;
        }

        Schema::table('exam_masters', function (Blueprint $table) {
            if (Schema::hasColumn('exam_masters', 'exam_type')) {
                $table->dropColumn('exam_type');
            }
            if (Schema::hasColumn('exam_masters', 'exam_structure_hash')) {
                $table->dropColumn('exam_structure_hash');
            }
        });
    }
};
