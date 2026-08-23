<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('designations', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | PRIMARY KEY
            |--------------------------------------------------------------------------
            */

            $table->id();


            /*
            |--------------------------------------------------------------------------
            | DESIGNATION NAME
            |--------------------------------------------------------------------------
            */

            $table->string(
                'designation_name',
                100
            );


            /*
            |--------------------------------------------------------------------------
            | DESIGNATION CODE
            |--------------------------------------------------------------------------
            */

            $table->string(
                'designation_code',
                50
            )->unique();


            /*
            |--------------------------------------------------------------------------
            | SECTION
            |--------------------------------------------------------------------------
            |
            | Each designation belongs to a Section.
            |
            | Example:
            | PRIMARY SECTION
            | SECONDARY SECTION
            | JUNIOR COLLEGE
            |
            */

            $table->foreignId(
                'section_id'
            )
            ->constrained(
                'sections'
            )
            ->restrictOnDelete()
            ->cascadeOnUpdate();


            /*
            |--------------------------------------------------------------------------
            | DESCRIPTION
            |--------------------------------------------------------------------------
            */

            $table->text(
                'description'
            )->nullable();


            /*
            |--------------------------------------------------------------------------
            | ACTIVE STATUS
            |--------------------------------------------------------------------------
            */

            $table->boolean(
                'is_active'
            )->default(true);


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

            $table->index(
                'designation_name'
            );

            $table->index(
                'section_id'
            );

            $table->index(
                'is_active'
            );
        });
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'designations'
        );
    }
};