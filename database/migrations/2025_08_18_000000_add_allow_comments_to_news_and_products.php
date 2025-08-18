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
        if (Schema::hasTable('news') && ! Schema::hasColumn('news', 'allow_comments')) {
            Schema::table('news', function (Blueprint $table) {
                $table->boolean('allow_comments')->default(true)->after('is_featured');
            });
        }

        if (Schema::hasTable('products') && ! Schema::hasColumn('products', 'allow_comments')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('allow_comments')->default(true)->after('published_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('news') && Schema::hasColumn('news', 'allow_comments')) {
            Schema::table('news', function (Blueprint $table) {
                $table->dropColumn('allow_comments');
            });
        }

        if (Schema::hasTable('products') && Schema::hasColumn('products', 'allow_comments')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('allow_comments');
            });
        }
    }
};
