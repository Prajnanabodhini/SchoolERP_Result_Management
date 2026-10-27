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
            */
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | DESIGNATION
            |--------------------------------------------------------------------------
            */
            $table->foreignId('designation_id')
                  ->constrained('designations')
                  ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | ACADEMIC YEAR
            |--------------------------------------------------------------------------
            */
            $table->foreignId('academic_year_id')
                  ->nullable()
                  ->constrained('academic_years')
                  ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | STANDARD
            |--------------------------------------------------------------------------
            */
            $table->foreignId('standard_id')
                  ->nullable()
                  ->constrained('standards')
                  ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | DIVISION
            |--------------------------------------------------------------------------
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

            // FIXED: Explicit short name added to prevent MySQL 64-character limit error
            $table->index([
                'academic_year_id',
                'standard_id',
                'division_id',
            ], 'idx_ud_year_std_div'); 
        });
    }

    /*
    |--------------------------------------------------------------------------
    | ROLLBACK MIGRATION
    |--------------------------------------------------------------------------
    */

    public function down(): void
    {
        Schema::dropIfExists('user_designations');
    }
};