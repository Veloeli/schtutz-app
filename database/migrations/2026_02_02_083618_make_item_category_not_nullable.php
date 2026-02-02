<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            // Drop old FK
            $table->dropForeign(['category_id']);
        });

        Schema::table('items', function (Blueprint $table) {
            // Make NOT NULL
            $table->unsignedBigInteger('category_id')->nullable(false)->change();

            // Re-add FK with restrict
            $table->foreign('category_id')
                ->references('id')->on('categories')
                ->restrictOnDelete();
        });
    }

    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });

        Schema::table('items', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->change();

            $table->foreign('category_id')
                ->references('id')->on('categories')
                ->nullOnDelete();
        });
    }
};
