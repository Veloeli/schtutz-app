<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['parent_id']);

            // Drop index (Laravel auto-names it, but phpMyAdmin shows the name)
            $table->dropIndex('categories_parent_id_foreign');

            // Drop the columns
            $table->dropColumn('parent_id');
            $table->dropColumn('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Recreate the columns
            $table->unsignedBigInteger('parent_id')->nullable()->after('user_id');
            $table->integer('sort_order')->nullable()->after('is_selectable');

            // Recreate the foreign key
            $table->foreign('parent_id')
                ->references('id')
                ->on('categories')
                ->onDelete('cascade');
        });
    }
};
