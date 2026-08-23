<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | RUN MIGRATION
    |--------------------------------------------------------------------------
    */

    public function up(): void
    {
        Schema::create('user_designations', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | PRIMARY KEY
            |--------------------------------------------------------------------------
            */

            $table->id();


            /*
            |--------------------------------------------------------------------------
            | USER
            |--------------------------------------------------------------------------
            |
            | The user receiving the designation.
            |
            */

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | DESIGNATION
            |--------------------------------------------------------------------------
            |
            | The designation assigned to the user.
            |
            */

            $table->foreignId('designation_id')
                ->constrained('designations')
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | ACADEMIC YEAR
            |--------------------------------------------------------------------------
            |
            | NULL means the designation is not restricted to
            | a particular academic year.
            |
            */

            $table->foreignId('academic_year_id')
                ->nullable()
                ->constrained('academic_years')
                ->nullOnDelete();


            /*
            |--------------------------------------------------------------------------
            | STANDARD
            |--------------------------------------------------------------------------
            |
            | Used where a designation is related to a particular
            | standard, such as Class Teacher.
            |
            */

            $table->foreignId('standard_id')
                ->nullable()
                ->constrained('standards')
                ->nullOnDelete();


            /*
            |--------------------------------------------------------------------------
            | DIVISION
            |--------------------------------------------------------------------------
            |
            | Used where a designation is related to a particular
            | division, such as Class Teacher.
            |
            */

            $table->foreignId('division_id')
                ->nullable()
                ->constrained('divisions')
                ->nullOnDelete();


            /*
            |--------------------------------------------------------------------------
            | TIMESTAMPS
            |--------------------------------------------------------------------------
            */

            $table->timestamps();


            /*
            |--------------------------------------------------------------------------
            | INDEXES
            |--------------------------------------------------------------------------
            */

            $table->index([
                'user_id',
                'designation_id',
            ]);

            $table->index([
                'academic_year_id',
                'standard_id',
                'division_id',
            ]);

        });
    }


    /*
    |--------------------------------------------------------------------------
    | ROLLBACK MIGRATION
    |--------------------------------------------------------------------------
    */

    public function down(): void
    {
        Schema::dropIfExists(
            'user_designations'
        );
    }
};