<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_books', function (Blueprint $table) {
            $table->id();

            /* -------------------------------------------------------------
             | Identification
             * ------------------------------------------------------------ */
            $table->string('book_code', 50)->unique();
            $table->string('book_name', 255);
            $table->string('author', 255)->nullable();
            $table->string('publisher', 255)->nullable();

            /* -------------------------------------------------------------
             | Publication details
             |
             | NOTE: Using smallInteger() instead of year(4) because
             | the source Excel file contains historical years like
             | 1881, 1676, 1904 — all below MySQL's YEAR minimum of 1901.
             * ------------------------------------------------------------ */
            $table->smallInteger('publication_year')->nullable();
            $table->string('edition', 100)->nullable();
            $table->integer('total_pages')->unsigned()->nullable();

            /* -------------------------------------------------------------
             | Category
             * ------------------------------------------------------------ */
            $table->unsignedBigInteger('category_id')->nullable();

            /* -------------------------------------------------------------
             | Inventory
             * ------------------------------------------------------------ */
            $table->integer('total_copies')->default(1);
            $table->integer('available_copies')->default(1);

            /* -------------------------------------------------------------
             | Donor / Bill info
             * ------------------------------------------------------------ */
            $table->string('donor_name', 255)->nullable();
            $table->string('bill_no', 50)->nullable();
            $table->date('bill_date')->nullable();
            $table->string('printed_price', 50)->nullable();
            $table->text('remarks')->nullable();

            /* -------------------------------------------------------------
             | Location / Status
             * ------------------------------------------------------------ */
            $table->string('shelf_location', 50)->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            /* -------------------------------------------------------------
             | Foreign key
             * ------------------------------------------------------------ */
            $table->foreign('category_id')
                  ->references('id')
                  ->on('library_book_categories')
                  ->onDelete('set null');

            /* -------------------------------------------------------------
             | Indexes
             * ------------------------------------------------------------ */
            $table->index('book_name');
            $table->index('category_id');
            $table->index('is_active');
            $table->index('donor_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_books');
    }
};