<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // --- rollup_category ---
        Schema::table('rollup_category', function (Blueprint $table) {
            $table->dropForeign(['rollup_id']);
            $table->dropForeign(['category_id']);
        });

        Schema::table('rollup_category', function (Blueprint $table) {
            $table->foreign('rollup_id')
                ->references('id')
                ->on('rollup')
                ->restrictOnDelete();

            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->restrictOnDelete();
        });

        // --- rollup_user ---
        Schema::table('rollup_user', function (Blueprint $table) {
            $table->dropForeign(['rollup_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::table('rollup_user', function (Blueprint $table) {
            $table->foreign('rollup_id')
                ->references('id')
                ->on('rollup')
                ->restrictOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        // --- rollup_user ---
        Schema::table('rollup_user', function (Blueprint $table) {
            $table->dropForeign(['rollup_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::table('rollup_user', function (Blueprint $table) {
            $table->foreign('rollup_id')
                ->references('id')
                ->on('rollup')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

        // --- rollup_category ---
        Schema::table('rollup_category', function (Blueprint $table) {
            $table->dropForeign(['rollup_id']);
            $table->dropForeign(['category_id']);
        });

        Schema::table('rollup_category', function (Blueprint $table) {
            $table->foreign('rollup_id')
                ->references('id')
                ->on('rollup')
                ->onDelete('cascade');

            $table->foreign('category_id')
                ->references('id')
                ->on('categories')   // ← FIXED: was incorrectly 'users'
                ->onDelete('cascade');
        });
    }
};
