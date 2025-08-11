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
        Schema::create('termables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('term_id')->constrained('terms')->cascadeOnDelete();
            $table->unsignedBigInteger('termable_id');
            $table->string('termable_type');
            $table->integer('order')->default(0)->index();
            $table->timestamps();

            $table->unique(['term_id', 'termable_type', 'termable_id'], 'termables_unique');
            $table->index(['termable_type', 'termable_id'], 'termables_morph_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('termables');
    }
};
