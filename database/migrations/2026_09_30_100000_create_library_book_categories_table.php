<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_book_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_name', 100);
            $table->string('description', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('category_name');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_book_categories');
    }
};