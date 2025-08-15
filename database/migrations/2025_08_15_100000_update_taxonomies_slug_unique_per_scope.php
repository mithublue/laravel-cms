<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('taxonomies', function (Blueprint $table) {
            // Drop existing unique index on slug
            $table->dropUnique('taxonomies_slug_unique');
            // Add composite unique index on (slug, scope)
            $table->unique(['slug', 'scope']);
        });
    }

    public function down(): void
    {
        Schema::table('taxonomies', function (Blueprint $table) {
            $table->dropUnique(['slug', 'scope']);
            $table->unique('slug');
        });
    }
};
