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
        Schema::create('taxonomies', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Human-readable name, e.g., Categories
            $table->string('slug')->unique(); // Machine name, e.g., categories
            $table->string('scope')->nullable()->index(); // e.g., page, post, news, product (null for global)
            $table->boolean('hierarchical')->default(true);
            $table->boolean('multiple')->default(true); // allow multiple terms per item
            $table->json('options')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxonomies');
    }
};
