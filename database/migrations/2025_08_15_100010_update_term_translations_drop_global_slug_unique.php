<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('term_translations', function (Blueprint $table) {
            // Drop global unique on (locale, slug) to allow same slug across different taxonomies
            $table->dropUnique('term_translations_locale_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::table('term_translations', function (Blueprint $table) {
            // Restore the unique if rolling back
            $table->unique(['locale', 'slug']);
        });
    }
};
