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
        Schema::create('mediables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->unsignedBigInteger('mediable_id');
            $table->string('mediable_type');
            $table->string('role')->nullable()->index(); // e.g., featured, gallery, attachment
            $table->integer('order')->default(0)->index();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['media_id', 'mediable_type', 'mediable_id', 'role'], 'mediables_unique');
            $table->index(['mediable_type', 'mediable_id'], 'mediables_morph_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mediables');
    }
};
