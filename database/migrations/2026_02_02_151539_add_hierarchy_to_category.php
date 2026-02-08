<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {

            // Add parent_id for the new hierarchy
            $table->unsignedBigInteger('parent_id')->nullable()->after('user_id');

            // Add a flag to indicate whether this category can be selected
            $table->boolean('is_selectable')->default(true)->after('name');

            // Optional sort order for siblings
            $table->integer('sort_order')->nullable()->after('is_selectable');

            // Code becomes optional metadata
            $table->string('code')->nullable()->after('sort_order');

            // Add FK to categories.id (self-referencing)
            $table->foreign('parent_id')
                ->references('id')->on('categories')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {

            // Drop FK first
            $table->dropForeign(['parent_id']);

            // Drop added columns
            $table->dropColumn(['parent_id', 'is_selectable', 'sort_order', 'code']);

            // Restore code to NOT NULL if needed
            // (Only do this if your legacy data guarantees it)
            $table->string('code')->nullable(false)->change();
        });
    }
};
