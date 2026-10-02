<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_issues', function (Blueprint $table) {

            $table->id();

            // Borrower
            $table->string('borrower_type', 20)->default('student');   // student | teacher
            $table->string('borrower_id', 50);                          // Studentid / user id
            $table->string('borrower_name');                            // "STUDNAME FATHERNAME"
            $table->string('borrower_class', 50)->nullable();           // e.g. "SEVENTH - B"

            // Book (simple — no master table needed)
            $table->string('book_name');
            $table->string('book_code', 50)->nullable();
            $table->string('author', 100)->nullable();

            // Dates
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->date('return_date')->nullable();

            // Status
            $table->string('status', 20)->default('issued');            // issued | returned

            $table->string('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            $table->index(['status', 'due_date']);
            $table->index('borrower_id');
            $table->index('issue_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_issues');
    }
};