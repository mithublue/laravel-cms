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
        Schema::table('menu_items', function (Blueprint $table) {
            if (!Schema::hasColumn('menu_items', 'target')) {
                $table->string('target', 20)->nullable()->after('url');
            }
            if (!Schema::hasColumn('menu_items', 'order')) {
                $table->integer('order')->default(0)->after('target');
            }
            if (!Schema::hasColumn('menu_items', 'linkable_type')) {
                $table->string('linkable_type')->nullable()->after('order');
            }
            if (!Schema::hasColumn('menu_items', 'linkable_id')) {
                $table->unsignedBigInteger('linkable_id')->nullable()->after('linkable_type');
            }
            if (!Schema::hasColumn('menu_items', 'classes')) {
                $table->string('classes')->nullable()->after('linkable_id');
            }
            if (!Schema::hasColumn('menu_items', 'rel')) {
                $table->string('rel')->nullable()->after('classes');
            }
            if (!Schema::hasColumn('menu_items', 'meta')) {
                $table->json('meta')->nullable()->after('rel');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            if (Schema::hasColumn('menu_items', 'meta')) {
                $table->dropColumn('meta');
            }
            if (Schema::hasColumn('menu_items', 'rel')) {
                $table->dropColumn('rel');
            }
            if (Schema::hasColumn('menu_items', 'classes')) {
                $table->dropColumn('classes');
            }
            if (Schema::hasColumn('menu_items', 'linkable_id')) {
                $table->dropColumn('linkable_id');
            }
            if (Schema::hasColumn('menu_items', 'linkable_type')) {
                $table->dropColumn('linkable_type');
            }
            if (Schema::hasColumn('menu_items', 'order')) {
                $table->dropColumn('order');
            }
            if (Schema::hasColumn('menu_items', 'target')) {
                $table->dropColumn('target');
            }
        });
    }
};
