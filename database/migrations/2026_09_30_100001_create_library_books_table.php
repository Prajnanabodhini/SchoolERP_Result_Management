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

            $table->string('book_code', 50)->unique();
            $table->string('book_name', 255);
            $table->string('author', 255)->nullable();
            $table->string('publisher', 255)->nullable();

            $table->unsignedBigInteger('category_id')->nullable();

            $table->integer('total_copies')->default(1);
            $table->integer('available_copies')->default(1);

            $table->string('shelf_location', 50)->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->foreign('category_id')
                ->references('id')
                ->on('library_book_categories')
                ->onDelete('set null');

            $table->index('book_name');
            $table->index('category_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_books');
    }
};