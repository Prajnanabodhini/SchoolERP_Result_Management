<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ADD SECTION_ID COLUMN ONLY IF IT DOES NOT ALREADY EXIST
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasColumn('designations', 'section_id')) {

            Schema::table('designations', function (Blueprint $table) {

                $table->foreignId('section_id')
                    ->nullable()
                    ->after('designation_code');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | ADD FOREIGN KEY ONLY IF IT DOES NOT ALREADY EXIST
        |--------------------------------------------------------------------------
        |
        | MySQL/MariaDB does not provide a simple Schema::hasForeignKey()
        | method, so check information_schema.
        |
        */

        $database = DB::getDatabaseName();

        $foreignKeyExists = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', $database)
            ->where('TABLE_NAME', 'designations')
            ->where('COLUMN_NAME', 'section_id')
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->exists();

        if (
            !$foreignKeyExists &&
            Schema::hasColumn('designations', 'section_id')
        ) {

            Schema::table('designations', function (Blueprint $table) {

                $table->foreign('section_id')
                    ->references('id')
                    ->on('sections')
                    ->restrictOnDelete()
                    ->cascadeOnUpdate();
            });
        }
    }


    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | DROP FOREIGN KEY IF IT EXISTS
        |--------------------------------------------------------------------------
        */

        $database = DB::getDatabaseName();

        $foreignKey = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', $database)
            ->where('TABLE_NAME', 'designations')
            ->where('COLUMN_NAME', 'section_id')
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->value('CONSTRAINT_NAME');

        if (
            $foreignKey &&
            Schema::hasTable('designations')
        ) {

            Schema::table('designations', function (Blueprint $table) use ($foreignKey) {

                $table->dropForeign($foreignKey);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | DROP COLUMN ONLY IF IT EXISTS
        |--------------------------------------------------------------------------
        */

        if (Schema::hasColumn('designations', 'section_id')) {

            Schema::table('designations', function (Blueprint $table) {

                $table->dropColumn('section_id');
            });
        }
    }
};