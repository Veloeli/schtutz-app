<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            // 1. Drop FK
            $table->dropForeign('categories_user_id_foreign');

            // 2. Make NOT NULL
            $table->unsignedBigInteger('user_id')->nullable(false)->change();

            // 3. Re-add FK
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->restrictOnDelete();
        });
    }

    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            // Reverse order
            $table->dropForeign('categories_user_id_foreign');

            $table->unsignedBigInteger('user_id')->nullable()->change();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->restrictOnDelete();
        });
    }
};
