<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | EXISTING DATABASE COMPATIBILITY
        |--------------------------------------------------------------------------
        |
        | The exam_master_subjects table already exists in the current
        | school_management database.
        |
        | Do not try to create it again.
        |
        */

        if (Schema::hasTable('exam_master_subjects')) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | CREATE TABLE FOR A NEW DATABASE
        |--------------------------------------------------------------------------
        |
        | This is only used when installing the application on a completely
        | new database where the table does not already exist.
        |
        */

        Schema::create('exam_master_subjects', function (Blueprint $table) {

            $table->id();

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | DO NOT DROP EXISTING TABLE
        |--------------------------------------------------------------------------
        |
        | This database already contains exam_master_subjects and the table
        | is part of the working ERP.
        |
        */

        // Intentionally left empty.
    }
};